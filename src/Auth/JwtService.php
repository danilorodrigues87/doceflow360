<?php

declare(strict_types=1);

namespace DoceFlow\Auth;

use DoceFlow\Config\Env;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtService {

	public static function key(): string {
		$k = Env::get('JWT_KEY', '');
		return $k !== '' ? $k : 'change-me';
	}

	/** HS256 no php-jwt v7 exige >= 256 bits; derivamos 32 bytes do .env. */
	private static function signingKey(): string {
		return hash('sha256', self::key(), true);
	}

	/** @param array<string,mixed> $extra */
	public static function encode(int $userId, int $tenantId, array $extra = [], int $ttlSeconds = 28800): string {
		$now = time();
		$payload = array_merge([
			'sub' => $userId,
			'tenant_id' => $tenantId,
			'product' => 'doceflow',
			'iat' => $now,
			'exp' => $now + $ttlSeconds,
		], $extra);
		return JWT::encode($payload, self::signingKey(), 'HS256');
	}

	/** @return array<string,mixed>|null */
	public static function decode(string $token): ?array {
		try {
			$decoded = JWT::decode($token, new Key(self::signingKey(), 'HS256'));
			return (array)$decoded;
		} catch (\Throwable) {
			return null;
		}
	}
}
