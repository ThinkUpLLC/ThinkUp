CREATE TABLE tt_follower_count (
network_user_id BIGINT NOT NULL ,
network VARCHAR( 20 ) NOT NULL ,
date DATE NOT NULL ,
count INT NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;