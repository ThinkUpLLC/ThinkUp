<?php
/**
 * Webapp
 *
 * Singleton provides hooks for webapp plugins.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Webapp extends PluginHook {
    /**
     *
     * @var Webapp
     */
    private static $instance;

    /**
     * @var array WebappTabs
     */
    private $webappTabs = array();

    /**
     *
     * @var string Name of the active plugin, defaults to "twitter"
     */
    private $activePlugin = "twitter";

    /**
     * Get the singleton instance of Webapp
     * @return Webapp
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Webapp();
        }
        return self::$instance;
    }

    /**
     * Provided only for tests that want to kill object in tearDown()
     */
    public static function destroyInstance() {
        if (isset(self::$instance)) {
            self::$instance = null;
        }
    }

    /**
     * Returns active plugin
     * @return str Name of active plugin (like "twitter" or "facebook")
     */
    public function getActivePlugin() {
        return $this->activePlugin;
    }

    /**
     * Sets active plugin
     * @param string $ap
     */
    public function setActivePlugin($ap) {
        $this->activePlugin = $ap;
    }

    /**
     *
     * @param Instance $instance
     * @return array WebappTabs
     */
    public function getChildTabsUnderPosts($instance) {
        return $this->callGetTabsMethod('getChildTabsUnderPosts', $instance );
    }

    /**
     *
     * @param Instance $instance
     * @return array WebappTabs
     */
    public function getChildTabsUnderReplies($instance) {
        return $this->callGetTabsMethod('getChildTabsUnderReplies', $instance );
    }

    /**
     *
     * @param Instance $instance
     * @return array WebappTabs
     *      */
    public function getChildTabsUnderFriends($instance) {
        return $this->callGetTabsMethod('getChildTabsUnderFriends', $instance );
    }

    /**
     *
     * @param Instance $instance
     * @return array WebappTabs
     */
    public function getChildTabsUnderFollowers($instance) {
        return $this->callGetTabsMethod('getChildTabsUnderFollowers', $instance );
    }

    /**
     *
     * @param Instance $instance
     * @return array WebappTabs
     */
    public function getChildTabsUnderLinks($instance) {
        return $this->callGetTabsMethod('getChildTabsUnderLinks', $instance );
    }

    /**
     * Call the specified getTabs method
     * @param str $method_name
     * @param Instance $instance
     * @return array WebappTabs
     */
    private function callGetTabsMethod($method_name, $instance) {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;
        if (method_exists($p, $method_name)) {
            return call_user_func(array($p, $method_name), $instance);
        } else {
            throw new Exception("The ".get_class($p)." object does not have a ".$method_name." method.");
        }
    }
    /**
     *
     * @param Instance $instance
     * @return array WebappTabs
     */
    public function getAllTabs($instance)  {
        $post_tabs = $this->getChildTabsUnderPosts($instance);
        $reply_tabs = $this->getChildTabsUnderReplies($instance);
        $friend_tabs = $this->getChildTabsUnderFriends($instance);
        $follower_tabs = $this->getChildTabsUnderFollowers($instance);
        $links_tabs = $this->getChildTabsUnderLinks($instance);

        return array_merge($post_tabs, $reply_tabs, $friend_tabs, $follower_tabs, $links_tabs);
    }

    /**
     *
     * @param string $tab_short_name
     * @param Instance $instance
     * @return WebappTab Tab for instance, null if none available for given short name
     */
    public function getTab($tab_short_name, $instance) {
        $all_tabs = $this->getAllTabs($instance);
        foreach ($all_tabs as $tab) {
            if ($tab->short_name == $tab_short_name) {
                return $tab;
            }
        }
        return null;
    }
}
