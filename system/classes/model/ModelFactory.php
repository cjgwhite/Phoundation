<?php

class ModelFactory {


    private static $modelRoot = "/system/classes/model";
    /**
     *  createObject
     *
     * Creates a new instance of a Model Object with
     * null attributes.
     *
     * @param <type> $object
     */
    public static function createObject($objectName) {
        global $APP_ROOT;

        $object = null;

        if (file_exists("$APP_ROOT/{self::modelRoot}/$objectName.php")) {

            require_once "$APP_ROOT/{self::modelRoot}/$objectName.php";
            $object = new $objectName();

        } else {

            $DB = DatabaseFactory::getConnection();
            

        }
       
    }

    public static function getObject($object) {

    }

    public static function saveObject($object) {

    }

    public static function findObjects($object) {
        
    }

}
?>
