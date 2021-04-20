<?php
	require("global.inc");

	$fdate = $servicedatefrom;
	$from_date = explode("/",$servicedatefrom);								
	$checking_from = $from_date[2]."-".$from_date[0]."-".$from_date[1];
	$servicedatefrom = $checking_from;

	$tdate = $servicedateto;
	$to_date = explode("/",$servicedateto);								
	$checking_to = $to_date[2]."-".$to_date[0]."-".$to_date[1];
	$servicedateto = $checking_to;
?>    
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" >
<title>Search and Select Employees</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/BSOS/Home/style_screen.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/educeit.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/merge.css" rel="stylesheet" type="text/css">
<script src=/BSOS/scripts/tabpane.js></script>
<script src=/BSOS/scripts/common_ajax.js></script>
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		buildEmployeesList();
    });
</script>
<script src=/BSOS/Accounting/Time_Mngmt/scripts/employeesearch.js></script>
<style type="text/css">
.divpopup {
	margin:0 auto;
	width:500px;
	height:100px;
	border: #333333 1px solid;
	background:#F3F3F3;
	padding:0px;
	font:12px normal Arial, Helvetica, sans-serif;
	line-height:22px;
	display:none;
}
.mouseovercont{	text-decoration:none; }
.mouseoutcont{	text-decoration:underline; }
#keydata {   
    height: 300px !important;
    vertical-align: top !important;
    color: #008000; 
    font-size: 13px; 
    font-weight: normal; 
    text-align: left; 
    width: 100%;
}
.mouseoutcont b{ font-weight:normal}
</style>
</head>

<body>

<div id='dynsndiv' style='display:none;'></div>
<div align="center" id="SaveAlert"></div>

<form name="empsearch" action="" method="POST">
<input type="hidden" name="mail_id" value="<?php echo $mailid;?>">
<input type="hidden" name="con_id" value="">
<input type="hidden" name="servicedatefrom" value="<?=$servicedatefrom?>">
<input type="hidden" name="servicedateto" value="<?=$servicedateto?>">
<input type="hidden" name="fdate" value="<?=$fdate?>">
<input type="hidden" name="tdate" value="<?=$tdate?>">
<input type="hidden" name="company_id" id="company_id" value="">

<?
	$mngqry = "select sno,name from manage where type='jotype' and  name not in('Direct') order by name";
	$mngres = mysql_query($mngqry,$db);

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$departments	= array();
	$sel_dep_query	= "SELECT d.sno, d.deptname FROM department d WHERE d.sno !='0' AND d.sno IN(".$deptAccesSno.") AND d.status='Active'";
	$res_dep_query	= mysql_query($sel_dep_query, $db);

	if (mysql_num_rows($res_dep_query) > 0) {

		while ($row = mysql_fetch_object($res_dep_query)) {

			$departments[$row->sno]	= $row->deptname;
		}
	}
?>

<table width=98%  height="80%" cellpadding=2 cellspacing=0 border="1"  align="center"  class="mainTable ProfileNewUI defaultTopRange">

<tr>
	<td  valign=middle align="center" colspan="6" height="25" class="mainHeading splheading">Search and Select Employees</td>
