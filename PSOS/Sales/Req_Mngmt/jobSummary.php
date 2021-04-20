<meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
<?php
	$addr1= $addr;
	require("global.inc");
        if($_REQUEST['jobposprw']=="yes"){
            if(isset($_COOKIE["jobpostings_summary_opendivs"])) {
                $opendivs = $_COOKIE["jobpostings_summary_opendivs"];
            } else {
                $opendivs= '9';  
            }
        }else{
            if(isset($_COOKIE["joborder_summary_opendivs"])) {
                $opendivs = $_COOKIE["joborder_summary_opendivs"];
            } else {
                $opendivs= '1,2,9'; 
            }
        }
	require("mysqlfun.inc");
	require("dispfunc.php");
	require("functions.php");
	require("Menu.inc");
	require("userDefine.php");
	require_once($akken_psos_include_path.'commonfuns.inc');
	require_once("multipleRatesClass.php");
	$menu=new EmpMenu();
	$burden_status = getBurdenStatus();
	require_once("custom_grid_functions.php");
	$custom_grid_function_obj = new CustomGridFunctions();
	$grdi_details = $custom_grid_function_obj->getGridName("job orders",$username);
	
	if(!empty($grdi_details))
		$totcolumns = count($custom_grid_function_obj->asFields("job orders", $username))+1;
	else
		$totcolumns = 22;
	$addr=$addr1;
        $temp_addr=$addr;
	$addr=$temp_addr;
	$posno=$addr;
      
	if($_GET['module'] == 'Admin_JobOrders' || $_GET['module'] == 'Admin_Contacts' || $_GET['module'] == 'Admin_Companies' || $_GET['module'] == 'Admin_Candidates')
	{
		$module = 'Admin_JobOrders';
	}else{
		$module = 'CRM';
	}
	    //From Admin->Joposting and CRM->joborders
        if($jobposprw=='yes'){
          $jobPosQuery = 'SELECT req_id FROM hotjobs WHERE sno="'.$addr.'"'; 
          $jobpos_res=mysql_query($jobPosQuery,$db);
	   $jobpos_row=mysql_fetch_row($jobpos_res); 
           $addr = $jobpos_row[0];
           $temp_addr=$addr;
           $addr=$temp_addr;
           $posno=$jobpos_row[0];
        }
        
	$ratesObj = new multiplerates();
	$mode_rate_type = "joborder";
	$type_order_id = $posno;
	$arrmatch=array();
	$defaultRatesArr = $ratesObj->getDefaultMutipleRates();	

	$reqAliasArr = array('Stage'=>'jobstage');
	$getRequiredSql = "SELECT column_name, element_required, element_alias FROM udv_grid_columns WHERE custom_form_modules_id = 4 AND element_required = 'yes'";
	$getRequiredResult = mysql_query($getRequiredSql);
	/* TLS-01202018 */
	$userFieldsArr = array();
	$userFieldsAlias = array();
	$reqArr = array();
	while($getRequiredRow = mysql_fetch_assoc($getRequiredResult))
	{
		$userFieldsArr[] = $getRequiredRow[element_alias];
		$userFieldsAlias[$getRequiredRow[element_alias]] = $getRequiredRow[element_alias];
		if($reqAliasArr[$getRequiredRow[element_alias]] != '')
		{
			$reqArr[] = $reqAliasArr[$getRequiredRow[element_alias]];
		}	
	}
	$reqArrStr = implode(",", $reqArr); 


	$que="select posid,postitle,deptid 
		from posdesc where posid=".$addr;
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	$jo_deptid = $row[2]; 
	$jo_name = $row[1];
	// Adding Department Permission 
	$deptAccessObj = new departmentAccess();
	$deptName = $deptAccessObj->getDepartmentName($jo_deptid);
	$deptUsrAccess = $deptAccessObj->getDepartmentUserAccess($username,$jo_deptid,"'FO'");

?>
<html>
<head>

<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CandidatesCustomTab.css">
<link href="/BSOS/css/grid.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link type="text/css" href="/BSOS/css/jquery.resize.css" rel="stylesheet" />
<link rel="stylesheet" href="/BSOS/css/multiple-select.css"/>
<link href="/BSOS/css/tooltip.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/select2_V_4.0.3.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/gigboard/gigboardCustom.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/select2_shift.css">

<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<script type="text/javascript" src="/BSOS/scripts/AkkuBi/jquery.slimscroll.js"></script>

