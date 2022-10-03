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
    

}
