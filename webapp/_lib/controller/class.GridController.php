<?php
/**
 * Grid Controller
 *
 * Returns Unbuffered JS XSS callback/JSON list of posts for javascript grid search view
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class GridController extends ThinkUpAuthController {

    /**
     * const max rows for grid
     */
    const MAX_ROWS = 5000;

    /**
     * number of days to look back for retweeted posts
     */
    const MAX_RT_DAYS = 30;

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
                $this->addInfoMessage('No user data to retrieve.');
                $this->is_missing_param = true;
                $this->setViewTemplate('inline.view.tpl');
            }
        }
        if (!isset($_GET['d'])) {
            $_GET['d'] = "tweets-all";
        }
    }

    /**
     * Outputs javascript callback string with json array/list of post as an argument
     */
    public function authControl() {
        if (!$this->is_missing_param) {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            if ( $instance_dao->isUserConfigured($_GET['u'], $_GET['n'])) {
                $username = $_GET['u'];
                $ownerinstance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                $instance = $instance_dao->getByUsername($username, $_GET['n']);
                if (!$ownerinstance_dao->doesOwnerHaveAccess($owner, $instance)) {
                    echo '{"status":"failed","message":"Insufficient privileges."}';
                } else {
                    echo "tu_grid_search.populate_grid(";
                    $post_dao = DAOFactory::GetDAO('PostDAO');
                    $posts_it = null;
                    if( $_GET['d'] == 'tweets-all') {
                        $posts_it = $post_dao->getAllPostsByUsernameIterator($_GET['u'], $_GET['n'], self::MAX_ROWS);
                    } else if ($_GET['d'] == 'tweets-mostreplies') {
                        $posts_it = $post_dao->getAllMentionsIterator($_GET['u'], self::MAX_ROWS, $_GET['n']);
                    } else if ($_GET['d'] == 'tweets-mostretweeted') {
                        $posts_it = $post_dao->getMostRetweetedPostsIterator($_GET['u'], $_GET['n'], 
                        self::MAX_ROWS, self::MAX_RT_DAYS);
                    }
                    echo '{"status":"success","posts": [' . "\n";
                    $cnt = 0;
                    foreach($posts_it as $key => $value) {
                        $cnt++;
                        $data = array('id' => $cnt, 'text' => $value->post_text, 'post_id' => $value->post_id,
                        'author' => $value->author_username, 'date' => $value->adj_pub_date);
                        echo json_encode($data) . ",\n";
                        flush();
                    }
                    $data = array('id' => -1, 'text' => 'Last Post',
                        'author' => 'nobody');
                    echo json_encode($data);
                    echo ']});';
                }
            } else {
                echo '{"status":"failed","message":"' . $_GET['u'] . 'is not configured."}';
            }
        } else {
            echo '{"status":"failed","message":"Missing Parameters"}';
        }

    }
}