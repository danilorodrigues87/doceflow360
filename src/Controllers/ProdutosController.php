<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Db\Connection;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Support\Input;
use DoceFlow\Support\Uuid;

final class ProdutosController extends ApiBase {

	public static function index(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('SELECT * FROM df_produtos WHERE tenant_id = :t ORDER BY nome');
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
		$stmt = Connection::app()->prepare(
			'INSERT INTO df_produtos (id, tenant_id, nome, categoria, preco_base, custo_estimado, observacoes) VALUES (:id,:t,:nome,:cat,:pb,:ce,:obs)'
		);
		$stmt->execute([
			'id' => $id,
			't' => $tenantId,
			'nome' => trim((string)($b['nome'] ?? '')),
			'cat' => trim((string)($b['categoria'] ?? '')),
			'pb' => (float)($b['precoBase'] ?? $b['preco_base'] ?? 0),
			'ce' => (float)($b['custoEstimado'] ?? $b['custo_estimado'] ?? 0),
			'obs' => (string)($b['observacoes'] ?? ''),
		]);
		JsonResponse::send(201, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function update(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$b = Input::jsonBody();
		$stmt = Connection::app()->prepare(
			'UPDATE df_produtos SET nome=:nome, categoria=:cat, preco_base=:pb, custo_estimado=:ce, observacoes=:obs WHERE id=:id AND tenant_id=:t'
		);
		$stmt->execute([
			'id' => $id,
			't' => $tenantId,
			'nome' => trim((string)($b['nome'] ?? '')),
			'cat' => trim((string)($b['categoria'] ?? '')),
			'pb' => (float)($b['precoBase'] ?? $b['preco_base'] ?? 0),
			'ce' => (float)($b['custoEstimado'] ?? $b['custo_estimado'] ?? 0),
			'obs' => (string)($b['observacoes'] ?? ''),
		]);
		JsonResponse::send(200, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function delete(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('DELETE FROM df_produtos WHERE id = :id AND tenant_id = :t');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		JsonResponse::send(200, ['ok' => true]);
	}

	/** @param array<string,mixed> $row */
	private static function mapOut(array $row): array {
		return [
			'id' => $row['id'],
			'nome' => $row['nome'],
			'categoria' => $row['categoria'],
			'precoBase' => (float)$row['preco_base'],
			'custoEstimado' => (float)$row['custo_estimado'],
			'observacoes' => $row['observacoes'] ?? '',
		];
	}

	/** @return array<string,mixed>|null */
	private static function fetchOne(int $tenantId, string $id): ?array {
		$stmt = Connection::app()->prepare('SELECT * FROM df_produtos WHERE id = :id AND tenant_id = :t LIMIT 1');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		$row = $stmt->fetch();
		return is_array($row) ? self::mapOut($row) : null;
	}
}
