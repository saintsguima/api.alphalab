<?php
namespace App\Services;

use App\Config\Env;

final class Signature
{
    public static function validate(?string $sigHeader, string $raw): bool
    {
        $secret = Env::get('WEBHOOK_SECRET', '');
        if (! $secret) {
            return true;
        }
        // sem validação
        if (! $sigHeader) {
            return false;
        }

        $calc = hash_hmac('sha256', $raw, $secret);
        return hash_equals($calc, $sigHeader);
    }
}
