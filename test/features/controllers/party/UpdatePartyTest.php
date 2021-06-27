<?php

use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileModel;

class UpdatePartyTest extends ControllerTest {
		
	private static ProfileModel $party_leader;
	

	private static PartyModel $party;

	/**
	 * @beforeClass
	 */
	public static function dbp() {
		SELF::$party_leader = createProfile();
		SELF::$party = createParty(SELF::$party_leader);
	}

	public function testUpdatePartyRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->put('/party/:id', [
			'json' => [
				'name' => 'New Party Name',
			]
		]);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}

	public function testUpdatePartyValidatesParamters() {
		$resp = SELF::$authenticatedClient->put('/party/y95304ie');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testValidEmptyUpdatePartyRequest() {
		$resp = SELF::$authenticatedClient->put('/party/' . SELF::$party->id);
		$this->assertEquals(Response::HTTP_EMPTY_RESPONSE, $resp->getStatusCode());
	}

	public function testValidateUpdatePartyRequestWithChanges() {
		$resp = SELF::$authenticatedClient->put('/party/' . SELF::$party->id, [
			'json' => [
				'name' => 'New Party name'
			]
		]);
		$this->assertEquals(Response::HTTP_EMPTY_RESPONSE, $resp->getStatusCode());
	}
}
?>