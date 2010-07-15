<?php
/**
 * Test AdminController
 *
 * Test admin controller to try the ThinkTankAdminController abstract class and Controller interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestAdminController extends ThinkTankAdminController implements Controller {
    public function authControl() {
        $this->setViewTemplate('testme.tpl');
        $this->addToView('test', 'Testing, testing, 123');
        return $this->generateView();
    }
}