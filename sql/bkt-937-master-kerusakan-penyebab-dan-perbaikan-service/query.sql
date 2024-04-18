-- START >> ms_service_kerusakan
select * from ms_service_kerusakan
EXEC MC.sys.sp_rename N'MC.dbo.MsKerusakan', N'ms_service_kerusakan', 'OBJECT';
ALTER TABLE ms_service_kerusakan ADD is_active bit DEFAULT 1 NOT NULL
ALTER TABLE ms_service_kerusakan ADD created_by varchar(255) DEFAULT 'DATA AWAL' NOT NULL
ALTER TABLE ms_service_kerusakan ADD created_date date DEFAULT getdate() NOT NULL
ALTER TABLE ms_service_kerusakan ADD modified_by varchar(100) DEFAULT NULL
ALTER TABLE ms_service_kerusakan ADD modified_date date DEFAULT NULL
-- END << ms_service_kerusakan

-- START >> ms_service_penyebab
select * from MsPerbaikan
EXEC MC.sys.sp_rename N'MC.dbo.MsPerbaikan', N'ms_service_perbaikan', 'OBJECT';
select * from ms_service_perbaikan
ALTER TABLE ms_service_penyebab ADD is_active bit DEFAULT 1 NOT NULL
ALTER TABLE ms_service_penyebab ADD created_by varchar(255) DEFAULT 'DATA AWAL' NOT NULL
ALTER TABLE ms_service_penyebab ADD created_date date DEFAULT getdate() NOT NULL
ALTER TABLE ms_service_penyebab ADD modified_by varchar(100) DEFAULT NULL
ALTER TABLE ms_service_penyebab ADD modified_date date DEFAULT NULL
-- END << ms_service_penyebab

-- START >> ms_service_perbaikan
EXEC MC.sys.sp_rename N'MC.dbo.MsPerbaikan', N'ms_service_perbaikan', 'OBJECT';
ALTER TABLE ms_service_perbaikan ADD is_active bit DEFAULT 1 NOT NULL
ALTER TABLE ms_service_perbaikan ADD created_by varchar(255) DEFAULT 'DATA AWAL' NOT NULL
ALTER TABLE ms_service_perbaikan ADD created_date date DEFAULT getdate() NOT NULL
ALTER TABLE ms_service_perbaikan ADD modified_by varchar(100) DEFAULT NULL
ALTER TABLE ms_service_perbaikan ADD modified_date date DEFAULT NULL
-- END << ms_service_perbaikan


--rubah nama table MsService jadi ms_service
EXEC MC.sys.sp_rename N'MC.dbo.MsService', N'ms_service', 'OBJECT';