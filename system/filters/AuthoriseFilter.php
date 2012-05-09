<?php
/**
 * Description of AuthoriseFilter
 *
 * @author Chris White
 */
class AuthoriseFilter extends AbstractFilter {

    protected function before() {
        global $APP_ROOT;

        $authorised = Authorisor::authorise($this->url);

        if (!$authorised) {
 			$authorised = false;
		    header('WWW-Authenticate: Basic realm="Restricted Access"');
		    header('HTTP/1.0 401 Unauthorized');

 		} 

        if ($authorised)
            return self::CHAIN_NEXT;
        else
            return self::CHAIN_END;
    }

    protected function after() {
        return self::CHAIN_END;
    }
}
?>
