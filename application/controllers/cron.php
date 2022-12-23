<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Bhumika 
 */
use Shared\Controller as Controller;
use Framework\{Registry, TimeZone, ArrayMethods};

use Shared\Services\Db;

class Cron extends Shared\Controller {
    public function __construct($options = array()) {
		parent::__construct($options);
		$this->noview();
		
	}
   
	public function daily() {
		$this->log("CRON Started");
        $this->log('Peak Memory at: ' . memory_get_peak_usage());
		$this->deleteFile();
	}
   
	/**
	 * [PUBLIC] this function will delete the files scheduled or deletion
	 */
    public function deleteFile() {
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