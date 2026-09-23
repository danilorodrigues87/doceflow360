<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Db\Connection;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Support\Input;
use DoceFlow\Support\Uuid;

final class EncomendasController extends ApiBase {

	public static function index(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('SELECT * FROM df_encomendas WHERE tenant_id = :t ORDER BY data_entrega DESC, criado_em DESC');
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
		self::upsert($tenantId, $id, $b, true);
		JsonResponse::send(201, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function update(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		self::upsert($tenantId, $id, Input::jsonBody(), false);
		JsonResponse::send(200, ['ok' => true, 'data' => self::fetchOne($tenantId, $id)]);
	}

	public static function delete(string $id): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$stmt = Connection::app()->prepare('DELETE FROM df_encomendas WHERE id = :id AND tenant_id = :t');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		JsonResponse::send(200, ['ok' => true]);
	}

	/** @param array<string,mixed> $b */
	private static function upsert(int $tenantId, string $id, array $b, bool $insert): void {
		$params = [
			'id' => $id,
			't' => $tenantId,
			'cliente_id' => (string)($b['clienteId'] ?? $b['cliente_id'] ?? ''),
			'produto_id' => (string)($b['produtoId'] ?? $b['produto_id'] ?? ''),
			'tema' => (string)($b['tema'] ?? ''),
			'massa' => (string)($b['massa'] ?? ''),
			'recheio' => (string)($b['recheio'] ?? ''),
			'tamanho_peso' => (string)($b['tamanhoPeso'] ?? $b['tamanho_peso'] ?? ''),
			'quantidade' => (int)($b['quantidade'] ?? 1),
			'data_pedido' => self::dateOrNull($b['dataPedido'] ?? $b['data_pedido'] ?? null),
			'data_entrega' => self::dateOrNull($b['dataEntrega'] ?? $b['data_entrega'] ?? null),
			'horario_entrega' => (string)($b['horarioEntrega'] ?? $b['horario_entrega'] ?? ''),
			'valor_total' => (float)($b['valorTotal'] ?? $b['valor_total'] ?? 0),
			'valor_pago' => (float)($b['valorPago'] ?? $b['valor_pago'] ?? 0),
			'forma_pagamento' => (string)($b['formaPagamento'] ?? $b['forma_pagamento'] ?? ''),
			'status' => (string)($b['status'] ?? 'Orçamento'),
		];
		if ($insert) {
			$sql = 'INSERT INTO df_encomendas (id, tenant_id, cliente_id, produto_id, tema, massa, recheio, tamanho_peso, quantidade, data_pedido, data_entrega, horario_entrega, valor_total, valor_pago, forma_pagamento, status)
				VALUES (:id,:t,:cliente_id,:produto_id,:tema,:massa,:recheio,:tamanho_peso,:quantidade,:data_pedido,:data_entrega,:horario_entrega,:valor_total,:valor_pago,:forma_pagamento,:status)';
		} else {
			$sql = 'UPDATE df_encomendas SET cliente_id=:cliente_id, produto_id=:produto_id, tema=:tema, massa=:massa, recheio=:recheio, tamanho_peso=:tamanho_peso, quantidade=:quantidade, data_pedido=:data_pedido, data_entrega=:data_entrega, horario_entrega=:horario_entrega, valor_total=:valor_total, valor_pago=:valor_pago, forma_pagamento=:forma_pagamento, status=:status WHERE id=:id AND tenant_id=:t';
		}
		Connection::app()->prepare($sql)->execute($params);
	}

	private static function dateOrNull(mixed $v): ?string {
		$s = trim((string)$v);
		return $s !== '' ? $s : null;
	}

	/** @param array<string,mixed> $row */
	private static function mapOut(array $row): array {
		return [
			'id' => $row['id'],
			'clienteId' => $row['cliente_id'],
			'produtoId' => $row['produto_id'],
			'tema' => $row['tema'],
			'massa' => $row['massa'],
			'recheio' => $row['recheio'],
			'tamanhoPeso' => $row['tamanho_peso'],
			'quantidade' => (int)$row['quantidade'],
			'dataPedido' => $row['data_pedido'],
			'dataEntrega' => $row['data_entrega'],
			'horarioEntrega' => $row['horario_entrega'],
			'valorTotal' => (float)$row['valor_total'],
			'valorPago' => (float)$row['valor_pago'],
			'formaPagamento' => $row['forma_pagamento'],
			'status' => $row['status'],
		];
	}

	/** @return array<string,mixed>|null */
	private static function fetchOne(int $tenantId, string $id): ?array {
		$stmt = Connection::app()->prepare('SELECT * FROM df_encomendas WHERE id = :id AND tenant_id = :t LIMIT 1');
		$stmt->execute(['id' => $id, 't' => $tenantId]);
		$row = $stmt->fetch();
		return is_array($row) ? self::mapOut($row) : null;
	}
}
