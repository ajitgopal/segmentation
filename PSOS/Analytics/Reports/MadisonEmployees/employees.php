<?php
	ob_start();
	$rlib_filename="hrmemployees.xml";

	require("global_reports.inc");
	require("rlib.inc");
	require("ftpFunctions.php");	
	$reportfrm=$reportfrm;                      
        $deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	require_once("functions.inc.php");
	require_once("../tableGeneratorClass.php");//is Required to display Reports Table on to the screen
	$currentURL = basename($_SERVER['PHP_SELF']);

	if($format=="")
		$format="html";

	$module="MadisonEmployeesRep";

	//Take availabkle columns...
	$totalFieldsArr  = array(
								"fein" => "FEIN",
								"empID" => "EmployeeId",
								"empSSN" => "SSN",
								"empFname" => "FirstName",
								"empInitial" => "Initial",
								"empLname" => "LastName",
								"empHomeAddr1" => "HomeAddress1",
								"empHomeAddr2" => "HomeAddress2",
								"empHomeCity" => "HomeCity",
								"empHomeState" => "HomeState",
								"empHomeZip" => "HomeZip",
								"empPhoneNum" => "PhoneNumber",
								"empMaritalStatus" => "FedMaritalStatus",
								"empGender" => "Gender",
								"empDOB" => "DOB",
								"empHireDate" => "HireDate",
								"empTaxState" => "TaxState",
								"empFedAllowance" => "FedAllowances",
								"empStateAllowance" => "StateAllowances",
								"empStateAddlAmount" => "StateAddlAmount",
								"empFedAddlAmount" => "FedAddlAmount",
								"empBranchName" => "BranchName",
								"empClass" => "Class",
								"firstAccountABA" => "PrimaryABA",
								"firstAccountNum" => "PrimaryAcct",
								"firstAccountType" => "PrimaryType",
								"firstAccountName" => "PrimaryBankName",
								"secondAccountABA" => "SecondABA",
								"secondAccountNum" => "SecondAcct",
								"secondAccountType" => "SecondType",
								"secondAccountName" => "SecondBankName",
								"secondAccountAmount" => "SecondAmt",
								"thirdAccountABA" => "ThirdABA",
								"thirdAccountNum" => "ThirdAcct",
								"thirdAccountType" => "ThirdType",
								"thirdAccountName" => "ThirdBankName",
								"thirdAccountAmount" => "ThirdAmt",
								"assignmentCustomer" => "CustomerName",
								"assignmentOrderID" => "OrderID",
								"assignmentStartDate" => "AssignmentStartDate",
								"jobTitle" => "JobTitle",
								"workCompCode" => "WorkCompCode",
								"workState" => "WorkState",
								"payrate" => "PayRate",
								"otpayrate" => "OTPayRate",
								"billrate" => "BillRate",
								"otbillrate" => "OTBillRate",
								"salary" => "Salary",
								"empmaritalstatus" => "FedW4_MaritalStatus",
								"empistaxexempt" => "FedW4_IsTaxExempt",
								"empadditionaljobs" => "FedW4_AdditionalJob",
								"empdependentsamt" => "FedW4_FedDependents",
								"empotherincome" => "FedW4_OtherIncome",
								"emptotaldeductions" => "FedW4_Deductions",
								"empaddtionalwithholding" => "FedW4_AdditionalWithholding",
								"empstatemaritalstatus" => "StateW4_MaritalStatus"
							);
	$totalFieldsArrCount = count($totalFieldsArr);
	$totalFieldsKeys = array_keys($totalFieldsArr);
	$totalFieldsNames = array_values($totalFieldsArr);

	//Code for  the report opened from MyReports  
	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$db);
		$vrowdata=mysql_fetch_row($rresult);
		$vrow=explode("|username->",$vrowdata[0]);
		$Analytics_MadisonEmployeesRep=$vrow[0];
		$cusername=$vrow[1];
		if(strpos($Analytics_MadisonEmployeesRep,"|username->")!=0)
        $Analytics_MadisonEmployeesRep=$vrow[0];

        session_update("cusername");
	session_update("Analytics_MadisonEmployeesRep");
		
		$rdata=explode("|",$Analytics_MadisonEmployeesRep);
	}//MyReport code completed
	else 
	{
		$rdata=explode("|",$Analytics_MadisonEmployeesRep);
		$tab=$rdata[0];
	}

	$sortarr = array("fein","empID","empSSN","empFname","empInitial","empLname","empHomeAddr1","empHomeAddr2","empHomeCity","empHomeState","empHomeZip","empPhoneNum","empMaritalStatus","empGender","empDOB","empHireDate","empTaxState","empFedAllowance","empStateAllowance","empStateAddlAmount","empFedAddlAmount","empBranchName","empClass","firstAccountABA","firstAccountNum","firstAccountType","firstAccountName","secondAccountABA","secondAccountNum","secondAccountType","secondAccountName","secondAccountAmount","thirdAccountABA","thirdAccountNum","thirdAccountType","thirdAccountName","thirdAccountAmount","assignmentCustomer","assignmentOrderID","assignmentStartDate","jobTitle","workCompCode","workState","payrate","otpayrate","billrate","otbillrate","salary","empmaritalstatus","empistaxexempt","empadditionaljobs","empdependentsamt","empotherincome","emptotaldeductions","empaddtionalwithholding","empstatemaritalstatus");
	$sortingorder_array = array("empID","empFname");
	$rep_sortorder="ASC";

	//If the Report  comes from  customize page ,fetching the values and kepping it in array.    
	if($tab=="addr" ||  ($view=="myreport" && $vrow[0]!=""))
	{
		session_update("Analytics_MadisonEmployeesRep");

		$rdata=explode("|",$Analytics_MadisonEmployeesRep);

		//column names and their corresponding selected values from the filters
		$filternames_array = explode('^',$rdata[3]);
		$filtervalues_array = explode('^',formateSlashes($rdata[4]));
	}

	$sortarrCount = count($sortarr);
	for($c=0;$c<$sortarrCount;$c++)
	{
		$tempVar = $sortarr[$c];
		$$tempVar = array();
	}
	
	for($tfCnt=0;$tfCnt < $sortarrCount;$tfCnt++)
	{
		$fieldTemp = $$sortarr[$tfCnt];
		$fieldTemp[0] = $sortarr[$tfCnt];
		$fieldTemp[1] = $totalFieldsArr[$sortarr[$tfCnt]];
		
		$headLine= str_repeat("-",strlen($totalFieldsArr[$sortarr[$tfCnt]]));
		$fieldTemp[2] = $headLine;
		$$sortarr[$tfCnt] =$fieldTemp;
	}

	$k=0;
	//Array  for displaying  heading for all the columns selected
	for($q=0 ; $q< $sortarrCount ; $q++)
	{ 
		$variable = $$sortarr[$q] ;
		if($variable[0]!="")
		{ 
			$data[0][$k] = $variable[0];
			$headval[0][$k] = $variable[1];
			$headval[1][$k] = $variable[2];
			$k++;
		}
	}
	if($k!=0)
	{
		$data[0][$k]="link";
		$k++;
		$data[0][$k]="link_length";
	}	
 	$i=1;
	//Start of Preparing Soring Order Data for each column selected under sort tab


	//Pagination Details
	$limitval = "200";//This is where we have to set the Page Limit to display number of records in that page.
	$cur_page = floor($start/$limitval)+1;
	if($start == "")
		$start = 0;
	$recordsCount = 0;
	$groupSnos= "";

	$mque="select madisondate from company_info";
	$mres=mysql_query($mque,$db);
	$mrow=mysql_fetch_row($mres);
	$madisondate = $mrow[0];

        //$department_dynStr = " AND FIND_IN_SET('".$username."',department.permission)>0 ";
        $department_dynStr = " AND department.sno !='0' AND department.sno IN ({$deptAccesSno}) ";
	//if($accountingpref!='NO' && strpos($accountingpref,'11')>0)
