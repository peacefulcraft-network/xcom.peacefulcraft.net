<?php

use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileModel;

function createProfile(): ProfileModel {
	return ProfileModel::createProfile();
}

function createParty(?ProfileModel $leader, ?string $party_name = 'test party'): PartyModel {
	if ($leader == null) {
		$leader = createProfile();
	}
	return PartyModel::createParty($leader, 'Test party');
}