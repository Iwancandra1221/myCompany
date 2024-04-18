CREATE TABLE Mst_ObjekPajak (
    kode_objek_pajak varchar(255) PRIMARY KEY,
    nama_objek_pajak  VARCHAR(max),
    pasal_pph VARCHAR(255),
    is_active BIT DEFAULT 1,
    modified_by varchar(255),
    modified_date DATETIME
);