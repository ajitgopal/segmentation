<?php   		
	require("global.inc");
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");	

	$XAJAX_ON="YES";
	$XAJAX_MOD="EmpMainPayRegister";	
	$GridHS=true;		
	require("Menu.inc");
	$menu=new EmpMenu();	
	
	/*
		Modifed Date : Oct 05, 2015.
		Modified By  : Rajesh kumar v
		Purpose      :  changed the php calendar to jquery calendar
		TS Task Id   :  AKKEN_7_5_0_765(Receive Payments Issue).
	
		Modifed Date : Sept 16, 2010.
		Modified By  : Harikrishna Srinivas.K
		Purpose      : removed ($) symbol for amount
		TS Task Id   : 5268.
	
		File Name: 
		Creation Date: 
		Module Name: If used in multiple modules, list them 
		Sub Module: 
		Main Purpose: 
		TS Task ID: 
		
		Modifed Date : Sep 24, 2009.
		Modified By  : Kumar Raju k.
		Purpose      : Displying customers with ids in customer invoices and payments.
		TS Task Id   : 4621, (Accounts Enh) Need to show Customers along with id in all places where we are displaying customers in application.
		
		Modifed Date : July 04, 2009.
		Modified By  : Kumar Raju k.
		Purpose      : Inserting tax and discount totals for the invoices into invoice_taxes tables.
		TS Task Id   : 4475, (Accounts Enh) In Invoice , need to store tax and discount total separately for flow and better performance.
	*/
		 
	if(!isset($val))
	{
        $thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
        $duedate=date("m/d/Y",$thisday);
        
        $qu="SELECT MIN(rdate), MAX(rdate) FROM acc_reg WHERE acc_reg.type='PMT' AND status='ER'";
        $res=mysql_query($qu,$db);
        $dd=mysql_fetch_row($res);
        
        if ((is_null($dd[0])) || (is_null($dd[1])))
	    {
            $thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d")-30,date("Y"));
            $indate=date("m/d/Y",$thisday);						
        }
        else
	    {
            $indate=date("m/d/Y",strtotime($dd[0]));
            $duedate=date("m/d/Y",strtotime($dd[1]));
        }
        if(!isset($client))
        	$client="none";
		
		$mindate = $indate;
		$maxdate = $duedate;
    }	
	else
	{
        if($val=="in")
        {
            $t2=date("m/d/Y",$thisday);
            $indate=$t2;
        }
        else if($val=="due")
        {
            $t2=date("m/d/Y",$thisday);
            $duedate=$t2;
        }
	}
	$qu="SELECT DISTINCT(staffacc_cinfo.username), staffacc_cinfo.cname, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1)." 
	FROM staffacc_cinfo 
	LEFT JOIN invoice ON staffacc_cinfo.sno = invoice.client_name 
	LEFT JOIN Client_Accounts ca ON (ca.typeid=staffacc_cinfo.sno)
	WHERE invoice.deliver='yes' AND ca.deptid !='0' AND ca.deptid IN(".$deptAccesSno.")
	ORDER BY staffacc_cinfo.cname";
	$res=mysql_query($qu,$db);
	$bpay=mysql_num_rows($res);
	$coptions="<option value=none ".sele($dd[0],$client).">All</option>";
	while($dd=mysql_fetch_row($res))
	{
	   $coptions.="<option value='".$dd[0]."' ".sele($dd[0],$client).">".$dd[2]."</option>";
	}
		
	function sele($a,$b)
	{
        if($a==$b)
            return "selected";
        else
            return "";
	}
	$menu->showHeader("accounting","Customers","4|6");	
?>
<script language="javascript">
function doRead(src)
{
	rowid=src.getProperty("item/index");
	result=actdata[rowid][7];
	window.location.href=result;
}
function openNewWindow()
{
	
	result=gridActData[gridRowId][10];//8
	var url="editreceivepayreg.php?acc=receive&sno="+result;

	$().modalBox({'html':'<div id="attribute-selector" style="margin-left:-463px !important; margin-top:-176px !important; left:50% !important; top:50% !important; position: fixed;  "><img id="preloaderW" src="/BSOS/images/preloader.gif" ><div class="scroll-area"><div class="scroll-pane"><iframe id="schCalendarView" src="'+url+'" border="0" width=1000px height=350px scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:1000px;overflow:hidden;height:350px;position:relative;top:0px;left:0px;"></iframe></div></div></div>'});
		$("#modal-wrapper").addClass("receivemodal-wrapper");
		$("#attribute-selector .scroll-area").css({height: 350,width: 1000});
}
function modalBoxCloseandCancel(){
	parent.top.modalBoxClose();	
}
function modalBoxClose()
{
	$().modalBox('close');
}
</script>
<style>
.active-column-0 {width: 0px; display:none !important; padding:0px;}
.active-column-0.gecko {width: 0px; display:none;}
.active-column-9 .active-box-resize{display: none;}
.active-column-9 {width: 100px;}
.active-column-6 { text-align: right;}
.active-column-7 {width: 120px;}
.gridserbox7 {width: 100px;}
.gridserbox8 {width: 100px;}
.active-column-8 {width: 120px;}
.titleNewPad td {padding-left: 10px;}
.gridpaging .gridsoptbox{padding: 2px 2px !important;}
</style>
<link rel="stylesheet" href="/BSOS/css/fontawesome.css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<script src='scripts/editpayments.js' language='javascript'></script>
<script src='/BSOS/scripts/date_format.js' language='javascript'></script>
<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<link href="/BSOS/css/NewUiGrid.css" rel="stylesheet" type="text/css">
<script language="JavaScript" src="/BSOS/scripts/calendar.js"></script>

