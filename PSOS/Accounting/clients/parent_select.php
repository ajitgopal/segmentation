<?php
	require("global.inc");
	require("dispfunc.php");
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno_FO = $deptAccessObj->getDepartmentAccess($username,"'FO'");
	$deptAccesSno_BO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	/*
		Modifed Date: April 30, 2009.
		Modified By: Kumar Raju k.
		Purpose: Modified the query to display the companies in searchbox when the candidate is not in emp tables.
		TS Task Id: 4285, (QA) Accounting vendors - consulting vendors - create contacts, now create candidate, the added contacts are lost. Need to finetune the code..
		
		Modifed Date: April 21, 2009.
		Modified By: Sandeep Ganachary.
		Purpose: User can able select Accounting customers record while creating consulting vendor.
		TS Task Id: 4277 (Prakash) Accounting Vendors - Consulting Vendors - 'select existing CRM company' option currently doesn’t bring accounting customers, need to bring accounting customers, which don’t have relation with crm companies. Need search by contact, search by company options in search window.
	*/



	if(isset($_REQUEST['id']))
	{
		$letter = $_REQUEST['id'];
	}
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}

	$getBrowser = new getBrowserInfo;
	$browser = $getBrowser->Name;
	
	
	//this is the function that will take all the snos of a company in its child hierarchy 
	function checkChild($csno)
	{
		global $maildb,$db,$username,$All_Child_Snos;
		
		$Child_Sql="select sno from staffoppr_cinfo where parent='".$csno."' AND  status='ER' AND crmcompany = 'Y' and acc_comp='0' 		AND (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') ";
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
	} //End of function checkChild($csno)

	$All_Child_Snos='';
	if($compSno!='')
		$All_Child_Snos=checkChild($compSno);
	else
	{
		if($Divisions!="")
		{
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
  	
	if($compSno!="")
   		$sno_chk= "sno!='".$compSno."' AND ";
  	else 
   		$sno_chk="";
   
	$Staffacc_Cond = "";
	if($venfrm == 'yes')
	{
		$Staffacc_Cond = "select distinct(venid) from vendorsubcon";
		$fetch_snos    = "SELECT acc_comp FROM staffoppr_cinfo WHERE acc_comp !='0'";
	}	
?>  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<title>Untitled Document</title>

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




function company(str)
 {
   id = "com"+str;
  if(document.getElementById(id))
	{
	document.getElementById(id).className="mouseovercont";
	}
 }
 function company_out(str)
 {
   id = "com"+str;
  if(document.getElementById(id))
	{
	document.getElementById(id).className = 'mouseoutcont';
	}
 }
 

function win(val,val2,val3,val4)
{
 var typeComp="";
 var contSno="";
 if(parent.window.opener.location.href.indexOf('&typecomp') > 0)
 {
 	var addressSplit=parent.window.opener.location.href.split("&typecomp=");
	var contSnoSplit=parent.window.opener.location.href.split("&contSno=");
	typeComp=addressSplit[1];
	contSno=contSnoSplit[1];
 }
 	parent.info_pass(val,val2,val3,val4,typeComp,contSno);
 }
</script>
</head>
<body >
<?php 
	if($search != "")
 	{
		$q=1;
		$qstr="";
		if($venfrm == 'yes' && $rec_type == 'CRM')
		{
		//$que1="select sno,cname,address1,address2,city,state,curl,phone,country,zip,ticker,department,keytech,industry,ctype,fax, csize,nloction,nbyears,nemployee,com_revenue,federalid,siccode,csource,parent,status from staffoppr_cinfo where ".$sno_chk." status='ER' and crmcompany='Y' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') and cname LIKE '%".$search."%' order by cname";
		$que1="SELECT staffoppr_cinfo.sno, staffoppr_cinfo.cname, staffoppr_cinfo.address1, staffoppr_cinfo.address2, staffoppr_cinfo.city, staffoppr_cinfo.state, staffoppr_cinfo.curl, staffoppr_cinfo.phone, staffoppr_cinfo.country, staffoppr_cinfo.zip, staffoppr_cinfo.ticker, staffoppr_cinfo.department, staffoppr_cinfo.keytech, staffoppr_cinfo.industry, staffoppr_cinfo.ctype, staffoppr_cinfo.fax, staffoppr_cinfo.csize, staffoppr_cinfo.nloction, staffoppr_cinfo.nbyears, staffoppr_cinfo.nemployee, staffoppr_cinfo.com_revenue, staffoppr_cinfo.federalid, staffoppr_cinfo.siccode, staffoppr_cinfo.csource, staffoppr_cinfo.parent, staffoppr_cinfo.status, staffoppr_cinfo.acc_comp, staffacc_cinfo.cname, staffacc_cinfo.username FROM staffoppr_cinfo LEFT JOIN staffacc_cinfo ON (staffoppr_cinfo.acc_comp = staffacc_cinfo.sno) WHERE ".$sno_chk." NOT EXISTS (SELECT venid FROM vendorsubcon,emp_list WHERE venid = staffacc_cinfo.username AND vendorsubcon.empid=emp_list.username AND venid!='' ) AND staffoppr_cinfo.cname!='' AND staffoppr_cinfo.status='ER' AND crmcompany='Y' AND (FIND_IN_SET('".$username."',staffoppr_cinfo.accessto) > 0 OR staffoppr_cinfo.owner = '".$username."' OR staffoppr_cinfo.accessto='ALL') AND acc_comp='0' AND staffoppr_cinfo.deptid !='0' AND staffoppr_cinfo.deptid IN(".$deptAccesSno_FO.") AND staffoppr_cinfo.cname LIKE '%".$search."%' ORDER BY staffoppr_cinfo.cname";
		}else if($_GET['vendors_hrm']=='yes')
		{
		$que1="select staffacc_cinfo.sno, staffacc_cinfo.cname,staffacc_cinfo.address1,staffacc_cinfo.address2,staffacc_cinfo.city,staffacc_cinfo.state,staffacc_cinfo.curl,staffacc_cinfo.phone,staffacc_cinfo.country,staffacc_cinfo.zip,staffacc_cinfo.ticker,staffacc_cinfo.department,staffacc_cinfo.keytech,staffacc_cinfo.industry,staffacc_cinfo.ctype,staffacc_cinfo.fax, staffacc_cinfo.csize,staffacc_cinfo.nloction,staffacc_cinfo.nbyears,staffacc_cinfo.nemployee,staffacc_cinfo.com_revenue,staffacc_cinfo.federalid,staffacc_cinfo.siccode,staffacc_cinfo.csource,staffacc_cinfo.parent 
				from staffacc_cinfo
				where staffacc_cinfo.type IN ('CV','BOTH') AND staffacc_cinfo.deptid !='0' AND staffacc_cinfo.deptid IN(".$deptAccesSno_BO.") AND staffacc_cinfo.cname LIKE '%".$search."%' ";
		}
		else
		{
		$que1="select sno,cname,address1,address2,city,state,curl,phone,country,zip,ticker,department,keytech,industry,ctype,fax, csize,nloction,nbyears,nemployee,com_revenue,federalid,siccode,csource,parent,status from staffoppr_cinfo where ".$sno_chk." status='ER' and crmcompany='Y' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') AND deptid !='0' AND deptid IN(".$deptAccesSno_FO.") and cname LIKE '%".$search."%' and acc_comp='0' order by cname";
		}
		if($venfrm != 'yes' || ($venfrm == 'yes' && $rec_type == 'CRM'))
		{
		$res1=mysql_query($que1,$db);
		$num_rows = mysql_num_rows($res1);
		}
		else
			$num_rows = 0;
?>
<?php
if($browser != 'MSIE')
{
?>
<script>
window.top.document.getElementById('delreq').style.display = 'none';
</script>
<?php
}
?>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
<?php  
		if($num_rows > 0) 
 		{
     		while($dd1=mysql_fetch_row($res1))
    		{
				$pass_var = '';
						  
				if($dd1[2] != '')
					$pass_var .= html_tls_specialchars(stripslashes($dd1[2]));
				if($dd1[3] != '')
				  $pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[3]));
				if($dd1[4] != '')
				  $pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[4]));
				if($dd1[5] != '')
				  $pass_var .= " ".html_tls_specialchars(stripslashes($dd1[5]));
			   $pass_newvar = $pass_var;
			   $pass_cname =  html_tls_specialchars(stripslashes($dd1[1]));
						 
				$deduction_var = '';
				$deduction_var .= html_tls_specialchars(stripslashes($dd1[2]));
				$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[3]));
			    $deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[4]));
			    $deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[5]));
			    $deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[9]));		
