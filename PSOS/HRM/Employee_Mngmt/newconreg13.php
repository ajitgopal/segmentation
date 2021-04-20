<?php
   	require("global.inc");
	require("onboarding_config.inc");

	require("Menu.inc");
	require_once($akken_psos_include_path.'commonfuns.inc');

	//These are the Useful Random session variables for Employee Compensation
	/* TLS-01202018 */
	$conusername = $_SESSION["conusername".$emprnm];
	$recno = $_SESSION["recno".$emprnm];
	$employee_name = $_SESSION["employee_name".$emprnm];
	$HRAT_CompSchedule=$_SESSION["HRAT_CompSchedule".$emprnm];
	$HREM_SheduleNum=$_SESSION["HREM_SheduleNum".$emprnm];

	require("dispfunc.php");
	$con_id="emp".$recno;
	$hisdet="All";
	$menu=new EmpMenu();

	//Declared to get tax type
	$page17 = $_SESSION["HRM_EmpMngmt_page17".$emprnm];
	$tax_arr = explode("|",$page17);
	$tax_type = $tax_arr[0];

	$page13 = $_SESSION["HRM_EmpMngmt_page13".$emprnm];

	$elements=explode("|",$page13);
        $paygroupsno  = $elements[62];
	if($elements[16]=='Temp/Contract to Direct')
 		$elements[16]='Temp/Contract';

	if($elements[34] == "N")
		$elements[33] ="0-0-0";

	if($elements[27]=="Y")
		$elements[16]="";

	$date=explode("-",$elements[2]);
	$edate=explode("-",$elements[15]);
	$tdate = explode("-",$elements[33]);
        $rhdate = explode("-",$elements[60]);
	
	$superUserStatus="";
	//Query for checking Super User.
	$chkSuperUserQue="select username from users where type='sp'";
	$resSuperUserQue=mysql_query($chkSuperUserQue,$db);
	$dataSuperUserQue=mysql_fetch_row($resSuperUserQue);
	
	//Query for getting the username from Emp_list.
	$chkEmpQue="select username from emp_list where sno='".$recno."'";
	$chkEmpRes=mysql_query($chkEmpQue,$db);
	$chkEmpRow=mysql_fetch_row($chkEmpRes);
	
	if($chkEmpRow[0]==$dataSuperUserQue[0])
        $superUserStatus="true"; //If super user condition satisfies, setting this variable.

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
	
	$elements_schedule=explode("|",$page13);
	$Htype=$elements_schedule[31];
	function DisplaySchdule($pos,$Htype)
	{
        global $username,$maildb,$db,$user_timezone;
        $RecordArray=array();
        array_push($RecordArray,$Htype);

		$que="select appno from assignment_schedule where contactsno like '%".$pos."|%' and modulename='HR->Compensation'";
        $res=mysql_query($que,$db);
        $row=mysql_fetch_row($res);
        $query="select sno,if(DATE_FORMAT(sch_date,'%c/%e/%Y')='0/0/0000','',DATE_FORMAT(sch_date,'%c/%e/%Y')),if(wdays>0,wdays,''),starthour,endhour from hrcon_tab  where tabsno='".$row[0]."' and coltype='compen'";
        $QryExc=mysql_query($query,$db);

        if(mysql_num_rows($QryExc)>0)
        {
        	while($SchRow=mysql_fetch_row($QryExc))
        	{
        		array_push($RecordArray,implode("|^AkkSplitCol^|",$SchRow));
        	}
        }
        return implode("|^AkkenSplit^|",$RecordArray);
	}
    if($elements_schedule[13]=='')
    {
        $HRAT_CompSchedule=DisplaySchdule($elements_schedule[30],$Htype);
    }

	if($elements[3]=="")
		$elements[3]=0;

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$deptAccesWhr = " AND sno !='0' AND sno IN (".$deptAccesSno.") "; 

	$query1="SELECT sno,if(depcode!='',CONCAT_WS(' - ',depcode,deptname),deptname) FROM department WHERE (loc_id = '".$elements[3]."' OR deflt = 'Y') AND status='Active' ".$deptAccesWhr." ORDER BY deflt";
	$res1=mysql_query($query1,$db);

	$dque="SELECT count(1) FROM department WHERE deflt='Y' ".$deptAccesWhr;
	$dres=mysql_query($dque,$db);
	$drow=mysql_fetch_row($dres);
	if($drow[0]==0)
		$query2="select l.city,l.state,l.country,l.serial_no,CONCAT_WS(' - ',l.loccode,l.heading) from contact_manage l LEFT JOIN department d ON l.serial_no=d.loc_id where l.status!='BP' AND d.status='Active' AND d.sno IN(".$deptAccesSno.") GROUP BY l.serial_no";
	else
		$query2="select city,state,country,serial_no,CONCAT_WS(' - ',loccode,heading) from contact_manage where status !='BP'";
	$res2=mysql_query($query2,$db);

	$query3="select name from manage where type='jotype' and status='Y' and name!='Direct' and name!='Temp/Contract to Direct'";
	$res3=mysql_query($query3,$db);
	
	$myelements=explode("|",$HRAT_CompSchedule);

