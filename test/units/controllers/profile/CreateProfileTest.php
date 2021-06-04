<?php

use net\peacefulcraft\apirouter\router\Response;

class CreateProfileTest extends ControllerTest {

	public function testCreateProfileRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post('/profile', [
			'json' => [
				'uuid' => 'b22eeb1d-2805-454a-8180-8f246249fe0e',
			]
		]);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}

	public function testCreateProfileEnforcesParameterRequirements() {
		$resp = SELF::$authenticatedClient->post('/profile', [
			'json' => ['fake_field' => 'asdf']
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

		$resp = SELF::$authenticatedClient->post('/profile', [
			'json' => ['uuid' => '']
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
		
		$resp = SELF::$authenticatedClient->post('/profile', [
			'json' => ['uuid' => '8644u6e5j-46ueh']
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testCreateProfileErrorsOnDuplicateCreationAttempts() {
		$resp = SELF::$authenticatedClient->post('/profile', [
			'json' => [
				'uuid' => '7464ffe3-fec1-4c1e-8cdc-7dcc688fb986'
			]
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

	}

	public function testSuccesfulCreateProfileReturnsCreatedProfile() {
		$resp = SELF::$authenticatedClient->post('/profile', [
			'json' => [
				'uuid' => 'b22eeb1d-2805-454a-8180-8f246249fe0e'
			]
		]);
		$this->assertEquals(Response::HTTP_CREATED, $resp->getStatusCode());

		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertArrayHasKey('id', $data);
		$this->assertIsInt($data['id']);
		$this->assertGreaterThan(1, $data['id']);	// populated database should take at least id 1
	}
}
?>