--
--  Add insight prefix field for label/badge text in display.
--
ALTER TABLE  tu_insights ADD  `prefix` VARCHAR( 255 ) NOT NULL COMMENT  'Prefix to the text content of the alert.' AFTER  slug;