<?php

/*
 * Created on 12 Nov 2008
 *
 */

class ControlFilter extends AbstractFilter {

    private $controlName = '__default';
    private $actionName = '__default';

    protected function before() {
        global $APP_ROOT;

        try {
            // convert the URL into a path array
            $path = explode('/', $this->url);

            $this->includePath = "";
            $match = array();
            while (count($path) > 0) {
                $node = array_shift($path);
                
                //if (eregi(".*(\.xml|\.html|\.php|\.json).*", $node, $match)) {
                if (preg_match("/.*(\.xml|\.html|\.php|\.json).*/i", $node, $match) != false) {    
                    $this->log->debug("Control: " . substr($node, 0, (strlen($match[1])) * -1));
                    $this->controlName = substr($node, 0, (strlen($match[1])) * -1) . '_control';
                    break;
                } else
                    $this->includePath .= '/' . $node;
            }

            //set the output format to the file extension in the URL
            $this->params['out-format'] = substr(strtoupper($match[1]), 1);


            // include the controler file
            $filename = "$APP_ROOT/control/" . $this->includePath . '/' . $this->controlName . '.php';
            $this->log->debug("controler filename : $filename");
            if (file_exists($filename)) {
                require_once($filename);
                $this->log->debug("$filename : INCLUDED");
            } else {
                //revert to default (index) control and push 'fake' control name onto
                //path for use as xsl
                $filename = "$APP_ROOT/control/__default_control.php";
                array_unshift($path, $this->includePath . '/' . substr($this->controlName, 0, strrpos($this->controlName, '_')));
                $this->controlName = "__default_control";
                require_once($filename);
            }


            if (!class_exists($this->controlName)) {
                throw new Exception('Controller _' . $this->controlName . '_ not found');
            }

            $class = new ReflectionClass($this->controlName);

            if (count($path) > 0
                    && $path[0] != null
                    && $path[0] != ""
                    && $class->hasMethod($path[0]. "_render")
            ) {
                $this->actionName = array_shift($path);
            }

            if (!$class->hasMethod($this->actionName . "_render"))
                throw new Exception($this->controlName . '->' . $this->actionName . '_render() -- Does not exist');

            if (count($path) > 0)
                $controlParams = &$path;

            //instantiate controller
            $controller = $class->newInstance();

            $this->log->debug("Execute : " . $this->controlName . "->" . $this->actionName . "_render()");
            // execute method
            $controller->__startAction($this->actionName.'_render', $controlParams, $this->params, $this->response);

            return self::CHAIN_NEXT;
        } catch (Exception $e) {

            throw $e;
        }
    }

    protected function after() {


        return self::CHAIN_END;
    }

}

?>
