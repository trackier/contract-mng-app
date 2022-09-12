<?php
use Shared\Services\Db;
use Framework\{Security};

class User extends Shared\Model
{
    
    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_password;
    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    * @index
    */
    protected $_email;
    // public function comparePassword($pass) {
	// 	if ($this->password) {
	// 		$decryptedHash = Security::aesDecrypt($this->password);
	// 		$isMatching = hash_equals(sha1($pass), $decryptedHash ? $decryptedHash : '');
	// 		if ($isMatching) {
	// 			return true;
	// 		}
	// 	}
	// 	return hash_equals(sha1($pass), $this->password ?? '');
	// }

}
