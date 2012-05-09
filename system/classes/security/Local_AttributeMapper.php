<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Local_AttributeMapper
 *
 * @author chris
 */
class Local_AttributeMapper extends AttributeMapper {

    protected function firstname_mapper() {
        return $this->attributes->firstname;
    }
    protected function surname_mapper() {
        return $this->attributes->surname;
    }
    protected function email_mapper(){
        return $this->attributes->email;
    }
    protected function rolelist_mapper(){
        $roles = array();

        foreach ($this->attributes->rolelist->role as $role)
            $roles[] = "".$role;
        
        return $roles;
    }
}
?>
