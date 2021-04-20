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

$rlib_filename = 'TricomTimesheet.xml';

if ($rptFrom != "0" && $rptFrom != "")
{
    $module = "TricomTimesheet" . $rptFrom;
}
else $module = "TricomTimesheet";

/*if ($view == "myreport")
{
    $rquery = "select reportoptions from reportdata where reportid='$id'";
    $rresult = mysql_query($rquery, $db);
    $vrowdata = mysql_fetch_row($rresult);
    $vrow = explode("|username->", $vrowdata[0]);
    $TricomTimesheet = $vrow[0];
    $cusername = $vrow[1];
    if (strpos($TricomTimesheet, "|username->") != 0) $TricomTimesheet = $vrow[0];
    session_update("cusername");
    session_update("TricomTimesheet");
}*/

$frm_values = explode('|', $TricomTimesheet);

$decimalPref = getDecimalPreference();

//Where condition for customers selected in filter
$where_cond = "";
// echo '<pre>'; print_r($frm_values);
$emp_name = $frm_values[0];
$timesheetstatus = $frm_values[1];
$timesheetdate_values = explode('*', $frm_values[2]);
$ts_sdate = $timesheetdate_values[0];
$ts_edate = $timesheetdate_values[1];
//echo $ts_sdate.'==='.$timesheetdate_values[0];
$dept = $frm_values[3];
$ts_wherecon = "";

if ($timesheetstatus != "")
{
    $timesheetstatus = explode('!#!', $frm_values[1]);
    if ($accountingExport == 'Exported')
    {
        $export_ystr = " AND th.exported_status='YES' ";
        $export_nstr = " AND th.exported_status !='YES' ";

        $exp_export_ystr = " AND e.exported_status='YES' ";
        $exp_export_nstr = " AND e.exported_status !='YES' ";

    }
    else
    {
        $export_ystr = " ";
        $export_nstr = " ";

        $exp_export_ystr = " ";
        $exp_export_nstr = " ";
    }

    $tsStatus = '';
    $tsexported = '';
    foreach ($timesheetstatus as $status)
    {
        if ($status != 'Exported')
        {
            $tsStatus = $tsStatus . "'" . $status . "',";
        }
    }

    $tsStatus = substr($tsStatus, 0, -1);
    if (in_array('ALL', $timesheetstatus)) // all selected
    
    {
        $pstatus = " AND  astatus IN ('Approved','Billed','ER') ";
        $nstatus = " AND  status IN ('Approved','Billed','ER') ";
        $ts_wherecon = " AND th.status IN ('Approved','Billed','ER')";
        $exp_wherecond = " AND e.status IN ('Approved','Billed','ER')";
    }
    else if (in_array('Exported', $timesheetstatus) && !in_array('ER', $timesheetstatus) && !in_array('Approved', $timesheetstatus)) // selected  only exported
    
    {
        /*$ts_wherecon .=  " AND th.status IN ('Approved','ER') AND th.exported_status='YES'";
         $exp_wherecond .=  " AND e.status IN ('Approved','ER') ";*/
        $pstatus = " AND  astatus IN ('Approved','Billed','ER') ";
        $nstatus = " AND  status IN ('Approved','Billed','ER') ";

        // $nstring =  " AND th.status IN ('Approved','ER') AND th.exported_status='YES'";
        $ts_wherecon = " AND th.status IN ('Approved','Billed','ER') " . $export_ystr;
        $exp_wherecond = " AND e.status IN ('Approved','Billed','ER') " . $exp_export_ystr;
    }
    else if (!in_array('Exported', $timesheetstatus) && in_array('ER', $timesheetstatus) && !in_array('Approved', $timesheetstatus)) // selected  only submitted
    
    {
        $pstatus = " AND  astatus ='ER' ";
        $nstatus = " AND status = 'ER' ";
        $ts_wherecon = " AND TRIM(th.status) ='ER'" . $export_nstr;
        $exp_wherecond = " AND TRIM(e.status) ='ER' " . $exp_export_nstr;
    }
    else if (!in_array('Exported', $timesheetstatus) && !in_array('ER', $timesheetstatus) && in_array('Approved', $timesheetstatus)) // selected  only approved
    
    {
        $nstatus = " AND status IN ('Approved','Billed') ";
        $pstatus = " AND  astatus IN ('Approved','Billed','ER') ";
        $ts_wherecon = " AND th.status IN ('Approved','Billed')" . $export_nstr;
        $exp_wherecond = " AND e.status IN ('Approved','Billed')" . $exp_export_nstr;
    }
    else if (!in_array('Exported', $timesheetstatus) && in_array('ER', $timesheetstatus) && in_array('Approved', $timesheetstatus)) // select submitted and approved
    
    {
        $nstatus = " AND  status IN ('Approved','Billed','ER') ";
        $pstatus = " AND  astatus IN ('Approved','Billed','ER') ";
        $ts_wherecon = " AND th.status IN ('Approved','Billed','ER') " . $export_nstr;
        $exp_wherecond = " AND (e.status IN ('Approved','Billed','ER'))" . $exp_export_nstr;
    }
    else if (in_array('Exported', $timesheetstatus) && in_array('ER', $timesheetstatus) && !in_array('Approved', $timesheetstatus)) // select exported and submitted
    
    {
        $nstatus = " AND  status ='ER' ";
        $pstatus = " AND  astatus ='ER' ";
        $ts_wherecon = " AND TRIM(th.status) ='ER' AND th.exported_status ='YES'" . $export_ystr;
        $exp_wherecond = " AND TRIM(e.status) ='ER' AND e.exported_status ='YES' " . $exp_export_ystr;
    }
    else if (in_array('Exported', $timesheetstatus) && !in_array('ER', $timesheetstatus) && in_array('Approved', $timesheetstatus)) // select exported and approved
    
    {
        $pstatus = " AND  astatus IN ('Approved','Billed','ER') ";
        $nstatus = " AND status IN ('Approved','Billed') ";
        $ts_wherecon = " AND th.status IN ('Approved','Billed') AND th.exported_status='YES'" . $export_ystr;
        $exp_wherecond = " AND e.status IN ('Approved','Billed')AND e.exported_status='YES'" . $exp_export_ystr;
    }
}

