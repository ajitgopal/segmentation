<?php 
	
	$temp_addr=$addr;
	$temp_subno=$subno;

	require("global.inc");
	require("dispfunc.php");

	$xajax = new xajax();
	$xajax->registerExternalFunction(array("gridData","gridData","getJoborderMangSub"),"gridData.inc");
	$xajax->processRequests();

	require("Menu.inc");
	$menu=new EmpMenu();
	require("activewidgets.php");

	$b="";//$b is contains element of column4 list box;
	$addr=$temp_addr;
	$subno=$temp_subno;

	$posno=$addr;
	$posdes_que="select username,postitle,contact,shift_type from posdesc where posid=".$posno;

	$posdes_res=mysql_query($posdes_que,$db);
	$pos_row=mysql_fetch_array($posdes_res);
	$sel_contact="SELECT CONCAT_WS(' ',staffoppr_contact.fname,if(staffoppr_contact.mname='',' ',staffoppr_contact.mname),staffoppr_contact.lname),staffoppr_contact.ytitle,staffoppr_cinfo.cname,staffoppr_contact.wphone FROM staffoppr_contact LEFT JOIN staffoppr_cinfo ON staffoppr_contact.csno = staffoppr_cinfo.sno WHERE staffoppr_contact.sno='".$pos_row[2]."' and staffoppr_contact.status='ER' and (FIND_IN_SET('$username',staffoppr_contact.accessto)>0 OR staffoppr_contact.owner='$username' OR staffoppr_contact.accessto='ALL')";
	$res_contact=mysql_query($sel_contact,$db);
	$row_contact=mysql_fetch_row($res_contact);
	$comp_contact=$row_contact[0];
	$title_contact=$row_contact[1];
	$comp_name= stripslashes($row_contact[2]);
	$phone_contact=$row_contact[3];
	$posid=$posno;

	if($pos_row[1]!="")	
		$win_title="Submissions - ".html_tls_entities($pos_row[1]);
	else
		$win_title="Submissions";

	$shiftTypeName = "";
	$shiftType = "regular";
	if ($pos_row['shift_type'] !="" && $pos_row['shift_type'] =="perdiem") {
		$shiftTypeName = " (Shift Type : Perdiem)";
		$shiftType = "perdiem";
		
	}else if ($pos_row['shift_type'] !="" && $pos_row['shift_type'] =="regular") {
		$shiftTypeName = " (Shift Type : Regular)";
		$shiftType = "regular";
	}

	if ($hidemodule == "" && $module !="") {
		$hidemodule = $module;
	}
	//fetching the job order type
	$jo_type = getJoborderType($posno);

	$display_sub_rates = "NO";
	if($jo_type != "Direct" && $jo_type!= "Internal Direct")
	{
		$display_sub_rates = "YES";

		if(!isset($pay_rate_type))
		{
			$pay_rate_type = "";
		}	
		if(!isset($pay_rate_txt))
		{
			$pay_rate_txt = "";
		}
	}

	

	/*if($place_link=="place_cand")
		echo "<script>window.resizeTo(1120,850);</script>";*/
	
	// bulk resubmit session information unset
	unset($_SESSION['cand_resubmit_details']);	

	function getJoborderType($pos_id)
	{
		global $db;

		$sel_que = "SELECT postype FROM posdesc WHERE posid='".$pos_id."'";
		$sel_res = mysql_query($sel_que,$db);
		$sel_row = mysql_fetch_array($sel_res);
		$job_type_sno = $sel_row[0];

		$job_type = getManage($job_type_sno);

		return $job_type;
	}	
	
?>
<html>
<head>
<title><?php echo $win_title;?></title>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/ajax-tooltip.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/tooltip.css">
<link href="/BSOS/css/gridhs.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/akken_gridhs.css" type=text/css rel=stylesheet>
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link href="/BSOS/css/filter.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/site.css" type=text/css rel=stylesheet>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery-1.11.1.js"></script>
<script src="/BSOS/scripts/cookies.js"></script>
<script src="/BSOS/scripts/gridhs.js"></script>
<script src="/BSOS/scripts/menu.js"></script>
<script src="/BSOS/scripts/paging.js"></script>
<script src="/BSOS/scripts/akken_gridhs.js"></script>
<script src="/BSOS/scripts/json.js"></script>
<script src="/BSOS/scripts/conMenu.js"></script>
<script src="scripts/validateact.js" language=javascript></script>
<script language=javascript src=/BSOS/scripts/validaterresume.js></script>
<script language="JavaScript" src="scripts/joborderinfo.js"></script>
<script language=javascript src="scripts/validatemarkreqman.js"></script>
<script language=javascript src="scripts/ajax12.js"></script>
<script language=javascript src="scripts/validateorder.js"></script>
<script language=javascript src="scripts/validatesup.js"></script>
<script language=javascript src="/BSOS/scripts/dynamicElementCreatefun.js"></script>
<script language=javascript src="/BSOS/scripts/common.js"></script>
<script language=javascript src="/BSOS/scripts/validatecheck.js"></script>
<script language=javascript src=scripts/validatenewsubmanage.js></script>
<script language=javascript src=/BSOS/scripts/commonact.js></script>
<script language="JavaScript" src="scripts/skillsmenu.js"></script>
<script language="JavaScript" src="scripts/validateskill.js"></script>
<script src="/BSOS/scripts/moveto.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script language="JavaScript" src="/BSOS/scripts/common_ajax.js"></script>
<script type="text/javascript" src="/BSOS/scripts/OutLookPlugInDom.js"></script>
<script src="/BSOS/scripts/ajax.js"></script>
<script src="/BSOS/scripts/shortlists.js"></script>
<script type="text/javascript" src="/BSOS/scripts/cea.js"></script>
<?php require_once("TextUs.php");?>
<script type="text/javascript" src="/BSOS/TextUs/scripts/textus.js"></script>
<script src="/BSOS/scripts/vinterviews.js"></script>
<script language=javascript src=/BSOS/scripts/ajax-tooltip.js></script>
<script language=javascript src=/BSOS/scripts/groupMail.js></script>
    
