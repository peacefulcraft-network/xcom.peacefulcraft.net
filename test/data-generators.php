<?php

use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileLinkModel;
use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\enum\ProfileLinkService;
use pcn\xcom\enum\ProfileLinkVisibility;

CONST PROFILE_DEFAULT_MOJANG_UUID = '7464ffe3-fec1-4c1e-8cdc-7dcc688fb986';

function createProfile(): ProfileModel {
	return ProfileModel::createProfile();
}

function linkProfileToMojangId(ProfileModel $Profile, ?string $uuid = PROFILE_DEFAULT_MOJANG_UUID): ProfileLinkModel {
	return ProfileLinkModel::createProfileLink($Profile, new ProfileLinkService(ProfileLinkService::MOJANG), $uuid, false, new ProfileLinkVisibility(ProfileLinkVisibility::PUBLIC_VISIBLE));
}

function createParty(?ProfileModel $leader, ?string $party_name = 'test party'): PartyModel {
	if ($leader == null) {
		$leader = createProfile();
	}
	return PartyModel::createParty($leader, 'Test party');
}