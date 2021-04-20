<?php 
   	require_once("global.inc");
	$XAJAX_ON="YES";
	$XAJAX_MOD="Invoice_History";
	
	$GridHS=true;	
	require_once("Menu.inc");
	$menu=new EmpMenu();
	require_once("accountwidgets.php");
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$user = (isset($_GET['token'])) ? encrypt_decrypt('decrypt',$_GET['token']):$username;
	if($user!= $username){
		$default_expire_link="https://login.$egdomain/?error=expire";
		echo "<script>top.window.location.href='".$default_expire_link."';</script>";
		exit;
	}
	
	function sele($a,$b)
	{
        if($a==$b)
            return "selected";
        else
            return "";
	}
	if($invclient=="")
	   $invclient = "all";

	$que="select invoice.client_name FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' WHERE invoice.deliver='Yes' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH')  AND Client_Accounts.clienttype IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno.") GROUP BY invoice.client_name";
	$res=mysql_query($que,$db);
	$bpay=mysql_num_rows($res);
	while($dd=mysql_fetch_row($res))
		$clients = ($clients==0) ? $dd[0] : $clients.",".$dd[0];

	$que="select Client_Accounts.loc_id FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' WHERE invoice.deliver='Yes' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.clienttype IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno.") GROUP BY Client_Accounts.loc_id";
	$res=mysql_query($que,$db);
	while($dd=mysql_fetch_row($res))
		$locations = ($locations==0) ? $dd[0] : $locations.",".$dd[0];

	$que="select Client_Accounts.deptid FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' WHERE invoice.deliver='Yes' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.clienttype IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno.") GROUP BY Client_Accounts.deptid";
	$res=mysql_query($que,$db);
	while($dd=mysql_fetch_row($res))
		$depts = ($depts==0) ? $dd[0] : $depts.",".$dd[0];

	if(!isSet($val))
	{
        if($bpay>0)
        {
            $qu="select MIN(STR_TO_DATE(invoice_date,'%m/%d/%Y')),MAX(STR_TO_DATE(invoice_date,'%m/%d/%Y')) from invoice where invoice.deliver='Yes' AND invoice.status = 'ACTIVE'";
            $res=mysql_query($qu,$db);
            $dd=mysql_fetch_row($res);
            $servicedateto=date("m/d/Y",strtotime($dd[1]));
            $servicedate=date("m/d/Y",strtotime($dd[0]));
			}
        else
        {
            $thisday2=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
            $servicedateto=date("m/d/Y",$thisday2);
            $thisday1=mktime(date("H"),date("i"),date("s"),date("m"),date("d")-6,date("Y"));
            $servicedate=date("m/d/Y",$thisday1);
        }
	}
	else
	{
        if($val=="serv")
        {
            $thisday1=$t1;
            $servicedate=date("m/d/Y",$t1);
            $servicedateto=$t2;
            $t21=explode("/",$t2);
            $thisday2= mktime (0,0,0,$t21[0],$t21[1],$t21[2]);
            $todaf=date("Y-m-d",$thisday2);
            $tod=date("Y-m-d",$t1);
        }
        else if($val=="servto")
        {
            $servicedate=$t1;
            $servicedateto=date("m/d/Y",$t2);
            $t11=explode("/",$t1);
            $thisday1= mktime (0,0,0,$t11[0],$t11[1],$t11[2]);
            $tod=date("Y-m-d",$t2);
            $todaf=date("Y-m-d",$thisday1);
        }
	    else if($val=="redirect")
        {
            $servicedate=$invservicedate;
            $servicedateto=$invservicedateto;
        }
    }

	$invservicedate=$servicedate;
	$invservicedateto=$servicedateto;

    $menu->showHeader("accounting","Invoice History","4|3");

    $sqlNewApp = "SELECT * FROM notifications_settings WHERE mod_id = 'invoice_css_download' AND status = 1 AND notify_status = 'ACTIVE'";
	$resNewApp = mysql_query($sqlNewApp,$db);
	$rowNewApp = mysql_fetch_assoc($resNewApp);

    $empNmes = '';
    $empNmeList = "SELECT group_concat(REPLACE(CONCAT_WS(' ',TRIM(hg.fname),TRIM(hg.mname),TRIM(hg.lname)),'  ',' '))  from hrcon_general hg  where hg.ustatus='active' AND hg.username IN (".$rowNewApp['notify_people'].")";

	$resNmeList 		= mysql_query($empNmeList,$db);
	$empNmeListArr  	= mysql_fetch_array($resNmeList);
	$empNmes			= $empNmeListArr[0];
