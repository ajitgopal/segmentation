<?php
	require("global.inc");
	require("Menu.inc");
	$menu=new EmpMenu();
	require("accountwidgets.php");

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	if($invclient=="")
		$invclient = "all";

	$clients=$locations=$depts=0;

	$que="select invoice.client_name FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' WHERE invoice.deliver='no' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN (".$deptAccesSno.")  GROUP BY invoice.client_name";
	$res=mysql_query($que,$db);
	$bpay=mysql_num_rows($res);
	while($dd=mysql_fetch_row($res))
		$clients = ($clients==0) ? $dd[0] : $clients.",".$dd[0];

	$que="select Client_Accounts.loc_id FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' WHERE invoice.deliver='no' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN (".$deptAccesSno.") GROUP BY Client_Accounts.loc_id";
	$res=mysql_query($que,$db);
	while($dd=mysql_fetch_row($res))
		$locations = ($locations==0) ? $dd[0] : $locations.",".$dd[0];

	$que="select Client_Accounts.deptid FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' WHERE invoice.deliver='no' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN (".$deptAccesSno.") GROUP BY Client_Accounts.deptid";
	$res=mysql_query($que,$db);
	while($dd=mysql_fetch_row($res))
		$depts = ($depts==0) ? $dd[0] : $depts.",".$dd[0];

	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

	if(!isset($val))
	{
        if($bpay>0)
        {
			//Query modified by vijaya to fix the bug in from date and to dates in delivery invoice eit page.
       	    $qu="SELECT DATE_FORMAT(MIN(STR_TO_DATE(invoice_date, '%m/%d/%Y')),'%m/%d/%Y'), 
			DATE_FORMAT(MAX(STR_TO_DATE( invoice_date, '%m/%d/%Y')),'%m/%d/%Y') 
			FROM invoice WHERE invoice.deliver = 'no' AND invoice.status = 'ACTIVE' ";
            $res=mysql_query($qu,$db);
            $dd=mysql_fetch_row($res);

            $servicedateto=$dd[1];
            $servicedate=$dd[0];
        }
        else
        {
            $thisday2=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
            $servicedateto=date("m/d/Y",$thisday2);
            $thisday1=mktime(date("H"),date("i"),date("s"),date("m"),date("d")-15,date("Y"));
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

    $menu->showHeader("accounting","Deliver Invoices","4|2");
?>
<script>
	// Grid sorting function
	var orig_function = window.headerClicked;
	window.headerClicked = function(e)
	{
	
		var botonfunc = e.srcElement.id;
		var posbotonfunc = botonfunc.indexOf(":" ,12);
		var next_posbotonfunc = botonfunc.indexOf("/" ,12);
		var numbotonfunc = botonfunc.substring(posbotonfunc+1, next_posbotonfunc);//botonfunc.charAt(posbotonfunc+1);
		var lastcolord = obj2.getSortProperty("index");
		var lastcoldir = obj2.getSortProperty("direction");
		//txtValHolder = retainTextBoxValues(); //To Retrieve Search Txt Boxes Values
		var filtervalues = [];
		filtervalues = getfilterValues();

		if(numbotonfunc=="0" || numbotonfunc=="")
		{
			return;
		}
		else
		{
			if(lastcolord == numbotonfunc)
			{
				if(lastcoldir == "ascending")
				{
					obj1.sort(numbotonfunc, "descending");
					obj2.sort(numbotonfunc, "descending");
				}
				else
				{
					obj1.sort(numbotonfunc, "ascending");
					obj2.sort(numbotonfunc, "ascending");
				}
			}
			else
			{
				obj1.sort(numbotonfunc, "ascending");
				obj2.sort(numbotonfunc, "ascending");
			}
			obj1.refresh();
			obj2.refresh();
			resizeGridColumns();
			remove_spaces();
		}
		//reassignTxtBoxValues(txtValHolder); // To Assign the same values to Search Txt Boxes
		setfilterValues(filtervalues);
	}
	// Resize grid when pages are changed
	var page_orig_function = window.goToPage;
	window.goToPage = function(delta)
	{
		var count = obj2.getProperty("row/pageCount");
		var number = obj2.getProperty("row/pageNumber");
		var record = obj2.getProperty("row/pageSize");
		var nh=0;
		var filtervalues = [];
		filtervalues = getfilterValues();   // To Assign the same values to Search Txt Boxes
		 
		number += delta;

		if (number < 0)
			number = 0

		if (number > count-1) 
			number = count-1

		if(count>0)
		{
			if((number+1)==count)
				document.getElementById('recordLabel').innerHTML = "Showing records " + ((number*record) + 1) + " to " + actdata.length + " of " + actdata.length + " ";
			else
				document.getElementById('recordLabel').innerHTML = "Showing records " + ((number*record) + 1) + " to " + ((number+1)*record) + " of " + actdata.length + " ";
			document.getElementById('pageLabel').innerHTML = "Page " + (number + 1) + " of " + count + " ";
			obj2.setProperty("row/pageNumber", number);
		}
		else
		{
			document.getElementById('recordLabel').innerHTML = "Showing records 0 to 0 of 0";
			document.getElementById('pageLabel').innerHTML = "Page " + (number + 1) + " of " + count + " ";
			nh=1;
		}
		obj2.refresh();
		resizeGridColumns();
		if(nh==1)
		{ 	
			//obj1.refresh();
			//obj2.refresh();
			resizeGridColumns();
			//remove_spaces(); 
			setfilterValues(filtervalues);
		}   
		//Modified code for deselecting the top check box of the grid(where ever we have the grid except email,CRM->contcats and Collaboration->Addressbook pages its deselect)
		
		if(typeof document.forms=="object")
		{
			var totCount=document.forms.length;
			for(var j=0;j<totCount;j++)
			{
				if(typeof document.forms[j]=="object")
				{
					if(typeof document.forms[j].chk=="object")
					   document.forms[j].chk.checked = false;
					   
					if(typeof document.forms[j].ck=="object")
					   document.forms[j].ck.checked = false;

					if(typeof document.forms[j].chk1=="object")
						document.forms[j].chk1.checked = false;
				}
			}
		}
	}
	// Grid columns resize
	function resizeGridColumns() 
	{
		var count = obj1.getColumnProperty("count");
		for (var j=0; j<count; j++)
		{
			cWidth = obj1.getTemplate("top/item", j).element().offsetWidth;
			var active_class = "active-column-"+j;
			var column_array = document.getElementsByClassName(active_class);
			for(var k=0; k<column_array.length; k++)
			{ 
				column_array[k].style.width = cWidth+"px";
			} 
		}
	} 
	// This function is for search textbox issue in chrome browser only
	function remove_spaces()
	{
		var targetDiv = document.getElementById("grid1.data.item:0").getElementsByClassName("active-row-cell");
		for(var m=0; m<targetDiv.length; m++)
		{ 
			var foo = targetDiv[m].innerHTML;
			foo = foo.replace(/&nbsp;/g, '');
			targetDiv[m].innerHTML = foo;
		} 
	}
	// get filter values
	function getfilterValues()
	{
		var temp_array = [];
		var count = obj1.getColumnProperty("count");
		for (var n=0; n<count-1; n++)
		{
			var tempvar = "column"+n;
			var tempvar2 = document.getElementsByName(tempvar)[0].value;
			temp_array.push(tempvar2);
		}
		return temp_array;
	}
	// set filter values
	function setfilterValues(input)
	{
		for(var p=0; p<input.length; p++)
		{
			var tempvar3 = "column"+p;
			document.getElementsByName(tempvar3)[0].value = input[p];
		}
	}
</script>
<script language="javascript">
function doRead(src)
{
	rowid=src.getProperty("item/index");
	result=actdata[rowid][11];
    //window.location.href=result;
	var v_heigth = 700;
	var remoteres;
    var v_width  = 1050;

	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	remoteres = window.open(result,"","width="+v_width+"px,height="+v_heigth+"px,resizable=yes,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px");
	remoteres.focus();	

}
function doReadIPad(src)
{
	gridRowItem=obj2.getProperty("selection/index");
	gridRowId=src.getProperty("item/index");

	if(gridRowItem!=gridRowId)
		obj2.setProperty("selection/index",gridRowId);
	else
		doRead(src);
}

</script>
<style>
.active-column-0 {width: 30px !important;}
.active-column-1 {width: 50px;}
.active-column-2 {width: 100px;}
.active-column-3 {width: 200px;}
.active-column-4 {width: 100px;}
.active-column-5 {width: 100px;}
.active-column-6 {width: 100px; text-align: left;}
.active-column-7 {width: 100px;}
.active-column-8 {width: 150px;}
.active-column-9 {width: 100px;}
.active-column-10 {width: 100px;}
.alert-cntrbtns {
	margin: 0 auto !important;
	width: 75px !important;
}
@media screen and (-webkit-min-device-pixel-ratio:0) {
.checkmark{left: -2px;}
#grid1 .active-scroll-top{padding-left: 5px !important;}
}
</style>
<script>
function initGrid1()
{
	obj1.setRowText("Search");
	obj1.setRowHeaderWidth("0px");
	obj1.setRowProperty("count", 1);
	obj1.setColumnProperty("count", 11);
	obj1.setDataProperty("text", function(i, j){return headdata[i][j]});
	obj1.setColumnProperty("text", function(i){return headcol[i]});
	obj1.styleSheet=document.styleSheets[document.styleSheets.length-1];
	//obj1.styleSheet.addRule("#grid1 .active-box-resize", "display: none;");
	obj1.setAction("selectRow", null);
	obj1.setEvent("onkeydown", null);
	obj1.getTemplate("top/item").setEvent("onmousedown", headerClicked);
	obj1.getTemplate("top/item").setEvent("onmouseup", "on_mouse_up(this);");
}

function initGrid2()
{
	obj2.setColumnProperty("count",11);
	obj2.setColumnProperty("text",function(i){return actcol[i]});
	obj2.setColumnHeaderHeight("1px");
	obj2.setRowHeaderWidth("0px");
	obj2.setModel("row", new Active.Rows.Page);
	obj2.setProperty("row/count", actdata.length);
	obj2.setProperty("row/pageSize", 20);
	obj2.setProperty("data/text", function(i,j){return actdata[i][j]});
	obj2.setProperty("selection/multiple", true);
	var row = new Active.Templates.Row;
	//row.setEvent("onmouseover", "mouseover(this, 'active-row-highlight')");
	//row.setEvent("onmouseout", "mouseout(this, 'active-row-highlight')");
	row.setStyle("background", alternate);
	obj2.setTemplate("row", row);

	var column0 = new Active.Templates.Text;
	column0.setStyle('width', '30px');
	obj2.setTemplate('column', column0, 0);

	var column1 = new Active.Templates.Text;
	column1.setStyle('width', '50px');
	obj2.setTemplate('column', column1, 1);

	var column2 = new Active.Templates.Text;
    column2.setStyle('width', '100px');
	obj2.setTemplate('column', column2, 2);
	
	var column3 = new Active.Templates.Text;
    column3.setStyle('width', '200px');
	obj2.setTemplate('column', column3, 3);

	var column4 = new Active.Templates.Text;
    column4.setStyle('width', '100px');
	obj2.setTemplate('column', column4, 4);

	var column5 = new Active.Templates.Text;
    column5.setStyle('width', '100px');
	obj2.setTemplate('column', column5, 5);

	var column6 = new Active.Templates.Text;
	column6.setStyle('width', '100px');
	column6.setStyle('text-align', 'right');
	obj2.setTemplate('column', column6, 6);
	
	var column7 = new Active.Templates.Text;
	column7.setStyle('width', '100px');
	obj2.setTemplate('column', column7, 7);

	var column8 = new Active.Templates.Text;
	column8.setStyle('width', '150px');
	obj2.setTemplate('column', column8, 8);

	var column9 = new Active.Templates.Text;
	column9.setStyle('width', '100px');
	obj2.setTemplate('column', column9, 9);
	
	var column10 = new Active.Templates.Text;
	column10.setStyle('width', '100px');
	obj2.setTemplate('column', column10, 9);

	var column11 = new Active.Templates.Text;
	obj2.setTemplate('column', column11, 10);
	
	row.setEvent("ondblclick", function(){this.action("readMes")});
	obj2.setTemplate("row", row);
	obj2.setAction("readMes", doRead);
	userAgent = navigator.userAgent.toLowerCase();
	if(userAgent.match(/iPhone/i) || userAgent.match(/iPod/i) || userAgent.match(/iPad/i) || userAgent.match(/mobile/i) || userAgent.match(/android/i)) 
	{
		obj2.getRowTemplate("row").getItemTemplate(1).setEvent("onclick", function(){this.action("readMesIPad")});
		obj2.getRowTemplate("row").getItemTemplate(2).setEvent("onclick", function(){this.action("readMesIPad")});
		obj2.getRowTemplate("row").getItemTemplate(3).setEvent("onclick", function(){this.action("readMesIPad")});
		obj2.setAction("readMesIPad", doReadIPad);
	}
}
</script>
<link href="/BSOS/css/resize.css" rel="stylesheet" type="text/css">
<script src="/BSOS/scripts/resize.js"></script>  
<script language="javascript" src="/BSOS/scripts/common_ajax.js"></script>
<script language="javascript" src="scripts/validateinhis.js"></script>
<script src="/BSOS/scripts/date_format.js" language="javascript"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language="JavaScript" src="scripts/mm_menu.js"></script>
<script src="/BSOS/scripts/calendar.js" language="javascript"></script>
<link href="/BSOS/css/calendar.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="/BSOS/css/popup_styles.css" media="screen" type="text/css">
<link href="/BSOS/css/NewUiGrid.css" rel="stylesheet" type="text/css">
<style>
	.active-box-normal{ border-bottom:none; }
	#grid1 .active-column-0 .active-box-resize{ display: none !important; }
	#grid1 .active-scroll-data{ height: 90px !important; }
	#grid1 .active-column-0{ border-bottom: none !important; width: 30px !important;}
	#grid1 .active-scroll-top{ width: 100% !important; }
	#grid1\.data\.item\:0 .active-column-0{ border: none !important;} 
	#grid1\.top\.item\:0 { height: 99%; }
	#grid2 .active-scroll-data, #grid2 .active-scroll-bars { overflow: hidden !important; }
	#grid2 .active-box-item { padding: 1px;}
	.active-box-item{border-style: none;}
	#grid1 .active-header-over .active-box-item{border: none !important;}

</style>
<form name="sheet" id="sheet" action="navinvoice.php" method="post">
  <input type=hidden name=invservicedate id="invservicedate" value="<? echo $invservicedate; ?>">
  <input type=hidden name=invservicedateto id="invservicedateto" value="<? echo $invservicedateto; ?>">
  <input type=hidden name=addr id="addr" value="" />
  <input type=hidden name=aa id="aa" value="" />
  <input type=hidden name=t1 id="t1" value="" />
  <input type=hidden name=t2 id="t2" value="" />
  <input type=hidden name=val id="val" value="" />
  <input type=hidden name=navpage id="navpage" value="invoicedeliver" />
  <input type="hidden" name="chkBoxsGrd" id="chkBoxsGrd" value="" />
  <input type="hidden" name="AllAddr" id="AllAddr" value="" />
  <input type="hidden" name="locations" id="locations" value="<?php echo $locations;?>" />
  <input type="hidden" name="depts" id="depts" value="<?php echo $depts;?>" />
  <input type="hidden" name="clients" id="clients" value="<?php echo $clients;?>" />
  <div id="main">
    <td valign=top align=center class=tbldata><table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI defaultTopRange">
        <div id="content">
          <tr>
            <td class="titleNewPad"><table cellpadding=0 cellspacing=0 border=0 width=100%>
                <tr>
                  <td align=left><font class=modcaption>&nbsp;Deliver&nbsp;Invoices</font></td>
                  <td align=right><font class=hfontstyle>Following invoices are created and ready to deliver to the Customers.</font></td>
                </tr>
                <tr>
                  <td colspan=2><font class=bstrip>&nbsp;</font></td>
                </tr>
                <tr>
                  <td colspan=2><table cellpadding=0 cellspacing=0 border=0 width=100%>
                      <tr>
                        <td><font class=afontstyle>&nbsp;Show Invoices in Location&nbsp;</font>
                          <select name="invlocation" id="location" class=drpdwne onChange=updateDeptList('no') style="width:125px">
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
                          <select name="invdept" id="department" class=drpdwne onChange=updateClientList('no') style="width:125px">
                            <option value="" <?php echo sele($invdept,"");?>>ALL</option>
                            <?php
					if($invlocation!="")
						$wcl=" AND Client_Accounts.loc_id=$invlocation ";

					$dque = "SELECT department.sno,department.deptname FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid LEFT JOIN department ON Client_Accounts.deptid=department.sno WHERE invoice.deliver='no' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND department.sno IN ($depts) $wcl GROUP BY Client_Accounts.deptid ORDER BY department.deptname";
					$dres = mysql_query($dque,$db);
					while($drow = mysql_fetch_row($dres))
						print "<option value='".$drow[0]."' ".sele($invdept,$drow[0]).">".$drow[1]."</option>";
					?>
                          </select>
                          <font class=afontstyle>&nbsp;for&nbsp;Customer&nbsp;</font>
                          <select name=invclient id="client" class=drpdwne style="width:175px">
                            <option value="" <?php echo sele($invclient,"");?>>ALL</option>
                            <?php
					$wcl1="";
					if($invlocation!="")
						$wcl1=" AND Client_Accounts.loc_id=$invlocation ";

					if($invdept!="")
						$wcl1.=" AND Client_Accounts.deptid=$invdept ";

					$cque="select distinct(invoice.client_name),staffacc_cinfo.cname,".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1)." FROM invoice LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid WHERE invoice.deliver='no' AND invoice.status = 'ACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND staffacc_cinfo.sno IN ($clients) $wcl1 ORDER BY ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1);
					$cres=mysql_query($cque,$db);
					while($crow=mysql_fetch_row($cres))
						print "<option value='".$crow[0]."' ".sele($invclient,$crow[0]).">".stripslashes($crow[2])."</option>";
					?>
                          </select>
                          &nbsp;<font class=afontstyle color=black>From&nbsp;
                          <input  type=text size=10  maxlength="10" name=servicedate value="<?php echo $servicedate;?>">
                          <script language='JavaScript'>new tcal ({'formname':'sheet','controlname':'servicedate'});</script></font><font class=afontstyle color=black>&nbsp;To&nbsp;
                          <input  type=text size=10 maxlength="10" name=servicedateto  value="<?php echo $servicedateto;?>">
                          <script language='JavaScript'>new tcal ({'formname':'sheet','controlname':'servicedateto'});</script>&nbsp;&nbsp;<a href=javascript:DateCheck('servicedate','servicedateto')>View</a>&nbsp;</font> </td>
                      </tr>
                    </table></td>
                </tr>
              </table></td>
          </tr>
          <tr>
            <td><font class=bstrip>&nbsp;</font></td>
          </tr>
        </div>
        <div id="topheader">
          <tr class="NewGridTopBg">
            <?php
				$name=explode("|","fa-print~Print&nbsp;|fa-arrow-circle-up~Export|droplist|fa-check~Deliver&nbsp;Invoices".((REMOVE_INVOICE == 'Y') ? '|fa-times~Remove&nbsp;Invoices' : ''));//print.gif~Print&nbsp;|
				$link=explode("|","javascript:DoPrintGrid();||<a href=\"javascript:doInvoiceExport();\">CSV</a>~<a href=\"javascript:DoSaveGrid();\">PDF</a>|javascript:doDeliver();".((REMOVE_INVOICE == 'Y') ? '|javascript:doDeleteInvoice();' : ''));	//javascript:DoPrintGrid();|
				$heading="user.gif~Deliver&nbsp;Invoices";
				$menu->showMainGridHeadingStrip1($name,$link,$heading);
			?>
          </tr>
        </div>
        <div id="grid_form">
          <tr>
            <td><?php
		$headers="<label class='container-chk'><input type=checkbox name=ck value=1 onClick=kev(this) tabindex=3><span class='checkmark'></span></label>|# Inv|Cust ID|Customer Name|InvoiceDate|DueDate|Balance|DeliveryMethod|Location|Department|PDF Orientation";
		$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>";
		echo headerGrid($headers,$sertypes);
		if($invclient=="all")
			$query="select invoice.sno,invoice.client_name,invoice.invoice_date,invoice.due_date,invoice.deposit,invoice.deliver,invoice.billed,invoice.total,invoice.discount,invoice.tax,invoice.invoice_number,invoice.templateid,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,invoice.pdf_orientation FROM invoice LEFT JOIN Client_Accounts ON Client_Accounts.typeid=invoice.client_name AND Client_Accounts.status='active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where str_to_date(invoice.invoice_date,'%m/%d/%Y') >= str_to_date('".$servicedate."','%m/%d/%Y') and str_to_date(invoice.invoice_date,'%m/%d/%Y') <= str_to_date('".$servicedateto."','%m/%d/%Y') and invoice.deliver='no' AND invoice.status = 'ACTIVE' ".$wcl1."  AND Client_Accounts.clienttype IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN (".$deptAccesSno.") GROUP BY invoice.sno order by str_to_date(invoice.invoice_date,'%m/%d/%Y') desc,invoice.stime desc";
		else
			$query="select invoice.sno,invoice.client_name,invoice.invoice_date,invoice.due_date,invoice.deposit,invoice.deliver,invoice.billed,invoice.total,invoice.discount,invoice.tax,invoice.invoice_number,invoice.templateid,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,invoice.pdf_orientation FROM invoice LEFT JOIN Client_Accounts ON Client_Accounts.typeid=invoice.client_name AND Client_Accounts.status='active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where str_to_date(invoice.invoice_date,'%m/%d/%Y') >= str_to_date('".$servicedate."','%m/%d/%Y') and str_to_date(invoice.invoice_date,'%m/%d/%Y') <= str_to_date('".$servicedateto."','%m/%d/%Y') and invoice.deliver='no' AND invoice.status = 'ACTIVE' ".$wcl1." AND invoice.client_name='".$invclient."'  AND Client_Accounts.clienttype IN ('CUST','BOTH') AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN (".$deptAccesSno.") GROUP BY invoice.sno order by str_to_date(invoice.invoice_date,'%m/%d/%Y') desc,invoice.stime desc";
		$data=mysql_query($query,$db);
		echo DisplayInvoice($data,$db);
		?>
              <script>
        var obj1 = new Active.Controls.Grid;
        obj1.setId("grid1");
        initGrid1();
        document.write(obj1);

        var obj2=new Active.Controls.Grid;
        obj2.setId("grid2");
        initGrid2();
        document.write(obj2);

        initSers();
        gridPage=showGridPage();
        document.write(gridPage);
        </script>
            </td>
          </tr>
        </div>
        <div id="botheader">
          <tr class="NewGridBotBg">
            <?php
        
		$name=explode("|","fa-print~Print&nbsp;|fa-arrow-circle-up~Export|droplist|fa-check~Deliver&nbsp;Invoices|fa-times~Remove&nbsp;Invoices");//print.gif~Print&nbsp;|
		$link=explode("|","javascript:DoPrintGrid();||<a href=\"javascript:doInvoiceExport();\">CSV</a>~<a href=\"javascript:DoSaveGrid();\">PDF</a>|javascript:doDeliver();|javascript:doDeleteInvoice();");//javascript:DoPrintGrid();|
		$heading="user.gif~Deliver&nbsp;Invoices";
		//$menu->showMainGridHeadingStrip1($name,$link,$heading);
	?>
          </tr>
        </div>
      </table></td>
  </div>
  <?php
	$menu->showFooter();
?>
</form>
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
<script>
//Removing the &nbsp; in the chrome browser when onloading the page- Email Collaboration grid
$(document).ready(function() { 
    $('#grid1').html(function(i,h){	
    	return h.replace(/&nbsp;/g,'');
    });
	$(window).scrollTop(0);	
});
</script>

<script> 
	function on_mouse_up(element)
	{
		var div_id = element.id;
		var div_width = document.getElementById(div_id).offsetWidth;
		
		var div_id_array = div_id.split(":");
		var selected_column = div_id_array[1].trim();
		var active_class = "active-column-"+selected_column;
		
		var column_array = document.getElementsByClassName(active_class);
		for(var i=0; i<column_array.length; i++)
		{ 
			column_array[i].style.width = div_width+"px";
		} 
	}
</script>

</body>
</html>