<?php namespace pcn\xcom\datasources\models;

use SplFixedArray;

class SessionPropertyModel extends SessionModel {

	CONST TABLE_NAME = 'session_property';
	CONST READABLE_FIELDS = ['session_id', 'property', 'value'];
	CONST WRITEABLE_FIELDS = ['value'];

	protected int $session_id;
	protected string $property;
	protected ?string $value;

	// Disable constructor
	private function __construct() {}

	protected function deserialize(): void {}

	public static function wrap(int $session_id, string $property, string $value): SessionPropertyModel {

	}

	public static function create(SessionModel $Session, string $property, string $value): SessionPropertyModel {

	}

	public static function fetchWithFilters(array $filters): SplFixedArray {

	}
}
?>