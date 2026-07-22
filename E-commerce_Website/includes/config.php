<?php
declare(strict_types=1);

$configPath = (string)(getenv('ECOMMERCE_CONFIG_PATH') ?: 'C:/xampp/secure/ecommerce-config.php');

if (!is_file($configPath)) {
	http_response_code(500);
	exit('Configuration file not found. Set ECOMMERCE_CONFIG_PATH correctly.');
}

require_once $configPath;

// Local fallback toggle: set ESEWA_DEMO_MODE=true to simulate successful callbacks.
if (!defined('ESEWA_DEMO_MODE')) {
	$demoEnv = getenv('ESEWA_DEMO_MODE');

	if ($demoEnv === false || $demoEnv === '') {
		define('ESEWA_DEMO_MODE', false);
	} else {
		define('ESEWA_DEMO_MODE', filter_var($demoEnv, FILTER_VALIDATE_BOOLEAN));
	}
}
