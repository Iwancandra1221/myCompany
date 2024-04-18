ALTER TABLE email_hashes ADD access_count INT DEFAULT 1 NOT NULL;
ALTER TABLE email_hashes ADD modified_date DATETIME DEFAULT NULL;
ALTER TABLE email_hashes ADD email_to  varchar(255) DEFAULT NULL;