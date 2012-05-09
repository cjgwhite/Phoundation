<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author chris
 */
class User {

    private $username = null;
    private $firstname = null;
    private $surname = null;
    private $email = null;
    private $rolelist = null;

    private $sourceAttributeList = null;

    public function __construct($attributeMapper = null) {
        if ($attributeMapper != null)
            $this->setAttributes($attributeMapper);
    }

    public function setAttributes($attMapper) {
        global $system_log;
        foreach ($this as $key=>$val) {
            $system_log->debug("Setting ... $key");
            if ($val == null)
                $this->$key = $attMapper->get($key);
            $system_log->debug("Set to {$this->$key}");
        }
    }

    public function __get($key) {
        return $this->$key;
    }

    public function __toString() {
        $out = "";
        foreach ($this as $key => $val) {
            if ($key != "sourceAttributeList") {
                $out .= "$key = $val\n";
            }
        }
        return $out;
    }

    public function __isset($key) {
        return isset($this->$key);
    }

}
?>
