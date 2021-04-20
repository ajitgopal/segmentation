<?php
/*
Created Date : March 23 2017.
Created By : Rajesh kumar V
Purpose : Created the Report for theraphy source customer - "Employee Credential Expiration Report "
*/
require_once('global_reports.inc');
require_once('Menu.inc');
require_once('functions.inc.php');

$menu	= new EmpMenu();
 $deptAccessObj = new departmentAccess();
 $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
$header	= '';
$name	= explode('|','fa fa-play~Run&nbsp;Report|fa fa-times~Close');
$link	= explode('|','javascript:runReport()|javascript:window.close()');

$report_options	= '';
$filtervalues	= '';
//$filternames	= 'departments|tsdate|edate|astatus';
$filternames	= "employeestatus^empdept^credentialtype^credentialname^credentialstatus^credentialvfromto^asgnstatus^credentialacquiredfromto";

if (isset($view) && $view == 'myreport' && isset($id) && !empty($id))
{
	$sel_rep_qry	= "SELECT reportoptions FROM reportdata WHERE reportid = ".$id;
	$res_rep_qry	= mysql_query($sel_rep_qry, $db);
	$rec_rep_qry	= mysql_fetch_object($res_rep_qry);
	$filtervalues	= $rec_rep_qry->reportoptions;

}
elseif (isset($main) && $main == 'main')
{
	$filtervalues	= $customEmployeeCredential;
}

