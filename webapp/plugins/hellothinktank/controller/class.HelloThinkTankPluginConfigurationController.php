<?php
/**
 * HelloThinkTank Plugin configuration controller
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class HelloThinkTankPluginConfigurationController extends ThinkTankAuthController {
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     * Constructor
     * @param Owner $owner
     */
    public function __construct($owner) {
        parent::__construct(true);
        $this->owner = $owner;
    }

    public function auth_control() {
        $config = Config::getInstance();
        $this->setViewTemplate($config->getValue('source_root_path').'webapp/plugins/hellothinktank/view/hellothinktank.account.index.tpl');
        $this->addToView('message', 'Hello, world! This is the example plugin configuration page for  '.$this->owner->user_email .'.');
        return $this->generateView();
    }
}