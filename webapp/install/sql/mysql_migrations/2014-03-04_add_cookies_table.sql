--
-- Create the cookies table
--

CREATE TABLE tu_cookies (
    cookie varchar(100) not null,
    owner_email varchar(200) not null,
    unique key (cookie),
    index (owner_email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Store cookies to remember users long-term.';
