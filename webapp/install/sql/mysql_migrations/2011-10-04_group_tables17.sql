-- Create tables for Groups/Lists

CREATE TABLE tu_groups (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    group_id VARCHAR(30) NOT NULL,
    network VARCHAR(20) NOT NULL,
    group_name VARCHAR(50) NOT NULL,
    active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    first_seen DATETIME NOT NULL,
    last_seen DATETIME NOT NULL,
    INDEX group_network (group_id, network)
);

/*
CREATE TABLE tu_group_owners (
    group_id VARCHAR(30) NOT NULL,
    network VARCHAR(20) NOT NULL,
    owner_user_id VARCHAR(30) NOT NULL,
    active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    first_seen DATETIME NOT NULL,
    last_seen DATETIME NOT NULL,
    INDEX group_network (group_id, network),
    INDEX owner_network (owner_user_id, network)
);
*/

CREATE TABLE tu_group_members (
    group_id VARCHAR(30) NOT NULL,
    network VARCHAR(20) NOT NULL,
    member_user_id VARCHAR(30) NOT NULL,
    active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    first_seen DATETIME NOT NULL,
    last_seen DATETIME NOT NULL,
    INDEX group_network (group_id, network),
    INDEX member_network (member_user_id, network)
);

CREATE TABLE tu_group_member_count (
    network VARCHAR(20) NOT NULL,
    member_user_id VARCHAR(30) NOT NULL,
    `date` DATE NOT NULL,
    count INT UNSIGNED NOT NULL,
    INDEX member_network (member_user_id, network)
);