<script language=javascript src="/BSOS/scripts/cookies.js"></script>
<script language=javascript src=/BSOS/scripts/grid.js></script>
<script language=javascript src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src=/BSOS/scripts/filter.js></script>
<script language=javascript src=/BSOS/scripts/paging.js></script>
<script language=javascript src=/BSOS/scripts/validaterresume.js></script>
<script src="scripts/validateact.js" language=javascript></script>
<script language="JavaScript" src="scripts/joborderinfo.js"></script>
<script language=javascript src="scripts/validatemarkreqman.js"></script>
<script language=javascript src="scripts/validateorder.js"></script>
<script language=javascript src="scripts/validatejoborder.js"></script>
<script language=javascript src="scripts/validatesup.js"></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/common.js"></script>
<script src="/BSOS/scripts/common_ajax.js"></script>
<script language=javascript src="/BSOS/scripts/validatecheck.js"></script>
<script language=javascript src=scripts/validatenewsubmanage.js></script>
<script language=javascript src=/BSOS/scripts/commonact.js></script>
<script language="JavaScript" src="scripts/skillsmenu.js"></script>
<script language="JavaScript" src="scripts/validateskill.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script type="text/javascript" src="/BSOS/scripts/OutLookPlugInDom.js"></script>
<script type="text/javascript" src="scripts/validatejb.js"></script>
<script src="/BSOS/scripts/vinterviews.js"></script>

<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>

<script type="text/javascript" src="/BSOS/scripts/crmNextPrev.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery.resize.js"></script>
<script type="text/javascript" src="/BSOS/scripts/eraseSessionVars.js"></script>
 <script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
 <!-- loads jquery & jquery modalbox END --> 
 <link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<!-- loads jquery & jquery modalbox -->
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.min.js"></script>
<!-- loads some utilities (not needed for your developments) -->
<link rel="stylesheet" type="text/css" href="/BSOS/css/shift_schedule/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/shift_schedule/jquery-ui.structure.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/shift_schedule/jquery-ui.theme.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/shift_schedule/schCalendar.css">

<!-- loads jquery ui -->
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery-ui-1.11.1.js"></script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/schCal_timeframe.js"></script>

 <!-- Perdiem Shift Scheduling -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/perdiem_shift_sch/perdiemShifts.css">
<script type="text/javascript" src="/BSOS/scripts/perdiem_shift_sch/PerdiemShiftSch.js"></script>

<?php
if (!$deptUsrAccess && $module =="CRM") {
	$deptAlertMsg = $deptAccessObj->displayPermissionAlertMsg($deptName); 
	?>
	<script type='text/javascript'> 
	alert("<?php echo $deptAlertMsg;?>");
	window.close();
	</script>
	<?php exit(); 			
} 

?>

<script type="text/javascript">
var questionnaire_itm_enabled = "<?php echo JOB_QUESTIONNAIRE_ENABLED; ?>";
// Open submission popup from update status popup for IE
function open_submissions_popup(url) 
{
	$().modalBox('close');
	$().modalBox({'html':'<div id="attribute-selector" style="margin-left:-300px !important; margin-top:-150px !important; left:40% !important; top:30% !important; position: fixed;  "><img id="preloaderW" src="/BSOS/images/preloader.gif" ><div class="scroll-area"><div class="scroll-pane"><iframe id="trisubmissions" src="'+url+'" border="0" width="100%" height="300" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%;overflow:hidden;height:550px;position:relative;top:0px;left:0px;"></iframe></div></div></div>'});
	$("#modal-wrapper").addClass("JoAssignmodal-wrapper");
	$("#attribute-selector .scroll-area").css({height: 550,width: 780});
}

function OLCampagin()
{
	DynCls_Ajax_result("/include/Outlook_Ajax_resp.php?rtype=BROADCAST",'BROADCAST','&RecSno=0',"OutlookProc()");
}

function OutlookProc()
{
	if(DynCls_Ajx_responseTxt.indexOf("***OutLookLogOut***")>0)
	{
		var LogMsg=DynCls_Ajx_responseTxt.split("|^^^***OutLookLogOut***^^^|");
		alert(LogMsg[1]);
	}
	else
	{
		dobroadcast();
	}
}

function show_skillDropDownList (e) {
	e.preventDefault();
	$(".skillDropDownList").show();
	return;
}

function hide_skillDropDownList (e) {
	e.preventDefault();
	setTimeout(function()
    {
    	$(".skillDropDownList").hide();
    }, 5000);
	
	return;
}
function modalBoxCloseandCancel(){
    parent.top.modalBoxClose(); 
}

