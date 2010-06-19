<?php
/**
 * Inline View Controller
 *
 * The AJAX-loaded HTML which fills in subtab content in ThinkTank's private dashboard.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InlineViewController extends ThinkTankAuthController {

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
        $this->addToView('controller_title', 'Inline View');
        $this->setViewTemplate('inline.view.tpl'); //default;requested tab may override
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (! isset($_GET[$param] ) ) {
                $this->addToView('error', 'Required query string parameter '.$param. ' missing.');
                $this->is_missing_param = true;
            }
        }
    }

    /**
     * @return str Rendered view markup
     * @TODO Throw an Insufficient privileges Exception when owner doesn't have access to an instance
     */
    public function authControl() {
        $webapp = Webapp::getInstance();
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
        $instance_dao = DAOFactory::getDAO('InstanceDAO');

        $continue = true;
        if (!$this->is_missing_param) {
            if ( $instance_dao->isUserConfigured($_GET['u'])) {
                $username = $_GET['u'];
                global $db; //@TODO: Remove once PDO port is complete
                $ownerinstance_dao = new OwnerInstanceDAO($db);
                if (!$ownerinstance_dao->doesOwnerHaveAccess($owner, $username)) {
                    $this->addToView('error','Insufficient privileges. <a href="/">Back</a>.');
                    $continue = false;
                } else {
                    $instance = $instance_dao->getByUsernameOnNetwork($username, $_GET['n']);
                    $this->addToViewCacheKey($instance->network_username);
                    $this->addToView('i', $instance);
                }
            } else {
                $this->addToView('error', $_GET['u'] . " is not configured.");
                $continue = false;
            }
        } else {
            $continue = false;
        }

        if ($continue) {
            if (!isset($_GET['d'])) {
                $_GET['d'] = "tweets-all";
            }
            $this->addToViewCacheKey($_GET['d']);

            $webapp->setActivePlugin($instance->network);
            $tab = $webapp->getTab($_GET['d'], $instance);
            $this->setViewTemplate($tab->view_template);
            $this->addToView('display', $tab->short_name);
            $this->addToView('header', $tab->name);
            $this->addToView('description', $tab->description);

            foreach ($tab->datasets as $dataset) {
                $this->addToView($dataset->name, $dataset->retrieveDataset());
            }
        }
        return $this->generateView();
    }
}
