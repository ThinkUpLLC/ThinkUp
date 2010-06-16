<?php
/**
 * Test Faux Plugin for TestOfPluginHook
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestFauxPlugin implements TestAppPlugin {
    /**
     * For testing purposes
     */
    public function performAppFunction() {
        //do something here
    }

    /**
     * For testing purposes
     */
    public function renderConfiguration($owner) {
        return "this is my configuration screen HTML";
    }

}

/**
 * Test Faux Plugin without the required method for TestOfPluginHook
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestFauxPluginOne {
}