function displayTimes()
{	
	
	$Timeoptions="<option value=''>&nbsp;--Select--&nbsp;</option>";
	$Timeoptions.="<option value='24:00'>12:00am</option>";
	$Timeoptions.="<option value='12:30'>12:30am</option>";
	
	for($i=1,$j=1;$i<24;$i=($i+0.5),$j++)
	{

		$tmin=($j%2==0)?'30':'00';
		$Intval=(int)$i;
		$disptime=$Intval.":".$tmin;
		$am=($i>=12)?"pm":"am";
		$Dt=(($Intval>=13)?($Intval-12):$Intval).":".$tmin;
		$Timeoptions.="<option value='".$disptime."'>".$Dt.$am."</option>";
		
	}
	return $Timeoptions;
}
$TimeOpt=displayTimes();
$DispTimes=display_SelectBox_Times();
     
	 if($VendorEmpType!="EmpVendor") //for accounting vendors
     {
     	 $IndexValue=1;
         $tabTitle="Employee Management";
         $tab1Title="Employee Management";
         $tab2Title="Employee Management";
         $msgtitle="Employee";
     }
     else if($VEmpType=='Vconsultant')
     {
      	 $IndexValue=0;
         $tabTitle="Edit Consultant";
         $tab1Title="Consultant Vendor";
         $tab2Title="Consultant Vendor";
         $msgtitle="Consultant";
     }
	 else if($VEmpType=='Vconsulting')
     {
      	 $IndexValue=0;
         $tabTitle="Edit Candidate";
         $tab1Title="Candidate";
         $tab2Title="Candidate";
         $msgtitle="Candidate";
     }
	
	$spl_Attribute = (PAYROLL_PROCESS_BY_MADISON == 'MADISON') ? 'udCheckNull ="YES" ' : '';
	$chkNull_Att = (DEFAULT_SYNCHR == 'Y' || DEFAULT_AKKUPAY=='Y') ? 'udCheckNull = "YES" ' : '';
	
	//Defining a variable for showing mandatory SyncHR star marks from this page only.
	$showMandatoryAstrik = "Y";
?>
<html>
<head>
<title><?php echo $tabTitle; ?></title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CustomTab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link href="/BSOS/css/calendar.css" rel="stylesheet" type="text/css">
<script>
	var madison = '<?=PAYROLL_PROCESS_BY_MADISON;?>';
	var syncHRDefault = '<?php echo DEFAULT_SYNCHR; ?>';
        var akkupayroll = '<?php echo DEFAULT_AKKUPAY ;?>';
</script>
<?php
	if(PAYROLL_PROCESS_BY_MADISON == 'MADISON' || DEFAULT_SYNCHR == 'Y' || DEFAULT_AKKUPAY == 'Y')
		echo "<script language=javascript src=/BSOS/scripts/formValidation.js></script>";
?>
<script type="text/javascript">
<?php
if(PAYROLL_PROCESS_BY_MADISON == 'MADISON')
	echo "var MADISON_VALIDATION = true;\n";
else
	echo "var MADISON_VALIDATION = false;\n";
