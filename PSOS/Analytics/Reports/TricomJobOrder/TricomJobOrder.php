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

$rlib_filename = 'TricomJobOrder.xml';

if ($rptFrom != "0" && $rptFrom != "") {
  $module = "TricomJobOrder" . $rptFrom;
}
else $module = "TricomJobOrder";

/*if ($view == "myreport") {
  $rquery = "select reportoptions from reportdata where reportid='$id'";
  $rresult = mysql_query($rquery, $db);
  $vrowdata = mysql_fetch_row($rresult);
  $vrow = explode("|username->", $vrowdata[0]);
  $TricomJobOrder = $vrow[0];
  $cusername = $vrow[1];
  if (strpos($TricomJobOrder, "|username->") != 0) $TricomJobOrder = $vrow[0];
  session_update("cusername");
  session_update("TricomJobOrder");
}*/
if($defaction == "print")
{
  $cusername  = $username;
  session_register("cusername");
}
$frm_values = explode('|', $TricomJobOrder);
$decimalPref = getDecimalPreference();
$dept = $frm_values[3];

$wherecon = "";
//Where condition for customers selected in filter
$where_cond = "";
$customer = explode('-', $frm_values[0]);
$c_id = $customer[0];
$cust_name = $customer[1];

$joborderid = explode('-', $frm_values[1]);
$jo_id = $joborderid[0];
$joborder_title = $joborderid[1];
$jo_type_id = $frm_values[2];

$job_title = $frm_values[1];
$selJobType = $frm_values[2];
$job_type = getManageList('jotype',$frm_values[2]);
$selDepartmentsList = $frm_values[3];
$dept_id  = str_replace('ALL,', '', $frm_values[3]);
$last_exp_date  = $frm_values[4];

