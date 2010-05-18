<?php
class Webapp extends PluginHook {
	private $webappTabs = array();
	private $activePlugin = "twitter"; //default to twitter

	function getActivePlugin() {
		return $activePlugin;
	}

	function setActivePlugin($ap) {
		$this->activePlugin = $ap;
	}

	function getChildTabsUnderPosts() {
		$pobj = $this->getPluginObject($this->activePlugin);
		$p = new $pobj;
		return $p->getChildTabsUnderPosts();
	}

	function getChildTabsUnderReplies() {
		$pobj = $this->getPluginObject($this->activePlugin);
		$p = new $pobj;
		return $p->getChildTabsUnderReplies();
	}

	function getChildTabsUnderFriends() {
		$pobj = $this->getPluginObject($this->activePlugin);
		$p = new $pobj;
		return $p->getChildTabsUnderFriends();
	}

	function getChildTabsUnderFollowers() {
		$pobj = $this->getPluginObject($this->activePlugin);
		$p = new $pobj;
		return $p->getChildTabsUnderFollowers();
	}

	function getChildTabsUnderLinks() {
		$pobj = $this->getPluginObject($this->activePlugin);
		$p = new $pobj;
		return $p->getChildTabsUnderLinks();
	}

	function getAllTabs()  {
		$pobj = $this->getPluginObject($this->activePlugin);
		$p = new $pobj;

		$post_tabs = $p->getChildTabsUnderPosts();
		$reply_tabs = $p->getChildTabsUnderReplies();
		$friend_tabs = $p->getChildTabsUnderFriends();
		$follower_tabs = $p->getChildTabsUnderFollowers();
		$links_tabs = $p->getChildTabsUnderLinks();

		return array_merge($post_tabs, $reply_tabs, $friend_tabs, $follower_tabs, $links_tabs);
	}

	function loadRequestedTabData($tabShortName) {
		global $s;

		$all_tabs = $this->getAllTabs();
		$requested_tab = '';
		$keep_looking = true;
		foreach ($all_tabs as $pt) {
			if ($keep_looking && $pt->short_name == $tabShortName) {
				$requested_tab = $pt;
				$keep_looking = false;
			}
		}
		$s->assign('header', $requested_tab->name);
		$s->assign('description', $requested_tab->description);
		foreach ($requested_tab->datasets as $dataset) {
			$s->assign($dataset->name, call_user_func_array(array($dataset->fetching_object, $dataset->fetching_method), $dataset->params));
		}
		return $requested_tab->view_template;
	}
}
?>