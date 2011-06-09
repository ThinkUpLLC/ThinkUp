ExportController
================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.ExportController.php

Copyright (c) 2009-2011 Gina Trapani, Michael Louis Thaler

Export Controller
Exports posts from an instance user on ThinkUp.


Properties
----------

REQUIRED_PARAMS
~~~~~~~~~~~~~~~

Required query string parameters

is_missing_param
~~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
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


authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
            // set the content type to avoid profiler data in our .csv file
            $this->setContentType('text/csv');
    
            if (!$this->is_missing_param) {
                $od = DAOFactory::getDAO('OwnerDAO');
                $owner = $od->getByEmail( $this->getLoggedInUser() );
    
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                if ( isset($_GET['u']) && $instance_dao->isUserConfigured($_GET['u'], $_GET['n']) ){
                    $username = $_GET['u'];
                    $network = $_GET['n'];
                    $instance = $instance_dao->getByUsernameOnNetwork($username, $network);
                    $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                    if ( !$owner_instance_dao->doesOwnerHaveAccess($owner, $instance) ) {
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


exportAllPosts
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        protected function exportAllPosts() {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $posts_it = $post_dao->getAllPostsByUsernameIterator($_GET['u'], $_GET['n']);
            $column_labels = array_keys(get_class_vars('Post'));
    
            self::outputCSV($posts_it, $column_labels, 'posts-'.$_GET['u'].'-'.$_GET['n']);
        }


exportAllFavPosts
~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        protected function exportAllFavPosts() {
            $fpost_dao = DAOFactory::getDAO('FavoritePostDAO');
            $posts_it = $fpost_dao->getAllFavoritePostsByUsernameIterator($_GET['u'], $_GET['n']);
            $column_labels = array_keys(get_class_vars('Post'));
    
            self::outputCSV($posts_it, $column_labels, 'favs-'.$_GET['u'].'-'.$_GET['n']);
        }


exportReplies
~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        protected function exportReplies() {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $replies_it = $post_dao->getRepliesToPostIterator($_GET['post_id'], $_GET['n']);
            $column_labels = array_keys(get_class_vars('Post'));
    
            self::outputCSV($replies_it, $column_labels, 'replies-'.$_GET['post_id']);
        }


outputCSV
~~~~~~~~~
* **@param** array $data An associative array of data.
* **@param** array $column_labels The first line of the CSV, by convention interpreted as column labels.
* **@param** str $filename The name of the CSV file, defaults to export.csv.


Sends an associative array to the browser as a .csv spreadsheet.

Flushes the output buffer on each line, to avoid clogging it with memory. An unfortunate side effect of this is
it means we can't count up the total size of the spreadsheet and put it in the HTTP headers (this would allow
browsers to display a progress bar as the spreadsheet is downloaded). The only way we'd be able to work around
this would be writing the data to a temp file first and then sending it to the user at the end; allowing this
as an optional paramater would be a possible future enhancement.

.. code-block:: php5

    <?php
        public static function outputCSV($data, $column_labels, $filename="export") {
            // check for contents before clearing the buffer to silence PHP notices
            if (ob_get_contents()) {
                ob_end_clean();
            }
    
            // make sure the file name does not contain spaces.
            $filename = str_replace(' ', '_', $filename);
    
            if( ! headers_sent() ) { // this is so our test don't barf on us
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                header('Pragma: no-cache');
                header('Expires: 0');
            }
    
            $fp = fopen('php://output', 'w');
            // output csv header
            fputcsv($fp, $column_labels);
            foreach($data as $id => $post) {
                fputcsv($fp, (array)$post);
    
                // flush after each fputcsv to avoid clogging the buffer on large datasets
                flush();
            }
            fclose($fp);
        }




