<?php
class HelloThinkTankPlugin implements CrawlerPlugin {

    public function renderConfiguration($owner) {
        $controller = new HelloThinkTankPluginConfigurationController($owner);
        return $controller->go();
    }

    public function crawl() {
        //echo "HelloThinkTank crawler plugin is running now.";
    }
}