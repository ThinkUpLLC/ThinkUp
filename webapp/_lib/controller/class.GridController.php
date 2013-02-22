<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.GridController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Grid Controller
 *
 * Returns Unbuffered JS XSS callback/JSON list of posts for javascript grid search view
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Guillaume Boudreau
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class GridController extends ThinkUpAuthController {
    /**
     * const max rows for grid
     */
    public static $MAX_ROWS = 5000;

    /**
     * number of days to look back for retweeted posts
     */
    const MAX_RT_DAYS = 30;
    /**
     * Required query string parameters
     * @var array u = instance username, n = network
     */
    var $REQUIRED_PARAMS = array('u', 'n');
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;
    /**
     * Constructor
     * @param bool $session_started
     * @return GridController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('No user data to retrieve.');
                $this->is_missing_param = true;
                $this->setViewTemplate('inline.view.tpl');
            }
            // or replies?
            if ($this->is_missing_param) {
                if (isset($_GET['t'])) {
                    $this->is_missing_param = false;
                }
            }
        }
        if (!isset($_GET['d'])) {
            $_GET['d'] = "tweets-all";
        }
    }

    /**
     * Outputs JavaScript callback string with json array/list of post as an argument
     */
    public function authControl($owner = false) {
        $public_search = false;
        if ($owner) {
            $public_search = true;
        }
        $private_reply_search = false;
        $this->setContentType('text/javascript');
        if (!$this->is_missing_param) {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            if ( $instance_dao->isUserConfigured($_GET['u'], $_GET['n'])) {
                $username = $_GET['u'];
                $ownerinstance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                if (!$owner) {
                    $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                }
                $instance = $instance_dao->getByUsername($username, $_GET['n']);
                if (!$ownerinstance_dao->doesOwnerHaveAccessToInstance($owner, $instance)) {
                    echo '{"status":"failed","message":"Insufficient privileges."}';
                } else {
                    echo "tu_grid_search.populate_grid(";
                    $posts_it;
                    if (isset($_GET['t'])) {
                        // replies?
                        $post_dao = DAOFactory::getDAO('PostDAO');
                        $posts_it = $post_dao->getRepliesToPostIterator($_GET['t'],$_GET['n'], 'default','km',
                        $public_search);
                        if (!$public_search) {
                            $private_reply_search = true;
                        }
                    } else {
                        if (isset($_GET['nolimit']) && $_GET['nolimit'] == 'true') {
                            self::$MAX_ROWS = 0;
                        }
                        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
                        $webapp_plugin_registrar->setActivePlugin($instance->network);
                        $tab = $webapp_plugin_registrar->getDashboardMenuItem($_GET['d'], $instance);
                        $posts_it = $tab->datasets[0]->retrieveIterator();
                    }
                    echo '{"status":"success","limit":' . self::$MAX_ROWS . ',"posts": [' . "\n";
                    $cnt = 0;
                    // lets make sure we have a post iterator, and not just a list of posts
                    if ( get_class($posts_it) != 'PostIterator' ) {
                        throw Exception("Grid Search should use a PostIterator to conserve memory");
                    }
                    foreach($posts_it as $key => $value) {
                        if ($private_reply_search) {
                            if (!$ownerinstance_dao->doesOwnerHaveAccessToPost($owner, $value)) {
                                continue;
                            }
                        }
                        $cnt++;
                        $data = array('id' => $cnt, 'text' => $value->post_text,
                        'post_id_str' => $value->post_id . '_str', 'author' => $value->author_username,
                        'date' => $value->adj_pub_date, 'network' => $value->network);
                        echo json_encode($data) . ",\n";
                        flush();
                    }
                    $data = array('id' => -1, 'text' => 'Last Post',
                        'author' => 'nobody');
                    echo json_encode($data);
                    echo ']});';
                }
            } else {
                echo '{"status":"failed","message":"' . $_GET['u'] . 'is not configured."}';
            }
        } else {
            echo '{"status":"failed","message":"Missing Parameters"}';
        }
    }

    /**
     * return max rows
     * @return int $MAX_ROWS
     */
    public static function getMaxRows() {
        return self::$MAX_ROWS;
    }

    /**
     * skip auth if we are a reply search
     * @return boolean $authed Returns true if the auth criteria has been met.
     */
    protected function preAuthControl() {
        $response = false; // default to false, no auth, unless we are a reply search
        if (isset($_GET['t']) && isset($_GET['n']) && $_GET['u']) {
            // We will allow public search on post replies
            // So, we need to make sure this is a public instance
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance = $instance_dao->getByUsername($_GET['u'], $_GET['n']);
            if ($instance->is_public != 1) {
                $response = false; // ie: authed failed
            } else {
                // we need to fetch the owner since we are not logged in...
                // and we'll pass to the grid controller
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner_instance = $owner_instance_dao->getByInstance($instance->id);
                $owner = $owner_dao->getById($owner_instance[0]->owner_id);
                $this->authControl($owner);
                $response = true;
            }
        }
        return $response;
    }
}