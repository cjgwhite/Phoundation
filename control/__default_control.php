<?php

/*
 * Created on 12 Nov 2008
 *
 */

class __default_control extends DefaultControl {

    public function __default_render($params) {
        global $APP_ROOT;

        $this->log->debug("__default_control");
        $data = array();
        if (count($params) > 0)
            $path = join("/", $params);
        else
            $path = "__default";

        if (is_dir("$APP_ROOT/pages/$path"))
            $path .= "/index";

        $this->log->debug("Got path : $path");

        foreach ($_REQUEST as $key => $val)
            if (substr($key, 0, 2) != "__")
                $data[strtolower($key)] = $val;

        $this->log->debug("Got data");

        if (!file_exists("$APP_ROOT/view/pages/$path.xsl")) {
            throw new Exception("Page does not exist");
        } else {
            $this->response->addData($data, "content");
            $this->response->addXsl("pages/$path.xsl");
        }
    }

    public function edit_render($params) {
        
    }

}

?>
