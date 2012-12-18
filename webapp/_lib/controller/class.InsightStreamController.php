<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.InsightStreamController.php
 *
 * Copyright (c) 2012 Gina Trapani
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
 * Insights stream controller
 *
 * Displays a list of insights for authenticated service users.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InsightStreamController extends ThinkUpController {
    public function control() {
        $config = Config::getInstance();
        $this->setViewTemplate('insights.tpl');
        $this->addToView('enable_bootstrap', true);

        if ($this->shouldRefreshCache() ) {
            $insight_dao = DAOFactory::getDAO('InsightDAO');
            //display individual insight
            if (isset($_GET['u']) && isset($_GET['n']) && isset($_GET['d']) && isset($_GET['s'])) {
                //get instance and check if it's public or that owner has access to it
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $instance = $instance_dao->getByUsernameOnNetwork(stripslashes($_GET["u"]), $_GET['n']);

                $should_display_insight = false;

                if (isset($instance)) {
                    if ($instance->is_public) {
                        $should_display_insight = true;
                    } else if ($this->isLoggedIn()) {
                        $owner_dao = DAOFactory::getDAO('OwnerDAO');
                        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                        if ($owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance)) {
                            $should_display_insight = true;
                        } else {
                            $this->addErrorMessage("You don't have rights to view this service user.");
                        }
                    } else  {
                        $this->addErrorMessage("You don't have rights to view this service user.");
                    }
                } else {
                    $this->addErrorMessage(stripslashes($_GET["u"])." on ".ucfirst($_GET['n']) .
                    " is not in ThinkUp.");
                }
                if ( $should_display_insight) {
                    $insights = array();
                    $insight = $insight_dao->getInsightByUsername($_GET['u'], $_GET['n'], $_GET['s'], $_GET['d']);
                    if (isset($insight)) {
                        $insights[] = $insight;
                        $insights = $this->eschewSecondPerson($insights);
                        $this->addToView('insights', $insights);
                    } else {
                        $this->addErrorMessage("This insight doesn't exist.");
                    }
                }
            } else { //display page of insights
                $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
                if (Session::isLoggedIn()) {
                    if ($this->isAdmin()) {
                        ///show all insights for all service users
                        $insights = $insight_dao->getAllInstanceInsights($page_count=50, $page);
                        $insights = $this->eschewSecondPerson($insights);
                        $this->addToView('insights', $insights);
                    } else {
                        //show only service users owner owns
                        $owner_dao = DAOFactory::getDAO('OwnerDAO');
                        $owner = $owner_dao->getByEmail($this->getLoggedInUser());

                        $insights = $insight_dao->getAllOwnerInstanceInsights($owner->id, $page_count=50, $page);
                        $insights = $this->eschewSecondPerson($insights);
                        $this->addToView('insights', $insights);
                    }
                } else {
                    //show just public service users in stream
                    $insights = $insight_dao->getPublicInsights($page_count=50, $page);
                    $insights = $this->eschewSecondPerson($insights);
                    $this->addToView('insights', $insights);
                }
                if (isset($insights) && sizeof($insights) > 0) {
                    $this->addToView('next_page', $page+1);
                    $this->addToView('last_page', $page-1);
                } else {
                    if ($this->isLoggedIn()) {
                        //if owner has no instances, show welcome message
                        $instance_dao = DAOFactory::getDAO('InstanceDAO');
                        $owned_instances = $instance_dao->getByOwner($this->getLoggedInUser(), $force_not_admin = false,
                        $only_active=true);
                        $site_root_path = Config::getInstance()->getValue('site_root_path');
                        if (sizeof($owned_instances) > 0) {
                            $this->addToView('message_header', "ThinkUp doesn't have any insights for you yet.");
                            $this->addToView('message_body', "Check back later, ".
                            "or <a href=\"".$site_root_path."crawler/updatenow.php\">update your ThinkUp data now</a>.");
                        } else {
                            $plugin_link = '<a href="'.$site_root_path.'account/?p=';
                            $this->addToView('message_header', "Welcome to ThinkUp. Let's get started.");
                            $this->addToView('message_body', "Set up a ".$plugin_link."twitter\">Twitter</a>, ".
                            "".$plugin_link."facebook\">Facebook</a>, ".$plugin_link.
                            "googleplus\">Google+</a>, or ".$plugin_link."foursquare\">Foursquare</a> account.");
                        }
                    } else { //redirect to login
                        $controller = new LoginController();
                        return $controller->go();
                    }
                }
            }
        }
        $this->addToView('tpl_path', THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/view/');
        return $this->generateView();
    }

    private function eschewSecondPerson($insights) {
        foreach ($insights as $insight) {
            $username = $insight->instance->network_username;
            if ($insight->instance->network == 'twitter') {
                $username = '@'.$username;
            }
            //your/Your
            $new_text = str_replace('your', $username."'s", $insight->text);
            $new_text = str_replace('Your', $username."'s", $new_text);
            //you/You
            $new_text = str_replace('you', $username, $new_text);
            $new_text = str_replace('You', $username, $new_text);
            $insight->text = $new_text;
        }
        return $insights;
    }

}