<?php

use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\ProfileModel;

class CreateProfileLinkTest extends ControllerTest {

	private static ProfileModel $user;

	/**
	 * @beforeClass
	 */
	public static function dbp() {
		SELF::$user = createProfile();
	}

	public function testCreateProfileLinkRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post("/profile/" . SELF::$user->id . "/link");

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}

	public function testCreateProfileLinkEnforcesParameterRequirements() {
		// Test missing params
		$resp = SELF::$authenticatedClient->post('/profile/' . SELF::$user->id . '/link');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

		// Test invalid link_service
		$resp = SELF::$authenticatedClient->post('/profile/' . SELF::$user->id . '/link', [
			'json' => [
				'link_service' => 'FAKE SERVICE',
				'link' => '950t3gwjuipMSK0',
				'is_speculative' => false,
				'visibility' => 'PUBLIC',
			]
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

		// Test invalid visibility
		$resp = SELF::$authenticatedClient->post('/profile/' . SELF::$user->id . '/link', [
			'json' => [
				'link_service' => 'MOJANG',
				'link' => '950t3gwjuipMSK0',
				'is_speculative' => false,
				'visibility' => '0eitjstw3r',
			]
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testCreateProfileLinkOnNonexistentProfileFails() {
		// Test invalid link_service
		$resp = SELF::$authenticatedClient->post('/profile/' . PHP_INT_MAX . '/link', [
			'json' => [
				'link_service' => 'FLARUM',
				'link' => '950t3gwjuipMSK0',
				'is_speculative' => false,
				'visibility' => 'PUBLIC',
			]
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}
}