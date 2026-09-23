<?php

declare(strict_types=1);

namespace DoceFlow\Config;

final class Env {

	/** @var array<string,string> */
	private static array $vars = [];

	public static function load(string $root): void {
		$path = $root . '/.env';
		if (!is_file($path)) {
			return;
		}
		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($lines === false) {
			return;
		}
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '' || str_starts_with($line, '#')) {
				continue;
			}
			if (!str_contains($line, '=')) {
				continue;
			}
			[$k, $v] = explode('=', $line, 2);
			$k = trim($k);
			$v = trim($v);
			if ($v !== '' && (($v[0] === '"' && str_ends_with($v, '"')) || ($v[0] === "'" && str_ends_with($v, "'")))) {
				$v = substr($v, 1, -1);
			}
			self::$vars[$k] = $v;
			if (getenv($k) === false) {
				putenv("$k=$v");
			}
		}
	}

	public static function get(string $key, string $default = ''): string {
		if (isset(self::$vars[$key])) {
			return self::$vars[$key];
		}
		$v = getenv($key);
		return $v !== false ? (string)$v : $default;
	}
}
