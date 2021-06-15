<?php

use net\peacefulcraft\apirouter\router\Response;

class RemovePartyMemberTest extends ControllerTest {
	public function testRemovePartyMemberRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->delete('/party/:id/membership/:uid');

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}
}
?>