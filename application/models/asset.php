<?php

namespace Models;
use Shared\Services\Db;
use Framework\{Security};

class Asset extends \Shared\Model {

	/**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "asset";

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
     * @label asset type
     */
    protected $_asset_type;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @label Vendor
	 */
	protected $_ven_id;

    /**
	 * @column
	 * @readwrite
	 * @type date
	 * 
	 * @label purchase date
	 */
	protected $_pur_date;

    /**
	 * @column
	 * @readwrite
	 * @type text
	 */
	protected $_description;

    /**
	 * @column
	 * @readwrite
	 * @type text
	 *
	 * @label Status
	 * @value assigned, available, discarded 
	 */
	protected $_status;
}
