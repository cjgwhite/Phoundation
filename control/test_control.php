<?php

class test_control extends DefaultControl {

    public function __default_render($params) {

        $DB = DatabaseFactory::getConnection();

        $result = $DB->query("SELECT * FROM test_table");
        $data['result'] = $result->fetchAll();

        $this->response->addData($data, "content");
        $this->response->addXsl("pages/test.xsl");

    }

}

?>
