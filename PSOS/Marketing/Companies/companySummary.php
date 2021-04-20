<?php	
	$CompSno=$addr;
	require("global.inc");
        if(isset($_COOKIE["company_summary_opendivs"])) {
            $opendivs = $_COOKIE["company_summary_opendivs"];
        } else {
            $opendivs= '1,2,7';
        }
	require("dispfunc.php");
	require("userDefine.php");
	require_once($akken_psos_include_path.'getSummaryNotes.php');
	$addr=$CompSno;
	require_once("multipleRatesClass.php");
	require_once("custom_grid_functions.php");
	$custom_grid_function_obj = new CustomGridFunctions();
	$grdi_details = $custom_grid_function_obj->getGridName("companies",$username);
	
	if(!empty($grdi_details))
		$totcolumns = count($custom_grid_function_obj->asFields("companies", $username))+1;
	else
		$totcolumns = 11;

	$module = isset($_GET['module'] )? $_GET['module']: '';

	if($_GET['module'] == 'CRM'){
		$module = 'CRM';

	}else{
		$module = 'Admin_Companies';
	}

	$que="select sno,cname,deptid 
		from staffoppr_cinfo where sno=".$addr;
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	$com_deptid = $row[2]; 
	$com_name = $row[1];
	// Adding Department Permission 
	$deptAccessObj = new departmentAccess();
	$deptName = $deptAccessObj->getDepartmentName($com_deptid);
	$deptUsrAccess = $deptAccessObj->getDepartmentUserAccess($username,$com_deptid,"'FO'"); 


	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

	$comid='com'.$addr;
	$module_type_appoint="Contacts->Companies";

	//get the manage information
	$CompSrc=array();
	$CompType=array();
	$Manage_Sql="select sno,name,type from manage where type in('compsource','comptype')";
	$Manage_Res=mysql_query($Manage_Sql,$db);
	while($Manage_Data=mysql_fetch_row($Manage_Res))
	{
		if($Manage_Data[2]=='comptype')
			$CompType[$Manage_Data[0]]=$Manage_Data[1];
		else
			$CompSrc[$Manage_Data[0]]=$Manage_Data[1];
	}


	asort($CompType);
	asort($CompSrc);

	//get the company information

	$Comp_Sql="SELECT staffoppr_cinfo.sno,approveuser,ceo_president,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears, nemployee,com_revenue,federalid,staffoppr_cinfo.status,accessto,phone,fax,industry,keytech,owner,muser,mdate,staffoppr_cinfo.parent,department,siccode,csource,ticker ,vcount,crmcompany,cdate,".tzRetQueryStringDTime("mdate","Date","/")." mod_date,".tzRetQueryStringDTime("cdate","Date","/")." created_date,compowner,compsummary,compbrief,compstatus,acc_comp,alternative_id,phone_extn,department.deptname FROM  staffoppr_cinfo
    LEFT JOIN department ON staffoppr_cinfo.deptid=department.sno 
    WHERE staffoppr_cinfo.sno=".$addr." ";
	
	//echo $Comp_Sql;
	//exit;
	
	$Comp_Res=mysql_query($Comp_Sql,$db);
	$Comp_Data=mysql_fetch_array($Comp_Res);
	$compdata="";
	$empty="";
	
	//For getting the multiple rates
	$ratesObj = new multiplerates();
	$mode_rate_type = "company";
	$type_order_id = $addr;
	
	$merge_sql = ' SELECT MAX( sno ) FROM contact_doc WHERE con_id = "com'.$addr.'" AND title LIKE \'merged_companies.rtf\'';
	$merge_res = mysql_query($merge_sql);
	$merge_row = mysql_fetch_row($merge_res);
	$mergeRecord = $merge_row[0] ? '<a href="#" style="text-decoration: underline;color: #0099ff;font-size:7.0pt;" onclick="javascript:editWin(\'editdoc.php?addr=com'.$addr.'&sno='.$merge_row[0].'&con_id=com'.$addr.'&hidemodule='.$module.'\')" >Yes</a>' : 'No';
	
	$accto=$Comp_Data[19];

	//The  Top Company Details
	$Top_Comp_Details="";
	$Comp_Main_Addr="";

	$s_status=$Comp_Data['compstatus'];
	$comp_Share_Type='Private';

	if($Comp_Data['accessto']==$username)
		$comp_Share_Type='Private';

	else if($Comp_Data['accessto']=='ALL')
		$comp_Share_Type='Public';


	else if(strpos("*".$Comp_Data['accessto'],",")>0)
		$comp_Share_Type='Shared';

	$comp_vcount=$Comp_Data['vcount'];
	 
	//this is the query for getting  all the active users
	require_once($akken_psos_include_path.'class.getOwnersList.php');
	$ownersObj = new getOwnersList();
	$Users_Array = $ownersObj->getOwners();
	
	//Users Query
	$users_que="SELECT username,name FROM users";
	$res_users=mysql_query($users_que,$db);
	$Array_Users=array();
	while($fetch_data=mysql_fetch_row($res_users))
	{
		$Array_Users[$fetch_data[0]]=$fetch_data[1];
	}
	
	
	$User_nos=implode(",",array_keys($Array_Users));
	$uersCnt=count($Users_Array);

/* ... .. Raj.. Checking server to change style for FF .. Raj .. ... */
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1){
		$rightflt = "style= 'width:26px;'";
		$rightflt3 = "style=''";
	}//End of if(To know the server)


	// Hubspot link formation
	$hs_search_url = "";
	$hs_link_qry = "select company_id from hubspot_companies where hubspot_status='Active' and respective_sno='".$addr."' order by mdate desc limit 1";
	$res_hs_link_qry = mysql_query($hs_link_qry, $db);
	if(mysql_num_rows($res_hs_link_qry) > 0)
	{
	while($hs_link_qry_row = mysql_fetch_row($res_hs_link_qry))
	{
	    $hs_cmp_id = $hs_link_qry_row[0];
	}
        
	$hs_link_qry2 = "SELECT portal_id FROM hubspot_account WHERE STATUS='A' ";
	$res_hs_link_qry2 = mysql_query($hs_link_qry2, $db);
	if(mysql_num_rows($res_hs_link_qry2) > 0)
	{
	    $hs_link_qry_row2 = mysql_fetch_row($res_hs_link_qry2);
	    $hs_portal_id = $hs_link_qry_row2[0];
	}
	$hs_search_url = "https://app.hubspot.com/contacts/".$hs_portal_id."/company/".$hs_cmp_id;
	}
?>
<html>
<head>
<title>Company <?=stripslashes($Comp_Data['cname'])!=''?" - ".html_tls_specialchars(stripslashes($Comp_Data['cname']),ENT_QUOTES):'';?></title>
<link rel="stylesheet" href="/BSOS/css/fontawesome.css"/>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/crm-summary.css" />
<link rel="stylesheet" type="text/css" href="/BSOS/css/crm-editscreen.css" />
<link rel="stylesheet" type="text/css"  href="/BSOS/css/candidatesInfo_tab.css"/>
<link type="text/css" rel="stylesheet" href="/BSOS/css/CandidatesCustomTab.css">
<link rel="stylesheet" href="/BSOS/css/multiple-select.css"/>
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link type="text/css" href="/BSOS/css/jquery.resize.css" rel="stylesheet" />
<link type="text/css" rel="stylesheet" href="/BSOS/css/calendar.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tooltip.css">
<script>var ac_fs_server_host ="<?php echo $_SERVER['HTTP_HOST'];?>";</script>
<script type="text/javascript" src="/BSOS/scripts/AC_FS_Cookie.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery.highlight.js"></script>
<script type="text/javascript" src="/BSOS/scripts/cookies.js"></script>
<script type="text/javascript" src="/BSOS/scripts/crmNextPrev.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery.resize.js"></script>
<script type="text/javascript" src="/BSOS/scripts/eraseSessionVars.js"></script>
<script src="/BSOS/scripts/common_ajax.js"></script>
<script language=javascript src=/BSOS/scripts/validatecommon.js></script>
<script language=javascript src=/BSOS/scripts/tabpane.js></script>
<script language=javascript src="scripts/Allacts.js"></script>
<script language=javascript src=/BSOS/scripts/validatecheck.js></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script type="text/javascript" src="/BSOS/scripts/OutLookPlugInDom.js"></script>
<script language=javascript src=scripts/companySummary.js></script>
<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/multiplerates.js"></script>

<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<script type="text/javascript" src="/BSOS/HubSpot/scripts/HubSpot.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<?php
if (!$deptUsrAccess && $module =="CRM") {
	$deptAlertMsg = $deptAccessObj->displayPermissionAlertMsg($deptName);
?>
<script type='text/javascript'> 
alert("<?php echo $deptAlertMsg;?>");
window.close();
</script>
<?php exit(); }  ?>
<style>
    #view-all-lnk10 span, #view-all-lnk11 span, #view-all-lnk12 span, #change7 span, #change1 span, #change12 span, #change3 span, #change13 span, #change5 span, #change6 span{
font-size:12px !important;
}
.crmsummary-content-title-leftborder{
font-size:12px !important;
}
.global-help-text{
font-size:12px !important;
}
.crmsummary-jocomp-table .crmsummary-content-table td, .crmsummary-jocomp-table td{
font-size:12px !important;
}
.summaryform-bold-title{
  font-size:12px !important;
 }
.crmsummary-lnk { font-size: 12px !important; text-decoration: none; color: #0273fd; vertical-align:middle;}
#leftpane{width:60%; float:left;}
#scrolldisplay{width:auto;}

 /* this is the new style we added for newly introduced Preferences settings Expand and collapse */
.ToDoPreference .cl-txtlnk {
    border: 2px solid #3eb8f0 !important;
    border-radius: 100% !important;
    box-sizing: border-box !important;
    float: inherit !important;
    height: 22px !important;
    padding: 1px 0 0 1px !important;
    text-align: center !important;
    width: 22px !important;
}
.ToDoPreference .cl-txtlnk i {
    color: #3eb8f0 !important;
    font-weight: bold !important;
}
.opcl-btnleftside_pref{
	background: #474c4f none repeat scroll 0 0;
    display: block;
    float: left;
    height: 10px;
    margin-bottom: 1px;
    margin-left: 5px;
    margin-top: 1px;
    padding: 0;
}
.opcl-btnrightside_pref {
    background: #474c4f none repeat scroll 0 0;
    display: block;
    float: left;
    height: 10px;
    line-height: 5pt;
    margin-bottom: 1px;
    margin-top: 1px;
    padding: 0;
}

.ToDoPreference .cl-txtlnk i.fa-angle-down {
    padding: 1px 0px 0 1px !important;
	margin-left:-5px !important;
}
.prefBorderCls{
	background: #ffffff none repeat scroll 0 0;
    border: 1px solid #cccccc;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    margin-left: 4px;
    margin-right: 4px;
    margin-top: -3px;
    padding: 5px;
}

a.tooltip{position: relative;}
a.tooltip span.tooltipReasonCode {
    background-color: #ffffff;
    border: 1px solid #a3a3a3;
    border-radius: 3px;
    box-shadow: 0 0 5px 0 #777777;
    opacity: 1;
    white-space: normal;
    left: inherit;
    right: 10px;
    width: 250px !important;
}
#CandidatesRow tr:last-child td .tooltip span{
	bottom: 0;
    left: inherit;
    right: 16px;
    top: inherit;
}
.akkencustomicons { margin: 0px; }
.akkencustomicons ul li { border: none; }
.akkencustomicons ul li:hover { border: none; }
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
	echo ".innerdivstyle{width:auto;overflow-x:auto; overflow-y:hidden;}\n";
