<?php

/**
 * @author Himanshu Rao
 */


use Framework\{Registry, TimeZone, ArrayMethods};
use Shared\Services\Db;

class vendor extends Shared\Controller {

	/**
	 * @before _secure
	 * [PUBLIC] This function will add vendor
	 * - Return message
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function add(){
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				$data = array_merge($data, ['user_id' => $this->user->_id]);
				$vendor = new \Models\vendor($data);
				$vendor->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'vendor Added successfully']);
				$this->redirect('/vendor/manage');
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find vendor base on query
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function manage() {
		$view = $this->getActionView();

		$query = ['user_id' => $this->user->_id];
		$searchKeyType = strtolower($this->request->get('type'));
		$searchValue = $this->request->get('search');
		switch ($searchKeyType) {
			case 'name':
				$query = array_merge($query, ['name' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'comapnyName':
				$query = array_merge($query, ['company_name' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'emp_id':
				$query = array_merge($query, ['emp_id' => $searchValue]);
				break;

			case 'phone':
				$query = array_merge($query, ['phone' => Db::convertType($searchValue, 'regex')]);
				break;
		
			case 'email':
				$query = array_merge($query, ['email' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'address':
				$query = array_merge($query, ['address' => Db::convertType($searchValue, 'regex')]);
				break;
			
			case 'state':
				$query = array_merge($query, ['state' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'country':
				$query = array_merge($query, ['country' => Db::convertType($searchValue, 'regex')]);
				break;
		}

        $vendors = \Models\vendor::selectAll($query, [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$count = \Models\vendor::count($query);

		$view->set([
			'vendors' => $vendors ?? [],
			'search' => $this->request->get('search', ''),
			'type' => $this->request->get('type', '')
		]);
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find and delete vendor by id
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function delete($id = null) {
		$view = $this->getActionView();
		if (!$id || !$this->request->isDelete()) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => 'Invalid Request']);
			$this->redirect('/vendor/manage');
		}

		$vendor = \Models\vendor::findById($id);
		if (!$vendor) {
			return $view->set('message', ['type' => 'error', 'text' => 'No vendor found!']);
		}
		$msg = "";
		try {
			$vendor->delete();
			$msg = 'vendor deleted successfully!';
		} catch (\Exception $e) {
			$msg = ['type' => 'error', 'text' => 'Something went wrong. Please Try Again'];
		}
		$view->set('message', $msg);
	}

    /**
	 * @before _secure
	 * [PUBLIC] This function will find and edit vendor
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function edit($id = null) {
		$view = $this->getActionView();
		if (!$id) {
			$this->_404();
		}
		$vendor = \Models\vendor::findById($id);
		if (!$vendor) {
			return $view->set('message', ['type' => 'error', 'text' => 'No vendor found!']);
		}
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['name', 'email', 'address', 'state', 'country', 'company_name', 'phone'] as $value) {
					if (isset($data[$value])) {
						$vendor->$value = $data[$value];
					}
				}
				$vendor->save();
				$view->set('message', ['type' => 'success', 'text' => 'vendor Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
		$view->set('vendor', $vendor);
	}
}