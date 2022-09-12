<?php
use Shared\Utils;
use Shared\Services\{Db};
use Framework\{ArrayMethods, Registry, TimeZone, Events, StringMethods, RequestMethods as RM};



class Contracttbl extends Shared\Model
{
    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_cname;

    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_type;

    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_company;
    /**
    * @column
    * @readwrite
   	* @type array
   */
    protected $_docInserted;

    /**
    * @column
    * @readwrite
   	* @type array
    */
    protected $_users;

    /**
    * @column
    * @readwrite
   	* @type date
    */
    protected $_startDate;

    /**
    * @column
    * @readwrite
   	* @type date
    */
    protected $_endDate;

    /**
    * @column
    * @readwrite
   	* @type text
    */
    protected $_notes;

    public function setName($name) {
        $this->_name = strtolower($name);
    }

    public function getName() {
        return ucfirst($this->_name);
    }

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