?>
  <tr></tr>
  <tr onmouseover="company('<?=$q;?>')" onMouseOut="company_out(<?=$q;?>)" onMouseDown="company_out('<?=$q;?>')" id=com<?=$q;?> class="mouseoutcont">
    <td colspan="5" height="22" onclick="win('<?=$dd1[0];?>','<?=$deduction_var;?>','<?=$dd1[25];?>','<?=addslashes($pass_cname);?>')"><b><?=stripslashes($dd1[1]);?></b><?=html_tls_entities(stripslashes($pass_var));?></td>
  </tr>
  <tr nowrap="nowrap">
	<td colspan="5" bgcolor="#ffffff"></td>
  </tr>
<?php 			$q++;
			}//End of while($dd1=mysql_fetch_row($res1))
		}//End of if($num_rows > 0)
		$acc_rows = 0;
		if($venfrm == 'yes' && $rec_type == 'ACC')
		{
			$acc_comp="SELECT staffacc_cinfo.username,staffacc_cinfo.cname,staffacc_cinfo.address1,staffacc_cinfo.address2, staffacc_cinfo.city,staffacc_cinfo.state,staffacc_cinfo.sno FROM staffacc_cinfo, staffacc_list WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffacc_cinfo.username NOT IN (".$Staffacc_Cond.") AND staffacc_cinfo.deptid !='0' AND staffacc_cinfo.deptid IN(".$deptAccesSno_BO.") AND staffacc_cinfo.cname LIKE '%".$search."%' ORDER BY staffacc_cinfo.cname";			
			$acc_res_comp=mysql_query($acc_comp,$db);
			$acc_rows=mysql_num_rows($acc_res_comp);
			$q=1;
			
if($browser != 'MSIE')
{
?>
<script>
window.top.document.getElementById('delreq').style.display = 'none';
</script>
<?php
}

			if($acc_rows > 0) 
			{
				while($dd1=mysql_fetch_row($acc_res_comp))
				{
					$pass_var = '';
					
					if($dd1[2] != '')
						$pass_var .= html_tls_specialchars(stripslashes($dd1[2]));
					if($dd1[3] != '')
					  $pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[3]));
					if($dd1[4] != '')
					  $pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[4]));
					if($dd1[5] != '')
					  $pass_var .= " ".html_tls_specialchars(stripslashes($dd1[5]));
				   
				   $pass_newvar = $pass_var;
				   $pass_cname =  html_tls_specialchars(stripslashes($dd1[1]));
