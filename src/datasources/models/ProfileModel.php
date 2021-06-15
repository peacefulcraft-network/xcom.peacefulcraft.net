<?php namespace pcn\xcom\datasources\models;

use DateTime;
use pcn\xcom\datasources\models\collections\ProfileLinkCollection;
use pcn\xcom\datasources\MySQLDatasource;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;
use RuntimeException;
use SplFixedArray;

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

	public static function createProfile(): ProfileModel {
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

		// created_at may not be accurate here, but it is mostly used for accounting so this is ok.
		// subsiquent fetches will use the database value which is accurate, this just avoids an extra query.
		$Profile = ProfileModel::wrap($profile_id, new DateTime('now'), new ProfileLinkCollection());

		return $Profile;
	}

	public static function fetchByIds(array $ids): ?SplFixedArray {
		$query = SELF::$_mysqli->prepare("SELECT * FROM `profile` WHERE `id`=?");
		$num_ids = count($ids);
		$ret = new SplFixedArray(count($ids));
		$meaingfulResult = false;

		for ($i=0; $i < $num_ids; $i++) {
			// Ignore invalid ids
			if (!is_int($ids[$i])) {
				continue;
			}
			$query->bind_param('i', $ids[$i]);
			$query->execute();
			$res = $query->get_result();
			if ($res->num_rows === 0) {
				$res->free();
				continue;
			}
			$ret[$i] = $res->fetch_object('\pcn\xcom\datasources\models\ProfileModel');
			$res->free();
			$meaingfulResult = true;
		}
		$query->close();

		return ($meaingfulResult)? $ret : null;
	}

	public static function fetchByProfileLink(ProfileLinkService $service, string $link): ?ProfileModel {
		$query = SELF::$_mysqli->prepare('
			SELECT `id`, `created_at`
			FROM `profile` LEFT JOIN `profile_link` ON `profile`.`id` = `profile_link`.`profile_id`
			WHERE `link_service`=? AND `link_identifier`=?
		');
		$query->bind_param('ss', $service, $link);
		$query->bind_result($id, $created_at);
		$query->execute();
		$query->store_result();
		if ($query->num_rows == 0) {
			$query->free_result();
			$query->close();
			return null;
		}

		$query->fetch();
		$query->free_result();
		$query->close();

		$Profile = self::fetchByIds([$id]);
		if ($Profile === null) { return null; }

		return $Profile[0];
	}
}
?>