<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LDAP_Authenticator
 *
 * @author chris
 */
class LDAP_Authenticator {
    public function authenticate($username, $password) {
        global $system_log;
		
		$ldapconfig['host'] = Config::get("system.ldap.host");
		$ldapconfig['port'] = Config::get("system.ldap.port");
		$ldapconfig['basedn'] = "";

        $system_log->debug("LDAP host = " . $ldapconfig['host']);
        $system_log->debug("LDAP port = " . $ldapconfig['port']);
        $system_log->debug("Username = " . $_SERVER['PHP_AUTH_USER']);

	    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
	        $ds=@ldap_connect($ldapconfig['host'],$ldapconfig['port']);
	        $r = @ldap_search( $ds, $ldapconfig['basedn'], 'uid=' . $_SERVER['PHP_AUTH_USER']);
	        if ($r) {
	            $result = @ldap_get_entries( $ds, $r);
	            if ($result[0]) {
	                if (@ldap_bind( $ds, $result[0]['dn'], $_SERVER['PHP_AUTH_PW']) ) {
                        $system_log->debug($result[0]);
                        return $result[0];
	                }
	            }
	        }
	    }
	    return false;
	}

}
?>
