<?php
class Dispatcher {

    private $chain = array();
    private $url = "";

    public function __construct() {
        global $APP_ROOT, $system_log;

        $this->base_url = Config::get('system.URL.root');
        $this->log = &$system_log;

        $this->log->debug('Starting Dispatcher');

        // get the URL control suffix as defined in the config
        $this->controlSuffix = Config::get("system.control.suffix");


        if (strpos($_SERVER['REQUEST_URI'],'?'))
            $url = substr($_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'],'?'));
        else
            $url = $_SERVER['REQUEST_URI'];

        // strip the base_url from the request URL
        $this->url = substr($url, strlen($this->base_url));

        if ($this->url == "/" || $this->url == "")
            $this->url = "index.html";

        $filterconf = file_get_contents("$APP_ROOT/system/config/filters.xml");
        $filters = new SimpleXMLElement($filterconf);
        $this->log->debug("URL = ".$this->url);
        foreach($filters->filter as $filter) {
            $pattern = "".$filter->pattern;
            if (eregi($pattern, $this->url) !== false) {
                $this->log->debug("match!!");
                require_once("$APP_ROOT/system/filters/" . $filter->filename);
                $filterClass = "".$filter->classname;
                $this->log->debug("filter: ".$filterClass);
                $this->chain[] = new $filterClass($this->url);
            }
        }
    }

    public function dispatch($params = array ()) {
        global $APP_ROOT;
        $this->log->debug("dispatching");
        $params = array(
            'url' => $this->url,
            'out-format' => 'HTML'
        );

        // get DOMi
        //$domi = new DOMi('data');
        $response = new SFResponse();
        $this->log->debug("Got response");
        try {
        
        //bootstrap the filter chain.
            $status = AbstractFilter::CHAIN_END;
            do {
                reset($this->chain);
                //get first filter
                $next = current($this->chain);

                $this->log->debug("do Filter ");
                if ($next)
                    $status = $next->doFilter($response, $params, $this->chain);

                $this->log->debug("CHAIN return code::$status");
            } while ($status == AbstractFilter::CHAIN_RESTART);

            if ($status == AbstractFilter::CHAIN_END) {
                $this->log->debug("Filter Chain complete");
                // if success then render
                //$domi->GenerateXsl($params['xsls']);
                $this->log->debug($params);

            } else {
                $this->log->debug("Filter Chain incomplete : return code::$status");
            }

        } catch (Exception $e) {

        // return error
            $this->log->info('Controler '.$this->controlName.' execution failed');
            $this->log->debug('Exception: ' . $e->getMessage());

            $response->addException($e);

        }


        if (!isset($params['NO_RENDER'])) {
            $response->render(strtoupper($params['out-format']));
        }

    }

}
?>
