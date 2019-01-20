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
use Illuminate\Support\Carbon;

function validateRequest(Request $request, string $secret): bool
{
    $signature = 'sha1=' . hash_hmac('sha1', $request->getContent(), $secret);
    return hash_equals($signature, $request->header('X-Hub-Signature', ''));
}

$router->get('/', function (Request $request) {
    return view('dashboard');
});

$router->get('/samples', function (Request $request) {
    $interval = $request->input('interval');

    $cols = [
        [
            'id' => 'created',
            'label' => 'Created',
            'type' => 'datetime',
        ],
        [
            'id' => 'count',
            'label' => 'Count',
            'type' => 'number',
        ],
    ];

    $rows = DB::table('samples')
        ->select('created_at', 'count')
        ->where('created_at', '>=', DB::raw('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ' . $interval . ' minute)'))
        ->orderBy('created_at')
        ->get()
        ->map(
            function ($sample) {
                $createdAt = Carbon::parse($sample->created_at);
                $month = $createdAt->month - 1;

                return [
                    'c' => [
                        [
                            'v' => "Date({$createdAt->year}, {$month}, {$createdAt->day}, {$createdAt->hour}, {$createdAt->minute}, {$createdAt->second})",
                            'f' => $createdAt->toDateTimeString(),
                        ],
                        [
                            'v' => $sample->count,
                        ],
                    ],
                ];
            }
        )
        ->all();

    return [
        'cols' => $cols,
        'rows' => $rows,
    ];
});

$router->post('/', function (Request $request) {
    if (!validateRequest($request, env('APP_WEBHOOK_SECRET'))) {
        return response('FORBIDDEN', 403);
    }

    $event = $request->header('X-GitHub-Event');
    $action = $request->input('action');

    $filters = include 'filters.php';

    DB::table('configs')
        ->where('event', 'like', "%$event%")
        ->when(
            $action,
            function ($q) use ($action) {
                return $q
                    ->where(
                        function ($q1) use ($action) {
                            $q1
                                ->where('action', 'like', "%$action%")
                                ->orWhereNull('action');
                        }
                    );
            }
        )
        ->get()
        ->filter(
            function ($config) use ($configs, $request) {
                if (isset($configs[$config['id']])) {
                    return $configs[$config['id']]($request);
                }
                return true;
            }
        )
        ->each(
            function ($config) use ($event, $request) {
                DB::table('events')->insert(
                    [
                        'config' => $config['id'],
                        'event' => $event,
                        'payload' => $request->all(),
                    ]
                );
            }
        );

    return 'OK';
});
