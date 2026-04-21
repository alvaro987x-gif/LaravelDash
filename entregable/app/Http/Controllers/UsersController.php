<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{

    public function index()
    {
        return view('welcome');
    }

    public function login()
    {
        $email = request('email');
        $password = request('password');

        if ($email === '' || $password === '') {
            return view('login', ['error' => 'Por favor, complete todos los campos.']);
        }

        if (Auth::attempt(['email' => $email, 'password' => $password])) {

            $user = Auth::user();

            if (!$user->rol) {
                Auth::logout();
                return view('login', ['error' => 'Usuario no autorizado.']);
            }

            return redirect('dashboard');

        } else {
            return view('login', ['error' => 'Credenciales inválidas, si no tiene cuenta, regístrese.']);
        }
    }

    public function register()
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $totalUsuarios = User::count();

        $user = new User();
        $user->name = request('name');
        $user->email = request('email');
        $user->password = Hash::make(request('password'));
        $user->avatar = request()->hasFile('avatar') ? request()->file('avatar')->store('avatars', 'public') : 'images/avatar.png';
        $user->rol = $totalUsuarios === 0 ? 'sargento' : 'policia';
        $user->save();

        return redirect('/login')->with('success', 'Registro exitoso.');
    }

    public function registerApi(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $totalUsuarios = User::count();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $request->hasFile('avatar') ? $request->file('avatar')->store('avatars', 'public') : null,
            'rol' => $totalUsuarios === 0 ? 'sargento' : 'policia'
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente'
        ]);
    }

    public function Dashboard($presoSeleccionado = null)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Por favor, inicie sesión para acceder al dashboard.');
        }

        $totalPresos = DB::table('presos')->count();
        $totalPresosAltoRiesgo = DB::table('presos')->where('nivel_peligrosidad', 'Alto')->count();
        $totalPresosMedio = DB::table('presos')->where('nivel_peligrosidad', 'Medio')->count();
        $totalPresosBajo = DB::table('presos')->where('nivel_peligrosidad', 'Bajo')->count();
        $totalPresosProfugos = DB::table('presos')->where('estado', 'Profugo')->count();
        $listarPresos = DB::table('presos')->get();
        $listarUsuarios = DB::table('users')->get();
        $updatePassword = DB::table('users')->where('id', Auth::id())->first();
        $updateProfile = DB::table('users')->where('id', Auth::id())->first();
        $updatePreso = DB::table('presos')->where('id', Auth::id())->first();

        return view('dashboard', [
            'totalPresos' => $totalPresos,
            'totalPresosAltoRiesgo' => $totalPresosAltoRiesgo,
            'totalPresosMedio' => $totalPresosMedio,
            'totalPresosBajo' => $totalPresosBajo,
            'totalPresosProfugos' => $totalPresosProfugos,
            'presos' => $listarPresos,
            'usuarios' => $listarUsuarios,
            'updatePassword' => $updatePassword,
            'updateProfile' => $updateProfile,
            'updatePreso' => $updatePreso,
            'presoSeleccionado' => $presoSeleccionado
        ]);
    }

    public function AñadirPreso(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'apellido' => 'required',
            'dni' => 'required|unique:presos',
            'delito' => 'required',
            'condena' => 'required|integer',
            'estado' => 'required|in:Encarcelado,Profugo',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'required|date|after_or_equal:fecha_ingreso',
            'observaciones' => 'nullable|string',
            'nivel_peligrosidad' => 'required|in:Bajo,Medio,Alto',
        ]);

        $imagenPath = $request->hasFile('imagen')
            ? $request->file('imagen')->store('presos', 'public')
            : 'images/avatar.png';

        DB::table('presos')->insert([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'dni' => $request->dni,
            'delito' => $request->delito,
            'condena' => $request->condena,
            'estado' => $request->estado,
            'nivel_peligrosidad' => $request->nivel_peligrosidad,
            'imagen' => $imagenPath,
            'fecha_ingreso' => $request->fecha_ingreso,
            'fecha_salida' => $request->fecha_salida,
            'observaciones' => $request->observaciones,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/dashboard')->with('success', 'Preso añadido correctamente.');
    }

    public function EditarPreso(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:presos,id',
            'nombre' => 'required',
            'apellido' => 'required',
            'dni' => 'required|unique:presos,dni,' . $request->id,
            'delito' => 'required',
            'condena' => 'required|integer',
            'estado' => 'required|in:Encarcelado,Profugo',
            'imagen' => 'nullable|image',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'required|date|after_or_equal:fecha_ingreso',
            'observaciones' => 'nullable|string',
            'nivel_peligrosidad' => 'required|in:Bajo,Medio,Alto',
        ]);

        $preso = DB::table('presos')->where('id', $request->id)->first();

        $imagenPath = $request->hasFile('imagen')
            ? $request->file('imagen')->store('presos', 'public')
            : $preso->imagen;

        DB::table('presos')->where('id', $request->id)->update([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'dni' => $request->dni,
            'delito' => $request->delito,
            'condena' => $request->condena,
            'estado' => $request->estado,
            'nivel_peligrosidad' => $request->nivel_peligrosidad,
            'imagen' => $imagenPath,
            'fecha_ingreso' => $request->fecha_ingreso,
            'fecha_salida' => $request->fecha_salida,
            'observaciones' => $request->observaciones,
            'updated_at' => now(),
        ]);

        return redirect('/dashboard')->with('success', 'Preso actualizado correctamente.');
    }

    public function VerPreso($id)
    {
        $preso = DB::table('presos')->where('id', $id)->first();

        if (!$preso) {
            return redirect('/dashboard')->with('error', 'Preso no encontrado.');
        }

        return $this->Dashboard($preso);
    }

    public function Logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Sesión cerrada correctamente.');
    }

    public function EditarPresoForm($id)
    {
        $preso = DB::table('presos')->where('id', $id)->first();

        if (!$preso) {
            return redirect('/dashboard')->with('error', 'Preso no encontrado.');
        }

        return view('editarPreso', ['preso' => $preso]);
    }

    public function CambiarContraseña(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect('/dashboard')->with('success', 'Contraseña actualizada correctamente.');
    }

    public function EditarPerfil(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : $user->avatar;

        DB::table('users')->where('id', $user->id)->update([
            'name' => $request->name,
            'avatar' => $avatarPath,
            'updated_at' => now(),
        ]);

        return redirect('/dashboard')->with('success', 'Perfil actualizado correctamente.');
    }

    public function UsersApi()
    {
        $users = DB::table('users')->get();
        return response()->json($users);
    }

    public function PresosApi()
    {
        $presos = DB::table('presos')->get();
        return response()->json($presos);
    }

    public function PresosAltoRiesgoApi()
    {
        $presos = DB::table('presos')->where('nivel_peligrosidad', 'Alto')->get();
        return response()->json($presos);
    }

    public function PresosProfugosApi()
    {
        $presos = DB::table('presos')->where('estado', 'Profugo')->get();
        return response()->json($presos);
    }

    public function PresosDashboardApi()
    {
        return response()->json([
            'totalPresos'          => DB::table('presos')->count(),
            'totalPresosAltoRiesgo'=> DB::table('presos')->where('nivel_peligrosidad', 'Alto')->count(),
            'totalPresosMedio'     => DB::table('presos')->where('nivel_peligrosidad', 'Medio')->count(),
            'totalPresosBajo'      => DB::table('presos')->where('nivel_peligrosidad', 'Bajo')->count(),
            'totalPresosProfugos'  => DB::table('presos')->where('estado', 'Profugo')->count(),
        ]);
    }

    public function UsuariosDashboardApi()
    {
        return response()->json([
            'totalUsuarios'  => DB::table('users')->count(),
            'totalSargentos' => DB::table('users')->where('rol', 'sargento')->count(),
            'totalPolicias'  => DB::table('users')->where('rol', 'policia')->count(),
        ]);
    }

    public function loginApi(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Login correcto',
                'user' => Auth::user()
            ]);
        }

        return response()->json([
            'message' => 'Credenciales inválidas'
        ], 401);
    }

    public function EditarPresoApi(Request $request)
{
    $preso = DB::table('presos')->where('id', $request->id)->first();

    $imagenPath = $request->hasFile('imagen')
        ? $request->file('imagen')->store('presos', 'public')
        : $preso->imagen;

    DB::table('presos')->where('id', $request->id)->update([
        'nombre' => $request->nombre,
        'apellido' => $request->apellido,
        'dni' => $request->dni,
        'delito' => $request->delito,
        'condena' => $request->condena,
        'estado' => $request->estado,
        'nivel_peligrosidad' => $request->nivel_peligrosidad,
        'imagen' => $imagenPath,
        'fecha_ingreso' => $request->fecha_ingreso,
        'fecha_salida' => $request->fecha_salida,
        'observaciones' => $request->observaciones,
        'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Preso actualizado correctamente']);
}

    public function AñadirPresoApi(Request $request)
    {
        try {
            $existe = DB::table('presos')->where('dni', $request->dni)->first();
            if ($existe) {
                return response()->json(['message' => 'El DNI ya existe en la base de datos'], 422);
            }

            $imagenPath = $request->hasFile('imagen')
                ? $request->file('imagen')->store('presos', 'public')
                : 'images/avatar.png';

            DB::table('presos')->insert([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'dni' => $request->dni,
                'delito' => $request->delito,
                'condena' => $request->condena,
                'estado' => $request->estado ?? 'Encarcelado',
                'nivel_peligrosidad' => $request->nivel_peligrosidad ?? 'Bajo',
                'imagen' => $imagenPath,
                'fecha_ingreso' => $request->fecha_ingreso,
                'fecha_salida' => $request->fecha_salida ?: null,
                'observaciones' => $request->observaciones ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Preso añadido correctamente']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    public function EditarPerfilApi(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'No autenticado'], 401);
        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : $user->avatar;
        DB::table('users')->where('id', $user->id)->update([
            'name' => $request->name,
            'avatar' => $avatarPath,
            'updated_at' => now(),
        ]);
        return response()->json(['message' => 'Perfil actualizado correctamente']);
    }

    public function CambiarContraseñaApi(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'No autenticado'], 401);
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Contraseña actual incorrecta'], 400);
        }
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->new_password),
        ]);
        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

}

