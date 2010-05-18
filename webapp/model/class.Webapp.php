<?php 
class Webapp extends PluginHook {
    //Define parent tabs as constants
    const POSTS_TAB = 1;
    const REPLIES_TAB = 2;
    const FOLLOWERS_TAB = 3;
    const FRIENDS_TAB = 4;
    const LINKS_TAB = 5;
    
    private $webappTabs = array();
    
    function registerChildTab($parentTab, $childTabName, $object, $objectMethod, $params) {

    
    }
    
}
?>
