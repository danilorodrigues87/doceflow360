<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Auth\RequestAuth;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Http\TenantGuard;

abstract class ApiBase {

	protected static function requireTenant(): ?int {
		if (!TenantGuard::assertTenantMatchesJwt()) {
			JsonResponse::error('Tenant do token não confere com o host.', 403);
			return null;
		}
		if (RequestAuth::requireAuth() === null) {
			JsonResponse::error('Não autenticado.', 401);
			return null;
		}
		$tid = TenantGuard::effectiveTenantId();
		if ($tid <= 0) {
			JsonResponse::error('Tenant inválido.', 403);
			return null;
		}
		return $tid;
	}
}
