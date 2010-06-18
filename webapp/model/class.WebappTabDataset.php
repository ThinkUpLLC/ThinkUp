<?php
class WebappTabDataset {
    var $name;
    var $fetching_object;
    var $fetching_method;
    var $params;

    function __construct($name, $fetching_object, $fetching_method, $params) {
        $this->name = $name;
        $this->fetching_object = $fetching_object;
        $this->fetching_method = $fetching_method;
        $this->params = $params;
    }
}
?>
