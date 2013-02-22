<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ExportController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Michael Louis Thaler
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
 * Export Controller
 * Exports posts from an instance user on ThinkUp.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Michael Louis Thaler
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ExportController extends ThinkUpAuthController {
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

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('post.export.tpl');
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('No user to retrieve.');
                $this->is_missing_param = true;
            }
        }
    }

    public function authControl() {
        // set the content type to avoid profiler data in our .csv file
        $this->setContentType('text/csv');

        if (!$this->is_missing_param) {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail( $this->getLoggedInUser() );

            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            if ( isset($_GET['u']) && $instance_dao->isUserConfigured($_GET['u'], $_GET['n']) ){
                $username = $_GET['u'];
                $network = $_GET['n'];
                $instance = $instance_dao->getByUsernameOnNetwork($username, $network);
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                if ( !$owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance) ) {
                    $this->addErrorMessage('Insufficient privileges');
                    return $this->generateView();
                } else {
                    $type = isset($_GET['type']) ? $_GET['type'] : 'posts';
                    switch ($type) {
                        case 'replies':
                            $this->exportReplies();
                            break;
                        case 'favorites':
                            $this->exportAllFavPosts();
                            break;
                        case 'posts':
                        default:
                            $this->exportAllPosts();
                            break;
                    }
                }
            } else {
                $this->addErrorMessage('User '.$_GET['u'] . ' on '. $_GET['n']. ' is not in ThinkUp.');
                return $this->generateView();
            }
        } else {
            return $this->generateView();
        }
    }

    protected function exportAllPosts() {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts_it = $post_dao->getAllPostsByUsernameIterator($_GET['u'], $_GET['n']);
        $column_labels = array_keys(get_class_vars('Post'));

        self::outputCSV($posts_it, $column_labels, 'posts-'.$_GET['u'].'-'.$_GET['n']);
    }

    protected function exportAllFavPosts() {
        $fpost_dao = DAOFactory::getDAO('FavoritePostDAO');
        $posts_it = $fpost_dao->getAllFavoritePostsByUsernameIterator($_GET['u'], $_GET['n']);
        $column_labels = array_keys(get_class_vars('Post'));

        self::outputCSV($posts_it, $column_labels, 'favs-'.$_GET['u'].'-'.$_GET['n']);
    }

    protected function exportReplies() {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $replies_it = $post_dao->getRepliesToPostIterator($_GET['post_id'], $_GET['n']);
        $column_labels = array_keys(get_class_vars('Post'));

        self::outputCSV($replies_it, $column_labels, 'replies-'.$_GET['post_id']);
    }

    /**
     * Sends an associative array to the browser as a .csv spreadsheet.
     *
     * Flushes the output buffer on each line, to avoid clogging it with memory. An unfortunate side effect of this is
     * it means we can't count up the total size of the spreadsheet and put it in the HTTP headers (this would allow
     * browsers to display a progress bar as the spreadsheet is downloaded). The only way we'd be able to work around
     * this would be writing the data to a temp file first and then sending it to the user at the end; allowing this
     * as an optional paramater would be a possible future enhancement.
     *
     * @param array $data An associative array of data.
     * @param array $column_labels The first line of the CSV, by convention interpreted as column labels.
     * @param str $filename The name of the CSV file, defaults to export.csv.
     */
    public static function outputCSV($data, $column_labels, $filename="export") {
        // check for contents before clearing the buffer to silence PHP notices
        if (ob_get_contents()) {
            ob_end_clean();
        }

        // make sure the file name does not contain spaces.
        $filename = str_replace(' ', '_', $filename).'.csv';

        if ( ! headers_sent() ) { // this is so our test don't barf on us
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        $fp = fopen('php://output', 'w');
        // output csv header
        fputcsv($fp, $column_labels);
        foreach($data as $id => $post) {
            //Set links to null to avoid E_NOTICE: Array to string conversion
            //TODO: Properly handle this and add links to exported file; perhaps not for tweets which can contain
            //multiple links but the main media link on G+ or Facebook posts
            $post->links = null;
            fputcsv($fp, (array)$post);

            // flush after each fputcsv to avoid clogging the buffer on large datasets
            flush();
        }
        fclose($fp);
    }
}
