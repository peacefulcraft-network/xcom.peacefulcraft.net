<?php namespace pcn\xcom\datasources\models;

use pcn\xcom\datasources\MySQLDatasource;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;

class ProfileLinkModel extends MySQLDatasource {

	private int $profile_id;
		public function getProfileId(): int { return $this->profile_id; }

	private string|ProfileLinkService $link_service;
		public function getProfileLinkService(): ProfileLinkService { return $this->link_service; }

	private string $link_identifier;
		public function getProfileLinkIdentifier(): string { return $this->link_identifier; }

	private bool $is_speculative;
		public function isLinkSpeculative(): bool { return $this->is_speculative; }

	private string|ProfileLinkVisibility $link_visibility;
		public function getProfileLinkVisibility() { return $this->link_visibility; }

	protected function deserialize(): void {
		$this->link_service = new ProfileLinkService($this->link_service);
		$this->link_visibility = new ProfileLinkVisibility($this->link_visibility);
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