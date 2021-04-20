<?php
ob_start();
require_once ('global_reports.inc');

require_once ('rlib.inc');

require_once ('functions.inc.php');
$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
global $accountingExport;

if (empty($format))
{
    $format = 'html';
}

$rlib_filename = 'TricomEmployee.xml';

if ($rptFrom != "0" && $rptFrom != "")
{
    $module = "TricomEmployee" . $rptFrom;
}
else $module = "TricomEmployee";

/*if ($view == "myreport")
{
    $rquery = "select reportoptions from reportdata where reportid='$id'";
    $rresult = mysql_query($rquery, $db);
    $vrowdata = mysql_fetch_row($rresult);
    $vrow = explode("|username->", $vrowdata[0]);
    $TricomEmployee = $vrow[0];
    $cusername = $vrow[1];
    if (strpos($TricomEmployee, "|username->") != 0) $TricomEmployee = $vrow[0];
    session_update("cusername");
    session_update("TricomEmployee");
}*/
if ($defaction == "print")
{
    $cusername = $username;
    session_register("cusername");
}
$frm_values = explode('|', $TricomEmployee);
$decimalPref = getDecimalPreference();
$emp_name = $frm_values[0];
$employee_status = $frm_values[1];
$selDepartmentsList = $frm_values[2];
$dept_id = str_replace('ALL,', '', $frm_values[2]);
$last_exp_date = $frm_values[3];

//Where condition for customers selected in filter
$where_cond = "";

