@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
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
    <h1 class="large__heading">{{ Auth::user()->name }}さんお疲れ様です。</h1>

    <!-- 状態に基づくエラーメッセージ -->
    @switch($status)
    @case(0)
    <div class="alert__alert__info">
        勤務開始ボタンを押してください。
    </div>
    @break

    @case(1)
    <div class="alert__alert__info">
        勤務中です。
    </div>
    @break

    @case(2)
    <div class="alert__alert__danger">
        現在休憩中のため、勤務開始と勤務終了のボタンは押せません。
    </div>
    @break

    @case(3)
    <div class="alert__alert__danger">
        勤務終了済みのため、この操作を実行できません。
    </div>
    @break

    @endswitch

    <!-- 成功メッセージ -->
    @if (session('success'))
    <div class="alert__alert__success">
        {{ session('success') }}
    </div>
    @endif

    <form class="form__wrap" action="{{ route('work') }}" method="post">
        @csrf
        <div class="form__item">
            <button class="form__item-button" type="submit" name="action" value="clock_in" {{ $status == 0 ? '' : 'disabled' }}>勤務開始</button>
        </div>
        <div class="form__item">
            <button class="form__item-button" type="submit" name="action" value="clock_out" {{ $status == 1 ? '' : 'disabled' }}>勤務終了</button>
        </div>
        <div class="form__item">
            <button class="form__item-button" type="submit" name="action" value="break_start" {{ $status == 1 ? '' : 'disabled' }}>休憩開始</button>
        </div>
        <div class="form__item">
            <button class="form__item-button" type="submit" name="action" value="break_end" {{ $status == 2 ? '' : 'disabled' }}>休憩終了</button>
        </div>
    </form>
</div>
@endsection