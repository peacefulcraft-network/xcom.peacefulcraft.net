<?php namespace pcn\xcom\middleware;

use net\peacefulcraft\apirouter\router\Middleware;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;

class EchobackEmptyRequestBody implements Middleware {
	public function run(array $config, Request $request, Response $response): bool {
		if (count($request->getBody()) === 0) {
			$response->setHttpResponseCode(Response::HTTP_EMPTY_RESPONSE);
			$response->setResponseTypeRaw(true);
			return false;
		}

		return true;
	}
}
?>