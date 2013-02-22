<?php
/**
 * ThinkUp/webapp/_lib/controller/class.StreamerAuthController.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * StreamerAuth Controller
 *
 * Runs crawler from the command line given valid command line credentials.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 */
class StreamerAuthController extends ThinkUpController {
    /**
     *
     * @var int The number of arguments passed to the streamer
     */
    var $argc;
    /**
     *
     * @var array The array of arguments passed to the streamer
     */
    var $argv;

    public function __construct($argc, $argv) {
        parent::__construct(true);
        $this->argc = $argc;
        $this->argv = $argv;
    }

    /**
     * @return string
     */
    public function control() {
        $output = "";
        $authorized = false;

        if (isset($this->argc) && $this->argc > 2) { // check for CLI credentials
            $session = new Session();
            $streamer_method = $this->argv[1];
            $username = $this->argv[2];
            if ($this->argc > 3) {
                $pw = $this->argv[3];
            } else {
                $pw = getenv('THINKUP_PASSWORD');
            }

            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($username);
            if ($owner_dao->isOwnerAuthorized($username, $pw)) {
                $authorized = true;
                Session::completeLogin($owner);
            } else {
                $output = "ERROR: Incorrect username and password.";
            }
        }

        if ($authorized) {
            $streamer = Streamer::getInstance();
            // print "have streamer method: $streamer_method\n";
            switch($streamer_method) {
                case 'stream':
                    $streamer->stream();
                    break;
                case 'streamProcess':
                    $streamer->streamProcess();
                    break;
                case 'shutdownStreams':
                    $streamer->shutdownStreams();
                    break;
                default:
                    $output = "Error: could not identify stream method to run.";
            }
        }

        return $output;
    }
}