<?php namespace pcn\xcom\datasources\models;

use net\peacefulcraft\apirouter\router\Response;
use net\peacefulcraft\apirouter\util\Validator;
use pcn\xcom\datasources\MySQLDatasource;
use RuntimeException;

class PartyModel extends MySQLDatasource {

	private int $id;
		public function getId(): int { return $this->id; }

	private int $leader_id;
		public function getLeaderId(): int { return $this->leader_id; }

	private string $name;
		public function getPartyName(): ?string { return $this->name; }

	private array $party_membership;
		public function getPartyMembership(): array { return $this->party_membership; }

	protected function deserialize(): void {}

	public function __construct(?int $party_id, int $leader_id = -1, ?string $name = null, array $party_membership = []) {
		if ($party_id === null) { return; }

		if ($leader_id === -1) {
			throw new RuntimeException("Leader id must be set and greater than 0.");
		}

		if (count($party_membership)) {
			throw new RuntimeException("Party must have at least one mmeber to be created.");
		}

		$this->id = $party_id;
		$this->leader_id = $leader_id;
		$this->name = $name;
		$this->party_membership = $party_membership;
	}

	public static function createParty(int $leader_id, string $name = null): PartyModel {
		$query = SELF::$_mysqli->prepare("INSERT INTO `party` VALUES(,?,?)");
		$query->bind_param("si", $name, $leader_id);
		$query->execute();
		$query->store_result();
		if ($query->affected_rows !== 1) {
			$query->close();
			throw new RuntimeException("Database error. Unable to create party.");
		}
		$party_id = $query->insert_id;
		$query->close();

		$Party = new PartyModel($party_id, $leader_id, $name, []);
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
		if ($query->num_rows() !== 1) {
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

		return new PartyModel($party_id, $leader_id, $name, $party_membership);
	}
 
	public static function deleteParty(int $party_id): void {
		$query = SELF::$_mysqli->prepare("DELETE FROM `party` WHERE `party_id`=?");
		$query->bind_param("i", $party_id);
		$query->execute();
		$query->store_result();
		$query->close();
		if ($query->affected_rows < 1) {
			throw new RuntimeException("Database error. Unable to confirm party removal.");
		}
	}

	public function setName(?string $name = null): void {
		if ($name === $this->name) { return; }

		$query = SELF::$_mysqli->prepare("UPDATE `party` SET `name`=? WHERE `id`=?");
		$query->bind_param("si", $name, $this->id);
		$query->execute();
		$query->store_result();
		$query->close();
		if ($query->affected_rows !== 1) {
			throw new RuntimeException("Database error. Unable to change party name");
		}

		$this->name = $name;
	}

	public function addMember(int $profile_id): void {
		if (in_array($profile_id, $this->party_membership, true)) { return; }

		$query = SELF::$_mysqli->prepare("INSERT INTO `party_membership` VALUES(?,?)");
		$query->bind_param("ii", $this->id, $profile_id);
		$query->execute();
		$query->store_result();
		$query->close();
		if ($query->affected_rows !== 1) {
			throw new RuntimeException("Database error. Unable to add user to party.");
		}

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
			throw new RuntimeException("Database error. Failed to transfer party leadership.");
		}
		
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
		$query->close();
		if ($query->affected_rows !== 1) {
			throw new RuntimeException("Database error. Failed to remove party member.");
		}

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

	public function jsonSerialize(): mixed {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'leader_id' => $this->leader_id,
			'party_membership' => $this->party_membership
		];
	}
}

?>