<?php
	require("global_reports.inc");
	require("Menu.inc");

	$reportfrm=$reportfrm;

	require_once("functions.inc.php");
  	$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	$menu		= new EmpMenu();
	$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate1	= date("m/d/Y",$thisday);
	$fromdate	= date("m/d/Y",$thisday);

	$dispNames	= array("empid"=>"Employee Id","empssn"=>"SSN","empfname"=>"First Name","empmname"=>"Middle Name","emplname"=>"Last Name","empaddr1"=>"Address 1","empaddr2"=>"Address 2","empcity"=>"City","empstate"=>"State","empzip"=>"Zip","empphone"=>"Primary Phone","empmaritalst"=>"Marital Status","empdob"=>"Date of Birth","empltax"=>"Tax","emphiredate"=>"Date of Hire","empBranchLocation"=>"HRM Location","empClass"=>"Employee Type","empFedEmployee"=>"Federal Withholding (Employee)","empFedCompany"=>"Federal Withholding (Company)","empStateEmployee"=>"State Withholding (Employee)","empStateCompany"=>"State Withholding (Company)","empgender"=>"Gender","empfedtaxemployee"=>"Additional Federal Tax Amount withheld from check (Employee)","empfedtaxcompany"=>"Additional Federal Tax Amount withheld from check (Company)","empstatetaxemployee"=>"Additional State Tax Amount withheld from check (Employee)","empstatetaxcompany"=>"Additional State Tax Amount withheld from check (Company)","empsecurityemployee"=>"Social Security (Employee)","empsecuritycompany"=>"Social Security (Company)","empmedicareemployee"=>"Medicare (Employee)","empmedicarecompany"=>"Medicare (Company)","emplocalwithhold1employee"=>"Local Withholding 1 (Employee)","emplocalwithhold1company"=>"Local Withholding 1 (Company)","emplw2employee"=>"Local Withholding 2 (Employee)","emplw2company"=>"Local Withholding 2 (Company)","empnoallowclaim"=>"Federal Tax Allowances","empstatetaxallowance"=>"State Tax Allowances","emppayproviderid"=>"Payroll Provider ID#","empFein"=>"Federal ID","empAbaAcc1"=>"PrimaryABA","empAccnumberAcc1"=>"PrimaryAcctNo","empAccTypeAcc1"=>"PrimaryAcctType","empBanknameAcc1"=>"PrimaryBankName","empAbaAcc2"=>"Acct2ABA","empAccnumberAcc2"=>"Acct2AcctNo","empAccTypeAcc2"=>"Acct2AcctType","empBanknameAcc2"=>"Acct2BankName","empDoublePayrate"=>"Double Time  Rate","empOvertimeRate"=>"Overtime Rate","empsalary"=>"Salary","empStatus"=>"Status","filingStatus"=>"Filing Status","empCreateduser" => "Created User","empCrateddate" => "Created Date","empMuser" => "Modified User","empMdate" => "Modified Date","pdlodging" => "Lodging","pdmie" => "M&amp;IE","pdtotal" => "Total","pdbillable" => "Billable","pdtaxable" => "Taxable","empEmail" => "Email","empEthnicity" => "Ethnicity","empVeterans" => "Veterans Status", "paychkdelmethod"=>"Pay Check Delivery Method", "empWithholdTaxState"=>"Withholding State","filingStateStatus" => "Filing State Status","filingLocalStatus" => "Filing Local Status","LocalJurisdiction" => "Local Jurisdiction","credentialtype" => "Credential Type","credentialname" => "Credential Name","credentialcountry" => "Country","credentialstates" => "Valid State","credentialvfromto" => "Credential Valid","credentialstatus" => "Credential Status","credacquiredfromto" => "Credential Acquire");
    
	if($view == "myreport")
	{
		$rquery = "select reportoptions from reportdata where reportid = '$id'";
		$rresult = mysql_query($rquery,$db);
		$vrowdata = mysql_fetch_row($rresult);
		$vrow = explode("|username->",$vrowdata[0]);
		$analytics_CREEmployee = $vrow[0];
		$cusername = $vrow[1];
	
		if(strpos($analytics_CREEmployee,"|username->") !=0 )
			$analytics_CREEmployee = $vrow[0];
	
		if(session_is_registered("Analytics_HrmEmployeeCredentials"))
			session_unregister(Analytics_HrmEmployeeCredentials);
		unset($Analytics_HrmEmployeeCredentials);
	
		session_update("cusername");
	
		$Analytics_HrmEmployeeCredentials = $vrow[0];
	}
	else
	{
		if($main == "main")
			$Analytics_HrmEmployeeCredentials	= $_REQUEST['Analytics_HrmEmployeeCredentials'];
		else
			$Analytics_HrmEmployeeCredentials	= $Analytics_HrmEmployeeCredentials;
	}
	$rdata		= explode("|",$Analytics_HrmEmployeeCredentials);
	
	$tab		= $rdata[18];
	$sort_order	= explode('^',$rdata[19]);
	$opt		= $rdata[2];

	//this is to fetch to and from dates that comes from selected date range 
	if($rdata[0] != "") {
		$fromdate	= explode("/",$rdata[0]);
		$todate		= explode("/",$rdata[1]);
		$ccheck		= "checked";
	}
	else {
		$fromdate	= "";
		$todate		= "";
		$ccheck		= "";
	}

	$dateopt	= $rdata[20];
	$sortcolumn	= $rdata[21];
	$esort		= $rdata[22];

	//Condition for, after customizing the report,to make the columns in "Column tab"  get selected
	if($tab=="addr") {
		$esort		= $rdata[22];
		$colstr		= $rdata[21];

		if($rdata[4] != "")
			$colchk1	= "checked";
		else
			$colchk1	= "";

		if($rdata[5] != "")
			$colchk2	= "checked";
		else
			$colchk2	= "";

		if($rdata[3] != "")
			$colchk3	= "";
		else
			$colchk3	= "";

		if($rdata[6] != "")
			$colchk4	= "checked";
		else
			$colchk4	= "";

		if($rdata[7] != "")
			$colchk5	= "checked";
		else
			$colchk5	= "";

		if($rdata[8] != "")
			$colchk6	= "checked";
		else
			$colchk6	= "";

		if($rdata[9] != "")
			$colchk7	= "checked";
		else
			$colchk7	= "";			

		if($rdata[23] != "")
			$colchk8	= "checked";
		else
			$colchk8	= "";	

		if($rdata[24] != "")
			$colchk9	= "checked";
		else
			$colchk9	= "";	

		if($rdata[25] != "")
			$colchk10	= "checked";
		else
			$colchk10	= "";

		if($rdata[26] != "")
			$colchk11	= "checked";
		else
			$colchk11	= "";

		if($rdata[27] != "")
			$colchk12	= "checked";
		else
			$colchk12	= "";

		if($rdata[28] != "")
			$colchk13	= "checked";
		else
			$colchk13	= "";

		if($rdata[29] != "")
			$colchk14	= "checked";
		else
			$colchk14	= "";

		if($rdata[30] != "")
			$colchk15	= "checked";

		if($rdata[34] != "")
			$colchk16	= "checked";
		else
			$colchk16	= "checked";

		if($rdata[35] != "")
			$colchk17	= "checked";
		else
			$colchk17	= "";

		if($rdata[36] != "")
			$colchk18	= "checked";
		else
			$colchk18	= "";	

		if($rdata[37] != "")
			$colchk19	= "checked";
		else
			$colchk19	= "";

		if($rdata[38] != "" && (PAYROLL_EMP != 'N'))
			$colchk20	= "checked";
		else
			$colchk20	= "";

		if($rdata[39] != "")
			$colchk21	= "checked";
		else
			$colchk21	= "";

		if($rdata[40] != "")
			$colchk22	= "checked";
		else
			$colchk22	= "";

		if($rdata[41] != "" && (PAYROLL_EMP != 'N'))
			$colchk23	= "checked";
		else
			$colchk23	= "";

		if($rdata[42] != "")
			$colchk24	= "checked";
		else
			$colchk24	= "";	

		if($rdata[43] != "" && (PAYROLL_EMP != 'N'))
			$colchk25	= "checked";
		else
			$colchk25	= "";

		if($rdata[44] != "")
			$colchk26	= "checked";
		else
			$colchk26	= "";	

		if($rdata[45] != "")
			$colchk27	= "checked";
		else
			$colchk27	= "";	

		if($rdata[46] != "")
			$colchk28	= "checked";
		else
			$colchk28	= "";

		if($rdata[47] != "")
			$colchk29	= "checked";
		else
			$colchk29	= "";

		if($rdata[48] != "")
			$colchk30	= "checked";
		else
			$colchk30	= "";

		if($rdata[49] != "")
			$colchk31	= "checked";
		else
			$colchk31	= "";	

		if($rdata[50] != "")
			$colchk32	= "checked";
		else
			$colchk32	= "";

		if($rdata[51] != "")
			$colchk33	= "checked";
		else
			$colchk33	= "";

		if($rdata[52] != "")
			$colchk34	= "checked";
		else
			$colchk34	= "";

		if($rdata[53] != "" && (PAYROLL_EMP != 'N'))
			$colchk35	= "checked";
		else
			$colchk35	= "";

		if($rdata[54] != "")
			$colchk36	= "checked";
		else
			$colchk36	= "";

		if($rdata[55] != "")
			$colchk37	= "checked";
		else
			$colchk37	= "";

		if($rdata[56] != "")
			$colchk38	= "checked";
		else
			$colchk38	= "";

		if($rdata[57] != "")
			$colchk39	= "checked";
		else
			$colchk39	= "";

		if($rdata[58] != "")
			$colchk40	= "checked";
		else
			$colchk40	= "";

		if($rdata[59] != "")
			$colchk41	= "checked";
		else
			$colchk41	= "";

		if($rdata[60] != "")
			$colchk42	= "checked";
		else
			$colchk42	= "";

		if($rdata[61] != "")
			$colchk43	= "checked";
		else
			$colchk43	= "";	

		if($rdata[62] != "")
			$colchk44	= "checked";
		else
			$colchk44	= "";	

		if($rdata[63] != "")
			$colchk45	= "checked";
		else
			$colchk45	= "";

		if($rdata[64] != "")
			$colchk46	= "checked";
		else
			$colchk46	= "";	

		if($rdata[65] != "")
			$colchk47	= "checked";
		else
			$colchk47	= "";	

		if($rdata[66] != "")
			$colchk48	= "checked";
		else
			$colchk48	= "";

		if($rdata[67] != "" && (PAYROLL_EMP != 'N'))
			$colchk49	= "checked";
		else
			$colchk49	= "";

		if($rdata[68] != "")
			$colchk50	= "checked";
		else
			$colchk50	= "";

		if($rdata[69] != "")
			$colchk51	= "checked";
		else
			$colchk51	= "";	

		if($rdata[70] != "")
			$colchk52	= "checked";
		else
			$colchk52	= "";

		if($rdata[71] != "")
			$colchk53	= "checked";
		else
			$colchk53	= "";

		if($rdata[72] != "")
			$colchk54	= "checked";
		else
			$colchk54	= "";	

		if($rdata[73] != "")
			$colchk55	= "checked";
		else
			$colchk55	= "";

		if($rdata[74] != "")
			$colchk56	= "checked";
		else
			$colchk56	= "";	

		//added by swapna for email
		if($rdata[80] != "")
			$colchk62	= "checked";
		else
			$colchk62	= "";
		//ended by swapna

		//These are for Per Diem
		if($rdata[75] != "")
			$colchk57	= "checked";
		else
			$colchk57	= "";

		if($rdata[76] != "")
			$colchk58	= "checked";
		else
			$colchk58	= "";

		if($rdata[77] != "")
			$colchk59	= "checked";
		else
			$colchk59	= "";

		if($rdata[78] != "")
			$colchk60	= "checked";
		else
			$colchk60	= "";

		if($rdata[79] != "")
			$colchk61	= "checked";
		else
			$colchk61	= "";

		if($rdata[84] != "")
			$colchk66	= "checked";
		else
			$colchk66	= "";

		if($rdata[85] != "")
			$colchk67	= "checked";
		else
			$colchk67	= "";	
		//ends here	

		//columns list ended

		if($rdata[10] != "")
			$orient		= $rdata[10];
		else
			$orient		= "landscape";

		if($rdata[11] != "")
			$rpaper		= $rdata[11];
		else
			$rpaper		= "letter";

		if($rdata[12] != "") {
			$compname	= $rdata[12];
			$check		= "checked";
		}
		else {
			$compname	= "";
			$check		= "";
		}

		if($rdata[13] != "") {
			$maintitle	= $rdata[13];
			$check1		= "checked";
		}
		else {
			$maintitle	= "";
			$check1		= "";
		}

		if($rdata[14] != "") {
			$sbtitle	= $rdata[14];
			$check2		= "checked";
		}
		else {
			$sbtitle	= "";
			$check2		= "";
		}

		if($rdata[15] != "")
			$check3		= "";
		else
			$check3		= "";

		if($rdata[16] != "")
			$check5		= "checked";
		else
			$check5		= "";						

		if($rdata[17] != "") {
			$efooter	= $rdata[17];
			$check6		= "checked";
		}
		else {
			$efooter	= "";
			$check6		= ""; 
		}

		//for filter
		$filternames		= $rdata[31];
		$filtervalues		= $rdata[32];		
		$sortingorder		= $rdata[33];

		$rdata_count		= count($rdata);
		$rcount			= 64; //Need to Change the count when new column is added -Important
		$checkArray		= array();

		for($r=81; $r<$rdata_count; $r++) {
			$checkvariable = "colchk".$rcount;

			if($rdata[$r] != "") {
				$$checkvariable = "checked";
				$checkArray[$rdata[$r]] = $$checkvariable; 
			}
			else
				$$checkvariable = "";

			$rcount++;
		}
	}
	else {
		//this is to make the default columns selected at the Column's Tab*/
		$colchk16	= "checked";
		$colchk64 	= "checked";

		//below are the variables that hold the default selected columns and their corresponding values(before customization)
		$filternames	= "empBranchLocation^empstatus^empCompanyCode^empdept^credentialtype^credentialname^credentialcountry^credentialstates^credentialvfromto^credentialstatus^credacquiredfromto";
		$filtervalues	= "^^ALL^";

		$sortingorder	= $filternames; 

		$check		= "checked";
		$check1		= "checked";
		$check2		= "checked";
		$check4		= "checked";
		$check5		= "checked";
		$check6		= "";

		$orient		= "landscape";
		$rpaper		= "letter";
		$compname	= $companyname;
		$maintitle	= "Employees Credential Report";
		$sbtitle	= "All Employees ";
		$efooter	= "";
		$alignn		= "standard";
		$esort		= 'ASC';
		$colstr		= 'empfname';
	}

	if($sortingorder)
		$sortingorder_array	= explode("^",$sortingorder);
	
	if(!empty($rdata[32])) {
		$employeedata = explode('^',addslashes($rdata[32]));
	}
