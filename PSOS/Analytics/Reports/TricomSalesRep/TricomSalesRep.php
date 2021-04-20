<?php
ob_start();
require_once ('global_reports.inc');

require_once ('rlib.inc');

require_once ('functions.inc.php');
$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
global $accountingExport;

if (empty($format)) {
  $format = 'html';
}

$rlib_filename = 'TricomSalesRep.xml';

if ($rptFrom != "0" && $rptFrom != "") {
  $module = "TricomSalesRep" . $rptFrom;
}
else $module = "TricomSalesRep";

/*if ($view == "myreport") {
  $rquery = "select reportoptions from reportdata where reportid='$id'";
  $rresult = mysql_query($rquery, $db);
  $vrowdata = mysql_fetch_row($rresult);
  $vrow = explode("|username->", $vrowdata[0]);
  $TricomSalesRep = $vrow[0];
  $cusername = $vrow[1];
  if (strpos($TricomSalesRep, "|username->") != 0) $TricomSalesRep = $vrow[0];
  session_update("cusername");
  session_update("TricomSalesRep");
}*/
if($defaction == "print")
{
  $cusername  = $username;
  session_register("cusername");
}
$frm_values = explode('|', $TricomSalesRep);

    $headers =  array('jobtitle' => 'Job Title','AssignmentId' => 'Assignment ID','emp_id' => 'Sales Rep','department' => 'HRM Departments','modifieddate' => 'Last Exported Date');
    $filternames = explode('|', $filter_names);
$decimalPref = getDecimalPreference();
$dept = $frm_values[0];

//Where condition for customers selected in filter
$where_cond = "";

$joborderid = explode('-', $frm_values[0]);
$j_id = $joborderid[0];
$j_title = $joborderid[1];
$assignid =  $frm_values[1];
$employee_id = explode('-', $frm_values[2]);
$e_id = $employee_id[0];
$dept_id  = str_replace('ALL,', '', $frm_values[3]);
$last_exp_date  = $frm_values[4];

if($dept_id!= "")
{
  //Getting count of the department
  $getdept_count  = "SELECT count(sno) FROM department WHERE status='Active' AND sno !='0' AND sno IN ({$deptAccesSno})";
  $getdept_query  = mysql_query($getdept_count,$db);
  $getdept_result = mysql_fetch_row($getdept_query);

  //Getting names of department to display on the report
  $getdepartments_name = "SELECT GROUP_CONCAT(deptname) as department,count(sno) FROM department WHERE sno IN(" . $dept_id . ")";
  $dept_query = mysql_query($getdepartments_name, $db);
  $dept_name = mysql_fetch_row($dept_query);
  $dept_array = explode(",",$dept_name[0]);

  if($dept_name[1] == $getdept_result[0])
  {
    $dept_name = array("0"=>"ALL");
  }
}else{
    $dept_id = $deptAccesSno;
}

// Report ids
$jo_id[0] = 'jo_id';
$ass_id[0] = 'ass_id';
$salesrep_id[0] = 'salesrep_id';
$fname[0] = 'fname';
$lname[0] = 'lname';
$comm[0] = 'comm';

// Report Heading Names

$jo_id[1] = 'JobOrderID';
$ass_id[1] = 'AssignmentID';
$salesrep_id[1] = 'SalesRepID';
$fname[1] = 'FirstName';
$lname[1] = 'LastName';
$comm[1] = 'Commission';

// Report Heading Seperations
$jo_id[2] = '----------------';
$ass_id[2] = '----------------';
$salesrep_id[2] = '----------------';
$fname[2] = '----------------';
$lname[2] = '----------------';
$comm[2] = '----------------'; 

$sortarr = array(
  'jo_id', 'ass_id', 'salesrep_id', 'fname', 'lname', 'comm');
$arr_count    = count($sortarr);
$rep_company  = $companyname.'<br>';
$rep_header   = 'Tricom SalesRep Report';//report title
$rep_date   = 'date';

$custalign = '';
$custalignspaces = '';

if($last_exp_date!='' ){
  $custalign = '<br>';
  $export_date = "success";
}

