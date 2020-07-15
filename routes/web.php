<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// RUTAS DE PRUEBA
Route::get('/', function () {
    return '<h1>Hola mundo con Laravel</h1>';
});

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/pruebas/{nombre?}', function ($nombre = null) {
    $texto = '<h2>Texto desde una ruta</h2>';
    $texto .= 'Nombre: '.$nombre;
    
    return view('pruebas', array(
        'texto' => $texto
    ));
});

Route::get('/animales', 'PruebasController@index');

Route::get('/test-orm', 'PruebasController@testOrm');


// RUTAS DEL API

    /*
     * Métodos HTTP comunes
     *   GET: Conseguir datos o recursos
     *   POST: Guardar datos o recursos o hacer logica desde formulario
     *   PUT: Actualizar recursos o datos
     *   DELETE: Eliminar datos o recursos
     * 
     * Commando por terminal para ver todas las rutas creadas:
     *   php artisan route:list
     */

    // Rutas de prueba
    //Route::get('/usuario/pruebas', 'UserController@pruebas');
    //Route::get('/categoria/pruebas', 'CategoryController@pruebas');
    //Route::get('/entrada/pruebas', 'PostController@pruebas');

    // Middleware para controlar el acceso CORS
    Route::group(['middleware' => ['cors']], function () {

        // Rutas del controlador de usuarios
        Route::post('/api/register', 'UserController@register');
        Route::post('/api/login', 'UserController@login');
        Route::put('/api/user/update', 'UserController@update');
        Route::post('/api/user/upload','UserController@upload')->middleware('api.auth');
        Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
        Route::get('/api/user/detail/{id}', 'UserController@detail');

        // Rutas del controlador de categorías (Usando el resource se crean muchas rutas automáticamente)
        Route::resource('/api/category', 'CategoryController');

        // Rutas del controlador de entradas
        Route::resource('/api/post', 'PostController');
        Route::post('/api/post/upload','PostController@upload');
        Route::get('/api/post/image/{filename}', 'PostController@getImage');
        route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
        route::get('/api/post/user/{id}', 'PostController@getPostsByUser');
    
    });
    