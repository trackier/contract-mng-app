<?php

namespace Models;
use Shared\Services\Db;
use Framework\{Security};

class Assigned extends \Shared\Model {

	/**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "assigned";

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
	 * @type mongoid
	 * @label Asset
	 */
	protected $_asset_id;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @label Employee
	 */
	protected $_emp_id;

    /**
	 * @column
	 * @readwrite
	 * @type datetime
	 * @label Handover Date
	 */
	protected $_handover_date;

    /**
	 * @column
	 * @readwrite
	 * @type datetime
	 * @label Assign Date
	 */
	protected $_assign_date;


}