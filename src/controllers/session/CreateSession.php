<?php namespace pcn\xcom\controllers\session;

use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\util\RequestFieldsExist;

class CreateSession implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		$body = $request->getBody();

		if (!RequestFieldsExist::RequestFieldsExist($body, ['uid'], $response)) {
			return;
		}
	}
}
?>