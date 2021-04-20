<?php
ob_start();
require_once ('global_reports.inc');

require_once ('rlib.inc');

require_once ('functions.inc.php');
$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
global $accountingExport;

if (empty($format)) {
  $format = 'html';
}

$rlib_filename = 'TricomAssignment.xml';

if ($rptFrom != "0" && $rptFrom != "") {
  $module = "TricomAssignment" . $rptFrom;
}
else $module = "TricomAssignment";

/*if ($view == "myreport") {
  $rquery = "select reportoptions from reportdata where reportid='$id'";
  $rresult = mysql_query($rquery, $db);
  $vrowdata = mysql_fetch_row($rresult);
  $vrow = explode("|username->", $vrowdata[0]);
  $TricomAssignment = $vrow[0];
  $cusername = $vrow[1];
  if (strpos($TricomAssignment, "|username->") != 0) $TricomAssignment = $vrow[0];
  session_update("cusername");
  session_update("TricomAssignment");
}*/

$frm_values = explode('|', $TricomAssignment);

$decimalPref = getDecimalPreference();

$dept = $frm_values[2];
// echo '<pre>'; print_r($frm_values);

$wherecon = "";
//Where condition for customers selected in filter
$where_cond = "";
$jo_title = $frm_values[0];
$jobtitle = explode('-', $frm_values[0]);
$jo_id = $jobtitle[0];
$job_title = $jobtitle[1];
$assign_id  = $frm_values[1];

$selDepartmentsList = $frm_values[2];
$dept_id  = str_replace('ALL,', '', $frm_values[2]);
$last_exp_date  = $frm_values[3];

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
$joborder_id[0] = 'joborder_id';
$assignment_id[0] = 'assignment_id';
$employee_id[0] = 'employee_id';
$ssn[0] = 'ssn';
$first_name[0] = 'first_name';
$middle_name[0] = 'middle_name';
$last_name[0] = 'last_name';
$workcomp_code[0] = 'workcomp_code';
$work_state[0] = 'work_state';
$pay_rate[0] = 'pay_rate';
$OTDbl_payRate_type[0] = 'OTDbl_payRate_type';
$OTPay_rate[0] = 'OTPay_rate';
$DblPay_rate[0] = 'DblPay_rate';
$hol_payRate_type[0] = 'hol_payRate_type';
$holPay_rate[0] = 'holPay_rate';
$bill_rate[0] = 'bill_rate';
$OTDblBill_rate_type[0] = 'OTDblBill_rate_type';
$OTBill_rate[0] = 'OTBill_rate';
$DblBill_rate[0] = 'DblBill_rate';
$holBill_rate_type[0] = 'holBill_rate_type';
$holBill_rate[0] = 'holBill_rate';
$user_field1[0] = 'user_field1';
$user_field2[0] = 'user_field2';
$user_field3[0] = 'user_field3';
$comments[0] = 'comments';

// Report Heading Names

$joborder_id[1] = 'JobOrderID';
$assignment_id[1] = 'AssignmentID';
$employee_id[1] = 'EmployeeID';
$ssn[1] = 'SSN';
$first_name[1] = 'FirstName';
$middle_name[1] = 'MiddleName';
$last_name[1] = 'LastName';
$workcomp_code[1] = 'WorkCompCode';
$work_state[1] = 'WorkState';
$pay_rate[1] = 'PayRate';
$OTDbl_payRate_type[1] = 'OTDblPayRateType';
$OTPay_rate[1] = 'OTPayRate';
$DblPay_rate[1] = 'DblPayRate';
$hol_payRate_type[1] = 'HolPayRateType';
$holPay_rate[1] = 'HolPayRate';
$bill_rate[1] = 'BillRate';
$OTDblBill_rate_type[1] = 'OTDblBillRateType';
$OTBill_rate[1] = 'OTBillRate';
$DblBill_rate[1] = 'DblBillRate';
$holBill_rate_type[1] = 'HolBillRateType';
$holBill_rate[1] = 'HolBillRate';
$user_field1[1] = 'UserField1';
$user_field2[1] = 'UserField2';
$user_field3[1] = 'UserField3';
$comments[1] = 'Comments';

// Report Heading Seperations
$joborder_id[2] = '---------------';
$assignment_id[2] = '---------------';
$employee_id[2] = '---------------';
$ssn[2] = '---------------';
$first_name[2] = '---------------';
$middle_name[2] = '---------------';
$last_name[2] = '---------------';
$workcomp_code[2] = '---------------';
$work_state[2] = '---------------';
$pay_rate[2] = '---------------';
$OTDbl_payRate_type[2] = '-------------------';
$OTPay_rate[2] = '---------------';
$DblPay_rate[2] = '---------------';
$hol_payRate_type[2] = '---------------';
$holPay_rate[2] = '---------------';
$bill_rate[2] = '---------------';
$OTDblBill_rate_type[2] = '------------------';
$OTBill_rate[2] = '---------------';
$DblBill_rate[2] = '---------------';
$holBill_rate_type[2] = '---------------';
$holBill_rate[2] = '---------------';
$user_field1[2] = '---------------';
$user_field2[2] = '---------------';
$user_field3[2] = '---------------';
$comments[2] = '---------------'; 

