--
-- Create the photos table
--

CREATE TABLE tu_photos (
    id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
    post_key int(11) NOT NULL COMMENT 'Internal ID of photo post.',
    filter varchar(255) COMMENT 'Native filter used on the photo.',
    standard_resolution_url varchar(255) COMMENT 'URL of standard resolution image file.',
    low_resolution_url varchar(255) COMMENT 'URL of low resolution image file.',
    thumbnail_url varchar(255) COMMENT 'URL of thumbnail image file.',
    PRIMARY KEY (id),
    UNIQUE KEY post_key (post_key)
) COMMENT='Photos posted by service users on a given network.';

