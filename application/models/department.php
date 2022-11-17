<?php

namespace Models;
use Shared\Services\Db;
use Framework\{Security};

class Department extends \Shared\Model {

	/**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "department";

	/**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 * @validate 
	 */
	protected $_team_lead_id;

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
	 * @label description
	 */
	protected $_description;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 * @validate required
	 */
	protected $_user_id;

}