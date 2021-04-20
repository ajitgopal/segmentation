<?php
	require("global.inc");
	require_once($akken_psos_include_path.'defaultTaxes.php');
	
	$addrs=$addr1;
	$hrvalss=$hrvals;
	$hrvalssBO=$hrvalsBO;
	if($nav=="export")
	{
		$addr=$addr2;
		$contents="Department Code,Department Name,Parent Department,Location,Class,# Employees,Front Office Permissions,Back Office Permissions,Created By,Created Date,Modified By,Modified Date \n";

		$que="SELECT depta.sno,depta.depcode AS adeptcode,depta.deptname AS adeptname,
				IF(deptb.depcode!='',CONCAT_WS(' - ',deptb.depcode,deptb.deptname),deptb.deptname) AS parent,
				IF(loc.loccode!='',CONCAT_WS(' - ',loc.loccode,loc.heading),loc.heading) AS locname, 
				class.classname,
				IF(emp_cnt.ct!='',emp_cnt.ct,0) AS empcnt,
				GROUP_CONCAT(DISTINCT elFO.name ORDER BY elFO.name ASC) AS FOempnames,
				GROUP_CONCAT(DISTINCT elBO.name ORDER BY elBO.name ASC) AS BOempnames,				 
				curs.name AS cname, 
				".tzRetQueryStringDTime('depta.stime','DateTime','/')." AS cdate,
				murs.name AS mname,
				".tzRetQueryStringDTime('depta.etime','DateTime','/')." AS mdate				
				FROM contact_manage loc,department depta 
				LEFT JOIN department_permission dept_per ON (depta.sno = dept_per.dept_sno)
				LEFT JOIN department_permission dept_perFO ON (depta.sno = dept_perFO.dept_sno and dept_perFO.type='FO')
				LEFT JOIN department_permission dept_perBO ON (depta.sno = dept_perBO.dept_sno AND dept_perBO.type='BO')
				LEFT JOIN department deptb ON depta.parent=deptb.sno 
				LEFT JOIN users curs ON depta.createdby=curs.username 
				LEFT JOIN users murs ON depta.modifiedby=murs.username 
				LEFT JOIN emp_list elFO ON (elFO.username = dept_perFO.permission AND dept_perFO.type='FO')
				LEFT JOIN emp_list elBO ON (elBO.username = dept_perBO.permission AND dept_perBO.type='BO')
				LEFT JOIN class_setup class ON (depta.classid=class.sno AND class.status='ACTIVE') 
				LEFT JOIN (SELECT COUNT(DISTINCT(emp_list.sno)) ct,dept FROM emp_list 
				LEFT JOIN hrcon_general ON emp_list.username=hrcon_general.username 
				LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username 
				LEFT JOIN hrcon_compen ON emp_list.username=hrcon_compen.username 
				WHERE hrcon_w4.ustatus='active' AND hrcon_compen.ustatus='active' 
				AND hrcon_general.ustatus='active' AND emp_list.lstatus NOT IN ('DA','INACTIVE') 
				AND emp_list.empterminated != 'Y' GROUP BY dept) emp_cnt ON emp_cnt.dept=depta.sno				  
				WHERE loc.serial_no=depta.loc_id 
				AND dept_per.permission = '".$username."'  AND depta.sno IN (".$addr.")
				AND depta.status='Active' GROUP BY depta.sno ORDER BY depta.deptname";

		/*$que="SELECT a.sno,a.depcode,a.deptname,
			IF(b.depcode!='',CONCAT_WS(' - ',b.depcode,b.deptname),b.deptname) AS parent,
			IF(loc.loccode!='',CONCAT_WS(' - ',loc.loccode,loc.heading),loc.heading) locname,
			class.classname,
			IF(emp_cnt.ct!='',emp_cnt.ct,0) as empcnt,
			group_concat(nm) AS empnames,
			curs.name cname,
			".tzRetQueryStringDTime('a.stime','DateTime','/')." cdate,
			murs.name mname,
			".tzRetQueryStringDTime('a.etime','DateTime','/')." mdate 
			FROM contact_manage loc,department a 
			LEFT JOIN department b ON a.parent=b.sno 
			LEFT JOIN users curs ON a.createdby=curs.username 
			LEFT JOIN users murs ON a.modifiedby=murs.username 
			LEFT JOIN class_setup class ON (a.classid=class.sno AND class.status='ACTIVE') 
			LEFT JOIN (SELECT COUNT(DISTINCT(emp_list.sno)) ct,dept FROM emp_list LEFT JOIN hrcon_general ON emp_list.username=hrcon_general.username 
			LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username 
			LEFT JOIN hrcon_compen ON emp_list.username=hrcon_compen.username WHERE hrcon_w4.ustatus='active' AND hrcon_compen.ustatus='active' AND hrcon_general.ustatus='active' AND emp_list.lstatus NOT IN ('DA','INACTIVE') AND emp_list.empterminated != 'Y' GROUP BY dept) emp_cnt ON emp_cnt.dept=a.sno 
			LEFT JOIN (SELECT name nm,username FROM emp_list) AS emp_name ON FIND_IN_SET(emp_name.username,a.permission) WHERE loc.serial_no=a.loc_id AND a.sno IN (".$addr.") AND FIND_IN_SET('".$username."',a.permission)>0 AND a.status='Active' GROUP BY a.sno ORDER BY a.deptname";*/
		$res=mysql_query($que,$db);
	
		while ($data=mysql_fetch_array($res))
		{
			 $contents.="\"".str_replace("\"","\"\"",html_tls_specialchars($data[1],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[2],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[3],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[4],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[5],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",$data[6])."\"".","."\"".str_replace("\"","\"\"",$data[7])."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[8],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[9],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[10],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[11],ENT_QUOTES))."\"".","."\"".str_replace("\"","\"\"",html_tls_specialchars($data[12],ENT_QUOTES))."\"".", \n";
		}
	
		mysql_close($db);

		$importfile_name="Departments.CSV";
		DownLoadFile($importfile_name,$contents,'application/csv',TRUE);
	}
	else if($nav=="added")
	{
        $hrvals=$hrvalss;
		$que="SELECT count(1) FROM department WHERE depcode='".$deptcode."' AND status='Active'";
		$res=mysql_query($que,$db);
		$row=mysql_fetch_row($res);
		$deptname=trim($deptname);
	
		if($row[0]==0)
		{
			// FO Permissions
            if($hrvals=="")
                $hrvals=$username;
            else
                $hrvals.=",".$username;

            // BO Permissions
            if($hrvalssBO=="")
                $hrvalssBO=$username;
            else
                $hrvalssBO.=",".$username;

			$que="INSERT INTO department (deptname,createdby,parent,stime,depcode,loc_id,classid,modifiedby,etime,status) VALUES ('".$deptname."','".$username."','".$avadept."',NOW(),'".$deptcode."','".$location."','".$selClasses."','".$username."',NOW(),'Active')";			
			$res=mysql_query($que,$db);
			$id=mysql_insert_id($db);
			
			if($id)
			{
			   $que_deptaccounts = "INSERT INTO department_accounts(sno,deptid,classid,income_acct,expense_acct,ar_acct,ap_acct,payliability_acct,payexpense_acct,status) VALUES('','".$id."','".$selClasses."','".$selIncome."','".$selExpense."','".$selAccReceivable."','".$selAccPayable."','".$selPayLiability."','".$selPayExpense."','ACTIVE')";
			  $resAccounts=mysql_query($que_deptaccounts,$db);

			  /* FO Permissions */
			  $dept_Sno = $id;
			  $FoPerUrsAry = explode(",",$hrvals);
			  foreach ($FoPerUrsAry as $key => $FoPerUrs) {
			  	$insertFO = "INSERT INTO department_permission (dept_sno,permission,type) VALUES ('".$dept_Sno."','".$FoPerUrs."','FO')";
        		mysql_query($insertFO, $db);
			  }

			   /* BO Permissions */
			  $BoPerUrsAry = explode(",",$hrvalssBO);
			  foreach ($BoPerUrsAry as $key => $BoPerUrs) {
			  	$insertBO = "INSERT INTO department_permission (dept_sno,permission,type) VALUES ('".$dept_Sno."','".$BoPerUrs."','BO')";
        		mysql_query($insertBO, $db);
			  }
			}

			if($addrs!="")
			{
                $addr1=$addrs;
				$empunames="";
				$query1="select username from emp_list where sno in (".$addr1.")";
				$res1=mysql_query($query1,$db);
				while($row1=mysql_fetch_row($res1))
				{
					if($empunames=="")
						$empunames=$row1[0];
					else
						$empunames.="','".$row1[0];
				}
				if($empunames!=" ")
				{
					$que2="update hrcon_compen set dept='".$id."',location='".$location."' where username in ('".$empunames."') and ustatus='active'";
					mysql_query($que2,$db);
					
					getDefaultAccInfo($empunames,$db);						
				}
			}
			
        ?>
        <script language=javascript>
            if(window.opener)
            {
               if(window.opener.name=="maindepts")
                {
                    var toList = window.opener.document.conreg.dept;
                	var nLen = toList.length;
                	
                	toList.options.length = nLen+1;
            		toList.options[nLen].text = "<?php echo $deptcode." - ".$deptname; ?>";
            		toList.options[nLen].value = "<?php echo $id; ?>";
            		toList.options[nLen].selected = true;
                }
                else
                {
                    var parwin=window.opener.location.href;
                    window.opener.location.href=parwin;
                }
            }
            window.close();
        </script>
        <?php
		}
		else
		{
			mysql_close($db);
  	        Header("Location:adddept.php?error=exist&deptname=".urlencode($deptcode." - ".$deptname));
		}
	}
	else if($nav=="move")
	{
		if($addr2!="")
		{
			$empunames="";
			$que1="select username from emp_list where sno in (".$addr2.")";
			$res1=mysql_query($que1,$db);
			while($row1=mysql_fetch_row($res1))
			{
				if($empunames=="")
					$empunames=$row1[0];
				else
					$empunames.="','".$row1[0];
			}
			if($empunames!=" ")
			{
				$getLocSql="SELECT loc_id FROM department WHERE sno = '".$edeptname1."' AND status='Active'";
				$getLocRes=mysql_query($getLocSql,$db);
				$getLocRow=mysql_fetch_row($getLocRes);
				
				$que2="UPDATE hrcon_compen SET dept='".$edeptname1."',location='".$getLocRow[0]."' WHERE username IN ('".$empunames."') AND ustatus='active'";
				mysql_query($que2,$db);
				
				
				getDefaultAccInfo($empunames,$db);						
			}
		}
		Header("Location:departments.php");
	}
	else if($nav=="edited")
	{
		$rdeptname1=trim($rdeptname1);
		$que="SELECT COUNT(1) FROM department WHERE depcode='".$deptcode."' AND sno != ".$edeptname." AND status='Active'";
		$res=mysql_query($que,$db);
		$row=mysql_fetch_row($res);
			
		if($row[0]==0)
		{
			// FO Permissions
            if($hrvals=="")
                $hrvals=$username;
            else
                $hrvals.=",".$username;

            // BO Permissions
            if($hrvalsBO=="")
                $hrvalsBO=$username;
            else
                $hrvalsBO.=",".$username;
				
			changeParentChildRelation('department', 'sno', $edeptname, 'parent');
			
			if($location == "")
				$loc_id = $prevLoc;
			else
				$loc_id = $location;
			
			if($prevLoc != "" && $currLoc != "" && ($prevLoc != $currLoc))
			{
				require_once('waitMsg.inc');
				
				$delLocQue = "call loc_dept_update_proc('".$prevLoc."','".$currLoc."','".$username."','".$edeptname."')";
				mysql_query($delLocQue,$db);
				
				$loc_id = $currLoc;
			}
			
			$getClassDept = "SELECT classid, loc_id FROM department WHERE sno='".$edeptname."' AND status='Active'";
			$resClassDept = mysql_query($getClassDept,$db);
			$rowClassDept = mysql_fetch_row($resClassDept);
			
			if($rowClassDept[1] != $loc_id){
				locTaxSet($delLocId, $newLocId, $edeptname);
			}
			
			$que="UPDATE department SET deptname='".$deptname."',depcode='".$deptcode."',classid='".$selClasses."',parent='".$avadept."',modifiedby='".$username."',etime=NOW(),loc_id='".$loc_id."' WHERE sno='".$edeptname."' AND status='Active'";
			$res=mysql_query($que,$db);
			
			// Backup old records
			$backupIns = "INSERT INTO his_department_permission (dept_sno,permission,type,muser,mdate)SELECT dept_sno,permission,type,'".$username."',NOW() FROM department_permission WHERE dept_sno='".$edeptname."'";
			mysql_query($backupIns,$db);

			// Deleting OLD records
			$deleteOld = "DELETE FROM department_permission WHERE dept_sno='".$edeptname."'";
			mysql_query($deleteOld,$db);

			// Inserting Newly

			/* FO Permissions */
			$dept_Sno = $edeptname;
			$FoPerUrsAry = explode(",",$hrvals);
			foreach ($FoPerUrsAry as $key => $FoPerUrs) {
				$insertFO = "INSERT INTO department_permission (dept_sno,permission,type) VALUES ('".$dept_Sno."','".$FoPerUrs."','FO')";
				mysql_query($insertFO, $db);
			}

			/* BO Permissions */
			$BoPerUrsAry = explode(",",$hrvalsBO);
			foreach ($BoPerUrsAry as $key => $BoPerUrs) {
				$insertBO = "INSERT INTO department_permission (dept_sno,permission,type) VALUES ('".$dept_Sno."','".$BoPerUrs."','BO')";
				mysql_query($insertBO, $db);
			}


			$que_update = "UPDATE department_accounts SET status = 'BACKUP' WHERE deptid='".$edeptname."' AND status = 'ACTIVE'";
			$que_res=mysql_query($que_update,$db);
						
			$que_deptaccounts = "INSERT INTO department_accounts(sno,deptid,classid,income_acct,expense_acct,ar_acct,ap_acct,payliability_acct,payexpense_acct,status) VALUES('','".$edeptname."','".$selClasses."','".$selIncome."','".$selExpense."','".$selAccReceivable."','".$selAccPayable."','".$selPayLiability."','".$selPayExpense."','ACTIVE')";
			$resAccounts=mysql_query($que_deptaccounts,$db);
			

			if($rowClassDept[0] != $selClasses)
			{
				require_once('waitMsg.inc');
				
				$changeClass = "call class_dept_update_proc('".$selClasses."','".$edeptname."')";
				mysql_query($changeClass,$db);
			}
			
		  ?>
			<script language=javascript>
				if(window.opener)
				{
				   if(window.opener.name=="maindepts")
					{
						var toList = window.opener.document.conreg.dept;
						var nLen = toList.length;
						
						toList.options.length = nLen+1;
						toList.options[nLen].text = "<?php echo $deptcode." - ".$deptname; ?>";
						toList.options[nLen].value = "<?php echo $id; ?>";
						toList.options[nLen].selected = true;
					}
					else
					{
						var parwin=window.opener.location.href;
						window.opener.location.href=parwin;
					}
				}
				window.close();
			</script>
		<?php
		}
	}
	else if($nav=="remove")
	{
		require_once('waitMsg.inc');
		
		if($avadept==0)
			$avadept=$defdept;
		
		$queryDepClass = "SELECT IFNULL(classid,0) AS classid FROM department_accounts WHERE deptid=".$avadept." AND status='ACTIVE'";
		$sqlDepClass = mysql_query($queryDepClass,$db);
		$rowDepClass = mysql_fetch_assoc($sqlDepClass);
		
		$delLocQue = "call department_del_proc('".$delIds."','".$avadept."', '".$username."', ".$rowDepClass['classid'].")";
		mysql_query($delLocQue,$db);
		
		$del_que = 'UPDATE department SET status="Deleted" WHERE sno IN ('.$delIds.') AND status = "Active"';
		$del_res = mysql_query($del_que,$db);
		
		if($del_res)
		{
			?>
			<script language="javascript">
				<?php if($pageCall == "mainGrid") { ?>
					window.opener.location.href="departments.php?mes=Department(s) has been Removed successfully.";
				<?php } else { ?>
					window.opener.location.href="../../Manage/managedepartments.php?err=suc_del&id=<?php echo $delIds; ?>";
				<?php } ?>
				window.close();
			</script>
			<?php
		}
	}
?>
