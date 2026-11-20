<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EduxVerifyMigrationCommand extends Command
{
    protected $signature = 'edux:verify-data';

    protected $description = 'Compare row counts between sms (source) and edu_x (Laravel)';

    public function handle(): int
    {
        $target = config('database.connections.mysql.database');
        $source = config('database.connections.sms_source.database');

        $tables = DB::select(
            'SELECT table_name FROM information_schema.tables WHERE table_schema = ? ORDER BY table_name',
            [$target]
        );

        $mismatches = 0;
        foreach ($tables as $row) {
            $table = $row->TABLE_NAME ?? $row->table_name ?? null;
            if (! $table) {
                continue;
            }
            $targetCount = DB::table($table)->count();
            $sourceCount = DB::connection('sms_source')->table($table)->count();
            if ($targetCount !== $sourceCount) {
                $this->warn("{$table}: source={$sourceCount} target={$targetCount}");
                $mismatches++;
            }
        }

        if ($mismatches === 0) {
            $this->info('All table row counts match between '.$source.' and '.$target.'.');
        } else {
            $this->error("{$mismatches} table(s) differ. Run edux:sync-database --fresh if needed.");
        }

        return $mismatches === 0 ? self::SUCCESS : self::FAILURE;
    }
}