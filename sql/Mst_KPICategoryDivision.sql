CREATE TABLE Mst_KPICategoryDivision (
    KPICategoryID varchar(255) not null,
    StartDate date not null,
    DivisionID varchar(255) null,
    DivisionName varchar(255) null,
    IsActive bit null,
    ModifiedBy varchar(255) null,
    ModifiedDate datetime null
);