<?php
	require("global.inc");
	$candrn=strtotime("now");
	
	if($_GET['module'] == 'Admin_JobOrders' || $_GET['module'] == 'Admin_Contacts' || $_GET['module'] == 'Admin_Companies' || $_GET['module'] == 'Admin_Candidates')
	{
		$module = 'Admin_Contacts';
	}else{
		$module = 'CRM';
	}

	$que="select CONCAT_WS(' ',staffoppr_contact.fname,if(staffoppr_contact.mname='',' ',staffoppr_contact.mname),staffoppr_contact.lname),staffoppr_contact.deptid from staffoppr_contact where staffoppr_contact.sno=".$addr;
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);	 
	$contact_name = $row[0];
	$contact_deptid = $row[1];
	// Adding Department Permission 
	$deptAccessObj = new departmentAccess();
	$deptName = $deptAccessObj->getDepartmentName($contact_deptid);
	$deptUsrAccess = $deptAccessObj->getDepartmentUserAccess($username,$contact_deptid,"'FO'"); 
	if (!$deptUsrAccess && $module =="CRM") {
		$deptAlertMsg = $deptAccessObj->displayPermissionAlertMsg($deptName); 
		?>
		<html>
		<head>
		<title>Contact <?php echo " - ".str_replace("\\","",$contact_name); ?> </title>
		<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
		<script type='text/javascript'> 
		alert("<?php echo $deptAlertMsg;?>");
		window.close();
		</script>				
		</head>
		</html>
		<?php exit(); 			
	} 

?>
<html>
<head>	
<script language='javascript'>	
	//get the super parent window
	function getSuperWindow()
	{	  
	  var parWin=window.opener;
	  while(parWin.opener)
	  {	    
		parWin=parWin.opener;
	  }	 
	 return  parWin;
	}
	
	
 //this code is for updating the main grid vcount with out refreshing 
var conid='<?=$addr?>';

window.location.href="contactSummary.php?addr=<?=$addr?>&posid=<?=$posid?>&contasno=<?=$contasno?>&newcont=<?=$newcont?>&sesvar=new&candrn=<?=$candrn?>&Rnd=<?=$candrn?>&var_stat=<?=$var_stat?>&typecomp=<?=$typecomp?>&module=<?=$module?>";	
</script>
</head>
</html>