<?php namespace pcn\xcom\controllers\party;

use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Response;
use net\peacefulcraft\apirouter\util\Validator;
use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\util\RequestFieldsExist;
use RuntimeException;

class CreateParty implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		$body = $request->getBody();

		// Check for required parameters
		if (!RequestFieldsExist::RequestFieldsExist(
			$body,
			[ 'leader_id' ],
			$response
		)) { return; }

		// Check for optional parameters
		$party_name = null;
		if (array_key_exists('party_name', $body) && Validator::meaningfullyExists($body['party_name'])) {
			$party_name = $body['party_name'];
		}

		// Try to create the party
		try {
			$Leader = ProfileModel::fetchByIds([$body['leader_id']]);
			if ($Leader === null) {
				$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorCode(-1);
				$response->setErrorMessage('No profile matches provided \'leader_id\'');
				return;
			}
			$Leader = $Leader[0];

			$Party = PartyModel::createParty($Leader, $party_name);
		} catch (RuntimeException $ex) {
			$response->setHttpResponseCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorCode($ex->getCode());
			$response->setErrorMessage($ex->getMessage());
			return;
		}

		// Return generated id
		$response->setHttpResponseCode(Response::HTTP_CREATED);
		$response->setData([
			'id' => $Party->id,
		]);
	}
}

?>