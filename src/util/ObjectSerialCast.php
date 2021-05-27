<?php namespace pcn\xcom\util;

use InvalidArgumentException;

abstract class ObjectSerialCast {
	/**
	 * Cast an object into a different class.
	 * Credit: https://gist.github.com/borzilleri/960035
	 *
	 * Currently this only supports casting DOWN the inheritance chain,
	 * that is, an object may only be cast into a class if that class 
	 * is a descendant of the object's current class.
	 *
	 * This is mostly to avoid potentially losing data by casting across
	 * incompatable classes.
	 *
	 * @param object $object The object to cast.
	 * @param string $class The class to cast the object into.
	 * @return object
	 */
	public static function SerialCast(object $object, string $class) {
		if( !is_object($object) ) 
			throw new InvalidArgumentException('$object must be an object.');
		if( !is_string($class) )
			throw new InvalidArgumentException('$class must be a string.');
		if( !class_exists($class) )
			throw new InvalidArgumentException(sprintf('Unknown class: %s.', $class));

		/**
		 * This is a beautifully ugly hack.
		 *
		 * First, we serialize our object, which turns it into a string, allowing
		 * us to muck about with it using standard string manipulation methods.
		 *
		 * Then, we use preg_replace to change it's defined type to the class
		 * we're casting it to, and then serialize the string back into an
		 * object.
		 */
		return unserialize(
			preg_replace(
				'/^O:\d+:"[^"]++"/', 
				'O:'.strlen($class).':"'.$class.'"',
				serialize($object)
			)
		);
	}
}

?>