?>
</script>
<script src="/BSOS/scripts/tabpane.js"></script>
<script language="javascript" src="/BSOS/scripts/validatehresume.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script src="/BSOS/scripts/calendar.js" language="javascript"></script>
<script src="/BSOS/scripts/tabpane.js"></script>
<script language="javascript" src="/BSOS/scripts/validaterempresume.js"></script>
<script language="javascript" src="/BSOS/scripts/validateremphr.js"></script>
<script language="javascript" src="/BSOS/scripts/schedule.js"></script>
<script language="javascript" src="scripts/place_schedule.js"></script>
<script language="javascript" src="/BSOS/scripts/common.js"></script>
<script language="javascript" src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src=/BSOS/scripts/ajaxsack.js></script>
<script>
function showCompen(val)
{
   var emprnm = document.conreg.emprnm.value;
   remote=window.open('viewcompen.php?date1='+val+'&emprnm='+emprnm,'status','width=500,height=450,resizable=no,scrollbars=yes,status=0');
   remote.focus();
}

function syncAPEmp(locid,empid)
{
	ss=document.getElementById("syncstatus");
	ss.innerHTML = "<img src='/BSOS/images/loading_icon_small.gif' border=0> Syncing Employee Data, please be patient......";
	var ajaxChk = new sack();
	ajaxChk.requestFile = 'syncAPEmp.php?locid='+locid+'&empid='+empid;
	ajaxChk.method = 'POST';
	ajaxChk.onCompletion = function ()
	{
		if(ajaxChk.response == 0)
		{
			ss.innerHTML = "";
			window.opener.doGridSearch('search');
			alert("Employee data has been synced successfully. Please re-open the employee record to reflect the changes.");
			self.close();
		}
	};
	ajaxChk.runAJAX();
}
</script>

<script type="text/javascript">
var asgnAlertStartDate = "<?php echo date('m/d/Y');?>";
var asgnAlertEndDay = "<?php echo date('d');?>";
var asgnAlertEndMonth = "<?php echo date('m');?>";
var asgnAlertEndYear = "<?php echo date('Y');?>"; 
</script>
<script type="text/javascript" src="/BSOS/scripts/hrworkcycles.js"></script>
</head>

<form method=post name=conreg>
<input type=hidden name=url>
<input type=hidden name=dest>
<input type=hidden name=daction value='storeresume.php'>
<input type=hidden name="HRM_EmpMngmt_page13<?php echo $emprnm; ?>" value="<?php echo html_tls_specialchars($page13,ENT_QUOTES);?>">
<input type=hidden name=retschsno value="<?php echo $myelements[2];?>">
<input type=hidden name=payrateassign value="<?php echo $elements[29];?>">
<input type=hidden name=brateval value="<?php echo $elements[24];?>">
<input type=hidden name=bratevalper value="<?php echo $elements[25];?>">
<input type=hidden name=bratevalid value="<?php echo $elements[26];?>">
<input type=hidden name=snoforwork value="<?php echo $elements[30];?>">
<input type=hidden name=addr value="<?php echo $addr;?>">
<input type=hidden name=edeptname value="<?php echo $edeptname;?>">
<input type=hidden name=employeeIdVal value="<?php echo $recno;?>">
<input type=hidden name=superUserStatus value="<?php echo $superUserStatus;?>">
<input type=hidden name="emprnm" value="<?php echo $emprnm; ?>">
<input type='hidden' name='companyinfo' value="<?php echo $companyinfo; ?>">
<input type='hidden' name='Rnd' value="<?php echo $Rnd; ?>">
<input type=hidden name=conUser value="<?php echo $conusername;?>">
<input type=hidden name=actAsgnClose>
<input type=hidden name=actAsgnEndDate>

<?php
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);
	$todaydate=date("n/j/Y",$thisday);
	
	$mysqlToDate = "";
	$qryMySqlTDate = "SELECT DATE_FORMAT(now(),'%c/%d/%Y')";
	$resMySqlTDate = mysql_query($qryMySqlTDate, $db);
	if(mysql_num_rows($resMySqlTDate) > 0) {
		$rowMySqlTDate = mysql_fetch_array($resMySqlTDate);
		$mysqlToDate = $rowMySqlTDate[0];
	}
