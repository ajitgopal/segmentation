<?php
	require("global.inc");
	require("dispfunc.php");

	$deptAccessObj = new departmentAccess();
	$deptAccesSno_FO = $deptAccessObj->getDepartmentAccess($username,"'FO'");
	$deptAccesSno_BO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	if(isset($_GET['id']))
		$letter = $_GET['id'];

	if(isset($_GET['search']))
		$search = $_GET['search'];
?>
<html>
<head>
<title>Search and Select Company</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function company(str)
{
	id = "com"+str;
	if(document.getElementById(id))
		document.getElementById(id).className="mouseovercont";
}

function company_out(str)
{
	id = "com"+str;
	if(document.getElementById(id))
		document.getElementById(id).className = 'mouseoutcont';
}
  
function win(val,val2,val3,val4,val5,val6)
{
	parent.info_pass(val,val2,val3,val4,val5,val6);
}
</script>
</head>

<body>

<?php 
function checkChild($csno)
{
	global $maildb,$db,$username,$All_Child_Snos;

	$Child_Sql="select sno from staffoppr_cinfo where parent='".$csno."' AND status='ER' AND crmcompany = 'Y' AND (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ";
	$Child_Res=mysql_query($Child_Sql,$db);
	$Child_Rows=mysql_num_rows($Child_Res);
	while($Child_Data=mysql_fetch_row($Child_Res))
	{
		if($All_Child_Snos=='')
			$All_Child_Snos=$Child_Data[0];
		else
			$All_Child_Snos.=",".$Child_Data[0];
		checkChild($Child_Data[0]);
	}
}

$All_Child_Snos='';
if($compSno!='')
	checkChild($compSno);

//get all the child snos and have a condition correspondingly
if($All_Child_Snos!='') 
	$Child_Condition=" AND sno NOT IN (".$All_Child_Snos.")";