?>
<html>
	<head>
		<title>Customize</title>

		<link type="text/css" rel="stylesheet" href="/BSOS/css/educeit.css">
		<link type="text/css" rel="stylesheet" href="/BSOS/css/calendar.css">
		<link type="text/css" rel="stylesheet" href="/BSOS/css/ui.dropdownchecklist.css">
		<link type="text/css" rel="stylesheet" href="/BSOS/Analytics/Reports/analytics.css">
		<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" type="text/css" href="/BSOS/Home/style_screen.css">
		<script type="text/javascript" src="/BSOS/scripts/tabpane.js"></script>
		<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
		<script type="text/javascript" src="/BSOS/scripts/jquery-min.js"></script>
		<script type="text/javascript" src="/BSOS/scripts/ui.core-min.js"></script>
		<script type="text/javascript" src="/BSOS/scripts/ui.dropdownchecklist-min.js"></script>

		<script type="text/javascript">

			function resetStartDate(fieldname)
			{
				document.getElementById(fieldname).value = '';
			}

			$(document).ready(function() {

				$("#list_of_departments").dropdownchecklist({
					firstItemChecksAll:true,
					width:150,
					maxDropHeight:100
				});	
				

				runReport = function() {
					
					var ts_minvalue = document.getElementById("min_credentialvfromto").value;
					var ts_maxvalue = document.getElementById("max_credentialvfromto").value;

					var acq_minvalue = document.getElementById("min_credentialacquiredfromto").value;
					var acq_maxvalue = document.getElementById("max_credentialacquiredfromto").value;

					m1 = ts_minvalue.split('/');
					m2 = ts_maxvalue.split('/');

					m3 = acq_minvalue.split('/');
					m4 = acq_maxvalue.split('/');
					
					//var e_minvalue = document.getElementById("min_edate").value;
					//var e_maxvalue = document.getElementById("max_edate").value;
					//m3 = e_minvalue.split('/');
					//m4 = e_maxvalue.split('/');
				
					var ts_st_date = new Date(m1[2],m1[0],m1[1]);
					var ts_et_date = new Date(m2[2],m2[0],m2[1]);
					
					var acq_st_date = new Date(m3[2],m3[0],m3[1]);
					var acq_et_date = new Date(m4[2],m4[0],m4[1]);
					//var exp_st_date = new Date(m3[2],m3[0],m3[1]);
					//var exp_et_date = new Date(m4[2],m4[0],m4[1]);
				
					if(ts_et_date < ts_st_date)
					{
						alert("To Date cannot not be less than From Date.\nPlease select a valid date.");							
					}

					if(acq_et_date < acq_st_date)
					{
						alert("To Date cannot not be less than From Date.\nPlease select a valid date.");			
					}
					else
					{
						var fieldnames = ["employeestatus","empdept","credentialtype","credentialname","credentialstatus","credentialvfromto","credentialacquiredfromto"];
						var filtervalues = "";
						for(var i=0;i<fieldnames.length;i++)
						{
						if(document.getElementById('select_'+fieldnames[i])) {

							var filterval='';
						
							if(document.getElementById('select_'+fieldnames[i]).multiple) {
						
							    emplist = document.getElementById('select_'+fieldnames[i]).length;
						
							    for(var ck=0; ck < emplist; ck++) {
						
								if(document.getElementById('select_'+fieldnames[i]).options[ck].selected && document.getElementById('select_'+fieldnames[i]).options[ck].value != "" ) {
						
								    if(filterval=='')
									filterval =document.getElementById('select_'+fieldnames[i]).options[ck].value;
						
								    else
									filterval += "!#!"+document.getElementById('select_'+fieldnames[i]).options[ck].value;
								}
							    }	
							} // if condition
						}
						else if (fieldnames[i] == "employeestatus" || fieldnames[i] == "credentialstatus") {
							var e = document.getElementById(fieldnames[i]);
							if (checkObject(e.options[0])) {
								var filterval = e.options[e.selectedIndex].value;
							}               
						}
						else if (fieldnames[i] == "credentialvfromto" || fieldnames[i] == "credentialacquiredfromto") {
							var sStartDate 	= $("#min_"+fieldnames[i]).val();
							var sEndDate 	= $("#max_"+fieldnames[i]).val();

							var filterval = sStartDate + "*" + sEndDate;
						}
						filtervalues = filtervalues+filterval+"^";
						}
						document.getElementById('filterValues').value = filtervalues;
						//console.log(filtervalues);
						//return;
						//var apstatusstr = '';
						//astatuslen = document.getElementById('select_astatus').length;
						//for(var ck=0; ck < astatuslen; ck++)
						//{
						//	if(document.getElementById('select_astatus').options[ck].selected && document.getElementById('select_astatus').options[ck].value != "" )
						//	{
						//		if(apstatusstr=='')
						//		       apstatusstr =document.getElementById('select_astatus').options[ck].value;
						//		else
						//		       apstatusstr += "!#!"+document.getElementById('select_astatus').options[ck].value;
						//	}
						//}
						//document.getElementById('hdnapstatus').value = apstatusstr;
						if (window.opener.location.href.indexOf("BSOS/Analytics/Reports/hrreport.php") > 0) 
						{								
							$("#customEmployeeCredential").attr("target", "customEmployeeCredential_report").submit();

						} 
						else if (window.opener.location.href.indexOf("BSOS/Analytics/Reports/myreports.php") > 0) 
						{

							$("#customEmployeeCredential").attr("target", "customEmployeeCredential_report").submit();

						} 
						else if (window.opener.location.href.indexOf("BSOS/Analytics/Reports/customEmployeeCredential/header.php") > 0) 
						{

							$("#customEmployeeCredential").attr("target", "reportwindow_customEmployeeCredential").submit();
						}

						setTimeout(function() {
							window.close();
						}, 10);
					}
					
				}
			});
			function checkObject(obj) {
				return obj && obj !== "null" && obj !== "undefined";
			}
		</script>
		<style type="text/css">
			.ui-dropdownchecklist-dropcontainer
			{
				width: 266px !important;
			}
		</style>
	</head>
	<body>
		<form name="customEmployeeCredential" id="customEmployeeCredential" action="storereport.php" method="post">

		<input type="hidden" name="main" value="<?php echo $main;?>">
		<input type="hidden" name="view" value="<?php echo $view;?>">
		<input type="hidden" name="reportfrm" value="<?php echo $reportfrm;?>">
		<input type="hidden" name="filterValues" id="filterValues" value="" />
		<input type="hidden" name="savedreportid" id="savedreportid" value="<?php echo $id; ?>" />
		<div id="main">
			<table width=99% cellpadding=0 cellspacing=0 border=0>
				<tr>
					<td>
						<table width=100% cellpadding=0 cellspacing=0 border=0>
							<tr>
								<td colspan=2><font class="bstrip">&nbsp;</font></td>
							</tr>
							<tr>
								<td colspan=2><font class="modcaption">&nbsp;Report Customization</font></td>
							</tr>
							<tr>
								<td colspan=2><font class="bstrip">&nbsp;</font></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">
							<tr>
								<td width=100% valign=top align=center>

									<div class="tab-pane" id="tabPane1">

										<script type="text/javascript">
											tp2 = new WebFXTabPane(document.getElementById("tabPane1"));
										</script>

										<div class="tab-page" id="tabPage11">

											<h2 class="tab">Report</h2>

											<script type="text/javascript">
												tp2.addTabPage(document.getElementById("tabPage11"));
											</script>

											<div class="tab-pane" id="tabPane2">

												<script type="text/javascript">
													tp2 = new WebFXTabPane(document.getElementById("tabPane2"));
												</script>

												<div class="tab-page" id="tabPage22" >
													<h2 class="tab">Filters</h2>

													<script type="text/javascript">
														tp2.addTabPage(document.getElementById("tabPage22"));
													</script>

													<table width="100%" border="0" cellspacing="2" cellpadding="2" class="ProfileNewUI" align="center">
														<tr class="NewGridTopBg">
															<?php $menu->showHeadingStrip1($name, $link, $header); ?>
														</tr>
														<tr>
															<td>
																<fieldset>
																	<legend>
																		<font class="afontstyle">Filters</font>
																	</legend>
																	<table width="100%" cellpadding="3" cellspacing="0" border="0" id="filter_table" class='ProfileNewUI'>
																		<tbody>
																			<tr id="filter_message">
																				<td>&nbsp;</td>
																				<td colspan="2" class="">Select the required options from the Available Columns displayed below and click on Run Report to generate the Report</td>
																			</tr>
																			<?php echo getFilters($filternames, $filtervalues,$frmpg,$deptAccesSno); ?>
																		</tbody>
																	</table>
																</fieldset>
															</td>
														</tr>
														<tr><td colspan="2"><font class="bstrip">&nbsp;</font></td></tr>
														<tr class="NewGridBotBg"><?php $menu->showHeadingStrip1($name, $link, $header); ?></tr>
													</table>
												</div>
											</div>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		</form>
	</body>
</html>