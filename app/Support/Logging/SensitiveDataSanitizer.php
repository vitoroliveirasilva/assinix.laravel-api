<?php

namespace App\Support\Logging;

class SensitiveDataSanitizer
{
    private const REDACTED = '[REDACTED]';

    private const SENSITIVE_KEYS = [
        'authorization',
        'cookie',
        'set-cookie',
        'x-xsrf-token',
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'plain_text_token',
        'remember_token',
        'secret',
        'api_key',
        'apikey',
        'client_secret',
    ];

    public function sanitize(array $data): array
    {
        return $this->sanitizeArray($data);
    }

    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if ($this->isSensitiveKey($normalizedKey)) {
                $sanitized[$key] = self::REDACTED;

                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }
}