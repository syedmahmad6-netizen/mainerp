<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class SmsTemplate extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','name','message_body','variables','is_active'];
    protected $casts = ['variables'=>'array','is_active'=>'boolean'];
    public function render(array $data): string {
        $msg = $this->message_body;
        foreach ($data as $key => $value) {
            $msg = str_replace('{'.$key.'}', $value, $msg);
        }
        return $msg;
    }
}