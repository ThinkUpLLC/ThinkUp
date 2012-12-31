--
-- Add filename field to insights table
--

ALTER TABLE  tu_insights ADD  filename VARCHAR( 100 ) COMMENT  'Name of file that generates and displays insight.';
