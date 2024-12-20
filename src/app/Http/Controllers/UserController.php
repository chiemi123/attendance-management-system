<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 全ユーザー一覧ページを表示
     */
    public function index()
    {
        // ページネーションでユーザーを取得
        $users = User::paginate(5);

        return view('user.index', compact('users'));
    }

    /**
     * 指定されたユーザーの勤怠表を表示
     */
    public function show($id, Request $request)
    {
        // ユーザー情報を取得
        $user = User::findOrFail($id);

        // 最大月を設定（2024年11月）
        $maxMonth = Carbon::create(2024, 11)->format('Y-m');
        $minMonth = Carbon::create(2024, 1)->format('Y-m');

        // 表示する月を取得（リクエストから、デフォルトは現在の月）
        $month = $request->input('month', now()->format('Y-m'));

        // 月が範囲外の場合は補正
        if ($month < $minMonth) {
            $month = $minMonth; // 最小月に補正
        } elseif ($month > $maxMonth) {
            $month = $maxMonth; // 最大月に補正
        }

        // 月の開始日と終了日を計算
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();


        // 勤怠データを取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in', [
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d'),
            ])
            ->get()
            ->keyBy(fn($att) => Carbon::parse($att->clock_in)->format('Y-m-d'));

        // 日付ごとにデータをマージ
        $attendanceData = collect($startOfMonth->daysUntil($endOfMonth))->mapWithKeys(function ($date) use ($attendances) {
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendances[$dateKey] ?? new Attendance();

            // 未退勤の場合は退勤時間をその日の 23:59:59 に設定
            if ($attendance->clock_in && !$attendance->clock_out) {
                $attendance->clock_out = Carbon::parse($attendance->clock_in)->endOfDay();
            }

            return [$dateKey => $attendance];
        });

        return view('user.show', compact('user', 'month', 'maxMonth', 'attendanceData'));
    }
}
