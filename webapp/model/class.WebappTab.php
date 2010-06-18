<?php
class WebappTab {
    var $short_name;
    var $name;
    var $description;
    var $datasets = array();
    var $view_template;

    function __construct($short_name, $name, $description='', $view_template='inline.view.tpl') {
        $this->short_name = $short_name;
        $this->name = $name;
        $this->description = $description;
        $this->view_template = $view_template;
    }

    function addDataset($dataset) {
        array_push($this->datasets, $dataset);
    }

    function getDatasets() {
        return $this->datasets;
    }
}
?>
