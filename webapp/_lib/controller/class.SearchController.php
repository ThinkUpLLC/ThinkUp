<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.SearchController.php
 *
 * Copyright (c) 2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
            if (isset($_GET['q']) && isset($_GET['n']) && isset($_GET['u'])) {
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                $instance = $instance_dao->getByUsernameOnNetwork(stripslashes($_GET["u"]), $_GET['n']);
                if (isset($instance) && $_GET['q'] != '') {
                    if ($owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance)) {
                        $page_number = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
                        $keywords = explode(' ', $_GET['q']);

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
                } else {
                    if (!isset($instance)) {
                        $this->addErrorMessage("Whoops! That user doesn't exist. Please try again.");
                    }
                    if ($_GET['q'] == '') {
                        $this->addErrorMessage("Uh-oh. Your search term is missing. Please try again.");
                    }
                }
                //Populate search dropdown with service users
                $this->addToView('instances', $instance_dao->getByOwner($owner));
            } else {
                $this->addErrorMessage("Uh-oh. ");
            }
            return $this->generateView();
        }
    }
}