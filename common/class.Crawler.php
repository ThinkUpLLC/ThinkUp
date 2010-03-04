<?php

class Crawler extends PluginHook {
	function crawl()  {
		$this->emit('crawl');
	}
}

?>