else
	echo ".innerdivstyle{width:100%;overflow-x:auto; overflow-y:hidden;}\n";
	
	function setcompanyinfo($Comp_Data,$comp_Share_Type,$addr)
	{	


		global $db;
		$Comp_query="SELECT staffoppr_cinfo.sno,
							staffoppr_cinfo.bill_req,
							staffoppr_cinfo.service_terms,
							staffoppr_cinfo.dress_code,
							staffoppr_cinfo.tele_policy,
							staffoppr_cinfo.smoke_policy,
							staffoppr_cinfo.parking,
							staffoppr_cinfo.park_rate,
							staffoppr_cinfo.directions,
							staffoppr_cinfo.culture,
							staffoppr_cinfo.bill_contact,
							staffoppr_cinfo.bill_address,
							staffoppr_cinfo.customerid,
							staffoppr_cinfo.deptid
							FROM  staffoppr_cinfo
    						LEFT JOIN department ON staffoppr_cinfo.deptid=department.sno 
    						WHERE staffoppr_cinfo.sno=".$addr."";

			$Comp_data=mysql_query($Comp_query,$db);
			$Comp_result=mysql_fetch_array($Comp_data);

			$parkingValues="";

			$parkingsplit = explode("|",$Comp_result[6]);
			$parkingdata="";
			$parkingdata=implode("-",$parkingsplit);

		$compdata=$Comp_Data[3]."|".$Comp_Data[4]."|".$Comp_Data[5]."|".$Comp_Data[6]."|".$Comp_Data[7]."|".$Comp_Data[8]."|".$Comp_Data[9]."|".$Comp_Data[10]."|".$Comp_Data[11]."|".$Comp_Data[12]."|".$Comp_Data[13]."|".$Comp_Data[14]."|".$Comp_Data[15]."|".$Comp_Data[16]."|".$Comp_Data[17]."|".$Comp_Data[22]."|||".$Comp_Data[20]."|".$Comp_Data[21]."||||".$Comp_Data[29]."|".$Comp_Data[31]."|".$Comp_Data[30]."||".$Comp_Data[27]."||editcontact|".$Comp_Data[40]."|".$Comp_Data[24]."|".$comp_Share_Type."|".$empty."|".$Comp_Data[37]."|".$Comp_Data[39]."|".$Comp_Data[38]."|".$Comp_Data[23]."||".$Comp_result[1]."|".$Comp_result[2]."|".$Comp_result[3]."|".$Comp_result[4]."|".$Comp_result[5]."|".$parkingdata."|".$Comp_result[8]."|".$Comp_result[9]."||".$Comp_result[10]."|com-".$Comp_result[11]."|".$Comp_result[12]."|".$Comp_Data[42]."|".$Comp_Data[43]."|".$Comp_Data[28]."||".$Comp_result[13];

		return $compdata;
	}


?>
@media screen and (-webkit-min-device-pixel-ratio:0) { /* hacked for chrome and safari */
 	.innerdivstyle{width:auto; overflow-x:auto; overflow-y:hidden;}
}
#TODO{width: 125px;}
#tododate{width: 98px;}


a.hubspot{margin-right:3px;vertical-align:bottom;}
.tooltip img{margin: 0px 2px;}

