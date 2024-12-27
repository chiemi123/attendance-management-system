<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\WorkBreak;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function work(Request $request)
    {
        $user = Auth::user(); // ログイン中のユーザー
        $action = $request->input('action'); // どのボタンが押されたかを取得

        // 勤務開始ボタンが押された場合
        if ($action === 'clock_in') {
            // 既に今日の勤務開始記録があるか確認
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('clock_in', Carbon::today()) // 今日の日付でフィルタ
                ->first();

            Attendance::create([
                'user_id' => $user->id,
                'clock_in' => Carbon::now(),
                'status' => '1', // 勤務中のステータスを設定
            ]);

            $status = 1; // 勤務中  
            return redirect()->back()->with('success', '出勤が完了しました。');
        }

        // 勤務終了ボタンが押された場合
        if ($action === 'clock_out') {
            $attendance = Attendance::where('user_id', $user->id) //データベースの Attendance テーブルから user_id カラムが ログイン中のユーザーのID と一致するデータを検索
                ->whereNull('clock_out')
                ->whereDate('clock_in', Carbon::today()) // 今日の日付を限定
                ->first(); //最初の1件だけ取得し、必要以上のデータを扱わないようにするため
            if ($attendance) {
                $attendance->update(['clock_out' => Carbon::now(), 'status' => '3']);
                // 成功メッセージを返す
                return redirect()->back()->with('success', '退勤が完了しました。');
            }
            $status = 3; // 勤務終了済み
        }

        // 休憩開始ボタンが押された場合
        if ($action === 'break_start') {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('clock_out')
                ->whereDate('clock_in', Carbon::today()) // 今日の日付を限定
                ->first();
            if ($attendance) {
                WorkBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::now(),

                ]);
                $attendance->update(['status' => '2']);
                // 成功メッセージを返す
                return redirect()->back()->with('success', '休憩開始しました。');
            }
            $status = 2; // ここで「休憩中」に設定する
        }

        // 休憩終了ボタンが押された場合
        if ($action === 'break_end') {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('clock_out')
                ->whereDate('clock_in', Carbon::today()) // 今日の日付を限定
                ->first();
            if ($attendance) {
                $workBreak = WorkBreak::where('attendance_id', $attendance->id)
                    ->whereNull('break_end')
                    ->first();
                if ($workBreak) {
                    $workBreak->update(['break_end' => Carbon::now()]);
                    // 成功メッセージを返す
                    return redirect()->back()->with('success', '休憩終了しました。');
                }
            }
        }

        // 勤怠データを再取得して状態を判定
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', Carbon::today())
            ->first();

        if (!$attendance) {
            $status = 0; // 勤務前
        } elseif ($attendance->clock_out) {
            $status = 3; // 勤務終了済み
        } elseif (WorkBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->exists()
        ) {
            $status = 2; // 休憩中
        } else {
            $status = 1; // 勤務中
        }


        // 元のページに戻る
        return redirect()->back()->with(compact(
            'status'
        ));
    }

    // 勤怠ページ表示
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_in', $today)
            ->first();

        // 状態を判定する
        if (!$attendance) {
            $status = 0; // 勤務前
        } elseif ($attendance->clock_out) {
            $status = 3; // 勤務終了済み
        } elseif (WorkBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end') // ここで「休憩中」を判定
            ->exists() //特定の条件に一致するデータがデータベースに存在するかどうかを調べる
        ) {
            $status = 2; // 休憩中
        } else {
            $status = 1; // 勤務中
        }

        return view('index', compact(
            'status'
        ));
    }


    public function attendance(Request $request)
    {
        // 今日の日付
        $today = now()->format('Y-m-d');

        // 日付一覧を取得（最新から過去順、今日以降を除外）
        $dates = Attendance::selectRaw('DATE(clock_in) as date')
            ->whereNotNull('clock_in') // nullを除外
            ->whereDate('clock_in', '<=', $today) // 今日以降を除外
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        // URLパラメータから日付を取得、指定がなければ今日の日付またはデータの最新日付を使用
        $defaultDate = $dates->first() ?: now()->format('Y-m-d'); // データがなければ今日の日付
        $date = $request->input('dates', $defaultDate);

        // 日付をCarbonインスタンスに変換
        $carbonDate = \Carbon\Carbon::parse($date);
        // 選択した日付の勤怠データを全ユーザー分取得
        $users = User::with(['attendances' => function ($query) use ($carbonDate) {
            $query->whereDate('clock_in', $carbonDate);
        }])->paginate(5); // ページネーション

        // デフォルト値を設定
        foreach ($users as $user) {
            if ($user->attendances->isEmpty()) {
                $user->attendances = collect([new Attendance([])]);
            } else {
                // 勤怠データがあるが退勤データがない場合、退勤時間を23:59:59に設定
                $user->attendances = $user->attendances->map(function ($attendance) {
                    if ($attendance->clock_in && !$attendance->clock_out) {
                        $attendance->clock_out = Carbon::parse($attendance->clock_in)->endOfDay();
                    }
                    return $attendance;
                });
            }
        }


        // ビューに渡す
        return view(
            'attendance',
            compact('users', 'carbonDate', 'dates')
        );
    }
}
