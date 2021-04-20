<?php
	require("global.inc");
	require("dispfunc.php");
	require("waitMsg.inc");	//To display the delay message

	$deptAccessObj = new departmentAccess();
	$deptAccesSno_FO = $deptAccessObj->getDepartmentAccess($username,"'FO'");
	$deptAccesSno_BO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	function getDispName($argFieldName)
	{
		$arrDisplayName  = array("companyname"=>"Company Name","BillingAddress"=>"Billing Address","Parent"=>"Parent","CustomCompanyName" => "Company Name");
		return $arrDisplayName[$argFieldName];
	} 	
	
	
	$appendurl = "fieldname={$fieldname}".((isset($ssearch) && ($ssearch=='empexp' || $ssearch=='custexp')) ? '&amp;ssearch='.$ssearch : '');
	
	
	
	$searchuser = $username;//$cusername;
	
	//$accesscond = "and (staffoppr_cinfo.owner = {$searchuser} OR FIND_IN_SET( {$searchuser}, staffoppr_cinfo.accessto ) >0 OR staffoppr_cinfo.accessto = 'ALL') and crmcompany='Y' ";
	if($fieldname == 'customer'){
		if($letter == "")
		   $condition = "sc.cname LIKE 'a%'";
		elseif($letter == "others")
			$condition = "(ASCII(sc.cname) < '65'  or  (ASCII(sc.cname) > '90' and ASCII(sc.cname) < '97') or 
			ASCII(sc.cname) > '122') and ASCII(sc.cname) != '32' and sc.cname != '' ";
		else
		  $condition = "sc.cname LIKE '".$letter."%'";
		  
		if($searchbox)
		  $condition = " sc.cname LIKE '%".$searchbox."%'";
		   
		$sqlStaff = "select sc.sno, sc.cname,sc.address1,sc.address2,sc.city,sc.state from staffacc_cinfo sc LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=sc.sno) where {$condition} AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") group by sc.sno order by sc.cname";
		$resultset = mysql_query($sqlStaff,$db);
	}else{
		if($letter == "")
		   $condition = "el.name LIKE 'a%'";
		elseif($letter == "others")
			$condition = "(ASCII(el.name) < '65'  or  (ASCII(el.name) > '90' and ASCII(el.name) < '97') or 
			ASCII(el.name) > '122') and ASCII(el.name) != '32' and el.name != '' ";
		else
		  $condition = "el.name LIKE '".$letter."%'";
		  
		if($searchbox)
		  $condition = " el.name LIKE '%".$searchbox."%'";
		   
		$sqlStaff = "select el.sno, el.name,el.address1,el.address2,el.city,el.state from emp_list el LEFT JOIN hrcon_compen hc ON (el.username = hc.username) where hc.dept !='0' AND hc.dept IN (".$deptAccesSno_BO.") AND el.empterminated !='Y' && {$condition} group by el.sno order by el.name";
		$resultset = mysql_query($sqlStaff,$db);
	}
	//echo $sqlStaff;
	$numRows = mysql_num_rows($resultset);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo getDispName($fieldname);?> Search</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/searchwindows.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery-1.11.1.js"></script>
<script type="text/javascript">
 function do_search()
 {
    var searchvalue = document.formsearch.searchbox.value;
    if(searchvalue == "")
	 {
		  alert("Enter any Search keyword");
		  document.formsearch.searchbox.focus();
		  
	 }else
	 {
		
		document.formsearch.submit();
	 }
 }
</script>
<script language=javascript src=scripts/manage_expense_rate.js></script>
<style>
.RowBoarder{
	font-size: 12px;
}
</style>
</head>
<body>
<form name="formsearch" id="formsearch" action=""  onsubmit="return do_search()">
<input type="hidden" id="fieldname" class="fieldname" name="fieldname" value="<?php echo $fieldname; ?>" />

<table width=99% cellpadding=1 cellspacing=1 class="ProfileNewUI" align="center">
   <tr>
	   <td valign=middle align="center" colspan="2"  class="joborderSerchHed">
	   <font> Search for <span id="cust_emp_lbl">
	   <?php if($fieldname =='customer'){ echo "Customer"; }else{ echo "Employee";} ?>
	   </span></font></td>
   </tr>
   <tr>
   
	<td  width=40%>
		<input type="radio" name="sel_emp_cust" value="customer" <?php if($fieldname =='customer'){ ?>checked <?php } ?> onclick="checkEmpCustMode(this);getEmpCustData('a');"><font class="plainText">Customer&nbsp; </font>
		<input type="radio" name="sel_emp_cust" value="employee" <?php if($fieldname =='employee'){ ?>checked <?php } ?> onclick="checkEmpCustMode(this);getEmpCustData('a');"><font class="plainText">Employee&nbsp; </font>
	</td> 
	<td width=80% >
		<input type="text" size=45  maxlength=30 name="searchbox" value="<?=html_tls_entities(stripslashes($searchbox));?>"/><a href="javascript:do_search();" > <i class="fa fa-search fa-lg" name="comp"></i> </a>&nbsp;
		<a href="javascript:getEmpCustData('a');" > <i class="fa fa-reply fa-lg"></i></a>
	</td>
   </tr>
   
