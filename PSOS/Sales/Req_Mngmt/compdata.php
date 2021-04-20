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
<script type="text/javascript" src="/BSOS/scripts/multiplerates.js" ></script>
<script language=javascript src="/BSOS/scripts/jQuery.js"></script>
<script type="text/javascript">

document.oncontextmenu=new Function("return false")

function new_win(val,sname,csno,cname,status,compname,fcomp)
{
	parent.new_info_pass(val,sname,csno,cname,status,compname,fcomp);
} 

function win1(val,fcomp,compname,compaddr)
{
	if(fcomp == 'upload')
	{
		getattachDetails(val);
	}
	else
	{
		parent.window.opener.win1(val,fcomp,compname,compaddr);
		parent.close();	
	}	
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

function alertPopup1(val,fcomp,compname,compaddr,contcount)
{	
	//when select company from placements
	if(parent.opener.location.href.indexOf("/BSOS/Sales/Req_Mngmt/placement.php") != -1)
	{
		//companyRateTypes(val, "company");
		parent.window.opener.alertPopup1(val,fcomp,compname,compaddr,contcount);
		parent.window.close();
	}
	else
	{
		var form	= parent.opener.document.conreg;
		var jobType = form.jobtype.options[form.jobtype.selectedIndex].text;
	
		// calling this function only when job type is temp/contract| Internal Temp/Contract | Temp/Contract to Direct rajani/rajesh
		if (form.jobtype.value == "") {
			parent.window.opener.document.getElementById('jobtype-data').className="seljob-type1";
			parent.window.opener.document.getElementById("crm-joborder-formback-msg").innerHTML="Please select a Job Type";
			parent.window.opener.conreg.jobtype.focus();
		}
		else
		{
			if (jobType == "Internal Temp/Contract" || jobType == "Temp/Contract" || jobType == "Temp/Contract to Direct")
			{
				parent.window.opener.document.getElementById('company').value=val;
				parent.window.opener.classToggle('billinginfo','plus');			
			}
			parent.window.close();	
			parent.window.opener.alertPopup1(val,fcomp,compname,compaddr,contcount);
			//parent.window.close();
		}
	}
	
}	

function getattachDetails(str1)
{
	var v_width  = 510;
	var v_heigth = 300;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	var afolder = parent.document.f1.attach_folder.value;
	top.parent.remattachfiles_cand=window.open("../../../include/attachmentscreen.php?cand_id="+str1+"&candjotype=companies&attach_folder="+afolder,"attachment","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");	
	top.parent.remattachfiles_cand.focus();
}

function cand_docs_attach(str,prodoc,resumename,candid)
{
	var toList   = parent.window.opener.document.attachment.cbFiles;
	var cbf = parent.window.opener.document.getElementById('cbFiles');
	var mainselbox = parent.window.opener.document.getElementById('cbFiles');
	var mainselen= mainselbox.length;
	var chk_compdocs = parent.window.opener.document.attachment.companydocs.value;
	var chk_compprof=parent.window.opener.document.attachment.companyprofile.value
	var len = toList.length;
	var str_split=str.split("|^");
	var spl_len = str_split.length;

	if(str_split[0] != '')
	{
		for(i=0;i<spl_len;i++)
		{
		    var canddetail=str_split[i].split("|-");
			for(j=0;j<mainselen;j++)
			{
				if(mainselbox.options[j].text == canddetail[1] && mainselbox.options[j].value == canddetail[0])
					var chksel="sel";
			}

			if(chksel!="sel")
			{
				var opt=parent.window.opener.document.createElement('option');
				parent.window.opener.document.getElementById('cbFiles').options.add(opt);
				opt.text=canddetail[1];
				opt.value=canddetail[0];
				opt.title=canddetail[1];
			  
				if(chk_compdocs=="")
					chk_compdocs = canddetail[0]+"|-"+canddetail[1];
				else
					chk_compdocs += "|compdocs^"+canddetail[0]+"|-"+canddetail[1];
				parent.window.opener.document.attachment.companydocs.value=chk_compdocs;
			}
		}
	}

	if(prodoc)
	{
		var prodoc1 = prodoc.substring(0,3);
		if(prodoc1 == 'yes')
		{
			proname1 = prodoc.split("|");
			proname = proname1[1]+".html";
			var mainselbox = parent.window.opener.document.getElementById('cbFiles');
			var mainselen= mainselbox.length;
			for(i=0;i<mainselen;i++)
			{
				if(mainselbox.options[i].title == proname && mainselbox.options[i].value == candid)
					var chksel="prof";
			} 

			if(chksel != "prof")
			{
				var opt=parent.window.opener.document.createElement('option');
				parent.window.opener.document.getElementById('cbFiles').options.add(opt);
				opt.text=proname;
				opt.value=candid;
				opt.title=proname;

				if(chk_compprof=="")
					chk_compprof=candid+"|-"+proname;
				else
					chk_compprof += "|compprofile^"+candid+"|-"+proname;
				parent.window.opener.document.attachment.companyprofile.value=chk_compprof;
			} 
		}
	}
	parent.close();
}
</script>
</head>

<body>

<?php 
	function checkChild($csno)
	{
		global $maildb,$db,$username,$All_Child_Snos;

		$Child_Sql="select sno from staffoppr_cinfo where parent='".$csno."' AND  status='ER' AND crmcompany = 'Y' AND  (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ";
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
		return $All_Child_Snos;
	}

	$All_Child_Snos='';

	if($compSno!='')
	{
		if($fcomp=='jobcompany' || $fcomp=='company')
			$All_Child_Snos='';
		else
			$All_Child_Snos=checkChild($compSno);
	}
	else
	{
		if($Comp_Divisions!="" || $Jobloc_Comp_Divisions!="")
		{
			if($fcomp=="jobloc_companyHierarchy")
				$Divisions=$Jobloc_Comp_Divisions;
			else
				$Divisions=$Comp_Divisions;

			$exp_divs=explode(",",$Divisions);
			for($i=0;$i<count($exp_divs);$i++)
			{
				if($All_Child_Snos=="")
					$All_Child_Snos=checkChild($exp_divs[$i]);
				else
					$All_Child_Snos.=",".checkChild($exp_divs[$i]);
			}

			if($All_Child_Snos=="")
				$All_Child_Snos=$Divisions;
			else
				$All_Child_Snos.=",".$Divisions;
		}
	}

	//get all the child snos and have a condition correspondingly
	if($All_Child_Snos!='') 
		$Child_Condition="  and sno not in (".$All_Child_Snos.")";

	//check that the present and parent should not be the same and this is needed only when comning  from Heirarchy pane..
	$Parent_condition='';
	if($fcomp=='companyHierarchy' || $fcomp=='jobloc_companyHierarchy') 
		$Parent_condition="sno!='$compSno' AND ";
	  
	$list_sno="SELECT acc_comp FROM staffoppr_cinfo WHERE acc_comp !='0'";
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
		$q=1;
		$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource from staffoppr_cinfo where ".$Parent_condition." status='ER' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL')".$Child_Condition." and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") and cname LIKE '".$search."%' and crmcompany='Y' order by cname";
		$res1=mysql_query($que1,$db);
		if($fcomp == 'upload')
		{
			$acc_rows = 0;
		}
		else
		{
			$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno FROM staffacc_cinfo, staffacc_list
			LEFT JOIN Client_Accounts ca ON (ca.typeid=staffacc_cinfo.sno)  WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.sno NOT IN (".$fetch_snos.") AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.")  AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffacc_cinfo.cname LIKE '".$search."%' ";
			$acc_res_comp=mysql_query($acc_comp,$db);
			$acc_rows=mysql_num_rows($acc_res_comp);
		}
		?>
		<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?
		$num_rows = mysql_num_rows($res1); 
		if($num_rows > 0) 
		{
			while($dd1=mysql_fetch_row($res1))
			{ 
				$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
				$pass_newvar = $dd1[1]." ".$pass_var;

				$compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];
				$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
				$compname=str_replace("'",'|Akkensiquote|',$comp_name);
				$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

				$sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'";
				$res_count=mysql_query($sel_count,$db);
				$fetch_count=mysql_fetch_row($res_count);
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">

				<?php
				if($fcomp=='jobcompany' || $fcomp=='company')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>','<?php echo  $fetch_count[0];?>')">
					<?php
				}
				else
				{
					?>
					<td colspan="5" height="22" onclick="win1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>')">
					<?
				}
				?>
				<? echo str_replace("\\","",$dd1[1]); ?> <? echo str_replace("\\","",$pass_var); ?></td>
				</tr>

				<tr nowrap="nowrap">
					<td colspan="5" bgcolor="#ffffff"></td>
				</tr>
				<?
				$q++;
			}
		}

		$q=1; 
		if($acc_rows > 0) 
		{
			while($dd1=mysql_fetch_row($acc_res_comp))
			{ 
				$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
				$pass_newvar = $dd1[1]." ".$pass_var;

				$compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];
				$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
				$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
				$compname=str_replace("'",'|Akkensiquote|',$comp_name);
				$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

				$sel_count="SELECT count(*) FROM staffacc_contact WHERE staffacc_contact.username='".$dd1[0]."'";
				$res_count=mysql_query($sel_count,$db);
				$fetch_count=mysql_fetch_row($res_count);
				?>
				<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
				<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
				<?php
				if($fcomp=='jobcompany' || $fcomp=='company')
				{
					?>
					<td colspan="5" height="22" onclick="alertPopup1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>','<?php echo  $fetch_count[0];?>')">
					<?php
				}
				else
				{
					?>
					<td colspan="5" height="22" onclick="win1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>')">
					<?
				}
				?>
				<? echo str_replace("\\","",$dd1[1]); ?> <? echo str_replace("\\","",$pass_var); ?></td>
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
 
		if($letter == "other")
		{   
			?>
			<table border="0" cellpadding="1" cellspacing="1" width="100%" >
			<?
			for($i=0 ; $i<10;$i++)
			{
				$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource  from staffoppr_cinfo  where ".$Parent_condition." status='ER' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ".$Child_Condition." and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") and cname LIKE '".$i."%' and crmcompany='Y' order by cname";
				$res1=mysql_query($que1,$db); 
		  
				if($fcomp == 'upload')
				{
					$acc_rows = 0;
				}
				else
				{
					$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno FROM staffacc_cinfo, staffacc_list LEFT JOIN Client_Accounts ca ON (ca.typeid=staffacc_cinfo.sno)  WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.sno NOT IN (".$fetch_snos.") AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") AND staffacc_cinfo.cname LIKE '".$i."%' ";
					$acc_res_comp=mysql_query($acc_comp,$db);
					$acc_rows=mysql_num_rows($acc_res_comp); 
				}

				while($dd1=mysql_fetch_row($res1))
				{
					$test = $q;
					$pass_var = $dd1[2].$dd1[3].$dd1[4]."."; 
					$pass_newvar = $dd1[1]." ".$pass_var;

					$compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];
					$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
					$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
					$compname=str_replace("'",'|Akkensiquote|',$comp_name);
					$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

					$sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'";
					$res_count=mysql_query($sel_count,$db);
					$fetch_count=mysql_fetch_row($res_count);
					?>
					<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>						
					<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
 
					<?php
					if($fcomp=='jobcompany' || $fcomp=='company')
					{
						?>
						<td colspan="5" height="22" onclick="alertPopup1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>','<?php echo  $fetch_count[0];?>')">
						<?php
					}
					else
					{
						?>
						<td colspan="5" height="22" onclick="win1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>')">
						<?
					}
					?>
					<? echo str_replace("\\","",$dd1[1]); ?> <? echo str_replace("\\","",$pass_var); ?> </td>
					</tr>

					<tr nowrap="nowrap"><td colspan="5" bgcolor="#ffffff"></td></tr>
					<?
					$q++;
				}

				if($acc_rows>0)    
				{
					while($dd1=mysql_fetch_row($acc_res_comp))
					{
						$test = $q;
						$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
						$pass_newvar = $dd1[1]." ".$pass_var;

						$compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];
						$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
						$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
						$compname=str_replace("'",'|Akkensiquote|',$comp_name);
						$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

						$sel_count="SELECT count(*) FROM staffacc_contact WHERE staffacc_contact.username='".$dd1[0]."'";
						$res_count=mysql_query($sel_count,$db);
						$fetch_count=mysql_fetch_row($res_count);
						?>
						<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>						
						<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
 
						<?php
						if($fcomp=='jobcompany' || $fcomp=='company')
						{
							?>
							<td colspan="5" height="22" onclick="alertPopup1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>','<?php echo  $fetch_count[0];?>')">
							<?php
						}
						else
						{
							?>
							<td colspan="5" height="22" onclick="win1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>')">
							<?
						}
						?>
						<? echo str_replace("\\","",$dd1[1]); ?> <? echo str_replace("\\","",$pass_var); ?> </td>
						</tr>

						<tr nowrap="nowrap"><td colspan="5" bgcolor="#ffffff"></td></tr>
						<?
						$q++;
					}
				}
			}
			?>

			<?
			if($test == 0)
			{
				?>
				<tr class="mouseoutcont"><td colspan="5" height="22" align="center"><b>Results not found.</b></td></tr>   
				<?
			} 
			?>
			</table>
			<?	
		}
		else
		{
			
			$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource  from staffoppr_cinfo where ".$Parent_condition." status='ER' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL')".$Child_Condition." and staffoppr_cinfo.deptid !='0' and staffoppr_cinfo.deptid IN (".$deptAccesSno_FO.") and cname LIKE '".$letter."%' and crmcompany='Y' order by cname";
			$res1=mysql_query($que1,$db);
			if($fcomp == 'upload') 	 
			{
				$acc_rows = 0;
			}
			else
			{	 
				$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno FROM staffacc_cinfo, staffacc_list LEFT JOIN Client_Accounts ca ON (ca.typeid=staffacc_cinfo.sno) WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.sno NOT IN (".$fetch_snos.") AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") AND staffacc_cinfo.cname LIKE '".$letter."%'";
				$acc_res_comp=mysql_query($acc_comp,$db);
				$acc_rows=mysql_num_rows($acc_res_comp);
			}
			?>
			<table border="0" cellpadding="1" cellspacing="1" width="100%" >
			<?
			$q=1;
			$num_rows = mysql_num_rows($res1); 
			if($num_rows > 0) 
			{
				while($dd1=mysql_fetch_row($res1))
				{ 
					$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
					$pass_newvar = $dd1[1]." ".$pass_var;

					$compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];   
					$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
					$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
					$compname=str_replace("'",'|Akkensiquote|',$comp_name);
					$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

					$sel_count="SELECT count(*) FROM staffoppr_contact WHERE staffoppr_contact.csno='".$dd1[0]."'";
					$res_count=mysql_query($sel_count,$db);
					$fetch_count=mysql_fetch_row($res_count);
					?>
					<tr> <input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /><tr>
					<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out('<?=$q?>')" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
					<?php
					
					if($fcomp=='jobcompany' || $fcomp=='company')
					{
						?>
						<td colspan="5" height="22" onclick="alertPopup1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>','<?php echo  $fetch_count[0];?>')">
						<?php
					}
					else
					{
						?>
						<td colspan="5" height="22" onclick="win1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>')">
						<?
					}
					?>
					<? echo str_replace("\\","",$dd1[1]); ?> <? echo str_replace("\\","",$pass_var); ?> </td>
					</tr>
					<tr nowrap="nowrap"><td colspan="5" bgcolor="#ffffff"></td></tr>
					<?
					$q++; 
				}
			}

			if($acc_rows > 0) 
			{
				while($dd1=mysql_fetch_row($acc_res_comp))
				{
					$pass_var = $dd1[2]." ".$dd1[3]." ".$dd1[4]."."; 
					$pass_newvar = $dd1[1]." ".$pass_var;

					$compaddr= settype($dd1[2], "string")."^^CompSplit^^".$dd1[3]."^^CompSplit^^".$dd1[4]."^^CompSplit^^".$dd1[5]."^^CompSplit^^".$dd1[8]."^^CompSplit^^".$dd1[9]."^^CompSplit^^".$dd1[7];
					$comp_name=str_replace('"','|Akkendbquote|',$dd1[1]);
					$addr_comp=str_replace('"','|Akkendbquote|',$compaddr);
					$compname=str_replace("'",'|Akkensiquote|',$comp_name);
					$addrcomp=str_replace("'",'|Akkensiquote|',$addr_comp);

					$sel_count="SELECT count(*) FROM staffacc_contact WHERE staffacc_contact.username='".$dd1[0]."'";
					$res_count=mysql_query($sel_count,$db);
					$fetch_count=mysql_fetch_row($res_count);
					?>
					<tr><input type="hidden" name=comp<?=$dd1[0]?> value="<? echo dispfdb($dd1[1]); ?>" /></tr>
					<tr onmouseover="company('<?=$q?>')" onMouseOut="company_out(<?=$q?>)" onMouseDown="company_out('<?=$q?>')" id=com<?=$q?> class="mouseoutcont">
					<?php
					if($fcomp=='jobcompany' || $fcomp=='company')
					{
						?>
						<td colspan="5" height="22" onclick="alertPopup1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>','<?php echo  $fetch_count[0];?>')">
						<?php
					}
					else
					{
						?>
						<td colspan="5" height="22" onclick="win1('<?=$dd1[0]?>','<?=$fcomp?>','<?=html_tls_specialchars(addslashes($compname));?>','<?=html_tls_specialchars(addslashes($addrcomp));?>')">
						<?
					}
					?>
					<? echo str_replace("\\","",$dd1[1]); ?> <? echo str_replace("\\","",$pass_var); ?></td>
					</tr>

					<tr nowrap="nowrap"><td colspan="5" bgcolor="#ffffff"></td></tr>
					<?
					$q++;
				}
			}

			if($acc_rows==0 && $num_rows==0)
			{
				?>
				<tr class="mouseoutcont"><td colspan="5" height="22" align="center"> <b>Results not found.</b></td></tr>
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