//	if($accountingpref!='NO' && chkUserPref($accountingpref,"11"))// New check implemented July 09, 2010 Piyush R chkUserPref
//		$department_dynStr = "";

	//get deposit type direct deposit sno from manage table...
	$delivermethodSno = getManageSno("Direct Deposit","deliverymethod");

	//Starts of checking the Filter  conditions in Main query as per the selected values from the filters for each column
	$filterCondString = "";	 
	$filtername_count=count($filternames_array);
	for($f=0;$f<$filtername_count;$f++)
	{
		if($filternames_array[$f] == "assignmentStartDate") // Assignment Start Date
		{
			$tabFieldname=getserTabName($filternames_array[$f]);
			$fromDate=$filtervalues_array[$f];

			if($fromDate=="")
				$fromDate = $madisondate;

			$fdate = ($fromDate) ? date("Y-m-d",strtotime($fromDate)) : "";

			$strDate = tzRetQueryStringSTRTODate("$tabFieldname","%m-%d-%Y","YMDDate","-");
			$endDate = tzRetQueryStringSTRTODate("hj.e_date","%m-%d-%Y","YMDDate","-");

			$filterCondString1=" AND $strDate >= '".$fdate."' ";
			$filterCondString2=" AND hj.e_date NOT IN ('','0-0-0') AND hj.e_date IS NOT NULL AND $endDate >= '".$fdate."' ";
		}
		else if(($filternames_array[$f] == "empClass" || $filternames_array[$f] == "empTaxState") && $filtervalues_array[$f] != "")
		{
			if($filtervalues_array[$f] != 'ALL')
			{
				$tabFieldname=getserTabName($filternames_array[$f]);
				$filterCondString .=" AND $tabFieldname = '".$filtervalues_array[$f]."' ";
			}
		}
		else if(($filternames_array[$f] != "") && trim($filtervalues_array[$f]) != "")
		{
			$tabFieldname=getserTabName($filternames_array[$f]);
			if($tabFieldname != ".")//this condition has to be removed later when branchname filed is there
				$filterCondString.=" AND ".$tabFieldname." LIKE '%".$filtervalues_array[$f]."%' ";
		}
	}

	if($filtername_count==0)
	{
		$fromDate = $madisondate;
		$fdate = ($fromDate) ? date("Y-m-d",strtotime($fromDate)) : "";

		$strDate = tzRetQueryStringSTRTODate("hj.s_date","%m-%d-%Y","YMDDate","-");
		$endDate = tzRetQueryStringSTRTODate("hj.e_date","%m-%d-%Y","YMDDate","-");

		$filterCondString1=" AND $strDate >= '".$fdate."' ";
		$filterCondString2=" AND hj.e_date NOT IN ('','0-0-0') AND hj.e_date IS NOT NULL AND $endDate >= '".$fdate."' ";
	}

	//End  of checking the Filter  conditions
	
	$sqlQryCount = "SELECT COUNT(mp.EmployeeId)
	FROM 
	madison_paydata mp
	LEFT JOIN hrcon_compen hc ON mp.paydata_emp_username=hc.username 
	LEFT JOIN hrcon_w4 hw4 ON (mp.paydata_emp_username=hw4.username AND hw4.ustatus='active')
	LEFT JOIN manage m1 ON (m1.sno=hc.emptype AND m1.type='jotype') 
	LEFT JOIN workerscomp wc ON (wc.workerscompid=hc.wcomp_code AND wc.status = 'active') 
	LEFT JOIN contact_manage ON (contact_manage.serial_no=hc.location) 
	LEFT JOIN department ON (hc.dept=department.sno)
	LEFT JOIN hrcon_jobs hj ON mp.paydata_emp_username=hj.username 
	LEFT JOIN staffacc_cinfo ON (hj.client=staffacc_cinfo.sno) 
	LEFT JOIN manage jtype on(hj.jotype=jtype.sno and jtype.type='jotype') 
	LEFT JOIN workerscomp wj ON (wj.workerscompid=hj.wcomp_code AND wj.status = 'active') 
	LEFT JOIN staffacc_cinfo scinfo ON (hj.endclient=scinfo.sno) 
	LEFT JOIN timesheet_hours ts ON (hj.pusername=ts.assid AND hj.username=ts.username) 
	WHERE 
	mp.paydata_status = 'N' 
	AND mp.paydata_emp_status NOT IN ('DA','INACTIVE') 
	AND ts.status IN ('Approved','Billed') 
	AND ((hj.ustatus='active') OR (hj.ustatus='closed' ".$filterCondString1.") OR (hj.ustatus='closed' ".$filterCondString2.")) 
	AND hj.jtype!='' AND hj.jotype!=0 
	AND hj.madison_order_id!='' 
	AND hc.ustatus = 'active' 
	AND mp.paydata_emp_terminated='N' $department_dynStr $filterCondString GROUP BY ts.assid";
	
	$resQryCount=mysql_query($sqlQryCount,$db);
	$infoQryCount = mysql_fetch_row($resQryCount);
	$recCount = mysql_num_rows($resQryCount);

	$limitString = "";	
	if($recCount > $limitval)
	{
		if($start >= $recCount)
			$start = abs(($recCount - $limitval));
		$limitString = ($format=='html')?" LIMIT ".$start.",".$limitval:'';
	}
	//ends here

	//Prasadd---NOTE: Dont change fetching order. If so, index should be changed in 'getOrderByIndex'  function...
	//Query to fetch all the Employees details
	
	$qryEmp="SELECT mp.EmployeeId, mp.paydata_ssn, mp.paydata_firstname, mp.paydata_middlename, mp.paydata_lastname, 
	mp.HomeAddress1, mp.HomeAddress2, mp.HomeCity, mp.HomeState, mp.HomeZip, mp.PhoneNumber, mp.MaritalStatus, mp.Gender, 
	mp.DOB, DATE_FORMAT(mp.HireDate,'%m/%d/%Y'), 
	mp.TaxState, mp.FedAllowances, mp.StateAllowances, mp.StateAddlAmount, mp.FedAddlAmount, mp.paydata_branchname, IF(mp.paydata_emptype IN ('Direct','Internal Direct'),'Staff','Temp'), mp.PrimaryBankABA, mp.PrimaryBankAcct, mp.PrimaryBankType, mp.PrimaryBankName, mp.PrimaryBankAmt, mp.SecondBankABA, mp.SecondBankAcct, mp.SecondBankType, mp.SecondBankName, mp.SecondBankAmt, mp.ThirdBankABA, mp.ThirdBankAcct, mp.ThirdBankType, mp.ThirdBankName, mp.ThirdBankAmt,	staffacc_cinfo.cname,hj.madison_order_id,DATE_FORMAT(STR_TO_DATE(if(hj.s_date='0-0-0','00-00-0000',hj.s_date),'%m-%d-%Y'),'%m/%d/%Y'),hj.project,IF(hc.assign_wcompcode='Y',CONCAT_WS('',wj.state,wj.code),CONCAT_WS('',wc.state,wc.code)),SUBSTRING(sloc.state,1,2), IF(hc.pay_assign='Y',IF(jtype.name in ('Internal Direct','Direct'), '', hj.pamount),IF(m1.name not in('Internal Direct','Direct'),hc.salary,'')), IF(hc.assign_overtime='Y',hj.otprate_amt,hc.over_time), hj.bamount, hj.otbrate_amt, IF(hc.pay_assign='Y',if(jtype.name in ('Internal Direct','Direct'), hj.rate,''),IF(m1.name in('Internal Direct','Direct'),hc.salary,'')), hj.pusername,contact_manage.feid, 'federal_exempt','state_exempt',
	DATE_FORMAT(mp.DOB,'%m-%d-%Y'),
	DATE_FORMAT(mp.HireDate,'%m-%d-%Y'),
	STR_TO_DATE(if(hj.s_date='0-0-0','00-00-0000',hj.s_date),'%m-%d-%Y'),
	'delivery_method', mp.paydata_sno,
	hw4.fstatus AS empmaritalstatus, hw4.federal_exempt AS empistaxexempt, hw4.multijobs_spouseworks AS empadditionaljobs, hw4.claim_dependents_total AS empdependentsamt, hw4.other_income_amt AS empotherincome, hw4.deduction_amt AS emptotaldeductions, hw4.aftaw AS empaddtionalwithholding, hw4.fsstatus AS empstatemaritalstatus, hw4.state_withholding
	FROM 
	madison_paydata mp
	LEFT JOIN hrcon_compen hc ON mp.paydata_emp_username=hc.username 
	LEFT JOIN hrcon_w4 hw4 ON (mp.paydata_emp_username=hw4.username AND hw4.ustatus='active')
	LEFT JOIN manage m1 ON (m1.sno=hc.emptype AND m1.type='jotype') 
	LEFT JOIN workerscomp wc ON (wc.workerscompid=hc.wcomp_code AND wc.status = 'active') 
	LEFT JOIN contact_manage ON (contact_manage.serial_no=hc.location) 
	LEFT JOIN department ON (hc.dept=department.sno)
	LEFT JOIN hrcon_jobs hj ON mp.paydata_emp_username=hj.username 
	LEFT JOIN staffacc_cinfo ON (hj.client=staffacc_cinfo.sno) 
	LEFT JOIN manage jtype on(hj.jotype=jtype.sno and jtype.type='jotype') 
	LEFT JOIN workerscomp wj ON (wj.workerscompid=hj.wcomp_code AND wj.status = 'active') 
	LEFT JOIN staffacc_location AS sloc ON (hj.endclient=sloc.sno) 
	LEFT JOIN timesheet_hours ts ON (hj.pusername=ts.assid AND hj.username=ts.username) 
	WHERE 
	mp.paydata_status = 'N' 
	AND mp.paydata_emp_status NOT IN ('DA','INACTIVE') 
	AND ts.status IN ('Approved','Billed') 
	AND ((hj.ustatus='active') OR (hj.ustatus='closed' ".$filterCondString1.") OR (hj.ustatus='closed' ".$filterCondString2.")) 
	AND hj.jtype!='' AND hj.jotype!=0 
	AND hj.madison_order_id!='' 
	AND hc.ustatus = 'active' 
	AND mp.paydata_emp_terminated='N' $department_dynStr ";

	$qryEmp.=" $filterCondString GROUP BY ts.assid $limitString";
	$resEmp=mysql_query($qryEmp,$db);

	//Pagination Details
	$curPageCount = mysql_num_rows($resEmp);
	$cur_page = calakken_cpageCount($start,$curPageCount);
	//Ends here
	
	//Code  for fetching the data for each column selected and put it in an array-$data
	$bankAccType1="";
	$bankAccType2="";
	$commaSepVal="";
	
	while($arr=mysql_fetch_row($resEmp))
	{
	 	$ii = 0;
		
		if(strpos($groupSnos,",")>0)
		{
			$expGroupSnos = explode(",",$groupSnos);
			if(!in_array($arr[56],$expGroupSnos))
				$groupSnos .= $commaSepVal.$arr[56];
		}
		else
		{
			$groupSnos .= $commaSepVal.$arr[56];
		}

		if(in_array("empDOB",$sortarr))
		{
			if($arr[13]!="00/00/0000" && $arr[13]!="0000-00-00" && $arr[13]!="000000")
				$displaybirthDate = $arr[13];
			else
				$displaybirthDate="";
		}

		if($arr[14]!="00/00/0000" && $arr[14]!="0000-00-00")
			$displayhireDate = $arr[14];
		else
			$displayhireDate="";

		if($arr[39]!="00/00/0000")
			$displayStartDate = $arr[39];
		else
			$displayStartDate="";
		
		//Data for Total Federal Tax Allowances
		/* if($arr[50]=='Y')
			$totFedtaxallow=99;
		else */
			$totFedtaxallow = $arr[16];
		
		//Data for Total State Tax Allowances
	/* 	if($arr[51]=='Y')
			$totStatetaxallow=99;
		else */
			$totStatetaxallow = $arr[17];
	    
		//Data for maritalstatus
/* 		if($arr[11]=="single")
			$mStatus="S";
		else if($arr[11]=="married")	
			 $mStatus="M"; 
		else
			$mStatus="";  */
		
		$mStatus=$arr[11];
		
		//PrimayAcctType
/* 		if($arr[24]=="CHECKING")
			$bankAccType1="Chk";
		else if($arr[24]=="SAVINGS")
			$bankAccType1="Sav";
		else
			$bankAccType1=""; */
		
		$bankAccType1=$arr[24];			
		
		//Acc2AcctType
/* 		if($arr[29]=="CHECKING")
			$bankAccType2="Chk";
		else if($arr[29]=="SAVINGS")
			$bankAccType2="Sav";
		else
			$bankAccType2=""; */
			
		$bankAccType2=$arr[29];
		
		//Acc3AcctType
	/* 	if($arr[34]=="CHECKING")
			$bankAccType3="Chk";
		else if($arr[34]=="SAVINGS")
			$bankAccType3="Sav";
		else
			$bankAccType3=""; */
			
		$bankAccType3=$arr[34];
		
		$empCalssType=$arr[21];
		$payRate = $arr[43];
		$overTimePayRate = $arr[44];
		$billRate = $arr[45];
		$overTimeBillRate = $arr[46];
		$displaysalary =$arr[47]; //hc.salary
			
		//Array for all column's data
		$values_array = array(
								"fein" => substr(trim($arr[49]),0,10),
								"empID" => substr(trim($arr[0]),0,10),
								"empSSN" => $ac_aced->decrypt($arr[1]),
								"empFname" => substr(trim($arr[2]),0,50),
								"empInitial" => substr(trim($arr[3]),0,10),
								"empLname" => substr(trim($arr[4]),0,50),
								"empHomeAddr1" => substr(trim($arr[5]),0,100),
								"empHomeAddr2" => $arr[6],
								"empHomeCity" => substr(trim($arr[7]),0,50),
								"empHomeState" => substr(trim($arr[8]),0,2),
								"empHomeZip" => substr(trim($arr[9]),0,5),
								"empPhoneNum" => substr(trim($arr[10]),0,25),
								"empMaritalStatus" => substr(trim($mStatus),0,1),
								"empGender" => substr(trim($arr[12]),0,1),
								"empDOB" => get_standard_dateFormat($ac_aced->decrypt($displaybirthDate), 'm-d-Y','m/d/Y'),
								"empHireDate" => $displayhireDate,
								"empTaxState" => substr(trim($arr[15]),0,2),
								"empFedAllowance" => $totFedtaxallow,
								"empStateAllowance" => $totStatetaxallow,
								"empStateAddlAmount" => ($arr[18]!=0.00)?$arr[18]:'',
								"empFedAddlAmount" => ($arr[19]!=0.00)?$arr[19]:'',
								"empBranchName" => substr(trim($arr[20]),0,25),
								"empClass" => substr(trim($empCalssType),0,10),
								"firstAccountABA" => $ac_aced->decrypt($arr[22]),
								"firstAccountNum" => $ac_aced->decrypt($arr[23]),
								"firstAccountType" => substr(trim($bankAccType1),0,3),
								"firstAccountName" => substr(trim($arr[25]),0,25),
								"secondAccountABA" => $ac_aced->decrypt($arr[27]),
								"secondAccountNum" => $ac_aced->decrypt($arr[28]),
								"secondAccountType" => substr(trim($bankAccType2),0,3),
								"secondAccountName" => substr(trim($arr[30]),0,25),
								"secondAccountAmount" => ($arr[31]!=0.00)?$arr[31]:'',
								"thirdAccountABA" => $ac_aced->decrypt($arr[32]),
								"thirdAccountNum" => $ac_aced->decrypt($arr[33]),
								"thirdAccountType" => substr(trim($bankAccType3),0,3),
								"thirdAccountName" => substr(trim($arr[35]),0,25),
								"thirdAccountAmount" => ($arr[36]!=0.00)?$arr[36]:'',
								"assignmentCustomer" => substr(trim($arr[37]),0,50),
								"assignmentOrderID" => substr(trim($arr[38]),0,15),
								"assignmentStartDate" => $displayStartDate,
								"jobTitle" => substr(trim($arr[40]),0,50),
								"workCompCode" => substr(trim($arr[41]),0,6),
								"workState" => substr(trim($arr[42]),0,2),
								"payrate" => ($payRate!=0.00)?$payRate:'',
								"otpayrate" => ($overTimePayRate!=0.00)?$overTimePayRate:'',
								"billrate" => ($billRate!=0.00)?$billRate:'',
								"otbillrate" => ($overTimeBillRate!=0.00)?$overTimeBillRate:'',
								"salary" => ($displaysalary!=0.00)?$displaysalary:'',
								"empmaritalstatus" => getMadisonFilingStatusCode($arr[57]),
								"empistaxexempt" => ($arr[58]=='Y') ? 'Y' : 'N',
								"empadditionaljobs" => ($arr[59]=='Y') ? 'Y' : 'N',
								"empdependentsamt" => ($arr[60]!=0.00) ? '$'.$arr[60] : '',
								"empotherincome" => ($arr[61]!=0.00) ? '$'.$arr[61] : '',
								"emptotaldeductions" => ($arr[62]!=0.00) ? '$'.$arr[62] : '',
								"empaddtionalwithholding" => ($arr[63]!=0.00) ? '$'.$arr[63] : '',
								"empstatemaritalstatus" => getMadisonStateFilingStatusCode(trim($arr[65]),trim($arr[64]))
							);
								
		//Preparing the actual data
		for($q=0;$q<$sortarrCount;$q++)
		{
			$variable = $$sortarr[$q];
			if($variable[0]!="")
			{		
				$data[$i][$ii] = html_tls_specialchars($values_array[$sortarr[$q]],ENT_QUOTES);
				$sslength_array[$sortarr[$q] ] = trim((strlen($values_array[$sortarr[$q] ]) <=strlen($variable[2])) ? strlen($values_array[$sortarr[$q] ]) : (strlen($variable[2])+3));
				$ii++;
			}
		}

		//Condition for each column's length
		if($sortarrCount)
			$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;

		//Link that will redirect to Employee's summary screen
		$data[$i][$ii]="javascript:showEmp('$arr[0]')";
		$ii++;

		$data[$i][$ii]=$slength;  //if column selected,corresponding length will be assigned to $data
		$ii++;

		$i++;

		$commaSepVal = ",";
	}//end of while

	$data=cleanArray($data);
	if($data=="")
	{
		$data=array();
		$data[0][0]="";
		$headval=array();
		$headval[0][0]="";
	}
	$rep_length=$i-1;
	$filename = $companyuser."_"."Employees_".date("mdy")."_".date("hms");
	
	//If the format is of type html then dispaly the data using html tables.
	if($format=="html")
	{
		echo '<link type="text/css" rel="stylesheet" href="/BSOS/Analytics/Reports/analytics.css">';
		echo generateReportTable($data,$cur_page,$start,$limitval,$recCount,$currentURL,$_SERVER['QUERY_STRING'],'Employee Report');//Parameters are Generated Data Array,The Current Page Number,The Starting Record Number Value,The Number of Records per Page,The Total Records,The Current Page URL, and the Query String,report display name.
	}
	else if($format == 'txt' || $format == 'csv')
	{
		if($export_type == 'local')
		{
			$fileName = $filename.".".$format;
			$mime = 'application/'.$format;		
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Type: $mime; name=$fileName");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$fileName");
			header("Content-Transfer-Encoding: binary");
			$dataHeaderCount = count($data[0])-2;
			for($t=0;$t<$dataHeaderCount; $t++)
			{
				$data[0][$t] = getDisplayName($data[0][$t]);
			}
			foreach($data as $row) 
			{
				$row = array_slice($row,0,count($row)-2);
				print '"' . stripslashes(implode('","',$row)) . "\"\n";
			}
		}
		else if($export_type == 'ftp')
		{
			$fileName = $filename.".".$format;
			$dataRecordCount = count($data);
			$dataHeaderCount = count($data[0])-2;
			for($t=0;$t<$dataHeaderCount; $t++)
			{
				$data[0][$t] = getDisplayName($data[0][$t]);
			}
			$csv_Data="";
			foreach($data as $row) 
			{
				$row = array_slice($row,0,count($row)-2);
				$csv_Data.= '"' . stripslashes(implode('","',$row)) . "\"\n";
			}
	
			$file_Name = $fileName;
			$fileName = $WDOCUMENT_ROOT."/".$fileName;
			
			$handle = fopen($fileName, 'w');
			fwrite($handle, $csv_Data);
			
			if($dataRecordCount > 1)
			{
				copyCSVToFTPIN($file_Name);//to place in FTP In folder
			}
			
			//update to processed state...
			$updateSql = "UPDATE madison_paydata mpd SET mpd.paydata_status='Y', mpd.paydata_processed_by='".$username."', mpd.paydata_processed_date=NOW() WHERE mpd.paydata_sno IN ($groupSnos) AND mpd.paydata_emp_status NOT IN ('DA','INACTIVE')";
			mysql_query($updateSql,$db);
		}
		else if($export_type == 'both')
		{
			$fileName = $filename.".".$format;
			$mime = 'application/'.$format;		
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Type: $mime; name=$fileName");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=$fileName");
			header("Content-Transfer-Encoding: binary");
			$dataRecordCount = count($data);
			$dataHeaderCount = count($data[0])-2;
			for($t=0;$t<$dataHeaderCount; $t++)
			{
				$data[0][$t] = getDisplayName($data[0][$t]);
			}
			$csv_Data="";
			foreach($data as $row) 
			{
				$row = array_slice($row,0,count($row)-2);
				$csv_Data.= '"' . stripslashes(implode('","',$row)) . "\"\n";
				print '"' . stripslashes(implode('","',$row)) . "\"\n";
			}
			
			$file_Name = $fileName;
			$fileName = $WDOCUMENT_ROOT."/".$fileName;
			
			$handle = fopen($fileName, 'w');
			fwrite($handle, $csv_Data);
			
			if($dataRecordCount > 1)
			{
				copyCSVToFTPIN($file_Name);//to place in FTP In folder
			}
			
			//update to processed state...
			$updateSql = "UPDATE madison_paydata mpd SET mpd.paydata_status='Y', mpd.paydata_processed_by='".$username."', mpd.paydata_processed_date=NOW() WHERE mpd.paydata_sno IN ($groupSnos) AND mpd.paydata_emp_status NOT IN ('DA','INACTIVE')";
			mysql_query($updateSql,$db);
		}
	}
	else
	{
		require("rlibdata.php"); //For the formats other than html ie for export purpose  using rlib.
	}

	//condition  for print option from main page
	if($defaction == "print")
	echo "<script>window.print(); window.setInterval('window.close();', 10000)</script>";
	function cleanArray($array)
	{
	   foreach ($array as $index => $value)
	   {
		   if(is_array($array[$index]))
				$array[$index] = cleanArray($array[$index]);
		   if(empty($value) && $value!=0)
				$array[$index]="";
	   }
	   return $array;
	}
if($format=="html")
{?>
	<script>
		if(window.top.document.getElementById('reccount'))
		{
			window.top.document.getElementById('reccount').value = '<?php echo $recCount; ?>';
		}
	<?php
	if($recCount == 0)
	{?>
		window.onload=function()
		{
			if(window.top.document.getElementById('reportwindow'))
			{
				window.top.document.getElementById('reportwindow').width= "100%";
			}
			if(window.top.document.getElementById('reportwindow'))
			{
				window.top.document.getElementById('reportwindow').height= "200px";
			}
		};
	<?php
	}?>
	</script>
<?php
}?>