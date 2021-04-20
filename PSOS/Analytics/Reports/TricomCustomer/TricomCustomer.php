<?php
ob_start();
require_once ('global_reports.inc');
require_once ('rlib.inc');
require_once ('functions.inc.php');
$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
if (empty($format))
{
    $format = 'html';
}

$rlib_filename = 'TricomCustomer.xml';

if ($rptFrom != "0" && $rptFrom != "")
{
    $module = "TricomCustomer" . $rptFrom;
}
else $module = "TricomCustomer";

/*if ($view == "myreport")
{
    $rquery = "select reportoptions from reportdata where reportid='$id'";
    $rresult = mysql_query($rquery, $db);
    $vrowdata = mysql_fetch_row($rresult);
    $vrow = explode("|username->", $vrowdata[0]);
    $TricomCustomer = $vrow[0];
    $cusername = $vrow[1];
    if (strpos($TricomCustomer, "|username->") != 0) 
        $TricomCustomer = $vrow[0];
    session_update("cusername");
    session_update("TricomCustomer");
}*/
if ($defaction == "print")
{
    $cusername = $username;
    session_register("cusername");
}
$frm_values = explode('|', $TricomCustomer);
$decimalPref = getDecimalPreference();
$dept = $frm_values[0];

$selDepartmentsList = $frm_values[0];
$dept_id = str_replace('ALL,', '', $frm_values[0]);
$customer = explode('-', $frm_values[1]);
$c_id = $customer[0];
$cust_name = $customer[1];
$last_exp_date = $frm_values[2];

if ($dept_id != "")
{
    //Getting count of the department
    $getdept_count = "SELECT count(sno) FROM department WHERE status='Active' AND sno !='0' AND sno IN ({$deptAccesSno})";
    $getdept_query = mysql_query($getdept_count, $db);
    $getdept_result = mysql_fetch_row($getdept_query);

    //Getting names of department to display on the report
    $getdepartments_name = "SELECT GROUP_CONCAT(deptname) AS department,count(sno) FROM department WHERE sno IN(" . $dept_id . ")";
    $dept_query = mysql_query($getdepartments_name, $db);
    $dept_name = mysql_fetch_row($dept_query);
    $dept_array = explode(",", $dept_name[0]);

    if ($dept_name[1] == $getdept_result[0])
    {
        $dept_name = array(
            "0" => "ALL"
        );
    }
}else{
    $dept_id = $deptAccesSno;
}

// Report ids
$cust_id[0] = 'cust_id';
$cname[0] = 'cname';
$branch[0] = 'branch';
$addr1[0] = 'addr1';
$addr2[0] = 'addr2';
$addr3[0] = 'addr3';
$city[0] = 'city';
$state[0] = 'state';
$zip[0] = 'zip';
$con_name[0] = 'con_name';
$phone[0] = 'phone';
$pterms[0] = 'pterms';
$email_inv_to[0] = 'email_inv_to';

// Report Heading Names
$cust_id[1] = 'CustomerID';
$cname[1] = 'CustomerName';
$branch[1] = 'BranchName';
$addr1[1] = 'Address1';
$addr2[1] = 'Address2';
$addr3[1] = 'Address3';
$city[1] = 'City';
$state[1] = 'State';
$zip[1] = 'ZipCode';
$con_name[1] = 'ContactName';
$phone[1] = 'ContactPhoneNumber';
$pterms[1] = 'PaymentTerms';
$email_inv_to[1] = 'EmailInvoiceTo';

// Report Heading Seperations
$cust_id[2] = '---------------';
$cname[2] = '---------------';
$branch[2] = '---------------';
$addr1[2] = '---------------';
$addr2[2] = '---------------';
$addr3[2] = '---------------';
$city[2] = '---------------';
$state[2] = '-------';
$zip[2] = '---------';
$con_name[2] = '---------------';
$phone[2] = '-------------------';
$pterms[2] = '----------------';
$email_inv_to[2] = '---------------';

$sortarr = array(
    'cust_id',
    'cname',
    'branch',
    'addr1',
    'addr2',
    'addr3',
    'city',
    'state',
    'zip',
    'con_name',
    'phone',
    'pterms',
    'email_inv_to'
);
$arr_count = count($sortarr);
$rep_company = $companyname . '<br>';
$rep_header = 'Tricom Customer Report'; 
$rep_date = 'date';

$custalign = '';
$custalignspaces = '';

