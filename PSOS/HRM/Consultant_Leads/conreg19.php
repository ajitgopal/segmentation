<?php
   	require("global.inc");
	require("Menu.inc");
	require("dispfunc.php");
	$menu=new EmpMenu();

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

	$page19 = $_SESSION['page19_'.$apprn];
	$page211 = $_SESSION['page211'.$apprn];

	$elements=explode("|",$page19);
        $paygroupsno = $elements[51];
	$date=explode("-",$elements[2]);
	$assignval=explode("|",$page211);
	
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
	if(count($elements)==21)
	{
		$rdobonus = $elements[17];
		$txtbonus = ($elements[18]=="0.00") ? "" : $elements[18];
                $txtworkcompcode = $elements[19];
		$chkworkcompcode = $elements[20];
		$txtperdiem = "";
	}
	else
	{
		$rdobonus = $elements[46];
		$txtbonus = ($elements[47]=="0.00") ? "" : $elements[47];
		$txtworkcompcode = $elements[48];
		$chkworkcompcode = $elements[49];
		$txtperdiem      = ($elements[50]=="0.00") ? "" : $elements[50];
	}
        
        //UOM: Dynamic Default ratetype for display
         $commandwin = $_SESSION[command.$HRM_HM_SESSIONRN];
         if($commandwin=="new"){
             $rateQuery = "SELECT * FROM manage_uom WHERE status='Active' AND is_default='Y' ";
             $rateResponse = mysql_query($rateQuery, $db);
             $rateRow = mysql_fetch_row($rateResponse);
            if($elements[16]=="Temp/Contract" || $elements[16]=="Internal Temp/Contract"){
                 $payRateVal = $rateRow[2];
             }else{
                 $payRateVal = 'YEAR';
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

    // Condition to select Internal Direct as default type
	if($elements[13]=='')
		$elements[16]="Internal Direct";
	
	if($elements[16]=="Internal Direct")
		$empsal="disabled";

	if($elements[0]=="")
	{
		$que="select count(*) from consultant_list";
		$res=mysql_query($que,$db);
		$row=mysql_fetch_row($res);
		$emp_id=($row[0]+1);
		$elements[0]=$emp_id;
	}
	
	if($elements[3]=="")
		$elements[3]=0;

	$query1="SELECT sno,if(depcode!='',CONCAT_WS(' - ',depcode,deptname),deptname) FROM department WHERE (loc_id = '".$elements[3]."' OR deflt = 'Y') AND status='Active' AND sno !='0' AND sno IN (".$deptAccesSno.") ORDER BY deflt";
	$res1=mysql_query($query1,$db);

	$dque="SELECT count(1) FROM department WHERE deflt='Y' AND sno !='0' AND sno IN (".$deptAccesSno.")";
	$dres=mysql_query($dque,$db);
	$drow=mysql_fetch_row($dres);
	if($drow[0]==0)
		$query2="select l.city,l.state,l.country,l.serial_no,CONCAT_WS(' - ',l.loccode,l.heading) from contact_manage l LEFT JOIN department d ON l.serial_no=d.loc_id where l.status!='BP' AND d.status='Active' AND d.sno !='0' AND d.sno IN (".$deptAccesSno.") GROUP BY l.serial_no";
	else
		$query2="select city,state,country,serial_no,CONCAT_WS(' - ',loccode,heading) from contact_manage where status !='BP'";
	$res2=mysql_query($query2,$db);

	$query3="select name from manage where type='jotype' and status='Y' and name!='Direct' and name!='Temp/Contract to Direct'";
	$res3=mysql_query($query3,$db);
	
	$Htype=$elements[31];
	function DisplaySchdule($pos,$Htype)
	{
	  global $username,$maildb,$db,$user_timezone; 
	  $RecordArray=array(); 
	  array_push($RecordArray,$Htype);
		 $query="select sno,if(DATE_FORMAT(sch_date,'%c/%e/%Y')='0/0/0000','',DATE_FORMAT(CONVERT_TZ(sch_date,'SYSTEM','".$user_timezone[1]."'),'%c/%e/%Y')),if(wdays>0,wdays,''),starthour,endhour from consultant_tab  where consno='".$pos."' and coltype='compen'"; 
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
if($elements[13]=='')
{
$HRAT_CompSchedule=DisplaySchdule($elements[30],$Htype);
}
$myelements=explode("|",$HRAT_CompSchedule);
$DispTimes=display_SelectBox_Times();
?>
<html>
<head>
<title>Add Applicant</title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CustomTab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<script>
  var akkupayroll = '<?php echo DEFAULT_AKKUPAY ;?>';
</script>
<script src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/validatehresume.js></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language=javascript src=/BSOS/scripts/validateresume1.js></script>
<script language=javascript src=/BSOS/scripts/validateapptracking.js></script>
<script language=javascript src=scripts/validateimg.js></script>
<script language=javascript src="/BSOS/scripts/schedule.js"></script>
<script language=javascript src=scripts/place_schedule.js></script>
<script language=javascript src="/BSOS/scripts/common.js"></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
</head>

<form method=post name=conreg>
<input type=hidden name=url>
<input type=hidden name=dest>
<input type=hidden name=daction value='storeresume.php'>
<input type=hidden name=page19>
<input type=hidden name=retschsno value="<?php echo $myelements[2];?>">
<input type=hidden name=payrateassign value="<?php echo $elements[29];?>">
<input type=hidden name=brateval value="<?php echo $elements[24];?>">
<input type=hidden name=bratevalper value="<?php echo $elements[25];?>">
<input type=hidden name=bratevalid value="<?php echo $elements[26];?>">
<input type=hidden name=snoforwork value="<?php echo $elements[30];?>">
<input type=hidden name=addr value="<?php echo $addr;?>">
<input type=hidden name=assigntype value="<?php echo getManage($assignval[2]);?>">
<input type="hidden" name="apprn" id="apprn" value="<?php echo $apprn; ?>">

<?php
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);
?>
<input type=hidden name=chkMode id="chkMode" value="add">
<input type=hidden name=dateval value="<?php echo $todate;?>">

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
			<td align=left><font class=modcaption>&nbsp;&nbsp;Applicant Tracking</font></td>
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
	<div class="tab-page" id="tabPage21">
		<h2 class="tab"><?php echo $applicant_Main_Tabnames["Profile Data"];?></h2>
		<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ), "javascript:doPost(1,19)");</script>
 	</div>
	<div class="tab-page" id="tabPage23">
		<h2 class="tab"><?php echo $applicant_Main_Tabnames["HR Data"];?></h2>
		<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage23" ));</script>

		<div class="tab-pane" id="tabPane1">
		<script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
		
		<div class="tab-page" id="tabPage12">
			<h2 class="tab"><?php echo $applicant_Tabnames["Immigration Status"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ), "javascript:doPost(9,19)" );</script>
		</div>
		<div class="tab-page" id="tabPage13">
			<h2 class="tab"><?php echo $applicant_Tabnames["Compensation"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage13" ) );</script>

			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr class="NewGridTopBg">
				<?php
					$name=explode("|","fa-user-plus~Add&nbsp;Applicant|fa-hand-o-left~Back|fa-hand-o-right~Next");
					$link=explode("|","javascript:DoappTrackSave(19)|javascript:validate(9,19)|javascript:validate(20,19)");
					$heading="user.gif~Applicant&nbsp;Tracking";
					$menu->showHeadingStrip1($name,$link,$heading);
				?>
				</tr>
		
				<?php
				if(isset($error))
					print "<tr><td><font class=afontstyle4>Some of the fields you haven't entered (or) wrong in Compensation. Click on Hire to check</font></td></tr>";
				?>
		
				<?php 
					$module_Flag='AppMngmnt';
					$compen_module_status='New';
					require("compensation.php"); 
				?>
				
		
				<tr class="NewGridBotBg">
				<!-- <?php
					//$name=explode("|","fa-user-plus~Add&nbsp;Applicant|fa-hand-o-left~Back|fa-hand-o-right~Next");
					//$link=explode("|","javascript:DoappTrackSave(19)|javascript:validate(9,19)|javascript:validate(20,19)");
					//$heading="user.gif~Applicant&nbsp;Tracking";
					//$menu->showHeadingStrip1($name,$link,$heading);
				?> -->
			</tr>
			</table>
		</div>

		<div class="tab-page" id="tabPage14">
			<h2 class="tab"><?php echo $applicant_Tabnames["Personal Profile"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage14" ), "javascript:doPost(20,19)" );</script>
		</div>
		<div class="tab-page" id="tabPage16">
			<h2 class="tab"><?php echo $applicant_Tabnames["Reporting"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage16" ), "javascript:doPost(22,19)" );</script>
		</div>
		<div class="tab-page" id="tabPage17">
			<h2 class="tab"><?php echo $applicant_Tabnames["Tax Deductions"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage17" ), "javascript:doPost(23,19)" );</script>
		</div>
		<div class="tab-page" id="tabPage18">
			<h2 class="tab"><?php echo $applicant_Tabnames["Other Deductions"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage18" ), "javascript:doPost(24,19)" );</script>
		</div>
		<div class="tab-page" id="tabPage112">
			<h2 class="tab"><?php echo $applicant_Tabnames["Expenses"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage112" ), "javascript:doPost(25,19)" );</script>
		</div>
		<div class="tab-page" id="tabPage19">
			<h2 class="tab"><?php echo $applicant_Tabnames["Benefits"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage19" ), "javascript:doPost(26,19)" );</script>
		</div>
		 <div class="tab-page" id="tabPage110">
			<h2 class="tab"><?php echo $applicant_Tabnames["Dependents"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage110" ), "javascript:doPost(27,19)" );</script>
		</div>
		 <div class="tab-page" id="tabPage111">
			<h2 class="tab"><?php echo $applicant_Tabnames["Emergency Contact"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage111" ), "javascript:doPost(28,19)" );</script>
		</div>
		<div class="tab-page" id="tabPage113">
			<h2 class="tab"><?php echo $applicant_Tabnames["PayCheck Delivery"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage113" ), "javascript:doPost(29,19)" );</script>
		</div>
                <div class="tab-page" id="tabPage31">
			<h2 class="tab"><?php echo $applicant_Tabnames["Garnishments"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage31" ), "javascript:doPost(31,19)" );</script>
		</div>
                <div class="tab-page" id="tabPage32">
			<h2 class="tab"><?php echo $applicant_Tabnames["Company Contributions"];?></h2>
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage32" ), "javascript:doPost(32,19)" );</script>
		</div>
	</div>
        <div class="tab-page" id="tabPage22">
		  <h2 class="tab"><?php echo $applicant_Main_Tabnames["Resume"];?></h2>
		  <script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ), "javascript:validate(16,19)" );</script>
		  </div>
	<script>tp1.setSelectedIndex(1);</script>
	
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
<script>
	var rowCount="<?php echo (int)$newRowId+1;?>";	
	var row_class="<?php echo $row_class;?>";
	setFormObject("document.conreg");
	defultFullTime();
	displayScheduledata("<?php echo $HRAT_CompSchedule;?>");
</script></body>
</html>
