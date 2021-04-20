<?php
	require_once("global.inc"); 
	require_once("accountwidgets.php");
	require_once("GetInvoiceData.php"); 	

	require_once("invoiceFunctions.php");
	require_once($akken_psos_include_path.'getInvoiceBillToAddr.php');

	require_once("Menu.inc");
	$menu=new EmpMenu();
        header("Cache-Control: max-age=300, must-revalidate");
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$sep = "";	
	$sep1 = "";
	$sep2 = "";

	$getTemplateId = "SELECT invtmp_sno FROM Invoice_Template WHERE invtmp_status = 'ACTIVE' ";
	$resTemplateId = mysql_query($getTemplateId,$db);
	while($rowTemplateId = mysql_fetch_row($resTemplateId))
	{
		$tpl_array_values = genericTemplate($rowTemplateId[0]);
		$template_Time=$tpl_array_values[4];

		foreach($template_Time as $key=> $values)
		{
			if($key == "Time")
			{
				if($values[0] == 'N')
				{
					$invId_Time .= $sep."".$rowTemplateId[0];
					$sep = ",";
				}
			}
		}

		$template_Expense=$tpl_array_values[5];

		foreach($template_Expense as $key=> $values)
		{
			if($key == "Expense")
			{
				if($values[0] == 'N')
				{
					$invId_Expense .= $sep1."".$rowTemplateId[0];
					$sep1 = ",";
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Charges")
			{
				if($values[0] == 'N')
				{
					$invId_Charge .= $sep2."".$rowTemplateId[0];
					$sep2 = ",";
				}
			}
		}
	}

	$invId_Time = str_replace(",","','",$invId_Time);
	$invId_Expense = str_replace(",","','",$invId_Expense);
	$invId_Charge = str_replace(",","','",$invId_Charge);

	$sep = "";
	$getCompId_time = "SELECT sno FROM staffacc_cinfo WHERE templateid IN ('".$invId_Time."') AND templateid NOT IN (0) AND type IN ('CUST','BOTH')";
	$resCompId_time = mysql_query($getCompId_time,$db);
	while($rowCompId_time = mysql_fetch_row($resCompId_time))
	{
		$Time_sno .= $sep."".$rowCompId_time[0];
		$sep = ',';
	}

	$sep = "";
	$getCompId_time = "SELECT sno FROM staffacc_cinfo WHERE templateid IN ('".$invId_Expense."') AND templateid NOT IN (0) AND type IN ('CUST','BOTH')";
	$resCompId_time = mysql_query($getCompId_time,$db);
	while($rowCompId_time = mysql_fetch_row($resCompId_time))
	{
		$Exp_sno .= $sep."".$rowCompId_time[0];
		$sep = ',';
	}

	$sep = "";
	$getCompId_time = "SELECT sno FROM staffacc_cinfo WHERE templateid IN ('".$invId_Charge."') AND templateid NOT IN (0) AND type IN ('CUST','BOTH')";
	$resCompId_time = mysql_query($getCompId_time,$db);
	while($rowCompId_time = mysql_fetch_row($resCompId_time))
	{
		$Charge_sno .= $sep."".$rowCompId_time[0];
		$sep = ',';
	}

	$Time_sno = str_replace(",","','",$Time_sno);
	$Exp_sno = str_replace(",","','",$Exp_sno);
	$Charge_sno = str_replace(",","','",$Charge_sno);

	if($Time_sno != '')
		$template_Time_Check = " AND ts.client NOT IN ('".$Time_sno."')";
	if($Exp_sno != '')
		$template_Expense_Check = " AND exp.client NOT IN ('".$Exp_sno."')";
	if($Charge_sno != '')
		$template_Charge_Check = " AND hrcon_jobs.client NOT IN ('".$Charge_sno."')";

	if($selClient !='0' && $selClient != "")
	{
		$clientCond = "AND timesheet.client IN (".$selClient.")";
		$empCond = "AND timesheet.username IN ('".str_replace(",","','",$selClient)."')";
		$dislpayforClient = "style='display:none'";
	}
	$billCondboth ='';
	if($selAddr!='' && $selClient!=''){
		$conassgid = ",GROUP_CONCAT(timesheet.assid)";
		$attention = ",hrcon_jobs.attention";
		$billCondboth = " and hrcon_jobs.bill_address='".$selAddr."' and hrcon_jobs.bill_contact='".$selClient."'";
	}
	if(!isset($invtype))
	   $invtype="Customer";

	if($change!="yes")
	{
		if($val=="")
		{
			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$cdate=date("m/d/Y",$thisday);
			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d")+15,date("Y"));
			$duedate=date("m/d/Y",$thisday);

			$qu="select MIN(par.sdate),MAX(par.edate) from par_timesheet par LEFT JOIN timesheet_hours ts on(par.sno=ts.parid) where  par.astatus IN ('ER','Approved') AND ts.status = 'Approved' and ts.type!='EARN' and ts.billable='Yes' AND client != '0'" ;
			$res=mysql_query($qu,$db);
			$dd=mysql_fetch_row($res);
			mysql_free_result($res);

			$qu1="select MIN(par.sdate),MAX(par.edate) from par_expense par LEFT JOIN expense exp on par.sno=exp.parid where par.astatus IN ('Approved','ER') and exp.status = 'Approved' and exp.billable='bil' AND client != '0' ";
			$res1=mysql_query($qu1,$db);
			$dd1=mysql_fetch_row($res1);
			mysql_free_result($res1);

			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);

			$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
			$resdirect = mysql_query($quedirect,$db);
			$rowdirect = mysql_fetch_row($resdirect);
			$snodirect = $rowdirect[0];

	 		$qu2="select MIN(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0','".$todaydate."', str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )), MAX(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0','".$todaydate."', str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )) from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND client != '0'";
			$res2=mysql_query($qu2,$db);
			$dd2=mysql_fetch_row($res2);
			mysql_free_result($res2);

			if(($dd[0]!=NULL) && ($dd1[0]!=NULL) && ($dd2[0]!=NULL))
			{
				if($dd[0]<=$dd1[0] && $dd[0]<=$dd2[0])
					$servicedate=date("m/d/Y",strtotime($dd[0]));
				else if($dd1[0]<=$dd[0] && $dd1[0]<=$dd2[0])
					$servicedate=date("m/d/Y",strtotime($dd1[0]));
				else 
					$servicedate=date("m/d/Y",strtotime($dd2[0]));

				if($dd[1]>=$dd1[1] && $dd[1]>=$dd2[1])
					$servicedateto=date("m/d/Y",strtotime($dd[1]));
				else if($dd1[1]>=$dd[1] && $dd1[1]>=$dd2[1])
					$servicedateto=date("m/d/Y",strtotime($dd1[1]));
				else 
					$servicedateto=date("m/d/Y",strtotime($dd2[1]));
			}
			else if(($dd[0]!=NULL) && ($dd1[0]!=NULL))
			{
				if($dd[0]<$dd1[0])
					$servicedate=date("m/d/Y",strtotime($dd[0]));
				else 
					$servicedate=date("m/d/Y",strtotime($dd1[0]));

				if($dd[1]>$dd1[1])
					$servicedateto=date("m/d/Y",strtotime($dd[1]));
				else 
					$servicedateto=date("m/d/Y",strtotime($dd1[1]));
			}
			else if(($dd[0]!=NULL) && ($dd2[0]!=NULL))
			{			
				if($dd[0]<$dd2[0])
					$servicedate=date("m/d/Y",strtotime($dd[0]));
				else 
					$servicedate=date("m/d/Y",strtotime($dd2[0]));

				if($dd[1]>$dd2[1])
					$servicedateto=date("m/d/Y",strtotime($dd[1]));
				else 
					$servicedateto=date("m/d/Y",strtotime($dd2[1]));
			}
			else if(($dd1[0]!=NULL) && ($dd2[0]!=NULL))
			{
				if($dd1[0]<$dd2[0])
					$servicedate=date("m/d/Y",strtotime($dd1[0]));
				else 
					$servicedate=date("m/d/Y",strtotime($dd2[0]));

				if($dd1[1]>$dd2[1])
					$servicedateto=date("m/d/Y",strtotime($dd1[1]));
				else 
					$servicedateto=date("m/d/Y",strtotime($dd2[1]));
			}
			else if($dd[0] != NULL)
			{				
				$servicedate=date("m/d/Y",strtotime($dd[0]));
				$servicedateto=date("m/d/Y",strtotime($dd[1]));
			}
			else if($dd1[0] != NULL)
			{				
				$servicedate=date("m/d/Y",strtotime($dd1[0]));
				$servicedateto=date("m/d/Y",strtotime($dd1[1]));
			}		
			else if($dd2[0] != NULL)
			{				
				$servicedate=date("m/d/Y",strtotime($dd2[0]));
				$servicedateto=date("m/d/Y",strtotime($dd2[1]));
			}		
			else
			{
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d")-30,date("Y"));
				$servicedate=date("m/d/Y",$thisday);
				$thisday4=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$servicedateto=date("m/d/Y",$thisday4);
			}

			$invservicedate=$servicedate;
			$invservicedateto=$servicedateto;			
		}
		else
		{
			if($val=="in")
			{
				$cdate=date("m/d/Y",$t1);
				$duedate=$t2;
				$servicedate=$t3;
				$servicedateto=$t4;
			}
			else if($val=="due")
			{
				$duedate=date("m/d/Y",$t1);
				$cdate=$t2;
				$servicedate=$t3;
				$servicedateto=$t4;
			}
			else if($val=="serv")
			{
				$servicedate=date("m/d/Y",$t1);
				$cdate=$t2;
				$duedate=$t3;
				$servicedateto=$t4;
			}
			else if($val=="servto")
			{
				$servicedateto=date("m/d/Y",$t1);
				$cdate=$t2;
				$duedate=$t3;
				$servicedate=$t4;
			}
			else if($val=="view")
			{			
				$cdate=$t1;
				$duedate=$t2;
				$servicedate=$t3;
				$servicedateto=$t4;

				$invservicedate=$servicedate;
				$invservicedateto=$servicedateto;
			}
			else if($val=="redirect")
			{
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$cdate=date("m/d/Y",$thisday);
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d")+15,date("Y"));
				$duedate=date("m/d/Y",$thisday);

				$servicedate = $invservicedate;
				$servicedateto = $invservicedateto;
			}
			else 
			{
				$cdate=date("m/d/Y",$t1);
				$duedate=date("m/d/Y",$t2);;
				$servicedate=date("m/d/Y",$t3);;
				$servicedateto=date("m/d/Y",$t4);;
			}
		}

		$sintdate=explode("/",$servicedate);
		$sintdate1=explode("/",$servicedateto);

		$ftdate=$sintdate[2]."-".$sintdate[0]."-".$sintdate[1];
		$ftdate1=$sintdate1[2]."-".$sintdate1[0]."-".$sintdate1[1];
	}
	else
	{
		$cdate = $invoicedate;
	}

	$menu->showHeader("accounting","Create Invoices","4|1");
?>
<script>
	// Grid sorting function
	var orig_function = window.headerClicked;
	window.headerClicked = function(e)
	{
		var botonfunc = e.srcElement.id;
		var posbotonfunc = botonfunc.indexOf(":" ,12);
		var numbotonfunc = botonfunc.charAt(posbotonfunc+1);
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
<script src="scripts/billingall.js"></script>
<script language="JavaScript" src="scripts/mm_menu.js"></script>
<script language="javascript" src="/BSOS/scripts/common_ajax.js"></script>
<script src="/BSOS/scripts/date_format.js" language=javascript></script>
<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
<link rel="stylesheet" href="/BSOS/css/fontawesome.css">

<?php
	if ($invtype=="Employee")
	{
		require_once("empgrid.inc");
		if($invlocation!="")
			$loc_clause=" hrcon_compen.location = $invlocation AND ";
		if($invdept!="")
			$loc_clause.=" hrcon_compen.dept = $invdept AND ";
	}
	else if($invtype=="Customer")
	{
		require_once("custgrid.inc");
		if($invlocation!="")
			$loc_clause=" Client_Accounts.loc_id = $invlocation AND ";
		if($invdept!="")
			$loc_clause.=" Client_Accounts.deptid = $invdept AND ";
	}
	else if($invtype=="EmployeeAsgn")
	{
		require_once("empasgngrid.inc");
		if($invlocation!="")
			$loc_clause=" hrcon_compen.location = $invlocation AND ";
		if($invdept!="")
			$loc_clause.=" hrcon_compen.dept = $invdept AND ";
	}
	else if($invtype=="Assignment" || $invtype=="Assignment_Approver")
	{
		if(isset($selClient) && $selClient != "")
			require_once("custasgngrid.inc");
		else
			require_once("asgngrid.inc");

		if($invlocation!="")
			$loc_clause=" Client_Accounts.loc_id = $invlocation AND ";
		if($invdept!="")
			$loc_clause.=" Client_Accounts.deptid = $invdept AND ";

	} elseif ($invtype == 'PONumber') {

		if (isset($invlocation) && !empty($invlocation)) {

			$loc_clause	= " hrcon_compen.location = '".$invlocation."' AND ";
		}

		if (isset($invdept) && !empty($invdept)) {

			$loc_clause	.= " hrcon_compen.dept = $invdept AND ";
		}

		require_once('ponumber.inc');
	}elseif ($invtype == 'PONumberassgn') {

		if (isset($invlocation) && !empty($invlocation)) {

			$loc_clause	= " hrcon_compen.location = '".$invlocation."' AND ";
		}

		if (isset($invdept) && !empty($invdept)) {

			$loc_clause	.= " hrcon_compen.dept = $invdept AND ";
		}
		if($selClient !='0' && $selClient != "")
	{
		$loc_clause = "timesheet.client IN (".$selClient.") AND hrcon_jobs.po_num = '".$ponum."' AND";
	}

		require_once('ponumberassgn.inc');
	}
	if ($invtype=="BillingContact")
	{
		require_once("billcontactgrid.inc");
		if($invlocation!="")
			$loc_clause=" Client_Accounts.loc_id = $invlocation AND ";
		if($invdept!="")
			$loc_clause.=" Client_Accounts.deptid = $invdept AND ";
	}
	if ($invtype=="Approver")
	{
		if (isset($invlocation) && !empty($invlocation)) {

			$loc_clause	= " hrcon_compen.location = '".$invlocation."' AND ";
		}

		if (isset($invdept) && !empty($invdept)) {

			$loc_clause	.= " hrcon_compen.dept = $invdept AND ";
		}

		require_once("ponumber.inc");
		
	}
?>
<link href="/BSOS/css/resize.css" rel="stylesheet" type="text/css">
<script src="/BSOS/scripts/resize.js"></script>  
<link href="/BSOS/css/NewUiGrid.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/iframeLoader.js"></script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<style> 
	.active-box-normal{ border-bottom:none; }
	#grid1 .active-column-0 .active-box-resize{ display: none !important; }
	#grid1 .active-scroll-data{ height: 90px !important;}
	#grid1 .active-column-0{width: 30px !important;}
	#grid1 .active-scroll-top{ width: 100% !important; }	
	#grid1\.data\.item\:0 .active-column-0{ border: none !important;} 
	#grid1\.top\.item\:0 { height: 99%; }
	#grid2 .active-scroll-data, #grid2 .active-scroll-bars { overflow: hidden !important; }
	#grid2 .active-box-item { padding: 1px;}
	.showpage {width: 100%;}
	@media screen and (-webkit-min-device-pixel-ratio:0) {
		.checkmark{left: -2px;}
	}
</style>
<script language="javascript">
function creditspop()
{
	var v_width  = 800;
    var v_heigth = 400;
	var top=(window.screen.availHeight-v_heigth)/2;
	var left=(window.screen.availWidth-v_width)/2;
	remote=window.open("available_credits_popup.php","cat",'width=800,height=400,statusbar=no,menubar=no,scrollbars=yes,dependent=yes,resizable=yes,hotkeys=no,left='+left+',top='+top);
	remote.focus();
	//window.open("available_credits_popup.php", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=500,left=500,width=800,height=400");
}
function openNewWindow()
{
	
	
	var url="available_credits_popup.php";

	$().modalBox({'html':'<div id="attribute-selector" style="margin-left:-363px !important; margin-top:-176px !important; left:50% !important; top:50% !important; position: fixed;  "><div class="scroll-area"><div class="scroll-pane"><iframe id="schCalendarView" src="'+url+'" border="0" width=800px height=450px scrolling="yes" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:800px;overflow-x:scroll;height:450px;position:relative;top:0px;left:0px;"></iframe></div></div></div>'});
		$("#modal-wrapper").addClass("receivemodal-wrapper");
		$("#attribute-selector .scroll-area").css({height: 400,width: 800});
}
</script>
<form name='invoice' id='invoice' action="invoicearchive.php" method="post">
  <input type=hidden name=t1 id="t1" value='' />
  <input type=hidden name=t2 id="t2" value='' />
  <input type=hidden name=t3 id="t3" value='' />
  <input type=hidden name=t4 id="t4" value='' />
  <input type=hidden name=t5 id="t5" value='' />
  <input type=hidden name=t6 id="t6" value='' />
  <input type=hidden name=val id="val" value='' />
  <input type=hidden name=totalvalues id="totalvalues" value='' />
  <input type=hidden name=totalids id="totalids" value='' />
  <input type=hidden name=mail id="mail" value='' />
  <input type=hidden name=fax id="fax" value='' />
  <input type=hidden name=aa id="aa" value="">
  <input type=hidden name=addr id="addr" value="">
  <input type=hidden name=maninvnum id="maninvnum" value="">
   <input type=hidden name=applypaymentterms id="applypaymentterms" value="no">
  <input type=hidden name=invservicedate id="invservicedate" value="<? echo $invservicedate; ?>">
  <input type=hidden name=invservicedateto id="invservicedateto" value="<? echo $invservicedateto; ?>">
  <input type=hidden name=ftdate id="ftdate" value="<? echo $ftdate; ?>">
  <input type=hidden name=ftdate1 id="ftdate1" value="<? echo $ftdate1; ?>" />
  <input type=hidden name=invoice_type id="invoice_type" value="<? echo $invtype; ?>" />
  <input type=hidden name=selClient id="selClient" value="<? echo $selClient; ?>" />
  <div id="main">
    <td valign=top align=center class=tbldata><table width=100% cellpadding=0 cellspacing=0 border=0>
        <div id="content">
          <tr>
            <td class="titleNewPad"><table cellpadding=0 cellspacing=0 border=0 width=100% class="ProfileNewUI">
                <tr>
                  <td width="20%" align=left><font class=modcaption>&nbsp;Create&nbsp;Invoices</font></td>
                  <td align=right>Following is a list of customers with unbilled Time, Expenses or Charges.</td>
                </tr>
              </table></td>
          </tr>
          <tr>
            <td width=100% valign="middle" align=right class="custBorderB titleNewPad"><table border="0" cellpadding=4 cellspacing=0 width=100% class="ProfileNewUI defaultTopRange custFont-13">
                <tr <?php echo $dislpayforClient; ?>>
                  <td colspan="5"><font class="afontstyle">&nbsp;Create Invoices by:</font><font face=arial size=1>
                    <select name="invtype" id="invtype"  class=drpdwne  style="width:150px">
						<option value="Customer" id="Customer" <?php echo getSel($invtype,"Customer"); ?>>Customer</option>
						<option value="Employee" id="Employee" <?php echo getSel($invtype,"Employee"); ?>>Employee</option>
						<option value="Assignment" id="Assignment" <?php echo getSel($invtype,"Assignment"); ?>>Billing Address</option>
						<option value="PONumber" id="PONumber" <?php echo getSel($invtype, 'PONumber'); ?>>PO Number</option>
						<option value="BillingContact" id="BillingContact" <?php echo getSel($invtype, 'BillingContact'); ?>>Billing Contact - ASGN</option>
						<option value="Approver" id="Approver" <?php echo getSel($invtype, 'Approver'); ?>>Approver</option>
                    </select>
                    </font> <font class="afontstyle">&nbsp;in Location</font>
                    <select name="invlocation" id="location" class=drpdwne style="width:175px" onChange=updateDeptList()>
                      <option value="" <?php echo getSel($invlocation,"");?>>ALL</option>
                      <?php
                      	$lque = "SELECT cm.serial_no, CONCAT(cm.loccode,' - ',cm.heading) 
						FROM contact_manage cm 
						LEFT JOIN department dept ON (dept.loc_id = cm.serial_no) WHERE dept.sno !=0 AND dept.sno IN (".$deptAccesSno.")
						GROUP BY cm.serial_no ORDER BY cm.loccode";
			//$lque = "SELECT serial_no, CONCAT(loccode,' - ',heading) FROM contact_manage ORDER BY loccode";
			$lres = mysql_query($lque,$db);
			while($lrow = mysql_fetch_row($lres))
				print "<option value='".$lrow[0]."' ".getSel($invlocation,$lrow[0]).">".$lrow[1]."</option>";
			?>
                    </select>
                    <font class="afontstyle">&nbsp;in Department</font>
                    <select name="invdept" id="department" class=drpdwne style="width:175px">
                      <option value="" <?php echo getSel($invdept,"");?>>ALL</option>
                      <?php
			if($invlocation!="")
				$wcl = " AND loc_id=$invlocation ";

			$dque = "SELECT sno, deptname FROM department WHERE 1=1 ".$wcl." AND sno !=0 AND sno IN (".$deptAccesSno.") ORDER BY deptname";
			$dres = mysql_query($dque,$db);
			while($drow = mysql_fetch_row($dres))
				print "<option value='".$drow[0]."' ".getSel($invdept,$drow[0]).">".$drow[1]."</option>";
			?>
                    </select>
                    <input type="hidden" name="invoicedate" size="10" maxlength="10" tabindex="1"  value="<?php echo $cdate; ?>">
                    <input type="hidden" name="duedate" size="10" maxlength="10" tabindex="2"  value="<?php echo $duedate; ?>">
                    <font class=afontstyle>From&nbsp;</font>
                    <input type=text size=10 maxlength="10" name=servicedate value="<?php echo $servicedate; ?>">
                    <script language="JavaScript"> new tcal ({'formname':'invoice','controlname':'servicedate'});</script>
                    <font class=afontstyle>&nbsp;&nbsp;To&nbsp;</font>
                    <input type=text size=10  maxlength="10" name=servicedateto value="<?php echo $servicedateto; ?>"> 
                    <script language="JavaScript"> new tcal ({'formname':'invoice','controlname':'servicedateto'});</script>
                     <a href=javascript:DateCheck('invoicedate','duedate','servicedate','servicedateto')>View</a></td>
                </tr>
              </table></td>
          </tr>
        </div>
        <div id="topheader">
          <tr class="NewGridTopBg">
            <?php
		if($selClient !='0' && $selClient != "")
			$name=explode("|","fa fa-eject~Generate&nbsp;Invoice|fa-ban~Cancel");
		else
			$name=explode("|","fa-file~New&nbsp;Invoice|fa fa-eject~Generate&nbsp;Invoice");

		if($selClient !='0' && $selClient != "")
		{
			if($invtype=="EmployeeAsgn")
				$link=explode("|","javascript:doSave();|invoiceall.php?val=redirect&invtype=Employee&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto");
			else
				$link=explode("|","javascript:doSave();|invoiceall.php?val=redirect&invtype=Customer&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto");
		}
		else
		{
			$link=explode("|","javascript:doManInvoice(); title=Click&nbsp;to&nbsp;create&nbsp;a&nbsp;manual&nbsp;invoice|javascript:doSave();");
		}
         // $name=explode("|","fa fa-usd~Available&nbsp;Credits");
		  //$link=explode("|","javascript:CreditsRegister();");
		if($selClient !='0' && $selClient != ""){				
			$heading= "invoice.gif~Create&nbsp;Invoices";
		}
		else {
			$heading = '';
			if(PAYROLL_PROCESS_BY!="QB"){
		$menu->showMainGridHeadingStrip1(array_merge(array('fa fa-pencil-square-o~Create/Edit&nbsp;Invoice&nbsp;Template', 'fa fa-check-square~Assign&nbsp;Invoice&nbsp;Template', 'fa fa-usd~Available&nbsp;Credits'), $name), array_merge(array('javascript:doInvoiceTempWin("edit");', 'javascript:AssignInvoice();', 'javascript:creditspop();'),$link),$heading);		
			} else {
		$menu->showMainGridHeadingStrip1(array_merge(array('fa fa-pencil-square-o~Create/Edit&nbsp;Invoice&nbsp;Template', 'fa fa-check-square~Assign&nbsp;Invoice&nbsp;Template'), $name), array_merge(array('javascript:doInvoiceTempWin("edit");', 'javascript:AssignInvoice();'),$link),$heading);				
			}
		}
	?>
          </tr>
        </div>
        <div id="grid_form">
          <tr>
            <td><?php
 		if($Time_sno != '')
			$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";
		if($Exp_sno != '')
			$template_Expense_Check = " AND expense.client NOT IN ('".$Exp_sno."')";
		if($Charge_sno != '')
			$template_Charge_Check = " AND hrcon_jobs.client NOT IN ('".$Charge_sno."')";
		 		
		$template_Check = $Time_sno."|".$Exp_sno."|".$Charge_sno;

		$charges="0";
		$time="0";
		$expenses="0";
		$amountdue="0";

		$cs1=$ftdate;
		$cs2=$ftdate1;
		$clientuser="";

		if($invtype=="Customer")
		{
			$message="No Invoices are available";
			$headers="<label class='container-chk'><input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>|Cust ID|Customer Name|# Inv|ServiceDate|Time|Charges|Expenses|TotalAmount|Inv.Template|Location|Department";
			$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>";
			echo headerGrid($headers,$sertypes);

			$query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid,'' as charge from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where ".$loc_clause." timesheet.client NOT IN ('',0) and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."'  AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND Client_Accounts.clienttype IN ('CUST','BOTH')  AND department.sno !='0' AND department.sno IN(".$deptAccesSno.") group by timesheet.client ORDER BY timesheet.sdate ASC";
			$data=mysql_query($query,$db);

			echo displayWorkCreateInvoiceall($data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check);
		}
		else if ($invtype=="Employee")
		{	
			$message="No Invoices are available";
			$headers="<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Employee|Cust ID|Customer Name|# Inv|ServiceDate|Time|Charges|Expenses|TotalAmount|Inv.Template|Location|Department";
			$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=11 onkeyup=searchGrid(this.value,'11')>";
			echo headerGrid($headers,$sertypes);					

			//$query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active' LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." timesheet.username=hrcon_jobs.username and hrcon_jobs.client= timesheet.client and timesheet.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND Client_Accounts.clienttype IN ('CUST','BOTH') group by timesheet.username,timesheet.client ORDER BY timesheet.sdate ASC ";
			$query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid,'' as charge from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active' LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." timesheet.username=hrcon_jobs.username and hrcon_jobs.client= timesheet.client and timesheet.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH')  AND department.sno !='0' AND department.sno IN(".$deptAccesSno.") group by timesheet.username,timesheet.client ORDER BY timesheet.sdate ASC ";
			$data=mysql_query($query,$db);

			echo displayWorkCreateInvoiceall_emp($data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check);
		}
		else if($invtype=="EmployeeAsgn")
		{
			$message="No Invoices are available";
			$headers="<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Employee|Cust ID|Customer Name|Billing Address|City|State|ServiceDate|Time|Charges|Expenses|TotalAmount|Location|Department";
			$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>|<input class=serbox12 type=text name=column12 size=10 onkeyup=searchGrid(this.value,'12')>|<input class=serbox13 type=text name=column13 size=10 onkeyup=searchGrid(this.value,'13')>|<input class=serbox14 type=text name=column14 size=10 onkeyup=searchGrid(this.value,'14')>|<input class=serbox15 type=text name=column15 size=10 onkeyup=searchGrid(this.value,'15')>";
			echo headerGrid($headers,$sertypes);					

			//$query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active' LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." timesheet.username=hrcon_jobs.username and hrcon_jobs.client= timesheet.client and timesheet.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') ".$empCond."  AND Client_Accounts.clienttype IN ('CUST','BOTH') group by timesheet.username,timesheet.client ORDER BY timesheet.sdate ASC ";
			$query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid,'' as charge  from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active' LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." timesheet.username=hrcon_jobs.username and hrcon_jobs.client= timesheet.client and timesheet.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') ".$empCond." AND department.sno !='0' AND department.sno IN(".$deptAccesSno.") group by timesheet.username,timesheet.client ORDER BY timesheet.sdate ASC ";
			$data=mysql_query($query,$db);

			echo displayWorkCreateInvoiceall_empassgn($data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient);
		}
		else if ($invtype=="Assignment")
		{
			$message="No Invoices are available";

			if(isset($selClient) && $selClient != "")
			{
				$headers="<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Cust ID|Customer Name|Billing Address|City|State| ServiceDate|Time|Charges|Expenses|TotalAmount|Location|Department";
				$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>";
			}
			else
			{
				$headers="<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Cust ID|Customer Name|Billing Address|City|State|ServiceDate|Time|Charges|Expenses|TotalAmount|Inv.Template|Location|Department";
				$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>|<input class=serbox12 type=text name=column12 size=10 onkeyup=searchGrid(this.value,'12')>";
			}
			echo headerGrid($headers,$sertypes);

			$query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid,'' as charge from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where ".$loc_clause." timesheet.client NOT IN ('',0) and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."'  AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') ".$clientCond."  AND Client_Accounts.clienttype IN ('CUST','BOTH') AND department.sno !='0' AND department.sno IN(".$deptAccesSno.") group by timesheet.client ORDER BY timesheet.sdate ASC ";
			$data=mysql_query($query,$db);			

			echo displayWorkCreateInvoiceall_assgn($data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient);
		}else if ($invtype=="Assignment_Approver")
		{
			$message="No Invoices are available";

			if(isset($selClient) && $selClient != "")
			{
				$headers="<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Cust ID|Customer Name|Billing Address|City|State| ServiceDate|Time|Charges|Expenses|TotalAmount|Location|Department";
				$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>";
			}
			else
			{
				$headers="<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Cust ID|Customer Name|Billing Address|City|State|ServiceDate|TimeCharges|Expenses|TotalAmount|Inv.Template|Location|Department";
				$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>";
			}
			echo headerGrid($headers,$sertypes);
			$approver_join ='';
			$approver_clause = '';
			if($manager_sno !=''){
				$approver_join = " LEFT JOIN staffacc_contact ON hrcon_jobs.manager = staffacc_contact.sno
					LEFT JOIN staffacc_contactacc ON hrcon_jobs.manager = staffacc_contactacc.con_id";
				$approver_clause = " AND hrcon_jobs.manager  IN (".$manager_sno.")";
			}
			$query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid,'' as charge from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno ".$approver_join." where ".$loc_clause." timesheet.client NOT IN ('',0) and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."'  AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') ".$clientCond."  AND Client_Accounts.clienttype IN ('CUST','BOTH') ".$approver_clause." AND department.sno !='0' AND department.sno IN(".$deptAccesSno.") group by timesheet.client ORDER BY timesheet.sdate ASC ";
			$data=mysql_query($query,$db);			

			echo displayWorkCreateInvoiceall_assgnApprover($data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient,$manager_sno);

		} elseif ($invtype == 'PONumber') {

			$headers	= "<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|PO Number|Cust ID|Customer Name|# Inv|ServiceDate|Time|Charges|Expenses|TotalAmount|Inv.Template|Location|Department";

			$sertypes	= "|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>";

			echo headerGrid($headers, $sertypes);

			echo displayCreateInvoiceForPONumber($ftdate, $ftdate1, $cdate, $duedate, $template_Check);
		}elseif ($invtype == 'PONumberassgn') {

			$headers	= "<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|PO Number|Cust ID|Customer Name|ServiceDate|Time|Charges|Expenses|TotalAmount|Inv.Template|Location|Department";

			$sertypes	= "|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>";
			
			echo headerGrid($headers, $sertypes);
			

			echo displayCreateInvoiceForPONumber_assgn($ftdate, $ftdate1, $cdate, $duedate, $template_Check);
		}
		else if ($invtype=="BillingContact")
		{	
			$message="No Invoices are available";
			$headers="<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Cust ID|Customer Name|Billing Contact - ASGN|# Inv|ServiceDate|Time|Charges|Expenses|TotalAmount|Inv.Template|Location|Department";
			$sertypes="|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>";
			echo headerGrid($headers,$sertypes);					

			
			 	 $query="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),timesheet.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax,staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,CONCAT(staffacc_contact.fname,'  ',staffacc_contact.lname),hrcon_jobs.bill_address,hrcon_jobs.bill_contact,staffacc_cinfo.override_tempid,'' as charge".$conassgid." from par_timesheet,timesheet_hours AS timesheet LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username LEFT JOIN emp_list ON emp_list.username=timesheet.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active'  LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno  LEFT JOIN staffacc_contact ON staffacc_contact.sno=hrcon_jobs.bill_contact where ".$loc_clause." timesheet.username=hrcon_jobs.username and hrcon_jobs.client= timesheet.client and timesheet.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and timesheet.parid=par_timesheet.sno and timesheet.type!='EARN' and timesheet.billable='Yes' and par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' and ".tzRetQueryStringDate('par_timesheet.sdate','YMDDate','-').">='".$ftdate."' and ".tzRetQueryStringDate('par_timesheet.edate','YMDDate','-')."<='".$ftdate1."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND (staffacc_contact.fname !='' || staffacc_contact.lname !='')  ".$billCondboth." AND department.sno !='0' AND department.sno IN(".$deptAccesSno.") group by timesheet.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact".$attention." ORDER BY timesheet.sdate ASC ";
			$data=mysql_query($query,$db);

			echo displayWorkCreateInvoiceall_billingContact($data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient,$selAddr);
		}
		else if ($invtype=="Approver")
		{	
			$headers	= "<input type=checkbox name=chk onClick=chke(this,document.forms[0],'auids[]')>|Approver|Cust ID|Customer Name|# Inv|ServiceDate|Time|Charges|Expenses|TotalAmount|Inv.Template|Location|Department";

			$sertypes	= "|<input class=serbox0 type=text name=column0 size=10 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=10 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=10 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=10 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=10 onkeyup=searchGrid(this.value,'4')>|<input class=serbox5 type=text name=column5 size=10 onkeyup=searchGrid(this.value,'5')>|<input class=serbox6 type=text name=column6 size=10 onkeyup=searchGrid(this.value,'6')>|<input class=serbox7 type=text name=column7 size=10 onkeyup=searchGrid(this.value,'7')>|<input class=serbox8 type=text name=column8 size=10 onkeyup=searchGrid(this.value,'8')>|<input class=serbox9 type=text name=column9 size=10 onkeyup=searchGrid(this.value,'9')>|<input class=serbox10 type=text name=column10 size=10 onkeyup=searchGrid(this.value,'10')>|<input class=serbox11 type=text name=column11 size=10 onkeyup=searchGrid(this.value,'11')>";

			echo headerGrid($headers, $sertypes);

			echo displayCreateInvoiceForApprover($ftdate, $ftdate1, $cdate, $duedate, $template_Check);
		}
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
		$heading="invoice.gif~Create&nbsp;Invoices";
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
<script>
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

	window.mm_menu_0515130056_1 = new Menu("root",100,19,"Verdana, Arial, Helvetica, sans-serif",10,"#000000","#000000","#EFEFEF","#CCCCCC","left","middle",3,0,300,-5,7,true,false,true,1,true,true);
	mm_menu_0515130056_1.addMenuItem("<b>Archive</b>","javascript:doRemove();");
	mm_menu_0515130056_1.addMenuItem("<b>ViewArchive</b>","javascript:doView();");

	mm_menu_0515130056_1.fontWeight="bold";
	mm_menu_0515130056_1.hideOnMouseOut=true;
	mm_menu_0515130056_1.bgColor='#555555';
	mm_menu_0515130056_1.menuBorder=1;
	mm_menu_0515130056_1.menuLiteBgColor='#FFFFFF';
	mm_menu_0515130056_1.menuBorderBgColor='#777777';
	mm_menu_0515130056_1.writeMenus();
</script>
<script>
//Removing the &nbsp; in the chrome browser when onloading the page- Email Collaboration grid
$(document).ready(function() { 
    $('#grid1, #grid2').html(function(i,h){	
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
