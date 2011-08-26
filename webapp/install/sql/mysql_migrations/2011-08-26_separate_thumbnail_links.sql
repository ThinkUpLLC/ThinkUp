-- Move image thumbnails to the new image_src field
UPDATE tu_links SET image_src=expanded_url WHERE is_image=1;

-- Set expanded_url equal to '' for images so that ExpandURLs redoes the expansion properly
UPDATE tu_links SET expanded_url='' WHERE is_image=1;

-- Drop deprecated field. From here on in, a link is an image if image_src is set
ALTER TABLE tu_links DROP is_image;