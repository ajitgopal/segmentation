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
		$onames1 = 'cnames1';
		$likes = " staffoppr_cinfo.cname ";
		$likes1 = " staffacc_cinfo.cname ";
	}
	else
	{
		$onames = 'names';
		$onames1 = 'names1';
		$likes = " TRIM(CONCAT_WS(' ',staffoppr_contact.fname,' ',staffoppr_contact.lname,'(',IF(staffoppr_contact.email='',staffoppr_contact.nickname,staffoppr_contact.email),')')) ";
		$likes1 = " TRIM(CONCAT_WS('',staffacc_contact.fname,' ',staffacc_contact.lname)) ";
	}
?>
<html>
<head>
<title>Search and Select Contact</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
<!--
//Disable right mouse click Script

var message="Function Disabled!";

///////////////////////////////////
function clickIE4(){
if (event.button==2){
alert(message);
return false;
}
}

function clickNS4(e){
if (document.layers||document.getElementById&&!document.all){
if (e.which==2 || e.which==3){
alert(message);
return false;
}
}
}

if (document.layers){
document.captureEvents(Event.MOUSEDOWN);
document.onmousedown=clickNS4;
}
else if (document.all&&!document.getElementById){
document.onmousedown=clickIE4;
}
//document.oncontextmenu=new Function("alert(message);return false")
document.oncontextmenu=new Function("return false")
// --> 
function win(val,fcomp,contactname,accsno,comp_sno,comp_name)
{
	parent.window.opener.win(val,fcomp,contactname,accsno,comp_sno,comp_name);
	parent.close();	
}

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

