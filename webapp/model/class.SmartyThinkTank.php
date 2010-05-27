<?php
class SmartyThinkTank extends Smarty {

    function SmartyThinkTank() {
        $config = Config::getInstance();
        $this->Smarty();
        $this->template_dir = array('view');
        $this->compile_dir = 'view/compiled_view/';
        $this->plugins_dir = array('plugins', 'view/plugins/');
        $this->cache_dir = 'view/compiled_view/cache';
        $this->cache_lifetime = 300;
        $this->caching = $config->getValue('cache_pages');
        $this->assign('app_name', $config->getValue('app_title'));
    }
}
?>
