<?php 
class SmartyThinkTank extends Smarty {

    function SmartyThinkTank() {
        global $THINKTANK_CFG;
        $this->Smarty();
        $this->template_dir = array('view');
        $this->compile_dir = 'view/compiled_view/';
        $this->plugins_dir = array('plugins', 'view/plugins/');
        $this->cache_dir = 'view/compiled_view/cache';
        $this->cache_lifetime = 300;
        $this->caching = $THINKTANK_CFG['cache_pages'];
        $this->assign('app_name', $THINKTANK_CFG['app_title']);
    }
    
}


?>
