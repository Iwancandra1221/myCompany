CREATE TABLE ms_service_jnsbrg (
    Kd_JnsBrg char(3) NOT NULL,
    Merk varchar(255) NOT NULL,
    Jns_Brg varchar(255) NOT NULL,
    JnsBrg varchar(255) NOT NULL,
    is_active int NULL,
    created_by varchar(50) NOT NULL,
    created_date datetime NOT NULL,
    modified_by varchar(50) NULL,
    modified_date datetime NULL,
    PRIMARY KEY (JnsBrg,Kd_JnsBrg, Merk,Jns_Brg)
);