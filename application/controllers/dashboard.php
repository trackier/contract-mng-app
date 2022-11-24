<?php

use Shared\Controller as Controller;
use Framework\{Registry, TimeZone};
use Shared\Services\Db;

use Framework\RequestMethods as RequestMethods;
class Dashboard extends Controller
{
	/**
     * [PUBLIC] This function will set the data of daily, monthly contracts in the view
	 * @before _secure
	 */
	public function view() {	
        $this->seo(["title" => "Dashboard"]); 
		$contractsTodayLines = [];
		$view = $this-> getActionView();
	    $query['live'] = $this->request->get('live', 0);
		$total = Contracttbl::count($query) ?? 0;
        $first_day_this_month = date('Y-m-01'); 
        $last_day_this_month  = date('Y-m-t');
        $dq = TimeZone::dateRangeQuery(['start' => $first_day_this_month, 'end' =>  $last_day_this_month ]);
		$dateQuery = Db::dateQuery($dq['start'], $dq['end']);
		$queryMonthly["created"] = $dateQuery;

        //Get all contract addedd today
        $dqToday = TimeZone::dateRangeQuery(['start' => date('Y-m-d'), 'end' => date('Y-m-d')]);
		$dateQueryToday = Db::dateQuery($dqToday['start'], $dqToday['end']);
		$queryToday["created"] = $dateQueryToday;
        $contractsToday = Contracttbl::selectAll($queryToday, [], [ 'order'=> 'created', 'direction' => 'desc', 'maxTimeMS' => 5000 ]);
        foreach ($contractsToday as $contract) {
            $line = 'Contract: '.$contract->cname.' of type '.$contract->type.' was added Today';
            $contractsTodayLines[] = $line;
        }
        $total = Contracttbl::count($query) ?? 0;
        $totalMonthly = Contracttbl::count($queryMonthly) ?? 0;
        $employees = User::selectAll([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $assets = \Models\Asset::selectAll([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $assigned = \Models\Assigned::selectAll([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $vendors = \Models\Vendor::selectAll([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);

        $view->set('employees', $employees);
        $view->set('assigned', $assigned);
        $view->set('vendors', $vendors);

        $view->set('assets', $assets);
        $view->set('monthlyTotal', $totalMonthly);
        $view->set('total', $total);
        $view->set('contractLines', $contractsTodayLines);
	
    }
}