?>
<input type=hidden name=dateval value="<?php echo $todate;?>">
<input type=hidden name=termdate value="<?php echo $tdate;?>">
<input type=hidden name=todaydate value="<?php echo $todaydate;?>">
<input type=hidden name="mysqltdate" id="mysqltdate" value="<?php echo $mysqlToDate;?>">
<style type="text/css" >
.modalDialog_contentDiv{height:auto !important }
.alert-ync-container, .alert-ync, .alert-ync-container{ height:100% !important ; min-height:99%; overflow:hidden}
.alert-w-chckbox-chkbox-uanas-moz, .alert-w-chckbox-chkbox-uanas-ie{ overflow-y: auto; overflow-x: hidden;}
.alert-cntrbtns-uas, .alert-cntrbtns-uanas{ margin:10px auto; width:188px;margin-left:265px !important } 
#DHTMLSuite_modalBox_iframe{ display:none !important;}
.dynamic-tab-pane-control.tab-pane select[name=sday], .dynamic-tab-pane-control.tab-pane select[name=syear], .dynamic-tab-pane-control.tab-pane select[name=rehire_sday], .dynamic-tab-pane-control.tab-pane select[name=rehire_syear]{width:85px !important;}

.dynamic-tab-pane-control.tab-pane input[name="txt_lodging"], .dynamic-tab-pane-control.tab-pane input[name="txt_mie"], .dynamic-tab-pane-control.tab-pane input[name="txt_total"], .dynamic-tab-pane-control.tab-pane select[name="sel_perdiem"], .dynamic-tab-pane-control.tab-pane select[name="sel_perdiem2"], .dynamic-tab-pane-control.tab-pane select[name="smonth"], .dynamic-tab-pane-control.tab-pane select[name="syear"], .dynamic-tab-pane-control.tab-pane select[name="rehire_smonth"], .dynamic-tab-pane-control.tab-pane select[name="rehire_syear"]{ width:96px !important;min-width:96px !important}
.dynamic-tab-pane-control.tab-pane select[name="sday"], .dynamic-tab-pane-control.tab-pane select[name="rehire_sday"], .dynamic-tab-pane-control.tab-pane input[name="earned_bill_text[]"]{width:60px !important;min-width:50px !important;}


.dynamic-tab-pane-control.tab-pane input[name="salary"], .dynamic-tab-pane-control.tab-pane select[name="salper"], .dynamic-tab-pane-control.tab-pane select[name="currencyid"], .dynamic-tab-pane-control.tab-pane select[name="benchsalper"], .dynamic-tab-pane-control.tab-pane select[name="benchcurrencyid"], .dynamic-tab-pane-control.tab-pane select[name="otrsalper"], .dynamic-tab-pane-control.tab-pane select[name="otrcurrencyid"], .dynamic-tab-pane-control.tab-pane select[name="dbltimerateper"], .dynamic-tab-pane-control.tab-pane select[name="dbltimeratecurr"], .dynamic-tab-pane-control.tab-pane select[name="ernd_bnfts_rateper[]"], .dynamic-tab-pane-control.tab-pane select[name="ernd_bnfts_ratecurr[]"]{width:90px !important;min-width:90px !important;}



