<?php

use net\peacefulcraft\apirouter\router\Response;

class UpdatePartyTest extends ControllerTest {
	public function testUpdatePartyRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->put('/party/:id', [
			'json' => [
				'name' => 'New Party Name',
			]
		]);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}
}
?>