<?php namespace pcn\xcom\controllers\profile;

use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\ProfileLinkModel;
use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;
use pcn\xcom\util\RequestFieldsExist;
use RuntimeException;

class CreateServiceLink implements Controller {

	public function handle(array $config, Request $request, Response $response): void {
		$body = $request->getBody();
		$body += $request->getUriParameters();

		if (!RequestFieldsExist::RequestFieldsExist($body, ['id', 'link_service', 'link', 'is_speculative', 'visibility'], $response)) {
			return;
		}

		try {
			$body['link_service'] = new ProfileLinkService($body['link_service']);
			$body['visibility'] = new ProfileLinkVisibility($body['visibility']);
		} catch (RuntimeException $ex) {
			$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorMessage($ex->getMessage());
			return;
		}

		try {
			$Profile = ProfileModel::fetchByIds([$body['id']]);
			if ($Profile === null) {
				$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorMessage('No profile exists under the given id');
				return;
			}
			$Profile = $Profile[0];

			ProfileLinkModel::createProfileLink($Profile, $body['link_service'], $body['link'], $body['is_speculative'], $body['visibility']);
			$response->setHttpResponseCode(Response::HTTP_CREATED);
		} catch (RuntimeException $ex) {
			$response->setHttpResponseCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorCode(Response::HTTP_INTERNAL_ERROR);
			$response->setErrorMessage('Database error. Unable to create link. Please try again.');
		}
	}
}

?>