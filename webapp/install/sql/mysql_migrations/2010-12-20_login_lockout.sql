ALTER TABLE  `tu_owners` ADD  `failed_logins` INT NOT NULL DEFAULT  '0';

ALTER TABLE  `tu_owners` ADD  `account_status` VARCHAR( 150 ) NOT NULL DEFAULT  '';