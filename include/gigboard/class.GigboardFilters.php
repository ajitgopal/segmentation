<?php
  require_once("global.inc");
  require_once($app_inc_path."gigboard/class.Gigboard.php");


  class GigBoardFilters 
  {
    
    function __construct()
    {
      global $db;
      $this->deptAccessObj = new departmentAccess();
      $this->db = $db;
    }
    /*
      This function is used to get all the customers list
    */
    public function SelectAllCustomers()
    {
      
      $select = "SELECT sc.sno,CONCAT(sc.sno,' - ',sc.cname) AS comp_name FROM `staffacc_cinfo` sc  ORDER BY sc.cname ASC";
      $result = mysql_query($select,$this->db) or die(mysql_error());
      return $result;
    }
    /*
      This function is used to get all Active employee list
    */
    public function SelectAllEmployees()
    {
      $select = "SELECT sno,name, ".getEntityDispName("sno","name")." AS empso_name,username FROM emp_list WHERE lstatus != 'DA' AND lstatus != 'INACTIVE' AND (empterminated!='Y' || UNIX_TIMESTAMP(IF(tdate='' || tdate IS NULL,NOW(),tdate))>UNIX_TIMESTAMP(NOW())) ORDER BY name ASC";
      $result = mysql_query($select,$this->db);
      return $result;
    }
    /*
      This function is used to get all Active HRM Department by the username.
    */
    public function SelectHRMDepartments()
    {
      global $username;
      $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");
      $select = "SELECT sno,deptname FROM department WHERE status ='Active'AND sno !='0' AND sno IN (".$selDept.") ";
      $result = mysql_query($select,$this->db);
      return $result;
    }

    /*
      This function used to return the customers list when searching the customers in GigBoard Filters model.
    */
    public function getCustomersBySearch($searchval,$selectedCust)
    {
      global $username;

      $customerids = '';
      $scsearchval = $searchval['term'];
        if (!empty($selectedCust)) {
          $customerids = " AND sno NOT IN (".implode(",",$selectedCust).")";
        }
        $start = 10;
        if ($searchval['_type'] == "query") {
          $limit = 'LIMIT 0,10';
        }else if ($searchval['_type'] == "query:append") {
          $page = $searchval['page'];
          $startPage = ($start * $page);
          $endPage = ($startPage + 10);
          $limit = 'LIMIT '.$startPage.', 10';           
        }
        $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");
      $select = "SELECT sc.sno,CONCAT(sc.sno,' - ',sc.cname) AS comp_name FROM `staffacc_cinfo` sc LEFT JOIN Client_Accounts ca ON (ca.typeid = sc.sno) WHERE ca.deptid !='0' AND ca.deptid IN (".$selDept.") AND CONCAT(sc.sno,' - ',sc.cname) LIKE '%".$scsearchval."%' ".$customerids." GROUP BY sc.sno ORDER BY sc.cname ASC ".$limit." ";
      $result = mysql_query($select,$this->db) or die(mysql_error());      
      return $result;
    }

    public function getCustomersBySearchCount($searchval,$selectedCust){
      global $username;
      $customerids = '';
      $scsearchval = $searchval['term'];
        if (!empty($selectedCust)) {
          $customerids = " AND sc.sno NOT IN (".implode(",",$selectedCust).")";
        }
        $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");
      $select = "SELECT sc.sno,CONCAT(sc.sno,' - ',sc.cname) AS comp_name FROM `staffacc_cinfo` sc LEFT JOIN Client_Accounts ca ON (ca.typeid = sc.sno) WHERE ca.deptid !='0' AND ca.deptid IN (".$selDept.") AND CONCAT(sc.sno,' - ',sc.cname) LIKE '%".$scsearchval."%' ".$customerids." GROUP BY sc.sno ORDER BY sc.cname ASC ";
      $result = mysql_query($select,$this->db) or die(mysql_error());      
      return $result;
    }

    /*
      
    */
    public function getEssCustomersBySearch($searchval,$selectedCust)
    {
      global $username;
      $customerids = '';
      $scsearchval = $searchval['term'];
        if (!empty($selectedCust)) {
          $customerids = " AND sc.sno NOT IN (".implode(",",$selectedCust).")";
        }
        $start = 10;
        if ($searchval['_type'] == "query") {
          $limit = 'LIMIT 0,10';
        }else if ($searchval['_type'] == "query:append") {
          $page = $searchval['page'];
          $startPage = ($start * $page);
          $endPage = ($startPage + 10);
          $limit = 'LIMIT '.$startPage.', 10';           
        }
        $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");
      $select = "SELECT sc.sno,CONCAT(sc.sno,' - ',sc.cname) AS comp_name FROM `staffacc_cinfo` sc INNER JOIN hrcon_jobs hj ON (hj.client = sc.sno) LEFT JOIN Client_Accounts ca ON (ca.typeid = sc.sno) WHERE ca.deptid !='0' AND ca.deptid IN (".$selDept.") AND hj.username = '".$username."' AND CONCAT(sc.sno,' - ',sc.cname) LIKE '%".$scsearchval."%' ".$customerids." GROUP BY sc.sno ORDER BY sc.cname ASC ".$limit." ";
      $result = mysql_query($select,$this->db) or die(mysql_error());      
      return $result;
    }

    public function getEssCustomersBySearchCount($searchval,$selectedCust){
      global $username;
      $customerids = '';
      $scsearchval = $searchval['term'];
        if (!empty($selectedCust)) {
          $customerids = " AND sc.sno NOT IN (".implode(",",$selectedCust).")";
        }
        $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");
      $select = "SELECT sc.sno,CONCAT(sc.sno,' - ',sc.cname) AS comp_name FROM `staffacc_cinfo` sc INNER JOIN hrcon_jobs hj ON (hj.client = sc.sno) LEFT JOIN Client_Accounts ca ON (ca.typeid = sc.sno) WHERE ca.deptid !='0' AND ca.deptid IN (".$selDept.") AND hj.username = '".$username."' AND CONCAT(sc.sno,' - ',sc.cname) LIKE '%".$scsearchval."%' ".$customerids." GROUP BY sc.sno ORDER BY sc.cname ASC ";
      $result = mysql_query($select,$this->db);      
      return $result;
    }
    /*

    */
    public function getSelectedCustomers()
    {
      global $username;

      $select = "SELECT sc.sno,CONCAT(sc.sno,'-',sc.cname) AS comp_name
                  FROM gigboard_filters gf                  
                  LEFT JOIN gigboard_filters_cust gfc ON (gf.sno = gfc.gb_filter_id)
                  LEFT JOIN staffacc_cinfo sc ON (sc.sno = gfc.customer_sno)
                  WHERE gf.status ='1'
                  AND gf.username='".$username."'
                  AND gfc.customer_sno !='0'
                  ORDER BY sc.cname ASC";
      $result = mysql_query($select,$this->db);
      return $result;      
    }
    /*
      This function used to return the employees list when searching the employees in GigBoard Filters model.
    */
    public function getEmployeesBySearch($searchval,$selectedCust,$assign_status,$hrm_dept,$selected_empids)
    {
      global $username;

      $customerids = '';
      $empids = '';
      $select ='';
      $searchempval = $searchval['term'];
      if (!empty($selectedCust) && $selectedCust !="undefined") {
        if ($assign_status != "") {
          $customerids = " AND hj.client IN  (".implode(",",$selectedCust).")";
        }
      }
      $hj_hrm_dept_cond = '';

      if ($hrm_dept != "0") {
        $hj_hrm_dept_cond = "AND hj.deptid ='".$hrm_dept."'";

      }     
      if (!empty($selected_empids) && $selected_empids !="undefined") {
        $ids = "'" . implode ( "', '", $selected_empids ) . "'";
        $empids = " AND el.sno NOT IN (".$ids.")";
      }
      $start = 10;
      if ($searchval['_type'] == "query") {
        $limit = 'LIMIT 0,10';
      }else if ($searchval['_type'] == "query:append") {
        $page = $searchval['page'];
        $startPage = ($start * $page);
        $endPage = ($startPage + 10);
        $limit = 'LIMIT '.$startPage.', 10';           
      }
      $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");
      $select = "SELECT el.username AS empusername,el.sno AS empsno,CONCAT(el.sno,'-',TRIM(el.name)) AS emp_name 
                  FROM emp_list el
                  LEFT JOIN hrcon_compen hc ON hc.username = el.username
                  LEFT JOIN hrcon_jobs hj ON hj.username = el.username
                  LEFT JOIN staffacc_cinfo sc ON sc.sno = hj.client
                  WHERE hj.ustatus = '".$assign_status."' 
                  ".$customerids."
                  ".$empids."
                  ".$hj_hrm_dept_cond."
                  AND hc.dept !='0' AND hc.dept IN (".$selDept.")
                  AND el.lstatus != 'DA' 
                  AND el.lstatus != 'INACTIVE' 
                  AND el.empterminated!='Y'
                  AND CONCAT(el.sno,'-',TRIM(el.name)) LIKE '%".$searchempval."%'
                  GROUP BY el.sno
                  ORDER BY el.name ASC ".$limit." ";
      $result = mysql_query($select,$this->db);      
      return $result;
    }
public function getEmployeesBySearchCount($searchval,$selectedCust,$assign_status,$hrm_dept,$selected_empids)
    {
      global $username;

      $customerids = '';
      $empids = '';
      $select ='';
      $searchval = $searchval['term'];
      if (!empty($selectedCust) && $selectedCust !="undefined") {
        if ($assign_status != "") {
          $customerids = " AND hj.client IN  (".implode(",",$selectedCust).")";
        }
      }
      $hj_hrm_dept_cond = '';

      if ($hrm_dept != "0") {
        $hj_hrm_dept_cond = "AND hj.deptid ='".$hrm_dept."'";

      }    
      if (!empty($selected_empids) && $selected_empids !="undefined") {
        $ids = "'" . implode ( "', '", $selected_empids ) . "'";
        $empids = " AND el.sno NOT IN (".$ids.")";
      }

       $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");
      $select = "SELECT el.sno AS empsno 
                  FROM emp_list el
                  LEFT JOIN hrcon_compen hc ON hc.username = el.username
                  LEFT JOIN hrcon_jobs hj ON hj.username = el.username
                  LEFT JOIN staffacc_cinfo sc ON sc.sno = hj.client
                  WHERE hj.ustatus = '".$assign_status."' 
                  ".$customerids."
                  ".$empids."
                  ".$hj_hrm_dept_cond."
                  AND hc.dept !='0' AND hc.dept IN (".$selDept.")
                  AND el.lstatus != 'DA' 
                  AND el.lstatus != 'INACTIVE' 
                  AND el.empterminated!='Y'
                  AND CONCAT(el.sno,'-',TRIM(el.name)) LIKE '%".$searchval."%'
                  GROUP BY el.sno
                  ORDER BY el.name ASC";
      
      $result = mysql_query($select,$this->db);      
      return $result;
    } 
    public function getSelectedEmployees()
    {
      global $username;

      $select = "SELECT el.sno AS empsno,CONCAT(el.sno,'-',TRIM(el.name)) AS emp_name
                  FROM gigboard_filters gf 
                  LEFT JOIN gigboard_filters_emp gfe ON (gf.sno = gfe.gb_filter_id)                 
                  LEFT JOIN emp_list el ON (el.sno = gfe.emp_sno)
                  WHERE gf.status ='1'
                  AND gf.username='".$username."'
                  AND gfe.emp_sno !='0'
                  ORDER BY el.name";
      $result = mysql_query($select,$this->db);
      return $result;      
    }

    /*
      This function is used to get the GigBoard Filter data by username.
    */
    public function getGigBoardSummaryFilterData()
    {
      global $username;
      $setQry = "SET SESSION group_concat_max_len=1073740800";
      mysql_query($setQry,$this->db);
      $select = "SELECT gf.sno,gf.filter_name,gf.assign_status,gf.hrm_dept,
                  IF(gf.hrm_dept = '0', 'ALL', dept.deptname ) AS deptname,
                  gf.group_by,gf.date_mode,gf.startdate,gf.enddate,
                  GROUP_CONCAT(DISTINCT(gfe.emp_sno) ORDER BY el.name ASC) AS empids,
                  GROUP_CONCAT(DISTINCT(gfc.customer_sno) ORDER BY sc.cname ASC) AS customerids, 
                  GROUP_CONCAT(DISTINCT(CONCAT(el.sno,'-',TRIM(el.name))) ORDER BY el.name ASC) AS empsnoname,
                  GROUP_CONCAT(DISTINCT(CONCAT(sc.sno,'-',REPLACE(sc.cname,',',' '))) ORDER BY sc.cname ASC) AS customernames,
                  CASE 
                    WHEN gf.assign_status = 'active' THEN 'Active'
                    WHEN gf.assign_status = 'pending' THEN 'Needs Approval'
                    WHEN gf.assign_status = 'cancel' THEN 'Cancelled'
                    WHEN gf.assign_status = 'closed' THEN 'Closed'
                  END AS assignmentStatus
                  FROM gigboard_filters gf
                  LEFT JOIN gigboard_filters_emp gfe ON (gf.sno = gfe.gb_filter_id)
                  LEFT JOIN gigboard_filters_cust gfc ON (gf.sno = gfc.gb_filter_id)
                  LEFT JOIN emp_list el ON (el.sno = gfe.emp_sno)
                  LEFT JOIN staffacc_cinfo sc ON (sc.sno = gfc.customer_sno)
                  LEFT JOIN department dept ON (dept.sno = gf.hrm_dept)
                  WHERE gf.status ='1'
                  AND gf.username='".$username."'
                  GROUP BY gf.username
                  ORDER BY sc.cname,el.name ASC";

                $result = mysql_query($select,$this->db);
      return  $result;         
    }
    /*
      This function is used to SET the default SESSION or login user saved Filter SESSION
    */
    public function getFilterSESSIONdata()
    {
      global $username;
      $setQry = "SET SESSION group_concat_max_len=1073740800";
      mysql_query($setQry,$this->db);
      $checkUserFilters = "SELECT gf.sno FROM gigboard_filters gf WHERE gf.username='".$username."'";
      $checkresult = mysql_query($checkUserFilters,$this->db);
      if (mysql_num_rows($checkresult)>0) {
        //Saved Filters
        $select = "SELECT gf.sno,gf.filter_name,gf.assign_status,gf.hrm_dept,
                  gf.group_by,gf.date_mode,gf.startdate,gf.enddate,
                  GROUP_CONCAT(DISTINCT(gfe.emp_sno) ORDER BY el.name ASC) AS empids,
                  GROUP_CONCAT(DISTINCT(gfc.customer_sno) ORDER BY sc.cname ASC) AS customerids
                  FROM gigboard_filters gf
                  LEFT JOIN gigboard_filters_emp gfe ON (gf.sno = gfe.gb_filter_id)
                  LEFT JOIN gigboard_filters_cust gfc ON (gf.sno = gfc.gb_filter_id)
                  LEFT JOIN emp_list el ON (el.sno = gfe.emp_sno)
                  LEFT JOIN staffacc_cinfo sc ON (sc.sno = gfc.customer_sno)
                  WHERE gf.status ='1'
                  AND gf.username='".$username."'";

        $result = mysql_query($select,$this->db);
        while ($row = mysql_fetch_array($result)) {
            unset($_SESSION['AccGigFilters']);
            $_SESSION['AccGigFilters'] = array();
            $_SESSION['AccGigFilters']['GroupBy'] = $row['group_by'];
            $_SESSION['AccGigFilters']['DateViewMode'] = $row['date_mode'];
            if ($row['date_mode'] == "day") {
              $_SESSION['AccGigFilters']['DayDate'] = $row['startdate'];
            }else if ($row['date_mode'] == "week") {
              $_SESSION['AccGigFilters']['WeekStartDate'] = $row['startdate'];
              $_SESSION['AccGigFilters']['WeekEndDate'] = $row['enddate'];
            }else if ($row['date_mode'] == "month") {
              $_SESSION['AccGigFilters']['MonthDate'] = $row['startdate'];
            }
            $_SESSION['AccGigFilters']['Customers'] = $row['customerids'];
            $_SESSION['AccGigFilters']['Employees'] = $row['empids'];
            $_SESSION['AccGigFilters']['Assignment_Status'] = $row['assign_status'];
            $_SESSION['AccGigFilters']['HRM_Department'] = $row['hrm_dept'];
            $_SESSION['AccGigFilters']['SessionMode'] = "Saved";
        }
      }else{
        // Default Filters
        unset($_SESSION['AccGigFilters']);
        
        /*$deptSnoRes = $this->SelectHRMDepartments();
        $i = 0;
        while($deptSnoRow=mysql_fetch_array($deptSnoRes))
        {
          if($i==0)
          {
            $deptSno  = $deptSnoRow[0];
          }
          $i++;
        }*/
        $deptSno = 0;
        $_SESSION['AccGigFilters']['GroupBy']           = "customers";
        $_SESSION['AccGigFilters']['DateViewMode']      = "day";
        $_SESSION['AccGigFilters']['DayDate']           = date("d-m-Y");
        $_SESSION['AccGigFilters']['SessionMode']       = "Restore";
        $_SESSION['AccGigFilters']['Assignment_Status'] = 'active';
        $_SESSION['AccGigFilters']['HRM_Department']    = $deptSno;
      }
      
      return $_SESSION['AccGigFilters'];         
    }

    /*
      This Query is used to backup old saved filter and insert newly added filters.(username)
    */
    public function SaveFilters($data)
    {
      global $username;
      $selectGBF = "SELECT GROUP_CONCAT(DISTINCT(sno)) AS filterSno  FROM gigboard_filters WHERE username='".$data['username']."'";
      $result = mysql_query($delQry,$this->db);
      $row = mysql_fetch_assoc($result);
      $selfilterSno = $row['filterSno'];
      $delQry = " DELETE FROM gigboard_filters WHERE username='".$data['username']."'";
      mysql_query($delQry,$this->db);
      if ($selfilterSno != "") {        
      
        $delQry_cust = " DELETE FROM gigboard_filters_cust WHERE gb_filter_id IN (".$selfilterSno.") ";
        mysql_query($delQry_cust,$this->db);

        $delQry_emp = " DELETE FROM gigboard_filters_emp WHERE gb_filter_id IN (".$selfilterSno.") ";
        mysql_query($delQry_emp,$this->db);
      }

      $startdate='';
      $enddate='';
      if ($data['datetype'] == "day") {
        $startdate = date('Y-m-d',strtotime($data['date_range_day']));
        $enddate = '';
      }elseif ($data['datetype'] == "week") {
        $startdate=date('Y-m-d',strtotime($data['date_range_weekS']));
        $enddate=date('Y-m-d',strtotime($data['date_range_weekE']));
      }elseif ($data['datetype'] == "month") {
        $date_month = explode("/",$data['date_range_month']);
        $date = date_parse($date_month[0]);
        $startdate = date('Y-m-d',strtotime($date['month'].'/01/'.$date_month[1]));
      }
      $insert = "INSERT INTO gigboard_filters (username,assign_status,hrm_dept,group_by,date_mode,startdate,enddate,status,cdate,mdate) VALUES ('".$data['username']."','".$data['assign_status']."','".$data['hrm_dept']."','".$data['groupby']."','".$data['datetype']."','".$startdate."','".$enddate."','1',NOW(),NOW())";
      mysql_query($insert,$this->db);
      $filter_id = mysql_insert_id($this->db);

      if (isset($data['customers']) && count($data['customers'])>0) {
        foreach ($data['customers'] as $customer) {
          $insert_cust = "INSERT INTO gigboard_filters_cust (gb_filter_id,customer_sno) VALUES ('".$filter_id."','".$customer."')";
         mysql_query($insert_cust,$this->db);
        }
      }else{
        $insert_cust = "INSERT INTO gigboard_filters_cust (gb_filter_id,customer_sno) VALUES ('".$filter_id."','0')";
        mysql_query($insert_cust,$this->db);
      }
      
      if (isset($data['employees']) && count($data['employees'])>0) {
        foreach ($data['employees'] as $employee) {
          $insert_emp = "INSERT INTO gigboard_filters_emp (gb_filter_id,emp_sno) VALUES ('".$filter_id."','".$employee."')";
         mysql_query($insert_emp,$this->db);
        }
      }else{
        $insert_emp = "INSERT INTO gigboard_filters_emp (gb_filter_id,emp_sno) VALUES ('".$filter_id."','0')";
        mysql_query($insert_emp,$this->db);
      }
      return $filter_id;
    }

    /*
      This Query is used to delete old filters update Default Filters.(username)
    */
    public function restoreToDefaultFilters()
    {
      global $username;
      $selfilterSno = "";
      $selectGBF = "SELECT GROUP_CONCAT(DISTINCT(sno)) AS filterSno  FROM gigboard_filters WHERE username='".$username."'";
      $result = mysql_query($delQry,$this->db);
      $row = mysql_fetch_assoc($result);
      $selfilterSno = $row['filterSno'];

      $backup = "DELETE FROM gigboard_filters WHERE username='".$username."'";
      mysql_query($backup,$this->db);
      if ($selfilterSno != "") {        
      
        $delQry_cust = " DELETE FROM gigboard_filters_cust WHERE gb_filter_id IN (".$selfilterSno.") ";
        mysql_query($delQry_cust,$this->db);

        $delQry_emp = " DELETE FROM gigboard_filters_emp WHERE gb_filter_id IN (".$selfilterSno.") ";
        mysql_query($delQry_emp,$this->db);
      }
      $deptSno  = 0;
      /*$deptSnoRes = $this->SelectHRMDepartments();
        $i = 0;
        while($deptSnoRow=mysql_fetch_array($deptSnoRes))
        {
          if($i==0)
          {
            $deptSno  = $deptSnoRow[0];
          }
          $i++;
        }*/
      
      $insert = "INSERT INTO gigboard_filters (username,assign_status,hrm_dept,group_by,date_mode,startdate,status,cdate,mdate) VALUES ('".$username."','active','".$deptSno."','customers','day',CURDATE(),'1',NOW(),NOW())";
      mysql_query($insert,$this->db);
      $filter_id = mysql_insert_id($this->db);
      $insert_cust = "INSERT INTO gigboard_filters_cust (gb_filter_id,customer_sno) VALUES ('".$filter_id."','0')";
      mysql_query($insert_cust,$this->db);

      $insert_emp = "INSERT INTO gigboard_filters_emp (gb_filter_id,emp_sno) VALUES ('".$filter_id."','0')";
      mysql_query($insert_emp,$this->db);

    }
    /*
      This Query is used display filter popup on landing page wheter the filters doesnot exist on the table
    */
    public function selGigboardFilters()
    {
        global $username;

        $selectQry = "SELECT count(*) AS filterCount FROM gigboard_filters WHERE username='".$username."'";
        $selectRes = mysql_query($selectQry,$this->db);
        $selectRow = mysql_fetch_array($selectRes);
        $count     = $selectRow[0];

        if($count==0)
        {
          return "Y";
        }
        else
        {
          return "N";
        }   
    }

    public function checkCustEmpSelected()
    {
      global $username;
      $output = "no";
      $sel_cust = "";
      $sel_emp = "";
      $sel_cust = "SELECT gfc.sno FROM gigboard_filters_cust gfc
                  JOIN gigboard_filters gf ON (gf.sno = gfc.gb_filter_id)
                  WHERE gf.username = '".$username."' AND gfc.customer_sno !='0'";
      $result_cust = mysql_query($sel_cust,$this->db);
      if (mysql_num_rows($result_cust)>0) {
        if (mysql_num_rows($result_cust)>1) {
         $output = "no^cust";
        }else{
          $output = "yes^cust";
        }
      }else{
        $sel_emp = "SELECT gfe.sno FROM gigboard_filters_emp gfe
                  JOIN gigboard_filters gf ON (gf.sno = gfe.gb_filter_id)
                  WHERE gf.username = '".$username."' AND gfe.emp_sno !='0'";
        $result_emp = mysql_query($sel_emp,$this->db);
        if (mysql_num_rows($result_emp)>0) {
          if (mysql_num_rows($result_emp)>1) {
            $output = "no^emp";
          }else{
            $output = "yes^emp";
          }
        }else{
          $output = "no^emp";
        }
      }
      if ($sel_emp != "" && $sel_cust != "") {
        if ((mysql_num_rows($result_emp)== 0) && (mysql_num_rows($result_cust)== 0)) {
          $output = "no";
        }
      }
      
      return $output;
    }

    public function getSelectedEssCustomers($selCustids)
    {
      global $username;

      $select = "SELECT sc.sno,CONCAT(sc.sno,'-',sc.cname) AS comp_name
                  FROM staffacc_cinfo sc
                  WHERE sc.sno IN (".$selCustids.")
                  ORDER BY sc.cname ASC";
      $result = mysql_query($select,$this->db);
      return $result;      
    }

  }
	?>
