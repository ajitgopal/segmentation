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
function win(val,fcomp)
{
	parent.info_pass(val,fcomp);
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
</script>
</head>

<body>

<?php 
function checkChild($csno)
{
	global $maildb,$db,$username,$All_Child_Snos;

	$Child_Sql="select sno from staffoppr_cinfo where parent='".$csno."' AND  status='ER' AND crmcompany = 'Y' AND status='ER' AND  (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ";
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
	$Child_Condition=" sno not in (".$All_Child_Snos.") and";
	
if($search != "")
{
	$q=1;
	$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource from staffoppr_cinfo where ".$Child_Condition." acc_comp='0' and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") and cname LIKE '".$search."%' and crmcompany='Y' and status='ER' order by cname";
	$res1=mysql_query($que1,$db);

	$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 3)." FROM staffacc_list,staffacc_cinfo LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=staffacc_cinfo.sno) WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.cname LIKE '%".$search."%' AND staffacc_list.status='ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") GROUP BY staffacc_cinfo.username";
	$acc_res_comp=mysql_query($acc_comp,$db);
	$acc_rows=mysql_num_rows($acc_res_comp);
	?>
	<table border="0" cellpadding="1" cellspacing="1" width="100%" >
	<?php
	if($acc_rows > 0) 
	{
		while($dd1=mysql_fetch_row($acc_res_comp))
		{
			$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4].".";
			$pass_newvar = $dd1[7]." ".$pass_var;
			$compaddr= settype($dd1[2], "string")." ".$dd1[3]." ".$dd1[4]." ".$dd1[5];
			$comp_name=str_replace('"','|Akkendbquote|',$dd1[7]);
			$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
			$compname=str_replace("'",'|Akkensiquote|',$comp_name);
			$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

			$sel_count="SELECT count(*) FROM staffacc_contact WHERE staffacc_contact.username='".$dd1[0]."' and staffacc_contact.acccontact='Y'";
			$res_count=mysql_query($sel_count,$db);
			$fetch_count=mysql_fetch_row($res_count);
			?>
			<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[7]); ?>" /></tr>	
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
			<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>')">
			<b><? echo stripslashes($dd1[7]); ?></b> <? echo stripslashes($pass_var); ?></td>
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
			$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
			$pass_newvar = $dd1[1]." ".$pass_var;
			$compaddr= settype($dd1[2], "string")." ".$dd1[3]." ".$dd1[4]." ".$dd1[5]." ".$dd1[7]." ".$dd1[8]." ".$dd1[9];
			$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
			$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
			$compname=str_replace("'",'|Akkensiquote|',$comp_name);
			$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

			$sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'  and acc_cont='0'";
			$res_count=mysql_query($sel_count,$db);
			$fetch_count=mysql_fetch_row($res_count);
			?>
			<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
			<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
			<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>')">
			<b><? echo stripslashes($dd1[1]); ?></b> <? echo stripslashes($pass_var); ?></td>
			</tr>
			<tr nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<?
			$q++;
		}
	}

	if($num_rows=='0' && $acc_rows=='0')
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
			$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction,nbyears,nemployee,com_revenue,federalid,siccode,csource from staffoppr_cinfo where ".$Child_Condition." cname LIKE '".$i."%' and crmcompany='Y' and acc_comp='0' and status='ER' and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") order by cname";
			$res1=mysql_query($que1,$db); 

			$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 3)." FROM staffacc_list,staffacc_cinfo LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=staffacc_cinfo.sno) WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.cname LIKE '".$i."%' AND staffacc_list.status='ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") GROUP BY staffacc_cinfo.username";
			$acc_res_comp=mysql_query($acc_comp,$db);
			$acc_rows=mysql_num_rows($acc_res_comp); 
			while($dd1=mysql_fetch_row($acc_res_comp))
			{  
				$test = $q;
				$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
				$pass_newvar = $dd1[7]." ".$pass_var;
				$compaddr= settype($dd1[2], "string")." ".$dd1[3]." ".$dd1[4]." ".$dd1[5];	
				$comp_name=str_replace('"','|Akkendbquote|',$dd1[7]);
				$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
				$compname=str_replace("'",'|Akkensiquote|',$comp_name);
				$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);
				$sel_count="SELECT count(*) FROM staffacc_contact WHERE staffacc_contact.username='".$dd1[0]."' and staffacc_contact.acccontact='Y'";
				$res_count=mysql_query($sel_count,$db);
				$fetch_count=mysql_fetch_row($res_count);
				?>  
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[7]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>')">
				<? echo stripslashes($dd1[7]); ?> <? echo stripslashes($pass_var); ?> </td>
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
				$pass_var = $dd1[2].$dd1[3].$dd1[4]."."; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$compaddr= settype($dd1[2], "string")." ".$dd1[3]." ".$dd1[4]." ".$dd1[5]." ".$dd1[7]." ".$dd1[8]." ".$dd1[9];
				$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
				$compname=str_replace("'",'|Akkensiquote|',$comp_name);
				$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);
				$sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'  and acc_cont='0'";
				$res_count=mysql_query($sel_count,$db);
				$fetch_count=mysql_fetch_row($res_count);
				?>  
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>')">
				<? echo stripslashes($dd1[1]); ?> <? echo stripslashes($pass_var); ?> </td>
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
		$q=1;
		$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource  from staffoppr_cinfo where ".$Child_Condition."   cname LIKE '".$letter."%' and acc_comp='0' and crmcompany='Y' and status='ER' and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") order by cname";
		$res1=mysql_query($que1,$db);

		$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state, staffacc_cinfo.sno, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 3)." FROM staffacc_list,staffacc_cinfo LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=staffacc_cinfo.sno) WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.cname LIKE '".$letter."%' AND staffacc_list.status='ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") GROUP BY staffacc_cinfo.username";
		$acc_res_comp=mysql_query($acc_comp,$db);
		$acc_rows=mysql_num_rows($acc_res_comp);
		?>
		<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?php
		if($acc_rows > 0) 
		{
			while($dd1=mysql_fetch_row($acc_res_comp))
			{ 
				$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
				$pass_newvar = $dd1[7]." ".$pass_var;
				$compaddr= settype($dd1[2], "string")." ".$dd1[3]." ".$dd1[4]." ".$dd1[5];
				$comp_name=str_replace('"','|Akkendbquote|',$dd1[7]);
				$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
				$compname=str_replace("'",'|Akkensiquote|',$comp_name);
				$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

				$sel_count="SELECT count(*) FROM staffacc_contact WHERE staffacc_contact.username='".$dd1[0]."' and staffacc_contact.acccontact='Y'";
				$res_count=mysql_query($sel_count,$db);
				$fetch_count=mysql_fetch_row($res_count);
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[7]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>')">
				<b><? echo stripslashes($dd1[7]); ?></b> <? echo stripslashes($pass_var); ?></td>
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
				$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
				$pass_newvar = $dd1[1]." ".$pass_var;
				$compaddr= settype($dd1[2], "string")." ".$dd1[3]." ".$dd1[4]." ".$dd1[5]." ".$dd1[7]." ".$dd1[8]." ".$dd1[9];
				$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
				$compname=str_replace("'",'|Akkensiquote|',$comp_name);
				$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);
				$sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."' and acc_cont='0'";
				$res_count=mysql_query($sel_count,$db);
				$fetch_count=mysql_fetch_row($res_count);
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /><tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<td colspan="5" height="22" onclick="win('<?=$dd1[0]?>','<?=$fcomp?>')">
				<b><? echo stripslashes($dd1[1]); ?></b> <? echo stripslashes($pass_var); ?> </td>
				</tr>
				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++;
			}
		}

		if($acc_rows==0 && $num_rows==0) 
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