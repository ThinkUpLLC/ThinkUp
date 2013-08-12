--
-- Merges the group member count history table with the count history table
--
INSERT IGNORE INTO tu_count_history (SELECT member_user_id, NULL, network, 'group_membership_count', date, count FROM tu_group_member_count) #rollback=9;

DROP TABLE IF EXISTS tu_group_member_count;