?>
<html>
<head>
<script language="javascript">
function chkClose() {

	form1.action	= "../ses.php";
	form1.submit();
	window.close();
}
</script>

<title>Customize</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/Home/style_screen.css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/Analytics/Reports/analytics.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/ui.dropdownchecklist.css" />

<script src=/BSOS/scripts/tabpane.js></script>
<script src=scripts/validatecust.js language="javascript"></script>
<script src=scripts/validatereport.js language="javascript"></script>
<script src=/BSOS/scripts/moveto.js language=javascript></script>
<script src=scripts/link.js language=javascript></script>
<script language="JavaScript" src="/BSOS/scripts/calendar.js"></script>
<script src=/BSOS/scripts/jquery-min.js language="javascript"></script>
<script src=/BSOS/scripts/ui.core-min.js language="javascript"></script>
<script src=/BSOS/scripts/ui.dropdownchecklist-min.js language="javascript"></script>
</head>

<body>
	<form name="form1" action="employees.php" method="post">
	<input type=hidden name=tab value='tabview'>
	<input type=hidden name='pagecandsk' id='pagecandsk' value="" />
	<input type=hidden name=daction value='storereport.php'>
	<input type=hidden name=dateval value="<?php echo $todate1;?>">
	<input type=hidden name=tabnam value='addr'>
	<input type=hidden name=main value="<?php echo $main;?>">
	<input type=hidden name=reportfrm value="<?php echo $reportfrm;?>">
	<input type="hidden" value="<?php echo PAYROLL_PROCESS_BY;?>" name="payroll_process" id="payroll_process" />
	<input type="hidden" value="<?php echo PAYROLL_EMP;?>" name="payroll_emp" id="payroll_emp" />
	
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
								<div class="tab-page" id="tabPage22" >
									<h2 class="tab">Filters</h2>
									<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ));</script>
									
									<div style="display:none">
										<?php require("viewcolumn.php"); require("viewfonts.php"); require("viewheader.php"); require("viewformat.php"); ?>
									</div>

									<table width="100%"  border="0" cellspacing="2" cellpadding="2">
									<tr class="NewGridTopBg">
										<?php
											$name=explode("|","fa fa-play~Run&nbsp;Report|fa fa-times~Close");
										  
											if($main == "main")
												$link	= explode("|","javascript:chkReport()|javascript:chkClose()");
											else
												$link	= explode("|","javascript:chkReport()|javascript:window.close()");
										  
											$heading	= "";
											$menu->showHeadingStrip1($name,$link,$heading);
										?>
									</tr>
									<?php
										if($dcheck == "") {
											$vis		= "";
											$vis1		= "";
											$disable2	= "disabled";
										}
										else {
											$vis		= "<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 >";
											$vis1		= "<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 >";
											$disable2	= "";
										}
									?>
									<tr>
										<td>
											<fieldset>
											<legend><font class=afontstyle>Filters</font></legend>
												<table width=100% cellpadding=3 cellspacing=0 border=0 id="filter_table" class="ProfileNewUI">
												<tbody>
													<tr id="filter_message"><td>&nbsp;</td>
														<td colspan="2" class="afontstyle">
															Select the required options from the Available Columns displayed below and click on Run Report to generate the Report.
														</td>
													</tr>

												<?php
													getFilters($filternames,$filtervalues,$deptAccesSno);
												?>

												</tbody>
												</table>	 
											</fieldset>
										</td>
									</tr>
									<tr>
										<td colspan=2>
											<font class=bstrip>&nbsp;</font>
										</td>
									</tr>
									<tr class="NewGridBotBg">
									<?php
										$name=explode("|","fa fa-play~Run&nbsp;Report|fa fa-times~Close");
									    
										if($main=="main")
											$link	= explode("|","javascript:chkReport()|javascript:chkClose()");
										else
											$link	= explode("|","javascript:chkReport()|javascript:window.close()");
									    
										$heading	= "";
										$menu->showHeadingStrip1($name,$link,$heading);
									?>
									</tr>
								      </table>
								</div>
			
								<?php
									if($ind=='')
										$ind=0;
								?>
							</div>
						</div>
					</div>
					</td>
				</tr>
				</table>
			</td>
			</tr>
			</div>
		
		</table>
		</td>
	</div>
	</form>
</body>
</html>
<script language='javascript'>
	var getSelCountry = '<?php echo $employeedata[6]; ?>';
	var getSelState = '<?php echo $employeedata[7]; ?>';
	if ((getSelCountry != "" && getSelState != "") || (getSelCountry != "" && getSelState == "")) {
		getCreCountryStates(getSelCountry,getSelState);
	}
</script>