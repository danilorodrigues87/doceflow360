<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

use DoceFlow\Controllers\AuthController;
use DoceFlow\Controllers\ClientesController;
use DoceFlow\Controllers\DashboardController;
use DoceFlow\Controllers\EncomendasController;
use DoceFlow\Controllers\EstoqueController;
use DoceFlow\Controllers\MetaController;
use DoceFlow\Controllers\ProdutosController;
use DoceFlow\Controllers\SettingsController;
use DoceFlow\Http\JsonResponse;
use DoceFlow\Tenant\TenantContext;
use DoceFlow\Tenant\TenantResolver;

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
	$uri = substr($uri, strlen($scriptDir)) ?: '/';
}
// Front controller via index.php/api/... (XAMPP sem mod_rewrite)
if (str_contains($uri, '/index.php')) {
	$after = substr($uri, strpos($uri, '/index.php') + strlen('/index.php'));
	$uri = ($after === '' || $after === false) ? '/' : $after;
}
if (isset($_SERVER['PATH_INFO']) && is_string($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
	$uri = $_SERVER['PATH_INFO'];
}
if (isset($_GET['r']) && is_string($_GET['r']) && str_starts_with($_GET['r'], '/')) {
	$uri = $_GET['r'];
}
$uri = rtrim($uri, '/') ?: '/';

TenantResolver::bootstrapFromRequest();

if (TenantContext::mode() === 'unknown_subdominio') {
	http_response_code(404);
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="utf-8"><title>Cliente não encontrado</title></head>'
		.'<body style="font-family:sans-serif;text-align:center;padding:4rem"><h1>Cliente não encontrado</h1>'
		.'<p>Este subdomínio não está cadastrado na XD360.</p></body></html>';
	exit;
}

if ($method === 'GET' && preg_match('#^/((df-api|df-ui)\.js|app/(df-api|df-ui)\.js)$#', $uri)) {
	$jsName = str_contains($uri, 'df-ui') ? 'df-ui.js' : 'df-api.js';
	$js = dirname(__DIR__) . '/resources/app/' . $jsName;
	if (is_file($js)) {
		header('Content-Type: application/javascript; charset=utf-8');
		readfile($js);
		exit;
	}
}

if ($method === 'GET' && !empty($_GET['launch'])) {
	$boot = dirname(__DIR__) . '/resources/app/bootstrap.html';
	if (is_file($boot)) {
		header('Content-Type: text/html; charset=utf-8');
		readfile($boot);
		exit;
	}
}

if ($method === 'GET' && ($uri === '/app' || $uri === '/index.html' || ($uri === '/' && empty($_GET['launch'])))) {
	$appFile = dirname(__DIR__) . '/resources/app/index.html';
	if (!is_file($appFile)) {
		$appFile = dirname(__DIR__) . '/resources/app/doceflow-pro.html';
	}
	if (is_file($appFile)) {
		header('Content-Type: text/html; charset=utf-8');
		readfile($appFile);
		exit;
	}
}

$routes = [
	'GET' => [
		'/api/health' => [MetaController::class, 'health'],
		'/api/v1/meta' => [MetaController::class, 'meta'],
		'/api/v1/auth/me' => [AuthController::class, 'me'],
		'/api/v1/clientes' => [ClientesController::class, 'index'],
		'/api/v1/produtos' => [ProdutosController::class, 'index'],
		'/api/v1/encomendas' => [EncomendasController::class, 'index'],
		'/api/v1/estoque' => [EstoqueController::class, 'index'],
		'/api/v1/settings' => [SettingsController::class, 'get'],
		'/api/v1/dashboard/summary' => [DashboardController::class, 'summary'],
	],
	'POST' => [
		'/api/v1/auth/exchange' => [AuthController::class, 'exchange'],
		'/api/v1/clientes' => [ClientesController::class, 'create'],
		'/api/v1/produtos' => [ProdutosController::class, 'create'],
		'/api/v1/encomendas' => [EncomendasController::class, 'create'],
		'/api/v1/estoque' => [EstoqueController::class, 'create'],
	],
	'PATCH' => [
		'/api/v1/settings' => [SettingsController::class, 'patch'],
	],
];

if (preg_match('#^/api/v1/clientes/([^/]+)$#', $uri, $m)) {
	if ($method === 'PUT') {
		ClientesController::update($m[1]);
		exit;
	}
	if ($method === 'DELETE') {
		ClientesController::delete($m[1]);
		exit;
	}
}
if (preg_match('#^/api/v1/produtos/([^/]+)$#', $uri, $m)) {
	if ($method === 'PUT') {
		ProdutosController::update($m[1]);
		exit;
	}
	if ($method === 'DELETE') {
		ProdutosController::delete($m[1]);
		exit;
	}
}
if (preg_match('#^/api/v1/encomendas/([^/]+)$#', $uri, $m)) {
	if ($method === 'PUT') {
		EncomendasController::update($m[1]);
		exit;
	}
	if ($method === 'DELETE') {
		EncomendasController::delete($m[1]);
		exit;
	}
}
if (preg_match('#^/api/v1/estoque/([^/]+)/quantidade$#', $uri, $m) && $method === 'PATCH') {
	EstoqueController::patchQuantidade($m[1]);
	exit;
}
if (preg_match('#^/api/v1/estoque/([^/]+)$#', $uri, $m)) {
	if ($method === 'PUT') {
		EstoqueController::update($m[1]);
		exit;
	}
	if ($method === 'DELETE') {
		EstoqueController::delete($m[1]);
		exit;
	}
}

$handler = $routes[$method][$uri] ?? null;
if ($handler !== null) {
	[$class, $action] = $handler;
	$class::$action();
	exit;
}

JsonResponse::error('Rota não encontrada.', 404);
