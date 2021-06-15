<?php

use net\peacefulcraft\apirouter\router\Response;

class DeletePartyTest extends ControllerTest {
	
	public function testDeletePartyRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->delete('/party/:id');

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}
}
?>