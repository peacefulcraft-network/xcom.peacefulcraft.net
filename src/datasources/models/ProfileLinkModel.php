<?php namespace pcn\xcom\datasources\models;

use pcn\xcom\datasources\MySQLDatasource;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;
use RuntimeException;
use SplFixedArray;

class ProfileLinkModel extends MySQLDatasource {

	CONST TABLE_NAME = 'profile_link';
	CONST READABLE_FIELDS = ['profile_id', 'link_service', 'link_identifier', 'is_speculative', 'link_visibility'];
	CONST WRITEABLE_FIELDS = ['is_speculative', 'link_visibility'];

	protected int $profile_id;
	/**
	 * Type string|ProfileLinkService to remain compatible with MySQLIResult->fetchObject()
	 * Once object is instantiated, deserialize() is called which wraps the value in an Enum.
	 */
	protected string|ProfileLinkService $link_service;
	protected string $link_identifier;
	protected bool $is_speculative;

	/**
	 * Type string|ProfileLinkVisibility to remain compatible with MySQLIResult->fetchObject()
	 * Once object is instantiated, deserialize() is called which wraps the value in an Enum.
	 */
	protected string|ProfileLinkVisibility $link_visibility;

	// Disable constructor
	private function __construct() {}

	public static function wrap(int $profile_id, ProfileLinkService $link_service, string $link_identifier, bool $is_speculative, ProfileLinkVisibility $link_visibility): ProfileLinkModel {
		$ProfileLink = new ProfileLinkModel();
		$ProfileLink->profile_id = $profile_id;
		$ProfileLink->link_service = $link_service;
		$ProfileLink->link_identifier = $link_identifier;
		$ProfileLink->is_speculative = $is_speculative;
		$ProfileLink->link_visibility = $link_visibility;

		return $ProfileLink;
	}

	protected function deserialize(): void {
		$this->link_service = new ProfileLinkService($this->link_service);
		$this->link_visibility = new ProfileLinkVisibility($this->link_visibility);
	}

	public static function createProfileLink(ProfileModel $Profile, ProfileLinkService $link_service, string $link, bool $is_speculative, ProfileLinkVisibility $visibility): ProfileLinkModel {
		// Create account link
		$query = SELF::$_mysqli->prepare('INSERT INTO `profile_link` VALUES(?,?,?,?,?)');

		$query->bind_param('issis', $Profile->id, $link_service, $link, $is_speculative, $visibility);
		$query->execute();
		$query->store_result();
		if ($query->affected_rows !== 1) {
			error_log(SELF::$_mysqli->error, SELF::$_mysqli->errno);
			$query->close();
		
			throw new RuntimeException('Error creating account link. Profile create failed.');
		}
		$query->close();

		$ProfileLink = ProfileLinkModel::wrap($Profile->id, $link_service, $link, $is_speculative, $visibility);
		return $ProfileLink;
	}

	public static function fetchProfileLink(ProfileLinkService $service, string $link): ?ProfileLinkModel {
		$query = SELF::$_mysqli->prepare('SELECT * FROM `profile_link` WHERE `link_service`=? AND `link_identifier`=?');
		$query->bind_param('ss', $service, $link);
		$query->execute();
		$res = $query->get_result();
		if ($res->num_rows === 0) {
			$res->free();
			$query->close();
			return null;
		}

		$Link = $res->fetch_object(SELF::class);
		return $Link;
	}

	public static function fetchProfileLinksByProfile(ProfileModel $Profile): SplFixedArray {
		$query = SELF::$_mysqli->prepare('SELECT * FROM `profile_link` WHERE `profile_id`=?');
		$query->bind_param('i', $Profile->id);
		$query->execute();
		$res = $query->get_result();

		$linkObs = new SplFixedArray($res->num_rows);
		$i = 0;
		for ($i=0; $j = $res->fetch_object(SELF::class); $i++) {
			$j->deserialize();
			$linkObs[$i] = $j;
		}

		$res->free();
		$query->close();
		return $linkObs;
	}
}

?>