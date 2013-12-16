ALTER TABLE  tu_insights CHANGE  prefix  headline VARCHAR( 255 ) NOT NULL
COMMENT  'Headline of the insight content.';

ALTER TABLE  tu_insights ADD  header_image VARCHAR( 255 ) NULL DEFAULT NULL COMMENT  'Optional insight header image.'
AFTER  related_data;

