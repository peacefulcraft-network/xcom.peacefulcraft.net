<?php namespace pcn\xcom\datasources;

use DateTime;
use mysqli;
use net\peacefulcraft\apirouter\enum\Enum;
use net\peacefulcraft\apirouter\exceptions\ValidationException;
use RuntimeException;
use SplFixedArray;
use Stringable;

abstract class MySQLDatasource implements \JsonSerializable {
	protected static mysqli $_mysqli;

	/**
	 * Extending classes should override this with their own values
	 */
	CONST TABLE_NAME = '';
	CONST READABLE_FIELDS = [];
	CONST WRITEABLE_FIELDS = [];

	protected int $id;

	private array $_dirty_fields = [];
		/**
		 * Clears all markers for modified fields.
		 */
		protected function resetDirtyFields() {
			$this->_dirty_fields = [];
		}

	public static function init(array $config) {
		SELF::$_mysqli = new mysqli(
			$config['host'],
			$config['user'],
			$config['password'],
			$config['database'],
			$config['port']
		);
	}

	/**
	 * Hook called after fields have been populated during construction.
	 * Used to convert primative values into more complex constructs like Enums
	 * 
	 * We do not use __wakeup() here since the serialization process is not PHP serialize().
	 */
	abstract protected function deserialize(): void;

	/**
	 * Setter that allows access to specified private & protected class fields,
	 * wrapper them with update tracking that allows for partial updating and
	 * cache dependency tracking.
	 * 
	 * The field must appear in $updatable_fields to be mutable.
	 * 
	 * __set is ONLY invoked when property access would normally violate encapsulation.
	 * This means that access from within a class and it's children will not
	 * trigger this method, unless the field was private and access was attempted by a child.
	 */
	public function __set(string $name, mixed $value): void {
		if (!in_array($name, static::WRITEABLE_FIELDS, true)) {
			throw new RuntimeException("Attempted to update immutable field ${name}.");
		}

		if (isset($this->$name)) {
			/**
			 * Special diff checks.
			 * 
			 * Check that both fields are enums and make sure there is a meaningful difference.
			 * If $value is not an enum, an Exception will be thrown by the assignment below so we can just omitt the check.
			 */
			if ($this->$name instanceof Enum && $value instanceof Enum) {
				if ($this->$name->_value === $value->_value) { return; }

			/**
			 * If there are no comparison overrides, fallback to simple quality check to avoid unecessary writes.
			 */
			} else if ($this->$name === $value) { return; }

			/**
			 * Check for a validation hook and run it if one exists
			 */
			if (method_exists($this, "validate_$name")) {
				
				/**
				 * On invalid input, method should throw a ValidationException that bubbles up to caller.
				 * 
				 * Relies on late bindings. 'static' will resolve to the class which used to call this method
				 */
				static::{"validate_$name"}($value);
			}
		}

		$this->$name = $value;
		$this->_dirty_fields[$name] = true;
	}

	/**
	 * Getter that allows access to specified private & protected class fields.
	 * 
	 * The field must appear in $readable_fields to be accessable.
	 * 
	 * __get is ONLY invoked when property access would normally violate encapsulation.
	 * This means that access from within a class and it's children will not
	 * trigger this method, unless the field was private and access was attempted by a child.
	 */
	public function __get(string $name): mixed {
		if (!in_array($name, static::READABLE_FIELDS, true)) {
			throw new RuntimeException("Attempted to access private or protected field ${name}.");
		}

		if (!isset($this->$name)) { return null; }

		return $this->$name;
	}

	/**
	 * Basic pagination.
	 * @param array Columns to return in result set.
	 * @param string Column to order by.
	 * @param string Value to start pagination from.
	 * @param int How many rows to fetch
	 * @param bool True: ascending, False: descending
	 * @return SplFixedArray Array containing hydrated model objects.
	 * 
	 * @throws ValidationException if $colums or $order_by is not a valid column in this table.
	 * @throws RuntimeException if there is a database error. 
	 * 
	 */
	public static function fetchOrderedRange(array $columns, string $order_by, mixed $start, int $size, bool $ascending = true): SplFixedArray {
		$columns = SELF::resolveColumnSubset($columns);
		if (!property_exists(static::class, $order_by)) {
			throw new ValidationException("Unknown property ${order_by}", -1);
		}
		$operator = ($ascending)? '<' : '>';

		$query = SELF::$_mysqli->prepare("SELECT ${columns} FROM " . static::TABLE_NAME . " WHERE ${order_by}${operator}? ORDER BY ${order_by} LIMIT ${size}");
		
		$types_string = '';
		$binds = [];
		SELF::bind_param_string([$start], $types_string, $binds);
		$query->bind_param($types_string, ...$binds);

		if (!$query->execute()) {
			$query->close();
			throw new RuntimeException('Intenral database error. Unable to fetch ordered range.');
		}

		$res = $query->get_result();
		$query->close();
		if ($res->num_rows === 0) {
			$res->free();
			return new SplFixedArray(0);
		}
		
		$ret = new SplFixedArray($res->num_rows);
		for ($i=0; $obj = $res->fetch_object(static::class); $i++) {
			$obj->deserialize();
			$ret[$i] = $obj;
		}
		$res->free();
		
		return $ret;
	}

