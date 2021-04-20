<?php
	require("global.inc");
	require("../../Include/commonfuns.inc");
	
		
	/*$GridHS=true;
	$xajax = new xajax();
	$xajax->registerExternalFunction(array("gridData","gridData","getHRMDepartments"),"gridData.inc");
	$xajax->processRequests();	*/
		
	require("Menu.inc");
	$menu=new EmpMenu();
	
?><head>
<title>Departments</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/gridhs.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/xtree.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/tab.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/style1.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/dropmenu.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/calendar.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/akken_gridhs.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">


<script language="javascript" src=scripts/validatedept.js></script>
<script src="/BSOS/scripts/tabpane.js"></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/common.js"></script>
<script src="/BSOS/scripts/cookies.js"></script>
<script src="/BSOS/scripts/gridhs.js"></script>
<script src="/BSOS/scripts/filter.js"></script>
<script src="/BSOS/scripts/menu.js"></script>
<script src="/BSOS/scripts/paging.js"></script>
<script src="/BSOS/scripts/preferences.js" language=javascript></script>
<script src="/BSOS/scripts/akken_gridhs.js"></script>
<script src="/BSOS/scripts/json.js"></script>
<script src="/BSOS/scripts/conMenu.js"></script>
<script language=javascript src="/BSOS/scripts/common_ajax.js"></script>

<style>
.defaultTopRange input, .defaultTopRange select{margin:5px 2px !important;}
.active-column-9 .active-box-resize {display: none;}
.styleAccDepartment{width:180px;}
#aw-column8 {width:88px !important;}
#aw-column7 {width:89px;}
	#aw-column8 { width:128px;}
@media screen and (-webkit-min-device-pixel-ratio:0) {
	.active-column-7 { width:100px;}
	#aw-column8 {width:95px;}
	#aw-column7 {width:91px;}
	.active-grid-row {white-space:nowrap !important;}

}
@-moz-document url-prefix() {

		.active-grid-row {white-space:nowrap !important;}
}
.active-box-item{ padding-right:0px \0/;padding-left:0px \0/;}
.active-scroll-data { position:inherit\9;white-space:normal\9;}

</style>
<?php //$xajax->printJavascript(''); ?>
</head>

<?php if($pageCall == 'manage') { ?>
<form method="post" name="adddept" id="adddept" action="../../Manage/dodept.php" onSubmit="return doSave()">
<?php } else { ?>
<form method="post" name="adddept" id="adddept" action="dodept.php" onSubmit="return doSave()">
<?php } ?>
<input type=hidden name=addr value="<?php echo $from;?>">
<input type=hidden name=addr1 value="">
<input type=hidden name=pagedept value="<?php echo $pagedept;?>">
<input type=hidden name=nav>
<input type=hidden name=hrvals>
<input type=hidden name=hrvalsBO>
<input type=hidden name=from value="<?php echo $from;?>">
<input type=hidden name=addr2 value="">
<input type=hidden name=edeptn>
<input type=hidden name=pdir>

