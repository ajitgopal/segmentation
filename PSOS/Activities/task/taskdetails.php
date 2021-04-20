<?php
	$addr_temp=$addr;
	require("global.inc");	
	$addr = $addr_temp;

	$deptAccessObj = new departmentAccess();
	$deptAccesSnoFO = $deptAccessObj->getDepartmentAccess($username,"'FO'");

	/*
    Modifed Date: 15 April 2009
    Modified By: Rayudu
    Purpose: Modified query for showing employees who has task manager preference.
    Task Id: 4195
    */

	require("Menu.inc");
	$menu=new EmpMenu();

	$colsno=$addr;
	$addr1=explode("|",$addr);
	$name=explode("|","fa fa-check-circle~Assign|fa fa-times~Close");
    $link=explode("|","javascript:doAssign();|javascript:window.close();");
    $heading="<div>task.gif~Assign&nbsp;Task&nbsp;Manager</div>";

	session_register("colsno");
	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}
?>
<html>
<head>
<title>Assign Task Manager</title>
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript" src="/BSOS/scripts/common_ajax.js"></script>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="scripts/taskdetails.js"></script>
<!-- <script type="text/javascript">
$(document).ready(function() {
		if( $('#selemp').val()!=''){
$('#groupmembers').append('<option value="foo" selected="selected">Foo</option>');
		}
});
</script> -->
<style type="text/css">
	.maingridbuttonspad{padding: 0px;}
	.akkenAvailableField{font-size: 13px;}
</style>
</head>
<body>
<form action='edittask.php' method='post' name='addtask' id='addtask'>
<input type=hidden name='sno' id='sno' value="<?php echo $addr;?>">
<input type=hidden name='selemp' id='selemp' value="<?php echo $selemp;?>" />

