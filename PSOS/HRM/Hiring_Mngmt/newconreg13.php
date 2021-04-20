<?php
   	require("ap_summit_export.php");
require("global.inc");
	require("onboarding_config.inc");
	require("Menu.inc");
	require("dispfunc.php");
	$menu=new EmpMenu();	

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

	//Declared to get tax type 
	/* TLS-01202018 */
	$tax_arr = explode("|",$_SESSION["page17".$HRM_HM_SESSIONRN]);
	$tax_type = $tax_arr[0];
	$elements=explode("|",$_SESSION["page13".$HRM_HM_SESSIONRN]);
        $paygroupsno  = $elements[58];
	
	$date=explode("-",$elements[2]);
	$assignval=explode("|",$_SESSION["page15".$HRM_HM_SESSIONRN]);
	$assign_type = explode("|",$HRHM_Jbtype);
	
	//For dsiabling the checknox for Payrate based on assignment
	if($command=="new" && $elements[3] == "")
	{
		$empsal	= 'disabled';
		$compen_module_status	= 'New';
	}
	else
	{
        $compen_module_status="";
	}
        //UOM: display Default Rate Type
         $commandwin = $_SESSION["command".$HRM_HM_SESSIONRN];
         $resupmodewin = $_SESSION['resupmode'.$HRM_HM_SESSIONRN];
         if($commandwin=="new" || $resupmodewin=="edit"){
            if($elements[16]=="Temp/Contract" || $elements[16]=="Internal Temp/Contract"){
                $rateQuery = "SELECT * FROM manage_uom WHERE status='Active' AND is_default='Y' ";
                $rateResponse = mysql_query($rateQuery, $db);
                if(mysql_num_rows($rateResponse)> 0){
                $rateRow = mysql_fetch_row($rateResponse);
                if($resupmodewin=="edit"){
                 $payRateVal = 'HOUR';
                }else{
                 $payRateVal = $rateRow[2];   
                    }
                }
                }else{
                    if($resupmodewin=="edit"){
                        $payRateVal = 'HOUR';
                    }else{
                        $payRateVal = 'YEAR';
                    }
                 }
             $elements[14] = $payRateVal;
             
         }
      // Condition to maintain the relation for employee type and job type in compensation and assignment tabs respectively
	if($assignval[23]=="compno")
	{
        $elements[27]='N';
    }
    else if(($assignval[23]=="compyes" && $elements[27]=='Y') || $elements[33]=='compyes')
	{
        $comptype=getManage($assignval[2]);
        if($comptype=='Temp/Contract to Direct')
            $elements[16]='Temp/Contract';
		else if($comptype=='Direct')
			$elements[27]='N';
        else if($comptype!="")
            $elements[16]=$comptype;
    }


	if(count($elements)==37)
	{
		$rdobonus = $elements[21];
		$txtbonus = ($elements[22]=="0.00") ? "" : $elements[22];
		$txtworkcompcode = '';
		$chkworkcompcode = 'N';
		$chkpaybasedcomp = 'Y';
		$lstpayperiod = '';
		$txtpayhours = '';
		$txtpaydays  = '';
		$txtperdiem = '';
		$lstsetup1 = 0; 
		$lstsetup2 = 0;
		$lstsetup3 = 0;
	}
	else
	{
		$rdobonus = $elements[46];
		$txtbonus = ($elements[47]=="0.00") ? "" : $elements[47];
		$txtworkcompcode = $elements[48];
		$chkworkcompcode = $elements[49];
		$chkpaybasedcomp = $elements[50];
		$lstpayperiod = $elements[51];
		$txtpayhours = $elements[52];
		$txtpaydays  = $elements[53];
		$txtperdiem = ($elements[54]=='0.00') ? "" : $elements[54];
		$lstsetup1 = $elements[55]; 
		$lstsetup2 = $elements[56];
		$lstsetup3 = $elements[57];
	}

	function sel($a,$b)
	{
		if($a==$b)
			return "checked";
		else
			return "";
	}
	
	function check_selected($a,$b)
	{
		if($a==$b)
			return true;
		else
			return false;
	}

	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}
    if($_SESSION["VendorEmpType".$HRM_HM_SESSIONRN]!="EmpVendor")
     {
		 $IndexValue=1;
		 $tabTile="Hiring Management";
		 $tab1Title="Hiring Management";
		 $tab2Title="Hiring Management";
     }
     else if($_SESSION["VEmpType".$HRM_HM_SESSIONRN]=='Vconsultant')
     {
		 $IndexValue=0;
		 $tabTile="Add Consultant";
		 $tab1Title="Consultant";
		 $tab2Title="Consultant";
     }
	 else if($_SESSION["VEmpType".$HRM_HM_SESSIONRN]=='Vconsulting')
     {
		 $IndexValue=0;
		 $tabTile="Add Candidate";
		 $tab1Title="Candidate";
		 $tab2Title="Candidate";
     }
	

	if($elements[0]=="")
	{
		$que="select count(*) from emp_list";
		$res=mysql_query($que,$db);
		$row=mysql_fetch_row($res);
		$emp_id=($row[0]+1);
		$elements[0]=$emp_id;
	}

	if($elements[3]=="")
		$elements[3]=0;

	$query1="SELECT sno,if(depcode!='',CONCAT_WS(' - ',depcode,deptname),deptname) FROM department WHERE (loc_id = '".$elements[3]."' OR deflt = 'Y') AND status='Active' AND department.sno !='0' AND department.sno IN(".$deptAccesSno.") ORDER BY deflt";
	$res1=mysql_query($query1,$db);

	$dque="SELECT count(1) FROM department WHERE deflt='Y' AND department.sno !='0' AND department.sno IN(".$deptAccesSno.")";
	$dres=mysql_query($dque,$db);
	$drow=mysql_fetch_row($dres);
	if($drow[0]==0)
		$query2="select l.city,l.state,l.country,l.serial_no,CONCAT_WS(' - ',l.loccode,l.heading) from contact_manage l LEFT JOIN department d ON l.serial_no=d.loc_id where l.status!='BP' AND d.status='Active' AND d.sno !='0' AND d.sno IN(".$deptAccesSno.") GROUP BY l.serial_no";
	else
		$query2="select city,state,country,serial_no,CONCAT_WS(' - ',loccode,heading) from contact_manage where status !='BP'";
	$res2=mysql_query($query2,$db);

	$query3="select name from manage where type='jotype' and status='Y' and name!='Direct' and name!='Temp/Contract to Direct'";
	$res3=mysql_query($query3,$db);
	$myelements=explode("|",$_SESSION["HRAT_CompSchedule".$HRM_HM_SESSIONRN]);
	$DispTimes=display_SelectBox_Times();
	
	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON == 'MADISON') ? 'udCheckNull = "YES" ' : '';
	$chkNull_Att = (DEFAULT_SYNCHR == 'Y' || DEFAULT_AKKUPAY=='Y') ? 'udCheckNull = "YES" ' : '';
        $chkNull_Att_AKK = (DEFAULT_AKKUPAY=='Y') ? 'udCheckNull = "YES" ' : '';

	$con_id = $_SESSION["conusername".$HRM_HM_SESSIONRN];
