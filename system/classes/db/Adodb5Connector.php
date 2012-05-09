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


        $conn = &ADONewConnection("$adapter");
	$conn->PConnect("$server", "$user", "$pwd", "$db");
        $conn->Execute("SET NAMES utf8");

        return $conn;

    }

}
?>
