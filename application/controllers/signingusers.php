<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;

use Framework\RequestMethods as RequestMethods;
class SigningUsers extends Controller
{
	/**
	 * [PUBLIC] This function will set signing users related data to the view.
	 * @before _secure
	 */
	public function manage() {	
		$this->seo(["title" => "Manage Signing Users"]); 
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
        $query['live'] = $this->request->get('live', 0);
		$uiQuery = $this->request->get("query", []);
		if (isset($uiQuery['name']) && $uiQuery['name']!= null) {
			$query['name'] = $uiQuery['name'];
		}
		if (isset($uiQuery['email']) && $uiQuery['email']!= null) {
			$query['email'] = $uiQuery['email'];
		}
		$dq = ['start' => $this->request->get('start'), 'end' => $this->request->get('end')];
		$query['created'] = Db::dateQuery($dq['start'], $dq['end']);
		$users = Signinguser::selectAll($query, [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		$total = Signinguser::count($query) ?? 0;
		$view->set("users", $users)->set('limit', $limit)
		->set('page', $page)
		->set('total', $total)
		->set('search', $this->request->get('search', ''))
		->set("start", $this->request->get('start'))
		->set("end", $this->request->get('end'));
    }

	/**
	 * [PUBLIC] This function will add/edit signing user details
	 * @before _secure
	 * @param $id
	 */
	public function add($id = null) {	
		$this->seo(["title" => " Signing User details"]); 
		$view = $this-> getActionView();
        if ($id) {
            $query['id'] = $id;
            $signingusers = Signinguser::first($query, [], ['maxTimeMS' => 5000 ]);
            $view->set("suser", $signingusers);
        }
		if ($this->request->post("action") == "addUser") {	
			$name = RequestMethods::post("username"); 
			$contact = RequestMethods::post("contact");
			$email = RequestMethods::post("email"); 
            if ($id) {
                $signingusers->name =  $name;
                $signingusers->contact =  $contact;
                $signingusers->email =  $email;

            } else {
                $signingusers = new Signinguser([
                    'name' => $name,
                    'contact' => $contact,
                    'email' => $email,
                ]);
            }
			$signingusers->save();
            header("Location: /signingusers/manage.html");
		}
	}

	/**
	 * [PUBLIC] This function will delete signing user details
	 * @before _secure
	 * @param $id
	 */
    public function deleteUser($id) {
        $query['id'] = $id;
		$signingUser = Signinguser::first($query, [], ['maxTimeMS' => 5000 ]);
        $signingUser->delete();
        header("Location: /signingusers/manage.html");
  	  }
	}