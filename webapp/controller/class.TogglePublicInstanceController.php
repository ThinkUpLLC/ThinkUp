<?php
require_once 'controller/class.ThinkUpAuthController.php';
/**
 * Toggle Public Instance Controller
 * Add/remove an instance from the public timeline.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TogglePublicInstanceController extends ThinkUpAuthController {
    /**
     * Required query string parameters
     * @var array u = instance username, p = 1 or 0, active or inactive
     */
    var $REQUIRED_PARAMS = array('u', 'p');

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
            $is_public = ($_GET["p"] != 1)?false:true;
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $this->addToView('result', $instance_dao->setPublic($_GET["u"], $is_public));
        }
        return $this->generateView();
    }
}