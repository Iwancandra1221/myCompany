CREATE TABLE trans_announcement (
    announcement_id int IDENTITY(1,1) PRIMARY KEY,
    is_active bit not null,
    announcement varchar(max) null,
    attachment_1 varchar(max) null,
    attachment_2 varchar(max) null,
    attachment_3 varchar(max) null,
    start_published_date date null,
    end_published_date date null,
    created_by nvarchar(50),
    created_date datetime null,
    modified_by nvarchar(50),
    modified_date datetime null
);