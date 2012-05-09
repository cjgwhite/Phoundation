<?php
class DefaultControl {

// array for sending messages back to the Dispatcher
    private $callback;

    // the DOM object wrapper for adding data too.
    protected $response;

    private $params;

    protected $log;

    public function __construct() {
        global $system_log;
        $this->log = $system_log;
    }

    public function no_render() {
        $this->log->debug("NO RENDER - set");
        $this->callback['NO_RENDER'] = true;
    }

    public function __startAction($actionName, &$params, &$callBack, &$response) {
        $this->response = &$response;
        $this->callback = &$callBack;
        $this->params = &$params;

        if (isset($_REQUEST['__PROCESS__'])) {
            try {
                $processorName = strtolower(str_replace(" ", "_", $_REQUEST['__PROCESS__'])) . "_processor";

                if (Authorisor::authoriseProcess(get_class($this), $processorName))
                    $this->$processorName($_REQUEST, $params);

            } catch (Exception $e) {
                $response->addException($e);
            //throw $e;
            }
        }

        try {
            $this->$actionName($params);
        } catch (Exception $e) {
            $response->addException($e);
        //throw $e;
        }

    }
}
?>
