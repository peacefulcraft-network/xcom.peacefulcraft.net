<?php

use net\peacefulcraft\apirouter\router\Response;

class CreateProfileTest extends ControllerTest {

	public function testCreateProfileRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post('/profile');

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}

	public function testSuccesfulCreateProfileReturnsCreatedProfile() {
		$resp = SELF::$authenticatedClient->post('/profile');
		$this->assertEquals(Response::HTTP_CREATED, $resp->getStatusCode());

		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertArrayHasKey('id', $data);
		$this->assertIsInt($data['id']);
		$this->assertGreaterThan(0, $data['id']);
	}
}
?>