<?php

/**
File name: init.php
Version: 1.0
Author: Chris White

Description:
This file is the default init script.

VERSION HISTORY

1.0 - file created

*/

//start the output buffer.
ob_start();

/*
* APP_ROOT is the directory on the server that the application is run from. 
* This is not necessarily the same as the DOCUMENT_ROOT of the server, for example
* where the application is run in a subfolder of the doc root.
*/
$APP_ROOT = substr(__FILE__, 0, strrpos(__FILE__,'/'));


set_include_path(get_include_path() . PATH_SEPARATOR . $APP_ROOT . '/system/includes/');

//session management
//ini_set('session.save_path', $APP_ROOT . '/system/sessions/');
ini_set('session.gc_maxlifetime', 2000);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_probability', 20);


//Tell PHP to detect the line endings of text not just to assume it
// This is required to cope with Machintosh line endings.
ini_set ("auto_detect_line_endings","1");


/******************************************
	START Utility Functions
*******************************************/

/**
*	findPath($dirPath, $className)
*	
* Used by __autoload to find the file that contains the class definition.
* scans the directory structure taking $dirPath as the root from which to scan
* looking for a file with the name $className.php. returns the path to the file.
*
* This functions assumes that the file containing the class is named exactly as the 
* class it contains with a .php extension. For example a class called TestClass would
* be contained in a file called TestClass.php.
*
* @param : $dirPath	-	The root path to search from
* @param : $className	-	The $className.php file to look for
* 
* @return : The path to the searched file or NULL if not found.
*/
function findPath($dirPath, $className) {

	global $APP_ROOT;


	if (file_exists($APP_ROOT.'/'.$dirPath.'/'.$className.'.php'))
		return $dirPath.'/'.$className.'.php';

	$dir = dir($APP_ROOT."/".$dirPath);
	while ($file = $dir->read()) {
		if (is_dir($dir->path."/".$file) && ($file != ".") && ($file != "..")) {
			$inc = findPath($dirPath."/".$file, $className);
			if (strpos($inc, "$className.php")) {
				return $inc;
			}
		}
	}
	
	return null;
}


/**
*	--autoload($className)
*
* Used by the system to 'include' the file containing the class definition of a class 
* refered to in the script but has not be described yet.
*
* @param : $className		-	The name of the class
*
*/
function __autoload($className) {

	global $APP_ROOT;

    $class_roots[] = "system/classes";

    foreach($class_roots as $root) {
        $inc = findPath($root, $className);
        if (strpos($inc, "$className.php")) {
            require_once $APP_ROOT . '/' . $inc;
            return;
        }
    }
}

/**************************************
	END Utility Functions
***************************************/

/**************************************
	Start Global Config setup	
***************************************/

Config::setConfDir($APP_ROOT."/system/config");

$URL_ROOT = Config::get('system.URL.root');

/***********************************
	End Global Config setup	
* ************************************/


/**********************************
	Start logging setup	
***********************************/
$LOG_TYPE = 'file';
if (Config::get('system.loglevel') == 'NONE')
  $LOG_TYPE = 'null';
$system_log = &Log::factory($LOG_TYPE, $APP_ROOT . Config::get("system.logfiledir") . "/" . $_SERVER['REMOTE_ADDR'] . ".log", "SID=".session_id(), array(), constant('PEAR_LOG_'.Config::get("system.loglevel")));
$system_log->info("Log file initialised");
/****************************
	End Logging setup	
*****************************/

$initParams = new SimpleXMLElement(file_get_contents("$APP_ROOT/system/config/init_params.xml"));
$system_log->info("Loaded init_params");

/****************************
 *     START INCLUDES
 ****************************/

foreach ($initParams->includes->include as $include) {
    switch ((string)$include['type']) {
        case 'require':
            $system_log->info("requiring ... $include");
            require_once($include);
            break;

        case 'include':
            $system_log->info("including ... $include");
            include_once($include);
            break;
    }
}

/****************************
 *     END INCLUDES
 ****************************/

/****************************
 *  START DB setup
 ****************************/
 
 //$DB = DatabaseFactory::getConnection();
 
/****************************
 *  END DB setup
 ****************************/
 
// We need to call this in order to be able to access the session object.
session_start();

$system_log->info("Request started");
$system_log->debug('Initial setup complete');


?>