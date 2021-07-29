<?php namespace pcn\xcom\controllers\session;

use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\util\RequestFieldsExist;

class DeleteSessionProperties implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		$body = $request->getBody();

		if (!RequestFieldsExist::RequestFieldsExist($body, ['sid', 'properties'], $response)) {
			return;
		}

		if (!is_array($body['properties'])) {
			$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorCode(-1);
			$response->setErrorMessage('Expected field \'properties\' to be a list');
			return;
		}

		if (count($body['properties']) === 0) {
			$response->setHttpResponseCode(Response::HTTP_NOT_MODIFIED);
			return;
		}
	}
}
?>