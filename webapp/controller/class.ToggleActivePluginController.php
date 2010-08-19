<?php
/**
 * Toggle Active Plugin Controller
 * Activate or deactivat a plugin.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class ToggleActivePluginController extends ThinkUpAdminController {
    /**
     * Required query string parameters
     * @var array pid = plugin ID, a = 1 or 0, active or inactive
     */
    var $REQUIRED_PARAMS = array('pid', 'a');

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
            $is_active = ($_GET["a"] != 1)?false:true;
            $plugin_dao = DAOFactory::getDAO('PluginDAO');
            $this->addToView('result', $plugin_dao->setActive($_GET["pid"], $is_active));
        }
        return $this->generateView();
    }
}