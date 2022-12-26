<?php

use Shared\Controller as Controller;
use Shared\Services\Db;
use Framework\{Registry, TimeZone, ArrayMethods};

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
        $assetsAssigned = \Models\Asset::selectAll(['status' => 'assigned'], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $assetsAvailable = \Models\Asset::selectAll(['status' => 'available'], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $vendors = \Models\Vendor::selectAll([], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);

        $view->set('employees', $employees);
        $view->set('vendors', $vendors);

        $view->set('assetsAvailable', $assetsAvailable);
        $view->set('assetsAssigned', $assetsAssigned);
        $view->set('monthlyTotal', $totalMonthly);
        $view->set('total', $total);
        $view->set('contractLines', $contractsTodayLines);
    
        $groupByOptions = ['department', 'status', 'requester_id', 'activity_id'];
        $uiQuery = $this->request->get('query', []);
        $view->set("optionsGroupBy", $groupByOptions);
        $this->seo(["title" => "Dashboard"]); 
        $view->set("query", $uiQuery ?? []);
        $label = [];
        $data = [];
        $dq = ['start' => $this->request->get('start'), 'end' => $this->request->get('end')];
        $query['created'] = Db::dateQuery($dq['start'], $dq['end']);
        $purchasereq = \Models\Purchasereq::selectAll($query, [$uiQuery['option'] ?? 'amount', $uiQuery['groupby'] ?? 'department'], ['maxTimeMS' => 5000 ]);
        $purchasereq = \Models\Purchasereq::groupBy($purchasereq, [$uiQuery['groupby'] ?? 'department'], [$uiQuery['groupby'] ?? 'department', $uiQuery['option'] ?? 'amount'] );
        foreach ($purchasereq as $key => $value) {
            $label[] = $key ?? ' not set';
            
            if (isset($uiQuery['option']) && $uiQuery['option'] == 'count') {
                $data[] = $value['count'];

            } else {
                $amount = \Models\purchasereq::getAmountAll(['amount' => $value['amount']]);
                $data[] = $amount;
            }
        } 
        $label2 = [];
        if (isset($uiQuery['groupby'])) {
            switch ($uiQuery['groupby']) {
                case 'department':
                    $department = \Models\Department::selectAll(['_id' => ['$in' => $label]], [], ['maxTimeMS' => 5000 ]);
                    $label2 = ArrayMethods::arrayKeys($department, 'name');
                    break;
                case 'requester_id':
                    $users = User::selectAll(['_id' => ['$in' => $label]], [], ['maxTimeMS' => 5000 ]);
                    $label2 = ArrayMethods::arrayKeys($users, 'name');
                    break;
                case 'activity_id':
                    $activity = \Models\Activity::selectAll(['_id' => ['$in' => $label]], [], ['maxTimeMS' => 5000 ]);
                    $label2 = ArrayMethods::arrayKeys($activity, 'name');
                    break;
                case 'status':
                    $label2 = $label;
                    break;
                default:
                    # code...
                    break;
            }

        }
        $view->set("data", $data)->set("label", $label2);
        $view->set("start", $this->request->get('start'));
        $view->set("end", $this->request->get('end'));
        $view->set("query", $this->request->get('query'));
        $view->set("chart", isset($uiQuery['chart']) ? $uiQuery['chart'] : 'pie');
    
	
    }
}