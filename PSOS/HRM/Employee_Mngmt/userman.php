<?php
	require("global.inc");
	require("onboarding_config.inc");
	require_once("docusign/DocuSignDB.php");
	$XAJAX_ON="YES";
	$XAJAX_MOD="EmployeeManagement";
	$GridHS=true;

	require("Menu.inc");
	$menu=new EmpMenu();

	$menu->showHeader("hrm","Employee Management","5");
        
    /* Links displayed based on whether Docusign is enabled */
	$docuSignDB 	= new DocuSignDB();
    	$POBNameTop 	= '';
	$POBLinkTop 	= '';
	$POBNameBottom 	= '';
	$POBLinkBottom 	= '';
	$ACANameTop 	= '';
	$ACALinkTop 	= '';
?>
<!--External CSS Added for POP UP ------------>
<link type="text/css" rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css">
<!--Loads modalbox css -->
<link rel="stylesheet" type="text/css" media="all" href="/BSOS/css/shift_schedule/calschdule_modalbox.css" />
<!--External CSS Added for POP UP -(END)------------>
<link rel="stylesheet" href="/BSOS/css/popup_styles.css" media="screen" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/educeit.css"/>
<link type="text/css" rel="stylesheet" href="/BSOS/css/merge.css"/>
<script src=/BSOS/scripts/preferences.js language=javascript></script>
<script src=scripts/userman.js language=javascript></script>
<?php require_once("TextUs.php");?>
<script type="text/javascript" src="/BSOS/TextUs/scripts/textus.js"></script>
<script type="text/javascript" src="/BSOS/wotc_mja/scripts/wotc_mja.js"></script>
<script type="text/javascript" src="/BSOS/Prophecy/scripts/prophecy_caregiver.js"></script>
<script type="text/javascript" src="/BSOS/Sterling/scripts/scripts.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script type="text/javascript" src="/BSOS/scripts/common_ajax.js"></script>
<script type="text/javascript" src="/BSOS/scripts/OutLookPlugInDom.js"></script>
<script type="text/javascript" src="/BSOS/eSkill/scripts/eSkill.js"></script>
<script type="text/javascript" src="/BSOS/scripts/hrworkcycles.js"></script>
<!-- loads jquery & jquery modalbox -->
<!--External JS Added for POP UP - ------------>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.min.js"></script>
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script>
<!--External JS Added for POP UP -(END)------------>
<script>OutLookPlugInDom['Enable']="<?php echo OUTLOOK_PLUG_IN;?>";</script>
<script language="javascript">

function resUp()
{
	var v_heigth = 620;
	var v_width  = 980;
        var top1=(window.screen.availHeight-v_heigth)/2;
        var left1=(window.screen.availWidth-v_width)/2;

	remote=window.open("/BSOS/HRM/Hiring_Mngmt/resup.php","UploadResume","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes");
	remote.focus();
}

function openNewWindow()
{	
	var result = ('<?php echo ACA_USER_ACCESS;?>' == 'Y' )?gridActData[gridRowId][24]:gridActData[gridRowId][23];
	var v_width  = 1150;
	var v_heigth = 620;
	
	remote=window.open("getnewconreg.php?command=emphire&addr=new&rec="+result,"HRM_Employee_Mngmt","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left=30,top=30,dependent=yes");
	remote.focus();
}

</script>
<script language="JavaScript" src="scripts/mm_menu.js"></script>
<script language="JavaScript" type="text/JavaScript">
function addToEverifyQ(mod)
{
	var oldval = "";
	var slid = "";
	
	if(mod == "hrmEmployeeMngmt")
	{
		form=document.tree;
		numAddrs = numSelected();
		valAddrs = valSelected2();
	}

	if(!numAddrs)
	{
		alert("You need to select a record to add to E-Verify Queue.");
		return;
	}
	else if(numAddrs > 500)
	{
		alert("Please Select 500 or less than 500 records to add to E-Verify Queue.");
		return;
	}
	$().modalBox({'html':'<div id="attribute-selector"><img id="preloaderW" src="/BSOS/images/preloader.gif" class="preloaderEvfy" ><div><iframe id="addToEverifyQueView" src="/BSOS/EVerify/addToQue.php?ids='+valAddrs+'" border="0" width="100%" scrolling="no" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0" style="width:100%;overflow:hidden;height:180px;position:relative;top:0px;left:0px;"></iframe></div></div>'});
	$("#modal-wrapper").addClass("Evfymodal-wrapper");
	$("#modal-wrapper").css({top: 150, position:'absolute', left: 275, width: 555});
}