if($dept_name[0]!='')
{
   $custalign = '<br>';
}
if($cust_name!='')
{
    $custalign = '<br>';
}
else
{
  $cust_name = 'ALL';
}
//Displaying selected filters for Run report

$Jobtitle       =  'Job Title: '.$j_title.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$AssignmentId   =  'Assignment ID: '.$assignid.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$SalesRepID     =  'Sales Rep: '.$frm_values[2].$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$Department     =  $dept_name[0] ? 'HRM Departments: '.$dept_name[0].$custalign."&nbsp;&nbsp;&nbsp;&nbsp;": '';
$LastExpDate    =  'Last Exported Date: '.$last_exp_date.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$rep_title       =  $Jobtitle.$AssignmentId.$SalesRepID.$Department.$LastExpDate;
$k = 0;


if($j_id !="")
{
  $where_cond .= " AND p.posid = ".$j_id;
}
if($assignid !="")
{
  $where_cond .= " AND hj.pusername = '".$assignid."' ";
}
if($e_id !="")
{
  $where_cond .= " AND re.sno = ".$e_id;
}
if($dept_id !="")
{
  $where_cond .= " AND hj.deptid !='0' AND hj.deptid IN(".$dept_id.")";  
}
if($last_exp_date !="")
{
  //$where_cond .= " AND str_to_date(hj.mdate,'%Y-%m-%d') >= str_to_date('".date("Y-m-d", strtotime($last_exp_date))."','%Y-%m-%d')"; 
  $where_cond .= " AND hj.mdate >= '".date("Y-m-d H:i:s", strtotime($last_exp_date))."' ";
  
}
// Array for displaying heading for all the columns selected

for ($q = 0; $q < $arr_count; $q++) {
  $variable = $$sortarr[$q];
  if (!empty($variable[0])) {
    $data[0][$k] = $variable[0];
    $headval[0][$k] = $variable[1];
    $headval[1][$k] = $variable[2];
    $k++;
  }
}

if ($k != 0) {
  $data[0][$k] = 'link';
  $k++;
  $data[0][$k] = 'link_length';
}


$setQry = "SET SESSION group_concat_max_len=1073740800";
mysql_query($setQry,$db);

$final_query = "(SELECT 
hj.posid AS JoborderId,
REPLACE(hj.pusername,'ASGN','') AS AssignmentId,
IF(ac.type='E',re.sno,'') AS SalesRepID,
IF(ac.type='E',hg.fname,'') AS FirstName,
IF(ac.type='E',hg.lname,'') AS LastName,
IF(ac.co_type='%',ac.amount,0) AS Commission
FROM posdesc p
INNER JOIN hrcon_jobs hj ON (hj.posid=p.posid)
INNER JOIN assign_commission ac ON (hj.sno=ac.assignid AND ac.assigntype='H' and ac.type='E')
INNER JOIN emp_list re ON (ac.person=re.username)
LEFT JOIN hrcon_general hg ON (hg.username = re.username AND hg.ustatus = 'active')
WHERE 1 = 1
".$where_cond." AND
re.lstatus NOT IN ('DA','INACTIVE') AND
hj.ustatus IN ('active','closed','cancel','pending') 
ORDER BY hj.posid DESC)

UNION ALL

(SELECT 
p.posid AS JoborderId,
REPLACE(hj.pusername,'ASGN','') AS AssignmentId,
IF(ac.type='E',re.sno,'') AS SalesRepID,
IF(ac.type='E',hg.fname,'') AS FirstName,
IF(ac.type='E',hg.lname,'') AS LastName,
IF(ac.co_type='%',ac.amount,0) AS Commission
FROM posdesc p
LEFT JOIN hrcon_jobs hj ON (hj.posid=p.posid)
LEFT JOIN assign_commission asgn_ac ON (hj.sno=asgn_ac.assignid AND asgn_ac.assigntype='H' and asgn_ac.type='E')
INNER JOIN assign_commission ac ON (p.posid=ac.assignid AND ac.assigntype='JO' AND ac.type='E')
INNER JOIN emp_list re ON (ac.person=re.username)
LEFT JOIN hrcon_general hg ON (hg.username = re.username AND hg.ustatus = 'active')
WHERE 1 = 1
".$where_cond." AND
re.lstatus NOT IN ('DA','INACTIVE')
AND asgn_ac.sno IS NULL
AND re.sno IS NOT NULL
ORDER BY p.posid DESC)";