function modalBoxClose()
{
    $().modalBox('close');
}


</script>

<style type="text/css">
#leftscroll{height:100%; width:60%; float:left; border-right:0px;}
#scrolldisplay{width:auto; height:100%;}
.allnotes-dropdown{ margin:3px 0px}
.summaryform-bold-title{ font-size:12px;}
.summaryform-formelement{ font-size:12px;}
.panel-table-content{ cursor:pointer}
.global-help-text{
font-size:12px !important;
}
.crmsummary-content-table td{
font-size:12px !important;
}
#change1 span{
font-size:12px !important;
}
#view-all-lnk10 span, #view-all-lnk11 span, #view-all-lnk12 span, #change7 span, #change1 span, #change12 span, #change3 span, #change13 span, #change5 span, #change6 span{
font-size:12px !important;
}
.crmsummary-skillname, .crmsummary-skillname b, .crmsummary-skillname font{
font-size:12px !important;
}
.skillHedNew ul{
	background: #fff none repeat scroll 0 0;
    border: 1px solid #484848;
    margin: 0;
    padding: 0;
}
.skillHedNew ul li {
	border-bottom: 1px solid #484848;
    line-height: 21px;
    list-style: outside none none;
    padding: 2px 10px;
    cursor: pointer;

}
.skillHedNew ul li:hover {
	color: #3eb8f0;

}
.skillDropDownList{
	position:absolute;
	top:18px;
	left:55px;
	display:none;
}
@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
.panel-table-header th{text-align: left !important;}
}

.assignPerdiemReAssignModal-wrapper{position: fixed !important; width:1100px !important; height: 600px !important; margin-left: -550px !important; margin-top: -300px !important;}
.assignPerdiemReAssignModal-wrapper .scroll-area{width: 1100px !important;}

body.perdiemnoscroll {
    overflow: hidden;
}
.smshiftdivcontainer {
    width: auto !important;
}
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
	echo ".innerdivstyle{width:auto; height:inherit; overflow:auto;}\n";
else
	echo ".innerdivstyle{height:100%; overflow:auto;}\n";
	/*removed width:100% regarding IE10 Compatible if any one have problem please add width:100% */
?>
@media screen and (-webkit-min-device-pixel-ratio:0) {	
	/*.innerdivstyle{ height:100% !important;  overflow-y:auto !important; overflow-x:hidden !important;}*/
	#leftscroll, #scrolldisplay { overflow-y:auto !important; overflow-x:hidden !important;  }
	::i-block-chrome, #leftscroll { width:58% !important; overflow-y:auto !important; overflow-x:hidden !important;  } 
	  
}
.errorLabel { background-color: #ffff33; }
.summarylongtext table,  .summarylongtext spna, .summarylongtext div{ width:100% !important}

/* Styles for modal box - submit*/
.alert-w-chckbox-chkbox-group-exe-moz
{
       border: none !important;
       height: auto !important;
       margin-top:0px !important;
}
.alert-w-chckbox-chkbox-content-group-exe
{
       padding: 0px !important;
}
.modalDialog_contentDiv
{
       height: auto !important;
       padding-bottom: 10px !important;
}
.alert-ync-container
{
       box-shadow:none !important;
}
.alert-ync-container .alert-ync br
{
       display: none !important;
}
.alert-cntrbtns-group-exe
{
       width:130px !important;
       padding-top: 5px !important;
       margin: 0px auto !important;
}
#custom_modal_box .smshiftdivcontainer
{
       margin-right: 5px !important;
}
.crmsummary-navtop a{ padding:8px 4px;}
a.tooltip{position: relative;}
a.tooltip span {
    background-color: #ffffff;
    border: 1px solid #a3a3a3;
    border-radius: 3px;
    box-shadow: 0 0 5px 0 #777777;
    opacity: 1;
    white-space: normal;
    left: 10px;
    width: 250px !important;
}
#candpanelid tr:last-child td .tooltip span{
	bottom: 0;
    left: inherit;
    right: 16px;
    top: inherit;
}
</style>
</head>

 <?php
 //Admin Jobposting-url-Change
 if($jobposprw=='yes'){
     ?>
<style type="text/css">
.ui-resizable-handle{ display:none !important}
</style>     
<body onFocus="displayPrevNext('jobman.php');">
   <?php }else{ ?>
    <body onFocus="displayPrevNext('reqman.php');">
      <?php  } ?>
