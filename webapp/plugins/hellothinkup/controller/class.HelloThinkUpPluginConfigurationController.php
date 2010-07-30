<?php
/**
 * HelloThinkUp Plugin configuration controller
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class HelloThinkUpPluginConfigurationController extends ThinkUpAuthController {
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

    public function authControl() {
        $config = Config::getInstance();
        $this->setViewTemplate($config->getValue('source_root_path').'webapp/plugins/hellothinkup/view/hellothinkup.account.index.tpl');
        $this->addToView('message', 'Hello, world! This is the example plugin configuration page for  '.$this->owner->email .'.');
        return $this->generateView();
    }
}