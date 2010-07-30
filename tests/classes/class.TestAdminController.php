<?php
/**
 * Test AdminController
 *
 * Test admin controller to try the ThinkUpAdminController abstract class and Controller interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestAdminController extends ThinkUpAdminController {
    public function authControl() {
        $this->setViewTemplate('testme.tpl');
        $this->addToView('test', 'Testing, testing, 123');
        return $this->generateView();
    }
}