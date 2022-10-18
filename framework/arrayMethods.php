<?php

namespace Framework {

	/**
	 * Utility methods for working with the basic data types we ﬁnd in PHP
	 */
	class ArrayMethods {

		private function __construct() {
			# code...
		}

		private function __clone() {
			//do nothing
		}

		/**
		 * Useful for converting a multidimensional array into a unidimensional array.
		 *
		 * @param type $array
		 * @param type $return
		 * @return type
		 */
		public static function flatten($array, $return = array()) {
			foreach ($array as $key => $value) {
				if (is_array($value) || is_object($value)) {
					$return = self::flatten($value, $return);
				} else {
					$return[] = $value;
				}
			}
			return $return;
		}

		public static function first($array) {
			if (sizeof($array) == 0) {
				return null;
			}

			$keys = array_keys($array);
			return $array[$keys[0]];
		}

		public static function last($array) {
			if (sizeof($array) == 0) {
				return null;
			}

			$keys = array_keys($array);
			return $array[$keys[sizeof($keys) - 1]];
		}

		public static function toObject($array) {
			$result = new \stdClass();
			foreach ($array as $key => $value) {
				if (strlen($key) === 0) {
					continue;
				}
				if (is_array($value)) {
					$result->{$key} = self::toObject($value);
				} else {
					$result->{$key} = $value;
				}
			} return $result;
		}

		/**
		 * Convert an object to array recursively
		 * @param  object $object Object derived from simple class that can be used as array in foreach
		 * @return array         Final Array
		 */
		public static function toArray($object) {
			$arr = [];
			$obj = (array) $object;
			foreach ($obj as $key => $value) {
				if (is_object($value)) {
					$arr[$key] = self::toArray($value);
				} else {
					$arr[$key] = $value;
				}
			}
			return $arr;
		}

		/**
		 * Removes all values considered empty() and returns the resultant array
		 * @param type $array
		 * @return type the resultant array
		 */
		public static function clean($array) {
			return array_filter($array, function ($item) {
				return !empty($item);
			});
		}

		/**
		 * Returns an array, which contains all the items of the initial array, after they have been trimmed of all whitespace.
		 * @param type $array
		 * @return type array trimmed
		 */
		public static function trim($array) {
			return array_map(function ($item) {
				return trim($item);
			}, $array);
		}

		/**
		 * Rearranges the array keys
		 */
		public static function reArray(&$array) {
			$file_ary = array();
			$file_keys = array_keys($array);
			if (count($file_keys) == 0) {
				return [];
			}
			$file_count = count($array[$file_keys[0]]);

			for ($i = 0; $i < $file_count; $i++) {
				foreach ($file_keys as $key) {
					$file_ary[$i][$key] = $array[$key][$i];
				}
			}

			return $file_ary;
		}

		public static function copy(&$from, &$to) {
			foreach ($from as $key => $value) {
				$to[$key] = $value;
			}
		}

		public static function counter(&$arr, $key, $count) {
			if (! is_array($arr)) {
				$arr = [];
			}
			if (!array_key_exists($key, $arr)) {
				$arr[$key] = 0;
			}
			$arr[$key] += $count;
		}

		public static function add(&$from, &$to) {
			$to = static::_add($from, $to);
		}

		public static function fastAdd($from, $to) {
			if (is_array($from) || is_object($from)) {
				foreach ($from as $key => $value) {
					if (isset($to[$key])) {
						$to[$key] += floatval($value);
					} else {
						$to[$key] = floatval($value);
					}
				}
			}
			return $to;
		}

		protected static function _add($from, $to) {
			if (!is_array($to)) {
				$to = [];
			}
			foreach ($from as $key => $value) {
				if (!array_key_exists($key, $to)) {
					$to[$key] = 0;
				}
				if (is_string($value) && strlen($value) == 24) {
					continue;
				}

				if (is_numeric($value)) {
					$to[$key] += $value;
				} else if (is_array($value)) {
					if (!is_array($to[$key])) {
						$to[$key] = [];
					}
					foreach ($value as $k => $v) {
						if (is_numeric($v)) {
							$to[$key][$k] = ($to[$key][$k] ?? 0) + $v;
						}
					}
				}
			}
			return $to;
		}