<script language="JavaScript1.2">mmLoadMenus();</script>
<form method=post name='conreg' id='conreg'>
	<input type='hidden' id='PageFrmHVal' name='PageFrmHVal' value='<?=$reqArrStr?>'/>
  <input type='hidden' name='chkAuth' id ='chkAuth' value ="<?=$chkAuth?>" />	
  <input type=hidden name="panel_type" id="panel_type" />
  <input type=hidden name="cand_subbid" id="cand_subbid" value='' />
  <input type=hidden name="aa" id="aa" value='' />
  <input type=hidden name="mode" id="mode" value='' />
   <input type=hidden name="candrn" id="candrn" value="<?php echo $candrn; ?>">
   <!-- From Admin->Joposting and CRM->joborders  -->
  <input type=hidden name="Ajobprewid" id="Ajobprewid" value="<?php echo $posid; ?>">
  <input type=hidden name="jobposprw" id="jobposprw" value="<?php echo $jobposprw; ?>">
  <input type=hidden name="admaddr" id="admaddr" value="<?php echo $addr1 ;?>"> 
  
  <input type=hidden name="panel_frm" id="panel_frm" value='' />
  <input type=hidden name="frm_module" id="frm_module" value='<?php echo $module;?>' />

	<div style="width:100%;overflow:hidden">
	  <div id="sumNav" style="display:inline-block !important;">&nbsp;
               <?php //Admin Jobposting-url-Change
               if($jobposprw=='yes'){  ?>
              <input class="sumBtn" type="button" name="sprev" id="sprev" value="<< Prev" onClick="javascript:prevGridRec(<?=$totcolumns?>,'jobman.php');">&nbsp;&nbsp;&nbsp;&nbsp;
              <input class="sumBtn" type="button" name="snext" id="snext" value="Next >>" onClick="javascript:nextGridRec(<?=$totcolumns?>,'jobman.php');">&nbsp;
               <?php }else{ ?>
               <input class="sumBtn" type="button" name="sprev" id="sprev" value="<< Prev" onClick="javascript:prevGridRec(<?=$totcolumns?>,'reqman.php');">&nbsp;&nbsp;&nbsp;&nbsp;
              <input class="sumBtn" type="button" name="snext" id="snext" value="Next >>" onClick="javascript:nextGridRec(<?=$totcolumns?>,'reqman.php');">&nbsp;
             <?php } ?>
          </div>
		<table width=100% cellpadding=0 cellspacing=0 border=0 align="center">
		<tr>
				<td>
					<div id="grid_form">
						<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="white">
							<tr>
								<td valign=top align=left>
									<div class="tab-pane" id="tabPane1">							
									  <script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
									  
									  <?php
									 if(isset($ustat) && trim($ustat)=="success"){
										print "<font class='afontstyle4' style='color: red;font-family:Arial;font-size:12px;margin-left: 30%;'>&nbsp; Job Order has been updated successfully.</font>";
									  }?>
									  <div class="tab-page" id="tabPage01">
										<h2 class="tab" onClick="return valspchar(0,'edit','summary','<?php echo $module;?>');">Summary</h2>
										<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage01" ) );</script>
                                                                                
                                                                                <!-- From Admin->Joposting and CRM->joborders-->
                                                                                    <?php if ($jobposprw == 'yes') { ?> 
                                                                                    <style type="text/css"> 
                                                                                         #leftscroll{height:100%; width:100% !important; float:none !important;} 
                                                                                    </style> 
                                                                                <?php } ?>
										<?php require("Summary.php");?>
									  </div>
									  <div class="tab-page" id="tabPage12">
												<h2 class="tab">Edit</h2>
                                                                                                <?php if ($jobposprw == 'yes') { ?> 
                                                                                    <script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ) , "editjoborder.php?tab=1&posid=<?php echo $addr1;?>&candrn=<?php echo $candrn;?>&jobposprw=yes&jobposflag=1&module=<?=$module?>" );
												
												</script>
                                                                                <?php }else{ ?>
                                                                                    
                                                                                <script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ) , "editjoborder.php?tab=1&posid=<?php echo $posid;?>&candrn=<?php echo $candrn;?>&module=<?=$module?>" );
												
												</script>    
                                                                              <?php  } ?>
												
									  </div>
                                                                          <?php if($jobposprw=='yes'){ ?>
                                                                         <div class="tab-page" id="tabPage13">
												<h2 class="tab">Job Posting Preview</h2>
                                                                                               <script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage13" ) , "/BSOS/Admin/Jobp_Mngmt/Joborder/joborderpreview.php?addr=<?php echo $addr1;?>&candrn=<?php echo $candrn;?>" );</script>
									                       
                                                                         </div>
                                                                          <?php }  ?>
                                                                          
									 <!-- <div class="tab-page" id="tabPage12">
										<h2 class="tab" onClick="return valspchar(1,'edit','summary');">Edit</h2>
										<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage12" ) );</script>
									  </div> -->
									</div>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
	</div>
  <script>tp1.setSelectedIndex(0);</script>