	/**
	 * Write any staged updates to the database. If there are no pending updates, method returns immediatly.
	 */
	public function commit(): int {
		$num_updates = count($this->_dirty_fields);
		if ($num_updates === 0) { return 0; }

		$query = 'UPDATE ' . static::TABLE_NAME . ' SET';
		$values = [];
		$type_string = "";
		$binds = [];
		foreach($this->_dirty_fields as $field=>$_) {
			// Add column to query
			$query = "${query} `${field}`=?,";
			$values[] = $this->$field;
		}
		SELF::bind_param_string($values, $type_string, $binds);
		$query = substr($query, 0, -1);	// remove trailing ','
		$query = "${query} WHERE `id`=?";

		$type_string = "${type_string}i";	// add an 'i' for the id field.
		array_push($binds, $this->id);

		$query = SELF::$_mysqli->prepare($query);
		$query->bind_param($type_string, ...$binds);
		$query->execute();
		$query->store_result();
		$num_rows = $query->num_rows;
		$query->free_result();
		$query->close();

		$this->_dirty_fields = [];

		return $num_rows;
	}

	/**
	 * In place generation of mysqli::bind_param parameters from $values array.
	 * @param array Values to bind with
	 * @param string String to populate with 'sib' types.
	 * @param array Array to hold variables that will be bound. Pass to bind_params with unpack operator (...). 
	 */
	protected static function bind_param_string(array $values, string &$type_string, array &$binds): void {
		foreach($values as $value) {
			if (is_string($value) || $value instanceof Stringable) {
				$type_string = "${type_string}s";
				array_push($binds, strval($value));

			} elseif (is_int($value)) {
				$type_string = "${type_string}i";
				array_push($binds, $value);

			} elseif ($value === null) {
				$type_string = "${type_string}s";
				array_push($binds, null);

			} elseif ($value instanceof DateTime) {
				$type_string = "${type_string}s";
				array_push($binds, $value->format('Y-m-d H:i:s'));

			}else {
				$type_string = "${type_string}b";
				array_push($binds, $value);

			}
		}
	}

	/**
	 * DO NOT PASS USER INPUT TO THIS FUNCTION.
	 * 
	 * Utility method used to facilitate safe usage of the $subset arguement on fetch...() methods.
	 * 
	 * Note that ensuring perfect execution of a model in any given permutation of initialized properties is not feasible.
	 * This feature is intended to be used to optimize small fetch routes and simple operations where the primary time-sink
	 * is externious SQL work. For complex operations with models, omitting the $subset arguement and fetching all fields
	 * is still advised. Generally this feature will also not work intuitivly with JOIN queries and is better left off in that case.
	 * 
	 * Takes an array of column names to fetch, validates them, and then returns a string of column names CSVd
	 * @param subset Array of strings of column names to fetch. An empty array will default to '*'
	 */
	protected static function resolveColumnSubset(array $subset = []): string {
		if (count($subset) === 0) { return '*'; }

		$ret = '';
		foreach($subset as $column) {
			/**
			 * This check is a compromise between security, convenience, and speed.
			 * 1. There will be class properties that exist that are not database column names.
			 * 2. We do not have a FULL list of database columns, only those that are publically exposed and writeable.
			 * 3. Enumerating another set of columns is tedious and doesn't actually gain that much because of point 4.
			 * 4. This check should still protect against SQL injections so the worst case scenario is a query fails because a developer
			 *    includes an erronious value in the $subset array that is also a property name on this class. Passing user input to $subset
			 *    is still NOT advised under any circumstance.
			 * 
			 * property_exists is a theoretically more efficient way of checking for class properties over ReflectionClass w/ array_compare().
			 */
			if (property_exists(static::class, $column)) {
				$ret = "${ret}${column},";
			} else {
				throw new ValidationException("Column ${column} is not known to datamodel " . SELF::class . " an can not be instantiated as part of model subset", -1);
			}
		}

		// strip of last ',' and return
		return substr($ret, 0, -1);
	}

	/**
	 * Utility method that generates an associative array containing all the fields found in $_readable_fields.
	 * In most cases, this method can be used as a complete \JsonSerializable implementation if not at least a base.
	 * 
	 * @return array An associative array containg all property values found in $_readable_fields
	 */
	public function jsonSerialize() {
		$arr = array();

		foreach(static::READABLE_FIELDS as $prop) {
			if (isset($this->$prop)) {
				$arr[$prop] = $this->$prop;
			}
		}

		return $arr;
	}

	/**
	 * Cleans up MySQL resources and closes the MySQLi connection.
	 */
	public static function teardown() {
		SELF::$_mysqli->close();
	}
}
?>