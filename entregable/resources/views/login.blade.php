<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h2>Iniciar Sesión</h2>

@if(session('error'))
    <p style="color:red;">
        {{ session('error') }}
    </p>
@endif

<form method="POST" action="/login">

    @if(isset($error))
        <p style="color:red">{{ $error }}</p>
    @endif

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif
    
    @csrf

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Ingresar</button>
</form>

<br>

<a href="{{ route('google.login') }}">
    <button style="padding:10px; background:#4285F4; color:white; border:none;">
        Iniciar sesión con Google
    </button>
</a>

<br><br>

<a href="/register">¿No tienes cuenta? Regístrate</
</body>
</html>