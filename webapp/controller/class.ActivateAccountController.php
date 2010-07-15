<?php
/**
 * Activate Account Controller
 * When a user registers for a ThinkTank account s/he receives an email with an activation link that lands here.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ActivateAccountController extends ThinkTankController {
    /**
     * Required query string parameters
     * @var array usr = instance email address, code = activation code
     */
    var $REQUIRED_PARAMS = array('usr', 'code');
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;
    /**
     * Constructor
     * @param bool $session_started
     * @return ActivateAccountController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->is_missing_param = true;
            }
        }
    }

    public function control() {
        $controller = new LoginController(true);
        if ($this->is_missing_param) {
            $controller->addErrorMessage('Invalid account activation credentials.');
        } else {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $acode = $owner_dao->getActivationCode($_GET['usr']);

            if ($_GET['code'] == $acode['activation_code']) {
                $owner_dao->updateActivate($_GET['usr']);
                $controller->addSuccessMessage("Success! Your account has been activated. Please log in.");
            } else {
                $controller->addErrorMessage('Houston, we have a problem: Account activation failed.');
            }
        }
        return $controller->go();
    }
}