		public static function addCopy($from, $to) {
			return static::_add($from, $to);
		}

		public static function topValues($arr, $count = 10, $order = 'desc') {
			$result = [];
			switch ($order) {
				case 'desc':
					arsort($arr);
					break;

				case 'asc':
					asort($arr);
					break;
			}

			$result = array_slice($arr, 0, $count);
			return $result;
		}

		/**
		 * Calculates the percentage of each key in the array
		 * @param  array  $arr    Array containing "key" => $count
		 * @param  integer $places To how many places the percentage should be round off
		 * @return array          Array containing "key" => percentage
		 */
		public static function percentage($arr, $places = 2) {
			$arr = self::topValues($arr, count($arr));
			$total = array_sum($arr);
			$result = [];

			if ($total == 0) {
				return $result;
			}

			foreach ($arr as $key => $value) {
				$result[$key] = number_format(($value / $total) * 100, $places);
			}
			return $result;
		}

		/**
		 * Function checks that all the values of $current array
		 * are present in $search array
		 * @param  array $search  Search Array
		 * @param  array $current Array to be tested against search array
		 * @param boolean $allElements All Elements in $current array should be present in $search array or not
		 * @return boolean
		 */
		public static function inArray($search, $current, $allElements = true) {
			$pass = true;
			if (! is_array($current)) {
				return false;
			}

			$current = array_unique($current);
			foreach ($current as $c) {
				if (!in_array($c, $search)) {
					$pass = false;
					break;
				}
			}

			// if size of $current is more than how can all of its elements be present in $search
			if (count($current) == 0 || ($allElements && count($current) > count($search))) {
				$pass = false;
			}
			return $pass;
		}

		public static function arrayKeys($arr = [], $key = null) {
			$ans = [];
			foreach ($arr as $k => $value) {
				if ($key) {
					$val = $value->$key ?? null;
					if ($val) {
						$ans[] = $val;
					}
				} else {
					$ans[] = $k;
				}
			}
			return $ans;
		}

		public static function assocArrayKeys($arr = [], $key = null) {
			$ans = [];
			foreach ($arr as $k => $value) {
				if ($key) {
					$val = $value[$key] ?? null;
					if ($val) {
						$ans[] = $val;
					}
				} else {
					$ans[] = $k;
				}
			}
			return $ans;
		}

		public static function assignDefault($arr, $indexes, $default = []) {
			foreach ($indexes as $i) {
				if (!isset($arr[$i])) {
					$arr[$i] = $default;
				}
			}
			return $arr;
		}

		/**
		 * Unset multiple keys from an array
		 * @param  array &$arr
		 * @param  array $keys Mulitple keys to be unset
		 */
		public static function removeKeys(&$arr, $keys = []) {
			foreach ($keys as $k) {
				unset($arr[$k]);
			}
			return $arr;
		}

		/**
		 * Remove multiple values from an array
		 * @param  array $arr    Master array containing all the values
		 * @param  array  $values Values to be removed if present in array
		 * @return array         Final Changed Array
		 */
		public static function removeValues($arr, $values = []) {
			if (!$arr) {
				return $arr;
			}
			foreach ($values as $v) {
				$index = array_search($v, $arr);

				if ($index !== false) {
					unset($arr[$index]);
				}
			}
			return array_values($arr);
		}

		public static function removeEmptyVals($arr) {
			$arr = array_map('trim', $arr);
			$arr = array_filter($arr);
			return $arr;
		}

		public static function modifyMap($arr, $mapKey = "") {
			$result = [];
			if (strlen($mapKey) === 0) {
				return $result;
			}
			foreach ($arr as $key => $value) {
				try {
					if (!is_object($value)) {
						throw new \Exception("Invalid Arguments provided");
					}
					$k = $value->$mapKey;
				} catch (\Exception $e) {
					$k = "";
				}
				$result[$k] = $value;
			}
			return $value;
		}

		public static function random($arr, $num = 1) {
			if (count($arr)) {
				shuffle($arr);

				$r = array();
				for ($i = 0; $i < $num; $i++) {
					$r[] = $arr[$i];
				}
				return $num == 1 ? $r[0] : $r;
			}
			return null;
		}

