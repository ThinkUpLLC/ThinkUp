<?php
/**
 * Webapp
 *
 * Provides hooks for webapp plugins.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Webapp extends PluginHook {
    /**
     * @var array WebappTab
     */
    private $webappTabs = array();
    /**
     *
     * @var string
     */
    private $activePlugin = "twitter"; //default to twitter
    /**
    * Returns active plugin
    */
    function getActivePlugin() {
        return $activePlugin;
    }
    /**
     * Sets active plugin
     * @param string $ap
     */
    function setActivePlugin($ap) {
        $this->activePlugin = $ap;
    }
    /**
     *
     * @param Instance $instance
     */
    function getChildTabsUnderPosts($instance) {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;
        return $p->getChildTabsUnderPosts($instance);
    }
    /**
     *
     * @param Instance $instance
     */
    function getChildTabsUnderReplies($instance) {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;
        return $p->getChildTabsUnderReplies($instance);
    }
    /**
     *
     * @param Instance $instance
     */
    function getChildTabsUnderFriends($instance) {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;
        return $p->getChildTabsUnderFriends($instance);
    }
    /**
     *
     * @param Instance $instance
     */
    function getChildTabsUnderFollowers($instance) {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;
        return $p->getChildTabsUnderFollowers($instance);
    }
    /**
     *
     * @param Instance $instance
     */
    function getChildTabsUnderLinks($instance) {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;
        return $p->getChildTabsUnderLinks($instance);
    }
    /**
     *
     * @param Instance $instance
     */
    function getAllTabs($instance)  {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;

        $post_tabs = $p->getChildTabsUnderPosts($instance);
        $reply_tabs = $p->getChildTabsUnderReplies($instance);
        $friend_tabs = $p->getChildTabsUnderFriends($instance);
        $follower_tabs = $p->getChildTabsUnderFollowers($instance);
        $links_tabs = $p->getChildTabsUnderLinks($instance);

        return array_merge($post_tabs, $reply_tabs, $friend_tabs, $follower_tabs, $links_tabs);
    }
    /**
     *
     * @param string $tabShortName
     * @param Instance $instance
     */
    function loadRequestedTabData($tabShortName, $instance) {
        global $s; //TODO: don't global this

        $all_tabs = $this->getAllTabs($instance);
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