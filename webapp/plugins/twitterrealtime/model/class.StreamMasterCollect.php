<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.StreamMasterCollect.php
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
 * Stream Master Collect
 * Initiates pulling in Twitter UserStream data from the command line, for asynchronous processing,
 * given valid command line credentials.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright Amy Unruh
 * @author Amy Unruh
 */

class StreamMasterCollect {
    /**
     * @var string
     */
    protected $streaming_dir;
    /**
     * @var string
     */
    protected $log_dir;
    /**
     * @var string
     */
    protected $php_path;
    /**
     * @var StreamProcDao
     */
    protected $stream_proc_dao;
    /**
     * @var InstanceDAO
     */
    protected $instance_dao;
    /**
     * @var OwnerDAO
     */
    protected $owner_dao;
    /**
     * @const int
     */
    const MAX_INSTANCES = 5; // max # of instances for which we will try to open twitter streams
    /**
    * @const int
    */
    const GAP_TIME = 600; // elapsed time in seconds since 'last report' before we conclude that a process is dead.
    /**
    * Constructor
    * @return StreamMasterCollect
    */
    public function __construct() {
        $config = Config::getInstance();
        $this->streaming_dir = $config->getValue('source_root_path') . 'webapp/plugins/twitterrealtime/streaming';
        // @TODO -- get this from plugin information now
        $this->log_dir = $config->getValue('source_root_path') . 'logs';

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('twitterrealtime', true);
        if (isset($options['php_path'])) {
            $this->php_path = $options['php_path']->option_value;
        } else {
            $this->php_path = null; // path to the php interp on the user's system
        }
        $this->stream_proc_dao = DAOFactory::getDAO('StreamProcDAO');
        $this->instance_dao = DAOFactory::getDAO('InstanceDAO');
        $this->owner_dao = DAOFactory::getDAO('OwnerDAO');

    }

    /**
     * @return void
     */
    public function shutdownStreams() {
        $logger = Logger::getInstance('stream_log_location');
        $logger->logInfo("killing all running streaming processes", __METHOD__.','.__LINE__);
        $this->killAllStreamingPIDs();
    }

    /**
     * @return
     */
    public function launchStreams() {
        $logger = Logger::getInstance('stream_log_location');

        if (!$this->php_path) {
            $logger->logError("php path is not set: check Twitter Realtime plugin configuration",
            __METHOD__.','.__LINE__);
            return;
        }

        // get information from database about all streams.  This data is indexed by email + instance id.
        $stream_hash = $this->stream_proc_dao->getAllStreamProcesses();

        // get all owners
        $owners = $this->owner_dao->getAllOwners();
        $count = 0;

        // exec the stream processing script for each owner. This will fire up the
        // stream consumption if the owner has a twitter instance.
        foreach ($owners as $owner) {
            if ($count == self::MAX_INSTANCES) {
                break; // only open user stream process for up to MAX_INSTANCES instances
            }
            // the last argument in the following causes only active instances to be retrieved.
            $instances = $this->instance_dao->getByOwnerAndNetwork($owner, 'twitter', true, true);
            foreach ($instances as $instance) {
                $owner_email = $owner->email;
                if (isset($owner_email)) {
                    $idx = $owner_email . "_" . $instance->id;
                    $start_new_proc = false;
                    // if a 'live' process for that user is already running, take no action
                    if (isset($stream_hash[$idx])  && ($stream_hash[$idx]['email'] == $owner_email) &&
                    $stream_hash[$idx]['instance_id'] == $instance->id) {
                        if (strtotime($stream_hash[$idx]['last_report']) < (time() - self::GAP_TIME)) {
                            $logger->logInfo("killing process " . $stream_hash[$idx]['process_id'] .
                            " -- it has not updated recently", __METHOD__.','.__LINE__);
                            $this->psKill($stream_hash[$idx]['process_id']);
                            $this->stream_proc_dao->deleteProcess($stream_hash[$idx]['process_id']);
                            $start_new_proc = true;
                        } else {
                            $logger->logInfo("process " . $stream_hash[$idx]['process_id'] .
                                " listed with recent update time for instance with $owner_email and " . 
                            $stream_hash[$idx]['instance_id'] . "-- not starting another one",
                            __METHOD__.','.__LINE__);
                            $count++; // include this proc in the count of running processes
                        }
                    } else { // start up a process for that instance
                        $start_new_proc = true;
                    }
                    if ($start_new_proc) {
                        $logger->logInfo("starting new process for " . "$owner_email and " .
                        $instance->id, __METHOD__.','.__LINE__);
                        $pass = $this->owner_dao->getPass($owner_email);
                        if ($pass && isset($this->php_path)) {
                            // @TODO - check that the dir paths are set properly
                            // then exec using that owner email and the encrypted pwd as args
                            $logfile = $this->log_dir . '/' . $owner_email . '_' . $instance->id . '.log';
                            $pid = shell_exec('cd ' . $this->streaming_dir . '; ' . $this->php_path .
                                ' stream2.php ' . ' ' . $instance->id . ' ' .
                            $owner_email . ' ' . $pass . ' > ' . $logfile . ' 2>&1 & echo $!');
                            if (!isset($pid)) {
                                throw new StreamingException(
                                    "error: could not obtain PID when starting stream2 process.");
                            }
                            // insert PID and email/instance id information into the database.
                            $res = $this->stream_proc_dao->insertProcessInfo(trim($pid), $owner_email, $instance->id);
                            if (!$res) {
                                throw new StreamingException(
                                    "error: issue inserting process information into database.");
                            }

                            $logger->logInfo("started pid " . trim($pid) . " for $owner_email and instance id " .
                            $instance->id, __METHOD__.','.__LINE__);
                            $count++;
                        } else {
                            $logger->logError("error: not launching stream for $owner_email-- error " .
                                  "with specified password or php path", __METHOD__.','.__LINE__);
                        }
                    }
                    if ($count == self::MAX_INSTANCES) {
                        break; // only open user stream process for up to MAX_OWNERS instances
                    }
                } else {
                    $logger->logError("error: email info not available. not launching stream for instance "
                    . $instance->id, __METHOD__.','.__LINE__);
                }
            } // end foreach instance
        } // end foreach owner

    } // end launch_streams

