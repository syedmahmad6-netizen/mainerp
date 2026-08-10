<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class SmsLog extends Model {
    use BelongsToSchool;
    protected $fillable = [
        'school_id','recipient_phone','recipient_name','message',
        'status','trigger_type','error_message','sent_at',
    ];
    protected $casts = ['sent_at'=>'datetime'];
}