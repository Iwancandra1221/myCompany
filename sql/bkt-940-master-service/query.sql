select * from ms_service

ALTER TABLE ms_service ADD is_active bit DEFAULT 1 NOT NULL
ALTER TABLE ms_service ADD created_by varchar(255) DEFAULT NULL
ALTER TABLE ms_service ADD created_date date DEFAULT NULL
ALTER TABLE ms_service ADD modified_by varchar(100) DEFAULT NULL
ALTER TABLE ms_service ADD modified_date date DEFAULT NULL


ALTER TABLE ms_service_kerusakan ADD autonumber INT IDENTITY(1,1);
ALTER TABLE ms_service_penyebab ADD autonumber INT IDENTITY(1,1);
ALTER TABLE ms_service_perbaikan ADD autonumber INT IDENTITY(1,1);