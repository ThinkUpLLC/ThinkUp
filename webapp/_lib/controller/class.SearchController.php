<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.SearchController.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Grid Export Controller
 * Exports Grid posts from an instance user on ThinkUp.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class SearchController extends ThinkUpAuthController {
    /**
     * Number of search results to display on a page
     * @var int
     */
    const PAGE_RESULTS_COUNT = 20;

    public function authControl() {
        $this->setViewTemplate('search.tpl');
        $this->addToView('enable_bootstrap', true);
        $this->addToView('tpl_path', THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/view/');

        if ($this->shouldRefreshCache() ) {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            $owner = $owner_dao->getByEmail($this->getLoggedInUser());
            if (isset($_GET['q']) && isset($_GET['n']) && isset($_GET['u']) && isset($_GET['c'])) {
                $instance = $instance_dao->getByUsernameOnNetwork(stripslashes($_GET["u"]), $_GET['n']);
                if (isset($instance) && $_GET['q'] != '') {
                    if ($owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance)) {
                        switch ($_GET["c"]) {
                            case "posts":
                                self::searchPosts();
                                break;
                            case "followers":
                                self::searchFollowers($instance->network_user_id);
                                break;
                            case "searches":
                                self::searchSearches();
                                break;
                            default:
                                self::searchPosts();
                        }
                    } else {
                        $this->addErrorMessage("Whoops! You don't have access to that user. Please try again.");
                    }
                } else {
                    if (!isset($instance)) {
                        $this->addErrorMessage("Whoops! That user doesn't exist. Please try again.");
                    }
                    if ($_GET['q'] == '') {
                        $this->addErrorMessage("Uh-oh. Your search term is missing. Please try again.");
                    }
                }
            } else {
                $this->addErrorMessage("Uh-oh. Your search terms are missing. Please try again.");
            }
            //Populate search dropdown with service users
            $instances = $instance_dao->getByOwner($owner);
            $this->addToView('instances', $instances);
            $saved_searches = array();
            if (sizeof($instances) > 0) {
                $instancehashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
                $saved_searches = $instancehashtag_dao->getHashtagsByInstances($instances);
            }
            $this->addToView('saved_searches', $saved_searches);
        }
        return $this->generateView();
    }
    /**
     * Populate view with post search results
     */
    private function searchPosts() {
        $page_number = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
        $keywords = explode(' ', $_GET['q']);
        $this->addToView('current_page', $page_number);

        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->searchPostsByUser($keywords, $_GET['n'], $_GET['u'], $page_number,
        $page_count=(self::PAGE_RESULTS_COUNT+1));

        if (isset($posts) && sizeof($posts) > 0) {
            if (sizeof($posts) == (self::PAGE_RESULTS_COUNT+1)) {
                $this->addToView('next_page', $page_number+1);
                $this->addToView('last_page', $page_number-1);
                array_pop($posts);
            }
            $this->addToView('posts', $posts);
        }
    }
    /**
     * Populate view with follower search results.
     * @param str $user_id
     */
    private function searchFollowers($user_id) {
        $page_number = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
        $keywords = explode(' ', $_GET['q']);

        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $users = $follow_dao->searchFollowers($keywords, $_GET['n'], $user_id, $page_number,
        $page_count=(self::PAGE_RESULTS_COUNT+1));

        if (isset($users) && sizeof($users) > 0) {
            if (sizeof($posts) == (self::PAGE_RESULTS_COUNT+1)) {
                $this->addToView('next_page', $page_number+1);
                $this->addToView('last_page', $page_number-1);
                array_pop($users);
            }
            $this->addToView('users', $users);
        }
    }
    /**
     * Populate view with post search results from search hashtags or keywords.
     */
    private function searchSearches() {
        $page_number = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
        $this->addToView('current_page', $page_number);

        $keywords = explode(' ', $_GET['q']);
        if (isset($_GET['k'])) {
            $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
            $hashtag = $hashtag_dao->getHashtag($_GET['k'], $_GET['n']);
            if (isset($hashtag)) {
                $post_dao = DAOFactory::getDAO('PostDAO');
                $posts = $post_dao->searchPostsByHashtag($keywords, $hashtag, $_GET['n'], $page_number,
                $page_count=(self::PAGE_RESULTS_COUNT+1));
                if (isset($posts) && sizeof($posts) > 0) {
                    if (sizeof($posts) == (self::PAGE_RESULTS_COUNT+1)) {
                        $this->addToView('next_page', $page_number+1);
                        $this->addToView('last_page', $page_number-1);
                        array_pop($posts);
                    }
                    $this->addToView('posts', $posts);
                }
            } else {
                $this->addErrorMessage("Uh-oh. ".$_GET['k']." is not a saved search. Please try again.");
            }
        }
    }
}