function viewHis1()
{
	window.location.href="emphis.php";
}
</script>
<!--[if IE]>
<style>
    #tca {
        margin-left:-6px !important;
    }
</style>
<![endif]-->
<style type="text/css">
/* Integration Columns Display None when disabled mode */
<?php if(DOCUSIGN_ENABLED=='N') { ?>
/*.active-column-11{width:0px;display:none !important;}
.active-column-12{width:0px;display:none !important;}*/
<?php } else { ?>
/*.active-column-11{min-width:100px;}
.active-column-12{min-width:100px;}*/
<?php } ?>

<?php if(EVERIFY_ACCESS=='N') { // Regarding E-Verify ?>
.active-column-13{width:0px;display:none !important;}
<?php } else { ?>
.active-column-13{min-width:110px;}
<?php } ?>
<?php if(WOTC_MJA_ENABLED=='N') { ?>
.active-column-14{width:0px;display:none !important;}
<?php } else { ?>
.active-column-14{min-width:100px;}
<?php } ?>
<?php if(PROPHECY_ENABLED=='N') { ?>
.active-column-15{width:0px;display:none !important;}
<?php } else { ?>
.active-column-15{min-width:60px;}
<?php } ?>
<?php if(STERLING_ENABLED=='N') { ?>
.active-column-16{width:0px;display:none !important;}
<?php } else { ?>
.active-column-16{min-width:130px;}
<?php } ?>
<?php if(ESKILL_ENABLED=='N') { ?>
.active-column-17{width:0px;display:none !important;}
<?php } else { ?>
.active-column-17{min-width:100px;}
<?php } ?>
/*.active-column-15 .active-box-resize {display: none;}
.active-column-15 {width:130px;}*/
.black_overlay{
    display: none;
    position: fixed;
    top: 0%;
    left: 0%;
    width: 100%;
    height: 108%;
    background-color: #000;
        -moz-opacity: 0.5;
        opacity:.55;
        z-index:9998;
    filter: alpha(opacity=80);
}
.dshtblbgcolor{
	background-color: #0193c9 !important;
}
.dstr2bgcolor{
		
	}
#smtableid input[type=text]
{
    font-family : Arial;
    font-size:11px;
    border:solid 1px #CCCCCC;
    height:24px;
    width:200px !important;
}

.smfont
{
    font-family : Arial;
    font-size:11px;
}
.newedittrclass table
{
    
}
.newedittrclass table td
{
    /*padding:2px 3px 0px 0px;
    vertical-align:top;*/
    font-weight:bold;
	color:#fff;
}
.newedittrclass img{vertical-align:middle;}
.newedittrclass font.link6{ padding-left:5px;}
.shiftnewbg
{
    background:#CFF3FF;
    border:#00B9F2 solid 3px;
}