$final_res = mysql_query($final_query, $db);
$final_count = mysql_num_rows($final_res);
$timesheet_sno = array();
$timesheet_parid = array();
$expense_sno = array();
$expense_parid = array();
$regular_rates = array();
$final_data = array();

while ($row = mysql_fetch_array($final_res)) {
  $final_data[] = $row;
  
}
// Form the report data

$i = 1;

foreach($final_data as $final_arr) {
  $ii = 0;
 

  // Array for all column's data

  $values_array = array(
      'jo_id' => ($final_arr['JoborderId']!= '' ? substr($final_arr['JoborderId'], 0, 8) : ''),
      'ass_id' => ($final_arr['AssignmentId']!= '' ? substr($final_arr['AssignmentId'], 0, 10) : ''),
      'salesrep_id' => ($final_arr['SalesRepID']!= '' ? substr($final_arr['SalesRepID'], 0, 9) : ''),
      'fname' => ($final_arr['FirstName']!= '' ? substr($final_arr['FirstName'], 0, 15) : ''),
      'lname' => ($final_arr['LastName']!= '' ? substr($final_arr['LastName'], 0, 30) : ''),
      'comm' => ($final_arr['Commission']!= '' ? substr($final_arr['Commission'], 0, 8) : ''),
  );
  for ($q = 0; $q <= $arr_count; $q++) {
    $variable = $$sortarr[$q];
    if (!empty($variable[0])) {
      $data[$i][$ii] = $values_array[$sortarr[$q]];
      $sslength_array[$sortarr[$q]] = trim((strlen($values_array[$sortarr[$q]]) <= strlen($variable[2])) ? strlen($values_array[$sortarr[$q]]) : (strlen($variable[2]) + 3));
      $ii++;
    }
  }

  if ($arr_count) {
    $slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
  }

  $data[$i][$ii] = '';
  $ii++;
  $i++;
}

$data = cleanArray($data);

if (empty($data)) {
  $data = array();
  $headval = array();
  $data[0][0] = '';
  $headval[0][0] = '';
}


$dateval = date('Ymd');
$timeval = date('His', time()); 
$meridian = date('A', time());

if(($j_id!= '' && $j_id!= 'ALL') || ($assign_id!= '' && $assign_id!= 'ALL') || ($e_id!='' && $e_id!='ALL')|| ($dept_name[0]!= 'ALL' && $dept_name[0]!= '')){
    $file = 'Tricom-SalesRep-' . $dateval.'-'.$timeval.'-'.$meridian.'-Filtered.' . $format;
}else{
    $file = 'Tricom-SalesRep-' . $dateval.'-'.$timeval.'-'.$meridian.'.' . $format;
}

$mime = 'application/' . $format;
$heading_names = array(
  'JobOrderID','AssignmentID','SalesRepID','FirstName','LastName','Commission');

if ($format == 'txt' || $format == 'csv') {
  
  header('Pragma: public');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Cache-Control: public');
  header("Content-Type: $mime; name=$file");
  header('Content-Description: File Transfer');
  header("Content-Disposition: attachment; filename=$file");
  header('Content-Transfer-Encoding: binary');
  $header_count = count($headval[0]);
  for ($t = 0; $t <= $header_count; $t++) {
    $data[0][$t] = trim($headval[0][$t]);
  }

  array_shift($data);
  print implode(',', $heading_names) . "\r\n";
  foreach($data as $row) {
    $row = array_slice($row, 0, count($row) - 1);
    print '"' . stripslashes(implode('","', $row)) . "\"\n";
    if ($format == 'txt') {
      echo "\r\n";
    }
  }

  if ($final_count == 0) {
    print "NO DATA\n";
  }
}
else {
  require_once ('rlibdata.php');
}

if (isset($defaction) && $defaction == 'print') {
  echo "<script type='text/javascript'>
    window.print();
    window.setInterval('window.close();', 10000);
  </script>";
}

?>
