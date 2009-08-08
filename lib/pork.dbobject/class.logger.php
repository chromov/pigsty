<?php


/**
 * Logger
 *
 * The logger provides basic logging / debug info storage to the database. Needs to be extended in the future.
 * 
 * @todo Make the auto-dbtype-switch if available.
 * @todo If all else fails: use emails. 
 * @package Pork-Utilities
 * @author Jelle Ursem
 * @copyright Jelle Ursem 2009
 * @version 1.0
 * @access public
 */
class Logger {
	private $checked= false;
	private $debugmode = false;

	#queries to create the initial table for your logging.
	private $createqueries = array(
		"sqlite" => "CREATE TABLE '@logtable@' ('RequestID' VARCHAR(255) NOT NULL, 'Message' VARCHAR(255) NOT NULL, 'Payload' TEXT NULL , 'Type' VARCHAR (20) NULL, 'Class' VARCHAR( 255 ) NULL , 'Function' VARCHAR( 255 ) NULL , 'File' VARCHAR( 255 ) NULL ,'Line' INT( 11 ) NULL , 'Time' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 'POST' TEXT NULL , 'GET' TEXT NULL , 'URI' VARCHAR( 255 ) NOT NULL , 'REMOTE_ADDR' VARCHAR( 25 ) NOT NULL )",
		"mysql" => "CREATE TABLE `@logtable@` ( `ROWID` int(11) NOT NULL auto_increment, `RequestID` varchar(255) NOT NULL, `Message` varchar(255) NOT NULL, `Payload` text NOT NULL, `Type` varchar(20) NOT NULL, `Class` varchar(255) NOT NULL, `Function` varchar(255) NOT NULL, `Line` int(11) NOT NULL, `File` varchar(255) NOT NULL, `Time` timestamp NOT NULL, `POST` text NOT NULL, `GET` text NOT NULL, `URI` varchar(255) NOT NULL, `REMOTE_ADDR` varchar(25) NOT NULL, PRIMARY KEY  (`ROWID`) ) AUTO_INCREMENT=0 ;"
	);

	public $RequestID;

	/**
	 * Logger::__construct()
	 */
	public function __construct()
	{
		$this->RequestID = md5($_SERVER['REQUEST_URI'].$_SERVER['REMOTE_ADDR'].$_SERVER['REQUEST_TIME']).time().uniqid();
	}

	/**
	 * Logger::Log()
	 * Sends a LOG message to the log. This will not work if debugmode is disabled.
	 */
	public static function Log($var)
	{
		if(Settings::Load()->Get('Logger', 'debug') == '1')
		{
			$trace = 
			self::getInstance()->createLog(Array('Variable' => $var, 'Parameters' => func_get_args()), 'LOG', debug_backtrace());
		}
	}

	/**
	 * Logger::Info()
	 * Sends an INFO message to the log. This will not work if debugmode is disabled.
	 */
	public static function Info($var)
	{
		if(Settings::Load()->Get('Logger', 'debug') == '1')
		{
			self::getInstance()->createLog(Array('Variable' => $var, 'Parameters' => func_get_args()), 'INFO', debug_backtrace());
		}
	}

	public static function Dump($var)
	{
		if(Settings::Load()->Get('Logger', 'debug') == '1')
		{
			self::getInstance()->createLog(Array('Variable' => $var, 'Parameters' => func_get_args()), 'DUMP', debug_backtrace());
		}
	}
		
	/**
	 * Logger::Warn()
	 * Sends a warning to the log. This will also work if debugmode is disabled.
	 */
	public static function Warn($var)
	{
		self::getInstance()->createLog(Array('Variable' => $var, 'Parameters' => func_get_args()), 'WARN', debug_backtrace());
	}


	public static function Error($var)
	{
		self::getInstance()->trace[] = debugLog::Log(Array('Parameters' => func_get_args()), 'ERROR', debug_backtrace());
	}


