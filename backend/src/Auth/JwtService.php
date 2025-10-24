<?php

namespace Cheqlist\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

class JwtService
{
    public function __construct(
        private readonly string $secret,
        private readonly string $algorithm = 'HS256'
    ) {
    }

    public function generateToken(array $claims, int $ttlSeconds = 3600): string
    {
        $now = time();
        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ]);

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function validateToken(string $token): array
    {
        if (trim($token) === '') {
            throw new InvalidArgumentException('Token cannot be empty');
        }

        $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));

        return (array) $decoded;
    }
}
