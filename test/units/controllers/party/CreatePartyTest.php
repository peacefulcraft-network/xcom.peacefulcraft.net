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
}
?>