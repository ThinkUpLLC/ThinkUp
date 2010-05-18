<?php 
interface iPlugin {
    public function renderConfiguration();
}

interface iCrawlerPlugin extends iPlugin {
    public function crawl();
}

interface iWebappPlugin extends iPlugin {
    public function getChildTabsUnderPosts();
	
    public function getChildTabsUnderReplies();
	
    public function getChildTabsUnderFriends();
	
    public function getChildTabsUnderFollowers();
	
    public function getChildTabsUnderLinks();
}
?>
