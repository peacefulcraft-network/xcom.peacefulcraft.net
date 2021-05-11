<?php namespace pcn\xcom\controllers;

use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Response;

class Index implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		$response->setData(array_merge($request->getUriParameters(), $request->getBody()));
		$response->setHttpResponseCode(Response::HTTP_OK);
	}
}
?>