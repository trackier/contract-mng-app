<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;
use Framework\RequestMethods as RequestMethods;
class Contract extends Controller
{ 	
	/**
	 * [PUBLIC] This function will set contracts related data to the view.
	 * @before _secure
	 */
	public function manage() {	
		$seo = ["title" => "Manage Contracts", "view" => $this->getLayoutView()];
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		$query['live'] = $this->request->get('live', 0);
		$contracts = Contracttbl::selectAll($query, [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		$total = Contracttbl::count($query) ?? 0;
		$view->set("contracts", $contracts)->set('limit', $limit)
		->set('page', $page)
		->set('total', $total)
		->set('search', $this->request->get('search', ''));
	}

	/**
	 * [PUBLIC] This function will Add/Edit Contracts .
	 * @before _secure
	 */
	public function addContract($id = null) {	
		$seo = ["title" => "Contracts", "view" => $this->getLayoutView()];
		$view = $this-> getActionView();
		$contractDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$contractDetails = Contracttbl::first($query, [], ['maxTimeMS' => 5000 ]);
			$view->set("contractDetails", $contractDetails);
			if ($contractDetails->docInserted) {
				$filesUploaded = ContractFile::selectAll(['fileId'=>['$in' => $contractDetails->docInserted]], ['filename','fileId'], ['maxTimeMS' => 5000 ]);
				foreach ($contractDetails->docInserted as $doc) {
					$files[] = $doc;
				}
				$view->set("files", $filesUploaded);
			}
		}
		$signingUsers = Signinguser::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$view->set("signingUsers", $signingUsers);
		if ($this->request->post("action") == "addContract") {	
			if (isset($_FILES['files'])) {
				$files = $this->fileUpload($_FILES);
			}
			$cname = RequestMethods::post("contract_name"); 
			$company = RequestMethods::post("company");
			$type = RequestMethods::post("type"); 
			$startDate = RequestMethods::post("startDate"); 
			$endDate = RequestMethods::post("endDate"); 
			$notes = RequestMethods::post("notes"); 
			$users = RequestMethods::post("users"); 

			if ($id) {
				$contractDetails->cname =  $cname;
                $contractDetails->type =  $type;
                $contractDetails->company =  $company;
				$contractDetails->startDate =  $startDate;
                $contractDetails->endDate =  $endDate;
                $contractDetails->notes =  $notes;
				$contractDetails->docInserted =  $files;
				$contractDetails->users =  $users;
			} else {
				$contractDetails = new Contracttbl([
					'cname' => $cname,
					'type' => $type,
					'company' => $company,
					'startDate' => $startDate,
					'endDate' => $endDate,
					'notes' => $notes,
					'docInserted' => $files,
					'users' => $users
				]);
			}
			$contractDetails->save();
			$view->set('message', 'Contract Saved successfully');
			if ($id) {
				header("Location: /contract/addContract/".$id);		
			}
		}
	}

	/**
	 * [PRIVATE] This function will upload files to uploads location.
	 * @param $files
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
	 * [PUBLIC] This function will delete contract based on Id provided  .
	 * @param $id
	 * @before _secure
	 */
	public function deleteContract($id) {
		$query['id'] = $id;
		$contractDetails = Contracttbl::first($query, [], ['maxTimeMS' => 5000 ]);
		$contractDetails->delete();
		header("Location: /contract/manage");
	}

	/**
	 * [PUBLIC] This function will schedule file deletion after 3 days  based on file Id and contract Id provided  .
	 * @param $id
	 * @param $contractId
	 * @before _secure
	 */
	public function deleteFile($id, $contractId) {
		$query['id'] = $contractId;
		$contractDetails = Contracttbl::first($query, [], ['maxTimeMS' => 5000 ]);
		$fileDetails = ContractFile::first(['fileId' => $id], [], ['maxTimeMS' => 5000 ]);
		foreach ($contractDetails->users as $user) {
			$userDetails = Signinguser::first(['id' => $user], [], ['maxTimeMS' => 5000 ]);
			$contents[] = sprintf('<p>	Your file %s for contract Name: %s has been deleted <br></p>',$fileDetails->filename, $contractDetails->cname);

			\Shared\Mail::send([
				'user' => $userDetails,
				'subject' => '[ALERT] File Deletion ',
				'template' => 'filedeletion',
				'contents' => implode("<br>", $contents)
			]);
		}
		$fileDetails->status = 'Deleted';
		$fileDetails->dueDelDate = date("Y/m/d", strtotime('+3 days'));
		$fileDetails->save();
		$docs = $contractDetails->docInserted;
		$newDocs = [];
		foreach ($docs as $doc) {
			if ($doc!=$id) {
				$newDocs[] = $doc;
			}
		}
		$contractDetails->docInserted = $newDocs  ;
		$contractDetails->save();
		header("Location: /contract/addContract/".$contractId);
	}
	
	/**
	 * [PUBLIC] This function will download file based on file id provided.
	 * @param $id
	 * @before _secure
	 */
	public function downloadFile($id) {
		$file = ContractFile::first(['fileId'=>$id], ['filename','fileId'], ['maxTimeMS' => 5000 ]);
		$extension = pathinfo($file->filename, PATHINFO_EXTENSION);
		$file_url = APP_PATH.'/public/uploads/'.$id.'.'.$extension;  
		header('Content-Type: application/octet-stream');  
		header("Content-Transfer-Encoding: utf-8");   
		header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");   
		readfile($file_url);  
		
	}

}