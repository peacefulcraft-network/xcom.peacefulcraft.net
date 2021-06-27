<?php

use net\peacefulcraft\apirouter\router\Response;
use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileModel;

class DeletePartyTest extends ControllerTest {
	
	private static ProfileModel $leader;
	private static PartyModel $party;

	/**
	 * @beforeClass
	 */
	public static function dbp() {
		SELF::$leader = createProfile();
		SELF::$party = createParty(SELF::$leader);
	}

	public function testDeletePartyRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->delete('/party/:id');

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}

	public function testDeletePartyValidatesParameters() {
		$resp = SELF::$authenticatedClient->delete('/party/2asdf94');
		$this->assertEquals(Response::HTTP_BAD_REQUEST, $resp->getStatusCode(), strval($resp->getBody()));
	}

	public function testValidDeletePartyRequest() {
		$resp = SELF::$authenticatedClient->delete('/party/' . SELF::$party->id);

		$this->assertEquals(Response::HTTP_EMPTY_RESPONSE, $resp->getStatusCode(), strval($resp->getBody()));
		$this->assertEquals(0, strlen(strval($resp->getBody())));
	}
}
?>