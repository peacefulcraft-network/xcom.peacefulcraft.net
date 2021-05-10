<?php namespace pcn\xcom\datasources\models;

use pcn\xcom\datasources\MySQLDatasource;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;
use RuntimeException;

class ProfileLinkModel extends MySQLDatasource {

	private int $profile_id;
		public function getProfileId(): int { return $this->profile_id; }

	private string|ProfileLinkService $link_service;
		public function getProfileLinkService(): ProfileLinkService { return $this->link_service; }

	private string $link_identifier;
		public function getProfileLinkIdentifier(): string { return $this->link_identifier; }

	private bool $is_speculative;
		public function isLinkSpeculative(): bool { return $this->is_speculative; }

	/**
	 * Type string|ProfileLinkVisibility to remain compatible with MySQLIResult->fetchObject()
	 * Once object is instantiated, deserialize() is called which wraps the value in an Enum.
	 */
	private string|ProfileLinkVisibility $link_visibility;
		public function getProfileLinkVisibility() { return $this->link_visibility; }

	protected function deserialize(): void {
		$this->link_service = new ProfileLinkService($this->link_service);
		$this->link_visibility = new ProfileLinkVisibility($this->link_visibility);
	}

	public function __construct(?int $profile_id, ?ProfileLinkService $link_identifier, ?bool $is_speculative) {
		/**
		 * If PK is null, assume this is instatiation by MySQLiResult->fetchObject().
		 * Values will be populated automatically and processed by $this->deserialize().
		 */
		if ($profile_id === null) { return; }

		if ($link_identifier === null) {
			throw new RuntimeException("link_identifier must be non-null.");
		}

		if ($is_speculative === null) {
			throw new RuntimeException("is_speculative must be non-null");
		}
			
		$this->profile_id = $profile_id;
		$this->link_identifier = $link_identifier;
		$this->is_speculative = $is_speculative;
	}

	public function jsonSerialize(): mixed {
		return [
			'profile_id' => $this->profile_id,
			'link_service' => $this->link_service,
			'link_identifier' => $this->link_identifier,
			'is_speculative' => $this->is_speculative,
			'link_visibility' => $this->link_visibility,
		];
	}
}

?>