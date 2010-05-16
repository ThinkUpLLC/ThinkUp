<?php

class Webapp extends PluginHook {
    private $configuration_options = array();

    function addToConfigMenu($link, $text)  {
        $this->configuration_options[] = array($link, $text);
    }

    function getConfigMenu() {
        return $this->configuration_options;
    }

    function configuration($plugin_name)  {
        $this->emit('configuration|'.$plugin_name);
    }
}

?>