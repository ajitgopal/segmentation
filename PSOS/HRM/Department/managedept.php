<?php

    require("global.inc");
	require("../../Include/commonfuns.inc");
	
	$XAJAX_ON="YES";
    $XAJAX_MOD="HistoryDepartment";

	$GridHS=true;
	require("Menu.inc");
    $menu=new EmpMenu();
	$menu->showHeader("hrm","Departments","11");
	function sel($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

	session_unregister("page1");
	session_unregister("page2");
	session_unregister("page3");
	session_unregister("page4");
	session_unregister("page5");
	session_unregister("page6");
	session_unregister("page7");
	session_unregister("page8");
	session_unregister("page9");
	session_unregister("page10");
	session_unregister("page111");
	session_unregister("page12");
	session_unregister("page13");
	session_unregister("page14");
	session_unregister("page15");
	session_unregister("page17");
	session_unregister("page18");
	session_unregister("page19");
	session_unregister("page20");
	session_unregister("page211");
	session_unregister("page22");
	session_unregister("page23");
	session_unregister("command");
	session_unregister("from");
?>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<script language="javascript" src=scripts/validatedept.js></script>
<script type="text/javascript" src="/BSOS/scripts/common_ajax.js"></script>
<script language="javascript" src=scripts/managedepartment.js></script>
<script type="text/javascript" src="/BSOS/scripts/OutLookPlugInDom.js"></script>
<script>OutLookPlugInDom['Enable']="<?php echo OUTLOOK_PLUG_IN;?>";</script>
<script language="javascript">
function openNewWindow()
{	
    form=document.adddept;
    var result = gridActData[gridRowId][8];
   var ename=form.edeptname.value;

    var v_width  = 950;
    var v_heigth = 620;

 remote=window.open("/BSOS/HRM/Employee_Mngmt/getnewconreg.php?command=emphire&from=dept&rec="+result+"&edeptname="+ename,"Department","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,resizable=yes,scrollbars=yes,left=30,top=30,dependent=yes");
    remote.focus();
}
</script>
<style>
.active-column-0 {width:  30px;}
.active-column-1 {width: 110px; text-align: left;}
.active-column-2 {width: 100px; text-aligh:left;}
.active-column-3 {width: 110px; text-align: left;}
.active-column-4 {width: 110px; text-align: left;}
.active-column-5 {width: 150px; text-align: left;}
.active-column-6 {width: 110px; text-align: left;}
.active-column-7 {width: 80px; text-align: left;}
.active-column-8 {width:  50px; text-align: left;}

.serbox0 { border: 1px solid black; width: 99px; height: 15px; font-family: arial; font-size:12px;}
.serbox1 { border: 1px solid black; width: 89px; height: 15px; font-family: arial; font-size:12px;}
.serbox2 { border: 1px solid black; width: 99px; height: 15px; text-align: left; font-family: arial; font-size:12px;}
.serbox3 { border: 1px solid black; width: 98px; height: 15px; text-align: left; font-family: arial; font-size:12px;}
.serbox4 { border: 1px solid black; width: 139px; height: 15px; text-align: left; font-family: arial; font-size:12px;}
.serbox5 { border: 1px solid black; width: 100px; height: 15px; text-align: left; font-family: arial; font-size:12px;}
.serbox6 { border: 1px solid black; width: 70px; height: 15px; text-align: left; font-family: arial; font-size:12px;}
.serbox7 { border: 1px solid black; width: 70px; height: 15px; text-align: font-family: arial; font-size:12px;}
.serbox8 { border: 1px solid black; width: 39px; height: 15px; text-align: font-family: arial; font-size:12px;}

@-moz-document url-prefix() {
	#aw-column7 {width:69px;}
}

@media screen and (-webkit-min-device-pixel-ratio:0) {
	#aw-column7 {width:77px;}

}

.active-templates-row{
    display: inline-block;
    min-width: 100%;
    overflow-y: visible;
    width: 100%;
}
</style>
<form method="post" name="adddept" id="adddept" action="dodept.php">
<input type=hidden name='addr' value="<?php echo $addr;?>">
<input type=hidden name='addr2' id='addr2' value="">
<input type=hidden name='norec' id='norec' value=<? echo $norec ?>>
<input type=hidden name='aa' id='aa' value="" />
<input type=hidden name='nav' id='nav' value="" />
<input type=hidden name='deptno' id='deptno' value=""/>
<input type=hidden name='edeptname' id='edeptname' value="<?php echo $edeptname;?>">

<div id="main">
<td valign=top align=center>
<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI defaultTopRange" align="center">
	<div id="content">
	<tr>
	<td class="titleNewPad">
		<table width=100% cellpadding=0 cellspacing=0 border=0>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
			<td align=left><div class=modcaption style="padding-top:18px "><?php 
			$query="SELECT deptname FROM department where sno=$edeptname AND status='Active'";
			$res=mysql_query($query,$db);
			$row=mysql_fetch_row($res);
			echo $row[0]; ?> &nbsp;Department</div>
			<div class=afonstyle style="text-align: right;">&nbsp;</div></td>
			<td colspan="2" align="right">
			<font class=afontstyle>Select Employees to move into another Department</font>
			<?php
			if($edeptname!="None" && $edeptname!="")
			{
			?>
				<font face="Arial, Helvetica, sans-serif" size="1">
				<select name="edeptname1" id="edeptname1" class="drpdwne" style="width:180px">
				<option value="None" selected>Select</option>
				<?php
				$deptAccessObj = new departmentAccess();
				$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO','BO'");

				$dque="SELECT dept.sno,IF(dept.depcode!='',CONCAT_WS(' - ', dept.depcode, dept.deptname),dept.deptname)
						,dept.parent 
						FROM department dept
						JOIN department_permission deptPer ON (deptPer.dept_sno = dept.sno) 
						WHERE dept.sno IN (".$deptAccesSno.") 
						AND dept.status='Active' GROUP BY dept.sno ORDER BY dept.parent";
				$dres=mysql_query($dque,$db);
				while($drow=mysql_fetch_row($dres))
				{
					if ($edeptname != $drow[2]) {
					
						if($drow[2]=="0")
						{
							print "<option ".sel($edeptname,$drow[0])." value=".$drow[0].">".$drow[1]."</option>";
						}
						else
						{
							$parent=getParent($drow[2],$drow[1],$db);
							print "<option ".sel($edeptname,$drow[0])." value=".$drow[0].">".$parent."</option>";
						}
					}
				}
				?>
				
				</select>
				</font>
				&nbsp;<font class=afontstyle><b><a href='javascript:doMove();'>Move</a></b></font>
			<?php
			}
			?>
			</td>
		</tr>
		</table>
	</td>
	</tr>
	<?php
	if(isset($mes))
		print "<tr><td align=center><font class=afontstyle4>$mes</font></td></tr>";
	?>
	</div>

	<div id="topheader">
	<tr class="NewGridTopBg">
	<?php
		$sendMailName="";
		$sendMailLink="";
		if(chkUserPref($collaborationpref,"1") || OUTLOOK_PLUG_IN=="Y")
	    {
			$sendMailName="fa fa-envelope~Send&nbsp;Mail|";
			$sendMailLink="javascript:doMail()|";
		}
		$HeaderName=explode("|",$sendMailName."fa-ban~Cancel");
		$HeaderLink=explode("|",$sendMailLink."departments.php");
		$headingName="user.gif~Departments";
		$menu->showHeadingStrip1($HeaderName,$HeaderLink,$headingName);
		
		$serstatus = "<select class=gridserbox id=aw-column6 name=column6 onChange=doSearchResetCat() ><option value=''>All</option><option value='OP'>On Assignment</option><option value='OB'>On Bench</option></select>";

	$sertype = "<select class=gridserbox name=aw-column7 id=aw-column7 onChange=doSearchResetCat() ><option value=''>All</option><option value='W-2'>W-2</option><option  value='1099'>1099</option><option value='C-to-C'>C-to-C</option><option value='None'>None</option></select>";
	?>
	</tr>
	</div>

	<div id="grid_form">
	<tr>
		<td>
        <script>
		var gridHeadCol = ["<label class='container-chk'><input type=checkbox name='chk' id='chk' onClick=chke(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>","First Name","Last Name","Skills","Phone","Email","Job Status","Type"];

		var gridHeadData = ["","<input class=gridserbox type=text id=aw-column1 name=aw-column1 value='' >","<input class=gridserbox type=text id=aw-column2 name=aw-column2 value=''>","<input class=gridserbox type=text id=aw-column3 name=aw-column3 value=''>","<input class=gridserbox type=text id=aw-column4 name=aw-column4 value=''>","<input class=gridserbox type=text id=aw-column5 name=aw-column5 value=''>","<span onclick=disableResize();><?php echo $serstatus; ?></span>","<span onclick=disableResize();><?php echo $sertype; ?></span>"];

			
			var gridActCol = ["","","","","","","",""];

			var gridActData = [];

			var gridValue = "HRM_HistoryDepartment";

			gridForm=document.forms[0];

			gridSearchResetColumn="8|";

			initGrids(8);
			
			gridExtraFields = new Array();
			gridExtraFields['edeptname']='<?php echo $edeptname;?>';
			xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);

			</script>
		</td>
	</tr>
	</div>

	<div id="botheader">
	<tr class="NewGridBotBg">
	<!-- <?php
		/*$menu->showHeadingStrip1($HeaderName,$HeaderLink,$headingName);*/
	?> -->
	</tr>
	</div>
</table>
</td>
</div>

<tr>
<?php
	$menu->showFooter();
?>
</tr>
</table>
</form>
</body>
</html>