/*tooltip style*/
.tooltip {position: relative; display: inline-block;}
.tooltip .tooltiptext {visibility: hidden;width: 200px;background-color: #e9e9e9;color: #484848;text-align: center;border-radius: 6px;padding: 5px 0;position: absolute;z-index: 1;top: 180%;left: 5%;margin-left: -15px; font-weight:normal; font-size:12px; line-height:18px;}
.tooltip .tooltiptext:after {content: "";position: absolute;bottom: 100%;left: 5%;margin-left: 0px;border-width: 5px;border-style: solid;border-color: transparent transparent #e9e9e9 transparent;}
.tooltip:hover .tooltiptext {visibility: visible;}
</style>

<script type="text/javascript">
$(function() { $("#leftpane").resizable({ handles: 'e'}); });
function modalBoxClose()
{
	$().modalBox('close');
}
function modalBoxCloseandCancel(){
	parent.top.modalBoxClose();	
}
</script>
</head>

<body>

<form name='compreg' id='compreg' method="post">
  <input type=hidden name='candrn' id='candrn' value="<?php echo $candrn;?>">
  <input type=hidden name='Rnd' id='Rnd' value="<?php echo $Rnd?>">
  <input type=hidden name='compid' id='compid' value="<?php echo $comid?>">
  <input type=hidden name='opprinfo' id='opprinfo' value="">
  <input type=hidden name='url' id='url' value="" />
  <input type=hidden name='summarypage' id='summarypage' value="summary">
  <input type=hidden name='addr' id='addr' value="<?=$addr?>">
  <input name="companyinfo" type="hidden" value="<?php echo setcompanyinfo($Comp_Data,$comp_Share_Type,$addr); ?>">
  <input type=hidden name='accto' value="<?=$accto?>">
  <input type=hidden name='chk_comp' value="<?=$chk_comp?>">
  <input type=hidden name='typecomp'>
  <input type=hidden name='contSno'>

  <input type=hidden name='selratesdata' id='selratesdata' value='' />
  <input type=hidden name='cap_separated_custom_shift_rates' id='cap_separated_custom_shift_rates' value='' />
  <input type=hidden name='changeowner' id='changeowner' value='' />
  <input type=hidden name='emplist' id='emplist' value='' />
  <input type=hidden name='ownerVal' id='ownerVal' value="<? echo $Comp_Data['owner']?>">
  <input type=hidden name='shareVal' id='shareVal' value="<? echo  $comp_Share_Type?>">
  <input type=hidden name='openDivs' id='openDivs' value='' />
  <input type=hidden name='newcust' id='newcust' value="<? echo $newcust?>">
  <input type=hidden name='comp_stat' id='comp_stat' value="<? echo $comp_stat?>">
  <input type=hidden name='par_stat' id='par_stat' value="<? echo $par_stat?>">
  <input type="hidden" name="companyMode" id="companyMode" value="Edit">
  <input type="hidden" name="hdnAssocString" value="" id="hdnAssocString" />
  <input type="hidden" name="hdnContAssoc" value="" id="hdnContAssoc" />
  <input type="hidden" name="hdnCompAssoc" value="" id="hdnCompAssoc" />
  <input type="hidden" name="hdnCandAssoc" value="" id="hdnCandAssoc" />
  <input type="hidden" name="hdnJobAssoc" value="" id="hdnJobAssoc" />
  <input type="hidden" name="hdnEmpAssoc" value="" id="hdnEmpAssoc" />
  <input type="hidden" name="profilecount" value="" id="profilecount" />
  <input type="hidden" name="notescount" value="" id="notescount" />
  <input type=hidden name='frm_module' id='frm_module' value="<? echo $module?>">
	<div style="width:100%;overflow:hidden">
		<div id="sumNav" style="margin-right:0px;">&nbsp;<input class="sumBtn" type="button" name="sprev" id="sprev" value='<< Prev' onClick="javascript:prevGridRec(<?=$totcolumns?>,'Companies.php');">&nbsp;&nbsp;&nbsp;&nbsp;<input class="sumBtn" type="button" name="snext" id="snext" value='Next >>' onClick="javascript:nextGridRec(<?=$totcolumns?>,'Companies.php');">&nbsp;</div>
		<div id="mergelinks"><input class="sumBtn" type="button" name="snext" value="Remove from Merge List" onClick="javascript:removeList('<?php echo $addr;?>');">&nbsp;&nbsp;&nbsp;&nbsp;<input class="sumBtn" type="button" name="snext" value="Mark as Master" onClick="javascript:masterMark('<?php echo $addr;?>');window.close();">&nbsp;&nbsp;&nbsp;&nbsp;</div>		
			  <div id="grid_form">
				  <table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="white">
					<tr>
					  <td valign=top align=left><div class="tab-pane" id="tabPane1">
            <script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
            <div class="tab-page" id="tabPage01">
				<h2 class="tab">Summary</h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage01" ) );</script>
				<div class='crmsummary-navtop'>
					<?php
						/*Out look plugin */
						require(BSOS_INCL."OutLookEnbLinks.php");
						$OutLookObj=new EnbOutLookPluginLinks(OUTLOOK_PLUG_IN,"CRMCOMP");
						$TaskNewLink="javascript:newTask('addtask','CRMCompanies','".$module."')";
						$AppointLink="javascript:newAppoint('addappoint','CRMCompanies','".$module."')";
						$EmailLink="javascript:newMail('CRMCompanies','','".$module."')";
						  
						$LinkTaskCont=$OutLookObj->EnableAjaxLink('Task',array('AkkenId'=>$addr,''),$TaskNewLink);
						$LinkAppointCont=$OutLookObj->EnableAjaxLink('App',array('AkkenId'=>$addr,''),$AppointLink);
						$LinkEmailCont=$OutLookObj->CheckOutlookStatuseEMail(array('AkkenId'=>$addr,''),$EmailLink,'','','','');
						  
					if((chkUserPref($collaborationpref,'1')) || OUTLOOK_PLUG_IN=="Y")
					{?>
                      <a href="javascript:void(0);" onclick="newDoc('adddocument','CRMCompanies','<?=$module?>');"><i class="fa fa-file-o  fa-lg"></i>&nbsp;&nbsp;Add Document</a> <a href="javascript:newEvent('addevent','CRMCompanies','<?=$module?>')"><i class="fa fa-puzzle-piece fa-lg"></i>&nbsp;&nbsp;Create Event</a> <a href="<?php echo $LinkTaskCont;?>"><i class="fa fa-retweet fa-lg"></i>&nbsp;&nbsp;Create Task</a> <a href="<?php echo $LinkAppointCont;?>"><i class="fa fa-phone-square fa-lg"></i>&nbsp;&nbsp;Create Appointment</a> <a href="<?php echo $LinkEmailCont;?>"> <i class="fa fa-envelope fa-lg"></i>&nbsp;&nbsp;Send Mail</a> <a id='topupdate'  href="javascript:updateSummary('topUpdate')" ><i class="fa fa-clone fa-lg"></i>&nbsp;&nbsp;Update</a> <span class="textUsfloatR"><span class="akkencustomicons"><ul><li><a class="link6" href="javascript:;"><i class="fa fa-globe fa-lg"></i>Integrated Sevices<i class="fa fa-angle-down fa-lg"></i></a><ul class="bottomicons"><li><a href="javascript:HubSpotInitiate('Companies_inside');">Sync to HubSpot</a></li></ul></li></ul></span></span> <a onClick="javascript:windclose();" href="javascript:void(0);"><i class="fa fa-times fa-lg"></i>&nbsp;&nbsp;Close</a>
                      <?}else{?>
                      <a href="javascript:void(0);" onclick="newDoc('adddocument','CRMCompanies','<?=$module?>');"><i class="fa fa-file-o fa-lg"></i>&nbsp;&nbsp;Add Document</a> <a href="javascript:newEvent('addevent','CRMCompanies','<?=$module?>')"> <i class="fa fa-puzzle-piece fa-lg"></i>Create Event</a>&nbsp; &nbsp<a href="<?php echo $LinkTaskCont;?>">Create Task</a> <a href="<?php echo $LinkAppointCont;?>"> <i class="fa fa-phone-square fa-lg"></i>Create Appointment</a> <a  id='topupdate' href="javascript:updateSummary('topUpdate')">Update</a> <span class="textUsfloatR"><span class="akkencustomicons"><ul><li><a class="link6" href="javascript:;"><i class="fa fa-globe fa-lg"></i>Integrated Sevices<i class="fa fa-angle-down fa-lg"></i></a><ul class="bottomicons"><li><a href="javascript:HubSpotInitiate('Companies_inside');">Sync to HubSpot</a></li></ul></li></ul></span></span> <a onClick="javascript:windclose();" href="javascript:void(0);"><i class="fa fa-times fa-lg"></i> Close</a>
                      <?}?>
				</div>
				<div class="line-top">&nbsp;</div>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" id='summaryContentHolder' class="content-border">
				<tr>
                <td id="leftpanetd" valign="top" colspan="2">
				<div id="leftpane"><div class="innerdivstyle">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr valign=top>
                    <td>
						<div id='manageRow'>

						<!--crmsummary -settings---->
						</div><!--joset-->
                        <!--crmsummary -settings---->                       
                        <div class="paneltitle ToDoPreference"> 
                    	<span id="rightflt" <?php echo $rightflt;?>>
                        <div class="opcl-btnleftside_pref">
                          <div align="left"></div>
                        </div>
                        <div><a href="javascript:classToggle(mntcmnt7,'DisplayBlock','DisplayNone',mntcmnt7,7,'<?=$module?>')" class="cl-txtlnk" >
                          <div id='hideExp7'><i class="fa fa-angle-right fa-lg"></i></div>
                          </a></div>
                        <div class="opcl-btnrightside_pref">
                          <div align="left"></div>
                        </div>
                        </span>
		  <a href="javascript:classToggle(mntcmnt7,'DisplayBlock','DisplayNone',mntcmnt7,7,'<?=$module?>')" > 
          <span class="paneltitle-span"><i class="fa fa-cog fa-lg"></i></span>
          <span class="paneltitle-span">Preferences</span></a>
	  </div>
		   <div class="DisplayNone" id="mntcmnt7">
                          <div class="crmsummary-settings-content prefBorderCls">
							<table cellpadding="0" cellspacing="0" border="0" width="99%">
							<tr>
							<td>
							<table width="99%" cellspacing="1" cellpadding="1">				   
							<tr>
							<td width="26%">
							<label class="labels">Status:</label></td>
							<td>
	
                            <select class="dropdown" name="sstatus" id='sstatus' style='width:120px'>
                              <option value='0'>-select-</option>
                              <?php
		$Ctype_Sql="select sno,name from manage where type='compstatus' order by name";
		$Ctype_Res=mysql_query($Ctype_Sql,$db);
		while($Ctype_Data=mysql_fetch_row($Ctype_Res))
		{
			echo "<option value='$Ctype_Data[0]' ".sele($Ctype_Data[0],$s_status).">".html_tls_specialchars($Ctype_Data[1],ENT_QUOTES)."</option>";
		}?>
	</select><?php if(EDITLIST_ACCESSFLAG){ ?>&nbsp;<a href="javascript:doManage('Company Status','sstatus');"><font class='editlinkrow1'>edit list</font></a> <?php } ?>
							</td>
							</tr>
							<tr>
							<td>
							<label class="labels">Owner:</label></td>
							<td>
                            <select class="dropdown" name="ownerCell" id='owner' style='width:150px' <?if($username!=$Comp_Data['owner']) echo "disabled"; ?> >
                              <?php
	foreach($Users_Array as $UserNo=>$UName)
	{?>
                              <option value="<?=$UserNo?>" <?=sele($Comp_Data['owner'],$UserNo)?> <?php if($Comp_Data['owner']=="" && $UserNo==$username) echo 'selected'; ?>>
                              <?=html_tls_specialchars($UName,ENT_QUOTES)?>
                              </option>
                              <?}?>
                            </select>	
							</td>
							</tr>
							<tr>
							<td>
							<label class="labels">Share:</label></td>
							<td>
                            <select class="dropdown" name="share" id='share' onChange="doShare(this.value)" <?if($username!=$Comp_Data['owner']) echo "disabled"; ?> style='width:70px'>
                              <option value='private' <?=sele($comp_Share_Type,'Private')?> >Private</option>
							  <option value='share'  <?=sele($comp_Share_Type,'Shared')?>><?=($comp_Share_Type=='Shared')?'Shared':'Share'?></option>
                              <option value='public' <?=sele($comp_Share_Type,'Public')?>>Public</option>
                            </select>
							<span id="view-all-emp"><?php if(($comp_Share_Type=='Shared') && ($username!=$Comp_Data['owner']))
							{?>
								<a href="javascript:newWin('/BSOS/Marketing/Lead_Mngmt/viewShareEmp.php?Module=company&addr=<?=$addr?>','shareEmps','450','400')"><font class="editlinkrow1">view list</font></a>
								<?php }else if(($comp_Share_Type=='Shared') && ($username==$Comp_Data['owner']))
								{ ?>				
								<a href="javascript:newWin('/BSOS/Marketing/Lead_Mngmt/contactShare.php?Module=company&addr=<?=$addr?>','shareEmps','450','400')"><font class="editlinkrow1">edit list</font></a> &nbsp;&nbsp;&nbsp;
							<? }?></span>
							</td>
							</tr>
							</table>
							</td>
							 <td width="14" align="center"><img src="../../images/vline.GIF" width="1" height="60" /></td>
							 <td valign="top"><span id="editlinkrow1">&nbsp;&nbsp;<font class='editlinkrow1'><a href="javascript:editCheckList('com');">Company Checklist</a></font></span></td>
							</tr>
							</table>
								
							</div><!--crmsummary-settings-content-->
                      <!--end of manageRow-->
                        </div>
                      <div class="line3">&nbsp;</div></td>
                  </tr>

                  <tr>                  
                  <td valign=top align="center" style="padding-left:2px"><div id="profilepane">
                  <div style="text-align:left" class="resumeHeading">&nbsp;&nbsp;Merge Records&nbsp;:&nbsp;<?php echo $mergeRecord;?></div>
                  <table  class="crmsummary-contactinfo-table" border="0" cellspacing="0" cellpadding="0" id='companyInfo'  align="center">
                    <?php
	//Address1
	if($Comp_Data['address1']!="")
	{
		$Top_Row2=dispTextdb($Comp_Data['address1']);
		$Comp_Main_Addr.=dispTextdb($Comp_Data['address1']);
	}

	//Address2
	if($Comp_Data['address2']!="")
	{
		if($Top_Row2!='')
			$Top_Row2.=",&nbsp;".dispTextdb($Comp_Data['address2']);
		else
			$Top_Row2=dispTextdb($Comp_Data['address2']);
		if($Comp_Main_Addr!='')
			$Comp_Main_Addr.=",&nbsp;".dispTextdb($Comp_Data['address2']);
		else
			$Comp_Main_Addr=dispTextdb($Comp_Data['address2']);
	}

	//city
	if($Comp_Data['city']!="")
	{
		$Top_Row3=dispTextdb($Comp_Data['city']);
		if($Comp_Main_Addr!='')
			$Comp_Main_Addr.=",&nbsp;".dispTextdb($Comp_Data['city']);
		else
			$Comp_Main_Addr=dispTextdb($Comp_Data['city']);
	}
	//State
	if($Comp_Data['state']!="")
	{
		if($Top_Row3!='')
			$Top_Row3.=",&nbsp;".dispTextdb($Comp_Data['state']);
		else
			$Top_Row3=dispTextdb($Comp_Data['state']);
		if($Comp_Main_Addr!='')
			$Comp_Main_Addr.=",&nbsp;".dispTextdb($Comp_Data['state']);
		else
			$Comp_Main_Addr=dispTextdb($Comp_Data['state']);

	}

    //Zip-------For displaying in company main address
	if($Comp_Data['zip']!="")
	{
		if($Top_Row3!='')
			$Top_Row3.="&nbsp;".dispTextdb($Comp_Data['zip']);
		else
			$Top_Row3=dispTextdb($Comp_Data['zip']);
	}

    //Zip------For displaying in divisions
	if($Comp_Data['zip']!="")
	{
		if($Comp_Main_Addr!='')
			$Comp_Main_Addr.="&nbsp;&nbsp;".dispTextdb($Comp_Data['zip']);
		else
			$Comp_Main_Addr=dispTextdb($Comp_Data['zip']);
	}
	
	//Country
	if($Comp_Data['country']!="0")
	{
		if($Top_Row3!='')
			$Top_Row3.=",&nbsp;".dispTextdb(getCountry($Comp_Data['country']))."</font>";
		else
			$Top_Row3=dispTextdb(getCountry($Comp_Data['country']))."</font>";

		if($Comp_Main_Addr!='')
			$Comp_Main_Addr.="&nbsp;,&nbsp;".dispTextdb(getCountry($Comp_Data['country']));
		else
			$Comp_Main_Addr=dispTextdb(getCountry($Comp_Data['country']));

	}
	
	if($Comp_Data['phone_extn'] != "" && $Comp_Data['phone'] != "")
		$main_phone = dispTextdb($Comp_Data['phone'])."&nbsp;<font class='crmsummary-content-title'>ext.</font>&nbsp;".dispTextdb($Comp_Data['phone_extn']);
	else if($Comp_Data['phone_extn'] == "" && $Comp_Data['phone'] != "")
		$main_phone = dispTextdb($Comp_Data['phone']);
	else if($Comp_Data['phone_extn'] != "" && $Comp_Data['phone'] == "")
		$main_phone = "&nbsp;<font class='crmsummary-content-title'>ext.</font>&nbsp;".dispTextdb($Comp_Data['phone_extn']);				
	
	//Phone
	if($Comp_Data['phone']!="" || $Comp_Data['phone_extn'] != "")
	{
		if($Top_Row3!='')
			$Top_Row3.="</br>".$main_phone."</font>";
		else
			$Top_Row3=$main_phone."</font>";
	}
	?>
                    <tr align="center">
                      <td ><span class="crmsummary-contactinfo-title"><b><?php echo dispTextdb(stripslashes($Comp_Data['cname']));?></b></span></td>
                    </tr>
                    <tr align="center">
                      <td style='word-wrap:break-word;'><b><?php echo stripslashes($Top_Row2);?></b></td>
                    </tr>
                    <tr align="center">
                      <td style='word-wrap:break-word;'><b><?php echo stripslashes($Top_Row3);?></b></td>
                    </tr>
                  </table>
                  <table width="97%" border="0" cellpadding="0" cellspacing="0" class="crmsummary-content-table"  >
                    <tr>
                      <td colspan=4>&nbsp;</td>
                    </tr>
					
			<tr>
				<td align=center>
				<?php
				/**
				* Include the file to generate user defined fields.
				*
				*/
				$mod = 2;
				include($app_inc_path."custom/getcustomfieldvalues.php");
				?>
				</td>
					</tr>
					
                    <tr align="left" valign="top">
                      <td align=left  width="25%" class="crmsummary-content-title">Company Name</td>
                      <td align=left  colspan=3><?=dispTextdb(stripslashes($Comp_Data['cname']))?>
                        <?if($Comp_Data['curl']!=''){?>
                        &nbsp;[ <a class="crmsummary-contentlnk" href="javascript:companySite('<?=addslashes($Comp_Data['curl'])?>','url')">website</a> ]
                        <?}?>
                        &nbsp;
                        <?php
                        if($hs_search_url!="")
                        {
                            echo "<a href='".$hs_search_url."' target='_blank' class='hubspot'><div class='tooltip'><img src='../../images/hubspot_icon.png' alt='HubSpot' /><span class='tooltiptext'>Click on this icon to view ".str_replace("\\","",urldecode( dispTextdb(stripslashes($Comp_Data['cname'])) ))." HubSpot profile</span></div></a>";
                        }
                        ?>
                    </td>
                    </tr>
                    <tr align="left" valign="top" >
                      <td class="crmsummary-content-title" >Address</td>
                      <?php
	$rootNode='';

	//to get the super parent of the present company
	getSupParent($addr);
	if($addr>0)
	{
		$Chld_Sql="select sno,cname from staffoppr_cinfo where  parent='".$addr."'";
		$Child_Res=mysql_query($Chld_Sql,$db);
		$Child_Rows=mysql_num_rows($Child_Res);
	}
	?>
                      <td  <?=($Child_Rows>0 || ($rootNode!='' && $rootNode!=$addr))?'colspan=2':'colspan=3'?>
	style='word-wrap:break-word;text-align:justify;'><span class="crmsummary-contentdata1">
                        <?=stripslashes($Comp_Main_Addr);?>
                        </span> </td>
                      <?php
	if( ($Child_Rows>0) || ($rootNode!='' && $rootNode!=$addr) )
	{
	?>
                      <td valign=top align="right"> <b><a href="javascript:doOpenDivision(<?php echo $addr; ?>,'<?php echo $module; ?>')" class="crmsummary-content-link-ref">View Divisions</a></b></td>
                      <?}?>
                    </tr>
					        
   
                    <!---start of Company Brief----->
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Company Brief</font></td>
                      <td  align=left  colspan="3"><div class="combrief-paneltxt" style="word-wrap:break-word;text-align:justify;">
                          <?=nl2br(html_tls_specialchars(stripslashes($Comp_Data['compbrief']),ENT_QUOTES))?>
                          &nbsp;</div></td>
                    </tr>
                    <!---end of Company Brief----->
                    <!---start of Company Summary----->
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Company Summary</font></td>
                      <td  align=left  colspan="3"><div class="combrief-paneltxt" style="word-wrap:break-word;text-align:justify;">
                          <?php echo nl2br(html_tls_specialchars(stripslashes($Comp_Data['compsummary']),ENT_QUOTES))?>
                          &nbsp;</div></td>
                    </tr>
                    <!---end of Company Summary----->
                    <!---start of Customer Id/Company Size----->
                    <tr align="left" valign="top">
                      <td align=left  width="25%" class="crmsummary-content-title">Customer ID#</td>
                      <td align=left width="25%"><span id='CmpType'>
                        <?if($Comp_Data['acc_comp']!='0' && $Comp_Data['acc_comp']!='') echo dispTextdb($Comp_Data['acc_comp'])?>
                        </span>&nbsp;</td>
                      <td align=left width="25%" class="crmsummary-content-title-leftborder">&nbsp;Company Size</td>
                      <td width="43%" align=left><?=dispTextdb($Comp_Data['csize'])?>
                        &nbsp;</td>
                    </tr>
                    <!---end of Customer Id/Company Size----->
                    <tr align="left" valign="top">
                      <td align=left  width="25%" class="crmsummary-content-title">Company Type</td>
                      <td align=left width="25%"><span id='CmpType'>
                        <?=dispTextdb(getManage($Comp_Data['ctype']))?>
                        </span>&nbsp;</td>
                      <td align=left class="crmsummary-content-title-leftborder">&nbsp;No. Employees</td>
                      <td align=left><?=dispTextdb($Comp_Data['nemployee']);?></td>
                    </tr>
                    <tr align="left" valign="top">
                      <td align=left  width="25%" class="crmsummary-content-title">Main Phone</td>
                      <td align=left width="25%"><?=$main_phone;?>
                        &nbsp;</td>
                      <td align=left class="crmsummary-content-title-leftborder">&nbsp;No. Locations</td>
                      <td align=left><?=dispTextdb($Comp_Data['nloction'])?></td>
                    </tr>
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Fax Number</font></td>
                      <td align=left><?=dispTextdb($Comp_Data['fax'])?>
                        &nbsp;</td>
                      <td align=left class="crmsummary-content-title-leftborder">&nbsp;Company Source</td>
                      <td align=left><span id='CmpSource'>
                        <?=dispTextdb(getManage($Comp_Data['csource']))?>
                        </span>&nbsp;</td>
                    </tr>
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Industry</font></td>
                      <td align=left><?=dispTextdb($Comp_Data['industry'])?>
                        &nbsp;</td>
                      <td align=left width="25%" class="crmsummary-content-title-leftborder">&nbsp;SIC Code</td>
                      <td width="43%" align=left><?=dispTextdb($Comp_Data['siccode'])?>
                        &nbsp;</td>
                    </tr>
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Year Founded</td>
                      <td align=left><?=dispTextdb($Comp_Data['nbyears'])?>
                        &nbsp;</td>
                      <td align=left class="crmsummary-content-title-leftborder">&nbsp;Federal Id</font></td>
                      <td align=left><?=dispTextdb($Comp_Data['federalid']) ?>
                        &nbsp;</td>
                    </tr>
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Company Revenue</td>
                      <td align=left><?=dispTextdb($Comp_Data['com_revenue'])?>
                        &nbsp;</td>
                      <!-- Change the code ($Comp_Data['compowner'])  -->
                      <td align=left class="crmsummary-content-title-leftborder">&nbsp;Ticker Symbol</td>
                      <td align=left><?=dispTextdb(stripslashes($Comp_Data['ticker']))?>
                        &nbsp;</td>
                    </tr>
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Company Ownership</font></td>
                      <td align=left><?=dispTextdb(stripslashes($Comp_Data['compowner']))?>
                        &nbsp;</td>
                      <td align=left class="crmsummary-content-title-leftborder">&nbsp;Alternative ID#</td>
                      <td align=left><?=dispTextdb(stripslashes($Comp_Data['alternative_id']))?>
                        &nbsp;</td>
                    </tr>
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Search Tags</td>
                      <td align=left  colspan="3"><div class="combrief-paneltxt" style="word-wrap:break-word;text-align:justify;">
                          <?php echo nl2br(html_tls_specialchars(stripslashes($Comp_Data['keytech']),ENT_QUOTES));?>
                          &nbsp;</div></td>
                    </tr>
					<tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title">Department</font></td>
                      <td align=left ><?php echo stripslashes($Comp_Data['department'])?>&nbsp;</td>
					  <td align=left class="crmsummary-content-title-leftborder">&nbsp;HRM Department</td>
					  <td align=left><?php echo(stripslashes($Comp_Data['deptname']))?></td>
                    </tr>
                    <?php
	$All_Act_ids=$comid;
	$Comp_Cont_Sql="select IF((fname='' AND mname='' AND lname=''),nickname,concat_ws(' ',fname,mname,lname)) name ,email,ytitle,wphone,city,state,sno,maincontact,dontcall,dontemail,ctype,wphone_extn from staffoppr_contact where crmcontact='Y' AND  status='ER' AND (FIND_IN_SET('".$username."',accessto)>0 or owner='".$username."' or accessto='ALL') AND csno='".$addr."'";
	$i=0;
	$Comp_Cont_Res=mysql_query($Comp_Cont_Sql,$db);
	$Comp_Cont_rows=mysql_num_rows($Comp_Cont_Res);

	$MainContacts=array();
	$OtherContacts=array();
	$ContactSnos=array();
	$AllContactDets=array();
	$MainContName=array();
	$OtherContName=array();
	while($Comp_Cont_Data=mysql_fetch_array($Comp_Cont_Res))
	{
		if($Comp_Cont_Data['maincontact']=='Y')
		{
			$MainContacts[]=array($Comp_Cont_Data['sno'],$Comp_Cont_Data['name'],$Comp_Cont_Data['ytitle'],$Comp_Cont_Data['wphone'],$Comp_Cont_Data['city'],$Comp_Cont_Data['state'],$Comp_Cont_Data['dontcall'],$Comp_Cont_Data['dontemail'],$Comp_Cont_Data['ctype'],$Comp_Cont_Data['wphone_extn']);
   		   //this is for sorting of contacts..
		   $MainContName[]=strtolower($Comp_Cont_Data['name']);
		}
		else
		{
			$OtherContacts[]=array($Comp_Cont_Data['sno'],$Comp_Cont_Data['name'],$Comp_Cont_Data['ytitle'],$Comp_Cont_Data['wphone'],$Comp_Cont_Data['city'],$Comp_Cont_Data['state'],$Comp_Cont_Data['dontcall'],$Comp_Cont_Data['dontemail'],$Comp_Cont_Data['ctype'],$Comp_Cont_Data['wphone_extn']);

		 //this is for sorting of contacts..
		 $OtherContName[]=strtolower($Comp_Cont_Data['name']);
		}
		$ContactSnos[]=$Comp_Cont_Data['sno'];
		$All_Act_ids.="|oppr".$Comp_Cont_Data['sno']."((,)|($))";

		$AllContactDets[$Comp_Cont_Data['sno']]=$Comp_Cont_Data['name'];
	}

	//sorting the Contacts For Display Purpose...
	array_multisort($MainContName, SORT_ASC, $MainContacts);
	array_multisort($OtherContName, SORT_ASC, $OtherContacts);

	$MainCnt=count($MainContacts);
	$OtherCnt=count($OtherContacts);
	
	?>
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title"><?php if($OtherCnt>0) {?>
                        <span class="crmsummary-content-title" onClick="javascript:classToggle(expcontacts,'DisplayBlock','DisplayNone','',9,'<?=$module?>')" style="cursor:pointer;">Contacts (<a class=crmsummary-companycontactlnk href='javascript:doAddCon()'>New</a>)</span>
                        <?php } else {?>
                        <span class="crmsummary-content-title">Contacts (<a class=crmsummary-companycontactlnk href='javascript:doAddCon()'>New</a>)</span> 
						<?php } ?>
						</td>
                      <td align=left colspan=3  style='border-right:0px solid #FFFFFF' valign=top><table width=100% cellspacing=0 cellpadding=0 border=0 id='contactsTable' class="nestedtable-removelines-white">
                          <?php
	//main contcats
	if($MainCnt>0)
	{
		for(;$i <  $MainCnt ;$i++)
		{

		$Contact_Row1="";
		if($MainContacts[$i][1]=="")
		$contact_Name='N/A';
		else if($MainContacts[$i][1]!="")
		$contact_Name=$MainContacts[$i][1]; $cont_summary_url="/BSOS/Marketing/Lead_Mngmt/reviewContact.php?addr=".$MainContacts[$i][0]."&contasno=".$MainContacts[$i][0]."&sesvar=new&module=".$module;

		$Contact_Row1.="<a  class=crmsummary-companycontactlnk href=javascript:editWin('$cont_summary_url','cont_summary')>".dispTextdb($contact_Name)."</a></font>";

		if($MainContacts[$i][2]!="")
		$Contact_Row1.="&nbsp;|&nbsp;".dispTextdb($MainContacts[$i][2]);


		if($MainContacts[$i][6]=='Y')
		$Contact_Row1.="&nbsp;|&nbsp;<font class='crmsummary-contentdata-cont'>Do Not Call</font>";

		else if($MainContacts[$i][3]!="" && $MainContacts[$i][9]!="")
			$Contact_Row1.="&nbsp;|&nbsp;".dispTextdb($MainContacts[$i][3])."&nbsp;x&nbsp;".dispTextdb($MainContacts[$i][9]);
		else if($MainContacts[$i][3]!="")
			$Contact_Row1.="&nbsp;|&nbsp;".dispTextdb($MainContacts[$i][3]);


		if($MainContacts[$i][4]!="")
		$Contact_Row1.="&nbsp;|&nbsp;".dispTextdb($MainContacts[$i][4]);

		if($MainContacts[$i][5]!="")
		$Contact_Row1.=",&nbsp;".dispTextdb($MainContacts[$i][5]);


		if($MainContacts[$i][8]!='0')
		$Contact_Row1.="&nbsp;|&nbsp;".dispTextdb(getManage($MainContacts[$i][8]));

		if($i==0)
		{?>
                          <tr>
                            <td align=left  <?=($OtherCnt>=0)?"width=75%":"width=50%"?>><?=$Contact_Row1?></td>
                            <? if($OtherCnt>=0)
		{?>
                            <td align=right valign=middle><span id="helptext9" class="global-help-text"></span> <span id="rightflt" <?php echo $rightflt;?>>
                              <div class="opcl-btnleftside">
                                <div align="left"></div>
                              </div>
                              <div><a  class="cl-txtlnk" href="javascript:classToggle(expcontacts,'DisplayBlock','DisplayNone','',9,'<?=$module?>')" style='text-decoration: none;'>
                                <div id='hideExp9'>+</div>
                                </a></div>
                              <div class="opcl-btnrightside">
                                <div align="right"></div>
                              </div>
                              </span> <span id="view-all-lnk"><a style='text-decoration: none;' href="javascript:classToggle(expcontacts,'DisplayBlock','DisplayNone','',9,'<?=$module?>')">all contacts</a></span>
                              <?}?>
                            </td>
                          </tr>
                          <?}else{?>
                          <tr>
                            <td align=left colspan=2 valign=top><?=$Contact_Row1?></td>
                            <?}

		}//while for1
	} else {
	if($OtherCnt>0)
		{?>
                          <tr>
                            <td width=75% valign=top><span id="helptext9" class="global-help-text">Main Contacts Display Area - To view all click [+] button</span> </td>
                            <td align=right valign=middle><span id="rightflt" <?php echo $rightflt;?>>
                              <div class="opcl-btnleftside">
                                <div align="left"></div>
                              </div>
                              <div><a  class="cl-txtlnk" href="javascript:classToggle(expcontacts,'DisplayBlock','DisplayNone','',9,'<?=$module?>')" style='text-decoration: none;'>
                                <div id='hideExp9'>+</div>
                                </a></div>
                              <div class="opcl-btnrightside">
                                <div align="right"></div>
                              </div>
                              </span> <span id="view-all-lnk"> <a style='text-decoration: none;' href="javascript:classToggle(expcontacts,'DisplayBlock','DisplayNone','',9,'<?=$module?>')" >all contacts</a></span></td>
                          </tr>
                          <?}?>

                          <?}?>
                          <tr valign=top>
                            <td colspan=2  style='border-bottom:0px solid #FFFFFF;border-right:0px solid #FFFFFF'><div id='expcontacts' class="DisplayNone" style='height:<?=($OtherCnt>0)?'':'30'?>;overflow:auto;' >
                                <table width=100% cellspacing=0 cellpadding=0 style='border-right:0px solid #FFFFFF'>
                                  <?php
	//other contcats

	$i=0;
	for(;$i <  $OtherCnt ;$i++)
	{
		$Contact_Row2="";
		if($OtherContacts[$i][1]=="")
			$contact_Name='N/A';
		else if($OtherContacts[$i][1]!="")
			$contact_Name=$OtherContacts[$i][1]; $cont_summary_url="/BSOS/Marketing/Lead_Mngmt/reviewContact.php?addr=".$OtherContacts[$i][0]."&contasno=".$OtherContacts[$i][0]."&sesvar=new&module=".$module;

		$Contact_Row2.="<a class='crmsummary-companycontactlnk' href=javascript:editWin('$cont_summary_url','cont_summary')>".dispTextdb($contact_Name)."</a></font>";


		if($OtherContacts[$i][2]!="")
			$Contact_Row2.="&nbsp;|&nbsp;".dispTextdb($OtherContacts[$i][2]);


		if($OtherContacts[$i][6]=='Y')
			$Contact_Row2.="&nbsp;|&nbsp;<font class='crmsummary-contentdata-cont'>Do Not Call</font>";

		else if($OtherContacts[$i][3]!='' && $OtherContacts[$i][9]!='')
			$Contact_Row2.="&nbsp;|&nbsp;".dispTextdb($OtherContacts[$i][3])."<font class='crmsummary-content-title'>&nbsp;ext.&nbsp;</font>".dispTextdb($OtherContacts[$i][9]);
		else if($OtherContacts[$i][3]!='' && $OtherContacts[$i][9]=='')
			$Contact_Row2.="&nbsp;|&nbsp;".dispTextdb($OtherContacts[$i][3]);
		else if ($OtherContacts[$i][3]=='' && $OtherContacts[$i][9]!='')
			$Contact_Row2.="&nbsp;|&nbsp;<font class='crmsummary-content-title'>&nbsp;ext.&nbsp;</font>".dispTextdb($OtherContacts[$i][9]);
		
		if($OtherContacts[$i][4]!="")
			$Contact_Row2.="&nbsp;|&nbsp;".dispTextdb($OtherContacts[$i][4]);

		if($OtherContacts[$i][5]!="")
			$Contact_Row2.=",&nbsp;".dispTextdb($OtherContacts[$i][5]);

		if($OtherContacts[$i][8]!='0')
			$Contact_Row2.="&nbsp;|&nbsp;".getManage($OtherContacts[$i][8]);

		echo  "<tr><td align=left colspan=2 >".stripslashes($Contact_Row2)."</td></tr>";
	} //while for2

	?>
                                </table>
                                <!--other contcats table--->
                              </div>
                              <!----end of exp contcats div-------->
                            </td>
                          </tr>
                        </table>
                        <!--end of contacts table-->
                      </td>
                    </tr>
                    <!--Start of Billing Information-->
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title"><a style='text-decoration: none;' href="javascript:classToggle(expbilling,'DisplayBlock','DisplayNone','expbilling',10,'<?=$module?>')"> <span class="crmsummary-content-title"><nobr>Billing Information</nobr></span></a> </font></td>
                      <td align=left colspan=3  style='border-right:0px solid #FFFFFF' valign=top><table width=100% cellspacing=0 cellpadding=0 border=0 class="nestedtable-removelines-white">
                          <!--before toggle starts-->
                          <tr>
                            <td width=75% valign=top><span id="helptext10" class="global-help-text">To view Billing Information click [+] button</span></td>
                            <td align=right valign=middle><span id="rightflt" <?php echo $rightflt;?>>
                              <div class="opcl-btnleftside">
                                <div align="left"></div>
                              </div>
                              <div><a  class="cl-txtlnk" href="javascript:classToggle(expbilling,'DisplayBlock','DisplayNone','expbilling',10,'<?=$module?>')" style='text-decoration: none;'>
                                <div id='hideExp10'>+</div>
                                </a></div>
                              <div class="opcl-btnrightside">
                                <div align="right"></div>
                              </div>
                              </span> <span id="view-all-lnk10"> <a style='text-decoration: none;' href="javascript:classToggle(expbilling,'DisplayBlock','DisplayNone','expbilling',10,'<?=$module?>')" >open</a> </span></td>
                          </tr>
                        </table></td>
                    </tr>
                    <!--before toggle ends-->
                    <!--after toggle starts-->
                    <tr align="left" valign=top>
                      <td  colspan=4  style='border-bottom:0px solid #FFFFFF;border-right:0px solid #FFFFFF;padding-left:5px;'><div id='expbilling' align=left class="DisplayNone" >
						
                          <table width=100% cellspacing=0 cellpadding=0 border=0 id='BillingTable' class="nestedtable-removelines-white">
                            <tr>
                              <td height=70 valign=center align=center><?=Display_Process_Msg?> </td>
                            </tr>
                          </table>
                        </div>
						<!--div>
						<table width=100% cellspacing=0 cellpadding=0 border=0>
						  <?php// echo $ratesObj->dispCustRatesSummary($ratesObj);?>
						  </table>
						</div>
                        <!----end of expbilling div-------->
                      </td>
                    </tr>
                    <!--after toggle ends-->
                    <!--End of Billing Information-->
                    <!--Start of Companies Culture-->
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title"><a style='text-decoration: none;' href="javascript:classToggle(compcult,'DisplayBlock','DisplayNone','compcult',11,'<?=$module?>')"> <span class="crmsummary-content-title">Company Culture</span></a> </td>
                      <td align=left colspan=3  style='border-right:0px solid #FFFFFF' valign=top><table width=100% cellspacing=0 cellpadding=0 border=0 class="nestedtable-removelines-white">
                          <!--before toggle starts-->
                          <tr>
                            <td width=75% valign=top><span id="helptext11" class="global-help-text">To view Companies Culture click [+] button</span></td>
                            <td align=right valign=middle><span id="rightflt" <?php echo $rightflt;?>>
                              <div class="opcl-btnleftside">
                                <div align="left"></div>
                              </div>
                              <div><a  class="cl-txtlnk" href="javascript:classToggle(compcult,'DisplayBlock','DisplayNone','compcult',11,'<?=$module?>')" style='text-decoration: none;'>
                                <div id='hideExp11'>+</div>
                                </a></div>
                              <div class="opcl-btnrightside">
                                <div align="right"></div>
                              </div>
                              </span> <span id="view-all-lnk11"> <a style='text-decoration: none;' href="javascript:classToggle(compcult,'DisplayBlock','DisplayNone','compcult',11,'<?=$module?>')" >open</a> </span></td>
                          </tr>
                        </table></td>
                    </tr>
                    <!--before toggle ends-->
                    <!--after toggle starts-->
                    <tr align="left" valign=top>
                      <td colspan=4  style='border-bottom:0px solid #FFFFFF;border-right:0px solid #FFFFFF;padding-left:10px'><div id='compcult' class="DisplayNone" >
                          <table width=100% cellspacing=0 cellpadding=0 border=0 id='BillingTable' class="nestedtable-removelines-white">
                            <tr>
                              <td height=70 valign=center align=center><?=Display_Process_Msg?> </td>
                            </tr>
                          </table>
                        </div>
                        <!----end of compcult div-------->
                      </td>
                    </tr>
                    <!--after toggle ends-->
                    
                    <!--End of Companies Culture-->
                    <!--Start of Opportunitites-->
                    <tr align="left" valign="top">
                      <td align=left class="crmsummary-content-title"><span onClick="javascript:classToggle(expcompoppr,'DisplayBlock','DisplayNone','expcompoppr',12,'<?=$module?>')" class="crmsummary-content-title" style="cursor:pointer;"> Opportunities (<a class=crmsummary-companycontactlnk href='javascript:doAddoppr()'>New</a>)</span> </td>
                      <td align=left colspan=3  style='border-right:0px solid #FFFFFF' valign=top><table width=100% cellspacing=0 cellpadding=0 border=0 class="nestedtable-removelines-white">
                          <!--before toggle starts-->
                          <tr>
                            <td width=75% valign=top><span id="helptext12" class="global-help-text">To view Opportunities click [+] button</span></td>
                            <td align=right valign=middle><span id="rightflt" <?php echo $rightflt;?>>
                              <div class="opcl-btnleftside">
                                <div align="left"></div>
                              </div>
                              <div><a  class="cl-txtlnk" href="javascript:classToggle(expcompoppr,'DisplayBlock','DisplayNone','expcompoppr',12,'<?=$module?>')" style='text-decoration: none;'>
                                <div id='hideExp12'>+</div>
                                </a></div>
                              <div class="opcl-btnrightside">
                                <div align="right"></div>
                              </div>
                              </span> <span id="view-all-lnk12"> <a style='text-decoration: none;' href="javascript:classToggle(expcompoppr,'DisplayBlock','DisplayNone','expcompoppr',12,'<?=$module?>')" >open</a> </span></td>
                          </tr>
                        </table></td>
                    </tr>
                    <!--before toggle ends-->
                    <!--after toggle starts-->
                    <tr valign=top>
                      <td colspan=4  style='border-bottom:0px solid #FFFFFF;border-right:0px solid #FFFFFF; padding-left:10px'><div id='expcompoppr' class="DisplayNone" >
                          <table width=100% cellspacing=0 cellpadding=0 border=0 id='OpprTable' class="nestedtable-removelines-white">
                            <tr>
                              <td height=70 valign=center align=center><?=Display_Process_Msg?></td>
                            </tr>
                          </table>
                        </div>
                        <!----end of expcompoppr div-------->
                      </td>
                    </tr>
                    <!--End of Opportunitites-->
                  </table>
                  <!--compinfo table------->
                  <!---created by modified by--->
                  <div class="crmsummary-createdbytxt" align="right">Created by
                    <?=getOwnerName($Comp_Data['approveuser']);?>
                    &nbsp;(
                    <?=$Comp_Data['created_date']?>
                    )&nbsp;-&nbsp;Modified by&nbsp;<span id='footernote'>
                    <?=getOwnerName($Comp_Data['muser']);?>
                    &nbsp;(
                    <?=$Comp_Data['mod_date']?>
                    )</span></div>
                  </td>
				</div>
                  </tr>
                </table>
                <!--left table-->
				</div>
				</div>

				<div id="scrolldisplay"><div class="innerdivstyle">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" id='rightdataholder'>
                      <!----------------------------------------------TODO-------------------------------------------------------------------->
                      <tr>
                        <td width=100% class="ToDoSummaryM"><table class="right-side-table" width="100%" border="0" cellspacing="0" cellpadding="0" id='rightmaintable'>
                            <tr <?php if(OUTLOOK_PLUG_IN=="Y" && OUTLOOK_TASK_MANAGER == "Y") { ?>style="display:none"; <? } ?>>
                              <td class="remind-background"><div class="paneltitle"> <span id="rightflt" <?php echo $rightflt;?>>
                                  <div class="opcl-btnleftside">
                                    <div align="left"></div>
                                  </div>
                                  <div><a href="javascript:classToggle(mntcmnt1,'DisplayBlock','DisplayNone',mntcmnt2,1,'<?=$module?>')" class="cl-txtlnk">
                                    <div id='hideExp1'><i class="fa fa-angle-right fa-lg"></i></div>
                                    </a></div>
                                  <div class="opcl-btnrightside">
                                    <div align="left"></div>
                                  </div>
                                  </span><a href="javascript:classToggle(mntcmnt1,'DisplayBlock','DisplayNone',mntcmnt2,1,'<?=$module?>')">  <span class="paneltitle-span"><i class="fa fa-bell fa-lg"></i>&nbsp;&nbsp;To Do Reminders</span></a> </div>
                                <div class="DisplayNone" id="mntcmnt1">
                                  <input type="hidden" name="dat" value="<?=date("d");?>">
                                  <input type="hidden" name="m" value="<?=date("m");?>">
                                  <input type="hidden" name="y" value="<?=date("Y");?>">
                                  <input type="hidden" name="h" value="<?=date("H");?>">
                                  <input type="hidden" name="mins" value="<?=date("i");?>">
                                  <input type="hidden" name="s" value="<?=date("s");?>">
                                  <div id="leftflt" class="remind-inputs">
                                    <input class="textinput remind-txtinput-width" id="TODO" type="text" maxlength="255" name="TODO" value="<?=$next_mnth?>" onKeyPress='return disableEnterKey(event)'>
                                    <span id='todoCal'>
                                        <input type="text" class="textinput" id="tododate" name="tododate" value="<?php echo date('m/d/Y');?>" readonly size="12" onKeyDown="return false;"/>
                                       
                                    </span> 
                                    <script language="JavaScript"> new tcal ({'formname':'compreg','controlname':'tododate'});</script>
                                    <select class="dropdown" name="tododatedur">
                                      <option selected value=''>-select-</option>
                                      <option value="24:00:00">12:00am</option>
                                      <option value="00:30:00">12:30am</option>
                                      <option value="1:00:00">1:00am</option>
                                      <option value="1:30:00">1:30am</option>
                                      <option value="2:00:00">2:00am</option>
                                      <option value="2:30:00">2:30am</option>
                                      <option value="3:00:00">3:00am</option>
                                      <option value="3:30:00">3:30am</option>
                                      <option value="4:00:00">4:00am</option>
                                      <option value="4:30:00">4:30am</option>
                                      <option value="5:00:00">5:00am</option>
                                      <option value="5:30:00">5:30am</option>
                                      <option value="6:00:00">6:00am</option>
                                      <option value="6:30:00">6:30am</option>
                                      <option value="7:00:00">7:00am</option>
                                      <option value="7:30:00">7:30am</option>
                                      <option value="8:00:00">8:00am</option>
                                      <option value="8:30:00">8:30am</option>
                                      <option value="9:00:00">9:00am</option>
                                      <option value="9:30:00">9:30am</option>
                                      <option value="10:00:00">10:00am</option>
                                      <option value="10:30:00">10:30am</option>
                                      <option value="11:00:00">11:00am</option>
                                      <option value="11:30:00">11:30am</option>
                                      <option value="12:00:00">12:00pm</option>
                                      <option value="12:30:00">12:30pm</option>
                                      <option value="13:00:00">1:00pm</option>
                                      <option value="13:30:00">1:30pm</option>
                                      <option value="14:00:00">2:00pm</option>
                                      <option value="14:30:00">2:30pm</option>
                                      <option value="15:00:00">3:00pm</option>
                                      <option value="15:30:00">3:30pm</option>
                                      <option value="16:00:00">4:00pm</option>
                                      <option value="16:30:00">4:30pm</option>
                                      <option value="17:00:00">5:00pm</option>
                                      <option value="17:30:00">5:30pm</option>
                                      <option value="18:00:00">6:00pm</option>
                                      <option value="18:30:00">6:30pm</option>
                                      <option value="19:00:00">7:00pm</option>
                                      <option value="19:30:00">7:30pm</option>
                                      <option value="20:00:00">8:00pm</option>
                                      <option value="20:30:00">8:30pm</option>
                                      <option value="21:00:00">9:00pm</option>
                                      <option value="21:30:00">9:30pm</option>
                                      <option value="22:00:00">10:00pm</option>
                                      <option value="22:30:00">10:30pm</option>
                                      <option value="23:00:00">11:00pm</option>
                                      <option value="23:30:00">11:30pm</option>
                                    </select>
                                  </div>
                                  <!--end of remind-background-->
                                  <span id="rightflt3" <?php echo $rightflt3;?> class="remind-background">
                                  <div><a  id='todoAdd' class="smform-txtlnk" href="javascript:UpdateToDo('0','todoAdd','<?=$module?>')">Add</a></div>
                                  </span>
                                  <!--for line--->
                                  <div class="space_5px">&nbsp;</div>
                                  <div class="space_5px">&nbsp;</div>
                                  <div class="line4">&nbsp;</div>
                                  <div class="space_5px">&nbsp;</div>
                                  <div id="sort" class="remind-sort-input"> <font class="remind-sortby">Sort By:&nbsp;</font>
                                    <select name='todos' class="dropdown-todo" onChange="othersToDo(this.value)">
									 <option value='All'>All To Dos</option>
                                     <option value='Mytodo'>My To Dos</option>
                                     
                                      <?php
	foreach($Users_Array as $UserNo=>$UName)
	{
		if($UserNo!=$username){?>
                                      <option value="<?=$UserNo?>" <?=sele($username,$UName)?>>
                                      <?=$UName;?>
                                      </option>
                                      <?}?>
                                      <?}?>
                                    </select>
                                  </div>
                                  <div class='space_5px'>&nbsp;</div>
									  <div id='ToDoRow'> <nobr>
                                    <?php
	//getting all the todos				
	$Rem_Sql="SELECT IF(ctime='00:00:00',DATE_FORMAT(tasklist.startdate,'%c/%e/%Y'),DATE_FORMAT(concat_ws(' ', tasklist.startdate,IF (ctime = '24:00:00','00:00:00',ctime)),'%c/%e/%Y %l:%i%p')),
				   tasklist.title,
				   tasklist.cuser,
				   datediff(tasklist.startdate,NOW()),
				   tasklist.sno,
				   tasklist.cuser,
				   tasklist.ctime,
				   DATE_FORMAT(tasklist.startdate,'%c/%e/%Y'),
				   tasklist.modulename,
				   tasklist.contactsno
			FROM tasklist
			WHERE tasklist.type=1
			  AND tasklist.tasktype='todo'
			  AND (tasklist.contactsno='".$comid."'
				   OR tasklist.contactsno IN
					 (SELECT concat('oppr',sno)
					  FROM staffoppr_contact
					  WHERE csno='$addr'
						AND crmcontact='Y'
						AND status='ER'))
			  AND tasklist.taskstatus!='Completed'
			  AND tasklist.status NOT IN ('remove',
										  'backup',
										  'ARCHIVE')
			ORDER BY tasklist.sno DESC";			
				

	$Rem_Res=mysql_query($Rem_Sql,$db);
	$Rem_Rows=mysql_num_rows($Rem_Res);
	while($Rem_Data=mysql_fetch_row($Rem_Res))
	{
		$cr_Date=explode(" ",$Rem_Data[0]);
		$cr_Date=$cr_Date[0];
		$cont_name=$AllContactDets[substr($Rem_Data[9],4)];
		if($cont_name=='')
			$contact_name='N/A';
		else
			$contact_name=$cont_name;

		$Comp_Data=strtolower($Rem_Data[7])."&nbsp;-&nbsp;".html_tls_specialchars($Array_Users[$Rem_Data[2]],ENT_QUOTES)."&nbsp;/&nbsp;".html_tls_specialchars($contact_name,ENT_QUOTES);
		$Comp_Data1=strtolower($Rem_Data[7])."&nbsp;-&nbsp;".html_tls_specialchars($Array_Users[$Rem_Data[2]],ENT_QUOTES);
		$Comp_Data2=strtolower($Rem_Data[0])."&nbsp;-&nbsp;".html_tls_specialchars($Array_Users[$Rem_Data[2]],ENT_QUOTES)."&nbsp;/&nbsp;".html_tls_specialchars($contact_name,ENT_QUOTES);
		$Comp_Data3=strtolower($Rem_Data[0])."&nbsp;-&nbsp;".html_tls_specialchars($Array_Users[$Rem_Data[2]],ENT_QUOTES);
		if(trim($Rem_Data[1]) !='')
		{
			$Comp_Data.="&nbsp;-&nbsp;".html_tls_specialchars($Rem_Data[1],ENT_QUOTES);
			$Comp_Data1.="&nbsp;-&nbsp;".html_tls_specialchars($Rem_Data[1],ENT_QUOTES);
			$Comp_Data2.="&nbsp;-&nbsp;".html_tls_specialchars($Rem_Data[1],ENT_QUOTES);
			$Comp_Data3.="&nbsp;-&nbsp;".html_tls_specialchars($Rem_Data[1],ENT_QUOTES);
		}

		if($Rem_Data[3]<0)
		{ //those which r expired
			?>
                                    <div class="remindtext-alert"> <nobr>
                                      <!--display the del link ,edit link for owners only-->
                                      <?php
			if($Rem_Data[5]==$username){?>
                                      <a class="remind-delete-align" href="javascript:delToDo('<?=$Rem_Data[4]?>')"> <img src="/BSOS/images/crm/icon-delete.gif" width="10" height="9" alt="" border="0"></a> (!)
                                      <? if($Rem_Data[8]=="Marketing->Prospects") { ?>
                                      <a href="javascript:editWin('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&module=<?=$module?>')" ><?php echo $Comp_Data."<br/>";?></a>
                                      <? } else {?>
                                      <a href="javascript:editWin('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&module=<?=$module?>')" ><?php echo $Comp_Data1."<br/>";?></a>
                                      <? } }else{
			//display only the content
			if($Rem_Data[8]=="Marketing->Prospects")
			{
			echo "&nbsp;&nbsp;&nbsp;".$Comp_Data."<br/>";
			}
			else
			{
			echo "&nbsp;&nbsp;&nbsp;".$Comp_Data1."<br/>";
			}
			}?>
                                      </nobr> </div>
                                    <!--end of remind textalert-->
                                    <?}else {	//normal todos
			?>
                                    <div class="remindtext"> <nobr>
                                      <!--display the del link for owners only-->
                                      <?php if($Rem_Data[5]==$username){?>
                                      <a class="remind-delete-align" href="javascript:delToDo('<?=$Rem_Data[4]?>')" ><img src="/BSOS/images/crm/icon-delete.gif" width="10" height="9" alt="" border="0"></a>
                                      <? if($Rem_Data[8]=="Marketing->Prospects"){ ?>
                                      <a href="javascript:editWin('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&module=<?=$module?>')"><?php echo $Comp_Data2."<br/>";?></a>
                                      <? } else { ?>
                                      <a href="javascript:editWin('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&module=<?=$module?>')"><?php echo $Comp_Data3."<br/>";?></a>
                                      <?} }else{
			//display only the content
			if($Rem_Data[8]=="Marketing->Prospects")
			{
			echo "&nbsp;&nbsp;&nbsp;".$Comp_Data2."<br/>";
			}
			else
			{
			echo "&nbsp;&nbsp;&nbsp;".$Comp_Data3."<br/>";
			}
			}?>
                                      </nobr> </div>
                                    <!--end of crmsummary-contentdata-todo-->
                                    <?}
	}//while
	?>
                                  </div>
                                  <!--end of ToDoRow-->
                                  <div class="space_5px">&nbsp;</div>
                                </div>
                                <!--end of mntcmnt1-->
                                <div class="line3">&nbsp;</div></td>
                            </tr>
                            <!----------------------------------------end of TODO------------------------------------------------------------------>
                            <tr>
                              <td width="100%" class="notes-cell"><div class="line2">&nbsp;</div>
                                <div class="paneltitle" > 
                                	 <span id="rightflt" <?php echo $rightflt;?>>
                                  <div class="opcl-btnleftside">
                                    <div align="left"></div>
                                  </div>
                                  <div><a href="javascript:classToggle(mntcmnt2,'DisplayBlock','DisplayNone',mntcmnt3,2,'<?=$module?>')" class="cl-txtlnk">
                                    <div id='hideExp2'><i class="fa fa-angle-right fa-lg"></i></div>
                                    </a></div>
                                  <div class="opcl-btnrightside">
                                    <div align="left"></div>
                                  </div>
                                  </span>
                                	<a href="javascript:classToggle(mntcmnt2,'DisplayBlock','DisplayNone',mntcmnt3,2,'<?=$module?>')"><span class="paneltitle-span"><i class="fa fa-sticky-note fa-lg"></i>&nbsp;&nbsp;Notes</span></a></div>
                                <!--end of panel title-->
                                <div class="DisplayNone" id="mntcmnt2">
								<!-- Aswini: added the processing message-->
								<span id="SPPW" ><?=Display_Process_Msg;?></span>
								<?php 
									// This function display Add Notes panel.
									dispSummaryNotesPanel($addr, 'company', 'allNotesNew', 'allNotes', $notes_edit_list,$module);
								?>
								    <div id="allNotesTotal" class="notes-paneltxt-summary" style="word-wrap:break-word;word-break:break-all;text-align:justify;scroll:auto" scroll="auto">
								    <div id="pmsg" style="text-align:justify;"></div> <!--added by swapna for procesing msg-->
									<div id="allNotesNew"  style="word-wrap:break-word;word-break:break-all;text-align:justify;" scroll="off"> </div>
                                    <div id="allNotes"  style="word-wrap:break-word; word-break:break-all;text-align:justify;" scroll="off">
                                      <?php
										getAllSummayNotes('company', $addr, 'false', 'summary','','',$module);
										?>
                                    </div>
                                    <!--allNotes-->
                                  </div>
                                  <!--allNotesTotal-->
                                </div>
                                <!--mntcmnt2-->
                              </td>
                            </tr>
                            <!---------------------------------------------------------End of Notes----------------------------->
                            <!--------------------------------------------------------------------Activities--------------------------------------->
                            <tr>
                              <td><div class="line2">&nbsp;</div>
                                <div id="ActivitiesRow">
                                  <div class="paneltitle"> 
									<span id="rightflt" <?php echo $rightflt;?>>
                                    <div class="opcl-btnleftside">
                                      <div align="left"></div>
                                    </div>
                                    <div><a href="javascript:classToggle(mntcmnt3,'DisplayBlock','DisplayNone',mntcmnt4,3,'<?=$module?>')" class="cl-txtlnk">
                                      <div id='hideExp3'><i class="fa fa-angle-right fa-lg"></i></div>
                                      </a></div>
                                    <div class="opcl-btnrightside">
                                      <div align="left"></div>
                                    </div>
                                    </span>
                                  <a href="javascript:classToggle(mntcmnt3,'DisplayBlock','DisplayNone',mntcmnt4,3,'<?=$module?>')">  <span class="paneltitle-span"><i class="fa fa-comments fa-lg"></i>&nbsp;&nbsp;Activities</span> </a> <span class="paneltitle-suptext">(Last 20)</span><span id="view-all-lnk"><a href="javascript:newWin('viewact.php?cmpSumsrc=Yes&addr=<?=$addr?>&module=<?php echo $_GET["module"];?>','allactivities','1260','600')">all activities</a></span> </div>
                                  <!--end of paneltitle-->
                                  <div class="DisplayNone" id="mntcmnt3"  >
                                    <table width=100%>
                                      <tr>
                                        <td height=70 valign=center align=center><?=Display_Process_Msg?></td>
                                      </tr>
                                    </table>
                                  </div>
                                </div>
                                <!--end of activities row-->
                                <div class="line2">&nbsp;</div>
                                <div class="line3">&nbsp;</div></td>
                            </tr>
                            <!--------------------------------------------------------------------Documents--------------------------------------->
                            <tr>
                              <td><div id="DocumentsRow">
                                  <div class="paneltitle"> 
									<span id="rightflt" <?php echo $rightflt;?>>
                                    <div class="opcl-btnleftside">
                                      <div align="left"></div>
                                    </div>
                                    <div><a href="javascript:classToggle(mntcmnt4,'DisplayBlock','DisplayNone',mntcmnt5,4,'<?=$module?>')" class="cl-txtlnk">
                                      <div id='hideExp4'><i class="fa fa-angle-right fa-lg"></i></div>
                                      </a></div>
                                    <div class="opcl-btnrightside">
                                      <div align="left"></div>
                                    </div>
                                    </span>
                                  <a href="javascript:classToggle(mntcmnt4,'DisplayBlock','DisplayNone',mntcmnt5,4,'<?=$module?>')"> <span class="paneltitle-span"><i class="fa fa-file fa-lg"></i>&nbsp;&nbsp;Documents</span> </a> <span class="paneltitle-suptext">[ <a href="javascript:newDoc('adddocument','Companies','<?=$module?>')">add document</a> ]</span></div>
                                  <!--end of paneltitle-->
                                  <div class="DisplayNone" id="mntcmnt4" >
                                    <table width=100%>
                                      <tr>
                                        <td height=70 valign=center align=center><?=Display_Process_Msg?></td>
                                      </tr>
                                    </table>
                                  </div>
                                </div>
                                <!--end of document Row-->
                                <div class="line3">&nbsp;</div>
                                <div class="line2">&nbsp;</div></td>
                            </tr>
                            <!--------------------------------------------------------------------JOb Orders--------------------------------------->
                            <tr>
                              <td><div id="JobOrdersRow">
                                  <div class="paneltitle"> 
									<span id="rightflt" <?php echo $rightflt;?>>
                                    <div class="opcl-btnleftside">
                                      <div align="left"></div>
                                    </div>
                                    <div><a href="javascript:classToggle(mntcmnt5,'DisplayBlock','DisplayNone',mntcmnt6,5,'<?=$module?>')" class="cl-txtlnk">
                                      <div id='hideExp5'><i class="fa fa-angle-right fa-lg"></i></div>
                                      </a></div>
                                    <div class="opcl-btnrightside">
                                      <div align="left"></div>
                                    </div>
                                    </span>
                                    <?php
                                    if($module == 'Admin_Companies')
                                    {
                                        $modulejob = 'Admin_JobOrders';
                                    }else{
                                        $modulejob = $module;
                                    }
                                    ?>
                                  <a href="javascript:classToggle(mntcmnt5,'DisplayBlock','DisplayNone',mntcmnt6,5,'<?=$module?>')"><span class="paneltitle-span"><i class="fa fa-list-alt fa-lg"></i>&nbsp;&nbsp;Job Orders</span> </a> <span class="paneltitle-suptext">(20 most current) [ <a href="javascript:newWin('/BSOS/Sales/Req_Mngmt/createjoborder.php?neworder=yes&frompage=company&compid=<?=$addr?>&module=<?=$modulejob?>','newjoborder','1200','700')">new</a> ] </span><span id="view-all-lnk"> <a href="javascript:newWin('allJobOrders.php?compid=<?=$addr?>&frompage=company&module=<?=$modulejob?>&cname=<?=urlencode(addslashes($Comp_Data['cname']))?>','allJobs','850','600')">all job orders</a> </span> </div>
                                  <!-- end of paneltitle-->
                                  <div class="DisplayNone" id="mntcmnt5" >
                                    <table width=100%>
                                      <tr>
                                        <td height=70 valign=center align=center><?=Display_Process_Msg?></td>
                                      </tr>
                                    </table>
                                  </div>
                                </div>
                                <!--end of JobOrders Row-->
                                <div class="line3">&nbsp;</div>
                                <div class="line2">&nbsp;</div></td>
                            </tr>
                            <!--------------------------------------------------------------------Candidates--------------------------------------->
                            <tr>
                              <td><div  id="CandidatesRow">
                                  <div class="paneltitle" > 
									<span id="rightflt" <?php echo $rightflt;?>>
                                    <div class="opcl-btnleftside">
                                      <div align="left"></div>
                                    </div>
                                    <div><a href="javascript:classToggle(mntcmnt6,'DisplayBlock','DisplayNone',mntcmnt1,6,'<?=$module?>')" class="cl-txtlnk">
                                      <div id='hideExp6'><i class="fa fa-angle-right fa-lg"></i></div>
                                      </a></div>
                                    <div class="opcl-btnrightside">
                                      <div align="left"></div>
                                    </div>
                                    </span>
                                    <?php
                                    if($module == 'Admin_Companies')
                                    {
                                        $modulecand = 'Admin_Candidates';
                                    }else{
                                        $modulecand = $module;
                                    }
                                    ?>
                                  <a href="javascript:classToggle(mntcmnt6,'DisplayBlock','DisplayNone',mntcmnt1,6,'<?=$module?>')"> <span class="paneltitle-span"><i class="fa fa-user fa-lg"></i>&nbsp;&nbsp;Candidates</span> </a> <span class="paneltitle-suptext">(20 most recent  placements) [ <a href="javascript:newWin('/BSOS/Marketing/Candidates/conreg1.php?resstat=new&candrn=<?=$candrn?>&module=<?=$modulecand?>&proid=','','890','470')">new</a> ] </span><span id="view-all-lnk"> <a href="javascript:newWin('allCandidates.php?compid=<?=$addr?>&cname=<?=urlencode(addslashes($Comp_Data['cname']))?>&candrn=<?=$candrn?>&module=<?=$modulecand?>','candidateopen','850','600')">all candidates</a> </span> </div>
                                  <!--end of paneltitle-->
                                  <div class="DisplayNone" id="mntcmnt6" >
                                    <table width=100%>
                                      <tr>
                                        <td height=70 valign=center align=center><?=Display_Process_Msg?></td>
                                      </tr>
                                    </table>
                                  </div>
                                </div>
                                <!--end of Candidates Row-->
                                <div class="line2">&nbsp;</div></td>
                            </tr>
                            <!--------------------------------------------End of candidates--------------------------->
                          </table>
                          <!--right main table-->
                        </td>
                      </tr>
                    </table>
                    <!--end of right data holder--->
                  </div></div>
                  <!--right pane div---->
                </td>
                </tr>
                
              </table>
              <!--end of summaryContentHolder table-->
			</div>
          
		  <div class="tab-page" id="tabPage11">
            <h2 class="tab">Edit</h2>
            <script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) , "editmanage.php?addr=<?php echo $addr;?>&Row=<?php echo $Row?>&Rnd=<?=$Rnd?>&candrn=<?php echo $candrn; ?>&newcust=<?=$newcust?>&comp_stat=<?=$comp_stat?>&par_stat=<?=$par_stat?>&module=<?=$module?>");
	</script>
          </div>
