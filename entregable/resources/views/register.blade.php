<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>

<h2>Registro de Usuario</h2>

<form method="POST" action="/register" enctype="multipart/form-data">

    @if ($errors->any())
        <div style="color:red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @csrf

    <label>Nombre:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Subir su Avatar:</label><br>
    <input type="file" name="avatar"><br><br>

    <button type="submit">Registrarse</button>
</form>

<br>

<a href="/login">Volver al login</a>

</body>
</html>