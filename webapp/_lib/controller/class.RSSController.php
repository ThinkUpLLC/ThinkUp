<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.RSSController.php
 *
 * Copyright (c) 2009-2013 Guillaume Boudreau
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
 * RSS Controller
 *
 * Launch the crawler, if the last updated date is older than X minutes, then return a valid RSS feed.
 * This will allow users to crawl their ThinkUp instances by subscribing to their ThinkUp RSS feed in any RSS reader.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 */
class RSSController extends ThinkUpAuthAPIController {

    /**
     * Launch the crawler, if the latest crawler_last_run date is older than X minutes, then return a valid RSS feed.
     * @return string rendered view markup
     */
    public function authControl() {
        $this->setContentType('application/rss+xml; charset=UTF-8');
        $this->setViewTemplate('rss.tpl');

        $config = Config::getInstance();
        $rss_crawler_refresh_rate = $config->getValue('rss_crawler_refresh_rate');
        if (empty($rss_crawler_refresh_rate)) {
            $rss_crawler_refresh_rate = 20; // minutes
        }

        $base_url = Utils::getApplicationURL();

        $crawler_plugin_registrar_launched = false;
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $email = $this->getLoggedInUser();
        $owner = parent::getOwner($email);
        $freshest_instance = $instance_dao->getFreshestByOwnerId($owner->id);
        if ($freshest_instance) {
            $crawler_plugin_registrar_last_run = strtotime($freshest_instance->crawler_last_run);
        }
        if ($freshest_instance && $crawler_plugin_registrar_last_run < time() - $rss_crawler_refresh_rate*60) {
            $crawler_plugin_registrar_run_url = $base_url.'crawler/run.php?'.sprintf('un=%s&as=%s',
            $email, $owner->api_key);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $crawler_plugin_registrar_run_url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // seconds
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // seconds
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $body = substr($result, strpos($result, "\r\n\r\n")+4);
            if (strpos($result, 'Content-Type: application/json') && function_exists('json_decode')) {
                $json = json_decode($body);
                if (isset($json->error)) {
                    $crawler_plugin_registrar_launched = false;
                } else if (isset($json->result) && $json->result == 'success') {
                    $crawler_plugin_registrar_launched = true;
                }
            } else if (strpos($body, 'Error starting crawler') !== FALSE) {
                $crawler_plugin_registrar_launched = false;
            } else {
                $crawler_plugin_registrar_launched = true;
            }
        }

        $items = array();
        $logger = Logger::getInstance();
        // Don't return an item if there is a crawler log defined;
        // it would just duplicate the information available in that file.
        if ($crawler_plugin_registrar_launched && !isset($logger->log)) {
            $title = 'ThinkUp crawl started on ' . date('Y-m-d H:i:s');
            $link = $base_url.'rss.php?d='.urlencode(date('Y-m-d H:i:s'));
            $description = "Last ThinkUp crawl ended on $freshest_instance->crawler_last_run<br />A new crawl ".
            "was started just now, since it's been more than $rss_crawler_refresh_rate minutes since the last run.";
            $items[] = self::createRSSItem($title, $link, $description);
        }
        $items = array_merge($items, $this->getAdditionalItems($base_url));

        //Add insights to RSS feed
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        if ($this->isAdmin()) {
            ///show all insights for all service users
            $insights = $insight_dao->getAllInstanceInsights($page_count=(InsightStreamController::PAGE_INSIGHTS_COUNT
            +1), 1);
        } else {
            //show only service users owner owns
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($this->getLoggedInUser());

            $insights = $insight_dao->getAllOwnerInstanceInsights($owner->id,
            $page_count=(InsightStreamController::PAGE_INSIGHTS_COUNT+1), 1);
        }
        if (sizeof($insights) == 0) {
            $title = 'No insights exist on ' . date('Y-m-d H:i:s');
            $link = $base_url.'rss.php?d='.urlencode(date('Y-m-d H:i:s'));
            $description = "ThinkUp doesn't have any insights to show you. Check your crawler log to make sure ".
            "ThinkUp is capturing data.";
            $items[] = self::createRSSItem($title, $link, $description);
        } else {
            foreach ($insights as $insight) {
                $username_in_title = (($insight->instance->network == 'twitter')?'@':'') .
                $insight->instance->network_username;
                $title = str_replace(':', '', $insight->headline). " (".$username_in_title .")";
                $link = $base_url.'?u='.$insight->instance->network_username.'&n='
                .urlencode($insight->instance->network). '&d='.urlencode(date('Y-m-d', strtotime($insight->date))).
                '&s='.urlencode($insight->slug);
                $description = $insight->headline." ". $insight->text. '<br><a href="'.$link.'">Link</a>';
                $time = strtotime($insight->date);
                $items[] = self::createRSSItem($title, $link, $description, $time);
            }
        }

        $this->addToView('items', $items);
        $this->addToView('logged_in_user', htmlspecialchars($this->getLoggedInUser()));
        $this->addToView('rss_crawler_refresh_rate', htmlspecialchars($rss_crawler_refresh_rate));

        return $this->generateView();
    }

    /**
     * Add extra RSS items to the feed, if necessary.
     * @param string $base_url The base URL to use in items' links.
     * @return array RSS items to add into the RSS feed
     */
    private function getAdditionalItems($base_url) {
        $items = array();
        // Make sure the crawler log, if specified, is writable; add an item if not
        $config = Config::getInstance();
        $log_location = $config->getValue('log_location');
        if ($log_location !== FALSE && !is_writable($log_location) &&
        (file_exists($log_location) || !is_writable(dirname($log_location)))) {
            $title = 'Error: crawler log is not writable';
            $link = $base_url.'rss.php?e=1&d='.urlencode(date('Y-m-d H:i:s'));
            $description = "The crawler log specified as <em>log_location</em> in config.inc.php<br/>".
            "&nbsp;&nbsp;&nbsp;&nbsp;<strong>$log_location</strong><br />".
            "is not writable by the user running your web server.<br />".
            "That means that all crawls launched by this RSS feed will not log anything in that file.<br />".
            "You should chown or chmod that file to insure it's being written to during those crawls.";
            $items[] = self::createRSSItem($title, $link, $description);
        }
        return $items;
    }

    /**
     * Build an RSS item from a title, link and description.
     * @param string $title
     * @param string $link
     * @param string $description
     * @param int $time Use current time if not specified
     * @return array RSS item
     */
    private static function createRSSItem($title, $link, $description, $time=null) {
        return array(
            'title'       => htmlspecialchars($title),
            'link'        => htmlspecialchars($link),
            'description' => htmlspecialchars($description),
            'pubDate'     => htmlspecialchars( (isset($time))?date('D, d M Y H:i:s T', $time):date('D, d M Y H:i:s T')),
            'guid'        => htmlspecialchars($link)
        );
    }
}