		public static function customImplode($input, $glue = ",") {
			$output = implode($glue, array_map(
				function ($v, $k) { return sprintf("%s=%s", $k, $v); },
				$input,
				array_keys($input)
			));
			return $output;
		}

		public static function arraySig($arr = []) {
			$signature = [];
			foreach ($arr as $key => $value) {
				if (is_array($value)) {
					$signature[$key] = self::arraySig($value);
				} else {
					$signature[$key] = sprintf("%s", $value);
				}
			}
			$k = self::customImplode($signature);
			return $k;
		}

		public static function mapChangeKey($arr, $old, $new) {
			return array_map(function ($v) use ($old, $new) {
				return static::changeKey($v, $old, $new);
			}, $arr);
		}

		/**
		 * Changed the key named $old to $new
		 * @param  array $arr Assoc array
		 * @param  string $old Name of the key to be replaced
		 * @param  string $new Key which replaces the old key
		 * @return array      Modified array
		 */
		public static function changeKey($arr, $old, $new) {
			if (isset($arr[$old])) {
				$arr[$new] = $arr[$old];
				unset($arr[$old]);	
			}
			return $arr;
		}

		public static function arraySum($arr, $key = null) {
			$result = 0;
			if ($key) {
				foreach ($arr as $obj) {
					$result += $obj[$key] ?? 0;
				}
			} else {
				$result = array_sum($arr);
			}
			return $result;
		}

		public static function filterObjects($superSet, $subSet, $primaryKey = '_id') {
			$result = [];
			$objectIds = [];
			foreach ($superSet as $obj) {
				$objectIds[$obj->$primaryKey] = $obj;
			}

			foreach ($subSet as $key) {
				if (isset($objectIds[$key])) {
					$result[$key] = $objectIds[$key];
				}
			}
			return $result;
		}

		public static function renameKey($arr, $oldKey, $newKey) {
			if (!array_key_exists($oldKey, $arr)) {
				return $arr;
			}
			$arr[$newKey] = $arr[$oldKey];
			unset($arr[$oldKey]);
			return $arr;
		}

		public static function customArraySort($field) {
			return function ($a, $b) use ($field) {
				$aField = $a[$field] ?? 0;
				$bField = $b[$field] ?? 0;
				if ($aField === $bField) {
					return 0;
				}
				return ($aField < $bField) ? 1 : -1;
			};
		}

		public static function diff(...$arguments) {
			$diff = array_diff(...$arguments);
			return array_values($diff);
		}

		public static function unique($arr) {
			return array_values(array_unique($arr));
		}

		/**
		 * MaxFloatVal loops over the array elements to check if there is any float value
		 * and if there is then finds the maximum of those values else returns zero
		 * @param  array $arr Mixed
		 * @return float
		 */
		public static function maxFloatVal($arr) {
			$result = [];
			foreach ($arr as $val) {
				$floatVal = StringMethods::parseFloat($val);
				if ($floatVal !== false) {
					$result[] = $floatVal;
				}
			}
			if (count($result) == 0) {
				return 0;
			}
			return max($result);
		}

		/**
		 * This function loops over the objects in an array and creates a map with index
		 * key and valKey
		 * @param  array $arr      Array of objects
		 * @param  string $indexKey Index property
		 * @param  string $valKey   Value property
		 * @return array           map[string]interface{}
		 */
		public static function createKeyMap($arr, $indexKey, $valKey) {
			$result = [];
			foreach ($arr as $obj) {
				$k = $obj->$indexKey ?? '';
				$result[$k] = $obj->$valKey ?? '';
			}
			return $result;
		}

		/**
		 * This function joins the array of string by the pipe operator so that the values
		 * can be used for regex matching
		 * @param  array  $v []string
		 * @return string
		 */
		public static function convertToRegexStr($v = []) {
			$str = "";
			if ($v) {
				$v = array_map('addslashes', $v);
				$str = implode("|", $v);
				$str = str_replace("| ", "|", $str);
				$str = str_replace(" |", "|", $str);
				$str = str_replace('&amp;', '&', $str);
			}
			return $str;
		}
	}
}
