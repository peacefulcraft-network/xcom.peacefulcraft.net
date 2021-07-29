<?php namespace pcn\xcom\datasources\models;

use DateTime;
use pcn\xcom\datasources\MySQLDatasource;

class SessionModel extends MySQLDatasource {

	CONST TABLE_NAME = 'session';
	CONST READABLE_FIELDS = ['id', 'profile_id', 'start_time', 'end_time'];
	CONST WRITEABLE_FIELDS = ['end_time'];

	protected int $id;
	protected int $profile_id;
	protected string|DateTime $start_time;
	protected string|DateTime|null $end_time;

	// Disable constructor
	private function __construct() {}


	protected function deserialize(): void {}

	public static function wrap(int $id, int $profile_id, DateTime $start_time, DateTime $end_time): SessionModel {

	}

	public static function create(ProfileModel $Profile): SessionModel {

	}
}
?>