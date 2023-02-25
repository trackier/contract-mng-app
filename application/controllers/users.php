<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;
use Shared\Services\Db;

use Framework\RequestMethods as RequestMethods;
class Users extends Controller
{
	/**
	 * [PUBLIC] This function will login user based on email id and password
	 * @before _session
	 * @after _csrfToken
	 */
	public function login() {	
		$this->seo(["title" => "Login"]); 
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
			if (!$error) {
				$pass = sha1($password);
				$user = User::first(["email" => $email, "password" => $pass]);
				if (!empty($user))
                {	$this->setUser($user);
					$session = Registry::get("session");
					$user = $this->getUser();
					$this->redirect('/dashboard/view');
                }
				else {
					$view->set('message', 'Please provide valid credentials');
				}
			}
		}
	}

	/**
	 * [PUBLIC] This function will login user based on email id and password
	 * @before _session
	 * @after _csrfToken
	 */
	public function oldlogin() {	
		$this->seo(["title" => "Login"]); 
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
			if (!$error) {
				$pass = sha1($password);
				$user = User::first(["email" => $email, "password" => $pass]);
				if (!empty($user))
                {	$this->setUser($user);
					$session = Registry::get("session");
					$user = $this->getUser();
					$this->redirect('/dashboard/view');
                }
				else {
					$view->set('message', 'Please provide valid credentials');
				}
			}
		}
	}

	/**[PUBLIC] This function wil set authorisation token in the session
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
	 * [PUBLIC] This function will change password of the user 
	 * @before _secure
	 */
	public function changepassword() {
		$this->seo(["title" => "Change Password"]); 
		$view = $this->getActionView();
		if ($this->request->post("action") == "changepassword") 
		{
			$password = RequestMethods::post("password");
			$password2 = RequestMethods::post("password2");
			if ($password != $password2) {
				$view->set('message', 'Passwords do not match!!');
				return;
			}
			
			$userDetail = User::first(array( "email=?" => $this->user->email));
			$userDetail->password = sha1($password);
			$userDetail->save();
			$view->set('message', 'Password set successfully');
		}
	}

	private function __gooleClient($redirectURI) {
		$appConf = Framework\Utils::getConfig("app")->app;

		$client = new Google_Client();
		
		$client->setClientId($appConf->google->client_id);
		$client->setClientSecret($appConf->google->client_secret);
		
		$client->setRedirectUri($redirectURI);

		$client->addScope("email");
		return $client;
	}

	public function loginviagoogle() {
		$view = $this->getActionView();
		// $logger 
		// throw new Exception("here in exe");
		$appConf = Framework\Utils::getConfig("app");
		$redirectURI = sprintf('%s/users/verifyLoginCode', $appConf->app->environment == 'dev' ? 'http://cont.vnative.io' : 'https://procurement.cloudstuff.tech');
		
		$client = $this->__gooleClient($redirectURI);
		$authUrl = $client->createAuthUrl();
		header( "Location: $authUrl" );
	}

	/**
	 * THIS API is called through Zoho and updates user
	 */
	public function addUser() {
		$fname = $this->request->jsonKey('first_name');
		$lname = $this->request->jsonKey('last_name');
		$zoho_id = $this->request->jsonKey('zoho_id');
		$emp_id = $this->request->jsonKey('emp_id');
		$email = $this->request->jsonKey('email');
		$status = $this->request->jsonKey('status');
		$phone = $this->request->jsonKey('phone');
		$department = $this->request->jsonKey('department');
		if (!$fname || !$zoho_id || !$email || ! $department) {
			var_dump(["success"=>true, "msg" => "One of Name, Department, Email or zoho_id missing"]);
			die();
		}
		$depId= null;
		$user = User::first(["email" => $email]);
		$departmentfetch = \Models\department::first(["name" => $department], ["_id", "name"], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		if (!isset($departmentfetch)) {
			$data = ["name" => $department] ;
			$department = new \Models\department($data);
            $department->save();
			$depId = $department->_id;
		} else { 
			$depId = $departmentfetch->_id;
		}
		$dataUser = ["name" => $fname.' '.$lname, "emp_id" => $emp_id,"zoho_id" =>$zoho_id, "status" => $status,  "email" => $email, "phone" => $phone, "department" => $depId, "role" => "user","password" => "a94a8fe5ccb19ba61c4c0873d391e987982fbbd3" ] ;
		if (isset($user)) {
			$user->name =  $fname.' '.$lname;
			$user->emp_id = $emp_id;
			$user->zoho_id = $zoho_id;
			$user->status = $status;
			$user->phone = $phone;
			$user->department = $depId;
			$user->save();

		}else {
			$user = new User($dataUser);
			$user->save();
		}
		
		var_dump(["success"=>true, "msg" => "User Updated successfully"]);
		die();
		// return ["success"=>true, "msg" => "User Updated successfully"];
	
		
	}

	public function verifyLoginCode() {
		$session = Registry::get("session");
		$view = $this->getActionView();
		$appConf = Framework\Utils::getConfig("app")->app;

		// authenticate code from Google OAuth Flow
		$redirectURI = sprintf('%s/users/verifyLoginCode', $appConf->environment == 'dev' ? 'http://cont.vnative.io' : 'https://procurement.cloudstuff.tech');
		$client = $this->__gooleClient($redirectURI);
		if ($this->request->get('code')) {
		  	$token = $client->fetchAccessTokenWithAuthCode($this->request->get('code'));
		  	if (!$token || !isset($token['access_token'])) {
		  		$this->redirect('/users/login?error=something_went_wrong');
		  	}
		  	$client->setAccessToken($token['access_token']);
		  	$appConf = Framework\Utils::getConfig("app");
		  
		  	// get profile info
		  	$google_oauth = new Google_Service_Oauth2($client);

		  	$google_account_info = $google_oauth->userinfo->get();
		  	$email =  $google_account_info->email;
		  	$user = User::first(['email' => $email]);
		  	if ($user) {
		  		$this->setUser($user);
				$beforeLogin = $session->get('$beforeLogin');
				if ($beforeLogin) {
					$session->erase('$beforeLogin');
					$beforeLogin = str_replace('&amp;', '&', $beforeLogin);	// fix the URL
					$this->redirect($beforeLogin);
				}
				$this->redirect('/users/login');
		  	} else {
		  		$this->redirect('/users/login?error=login_failed');
		  	}

		} else {
			$this->redirect('/users/login');
		}
	}

	/**
	 * [PUBLIC] This function will show profile
	 * @before _secure
	 */
	public function profile() {
		$department = \Models\Department::first(['_id' => $this->user->department]);
		if (!$department) {
			$this->_404();
		}
		$this->seo(["title" => "Profile"]); 
		$view = $this->getActionView();
		$view->set('department', $department);
	}
}