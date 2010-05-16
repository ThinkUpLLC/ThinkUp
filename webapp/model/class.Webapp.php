<?php

class Webapp extends PluginHook {
    private $configuration_options = array();

    function configuration($plugin_name)  {
        $this->emit('configuration|'.$plugin_name);
    }
}

?>