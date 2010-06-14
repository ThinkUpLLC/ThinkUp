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
     * @param string $tabShortName
     * @param Instance $instance
     * @TODO refigure this mess, shouldn't global $s, should use Controller architecture (but how--as a controller inside a controller?)
     */
    public function loadRequestedTabData($tabShortName, $instance) {
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