if ($timesheetdate_values[0]!= '')
{
    $ts_wherecon .= ' AND th.sdate >= str_to_date(\'' . $timesheetdate_values[0] . '\',\'%m/%d/%Y\')';
    $exp_wherecond .= ' AND e.edate >= str_to_date(\'' . $timesheetdate_values[0] . '\',\'%m/%d/%Y\')';

}
if ($timesheetdate_values[1]!= "")
{
    $ts_wherecon .= ' AND th.sdate <= str_to_date(\'' . $timesheetdate_values[1] . '\',\'%m/%d/%Y\')';
    $exp_wherecond .= ' AND e.edate <= str_to_date(\'' . $timesheetdate_values[1] . '\',\'%m/%d/%Y\')';

}
if ($emp_name != '')
{
    $ts_wherecon .= " AND el.name = '" . $emp_name . "'";
    $exp_wherecond .= " AND el.name = '" . $emp_name . "'";
    $emp_wherecond .= " AND el.name = '" . $emp_name . "'";
}

$selDepartmentsList = $frm_values[3];
$dept_id = str_replace('ALL,', '', $frm_values[3]);

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
if ($dept_id != "")
{
    $ts_wherecon .= "AND dept.sno !='0'  AND dept.sno IN(" . $dept_id . ") ";
    $exp_wherecond .= "AND dept.sno !='0'  AND dept.sno IN(" . $dept_id . ") ";
    $emp_wherecond .= "AND dept.sno !='0'  AND dept.sno IN(" . $dept_id . ") ";

}

// Report ids
$joborder_id[0] = 'joborder_id';
$assignment_id[0] = 'assignment_id';
$cust_id[0] = 'cust_id';
$employee_id[0] = 'employee_id';
$work_date[0] = 'work_date';
$std_pay_hrs[0] = 'std_pay_hrs';
$ot_pay_hrs[0] = 'ot_pay_hrs';
$dt_pay_hrs[0] = 'dt_pay_hrs';
$hol_pay_hrs[0] = 'dt_pay_hrs';
$std_bill_hrs[0] = 'std_bill_hrs';
$ot_bill_hrs[0] = 'ot_bill_hrs';
$dt_bill_hrs[0] = 'dt_bill_hrs';
$hol_bill_hrs[0] = 'hol_bill_hrs';
$addl_type[0] = 'addl_type';
$addl_code[0] = 'addl_code';
$addl_amt_type[0] = 'addl_amt_type';
$addl_pay_amt[0] = 'add_pay_amt';
$addl_bill_amt[0] = 'addl_bill_amt';
$workcomp_code[0] = 'workcomp_code';
$work_state[0] = 'work_state';
$rate_override[0] = 'rate_override';
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
$usr_fld_override[0] = 'usr_fld_override';
$user_field1[0] = 'user_field1';
$user_field2[0] = 'user_field2';
$user_field3[0] = 'user_field3';
$comments[0] = 'comments';
$late_hrs_flag[0] = 'late_hrs_flag';
$ded_cycle[0] = 'ded_cycle';
$pay_sep_chk[0] = 'pay_sep_chk';
$bill_sep_inv[0] = 'bill_sep_inv';
$cmtn1[0] = 'cmtn1';
$cmtn2[0] = 'cmtn2';
$cmtn3[0] = 'cmtn3';
$adjustments[0] = 'adjustments';

