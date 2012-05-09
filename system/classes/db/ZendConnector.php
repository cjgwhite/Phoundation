<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ZendConnector
 *
 * @author chris
 */
class ZendConnector {
    
    public function connect($adapter, $host, $user, $pwd, $db) {
        
        $DB = Zend_Db::factory($adapter, array(
            'host'     => $server,
            'username' => $user,
            'password' => $pwd,
            'dbname'   => $db
        ));

        //Make queries return arrays of objects
        $DB->setFetchMode(Zend_Db::FETCH_OBJ);

        return $DB;
        
    }

}
?>
