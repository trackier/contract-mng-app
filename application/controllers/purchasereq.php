<?php

use Shared\Controller as Controller;
use Framework\{Registry, TimeZone, ArrayMethods};
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
		$this->seo(["title" => "Manage Purchase Request"]); 
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		$query['live'] = $this->request->get('live', 0);
		if ($this->user->role == 'user') {
			$query['users'] = ['$in' => [$this->user->_id]];
		}
		$query = [];
		$uiQuery = $this->request->get("query", []);
		$query['status'] = $uiQuery['status'] ?? [];
		$query = [];
		

	
		if (!$uiQuery || ( $uiQuery && $uiQuery['status'] == '')) {
			$query['status'] = ['$in' => ['pending', 'approved', 'rejected by department']];
		}
		$query['department'] = $this->user->department;

		$isDepHead = User::isDepartmentHead($this->user->_id, $this->user->department);
		if ($isDepHead) {
			$purchasereq = \Models\Purchasereq::selectAll($query, [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		}
		$users = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$view->set("query", $uiQuery ?? []);
		$view->set("purchasereq", $purchasereq);
  		$view->set("users", $users);
    }

	/**
	 * [PUBLIC] This function will set Purchasereq related data to the view.
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function manageFinance() {	
		$this->seo(["title" => "Manage Finance Purchase Request"]); 
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		$query['live'] = $this->request->get('live', 0);
		if ($this->user->role == 'user') {
			$query['users'] = ['$in' => [$this->user->_id]];
		}
		$query = [];
		$uiQuery = $this->request->get("query", []);
		$query['status'] = $uiQuery['status'] ?? [];
		if (!$uiQuery || ( $uiQuery && $uiQuery['status'] == '')) {
			$query['status'] = ['$in' => ['pending', 'approved', 'rejected', 'processed']];
		}
		$isFinHead = User::isFinanceHead($this->user->_id);
		if ($isFinHead) {
			$purchasereq = \Models\Purchasereq::selectAll($query, [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		}
		
		$users = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$view->set("purchasereq", $purchasereq??[]);
		$view->set("query", $uiQuery ?? []);
	
	}

    /**
	 * [PUBLIC] This function will set Purchasereq related data to the view.
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function viewall() {	
		$this->seo(["title" => "All Purchase Requests"]); 
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		$query['live'] = $this->request->get('live', 0);
		$query = [];
		$uiQuery = $this->request->get("query", []);
		$query['status'] = $uiQuery['status'] ?? [];
		if (!$uiQuery || ( $uiQuery && $uiQuery['status'] == '')) {
			$query['status'] = ['$in' => ['approved', 'rejected', 'processed', 'rejected by department', 'pending']];
		}
		$query['requester_id'] = $this->user->_id;
		$Purchasereq1 = \Models\Purchasereq::selectAll($query, [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
        $users = User::selectAll([], [], ['maxTimeMS' => 5000 ]);

        // var_dump($this->user->_id);
        // die();
		$view->set("purchasereq", $Purchasereq1);
		$view->set("query", $uiQuery);
        $view->set("users", $users);
	
	}

 	/**
	 * [PUBLIC] This function will set Purchasereq related data to the view.
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function report() {	
		$this->seo(["title" => "Report"]); 
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$dq = ['start' => $this->request->get('start'), 'end' => $this->request->get('end')];
		$view = $this-> getActionView();
		
		$query['live'] = $this->request->get('live', 0);
		if ($this->user->role == 'user') {
			$query['users'] = ['$in' => [$this->user->_id]];
		}
		$query = [];
		$uiQuery = $this->request->get("query", []);
		$query['status'] = $uiQuery['status'] ?? [];
		$groupBy;
		if ($uiQuery) {
			if (isset($uiQuery['groupBy'])) {
				$groupBy = array_keys($uiQuery['groupBy']);
			}
			foreach (['requester_id', 'approver1_id', 'department', 'activity_id', 'pr_id'] as $key) {
				if (isset($uiQuery[$key]) && $uiQuery[$key]) {
					if ($key == 'name') {
						$query[$key] = Db::convertType($uiQuery[$key], 'regex');
					} else {
						$query[$key] = $uiQuery[$key];
					}
				}
			}
		}
		if (!$uiQuery || ( $uiQuery && $uiQuery['status'] == '')) {
			$query['status'] = ['$in' => ['pending', 'approved', 'rejected', 'rejected by department', 'processed']];
		}
		$query['created'] = Db::dateQuery($dq['start'], $dq['end']);
		
		if ($this->user->role == 'admin') {
			$purchasereq = \Models\Purchasereq::selectAll($query, [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		}
		
		$requesterIds = ArrayMethods::arrayKeys($purchasereq, 'requester_id');
		$requesters = User::selectAll(['_id' => ['$in' => $requesterIds]], [], ['maxTimeMS' => 5000 ]);
		$requesters = ArrayMethods::arrayMaps($requesters, '_id');

		$approverIds = ArrayMethods::arrayKeys($purchasereq, 'approver1_id');
		$approvers = User::selectAll(['_id' => ['$in' => $approverIds]], [], ['maxTimeMS' => 5000 ]);
		$approvers = ArrayMethods::arrayMaps($approvers, '_id');

		$departmentIds = ArrayMethods::arrayKeys($purchasereq, 'department');
		$departments = \Models\Department::selectAll(['_id' => ['$in' => $departmentIds]], [], ['maxTimeMS' => 5000 ]);
		$departments = ArrayMethods::arrayMaps($departments, '_id');

		$activityIds = ArrayMethods::arrayKeys($purchasereq, 'activity_id');
		$activities = \Models\Activity::selectAll(['_id' => ['$in' => $activityIds]], [], ['maxTimeMS' => 5000 ]);
		$activities = ArrayMethods::arrayMaps($activities, '_id');
		
		if (isset($groupBy)) {
			// var_dump($purchasereq);
			// die();
			$purchasereq = \Models\Purchasereq::groupBy($purchasereq, $groupBy);
		}
		$users = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$view->set("purchasereq", $purchasereq??[]);
		$view->set("start", $this->request->get('start'));
		$view->set("end", $this->request->get('end'));
		$view->set("requesters", $requesters??[]);
		$view->set("departments", $departments??[]);
		$view->set("activities", $activities??[]);
		$view->set("approvers", $approvers??[]);
		$view->set("query", $uiQuery ?? []);
		$view->set("groupBy", $groupBy??[]);
		$view->set("ifgroupBy", isset($groupBy));
	}

	/**
	 * [PUBLIC] This function will set Purchasereq related data to the view.
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function dashboard() {	
		$this->seo(["title" => "Dashboard"]); 
	}
	/**
	 * [PUBLIC] This function will Add/Edit Purchasereq .
	 * @param $id
	 * @author Bhumika <bhumika@trackier.com>
	 * @before _secure
	 */
	public function add() {	
		$this->seo(["title" => "Add Purchase Request Details"]); 
		$view = $this->getActionView();
		$contractDetails = [];
		$files = [];
		$categories = ["Advertising and Marketing","Automobile Expense","Bank Fees and Charges","Computer Repair and Maintenance","Corporate Gifting","Furniture and Equipment","International Travel Expense","IT related Expense","Meals and Entertainment","Office Supplies","Stationary","Telephone Expense"];
		$approver1_id = Models\Department::first(["_id" => $this->user->department], ['team_lead_id'], ['maxTimeMS' => 5000 ]);
		// var_dump($approver1_id);
		// die();
		$activities = Models\Activity::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$view->set('activities', $activities);
		$pr_id = 'PR-'. rand(0, 999) . Models\purchasereq::count([]);
		$employee = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
        $view->set("categories", $categories);
        if ($this->request->isPost()) {	
			$total = 0;
			$data = $this->request->post('data', []);
			if (isset($data['items'])) {
				foreach ($data['items'] as $value) {
					if (!($value['erate'] && $value['quantity'])) {
						$view->set('message', 'Check rate and quantity entered in items');
						return;
					}
					$total = $total + $value['erate']* $value['quantity'];
				} 
			} else {
				$view->set('message', 'Check Items Not set');
				return;
			}
		
			
            if (isset($_FILES['files'])) {
				$files = $this->fileUpload($_FILES);
			}
            $purchasereq = new \Models\purchasereq();
            $purchasereq->notes = $data['notes'];
            $purchasereq->items = $data['items'];
			$purchasereq->expectedDate = $data['expectedDate'];
			$purchasereq->activity_id = $data['activity_id'];
			$purchasereq->docInserted = $files;
            $purchasereq->approver1_id = $approver1_id->team_lead_id;
            $purchasereq->requester_id = $this->user->_id;
            $purchasereq->status = "pending";
			$purchasereq->submittedOn = date("Y/m/d");
			$purchasereq->amount = $total;
			$purchasereq->pr_id = $pr_id;
			$purchasereq->department = $this->user->department;
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
		$this->seo(["title" => "Edit Purchase Request"]); 
		$view = $this-> getActionView();
		$query['id'] = $id;
        $purchasereq = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
		$categories = ["Advertising and Marketing","Automobile Expense","Bank Fees and Charges","Computer Repair and Maintenance","Corporate Gifting","Furniture and Equipment","International Travel Expense","IT related Expense","Meals and Entertainment","Office Supplies","Stationary","Telephone Expense"];

        $view->set("purchasereq", $purchasereq)->set("categories", $categories);

		$files = [];
        if ($purchasereq->docInserted) {
            $filesUploaded = ContractFile::selectAll(['fileId'=>['$in' => $purchasereq->docInserted]], ['filename','fileId'], ['maxTimeMS' => 5000 ]);
            foreach ($purchasereq->docInserted as $doc) {
                $files[] = $doc;
            }
            $view->set("files", $filesUploaded);
        }
		$activities = Models\Activity::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$view->set('activities', $activities);
		$employee = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
		
         if ($this->request->isPost()) {	
            $data = $this->request->post('data', []);
			if (isset($_FILES['files'])) {
				$f =  $this->fileUpload($_FILES);
				if(count($f)> 0) {
					$files[] = $f[0];
				}
			}
			$data = $this->request->post('data', []);
            $purchasereq->notes = $data['notes'];
            $purchasereq->items = $data['items'];
			$purchasereq->docInserted = $files;
            $purchasereq->requester_id = $this->user->_id;
            $purchasereq->denialReason = "";
            $purchasereq->status = "pending";
			$purchasereq->activity_id = $data['activity_id']??null;
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
		$this->seo(["title" => "Purchase Request Details"]); 
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
		$activity = \Models\Activity::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$activityMap = [];
		foreach ($activity as $value) {
			$activityMap[$value->_id] = $value;
		}
		$view->set("activity", $activity);
	}

    public function approve1($id = null) {	
		
		$purchaseReqDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$purchaseReqDetails = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
            $purchaseReqDetails->status = 'approved';
            $department = \Models\department::first(["name" => "Finance"], ["team_lead_id"], ['maxTimeMS' => 5000 ]);
            $purchaseReqDetails->approver1_id = $this->user->id;
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
			$purchaseReqDetails->approver1_id = $this->user->id;
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
			$purchaseReqDetails->approver2_id = $this->user->id;
            $purchaseReqDetails->paymentDate = date("Y/m/d");
            $purchaseReqDetails->save();
			header("Location: /purchasereq/manageFinance");
		}
	}

    public function deny2($id = null) {	
		
		$purchaseReqDetails = [];
		$data = $this->request->post('data', []);
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$purchaseReqDetails = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
			$purchaseReqDetails->approver2_id = $this->user->id;
            $purchaseReqDetails->status = 'rejected';
            $purchaseReqDetails->denialReason = $data['denialReason'];
            $purchaseReqDetails->save();
			header("Location: /purchasereq/manageFinance");
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
	 * [PUBLIC] This function will delete purchase request based on Id provided  .
	 * @param $id
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function delete($id) {
		$query['id'] = $id;
		$purchaseReq = \Models\purchasereq::first($query, [], ['maxTimeMS' => 5000 ]);
		$purchaseReq->delete();
		header("Location: /purchasereq/viewall");
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