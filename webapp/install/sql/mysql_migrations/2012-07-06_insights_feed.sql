CREATE TABLE IF NOT EXISTS tu_insight_baselines (
  date date NOT NULL COMMENT 'Date of baseline statistic.',
  instance_id int(11) NOT NULL COMMENT 'Instance ID.',
  slug varchar(100) NOT NULL COMMENT 'Unique identifier for a type of statistic.',
  value int(11) NOT NULL COMMENT 'The numeric value of this stat/total/average.',
  UNIQUE KEY unique_base (date,instance_id,slug),
  KEY date (date,instance_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci COMMENT='Insight baseline statistics.';
