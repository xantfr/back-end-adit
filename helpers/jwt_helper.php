<?php
class JWT {
    private static $secret = 'taskflow_secret_key_2024_!@#$%';
    private static $algo   = 'SHA256';

    public static function encode(array $payload): string {
        $header  = self::base64url(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = self::base64url(json_encode($payload));
        $sig     = self::base64url(hash_hmac(self::$algo, "$header.$payload", self::$secret, true));
        return "$header.$payload.$sig";
    }

    public static function decode(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $payload, $sig] = $parts;
        $expected = self::base64url(hash_hmac(self::$algo, "$header.$payload", self::$secret, true));
        if (!hash_equals($expected, $sig)) return null;
        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        if (!$data || (isset($data['exp']) && $data['exp'] < time())) return null;
        return $data;
    }

    private static function base64url(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
