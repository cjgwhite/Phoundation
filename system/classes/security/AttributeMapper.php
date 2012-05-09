<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AttributeMapper
 *
 * @author chris
 */
abstract class AttributeMapper {
    
    private $mapDef = array();

    protected $attributes;

    public static function newInstance($name, $attributes) {
        $attMap = $name . "_AttributeMapper";
        if (class_exists($attMap))
            return new $attMap($attributes);
        else
            return null;
    }

    public function __construct($attributes) {
        $this->attributes = $attributes;
    }

    public function map($key) {

        if ($key == "sourceAttributeList")
            return $this->attributes;

        if (method_exists($this, $key . "_mapper")) {
            $meth = $key . "_mapper";
            return $this->$meth();
        } 
        
        if (isset($mapDef[$key]))
            $key = $mapDef[$key];

        if (is_array($this->attributes))
            return $this->attributes[$key];
        else if (is_object($this->attributes))
            return $this->attributes->$key;
        else return null;
    }

    public function get($key) {
        return $this->map($key);
    }

    private function username_mapper() {
        return $_SERVER['PHP_AUTH_USER'];
    }
    
    protected abstract function firstname_mapper();
    protected abstract function surname_mapper();
    protected abstract function email_mapper();
    protected abstract function rolelist_mapper();
    
}
?>
