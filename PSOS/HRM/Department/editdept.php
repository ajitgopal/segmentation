<?php
	require("global.inc");
	require("../../Include/commonfuns.inc");
	require("Menu.inc");
	$menu=new EmpMenu();
	
	function sele($a,$b)
	{
        $cnt=0;
        $a1=explode(",",$a);
        for($k=0;$k<count($a1);$k++)
        {
            if($a1[$k]==$b)
                $cnt++;
        }


		if($cnt>0)
			return "selected";
		else
			return "";
	}
?>

<head>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/tab.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/gridhs.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/xtree.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link href="/BSOS/css/style1.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/dropmenu.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/calendar.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/akken_gridhs.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">


<script language="javascript" src="scripts/validatedept.js"></script>
<script language="javascript" src="/BSOS/scripts/common.js"></script>
<script src="/BSOS/scripts/grid.js"></script>
<script src="/BSOS/scripts/filter.js"></script>
<script src="/BSOS/scripts/paging.js"></script>
<script src="/BSOS/scripts/tabpane.js"></script>
<title>Edit Department</title>
</head>

<body>
<?php if($pageCall == 'manage') { ?>
<form method="post" name="adddept" id="adddept" action="../../Manage/dodept.php">
<?php } else { ?>
<form method="post" name='adddept' id='adddept' action="dodept.php">
<?php } ?>
<input type=hidden name='addr' id='addr' value="old">
<input type=hidden name='nav' id='nav' value='' />
<input type=hidden name='hrvals' id='hrvals' value='' />
<input type=hidden name='hrvalsBO' id='hrvalsBO' value='' />
<input type=hidden name='currLoc' id='currLoc' />
<input type=hidden name='currDepCode' id='currDepCode' />
<input type="hidden" name="edeptname" id="edeptname" value="<?php echo $edeptname;?>">

