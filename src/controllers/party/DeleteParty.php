<?php namespace pcn\xcom\controllers\party;

use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\PartyModel;
use RuntimeException;

class DeleteParty implements Controller {
	public function handle(array $config, Request $request, Response $response): void {
		$party_id = $request->getUriParameters()['id'];
		
		if (!PartyModel::validatePartyId($party_id)) {
			$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorMessage('Party id must be a meaningful, positive, non-zero integer');
			return;
		}

		try {
			PartyModel::deleteParty($party_id);
		} catch (RuntimeException $ex) {
			$response->setHttpResponseCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorMessage('Error deleting party.');
			return;
		}

		$response->setHttpResponseCode(Response::HTTP_EMPTY_RESPONSE);
		$response->setResponseTypeRaw(true);
	}
}
?>