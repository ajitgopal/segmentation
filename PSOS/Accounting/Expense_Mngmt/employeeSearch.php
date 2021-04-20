<?php 
	require("global.inc");
	require("dispfunc.php");
	require("waitMsg.inc");	//To display the delay message
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno_FO = $deptAccessObj->getDepartmentAccess($username,"'FO'");
	$deptAccesSno_BO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	function getDispName($argFieldName)
	{
		$arrDisplayName  = array("employeename"=>"Employee Name","BillingAddress"=>"Billing Address","Parent"=>"Parent","CustomCompanyName" => "Employee Name");
		return $arrDisplayName[$argFieldName];
	} 	
	$appendurl = "fieldname={$fieldname}".((isset($ssearch) && $ssearch=='empexp') ? '&amp;ssearch=empexp' : '');
	
	$searchuser = $username;//$cusername;
	
	if($letter == "")
	   $condition = "el.name LIKE 'a%'";
	elseif($letter == "others")
		$condition = "(ASCII(el.name) < '65'  or  (ASCII(el.name) > '90' and ASCII(el.name) < '97') or 
		ASCII(el.name) > '122') and ASCII(el.name) != '32' and el.name != '' ";
	else
	  $condition = "el.name LIKE '".$letter."%'";
	  
	if($searchbox)
	  $condition = " el.name LIKE '%".$searchbox."%'";
	   
	$sqlStaff = "select el.sno, el.name from emp_list el LEFT JOIN hrcon_compen hc ON (el.username = hc.username) where hc.dept !='0' AND hc.dept IN (".$deptAccesSno_BO.") and {$condition} {$accesscond} group by el.sno order by el.name";
	
	$resultset = mysql_query($sqlStaff,$db);
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
<script type="text/javascript">
 function do_search()
 {
    var searchvalue = document.formsearch.searchbox.value;
    if(searchvalue == "")
	{
		alert("Enter any Search keyword");
		document.formsearch.searchbox.focus();
	}  
	else
	{
		document.formsearch.submit();
	}
 }
</script>
</head>
<body>
<form name="formsearch" action=""  onsubmit="return do_search()">
<input type="hidden" name="fieldname" value="<?= $fieldname ?>" />

<table width=99% cellpadding=1 cellspacing=1 class="ProfileNewUI" align="center">
   <tr>
	   <td valign=middle align="center" colspan="2"  class="joborderSerchHed">
	   <font> Search for Employee(s)</font></td>
   </tr>
   <tr>
	<td  width=20%><font class="plainText">Employee&nbsp;Name&nbsp;: </font></td> 
	<td width=80% ><input type="text" size=45  maxlength=30 name="searchbox" value="<?=html_tls_entities(stripslashes($searchbox));?>"/><a href="javascript:do_search()" > <i class="fa fa-search" name="comp"></i> </a>&nbsp;
	<a href="employeeSearch.php?letter=a&<?= $appendurl?>"> <i class="fa fa-reply"></i></a></td>
   </tr>
   
