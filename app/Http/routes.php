<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// header('Access-Control-Allow-Origin: *');

Route::get('/', function () {
    return view('welcome');
});

Route::post('usuario/edit', 'userCtrl@edit');

Route::group(['middleware' => ['cors']], function () {
    Route::post('register', 'userCtrl@register');
    Route::post('login', 'userCtrl@login');
    Route::post('publicar', 'userCtrl@publicar');

    Route::group(['middleware' => 'jwt-auth'], function () {
        Route::post('get_user', 'userCtrl@get_user_details');

        // Route::post('usuario/edit', 'userCtrl@edit');

        Route::post('favorito', 'userCtrl@favorito');

        Route::post('comentar', 'userCtrl@comentar');

        Route::post('comentario_d', 'userCtrl@comentario_d');

        Route::post('comentario_r', 'userCtrl@comentario_r');

        Route::post('comentario_rd', 'userCtrl@comentario_rd');

        Route::post('admin', 'userCtrl@admin');

        Route::post('add_cat', 'categoriasCtrl@add');

        Route::post('delete_cat', 'categoriasCtrl@delete');

        Route::post('edit_cat', 'categoriasCtrl@edit');
    });
});

Route::get('categorias', 'categoriasCtrl@get');

Route::get('estados', 'estadosCtrl@get');

Route::get('publicaciones', 'productoCtrl@get');

Route::get('filtros', 'productoCtrl@filtro');

Route::group(['middleware' => ['cors'], 'prefix' => 'publicacion'], function () {
    Route::post('add_foto', 'productoCtrl@subir_foto');

    Route::get('{name}', 'productoCtrl@show');

    Route::group(['middleware' => 'jwt-auth'], function () {
        Route::post('delete', 'productoCtrl@delete');

        Route::post('aprobar', 'productoCtrl@aprobar');
    });
});