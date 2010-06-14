<?php
/**
 * Test Plugin for use with TestOfPluginHook
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface TestAppPlugin extends ThinkTankPlugin {

    /**
     * Test method for TestOfPluginHook
     */
    public function performAppFunction();
}