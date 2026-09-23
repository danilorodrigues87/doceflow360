<?php

declare(strict_types=1);

namespace DoceFlow\Http;

use DoceFlow\Auth\RequestAuth;
use DoceFlow\Tenant\TenantContext;

final class TenantGuard {

	public static function assertTenantMatchesJwt(): bool {
		$claims = RequestAuth::requireAuth();
		if ($claims === null) {
			return false;
		}
		$jwtTenant = (int)$claims['tenant_id'];
		if (!TenantContext::isTenantHost()) {
			return true;
		}
		$hostTenant = TenantContext::getTenantId();
		if ($hostTenant === null) {
			return true;
		}
		return $hostTenant === $jwtTenant;
	}

	public static function effectiveTenantId(): int {
		$id = RequestAuth::tenantIdFromClaims();
		if ($id > 0) {
			return $id;
		}
		return (int)(TenantContext::getTenantId() ?? 0);
	}
}
