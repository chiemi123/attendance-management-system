<?php

namespace Database\Factories;

use App\Models\WorkBreak;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;


class WorkBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = WorkBreak::class;


    public function definition()
    {
        $break_start = Carbon::today()->addHours(rand(11, 13)); // 休憩開始時間
        $break_end = (clone $break_start)->addMinutes(rand(30, 60)); // 休憩終了時間

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $break_start,
            'break_end' => $break_end,
            'break_duration' => $break_start->diffInMinutes($break_end), // 休憩時間合計
        ];
    }
}