$sortarr = array(
  'joborder_id','assignment_id','employee_id','ssn','first_name','middle_name','last_name','workcomp_code','work_state','pay_rate','OTDbl_payRate_type','OTPay_rate','DblPay_rate','hol_payRate_type','holPay_rate','bill_rate','OTDblBill_rate_type','OTBill_rate','DblBill_rate','holBill_rate_type','holBill_rate','user_field1','user_field2','user_field3','comments'
);
$arr_count    = count($sortarr);
$rep_company  = $companyname.'<br>';
$rep_header   = 'Tricom Assignment Report';//report title
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
if($job_title!='')
{
    $custalign = '<br>';
}
else
{
  $job_title = 'ALL';
}
if($assign_id!='')
{
    $custalign = '<br>';
}
else
{
  $assign_id = 'ALL';
}
//Displaying selected filters for Run report

$JobTitle   =   'Job Title: '.$job_title.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$AssignmentId =  'Assignment ID: '.str_ireplace("ASGN",'',$assign_id).$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$Department     =   'HRM Departments: '.$dept_name[0].$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$LastExpDate    = 'Last Exported Date: '.$last_exp_date.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";

$rep_title       =  $JobTitle.$AssignmentId.$Department.$LastExpDate;
$k = 0;


if($jo_id !="")
{
  $where_cond .= " AND p.posid = '".$jo_id."'";  
}

