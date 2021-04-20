<?php
	$pagec1=$pagepla;
	require("global.inc");
	require("Menu.inc");
        $deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	$menu=new EmpMenu();
	if($_GET['sesreportname']!="")
	{
		$sesreportname=$_GET['sesreportname'];
		session_update("sesreportname");
	}
	if($main=="main")
	{
		if(session_is_registered("pagepla"))
			session_unregister("pagepla");
		unset($pagepla);
		$pagepla=$pagec1;
	}

	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate1=date("m/d/Y",$thisday);
	
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$fromdate=date("m/d/Y",$thisday);
	
	$rdata=explode("|",$pagepla);

	$tab=$rdata[19];
	$col_order=split('~',$rdata[20]);

	if($rdata[0]!="")
	{
		$fromdate=explode("/",$rdata[0]);
		$todate=explode("/",$rdata[1]);
		$ccheck="checked";
	}
	else
	{
		$fromdate="";
		$todate="";
		$ccheck="";
	}

	if($tab=="addr")
	{
		$esort=$rdata[22];
		$colstr=$rdata[21];

		if($rdata[3]!="")
			$colchk1="checked";
		else
			$colchk1="";
			
		if($rdata[4]!="")
			$colchk2="checked";
		else
			$colchk2="";
		
		if($rdata[5]!="")
			$colchk3="checked";
		else
			$colchk3="";
		
		if($rdata[6]!="")
			$colchk4="checked";
		else
			$colchk4="";
			
		if($rdata[7]!="")
			$colchk5="checked";
		else
			$colchk5="";
		
		if($rdata[8]!="")
			$colchk6="checked";
		else
			$colchk6="";
			
		if($rdata[9]!="")
			$colchk7="checked";
		else
			$colchk7="";
			
        if($rdata[10]!="")
			$colchk8="checked";
		else
			$colchk8="";

		if($rdata[11]!="")
			$orient=$rdata[11];
		else
			$orient="landscape";
			
		if($rdata[12]!="")
			$rpaper=$rdata[12];
		else
			$rpaper="letter";
					
		if($rdata[13]!="")
		{
			$compname=$rdata[13];
			$check="checked";
		}
		else
		{
			$compname="";
			$check="";
		}

		if($rdata[14]!="")
		{
			$maintitle=$rdata[14];
			$check1="checked";
		}
		else
		{
			$maintitle="";
			$check1="";
		}
		
		if($rdata[15]!="")
		{
			$sbtitle=$rdata[15];
			$check2="checked";
		}
		else
		{
			$sbtitle="";
			$check2="";
		}
	
		if($rdata[16]!="")
			$check3="checked";
		else
			$check3="";
					
		if($rdata[17]!="")
			$check5="checked";
		else
			$check5="";						
				
		if($rdata[18]!="")
		{
			$efooter=$rdata[18];
			$check6="checked";
		}
		else
		{
			$efooter="";
			$check6="";	
		}
		$selcand=$rdata[23];
		
	}
	else
	{
		$esort="ASC";
		$colstr="Candidate";

        $colchk1="checked";
		$colchk2="checked";
		$colchk3="checked";
		$colchk4="checked";
		$colchk5="checked";
		$colchk6="checked";
		$colchk7="checked";
        $colchk8="checked";
								
		$check="checked";
		$check1="checked";
		$check2="checked";
		$check3="checked";
		$check4="checked";
		$check5="checked";
		$check6="checked";
		
		$orient="landscape";
		$rpaper="letter";
		$compname=$companyname;
		$maintitle="Company Report";
		$sbtitle="Profit & Loss by Assignments";
		$efooter="Footer";
		$alignn="standard";
	}	

	function sele($a,$b)
	{
		if($a==$b)
		  return "selected";
		else
		  return "";

	}
	
	function sel($a,$b)
	{
		if($a==$b)
		  return "checked";
		else
		  return "";
	}
?>
<html>
<head>
<script language="javascript">
function chkClose()
{
	form1.action="../ses.php";
	form1.submit();
	window.close();

}
</script>
<title>Customize</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<script src=/BSOS/scripts/tabpane.js></script>
<script src=scripts/validatecust.js language="javascript"></script>
<script src=/BSOS/scripts/moveto.js language=javascript></script>
</head>
<body>
<form name="form1" action=accounts.php method=post target="reportwindow">
<input type=hidden name=tab value='tabview'>
<input type=hidden name=pclrep>
<input type=hidden name=daction value='storereport.php'>
<input type=hidden name=tabnam value='addr'>
<input type=hidden name=dateval value="<?php echo $todate1;?>">
<input type=hidden name=main value="<?php echo $main; ?>">
<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
	<td>
		<table width=100% cellpadding=0 cellspacing=0 border=0>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=modcaption>&nbsp;&nbsp;Report Customization</font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
		</table>
	</td>
	</tr>
	</div>
	<div id="grid_form">
	<tr>
	<td>
	<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">

	<tr>
		<td width=100% valign=top align=center>
		<div class="tab-pane" id="tabPane1">
        <script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
			<div class="tab-page" id="tabPage11">
			<h2 class="tab">Report</h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) );</script>
                <div class="tab-pane" id="tabPane2">
                <script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
				<div class="tab-page" id="tabPage21" >
				<h2 class="tab">Customize</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ));</script>
				<?php require("viewcust.php");?>
				</div>
				<!--<div class="tab-page" id="tabPage22" >
				<h2 class="tab">Filter</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ));</script>
				<?php require("viewfilter.php");?>
				</div>-->
				<div class="tab-page" id="tabPage23" >
				<h2 class="tab">Columns</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage23" ));</script>
				<?php require("viewcolumn.php");?>
				</div>
				<div class="tab-page" id="tabPage24" >
				<h2 class="tab">Sort</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage24" ));</script>
				<?php require("viewcolsort.php");?>
				</div>
				<div class="tab-page" id="tabPage25" >
				<h2 class="tab">Order</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage25" ));</script>
				<?php require("viewsort.php");?>
				</div>
				<div class="tab-page" id="tabPage26">
				<h2 class="tab">Format</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage26" ));</script>
				<?php require("viewformat.php");?>
				</div>
				<!--<div class="tab-page"  id="tabPage25">
				<h2 class="tab">Fonts</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage25" ));</script>
				<?php //require("viewfonts.php");?>
				</div>-->
                <div class="tab-page" id="tabPage27">
				<h2 class="tab">Header/Footer</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage27" ));</script>
				<?php require("viewheader.php");?>
				</div>
			 <!--</div>-->
			 <?php
                if($ind=='')
                    $ind=0;
			 ?>
             </div>
		</td>
		</tr>
	</table>
	</td>
	</tr>
	</div>
</tr>
</table>
</div>
</form>
</body>
</html>
