<?php

use net\peacefulcraft\apirouter\router\Response;

class CreateProfileTest extends ControllerTest {

	public function testCreateProfileRequiresAuthentication() {
		$resp = SELF::$unAuthenticatedClient->post('/profile', [
			'json' => [
				'uuid' => 'b22eeb1d-2805-454a-8180-8f246249fe0e',
			]
		]);

		$this->assertEquals(Response::HTTP_UNAUTHORIZED, $resp->getStatusCode());
	}
}
?>