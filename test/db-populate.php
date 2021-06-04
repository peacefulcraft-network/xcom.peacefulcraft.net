<?php

use pcn\xcom\datasources\models\ProfileModel;
use pcn\xcom\datasources\MySQLDatasource;

require_once(__DIR__ . '/../config/config.sample.php');
MySQLDatasource::init($config['mysql']);

$ParsonswyProfile = ProfileModel::createProfile('7464ffe3-fec1-4c1e-8cdc-7dcc688fb986');

?>