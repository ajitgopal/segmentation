<?php
	require("global.inc");
	$addr1=$addr;
	/*
	Modifed Date : February 06, 2010
	Modified By  : Swetha
	Purpose      : Removed the updation of the vistors count
	Task Id      : 4900
	*/
	
	// Condition to check UDF will display for only Frontoffice
	if(isset($_GET['chkAuth']) ){
		$chkAuth = $_GET['chkAuth'];
	}else{
		$chkAuth = '';
	}
	
	$hidecrm = (isset($hidecrm))?$hidecrm:'';

	if($hidecrm == 'hidecrm' && $module == 'Admin_Candidates'){
		$module = 'Admin_JobOrders';
	}
	else{
		$module = $_GET['module'];
	}

	$que="select posid,postitle,deptid 
		from posdesc where posid=".$addr1;
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	$jo_deptid = $row[2]; 
	$jo_name = $row[1];
	// Adding Department Permission 
	$deptAccessObj = new departmentAccess();
	$deptName = $deptAccessObj->getDepartmentName($jo_deptid);
	$deptUsrAccess = $deptAccessObj->getDepartmentUserAccess($username,$jo_deptid,"'FO'"); 
	if (!$deptUsrAccess && $module =="CRM") {
		$deptAlertMsg = $deptAccessObj->displayPermissionAlertMsg($deptName); 
		?>
		<html>
		<head>
		<title>Job Order<?php echo " - ".str_replace("\\","",$jo_name); ?> </title>
		<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
		<script type='text/javascript'> 
		alert("<?php echo $deptAlertMsg;?>");
		window.close();
		</script>				
		</head>
		</html>
		<?php exit(); 			
	} 

	$candrn=strtotime("now");
        if($jobposprw=='yes'){
         HEADER("Location:jobSummary.php?addr=".$addr1."&candrn=".$candrn."&chkAuth=".$chkAuth."&jobposprw=yes&module=".$module);   
        }else{
        	 HEADER("Location:jobSummary.php?addr=".$addr1."&candrn=".$candrn."&chkAuth=".$chkAuth."&module=".$module);   
        }
	
?>
<!--html>
  <head>	
	<script language='javascript'>	
	
	//get the super parent window
	/*function getSuperWindow()
	{	  
	  var parWin=window.opener;
	  while(parWin.opener)
	  {	    
		parWin=parWin.opener;
	  }	 
	 return  parWin;
	}*/
 
 //this code is for updating the main grid vcount with out refreshing 
  var posid='<?=$addr?>'; 
 /* var gridArr = new Array('','','','','','','','','','','','vcount','');		
  var par=getSuperWindow();
  if((typeof par.gridUpdation=='function') || (typeof par.gridUpdation=='object'))
  {
	par.gridUpdation(posid,gridArr); 
  }
  window.location.href="jobSummary.php?addr="+posid+"&candrn="+<?=$candrn?>;
</script>
</head>
</html--> 