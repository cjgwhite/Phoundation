<?php

/*
 * Created on 12 Nov 2008
 *
 */

class TemplateFilter extends AbstractFilter {

    protected $nav = array();

    protected function before() {
        global $APP_ROOT;

        $templateData = array(
            "ROOT_URI" => Config::get("system.URL.root"),
            "APP_ROOT" => $APP_ROOT,
            "HOST" => $_SERVER['SERVER_NAME'],
            "PORT" => $_SERVER['SERVER_PORT'],
            "Title" => "eLearning Restricted"
        );

        $tempConf = file_get_contents("$APP_ROOT/system/config/templates.xml");
        $templates = new SimpleXMLElement($tempConf);

        $template = "default.xsl";

        //Get the template for the URL.
        foreach ($templates->template as $templ) {
            $pattern = "" . $templ->pattern;
            if (eregi($pattern, $this->url) !== false) {
                $this->log->debug("TEMPLATE MATCH : $pattern : {$this->url}");
                $template = "templates/" . $templ->filename;
                $navFile = $templ->navigation;
                break;
            }
        }

        $xml = file_get_contents("$APP_ROOT/system/config/nav/$navFile");
        $this->nav = new SimpleXMLElement($xml);
        $this->params['nav'] = &$this->nav;

        $this->response->addData($templateData, "template");
        //include the header
        $this->log->debug("TEMPLATE = $template");
        $this->response->addXsl($template);
        $this->response->addXsl("templates/nav.xsl");

        return self::CHAIN_NEXT;
    }

    protected function after() {
        $this->log->debug("Adding Navigation data");
        $this->response->addData($this->nav, "navigation");
        return self::CHAIN_END;
    }

}

?>
