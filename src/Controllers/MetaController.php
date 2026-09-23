<?php

declare(strict_types=1);

namespace DoceFlow\Controllers;

use DoceFlow\Http\JsonResponse;

final class MetaController {

	public static function meta(): void {
		JsonResponse::send(200, [
			'ok' => true,
			'hoje' => date('Y-m-d'),
			'product' => 'doceflow',
			'version' => '0.1.0',
		]);
	}

	public static function health(): void {
		JsonResponse::send(200, ['ok' => true, 'service' => 'doceflow']);
	}
}