if ($last_exp_date != '')
{
    $custalign = '<br>';
    $export_date = "success";
}

if ($dept_name[0] != '')
{
    $custalign = '<br>';
}
if ($cust_name != '')
{
    $custalign = '<br>';
}
else
{
    $cust_name = 'ALL';
}
//Displaying selected filters for Run report
$Department = $dept_name[0] ? 'HRM Departments: ' . $dept_name[0] . $custalign . "&nbsp;&nbsp;&nbsp;&nbsp;" : '';
$CustomerName = $cust_name ? 'Customer Name: ' . $cust_name . $custalign . "&nbsp;&nbsp;&nbsp;&nbsp;" : '';
$LastExpDate =  'Last Exported Date: ' . $last_exp_date . $custalign . "&nbsp;&nbsp;&nbsp;&nbsp;" ;
$rep_title = $Department . $CustomerName . $LastExpDate;
$k = 0;

//Filter conditions to apply on query
$where_cond = "";
if ($c_id != "")
{
    $where_cond .= " AND scinfo.sno = " . $c_id;
}

if ($dept_id != "")
{
    $where_cond .= " AND ca.deptid !='0'  AND ca.deptid IN(" . $dept_id . ")";
}
if ($last_exp_date != "")
{
   $where_cond .= " AND scinfo.mdate >= '" . date("Y-m-d H:i:s", strtotime($last_exp_date)) . "' ";
}
// Array for displaying heading for all the columns selected
for ($q = 0;$q < $arr_count;$q++)
{
    $variable = $$sortarr[$q];
    if (!empty($variable[0]))
    {
        $data[0][$k] = $variable[0];
        $headval[0][$k] = $variable[1];
        $headval[1][$k] = $variable[2];
        $k++;
    }
}

if ($k != 0)
{
    $data[0][$k] = 'link';
    $k++;
    $data[0][$k] = 'link_length';
}

$final_query = "SELECT
IF (scinfo.mdate = sclst.stime,CONCAT('*',scinfo.sno,'*'),scinfo.sno) AS 'Customer_Id',
scinfo.cname AS 'Customer',
d.depcode AS 'BranchName',
scinfo.address1 AS 'Address1',
scinfo.address2 AS 'Address2',
scinfo.city AS 'City',
scinfo.state AS 'State',
scinfo.zip AS 'ZipCode',
CONCAT_WS(' ',con.fname,con.lname) AS 'bill_contact',
IF(wphone!='',wphone,IF(mobile!='',mobile,IF(hphone!='',hphone,''))) AS 'bill_contact_phone',
bpt.billpay_desc AS 'PaymentTerms',
con.email AS 'EmailInvoiceTo',
sclst.stime,
scinfo.mdate

FROM staffacc_cinfo scinfo
LEFT JOIN staffacc_list sclst ON (scinfo.username = sclst.username)
LEFT JOIN Client_Accounts ca ON (ca.typeid=scinfo.sno AND ca.status='active' AND ca.clienttype='CUST')
LEFT JOIN department d ON (d.sno=ca.deptid AND d.status='active')
LEFT JOIN staffacc_contact con ON (con.sno=scinfo.bill_contact)
LEFT JOIN bill_pay_terms bpt ON (bpt.billpay_termsid=scinfo.bill_req AND bpt.billpay_type='PT' AND bpt.billpay_status='active')
WHERE 1 = 1
" . $where_cond . "
AND sclst.status='ACTIVE'
ORDER BY scinfo.sno DESC; ";
$final_res = mysql_query($final_query, $db);
$final_count = mysql_num_rows($final_res);
$timesheet_sno = array();
$timesheet_parid = array();
$expense_sno = array();
$expense_parid = array();
$regular_rates = array();
$final_data = array();

while ($row = mysql_fetch_array($final_res))
{
    $final_data[] = $row;

}
// Form the report data
$i = 1;

