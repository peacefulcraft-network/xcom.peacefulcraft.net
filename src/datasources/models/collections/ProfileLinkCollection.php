<?php namespace pcn\xcom\datasources\models\collections;

use pcn\xcom\datasources\models\ProfileLinkModel;
use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\enum\ProfileLinkService;
use RuntimeException;

/**
 * A model collection for wrapper a profile's linked service entries
 */
class ProfileLinkCollection {
	/**
	 * Parent profile which these links belong to
	 */
	private ProfileModel $profile;
		public function getProfile(): ProfileModel { return $this->profile; }

	private array $links;
		/**
		 * @return ProfileLinkModel the ProfileLinkModel for that service on this account.
		 * @return null If no link for the specified service exists.
		 */
		public function getProfileLink(ProfileLinkService $link_service): ?ProfileLinkModel {
			if (array_key_exists($link_service, $this->links)) {
				return $link_service[$link_service->_value];
			}

			return null;
		}

		/**
		 * Adds the service link to this ProfileLink collection.
		 * Passing null as the $link will remove the value
		 */
		public function setProfileLink(ProfileLinkService $link_service, ?ProfileLinkModel $link = null): void {
			if ($link === null) {
				unset($this->links[$link_service->_value]);
			} else {
				$this->links[$link_service->_value] = $link;
			}
		}

	/**
	 * @param profile The parent profile which these links belong to
	 * @param links An array of ProfileLinkModel objects reprsenting this $profile's service links.
	 */
	public function __construct(ProfileModel $profile, array $links = []) {
		$this->profile = $profile;

		foreach($links as $link) {
			if (!($link instanceof ProfileLinkModel)) {
				throw new RuntimeException('Contents of $links must be instnaceof ProfileLinkModel.');
			}
		}

		$this->links = $links;
	}
}

?>