<?php
namespace App\Services;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SmsService — Sends SMS via NioSMS (Pakistan) or Twilio (International).
 *
 * Configure in school settings:
 *   sms_provider: 'niosms' or 'twilio'
 *   sms_api_key:  API key from provider
 *   sms_sender_id: Sender name/number
 *
 * Usage:
 *   app(SmsService::class)->send('+923001234567', 'Hello World', 'manual');
 *   app(SmsService::class)->sendBulk($phoneNumbersArray, 'Message text', 'fee_reminder');
 */
class SmsService {

    private string $provider;
    private string $apiKey;
    private string $senderId;

    public function __construct() {
        $settings       = tenant()->getSchool()->settings ?? [];
        $this->provider = $settings['sms_provider']  ?? 'niosms';
        $this->apiKey   = $settings['sms_api_key']   ?? '';
        $this->senderId = $settings['sms_sender_id'] ?? 'GNOSIS';
    }

    /**
     * Send SMS to a single recipient.
     */
    public function send(string $phone, string $message, string $triggerType = 'manual', string $recipientName = ''): bool {
        // Clean phone number — ensure Pakistani format
        $phone = $this->cleanPhone($phone);
        if (empty($phone)) {
            $this->log($phone, $message, 'failed', $triggerType, $recipientName, 'Invalid phone number');
            return false;
        }

        // Check if SMS is enabled for this school
        if (!tenant()->getSchool()->setting('sms_enabled', false)) {
            $this->log($phone, $message, 'failed', $triggerType, $recipientName, 'SMS not enabled for this school');
            return false;
        }

        if (empty($this->apiKey)) {
            $this->log($phone, $message, 'failed', $triggerType, $recipientName, 'SMS API key not configured');
            return false;
        }

        try {
            $success = match($this->provider) {
                'niosms' => $this->sendViaNioSms($phone, $message),
                'twilio' => $this->sendViaTwilio($phone, $message),
                default  => false,
            };

            $this->log($phone, $message, $success ? 'sent' : 'failed', $triggerType, $recipientName);
            return $success;
        } catch (\Exception $e) {
            Log::error('SMS send failed', ['phone'=>$phone,'error'=>$e->getMessage()]);
            $this->log($phone, $message, 'failed', $triggerType, $recipientName, $e->getMessage());
            return false;
        }
    }

    /**
     * Send same message to multiple recipients.
     */
    public function sendBulk(array $recipients, string $message, string $triggerType = 'manual'): array {
        $results = ['sent'=>0,'failed'=>0];
        foreach ($recipients as $recipient) {
            $phone = is_array($recipient) ? ($recipient['phone']??'') : $recipient;
            $name  = is_array($recipient) ? ($recipient['name']??'')  : '';
            $sent  = $this->send($phone, $message, $triggerType, $name);
            $sent ? $results['sent']++ : $results['failed']++;
        }
        return $results;
    }

    // ── Providers ─────────────────────────────────────────────────────────────

    private function sendViaNioSms(string $phone, string $message): bool {
        $response = Http::get('https://api.niosms.com/sendsms', [
            'token'   => $this->apiKey,
            'to'      => $phone,
            'message' => $message,
            'sender'  => $this->senderId,
        ]);
        return $response->successful() && !str_contains(strtolower($response->body()), 'error');
    }

    private function sendViaTwilio(string $phone, string $message): bool {
        $settings  = tenant()->getSchool()->settings ?? [];
        $accountSid= $settings['twilio_account_sid'] ?? '';
        $authToken = $settings['twilio_auth_token']  ?? '';
        if (empty($accountSid) || empty($authToken)) return false;
        $response = Http::withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $this->senderId,
                'To'   => $phone,
                'Body' => $message,
            ]);
        return $response->successful();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function cleanPhone(string $phone): string {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        // Convert Pakistani local format to international
        if (str_starts_with($phone, '03')) {
            $phone = '+92' . substr($phone, 1);
        } elseif (str_starts_with($phone, '3') && strlen($phone) === 10) {
            $phone = '+92' . $phone;
        }
        return $phone;
    }

    private function log(string $phone, string $message, string $status, string $triggerType, string $name = '', string $error = ''): void {
        try {
            SmsLog::create([
                'school_id'       => tenant()->getSchoolId(),
                'recipient_phone' => $phone,
                'recipient_name'  => $name,
                'message'         => $message,
                'status'          => $status,
                'trigger_type'    => $triggerType,
                'error_message'   => $error ?: null,
                'sent_at'         => $status === 'sent' ? now() : null,
            ]);
        } catch (\Exception $e) {
            Log::error('SMS log failed', ['error'=>$e->getMessage()]);
        }
    }
}