<?php

use net\peacefulcraft\apirouter\Application;
use net\peacefulcraft\apirouter\router\RequestMethod;
use pcn\xcom\datasources\MySQLDatasource;

if (!isset($config)) {
	trigger_error('$config is undefined. Initializing application with empty configuration.', E_USER_NOTICE);
	$config = array();
}

$Application = new Application($config);
MySQLDatasource::init($config['mysql']);
$Router = $Application->getRouter();

$Router->registerRoute(RequestMethod::GET, "", [], '\pcn\xcpm\controllers\Index.php');
?>