<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkBreak;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $userId = 1; // 特定のユーザーIDを指定
        $user = User::find($userId); // ユーザーを取得


        foreach (range(1, 31) as $day) { // 10月1日～31日まで繰り返し
            $date = Carbon::create(2024, 10, $day); // 2024年10月の日付を作る

            // 平日判定（CarbonのisWeekdayメソッドを使う）
            if ($date->isWeekday()) { // 平日のみ処理する
                // 勤怠データを作る（勤務開始と終了）
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'clock_in' => $date->copy()->setTime(9, 0), // 朝9時に勤務開始
                    'clock_out' => $date->copy()->setTime(18, 0), // 夕方6時に勤務終了
                ]);

                // 休憩データを作る（昼休み）
                WorkBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $date->copy()->setTime(12, 0), // お昼12時に休憩開始
                    'break_end' => $date->copy()->setTime(13, 0), // お昼1時に休憩終了
                ]);
            }
        }

        // ユーザーを作成
        //User::factory(100)->create();

        // 各ユーザーに対して勤怠データと休憩データを生成
        //User::all()->each(function ($user) {
        // 各ユーザーに1つの勤怠データを作成
        //$attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 勤怠データに関連付けられた2つの休憩データを作成
        //WorkBreak::factory(2)->create(['attendance_id' => $attendance->id]);
        //});
    }
}
