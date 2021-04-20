<?php
	require("global.inc");
	require_once('json_functions.inc');

	$deptAccessObj = new departmentAccess();
	$deptAccesSno_BO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$keyword = strval($_POST['query']);
	$type = strval($_POST['type']);
	$search_param = "{$keyword}";
	$customers = array();
	$sel_custs = "select sc.sno,sc.cname from staffacc_cinfo sc LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=sc.sno) ";
	if($type=='number'){
		$sel_custs .= " where sc.sno like '%".$search_param."%' AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") GROUP BY sc.sno ORDER BY sc.cname ";
	}
	if($type=='text'){
		$sel_custs .= " where sc.cname like '%".$search_param."%' AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") GROUP BY sc.sno ORDER BY sc.cname ";
	}
	$res_customers = mysql_query($sel_custs);
	if (mysql_num_rows($res_customers) > 0) {
		while($row = mysql_fetch_assoc($res_customers)) {
			$customers[] = $row["sno"].' - '.$row["cname"];
		}
		$customers = str_replace('\\', '', $customers);
		echo json_encode($customers);
	}	
?>