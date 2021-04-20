<?php
	ob_start();
	$rlib_filename="hrassignments.xml";
	require("global_reports.inc");
	require("rlib.inc");
	require_once("functions.inc.php");
        $deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
    if($format=="")
		$format="html";
	$filename="hrassignments";
	if($rptFrom !="0" && $rptFrom !="")
		$module="hrassignments".$rptFrom;
	else
		$module="hrassignments";

	function seleAtype($ast)
	{
		if($ast=="OP")
			$astyp="Project";
		else if($ast=="OB")
			$astyp="On Bench";
		else if($ast=="OV")
			$astyp="On Vacation";	
		else if($ast=="AS")
		    $astyp="Administrative Staff";		
		return $astyp;
	}
	
	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$db);
		$vrowdata=mysql_fetch_row($rresult);
		$vrow=explode("|username->",$vrowdata[0]);
		$analyticsAssign=$vrow[0];
		$cusername=$vrow[1];
		if(strpos($analyticsAssign,"|username->")!=0)
        	$analyticsAssign=$vrow[0];
	
        	session_update("cusername");
		session_update("analyticsAssign");
    	
		$rdata=explode("|",$analyticsAssign);
	}
	else
	{
		if($flag=="subuser")
        {
            session_unregister("analyticsAssign");
            session_register("analyticsAssign");
            $analyticsAssign=$pagescompval;
		}
        if($defaction=="print")
        {
			$cusername=$username;
			session_register("cusername");
        }
     	$rdata=explode("|",$analyticsAssign);

		$tab=$rdata[9];
	}
		
	if($flag=="subuser")
	{
		session_register("cusername");
		$cusername=$listname_val;
		$flag="";
	}
	//while customization...
	if($tab=="addr" || ($view=="myreport" && trim($vrow[0])!=""))
	{
		//date filter values;
		$dateopt = $rdata[1];
		$fromdate = $rdata[2];
		$todate = $rdata[3];
		
		//filter pane values
		$filternames_array = explode('^',$rdata[4]);
		$filtervalues_array = explode('^',formateSlashes($rdata[5]));
		
		//sort orders
		$sortingorder = $rdata[6];		
		if($sortingorder == "")
		{
			$rep_sortorder = "ASC";
			$rep_sortcol = "CompanyName";
		}
		else
		{
			$rep_sortcol=$rdata[6];
			$rep_sortorder=$rdata[7]; //like ASC or DESC
			$sortingorder_array = explode("^",$sortingorder);
		}		
		$sortarr = explode('^',$rdata[8]);		

		$com_qr_str = "";
    	//Format  page values;
		$rep_orient=$rdata[11]!="" ? $rdata[11] : "landscape";
		$rep_paper=$rdata[12]!="" ? $rdata[12] : "letter";
		
		//header/footer page values;
		$rep_company=$rdata[13]!="" ? $rdata[13] : "";
		$rep_header=$rdata[14]!="" ? $rdata[14] : "";
		$rep_title=$rdata[15]!="" ? $rdata[15] : "";
		$rep_date=$rdata[16]!="" ? $rdata[16] : "";
		$rep_page=$rdata[17]!="" ? $rdata[17] : "";
		$rep_footer=$rdata[18]!="" ? $rdata[18] : "";
		//columns names start from $rdata[20]
		$varCompanyName[0] = $rdata[20];
		$varFirstName[0] = $rdata[21];
		$varMiddleName[0] =  $rdata[22];
		$varLastName[0] = $rdata[23];
		$varPayRate[0] =  $rdata[24];
		$varBillRate[0] =  $rdata[25];
		$varSalary[0] =  $rdata[26];
		$varCompenCode[0] =  $rdata[27]; //Compensation Code
		$varEmployeeId[0] =  $rdata[28];
		$varSSN[0] =  $rdata[29]; 
		$varAssgName[0] =   $rdata[30];
		$varStartDate[0] = $rdata[31];
		$varRecruiter[0] = $rdata[32];
		$varSalesAgent[0] =   $rdata[33];
		$varStatus[0] = $rdata[34];
		$otbrate[0] = $rdata[35];
		$blocation[0] = $rdata[36];
		$otpayrate[0] = $rdata[37];
		$enddate[0] = $rdata[38];
		$expenddate[0] = $rdata[39];
		$federalid[0] = $rdata[40];
		$customer[0] = $rdata[41];
		$dblpayrate[0] = $rdata[42];
		$dblbrate[0] = $rdata[43];
		$jocreator[0] = $rdata[44];
		$commempname[0] = $rdata[45];
		$commamount[0] = $rdata[46];
		$commsource[0] = $rdata[47];
		$placefee[0] = $rdata[48];
		$margin[0] = $rdata[49];
		$assignmentid[0] = $rdata[50];
		$paypid[0] = $rdata[51];
		$credate[0] = $rdata[52];
		$moduser[0] = $rdata[53];
		$moddate[0] = $rdata[54];
		$assignmenttype[0] = $rdata[55];
		$jobtype[0] = $rdata[56];
		$subhours[0] = $rdata[57];
		$apphours[0] = $rdata[58];
		$burden[0] = $rdata[59];
		$subdate[0] = $rdata[60];
		$aprdate[0] = $rdata[61];
		$tstrdate[0] = $rdata[62];
		$tenddate[0] = $rdata[63];
		$corpcode[0] = $rdata[64];
		//these are for per Diem
		$pdlodging[0] = $rdata[65];
		$pdmie[0] = $rdata[66];
		$pdtotal[0] = $rdata[67];
		$pdbillable[0] = $rdata[68];
		$pdtaxable[0] = $rdata[69];
		$pdtotamt[0] = $rdata[70];
		$alternateId[0] = $rdata[71];//added this new coloumn
		$reghours[0] = $rdata[72];
		$overtimehours[0] = $rdata[73];
		$doubletimehours[0] = $rdata[74];
		$billablehours[0] = $rdata[75];
		$reportperson[0] = $rdata[76];
		$contactperson[0] = $rdata[77];
		$assign_category[0] = $rdata[78];
		$assign_refcode[0] = $rdata[79];
		$assign_billcontact[0] = $rdata[80];
		$assign_billaddr[0] = $rdata[81];
		$assign_imethod[0] = $rdata[82];
		$assign_iterms[0] = $rdata[83];
		$assign_pterms[0] = $rdata[84];
		$assign_tsapproved[0] = $rdata[85];
		$assign_ponumber[0] = $rdata[86];
		$assign_department[0] = $rdata[87];
		$assign_billterms[0] = $rdata[88];
		$assign_sterms[0] = $rdata[89];
		$jlocation[0] = $rdata[90];
		$assgnReasonCodes[0] = $rdata[108];
		$assgnReason[0] = $rdata[91];
		$workersCompRate[0] =  $rdata[92]; //Compensation rate
		$HRMDepartment[0] =  $rdata[93]; //HRMDepartment
		$Role[0]=$rdata[94]; // Role, added Sep 06 2010 Piyush R.
		$markup[0]=$rdata[95];		
		$empEmailId[0]=$rdata[96];
		$empPrimaryNumber[0] = $rdata[97];
		$empMobileNumber[0] = $rdata[98];
		$fedtaxallowance[0] =  $rdata[111]; //Federal tax allowances
		$filingstatus[0]=  $rdata[112];
		$withholdingstate[0]=  $rdata[113];
		$statetaxallowance[0]=  $rdata[114];
		$paychkdelmethod[0]=  $rdata[115];
		$statetaxwithholdpercentage[0]=  $rdata[116];
        	$BillBurden[0] = $rdata[99];
		$RegularPayRate[0] = $rdata[100];
		$RegularSalary[0] = $rdata[101];
		$Regularotpayrate[0] = $rdata[102];
		$Regulardblpayrate[0] = $rdata[103];
		$Regularperdiemrate[0] = $rdata[104];
		$PayBurdenType[0] = $rdata[105];
		$BillBurdenType[0] = $rdata[106];
		$industry[0] = $rdata[107];
		//ends here
					
		//i think  the fallowing code is no where use...-prasadd
		$cur_com_emp_ptrn = $rdata[72];//the current index value should be 2 greater than max loop value in custreport.php
		if($cur_com_emp_ptrn != "")
		{
			$type_person_splitter = explode("^",$cur_com_emp_ptrn);
			$assign_Type_Val = trim($type_person_splitter[0]);
			$assign_Person_Val = trim($type_person_splitter[1]);
			
			$com_qr_str = " and a.type = '".$assign_Type_Val."' and a.person = ".$assign_Person_Val;
		}
	}
	else 
	{
		//Add field names in this array to show them by default....
		if($rptFrom == "1")
		{
			$sortarr = array("FirstName","LastName","AssignmentName","CompanyName","Status","StartDate","assgnReasonCodes");
			$filternames_array = $sortarr;
			$filtervalues_array = array("","","","","active","","");
			$sortingorder_array = array("FirstName","LastName","AssignmentName","StartDate");
			$rep_header="Expected Time Sheets";
			$rep_title="Employees on Projects";
		}
		elseif($rptFrom == "2")
		{
			$sortarr = array("SalesAgent","assignmenttype","CompanyName","AssignmentName","FirstName","LastName","StartDate","Status","industry");
			$filternames_array = $sortarr;
			$filtervalues_array = array("","OP","","","","","","pending","");
			$sortingorder_array = array("SalesAgent");
			$rep_header="Assignments to be approved";
			$rep_title="By Recruiter for a date range";
		}
		elseif($rptFrom == "3")
		{
			$sortarr = array("SalesAgent","CompanyName","jobtype","AssignmentName","Status","FirstName","LastName","StartDate","enddate","aprdate");
			$filternames_array = array("CompanyName","FirstName","LastName","AssignmentName","StartDate","SalesAgent","Status","enddate","jobtype","aprdate","subdate");
			$filtervalues_array = array("","","","","","","ALL","","ALL","","");
			$sortingorder_array = array("SalesAgent","CompanyName","AssignmentName");
			$rep_header="Placements By Recruiter";
			$rep_title="Placements";
		} 
		else
		{	$sortarr = array("federalid","customer","CompanyName","FirstName","MiddleName","LastName","PayRate","otpayrate","BillRate","otbrate","Salary","CompenCode","SSNNumber","AssignmentName","StartDate","blocation","Recruiter","SalesAgent","placefee","margin","assignmenttype","jobtype","reportperson","contactperson");
			$filternames_array = $sortarr;
			$filtervalues_array = array("","","","","","","","","","","","","","","","","","","","","","","","");
			$sortingorder_array = array("federalid","customer");
			$rep_header="Assignment Report";
			$rep_title="All Assignments";
		}
		$dateopt = "none";
		$fromdate = "";
		$todate = "";
		$com_qr_str = "";

		$rep_orient="landscape";
		$rep_paper="letter";
		
		$rep_company=$companyname;
		
		$rep_date="date";
		$rep_page="pageno";
		$rep_footer="";	

		$rep_sortorder="ASC";
		$rep_sortcol="CompanyName";

		$varCompanyName[0] = "CompanyName";
		$varFirstName[0] = "FirstName";
		$varMiddleName[0] =  "MiddleName";
		$varLastName[0] = "LastName";
		$varPayRate[0] =  "PayRate";
		$varBillRate[0] =  "BillRate";
		$varSalary[0] =  "Salary";
		$varCompenCode[0] =  "CompenCode"; //Compensation Code
		$varEmployeeId[0] =  "EmployeeId";
		$varSSN[0] =  "SSNNumber";
		$varAssgName[0] =   "AssignmentName";
		$varStartDate[0] = "StartDate";
		$varRecruiter[0] = "Recruiter";
		$varSalesAgent[0] =   "SalesAgent";
		$varStatus[0] = "Status";
		$otbrate[0]="otbrate";
		$blocation[0]="blocation";
		$otpayrate[0]="otpayrate";
		$enddate[0] = "enddate";
		$expenddate[0] = "expenddate";
		$federalid[0] = "federalid";
		$customer[0] = "customer";
		$dblpayrate[0] = "dblpayrate";
		$dblbrate[0] = "dblbrate";
		$jocreator[0] = "jocreator";
		$commempname[0] = "commempname";
		$commamount[0] = "commamount";
		$commsource[0] = "commsource";
		$placefee[0] = "placefee";
		$margin[0] = "margin";
		$assignmentid[0] = "assignmentid";
		$paypid[0] = "paypid";
		$credate[0] = "credate";
		$moduser[0] = "moduser";
		$moddate[0] = "moddate";
		$assignmenttype[0] = "assignmenttype";
		$jobtype[0] = "jobtype";
		$subhours[0] = "subhours";
		$apphours[0] = "apphours";
		$burden[0] = "burden";
		$subdate[0] = "subdate";
		$aprdate[0] = "aprdate";
		$tstrdate[0] = "tstrdate";
		$tenddate[0] = "tenddate";
		$reportperson[0] = "reportperson";
		$contactperson[0] = "contactperson";
		$workersCompRate[0] =  "workersCompRate"; //Compensation Rate
		$HRMDepartment[0] =  "HRMDepartment"; //HRMDepartment
		$fedtaxallowance[0] =  "fedtaxallowance"; //Federal Tax Allowances
		$filingstatus[0]=  "filingstatus";
		$withholdingstate[0]=  "withholdingstate";
		$statetaxallowance[0]=  "statetaxallowance";
		$statetaxwithholdpercentage[0] = 'statetaxwithholdpercentage';
		$paychkdelmethod[0]=  "paychkdelmethod";
		$Role[0]=  "Role"; // Role added Sep 06, 2010 Piyush R.
		$markup[0]=  "markup";		
		$empEmailId[0] = 'Primary E-mail';
		$empPrimaryNumber[0] = 'Primary Phone';
		$empMobileNumber[0] = 'Mobile Phone';
        	$BillBurden[0] = "BillBurden";
		$RegularPayRate[0] = "RegularPayRate";
		$RegularSalary[0] = "RegularSalary";
		$Regularotpayrate[0] = "Regularotpayrate";
		$Regulardblpayrate[0] = "Regulardblpayrate";
		$Regularperdiemrate[0] = "Regularperdiemrate";
		$PayBurdenType[0] = "PayBurdenType";
		$BillBurdenType[0] = "BillBurdenType";
		$industry[0] = "industry";
		$assgnReasonCodes[0] = "assgnReasonCodes";
		$assgnReason[0] = "assgnReason";
		
	}
		$varCompanyName[1] = "Company Name";
		$varFirstName[1] = "First Name";
		$varMiddleName[1] =  "Middle Name";
		$varLastName[1] = "Last Name";
		$varPayRate[1] =  "Pay Rate";
		$varBillRate[1] =  "Bill Rate";
		$varSalary[1] =  "Salary";
		$varCompenCode[1] =  "Workers Compensation Code"; //Compensation Code
		$varEmployeeId[1] =  "Employee Id";
		$varSSN[1] =  "SSN";
		$varAssgName[1] =   "Assignment Name";
		$varStartDate[1] = "Start Date";
		$varRecruiter[1] = "Recruiter/Vendor";
		$varSalesAgent[1] = "Placed By";
		$varStatus[1] = "Status";
		$otbrate[1]="Overtime Bill Rate";
		$blocation[1]="HRM Location";
		$otpayrate[1]="Overtime Pay Rate";
		$enddate[1] = "End Date";
		$expenddate[1] = "Expected End Date";
		$federalid[1] = "Federal ID";
		$customer[1] = "Customer #";
		$dblpayrate[1] = "Double Time Pay Rate";
		$dblbrate[1] = "Double Time Bill Rate";
		$jocreator[1] = "Job Order Creator";
		$commempname[1] = "Commission Employee Name";
		$commamount[1] = "Commission Amount";
		$commsource[1] = "Commission Source";
		$placefee[1] = "Placement Fee";
		$margin[1] = "Margin ($)";
		$assignmentid[1] = "Assignment ID";
		$paypid[1] = "Payroll Provider ID#";
		$credate[1] = "Created Date";
		$moduser[1] = "Modified User";
		$moddate[1] = "Modified Date";
		$assignmenttype[1] = "Assignment Type";
		$jobtype[1] = "Job Type";
		$subhours[1] = "Total Hours";
		$apphours[1] = "Number of hours Approved";
		$burden[1] = "Pay Burden";
		$subdate[1] = "Submitted Date";
		$aprdate[1] = "Approved Date";
		$tstrdate[1] = "Timesheet Start Date";
		$tenddate[1] = "Timesheet End Date";
		$corpcode[1] = "Corp Code";
		$workersCompRate[1] =  "Workers Compensation Rate"; //Compensation Code
		$HRMDepartment[1] =  "HRM Department"; //HRMDepartment
		$fedtaxallowance[1]="Federal Tax Allowances"; //Federal Tax Allowances
		$filingstatus[1]=  "Filing Status";
		$withholdingstate[1]=  "Withholding State";
		$statetaxallowance[1]=  "State Tax Allowances";
		$statetaxwithholdpercentage[1]=  "State Tax Withholding Percentage";
		$paychkdelmethod[1]=  "Pay Check Delivery Method";
		$Role[1] = "Role";
		$markup[1] = "Mark Up";		
		$empEmailId[1] = 'Primary E-mail';
		$empPrimaryNumber[1] = 'Primary Phone';
		$empMobileNumber[1] = 'Mobile Phone';
		$BillBurden[1] = "Bill Burden";
		$RegularPayRate[1] = "Regular Pay Rate(HRM)";
		$RegularSalary[1] = "Salary(HRM)";
		$Regularotpayrate[1] = "Overtime Pay Rate(HRM)";
		$Regulardblpayrate[1] = "Double Time Pay Rate(HRM)";
		$Regularperdiemrate[1] = "Per Diem Total Amount(HRM)";
		$PayBurdenType[1] = "Pay Burden Type";
		$BillBurdenType[1] = "Bill Burden Type";
		$industry[1] = "Industry";
		//these are for per Diem
		$pdlodging[1] = "Lodging";
		$pdmie[1] = "M&IE";
		$pdtotal[1] = "Per Diem - Total";
		$pdbillable[1] ="Per Diem - Billable";
		$pdtaxable[1] = "Per Diem - Taxable";
		$pdtotamt[1] = "Per Diem Total Amount";
		$alternateId[1] = "Job ID";
		$reghours[1] = "Regular Hours";
		$overtimehours[1] = "Over Time Hours";
		$doubletimehours[1] = "Double Time Hours";
		$billablehours[1] = "Billable Hours";
        $reportperson[1] = "Reports to";
        $contactperson[1] = "Contact";
		$assign_category[1] = "Category";
		$assign_refcode[1] = "Ref.Code";
		$assign_billcontact[1] = "Billing Contact";
		$assign_billaddr[1] = "Billing Address";
		$assign_imethod[1] = "Invoice Method";
		$assign_iterms[1] = "Invoice Frequency";
		$assign_pterms[1] = "Payment Terms(#days)";
		$assign_tsapproved[1] = "Timesheet Approval";
		$assign_ponumber[1] = "PO Number";
		$assign_department[1] = "Department";
		$assign_billterms[1] = "Billing Terms";
		$assign_sterms[1] = "Service Terms";
		$jlocation[1] = "Job Location";
		$assgnReasonCodes[1] = "Reason Codes";
		$assgnReason[1] = "Reason";
		
		//ends here
		
		$varCompanyName[2] = "--------------------";
		$varFirstName[2] = "--------------------";
		$varMiddleName[2] =  "--------------------";
		$varLastName[2] = "--------------------";
		$varPayRate[2] =  "--------------------";
		$varBillRate[2] =  "--------------------";
		$varSalary[2] =  "--------------------";
		$varCompenCode[2] =  "--------------------------------------------";
		$varEmployeeId[2] =  "--------------------";
		$varSSN[2] =  "------------";
		$varAssgName[2] =   "--------------------";
		$varStartDate[2] = "--------------";
		$varRecruiter[2] = "-----------------";
		$varSalesAgent[2] =   "---------------";
		$varStatus[2] = "----------------";
		$otbrate[2]="---------------------";
		$blocation[2]="-----------------------------";
		$otpayrate[2]="--------------------";
		$enddate[2] = "----------";
		$expenddate[2] = "-------------------";
		$federalid[2] = "----------";
		$customer[2] = "-------------";
		$dblpayrate[2] = "----------------------";
		$dblbrate[2] = "----------------------";
		$jocreator[2] = "----------------------";
		$commempname[2] = "--------------------------";
		$commamount[2] = "---------------------------";
		$commsource[2] = "--------------------------";
		$placefee[2] = "---------------------";
		$margin[2] = "-----------";
		$assignmentid[2] = "-----------------";
		$paypid[2] = "--------------------------";
		$credate[2] = "--------------------------";
		$moduser[2] = "--------------------------";
		$moddate[2] = "--------------------------";
		$assignmenttype[2] = "----------------------";
		$jobtype[2] = "-------------------------";
		$subhours[2] = "-------------------------";
		$apphours[2] = "-------------------------";
		$burden[2] = "-------------------------";
		$subdate[2] = "----------------------";
		$aprdate[2] = "----------------------";
		$tstrdate[2] = "---------------------";
		$tenddate[2] = "-----------------------";
		$corpcode[2] = "----------";
		$assign_category[2] = "----------------------------";
		$assign_refcode[2] = "---------------";
		$assign_billcontact[2] = "-------------------------";
		$assign_billaddr[2] = "---------------------------";
		$assign_imethod[2] = "---------------";
		$assign_iterms[2] = "-------------------";
		$assign_pterms[2] = "------------------------";
		$assign_tsapproved[2] = "-------------------";
		$assign_ponumber[2] = "------------------";
		$assign_department[2] = "-----------------";
		$assign_billterms[2] = "----------------------------";
		$assign_sterms[2] = "----------------------------";
		$jlocation[2] = "----------------------------";
		$workersCompRate[2] =  "--------------------------------";
		$HRMDepartment[2] = "-------------------";
		$fedtaxallowance[2]="----------------------------";
		$filingstatus[2]="----------------------------"  ;
		$withholdingstate[2]= "----------------------------" ;
		$statetaxallowance[2]="----------------------------" ;
		$statetaxwithholdpercentage[2]= "------------------------------------------" ;
		$paychkdelmethod[2]="----------------------------"  ;
		$Role[2] = "----------------------------";
		$markup[2] = "----------------------------";
		$empEmailId[2] = "------------------------------------------" ;
		$empPrimaryNumber[2] = "----------------------------";
		$empMobileNumber[2] = "----------------------------";
		//these are for per Diem
		$pdlodging[2] = "-------------------------";
		$pdmie[2] = "-------------------------";
		$pdtotal[2] = "-------------------------";
		$pdbillable[2] = "-----------------------";
		$pdtaxable[2] = "-----------------------";
		$pdtotamt[2] = "--------------------------";
		$alternateId[2] = "--------------------";
		$reghours[2] = "------------------";
		$overtimehours[2] = "-------------------";
		$doubletimehours[2] = "------------------";
		$billablehours[2] = "------------------";
		$reportperson[2] = "-------------------------";
        $contactperson[2] = "-------------------------";
        $assgnReasonCodes[2] = "----------------------------";
		$assgnReason[2] = "----------------------------";
		$BillBurden[2] ="-------------------------";
		$RegularPayRate[2] = "-------------------------";
		$RegularSalary[2] = "-------------------------";
		$Regularotpayrate[2] = "--------------------------";
		$Regulardblpayrate[2] = "----------------------------";
		$Regularperdiemrate[2] = "---------------------------";
		$PayBurdenType[2] = "-----------------------";
		$BillBurdenType[2] = "----------------------";
		$industry[2] = "----------------------";
		//ends here
		
		$count_sortarr = count($sortarr);
		$rep_sortcolno="";
		if($sortarr[0]!="")
		{
			for($q=0;$q<$count_sortarr;$q++)
			{
				if($sortarr[$q]==$rep_sortcol)
				{
					$rep_sortcolno=$q;
				}
			}
		}
	
	$qstr="";
	$vstr="";
	$fromstr = "";
	$str = "";	
	
	$wcompRateColSelected = "";
	$whr_industry= "";
	$wCompRatesArr = array();
	if(in_array("industry",$filternames_array))
	{
		$index = array_search("industry",$filternames_array);
		if($filtervalues_array[$index]!= 'ALL' && $filtervalues_array[$index]!= ''){
		$whr_industry.= " AND mg.sno = '".$filtervalues_array[$index]."'";
		}
	}
	if(in_array("workersCompRate",$filternames_array))
	{
		$wcompRateColSelected = "yes";
	}
	
	if(in_array("Status",$filternames_array))
	{
		$index = array_search("Status",$filternames_array);
		$statusValue =  $filtervalues_array[$index];
	}
	else
	{
		$statusValue = "ALL";
	}
	
	//handling the case where the report in My reports with approved value
	if($statusValue=="approved")
	{
		$statusValue = "active";
	}

	$timeFilter = "no";
	$timeFilterVal = "";
	$subHrsVal = "";
	$appHrsVal = "";
	$appDateVal = "";
	$hr_join_cond = ", ";
	$hrcon_payburdentype_havingcond = "";
	$hrcon_billburdentype_havingcond = "";
	
	if(($statusValue == "active") || ($statusValue == "pending") || ($statusValue == "closed") || ($statusValue == "cancel") || ($statusValue == "ALL"))
	{
		$arrTableNames = array("CompanyName" => "hrcon_jobs.client","FirstName" => "hrcon_general.fname",
		"MiddleName" => "hrcon_general.mname","LastName" => "hrcon_general.lname","PayRate" => "hrcon_jobs.pamount",
		"BillRate" => "hrcon_jobs.bamount","Salary" => "hrcon_jobs.rate","CompenCode" => "hrcon_jobs.wcomp_code",
		"EmployeeId" => "emp_list.sno","SSNNumber" => "REPLACE(hrcon_personal.ssn,'-','')","AssignmentName" => "hrcon_jobs.project",
		"StartDate" => "hrcon_jobs.s_date","SalesAgent" => "hrcon_jobs.owner","Recruiter" => "hrcon_jobs.username","otbrate" => 
		"hrcon_jobs.otbrate_amt","otpayrate" =>"hrcon_jobs.otprate_amt","enddate" =>"hrcon_jobs.e_date","expenddate" =>"hrcon_jobs.exp_edate","federalid" =>"contact_manage.feid","customer" =>"staffacc_cinfo.customerid","dblpayrate" =>"hrcon_jobs.double_prate_amt","dblbrate" => "hrcon_jobs.double_brate_amt","jocreator" =>"users.name","placefee" => "hrcon_jobs.placement_fee","margin" => "hrcon_jobs.margin","assignmentid" => "hrcon_jobs.pusername","paypid" => "IF(TRIM(IFNULL(hrcon_jobs.payrollpid,'')) != '',hrcon_jobs.payrollpid,hrcon_w4.payrollpid)","credate" => "hrcon_jobs.cdate","moduser" => "hrcon_jobs.muser","moddate" => "hrcon_jobs.mdate","assignmenttype"=>"hrcon_jobs.jtype","jobtype"=>"hrcon_jobs.jotype","subhours" => "subhrs","apphours" => "apphrs","burden" => "hrcon_jobs.burden","tstrdate" => "par_timesheet.sdate","tenddate" => "par_timesheet.edate","subdate" => "par_timesheet.stime","aprdate" => "tsa.approvetime","pdlodging"=>"hrcon_jobs.diem_lodging","pdmie"=>"hrcon_jobs.diem_mie","pdtotal"=>"hrcon_jobs.diem_total","pdbillable"=>"hrcon_jobs.diem_billable","pdtaxable"=>"hrcon_jobs.diem_taxable","alternateId"=>"hrcon_jobs.posid","reghours" => "reghrs","overtimehours" => "overtimehrs","doubletimehours" => "doubletimehrs","billablehours" => "billablehrs","reportperson" => "trim(CONCAT_WS(' ',report.fname,report.lname))","contactperson" => "trim(CONCAT_WS(' ',contact.fname,contact.lname))","assign_category" => "hrcon_jobs.catid","assign_refcode" => "hrcon_jobs.refcode","assign_billcontact" => "hrcon_jobs.bill_contact","assign_billaddr" => "hrcon_jobs.bill_address","assign_imethod" => "staffacc_cinfo.inv_method","assign_iterms" => "staffacc_cinfo.inv_terms","assign_pterms" => "hrcon_jobs.pterms","assign_tsapproved" => "hrcon_jobs.tsapp","assign_ponumber" => "hrcon_jobs.po_num","assign_department" => "hrcon_jobs.department","assign_billterms" => "hrcon_jobs.bill_req","assign_sterms" => "hrcon_jobs.service_terms","jlocation" => "hrcon_jobs.endclient","assgnReasonCodes"=>"reason_codes.sno","assgnReason" => "IF(hrcon_jobs.ustatus='cancel',hrcon_jobs.notes_cancel,hrcon_jobs.reason)","workersCompRate" => "rp.rate","HRMDepartment" => "hrcon_jobs.deptid","fedtaxallowance" => "hw.tnum","statetaxallowance"=> "hw.tstatetax","paychkdelmethod"=> "hd.delivery_method","filingstatus"=> "hw.fstatus","withholdingstate"=> "hw.state_withholding","Role"=>"","statetaxwithholdpercentage" => "hw.swh", "markup"=>"hrcon_jobs.markup","empEmailId"=>"emp_list.email","empPrimaryNumber"=>"hrcon_general.wphone","empMobileNumber"=>"hrcon_general.mobile","BillBurden" => "hrcon_jobs.bill_burden","RegularPayRate" => "hrcon_compen.RPayrate","RegularSalary" => "hrcon_compen.RSalary","Regularotpayrate" => "hrcon_compen.over_time","Regulardblpayrate" => "hrcon_compen.double_rate_amt","Regularperdiemrate" => "hrcon_compen.RDiemTotal","PayBurdenType" => "phbd.bt_id","BillBurdenType" => "bhbd.bt_id");
		
		$count_filternames_array = count($filternames_array);
		$string = "";
		for($f=0;$f<$count_filternames_array;$f++)
		{
			
			if(trim($filternames_array[$f]) != "")
			{				
				
				if(!(($filternames_array[$f] == "Status")  || ($filternames_array[$f] == "CompanyName") || 
				($filternames_array[$f] == "SalesAgent") || ($filternames_array[$f] == "moduser") || ($filternames_array[$f] == "Recruiter")||($filternames_array[$f] == "blocation") ||($filternames_array[$f] == "federalid") ||($filternames_array[$f] == "corpcode")  || ($filternames_array[$f] == "margin") || ($filternames_array[$f] == "assign_billcontact") || ($filternames_array[$f] == "assign_billaddr") || ($filternames_array[$f] == "jlocation") || ($filternames_array[$f] == "PayRate") || ($filternames_array[$f] == "BillRate") || ($filternames_array[$f] == "Salary") || ($filternames_array[$f] == "otbrate") || ($filternames_array[$f] == "otpayrate") || ($filternames_array[$f] == "dblpayrate") || ($filternames_array[$f] == "dblbrate") || ($filternames_array[$f] == "markup") || ($filternames_array[$f] == "RegularPayRate") || ($filternames_array[$f] == "RegularSalary") || ($filternames_array[$f] == "Regularotpayrate") || ($filternames_array[$f] == "Regulardblpayrate") || ($filternames_array[$f] == "Regularperdiemrate")))
				{
					
					$colname = getColumnName($filternames_array[$f]);					
					$tablename = $arrTableNames[$filternames_array[$f]];
					
					if(($colname == "customerid" || $colname == "inv_method" || $colname == "inv_terms") && $filtervalues_array[$f] != "")
					{
						$hr_join_cond = " LEFT JOIN staffacc_cinfo ON hrcon_jobs.client = staffacc_cinfo.sno, ";
					}
					if(($colname == "federalid") && $filtervalues_array[$f] != "")
					{
						
					}
					if( !(($filtervalues_array[$f] == "ALL") || (trim($filtervalues_array[$f]) == ''))) 
					{						
						if(($filternames_array[$f] == "StartDate") || ($filternames_array[$f] == "moddate") || ($filternames_array[$f] == "credate") || ($filternames_array[$f] == "PayRate") 
						|| ($filternames_array[$f] == "BillRate") || 
						($filternames_array[$f] == "EmployeeId") || ($filternames_array[$f] == "enddate")|| ($filternames_array[$f] == "expenddate")|| ($filternames_array[$f] == "customer") || ($filternames_array[$f] == "placefee") || ($filternames_array[$f] == "burden") || ($filternames_array[$f] == "subhours") || ($filternames_array[$f] == "apphours") ||  ($filternames_array[$f] == "subdate") || ($filternames_array[$f] == "aprdate") || ($filternames_array[$f] == "tstrdate")  || ($filternames_array[$f] == "tenddate") || ($filternames_array[$f] == "pdlodging") || ($filternames_array[$f] == "pdmie") || ($filternames_array[$f] == "pdtotal") || ($filternames_array[$f] == "pdtotamt") || ($filternames_array[$f] == "alternateId") || ($filternames_array[$f] == "reghours") || ($filternames_array[$f] == "overtimehours") || ($filternames_array[$f] == "doubletimehours") || ($filternames_array[$f] == "billablehours")|| ($filternames_array[$f] == "BillBurden") || ($filternames_array[$f] == "RegularPayRate") || ($filternames_array[$f] == "RegularSalary") || ($filternames_array[$f] == "Regularotpayrate") || ($filternames_array[$f] == "Regulardblpayrate") || ($filternames_array[$f] == "Regularperdiemrate"))
						{
							$ranges = explode("*",$filtervalues_array[$f]);
							$maxvalue = $ranges[0]; 
							$minvalue = $ranges[1];
							
							if(($filternames_array[$f] == "StartDate") || ($filternames_array[$f] == "enddate")) 
							{
								$fdate = ($minvalue) ? date("Y-m-d",strtotime($minvalue)) : "";
								$tdate = ($maxvalue) ? date("Y-m-d",strtotime($maxvalue)) : "";
								$strDate = tzRetQueryStringSTRTODate("$tablename","%m-%d-%Y","YMDDate","-");	  
								if($fdate != "" && $tdate != "")
								   $string=" ( $strDate >='".$fdate."' and $strDate <='".$tdate."' ) and ";		
								elseif($tdate != "" && $fdate == "")
									$string = " $strDate <= '".$tdate."' and ";
								elseif($fdate != "" && $tdate == "")
									$string = "  $strDate >= '".$fdate."' and ";						
							}
							else if($filternames_array[$f] == "expenddate" || $filternames_array[$f] == "moddate" || $filternames_array[$f] == "credate")
							{
								$exp_fromdate = ($minvalue) ? date("Y-m-d",strtotime($minvalue)) : "";
								$exp_todate = ($maxvalue) ? date("Y-m-d",strtotime($maxvalue)) : "";
								$expdate=tzRetQueryStringDTime("$tablename","YMDDate","-");
								
								if($exp_fromdate != "" && $exp_todate != "")
								   $string= "  $expdate >='".$exp_fromdate."' and  $expdate <='".$exp_todate."' and ";		
								elseif($exp_todate != "" && $exp_fromdate == "")
									$string = " $expdate <= '".$exp_todate."' and ";
								elseif($exp_fromdate != "" && $exp_todate == "")
									$string = " $expdate >= '".$exp_fromdate."' and ";
							}
							else if($filternames_array[$f] == "tstrdate" || $filternames_array[$f] == "tenddate"  || $filternames_array[$f] == "subdate")
							{
								$exp_fromdate = ($minvalue) ? date("Y-m-d",strtotime($minvalue)) : "";
								$exp_todate = ($maxvalue) ? date("Y-m-d",strtotime($maxvalue)) : "";
								
								if($filternames_array[$f] == "subdate")
									$expdate=tzRetQueryStringDTime("$tablename","YMDDate","-");
								else
									$expdate=tzRetQueryStringDate("$tablename","YMDDate","-");
									
								if($exp_fromdate != "" && $exp_todate != "")
								   $timevalues= "AND $expdate >='".$exp_fromdate."' AND  $expdate <='".$exp_todate."' ";		
								elseif($exp_todate != "" && $exp_fromdate == "")
									$timevalues = "AND $expdate <= '".$exp_todate."' ";
								elseif($exp_fromdate != "" && $exp_todate == "")
									$timevalues = "AND $expdate >= '".$exp_fromdate."' ";
								
								if($minvalue != "" || $maxvalue == "")
									$timeFilter = "yes";	
							}
							else if($filternames_array[$f] == "subhours" || $filternames_array[$f] == "pdtotamt" || $filternames_array[$f] == "apphours" || $filternames_array[$f] == "reghours" || $filternames_array[$f] == "overtimehours" || $filternames_array[$f] == "doubletimehours" || $filternames_array[$f] == "billablehours")
							{
								$string = "";
								if($minvalue != "" || $maxvalue == "")
									$timeFilter = "yes";
							}
							else if($filternames_array[$f] == "aprdate")
							{
								
								$exp_fromdate = ($minvalue) ? date("Y-m-d",strtotime($minvalue)) : "";
								$exp_todate = ($maxvalue) ? date("Y-m-d",strtotime($maxvalue)) : "";
								
								$approveddatefilter = "";
								$expdate=tzRetQueryStringDTime("$tablename","YMDDate","-");
									
								if($exp_fromdate != "" && $exp_todate != "")
								   $approveddatefilter= "AND $expdate >='".$exp_fromdate."' AND  $expdate <='".$exp_todate."' ";		
								elseif($exp_todate != "" && $exp_fromdate == "")
									$approveddatefilter = "AND $expdate <= '".$exp_todate."' ";
								elseif($exp_fromdate != "" && $exp_todate == "")
									$approveddatefilter = "AND $expdate >= '".$exp_fromdate."' ";
								
								if($minvalue != "" || $maxvalue == "")
									$timeFilter = "yes";
							}
							//this filter condition for alternative id...
							else if($filternames_array[$f] == "alternateId")
							{
								if($maxvalue != "" && $minvalue != "")
									$string = " ({$tablename} >= ".$minvalue." and  {$tablename} <= ".$maxvalue." ) and";
								elseif($maxvalue != "" && $minvalue == "")
									$string = " ({$tablename} <= ".$maxvalue." ) and";
								elseif($minvalue != "" && $maxvalue == "")
									$string = " ({$tablename} >= ".$minvalue." ) and";
							}
							else
							{   
							if($maxvalue != "" && $minvalue != "")
							  $string = " ({$tablename} >= ".$minvalue." and  {$tablename} <= ".$maxvalue."  and {$tablename} != 0) and";
							elseif($maxvalue != "" && $minvalue == "")
							  $string = " ({$tablename} <= ".$maxvalue." ) and";
							elseif($minvalue != "" && $maxvalue == "")
							  $string = " ({$tablename} >= ".$minvalue." ) and";
							}
						}
						else if($filternames_array[$f] == "assign_category" || $filternames_array[$f] == "CompenCode" || $filternames_array[$f] == "assign_billterms" || $filternames_array[$f] == "assign_pterms")
						{
							$string = " {$tablename} ='{$filtervalues_array[$f]}' AND ";
						}
						else if($filternames_array[$f] == "workersCompRate")
						{
							
						}else if($filternames_array[$f] == 'empEmailId'){
							 $string = " {$tablename} like '%{$filtervalues_array[$f]}%' AND ";
						}
						else if($filternames_array[$f] == 'empPrimaryNumber'){
							 $string = " {$tablename} like '%{$filtervalues_array[$f]}%' AND ";
						}
						else if($filternames_array[$f] == 'empMobileNumber'){
							 $string = " {$tablename} like '%{$filtervalues_array[$f]}%' AND ";
						}
						else if($filternames_array[$f] == "HRMDepartment")
						{
							$string = " {$tablename} ='{$filtervalues_array[$f]}' AND ";
						}
						else if($filternames_array[$f] == "PayBurdenType")
						{
							if($filtervalues_array[$f] != 'Old') 
							{
								$string = " {$tablename} ='{$filtervalues_array[$f]}' AND ";
							}
							else 
							{
								$string = " (pbt.`burden_type_name` = '' OR pbt.`burden_type_name` IS NULL) AND mng.name != 'Internal Direct' AND mng.name != 'Direct' AND ";
							}
						}
						else if($filternames_array[$f] == "BillBurdenType")
						{
							if($filtervalues_array[$f] != 'Old')
							{
								$string = " {$tablename} ='{$filtervalues_array[$f]}' AND ";
							}
							else 
							{
								$string = " (bbt.`burden_type_name` = '' OR bbt.`burden_type_name` IS NULL) AND mng.name != 'Internal Direct' AND mng.name != 'Direct' AND ";
							}
						}
						else if($filternames_array[$f] == "assign_imethod" || $filternames_array[$f] == "assign_iterms")
						{
							if($filtervalues_array[$f] !="ALL")
							{
								$string = " {$tablename} ='{$filtervalues_array[$f]}' AND ";
							}
						}
						else if($filternames_array[$f] == "commempname" || $filternames_array[$f] == "commamount" || $filternames_array[$f] == "commsource" || $filternames_array[$f] == "Role")
						{
							$string = "";
						}
						else if($filternames_array[$f] == "industry" )
						{
							$string = "";
						}
						else if($filternames_array[$f] == "assgnReason" )
						{
							$string = " {$tablename} like '%{$filtervalues_array[$f]}%' AND  ";
						}
						else if($filternames_array[$f] == "assgnReasonCodes" )
						{
							$string = " {$tablename} ='{$filtervalues_array[$f]}' AND  ";
						}
						else
							$string = " {$tablename} like '%{$filtervalues_array[$f]}%' and ";
					   
						$hrcon_cond_filter .= " {$string} ";
						$timeFilterVal .= " {$timevalues} ";
						$subHrsVal = " {$subhrsval} ";
						$appHrsVal = " {$apphrsval} ";
						$appDateVal = " {$approveddatefilter} ";
					}
				} 
				
				
			}
		}
		$hrconstr = $hrcon_cond_filter;
	}
	

	$fieldnames  =  array("CompanyName","FirstName","MiddleName","LastName","PayRate","BillRate","Salary","CompenCode","EmployeeId","SSNNumber","AssignmentName","StartDate","Recruiter","SalesAgent","Status","otbrate","blocation","otpayrate","enddate","expenddate","federalid","customer","dblpayrate","dblbrate","jocreator","commempname","commamount","commsource","placefee","margin","assignmentid","paypid","credate","moduser","moddate","assignmenttype","jobtype","subhours","apphours","burden","subdate","aprdate","tstrdate","tenddate","corpcode","pdlodging","pdmie","pdtotal","pdbillable","pdtaxable","pdtotamt","alternateId","reghours","overtimehours","doubletimehours","billablehours","reportperson","contactperson","assign_category","assign_refcode","assign_billcontact","assign_billaddr","assign_imethod","assign_iterms","assign_pterms","assign_tsapproved","assign_ponumber","assign_department","assign_billterms","assign_sterms","jlocation","assgnReasonCodes","assgnReason","workersCompRate","HRMDepartment","fedtaxallowance","statetaxallowance","paychkdelmethod","filingstatus","withholdingstate","Role","statetaxwithholdpercentage", "markup",'empEmailId','empPrimaryNumber','empMobileNumber','BillBurden',"RegularPayRate","RegularSalary","Regularotpayrate","Regulardblpayrate","Regularperdiemrate","PayBurdenType","BillBurdenType","industry");	

	$variablenames = array("varCompanyName","varFirstName","varMiddleName","varLastName","varPayRate","varBillRate",
	"varSalary","varCompenCode","varEmployeeId","varSSN","varAssgName","varStartDate","varRecruiter","varSalesAgent","varStatus","otbrate","blocation","otpayrate","enddate","expenddate","federalid","customer","dblpayrate","dblbrate","jocreator","commempname","commamount","commsource","placefee","margin","assignmentid","paypid","credate","moduser","moddate","assignmenttype","jobtype","subhours","apphours","burden","subdate","aprdate","tstrdate","tenddate","corpcode","pdlodging","pdmie","pdtotal","pdbillable","pdtaxable","pdtotamt","alternateId","reghours","overtimehours","doubletimehours","billablehours","reportperson","contactperson","assign_category","assign_refcode","assign_billcontact","assign_billaddr","assign_imethod","assign_iterms","assign_pterms","assign_tsapproved","assign_ponumber","assign_department","assign_billterms","assign_sterms","jlocation","assgnReasonCodes","assgnReason","workersCompRate","HRMDepartment","fedtaxallowance","statetaxallowance","paychkdelmethod","filingstatus","withholdingstate","Role","statetaxwithholdpercentage", "markup",'empEmailId','empPrimaryNumber','empMobileNumber','BillBurden',"RegularPayRate","RegularSalary","Regularotpayrate","Regulardblpayrate","Regularperdiemrate","PayBurdenType","BillBurdenType","industry");
   
	//This part of code is for assigning the values to data variables.
	
	$k=0;
		
	$count_fieldnames = count($fieldnames);	

    for($q=0 ; $q< $count_sortarr ; $q++)
    {
		
		for($f=0 ; $f<$count_fieldnames ; $f++)
		{
					
			if($sortarr[$q] == $fieldnames[$f])
			{
				$variable = $$variablenames[$f];
				if($variable[0]!="")
				{ 
					$data[0][$k] = $variable[0];
					$headval[0][$k] = $variable[1];
					$headval[1][$k] = $variable[2];
					$k++;
				}
			}
		}
	}
			
    if($k!=0)
    {
        $data[0][$k]="link";
        $k++;
        $data[0][$k]="link_length";
    }
	
	//Arrays for holding Billable and Taxable Types
	$arrayBillableType = array('Y' => 'Billable','N' =>'Non-Billable');
	$arrayTaxableType = array('Y' => 'Taxable','N' =>'Non-Taxable');
	//Ends here
	$columnCheck = 0;
	$newColumns = array("subhours","apphours","subdate","aprdate","tstrdate","tenddate","reghours","overtimehours","doubletimehours","billablehours");
	$newColumnsCount = count($newColumns);
	for($ext = 0;$ext<$newColumnsCount;$ext++)
	{
		if(in_array($newColumns[$ext],$sortarr) && ($rdata[57] != "" || $rdata[58] != "" || $rdata[60] != "" || ($rdata[61] != "" || $rptFrom == "3") || $rdata[62] != "" || $rdata[63] != ""  || $rdata[72] != "" || $rdata[73] != "" || $rdata[74] != "" || $rdata[75] != ""))
		{
			$columnCheck = $columnCheck+1;
		}
	}
	$commissionCheck = 0;
	$commColumns = array("commempname","commamount","commsource","Role");
	$commColumnsCount = count($commColumns);
	for($ext = 0;$ext<$commColumnsCount;$ext++)
	{
		if(in_array($commColumns[$ext],$sortarr))
			$commissionCheck = $commissionCheck+1;
	}
	$i=1;
	
	$hrcon_payburdentype = "if((mng.name = 'Internal Direct' OR mng.name = 'Direct'),'',if((pbt.`burden_type_name` = '' OR pbt.`burden_type_name` IS NULL),CONCAT('Older Burden(',hrcon_jobs.burden,'%)'),CONCAT(pbt.`burden_type_name`,'(',GROUP_CONCAT(DISTINCT CONCAT(pbi.burden_item_name,'-',pbi.burden_value,IF(pbi.burden_mode='percentage','%','')) SEPARATOR ' + '),')')))";

	$hrcon_billburdentype = "if((mng.name = 'Internal Direct' OR mng.name = 'Direct'),'',if((bbt.`burden_type_name` = '' OR bbt.`burden_type_name` IS NULL),CONCAT('Older Burden(',hrcon_jobs.bill_burden,'%)'),CONCAT(bbt.burden_type_name,'(',GROUP_CONCAT(DISTINCT CONCAT(bbi.burden_item_name,'-',bbi.burden_value,IF(bbi.burden_mode='percentage','%','')) SEPARATOR ' + '),')')))";
	
	$empcon_payburdentype = "if((mng.name = 'Internal Direct' OR mng.name = 'Direct'),'',if((pbt.`burden_type_name` = '' OR pbt.`burden_type_name` IS NULL),CONCAT('Older Burden(',empcon_jobs.burden,'%)'),CONCAT(pbt.`burden_type_name`,'(',GROUP_CONCAT(DISTINCT CONCAT(pbi.burden_item_name,'-',pbi.burden_value,IF(pbi.burden_mode='percentage','%','')) SEPARATOR ' + '),')')))";
	
	$empcon_billburdentype = "if((mng.name = 'Internal Direct' OR mng.name = 'Direct'),'',if((bbt.`burden_type_name` = '' OR bbt.`burden_type_name` IS NULL),CONCAT('Older Burden(',empcon_jobs.bill_burden,'%)'),CONCAT(bbt.burden_type_name,'(',GROUP_CONCAT(DISTINCT CONCAT(bbi.burden_item_name,'-',bbi.burden_value,IF(bbi.burden_mode='percentage','%','')) SEPARATOR ' + '),')')))";
	
	if(($tab=="addr" || ($view=="myreport" && $vrow[0]!="")))
	{
		if($statusValue == "active" ||  $statusValue == "pending" || $statusValue == "closed" ||  $statusValue == "cancel" || $statusValue == "ALL")
		{
			if($statusValue != "ALL" && $statusValue != "")
				$hrconStatusCond= " AND hrcon_jobs.ustatus='".$statusValue."'";
			else
				$hrconStatusCond= " AND hrcon_jobs.ustatus IN ('active','pending','closed','cancel')";
			
		
			$qryHrCon = "SELECT emp_list.sno  AS empsno, hrcon_jobs.client, hrcon_general.fname,hrcon_general.mname, hrcon_general.lname, hrcon_jobs.pamount, hrcon_jobs.pperiod, hrcon_jobs.pcurrency, hrcon_jobs.bamount,hrcon_jobs.bperiod, hrcon_jobs.bcurrency, hrcon_jobs.rate,CONCAT_WS('-',wcomp.code,wcomp.title,wcomp.state) wcomp_code, hrcon_compen.emp_id, REPLACE(hrcon_personal.ssn,'-','') AS ssn, hrcon_jobs.project, ".tzRetQueryStringDate("STR_TO_DATE(if(hrcon_jobs.s_date='0-0-0','00-00-0000',hrcon_jobs.s_date),'%m-%d-%Y')","Date","-")." AS s_date, hrcon_jobs.owner,hrcon_jobs.ustatus,hrcon_jobs.sno  AS hrcon_sno,hrcon_jobs.rateperiod,hrcon_jobs.vendor,hrcon_jobs.otrate AS otrate, hrcon_compen.location, hrcon_jobs.otbrate_amt  AS otbrate,hrcon_jobs.otbrate_period  AS otbperiiod, hrcon_jobs.otbrate_curr  AS otbcurrency, hrcon_jobs.otprate_amt  AS otprate, hrcon_jobs.otprate_period  AS otpperiiod, hrcon_jobs.otprate_curr  AS otpcurrency,hrcon_jobs.rateper  AS ratecurrency, ".tzRetQueryStringDate("STR_TO_DATE(if(hrcon_jobs.e_date='0-0-0','00-00-0000',hrcon_jobs.e_date),'%m-%d-%Y')","Date","-")." AS e_date, DATE_FORMAT(DATE(hrcon_jobs.exp_edate),'%m/%d/%Y')AS exp_enddate, hrcon_jobs.double_prate_amt  AS dtprate, hrcon_jobs.
			double_prate_period  AS dtpperiiod, hrcon_jobs.double_prate_curr  AS dtpcurrency, hrcon_jobs.double_brate_amt  AS dtbrate, hrcon_jobs.double_brate_period  AS dtbperiiod, hrcon_jobs.double_brate_curr  AS dtbcurrency, users.name  AS jobordercreator, hrcon_jobs.placement_fee,hrcon_jobs.placement_curr, hrcon_jobs.margin, concat_ws('|',hrcon_jobs.pamount,hrcon_jobs.bamount,hrcon_jobs.burden,hrcon_jobs.margin,hrcon_jobs.bill_burden) AS mar, hrcon_jobs.pusername  AS idassignment, IF(TRIM(IFNULL(hrcon_jobs.payrollpid,'')) != '',hrcon_jobs.payrollpid,hrcon_w4.payrollpid) AS payrollpid, ".tzRetQueryStringDTime("hrcon_jobs.cdate","Date","/")." AS cdate, hrcon_jobs.muser, ".tzRetQueryStringDTime("hrcon_jobs.mdate","Date","/")." AS mdate,hrcon_jobs.jtype,hrcon_jobs.jotype,if(hrcon_jobs.burden != '0.00',hrcon_jobs.burden,'') burden,hrcon_jobs.username username,hrcon_jobs.diem_lodging,hrcon_jobs.diem_mie,hrcon_jobs.diem_total,hrcon_jobs.diem_billable,hrcon_jobs.diem_taxable,hrcon_jobs.diem_currency,hrcon_jobs.diem_period,hrcon_jobs.posid,concat_ws(' ',contact.fname,contact.lname) contact, concat_ws(' ',report.fname,report.lname) reportto,hrcon_jobs.catid catid,hrcon_jobs.refcode refcode,hrcon_jobs.bill_contact bill_contact,hrcon_jobs.bill_address bill_address,'','',payterms.billpay_code pterms,hrcon_jobs.tsapp tsapp,hrcon_jobs.po_num po_num,hrcon_jobs.department department,bpterms.billpay_code bill_req,hrcon_jobs.service_terms service_terms,hrcon_jobs.endclient,IF(hrcon_jobs.ustatus='cancel',hrcon_jobs.notes_cancel,hrcon_jobs.reason) notes_cancel,reason_codes.reason AS reasons,wcomp.sno AS wcompSno,dept.deptname AS HRMDepartment,IF(hrcon_w4.federal_exempt='Y','Exempt',IF(hrcon_w4.tnum=0.00 ||hrcon_w4.tnum=0,'',hrcon_w4.tnum)) as fedTaxAllowance,IF(hrcon_w4.state_exempt='Y','Exempt',IF(hrcon_w4.tstatetax=0.00 ||hrcon_w4.tstatetax=0,'',hrcon_w4.tstatetax)) as stateTaxAllowance, hd.delivery_method as payCheckDelMethod ,hrcon_w4.state_withholding as withholdingState, hrcon_w4.fstatus as filingStatus, hrcon_w4.swh as statetaxwithholdpercentage, hrcon_jobs.markup,emp_list.email AS empEmailId, hrcon_general.wphone AS empPrimaryNumber, hrcon_general.mobile AS empMobileNumber, hrcon_general.wphone_extn AS empPrimaryNumberExt,hrcon_jobs.corp_code AS corpcodesno,if(hrcon_jobs.bill_burden != '0.00',hrcon_jobs.bill_burden,'') bill_burden, IF((manage.name='Temp/Contract' || manage.name='Internal Temp/Contract'),hrcon_compen.salary,'0.00') as RPayrate, IF((manage.name='Internal Direct' || hrcon_compen.emptype=0),hrcon_compen.salary,'0.00') as RSalary, hrcon_compen.over_time, hrcon_compen.double_rate_amt, hrcon_compen.diem_total as RDiemTotal, hrcon_compen.diem_currency as RDiemCurrency, hrcon_compen.diem_period as RDiemPeriod, ".$hrcon_payburdentype." as PBType, ".$hrcon_billburdentype." as BBType ,mg.name AS industry
			FROM hrcon_jobs 
			LEFT JOIN manage mng ON (hrcon_jobs.jotype = mng.sno) 
			LEFT JOIN workerscomp wcomp ON(wcomp.workerscompid = hrcon_jobs.wcomp_code AND wcomp.status = 'active')
			LEFT JOIN bill_pay_terms bpterms ON(bpterms.billpay_termsid = hrcon_jobs.bill_req AND bpterms.billpay_type = 'BT' AND bpterms.billpay_status = 'active')
			LEFT JOIN bill_pay_terms payterms ON (payterms.billpay_termsid = hrcon_jobs.pterms AND payterms.billpay_type = 'PT' AND payterms.billpay_status = 'active')
			LEFT JOIN department dept ON (hrcon_jobs.deptid = dept.sno)
			LEFT JOIN manage mg ON(hrcon_jobs.industryid = mg.sno  AND mg.type ='joindustry')  
			LEFT JOIN hrcon_burden_details as phbd ON (phbd.hrcon_jobs_sno = hrcon_jobs.sno AND phbd.ratetype = 'payrate')
			LEFT JOIN burden_types as pbt ON (pbt.sno = phbd.bt_id)
			LEFT JOIN burden_items as pbi ON (pbi.sno = phbd.bi_id)
			LEFT JOIN hrcon_burden_details as bhbd ON (bhbd.hrcon_jobs_sno = hrcon_jobs.sno AND bhbd.ratetype = 'billrate')
			LEFT JOIN burden_types as bbt ON (bbt.sno = bhbd.bt_id)
			LEFT JOIN burden_items as bbi ON (bbi.sno = bhbd.bi_id)
			LEFT JOIN staffacc_contact contact ON (hrcon_jobs.contact=contact.sno) LEFT JOIN staffacc_contact report ON (hrcon_jobs.manager=report.sno)
			LEFT JOIN posdesc ON posdesc.posid = hrcon_jobs.posid
			LEFT JOIN reason_codes ON (hrcon_jobs.reason_id = reason_codes.sno AND reason_codes.type IN('assigncancelcode','assignclosecode') AND reason_codes.status='Active')
			LEFT JOIN users ON users.username = posdesc.owner".$hr_join_cond."
			emp_list
			LEFT JOIN hrcon_w4 ON (emp_list.username = hrcon_w4.username)
			LEFT JOIN hrcon_general ON (emp_list.username = hrcon_general.username AND hrcon_general.ustatus = 'active')
			LEFT JOIN hrcon_compen ON (emp_list.username = hrcon_compen.username AND hrcon_compen.ustatus = 'active')
			LEFT JOIN manage ON (hrcon_compen.emptype = manage.sno) 
			LEFT JOIN hrcon_personal ON (emp_list.username = hrcon_personal.username AND hrcon_personal.ustatus = 'active')
			LEFT JOIN hrcon_deposit  hd ON ( emp_list.username =hd.username and hd.ustatus = 'active'  )
			WHERE ".$hrconstr." hrcon_jobs.jtype != ''
			AND emp_list.username = hrcon_jobs.username
                                                                  AND dept.sno !='0' AND dept.sno IN ({$deptAccesSno})
			AND emp_list.lstatus != 'DA'
			AND hrcon_jobs.jotype != '0'".$hrconStatusCond."
			AND hrcon_w4.ustatus = 'active'
			".$whr_industry."
			
			GROUP BY hrcon_jobs.sno";
		}
		
	}		 
	else
	{
		if($statusValue != "ALL" && $statusValue != "")
			$hrconStatusCond= " AND hrcon_jobs.ustatus='$statusValue'";
		else
			$hrconStatusCond= " AND hrcon_jobs.ustatus IN ('active','pending','closed','cancel')";
			
	$qryHrCon = "SELECT emp_list.sno  AS empsno, hrcon_jobs.client, hrcon_general.fname,hrcon_general.mname, hrcon_general.lname, hrcon_jobs.pamount, hrcon_jobs.pperiod, hrcon_jobs.pcurrency, hrcon_jobs.bamount,hrcon_jobs.bperiod, hrcon_jobs.bcurrency, hrcon_jobs.rate,CONCAT_WS('-',wcomp.code,wcomp.title,wcomp.state) wcomp_code, hrcon_compen.emp_id, REPLACE(hrcon_personal.ssn,'-','') AS ssn, hrcon_jobs.project, ".tzRetQueryStringDate("STR_TO_DATE(if(hrcon_jobs.s_date='0-0-0','00-00-0000',hrcon_jobs.s_date),'%m-%d-%Y')","Date","-")." AS s_date, hrcon_jobs.owner,hrcon_jobs.ustatus,hrcon_jobs.sno  AS hrcon_sno,hrcon_jobs.rateperiod,hrcon_jobs.vendor,hrcon_jobs.otrate AS otrate, hrcon_compen.location, hrcon_jobs.otbrate_amt  AS otbrate,hrcon_jobs.otbrate_period  AS otbperiiod, hrcon_jobs.otbrate_curr  AS otbcurrency, hrcon_jobs.otprate_amt  AS otprate, hrcon_jobs.otprate_period  AS otpperiiod, hrcon_jobs.otprate_curr  AS otpcurrency,hrcon_jobs.rateper  AS ratecurrency, ".tzRetQueryStringDate("STR_TO_DATE(if(hrcon_jobs.e_date='0-0-0','00-00-0000',hrcon_jobs.e_date),'%m-%d-%Y')","Date","-")." AS e_date, DATE_FORMAT(DATE(hrcon_jobs.exp_edate),'%m/%d/%Y')AS exp_enddate, hrcon_jobs.double_prate_amt  AS dtprate, hrcon_jobs.
		double_prate_period  AS dtpperiiod, hrcon_jobs.double_prate_curr  AS dtpcurrency, hrcon_jobs.double_brate_amt  AS dtbrate, hrcon_jobs.double_brate_period  AS dtbperiiod, hrcon_jobs.double_brate_curr  AS dtbcurrency, users.name  AS jobordercreator, hrcon_jobs.placement_fee,hrcon_jobs.placement_curr, hrcon_jobs.margin, concat_ws('|',hrcon_jobs.pamount,hrcon_jobs.bamount, hrcon_jobs.burden,hrcon_jobs.margin,hrcon_jobs.bill_burden) AS mar, hrcon_jobs.pusername  AS idassignment, IF(TRIM(IFNULL(hrcon_jobs.payrollpid,'')) != '',hrcon_jobs.payrollpid,hrcon_w4.payrollpid) AS payrollpid, ".tzRetQueryStringDTime("hrcon_jobs.cdate","Date","/")." AS cdate, hrcon_jobs.muser, ".tzRetQueryStringDTime("hrcon_jobs.mdate","Date","/")." AS mdate,hrcon_jobs.jtype,hrcon_jobs.jotype,if(hrcon_jobs.burden != '0.00',hrcon_jobs.burden,'') burden,hrcon_jobs.username username,hrcon_jobs.diem_lodging,hrcon_jobs.diem_mie,hrcon_jobs.diem_total,hrcon_jobs.diem_billable,hrcon_jobs.diem_taxable,hrcon_jobs.diem_currency,hrcon_jobs.diem_period,hrcon_jobs.posid,concat_ws(' ',contact.fname,contact.lname) contact, concat_ws(' ',report.fname,report.lname) reportto,hrcon_jobs.catid catid,hrcon_jobs.refcode refcode,hrcon_jobs.bill_contact bill_contact,hrcon_jobs.bill_address bill_address,'','',payterms.billpay_code pterms,hrcon_jobs.tsapp tsapp,hrcon_jobs.po_num po_num,hrcon_jobs.department department,bpterms.billpay_code bill_req,hrcon_jobs.service_terms service_terms,hrcon_jobs.endclient,IF(hrcon_jobs.ustatus='cancel',hrcon_jobs.notes_cancel,hrcon_jobs.reason) notes_cancel,reason_codes.reason AS reasons,wcomp.sno AS wcompSno,dept.deptname AS HRMDepartment,IF(hrcon_w4.federal_exempt='Y','Exempt',IF(hrcon_w4.tnum=0.00 ||hrcon_w4.tnum=0,'',hrcon_w4.tnum)) as fedTaxAllowance,IF(hrcon_w4.state_exempt='Y','Exempt',IF(hrcon_w4.tstatetax=0.00 ||hrcon_w4.tstatetax=0,'',hrcon_w4.tstatetax)) as stateTaxAllowance, hd.delivery_method as payCheckDelMethod ,hrcon_w4.state_withholding as withholdingState, hrcon_w4.fstatus as filingStatus ,hrcon_w4.swh as statetaxwithholdpercentage, hrcon_jobs.markup,emp_list.email AS empEmailId, hrcon_general.wphone AS empPrimaryNumber, hrcon_general.mobile AS empMobileNumber, hrcon_general.wphone_extn AS empPrimaryNumberExt,hrcon_jobs.corp_code AS corpcodesno,if(hrcon_jobs.bill_burden != '0.00',hrcon_jobs.bill_burden,'') bill_burden, IF((manage.name='Temp/Contract' || manage.name='Internal Temp/Contract'),hrcon_compen.salary,'0.00') as RPayrate, IF((manage.name='Internal Direct' || hrcon_compen.emptype=0),hrcon_compen.salary,'0.00') as RSalary, hrcon_compen.over_time, hrcon_compen.double_rate_amt, hrcon_compen.diem_total as RDiemTotal, hrcon_compen.diem_currency as RDiemCurrency, hrcon_compen.diem_period as RDiemPeriod, ".$hrcon_payburdentype." as PBType, ".$hrcon_billburdentype." as BBType , mg.name AS industry
		FROM hrcon_jobs 
		LEFT JOIN manage mng ON (hrcon_jobs.jotype = mng.sno) 
		LEFT JOIN workerscomp wcomp ON(wcomp.workerscompid = hrcon_jobs.wcomp_code AND wcomp.status = 'active')
		LEFT JOIN bill_pay_terms bpterms ON(bpterms.billpay_termsid = hrcon_jobs.bill_req AND bpterms.billpay_type = 'BT' AND bpterms.billpay_status = 'active')
		LEFT JOIN bill_pay_terms payterms ON (payterms.billpay_termsid = hrcon_jobs.pterms AND payterms.billpay_type = 'PT' AND payterms.billpay_status = 'active') 
		LEFT JOIN department dept ON (hrcon_jobs.deptid = dept.sno)
		LEFT JOIN manage mg ON(hrcon_jobs.industryid = mg.sno  AND mg.type ='joindustry') 
		LEFT JOIN hrcon_burden_details as phbd ON (phbd.hrcon_jobs_sno = hrcon_jobs.sno AND phbd.ratetype = 'payrate')
		LEFT JOIN burden_types as pbt ON (pbt.sno = phbd.bt_id)
		LEFT JOIN burden_items as pbi ON (pbi.sno = phbd.bi_id)
		LEFT JOIN hrcon_burden_details as bhbd ON (bhbd.hrcon_jobs_sno = hrcon_jobs.sno AND bhbd.ratetype = 'billrate')
		LEFT JOIN burden_types as bbt ON (bbt.sno = bhbd.bt_id)
		LEFT JOIN burden_items as bbi ON (bbi.sno = bhbd.bi_id)
		LEFT JOIN staffacc_contact contact ON (hrcon_jobs.contact=contact.sno) LEFT JOIN staffacc_contact report ON (hrcon_jobs.manager=report.sno)
		LEFT JOIN posdesc ON posdesc.posid = hrcon_jobs.posid
		LEFT JOIN reason_codes ON (hrcon_jobs.reason_id = reason_codes.sno AND reason_codes.type IN('assigncancelcode','assignclosecode') AND reason_codes.status='Active')
		LEFT JOIN users ON users.username = posdesc.owner,
		emp_list
		LEFT JOIN hrcon_w4 ON (emp_list.username = hrcon_w4.username)
		LEFT JOIN hrcon_general ON (emp_list.username = hrcon_general.username AND hrcon_general.ustatus = 'active')
		LEFT JOIN hrcon_compen ON (emp_list.username = hrcon_compen.username AND hrcon_compen.ustatus = 'active')
		LEFT JOIN manage ON hrcon_compen.emptype = manage.sno
		LEFT JOIN hrcon_personal ON (emp_list.username = hrcon_personal.username AND hrcon_personal.ustatus = 'active')
		LEFT JOIN hrcon_deposit  hd ON ( emp_list.username =hd.username and hd.ustatus = 'active'  )
		WHERE ".$hrconstr." emp_list.username = hrcon_jobs.username
		AND hrcon_jobs.jtype != ''
		AND emp_list.lstatus != 'DA'
                                            AND dept.sno !='0' AND dept.sno IN ({$deptAccesSno})
		".$hrconStatusCond."
		AND hrcon_jobs.jotype != '0'
		AND hrcon_w4.ustatus = 'active'
		".$whr_industry."
		
		GROUP BY hrcon_jobs.sno";
        }
	
	//Get the job types from manage table and put it in an array
	$arrManage = array();
	$manageSql="select sno,name from manage where type='jotype'";
	$manageResult=mysql_query($manageSql,$db);
	while($rowManage = mysql_fetch_row($manageResult))
	{
		$arrManage[$rowManage[0]] = $rowManage[1];
	}

	$rsHrCon = mysql_query($qryHrCon,$db);
	
        
	$condHrCon = "1";
	$indexCompanyName = array_search("CompanyName",$filternames_array);
	$indexSalesAgent = array_search("SalesAgent",$filternames_array);
	$indexRecruiter = array_search("Recruiter",$filternames_array);
	$indexBlocation = array_search("blocation",$filternames_array);
	$indexFederalid = array_search("federalid",$filternames_array);
	$indexCommEmpName = array_search("commempname",$filternames_array);
	$indexCommAmount = array_search("commamount",$filternames_array);
	$indexCommSource = array_search("commsource",$filternames_array);
	$indexmuser = array_search("moduser",$filternames_array);
	$indexCorpCode = array_search("corpcode",$filternames_array);
	$indexDiemBillableType = array_search("pdbillable",$filternames_array);
	$indexDiemTaxableType = array_search("pdtaxable",$filternames_array);
	$indexmargin = array_search("margin",$filternames_array);
	$indexBillContact = array_search("assign_billcontact",$filternames_array);
	$indexBillAddr = array_search("assign_billaddr",$filternames_array);
	$indexJlocation = array_search("jlocation",$filternames_array);
	$indexRole = array_search("Role",$filternames_array);
	$indexMarkup = array_search("markup",$filternames_array);
	
	$indexrate = array_search("Salary",$filternames_array);
	$indexpamount = array_search("PayRate",$filternames_array);
	$indexbamount = array_search("BillRate",$filternames_array);
	$indexotprate = array_search("otpayrate",$filternames_array);
	$indexotbrate = array_search("otbrate",$filternames_array);
	$indexdtprate = array_search("dblpayrate",$filternames_array);
	$indexdtbrate = array_search("dblbrate",$filternames_array);
	
	$indexrpayrate = array_search("RegularPayRate",$filternames_array);
	$indexrsalary = array_search("RegularSalary",$filternames_array);
	$indexrotrate = array_search("Regularotpayrate",$filternames_array);
	$indexrdtrate = array_search("Regulardblpayrate",$filternames_array);
	$indexrpdiemtotal = array_search("Regularperdiemrate",$filternames_array);
	
	$arrCompIds = ($indexCompanyName === false) ? '0' : getStringFilterIds("CompanyName",$filtervalues_array[$indexCompanyName]);
	$arrSalesIds = ($indexSalesAgent === false) ? '0' : getStringFilterIds("SalesAgent",$filtervalues_array[$indexSalesAgent]);
	$arrRecIds = ($indexRecruiter === false) ? '0' : getStringFilterIds("Recruiter",$filtervalues_array[$indexRecruiter]);
	$arrBlocIds = ($indexBlocation === false) ? '0' : getStringFilterIds("blocation",$filtervalues_array[$indexBlocation]);
	$arrFederalIds = ($indexFederalid === false) ? '0' : getStringFilterIds("federalid",$filtervalues_array[$indexFederalid]);
	$arrCommEmpIds = ($indexCommEmpName === false) ? '0' : getStringFilterIds("commempname",$filtervalues_array[$indexCommEmpName]);
	$arrmuser = ($indexmuser === false) ? '0' : getStringFilterIds("moduser",$filtervalues_array[$indexmuser]);
	$arrcorpcodeIds = ($indexCorpCode === false) ? '0' : getStringFilterIds("corpcode",$filtervalues_array[$indexCorpCode]);
	$indexmarginFilterVal = $filtervalues_array[$indexmargin];
	$arrBillContactIds = ($indexBillContact === false) ? '0' : getStringFilterIds("assign_billcontact",$filtervalues_array[$indexBillContact]);
	$arrBillAddrIds = ($indexBillAddr === false) ? '0' : getStringFilterIds("assign_billaddr",$filtervalues_array[$indexBillAddr]);
	$arrJlocationIds = ($indexJlocation === false) ? '0' : getStringFilterIds("jlocation",$filtervalues_array[$indexJlocation]);
	
	$comm_calc_type = array("P" =>"placement fee","BR" =>"bill rate","PR" =>"pay rate","RR" =>"salary","MN" =>"margin","MP" =>"markup");//this is for holding commission types

	while($rowHrCon = @mysql_fetch_array($rsHrCon))
	{	
        
  
         $reasonNote=html_entity_decode(stripslashes($rowHrCon['notes_cancel']));
					
		$perDiemCurrencyPeriod = $rowHrCon['diem_currency']." / ".$rowHrCon['diem_period']; 
		
		//Billable and Taxable types based on Job types
		if($arrManage[$rowHrCon['jotype']] == 'Internal Direct' || $arrManage[$rowHrCon['jotype']] == 'Direct')
		{
			$diemBillableType = '';
			$diemTaxableType = '';
			$diemLodgingValue = '';
			$diemMIEValue = '';
			$diemTotalValue = '';
			
			$rowHrCon['pamount'] = 0.00;
			$rowHrCon['bamount'] = 0.00;
			$rowHrCon['otprate'] = 0.00;
			$rowHrCon['otbrate'] = 0.00;
			$rowHrCon['dtprate'] = 0.00;
			$rowHrCon['dtbrate'] = 0.00;
		}
		else
		{
			$diemBillableType = $arrayBillableType[$rowHrCon['diem_billable']];
			$diemTaxableType = $arrayTaxableType[$rowHrCon['diem_taxable']];
			$diemLodgingValue = ($rowHrCon['diem_lodging'] > 0 && $rowHrCon['diem_lodging'] != '')?($rowHrCon['diem_lodging']." ".$perDiemCurrencyPeriod):'';
			$diemMIEValue = ($rowHrCon['diem_mie'] > 0 && $rowHrCon['diem_mie'] != '')?($rowHrCon['diem_mie']." ".$perDiemCurrencyPeriod):'';
			$diemTotalValue = ($rowHrCon['diem_total'] > 0 && $rowHrCon['diem_total'] != '')?($rowHrCon['diem_total']." ".$perDiemCurrencyPeriod):'';
			
			if(trim($arrManage[$rowHrCon['jotype']]) == "Temp/Contract" || trim($arrManage[$rowHrCon['jotype']]) == "Internal Temp/Contract")
			{
				$rowHrCon['rate'] = 0.00;
			}
		}
		//ends here
		$hrTimesheetCount = getTimeRowsCount($rowHrCon['idassignment'],$rowHrCon['username']);
		if(in_array("aprdate",$sortarr) && $columnCheck != 0 && ($rdata[61] != "" || $rptFrom == "3") && $rowHrCon['idassignment'] !="")
		{
			$resArray = getTimeSheets($rowHrCon['idassignment'],"approve",$rowHrCon['username'],$timeFilterVal,$subHrsVal,$appHrsVal,$appDateVal);
		}
		else if($columnCheck != 0 && ($timeFilter == "yes" || $rdata[57] != "" || $rdata[58] != "" || $rdata[60] != "" || $rdata[62] != "" || $rdata[63] != "" || $rdata[72] != "" || $rdata[73] != "" || $rdata[74] != "" || $rdata[75] != "") && $rowHrCon['idassignment'] !="")
		{
			$resArray = getTimeSheets($rowHrCon['idassignment'],"submitted",$rowHrCon['username'],$timeFilterVal,$subHrsVal,$appHrsVal,$appDateVal);
		}
		else
			$resArray = array();

		$resArrayCount = count($resArray);
		
		$timeCount = 0;
	   	$timeCount = count($resArray['subdate']);
		if(($tab=="addr" || $rptFrom == "3") && $timeFilter == "yes")
			$timeCount = count($resArray['subdate']);
		else if(($tab=="addr" || $rptFrom == "3") && $timeFilter == "no" && $timeCount == 0)
			$timeCount = 1;
	   	else
			$timeCount = $timeCount;
		
		$hr_staffacc_array=companyName($rowHrCon['client']);
		// mloc fix
		$hr_billaddress=companyName_jlocation_billaddr($rowHrCon['bill_address']);
              
		$hr_jlocation=companyName_jlocation_billaddr($rowHrCon['endclient']);

		$hr_margin = getAsgn_comm_calculate("margin|".$rowHrCon['mar']);
		
		//Now putting all columsn into this while loop
	   	if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
		{
			if(in_array("CompanyName",$filternames_array) && ($filtervalues_array[$indexCompanyName] != '') && 
			($filtervalues_array[$indexCompanyName] != "ALL"))
			{
				$condHrCon =  (in_array($rowHrCon['client'],$arrCompIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			
			if(in_array("SalesAgent",$filternames_array) && ($filtervalues_array[$indexSalesAgent] != '') && 
			($filtervalues_array[$indexSalesAgent] != "ALL"))
			{
				$condHrCon =  (in_array($rowHrCon['owner'],$arrSalesIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("moduser",$filternames_array) && ($filtervalues_array[$indexmuser] != '') && 
			($filtervalues_array[$indexmuser] != "ALL"))
			{
				$condHrCon =  (in_array($rowHrCon['muser'],$arrmuser)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("Recruiter",$filternames_array) && ($filtervalues_array[$indexRecruiter] != '') && 
			($filtervalues_array[$indexRecruiter] != "ALL"))
			{
				$condHrCon =  (in_array($rowHrCon['username'],$arrRecIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("assign_billcontact",$filternames_array) && ($filtervalues_array[$indexBillContact] != '') && 
			($filtervalues_array[$indexBillContact] != ""))
			{
				$condHrCon =  (in_array($rowHrCon['bill_contact'],$arrBillContactIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("assign_billaddr",$filternames_array) && ($filtervalues_array[$indexBillAddr] != '') && 
			($filtervalues_array[$indexBillAddr] != ""))
			{
				$condHrCon =  (in_array($rowHrCon['bill_address'],$arrBillAddrIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("jlocation",$filternames_array) && ($filtervalues_array[$indexJlocation] != '') && 
			($filtervalues_array[$indexJlocation] != ""))
			{
			   
			  	$condHrCon =  (in_array($rowHrCon['endclient'],$arrJlocationIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("blocation",$filternames_array) && ($filtervalues_array[$indexBlocation] != '') && 
			($filtervalues_array[$indexBlocation] != "ALL"))
			{
				$condHrCon =  (in_array($rowHrCon['location'],$arrBlocIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("federalid",$filternames_array) && ($filtervalues_array[$indexFederalid] != '') && 
			($filtervalues_array[$indexFederalid] != "ALL"))
			{
				$condHrCon =  (in_array($rowHrCon['location'],$arrFederalIds)) ? "1" : "0";
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("corpcode",$filternames_array) && ($filtervalues_array[$indexCorpCode] != '') && 
			($filtervalues_array[$indexCorpCode] != "ALL"))
			{
				if($rowHrCon['corpcodesno'] != "" && $rowHrCon['corpcodesno'] != '0')
				{
					$condHrCon =  ($rowHrCon['corpcodesno']==$filtervalues_array[$indexCorpCode]) ? "1" : "0";
				}
				else
				{
					$condHrCon =  (in_array($rowHrCon['client'],$arrcorpcodeIds)) ? "1" : "0";
				}
				
				if(!$condHrCon == "1")
			 		continue;
			}
			if(in_array("pdbillable",$filternames_array) && ($filtervalues_array[$indexDiemBillableType] != '') && 
			($filtervalues_array[$indexDiemBillableType] != "ALL"))
			{
				$condHrCon = ($arrayBillableType[$filtervalues_array[$indexDiemBillableType]] == $diemBillableType) ? "1" : "0";
				if(!$condHrCon == "1")
				continue;
			}
			if(in_array("pdtaxable",$filternames_array) && ($filtervalues_array[$indexDiemTaxableType] != '') && 
			($filtervalues_array[$indexDiemTaxableType] != "ALL"))
			{
				$condHrCon = ($arrayTaxableType[$filtervalues_array[$indexDiemTaxableType]] == $diemTaxableType) ? "1" : "0";
				if(!$condHrCon == "1")
				continue;
			}
			if(in_array("margin",$filternames_array) && ($filtervalues_array[$indexmargin] != ''))
			{
				$limit_val = explode("*",addslashes($filtervalues_array[$indexmargin]));
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($hr_margin >= $minvalue && $hr_margin <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($hr_margin <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($hr_margin >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			
			//rates
			if(in_array("Salary",$filternames_array) && ($filtervalues_array[$indexrate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexrate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['rate'] >= $minvalue && $rowHrCon['rate'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['rate'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['rate'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("PayRate",$filternames_array) && ($filtervalues_array[$indexpamount] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexpamount]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['pamount'] >= $minvalue && $rowHrCon['pamount'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['pamount'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['pamount'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("RegularPayRate",$filternames_array) && ($filtervalues_array[$indexrpayrate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexrpayrate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['RPayrate'] >= $minvalue && $rowHrCon['RPayrate'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['RPayrate'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['RPayrate'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("RegularSalary",$filternames_array) && ($filtervalues_array[$indexrsalary] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexrsalary]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['RSalary'] >= $minvalue && $rowHrCon['RSalary'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['RSalary'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['RSalary'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("Regularotpayrate",$filternames_array) && ($filtervalues_array[$indexrotrate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexrotrate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['over_time'] >= $minvalue && $rowHrCon['over_time'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['over_time'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['over_time'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("Regulardblpayrate",$filternames_array) && ($filtervalues_array[$indexrdtrate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexrdtrate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['double_rate_amt'] >= $minvalue && $rowHrCon['double_rate_amt'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['double_rate_amt'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['double_rate_amt'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("Regularperdiemrate",$filternames_array) && ($filtervalues_array[$indexrpdiemtotal] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexrpdiemtotal]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['RDiemTotal'] >= $minvalue && $rowHrCon['RDiemTotal'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['RDiemTotal'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['RDiemTotal'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("BillRate",$filternames_array) && ($filtervalues_array[$indexbamount] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexbamount]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['bamount'] >= $minvalue && $rowHrCon['bamount'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['bpamount'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['bamount'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("otpayrate",$filternames_array) && ($filtervalues_array[$indexotprate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexotprate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['otprate'] >= $minvalue && $rowHrCon['otprate'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['otprate'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['otprate'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("otbrate",$filternames_array) && ($filtervalues_array[$indexotbrate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexotbrate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['otbrate'] >= $minvalue && $rowHrCon['otbrate'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['otbrate'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['otbrate'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("dblpayrate",$filternames_array) && ($filtervalues_array[$indexdtprate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexdtprate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['dtprate'] >= $minvalue && $rowHrCon['dtprate'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['dtprate'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['dtprate'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
			if(in_array("dblbrate",$filternames_array) && ($filtervalues_array[$indexdtbrate] != ''))
			{
				$limit_val = explode("*",$filtervalues_array[$indexdtbrate]);
				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['dtbrate'] >= $minvalue && $rowHrCon['dtbrate'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['dtbrate'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['dtbrate'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}

			if(in_array("markup",$filternames_array) && ($filtervalues_array[$indexMarkup] != ''))
			{
				$limit_val = explode("*",addslashes($filtervalues_array[$indexMarkup]));

				$maxvalue = $limit_val[0];
				$minvalue = $limit_val[1];
				if($maxvalue != "" && $minvalue != "")
					$condHrCon = ($rowHrCon['markup'] >= $minvalue && $rowHrCon['markup'] <= $maxvalue) ? "1" : "0";
				elseif($maxvalue != "" && $minvalue == "")
					$condHrCon = ($rowHrCon['markup'] <= $maxvalue) ? "1" : "0";
				elseif($minvalue != "" && $maxvalue == "")
					$condHrCon = ($rowHrCon['markup'] >= $minvalue) ? "1" : "0";
				if(!$condHrCon == "1")
					continue;
			}
		}
		
		if($rowHrCon['ustatus'] == "cancel")
			$sta = "cancelled";
		else if($rowHrCon['ustatus'] == "active")
			$sta = "active";
		else
			$sta = "closed";
				
		$linkValue = trim($rowHrCon['hrcon_sno']."|"."15"."|".$sta.""."|".$rowHrCon['empsno']);
		
		$assStdate="";
	   	$hrStdate="";
		$assEnddate="";
	   	$hrEnddate="";
		
		$hrStdate=($rowHrCon['s_date'] == '00-00-0000') ?  "" : trim(str_replace('-','/',$rowHrCon['s_date']));
		if($hrStdate!="")
		 {
			 if(trim(strlen($hrStdate)) < 10)
			   $assStdate="0".$hrStdate;
			  else
			   $assStdate=$hrStdate; 
		 }
		$hrEnddate=($rowHrCon['e_date'] == '00-00-0000') ?  "" : trim(str_replace('-','/',$rowHrCon['e_date']));
		if($hrEnddate!="")
		{
			 if(trim(strlen($hrEnddate)) < 10)
			   $assEnddate="0".$hrEnddate;
			  else
			   $assEnddate=$hrEnddate; 
		}
		
		$cur_commission_count = 0;//initialize to zero...
		
		//commission columns selected,timesheet columns selected or not but that assignment dont have time records--then go for this if condition
		if($commissionCheck > 0 && ($columnCheck == 0 || $hrTimesheetCount == 0))
		{ 
			

			$commission_que="SELECT amount, co_type, comm_calc, a.type, el.name AS cempname, person, a.roleid, a.sno FROM assign_commission a LEFT JOIN emp_list AS el ON a.person = el.username WHERE a.assigntype = 'H' and a.assignid = '".$rowHrCon['hrcon_sno']."' and a.type='E' UNION SELECT amount, co_type, comm_calc, a.type, concat_ws( ' ', b.fname, b.mname, b.lname ) AS cempname, person, a.roleid, a.sno FROM assign_commission a LEFT JOIN staffacc_contact b ON (a.person = b.sno and b.username!='') WHERE a.assigntype = 'H' and a.type='A' and a.assignid = '".$rowHrCon['hrcon_sno']."'";
			$commission_res = mysql_query($commission_que,$db);

	   		$cur_commission_count = mysql_num_rows($commission_res);
	   		if($cur_commission_count == 0 && ($columnCheck == 0 || ($columnCheck > 0 && $resArrayCount == 0)))
	   		{
				$commission_res = mysql_query("SELECT 1",$db);//This Query is for making the below while loop to iterate atleast once.
				$cur_commission_count = 1;
			}
			while($commission_res_info = mysql_fetch_assoc($commission_res))
			{
				$commission_amount = "";
				if($commission_res_info['co_type'] == 'flat fee' || $commission_res_info['co_type'] == '%')
					$commission_amount = $commission_res_info['amount']." ".$commission_res_info['co_type'];
				else
					$commission_amount = $commission_res_info['amount'];
				
				// Code to get Roles - added 6 Sep 2010 Piyush R.
				$roleName = 'SELECT roletitle from company_commission WHERE sno='.$commission_res_info['roleid'];
				$roleRs = mysql_query($roleName,$db);
				$roleRes = mysql_fetch_array($roleRs);
				// Role Code ends here	
				
				if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
				{
					if(in_array("commempname",$filternames_array) && ($filtervalues_array[$indexCommEmpName] != '') &&($filtervalues_array[$indexCommEmpName] != "ALL"))
					{
						//$condHrCon =  (in_array($commission_res_info['person'],$arrCommEmpIds)) ? "1" : "0";						
						$condHrCon =  (in_array($commission_res_info['cempname'],$arrCommEmpIds)) ? "1" : "0";
						if(!$condHrCon == "1")
							continue;
					}
					if(in_array("commamount",$filternames_array) && ($filtervalues_array[$indexCommAmount] != '') &&($filtervalues_array[$indexCommAmount] != "ALL"))
					{
						$limit_val = explode("*",addslashes($filtervalues_array[$indexCommAmount]));
						$maxvalue = $limit_val[0];
						$minvalue = $limit_val[1];
						if($maxvalue != "" && $minvalue != "")
							$condHrCon = ($commission_res_info['amount'] >= $minvalue && $commission_res_info['amount'] <= $maxvalue) ? "1" : "0";
						elseif($maxvalue != "" && $minvalue == "")
							$condHrCon = ($commission_res_info['amount'] <= $maxvalue) ? "1" : "0";
						elseif($minvalue != "" && $maxvalue == "")
							$condHrCon = ($commission_res_info['amount'] >= $minvalue) ? "1" : "0";
						if(!$condHrCon == "1")
							continue;
					}
					if(in_array("commsource",$filternames_array) && ($filtervalues_array[$indexCommSource] != '') &&($filtervalues_array[$indexCommSource] != "ALL"))
					{
						$condHrCon =  ($commission_res_info['comm_calc']==$filtervalues_array[$indexCommSource]) ? "1" : "0";
						if(!$condHrCon == "1")
							continue;
					}
					if(in_array("Role",$filternames_array) && ($filtervalues_array[$indexRole] != '') &&($filtervalues_array[$indexRole] != "ALL"))
					{
						$condHrCon =  ($commission_res_info['roleid']==$filtervalues_array[$indexRole]) ? "1" : "0";
						if(!$condHrCon == "1")
							continue;
					}
				}
				if($wcompRateColSelected == "yes")
				$wCompRatesArr = getWcompRates($rowHrCon['wcompSno'],$assStdate,$assEnddate);
				$wCompRatesArrCount = count($wCompRatesArr);
				$wCompRatesArrCount = ($wCompRatesArrCount > 0) ? $wCompRatesArrCount : 1;
				
				// Setting up Federal Tax and State Tax
				//Data for Total Federal Tax Allowances
				if($arr[70]=='Y')
					$totFedtaxallow='Exempt';//$totFedtaxallow=99; modified Piyush R
				else
					$totFedtaxallow=($arr[35]!=0.00)? $arr[35] : "";
				
				//Data for Total State Tax Allowances
				if($arr[71]=='Y')
					$totStatetaxallow='Exempt';//$totStatetaxallow=99; modified Piyush R
				else
					$totStatetaxallow=($arr[36]!=0.00)? $arr[36] : "";
				// ends here
				
				for($wc=0;$wc < $wCompRatesArrCount; $wc++)
				{
					
					$state=getStateAbbrFromId($rowHrCon['withholdingState'],$dbcon='db');
					$stateArr = explode('|',$state);
					
					if($rowHrCon['corpcodesno'] != "" && $rowHrCon['corpcodesno'] != '0')
					{
						$corpCodeVal = $rowHrCon['corpcodesno'];
						$corpCodeType = "corpcode";
					}
					else
					{
						$corpCodeVal = $rowHrCon['client'];
						$corpCodeType = "client";
					}
					switch ($rowHrCon['ustatus']) {

						case 'active':
							$rowHrCon_status = "Active";
							break;

						case 'closed':
							$rowHrCon_status = "Closed";
							break;

						case 'cancel':
							$rowHrCon_status = "Cancelled";
							break;

						case 'pending':
							$rowHrCon_status = "Needs Approval";
							break;

						default:
							$rowHrCon_status = "";
							break;
					}
					$rowHrCon['notes_cancel'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['notes_cancel']);
					$rowHrCon['reasons'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['reasons']);
					$values_array = 
						array(
							"CompanyName" => stripslashes($hr_staffacc_array[0]),
							"FirstName" => stripslashes($rowHrCon['fname']),
							"MiddleName" => stripslashes($rowHrCon['mname']), 
							"LastName" => stripslashes($rowHrCon['lname']) ,
							"PayRate" => ($rowHrCon['pamount'] == "0.00") ? "" : "$rowHrCon[pamount]",
							"BillRate" => ($rowHrCon['bamount'] == "0.00") ? "" : "$rowHrCon[bamount]",
							"Salary" => ($rowHrCon['rate'] == "0.00") ? "" : $rowHrCon['rate'],
							"CompenCode" => $rowHrCon['wcomp_code'] ,
							"EmployeeId" => $rowHrCon['empsno'] ,
							"SSNNumber" => $ac_aced->decrypt($rowHrCon['ssn']),
							"AssignmentName" => stripslashes($rowHrCon['project']) ,
							"StartDate" =>  $assStdate,
							"Recruiter" => stripslashes(getRecruiter($rowHrCon['username'])),
							"SalesAgent" => stripslashes(getOwnerName($rowHrCon['owner'])),
							"Status" => $rowHrCon_status,
							"otbrate" => ($rowHrCon['otbrate'] == "0.00") ? "" : $rowHrCon[otbrate],
							"blocation" => stripslashes(getBranchLocation($rowHrCon['location'])),
							"otpayrate" => ($rowHrCon['otprate'] == "0.00") ? "" : $rowHrCon[otprate],
							"enddate" =>$assEnddate,
							"expenddate" => ($rowHrCon['exp_enddate'] == "00/00/0000" ) ? "" : $rowHrCon['exp_enddate'],
							"federalid" => getFederalid($rowHrCon['location']),
							"customer" => $hr_staffacc_array[2],
							"dblpayrate" =>($rowHrCon['dtprate'] == "0.00") ? "" : $rowHrCon[dtprate],
							"dblbrate" =>($rowHrCon['dtbrate'] == "0.00") ? "" : $rowHrCon[dtbrate],
							"jocreator" => $rowHrCon['jobordercreator'],
							"commempname" => $commission_res_info['cempname'],
							"commamount" => $commission_amount,
							"commsource" => $comm_calc_type[$commission_res_info['comm_calc']],
							"placefee" => ($rowHrCon['placement_fee'] == "0.00" ) ? "" : $rowHrCon['placement_fee'],
							"margin" => ($rowHrCon['margin'] == "0.00" ) ? "" : number_format($hr_margin,2,".",""),
							"assignmentid" => $rowHrCon['idassignment'],
							"paypid" => $rowHrCon['payrollpid'],
							"credate" => ($rowHrCon['cdate'] == "00/00/0000" ) ? "" : $rowHrCon['cdate'], 
							"moduser" => stripslashes(getOwnerName($rowHrCon['muser'])),
							"moddate" => ($rowHrCon['mdate'] == "00/00/0000" ) ? "" : $rowHrCon['mdate'],
							"assignmenttype" => stripslashes(seleAtype($rowHrCon['jtype'])),
							"jobtype" => $arrManage[$rowHrCon['jotype']],
							"subhours" => "",
							"apphours" => "",
							"burden" => $rowHrCon['burden'],
							"subdate" => "",
							"aprdate" => "",
							"tstrdate" => "",
							"tenddate" => "",
							"corpcode" => getCorpCode($corpCodeVal,$corpCodeType,$dbcon='db'), //2nd parameter is must ,to know if we are passing staffacc_cinfo sno or corp_code(client means sno)
							"pdlodging" => $diemLodgingValue,
							"pdmie" => $diemMIEValue,
							"pdtotal" => $diemTotalValue,
							"pdbillable" => $diemBillableType,
							"pdtaxable" =>  $diemTaxableType,
							"pdtotamt" => "",
							"alternateId" => ($rowHrCon['posid'] == 0)?"":$rowHrCon['posid'],
							"reghours" => "",
							"overtimehours" => "",
							"doubletimehours" => "",
							"billablehours" => "",
							"reportperson" => stripslashes($rowHrCon['reportto']),
							"contactperson" => stripslashes($rowHrCon['contact']),
							"assign_category" => getManage($rowHrCon['catid']),
							"assign_refcode" => $rowHrCon['refcode'],
							"assign_billcontact" => stripslashes(getBillContact($rowHrCon['bill_contact'])),
							"assign_billaddr" => stripslashes($hr_billaddress[0]),
							"assign_imethod" => $hr_staffacc_array[3],
							"assign_iterms" => $hr_staffacc_array[4],
							"assign_pterms" => $rowHrCon['pterms'],
							"assign_tsapproved" => $rowHrCon['tsapp'],
							"assign_ponumber" => $rowHrCon['po_num'],
							"assign_department" => $rowHrCon['department'],
							"assign_billterms" => $rowHrCon['bill_req'],
							"assign_sterms" => $rowHrCon['service_terms'],
							"jlocation" => stripslashes($hr_jlocation[0]),
							"assgnReasonCodes" =>html_entity_decode(stripslashes($rowHrCon['reasons'])),
							"assgnReason" =>$reasonNote, 
							"workersCompRate" => $wCompRatesArr[$wc],
							"HRMDepartment" => $rowHrCon['HRMDepartment'],
							"fedtaxallowance" => $rowHrCon['fedTaxAllowance'],
							"statetaxallowance" => $rowHrCon['stateTaxAllowance'],
							"filingstatus" => $rowHrCon['filingStatus'],
							"withholdingstate" => $stateArr[1],
							"paychkdelmethod" =>ucfirst(getManage($rowHrCon['payCheckDelMethod'])),
							"Role" => stripslashes($roleRes['roletitle']),
							"statetaxwithholdpercentage" => $rowHrCon['statetaxwithholdpercentage'],
							"markup" => $rowHrCon['markup'],							
							"empEmailId" => $rowHrCon['empEmailId'],
							"empPrimaryNumber" => (!empty($rowHrCon['empPrimaryNumberExt'])) ? $rowHrCon['empPrimaryNumber']." X ".$rowHrCon['empPrimaryNumberExt'] : $rowHrCon['empPrimaryNumber'],
							"empMobileNumber" => $rowHrCon['empMobileNumber'],
                            "BillBurden" => $rowHrCon['bill_burden'],
							"RegularPayRate" => ($rowHrCon['RPayrate'] == "0.00") ? "" : $rowHrCon['RPayrate'],
							"RegularSalary" => ($rowHrCon['RSalary'] == "0.00") ? "" : $rowHrCon['RSalary'],
							"Regularotpayrate" => ($rowHrCon['over_time'] == "0.00") ? "" : $rowHrCon['over_time'],
							"Regulardblpayrate" => ($rowHrCon['double_rate_amt'] == "0.00") ? "" : $rowHrCon['double_rate_amt'],
							"Regularperdiemrate" => ($rowHrCon['RDiemTotal'] == "0.00") ? "" : $rowHrCon['RDiemTotal']." ".$rowHrCon['RDiemCurrency']." / ".$rowHrCon['RDiemPeriod'],
							"PayBurdenType" => $rowHrCon['PBType'],
							"BillBurdenType" => $rowHrCon['BBType'],
							"industry" => stripslashes($rowHrCon['industry'])
						);
						$ii=0;
				
						for($q=0;$q<$count_sortarr;$q++)
						{
							for($f=0 ; $f<$count_fieldnames; $f++)
							{
								if($sortarr[$q] == $fieldnames[$f])
								{
									$variable = $$variablenames[$f];
									if($variable[0]!="")
									{
										$data[$i][$ii] = $values_array[$fieldnames[$f]];
										$sslength_array[$fieldnames[$f]] = trim((strlen($values_array[$fieldnames[$f]]) <= 
										strlen($variable[2])) ? strlen($values_array[$fieldnames[$f]]) : (strlen($variable[2])+3));
										$ii++;
									}
								}
							}
						}		
						if($count_sortarr)
							$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
				
						$data[$i][$ii] = "javascript:showAssig('$linkValue','$def_appsvr_domain')";
						$ii++;
						if(($varCompanyName[0]!="") || ($varFirstName[0]!="") || ($varMiddleName[0]!="") || ($varLastName[0]!="") || ($varPayRate[0]!="") || ($varBillRate[0] != "")|| ($varSalary[0] != "")|| ($varCompenCode[0] != "") || ($varEmployeeId[0] != "") || ($varSSN[0] != "") || ($varAssgName[0] != "") || ($varStartDate[0] != "") || ($varRecruiter[0] != "")  || ($varSalesAgent[0] != "")|| ($varStatus[0] != "") || ($otbrate[0] != "") || ($blocation[0] != "") || ($otpayrate[0] != "")|| ($enddate[0] != "")|| ($expenddate[0] != "") || ($federalid[0] != "") || ($customer[0] != "") ||($dblpayrate[0] != "") || ($dblbrate[0] != "") || ($jocreator[0] != "") || ($commempname[0] != "") || ($commamount[0] != "") || ($commsource[0] != "") || ($placefee[0] != "") || ($margin[0] != "") || ($assignmentid[0] != "") || ($assignmenttype[0] != "") || ($jobtype[0] != "") || ($subhours[0] != "") || ($apphours[0] != "") || ($burden[0] != "") || ($subdate[0]!="") || ($aprdate[0]!="") || ($tstrdate[0] != "") || ($tenddate[0] != "") || ($corpcode[0] != "")  || ($pdlodging[0] != "")|| ($pdmie[0] != "")|| ($pdtotal[0] != "")|| ($pdbillable[0] != "")|| ($pdtaxable[0] != "")  || ($pdtotamt[0] != "")  || ($alternateId[0] != "") || ($reghours[0] != "") || ($overtimehours[0] != "") || ($doubletimehours[0] != "") || ($billablehours[0] != "") || ($reportperson[0] != "") || ($contactperson[0] != "") || ($assign_category[0] != "") || ($assign_refcode[0] != "") || ($assign_billcontact[0] != "") || ($assign_billaddr[0] != "") || ($assign_imethod[0] != "") || ($assign_iterms[0] != "") || ($assign_pterms[0] != "") || ($assign_tsapproved[0] != "") || ($assign_ponumber[0] != "") || ($assign_department[0] != "") || ($assign_billterms[0] != "") || ($assign_sterms[0] != "") || ($jlocation[0] != "") || ($assgnReasonCodes[0] != "") || ($assgnReason[0] != "") || ($workersCompRate[0] != "") || ($HRMDepartment[0] != "")|| ($fedtaxallowance[0] != "")|| ($filingstatus[0]!= "") ||($withholdingstate[0]!="")|| ($statetaxallowance[0]!="")||($paychkdelmethod[0]!="") ||($Role[0]!="") || ($statetaxwithholdpercentage[0] != "") || ($Markup[0] != "") || ($empEmailId[0] != "") || ($empPrimaryNumber[0] != "") || ($empMobileNumber[0] != "") || ($BillBurden[0] != "") || ($RegularPayRate[0] != "") || ($RegularSalary[0] != "") || ($Regularotpayrate[0] != "") || ($Regulardblpayrate[0] != "") || ($Regularperdiemrate[0] != "") || ($PayBurdenType[0] != "") || ($BillBurdenType[0] != "") )
						{
							$data[$i][$ii]=$slength;                          
							$ii++;
						}			
					$i++;
				}
			}
		}
		
		elseif($columnCheck > 0 && $hrTimesheetCount > 0)//if time records are there-then go for this block -dont mind commision checked or unchecked
		{
		
			//if(!($resArrayCount == 0 && $cur_commission_count > 0))//check this for if commission is there and time sheet is not there, then dont enter the loop...
			if($resArrayCount == 0 && $timeFilter == 'yes')
			{
				continue;
			}
			if($resArrayCount == 0)
			{
				$timeCount = 1;
			}
			if($timeCount > 0)
			{
				for($k=0;$k<$timeCount;$k++)
				{
					$pdTotalValue = "";
					$fields = array("subhours","apphours","pdtotamt","reghours","overtimehours","doubletimehours","billablehours");
					$pdTotalValue = ($rowHrCon['diem_period'] == 'FLATFEE')?$diemTotalValue:number_format($resArray['days'][$k] * ( calculateAmountTotal($diemTotalValue,$rowHrCon['diem_period'], "day")), 2, '.','');
					if($pdTotalValue == '0.00')
						$pdTotalValue = "";
					$phpFilter = 1;
					if($tab == "addr")
					{
						$fieldsCount = count($fields);
						for($j=0;$j<$fieldsCount;$j++)
						{
							if($phpFilter == 1)
							{
								if(in_array($fields[$j],$filternames_array))
								{
									$index = array_search($fields[$j],$filternames_array);
									switch($fields[$j])
									{
										case 'subhours' :
											$values['subhours'] = $resArray['subhrs'][$k];
											break;
										case 'apphours' :
											$values['apphours'] = $resArray['apphrs'][$k];
											break;
										case 'pdtotamt' :
											$values['pdtotamt'] = $pdTotalValue;
											break;
										case 'reghours' :
											$values['reghours'] = $resArray['reghrs'][$k];
											break;
										case 'overtimehours' :
											$values['overtimehours'] = $resArray['overtimehrs'][$k];
											break;
										case 'doubletimehours' :
											$values['doubletimehours'] = $resArray['doubletimehrs'][$k];
											break;
										case 'billablehours' :
											$values['billablehours'] = $resArray['billablehrs'][$k];
											break;	
									}
									$ranges = explode("*",$filtervalues_array[$index]);
									$minvalue = $ranges[1];
									$maxvalue = $ranges[0]; 
									
									if($minvalue != "" && $maxvalue != "")
										$phpFilter =  ( ($values[$fields[$j]] >= $minvalue) && ($values[$fields[$j]] <= $maxvalue) ) ? "1" : "0";
									elseif($minvalue != "" && $maxvalue == "")
									{
										$phpFilter = ( ($values[$fields[$j]] >= $minvalue) ) ? "1" : "0";
									}
									elseif($maxvalue != "" && $minvalue == "")
										$phpFilter =  (  ($values[$fields[$j]] <= $maxvalue) ) ? "1" : "0";
									elseif($maxvalue == "" && $minvalue == "")
										$phpFilter =  1;
								}
								else
									$phpFilter =  1;
							}
							else
								break;
						}
					}
					else
						$phpFilter = 1;
					
					if($pdTotalValue == '0.00' || $pdTotalValue == '')
						$pdTotalValue = "";
					else
					{
						$pdTotalValueUnits = ($rowHrCon['diem_period'] == 'FLATFEE')?'':$rowHrCon['diem_currency']." / DAY"; 
						$pdTotalValue = $pdTotalValue." ".$pdTotalValueUnits; 
					}
					
					$ii=0;
					if($phpFilter == 1)
					{
						if($commissionCheck > 0)//if commission is checked  repeat the records  based on commission employees...
						{ 
							$commission_que="SELECT amount, co_type, comm_calc, a.type, el.name AS cempname, person, a.roleid, a.sno FROM assign_commission a LEFT JOIN emp_list AS el ON a.person = el.username WHERE a.assigntype = 'H' and a.assignid = '".$rowHrCon['hrcon_sno']."' and a.type='E' UNION SELECT amount, co_type, comm_calc, a.type, concat_ws( ' ', b.fname, b.mname, b.lname ) AS cempname, person, a.roleid, a.sno FROM assign_commission a LEFT JOIN staffacc_contact b ON (a.person = b.sno and b.username!='') WHERE a.assigntype = 'H' and a.type='A' and a.assignid = '".$rowHrCon['hrcon_sno']."'";
							$commission_res = mysql_query($commission_que,$db);
				
							$cur_commission_count = mysql_num_rows($commission_res);
							if($cur_commission_count == 0)
							{
								$commission_res = mysql_query("SELECT 1",$db);//This Query is for making the below while loop to iterate atleast once.
								$cur_commission_count = 1;
							}
							while($commission_res_info = mysql_fetch_assoc($commission_res))
							{
								$commission_amount = "";
								if($commission_res_info['co_type'] == 'flat fee' || $commission_res_info['co_type'] == '%')
									$commission_amount = $commission_res_info['amount']." ".$commission_res_info['co_type'];
								else
									$commission_amount = $commission_res_info['amount'];
								
								// Code to get Roles - added 6 Sep 2010 Piyush R.
								$roleName = 'SELECT roletitle from company_commission WHERE sno='.$commission_res_info['roleid'];
								$roleRs = mysql_query($roleName,$db);
								$roleRes = mysql_fetch_array($roleRs);
								// Role Code ends here	
								
								if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
								{
									if(in_array("commempname",$filternames_array) && ($filtervalues_array[$indexCommEmpName] != '') &&($filtervalues_array[$indexCommEmpName] != "ALL"))
									{
										//$condHrCon =  (in_array($commission_res_info['person'],$arrCommEmpIds)) ? "1" : "0";
										$condHrCon =  (in_array($commission_res_info['cempname'],$arrCommEmpIds)) ? "1" : "0";
										if(!$condHrCon == "1")
											continue;
									}
									if(in_array("commamount",$filternames_array) && ($filtervalues_array[$indexCommAmount] != '') &&($filtervalues_array[$indexCommAmount] != "ALL"))
									{
										$limit_val = explode("*",addslashes($filtervalues_array[$indexCommAmount]));
										$maxvalue = $limit_val[0];
										$minvalue = $limit_val[1];
										if($maxvalue != "" && $minvalue != "")
											$condHrCon = ($commission_res_info['amount'] >= $minvalue && $commission_res_info['amount'] <= $maxvalue) ? "1" : "0";
										elseif($maxvalue != "" && $minvalue == "")
											$condHrCon = ($commission_res_info['amount'] <= $maxvalue) ? "1" : "0";
										elseif($minvalue != "" && $maxvalue == "")
											$condHrCon = ($commission_res_info['amount'] >= $minvalue) ? "1" : "0";
										if(!$condHrCon == "1")
											continue;
									}
									if(in_array("commsource",$filternames_array) && ($filtervalues_array[$indexCommSource] != '') &&($filtervalues_array[$indexCommSource] != "ALL"))
									{
										$condHrCon =  ($commission_res_info['comm_calc']==$filtervalues_array[$indexCommSource]) ? "1" : "0";
										if(!$condHrCon == "1")
											continue;
									}
									if(in_array("Role",$filternames_array) && ($filtervalues_array[$indexRole] != '') &&($filtervalues_array[$indexRole] != "ALL"))
									{
										$condHrCon =  ($commission_res_info['roleid']==$filtervalues_array[$indexRole]) ? "1" : "0";
										if(!$condHrCon == "1")
											continue;
									}
								}
								if($wcompRateColSelected == "yes")
									$wCompRatesArr = getWcompRates($rowHrCon['wcompSno'],$assStdate,$assEnddate);
								$wCompRatesArrCount = count($wCompRatesArr);
								$wCompRatesArrCount = ($wCompRatesArrCount > 0) ? $wCompRatesArrCount : 1;
								for($wc=0;$wc < $wCompRatesArrCount; $wc++)
								{
									$state=getStateAbbrFromId($rowHrCon['withholdingState'],$dbcon='db');
									$stateArr = explode('|',$state);
									if($rowHrCon['corpcodesno'] != "" && $rowHrCon['corpcodesno'] != '0')
									{
										$corpCodeVal = $rowHrCon['corpcodesno'];
										$corpCodeType = "corpcode";
									}
									else
									{
										$corpCodeVal = $rowHrCon['client'];
										$corpCodeType = "client";
									}
									switch ($rowHrCon['ustatus']) {
						
										case 'active':
											$rowHrCon_status = "Active";
											break;

										case 'closed':
											$rowHrCon_status = "Closed";
											break;

										case 'cancel':
											$rowHrCon_status = "Cancelled";
											break;

										case 'pending':
											$rowHrCon_status = "Needs Approval";
											break;

										default:
											$rowHrCon_status = "";
											break;
									}
									$rowHrCon['notes_cancel'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['notes_cancel']);
									$rowHrCon['reasons'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['reasons']);
									$values_array = 
										array(
											"CompanyName" => stripslashes($hr_staffacc_array[0]),
											"FirstName" => stripslashes($rowHrCon['fname']),
											"MiddleName" => stripslashes($rowHrCon['mname']) , 
											"LastName" => stripslashes($rowHrCon['lname']) ,
											"PayRate" => ($rowHrCon['pamount'] == "0.00") ? "" : "$rowHrCon[pamount]",
											"BillRate" => ($rowHrCon['bamount'] == "0.00") ? "" : "$rowHrCon[bamount]",
											"Salary" => ($rowHrCon['rate'] == "0.00") ? "" : $rowHrCon['rate'],
											"CompenCode" => $rowHrCon['wcomp_code'],
											"EmployeeId" => $rowHrCon['empsno'] ,
											"SSNNumber" => $ac_aced->decrypt($rowHrCon['ssn']),
											"AssignmentName" => stripslashes($rowHrCon['project']) ,
											"StartDate" =>  $assStdate,
											"Recruiter" => stripslashes(getRecruiter($rowHrCon['username'])),
											"SalesAgent" => stripslashes(getOwnerName($rowHrCon['owner'])),
											"Status" => $rowHrCon_status,
											"otbrate" => ($rowHrCon['otbrate'] == "0.00") ? "" : $rowHrCon[otbrate],
											"blocation" => stripslashes(getBranchLocation($rowHrCon['location'])),
											"otpayrate" => ($rowHrCon['otprate'] == "0.00") ? "" : $rowHrCon[otprate],
											"enddate" =>$assEnddate,
											"expenddate" => ($rowHrCon['exp_enddate'] == "00/00/0000" ) ? "" : $rowHrCon['exp_enddate'],
											"federalid" => getFederalid($rowHrCon['location']),
											"customer" => $hr_staffacc_array[2],
											"dblpayrate" =>($rowHrCon['dtprate'] == "0.00") ? "" : $rowHrCon[dtprate],
											"dblbrate" =>($rowHrCon['dtbrate'] == "0.00") ? "" : $rowHrCon[dtbrate],
											"jocreator" => stripslashes($rowHrCon['jobordercreator']),
											"commempname" => $commission_res_info['cempname'],
											"commamount" => $commission_amount,
											"commsource" => $comm_calc_type[$commission_res_info['comm_calc']],
											"placefee" => ($rowHrCon['placement_fee'] == "0.00" ) ? "" : $rowHrCon['placement_fee'],
											"margin" => ($rowHrCon['margin'] == "0.00" ) ? "" : number_format($hr_margin,2,".",""),
											"assignmentid" => $rowHrCon['idassignment'],
											"paypid" => $rowHrCon['payrollpid'],
											"credate" => ($rowHrCon['cdate'] == "00/00/0000" ) ? "" : $rowHrCon['cdate'], 
											"moduser" =>stripslashes(getOwnerName($rowHrCon['muser'])),
											"moddate" => ($rowHrCon['mdate'] == "00/00/0000" ) ? "" : $rowHrCon['mdate'],
											"assignmenttype" => stripslashes(seleAtype($rowHrCon['jtype'])),
											"jobtype" => $arrManage[$rowHrCon['jotype']],
											"subhours" => $resArray['subhrs'][$k],
											"apphours" => $resArray['apphrs'][$k],
											"burden" => $rowHrCon['burden'],
											"subdate" => $resArray['subdate'][$k],
											"aprdate" => $resArray['appdate'][$k],
											"tstrdate" => $resArray['strdate'][$k],
											"tenddate" => $resArray['enddate'][$k],
											"corpcode" => getCorpCode($corpCodeVal,$corpCodeType,$dbcon='db'), //2nd parameter is must ,to know if we are passing staffacc_cinfo sno or corp_code(client means sno)
											"pdlodging" => $diemLodgingValue,
											"pdmie" => $diemMIEValue,
											"pdtotal" => $diemTotalValue,
											"pdbillable" => $diemBillableType,
											"pdtaxable" =>  $diemTaxableType,
											"pdtotamt" => $pdTotalValue,
											"alternateId" => ($rowHrCon['posid'] == 0)?"":$rowHrCon['posid'],
											"reghours" => $resArray['reghrs'][$k],
											"overtimehours" => $resArray['overtimehrs'][$k],
											"doubletimehours" => $resArray['doubletimehrs'][$k],
											"billablehours" => ($resArray['billablehrs'][$k] == 0)?"":$resArray['billablehrs'][$k],
											"reportperson" => stripslashes($rowHrCon['reportto']),
											"contactperson" => stripslashes($rowHrCon['contact']),
											"assign_category" => getManage($rowHrCon['catid']),
											"assign_refcode" => $rowHrCon['refcode'],
											"assign_billcontact" => stripslashes(getBillContact($rowHrCon['bill_contact'])),
											"assign_billaddr" => stripslashes($hr_billaddress[0]),
											"assign_imethod" => $hr_staffacc_array[3],
											"assign_iterms" => $hr_staffacc_array[4],
											"assign_pterms" => $rowHrCon['pterms'],
											"assign_tsapproved" => $rowHrCon['tsapp'],
											"assign_ponumber" => $rowHrCon['po_num'],
											"assign_department" => $rowHrCon['department'],
											"assign_billterms" => $rowHrCon['bill_req'],
											"assign_sterms" => $rowHrCon['service_terms'],
											"jlocation" => stripslashes($hr_jlocation[0]),
											"assgnReasonCodes" =>stripslashes($rowHrCon['reasons']),
											"assgnReason" =>$reasonNote,
											"workersCompRate" => $wCompRatesArr[$wc],
											"HRMDepartment" => $rowHrCon['HRMDepartment'],
											"fedtaxallowance" => $rowHrCon['fedTaxAllowance'],
											"statetaxallowance" => $rowHrCon['stateTaxAllowance'],
											"filingstatus" => $rowHrCon['filingStatus'],
											"withholdingstate" => $stateArr[1],
											"paychkdelmethod" =>ucfirst(getManage($rowHrCon['payCheckDelMethod'])),
											"Role" =>stripslashes($roleRes['roletitle']),
											"statetaxwithholdpercentage" => $rowHrCon['statetaxwithholdpercentage'],
											"markup" => $rowHrCon['markup'],											
											"empEmailId" => $rowHrCon['empEmailId'],
											"empPrimaryNumber" => (!empty($rowHrCon['empPrimaryNumberExt'])) ? $rowHrCon['empPrimaryNumber']." X ".$rowHrCon['empPrimaryNumberExt'] : $rowHrCon['empPrimaryNumber'],
											"empMobileNumber" => $rowHrCon['empMobileNumber'],
                                            						"BillBurden" => $rowHrCon['bill_burden'],
											"RegularPayRate" => ($rowHrCon['RPayrate'] == "0.00") ? "" : $rowHrCon['RPayrate'],
											"RegularSalary" => ($rowHrCon['RSalary'] == "0.00") ? "" : $rowHrCon['RSalary'],
											"Regularotpayrate" => ($rowHrCon['over_time'] == "0.00") ? "" : $rowHrCon['over_time'],
											"Regulardblpayrate" => ($rowHrCon['double_rate_amt'] == "0.00") ? "" : $rowHrCon['double_rate_amt'],
											"Regularperdiemrate" => ($rowHrCon['RDiemTotal'] == "0.00") ? "" : $rowHrCon['RDiemTotal']." ".$rowHrCon['RDiemCurrency']." / ".$rowHrCon['RDiemPeriod'],
											"PayBurdenType" => $rowHrCon['PBType'],
											"BillBurdenType" => $rowHrCon['BBType'],
											"industry" => stripslashes($rowHrCon['industry'])
										);
										$ii=0;
								
										for($q=0;$q<$count_sortarr;$q++)
										{
											for($f=0 ; $f<$count_fieldnames; $f++)
											{
												if($sortarr[$q] == $fieldnames[$f])
												{
													$variable = $$variablenames[$f];
													if($variable[0]!="")
													{
														$data[$i][$ii] = $values_array[$fieldnames[$f]];
														$sslength_array[$fieldnames[$f]] = trim((strlen($values_array[$fieldnames[$f]]) <= 
														strlen($variable[2])) ? strlen($values_array[$fieldnames[$f]]) : (strlen($variable[2])+3));
														$ii++;
													}
												}
											}
										}		
										if($count_sortarr)
											$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
								
										$data[$i][$ii] = "javascript:showAssig('$linkValue','$def_appsvr_domain')";
										$ii++;
										if(($varCompanyName[0]!="") || ($varFirstName[0]!="") || ($varMiddleName[0]!="") || ($varLastName[0]!="") || ($varPayRate[0]!="") || ($varBillRate[0] != "")|| ($varSalary[0] != "")|| ($varCompenCode[0] != "") || ($varEmployeeId[0] != "") || ($varSSN[0] != "") || ($varAssgName[0] != "") || ($varStartDate[0] != "") || ($varRecruiter[0] != "")  || ($varSalesAgent[0] != "")|| ($varStatus[0] != "") || ($otbrate[0] != "") || ($blocation[0] != "") || ($otpayrate[0] != "")|| ($enddate[0] != "")|| ($expenddate[0] != "") || ($federalid[0] != "") || ($customer[0] != "") ||($dblpayrate[0] != "") || ($dblbrate[0] != "") || ($jocreator[0] != "") || ($commempname[0] != "") || ($commamount[0] != "") || ($commsource[0] != "") || ($placefee[0] != "") || ($margin[0] != "") || ($assignmentid[0] != "") || ($assignmenttype[0] != "") || ($jobtype[0] != "") || ($subhours[0] != "") || ($apphours[0] != "") || ($burden[0] != "") || ($subdate[0]!="") || ($aprdate[0]!="") || ($tstrdate[0] != "") || ($tenddate[0] != "") || ($corpcode[0] != "")  || ($pdlodging[0] != "")|| ($pdmie[0] != "")|| ($pdtotal[0] != "")|| ($pdbillable[0] != "")|| ($pdtaxable[0] != "")  || ($pdtotamt[0] != "")  || ($alternateId[0] != "") || ($reghours[0] != "") || ($overtimehours[0] != "") || ($doubletimehours[0] != "") || ($billablehours[0] != "") || ($reportperson[0] != "") || ($contactperson[0] != "") || ($assign_category[0] != "") || ($assign_refcode[0] != "") || ($assign_billcontact[0] != "") || ($assign_billaddr[0] != "") || ($assign_imethod[0] != "") || ($assign_iterms[0] != "") || ($assign_pterms[0] != "") || ($assign_tsapproved[0] != "") || ($assign_ponumber[0] != "") || ($assign_department[0] != "") || ($assign_billterms[0] != "") || ($assign_sterms[0] != "") || ($jlocation[0] != "") || ($assgnReasonCodes[0] != "") || ($assgnReason[0] != "") || ($workersCompRate[0] != "") || ($HRMDepartment[0] != "")|| ($fedtaxallowance[0] != "")|| ($filingstatus[0]!= "") ||($withholdingstate[0]!="")|| ($statetaxallowance[0]!="")||($paychkdelmethod[0]!="") ||($Role[0]!="") || ($statetaxwithholdpercentage[0] != "") || ($Markup[0] != "") || ($empEmailId[0] != "") || ($empPrimaryNumber[0] != "") || ($empMobileNumber[0] != "") || ($BillBurden[0] != "") || ($RegularPayRate[0] != "") || ($RegularSalary[0] != "") || ($Regularotpayrate[0] != "") || ($Regulardblpayrate[0] != "") || ($Regularperdiemrate[0] != "") || ($PayBurdenType[0] != "") || ($BillBurdenType[0] != ""))
										{
											$data[$i][$ii]=$slength;
											$ii++;
										}			
									$i++;
									
								}
							}
						}
						else
						{
							if($wcompRateColSelected == "yes")
								$wCompRatesArr = getWcompRates($rowHrCon['wcompSno'],$assStdate,$assEnddate);
							$wCompRatesArrCount = count($wCompRatesArr);
							$wCompRatesArrCount = ($wCompRatesArrCount > 0) ? $wCompRatesArrCount : 1;
							for($wc=0;$wc < $wCompRatesArrCount; $wc++)
							{
								$state=getStateAbbrFromId($rowHrCon['withholdingState'],$dbcon='db');
								$stateArr = explode('|',$state);
								if($rowHrCon['corpcodesno'] != "" && $rowHrCon['corpcodesno'] != '0')
								{
									$corpCodeVal = $rowHrCon['corpcodesno'];
									$corpCodeType = "corpcode";
								}
								else
								{
									$corpCodeVal = $rowHrCon['client'];
									$corpCodeType = "client";
								}
								switch ($rowHrCon['ustatus']) {
						
										case 'active':
											$rowHrCon_status = "Active";
											break;

										case 'closed':
											$rowHrCon_status = "Closed";
											break;

										case 'cancel':
											$rowHrCon_status = "Cancelled";
											break;

										case 'pending':
											$rowHrCon_status = "Needs Approval";
											break;

										default:
											$rowHrCon_status = "";
											break;
								}
								$rowHrCon['notes_cancel'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['notes_cancel']);
								$rowHrCon['reasons'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['reasons']);
								$values_array = 
								array(
									"CompanyName" => stripslashes($hr_staffacc_array[0]),
									"FirstName" => stripslashes($rowHrCon['fname']),
									"MiddleName" => stripslashes($rowHrCon['mname']), 
									"LastName" => stripslashes($rowHrCon['lname']),
									"PayRate" => ($rowHrCon['pamount'] == "0.00") ? "" : "$rowHrCon[pamount]",
									"BillRate" => ($rowHrCon['bamount'] == "0.00") ? "" : "$rowHrCon[bamount]",
									"Salary" => ($rowHrCon['rate'] == "0.00") ? "" : $rowHrCon['rate'],
									"CompenCode" => $rowHrCon['wcomp_code'],
									"EmployeeId" => $rowHrCon['empsno'],
									"SSNNumber" => $ac_aced->decrypt($rowHrCon['ssn']),
									"AssignmentName" => stripslashes($rowHrCon['project']) ,
									"StartDate" =>  $assStdate,
									"Recruiter" =>  stripslashes(getRecruiter($rowHrCon['username'])),
									"SalesAgent" => stripslashes(getOwnerName($rowHrCon['owner'])),
									"Status" => $rowHrCon_status,
									"otbrate" => ($rowHrCon['otbrate'] == "0.00") ? "" : $rowHrCon[otbrate],
									"blocation" => stripslashes(getBranchLocation($rowHrCon['location'])),
									"otpayrate" => ($rowHrCon['otprate'] == "0.00") ? "" : $rowHrCon[otprate],
									"enddate" =>$assEnddate ,
									"expenddate" => ($rowHrCon['exp_enddate'] == "00/00/0000" ) ? "" : $rowHrCon['exp_enddate'],
									"federalid" => getFederalid($rowHrCon['location']),
									"customer" => $hr_staffacc_array[2],
									"dblpayrate" =>($rowHrCon['dtprate'] == "0.00") ? "" : $rowHrCon[dtprate],
									"dblbrate" =>($rowHrCon['dtbrate'] == "0.00") ? "" : $rowHrCon[dtbrate],
									"jocreator" => stripslashes($rowHrCon['jobordercreator']),
									"commempname" => "",
									"commamount" => "",
									"commsource" => "",
									"placefee" => ($rowHrCon['placement_fee'] == "0.00" ) ? "" : $rowHrCon['placement_fee'],
									"margin" => ($rowHrCon['margin'] == "0.00" ) ? "" : number_format($hr_margin,2,".",""),
									"assignmentid" => $rowHrCon['idassignment'],
									"paypid" => $rowHrCon['payrollpid'],
									"credate" => ($rowHrCon['cdate'] == "00/00/0000" ) ? "" : $rowHrCon['cdate'],
									"moduser" => stripslashes(getOwnerName($rowHrCon['muser'])),
									"moddate" => ($rowHrCon['mdate'] == "00/00/0000" ) ? "" : $rowHrCon['mdate'],
									"assignmenttype" => stripslashes(seleAtype($rowHrCon['jtype'])),
									"jobtype" => $arrManage[$rowHrCon['jotype']],
									"subhours" => $resArray['subhrs'][$k],
									"apphours" => $resArray['apphrs'][$k],
									"burden" => $rowHrCon['burden'],
									"subdate" => $resArray['subdate'][$k],
									"aprdate" => $resArray['appdate'][$k],
									"tstrdate" => $resArray['strdate'][$k],
									"tenddate" => $resArray['enddate'][$k],
									"corpcode" => getCorpCode($corpCodeVal,$corpCodeType,$dbcon='db'), 
									"pdlodging" => $diemLodgingValue,
									"pdmie" => $diemMIEValue,
									"pdtotal" => $diemTotalValue,
									"pdbillable" => $diemBillableType,
									"pdtaxable" =>  $diemTaxableType,
									"pdtotamt" => $pdTotalValue,
									"alternateId" => ($rowHrCon['posid'] == 0)?"":$rowHrCon['posid'],
									"reghours" => $resArray['reghrs'][$k],
									"overtimehours" => $resArray['overtimehrs'][$k],
									"doubletimehours" => $resArray['doubletimehrs'][$k],
									"billablehours" => ($resArray['billablehrs'][$k] == 0)?"":$resArray['billablehrs'][$k],
									"reportperson" => stripslashes($rowHrCon['reportto']),
									"contactperson" => stripslashes($rowHrCon['contact']),
									"assign_category" => getManage($rowHrCon['catid']),
									"assign_refcode" => $rowHrCon['refcode'],
									"assign_billcontact" => stripslashes(getBillContact($rowHrCon['bill_contact'])),
									"assign_billaddr" => stripslashes($hr_billaddress[0]),
									"assign_imethod" => $hr_staffacc_array[3],
									"assign_iterms" => $hr_staffacc_array[4],
									"assign_pterms" => $rowHrCon['pterms'],
									"assign_tsapproved" => $rowHrCon['tsapp'],
									"assign_ponumber" => $rowHrCon['po_num'],
									"assign_department" => $rowHrCon['department'],
									"assign_billterms" => $rowHrCon['bill_req'],
									"assign_sterms" => $rowHrCon['service_terms'],
									"jlocation" => stripslashes($hr_jlocation[0]),
									"assgnReasonCodes" =>stripslashes($rowHrCon['reasons']),
									"assgnReason" =>$reasonNote,
									"workersCompRate" => $wCompRatesArr[$wc],
									"HRMDepartment" =>$rowHrCon['HRMDepartment'],
									"fedtaxallowance" => $rowHrCon['fedTaxAllowance'],
									"statetaxallowance" => $rowHrCon['stateTaxAllowance'],
									"filingstatus" => $rowHrCon['filingStatus'],
									"withholdingstate" => $stateArr[1],
									"paychkdelmethod" =>ucfirst(getManage($rowHrCon['payCheckDelMethod'])),
									"Role" => stripslashes($roleRes['roletitle']),
									"statetaxwithholdpercentage" => $rowHrCon['statetaxwithholdpercentage'],
									"markup" => $rowHrCon['markup'],
									"empEmailId" => $rowHrCon['empEmailId'],
									"empPrimaryNumber" => (!empty($rowHrCon['empPrimaryNumberExt'])) ? $rowHrCon['empPrimaryNumber']." X ".$rowHrCon['empPrimaryNumberExt'] : $rowHrCon['empPrimaryNumber'],
									"empMobileNumber" => $rowHrCon['empMobileNumber'],
									"BillBurden" => $rowHrCon['bill_burden'],
									"RegularPayRate" => ($rowHrCon['RPayrate'] == "0.00") ? "" : $rowHrCon['RPayrate'],
									"RegularSalary" => ($rowHrCon['RSalary'] == "0.00") ? "" : $rowHrCon['RSalary'],
									"Regularotpayrate" => ($rowHrCon['over_time'] == "0.00") ? "" : $rowHrCon['over_time'],
									"Regulardblpayrate" => ($rowHrCon['double_rate_amt'] == "0.00") ? "" : $rowHrCon['double_rate_amt'],
									"Regularperdiemrate" => ($rowHrCon['RDiemTotal'] == "0.00") ? "" : $rowHrCon['RDiemTotal']." ".$rowHrCon['RDiemCurrency']." / ".$rowHrCon['RDiemPeriod'],
									"PayBurdenType" => $rowHrCon['PBType'],
									"BillBurdenType" => $rowHrCon['BBType'],
									"industry" => stripslashes($rowHrCon['industry'])
									);
								//time row preparation
								for($q=0;$q<$count_sortarr;$q++)
								{
									for($f=0 ; $f<$count_fieldnames ; $f++)
									{
										if($sortarr[$q] == $fieldnames[$f])
										{
											$variable = $$variablenames[$f];
											if($variable[0]!="")
											{
												$data[$i][$ii] = $values_array[$fieldnames[$f]];
												$sslength_array[$fieldnames[$f]] = trim((strlen($values_array[$fieldnames[$f]]) <= strlen($variable[2])) ? strlen($values_array[$fieldnames[$f]]) : (strlen($variable[2])+3));
												$ii++;
											}
										}
									}
								}	
								if($count_sortarr)
									$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
								
								$data[$i][$ii] = "javascript:showAssig('$linkValue','$def_appsvr_domain')";
								$ii++;
							
								if(($varCompanyName[0]!="") || ($varFirstName[0]!="") || ($varMiddleName[0]!="") || ($varLastName[0]!="") || ($varPayRate[0]!="") || ($varBillRate[0] != "")|| ($varSalary[0] != "")|| ($varCompenCode[0] != "") || ($varEmployeeId[0] != "") || ($varSSN[0] != "") || ($varAssgName[0] != "") || ($varStartDate[0] != "") || ($varRecruiter[0] != "")  || ($varSalesAgent[0] != "")|| ($varStatus[0] != "") || ($otbrate[0] != "") || ($blocation[0] != "") || ($otpayrate[0] != "")|| ($enddate[0] != "")|| ($expenddate[0] != "") || ($federalid[0] != "") || ($customer[0] != "") ||($dblpayrate[0] != "") || ($dblbrate[0] != "") || ($jocreator[0] != "") || ($commempname[0] != "") || ($commamount[0] != "") || ($commsource[0] != "") || ($placefee[0] != "") || ($margin[0] != "") || ($assignmentid[0] != "") || ($assignmenttype[0] != "") || ($jobtype[0] != "") || ($subhours[0] != "") || ($apphours[0] != "") || ($burden[0] != "") || ($subdate[0]!="") || ($aprdate[0]!="") || ($tstrdate[0] != "") || ($tenddate[0] != "") || ($corpcode[0] != "") || ($pdlodging[0] != "")|| ($pdmie[0] != "")|| ($pdtotal[0] != "")|| ($pdbillable[0] != "")|| ($pdtaxable[0] != "") || ($pdtotamt[0] != "") || ($alternateId[0] != "") || ($reghours[0] != "") || ($overtimehours[0] != "") || ($doubletimehours[0] != "") || ($billablehours[0] != "") || ($reportperson[0] != "") || ($contactperson[0] != "") || ($assign_category[0] != "") || ($assign_refcode[0] != "") || ($assign_billcontact[0] != "") || ($assign_billaddr[0] != "") || ($assign_imethod[0] != "") || ($assign_iterms[0] != "") || ($assign_pterms[0] != "") || ($assign_tsapproved[0] != "") || ($assign_ponumber[0] != "") || ($assign_department[0] != "") || ($assign_billterms[0] != "") || ($assign_sterms[0] != "") || ($jlocation[0] != "")  || ($assgnReasonCodes[0] != "") || ($assgnReason[0] != "") || ($workersCompRate[0] != "") || ($HRMDepartment[0] != "")|| ($fedtaxallowance[0] != "")|| ($filingstatus[0]!= "") ||($withholdingstate[0]!="")|| ($statetaxallowance[0]!="")||($paychkdelmethod[0]!="") ||($Role[0]!="") || ($statetaxwithholdpercentage[0] != "") || ($Markup[0] != "") || ($empEmailId[0] != "") || ($empPrimaryNumber[0] != "") || ($empMobileNumber[0] != "") || ($BillBurden[0] != "") || ($RegularPayRate[0] != "") || ($RegularSalary[0] != "") || ($Regularotpayrate[0] != "") || ($Regulardblpayrate[0] != "") || ($Regularperdiemrate[0] != "") || ($PayBurdenType[0] != "") || ($BillBurdenType[0] != "")||($industry[0] != ""))
								{
									$data[$i][$ii]=$slength;
									$ii++;
								}
								//end of row preparation			
								$i++;
							}
						}
					} //if($phpFilter == 1)
				}//end of for loop...
			}
            
		}
		else// if commission  and timesheet are unchecked simply display assignment details...
		{
			

			
			if($wcompRateColSelected == "yes")
				$wCompRatesArr = getWcompRates($rowHrCon['wcompSno'],$assStdate,$assEnddate);
			$wCompRatesArrCount = count($wCompRatesArr);
			$wCompRatesArrCount = ($wCompRatesArrCount > 0) ? $wCompRatesArrCount : 1;
			for($wc=0;$wc < $wCompRatesArrCount; $wc++)
			{
			
				$state=getStateAbbrFromId($rowHrCon['withholdingState'],$dbcon='db');
				$stateArr = explode('|',$state);
				if($rowHrCon['corpcodesno'] != "" && $rowHrCon['corpcodesno'] != '0')
				{
					$corpCodeVal = $rowHrCon['corpcodesno'];
					$corpCodeType = "corpcode";
				}
				else
				{
					$corpCodeVal = $rowHrCon['client'];
					$corpCodeType = "client";
				}
				switch ($rowHrCon['ustatus']) {
						
					case 'active':
						$rowHrCon_status = "Active";
						break;

					case 'closed':
						$rowHrCon_status = "Closed";
						break;

					case 'cancel':
						$rowHrCon_status = "Cancelled";
						break;

					case 'pending':
						$rowHrCon_status = "Needs Approval";
						break;

					default:
						$rowHrCon_status = "";
						break;
				}
				$rowHrCon['notes_cancel'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['notes_cancel']);
				$rowHrCon['reasons'] = iconv('utf-8', 'ascii//TRANSLIT', $rowHrCon['reasons']);
				$values_array = 
					array(
							"CompanyName" => stripslashes($hr_staffacc_array[0]),
							"FirstName" => stripslashes($rowHrCon['fname']),
							"MiddleName" =>stripslashes( $rowHrCon['mname']) , 
							"LastName" => stripslashes($rowHrCon['lname']),
							"PayRate" => ($rowHrCon['pamount'] == "0.00") ? "" : "$rowHrCon[pamount]",
							"BillRate" => ($rowHrCon['bamount'] == "0.00") ? "" : "$rowHrCon[bamount]",
							"Salary" => ($rowHrCon['rate'] == "0.00") ? "" : $rowHrCon['rate'],
							"CompenCode" => $rowHrCon['wcomp_code'],
							"EmployeeId" => $rowHrCon['empsno'] ,
							"SSNNumber" => $ac_aced->decrypt($rowHrCon['ssn']),
							"AssignmentName" => stripslashes($rowHrCon['project']),
							"StartDate" =>  $assStdate,
							"Recruiter" =>  stripslashes(getRecruiter($rowHrCon['username'])),
							"SalesAgent" => stripslashes(getOwnerName($rowHrCon['owner'])),
							"Status" => $rowHrCon_status,
							"otbrate" => ($rowHrCon['otbrate'] == "0.00") ? "" : $rowHrCon[otbrate],
							"blocation" => stripslashes(getBranchLocation($rowHrCon['location'])),
							"otpayrate" => ($rowHrCon['otprate'] == "0.00") ? "" : $rowHrCon[otprate],
							"enddate" =>$assEnddate,
							"expenddate" => ($rowHrCon['exp_enddate'] == "00/00/0000" ) ? "" : $rowHrCon['exp_enddate'],
							"federalid" => getFederalid($rowHrCon['location']),
							"customer" => $hr_staffacc_array[2],
							"dblpayrate" =>($rowHrCon['dtprate'] == "0.00") ? "" : $rowHrCon[dtprate],
							"dblbrate" =>($rowHrCon['dtbrate'] == "0.00") ? "" : $rowHrCon[dtbrate],
							"jocreator" => stripslashes($rowHrCon['jobordercreator']),
							"commempname" => "",
							"commamount" => "",
							"commsource" => "",
							"placefee" => ($rowHrCon['placement_fee'] == "0.00" ) ? "" : $rowHrCon['placement_fee'],
							"margin" => ($rowHrCon['margin'] == "0.00" ) ? "" : number_format($hr_margin,2,".",""),
							"assignmentid" => $rowHrCon['idassignment'],
							"paypid" => $rowHrCon['payrollpid'],
							"credate" => ($rowHrCon['cdate'] == "00/00/0000" ) ? "" : $rowHrCon['cdate'], 
							"moduser" => getOwnerName($rowHrCon['muser']),
							"moddate" => ($rowHrCon['mdate'] == "00/00/0000" ) ? "" : $rowHrCon['mdate'],
							"assignmenttype" => stripslashes(seleAtype($rowHrCon['jtype'])),
							"jobtype" => $arrManage[$rowHrCon['jotype']],
							"subhours" => "",
							"apphours" => "",
							"burden" => $rowHrCon['burden'],
							"subdate" => "",
							"aprdate" => "",
							"tstrdate" => "",
							"tenddate" => "",
							"corpcode" => getCorpCode($corpCodeVal,$corpCodeType,$dbcon='db'),
							"pdlodging" => $diemLodgingValue,
							"pdmie" => $diemMIEValue,
							"pdtotal" => $diemTotalValue,
							"pdbillable" => $diemBillableType,
							"pdtaxable" =>  $diemTaxableType,
							"pdtotamt" => "",
							"alternateId" => ($rowHrCon['posid'] == 0)?"":$rowHrCon['posid'],
							"reghours" => "",
							"overtimehours" => "",
							"doubletimehours" => "",
							"billablehours" => "",
							"reportperson" => stripslashes($rowHrCon['reportto']),
							"contactperson" => stripslashes($rowHrCon['contact']),
							"assign_category" => getManage($rowHrCon['catid']),
							"assign_refcode" => $rowHrCon['refcode'],
							"assign_billcontact" => stripslashes(getBillContact($rowHrCon['bill_contact'])),
							"assign_billaddr" => stripslashes($hr_billaddress[0]),
							"assign_imethod" => $hr_staffacc_array[3],
							"assign_iterms" => $hr_staffacc_array[4],
							"assign_pterms" => $rowHrCon['pterms'],
							"assign_tsapproved" => $rowHrCon['tsapp'],
							"assign_ponumber" => $rowHrCon['po_num'],
							"assign_department" => $rowHrCon['department'],
							"assign_billterms" => $rowHrCon['bill_req'],
							"assign_sterms" => $rowHrCon['service_terms'],
							"jlocation" => stripslashes($hr_jlocation[0]),
							"assgnReasonCodes" => stripslashes($rowHrCon['reasons']),
							"assgnReason" => $reasonNote,
							"workersCompRate" => $wCompRatesArr[$wc],
							"HRMDepartment" => $rowHrCon['HRMDepartment'],
							"fedtaxallowance" => $rowHrCon['fedTaxAllowance'],
							"statetaxallowance" => $rowHrCon['stateTaxAllowance'],
							"filingstatus" => $rowHrCon['filingStatus'],
							"withholdingstate" => $stateArr[1],
							"paychkdelmethod" =>ucfirst(getManage($rowHrCon['payCheckDelMethod'])),
							"Role" => stripslashes($roleRes['roletitle']),
							"statetaxwithholdpercentage" => $rowHrCon['statetaxwithholdpercentage'],
							"markup" => $rowHrCon['markup'],							
							"empEmailId" => $rowHrCon['empEmailId'],
							"empPrimaryNumber" => (!empty($rowHrCon['empPrimaryNumberExt'])) ? $rowHrCon['empPrimaryNumber']." X ".$rowHrCon['empPrimaryNumberExt'] : $rowHrCon['empPrimaryNumber'],
							"empMobileNumber" => $rowHrCon['empMobileNumber'],
							"BillBurden" => $rowHrCon['bill_burden'],
							"RegularPayRate" => ($rowHrCon['RPayrate'] == "0.00") ? "" : $rowHrCon['RPayrate'],
							"RegularSalary" => ($rowHrCon['RSalary'] == "0.00") ? "" : $rowHrCon['RSalary'],
							"Regularotpayrate" => ($rowHrCon['over_time'] == "0.00") ? "" : $rowHrCon['over_time'],
							"Regulardblpayrate" => ($rowHrCon['double_rate_amt'] == "0.00") ? "" : $rowHrCon['double_rate_amt'],
							"Regularperdiemrate" => ($rowHrCon['RDiemTotal'] == "0.00") ? "" : $rowHrCon['RDiemTotal']." ".$rowHrCon['RDiemCurrency']." / ".$rowHrCon['RDiemPeriod'],
							"PayBurdenType" => $rowHrCon['PBType'],
							"BillBurdenType" => $rowHrCon['BBType'],
							"industry" =>stripslashes($rowHrCon['industry'])
						);
						$ii=0;

						for($q=0;$q<$count_sortarr;$q++)
						{
							for($f=0 ; $f<$count_fieldnames ; $f++)
							{
								if($sortarr[$q] == $fieldnames[$f])
								{
									

									$variable = $$variablenames[$f];
									if($variable[0]!="")
									{
										
										$data[$i][$ii] = $values_array[$fieldnames[$f]];
										$sslength_array[$fieldnames[$f]] = trim((strlen($values_array[$fieldnames[$f]]) <= 
										strlen($variable[2])) ? strlen($values_array[$fieldnames[$f]]) : (strlen($variable[2])+3));
										$ii++;
									}	
								}
							}
						}		
						if($count_sortarr)
							$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
						
						$data[$i][$ii] = "javascript:showAssig('$linkValue','$def_appsvr_domain')";
						$ii++;
				
						if(($varCompanyName[0]!="") || ($varFirstName[0]!="") || ($varMiddleName[0]!="") || ($varLastName[0]!="") || ($varPayRate[0]!="") || ($varBillRate[0] != "")|| ($varSalary[0] != "")|| ($varCompenCode[0] != "") || ($varEmployeeId[0] != "") || ($varSSN[0] != "") || ($varAssgName[0] != "") || ($varStartDate[0] != "") || ($varRecruiter[0] != "")  || ($varSalesAgent[0] != "")|| ($varStatus[0] != "") || ($otbrate[0] != "") || ($blocation[0] != "") || ($otpayrate[0] != "")|| ($enddate[0] != "")|| ($expenddate[0] != "") || ($federalid[0] != "") || ($customer[0] != "") ||($dblpayrate[0] != "") || ($dblbrate[0] != "") || ($jocreator[0] != "") || ($commempname[0] != "") || ($commamount[0] != "") || ($commsource[0] != "") || ($placefee[0] != "") || ($margin[0] != "") || ($assignmentid[0] != "") || ($assignmenttype[0] != "") || ($jobtype[0] != "") || ($subhours[0] != "") || ($apphours[0] != "") || ($burden[0] != "") || ($subdate[0]!="") || ($aprdate[0]!="") || ($tstrdate[0] != "") || ($tenddate[0] != "") || ($corpcode[0] != "")  || ($pdlodging[0] != "")|| ($pdmie[0] != "")|| ($pdtotal[0] != "")|| ($pdbillable[0] != "")|| ($pdtaxable[0] != "") || ($pdtotamt[0] != "") || ($alternateId[0] != "") || ($reghours[0] != "") || ($overtimehours[0] != "") || ($doubletimehours[0] != "") || ($billablehours[0] != "") || ($reportperson[0] != "") || ($contactperson[0] != "") || ($assign_category[0] != "") || ($assign_refcode[0] != "") || ($assign_billcontact[0] != "") || ($assign_billaddr[0] != "") || ($assign_imethod[0] != "") || ($assign_iterms[0] != "") || ($assign_pterms[0] != "") || ($assign_tsapproved[0] != "") || ($assign_ponumber[0] != "") || ($assign_department[0] != "") || ($assign_billterms[0] != "") || ($assign_sterms[0] != "") || ($jlocation[0] != "") ||  ($assgnReasonCodes[0] != "") || ($assgnReason[0] != "") || ($workersCompRate[0] != "") || ($HRMDepartment[0] != "")|| ($fedtaxallowance[0] != "")|| ($filingstatus[0]!= "") ||($withholdingstate[0]!="")|| ($statetaxallowance[0]!="")||($paychkdelmethod[0]!="") ||($Role[0]!="") || ($statetaxwithholdpercentage[0] != "") || ($Markup[0] != "") || ($empEmailId[0] != "") || ($empPrimaryNumber[0] != "") || ($empMobileNumber[0] != "") || ($BillBurden[0] != "") || ($RegularPayRate[0] != "") || ($RegularSalary[0] != "") || ($Regularotpayrate[0] != "") || ($Regulardblpayrate[0] != "") || ($Regularperdiemrate[0] != "") || ($PayBurdenType[0] != "") || ($BillBurdenType[0] != "")||($industry[0] != ""))
						{
							$data[$i][$ii]=$slength;
							$ii++;
						}			
				$i++;
				
			}				
		}
		
	} 
//-----------------------hrcon table   records completed----------------------//
	if($data=="")
	{
		$data=array();
		$data[0][0]="";
		$headval=array();
		$headval[0][0]="";
	}
	$rep_length=$i-1;
	require("rlibdata.php");
	
	if($defaction == "print")
		echo "<script>window.print(); window.setInterval('window.close();', 10000)</script>"; 
?>
