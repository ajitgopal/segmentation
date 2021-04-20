<?php
	$temp_addr=$addr;
	require("global.inc");	
	$addr=$temp_addr; 

	$candrn=strtotime("now");

	// By any chance the same company is set as parent to itself then set the parent as 0
	$uque = "UPDATE staffoppr_cinfo SET parent=0 WHERE parent=sno AND sno=$addr";
	mysql_query($uque,$db);
	
	// Condition to check UDF will display for only Frontoffice
	if(isset($_GET['chkAuth'])){
		$chkAuth = $_GET['chkAuth'];
	}else{
		$chkAuth = '';
	}	
	if(isset($_GET['module'])){
		$module = ($_GET['module'] == '')? '': $_GET['module'];
	}
	else if(isset($_GET['hidemodule'])){
		$module = ($_GET['hidemodule'] == '')? '': $_GET['hidemodule'];
	}

	if($module == 'Admin_JobOrders' || $module == 'Admin_Contacts' || $module == 'Admin_Companies' || $module == 'Admin_Candidates')
	{
		$module = 'Admin_Companies';
	}else{
		$module = 'CRM';
	}
	$que="select sno,cname,deptid 
		from staffoppr_cinfo where sno=".$addr;
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	$com_deptid = $row[2]; 
	$com_name = $row[1];
	// Adding Department Permission 
	$deptAccessObj = new departmentAccess();
	$deptName = $deptAccessObj->getDepartmentName($com_deptid);
	$deptUsrAccess = $deptAccessObj->getDepartmentUserAccess($username,$com_deptid,"'FO'"); 
?>
<html>
<head>	
<title>Company <?php echo " - ".str_replace("\\","",$com_name); ?> </title>
<?php
if (!$deptUsrAccess && $module =="CRM") {
	$deptAlertMsg = $deptAccessObj->displayPermissionAlertMsg($deptName);
?>
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type='text/javascript'> 
alert("<?php echo $deptAlertMsg;?>");
window.close();
</script>
<?php exit(); }  ?>
<script language='javascript'>		
//get the super parent window
function getSuperWindow()
{
	var parWin=window.opener;
	while(parWin.opener)
		parWin=parWin.opener;
	return  parWin;
}
 
//this code is for updating the main grid vcount with out refreshing 
var compid='<?=$addr?>'; 
window.location.href="companySummary.php?addr="+compid+"&candrn="+<?=$candrn?>+"&Rnd=<?=$candrn?>&newcust=<?=$newcust?>&comp_stat=<?=$comp_stat?>&par_stat=<?=$par_stat?>&chkAuth=<?=$chkAuth?>&module=<?=$module?>";
</script>
</head>
</html>