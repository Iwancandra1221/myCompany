use MC
GO
ALTER TABLE Log_UniqueCode
ADD brand varchar(255) NULL
GO
UPDATE mc.dbo.Log_UniqueCode 
SET mc.dbo.Log_UniqueCode.brand = rtrim(ih.Merk)
from bhakti.dbo.tblinheader ih
where mc.dbo.Log_UniqueCode.ProductID = ih.Kd_Brg
GO  