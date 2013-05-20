--
-- Add time_generated and time_updated fields to insights table
--

ALTER TABLE  tu_insights ADD time_generated datetime NOT NULL COMMENT 'Date and time when insight was generated.';

ALTER TABLE  tu_insights ADD time_updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Time when the insight was last updated.';

UPDATE tu_insights SET time_generated = CAST(date AS DATETIME);