<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EduxSeedDashboardCommand extends Command
{
    protected $signature = 'edux:seed-dashboard';

    protected $description = 'Seed demo dashboard widgets (students, fees, attendance) into the Laravel edu_x database';

    public function handle(): int
    {
        $database = config('database.connections.mysql.database');
        if (! $database) {
            $this->error('Configure DB_DATABASE in .env first.');

            return self::FAILURE;
        }

        putenv('EDUX_SEED_DATABASE='.$database);

        $script = base_path('edux/temp/edux_dashboard_dummy_data.php');
        if (! is_file($script)) {
            $this->error('Missing '.$script);

            return self::FAILURE;
        }

        $this->info('Seeding dashboard demo data into '.$database.' ...');
        require $script;
        $this->info('Dashboard seed complete. Open '.url('admin/admin/dashboard'));

        return self::SUCCESS;
    }
}