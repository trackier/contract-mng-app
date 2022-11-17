<?php

/**
 * @author Bhumika
 */


use Framework\{Registry, TimeZone, ArrayMethods};
use Shared\Services\Db;

class  Department extends Shared\Controller {

	/**
	 * @before _secure
	 * [PUBLIC] This function will add department
	 * - Return message
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function add(){
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				$data = array_merge($data, ['user_id' => $this->user->_id]);
               
				$department = new \Models\department($data);
                
				$department->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Department Added successfully']);
				$this->redirect('/department/manage');
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find department base on query
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function manage() {
		$view = $this->getActionView();

		$query = [];
		$searchKeyType = strtolower($this->request->get('type'));
		$searchValue = $this->request->get('search');
		

        $departments = \Models\department::selectAll($query, [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$count = \Models\department::count($query);
        $view->set([
			'departments' => $departments ?? [],
			'search' => $this->request->get('search', ''),
			'type' => $this->request->get('type', '')
		]);
	}

	/**
	 * @before _secure
	 * [PUBLIC] This function will find and delete department by id
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function delete($id = null) {
		$view = $this->getActionView();
       if (!$id) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => 'Invalid Request']);
			$this->redirect('/department/manage');
		}

		$department = \Models\department::findById($id);
		if (!$department) {
			return $view->set('message', ['type' => 'error', 'text' => 'No department found!']);
		}
		$msg = "";
		try {
          
			$department->delete();
			$msg = 'department deleted successfully!';
           
		} catch (\Exception $e) {
			$msg = ['type' => 'error', 'text' => 'Something went wrong. Please Try Again'];
		}
        \Shared\Utils::flashMsg(['type' => 'success', 'text' => $msg]);
        $this->redirect('/department/manage');
	}

    /**
	 * @before _secure
	 * [PUBLIC] This function will find and edit department
	 * @author Bhumika <bhumika@trackier.com>
	 */
	public function edit($id = null) {
        $users = User::selectAll(["department" => $id], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);

		$view = $this->getActionView();
		if (!$id) {
			$this->_404();
		}
		$department = \Models\department::findById($id);
		if (!$department) {
			return $view->set('message', ['type' => 'error', 'text' => 'No department found!']);
		}
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['name', 'description', 'team_lead_id'] as $value) {
					if (isset($data[$value])) {
						$department->$value = $data[$value];
					}
				}
				$department->save();
				$view->set('message', ['type' => 'success', 'text' => 'department Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
		$view->set('department', $department);
        $view->set('users', $users);
	}
}