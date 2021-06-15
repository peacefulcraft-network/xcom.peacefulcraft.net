<?php

use net\peacefulcraft\apirouter\router\Response;

class AddPartyMemberTest extends ControllerTest {

	public function testAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post('/party/1/membership/b22eeb1d-2805-454a-8180-8f246249fe0e');

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}
}
?>