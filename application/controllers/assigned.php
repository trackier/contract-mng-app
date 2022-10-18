<?php

use Shared\Services\Db;
use Framework\{Registry, TimeZone, ArrayMethods};

class Assigned extends Shared\Controller {

	/**
	 * @before _secure
	 * [PUBLIC] This function will add assigned
	 * - Return message
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function add(){
        $view = $this->getActionView();
        $query = ['user_id' => $this->account->_id];
        $employees = \Models\Employee::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $assets = \Models\Asset::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				if (!$data['handover_date']) {
					unset($data['handover_date']);
				}
				$data = array_merge($data, ['user_id' => $this->account->_id]);
				$assigned = new \Models\Assigned($data);
				$assigned->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Assigned Added successfully']);
				$this->redirect('/assigned/manage');
				
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
        $view->set([
			'assets' => $assets ?? [],
			'employees' => $employees ?? []
		]);
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find assigned base on query
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function manage() {
		$view = $this->getActionView();

		$query = ['user_id' => $this->account->_id];
		$uiQuery = $this->request->get("query", []);
		if ($uiQuery) {
			foreach (['asset_id', 'emp_id'] as $key) {
				if (isset($uiQuery[$key]) && $uiQuery[$key]) {
					$query[$key] = $uiQuery[$key];
				}
			}
		}

        $assigneds = \Models\Assigned::selectAll($query, [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $empIds = ArrayMethods::arrayKeys($assigneds, 'emp_id');
        if ($empIds) {
            $employees = \Models\Employee::cacheAllv2(['user_id' => $this->account->_id, '_id' => ['$in' => $empIds]], ['_id', 'name'], ['maxTimeMS' => 5000]);
        }
        $assetIds = ArrayMethods::arrayKeys($assigneds, 'asset_id');
        if ($assetIds) {
            $assets = \Models\Asset::selectAll(['user_id' => $this->account->_id, '_id' => ['$in' => $assetIds]], ['_id', 'name', 'asset_type'], ['maxTimeMS' => 5000]);
        }
        $total = $count = \Models\Assigned::count($query);

		$view->set([
			'assigneds' => $assigneds ?? [],
            'assets' => $assets ?? [],
			'employees' => $employees ?? [],
			'query' => $uiQuery
		]);
	}

    /**
	 * @before _secure
	 * [PUBLIC] This function will find and delete assigned by id
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function delete($id = null) {
		$view = $this->getActionView();
		if (!$id || !$this->request->isDelete()) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => 'Invalid Request']);
			$this->redirect('/assigned/manage');
		}

		$asset = \Models\Assigned::findById($id);
		if (!$asset) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Assigned found!']);
		}
		$msg = "";
		try {
			$asset->delete();
			$msg = 'Assigned deleted successfully!';
		} catch (\Exception $e) {
			$msg = ['type' => 'error', 'text' => 'Something went wrong. Please Try Again'];
		}
		$view->set('message', $msg);
	}

    /**
	 * @before _secure
	 * [PUBLIC] This function will find and edit assigned
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function edit($id = null) {
		$view = $this->getActionView();
		if (!$id) {
			$this->_404();
		}
        $query = ['user_id' => $this->account->_id];
        $employees = \Models\Employee::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $assets = \Models\Asset::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$assigned = \Models\Assigned::findById($id);
		if (!$assigned) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Assigned found!']);
		}
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['asset_id', 'emp_id', 'assign_date', 'handover_date'] as $value) {
					if (isset($data[$value])) {
						$assigned->$value = $data[$value];
					}
				}
				$assigned->save();
				$view->set('message', ['type' => 'success', 'text' => 'Assigned Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
        $view->set([
            'assigned' => $assigned ?? [],
			'assets' => $assets ?? [],
			'employees' => $employees ?? []
		]);
	}

}