function alertPopup(val,fcomp,contactname,accsno,comp_sno,compname)
{
	var form	= parent.opener.document.conreg;
	var jobType 	= form.jotype.options[form.jotype.selectedIndex].text;
	if (jobType == "Internal Temp/Contract" || jobType == "Temp/Contract" || jobType == "Temp/Contract to Direct")
	{
		parent.window.opener.classToggle('billinginfo','plus');
	}
	parent.window.opener.alertPopup(val,fcomp,contactname,accsno,comp_sno,compname);
	parent.window.close();
} 
</script>
</head>
<body>
<?php 
if($search != "")
{
	$q=1;
	if($by=='bycompany')
	{
		$onames = 'cnames';
		$onames1 = 'cnames1';
		$like_chk= " staffoppr_cinfo.cname LIKE  '".$search."%'";
		$likes1 = " staffacc_cinfo.cname LIKE  '".$search."%'";	
	}
	else
	{
		$onames = 'names';
		$onames1 = 'names1';	
		$like_chk="";
		if(count(explode(" ",trim($search)))>2)
		{
			$like_chk= " CONCAT_WS(' ', staffoppr_contact.fname, IF(staffoppr_contact.mname ='',NULL,staffoppr_contact.mname), staffoppr_contact.lname) LIKE  '".$search."%'";
			$likes1 = " CONCAT_WS(' ', staffacc_contact.fname, IF(staffacc_contact.mname ='',NULL,staffacc_contact.mname), staffacc_contact.lname) LIKE  '".$search."%'";
		}
		else
		{
			$like_chk= " CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.lname) LIKE  '".trim($search)."%'";
			$likes1 = " CONCAT_WS(' ',staffacc_contact.fname,staffacc_contact.lname) LIKE  '".trim($search)."%'";
		}	
	}

	$que1="select CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)) as names,CONCAT_WS(' ',staffoppr_cinfo.address1,staffoppr_cinfo.address2,staffoppr_cinfo.city,staffoppr_cinfo.state,staffoppr_cinfo.zip) ,staffoppr_contact.csno,staffoppr_cinfo.cname as cnames from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno=staffoppr_cinfo.sno and staffoppr_cinfo.status='ER' where staffoppr_contact.acc_cont='0' and ".$like_chk." and staffoppr_contact.crmcontact='Y' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN (".$deptAccesSno_FO.") order by ".$onames;
	$res1=mysql_query($que1,$db);

	$acc_cus="SELECT staffacc_contact.username, TRIM(concat_ws(' ', staffacc_contact.fname, staffacc_contact.mname, staffacc_contact.lname)) as names1,staffacc_contact.sno,staffacc_cinfo.cname as cnames1,staffacc_cinfo.sno, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 3)." FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username=staffacc_cinfo.username LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username WHERE ".$likes1." AND staffacc_cinfo.type IN ('CUST','BOTH') AND staffacc_contact.acccontact='Y' AND staffacc_list.status='ACTIVE' and staffacc_contact.username!='' and staffacc_contact.deptid !='0' and staffacc_contact.deptid IN (".$deptAccesSno_BO.") order by '".$onames1."'";	
	$acc_cus_res=mysql_query($acc_cus,$db);				
	$acc_rows=mysql_num_rows($acc_cus_res);
	?>
	<table border="0" cellpadding="1" cellspacing="1" width="100%" >
	<?php
	if($acc_rows>0)
	{
		while($dd1=mysql_fetch_row($acc_cus_res))
		{ 
			$pass_var = $dd1[2]; 
			$pass_newvar = $dd1[1]." ".$pass_var;
			$con_name=str_replace('"','|Akkendbquote|',$dd1[1]);
			$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
			$contname=str_replace("'",'|Akkensiquote|',$con_name);
			$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
			$CompName=str_replace('"','|Akkendbquote|',$dd1[3]);
			$CompName=str_replace("'",'|Akkensiquote|',$CompName);
			$dd1[5] = dispfdb(stripslashes($dd1[5]));
			?>
			<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
			<?php
			if($fcomp=='reportcontact' || $fcomp=='refcontact' || $fcomp=='billcontact')
			{
				?>
				<td colspan="5" height="22" onclick="alertPopup('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','<?=addslashes($dd1[2])?>','<?php echo addslashes($dd1[4]);?>','<?php echo stripslashes($CompName);?>')"> 
				<?php
			}
			else
			{
				?>
				<td colspan="5" height="22" onclick="win('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','<?=addslashes($dd1[2])?>','<?=addslashes($dd1[4])?>','<?php echo stripslashes($CompName);?>')"> 
				<?php
				}
			?>
			<? echo stripslashes($dd1[1]); if($dd1[5]!='') echo "(".stripslashes($dd1[5]).") "; ?> <? //echo $pass_var;?></td>
			</tr>

			<tr nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<?
			$q++; 
		}
	} 

	$num_rows = mysql_num_rows($res1); 
	if($num_rows > 0) 
	{
		while($dd1=mysql_fetch_row($res1))
		{ 
			$pass_var = $dd1[2]; 
			$pass_newvar = $dd1[1]." ".$pass_var;
			$con_name=str_replace('"','|Akkendbquote|',$dd1[1]);
			$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
			$contname=str_replace("'",'|Akkensiquote|',$con_name);
			$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
			$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
			$CompName=str_replace("'",'|Akkensiquote|',$CompName);
			$dd1[4] = dispfdb(stripslashes($dd1[4]));
			?>
			<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
			<?php
			if($fcomp=='reportcontact' || $fcomp=='refcontact' || $fcomp=='billcontact')
			{
				?>
				<td colspan="5" height="22" onclick="alertPopup('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','','<?=addslashes($dd1[3])?>','<?php echo stripslashes($CompName);?>')"> 
				<?php
			}
			else
			{
				?>
				<td colspan="5" height="22" onclick="win('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','','<?=addslashes($dd1[3])?>','<?php echo stripslashes($CompName);?>')"> 
				<?php
			}
			?>
			<? echo stripslashes($dd1[1]); if($dd1[4]!='') echo  "(".stripslashes($dd1[4]).") "; 
			echo $dd1[2]; ?> <? //echo $pass_var;?></td>
			</tr>

			<tr nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<?
			$q++;
		}
	}

	if($acc_rows=='0' && $num_rows=='0') 
	{
		?>
		<tr class="mouseoutcont"><td colspan="5" height="22" align="center"><b>Search results not found.</b></td></tr>
		<?
	}
	?>
	</table>
	<?
}
else
{
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
			$que1="select CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)),CONCAT_WS(' ',staffoppr_cinfo.address1,staffoppr_cinfo.address2,staffoppr_cinfo.city,staffoppr_cinfo.state,staffoppr_cinfo.zip) ,  staffoppr_contact.csno,staffoppr_cinfo.cname as cnames from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno=staffoppr_cinfo.sno and staffoppr_cinfo.status='ER' where staffoppr_contact.acc_cont='0' and TRIM(CONCAT_WS('',staffoppr_contact.fname,' ',staffoppr_contact.lname,'(',IF(staffoppr_contact.email='',staffoppr_contact.nickname,staffoppr_contact.email),')')) LIKE '".$i."%' and staffoppr_contact.crmcontact='Y' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN(".$deptAccesSno_FO.") ";
			$res1=mysql_query($que1,$db);

			$acc_cus="SELECT staffacc_contact.username, TRIM(concat_ws(' ', staffacc_contact.fname, staffacc_contact.mname, staffacc_contact.lname), staffacc_contact.sno,staffacc_cinfo.cname as cnames1,staffacc_cinfo.sno, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 3)." FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username=staffacc_cinfo.username LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username WHERE staffacc_cinfo.type IN ('CUST','BOTH') AND concat_ws( '', staffacc_contact.fname, '', staffacc_contact.lname ) LIKE '".$i."%' AND staffacc_contact.acccontact='Y' AND staffacc_list.status='ACTIVE' and staffacc_contact.username!='' and staffacc_contact.deptid !='0' and staffacc_contact.deptid IN(".$deptAccesSno_FO.") "; 
			$acc_cus_res=mysql_query($acc_cus,$db);	
			$acc_rows=mysql_num_rows($acc_cus_res);

			while($dd1=mysql_fetch_row($acc_cus_res))
			{  
				$test = $q;
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$con_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[3]);
				$CompName=str_replace("'",'|Akkensiquote|',$CompName);
				$dd1[5] = dispfdb(stripslashes($dd1[5]));
				?>  
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php
				if($fcomp=='reportcontact' || $fcomp=='refcontact' || $fcomp=='billcontact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','<?=addslashes($dd1[2])?>','<?php echo addslashes($dd1[4]);?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				else
				{
					?>
					<td colspan="5" height="22" onclick="win('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','<?=addslashes($dd1[2])?>','<?=addslashes($dd1[4])?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				?>
				<? echo stripslashes($dd1[1]); ?> <? //echo $pass_var; ?> </td>
				</tr>
				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++;
			}

			while($dd1=mysql_fetch_row($res1))
			{  
				$test = $q;
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$con_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
				$CompName=str_replace("'",'|Akkensiquote|',$CompName);
				$dd1[4] = dispfdb(stripslashes($dd1[4]));
				?>  
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php
				if($fcomp=='reportcontact' || $fcomp=='refcontact' || $fcomp=='billcontact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','','<?=addslashes($dd1[3])?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				else
				{
					?>
					<td colspan="5" height="22" onclick="win('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','','<?=addslashes($dd1[3])?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				?>
				<? echo stripslashes($dd1[1]); if($dd1[4]!='') echo " (".stripslashes($dd1[4]).") "; echo $dd1[2]; ?> </td>
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
			<tr class="mouseoutcont"><td colspan="5" height="22" align="center"><b>Results not found.</b></td>
			</tr>     
			<?
		} 
		?>
		</table>
		<?	
	}
	else
	{
		$que1="select CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)) as names,CONCAT_WS(' ',staffoppr_cinfo.address1,staffoppr_cinfo.address2,staffoppr_cinfo.city,staffoppr_cinfo.state,staffoppr_cinfo.zip) ,staffoppr_contact.csno,staffoppr_cinfo.cname as cnames from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno=staffoppr_cinfo.sno and staffoppr_cinfo.status='ER' where staffoppr_contact.acc_cont='0' and ".$likes." LIKE '".$letter."%' and staffoppr_contact.crmcontact='Y' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN (".$deptAccesSno_FO.") order by ".$onames;
		$res1=mysql_query($que1,$db);

		$acc_cus="SELECT staffacc_contact.username, TRIM(concat_ws(' ', staffacc_contact.fname, staffacc_contact.mname, staffacc_contact.lname)) as names1,staffacc_contact.sno,staffacc_cinfo.cname as cnames1,staffacc_cinfo.sno, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 3)." FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username=staffacc_cinfo.username LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username  WHERE ".$likes1." LIKE '".$letter."%' AND staffacc_cinfo.type IN ('CUST','BOTH') AND staffacc_contact.acccontact='Y' AND staffacc_list.status = 'ACTIVE' and staffacc_contact.username!='' and staffacc_contact.deptid !='0' and staffacc_contact.deptid IN (".$deptAccesSno_BO.") order by ".$onames1;
		$acc_cus_res=mysql_query($acc_cus,$db);	
		$acc_rows=mysql_num_rows($acc_cus_res);
		?>
		<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?
		$q=1;
		$num_rows = mysql_num_rows($res1); 
		if($acc_rows>0)
		{
			while($dd1=mysql_fetch_row($acc_cus_res))
			{ 
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$con_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[3]);
				$CompName=str_replace("'",'|Akkensiquote|',$CompName);
				$dd1[5] = dispfdb(stripslashes($dd1[5]));
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php
				if($fcomp=='reportcontact' || $fcomp=='refcontact' || $fcomp=='billcontact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','<?=addslashes($dd1[2])?>','<?php echo addslashes($dd1[4]);?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				else
				{
					?> 
					<td colspan="5" height="22" onclick="win('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','<?=addslashes($dd1[2])?>','<?php echo addslashes($dd1[4]);?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				?>
				<? echo stripslashes($dd1[1]); if($dd1[5]!='') echo " (".stripslashes($dd1[5]).") "; ?> <? //echo $pass_var;?></td>
				</tr>
				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++; 
			}
		} 

		if($num_rows > 0) 
		{
			while($dd1=mysql_fetch_row($res1))
			{ 
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$con_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
				$CompName=str_replace("'",'|Akkensiquote|',$CompName);
				$dd1[4] = dispfdb(stripslashes($dd1[4]));
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /><tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php
				if($fcomp=='reportcontact' || $fcomp=='refcontact' || $fcomp=='billcontact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','','<?=addslashes($dd1[3])?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				else
				{
					?>
					<td colspan="5" height="22" onclick="win('<?=addslashes($dd1[0])?>','<?=addslashes($fcomp)?>','<?=addslashes($contname)?>','','<?=addslashes($dd1[3])?>','<?php echo stripslashes($CompName);?>')"> 
					<?php
				}
				?>
				<? echo stripslashes($dd1[1]); if($dd1[4]!='') echo " (".stripslashes($dd1[4]).") "; echo $dd1[2]; ?>  </td>
				</tr>
				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++; 
			}
		} 

		if($acc_rows=='0' && $num_rows=='0')
		{
			?>
			<tr class="mouseoutcont"><td colspan="5" height="22" align="center"><b>Results not found.</b></td></tr>
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