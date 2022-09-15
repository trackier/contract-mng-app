<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;

use Framework\RequestMethods as RequestMethods;
class SigningUsers extends Controller
{
	/**
	 * @before _secure
	 */
	public function manage() {	
		$seo = ["title" => "Manage Signing Users", "view" => $this->getLayoutView()];
		$page = $this->request->get('page', 1);
		$limit = $this->request->get('limit', 50);
		$view = $this-> getActionView();
        $query['live'] = $this->request->get('live', 0);
		$users = Signinguser::selectAll($query, [], [ 'order'=> 'created', 'direction' => 'desc', 'limit' => $limit, 'page' => $page, 'maxTimeMS' => 5000 ]);
		$total = Signinguser::count($query) ?? 0;
		$view->set("users", $users)->set('limit', $limit)
		->set('page', $page)
		->set('total', $total)
		->set('search', $this->request->get('search', ''));
    }

	/**
	 * @before _secure
	 */
	public function add($id = null) {	
		$seo = ["title" => "Contracts", "view" => $this->getLayoutView()];
		$view = $this-> getActionView();
        if ($id) {
            $query['id'] = $id;
            $signingusers = Signinguser::first($query, [], ['maxTimeMS' => 5000 ]);
            $view->set("suser", $signingusers);
        }
		
        if ($this->request->post("action") == "addUser") 
		{	
			$fullname = RequestMethods::post("username"); 
			$contact = RequestMethods::post("contact");
			$email = RequestMethods::post("email"); 
            if ($id) {
                $signingusers->fullname =  $fullname;
                $signingusers->contact =  $contact;
                $signingusers->email =  $email;

            } else {
                $signingusers = new Signinguser([
                    'fullname' => $fullname,
                    'contact' => $contact,
                    'email' => $email,
                ]);
            }
			$signingusers->save();
            header("Location: /signingusers/manage.html");
		}
	}

    public function deleteUser($id) {
        $query['id'] = $id;
		$signingUser = Signinguser::first($query, [], ['maxTimeMS' => 5000 ]);
        $signingUser->delete();
        header("Location: /signingusers/manage.html");
    }
}