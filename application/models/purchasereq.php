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
     * @type array
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
	public static function groupBy($array, $groupBy, $fields = []) {
		$carry = [];
		foreach ($groupBy as $group) {
			$groupbyVal = $group;
			foreach($array as $key => $item) {
				if(!isset($carry[$item->$groupbyVal])){ 
					$carry[$item->$groupbyVal] = [$group=>$item->$groupbyVal, 'amount'=>[$item->_amount], 'count' => 1]; 
					
					
					foreach($groupBy as $group2) {
						if (($item->$group2)) {
							$carry[$item->$groupbyVal][$group2] = $item->$group2; 
						}
					}
					foreach($fields as $field) {
						if (!isset($carry[$item->$groupbyVal][$field]) && ($field!='count') && ($field!='amount')) {
							
							$carry[$item->$groupbyVal][$field] = $item->$field; 
							//var_dump($carry[$item->$groupbyVal][$field]);
						}
					}
					
				} else { 
					$carry[$item->$groupbyVal]['amount'][] = $item->_amount; 
					$carry[$item->$groupbyVal]['count'] += 1; 
				} 
			}
			break;
		}
		return $carry; 
	}

	public static function getAmountSingle($amountArr) {
		$amountArr = $amountArr->_amount;
		$amount;
		$amountStr = '';
		foreach ($amountArr as $key => $amt) {
			if (!isset($amount[$key])) {
				$amount[$key] =  $amt;
			} else {
				$amount[$key] = $amount[$key] +  $amt;
			}
		}
		foreach ($amount as $key => $amt) {
			$amountStr = $amountStr . $key .' '.  $amt . ' | ';
		}
		return $amountStr; 
	}

	public static function getAmountAll($amountArray) {
		
		$amountArray = $amountArray["amount"];
		$amount = [];
		$amountStr = '';
		foreach ($amountArray as $amountArr) {
			
			foreach ($amountArr as $key => $amt) {
				if (!isset($amount[$key])) {
					$amount[$key] =  $amt;
				} else {
					$amount[$key] = $amount[$key] +  $amt;
				}
			}
		}
		foreach ($amount as $key => $amt) {
			$amountStr = $amountStr . $key .' '.  $amt . ' | ';
		}
		return $amountStr; 
	}
	
}
