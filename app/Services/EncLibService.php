<?php

namespace App\Services;

class EncLibService
{
    private string $pubKey = 'ss@pubkey';

    private string $pvtKey = 'ss@pvtkey';

    public function passHashVerify(string $password, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        if (password_get_info($hash)['algo'] !== 0) {
            return password_verify($password, $hash);
        }

        return hash_equals($hash, $password);
    }

    public function passHashEncrypt(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}