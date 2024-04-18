  ALTER TABLE Log_UniqueCode
	ADD deleted_by varchar(255) null,
	deleted_date datetime NULL,
	reason_deleted varchar(MAX) NULL