.tr2bgcolor
{
    background:#CFF3FF;
}
tr.tr2bgcolor td
{
    border-bottom:none
}
.erro_class
{
    border:solid 1px #F3C8C9;
}
.akkencustomicons ul li:hover ul{ width:auto; min-width:154px; padding:0px;}
.akkencustomicons ul li:hover ul li:empty{display:none;}
.preloaderWotc,.preloaderEvfy{position:absolute; margin-left:-16px; margin-top:-16px; top:50%; left:50%;}
.akkenNewClose i.fa-2x:before{color:#89979f !important; font-size:20px;}
.Wotcmodal-wrapper,.Evfymodal-wrapper{ top:50% !important; left:50% !important; margin-top:-90px !important; margin-left:-277px !important;  }

.prophecyIniModel #attribute-selector .scroll-area{ height:440px; width:700px;  }
.prophecyIniModel{position:fixed !important; top:50% !important; left:50% !important; margin-top:-220px !important; margin-left:-350px !important;}
.prophecyViewModel #attribute-selector .scroll-area{ height:520px; width:750px;  }
.prophecyViewModel{margin-top:-260px !important; margin-left:-375px !important;left:50% !important;top:50% !important;position: fixed !important;}
.sterlingViewModel #attribute-selector .scroll-area{ height:370px; width:750px;  }
.sterlingViewModel{margin-top:-260px !important; margin-left:-375px !important;left:50% !important;top:60% !important;}
#preloaderW {left: 50%;margin-left: -16px !important; margin-top: -16px !important;position: absolute;top: 50%;z-index: 99;}
.sterlingmodal-wrapper{ top:50% !important; left:50% !important; margin-top:-90px !important; margin-left:-277px !important;  }
.eSkillmodal-wrapper{ position:fixed !important; top:50% !important; left:50% !important; margin-top:-175px !important; margin-left:-375px !important; height:350px !important;}
#evfy_sub_menu_items{display:none;}

</style>

<form name=tree method="post" action="remuser.php">
<input type=hidden name=addr>
<input type=hidden name=norec value=<? echo $norec ?>>
<input type=hidden name=aa>
<input type=hidden name=employeeRelation>
<input type=hidden name=daction value=nav.php>

<div id="main">
<td valign=top align=center>
<table width=100% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
		<td class="titleNewPad">
		<table width=100% cellpadding=0 cellspacing=0 border=0>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
			<td><font class=modcaption>Employee Management</font></td>
			<td align=right><font class=hfontstyle>Following are the current Active Employees</font></td>
		</tr>
		<?php
		if($varerr=="superuser")
		{
		?>
		<tr>
			<td colspan="2" align="center"><font class=afontstyle4>&nbsp;You can't Delete superuser</font></td>
			
		</tr>
		<?php
		}
		?>
		<tr>
			<td colspan=2><font class=bstrip>&nbsp;</font></td>
		</tr>
		</table>
		<div id="ds_status_message" align="center">&nbsp;</div>
		</td>
	</tr>
	</div>

	<div id="topheader">	
	<tr class="NewGridTopBg">
	<?php
	/////////////Concenated Wotc MJA Form in $integratedServices variable ///////////////////////////
	
	$integratedServices = "|javascript:void(0);|".((TEXT_US_ENABLED == 'Y' && TEXTUS_USER_ACCESS=='Y') ? '<a href=javascript:doTextUS(\'hrmemployees\');>TextUs Texting</a>' : '').((WOTC_MJA_ENABLED == 'Y' && WOTC_MJA_USER_ACCESS=='Y') ? '~<a href=javascript:WotcMJA(\'hrmEmployeeMngmt\');>WOTC-MJA Forms</a>' : '').((PROPHECY_ENABLED == 'Y' && PROPHECY_USER_ACCESS=='Y') ? '~<a href=javascript:prophecyInitiate(\'hrmEmployeeMngmt\');>Relias Prophecy Send Tests</a>' : '').((STERLING_ENABLED=='Y' && STERLING_USER_ACCESS=='Y') ? '~<a href=javascript:sterlingInitiate(\'hrmEmployeeMngmt\',\''.STERLING_INTEGRATION.'\');>Initiate Sterling Package</a>' : '').((ESKILL_ENABLED == 'Y' && ESKILL_USER_ACCESS=='Y') ? '~<a href=javascript:eSkillInitiate(\'EmployeeManagement\');>eSkill Tests</a>' : '').((EVERIFY_ACCESS == 'Y') ? '~<a href="javascript:void(0);">E-Verify</a>^^submenu^^<a href=javascript:addToEverifyQ(\'hrmEmployeeMngmt\');>Add to Queue</a>^<a href=\'eVerifyEmps.php?case_status=Queue\'>View Queued Cases</a>^<a href=\'eVerifyEmps.php?case_status=Open\'>View Open Cases</a>' : '');
	
	if(DOCUSIGN_ENABLED == 'Y' && $docuSignDB->isDocuSignUser()){ 
		$POBNameTop = "fa fa-pencil-square-o~Paperless Onboarding|droplist|";
		$POBLinkTop = "javascript:showDocSignPopUp('grid','empmngmt');|<a href=\"javascript:showDocSignPopUp('grid','empmngmt')\">Paperless Onboarding</a>~<a href=\"javascript:getDSPOBStatus('grid','empmngmt')\">Update POBStatus</a>~<a href=\"javascript:resendDocuments('grid','empmngmt')\">Re-send Documents</a>^^submenu^^<a href=\"javascript:voidDocuments('grid','empmngmt')\">Void Documents</a>|";
	}

	if(ACA_USER_ACCESS == 'Y'){
		$ACANameTop = "|fa fa-arrows~Move to ACA Management";
		$ACALinkTop = "|javascript:movetoACA()";
	}

	if(!chkUserPref($collaborationpref,"1") && OUTLOOK_PLUG_IN=="N")
	{
		$name=explode("|",$POBNameTop."fa fa-thumbs-o-up~Approvals|fa-arrow-circle-up~Export|fa-archive~Delete|droplist|fa fa-user-plus~New&nbsp;Employee".$obDocName."|fa-globe~Integrated Services|droplist".$ACANameTop);
		$link=explode("|",$POBLinkTop."empman.php|javascript:doExport()|javascript:doFire();|<a href=\"javascript:doFire();\">Archive</a>~<a href=\"javascript:viewHis1();\">ViewArchive</a>|javascript:resUp()".$obDocLink.$integratedServices.$ACALinkTop);
	}
	else
	{
		$name=explode("|",$POBNameTop."fa fa-thumbs-o-up~Approvals|fa-arrow-circle-up~Export|fa-envelope~Send&nbsp;Mail|fa-archive~Delete|droplist|fa fa-user-plus~New&nbsp;Employee".$obDocName."|fa-globe~Integrated Services|droplist".$ACANameTop);
		$link=explode("|",$POBLinkTop."empman.php|javascript:doExport()|javascript:doMail()|javascript:doFire();|<a href=\"javascript:doFire();\">Archive</a>~<a href=\"javascript:viewHis1();\">View Archive</a>|javascript:resUp()".$obDocLink.$integratedServices.$ACALinkTop);
	}



	$heading="";
	//$menu->showMainGridHeadingStrip1($name,$link,$heading);
	$menu->showMainGridHeadingStripSubItems($name,$link,$heading);
	?>
	</tr>
	</div>

	<?php
		$ds_stages="<select class=gridserbox id=aw-column11 name=column11 onChange=doSearchResetCat()><option value=''>All</option>";

		$mque="SELECT name, stage_value FROM manage WHERE type='ds_pob' ORDER BY name";
		$mres=mysql_query($mque,$db);
		while($mrow=mysql_fetch_row($mres))
			$ds_stages.="<option value='".$mrow[0]."'>".ucwords($mrow[0])."</option>";
		$ds_stages.="</select>";


		$ev_statuses="";
		$mque="select distinct(case_status_display) from everify_cases where case_status not in ('Backup') order by case_status_display";
		$mres=mysql_query($mque,$db);
		while($mrow=mysql_fetch_row($mres))
			$ev_statuses.="<option value='".$mrow[0]."'>".$mrow[0]."</option>";



		// emp hrm departments
		$deptAccessObj = new departmentAccess();
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
		$emp_hrm_depts="";
		$ehdque="select CONCAT_WS(' - ',department.depcode,department.deptname) AS deptname from department where status='Active' AND sno IN (".$deptAccesSno.") order by deptname";
		$ehdres=mysql_query($ehdque,$db);
		while($ehdrow=mysql_fetch_row($ehdres))
			$emp_hrm_depts.="<option value='".$ehdrow[0]."'>".$ehdrow[0]."</option>";
		
	?>

	<div id="grid_form">
	<tr>
		<td>
		<?php
		if(ACA_USER_ACCESS == 'Y'){ 
		?>
			<script>
				var gridHeadCol = ["<label class='container-chk'><input type=checkbox name='chk' id='chk' onClick=chke(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>","First Name","Last Name","Employee ID","SSN","Phone","Email","Job Status","Type","Status","HRM Department","POB Status","I9 Status","E-Verify Status","WOTC Status","RPC","Sterling Order Status","eSkill Tests","Visible in ACA","Created By","Created Date","Modified By","Modified Date"];
				var gridHeadData = ["",
				"<input class=gridserbox type=text name=aw-column1 id='aw-column1'>",
				"<input class=gridserbox type=text name=aw-column2 id='aw-column2'>",
				"<input class=gridserbox type=text name=aw-column3 id='aw-column3'>",
				"<input class=gridserbox type=text name=aw-column4 id=aw-column4 >",
				"<input class=gridserbox type=text name=aw-column5 id=aw-column5>",
				"<input class=gridserbox type=text name=aw-column6 id=aw-column6>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column7 name=column7 onChange=doSearchResetCat()><option value=''>All</option><option value='OP'>On Assignment</option><option value='OB'>On Bench</option></select></span>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column8 name=column8 onChange=doSearchResetCat()><option value=''>All</option><option value='W-2'>W-2</option><option value='1099'>1099</option><option value='C-to-C'>C-to-C</option><option value='None'>None</option></select></select>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column9 name=column9 onChange=doSearchResetCat()><option value=''>All</option><option value='N'>Active</option><option value='Y'>Terminated</option></select></span>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column10 name=column10 onChange=doSearchResetCat()><option value=''>All</option><?php echo $emp_hrm_depts;?></select></span>",
				"<span onclick=disableResize();><?php echo $ds_stages;?></span>",
				"<input class=gridserbox type=text name=aw-column12 id=aw-column12 size=15 value=''>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column13 name=column13 onChange=doSearchResetCat()><option value=''>All</option><option value='Open'>Open (Not Closed)</option><?php echo $ev_statuses;?></select></span>",
				"<input class=gridserbox type=text name=aw-column14 id=aw-column14 size=15 value=''>",
				"<input class=gridserbox type=text id=aw-column15 name=aw-column15 value='' style='display:none;'>",
				"<input class=gridserbox type=text id=aw-column16 name=aw-column16 value='' style='display:none;'>",
				"<input class=gridserbox type=text id=aw-column17 name=aw-column17 title='eSkill Tests' style='display:none;'>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column18 name=column18 onChange=doSearchResetCat()><option value=''>All</option><option value='Yes'>Yes</option><option value='No'>No</option></select></span>",
				"<input class=gridserbox type=text name=aw-column19 id=aw-column19>",
				"<input class=gridserbox type=text name=aw-column20 id=aw-column20>",
				"<input class=gridserbox type=text name=aw-column21 id=aw-column21>",
				"<input class=gridserbox type=text id='aw-column22' name=aw-column22>"];

				var gridActCol = ["","","","","","","","","","","","","","","","","","","","","",""];
				var gridActData = [];
				var gridValue = "HRM_EmployeeManagement";
				gridForm=document.tree;
				gridSortCol= 22;
				gridSort="DESC";
				//gridSearchResetColumn="23|";
				initGrids(23);

				xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);
			</script>
		<?php
		}
		else{
		?>
		<script>
				var gridHeadCol = ["<label class='container-chk'><input type=checkbox name='chk' id='chk' onClick=chke(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>","First Name","Last Name","Employee ID","SSN","Phone","Email","Job Status","Type","Status","HRM Department","POB Status","I9 Status","E-Verify Status","WOTC Status","RPC","Sterling Order Status","eSkill Tests","Created By","Created Date","Modified By","Modified Date"];
				var gridHeadData = ["",
				"<input class=gridserbox type=text name=aw-column1 id='aw-column1'>",
				"<input class=gridserbox type=text name=aw-column2 id='aw-column2'>",
				"<input class=gridserbox type=text name=aw-column3 id='aw-column3'>",
				"<input class=gridserbox type=text name=aw-column4 id=aw-column4>",
				"<input class=gridserbox type=text name=aw-column5 id=aw-column5>",
				"<input class=gridserbox type=text name=aw-column6 id=aw-column6>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column7 name=column7 onChange=doSearchResetCat()><option value=''>All</option><option value='OP'>On Assignment</option><option value='OB'>On Bench</option></select></span>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column8 name=column8 onChange=doSearchResetCat()><option value=''>All</option><option value='W-2'>W-2</option><option value='1099'>1099</option><option value='C-to-C'>C-to-C</option><option value='None'>None</option></select></select>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column9 name=column9 onChange=doSearchResetCat()><option value=''>All</option><option value='N'>Active</option><option value='Y'>Terminated</option></select></span>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column10 name=column10 onChange=doSearchResetCat()><option value=''>All</option><?php echo $emp_hrm_depts;?></select></span>",
				"<span onclick=disableResize();><?php echo $ds_stages;?></span>",
				"<input class=gridserbox type=text name=aw-column12 id=aw-column12 size=15 value='' style='display:none;'>",
				"<span onclick=disableResize();><select class=gridserbox id=aw-column13 name=column13 onChange=doSearchResetCat()><option value=''>All</option><option value='Open'>Open (Not Closed)</option><?php echo $ev_statuses;?></select></span>",
				"<input class=gridserbox type=text name=aw-column14 id=aw-column14 size=15 value=''>",
				"<input class=gridserbox type=text id=aw-column15 name=aw-column15 value='' style='display:none;'>",
				"<input class=gridserbox type=text id=aw-column16 name=aw-column16 value='' style='display:none;'>",
				"<input class=gridserbox type=text id=aw-column17 name=aw-column17 style='display:none;' >",
				"<input class=gridserbox type=text name=aw-column18 id=aw-column18>",
				"<input class=gridserbox type=text name=aw-column19 id=aw-column19>",
				"<input class=gridserbox type=text name=aw-column20 id=aw-column20>",
				"<input class=gridserbox type=text id='aw-column21' name=aw-column21>"];

				var gridActCol = ["","","","","","","","","","","","","","","","","","","","",""];
				var gridActData = [];
				var gridValue = "HRM_EmployeeManagement";
				gridForm=document.tree;
				gridSortCol= 21;
				gridSort="DESC";
				//gridSearchResetColumn="22|";
				initGrids(22);

				xajax_gridData(gridSortCol,gridSort,gridPage,gridRecords,gridSearchType,gridSearchFields,gridExtraFields);
			</script>
		<?php
		}
		?>
			
		</td>
	</tr>
	</div>

	<div id="botheader">
	<tr class="NewGridBotBg">
	<!-- <?php //$menu->showMainGridHeadingStrip1($name,$link,$heading); ?> -->
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
</table>
</form>
<script language='javascript'>
var width1=800;
var height1=600;
var top1=(window.screen.availHeight-height1)/2;
var left1=(window.screen.availWidth-width1)/2;
<?php
	if($popup != "")
	{
?>	
	result = "getnewconreg.php?command=emphire&addr=new&rec=<?php echo $popup.'&line='.$recsno.'&con_id='.$con_id."&module_type_appoint=".$_GET['module_type_appoint']; ?>" ;
	remote=window.open(result,"Contact","width="+width1+"px,height="+height1+"px,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes");
	  try{
          remote.focus();
        }
        catch(e)
        {
            var url 	= "/BSOS/Activities/email/clearsession.php";
            var rtype 	= 'rtype';
            var content = "";

            DynCls_Ajax_result(url,rtype,content,'displayResponse()');
       }

    function displayResponse()
    {
      
    }

<?php
	}
