<?php
class HelloThinkUpPlugin implements CrawlerPlugin {

    public function renderConfiguration($owner) {
        $controller = new HelloThinkUpPluginConfigurationController($owner);
        return $controller->go();
    }

    public function crawl() {
        //echo "HelloThinkUp crawler plugin is running now.";
    }
}