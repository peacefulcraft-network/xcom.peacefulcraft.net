<?php

use pcn\xcom\datasources\models\ProfileModel;

function createProfile(): ProfileModel {
	return ProfileModel::createProfile();
}