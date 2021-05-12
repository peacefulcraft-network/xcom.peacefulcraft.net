<?php namespace pcn\xcom\datasources;

use mysqli;

abstract class MySQLDatasource implements \JsonSerializable {
	protected static mysqli $_mysqli;

	public function __construct() {
		$this->deserialize();
	}

	public static function init(array $config) {
		SELF::$_mysqli = new mysqli(
			$config['host'],
			$config['user'],
			$config['password'],
			$config['database'],
			$config['port']
		);
	}

	abstract protected function deserialize(): void;

	public static function teardown() {
		SELF::$_mysqli->close();
	}
}
?>