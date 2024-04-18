<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

    function InitView($data = array())
    {
        /*$lang = $this->session->userdata('language');
        if($lang != 'mandarin')*/
        $lang = 'english';
        $this->lang->load('general',$lang);

        if(!isset($data['title']))
        {
            $data['title'] = 'Browse Data';
        }

        $controller = $this->uri->segment(1);
        $method = $this->uri->segment(2);

        $exclude = array('login','logout');
        if(!in_array($controller,$exclude))
        {
            if($this->session->userdata('uid') == '')
            {
                redirect('logout');
            } else {
                //die("lanjut");
            }
        }
        $this->session->set_userdata('last_url',$this->uri->uri_string.'?'.$_SERVER['QUERY_STRING']);
        $this->RenderHeader($data);
        $this->RenderNav($data);
        $this->RenderFooter($data); 
    }

    public function RenderNav($data = array())
    {
        /*$lang = $this->session->userdata('language');
        if($lang != 'mandarin')*/
        $lang = 'english';
        $this->lang->load('general',$lang);
        $this->template->write_view('navigation', 'template/navigation', $data);
    }

	public function RenderHeader($data = array())
	{
		$this->template->write_view('header', 'template/header', $data);
	}

    public function CreateNav($arr,$curr)
    {
        $res = array();
        if(!empty($arr))
        {
            foreach($arr as $k=>$a)
            {
                if($a->parent_id == $curr)
                {
                    $m = $a;
                    unset($arr[$k]);
                    $m->child = $this->CreateNav($arr,$a->menu_id);
                    $res[] = $a;
                }
            }
        }
        return $res;
    }

	public function RenderFooter($data = array())
	{
		$this->template->write_view('footer', 'template/footer', $data);
	}

	public function RenderView($str,$data= array())
	{
        $this->InitView($data);
		$this->template->write_view('content', $str, $data);
		$this->template->render();
	}

    public function SetTemplate($file = '')
    {
        $this->template->set_master_template($file);
    }

	public function RenderSubView($subtemp,$str,$data= array())
	{
        $this->InitView($data);
		$this->template->write_view('content', $subtemp, $data);
		$this->template->render();
	}

	function Sanitize($someString)
 	{
  		return $this->db->escape_str(htmlspecialchars(stripslashes(trim($someString)), ENT_QUOTES));
 	}

    function PopulatePost()
    {
        $data = array();
        foreach($_POST as $key => $value)
        {
            if($key != 'btnSubmit' )
                if(!is_array($value))
                    $data[$key] = $this->Sanitize($value);
                else
                    $data[$key] = $value;
        }
        return $data;
    }

    function return_empty()
    {    
        $output = array(
            "sEcho" => 0,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => array()
        );
        return array('output'=>$output,'datares'=>array() );
    }
    
    /*** For MS SQL ***/
     function GetData($aColumns = array(),$sIndexColumn = '',$sTable ='',$add_where = '')
     {
         if(empty($aColumns) || $sIndexColumn == '' || $sTable == '')
             return $this->return_empty();
         $sLimit = "";
         if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
         {
            $start = $this->input->get('iDisplayStart');
            $end = $start + $this->input->get('iDisplayLength');
            $sLimit = " row_number > ".$start.' AND row_number <= '.$end;
         }
        
         $sOrder = '';
         if ( isset( $_GET['iSortCol_0'] ) )
         {
            
             $sOrder = "ORDER BY  ";

             for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
             {
                 if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                 {
                    
                     $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
                     ".$this->db->escape_str( $_GET['sSortDir_'.$i] ) .", ";
                 }
             }
            
             $sOrder = substr_replace( $sOrder, "", -2 );
             if ( $sOrder == "ORDER BY" )
             {
                 $sOrder = "";
             }
         }
        
         $sWhere = "";
         if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
         {
             $sWhere = "WHERE (";
             for ( $i=0 ; $i<count($aColumns) ; $i++ )
             {
                 //check for date field start here
                 $datefield = array('last_login','start_date','create_date');
                 if(in_array($aColumns[$i],$datefield))
                     $sWhere .= "DATE_FORMAT(".$aColumns[$i].",'%d %b %Y') LIKE '%".$this->db->escape_str( $_GET['sSearch'] )."%' OR ";
                 else          
                     $sWhere .= $aColumns[$i]." LIKE '%".$this->db->escape_str( $_GET['sSearch'] )."%' OR ";
                 //end here
             }
             $sWhere = substr_replace( $sWhere, "", -3 );
             $sWhere .= ')';
         }
        
         /* Individual column filtering */
         for ( $i=0 ; $i<count($aColumns) ; $i++ )
         {
             if ( isset($_GET['bSearchable_'.$i]) && ($_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ))
             {
                 if ( $sWhere == "" )
                 {
                     $sWhere = "WHERE ";
                 }
                 else
                 {
                     $sWhere .= " AND ";
                 }
                $sWhere .= $aColumns[$i]." LIKE '%".$this->db->escape_str($_GET['sSearch_'.$i])."%' ";
            }
        }
        if($add_where != '')
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= " ".$add_where." ";
        }
        
        $sLimit = ' WHERE '.$sLimit;
        /*
         * SQL queries
         * Get data to display
         */
        $order = $sIndexColumn;
        if($sOrder != '')
        {
            $order = str_replace("ORDER BY", "", $sOrder).','.$sIndexColumn;
        }
        $sQuery = "
            SELECT * FROM (
            SELECT ".str_replace(" , ", " ", implode(", ", $aColumns)).", ROW_NUMBER() OVER (ORDER BY ".$order.") as row_number
            FROM   $sTable $sWhere) src
            $sLimit
            $sOrder
        ";
       
        
        $datares = $this->db->query($sQuery);
        
        /* Data set length after filtering */
        $sQuery = "
                SELECT COUNT(".$sIndexColumn.") as count
                FROM   $sTable ".$sWhere;
        $datares2 = $this->db->query($sQuery);
        
        $iFilteredTotal = $datares2->row()->count;
        
        /* Total data set length */
        
        if($add_where != '')
        {
            $sQuery = "
                SELECT COUNT(".$sIndexColumn.") as count
                FROM   $sTable WHERE ".$add_where;
        }
        else
        {
            $sQuery = "
                SELECT COUNT(".$sIndexColumn.") as count
                FROM   $sTable ";
        }
        
        $datacount = $this->db->query($sQuery);
        $iTotal = $datacount->row()->count;
        
        
        /*
         * Output
         */
        $output = array(
            "sEcho" => isset($_GET['sEcho']) ? intval($_GET['sEcho']) : 0,
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
                
        return array('output'=>$output,'datares'=>$datares);
    }

    /*** MySQL ***/


    // function getdata($aColumns = array(),$sIndexColumn = '',$sTable ='',$add_where = '')
    // {
    //     if(empty($aColumns) || $sIndexColumn == '' || $sTable == '')
    //         return $this->return_empty();
    //     $sLimit = "";
    //     if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    //     {
    //         $sLimit = "LIMIT ".( $_GET['iDisplayStart'] ).", ".
    //         ( $_GET['iDisplayLength'] );
    //     }

    //     /*
    //     * Ordering
    //     */
    //     $sOrder = '';
    //     if ( isset( $_GET['iSortCol_0'] ) )
    //     {
    //         $sOrder = "ORDER BY  ";
    //         for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
    //         {
    //             if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
    //             {
    //                 $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
    //                 ".( $_GET['sSortDir_'.$i] ) .", ";
    //             }
    //         }

    //         $sOrder = substr_replace( $sOrder, "", -2 );
    //         if ( $sOrder == "ORDER BY" )
    //         {
    //             $sOrder = "";
    //         }
    //     }

    //     $sWhere = "";
    //     if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
    //     {
    //         $sWhere = "WHERE (";
    //         for ( $i=0 ; $i<count($aColumns) ; $i++ )
    //         {
    //             //check for date field start here
    //             $datefield = array('last_login','start_date','create_date');
    //             if(in_array($aColumns[$i],$datefield))
    //             $sWhere .= "DATE_FORMAT(".$aColumns[$i].",'%d %b %Y') LIKE '%".( $_GET['sSearch'] )."%' OR ";
    //             else          
    //             $sWhere .= $aColumns[$i]." LIKE '%".( $_GET['sSearch'] )."%' OR ";
    //             //end here
    //         }
    //         $sWhere = substr_replace( $sWhere, "", -3 );
    //         $sWhere .= ')';
    //     }

    //     /* Individual column filtering */
    //     for ( $i=0 ; $i<count($aColumns) ; $i++ )
    //     {
    //         if ( isset($_GET['bSearchable_'.$i]) && ($_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ))
    //         {
    //             if ( $sWhere == "" )
    //             {
    //                 $sWhere = "WHERE ";
    //             }
    //             else
    //             {
    //                 $sWhere .= " AND ";
    //             }
    //             $sWhere .= $aColumns[$i]." LIKE '%".($_GET['sSearch_'.$i])."%' ";
    //         }
    //     }
    //     if($add_where != '')
    //     {
    //         if ( $sWhere == "" )
    //         {
    //             $sWhere = "WHERE ";
    //         }
    //         else
    //         {
    //             $sWhere .= " AND ";
    //         }
    //         $sWhere .= " ".$add_where." ";
    //     }
    //     /*
    //     * SQL queries
    //     * Get data to display
    //     */
    //     $sQuery = "
    //         SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
    //         FROM   $sTable
    //         $sWhere
    //         $sOrder
    //         $sLimit
    //         ";

    //     $datares = $this->db->query($sQuery);

    //     /* Data set length after filtering */


    //     $sQuery = "
    //         SELECT FOUND_ROWS() as count
    //         ";

    //     $datacount = $this->db->query($sQuery);
    //     $iFilteredTotal = $datacount->row()->count;

    //     /* Total data set length */

    //     if($add_where != '')
    //     {
    //         $sQuery = "
    //         SELECT COUNT(".$sIndexColumn.") as count
    //         FROM   $sTable ".$sWhere;
    //     }
    //     else
    //     {
    //         $sQuery = "
    //         SELECT COUNT(".$sIndexColumn.") as count
    //         FROM   $sTable ";
    //     }

    //     $datacount = $this->db->query($sQuery);
    //     $iTotal = $datacount->row()->count;


    //     /*
    //     * Output
    //     */
    //     $output = array(
    //         "sEcho" => isset($_GET['sEcho']) ? intval($_GET['sEcho']) : 0,
    //         "iTotalRecords" => $iTotal,
    //         "iTotalDisplayRecords" => $iFilteredTotal,
    //         "aaData" => array()
    //     );

    //     return array('output'=>$output,'datares'=>$datares);
    // }
}

/* End of file masterTemplate.php */
/* Location: ./application/controllers/masterTemplate.php */