<tr>
   <td class="joborderSerchList" colspan="2"> &nbsp;&nbsp;&nbsp;
   <a href="employeeSearch.php?letter=a&<?= $appendurl?>" class="alphabetStyle">A</a>&nbsp;
   <a href="employeeSearch.php?letter=b&<?= $appendurl?>" class="alphabetStyle">B</a>&nbsp;
   <a href="employeeSearch.php?letter=c&<?= $appendurl?>" class="alphabetStyle">C</a>&nbsp;
   <a href="employeeSearch.php?letter=d&<?= $appendurl?>" class="alphabetStyle">D</a>&nbsp;
   <a href="employeeSearch.php?letter=e&<?= $appendurl?>" class="alphabetStyle">E</a>&nbsp;
   <a href="employeeSearch.php?letter=f&<?= $appendurl?>" class="alphabetStyle">F</a>&nbsp;
   <a href="employeeSearch.php?letter=g&<?= $appendurl?>" class="alphabetStyle">G</a>&nbsp;
   <a href="employeeSearch.php?letter=h&<?= $appendurl?>" class="alphabetStyle">H</a>&nbsp;
   <a href="employeeSearch.php?letter=i&<?= $appendurl?>" class="alphabetStyle">I</a>&nbsp;
   <a href="employeeSearch.php?letter=j&<?= $appendurl?>" class="alphabetStyle">J</a>&nbsp;
   <a href="employeeSearch.php?letter=k&<?= $appendurl?>" class="alphabetStyle">K</a>&nbsp;
   <a href="employeeSearch.php?letter=m&<?= $appendurl?>" class="alphabetStyle">M</a>&nbsp;
   <a href="employeeSearch.php?letter=n&<?= $appendurl?>" class="alphabetStyle">N</a>&nbsp;
   <a href="employeeSearch.php?letter=o&<?= $appendurl?>" class="alphabetStyle">O</a>&nbsp;
   <a href="employeeSearch.php?letter=p&<?= $appendurl?>" class="alphabetStyle">P</a>&nbsp;
   <a href="employeeSearch.php?letter=q&<?= $appendurl?>" class="alphabetStyle">Q</a>&nbsp;
   <a href="employeeSearch.php?letter=r&<?= $appendurl?>" class="alphabetStyle">R</a>&nbsp;
   <a href="employeeSearch.php?letter=s&<?= $appendurl?>" class="alphabetStyle">S</a>&nbsp;
   <a href="employeeSearch.php?letter=t&<?= $appendurl?>" class="alphabetStyle">T</a>&nbsp;
   <a href="employeeSearch.php?letter=u&<?= $appendurl?>" class="alphabetStyle">U</a>&nbsp;
   <a href="employeeSearch.php?letter=v&<?= $appendurl?>" class="alphabetStyle">V</a>&nbsp;
   <a href="employeeSearch.php?letter=w&<?= $appendurl?>" class="alphabetStyle">W</a>&nbsp;
   <a href="employeeSearch.php?letter=x&<?= $appendurl?>" class="alphabetStyle">X</a>&nbsp;
   <a href="employeeSearch.php?letter=y&<?= $appendurl?>" class="alphabetStyle">Y</a>&nbsp;
   <a href="employeeSearch.php?letter=z&<?= $appendurl?>" class="alphabetStyle">Z</a>&nbsp;&nbsp;&nbsp;
   <a href="employeeSearch.php?letter=others&<?= $appendurl?>" class="alphabetStyle">other</a>
   </td>   
</tr>
 
 <tr>
	<td colspan="2"><font class="plainText">Click on Employee for selection</font></td> 
 </tr>
 <tr>
	<td colspan="2"><div class="styleBg">
	<table border="0" cellpadding="0" cellspacing="0" width="95%" align="center">
	<tr class="mouseoutcont"><td  height="3"></td></tr>
	<?php
		$rowloop = 1 ; 
		if($numRows) {
	       while($row = mysql_fetch_assoc($resultset))   {			 
	?>
		<tr  onmouseover="mouseover('<?=$rowloop?>')" onMouseOut="mouseout(<?=$rowloop?>)" onMouseDown="mouseout('<?=$rowloop?>')" 
		id=rowid<?=$rowloop?> class="mouseoutcont">
		<td  height="22" onclick="javascript : <?= (isset($ssearch) && $ssearch=='empexp') ? 'empexpensetyp(\''.$row['sno'].'\');' : 'displayEmplist(\''.$row['sno'].'\', \''.$row['name'].'\');'?>" class="RowBoarder">
		<strong><?= html_tls_specialchars($row['name']); ?></strong> <?php echo " - ".$row['sno']; ?></td>
		</tr>
		<tr nowrap="nowrap">
		<td bgcolor="#ffffff"></td>
		</tr>
	<?php
	   ++$rowloop;
	   }//while close
    } //if close
	else
	  echo "<tr class='mouseoutcont'><td height='22' align='center'><strong>Results not found.</strong></td></tr>"
	?>

	</table></div>
	</td>
</tr>
	  
  </table>
  <?php
		if(isset($ssearch) && $ssearch=='empexp')
		{
			echo '<input type="hidden" name="ssearch" id="ssearch" value="empexp" />';
		}
	?>
  </form>
  <form name="empexpFrm" id="empexpFrm" method="post" action="empexpensetype.php">
  	<input type="hidden" name="eid" id="eid" value="" />
  </form>
<script src="/BSOS/scripts/validateBottom.js"></script>
<script src="/BSOS/scripts/searchwindow.js"></script>
<script language="javascript">
function empexpensetyp(id)
{
	document.getElementById('eid').value = id;
	window.resizeTo(700, 620);
}
</script>
</body>
</html>