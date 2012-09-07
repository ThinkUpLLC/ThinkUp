--
-- Widen text field to accommadate longer insights.
--
ALTER TABLE  tu_insights CHANGE  `text`  text TEXT NOT NULL COMMENT  'Text content of the alert.';