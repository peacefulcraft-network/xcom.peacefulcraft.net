<?php namespace pcn\xcom\controllers\party;

use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\PartyModel;

class UpateParty implements Controller {
	public function handle(array $config, Request $request, Response $response): void {
		$party_id = $request->getUriParameters()['id'];
		$body = $request->getBody();

		if (!PartyModel::validatePartyId($party_id)) {
			$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorMessage('Party id must be a meaningful, positive, non-zero integer');
			return;
		}

		if (array_key_exists('name', $body)) {
			$Party = PartyModel::fetchById($party_id);
			$Party->setName($body['name']);
			$Party->commit();
		}

		$response->setHttpResponseCode(Response::HTTP_EMPTY_RESPONSE);
	}
}
?>