<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Attendance::class;

    public function definition()
    {
        $clockIn = Carbon::today()->addHours(rand(8, 10)); // 出勤時間をランダムに設定
        $clockOut = (clone $clockIn)->addHours(rand(6, 8)); // 退勤時間をランダムに設定

        return [
            'user_id' => User::factory(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'work_duration' => $clockIn->diffInMinutes($clockOut), // 勤務時間合計を計算
        ];
    }
}
