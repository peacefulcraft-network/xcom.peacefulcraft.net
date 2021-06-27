<?php

use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\ProfileModel;

class CreatePartyTest extends ControllerTest {

	private static ProfileModel $profile_with_no_party;

	/**
	 * @beforeClass
	 */
	public static function dbp() {
		SELF::$profile_with_no_party = createProfile();
	}

	public function testCreatePartyRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post('/party', [
			'json' => [
				'leader_id' => 'b22eeb1d-2805-454a-8180-8f246249fe0e',
			]
		]);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode(), strval($resp->getBody()));
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
			'json' => ['leader_id' => SELF::$profile_with_no_party->id]
		]);
		$this->assertEquals(Response::HTTP_CREATED, $resp->getStatusCode(), strval($resp->getBody()));

		$api_resp = json_decode($resp->getBody(), true);
		$data = $api_resp['data'];
		$this->assertArrayHasKey('id', $data, strval($resp->getBody()));
		$this->assertIsInt($data['id'], strval($resp->getBody()));
	}
}
?>