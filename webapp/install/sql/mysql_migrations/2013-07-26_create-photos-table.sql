--
-- Create the photos table for the Instagram plugin
--

CREATE TABLE tu_photos (
	id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Interal unique ID of photo.',
	internal_post_id int(11) NOT NULL COMMENT 'ID of the row in the posts table for more information on a photo.',
	photo_page varchar(255) COMMENT 'URL of the photo page inside the respective service.',
	filter varchar(255) COMMENT 'Native filter used on the photo.',
	standard_resolution_url varchar(255) COMMENT 'URL of standard resolution image file.',
	low_resolution_url varchar(255) COMMENT 'URL of low resolution image file.',
	thumbnail_url varchar(255) COMMENT 'URL of thumbnail image file.',
	PRIMARY KEY (id),
	UNIQUE KEY internal_post_id (internal_post_id)
) COMMENT='Photos posted by service users on a given network.';