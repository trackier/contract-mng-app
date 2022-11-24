<?php

/**
 * @author Himanshu Rao
 */

use Framework\{Registry, TimeZone, ArrayMethods};
use Shared\Services\Db;

class Asset extends Shared\Controller {

	/**
	 * @before _secure
	 * [PUBLIC] This function will add asset
	 * - Return message
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function add(){
		$this->seo(["title" => "Add Asset Details"]); 
		$view = $this->getActionView();
        $vendors = \Models\vendor::selectAll(['user_id' => $this->user->_id], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				if (!in_array($data['asset_type'], ['accessories', 'ipad', 'phone', 'laptop', 'car'])) {
					throw new Exception("wrong asset type selected");
				}
				$data = array_merge($data, ['user_id' => $this->user->_id]);
				$asset = new Models\Asset($data);
				$asset->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Asset Added successfully']);
				$this->redirect('/asset/manage');
				
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
		$view->set([
			'vendors' => $vendors ?? []
		]);
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find asset base on query
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function manage() {
		$this->seo(["title" => "Manage Assets"]); 
		$view = $this->getActionView();

		$query = [];
		$uiQuery = $this->request->get("query", []);
		if ($uiQuery) {
			foreach (['status', 'asset_type', 'ven_id', 'name'] as $key) {
				if (isset($uiQuery[$key]) && $uiQuery[$key]) {
					if ($key == 'name') {
						$query[$key] = Db::convertType($uiQuery[$key], 'regex');
					} else {
						$query[$key] = $uiQuery[$key];
					}
				}
			}
		}

		$assets = \Models\Asset::selectAll($query, [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$vendors = \Models\vendor::selectAll([], [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);

		$view->set([
			'assets' => $assets ?? [],
			'vendors' => $vendors ?? [],
			'query' => $uiQuery
		]);
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find and delete asset by id
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function delete($id = null) {
		$view = $this->getActionView();
		$asset = \Models\Asset::findById($id);
		if (!$asset) {
			return $view->set('message', ['type' => 'error', 'text' => 'No asset found!']);
		}
		$msg = "";
		try {
			$asset->delete();
			$msg = 'Asset deleted successfully!';
			$this->redirect('/asset/manage');
		} catch (\Exception $e) {
			$msg = ['type' => 'error', 'text' => 'Something went wrong. Please Try Again'];
		}
		$view->set('message', $msg);
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find and edit id
	 * @author Himanshu Rao <himanshurao@trackier.com>
	 */
	public function edit($id = null) {
		$this->seo(["title" => "Update Asset Details"]); 
		$view = $this->getActionView();
		if (!$id) {
			$this->_404();
		}
		$asset = Models\Asset::findById($id);
		$vendors = Models\vendor::cacheAllv2(['user_id' => $this->user->_id], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		if (!$asset) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Asset found!']);
		}
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['name', 'asset_type', 'status', 'ven_id', 'description', 'pur_date'] as $value) {
					if (isset($data[$value])) {
						$asset->$value = $data[$value];
					}
				}
				$asset->save();
				$view->set('message', ['type' => 'success', 'text' => 'Asset Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
        $view->set([
            'asset' => $asset ?? [],
			'vendors' => $vendors ?? []
		]);
	}
}