<?php
/*
Created Date : March 23 2017.
Created By : Rajesh kumar V
Purpose : Created the Report for theraphy source customer - "Employee Credential Expiration Report "
*/
	ob_start();
	require_once('global_reports.inc');
	require_once('rlib.inc');
	require_once('functions.inc.php');
        $deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	if (empty($format)) {
	
		$format	= 'html';
	}
	
	function addslashesforstring($str)
	{
		$inputStrArr = explode(',',$str);
		$outputStrArr = Array();
		foreach($inputStrArr AS $key=>$val)
		{
			$val = "'".addslashes($val)."'";
			array_push($outputStrArr, $val);
		}
		$outputStr = implode(',', $outputStrArr);
		return $outputStr;
	}

	$rlib_filename		= 'customEmployeeCredential.xml';
	
	$frm_values		= explode('^', $customEmployeeCredential);
	
	$empStatus		= $frm_values[0];
	$liftOfDepartments	= explode(',',$frm_values[1]);
	$credentialTypes	= explode(',',$frm_values[2]);
	$credentialNames	= explode(',',$frm_values[3]);
	$credentialStatus	= $frm_values[4];
	$credentialValidFromTo	= explode("*", $frm_values[5]);
	
	$from_cred_valid_from	= $credentialValidFromTo[0];
	$from_cred_valid_to	= $credentialValidFromTo[1];
	
	$assignmentstatus	= $frm_values[6];
	$credentialAcquiredValidFromTo	= explode("*", $frm_values[7]);
	
	$from_cred_acq_valid_from	= $credentialAcquiredValidFromTo[0];
	$from_cred_acq_valid_to	= $credentialAcquiredValidFromTo[1];
	
	
	//Getting the filters selected
	
	$where_cond	= " ";

	//Employee status
	if($empStatus == 'N') {
		$where_cond .=" AND el.empterminated = 'N'";
	}
	else if($empStatus == 'Y')
	{
		$where_cond .=" AND el.empterminated = 'Y'";
	}
        
                    //Setting Only User Department
	if(in_array('ALL',$liftOfDepartments))
	{
                          $liftOfDepartments =   array_diff($liftOfDepartments, ['ALL'] );
	}

	//Department filter
	if(!in_array('ALL',$liftOfDepartments))
	{
		$where_cond .=" AND dt.deptname IN (".addslashesforstring(implode(',',$liftOfDepartments)).") ";
	}

	//Credential Type filter
	if(!in_array('ALL',$credentialTypes))
	{
		$where_cond .=" AND hcre.cre_type_id IN (".addslashesforstring(implode(',',$credentialTypes)).") ";
	}

	//Credential Name filter
	if(!in_array('ALL',$credentialNames))
	{
		$where_cond .=" AND hcre.cre_name_id IN (".addslashesforstring(implode(',',$credentialNames)).") ";
	}

	//Credential Status filter
	if($credentialStatus != 'ALL' && $credentialStatus != "")
	{
		if($credentialStatus != '' && $credentialStatus!='EXPIRED')
		{
			$where_cond_cs = " AND hcre.status='".addslashes($credentialStatus)."'";
			
			$currentdate = date("Y-m-d");
			$where_cond_cs .= " AND (hcre.valid_to='0000-00-00' OR hcre.valid_to='00/00/0000' OR ".tzRetQueryStringDTime('hcre.valid_to','YMDDate','-')." >= '".$currentdate."')";
		} 
		if($credentialStatus =='INACTIVE'){
			$currentdate = date("Y-m-d");
			$where_cond_cs = "AND hcre.status='".addslashes($credentialStatus)."'";
		}
		if($credentialStatus =='EXPIRED'){
			$currentdate = date("Y-m-d");
			$where_cond_cs = " AND hcre.status='ACTIVE' AND ".tzRetQueryStringDTime('hcre.valid_to','YMDDate','-')." < '".$currentdate."'";
		}
	}
	$where_cond .= $where_cond_cs;
	
	//Credentials From and To filters
	if($from_cred_valid_from != '' && $from_cred_valid_to != '')
	{
		$from_cred_valid_from = date("Y-m-d",strtotime($from_cred_valid_from));
		$from_cred_valid_to = date("Y-m-d",strtotime($from_cred_valid_to));
		
		$where_cond .= " AND (".tzRetQueryStringDTime('hcre.valid_to','YMDDate','-')." >= '".$from_cred_valid_from."' AND ".tzRetQueryStringDTime('hcre.valid_to','YMDDate','-')." <='".$from_cred_valid_to."')";
	}
	else if($from_cred_valid_from != '' && $from_cred_valid_to == '')
	{
		$from_cred_valid_from = date("Y-m-d",strtotime($from_cred_valid_from));
		
		$where_cond .= " AND ".tzRetQueryStringDTime('hcre.valid_to','YMDDate','-')." >= '".$from_cred_valid_from."'";
	}
	else if($from_cred_valid_from == '' && $from_cred_valid_to != '')
	{
		$from_cred_valid_to = date("Y-m-d",strtotime($from_cred_valid_to));
				
		$where_cond .= " AND ".tzRetQueryStringDTime('hcre.valid_to','YMDDate','-')." <= '".$from_cred_valid_to."'";	
	}

	//Credentials Acquired Date From and To filters
	if($from_cred_acq_valid_from != '' && $from_cred_acq_valid_to != '')
	{
		$from_cred_acq_valid_from = date("Y-m-d",strtotime($from_cred_acq_valid_from));
		$from_cred_acq_valid_to = date("Y-m-d",strtotime($from_cred_acq_valid_to));
		
		$where_cond .= " AND (".tzRetQueryStringDTime('hcre.acquired_date','YMDDate','-')." >= '".$from_cred_acq_valid_from."' AND ".tzRetQueryStringDTime('hcre.acquired_date','YMDDate','-')." <='".$from_cred_acq_valid_to."')";
	}
	else if($from_cred_acq_valid_from != '' && $from_cred_acq_valid_to == '')
	{
		$from_cred_acq_valid_from = date("Y-m-d",strtotime($from_cred_acq_valid_from));
		
		$where_cond .= " AND ".tzRetQueryStringDTime('hcre.acquired_date','YMDDate','-')." >= '".$from_cred_acq_valid_from."'";
	}
	else if($from_cred_acq_valid_from == '' && $from_cred_acq_valid_to != '')
	{
		$from_cred_acq_valid_to = date("Y-m-d",strtotime($from_cred_acq_valid_to));
				
		$where_cond .= " AND ".tzRetQueryStringDTime('hcre.acquired_date','YMDDate','-')." <= '".$from_cred_acq_valid_to."'";	
	}
	
	//Assignment status filters
	if($assignmentstatus != "")
	{
		if($assignmentstatus == 'ALL'){
			
			$where_cond_as = " AND hr.jtype!='' AND hr.jotype!='0' AND hr.ustatus IN ('active','closed','cancel')";
			
		}elseif($assignmentstatus == 'active'){
			
			$where_cond_as = " AND hr.jtype!='' AND hr.jotype!='0' AND hr.ustatus = 'active'";
			
		}elseif($assignmentstatus == 'closed'){
			
			$where_cond_as = " AND hr.jtype!='' AND hr.jotype!='0' AND hr.ustatus = 'closed'";		
		}else{
			
			$where_cond_as = " AND hr.jtype!='' AND hr.jotype!='0' AND hr.ustatus = 'cancel'";		
		}
		$where_cond .= $where_cond_as;
	}


	//Report ids
	$lastname[0]		= 'lastname';
	$firstname[0]		= 'firstname';
	$middlename[0]		= 'middlename';
	$employeeid[0]		= 'employeeid';
	$ssn[0]			= 'ssn';
	$credentialname[0]	= 'credentialname';
	$credentialstatus[0]	= 'credentialstatus';
	$expiredate[0]		= 'expiredate';
	$onassignment[0]	= 'onassignment';
	$employeestatus[0]	= 'employeestatus';
	$acquireddate[0]	= 'acquireddate';
	
	
	//Report heading Names
	$lastname[1]		= 'Last Name';
	$firstname[1]		= 'First Name';
	$middlename[1]		= 'Middle Name';
	$employeeid[1]		= 'Employee Id';
	$ssn[1]				= 'SSN';
	$credentialname[1]	= 'Credential Name';
	$credentialstatus[1]	= 'Credential Status';
	$expiredate[1]		= 'Expiration Date';
	$onassignment[1]	= 'On Assignment';
	$employeestatus[1]	= 'Employee Status';
	$acquireddate[1]	= 'Credential Acquired Date';
	
	//Report Heading Seperations
	$lastname[2]		= '----------------------';
	$firstname[2]		= '----------------------';
	$middlename[2]		= '----------------------';
	$employeeid[2]		= '----------------------';
	$ssn[2]			= '----------------------';
	$credentialname[2]	= '----------------------';
	$credentialstatus[2]	= '----------------------';
	$expiredate[2]		= '----------------------';
	$onassignment[2]	= '----------------------';
	$employeestatus[2]	= '----------------------';
	$acquireddate[2]	= '----------------------------';
	
	
	$sortarr 	= array('lastname',
				'firstname',
				'middlename',
				'employeeid',
				'ssn',
				'credentialname',
				'credentialstatus',
				'expiredate',
				'onassignment',
				'employeestatus',
				'acquireddate');
	
				
	$j=1;
	for($i = 0; $i<15; $i++)
	{  	
		$name 		= 'dynassignment'.$i;
		${$name}[] 	= 'dynassignment'.$i;
		${$name}[] 	= 'Assignment '.$j.' - Company';
		${$name}[] 	= '--------------------------';
	
		$sortarr[]	= 'dynassignment'.$i;
		$j++;	
	}
	
	$arr_count	= count($sortarr);
	$rep_company	= $companyname;
	$rep_header	= 'Employee Credential Expiration Report';
	$rep_date	= 'date';
	
	$k = 0;
	
	//Array for displaying heading for all the columns selected
	for ($q = 0; $q < $arr_count; $q++) 
	{
		
		$variable	= $$sortarr[$q];
		if (!empty($variable[0])) 
		{
			$data[0][$k]	= $variable[0];
			$headval[0][$k]	= $variable[1];
			$headval[1][$k]	= $variable[2];
	
			$k++;
		}
	}
	
	if ($k != 0) 
	{
		$data[0][$k]	= 'link';
		$k++;
		$data[0][$k]	= 'link_length';
	}

	$groupbyCond = " GROUP BY el.sno, hcre.id"; //Group by condition intially should have

	//$department_dynStr = " AND FIND_IN_SET('".$username."',dt.permission)>0 ";
	$department_dynStr = " AND dt.sno !='0' AND dt.sno IN ({$deptAccesSno}) ";
