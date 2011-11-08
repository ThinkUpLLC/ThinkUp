--
-- Create tables for Groups/Lists support
--

CREATE TABLE tu_groups (
    id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
    group_id VARCHAR(50) NOT NULL COMMENT 'Group/list ID on the source network.',
    network VARCHAR(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
    group_name VARCHAR(50) NOT NULL COMMENT 'Name of the group or list on the source network.',
    is_active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Whether or not the group is active (1 if so, 0 if not.)',
    first_seen DATETIME NOT NULL COMMENT 'First time this group was seen on the originating network.',
    last_seen DATETIME NOT NULL COMMENT 'Last time this group was seen on the originating network.',
    PRIMARY KEY (id),
    INDEX group_network (group_id, network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Groups/lists/circles of users.';

CREATE TABLE tu_group_members (
    group_id VARCHAR(50) NOT NULL COMMENT 'Group/list ID on the source network.',
    network VARCHAR(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
    member_user_id VARCHAR(30) NOT NULL COMMENT 'User ID of group member on a given network.',
    is_active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Whether or not the user is active in the group (1 if so, 0 if not.)',
    first_seen DATETIME NOT NULL COMMENT 'First time this user was seen in the group.',
    last_seen DATETIME NOT NULL COMMENT 'Last time this user was seen in the group.',
    INDEX group_network (group_id, network),
    INDEX member_network (member_user_id, network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Service users who are members of groups/lists.';

CREATE TABLE tu_group_member_count (
    network VARCHAR(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
    member_user_id VARCHAR(30) NOT NULL COMMENT 'User ID on a particular service in a number of groups/lists.',
    date DATE NOT NULL COMMENT 'Date of group count.',
    count INT UNSIGNED NOT NULL COMMENT 'Total number of groups the user is in.',
    INDEX member_network (member_user_id, network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Group membership counts by date and time.';
