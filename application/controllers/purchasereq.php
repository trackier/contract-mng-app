<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;
use Framework\RequestMethods as RequestMethods;
class Purchasereq extends Controller
{ 	
	/**
	 * [PRIVATE] This function will send email to the users.
	 * @param $users
	 * @param $content
	 */
	private function sendEmail($users, $content, $subject) {	
		foreach ($users as $user) {
			$userDetails = Signinguser::first(['id' => $user], [], ['maxTimeMS' => 5000 ]);
			if (!$userDetails) {
				$userDetails = User::first(['id' => $user], [], ['maxTimeMS' => 5000 ]);
			}
			\Shared\Mail::send([
				'user' => $userDetails,
				'subject' => $subject,
				'template' => 'filedeletion',
				'contents' => $content
			]);
		}
		return;
	}
	/**
	 * [PUBLIC] This function will set Purchasereq related data to the view.
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function manage() {	
		$seo = ["title" => "Manage Purchasereq", "view" => $this->getLayoutView()];
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		$query['live'] = $this->request->get('live', 0);
		if ($this->user->role == 'user') {
			$query['users'] = ['$in' => [$this->user->_id]];
		}
		$Purchasereq1 = \Models\Purchasereq::selectAll(['approver1_id' => $this->user->_id, "status" => "pending"], [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
        $Purchasereq2 = \Models\Purchasereq::selectAll(['approver2_id' => $this->user->_id, "status" => "approved"], [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);

		// $assets = \Models\Asset::selectAll([], ['_id', 'type', 'status'], ['maxTimeMS' => 5000]);
		// $vendors = \Models\vendor::selectAll([], ['_id', 'type', 'status'], ['maxTimeMS' => 5000]);
		// $employees = \Models\Employee::selectAll([], ['_id', 'type', 'status'], ['maxTimeMS' => 5000]);
		// $assigneds = \Models\Assigned::selectAll([], ['_id', 'type', 'status'], ['maxTimeMS' => 5000]);
        $users = User::selectAll([], [], ['maxTimeMS' => 5000 ]);

        // var_dump($Purchasereq2);
        // die();
		$view->set("purchasereq", $Purchasereq1);
        $view->set("purchasereq2", $Purchasereq2);
        $view->set("users", $users);
       
	
	}

    /**
	 * [PUBLIC] This function will set Purchasereq related data to the view.
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function viewall() {	
		$seo = ["title" => "Manage Purchasereq", "view" => $this->getLayoutView()];
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		$query['live'] = $this->request->get('live', 0);
		
		$Purchasereq1 = \Models\Purchasereq::selectAll(['requester_id' => $this->user->_id], [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
        $users = User::selectAll([], [], ['maxTimeMS' => 5000 ]);

        // var_dump($this->user->_id);
        // die();
		$view->set("purchasereq", $Purchasereq1);
        $view->set("users", $users);
	
	}


	/**
	 * [PUBLIC] This function will Add/Edit Purchasereq .
	 * @param $id
	 * @author Bhumika <bhumika@trackier.com>
	 * @before _secure
	 */
	public function add() {	
		$seo = ["title" => "Purchasereq", "view" => $this->getLayoutView()];
		$view = $this-> getActionView();
		$contractDetails = [];
		$files = [];
		
		$approver1_id = Models\Department::first(["_id" => $this->user->department], ['team_lead_id'], ['maxTimeMS' => 5000 ]);
		$employee = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
        
        if ($this->request->isPost()) {	
            $data = $this->request->post('data', []);
            if (isset($_FILES['files'])) {
				$files = $this->fileUpload($_FILES);
			}
			$data = $this->request->post('data', []);
            $purchasereq = new \Models\purchasereq();
            $purchasereq->notes = $data['notes'];
            $purchasereq->items = $data['items'];
			$purchasereq->docInserted = $files;
            $purchasereq->approver1_id = $approver1_id->team_lead_id;
            $purchasereq->requester_id = $this->user->_id;
            $purchasereq->status = "pending";
            $purchasereq->save();
		
			$view->set('message', 'Purchase Request Saved successfully');
			
		}
		
	}

