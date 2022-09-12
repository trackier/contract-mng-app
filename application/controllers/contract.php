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
        
    //     $contracts = Contracttbl::selectAll();

    //    var_dump($contracts);
    //    die();
        // $view->set("contracts", $contracts);
       
       // var_dump($contracts);
       // die();
		// if ($this->request->post("action") == "addContract") 
		// {
		// 	$name = RequestMethods::post("contract_name"); 
        //     $company = RequestMethods::post("company");
        //     $type = RequestMethods::post("type"); 
        //     $startDate = RequestMethods::post("startDate"); 
        //     $endDate = RequestMethods::post("endDate"); 
        //     $notes = RequestMethods::post("notes"); 
        //     $users = RequestMethods::post("users"); 
		// 	$view = $this-> getActionView(); $error = false;
			
        //     {   
        //         var_dump($name, $company, $type,  $startDate,$endDate,$notes);
        //         die();
		// 		// $user = User::first(array( "email=?" =>$email, "password=?" =>$password,));
		// 		// if (!empty($user))
        //         // {
		// 		// 	$session = Registry::get("session"); $session->set("user", serialize($user));
        //         //     header("Location: /users/profile.html");
		// 		// 	exit(); 
		// 		// }
		// 		// else {
		// 		// 	$view->set("password_error", "Email address and/or password are incorrect");
		// 		//  }
		// 	}
		// }
	}

	/**
	 * @before _secure
	 */
    public function addContract() {	
		
		if ($this->request->post("action") == "addContract") 
		{
			$cname = RequestMethods::post("contract_name"); 
            $company = RequestMethods::post("company");
            $type = RequestMethods::post("type"); 
            $startDate = RequestMethods::post("startDate"); 
            $endDate = RequestMethods::post("endDate"); 
            $notes = RequestMethods::post("notes"); 
            $users = RequestMethods::post("users"); 
			//$view = $this-> getActionView(); $error = false;
            
                
            $contract = new Contracttbl([
                'cname' => $cname,
                'type' => $type,
                'company' => $company,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'notes' => $notes,
            ]);
            $contract->save();
            header("Location: /contract/manage.html");
		}
	}


}