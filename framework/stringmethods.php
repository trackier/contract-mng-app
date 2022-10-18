<?php

namespace Framework {

	/**
	 * Utility methods for working with the basic data types we ﬁnd in PHP
	 */
	class StringMethods {
		const DELIMITER = '_SWIFTMVC_FRAMEWORK_';

		/**
		 *for the normalization of regular expression strings, so that the remaining methods can operate on them 
		 * without ﬁrst having to check or normalize them.
		 * @var string 
		 */
		private static $_delimiter = "#";
		private static $_singular = array(
			"(matr)ices$" => "\\1ix",
			"(vert|ind)ices$" => "\\1ex",
			"^(ox)en" => "\\1",
			"(alias)es$" => "\\1",
			"([octop|vir])i$" => "\\1us",
			"(cris|ax|test)es$" => "\\1is",
			"(shoe)s$" => "\\1",
			"(o)es$" => "\\1",
			"(bus|campus)es$" => "\\1",
			"([m|l])ice$" => "\\1ouse",
			"(x|ch|ss|sh)es$" => "\\1",
			"(m)ovies$" => "\\1\\2ovie",
			"(s)eries$" => "\\1\\2eries",
			"([^aeiouy]|qu)ies$" => "\\1y",
			"([lr])ves$" => "\\1f",
			"(tive)s$" => "\\1",
			"(hive)s$" => "\\1",
			"([^f])ves$" => "\\1fe",
			"(^analy)ses$" => "\\1sis",
			"((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$" => "\\1\\2sis",
			"([ti])a$" => "\\1um",
			"(p)eople$" => "\\1\\2erson",
			"(m)en$" => "\\1an",
			"(s)tatuses$" => "\\1\\2tatus",
			"(c)hildren$" => "\\1\\2hild",
			"(n)ews$" => "\\1\\2ews",
			"([^u])s$" => "\\1"
		);
		private static $_plural = array(
			"^(ox)$" => "\\1\\2en",
			"([m|l])ouse$" => "\\1ice",
			"(matr|vert|ind)ix|ex$" => "\\1ices",
			"(x|ch|ss|sh)$" => "\\1es",
			"([^aeiouy]|qu)y$" => "\\1ies",
			"(hive)$" => "\\1s",
			"(?:([^f])fe|([lr])f)$" => "\\1\\2ves",
			"sis$" => "ses",
			"([ti])um$" => "\\1a",
			"(p)erson$" => "\\1eople",
			"(m)an$" => "\\1en",
			"(c)hild$" => "\\1hildren",
			"(buffal|tomat)o$" => "\\1\\2oes",
			"(bu|campu)s$" => "\\1\\2ses",
			"(alias|status|virus)" => "\\1es",
			"(octop)us$" => "\\1i",
			"(ax|cris|test)is$" => "\\1es",
			"s$" => "s",
			"$" => "s"
		);

		private function __construct() {
			// do nothing
		}

		private function __clone() {
			// do nothing
		}

		/**
		 * For the normalization of regular expression strings, so that the remaining methods can operate on them
		 * without ﬁrst having to check or normalize them. 
		 * @param string $pattern
		 * @return string
		 */
		private static function _normalize($pattern) {
			return self::$_delimiter . trim($pattern, self::$_delimiter) . self::$_delimiter;
		}

		public static function getDelimiter() {
			return self::$_delimiter;
		}

		public static function setDelimiter($delimiter) {
			self::$_delimiter = $delimiter;
		}

		/**
		 * Perform similarly to the preg_match_all() and preg_split() functions, but require less formal structure to the regular expressions,
		 * and return a more predictable set of results
		 * 
		 * @param string $string
		 * @param string $pattern
		 * @return array return the ﬁrst captured substring, the entire substring match, or null
		 */
		public static function match($string, $pattern) {
			preg_match_all(self::_normalize($pattern), $string, $matches, PREG_PATTERN_ORDER);

			if (!empty($matches[1])) {
				return $matches[1];
			}

			if (!empty($matches[0])) {
				return $matches[0];
			}

			return null;
		}

		/**
		 * Perform similarly to the preg_split() functions, but require less formal structure to the regular expressions,
		 * and return a more predictable set of results
		 * 
		 * @param string $string
		 * @param string $pattern
		 * @param int $limit
		 * @return array|false return the results of a call to the preg_split() function, after setting some ﬂags and normalizing the regular expression.
		 */
		public static function split($string, $pattern, $limit = null) {
			$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
			return preg_split(self::_normalize($pattern), $string, $limit, $flags);
		}

		public static function sanitize($string, $mask) {
			if (is_array($mask)) {
				$parts = $mask;
			} else if (is_string($mask)) {
				$parts = str_split($mask);
			} else {
				return $string;
			}

			foreach ($parts as $part) {
				$normalized = self::_normalize("\\{$part}");
				$string = preg_replace(
						"{$normalized}m", "\\{$part}", $string
				);
			}

			return $string;
		}

		public static function unique($string) {
			$unique = "";
			$parts = str_split($string);

			foreach ($parts as $part) {
				if (!strstr($unique, $part)) {
					$unique .= $part;
				}
			}

			return $unique;
		}

		public static function indexOf($string, $substring, $offset = null) {
			$position = strpos($string, $substring, $offset);
			if (!is_int($position)) {
				return -1;
			}
			return $position;
		}

		public static function lastIndexOf($string, $substring, $offset = null) {
			$position = strrpos($string, $substring, $offset);
			if (!is_int($position)) {
				return -1;
			}
			return $position;
		}

		public static function singular($string) {
			$result = $string;

			foreach (self::$_singular as $rule => $replacement) {
				$rule = self::_normalize($rule);

				if (preg_match($rule, $string)) {
					$result = preg_replace($rule, $replacement, $string);
					break;
				}
			}

			return $result;
		}

