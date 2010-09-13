<?php
/**
 * Inline View Controller
 *
 * The AJAX-loaded HTML which fills in subtab content in ThinkUp's private dashboard.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InlineViewController extends ThinkUpAuthController {

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
        $this->setPageTitle('Inline View');
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('No user to retrieve.');
                $this->is_missing_param = true;
                $this->setViewTemplate('inline.view.tpl');
            }
        }
        if (!isset($_GET['d'])) {
            $_GET['d'] = "tweets-all";
        }
    }

    /**
     * @return str Rendered view markup
     * @TODO Throw an Insufficient privileges Exception when owner doesn't have access to an instance
     */
    public function authControl() {
        if (!$this->is_missing_param) {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance = $instance_dao->getByUsernameOnNetwork($_GET['u'], $_GET['n']);
            if (isset($instance)) {
                $webapp = Webapp::getInstance();
                $webapp->setActivePlugin($instance->network);
                $tab = $webapp->getTab($_GET['d'], $instance);
                $this->setViewTemplate($tab->view_template);
            } else {
                $continue = false;
            }
        } else {
            $continue = false;
        }

        if ($this->shouldRefreshCache()) {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($this->getLoggedInUser());

            $continue = true;
            if (!$this->is_missing_param) {
                $instance = $instance_dao->getByUsernameOnNetwork($_GET['u'], $_GET['n']);
                if ( isset($instance)) {
                    $ownerinstance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                    if (!$ownerinstance_dao->doesOwnerHaveAccess($owner, $instance)) {
                        $this->addErrorMessage('Insufficient privileges. <a href="/">Back</a>.');
                        $continue = false;
                    } else {
                        $this->addToView('i', $instance);
                    }
                } else {
                    $this->addErrorMessage($_GET['u'] . " is not configured.");
                    $continue = false;
                }
            } else {
                $continue = false;
            }

            if ($continue) {
                $this->addToView('display', $tab->short_name);
                $this->addToView('header', $tab->name);
                $this->addToView('description', $tab->description);

                foreach ($tab->datasets as $dataset) {
                    if($dataset->isSearchable()) {
                        $view_name = 'is_searchable';
                        $this->addToView($view_name, true);
                    }
                    $this->addToView($dataset->name, $dataset->retrieveDataset());
                }
            } else {
                $this->setViewTemplate('inline.view.tpl');
            }
        }
        return $this->generateView();
    }
}
