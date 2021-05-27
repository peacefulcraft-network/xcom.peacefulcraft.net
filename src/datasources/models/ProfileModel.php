<?php namespace pcn\xcom\datasources\models;

use DateTime;
use pcn\xcom\datasources\models\collections\ProfileLinkCollection;
use pcn\xcom\datasources\MySQLDatasource;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;
use RuntimeException;

class ProfileModel extends MySQLDatasource {

	CONST TABLE_NAME = 'profile';
	CONST READABLE_FIELDS = ['id', 'created_at', 'links'];
	CONST WRITEABLE_FIELDS = [];

	protected int $id;
	protected string|DateTime $created_at;

	protected ?ProfileLinkCollection $links;

	// Disable constructor
	private function __construct() {}

	public static function wrap(int $id, DateTime $created_at, ProfileLinkCollection $links): ProfileModel {
		$Profile = new ProfileModel();
		$Profile->id = $id;
		$Profile->created_at = $created_at;
		
		$Profile->links = $links;

		return $Profile;
	}

	/**
	 * Called to parse raw values set by MySQLiResult->fetchObject().
	 */
	protected function deserialize(): void {
		$this->created_at = new DateTime($this->created_at);
	}

	public static function createProfile(string $uuid): ProfileModel {
		// Create profile
		$query = SELF::$_mysqli->prepare("INSERT INTO `profile` VALUES()");
		$query->execute();
		$query->store_result();
		if ($query->affected_rows !== 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
			throw new RuntimeException("Unable to confirm succesful profile creation.");
		}
		$profile_id = $query->insert_id;
		$query->close();

		// Create account link
		$query = SELF::$_mysqli->prepare('INSERT INTO `profile_link` VALUES(?,?,?,?,?)');
		$zero = 0;
		$query->bind_param('issis', $profile_id, ProfileLinkService::MOJANG, $uuid, $zero, ProfileLinkVisibility::PUBLIC_VISIBLE);
		$query->execute();
		$query->store_result();
		if ($query->affected_rows == 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();

			// On error, try to delete orphaned profile entry
			SELF::$_mysqli->query('DELETE FROM `profile` WHERE `id`=' . $profile_id);
		
			throw new RuntimeException('Error creating account link. Profile create failed.');
		}
		$query->close();

		$MojangAccountLink = ProfileLinkModel::wrap($profile_id, new ProfileLinkService(ProfileLinkService::MOJANG), $uuid, false, new ProfileLinkVisibility(ProfileLinkVisibility::PUBLIC_VISIBLE));
		$ProfileLinks = new ProfileLinkCollection([ $MojangAccountLink ]);

		// created_at may not be accurate here, but it is mostly used for accounting so this is ok.
		// subsiquent fetches will use the database value which is accurate, this just avoids an extra query.
		$Profile = ProfileModel::wrap($profile_id, new DateTime('now'), $ProfileLinks);

		return $Profile;
	}

	public static function fetchByIds(array $ids): array {
		$query = SELF::$_mysqli->prepare("SELECT * FROM `profile` WHERE `id`=?");
		$res = array();
		foreach($ids as $id) {
			$query->bind_param("i", $id);
			$query->execute();
			$query->store_result();
			array_push($res, $query->get_result()->fetch_object('\pcn\xcom\datasources\models\ProfileModel'));
		}
		$query->close();

		return $res;
	}
}
?>