		public static function plural($string) {
			$result = $string;

			foreach (self::$_plural as $rule => $replacement) {
				$rule = self::_normalize($rule);

				if (preg_match($rule, $string)) {
					$result = preg_replace($rule, $replacement, $string);
					break;
				}
			}

			return $result;
		}

		public static function dateTimeDiff($start, $end) {
			$day_1 = date_create($start);
			$day_2 = date_create($end);

			$interval = date_diff($day_1, $day_2);
			return $interval->format('%a');
		}

		/**
		 * @deprecated Use TimeZone::printDateTime() instead
		 * @param  string $datetime [description]
		 * @param  [type] $user     [description]
		 * @return [type]           [description]
		 */
		public static function datetime_to_text($datetime = "", $user = null) {
			if (is_object($datetime) && is_a($datetime, 'DateTime')) {
				if (is_object($user)) {
					$datetime = $user->timeZone($datetime);
				}
				return $datetime->format('F j\, o \a\t g\:i a');
			} else if ($datetime == '0000-00-00 00:00:00') {
				return "Not Specified";
			} else {
				$unixdatetme = strtotime($datetime);
				return strftime("%B %d %Y at %I:%M %p", $unixdatetme);
			}
		}

		/**
		 * @deprecated Use TimeZone::printDate instead
		 * @param  string $datetime [description]
		 */
		public static function only_date($datetime = "") {
			if (is_object($datetime) && is_a($datetime, 'DateTime')) {
				$datetime = date('Y-m-d H:i:s', $datetime->getTimestamp());
			}
			if ($datetime == '0000-00-00 00:00:00') {
				return 'Not Specified';
			} else {
				$unixdatetme = strtotime($datetime);
				return strftime("%B %d, %Y", $unixdatetme);
			}
		}

		public static function url($url) {
			$pattern = array(' ', '?', '.', ':', '\'', '/', '(', ')', ',', '&');
			$replace = array('-', '', '', '', '', '', '', '', '', '');
			return urlencode(str_replace($pattern, $replace, $url));
		}

		/**
		 * Generates Unique Random string
		 */
		public static function uniqRandString($length = 22) {
			$unique_random_string = md5(uniqid(mt_rand(), true));
			$base64_string = base64_encode($unique_random_string);
			$modified_base64_string = str_replace('+', '.', $base64_string);
			$salt = substr($modified_base64_string, 0, $length);

			return $salt;
		}

		public static function utcDateTime($date, $dt) {
			return TimeZone::utcDateTime($date, $dt);
		}

		public static function tzConverter($dt, $extra = []) {
			return TimeZone::zoneConverter($dt, $extra);
		}

		public static function maskIp($ip) {
			$parts = explode(".", $ip);
			$lastIndex = count($parts) - 1;
			$parts[$lastIndex] = "xxx";
			return implode(".", $parts);
		}

		public static function utf8Clean($str) {
			$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
			$str = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $str);
			return $str;
		}

		public static function replaceUnderscore($str, $replace = " ") {
			$parts = explode("_", $str);
			return implode($replace, $parts);
		}

		public static function makePrimaryKey($obj, $groupBy = []) {
			$pieces = [];
			foreach ($groupBy as $key) {
				if (isset($obj[$key]) && is_string($obj[$key]) && strlen($obj[$key]) == 0) {
					$v = ' ';
				} else {
					$v = $obj[$key] ?? ' ';
				}
				$pieces[] = $v;
			}
			return implode(static::DELIMITER, $pieces);
		}

		/**
		 * Converts floating point number to rational representation
		 * Reference --> https://stackoverflow.com/questions/14330713/converting-float-decimal-to-fraction
		 * @param  float $n         Number
		 * @param  float  $tolerance Tolerance
		 * @return string
		 */
		public static function float2rat($n, $tolerance = 1.e-6) {
			$h1=1; $h2=0;
			$k1=0; $k2=1;
			$b = 1/$n;
			do {
				$b = 1/$b;
				$a = floor($b);
				$aux = $h1; $h1 = $a*$h1+$h2; $h2 = $aux;
				$aux = $k1; $k1 = $a*$k1+$k2; $k2 = $aux;
				$b = $b-$a;
			} while (abs($n-$h1/$k1) > $n*$tolerance);

			return "$h1/$k1";
		}

		/**
		 * Parse Float checks if the string contains any floating point number
		 * if it does then returns the first match else returns false on failure
		 * @param  string $val String value
		 * @return float|bool      Floating point number on match, else false
		 */
		public static function parseFloat($val) {
			$pattern = '/(\-?([0-9]+)(\.[0-9]+)?)/';
			preg_match($pattern, $val, $matches);
			if (isset($matches[1])) {
				return (float) $matches[1];
			}
			return false;
		}

		/**
		 * XorEncryptDecrypt runs a XOR encryption on the input string, encrypting it if it
		 * hasn't already been, and decrypting it if it has, using the key provided
		 *
		 * Source: https://github.com/KyleBanks/XOREncryption/blob/master/PHP/XOREncryption.php
		 * https://github.com/KyleBanks/XOREncryption/blob/master/LICENSE
		 * @param  string $input Input string
		 * @param  string $key   Key to decode or encode with
		 * @return string        Modified input
		 */
		public static function xorEncryptDecrypt($input, $key) {
			$inputLen = strlen($input);
			$keyLen = strlen($key);

			if ($inputLen <= $keyLen) {
				return $input ^ $key;
			}

			for ($i = 0; $i < $inputLen; ++$i) {
				$input[$i] = $input[$i] ^ $key[$i % $keyLen];
			}
			return $input;
		}
	}
}
