<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase {

	public static Client $authenticatedClient;
	public static Client $unAuthenticatedClient;

	public static function init(string $host, string $writer_token): void {
		global $config;

		SELF::$authenticatedClient = new Client([
			'base_uri' => $host,
			'headers' => [
				'Authorization' => $writer_token,
			],
			'http_errors' => false,
		]);
		SELF::$unAuthenticatedClient = new Client([
			'base_uri' => $host,
			'http_errors' => false,
		]);
	}
}

?>