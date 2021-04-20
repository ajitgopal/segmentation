<?php
	require("global.inc");

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	if($locid!="")
		$wcl = " AND loc_id=$locid ";

	$result = "";

	$que = "SELECT CONCAT(sno,'|akkenPSplit|',deptname) FROM department WHERE 1=1 ".$wcl." AND sno !='0' AND sno IN(".$deptAccesSno.") ORDER BY deptname";
	$res = mysql_query($que,$db);
	while($row = mysql_fetch_row($res))
		$result = ($result=="") ? $row[0] : $result."|akkenCSplit|".$row[0];

	echo $result;
?>