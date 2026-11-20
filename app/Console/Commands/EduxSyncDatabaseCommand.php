<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

class EduxSyncDatabaseCommand extends Command
{
    protected $signature = 'edux:sync-database {--fresh : Drop and recreate edu_x from sms}';

    protected $description = 'Clone schema and data from the read-only CodeIgniter sms database into edu_x';

    public function handle(): int
    {
        $source = config('database.connections.sms_source.database', 'sms');
        $target = config('database.connections.mysql.database', 'edu_x');
        $mysql = $this->mysqlBinary();

        if ($this->option('fresh')) {
            DB::statement("DROP DATABASE IF EXISTS `{$target}`");
            DB::statement("CREATE DATABASE `{$target}` CHARACTER SET utf8 COLLATE utf8_general_ci");
        }

        $this->info("Cloning {$source} -> {$target} ...");

        $result = Process::run([
            $mysql['mysqldump'],
            '-u'.$mysql['user'],
            ...($mysql['password'] !== '' ? ['-p'.$mysql['password']] : []),
            $source,
        ], function (string $type, string $output) use ($mysql, $target) {
            if ($type === 'err') {
                return;
            }
            Process::input($output)->run([
                $mysql['mysql'],
                '-u'.$mysql['user'],
                ...($mysql['password'] !== '' ? ['-p'.$mysql['password']] : []),
                $target,
            ]);
        });

        if (! $result->successful()) {
            $this->error($result->errorOutput());

            return self::FAILURE;
        }

        $tables = DB::selectOne('SELECT COUNT(*) as c FROM information_schema.tables WHERE table_schema = ?', [$target]);
        $this->info("Done. Tables in {$target}: {$tables->c}");

        return self::SUCCESS;
    }

    private function mysqlBinary(): array
    {
        $base = env('LARAGON_MYSQL', 'D:/laragon/bin/mysql/mysql-8.4.3-winx64/bin');

        return [
            'mysql' => $base.'/mysql.exe',
            'mysqldump' => $base.'/mysqldump.exe',
            'user' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];
    }
}