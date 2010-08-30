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
                    $pd = DAOFactory::getDAO('PostDAO');
                    $posts_it = $pd->getAllPostsByUsernameIterator($username, $network);
                    if( ! headers_sent() ) { // this is so our test don't barf on us
                        // set export headers...
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="export.csv"');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                    }
                    // get object var names
                    $vars = array_keys(get_class_vars('Post'));
                    // get output handle
                    $fp = fopen('php://output', 'w');
                    // output csv header
                    fputcsv($fp, $vars);
                    foreach($posts_it as $id => $post) {
                        $post_array = array();
                        // output post csv line
                        fputcsv($fp, (array)$post);
                        // flush output buffer
                        flush();
                    }
                    // close output handle
                    fclose($fp);
                }
            } else {
                $this->addErrorMessage('User '.$_GET['u'] . ' on '. $_GET['n']. ' is not in ThinkUp.');
                return $this->generateView();
            }
        } else {
            return $this->generateView();
        }
    }
}