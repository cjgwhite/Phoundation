<?php

class DatabaseFactory {

    private static $connections = array();

    public static function getConnection($name = "") {
        global $APP_ROOT;

        if ($name == "")
            $name = Config::get("database.default");

        if (!isset(self::$connections[$name])) {
            $adapter = Config::get("database.$name.adapter");
            $user = Config::get("database.$name.user");
            $pwd = Config::get("database.$name.password");
            $server = Config::get("database.$name.host");
            $db = Config::get("database.$name.dbname");

            $connectorName = Config::get("database.connector") . "Connector";
            $DBConnector = new $connectorName();

            $DB = $DBConnector->connect($adapter, $server, $user, $pwd, $db);

            self::$connections[$name] = $DB;
        }

        return self::$connections[$name];
    }

}

?>