<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
	<div id="content">
	<tr>
		<td>
		<table width=99% cellpadding=0 cellspacing=0 border=0>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
			<td><font class=modcaption>&nbsp;&nbsp;Edit&nbsp;Department</font></td>
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
		print "<tr><td><font class=afontstyle>Department with the name <b>".$rdeptname."</b> already exists - Please choose another Department Name.</font></td></tr>";
	?>
	<div id="grid_form">
	<tr>
	<td>
		<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">
		<tr>
		<td width=100% valign=top align=left>
			<div class="tab-pane" id="tabPane2">
			<script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
			<div class="tab-page" id="tabPage21">
			<h2 class="tab">Edit Department</h2>
			<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ));</script>
			<table width=100% cellpadding=2 cellspacing=0 border=0>
			<div id="topheader">
			<tr class="NewGridTopBg">
			<?php
				if($pageCall == 'manage')
				{
					$name=explode("|","fa fa-floppy-o~Save|fa-ban~Cancel");
					$link=explode("|","javascript:doUpdate()|../../Manage/managedepartments.php");
				}
				else
				{
					$name=explode("|","fa fa-clone~Update|fa fa-times~Cancel");
					$link=explode("|","javascript:doUpdate()|javascript:window.close()");
				}
				$heading="user.gif~Edit&nbsp;Department";
				$menu->showHeadingStrip1($name,$link,$heading);
			?>
			</tr>
			</div>
					<?php
					   $que = "SELECT d.deptname, d.permission, d.depcode, IFNULL(da.classid,0), d.loc_id, 
					   d.parent, da.income_acct,da.expense_acct, da.ar_acct, da.ap_acct, da.payliability_acct, da.payexpense_acct, GROUP_CONCAT(DISTINCT dpFO.permission) AS FoPerUsr, GROUP_CONCAT(DISTINCT dpBO.permission) AS BoPerUsr
								FROM department d 
								LEFT JOIN department_permission dpFO ON (dpFO.dept_sno = d.sno AND dpFO.type='FO')
								LEFT JOIN department_permission dpBO ON (dpBO.dept_sno = d.sno AND dpBO.type='BO')
								LEFT JOIN department_accounts da ON (d.sno=da.deptid AND da.status='ACTIVE') 
								WHERE d.sno='".$edeptname."' 
								AND d.status='Active' GROUP BY d.sno";
						$res = mysql_query($que,$db);
						$row = mysql_fetch_array($res);
						
										
						?>
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
											<td width="34%"><input type="text" name="deptname" id="deptname" size="32" maxlength="31" value="<?php echo $row[0];?>" style="width:190px"></td>
											<td width="20%" align="left">
												<font class=afontstyle>Department Code</font><font class=sfontstyle>*</font>
											</td>
											<td width="32%">
												<input type="text" name="deptcode" id="deptcode" size="10" maxlength="9" value="<?php echo $row[2];?>" style="width:190px">
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
												<input type="hidden" name="prevLoc" id="prevLoc" value="<?php echo $row[4];?>">
												<input type="hidden" name="prevDepCode" id="prevDepCode"  value="<?php echo $row[2];?>">
												<select name="location" id="location" class="drpdwne"  <?php if($chkDeflt=='Y') {?> disabled="disabled" <?php } ?> style="width:190px;" onChange="javascript:getLocClass(this,'<?php echo $row[5]; ?>','<?php echo $edeptname; ?>');" >
												<?php
												$sql_qry = "SELECT serial_no,IF(loccode!='',CONCAT_WS(' - ', loccode, heading),heading),classid FROM contact_manage WHERE status!='BP' ORDER BY deflt";
												$loccls = '';
												$run_qry= mysql_query($sql_qry,$db);
												while($loc = mysql_fetch_array($run_qry))
												{
													if($loccls == '')
													   $loccls = $loc[2];
													echo "<option value='".$loc['serial_no']."' ".sele($row[4],$loc['serial_no'])." id=\"".$row[3]."\">".$loc[1]."</option>";
												}
												?>								
												</select>
									    		<?php if($chkDeflt=='N') {?>
									    		<a href="javascript:doManageLocations('location','location');" class="edit-list">edit list</a>
              									<?php } ?>		
              								</td>
											<td width="20%"><font class=afontstyle>Class</font></td>
											<td width="32%" ><?php  echo classSelBox(getClassesSetups(),'selClasses',$row[3]); ?></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr class=tr2bgcolor>
								<td colspan="3">
									<table border="0" width="100%" cellpadding="0" cellspacing="0">
										<tr>
							  				<td width="14%" ><?php if($chkDeflt=='N') {?> 
												<font class=afontstyle>Parent Department</font>
												<?php } else { ?>&nbsp;<?php } ?>	
											</td>
											<td width="34%" ><?php if($chkDeflt=='N') {?> 
												<font face=arial size=1>
												<select name="avadept" id="avadept" class="drpdwne" style="width:180px">
													<option value="0">Select Department</option>
													<?php
													 $dque="SELECT sno,IF(depcode!='',CONCAT_WS(' - ', depcode, deptname),deptname),parent FROM department WHERE FIND_IN_SET('".$username."',permission)>0 AND parent!='".$edeptname."' AND sno!='".$edeptname."' AND status='Active' ORDER BY parent";
													$dres=mysql_query($dque,$db);
													while($drow=mysql_fetch_row($dres))
													{
														if($drow[2]=="0")
														{
															print "<option value=".$drow[0]." ".sele($row[5],$drow[0]).">".$drow[1]."</option>";
														}
														else
														{
															$parent=getParent($drow[2],$drow[1],$db);
															print "<option value=".$drow[0]." ".sele($row[5],$drow[0]).">".$parent."</option>";
														}
													}
													?>
												</select>
				                                </font>
										      <?php } else { ?>
										      <input type="hidden" name="avadept" id="avadept" value="0">
                  							  <?php } ?>
				  							</td>
											<td width="20%" align="left">
												<font class=afontstyle>&nbsp;
												<span id="load_img" style="vertical-align:middle; display:none;">
										   			<img src="/BSOS/images/loading_icon_small.gif" width="16" height="16" border="0" />
												</span>
												</font>
											</td>
											<td width="32%" >&nbsp;</td>
										</tr>
									</table>						
								</td>
							</tr>
						</table>
					</fieldset>	
					<fieldset>
						<legend><font class="afontstyle">Accounts Setup</font></legend>
						<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
							<tr><td colspan="4"></td></tr>
							<tr class=tr2bgcolor valign=top>
								<td colspan="3">
									<table border="0" width="100%" cellpadding="0" cellspacing="0">
										<tr class=tr2bgcolor>
									  		<td colspan="5" valign="middle">
											<font class=afontstyle><strong>Accounts Setup</strong></font>&nbsp;
											 <?php if(MANAGE_ACCOUNTS == 'Y')
								   				 { ?>
									        <a href="javascript:doManageAccounts('DEPT')" class="edit-list">Manage Accounts</a>
												<?php
												  }
												?>								
									  		</td>
											
										</tr>
										
										<tr class=tr2bgcolor>
							  				<td width="21%" align="left" valign="bottom">
								  				<div align="left">
								  				<font class=afontstyle>Accounts Receivable Account</font><font class=sfontstyle>*</font>
												</div>
											</td>
							  				<td width="27%" align="left" valign="bottom">
								  				<div align="left" id="DivselAccReceivable">
													<font class=afontstyle>
													<select name="selAccReceivable" id="selAccReceivable" class="drpdwne" style="width:180px"  >
															
															<?php
																										
															  $selAccRec = $row['ar_acct'] != ''?$row['ar_acct']:1;
															$optionvalues =  accountsSelBox($selAccRec, $filters = array('AR'),'','dept');	
															foreach($optionvalues as $options)
															{
																echo $options;
															}	
														   ?>								
													</select>
													</font>
								 				</div>
											</td>
										
							  				<td align="left" valign="bottom" ><div align="left"><font class=afontstyle>Accounts Payable Account</font><font class=sfontstyle>*</font></div></td>
							  				<td align="left" valign="bottom" >
							  					<div align="left" id="DivselAccPayable"><font face="arial" size="1">
							    				<select name="selAccPayable" id="selAccPayable" class="drpdwne" style="width:180px"  >
													<?php
													$selAccPay = $row['ap_acct'] != ''?$row['ap_acct']:7;
													$optionvalues =  accountsSelBox($selAccPay , $filters = array('AP'),'','dept');	
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
										<tr class=tr1bgcolor>
							  				<td align="left" valign="bottom" ><div align="left"> <font class=afontstyle>Income Account</font><font class=sfontstyle>*</font></div></td>
							  				<td align="left" valign="bottom" >
							  					<div align="left" id="DivselIncome"><font face="arial" size="1">
											    <select name="selIncome" id="selIncome" class="drpdwne" style="width:180px"  >
													<?php
													   $selIncome = $row['income_acct'] != ''?$row['income_acct']:9;
														$optionvalues =  accountsSelBox($selIncome, $filters = array('INC'),'','dept');		
														foreach($optionvalues as $options)
														{
														echo $options;
														}	
												    ?>			
												</select>
							    				</font>
							    				</div>
							    			</td>							  			
							  				<td align="left" valign="bottom" ><div align="left"><font class=afontstyle>Expense Accounts</font><font class=sfontstyle>*</font></div></td>
							  				<td align="left" valign="bottom" ><div align="left" id="DivselExpense"><font face="arial" size="1">
											    <select name="selExpense" id="selExpense" class="drpdwne" style="width:180px"  >
													 <?php
														$selExp = $row['expense_acct'] != ''?$row['expense_acct']:12;
														$optionvalues =  accountsSelBox($selExp, $filters = array('EXP'),'','dept');	
														foreach($optionvalues as $options)
														{
															echo $options;
														}	
													   ?>					
												</select>
								    			</font></div>
								    		</td>
							  			</tr>
										<tr class=tr2bgcolor>
							  				<td align="left" valign="bottom" ><div align="left"><font class=afontstyle>Payroll Expense Account</font><font class=sfontstyle>*</font></div></td>
							  				<td align="left" valign="bottom" ><div align="left" id="DivselPayExpense"><font face="arial" size="1">
											    <select name="selPayExpense" id="selPayExpense" class="drpdwne" style="width:180px"  >									
												<?php
													$selPayExp = $row['payexpense_acct'] != ''?$row['payexpense_acct']:11;
													$optionvalues =  accountsSelBox($selPayExp, $filters = array('EXP'),'','dept');	
													foreach($optionvalues as $options) {
														echo $options;
													}
												?>
												</select>
											    </font></div>
											</td>							  			
							  				<td align="left" valign="bottom"><div align="left"><font class=afontstyle>Payroll Liability Account</font><font class=sfontstyle>*</font></div></td>
							  				<td align="left" valign="bottom" ><div align="left" id="DivselPayLiability"><font face="arial" size="1">
												<select name="selPayLiability" id="selPayLiability" class="drpdwne" style="width:180px"  >	
												<?php
												$selPayLib = $row['payliability_acct'] != ''?$row['payliability_acct']:6;
												$optionvalues =  accountsSelBox($selPayLib, $filters = array('CLIAB'),'','dept');		
												foreach($optionvalues as $options) {
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
							<tr><td colspan="3"></td></tr>
							<tr><td colspan="3"></td></tr>
						</table>
					</fieldset>
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
								<td colspan="2" align="left">
									<font face=arial size=1>
										<select name="avahrs" id="avahrs" size=10 class="drpdwne1" style="width:60%" multiple>
										  	<?php
											$selVals =explode(",",$row[12]);

											$dque="SELECT users.username, users.name FROM users  LEFT JOIN sysuser ON users.username = sysuser.username WHERE users.status != 'DA' AND users.usertype!='' AND (sysuser.hrm != 'NO' OR sysuser.accounting != 'NO') AND users.username!='".$username."'";
											$dres=mysql_query($dque,$db);
											while($drow=mysql_fetch_row($dres))
											{
												$getSelect = ($selVals != "" && in_array($drow[0],$selVals)) ? " selected":"";
												print "<option value=".$drow[0]." ".$getSelect.">".$drow[1]."</option>";
											}
											?>
										</select>
							  		</font>
							  		<a href="javascript:clrAll()" class="edit-list">Clear</a>&nbsp;&nbsp;<a href="javascript:selectAll()" class="edit-list">Select All</a>
											
									<div align="right"></div>
								</td>
								<td colspan="2" align="left">
									<font face=arial size=1>
										<select name="avahrsBO" id="avahrsBO" size=10 class="drpdwne1" style="width:70%" multiple>
										  <?php
											$selVals =explode(",",$row[13]);

											$dque="SELECT users.username, users.name FROM users  LEFT JOIN sysuser ON users.username = sysuser.username WHERE users.status != 'DA' AND users.usertype!='' AND (sysuser.hrm != 'NO' OR sysuser.accounting != 'NO') AND users.username!='".$username."'";
											$dres=mysql_query($dque,$db);
											while($drow=mysql_fetch_row($dres))
											{
												$getSelect = ($selVals != "" && in_array($drow[0],$selVals)) ? " selected":"";
												print "<option value=".$drow[0]." ".$getSelect.">".$drow[1]."</option>";
											}
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
			<tr class="NewGridBotBg">
			<!-- <?php
				/*if($pageCall == 'manage')
				{
					$name=explode("|","fa fa-floppy-o~Save|fa-ban~Cancel");
					$link=explode("|","javascript:doUpdate()|../../Manage/managedepartments.php");
				}
				else
				{
					$name=explode("|","fa fa-floppy-o~Save|fa fa-times~Close");
					$link=explode("|","javascript:doUpdate()|javascript:window.close()");
				}
				$heading="user.gif~Edit&nbsp;Department";
				$menu->showHeadingStrip1($name,$link,$heading);*/
			?> -->
			</tr>
			</div>
			<tr><td style="height:5px;"></td></tr>
			
			</table>
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
<script language="javascript">
<?php if($chkDeflt=='N') {?> 
document.onLoad = function(e) {
getLocClass(document.getElementById('location'),"<?php echo $row[5]; ?>","<?php echo $edeptname; ?>");
}
<?php } ?>
</script>
<style type="text/css">
	.drpdwne,.drpdwnacc  {
	 	width: 190px !important;
	}
</style>