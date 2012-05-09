<?php
/*
 * Created on 12 Nov 2008
 *
 */
 
 class AuthenticateFilter extends AbstractFilter {
 	
 	public function __construct() {
 		parent::__construct();
 	}
 	
 	protected function before() {
        $this->log->debug("Authenticating .. ");
 		// check authentication...

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
            Authenticator::login($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);

        return self::CHAIN_NEXT;

 	}
 	protected function after() {
 		
 		return self::CHAIN_END;
 	}
 	
 }
?>
