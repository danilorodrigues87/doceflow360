<?php
/**
 * Teste server-to-server (remova após usar).
 * Uso: defina LAUNCH_TOKEN no .env ou passe ?token=... na URL (só admin).
 */
declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

use DoceFlow\Services\Xd360LaunchClient;

header('Content-Type: text/plain; charset=utf-8');

$token = trim((string)($argv[1] ?? $_GET['token'] ?? ''));
if ($token === '') {
	echo "CLI: php scripts/testar-launch-exchange.php SEU_TOKEN_LAUNCH\n";
	exit(1);
}

$r = Xd360LaunchClient::exchangeLaunchToken($token);
echo json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