<tr>
   <td class="joborderSerchList" colspan="2"> &nbsp;&nbsp;&nbsp;
   <?php 
       $lettersList = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
       foreach($lettersList as $l){ ?>
           
           <a href="companyEmpSearch.php?letter=<?=$l?>&<?= $appendurl?>"  class="alphabetStyle" ><?php  echo ucfirst($l); ?></a>&nbsp;
           
      <?php } ?>
   
      &nbsp;&nbsp;&nbsp;<a href="companyEmpSearch.php?letter=others&<?= $appendurl?>"  class="alphabetStyle">other</a>
    
   </td>   
</tr>
 
 <tr>
	<td colspan="2"><font class="plainText">Click on <span id="cust_emp_lbl2"><?php if($fieldname =='customer'){ echo "Customer"; }else{ echo "Employee";} ?></span> for selection</font></td> 
 </tr>
 <tr>
	<td colspan="2">
	<div class="styleBg">
	<table border="0" cellpadding="0" cellspacing="0" width="95%" align="center">
	<tr ><td  height="3"></td></tr>
	<? $rowloop = 1 ; 
	if($numRows) {
	       while($row = mysql_fetch_assoc($resultset))   {
		     $address = $row['city']." ".$row['state'];
			 
	?>
	    <tr  onmouseover="mouseover('<?=$rowloop?>')" onMouseOut="mouseout(<?=$rowloop?>)" onMouseDown="mouseout('<?=$rowloop?>')" 
		id=rowid<?=$rowloop?> >
	    <?php if($fieldname=='customer'){ ?>
		
		<td  height="22" onclick="javascript : <?= (isset($ssearch) && $ssearch=='custexp') ? 'custexpensetyp(\''.$row['sno'].'\');' : 'displayValue(\''.$row['sno'].'\', \''.$row['cname'].'\');'?>" class="RowBoarder">
		<strong><?= html_tls_specialchars(stripslashes($row['cname'])); ?></strong> <?=  $address ?><?php echo " - ".$row['sno']; ?></td>
		
		<?php }else{ ?>
			
		<td  height="22" onclick="javascript : <?= (isset($ssearch) && $ssearch=='empexp') ? 'empexpensetyp(\''.$row['sno'].'\');' : 'displayValue(\''.$row['sno'].'\', \''.$row['name'].'\');'?>" class="RowBoarder">
		<strong><?= html_tls_specialchars($row['name']); ?></strong> <?=  $address ?><?php echo " - ".$row['sno']; ?></td>
		
			
		<?php } ?>
		</tr>
		<tr nowrap="nowrap">
		<td bgcolor="#ffffff"></td>
		</tr>
	<?
	   ++$rowloop;
	   }//while close
    } //if close
	else
	  echo "<tr class='mouseoutcont'><td height='22' align='center'><strong>Results not found.</strong></td></tr>"
	?>

	</table>
	</div>
	</td>
</tr>
	  
  </table>
  <?php
  	if(isset($ssearch) && $ssearch=='custexp')
	{
		echo '<input type="hidden" name="ssearch" id="ssearch" value="custexp" />';
	}
	if(isset($ssearch) && $ssearch=='empexp')
	{
		echo '<input type="hidden" name="ssearch" id="ssearch" value="empexp" />';
	}
?>
  </form>
  <form name="custexpFrm" id="custexpFrm" method="post" action="custexpensetype.php">
  	<input type="hidden" name="cid" id="cid" value="" />
  </form>
   <form name="empexpFrm" id="empexpFrm" method="post" action="empexpensetype.php">
  	<input type="hidden" name="eid" id="eid" value="" />
  </form>
<script src="/BSOS/scripts/validateBottom.js"></script>
<script src="/BSOS/scripts/searchwindow.js"></script>
<script language="javascript">
function custexpensetyp(id)
{
	document.getElementById('cid').value = id;
	window.resizeTo(700, 620);
	document.forms['custexpFrm'].submit();
}
function empexpensetyp(id)
{
	document.getElementById('eid').value = id;
	window.resizeTo(700, 620);
	document.forms['empexpFrm'].submit();
}
</script>
</body>
</html>   
   
