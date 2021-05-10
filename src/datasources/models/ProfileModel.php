<?php namespace pcn\xcom\datasources\models;

use DateTime;
use pcn\xcom\datasources\models\collections\ProfileLinkCollection;
use pcn\xcom\datasources\MySQLDatasource;
use RuntimeException;

class ProfileModel extends MySQLDatasource {
	private int $id;
		public function getId(): int { return $this->id; }

	private ProfileLinkCollection $links;
		public function getLinks(): ProfileLinkCollection { return $this->links; }
		private function setLinks(ProfileLinkCollection $links): void { $this->links = $links; }

	private string|DateTime $created_at;
		public function getCreatedAt(): DateTime { return $this->created_at; }

	/**
	 * Called to parse raw values set by MySQLiResult->fetchObject().
	 */
	protected function deserialize(): void {
		$this->created_at = new DateTime($this->created_at);
	}

	public function __construct(?int $id, ?ProfileLinkCollection $links = null, ?DateTime $created_at = null) {
		/**
		 * If PK is null, assume this is instatiation by MySQLiResult->fetchObject().
		 * Values will be populated automatically and processed by $this->deserialize().
		 */
		if ($id === null) { return; }
		
		if ($created_at === null) {
			throw new RuntimeException("created_at must be non-null");
		}

		$this->id = $id;
		$this->created_at = $created_at;
	}

	public static function createProfile(): ProfileModel {

		$Profile = new ProfileModel();
		$ProfileLinks = new ProfileLinkCollection($Profile, []);
		$Profile->setLinks($ProfileLinks);

		return $Profile;
	}

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