<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.InsightStreamController.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
 * Insights stream controller
 *
 * Displays a list of insights for authenticated service users.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2016 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InsightStreamController extends ThinkUpController {
    /**
     * Number of insights to display on a page
     * @var int
     */
    const PAGE_INSIGHTS_COUNT = 20;
    /**
     * Template name
     * @var string
     */
    var $tpl_name = 'insights.tpl';

    public function control() {
        $config = Config::getInstance();
        $this->setViewTemplate($this->tpl_name);
        $this->addToView('enable_bootstrap', true);
        $this->addToView('developer_log', $config->getValue('is_log_verbose'));
        $this->addToView('thinkup_application_url', Utils::getApplicationURL());

        if ($this->shouldRefreshCache() ) {
            if (isset($_GET['u']) && isset($_GET['n']) && isset($_GET['d']) && isset($_GET['s'])) {
                $this->displayIndividualInsight();
                if (isset($_GET['share'])) {
                    $this->addToView('share_mode', true);
                }
            } else {
                if (!$this->displayPageOfInsights()) {
                    $controller = new LoginController(true);
                    return $controller->go();
                }
            }
            if ($this->isLoggedIn()) {
                //Populate search dropdown with service users and add thinkup_api_key for desktop notifications.
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                $this->addToView('thinkup_api_key', $owner->api_key);
                $this->addHeaderJavaScript('assets/js/notify-insights.js');

                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $instances = $instance_dao->getByOwnerWithStatus($owner);
                $this->addToView('instances', $instances);
                $saved_searches = array();
                if (sizeof($instances) > 0) {
                    $instancehashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
                    $saved_searches = $instancehashtag_dao->getHashtagsByInstances($instances);
                }
                $this->addToView('saved_searches', $saved_searches);

                //Start off assuming connection doesn't exist
                $connection_status = array('facebook'=>'inactive', 'twitter'=>'inactive', 'instagram'=>'inactive');
                foreach ($instances as $instance) {
                    if ($instance->auth_error != '') {
                        $connection_status[$instance->network] = 'error';
                    } else { //connection exists, so it's active
                        $connection_status[$instance->network] = 'active';
                    }
                }
                $this->addToView('facebook_connection_status', $connection_status['facebook']);
                $this->addToView('twitter_connection_status', $connection_status['twitter']);
                $this->addToView('instagram_connection_status', $connection_status['instagram']);
            }
        }

        $this->addToView('tpl_path', THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/view/');
        if ($config->getValue('image_proxy_enabled') == true) {
            $this->addToView('image_proxy_sig', $config->getValue('image_proxy_sig'));
        }
        return $this->generateView();
    }

    /**
     * Load view with data to display individual insight.
     */
    private function displayIndividualInsight() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');

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
                    $redirect_url = urlencode(Utils::getApplicationURL(). Utils::getApplicationRequestURI());
                    $this->addToView('redirect_url', $redirect_url);
                    $this->addErrorMessage("<a href='session/login.php?redirect=".$redirect_url.
                        '\'>Log in</a> to see this insight.', null, true);
                }
            } else  {
                $redirect_url = urlencode(Utils::getApplicationURL(). Utils::getApplicationRequestURI());
                $this->addToView('redirect_url', $redirect_url);
                $this->addErrorMessage("<a href='session/login.php?redirect=".$redirect_url.
                    '\'>Log in</a> to see this insight.', null, true);
            }
        } else {
            $this->addErrorMessage(stripslashes($_GET["u"])." on ".ucfirst($_GET['n']) ." is not in ThinkUp.");
        }
        if ( $should_display_insight) {
            $insights = array();
            $insight = $insight_dao->getInsightByUsername($_GET['u'], $_GET['n'], $_GET['s'], $_GET['d']);
            if (isset($insight)) {
                $insights[] = $insight;
                $this->addToView('insights', $insights);
                $this->addToView('expand', true);
                $thinkupllc_endpoint = Config::getInstance()->getValue('thinkupllc_endpoint');
                if (isset($thinkupllc_endpoint)) {
                    $insight_params = array(
                        // ThinkUp LLC-specific username
                        'tu'=>Config::getInstance()->getValue('install_folder'),
                        'u'=>$insight->instance->network_username,
                        'n'=>$insight->instance->network,
                        'd'=>date('Y-m-d', strtotime($insight->date)),
                        's'=>$insight->slug,
                        'share'=>1
                        );
                    $insight_image = "https://shares.thinkup.com/insight?".(http_build_query($insight_params));
                    $twitter_card = "summary_large_image";
                } else {
                    $insight_image = "https://www.thinkup.com/join/assets/ico/apple-touch-icon-144-precomposed.png";
                    $twitter_card = "summary";
                }
                $this->addToView('install_folder', Config::getInstance()->getValue('install_folder'));
                $this->addToView('insight_image', $insight_image);
                $this->addToView('twitter_card', $twitter_card);
            } else {
                $this->addErrorMessage("This insight doesn't exist.");
            }
        }
    }

    /**
     * Load view with data to display page of insights.
     */
    protected function displayPageOfInsights() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');

        $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
        if (Session::isLoggedIn()) {
            if ($this->isAdmin()) {
                ///show all insights for all service users
                $insights = $insight_dao->getAllInstanceInsights($page_count=(self::PAGE_INSIGHTS_COUNT+1),
                $page);
            } else {
                //show only service users owner owns
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());

                $insights = $insight_dao->getAllOwnerInstanceInsights($owner->id,
                $page_count=(self::PAGE_INSIGHTS_COUNT+1), $page);
            }
        } else {
            //show just public service users in stream
            $insights = $insight_dao->getPublicInsights($page_count=(self::PAGE_INSIGHTS_COUNT+1), $page);
        }
        if (isset($insights) && sizeof($insights) > 0) {
            if (sizeof($insights) == (self::PAGE_INSIGHTS_COUNT+1)) {
                $this->addToView('next_page', $page+1);
                array_pop($insights);
            }
            if ($page != 1) {
                $this->addToView('last_page', $page-1);
            }
            $this->addToView('install_folder', Config::getInstance()->getValue('install_folder'));
            $this->addToView('insights', $insights);
        } else {
            if ($this->isLoggedIn()) {
                //if owner has no instances, show welcome message
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                if (!isset($owner)) {
                    $owner_dao = DAOFactory::getDAO('OwnerDAO');
                    $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                }
                $owned_instances = $instance_dao->getByOwner($owner, $force_not_admin = false, $only_active=true);
                $config = Config::getInstance();
                $site_root_path = $config->getValue('site_root_path');
                $plugin_link = '<a href="'.$site_root_path.'account/?p=';
                if (sizeof($owned_instances) > 0) {
                    $this->addToView('message_header', "ThinkUp doesn't have any insights for you yet.");
                    if (!Utils::isThinkUpLLC()) {
                        $this->addToView('message_body', "Check back later, ".
                        "or <a href=\"".$site_root_path."crawler/updatenow.php\">update your ThinkUp data now</a>.");
                    }
                } else {
                    $this->addToView('message_header', "Welcome to ThinkUp. Let's get started.");

                    $thinkupllc_endpoint = $config->getValue('thinkupllc_endpoint');
                    if (isset($thinkupllc_endpoint)) {
                        $this->addToView('message_body', "Set up a ".$plugin_link."twitter\">Twitter</a> or ".
                        "".$plugin_link."facebook\">Facebook</a> account.");
                    } else {
                        $this->addToView('message_body', "Set up a ".$plugin_link."twitter\">Twitter</a>, ".
                        "".$plugin_link."facebook\">Facebook</a>, or "
                        .$plugin_link."instagram\">Instagram</a> account.");
                    }
                }
            } else { //redirect to login
                return false;
            }
        }
        return true;
    }
}
