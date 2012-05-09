<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Authorisor
 *
 * @author chris
 */
class Authorisor {

    public function authorise($url) {
        global $APP_ROOT, $system_log;

        $system_log->debug("Authorising...");

        if (!isset($_SESSION['USER']))
            return false;

        //User hjas not been authenticated
        if (!$_SESSION['USER'])
            return false;

        //Deny / Allow
        $authorised = false;

        $authMod = Config::get("system.authorisation.module") . "_Authorisor";
        $system_log->debug("$authMod");
        $authObj = new $authMod();
        $authorised = $authObj->authorise($url);
        
        if ($authorised)
            $system_log->debug("Success!");
        else
            $system_log->debug("Failed!");

        return $authorised;

    }

    public function authoriseProcess($url, $process) {
        $authorised = false;
        
        $authMod = Config::get("system.authorisation.module") . "_Authorisor";
        $authObj = new $authMod();
        $authorised = $authObj->authoriseProcess($url,$process);

        return $authorised;
    }
/*
    public function authoriseProcess($controller, $process) {

        $authorised = false;

        $authMod = Config::get("system.authorisation.module") . "_Authorisor";
        $authObj = new $authMod();
        $authorised = $authObj->authoriseProcess($controller,$process);

        return $authorised;

    }
*/
}
?>
