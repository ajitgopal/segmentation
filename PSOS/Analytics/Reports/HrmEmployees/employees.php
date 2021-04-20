<?php
	ob_start();
	require("global_reports.inc");
	/*
	TS ID: 4112   
	By: Swapna
	Date:March 12 2009
	Details: Added new column Email to the HRM new Employees Report.
	
	Modifed Date:18th march 2009
	Modified By: prasadd
	Purpose: To change employee query for hrcon-jobs left join  active condition according to HRM-Employee mngmnt query modification...
	TS Task Id:4144-support
	
	Modifed Date: April 06, 2009.
	Modified By: Kumar Raju K.
	Purpose: Added ethnicity and veteran status columns in new employee report.
	TS Task Id: (4204), (Prakash) In HRM - New Employee Report,need to add Ethnicity and veteran status.
	
	Modifed Date: April 13, 2009.
	Modified By: Fathima.
	Purpose: Modified code for proper displaying of veteran status.
	TS Task Id: (4204), (Prakash) In HRM - New Employee Report,need to add Ethnicity and veteran status.
	
	Modifed Date: August 11, 2010.
	Modified By: Piyush R.
	Purpose: Added new columns.
	Task Id: 5255 - (Prakash) Need to add columns, Federal tax Allowance - State Tax Allowance - Filing Status - Withholding State - Pay check delivery method  , to existing reports New Employee, Timesheet and Assignments reports.
	
	*/	
	$rlib_filename="hrmemployees.xml";
	require("rlib.inc");	
	
	$reportfrm=$reportfrm;
	require_once("functions.inc.php");
	$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	if($format=="")
		$format="html";
	$filename="HrmEmployees";
	$module="HrmEmp";
	
	if($reportfrm==2)
	{
		$module="Deductions";
	}
	
	//Code for  the report opened from MyReports  
	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$db);
		$vrow=mysql_fetch_row($rresult);
		$Analytics_HrmEmployees=$vrow[0];
		
		session_update("Analytics_HrmEmployees");
		
		$rdata=explode("|",$Analytics_HrmEmployees);
	}//MyReport code completed
	else if($defaction == "print")
	{
		$Analytics_HrmEmployees=$_REQUEST['Analytics_HrmEmployees'];
		$rdata=explode("|",$Analytics_HrmEmployees);
		$tab=$rdata[18];
	}
	else 
	{
		if($view == "predef"){
		$Analytics_HrmEmployees=$_REQUEST['Analytics_HrmEmployees'];
		}
		$rdata=explode("|",$Analytics_HrmEmployees);
		$tab=$rdata[18];
	}

	// Start of code for dynamic display of columns of note type
	$arrEarTypes = getAllEarTypes();
	$earTypesCount  = count($arrEarTypes);
	$datanumber = 82;        //this number must be changed whenever the rdata static count changes..IMPORTANT
	$loopear = 0;
	for($loopnote=0;$loopnote<$earTypesCount;$loopnote++)
	{
		//$arrEarTypes[$loopnote] = str_replace('---',' ',$arrEarTypes[$loopnote]);
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
			$$notetype = array($arrDynamicFieldNames[$loopnote],$dynDisplay_name,$headLine); 
		$arrNoteReferences[$loopnote] = $$notetype;
	}
	$countArrNoteReferences = count($arrNoteReferences);
	// end of code for dynamic display of columns of note type
	
	//If the Report  comes from  customize page ,fetching the values and kepping it in array.    
	if($tab=="addr" ||  ($view=="myreport" && $vrow[0]!=""))
	{	
		session_update("Analytics_HrmEmployees");
		
		$rdata=explode("|",$Analytics_HrmEmployees);
		$sortarr=explode("^",$rdata[19]);
		$type=$rdata[2];
		if($rdata[0]!="")
		{
			$date=explode('/',$rdata[0]);
			$date1=$date[2]."-".$date[0]."-".$date[1];

			$date=explode('/',$rdata[1]);
			$date2=$date[2]."-".$date[0]."-".$date[1];
		}
		else
		{
			$date1="";
			$date2="";
		}
		$rep_orient=$rdata[10]!="" ? $rdata[10] : "landscape";
		$rep_paper=$rdata[11]!="" ? $rdata[11] : "letter";
		$rep_company=$rdata[12]!="" ? stripslashes($rdata[12]) : "";
		$rep_header=$rdata[13]!="" ? stripslashes($rdata[13]) : "";
		$rep_title=$rdata[14]!="" ? stripslashes($rdata[14]) : "";
		$rep_date=$rdata[15]!="" ? $rdata[15] : "";
		$rep_page=$rdata[16]!="" ? $rdata[16] : "";
		$rep_footer=$rdata[17]!="" ? stripslashes($rdata[17]) : "";
		
		//For sorting order
		if($rdata[33]=="")
		{
			$rep_sortorder="ASC";
			$rep_sortcol="empfname";
		}
		else
		{
			$rep_sortorder=$rdata[22];
			$rep_sortcol=$rdata[33];
			$sortingorder = $rdata[33];
			$sortingorder_array = explode("^",$sortingorder);
		}
		
		//Data comming from customize page for each column
		$empid[0]=$rdata[4];
		$empssn[0]=$rdata[5];
		$empfname[0]=$rdata[3];
		$empmname[0]=$rdata[6];
		$emplname[0]=$rdata[7];
		$empaddr1[0]=$rdata[8];
		$empaddr2[0]=$rdata[9];
		$empcity[0]=$rdata[23];
		$empstate[0]=$rdata[24];
		$empzip[0]=$rdata[25];
		$empphone[0]=$rdata[26];
		$empmaritalst[0]=$rdata[27];
		$empdob[0]=$rdata[28];
		$empltax[0]=$rdata[29];
		$emphiredate[0]=$rdata[30];
		$empBranchLocation[0]=$rdata[34];
		$empClass[0]=$rdata[35];
		$empFedEmployee[0]=$rdata[36];
		$empFedCompany[0]=$rdata[37];
		$empStateEmployee[0]=$rdata[38];
		$empStateCompany[0]=$rdata[39];
		$empgender[0]=$rdata[40];
		$empfedtaxemployee[0]=$rdata[41];
		$empfedtaxcompany[0]=$rdata[42];
		$empstatetaxemployee[0]=$rdata[43];
		$empstatetaxcompany[0]=$rdata[44];
		$empsecurityemployee[0]=$rdata[45];
		$empsecuritycompany[0]=$rdata[46];
		$empmedicareemployee[0]=$rdata[47];
		$empmedicarecompany[0]=$rdata[48];
		$emplocalwithhold1employee[0]=$rdata[49];
		$emplocalwithhold1company[0]=$rdata[50];
		$emplw2employee[0]=$rdata[51];
		$emplw2company[0]=$rdata[52];
		$empnoallowclaim[0]=$rdata[53];
		$empstatetaxallowance[0]=$rdata[54];
		$emppayproviderid[0]=$rdata[55];
		$empFein[0]=$rdata[56];
		$empAbaAcc1[0]=$rdata[57];
		$empAccnumberAcc1[0]=$rdata[58];
		$empAccTypeAcc1[0]=$rdata[59];
		$empBanknameAcc1[0]=$rdata[60];
		$empAbaAcc2[0]=$rdata[61];
		$empAccnumberAcc2[0]=$rdata[62];
		$empAccTypeAcc2[0]=$rdata[63];
		$empBanknameAcc2[0]=$rdata[64];
		$empDoublePayrate[0]=$rdata[65];
		$empOvertimeRate[0]=$rdata[66];
		$empsalary[0]=$rdata[68];
		$empStatus[0]=$rdata[69];
		$filingStatus[0]=$rdata[70];
		$empMdate[0]=$rdata[71];		
		$empCreateduser[0]=$rdata[72];
		$empCrateddate[0]=$rdata[73];
		$empMuser[0]=$rdata[74];
		$pdlodging[0]=$rdata[75];
		$pdmie[0]=$rdata[76];
		$pdtotal[0]=$rdata[77];
		$pdbillable[0]=$rdata[78];
		$pdtaxable[0]=$rdata[79];
		$empEmail[0]=$rdata[80]; //added by swapna
		$empEthnicity[0]=$rdata[81]; //added by kumar raju
		$empVeterans[0]=$rdata[82]; //added by kumar raju
		$paychkdelmethod[0]=$rdata[84]; //added by Piyush R
		$empWithholdTaxState[0]=$rdata[85]; //added by Piyush R
		$filingStatusState[0]=$rdata[86];
		$empHrmDept[0]=$rdata[87];
		$empDisability[0]=$rdata[88];
		$qca[0]=$rdata[array_search('qca', $rdata)];
		$otherdependents[0]=$rdata[array_search('otherdependents', $rdata)];
		$claimtot[0]=$rdata[array_search('claimtot', $rdata)];
		$otherincome[0]=$rdata[array_search('otherincome', $rdata)];
		$deduct[0]=$rdata[array_search('deduct', $rdata)];
		$schooldist[0]=$rdata[array_search('schooldist', $rdata)];
				
		//column names and their corresponding selected values from the filters
		$filternames_array = explode('^',$rdata[31]);
		$filtervalues_array = explode('^',formateSlashes($rdata[32]));
	}
	else
	{
		if($reportfrm==2)
		{
			$sortarr=array("empFein","empid","empssn","emplname","empfname","empAbaAcc1","empAccnumberAcc1","empAccTypeAcc1","empBanknameAcc1","empAbaAcc2","empAccnumberAcc2","empAccTypeAcc2","empBanknameAcc2","empClass");
		}
		else
		{
			$sortarr=array("empFein","empid","empssn","empfname","empmname","emplname","empaddr1","empaddr2","empcity","empstate","empzip","empphone","empmaritalst","empgender","empdob","empStateEmployee","empnoallowclaim","empBranchLocation","emphiredate","empstatetaxemployee","empfedtaxemployee","empClass","empHrmDept");
		}
		
		$date1="";
		$date2="";
		$type="All";
		
		/*$filternames_array = explode('^',$sortarr);
		$filtervalues1 = "''^''^''^''^''^''^''^''^''^''^''^ALL^''^''^''^ALL^''^''^''";
	    $filtervalues1=explode('^',$filtervalues1);*/
		$sortingorder_array = $sortarr;

		$rep_orient="landscape";
		$rep_paper="letter";
		
		$rep_company=$companyname;
		$rep_header="Employees Report";
		$rep_title="All Employees";
		$rep_date="date";
		$rep_page="pageno";
		$rep_footer="";	

        $rep_sortorder="ASC";
		$rep_sortcol="empid";

		$empid[0]="empid";
		$empssn[0]="empssn";
		$empfname[0]="empfname";
		$empmname[0]="empmname";
		$emplname[0]="emplname";
		$empaddr1[0]="empaddr1";
		$empaddr2[0]="empaddr2";
		$empcity[0]="empcity";
		$empstate[0]="empstate";
		$empzip[0]="empzip";
		$empphone[0]="empphone";
		$empmaritalst[0]="empmaritalst";
		$empdob[0]="empdob";
		$empltax[0]="empltax";
		$emphiredate[0]="emphiredate";
		$empBranchLocation[0]="empBranchLocation";
		$empClass[0]="empClass";
		$empStatus[0]="Status";
		$empEmail[0]="empEmail";  //added by swapna
		$empEthnicity[0]="empEthnicity";  //added by kumar raju
		$empVeterans[0]="empVeterans";  //added by kumar raju
		$paychkdelmethod[0]="paychkdelmethod";  //added by Piyush R
		$empHrmDept[0]="empHrmDept";  
		$empDisability[0]="empDisability";
		$qca[0]="qca";
		$otherdependents[0]="otherdependents";
		$claimtot[0]="claimtot";
		$otherincome[0]="otherincome";
		$deduct[0]="deduct";
		$schooldist[0]="schooldist";
	}

	//Display names for each column heading 
	$empFedEmployee[1]="Federal Withholding (Employee)";
	$empFedCompany[1]="Federal Withholding (Company)";	
	$empStateCompany[1]="State Withholding (Company)";
	$empStateEmployee[1]="State Withholding (Employee)";
	$empfedtaxemployee[1]="Additional Federal Tax Amount withheld from check (Employee)";
	$empstatetaxemployee[1]="Additional State Tax Amount withheld from check (Employee)";
	$empnoallowclaim[1]="Federal Tax Allowances";
	$empfedtaxcompany[1]="Additional Federal Tax Amount withheld from check (Company)";	
	$empstatetaxcompany[1]="Additional State Tax Amount withheld from check (Company)";
	$empsecurityemployee[1]="Social Security (Employee)";
	$empsecuritycompany[1]="Social Security (Company)";
	$empmedicareemployee[1]="Medicare (Employee)";
	$empmedicarecompany[1]="Medicare (Company)";
	$emplocalwithhold1employee[1]="Local Withholding 1 (Employee)";
	$emplocalwithhold1company[1]="Local Withholding 1 (Company)";
	$emplw2employee[1]="Local Withholding 2 (Employee)";
	$emplw2company[1]="Local Withholding 2 (Company)";	
	$empstatetaxallowance[1]="State Tax Allowances";
	$filingStatus[1] = "Filing Status";
	$empWithholdTaxState[1] = "Withholding State";
	$filingStatusState[1] = "Filing Status(state) for Withholding";
	$qca[1]="Qualifying Children Amount";
	$otherdependents[1]="Other Dependents";
	$claimtot[1]="Claim Dependents Total";
	$otherincome[1]="Other income (not from jobs)";
	$deduct[1]="Deductions";
	$schooldist[1]="School District";

	$empid[1]="Employee Id";
	$empssn[1]="SSN";
	$empfname[1]="First Name";
	$empmname[1]="Middle Name";
	$emplname[1]="Last Name";
	$empaddr1[1]="Address 1";
	$empaddr2[1]="Address 2";
	$empcity[1]="City";
	$empstate[1]="State";
	$empzip[1]="Zip";
	$empphone[1]="Primary Phone";
	$empmaritalst[1]="Marital Status";
	$empdob[1]="Date of Birth";
	$empltax[1]="Tax";
	$emphiredate[1]="Date of Hire";
	$empBranchLocation[1]="HRM Location";
	$empClass[1]="Employee Type";
	$empgender[1]="Gender";	
	$emppayproviderid[1]="Payroll Provider ID#";
	$empFein[1]="Federal ID";
	$empAbaAcc1[1]="PrimaryABA";
	$empAccnumberAcc1[1]="PrimaryAcctNo";
	$empAccTypeAcc1[1]="PrimaryAcctType";
	$empBanknameAcc1[1]="PrimaryBankName";
	$empAbaAcc2[1]="Acct2ABA";
	$empAccnumberAcc2[1]="Acct2AcctNo";
	$empAccTypeAcc2[1]="Acct2AcctType";
	$empBanknameAcc2[1]="Acct2BankName";
	$empDoublePayrate[1]="Double Time  Rate";
	$empOvertimeRate[1]="Overtime Rate";	
	$empsalary[1]="Salary";
	$empStatus[1]="Status";
	$empMdate[1]="Modified Date";
	$empCreateduser[1]="Created User";
	$empCrateddate[1]="Created Date";
	$empMuser[1]="Modified User";
	$empEmail[1]="Email"; //added by swapna
	$empEthnicity[1]="Ethnicity"; //added by kumar raju
	$empVeterans[1]="Veterans Status"; //added by kumar raju
	$paychkdelmethod[1]="Pay Check Delivery Method"; //added by Piyush R
	$empHrmDept[1]="HRM Department"; 
	$empDisability[1]="Disability";
	
	//these are for Per Diem
	$pdlodging[1] = "Lodging";
	$pdmie[1] = "M&IE";
	$pdtotal[1] = "Per Diem - Total";
	$pdbillable[1] = "Per Diem - Billable";
	$pdtaxable[1] = "Per Diem - Taxable";
	//ends here
	
	//underline for each column heading
	$empFedEmployee[2]="-------------------------------";
	$empFedCompany[2]="------------------------------";
	$empStateCompany[2]="-----------------------------";
	//$empWithholdTaxState[2]="----------------------";
	$empnoallowclaim[2]="----------------------------";
	$empStateEmployee[2]="-------------------------------";
	$empfedtaxemployee[2]="-------------------------------------------------------------";
	$empstatetaxemployee[2]="-----------------------------------------------------------";
	$empfedtaxcompany[2]="------------------------------------------------------------";
	$empstatetaxcompany[2]="----------------------------------------------------------";
	$empsecurityemployee[2]="---------------------------";
	$empsecuritycompany[2]="--------------------------";
	$empmedicareemployee[2]="--------------------";
	$empmedicarecompany[2]="-------------------";
	$emplocalwithhold1employee[2]="-------------------------------";
	$emplocalwithhold1company[2]="------------------------------";
	$emplw2employee[2]="-------------------------------------";
	$emplw2company[2]="-----------------------------";	
	$empstatetaxallowance[2]="--------------------------";
	$filingStatus[2] = "------------------------------------";
	$empWithholdTaxState[2] = "------------------------------------";
	$filingStatusState[2] = "------------------------------------";
	$qca[2]="------------------------------------";
	$otherdependents[2]="-------------------";
	$claimtot[2]="-----------------------------";
	$otherincome[2]="-------------------------------";
	$deduct[2]="-------------------";
	$schooldist[2]="-------------------";

    $empid[2]="------------";
	$empssn[2]="------------";
	$empfname[2]="-------------------------";
	$empmname[2]="-------------------------";
	$emplname[2]="-------------------------";
	$empaddr1[2]="--------------------------------------";
	$empaddr2[2]="--------------------------------------";
	$empcity[2]="-----------------------";
	$empstate[2]="----------------------";
	$empzip[2]="----------------";
	$empphone[2]="---------------";
	$empmaritalst[2]="--------------";
	$empdob[2]="-------------";
	$empltax[2]="-------";
	$emphiredate[2]="------------";
	$empBranchLocation[2]="-------------------------------------------------";
	$empClass[2]="-----------------------";
	$empgender[2]="------";	
	$emppayproviderid[2]="---------------------";
	$empFein[2]="---------------";
	$empAbaAcc1[2]="-----------";
	$empAccnumberAcc1[2]="--------------";
	$empAccTypeAcc1[2]="----------------";
	$empBanknameAcc1[2]="----------------";
	$empAbaAcc2[2]="---------";
	$empAccnumberAcc2[2]="------------";
	$empAccTypeAcc2[2]="--------------";
	$empBanknameAcc2[2]="--------------";
	$empDoublePayrate[2]="---------------------";
	$empOvertimeRate[2]="-----------------------";	
	$empsalary[2]="------------------";
	$empStatus[2]="----------";
	$empMdate[2]="------------------------";
	$empCreateduser[2]="---------------------------------";
	$empCrateddate[2]="------------------------";
	$empMuser[2]="---------------------------------";
	$empEmail[2]="---------------------------------"; //added by swapna
	$empEthnicity[2]="---------------------------------"; //added by kumar raju
	$empVeterans[2]="---------------------------------"; //added by kumar raju
	$paychkdelmethod[2]="---------------------------------"; //added by piyush r
	$empHrmDept[2]="---------------------------------"; 
	$empDisability[2]="---------------------------------";
	
	//these are for Per Diem
	$pdlodging[2] = "-----------------------------";
	$pdmie[2] = "-----------------------------";
	$pdtotal[2] = "-----------------------------";
	$pdbillable[2] = "-----------------------";
	$pdtaxable[2] = "-----------------------";
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
	for($q=0 ; $q< $sortarry_count ; $q++)
	{ 
		$variable = $$sortarr[$q] ;
		if($variable[0]!="")
		{ 
			$data[0][$k] 	= $variable[0];
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
	
	$groupbyCond="group by el.sno"; //Group by condition intially should have
	
	if(in_array("empStatus",$sortarr))
		$empStatusCond=" and empterminatedNew='N'";

	//order by preparation...
	$orderBy = "";
	
	$sortingorder_array_count = count($sortingorder_array);

	for($scount = 0;$scount < $sortingorder_array_count; $scount++)
	{
		$fieldArr = getOrderByName($sortingorder_array[$scount]);
		$getIndex = $fieldArr[1];
		$orderBy = ($orderBy == "") ? " ORDER BY ".$getIndex." ".$rep_sortorder : $orderBy.", ".$getIndex." ".$rep_sortorder;
	}
	$sortingorder_array = "";//Dont define as array....to avoid sorting in rlib.

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


		if(($filternames_array[$f] == "empmaritalst") || ($filternames_array[$f] == "empltax")|| ($filternames_array[$f] == "empgender") || ($filternames_array[$f] == "empAccTypeAcc1")|| ($filternames_array[$f] == "empAccTypeAcc2") ) 
		{
		if($filtervalues_array[$f] != "ALL")
		$accstr=$accstr." and $filedtable.$fieldname like '%$filtervalues_array[$f]%' ";
		}
		else if($filternames_array[$f] == "empClass")
		{ 
			if($filtervalues_array[$f] != "ALL")
			{
				 $accstr=$accstr." and $filedtable.$fieldname = '$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "empStatus")
		{
			$empStatusCond="";
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and empterminatedNew='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "empHrmDept")
		{
			$feidArrD = explode('!#!',$filtervalues_array[$f]);
			
			$D =0;
	
			if(!in_array('ALL',$feidArrD))
			{
				if($feidArrD[0] != '')
				{
					$filterCondString_D = " AND (";
					
					foreach($feidArrD as $deptcoded)
					{
						if($D==0)
							$filterCondString_D .= "dt.deptname = '".addslashes($deptcoded)."'";
						else	
							$filterCondString_D .= " OR dt.deptname = '".addslashes($deptcoded)."'";

						$D++;
					}
					
					$filterCondString_D .= ")";
				}
			}

			$accstr = $accstr.$filterCondString_D;
		}
		else if($filternames_array[$f] == "filingStatus")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hw.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "filingStatusState")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hw.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "pdbillable")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hc.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "pdtaxable")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hc.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		//added by swapna for Email
		else if($filternames_array[$f] == "empEmail")
		{
			if($filtervalues_array[$f] != "")
			{
				$accstr=$accstr." and hg.$fieldname like '%$filtervalues_array[$f]%' ";
			}
		}
		//ended by swapna
		else if($filternames_array[$f] == "empnoallowclaim" || $filternames_array[$f] == "empstatetaxallowance" || $filternames_array[$f] == "pdlodging"|| $filternames_array[$f] == "pdmie"|| $filternames_array[$f] == "pdtotal")
		{
			if($filtervalues_array[$f]!="*" && $filtervalues_array[$f]!="")
			{
				$fedStatefields=explode("*",$fieldname); 
				$numberFiledname=$fedStatefields[0];
				$exemptFieldname=$fedStatefields[1];
				
				if($filtervalues_array[$f]=="Exempt_$filternames_array[$f]")
				{
					 $accstr=$accstr." and $filedtable.$exemptFieldname ='Y' ";
					 $new_Condition = "  and  $filedtable.$exemptFieldname!='Y'";
				}
				else
				{
					$ranges = explode("*",$filtervalues_array[$f]);
					$maxvalue = $ranges[1];
					$minvalue = $ranges[0];
					
					if($maxvalue!="" && $minvalue!="")
					$accstr=$accstr." and  ".$filedtable.".".$numberFiledname." <=".(double) $maxvalue." and ".$filedtable.".".$numberFiledname." >=".(double) $minvalue;
					elseif($maxvalue!="")
					$accstr=$accstr." and  ".$filedtable.".".$numberFiledname." <=".(double) $maxvalue .$new_Condition;
					elseif($minvalue!="")
					$accstr=$accstr." and  ".$filedtable.".".$numberFiledname." >=".(double) $minvalue;
				}
			}
		}
		
		else if($filternames_array[$f] == "empFedEmployee" || $filternames_array[$f] == "empStateEmployee" || $filternames_array[$f] == "empFedCompany" || $filternames_array[$f] == "empStateCompany" || $filternames_array[$f] == "empfedtaxemployee" || $filternames_array[$f] == "empfedtaxcompany" || $filternames_array[$f] == "empstatetaxemployee"  || $filternames_array[$f] == "empstatetaxcompany" || $filternames_array[$f] == "empsecurityemployee"  || $filternames_array[$f] == "empsecuritycompany" || $filternames_array[$f] == "empmedicareemployee" || $filternames_array[$f] == "empmedicarecompany" || $filternames_array[$f] == "emplocalwithhold1employee" || $filternames_array[$f] == "emplocalwithhold1company" || $filternames_array[$f] == "emplw2employee" || $filternames_array[$f] == "emplw2company" || $filternames_array[$f] == "empid" )
		{ //All the columns ,having the number values 
			
			if($filternames_array[$f] == "empid") 
				$tableName="el";
			else if($filternames_array[$f] == "empOvertimeRate")
				$tableName="hr";
			else
				$tableName="hw";
			$ranges = explode("*",$filtervalues_array[$f]);
			$maxvalue = $ranges[1];
			$minvalue = $ranges[0];
			
			if($filtervalues_array[$f] != "ALL" && $filtervalues_array[$f] != "")
			{		
			  if($maxvalue!="" && $minvalue!="")
				$accstr=$accstr." and  ".$tableName.".".$fieldname." <=".(double) $maxvalue." and ".$tableName.".".$fieldname." >=".(double) $minvalue;
			  elseif($maxvalue!="")
				$accstr=$accstr." and  ".$tableName.".".$fieldname." <=".(double) $maxvalue;
			  elseif($minvalue!="")
				$accstr=$accstr." and  ".$tableName.".".$fieldname." >=".(double) $minvalue;
			  
			}
		}
		
		else if(($filternames_array[$f] == "empdob") || ($filternames_array[$f] == "emphiredate")) //Date of birth
		{
		 	if($filtervalues_array[$f]!='*')
			{
				$tabFieldname=$filedtable.".".$fieldname;
				$filterDate=explode("*",$filtervalues_array[$f]); 
				$fromDate=$filterDate[0];
				$toDate=$filterDate[1];
				if($fromDate!='')
					$fromDate=date("Y-m-d",strtotime($fromDate));
				else
					$fromDate='';
				if($toDate!='')
					$toDate=date("Y-m-d",strtotime($toDate));
				else
					$toDate='';
				
				$dob_formate=" ".tzRetQueryStringSTRTODate("$tabFieldname","%m-%d-%Y","YMDDate","-")." "; 
				if($fromDate!='' && $toDate!='') 
				   $accstr=$accstr." and $dob_formate  between '".$fromDate."' and  '".$toDate."'";
				else if($fromDate!='' && $toDate=='' )
				   $accstr=$accstr." and  $dob_formate >='".$fromDate."'";
				else if($fromDate=='' && $toDate!='' )
				   $accstr=$accstr." and $dob_formate <='".$toDate."'";
		 	}
		
		}
		else if($filternames_array[$f] == "empMdate" || $filternames_array[$f] == "empCrateddate")
		{
				$tabFieldname="el.".$fieldname;
				$filterDate=explode("*",$filtervalues_array[$f]); 
				$fromDate=$filterDate[0];
				$toDate=$filterDate[1];
				if($fromDate!='')
					$fromDate=date("Y-m-d",strtotime($fromDate));
				else
					$fromDate='';
				if($toDate!='')
					$toDate=date("Y-m-d",strtotime($toDate));
				else
					$toDate='';	
			
			if($fromDate != "" && $toDate != "") 
				$accstr=$accstr." and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." >= '".$fromDate."' and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." <= '".$toDate."'";
			elseif($fromDate != "" && $toDate == "")
				$accstr=$accstr."  and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." >= '".$fromDate."' ";
			elseif($toDate != "" && $fromDate == "")
				$accstr=$accstr." and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." <= '".$toDate."' ";
		}
		else if(($filternames_array[$f] == "empBranchLocation") || ($filternames_array[$f] == "empFein") || ($filternames_array[$f] == "empDoublePayrate") || ($filternames_array[$f] == "empsalary")|| ($filternames_array[$f] == "empOvertimeRate") || ($filternames_array[$f] == "empCreateduser")  || ($filternames_array[$f] == "empMuser") )
		{ //These columns have separate filter conditions below
		}
		else if(in_array($filternames_array[$f],$arrDynamicFieldNames))
		{
		}
		else if($filternames_array[$f] == "empssn" && $filtervalues_array[$f] != "")
		{
			$accstr=$accstr." AND REPLACE($filedtable.$fieldname,'-','') like '%$filtervalues_array[$f]%' ";
		}
		else if($filternames_array[$f] == "empEthnicity")
		{
		}
		else if($filternames_array[$f] == "empVeterans")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hp.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "empDisability")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hp.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "qca"||$filternames_array[$f] == "otherdependents"||$filternames_array[$f] == "claimtot"||$filternames_array[$f] == "otherincome"||$filternames_array[$f] == "deduct"||$filternames_array[$f] == "schooldist")
		{
			if($filtervalues_array[$f]){
				$accstr=$accstr." and $filedtable.$fieldname ='$filtervalues_array[$f]' ";
			}
		}

		else
		{
			if($filtervalues_array[$f])
				$accstr=$accstr." and $filedtable.$fieldname like '%$filtervalues_array[$f]%' ";
		}
	}//End of Filter conditions

	//$department_dynStr = " AND FIND_IN_SET('".$username."',dt.permission)>0 ";
	$department_dynStr = " AND dt.sno !='0' AND dt.sno IN ({$deptAccesSno}) ";
	//if($accountingpref!='NO' && strpos($accountingpref,'11')>0)
	/*if($accountingpref!='NO' && chkUserPref($accountingpref,"11"))// New check implemented July 09, 2010 Piyush R chkUserPref
	{
		$department_dynStr = "";
	}*/

	//prasadd-changed query for hrcon_jobs left join  active condition...
	//Query to fetch all the Employees details
