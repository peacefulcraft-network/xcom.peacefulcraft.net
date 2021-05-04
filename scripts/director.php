<?php
use net\peacefulcraft\apirouter\ConsoleApplication;

require(__DIR__ . "/../vendor/autoload.php");
$console = new ConsoleApplication(true, [
	__DIR__ . '/../src/cli/commands'
]);
?>