@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/index.css') }}">
@endsection

@section('header__nav')
<nav>
    <ul class="header__nav">
        @if (Auth::check())
        <li class="header__nav__item">
            <a class="header__nav__link" href="/">ホーム</a>
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
    <h2>ユーザー一覧</h2>
    <table class="user__table">
        <thead>
            <tr>
                <th>No.</th>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>登録日</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td>
                    <a href="{{ route('user.show', $user->id) }}" class="user_attendance_btn">勤怠表</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ページネーション --}}
    <div class="d__flex__justify__content__center">
        {{ $users->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection