<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', static function () {
    return view('welcome');
});

Route::get( '/welcome', static function () {
    return phpinfo();
});

Route::get( '/pruebas/{nombre?}', static function ($nombre = null) {
    $texto = "texto desde la ruta: ".$nombre;
    return "<h2>$texto</h2>";
});
