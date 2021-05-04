<?php namespace pcn\xcom\cli\commands;

use net\peacefulcraft\apirouter\console\Command;
use net\peacefulcraft\apirouter\console\Console;

class DatabaseMigrations implements Command {

	public function getName(): string {
		return "database";
	}

	public function getDescription(): string {
		return "Database creation, migrations, and managemnet.";
	}

	public function printHelpMessage(): void {
		Console::printLine("[blue]database [green]action[normal]");
		Console::printLine("[tab][green]create[normal][tab][tab]Create a new, empty database.");
		Console::printLine("[tab][green]migrate[normal][tab] Check for any perform any needed migrations.");
		Console::printLine("[tab][green]populate[normal][tab]Populate the database with test data.");
	}

	public function getArgs(): array { return []; }

	public function execute(Console $console, array $args): int {
		if (count($args) === 0) {
			$this->printHelpMessage();
			Console::printLine("[red]No arguemnets supplied.");
			return E_ERROR;
		}

		switch($args[0]) {
			case "create":

			break; case "migrate":

			break; case "populate":

			break; default:
				$this->printHelpMessage();
				Console::printLine("[red]Unknown database action [yellow]{$args[0]}.");
				return E_ERROR;
			break;
		}

		return 0;
	}
}
?>