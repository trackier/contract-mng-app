<?php
use Shared\Controller as Controller;
use Framework\{Registry, TimeZone, ArrayMethods};
use Shared\Services\Db;
use Framework\RequestMethods as RequestMethods;

class Purchaseorder extends Controller { 

	/**
	 * @before _secure
	 */
	public function manage() {	
		
		$this->seo(["title" => "Manage Purchase Order"]); 
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		if ($this->user->role == 'admin') {
			$vendors = \Models\vendor::selectAll([], [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
			$purchaseOrder = \Models\purchaseorder::selectAll([], [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		} else {
			$vendors = \Models\vendor::selectAll(['user_id' => $this->user->_id], [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
			$purchaseOrder = \Models\purchaseorder::selectAll(['user_id' => $this->user->_id], [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		}

		$view->set("vendors", $vendors); 
		$view->set("purchaseOrder", $purchaseOrder);       
	
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will add Purchase Order
	 * - Return message
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function add($id = null) {
		$this->seo(["title" => "Add Purchase Order"]); 
		$vendors = \Models\vendor::selectAll([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$view = $this->getActionView();
		if ($id) {
			$purchaseOrder = \Models\purchaseorder::findById($id);
			if (!$id) {
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Please add a Purchase Order']);
				$this->redirect('/purchaseorder/add');
			}
		}
		try {
			if ($this->request->isPost()) {
				$total = [];
				$data = $this->request->post('data', []);
				if ($purchaseOrder) {
					$order = $purchaseOrder;
				} else {
					$order = new \Models\purchaseorder(['user_id' => $this->user->_id]);
				}
				$order->name = $data['name'];
				$order->invoice_mood = $data['invoice_mood'];
				$order->description = $data['desc'];
				$order->endDate = $data['endDate'];
				$order->startDate = $data['startDate'];
				$order->vendor_id = $data['vendor'];
				$total[$data['currency']] = $data['amount'];
				$order->amount = $total;
				$order->save();
				if  ($id) { 
					\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Purchase Order Updated successfully']);
				} else { 
					\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Purchase Order Added successfully']);
					$this->redirect('/purchaseorder/manage');
				}
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
		if ($id) {
			foreach ($purchaseOrder->amount as $key => $value) {
				$curr = $key; $val = $value;
			}
			$view->set('purchaseOrder', $purchaseOrder)
				->set('curr', $curr)
				->set('val', $val);
		}
		$view->set('vendors', $vendors);
	}


	/**
	 * [PUBLIC] This function will delete purchase order .
	 * @param $id
	 * @before _secure
	 */
	public function delete($id) {
		$query['id'] = $id;
		$purchaseReq = \Models\purchaseorder::first($query, [], ['maxTimeMS' => 5000 ]);
		$purchaseReq->delete();
		\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Purchase Order Deleted successfully']);
		$this->redirect('/purchaseorder/manage');
	}

}