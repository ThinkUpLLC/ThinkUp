ALTER TABLE tu_instances
    ADD posts_per_day decimal(7,2) DEFAULT NULL AFTER total_follows_in_system,
    ADD posts_per_week decimal(7,2) DEFAULT NULL AFTER posts_per_day,
    ADD percentage_replies decimal(4,2) DEFAULT NULL AFTER posts_per_week,
    ADD percentage_links decimal(4,2) DEFAULT NULL AFTER percentage_replies;
