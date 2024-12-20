@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('header__nav')
<nav>
    <ul class="header__nav">
        @if (Auth::check())
        <li class="header__nav__item">
            <a class="header__nav__link" href="/">ホーム</a>
            <a class="header__nav__link" href="{{ route('user.show', ['id' => Auth::id()]) }}">勤怠表</a>
            <a class="header__nav__link" href="/attendance">日付一覧</a>
            <a class="header__nav__link" href="/users">ユーザー一覧</a>
        </li>
        <li class="header__nav__item">
            <form action="/logout" method="post">
                @csrf
                <button class="header__nav__button">ログアウト</button>
            </form>
        </li>
        @endif
    </ul>
</nav>
@endsection

@section('content')
<div class="container">
    <!-- 日付変更ボタン -->
    <div class="date__navigation">
        <a class="subDay_button" href="{{ route('attendance', ['dates' =>$carbonDate->copy()->subDay()->format('Y-m-d')]) }}">＜</a>
        <!-- 現在の日付 -->
        <span>{{ $carbonDate->isoFormat('YYYY/MM/DD (ddd)') }}</span>
        @if ($carbonDate->isToday())
        <!-- 今日の日付の場合、次の日付へのリンクを非表示 -->
        @else
        <a class="addDay_button" href="{{ route('attendance', ['dates' => $carbonDate->copy()->addDay()->format('Y-m-d')]) }}">＞</a>
        @endif
    </div>

    <table class="attendance__table">
        <tr>
            <th>名前</th>
            <th>勤務開始</th>
            <th>勤務終了</th>
            <th>休憩時間</th>
            <th>勤務時間</th>
        </tr>

        <!-- ユーザーごとのループ -->
        @forelse ($users as $user)
        <tr>
            <!-- 名前 -->
            <td>{{ $user->name }}</td>
            <td>{{ $user->attendances->first()->formatted_clock_in }}</td>
            <td>
                @if ($user->attendances->first()->is_default_clock_out)
                <span class="clock-out-warning" data-tooltip="この勤務時間は未退勤のデフォルト設定です">
                    未退勤（23:59:59）
                </span>
                @else
                {{ $user->attendances->first()->formatted_clock_out }}
                @endif
            </td>
            <td>{{ $user->attendances->first()->formatted_break_duration }}</td>
            <td>{{ $user->attendances->first()->formatted_work_duration }}</td>
        </tr>
        @empty
        <tr class="no-data-row">
            <td colspan="5">データがありません</td>
        </tr>
        @endforelse
    </table>
    <!-- Bootstrapスタイルのページネーション -->
    <div class="d__flex__justify__content__center">
        {{ $users->appends(['dates' => $carbonDate->format('Y-m-d')])->links('pagination::bootstrap-4') }}
    </div>

</div>
@endsection