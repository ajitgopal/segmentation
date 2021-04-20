<?php   		
	require("global.inc");
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	
	$XAJAX_ON	= "YES";
	$XAJAX_MOD	= "CustomerCreditsRegister";	
	$GridHS		= true;

	require("Menu.inc");
	$menu		= new EmpMenu();
	
	/*
		Created Date	: November 04, 2014.
		Created By	: Kumar Raju k.
		Purpose		: Do display the history of transaction that paid to invioce using Applied Credits.
	*/

	if(!isset($val)) {

		$thisday = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$duedate = date("m/d/Y",$thisday);
		
		$accreg_selQuery = "SELECT MIN(stime), MAX(stime) FROM invoice WHERE deliver='yes' AND billed='no' AND total > 0 AND status='ACTIVE'";
		$accreg_resQuery = mysql_query($accreg_selQuery,$db);
		$dd = mysql_fetch_row($accreg_resQuery);
		
		if ((is_null($dd[0])) || (is_null($dd[1])))  {

			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d")-30,date("Y"));
			$indate=date("m/d/Y",$thisday);						
		}
		else {

			$indate=date("m/d/Y",strtotime($dd[0]));
			$duedate=date("m/d/Y",strtotime($dd[1]));
		}

		if(!isset($client)) {

			$client="none";
			$mindate = $indate;
			$maxdate = $duedate;
		}
	}	
	else {
		if($val == "in") {

			$t2=date("m/d/Y",$thisday);
			$indate=$t2;
		}
		else if($val=="due") {

			$t2=date("m/d/Y",$thisday);
			$duedate=$t2;
		}
	}
	
	$selQuery = "SELECT DISTINCT(staffacc_cinfo.username), staffacc_cinfo.cname, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1)." 
	FROM staffacc_cinfo 
	LEFT JOIN invoice ON staffacc_cinfo.sno = invoice.client_name 
	LEFT JOIN Client_Accounts ca ON (ca.typeid=staffacc_cinfo.sno)
	WHERE invoice.deliver = 'yes' AND ca.deptid !='0' AND ca.deptid IN(".$deptAccesSno.")
	ORDER BY staffacc_cinfo.cname";
	$resQuery = mysql_query($selQuery,$db);

	if(mysql_num_rows($resQuery) > 0) {
	
		$coptions = "<option value=none ".sele($dd[0],$client).">All</option>";
	
		while($dd = mysql_fetch_row($resQuery))
		{
			$coptions .= "<option value='".$dd[0]."' ".sele($dd[0],$client).">".$dd[2]."</option>";
		}
	}
	
	function sele($a,$b) {

		if($a==$b) {

			return "selected";
		}
		else {

			return "";
		}
	}

	$menu->showHeader("accounting","Customers","4|4");	
?>
<script language="javascript">
	function doRead(src) {

		rowid = src.getProperty("item/index");
		result = actdata[rowid][6];
		window.location.href=result;
	}

	function openNewWindow() {

		return false;
	}
</script>

<style>
	.active-column-0 {
		width: 0px;
		display:none;
	}

	.active-column-0.gecko {
		width: 0px;
		display:none;
	}

	/*  .active-column-7 .active-box-resize {display: none;}

	.active-column-7 {
		width: 100px;
	}

	.gridserbox6 {
		width: 100%;
		height: 17px;
		font-family: Arial;
		font-size:10px;
		text-align:right;
	}  */

	
</style>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/calendar.css" rel="stylesheet" type="text/css">
<script src="/BSOS/scripts/calendar.js" language="javascript"></script>
<script src='scripts/editpayments.js' language='javascript'></script>
<script src='/BSOS/scripts/date_format.js' language='javascript'></script>
<form name="creditsregisterform" id="creditsregisterform" method="POST" />
<input type=hidden id="val" name="val" />
<input type=hidden id="t1" name="t1" />
<input type=hidden id="t2" name="t2" />
<input type=hidden name='thisday' id='thisday'>
<input type=hidden id="datedue" name="datedue"  value="<?php echo date("Y-m-d",strtotime($duedate)); ?>"/>
<input type=hidden id="datein" name="datein"  value="<?php echo date("Y-m-d",strtotime($indate)); ?>"/>
<input type=hidden id="maxdate" name="maxdate" value="<?php echo $maxdate; ?>"/>
<input type=hidden id="mindate" name="mindate"  value="<?php echo $mindate; ?>"/>