if ($dept_id != "")
{
    //Getting count of the department
    $getdept_count = "SELECT count(sno) FROM department WHERE status='Active' AND sno !='0' AND sno IN ({$deptAccesSno})";
    $getdept_query = mysql_query($getdept_count, $db);
    $getdept_result = mysql_fetch_row($getdept_query);

    //Getting names of department to display on the report
    $getdepartments_name = "SELECT GROUP_CONCAT(deptname) as department,count(sno) FROM department WHERE sno IN(" . $dept_id . ")";
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
$feid[0] = 'feid';
$emp_id[0] = 'emp_id';
$ssn[0] = 'ssn';
$branch[0] = 'branch';
$fname[0] = 'fname';
$intial[0] = 'intial';
$lname[0] = 'lname';
$addr1[0] = 'addr1';
$addr2[0] = 'addr2';
$city[0] = 'city';
$state[0] = 'state';
$zip[0] = 'zip';
$phone[0] = 'phone';
$gender[0] = 'gender';
$wcode[0] = 'wcode';
$class[0] = 'class';
$dob[0] = 'dob';
$tax_state[0] = 'tax_state';
$work_state[0] = 'work_state';
$hire_date[0] = 'hire_date';
$fed_marital_status[0] = 'fed_marital_status';
$fed_allowances[0] = 'fed_allowances';
$fed_amt[0] = 'fed_amt';
$fed_percent[0] = 'fed_percent';
$state_mstatus[0] = 'state_mstatus';
$state_allowances[0] = 'state_allowances';
$state_add_amt[0] = 'state_add_amt';
$state_add_per[0] = 'state_add_per';
$primary_aba[0] = 'primary_aba';
$primary_acct[0] = 'primary_acct';
$primary_type[0] = 'primary_type';
$primary_bname[0] = 'primary_bname';
$primary_amt[0] = 'primary_amt';
$second_aba[0] = 'second_aba';
$second_acct[0] = 'second_acct';
$second_type[0] = 'second_type';
$second_bank[0] = 'second_bank';
$second_amt[0] = 'second_amt';
$third_aba[0] = 'third_aba';
$third_acct[0] = 'third_acct';
$third_type[0] = 'third_type';
$third_bank[0] = 'third_bank';
$third_amt[0] = 'third_amt';
$estatus[0] = 'emp_status';
$term_date[0] = 'term_date';
$dept[0] = 'dept';

// Report Heading Names
$feid[1] = 'FEIN';
$emp_id[1] = 'EmployeeID';
$ssn[1] = 'SSN';
$branch[1] = 'BranchName';
$fname[1] = 'FirstName';
$intial[1] = 'Initial';
$lname[1] = 'LastName';
$addr1[1] = 'HomeAddress1';
$addr2[1] = 'HomeAddress2';
$city[1] = 'HomeCity';
$state[1] = 'HomeState';
$zip[1] = 'HomeZip';
$phone[1] = 'PhoneNumber';
$gender[1] = 'Gender';
$wcode[1] = 'WorkCompCode';
$class[1] = 'Class';
$dob[1] = 'DOB';
$tax_state[1] = 'TaxState';
$work_state[1] = 'WorkState';
$hire_date[1] = 'HireDate';
$fed_marital_status[1] = 'FedMaritalStatus';
$fed_allowances[1] = 'FedAllowances';
$fed_amt[1] = 'FedAddlAmount';
$fed_percent[1] = 'FedAddlPercent';
$state_mstatus[1] = 'StateMaritalStatus';
$state_allowances[1] = 'StateAllowances';
$state_add_amt[1] = 'StateAddlAmount';
$state_add_per[1] = 'StateAddlPercent';
$primary_aba[1] = 'PrimaryABA';
$primary_acct[1] = 'PrimaryAcct';
$primary_type[1] = 'PrimaryType';
$primary_bname[1] = 'PrimaryBankName';
$primary_amt[1] = 'PrimaryAmt';
$second_aba[1] = 'SecondABA';
$second_acct[1] = 'SecondAcct';
$second_type[1] = 'SecondType';
$second_bank[1] = 'SecondBankName';
$second_amt[1] = 'SecondAmt';
$third_aba[1] = 'ThirdABA';
$third_acct[1] = 'ThirdAcct';
$third_type[1] = 'ThirdType';
$third_bank[1] = 'ThirdBankName';
$third_amt[1] = 'ThirdAmt';
$estatus[1] = 'EmpStatus';
$term_date[1] = 'TermDate';
$dept[1] = 'Department';

// Report Heading Seperations
$feid[2] = '--------------------';
$emp_id[2] = '--------------------';
$ssn[2] = '--------------------';
$branch[2] = '--------------------';
$fname[2] = '--------------------';
$intial[2] = '--------------------';
$lname[2] = '--------------------';
$addr1[2] = '--------------------';
$addr2[2] = '--------------------';
$city[2] = '--------------------';
$state[2] = '--------------------';
$zip[2] = '--------------------';
$phone[2] = '--------------------';
$gender[2] = '--------------------';
$wcode[2] = '--------------------';
$class[2] = '--------------------';
$dob[2] = '--------------------';
$tax_state[2] = '--------------------';
$work_state[2] = '--------------------';
$hire_date[2] = '--------------------';
$fed_marital_status[2] = '--------------------';
$fed_allowances[2] = '--------------------';
$fed_amt[2] = '--------------------';
$fed_percent[2] = '--------------------';
$state_mstatus[2] = '--------------------';
$state_allowances[2] = '--------------------';
$state_add_amt[2] = '--------------------';
$state_add_per[2] = '--------------------';
$primary_aba[2] = '--------------------';
$primary_acct[2] = '--------------------';
$primary_type[2] = '--------------------';
$primary_bname[2] = '--------------------';
$primary_amt[2] = '--------------------';
$second_aba[2] = '--------------------';
$second_acct[2] = '--------------------';
$second_type[2] = '--------------------';
$second_bank[2] = '--------------------';
$second_amt[2] = '--------------------';
$third_aba[2] = '--------------------';
$third_acct[2] = '--------------------';
$third_type[2] = '--------------------';
$third_bank[2] = '--------------------';
$third_amt[2] = '--------------------';
$estatus[2] = '--------------------';
$term_date[2] = '--------------------';
$dept[2] = '--------------------';

$sortarr = array(
    'feid',
    'emp_id',
    'ssn',
    'branch',
    'fname',
    'intial',
    'lname',
    'addr1',
    'addr2',
    'city',
    'state',
    'zip',
    'phone',
    'gender',
    'wcode',
    'class',
    'dob',
    'tax_state',
    'work_state',
    'hire_date',
    'fed_marital_status',
    'fed_allowances',
    'fed_amt',
    'fed_percent',
    'state_mstatus',
    'state_allowances',
    'state_add_amt',
    'state_add_per',
    'primary_aba',
    'primary_acct',
    'primary_type',
    'primary_bname',
    'primary_amt',
    'second_aba',
    'second_acct',
    'second_type',
    'second_bank',
    'second_amt',
    'third_aba',
    'third_acct',
    'third_type',
    'third_bank',
    'third_amt',
    'estatus',
    'term_date',
    'dept'
);
$arr_count = count($sortarr);
$rep_company = $companyname . '<br>';
$rep_header = 'Tricom Employee Report'; //report title
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

if ($emp_name != '')
{
    $custalign = '<br>';
}
else
{
    $emp_name = 'ALL';
}
$emp_status = ($employee_status == "Y") ? 'Terminated' : 'Active';
if ($employee_status == 'ALL')
{
    $emp_status = 'ALL';
}
//Displaying selected filters for Run report
$EmployeeName = $emp_name ? 'Employee Name: ' . $emp_name . $custalign . "&nbsp;&nbsp;&nbsp;&nbsp;" : '';
$EmployeeStatus = $emp_status ? 'Employee Status: ' . $emp_status . $custalign . "&nbsp;&nbsp;&nbsp;&nbsp;" : '';
$Department = $dept_name[0] ? 'HRM Departments: ' . $dept_name[0] . $custalign . "&nbsp;&nbsp;&nbsp;&nbsp;" : '';
$LastExpDate = 'Last Exported Date: ' . $last_exp_date . $custalign . "&nbsp;&nbsp;&nbsp;&nbsp;";
$rep_title = $EmployeeName . $EmployeeStatus . $Department . $LastExpDate;
$k = 0;

if ($emp_name != "" && $emp_name != 'ALL')
{
    $where_cond .= " AND emp.name = '" . $emp_name . "'";
}
if ($employee_status != "" && $employee_status != 'ALL')
{
    $where_cond .= " AND emp.empterminated = '" . $employee_status . "'";
}
if ($dept_id != "")
{
    $where_cond .= " AND hcomp.dept !='0' AND hcomp.dept IN(" . $dept_id . ")";
}
if ($last_exp_date != "")
{
    //$where_cond .= " AND str_to_date(emp.mtime,'%Y-%m-%d') >= str_to_date('" . date("Y-m-d", strtotime($last_exp_date)) . "','%Y-%m-%d') ";

    $where_cond .= " AND emp.mtime >= '".date("Y-m-d H:i:s", strtotime($last_exp_date))."'";
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

$final_query = "SELECT cm.feid AS 'FEIN',
       emp.sno AS 'EmployeeID',
       hrper.ssn AS 'SSN',
       dept.depcode AS 'BranchName',
       hg.fname AS 'FirstName',
       hg.mname AS 'Initial',
       hg.lname AS 'LastName',
       hg.address1 AS 'HomeAddress1',
       hg.address2 AS 'HomeAddress2',
       hg.city AS 'HomeCity',
       hg.state AS 'HomeState',
       hg.zip AS 'HomeZip',
       CONCAT(hg.wphone_extn, ' ', hg.wphone) AS 'PhoneNumber',
       IF(hrper.hp_gender = 'N', '', hrper.hp_gender) AS 'Gender',
       IF(hcomp.assign_wcompcode = 'Y', tmp.ass_code, emp_wc.code)
          AS 'WcCode',
       IF(
          hrcon_w4.tax = 'C-to-C',
          'C',
          IF(
             hcomp.job_type = 'Y',
             tmp.ass_jtype,
             IF(emp_mg.name = 'Temp/Contract',
                'T',
                IF(emp_mg.name = 'Internal Temp/Contract', 'I', ''))))
          AS 'Class',
       hrper.d_birth AS 'DOB',
       st_code.state_abbr AS 'TaxState',
       IF(hcomp.assign_wcompcode = 'Y', REPLACE(tmp.ass_state,'N/A',''),REPLACE(emp_wc.state,'N/A',''))
          AS 'WorkState',
       DATE_FORMAT(STR_TO_DATE(hcomp.date_hire, '%m-%d-%Y'), '%m/%d/%Y')
          AS 'HireDate',
       hrcon_w4.fstatus AS 'FedMaritalStatus',
       hrcon_w4.tnum AS 'FedAllowances',
       IF(hrcon_w4.aftaw_curr != '%', hrcon_w4.aftaw, '') AS 'FedAddlAmount',
       IF(hrcon_w4.aftaw_curr = '%', hrcon_w4.aftaw, '') AS 'FedAddlPercent',
       hrcon_w4.fsstatus AS 'StateMaritalStatus',
       hrcon_w4.tstatetax AS 'StateAllowances',
       IF(hrcon_w4.astaw_curr != '%', hrcon_w4.astaw, '')
          AS 'StateAddlAmount',
       IF(hrcon_w4.astaw_curr = '%', hrcon_w4.astaw, '')
          AS 'StateAddlPercent',
       IF(m.name = 'Direct Deposit', hrbnk.bankrtno, '') AS 'PrimaryABA',
       IF(hrbnk.bankrtno != '' AND m.name = 'Direct Deposit',
          hrbnk.bankacno,
          '')
          AS 'PrimaryAcct',
       IF(
          hrbnk.bankrtno != '' AND m.name = 'Direct Deposit',
          IF(hrbnk.acc1_type = 'CHECKING',
             'C',
             IF(hrbnk.acc1_type = 'SAVINGS', 'S', '')),
          '')
          AS 'PrimaryType',
       IF(hrbnk.bankrtno != '' AND m.name = 'Direct Deposit',
          hrbnk.bankname,
          '')
          AS 'PrimaryBankName',
       IF(m.name = 'Direct Deposit',
          IF(hrbnk.acc1_payperiod = 'percent', '', hrbnk.acc1_amt),
          '')
          AS 'PrimaryAmt',
       IF(m.name = 'Direct Deposit', hrbnk.acc2_bankrtno, '') AS 'SecondABA',
       IF(hrbnk.acc2_bankrtno != '' AND m.name = 'Direct Deposit',
          hrbnk.acc2_bankacno,
          '')
          AS 'SecondAcct',
       IF(
          hrbnk.acc2_bankrtno != '' AND m.name = 'Direct Deposit',
          IF(hrbnk.acc2_type = 'CHECKING',
             'C',
             IF(hrbnk.acc2_type = 'SAVINGS', 'S', '')),
          '')
          AS 'SecondType',
       IF(hrbnk.acc2_bankrtno != '' AND m.name = 'Direct Deposit',
          hrbnk.acc2_bankname,
          '')
          AS 'SecondBankName',
       IF(m.name = 'Direct Deposit',
          IF(hrbnk.acc2_payperiod = 'percent', '', hrbnk.acc2_amt),
          '')
          AS 'SecondAmt',
       IF(m.name = 'Direct Deposit', hrbnk.acc3_bankrtno, '') AS 'ThirdABA',
       IF(hrbnk.acc3_bankrtno != '' AND m.name = 'Direct Deposit',
          hrbnk.acc3_bankacno,
          '')
          AS 'ThirdAcct',
       IF(
          hrbnk.acc3_bankrtno != '' AND m.name = 'Direct Deposit',
          IF(hrbnk.acc3_type = 'CHECKING',
             'C',
             IF(hrbnk.acc3_type = 'SAVINGS', 'S', '')),
          '')
          AS 'ThirdType',
       IF(hrbnk.acc3_bankrtno != '' AND m.name = 'Direct Deposit',
          hrbnk.acc3_bankname,
          '')
          AS 'ThirdBankName',
       IF(m.name = 'Direct Deposit',
          IF(hrbnk.acc3_payperiod = 'percent', '', hrbnk.acc3_amt),
          '')
          AS 'ThirdAmt',
       IF(emp.empterminated = 'N', 'Active', 'Terminated') AS 'EmpStatus',
       IF(emp.empterminated = 'N', '', DATE_FORMAT(emp.tdate, '%m/%d/%Y'))
          AS 'TermDate',
       dept.depcode AS 'Department'
  FROM emp_list emp
       INNER JOIN hrcon_general hg
          ON (hg.username = emp.username AND hg.ustatus = 'active')
       LEFT JOIN hrcon_personal hrper
          ON (hrper.username = emp.username AND hrper.ustatus = 'active')
       LEFT JOIN hrcon_compen hcomp
          ON (hcomp.username = emp.username AND hcomp.ustatus = 'active')
       LEFT JOIN hrcon_w4
          ON (    hrcon_w4.username = emp.username
              AND hrcon_w4.ustatus = 'active')
       LEFT JOIN hrcon_deposit hrbnk
          ON (hrbnk.username = emp.username AND hrbnk.ustatus = 'active')
       LEFT JOIN manage m
          ON (    m.sno = hrbnk.delivery_method
              AND m.type = 'deliverymethod'
              AND m.name = 'Direct Deposit')
       LEFT JOIN contact_manage cm ON (cm.serial_no = hcomp.location)
       LEFT JOIN department dept
          ON (dept.sno = hcomp.dept AND dept.status = 'Active')
       LEFT JOIN manage emp_mg
          ON (    emp_mg.sno = hcomp.emptype
              AND emp_mg.type = 'jotype'
              AND hcomp.job_type = 'N')
       LEFT JOIN workerscomp emp_wc
          ON (    emp_wc.workerscompid = hcomp.wcomp_code
              AND emp_wc.status = 'active')
       LEFT JOIN state_codes st_code
          ON (st_code.state_id = hrcon_w4.state_withholding)
       LEFT JOIN
       (SELECT wc.code AS 'ass_code',
               wc.state AS 'ass_state',
               IF(mg.name = 'Temp/Contract',
                  'T',
                  IF(mg.name = 'Internal Temp/Contract', 'I', ''))
                  AS 'ass_jtype',
               hj.username AS 'emp_username'
          FROM hrcon_jobs hj
               INNER JOIN emp_list el ON (el.username = hj.username)
               LEFT JOIN workerscomp wc
                  ON (    wc.workerscompid = hj.wcomp_code
                      AND wc.status = 'active')
               LEFT JOIN manage mg
                  ON (mg.sno = hj.jotype AND mg.type = 'jotype')
        ORDER BY hj.sno DESC
         LIMIT 1) tmp
          ON (tmp.emp_username = emp.username)
         WHERE 1= 1 " . $where_cond . " ";
$final_res = mysql_query($final_query, $db);
$final_count = mysql_num_rows($final_res);
$timesheet_sno = array();
$timesheet_parid = array();
$expense_sno = array();
$expense_parid = array();
$regular_rates = array();
$final_data = array();

$marital_code = array(
    'Single' => '1',
    'Married' => '2',
    'Married Filing Separate' => '3',
    'Married Both Spouses Working' => '4',
    'Married One Spouse Working' => '5',
    'Head of Household' => '6',
    'Married Multiple Employers' => '7',
    'Widow or Widower' => '8',
    'Married Not Living With Spouse' => '9',
    'Married Joint Claiming All' => '10',
    'Married Joint Claiming Half' => '11',
    'Married Separate Claiming All' => '12',
    'Married Joint Claiming Non' => '13',
    'Married Living With Spouse' => '14',
    'Married Withhold at Single' => '15',
    'Civil Union' => '16',
    'Civil Union Withhold at Single' => '17',
    'Single or Married filing separately' => '3'
);

$state_marital_code = array(
    'S' => '1',
    'M' => '2',
    'H' => '6',
    'MS' => '3'
);

while ($row = mysql_fetch_array($final_res))
{
    $final_data[] = $row;

}
function formatted_feid($val){
  $feid = preg_replace('/\D/', '', $val);
  $feid_val = substr($feid,0,2).'-'.substr($feid,2,4);
  return $feid_val;
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
// Form the report data
$i = 1;

foreach ($final_data as $final_arr)
{
    $ii = 0;

    // Array for all column's data
    $values_array = array(
        'feid' => ($final_arr['FEIN'] != '' ? formatted_feid(substr($final_arr['FEIN'], 0, 10)) : ''),
        'emp_id' => ($final_arr['EmployeeID'] != '' ? substr($final_arr['EmployeeID'], 0, 9) : ''),
        'ssn' => ($ac_aced->decrypt($final_arr['SSN'])!= '' ? substr(formatted_ssn($ac_aced->decrypt($final_arr['SSN'])), 0, 11) : ''),
        'branch' => ($final_arr['BranchName'] != '' ? substr($final_arr['BranchName'], 0, 30) : ''),
        'fname' => ($final_arr['FirstName'] != '' ? substr($final_arr['FirstName'], 0, 15) : ''),
        'intial' => ($final_arr['Initial'] != '' ? substr($final_arr['Initial'], 0, 1) : ''),
        'lname' => ($final_arr['LastName'] != '' ? substr($final_arr['LastName'], 0, 30) : ''),
        'addr1' => ($final_arr['HomeAddress1'] != '' ? substr($final_arr['HomeAddress1'], 0,30) : ''),
        'addr2' => ($final_arr['HomeAddress2'] != '' ? substr($final_arr['HomeAddress2'], 0,30) : ''),
        'city' => ($final_arr['HomeCity'] != '' ? substr($final_arr['HomeCity'], 0,18) : ''),
        'state' => ($final_arr['HomeState'] != '' ? substr($final_arr['HomeState'],0, 2) : ''),
        'zip' => ($final_arr['HomeZip'] != '' ? formatted_zip(substr($final_arr['HomeZip'], 0,10)) : ''),
        'phone' => ($final_arr['PhoneNumber'] == '' || $final_arr['PhoneNumber'] == '--' )? '' :formatted_phone(substr($final_arr['PhoneNumber'], 0,13)),
        'gender' => ($final_arr['Gender'] != '' ? substr($final_arr['Gender'],0, 1) : ''),
        'wcode' => ($final_arr['WcCode'] != '' ? substr($final_arr['WcCode'],0, 6) : ''),
        'class' => ($final_arr['Class'] != '' ? substr($final_arr['Class'],0, 1) : ''),
        'dob' => get_standard_dateFormat($ac_aced->decrypt($final_arr['DOB']), 'm-d-Y','m/d/Y'),
        'tax_state' => ($final_arr['TaxState'] != '' ? substr($final_arr['TaxState'],0, 2) : ''),
        'work_state' => ($final_arr['WorkState'] != '' ? substr($final_arr['WorkState'],0, 2) : ''),
        'hire_date' => ($final_arr['HireDate'] != '' ? substr($final_arr['HireDate'], 0,10) : ''),
        'fed_marital_status' => ($marital_code[$final_arr['FedMaritalStatus']]!= '' ? substr($marital_code[$final_arr['FedMaritalStatus']],0, 2) : ''),
        'fed_allowances' => ($final_arr['FedAllowances'] != '' ? substr($final_arr['FedAllowances'],0, 2) : ''),
        'fed_amt' => ($final_arr['FedAddlAmount'] != '' ? substr($final_arr['FedAddlAmount'],0, 7) : ''),
        'fed_percent' => ($final_arr['FedAddlPercent'] != '' ? substr($final_arr['FedAddlPercent'],0, 5) : ''),
        'state_mstatus' => ($marital_code[$final_arr['StateMaritalStatus']]!= '' ? substr($marital_code[$final_arr['StateMaritalStatus']],0, 2) : ''),
        'state_allowances' => ($final_arr['StateAllowances'] != '' ? substr($final_arr['StateAllowances'],0, 2) : ''),
        'state_add_amt' => ($final_arr['StateAddlAmount'] != '' ? substr($final_arr['StateAddlAmount'],0, 7) : ''),
        'state_add_per' => ($final_arr['StateAddlPercent'] != '' ? substr($final_arr['StateAddlPercent'],0, 5) : ''),
        'primary_aba' => ($final_arr['PrimaryABA'] != '' ? substr($ac_aced->decrypt($final_arr['PrimaryABA']),0, 9) : ''),
        'primary_acct' => ($final_arr['PrimaryAcct'] != '' ? substr($ac_aced->decrypt($final_arr['PrimaryAcct']), 0,17) : ''),
        'primary_type' => ($final_arr['PrimaryType'] != '' ? substr($final_arr['PrimaryType'],0, 1) : ''),
        'primary_bname' => ($final_arr['PrimaryBankName'] != '' ? substr($final_arr['PrimaryBankName'], 0,30) : ''),
        'primary_amt' => ($final_arr['PrimaryAmt'] != '' ? substr($final_arr['PrimaryAmt'], 0,11) : ''),
        'second_aba' => ($final_arr['SecondABA'] != '' ? substr($ac_aced->decrypt($final_arr['SecondABA']),0, 9) : ''),
        'second_acct' => ($final_arr['SecondAcct'] != '' ? substr($ac_aced->decrypt($final_arr['SecondAcct']), 0,17) : ''),
        'second_type' => ($final_arr['SecondType'] != '' ? substr($final_arr['SecondType'],0, 1) : ''),
        'second_bank' => ($final_arr['SecondBankName'] != '' ? substr($final_arr['SecondBankName'], 0,30) : ''),
        'second_amt' => ($final_arr['SecondAmt'] != '' ? substr($final_arr['SecondAmt'], 0,11) : ''),
        'third_aba' => ($final_arr['ThirdABA'] != '' ? substr($ac_aced->decrypt($final_arr['ThirdABA']),0, 9) : ''),
        'third_acct' => ($final_arr['ThirdAcct'] != '' ? substr($ac_aced->decrypt($final_arr['ThirdAcct']), 0,17) : ''),
        'third_type' => ($final_arr['ThirdType'] != '' ? substr($final_arr['ThirdType'],0, 1) : ''),
        'third_bank' => ($final_arr['ThirdBankName'] != '' ? substr($final_arr['ThirdBankName'], 0,30) : ''),
        'third_amt' => ($final_arr['ThirdAmt'] != '' ? substr($final_arr['ThirdAmt'], 0,11) : ''),
        'estatus' => ($final_arr['EmpStatus'] != '' ? substr($final_arr['EmpStatus'],0, 1) : ''),
        'term_date' => ($final_arr['TermDate'] != '' ? substr($final_arr['TermDate'], 0,10) : ''),
        'dept' => ($final_arr['Department']!= '' ? substr($final_arr['Department'],0, 5) : ''),

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
if(($dept_name[0]!= '' && $dept_name[0]!= 'ALL') || ($emp_name!= 'ALL' && $emp_name!= '') || ($employee_status!= 'ALL' && $employee_status!= '')){
    $file = 'Tricom-Employee-' . $dateval.'-'.$timeval.'-'.$meridian.'-Filtered.' . $format;
}else{
    $file = 'Tricom-Employee-' . $dateval.'-'.$timeval.'-'.$meridian.'.' . $format;
}
$mime = 'application/' . $format;
$heading_names = array(
    'FEIN',
    'EmployeeID',
    'SSN',
    'BranchName',
    'FirstName',
    'Initial',
    'LastName',
    'HomeAddress1',
    'HomeAddress2',
    'HomeCity',
    'HomeState',
    'HomeZip',
    'PhoneNumber',
    'Gender',
    'WorkCompCode',
    'Class',
    'DOB',
    'TaxState',
    'WorkState',
    'HireDate',
    'FedMaritalStatus',
    'FedAllowances',
    'FedAddlAmount',
    'FedAddlPercent',
    'StateMaritalStatus',
    'StateAllowances',
    'StateAddlAmount',
    'StateAddlPercent',
    'PrimaryABA',
    'PrimaryAcct',
    'PrimaryType',
    'PrimaryBankName',
    'PrimaryAmt',
    'SecondABA',
    'SecondAcct',
    'SecondType',
    'SecondBankName',
    'SecondAmt',
    'ThirdABA',
    'ThirdAcct',
    'ThirdType',
    'ThirdBankName',
    'ThirdAmt',
    'EmpStatus',
    'TermDate',
    'Department'
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

if ($defaction == "print") echo "<script>window.print(); window.setInterval('window.close();', 10000)</script>";

?>
