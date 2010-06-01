<?php
interface WebappPlugin extends ThinkTankPlugin {
    public function getChildTabsUnderPosts($instance);

    public function getChildTabsUnderReplies($instance);

    public function getChildTabsUnderFriends($instance);

    public function getChildTabsUnderFollowers($instance);

    public function getChildTabsUnderLinks($instance);
}
?>