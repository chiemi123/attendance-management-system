<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Attendance;

class FixUnfinishedAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:fix-unfinished';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '未退勤の勤怠記録を補正します';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday();

        // 未退勤の勤怠記録を取得
        $unfinishedAttendances = Attendance::whereNull('clock_out')
            ->whereDate('clock_in', '<', $yesterday->endOfDay())
            ->get();

        foreach ($unfinishedAttendances as $attendance) {
            $attendance->update([
                'clock_out' => $attendance->clock_in->copy()->endOfDay(), // その日の23:59:59
                'work_duration' => $attendance->clock_in->diffInMinutes($attendance->clock_in->copy()->endOfDay()),
                'status' => 'clocked_out',
            ]);
        }

        $this->info('未退勤記録を補正しました。');
    }
}
