<?php 
class HelloThinkTankPlugin implements iCrawlerPlugin {

    public function renderConfiguration() {
        global $s;
        $s->assign('message', 'Hello, world! This is the configuration page for the test plugin.');
    }
    
    public function crawl() {
        //echo "HelloThinkTank crawler plugin is running now.";
    }
}
?>
