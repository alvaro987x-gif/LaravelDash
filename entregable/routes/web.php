<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('/login', [UsersController::class, 'login']);
Route::post('/register', [UsersController::class, 'register']);
Route::get('/dashboard', [UsersController::class, 'Dashboard']);
Route::post('/logout', [UsersController::class, 'Logout'])->middleware('auth');
Route::get('/presos', [UsersController::class, 'ListarPresos'])->middleware('auth');
Route::get('/presos/{id}', [UsersController::class, 'VerPreso'])->middleware('auth');
Route::get('/usuarios', [UsersController::class, 'ListarUsuarios'])->middleware('auth');
Route::post('/updatePassword', [UsersController::class, 'CambiarContraseña'])->middleware('auth');
Route::post('/updateProfile', [UsersController::class, 'EditarPerfil'])->middleware('auth');
Route::post('/añadirPreso', [UsersController::class, 'AñadirPreso'])->middleware('auth');
Route::post('/editarPreso', [UsersController::class, 'EditarPreso'])->middleware('auth');