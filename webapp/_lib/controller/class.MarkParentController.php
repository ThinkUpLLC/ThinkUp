<?php
/**
 * Mark Parent Controller
 *
 * Mark a post the parent of a reply.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class MarkParentController extends ThinkUpAuthController {
    /**
     * Required query string parameters
     * @var array parend ID, orphan ID(s), parent/orphan post network, template, cache key
     */
    var $REQUIRED_PARAMS = array('pid', 'oid', 'n', 't', 'ck');

    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('session.toggle.tpl');
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('Missing required parameters.');
                $this->is_missing_param = true;
            }
        }
    }

    public function authControl(){
        if (!$this->is_missing_param) {
            $template = $_GET["t"];
            $cache_key = $_GET["ck"];
            $pid = $_GET["pid"];
            $oid =  $_GET["oid"];
            $network = $_GET['n'];
            $config = Config::getInstance();

            $post_dao = DAOFactory::getDAO('PostDAO');
            foreach ($oid as $o) {
                if ( isset($_GET["fp"])) {
                    $result = $post_dao->assignParent($pid, $o, $network, $_GET["fp"]);
                } else {
                    $result = $post_dao->assignParent($pid, $o, $network);
                }
            }

            $s = new SmartyThinkUp();
            $s->clear_cache($template, $cache_key);
            if ($result > 0 ) {
                $this->addToView('result', 'Assignment successful.');
            } else {
                $this->addToView('result', 'No data was changed.');
            }
        }
        return $this->generateView();
    }
}