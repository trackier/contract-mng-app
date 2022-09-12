<?php
namespace Framework;

use Defuse\Crypto\Crypto;

/**
 * This is a class which hashes the string for storing in the database and
 * also considering the Timing Leaks
 * Reference: https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
 *
 * Dependencies ------> php-mcyrpt, php-xml
 */
class Security {
	protected static $_algos = ['sha256', 'sha384'];

	protected static function _verifyAlgo($algo) {
		if (!in_array($algo, self::$_algos)) {
			throw new \Exception("Invalid Second argument algo");
		}
	}

	public static function generateToken($length = 20) {
		return bin2hex(random_bytes($length));
	}

	public static function hashStr($str, $algo = 'sha384') {
		self::_verifyAlgo($algo);

		return password_hash(
		    base64_encode(
		        hash($algo, $str, true)
		    ),
		    PASSWORD_DEFAULT
		);
	}

	public static function verifyHash($hashStr, $plainStr, $algo = 'sha384') {
		self::_verifyAlgo($algo);

		return password_verify(
		    base64_encode(
		        hash($algo, $plainStr, true)
		    ),
		    $hashStr
		);
	}

	public static function encrypt($data, $key, $useNewLogic = false) {
		if ($useNewLogic) {
			return Crypto::encryptWithPassword($data, $key);
		}
		return $data;
		// old logic is deprecated
		// $e = new Security\Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		// $hashed = $e->encrypt($data, $key);
		
		// return utf8_encode($hashed);
	}

	public static function decrypt($data, $key, $useNewLogic = false) {
		if ($useNewLogic) {
			return Crypto::decryptWithPassword($data, $key);
		}
		return $data;
		// old logic is deprecated
		// $data = utf8_decode($data);
		// $e = new Security\Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		// $normal = $e->decrypt($data, $key);

		// return $normal;
	}

	public static function uuidv4() {
		$data = random_bytes(16);
		assert(strlen($data) == 16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	public static function aesEncrypt($plaintext, $secret) {
		$hashKey = openssl_digest($secret, 'SHA256', FALSE);
		$key = substr($hashKey,0,32);
		$ivLen = openssl_cipher_iv_length("AES-256-CBC");
		$iv = openssl_random_pseudo_bytes($ivLen);
		$cipherTextRaw = openssl_encrypt($plaintext, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $cipherTextRaw, $key, TRUE);
		$cipherEncoded = base64_encode($cipherTextRaw);
		$ivEncoded = base64_encode($iv);
		$hmacEncoded = base64_encode($hmac);
		return $ivEncoded.$hmacEncoded.$cipherEncoded;
	}

	public static function aesDecrypt($data, $secret) {
		$hashKey = openssl_digest($secret, 'SHA256', FALSE);
		$key = substr($hashKey,0,32);
		$dataDecoded = base64_decode($data);
		$ivLen = openssl_cipher_iv_length("AES-256-CBC");
		$iv = substr($dataDecoded, 0, $ivLen);
		$cipherTextRaw = base64_decode(substr($data,68));
		$originalPlainText = openssl_decrypt($cipherTextRaw, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
		return $originalPlainText;
	}
	
}
