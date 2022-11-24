<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;
use Framework\RequestMethods as RequestMethods;
class Activity extends Controller
{ 	
	
	/**
	 * [PUBLIC] This function will set Activity related data to the view.
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function manage() {	
		$this->seo(["title" => "Manage Activities"]); 
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
		
		$activity = \Models\Activity::selectAll([], [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		
		$users = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
		$userMap = [];
		foreach ($users as  $value) {
			$userMap [$value->_id] = $value->name;
		}
		$view->set("activity", $activity);
		$view->set("users", $userMap);
       
	
	}

   	/**
	 * [PUBLIC] This function will Add/Edit Activity .
	 * @param $id
	 * @author Bhumika <bhumika@trackier.com>
	 * @before _secure
	 */
	public function add($id = null) {	
		$this->seo(["title" => "Add Activity"]); 
		$view = $this-> getActionView();
		$contractDetails = [];
		$files = [];
		if ($id) {
			$query['id'] = $id;
			$activity = Models\Activity::first($query, [], ['maxTimeMS' => 5000 ]);
			$view->set("activityDetails", $activity);
		}
		$act_id = 'ACT-' .rand(0, 999). Models\Activity::count([]);
		$employee = User::selectAll([], [], ['maxTimeMS' => 5000 ]);
        $view->set('users', $employee);
        if ($this->request->isPost()) {	
			if (!$id) {
				$activity =  new \Models\activity();
				$activity->act_id = $act_id;
			}
			$total = 0;
			$data = $this->request->post('data', []);
            $activity->description = $data['description'];
            $activity->teamMembers = $data['teamMembers'];
			$activity->startDate = $data['startDate'];
			$activity->endDate = $data['endDate'];
			$activity->name = $data['name'];
			$activity->save();
			$view->set('message', 'Activity Saved successfully');
		}
	}

    /**
	 * [PUBLIC] This function will delete contract based on Id provided  .
	 * @param $id
	 * @before _secure
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function delete($id) {
		$query['id'] = $id;
		$activity = \Models\Activity::first($query, [], ['maxTimeMS' => 5000 ]);
		$activity->delete();
		header("Location: /activity/manage");
	}

}