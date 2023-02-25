<?php

namespace Models;

use Shared\Services\Db;
use Framework\{Security};

class Purchaseorder extends \Shared\Model
{
	
	/**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "purchaseorder";

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
	* @length 100
	* @index
	*/
	protected $_invoice_mood;

	/**
	* @column
	* @readwrite
   	* @type text
	*/
	protected $_name;

	/**
	* @column
	* @readwrite
   	* @type text
	*/
	protected $_description;

	/**
	* @column
	* @readwrite
	* @type mongoid
	* @index
	* @validate required
	*/
	protected $_vendor_id;

	/**
	 * @column
	 * @readwrite
	 * @index
	 * @type array
	 * @validate required
	 */
	protected $_amount;

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
}
