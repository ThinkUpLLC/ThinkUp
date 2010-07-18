<?php
/**
 * Test AuthController
 *
 * Test auth controller to try the ThinkTankAuthController abstract class and Controller interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestAuthController extends ThinkTankAuthController {
    public function authControl() {
        $this->setViewTemplate('testme.tpl');
        $this->addToView('test', 'Testing, testing, 123');
        return $this->generateView();
    }
}