?>
<style>
.active-column-7 { text-align: right;}
.active-column-6 { text-align: right;}

.active-column-11 {width: 85px;}

@media screen and (-webkit-min-device-pixel-ratio:0) {
.active-column-6 { text-align: left;padding-left:-115px;}
.active-column-7 { text-align: left;padding-left:-114px;}
.serbox6{text-align:left !important;}
 }
 .alert-cntrbtns {
    margin: 0 auto !important;
    width: 75px !important;
}
#attribute-selector .scroll-area {
height: 483px !important;
width: 700px !important;
overflow: hidden !important;
}
.JoAssignmodal-wrapper {
	left: 50% !important;
	margin-left: -339px !important;
}#modal-wrapper {
	position: absolute;
	top: 50% !important;
	z-index: 9991;
	/* border: solid 2px #047EA0; */
	left: 50% !important;
	margin-left: -450px;
	margin-top: -280px;
}
</style>

<link rel="stylesheet" href="/BSOS/css/popup_styles.css" media="screen" type="text/css">
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language="javascript" src="/BSOS/scripts/common_ajax.js"></script>
<script language="javascript" src="scripts/validateinhis.js"></script>
<script src="/BSOS/scripts/date_format.js" language="javascript"></script>
<script src="/BSOS/scripts/calendar.js" language="javascript"></script>

<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/iframeLoader.js"></script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>

<link href="/BSOS/css/calendar.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/BSOS/css/colorbox.css" media="screen" type="text/css">
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">

<script language="javascript">