</tr>
<tr>
	<td width="1%"></td>
	<td width="51%" valign="top">
	<table width="96%" height="210" border="0"  cellpadding=2 cellspacing=0>
	<tr>
		<td colspan="2">
		<table width="420" border="0" cellpadding=2 cellspacing=0>
		<tr>
			<td width=16% align="right"><font class="afontstyle"> Filter&nbsp;Employee&nbsp;List&nbsp;By</font></td>
			<td width="1%"><b>:</b></td>
			<td width=45%>
			<select name="filter" id="filter" style="width:200" onChange="Chngfilter(this.value)">
			<option value="0">--Select--</option>
			<option value="AssignmentCompanies">&nbsp;Assignment Companies</option>
			<option value="PONumbers">&nbsp;PO Numbers</option>
			<?
			while($mngrow = mysql_fetch_row($mngres))
				print "<option value='".$mngrow[0]."'>&nbsp;".$mngrow[1]."</option>";
			?>
			<option value="Temp_Contract_OB">&nbsp;Temp/Contract (On Bench)</option>
			</select>
			</td>   
			<td id="disp_icon" style="display:none;" align="left">&nbsp;<a href="javascript:assignEmp_popup()" id="disp_icon_link"><i class="fa fa-search"></i></a></td> 
		</tr>
		<tr>
			<td width="50%">
				<select name="departments" id="departments" style="width:200;" onChange="javascript:Chngfilter(this.value);">
					<?php
					if (!empty($departments)) {

						$dept_ids	= implode(',', array_keys($departments));

						echo '<option value="'. $dept_ids. '">Select HRM Department (All)</option>';

						foreach ($departments as $id => $name) {

							echo '<option value="'. $id .'">'. $name .'</option>';
						}
					}
					?>
				</select>
			</td>
			<td width="1%">&nbsp;</td>
			<td width="48%">
				<select name="sortby" id="sortby" style="width:200;" onChange="javascript:Chngfilter(this.value);">
					<option value="fname">Sort By First&nbsp;Name,&nbsp;Last&nbsp;Name</option>
					<option value="lname" selected>Sort By Last&nbsp;Name,&nbsp;First&nbsp;Name</option>
					<option value="sno">Sort By Employee&nbsp;Id</option>
					<option value="asgno">Sort By Assignment&nbsp;Id</option>
				</select>
			</td>
			<td width="1%">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="4" height="4"></td>
		</tr>
		<tr>
			<td width="50%" colspan=2><font class="afontstyle">Click to add to the list</font></td>
			<td width="50%" colspan=2 valign="bottom" align="right" colspan=1><font class="afontstyle"><a href="javascript:addAllToList();">Add&nbsp;all&nbsp;to&nbsp;the&nbsp;list</a></font></td>
		</tr>
		</table>
		</td>
	</tr>    
	<tr>
		<td colspan="2" width="420">
		<table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td  width="100%">
			<table border="0" width="100%" cellpadding="0" cellspacing="3">
			<tr>
				<td valign="baseline"><font class="afontstyle">Employee&nbsp;Name</font>&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="emp_name" maxlength=256  class="summaryform-formelement" style="width:260px;">&nbsp;<a href="#" onClick="return EmpFilterSearch()"><i class="fa fa-search" alt='Employee Name Search'></i></a></td>
			</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="260" ><div id="keydata"><input type="hidden" name="listsnos"><input type="hidden" name="listnames"><input type="hidden" name="compnames"><input type="hidden" name="asgnmids" value=""></div></td>
		</tr>
		</table>
		</td>
	</tr>
	</table>	 
	</td>

	<td width="1%"></td>
	<td width="60%" valign="top" >
	<table width="96%" height="288" border="0" cellpadding=1 cellspacing=0 align="top">
	<tr>
		<td>&nbsp;</td>
		<td valign="baseline"><font class="afontstyle">Selected&nbsp;Employees &nbsp;&nbsp;</font><input type="button" name="select_all" value="Select All" class="button" onClick="return selectall('empsearch','target')"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="remove_list" value="Remove From List" class="button"  onClick="javascript:removeEmpList('empsearch','target');"/></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="right"><font class="afontstyle">No.of Employees selected: <strong><span id="selEmpCountID">0</span></strong></font></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="center"><select name="target" style="width:350px" size="20" multiple></select></td>
		<td>&nbsp;</td>
	</tr>
	<tr><td colspan="3">&nbsp;</td></tr>
	</table>	 
	</td>
</tr>

<tr bordercolor="#E4F8FF">
	<td></td>
	<td colspan="6" height="45">
		<div align="center">
		<input type="submit" name="cancel" value="Cancel" class="button"  onClick="return winclose('empsearch','target')"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" name="assoc" value="Create List" class="button"  onClick="return associa('empsearch','target')"/>		
		</div>
	</td>
</tr>
</table>
</form>
</body>
</html>