<div id="main">
	<td valign=top align=center>
		<table width=99% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI defaultTopRange">
			<div id="content">
				<tr>
					<td>
						<table width=99% cellpadding=0 cellspacing=0 border=0>
						<tr>
							<td colspan=2 ><font class=bstrip>&nbsp;</font></td>
						</tr>
						<tr>
							<td><font class=modcaption>&nbsp;&nbsp;Add&nbsp;Department</font></td>
						</tr>
						<tr>
							<td colspan=2><font class=bstrip>&nbsp;</font></td>
						</tr>
					</table>
					</td>
				</tr>
			</div>
			<?php
			if(isset($error))
				print "<tr><td><font class=afontstyle>Department with the name <b>".$deptname."</b> is already existed - Please choose another Department Name.</font></td></tr>";
			?>
			<tr>
				<td>
					<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">
						<tr>
							<td width=100% valign=top align=left>
								<div class="tab-pane" id="tabPane2">
								<script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
								<div class="tab-page" id="tabPage21">
									<!-- <h2 class="tab">Add Department</h2>-->			
								<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ));</script>
								<table width=100% cellpadding=2 cellspacing=0 border=0>
									<div id="topheader">
										<tr class="NewGridTopBg">
											<?php
												if($pageCall == 'manage')
												{
													$name=explode("|","fa fa-floppy-o~Save|fa fa-arrow-circle-left~Cancel");
													$link=explode("|","javascript:doSave()|../../Manage/managedepartments.php");
												}
												else
												{
													$name=explode("|","fa fa-floppy-o~Save|fa fa-times~Cancel");
													$link=explode("|","javascript:doSave()|javascript:window.close()");
												}
												$heading="";
												$menu->showHeadingStrip1($name,$link,$heading,"left");
											?>
										</tr>
									</div>
									<div id="grid_form">
									<tr>
										<td>
											<fieldset>
												<legend><font class="afontstyle">Addinfo</font></legend>
												<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
													<tr><td colspan="3"></td></tr>											
													<tr class=tr2bgcolor>
														<td colspan="3">
															<table border="0" width="100%" cellpadding="0" cellspacing="0">
																<tr>
								  									<td width="14%"><font class=afontstyle>Department Name</font><font class=sfontstyle>*</font></td>
																	<td width="34%">
																		<input type=text name="deptname" id="deptname" size="32" maxlength="31">
																	</td>
																	<td width="20%" align="left">
																		<font class=afontstyle>Department Code</font><font class=sfontstyle>*</font>
																	</td>
																	<td width="32%">
																		<input type=text name="deptcode" id="deptcode" size="10" maxlength="9" style="width:190px">
									  							    </td>
																</tr>
																
															</table>					
														</td>
													</tr>
													<tr class="tr1bgcolor">
														<td colspan="3" valign="left">
															<table border="0" width="100%" cellpadding="0" cellspacing="0">
																<tr>
								  									<td width="14%"><font class=afontstyle>Location</font><font class=sfontstyle>*</font></td>
																	<td width="34%">
																		<select name="location" id="location" class="drpdwne" style="width:190px" onchange="javascript:getLocClass(this);" >
																			<?php
																			$sql_qry = "SELECT serial_no,IF(loccode!='',CONCAT_WS(' - ', loccode, heading),heading),classid FROM contact_manage WHERE status!='BP' ORDER BY deflt";
																			 $loccls = '';
																			$run_qry= mysql_query($sql_qry,$db);
																			while($loc = mysql_fetch_array($run_qry))
																			{
																				if($loccls == '')
																				   $loccls = $loc[2];
																				echo "<option value='".$loc['serial_no']."' id=\"".$loc[2]."\">".$loc[1]."</option>";
																			}
																			?>								
																		</select>
										 								<a href="javascript:doManageLocations('location','location');" class="edit-list">edit list</a>
																	</td>
																	<td width="20%"><font class=afontstyle>Class</font></td>
																	<td width="32%" ><?php  echo classSelBox(getClassesSetups(),'selClasses',$loccls); ?></td>
																</tr>
															</table>
														</td>
													</tr>
													<tr class="tr1bgcolor">
														<td colspan="3" valign="left">
															<table border="0" width="100%" cellpadding="0" cellspacing="0">
																
																<tr>
								  									<td width="14%" bgcolor="#f6f7f9"><font class="afontstyle">Parent Department</font></td>
								  									<td width="34%" bgcolor="#f6f7f9">
																		<font face=arial size=1>
																		<?php
																		departmentSelBox('avadept', '', 'drpdwne', '', 'styleAccDepartment','','');
																		?>
																	  	</font>
																		<span id="load_img" style="vertical-align:middle; display:none;">
																			<img src="/BSOS/images/loading_icon_small.gif" width="16" height="16" border="0" />
																		</span>
																	</td>
																	<td width="20%" align="left" bgcolor="#f6f7f9">
																		<font class=afontstyle>&nbsp;</font>
																	</td>
																	<td width="32%" bgcolor="#f6f7f9">&nbsp;</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</fieldset>
											</br>
											<fieldset>
												<legend><font class="afontstyle">Accounts Setup</font></legend>
												
												<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
													<tr><td colspan="4"></td></tr>	
													<tr class=tr2bgcolor valign=top>
														<td colspan="4">
															<table border="0" width="100%" cellpadding="0" cellspacing="0">
																<tr class="tr2bgcolor">
															  		<td colspan="5" valign="middle"><font class=afontstyle><strong>Accounts Setup</strong></font>&nbsp;
																  	<?php if(MANAGE_ACCOUNTS == 'Y')
																    { ?>
														    		<a href="javascript:doManageAccounts('DEPT')" class="edit-list">Manage Accounts</a>
																	
																	<?php
																	}
																	?>
																	</td>
																	
																</tr>
																
																<tr class="tr2bgcolor">
																  	<td width="21%" align="left"  bgcolor="#f6f7f9">
																  		<div align="left"><font class=afontstyle>Accounts Receivable Account</font><font class=sfontstyle>*</font></div>
																	</td>
																  	<td width="27%" align="left"  bgcolor="#f6f7f9">
																	  	<div align="left" id="DivselAccReceivable">
																	  		<font class=afontstyle>
																				<select name="selAccReceivable" id="selAccReceivable" class="drpdwne" style="width:180px"  >
																					
																					<?php
																					$optionvalues =  accountsSelBox(1, $filters = array('AR'),'','dept');	
																					foreach($optionvalues as $options)
																					{
																						echo $options;
																					}	
																				   ?>								
																	    		</select>
																	    	</font>
																		</div>
																	</td>															
																  	<td   bgcolor="#f6f7f9">
																  		<div align="left"><font class=afontstyle>Accounts Payable Account</font><font class=sfontstyle>*</font></div>
																	</td>
																	<td   bgcolor="#f6f7f9">
																			<div align="left" id="DivselAccPayable">
																				<font face="arial" size="1">
																					<select name="selAccPayable" id="selAccPayable" class="drpdwne" style="width:180px"  >
																						
																						<?php
																						$optionvalues =  accountsSelBox(7, $filters = array('AP'),'','dept');	
																						foreach($optionvalues as $options)
																						{
																							echo $options;
																						}	
																					   ?>								
																					  </select>
																				</font>
																			</div>
																	</td>
																</tr>
																<tr class="tr1bgcolor">
																  	<td  ><div align="left"><font class=afontstyle>Income Account</font><font class=sfontstyle>*</font></div></td>
																  	<td   >
																		<div align="left" id="DivselIncome">
																			<font face="arial" size="1">
																				<select name="selIncome" id="selIncome" class="drpdwne" style="width:180px"  >
																							
																						<?php
																						$optionvalues =  accountsSelBox(9, $filters = array('INC'),'','dept');	
																						foreach($optionvalues as $options)
																						{
																							echo $options;
																						}	
																					   ?>			
																				</select>
																			</font>
																		</div>
																	</td>
																  	<td align="left"  ><div align="left"><font class=afontstyle>Expense Accounts</font><font class=sfontstyle>*</font></div></td>
																  	<td align="left"  ><div align="left" id="DivselExpense">
																  		<font face="arial" size="1">
																			<select name="selExpense" id="selExpense" class="drpdwne" style="width:180px"  >
																					<?php
																					$optionvalues =  accountsSelBox(12, $filters = array('EXP'),'','dept');	
																					foreach($optionvalues as $options)
																					{
																						echo $options;
																					}	
																				   ?>					
																			 </select>
																    	</font></div>
																	</td>
																</tr>
																<tr class="tr2bgcolor">
																  <td align="left"  bgcolor="#f6f7f9"><div align="left" ><font class=afontstyle>Payroll Expense Account</font><font class=sfontstyle>*</font></div></td>
																  <td align="left"  bgcolor="#f6f7f9"><div align="left" id="DivselPayExpense">
																  		<font face="arial" size="1">
																			<select name="selPayExpense" id="selPayExpense" class="drpdwne" style="width:180px"  >
																				   <?php
																					$optionvalues =  accountsSelBox(11, $filters = array('EXP'),'','dept');	
																					foreach($optionvalues as $options)
																					{
																						echo $options;
																					}	
																				   ?>							
																			</select>
																    	</font></div>
																	</td>
																
																  	<td align="left"  bgcolor="#f6f7f9"><div align="left" ><font class=afontstyle>Payroll Liability Account</font><font class=sfontstyle>*</font></div></td>
																  	<td align="left"  bgcolor="#f6f7f9"><div align="left"  id="DivselPayLiability">
																  		<font face="arial" size="1">
																			<select name="selPayLiability" id="selPayLiability" class="drpdwne" style="width:180px"  >
																					<?php
																					$optionvalues =  accountsSelBox(6, $filters = array('CLIAB'),'','dept');	
																					foreach($optionvalues as $options)
																					{
																						echo $options;
																					}	
																				   ?>								
																			</select>
																   		</font></div>
																	</td>
																</tr>
															</table>						
														</td>
													</tr>
												</table>
											</fieldset>
											</br>
											<fieldset>
												<legend><font class="afontstyle">Permissions</font></legend>
												
												<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
													<tr><td colspan="3"></td></tr>											
													<tr class="tr2bgcolor">
														<td colspan="2" align="left" >
															<font class=afontstyle><strong>Front Office Permissions</strong></font>
															<div align="left"><font class="afontstyle" style="font-size: 10px; color:#f64a52;">The Permissions will be given to modules including Contacts, Companies, Job Orders, Candidates, Applicant Tracking and Hiring Management.</font></div>
														</td>
														<td colspan="2" align="left" >
															<font class=afontstyle><strong>Back Office Permissions</strong></font>
															<div align="left"><font class="afontstyle" style="font-size: 10px; color:#f64a52;">The Permissions will be given to modules including Employee Management, Assignment, Time Sheets and Expenses.</font></div>
														</td>
													</tr>	
													<tr class="tr1bgcolor">
														<td colspan="2" align="left" bgcolor="#FFFFFF">
															<font face=arial size=1>
																<select name="avahrs" id="avahrs" size=10 class="drpdwne1" style="width:60%" multiple>
																  <?php
																		$dque="SELECT users.username, users.name FROM users  LEFT JOIN sysuser ON users.username = sysuser.username WHERE users.status != 'DA' AND users.usertype!='' AND (sysuser.hrm != 'NO' OR sysuser.accounting != 'NO') AND users.username!='".$username."'";
																		$dres=mysql_query($dque,$db);
																		while($drow=mysql_fetch_row($dres))
																			print "<option value=".$drow[0].">".$drow[1]."</option>";
																		?>
																</select>
													  		</font>
													  		<a href="javascript:clrAll()" class="edit-list">Clear</a>&nbsp;&nbsp;<a href="javascript:selectAll()" class="edit-list">Select All</a>
																	
															<div align="right"></div>
														</td>
														<td colspan="2" align="left" bgcolor="#FFFFFF">
															<font face=arial size=1>
																<select name="avahrsBO" id="avahrsBO" size=10 class="drpdwne1" style="width:70%" multiple>
																  <?php
																		$dque="SELECT users.username, users.name FROM users  LEFT JOIN sysuser ON users.username = sysuser.username WHERE users.status != 'DA' AND users.usertype!='' AND (sysuser.hrm != 'NO' OR sysuser.accounting != 'NO') AND users.username!='".$username."'";
																		$dres=mysql_query($dque,$db);
																		while($drow=mysql_fetch_row($dres))
																			print "<option value=".$drow[0].">".$drow[1]."</option>";
																		?>
																</select>
													  		</font>
													  		<a href="javascript:clrAllBO()" class="edit-list">Clear</a>&nbsp;&nbsp;<a href="javascript:selectAllBO()" class="edit-list">Select All</a>
															<div align="right"></div>
														</td>
													</tr>															
													<tr><td colspan="3"></td></tr>
												</table>
											</fieldset>
										</td>
									</tr>
								</div>
								<div id="botheader">
									<tr class="NewGridTopBg">
			
									</tr>
								</div>			
							</table>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
	</table>
</td>
</div>
</form>
<script language="javascript">
getLocClass(document.getElementById('location'));
</script>
<style type="text/css">
	.drpdwne,.drpdwnacc {
	 	width: 190px !important;
	}
</style>