	/**
	 * Logger::Trace()
	 * Sends a debug_print_backtrace to the log. This will also work if debugmode is disabled.
	 */
	public static function Trace($var)
	{
		$trace = debug_backtrace();
		self::getInstance()->PHPError($var, 'TRACE', $trace[sizeof($trace) == 1 ? 0 : 1]['file'], $trace[sizeof($trace) == 1 ? 0 : 1]['line'], $trace);
//		self::getInstance()->createLog(Array('Variable' => $var, 'Parameters' => func_get_args(), 'Trace'=>$trace), 'TRACE');
	}

	
	/** 
	 * Logger::PHPError
	 * Catches PHP errors to the database.
	 */
	public function PHPError($errstr, $err,  $errfile, $errline, $backtrace)
	{
		if((($err) == 'NOTICE' || $err == 'STRICT NOTICE') && $this->debugmode == false) return;
		$log = new DebugLog();

		$log->Payload = @json_encode($backtrace);
		$log->Message = $errstr;
		$log->REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		$log->URI = $_SERVER['REQUEST_URI'];
		$log->POST = !empty($_POST) ? @json_encode($_POST) : "";
		$log->GET = !empty($_GET) ? @json_encode($_GET) : "";
		$log->RequestID = $this->RequestID;

		$log->Class = @$backtrace[0]['class'];
		$log->Function = @$backtrace[0]['function'];
		$log->Type = $err;
		$log->File = $errfile;
		$log->Line = $errline;
		$log->Time = now();
		$log->Save();
		
	}

	/**
	 * Logger:: CreateLog
	 * Used internally and externally to actually insert data into the db.
	 *
	 */
	public function createLog($debug, $type='LOG', $trace=false)
	{
		$log = new DebugLog();

		$log->Message = ($type != 'DUMP' && sizeof($debug['Parameters']) == 1 && is_string($debug['Variable'])) ? $debug['Variable'] : json_encode($debug);
		$log->RequestID = $this->RequestID;
		$log->REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		$log->URI = $_SERVER['REQUEST_URI'];
		$log->POST = !empty($_POST) ? json_encode($_POST) : "";
		$log->GET = !empty($_GET) ? json_encode($_GET) : "";
		$log->Type = $type;
		$log->Time = now();
		
		if(!$trace) $trace = debug_backtrace();
		$curlog = (sizeof($trace) > 2) ? $trace[2] : (sizeof($trace)> 1) ?  $trace[1] : $trace[0];
		
		if($type == 'TRACE') {
			ob_start();			
			debug_print_backtrace();
			$debug['Trace']= nl2br(ob_get_clean());
			$log->Payload = json_encode($debug);
		}
		if($type == 'DUMP') {
			$log->Payload = json_encode($debug);
		}
		$log->Function = @$curlog['function'];
		$log->Class = @$curlog['class'];
		$log->Line = @$curlog['line'];
		$log->File = @$curlog['file'];

		$log->Save();
	}



	/**
	 * Logger::logEmail()
	 * 
	 * @todo make it so that this sends an email and is automatically used as fallback.
	 * @param mixed $message
	 */
	public static function logEmail($message)
	{
		echo('email log '.$message);

	}
	
	/**
	 * Logger::dbCheck()
	 * Checks if the current database exists, and creates it if it's not there.
	 */
	public function dbCheck()
	{
		if(!$this->checked)
		{
			$this->log[] = "checking if db exists";
			$this->checked = dbConnection::getInstance('Logger')->tableExists(Settings::Load()->Get('Logger', 'logtable'));
			if (!$this->checked) 
			{	
				$createquery = $this->createqueries[strtolower(Settings::Load()->Get('Logger', 'dbtype'))];
				$createquery = str_replace('@logtable@', Settings::Load()->Get('Logger', 'logtable'), $createquery);
				$this->checked = dbConnection::getInstance('Logger')->query($createquery);
				if(!$this->checked) die( "Error creating logdatabase");
				Logger::Log("Log database created.");
			}	
		}
	}		


	/**
	 * Logger::getInstance()
	 * Singleton functionality
	 * 
	 * @return Logger $instance
	 */
	public static function getInstance()
    {
		static $instance;
		if (!isset($instance)) 
		{
			 $c = __CLASS__;
		     $instance = new $c;
			 $instance->dbCheck();
		}
        return $instance;
    }



}

/**
  *
  * DebugLog dbObject class. As soon as the database is created, this is used to insert records into the debuglog table.
  *
  */
class DebugLog extends dbObject
{
		function __construct($ID=false)
        {
            $this->__setupDatabase('debuglog', // db table
                array('ROWID' => 'ID',    // db field => object property
						'RequestID' => 'RequestID',
						'Message' => 'Message',
                        'Payload' => 'Payload', 
                        'Type' => 'Type', 
                        'Class' => 'Class', 
                        'Function' => 'Function', 
                        'Line' => 'Line',
						'File' => 'File',
                        'Time' => 'Time', 
                        'POST' => 'POST', 
                        'GET' => 'GET', 
                        'URI' => 'URI', 
                        'REMOTE_ADDR' => 'REMOTE_ADDR'),
                        'ROWID',    // primary db key 
                        $ID, 'Logger');    // primary key value 
			$this->setOrderProperty('Time', 'DESC');	
		}
}

	

?>