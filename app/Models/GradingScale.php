<?php
namespace App\Models;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class GradingScale extends Model {
    use BelongsToSchool;
    protected $fillable = ["school_id","grade","min_percent","max_percent","gpa"];
    protected $casts = ["min_percent"=>"decimal:2","max_percent"=>"decimal:2","gpa"=>"decimal:2"];
}