<?php
/**
 * Test AuthAPIController
 *
 * Test auth API controller to try the ThinkUpAuthAPIController abstract class and Controller interface
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 */
class TestAuthAPIController extends ThinkUpAuthAPIController {
    public function authControl() {
        if ($this->isAPICall()) {
            $this->setContentType('application/json; charset=UTF-8');
            return '{"result":"success"}';
        } else {
            return '<html><body>Success</body></html>';
        }
    }
}