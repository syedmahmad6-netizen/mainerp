<?php
namespace App\Filament\SchoolAdmin\Pages;

use App\Models\Section;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\SmsService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SendSms extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Send SMS';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.school-admin.pages.send-sms';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'target_type'    => 'all_parents',
            'section_id'     => null,
            'custom_number'  => '',
            'message_type'   => 'template',
            'template_id'    => null,
            'custom_message' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Recipients')
                ->schema([
                    Forms\Components\Select::make('target_type')
                        ->label('Send To')
                        ->options([
                            'all_parents'   => 'All Parents',
                            'class_parents' => 'Parents of a Specific Class',
                            'all_teachers'  => 'All Teachers',
                            'custom_number' => 'Custom Phone Number',
                        ])
                        ->required()
                        ->default('all_parents'),

                    Forms\Components\Select::make('section_id')
                        ->label('Class & Section')
                        ->helperText('Only used if "Parents of a Specific Class" is selected above.')
                        ->options(fn () =>
                            Section::with('schoolClass')->get()
                                ->mapWithKeys(fn ($s) => [$s->id => $s->schoolClass->name . ' – ' . $s->name])
                        )
                        ->searchable(),

                    Forms\Components\TextInput::make('custom_number')
                        ->label('Phone Number')
                        ->tel()
                        ->placeholder('03XX-XXXXXXX')
                        ->helperText('Only used if "Custom Phone Number" is selected above.'),
                ])->columns(2),

            Forms\Components\Section::make('Message')
                ->schema([
                    Forms\Components\Radio::make('message_type')
                        ->label('Message Type')
                        ->options(['template' => 'Use Template', 'custom' => 'Write Custom'])
                        ->default('template')
                        ->inline(),

                    Forms\Components\Select::make('template_id')
                        ->label('Select Template (if using a Template)')
                        ->options(fn () => SmsTemplate::where('is_active', true)->pluck('name', 'id'))
                        ->searchable(),

                    Forms\Components\Textarea::make('custom_message')
                        ->label('Custom Message Text (if Writing Custom)')
                        ->rows(4)
                        ->helperText('Max 160 characters for single SMS. Longer messages are charged as multiple SMS.'),
                ]),
        ])->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $sms  = app(SmsService::class);

        $message = '';
        if ($data['message_type'] === 'template' && !empty($data['template_id'])) {
            $template = SmsTemplate::find($data['template_id']);
            $message  = $template?->render(['school_name' => tenant()->getSchool()->name]) ?? '';
        } else {
            $message = $data['custom_message'] ?? '';
        }

        if (empty(trim($message))) {
            Notification::make()->title('Please enter a message.')->warning()->send();
            return;
        }

        $recipients = [];
        switch ($data['target_type']) {
            case 'all_parents':
                $recipients = User::where('role', 'parent')->whereNotNull('phone')->get()
                    ->map(fn ($u) => ['phone' => $u->phone, 'name' => $u->name])->toArray();
                break;
            case 'class_parents':
                if (!empty($data['section_id'])) {
                    $section = Section::find($data['section_id']);
                    if ($section) {
                        $studentIds = $section->students()->pluck('id');
                        $recipients = \App\Models\ParentProfile::whereHas('students', fn ($q) => $q->whereIn('student_profile_id', $studentIds))
                            ->with('user')->get()
                            ->map(fn ($p) => ['phone' => $p->user->phone ?? '', 'name' => $p->user->name ?? ''])->toArray();
                    }
                }
                break;
            case 'all_teachers':
                $recipients = User::where('role', 'teacher')->whereNotNull('phone')->get()
                    ->map(fn ($u) => ['phone' => $u->phone, 'name' => $u->name])->toArray();
                break;
            case 'custom_number':
                if (!empty($data['custom_number'])) {
                    $recipients = [['phone' => $data['custom_number'], 'name' => '']];
                }
                break;
        }

        if (empty($recipients)) {
            Notification::make()->title('No recipients found.')->warning()->send();
            return;
        }

        $results = $sms->sendBulk($recipients, $message, 'manual');

        Notification::make()
            ->title("SMS sent: {$results['sent']} delivered, {$results['failed']} failed")
            ->body(count($recipients) . ' total recipients.')
            ->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('send')->label('Send SMS')->submit('send')->icon('heroicon-o-paper-airplane')->color('primary'),
        ];
    }
}