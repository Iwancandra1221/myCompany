<?php
	Class ZenModel extends CI_Model
	{
		public function CheckMaster($table, $key, $value)
		{
			$qry = "SELECT * FROM ".$table." WHERE ".$key."='".$value."'";
			$res = $this->db->query($qry);
			if ($res->num_rows()>0) {
				return true;
			} else {
				return false;
			}
		}

		public function SyncMsGroup($data, $user)
		{
			// {"GROUPID":"CDPS1","BRANCHID":"BLI","GROUPNAME":"Bali","CITY":"DENPASAR"}
			$this->db->trans_start();
			$this->db->query("UPDATE Ms_Group SET IsActive=0");

			for ($i=0; $i<count($data);$i++) {
				if ($this->CheckMaster("Ms_Group", "GroupID", $data[$i]["GROUPID"])) {
					$this->db->where("GroupID", $data[$i]["GROUPID"]);
					$this->db->set("BranchID", $data[$i]["BRANCHID"]);
					$this->db->set("Name", $data[$i]["GROUPNAME"]);
					$this->db->set("City", $data[$i]["CITY"]);
					$this->db->set("IsActive", 1);
					$this->db->set("UpdatedBy", $user);
					$this->db->set("UpdatedDate", date("Y-m-d H:i:s"));
					$this->db->update("Ms_Group");
				} else {
					$this->db->set("GroupID", $data[$i]["GROUPID"]);
					$this->db->set("BranchID", $data[$i]["BRANCHID"]);
					$this->db->set("Name", $data[$i]["GROUPNAME"]);
					$this->db->set("City", $data[$i]["CITY"]);
					$this->db->set("IsActive", 1);
					$this->db->set("CreatedBy", $user);
					$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
					$this->db->insert("Ms_Group");
				}
			}
			$this->db->trans_complete();
		}


		public function SyncMsBranch($data, $user)
		{
			$this->db->trans_start();
			// $this->db->query("UPDATE Ms_Branch SET IsActive=0");

			for ($i=0; $i<count($data);$i++) {
				if ($this->CheckMaster("Ms_Branch", "BranchID", $data[$i]["BRANCHID"])) {
					$this->db->where("BranchId", $data[$i]["BRANCHID"]);
					//BranchCode jangan diupdate
					$this->db->set("BranchName", $data[$i]["BRANCHNAME"]);
					$this->db->set("BranchHead", $data[$i]["BRANCHHEAD"]);
					$this->db->set("BranchAddress", $data[$i]["BRANCHADDRESS"]);
					$this->db->set("IsActive", 1);
					$this->db->set("UpdatedBy", $user);
					$this->db->set("UpdatedDate", date("Y-m-d H:i:s"));
					$this->db->update("Ms_Branch");
				} else {
					$this->db->set("BranchId", $data[$i]["BRANCHID"]);
					$this->db->set("BranchCode", $data[$i]["BRANCHID"]);
					$this->db->set("BranchName", $data[$i]["BRANCHNAME"]);
					$this->db->set("BranchHead", $data[$i]["BRANCHHEAD"]);
					$this->db->set("BranchAddress", $data[$i]["BRANCHADDRESS"]);
					$this->db->set("IsActive", 1);
					$this->db->set("CreatedBy", $user);
					$this->db->set("CreatedDate", date("Y-m-d H:i:s"));
					$this->db->insert("Ms_Branch");
				}
				// die($this->db->last_query());
			}
			$this->db->trans_complete();
		}

		public function JsonBranch(){
			$this->db->select("a.BranchID,a.BranchName,a.BranchHead,case when a.IsActive=1 then 'Active' else 'Not Active' end as IsActive,a.UpdatedBy,a.UpdatedDate,b.UserName");
			$this->db->join('msuserhd b','a.BranchHead=b.AlternateID');
			$qry = $this->db->get('Ms_Branch a');
			if ($qry->num_rows()>0) {
				return $qry->result();
			}
		}

		public function UpdateStatus($BranchID,$Status){
			$this->db->query("UPDATE Ms_Branch SET IsActive='".$Status."' WHERE BranchID='".$BranchID."'");
		}
	}
?>