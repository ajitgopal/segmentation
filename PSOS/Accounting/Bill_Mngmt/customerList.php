<?php 
	require("global.inc");
	require("dispfunc.php");
	require("waitMsg.inc");	//To display the delay message

	$deptAccessObj = new departmentAccess();
	$deptAccesSno_BO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	function getDispName($argFieldName)
	{
		$arrDisplayName  = array("companyname"=>"Company Name","BillingAddress"=>"Billing Address","Parent"=>"Parent","CustomCompanyName" => "Company Name");
		return $arrDisplayName[$argFieldName];
	}
	$appendurl = "fieldname={$fieldname}".((isset($ssearch) && $ssearch=='custexp') ? '&amp;ssearch=custexp' : '');

	$searchuser = $username;
	
	if($letter == "")
	   $condition = "sc.cname LIKE 'a%'";
	elseif($letter == "others")
		$condition = "(ASCII(sc.cname) < '65' or (ASCII(sc.cname) > '90' and ASCII(sc.cname) < '97') or 
		ASCII(sc.cname) > '122') and ASCII(sc.cname) != '32' and sc.cname != '' ";
	else
	  $condition = "sc.cname LIKE '".$letter."%'";

	if($searchbox)
	  $condition = " sc.cname LIKE '%".$searchbox."%'";

	$sqlStaff = "select sc.sno, sc.cname,sc.address1,sc.address2,sc.city,sc.state from staffacc_cinfo sc LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=staffacc_cinfo.sno) where Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") AND {$condition} group by sc.sno order by sc.cname";
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
function displaycust(val,compname)
{
	parent.window.opener.displaycust(val,compname);
}
</script>
</head>
<body>
<form name="formsearch" action="" onsubmit="return do_search()">
	<input type="hidden" name="fieldname" value="<?= $fieldname ?>" />
	<table width=99% cellpadding=1 cellspacing=1 class="ProfileNewUI" align="center">
		<tr>
			<td valign="middle" align="center" colspan="2" class="joborderSerchHed">
				<font> Search for Customers</font>
			</td>
		</tr>
		<tr>
			<td width="20%"><font class="plainText">Customer&nbsp;Name&nbsp;: </font></td> 
			<td width="80%">
				<input type="text" size="45"  maxlength="30" name="searchbox" value="<?=html_tls_entities(stripslashes($searchbox));?>"/>&nbsp;<a href="javascript:do_search()" ><i class="fa fa-search fa-lg" name="comp"></i></a>&nbsp;
				<a href="customerList.php?letter=a&<?= $appendurl?>"> <i class="fa fa-reply fa-lg"></i></a>
			</td>
		</tr>
		<tr>
		   <td class="joborderSerchList" colspan="2"> &nbsp;&nbsp;&nbsp;
			   <a href="customerList.php?letter=a&<?= $appendurl?>" class="alphabetStyle">A</a>&nbsp;
			   <a href="customerList.php?letter=b&<?= $appendurl?>" class="alphabetStyle">B</a>&nbsp;
			   <a href="customerList.php?letter=c&<?= $appendurl?>" class="alphabetStyle">C</a>&nbsp;
			   <a href="customerList.php?letter=d&<?= $appendurl?>" class="alphabetStyle">D</a>&nbsp;
			   <a href="customerList.php?letter=e&<?= $appendurl?>" class="alphabetStyle">E</a>&nbsp;
			   <a href="customerList.php?letter=f&<?= $appendurl?>" class="alphabetStyle">F</a>&nbsp;
			   <a href="customerList.php?letter=g&<?= $appendurl?>" class="alphabetStyle">G</a>&nbsp;
			   <a href="customerList.php?letter=h&<?= $appendurl?>" class="alphabetStyle">H</a>&nbsp;
			   <a href="customerList.php?letter=i&<?= $appendurl?>" class="alphabetStyle">I</a>&nbsp;
			   <a href="customerList.php?letter=j&<?= $appendurl?>" class="alphabetStyle">J</a>&nbsp;
			   <a href="customerList.php?letter=k&<?= $appendurl?>" class="alphabetStyle">K</a>&nbsp;
			   <a href="customerList.php?letter=l&<?= $appendurl?>" class="alphabetStyle">L</a>&nbsp;
			   <a href="customerList.php?letter=m&<?= $appendurl?>" class="alphabetStyle">M</a>&nbsp;
			   <a href="customerList.php?letter=n&<?= $appendurl?>" class="alphabetStyle">N</a>&nbsp;
			   <a href="customerList.php?letter=o&<?= $appendurl?>" class="alphabetStyle">O</a>&nbsp;
			   <a href="customerList.php?letter=p&<?= $appendurl?>" class="alphabetStyle">P</a>&nbsp;
			   <a href="customerList.php?letter=q&<?= $appendurl?>" class="alphabetStyle">Q</a>&nbsp;
			   <a href="customerList.php?letter=r&<?= $appendurl?>" class="alphabetStyle">R</a>&nbsp;
			   <a href="customerList.php?letter=s&<?= $appendurl?>" class="alphabetStyle">S</a>&nbsp;
			   <a href="customerList.php?letter=t&<?= $appendurl?>" class="alphabetStyle">T</a>&nbsp;
			   <a href="customerList.php?letter=u&<?= $appendurl?>" class="alphabetStyle">U</a>&nbsp;
			   <a href="customerList.php?letter=v&<?= $appendurl?>" class="alphabetStyle">V</a>&nbsp;
			   <a href="customerList.php?letter=w&<?= $appendurl?>" class="alphabetStyle">W</a>&nbsp;
			   <a href="customerList.php?letter=x&<?= $appendurl?>" class="alphabetStyle">X</a>&nbsp;
			   <a href="customerList.php?letter=y&<?= $appendurl?>" class="alphabetStyle">Y</a>&nbsp;
			   <a href="customerList.php?letter=z&<?= $appendurl?>" class="alphabetStyle">Z</a>&nbsp;
			   <a href="customerList.php?letter=others&<?= $appendurl?>" class="alphabetStyle">other</a>
		   </td>
		</tr>
		<tr>
			<td colspan="2"><font class="plainText">Click on Company for selection</font></td>
		</tr>
		 <tr>
			<td colspan="2">
				<div class="styleBg">
					<table border="0" cellpadding="0" cellspacing="0" width="95%" align="center">
						<tr class="mouseoutcont"><td  height="3"></td></tr>
						<?php $rowloop = 1;
						if($numRows) {
							   while($row = mysql_fetch_assoc($resultset))   {
								 $address = $row['city']." ".$row['state'];
						?>
							<tr onmouseover="mouseover('<?=$rowloop?>')" onMouseOut="mouseout(<?=$rowloop?>)" onMouseDown="mouseout('<?=$rowloop?>')" id=rowid<?=$rowloop?> class="mouseoutcont">
								<td  height="22" onclick="javascript :parent.window.opener.displaycust(<?php echo $row['sno'];?>, '<?php echo addslashes($row['cname']); ?>');" class="RowBoarder">
									<strong><?= html_tls_specialchars($row['sno'].' - '.str_replace("\\","",$row['cname'])); ?></strong>
									<?=  str_replace("\\","",$address); ?>
								</td>
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
	?>
</form>
<form name="custexpFrm" id="custexpFrm" method="post" action="custexpensetype.php">
	<input type="hidden" name="cid" id="cid" value="" />
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
</script>
</body>
</html>