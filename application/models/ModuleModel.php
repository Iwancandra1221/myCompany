<?php
	Class ModuleModel extends CI_Model
	{

		function getList(){
			$this->db->select("*, (case module_type when 'PARENT' then 1 when 'CHILD' then 2 else 3 end) as module_type_id");
			$this->db->from('tb_modulemycompany_hd');  
			$this->db->order_by("(case module_type when 'PARENT' then 1 when 'CHILD' then 2 else 3 end)", "ASC");
			$this->db->order_by('position', 'ASC');

			return $this->db->get()->result();
		}

		function getListActive(){
			$this->db->select("*, (case module_type when 'PARENT' then 1 when 'CHILD' then 2 else 3 end) as module_type_id");
			$this->db->from('tb_modulemycompany_hd');  
			$this->db->where('is_active','1');
			$this->db->order_by("(case module_type when 'PARENT' then 1 when 'CHILD' then 2 else 3 end)", "ASC");
			$this->db->order_by('position', 'ASC');
			return $this->db->get()->result();
		}

		function getModuleList($module_type="PARENT", $parent_id="",$where=null,$order=null,$top=null){
			$this->db->select("*");
			$this->db->from('tb_modulemycompany_hd'); 
			$this->db->where('module_type',$module_type);
			if ($parent_id!="") 
				$this->db->where("parent_module_id", $parent_id);
			$this->db->order_by('position', 'ASC');
			$result = $this->db->get()->result();
			//echo $this->db->last_query();
			return $result;
		}
		function getModuleList_v2($where,$order,$top=10,$offset=0){
			$whereStr = "";
			if($where!=null){;
				$whereStr = WhereStr($where);
			}
			$query = <<<SQL
			WITH cte AS (
				SELECT 
					module_id, 
					module_name,
					module_type,
					description,
					is_active,
					controller,
					parent_module_id,
					[position],
					1 as depth,
					CAST(ROW_NUMBER() OVER (ORDER BY position) AS VARCHAR(MAX)) AS HierarchyPath
				FROM tb_modulemycompany_hd
				WHERE parent_module_id = ''
				UNION ALL
				SELECT 
					t.module_id, 
					t.module_name,
					t.module_type,
					t.description,
					t.is_active,
					t.controller,
					t.parent_module_id, 
					t.[position],
					c.depth+1,
					CAST(c.HierarchyPath + '.' + CAST(ROW_NUMBER() OVER (ORDER BY t.position) AS VARCHAR(MAX)) AS VARCHAR(MAX))
			  FROM tb_modulemycompany_hd AS t
			  INNER JOIN cte AS c ON t.parent_module_id = c.module_id
			)
			select TOP {$top} * from (
				SELECT 
					cte.*,
					CONCAT(REPLICATE('_', cte.depth * 2), cte.module_id) as module_id_depth,
					concat(x.position,x.module_id) as pos_2,
					ROW_NUMBER() OVER (ORDER BY HierarchyPath) as RowNum
				FROM  cte
				left join (select * from tb_modulemycompany_hd where parent_module_id = '' ) x on x.module_id = cte.module_id
				{$whereStr}
			) as x
			where RowNum > $offset 
SQL;

			$getData = $this->db->query($query)->result_array();
			$result['data'] = $getData;
			$result['count'] = 0;
			//echo 'query '.$this->db->last_query();
			$queryCount = <<<SQL
			WITH cte AS (
				SELECT 
					module_id, 
					module_name,
					module_type,
					description,
					is_active,
					controller,
					parent_module_id,
					[position],
					1 as depth,
					CAST(ROW_NUMBER() OVER (ORDER BY position) AS VARCHAR(MAX)) AS HierarchyPath
				FROM tb_modulemycompany_hd
				WHERE parent_module_id = ''
				UNION ALL
				SELECT 
					t.module_id, 
					t.module_name,
					t.module_type,
					t.description,
					t.is_active,
					t.controller,
					t.parent_module_id, 
					t.[position],
					c.depth+1,
					CAST(c.HierarchyPath + '.' + CAST(ROW_NUMBER() OVER (ORDER BY t.position) AS VARCHAR(MAX)) AS VARCHAR(MAX))
			  FROM tb_modulemycompany_hd AS t
			  INNER JOIN cte AS c ON t.parent_module_id = c.module_id
			)
			SELECT COUNT(cte.module_id) as c
			FROM  cte
			left join (select * from tb_modulemycompany_hd where parent_module_id = '' ) x on  x.module_id = cte.module_id
			{$whereStr}
SQL;
			$getCount = $this->db->query($queryCount)->row();
			if($getCount!=null){
				$result['count'] = $getCount->c;
			}
			return $result;
		}
		function getModule($where){
			$this->db->select("tb_modulemycompany_hd.*");
			$this->db->where($where);
			$result = $this->db->get('tb_modulemycompany_hd')->row();
			return $result;
		}
		function getParentList_v2($jenis){ 
			$query =  "WITH cte AS (
				SELECT 
					module_id, 
					module_name,
					module_type,
					description,
					is_active,
					controller,
					parent_module_id,
					[position],
					1 as depth,
					CAST(ROW_NUMBER() OVER (ORDER BY position) AS VARCHAR(MAX)) AS HierarchyPath
				FROM tb_modulemycompany_hd
				WHERE parent_module_id = ''
				UNION ALL
				SELECT 
					t.module_id, 
					t.module_name,
					t.module_type,
					t.description,
					t.is_active,
					t.controller,
					t.parent_module_id, 
					t.[position],
					c.depth+1,
					CAST(c.HierarchyPath + '.' + CAST(ROW_NUMBER() OVER (ORDER BY t.position) AS VARCHAR(MAX)) AS VARCHAR(MAX))
			  FROM tb_modulemycompany_hd AS t
			  INNER JOIN cte AS c ON t.parent_module_id = c.module_id
			) 

			SELECT 
				module_id, 
				module_name,
				module_type
			FROM (
				SELECT 
					cte.*,
					CONCAT(REPLICATE('_', cte.depth * 2), cte.module_id) as module_id_depth,
					ROW_NUMBER() OVER (ORDER BY HierarchyPath) as RowNum
				FROM cte
				WHERE cte.is_active = 1
				  AND cte.depth = 
				  CASE
				    WHEN '".$jenis."' = 'PARENT' THEN 0
					WHEN '".$jenis."' = 'CHILD' THEN 1
					WHEN '".$jenis."' = 'GRANDCHILD' THEN 2 
					ELSE 3
				    END  
			) AS x   
		    ORDER BY RowNum;";
			$result = $this->db->query($query)->result_array();
			return $result;
		}
		function getParentList(){
			$str = "Select *, 
						(case when module_type='PARENT' then module_id+'0' else parent_module_id+cast(position as varchar(2)) end) as new_sort
					From tb_modulemycompany_hd
					Where is_active=1 and module_type in ('PARENT','CHILD','GRANDCHILD')
					Order By module_id,(case when module_type='PARENT' then module_id+'0' else parent_module_id+cast(position as varchar(2)) end)";
			$res = $this->db->query($str);
			if ($res->num_rows()>0)
				return $res->result();
			else
				return array();
		}
		
		function get($kdmodule){
			$this->db->select('*');
			$this->db->from('tb_modulemycompany_hd');
			$this->db->where('module_id', $kdmodule);
			return $this->db->get()->result();
		 }

		function getDetail($namactr,$listrole){
			//die(json_encode($listrole));
			$this->db->select('controller,max(tb_modulemycompany_dt.can_read) as can_read, max(tb_modulemycompany_dt.can_create) as can_create, max(tb_modulemycompany_dt.can_update) as can_update, max(tb_modulemycompany_dt.can_delete) as can_delete, max(tb_modulemycompany_dt.can_print) as can_print');
			$this->db->from('tb_modulemycompany_dt');
			$this->db->join('tb_modulemycompany_hd','tb_modulemycompany_dt.module_id = tb_modulemycompany_hd.module_id');
			$this->db->where('controller', $namactr);
			$this->db->where_in('role_id',$listrole);
			$this->db->group_by('controller');
			//$res = $this->db->get();
			//die($this->db->last_query());
			
			return $this->db->get()->row();
		}

		function CheckAccess($uri1="", $uri2=""){
			if($uri2 != '')
				$ctrname = $uri1."/".$uri2;
			else
				$ctrname = $uri1;

			if (array_key_exists("role", $_SESSION)) {
				//echo("still here");
				$access = $this->ModuleModel->getDetail($ctrname, $_SESSION['role']);
				if ($access==null) {
					$_SESSION["can_read"]  =false;
					$_SESSION["can_create"]=false;
					$_SESSION["can_update"]=false;
					$_SESSION["can_delete"]=false;
					$_SESSION["can_print"] =false;
				} else {
					$_SESSION["can_read"]=$access->can_read;
					$_SESSION["can_create"]=$access->can_create;
					$_SESSION["can_update"]=$access->can_update;
					$_SESSION["can_delete"]=$access->can_delete;
					$_SESSION["can_print"]=$access->can_print;
				}
			} else {
				show_error("SESI ANDA TELAH HABIS. Harap Login Kembali",10);
			}
			return true;
		}

		function addData($data){
			$this->db->insert('tb_modulemycompany_hd', $data);
		}

		function updateData($data,$kdmodule){
			$module = $this->get($kdmodule);
			$cur_pos = $module[0]->position;
			$new_pos = $data["position"];

			$this->db->trans_start();

			$this->db->where('module_id', $kdmodule);
			$this->db->update('tb_modulemycompany_hd', $data);

			$qry="";
			if ($new_pos<$cur_pos) {
				$qry = "update tb_modulemycompany_hd set [position]=[position]+1 where parent_module_id='".$data["parent_module_id"]."' and module_id<>'".$kdmodule."' and [position] between $new_pos and $cur_pos";
				$this->db->query($qry);
			} else if ($new_pos>$cur_pos) {
				$qry = "update tb_modulemycompany_hd set [position]=[position]-1 where parent_module_id='".$data["parent_module_id"]."' and module_id<>'".$kdmodule."' and [position] between $cur_pos and $new_pos";
				$this->db->query($qry);	   			
			}

			$this->db->trans_complete();
		}

		function deleteData($kdmodule){
			$this->db->where('module_id', $kdmodule);
			$this->db->delete('tb_modulemycompany_hd');
		
		}

		function getListByRole($listrole){
			if (isset($_SESSION["logged_in"])) {
				$user = $_SESSION["logged_in"];
				$useremail = $user["useremail"];
			} else {
				$useremail = "";
			}
			
			// $qry = "Select hd.module_id, hd.module_name, hd.controller, hd.module_type, hd.parent_module_id, hd.position,
			// 		max(dt.can_read) as can_read, max(dt.can_create) as can_create, max(dt.can_update) as can_update, 
			// 		max(dt.can_delete) as can_delete, max(dt.can_print) as can_print, hd.is_active
			// 		from tb_modulemycompany_hd hd inner join tb_modulemycompany_dt dt on hd.module_id=dt.module_id 
			// 		where dt.role_id in (select role_id from tb_user_dt where UserEmail = '".$useremail."')
			// 		group by hd.module_id, hd.module_name, hd.controller, hd.module_type, hd.parent_module_id, hd.position, hd.is_active
			// 		order by hd.module_type, isnull(hd.parent_module_id,''), hd.position ";
			$qry = "Select hd.module_id, hd.module_name, hd.controller, hd.module_type, hd.parent_module_id, hd.position,
					max(dt.can_read) as can_read, max(dt.can_create) as can_create, max(dt.can_update) as can_update, 
					max(dt.can_delete) as can_delete, max(dt.can_print) as can_print, hd.is_active
					from tb_modulemycompany_hd hd inner join tb_modulemycompany_dt dt on hd.module_id=dt.module_id 
					where dt.role_id in (''";
			for ($i=0;$i<count($listrole);$i++) {
				$qry .= ",'".$listrole[$i]."'";
			}
			$qry .= ")
					group by hd.module_id, hd.module_name, hd.controller, hd.module_type, hd.parent_module_id, hd.position, hd.is_active
					order by hd.module_type, isnull(hd.parent_module_id,''), hd.position ";

			// die($qry);

			$res = $this->db->query($qry);
			if ($res->num_rows()>0)
				return $res->result();
			else
				return array();
		}

		//date('Y/m/d h:i:s A')
		function updateKode($KodeLama, $KodeBaru){
			
			$this->db->trans_start();

			$this->db->set('module_id', $KodeBaru);
			$this->db->where('module_id', $KodeLama);
			$this->db->update('tb_modulemycompany_dt');

			$this->db->set('parent_module_id', $KodeBaru);
			$this->db->where('parent_module_id', $KodeLama);
			$this->db->update('tb_modulemycompany_hd');

			$this->db->set('module_id', $KodeBaru);
			$this->db->where('module_id', $KodeLama);
			$this->db->update('tb_modulemycompany_hd');

			$this->db->trans_complete();
		}

		function updateStatus($moduleId,$isActive){
			$this->db->trans_start();

			$this->db->set('is_active',$isActive);
			$this->db->where('module_id',$moduleId);

			$this->db->update('tb_modulemycompany_hd');

			$result = $this->db->trans_status();
			if ($result === FALSE) {
			    // Transaksi gagal, lakukan rollback
			    $this->db->trans_rollback();
			} else {
			    // Transaksi berhasil, lakukan commit
			    $this->db->trans_commit();
			}
			return $result;
		}
	}
?>