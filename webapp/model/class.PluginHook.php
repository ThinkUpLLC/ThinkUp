<?php
class PluginHook {
    private $callbacks = array(); // All the registered callbacks, an array of arrays where the index is the action name

    // Register a function/method as a callback function.
    public function registerCallback($callback, $trigger) {
        $this->callbacks[$trigger][] = $callback;
    }

    // Run all functions registered as callbacks
    public function emit($trigger, $params = null) {
        foreach ($this->callbacks[$trigger] as $callback) {
            call_user_func($callback, $params);
        }
    }
}

?>
