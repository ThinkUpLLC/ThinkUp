--
-- Create the cookies table
--

CREATE TABLE tu_cookies (
    cookie varchar(100) not null COMMENT 'Unique cookie key.',
    owner_email varchar(200) not null COMMENT 'Email of owner logged in with this cookie.',
    unique key (cookie),
    index (owner_email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Browser cookies that maintain logged-in user sessions.';
