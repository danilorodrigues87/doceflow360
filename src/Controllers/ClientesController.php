<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Db\Connection;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Support\Input;
use DoceFlow\Support\Uuid;

final class ClientesController extends ApiBase {

	public static function index(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('SELECT * FROM df_clientes WHERE tenant_id = :t ORDER BY nome');
		$stmt->execute(['t' => $tenantId]);
		$rows = $stmt->fetchAll();
		JsonResponse::send(200, ['ok' => true, 'data' => array_map([self::class, 'mapOut'], $rows)]);
	}

	public static function create(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$b = Input::jsonBody();
		$id = trim((string)($b['id'] ?? '')) ?: Uuid::v4();
		$stmt = Connection::app()->prepare(
			'INSERT INTO df_clientes (id, tenant_id, nome, whatsapp, email, endereco, observacoes) VALUES (:id,:t,:nome,:wa,:email,:end,:obs)'
		);
		$stmt->execute([
			'id' => $id,
			't' => $tenantId,
			'nome' => trim((string)($b['nome'] ?? '')),
			'wa' => trim((string)($b['whatsapp'] ?? '')),
			'email' => trim((string)($b['email'] ?? '')),
			'end' => (string)($b['endereco'] ?? ''),
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
			'UPDATE df_clientes SET nome=:nome, whatsapp=:wa, email=:email, endereco=:end, observacoes=:obs WHERE id=:id AND tenant_id=:t'
		);
		$stmt->execute([
			'id' => $id,
			't' => $tenantId,
			'nome' => trim((string)($b['nome'] ?? '')),
			'wa' => trim((string)($b['whatsapp'] ?? '')),
			'email' => trim((string)($b['email'] ?? '')),
			'end' => (string)($b['endereco'] ?? ''),
			'obs' => (string)($b['observacoes'] ?? ''),
		]);
		JsonResponse::send(200, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function delete(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('DELETE FROM df_clientes WHERE id = :id AND tenant_id = :t');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		JsonResponse::send(200, ['ok' => true]);
	}

	/** @param array<string,mixed> $row */
	private static function mapOut(array $row): array {
		return [
			'id' => $row['id'],
			'nome' => $row['nome'],
			'whatsapp' => $row['whatsapp'],
			'email' => $row['email'],
			'endereco' => $row['endereco'] ?? '',
			'observacoes' => $row['observacoes'] ?? '',
		];
	}

	/** @return array<string,mixed>|null */
	private static function fetchOne(int $tenantId, string $id): ?array {
		$stmt = Connection::app()->prepare('SELECT * FROM df_clientes WHERE id = :id AND tenant_id = :t LIMIT 1');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		$row = $stmt->fetch();
		return is_array($row) ? self::mapOut($row) : null;
	}
}
