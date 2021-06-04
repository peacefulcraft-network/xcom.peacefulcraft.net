<?php namespace pcn\xcom\controllers\profile;

use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\ProfileLinkModel;
use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\util\RequestFieldsExist;
use RuntimeException;

class CreateProfile implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		$body = $request->getBody();

		// Check for required parameters
		if (!RequestFieldsExist::RequestFieldsExist(
			$body,
			[ 'uuid' ],
			$response
		)) { return; }

		if (strlen($body['uuid']) !== 36) {
			$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorMessage('Invalid value in field \'uuid\'.');
			return;
		}

		$Link = ProfileLinkModel::fetchProfileLink(new ProfileLinkService(ProfileLinkService::MOJANG), $body['uuid']);
		if ($Link !== null) {
			$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorMessage('This uuid is already linked to an account.');
			return;
		}

		try {
			$Profile = ProfileModel::createProfile($body['uuid']);
			$response->setHttpResponseCode(Response::HTTP_CREATED);
			$response->setData([ 'id' => $Profile->id ]);

		} catch (RuntimeException $ex) {
			$response->setHttpResponseCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorMessage($ex->getMessage());
			return;
		}
	}

}

?>