<div id="tque"></div>
<div id="oque"></div>
<div id="main">
	<td valign=top align=center class=tbldata>
		<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI defaultTopRange" align="center">
			<div id="content">
				<tr>
					<td>
						<table width=100% cellpadding=0 cellspacing=0 border=0>
							<tr>
								<td align=left class="titleNewPad">
									<font class=modcaption>&nbsp;Credits&nbsp;Register</font>
								</td>
								<td align="right" class="titleNewPad">
									<font class=afontstyle color=black>
										From&nbsp;<input type=text id='frmdt' name=indate size=10 maxlength=10  value='<?php echo $indate;?>'>
										<script language='JavaScript'>new tcal ({'formname':'creditsregisterform','controlname':'indate'});</script>&nbsp;&nbsp;
										To&nbsp;<input type=text name=duedate id='todt' size=10  maxlength=10 value='<?php echo $duedate;?>'>
										<script language='JavaScript'>new tcal ({'formname':'creditsregisterform','controlname':'duedate'});</script>&nbsp;
										<a href="javascript:DateCheck('indate','duedate','','','')">view</a>
									</font>
								</td>
							</tr>
							<tr>
								<td>
									<img src='/BSOS/images/white.jpg' width=10 heigh=10>
								</td>
							</tr>
							<tr>
								<td width=50%>
									&nbsp;<font class=afontstyle>Select Customer&nbsp;</font>
									<select name='client' id='client' onChange="javascript:doCreditsClient();" class=drpdwnacc>
										<?php
											print $coptions;
										?>
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<img src='/BSOS/images/white.jpg' width=10 heigh=10>
					</td>
				</tr>
			</div>
		
			<div id="topheader">
				<tr class="NewGridTopBg">
					<?php
						$name = explode("|","fa-ban~Cancel");
						$link = explode("|","javascript:doCreditsRegisterCancel()");
						$heading = "";
						$menu->showHeadingStrip1($name,$link,$heading,"left");
					?>
				</tr>
			</div>
		
			<div id="grid_form">
				<tr>
					<td>
						<script language='javascript'>
							var gridHeadCol = ["","Invoice Number","<?php echo getEntityDispHeading('ID', 'Customer Name', 1); ?>","CreateDate","Applied Date","Payment&nbsp;Method","Credit&nbsp;Number","Original&nbsp;Created&nbsp;Amount","Credit&nbsp;Amount&nbsp;Used","Credit&nbsp;Amount&nbsp;Balance","<a href=javascript:doGridSearch('search');>Search</a>&nbsp;&nbsp;<a href=javascript:clearGridSearch(),doGridSearch('reset');>Reset</a>"];
							
							var gridHeadData = ["","<input class=gridserbox type=text id=aw-column1 name=aw-column1 value=''>","<input class=gridserbox type=text id=aw-column2 name=aw-column2 value=''>","<input class=gridserbox type=text id=aw-column3 name=aw-column3 value=''>","<input class=gridserbox type=text id=aw-column4 name=aw-column4 value=''>","<input class=gridserbox type=text id=aw-column5 name=aw-column5 value=''>","<input class=gridserbox type=text id=aw-column6 name=aw-column6 value=''>","<input class=gridserbox type=text id=aw-column7 name=aw-column7 value=''>","<input class=gridserbox type=text id=aw-column8 name=aw-column8 value=''>","<input class=gridserbox type=text id=aw-column9 name=aw-column9 value=''>","<input type=hidden id=aw-column10><a href=javascript:doGridSearch('search');><i alt='Search' class='fa fa-search fa-lg'></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=javascript:clearGridSearch(),doGridSearch('reset');><i alt='Reset' class='fa fa-reply fa-lg'></i></a>"];
							
							
							var gridActCol = ["","","","","","","","","",""];
							var gridActData = [];
							var gridValue = "Accounting_custCreditsRegister";
							gridForm = document.forms[0];
							gridSearchResetColumn = "10|";
							gridSortCol = 3;//Default sorting column should be date
							gridSort = 'Desc';
							initGrids(11);
							gridExtraFields = new Array();
							gridExtraFields['client']=document.getElementById('client').value;
							gridExtraFields['datedue']=document.getElementById('datedue').value;
							gridExtraFields['datein']=document.getElementById('datein').value;
							xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);
						</script>
						<font class=afontstyle> </font>
					</td>
				</tr>
			</div>
		
			<div id="botheader">
				<tr class="NewGridBotBg">
					<?php
						/*$name = explode("|","fa-ban~Cancel");
						$link = explode("|","javascript:doCreditsRegisterCancel()");
						$heading = "user.gif~Credits&nbsp;Register";
						$menu->showHeadingStrip1($name,$link,$heading);*/
					?>
				</tr>
			</div>
		
		</table>
	</td>
</div>
</form>
</body>
</html>