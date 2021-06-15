<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase {

	public static Client $authenticatedClient;
	public static Client $unAuthenticatedClient;

	protected static mysqli $mysqli;

	public static function init(string $host, string $writer_token, array $sql): void {
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

		SELF::$mysqli = new mysqli($sql['host'], $sql['user'], $sql['password'], $sql['database'], $sql['port']);
	}

	/**
	 * @beforeClass
	 */
	public static function _dbTruncate() {
		$tables = mysqli_query(SELF::$mysqli, "SHOW TABLES")->fetch_all(MYSQLI_NUM);
		mysqli_query(SELF::$mysqli, "SET FOREIGN_KEY_CHECKS=0;");
		foreach($tables as $table) {
			if ($table[0] == "agnostic_migrations") { continue; }
			mysqli_query(SELF::$mysqli, "TRUNCATE TABLE ${table[0]}");
		}
	}
}

?>