?>
<html>
<head>
<title><?php echo $tabTile; ?></title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CustomTab.css">
<!--<link href="/BSOS/css/crm-editscreen.css" type=text/css rel=stylesheet>-->
<link href="/BSOS/css/calendar.css" rel="stylesheet" type="text/css">
<script src="/BSOS/scripts/calendar.js" language="javascript"></script>
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<script type="text/javascript">
<?php
if(PAYROLL_PROCESS_BY_MADISON == 'MADISON')
	echo "var MADISON_VALIDATION = true;\n";
else
	echo "var MADISON_VALIDATION = false;\n";
?>
</script>
<script src=/BSOS/scripts/tabpane.js></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>

<script src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/validatehresume.js></script>
<script language=javascript src=/BSOS/scripts/validatehhr.js></script>
<script language=javascript src="/BSOS/scripts/schedule.js"></script>
<script language=javascript src=scripts/place_schedule.js></script>
<script language=javascript src="/BSOS/scripts/common.js"></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script type="text/javascript" src="/BSOS/scripts/hrworkcycles.js"></script>
<script>
	var madison = '<?=PAYROLL_PROCESS_BY_MADISON;?>';
	var syncHRDefault = '<?php echo DEFAULT_SYNCHR; ?>';
	var QBCDefault = '<?php echo DEFAULT_QBCANADA; ?>';
        var akkupayroll = '<?php echo DEFAULT_AKKUPAY ;?>';
        //added this code to control the save candidate functionality from accounting vendores while create a new candiddate from that page
        //There is no diff found in between these two session varible , where some one might have unknowingly declared multiple variables for same purpose
        var fromAccountingVendor = '<?php echo $_SESSION["VendorEmpType".$HRM_HM_SESSIONRN] ;?>';
        var fromAccountingVConsultant = '<?php echo $_SESSION["VEmpType".$HRM_HM_SESSIONRN] ;?>'; 
