<?php
	require("global.inc");
	require("dispfunc.php");

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$qstr="";
	$poCond = "";
	if($asstype == "assg_emp")
	{
		if($ponum == '')
		{
			$qstr=" and hrcon_jobs.client='".$sno."' ";
		}
	}
	else if($search_emp == "yes")
	{
		$qstr=" AND CONCAT_WS(' ',TRIM(hrcon_general.lname),IF(TRIM(hrcon_general.fname)='',NULL,TRIM(hrcon_general.fname)),TRIM(hrcon_general.mname)) LIKE '%".$searchval."%' ";
	}
	else 
	{
		if($filter != 'Temp_Contract_OB')
			$filterName = getManage($filter);

		if($filterName == 'Temp/Contract')
		{
			$tcQstr = " AND hrcon_jobs.jtype!='OB'";
			$qstr=" AND IF(hrcon_jobs.jotype!=0,hrcon_jobs.jotype, hrcon_compen.emptype) = '".$filter."'";
		}
		else if($filter == 'Temp_Contract_OB')
		{
			$filterSno = getManageSno('Temp/Contract','jotype');
			$tcQstr = " AND hrcon_jobs.jtype='OB'";
			$qstr=" AND IF(hrcon_jobs.jotype!=0,hrcon_jobs.jotype, hrcon_compen.emptype) = '".$filterSno."'";
		}
		else
		{
			$qstr=" AND IF(hrcon_jobs.jotype!=0,hrcon_jobs.jotype, hrcon_compen.emptype) = '".$filter."' ";
		}
	}

	$and_clause	= '';

	if (!empty($departments)) {

		$and_clause	= ' AND department.sno !=0 AND department.sno IN ('. $departments .')';

		if (empty($asstype) && empty($search_emp) && empty($filter)) {

			$qstr	= '';
		}

	} else {

		$dept_ids	= array();
		$sel_dep_query	= "SELECT d.sno FROM department d WHERE d.sno !='0' AND d.sno IN(".$deptAccesSno.") AND d.status='Active'";
		$res_dep_query	= mysql_query($sel_dep_query, $db);

		if (mysql_num_rows($res_dep_query) > 0) {

			while ($row = mysql_fetch_object($res_dep_query)) {

				$dept_ids[]	= $row->sno;
			}

			$departments	= implode(',', $dept_ids);
		}

		$and_clause	= ' AND department.sno !=0 AND department.sno IN ('. $departments .')';

		if (empty($asstype) && empty($search_emp) && empty($filter)) {

			$qstr	= '';
		}
	}

	$order_clause	= ' ORDER BY trim(hrcon_general.lname), trim(hrcon_general.fname), trim(hrcon_general.mname)';

	if (!empty($sortby)) {

		if ($sortby == 'fname') {

			$order_clause	= ' ORDER BY trim(hrcon_general.fname), trim(hrcon_general.lname) ';

		} elseif ($sortby == 'lname') {

			$order_clause	= ' ORDER BY trim(hrcon_general.lname), trim(hrcon_general.fname) ';

		} elseif ($sortby == 'sno') {

			$order_clause	= ' ORDER BY emp_list.sno ';

		} elseif ($sortby == 'asgno') {

			$order_clause	= ' ORDER BY hrcon_jobs.assign_no ';
		}
	}
	
	if(!empty($ponum))
	{
		$poCond = " AND hrcon_jobs.po_num = '".$ponum."'";
	}

	$dynamicUstatus = " AND ((hrcon_jobs.ustatus IN ('active','closed','cancel') AND (hrcon_jobs.s_date IS NULL OR hrcon_jobs.s_date='' OR hrcon_jobs.s_date='0-0-0' OR (DATE(STR_TO_DATE(s_date,'%m-%d-%Y')) <= '".$servicedateto."'))) AND (IF(hrcon_jobs.ustatus='closed',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$servicedatefrom."'),1)) AND (IF(hrcon_jobs.ustatus='cancel',(hrcon_jobs.e_date IS NOT NULL AND hrcon_jobs.e_date<>'' AND hrcon_jobs.e_date<>'0-0-0' AND hrcon_jobs.e_date <> hrcon_jobs.s_date AND DATE(STR_TO_DATE(e_date,'%m-%d-%Y'))>='".$servicedatefrom."'),1)))";
	
	$listqry = "select ".getEntityDispName("emp_list.sno","CONCAT_WS(' ',hrcon_general.lname,hrcon_general.fname,hrcon_general.mname)",3).",emp_list.sno,emp_list.username,hrcon_jobs.client,hrcon_jobs.assign_no from emp_list,hrcon_general,hrcon_compen,hrcon_jobs,department where emp_list.username = hrcon_general.username and emp_list.username = hrcon_compen.username and emp_list.username = hrcon_jobs.username AND hrcon_compen.dept = department.sno and emp_list.lstatus NOT IN('INACTIVE','DA') and (emp_list.empterminated != 'Y' || (UNIX_TIMESTAMP(DATE_FORMAT(emp_list.tdate,'%Y-%m-%d'))-UNIX_TIMESTAMP())>0) AND  hrcon_jobs.jtype != '' AND hrcon_compen.ustatus = 'active' AND hrcon_compen.timesheet != 'Y' AND hrcon_general.ustatus='active' AND hrcon_jobs.pusername!=''".$dynamicUstatus." $poCond $qstr $tcQstr $and_clause GROUP BY emp_list.username, emp_list.name, hrcon_jobs.assign_no " . $order_clause;
 	$res_assoc_email=mysql_query($listqry,$db);
	$num_rows=mysql_num_rows($res_assoc_email);
	$display="<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">";

	if($num_rows > 0) 
	{
		$count		= 1;
		$listsnos	= '';
		$listnames	= '';
		$compnames	= '';
		$asgnmids	= '';
		$chkflg		= 0;

		while ($fetch_assoc_email=mysql_fetch_row($res_assoc_email)) {

			$uid	= $fetch_assoc_email[2];
			$comuid	= $fetch_assoc_email[3];
			$asgid	= $fetch_assoc_email[4];
			$name	= trim($fetch_assoc_email[0]).' - '.$asgid;

			if($listsnos == "")
				$listsnos = $uid;
			else
				$listsnos .= ",".$uid;

			if($compnames == "")
				$compnames = $comuid;
			else
				$compnames .= ",".$comuid;

			if (empty($asgnmids))
			$asgnmids	= $asgid;
			else
			$asgnmids	.= '#'. $asgid;

			$disp_result="<b>".$name."</b>";
			$disp_result_target=str_replace("<b>","",$disp_result); // value for, add to target list when we press at source
			$disp_result_target=str_replace("</b>","",$disp_result_target);

			if($listnames == "")
				$listnames = html_tls_specialchars($disp_result_target);
			else
				$listnames .= "|".html_tls_specialchars($disp_result_target);

			if($sno != '') {

				$display.="<TR onmouseover=\"return company(".$count.")\" onMouseOut=\"return company_out(".$count.")\" onMouseDown=\"return company_out(".$count.")\" id=\"com".$count."\" class=\"mouseoutcont\"><td colspan=\"5\" height=\"22\" ><a href=\"javascript:addtolistC('".html_tls_specialchars(addslashes($disp_result_target))."','$uid','$comuid','$asgid')\" class=\"mouseoutcont\">$disp_result</a></td></tr><TR nowrap=\"nowrap\"><td colspan=\"5\" bgcolor=\"#ffffff\"></td></tr>";

			} else {

				$display.="<TR onmouseover=\"return company(".$count.")\" onMouseOut=\"return company_out(".$count.")\" onMouseDown=\"return company_out(".$count.")\" id=\"com".$count."\" class=\"mouseoutcont\"><td colspan=\"5\" height=\"22\" ><a href=\"javascript:addtolist('".html_tls_specialchars(addslashes($disp_result_target))."','$uid','$comuid','$asgid')\" class=\"mouseoutcont\">$disp_result</a></td></tr><TR nowrap=\"nowrap\"><td colspan=\"5\" bgcolor=\"#ffffff\"></td></tr>";
			}

			$count++;
 	   }
		
	}
	else if($num_rows=='0')
	{ 
		$display.="<TR><td colspan=\"5\" height=\"100\" valign=\"center\" align=\"center\"><b>Search results not found.</b></td></tr>";
	}
	$display.="<input type='hidden' name='listsnos' value='".$listsnos."'><input type='hidden' name='listnames' value=\"$listnames\"><input type='hidden' name='compnames' id='compnames' value=\"$compnames\"><input type='hidden' name='asgnmids' id='asgnmids' value='".$asgnmids."'></table>";
	echo $display;
?>