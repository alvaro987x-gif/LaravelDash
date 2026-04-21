<?php

use App\Http\Controllers\UsersController;

Route::post('/login', [UsersController::class, 'loginApi']);
Route::post('/register', [UsersController::class, 'registerApi']);
Route::get('/users', [UsersController::class, 'UsersApi']);
Route::get('/presos', [UsersController::class, 'PresosApi']);
Route::get('/presosAltoRiesgo', [UsersController::class, 'PresosAltoRiesgoApi']);
Route::get('/presosProfugos', [UsersController::class, 'PresosProfugosApi']);
Route::get('/presosDashboard', [UsersController::class, 'PresosDashboardApi']);
Route::get('/usuariosDashboard', [UsersController::class, 'UsuariosDashboardApi']);
Route::post('/editarPreso', [UsersController::class, 'EditarPresoApi']);
Route::post('/añadirPreso', [UsersController::class, 'AñadirPresoApi']);
Route::post('/updateProfile', [UsersController::class, 'EditarPerfilApi']);
Route::post('/updatePassword', [UsersController::class, 'CambiarContraseñaApi']);