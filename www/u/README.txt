Requirements

- MySQL 3.x or later
- PHP 4+ with GD Library
- Apache Server
- Linux

How to Install

1. First create a mysql database (say phplogin) for a particular user name (say guest) and password ( say guest). Then give all previleges of database to the user.
2. Copy the following SQL to create a table and structure

CREATE TABLE `users` (
`id` int(20) NOT NULL auto_increment,
`full_name` varchar(200) collate latin1_general_ci NOT NULL default '',
`user_name` varchar(200) collate latin1_general_ci NOT NULL default '',
`user_pwd` varchar(200) collate latin1_general_ci NOT NULL default '',
`user_email` varchar(200) collate latin1_general_ci NOT NULL default '',
`activation_code` int(10) NOT NULL default '0',
`joined` date NOT NULL default '0000-00-00',
`country` varchar(100) collate latin1_general_ci NOT NULL default '',
`user_activated` int(1) NOT NULL default '0',
PRIMARY KEY (`id`)
)

3. Open dbc.php to edit mysql database name, user name and password.