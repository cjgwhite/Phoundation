<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LDAP_AttributeMapper
 *
 * @author chris
 */
class LDAP_AttributeMapper extends AttributeMapper {
    

    protected function firstname_mapper() {
        return $this->attributes['givenname'][0];
    }
    protected function surname_mapper() {
        return $this->attributes['sn'][0];
    }
    protected function email_mapper(){
        return $this->attributes['mail'][0];
    }
    protected function rolelist_mapper(){
        global $APP_ROOT;

        $roles = $this->attributes['employeetype'];
        unset($roles['count']);

        $authdata = file_get_contents("$APP_ROOT/system/config/users.xml");
        $authdata = new SimpleXMLElement($authdata);
        foreach ($authdata->user as $user) {
            if (trim($user->username) == trim($this->get("username"))) {
                foreach ($user->rolelist->role as $role)
                    $roles[] = "".$role;
                break;
            }
        }

        return $roles;
    }

}
?>
