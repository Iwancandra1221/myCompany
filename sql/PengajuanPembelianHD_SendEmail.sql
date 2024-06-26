USE [BHAKTI]
GO
/****** Object:  StoredProcedure [dbo].[PengajuanPembelianHD_SendEmail]    Script Date: 3/2/2023 9:13:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER PROCEDURE [dbo].[PengajuanPembelianHD_SendEmail]
--DECLARE	
	@Kd_Pengajuan AS VARCHAR(255)='PPBI/DMI/2206/0011',
	@Status AS VARCHAR(255)='TAMBAH'
AS
BEGIN
	DECLARE @MSG AS VARCHAR(MAX)
	DECLARE @db_cursor AS CURSOR, @NEXTDIV AS VARCHAR(100)
	DECLARE @@Email as VARCHAR(255), @@Emails as VARCHAR(1000), @@UserName varchar(255), @@Atasan varchar(255)
	DECLARE @@Subject as VARCHAR(255), @@Body as VARCHAR(MAX)
	DECLARE @Email as BIT, @ACTION VARCHAR(255), @NO_TRX VARCHAR(255), @Users_approved VARCHAR(255)
	SET @Email = 0
	
	
	DECLARE @NM_PENGAJUAN VARCHAR(255), @TGL_PENGAJUAN VARCHAR(255), @USERJABATAN VARCHAR(255), @USERDIVISI VARCHAR(255),
		@KETERANGAN VARCHAR(1000), @KD_JENISBI VARCHAR(255), @KD_MERKBI VARCHAR(255), @KD_PENGIRIMANBI VARCHAR(255),
		@JENIS_PENGIRIMAN VARCHAR(255), @TGL_DIBUTUHKAN VARCHAR(255), @KET_CANCELLED VARCHAR(1000),
		@CANCELLED_BY VARCHAR(255), @CANCELLED_DATE VARCHAR(255), @STATUS_PENGAJUAN VARCHAR(255),
		@APPROVALMANAGER_BY VARCHAR(255), @APPROVALMANAGER_DATE VARCHAR(255), @CREATE_BY VARCHAR(255),
		@APPROVALGM_BY VARCHAR(255), @APPROVALGM_DATE VARCHAR(255),
		@APPROVALPURCHASING_BY VARCHAR(255), @APPROVALPURCHASING_DATE VARCHAR(255),
		@TGL_ESTSELESAI VARCHAR(255), @TGL_ESTKIRIM VARCHAR(255), @PURCHASING_NOTE VARCHAR(1000),
		@REJECT_NOTE VARCHAR(1000)
	SELECT @NM_PENGAJUAN = HD.NM_PENGAJUAN, @TGL_PENGAJUAN = CONVERT(VARCHAR(50),HD.Entry_Time,113),
		@USERJABATAN = HD.UserJabatan, @USERDIVISI = HD.UserDivisi, @KETERANGAN =HD.Keterangan,
		@KD_JENISBI = HD.Kd_JenisBI, @KD_MERKBI = HD.Kd_MerkBI, @KD_PENGIRIMANBI=HD.Kd_PengirimanBI,
		@JENIS_PENGIRIMAN=HD.JenisPengiriman, @TGL_DIBUTUHKAN=CONVERT(VARCHAR(50),hd.Tgl_Dibutuhkan,106),
		@KET_CANCELLED = ISNULL(HD.Ket_Cancelled,''), @CANCELLED_BY=ISNULL(HD.Cancelled_By, ''),
		@CANCELLED_DATE = (CASE WHEN HD.CANCELLED_DATE IS NULL THEN '' ELSE CONVERT(VARCHAR(50),HD.CANCELLED_DATE,113) END),
		@STATUS_PENGAJUAN = HD.Status_Pengajuan, @APPROVALMANAGER_BY=ISNULL(HD.APPROVALMANAGER_BY,''),
		@APPROVALMANAGER_DATE= (CASE WHEN HD.APPROVALMANAGER_DATE IS NULL THEN '' ELSE CONVERT(VARCHAR(50),HD.APPROVALMANAGER_DATE,113) END),
		@APPROVALGM_BY=ISNULL(HD.APPROVALGM_BY,''),
		@APPROVALGM_DATE= (CASE WHEN HD.APPROVALGM_DATE IS NULL THEN '' ELSE CONVERT(VARCHAR(50),HD.APPROVALGM_DATE,113) END),
		@CREATE_BY = HD.[USER_NAME], @APPROVALPURCHASING_BY = ISNULL(HD.UpdatePurchasing_By,''),
		@APPROVALPURCHASING_DATE= (CASE WHEN HD.UpdatePurchasing_Date IS NULL THEN '' ELSE CONVERT(VARCHAR(50),HD.UpdatePurchasing_Date,113) END),
		@TGL_ESTSELESAI= (CASE WHEN HD.TglEstSelesai  IS NULL THEN '' ELSE CONVERT(VARCHAR(50),HD.TglEstSelesai,113) END),
		@TGL_ESTKIRIM= (CASE WHEN HD.TglEstKirim  IS NULL THEN '' ELSE CONVERT(VARCHAR(50),HD.TglEstKirim,113) END),
		@PURCHASING_NOTE = ISNULL(HD.KETERANGAN2,''), @REJECT_NOTE = ISNULL(KET_CANCELLED2,'')
	FROM TBLPENGAJUANPEMBELIANHD HD
	WHERE HD.Kd_Pengajuan = @Kd_Pengajuan
	
	
	DECLARE @KDBRG varchar(255), @MERKBRG varchar(255), @DIVBRG varchar(255)
	IF @KD_JENISBI = 'PRODUK'
	BEGIN
		SELECT TOP 1 @KDBRG = a.KD_BRG, @MERKBRG=b.MERK, @DIVBRG = (case when b.Cosan='Y' then 'CO&SANITARY' else b.Divisi end)
		FROM TblPengajuanPembelianDT a INNER JOIN TblINHeader b on a.Kd_Brg=LTRIM(RTRIM(b.KD_BRG))
		WHERE Kd_Pengajuan = @Kd_Pengajuan 
		
		IF @DIVBRG = 'MIYAKOKR'
		BEGIN
			SET @KD_MERKBI = 'MIYAKOKR'
		END
	END
		
	IF EXISTS(SELECT * FROM TblPengajuanPembelianHD WHERE Kd_Pengajuan=@Kd_Pengajuan) AND
		EXISTS(SELECT * FROM TBLPENGAJUANPEMBELIANDT WHERE KD_PENGAJUAN = @Kd_Pengajuan)
	BEGIN
		SET @Email = 1
	END
	ELSE
	BEGIN
		SET @Email = 0
	END

	IF @Email = 1
	BEGIN
		SET @NO_TRX = REPLACE(@KD_PENGAJUAN,'/','_')
		
		IF @STATUS = 'TAMBAH' OR @STATUS = 'RUBAH'
		BEGIN
			IF @KD_JENISBI = 'SPAREPART'
			BEGIN
				SET @NEXTDIV = 'SERVICE SPAREPART (MANAGER)'
			END
			ELSE IF @DIVBRG='CO&SANITARY'
			BEGIN
				SET @NEXTDIV = 'CO&SANITARY (MANAGER)'
			END
			ELSE IF @DIVBRG='MIYAKOKR' OR @DIVBRG = 'MICOOK'
			BEGIN
				SET @NEXTDIV = 'MICOOK (MANAGER)'
			END
			ELSE IF @KD_MERKBI = 'MIYAKO'
			BEGIN				
				SET @NEXTDIV = 'MIYAKO (MANAGER)'
			END
			ELSE IF @KD_MERKBI='RINNAI' 
			BEGIN
				SET @NEXTDIV = 'RINNAI (MANAGER)'
			END
			ELSE IF @KD_MERKBI = 'SHIMIZU' 
			BEGIN
				SET @NEXTDIV = 'SHIMIZU (MANAGER)'
			END
			ELSE
			BEGIN
				SET @NEXTDIV = 'OPERASIONAL (GENERAL MANAGER)'
			END
			
			IF @STATUS = 'TAMBAH'
			BEGIN
				SET @@SUBJECT = 'REQ.PEMBELIAN IMPORT BARU ['+@NO_TRX+']'
			END
			ELSE
			BEGIN
				SET @@SUBJECT = 'REQ.PEMBELIAN IMPORT REV. ['+@NO_TRX+']'
			END
		END
		ELSE IF @STATUS = 'WAITING FOR GM APPROVAL'
		BEGIN
			SET @NEXTDIV = 'PENJUALAN (GENERAL MANAGER)'
			SET @@SUBJECT = 'REQ.PEMBELIAN IMPORT ['+@NO_TRX+']'
		END
		ELSE IF @STATUS = 'MANAGER APPROVED'
		BEGIN
			SET @NEXTDIV = 'PURCHASING IMPORT'
			SET @@SUBJECT = 'REQ ['+@NO_TRX+'] DIAPPROVE'
		END
		ELSE IF @STATUS = 'MANAGER REJECTED'
		BEGIN
			SET @NEXTDIV = 'PURCHASING IMPORT'
			SET @@SUBJECT = 'REQ ['+@NO_TRX+'] DITOLAK'
		END
		ELSE IF @STATUS = 'PURCHASING APPROVED'
		BEGIN
			SET @NEXTDIV = ''
			SET @@SUBJECT = 'REQ.PEMBELIAN IMPORT '+@NO_TRX+' DIPROSES'
		END
		ELSE IF @STATUS = 'REQUEST FOR CANCEL'
		BEGIN
			SET @NEXTDIV = 'PURCHASING IMPORT'
			SET @@SUBJECT = 'PENGAJUAN PEMBATALAN '+@NO_TRX
		END
		ELSE IF @STATUS = 'REQUEST CANCEL APPROVED'
		BEGIN
			SET @NEXTDIV = ''
			SET @@SUBJECT = 'PENGAJUAN PEMBATALAN '+@NO_TRX+' DISETUJUI'
		END
		ELSE IF @STATUS = 'REQUEST CANCEL REJECTED'
		BEGIN
			SET @NEXTDIV = ''
			SET @@Subject = 'PENGAJUAN PEMBATALAN '+@NO_TRX+' DITOLAK'
		END

		SET @@Emails = ''
		SELECT @@Emails AS Email1

		SELECT @@Email = isnull(User_Email,'')
		FROM TblUser 		
		WHERE [USER_NAME]=@CANCELLED_BY OR [USER_NAME]=@CREATE_BY
		
		IF @NEXTDIV <> ''
		BEGIN			
			IF @Status<>'TAMBAH' and @Status<>'RUBAH' AND @STATUS<>'WAITING FOR GM APPROVAL' AND @@EMAIL<>''
			BEGIN   
				SET @@Emails += (case when @@Emails<>'' then ',' else '' end) + '"' + @@Email + '"'
				SELECT @@Emails AS Email2
			END   
			
			Select @Users_approved=b.User_Name
			From Cof_Email_Job a inner join TblUser b on a.Penerima_Email = b.User_Name
			Where Nama_Job=@NEXTDIV and a.Aktif=1 

			SET @db_cursor = CURSOR FOR
				Select rtrim(b.User_Email), rtrim(a.Penerima_Email)
				From Cof_Email_Job a inner join TblUser b on a.Penerima_Email = b.User_Name
				Where Nama_Job=@NEXTDIV and a.Aktif=1 

			OPEN @db_cursor  
			FETCH NEXT FROM @db_cursor INTO @@Email,@@UserName
			WHILE @@FETCH_STATUS = 0   
			BEGIN   
				SET @@Emails = (case when @@Emails<>'' then ',' else '' end) + '"' + @@Email + '"'
				SELECT @@Emails AS Email3

				IF @Status='TAMBAH' or @Status='RUBAH' or @Status='WAITING FOR GM APPROVAL'
				BEGIN
					SET @@Atasan=@@UserName
				END
				FETCH NEXT FROM @db_cursor INTO @@Email,@@UserName 
			END   
			CLOSE @db_cursor   
			DEALLOCATE @db_cursor
		END
		ELSE IF @@EMAIL<>''
		BEGIN
			SET @@Emails += (case when @@Emails<>'' then ',' else '' end) + '"' + @@Email + '"'
			SELECT @@Emails AS Email4
		END

		BEGIN TRANSACTION
		BEGIN TRY	
			
			IF @@Emails <> ''
			BEGIN

				DECLARE @Urls varchar(1000), @isSuccess bit
				DECLARE @UserTO varchar(max)   
				DECLARE @mcUrl varchar(1000) 
				DECLARE @Priority varchar(1000)
				SELECT @mcUrl = myCompany_URL FROM TblConfig   
				
				DECLARE @Object AS INT;
				DECLARE @ResponseText AS VARCHAR(8000);
				DECLARE @RequestEmail As VARCHAR(255);
				SET @RequestEmail='';
				SELECT @RequestEmail = User_Email FROM TblUser where User_Name = @NM_PENGAJUAN 
				if @RequestEmail=''
				BEGIN 
					SET @RequestEmail='';
				END

	
				SET @Priority = 0	
				IF @NEXTDIV in ('(MANAGER)')
					BEGIN 
					SET @Priority = 2
				END
				ELSE IF @NEXTDIV in ('GENERAL MANAGER')
					BEGIN
					SET @Priority = 1
				END

				DECLARE @EmailReplace As VARCHAR(255);
				select @EmailReplace=replace(@@Emails,'".', '')
				select @EmailReplace=replace(@@Emails,'"', '')

				DECLARE @bodyGet AS NVARCHAR(MAX) =
				'?ApprovalType=PURCHASE IMPORT
					&RequestNo='+@Kd_Pengajuan+'  
					&RequestBy='+@NM_PENGAJUAN+'  
					&RequestDate='+@TGL_PENGAJUAN+'
					&RequestByName='+@NM_PENGAJUAN+'
					&RequestByEmail='+@RequestEmail+'
					&ApprovedBy='+@EmailReplace+'
					&ApprovedByName='+@Users_approved+'
					&ApprovedByEmail='+@EmailReplace+'  
					&ApprovalStatus=UNPROCESSED  
					&ApprovalNote=UNPROCESSED 
					&ApprovalNeeded=1
					&Priority='+@Priority+'
					&AddInfo1=Kategori
					&AddInfo1Value='+@KD_JENISBI+'
					&AddInfo2=Divisi
					&AddInfo2Value='+@KD_MERKBI+'
					&AddInfo8=Catatan
					&AddInfo8Value='+@KETERANGAN+'
					&BhaktiFlag=UNPROCESSED  
					&BhaktiProcessDate='+@TGL_PENGAJUAN+'
					&IsCancelled=0  
					&LocationCode=HO
					&IsEmailed=0
					&EmailedDate='+@TGL_PENGAJUAN

				--SET @mcUrl = 'http://10.1.0.32/myCompany/' --nanti di komen,cuma buat test
				SET @Urls = @mcUrl + 'api/approval/insert' + @bodyGet 
				EXEC sp_OACreate 'MSXML2.XMLHTTP', @Object OUT;
				EXEC sp_OAMethod @Object, 'open', NULL, 'post', @Urls, 'false'
				EXEC sp_OAMethod @Object, 'setRequestHeader', null, 'Content-Type', 'application/json'
				EXEC sp_OAMethod @Object, 'send', null, @bodyGet
				EXEC sp_OAMethod @Object, 'responseText', @ResponseText OUTPUT
				print @ResponseText
				SET @isSuccess = (CASE WHEN CHARINDEX('"pesan":"Request Ini Berhasil Diinsert"', isnull(@ResponseText,''))=0 THEN 0 ELSE 1 END) 
				EXEC sp_OADestroy @Object

				SET @@Body = '<h3>REQUEST PEMBELIAN BARANG IMPORT</h3><br/>'
				set @@Body = @@Body + 'NO REQUEST : <b>'+@Kd_Pengajuan+'</b><br/>'
				set @@Body = @@Body + 'JENIS : <b>'+@KD_JENISBI+'</b><br/>'
				set @@Body = @@Body + 'MERK : <b>'+@KD_MERKBI+'</b><br/><br/>'
				IF @STATUS<>'TAMBAH' and @Status<>'RUBAH' 
					and @Status<>'MANAGER APPROVED' and @Status<>'MANAGER REJECTED'
					AND @Status<>'WAITING FOR GM APPROVAL'
				BEGIN
				set @@Body = @@Body + 'PENGIRIMAN VIA : <b>'+@KD_PENGIRIMANBI +'</b><br/>'
				set @@Body = @@Body + 'JENIS PENGIRIMAN : <b>'+@JENIS_PENGIRIMAN +'</b><br/><br/>'
				END
				set @@Body = @@Body + 'TGL DIPERLUKAN : <b>'+@TGL_DIBUTUHKAN+'</b><br/>'
				set @@Body = @@Body + 'KETERANGAN : <br><b>'+replace(replace(@KETERANGAN,char(10),'<br>'),char(13),'')+'</b></br><br/>'
				
				SET @@Body = @@Body + '<table>'
				SET @@Body = @@Body + '<tr style=''background-color:#e1f5a4;''>'
				SET @@Body = @@Body + '<td width=''5%'' style=''border:1px solid #ccc''>No</td>'
				SET @@Body = @@Body + '<td width=''15%'' style=''border:1px solid #ccc''>Kd Brg</td>'
				IF @KD_JENISBI in ('PRODUK','SPAREPART')
				BEGIN
				SET @@Body = @@Body + '<td width=''25%'' style=''border:1px solid #ccc''>Nm Brg</td>'
				END
				SET @@Body = @@Body + '<td width=''15%'' style=''border:1px solid #ccc''>QTY</td>'
				SET @@Body = @@Body + '</tr>'

				--SELECT 1 as Posisi, @@Body as BodyEmail	

				DECLARE @KD_BRG VARCHAR(255),@NM_BRG VARCHAR(1000), @QTY INT, @NO INT
				SET @NO = 1 
				
				SET @db_cursor = CURSOR FOR
					SELECT KD_BRG, QTY
					FROM TBLPENGAJUANPEMBELIANDT
					WHERE KD_PENGAJUAN = @KD_PENGAJUAN 
				OPEN @db_cursor  
				FETCH NEXT FROM @db_cursor INTO @KD_BRG, @QTY
				WHILE @@FETCH_STATUS = 0   
				BEGIN   
					IF @KD_JENISBI = 'PRODUK' 
					BEGIN 
						SELECT @NM_BRG = rtrim(NM_BRG) FROM TblINHeader WHERE LTRIM(RTRIM(KD_BRG))=@KD_BRG 
					END
					ELSE IF @KD_JENISBI = 'SPAREPART' 
					BEGIN
						SELECT @NM_BRG = rtrim(NM_SPAREPART) FROM TblHeaderInSp WHERE LTRIM(RTRIM(kd_sparepart))=@KD_BRG
					END
					ELSE
					BEGIN
						SET @NM_BRG = ''
					END 
					
					SET @@Body = @@Body + '<tr>'
					SET @@Body = @@Body + '<td width=''5%'' style=''border:1px solid #ccc''>'+cast(@NO as varchar(5))+'</td>'
					SET @@Body = @@Body + '<td width=''15%'' style=''border:1px solid #ccc''>'+@KD_BRG+'</td>'
					IF @KD_JENISBI in ('PRODUK','SPAREPART')
					BEGIN
					SET @@Body = @@Body + '<td width=''25%'' style=''border:1px solid #ccc''>'+@NM_BRG+'</td>'
					END
					SET @@Body = @@Body + '<td width=''15%'' style=''border:1px solid #ccc''>'+CAST(@QTY as VARCHAR(10))+'</td>'
					SET @@Body = @@Body + '</tr>'
					
					SET @NO =@NO+1
					FETCH NEXT FROM @db_cursor INTO @KD_BRG, @QTY
				END   
				CLOSE @db_cursor   
				DEALLOCATE @db_cursor				
				--SELECT 2 as Posisi, @@Body as BodyEmail	
				
				SET @@Body = @@Body + '</table><br/>'
				SET @@Body = @@Body + '<style>'
				SET @@Body = @@Body + '.btn { '
				SET @@Body = @@Body + 'float:left;margin-right:30px;border:1px solid #ccc;border-radius:10px;color:white;cursor:pointer;'
				SET @@Body = @@Body + 'font-size:20px;line-height:30px;vertical-align:middle;width:200px;height:30px;padding:5px;text-align:center;'
				SET @@Body = @@Body + '}'
				SET @@Body = @@Body + '.btn:hover { '
				SET @@Body = @@Body + 'color:yellow;font-weight:bold;'
				SET @@Body = @@Body + '}'
				SET @@Body = @@Body + '</style>'
				
				DECLARE @URL as VARCHAR(1000)
				SELECT @URL = myCompany_URL FROM TblConfig 
				SET @URL = @URL+'PembelianImportApproval'
				
				--IF @Status IN ('TAMBAH','RUBAH','WAITING FOR GM APPROVAL')
				--BEGIN

				--	IF @KD_JENISBI NOT IN ('PRODUK','SPAREPART')
				--	BEGIN
				--		SET @@Body = @@Body + '<a href='''+@URL+'/ApproveRequest?api=APITES&kdreq='+SQLHTTP.net.UrlEncode(@Kd_Pengajuan)+'&empid='+SQLHTTP.net.UrlEncode(@@Atasan)+'&gm=2''>'
				--		SET @@Body = @@Body + '<div class=''btn'' style=''background-color:#0a7313;''>APPROVE</div></a>'
				--		SET @@Body = @@Body + '<a href='''+@URL+'/RejectRequest?api=APITES&kdreq='+SQLHTTP.net.UrlEncode(@Kd_Pengajuan)+'&empid='+SQLHTTP.net.UrlEncode(@@Atasan)+'&gm=2''>'
				--		SET @@Body = @@Body + '<div class=''btn'' style=''background-color:#7a0202;''>REJECT</div></a>'
				--		SET @@Body = @@Body + '<div style=''clear:both;''></div>'								
				--	END				
				--	ELSE IF (@STATUS = 'TAMBAH' OR @STATUS = 'RUBAH')
				--	BEGIN
				--		SET @@Body = @@Body + '<a href='''+@URL+'/ApproveRequest?api=APITES&kdreq='+SQLHTTP.net.UrlEncode(@Kd_Pengajuan)+'&empid='+SQLHTTP.net.UrlEncode(@@Atasan)+'&gm=0''>'
				--		SET @@Body = @@Body + '<div class=''btn'' style=''background-color:#0a7313;''>APPROVE</div></a>'
				--		SET @@Body = @@Body + '<a href='''+@URL+'/RejectRequest?api=APITES&kdreq='+SQLHTTP.net.UrlEncode(@Kd_Pengajuan)+'&empid='+SQLHTTP.net.UrlEncode(@@Atasan)+'&gm=0''>'
				--		SET @@Body = @@Body + '<div class=''btn'' style=''background-color:#7a0202;''>REJECT</div></a>'
				--		SET @@Body = @@Body + '<div style=''clear:both;''></div>'
				--	END
				--	ELSE 
				--	BEGIN
				--		SET @@Body = @@Body + '<a href='''+@URL+'/ApproveRequest?api=APITES&kdreq='+SQLHTTP.net.UrlEncode(@Kd_Pengajuan)+'&empid='+SQLHTTP.net.UrlEncode(@@Atasan)+'&gm=1''>'
				--		SET @@Body = @@Body + '<div class=''btn'' style=''background-color:#0a7313;''>APPROVE</div></a>'
				--		SET @@Body = @@Body + '<a href='''+@URL+'/RejectRequest?api=APITES&kdreq='+SQLHTTP.net.UrlEncode(@Kd_Pengajuan)+'&empid='+SQLHTTP.net.UrlEncode(@@Atasan)+'&gm=1''>'
				--		SET @@Body = @@Body + '<div class=''btn'' style=''background-color:#7a0202;''>REJECT</div></a>'
				--		SET @@Body = @@Body + '<div style=''clear:both;''></div>'				
				--	END
				
				--END
				
				SET @@Body = @@Body + '----<br/>'
				SET @@Body = @@Body + 'Log Request Pembelian Import<br/>'
				SET @@Body = @@Body + 'Request Oleh: <b>'+@NM_PENGAJUAN+' ['+@TGL_PENGAJUAN+']</b><br/>'
				IF @APPROVALMANAGER_BY<>''
				SET @@Body = @@Body + 'Approval Manager Oleh: <b>'+@APPROVALMANAGER_BY+' ['+@APPROVALMANAGER_DATE+']</b><br/>'								
				IF @APPROVALGM_BY<>'' and (@APPROVALGM_BY<>@APPROVALMANAGER_BY OR @APPROVALGM_DATE<>@APPROVALMANAGER_DATE)
				SET @@Body = @@Body + 'Approval GM Oleh: <b>'+@APPROVALGM_BY+' ['+@APPROVALGM_DATE+']</b><br/>'
				
				IF @APPROVALPURCHASING_BY <>'' AND @STATUS='PURCHASING APPROVED'
				SET @@Body = @@Body + 'Approval Div.Purchasing Oleh: <b>'+@APPROVALPURCHASING_BY+' ['+@APPROVALPURCHASING_DATE+']</b><br/>'
				
				IF @CANCELLED_BY<>''
				BEGIN
				SET @@Body = @@Body + 'Request Batal oleh : <b>'+@CANCELLED_BY+' ['+@CANCELLED_DATE+']</b><br/>'
				SET @@Body = @@Body + 'Keterangan : <b>'+@KET_CANCELLED+'</b><br/>'
				END
				IF @APPROVALPURCHASING_BY <>'' AND @STATUS='REQUEST CANCEL APPROVED'
				BEGIN
				SET @@Body = @@Body + 'Request Batal disetujui Oleh: <b>'+@APPROVALPURCHASING_BY+' ['+@APPROVALPURCHASING_DATE+']</b><br/>'
				END			
				IF @APPROVALPURCHASING_BY <>'' AND @STATUS='REQUEST CANCEL REJECTED'
				BEGIN
				SET @@Body = @@Body + 'Request Batal ditolak Oleh: <b>'+@APPROVALPURCHASING_BY+' ['+@APPROVALPURCHASING_DATE+']</b><br/>'
				END		
				
				SET @@Emails = '['+@@Emails+']'

				SELECT @@Emails AS Email5
				
				--SET @@Emails = '"testprogrambit@gmail.com"'
				--EXEC SP_SendEmail '"testprogrambit@gmail.com"', '"itdev.dist@bhakti.co.id"', 'tes', 'test' 

				EXEC SP_SendEmail @@Emails, '', @@Subject, @@Body 

				--SELECT 4 as Posisi, @@Body as BodyEmail	

			END
					
			COMMIT TRANSACTION
		END TRY
		BEGIN CATCH
			ROLLBACK TRANSACTION
			SELECT @MSG = ERROR_MESSAGE() 
			RAISERROR(@MSG, 18,10)
		END CATCH
	END
END
