<?php
/**
 * Toggle Active Instance Controller
 * Set an instance active or inactive.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class ToggleActiveInstanceController extends ThinkUpAdminController {
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

    public function adminControl(){
        if (!$this->is_missing_param) {
            $is_active = ($_GET["p"] != 1)?false:true;
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $this->addToView('result', $instance_dao->setActive($_GET["u"], $is_active));
        }
        return $this->generateView();
    }
}