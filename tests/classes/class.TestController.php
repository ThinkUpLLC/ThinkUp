<?php
/**
 * Test Controller
 * Test controller to try the ThinkUpController abstract class and Controller interface
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestController extends ThinkUpController {

    public function control() {
        if(isset($_GET['json'])) {
            $this->json_data = array( 'aname' => 'a value', 'alist' => array('apple', 'pear', 'banana') );
        } elseif (isset($_GET['throwexception'])) {
            throw new Exception("Testing exception handling!");
        } else {
            $this->setViewTemplate('testme.tpl');
            $this->addToView('test', 'Testing, testing, 123');
            if(isset($_GET['text'])) {
                $this->setContentType('text/plain');
            }
        }
        return $this->generateView();
    }
}
