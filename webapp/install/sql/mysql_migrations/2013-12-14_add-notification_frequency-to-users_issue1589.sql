ALTER TABLE tu_owners ADD email_notification_frequency VARCHAR(10) DEFAULT 'daily' COMMENT 'How often to send email notifications (daily, weekly, both, never).';
