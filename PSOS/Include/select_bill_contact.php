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

	if($by=='bycompany')
	{
		$onames = 'cnames';
		$likes = " staffoppr_cinfo.cname ";
	}
	else
	{
		$onames = 'names';
		$likes = " TRIM(CONCAT_WS('',staffoppr_contact.fname,' ',staffoppr_contact.lname,'(',IF(staffoppr_contact.email='',staffoppr_contact.nickname,staffoppr_contact.email),')')) ";
	}
?>
<html>
<head>
<title>Search and Select Contact</title>
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

function win(val,sname,csno,cname,status,compname)
{
	parent.info_pass(val,sname,csno,cname,status,compname);
} 
</script>
</head>

<body>
<?php 
if(trim($search) != "")
{
	$q=1;
	$qstr="";

	if($by=='bycompany')
	{
		$onames = 'cnames';
		$like_chk = " staffoppr_cinfo.cname LIKE  '".$search."%'";	
	}
	else
	{
		$onames = 'names';

		$like_chk="";
		if(count(explode(" ",trim($search)))>2)
			$like_chk= " CONCAT_WS(' ', staffoppr_contact.fname, IF(staffoppr_contact.mname ='',NULL,staffoppr_contact.mname), staffoppr_contact.lname) LIKE  '".trim($search)."%'";
		else
			$like_chk= " CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.lname) LIKE  '".trim($search)."%'";
	}
			
	$que1="select CONCAT('',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)) as names,staffoppr_contact.csno, staffoppr_contact.status,staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname,staffoppr_contact.address1,staffoppr_contact.address2,staffoppr_contact.city,staffoppr_contact.state,staffoppr_contact.wphone,staffoppr_contact.ytitle,staffoppr_cinfo.cname as cnames,staffoppr_cinfo.address1,staffoppr_cinfo.address2,staffoppr_cinfo.city,staffoppr_cinfo.state from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno = staffoppr_cinfo.sno where staffoppr_contact.status='ER' and (FIND_IN_SET('".$username."',staffoppr_contact.accessto)>0 or staffoppr_contact.owner = '".$username."' or staffoppr_contact.accessto='ALL') ".$qstr." and  staffoppr_contact.crmcontact='Y' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN (".$deptAccesSno_FO.") and ".$like_chk." order by ".$onames;
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
			$pass_var = "."; 
			$dd1[1] = stripslashes($dd1[1]);
			$dd1[13] = stripslashes($dd1[13]);
			$pass_newvar = $dd1[1]." ".$pass_var;

			$new_str = addslashes($dd1[4])." ".addslashes($dd1[5])." ".addslashes($dd1[6]);

			if($dd1[7] == '')
				$add1 = '';
			else
				$add1 = $dd1[7];
			if($dd1[8] == '')
				$add2 = '';
			else
				$add2 = ",".$dd1[8];	
			if($dd1[9] == '')
				$city = '';
			else
				$city = ",".$dd1[9];
			if($dd1[10] == '')
				$state = '';
			else
				$state = ",".$dd1[10];
			$address1 = " $add1 $add2 $city $state ";   	  
			?>
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
			<?
			if($dd1[2]  != "")
			{				 
				$comp_address = ''; 
				if($dd1[14] != '')
					$comp_address .= addslashes($dd1[14]);
				if($dd1[15] != '')
					$comp_address .= " ".addslashes($dd1[15]);
				if($dd1[16] != '')
					$comp_address .= " ".addslashes($dd1[16]);
				if($dd1[17] != '')
					$comp_address .= " ".addslashes($dd1[17]);
				$comp_newaddr = dispfdb($comp_address);
				$comp_cname= dispfdb(stripslashes($dd1[13]));
			}
			?>
			<td colspan="5" height="22" onclick='win("<?=$dd1[0]?>","<?=dispfdb($new_str);?>","<?=$dd1[2]?>","<?=$comp_newaddr;?>","<?=$dd1[3]?>","<?=$comp_cname?>")'> 
				
				<?
				echo $dd1[1]; 
				if($dd1[13]!='') 
					echo " ($dd1[13]) "; 
				echo stripslashes($address1); 
				?>
				
				<? echo stripslashes($pass_var); ?>
			</td>
			</tr>

			<tr nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<? $q++; 
		}
	}
	else
	{
		?>
		<tr class="mouseoutcont">
			<td colspan="5" height="22" align="center"> <b>Results not found.</b></td>
		</tr>
		</table>
		<?
	}
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
		for($i=0 ; $i<10;$i++)
		{
			$que1="select CONCAT('',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)) as names,staffoppr_contact.csno, staffoppr_contact.status,staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname,staffoppr_contact.address1,staffoppr_contact.address2,staffoppr_contact.city,staffoppr_contact.state,staffoppr_contact.wphone,staffoppr_contact.ytitle,staffoppr_cinfo.cname as cnames,staffoppr_cinfo.address1,staffoppr_cinfo.address2,staffoppr_cinfo.city,staffoppr_cinfo.state from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno = staffoppr_cinfo.sno where staffoppr_contact.status='ER' and (FIND_IN_SET('".$username."',staffoppr_contact.accessto)>0 or staffoppr_contact.owner = '".$username."' or staffoppr_contact.accessto='ALL') ".$qstr." and ".$likes." LIKE '".$i."%' and staffoppr_contact.crmcontact='Y' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN (".$deptAccesSno_FO.") order by ".$onames;
			$res1=mysql_query($que1,$db);  
			while($dd1=mysql_fetch_row($res1))
			{
				$test = $q;
				$pass_var = "."; 
				$dd1[1] = stripslashes($dd1[1]);
				$dd1[13] = stripslashes($dd1[13]);
				$pass_newvar = $dd1[1]." ".$pass_var;
				$new_str = addslashes($dd1[4])." ".addslashes($dd1[5])." ".addslashes($dd1[6]);
				if($dd1[7] == '')
					$add1 = '';
				else
					$add1 = $dd1[7];
				if($dd1[8] == '')
					$add2 = '';
				else
					$add2 = ",".$dd1[8];	
				if($dd1[9] == '')
					$city = '';
				else
					$city = ",".$dd1[9];
				if($dd1[10] == '')
					$state = '';
				else
					$state = ",".$dd1[10];
				$address1 = " $add1 $add2 $city $state ";

				if($dd1[2]  != "")
				{
					$comp_address = ''; 
					if($dd1[14] != '')
						$comp_address .= addslashes($dd1[14]);
					if($dd1[15] != '')
						$comp_address .= " ".addslashes($dd1[15]);
					if($dd1[16] != '')
						$comp_address .= " ".addslashes($dd1[16]);
					if($dd1[17] != '')
						$comp_address .= " ".addslashes($dd1[17]);
					$comp_newaddr = dispfdb($comp_address);
					$comp_cname= dispfdb(stripslashes($dd1[13]));
				}
				?>						
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
					<td colspan="5" height="22" onclick='win("<?=$dd1[0]?>","<?=dispfdb($new_str);?>","<?=$dd1[2]?>","<?=$comp_newaddr;?>","<?=$dd1[3]?>","<?=$comp_cname?>")'> 
					<?
					echo $dd1[1]; if($dd1[13]!='') echo " ($dd1[13]) "; 
					echo $address1; 
					?> <? echo $pass_var; ?>
					</td>
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
				<td colspan="5" height="22" align="center"> <b>Results not found.</b></td>
			</tr>     
			<?
		} 
		?>	
		</table>
		<?	
	}
	else
	{
		$que1="select CONCAT('',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)) as names,staffoppr_contact.csno, staffoppr_contact.status,staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname,staffoppr_contact.address1,staffoppr_contact.address2,staffoppr_contact.city,staffoppr_contact.state,staffoppr_contact.wphone,staffoppr_contact.ytitle,staffoppr_cinfo.cname as cnames,staffoppr_cinfo.address1,staffoppr_cinfo.address2,staffoppr_cinfo.city,staffoppr_cinfo.state from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno = staffoppr_cinfo.sno where staffoppr_contact.status='ER' and (FIND_IN_SET('".$username."',staffoppr_contact.accessto)>0 or staffoppr_contact.owner = '".$username."' or staffoppr_contact.accessto='ALL') ".$qstr." and ".$likes." LIKE '".$letter."%' and staffoppr_contact.crmcontact='Y' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN (".$deptAccesSno_FO.") order by ".$onames;
		$res1=mysql_query($que1,$db); 
		?>
		<table border="0" cellpadding="2" cellspacing="0" width="100%" >
		<?
		$q=1;
		$num_rows = mysql_num_rows($res1); 
		if($num_rows > 0) 
		{
			while($dd1=mysql_fetch_row($res1))
			{ 
				$pass_var = "."; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$dd1[1] = stripslashes($dd1[1]);
				$dd1[13] = stripslashes($dd1[13]);
				$new_str = addslashes($dd1[4])." ".addslashes($dd1[5])." ".addslashes($dd1[6]);
				if($dd1[7] == '')
					$add1 = '';
				else
					$add1 = $dd1[7];
				if($dd1[8] == '')
					$add2 = '';
				else
					$add2 = ",".$dd1[8];	
				if($dd1[9] == '')
					$city = '';
				else
					$city = ",".$dd1[9];
				if($dd1[10] == '')
					$state = '';
				else
					$state = ",".$dd1[10];
				$address1 = " $add1 $add2 $city $state ";
				?>	
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?
				if($dd1[2]  != "")
				{
					$comp_address = ''; 
					if($dd1[14] != '')
						$comp_address .= addslashes($dd1[14]);
					if($dd1[15] != '')
						$comp_address .= " ".addslashes($dd1[15]);
					if($dd1[16] != '')
						$comp_address .= " ".addslashes($dd1[16]);
					if($dd1[17] != '')
						$comp_address .= " ".addslashes($dd1[17]);

					$comp_newaddr = dispfdb($comp_address);
					$comp_cname= dispfdb($dd1[13]);
				}
				?>
				<td colspan="5" height="22" onclick='win("<?=$dd1[0]?>","<?=dispfdb($new_str);?>","<?=$dd1[2]?>","<?=$comp_newaddr;?>","<?=$dd1[3]?>","<?=$comp_cname?>")>  
					<? echo $dd1[1]; if($dd1[13]!='') echo " ($dd1[13]) "; 
					echo $address1; 
					?> <? echo $pass_var; ?>
				</td>
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