<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkBreak extends Model
{
    use HasFactory;
    protected $fillable = ['attendance_id', 'break_start', 'break_end'];
    protected $dates = ['break_start', 'break_end'];

    /**
     * Breakの勤怠情報 (Attendance) とのリレーション (逆1対多)
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

}
