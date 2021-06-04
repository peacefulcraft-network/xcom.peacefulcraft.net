<?php

use net\peacefulcraft\apirouter\router\Response;

class GetProfileTest extends ControllerTest {

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
		$resp = SELF::$unAuthenticatedClient->get('/profile?id=1');
		$this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
		
		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertEquals("", $api_resp['error']);
		$this->assertEquals(1, $data['id']);
	}

	public function testValidGetProfileByMojangLink() {
		$resp = SELF::$unAuthenticatedClient->get('/profile?service_id=MOJANG&link_identifier=7464ffe3-fec1-4c1e-8cdc-7dcc688fb986');
		$this->assertEquals(Response::HTTP_OK, $resp->getStatusCode());
		
		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertEquals("", $api_resp['error']);
		$this->assertEquals(1, $data['id']);
	}
}

?>