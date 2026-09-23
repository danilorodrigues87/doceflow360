<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Db\Connection;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Support\Input;
use DoceFlow\Support\Uuid;

final class EstoqueController extends ApiBase {

	public static function index(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('SELECT * FROM df_estoque WHERE tenant_id = :t ORDER BY ingrediente');
		$stmt->execute(['t' => $tenantId]);
		JsonResponse::send(200, ['ok' => true, 'data' => array_map([self::class, 'mapOut'], $stmt->fetchAll())]);
	}

	public static function create(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$b = Input::jsonBody();
		$id = trim((string)($b['id'] ?? '')) ?: Uuid::v4();
		self::save($tenantId, $id, $b);
		JsonResponse::send(201, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function update(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		self::save($tenantId, $id, Input::jsonBody());
		JsonResponse::send(200, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function patchQuantidade(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$b = Input::jsonBody();
		$delta = (float)($b['delta'] ?? 0);
		$stmt = Connection::app()->prepare('UPDATE df_estoque SET quantidade = GREATEST(0, quantidade + :d) WHERE id = :id AND tenant_id = :t');
		$stmt->execute(['d' => $delta, 'id' => $id, 't' => $tenantId]);
		JsonResponse::send(200, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function delete(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('DELETE FROM df_estoque WHERE id = :id AND tenant_id = :t');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		JsonResponse::send(200, ['ok' => true]);
	}

	/** @param array<string,mixed> $b */
	private static function save(int $tenantId, string $id, array $b): void {
		$pdo = Connection::app();
		$exists = $pdo->prepare('SELECT id FROM df_estoque WHERE id = :id AND tenant_id = :t');
		$exists->execute(['id' => $id, 't' => $tenantId]);
		$params = [
			'id' => $id,
			't' => $tenantId,
			'ingrediente' => trim((string)($b['ingrediente'] ?? '')),
			'quantidade' => (float)($b['quantidade'] ?? 0),
			'unidade' => trim((string)($b['unidade'] ?? '')),
			'estoque_minimo' => (float)($b['estoqueMinimo'] ?? $b['estoque_minimo'] ?? 0),
			'fornecedor' => trim((string)($b['fornecedor'] ?? '')),
		];
		if ($exists->fetch()) {
			$sql = 'UPDATE df_estoque SET ingrediente=:ingrediente, quantidade=:quantidade, unidade=:unidade, estoque_minimo=:estoque_minimo, fornecedor=:fornecedor WHERE id=:id AND tenant_id=:t';
		} else {
			$sql = 'INSERT INTO df_estoque (id, tenant_id, ingrediente, quantidade, unidade, estoque_minimo, fornecedor) VALUES (:id,:t,:ingrediente,:quantidade,:unidade,:estoque_minimo,:fornecedor)';
		}
		$pdo->prepare($sql)->execute($params);
	}

	/** @param array<string,mixed> $row */
	private static function mapOut(array $row): array {
		return [
			'id' => $row['id'],
			'ingrediente' => $row['ingrediente'],
			'quantidade' => (float)$row['quantidade'],
			'unidade' => $row['unidade'],
			'estoqueMinimo' => (float)$row['estoque_minimo'],
			'fornecedor' => $row['fornecedor'],
		];
	}

	/** @return array<string,mixed>|null */
	private static function fetchOne(int $tenantId, string $id): ?array {
		$stmt = Connection::app()->prepare('SELECT * FROM df_estoque WHERE id = :id AND tenant_id = :t LIMIT 1');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		$row = $stmt->fetch();
		return is_array($row) ? self::mapOut($row) : null;
	}
}