$qryEmp="SELECT el.sno,hc.emp_id,REPLACE(hp.ssn,'-',''),hg.fname,hg.mname,hg.lname,hg.address1,hg.address2,hg.city,hg.state,hg.zip,IF(hg.wphone='---','',hg.wphone),hp.m_status,hp.d_birth,hw.tax,".tzRetQueryStringSTRTODate("hc.date_hire","%m-%d-%Y","Date","-").",hc.location,hc.emptype,hw.fwh,hw.cfwh,hw.swh,hw.cswh,hp.hp_gender,hw.aftaw,hw.caftaw,hw.astaw,hw.castaw,hw.sswh,hw.csswh,hw.mwh,hw.cmwh, hw.localw1_amt,hw.clocalw1_amt,hw.localw2_amt,hw.clocalw2_amt,hw.tnum,hw.tstatetax,hw.payrollpid,hr.client,hd.bankrtno,hd.bankacno,hd.acc1_type,hd.bankname,hd.acc2_bankrtno ,hd.acc2_bankacno,hd.acc2_type,hd.acc2_bankname,hr.pamount,hr.pcurrency ,hr.pperiod ,hc.assign_double,hr.double_brate_amt,hr.double_brate_curr ,hr.double_brate_period,hc.double_rate_amt ,hc.double_brate_curr hrcompenbillcurr,hc.double_rate_period,hr.rate,hr.rateper,hr.rateperiod,hr.jotype,hc.pay_assign,hc.salary,hc.shper,hc.salper,hc.assign_overtime,hc.over_time,hc.ot_currency,hc.ot_period,el.username,hw.federal_exempt,hw.state_exempt,el.empterminated,hw.fstatus,".tzRetQueryStringDTime("el.mtime","Date","/").",el.approveuser,".tzRetQueryStringDTime("el.stime","Date","/").",el.muser,hc.diem_lodging,hc.diem_mie,hc.diem_total,hc.diem_billable,hc.diem_taxable,hc.diem_currency,hc.diem_period,hg.email,hp.ethnicity,hp.veteran_status, hd.delivery_method,hw.state_withholding,hw.fsstatus,dt.deptname,empterminatedNew,hp.disability
,hw.qualify_child_amt,hw.other_dependents_amt,hw.claim_dependents_total,hw.other_income_amt,hw.deduction_amt,hw.school_dist
	FROM hrcon_general hg,hrcon_w4 hw,emp_list el
	LEFT JOIN (SELECT emp_list.username , IF(
                  UNIX_TIMESTAMP(DATE_FORMAT(emp_list.tdate, '%Y-%m-%d')) <=
                     UNIX_TIMESTAMP(),
                  'Y',
                  'N') as empterminatedNew
          FROM emp_list ) as temptable ON el.username = temptable.username
	LEFT JOIN hrcon_jobs hr ON (el.username=hr.username and hr.ustatus='active')
	LEFT JOIN hrcon_compen hc ON el.username = hc.username  
	LEFT JOIN contact_manage cm ON cm.serial_no = hc.location  
	LEFT JOIN hrcon_contribute hcb ON hcb.username = el.username
	LEFT JOIN hrcon_personal hp ON  el.username =hp.username and hp.ustatus = 'active'
	LEFT JOIN manage m ON  m.sno = hp.disability and m.status = 'Y'
	LEFT JOIN hrcon_deposit  hd ON  el.username =hd.username and hd.ustatus = 'active'  
	LEFT JOIN department dt ON hc.dept = dt.sno 
	WHERE el.username = hg.username  AND el.username = hw.username AND el.username =hc.username	AND hw.ustatus = 'active' AND hg.ustatus = 'active' AND hc.ustatus = 'active' AND el.lstatus NOT IN('DA','INACTIVE') $department_dynStr  $empIntDirCond  $empStatusCond  $accstr ";
	$qryEmp.=" $groupbyCond $orderBy";
	$resEmp=mysql_query($qryEmp,$rptdb);
	
	$indexLocation = array_search("empBranchLocation",$filternames_array);
	$indexFederalid = array_search("empFein",$filternames_array);
	$strLocationIds = getStringFilterIds("empBranchLocation",$filtervalues_array[$indexLocation]);
	$strFederalIds = getStringFilterIds("empFein",$filtervalues_array[$indexFederalid]);
	$indexFein = array_search("empFein",$filternames_array);
	$indexCreatedUser = array_search("empCreateduser",$filternames_array);
   	$indexModifiedUser = array_search("empMuser",$filternames_array);
	$indexDiemBillableType = array_search("pdbillable",$filternames_array);
	$indexDiemTaxableType = array_search("pdtaxable",$filternames_array);
	$indexEmpEthnicity = array_search("empEthnicity",$filternames_array);
	$strEmpEthnicityIds = getStringFilterIds("empEthnicity",$filtervalues_array[$indexEmpEthnicity]);
	
	//Satrt of Fecting the data for dynamic columns
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
	
	$fieldnames = array("empDoublePayrate","empsalary","empOvertimeRate");
	
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
		$doublePayRateRate = "";
		$perDiemCurrencyPeriod = $arr[83]." / ".$arr[84]; 
		
		//Data for class
		if(getManage($arr[17])=="Direct")
			$empCalssType="Internal Direct";
		else if(getManage($arr[17])=="Temp/Contract to Direct")
			$empCalssType="Temp/Contract";
		else
			$empCalssType=getManage($arr[17]);	
		
		$locationFeid = getFederalid($arr[16]);
		//Billable and Taxable types based on Job types
		if($empCalssType == 'Internal Direct' || $empCalssType == 'Direct')
		{
			$diemBillableType = '';
			$diemTaxableType = '';
			$diemLodgingValue = '';
			$diemMIEValue = '';
			$diemTotalValue = '';
		}
		else
		{
			$diemBillableType = $arrayBillableType["$arr[81]"];
			$diemTaxableType = $arrayTaxableType["$arr[82]"];
			$diemLodgingValue = ($arr[78] > 0 && $arr[78] != '')?($arr[78]." ".$perDiemCurrencyPeriod):'';
			$diemMIEValue = ($arr[79] > 0 && $arr[79] != '')?($arr[79]." ".$perDiemCurrencyPeriod):'';
			$diemTotalValue = ($arr[80] > 0 && $arr[80] != '')?($arr[80]." ".$perDiemCurrencyPeriod):'';
		}
		//ends here
		
		//start of code for filtering 
		//filter conditions for  HRM Location and Federal ID as they needs to fetch from 2 different tables
		if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
		{
			if(in_array("empBranchLocation",$filternames_array) && ($filtervalues_array[$indexLocation] != '') && 
			($filtervalues_array[$indexLocation] != "ALL"))
			{
				$empblocation =  (in_array($arr[16],$strLocationIds)) ? "1" : "0";
				if(!$empblocation == "1")
			 		continue;
			}
			if(in_array("empFein",$filternames_array) && ($filtervalues_array[$indexFederalid] != '') && 
			($filtervalues_array[$indexFederalid] != "ALL"))
			{
				$empFeid =  (in_array($arr[16],$strFederalIds)) ? "1" : "0";
				if(!$empFeid == "1")
			 		continue;
			}
			if(in_array("empCreateduser",$filternames_array) && ($filtervalues_array[$indexCreatedUser] != '') && 
			($filtervalues_array[$indexCreatedUser] != "ALL"))
			{
				$strIds = getStringFilterIds("empCreateduser",$filtervalues_array[$indexCreatedUser]);
				$usercond =  (in_array($arr[75],$strIds)) ? "1" : "0";
				if(!$usercond == "1")
				continue;
			}	
			if(in_array("empMuser",$filternames_array) && ($filtervalues_array[$indexModifiedUser] != '') && 
			($filtervalues_array[$indexModifiedUser] != "ALL"))
			{
				$strIds = getStringFilterIds("empMuser",$filtervalues_array[$indexModifiedUser]);
				$musercond =  (in_array($arr[77],$strIds)) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			if(in_array("pdbillable",$filternames_array) && ($filtervalues_array[$indexDiemBillableType] != '') && 
			($filtervalues_array[$indexDiemBillableType] != "ALL"))
			{
				$musercond = ($arrayBillableType["$filtervalues_array[$indexDiemBillableType]"] == $diemBillableType) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			if(in_array("pdtaxable",$filternames_array) && ($filtervalues_array[$indexDiemTaxableType] != '') && 
			($filtervalues_array[$indexDiemTaxableType] != "ALL"))
			{
				$musercond = ($arrayTaxableType["$filtervalues_array[$indexDiemTaxableType]"] == $diemTaxableType) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			if(in_array("empEthnicity",$filternames_array) && ($filtervalues_array[$indexEmpEthnicity] != ''))
			{
				$musercond =  (in_array($arr[86],$strEmpEthnicityIds)) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			
			if($v1 == 0)
				$v1 = 1;

			//Filter Condition for dynamic columns
			for($dcol=0;$dcol<count($filternames_array);$dcol++)
			{
				if($v1==1)		
				{
					if(in_array($arrDynamicFieldNames[$dcol],$filternames_array))
					{
						$indexDyncol = array_search($arrDynamicFieldNames[$dcol],$filternames_array);
						$benColval=$filtervalues_array[$indexDyncol];  
						
						if($benColval != "*" && $benColval !='')
						{
							$rangesBen = explode("*",$benColval);
							$minvalueBen = $rangesBen[0];
							$maxvalueBen = $rangesBen[1];
							
							$dbaseVal = $dynamic_values[$arrDynamicFieldNames[$dcol]][$arr[69]];
							
							if($minvalueBen != "" && $maxvalueBen != "")
								$v1 =  (($dbaseVal >=$minvalueBen) && ($dbaseVal <= $maxvalueBen)) ? "1" : "0";
							elseif($minvalueBen != "" && $maxvalueBen == "")
								$v1 = ( ($dbaseVal >= $minvalueBen) ) ? "1" : "0";
							elseif($maxvalueBen != "" && $minvalueBen == "")
								$v1 =  ( ($dbaseVal <= $maxvalueBen) ) ? "1" : "0";
							elseif($maxvalueBen == "" && $minvalueBen == "")
								$v1 =  1; 
						}
					}
					else
						$v1 =  1;
				}
				else
					break;
				
		   }
		}
		
		//Filter conditions for  Dobletime PayRate,salary $ Overtime Rate as they need to fetch  from 2 tables based on conditions
		$assnJobType=getManage($arr[60]);//assignment type
		
		//Assignening value for  Double Time Pay Rate
		if($arr[50]=="Y")			
		{	
			if($assnJobType=="Direct" || $assnJobType=="Internal Direct")
				$doublePayRateRate = $arr[57];
			else
				$doublePayRateRate = $arr[47];
		}
		else
			$doublePayRateRate = $arr[54];
	   //Assignening value for Salary
	   if($arr[61]=="Y")			
		{	
			if($assnJobType=="Direct" || $assnJobType=="Internal Direct")
				$salary = $arr[57];
			else
				$salary = $arr[47];
		}
		else
			$salary = $arr[62];
			
		//Assignening value for Overtime Rate
	   if($arr[65]=="Y")			
		{	
			if($assnJobType=="Direct" || $assnJobType=="Internal Direct")
				$overTimeRate = $arr[57];
			else
				$overTimeRate = $arr[47];
		}
		else
			$overTimeRate = $arr[66];
	
		if($v == 0)
		  $v = 1;
		if($tab == "addr")
		{
			for($j=0;$j<count($fieldnames);$j++)
			{
				if($v == 1)
				{
					if(in_array($fieldnames[$j],$filternames_array))
					{
						$index = array_search($fieldnames[$j],$filternames_array);
						switch($fieldnames[$j])
						{
							case 'empDoublePayrate' :
							$values['empDoublePayrate'] = $doublePayRateRate;
							break;
							case 'empsalary' :
							$values['empsalary'] = $salary;
							break;
							case 'empOvertimeRate' :
							$values['empOvertimeRate'] = $overTimeRate;
							break;
						 
						}
						$ranges = explode("*",$filtervalues_array[$index]);
						$minvalue = $ranges[0];
						$maxvalue = $ranges[1]; 
						
						if($minvalue != "" && $maxvalue != "")
							$v =  (($values[$fieldnames[$j]] >=$minvalue) && ($values[$fieldnames[$j]] <= $maxvalue)) ? "1" : "0";
						elseif($minvalue != "" && $maxvalue == "")
							$v = ( ($values[$fieldnames[$j]] >= $minvalue) ) ? "1" : "0";
						elseif($maxvalue != "" && $minvalue == "")
							$v =  (  ($values[$fieldnames[$j]] <= $maxvalue) ) ? "1" : "0";
						elseif($maxvalue == "" && $minvalue == "")
							$v =  1;
					}
					else
						$v = 1;	
	
				}
				else
				break;
			}
		}
		else
		{	
			$values['empDoublePayrate'] = $doublePayRateRate;
			$values['empsalary'] = $salary;
			$values['empOvertimeRate'] = $overTimeRate;
		}
		
		//-------------Code for assigning the each column's data to the $data---------- 
		//Preparing Data for DOB
		$bdate='';
		$arraybdate='';
		if(in_array("empdob",$sortarr))
		{
			if($arr[13]!="00/00/0000" && $arr[13]!="00-00-0000")
			{
				$bdate=explode("-",$arr[13]);
				$arraybdate[0]=$bdate[2];
				$arraybdate[1]=$bdate[0];
				$arraybdate[2]=$bdate[1];
				$birth_date=implode("/",$arraybdate);
				$birthDate=str_replace("-","/",$arr[13]);
			}
			else
				$birthDate="";
		}
		
		//Data for Total Federal Tax Allowances
		if($arr[70]=='Y')
			$totFedtaxallow='Exempt';//$totFedtaxallow=99; modified Piyush R
		else
			/* 18-Jul-2013 $totFedtaxallow=($arr[35]!=0.00)? $arr[35] : ""; */
			$totFedtaxallow=$arr[35];
		
		//Data for Total State Tax Allowances
		if($arr[71]=='Y')
			$totStatetaxallow='Exempt';//$totStatetaxallow=99; modified Piyush R
		else
			/* 18-Jul-2013 $totStatetaxallow=($arr[36]!=0.00)? $arr[36] : "";*/
			$totStatetaxallow=$arr[36];
	    
		//Data for Gender
		if($arr[22]=="F")
			$gender="Female";
		else if($arr[22]=="M")	
			 $gender="Male"; 
		else
			$gender=""; 
		
		//PrimayAcctType
		if($arr[41]=="CHECKING")
			$bankAccType1="Chk";
		else if($arr[41]=="SAVINGS")
			$bankAccType1="Sav";
		else
			$bankAccType1="";
		
		//Acc2AcctType
		if($arr[45]=="CHECKING")
			$bankAccType2="Chk";
		else if($arr[45]=="SAVINGS")
			$bankAccType2="Sav";
		else
			$bankAccType2="";
		
		$state=getStateAbbrFromId($arr[89]);
		$stateArr = explode('|',$state);
		//Array for all column's data
		$values_array = array(
							"empid" => $arr[0],
							"empssn" => $ac_aced->decrypt($arr[2]),
							"empfname" => $arr[3], 
							"empmname" => $arr[4] ,
							"emplname" => $arr[5] , 
							"empaddr1" => $arr[6],
							"empaddr2" => $arr[7],
							"empcity" =>  $arr[8],
							"empstate" => $arr[9],
							"empzip" =>   $arr[10],
							"empphone" => $arr[11],
							"empmaritalst" =>$arr[12],
							"empdob" => get_standard_dateFormat($ac_aced->decrypt($birthDate), 'm-d-Y','m/d/Y'),
							"empltax" => $arr[14],
							"emphiredate" => ($arr[15]!="00/00/0000" && $arr[15]!="00-00-0000") ? str_replace("-","/",$arr[15]) : "",
							"empBranchLocation" => trim(getBranchLocation($arr[16]),','),
							"empClass" => $empCalssType,
							"empFedEmployee" =>($arr[18]!=0.00)? $arr[18] : "" ,
							"empFedCompany" =>  ($arr[19]!=0.00)? $arr[19] : "",
							"empStateEmployee" =>($arr[20]!=0.00)? $arr[20] : "",
							"empStateCompany" =>($arr[21]!=0.00)? $arr[21] : "",
							"empgender" => $gender,
							"empfedtaxemployee" =>($arr[23]!=0.00)? $arr[23] : "",
							"empfedtaxcompany" =>($arr[24]!=0.00)? $arr[24] : "",
							"empstatetaxemployee" =>($arr[25]!=0.00)? $arr[25] : "",
							"empstatetaxcompany" =>($arr[26]!=0.00)? $arr[26] : "",
							"empsecurityemployee" =>($arr[27]!=0.00)? $arr[27] : "",
							"empsecuritycompany" =>($arr[28]!=0.00)? $arr[28] : "",
							"empmedicareemployee" =>($arr[29]!=0.00)? $arr[29] : "",
							"empmedicarecompany" =>($arr[30]!=0.00)? $arr[30] : "",
							"emplocalwithhold1employee" =>($arr[31]!=0.00)? $arr[31] : "",
							"emplocalwithhold1company" =>($arr[32]!=0.00)? $arr[32] : "",
							"emplw2employee" =>($arr[33]!=0.00)? $arr[33] : "",
							"emplw2company" =>($arr[34]!=0.00)? $arr[34] : "",
							"empnoallowclaim" =>$totFedtaxallow,
							"empstatetaxallowance" =>$totStatetaxallow,
							"emppayproviderid" =>$arr[37],
							"empFein" =>getFederalId($arr[16]),
							"empAbaAcc1" =>$ac_aced->decrypt($arr[39]),
							"empAccnumberAcc1" =>$ac_aced->decrypt($arr[40]),
							"empAccTypeAcc1" =>$bankAccType1,
							"empBanknameAcc1" =>$arr[42],
							"empAbaAcc2" =>$ac_aced->decrypt($arr[43]),
							"empAccnumberAcc2" =>$ac_aced->decrypt($arr[44]),
							"empAccTypeAcc2" =>$bankAccType2,
							"empBanknameAcc2" =>$arr[46],
							"empDoublePayrate" =>($values['empDoublePayrate']!=0.00) ? $values['empDoublePayrate'] : "" ,
							"empOvertimeRate" =>($values['empOvertimeRate']!=0.00) ? $values['empOvertimeRate'] : ""  ,
							"empsalary" =>($values['empsalary']!=0.00)? $values['empsalary'] : "" ,
							"empStatus" => ($arr[92]=='N') ?"Active" : "Terminated",
							"filingStatus" => $arr[73],
							"empMdate" => ($arr[74]!='00/00/0000') ? $arr[74] : "",
							"empCreateduser" => getOwnerName($arr[75]),
							"empCrateddate" => ($arr[76]!='00/00/0000') ? $arr[76] : "",
							"empMuser" => getOwnerName($arr[77]),
							"pdlodging" => $diemLodgingValue,
							"pdmie" => $diemMIEValue,
							"pdtotal" => $diemTotalValue,
							"pdbillable" => $diemBillableType,
							"pdtaxable" =>  $diemTaxableType,
							"empEmail" =>  $arr[85], //added by swapna for email
							"empEthnicity" =>  getManage($arr[86]), //added by kumar raju for ethnicity
							"empVeterans" =>  ucfirst(getManage($arr[87])), //added by kumar raju for veterans status
							"paychkdelmethod" => ucfirst(getManage($arr[88])), //added by piyush r
							"empWithholdTaxState" => ($arr[89] != 0)? $stateArr[1] :'', //added by piyush r
							"filingStatusState" => $arr[90],
							"empHrmDept" => $arr[91],
							"empDisability" =>  ucfirst(getManage($arr[93])),
							"qca" =>  $arr[94],
							"otherdependents" =>  $arr[95],
							"claimtot" =>  $arr[96],
							"otherincome" =>  $arr[97],
							"deduct" =>  $arr[98],
							"schooldist" =>  $arr[99]
					  
				);
				//"empWithholdTaxState" =>$arr[9], removed duplicate Piyush R
				//($arr[72]=='ER')? "Terminated":
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
				$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
			
			//Link that will redirect to Employee's summary screen
			$data[$i][$ii]="javascript:showEmp('$arr[0]','$def_appsvr_domain')";
			$ii++;
			
			if(($empid[0]!="") || ($empssn[0]!="") || ($empfname[0]!="") || ($empmname[0]!="") || ($emplname[0]!="") || ($empaddr1[0]!="") || ($empaddr2[0]!="") || ($empcity[0]!="") || ($empstate[0]!="") || ($empzip[0]!="") || ($empphone[0]!="") || ($empmaritalst[0]!="")  || ($empdob[0]!="")  || ($empltax[0]!="") || ($emphiredate[0]!="") || ($empBranchLocation[0]!="") || ($empClass[0]!="") || ($empFedEmployee[0]!="") || ($empFedCompany[0]!="") || ($empStateEmployee[0]!="") || ($empStateCompany[0]!="") || ($empgender[0]!="")|| ($empfedtaxemployee[0]!="") || ($empfedtaxcompany[0]!="")|| ($empstatetaxemployee[0]!="")|| ($empstatetaxcompany[0]!="") || ($empsecurityemployee[0]!="")|| ($empsecuritycompany[0]!="")|| ($empmedicareemployee[0]!="")|| ($empmedicarecompany[0]!="")|| ($emplocalwithhold1employee[0]!="")|| ($emplocalwithhold1company[0]!="")|| ($emplw2employee[0]!="")|| ($emplw2company[0]!="")|| ($empnoallowclaim[0]!="") || ($empstatetaxallowance[0]!="")|| ($emppayproviderid[0]!="")|| ($empFein[0]!="")|| ($empAbaAcc1[0]!="")|| ($empAccnumberAcc1[0]!="")|| ($empAccTypeAcc1[0]!="")|| ($empBanknameAcc1[0]!="")|| ($empAbaAcc2[0]!="")|| ($empAccnumberAcc2[0]!="")|| ($empAccTypeAcc2[0]!="")|| ($empBanknameAcc2[0]!="")|| ($empDoublePayrate[0]!="")|| ($empOvertimeRate[0]!="")|| ($empStatus[0]!="") || ($filingStatus[0]!="") || ($empMdate[0]!="")  || ($empCreateduser[0]!="") || ($empCrateddate[0]!="") || ($empMuser[0]!="") || ($pdlodging[0]!="")|| ($pdmie[0]!="")|| ($pdtotal[0]!="")|| ($pdbillable[0]!="")|| ($pdtaxable[0]!="") || ($empEmail[0]!="") || ($empEthnicity[0]!="") || ($empVeterans[0]!="")||($paychkdelmethod[0]!="")||($empWithholdTaxState[0]!="")||($filingStatusState[0]!="")||($empHrmDept[0]!="") || ($empDisability[0]!="")|| ($qca[0]!="")|| ($otherdependents[0]!="")|| ($claimtot[0]!="")|| ($otherincome[0]!="")|| ($deduct[0]!="")|| ($schooldist[0]!="")) 
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
		//$fileName = "EmployeePayrolldata".$date.".".$format;
		$fileName ='HrmEmployees'.$date.".".$format;
		$mime = 'application/'.$format;		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Type: $mime; name=$fileName");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$fileName");
		header("Content-Transfer-Encoding: binary");
		$dataHeaderCount = count($headval[0]);
		for($t=0;$t<=$dataHeaderCount; $t++)
		{
			$data[0][$t] = trim($headval[0][$t]);
		}
		foreach($data as $row) 
		{
			$row = array_slice($row,0,count($row)-2);
			print '"' . stripslashes(implode('","',$row)) . "\"\n";
		}

	 } else {
		require("rlibdata.php");
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
?>