<?php namespace pcn\xcom\datasources\models;

use pcn\xcom\datasources\MySQLDatasource;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;

class ProfileLinkModel extends MySQLDatasource {

	CONST TABLE_NAME = 'profile_link';
	CONST READABLE_FIELDS = ['profile_id', 'link_service', 'is_speculative', 'link_visibility'];
	CONST WRITEABLE_FIELDS = ['is_speculative', 'link_visibility'];

	protected int $profile_id;
	protected string|ProfileLinkService $link_service;
	protected string $link_identifier;
		public function remoteIdentifieryVerify(string $identifier): bool
			{ return $this->link_identifier === $identifier; }
	protected bool $is_speculative;

	/**
	 * Type string|ProfileLinkVisibility to remain compatible with MySQLIResult->fetchObject()
	 * Once object is instantiated, deserialize() is called which wraps the value in an Enum.
	 */
	private string|ProfileLinkVisibility $link_visibility;

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
}

?>