?> 
  <tr></tr>
  <tr onmouseover="company('<?=$q;?>')" onMouseOut="company_out(<?=$q;?>)" onMouseDown="company_out('<?=$q;?>')" id="com<?=$q;?>" class="mouseoutcont">
    <td colspan="5" height="22" onclick="win('<?=$dd1[0];?>','','','<?=$pass_cname;?>')"><b><?=stripslashes($dd1[1]);?></b><?=stripslashes($pass_var);?></td>
  </tr>
  <tr nowrap="nowrap">
	<td colspan="5" bgcolor="#ffffff"></td>
  </tr>
<?php 
					$q++; 
				}
			}
		} //End of if($venfrm == 'yes')
		if($num_rows=='0' && $acc_rows=='0') 
		{ 
?>
  <tr class="mouseoutcont">
    <td colspan="5" height="22" align="center"><b>Search results not found.</b></td>
  </tr>
<?php 
		}
?>
</table>
<?php 
	}
	else
	{
		$qstr="";
		$q=1;
		if($letter == "")
		{
			$letter = 'a';
		}
  		if($letter == "others")
 		{
?>
<table border="0" cellpadding="1" cellspacing="1" width="100%" >
<?php
	 	if($venfrm == 'yes' && $rec_type == 'CRM')
		{
			//$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource,parent,status  from staffoppr_cinfo  where ".$sno_chk." status='ER' and crmcompany='Y' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') and cname not regexp '^[a-z]' order by cname";
	    	$que1="SELECT staffoppr_cinfo.sno, staffoppr_cinfo.cname, staffoppr_cinfo.address1, staffoppr_cinfo.address2, staffoppr_cinfo.city, staffoppr_cinfo.state, staffoppr_cinfo.curl, staffoppr_cinfo.phone, staffoppr_cinfo.country, staffoppr_cinfo.zip, staffoppr_cinfo.ticker, staffoppr_cinfo.department, staffoppr_cinfo.keytech, staffoppr_cinfo.industry, staffoppr_cinfo.ctype, staffoppr_cinfo.fax, staffoppr_cinfo.csize, staffoppr_cinfo.nloction, staffoppr_cinfo.nbyears, staffoppr_cinfo.nemployee, staffoppr_cinfo.com_revenue, staffoppr_cinfo.federalid, staffoppr_cinfo.siccode, staffoppr_cinfo.csource, staffoppr_cinfo.parent, staffoppr_cinfo.status, staffoppr_cinfo.acc_comp, staffacc_cinfo.cname, staffacc_cinfo.username FROM staffoppr_cinfo LEFT JOIN staffacc_cinfo ON (staffoppr_cinfo.acc_comp = staffacc_cinfo.sno) WHERE ".$sno_chk." NOT EXISTS (SELECT venid FROM vendorsubcon,emp_list WHERE venid = staffacc_cinfo.username AND vendorsubcon.empid=emp_list.username AND venid!='' ) AND staffoppr_cinfo.cname!='' AND staffoppr_cinfo.status='ER' AND crmcompany='Y' AND (FIND_IN_SET('".$username."',staffoppr_cinfo.accessto) > 0 OR staffoppr_cinfo.owner = '".$username."' OR staffoppr_cinfo.accessto='ALL') and staffoppr_cinfo.acc_comp='0' AND staffoppr_cinfo.deptid !='0' AND staffoppr_cinfo.deptid IN(".$deptAccesSno_FO.") AND staffoppr_cinfo.cname NOT REGEXP '^[a-z]' AND staffoppr_cinfo.cname != '' ORDER BY staffoppr_cinfo.cname";
		}else if($_GET['vendors_hrm']=='yes')
		{
		 $que1="select staffacc_cinfo.sno, staffacc_cinfo.cname,staffacc_cinfo.address1,staffacc_cinfo.address2,staffacc_cinfo.city,staffacc_cinfo.state,staffacc_cinfo.curl,staffacc_cinfo.phone,staffacc_cinfo.country,staffacc_cinfo.zip,staffacc_cinfo.ticker,staffacc_cinfo.department,staffacc_cinfo.keytech,staffacc_cinfo.industry,staffacc_cinfo.ctype,staffacc_cinfo.fax, staffacc_cinfo.csize,staffacc_cinfo.nloction,staffacc_cinfo.nbyears,staffacc_cinfo.nemployee,staffacc_cinfo.com_revenue,staffacc_cinfo.federalid,staffacc_cinfo.siccode,staffacc_cinfo.csource,staffacc_cinfo.parent 
				from staffacc_cinfo
				where staffacc_cinfo.type IN ('CV','BOTH') AND staffacc_cinfo.deptid !='0' AND staffacc_cinfo.deptid IN(".$deptAccesSno_BO.")
				AND staffacc_cinfo.cname NOT regexp '^[a-z]' ";
		}else
		{
			$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource,parent,status  from staffoppr_cinfo  where ".$sno_chk." status='ER' and crmcompany='Y' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') and cname not regexp '^[a-z]' AND cname != '' and acc_comp='0' and deptid !='0' and deptid IN (".$deptAccesSno_FO.") order by cname";
		}
		if($venfrm != 'yes' || ($venfrm == 'yes' && $rec_type == 'CRM'))  	
		{	
			$res1=mysql_query($que1,$db);  
		   
if($browser != 'MSIE')
{
?>
<script>
window.top.document.getElementById('delreq').style.display = 'none';
</script>
<?php
}

			while($dd1=mysql_fetch_row($res1))
    		{  
				$test = $q;
				$pass_var = '';
				
				if($dd1[2] != '')
					$pass_var .= html_tls_specialchars(stripslashes($dd1[2]));
				if($dd1[3] != '')
					$pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[3]));							
				if($dd1[4] != '')
				  $pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[4]));
				if($dd1[5] != '')
				  $pass_var .= " ".html_tls_specialchars(stripslashes($dd1[5]));
			   	
				$pass_newvar = $pass_var;
			   	$pass_cname =  html_tls_specialchars(stripslashes($dd1[1]));
				
				$deduction_var = '';
				$deduction_var .= html_tls_specialchars(stripslashes($dd1[2]));
				$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[3]));
				$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[4]));
				$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[5]));
				$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[9]));	
