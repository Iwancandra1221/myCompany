CREATE NONCLUSTERED INDEX idx_Tgl_PO ON TblPOHeader (Tgl_PO);
CREATE NONCLUSTERED INDEX idx_status ON TblPOHeader (status);
CREATE NONCLUSTERED INDEX idx_PO_Type ON TblPOHeader (PO_Type);
CREATE NONCLUSTERED INDEX idx_Kd_Supl ON TblPOHeader (Kd_Supl);
CREATE NONCLUSTERED INDEX idx_Kategori_Brg ON TblPOHeader (Kategori_Brg);
CREATE NONCLUSTERED INDEX idx_Kd_Lokasi ON TblPOHeader (Kd_Lokasi);

DROP INDEX IF EXISTS IX_tblPOHeader ON tblPOHeader;
-- Membuat indeks baru
CREATE NONCLUSTERED INDEX IX_tblPOHeader 
ON tblPOHeader (Kategori_Brg, Kd_Supl, tgl_PO, status, PO_Type, Kd_Lokasi);


CREATE NONCLUSTERED INDEX idx_No_PO ON TblPoDetail (No_PO);
CREATE NONCLUSTERED INDEX idx_Kd_Brg ON TblPoDetail (Kd_Brg);

DROP INDEX IF EXISTS IX_tblPODetail ON TblPoDetail;
-- Membuat indeks baru
CREATE NONCLUSTERED INDEX IX_tblPODetail 
ON TblPoDetail (No_PO, Kd_Brg);
