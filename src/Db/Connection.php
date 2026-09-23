<?php

declare(strict_types=1);

namespace DoceFlow\Db;

use DoceFlow\Config\Env;
use PDO;

final class Connection {

	private static ?PDO $pdo = null;
	private static ?PDO $xd360 = null;

	public static function app(): PDO {
		if (self::$pdo instanceof PDO) {
			return self::$pdo;
		}
		self::$pdo = self::make(
			Env::get('DB_HOST', '127.0.0.1'),
			Env::get('DB_NAME', 'doceflow'),
			Env::get('DB_USER', 'root'),
			Env::get('DB_PASS', ''),
			Env::get('DB_CHARSET', 'utf8mb4')
		);
		return self::$pdo;
	}

	public static function xd360(): PDO {
		if (self::$xd360 instanceof PDO) {
			return self::$xd360;
		}
		self::$xd360 = self::make(
			Env::get('XD360_DB_HOST', Env::get('DB_HOST', '127.0.0.1')),
			Env::get('XD360_DB_NAME', 'xd360'),
			Env::get('XD360_DB_USER', Env::get('DB_USER', 'root')),
			Env::get('XD360_DB_PASS', Env::get('DB_PASS', '')),
			'utf8mb4'
		);
		return self::$xd360;
	}

	private static function make(string $host, string $db, string $user, string $pass, string $charset): PDO {
		$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
		$pdo = new PDO($dsn, $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
		return $pdo;
	}
}