?>
  <tr></tr>
  <tr onmouseover="company('<?=$q;?>')" onMouseOut="company_out('<?=$q;?>')" onMouseDown="company_out('<?=$q;?>')" id=com<?=$q;?> class="mouseoutcont">
    <td colspan="5" height="22" onclick="win('<?=$dd1[0];?>','<?=$deduction_var;?>','<?=$dd1[25];?>','<?=$pass_cname;?>')"><?=stripslashes($dd1[1]);?><?=html_tls_entities(stripslashes($pass_var));?></td>
  </tr>
  <tr nowrap="nowrap">
	<td colspan="5" bgcolor="#ffffff"></td>
  </tr>
<?php 
				$q++; 
			} //End of while($dd1=mysql_fetch_row($res1))
		}	
			if($venfrm == 'yes' && $rec_type == 'ACC')
			{
				$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno FROM staffacc_cinfo, staffacc_list WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.username NOT IN (".$Staffacc_Cond.") AND staffacc_cinfo.cname not regexp '^[a-z]' AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffacc_cinfo.cname != '' AND staffacc_cinfo.deptid !='0' AND staffacc_cinfo.deptid IN(".$deptAccesSno_BO.") ORDER BY staffacc_cinfo.cname";
				$acc_res_comp=mysql_query($acc_comp,$db);
				$acc_rows=mysql_num_rows($acc_res_comp);
				
if($browser != 'MSIE')
{
?>
<script>
window.top.document.getElementById('delreq').style.display = 'none';
</script>
<?php
}

				while($dd1=mysql_fetch_row($acc_res_comp))
				{  
					$test = $q;
					$pass_var = '';
					
					if($dd1[2] != '')
						$pass_var .= html_tls_specialchars(stripslashes($dd1[2]));
					if($dd1[3] != '')
						$pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[3]));							
					if($dd1[4] != '')
					  $pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[4]));
					
					$pass_newvar = $pass_var;
					$pass_cname =  html_tls_specialchars(stripslashes($dd1[1]));
					
					$deduction_var = '';
					$deduction_var .= html_tls_specialchars(stripslashes($dd1[2]));
					$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[3]));
					$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[4]));
					$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[5]));
					$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[9]));	
