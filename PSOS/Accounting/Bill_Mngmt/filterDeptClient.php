<?php
	require("global.inc");
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$clients = 0;
	$que="select invoice.client_name FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid WHERE invoice.deliver='$deliver' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno.") GROUP BY invoice.client_name";
	$res=mysql_query($que,$db);
	while($dd=mysql_fetch_row($res))
		$clients = ($clients==0) ? $dd[0] : $clients.",".$dd[0];

	if($locid!="")
		$wcl=" AND Client_Accounts.loc_id=$locid ";

	if($deptid!="")
		$wcl.=" AND Client_Accounts.deptid=$deptid ";

	$result = "";

	$que = "select CONCAT(invoice.client_name,'|akkenPSplit|',".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1).") FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid WHERE invoice.deliver='$deliver' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND staffacc_cinfo.sno IN ($clients) $wcl GROUP BY invoice.client_name ORDER BY ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1);
	$res = mysql_query($que,$db);
	while($row = mysql_fetch_row($res))
		$result = ($result=="") ? $row[0] : $result."|akkenCSplit|".$row[0];

	echo $result;
?>