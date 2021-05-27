<?php namespace pcn\xcom\datasources\models;

use net\peacefulcraft\apirouter\util\Validator;
use pcn\xcom\datasources\MySQLDatasource;
use RuntimeException;

class PartyModel extends MySQLDatasource {

	private int $id;
	private int $leader_id;
	private string $name;
	private array $party_membership;

	// Disable constructor
	private function __construct() {}

	public static function wrap(int $id, int $leader_id, string $name, array $party_membership): PartyModel {
		$Party = new PartyModel();
		$Party->id = $id;
		$Party->leader_id = $leader_id;
		$Party->name = $name;
		$Party->party_membership = $party_membership;

		return $Party;
	}

	protected function deserialize(): void {}

	public static function createParty(int $leader_id, string $name = null): PartyModel {
		$query = SELF::$_mysqli->prepare("INSERT INTO `party` (`name`, `leader_id`) VALUES(?,?)");
		$query->bind_param("si", $name, $leader_id);
		$query->execute();
		$query->store_result();
		if ($query->affected_rows !== 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
			throw new RuntimeException("Database error. Unable to create party.");
		}
		$party_id = $query->insert_id;
		$query->close();

		$Party = PartyModel::wrap($party_id, $leader_id, $name, []);
		$Party->addMember($leader_id);

		return $Party;
	}

	public static function fetchById(int $party_id, bool $fetch_membership = false): ?PartyModel {
		$name = $leader_id = "";
		$party_membership = [];
		
		$query = SELF::$_mysqli->prepare("SELECT `name`,`leader_id` FROM `party` WHERE `party_id`=?");
		$query->bind_param("i", $party_id);
		$query->bind_result($name, $leader_id);
		$query->execute();
		$query->store_result();
		if ($query->num_rows !== 1) {
			$query->close();
			return null;	
		}
		$query->fetch();
		$query->close();

		if ($fetch_membership) {
			$query = SELF::$_mysqli->prepare("SELECT `profile_id` FROM `party_membership` WHERE `party_id`=?");
			$query->bind_param("i", $party_id);
			$query->execute();
			$query->store_result();
			$party_membership = $query->get_result()->fetch_array(MYSQLI_NUM);
			$query->close();
		}

		return PartyModel::wrap($party_id, $leader_id, $name, $party_membership);
	}
 
	public static function deleteParty(int $party_id): void {
		$query = SELF::$_mysqli->prepare("DELETE FROM `party` WHERE `party_id`=?");
		$query->bind_param("i", $party_id);
		$query->execute();
		$query->store_result();
		if ($query->affected_rows < 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
			throw new RuntimeException("Database error. Unable to confirm party removal.");
		}
		$query->close();
	}

	public function setName(?string $name = null): void {
		if ($name === $this->name) { return; }

		$query = SELF::$_mysqli->prepare("UPDATE `party` SET `name`=? WHERE `id`=?");
		$query->bind_param("si", $name, $this->id);
		$query->execute();
		$query->store_result();
		$query->close();
		if ($query->affected_rows !== 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
			throw new RuntimeException("Database error. Unable to change party name");
		}
		$query->close();

		$this->name = $name;
	}

	public function addMember(int $profile_id): void {
		if (in_array($profile_id, $this->party_membership, true)) { return; }

		$query = SELF::$_mysqli->prepare("INSERT INTO `party_membership` VALUES(?,?)");
		$query->bind_param("ii", $this->id, $profile_id);
		$query->execute();
		$query->store_result();
		if ($query->affected_rows !== 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
			throw new RuntimeException("Database error. Unable to add user to party.");
		}
		$query->close();

		array_push($this->party_membership, $profile_id);
	}

	public function setLeader(int $profile_id): void {
		if (!in_array($profile_id, $this->party_membership, true)) {
			throw new RuntimeException("User must be member of party to transfer leadership.");
		}

		$query = SELF::$_mysqli->prepare("UPDATE `party` SET `leader_id`=? WHERE `id`=?");
		$query->bind_param("ii", $profile_id, $this->id);
		$query->execute();
		$query->store_result();
		$query->close();
		if ($query->affected_rows !== 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
			throw new RuntimeException("Database error. Failed to transfer party leadership.");
		}
		$query->close();
		
		$this->leader_id = $profile_id;
	}

	public function removeMember(int $profile_id): void {
		if ($this->leader_id === $profile_id) {
			throw new RuntimeException("Unable to remove leader from party. Transfer leadership, then try again.");
		}

		$pos = array_search($profile_id, $this->party_membership, true);
		if (!$pos) { return; }

		$query = SELF::$_mysqli->prepare("DELETE FROM `party_membership` WHERE `profile_id`=?");
		$query->bind_param("i", $profile_id);
		$query->execute();
		$query->store_result();
		if ($query->affected_rows !== 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
			throw new RuntimeException("Database error. Failed to remove party member.");
		}
		$query->close();

		unset($this->party_membership[$pos]);
	}

	public static function validatePartyId(?int $party_id): bool {
		if (
			Validator::meaningfullyExists($party_id)
			&& is_numeric($party_id)
			&& $party_id > 0
			&& !is_float($party_id)
		) {
			return true;
		} else { return false; }
	}
}

?>