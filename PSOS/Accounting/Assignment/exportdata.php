<?php
	require("global.inc");
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$contents="Customer,Candidate,Type,End Client,($) Rate,Start Date,End Date,Sales Agent \n";
    
	$que1="select emp_list.name,hrcon_jobs.client,hrcon_general.fname,hrcon_general.lname,hrcon_w4.tax,hrcon_jobs.endclient,CONCAT(hrcon_jobs.rate,'/',hrcon_jobs.rateper),hrcon_jobs.s_date,hrcon_jobs.e_date,hrcon_jobs.sagent from emp_list LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username LEFT JOIN hrcon_jobs ON emp_list.username=hrcon_jobs.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = hrcon_jobs.client AND staffacc_cinfo.type IN ('CUST', 'BOTH') LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username LEFT JOIN department ON hrcon_compen.dept=department.sno where hrcon_jobs.sno in (".$addr.") and hrcon_w4.ustatus='active' and hrcon_jobs.ustatus='active' and hrcon_general.ustatus='active' and emp_list.lstatus != 'DA'  and  emp_list.lstatus != 'INACTIVE'   and hrcon_jobs.jtype='OP' and department.sno IN (".$deptAccesSno.") group by emp_list.sno,hrcon_jobs.sno";
	$res1=mysql_query($que1,$db);
    while($row1 = mysql_fetch_array($res1))
	{
     	if ( $row1[5] != 0 ) // For end client
		{
			$que21="select cname from staffacc_cinfo where sno=".$row1[5];
			$res21=mysql_query($que21,$db);
			$row21=mysql_fetch_row($res21);	
			$client=$row21[0];
		}
		else
		{
			$client="";
		}
		
		$que2="select cname from staffacc_cinfo where sno=".$row1[1]."";
		$res2=mysql_query($que2,$db);
		$row2=mysql_fetch_row($res2);	
		
		$que3="select name from emp_list where username='".$row1[9]."'";
		$res3=mysql_query($que3,$db);
		$row3=mysql_fetch_row($res3);
        //if($row1[5]=="none")
            //$row1[5]="";

       	$contents.="\"".str_replace("\"","\"\"",$row2[0])."\"".","."\"".str_replace("\"","\"\"",$row1[2])." ".str_replace("\"","\"\"",$row1[3])."\"".","."\"".str_replace("\"","\"\"",$row1[4])."\"".","."\"".str_replace("\"","\"\"",$client)."\"".","."\"".str_replace("\"","\"\"",$row1[6])."\"".","."\"".str_replace('-','/',$row1[7])."\"".","."\"".str_replace('-','/',$row1[8])."\"".","."\"".str_replace("\"","\"\"",$row3[0])."\"".", \n";
    }

	mysql_close($db);

	//$importfile_name=$realusername.".CSV";
	$importfile_name="Assignments.CSV";
	DownLoadFile($importfile_name,$contents,'application/csv',TRUE);
?>