<script>OutLookPlugInDom['Enable']="<?php echo OUTLOOK_PLUG_IN;?>";</script>
<script language=javascript>
function openNewWindow(val) 
{
	var req_id = document.markreqman.posid.value
	var res3 = val.split("|");
	var url = "subinfo.php?addr="+res3[0]+"&sno="+req_id+"&con_id=cand"+res3[0]+"&coming="+res3[1]+"&seqnumber="+res3[2]+"&status="+res3[3]+"&shiftsnos="+res3[4]+"&res_sno="+res3[5]+"&module=<?=$_GET['hidemodule'];?>";
	var v_heigth = 600;
	var v_width  = 800; 
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	
	$().modalBox({'html':'<div id="attribute-selector" style="position:fixed; display:block; top:21px;"><img id="preloaderW" src="/BSOS/images/preloader.gif"><div class="scroll-area"><div class="scroll-pane"><iframe id="subinfo_popup" src="'+url+'" border="0" width="100%" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%; overflow:hidden; height:100%; position:relative; top:0px; left:0px;"></iframe></div></div></div>'});
}

function viewhistorypopup(req_id,res_id,shift_id,seqnumber) 
{
	var url ="win_notes_history.php?req_id="+req_id+"&res_id="+res_id+"&shift_id="+shift_id+"&seqnumber="+seqnumber;
		
	$().modalBox({'html':'<div id="attribute-selector" style="position:fixed;display:block;top:18%;left:14%;width:70%;"><img id="preloaderW" src="/BSOS/images/preloader.gif"><div class="scroll-area" style="height:320px; width:100%;"><div class="scroll-pane"><iframe id="subinfo_popup" src="'+url+'" border="0" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%; overflow:hidden; height:320px; position:relative; top:0px; left:0px;"></iframe></div></div></div>'});
}
</script>
<script>
	$(document).ready(function(){
		$(window).resize(function(){
			var getwindowwidth = $(window).width();
			var setcontentwidth = (getwindowwidth - 10);
			$(".active-scroll-search, .active-scroll-data, .active-scroll-top").width(setcontentwidth);
		}).resize();
		$(window).load(function() {
			$(".subInfoCont").html($("#submissionContent").html());
		});
	});
</script>

<!-- New Akken UI changes -->
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<script type="text/javascript">
	function modalBoxCloseandCancel()
	{
		modalBoxClose();
	}
	function modalBoxClose() 
	{
		$().modalBox('close');
	}
	// This function is used for reload the Submission Information iframe 
	function reload_subinfo() 
	{
		var f = document.getElementById('subinfo_popup');
		f.src = f.src;
	}
	// Reload update status popup for IE
	function reload_frm_updatestatus(url,interview_popup) 
	{
		$().modalBox('close');
		if(interview_popup != 1)
		{
			$().modalBox({'html':'<div id="attribute-selector" style="position: fixed; display: block; top: 3%;"><img id="preloaderW" src="/BSOS/images/preloader.gif"><div class="scroll-area"><div class="scroll-pane"><iframe id="popup" src="'+url+'" border="0" width="100%" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%; overflow:hidden; height:100%; position:relative; top:0px; left:0px;"></iframe></div></div></div>'});
		}
	}
	// Open submission popup from update status popup for IE
	function open_submissions_popup(url) 
	{
		$().modalBox('close');
		$().modalBox({'html':'<div id="attribute-selector" style="margin-left:-300px !important; margin-top:-150px !important; left:40% !important; top:30% !important; position: fixed;  "><img id="preloaderW" src="/BSOS/images/preloader.gif" ><div class="scroll-area"><div class="scroll-pane"><iframe id="trisubmissions" src="'+url+'" border="0" width="100%" height="300" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%;overflow:hidden;height:550px;position:relative;top:0px;left:0px;"></iframe></div></div></div>'});
		$("#modal-wrapper").addClass("JoAssignmodal-wrapper");
		$("#attribute-selector .scroll-area").css({height: 550,width: 780});
	}
	// 
	function reload_multicand_update_popup() 
	{
		$().modalBox('close');
		candUpdateStatus();
	}

function removeTaggedMngeSub() {

	numAddrs = numSelected();
	
	if(numAddrs > 0) 
	{
		var chkTgPro = confirm('Tagged candidate profiles will be lost. \nClick on "Ok" to  continue. \nClick on "Cancel" to go back to the tagged candidates.');
		if(chkTgPro == true) 
		{
			window.close();
		}
		else 
		{
			return;
		}	
	}
	else
	{
		window.close();
	}
}
</script>
<!-- New Akken UI changes -->

