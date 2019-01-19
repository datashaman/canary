<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    return DB::table('requests')->get();
});

$router->post('/', function (Illuminate\Http\Request $request) {
    $inserted = DB::table('requests')->insert($request->only('user', 'pull_request'));

    return $inserted ? 'OK' : 'ERR';
});
