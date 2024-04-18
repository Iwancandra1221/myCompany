USE [BHAKTI]
GO
/****** Object:  Table [dbo].[TblPPNMasukanv2]    Script Date: 2023-10-12 4:55:08 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER TABLE [dbo].[TblPPNMasukanv2](
	[nourut] [numeric](18, 0) IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[no_transaksi] [nvarchar](50) NOT NULL,
	[tgl_transaksi] [datetime] NOT NULL,
	[no_tagihan] [nvarchar](50) NULL,
	[IsPotongPPH] [nvarchar](1) NULL,
	[tgl_tagihan] [datetime] NULL,
	[IsSupplier] [nvarchar](1) NULL,
	[IsCustomer] [nvarchar](1) NULL,
	[KodeSupplier] [nvarchar](50) NULL,
	[KodeCustomer] [nchar](10) NULL,
	[kd_plg] [nchar](10) NULL,
	[nama_pemberi_jasa] [nchar](50) NULL,
	[detail_suplcust] [text] NULL,
	[cabang] [nvarchar](max) NULL,
	[NamaJasaKenaPajak] [nvarchar](max) NULL,
	[bulanpotongpph] [float] NULL,
	[tahunpotongpph] [float] NULL,
	[kode_objek_pajak] [nvarchar](50) NULL,
	[nama_objek_pajak] [nvarchar](max) NULL,
	[DPPPemotongan] [float] NULL,
	[IsPPH23] [nvarchar](1) NULL,
	[IsPPH4] [nvarchar](1) NULL,
	[IsPPH21] [nvarchar](1) NULL,
	[IsPPH15] [nvarchar](1) NULL,
	[TarifPPH] [float] NULL,
	[JumlahPotong] [float] NULL,
	[keterangan] [nvarchar](100) NULL,
	[no_skb] [nvarchar](100) NULL,
	[kategori_biaya] [nvarchar](100) NULL,
	[kategori] [nvarchar](100) NULL,
	[IsFakturPajak] [nvarchar](1) NULL,
	[NoFP] [nvarchar](50) NULL,
	[Tanggal] [datetime] NULL,
	[BulanMasaPajak] [float] NULL,
	[TahunMasaPajak] [float] NULL,
	[statusdikreditkan] [nvarchar](1) NULL,
	[DPP] [float] NULL,
	[PPN_Persen] [float] NULL,
	[PPN] [float] NULL,
	[Total] [float] NULL,
	[TglNK] [datetime] NULL,
	[NoBuktiPotong] [nvarchar](50) NULL,
	[TglPotong] [nvarchar](50) NULL,
	[OtomatisCreateNK] [nvarchar](1) NULL,
	[TipeNK] [nvarchar](50) NULL,
	[NoNK] [nvarchar](50) NULL,
	[TotalNK] [float] NULL,
	[UserInput] [nvarchar](50) NULL,
	[TglInput] [datetime] NULL,
	[UserEdit] [nvarchar](50) NULL,
	[TglEdit] [datetime] NULL,
	[KategoriNK] [nvarchar](250) NULL,
	[No_Ref] [varchar](50) NULL,
	[Kd_Lokasi] [varchar](3) NULL,
	[Divisi] [nvarchar](250) NULL,
	[TarifManual] [nvarchar](1) NULL,
	[NamaJasaKenaPajak_fp] [text] NULL,
	[TarifPPH2] [float] NULL,
	[NPWP] [nvarchar](255) NULL,
 CONSTRAINT [PK_TblPPNMasukanv2] PRIMARY KEY CLUSTERED 
(
	[nourut] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
