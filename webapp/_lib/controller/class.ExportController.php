<?php
/**
 * Export Controller
 * Exports posts from an instance user on ThinkUp.
 *
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
        if (!$this->is_missing_param) {
            $od = DAOFactory::getDAO('OwnerDAO');
            $owner = $od->getByEmail( $this->getLoggedInUser() );

            $id = DAOFactory::getDAO('InstanceDAO');
            if ( isset($_GET['u']) && $id->isUserConfigured($_GET['u'], $_GET['n']) ){
                $username = $_GET['u'];
                $network = $_GET['n'];
                $oid = DAOFactory::getDAO('OwnerInstanceDAO');
                if ( !$oid->doesOwnerHaveAccess($owner, $username) ) {
                    $this->addErrorMessage('Insufficient privileges');
                    return $this->generateView();
                } else {
                    $type = isset($_GET['type']) ? $_GET['type'] : 'posts';

                    switch ($type) {
                    case 'replies':
                        $this->exportReplies();
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
        $pd = DAOFactory::getDAO('PostDAO');
        $posts_it = $pd->getAllPostsByUsernameIterator($_GET['u'], $_GET['n']);
        $vars = array_keys(get_class_vars('Post'));

        self::outputCSV($posts_it, $vars);
    }

    protected function exportReplies() {
        $pd = DAOFactory::getDAO('PostDAO');
        $replies = $pd->getRepliesToPostIterator($_GET['post_id'], $_GET['n']);
        $heading = array_keys(get_class_vars('Post'));

        self::outputCSV($replies, $heading);
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
     */
    public static function outputCSV($data, $column_labels) {
        // check for contents before clearing the buffer to silence PHP notices
        if (ob_get_contents()) {
            ob_end_clean();
        }

        if( ! headers_sent() ) { // this is so our test don't barf on us
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="export.csv"');
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

}