function showPopup(eval, rowId){
	var v_heigth = 550;
	var v_width  = 650;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	var url = 'invoice_logs.php?id='+rowId;
	window.open(url, "emailinv", "width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
	remoteres.focus();
		
}
function showPopupOld(eval,rowId){
	
	document.getElementById('light').style.display='block';
	document.getElementById('content').style.display='block';
	document.getElementById('logs').src = 'invoice_logs.php?id='+rowId;
	

   var docHeight = $(document).height();

   $(".black_overlay").append("<div id='overlay'></div>");

   $("#overlay")
      .height(docHeight)
      .css({
         'opacity' : 0.4,
         'position': 'absolute',
         'top': 0,
         'left': 0,
         'background-color': 'black',
         'width': '100%',
         'z-index': 5000
      });
}

function openNewWindow()
{
	result = gridActData[gridRowId][14]+"&acc=invoicehis";
	//window.location.href=result;
	var v_heigth = 700;
	var remoteres;
    var v_width  = 1050;

	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	remoteres = window.open(result,"","width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
	remoteres.focus();
}

function getPdf_orient(){
	var form=document.sheet;
	var get_auids = document.getElementsByName("auids[]");
	var get_newAuids = document.getElementsByName("newauids[]");
	var pdfOrit_diff = 0;
	var prev_orient = '';
	for (var i=0; i < get_newAuids.length; i++) 
	{
		if (get_auids[i].checked == true){
			getAuid_val=get_newAuids[i].value;
			
			if(prev_orient != getAuid_val && prev_orient!=''){
				pdfOrit_diff=1;
			}
			prev_orient = getAuid_val;
		}
	}
	return pdfOrit_diff; 
}

function printingOptions()
{
	var form=document.sheet;
	numAddrs = numSelected();
	valAddrs = valSelected();
	var pdf_orientation = getPdf_orient();
	if (numAddrs < 0) 
	{
		alert("There are no Invoice(s) to print.");
		return;
	}
	else if (! numAddrs) 
	{
		alert("Select a Customer to print Invoice.");
		return;
	}
	else if(pdf_orientation == 1)
	{
		alert("Select the invoices which have same PDF Orientation type.");
		return;
	}
	else
	{
		var v_heigth = 220;
		var v_width  = 500;
		var top1=(window.screen.availHeight-v_heigth)/2;
		var left1=(window.screen.availWidth-v_width)/2;
		remoteres = window.open("selectprintoptions.php","","width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
		remoteres.focus();
	}	
}

function emailingOptions()
{
	var form=document.sheet;
	numAddrs = numSelected();
	valAddrs = valSelected();
	if (numAddrs < 0) 
	{
		alert("There are no Invoice(s) to Email.");
		return;
	}
	else if (! numAddrs) 
	{
		alert("Select a Customer to  Email.");
		return;
	}
	else
	{
	
	    var v_heigth = 650;
		var v_width  = 850;
		var top1=(window.screen.availHeight-v_heigth)/2;
		var left1=(window.screen.availWidth-v_width)/2;
		remoteres = window.open("selectemailoptions.php?valAddrs="+valAddrs,"","width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
		remoteres.focus();
		//emailconfim(valAddrs);
	}	
}

function emailconfim(valAddrs){
	if(confirm('Invoice(s) will be sent individually to the email address of the billing contact on the invoice')){
		var v_heigth = 850;
		var v_width  = 750;
		var top1=(window.screen.availHeight-v_heigth)/2;
		var left1=(window.screen.availWidth-v_width)/2;
		var url = "emailInvoice.php?addrs="+valAddrs;
		remoteres = window.open(url,"emailprint","width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
		remoteres.focus();
	}
	else{
		return;	
	}

}

function emailingTemplate()
{
	/*var v_heigth =600;
	var v_width  = 950;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	var url = "emailtemplate.php";
	remoteres = window.open(url,"emailtemplate", "width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
	remoteres.focus();*/
	alert('This section is moved to Admin>>Notification Management>>Invoice Delivery Notification');
 }
 
function downloadInv(invId){
	if(invId == ''){
		alert('Please Select the Invoice');
	}else{
		var result="InvoiceDownload.php?addr="+invId;
		window.location.href = result;
	}
}

//Function for notifying users 
/*function notify_users()
{                       
	var win_height	= 200;
    var win_width	= 450;

    var win_top	= (window.screen.availHeight-win_height)/2;
    var win_left	= (window.screen.availWidth-win_width)/2;
    
    var win_url	= "notify_users.php";
    var win_param	= "width="+win_width+"px,height="+win_height+"px,resizable=yes,scrollbars=yes,left="+win_left+"px,top="+win_top+"px,status=0";

     
    $().modalBox({'html':'<div id="attribute-selector" style="margin-left:-384px !important; margin-top:-185px !important; left:50% !important; top:50% !important; position: fixed;  "><img id="preloaderW" src="/BSOS/images/preloader.gif" ><div class="scroll-area"><div class="scroll-pane"><iframe id="schCalendarView" src="'+win_url+'","'+win_param+'","width='+win_width+'px,height='+win_height+'px,statusbar=no,menubar=no,scrollbars=yes,left='+win_left+'px,top='+win_top+'px,dependent=yes" border="0" width="100%" height="300" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%;overflow:hidden;height:400px;position:relative;top:0px;left:0px;"></iframe></div></div></div>'});
		$("#modal-wrapper").addClass("JoAssignmodal-wrapper");
		$("#attribute-selector .scroll-area").css({height: 400,width: 600});
}*/

function notify_users(param)
{
	var v_width  = 675;
	var v_heigth = 496;
	var top=(window.screen.availHeight-v_heigth)/2;
	var left=(window.screen.availWidth-v_width)/2;

	var form=document.forms['sheet'];
	numAddrs = numSelected();
	valAddrs = valSelected();
	if(valAddrs == ''){
		alert('Please select atleast one invoice to notify');
		return;
	}
	var get_auids = document.getElementsByName("auids[]");
	var get_custids = document.getElementsByName("custids[]");
	var custid = '';
	for (var i=0; i < get_custids.length; i++) 
	{
		if (get_auids[i].checked == true){
			getcustid_val=get_custids[i].value;
			custid = custid+','+getcustid_val;
		}
	}
	var cust = custid.split(',');
	var id = custid.split(',');
	var result = true;
	for (var i = 1; i < cust.length; i++) {
	if (cust[i]!= id[1]) {
	    result = false;
	    break;
	}
	}
	if(result == false){
		alert('To intiate a manual notification, select invoices from the same customer');
		return;
	}
	
	//document.getElementById("chkBoxsGrd").value=valAddrs;
	//var Emp = document.getElementById('akkenSelids').value;
	var url = "notify_users.php?sno="+valAddrs;
	$().modalBox({'html':'<div id="attribute-selector"><div class="scroll-area"><div class="scroll-pane"><iframe id="schCalendarView" src="'+url+'","HrmExport","width='+v_width+'px,height='+v_heigth+'px,statusbar=no,menubar=no,scrollbars=yes,dependent=yes" border="0" width="100%" height="478" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%;overflow:hidden;height:478px;position:relative;top:0px;left:0px;"></iframe></div></div></div>'});
	$("#modal-wrapper").addClass("JoAssignmodal-wrapper");
	$("#modal-wrapper").css({left: left});
	$("#modal-wrapper").css({top: top});
	$("#attribute-selector .scroll-area").css({height: 502,width: 700});
}

function modalBoxClose()
{
	$().modalBox('close');
}

</script>

<form name="sheet" id="sheet" action=navinvoice.php method=post>
<input type=hidden name=invservicedate id="invservicedate" value="<? echo $invservicedate; ?>">
<input type=hidden name=invservicedateto id="invservicedateto" value="<? echo $invservicedateto; ?>">
<input type=hidden name=addr id=addr value="" />
<input type=hidden name=aa id=aa value="" />
<input type=hidden name=t1 id=t1 value="" />
<input type=hidden name=t2 id=t2 value="" />
<input type=hidden name=val id=val value="" />
<input type=hidden name='printinvoice' id='printinvoice' value='yes' />
<input type=hidden name=navpage id=navpage value="invoicehistory" />
<input type=hidden name=Cust_id id=Cust_id value="<? echo $client; ?>" />
<input type="hidden" name="chkBoxsGrd" id="chkBoxsGrd" value="" />
<input type="hidden" name="AllAddr" id="AllAddr" value="" />
<input type=hidden name="chkInv" id="chkInv" value="InvHistory">
<input type="hidden" name="locations" id="locations" value="<?php echo $locations;?>" />
<input type="hidden" name="depts" id="depts" value="<?php echo $depts;?>" />
<input type="hidden" name="clients" id="clients" value="<?php echo $clients;?>" />
<input type="hidden" id="akkenSelids" name="akkenListEmp" value="<?php echo $rowNewApp['notify_people']; ?>">

<div id=tque></div>
<div id=oque></div>

<div id="main">	
<td valign=top align=center class=tbldata>
<table width=100% cellpadding=0 cellspacing=0 border=0 align="center" class="ProfileNewUI defaultTopRange">
		<tr>
		<td class="titleNewPad">
		<table cellpadding=0 cellspacing=0 border=0 width=100%>
		<tr>
			<td align=left><font class=modcaption>&nbsp;Invoices&nbsp;History</font></td>
			<td align=right><font class=hfontstyle>Following Invoices are delivered to the Customers.</font></font></td>
		</tr>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		   <tr>
			<td colspan=2>
			<table cellpadding=0 cellspacing=0 border=0 width=100% class="ProfileNewUI">
			<tr>
				<td>
					<font class=afontstyle>&nbsp;Show Invoices in Location&nbsp;</font>
					<select name="invlocation" id="location" class=drpdwne onChange=updateDeptList('Yes') style="width:125px">
					<option value="" <?php echo sele($invlocation,"");?>>ALL</option>
					<?php
					$lque = "SELECT cm.serial_no, CONCAT(cm.loccode,' - ',cm.heading) 
									FROM contact_manage cm 
									LEFT JOIN department dept ON (dept.loc_id = cm.serial_no) 
									WHERE cm.serial_no IN (".$locations.") AND dept.sno !=0 
									AND dept.sno IN (".$deptAccesSno.") GROUP BY cm.serial_no ORDER BY cm.loccode";
					//$lque = "SELECT serial_no, CONCAT(loccode,' - ',heading) FROM contact_manage WHERE serial_no IN ($locations) ORDER BY loccode";
					$lres = mysql_query($lque,$db);
					while($lrow = mysql_fetch_row($lres))
						print "<option value='".$lrow[0]."' ".sele($invlocation,$lrow[0]).">".$lrow[1]."</option>";
					?>
					</select>
					<font class=afontstyle>&nbsp;in&nbsp;Department&nbsp;</font>
					<select name="invdept" id="department" class=drpdwne onChange=updateClientList('Yes') style="width:125px">
					<option value="" <?php echo sele($invdept,"");?>>ALL</option>
					<?php
					if($invlocation!="")
						$wcl=" AND Client_Accounts.loc_id=$invlocation ";

					$dque = "SELECT department.sno,department.deptname FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid LEFT JOIN department ON Client_Accounts.deptid=department.sno WHERE invoice.deliver='Yes' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND department.sno IN ($depts) $wcl GROUP BY Client_Accounts.deptid ORDER BY department.deptname";
					$dres = mysql_query($dque,$db);
					while($drow = mysql_fetch_row($dres))
						print "<option value='".$drow[0]."' ".sele($invdept,$drow[0]).">".$drow[1]."</option>";
					?>
					</select>
					<font class=afontstyle>&nbsp;for&nbsp;Customer&nbsp;</font>
					<select name=invclient id="client" class=drpdwne style="width:175px">
					<option value="all" <?php echo sele($invclient,"");?>>ALL</option>
					<?php
					$wcl1="";
					if($invlocation!="")
						$wcl1=" AND Client_Accounts.loc_id=$invlocation ";

					if($invdept!="")
						$wcl1.=" AND Client_Accounts.deptid=$invdept ";

					$cque="select distinct(invoice.client_name),staffacc_cinfo.cname,".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1)." FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid WHERE invoice.deliver='Yes' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND staffacc_cinfo.sno IN ($clients) $wcl1 AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno.") ORDER BY ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1);
					$cres=mysql_query($cque,$db);
					while($crow=mysql_fetch_row($cres))
						print "<option value='".$crow[0]."' ".sele($invclient,$crow[0]).">".$crow[2]."</option>";
					?>
					</select>
					&nbsp;<font class=afontstyle color=black>From&nbsp;<input type=text size=10  maxlength="10" name=servicedate value="<?php echo $servicedate;?>"><script language='JavaScript'>new tcal ({'formname':'sheet','controlname':'servicedate'});</script></font> <font class=afontstyle color=black>&nbsp;To&nbsp;<input type=text size=10 maxlength="10" name=servicedateto  value="<?php echo $servicedateto;?>"><script language='JavaScript'>new tcal ({'formname':'sheet','controlname':'servicedateto'});</script>&nbsp;&nbsp;<a href=javascript:DateCheck('servicedate','servicedateto')>View</a>&nbsp;</font>
				</td>
			</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td><font class=bstrip>&nbsp;</font></td>
		</tr>
		</table>
		</td>
		</tr>
	</div>
	
	<div id="topheader">
    <tr  class="NewGridTopBg">
	<?php	
		$manual_notify = 'N';
		$manual_notifyQry = "SELECT * FROM notifications_templates WHERE mod_id = 'invoice_css_deliver' AND status = 'ACTIVE'";
		$manual_notifyRes = mysql_query($manual_notifyQry,$db);
		$manual_notify_val = mysql_fetch_array($manual_notifyRes);
		$manual_notify = $manual_notify_val['manual_notify'];

		$name=explode("|","fa-wrench~Setup Email Format &nbsp;|fa-envelope-o~Email&nbsp;|fa-print~Print&nbsp;|fa-arrow-circle-up~Export|droplist".((REMOVE_INVOICE == 'Y') ? '|fa-times~Remove&nbsp;Invoices' : '').(($manual_notify == 'Y') ? '|fa-check~Invoice&nbsp;Notify' : ''));
		$link=explode("|","javascript:emailingTemplate();| javascript:emailingOptions();|javascript:printingOptions();||<a href=\"javascript:doInvoiceExport();\">CSV</a>~<a href=\"javascript:DoSaveGrid();\">PDF</a>".((REMOVE_INVOICE == 'Y') ? '|javascript:doDeleteInvoice();' : '').'|javascript:notify_users();');	
		$heading="user.gif~Invoices&nbsp;History";
		$menu->showMainGridHeadingStrip1($name,$link,$heading);
	?>
    </tr>
    </div>
    
    <div id="grid_form">
    <tr>
		<td>
		<script>
		var gridHeadCol =["<label class='container-chk'><input type=checkbox name=ck id='ck' onClick=kev(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>","Invoice. No.","<?php echo getEntityDispHeading('ID', 'Customer Name', 1); ?>","Service&nbsp;Date","Invoice&nbsp;Date","Due&nbsp;Date","Total&nbsp;Amount","Amount&nbsp;Due","Location","Department","PDF&nbsp;Orientation","Delivered By","Delivered Date","<a href=javascript:doGridSearch('search');>Search</a>&nbsp;&nbsp;<a href=javascript:clearGridSearch(),doGridSearch('reset');>Reset</a>"];
		var gridHeadData = ["","<input class=gridserbox type=text id=aw-column1 name=aw-column1>","<input class=gridserbox type=text id=aw-column2 name=aw-column2>","<input class=gridserbox type=text id=aw-column3 name=aw-column3>","<input class=gridserbox type=text id=aw-column4 name=aw-column4>","<input class=gridserbox type=text id=aw-column5 name=aw-column5>","<input class=gridserbox type=text id=aw-column6 name=aw-column6>","<input class=gridserbox type=text id=aw-column7 name=aw-column7>","<input class=gridserbox type=text id=aw-column8 name=aw-column8>","<input class=gridserbox type=text id=aw-column9 name=aw-column9>","<input class=gridserbox type=text id=aw-column10 name=aw-column10>","<input class=gridserbox type=text id=aw-column11 name=aw-column11>","<input class=gridserbox type=text id=aw-column12 name=aw-column12>","<input type=hidden id=aw-column13><a href=javascript:doGridSearch('search');><i alt='Search' class='fa fa-search fa-lg'></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=javascript:clearGridSearch(),doGridSearch('reset');><i alt='Reset' class='fa fa-reply fa-lg'></i></a>"];
		var gridActCol = ["","","","","","","","","","","","","",""];
		var gridActData = [];
		var gridValue = "ACC_Invoicehistory";
		gridForm=document.forms[0];
		gridSearchResetColumn="13|";
		initGrids(14);
		gridExtraFields=new Array();
		gridExtraFields['invclient']="<? echo  $invclient; ?>";
		gridExtraFields['invlocation']="<? echo  $invlocation; ?>";
		gridExtraFields['invdept']="<? echo  $invdept; ?>";
		gridExtraFields['servicedate']="<? echo  $servicedate; ?>";
		gridExtraFields['servicedateto']="<? echo  $servicedateto; ?>";
		xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);	
		</script>
	    </td>
    </tr>
    </div>

    <div id="botheader">
    <tr class="NewGridBotBg">
	<?php
		// $name=explode("|","fa-wrench~Setup Email Format &nbsp;|fa-envelope-o~Email&nbsp;|fa-print~Print&nbsp;|fa-arrow-circle-up~Export|droplist|fa-times~Remove&nbsp;Invoices");
		// $link=explode("|","javascript:emailingTemplate();|javascript:emailingOptions();|javascript:printingOptions();||<a href=\"javascript:doInvoiceExport();\">CSV</a>~<a href=\"javascript:DoSaveGrid();\">PDF</a>|javascript:doDeleteInvoice();");
		// $heading="user.gif~Invoices&nbsp;History";
		// $menu->showMainGridHeadingStrip1($name,$link,$heading);
	?>
    </tr>
    </div>
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
<script language="JavaScript" src="scripts/mm_menu.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_nbGroup(event, grpName) { //v6.0
  var i,img,nbArr,args=MM_nbGroup.arguments;
  if (event == "init" && args.length > 2) {
    if ((img = MM_findObj(args[2])) != null && !img.MM_init) {
      img.MM_init = true; img.MM_up = args[3]; img.MM_dn = img.src;
      if ((nbArr = document[grpName]) == null) nbArr = document[grpName] = new Array();
      nbArr[nbArr.length] = img;
      for (i=4; i < args.length-1; i+=2) if ((img = MM_findObj(args[i])) != null) {
        if (!img.MM_up) img.MM_up = img.src;
        img.src = img.MM_dn = args[i+1];
        nbArr[nbArr.length] = img;
    } }
  } else if (event == "over") {
    document.MM_nbOver = nbArr = new Array();
    for (i=1; i < args.length-1; i+=3) if ((img = MM_findObj(args[i])) != null) {
      if (!img.MM_up) img.MM_up = img.src;
      img.src = (img.MM_dn && args[i+2]) ? args[i+2] : ((args[i+1])? args[i+1] : img.MM_up);
      nbArr[nbArr.length] = img;
    }
  } else if (event == "out" ) {
    for (i=0; i < document.MM_nbOver.length; i++) {

      img = document.MM_nbOver[i]; img.src = (img.MM_dn) ? img.MM_dn : img.MM_up; }
  } else if (event == "down") {
    nbArr = document[grpName];
    if (nbArr)
      for (i=0; i < nbArr.length; i++) { img=nbArr[i]; img.src = img.MM_up; img.MM_dn = 0; }
    document[grpName] = nbArr = new Array();
    for (i=2; i < args.length-1; i+=2) if ((img = MM_findObj(args[i])) != null) {
      if (!img.MM_up) img.MM_up = img.src;
      img.src = img.MM_dn = (args[i+1])? args[i+1] : img.MM_up;
      nbArr[nbArr.length] = img;
  } }
}

	window.mm_menu_0515130056_1 = new Menu("root",100,19,"Verdana, Arial, Helvetica, sans-serif",10,"#000000","#000000","#EFEFEF","#CCCCCC","left","middle",3,0,300,-5,100,true,false,true,1,true,true);
	
	mm_menu_0515130056_1.addMenuItem("<b>CSV</b>","javascript:doInvoiceExport();");
	mm_menu_0515130056_1.addMenuItem("<b>PDF</b>","javascript:DoSaveGrid();");
	

	mm_menu_0515130056_1.fontWeight="bold";
	mm_menu_0515130056_1.hideOnMouseOut=true;
	mm_menu_0515130056_1.bgColor='#555555';
	mm_menu_0515130056_1.menuBorder=1;
	mm_menu_0515130056_1.menuLiteBgColor='#FFFFFF';
	mm_menu_0515130056_1.menuBorderBgColor='#777777';
	mm_menu_0515130056_1.writeMenus();
//-->
</script>

</body>
</html>