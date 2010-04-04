<?php 
class SmartyThinkTank extends Smarty {

    function SmartyThinkTank() {
        global $THINKTANK_CFG;
        $this->Smarty();
        $this->template_dir = array('templates');
        $this->compile_dir = 'templates_c/';
        $this->plugins_dir = array('plugins', 'templates/plugins/');
        $this->cache_dir = 'templates_c/cache';
        $this->cache_lifetime = 300;
        $this->caching = $THINKTANK_CFG['cache_pages'];
        $this->assign('app_name', $THINKTANK_CFG['app_title']);
    }
    
}


?>
