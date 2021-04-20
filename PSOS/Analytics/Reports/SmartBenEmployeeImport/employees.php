<?php
	ob_start();

	$rlib_filename="hrmsmartben.xml";
	require("global_reports.inc");
	require("rlib.inc");
	$reportfrm=$reportfrm;
	require_once("functions.inc.php");
	$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	if($format=="")
		$format="html";

	$filename="PEG_BenefitsEligibility".date('ymd');
	$module="HrmEmpSmartBen";
	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$db);
		$vrowdata=mysql_fetch_row($rresult);
		$vrow=explode("|username->",$vrowdata[0]);
		$Analytics_SmartBenEmployeeImport=$vrow[0];
		$cusername=$vrow[1];
		if(strpos($Analytics_SmartBenEmployeeImport,"|username->")!=0)
			$Analytics_SmartBenEmployeeImport=$vrow[0];

		session_update("cusername");		
		session_update("Analytics_SmartBenEmployeeImport");		
		$rdata=explode("|",$Analytics_SmartBenEmployeeImport);
	}
	else
	{
		if($defaction == "print")
		{
			$Analytics_SmartBenEmployeeImport=$_REQUEST['Analytics_SmartBenEmployeeImport'];
			$rdata=explode("|",$Analytics_SmartBenEmployeeImport);
			$tab=$rdata[18];
		}
		else 
		{
			$rdata=explode("|",$Analytics_SmartBenEmployeeImport);
			$tab=$rdata[18];
		}
	}

	// Start of code for dynamic display of columns of note type
	$arrEarTypes = getAllEarTypes();
	$earTypesCount  = count($arrEarTypes);
	$datanumber = 81; //this number must be changed whenever the rdata static count changes..IMPORTANT
	$loopear = 0;

	for($loopnote=0;$loopnote<$earTypesCount;$loopnote++)
	{
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_tdollamt";
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_compcontr";
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_empcontr";
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_dollamtded";
		$loopear++;
	}

	$valcount  = 0;
	$rdata_count = count($rdata);
	$checkArray = array();

	for($r=81;$r<$rdata_count;$r++)
	{
		if($rdata[$r] != "")
		{
		   $checkArray[$rdata[$r]] = $rdata[$r];
		   $valcount++; 
		}
	}

	$countarrDynamicFieldNames = count($arrDynamicFieldNames);
	for($loopnote=0;$loopnote<$countarrDynamicFieldNames;$loopnote++)
	{
		$notetype =  $arrDynamicFieldNames[$loopnote];
		$explodEar = explode("_",$notetype); 
		$dynDisplay_name = getDisplayName($arrDynamicFieldNames[$loopnote]);
		$headLine = "--";
		$headLine.= str_repeat("-",strlen($dynDisplay_name));
		if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
		{
			if(in_array($arrDynamicFieldNames[$loopnote],$checkArray))
				$$notetype = array($arrDynamicFieldNames[$loopnote],$dynDisplay_name,$headLine);
			else
				$$notetype = array('','',str_repeat("-",strlen($dynDisplay_name)));
		}
		else
		{
			$$notetype = array($arrDynamicFieldNames[$loopnote],$dynDisplay_name,$headLine); 
		}
		$arrNoteReferences[$loopnote] = $$notetype;
	}	
	
	$filternames = $rdata[31];	
	$filtervalues = $rdata[32];	
    $arrFilterNames = explode('^',$rdata[31]);
	$arrFilterValues = explode('^',addslashes($rdata[32]));			
		
		if($rdata[32] != '')
		{            
			$companydata = explode('^',addslashes($rdata[32]));
			
			// location code			
			$Ldata = $companydata[0];			
			$feidArrL= explode('!#!',$Ldata);			
			$ddata = $companydata[3];			
			$feidArrD= explode('!#!',$ddata);			
						
			//status code			
			$sdata = $companydata[0];
			
			if($sdata == 'N') {			
				$filterCondString_S .=" and el.empterminated = 'N'";			
			}else if($sdata == 'Y') {			
				$filterCondString_S .=" and el.empterminated = 'Y'";			
			}	
			
			$filtercond = $filterCondString_L ." ".$filterCondString_S . "  ". $filterCondString1 ."  ".$filterCondString_D;
		}

	$cpque="SELECT payperiod FROM cpaysetup WHERE status='ACTIVE'";
	$cpres=mysql_query($cpque,$db);
	$cprow=mysql_fetch_row($cpres);

	$countArrNoteReferences = count($arrNoteReferences);

	$sortarr=array("empusername","paytypeid","password","empssn","emplname","empfname","empmname","empannualsalary","empannualsalaryplus","emphourlysalary","empbonus","empfacilityid","empdob","emphiredate","empbenefitdate","emppaytypechangedate","emptermdate","empmaritalst","empgender","empaddr1","empaddr2","empcity","empstate","empzip","empPrimaryEmail","empjobtitle","empdept","occupation","empid");

	$date1="";
	$date2="";
	$type="All";

	$fieldNameArr 	= explode('^',$rdata[31]);
	$fieldValArr 	= explode('^',$rdata[32]);

	$sortingorder_array = $sortarr;

	if($fieldNameArr[0]=='empstatus' && $fieldValArr[0]=='Y'){
		$emptype 	= 'Terminated';
	}
	else if($fieldNameArr[0]=='empstatus' && $fieldValArr[0]=='N'){
		$emptype 	= 'Active';
	}else{
		$emptype 	= 'ALL';
	}
		
	$rep_company=$companyname;
	$rep_header="SmartBen Employee Import Report";
	$rep_title="Employee Status: ".$emptype;
	$rep_date="date";
	$rep_page="pageno";
	$rep_footer="";	

	$rep_sortorder="ASC";
	$rep_sortcol="empid";
	$empusername[0]="username"; //0
	$paytypeid[0]="paytypeid"; //1
	$password[0]="password"; //2
	$empssn[0]="empssn"; //3
	$emplname[0]="emplname"; //4
	$empfname[0]="empfname"; //5
	$empmname[0]="empmname"; //6
	$empannualsalary[0]="empannualsalary"; //7
	$empannualsalaryplus[0]="empannualsalaryplus"; //8
	$emphourlysalary[0]="emphourlysalary"; //9
	$empbonus[0]="empbonus"; //10
	$empfacilityid[0]="empfacilityid"; //11
	$empdob[0]="empdob"; //12
	$emphiredate[0]="emphiredate"; //13
	$empbenefitdate[0]="empbenefitdate"; //14
	$emppaytypechangedate[0]="emppaytypechangedate"; //15
	$emptermdate[0]="emptermdate"; //16
	$empmaritalst[0]="empmaritalst"; //17
	$empgender[0]="empgender"; //18
	$empaddr1[0]="empaddr1"; //19
	$empaddr2[0]="empaddr2"; //20
	$empcity[0]="empcity"; //21
	$empstate[0]="empstate"; //22
	$empzip[0]="empzip"; //23
	$empPrimaryEmail[0]="empPrimaryEmail"; //24
	$empjobtitle[0]="empjobtitle"; //25
	$empdept[0]="empdept"; //26
	$occupation[0]="occupation"; //27
	$empid[0]="payrollcode"; //28

	//Display names for each column heading
	$empusername[1]="Username";//1
	$paytypeid[1]="PayType Id";//2
	$password[1]="Password";//3	
	$empssn[1]="SSN";//4
	$emplname[1]="Last Name";//5
	$empfname[1]="First Name";//6
	$empmname[1]="Middle Name";//7
	$empannualsalary[1]="Annual Salary";//8
	$empannualsalaryplus[1]="Annual Salary Plus";//9
	$emphourlysalary[1]="Hourly Salary";//10
	$empbonus[1]="Bonus";//11
	$empfacilityid[1]="Facility ID";//12
	$empdob[1]="Birth Date";//13	
	$emphiredate[1]="Hire Date";//14
	$empbenefitdate[1]="Benefit Date";//15
	$emppaytypechangedate[1]="PayType Change Date";//16
	$emptermdate[1]="Term Date";//17
	$empmaritalst[1]="Marital Status";//18
	$empgender[1]="Gender";//19	
	$empaddr1[1]="Address";//20
	$empaddr2[1]="Apt.-Unit-Ste.";//21
	$empcity[1]="City";//22
	$empstate[1]="State";//23
	$empzip[1]="Zip Code";//24
	$empPrimaryEmail[1]="Office Email"; //25
	$empjobtitle[1]="Job Title"; //26
	$empdept[1]="Department"; //27
	$occupation[1]="Occupation";//28
	$empid[1]="Payroll Code";//29

	//underline for each column heading
	$empusername[2]="-----------------";//1
	$paytypeid[2]="-----------------";//2
	$password[2]="-----------------";//3	
	$empssn[2]="-----------------";//4
	$emplname[2]="-----------------";//5
	$empfname[2]="-----------------";//6
	$empmname[2]="-----------------";//7
	$empannualsalary[2]="-----------------";//8
	$empannualsalaryplus[2]="-----------------------";//9
	$emphourlysalary[2]="-----------------";//10
	$empbonus[2]="-----------------";//11
	$empfacilityid[2]="-----------------";//12
	$empdob[2]="-----------------";//13	
	$emphiredate[2]="-----------------";//14
	$empbenefitdate[2]="-----------------";//15
	$emppaytypechangedate[2]="--------------------------------";//16
	$emptermdate[2]="-----------------";//17
	$empmaritalst[2]="-----------------";//18
	$empgender[2]="-----------------";//19	
	$empaddr1[2]="-----------------";//20
	$empaddr2[2]="-----------------";//21
	$empcity[2]="-----------------";//22
	$empstate[2]="-----------------";//23
	$empzip[2]="-----------------";//24
	$empPrimaryEmail[2]="-----------------"; //25
	$empjobtitle[2]="-----------------"; //26
	$empdept[2]="-----------------"; //27
	$occupation[2]="-----------------";//28
	$empid[2]="-----------------";//29
	
	//ends here
	$sortarry_count=count($sortarr);

	$rep_sortcolno="";
    if($sortarr[0]!="")
    {
        for($q=0;$q<$sortarry_count;$q++)
        {
            if($sortarr[$q]==$rep_sortcol)
            {
                $rep_sortcolno=$q;
            }
        }
    }

	$k=0;
	
	//Array  for displaying  heading for all the columns selected
	for($q=0;$q<$sortarry_count;$q++)
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
	
	//Arrays for holding Billable and Taxable Types
	$arrayBillableType = array('Y' => 'Billable','N' =>'Non-Billable');
	$arrayTaxableType = array('Y' => 'Taxable','N' =>'Non-Taxable');
	//Ends here
	//Filter  conditions in Main query as per the selected values from the filters for each column
	$filtername_count=count($filternames_array);
	$accstr='';
	$from_hire="";
	$to_hire="";
	$empIntDirCond="";
	$empStatusCond="";

	//$groupbyCond="group by el.sno"; //Group by condition intially should have
	$groupbyCond = " GROUP BY hj.sno, el.sno ";

	for($f=0;$f<$filtername_count;$f++)
	{
		$fieldname="";
		$filedtable="";
		$fieldname=getserColName($filternames_array[$f]);
		$filedtable=getserTabName($filternames_array[$f]);

		if($filedtable=="hrcon_compen")
			$filedtable="hc";
		else if($filedtable=="hrcon_personal")
			$filedtable="hp";
		else if($filedtable=="hrcon_general")
			$filedtable="hg";
		else if($filedtable=="hrcon_w4")
			$filedtable="hw";
		else if($filedtable=="hrcon_deposit")
			$filedtable="hd";
		else if($filedtable=="hrcon_jobs")
			$filedtable="hr";

		if($filternames_array[$f] == "empStatus")
		{
			$empStatusCond="";
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and el.$fieldname='$filtervalues_array[$f]' ";
			}
		}		
		else
		{
			if($filtervalues_array[$f])
				$accstr=$accstr." and $filedtable.$fieldname like '%$filtervalues_array[$f]%' ";
		}
	}//End of Filter conditions

	//$department_dynStr = " AND FIND_IN_SET('".$username."',d.permission)>0 ";
        $department_dynStr = " AND d.sno !='0' AND d.sno IN ({$deptAccesSno}) ";
