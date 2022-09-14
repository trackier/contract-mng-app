<?php
use Shared\Services\Db;
use Framework\{Security};

class Signinguser extends Shared\Model
{
    
    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_email;
    /**
    * @column
    * @readwrite
    * @type text
   */
    protected $_fullname;
   /**
    * @column
    * @readwrite
    * @type integer
    */
    protected $_contact;
   

}