// Report Heading Names


$joborder_id[1] = 'JobOrderID';
$assignment_id[1] = 'AssignmentID';
$cust_id[1] = 'CustomerID';
$employee_id[1] = 'EmployeeID';
$work_date[1] = 'WorkDate';
$std_pay_hrs[1] = 'StdPayHrs';
$ot_pay_hrs[1] = 'OTPayHrs';
$dt_pay_hrs[1] = 'DblPayHrs';
$hol_pay_hrs[1] = 'HolPayHrs';
$std_bill_hrs[1] = 'StdBillHrs';
$ot_bill_hrs[1] = 'OTBillHrs';
$dt_bill_hrs[1] = 'DblBillHrs';
$hol_bill_hrs[1] = 'HolBillHrs';
$addl_type[1] = 'AddlType';
$addl_code[1] = 'AddlCode';
$addl_amt_type[1] = 'AddlAmtType';
$addl_pay_amt[1] = 'AddlPayAmt';
$addl_bill_amt[1] = 'AddlBillAmt';
$workcomp_code[1] = 'WorkCompCode';
$work_state[1] = 'WorkState';
$rate_override[1] = 'RateOverride';
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
$usr_fld_override[1] = 'UsrFldOverride';
$user_field1[1] = 'UserField1';
$user_field2[1] = 'UserField2';
$user_field3[1] = 'UserField3';
$comments[1] = 'Comments';
$late_hrs_flag[1] = 'LateHoursFlag';
$ded_cycle[1] = 'DedCycle';
$pay_sep_chk[1] = 'PaySepChk';
$bill_sep_inv[1] = 'BillSepInv';
$cmtn1[1] = 'Comment1';
$cmtn2[1] = 'Comment2';
$cmtn3[1] = 'Comment3';
$adjustments[1] = 'Adjustment';

// Report Heading Seperations
$joborder_id[2] = '---------------';
$assignment_id[2] = '---------------';
$cust_id[2] = '---------------';
$employee_id[2] = '---------------';
$work_date[2] = '---------------';
$std_pay_hrs[2] = '---------------';
$ot_pay_hrs[2] = '---------------';
$dt_pay_hrs[2] = '---------------';
$hol_pay_hrs[2] = '---------------';
$std_bill_hrs[2] = '---------------';
$ot_bill_hrs[2] = '---------------';
$dt_bill_hrs[2] = '---------------';
$hol_bill_hrs[2] = '---------------';
$addl_type[2] = '---------------';
$addl_code[2] = '---------------';
$addl_amt_type[2] = '---------------';
$addl_pay_amt[2] = '---------------';
$addl_bill_amt[2] = '---------------';
$workcomp_code[2] = '---------------';
$work_state[2] = '---------------';
$rate_override[2] = '---------------';
$pay_rate[2] = '---------------';
$OTDbl_payRate_type[2] = '-------------------';
$OTPay_rate[2] = '---------------';
$DblPay_rate[2] = '---------------';
$hol_payRate_type[2] = '------------------';
$holPay_rate[2] = '---------------';
$bill_rate[2] = '---------------';
$OTDblBill_rate_type[2] = '-------------------';
$OTBill_rate[2] = '---------------';
$DblBill_rate[2] = '---------------';
$holBill_rate_type[2] = '---------------';
$holBill_rate[2] = '---------------';
$usr_fld_override[2] = '---------------';
$user_field1[2] = '---------------';
$user_field2[2] = '---------------';
$user_field3[2] = '---------------';
$comments[2] = '---------------';
$late_hrs_flag[2] = '---------------';
$ded_cycle[2] = '---------------';
$pay_sep_chk[2] = '---------------';
$bill_sep_inv[2] = '---------------';
$cmtn2[2] = $cmtn2[2] = '---------------';
$cmtn3[2] = '---------------';
$adjustments[2] = '---------------';

