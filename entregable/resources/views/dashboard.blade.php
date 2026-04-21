<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

@php
use Illuminate\Support\Str;
@endphp

<div style="display:flex;">

    <div style="width:150px; border:1px solid black; padding:10px;">
        <h3>Menú</h3>
        <ul>

            @if(auth()->check() && auth()->user()->rol === 'sargento')
            <li>
                <a href="#" onclick="event.preventDefault();
                    var t = document.getElementById('tablaUsuarios');
                    t.style.display = (t.style.display === 'block') ? 'none' : 'block';
                ">
                    Usuarios
                </a>
            </li>
            @endif

            <li>
                <a href="#" onclick="event.preventDefault();
                    var t = document.getElementById('tablaPresos');
                    t.style.display = (t.style.display === 'block') ? 'none' : 'block';
                ">
                    Presos
                </a>
            </li>

            <li>
                <a href="#" onclick="event.preventDefault();
                    var p = document.getElementById('perfil');
                    p.style.display = (p.style.display === 'block') ? 'none' : 'block';
                ">
                    Perfil
                </a>
            </li>

            <li>
                <a href="#" onclick="event.preventDefault();
                    var f = document.getElementById('formAñadirPreso');
                    f.style.display = (f.style.display === 'block') ? 'none' : 'block';
                ">
                    Añadir preso
                </a>
            </li>

            <li>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit">Cerrar sesión</button>
                </form>
            </li>

        </ul>
    </div>

    <div style="padding:20px; flex:1;">

        <h2>Bienvenido al Dashboard</h2>

        @if($errors->any())
            <div style="color:red;">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <p style="color:red;">{{ session('error') }}</p>
        @endif

        @if(session('success'))
            <p style="color:green;">{{ session('success') }}</p>
        @endif

        <div style="display:flex; gap:20px; margin-top:20px;">
            <div style="border:1px solid black; padding:20px; width:150px; text-align:center;">
                <h4>Total presos</h4>
                <p>{{ $totalPresos }}</p>
            </div>

            <div style="border:1px solid black; padding:20px; width:150px; text-align:center;">
                <h4>Alto riesgo</h4>
                <p>{{ $totalPresosAltoRiesgo }}</p>
            </div>

            <div style="border:1px solid black; padding:20px; width:150px; text-align:center;">
                <h4>Prófugos</h4>
                <p>{{ $totalPresosProfugos }}</p>
            </div>
        </div>

        <div style="display:flex; gap:40px; margin-top:20px; align-items:flex-start;">

            <div style="width:300px;">
                <h3>Estado de presos</h3>
                <canvas id="graficoPresos" width="300" height="300"></canvas>
                <script>
                    var ctx1 = document.getElementById('graficoPresos').getContext('2d');
                    new Chart(ctx1, {
                        type: 'pie',
                        data: {
                            labels: ['Encarcelados', 'Prófugos'],
                            datasets: [{
                                data: [{{ $totalPresos - $totalPresosProfugos }}, {{ $totalPresosProfugos }}],
                                backgroundColor: ['#4CAF50', '#f44336']
                            }]
                        }
                    });
                </script>
            </div>

            <div style="width:350px;">
                <h3>Nivel de peligrosidad</h3>
                <canvas id="graficoNivel" width="350" height="300"></canvas>
                <script>
                    var ctx2 = document.getElementById('graficoNivel').getContext('2d');
                    new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: ['Bajo', 'Medio', 'Alto'],
                            datasets: [{
                                label: 'Cantidad de presos',
                                data: [{{ $totalPresosBajo }}, {{ $totalPresosMedio }}, {{ $totalPresosAltoRiesgo }}],
                                backgroundColor: ['#4CAF50', '#FF9800', '#f44336']
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                </script>
            </div>

        </div>

        @if(auth()->user()->rol === 'sargento')
        <div id="tablaUsuarios" style="display:none; margin-top:20px;">
            <h3>Lista de usuarios</h3>
            <table border="1">
                <tbody>
                    @foreach($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->id }}</td>
                        <td>{{ $usuario->name }}</td>
                        <td>{{ $usuario->email }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div id="tablaPresos" style="display:none; margin-top:20px;">
            <h3>Lista de presos</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>DNI</th>
                        <th>Peligrosidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($presos as $preso)
                    <tr>
                        <td>{{ $preso->id }}</td>
                        <td>{{ $preso->nombre }}</td>
                        <td>{{ $preso->apellido }}</td>
                        <td>{{ $preso->dni }}</td>
                        <td>{{ $preso->nivel_peligrosidad }}</td>
                        <td>{{ $preso->estado }}</td>
                        <td>
                            <a href="/presos/{{ $preso->id }}">Ver más</a>

                            <a href="#" onclick="event.preventDefault();
                                document.getElementById('edit_id').value = '{{ $preso->id }}';
                                document.getElementById('edit_nombre').value = '{{ addslashes($preso->nombre) }}';
                                document.getElementById('edit_apellido').value = '{{ addslashes($preso->apellido) }}';
                                document.getElementById('edit_dni').value = '{{ $preso->dni }}';
                                document.getElementById('edit_delito').value = '{{ addslashes($preso->delito) }}';
                                document.getElementById('edit_condena').value = '{{ $preso->condena }}';
                                document.getElementById('edit_estado').value = '{{ $preso->estado }}';
                                document.getElementById('edit_nivel').value = '{{ $preso->nivel_peligrosidad }}';
                                document.getElementById('edit_fecha_ingreso').value = '{{ $preso->fecha_ingreso }}';
                                document.getElementById('edit_fecha_salida').value = '{{ $preso->fecha_salida }}';
                                document.getElementById('edit_observaciones').value = '{{ addslashes($preso->observaciones ?? '') }}';
                                document.getElementById('info_nombre').innerText = '{{ addslashes($preso->nombre) }}';
                                document.getElementById('info_apellido').innerText = '{{ addslashes($preso->apellido) }}';
                                document.getElementById('info_dni').innerText = '{{ $preso->dni }}';
                                document.getElementById('info_delito').innerText = '{{ addslashes($preso->delito) }}';
                                document.getElementById('info_condena').innerText = '{{ $preso->condena }}';
                                document.getElementById('info_estado').innerText = '{{ $preso->estado }}';
                                document.getElementById('info_nivel').innerText = '{{ $preso->nivel_peligrosidad }}';
                                document.getElementById('info_fecha_ingreso').innerText = '{{ $preso->fecha_ingreso }}';
                                document.getElementById('info_fecha_salida').innerText = '{{ $preso->fecha_salida }}';
                                var f = document.getElementById('editarPresoForm');
                                f.style.display = 'block';
                                f.scrollIntoView({ behavior: 'smooth' });
                            ">
                                Editar
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($presoSeleccionado)
        <div style="margin-top:20px; border:2px solid black; padding:15px;">
            <h3>Detalle del preso</h3>

            <img src="{{ asset('storage/' . $presoSeleccionado->imagen) }}" width="120">

            <p>{{ $presoSeleccionado->nombre }}</p>
            <p>{{ $presoSeleccionado->apellido }}</p>
            <p>{{ $presoSeleccionado->dni }}</p>
            <p>{{ $presoSeleccionado->delito }}</p>
            <p>{{ $presoSeleccionado->condena }}</p>
            <p>{{ $presoSeleccionado->estado }}</p>
            <p>{{ $presoSeleccionado->nivel_peligrosidad }}</p>

            <a href="/dashboard">Volver</a>
        </div>
        @endif

        <div id="formAñadirPreso" style="display:none; margin-top:20px;">
            <h3>Añadir preso</h3>

            <form action="/añadirPreso" method="POST" enctype="multipart/form-data">
                @csrf

                <label>Nombre:</label><br>
                <input type="text" name="nombre"><br><br>
                <label>Apellido:</label><br>
                <input type="text" name="apellido"><br><br>
                <label>DNI:</label><br>
                <input type="text" name="dni"><br><br>
                <label>Delito:</label><br>
                <input type="text" name="delito"><br><br>
                <label>Condena (años):</label><br>
                <input type="number" name="condena"><br><br>

                <select name="estado">
                    <option value="Encarcelado">Encarcelado</option>
                    <option value="Profugo">Profugo</option>
                </select><br><br>

                <select name="nivel_peligrosidad">
                    <option value="Bajo">Bajo</option>
                    <option value="Medio">Medio</option>
                    <option value="Alto">Alto</option>
                </select><br><br>

                <input type="file" name="imagen"><br><br>
                <label>Fecha de ingreso:</label><br>
                <input type="date" name="fecha_ingreso"><br><br>
                <label>Fecha de salida:</label><br>
                <input type="date" name="fecha_salida"><br><br>
                <label>Observaciones:</label><br>
                <textarea name="observaciones"></textarea><br><br>

                <button type="submit">Guardar preso</button>
            </form>
        </div>

        <div id="editarPresoForm" style="display:none; margin-top:20px;">
            <h3>Editar preso</h3>

            <div style="display:flex; gap:40px;">

                <div style="border:1px solid black; padding:15px; min-width:200px;">
                    <h4>Datos actuales</h4>
                    <p><strong>Nombre:</strong> <span id="info_nombre"></span></p>
                    <p><strong>Apellido:</strong> <span id="info_apellido"></span></p>
                    <p><strong>DNI:</strong> <span id="info_dni"></span></p>
                    <p><strong>Delito:</strong> <span id="info_delito"></span></p>
                    <p><strong>Condena:</strong> <span id="info_condena"></span> años</p>
                    <p><strong>Estado:</strong> <span id="info_estado"></span></p>
                    <p><strong>Nivel:</strong> <span id="info_nivel"></span></p>
                    <p><strong>Fecha ingreso:</strong> <span id="info_fecha_ingreso"></span></p>
                    <p><strong>Fecha salida:</strong> <span id="info_fecha_salida"></span></p>
                </div>

                <div>
                    <form action="/editarPreso" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="id" id="edit_id">

                        <label>Nombre:</label><br>
                        <input type="text" name="nombre" id="edit_nombre"><br><br>

                        <label>Apellido:</label><br>
                        <input type="text" name="apellido" id="edit_apellido"><br><br>

                        <label>DNI:</label><br>
                        <input type="text" name="dni" id="edit_dni"><br><br>

                        <label>Delito:</label><br>
                        <input type="text" name="delito" id="edit_delito"><br><br>

                        <label>Condena (años):</label><br>
                        <input type="number" name="condena" id="edit_condena"><br><br>

                        <label>Estado:</label><br>
                        <select name="estado" id="edit_estado">
                            <option value="Encarcelado">Encarcelado</option>
                            <option value="Profugo">Profugo</option>
                        </select><br><br>

                        <label>Nivel de peligrosidad:</label><br>
                        <select name="nivel_peligrosidad" id="edit_nivel">
                            <option value="Bajo">Bajo</option>
                            <option value="Medio">Medio</option>
                            <option value="Alto">Alto</option>
                        </select><br><br>

                        <label>Imagen (opcional):</label><br>
                        <input type="file" name="imagen"><br><br>

                        <label>Fecha de ingreso:</label><br>
                        <input type="date" name="fecha_ingreso" id="edit_fecha_ingreso"><br><br>

                        <label>Fecha de salida:</label><br>
                        <input type="date" name="fecha_salida" id="edit_fecha_salida"><br><br>

                        <label>Observaciones:</label><br>
                        <textarea name="observaciones" id="edit_observaciones"></textarea><br><br>

                        <button type="submit">Guardar cambios</button>
                        <button type="button" onclick="document.getElementById('editarPresoForm').style.display='none'">Cancelar</button>
                    </form>
                </div>

            </div>
        </div>

        <div id="perfil" style="display:none; margin-top:20px; border:1px solid black; padding:10px;">
            <h3>Perfil</h3>

            @if(auth()->user()->avatar)
                @if(Str::startsWith(auth()->user()->avatar, 'http'))
                    <img src="{{ auth()->user()->avatar }}" width="100">
                @else
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" width="100">
                @endif
            @else
                <img src="{{ asset('images/default-avatar.png') }}" width="100">
            @endif

            <p>Nombre: {{ auth()->user()->name }}</p>
            <p>Email: {{ auth()->user()->email }}</p>

            <button onclick="
                var f = document.getElementById('formCambiarContrasena');
                f.style.display = (f.style.display === 'block') ? 'none' : 'block';
            ">
                Cambiar contraseña
            </button>

            <div id="formCambiarContrasena" style="display:none;">
                <form action="/updatePassword" method="POST">
                    @csrf
                    <input type="password" name="current_password" placeholder="Actual"><br><br>
                    <input type="password" name="new_password" placeholder="Nueva"><br><br>
                    <input type="password" name="new_password_confirmation" placeholder="Confirmar"><br><br>
                    <button type="submit">Guardar</button>
                </form>
            </div>

            <button onclick="
                var f = document.getElementById('formEditarPerfil');
                f.style.display = (f.style.display === 'block') ? 'none' : 'block';
            ">
                Editar perfil
            </button>

            <div id="formEditarPerfil" style="display:none;">
                <form action="/updateProfile" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label>Nombre:</label><br>
                    <input type="text" name="name" value="{{ auth()->user()->name }}"><br><br>
                    <input type="file" name="avatar"><br><br>
                    <button type="submit">Guardar</button>
                </form>
            </div>

        </div>

    </div>

</div>

</body>
</html>