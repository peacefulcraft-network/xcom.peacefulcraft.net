<?php namespace pcn\xcom\controllers\party;

use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Response;
use net\peacefulcraft\apirouter\util\Validator;
use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\util\RequestFieldsExist;
use RuntimeException;

class PartyCreate implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		$body = $request->getBody();

		// Check for required parameters
		if (!RequestFieldsExist::RequestFieldsExist(
			$body,
			[ "leader_id" ],
			$response
		)) { return; }

		// Check for optional parameters
		$party_name = null;
		if (array_key_exists("party_name", $body) && Validator::meaningfullyExists($body["party_name"])) {
			$party_name = $body["party_name"];
		}

		// Try to create the party
		try {
			$Party = PartyModel::createParty($body["leader_id"], $party_name);
		} catch (RuntimeException $ex) {
			$response->setHttpResponseCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorCode($ex->getCode());
			$response->setErrorMessage($ex->getMessage());
			return;
		}

		// Return generated id
		$response->setHttpResponseCode(Response::HTTP_OK);
		$response->setData([
			"id" => $Party->getId(),
		]);
	}
}

?>