<?php
	ob_start();
	require("global_reports.inc");
	$pageRR = $pageRecRep;
	$rlib_filename="department.xml";
	require("rlib.inc");
 	$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	if($format=="")
		$format="html";
	$filename="department";
	$module="department";
	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$db);
		$vrow=mysql_fetch_row($rresult);
		$pageRecRep=$vrow[0];
		
		 /* TLS-01202018 */
		
		session_update("pageRecRep");
		$rdata=explode("|",$pageRecRep);		
	}
	else if ($view=="department")
	{
		$pageRecRep = $pageRR;
		session_register("pageRecRep");

		$rdata=explode("|",$pageRecRep);
		$tab=$rdata[13];
	}
	else
	{
		session_register("pageRecRep");
		$rdata=explode("|",$pageRecRep);
		$tab=$rdata[13];
	}
	if($tab=="addr" || ($view=="myreport" && $vrow[0]!="") || ($view=="department"))
	{ 
		$sortarr=explode("~",$rdata[14]);

		$rep_orient=$rdata[5]!="" ? $rdata[5] : "landscape";
		$rep_paper=$rdata[6]!="" ? $rdata[6] : "letter";

		$rep_company=$rdata[7]!="" ? stripslashes($rdata[7]) : "";
		$rep_header=$rdata[8]!="" ? stripslashes($rdata[8]) : "";
		$rep_title=$rdata[9]!="" ? stripslashes($rdata[9]) : "";
		$rep_date=$rdata[10]!="" ? $rdata[10] : "";
		$rep_page=$rdata[11]!="" ? $rdata[11] : "";
		$rep_footer=$rdata[12]!="" ? stripslashes($rdata[12]) : "";

		if($rdata[16]=="")
		{
			$rep_sortorder="ASC";
			$rep_sortcol="deptname";
		}
		else
		{
			$rep_sortorder=$rdata[16];
			$rep_sortcol=$rdata[15];
		}

		$deptname[0]=$rdata[0];
		$permissions[0]=$rdata[1];
		$deptparent[0]=$rdata[2];
		$empname[0]=$rdata[3];
		$deptdate[0]=$rdata[4];
	}
	else
	{
		$sortarr=array("deptname","permissions","deptparent","empname","deptdate");
	
		$rep_orient="landscape";
		$rep_paper="letter";
		
		$rep_company=$companyname;
		$rep_header="Departments Report";
		$rep_title="Departments";
		$rep_date="date";
		$rep_page="pageno";
		$rep_footer="Footer";	

		$rep_sortorder="ASC";
		$rep_sortcol="deptname";

		$deptname[0]="deptname";
		$permissions[0]="permissions";
		$empname[0]="empname";
		$deptdate[0]="deptdate";
		$deptparent[0]="deptparent";
		
	}
        $deptname[1]="Department Name";
		$permissions[1]="Permissions";
		$deptparent[1]="Parent Department";
		$empname[1]="No.of Employees";
		$deptdate[1]="Created Date";
		
		$deptname[2]="-------------------";
		$permissions[2]="------------------------";
		$empname[2]="--------------";
		$deptdate[2]="------------------";
		$deptparent[2]="------------------";
		



    $rep_sortcolno="";
    if($sortarr[0]!="")
    {
    	for($q=0;$q<count($sortarr);$q++)
    	{
    		if($sortarr[$q]==$rep_sortcol)
    		{
    			$rep_sortcolno=$q;
    		}
    	}
    }

	$k=0;

	for($q=0;$q<count($sortarr);$q++)
	{
		if($sortarr[$q]=="deptname")
		{
			if($deptname[0]!="")
			{
				$data[0][$k]=$deptname[0];
				$headval[0][$k]=$deptname[1];
				$headval[1][$k]=$deptname[2];
				$k++;
			}
		}
		else if($sortarr[$q]=="permissions")
		{
			if($permissions[0]!="")
			{
				$data[0][$k]=$permissions[0];
				$headval[0][$k]=$permissions[1];
				$headval[1][$k]=$permissions[2];
				$k++;
			}
		}
		else if($sortarr[$q]=="empname")
		{
			if($empname[0]!="")
			{
				$data[0][$k]=$empname[0];
				$headval[0][$k]=$empname[1];
				$headval[1][$k]=$empname[2];
				$k++;	
			}
		}
		else if($sortarr[$q]=="deptdate")
		{
			if($deptdate[0]!="")
			{
				$data[0][$k]=$deptdate[0];
				$headval[0][$k]=$deptdate[1];
				$headval[1][$k]=$deptdate[2];	
				$k++;		
			}
		}
		else if($sortarr[$q]=="deptparent")
		{
			if($deptparent[0]!="")
			{
				$data[0][$k]=$deptparent[0];
				$headval[0][$k]=$deptparent[1];
				$headval[1][$k]=$deptparent[2];	
				$k++;		
			}
		}
		
	}
	if($k!=0)
	{
		$data[0][$k]="link";
		$k++;
		$data[0][$k]="link_length";
	}

	

	$query="SELECT a.sno,a.deptname,b.deptname as parent,group_concat(DISTINCT dp.permission),".tzRetQueryStringDTime("a.stime","Date","/")." FROM department a  LEFT JOIN department b ON a.parent=b.sno LEFT JOIN department_permission dp ON dp.dept_sno = a.sno WHERE a.sno !='0' AND a.sno IN ({$deptAccesSno})  AND a.status='Active' group by a.sno";
	
	$res=mysql_query($query,$db);

	$i=1;
	while ($arr = mysql_fetch_array($res))
	{
            $perm="";
			$que="select count(*) from emp_list LEFT JOIN hrcon_compen ON emp_list.username=hrcon_compen.username where hrcon_compen.dept='".$arr[0]."' and hrcon_compen.ustatus='active' and emp_list.lstatus!='DA'";
			$res1=mysql_query($que,$db);
			$row=mysql_fetch_row($res1);
		
			$que1="select name from emp_list where username in (".$arr[3].")";
			$res1=mysql_query($que1,$db);
			while ($row1=mysql_fetch_row($res1))
			{
				if ($perm=="")
					$perm=$row1[0];
				else
					$perm.=",".$row1[0];
			}
			
			
			$ii=0;
			
			for($q=0;$q<count($sortarr);$q++)
            {
              
			if($sortarr[$q]=="deptname")
			{

				if($deptname[0]!="")
				{	
					$data[$i][$ii]=$arr[1];
					$sslen1=trim((strlen($arr[1]) <= strlen($deptname[2])) ? strlen($arr[1]) : (strlen($deptname[2])+3));
					$ii++;
					
				}
			}
			if($sortarr[$q]=="permissions")
			{
				if($permissions[0]!="")
				{		
					$data[$i][$ii]=$perm;
					$sslen2=trim((strlen($perm) <= strlen($permissions[2])) ? strlen($perm) : (strlen($permissions[2])+3));
					$ii++;
				}
			}
			if($sortarr[$q]=="empname")
			{
				if($empname[0]!="")
				{
					$data[$i][$ii]=$row[0];
					$sslen3=trim((strlen($row[0]) <= strlen($empname[2])) ? strlen($row[0]) : (strlen($empname[2])+3));
					$ii++;
				}
			}
			if($sortarr[$q]=="deptdate")
			{
				if($deptdate[0]!="")	
				{
					$data[$i][$ii]=$arr[4];
					$sslen4=trim((strlen($arr[4]) <= strlen($deptdate[2])) ? strlen($arr[4]) : (strlen($deptdate[2])+3));
					$ii++;
				}
			}
			if($sortarr[$q]=="deptparent")
			{
				if($deptparent[0]!="")	
				{
					$data[$i][$ii]=$arr[2];
					$sslen5=trim((strlen($arr[2]) <= strlen($deptparent[2])) ? strlen($arr[2]) : (strlen($deptparent[2])+3));
					$ii++;
				}
			}
			
		}
			
		for($q=0;$q<count($sortarr);$q++)
		{
			if($sortarr[$q]=="deptname")
			{ 
				if($deptname[0]!="")
					$slength=$sslen1;					
					break;
					
			}
			else if($sortarr[$q]=="permissions")
			{
				if($permissions[0]!="")
					$slength=$sslen2;
					break;
			}
			else if($sortarr[$q]=="empname")
			{
				if($empname[0]!="")
					$slength=$sslen3;
					break;
			}
			else if($sortarr[$q]=="deptdate")
			{
				if($deptdate[0]!="")
					$slength=$sslen4;
					break;
			}
			else if($sortarr[$q]=="deptparent")
			{
				if($parent[0]!="")
					$slength=$sslen5;
					break;
			}
		}
		$data[$i][$ii]="javascript:showDept('$arr[0]','$def_appsvr_domain')";
		$ii++;
		if(($deptname[0]!="") || ($permissions[0]!="") || ($empname[0]!="") || ($deptdate[0]!="") || ($deptparent[0]!=""))
		{
			
			$data[$i][$ii]=$slength;
			$ii++;
		}
		$i++;			
	}
	$data=cleanArray($data);
	if($data=="")
	{
		$data=array();
		$data[0][0]="";
		$headval=array();
		$headval[0][0]="";
	}
	$rep_length=$i-1;
	require("rlibdata.php");

 	if($defaction == "print")
 		echo "<script>window.print(); window.setInterval('window.close();', 10000)</script>";

    function cleanArray($array)
	{
	   foreach ($array as $index => $value)
	   {
		   if(is_array($array[$index]))
				$array[$index] = cleanArray($array[$index]);
		   if(empty($value) && $value!=0)
				$array[$index]="";
	   }
	   return $array;
	}
    
	/* Added code to remove javascript code appending in CSV file for recruitment reports */
	if ($format == 'html')
	{
		 echo '<script src=/BSOS/scripts/reportwinclose.js language="javascript"></script>';
	}
?>
