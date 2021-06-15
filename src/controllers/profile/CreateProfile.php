<?php namespace pcn\xcom\controllers\profile;

use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\ProfileModel;
use RuntimeException;

class CreateProfile implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		try {
			$Profile = ProfileModel::createProfile();
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