//	if($accountingpref!='NO' && chkUserPref($accountingpref,"11"))// New check implemented July 09, 2010 Piyush R chkUserPref
//	{
//		$department_dynStr = "";
//	}

	// Get the workers comp details 
	$final_query = 	"SELECT 
			hg.lname AS last_name,
			hg.fname AS first_name,
			hg.mname AS middle_name,
			el.sno AS emp_id,
			REPLACE(hp.ssn, '-', '') AS emp_ssn,
			mn.credential_name AS credential_name,
			DATE_FORMAT(hcre.valid_from, '%m/%d/%Y') AS 'crevalid_from',
			DATE_FORMAT(hcre.valid_to, '%m/%d/%Y') AS 'crevalid_to',			
			IF(el.empterminated='N','Active','Terminated') AS emp_status,
			hcre.status,
			DATE_FORMAT(hcre.acquired_date, '%m/%d/%Y') AS acquired_date
		FROM
			hrcon_general hg,
			hrcon_w4 hw,
			hrcon_credentials hcre 
		LEFT JOIN manage_credentials_type mt ON (hcre.cre_type_id = mt.id)
		LEFT JOIN manage_credentials_name mn ON (hcre.cre_name_id = mn.id), emp_list el
		LEFT JOIN hrcon_jobs hr ON el.username=hr.username
		LEFT JOIN hrcon_compen hc ON el.username = hc.username  
		LEFT JOIN hrcon_personal hp ON  el.username =hp.username and hp.ustatus = 'active'
		LEFT JOIN department dt ON hc.dept = dt.sno 
		LEFT JOIN contact_manage co ON hc.location = co.serial_no 
		WHERE el.username = hg.username
		AND el.username = hw.username	
		AND el.username = hc.username
		AND el.username = hcre.app_username 
		AND hcre.ustatus = 'active'
		AND hw.ustatus = 'active'
		AND hg.ustatus = 'active'
		AND hc.ustatus = 'active'
		AND el.lstatus NOT IN ('DA','INACTIVE')
		".$where_cond."
		$department_dynStr";
		
	$final_query .= $groupbyCond;


