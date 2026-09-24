<?php

declare(strict_types=1);

namespace DoceFlow\Services;

use DoceFlow\Config\Env;

final class Xd360LaunchClient {

	/**
	 * Troca launch token no XD360.
	 *
	 * @return array<string,mixed> sempre com chave 'ok' (bool); se false, 'message' explica o motivo
	 */
	public static function exchangeLaunchToken(string $plainToken): array {
		$plainToken = trim($plainToken);
		if ($plainToken === '') {
			return ['ok' => false, 'message' => 'Token de launch vazio.'];
		}

		$base = rtrim(Env::get('XD360_API_URL', ''), '/');
		if ($base === '') {
			return ['ok' => false, 'message' => 'XD360_API_URL não configurado no .env do DoceFlow.'];
		}

		$url = $base . '/api/v1/produtos/launch/exchange';
		$secret = trim((string)Env::get('PRODUCT_LAUNCH_SECRET', ''));
		$body = json_encode(['token' => $plainToken], JSON_UNESCAPED_UNICODE);

		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
		];
		if ($secret !== '') {
			$headers[] = 'X-Product-Launch-Secret: ' . $secret;
		}

		$raw = self::httpPost($url, $body, $headers);
		if ($raw === null) {
			return [
				'ok' => false,
				'message' => 'Não foi possível contactar o painel (XD360). Verifique XD360_API_URL, SSL e allow_url_fopen/cURL no servidor.',
			];
		}

		$data = json_decode($raw, true);
		if (!is_array($data)) {
			return [
				'ok' => false,
				'message' => 'Resposta inválida do painel (não é JSON). Confira se a URL aponta para app.xd360.com.br.',
			];
		}

		if (empty($data['ok'])) {
			$msg = (string)($data['erro'] ?? $data['message'] ?? 'Token inválido ou expirado.');
			return ['ok' => false, 'message' => $msg];
		}

		return $data;
	}

	/** @param list<string> $headers */
	private static function httpPost(string $url, string $body, array $headers): ?string {
		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			curl_setopt_array($ch, [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => true,
			]);
			$raw = curl_exec($ch);
			$err = curl_error($ch);
			$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if ($raw === false || $raw === '') {
				error_log('[DoceFlow] launch exchange curl failed: ' . $err . ' HTTP ' . $code);
				return null;
			}
			return (string)$raw;
		}

		$ctx = stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => implode("\r\n", $headers),
				'content' => $body,
				'timeout' => 20,
				'ignore_errors' => true,
			],
			'ssl' => [
				'verify_peer' => true,
				'verify_peer_name' => true,
			],
		]);
		$raw = @file_get_contents($url, false, $ctx);
		if ($raw === false || $raw === '') {
			error_log('[DoceFlow] launch exchange file_get_contents failed: ' . $url);
			return null;
		}
		return $raw;
	}
}
