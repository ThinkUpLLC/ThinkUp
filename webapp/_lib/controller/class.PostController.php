<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.PostController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * Post Controller
 *
 * Displays a post and its replies, retweets, reach, and location information.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class PostController extends ThinkUpController {
    /**
     * View name
     * @var str
     */
    var $view_name;

    public function control() {
        $this->view_name = (isset($_GET['v']))?$_GET['v']:'default';
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->setPageTitle('Post Details');
        $this->setViewTemplate('post.index.tpl');

        $network = (isset($_GET['n']) )?$_GET['n']:'twitter';
        if ($this->shouldRefreshCache()) {
            if ( isset($_GET['t']) ) {
                $post_id = $_GET['t'];
                $post = $post_dao->getPost($post_id, $network);
                if ( isset($post) ){
                    $config = Config::getInstance();
                    $this->addToView('disable_embed_code', ($config->getValue('is_embed_disabled') ||
                    $post->is_protected ));
                    if (isset($_GET['search'])) {
                        $this->addToView('search_on', true);
                    }

                    $viewer_has_access_to_post = false;
                    if ( !$post->is_protected ) { // post is public
                        if ($this->isLoggedIn()) { // user is logged in
                            $viewer_has_access_to_post = true;
                        } else { //not logged in
                            $instance_dao = DAOFactory::getDAO('InstanceDAO');
                            $viewer_has_access_to_post = $instance_dao->isInstancePublic($post->author_username,
                            $post->network);
                        }
                    } elseif ($this->isLoggedIn()) {
                        $owner_dao = DAOFactory::getDAO('OwnerDAO');
                        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                        $viewer_has_access_to_post = $owner_instance_dao->doesOwnerHaveAccessToPost($owner, $post);
                    }

                    if ($viewer_has_access_to_post) {
                        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
                        $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
                        if (isset($options['distance_unit']->option_value)) {
                            $distance_unit = $options['distance_unit']->option_value;
                        } else {
                            $distance_unit = 'km';
                        }
                        $this->addToView('post', $post);
                        $this->addToView('unit', $distance_unit);

                        $replies = $post_dao->getRepliesToPost($post_id, $network, 'default', $distance_unit);

                        $public_replies = array();
                        $viewable_replies = array();
                        foreach ($replies as $reply) {
                            if (!$reply->is_protected) {
                                $public_replies[] = $reply;
                                $viewable_replies[] = $reply;
                            } else {
                                if ($this->isLoggedIn()) {
                                    if (!isset($owner_dao)) {
                                        $owner_dao = DAOFactory::getDAO('OwnerDAO');
                                        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                                        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                                    }
                                    if ( $owner_instance_dao->doesOwnerHaveAccessToPost($owner, $reply)) {
                                        $viewable_replies[] = $reply;
                                    }
                                }
                            }
                        }
                        $public_replies_count = count($public_replies);
                        $this->addToView('public_reply_count', $public_replies_count );

                        if ($this->isLoggedIn()) {
                            $this->addToView('replies', $viewable_replies );
                        } else {
                            $this->addToView('replies', $public_replies );
                        }
                        $all_replies_count = count($replies);
                        $private_reply_count = $all_replies_count - $public_replies_count;
                        $this->addToView('private_reply_count', $private_reply_count );

                        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
                        $sidebar_menu = $webapp_plugin_registrar->getPostDetailMenu($post);
                        $this->addToView('sidebar_menu', $sidebar_menu);
                        $this->loadView($post);
                    } else {
                        $this->addErrorMessage('Insufficient privileges');
                    }
                } else {
                    $this->addErrorMessage('Post not found');
                }
            } else {
                $this->addErrorMessage('Post not specified');
            }
        }
        return $this->generateView();
    }

    /**
     * Load the view with required variables
     */
    private function loadView($post) {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        if ($this->view_name != 'default') {
            $menu_item = $webapp_plugin_registrar->getPostDetailMenuItem($this->view_name, $post);
            if ($menu_item != null ) {
                $this->addToView('data_template', $menu_item->view_template);
                $this->addToView('display', $this->view_name);
                $this->addToView('header', $menu_item->name);
                $this->addToView('description', $menu_item->description);

                $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
                foreach ($menu_item->datasets as $dataset) {
                    if (array_search('#page_number#', $dataset->method_params) !== false) { //there's paging
                        $this->addToView('next_page', $page+1);
                        $this->addToView('last_page', $page-1);
                    }
                    $this->addToView($dataset->name, $dataset->retrieveDataset($page));
                    if (Session::isLoggedIn() && $dataset->isSearchable()) {
                        $view_name = 'is_searchable';
                        $this->addToView($view_name, true);
                    }
                }
            }
        }
    }
}