</script>
<?php
	if(PAYROLL_PROCESS_BY_MADISON == 'MADISON' || DEFAULT_SYNCHR == 'Y' || DEFAULT_AKKUPAY == 'Y')
		echo "<script language=javascript src=/BSOS/scripts/formValidation.js></script>";
?>
<script type="text/javascript" src="/BSOS/scripts/hrworkcycles.js"></script>

<style type="text/css">
 .dynamic-tab-pane-control.tab-pane input[name="txt_total"], .dynamic-tab-pane-control.tab-pane select[name="sel_perdiem"], .dynamic-tab-pane-control.tab-pane select[name="sel_perdiem2"], .dynamic-tab-pane-control.tab-pane select[name="smonth"], .dynamic-tab-pane-control.tab-pane select[name="syear"], .dynamic-tab-pane-control.tab-pane select[name="rehire_smonth"], .dynamic-tab-pane-control.tab-pane select[name="rehire_syear"]{ width:96px !important;min-width:96px !important}
.dynamic-tab-pane-control.tab-pane select[name="sday"], .dynamic-tab-pane-control.tab-pane select[name="rehire_sday"], .dynamic-tab-pane-control.tab-pane input[name="earned_bill_text[]"]{width:50px !important;min-width:50px !important;}


.dynamic-tab-pane-control.tab-pane input[name="salary"], .dynamic-tab-pane-control.tab-pane select[name="salper"], .dynamic-tab-pane-control.tab-pane select[name="currencyid"], .dynamic-tab-pane-control.tab-pane select[name="benchsalper"], .dynamic-tab-pane-control.tab-pane select[name="benchcurrencyid"], .dynamic-tab-pane-control.tab-pane select[name="otrsalper"], .dynamic-tab-pane-control.tab-pane select[name="otrcurrencyid"], .dynamic-tab-pane-control.tab-pane select[name="dbltimerateper"], .dynamic-tab-pane-control.tab-pane select[name="dbltimeratecurr"], .dynamic-tab-pane-control.tab-pane select[name="ernd_bnfts_rateper[]"], .dynamic-tab-pane-control.tab-pane select[name="ernd_bnfts_ratecurr[]"]{width:90px !important;min-width:90px !important;}

