<?php 
    $pagec1=$analyticsAssign;
	require("global_reports.inc");
	require_once("functions.inc.php");
	require("Menu.inc");
        $deptAccessObj = new departmentAccess();
 	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	$menu=new EmpMenu();
	/*
	Modifed Date : September 06, 2010
	Modified By  : Piyush R
	Purpose      : (Commission roles enh) Analytics-Need to provide a new column called Commission Role in Assignment and Joborder Reports.
	Task Id      : 5276.


	Modifed Date : June 28, 2010.
	Modified By  : Prasadd.
	Purpose      : Added new col called Submitted Candidate Primary Phone. 
	Task Id      : 5183.
	
	Modifed Date : Feb 08, 2010
	Modified By  : Prasadd.
	Purpose      : Added new column 'Workers Compensation Rate'
	Task Id      : 4941 (Prakash) New column 'Workers Compensation Rate' should be added to Assignment report.
	
	Modifed Date : Dec 28, 2009
	Modified By  : Sambasivarao L.
	Purpose      : Added new reason column in report
	Task Id      : 4851 (Prakash) Need "Reason" field in Assingment Report. This is the field that pops up when user cancel or close the assignment and enter the end date. We do not need this field in the filters.
	
	Modifed Date : July 01, 2009
	Modified By  : Prasadd.
	Purpose      : Added new columns
	Task Id      : 4470.
	
	Modifed Date: August 12, 2010.
	Modified By: Piyush R.
	Purpose: Added new columns.
	Task Id: 5255 - (Prakash) Need to add columns, Federal tax Allowance - State Tax Allowance - Filing Status - Withholding State - Pay check delivery method  , to existing reports New Employee, Timesheet and Assignments reports.
	
	*/
	if($main=="main")
    {
        if(session_is_registered("analyticsAssign"))
            session_unregister(analyticsAssign);
            unset($analyticsAssign);
        $analyticsAssign=$pagec1;
    }

	$fieldnames = array("federalid","customer","CompanyName","FirstName","MiddleName","LastName","PayRate","otpayrate","BillRate","otbrate","Salary","CompenCode","SSNNumber","AssignmentName","StartDate","blocation","Recruiter","SalesAgent","placefee","margin","assignmenttype","jobtype","corpcode","reportperson","contactperson","assign_category","assign_refcode","assign_billcontact","assign_billaddr","assign_imethod","assign_iterms","assign_pterms","assign_tsapproved","assign_ponumber","assign_department","assign_billterms","assign_sterms","jlocation","assgnReasonCodes","assgnReason","workersCompRate","HRMDepartment","fedtaxallowance","statetaxallowance","paychkdelmethod","filingstatus","withholdingstate","Role","statetaxwithholdpercentage", "markup","empEmailId","empPrimaryNumber","empMobileNumber","RegularPayRate","RegularSalary","Regularotpayrate","Regulardblpayrate","Regularperdiemrate","PayBurdenType","BillBurdenType","industry");
	$rdata=explode("|",$analyticsAssign);
	$col_order=explode("^",$rdata[8]);
	$tab=$rdata[9];
  
	if($tab=="addr")
	{
		// this loop to check the columns
		for($r=20;$r<=116;$r++) // updated from 93 to 100. Piyush R, added new columns August 12, 2010,updated again to 101. Piyush R, added new columns Sep 6, 2010, Added new columns from 100 to 106
		{
			 $checkvariable = "colchk".$rdata[$r];
			if($rdata[$r] != "")
			   $$checkvariable = "checked";
		    else
			   $$checkvariable = "";
		}	
		
		$dateopt = $rdata[1];
		if($rdata[2] != "")
		{
		   $fromdate = $rdata[2];
		   $todate = $rdata[3];
		   $ccheck="checked";
		}
		else
		{
		    $fromdate = "";
			$todate  = "";
			$ccheck = "";
		}
		
		//filter pane values
		$filternames = $rdata[4];
		$filtervalues = stripslashes($rdata[5]);
		
		$sortingorder = $rdata[6];
		$sortarr = explode('^',$rdata[8]);
		
		$esort=$rdata[7];
		
        if($rdata[11]!="")
			$orient=$rdata[11];
		else
			$orient="landscape";
			
		if($rdata[12]!="")
			$rpaper=$rdata[12];
		else
			$rpaper="letter";
						
		if($rdata[13]!="")
		{
			$compname=$rdata[13];
			$check="checked";
		}
		else
		{
			$compname="";
			$check="";
		}

		if($rdata[14]!="")
		{
			$maintitle=$rdata[14];
			$check1="checked";
		}
		else
		{
			$maintitle="";
			$check1="";
		}
		
		if($rdata[15]!="")
		{
			$sbtitle=$rdata[15];
			$check2="checked";
		}
		else
		{
			$sbtitle="";
			$check2="";
		}
	
		if($rdata[16]!="")
			$check3="checked";
		else
			$check3="";
					
		if($rdata[17]!="")
			$check5="checked";
		else
			$check5="";						
				
		if($rdata[18]!="")
		{
			$efooter=$rdata[18];
			$check6="checked";
		}

		else
		{
			$efooter="";
			$check6="";	
		}
	}
	else
	{
		$esort="ASC";
		$colstr="";
		
		if($rptFrom == "1")
		{
			$colchkFirstName = "checked";
			$colchkLastName = "checked";
			$colchkAssignmentName = "checked";
			$colchkCompanyName = "checked";
			$colchkStatus = "checked";
			$colchkStartDate = "checked";
			
			$sortarr = array("FirstName","LastName","AssignmentName","CompanyName","Status","StartDate");
			$filternames = "FirstName^LastName^AssignmentName^CompanyName^Status^StartDate^";
			$filtervalues = "^^^^active^^";
			$sortingorder = "FirstName^LastName^AssignmentName^StartDate";
			
			$maintitle="Expected Time Sheets";
			$sbtitle="Employees on Projects";
			$colstr="FirstName";
		}
		elseif($rptFrom == "2")
		{
			$colchkassignmenttype = "checked";
			$colchkSalesAgent = "checked";
			$colchkCompanyName = "checked";
			$colchkAssignmentName = "checked";
			$colchkFirstName = "checked";
			$colchkLastName = "checked";
			$colchkStartDate = "checked";
			$colchkStatus = "checked";
			$colchkindustry = "";
			$colchkassgnReasonCodes = "";
			$colchkassgnReason = "";
			


			
			$sortarr = array("SalesAgent","assignmenttype","CompanyName","AssignmentName","FirstName","LastName","StartDate","Status");
			$filternames = "SalesAgent^assignmenttype^CompanyName^AssignmentName^FirstName^LastName^StartDate^Status^";
			$filtervalues = "^OP^^^^^^pending^";
			$sortingorder = "SalesAgent";
			
			$maintitle="Assignments to be approved";
			$sbtitle="By Recruiter for a date range";
			$colstr="SalesAgent";
		}
		elseif($rptFrom == "3")
		{
			$colchkSalesAgent = "checked";
			$colchkCompanyName = "checked";
			$colchkAssignmentName = "checked";
			$colchkFirstName = "checked";
			$colchkLastName = "checked";
			$colchkStartDate = "checked";
			$colchkStatus = "checked";
			$colchkjobtype = "checked";
			$colchkenddate = "checked";
			$colchkaprdate = "checked";
			$colchkindustry = "checked";
			$colchkassgnReasonCodes = "checked";
			$colchkassgnReason = "checked";
			$sortarr = array("SalesAgent","CompanyName","jobtype","AssignmentName","Status","FirstName","LastName","StartDate","enddate","aprdate");
			$filternames = "SalesAgent^CompanyName^jobtype^AssignmentName^Status^FirstName^LastName^StartDate^enddate^aprdate^subdate^";
			$filtervalues = "^^^^^^^^^^^";
			$sortingorder = "SalesAgent^CompanyName^AssignmentName";
			
			$maintitle="Placements By Recruiter";
			$sbtitle="Placements";
			$colstr="SalesAgent";
		}
		else
		{
			$colchkCompanyName = "checked"; 
			$colchkFirstName = "checked"; 
			$colchkMiddleName = "checked";
			$colchkLastName = "checked"; 
			$colchkPayRate = "checked";  
			$colchkBillRate = "checked"; 
			$colchkSalary = "checked"; 
			$colchkCompenCode = "checked";
			$colchkworkersCompRate = "";
			$colchkSSNNumber = "checked"; 
			$colchkAssignmentName = "checked"; 
			$colchkStartDate = "checked"; 
			$colchkRecruiter = "checked"; 
			$colchkSalesAgent = "checked"; 
			$colchkotbrate = "checked";
			$colchkblocation = "checked";
			$colchkotpayrate = "checked";
			$colchkfederalid = "checked";
			$colchkcustomer = "checked";
			$colchkassignmenttype = "checked";
			$colchkjobtype = "checked";
			$colchkplacefee = "checked";
			$colchkmargin = "checked";
			$colchkpdtotamt = "";
			$colchkalternateId = "";
			$colchkreportperson = "checked";
			$colchkcontactperson = "checked";
			$colchkHRMDepartment = "";
			$colchkfedtaxallowance = "";
			$colchkfilingstatus=  "";
			$colchkwithholdingstate= "";
			$colchkstatetaxwithholdpercentage = '';
			$colchkstatetaxallowance=  "";
			$colchkpaychkdelmethod=  "";
			$colchkRole=  "";
			$colchkRegularPayRate= "";
			$colchkRegularSalary = "";
			$colchkRegularotpayrate = "";
			$colchkRegulardblpayrate = "";
			$colchkRegularperdiemrate = "";
			$colchkPayBurdenType = "";
			$colchkBillBurdenType = "";
			$colchkindustry = "";
			$colchkassgnReasonCodes = "";
			$colchkassgnReason = "";
			
			$sortarr = array("federalid","customer","CompanyName","FirstName","MiddleName","LastName","PayRate","otpayrate","BillRate","otbrate","Salary","CompenCode","SSNNumber","AssignmentName","StartDate","blocation","Recruiter","SalesAgent","placefee","margin","assignmenttype","jobtype","reportperson","contactperson");
			$filternames = "federalid^customer^CompanyName^FirstName^MiddleName^LastName^PayRate^otpayrate^BillRate^otbrate^Salary^CompenCode^SSNNumber^AssignmentName^StartDate^blocation^Recruiter^SalesAgent^placefee^margin^assignmenttype^jobtype^reportperson^contactperson";
			
			$filtervalues = "^^^^^^^^^^^^^^^^^^^^^^^";
			$sortingorder = "federalid^customer";
			
			$maintitle="Assignment Report";
			$sbtitle="Assignment";
			$colstr="federalid";
		}
		
		$sortarrCount = count($sortarr);
		$check="checked";
		$check1="checked";
		$check2="checked";
		$check3="checked";
		$check4="checked";
		$check5="checked";
		$check6="";
		
		$dateopt = "none";
		$fromdate = "";
		$ccheck  = "";
		
		$orient="landscape";
		$rpaper="letter";
		$compname=$companyname;
		
		$efooter="";
		$alignn="standard";
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
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/Analytics/Reports/analytics.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/calendar.css">
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/Home/style_screen.css">
<script src=/BSOS/scripts/tabpane.js></script>
<script src=scripts/validatecust.js language="javascript"></script>
<script src=scripts/link.js language="javascript"></script>
<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery-min.js"></script>
<script type="text/javascript" src="/BSOS/scripts/ui.core-min.js"></script>
<script type="text/javascript" src="/BSOS/scripts/ui.dropdownchecklist-min.js"></script>
<script src=/BSOS/scripts/moveto.js language=javascript></script>
</head>
<body>
<form name="form1" action="requirements.php" method=post>
<input type=hidden name=tab value='tabview'>
<input type=hidden name="assgnpage">
<input type=hidden name=daction value='storereport.php'>
<input type=hidden name=tabnam value='addr'>
<input type=hidden name=dateval value="<?php echo $todate1;?>">
<input type="hidden" name="main" value="<?php echo $main; ?>">
<input type="hidden" name="comm_personid" id="comm_personid" value="">
<input type="hidden" name="rptFrom" id="rptFrom" value="<?php echo $rptFrom;?>">

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
		<td width="100%" valign=top align=center>
		<div class="tab-pane" id="tabPane1">
        <script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
			<div class="tab-page" id="tabPage11">
			<h2 class="tab">Report</h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) );</script>
                <div class="tab-pane" id="tabPane2">
                <script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
               
				<div class="tab-page" id="tabPage21" >
				<h2 class="tab">Columns</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ));</script>
				<?php require("viewcolumn.php");?>
				</div>
				
				<div class="tab-page" id="tabPage22">
				<h2 class="tab">Order</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ));</script>
				<?php require("viewsort.php");?>
				</div>
				
				<div class="tab-page" id="tabPage23">
				<h2 class="tab">Sort</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage23" ));</script>
				<?php require("viewcolsort.php");?>
				</div>
							   
			    <div class="tab-page" id="tabPage24" >
				<h2 class="tab">Filters</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage24" ));</script>
				<?php require("viewfilter.php");?>
				</div>
				
                <div class="tab-page" id="tabPage25">
				<h2 class="tab">Header/Footer</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage25" ));</script>
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