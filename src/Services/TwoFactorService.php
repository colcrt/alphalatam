<?php

declare(strict_types=1);

namespace App\Services;

class TwoFactorService
{
    public function generarSecret(): string
    {
        return bin2hex(random_bytes(20));
    }

    public function obtenerQrCodeUrl(string $email, string $secret): string
    {
        $encoded = urlencode('PlataformaDocumental:' . $email);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/' . $encoded . '?secret=' . $secret;
    }

    public function generarRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    public function verificarCodigo(string $secret, string $code): bool
    {
        $timeSlice = floor(time() / 30);
        for ($offset = -1; $offset <= 1; $offset++) {
            $calculatedCode = $this->generateCodeFromSecret($secret, $timeSlice + $offset);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }

    public function encriptarSecret(string $secret): string
    {
        return password_hash($secret, PASSWORD_DEFAULT);
    }

    public function encriptarRecoveryCodes(array $codes): string
    {
        return password_hash(implode(',', $codes), PASSWORD_DEFAULT);
    }

    private function generateCodeFromSecret(string $secret, int $timeSlice): string
    {
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $secret, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashPart = substr($hm, $offset, 4);
        $value = unpack('N', $hashPart)[1];
        $value = $value & 0x7FFFFFFF;
        $code = $value % 1000000;
        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }
}
