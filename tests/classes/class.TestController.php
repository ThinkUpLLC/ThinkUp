<?php
/**
 * Test Controller
 * Test controller to try the ThinkTankController abstract class and Controller interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestController extends ThinkTankController implements Controller {
    public function control() {
        $this->setViewTemplate('testme.tpl');
        $this->addToView('test', 'Testing, testing, 123');
        return $this->renderView();
    }
}