$final_res = mysql_query($final_query, $rptdb);

//Form the report data
$i	= 1;
while($final_arr = mysql_fetch_array($final_res))
{
	$ii = 0;

	//getting the employee active assignment details
	$empsno 		= $final_arr['emp_id'];
	$empAssignmentsArr 	= getEmployeeAssignments($empsno, $where_cond_as);
	
	if($assignmentstatus == 'ALL')
	{
		//getting the active assignment count when all is selected for 'On Assignment' Column
		$activeAssignmentCon = " AND hr.jtype!='' AND hr.jotype!='0' AND hr.ustatus = 'active'";
		$empAciveAssignmentsArr 	= getEmployeeAssignments($empsno, $activeAssignmentCon);
		if(count($empAciveAssignmentsArr)>0)
		{
			$on_assignment = 'Yes';
		}
		else
		{
			$on_assignment = 'No';
		}
	}
	else
	if($assignmentstatus == 'active' && count($empAssignmentsArr)>0)
	{
		$on_assignment = 'Yes';
	}
	else
	{
		$on_assignment = 'No';
	}
	
	//getting the status of credential
	if(empty($final_arr['crevalid_to']) || $final_arr['crevalid_to'] == '0000-00-00' || $final_arr['crevalid_to'] == '00/00/0000')
	{
		$cre_status = $final_arr["status"];
	}
	else
	{
		$start_ts 	= strtotime(date('Y-m-d', strtotime($final_arr['crevalid_to'])));
		$end_ts 	= strtotime(date('Y-m-d'));
		$diff 		= $end_ts - $start_ts;
		$getdayscount 	= round($diff / 86400);
			
		$cre_status = ($getdayscount > 0) ? 'EXPIRED' : $final_arr["status"];
	}
	if($final_arr["status"] == 'INACTIVE')
	{
		$cre_status ='INACTIVE';
	}

	if($final_arr['crevalid_to']=='00/00/0000'){
		$final_arr['crevalid_to']='';
	}
	if($final_arr['acquired_date']=='00/00/0000'){
		$final_arr['acquired_date']='';
	}

	//Array for all column's data			
	$values_array = array(
				'lastname'	=> $final_arr['last_name'],
				'firstname'	=> $final_arr['first_name'],
				'middlename'	=> $final_arr['middle_name'],
				'employeeid'	=> $final_arr['emp_id'],
				'ssn'		=> $ac_aced->decrypt($final_arr['emp_ssn']),
				'credentialname'=> $final_arr['credential_name'],
				'credentialstatus' => $cre_status,
				'expiredate'	=> $final_arr['crevalid_to'],
				'onassignment'	=> $on_assignment,
				'employeestatus'=> $final_arr['emp_status'],
				'acquireddate' => $final_arr['acquired_date']
			);
	
	//loop for binding the employee assignment information
	for($l = 0; $l< 15; $l++)
	{
		$empAssignmentVal = ((isset($empAssignmentsArr[$l]) && !empty($empAssignmentsArr[$l]))? $empAssignmentsArr[$l] : '');
		$values_array['dynassignment'.$l] = stripslashes($empAssignmentVal);
	}
	//End
	
	$values_array_pdf[] = $values_array;
	
	for ($q = 0; $q <= $arr_count; $q++) 
	{
		$variable	= $$sortarr[$q];

		if (!empty($variable[0])) 
		{
			$data[$i][$ii]	= $values_array[$sortarr[$q]];
			$sslength_array[$sortarr[$q]]	= trim((strlen($values_array[$sortarr[$q]]) <= strlen($variable[2])) ? strlen($values_array[$sortarr[$q]]) : (strlen($variable[2])+3));
			$ii++;
		}
	}

	if ($arr_count) 
	{
		$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
	}

	$data[$i][$ii]	= '';
	$ii++;

	if (!empty($departments[0])) 
	{
		$data[$i][$ii]	= $slength;
		$ii++;
	}	
	
	$i++;
}

