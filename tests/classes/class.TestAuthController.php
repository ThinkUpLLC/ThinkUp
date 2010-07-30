<?php
/**
 * Test AuthController
 *
 * Test auth controller to try the ThinkUpAuthController abstract class and Controller interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestAuthController extends ThinkUpAuthController {
    public function authControl() {
        $this->setViewTemplate('testme.tpl');
        $this->addToView('test', 'Testing, testing, 123');
        return $this->generateView();
    }
}