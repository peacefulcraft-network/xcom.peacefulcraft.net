<?php namespace pcn\xcom\cli\commands;

use mysqli;
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
		Console::printLine("[tab][green]drop[normal][tab] Drop all tables in the xcom database.");
		Console::printLine("[tab][green]migrate[normal][tab] Check for any perform any needed migrations.");
		Console::printLine("[tab][green]populate[normal][tab]Populate the database with test data.");
	}

	public function getArgs(): array { return []; }

	public function execute(array $config, Console $console, array $args): int {
		if (count($args) === 0) {
			$this->printHelpMessage();
			Console::printLine("[red]No arguemnets supplied.");
			return E_ERROR;
		} 

		$docker_cmd = 'docker run --rm -i -t --network pcn-xcom-development -v ' . __DIR__ . '/../../../sql:/sql -w /sql -e AGNOSTIC_HOST=mariadb -e AGNOSTIC_USER=xcom -e AGNOSTIC_DATABASE=xcom -e AGNOSTIC_TYPE=mysql -e AGNOSTIC_MIGRATIONS_DIR=/sql registry.peacefulcraft.net/database-agnostic:latest agnostic --password xcom ';

		switch($args[0]) {
			case "drop":
				$mysqli = new mysqli("127.0.0.1", "xcom", "xcom", "xcom");
				$result = $mysqli->query("
					SELECT `table_name`
					FROM information_schema.tables
					WHERE table_schema = 'xcom';
				");
				$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
				foreach($result->fetch_all(MYSQLI_NUM) as $table) {
					$mysqli->query("DROP TABLE IF EXISTS {$table[0]};");
				}
				$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

				$mysqli->close();

			break; case "migrate":
				// Check for migrations table
				$mysqli = new mysqli("127.0.0.1", "xcom", "xcom", "xcom");
				$result = $mysqli->query("
					SELECT `table_name`
					FROM information_schema.tables
					WHERE table_schema = 'xcom' AND table_name='agnostic_migrations';
				");
				if ($result->num_rows === 0) {
					Console::printLine("[yellow]Migrations table not found. Creating it.");
					system($docker_cmd . 'bootstrap --no-load-existing');
				} else {
					Console::printLine("[green]Found existing migrations table.");
				}
				$mysqli->close();

				// Run migrations
				system($docker_cmd .  'migrate');

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