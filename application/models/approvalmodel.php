<?php
	class approvalmodel extends CI_Model
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('UserModel');
            $this->allow_dev_approve = true;
		}
		
        function getconfigapproval($amount,$AddInfo1Value,$AddInfo2Value,$AddInfo3Value,$branchID)
        {
            $qry = " SELECT top 1 ConfigID from Ms_ConfigApprovalHD a
                   WHERE a.EventID = 'CREDIT LIMIT' 
                   AND (a.AddInfo1 = '".$AddInfo1Value."' OR a.AddInfo2 = '".$AddInfo1Value."' OR a.AddInfo3 = '".$AddInfo1Value."')  
                   AND (a.AddInfo1 = '".$AddInfo2Value."' OR a.AddInfo2 = '".$AddInfo2Value."' OR a.AddInfo3 = '".$AddInfo2Value."')  
                   AND (a.AddInfo1 = '".$AddInfo3Value."' OR a.AddInfo2 = '".$AddInfo3Value."' OR a.AddInfo3 = '".$AddInfo3Value."')  
                   AND IsActive = 1 AND   a.BranchID = '".$branchID."' 
                   AND getdate()>= ActiveDate  
                   ORDER BY ActiveDate desc ";  
           $res = $this->db->query($qry);
           if ($res->num_rows()>0) 
           {
               $qry = " SELECT * FROM Ms_ConfigApprovalDT b 
                   WHERE ConfigID = '".$res->row()->ConfigID."' AND (".$amount." >= MinAmount or MinAmount = 0) 
                   AND (".$amount." < MaxAmount OR MaxAmount = 0 Or MaxAMount = ".$amount.") "; 
   
               $res = $this->db->query($qry);
               if ($res->num_rows()>0) 
                   return $res->result();
               else
                   return array();
           }
           else
               return array();  
        }

        function getexpiredate($post)
        {
            $qry = "select ConfigValue as ExpiryDate from Ms_Config 
                   WHERE ConfigType='".$post['ApprovalType']."'"; 

            $res = $this->db->query($qry);
            if ($res->num_rows()>0){
                return $res->result();
            } else {
                return null;
            }

        }

        function updateapproval($a,$b,$c,$d){
            $this->db->where('ApprovalType','PURCHASE IMPORT');
            $this->db->where('RequestNo',$a);

            if($c=='0' && $d=='approval'){
                $this->db->set('BhaktiFlag','WAITING FOR GM APPROVAL');
                $this->db->set('ApprovalStatus','WAITING FOR GM APPROVAL');
                $this->db->set('ApprovalNote','WAITING FOR GM APPROVAL');
            }else if($c=='1' && $d=='approval'){
                $this->db->set('BhaktiFlag','APPROVED');
                $this->db->set('ApprovalStatus','APPROVED');
                $this->db->set('ApprovalNote','APPROVED');
            }

            if($d=='cancel'){
                $this->db->set('BhaktiFlag','REJECTED');
                $this->db->set('ApprovalStatus','REJECTED');
                $this->db->set('ApprovalNote','REJECTED');
            }

            $this->db->update('TblApproval');
        }

        function simpan($post, $listapprovers)
        {
            $ERR_MSG='';

            $this->db->trans_begin();

            for($i=0; $i<count($listapprovers); $i++) {
                $this->db->set('ApprovalType',$post['ApprovalType']);
                $this->db->set('RequestNo',$post['RequestNo']);
                $this->db->set('RequestBy',$post['RequestBy']);
                $this->db->set('RequestDate',$post['RequestDate']);
                $this->db->set('RequestByName',$post['RequestByName']);
                $this->db->set('RequestByEmail',$post['RequestByEmail']);
                $this->db->set('ApprovedBy',$post['ApprovedBy']);
                $this->db->set('ApprovedByName',$post['ApprovedByName']);
                $this->db->set('ApprovedByEmail',$post['ApprovedByEmail']);
                $this->db->set('ApprovedDate',(ISSET($post['ApprovedDate'])?$post['ApprovedDate']:NULL));
                $this->db->set('ApprovalStatus',$post['ApprovalStatus']);
                $this->db->set('ApprovalNote',(ISSET($post['ApprovalNote'])?$post['ApprovalNote']:NULL));
                $this->db->set('AddInfo1',$post['AddInfo1']);
                $this->db->set('AddInfo1Value',$post['AddInfo1Value']);
                $this->db->set('AddInfo2',$post['AddInfo2']);
                $this->db->set('AddInfo2Value',$post['AddInfo2Value']);
                $this->db->set('AddInfo3',$post['AddInfo3']);
                $this->db->set('AddInfo3Value',$post['AddInfo3Value']);
                $this->db->set('AddInfo4',$post['AddInfo4']);
                $this->db->set('AddInfo4Value',$post['AddInfo4Value']);
                $this->db->set('AddInfo5',$post['AddInfo5']);
                $this->db->set('AddInfo5Value',$post['AddInfo5Value']);
                $this->db->set('AddInfo6',$post['AddInfo6']);
                $this->db->set('AddInfo6Value',$post['AddInfo6Value']);
                $this->db->set('AddInfo7',$post['AddInfo7']);
                $this->db->set('AddInfo7Value',$post['AddInfo7Value']);
                $this->db->set('AddInfo8',$post['AddInfo8']);
                $this->db->set('AddInfo8Value',$post['AddInfo8Value']);
                $this->db->set('AddInfo9',$post['AddInfo9']);
                $this->db->set('AddInfo9Value',$post['AddInfo9Value']);
                $this->db->set('AddInfo10',$post['AddInfo10']);
                $this->db->set('AddInfo10Value',$post['AddInfo10Value']);
                $this->db->set('AddInfo11',$post['AddInfo11']);
                $this->db->set('AddInfo11Value',$post['AddInfo11Value']);
                $this->db->set('AddInfo12',$post['AddInfo12']);
                $this->db->set('AddInfo12Value',$post['AddInfo12Value']);
                $this->db->set('ApprovalNeeded',$post['ApprovalNeeded']);
                $this->db->set('Priority',$post['Priority']);
                $this->db->set('ExpiryDate',$post['ExpiryDate']);
                $this->db->set('BhaktiFlag',$post['BhaktiFlag']);
                $this->db->set('BhaktiProcessDate',(ISSET($post['BhaktiProcessDate'])?$post['BhaktiProcessDate']:NULL));
                $this->db->set('IsCancelled',$post['IsCancelled']);
                $this->db->set('CancelledBy',(ISSET($post['CancelledBy'])?$post['CancelledBy']:NULL));
                $this->db->set('CancelledByName',(ISSET($post['CancelledByName'])?$post['CancelledByName']:NULL));
                $this->db->set('CancelledDate',(ISSET($post['CancelledDate'])?$post['CancelledDate']:NULL));
                $this->db->set('CancelledNote',(ISSET($post['CancelledNote'])?$post['CancelledNote']:NULL));
                $this->db->set('CancelledByEmail',(ISSET($post['CancelledByEmail'])?$post['CancelledByEmail']:NULL));
                $this->db->set('LocationCode',$post['LocationCode']);
                $this->db->set('IsEmailed',$post['IsEmailed']);
                $this->db->set('EmailedDate',(ISSET($post['EmailedDate'])?$post['EmailedDate']:NULL));

                $this->db->insert('TblApproval');

                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        $ERR_CODE = $error["code"];
                        $ERR_MSG.= "message: ".$error[ 'message']." ";
                    }
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
 			}
			else{
                $this->db->trans_rollback();
                return false;
 			}	
        }

        //approval type dan request id
        function get($post)
        {
            $qry = "SELECT * 
                    FROM TblApproval 
                    WHERE ApprovalType = '".$post['ApprovalType']."' 
                    AND (
                        RequestNo = '".$post['RequestNo']."' 
                        OR RequestNo = REPLACE(REPLACE('".$post['RequestNo']."' ,'-',''),'_','')
                    ) 
                    ORDER BY [priority]";

            $res = $this->db->query($qry);
            if ($res->num_rows()>0) 
                return $res->result();
            else
                return array();
        }

        function getbyapprover($post)
        {
            $qry = "SELECT * 
                    FROM TblApproval 
                    WHERE ApprovalType = '".$post['ApprovalType']."' 
                    AND [Priority] = ".$post['Priority']. "
                    AND (
                        RequestNo = '".$post['RequestNo']."' 
                        OR RequestNo = REPLACE(REPLACE('".$post['RequestNo']."' ,'-',''),'_','')
                    ) 
                    AND (ApprovedBy='".$post['ApprovedBy']."' or ApprovedByEmail='".$post['ApprovedBy']."'
                        or ApprovedByEmail='".$post["ApprovedByEmail"]."')
                    AND (ExpiryDate is null or ExpiryDate>=cast(GETDATE() as date))
                    ORDER BY [Priority]";
                // die($qry);
            $res = $this->db->query($qry);
            if ($res->num_rows()>0) 
                return $res->result();
            else
                return array();
        }

        function getandcheckapproval($post)
        {
            $qry = "

            Select a.ApprovalType as RequestType, a.RequestNo, a.RequestByName, MIN(a.RequestDate) as RequestDate, 
            a.AddInfo1Value as KodePelanggan, a.AddInfo2Value as Divisi, CAST(a.AddInfo3Value as MONEY) as CreditLimitBaru,
            a.AddInfo6Value as NamaPelanggan, CAST(a.AddInfo7Value as MONEY) as CreditLimitPermanent, 
            a.AddInfo8Value as Catatan, a.AddInfo9Value as Wilayah, 
            CAST(a.AddInfo11Value as MONEY) as CreditLimitTemporary, CAST(a.AddInfo12Value as MONEY) as KenaikanCL,  
            a.[Priority], a.ExpiryDate, a.ApprovalNeeded, sum(case when a.ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount, 
            (case when sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) > a.ApprovalNeeded then a.ApprovalNeeded 
                else sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) end) as ApprovedCount,
            sum(case when a.ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount,
            sum(case when a.IsCancelled=1 then 1 else 0 end) as CancelledCount
            FROM TblApproval a 
            WHERE ApprovalType = '".$post['ApprovalType']."' and RequestNo = '".$post['RequestNo']."'
                Group By a.ApprovalType, a.RequestNo, a.RequestByName,
                     a.AddInfo1Value, a.AddInfo2Value, CAST(a.AddInfo3Value as MONEY),
                     a.AddInfo6Value, CAST(a.AddInfo7Value as MONEY), a.AddInfo8Value, a.AddInfo9Value,
                     CAST(a.AddInfo11Value as MONEY), CAST(a.AddInfo12Value as MONEY), 
                     a.[Priority], a.ExpiryDate, a.ApprovalNeeded, a.ApprovedByEmail, a.AddInfo12Value, a.AddInfo4Value";
            // die($qry);

            $res = $this->db->query($qry);
            if ($res->num_rows()>0) 
                return $res->result();
            else
                return array();
        }

		function delete($post) 
        {
			$ERR_MSG = '';

            $this->db->trans_begin();
            $this->db->where('ApprovalType',$post['ApprovalType']);
            $this->db->where('RequestNo',$post['RequestNo']);
            $this->db->where('ApprovedBy',$post['ApprovedBy']); // Aliat - tambahan ApprovedBy supaya unik, karena 1 norequest bisa banyak level approval
            $this->db->where('Priority',(ISSET($post['Priority'])) ? $post['Priority'] : 1);
            $this->db->delete('TblApproval');

            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }
            // die($this->db->last_query());

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
 			}			
		}

        function approve($post)
        {

            $is_developer = 0;
            if (array_key_exists("role", $_SESSION)) 
            {
                $jml = count($_SESSION['role']);
                for($i=0;$i<$jml;$i++) {
                    if ($_SESSION['role'][$i]=="ROLE01"){
                        $is_developer = 1;
                    }
                }
            }

            $is_developer = 0;
            $ERR_MSG='';

            // die(json_encode($post));

            $this->db->trans_begin();
            
            if ($is_developer==0){   
                $this->db->where('ApprovalType',$post['ApprovalType']);
                $this->db->where('RequestNo',$post['RequestNo']);
                $this->db->where("(ApprovedBy = '".$post['ApprovedBy']."' or ApprovedBy='".$post['ApprovedByEmail']."' or ApprovedByEmail='".$post['ApprovedByEmail']."')");
                $this->db->where('Priority',(ISSET($post['Priority'])) ? $post['Priority'] : 1);
            } else {
                $this->db->where('ApprovalType',$post['ApprovalType']);
                $this->db->where('RequestNo',$post['RequestNo']);             
                $this->db->where('Priority',(ISSET($post['Priority'])) ? $post['Priority'] : 1);
            }
            $this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
            $this->db->set('ApprovalStatus','APPROVED');
            $this->db->set('ApprovalNote',(ISSET($post['ApprovalNote'])?$post['ApprovalNote']:NULL));
            $this->db->update('TblApproval');

            // die($this->db->last_query());

            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
 			}	
        }	

        function reject($post)
        {

            $is_developer = 0;
            if (array_key_exists("role", $_SESSION)) 
            {
                $jml = count($_SESSION['role']);
                for($i=0;$i<$jml;$i++) {
                    if ($_SESSION['role'][$i]=="ROLE01"){
                        $is_developer = 1;
                    }
                }
            }

            $ERR_MSG='';

            $this->db->trans_begin();

            if ($is_developer==0){   
                $this->db->where('ApprovalType',$post['ApprovalType']);
                $this->db->where('RequestNo',$post['RequestNo']);
                $this->db->where("(ApprovedBy = '".$post['ApprovedBy']."' or ApprovedBy='".$post['ApprovedByEmail']."' or ApprovedByEmail='".$post['ApprovedByEmail']."')");
            } else {
                $this->db->where('ApprovalType',$post['ApprovalType']);
                $this->db->where('RequestNo',$post['RequestNo']);             
            }

            $this->db->set('ApprovedDate',date('Y-m-d H:i:s'));
            $this->db->set('ApprovalStatus','REJECTED');
            $this->db->set('ApprovalNote',(ISSET($post['ApprovalNote'])?$post['ApprovalNote']:NULL));
            $this->db->update('TblApproval');
          
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
 			}	
        }	

        function cancel($post)
        {
            $ERR_MSG='';

            $this->db->trans_begin();
            $this->db->where('ApprovalType',$post['ApprovalType']);
            $this->db->where('RequestNo',$post['RequestNo']);
            $this->db->set('ApprovalStatus','CANCELLED');
            $this->db->set('IsCancelled',1);
            $this->db->set('CancelledBy',(ISSET($post['CancelledBy'])?$post['CancelledBy']:NULL));
            $this->db->set('CancelledByName',(ISSET($post['CancelledByName'])?$post['CancelledByName']:NULL));
            $this->db->set('CancelledDate',date('Y-m-d H:i:s'));
            $this->db->set('CancelledNote',(ISSET($post['CancelledNote'])?$post['CancelledNote']:NULL));
            $this->db->set('CancelledByEmail',(ISSET($post['CancelledByEmail'])?$post['CancelledByEmail']:NULL));
            $this->db->update('TblApproval');
            
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
			}
        }	

        function close($approvalType, $requestNo, $status, $info=array())
        {
            $ERR_MSG='';
            if ($status == "ON PROGRESS") {
                return false;
            }

            $this->db->trans_begin();
            $this->db->where('ApprovalType',$approvalType);
            $this->db->where('RequestNo',$requestNo);
            if ($status=="REJECTED") {
                $this->db->set('ApprovalStatus','REJECTED');                
            } else if ($status=="CANCELLED") {
                $this->db->set('ApprovalStatus','CANCELLED');
            } else if ($status=="CLOSED") {
                $this->db->set('ApprovalStatus','CLOSED');                                
            } else {
                $this->db->set('ApprovalStatus','APPROVED');
            } 

            if ($status == "CANCELLED") {
                $this->db->set('IsCancelled',1);
                $this->db->set('CancelledBy', $info["cancelledBy"]);
                $this->db->set('CancelledByName',$info["cancelledByName"]);
                $this->db->set('CancelledDate',date('Y-m-d H:i:s'));
                $this->db->set('CancelledNote',$info["cancelledNote"]);
                $this->db->set('CancelledByEmail',$info["cancelledByEmail"]);
            }
            $this->db->update('TblApproval');
            
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

            if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
            }
            else{
                $this->db->trans_rollback();
                return false;
            }
        }   

        //jika jumlah Approval di setiap [Priority] sudah sama dengan ApprovalNeeded, 
        //maka ubah BhaktiFlag ke PENDING (siap ditarik/diupdate ke Bhakti)
        function updatebhaktiflag($post)
        {
            $ERR_MSG='';

            $this->db->trans_begin();
            $this->db->where('ApprovalType',$post['ApprovalType']);
            $this->db->where('RequestNo',$post['RequestNo']);
            $this->db->set('BhaktiFlag','PENDING');
            $this->db->update('TblApproval');
            
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
			}
        }

        function insert($post)
        {

            $ERR_MSG='';
            $this->db->trans_begin();

            $this->db->set('ApprovalType',$post['ApprovalType']);
            $this->db->set('RequestNo',$post['RequestNo']);
            $this->db->set('RequestBy',$post['RequestBy']);
            $this->db->set('RequestDate',$post['RequestDate']);
            $this->db->set('RequestByName',$post['RequestByName']);
            $this->db->set('RequestByEmail',$post['RequestByEmail']);
            $this->db->set('ApprovedBy',$post['ApprovedBy']);
            $this->db->set('ApprovedByName',$post['ApprovedByName']);
            $this->db->set('ApprovedByEmail',$post['ApprovedByEmail']);
            $this->db->set('ApprovedDate',(ISSET($post['ApprovedDate'])?$post['ApprovedDate']:NULL));
            $this->db->set('ApprovalStatus',$post['ApprovalStatus']);
            $this->db->set('ApprovalNote',(ISSET($post['ApprovalNote'])?$post['ApprovalNote']:NULL));
            if(!empty($post['ApprovalNeeded'])){
                $this->db->set('ApprovalNeeded',$post['ApprovalNeeded']);
            }
            if(!empty($post['AddInfo1'])){
                $this->db->set('AddInfo1',$post['AddInfo1']);
                $this->db->set('AddInfo1Value',$post['AddInfo1Value']);
            }

            if(!empty($post['AddInfo2'])){
                $this->db->set('AddInfo2',$post['AddInfo2']);
                $this->db->set('AddInfo2Value',$post['AddInfo2Value']);
            }

            if(!empty($post['AddInfo3'])){
                $this->db->set('AddInfo3',$post['AddInfo3']);
                $this->db->set('AddInfo3Value',$post['AddInfo3Value']);
            }

            if(!empty($post['AddInfo4'])){
                $this->db->set('AddInfo4',$post['AddInfo4']);
                $this->db->set('AddInfo4Value',$post['AddInfo4Value']);
            }

            if(!empty($post['AddInfo5'])){
                $this->db->set('AddInfo5',$post['AddInfo5']);
                $this->db->set('AddInfo5Value',$post['AddInfo5Value']);
            }

            if(!empty($post['AddInfo6'])){
                $this->db->set('AddInfo6',$post['AddInfo6']);
                $this->db->set('AddInfo6Value',$post['AddInfo6Value']);
            }

            if(!empty($post['AddInfo7'])){
                $this->db->set('AddInfo7',$post['AddInfo7']);
                $this->db->set('AddInfo7Value',$post['AddInfo7Value']);
            }

            if(!empty($post['AddInfo8'])){
                $this->db->set('AddInfo8',$post['AddInfo8']);
                $this->db->set('AddInfo8Value',$post['AddInfo8Value']);
            }

            if(!empty($post['AddInfo9'])){
                $this->db->set('AddInfo9',$post['AddInfo9']);
                $this->db->set('AddInfo9Value',$post['AddInfo9Value']);
            }

            if(!empty($post['AddInfo10'])){
                $this->db->set('AddInfo10',$post['AddInfo10']);
                $this->db->set('AddInfo10Value',$post['AddInfo10Value']);
            }

            if(!empty($post['AddInfo11'])){
                $this->db->set('AddInfo11',$post['AddInfo11']);
                $this->db->set('AddInfo11Value',$post['AddInfo11Value']);
            }

            if(!empty($post['AddInfo12'])){
                $this->db->set('AddInfo12',$post['AddInfo12']);
                $this->db->set('AddInfo12Value',$post['AddInfo12Value']);
            }

            if(!empty($post['Priority'])){
                $this->db->set('Priority',$post['Priority']);
            }

            if(!empty($post['ExpiryDate'])){
                $this->db->set('ExpiryDate',$post['ExpiryDate']);
            }

            $this->db->set('BhaktiFlag',$post['BhaktiFlag']);

            if(!empty($post['BhaktiProcessDate'])){
                $this->db->set('BhaktiProcessDate',(ISSET($post['BhaktiProcessDate'])?$post['BhaktiProcessDate']:NULL));
            }

            $this->db->set('IsCancelled',$post['IsCancelled']);

            if(!empty($post['CancelledBy'])){
                $this->db->set('CancelledBy',(ISSET($post['CancelledBy'])?$post['CancelledBy']:NULL));
            }

            if(!empty($post['CancelledByName'])){
                $this->db->set('CancelledByName',(ISSET($post['CancelledByName'])?$post['CancelledByName']:NULL));
            }

            if(!empty($post['CancelledDate'])){
                $this->db->set('CancelledDate',(ISSET($post['CancelledDate'])?$post['CancelledDate']:NULL));
            }

            if(!empty($post['CancelledNote'])){
                $this->db->set('CancelledNote',(ISSET($post['CancelledNote'])?$post['CancelledNote']:NULL));
            }

            if(!empty($post['CancelledByEmail'])){
                $this->db->set('CancelledByEmail',(ISSET($post['CancelledByEmail'])?$post['CancelledByEmail']:NULL));
            }

            if(!empty($post['LocationCode'])){
                $this->db->set('LocationCode',$post['LocationCode']);
            }

            $this->db->set('IsEmailed',$post['IsEmailed']);

            if(!empty($post['EmailedDate'])){
                $this->db->set('EmailedDate',(ISSET($post['EmailedDate'])?$post['EmailedDate']:NULL));
            }
            // die($this->db->last_query());
            $this->db->insert('TblApproval');
            // die(".");
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_CODE = $error["code"];
                    $ERR_MSG.= "message: ".$error[ 'message']." ";
                }
            }
            // die("ERROR: ".$ERR_MSG);

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
 			}
			else{
                $this->db->trans_rollback();
                return false;
 			}	
        }
		
		function emailed($post)
        {
            $ERR_MSG='';

            $this->db->trans_begin();
            $this->db->where('ApprovalType',$post['ApprovalType']);
            $this->db->where('RequestNo',$post['RequestNo']);
            $this->db->where('ApprovedBy',$post['ApprovedBy']);
            $this->db->set('IsEmailed', true);
            $this->db->set('EmailedDate', date('Y-m-d H:i:s'));
            $this->db->update('TblApproval');
          
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
 			}	
        }


        function getpendingrequests($data)
        {

            $is_developer = 0;
            if (array_key_exists("role", $_SESSION)) 
            {
                $jml = count($_SESSION['role']);
                for($i=0;$i<$jml;$i++) {
                    if ($_SESSION['role'][$i]=="ROLE01"){
                        $is_developer = 1;
                    }
                }
            }

            $data_list=array();
            
            $page=0;
            if(!empty($data['iDisplayStart'])){
                $page=$data['iDisplayStart'];
            }


            $SortCol='a.RequestType, RequestDate';
            $SortDir='desc';


            if(!empty($data['iSortCol_0'])){
                if($data['iSortCol_0']==1){
                    $SortCol='a.RequestType';
                }else if($data['iSortCol_0']==2){
                    $SortCol='a.RequestNo';
                }else if($data['iSortCol_0']==3){
                    $SortCol='a.Wilayah';
                }
            }


            if(!empty($data['sSortDir_0'])){
                $SortDir=$data['sSortDir_0'];
            }



            $page=0;
            if(!empty($data['iDisplayStart'])){
                $page=$data['iDisplayStart'];
            }

            $total_data_view=10;
            if(!empty($data['iDisplayLength'])){
                $total_data_view=$data['iDisplayLength'];
            }


        

            $query = "Select DISTINCT a.*, b.Url
            FROM (
                Select DISTINCT a.ApprovalType as RequestType, a.RequestNo, a.RequestByName, MIN(a.RequestDate) as RequestDate, 
                a.AddInfo1Value as KodePelanggan, a.AddInfo2Value as Divisi, 
                a.AddInfo6Value as NamaPelanggan, a.AddInfo8Value as Catatan, a.AddInfo9Value as Wilayah,
                a.[Priority], a.ExpiryDate, a.ApprovalNeeded, sum(case when a.ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount, 
                (case when sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) > a.ApprovalNeeded then a.ApprovalNeeded 
                    else sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) end) as ApprovedCount,
                sum(case when a.ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount,
                a.AddInfo4Value 
                FROM TblApproval a 
                WHERE a.IsCancelled = 0 
                    AND (a.ExpiryDate is null or a.ExpiryDate>=CAST(GETDATE() as DATE))
                    ";

            if(!empty($data['sSearch'])){
                $query  .= " AND (a.ApprovalType LIKE '%".$data['sSearch']."%' OR a.RequestNo LIKE '%".$data['sSearch']."%' OR a.RequestByName LIKE '%".$data['sSearch']."%' OR a.RequestDate LIKE '%".$data['sSearch']."%' OR a.AddInfo1Value LIKE '%".$data['sSearch']."%' OR a.AddInfo2Value LIKE '%".$data['sSearch']."%' OR a.AddInfo3Value LIKE '%".$data['sSearch']."%' 
                    OR a.AddInfo6Value LIKE '%".$data['sSearch']."%' OR a.AddInfo7Value LIKE '%".$data['sSearch']."%' OR a.AddInfo8Value LIKE '%".$data['sSearch']."%' 
                    OR a.AddInfo9Value LIKE '%".$data['sSearch']."%' OR a.AddInfo11Value LIKE '%".$data['sSearch']."%' OR a.AddInfo12Value LIKE '%".$data['sSearch']."%' 
                    OR a.[Priority] LIKE '%".$data['sSearch']."%' OR a.ExpiryDate LIKE '%".$data['sSearch']."%' OR a.ApprovalNeeded LIKE '%".$data['sSearch']."%' 
                    OR a.ApprovalStatus LIKE '%".$data['sSearch']."%' OR a.AddInfo4Value LIKE '%".$data['sSearch']."%')";
            }


            $query .= " 
                Group By a.ApprovalType, a.RequestNo, a.RequestByName,
                     a.AddInfo1Value, a.AddInfo2Value, 
                     a.AddInfo6Value, a.AddInfo8Value, a.AddInfo9Value,
                     a.[Priority], a.ExpiryDate, a.ApprovalNeeded, a.AddInfo12Value, a.AddInfo4Value
                HAVING sum(case when a.ApprovalStatus='REJECTED' then 1 else 0 end)=0
                and (case when sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) > a.ApprovalNeeded then a.ApprovalNeeded 
                         else sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) end) < a.ApprovalNeeded 
            ) a ";

            $query.= " inner join (select b.ApprovalType, b.RequestNo, b.[Priority],
                CASE
                WHEN b.ApprovalType = 'CREDIT LIMIT' THEN '".site_url()."MsDealerApprovalV2/ProcessRequestV2?type=cl&id='+b.RequestNo+''
                WHEN b.ApprovalType = 'CBD ON' THEN '".site_url()."MsDealerApproval/ProcessRequest?type=cbdon&id='+b.RequestNo+''
                WHEN b.ApprovalType = 'CBD OFF' THEN '".site_url()."MsDealerApproval/ProcessRequest?type=cbdoff&id='+b.RequestNo+''
                WHEN b.ApprovalType = 'TARGET SPG' THEN '".site_url()."TargetSalesmanApprovalV2/viewTarget?norequest='+b.RequestNo+''
                WHEN b.ApprovalType = 'TUNJANGAN PRESTASI SPG' THEN '".site_url()."TunjanganPrestasiSPGApproval/ViewApproval?noimport='+b.RequestNo+''
                WHEN b.ApprovalType = 'PLAN PO' THEN '".site_url()."PlanPO/viewFromDashboard?trxid='+b.AddInfo1Value+''
                WHEN b.ApprovalType = 'CAMPAIGN PLAN' THEN '".site_url()."CampaignPlan/viewFromDashboard?trxid='+b.RequestNo+''
                WHEN b.ApprovalType = 'TARGET KPI KARYAWAN' THEN '".site_url()."TargetKaryawanApproval/ProsesTargetKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+'&week='+b.AddInfo12Value+''
                WHEN b.ApprovalType = 'TARGET KPI' THEN '".site_url()."TargetSalesmanApproval/ProsesTargetKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+''
                WHEN b.ApprovalType = 'ACHIEVEMENT KPI KARYAWAN' THEN '".site_url()."TargetKaryawanApproval/ProsesAchievementKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+'&week='+b.AddInfo12Value+''
                WHEN b.ApprovalType = 'ACHIEVEMENT KPI' THEN '".site_url()."TargetSalesmanApproval/ProsesAchievementKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+'&week='+b.AddInfo12Value+''
                WHEN b.ApprovalType = 'PURCHASE IMPORT' THEN '".site_url()."pembelianImportApproval/view?norequest='+b.RequestNo+''
                WHEN b.ApprovalType = 'UNLOCK TOKO' THEN '".site_url()."index.php/MsDealerApproval/ProsesRequestUnlock?'+b.AddInfo4Value+''
                WHEN b.ApprovalType = 'REQUEST PORO' THEN '".site_url()."PreOrderPembelianApprovalV2/ApproveNew?'+b.AddInfo4Value+''
                WHEN b.ApprovalType = 'TARGET KPI SALESMAN' THEN '".site_url()."Targetkpisalesman/ProsesTargetKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+''
                WHEN b.ApprovalType = 'ACHIEVEMENT KPI SALESMAN' THEN '".site_url()."Achievementkpisalesman/ProsesAchievementKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+'&week='+b.AddInfo12Value+''
                WHEN b.ApprovalType = 'TARGET KPI V2' THEN '".site_url()."TargetKPIV2Approval/ProsesTargetKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+'&week='+b.AddInfo12Value+''
                WHEN b.ApprovalType = 'ACHIEVEMENT KPI V2' THEN '".site_url()."TargetKPIV2Approval/ProsesAchievementKPI?req='+b.RequestNo+'&app='+b.ApprovedByEmail+'&week='+b.AddInfo12Value+''
                ELSE '' END as Url 
                from TblApproval b
                where ApprovalStatus = 'UNPROCESSED' and IsEmailed=1 
                    AND (b.ExpiryDate is null or b.ExpiryDate >= cast(GETDATE() as date))
                ";
            if ($is_developer== 0 || $this->allow_dev_approve==false){   
                $query .= " AND (ApprovedByEmail = '".$data['ApprovedByEmail']."'
                    OR ApprovedBy = '".$data['ApprovedByEmail']."'
                    OR ApprovedBy = '".$data['ApprovedBy']."') ";
                if($data['ApprovalType']!=''){  
                    $query .= " AND ApprovalType = '".$data['ApprovalType']."' ";
                }
            }
            $query .= ") b on a.RequestType=b.ApprovalType and a.RequestNo=b.RequestNo and a.[Priority]=b.[Priority]";

            
            $query_jum = $query;
            $query .= " ORDER BY ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";

            // die($query);
            $resjum=$this->db->query($query_jum);
            $res=$this->db->query($query);

            if($res->num_rows() > 0){

                $hasildata['total']=$resjum->num_rows();
                $hasildata['data']=$res->result();
                return $hasildata;

            }else{
                return array();
            }
        }

        function getpendingrequestcount($data)
        {

            $is_developer = 0;
            if (array_key_exists("role", $_SESSION)) 
            {
                $jml = count($_SESSION['role']);
                for($i=0;$i<$jml;$i++) {
                    if ($_SESSION['role'][$i]=="ROLE01"){
                        $is_developer = 1;
                    }
                }
            }

            $data_list=array();
            
        
            $query = "Select count(a.RequestNo) as JmlRequest
            FROM (
                Select DISTINCT a.ApprovalType as RequestType, a.RequestNo, a.RequestByName, MIN(a.RequestDate) as RequestDate, 
                a.AddInfo1Value as KodePelanggan, a.AddInfo2Value as Divisi, 
                a.AddInfo6Value as NamaPelanggan, a.AddInfo8Value as Catatan, a.AddInfo9Value as Wilayah,  
                a.[Priority], a.ExpiryDate, a.ApprovalNeeded, sum(case when a.ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount, 
                (case when sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) > a.ApprovalNeeded then a.ApprovalNeeded 
                    else sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) end) as ApprovedCount,
                sum(case when a.ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount,
                a.AddInfo4Value 
                FROM TblApproval a 
                WHERE a.IsCancelled = 0 
                    AND (a.ExpiryDate is null or a.ExpiryDate >= cast(GETDATE() as date))
                ";

            if(!empty($data['sSearch'])){
                $query  .= " AND (a.ApprovalType LIKE '%".$data['sSearch']."%' OR a.RequestNo LIKE '%".$data['sSearch']."%' OR a.RequestByName LIKE '%".$data['sSearch']."%' OR a.RequestDate LIKE '%".$data['sSearch']."%' OR a.AddInfo1Value LIKE '%".$data['sSearch']."%' OR a.AddInfo2Value LIKE '%".$data['sSearch']."%' OR a.AddInfo3Value LIKE '%".$data['sSearch']."%' 
                    OR a.AddInfo6Value LIKE '%".$data['sSearch']."%' OR a.AddInfo7Value LIKE '%".$data['sSearch']."%' OR a.AddInfo8Value LIKE '%".$data['sSearch']."%' 
                    OR a.AddInfo9Value LIKE '%".$data['sSearch']."%' OR a.AddInfo11Value LIKE '%".$data['sSearch']."%' OR a.AddInfo12Value LIKE '%".$data['sSearch']."%' 
                    OR a.[Priority] LIKE '%".$data['sSearch']."%' OR a.ExpiryDate LIKE '%".$data['sSearch']."%' OR a.ApprovalNeeded LIKE '%".$data['sSearch']."%' 
                    OR a.ApprovalStatus LIKE '%".$data['sSearch']."%' OR a.AddInfo4Value LIKE '%".$data['sSearch']."%')";
            }


            $query .= " 
                Group By a.ApprovalType, a.RequestNo, a.RequestByName,
                     a.AddInfo1Value, a.AddInfo2Value, 
                     a.AddInfo6Value, a.AddInfo8Value, a.AddInfo9Value,
                     a.[Priority], a.ExpiryDate, a.ApprovalNeeded, a.AddInfo12Value, a.AddInfo4Value
                HAVING sum(case when a.ApprovalStatus='REJECTED' then 1 else 0 end)=0
                and (case when sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) > a.ApprovalNeeded then a.ApprovalNeeded 
                         else sum(case when a.ApprovalStatus='APPROVED' then 1 else 0 end) end) < a.ApprovalNeeded 
            ) a ";
            $query.= " inner join (select ApprovalType, RequestNo, [Priority]
                from TblApproval b
                where ApprovalStatus = 'UNPROCESSED' and IsEmailed=1 
                    AND (b.ExpiryDate is null or b.ExpiryDate >= cast(GETDATE() as date))
                ";
            if ($is_developer== 0 || $this->allow_dev_approve==false){   
                $query .= " AND (ApprovedByEmail = '".$data['ApprovedByEmail']."'
                    OR ApprovedBy = '".$data['ApprovedByEmail']."'
                    OR ApprovedBy = '".$data['ApprovedBy']."') ";
                if($data['ApprovalType']!=''){  
                    $query .= " AND ApprovalType = '".$data['ApprovalType']."' ";
                }
            }
            $query .= ") b on a.RequestType=b.ApprovalType and a.RequestNo=b.RequestNo and a.[Priority]=b.[Priority] ";
                // ORDER BY ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";


            // die($query);

            $resjum=$this->db->query($query);
            if ($resjum->num_rows() > 0)
                // die("#".$resjum->row()->JmlRequest);
                return $resjum->row()->JmlRequest;
            else
                return 0;
        }

		public function doaction($action, $post)
		{	
            $Priority = 0;
			$CountApproved = 0;
			$CountRejected = 0;
			$CountCancelled = 0;
			$CountUnprocessed = 0;
			$ApprovalNeeded = 0;

			$lanjut = true;

            $x = array();
            $x["pesan"] = "";
			
			//Jika INSERT, Cek Dahulu UserEmail Approver Terdaftar di msuserhd 
			if ($action=="insert") {
				/* LAMA
				$approver1 = $this->UserModel->Get($post['ApprovedBy']);		//Cek by UserID
				$approver2 = $this->UserModel->Get3($post['ApprovedByEmail']);		//Cek by UserEmail
				if ($approver1==null && $approver2==null) {
					$lanjut = false;
					$x["pesan"] = "Useremail Approver ".$post['ApprovedByEmail']." Tidak Terdaftar di MyCompany. Request Gagal Disimpan di MyCompany";
				}
				else{
					if (ISSET($approver2->USERID) && $post["ApprovedBy"] != $approver2->USERID) {
						$post["ApprovedBy"] = $approver2->USERID; 
					} else if (ISSET($approver1->Email) && $post["ApprovedByEmail"] != $approver1->Email) {
						$post["ApprovedByEmail"] = $approver1->Email;
					}
				}
				*/
				
				$approver1 = $this->UserModel->Get($post['ApprovedBy']);
				if($approver1){
					$post["ApprovedBy"] = $approver1->USERID;
					if(!ISSET($post["ApprovedByEmail"])){
						$post["ApprovedByEmail"] = $approver1->UserEmail;
					}
				}
				else{
					if(!ISSET($post["ApprovedByEmail"])){
						$post["ApprovedByEmail"] = $post['ApprovedBy'];
					}
					$approver2 = $this->UserModel->Get3($post['ApprovedByEmail']);
					if($approver2){
						$post["ApprovedBy"] = $approver2->USERID; 
						if($post["ApprovedByEmail"] == $post['ApprovedBy']){
							$post["ApprovedByEmail"] = $approver2->USEREMAIL;
						}
					}
					else{
						$lanjut = false;
						$x["pesan"] = "Useremail Approver ".$post['ApprovedByEmail']." Tidak Terdaftar di MyCompany. Request Gagal Disimpan di MyCompany";
					}
				}
			} else if ($action=="approve") {
				/* LAMA
                $post["ApprovedByEmail"] = $post["ApprovedBy"];
                $approver1 = $this->UserModel->Get($post['ApprovedBy']);            //Cek by UserID
                // echo(json_encode($approver1)."<br><br>");
                $approver2 = $this->UserModel->Get3($post['ApprovedByEmail']);      //Cek by UserEmail
                // echo(json_encode($approver2)."<br><br>");
                if ($approver1!=null && $approver2!=null) {
                    if ($post["ApprovedBy"] != $approver2->USERID) {
                        $post["ApprovedBy"] = $approver2->USERID; 
                    } else if ($post["ApprovedByEmail"] != $approver1->Email) {
                        $post["ApprovedByEmail"] = $approver1->Email;
                    }
                }
				*/
				$approver1 = $this->UserModel->Get($post['ApprovedBy']);
				if($approver1){
					$post["ApprovedBy"] = $approver1->USERID; 
					$post["ApprovedByEmail"] = $approver1->UserEmail;
				}
				else{
					if(!ISSET($post["ApprovedByEmail"])){
						$post["ApprovedByEmail"] = $post['ApprovedBy'];
					}
					$approver2 = $this->UserModel->Get3($post['ApprovedByEmail']);
					if($approver2){
						$post["ApprovedBy"] = $approver2->USERID; 
						$post["ApprovedByEmail"] = $approver2->USEREMAIL;
					}
					else{
						$lanjut = false;
						$x["pesan"] = "Useremail Approver ".$post['ApprovedByEmail']." Tidak Terdaftar di MyCompany. Request Gagal Diapprove di MyCompany";
					}
				}
            }

            // die(json_encode($post));


			if ($lanjut==true) {
				$get = $this->getandcheckapproval($post);
                // die(json_encode($get)."<br><br>");
				if (count($get)>0) {

					for($i=0;$i<count($get);$i++) {

                        $Priority = $get[$i]->Priority;
                        $ApprovalNeeded += $get[$i]->ApprovalNeeded;
                        $CountApproved += $get[$i]->ApprovedCount;
                        $CountRejected += $get[$i]->RejectedCount;
                        $CountCancelled += $get[$i]->CancelledCount;
                        $CountUnprocessed += $get[$i]->UnprocessedCount;
					}

					if ($action=='insert'){
                        if ($CountRejected>0){
                            $x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
                        } elseif ($CountCancelled>0){
                            $x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
                        // } elseif ($CountApproved >= $ApprovalNeeded){
							// 
                        } else {

                            for($i=0;$i<count($get);$i++) {

                                if ($post["Priority"] == $get[$i]->Priority) {
                                    if ($CountApproved >= $ApprovalNeeded) {
                                        $x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
                                        return $x;
                                    }

                                    break;
                                }
                            }
                            
                            // die("here");
                            $requests = $this->getbyapprover($post);
                            if (count($requests)>0) {
                                $request = $requests[0];
                                if ($request->Priority==$post["Priority"] && $request->ApprovalStatus == "APPROVED") {
                                    $x["pesan"] = "Request Ini Sudah Anda approve, Tidak Dapat Di".$action." Lagi";
                                } else {
                                    $delete = $this->delete($post);
                                    if ($delete==true) {
                                        // die("delete true");
                                        $doaction = $this->insert($post);
                                         if ($doaction==true) {
                                             $x["pesan"] = "Request Ini Berhasil Di".$action."";   
                                         } else {
                                             $x["pesan"] = "Request Ini Gagal Di".$action."";  
                                         }
                                    } else {
                                        // die("delete false");
                                        $x["pesan"] = "Request Ini Gagal Di".$action."";  
                                    }   
                                }                             
                            } else {
                                // die("insert2");
                                $doaction = $this->insert($post);
                                 if ($doaction==true) {
                                     $x["pesan"] = "Request Ini Berhasil Di".$action."";   
                                 } else {
                                     $x["pesan"] = "Request Ini Gagal Di".$action."";  
                                 }                                
                            }
                        }
					} elseif ($action=='approve'){
                        if ($CountRejected>0){
                            $x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
                        } elseif ($CountCancelled>0){
                            $x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
                        } elseif ($CountApproved >= $ApprovalNeeded){
                            $x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
                        } else {
                            $requests = $this->getbyapprover($post);
							echo json_encode($requests).'<br><br>';
                            if (count($requests)>0) {
                                $request = $requests[0];
                                if ($request->ApprovalStatus=="UNPROCESSED") {
                                    $doaction = $this->approve($post);
                                    if ($doaction==true) {
                                        $x["pesan"] = "Request Ini Berhasil Di".$action.""; 
                                        
                                        $CountApproved++;
                                        if ($CountApproved==$ApprovalNeeded){
                                            $updatebhaktiflag = $this->updatebhaktiflag($post);
                                        }
                                    } else {
                                        $x["pesan"] = "Request Ini Gagal Di".$action."";    
                                    }                                
                                } else {
                                    $x["pesan"] = "Request Ini Sudah Anda approve, Tidak Dapat Di".$action." Lagi";
                                }
                            } else {
                                $x["pesan"] = "Anda bukan salah satu pengapprove request ini, Tidak Dapat Di".$action." Lagi";
                            }
						}
					} elseif ($action=='reject'){
                        if ($CountRejected>0){
                            $x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
                        } elseif ($CountCancelled>0){
                            $x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
                        } elseif ($CountApproved >= $ApprovalNeeded){
                            $x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
                        } else {
							$doaction = $this->reject($post);
							if ($doaction==true) {
								$x["pesan"] = "Request Ini Berhasil Di".$action."";	
							} else {
								$x["pesan"] = "Request Ini Gagal Di".$action."";	
							}
						}
					} elseif ($action=='cancel'){
                        if ($CountRejected>0){
                            $x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
                        } else if ($CountCancelled>0){
                            $x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
                        } else if ($CountApproved >= $ApprovalNeeded){
                            $x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
						} else {
							$doaction = $this->cancel($post);
							if ($doaction==true) {
								$x["pesan"] = "Request Ini Berhasil Di".$action."";	
							} else {
								$x["pesan"] = "Request Ini Gagal Di".$action."";	
							}
						}
					} elseif ($action=='delete'){
                        if ($CountRejected>0){
                            $x["pesan"] = "Request Ini Sudah Direject, Tidak Dapat Di".$action." Lagi";
                        } elseif ($CountCancelled>0){
                            $x["pesan"] = "Request Ini Sudah Dicancel, Tidak Dapat Di".$action." Lagi";
                        } elseif ($CountApproved >= $ApprovalNeeded){
                            $x["pesan"] = "Request Ini Sudah Diapprove, Tidak Dapat Di".$action." Lagi";
						} else {
							$doaction = $this->delete($post);
							if ($doaction==true) {
								$x["pesan"] = "Request Ini Berhasil Di".$action."";	
							} else {
								$x["pesan"] = "Request Ini Gagal Di".$action."";	
							}
						}
					}

				} else {
					if ($action=='insert'){
                        // die("insert3");
						$doaction = $this->insert($post);
						if ($doaction==true) {
							$x["pesan"] = "Request Ini Berhasil Di".$action."";	
						} else {
							$x["pesan"] = "Request Ini Gagal Di".$action."";	
						}
					} else {
						$x["pesan"] = "Request Ini Tidak Ditemukan, Tidak Dapat Di".$action."";
					}
				}
			}
			return $x;
		}
	
		function updateisemailednextpriority($post)
        {
            $ERR_MSG='';

            $this->db->trans_begin();
            $this->db->where('ApprovalType',$post['ApprovalType']);
            $this->db->where('RequestNo',$post['RequestNo']);
            $this->db->where('Priority',$post['Priority']+1);
            $this->db->set('IsEmailed', true);
            $this->db->set('EmailedDate', date('Y-m-d H:i:s'));
            $this->db->update('TblApproval');

            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
 			}	
        }

		function CancelTblApprovalBecauseTrxIsDeleted($post)
        {
            $ERR_MSG='';

            $this->db->trans_begin();
            $this->db->where('ApprovalType',$post['ApprovalType']);
            $this->db->where('RequestNo',$post['RequestNo']);
            $this->db->set('ApprovalStatus','UNPROCESSED');
            $this->db->set('ApprovalStatus','CANCELLED');
            $this->db->set('IsCancelled',1);
            $this->db->set('CancelledBy',(ISSET($post['CancelledBy'])?$post['CancelledBy']:NULL));
            $this->db->set('CancelledByName',(ISSET($post['CancelledByName'])?$post['CancelledByName']:NULL));
            $this->db->set('CancelledDate',date('Y-m-d H:i:s'));
            $this->db->set('CancelledNote',(ISSET($post['CancelledNote'])?$post['CancelledNote']:NULL));
            $this->db->set('CancelledByEmail',(ISSET($post['CancelledByEmail'])?$post['CancelledByEmail']:NULL));
            $this->db->update('TblApproval');

            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    $ERR_MSG.= $error['message']."; ";
                }
            }

			if($ERR_MSG==''){
                $this->db->trans_commit();
                return true;
			}
			else{
                $this->db->trans_rollback();
                return false;
 			}	
        }


        function getpendingrequests_backup($data)
        {

            $is_developer = 0;
            if (array_key_exists("role", $_SESSION)) 
            {
                $jml = count($_SESSION['role']);
                for($i=0;$i<$jml;$i++) {
                    if ($_SESSION['role'][$i]=="ROLE01"){
                        $is_developer = 1;
                    }
                }
            }

            $data_list=array();
            
            $page=0;
            if(!empty($data['iDisplayStart'])){
                $page=$data['iDisplayStart'];
            }


            $SortCol='ApprovalType, RequestDate';
            $SortDir='desc';


            if(!empty($data['iSortCol_0'])){
                if($data['iSortCol_0']==1){
                    $SortCol='ApprovalType';
                }else if($data['iSortCol_0']==2){
                    $SortCol='RequestNo';
                }else if($data['iSortCol_0']==3){
                    $SortCol='Wilayah';
                }
            }


            if(!empty($data['sSortDir_0'])){
                $SortDir=$data['sSortDir_0'];
            }



            $page=0;
            if(!empty($data['iDisplayStart'])){
                $page=$data['iDisplayStart'];
            }

            $total_data_view=10;
            if(!empty($data['iDisplayLength'])){
                $total_data_view=$data['iDisplayLength'];
            }


        

            $query = "

            Select ApprovalType as RequestType, RequestNo, RequestByName, MIN(RequestDate) as RequestDate, 
            AddInfo1Value as KodePelanggan, AddInfo2Value as Divisi, CAST(AddInfo3Value as MONEY) as CreditLimitBaru,
            AddInfo6Value as NamaPelanggan, CAST(AddInfo7Value as MONEY) as CreditLimitPermanent, AddInfo8Value as Catatan, AddInfo9Value as Wilayah,
            CAST(AddInfo11Value as MONEY) as CreditLimitTemporary, CAST(AddInfo12Value as MONEY) as KenaikanCL,  
            [Priority], ExpiryDate, ApprovalNeeded, sum(case when ApprovalStatus='UNPROCESSED' then 1 else 0 end) as UnprocessedCount, 
            (case when sum(case when ApprovalStatus='APPROVED' then 1 else 0 end)>ApprovalNeeded then ApprovalNeeded else sum(case when ApprovalStatus='APPROVED' then 1 else 0 end) end) as ApprovedCount,
            sum(case when ApprovalStatus='REJECTED' then 1 else 0 end) as RejectedCount,
            AddInfo4Value,
            CASE
            WHEN ApprovalType = 'CREDIT LIMIT' THEN '".site_url()."MsDealerApprovalV2/ProcessRequestV2?type=cl&id='+RequestNo+''
            WHEN ApprovalType = 'CBD ON' THEN '".site_url()."MsDealerApproval/ProcessRequest?type=cbdon&id='+RequestNo+''
            WHEN ApprovalType = 'CBD OFF' THEN '".site_url()."MsDealerApproval/ProcessRequest?type=cbdoff&id='+RequestNo+''
            WHEN ApprovalType = 'TARGET SPG' THEN '".site_url()."TargetSalesmanApprovalV2/viewTarget?norequest='+RequestNo+''
            WHEN ApprovalType = 'TUNJANGAN PRESTASI SPG' THEN '".site_url()."TunjanganPrestasiSPGApproval/ViewApproval?noimport='+RequestNo+''
            WHEN ApprovalType = 'PLAN PO' THEN '".site_url()."PlanPO/viewFromDashboard?trxid='+AddInfo1Value+''
            WHEN ApprovalType = 'CAMPAIGN PLAN' THEN '".site_url()."CampaignPlan/viewFromDashboard?trxid='+RequestNo+''
            WHEN ApprovalType = 'TARGET KPI KARYAWAN' THEN '".site_url()."TargetKaryawanApproval/ProsesTargetKPI?req='+RequestNo+'&app='+ApprovedByEmail+'&week='+AddInfo12Value+''
            WHEN ApprovalType = 'TARGET KPI' THEN '".site_url()."TargetSalesmanApproval/ProsesTargetKPI?req='+RequestNo+'&app='+ApprovedByEmail+''
            WHEN ApprovalType = 'ACHIEVEMENT KPI KARYAWAN' THEN '".site_url()."TargetKaryawanApproval/ProsesAchievementKPI?req='+RequestNo+'&app='+ApprovedByEmail+'&week='+AddInfo12Value+''
            WHEN ApprovalType = 'ACHIEVEMENT KPI' THEN '".site_url()."TargetSalesmanApproval/ProsesAchievementKPI?req='+RequestNo+'&app='+ApprovedByEmail+'&week='+AddInfo12Value+''
            WHEN ApprovalType = 'PURCHASE IMPORT' THEN '".site_url()."pembelianImportApproval/view?norequest='+RequestNo+''
            WHEN ApprovalType = 'UNLOCK TOKO' THEN '".site_url()."index.php/MsDealerApproval/ProsesRequestUnlock?'+AddInfo4Value+''
            WHEN ApprovalType = 'REQUEST PORO' THEN '".site_url()."PreOrderPembelianApprovalV2/ApproveNew?'+AddInfo4Value+''
            ELSE '' END as Url 
            FROM TblApproval 
            WHERE ApprovalStatus = 'UNPROCESSED' AND IsEmailed = 1
                AND IsCancelled = 0 
                AND (ExpiryDate IS NULL OR convert(varchar(max),ExpiryDate,112) >= convert(varchar(max),getdate(),112)) ";

            // 16-Juni-2023 Aliat - untuk ApprovedCount >= ApprovalNeeded, maka tidak ditampilkan lg di dashboard
            $query .= " AND RequestNo NOT IN (SELECT RequestNo
                     FROM TblApproval
                     WHERE (ApprovalStatus = 'APPROVED')
                     GROUP BY RequestNo, ApprovalNeeded
                     HAVING (COUNT(ApprovalStatus) >= ApprovalNeeded)) ";
                        
            if ($is_developer==0){   
                $query .= " AND (ApprovedByEmail = '".$data['ApprovedByEmail']."'
                    OR ApprovedBy = '".$data['ApprovedByEmail']."'
                    OR ApprovedBy = '".$data['ApprovedBy']."') ";

                if($data['ApprovalType']!=''){  
                    $query .= " AND ApprovalType = '".$data['ApprovalType']."' ";
                }
            } else {
                $query .= " AND 1=1 ";
            }


            if(!empty($data['sSearch'])){
                $query  .= " AND (ApprovalType LIKE '%".$data['sSearch']."%' OR RequestNo LIKE '%".$data['sSearch']."%' OR RequestByName LIKE '%".$data['sSearch']."%' OR RequestDate LIKE '%".$data['sSearch']."%' OR AddInfo1Value LIKE '%".$data['sSearch']."%' OR AddInfo2Value LIKE '%".$data['sSearch']."%' OR AddInfo3Value LIKE '%".$data['sSearch']."%' OR AddInfo6Value LIKE '%".$data['sSearch']."%' OR AddInfo7Value LIKE '%".$data['sSearch']."%' OR AddInfo8Value LIKE '%".$data['sSearch']."%' OR AddInfo9Value LIKE '%".$data['sSearch']."%' OR AddInfo11Value LIKE '%".$data['sSearch']."%' OR AddInfo12Value LIKE '%".$data['sSearch']."%' OR [Priority] LIKE '%".$data['sSearch']."%' OR ExpiryDate LIKE '%".$data['sSearch']."%' OR ApprovalNeeded LIKE '%".$data['sSearch']."%' OR ApprovalStatus LIKE '%".$data['sSearch']."%' OR AddInfo4Value LIKE '%".$data['sSearch']."%')";
            }


            $query_jum = $query." Group By ApprovalType, RequestNo, RequestByName,
                     AddInfo1Value, AddInfo2Value, CAST(AddInfo3Value as MONEY),
                     AddInfo6Value, CAST(AddInfo7Value as MONEY), AddInfo8Value, AddInfo9Value,
                     CAST(AddInfo11Value as MONEY), CAST(AddInfo12Value as MONEY), 
                     [Priority], ExpiryDate, ApprovalNeeded, ApprovedByEmail, AddInfo12Value, AddInfo4Value";
            $query .= " 
                Group By ApprovalType, RequestNo, RequestByName,
                     AddInfo1Value, AddInfo2Value, CAST(AddInfo3Value as MONEY),
                     AddInfo6Value, CAST(AddInfo7Value as MONEY), AddInfo8Value, AddInfo9Value,
                     CAST(AddInfo11Value as MONEY), CAST(AddInfo12Value as MONEY), 
                     [Priority], ExpiryDate, ApprovalNeeded, ApprovedByEmail, AddInfo12Value, AddInfo4Value
                ORDER BY ".$SortCol." ".$SortDir." OFFSET ".$page." ROWS FETCH NEXT ".$total_data_view." ROWS ONLY";



            $resjum=$this->db->query($query_jum);

            $res=$this->db->query($query);
            if($res->num_rows() > 0){

                $hasildata['total']=$resjum->num_rows();
                $hasildata['data']=$res->result();
                return $hasildata;

            }else{
                return array();
            }
        }

        function getpendingrequestcount_backup($post)
        {
            $is_developer = 0;
            if (array_key_exists("role", $_SESSION)) 
            {
                $jml = count($_SESSION['role']);
                for($i=0;$i<$jml;$i++) {
                    if ($_SESSION['role'][$i]=="ROLE01"){
                        $is_developer = 1;
                    }
                }
            }
            
            if($post['ApprovalType']==''){
                $qry = "Select RequestNo
                FROM TblApproval 
                WHERE ApprovalStatus = 'UNPROCESSED' AND IsEmailed = 1
                AND IsCancelled = 0
                AND (ExpiryDate IS NULL OR convert(varchar(max),ExpiryDate,112) >= convert(varchar(max),getdate(),112))";
            if ($is_developer==0){   
                $qry .= " AND (ApprovedByEmail = '".$post['ApprovedByEmail']."'
                            OR ApprovedBy = '".$post['ApprovedByEmail']."'
                            OR ApprovedBy = '".$post['ApprovedBy']."') ";
            } else {
                $qry .= " AND ApprovalType in ('CREDIT LIMIT', 'REQUEST PORO') ";
                    // $qry .= " AND 1=1 ";
            }

                $qry .= " Group By ApprovalType, RequestNo, RequestByName,
                        AddInfo1Value, AddInfo2Value, CAST(AddInfo3Value as MONEY),
                        AddInfo6Value, CAST(AddInfo7Value as MONEY), AddInfo8Value, AddInfo9Value,
                        CAST(AddInfo11Value as MONEY), CAST(AddInfo12Value as MONEY), 
                        [Priority], ExpiryDate, ApprovalNeeded, ApprovedByEmail, AddInfo12Value, AddInfo4Value";
            } else {
                $qry = "Select RequestNo
                FROM TblApproval 
                WHERE ApprovalType = '".$post['ApprovalType']."' 
                AND ApprovalStatus = 'UNPROCESSED' AND IsEmailed = 1
                AND IsCancelled = 0
                AND (ExpiryDate IS NULL OR convert(varchar(max),ExpiryDate,112) >= convert(varchar(max),getdate(),112))";
                if ($is_developer==0){   
                    $qry .= " AND (ApprovedByEmail = '".$post['ApprovedByEmail']."'
                                OR ApprovedBy = '".$post['ApprovedByEmail']."'
                                OR ApprovedBy = '".$post['ApprovedBy']."') ";
                } else {
                    $qry .= " AND 1=1 ";
                }
                $qry .= " Group By ApprovalType, RequestNo, RequestByName,
                        AddInfo1Value, AddInfo2Value, CAST(AddInfo3Value as MONEY),
                        AddInfo6Value, CAST(AddInfo7Value as MONEY), AddInfo8Value, AddInfo9Value,
                        CAST(AddInfo11Value as MONEY), CAST(AddInfo12Value as MONEY), 
                        [Priority], ExpiryDate, ApprovalNeeded, ApprovedByEmail, AddInfo12Value, AddInfo4Value ";
            }
            // die($qry);
            $res = $this->db->query($qry);
            return $res->num_rows();
        }

	}
?>