<?php namespace pcn\xcom\util;

use ReflectionFunction;
use ReflectionMethod;

abstract class ArrayUtils {

	/**
	 * Performans an in place merge of $arrays into $target.
	 * @param target Array to receive contents of $arrays
	 * @param arrays One or more arrays to merge into $target
	 */
	public static function array_merge_in_place(array &$target, array ...$arrays): void {
		foreach($arrays as $array) {
			foreach($array as $key => $value) {
				$target[$key] = $value;
			}
		}
	}

	/**
	 * Takes a function name and arguements passed to it and matches the
	 * positional args array up with the positional arguements of that function.
	 * Credit: https://stackoverflow.com/a/53736505
	 *
	 * @param function The name of the function to retreive args from
	 * @param args The list of arguements to match
	 * @return array An associative array with parameter=>args matched positionally.
	 */
	public static function ArgsToAssocArray(string $function, array $args): array {
		if (false === strpos($function, '::')) {
			$reflection = new ReflectionFunction($function);
		} else {
			$reflection = new ReflectionMethod(...explode('::', $function));
		}
		$assoc = [];
		foreach ($reflection->getParameters() as $i => $parameter) {
			$assoc[$parameter->getName()] = $args[$i];
		}
		return $assoc;
	}


	/**
	 * Compare two n deimensial arrays for equality.
	 * Note that because these are arrays, values as the same numeric index are forced to match. 
	 * [1, 2, 3] compared to [3, 2, 1] will always return false no matter the arguments passed.
	 * Note the implications this has on superset comparisons. [1, 2, 3] is technically a superset of [1, 3], 
	 * but the function will return false when these are compared with arrayCompare([1,3], [1,2,3], true, false)
	 * because arr1[1] != arr2[1]. Superset only considers indexes, not values at those indexes.
	 * 
	 * @param arr1 Expected array
	 * @param arr2 Generated, actual, or otherwise needs testing value
	 * @param allow_superset True: arr2 contains all of arr1, but may also contain additional keys not found in arr1
	 * 												False: arr1 and arr2 are identical
	 * @param strict Whether to check for exact matches or allow cohersion
	 * @return bool True for equality, false for inequality
	 */
	public static function array_compare(array $arr1, array $arr2, bool $allow_superset = false, bool $strict = true, string &$fail_reason = ''): bool {

		// No superset means keyset should match
		if (!$allow_superset) {
			/**
			 * array_diff_key only returns the entries of arg0 that are not in argN.
			 * If supersets are disabled, there should be no KEYS in arr2 that are not in arr1.
			 * This check does not account for the fact that there may be keys in arr1 that are not in arr2.
			 * The for loop is for determining that all keys in arr1 are in arr2 and that their values match.
			 */
			$keys = array_diff_key($arr2, $arr1);
			if (count($keys) > 0) {
				$fail_reason = 'Superset check failed: keys of arr1 and arr2 do not match: ' . implode(' ', array_keys($keys));
				return false;
			}
		}

		foreach($arr1 as $key=>$value) { // $arr1[$key] and $value are the same for our purposes here
			// Check the key exists
			if (array_key_exists($key, $arr2)) {

				// Check if we need to recursivly compare
				if (is_array($value)) {
					// Ensure we won't get any errors for array access on a non-array
					if (is_array($arr2[$key])) {

						// Recursivly compare
						if (!SELF::array_compare($value, $arr2[$key], $allow_superset, $strict, $fail_reason)) { return false; }

					// arr1 expects array, but arr2 doesn't have an array at $key
					} else {
						$fail_reason = "arr1 expected array at key $key, but arr2 had something else.";
						return false;
					}

				// Not an array, check for equality
				} else {
					if ($strict) {
						if ($value !== $arr2[$key]) {
							$fail_reason = "mismatch at key $key";
							return false;
						}
					} else {
						if ($value != $arr2[$key]) {
							$fail_reason = "mistmatch at key $key";
							return false;
						}
					}
				}

			// arr2 lacked a key from arr1
			} else {
				$fail_reason = "arr2 lacked key $key";
				return false;
			}
		}

		return true;
	}
}

?>