function formatted_zip($val){
  $count = strlen($val);
  if($count == '5'){
    $zip = $val;
  }if ($count >'5') {
    $zipval = preg_replace('/\D/', '', $val);
    $zip = substr($zipval,0,5).'-'.substr($zipval,5,4);
  }
  return $zip;
}
function formatted_phone($val){
  $phone = preg_replace('/\D/', '', $val);
  $phone_val = substr($phone,0,3).'-'.substr($phone,3,3).'-'.substr($phone,6,4);
  return $phone_val;
}
function formatEmail($email)
{
   if(filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email))
        return $email;
   else
        return NULL;
}
foreach ($final_data as $final_arr)
{
    $ii = 0;

    // Array for all column's data
    $values_array = array(
        'cust_id' => substr($final_arr['Customer_Id'], 0, 9) ,
        'cname' => ($final_arr['Customer'] != '' ? substr(stripslashes($final_arr['Customer']), 0, 30) : '') ,
        'branch' => ($final_arr['BranchName']!= ''? substr($final_arr['BranchName'], 0, 30) :'') ,
        'addr1' => ($final_arr['Address1'] != '' ? substr($final_arr['Address1'], 0, 30) : '') ,
        'addr2' => ($final_arr['Address2'] != '' ? substr($final_arr['Address2'], 0, 30) : '') ,
        'addr3' => '',
        'city' => ($final_arr['City'] != '' ? substr($final_arr['City'], 0, 18) : '') ,
        'state' => ($final_arr['State'] != '' ? substr($final_arr['State'], 0, 2) : '') ,
        'zip' => ($final_arr['ZipCode'] != '' ? formatted_zip(substr($final_arr['ZipCode'],0,10)) : '') ,
        'con_name' => ($final_arr['bill_contact'] != '' ? substr($final_arr['bill_contact'], 0, 30) : '') ,
        'phone' => ($final_arr['bill_contact_phone'] != '' ? formatted_phone($final_arr['bill_contact_phone']) : '') ,
        'pterms' => ($final_arr['PaymentTerms'] != '' ? substr($final_arr['PaymentTerms'], 0, 15) : '') ,
        'email_inv_to' => ($final_arr['EmailInvoiceTo'] != '' ? formatEmail(substr($final_arr['EmailInvoiceTo'], 0, 50)): '')
    );
    for ($q = 0;$q <= $arr_count;$q++)
    {
        $variable = $$sortarr[$q];
        if (!empty($variable[0]))
        {
            $data[$i][$ii] = $values_array[$sortarr[$q]];
            $sslength_array[$sortarr[$q]] = trim((strlen($values_array[$sortarr[$q]]) <= strlen($variable[2])) ? strlen($values_array[$sortarr[$q]]) : (strlen($variable[2]) + 3));
            $ii++;
        }
    }

    if ($arr_count)
    {
        $slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
    }

    $data[$i][$ii] = '';
    $ii++;
    $i++;
}

$data = cleanArray($data);

if (empty($data))
{
    $data = array();
    $headval = array();
    $data[0][0] = '';
    $headval[0][0] = '';
}

$dateval = date('Ymd');
$timeval = date('His', time());
$meridian = date('A', time());

//$deptpos = strpos($frm_values[0], "ALL");


if(($dept_name[0]!= '' && $dept_name[0]!= 'ALL') || $c_id!= ''){
    $file = 'Tricom-Customer-' . $dateval.'-'.$timeval.'-'.$meridian.'-Filtered.' . $format;
}else{
    $file = 'Tricom-Customer-' . $dateval.'-'.$timeval.'-'.$meridian.'.' . $format;
}

$mime = 'application/' . $format;
$heading_names = array(
    'CustomerID',
    'CustomerName',
    'BranchName',
    'Address1',
    'Address2',
    'Address3',
    'City',
    'State',
    'ZipCode',
    'ContactName',
    'ContactPhoneNumber',
    'PaymentTerms',
    'EmailInvoiceTo'
);

if ($format == 'txt' || $format == 'csv')
{

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: public');
    header("Content-Type: $mime; name=$file");
    header('Content-Description: File Transfer');
    header("Content-Disposition: attachment; filename=$file");
    header('Content-Transfer-Encoding: binary');
    $header_count = count($headval[0]);
    for ($t = 0;$t <= $header_count;$t++)
    {
        $data[0][$t] = trim($headval[0][$t]);
    }

    array_shift($data);
    print implode(',', $heading_names) . "\r\n";
    foreach ($data as $row)
    {
        $row = array_slice($row, 0, count($row) - 1);
        print '"' . stripslashes(implode('","', $row)) . "\"\n";
        if ($format == 'txt')
        {
            echo "\r\n";
        }
    }

    if ($final_count == 0)
    {
        print "NO DATA\n";
    }
}
else
{
    require_once ('rlibdata.php');
}

if (isset($defaction) && $defaction == 'print')
{
    echo "<script type='text/javascript'>
    window.print();
    window.setInterval('window.close();', 10000);
  </script>";
}

?>
