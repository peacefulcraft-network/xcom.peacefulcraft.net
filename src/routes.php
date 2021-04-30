<?php

use net\peacefulcraft\apirouter\Application;
use net\peacefulcraft\apirouter\router\RequestMethod;

if (!isset($config)) {
	trigger_error('$config is undefined. Initializing application with empty configuration.', E_USER_NOTICE);
	$config = array();
}

$Application = new Application($config);
$Router = $Application->getRouter();

$Router->registerRoute(RequestMethod::GET, "", [], '\pcn\xcpm\controllers\Index.php');
?>