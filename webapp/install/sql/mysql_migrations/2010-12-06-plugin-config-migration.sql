INSERT INTO tu_options (namespace, option_name, option_value, last_updated, created)
(SELECT concat('plugin_options-', plugin_id), option_name, option_value, now(), now() FROM tu_plugin_options);