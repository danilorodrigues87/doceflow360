<?php

declare(strict_types=1);

namespace DoceFlow\Http;

final class JsonResponse {

	public static function send(int $code, array $data): void {
		if (!headers_sent()) {
			http_response_code($code);
			header('Content-Type: application/json; charset=utf-8');
		}
		echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	public static function error(string $message, int $code = 400, array $extra = []): void {
		self::send($code, array_merge(['erro' => $message, 'message' => $message], $extra));
	}
}
