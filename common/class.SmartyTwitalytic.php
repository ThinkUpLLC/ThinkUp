<?php 
class SmartyTwitalytic extends Smarty {

    function SmartyTwitalytic() {
        global $TWITALYTIC_CFG;
        $this->Smarty();
        $this->template_dir = 'templates';
        $this->compile_dir = 'templates_c/';
        $this->plugins_dir = array('plugins', 'templates/plugins/');
        $this->cache_dir = 'templates_c/cache';
        $this->cache_lifetime = 300;
        $this->caching = $TWITALYTIC_CFG['cache_pages'];
        $this->assign('app_name', $TWITALYTIC_CFG['app_title']);
    }
    
}


?>
