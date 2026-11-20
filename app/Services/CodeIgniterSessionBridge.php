<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * Write Laravel session payloads into CodeIgniter file sessions (ci_session cookie).
 */
class CodeIgniterSessionBridge
{
    private function sessionDirectory(): string
    {
        $sessionPath = storage_path('framework/ci-sessions');
        if (! is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }

        return $sessionPath;
    }

    private function encodePayload(array $data): string
    {
        $payload = '';
        foreach ($data as $key => $value) {
            $payload .= $key.'|'.serialize($value);
        }

        return $payload;
    }

    private function writeSessionFile(string $sessionId, array $data): void
    {
        $file = $this->sessionDirectory().DIRECTORY_SEPARATOR.'ci_session'.$sessionId;
        file_put_contents($file, $this->encodePayload($data), LOCK_EX);
        $_COOKIE['ci_session'] = $sessionId;
    }

    public function handoffStaffSession(array $admin): string
    {
        $sessionId = $this->resolveSessionId();
        $this->writeSessionFile($sessionId, [
            'admin' => $admin,
            '__ci_last_regenerate' => time(),
        ]);

        return $sessionId;
    }

    public function handoffStudentSession(array $student): string
    {
        $sessionId = $this->resolveSessionId();
        $payload = [
            'student' => $student,
            '__ci_last_regenerate' => time(),
        ];
        if (Session::has('current_class')) {
            $payload['current_class'] = Session::get('current_class');
        }
        $this->writeSessionFile($sessionId, $payload);

        return $sessionId;
    }

    private function resolveSessionId(): string
    {
        $existing = $_COOKIE['ci_session'] ?? '';
        if ($existing !== '' && preg_match('/^[a-zA-Z0-9,-]{20,128}$/', $existing)) {
            return $existing;
        }

        return bin2hex(random_bytes(20));
    }

    public function destroyCiSession(): void
    {
        $sessionId = $_COOKIE['ci_session'] ?? '';
        if ($sessionId === '') {
            return;
        }

        $file = $this->sessionDirectory().DIRECTORY_SEPARATOR.'ci_session'.$sessionId;
        if (is_file($file)) {
            unlink($file);
        }
    }
}