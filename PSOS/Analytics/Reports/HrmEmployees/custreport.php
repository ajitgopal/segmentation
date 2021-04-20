<?php
	require("global_reports.inc");
	//require("global.inc");
/*
	TS ID: 4112   
	By: Swapna
	Date:March 12 2009
	Details: Added new column Email to the HRM new Employees Report.
	
	Modifed Date: April 06, 2009.
	Modified By: Kumar Raju K.
	Purpose: Added ethnicity and veteran status columns in new employee report.
	TS Task Id: (4204), (Prakash) In HRM - New Employee Report,need to add Ethnicity and veteran status.
	
	Modifed Date: August 11, 2010.
	Modified By: Piyush R.
	Purpose: Added new columns.
	Task Id: 5255 - (Prakash) Need to add columns, Federal tax Allowance - State Tax Allowance - Filing Status - Withholding State - Pay check delivery method  , to existing reports New Employee, Timesheet and Assignments reports.
	
*/	
	require("Menu.inc");
	$reportfrm=$reportfrm;
	require_once("functions.inc.php");
	//error_reporting(E_ALL);
	//ini_set('display_errors','on');
	 $deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	$menu=new EmpMenu();
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate1=date("m/d/Y",$thisday);
	
	//$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$fromdate=date("m/d/Y",$thisday);
	
	//$dispNames  = array("empid"=>"Employee Id","empssn"=>"SSN","empfname"=>"First Name","empmname"=>"Middle Name","emplname"=>"Last Name","empaddr1"=>"Address 1","empaddr2"=>"Address 2","empcity"=>"City","empstate"=>"State","empzip"=>"Zip","empphone"=>"Primary Phone","empmaritalst"=>"Marital Status","empdob"=>"Date of Birth","empltax"=>"Tax","emphiredate"=>"Date of Hire","empBranchLocation"=>"HRM Location","empClass"=>"Employee Type","empFedEmployee"=>"Federal Withholding (Employee)","empFedCompany"=>"Federal Withholding (Company)","empStateEmployee"=>"state withholding (Employee)","empStateCompany"=>"state withholding (Company)","empgender"=>"Gender","empfedtaxemployee"=>"Additional Federal Tax Amount withheld from check (Employee)","empfedtaxcompany"=>"Additional Federal Tax Amount withheld from check (Company)","empstatetaxemployee"=>"Additional State Tax Amount withheld from check (Employee)","empstatetaxcompany"=>"Additional State Tax Amount withheld from check (Company)","empsecurityemployee"=>"Social Security (Employee)","empsecuritycompany"=>"Social Security (Company)","empmedicareemployee"=>"Medicare (Employee)","empmedicarecompany"=>"Medicare (Company)","emplocalwithhold1employee"=>"Local Withholding 1 (Employee)","emplocalwithhold1company"=>"Local Withholding 1 (Company)","emplw2employee"=>"Local Withholding 2 (Employee)","emplw2company"=>"Local Withholding 2 (Company)","empnoallowclaim"=>"Total Federal Tax Allowances","empstatetaxallowance"=>"Total State Tax Allowances","emppayproviderid"=>"Payroll Provider ID#","empFein"=>"Federal ID","empAbaAcc1"=>"ABA(Account #1)","empAccnumberAcc1"=>"Account Number(Account #1)","empAccTypeAcc1"=>"Account Type(Account #1)","empBanknameAcc1"=>"Bank Name(Account #1)","empAbaAcc2"=>"ABA(Account #2)","empAccnumberAcc2"=>"Account Number(Account #2)","empAccTypeAcc2"=>"Account Type(Account #2)","empBanknameAcc2"=>"Bank Name(Account #2)","empDoublePayrate"=>"Double Time  Rate","empOvertimeRate"=>"Overtime Rate","empWithholdTaxState"=>"Withholding Tax State","empsalary"=>"Salary","empStatus"=>"Status","filingStatus"=>"Filing Status for Withholding","empCreateduser" => "Created User","empCrateddate" => "Created Date","empMuser" => "Modified User","empMdate" => "Modified Date","empEmail" => "Email","empEthnicity" => "Ethnicity","empVeterans" => "Veterans Status");
	$dispNames  = array("empid"=>"Employee Id","empssn"=>"SSN","empfname"=>"First Name","empmname"=>"Middle Name","emplname"=>"Last Name","empaddr1"=>"Address 1","empaddr2"=>"Address 2","empcity"=>"City","empstate"=>"State","empzip"=>"Zip","empphone"=>"Primary Phone","empmaritalst"=>"Marital Status","empdob"=>"Date of Birth","empltax"=>"Tax","emphiredate"=>"Date of Hire","empBranchLocation"=>"HRM Location","empClass"=>"Employee Type","empFedEmployee"=>"Federal Withholding (Employee)","empFedCompany"=>"Federal Withholding (Company)","empStateEmployee"=>"State Withholding (Employee)","empStateCompany"=>"State Withholding (Company)","empgender"=>"Gender","empfedtaxemployee"=>"Additional Federal Tax Amount withheld from check (Employee)","empfedtaxcompany"=>"Additional Federal Tax Amount withheld from check (Company)","empstatetaxemployee"=>"Additional State Tax Amount withheld from check (Employee)","empstatetaxcompany"=>"Additional State Tax Amount withheld from check (Company)","empsecurityemployee"=>"Social Security (Employee)","empsecuritycompany"=>"Social Security (Company)","empmedicareemployee"=>"Medicare (Employee)","empmedicarecompany"=>"Medicare (Company)","emplocalwithhold1employee"=>"Local Withholding 1 (Employee)","emplocalwithhold1company"=>"Local Withholding 1 (Company)","emplw2employee"=>"Local Withholding 2 (Employee)","emplw2company"=>"Local Withholding 2 (Company)","empnoallowclaim"=>"Federal Tax Allowances","empstatetaxallowance"=>"State Tax Allowances","emppayproviderid"=>"Payroll Provider ID#","empFein"=>"Federal ID","empAbaAcc1"=>"PrimaryABA","empAccnumberAcc1"=>"PrimaryAcctNo","empAccTypeAcc1"=>"PrimaryAcctType","empBanknameAcc1"=>"PrimaryBankName","empAbaAcc2"=>"Acct2ABA","empAccnumberAcc2"=>"Acct2AcctNo","empAccTypeAcc2"=>"Acct2AcctType","empBanknameAcc2"=>"Acct2BankName","empDoublePayrate"=>"Double Time  Rate","empOvertimeRate"=>"Overtime Rate","empsalary"=>"Salary","empStatus"=>"Status","filingStatus"=>"Filing Status","empCreateduser" => "Created User","empCrateddate" => "Created Date","empMuser" => "Modified User","empMdate" => "Modified Date","pdlodging" => "Lodging","pdmie" => "M&amp;IE","pdtotal" => "Total","pdbillable" => "Billable","pdtaxable" => "Taxable","empEmail" => "Email","empEthnicity" => "Ethnicity","empVeterans" => "Veterans Status", "paychkdelmethod"=>"Pay Check Delivery Method", "empWithholdTaxState"=>"Withholding State","filingStatusState"=>"Filing Status(state) for Withholding","empHrmDept"=>"HRM Department","empDisability" => "Disability","qca" => "Qualifying Children Amount","otherdependents" => "Other Dependents","claimtot" => "Claim Dependents Total","otherincome" => "Other income (not from jobs)","deduct" => "Deductions","schooldist" => "School District");
	//"empWithholdTaxState"=>"Withholding Tax State", removed(duplicate) Piyush R
    //-------------------------------Satrt of Code for dynamic Coumns ---------------
	$arrEarTypes = getAllEarTypes();
	$earTypesCount  = count($arrEarTypes);
	$loopear = 0;
	for($loopnote=0;$loopnote<$earTypesCount;$loopnote++)
	{
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_tdollamt";
		$dispNames[$arrEarTypes[$loopnote]."_tdollamt"] = getDisplayName($arrEarTypes[$loopnote]."_tdollamt");
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_compcontr";
		$dispNames[$arrEarTypes[$loopnote]."_compcontr"] = getDisplayName($arrEarTypes[$loopnote]."_compcontr");
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_empcontr";
		$dispNames[$arrEarTypes[$loopnote]."_empcontr"] = getDisplayName($arrEarTypes[$loopnote]."_empcontr");
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_dollamtded";
		$dispNames[$arrEarTypes[$loopnote]."_dollamtded"] = getDisplayName($arrEarTypes[$loopnote]."_dollamtded");
		$loopear++;
	}
	 //-------------------------------End of Code for dynamic Coumns ---------------

	if($main=="main")
		$Analytics_HrmEmployees=$_REQUEST['Analytics_HrmEmployees'];
	else
		$Analytics_HrmEmployees=$Analytics_HrmEmployees;	
	
	$rdata=explode("|",$Analytics_HrmEmployees);
	$tab=$rdata[18];
	$sort_order=explode('^',$rdata[19]);
	$opt=$rdata[2];

	//this is to fetch to and from dates that comes from selected date range 
	if($rdata[0]!="")
	{
    	$fromdate=explode("/",$rdata[0]);
    	$todate=explode("/",$rdata[1]);
    	$ccheck="checked";
	}
	else
	{
        $fromdate="";
        $todate="";
        $ccheck="";
    }
    $dateopt=$rdata[20];
    $sortcolumn=$rdata[21];
	$esort=$rdata[22];

	//Condition for, after customizing the report,to make the columns in "Column tab"  get selected
	if($tab=="addr")
	{
		
        $esort=$rdata[22];
        $colstr=$rdata[21];

		if($rdata[4]!="")
			$colchk1="checked";
		else
			$colchk1="";
			
		if($rdata[5]!="")
			$colchk2="checked";
		else
			$colchk2="";
		
		if($rdata[3]!="")
			$colchk3="checked";
		else
			$colchk3="";
			
        if($rdata[6]!="")
			$colchk4="checked";
		else
			$colchk4="";
			
        if($rdata[7]!="")
			$colchk5="checked";
		else
			$colchk5="";

        if($rdata[8]!="")
			$colchk6="checked";
		else
			$colchk6="";
        if($rdata[9]!="")
			$colchk7="checked";
		else
			$colchk7="";			
        
		if($rdata[23]!="")
			$colchk8="checked";
		else
			$colchk8="";	
		if($rdata[24]!="")
			$colchk9="checked";
		else
			$colchk9="";	
		if($rdata[25]!="")
			$colchk10="checked";
		else
			$colchk10="";
		
		if($rdata[26]!="")
			$colchk11="checked";
		else
			$colchk11="";
		if($rdata[27]!="")
			$colchk12="checked";
		else
			$colchk12="";
		if($rdata[28]!="")
			$colchk13="checked";
		else
			$colchk13="";
		if($rdata[29]!="")
			$colchk14="checked";
		else
			$colchk14="";
		if($rdata[30]!="")
			$colchk15="checked";
		if($rdata[34]!="")
			$colchk16="checked";
		else
			$colchk16="";
		if($rdata[35]!="")
			$colchk17="checked";
		else
			$colchk17="";
		if($rdata[36]!="")
			$colchk18="checked";
		else
			$colchk18="";	
		if($rdata[37]!="")
			$colchk19="checked";
		else
			$colchk19="";
		if($rdata[38]!="" && (PAYROLL_EMP != 'N'))
			$colchk20="checked";
		else
			$colchk20="";
		if($rdata[39]!="")
			$colchk21="checked";
		else
			$colchk21="";
			
		if($rdata[40]!="")
			$colchk22="checked";
		else
			$colchk22="";
			
		if($rdata[41]!="" && (PAYROLL_EMP != 'N'))
			$colchk23="checked";
		else
			$colchk23="";
		
		if($rdata[42]!="")
			$colchk24="checked";
		else
			$colchk24="";	
		if($rdata[43]!="" && (PAYROLL_EMP != 'N'))
			$colchk25="checked";
		else
			$colchk25="";
		if($rdata[44]!="")
			$colchk26="checked";
		else
			$colchk26="";	
		if($rdata[45]!="")
			$colchk27="checked";
		else
			$colchk27="";	
		if($rdata[46]!="")
			$colchk28="checked";
		else
			$colchk28="";
		if($rdata[47]!="")
			$colchk29="checked";
		else
			$colchk29="";
		if($rdata[48]!="")
			$colchk30="checked";
		else
			$colchk30="";
		if($rdata[49]!="")
			$colchk31="checked";
		else
			$colchk31="";	
		if($rdata[50]!="")
			$colchk32="checked";
		else
			$colchk32="";
		if($rdata[51]!="")
			$colchk33="checked";
		else
			$colchk33="";
		if($rdata[52]!="")
			$colchk34="checked";
		else
			$colchk34="";
		if($rdata[53]!="" && (PAYROLL_EMP != 'N'))
			$colchk35="checked";
		else
			$colchk35="";
		if($rdata[54]!="")
			$colchk36="checked";
		else
			$colchk36="";
		if($rdata[55]!="")
			$colchk37="checked";
		else
			$colchk37="";
		if($rdata[56]!="")
			$colchk38="checked";
		else
			$colchk38="";
		if($rdata[57]!="")
			$colchk39="checked";
		else
			$colchk39="";
		if($rdata[58]!="")
			$colchk40="checked";
		else
			$colchk40="";
		if($rdata[59]!="")
			$colchk41="checked";
		else
			$colchk41="";
		if($rdata[60]!="")
			$colchk42="checked";
		else
			$colchk42="";
		if($rdata[61]!="")
			$colchk43="checked";
		else
			$colchk43="";	
		if($rdata[62]!="")
			$colchk44="checked";
		else
			$colchk44="";	
		if($rdata[63]!="")
			$colchk45="checked";
		else
			$colchk45="";
		if($rdata[64]!="")
			$colchk46="checked";
		else
			$colchk46="";	
		if($rdata[65]!="")
			$colchk47="checked";
		else
			$colchk47="";	
		if($rdata[66]!="")
			$colchk48="checked";
		else
			$colchk48="";
		if($rdata[67]!="" && (PAYROLL_EMP != 'N'))
			$colchk49="checked";
		else
			$colchk49="";
		if($rdata[68]!="")
			$colchk50="checked";
		else
			$colchk50="";
		if($rdata[69]!="")
			$colchk51="checked";
		else
			$colchk51="";	
		
		if($rdata[70]!="")
			$colchk52="checked";
		else
			$colchk52="";
		
		if($rdata[71]!="")
			$colchk53="checked";
		else
			$colchk53="";
		if($rdata[72]!="")
			$colchk54="checked";
		else
			$colchk54="";	
		if($rdata[73]!="")
			$colchk55="checked";
		else
			$colchk55="";
		if($rdata[74]!="")
			$colchk56="checked";
		else
			$colchk56="";	
		//added by swapna for email
		if($rdata[80]!="")
			$colchk62="checked";
		else
			$colchk62="";
		//ended by swapna

		if($rdata[81]!="")
			$colchk63="checked";
		else
			$colchk63="";
		if(($rdata[array_search('empVeterans', $rdata)])!="")
			$colchk64="checked";
		else
			$colchk64="";

		//These are for Per Diem
		if($rdata[75]!="")
			$colchk57="checked";
		else
			$colchk57="";
		if($rdata[76]!="")
			$colchk58="checked";
		else
			$colchk58="";
		if($rdata[77]!="")
			$colchk59="checked";
		else
			$colchk59="";
		if($rdata[78]!="")
			$colchk60="checked";
		else
			$colchk60="";
		if($rdata[79]!="")
			$colchk61="checked";
		else
			$colchk61="";
		
		if($rdata[84]!="")
			$colchk66="checked";
		else
			$colchk66="";
			
		if($rdata[85]!="")
			$colchk67="checked";
		else
			$colchk67="";	
		
		if($rdata[86]!="")
			$colchk68="checked";
		else
			$colchk68="";	
		//ends here	
		
		if($rdata[87]!="")
			$colchk69="checked";
		else
			$colchk69="";

		if($rdata[88]!="")
			$colchk90="checked";
		else
			$colchk90="";

		if(($rdata[array_search('qca', $rdata)])!="")
			$colchk91="checked";
		else
			$colchk91="";

		if(($rdata[array_search('otherdependents', $rdata)])!="")
			$colchk92="checked";
		else
			$colchk92="";

		if(($rdata[array_search('claimtot', $rdata)])!="")
			$colchk93="checked";
		else
			$colchk93="";

		if(($rdata[array_search('otherincome', $rdata)])!="")
			$colchk94="checked";
		else
			$colchk94="";

		if(($rdata[array_search('deduct', $rdata)])!="")
			$colchk95="checked";
		else
			$colchk95="";

		if(($rdata[array_search('schooldist', $rdata)])!="")
			$colchk96="checked";
		else
			$colchk96="";

			
		//columns list ended	
		
		if($rdata[10]!="")
			$orient=$rdata[10];
		else
			$orient="landscape";
			
		if($rdata[11]!="")
			$rpaper=$rdata[11];
		else
			$rpaper="letter";
					
		if($rdata[12]!="")
		{
			$compname=$rdata[12];
			$check="checked";
		}
		else
		{
			$compname="";
			$check="";
		}

		if($rdata[13]!="")
		{
			$maintitle=$rdata[13];
			$check1="checked";
		}
		else
		{
			$maintitle="";
			$check1="";
		}
		
		if($rdata[14]!="")
		{
			$sbtitle=$rdata[14];
			$check2="checked";
		}
		else
		{
			$sbtitle="";
			$check2="";
		}
	
		if($rdata[15]!="")
			$check3="checked";
		else
			$check3="";
					
		if($rdata[16]!="")
			$check5="checked";
		else
			$check5="";						
				
		if($rdata[17]!="")
		{
			$efooter=$rdata[17];
			$check6="checked";
		}
		else
		{
			$efooter="";
			$check6="";	
		}
		
	 //for filter
	$filternames = $rdata[31];
	$filtervalues = $rdata[32];		

	$sortingorder = $rdata[33];
	
	$rdata_count = count($rdata);
	$rcount = 70; //Need to Change the count when new column is added -Important
	$checkArray = array();
	for($r=82;$r<$rdata_count;$r++)
	{
		$checkvariable = "colchk".$rcount;
		if($rdata[$r] != "")
		{
		   $$checkvariable = "checked";
		   $checkArray[$rdata[$r]] = $$checkvariable; 
		}
		else
		   $$checkvariable = "";

		$rcount++;
	}
	
	}
	else
	{

		if($reportfrm==2)//If the report   opend from Deduction Report
		 {
			$colchk38="checked";
			$colchk1="checked";
			$colchk2="checked";
			$colchk5="checked";
			$colchk3="checked";
			$colchk39="checked";
			$colchk40="checked";
			$colchk41="checked";
			$colchk42="checked";
			$colchk43="checked";
			$colchk44="checked";
			$colchk45="checked";
			$colchk46="checked";
			$colchk17="checked";
			
			//below are the variables that hold the default selected columns and their corresponding values(before customization)
			$filternames ="empFein^empid^empssn^emplname^empfname^empAbaAcc1^empAccnumberAcc1^empAccTypeAcc1^empBanknameAcc1^empAbaAcc2^empAccnumberAcc2^empAccTypeAcc2^empBanknameAcc2^empClass";
			
			$filtervalues ="^^^^^^^ALL^^^^ALL^^";
			$sortingorder = $filternames; 
		 }
		else
		{
			//this is to make the default columns selected at the Column's Tab
			$colchk1="checked";
			$colchk2="checked";
			$colchk3="checked";
			$colchk4="checked";
			$colchk5="checked";
			$colchk6="checked";
			$colchk7="checked";
			$colchk8="checked";
			$colchk9="checked";
			$colchk10="checked";
			$colchk11="checked";
			$colchk12="checked";
			$colchk13="checked";
			$colchk14="checked";
			$colchk15="checked";
			$colchk16="checked";
			$colchk17="checked";
			$colchk51="checked";
			
			//below are the variables that hold the default selected columns and their corresponding values(before customization)
			$filternames ="empfname^empmname^emplname^empphone^empaddr1^empaddr2^empcity^empstate^empzip^empssn^empdob^empmaritalst^emphiredate^empid^empBranchLocation^empltax^empClass^empStatus";
			
			$filtervalues ="''^''^''^''^''^''^''^''^''^''^''^ALL^''^''^''^ALL^''^''^''";
			$sortingorder = $filternames; 
		}
		
		//$ccheck="checked";
		$check="checked";
		$check1="checked";
		$check2="checked";
		$check3="checked";
		$check4="checked";
		$check5="checked";
		$check6="";
		
		$orient="landscape";
		$rpaper="letter";
		$compname=$companyname;
		$maintitle="Employees Report";
		$sbtitle="All Employees ";
		$efooter="";
		$alignn="standard";

        $esort='ASC';
        $colstr='empfname';
	}
	

	if($sortingorder)
	    $sortingorder_array = explode("^",$sortingorder);