if($dept_id !="")
{
  $where_cond .= " AND dept.sno !='0'  AND dept.sno IN(".$dept_id.")";  
}
if($assign_id !="" && $assign_id!= 'ALL')
{
  $where_cond .= " AND hj.pusername ='".$assign_id."'";  
}
if($last_exp_date !="")
{
  //$where_cond .= " AND str_to_date(hj.mdate,'%Y-%m-%d') >= str_to_date('".date("Y-m-d", strtotime($last_exp_date))."','%Y-%m-%d') ";
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

function formatted_ssn($ssn){
  $clean_ssn      = preg_replace("/[^0-9,.]/", "", $ssn);
  $formatted_ssn  = preg_replace("/^(\d{3})(\d{2})(\d{4})$/", "$1-$2-$3", $clean_ssn);
  
  if(preg_match('/^\d{3}-\d{2}-\d{4}$/', $formatted_ssn))
  {
      return $formatted_ssn;
  } 
  else return NULL;
}

$setQry = "SET SESSION group_concat_max_len=1073740800";
mysql_query($setQry,$db);

$final_query = "SELECT 
              hj.posid AS 'JoborderId',
              IF (hj.mdate = hj.cdate,CONCAT(REPLACE(hj.pusername,'ASGN','*'),'*'),REPLACE(hj.pusername,'ASGN','')) AS 'AssignmentId',
              emp.sno AS 'EmpId',
              hp.ssn AS 'SSN',
              hg.fname AS 'FirstName',
              hg.mname AS 'MiddleName',
              hg.lname AS 'LastName', 
              wc.code AS 'WorkCompCode',
              REPLACE(wc.state,'N/A','') AS 'WorkState',
              hj.pamount AS 'PayRate',
              'A' AS 'OTDblPayRateType',
              hj.double_prate_amt AS 'DblPayRate',
              hj.otprate_amt AS 'OTPayRate',
              'A' AS 'HolPayRateType',
              mjphy.rate AS 'HolPayRate',
              hj.bamount AS 'BillRate',
              'A' AS 'OTDblBillRateType',
              hj.double_brate_amt AS 'DblBillRate',
              hj.otbrate_amt AS 'OTBillRate',
              'A' AS 'HolBillRateType',
              mjbhy.rate AS 'HolBillRate',
              '' AS 'UserField1',
              '' AS 'UserField2',
              '' AS 'UserField3',
              '' AS 'Comments',
              hj.cdate,
              hj.mdate
              FROM hrcon_jobs hj
              INNER JOIN posdesc p ON (hj.posid=p.posid)
              INNER JOIN hrcon_general hg ON (hg.username = hj.username AND hg.ustatus = 'active')
              LEFT JOIN emp_list emp ON (emp.username = hg.username)
              LEFT JOIN department dept ON (dept.sno=p.deptid AND dept.status='Active')
              LEFT JOIN hrcon_personal hp ON (emp.username = hp.username and hp.ustatus = 'active')
              LEFT JOIN workerscomp wc ON (wc.workerscompid = hj.wcomp_code AND wc.status = 'active')
              LEFT JOIN multiplerates_assignment mjbhy ON (hj.sno=mjbhy.asgnid AND mjbhy.asgn_mode='hrcon' AND mjbhy.ratetype='billrate' AND mjbhy.ratemasterid=(select rateid from multiplerates_master mmb where mmb.name='Holiday' AND mmb.status='ACTIVE'))
              LEFT JOIN multiplerates_assignment mjphy ON (hj.sno=mjphy.asgnid AND mjphy.asgn_mode='hrcon' AND mjphy.ratetype='payrate' AND mjphy.ratemasterid=(select rateid from multiplerates_master mmb where mmb.name='Holiday' AND mmb.status='ACTIVE'))

              WHERE 
              hj.ustatus IN ('active','closed','cancel','pending') AND
              emp.lstatus NOT IN ('DA','INACTIVE')
              ".$where_cond."
              ORDER BY p.posid DESC,hj.pusername DESC ";

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
    'joborder_id' => ($final_arr['JoborderId']!= '' ? substr($final_arr['JoborderId'], 0, 8) : ''),
    'assignment_id' => ($final_arr['AssignmentId']!= '' ? substr($final_arr['AssignmentId'], 0, 10) : ''),
    'employee_id' => ($final_arr['EmpId']!= '' ? substr($final_arr['EmpId'], 0, 9) : ''),
    'ssn' => ($ac_aced->decrypt($final_arr['SSN'])!= '' ? substr(formatted_ssn($ac_aced->decrypt($final_arr['SSN'])), 0, 11) : ''),
    'first_name' => ($final_arr['FirstName']!= '' ? substr($final_arr['FirstName'], 0, 15) : ''),
    'middle_name' => ($final_arr['MiddleName']!= '' ? substr($final_arr['MiddleName'], 0, 15) : ''),
    'last_name' => ($final_arr['LastName']!= '' ? substr($final_arr['LastName'], 0, 30) : ''),
    'workcomp_code' => ($final_arr['WorkCompCode']!= '' ? substr($final_arr['WorkState'].$final_arr['WorkCompCode'], 0, 6) : ''),
    'work_state' => ($final_arr['WorkState']!= '' ? substr($final_arr['WorkState'], 0, 1) : ''),
    'pay_rate' => ($final_arr['PayRate']!= '' ? $final_arr['PayRate']: ''),
    'OTDbl_payRate_type' => ($final_arr['OTDblPayRateType']!= '' ? substr($final_arr['OTDblPayRateType'], 0, 1) : ''),
    'OTPay_rate' => ($final_arr['OTPayRate']!= '' ? $final_arr['OTPayRate']: ''),
    'DblPay_rate' => ($final_arr['DblPayRate']!= '' ? $final_arr['DblPayRate']: ''),
    'hol_payRate_type' => ($final_arr['HolPayRateType']!= '' ? substr($final_arr['HolPayRateType'], 0, 1) : ''),
    'holPay_rate' => ($final_arr['HolPayRate']!= '' ? $final_arr['HolPayRate'] : ''),
    'bill_rate' => ($final_arr['BillRate']!= '' ? $final_arr['BillRate'] : ''),
    'OTDblBill_rate_type' => ($final_arr['OTDblBillRateType']!= '' ? substr($final_arr['OTDblBillRateType'], 0, 1) : ''),
    'OTBill_rate' => ($final_arr['OTBillRate']!= '' ? $final_arr['OTBillRate']: ''),
    'DblBill_rate' => ($final_arr['DblBillRate']!= '' ? $final_arr['DblBillRate']: ''),
    'holBill_rate_type' => ($final_arr['HolBillRateType']!= '' ? substr($final_arr['HolBillRateType'], 0, 1) : ''),
    'holBill_rate' => ($final_arr['HolBillRate']!= '' ? $final_arr['HolBillRate']: ''),
    'user_field1' => ($final_arr['UserField1']!= '' ? substr($final_arr['UserField1'], 0, 30) : ''),
    'user_field2' => ($final_arr['UserField2']!= '' ? substr($final_arr['UserField2'], 0, 50) : ''),
    'user_field3' => ($final_arr['UserField3']!= '' ? substr($final_arr['UserField3'], 0, 50) : ''),
    'comments' => ($final_arr['Comments'] != '' ? substr($final_arr['Comments'], 0, 50) : ''),
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
if(($assign_id!= '' && $assign_id!= 'ALL') || ($jo_id!= '' && $jo_id!= 'ALL') || ($dept_name[0]!= 'ALL' && $dept_name[0]!= '')){
    $file = 'Tricom-Assignment-' . $dateval.'-'.$timeval.'-'.$meridian.'-Filtered.' . $format;
}else{
    $file = 'Tricom-Assignment-' . $dateval.'-'.$timeval.'-'.$meridian.'.' . $format;
}
$mime = 'application/' . $format;
$heading_names = array(
  'JobOrderID','AssignmentID','EmployeeID','SSN','FirstName','MiddleName','LastName','WorkCompCode','WorkState','PayRate','OTDblPayRateType','OTPayRate','DblPayRate','HolPayRateType','HolPayRate','BillRate','OTDblBillRateType','OTBillRate','DblBillRate','HolBillRateType','HolBillRate','UserField1','UserField2','UserField3','Comments'
);

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
