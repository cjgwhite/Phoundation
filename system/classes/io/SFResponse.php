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
    private $xsls = array();
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
            $this->domi->Render($this->xsls, $this->$format);
        }
        
        
        $this->log->debug("Render Completed");
    }

    public function clearXsls(){
        $this->xsls = array();
    }
    public function removeXsl($xsl) {
        global $APP_ROOT;
        $filepath = "$APP_ROOT/view/$xsl";
        $key = array_search($filepath, $this->xsls);
        if ($key !== false)
            unset($this->xsls[$key]);
    }
    public function addXsl($xsl) {
        $this->log->debug("adding XSL : $xsl");
        if (is_array($xsl))
            foreach ($xsl as $thisXsl)
                $this->addXslFile($thisXsl);
        else
            $this->addXslFile($xsl);
    }
    private function addXslFile($xsl) {
        global $APP_ROOT;
		$filepath = "$APP_ROOT/view/$xsl";
        if (file_exists($filepath)) {
            if (array_search($filepath, $this->xsls) === false)
                $this->xsls[] = $filepath;
            $this->log->debug("XSL - $filepath - added");
        } else {
            $this->log->warning("XSL - $filepath - not found");
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
