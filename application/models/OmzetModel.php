<?php
	Class OmzetModel extends CI_Model
	{
		function CheckStatusOmzet($th, $bl, $param){
			$str = "Select * 
					From TblOmzetNasional Where Tahun=".$th." and Bulan=".$bl." and KategoriBrg='".$param["KATEGORI_BRG"]."' 
						and Wilayah='".$param["WILAYAH"]."' and Divisi='".$param["DIVISI"]."' and Merk='".$param["MERK"]."' and KdLokasi='".$param["KD_LOKASI"]."'";
		    $res = $this->db->query($str);
		    if ($res->num_rows()==0)
		    	return "NEW";
		    else {
		    	$status = $res->row()->IsLocked;
		    	if ($status==0) 
		    		return "SAVED";
		    	else
		    		return "SAVED AND LOCKED";
		    }
		}

		function DelDataOmzet($kategori, $wilayah, $th, $bl, $kdlokasi="") {
			$str = "DELETE FROM TblOmzetNasional 
					WHERE Tahun=".$th." and Bulan=".$bl." and KategoriBrg='".$kategori."' and Wilayah='".$wilayah."'
						and KdLokasi='".$kdlokasi."'";
		    $this->db->query($str);				
		    //die("deleted");
		    return true;
		}

		function SaveDataOmzet($th, $bl, $param, $user){
			//echo(json_encode($param)."<br><br>");
			$str = "INSERT INTO TblOmzetNasional (Tahun, Bulan, KdLokasi, KategoriBrg, Wilayah, Divisi, Merk, 
						TotalJual, TotalReturBagus, TotalReturCacat, TotalDisc, OmzetNetto, CreatedBy, CreatedDate) 
					Select ".$th.", ".$bl.",'".$param["KD_LOKASI"]."','".$param["KATEGORI_BRG"]."','".$param["WILAYAH"]."','".$param["DIVISI"]."','".$param["MERK"]."',".
						$param["TOTAL_JUAL"].",".$param["TOTAL_RETURB"].",".$param["TOTAL_RETURC"].",".$param["TOTAL_DISC"].",".$param["OMZET_NETTO"].
						",'".$user."', Getdate() ";
			//if ($param["WILAYAH"]=="MODERN OUTLET") die($str);
		    $this->db->query($str);
		    return true;
		}

		function OmzetBulanan_GetListWilayah($kategori, $th, $bl){

			$str = "SELECT distinct isnull(b.WilayahGroup,a.Wilayah) as Wilayah
					FROM TblOmzetNasional a left join TblConfigReport_Wilayah b on a.Wilayah=b.Wilayah
					Where Tahun=".$th." and Bulan=".(($bl==0)?"Bulan":$bl)." and KategoriBrg='".$kategori."'
					ORDER BY isnull(b.WilayahGroup,a.Wilayah)";
		    $res = $this->db->query($str);
		    if ($res->num_rows()>0) {
		    	return $res->result();
		    } else {
		    	return array();
		    }
		}

		function OmzetBulanan_Gets($kategori, $th, $bl) 
		{
			$str = "SELECT isnull(b.WilayahGroup,a.Wilayah) as Wilayah, a.Divisi, a.Merk,
						SUM(a.TotalJual) as TotalJual, SUM(a.TotalReturBagus) as TotalReturBagus,
						SUM(a.TotalReturCacat) as TotalReturCacat, SUM(a.TotalDisc) as TotalDisc,
						SUM(a.OmzetNetto) as OmzetNetto
					FROM TblOmzetNasional a left join (select * from TblConfigReport_Wilayah where ReportOpt='OMZET NASIONAL') b on a.Wilayah=b.Wilayah 
					Where Tahun=".$th." and Bulan=".(($bl==0)?"Bulan":$bl)." and KategoriBrg='".$kategori."'
					GROUP BY Divisi, Merk, isnull(b.WilayahGroup,a.Wilayah)
					ORDER BY Divisi, Merk, isnull(b.WilayahGroup,a.Wilayah)";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}
		
		function OmzetBulanan_OmzetDivisiMerk($kategori, $th, $bl) 
		{
			$str = "SELECT Divisi, Merk, SUM(OmzetNetto) as TotalOmzetNetto
					FROM TblOmzetNasional 
					Where Tahun=".$th." and Bulan=".(($bl==0)?"Bulan":$bl)." and KategoriBrg='".$kategori."'
					GROUP BY Divisi, Merk
					ORDER BY Divisi, Merk";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}

		function OmzetBulanan_OmzetWilayah($kategori, $th, $bl) 
		{
			$str = "SELECT isnull(b.WilayahGroup,a.Wilayah) as Wilayah, SUM(TotalJual) as TotalJual, SUM(TotalReturBagus) as TotalReturBagus,
						SUM(TotalReturCacat) as TotalReturCacat, SUM(TotalDisc) as TotalDisc, Sum(OmzetNetto) as OmzetNetto
					FROM TblOmzetNasional a left join (select * from TblConfigReport_Wilayah where ReportOpt='OMZET NASIONAL') b on a.Wilayah=b.Wilayah 
					Where Tahun=".$th." and Bulan=".(($bl==0)?"Bulan":$bl)." and KategoriBrg='".$kategori."'
					GROUP BY isnull(b.WilayahGroup,a.Wilayah)
					ORDER BY isnull(b.WilayahGroup,a.Wilayah)";
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}

		function Summary($th, $bl) 
		{
			$str = "SELECT (Case a.KdLokasi when 'BOG' then 'BOGOR' when 'KRW' then 'KARAWANG' else c.NamaDb end) as Wilayah,
						MAX(a.CreatedBy) as CreatedBy, convert(varchar(50),MAX(a.CreatedDate),113) as CreatedDate, 
						MAX(a.IsLocked) as IsLocked, MAX(a.LockedBy) as LockedBy, convert(varchar(20),MAX(a.LockedDate),106) as LockedDate
					FROM TblOmzetNasional a left join (select * from TblConfigReport_Wilayah where ReportOpt='OMZET NASIONAL') b on a.Wilayah=b.Wilayah 
						left join MsDatabase c on (case a.KdLokasi when 'DMI' then 'JKT' else a.KdLokasi end)=c.BranchId
					Where Tahun=".$th." and Bulan=".(($bl==0)?"Bulan":$bl)."
					GROUP BY (Case a.KdLokasi when 'BOG' then 'BOGOR' when 'KRW' then 'KARAWANG' else c.NamaDb end)
					ORDER BY (Case a.KdLokasi when 'BOG' then 'BOGOR' when 'KRW' then 'KARAWANG' else c.NamaDb end)";
			//die($str);
			$res = $this->db->query($str);
			if ($res->num_rows()>0) {
				return $res->result();
			} else {
				return array();
			}
		}		

		function LaporanOmzetNational($report){
			// print_r($report);die();
			$namaBulan = [
			    1 => "Januari",
			    2 => "Februari",
			    3 => "Maret",
			    4 => "April",
			    5 => "Mei",
			    6 => "Juni",
			    7 => "Juli",
			    8 => "Agustus",
			    9 => "September",
			    10 => "Oktober",
			    11 => "November",
			    12 => "Desember"
			];

			$data_report = array();

			$data_report['TahunAwal'] = $report['year_start'];
			$data_report['TahunAkhir'] = $report['year_end'];
			$data_report['TipePPN'] = $report['nama_ppn'];
			$data_report['Judul'] = strtoupper($report['judul']);

			    $jmlhDataAkhir = 0;
			    $totalPerBulanAkhir = array();
			    $totalPeriodeAwal = 0;
			    $totalPeriodeAkhir = 0;
				
				$data=$report['report'];
					
			    foreach($data as $keyDiv => $bypriode){

				        $jmlhDataAkhir++;

				        $dataPeriode = $bypriode['periode'];

				            if($dataPeriode!=''){
				                $totalPeriodeAwal = 0;
				                $totalPeriodeAkhir = 0;
				                $jmlhDataAwal = 0;
				                $jmlhDataAkhir = 0;

				                foreach($namaBulan as $keyBulan => $valueBulan){

				                    $index = ($keyBulan-1);

				                    if(isset($dataPeriode[$index])){

				                        $dppStart = floatval($dataPeriode[$index]['dpp_start']);
				                        $dppEnd = floatval($dataPeriode[$index]['dpp_end']);

				                        if($dppStart!==0 && $dppStart!=''){
				                             $jmlhDataAwal+=1;
				                        }
				                        if($dppEnd!==0 && $dppEnd !='' ){
				                            $jmlhDataAkhir+=1;
				                        }

				                        if(empty($data_report['data'][$keyBulan]['TotalPeriodeAwal'])){
				                            $data_report['data'][$keyBulan]['TotalPeriodeAwal'] = 0;
				                        }

				                        if(empty($data_report['data'][$keyBulan]['TotalPeriodeAkhir'])){
				                            $data_report['data'][$keyBulan]['TotalPeriodeAkhir'] = 0;
				                        }

				                        $data_report['data'][$keyBulan]['TotalPeriodeAwal'] += $dppStart;
				                        $data_report['data'][$keyBulan]['TotalPeriodeAkhir'] += $dppEnd;

				                        $totalPeriodeAwal += $dppStart;
				                        $totalPeriodeAkhir += $dppEnd;

				                    }

				                

				            }
				            
			        }
			    }

			    $jum_Awal=0;
			    $jum_Akhir=0;
			    $total_Awal = 0;
			    $total_Akhir = 0;
			    for($i=1; $i<=12; $i++){

					$totalPeriodeAwal   = $data_report['data'][$i]['TotalPeriodeAwal'];
					$totalPeriodeAkhir  = $data_report['data'][$i]['TotalPeriodeAkhir'];

					if($totalPeriodeAwal>0){
						$jum_Awal++;
					}

					if($totalPeriodeAkhir>0){
						$jum_Akhir++;
					}

					$total_Awal += $totalPeriodeAwal;
			   		$total_Akhir += $totalPeriodeAkhir;
					
					$persentase =  ceil((($totalPeriodeAkhir/$totalPeriodeAwal)-1)*100).'%';

					if(empty($data_report['data'][$i]['Persentase'])){
						$data_report['data'][$i]['Persentase'] = 0;
					}

					$data_report['data'][$i]['Selisih']     = $totalPeriodeAkhir-$totalPeriodeAwal;
					$data_report['data'][$i]['Persentase']  = $persentase;
			            
				}
				
				$avgAwal = $total_Awal/$jum_Awal;
				$avgAkhir = $total_Akhir/$jum_Akhir;

				$persentaseAvg = round((($avgAkhir/$avgAwal)-1)*100, 1).'%';

				$data_report['avgAwal']    = $avgAwal;
				$data_report['avgAkhir']    = $avgAkhir;
				$data_report['SelisihAvg']    = $avgAkhir-$avgAwal;
				$data_report['persentaseAvg']    = $persentaseAvg;

			return $data_report;
		}
	}
?>