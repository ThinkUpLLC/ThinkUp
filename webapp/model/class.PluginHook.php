<?php
class PluginHook {
	private $plugins = array(); // Array that associates plugin folder shortname with the plugin object name
	private $object_method_callbacks = array(); // All the registered callbacks, an array of arrays where the index is the action name

	// Register an object method call
	public function registerObjectMethod($trigger, $o, $m) {
		$obj = new $o;
		$this->object_method_callbacks[$trigger][] = array($o, $m);
	}

	// Run all object methods registered as callbacks
	public function emitObjectMethod($trigger, $params = array()) {
		foreach ($this->object_method_callbacks[$trigger] as $callback) {
			call_user_func($callback, $params);
		}
	}

	// Register an object plugin name
	public function registerPlugin($shortname, $objectname) {
		$this->plugins[$shortname] = $objectname;
	}

	// Retrieve an object plugin name
	public function getPluginObject($shortname) {
		if (!isset($this->plugins[$shortname]) ) {
			throw new Exception("No plugin object defined for: " . $shortname);
		}
		return $this->plugins[$shortname];
	}

}
?>
