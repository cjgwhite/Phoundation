<?php
abstract class AbstractFilter {

    abstract protected function before();
    abstract protected function after();


    /*
     * RETURN CODES
     *
     * These are the return values from the before() and after()
     * methods. These methods MUST return one of these values OR
     * throw and exception.
     */
    const CHAIN_END = 101; // Make this Last Filter in Chain
    const CHAIN_NEXT = 102; // Process next Filter in Chain
    const CHAIN_RESTART = 103; // Restart from beginning of Chain
    const CHAIN_TERMINATE = 104; // Cease Processing of Chain immediately
    /*
     * END - RETURN CODES
     */


    protected $params = array();
    protected $log = null;
    protected $domi = null;
    protected $response = null;
    protected $url = "";

    public function __construct($url = '') {
        global $system_log;
        $this->log = $system_log;

        $this->log->debug("filter created: " . get_class($this));
        $this->url = $url;
    }

    public function doFilter(&$response, &$params, &$chain) {

        $this->response = &$response;
        $this->params = &$params;

        try {

            $status = $this->before();
            while ($status != self::CHAIN_END) {

                switch($status) {
                    case self::CHAIN_RESTART:
                    case self::CHAIN_TERMINATE:
                        return $status;
                        break;
                    case self::CHAIN_NEXT:
                        $next = next($chain);
                        if ($next) {
                            $status = $next->doFilter($this->response, $this->params, $chain);
                            if ($status == self::CHAIN_NEXT)
                                $status = self::CHAIN_END;
                            continue 2;
                        } else {
                            $status = self::CHAIN_END;
                            continue 2;
                        }

                        break;
                }
            }

            return $this->after();
        } catch (Exception $e) {
            throw $e;
        }

    }

}
?>
