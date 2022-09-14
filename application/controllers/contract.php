<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;

use Framework\RequestMethods as RequestMethods;
class Contract extends Controller
{
	/**
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
		if ($this->request->post("action") == "addContract") 
		{	if(isset($_FILES['files'])) {
				$upload_dir = '/Users/bhumikabisht/Work/trackier/contract-mng-app/application/controllers/uploads'.DIRECTORY_SEPARATOR;
				$maxsize = 2 * 1024 * 1024;
				if(!empty(array_filter($_FILES['files']['name']))) {
					foreach ($_FILES['files']['tmp_name'] as $key => $value) {
						$file_tmpname = $_FILES['files']['tmp_name'][$key];
						$uniqueId =  uniqid();
						$file_name = $uniqueId.'.'. pathinfo($_FILES['files']['name'][$key], PATHINFO_EXTENSION);
						$file_size = $_FILES['files']['size'][$key];
						$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
						$filepath = $upload_dir.$file_name;
						
						if ($file_size > $maxsize){
							$view->set('message',"EFile size greater than 2 MB");  
							return;  
						}
						if( move_uploaded_file($file_tmpname, $filepath)) {
							$contractfiles = new ContractFile([
								'filename' => $_FILES['files']['name'][$key],
								'status' => 'Active',
								'fileId' => $uniqueId
							]);
							$contractfiles->save();
							$files[] = $uniqueId ;} else {      
							$view->set('message',"Error uploading file");  
							return;            
						}
					}
				}
				else {
					$view->set('message', 'No file selected');
				}
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
				header("Location: /contract/addContract/".$contractId);		
			}
		}
	}

	public function deleteContract($id) {
		$query['id'] = $id;
		$contractDetails = Contracttbl::first($query, [], ['maxTimeMS' => 5000 ]);
		$contractDetails->delete();
		header("Location: /contract/manage");
	}

	public function deleteFile($id, $contractId) {
		$query['id'] = $contractId;
		$contractDetails = Contracttbl::first($query, [], ['maxTimeMS' => 5000 ]);
		$fileDetails = ContractFile::first(['fileId' => $id], [], ['maxTimeMS' => 5000 ]);
		$fileDetails->status = 'Deleted';
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
	public function downloadFile($id) {
		$file = ContractFile::first(['fileId'=>$id], ['filename','fileId'], ['maxTimeMS' => 5000 ]);
		$extension = pathinfo($file->filename, PATHINFO_EXTENSION);
		$file_url = '/Users/bhumikabisht/Work/trackier/contract-mng-app/application/controllers/uploads/'.$id.'.'.$extension;  
		header('Content-Type: application/octet-stream');  
		header("Content-Transfer-Encoding: utf-8");   
		header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");   
		readfile($file_url);  
		
	}

}