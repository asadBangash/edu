<?php

namespace App\Console\Commands;

use App\Services\EduxAssetSeeder;
use Illuminate\Console\Command;

class EduxSeedAssetsCommand extends Command
{
    protected $signature = 'edux:seed-assets
                            {--no-db : Only copy files, skip database updates}';

    protected $description = 'Seed school logos, login backgrounds, and placeholder images into uploads';

    public function handle(EduxAssetSeeder $seeder): int
    {
        if (! config('database.connections.mysql.database')) {
            $this->error('Configure DB_DATABASE in .env first.');

            return self::FAILURE;
        }

        try {
            $result = $seeder->seedAll(updateDatabase: ! $this->option('no-db'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Wrote/synced '.$result['files'].' upload files.');
        if ($result['db']) {
            $this->info('Updated sch_settings logo columns and login backgrounds.');
        }

        $this->line('Open '.url('schsettings/logo').' or reload the admin header to verify.');

        return self::SUCCESS;
    }
}