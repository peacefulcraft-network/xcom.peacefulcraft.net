<?php namespace pcn\xcom\util;

use net\peacefulcraft\apirouter\router\Response;
use net\peacefulcraft\apirouter\util\Validator;

abstract class RequestFieldsExist {
	public static function RequestFieldsExist(array $body, array $fields, Response $response): bool {
		foreach ($fields as $field) {
			if (
				!array_key_exists($field, $body) ||
				!Validator::meaningfullyExists($body[$field])
			) {
				$response->setHttpResponseCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorCode(Response::HTTP_BAD_REQUEST);
				$response->setErrorMessage("Request is lacking field '$field'.");
				return false;
			}
		}

		return true;
	}
}
?>