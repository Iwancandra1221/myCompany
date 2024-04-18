<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

function CleanString($someString)
{
	$someString = str_replace("\t",'',$someString);
	$someString = str_replace("\r",'',$someString);
	$someString = str_replace("\n",'',$someString);
	return $someString;
}

function BuildStringid($string_id,$addition = '')
{
	$string_id = strtolower(str_replace(' ','-',$string_id.$addition));
	$string_id = strtolower(str_replace('%','',$string_id));
	$string_id = strtolower(str_replace('(','',$string_id));
	$string_id = strtolower(str_replace(')','',$string_id));
	$string_id = strtolower(str_replace('/','',$string_id));
	$string_id = strtolower(str_replace('+','',$string_id));
	$ret = '';

	for($i=0;$i<strlen($string_id);$i++)
	{
		if(is_numeric($string_id[$i]))
		{
			$ret .= $string_id[$i];
		}
		if($string_id[$i] == '-')
		{
			$ret .= $string_id[$i];
		}
		if($string_id[$i] >= 'A' && $string_id[$i] <= 'Z')
		{
			$ret .= $string_id[$i];
		}
		if($string_id[$i] >= 'a' && $string_id[$i] <= 'z')
		{
			$ret .= $string_id[$i];
		}
	}
	return $ret;
}

function GenerateMenus($arr,$navActive)
{
	foreach($arr as $a)
	{
		$link = '#';
		$class = 'class="hasChild"';
		$active = '';
		$arrow = '<i class="fa fa-angle-down arrow"></i>';
		if(!isset($a->child) || (isset($a->child) && empty($a->child)))
		{
			$class = '';
			$arrow = '';
			if($a->link != '#')
				$link = site_url($a->link);
		}
		if($navActive == $a->link)
		{
			if($class == '')
				$class = 'class="active"';
		}
		echo '<li '.$class.' menu="'.$a->menu_id.'">';
		echo '<a href="'.$link.'" menu="'.$a->menu_id.'"><i class="fa '.$a->class_menu.'"></i>'.$a->menu_name.' '.$arrow.'</a>';
		if(isset($a->child) && !empty($a->child))
		{
			echo '<ul parent="'.$a->menu_id.'">';
			GenerateMenus($a->child,$navActive);
			echo '</ul>';
		}
		echo '</li>';
	}
}
	
function DatatableQuery($param, $sTable, $aColumns, $sWhere, $show_no = 1)
{
		$xColumns = array();
		foreach($aColumns as $col){
			$xColumns[] = current(preg_split('/ as /i', $col));  //hapus alias kolom
		}
		
		//Ordering
		$sOrder = "";
		if (isset($param['order']))
		{
			$idx = max(($param['order'][0]['column']) - ($show_no),0);
			$sOrder = " ORDER BY ".$xColumns[$idx]." ".(ISSET($param['order'][0]['dir'])?$param['order'][0]['dir']:'ASC');
		}
		else{
			$curColumn = current(explode(' as ', $xColumns[0]));  //hapus alias kolom
			$sOrder = " ORDER BY ".$curColumn." ASC";
		}
		
		//Filtering
		$sFiltering = "";
		if ( isset($param['search']) && $param['search']['value'] != "" )
		{
			$sFiltering = " WHERE (";
			for ( $i=0 ; $i<count($xColumns) ; $i++ )
			{
				if ( isset($param['columns'][$i]['searchable']) && $param['columns'][$i]['searchable'] == "true" )
				{
					$sFiltering .= "".$xColumns[$i]." LIKE '%".$param['search']['value']."%' OR ";
				}
			}
			$sFiltering = substr_replace( $sFiltering, "", -3 );
			$sFiltering .= ')';
		}
		
		/* Individual column filtering */
		for ( $i=0 ; $i<count($xColumns) ; $i++ )
		{
			if ( isset($param['columns'][$i]['searchable']) && $param['columns'][$i]['searchable'] == "true" && $param['columns'][$i]['search']['value'] != '' )
			{
				if ( $sFiltering == "" )
				{
					$sFiltering = "WHERE ";
				}
				else
				{
					$sFiltering .= " AND ";
				}
				$sFiltering .= "".$xColumns[$i]." LIKE '%".$param['columns'][$i]['search']['value']."%' ";
			}
		}
		
		if($sWhere!=''){
			$sWhere = (($sFiltering!='') ? ' AND ' : ' WHERE ').' '.$sWhere;
		}

		$start = $param['start'];
		$end = $start + ($param['length']) + 1 ;
		$sQueryFiltered = "
			SELECT ".implode(", ",$aColumns)."
			FROM   $sTable
			$sFiltering
			$sWhere
			$sOrder
			";
		if(($param['length'])>0){
		$sQueryFiltered .= "
			OFFSET ".$param['start']." ROWS 
			FETCH NEXT ".$param['length']." ROWS ONLY ";
		}
		$sQueryTotal = "
			SELECT COUNT(*) as total FROM (SELECT ".implode(", ",$aColumns)."
			FROM $sTable
			$sFiltering
			$sWhere) as x
		";
		$query = array("sQueryFiltered" => $sQueryFiltered,"sQueryTotal" => $sQueryTotal);
		return $query;
}
function WhereStr($where){
	$str = "";
	$operators = ['>=', '<=', '>', '<', '=', '<>'];
	if($where!=null){
		$str = "WHERE ";
		foreach($where as $key => $value){
			
			if($value=='null'){
				$str .= $key." IS NULL AND ";
			}
			else if($value=='not null'){
				$str .= $key." IS NOT NULL AND ";
			}
			else{
				// $key = "table.column >";
				$key = str_replace("\n", "", $key);
				$key = str_replace("\r", "", $key);
				$key = str_replace("\t", " ", $key);
				$pattern = '/(' . implode('|', array_map('preg_quote', $operators)) . ')/';
				$key_array = preg_split($pattern, $key, -1, PREG_SPLIT_DELIM_CAPTURE);

				//$key_array = explode(" ",$key);
				if(count($key_array)==1){
					$str .= $key_array[0];
					if($value!=''){
						$str =" = '".$value."' AND ";
					}
				}
				else{
					$str .= $key_array[0]." ".$key_array[1]." '".$value."' AND ";
				}
			}
			
		}
		$str = rtrim($str," AND ");
	}
	return $str;
	
}