<style>
.active-column-0 { width: 34px !important;}
.active-column-1 { width: 34px !important; }
.active-column-2 {width: 150px;}
.active-column-3 {width: 130px;}
.active-column-4 {width: 105px;}
.active-column-5 {width: 140px;}
.active-column-6 {width: 155px;}
.active-column-7 {width: 100px;}
.active-column-11 { width: 34px !important; }
.active-column-12 { width: 34px !important; }
.active-column-13 { width: 34px !important; }
.active-column-14 { width: 34px !important; }
.active-column-15 { width: 34px !important; }
.active-column-16 { width: 34px !important; }
.active-column-17 { width: 34px !important; }
.active-column-18 { width: 34px !important; }

.active-column-10 .active-box-resize {display: none;}
.active-column-11 .active-box-resize {display: none;}
.active-column-12 .active-box-resize {display: none;}
.active-column-13 .active-box-resize {display: none;}
.active-column-14 .active-box-resize {display: none;}
.active-column-15 .active-box-resize {display: none;}
.active-column-16 .active-box-resize {display: none;}
.active-column-17 .active-box-resize {display: none;}
.active-column-18 .active-box-resize {display: none;}
.active-column-19 .active-box-resize {display: none;} 
.active-column-19{ display:block; float:left; clear:both; width:96% !important;  line-height: 20px; padding:0px; padding-left: 40px;white-space: normal; font-weight:normal !important;}
.active-scroll-data, .active-templates-row{ height:auto !important}

