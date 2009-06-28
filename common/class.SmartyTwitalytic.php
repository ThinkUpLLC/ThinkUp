<?php

class SmartyTwitalytic extends Smarty {

	function SmartyTwitalytic($caching=false) {
		global $TWITALYTIC_CFG;
		$this->Smarty();
		$this->template_dir = 'templates';
		$this->compile_dir = 'templates_c/';
		$this->plugins_dir = array(
		                       'plugins','templates/plugins/');
//		$this->cache_dir = $TWITALYTIC_CFG['siteAbsolutePath'].'/smarty/cache/';
		$this->caching = $caching;
		$this->assign('app_name', $TWITALYTIC_CFG['app_title']);
	}

}


?>