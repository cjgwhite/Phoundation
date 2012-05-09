<?php
class Authenticator {

    public static function login($username, $password) {
        global $system_log;
        $system_log->debug("Authenticator::login()");

        if (!$_SESSION['USER'] || $_SESSION['USER']->username != $username) {
            $system_log->debug("logging in user");
            $authMods = explode(",",Config::get("system.authentication.module"));
            foreach ($authMods as $authModPrefix) {
                $authMod = $authModPrefix . "_Authenticator";
                $system_log->debug("Authentication module = $authMod");
                $authObj = new $authMod();
                $ret = $authObj->authenticate($username, $password);
                if ($ret != false) {
                    $system_log->debug("Creating USER object");
                    $attMap = AttributeMapper::newinstance($authModPrefix, $ret);
                    $_SESSION['USER'] = new User($attMap);
                    $system_log->debug("Authentication success - $authMod");
                    return true;

                } else {
                    $_SESSION['USER'] = false;
                    $system_log->debug("Authentication failed - $authMod");
                }
            }
        }

        return false;
    }
}
?>