?>  
  <tr></tr>
  <tr onmouseover="company('<?=$q;?>')" onMouseOut="company_out('<?=$q;?>')" onMouseDown="company_out('<?=$q;?>')" id=com<?=$q;?> class="mouseoutcont">
    <td colspan="5" height="22" onclick="win('<?=$dd1[0];?>','<?=$deduction_var;?>','','<?=$pass_cname;?>')"><?=stripslashes($dd1[1]);?><?=html_tls_entities(stripslashes($pass_var));?></td>
  </tr>
  <tr nowrap="nowrap">
	<td colspan="5" bgcolor="#ffffff"></td>
  </tr>
<?php 				
					$q++; 
				} //End of while($dd1=mysql_fetch_row($acc_res_comp))
		} //End of if($venfrm == 'yes')	
 		if($test == 0)
   		{ 
?>
 <tr class="mouseoutcont">
   <td colspan="5" height="22" align="center"><b>Results not found.</b></td>
 </tr>  
   
<?php   
		} 
?>	
</table>
<?php
	}
	else 
	{
 		if($venfrm == 'yes' && $rec_type == 'CRM')
		{
		//$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource, parent, status  from staffoppr_cinfo where ".$sno_chk." status='ER' and crmcompany='Y' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') and cname LIKE '".$letter."%' order by cname";
		$que1="SELECT staffoppr_cinfo.sno, staffoppr_cinfo.cname, staffoppr_cinfo.address1, staffoppr_cinfo.address2, staffoppr_cinfo.city, staffoppr_cinfo.state, staffoppr_cinfo.curl, staffoppr_cinfo.phone, staffoppr_cinfo.country, staffoppr_cinfo.zip, staffoppr_cinfo.ticker, staffoppr_cinfo.department, staffoppr_cinfo.keytech, staffoppr_cinfo.industry, staffoppr_cinfo.ctype, staffoppr_cinfo.fax, staffoppr_cinfo.csize, staffoppr_cinfo.nloction, staffoppr_cinfo.nbyears, staffoppr_cinfo.nemployee, staffoppr_cinfo.com_revenue, staffoppr_cinfo.federalid, staffoppr_cinfo.siccode, staffoppr_cinfo.csource, staffoppr_cinfo.parent, staffoppr_cinfo.status, staffoppr_cinfo.acc_comp, staffacc_cinfo.cname, staffacc_cinfo.username FROM staffoppr_cinfo LEFT JOIN staffacc_cinfo ON (staffoppr_cinfo.acc_comp = staffacc_cinfo.sno) WHERE ".$sno_chk." NOT EXISTS (SELECT venid FROM vendorsubcon,emp_list WHERE venid = staffacc_cinfo.username AND vendorsubcon.empid=emp_list.username AND venid!='' ) AND staffoppr_cinfo.cname!='' AND staffoppr_cinfo.status='ER' AND crmcompany='Y' AND (FIND_IN_SET('".$username."',staffoppr_cinfo.accessto) > 0 OR staffoppr_cinfo.owner = '".$username."' OR staffoppr_cinfo.accessto='ALL') AND staffoppr_cinfo.acc_comp='0' AND staffoppr_cinfo.deptid !='0' AND staffoppr_cinfo.deptid IN(".$deptAccesSno_FO.") AND staffoppr_cinfo.cname LIKE '".$letter."%' ORDER BY staffoppr_cinfo.cname";
		}else if($_GET['vendors_hrm']=='yes')
		{
		 $que1="select staffacc_cinfo.sno, staffacc_cinfo.cname,staffacc_cinfo.address1,staffacc_cinfo.address2,staffacc_cinfo.city,staffacc_cinfo.state,staffacc_cinfo.curl,staffacc_cinfo.phone,staffacc_cinfo.country,staffacc_cinfo.zip,staffacc_cinfo.ticker,staffacc_cinfo.department,staffacc_cinfo.keytech,staffacc_cinfo.industry,staffacc_cinfo.ctype,staffacc_cinfo.fax, staffacc_cinfo.csize,staffacc_cinfo.nloction,staffacc_cinfo.nbyears,staffacc_cinfo.nemployee,staffacc_cinfo.com_revenue,staffacc_cinfo.federalid,staffacc_cinfo.siccode,staffacc_cinfo.csource,staffacc_cinfo.parent 
				from staffacc_cinfo
				where staffacc_cinfo.type IN ('CV','BOTH') AND staffacc_cinfo.deptid !='0' AND staffacc_cinfo.deptid IN(".$deptAccesSno_BO.")
				AND staffacc_cinfo.cname LIKE '".$letter."%' ";
		}else
		{
		$que1="select sno,cname,address1,address2,city,state,curl,phone ,country , zip ,ticker,department ,keytech ,industry, ctype ,fax , csize,nloction ,nbyears , nemployee ,com_revenue ,federalid ,siccode ,csource, parent, status  from staffoppr_cinfo where ".$sno_chk." status='ER' and crmcompany='Y' and (FIND_IN_SET('".$username."',accessto)>0 or owner = '".$username."' or accessto='ALL') and deptid !='0' and deptid IN(".$deptAccesSno_FO.") and cname LIKE '".$letter."%' and acc_comp='0' order by cname";
		}
		if($venfrm != 'yes' || ($venfrm == 'yes' && $rec_type == 'CRM'))
		{
		$res1=mysql_query($que1,$db);
		$num_rows = mysql_num_rows($res1);
		}
		else
			$num_rows = 0;
?>
<?php
if($browser != 'MSIE')
{
?>
<script>
window.top.document.getElementById('delreq').style.display = 'none';
</script>
<?php
}
?>
<table border="0" cellpadding="1" cellspacing="1" width="100%" >
<?php
		$q=1;
 		if($num_rows > 0) 
 		{
 			while($dd1=mysql_fetch_row($res1))
    		{ 
				$pass_var = '';
							
				if($dd1[2] != '')
					$pass_var .= html_tls_specialchars(stripslashes($dd1[2]));
				if($dd1[3] != '')
					$pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[3]));
				if($dd1[4] != '')
				    $pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[4]));
				if($dd1[5] != '')
				    $pass_var .= " ".html_tls_specialchars(stripslashes($dd1[5]));
				
				$pass_newvar = $pass_var;				
				$pass_cname =  html_tls_specialchars(stripslashes($dd1[1]));
				
				$deduction_var = '';
				$deduction_var .= html_tls_specialchars(stripslashes($dd1[2]));
				$deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[3]));
			    $deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[4]));
			    $deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[5]));
			    $deduction_var .= "##".html_tls_specialchars(stripslashes($dd1[9]));				
