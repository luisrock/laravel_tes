<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Mensagem de contato</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="margin: 0 0 16px;">Nova mensagem de contato</h2>

    <p style="margin: 0 0 8px;"><strong>Nome:</strong> {{ $name }}</p>
    <p style="margin: 0 0 8px;"><strong>E-mail:</strong> {{ $email }}</p>
    <p style="margin: 0 0 8px;"><strong>Assunto:</strong> {{ $contactSubject }}</p>

    <hr style="border: 0; border-top: 1px solid #cbd5e1; margin: 16px 0;">

    <p style="margin: 0 0 8px;"><strong>Mensagem:</strong></p>
    <div style="white-space: pre-line;">{{ $body }}</div>
</body>
</html>
