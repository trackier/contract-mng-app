<?php
use Shared\Services\Db;
use Framework\{Security};

class ContractFile extends Shared\Model
{
    
    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_filename;

    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_fileId;

    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    * @index
    */
    protected $_status;


    /**
    * @column
    * @readwrite
   	* @type date
    */
    protected $_dueDelDate;

   

}
