<?php

namespace Models;
use Shared\Services\Db;
use Framework\{Security};

class Employee extends \Shared\Model {

	/**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "employee";

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

}