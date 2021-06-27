<?php

use net\peacefulcraft\apirouter\Application;
use net\peacefulcraft\apirouter\router\RequestMethod;
use pcn\xcom\datasources\MySQLDatasource;

ob_start();
if (!isset($config)) {
	trigger_error('$config is undefined. Initializing application with empty configuration.', E_USER_NOTICE);
	$config = array();
}

$Application = new Application($config);
MySQLDatasource::init($config['mysql']);
$Router = $Application->getRouter();

$Router->registerRoute(RequestMethod::GET, "/health", [], '\pcn\xcom\controllers\Index');

// Profile routes
$Router->registerRoute(RequestMethod::POST, '/profile', [
	'\pcn\xcom\middleware\HasAuthorizationToken',
], '\pcn\xcom\controllers\profile\CreateProfile');
$Router->registerRoute(RequestMethod::POST, '/profile/:id/link', [
	'\pcn\xcom\middleware\HasAuthorizationToken',
], '\pcn\xcom\controllers\profile\CreateServiceLink');
// Uses standard url encoded parameters
$Router->registerRoute(RequestMethod::GET, '/profile', [], '\pcn\xcom\controllers\profile\GetProfile');

// Party routes
$Router->registerRoute(RequestMethod::POST, '/party', [
	'\pcn\xcom\middleware\HasAuthorizationToken',
], '\pcn\xcom\controllers\party\CreateParty');
$Router->registerRoute(RequestMethod::PUT, '/party/:id', [
	'\pcn\xcom\middleware\HasAuthorizationToken',
], '\pcn\xcom\controllers\party\UpdateParty');
$Router->registerRoute(RequestMethod::DELETE, '/party/:id', [
	'\pcn\xcom\middleware\HasAuthorizationToken',
], '\pcn\xcom\controllers\party\DeleteParty');
$Router->registerRoute(RequestMethod::POST, '/party/:id/membership/:uid', [
	'\pcn\xcom\middleware\HasAuthorizationToken',
], '\pcn\xcom\controllers\party\AddPartyMember');
$Router->registerRoute(RequestMethod::DELETE, '/party/:id/membership/:uid', [
	'\pcn\xcom\middleware\HasAuthorizationToken',
], '\pcn\xcom\controllers\party\RemovePartyMember');

$Application->handle();
ob_flush();
?>