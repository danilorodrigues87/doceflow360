<?php

declare(strict_types=1);

namespace DoceFlow\Services;

use DoceFlow\Config\Env;

final class Xd360LaunchClient {

	/** @return array<string,mixed>|null */
	public static function exchangeLaunchToken(string $plainToken): ?array {
		$plainToken = trim($plainToken);
		if ($plainToken === '') {
			return null;
		}

		$base = rtrim(Env::get('XD360_API_URL', ''), '/');
		if ($base === '') {
			return null;
		}

		$url = $base.'/api/v1/produtos/launch/exchange';
		$secret = Env::get('PRODUCT_LAUNCH_SECRET', '');

		$body = json_encode(['token' => $plainToken], JSON_UNESCAPED_UNICODE);
		$ctx = stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => implode("\r\n", [
					'Content-Type: application/json',
					'Accept: application/json',
					$secret !== '' ? 'X-Product-Launch-Secret: '.$secret : '',
				]),
				'content' => $body,
				'timeout' => 15,
				'ignore_errors' => true,
			],
		]);

		$raw = @file_get_contents($url, false, $ctx);
		if ($raw === false || $raw === '') {
			return null;
		}
		$data = json_decode($raw, true);
		return is_array($data) && !empty($data['ok']) ? $data : null;
	}
}