?>

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

	window.mm_menu_0515130056_0 = new Menu("root",140,19,"Verdana, Arial, Helvetica, sans-serif",10,"#000000","#000000","#EFEFEF","#CCCCCC","left","middle",3,0,300,-5,7,true,false,true,1,true,true);
	mm_menu_0515130056_0.addMenuItem("<b>Paperless Onboarding</b>","javascript:showDocSignPopUp('grid','empmngmt');");
	mm_menu_0515130056_0.addMenuItem("<b>Update POBStatus</b>","javascript:getDSPOBStatus('grid','empmngmt');");
	mm_menu_0515130056_0.addMenuItem("<b>Void Documents</b>","javascript:voidDocuments('grid','empmngmt');");
	
	window.mm_menu_0515130056_1 = new Menu("root",80,19,"Verdana, Arial, Helvetica, sans-serif",10,"#000000","#000000","#EFEFEF","#CCCCCC","left","middle",3,0,300,-5,7,true,false,true,1,true,true);
	mm_menu_0515130056_1.addMenuItem("<b>Archive</b>","javascript:doFire();");
	mm_menu_0515130056_1.addMenuItem("<b>ViewArchive</b>","javascript:viewHis1();");
	
	mm_menu_0515130056_0.fontWeight="bold";
	mm_menu_0515130056_0.hideOnMouseOut=true;
	mm_menu_0515130056_0.bgColor='#555555';
	mm_menu_0515130056_0.menuBorder=1;
	mm_menu_0515130056_0.menuLiteBgColor='#FFFFFF';
	mm_menu_0515130056_0.menuBorderBgColor='#777777';
	mm_menu_0515130056_0.writeMenus();
	
	mm_menu_0515130056_1.fontWeight="bold";
	mm_menu_0515130056_1.hideOnMouseOut=true;
	mm_menu_0515130056_1.bgColor='#555555';
	mm_menu_0515130056_1.menuBorder=1;
	mm_menu_0515130056_1.menuLiteBgColor='#FFFFFF';
	mm_menu_0515130056_1.menuBorderBgColor='#777777';
	mm_menu_0515130056_1.writeMenus();
