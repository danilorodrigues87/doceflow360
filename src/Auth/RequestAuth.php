<?php

declare(strict_types=1);

namespace DoceFlow\Auth;

final class RequestAuth {

	private static ?array $claims = null;

	public static function bearerFromRequest(): ?string {
		$hdr = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
		if ($hdr === '' && function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			$hdr = (string)($headers['Authorization'] ?? $headers['authorization'] ?? '');
		}
		if (preg_match('/Bearer\s+(\S+)/i', $hdr, $m)) {
			return $m[1];
		}
		return null;
	}

	/** @return array<string,mixed>|null */
	public static function claims(): ?array {
		if (self::$claims !== null) {
			return self::$claims;
		}
		$token = self::bearerFromRequest();
		if ($token === null || $token === '') {
			return null;
		}
		self::$claims = JwtService::decode($token);
		return self::$claims;
	}

	public static function requireAuth(): ?array {
		$claims = self::claims();
		if ($claims === null || empty($claims['sub']) || empty($claims['tenant_id'])) {
			return null;
		}
		if (($claims['product'] ?? '') !== 'doceflow') {
			return null;
		}
		return $claims;
	}

	public static function tenantIdFromClaims(): int {
		$claims = self::requireAuth();
		return $claims ? (int)$claims['tenant_id'] : 0;
	}

	public static function userIdFromClaims(): int {
		$claims = self::requireAuth();
		return $claims ? (int)$claims['sub'] : 0;
	}
}
