<?php

use pcn\xcom\datasources\MySQLDatasource;

echo "Performing test setup..." . PHP_EOL;
require(__DIR__ . "/../vendor/autoload.php");
require(__DIR__ . "/../config/config.sample.php");
require(__DIR__ . '/data-generators.php');

MySQLDatasource::init($config['mysql']);

require('ControllerTest.php');
ControllerTest::init('http://127.0.0.1:8081', $config['writer_token'], $config['mysql']);

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