</script>

<div id="getdocsigndocs" class="docsignpopup afontstyle">
    <table width="100%" height="100%">
        <tr>
            <td valign="middle" align="center">
                Processing, please wait...<br />
                <br />
                <img src="/BSOS/images/preloader.gif" align="middle" />
            </td>
        </tr>
    </table>
</div>
<div class="docsignpopup afontstyle" id="voiddocuments">
    <table width="100%" cellspacing="0" cellpadding="0" border="0" height="100%" align="center" class="ds_container">
        <tbody>
            <tr id="newtabheadingtr" style="">
                <td valign="middle" align="left" style="height: 20px;" colspan="8" class="newedittrclass">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="dshtblbgcolor">
                        <tbody>
                            <tr>
                                <td align="left">
                                    <table cellspacing="1" cellpadding="1" border="0">
                                        <tbody>
                                            <tr align="left">
                                                <td width="15%" nowrap="">
                                                    <font style="padding-left: 5px; font-family: Arial; font-size: 14px; font-weight: bold; color: #ffffff;">Paperless Onboarding: Void Documents</font>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: auto;">&nbsp;</td>
                                <td style="width: auto;"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr class="dstr2bgcolor">
                <td valign="top" align="center" colspan="2">
                    <div class="ds_scrollable_div" id="section-scrollable" style="height: 195px;">
                        <fieldset align="left" style="margin: 8px 5px 7px; padding: 5px; border: solid 1px #909090; clear: both; text-align: left;">
                            <table width="100%" cellspacing="0" cellpadding="2" border="0" class="crmsummary-content-table afontstyle">
                                <tbody>
                                    <tr>
                                        <td width="100%" colspan="2">Do you want to Void Documents for selected Employee(s)?</td>
                                    </tr>
                                    <tr>
                                        <td>Enter Reason to void the documents<label style="color: red;">*</label></td>
                                        <td><textarea id="reasonforvoid" cols="25" rows="2"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td style="color: red;" id="errormsg"></td>
                                    </tr>
                                    <tr>
                                        <td align="center" colspan="2">Click OK to Void.Click Cancel to Return.</td>
                                    </tr>
                                    <tr>
                                        <td align="right"><input class="button" type="button" align="middle" onclick="voidDocument('grid','empmngmt')" value="OK" id="voiddocs" /></td>
                                        <td align="left"><input class="button" type="button" align="middle" value="Cancel" id="cancelvoiddocs" onclick="closevoidDocument()" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </fieldset>
                        <div style="text-align:left;font-size:13px;font-weight:bold;padding-left:5px;"><span style="color: red;">Note: </span>Use "Re-send Documents" instead of Voiding.</div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div id="voiddocumtsclose" class="docsignpopup afontstyle">
    <table width="100%" cellspacing="0" cellpadding="0" border="0" height="100%" align="center" class="ds_container_sent">
        <tbody>
            <tr style="" id="newtabheadingtr">
                <td valign="middle" align="left" colspan="8" style="height: 20px;" class="newedittrclass">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="dshtblbgcolor">
                        <tbody>
                            <tr>
                                <td align="left">
                                    <table cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr align="left">
                                                <td width="15%" nowrap=""><font style="padding-left: 5px; font-family: Arial; font-size: 14px; font-weight: bold; color: #ffffff;">Paperless Onboarding: Status</font></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: auto;">&nbsp;</td>
                                <td style="width: auto;">
                                    <table cellspacing="0" cellpadding="0" border="0" align="right" style="width: auto;">
                                        <tbody name="toplink" id="toplink">
                                            <tr>
                                                <td><font class="ffontstyle">&nbsp;&nbsp;</font></td>
                                                <td valign="middle">&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td><font class="ffontstyle">&nbsp;&nbsp;</font></td>
                                                <td valign="middle">&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td valign="middle"><font class="ffontstyle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font><i alt="close" class="fa fa-times"></i></td>
                                                <td>
                                                    <a href="javascript:closeVoidDocPopUp();" class="link6"><font style="padding-left: 2px; font-family: Arial; font-size: 12px; font-weight: bold; color: #ffffff !important;">Close</font></a>
                                                </td>
                                                <td><font class="ffontstyle">&nbsp;&nbsp;</font></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2">
                    <fieldset style="margin: 8px 5px 7px; padding: 5px; border: solid 1px #909090; clear: both; text-align: left;" align="left">
                        <legend>
                            <label for="credentials"><font style="font-size: 8pt;">Status </font></label>
                        </legend>
                        <table width="100%" cellspacing="0" cellpadding="2" border="0" class="crmsummary-content-table afontstyle">
                            <tbody>
                                <tr>
                                    <td>Documents are voided successfully.</td>
                                    <td><i alt="Success" class="fa fa-thumbs-o-up"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="resenddocuments" class="docsignpopup afontstyle" style="width: 30%; height:135px !important;">
    <table width="100%" cellspacing="0" cellpadding="0" border="0" height="100%" align="center">
        <tbody>
            <tr id="newtabheadingtr" style="">
                <td valign="middle" align="left" style="height: 20px;" class="newedittrclass">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="dshtblbgcolor">
                        <tbody>
                            <tr>
                                <td align="left">
                                    <table cellspacing="1" cellpadding="1" border="0">
                                        <tbody>
                                            <tr align="left">
                                                <td width="15%" nowrap="">
                                                    <font style="padding-left: 5px; font-family: Arial; font-size: 14px; font-weight: bold; color: #ffffff;">Paperless Onboarding: Re-send Documents</font>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: auto;">&nbsp;</td>
                                <td style="width: auto;"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr class="dstr2bgcolor">
                <td valign="top" align="center">
                    <div style="height: 120px;">
                        <fieldset align="left" style="margin: 8px 5px 7px; padding: 5px; border: solid 1px #909090; clear: both; text-align: left;">
                            <table width="100%" cellspacing="0" cellpadding="2" border="0" class="crmsummary-content-table afontstyle">
                                <tbody>
                                    <tr>
                                        <td width="100%" colspan="2">Do you want to Re-send Documents for selected Employee(s)?</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td style="color: red;" id="resend_errormsg"></td>
                                    </tr>
                                    <tr>
                                        <td align="center" colspan="2">Click OK to Re-send or Cancel to return.</td>
                                    </tr>
                                    <tr>
                                        <td width="50%" align="right"><input class="button" type="button" align="middle" onclick="resendDocument('grid','empmngmt')" value="OK" id="resenddocs" /></td>
                                        <td align="left"><input class="button" type="button" align="middle" value="Cancel" id="cancelresenddocs" onclick="closeResendDocument()" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </fieldset>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div id="resenddocumtsclose" class="docsignpopup afontstyle">
    <table width="100%" cellspacing="0" cellpadding="0" border="0" height="100%" align="center" class="ds_container_sent">
        <tbody>
            <tr style="" id="newtabheadingtr">
                <td valign="middle" align="left" colspan="8" style="height: 20px;" class="newedittrclass">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="dshtblbgcolor">
                        <tbody>
                            <tr>
                                <td align="left">
                                    <table cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr align="left">
                                                <td width="15%" nowrap=""><font style="padding-left: 5px; font-family: Arial; font-size: 14px; font-weight: bold; color: #ffffff;">Paperless Onboarding: Status</font></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: auto;">&nbsp;</td>
                                <td style="width: auto;">
                                    <table cellspacing="0" cellpadding="0" border="0" align="right" style="width: auto;">
                                        <tbody name="toplink" id="toplink">
                                            <tr>
                                                <td><font class="ffontstyle">&nbsp;&nbsp;</font></td>
                                                <td valign="middle">&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td><font class="ffontstyle">&nbsp;&nbsp;</font></td>
                                                <td valign="middle">&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td valign="middle"><font class="ffontstyle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font><i alt="close" class="fa fa-times"></i></td>
                                                <td>
                                                    <a href="javascript:closeResendDocPopUp();" class="link6"><font style="padding-left: 2px; font-family: Arial; font-size: 12px; font-weight: bold; color: #ffffff !important;">Close</font></a>
                                                </td>
                                                <td><font class="ffontstyle">&nbsp;&nbsp;</font></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2">
                    <fieldset style="margin: 8px 5px 7px; padding: 5px; border: solid 1px #909090; clear: both; text-align: left;" align="left">
                        <legend>
                            <label for="credentials"><font style="font-size: 8pt;">Status </font></label>
                        </legend>
                        <table width="100%" cellspacing="0" cellpadding="2" border="0" class="crmsummary-content-table afontstyle">
                            <tbody>
                                <tr>
                                    <td>Documents Re-sent successfully.</td>
                                    <td><i alt="Success" class="fa fa-thumbs-o-up"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="fade" class="black_overlay"></div>
<div style="display: none;" id="tque"></div>
<div style="display: none;" id="oque"></div>
</body>
</html>
<style>
.button
{
	height:inherit;
}
</style>