.dynamic-tab-pane-control.tab-pane input[name="salary"], .dynamic-tab-pane-control.tab-pane input[name="benchrate"], .dynamic-tab-pane-control.tab-pane input[name="otr"], .dynamic-tab-pane-control.tab-pane input[name="dbltimerate"], .dynamic-tab-pane-control.tab-pane input[name="ernd_bnfts_rate[]"], .dynamic-tab-pane-control.tab-pane input[name="txt_lodging"], .dynamic-tab-pane-control.tab-pane input[name="txt_mie"]{ width:63px !important; min-width:63px !important}
</style>
</head>
<body>
<form method=post name='conreg' id='conreg'>
<input type=hidden name=savehire>
<input type=hidden name='url' id='url' value='' />
<input type=hidden name='dest' id='dest' value='' />
<?php
if($cand_sno != '')
{
?>
<input type=hidden name='daction' id='daction' value='storeresume.php?cand_sno=<?=$cand_sno?>&mode=back'>
<?php
}
else
{
?>
<input type=hidden name='daction' id='daction' value='storeresume.php'>
<?
}
?>
<input type=hidden name=retschsno value="<?php echo $myelements[2];?>">
<input type=hidden name=payrateassign value="<?php echo $elements[29];?>">
<input type=hidden name=brateval value="<?php echo $elements[24];?>">
<input type=hidden name=bratevalper value="<?php echo $elements[25];?>">
<input type=hidden name=bratevalid value="<?php echo $elements[26];?>">
<input type=hidden name=snoforwork value="<?php echo $elements[30];?>">
<input type=hidden name=addr value="<?php echo $addr;?>">
<input type=hidden name=assigntype value="<?php echo getManage($assignval[2]);?>">
<input type=hidden name=VendorEmpType value="<?php echo $_SESSION["VendorEmpType".$HRM_HM_SESSIONRN]; ?>">
<input type=hidden name=VEmpType value="<?php echo $_SESSION["VEmpType".$HRM_HM_SESSIONRN]; ?>">
<input type='hidden' name='companyinfo' value="<?php echo $companyinfo; ?>">
<input type='hidden' name='Rnd' value="<?php echo $Rnd; ?>">
<?php
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);
?>
<input type=hidden name=dateval value="<?php echo $todate;?>">

<input type=hidden name=typecheck value="<?php echo "hireassignment";?>">
<input type=hidden name=typename value="<?php echo $assign_type[0];?>">
<input type=hidden name=typename1 value="<?php echo  $assign_type[1];?>">
<input type=hidden name=typecomp value="<?php echo  $assign_type[2];?>">
<input type=hidden name=typetime value="<?php echo  $assign_type[3];?>">
<input type=hidden name=pagename value="page13">
<input type=hidden name=exist value="<?php echo $cand_sno;?>">
<input type='hidden' name='validateMadison_ses' id='validateMadison_ses' value="<?php echo html_tls_specialchars($_SESSION["validateMadison_ses".$HRM_HM_SESSIONRN],ENT_QUOTES); ?>">
<input type='hidden' name='validateSyncHR_ses' id='validateSyncHR_ses' value="<?php echo html_tls_specialchars($_SESSION["validateSyncHR_ses".$HRM_HM_SESSIONRN],ENT_QUOTES); ?>">
<input type=hidden name=hrmhmsessionrn id=hrmhmsessionrn value="<?php echo $HRM_HM_SESSIONRN; ?>">
<input type=hidden name='page17<?php echo $HRM_HM_SESSIONRN; ?>' id='page17<?php echo $HRM_HM_SESSIONRN; ?>' value='<?php echo $_SESSION["page17".$HRM_HM_SESSIONRN];?>'>
<input type=hidden name='page13<?php echo $HRM_HM_SESSIONRN; ?>' id='page13<?php echo $HRM_HM_SESSIONRN; ?>' value='<?php echo $_SESSION["page13".$HRM_HM_SESSIONRN];?>'>
<input type=hidden name='page15<?php echo $HRM_HM_SESSIONRN; ?>' id='page15<?php echo $HRM_HM_SESSIONRN; ?>' value='<?php echo $_SESSION["page15".$HRM_HM_SESSIONRN];?>'>
<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
		<td>
		<table width=99% cellpadding=0 cellspacing=0 border=0>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
        <?php
		if($_SESSION["command".$HRM_HM_SESSIONRN] != "new")
		{
        ?>
			<td align=left><font class=modcaption>&nbsp;&nbsp;<?php $names=explode("|",$_SESSION["page1".$HRM_HM_SESSIONRN]); echo dispTextdb($names[0])." ".dispTextdb($names[2]); ?></font></td>
		<?php
		}
		else
		{
          ?>
			<td align=left><font class=modcaption>&nbsp;&nbsp;<?php echo $tab1Title; ?></font></td>
	<? } ?>
		</tr>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		</table>
		</td>
	</tr>
	
	</div>

	<div id="grid_form">
	<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white" class="ProfileNewUI">
	<tr>
	<td width=100% valign=top align=left>
	<div class="tab-pane" id="tabPane2">
	<script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
	<div class="tab-page" id="tabPage22">
		<h2 class="tab"><?php echo $hiring_Main_Tabnames["Profile Data"];?></h2>
		<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ), "javascript:doPost(1,13)");</script>
 	</div>
	<div class="tab-page" id="tabPage21">
		<h2 class="tab"><?php echo $hiring_Main_Tabnames["HR Data"];?></h2>
		<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ));</script>

  <div class="tab-pane" id="tabPane1">
		<script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
 <?php if($_SESSION["VendorEmpType".$HRM_HM_SESSIONRN]!="EmpVendor") { ?>
 	<div class="tab-page" id="tabPage12">
			<h2 class="tab"><?php echo $hiring_Tabnames["Immigration"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ), "javascript:doPost(23,13)" );</script>
		</div>

