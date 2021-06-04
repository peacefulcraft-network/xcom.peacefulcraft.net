<?php

use pcn\xcom\datasources\models\PartyModel;
use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\datasources\MySQLDatasource;

require_once(__DIR__ . '/../config/config.sample.php');
MySQLDatasource::init($config['mysql']);

$ParsonswyProfile = ProfileModel::createProfile('7464ffe3-fec1-4c1e-8cdc-7dcc688fb986');

// Used in controllers\party\GetPartyTest
// Used in controllers\party\DeletePartyTest
$PartyLeaderProfile = ProfileModel::createProfile('769db47e-3388-416e-9adf-2cfe34ade534');
$Party = PartyModel::createParty($PartyLeaderProfile->id, 'Staff Team');

// Used in controllers\party\CreatePartyTest
$PartyLessProfile = ProfileModel::createProfile('34d29875-43bf-4a08-8256-e5bf27e0bf19');
?>