$sortarr = array(
    'joborder_id',
    'assignment_id',
    'cust_id',
    'employee_id',
    'work_date',
    'std_pay_hrs',
    'ot_pay_hrs',
    'dt_pay_hrs',
    'hol_pay_hrs',
    'std_bill_hrs',
    'ot_bill_hrs',
    'dt_bill_hrs',
    'hol_bill_hrs',
    'addl_type',
    'addl_code',
    'addl_amt_type',
    'addl_pay_amt',
    'addl_bill_amt',
    'workcomp_code',
    'work_state',
    'rate_override',
    'pay_rate',
    'OTDbl_payRate_type',
    'OTPay_rate',
    'DblPay_rate',
    'hol_payRate_type',
    'holPay_rate',
    'bill_rate',
    'OTDblBill_rate_type',
    'OTBill_rate',
    'DblBill_rate',
    'holBill_rate_type',
    'holBill_rate',
    'usr_fld_override',
    'user_field2',
    'user_field2',
    'user_field3',
    'comments',
    'late_hrs_flag',
    'ded_cycle',
    'pay_sep_chk',
    'bill_sep_inv',
    'cmtn2',
    'cmtn3',
    'adjustments'
);
$arr_count = count($sortarr);
$rep_company = $companyname . '<br>';
$rep_header = 'Tricom Timesheet Report'; //report title
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
if ($job_title != '')
{
    $custalign = '<br>';
}
else
{
    $job_title = 'ALL';
}
if ($assign_id != '')
{
    $custalign = '<br>';
}
else
{
    $assign_id = 'ALL';
}
//Displaying selected filters for Run report
$empalign = '';
$TimesheetDate = '';
$empalignspaces = '';

if ($emp_name != '')
{
    $empalign = '<br />';
}

if ($timesheetdate_values[0] != '' || $timesheetdate_values[1] != '')
{
    $TimesheetDate = "success";
    $timesheetdatealign = '<br />';
}

$timestatuslist = '';

if ($timesheetstatus == 'ALL')
{
    $timestatuslist = "ALL";

}

if (in_array('ALL', $timesheetstatus))
{
    $timestatuslist = 'ALL';
}
else
{
    if (in_array('Approved', $timesheetstatus))
    {
        $timestatuslist .= 'Approved';
    }

    if (in_array('Approved', $timesheetstatus) && in_array('ER', $timesheetstatus))
    {
        $timestatuslist .= ', ';
    }

    if (in_array('ER', $timesheetstatus))
    {
        $timestatuslist .= 'Submitted';
    }

    if ((in_array('ER', $timesheetstatus) || in_array('Approved', $timesheetstatus)) && in_array('Exported', $timesheetstatus))
    {
        $timestatuslist .= ', ';
    }

    if (in_array('Exported', $timesheetstatus))
    {
        $timestatuslist .= 'Exported';
    }
}

$rep_company = $companyname;
$rep_header = 'Tricom Timesheet Report';
$rep_date = 'date';
$EmployeeName = 'Employee Name: ' . $emp_name . $custalign . '&nbsp;&nbsp;&nbsp;&nbsp;';
$Date =  'Timesheet/Expense Date: ' . $timesheetdate_values[0] . ' - ' . $timesheetdate_values[1] . $custalign . '&nbsp;&nbsp;&nbsp;&nbsp;';
$TimesheetStatus = $timestatuslist ? 'Timesheet/Expense Status: ' . $timestatuslist . $custalign . '&nbsp;&nbsp;&nbsp;&nbsp;' : '';
$Department = $dept_name[0] ? 'HRM Departments: ' . $dept_name[0] . $custalign . '&nbsp;&nbsp;&nbsp;&nbsp;' : '';

$rep_title = $EmployeeName . $TimesheetStatus . $Date . $Department;
$k = 0;

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

$setQry = "SET SESSION group_concat_max_len=1073740800";
mysql_query($setQry, $db);

$addl_codes = array(
    'Sick Pay' => '104',
    'Vacation' => '105',
    'Differential' => '165',
    'Bonus-Temps' => '120',
    'Attendance Bonus' => '128'
);
$addl_amt = array(
    'Sick Pay' => 'H',
    'Vacation' => 'H',
    'Differential' => 'H'
);

