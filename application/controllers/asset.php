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
		$files = [];
        $vendors = \Models\vendor::selectAll([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				if (!in_array($data['asset_type'], ['accessories', 'ipad', 'phone', 'laptop', 'car'])) {
					throw new Exception("wrong asset type selected");
				}
				$data = array_merge($data, ['user_id' => $this->user->_id]);
				if (isset($_FILES['files'])) {
					$files = $this->fileUpload($_FILES);
				}
				$asset = new Models\Asset($data);
				$asset->status = 'available';
				$asset->docInserted = $files;
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
	 * [PRIVATE] This function will upload files to uploads location.
	 * @param $files
	 * @author Bhumika <bhumika@trackier.com>
	 * @return $filesUploaded
	 */
	private function fileUpload($files) {
		$upload_dir = APP_PATH.'/public/uploads'.DIRECTORY_SEPARATOR;
		$filesUploaded = [];
		if(!empty(array_filter($files['files']['name']))) {
			foreach ($files['files']['tmp_name'] as $key => $value) {
				$file_tmpname = $files['files']['tmp_name'][$key];
				$uniqueId =  uniqid();
				$file_name = $uniqueId.'.'. pathinfo($files['files']['name'][$key], PATHINFO_EXTENSION);
				$file_size = $files['files']['size'][$key];
				$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
				$filepath = $upload_dir.$file_name;
				if (move_uploaded_file($file_tmpname, $filepath)) {
					$contractfiles = new ContractFile([
						'filename' => $files['files']['name'][$key],
						'status' => 'Active',
						'fileId' => $uniqueId
					]);
					$contractfiles->save();
					$filesUploaded[] = $uniqueId ;
				}
			}
		}
		return $filesUploaded;
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
		$users = User::cacheAllv2([], [], ['maxTimeMS' => 5000]);
		$users = ArrayMethods::arrayMaps($users, '_id');
		$vendors = Models\vendor::cacheAllv2([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		if (!$asset) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Asset found!']);
		}
		if ($asset->docInserted) {
            $filesUploaded = ContractFile::selectAll(['fileId'=>['$in' => $asset->docInserted]], ['filename','fileId'], ['maxTimeMS' => 5000 ]);
            foreach ($asset->docInserted as $doc) {
                $files[] = $doc;
            }
            $view->set("files", $filesUploaded);
        }
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['name', 'asset_type', 'status', 'ven_id', 'description', 'pur_date'] as $value) {
					if (isset($data[$value])) {
						$asset->$value = $data[$value];
					}
				}
				if (isset($_FILES['files'])) {
					$f =  $this->fileUpload($_FILES);
					if(count($f)> 0) {
						$files[] = $f[0];
					}
				}
				$asset->docInserted = $files;
				$asset->save();
				$view->set('message', ['type' => 'success', 'text' => 'Asset Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
        $view->set([
            'asset' => $asset ?? [],
			'vendors' => $vendors ?? [],
			'users' => $users ?? []
		]);
	}

	/**
	 * [PUBLIC] This function will schedule file deletion after 3 days  based on file Id and contract Id provided  .
	 * @param $id
	 * @param $contractId
	 * @author Bhumika <bhumika@trackier.com>
	 * @before _secure
	 */
	public function deleteFile($id, $assetId) {
		$query['id'] = $assetId;
		$assetDetails = \Models\Asset::first($query, [], ['maxTimeMS' => 5000 ]);
		$fileDetails = ContractFile::first(['fileId' => $id], [], ['maxTimeMS' => 5000 ]);
		
		$fileDetails->status = 'Deleted';
		$fileDetails->dueDelDate = date("Y/m/d", strtotime('+3 days'));
		$fileDetails->save();
		$docs = $assetDetails->docInserted;
		$newDocs = [];
		foreach ($docs as $doc) {
			if ($doc!=$id) {
				$newDocs[] = $doc;
			}
		}
		$assetDetails->docInserted = $newDocs  ;
		$assetDetails->save();
		$subject = 'File Deletion';
		header("Location: /asset/edit/".$assetId);
	}
}