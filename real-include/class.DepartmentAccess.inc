<?php

class departmentAccess
{
	
	public function getDepartmentName($deptid)
	{
		global $maildb,$db;
		$output = "";
		$select = "SELECT deptname FROM department 				
					WHERE sno ='".$deptid."'";
		$result = mysql_query($select,$db);
		if (mysql_num_rows($result)>0) {			
			$row = mysql_fetch_row($result);			
			$output = $row[0];			
		}
		return $output;
	}

	public function getDepartmentAccess($accessuser='',$module='')
	{
		global $maildb,$db;
		$output = 0;
		$select = "SELECT GROUP_CONCAT(DISTINCT dept.sno) AS deptAccess FROM department dept
					JOIN department_permission deptPer ON (deptPer.dept_sno = dept.sno) 
					WHERE deptPer.permission ='".$accessuser."' AND deptPer.type IN (".$module.") 
					GROUP BY deptPer.permission";
		$result = mysql_query($select,$db);
		if (mysql_num_rows($result)>0) {			
			$row = mysql_fetch_row($result);
			if ($row[0] == "") {
				$output = 0;
			}else{
				$output = $row[0];
			}
			
		}
		return $output;
	}

	public function getDepartmentUserAccess($accessuser='',$deptid='',$module='')
	{
		global $maildb,$db;
		$output = false;
		$select = "SELECT DISTINCT dept.sno AS deptAccess FROM department dept
					JOIN department_permission deptPer ON (deptPer.dept_sno = dept.sno) 
					WHERE deptPer.permission ='".$accessuser."' AND deptPer.dept_sno IN(".$deptid.") 
					AND deptPer.type IN (".$module.") GROUP BY deptPer.permission";
		$result = mysql_query($select,$db);
		if (mysql_num_rows($result)>0) {
			$output = true;
		}
		return $output;
	}

	public function displayPermissionAlertMsg($deptName='')
	{
		$output = "Permissions to HRM Department '".$deptName."' required to access information.";
		return $output;
	}

	/* Inserting into Department FO Permission when creating new User */

	public function instDepartmentFOPermissionForNewUsr($accessuser='')
	{
		global $maildb,$db;

		$usrAccess = $accessuser;

	    $select_dept = "SELECT * FROM department WHERE status = 'Active'";
	    $result_dept = mysql_query($select_dept, $db);

	    while ($row_dept = mysql_fetch_array($result_dept)) {
	      $dept_Sno = $row_dept['sno'];

	      $insertFO = "INSERT INTO department_permission (dept_sno,permission,type) VALUES ('".$dept_Sno."','".$usrAccess."','FO')";
	     $output = mysql_query($insertFO, $db);
	    }
		return $output;
	}


}
?>