.dynamic-tab-pane-control.tab-pane input[name="salary"], .dynamic-tab-pane-control.tab-pane input[name="benchrate"], .dynamic-tab-pane-control.tab-pane input[name="otr"], .dynamic-tab-pane-control.tab-pane input[name="dbltimerate"], .dynamic-tab-pane-control.tab-pane input[name="ernd_bnfts_rate[]"], .dynamic-tab-pane-control.tab-pane input[name="txt_lodging"], .dynamic-tab-pane-control.tab-pane input[name="txt_mie"], .dynamic-tab-pane-control.tab-pane input[name=""], .dynamic-tab-pane-control.tab-pane input[name=""]{ width:63px !important; min-width:63px !important}
.empTxtDec .afontstyle a, .empTxtDec .afontstyle a:link{ text-decoration:underline; }
.empTxtDec .afontstyle a:hover{ text-decoration:none; color:#3eb8f0 }

</style> 
<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
		<td>
		<table width=99% cellpadding=0 cellspacing=0 border=0>
		<tr>
			<td colspan=2 ><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
			<td><font class=modcaption>&nbsp;&nbsp;<?php echo dispTextdb($employee_name); ?></font></td>
		</tr>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		</table>
		</td>
	</tr>

	<tr><td align=center><span id=syncstatus></span></td></tr>

	<?php
	if($ustat=="success")
		print "<tr><td><font class=afontstyle4>&nbsp;$msgtitle Compensation has been updated Sucessfully.</font></td></tr>";
	?>
	</div>
	
	<div id="grid_form">
	<table border="0" width="100%" cellspacing="5" cellpadding="0" class="ProfileNewUI">
	<tr>
	<td width=100% valign=top align=left>
    <div class="tab-pane" id="tabPane2">
	<script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
        <div class="tab-page" id="tabPage21">
          <h2 class="tab"><?php echo $employees_Main_Tabnames["Profile Data"];?></h2>
            <script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ),"javascript:doPost(1,13)" );</script>
        </div>
        <div class="tab-page" id="tabPage22">
        <h2 class="tab"><?php echo $employees_Main_Tabnames["HR Data"];?></h2>
        	<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ) );</script>
			<div class="tab-pane" id="tabPane121">
			<script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane121" ) );</script>
			<?php if($VendorEmpType!="EmpVendor") 
			{
				?>
				<div class="tab-page" id="tabPage221">
				<h2 class="tab"><?php echo $employees_Tabnames["Immigration"];?></h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage221" ), "javascript:doPost(25,13)" );</script>
				</div>
				<?php
			}
			?>
			<div class="tab-page" id="tabPage222">
			<h2 class="tab"><?php echo $employees_Tabnames["Compensation"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage222" ) );</script>
			
			<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
        	<tr class="NewGridTopBg">
        	<?php
				$ap_que = "SELECT COUNT(1) FROM contact_manage WHERE serial_no='".$elements[3]."' AND mphr_username!='' AND mphr_csno!='' AND push_data='N'";
				$ap_res = mysql_query($ap_que,$db);
				$ap_row = mysql_fetch_row($ap_res);

				$ape_que = "SELECT COUNT(1) FROM mpHR_locEmpInfo WHERE locid='".$elements[3]."' AND empsno='".$elements[0]."'";
				$ape_res = mysql_query($ape_que,$db);
				$ape_row = mysql_fetch_row($ape_res);

				if($ap_row[0]>0 && $ape_row[0]==0)
				{
	        		$name=explode("|",$obName."fa fa-clone~Sync Employee Data|fa fa-clone~Update|fa fa-times~Close");
	          		$link=explode("|",$obLink."javascript:syncAPEmp('".$elements[3]."','".$elements[0]."')|javascript:terminationCheck()|javascript:window.close()");
				}
				else
				{
	        		$name=explode("|",$obName."fa fa-clone~Update|fa fa-times~Close");
	          		$link=explode("|",$obLink."javascript:terminationCheck()|javascript:window.close()");
				}
            	$heading="user.gif~".$tab1Title;
        		$menu->showHeadingStrip1($name,$link,$heading);
        	?>
        	</tr>

			<?php $module_Flag='EmpMngmnt'; ?>
			<?php require("compensation.php"); ?>

			<tr class="NewGridBotBg">
			<!-- <?php
				/*$name=explode("|",$obName."fa fa-clone~Update|fa fa-times~Close");
				$link=explode("|",$obLink."javascript:terminationCheck()|javascript:window.close()");
				$heading="user.gif~".$tab1Title;
				$menu->showHeadingStrip1($name,$link,$heading);*/
			?> -->
			</tr>

        <tr> 
        <?php
        $cque="select ".tzRetQueryStringDTime("udate","DateTimeSec","/").",emp_id,dept, date_hire,location, sno, emp_rehire_date,emp_terminate_date,modified_user,approved_user from hrcon_compen where username='".$conusername."' and ustatus='backup' order by udate desc";        
            $cres=mysql_query($cque,$db);
            $page013="";
            while($crow=mysql_fetch_row($cres))
            {
                if($crow[2]!="")
                {
                    $que="SELECT deptname FROM department WHERE sno=$crow[2] AND status='Active'";
                    $res=mysql_query($que,$db);
                    if($res)
                    {
                    	$row=mysql_fetch_row($res);
                    }
                }
                if($crow[4]!="")
                {
                    $que="select CONCAT(city,',',state),country from contact_manage where serial_no=$crow[4]";
                    $res=mysql_query($que,$db);
                    if($res)
                    {
                    	$row1=mysql_fetch_row($res);
                    }
					$location=$row1[0].",".getCountry($row1[1]);
                }
				$sdate=explode("-",$crow[3]);
				if($sdate[0]<10)
				   $sdate[0]="0".$sdate[0];
                                if($sdate[1]==0)
				   $sdate[1]="00";
                                if($sdate[2]==0)
				   $sdate[2]="0000";
				
				$hire=$sdate[0]."/".$sdate[1]."/".$sdate[2];
				if($hire=='00/00/0000' || $hire=='0/00/0000')
					$hire="";
                                if($crow[6] !="0000-00-00")
                                {
                                    $rdate  =   date("m/d/Y", strtotime($crow[6]));
                                }
                                else
                                {
                                    $rdate  =   "";
                                }
                                if($crow[7] !="0000-00-00")
                                {
                                    $terminatedate  =   date("m/d/Y", strtotime($crow[7]));
                                }
                                else
                                {
                                    $terminatedate  =   "";
                                }
                                
                    $modifieduser="";            
                    if($crow[8]!="" && $crow[8]!=0)
                    {
                        $userque="SELECT name FROM users WHERE username='".$crow[8]."'"; 
                        $userres=mysql_query($userque,$db);
                        $userrow=mysql_fetch_row($userres);
                        
                        if($crow[8]== $crow[9]){
                            $modifieduser =  $userrow[0].' (Ess User Updated)';
                        }else{
                            $modifieduser =  $userrow[0];
                        }  
                    }                
                if($page013=="")
                    $page013=$crow[0]."|".$modifieduser."|".$row[0]."|".$hire."|".$terminatedate."|".$rdate."|".$location."|".$crow[5];
                else
                    $page013.="^".$crow[0]."|".$modifieduser."|".$row[0]."|".$hire."|".$terminatedate."|".$rdate."|".$location."|".$crow[5];
            }
            if($page013!="")
            {
                $tok1=explode("^",$page013);
                for($i=0;$i<count($tok1);$i++)
                {
                    $fdata[$i]=explode("|",$tok1[$i]);
                }
            }
            if(count($fdata)>0)
            {
                print "<tr><td class=empTxtDec><table width=100% border=0 cellpadding=2 cellspacing=0 class=ProfileNewUI empTxtDec><tr class=hthbgcolor><td width=20%><font class=afontstyle><b>Modified Date</b></font></td><td width=20%><font class=afontstyle><b>Modified By</b></font></td><td width=10%><font class=afontstyle><b>Department</b></font></td><td width=10%><font class=afontstyle><b>Hired Date</b></font></td><td width=10%><font class=afontstyle><b>Terminate Date</b></font></td><td width=10%><font class=afontstyle><b>Re-Hire Date</b></font></td><td width=30%><font class=afontstyle><b>Location</b></font></td></tr>";
                for($j=0;$j<count($fdata);$j++)
                {
                    $fdata[$j][0]=$fdata[$j][0];

                    if($j%2==0)
                    	$class="tr1bgcolor";
                    else
                    	$class="tr2bgcolor";
                    print "<tr class=".$class.">";
                    for($i=0;$i<7;$i++) 
                    {
                    	if($i!=0 && $i!=1){
                    		print "<td width=10%><font class=afontstyle>".$fdata[$j][$i]."</font></td>";
                        }else if($i==1){
                            print "<td width=20%><font class=afontstyle>".$fdata[$j][$i]."</font></td>";
                        }
                        else{
                    		print "<td width=20%><font class=afontstyle><a href='javascript:showCompen(\"".$fdata[$j][7]."\")'>".$fdata[$j][$i]."</a></font></td>";
                        }
                    }
                    print "<td><font class=afontstyle>&nbsp;</font></td><td><font class=afontstyle>&nbsp;</font></td></tr>";
                }
                print "</table></td></tr>";
            }
        ?>
        </tr>
        </table>
        </div>
	
        <div class="tab-page" id="tabPage223">
            <h2 class="tab"><?php echo $employees_Tabnames["Personal Profile"];?></h2>
            <script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage223" ), "javascript:doPost(14,13)" );</script>
        </div>
   <?php if($VendorEmpType!="EmpVendor") { ?>
		<div class="tab-page" id="tabPage224">
			<h2 class="tab"><?php echo $employees_Tabnames["Assignments"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage224" ), "javascript:doPost(15,13)" );</script>
		</div>

		<div class="tab-page" id="tabPage225">
			<h2 class="tab"><?php echo $employees_Tabnames["Reporting"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage225" ), "javascript:doPost(22,13)" );</script>
		</div>
		<div class="tab-page" id="tabPage226">
			<h2 class="tab"><?php echo $employees_Tabnames["Tax Deductions"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage226" ), "javascript:doPost(17,13)" );</script>
		</div>
    <?php
		}
		if($VendorEmpType!="EmpVendor")
		{ ?>
				<div class="tab-page" id="tabPage227">
					<h2 class="tab"><?php echo $employees_Tabnames["Other Deductions"];?></h2>
					<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage227" ), "javascript:doPost(18,13)" );</script>
				</div>
		<?php
			if(PAYROLL_PROCESS_BY != "VERTEX") {
		?>
				<div class="tab-page" id="tabPage233">
					<h2 class="tab"><?php echo $employees_Tabnames["Other Expenses"];?></h2>
					<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage233" ), "javascript:doPost(26,13)" );</script>
				</div>
		<?php
			}
		?>		
				<div class="tab-page" id="tabPage228">
					<h2 class="tab"><?php echo $employees_Tabnames["Benefits"];?></h2>
					<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage228" ), "javascript:doPost(19,13)" );</script>
				</div>		
		<div class="tab-page" id="tabPage229">
			<h2 class="tab"><?php echo $employees_Tabnames["Dependents"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage229" ), "javascript:doPost(20,13)" );</script>
		</div>

		<div class="tab-page" id="tabPage231">
			<h2 class="tab"><?php echo $employees_Tabnames["Emergency Contact"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage231" ), "javascript:doPost(21,13)" );</script>
		</div>

		<div class="tab-page" id="tabPage232">
			<h2 class="tab"><?php echo $employees_Tabnames["PayCheck Delivery"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage232" ), "javascript:doPost(24,13)" );</script>
		</div>
                <div class="tab-page" id="tabPage234">
			<h2 class="tab"><?php echo $employees_Tabnames["Garnishments"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage234" ), "javascript:doPost(29,13)" );</script>
		</div>
                <div class="tab-page" id="tabPage235">
			<h2 class="tab"><?php echo $employees_Tabnames["Company Contributions"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage235" ), "javascript:doPost(30,13)" );</script>
		</div>
    </div>
    
    <script>tp1.setSelectedIndex(1);</script>
	<div class="tab-page" id="tabPage23">
		<h2 class="tab"><?php echo $employees_Main_Tabnames["Resume"];?></h2>
		<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage23" ), "javascript:doPost(23,13)" );</script>
	</div>
	
	<div class="tab-page" id="tabPage24">
		<h2 class="tab"><?php echo $employees_Main_Tabnames["Activities"];?></h2>
		<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage24" ), "viewact.php?emprnm=<?=$emprnm;?>&addr=old" );</script>
	</div>
   <?php } ?>
	<script>tp2.setSelectedIndex(1);</script>
	</div>
	</td>
	</tr>
	</table>
	</div>
</table>
</td>
</div>

</form>
<script>
	document.forms[0].dept.focus();
</script>
<?php
echo "<script type='text/javascript'>DisBox1();</script>";
?>
<script language="javascript">
var rowCount="<?php echo (int)$newRowId+1;?>";	
var row_class="<?php echo $row_class;?>";
setFormObject("document.conreg");
</script>
<?php
	if($HREM_SheduleNum <= 0 && $elements[31]!='parttime')
		echo "<script type='text/javascript'>defultFullTime();</script>";
	
	$HREM_SheduleNum = 1;
	$_SESSION["HREM_SheduleNum".$emprnm] = $HREM_SheduleNum;
?>
<script>
displayScheduledata("<?php echo $HRAT_CompSchedule;?>");

</script>
</body>
</html>
