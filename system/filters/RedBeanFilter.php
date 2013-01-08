<?php
/**
 * Description of PassThruFilter
 *
 * @author chris
 */
class RedBeanFilter extends AbstractFilter{


    private $activeList;
    
    protected function before() {
        global $APP_ROOT;

        $this->activeList = split(",", Config::get("redbean.active"));
        
        if (count($this->activeList) == 0 || (count($this->activeList) == 1 && $this->activeList == ""))
            R::setup();
        else
            foreach($activeList as $db) {
                $frozen = strtoupper(Config::get("redbean.$db.frozen")) != "FALSE";
                R::addDatabase($db, Config::get("redbean.$db.dsn"), Config::get("redbean.$db.user"), Config::get("redbean.$db.password"), $frozen);
            }
        
        return self::CHAIN_END;

    }

    protected function after(){

        foreach($activeList as $db) {
            R::selectDatabase($db);
            
            if (strtoupper(Config::get("redbean.$db.nuke")) == "TRUE")
                R::nuke();
            
            R::close();
        }
        
        return self::CHAIN_END;
    }
}
?>
