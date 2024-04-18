CREATE TABLE TblGracePeriodJTFaktur (
    nourut int IDENTITY PRIMARY KEY,
    Wilayah varchar(255) NULL,
    Divisi varchar(20) NULL,
    Kd_Plg varchar(255) NOT NULL,
    JT_Lama datetime NOT NULL,
    JT_Baru datetime NOT NULL,
    User_Name varchar(255) NOT NULL,
    Entry_Time datetime NULL,
    request_no varchar(100) NULL,
    request_note varchar(255) NULL,
    request_status varchar(255) NULL,
    is_cancelled varchar(255) NULL,
    cancelled_by varchar(255) NULL,
    cancelled_date datetime NULL,
    cancelled_note varchar(255) NULL
);