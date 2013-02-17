ALTER TABLE tu_instances_twitter DROP last_unfav_page_checked;
ALTER TABLE tu_instances_twitter DROP last_page_fetched_favorites;

DELETE FROM tu_options WHERE option_name='favs_older_pages' OR option_name='favs_cleanup_pages';