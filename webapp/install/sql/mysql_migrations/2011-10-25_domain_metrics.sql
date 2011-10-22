CREATE TABLE tu_domain_metrics (
   instance_id INT(11) NOT NULL COMMENT 'Internal instance ID',
   date DATE NOT NULL COMMENT 'Date of metric values',
   widget_like_views INT UNSIGNED NOT NULL COMMENT 'Number of times people viewed Like buttons on your site',
   widget_likes INT UNSIGNED NOT NULL COMMENT 'Number of times people clicked Like buttons on your site',
   UNIQUE INDEX user_date (instance_id, date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Domain metrics collected by social network, e.g. Facebook Insights';
