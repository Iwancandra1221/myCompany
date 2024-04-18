CREATE TABLE Ms_JobsDT (
    job_id varchar(255) not null,
    DatabaseId int not null,
    job_schedule_type varchar(255) null,
    job_schedule_day varchar(255) null,
    job_priority int null,
    [server] varchar(255) null,
    [database] varchar(255) null,
    is_active bit not null,
    job_custom_query varchar(max) null
);