<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'clock_in', 'clock_out', 'status'];
    protected $dates = ['clock_in', 'clock_out'];
    protected $attributes = [
        'clock_in' => null,
        'clock_out' => null,
        //'breaks_duration' => 0,
        //'work_duration' => 0,
    ];

    /**
     * Attendanceのユーザーとのリレーション (逆1対多)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Attendanceの休憩とのリレーション (1対多)
     */
    public function work_breaks()
    {
        return $this->hasMany(WorkBreak::class); //'attendance_id'
    }

    // 勤務開始フォーマット
    public function getFormattedClockInAttribute()
    {
        return optional($this->clock_in)->format('H:i:s') ?? '未出勤';
    }

    // 勤務終了フォーマット
    public function getFormattedClockOutAttribute()
    {
        return optional($this->clock_out)->format('H:i:s') ?? '未退勤';
    }

    // 未退勤（デフォルト時間）の判定
    public function getIsDefaultClockOutAttribute()
    {
        // clock_outがCarbonオブジェクトとして存在し、時間部分が23:59:59と一致しているか確認
        return $this->clock_out && $this->clock_out->format('H:i:s') === '23:59:59';
    }

    // 休憩時間フォーマット
    public function getFormattedBreakDurationAttribute()
    {
        return gmdate('H:i:s', $this->breaks_duration ?? 0);
    }

    // 勤務時間フォーマット
    public function getFormattedWorkDurationAttribute()
    {
        return gmdate('H:i:s', $this->work_duration ?? 0);
    }

    /**
     * 休憩時間合計を取得
     */
    public function getBreaksDurationAttribute()
    {
        return $this->work_breaks->sum(function ($break) {
            // break_start と break_end を Carbon インスタンスに変換
            $breakStart = $break->break_start ? Carbon::parse($break->break_start) : null;
            $breakEnd = $break->break_end ? Carbon::parse($break->break_end) : null;

            // break_end がある場合のみ差分を計算
            return $breakEnd ? $breakEnd->diffInSeconds($breakStart) : 0;
        });
    }
    /**
     * 勤務時間合計を取得
     */
    public function getWorkDurationAttribute()
    {
        if ($this->clock_in) {
            $clockIn = Carbon::parse($this->clock_in);
            $clockOut =  $this->clock_out ? Carbon::parse($this->clock_out) : $clockIn->copy()->endOfDay(); // デフォルトを23:59:59に設定
            // 総勤務時間を計算
            $totalWorkSeconds = $clockIn->diffInSeconds($clockOut);

            // 休憩時間を取得（既存のアクセサ利用）
            $breakSeconds = $this->breaks_duration;


            // 勤務時間から休憩時間を引いた値を返す
            return max($totalWorkSeconds - $breakSeconds, 0);
        }

        return 0; // 出勤が未設定の場合は 0
    }
}
