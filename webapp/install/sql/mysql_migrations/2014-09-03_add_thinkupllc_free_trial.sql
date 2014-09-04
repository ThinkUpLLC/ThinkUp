ALTER TABLE  tu_owners ADD  is_free_trial TINYINT NOT NULL DEFAULT  '0' 
COMMENT  'Whether or not ThinkUp.com member is on free trial (1 if so, 0 if not).' AFTER  membership_level;