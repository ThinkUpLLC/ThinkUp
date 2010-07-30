<?php
/**
 * Test Controller
 * Test controller to try the ThinkUpController abstract class and Controller interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestController extends ThinkUpController {
    public function control() {
        $this->setViewTemplate('testme.tpl');
        $this->addToView('test', 'Testing, testing, 123');
        return $this->generateView();
    }
}