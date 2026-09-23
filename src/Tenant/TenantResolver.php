<?php

declare(strict_types=1);

namespace DoceFlow\Tenant;

use DoceFlow\Config\Env;
use DoceFlow\Db\Connection;
use PDO;

/** Espelha regras básicas do XD360 TenantHostHelper (subdomínio + dev slug). */
final class TenantResolver {

	public static function bootstrapFromRequest(): void {
		if (TenantContext::isBooted()) {
			return;
		}

		$host = self::hostSemPorta();
		if ($host === '') {
			TenantContext::boot('platform');
			return;
		}

		$masterHost = strtolower(trim(Env::get('XD360_MASTER_HOST', 'localhost')));
		if ($masterHost !== '' && $host === $masterHost) {
			TenantContext::boot('platform');
			return;
		}

		$appHost = strtolower(trim(Env::get('DOCEFLOW_APP_HOST', '')));
		if ($appHost !== '' && $host === $appHost) {
			TenantContext::boot('platform');
			return;
		}

		$devSlug = trim(Env::get('DOCEFLOW_DEV_TENANT_SLUG', ''));
		if ($devSlug !== '' && self::isLocalHost($host)) {
			$row = self::fetchTenantBySlug($devSlug);
			if ($row !== null) {
				TenantContext::boot('subdominio_dev', (int)$row['id'], $devSlug);
				return;
			}
		}

		$base = strtolower(trim(Env::get('XD360_BASE_DOMAIN', '')));
		if ($base !== '' && str_ends_with($host, '.'.$base)) {
			$sub = substr($host, 0, -(strlen($base) + 1));
			$slug = self::extrairSlugSubdominio($sub);
			if ($slug !== null) {
				$row = self::fetchTenantBySlug($slug);
				if ($row !== null) {
					TenantContext::boot('subdominio', (int)$row['id'], $slug);
					return;
				}
				TenantContext::boot('unknown_subdominio');
				return;
			}
		}

		// Domínio custom: fora do escopo atual (ver plano XD360).

		TenantContext::boot('platform');
	}

	/** @return array{id:int,slug:?string}|null */
	private static function fetchTenantBySlug(string $slug): ?array {
		$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug)) ?? '';
		if ($slug === '') {
			return null;
		}
		try {
			$pdo = Connection::xd360();
			$stmt = $pdo->prepare('SELECT id, slug FROM clientes_assinantes WHERE slug = :slug AND ativo = :ativo LIMIT 1');
			$stmt->execute(['slug' => $slug, 'ativo' => 's']);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return is_array($row) ? ['id' => (int)$row['id'], 'slug' => $row['slug'] ?? null] : null;
		} catch (\Throwable) {
			return null;
		}
	}

	/** @return array{id:int,slug:?string}|null */
	private static function fetchTenantByCustomDomain(string $host): ?array {
		$host = preg_replace('/^www\./', '', strtolower($host)) ?? $host;
		try {
			$pdo = Connection::xd360();
			foreach (['dominio_custom', 'dominio_painel'] as $col) {
				if (!self::columnExists($pdo, $col)) {
					continue;
				}
				$stmt = $pdo->prepare("SELECT id, slug FROM clientes_assinantes WHERE {$col} = :host AND ativo = 's' LIMIT 1");
				$stmt->execute(['host' => $host]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if (is_array($row)) {
					return ['id' => (int)$row['id'], 'slug' => $row['slug'] ?? null];
				}
			}
		} catch (\Throwable) {
			return null;
		}
		return null;
	}

	private static function columnExists(PDO $pdo, string $col): bool {
		static $cache = [];
		if (isset($cache[$col])) {
			return $cache[$col];
		}
		try {
			$stmt = $pdo->query("SHOW COLUMNS FROM clientes_assinantes LIKE '".$col."'");
			$cache[$col] = $stmt && $stmt->rowCount() > 0;
		} catch (\Throwable) {
			$cache[$col] = false;
		}
		return $cache[$col];
	}

	private static function hostSemPorta(): string {
		$host = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
		return preg_replace('/:\d+$/', '', $host) ?? $host;
	}

	private static function isLocalHost(string $host): bool {
		return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
	}

	private static function extrairSlugSubdominio(string $sub): ?string {
		$sub = strtolower(trim($sub));
		if ($sub === '' || str_contains($sub, '.')) {
			return null;
		}
		if (!preg_match('/^[a-z0-9](?:[a-z0-9-]{0,78}[a-z0-9])?$/', $sub)) {
			return null;
		}
		return $sub;
	}
}
