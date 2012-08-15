<?php
/**
 * Description of SFResponse
 *
 * @author chris
 */
class SFResponse {

    private $HTML = DOMi::RENDER_HTML;
    private $XML = DOMi::RENDER_XML;
    private $PHP = DOMi::RENDER_HTML;
    private $PDF = DOMi::RENDER_HTML;
    private $JSON = "JSON";

    private $data = array();
    private $views = array();
    private $domi;
    private $log;

    public function __construct() {
        global $system_log;
        $this->log = &$system_log;
        $this->domi = new DOMi('data');
    }

    public function render($format) {
        global $APP_ROOT;
        
        switch (strtoupper($format)) {
            case 'XML':
                header("Content-type: text/xml; charset=UTF-8");
                break;
            case 'HTML':
            case 'PHP':
                header("Content-type: text/html; charset=UTF-8");
                break;
            case 'JSON' :
                header("Content-type: application/json; charset=UTF-8");
                break;
        }

        $this->log->debug("Converting data to XML");
        $this->domi->AttachToXml($this->data, "data");
        $this->log->debug($this->domi->saveXML());
        $this->log->debug("Rendering... $format");
        if (strtoupper($format) == "JSON") {
            $this->log->debug("Generating json output ...");

            /*
            $xsl = "$APP_ROOT/view/templates/xml2json.xsl";
            $this->domi->Render($xsl);
            */
            ob_clean();
            echo json_encode($dataroot);

        } else {
            $this->domi->Render($this->views, $this->$format);
        }
        
        
        $this->log->debug("Render Completed");
    }

    public function clearViews() {
        $this->views = array();
    }
    
    //depricated
    public function clearXsls(){
        $this->clearViews();
    }
    
    //depricated
    public function removeXsl($xsl) {
        $this->removeView($xsl);
    }
    public function removeView($view) {
        global $APP_ROOT;
        $filepath = "$APP_ROOT/view/$view";
        $key = array_search($filepath, $this->views);
        if ($key !== false)
            unset($this->views[$key]);
    }
    
    //depricated
    public function addXsl($xsl) {
        $this->addView($xsl);
    }
    public function addView($view) {
        $this->log->debug("adding XSL : $view");
        if (is_array($view))
            foreach ($view as $thisXsl)
                $this->addViewFile($thisXsl);
        else
            $this->addViewFile($view);
    }
    private function addViewFile($view) {
        global $APP_ROOT;
		$filepath = "$APP_ROOT/view/$view";
        if (file_exists($filepath)) {
            if (array_search($filepath, $this->views) === false)
                $this->views[] = $filepath;
            $this->log->debug("VIEW - $filepath - added");
        } else {
            $this->log->warning("VIEW - $filepath - not found");
        }
    }

    public function clearData() {
        $this->data = array();
    }
    public function removeData($nodeName) {
        $path = explode("/", $nodeName);
        $pos = &$this->data;
        foreach ($path as $node) {
            if (!isset($pos[$node]))
                return false;
            $pos = &$pos[$node];
        }
        unset($pos);
        return true;
    }
    public function addData($newData, $nodeName, $merge = false) {
        $this->log->debug("Adding data to node $nodeName");
        $path = explode("/", $nodeName);
        $pos = &$this->data;
        foreach ($path as $node) {
            if (!isset($pos[$node]))
                $pos[$node] = array();
            $pos = &$pos[$node];
        }

        if ($merge)
            $pos = array_merge($pos, $newData);
        else
            $pos = $newData;
    }
    
    public function addException($e) {
        $this->log->debug($e->getMessage());
        $this->addXsl("error.xsl");
        $error = array(
				"error_message" => $e->getMessage(),
    			"code" => $e->getCode(),
    			"file" => $e->getFile(),
    			"line" => $e->getLine(),
    			"trace" => $e->getTraceAsString()
		);

        $this->addData($error, "exception", true);
    }
}
?>
