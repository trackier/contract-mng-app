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
            { 	$pass = sha1($password);
				$user = User::first(array( "email=?" =>$email, "password=?" =>$pass,));
				if (!empty($user))
                {	$this->setUser($user);
					$session = Registry::get("session");
					$user = $this->getUser();
					$this->redirect('/contract/manage');
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
		$seo = ["title" => "Change Password", "view" => $this->getLayoutView()];
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
		$appConf = Framework\Utils::getConfig("app");
		$redirectURI = sprintf('%s/users/verifyLoginCode', 'http://cont.vnative.io');
		$client = $this->__gooleClient($redirectURI);
		$authUrl = $client->createAuthUrl();
		var_dump($authUrl);
		die();
		$view->set('link', $authUrl);
	}

	public function verifyLoginCode() {
		$session = Registry::get("session");
		$view = $this->getActionView();
		$appConf = Framework\Utils::getConfig("app")->app;

		// authenticate code from Google OAuth Flow
		$redirectURI = sprintf('%s/users/verifyLoginCode', 'http://cont.vnative.io');
		$client = $this->__gooleClient($redirectURI);
		if ($this->request->get('code')) {
		  	$token = $client->fetchAccessTokenWithAuthCode($this->request->get('code'));
		  	if (!$token || !isset($token['access_token'])) {
		  		$this->redirect('/auth/login?error=something_went_wrong');
		  	}
		  	$client->setAccessToken($token['access_token']);
		  	$appConf = Framework\Utils::getConfig("app");
		  
		   
		  	// get profile info
		  	$google_oauth = new Google_Service_Oauth2($client);
		  	$google_account_info = $google_oauth->userinfo->get();
		  	$email =  $google_account_info->email;
			$email = self::SPECIAL_EMAIL_CASES_MAPPING[$email] ?? $email;
		  	$user = Models\User::first(['email' => $email]);

		  	if ($user) {
			  	
				$this->setUser($user);
				$beforeLogin = $session->get('$beforeLogin');
				if ($beforeLogin) {
					$session->erase('$beforeLogin');
					$beforeLogin = str_replace('&amp;', '&', $beforeLogin);	// fix the URL
					$this->redirect($beforeLogin);
				}
				$this->redirect('/admin/index');
		  	} else {
		  		$this->redirect('/auth/login?error=login_failed');
		  	}

		} else {
			$this->redirect('/auth/login');
		}
	}
}