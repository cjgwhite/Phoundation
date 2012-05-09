<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Local_Authorisor
 *
 * @author chris
 */
class Local_Authorisor {
    private function getAuthDetails() {
        global $APP_ROOT;
        $authconf = file_get_contents("$APP_ROOT/system/config/auth.xml");
        return new SimpleXMLElement($authconf);
    }
    public function authorise($url) {
        global $APP_ROOT, $system_log;

        $system_log->debug($_SESSION['USER']->rolelist);

        $authorised = true;

        //Check the user is authorised to view the requested resource
        $authorisor = $this->getAuthDetails();

        $authCount = 0;
        $matchedResources = 0;
        foreach($authorisor->resource as $res) {
            $pattern = "".$res->pattern;
            if (eregi($pattern, $url) !== false) {

                $system_log->debug("matched $pattern");

                $matchedResources++;

                if (isset($res->rolelist) && isset($_SESSION['USER']->rolelist)) {
                    $rolelist = $res->rolelist;
                    if ($this->checkRoles($rolelist))
                        $authCount++;
                }
                if (isset($res->userlist)) {
                    $userlist = $res->userlist;

                    if ($this->checkUser($userlist))
                        $authCount++;
                }
            }
        }

        if ($authCount >= $matchedResources)
            return true;
        else
            return false;

    }
/*
    public function authoriseProcess($controller, $processName) {

        $authorised = false;

        $authorisor = $this->getAuthDetails();

        foreach($authorisor->process as $process) {
            if ($process->controller == $controller) {
                foreach ($process->processor as $processor) {
                    if ($processor->method == $processName) {
                        if (isset($processor->rolelist))
                            $authorised = $this->checkRoles($processor->rolelist);
                        if (!$authorised && isset($processor->userlist))
                            $authorised = $this->checkUser($processor->userlist);
                        break;
                    }
                }
                break;
            }
        }

        return $authorised;
    }
*/
    public function authoriseProcess($url, $processName) {

        $authorised = false;

        $authorisor = $this->getAuthDetails();

        foreach ($authorisor->resource as $res) {
            $pattern = "".$res->pattern;
            if (eregi($pattern, $url) !== false) {
                foreach($res->process as $process) {

                    foreach ($process->processor as $processor) {
                        if ($processor->method == $processName) {
                            if (isset($processor->rolelist))
                                $authorised = $this->checkRoles($processor->rolelist);
                            if (!$authorised && isset($processor->userlist))
                                $authorised = $this->checkUser($processor->userlist);
                            break;
                        }
                    }
                    break;

                }
            }
        }

        return $authorised;
    }

    private function checkRoles($rolelist) {
        global $system_log;
        if (!isset($_SESSION['USER']->rolelist))
            return false;

        $userRoles = $_SESSION['USER']->rolelist;

        $system_log->debug("Checking ... roles");
        $system_log->debug($userRoles);
        $system_log->debug($rolelist);

        foreach ($rolelist->role as $role) {
            foreach ($userRoles as $userrole) {
                if (strtoupper($userrole) == strtoupper($role)) {
                    return true;
                }
            }
        }

        return false;
    }
    private function checkUser($userlist) {
        if (!isset($_SESSION['USER']->username))
            return false;

        foreach ($userlist->username as $user) {
            if (strtoupper($_SESSION['USER']->username) == strtoupper($user)) {
                return true;
            }
        }

        return false;
    }
}
?>
