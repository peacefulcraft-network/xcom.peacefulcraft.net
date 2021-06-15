<?php
use pcn\xcom\datasources\MySQLDatasource;

require_once(__DIR__ . '/../config/config.sample.php');
MySQLDatasource::init($config['mysql']);
?>