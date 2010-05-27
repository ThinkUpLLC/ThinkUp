<?php
interface WebappPlugin extends ThinkTankPlugin {
    public function getChildTabsUnderPosts();

    public function getChildTabsUnderReplies();

    public function getChildTabsUnderFriends();

    public function getChildTabsUnderFollowers();

    public function getChildTabsUnderLinks();
}
?>