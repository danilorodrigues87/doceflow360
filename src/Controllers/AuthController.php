<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Auth\JwtService;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Http\TenantGuard;
use DoceFlow\Services\Xd360LaunchClient;
use DoceFlow\Support\Input;
use DoceFlow\Tenant\TenantContext;

final class AuthController {

	public static function exchange(): void {
		$body = Input::jsonBody();
		$token = trim((string)($body['token'] ?? $_GET['launch'] ?? $_GET['token'] ?? ''));
		if ($token === '') {
			JsonResponse::error('Informe o token de launch.', 400);
			return;
		}

		$result = Xd360LaunchClient::exchangeLaunchToken($token);
		if (empty($result['ok'])) {
			JsonResponse::error((string)($result['message'] ?? 'Token inválido ou expirado.'), 401);
			return;
		}

		$tenantId = (int)($result['tenant_id'] ?? 0);
		$userId = (int)($result['user_id'] ?? 0);
		if ($tenantId <= 0 || $userId <= 0) {
			JsonResponse::error('Resposta inválida da plataforma.', 502);
			return;
		}

		if (TenantContext::isTenantHost()) {
			$hostTenant = TenantContext::getTenantId();
			if ($hostTenant !== null && $hostTenant !== $tenantId) {
				JsonResponse::error('Token não pertence a este cliente (host).', 403);
				return;
			}
		}

		$expiresIn = 8 * 3600;
		try {
			$access = JwtService::encode($userId, $tenantId, [
				'email' => (string)($result['email'] ?? ''),
				'nome' => (string)($result['nome'] ?? ''),
			], $expiresIn);
		} catch (\Throwable $e) {
			error_log('[DoceFlow auth.exchange] JWT: '.$e->getMessage());
			JsonResponse::error('Erro ao emitir sessão do app.', 500);
			return;
		}

		JsonResponse::send(200, [
			'ok' => true,
			'user' => [
				'id' => $userId,
				'nome' => (string)($result['nome'] ?? ''),
				'email' => (string)($result['email'] ?? ''),
				'tenant_id' => $tenantId,
			],
			'tokens' => [
				'accessToken' => $access,
				'expiresIn' => $expiresIn,
			],
		]);
	}

	public static function me(): void {
		if (!TenantGuard::assertTenantMatchesJwt()) {
			JsonResponse::error('Tenant do token não confere com o host.', 403);
			return;
		}
		$claims = \DoceFlow\Auth\RequestAuth::requireAuth();
		if ($claims === null) {
			JsonResponse::error('Não autenticado.', 401);
			return;
		}
		JsonResponse::send(200, [
			'ok' => true,
			'user' => [
				'id' => (int)$claims['sub'],
				'tenant_id' => (int)$claims['tenant_id'],
				'email' => (string)($claims['email'] ?? ''),
				'nome' => (string)($claims['nome'] ?? ''),
			],
			'tenant_host' => TenantContext::getSlug(),
		]);
	}
}
