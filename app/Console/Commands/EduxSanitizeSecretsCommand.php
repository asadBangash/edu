<?php

namespace App\Console\Commands;

use App\Support\SmartSchoolPaths;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EduxSanitizeSecretsCommand extends Command
{
    protected $signature = 'edux:sanitize-secrets {--edux= : Path to edux root (default: codeigniter_path)}';

    protected $description = 'Remove demo API keys from edux/ before Git push (GitHub push protection).';

    public function handle(): int
    {
        $root = $this->option('edux') ?: SmartSchoolPaths::runtimeRoot();

        $firebase = $root.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'third_party'.DIRECTORY_SEPARATOR.'firebase_notification_key.json';
        if (is_file($firebase)) {
            File::delete($firebase);
            $this->info('Removed firebase_notification_key.json (keep your real copy locally only).');
        }

        $paymongo = $root.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'Paymongo.php';
        if (is_file($paymongo)) {
            $body = file_get_contents($paymongo);
            $body = preg_replace(
                '/setKeys\\([^)]+\\)/',
                "setKeys('', '')",
                $body,
            ) ?? $body;
            file_put_contents($paymongo, $body);
            $this->info('Scrubbed Paymongo.php demo keys.');
        }

        $lang = $root.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'Langconvert.php';
        if (is_file($lang)) {
            $body = file_get_contents($lang);
            $body = preg_replace(
                "/'key'\\s*=>\\s*'trnsl\\.[^']+'/",
                "'key' => (string) (getenv('YANDEX_TRANSLATE_API_KEY') ?: '')",
                $body,
            ) ?? $body;
            file_put_contents($lang, $body);
            $this->info('Scrubbed Langconvert.php Yandex keys.');
        }

        $this->line('Copy firebase_notification_key.json.example to firebase_notification_key.json on each server.');

        return self::SUCCESS;
    }
}
