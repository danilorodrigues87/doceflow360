<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

DoceFlow\Config\Env::load(dirname(__DIR__));

ini_set('display_errors', '0');
error_reporting(E_ALL);

date_default_timezone_set(DoceFlow\Config\Env::get('TIMEZONE', 'America/Sao_Paulo'));