/*
	if($accountingpref!='NO' && chkUserPref($accountingpref,"11"))// New check implemented July 09, 2010 Piyush R chkUserPref
	{
		$department_dynStr = "";
	}*/
	
	//Query to fetch all the Employees details

	$qryEmp="SELECT hp.ssn as username_ssn,hp.d_birth as bd_username, hc.job_type as paytypeid, REPLACE(hp.ssn, '-', '') as ssn, hg.lname as lastname, hg.fname as firstname, hg.mname as middlename, CEIL(hj.rate) as annualsalary, '' as annualsalaryplus, hj.pamount as hourly_salary, '' as bonus, '' as facilityid, if(hc.emp_rehire_date!='', ".tzRetQueryStringSTRTODate("hc.emp_rehire_date","%m-%d-%Y","Date","-").", ".tzRetQueryStringSTRTODate("hc.date_hire","%m-%d-%Y","Date","-").") AS date_hire, ".tzRetQueryStringSTRTODate("hj.s_date","%m-%d-%Y","Date","-")." as benefit_date, '' as paytype_changedate, '' as term_date, hp.m_status as marital_status, hp.hp_gender as gender, hg.address1 as address1, hg.address2 as address2, hg.city as city, hg.state as state, hg.zip as zipcode, hg.email as office_email, hj.project as job_title, d.deptname as department, m.name as occupation, el.sno as payrollcode, hj.username, hj.jotype, hc.emptype, hc.pay_assign, hc.salary
		FROM emp_list el
		LEFT JOIN hrcon_jobs hj ON el.username = hj.username
		LEFT JOIN hrcon_personal hp ON el.username = hp.username
		LEFT JOIN hrcon_general hg ON el.username = hg.username
		LEFT JOIN hrcon_compen hc ON el.username = hc.username
		LEFT JOIN department d ON hc.dept = d.sno
		LEFT JOIN manage m ON hj.industryid = m.sno AND m.type = 'joindustry'
		WHERE el.lstatus != 'DA'
		AND hc.ustatus='active'
		AND hp.ustatus='active'
		AND d.status='Active'
		AND hj.ustatus ='active'
		AND hj.jtype like '%OP%'
		 ".$filtercond." $department_dynStr ";

	$qryEmp.=" $groupbyCond ";
	$resEmp=mysql_query($qryEmp,$rptdb);

	//Satrt of Fetching the data for dynamic columns
	$dynamic_values = array();

	$sql = "select hct.tot_dollar_amt,hct.comp_contribution,hct.emp_contribution,hct.dollar_amt_deduct,hct.title,hct.username from emp_list el ,hrcon_contribute hct where el.username = hct.username and hct.ustatus = 'active'"; 
	$res = mysql_query($sql,$rptdb); 
	while($row = mysql_fetch_row($res))
	{
		$row[4] = str_replace(" ","___","$$".$row[4]);
		$dynamic_values[$row[4]."_tdollamt"][$row[5]] = $row[0];
		$dynamic_values[$row[4]."_compcontr"][$row[5]] = $row[1];
		$dynamic_values[$row[4]."_empcontr"][$row[5]] = $row[2];
		$dynamic_values[$row[4]."_dollamtded"][$row[5]] = $row[3];
	}
	//End of Fecting the data for dynamic columns
	//Code  for fetching the data for each column selected and put it in an array-$data
	$v = 1;
	$v1 = 1;
	$bankAccType1="";
	$bankAccType2="";	
	while($arr=mysql_fetch_row($resEmp))
	{
	 	$ii = 0;
		if($v1==0)
			$v1=1;
		
		//-------------Code for assigning the each column's data to the $data---------- 
		//Preparing Data for Employee
		$bdate 			= '';
		$arraybdate 	= '';
		$dob 			= '';
		$hiredate 		= '';
		$benefitdate 	= '';
		$emp_password 	= '';
		$assnJobType 	= '';
		$jobtype 		= '';
		$empJobType 	= '';
		$annual_salary  = '';

		$empJobType=getManage($arr[30]);
		$lastestassnJobType=getAssignmentDepartment($arr[28]);
		$assnJobType=getManage($arr[29]);
		$jobtype = ($arr[2] != 'Y') ? $empJobType : $lastestassnJobType;

		//Annual Salary
		if($arr[7]!='0'){
			$annual_salary = ceil($arr[7]);
		}else{
			$annual_salary = '';
		}

		//Hourly Salary
		if($arr[9]!='0'){
			$hourly_salary = $arr[9];
		}else{
			$hourly_salary = '';
		}

		$empsal 		= ceil($arr[32]);
		$annual_salary	= ($arr[31] != 'Y') ? $empsal : $annual_salary;
		$hourly_salary	= ($arr[31] != 'Y') ? $empsal : $hourly_salary;

		//Data for Employee Type
		if($jobtype=="Internal Temp/Contract"){
			$emptype = "17000";
		}
		elseif($jobtype=="Internal Direct"){
			$emptype = "17000";
		}
		elseif($jobtype=="Temp/Contract"){
			$emptype = "17001";
		}
		elseif($jobtype=="Temp/Contract to Direct"){
			$emptype = "17001";
		}
		else{
			$emptype = '';
		}

		if($annual_salary == 0 || $annual_salary == NULL){
			$annual_salary = '';
		}else{
			$annual_salary = $annual_salary;
		}

		if($hourly_salary == 0 || $hourly_salary == NULL){
			$hourly_salary = '';
		}else{
			$hourly_salary = number_format($hourly_salary, 2, '.', '');
		}

		//Data for Annual Salary and Hourly Salary depending on Emp type
		if($arr[2] != 'Y' && $arr[31] != 'Y' && $jobtype=="Internal Temp/Contract"){
			$annual_salary = '';
			$hourly_salary = $hourly_salary;
		}
		elseif($arr[2] != 'Y' && $jobtype=="Internal Direct"){
			$annual_salary = $annual_salary;
			$hourly_salary = '';
		}
		elseif($arr[2] != 'Y' && $jobtype=="Temp/Contract"){
			$annual_salary = '';
			$hourly_salary = $hourly_salary;
		}
		elseif($arr[2] != 'Y' && $jobtype=="Temp/Contract to Direct"){
			$annual_salary = $annual_salary;
			$hourly_salary = $hourly_salary;
		}

		//Data for Annual Salary and Hourly Salary depending on Assignment type
		if($arr[31] != 'Y' && $empJobType=="Internal Temp/Contract"){
			$annual_salary = '';
			$hourly_salary = $hourly_salary;
		}
		elseif($arr[31] != 'Y' && $empJobType=="Internal Direct"){
			$annual_salary = $annual_salary;
			$hourly_salary = '';
		}
		elseif($arr[31] != 'Y' && $empJobType=="Temp/Contract"){
			$annual_salary = '';
			$hourly_salary = $hourly_salary;
		}
		elseif($arr[31] != 'Y' && $empJobType=="Temp/Contract to Direct"){
			$annual_salary = $annual_salary;
			$hourly_salary = $hourly_salary;
		}elseif($arr[31] != 'Y' && $empJobType==""){
			$annual_salary = $annual_salary;
			$hourly_salary = '';
		}

		//Data for Employee Password
		if($arr[1]=="0-0-0"){
			$finalPassword="";
		}
		else{
			$emp_password 	=	$ac_aced->decrypt($arr[1]);
			$dtArr 			= 	explode('-',$emp_password);
			$finalPassword 	= 	'';
			foreach($dtArr as $dtval){
				$dtvalsize = strlen((string)$dtval);
				if($dtvalsize==1 && $dtval!='00'){
					$dtval = '0'.$dtval;
				}elseif($dtvalsize==1){
					$dtval = '';
				}
				$finalPassword .= $dtval;
			}
		}

		//Data for Employee Gender
		if($arr[17]=="F"){
			$gender="F";
		}
		else if($arr[17]=="M"){
			$gender="M"; 
		}
		else{
			$gender="";
		}

		//Data for preparing Employee Username
		$empbdt = $ac_aced->decrypt($arr[1]);
		$empbdtArr = explode('-',$empbdt);
		$usernameDay 	= '';
		$empbdvalsize 	= strlen((string)$empbdtArr[1]);
		if($empbdvalsize==1 && $empbdtArr[1]!='0'){
			$empbdval 	= 	'0'.$empbdtArr[1];
		}elseif($empbdtArr[1]=='0'){
			$empbdval 	= 	'';
		}else{
			$empbdval 	= 	$empbdtArr[1];
		}
		$usernameDay 	= 	$empbdval;
		$empusername 	= 	substr($ac_aced->decrypt($arr[0]),5).$usernameDay.'PEG';

		//Data for Employee Date of Birth
		if($arr[1]!='' && $arr[1]!='0-0-0'){
			$dob = $arr[1];
		}else{
			$dob ='00/00/0000';
		}

		//Data for Employee Hire Date
		if($arr[12]!='' && $arr[12]!='0-0-0'){
			$hiredate = $arr[12];
		}else{
			$hiredate ='00/00/0000';
		}

		//Data for Employee Benefit date
		if($arr[13]!='' && $arr[13]!='0-0-0' && $arr[13]!=NULL){
			$benefitdate =$arr[13];  
		}else{
			$benefitdate ='00/00/0000';
		}

		$annual_salary = (string)$annual_salary;

		$values_array = array(
			"empusername"=>$empusername,
			"paytypeid"=>$emptype,
			"password"=>$finalPassword,
			"empssn"=>$ac_aced->decrypt($arr[3]),
			"emplname"=>$arr[4],
			"empfname"=>$arr[5],
			"empmname"=>$arr[6],
			"empannualsalary"=>$annual_salary,
			"empannualsalaryplus"=>$arr[8],
			"emphourlysalary"=>$hourly_salary,
			"empbonus"=>$arr[10],
			"empfacilityid"=>$arr[11],
			"empdob"=>get_standard_dateFormat($ac_aced->decrypt($dob), 'm-d-Y','m/d/Y'),
			"emphiredate"=>$hiredate,
			"empbenefitdate"=>$benefitdate,
			"emppaytypechangedate"=>$arr[14],
			"emptermdate"=>$arr[15],
			"empmaritalst"=>ucfirst($arr[16]),
			"empgender"=>$gender,
			"empaddr1"=>$arr[18],
			"empaddr2"=>$arr[19],
			"empcity"=>$arr[20],
			"empstate"=>$arr[21],
			"empzip"=>$arr[22],
			"empPrimaryEmail"=>$arr[23],
			"empjobtitle"=>$arr[24],
			 "empdept"=>$arr[25],
			"occupation"=>$arr[26],
			"empid"=>$arr[27]
		);

		//Preparing the actual data
		if($v == 1 && $v1 == 1)
		{
			for($q=0;$q<$sortarry_count;$q++)
			{
				if(in_array($sortarr[$q],$arrDynamicFieldNames))  //data array for dynamic columns
				{
					if($dynamic_values[$sortarr[$q]][$arr[69]]!="0.00")
						$values_array[$sortarr[$q]] =  $dynamic_values[$sortarr[$q]][$arr[69]];
					else
						$values_array[$sortarr[$q]] = "";
				}

				$variable = $$sortarr[$q];

				if($variable[0]!="")
				{
					$data[$i][$ii] = $values_array[$sortarr[$q]];
					$sslength_array[$sortarr[$q] ] = trim((strlen($values_array[$sortarr[$q] ]) <=strlen($variable[2])) ? strlen($values_array[$sortarr[$q] ]) : (strlen($variable[2])+3));
					$ii++;
				}
			}

			//Condition for each column's length
			if($sortarry_count)
				$slength = $sslength_array[$sortarr[28]] ? $sslength_array[$sortarr[28]] : 1;
			
			//Link that will redirect to Employee's summary screen
			$data[$i][$ii]="javascript:showEmp('$arr[27]','$def_appsvr_domain')";
			$ii++;
			
			if(($empusername[0]!="") || ($paytypeid[0]!="") || ($password[0]!="") || ($empssn[0]!="") || ($emplname[0]!="") || ($empfname[0]!="") || ($empmname[0]!="") || ($empannualsalary[0]!="") || ($empannualsalaryplus[0]!="") || ($emphourlysalary[0]!="") || ($empbonus[0]!="") || ($empfacilityid[0]!="") || ($empdob[0]!="") || ($emphiredate[0]!="") || ($empbenefitdate[0]!="") || ($emppaytypechangedate[0]!="") || ($emptermdate[0]!="") || ($empmaritalst[0]!="") || ($empgender[0]!="") || ($empaddr1[0]!="") || ($empaddr2[0]!="") || ($empcity[0]!="") || ($empstate[0]!="") || ($empzip[0]!="") || ($empPrimaryEmail[0]!="") || ($empjobtitle[0]!="") || ($empdept[0]!="") || ($occupation[0]!="") || ($payrollcode[0]!=""))
			{
				$data[$i][$ii]=$slength;  //if column selected,corresponding length will be assigned to $data
				$ii++;
			}
			else if($countarrDynamicFieldNames) //for Dynamic columns
			{
				for($looplength = 0;$looplength<$countarrDynamicFieldNames;$looplength++)
				{
					$variable = $arrDynamicFieldNames[$looplength];
					if($variable[0] != "")
					{
						$data[$i][$ii]=$slength;
						$ii++;
					}
				}//for($looplength = 0;
			}
			$i++;
		}//End of Preparing actual data
   
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
	if($format == 'csv')
	{
		$date=date("YmdHis", time());
		$filename = $filename.".".$format;
		$mime = 'application/'.$format;		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Type: $mime; name=$filename");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Transfer-Encoding: binary");
		$dataHeaderCount = count($headval[0]);
		for($t=0;$t<=$dataHeaderCount; $t++)
		{
			$data[0][$t] = trim($headval[0][$t]);
		}
		foreach($data as $row) 
		{
			$row = array_slice($row,0,count($row)-2);
			print '"'.stripslashes(implode('","',$row))."\"\n";
		}
	} else {
		require("rlibdata.php");
	}

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
?>