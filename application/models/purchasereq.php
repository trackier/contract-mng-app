<?php

namespace Models;

use Shared\Services\Db;
use Framework\{Security};

class Purchasereq extends \Shared\Model
{
    
    /**
	 * @readwrite
	 * @var string
	 */
	protected $_table = "purchasereq";

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
    protected $_paymentDate;

    /**
    * @column
    * @readwrite
   	* @type date
    */
    protected $_submittedOn;

    /**
    * @column
    * @readwrite
   	* @type date
    */
    protected $_expectedDate;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 * @validate required
	 */
	protected $_approver1_id;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 */
	protected $_activity_id;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 * @validate required
	 */
	protected $_approver2_id;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 * @validate required
	 */
	protected $_requester_id;

    /**
	 * @column
	 * @readwrite
	 * @type mongoid
	 * @index
	 * @validate required
	 */
	protected $_department;

    /**
	 * @column
	 * @readwrite
	 * @type text
	 * @index
	 * @validate required
	 */
	protected $_pr_id;

     /**
	 * @column
	 * @readwrite
	 * @index
     * @type text
	 * @validate required
	 */
	protected $_amount;

    /**
    * @column
    * @readwrite
   	* @type text
    */
    protected $_notes;

    /**
    * @column
    * @readwrite
   	* @type text
    */
    protected $_denialReason;

    /**
    * @column
    * @readwrite
   	* @type array
    */
    protected $_items;

    /**
    * @column
    * @readwrite
   	* @type array
    */
    protected $_docInserted;
	public static function groupBy($array, $groupBy) {
		$carry = [];
		foreach ($groupBy as $group) {
			$groupbyVal = $group;
			foreach($array as $key => $item) {
				if(!isset($carry[$item->$groupbyVal])){ 
					$carry[$item->$groupbyVal] = [$group=>$item->$groupbyVal, 'amount'=>$item->amount, 'count' => 1]; 
					foreach($groupBy as $group2) {
						if (($item->$group2)) {
							$carry[$item->$groupbyVal][$group2] = $item->$group2; 
						}
					}
				} else { 
					$carry[$item->$groupbyVal]['amount'] += $item->amount; 
					$carry[$item->$groupbyVal]['count'] += 1; 
				} 
			}
			break;
		}
		return $carry; 
	}
	
}