</form>

<form name="markreqman" id="markreqman">
<input type=hidden name='tskills' id='tskills' value="<?php echo dispfdb(str_replace("-","",$skillsnames));?>">
<input type=hidden name='candrn' value="<?php echo $candrn;?>">
<input type=hidden name='tskillsdisplay' id='tskillsdisplay' value="<?php echo $skillnamedisplay;?>">
<input type=hidden name='tskillsdisplayID' id='tskillsdisplayID' value="<?php echo $skillnamedisplayID;?>">
</form>

<script type="text/javascript" src="/BSOS/scripts/jquery.resize.js"></script>

<script type="text/javascript">
	var sourceID = document.getElementById("sourcid").value;
	var rtype = document.getElementById("sourctype").value;
	var cookiecheck = getCookies("notesAssChk");
	var module = document.getElementById("frm_module").value;
	document.getElementById("notesAssChk").checked = getCookies("notesAssChk")==1? true : false;

	if(cookiecheck==1)
		{
		var url         = "/BSOS/Include/getSummaryNotes.php?module="+module;	
		var assCheck    = true;
        if(rtype == "candidate"){
			var NotesDisBoxName = "dispNotesNew";
			var NotesHideBoxName = "dispNotes";
		} else {
			var NotesDisBoxName = "allNotesNew";
			var NotesHideBoxName = "allNotes";
		}		
		compHierarchyFlag = true;
		var content     = "noteAjaxFlag=true&sourceID="+sourceID+"&showAssociate="+assCheck+"&rtype="+rtype+"&compHierarchyFlag="+compHierarchyFlag;
		//alert(content)
		var funname     = "returnSummaryNotesDisplay('"+NotesDisBoxName+"', '"+NotesHideBoxName+"')";
		//document.getElementById("dynsndiv").style.display   = "none";
		DynCls_Ajax_result(url,rtype,content,funname);
		}
$(function() { 
	$("#leftscroll").resizable({ handles: 'e'}); 
});
String.prototype.replaceAll = function(from,to)
{
	var temp = this;
	var index = temp.indexOf(from);
	while(index != -1)
	{
		temp = temp.replace(from,to);
		index = temp.indexOf(from);
	}
	return temp;
}

if(window.opener)
{
	try 
	{
		var keword="";
		parwin = window.opener;
		if(parwin.document.searchfrm.keywords)
		{
			keyword = parwin.document.searchfrm.keywords.value.toUpperCase();
			notesopt = parwin.document.searchfrm.notesopt;
			if(keyword!="")
			{
				profilecount = document.conreg.profilecount;
				notescount = document.conreg.notescount;

				keywords = keyword.match(/("[^"]*")|([^\s"]+)/g);
				for(i=0;i<keywords.length;i++)
				{
					if(keywords[i]!="AND" && keywords[i]!="OR")
					{
						if(keywords[i].indexOf("*") != '-1'){
							keywords[i] = keywords[i].replaceAll('*','');
						}
						if(notesopt.value=="profile" || notesopt.value=="both")
						{
							$('#profilepane').highlight(keywords[i].replaceAll('"',''),profilecount,0);
							if(profilecount.value>0)
								document.getElementById("profilepane").className = "crmsummary-highlight";
						}

						if(notesopt.value=="notes" || notesopt.value=="both")
						{
							$('#allNotes').highlight(keywords[i].replaceAll('"',''),notescount,0);
							if(notescount.value>0)
								document.getElementById("allNotes").className = "crmsummary-highlight";
						}
					}
				}
			}
		}
	}
	catch(e){}	
}

window.onload   = setSummaryHeight;
window.onresize = setSummaryHeight;

