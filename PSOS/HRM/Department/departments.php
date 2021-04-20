<?php

	require("global.inc");

	require("../../Include/commonfuns.inc");

	$XAJAX_ON="YES";

    $XAJAX_MOD="Departments";

	$GridHS=true;

	/*

	Modified Date : January 02, 2009

	Modified By   : Swetha K.

	Purpose       : Changed the Modified Date to Modified By

	TS Task Id    : 4876

	

	Modified Date : Dec 30, 2009

	Modified By   : Kumar Raju K.

	Purpose       : Provided Delete and Edit Departments and changed grid display columns.

	TS Task Id    : 4875, (MultiplePayrates Enh) Need to provide Delete and Edit Departments and change grid display columns.

	

	Modifed Date : December 11, 2009.

	Modified By  : Sambasivarao.L.

	Purpose      : Providing new class functionality.

	TS Task Id  : 4846 (Accounting Enh) HRM-Departments- Need to implement class droplist functionality.

	

	Modifed Date : Aug 21, 2009

	Modified By  : Prasadd

	Purpose      : HRM departments main grid location column is added.

	Task Id      : 4431.

	*/

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

	session_unregister("fromAssign");

	session_unregister("conusername");

	session_unregister("recno");

	session_unregister("employee_name");

?>

<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">

<script language="javascript" src="scripts/validatedept.js"></script>

<script language="javascript" src="scripts/validatedepartment.js"></script>

<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>

<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>

<script languege="javascript">

function openNewWindow()

{

	result=gridActData[gridRowId][14];

    document.location.href="managedept.php?edeptname="+result;

}

</script>

<style type="text/css">
.active-column-6 {width: 70px;}
.active-column-7 {width: 180px; text-align: left; direction:ltr;}
.active-column-8 {width: 180px; text-align: left; direction:ltr;}

/* .active-column-12 .active-box-resize {display: none;} */

.gridserbox6 {width: 100%; font-family: Arial; font-size:10px; text-align: left !important;border:1px solid #aaaaaa;height:35px;}

.modalDialog_transDivs{	

	filter:alpha(opacity=40);	/* Transparency */

	opacity:0.4;	/* Transparency */

	background-color:#AAA;

	z-index:1000;

	position:absolute; /* Always needed	*/

}
.ActionMenuLeftFixed{z-index: 998px !important;}

</style>



<form method="post" name="adddept" id="adddept" action="dodept.php">

<input type="hidden" name="addr" id="addr" value="<?php echo $addr;?>">

<input type="hidden" name="addr2" id="addr2" value="">

<input type="hidden" name="norec" id="norec" value=<? echo $norec ?>>

<input type="hidden" name="aa" id="aa">

<input type="hidden" name="nav" id="nav">

<input type="hidden" name="hrvals" id="hrvals">

<input type="hidden" name="pdir" id="pdir">

<input type="hidden" name="edeptn" id="edeptn">



<div id="main">

<td valign=top align=center>

<table width=99% cellpadding=0 cellspacing=0 border=0>

	<div id="content">

	<tr>

		<td class="titleNewPad">

			<table width=100% cellpadding=0 cellspacing=0 border=0>

				<tr>

					<td colspan=2><font class=bstrip>&nbsp;</font></td>

				</tr>

				<tr>

					<td><font class=modcaption>Departments</font></td>

					<td align=right><font class=hfontstyle>Following are the current Available Departments</font></td>

				</tr>

				<tr>

					<td colspan=2><font class=bstrip>&nbsp;</font></td>

				</tr>

				<tr>

					<td colspan=2 align="center"><font class=afontstyle4><?php echo $mes; ?></font></td>

				</tr>

			</table>

		</td>

	</tr>

	</div>



	<div id="topheader">

	<tr class="NewGridTopBg">

	<?php

		$name=explode("|","fa-plus-square~Add&nbsp;Department|fa-arrow-circle-up~Export|fa fa-scissors~Edit|fa-trash~Delete");

		$link=explode("|","javascript:doAdd()|javascript:doExport()|javascript:doEdit()|javascript:doDelete()");

     	$heading="";

		$menu->showMainGridHeadingStrip1($name,$link,$heading);

	?>

	</tr>

	</div>



	<div id="grid_form">

	<tr>

		<td>

			<script>

			var gridHeadCol = ["<label class='container-chk'><input type=checkbox name='chk' id='chk' onClick=chke(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>","Department Code","Department Name","Parent Department","Location","Class","# Employees ","Front Office Permissions","Back Office Permissions","Created Date","Created By","Modified Date","Modified By",""];

			

			var gridHeadData = ["","<input class=gridserbox type=text id=aw-column1 name=aw-column1 value=''>","<input class=gridserbox type=text id=aw-column2 name=aw-column2 value=''>","<input class=gridserbox type=text id=aw-column3 name=aw-column3 value=''>","<input class=gridserbox type=text id=aw-column4 name=aw-column4 value=''>","<input class=gridserbox type=text id=aw-column5 name=aw-column5 value=''>","<input class=gridserbox type=text id=aw-column6 name=aw-column6 value=''>","<input class=gridserbox type=text id=aw-column7 name=aw-column7 value=''>","<input class=gridserbox type=text id=aw-column8 name=aw-column8 value=''>","<input class=gridserbox type=text id=aw-column9 name=aw-column9 value=''>","<input class=gridserbox type=text id=aw-column10 name=aw-column10 value=''>","<input class=gridserbox type=text id=aw-column11 name=aw-column11 value=''>","<input type=text class=gridserbox id=aw-column12 name=aw-column12 value=''>",""];

			

			

			var gridActCol = ["","","","","","","","","","","","","",""];

			var gridActData = [];

			var gridValue = "HRM_Departments";

			gridForm=document.forms[0];

			gridSearchResetColumn="13|";

			initGrids(14);

	

			xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);

			</script>

			</td>

	</tr>

	</div>



	<div id="botheader">

	<tr class="NewGridBotBg">

	<!-- <?php

		/*$name=explode("|","fa-arrow-circle-up~Export|fa-plus-square~Add&nbsp;Department|fa fa-scissors~Edit&nbsp;Department|fa-trash~Delete&nbsp;Department");

		$link=explode("|","javascript:doExport()|javascript:doAdd()|javascript:doEdit()|javascript:doDelete()");

		$heading="";

		$menu->showMainGridHeadingStrip1($name,$link,$heading);*/

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