?>
<html>
<head>
<script language="javascript">
function chkClose()
{
	form1.action="../ses.php";
	form1.submit();
	window.close();
}
</script>
<title>Customize</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/Home/style_screen.css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/Analytics/Reports/analytics.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/ui.dropdownchecklist.css" />
<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<script src=/BSOS/scripts/calendar.js></script>
<script src=/BSOS/scripts/tabpane.js></script>
<script src=scripts/validatecust.js language="javascript"></script>
<script src=scripts/validatereport.js language="javascript"></script>
<script src=/BSOS/scripts/moveto.js language=javascript></script>
<script src=scripts/link.js language="javascript"></script>

<script src=/BSOS/scripts/jquery-min.js language="javascript"></script>

<script src=/BSOS/scripts/ui.core-min.js language="javascript"></script>

<script src=/BSOS/scripts/ui.dropdownchecklist-min.js language="javascript"></script>

</head>
<body>
<form name="form1" action=employees.php method=post>
<input type=hidden name=tab value='tabview'>
<input type=hidden name='pagecandsk' id='pagecandsk' value="" />
<input type=hidden name=daction value='storereport.php'>
<input type=hidden name=dateval value="<?php echo $todate1;?>">
<input type=hidden name=tabnam value='addr'>
<input type=hidden name=main value="<?php echo $main;?>">
<input type=hidden name=reportfrm value="<?php echo $reportfrm;?>">
<input type="hidden" value="<?php echo PAYROLL_PROCESS_BY;?>" name="payroll_process" id="payroll_process" />
<input type="hidden" value="<?php echo PAYROLL_EMP;?>" name="payroll_emp" id="payroll_emp" />
<!--<input type=hidden name=daterange value="<?php //echo $daterange;?>">-->
<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
	<td>
		<table width=100% cellpadding=0 cellspacing=0 border=0>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=modcaption>&nbsp;&nbsp;Report Customization</font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
		</table>
	</td>
	</tr>
	</div>
	<div id="grid_form">
	<tr>
	<td>
	<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">

	<tr>
		<td width=100% valign=top align=center>
		<div class="tab-pane" id="tabPane1">
        <script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
			<div class="tab-page" id="tabPage11">
			<h2 class="tab">Report</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage11" ) );</script>
                <div class="tab-pane" id="tabPane2">
                <script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
				
				<!--<div class="tab-page" id="tabPage21" >
				<h2 class="tab">Customize</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ));</script>
				<?php //require("viewcust.php");?>
				</div>-->
				
				<div class="tab-page" id="tabPage23" >
				<h2 class="tab">Columns</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage23" ));</script>
				<?php require("viewcolumn.php");?>
				</div>
				
				<div class="tab-page" id="tabPage25">
				<h2 class="tab">Order</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage25" ));</script>
				<?php require("viewsort.php");?>
				</div>
				
				
				<div class="tab-page" id="tabPage24" >
				<h2 class="tab">Sort</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage24" ));</script>
				<?php require("viewcolsort.php");?>
				</div>
				
				<div class="tab-page" id="tabPage22" >
				<h2 class="tab">Filters</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ));</script>
				<?php require("viewfilter.php");?>
				</div>
				<!-- <div class="tab-page"  id="tabPage26">
				<h2 class="tab">Fonts</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage26" ));</script>
				<?php //require("viewfonts.php");?>
				</div> -->
                <div class="tab-page" id="tabPage27">
				<h2 class="tab">Header/Footer</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage27" ));</script>
				<?php require("viewheader.php");?>
				</div>
			    <div class="tab-page" id="tabPage26">
				<h2 class="tab">Page Setup</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage26" ));</script>
				<?php require("viewformat.php");?>
				</div>
			 <?php
                if($ind=='')
                    $ind=0;
			 ?>
             </div>
		</td>
		</tr>
	</table>
	</td>
	</tr>
	</div>
</tr>
</table>
</div>
</form>
</body>
</html>
