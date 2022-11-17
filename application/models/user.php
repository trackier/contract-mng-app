<?php
use Shared\Services\Db;
use Framework\{Security};

class User extends Shared\Model
{
    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 * @validate required
	 */
	protected $_user_id;

	/**
	 * @column
	 * @readwrite
	 * @type text
	 *
	 * @label Name
	 */
	protected $_name;

	/**
	 * @column
	 * @readwrite
	 * @type text
	 *
	 * @label Employee Id
	 */
	protected $_emp_id;

	/**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @validate required
	 * @label Department
	 */
	protected $_department;

	/**
	 * @column
	 * @readwrite
	 * @type text
	 * @length 255
	 * @index
	 * 
	 * @validate required, min(8), max(255)
	 * @label Email Address
	 */
	protected $_email;

	/**
	 * @column
	 * @readwrite
	 * @type text
	 * @length 200
	 * 
	 * @validate max(200)
	 * @label phone number
	 */
	protected $_phone = null;

	/**
	 * @column
	 * @readwrite
	 * @type text
	 * @enum -> (user, admin)
     * @label role
	 */
	protected $_role;



    /**
    * @column
    * @readwrite
    * @type text
    * @length 100
    */
    protected $_password;
}
