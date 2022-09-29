<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Bhumika 
 */
use Shared\Controller as Controller;
use Framework\Registry as Registry;

use Shared\Services\Db;

class Cron extends Shared\Controller {
    
    /**
	 * Code Version variable stores the version of the code currently there
	 * @var string
	 */
	public $codeVersion;

	public function __construct($options = array()) {
		parent::__construct($options);
		$this->noview();
		
	}
    public function setLoggerConfig($appName = null) {
		parent::setLoggerConfig('webapp-cli');
	}
    public function index($type = "daily") {
		switch ($type) {
			case 'minutely':
				$this->_minutely();
				break;

			// case 'fifteenmin':
			// 	$this->_fifteenMin();
			// 	break;

			// case 'halfhourly':
			// 	$this->_halfHourly();
			// 	break;

			// case 'hourly':
			// 	$this->_hourly();
			// 	break;

			// case 'daily':
			// 	$this->_daily();
			// 	break;

			// case 'weekly':
			// 	$this->_weekly();
			// 	break;

			// case 'monthly':
			// 	$this->_monthly();
			// 	break;

			default:
				var_dump('Invalid endpoint');
				break;
		}
	}

    public function daily() {
		$this->log("CRON Started");
        $this->log('Peak Memory at: ' . memory_get_peak_usage());
		$this->deleteFile();
	}
   

    public function deleteFile() {
	
        //delete file from uploads
        //delete file contract
       
		$this->log('delete file Cron Started ');
        $todayDate = date('Y-m-d');
		$date = Db::dateQuery($todayDate, $todayDate);
        $contractFiles = ContractFile::all(['dueDelDate' => $date]);
        foreach ($contractFiles as $contractFile ) {
            $extension = pathinfo($contractFile->filename, PATHINFO_EXTENSION);
            $file_url = APP_PATH.'/public/uploads/'.$contractFile->fileId.'.'.$extension;  
			$contractFile = ContractFile::first(['id' => $contractFile->id ], []);
			$contractFile->delete();
            if(is_file($file_url))  unlink($file_url); 

        }
       $this->log('File Cron Ended');
	}

}