<?php

use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\ProfileLinkModel;
use pcn\xcom\datasources\models\ProfileModel;

class GetProfileTest extends ControllerTest {

	private static ProfileModel $user;
	private static ProfileLinkModel $mojang_link;

	/**
	 * @beforeClass
	 */
	public static function dbp() {
		SELF::$user = createProfile();
		SELF::$mojang_link = linkProfileToMojangId(SELF::$user);
	}

	public function testGetProfileEnforcesParameterRequirements() {
		$resp = SELF::$unAuthenticatedClient->get('/profile');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

		$resp = SELF::$unAuthenticatedClient->get('/profile?id=');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
		
		$resp = SELF::$unAuthenticatedClient->get('/profile?service_id=MOJANG');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
		
		$resp = SELF::$unAuthenticatedClient->get('/profile?service_id=MOJANG&link_identifier=');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

		$resp = SELF::$unAuthenticatedClient->get('/profile?service_id=FAKELINK&link_identifier=415632');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testGetProfile404sOnNonExistentProfile() {
		$resp = SELF::$unAuthenticatedClient->get('/profile?id=10000000000');
		$this->assertEquals(Response::HTTP_NOT_FOUND, $resp->getStatusCode());

		$resp = SELF::$unAuthenticatedClient->get('/profile?service_id=MOJANG&link_identifier=415632');
		$this->assertEquals(Response::HTTP_NOT_FOUND, $resp->getStatusCode());
	}

	public function testValidGetProfileById() {
		$resp = SELF::$unAuthenticatedClient->get('/profile?id=' . SELF::$user->id);
		$this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
		
		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertEquals("", $api_resp['error']);
		$this->assertEquals(1, $data['id']);
	}

	public function testValidGetProfileByMojangLink() {
		$resp = SELF::$unAuthenticatedClient->get('/profile?service_id=MOJANG&link_identifier=' . SELF::$mojang_link->link_identifier);
		$this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
		
		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertEquals("", $api_resp['error']);
		$this->assertEquals(1, $data['id']);
	}
}

?>