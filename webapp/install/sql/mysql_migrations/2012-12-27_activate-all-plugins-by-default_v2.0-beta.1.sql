-- Activate all plugins
UPDATE tu_plugins SET is_active=1;

-- Make active the default
ALTER TABLE  tu_plugins CHANGE  is_active  is_active TINYINT( 4 ) NOT NULL DEFAULT  '1' COMMENT  'Whether or not the plugin is activated (1 if so, 0 if not.)';