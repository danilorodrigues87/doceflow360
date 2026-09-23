<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Db\Connection;
use DoceFlow\Http\JsonResponse;

final class DashboardController extends ApiBase {

	public static function summary(): void {
		$tenantId = self::requireTenant();
		if ($tenantId === null) {
			return;
		}
		$hoje = date('Y-m-d');
		$pdo = Connection::app();

		$enc = $pdo->prepare('SELECT status, data_entrega, valor_total, valor_pago, forma_pagamento FROM df_encomendas WHERE tenant_id = :t');
		$enc->execute(['t' => $tenantId]);
		$rows = $enc->fetchAll();

		$faturado = 0.0;
		$recebido = 0.0;
		$pendente = 0.0;
		$emProducao = 0;
		$entregues = 0;
		$hojeCount = 0;
		$atrasados = 0;

		foreach ($rows as $o) {
			if (($o['status'] ?? '') === 'Cancelado') {
				continue;
			}
			$vt = (float)$o['valor_total'];
			$vp = (float)$o['valor_pago'];
			$faturado += $vt;
			$recebido += $vp;
			$pend = $vt - $vp;
			if ($pend > 0) {
				$pendente += $pend;
			}
			if ($o['status'] === 'Em produção') {
				$emProducao++;
			}
			if ($o['status'] === 'Entregue') {
				$entregues++;
			}
			$de = (string)($o['data_entrega'] ?? '');
			if ($de === $hoje && $o['status'] !== 'Cancelado') {
				$hojeCount++;
			}
			if ($de !== '' && $de < $hoje && $o['status'] !== 'Entregue' && $o['status'] !== 'Cancelado') {
				$atrasados++;
			}
		}

		$est = $pdo->prepare('SELECT COUNT(*) AS c FROM df_estoque WHERE tenant_id = :t AND quantidade <= estoque_minimo');
		$est->execute(['t' => $tenantId]);
		$estoqueBaixo = (int)($est->fetch()['c'] ?? 0);

		JsonResponse::send(200, [
			'ok' => true,
			'hoje' => $hoje,
			'data' => [
				'faturadoTotal' => round($faturado, 2),
				'recebidoTotal' => round($recebido, 2),
				'pendenteTotal' => round($pendente, 2),
				'emProducao' => $emProducao,
				'entregues' => $entregues,
				'entregasHoje' => $hojeCount,
				'atrasados' => $atrasados,
				'estoqueBaixo' => $estoqueBaixo,
			],
		]);
	}
}