<div id="main" class="akkenNewPopUp">
<td valign=top align=center>
<table width=100% cellpadding=0 cellspacing=0 border=0  class="ProfileNewUI" >
	<div id="topheader">
	<tr class="NewGridTopBg">
	<?php $menu->showHeadingStrip1($name,$link,$heading); ?>
	</tr>
	</div>

	<?php
	$que="select tasklist.sno,tasklist.type,tasklist.title,".tzRetQueryStringSelBoxDate('tasklist.startdate','Date','/').",if(tasklist.duedate='0000-00-00', '',".tzRetQueryStringSelBoxDate('tasklist.duedate','Date','/')."),tasklist.taskstatus, tasklist.priority,tasklist.percom,tasklist.notes,".tzRetQueryStringDate('tasklist.datecreated','Date','/').",tasklist.ctime  from tasklist,tasks where sno=".$addr1[0];
	$res=mysql_query($que,$db);
	$data=mysql_fetch_row($res);

	$assign="";
	$que="select sendto from tasklist where contactsno='ass".$addr."'";
	$res=mysql_query($que,$db);
	while($row=mysql_fetch_row($res))
	{
		if($assign=="")
			$assign=$row[0];
		else
			$assign.=",".$row[0];
	}
	
	$tasksName="";
	$tasksQue = "SELECT taskname FROM tasks WHERE taskid IN (".$data[1].")";
	$tasksRes=mysql_query($tasksQue,$db);
	while($tasksData=mysql_fetch_row($tasksRes))
	{
		if($tasksName=="")
			$tasksName=$tasksData[0];
		else
			$tasksName.=", ".$tasksData[0];
	}
	$data[1] = $tasksName;
	?>

	<div id="grid_form">
    <tr>
		<td><img src="/BSOS/images/white.jpg" width="10" height="10"></td>
	</tr>
	<tr>
		<td>
		<table border=0 width=100% cellpadding=3 cellspacing=0>
		<?php
		if($assign!="")
		{
			$que="SELECT tasklist.sno,emp_list.name FROM emp_list LEFT JOIN tasklist ON find_in_set(emp_list.username,tasklist.sendto) WHERE tasklist.contactsno='ass".$addr."' AND emp_list.username in ('".str_replace(",","','",$assign)."') ORDER BY emp_list.name";
			$res=mysql_query($que,$db);
			while($row=mysql_fetch_row($res))
			{
				if($assignEmployees=="")
					$assignEmployees=$row[0]."|".$row[1];
				else
					$assignEmployees.="^".$row[0]."|".$row[1];
			}
		}

		$custtype="none";
		$disable="disabled";

		$employees="";

		if($assign!="")
			$que="SELECT hrcon_compen.dept,hrcon_compen.username,users.userid,emp_list.name,emp_list.sno FROM hrcon_compen LEFT JOIN emp_list ON emp_list.username=hrcon_compen.username LEFT JOIN users ON users.username=emp_list.username LEFT JOIN sysuser ON users.username=sysuser.username WHERE emp_list.lstatus!='DA' AND hrcon_compen.username!='".$username."' AND hrcon_compen.username NOT IN('".str_replace(",","','",$assign)."') AND hrcon_compen.ustatus='active' AND emp_list.lstatus != 'DA' AND users.status !='DA' AND emp_list.empterminated != 'Y' AND LOCATE('+6+',CONCAT(sysuser.collaboration,'+'))>0 AND hrcon_compen.dept !='0' AND hrcon_compen.dept IN (".$deptAccesSnoFO.") GROUP BY emp_list.username ORDER BY TRIM(emp_list.name)";
		else
			$que="SELECT hrcon_compen.dept, hrcon_compen.username, users.userid, emp_list.name,emp_list.sno FROM hrcon_compen, emp_list LEFT JOIN users ON users.username=emp_list.username LEFT JOIN sysuser ON users.username=sysuser.username WHERE emp_list.username=hrcon_compen.username AND emp_list.lstatus NOT IN ('DA','INACTIVE') AND users.status !='DA' AND hrcon_compen.username!='".$username."' AND hrcon_compen.ustatus='active' AND emp_list.empterminated!='Y' AND LOCATE('+6+',CONCAT( sysuser.collaboration,'+'))>0 AND hrcon_compen.dept !='0' AND hrcon_compen.dept IN (".$deptAccesSnoFO.") GROUP BY emp_list.username ORDER BY TRIM(emp_list.name)";
		$res=mysql_query($que,$db);
		while($row=mysql_fetch_row($res))
		{
			if($row[2]!="")
			{
				if($employees=="")
					$employees=$row[0]."|".$row[1]."|".trim($row[3])."|".$row[4];
				else
					$employees.="^".$row[0]."|".$row[1]."|".trim($row[3])."|".$row[4];
			}
		}
		?>
		<input type=hidden name=assignemployees value="<?php echo html_tls_specialchars($assignEmployees,ENT_QUOTES);?>">
  		<input type=hidden name=employees value="<?php echo html_tls_specialchars($employees,ENT_QUOTES);?>">
		<tr>
			<td colspan="3">
				<table border=0 cellpadding=0 cellspacing=0 width=100%>
					<tr>
						<td class=summaryrow>
							<font class=afontstyle>&nbsp;Departments:</font>
						</td>
						<td class=summarytext>
						<nobr>
						&nbsp;
							<select name=dept onChange='getEmployee(document.addtask.dept,document.addtask.emplist,"--Select Employee--")'>
							<option value="none">--Select Department--</option>
							<option value="all">All</option>
							<?php
							$disable="disabled";
							$custtype="none";
							$que="SELECT sno,deptname FROM department WHERE status='Active' AND sno IN(".$deptAccesSnoFO.")";
							$res=mysql_query($que,$db);
							while($row=mysql_fetch_row($res))
								print "<option  value=".$row[0]." ".sele($row[0],$custtype).">".$row[1]."</option>";
							?>
							</select>
						</nobr>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign=top align=left>
				<table border=0 cellpadding=0 cellspacing=0 width=100%>
				<tr>
					<td class="summaryrow akkenAvailableField">
		      			<span>Available Employees List</span>
		      		</td> 
					<!-- <td align=center><font class=afontstyle>Available Employees List</font></td> -->
				</tr>
				<tr>
					<td width="205">
					  <select name="emplist" class=selectboxes multiple size=10 >
					  <option value="none">-- None --</option>
					</td>
				</tr>
				</table>
			 </td>
			 <td align=center class="formCell" width="">
				<div class="akkenPopupAddBtn"><a href="javascript:void(0);" onclick="add()">Add&nbsp;<i class="fa fa-chevron-right"></i></a></div>
				<div class="akkenPopupAddBtn"><a href="javascript:void(0);" onclick="remove()"><i class="fa fa-chevron-left"></i>&nbsp;Remove</a></div>
				<div class="akkenPopupAddBtn"><a href="javascript:void(0);" onclick="doClose('<?php echo $addr; ?>')"><i class="fa fa fa-refresh"></i>&nbsp;Reload</a></div>
			</td>
			<td valign=top align=left>
				<table border=0 cellpadding=0 cellspacing=0 width=100%>
				<tr>
					<td class="summaryrow akkenAvailableField">
			      		<span> Receiving Employees List</span>
			  		</td>
					<!-- <td align=center><font class=afontstyle>Receiving Employees List</font></td> -->
				</tr>
				<tr>
					<td width="205">
					<select class=selectboxes name="groupmembers" id="groupmembers" multiple size=10>
					<!-- <option value='No Members Added'>No Members Added</option>; -->
					<?php if($selemp=='') { ?>
						<option value="No Members Added">No Members Added</option>
					<?php } 
					if($selemp!='')
					{
						$explode_emp=explode(',',$selemp);
						$implode_emp=implode("','",$explode_emp);
						$que1="SELECT username,name,sno AS name FROM emp_list WHERE username IN ('".$implode_emp."') ";
						$res1=mysql_query($que1,$db);
						if(mysql_num_rows($res1)>0)
						{
							while($drow=mysql_fetch_row($res1))
							{
								$getSelect = ($selemp != "" && in_array($drow[0],$selemp)) ? " selected":"";
								print "<option value=".$drow[0]." ".$getSelect.">".$drow[1]." - ".$drow[2]."</option>";
							}
						}
						else
						{
							print "<option value='No Members Added'>No Members Added</option>";	
						}	
					}		
					?>
					
					</select>
					</td>
				</tr>
				</table>			
			</td>
		</tr>
		<tr>
			<td>
				<font class=afontstyle>
					<a href="javascript:selectAll()">Select All</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="javascript:clearAll()">Clear All</a>
				</font>
			</td>
		</tr>
		<tr>
		  	<td colspan="3" class="akkenPopupPad_t_10" style="font-size:13px;font-family:arial;">
				<span style="color:#ff0000; font-weight:bold">Note</span> : Employees who have User Accounts with Collaboration-Task Manager preference set will be shown in the Available Employees List.
			</td>
		</tr>
		</table>
		</td>
	</tr>
    <tr>
		<td><img src="/BSOS/images/white.jpg" width="10" height="10"></td>
	</tr>
	</div>

	<div id="botheader">
	<tr class="NewGridBotBg">
		<?php //$menu->showHeadingStrip1($name,$link,$heading); ?>
	</tr>
	</div>

</table>
</td>
</div>
</tr>
</table>
</td>
</form>
</body>
</html>
<script language="javascript">
	getAssignEmployee(document.addtask.dept,document.addtask.groupmembers,"--Select Employee--");
</script>