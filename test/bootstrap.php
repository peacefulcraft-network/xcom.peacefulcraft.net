<?php

use GuzzleHttp\Psr7\Response;

echo "Performing test setup..." . PHP_EOL;
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../config/config.sample.php");

require('ControllerTest.php');
ControllerTest::init('http://127.0.0.1:8081', $config['writer_token']);

$apiAlive = false;
try {
	$resp = ControllerTest::$authenticatedClient->get('/health');
	if ($resp->getStatusCode() === 200) {
		$apiAlive = true;
	}
} finally {
	if (!$apiAlive) {
		echo "Failed to reach API/health route. Is the API running?";
		exit(1);
	}
}

?>