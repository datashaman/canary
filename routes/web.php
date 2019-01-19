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

use Swop\GitHubWebHook\Security\SignatureValidator;

$validator = new SignatureValidator();

$router->get('/', function () {
    $all = DB::table('requests')
        ->orderBy('created_at', 'desc')
        ->get();

    $averages = [];
    $intervals = [5, 25, 75];

    foreach ($intervals as $interval) {
        $count = DB::table('requests')
            ->where('created_at', '>=', DB::raw('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . $interval . ' minute)'))
            ->count();
        $averages[$interval] = round($count / $interval, 2);
    }

    return [
        'averages' => $averages,
        'all' => $all,
    ];
});

$router->post('/', function (Illuminate\Http\Request $request) use ($validator) {
    if ($validator->validate($request, env('APP_WEBHOOK_SECRET'))) {
        $inserted = DB::table('requests')->insert(
            $request->only('user', 'pull_request')
        );

        return $inserted ? 'OK' : 'ERR';
    } else {
        return response('FORBIDDEN', 403);
    }
});
