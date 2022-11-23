<?php

namespace Models;

use Shared\Services\Db;
use Framework\{Security};

class Activity extends \Shared\Model
{
    
    /**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "activity";

    /**
    * @column
    * @readwrite
    * @type text
    * @validate required
    * @length 100
    * @index
    */
    protected $_name;

    /**
    * @column
    * @readwrite
    * @type text
    * @validate required
    * @length 100
    * @index
    */
    protected $_description;


    /**
    * @column
    * @readwrite
    * @validate required
   	* @type date
    */
    protected $_startDate;

    /**
    * @column
    * @readwrite
   	* @type date
    * @validate required
    */
    protected $_endDate;

    
    /**
    * @column
    * @readwrite
   	* @type array
    * @validate required
    */
    protected $_teamMembers;
    
    /**
	 * @column
	 * @readwrite
	 * @type text
	 * @index
	 * @validate required
	 */
	protected $_act_id;

    
}
