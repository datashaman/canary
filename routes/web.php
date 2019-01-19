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

use Illuminate\Http\Request;

function validateRequest(Request $request, string $secret): bool
{
    $signature = 'sha1=' . hash_hmac('sha1', $request->getContent(), $secret);
    return hash_equals($signature, $request->header('X-Hub-Signature', ''));
}

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

$router->post('/', function (Request $request) {
    if (!validateRequest($request, env('APP_WEBHOOK_SECRET'))) {
        return response('FORBIDDEN', 403);
    }

    if (
        $request->header('X-GitHub-Event') == 'issue_comment'
        && $request->input('action') == 'created'
        && starts_with($request->input('comment.body'), 'please build')
        && DB::table('requests')->insert(
            [
                'user' => $request->input('sender.login'),
                'pull_request' => $request->input('issue.html_url'),
            ]
        )
    ) {
        return 'OK';
    }

    return '';
});
