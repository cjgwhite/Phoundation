<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PDOConnector
 *
 * @author chris
 */
class PDOConnector {

    public function connect($adapter, $host, $user, $pwd, $db) {
        global $APP_ROOT, $system_log;
        switch ($adapter) {
            case 'mysql':
                $dsn = $adapter . ':host=' . $host . ';dbname=' . $db;
                $conn = new PDO($dsn, $user, $pwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
                break;
            case 'sqlite':
                $system_log->debug("$adapter:$APP_ROOT/$db");
                $dsn = $adapter . ':' . $APP_ROOT . '/' . $db;
                $conn = new PDO($dsn);
                break;
        }
        return $conn;

    }

}
?>
