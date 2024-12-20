@extends('layouts.app')

@section('css')
<!-- Flatpickrのスタイル（見た目） -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css?ver=1.0">
<!-- 月選択用の特別なスタイル -->
<link rel="stylesheet" href="https://npmcdn.com/flatpickr/dist/plugins/monthSelect/style.css">
<link rel="stylesheet" href="{{ asset('css/user/show.css') }}">
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
    <form method="GET" action="{{ route('user.show', $user->id) }}">
        <div class="month-picker-wrapper">
            <label for="monthPicker">勤怠月を選択してください</label>
            <input type="text" id="monthPicker" name="month" placeholder="勤怠月の選択は、ここをクリックしてください" onchange="this.form.submit()">
        </div>
    </form>
    <table class="user__attendance__table">
        <h2>{{ $user->name }}さんの勤怠表</h2>
        <thead>
            <tr>
                <th>日付</th>
                <th>勤務開始</th>
                <th>勤務終了</th>
                <th>休憩時間</th>
                <th>勤務時間</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceData as $date => $attendance)
            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->isoFormat('YYYY/MM/DD (ddd)') }}</td>
                <td>{{ $attendance->formatted_clock_in }}</td>
                <td>@if ($attendance->is_default_clock_out)
                    <span class="clock-out-warning" data-tooltip="この勤務時間は未退勤のデフォルト設定です">未退勤（23:59:59）</span>
                    @else
                    {{ $attendance->formatted_clock_out }}
                    @endif
                </td>
                <td>{{ $attendance->formatted_break_duration }}</td>
                <td>{{ $attendance->formatted_work_duration }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<!-- Flatpickr本体 -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr?ver=1.0"></script>
<!-- Flatpickrの「月を選ぶための道具」 -->
<script src="https://npmcdn.com/flatpickr/dist/plugins/monthSelect/index.js"></script>
<!-- 日本語ロケール -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#monthPicker", {
            locale: "ja", // 日本語にする
            plugins: [
                new monthSelectPlugin({ // 月選択の特別な設定
                    shorthand: true, // 月を短く（例: Jan, Feb）
                    dateFormat: "Y-m", // 年と月だけ表示
                    altFormat: "Y年F月", // 見た目のフォーマット（例: 2024年11月）
                })
            ],
            disable: [{
                    from: "1900-01",
                    to: "2023-12"
                }, // 2024年1月以前を無効化
                {
                    from: "2024-12",
                    to: "9999-12"
                } // 2024年12月以降を無効化
            ]
        });
    });
</script>
@endsection