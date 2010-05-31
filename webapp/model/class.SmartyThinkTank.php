<?php
class SmartyThinkTank extends Smarty {

    function SmartyThinkTank() {
        $config = Config::getInstance();
        $src_root_path = $config->getValue('source_root_path');
        $this->Smarty();
        $this->template_dir = array( $src_root_path.'webapp/view', $src_root_path.'tests/view');
        $this->compile_dir = $src_root_path.'webapp/view/compiled_view/';
        $this->plugins_dir = array('plugins', 'view/plugins/');
        $this->cache_dir = $src_root_path.'webapp/view/compiled_view/cache';
        $this->cache_lifetime = 300;
        $this->caching = $config->getValue('cache_pages');
        $this->assign('app_name', $config->getValue('app_title'));
    }
}
?>
