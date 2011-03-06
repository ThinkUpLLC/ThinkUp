--
-- Remove the Flickr Thumbnails plugin and move all of its options (the Flickr API key) to the Expand URLs plugin
--

-- Get the Flickr Thumbnails plugin ID
SELECT @ftid :=id FROM tu_plugins WHERE folder_name='flickrthumbnails';

-- Set the plugin option namespace
SET @ftnspace = CONCAT("plugin_options-", @ftid);

-- Deactivate the Flickr Thumbnails plugin
UPDATE tu_plugins SET is_active = 0 WHERE id = @ftid;

-- Get the Expand URLs plugin ID
SELECT @euid :=id FROM tu_plugins WHERE folder_name='expandurls';

-- Set the plugin option namespace
SET @eunspace = CONCAT("plugin_options-", @euid);

-- Delete the Flickr Thumbnails plugin
DELETE FROM tu_plugins WHERE id = @ftid;

-- Transfer its options (the Flickr API key) to the Expand URLs plugin
UPDATE tu_options SET namespace=@eunspace WHERE namespace=@ftnspace;
