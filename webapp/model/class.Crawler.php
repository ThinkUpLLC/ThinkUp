<?php 
class Crawler extends PluginHook {
    function crawl() {
        $this->emitObjectMethod('crawl');
    }
    
    function registerCrawlerPlugin($object_name) {
        $this->registerObjectMethod('crawl', $object_name, 'crawl');
    }
}
?>
