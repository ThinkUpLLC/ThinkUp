ALTER TABLE tt_owners DROP user_name,  DROP country;

ALTER TABLE  tt_owners CHANGE  user_activated  is_activated INT( 1 ) NOT NULL DEFAULT  '0';

ALTER TABLE  tt_owners CHANGE  user_pwd  pwd VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE  tt_owners CHANGE  user_email  email VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