$final_query = "SELECT * FROM ( SELECT 
          hj.posid AS JobOrderID,
          REPLACE(hj.pusername,'ASGN','') AS AssignmentID,
          el.sno AS EmployeeID,
          stacc.sno AS CustomerID,
          DATE_FORMAT(MIN(th.sdate), '%Y%m%d') AS WorkDate,
          rate.name AS cust_rate,
          rate.name AS AddlCode,
        IF(rate.name='Regular',
        IF(comp.pay_assign='Y',(th.hours*ma.rate),(th.hours*comp.salary)), 
        IF(rate.name='OverTime',
        IF(comp.assign_overtime='Y',(th.hours*ma.rate),(th.hours*comp.over_time)),
        IF(rate.name='DoubleTime',
        IF(comp.assign_double='Y',(th.hours*ma.rate),(th.hours*comp.double_rate_amt)),(th.hours*ma.rate)))) AS AddlPayAmt,
        th.hours*mab.rate AS AddlBillAmt,
          SUM(IF(th.hourstype = 'rate1',th.hours,0.00)) AS StdPayHrs,
          SUM(IF(th.hourstype = 'rate2',th.hours,0.00)) AS OTPayHrs,
          SUM(IF(th.hourstype = 'rate3',th.hours,0.00)) AS DblPayHrs,
          SUM(IF(rate.name = 'Holiday',th.hours,0.00)) AS HolPayHrs,
          SUM(IF(th.hourstype = 'rate1' && th.billable ='Yes',th.hours,0.00)) AS StdBillHrs,
          SUM(IF(th.hourstype = 'rate2' && th.billable ='Yes',th.hours,0.00)) AS OTBillHrs,
          SUM(IF(th.hourstype = 'rate3' && th.billable ='Yes',th.hours,0.00)) AS DblBillHrs,
          SUM(IF(rate.name = 'Holiday' && th.billable ='Yes',th.hours,0.00)) AS HolBillHrs,
          CONCAT(REPLACE(wc.state,'N/A',''),wc.code) AS WorkCompCode,
          REPLACE(wc.state,'N/A','') AS WorkState,
          rate.peditable AS RateOverride,
          IF(th.hourstype = 'rate1',IF(comp.pay_assign='Y',ma.rate,comp.salary),0.00)AS PayRate,
          IF(th.hourstype = 'rate2',IF(comp.assign_overtime='Y',ma.rate,comp.over_time),0.00)AS OTPayRate,
          IF(th.hourstype = 'rate3',IF(comp.assign_double='Y',ma.rate,comp.double_rate_amt),0.00)AS DblPayRate,
          IF(rate.name = 'Holiday',ma.rate,0.00)AS HolPayRate,
          IF(th.hourstype = 'rate1',IF(comp.pay_assign='Y',mab.rate,comp.salary),0.00)AS BillRate,
          IF(th.hourstype = 'rate2',IF(comp.assign_overtime='Y',mab.rate,comp.over_time),0.00)AS OTBillRate,
          IF(th.hourstype = 'rate3',IF(comp.assign_double='Y',mab.rate,comp.double_rate_amt),0.00)AS DblBillRate,
          IF(rate.name = 'Holiday',mab.rate,0.00)AS HolBillRate,
      IF(rate.name='Regular',
      IF(comp.pay_assign='Y',ma.rate,comp.salary), 
      IF(rate.name='OverTime',
      IF(comp.assign_overtime='Y',ma.rate,comp.over_time),
      IF(rate.name='DoubleTime',
      IF(comp.assign_double='Y',ma.rate,comp.double_rate_amt),ma.rate))) AS Pay_Rate,
      mab.rate AS Bill_Rate,
      'P' AS AddlType,
      GROUP_CONCAT(DISTINCT th.sno) AS Sno,
      GROUP_CONCAT(DISTINCT th.parid) AS Parid,
      th.hourstype,
      'timesheet' AS rowtype
          FROM  timesheet_hours th
          INNER JOIN hrcon_jobs hj ON ( th.assid= hj.pusername AND th.username = hj.username AND hj.ustatus <> 'backup') 
          LEFT JOIN emp_list el ON hj.username=el.username
          LEFT JOIN posdesc pd ON hj.posid = pd.posid
          LEFT JOIN multiplerates_master rate
          ON (rate.rateid = th.hourstype AND rate.status = 'ACTIVE')
          LEFT JOIN multiplerates_assignment ma
          ON (    ma.ratemasterid = th.hourstype
              AND ma.asgnid = hj.sno
              AND ma.asgn_mode = 'hrcon'
              AND ma.status = 'ACTIVE'
              AND ma.ratetype = 'payrate'
           )
          LEFT JOIN multiplerates_assignment mab
          ON (    mab.ratemasterid = th.hourstype
              AND mab.asgnid = hj.sno
              AND mab.asgn_mode = 'hrcon'
              AND mab.status = 'ACTIVE'
              AND mab.ratetype = 'billrate')
          LEFT JOIN department dept ON (dept.sno=hj.deptid AND dept.status='Active')
          LEFT JOIN staffacc_cinfo stacc ON hj.client=stacc.sno 
          LEFT JOIN workerscomp wc ON (wc.workerscompid = hj.wcomp_code AND wc.status = 'active')
          LEFT JOIN hrcon_compen comp ON hj.username=comp.username  AND comp.ustatus = 'active' 
          WHERE  1 
          $ts_wherecon
          AND rate.name IN ('Regular','OverTime','DoubleTime','Holiday','Sick Pay','Vacation','Differential','Bonus-Temps','Attendance Bonus')
          AND hj.jtype !=''  
          AND hj.jotype !='0'
          AND hj.ustatus NOT IN ('cancel','pending')
          GROUP BY th.parid ,th.assid ,th.status ,rate.name
    UNION
    
    SELECT 
          hj.posid AS JobOrderID,
          REPLACE(hj.pusername,'ASGN','') AS AssignmentID,
          el.sno AS EmployeeID,
          stacc.sno AS CustomerID,
          DATE_FORMAT(e.edate, '%Y%m%d') AS WorkDate,
          et.title AS CODE,
          et.code AS AddlCode,
          e.expense_payrate AS AddlPayAmt,
          e.expense_billrate AS AddlBillAmt,
          '' AS StdPayHrs,
          '' AS OTPayHrs,
          '' AS DblPayHrs,
          '' AS HolPayHrs,
          '' AS StdBillHrs,
          '' AS OTBillHrs,
          '' AS DblBillHrs,
          '' AS HolBillHrs,
          CONCAT(REPLACE(wc.state,'N/A',''),wc.code) AS WorkCompCode,
          REPLACE(wc.state,'N/A','') AS WorkState,
          edr.payrate_overwrite AS RateOverride,
          e.unitcost AS PayRate,
          '' AS OTPayRate,
          '' AS DblPayRate,
          '' AS HolPayRate,
          e.billrate AS BillRate,
          '' AS OTBillRate,
          '' AS DblBillRate,
          '' AS HolBillRate,
          '' AS Pay_Rate,
          '' AS Bill_Rate,
          'R' AS AddlType,
          GROUP_CONCAT(DISTINCT e.sno) AS Sno,
          GROUP_CONCAT(DISTINCT e.parid) AS Parid,
          '' AS hourstype,
          'expense' AS rowtype

    FROM expense e
    LEFT JOIN exp_type et ON et.sno = e.expid
    LEFT JOIN par_expense p ON e.parid = p.sno
    LEFT JOIN exp_default_rates edr ON e.expid = edr.etype_id
    LEFT JOIN hrcon_jobs hj ON (p.username = hj.username AND hj.ustatus <> 'backup'
    AND p.username = hj.username
    AND e.assid = hj.pusername
    AND hj.ustatus <> 'backup')
    LEFT JOIN emp_list el ON hj.username=el.username
    LEFT JOIN staffacc_cinfo stacc ON hj.client = stacc.sno
    LEFT JOIN hrcon_compen ecomp ON ecomp.username = hj.username AND ecomp.ustatus = 'active'
    LEFT JOIN hrcon_general hemp ON hj.username = hemp.username
    LEFT JOIN hrcon_w4 ep ON hj.username = ep.username AND ep.ustatus = 'active'
    LEFT JOIN workerscomp wc ON (wc.workerscompid = hj.wcomp_code AND wc.status = 'active')
    LEFT JOIN department dept ON hj.deptid = dept.sno
  WHERE 1  $exp_wherecond 
          AND hj.jtype !=''  
          AND hj.jotype !='0'
          AND hj.ustatus NOT IN ('cancel','pending')
          AND e.status NOT IN ('Backup')
  GROUP BY et.sno, p.username, e.assid
  
  UNION
  
  SELECT 
    
          hj.posid AS JobOrderID,
          REPLACE(hj.pusername,'ASGN','') AS AssignmentID,
          el.sno AS EmployeeID,
          stacc.sno AS CustomerID,
          hd.start_date AS WorkDate,
          hd.deduction_code AS AddlCode,
          hd.deduction_code,
          hd.amount AS AddlPayAmt,
          hd.amount AS AddlBillAmt,
          '' AS StdPayHrs,
          '' AS OTPayHrs,
          '' AS DblPayHrs,
          '' AS HolPayHrs,
          '' AS StdBillHrs,
          '' AS OTBillHrs,
          '' AS DblBillHrs,
          '' AS HolBillHrs,
          CONCAT(REPLACE(wc.state,'N/A',''),wc.code) AS WorkCompCode,
          REPLACE(wc.state,'N/A','') AS WorkState,
          IF(hd.akku_overrideamt!='','Y','N') AS RateOverride,
          '' AS PayRate,
          '' AS OTPayRate,
          '' AS DblPayRate,
          '' AS HolPayRate,
          '' AS BillRate,
          '' AS OTBillRate,
          '' AS DblBillRate,
          '' AS HolBillRate,
          '' AS Pay_Rate,
          '' AS Bill_Rate,
          'D' AS AddlType,
          '' AS Sno,
          '' AS Parid,
          '' AS hourstype,
          'deduction' AS rowtype
  FROM hrcon_deduct hd
  LEFT JOIN emp_list el ON hd.username=el.username AND hd.ustatus <> 'backup'
  LEFT JOIN hrcon_jobs hj ON hd.username = hj.username AND hj.ustatus <> 'backup'
  LEFT JOIN staffacc_cinfo stacc ON hj.client = stacc.sno
  LEFT JOIN hrcon_compen ecomp ON ecomp.username = hj.username AND ecomp.ustatus = 'active'
  LEFT JOIN hrcon_general hemp ON hj.username = hemp.username
  LEFT JOIN workerscomp wc ON (wc.workerscompid = ecomp.wcomp_code AND wc.status = 'active')
  LEFT JOIN department dept ON hj.deptid = dept.sno
  WHERE 1  $emp_wherecond AND
        hj.jtype !=''  
          AND hj.jotype !='0'
          AND hd.ustatus <> 'backup'
          AND hj.ustatus NOT IN ('cancel','pending')
    
  ) te ORDER BY te.hourstype DESC";

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

