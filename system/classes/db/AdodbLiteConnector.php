<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdodbLiteConnector
 *
 * @author chris
 */
class AdodbLiteConnector {

    public function connect($adapter, $host, $user, $pwd, $db) {

        $DB = ADONewConnection($adapter);
        $DB->createdatabase = true ;
        $result = $db->PConnect("$server", "$user", "$pwd", "$db");
        $DB->Execute("SET NAMES utf8");

        return $DB;

    }

}
?>
