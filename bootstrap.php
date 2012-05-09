<?php
/**
File name: bootstrap.php
Version: 1.0
Author: Chris White

Description:
This file is the default script. Bootstraps the application.
All requests are to be redirected through this file

VERSION HISTORY

1.0 - file created

*/
 
 require_once ('init.php');
 
 $system_log->debug('Bootstrap ***');
 $dispatcher = new Dispatcher($_REQUEST);
 $system_log->debug('Dispatch ***');
 $dispatcher->dispatch();
 $system_log->debug('Complete ***');

 require_once ('cleanup.php');
?>
