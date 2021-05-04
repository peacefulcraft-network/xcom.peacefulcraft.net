<?php namespace pcn\xcom\middleware;

use net\peacefulcraft\apirouter\router\Middleware;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;

/**
 * Checks for 'Authorization' header on requests and compares
 * against configured writer token in Application configuration.
 * Only requests bearing the correct token are allowed to pass.
 */
class HasAuthorizationToken implements Middleware {
	public function run(array $config, Request $request, Response $response): bool {
		$headers = $request->getHeaders();
		if (!array_key_exists('Authorization', $headers)) {
			$response->setHttpResponseCode(Response::HTTP_UNAUTHORIZED);
			$response->setErrorMessage('Authorization token not present on request.');
			return false;
		}

		if ($headers['Authorization'] !== $config['writer_token']) {
			$response->setHttpResponseCode(Response::HTTP_UNAUTHORIZED);
			$response->setErrorMessage('Authorization token invalid.');
			return false;
		}

		return true;
	}
}

?>