<?php namespace pcn\xcom\controllers\profile;

use net\peacefulcraft\apirouter\router\Controller;
use net\peacefulcraft\apirouter\router\Request;
use net\peacefulcraft\apirouter\router\Response;
use net\peacefulcraft\apirouter\util\Validator;
use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\enum\ProfileLinkService;
use RuntimeException;

class GetProfile implements Controller {
	public function handle(array $config, Request $request, Response $response): void {
		$params = $request->getUriParameters();
		/**
		 * Fetch by profile id
		 */
		if (array_key_exists('id', $params)) {
			$id = intval($params['id']);
			if (!(is_int($id) && $id > 0)) {
				$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorMessage('Invalid value in field \'id\'.');
				return;
			}
			
			$profile = ProfileModel::fetchByIds([ $id ]);
			if ($profile === null) {
				$response->setHttpResponseCode(Response::HTTP_NOT_FOUND);
				$response->setErrorCode(Response::HTTP_NOT_FOUND);
				$response->setErrorMessage('Profile not found.');
				return;
			}
			
			$response->setHttpResponseCode(Response::HTTP_OK);
			$response->setData($profile[0]);

		/**
		 * Fetch by service link (oAuth, etc)
		 */
		} else if (array_key_exists('service_id', $params) && array_key_exists('link_identifier', $params)) {
			$service = $params['service_id'];
			$link_identifier = $params['link_identifier'];
			try {
				$service = new ProfileLinkService($service);
				if (!Validator::meaningfullyExists($link_identifier)) {
					throw new RuntimeException('\'Link Identifier\' must be a meaningful string.');
				}
			} catch (RuntimeException $ex) {
				$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorMessage($ex->getMessage());
				return;
			}

			$Profile = ProfileModel::fetchByProfileLink($service, $link_identifier);
			if ($Profile === null) {
				$response->setHttpResponseCode(Response::HTTP_NOT_FOUND);
				$response->setErrorCode(Response::HTTP_NOT_FOUND);
				$response->setErrorMessage('No profile found by that link.');
				return;
			}

			$response->setData($Profile);
			$response->setHttpResponseCode(Response::HTTP_OK);

		/**
		 * Bad request. Unresolvable set of profile parameters
		 */
		} else {
			$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorCode(Response::HTTP_BAD_REQUEST);
			$response->setErrorMessage('One of \'id\' or \'service_id\' and \'link_identifier\' are required.');
			return;
		}
	}
}

?>