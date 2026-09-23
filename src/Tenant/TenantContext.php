<?php

declare(strict_types=1);

namespace DoceFlow\Tenant;

final class TenantContext {

	private static bool $booted = false;
	private static string $mode = 'platform';
	private static ?int $tenantId = null;
	private static ?string $slug = null;

	public static function boot(string $mode, ?int $tenantId = null, ?string $slug = null): void {
		self::$booted = true;
		self::$mode = $mode;
		self::$tenantId = $tenantId;
		self::$slug = $slug;
	}

	public static function isBooted(): bool {
		return self::$booted;
	}

	public static function mode(): string {
		return self::$mode;
	}

	public static function getTenantId(): ?int {
		return self::$tenantId;
	}

	public static function getSlug(): ?string {
		return self::$slug;
	}

	public static function isTenantHost(): bool {
		return self::$mode !== 'platform' && self::$tenantId !== null;
	}
}
