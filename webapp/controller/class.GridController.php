<?php
/**
 * Grid Controller
 *
 * Returns Unbuffered JSON list of posts for ajax grid view
 *
 * @author Msrk Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class GridController extends ThinkUpAuthController {

    /**
     * const max rows for grid
     */
     const MAX_ROWS = 100000;
     
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

    /**
     * Constructor
     * @param bool $session_started
     * @return InlineViewController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('No user to retrieve.');
                $this->is_missing_param = true;
                $this->setViewTemplate('inline.view.tpl');
            }
        }
        if (!isset($_GET['g'])) {
            $_GET['g'] = "tweets-all";
        }
    }

    /**
     * @return str Rendered view markup
     * @TODO Throw an Insufficient privileges Exception when owner doesn't have access to an instance
     */
    public function authControl() {
        if (!$this->is_missing_param) {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            if ( $instance_dao->isUserConfigured($_GET['u'], $_GET['n'])) {
                $username = $_GET['u'];
                $ownerinstance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                if (!$ownerinstance_dao->doesOwnerHaveAccess($owner, $username)) {
                    echo "{'status':'failed','message':'Insufficient privileges.'}";
                } else {
                    $post_dao = DAOFactory::GetDAO('PostDAO');
                    $mentions_it = $post_dao->getAllMentionsIterator($_GET['u'], self::MAX_ROWS, $_GET['n']);
                    //var_dump($mentions_it);
                    echo "{'status':'success','posts': [{}";
                    foreach($mentions_it as $key => $value) {
                        $data = array('text' => $value->post_text);
                        echo json_encode($data) . "\n";
                        flush();
                    }
                    echo "]";
                }
            } else {
                echo "{'status':'failed','message':'" . $_GET['u'] . "is not configured.'}";
            }
        } else {
            echo "{'status':'failed','message':'Missing Parameters'}";
        }
        
    }
}