</td>
</tr>
</table>
</div>
</div>
      <!--end of grid-form-->
</form>
<!--this form is for checklist---->
<form name='conreg' id='conreg' method='post'>
  <input type="hidden" name="sno_staff" id="sno_staff" value="<?php echo $addr;?>" />
  <input type="hidden" name="posid" id="posid" value='' />
</form>

<script language="javascript">
bodyOnFocus();
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
				profilecount = document.compreg.profilecount;
				notescount = document.compreg.notescount;

				//keywords = keyword.match(/(\w|\s)*\w(?=")|\w+/g);
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

	function doNew()
	{
	var v_heigth = 470;
	var v_width  = 890;
	var top=(window.screen.availHeight-v_heigth)/2;
	var left=(window.screen.availWidth-v_width)/2;
	remote=window.open("/BSOS/Marketing/Candidates/conreg1.php?resstat=new&proid=<?php echo $contasno;?>",'contractor',"width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left=0,top=0,dependent=no,resizable=yes,left="+left+",top="+top);
	remote.focus();
	}

	//for opening a task from Edesk
	<?php
	if($ptype == "Appointment")
	{
		?>
		result = "editappoint.php?addr=<?php echo $conid . '&line=' . $line . '&con_id=' . $con_id."&module_type_appoint=".$_GET['module_type_appoint'] . "&module=".$_GET['module']; ?>";
		editWin(result);
		<?php
	} 
	else if($ptype == "Task")
	{
		?>
		result = "edittask.php?addr=<?php echo $conid . '&sno=' . $line . '&con_id=' . $con_id."&module_type_appoint=".$_GET['module_type_appoint'] . "&module=".$_GET['module']. "&pageName=".$pageName; ?>";
		editWin(result);
		<?php
	}
	?>
	
	/*window.onload = setHeight;
	window.onresize = setHeight;
	
	function setHeight(e)
	{
		try {
			document.getElementById("leftpanetd").height = (parseInt(document.body.clientHeight) - 100);
			$("#leftpane").css({height:"100%"});
			$("#scrolldisplay").css({height:"100%"});
		}
		catch(e){}
	}*/
	document.getElementById("SPPW").style.display = "none";
	function bodyOnFocus() {
		try {
		displayPrevNext('Companies.php');
		} catch(e) {}
	}

			//------------ This function will prevent user to close browser if the notes field has any values to save
	window.onbeforeunload = function(e) {
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
	function windclose(){
		var mod_const	= "<?=$candrn?>";
		if (document.getElementById("notes").value != "")
			{
				document.getElementById('notes').style.backgroundColor = "#ffff33";
				if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))  {
						var conf = confirm('You have notes that is not saved. \nClick on "OK " to close the window without saving notes. \nCick on "Cancel" to go back and save the notes.');

						if (conf == true){

							eraseSessionVars("companies", mod_const);
							window.close();
						}

				} else {

					eraseSessionVars("companies", mod_const);
					window.close();
				}

			} else {

				eraseSessionVars("companies", mod_const);
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
	$module = isset($_GET['module'] )? $_GET['module']: '';
	if($module == 'CRM' || $module == '' || $module == undefined){
		$module = 'CRM';

	}else{
		$module = 'Admin_Companies';
	}
	if(isset($opendivs) && !empty($opendivs))
	{
		$getOpendivs = explode(",",$opendivs);
		if(count($getOpendivs)>0)
		{
			foreach($getOpendivs as $odid)
			{
				if($odid==7)
				{
					?>
                                classToggle(mntcmnt<?=$odid?>,'DisplayBlock','DisplayNone',mntcmnt<?=$odid?>,<?=$odid?>,'<?=$module?>');
				try {
                                   classToggle(joset,'DisplayBlock','DisplayNone','',7,'<?=$module?>');
                                } catch (e) {
                                     console.log('An error has occurred: '+e.message);
                                }
                                <?php
				}else if($odid=='expcompoppr'){ ?>
                                    classToggle(expcompoppr,'DisplayBlock','DisplayNone','expcompoppr',12,'<?=$module?>');
                                <?php }else if($odid=='expbilling'){ ?>
                                    classToggle(expbilling,'DisplayBlock','DisplayNone','expbilling',10,'<?=$module?>');
                                <?php  }else if($odid=='compcult'){ ?>
                                    classToggle(compcult,'DisplayBlock','DisplayNone','compcult',11,'<?=$module?>');
                                <?php }else if($odid=='expcontacts'){ ?>
                                    classToggle(expcontacts,'DisplayBlock','DisplayNone','',9,'<?=$module?>');
                                <?php }else {?>	
				classToggle(mntcmnt<?=$odid?>,'DisplayBlock','DisplayNone',mntcmnt<?=$odid?>,<?=$odid?>,'<?=$module?>');
<?php
				}
			}
		}
	}
?>
window.resizeTo(1127,725);
</script>
<style type="text/css">
.ToDoPreference .cl-txtlnk {
    border: 2px solid #3eb8f0 !important;
    border-radius: 100% !important;
    box-sizing: border-box !important;
    float: inherit  !important;
    height: 22px !important;
    padding: 1px 0 0 1px !important;
    text-align: center !important;
    width: 22px !important;
}
.ToDoPreference .cl-txtlnk i.fa-angle-down {
    padding: 1px 0px 0 1px !important;
	margin-left:-5px !important;
}
.prefBorderCls{
	background: #ffffff none repeat scroll 0 0;
    border: 1px solid #cccccc;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    margin-left: 4px;
    margin-right: 4px;
    margin-top: -3px;
    padding: 5px;
}
</style>
	
</body>
</html>