foreach ($final_data as $final_arr)
{
    $ii = 0;

    // Array for all column's data
    if($final_arr['AddlType'] == 'D' && $final_arr['WorkDate']!=''){
       $wrk_date =date_create($final_arr['WorkDate']);
        $final_arr['WorkDate']= date_format($wrk_date,"Ymd");
    }
    $values_array = array(
        'joborder_id' => substr($final_arr['JobOrderID'], 0, 8) ,
        'assignment_id' => substr($final_arr['AssignmentID'], 0, 10) ,
        'cust_id' => substr($final_arr['CustomerID'], 0, 9) ,
        'employee_id' => substr($final_arr['EmployeeID'], 0, 9) ,
        'work_date' => ($final_arr['WorkDate'] != '' ? substr($final_arr['WorkDate'], 0, 8) : '') ,
        'std_pay_hrs' => $final_arr['StdPayHrs'],
        'ot_pay_hrs' => $final_arr['OTPayHrs'],
        'dt_pay_hrs' => $final_arr['DblPayHrs'],
        'hol_pay_hrs' => $final_arr['HolPayHrs'],
        'std_bill_hrs' => $final_arr['StdBillHrs'],
        'ot_bill_hrs' => $final_arr['OTBillHrs'],
        'dt_bill_hrs' => $final_arr['DblBillHrs'],
        'hol_bill_hrs' => $final_arr['HolBillHrs'],
        'addl_type' => $final_arr['AddlType'],
        'addl_code' => ($final_arr['AddlType'] == 'P' ? ($addl_codes[$final_arr['AddlCode']] != '' ? substr($addl_codes[$final_arr['AddlCode']], 0, 8) : '') : ($final_arr['AddlCode'] != '' ? substr($final_arr['AddlCode'], 0, 8) : '')) ,
        'addl_amt_type' => ($final_arr['AddlType'] == 'P' ? ($addl_codes[$final_arr['AddlCode']] != '' ? substr($addl_amt[$final_arr['AddlCode']], 0, 1) : '') : 'A') ,
        'addl_pay_amt' => $final_arr['AddlPayAmt'],
        'addl_bill_amt' => $final_arr['AddlBillAmt'],
        'workcomp_code' => ($final_arr['WorkCompCode'] != '' ? substr($final_arr['WorkCompCode'], 0, 6) : '') ,
        'work_state' => ($final_arr['WorkState'] != '' ? substr($final_arr['WorkState'], 0, 2) : '') ,
        'rate_override' => $final_arr['RateOverride'],
        'pay_rate' => number_format($final_arr['PayRate'], $decimalPref, '.', '') ,
        'OTDbl_payRate_type' => 'A',
        'OTPay_rate' => $final_arr['OTPayRate'],
        'DblPay_rate' => $final_arr['DblPayRate'],
        'hol_payRate_type' => $final_arr['HolPayRateType'],
        'holPay_rate' => $final_arr['HolPayRate'],
        'bill_rate' => number_format($final_arr['BillRate'], $decimalPref, '.', '') ,
        'OTDblBill_rate_type' => 'A',
        'OTBill_rate' => $final_arr['OTBillRate'],
        'DblBill_rate' => $final_arr['DblBillRate'],
        'holBill_rate_type' => $final_arr['HolBillRateType'],
        'holBill_rate' => $final_arr['HolBillRate'],
        'usr_fld_override' => $final_arr['UsrFldOverride'],
        'user_field1' => '',
        'user_field2' => '',
        'user_field3' => '',
        'comments' => '',
        'late_hrs_flag' => '',
        'ded_cycle' => '',
        'pay_sep_chk' => '',
        'bill_sep_inv' => '',
        'cmtn2' => '',
        'cmtn3' => '',
        'adjustments' => ''
    );
    if ($final_arr['rowtype'] == 'timesheet')
    {
        if (!empty($final_arr['Sno'])) $timesheet_sno[] = $final_arr['Sno'];
        if (!empty($final_arr['Parid'])) $timesheet_parid[] = $final_arr['Parid'];
    }

    if ($final_arr['rowtype'] == 'expense')
    {
        if (!empty($final_arr['Sno'])) $expense_sno[] = $final_arr['Sno'];
        if (!empty($final_arr['Parid'])) $expense_parid[] = $final_arr['Parid'];
    }

    //print_r($timesheet_sno);
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
if(
$emp_name!='' || $TimesheetDate!='' || $timestatuslist!= 'ALL' || $dept_name[0]!= 'ALL'){
    $file = 'Tricom-Timesheet-' . $dateval.'-'.$timeval.'-Filtered.' . $format;
}else{
    $file = 'Tricom-Timesheet-' . $dateval.'-'.$timeval.$format;   
}

$mime = 'application/' . $format;
$heading_names = array(
    'JobOrderID',
    'AssignmentID',
    'CustomerID',
    'EmployeeID',
    'WorkDate',
    'StdPayHrs',
    'OTPayHrs',
    'DblPayHrs',
    'HolPayHrs',
    'StdBillHrs',
    'OTBillHrs',
    'DblBillHrs',
    'HolBillHrs',
    'AddlType',
    'AddlCode',
    'AddlAmtType',
    'AddlPayAmt',
    'AddlBillAmt',
    'WorkCompCode',
    'WorkState',
    'RateOverride',
    'PayRate',
    'OTDblPayRateType',
    'OTPayRate',
    'DblPayRate',
    'HolPayRateType',
    'HolPayRate',
    'BillRate',
    'OTDblBillRateType',
    'OTBillRate',
    'DblBillRate',
    'HolBillRateType',
    'HolBillRate',
    'UsrFldOverride',
    'UserField1',
    'UserField2',
    'UserField3',
    'Comments',
    'LateHoursFlag',
    'DedCycle',
    'PaySepChk',
    'BillSepInv',
    'Comment1',
    'Comment2',
    'Comment3',
    'Adjustment'
);

if ($format == 'txt' || $format == 'csv')
{
    if ($accountingExport == 'Exported')
    {
        if ($exported_chk == 'exported')
        {
            //echo '---'.$timesheet_sno;
            if (count($timesheet_sno) > 0)
            {
                $timesheet_sno = array_unique($timesheet_sno);
                $timesheet_parid = array_unique($timesheet_parid);
                $tsnos = implode(",", $timesheet_sno);
                $psnos = implode(",", $timesheet_parid);
                //echo $tsnos;
                //exit();
                mysql_query("update par_timesheet set exported_status='YES',exported_time=now(), exported_user= '" . $username . "' , not_exported_user = '0' where sno IN (" . $psnos . ") " . $pstatus, $db);
                mysql_query("update timesheet_hours set exported_status='YES',exported_time=now(), exported_user= '" . $username . "' , not_exported_user = '0' where sno IN (" . $tsnos . ") AND status!='Backup' " . $nstatus, $db);
            }

            if (count($expense_sno) > 0)
            {
                $expense_sno = array_unique($expense_sno);
                $expense_parid = array_unique($expense_parid);
                $esnos = implode(",", $expense_sno);
                $exp_psnos = implode(",", $expense_parid);
                $expense_upd_parid = array();
                mysql_query("UPDATE expense SET exported_status='YES',exported_time=now(), exported_user= '" . $username . "', not_exported_user = '0'  WHERE sno IN (" . $esnos . ") AND status!='Backup' ", $db);
                mysql_query("UPDATE par_expense SET exported_status='YES',exported_time=now(), exported_user= '" . $username . "', not_exported_user = '0'  WHERE sno IN (" . $exp_psnos . ")", $db);
            }
        } //echo $accountingExport.$format.'23';
        //exit();
        
    }

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

if (isset($defaction) && $defaction == 'print') {
  echo "<script type='text/javascript'>
    window.print();
    window.setInterval('window.close();', 10000);
  </script>";
}

?>