<?php } ?>
		<div class="tab-page" id="tabPage13">
			<h2 class="tab"><?php echo $hiring_Tabnames["Compensation"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage13" ) );</script>

			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr class="NewGridTopBg">
				<?php
                   if($_SESSION["VendorEmpType".$HRM_HM_SESSIONRN]!="EmpVendor")//for accounting vendors
                   {
    					$name=explode("|",$obName."fa fa-floppy-o~Save|fa fa-thumbs-up~Hire|fa fa-hand-o-left~Back|fa fa-hand-o-right~Next");
    					$link=explode("|",$obLink."javascript:doSaveHire(13)|javascript:doHire(13)|javascript:validate(23,13)|javascript:validate(14,13)");
                    }else
                    {
                        $name=explode("|","fa fa-floppy-o~Save|fa fa-hand-o-left~Back|fa fa-hand-o-right~Next");
    					$link=explode("|","javascript:doHire(13)|javascript:validate(2,13)|javascript:validate(14,13)");
                    }
					$heading="user.gif~".$tab1Title;
					$menu->showHeadingStrip1($name,$link,$heading);
				?>
				</tr>
				<?php
					if(trim($tax_type) == "" && $_SESSION["VEmpType".$HRM_HM_SESSIONRN] == "Vconsultant")
					{
						$tax_type = "1099";
					}
					elseif(trim($tax_type) == "" && $_SESSION["VEmpType".$HRM_HM_SESSIONRN] == "Vconsulting")
					{
						$tax_type = "C-to-C";
					}
					$module_Flag='HrngMngmnt';
					require("compensation.php");
				?>
				<tr class="NewGridBotBg">
				<!-- <?php
					//if($_SESSION["VendorEmpType".$HRM_HM_SESSIONRN]!="EmpVendor")//for accounting vendors
                   {
	                	//$name=explode("|",$obName."fa fa-floppy-o~Save|fa fa-thumbs-up~Hire|fa fa-hand-o-left~Back|fa fa-hand-o-right~Next");
	    				//$link=explode("|",$obLink."javascript:doSaveHire(13)|javascript:doHire(13)|javascript:validate(23,13)|javascript:validate(14,13)");
                    }//else
                    {
                        //$name=explode("|","fa fa-floppy-o~Save|fa fa-hand-o-left~Back|fa fa-hand-o-right~Next");
    					//$link=explode("|","javascript:doHire(13)|javascript:validate(2,13)|javascript:validate(14,13)");
                        }
					//$heading="user.gif~".$tab1Title;
					//$menu->showHeadingStrip1($name,$link,$heading);
				?> -->
			</tr>
			</table>
		</div>

		<div class="tab-page" id="tabPage14">
			<h2 class="tab"><?php echo $hiring_Tabnames["Personal Profile"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage14" ), "javascript:doPost(14,13)" );</script>
		</div>
		<?php 
		if($_SESSION["VendorEmpType".$HRM_HM_SESSIONRN]!="EmpVendor") 
		{
			?>
			<div class="tab-page" id="tabPage15">
				<h2 class="tab"><?php echo $hiring_Tabnames["Assignments"];?></h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage15" ), "javascript:doPost(15,13)" );</script>
			</div>
			<div class="tab-page" id="tabPage16">
				<h2 class="tab"><?php echo $hiring_Tabnames["Reporting"];?></h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage16" ), "javascript:doPost(22,13)" );</script>
			</div>
			<div class="tab-page" id="tabPage17">
				<h2 class="tab"><?php echo $hiring_Tabnames["Tax Deductions"];?></h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage17" ), "javascript:doPost(17,13)" );</script>
			</div>
  			<?php
		}
		if($_SESSION["VendorEmpType".$HRM_HM_SESSIONRN]!="EmpVendor") 
		{
			?>
				<div class="tab-page" id="tabPage18">
					<h2 class="tab"><?php echo $hiring_Tabnames["Other Deductions"];?></h2>
					<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage18" ), "javascript:doPost(18,13)" );</script>
				</div>
				<div class="tab-page" id="tabPage112">
					<h2 class="tab"><?php echo $hiring_Tabnames["Expenses"];?></h2>
					<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage112" ), "javascript:doPost(24,13)" );</script>
				</div>
				<div class="tab-page" id="tabPage19">
					<h2 class="tab"><?php echo $hiring_Tabnames["Benefits"];?></h2>
					<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage19" ), "javascript:doPost(19,13)" );</script>
				</div>
			<?php
			//}
			?>	
		 <div class="tab-page" id="tabPage110">
			<h2 class="tab"><?php echo $hiring_Tabnames["Dependents"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage110" ), "javascript:doPost(20,13)" );</script>
		</div>
		 <div class="tab-page" id="tabPage111">
			<h2 class="tab"><?php echo $hiring_Tabnames["Emergency Contact"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage111" ), "javascript:doPost(21,13)" );</script>
		</div>
		<div class="tab-page" id="tabPage113">
			<h2 class="tab"><?php echo $hiring_Tabnames["PayCheck Delivery"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage113" ), "javascript:doPost(25,13)" );</script>
		</div>
                <div class="tab-page" id="tabPage114">
			<h2 class="tab"><?php echo $hiring_Tabnames["Garnishments"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage114" ), "javascript:doPost(28,13)" );</script>
		</div>
                <div class="tab-page" id="tabPage115">
			<h2 class="tab"><?php echo $hiring_Tabnames["Company Contributions"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage115" ), "javascript:doPost(29,13)" );</script>
		</div>
   <?php } ?>
	</div>
	<script>tp1.setSelectedIndex(<?php echo $IndexValue; ?>);</script>
	</div>

	<?php
	if($con_id!="")
	{
		?>
		<div class="tab-page" id="tabPage23">
			<h2 class="tab"><?php echo $hiring_Main_Tabnames["Activities"];?></h2>
			<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage23" ), "viewhract.php?HRM_HM_SESSIONRN=<?=$HRM_HM_SESSIONRN;?>" );</script>
		</div>
		<?php
	}
	?>

	</div>
	</td>
	</tr>
	</table>
	<script>tp2.setSelectedIndex(1);</script>
	</div>
</table>
</td>
</div>
</form>
<?php
echo "<script type='text/javascript'>DisBox1();</script>";
echo "<script type='text/javascript'>DisBox2();</script>";
?>
<script language="javascript">
var rowCount="<?php echo (int)$newRowId+1;?>";	
var row_class="<?php echo $row_class;?>";
setFormObject("document.conreg");
</script>
<?php
if($command=="new" && $elements[13] != 'NAV')
	echo "<script type='text/javascript'>defultFullTime();</script>";

if($HRHM_SheduleNum <= 0 )
	echo "<script type='text/javascript'>defultFullTime();</script>";
	
$HRHM_SheduleNum = 1;
$_SESSION["HRHM_SheduleNum"] = $HRHM_SheduleNum;
?>
<script>
displayScheduledata("<?php echo $_SESSION["HRAT_CompSchedule".$HRM_HM_SESSIONRN];?>");
</script>
</body>
</html>