if (empty($data)) {

	$data		= array();
	$headval	= array();

	$data[0][0]	= '';
	$headval[0][0]	= '';
}

$date	= date('YmdHis', time());
$file	= 'Employee_Credential_Expiration_Report'. $date .'.'. $format;
$mime	= 'application/'. $format;

if ($format == 'csv' || $format == 'txt')
{
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: public');
	header("Content-Type: $mime; name=$file");
	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=$file");
	header('Content-Transfer-Encoding: binary');

	$cur_date	= getCurrentDateTime();
	if($format == 'csv'){
	print '"'.$rep_company."\"\n";
	print '"'.$rep_header."\"\n";
	print '"'.$cur_date."\"\n\n";
	}
	if($format == 'txt'){
	print '"'.$rep_company."\"\n";
	echo "\r\n";
	print '"'.$rep_header."\"\n";
	echo "\r\n";
	print '"'.$cur_date."\"\n\n";
	echo "\r\n";
	echo "\r\n";
	}

	$header_count	= count($headval[0]);

	for ($t = 0; $t <= $header_count; $t++)
	{
		$data[0][$t]	= trim($headval[0][$t]);
	}

	foreach ($data as $row)
	{
		$row	= array_slice($row, 0, count($row)-2);
		print '"'. stripslashes(implode('","',$row)) ."\"\n";
		if($format == 'txt'){
      	echo "\r\n";
    	}
	}
}
else if($format == 'pdf') 
{
	$input_array		= $values_array_pdf;
	$output_file_name	= $file;
	$rep_header		= $rep_header;
	$fromDate		= date("m/d/Y",strtotime($ts_from_dt));
	$toDate			= date("m/d/Y",strtotime($ts_to_dt));
	$totalPDFFieldsArr	= getTotalPDFFieldNames();
	convert_to_pdf($input_array, $output_file_name, $get_tsdate_exp, $get_tsdate_exp, $rep_header, $totalPDFFieldsArr);	
}
else
{
	require_once('rlibdata.php');
}

if (isset($default_action) && $default_action == 'print')
{
	echo "<script type='text/javascript'>
		window.print();
		window.setInterval('window.close();', 10000);
	</script>";
}
?>