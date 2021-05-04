<?php namespace pcn\xcom\datasources\models;

use pcn\xcom\datasources\MySQLDatasource;

class ProfileModel extends MySQLDatasource {
	private int $id;
		public function getId(): int { return $this->id; }

	// TODO: Probably don't store as a string
	private string $created_at;
		public function getCreatedAt() { return $this->created_at; }

	protected function deserialize(): void {}

	public static function fetchByIds(array $ids): array {
		$query = SELF::$_mysqli->prepare("SELECT * FROM `profile` WHERE `id`=?");
		$res = array();
		foreach($ids as $id) {
			$query->bind_param("i", $id);
			$query->execute();
			$query->store_result();
			array_push($res, $query->get_result()->fetch_object('\pcn\xcom\datasources\models\ProfileMode.php'));
		}
		$query->close();

		return $res;
	}

	public function jsonSerialize(): mixed {
		return [
			'id' => $this->id,
			'created_at' => $this->created_at
		];
	}
}
?>