if($dept_id!= "")
{
  //Getting count of the department
  $getdept_count  = "SELECT count(sno) FROM department WHERE status='Active' AND sno !='0' AND sno IN ({$deptAccesSno})";
  $getdept_query  = mysql_query($getdept_count,$db);
  $getdept_result = mysql_fetch_row($getdept_query);

  //Getting names of department to display on the report
  $getdepartments_name = "SELECT GROUP_CONCAT(deptname) AS department,count(sno) FROM department WHERE sno IN(".$dept_id.")";
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
$joborder_id[0]='joborder_id';
$cust_id[0] = 'cust_id';
$comp_name[0] = 'cname';
$branch_name[0] = 'branch_name';
$bill_contact[0] = 'bill_contact';
$workcomp_code[0] = 'workcomp_code';
$work_state[0] = 'work_state';
$PayRate[0] = 'pay_rate';
$OTDblPayRate_type[0] = 'OTDblPayRate_type';
$OTPay_rate[0] = 'OTPay_rate';
$DblPay_rate[0]='DblPay_rate';
$HolPayRate_type[0] ='HolPayRate_type';
$HolPay_rate[0] = 'HolPay_rate';
$Bill_rate[0] = 'Bill_rate';
$OTDblBillRate_type[0]='OTDblBillRate_type';
$OTBill_rate[0]='OTBill_rate';
$DblBill_rate[0]='DblBill_rate';
$HolBillRate_type[0]='HolBillRate_type';
$HolBill_rate[0]='HolBill_rate';
$User_field1[0]='User_field1';
$User_field2[0]='User_field2';
$User_field3[0]='User_field3';
$Default_commentsFlag[0]='Default_commentsFlag';
$Default_comments[0]='Default_comments';


// Report Heading Names
$joborder_id[1]='JobOrderID';
$cust_id[1]='CustomerID';
$comp_name[1]='CustomerName';
$branch_name[1]='BranchName';
$bill_contact[1]='BillingAttn';
$workcomp_code[1]='WorkCompCode';
$work_state[1]='WorkState';
$PayRate[1]='PayRate';
$OTDblPayRate_type[1]='OTDblPayRateType';
$OTPay_rate[1]='OTPayRate';
$DblPay_rate[1]='DblPayRate';
$HolPayRate_type[1]='HolPayRateType';
$HolPay_rate[1]='HolPayRate';
$Bill_rate[1]='BillRate';
$OTDblBillRate_type[1]='OTDblBillRateType';
$OTBill_rate[1]='OTBillRate';
$DblBill_rate[1]='DblBillRate';
$HolBillRate_type[1]='HolBillRateType';
$HolBill_rate[1]='HolBillRate';
$User_field1[1]='UserField1';
$User_field2[1]='UserField2';
$User_field3[1]='UserField3';
$Default_commentsFlag[1]='DefaultCommentsFlag';
$Default_comments[1]='DefaultComments';





// Report Heading Seperations
$joborder_id[2]='---------------';
$cust_id[2]='---------------';
$comp_name[2]='---------------';
$branch_name[2]='---------------';
$bill_contact[2]='---------------';
$workcomp_code[2]='---------------';
$work_state[2]='---------------';
$PayRate[2]='---------------';
$OTDblPayRate_type[2]='-------------------';
$OTPay_rate[2]='---------------';
$DblPay_rate[2]='---------------';
$HolPayRate_type[2]='--------------------';
$HolPay_rate[2]='---------------';
$Bill_rate[2]='---------------';
$OTDblBillRate_type[2]='------------------';
$OTBill_rate[2]='---------------';
$DblBill_rate[2]='---------------';
$HolBillRate_type[2]='---------------';
$HolBill_rate[2]='---------------';
$User_field1[2]='---------------';
$User_field2[2]='---------------';
$User_field3[2]='---------------';
$Default_commentsFlag[2]='----------------------';
$Default_comments[2]='---------------';


$sortarr = array('joborder_id','cust_id','comp_name','branch_name','bill_contact','workcomp_code','work_state','PayRate','OTDblPayRate_type','OTPay_rate','DblPay_rate','HolPayRate_type','HolPay_rate','Bill_rate','OTDblBillRate_type','OTBill_rate','DblBill_rate','HolBillRate_type','HolBill_rate','User_field1','User_field2','User_field3','Default_commentsFlag','Default_comments');

$arr_count    = count($sortarr);
$rep_company  = $companyname.'<br>';
$rep_header   = 'Tricom JobOrder Report';//report title
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

if($job_title!='')
{
  $custalign = '<br>';
}
else
{
  $job_title = 'ALL';
}

if($job_type!='')
{
    $custalign = '<br>';
}
else
{
  $job_type = 'ALL';
}
//Displaying selected filters for Run report
$CustomerName   =  'Customer Name: '.$cust_name.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$JobTitle       =  'Job Title: '.$job_title.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$JobType        =  'Job Type: '.$job_type.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$Department     =  'HRM Departments: '.$dept_name[0].$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$LastExpDate    =  'Last Exported Date: '.$last_exp_date.$custalign."&nbsp;&nbsp;&nbsp;&nbsp;";
$rep_title      =  $CustomerName.$JobTitle.$JobType.$Department.$LastExpDate;
$k = 0;


if($c_id !="")
{
  $where_cond .= " AND cust_info.sno = ".$c_id;  
}
if($jo_id !="")
{
  $where_cond .= " AND p.posid = ".$jo_id;  
}
if($dept_id !="")
{
  $where_cond .= " AND dept.sno !='0' AND dept.sno IN(".$dept_id.")";  
}
if($jo_type_id !="" && $jo_type_id != 'ALL')
{
  $where_cond .= " AND p.postype ='".$jo_type_id."'";  
}
if($last_exp_date !="")
{
  //$where_cond .= " AND str_to_date(p.mdate,'%Y-%m-%d') >= str_to_date('".date("Y-m-d", strtotime($last_exp_date))."','%Y-%m-%d') ";
  $where_cond .= " AND p.mdate >= '".date("Y-m-d H:i:s", strtotime($last_exp_date))."' ";  
  
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

$final_query = "SELECT 
              IF (p.mdate = p.stime,CONCAT('*',p.posid,'*'),p.posid) AS 'JoborderId',
              cust_info.sno AS 'CustomerID',
              cust_info.cname AS 'CustomerName',
              dept.depcode AS 'BranchName', 
              CONCAT_WS(' ',com_cont.fname,mname,lname) AS 'BillingAttn',
              wc.code AS 'WorkCompCode',
              REPLACE(wc.state,'N/A','') AS 'WorkState',
              mjp.rate AS 'Pay_Rate',
              'A' AS 'OTDblPayRateType',
              mjpdt.rate AS 'DblPayRate',
              mjpot.rate AS 'OTPayRate',
              'A' AS 'HolPayRateType',
              mjphy.rate AS 'HolPayRate',
              mjb.rate AS 'Bill_Rate',
              'A' AS 'OTDblBillRateType',
              mjbdt.rate AS 'DblBillRate',
              mjbot.rate AS 'OTBillRate',
              'A' AS 'HolBillRateType',
              mjbhy.rate AS 'HolBillRate',
              '' AS 'UserField1',
              '' AS 'UserField2',
              '' AS 'UserField3',
              '' AS 'DefaultCommentsFlag',
              '' AS 'DefaultComments',
              p.stime,
              p.mdate

              FROM posdesc p
              INNER JOIN staffoppr_cinfo com_info ON (p.company=com_info.sno)
              INNER JOIN staffacc_cinfo cust_info ON (com_info.acc_comp=cust_info.sno)
              LEFT JOIN department dept ON (dept.sno=p.deptid AND dept.status='Active')
              LEFT JOIN staffoppr_contact com_cont ON (com_cont.sno=p.billingto)
              LEFT JOIN multiplerates_joborder mjb ON (p.posid=mjb.joborderid AND mjb.ratemasterid='rate1' AND mjb.jo_mode='joborder' AND mjb.ratetype='billrate')
              LEFT JOIN multiplerates_joborder mjp ON (p.posid=mjp.joborderid AND mjp.ratemasterid='rate1' AND mjp.jo_mode='joborder' AND mjp.ratetype='payrate')
              LEFT JOIN multiplerates_joborder mjbot ON (p.posid=mjbot.joborderid AND mjbot.ratemasterid='rate2' AND mjbot.jo_mode='joborder' AND mjbot.ratetype='billrate')
              LEFT JOIN multiplerates_joborder mjpot ON (p.posid=mjpot.joborderid AND mjpot.ratemasterid='rate2' AND mjpot.jo_mode='joborder' AND mjpot.ratetype='payrate')
              LEFT JOIN multiplerates_joborder mjbdt ON (p.posid=mjbdt.joborderid AND mjbdt.ratemasterid='rate3' AND mjbdt.jo_mode='joborder' AND mjbdt.ratetype='billrate')
              LEFT JOIN multiplerates_joborder mjpdt ON (p.posid=mjpdt.joborderid AND mjpdt.ratemasterid='rate3' AND mjpdt.jo_mode='joborder' AND mjpdt.ratetype='payrate')
              LEFT JOIN multiplerates_joborder mjbhy ON (p.posid=mjbhy.joborderid AND mjbhy.jo_mode='joborder' AND mjbhy.ratetype='billrate' AND mjbhy.ratemasterid=(SELECT rateid FROM multiplerates_master mmb WHERE mmb.name='Holiday' AND mmb.status='ACTIVE'))
              LEFT JOIN multiplerates_joborder mjphy ON (p.posid=mjphy.joborderid AND mjphy.jo_mode='joborder' AND mjphy.ratetype='payrate' AND mjphy.ratemasterid=(SELECT rateid FROM multiplerates_master mmb WHERE mmb.name='Holiday' AND mmb.status='ACTIVE'))
              LEFT JOIN workerscomp wc ON (wc.workerscompid = p.wcomp_code AND wc.status = 'active')
              WHERE 
              com_info.status NOT IN ('backup')
              ".$where_cond."
              GROUP BY p.posid
              ORDER BY p.posid DESC ";

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
      'cust_id' => ($final_arr['CustomerID']!= '' ? substr($final_arr['CustomerID'], 0, 9) : ''),
      'comp_name' => ($final_arr['CustomerName']!= '' ? substr($final_arr['CustomerName'], 0, 30) : ''),
      'branch_name' => ($final_arr['BranchName']!= '' ? $final_arr['BranchName']: ''),
      'bill_contact' => ($final_arr['BillingAttn']!= '' ? substr($final_arr['BillingAttn'], 0, 30) : ''),
      'workcomp_code' => ($final_arr['WorkCompCode']!= '' ? substr($final_arr['WorkCompCode'], 0, 6) : ''),
      'work_state' => ($final_arr['WorkState']!= '' ? substr($final_arr['WorkState'], 0, 2) : ''),
      'PayRate' => ($final_arr['Pay_Rate']!= '' ? $final_arr['Pay_Rate']: ''),
      'OTDblPayRate_type' => ($final_arr['OTDblPayRateType']!= '' ? substr($final_arr['OTDblPayRateType'], 0, 1) : ''),
      'OTPay_rate' => ($final_arr['OTPayRate']!= '' ? $final_arr['OTPayRate']: ''),
      'DblPay_rate' => ($final_arr['DblPayRate']!= '' ? $final_arr['DblPayRate']: ''),
      'HolPayRate_type' => ($final_arr['HolPayRateType']!= '' ? substr($final_arr['HolPayRateType'], 0, 1) : ''),
      'HolPay_rate' => ($final_arr['HolPayRate']!= '' ? $final_arr['HolPayRate']: ''),
      'Bill_rate' => ($final_arr['Bill_Rate']!= '' ? $final_arr['Bill_Rate']: ''),
      'OTDblBillRate_type' => ($final_arr['OTDblBillRateType']!= '' ? substr($final_arr['OTDblBillRateType'], 0, 1) : ''),
      'OTBill_rate' => ($final_arr['OTBillRate']!= '' ? $final_arr['OTBillRate']: ''),
      'DblBill_rate' => ($final_arr['DblBillRate']!= '' ? $final_arr['DblBillRate']: ''),
      'HolBillRate_type' => ($final_arr['HolBillRateType']!= '' ? substr($final_arr['HolBillRateType'], 0, 1) : ''),
      'HolBill_rate' => ($final_arr['HolBillRate']!= '' ? $final_arr['HolBillRate']: ''),
      'User_field1' => ($final_arr['UserField1']!= '' ? substr($final_arr['UserField1'], 0, 30) : ''),
      'User_field2' => ($final_arr['UserField2']!= '' ? substr($final_arr['UserField2'], 0, 50) : ''),
      'User_field3' => ($final_arr['UserField3']!= '' ? substr($final_arr['UserField3'], 0, 50) : ''),
      'Default_commentsFlag' => ($final_arr['DefaultCommentsFlag']!= '' ? substr($final_arr['DefaultCommentsFlag'], 0, 1) : ''),
      'Default_comments' => ($final_arr['DefaultComments']!= '' ? substr($final_arr['DefaultComments'], 0,50) : '')
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

if(($jo_type_id != 'ALL' && $jo_type_id!='') || ($c_id!= '' && $c_id!= 'ALL') || ($jo_id!= '' && $jo_id!= 'ALL') || ($dept_name[0]!= 'ALL' && $dept_name[0]!= '')){
    $file = 'Tricom-JobOrder-' . $dateval.'-'.$timeval.'-'.$meridian.'-Filtered.' . $format;
}else{
    $file = 'Tricom-JobOrder-' . $dateval.'-'.$timeval.'-'.$meridian.'.' . $format;
}
$mime = 'application/' . $format;
$heading_names = array('JobOrderID','CustomerID','CustomerName','BranchName','BillingAttn','WorkCompCode','WorkState','PayRate','OTDblPayRateType','OTPayRate','DblPayRate','HolPayRateType','HolPayRate','BillRate','OTDblBillRateType','OTBillRate','DblBillRate','HolBillRateType','HolBillRate','UserField1','UserField2','UserField3','DefaultCommentsFlag','DefaultComments');

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
