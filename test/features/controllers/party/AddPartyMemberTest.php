<?php

use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileModel;

class AddPartyMemberTest extends ControllerTest {

	private static ProfileModel $party_leader;
	private static ProfileModel $party_joiner;

	private static PartyModel $party;

	/**
	 * @beforeClass
	 */
	public static function dbp() {
		SELF::$party_leader = createProfile();
		SELF::$party_joiner = createProfile();
		SELF::$party = createParty(SELF::$party_leader);
	}

	public function testAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post('/party/1/membership/' . SELF::$party_leader->id);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}

	public function testAddPartyMemberValidatesParameters() {
		// Requires uid
		$resp = SELF::$authenticatedClient->post('/party/2/membership/' . SELF::$party_leader->id);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testAddPartyMemberValidatesPartyOnRequestExists() {
		// Party with that id should exist
		$resp = SELF::$authenticatedClient->post('/party/' . SELF::$party->id + 1000 . '/membership/' . SELF::$party_leader->id);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testAddPartyMemberValidatesProfileIdExists() {
		// Party with that profile id that doesn't exist
		$resp = SELF::$authenticatedClient->post('/party/' . SELF::$party->id . '/membership/' . SELF::$party_leader->id + 1000);
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode());
	}

	public function testAddPartyMemberWorks() {
		// Party with that id should exist
		$resp = SELF::$authenticatedClient->post('/party/' . SELF::$party->id . '/membership/' . SELF::$party_joiner->id);
		$this->assertEquals(Response::HTTP_CREATED, $resp->getStatusCode(), strval($resp->getBody()));
	}
}
?>