    /**
	 * [PUBLIC] This function will Add/Edit Purchasereq .
	 * @param $id
	 * @author Bhumika <bhumika@trackier.com>
	 * @before _secure
	 */
    public function edit($id) {	
		$seo = ["title" => "Edit Purchare request", "view" => $this->getLayoutView()];
		$view = $this-> getActionView();
		$query['id'] = $id;
        $purchasereq = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
        $view->set("purchasereq", $purchasereq);
        if ($purchasereq->docInserted) {
            $filesUploaded = ContractFile::selectAll(['fileId'=>['$in' => $purchasereq->docInserted]], ['filename','fileId'], ['maxTimeMS' => 5000 ]);
            foreach ($purchasereq->docInserted as $doc) {
                $files[] = $doc;
            }
            $view->set("files", $filesUploaded);
        }
		$files = [];
		
		$approver1_id = Models\Department::first(["_id" => $this->user->department], ['team_lead_id'], ['maxTimeMS' => 5000 ]);
		$employee = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
        
        if ($this->request->isPost()) {	
            
            $data = $this->request->post('data', []);
            if (isset($_FILES['files'])) {
				$files = $this->fileUpload($_FILES);
			}
			$data = $this->request->post('data', []);
            $purchasereq->notes = $data['notes'];
            $purchasereq->items = $data['items'];
			$purchasereq->docInserted = $files;
            $purchasereq->approver1_id = $approver1_id->team_lead_id;
            $purchasereq->requester_id = $this->user->_id;
            $purchasereq->denialReason = "";
            $purchasereq->status = "pending";
            $purchasereq->save();
		
			$view->set('message', 'Purchase Request Edited successfully');
			
		}
		
	}
    
    /**
	 * [PUBLIC] This function will Add/Edit Purchasereq .
	 * @param $id
	 * @author Bhumika <bhumika@trackier.com>
	 * @before _secure
	 */
	public function view($id = null) {	
		$seo = ["title" => "Purchasereq", "view" => $this->getLayoutView()];
		$view = $this-> getActionView();
		$purchaseReqDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$purchaseReqDetails = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
			$view->set("purchaseReqDetails", $purchaseReqDetails);
			if ($purchaseReqDetails->docInserted) {
				$filesUploaded = ContractFile::selectAll(['fileId'=>['$in' => $purchaseReqDetails->docInserted]], ['filename','fileId'], ['maxTimeMS' => 5000 ]);
				foreach ($purchaseReqDetails->docInserted as $doc) {
					$files[] = $doc;
				}
				$view->set("files", $filesUploaded);
			}
		}
	}

    public function approve1($id = null) {	
		
		$purchaseReqDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$purchaseReqDetails = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
            $purchaseReqDetails->status = 'approved';
            $department = \Models\department::first(["name" => "Finance"], ["team_lead_id"], ['maxTimeMS' => 5000 ]);
            $purchaseReqDetails->approver2_id = $department->team_lead_id;
            $purchaseReqDetails->save();
			header("Location: /purchasereq/manage");
		}
	}

    public function deny1($id = null) {	
		$data = $this->request->post('data', []);
        $purchaseReqDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$purchaseReqDetails = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
            $purchaseReqDetails->status = 'rejected by department';
            $purchaseReqDetails->denialReason = $data['denialReason'];
            $purchaseReqDetails->save();
			header("Location: /purchasereq/manage");
		}
	}

    public function approve2($id = null) {	
		
		$purchaseReqDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$purchaseReqDetails = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
            $purchaseReqDetails->status = 'processed';
            $purchaseReqDetails->paymentDate = date("Y/m/d");
            $purchaseReqDetails->save();
			header("Location: /purchasereq/manage");
		}
	}

    public function deny2($id = null) {	
		
		$purchaseReqDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$purchaseReqDetails = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
            $purchaseReqDetails->status = 'rejected';
          
            $purchaseReqDetails->save();
			header("Location: /purchasereq/manage");
		}
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
	 * [PUBLIC] This function will delete contract based on Id provided  .
	 * @param $id
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function deleteContract($id) {
		$query['id'] = $id;
		$contractDetails = Contracttbl::first($query, [], ['maxTimeMS' => 5000 ]);
		$contents = sprintf('<p>	Your contract with  contract Name: %s has been deleted <br></p>', $contractDetails->cname);
		$subject = 'Contract Deletion';
		$this->sendEmail($contractDetails->users, $contents, $subject);
		$contractDetails->delete();
		header("Location: /contract/manage");
	}

	/**
	 * [PUBLIC] This function will schedule file deletion after 3 days  based on file Id and contract Id provided  .
	 * @param $id
	 * @param $contractId
	 * @author Bhumika <bhumika@trackier.com>
	 * @before _secure
	 */
	public function deleteFile($id, $purchaseReqId) {
		$query['id'] = $purchaseReqId;
		$purchaseReqDetails =  \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
		$fileDetails = ContractFile::first(['fileId' => $id], [], ['maxTimeMS' => 5000 ]);
		
		$fileDetails->status = 'Deleted';
		$fileDetails->dueDelDate = date("Y/m/d", strtotime('+3 days'));
		$fileDetails->save();
		$docs = $purchaseReqDetails->docInserted;
		$newDocs = [];
		foreach ($docs as $doc) {
			if ($doc!=$id) {
				$newDocs[] = $doc;
			}
		}
		$purchaseReqDetails->docInserted = $newDocs  ;
		$purchaseReqDetails->save();
		
		header("Location: /purchasereq/edit/".$purchaseReqId);
	}
	
	/**
	 * [PUBLIC] This function will download file based on file id provided.
	 * @param $id
	 * @author Bhumika <bhumika@trackier.com>
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