?>
  <tr><tr>
  <tr onmouseover="company('<?=$q;?>')" onMouseOut="company_out('<?=$q;?>')" onMouseDown="company_out('<?=$q;?>')" id="com<?=$q;?>" class="mouseoutcont">
    <td colspan="5" height="22" onclick="win('<?=$dd1[0];?>','<?=$deduction_var;?>','<?=$dd1[25];?>','<?=$pass_cname;?>')"><?=stripslashes($dd1[1]); ?><?=html_tls_entities(stripslashes($pass_var));?></td>
  </tr>
  <tr nowrap="nowrap">
	<td colspan="5" bgcolor="#ffffff"></td>
  </tr>
<?php 
				$q++; 
			}
		}
		$acc_rows=0;
		if($venfrm == 'yes' && $rec_type == 'ACC')
		{ 
			$acc_comp="SELECT staffacc_cinfo.username, staffacc_cinfo.cname, staffacc_cinfo.address1, staffacc_cinfo.address2, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno FROM staffacc_cinfo, staffacc_list WHERE staffacc_cinfo.username = staffacc_list.username AND staffacc_cinfo.username NOT IN (".$Staffacc_Cond.") AND staffacc_cinfo.cname LIKE '".$letter."%' AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND staffacc_cinfo.deptid !='0' AND staffacc_cinfo.deptid IN(".$deptAccesSno_BO.") ORDER BY staffacc_cinfo.cname";
			$acc_res_comp=mysql_query($acc_comp,$db);
			$acc_rows=mysql_num_rows($acc_res_comp);
		
if($browser != 'MSIE')
{
?>
<script>
window.top.document.getElementById('delreq').style.display = 'none';
</script>
<?php
}

			if($acc_rows > 0) 
			{
				while($dd1=mysql_fetch_row($acc_res_comp))
				{ 
					$pass_var = '';
								
					if($dd1[2] != '')
						$pass_var .= html_tls_specialchars(stripslashes($dd1[2]));
					if($dd1[3] != '')
						$pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[3]));
					if($dd1[4] != '')
						$pass_var .= ", ".html_tls_specialchars(stripslashes($dd1[4]));
					
					$pass_newvar = $pass_var;
					$pass_cname =  html_tls_specialchars(stripslashes($dd1[1]));
?>
  <tr></tr>
  <tr onmouseover="company('<?=$q;?>')" onMouseOut="company_out(<?=$q;?>)" onMouseDown="company_out('<?=$q;?>')" id="com<?=$q;?>" class="mouseoutcont">
	<td colspan="5" height="22" onclick="win('<?=$dd1[0];?>','','','<?=$pass_cname;?>')"><?=stripslashes($dd1[1]);?> <?=html_tls_entities(stripslashes($pass_var));?></td>
  </tr>
  <tr nowrap="nowrap">
	<td colspan="5" bgcolor="#ffffff"></td>
  </tr>
<?php 			
					$q++;
				}
			}
		} //End of if($venfrm == 'yes')	
		if($acc_rows==0 && $num_rows==0)
		{
?>
  <tr class="mouseoutcont">
    <td colspan="5" height="22" align="center"><b>Results not found.</b></td>
  </tr>
<?php 
		}
	}// others
?>
</table>
<?php 
	}
?>
<?php
if($browser == 'MSIE')
{
?>
<script>
window.top.document.getElementById('delreq').style.display = 'none';
</script>
<?php
}
?>
</body>
</html>