    /**
     * @param  $pid
     * @return bool
     * currently unused
     */
    private function psExists($pid) {
        exec("ps ax | grep $pid 2>&1", $output);
        while ( list(,$row) = each($output) ) {
            $row_array = explode(" ", $row);
            $check_pid = $row_array[0];

            if ($pid == $check_pid) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws Exception
     * @return array
     * currently unused (information stored in database instead)
     */
    private function getExistingPIDs() {
        $logger = Logger::getInstance('stream_log_location');
        $dh = @opendir($this->streaming_dir);
        $pids = array();
        if (!$dh) {
            throw new Exception("Cannot open directory " . $this->streaming_dir);
        } else {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    $pos = strpos($file, '.pid');
                    if ($pos > 0) {
                        // extract pid
                        $pid = substr($file, 0, $pos);
                        $logger->logInfo("found pid $pid", __METHOD__.','.__LINE__);
                        $pids[]= $pid;
                    }
                }
            }
            closedir($dh);
            return $pids;
        }
        unset($dh, $dir, $file, $requiredFile);
        return $plugins;
    }

    /**
     * hah. this method courtesy of txt2re.com, a genius service.
     * (sadly,) currently not used.
     */
    private function isSoCool($psline) {
        $logger = Logger::getInstance('stream_log_location');
        $res = array();

        $re1='((?:[a-z][a-z]+))';
        $re2='.*?';
        $re3='(\\d+)';
        $re4='.*?';
        $re5='(stream2\\.php)';
        $re6='(\\s+)';
        $re7='(\\d+)';
        $re8='(\\s+)';
        $re9='([\\w-+]+(?:\\.[\\w-+]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7})';

        if ($c=preg_match_all ("/".$re1.$re2.$re3.$re4.$re5.$re6.$re7.$re8.$re9."/is", $psline, $matches)) {
            $word1=$matches[1][0];
            $int1=$matches[2][0];
            $file1=$matches[3][0];
            $ws1=$matches[4][0];
            $int2=$matches[5][0];
            $ws2=$matches[6][0];
            $email1=$matches[7][0];
            $logger->logInfo("($word1) ($int1) ($file1) ($ws1) ($int2) ($ws2) ($email1)", __METHOD__.','.__LINE__);
            $res = array('login' => $word1 , 'psid' => $int1, 'id' => $int2, 'email' => $email1);
        }
        return $res;
    }

    /**
     * currently not used (information stored in the db is used instead)
     */
    private function findAllRunningStreams() {
        $cmd = "ps auxwww | grep stream2.php | grep " . $this->php_path;
        exec($cmd, $output, $returnValue);
        $found = array();
        foreach ($output as $psline) {
            $res = $this->isSoCool($psline);
            if ($res) {
                $found[$res['email'] . "_" . $res['id']]= $res;
            }
        }
        return $found;
    }

    /**
     * @return array
     */
    private function killAllStreamingPIDs() {
        $logger = Logger::getInstance('stream_log_location');
        $pid_data = $this->stream_proc_dao->getAllStreamProcessIDs();
        foreach ($pid_data as $pid_row) {
            // kill the given pid
            $pid = $pid_row['process_id'];
            $logger->logInfo("killing: $pid", __METHOD__.','.__LINE__);
            $this->psKill($pid);
            // now delete it from the database
            $this->stream_proc_dao->deleteProcess($pid);
        }
        // return $pids;
    }

    /**
     * @param  $pid
     * @return void
     */
    private function psKill($pid) {
        $logger = Logger::getInstance('stream_log_location');
        $output = null;
        $returnValue = -1;
        exec("kill -9 $pid", $output, $returnValue);
        $logger->logInfo("killed: $pid", __METHOD__.','.__LINE__);
    }
}
