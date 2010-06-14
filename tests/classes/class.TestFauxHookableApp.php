<?php
/**
 * Faux TestPluginHook class for TestOfPluginHook test
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestFauxHookableApp extends PluginHook {
    /**
     * For testing purposes
     */
    public function performAppFunction() {
        $this->emitObjectMethod('performAppFunction');
    }

    /**
     * For testing purposes
     * @param str $object_name Object name
     */
    public function registerPerformAppFunction($object_name) {
        $this->registerObjectMethod('performAppFunction', $object_name, 'performAppFunction');
    }
}