function setSummaryHeight()
{
	try{
		//document.getElementById("mainpane").height = (parseInt(document.body.clientHeight) - 110);
		$("#leftscroll").css({height:"100%"});
		$("#scrolldisplay").css({height:"100%"});
	}
	catch(e){}
}

			//------------ This function will prevent user to close browser if the notes field has any values to save
	window.onbeforeunload = function(e) {
            var elem = document.getElementById("notes");
             if(typeof elem !== 'undefined' && elem !== null){

		if (document.getElementById("notes").value != "")
			{
				document.getElementById('notes').style.backgroundColor = "#ffff33";
				if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))  {
						e = window.event || e; 
						if ((e.clientX < 0) || (e.clientY < 0) || window.event.altKey == true)
							{ e.returnValue = 'You have notes that is not saved. \nClick on "Leave this " to close the window without saving notes. \nCick on "Stay on this page" to go back and save the notes.'; }
				} 
				else
					return 'You have notes that is not saved. \nClick on "Leave this " to close the window without saving notes. \nCick on "Stay on this page" to go back and save the notes.';
			}
                    }
	}
	function windclose(){
		var mod_const	= "<?=$candrn?>";
		var elem = document.getElementById("notes");
		if(typeof elem !== 'undefined' && elem !== null){
			if (document.getElementById("notes").value != "")
			{
					document.getElementById('notes').style.backgroundColor = "#ffff33";
					if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))  {
							var conf = confirm('You have notes that is not saved. \nClick on "OK " to close the window without saving notes. \nCick on "Cancel" to go back and save the notes.');

							if (conf == true){

								eraseSessionVars("joborders", mod_const);
								window.close();
							}
					}
					else {
						window.onbeforeunload = function(e) {
							return "You have notes that is not saved.";
						}

						eraseSessionVars("joborders", mod_const);
						window.close();
					}

				}else {

					eraseSessionVars("joborders", mod_const);
					window.close();
				}
			}else {

				eraseSessionVars("joborders", mod_const);
				window.close();
			} 
                   
		}
	//------------ Function of note ends here
(function($) {
   $(document).ready(function() {
 		
       $('a').filter(function() {
           return (/^javascript\:/i).test($(this).attr('href'));
       }).each(function() {
           var hrefscript = $(this).attr('href');
           hrefscript = hrefscript.substr(11);
           $(this).data('hrefscript', hrefscript);
       }).click(function() {
           var hrefscript = $(this).data('hrefscript');
           eval(hrefscript);
           return false;
       }).attr('href', '#');
 
   });
})(jQuery);

<?php
	if(isset($opendivs) && !empty($opendivs))
	{
		$getOpendivs = explode(",",$opendivs);
		if(count($getOpendivs)>0)
		{
			foreach($getOpendivs as $odid)
			{
				if($odid==9)
				{
					?>
                                classToggle(mntcmnt<?=$odid?>,'DisplayBlock','DisplayNone',mntcmnt<?=$odid?>,<?=$odid?>,'<?=$module?>');
				try {
                                   classToggle(joset,'DisplayBlock','DisplayNone','',9,'<?=$module?>');
                                } catch (e) {
                                    console.log('An error has occurred: '+e.message);
                                }
                                <?php
				}else if($odid=='skillRow'){ ?>
                                    classToggle(skillRow,'DisplayBlock','DisplayNone','skillRow',13,'<?=$module?>');
                                <?php
				}else if($odid=='toggle3'){ ?>
                                    classToggleNew(toggle3,'DisplayBlock','DisplayNone',3);
                                <?php 
				}else if($odid=='toggle1'){ ?>
                                    classToggleNew(toggle1,'DisplayBlock','DisplayNone',1);
                                <?php }
				else if($odid=='toggle6'){ ?>
                                    classToggleNew(toggle6,'DisplayBlock','DisplayNone',6);
                                <?php }
				else if($odid=='toggle7'){ ?>
                                    classToggleNew(toggle7,'DisplayBlock','DisplayNone',7);
                                <?php }
                                else if($odid=='scheduleRow'){ ?>
                                    classToggleNew(scheduleRow,'DisplayBlock','DisplayNone',12);
                                <?php }
				else if($odid=='toggle5'){ ?>
                                    classToggleNew(toggle5,'DisplayBlock','DisplayNone',5);
                                <?php } else
				{
                                    if($jobposprw!='yes'){ ?>
				 classToggle(mntcmnt<?=$odid?>,'DisplayBlock','DisplayNone',mntcmnt<?=$odid?>,<?=$odid?>,'<?=$module?>');
                                <?php }
				}
			}
		}
	}
?>
</script>
<script>window.focus();</script>
</body>
</html>
