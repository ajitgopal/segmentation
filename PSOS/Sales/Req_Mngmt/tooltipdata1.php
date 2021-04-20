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
		$likes = " TRIM(CONCAT_WS('',staffoppr_contact.fname,' ',staffoppr_contact.lname,'(',IF(staffoppr_contact.email='',staffoppr_contact.nickname,staffoppr_contact.email),')')) ";
		$likes1 = " TRIM(CONCAT_WS('',staffacc_contact.fname,' ',staffacc_contact.lname)) ";
	}
?>
<html>
<head>
<title>Search and Select Contact</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function win(val,fcomp,contactname,companyaddr,comp_sno,accsno,compname,conemail)
{   
	if(parent.window.opener.location.href.indexOf("/BSOS/Activities/email/contacts.php")>=0 || parent.window.opener.location.href.indexOf("/include/bulk_targetList.php")>=0 || parent.window.opener.location.href.indexOf("/BSOS/Include/addParticipants.php")>=0)
	{
		var val1 = val.substring(10);
		var toList = parent.window.opener.document.getElementById('groupmembers');
		var len = toList.options.length;

		if(conemail == '')
			conemail = contactname;

		var duplicate = "No";
		for(k=0;k<len;k++)
		{
			if(toList.options[k].text == conemail)
			{
				duplicate = "yes";
				break;
			}
		}	
		if(toList.options[0].text == "No Members Added" || toList.options[0].text == "Select participants and add to list")
		{
			toList.options[0] = null;
		}		
		var opt=parent.window.opener.document.createElement('option');
		if(duplicate != "yes")
		{
			parent.window.opener.document.getElementById('groupmembers').options.add(opt);
			opt.text=conemail;
			opt.value="crmcont"+val1;
		}
	}
	else if(parent.window.opener.location.href.indexOf("/BSOS/Admin/User_Mngmt/userman.php")>=0)
	{
		  var val1 = comp_sno;
          parent.window.opener.document.tree.aa.value="new";
 		  parent.window.opener.document.tree.addr.value ="";
 		  parent.window.opener.document.tree.cont_id.value = val1;
 		  parent.window.opener.document.tree.submit();
	}
	else
	{	
		parent.window.opener.win(val,fcomp,contactname,companyaddr,comp_sno,accsno,compname);
	}
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

function alertPopup(val,fcomp,contactname,comp_sno,accsno,compname)
{
	//alert(val+'-'+fcomp+'-'+contactname+'-'+comp_sno+'-'+accsno+'-'+compname);
	//parent.window.opener.alertPopup(val,fcomp,contactname,comp_sno,accsno,compname);
	/*
	if(parent.window.opener != null && !parent.window.opener.closed){
		//alert(parent.window.opener.location.href+"-----"+parent.window.location.href);
		parent.window.opener.MsgBoard();
		parent.window.opener.alertPopup(val,fcomp,contactname,comp_sno,accsno,compname);
		//parent.window.opener.close();
	}*/
	if(parent.opener.location.href.indexOf("/BSOS/Sales/Req_Mngmt/placement.php") != -1)
	{
		parent.window.opener.alertPopup(val,fcomp,contactname,comp_sno,accsno,compname);
		parent.window.close();
	}
	else
	{
		var form	= parent.opener.document.conreg;
		var jobType = form.jobtype.options[form.jobtype.selectedIndex].text;
		if (jobType == "Internal Temp/Contract" || jobType == "Temp/Contract" || jobType == "Temp/Contract to Direct")
		{	
			parent.window.opener.classToggle('billinginfo','plus');
		}
		parent.window.opener.alertPopup(val,fcomp,contactname,comp_sno,accsno,compname);
		parent.window.close();
		
	}
	
} 
</script>
</head>

<body>

<?php
	$condition="";
	$wherecond= 1;
	$acc_condition="and staffacc_contact.username!=''";
	if($crmcontacts == 'yes')
	{
		$condition="and staffoppr_contact.email!='' AND staffoppr_contact.dontemail != 'Y'";
		$acc_condition="and staffacc_contact.email!=''";
	}

	$list_sno="SELECT distinct(acc_cont)  FROM staffoppr_contact WHERE acc_cont !=0";
	$res_list_sno=mysql_query($list_sno,$db);
	$fetch_snos="";
 	while($fetch_list=mysql_fetch_row($res_list_sno))
 	{
		if($fetch_snos=="")
			$fetch_snos=$fetch_list[0];
		else
			$fetch_snos.=",".$fetch_list[0];
	}

if($search != "")
{
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

	if($crmcontacts != "yes")
	{
		if($prefself == "yes")
			$wherecond = "staffacc_contact.sno NOT  IN ( SELECT staffacc_contactacc.con_id FROM staffacc_contactacc) and staffacc_contact.acccontact = 'Y' and staffacc_contact.username!='' and".$likes1." order by ".$onames1;
		else
			$wherecond = "staffacc_contact.sno NOT IN (".$fetch_snos.") and acccontact='Y' ".$acc_condition." and ".$likes1." order by ".$onames1;
	}

	$q=1;
	$num_rows =0;
	if($prefself!="yes")
	{
	    $que1="select CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ', staffoppr_contact.fname, staffoppr_contact.mname, staffoppr_contact.lname)) as names,
	    CONCAT_WS(',',staffoppr_cinfo.address1,staffoppr_cinfo.city,staffoppr_cinfo.state) ,staffoppr_contact.csno,staffoppr_cinfo.cname as cnames,IF(staffoppr_contact.nickname = '',staffoppr_contact.email, CONCAT(staffoppr_contact.nickname,'(',staffoppr_contact.email,')') ) from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno=staffoppr_cinfo.sno where staffoppr_contact.status='ER' and (FIND_IN_SET('".$username."',staffoppr_contact.accessto)>0 or staffoppr_contact.owner = '".$username."' or staffoppr_contact.accessto='ALL') ".$condition." and ( ".$like_chk." ) and staffoppr_contact.crmcontact='Y' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN(".$deptAccesSno_FO.") order by ".$onames;
		$res1=mysql_query($que1,$db);
		$num_rows = mysql_num_rows($res1);
	}

	$acc_rows=0;

	if($crmcontacts != 'yes')
	{
		$acc_cus="SELECT staffacc_contact.username,TRIM(concat_ws(' ',staffacc_contact.fname,staffacc_contact.mname, staffacc_contact.lname)) as names1,CONCAT_WS(',',staffacc_cinfo.address1,staffacc_cinfo.city,staffacc_cinfo.state),staffacc_contact.sno,staffacc_cinfo.cname as cnames1,staffacc_contact.email,staffacc_cinfo.address1,staffacc_cinfo.city,staffacc_cinfo.state FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username=staffacc_cinfo.username WHERE staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffacc_contact.deptid !='0' AND staffacc_contact.deptid IN(".$deptAccesSno_BO.") AND ".$wherecond;
		$acc_cus_res=mysql_query($acc_cus,$db);
		$acc_rows=mysql_num_rows($acc_cus_res);
	}
	?>
	<table border="0" cellpadding="1" cellspacing="1" width="100%" >
	<? 
	if($num_rows > 0) 
	{
		while($dd1=mysql_fetch_row($res1))
		{ 
			$pass_var = $dd1[2]; 
			$pass_newvar = $dd1[1]." ".$pass_var;
			settype($dd1[1], "string");

			$con_name=str_replace('"','|Akkendbquote|', $dd1[1]);
			$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
			$contname=str_replace("'",'|Akkensiquote|',$con_name);
			$addrval=str_replace("'",'|Akkensiquote|',$addr_val);	
			$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
			$CompName=str_replace("'",'|Akkensiquote|',$CompName);	
			$dd1[4] = dispfdb($dd1[4]);
			$canemail = $dd1[5];
			?>
			<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
			<?php 
			if($fcomp=='refcontact' || $fcomp=='contact')
			{
				?>
				<td colspan="5" height="22" onclick="alertPopup('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=$dd1[3]?>','','<?=html_tls_specialchars(addslashes($CompName));?>')"> 
				<?php
			} 		
			else
			{
				?>
				<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=html_tls_specialchars(addslashes($addrval));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','','<?=html_tls_specialchars(addslashes($CompName));?>','<?=html_tls_specialchars(addslashes($canemail));?>')"> 
				<?php
			}
			?>
			<? echo $dd1[1]; if($dd1[3]!='0' && $dd1[3]!='' ) echo " (".str_replace("\\","",$dd1[4]).") ";   echo $dd1[2]; ?> <? //echo $pass_var;?></td>
			</tr>
			<tr nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<?
			$q++;
		}
	}

	if($acc_rows>0)
	{
		while($dd1=mysql_fetch_row($acc_cus_res))
		{ 
			$pass_var = $dd1[2]; 
			$pass_newvar = $dd1[1]." ".$pass_var;
			settype($dd1[1], "string");

			$con_name=str_replace('"','|Akkendbquote|', $dd1[1]);
			$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
			$contname=str_replace("'",'|Akkensiquote|',$con_name);
			$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
			$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
			$CompName=str_replace("'",'|Akkensiquote|',$CompName);	
			$dd1[4] = dispfdb($dd1[4]);
			$canemail = $dd1[5];
			?>
			<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
			<?php 
			if($fcomp=='refcontact' || $fcomp=='contact')
			{
				?>
				<td colspan="5" height="22" onclick="alertPopup('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=$dd1[5]?>','<?=$dd1[3]?>','<?=html_tls_specialchars(addslashes($CompName));?>')"> 
				<?php
			} 
			else
			{
				?>
				<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=html_tls_specialchars(addslashes($addrval));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($dd1[2]));?>','<?=html_tls_specialchars(addslashes(CompName));?>','<?=html_tls_specialchars(addslashes($canemail));?>')"> 
				<?php
			}
			?>
			<? echo $dd1[1]; if($dd1[4]!='') echo " (".str_replace("\\","",$dd1[4]).") "; echo addrValue($dd1[7],$dd1[8],$dd1[9]); ?> <? //echo $pass_var;?></td>
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
			if($prefself != "yes")
			{
				$que1="select CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)),
				CONCAT_WS(' ',staffoppr_cinfo.address1,staffoppr_cinfo.city,staffoppr_cinfo.state) ,  staffoppr_contact.csno,staffoppr_cinfo.cname, IF(staffoppr_contact.nickname = '',staffoppr_contact.email, CONCAT(staffoppr_contact.nickname,'(',staffoppr_contact.email,')') ) from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno=staffoppr_cinfo.sno where staffoppr_contact.status='ER' and staffoppr_contact.deptid !='0' and staffoppr_contact.deptid IN(".$deptAccesSno_FO.") and (FIND_IN_SET('".$username."',staffoppr_contact.accessto)>0 or staffoppr_contact.owner = '".$username."' or staffoppr_contact.accessto='ALL') and TRIM(CONCAT_WS('',staffoppr_contact.fname,' ',staffoppr_contact.lname,'(',IF(staffoppr_contact.email='',staffoppr_contact.nickname,staffoppr_contact.email),')')) ".$condition." LIKE '".$i."%' and staffoppr_contact.crmcontact='Y'";
				$res1=mysql_query($que1,$db);
			}

			if($crmcontacts != "yes")
			{
				if($prefself == "yes")
					$wherecond = "staffacc_contact.sno NOT  IN ( SELECT staffacc_contactacc.con_id FROM staffacc_contactacc) and staffacc_contact.acccontact = 'Y' and staffacc_contact.username!='' and  ".$likes1." LIKE '".$i."%'  order by ".$onames1;
				else
					$wherecond = "staffacc_contact.sno NOT IN (".$fetch_snos.") and acccontact='Y' ".$acc_condition." and ".$likes1." LIKE '".$i."%'  order by ".$onames1;
			}	
	
			if($crmcontacts != 'yes')
				$acc_cus="SELECT staffacc_contact.username,concat_ws(' ',staffacc_contact.fname,staffacc_contact.mname, staffacc_contact.lname) as names1,CONCAT_WS(' ',staffacc_cinfo.address1,staffacc_cinfo.city,staffacc_cinfo.state),staffacc_contact.sno,staffacc_cinfo.cname,staffacc_cinfo.sno as cnames1,staffacc_contact.email,staffacc_cinfo.address1,staffacc_cinfo.city,staffacc_cinfo.state FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username=staffacc_cinfo.username WHERE staffacc_cinfo.type IN ('CUST', 'BOTH') and staffacc_contact.deptid !='0' and staffacc_contact.deptid IN(".$deptAccesSno_BO.") AND  ".$wherecond;

			$acc_cus_res=mysql_query($acc_cus,$db);	
			$acc_rows=mysql_num_rows($acc_cus_res);

			while($dd1=mysql_fetch_row($res1))
			{  
				$test = $q;
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				settype($dd1[1], "string");

				$con_name=str_replace('"','|Akkendbquote|', $dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
				$CompName=str_replace("'",'|Akkensiquote|',$CompName);
				$dd1[4] = dispfdb($dd1[4]);
				$conemail = $dd1[5];
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php 
				if($fcomp=='refcontact' || $fcomp=='contact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=$dd1[3]?>','','<?=html_tls_specialchars(addslashes($CompName));?>')"> 
					<?php
				} 
				else
				{
					?>
					<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=html_tls_specialchars(addslashes($addrval));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($CompName));?>','<?=html_tls_specialchars(addslashes($conemail));?>')"> 
					<?php
				}
				?>
				<? echo $dd1[1]; if($dd1[3]!='' && $dd1[3]!='0') echo " (".str_replace("\\","",$dd1[4]).") "; echo $dd1[2];?> <? //echo $pass_var; ?> </td>
				</tr>

				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++;
			}

			while($dd1=mysql_fetch_row($acc_cus_res))
			{  
				$test = $q;
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				settype($dd1[1], "string");

				$con_name=str_replace('"','|Akkendbquote|', $dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
		        $CompName=str_replace("'",'|Akkensiquote|',$CompName);	
				$dd1[4] = dispfdb($dd1[4]);
				$conemail = $dd1[5];
				?>  
				<tr> <input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>						
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont"> 
				<?php 
				if($fcomp=='refcontact' || $fcomp=='contact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=$dd1[5]?>','<?=$dd1[3]?>','<?=html_tls_specialchars(addslashes($CompName));?>')"> 
					<?php
				} 
				else
				{
					?>
					<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=html_tls_specialchars(addslashes($addrval));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($dd1[2]));?>','<?=html_tls_specialchars(addslashes(CompName));?>','<?=html_tls_specialchars(addslashes(conemail));?>')"> 
					<?php
				}
				?>
				<?php echo $dd1[1]; if($dd1[4]!='') echo " (".str_replace("\\","",$dd1[4]).") "; echo addrValue($dd1[7],$dd1[8],$dd1[9]); ?> <? //echo $pass_var;?></td>
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
		if($crmcontacts != "yes")
		{
			if($prefself == "yes")
				$wherecond = "staffacc_contact.sno NOT  IN( SELECT staffacc_contactacc.con_id FROM staffacc_contactacc) and staffacc_contact.acccontact = 'Y' and staffacc_contact.username!='' and ".$likes1." LIKE '".$letter."%'  order by ".$onames1;
			else
				$wherecond = "staffacc_contact.sno NOT IN (".$fetch_snos.") and acccontact='Y' ".$acc_condition." and ".$likes1." LIKE '".$letter."%'  order by ".$onames1;
		}

		$num_rows = 0;
		if($prefself!="yes")
		{
			$que1="select CONCAT('staffoppr-',staffoppr_contact.sno),TRIM(CONCAT_WS(' ',staffoppr_contact.fname,staffoppr_contact.mname,staffoppr_contact.lname)) as names,
			CONCAT_WS(' ',staffoppr_cinfo.address1,staffoppr_cinfo.city,staffoppr_cinfo.state) ,staffoppr_contact.csno,staffoppr_cinfo.cname as cnames,IF(staffoppr_contact.nickname = '',staffoppr_contact.email, CONCAT(staffoppr_contact.nickname,'(',staffoppr_contact.email,')') ) from staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno=staffoppr_cinfo.sno where staffoppr_contact.status='ER' and (FIND_IN_SET('".$username."',staffoppr_contact.accessto)>0 or staffoppr_contact.owner = '".$username."' or staffoppr_contact.accessto='ALL') and staffoppr_contact.deptid !='0' and and staffoppr_contact.deptid IN (".$deptAccesSno_FO.") and ".$likes." LIKE '".$letter."%' and staffoppr_contact.crmcontact='Y' ".$condition." order by ".$onames;
			$res1=mysql_query($que1,$db);
			$num_rows = mysql_num_rows($res1);
		}

		if($crmcontacts != 'yes')
		{
			$acc_cus="SELECT staffacc_contact.username,concat_ws(' ',staffacc_contact.fname,staffacc_contact.mname, staffacc_contact.lname) as names1,CONCAT_WS('',staffacc_cinfo.address1,staffacc_cinfo.city,staffacc_cinfo.state),staffacc_contact.sno,staffacc_cinfo.cname,staffacc_cinfo.sno as cnames1, staffacc_contact.email,staffacc_cinfo.address1 ,staffacc_cinfo.city,staffacc_cinfo.state FROM staffacc_contact LEFT JOIN staffacc_cinfo ON staffacc_contact.username=staffacc_cinfo.username WHERE staffacc_cinfo.type IN ('CUST', 'BOTH') and staffacc_contact.deptid !='0' and staffacc_contact.deptid IN(".$deptAccesSno_BO.") AND  ".$wherecond;
			$acc_cus_res=mysql_query($acc_cus,$db);
			$acc_rows=mysql_num_rows($acc_cus_res);
		}
		?>
		<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?
		$q=1; 
		if($num_rows > 0) 
		{
			while($dd1=mysql_fetch_row($res1))
			{
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				settype($dd1[1], "string");
				$con_name=str_replace('"','|Akkendbquote|', $dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
				$CompName=str_replace("'",'|Akkensiquote|',$CompName);	
				$dd1[4] = dispfdb($dd1[4]);
				$conemail = $dd1[5];
				?>
				<tr> <input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /><tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php 
				if($fcomp=='refcontact' || $fcomp=='contact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=$dd1[3]?>','','<?=html_tls_specialchars(addslashes($CompName));?>')"> 
					<?php
				} 
				else
				{
					?>
					<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=html_tls_specialchars(addslashes($addrval));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($CompName));?>','<?=html_tls_specialchars(addslashes($conemail));?>')"> 
					<?php
				}
				?>
				<? echo $dd1[1]; if($dd1[3]!='' &&  $dd1[3]!='0') echo " (".str_replace("\\","",$dd1[4]).") "; echo $dd1[2]; ?>  <? //echo $pass_var; ?> </td>
				</tr>	
				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++; 
			}
		} 
		if($acc_rows>0)
		{
			while($dd1=mysql_fetch_row($acc_cus_res))
			{ 
				$pass_var = $dd1[2]; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				settype($dd1[1], "string");

				$con_name=str_replace('"','|Akkendbquote|', $dd1[1]);
				$addr_val=str_replace('"','|Akkendbquote|',$dd1[2]);
				$contname=str_replace("'",'|Akkensiquote|',$con_name);
				$addrval=str_replace("'",'|Akkensiquote|',$addr_val);
				$CompName=str_replace('"','|Akkendbquote|',$dd1[4]);
			    $CompName=str_replace("'",'|Akkensiquote|',$CompName);	
				$dd1[4] = dispfdb($dd1[4]);
				$conemail = $dd1[6];
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php 
				if($fcomp=='refcontact' || $fcomp=='contact')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=$dd1[5]?>','<?=$dd1[3]?>','<?=html_tls_specialchars(addslashes($CompName));?>')"> 
					<?php
				}
				else
				{
					?>
					<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($contname));?>','<?=html_tls_specialchars(addslashes($addrval));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($dd1[3]));?>','<?=html_tls_specialchars(addslashes($CompName));?>','<?=html_tls_specialchars(addslashes($conemail));?>')"> 
					<?php
				}
				?>
				<? echo $dd1[1]; if($dd1[4]!='') echo " (".str_replace("\\","",$dd1[4]).") "; echo addrValue($dd1[7],$dd1[8],$dd1[9]);?> <? //echo $pass_var;?></td>
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

function addrValue($row1,$row2,$row3)
{
	$comp_addr="";
	if($row1!='' && $row2!='')
		$comp_addr=$row1.", ".$row2;
	else if($row1!='') 
		$comp_addr=$row1;	
	else if($row2!='')
		$comp_addr=$row2;		
	if($row3!='')
	{
		if($comp_addr!='')
			$comp_addr.=", ".$row3;
		else
			$comp_addr=$row3;			
	}	
	return($comp_addr);
}
?>
</body>
</html>