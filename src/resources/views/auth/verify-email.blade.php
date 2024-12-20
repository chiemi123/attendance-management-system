<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
</head>

<body>
    <h1>メール認証が必要です</h1>
    <p>登録したメールアドレスに認証リンクを送信しました。メールを確認し、リンクをクリックしてください。</p>
    <p>認証メールを再送信するには、以下のボタンをクリックしてください。</p>
    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit">認証メールを再送信</button>
    </form>
</body>

</html>