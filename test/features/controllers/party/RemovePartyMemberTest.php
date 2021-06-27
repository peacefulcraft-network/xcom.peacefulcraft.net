<?php

use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileModel;

class RemovePartyMemberTest extends ControllerTest {
	
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
		SELF::$party->addMember(SELF::$party_joiner);
	}
	
	public function testRemovePartyMemberRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->delete('/party/:id/membership/:uid');

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode(), strval($resp->getBody()));
	}

	public function testRemovePartyMemberValidatesParameters() {
		$resp = SELF::$authenticatedClient->delete('/party/' . SELF::$party->id . '/membership/289304ad');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode(), strval($resp->getBody()));
	}

	public function testRemovePartyLeaderFails() {
		$resp = SELF::$authenticatedClient->delete('/party/' . SELF::$party->id . '/membership/' . SELF::$party_leader->id);
		$this->assertEquals(Response::HTTP_INTERNAL_ERROR, $resp->getStatusCode(), strval($resp->getBody()));
	}

	public function testValidRemovePartyRequest() {
		$resp = SELF::$authenticatedClient->delete('/party/' . SELF::$party->id . '/membership/' . SELF::$party_joiner->id);
		$this->assertEquals(Response::HTTP_EMPTY_RESPONSE, $resp->getStatusCode(), strval($resp->getBody()));
		$this->assertEquals(0, strlen(strval($resp->getBody())));
	}
}
?>