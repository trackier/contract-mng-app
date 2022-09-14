<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;

use Framework\RequestMethods as RequestMethods;
class Users extends Controller
{
	/**
	 * @before _session
	 * @after _csrfToken
	 */
	public function login() {	
		$seo = ["title" => "Login", "view" => $this->getLayoutView()];
		if ($this->request->post("action") == "login") 
		{
			$email = RequestMethods::post("email"); $password = RequestMethods::post("password");
			$view = $this-> getActionView(); $error = false;
			if (empty($email))
			{
				$view->set("email_error", "Email not provided");
				$error = true; 
			}
			if (empty($password))
            {
				$view->set("password_error", "Password not provided");
				$error = true;
			}
			if (!$error)
            {
				$user = User::first(array( "email=?" =>$email, "password=?" =>$password,));
				if (!empty($user))
                {	$this->setUser($user);
					$session = Registry::get("session");
					$user = $this->getUser();
					$this->redirect('/contract/manage');
                   
				}
				else {
					$view->set("password_error", "Email address and/or password are incorrect");
				 }
			}
		}
	}
	/**
	 * @protected
	 */
	public function _csrfToken() {
		$session = $this->getSession();
		$csrf_token = Framework\StringMethods::uniqRandString(44);
		$session->set('Auth\Request:$token', $csrf_token);

		if ($this->actionView) {
			$this->actionView->set('__token', $csrf_token);
		}
	}

	/**
	 * @before _secure
	 */
	public function changepassword() {
		$seo = ["title" => "Change Password", "view" => $this->getLayoutView()];
		$view = $this->getActionView();
		if ($this->request->post("action") == "changepassword") 
		{
			$password = RequestMethods::post("password");
			$userDetail = User::first(array( "email=?" => $this->user->email));
			$userDetail->password = $password;
			$userDetail->save();
			$view->set('message', 'Password set successfully');
		}
	}
	
}