<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.CrawlerAuthController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * CrawlerAuth Controller
 *
 * Runs crawler from the command line given valid command line credentials.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class CrawlerAuthController extends ThinkUpController {

    /**
     *
     * @var int The number of arguments passed to the crawler
     */
    var $argc;

    /**
     *
     * @var array The array of arguments passed to the crawler
     */
    var $argv;
    /**
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($argc, $argv) {
        parent::__construct(true);
        $this->argc = $argc;
        $this->argv = $argv;
    }

    public function control() {
        $output = "";
        $authorized = false;

        if (isset($this->argc) && $this->argc > 1) { // check for CLI credentials
            $session = new Session();
            $username = $this->argv[1];
            if ($this->argc > 2) {
                $pw = $this->argv[2];
            } else {
                $pw = getenv('THINKUP_PASSWORD');
            }

            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($username);
            $passcheck = $owner_dao->getPass($username);
            if ($session->pwdCheck($pw, $passcheck)) {
                $authorized = true;
                Session::completeLogin($owner);
            } else {
                $output = "ERROR: Incorrect username and password.";
            }
        } else { // check user is logged in on the web
            if ( $this->isLoggedIn() ) {
                $authorized = true;
            } else {
                $output = "ERROR: Invalid or missing username and password.";
            }
        }

        if ($authorized) {
            $crawler = Crawler::getInstance();
            $crawler->crawl();
        }

        return $output;
    }
}