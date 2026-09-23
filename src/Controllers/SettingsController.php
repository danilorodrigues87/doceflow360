<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Db\Connection;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Support\Input;

final class SettingsController extends ApiBase {

	public static function get(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		JsonResponse::send(200, ['ok' => true, 'data' => self::load($tenantId)]);
	}

	public static function patch(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$b = Input::jsonBody();
		$theme = trim((string)($b['theme'] ?? $b['tema'] ?? 'rosa'));
		$prefs = isset($b['prefs']) && is_array($b['prefs']) ? json_encode($b['prefs'], JSON_UNESCAPED_UNICODE) : null;
		$pdo = Connection::app();
		$stmt = $pdo->prepare(
			'INSERT INTO df_tenant_settings (tenant_id, theme, prefs_json) VALUES (:t,:theme,:prefs)
			 ON DUPLICATE KEY UPDATE theme = VALUES(theme), prefs_json = COALESCE(VALUES(prefs_json), prefs_json)'
		);
		$stmt->execute(['t' => $tenantId, 'theme' => $theme, 'prefs' => $prefs]);
		JsonResponse::send(200, ['ok' => true, 'data' => self::load($tenantId)]);
	}

	/** @return array<string,mixed> */
	private static function load(int $tenantId): array {
		$stmt = Connection::app()->prepare('SELECT theme, prefs_json FROM df_tenant_settings WHERE tenant_id = :t LIMIT 1');
		$stmt->execute(['t' => $tenantId]);
		$row = $stmt->fetch();
		if (!is_array($row)) {
			return ['theme' => 'rosa', 'prefs' => new \stdClass()];
		}
		$prefs = [];
		if (!empty($row['prefs_json'])) {
			$decoded = json_decode((string)$row['prefs_json'], true);
			$prefs = is_array($decoded) ? $decoded : [];
		}
		return ['theme' => $row['theme'] ?? 'rosa', 'prefs' => $prefs];
	}
}