.labels1 { display:inline; font-family: arial, helvetica, sans-serif; font-size: 12px;  margin-top: 7px; text-decoration: underline; }
.labels1:hover{ text-decoration:none; color:#1d89cf}
.active-templates-search {padding: 0px 0px \0/;}
.active-row-cell { padding: 0px 0px \0/;}
.active-box-resize{ right:-5px \0/;}
.active-box-item{ padding-left:7px \0/;}

@media screen\0  and (-ms-high-contrast: active), (-ms-high-contrast: none) 
{
	.active-templates-search {padding: 0px 0px \0/;}
	.active-row-cell { padding: 0px 0px \0/;}
	.active-box-resize{ right:-5px \0/;}
	.active-box-item{ padding-left:0px \0/;}
	.active-box-image{ margin-left:8px \0/;}
	.active-templates-status {padding-left:10px \0/;}
	.active-templates-header{padding:0px 0px; box-sizing:border-box;}
}

@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) 
{  
	.active-scroll-search, .active-scroll-left, .active-scroll-data, .active-scroll-top{ width:100% !important;}
} 
.active-templates-row{display: inline-block;min-width: 100%;overflow-y: visible;width: 100%;}
.akkencustomicons ul li:hover ul{ width:auto; min-width:154px; padding:0px;}
.akkencustomicons ul li:hover ul li:empty{display:none;}
/*Ramana Added New Styles for New UI*/
/*.active-column-9{ display:block; float:left; clear:both; width:100% !important; background:#f6f7f9; margin-bottom:1px;} */
.assignmentblkNew{ float:left; margin-left:30px; width: 56%;}
.assignmentblkNew div{  display:inline}
.submissionblkNew{ float:right; width:40%; text-align:right}
.submissionblkNew div{ display:inline}
.submissionblkNew i.fa, .assignmentblkNew i.fa{ margin:0px 5px; font-size:20px;}		
/* .active-selection-true .active-column-9{ background: #f6f7f9 !important} */	
.active-selection-true .active-row-cell .assignmentblkNew .fa-square:before{color:inherit !important}	
.active-column-1 .linkrow, .active-column-2 .linkrow, .active-box-item .linkrow, .active-column-19 .lbl_viewhistory, .active-column-3 .linkrow{ color:#3eb8f0; font-size:14px; font-weight:bold; text-decoration:none}
.lbl_viewh istory{ color:#3eb8f0; font-size:12px; font-weight:bold; text-decoration:none; }
.active-column-1 .linkrow:hover, .active-column-2 .linkrow:hover, .active-box-item .linkrow:hover, .lbl_viewhistory:hover{ text-decoration:underline}
	
.active-grid-row:hover .active-templates-text a .lbl_viewhistory{ color:#fff}
.active-grid-row:hover .active-templates-text a{ text-decoration:underline;}
@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) 
{
	.submission_notes{ width:900px;}
}
.sub_status{ margin-right: 15px; }
.manageSubSticky{position:fixed; top:70px; left:0px; width:100%; height:40px; padding:10px 0px; z-index:999; background:#fff; border-bottom:solid 1px #ccc;}
.manageSubCandDtls{ position:fixed; width:100%; height:74px; z-index:999}
.active-selection-true a, .active-selection-true a .linkrow, .active-selection-true .active-row-cell a .linkrow, .active-selection-true .active-row-cell a, .active-selection-true .lbl_viewhistory{text-decoration:underline }
.active-selection-true:hover .fa-file-text:before {  color: #148aa5 !important;}
.active-selection-true:hover .fa-book:before{ color:#2a82d7 !important}
.active-selection-true:hover .fa-user-plus:before {color: #009684 !important;}
.active-selection-true:hover .fa-ticket:before {color: #fd7222 !important;}
.active-selection-true:hover .fa-industry:before {color: #beae0b !important;}
.active-selection-true:hover .fa-list-alt:before {color: #a51497 !important;}
.active-selection-true:hover .fa-calendar-check-o:before {color: #d7462c !important;}
.active-selection-true:hover .fa-suitcase:before { color: #a51497 !important; }
.active-selection-true:hover .fa-tasks:before {color: #148aa5 !important;}
.DisplayNone{ display:none; }
.DisplayBlock{ display:block; padding:8px 10px 13px 50px; line-height:18px; }
.fa-plus-square:before{color:#a1a1a1 !important;  }
.fa-minus-square:before{color:#a1a1a1 !important;}
.active-grid-row:hover .fa-plus-square:before{ color:#fff !important;}
.active-grid-row:hover .fa-minus-square:before{ color:#fff !important;}
.active-selection-true:hover .fa-plus-square:before{ color:#bebebe !important;}
.active-selection-true:hover .fa-minus-square:before{ color:#bebebe !important;}
.active-column-19{ padding:0px !important; line-height:0px;}
.akkencustomicons ul li a .fa-plus-square:before{ color:#d8c80f !important; }
@media screen and (-webkit-min-device-pixel-ratio:0) { 
    .active-column-0 { width: 34px !important; text-align:center;}
}
.active-column-0{ margin-left: 8px; }
.active-column-20{ width:1px !important;}
</style>
<script type="text/javascript">
//For Toggling individual submission notes
function submission_notes_toggle(element1,class1,class2,eleno)
{	
	var element = document.getElementById(element1+"_"+eleno);
	if(element.className==class1)
	{
		element.className = class2;
		document.getElementById('toggle_btn_'+eleno).innerHTML="<i class='fa fa-plus-square fa-lg'></i>";
	}
	else if (element.className==class2)
	{
		element.className = class1;
		document.getElementById('toggle_btn_'+eleno).innerHTML="<i class='fa fa-minus-square fa-lg'></i>";
	}
	
	chk_default_toggle();
} 
//For Toggling all submission notes
function submission_notes_toggle_all(class1,class2)
{	
	var element = document.getElementsByName("sub_notes[]");
	var default_toggle = document.getElementById('toggle_btn_default');
	if(default_toggle.className=='icon_expand')
	{
		default_toggle.className = "icon_hide";
		default_toggle.innerHTML="<i class='fa fa-minus-square fa-lg'>";
		for(var i=0; i<element.length; i++)
		{
			if(element[i].className!="")
			{
				element[i].className = class1;
			}
			if(document.getElementById('toggle_btn_'+i).innerHTML!="")
			{
				document.getElementById('toggle_btn_'+i).innerHTML="<i class='fa fa-minus-square fa-lg'>";
			}
		}
	}
	else if(default_toggle.className=='icon_hide')
	{
		default_toggle.className = "icon_expand";
		default_toggle.innerHTML="<i class='fa fa-plus-square fa-lg'>";
		for(var i=0; i<element.length; i++)
		{
			if(element[i].className!="")
			{
				element[i].className = class2;
			}
			if(document.getElementById('toggle_btn_'+i).innerHTML!="")
			{
				document.getElementById('toggle_btn_'+i).innerHTML="<i class='fa fa-plus-square fa-lg'>";
			}
		}
	}
} 
//For checking default Toggle
function chk_default_toggle()
{
	var element = document.getElementsByName("sub_notes[]");
	var default_toggle = document.getElementById('toggle_btn_default');
	array_toggle = new Array();
	for(var i=0; i<element.length; i++)
	{
		if(element[i].className == "DisplayBlock")
		{
			array_toggle.push("DisplayBlock");
		}
		else
		{
			array_toggle.push("DisplayNone");
		}
	}
	if(array_toggle.indexOf("DisplayNone")==-1)
	{
		//console.log("Minimized Blocks not found");
		default_toggle.className = "icon_hide";
		default_toggle.innerHTML="<i class='fa fa-minus-square fa-lg'>";
	}
	if(array_toggle.indexOf("DisplayBlock")==-1)
	{
		//console.log("Minimized Blocks found");
		default_toggle.className = "icon_expand";
		default_toggle.innerHTML="<i class='fa fa-plus-square fa-lg'>";
	}
}

function doManage(nameManage,name1)
{
	var v_width  = 600;
	var v_heigth = 300;
	var top=(window.screen.availHeight-v_heigth)/2;
	var left=(window.screen.availWidth-v_width)/2;
	var remote_act=window.open("../../../BSOS/Manage/add.php?nameManage="+nameManage+"&name1="+name1,"resume",'width=600,height=300,statusbar=no,menubar=no,scrollbars=yes,dependent=no,resizable=yes,hotkeys=no,left='+left+',top='+top);
	remote_act.focus();
}

//BLOCKING THE NON NUMERIC ONES
function blockNonNumbers(obj, e, allowDecimal, allowNegative)
{
    var key;
    var isCtrl = false;
    var keychar;
    var reg;

    if(window.event) {
            key = e.keyCode;
            isCtrl = window.event.ctrlKey
    }
    else if(e.which) {
            key = e.which;
            isCtrl = e.ctrlKey;
    }

    if (isNaN(key)) return true;

    keychar = String.fromCharCode(key);

    // check for backspace or delete, or if Ctrl was pressed
    if (key == 8 || isCtrl)
    {
        return true;
    }

    reg = /\d/;
    var isFirstN = allowNegative ? keychar == '-' && obj.value.indexOf('-') == -1: false;
    var isFirstD = allowDecimal ? keychar == '.' && obj.value.indexOf('.') == -1 : false;

    return isFirstN || isFirstD || reg.test(keychar);
}

// Applying the Pay Rate Type and Pay Rate filter if pay rate exists.
function filterWithPayVals()
{
	var pay_rate_option = $('#pay_rate_type').val();
	var pay_rate_val = $("#pay_rate_txt").val();

	if(pay_rate_val!="" && isNaN(pay_rate_val)){
		alert('Please enter valid pay rate value.');
	        return;
	}

	gridExtraFields['pay_rate_type'] = pay_rate_option;
	gridExtraFields['pay_rate_txt'] = pay_rate_val;
	doGridSearch('reset');

}

function resetPayRateFilters()
{

	var pay_rate_option = $('#pay_rate_type').val();
	var pay_rate_val = $("#pay_rate_txt").val();

	// if(pay_rate_val!="" && pay_rate_val!=0 && pay_rate_val!='.')
	// {
		$('#pay_rate_type').prop('selectedIndex',0);
		$("#pay_rate_txt").val("");
		gridExtraFields['pay_rate_type'] = "";
		gridExtraFields['pay_rate_txt'] = "";
		doGridSearch('reset');		
	// }
}

function updatePayRateFilter()
{
	var pay_rate_option = $('#pay_rate_type').val();

	//need to update the pay rate type if any newly added to the submission upate.
	$.ajax({
        url: '/include/displaysubmissionshiftrates.php',
        type: 'POST',
        data: 'posId=<?php echo $posid; ?>&selectedVal='+pay_rate_option+'',
        dataType : 'text',
        async: true,
        success: function (data) {
	        if (data != "")
			{
				$('#pay_rate_type').html(data);
			}
		}
	});
}
</script>


<?php
	$css_shiftcolors =  array('#000000', '#444444', '#666666', '#999999', '#cccccc', '#c0ab78', '#86825a', '#a08f70'
				, '#c4225e', '#c48467', '#ffff00', '#befd1d', '#5d7dad', '#064fae', '#9900ff', '#ff00ff'
				, '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#cfe2f3', '#d9d2e9', '#ead1dc'
				, '#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#9fc5e8', '#b4a7d6', '#d5a6bd'
				, '#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6fa8dc', '#8e7cc3', '#c27ba0'
				, '#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3d85c6', '#674ea7', '#a64d79'
				, '#990000', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#0b5394', '#351c75', '#741b47'
				, '#660000', '#783f04', '#7f6000', '#274e13', '#0c343d', '#073763', '#20124d', '#4C1130'
				, '#456bdb', '#fe9a2e', '#04b431', '#ff0000', '#ffffff'); // last Row for Reserved Shift Colors
	
	foreach($css_shiftcolors as $css_shiftcolor){
		$css_colorcode = str_replace("#","",$css_shiftcolor);
		echo "<style>
			.shiftcss_".$css_colorcode."{
				background: ".$css_shiftcolor." none repeat scroll 0 0;
				display: inline-block;
				height: 15px;
				width: 15px;
				margin: 2px;
				overflow: hidden;
				float: left;
			    }
			</style>";
	}
?>
<style type="text/css">
.SummaryTopBg{ padding: 5px 10px 14px 10px; }
.KeywordsearchBtn{background: #fff;padding: 10px;border-radius: 4px;margin: 0px;margin-right: 0px;float: right;margin-right: 0px;}
.FilterInputsLbl{font-size: 13px; font-weight: bold; margin-right: 4px; color: #58666e;}
.FilterInputs{width:100px; padding: 6px 4px; border-radius: 4px; border:solid 1px #ccc; font-size: 13px;}
.KeywordsearchBtnNew .aemailbutton1{ display: inline-block; width: 20px; margin: 0px; padding: 5px;}

    <?php
    if($display_sub_rates == "YES")
    {
        ?>	    
    .manageSubCandDtls{ height: 90px;}
    .SummaryTopBg{ height: 82px;}
    .SummaryTopBg .modcaption{ line-height: 18px; }  
    .manageSubSticky{ top: 85px;}
    .SubGrid-gapT{ padding-top: 93px !important;}
    <?php
    }
    ?>
    
</style>
</head>

<body onclick="showDiv1();">
<script language="JavaScript1.2">mmLoadMenus();</script>

<form action=reqnavigate.php method=post name=markreqman>
<input type=hidden name=aa>
<input type=hidden name=addr id=addr value="<?php echo $posid;?>">
<input type=hidden name=ccon>
<input type=hidden name=eemp>
<input type=hidden name=scon>
<input type=hidden name=navigate value=req>
<input type=hidden name=req>
<input type=hidden name="reqid" id="reqid" value="<?php echo $addr;?>">
<input type=hidden name=from value=notmail>
<input type=hidden name=pid value="<?php echo $pid;?>">
<input type=hidden name=count_inter>
<input type=hidden name=addr value="<?php echo $addr;?>">
<input type=hidden name=jobowner value="<?php echo $pos_row[30];?>">
<input type=hidden name=posid id="posid" value="<?php echo $posid;?>">
<input type=hidden name=jobshare value="<?php echo $pos_row[28];?>">
<input type=hidden name=emplist>
<input type=hidden name=from value=notmail>
<input type=hidden name=url>
<input type=hidden name=dest>
<input type=hidden name=page2 value="<?php echo $page2;?>">
<input type=hidden name=page3>
<input type=hidden name=skills value="<?php echo dispfdb($skills);?>">
<input type=hidden name=page5>
<input type=hidden name=page6>
<input type=hidden name=page7>
<input type=hidden name=ldndis>
<input type=hidden name=sno_staff value="<?php echo $scli[1];?>">
<input type=hidden name=delstatus value="<?php echo dispfdb($delstatus);?>">
<input type=hidden name=message1 value="<?php echo $message1;?>">
<input type=hidden name=stat>
<input type=hidden name=con_id value="<?php echo $con_id;?>">
<input type=hidden name=conemail value="">
<input type=hidden name=fdate>
<input type=hidden name=candrn value="<?php echo $candrn;?>">
<input type=hidden name=clientsno value="<?php echo $clientsno;?>">
<input type=hidden name=conttype value="<?php echo $pos_row[23];?>">
<input type=hidden name=position value="<?php echo dispfdb($pos_row[1]);?>">
<input type=hidden name=candids_chk>
<input type=hidden name='hidcompsno' id='hidcompsno' value='' >
<input type="hidden" name="hdnOutlookEnable" id="hdnOutlookEnable" value="<?php echo OUTLOOK_PLUG_IN;?>">
<input type=hidden name='shift_type' id='shift_type' value='<?php echo $shiftType;?>' >
<div id="main">
<td valign=top align=center class=tbldata>
<table width=99% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
	<div id="content">
	<tr>
    <td>
    <div class="manageSubCandDtls">
		<table width=100% cellpadding=0 cellspacing=0 border=0 class="SummaryTopBg subPadB-16">
		<tr>
           
			<td align=left width='60%' style="vertical-align: top;">
                
                
                <font class="modcaption summaryjobname subPadL-0">Submissions For <a href="javascript:openjoborders(<?php echo $posno;?>,'<?php echo $hidemodule;?>');"><?=$pos_row[postitle]?> </a><?=$shiftTypeName?></font>
            <div id="joborder_contact" class="jobIDMblk">
		        <font class="jobIdColor">JOB ID #<?php echo $addr; ?></font>&nbsp;|&nbsp;
				<? 
				if($comp_contact!="") {?>
					<a href="javascript:opencontacts(<?php echo $pos_row[2];?>,'<?php echo $hidemodule;?>');"><font class="labels1"><?=$comp_contact?></font></a> 
				<? } 
				if($title_contact!='') {?>
					&nbsp;|&nbsp;<font class="modcaption2"><?=$title_contact?></font><? } if($comp_name!='') {?>&nbsp;|&nbsp;<font class="modcaption2"><?=$comp_name?></font>
				<? } 
				if($phone_contact!='' && $phone_contact!='--') {?>
					&nbsp;|&nbsp;<font class="modcaption1"><?=$phone_contact?></font><? 
				}?>
			</div>
            </td>
			<?php
			if($display_sub_rates == "YES")
			{
				?>			
				<td style="vertical-align: top;">
	                <div class="KeywordsearchBtn KeywordsearchBtnNew">
					<span class="FilterInputsLbl">Pay Rate Type: </span>
					<select name="pay_rate_type" id="pay_rate_type" class="drpdwne FilterInputs">
	              		<option value="" <?php echo getSel($pay_rate_type,"");?>>ALL</option>
	              		<?php
						$lque = "select mm.rateid,mm.name from 
				resume_status as rs			
				inner join multiplerates_joborder as mj ON (rs.sno=mj.joborderid)
						LEFT JOIN multiplerates_master as mm ON(mm.rateid = mj.ratemasterid)
						WHERE rs.req_id = '".$posid."' and mj.jo_mode = 'submission' GROUP BY mm.rateid order by trim(mm.name) ASC";
						$lres = mysql_query($lque,$db);
						while($lrow = mysql_fetch_row($lres))
							print "<option value='".$lrow[0]."' ".getSel($pay_rate_type,$lrow[0]).">".$lrow[1]."</option>";
						?>
					</select> 
	                <span class="FilterInputsLbl">Pay Rate:</span>
					<input maxlength="10" name="pay_rate_txt" id="pay_rate_txt" value="<?php echo $pay_rate_txt;?>" type="text" onkeypress="if(event.keyCode==13){filterWithPayVals();}" class="FilterInputs" style="width: 50px;"> 

					<span class="aemailbutton1">                    
	                    <a href="javascript:void(0);" name="search" onclick="filterWithPayVals();" title="Search"><i class="fa fa-search"></i> 
	                    </a>
	                </span>  
	                  <span class="aemailbutton1">
	                    <a href="javascript:void(0);" name="reset" onclick="resetPayRateFilters();" title="Reset">
	                      <i class="fa fa-reply"></i>
	                    </a>                    
	                </span>
	                </div>
				</td>
		
			<?php
			}			
			?>
		</tr>
	
		</table>
     </div>
		</td>
	</tr>
	<?php
	if($mailstat=="request")
		print "<script></script>";
	else if($mailstat=="setup")
		print "<script>window.focus();</script>";
	else if($mailstat=="task")
		print "<script>window.focus(); </script>";
	else if($mailstat=="update")
		print "<script>window.focus(); </script>";
	else if($place_link=="place_cand")
 	{
		echo "<script>window.resizeTo(1120,850);window.focus();</script>";
	}
	?>
	</div>
	<?php $xajax->printJavascript(''); ?>
	<div id="grid_form"  style="padding-right:10px;">
	<tr>
		<td>
			<div id="submissionContent" style="display:none;" >
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ProfileNewUI">				 
					<tr>
					  <td colspan="2"><div>Click candidate name to view candidate summary screen. Click Date/Time to view submission email.</div></td>
					</tr>
					<tr>
					  <td><table width="370" border="0" cellpadding="0" cellspacing="8" class="ProfileNewUI">            
                <tr colspan="3">
                  <td colspan="2" nowrap=""><i class="fa fa-file-text" alt="Notes"></i> &nbsp;&nbsp;Create a note about this Candidate &nbsp;(also shows in Job Order)</span></td>
                </tr>
                <tr colspan="2">
                  <td width="281"><i class="fa fa-book" alt="Request Interview"></i> &nbsp;&nbsp;Request an interview with the Contact</td>
                  <td width="33" nowrap="" class="hfontstyle">&nbsp;</td>
                </tr>
                <tr>
                  <td class="hfontstyle" nowrap=""><i class="fa fa-user-plus" alt="Setup Interview"></i>&nbsp;&nbsp;Set up an interview with the Candidate</td>
                  <td class="hfontstyle" nowrap="">&nbsp;&nbsp;</td>
                </tr>
                <tr>
                  <td nowrap=""><i class="fa fa-ticket" alt="Update Status"></i>&nbsp;&nbsp;Change interview status of Candidate&nbsp;(with notes)</td>
                </tr>
              
            </table></td>
					  <td><table cellpadding="0" cellspacing="8" border="0" class="ProfileNewUI">
           
                <tr>
                  <td nowrap="" class="hfontstyle"><i class="fa fa-industry" alt="Forward to Hiring"></i>&nbsp;&nbsp;Place a Candidate (this button sends Candidate to HR Hiring Screens)<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hire an Employee (this button sends Candidate to Accounting for approval)</td>
                </tr>
                <tr>
                  <td class="hfontstyle" align="left"><i class="fa fa-list-alt" alt="Re-Submit"></i>&nbsp;&nbsp;Re-Submit Candidate details to Contact</td>
                </tr>
                <tr>
                  <td class="hfontstyle" align="left"><i class="fa fa-tasks"></i> &nbsp;&nbsp;View details after placement/hiring</td>
                </tr>
                <tr>
                  <td class="hfontstyle" align="left"><i class="fa fa-calendar-check-o" title="Availability Details" alt="Availability Details"></i> &nbsp;&nbsp;View Candidate availability details</td>
                </tr>
            
            </table></td>
					</tr>
				</table>
			</div>

			<tr>
				<td><font class=bstrip>&nbsp;</font></td>
			</tr>
			<tr class="NewGridTopBg">
			<?php
			$infoIcon = "submissionInfoIcon";
			$integratedServices = "|javascript:void(0);|".((TEXT_US_ENABLED == 'Y' && TEXTUS_USER_ACCESS=='Y') ? '<a href=javascript:doTextUS(\'crmsubmissions\');>TextUs Texting</a>~' : '')."".((DEFAULT_CEA=='Y') ? '<a href=javascript:doCEA(\'crmsubmissions\');>Call-Em-All Text/Voice Broadcasting</a>' : '' );

			if(DEFAULT_VINTERVIEW_USER!="")
			{
				
				$integratedServices.='~<a href="javascript:manVInterview('.$posid.')">Manage&nbsp;Video&nbsp;Interviews</a>~<a href="javascript:doVInterview()">Setup&nbsp;Video&nbsp;Interview</a>';
			}

			$bulkMailAccess="|fa fa-envelope~Send Email";
			$bulkMailLink="|javascript:doBulkMail();";
			$bulkResubmitAccess="|fa fa-list-alt~Re-Submit";
			$bulkResubmitLink="|javascript:reSubmitMail();";
			// Bulk Placement Enabling when Admin >> User Management >> Preferences >> JobOrder >> Bulk Place.
			$bulkPlaceBtton='';
			$bulkPlaceBttonScript='';

			if (BULK_PLACE_ACCESS == "Y") {
				$bulkPlaceBtton='|fa fa fa-database~Bulk Place';
				$bulkPlaceBttonScript='|javascript:doBulk_place_manage_sub();';
			}
			
			if($navpage=="joborder")
			{
					$name=explode("|",$infoIcon."|".$vtitle."|fa-globe~Integrated Services|droplist|fa fa-filter~Short&nbsp;List|fa fa-plus-square~Place&nbsp;on&nbsp;Another&nbsp;Job|fa fa-ticket~Update&nbsp;Status".$bulkMailAccess.$bulkResubmitAccess.$bulkPlaceBtton."|fa fa-television~Broadcast|fa fa-times~Close");
					$link=explode("|","|".$vlink.$integratedServices."|javascript:getJobOrder();|javascript:getJobOrderList();|javascript:candUpdateStatus();".$bulkMailLink.$bulkResubmitLink.$bulkPlaceBttonScript."|javascript:dobroadcast_crm();|javascript:removeTaggedMngeSub();");				
			}
			else
			{			
					$name=explode("|",$infoIcon."|".$vtitle."|fa-globe~Integrated Services|droplist|fa fa-filter~Short&nbsp;List|fa fa-plus-square~Place&nbsp;on&nbsp;Another&nbsp;Job|fa fa-ticket~Update&nbsp;Status".$bulkMailAccess.$bulkResubmitAccess.$bulkPlaceBtton."|fa fa-television~Broadcast|fa fa-times~Close");
					$link=explode("|","|".$vlink.$integratedServices."|javascript:getJobOrder();|javascript:getJobOrderList();|javascript:candUpdateStatus();".$bulkMailLink.$bulkResubmitLink.$bulkPlaceBttonScript."|javascript:dobroadcast_crm();|javascript:closeCand();");
			}
			$heading="";
			$menu->showHeadingStrip1($name,$link,$heading,"left");
			?>
			</tr>

			<tr>
			<td style="padding-top:76px" class="SubGrid-gapT">
			<?php
		  	$sel="<select class=gridserbox name=column6 id=aw-column6 onChange=doSearchResetCat()>";
		 	$sel=$sel."<option value=''>All</option>";
			$que1="select sno,name from manage where type='interviewstatus' or type='interview status'  order by name";
			$res1=mysql_query($que1,$db);
			while($dd1=mysql_fetch_row($res1))
				$sel=$sel. "<option  value='".$dd1[0]."' >".$dd1[1]."</option>";
			$sel=$sel."</select>";

			require("Requirement.php");
			?>
			<script>
			var toggle = "<a href="+'"'+"javascript:submission_notes_toggle_all('DisplayBlock','DisplayNone');"+'"'+"style='text-decoration:none;'><span id='toggle_btn_default' class='icon_expand'><i class='fa fa-plus-square fa-lg'></i></span></a>";
			 
			var gridHeadCol = [toggle,"<label class='container-chk'><input type=checkbox name='chk' id='chk' onClick=chke(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>","Submitted&nbsp;Date","Candidate&nbsp;Name","Candidate&nbsp;Phone","Last&nbsp;Updated","Status&nbsp;<?php if(EDITLIST_ACCESSFLAG){ ?> [<a href=javascript:doManage('Interviewstatus','column6');><font class=linkrow>edit list</font></a>]<?php } ?>","Video Interview Status","Placement Status","Submitted on Shifts","Type","","","","","","","","","",""];
			
			var gridHeadData = ["","","<input class=gridserbox type=text name=aw-column2 id=aw-column2>","<input class=gridserbox type=text name=aw-column3 id=aw-column3>","<input class=gridserbox type=text name=aw-column4  id=aw-column4>","<input class=gridserbox type=text name=aw-column5  id=aw-column5>","<span onclick=disableResize();><?=$sel;?></span>","<input class=gridserbox type=text name=aw-column7 id=aw-column7>","<input class=gridserbox type=hidden name=aw-column8 id=aw-column8>","<input class=gridserbox type=hidden name=aw-column9 id=aw-column9>","<span onclick=disableResize();><select id=aw-column10 class=gridserbox name=aw-column10 onChange=doSearchResetCat()><option value=''>All</option><option value='My Candidate'>My&nbsp;Candidates</option><option value='Candidate'>Candidates</option><option value='Employee'>Employees</option></select></span>","","","","","","","","","",""]; 
			
			var gridActCol = ["","","","","","","","","","","","","","","","","","","","",""];
			var gridActData = [];
			var gridValue = "CRM_JoborderMangSub";
			var gridSortCol = 2; 
			var gridSort = 'DESC';
			gridForm=document.forms[0];
			gridSearchResetColumn="1|8|9|11|12|13|14|15|16|17|18|19|20";
			gridExtraFields = new Array();
			gridExtraFields['posid']='<?php echo $posid;?>';
			gridExtraFields['hidemodule'] = '<?=$_GET['hidemodule'];?>';

			//Consider the pay rate type and pay rate values in the grid only display_sub_rates flag is "YES"
			gridExtraFields['display_sub_rates'] = '<?php echo $display_sub_rates; ?>';
			<?php
			if($display_sub_rates == "YES")
			{

				?>
				gridExtraFields['pay_rate_type']='<?php echo ($pay_rate_type!="")?$pay_rate_type:"";?>';
				gridExtraFields['pay_rate_txt']='<?php echo ($pay_rate_txt!="")?$pay_rate_txt:"";?>';
				<?php
			}
			?>

	       	initGrids(21);
			xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);
	   		</script>
			</td>
			</tr>

			<tr class="NewGridBotBg">
			<?php //$menu->showHeadingStrip1($name,$link,$heading); ?>
			</tr>
		</div>
		</td>
	</tr>
	</div>
</table>
</td>
</table>
</div>
</form>
<form name=conreg method=post>
<input type=hidden name=posid id="posid" value="<?php echo $posid;?>">
<input type=hidden name=sno_staff value="<?php echo $scli[1];?>">
<input type=hidden name=showrec>
</form>
</body>
</html>
