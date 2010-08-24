CREATE TABLE tu_encoded_locations (
  id int(11) NOT NULL AUTO_INCREMENT,
  short_name varchar(255) NOT NULL,
  full_name varchar(255) NOT NULL,
  latlng varchar(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE tu_encoded_locations ADD INDEX (short_name);
