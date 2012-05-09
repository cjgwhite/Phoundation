<?php
class Local_Authenticator {
    public function authenticate($username, $password) {
        global $APP_ROOT, $system_log;

        $system_log->debug("LOCAL Authentication");

        $authdata = file_get_contents("$APP_ROOT/system/config/users.xml");
        $authdata = new SimpleXMLElement($authdata);
        foreach ($authdata->user as $user) {
            if (trim($user->username) == trim($username)) {
                if ($user->password == $password) {
                    $user->loggedin = "true";
                    return $user;
                }
            }
        }
        
        return false;
    }
}
?>
