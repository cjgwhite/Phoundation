<?php


class Config {

    private static $instance = null;
    private static $confdir = "";
    private $config = array();

    private function __construct() {

    }

    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new Config();

        return self::$instance;
    }

    public static function setConfDir($confdir) {
        self::$confdir = $confdir;
    }

    private function loadall() {

    }

    private function load($configFile) {

        if (array_key_exists($configFile, $this->config))
            return true;

        if (file_exists(self::$confdir . '/' . $configFile)) {
            // Load the lines of the config file into an array...

            $confArray = array();
            $lines = file(self::$confdir . '/' . $configFile);
            foreach ($lines as $line) {

                $line = trim($line);

                // strip comments
                if (strpos($line, "#") !== false)
                    if (strpos($line, "#") > 0)
                        $line = substr($line, 0, strpos($line, "#"));
                    else
                        $line = "";

                if (strpos($line, "=")) {
                    list($name, $value) = explode("=", $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    $confArray[$name] = $value;
                }
            }
            $this->add($configFile,$confArray);
            return true;
        } else {
            return false;
        }
    }

    private function add($conffile,$config) {
        $this->config[$conffile] = $config;
    }

    public static function get($config) {
        $instance = self::getInstance();

        //split $config on first dot.
        $conf = substr($config, 0, strpos($config, "."));
        $entry = substr($config, strpos($config, ".")+1);

        if ($instance->load($conf.'.conf')) {
            $confArray = $instance->config[$conf.'.conf'];
            if (array_key_exists($entry, $confArray))
                return $confArray[$entry];
        }
        return "";

    }

}

?> 
