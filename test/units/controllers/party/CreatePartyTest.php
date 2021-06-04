<?php

use net\peacefulcraft\apirouter\router\Response;

class CreatePartyTest extends ControllerTest {

	public function testCreatePartyRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post('/party', [
			'json' => [
				'leader_id' => 'b22eeb1d-2805-454a-8180-8f246249fe0e',
			]
		]);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}

	public function testCreatePartyEnforcesParameterContraints() {
		$resp = SELF::$authenticatedClient->post('/party', [
			'json' => ['fake_field' => 'asdf']
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

		$resp = SELF::$authenticatedClient->post('/party', [
			'json' => ['leader_id' => '']
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());

		// Party name is an optional field that should not influence evaluation of 'leader_id'
		$resp = SELF::$authenticatedClient->post('/party', [
			'json' => ['party_name' => '']
		]);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testValidCreatePartyReturnsPartyDetails() {
		$resp = SELF::$authenticatedClient->post('/party', [
			'json' => ['leader_id' => 3]
		]);
		$this->assertEquals(Response::HTTP_CREATED, $resp->getStatusCode());

		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertArrayHasKey('id', $data);
		$this->assertIsInt($data['id']);
	}
}
?>