<!-- Modal Box Popup CSS and JS Code Starts -->
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<!-- Modal Box Popup CSS and JS Code Ends -->

<form name="f1" id="f1" method="POST" />
<input type=hidden id="val" name="val" />
<input type=hidden id="t1" name="t1" />
<input type=hidden id="t2" name="t2" />
<input type=hidden name='thisday' id='thisday'>
<input type=hidden id="datedue" name="datedue"  value="<? echo  date("Y-m-d",strtotime($duedate)); ?>" />
<input type=hidden id="datein" name="datein"  value="<? echo  date("Y-m-d",strtotime($indate)); ?>" />
<input type=hidden id="maxdate" name="maxdate" value="<?php  echo $maxdate; ?>" />
<input type=hidden id="mindate" name="mindate"  value="<?php  echo $mindate; ?>" />
<div id='oque'></div>
<div id='tque'></div>
<div id="main">
<td valign=top align=center class=tbldata>
<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI defaultTopRange">
    <div id="content">
    <tr>
    <td class="titleNewPad">
    <table width=100% cellpadding=0 cellspacing=0 border=0>
         <tr>
            <td align=left class="modcaption">&nbsp;Receive&nbsp;Payments&nbsp;Register</td>
            <td align="right" >
		From&nbsp;
		<input type=text id='frmdt' name='indate' size=10 maxlength=10  value='<?php echo $indate;?>'>
		<script language="JavaScript"> new tcal ({'formname':window.form,'controlname':'frmdt'});</script>
		&nbsp;&nbsp;To&nbsp;
		<input type=text name='duedate' id='todt' size=10  maxlength=10 value='<?php echo $duedate;?>'>
		<script language="JavaScript"> new tcal ({'formname':window.form,'controlname':'todt'});</script>
		&nbsp;
		<a href="javascript:DateCheck('indate','duedate','','','')">view</a>
	
	    </td>
        </tr>
		<tr>
            <td width=25%>&nbsp;<font class=afontstyle>Select Customer&nbsp;</font>
            <select name='client' id='client' onChange="javascript:doClient1()" class=drpdwnacc>
            <?php
                print stripslashes($coptions);
            ?>
            </select>
            </td>
        </tr>
    </table>
    </td>
    </tr>
    </div>
		
    <div id="topheader">
	<tr class="NewGridTopBg">
	<?php
		$name=explode("|","fa fa-usd~Credits&nbsp;Register");
		$link=explode("|","javascript:doCreditsRegister()");
		$heading="user.gif~Receive&nbsp;Payments&nbsp;Register";
		$menu->showMainGridHeadingStrip1($name,$link,$heading);
	?>
	</tr>
	</div>
		
    <div id="grid_form">
    <tr>
    <td>
	
	<script>
		var gridHeadCol = ["","<?php echo getEntityDispHeading('ID', 'Customer Name', 1); ?>","Date","Payment&nbsp;Method","Check&nbsp;Number","Account","Amount","Adjustment Type","Adjustment Amount","<a href=javascript:doGridSearch('search');>Search</a>&nbsp;&nbsp;<a href=javascript:clearGridSearch(),doGridSearch('reset');>Reset</a>"];
		
		var gridHeadData = ["","<input class=gridserbox type=text id=aw-column1 name=aw-column1 value=''>","<input class=gridserbox type=text id=aw-column2 name=aw-column2 value=''>","<input class=gridserbox type=text id=aw-column3 name=aw-column3 value=''>","<input class=gridserbox type=text id=aw-column4 name=aw-column4 value=''>","<input class=gridserbox type=text id=aw-column5 name=aw-column5 value=''>","<input class=gridserbox6 type=text id=aw-column6 name=aw-column6 value=''>","<input class=gridserbox7 type=text id=aw-column7 name=aw-column7 value=''>","<input class=gridserbox8 type=text id=aw-column8 name=aw-column8 value=''>","<input type=hidden id=aw-column9><a href=javascript:doGridSearch('search');><i alt='Search' class='fa fa-search fa-lg'></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=javascript:clearGridSearch(),doGridSearch('reset');><i alt='Reset' class='fa fa-reply fa-lg'></i></a>"];
		
		
		var gridActCol = ["","","","","","","","","","",""];
		var gridActData = [];
		var gridValue = "Accounting_empPayRegister";
		gridForm=document.forms[0];
		gridSearchResetColumn="9|";
		gridSortCol=2;//Default sorting column should be date
		gridSort = 'Desc';
		initGrids(10);
		gridExtraFields=new Array();
		gridExtraFields['client']=document.getElementById('client').value;
		gridExtraFields['datedue']=document.getElementById('datedue').value;
		gridExtraFields['datein']=document.getElementById('datein').value;
		xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);
		</script>
    <font class=afontstyle>   
    </font>
    </td>
    </tr>
    </div>
		
	<div id="botheader">
	<tr class="NewGridBotBg">
	<?php
		/*$name=explode("|","fa fa-usd~Credits&nbsp;Register");
		$link=explode("|","javascript:doCreditsRegister()");
		$heading="user.gif~Receive&nbsp;Payments&nbsp;Register";
		$menu->showMainGridHeadingStrip1($name,$link,$heading);*/
	?>
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
</form>
</body>
</html>