if($search != "")
{
	$q=1;
	$qstr="";

	$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource ,parent,status from staffoppr_cinfo where sno!='".$compSno."' AND  status='ER' and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") and crmcompany = 'Y' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ".$qstr.$Child_Condition." and cname LIKE '".$search."%'  order by cname ";
	$res1=mysql_query($que1,$db);
	?>
	<table border="0" cellpadding="1" cellspacing="1" width="100%" >
	<? $num_rows = mysql_num_rows($res1); 
	if($num_rows > 0) 
	{
		while($dd1=mysql_fetch_row($res1))
		{ 
			$cname = html_tls_specialchars(addslashes($dd1[1])); 
			$add1 = html_tls_specialchars(addslashes($dd1[2]));
			$add12 = html_tls_specialchars(addslashes($dd1[3]));
			$city = html_tls_specialchars(addslashes($dd1[4]));
			$state = html_tls_specialchars(addslashes($dd1[5]));

			$pass_var = '';

			if($add1 != '')
				$pass_var .= $add1;
			if($add2 != '')
				$pass_var .= " ".$add2;
			if($city != '')
				$pass_var .= " ".$city;
			if($state != '')
				$pass_var .= " ".$state;

			$sel_cont = "select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname from staffoppr_contact where csno =".$dd1[0];
			$sel_res = mysql_query($sel_cont,$db);
			$sel_contact = "";
			$sel_contact_sno = "";
			while($sel_row = mysql_fetch_row($sel_res))
			{
				$sel_name = $sel_row[2]." ".$sel_row[3]." ".$sel_row[4];
				$sel_contact .=  html_tls_specialchars(addslashes($sel_name))."|";
				$sel_contact_sno .= $sel_row[1]."|";
			}
			$list_cont = trim($sel_contact,"|");
			$list_cont_sno = trim($sel_contact_sno,"|");
			?>
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<? echo $pass_var;?>','<?=$list_cont?>','<?=$list_cont_sno?>','<?=$dd1[25]?>','<?=$cname?>')"><? echo str_replace("\\","",$cname); ?> <? echo str_replace("\\","",$pass_var); ?> </td>
			</tr>
			<tr nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<?
			$q++;
		}
	}
	else
	{
		?>
		<tr class="mouseoutcont"> 
			<td colspan="5" height="22" align="center"><b>Search results not found.</b></td>
		</tr>
		<?
	}
	?>
	</table>
	<?
}
else
{
	$qstr="";
	$q=1;

	if($letter == "")
		$letter = 'a';

	if($letter == "others")
	{   
		?>
		<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?
		for($i=0;$i<10;$i++)
		{
			$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource ,parent,status  from staffoppr_cinfo  where sno!='".$compSno."' AND  status='ER' and crmcompany = 'Y' and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ".$qstr.$Child_Condition." and cname LIKE '".$i."%'  order by cname";
			$res1=mysql_query($que1,$db);  
			while($dd1=mysql_fetch_row($res1))
			{
				$test = $q;
				$cname = html_tls_specialchars(addslashes($dd1[1])); 
				$add1 = html_tls_specialchars(addslashes($dd1[2]));
				$add12 = html_tls_specialchars(addslashes($dd1[3]));
				$city = html_tls_specialchars(addslashes($dd1[4]));
				$state = html_tls_specialchars(addslashes($dd1[5]));

				$pass_var = '';
				if($add1 != '')
					$pass_var .= $add1;
				if($add2 != '')
					$pass_var .= " ".$add2;
				if($city != '')
					$pass_var .= " ".$city;
				if($state != '')
					$pass_var .= " ".$state;

				$sel_contact = "";
				$sel_contact_sno = "";
				$sel_cont = "select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname from staffoppr_contact where csno =".$dd1[0];
				$sel_res = mysql_query($sel_cont,$db);
				while($sel_row = mysql_fetch_row($sel_res))
				{
					$sel_name = $sel_row[2]." ".$sel_row[3]." ".$sel_row[4]; 
					$sel_contact .= html_tls_specialchars(addslashes($sel_name))."|";
					$sel_contact_sno .= $sel_row[1]."|";
				}
				$list_cont = trim($sel_contact,"|");
				$list_cont_sno = trim($sel_contact_sno,"|");
				?>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
					<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<? echo $pass_var;?>','<?=$list_cont?>','<?=$list_cont_sno?>','<?=$dd1[25]?>','<?=$cname?>')"><b><? echo str_replace("\\","",$cname); ?></b> <? echo str_replace("\\","",$pass_var); ?></td>
				</tr>
				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++;
			}
		}

		if($test == 0)
		{
			?>
			<tr class="mouseoutcont">
				<td colspan="5" height="22" align="center"><b>Results not found.</b></td>
			</tr>
			<?
		} 
		?>
		</table>
		<?	
	}
	else
	{
		$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource ,parent,status from staffoppr_cinfo where sno!='".$compSno."' AND status='ER' and crmcompany = 'Y' and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ".$qstr.$Child_Condition." and cname LIKE '".$letter."%'  order by cname";
		$res1=mysql_query($que1,$db); 
		?>
		<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?
		$q=1;
		$num_rows = mysql_num_rows($res1); 
		if($num_rows > 0) 
		{
			while($dd1=mysql_fetch_row($res1))
			{
				$cname =html_tls_specialchars(addslashes($dd1[1])); 
				$add1 = html_tls_specialchars(addslashes($dd1[2]));
				$add12 = html_tls_specialchars(addslashes($dd1[3]));
				$city = html_tls_specialchars(addslashes($dd1[4]));
				$state = html_tls_specialchars(addslashes($dd1[5]));

				$pass_var = '';
				if($add1 != '')
					$pass_var .= $add1;
				if($add2 != '')
					$pass_var .= " ".$add2;
				if($city != '')
					$pass_var .= " ".$city;
				if($state != '')
					$pass_var .= " ".$state;

				$sel_contact = "";
				$sel_contact_sno = "";

				$sel_cont = "select TRIM(CONCAT_WS('',fname,' ',lname,'(',IF(email='',nickname,email),')')),sno,fname,mname,lname from staffoppr_contact where csno =".$dd1[0];
				$sel_res = mysql_query($sel_cont,$db);
				while($sel_row = mysql_fetch_row($sel_res))
				{
					$sel_name = $sel_row[2]." ".$sel_row[3]." ".$sel_row[4];
					$sel_contact .= html_tls_specialchars(addslashes($sel_name))."|";
					$sel_contact_sno .= $sel_row[1]."|";
				}
				$list_cont = trim($sel_contact,"|");
				$list_cont_sno = trim($sel_contact_sno,"|");
				?>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont"> 
					<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<? echo $pass_var; ?>','<?=$list_cont?>','<?=$list_cont_sno?>','<?=$dd1[25]?>','<?=$cname?>')"><? echo str_replace("\\","",$cname); ?> <? echo str_replace("\\","",$pass_var); ?></td>
				</tr>
				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++; 
			}
		}
		else
		{
			?>
			<tr class="mouseoutcont">
				<td colspan="5" height="22" align="center"><b>Results not found.</b></td>
			</tr>
			<?
		}
	}
	?>
	</table>
	<?
}
?>
</body>
</html>