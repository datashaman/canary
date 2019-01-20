<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class Sample extends Command
{
    /**
     * @var string
     */
    protected $signature = 'sample {interval=1 : Minutes to sample.}';

    /**
     * @var string
     */
    protected $description = 'Sample events.';

    public function handle()
    {
        $interval = (int) $this->argument('interval');

        $count = DB::table('events')
            ->where(
                'created_at',
                '>=',
                DB::raw("DATE_SUB(CURRENT_TIMESTAMP, INTERVAL $interval minute)")
            )
            ->count();

        DB::table('samples')->insert(compact('count', 'interval'));
    }
}
