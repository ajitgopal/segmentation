<?php
/*
	Modifed Date: Sep 9th,2015
	Modified By: Srikanth Algani
	Purpose: Adding new column 'Assignments Created'.
	TS Task Id:
*/
	$pagec1	= $analytics_activitiespage;

	require_once("functions.inc.php");
	require("Menu.inc");
	require("global_reports.inc");
$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	$menu	= new EmpMenu();	

	if($main=="main" && $view!="myreports")
	{
		
		if(session_is_registered("analytics_activitiespage"))
			session_unregister(analytics_activitiespage);
		unset($analytics_activitiespage);
		$analytics_activitiespage=$pagec1;
	}

	// Start of code for dynamic display of columns of note type
	$arrNoteTypes 		= getAllNoteTypes();
	$noteTypesCount  	= count($arrNoteTypes);
	$loopnote = 0;
	foreach($arrNoteTypes as $key=>$value)
	{
		$arrDynamicFieldNames[$loopnote] = "DynamicNoteType_$key";
		$loopnote++;
	}
	// end of code for dynamic display of columns of note type
	
	// Start of code for dynamic display of columns of note type
	$arrSubRoles 		= getAllRolesTypes();
	$arrSubRolesID 		= getAllRolesTypesID();
	
	$SubRolesCount  	= count($arrSubRoles);
	for($loopRole=0;$loopRole<$SubRolesCount;$loopRole++)
	{
		$arrDynamicFieldNamesRole[$loopRole] = "DynamicSubRole{$loopRole}_$arrSubRolesID[$loopRole]";
	}
	// end of code for dynamic display of columns of note type

	$fieldnames = 	array("EmployeeName",
				"Ecampaigns",
				"Submissions",
				"JobPostings",
				"CandidatesEcampaigned",
				"JobOrdersEcampaigned",		
				"CandidatesSubmitted",
				"Interviews",
				"CandidatesPlaced",
				"JobOrders",
				"JobOrdersModified",
				"Companies",
				"CompaniesModified",
				"Contacts",
				"ContactsModified",
				"Candidates",
				"CandidatesModified",
				"Revenue",
				"Appointments",
				"Events",
				"SentMail",
				"ReceivedMail",
				"RespondedDetails",
				"AssignmentsCreated",
				"CreatedTasks",
				"ModifiedTasks",
				"CompletedTasks",
				"ActTypeNotes",
				"CandsApplied",
				"createdDate",
				"createdUser",
				"modifiedDate",
				"modifiedUser",
				"Source",
				"SourceType",
				"Owner",
				"Status",
				"CandidateType",
				"ProfileTitle",
				"JobsApplied"
			);

	if($noteTypesCount)
		$fieldnames 	= pushArrayElements($fieldnames,$arrDynamicFieldNames);
	if($SubRolesCount)
		$fieldnames 	= pushArrayElements($fieldnames,$arrDynamicFieldNamesRole);

	if($view=="myreport")
	{
		$rquery		= "SELECT reportoptions FROM reportdata WHERE reportid ='$id'";
		$rresult	= mysql_query($rquery,$db);
		$vrowdata	= mysql_fetch_row($rresult);

		$vrow		= explode("|username->",$vrowdata[0]);
		$analytics_activitiespage = $vrow[0];
		$cusername	= $vrow[1];

		if(strpos($analytics_activitiespage,"|username->") != 0)
			$analytics_activitiespage	= $vrow[0];

		if(session_is_registered("analytics_activitiespage"))
			session_unregister(analytics_activitiespage);

			unset($analytics_activitiespage);


		$cusername	= $vrow[1];
		session_update("cusername");

		$analytics_activitiespage	= $vrow[0];
		session_update("analytics_activitiespage");

		$rdata=explode("|",$analytics_activitiespage);
	}
	else
	{
		$rdata=explode("|",$analytics_activitiespage);
	}

	$col_order		= explode("^",$rdata[8]);
	$tab			= $rdata[9];
	$customFileldName 	= ($tab=="addr") ? $rdata[0] : "EmployeeName";
	$fieldnames[0] 		= $customFileldName;	
	if($tab == "addr")
	{
		$customfilter 	= $rdata[0];
		$dateopt 	= $rdata[1];
		$style_timeframerow = ($dateopt == "tfcustom") ? "" : "style='display:none'";

		if($rdata[1] == "none")
		{
			$fromdate 	= "";
			$todate  	= "";
		}
		else
		{
			$fromdate 	= $rdata[2];
			$todate 	= $rdata[3];
		}
		
		if($dateopt == "tfcustom") 
		{
			$daterange_fromdate	= $rdata[2];
			$daterange_todate	= $rdata[3];
		}
		else
		{
			$daterange_fromdate	= "";
			$daterange_todate	= "";
		}
	       
		//filter pane values
		$filternames 		= $rdata[4];
		$filtervalues 		= $rdata[5];
		$sortingorder 		= $rdata[6];
		$rdata_count 		= count($rdata);
		
		$esort			= $rdata[7];
		
		if($rdata[11] != "")
			$orient		= $rdata[11];
		else
			$orient		= "landscape";
			
		if($rdata[12] != "")
			$rpaper		= $rdata[12];
		else
			$rpaper		= "letter";

		if($rdata[13] != "")
		{
			$compname	= $rdata[13];
			$check		= "checked";
		}
		else
		{
			$compname	= "";
			$check		= "";
		}

		if($rdata[14] != "")
		{
			$maintitle	= $rdata[14];
			$check1		= "checked";
		}
		else
		{
			$maintitle	= "";
			$check1		= "";
		}
		
		if($rdata[15] != "")
		{
			$sbtitle	= $rdata[15];
			$check2		= "checked";
		}
		else
		{
			$sbtitle	= "";
			$check2		= "";
		}
	
		if($rdata[16] != "")
			$check3		= "checked";
		else
			$check3		= "";

		if($rdata[17] != "")
			$check5		= "checked";
		else
			$check5		= "";						
				
		if($rdata[18] != "")
		{
			$efooter	= $rdata[18];
			$check6		= "checked";
		}
		else
		{
			$efooter	= "";
			$check6		= "";	
		}

		$rcount	= 1;
		
		for($r=19; $r<$rdata_count; $r++)
		{
			$checkvariable	= "colchk".$rcount;
			if($rdata[$r] != "")
			{
				$returnValue 	= substr($rdata[$r], 0, 14);
				if($returnValue == 'DynamicSubRole') 
				{
					$role_data 	= getOneRole($rdata[$r]);
					$role_sno 	= getSnoRole($rdata[$r]);
					$checkvariable 	= 'colchkDynamicSubRole'.$role_sno;
					if($role_data == 1 || $role_data == '1') 
					{
						$$checkvariable = "checked=checked";
					}
					else
					{
						$sortingorder 	= str_replace('^'.$rdata[$r] ,'',$sortingorder);
						if (($key = array_search($rdata[$r], $col_order)) !== false)
						{
							unset($col_order[$key]);
						}
						$$checkvariable = "";
					}
				}
				else if($returnValue == "DynamicNoteTyp")
				{
					$note_data 	= getOneNote($rdata[$r]);
					$note_sno 	= getNotesSno($rdata[$r]);
					$checkvariable 	= 'colchkDynamicNote'.$note_sno;
					if($note_data == 1 || $note_data == '1') 
					{
						$$checkvariable = "checked=checked";
					}
					else
					{
						$sortingorder 	= str_replace('^'.$rdata[$r] ,'',$sortingorder);
						if (($key = array_search($rdata[$r], $col_order)) !== false)
						{
							unset($col_order[$key]);
						}
						$$checkvariable = "";
					}
				}
				else
				{
					$$checkvariable = "checked";
				}
			}
			else
			{
				$$checkvariable = "";
			}

			$rcount++;
		}
		
	}
	else
	{
		$esort		= "ASC";
		$colstr		= "";

		$colchk1 	= "checked"; //EmployeeName
		$colchk2 	= "checked"; //Ecampaigns
		$colchk3 	= "checked"; //Submissions
		$colchk4 	= "checked"; //Interviews
		$colchk5 	= "checked";  //Placements
		$colchk6 	= "checked";  //Tasks
		$colchk7 	= "checked";  // Modified Tasks
		$colchk8 	= "checked";  // Completed Tasks
		$colchk9 	= "checked"; // Notes
		$colchk10 	= "checked"; // Candidates Applied
		$colchk11	= "checked"; // Candidates eCampaigned
		$colchk12 	= "checked"; // Job Orders eCampaigned
		$colchk13 	= "checked"; // Candidates Submitted
		$colchk14 	= "checked";  //Job Orders Created Job Postings
		$colchk15 	= "checked"; // Placement Fee
		$colchk16 	= "checked"; // Job orders
		$colchk17 	= "checked"; // Job ORders Modified
		$colchk18 	= "checked";  //Contacts created  Companies
		$colchk19 	= "checked";  // Companies Modified
		$colchk20 	= "checked";  // Contacts
		$colchk21 	= "checked"; // Contacts Modified
		$colchk22 	= "checked"; // Candidates
		$colchk23 	= "checked"; // Candidates Modified
		$colchk24 	= "checked"; // Appointments
		$colchk25 	= "checked"; // Events
		$colchk26 	= "checked"; // Sent E-Mails
		$colchk27 	= "checked"; // Received e-Mails
		$colchk28 	= "checked"; // Responded Details
                $colchk29 	= "checked"; //Assignments created (New column added)
		$colchk30 	= ""; // Created Date
		$colchk31 	= ""; // Created User
		$colchk32 	= ""; // Modified Date
		$colchk33 	= ""; // Modified User
		$colchk34 	= ""; // Source
		$colchk35 	= ""; // Source Type
		$colchk36 	= ""; // Owner
		$colchk37 	= ""; // Status
		$colchk38 	= ""; // Candidate Type
		$colchk39 	= ""; // Profile Title
                $colchk40 	= ""; // Jobs Applied
                
		// New string added after 'Responded details' for new column
	        $filternames 	= "EmployeeName^Ecampaigns^Submissions^JobPostings^CandidatesEcampaigned^JobOrdersEcampaigned^CandidatesSubmitted^Interviews^CandidatesPlaced^JobOrders^JobOrdersModified^Companies^CompaniesModified          ^Contacts^ContactsModified^Candidates^CandidatesModified^Revenue^Appointments^Events^SentMail^ReceivedMail	^RespondedDetails^AssignmentsCreated^CreatedTasks^ModifiedTasks^CompletedTasks^ActTypeNotes^CandsApplied^";
		// one '^' symbol added for New column in the last
                $filtervalues 	= "^^^^^^^^^^^^^^^^^^^^^^^^^^^";
                // New string added after 'Responded details' for new column
		$sortingorder 	=  "EmployeeName^Ecampaigns^Submissions^JobPostings^CandidatesEcampaigned^JobOrdersEcampaigned^CandidatesSubmitted^Interviews^CandidatesPlaced^JobOrders^JobOrdersModified^Companies^CompaniesModified^Contacts^ContactsModified^Candidates^CandidatesModified^Revenue^Appointments^Events^SentMail^ReceivedMail^RespondedDetails^AssignmentsCreated^CreatedTasks^ModifiedTasks^CompletedTasks^ActTypeNotes^CandsApplied";
		
		
		if($noteTypesCount)
		{
			$colchknumber = 41; // count incremented from 40 to 41 as the new column added
			for($loopnote = 0; $loopnote<count($arrDynamicFieldNames); $loopnote++)
			{
				$checkvariable 	= "colchk".$colchknumber;
				$$checkvariable = "checked";
				$colchknumber++;

				$filternames 	.= $arrDynamicFieldNames[$loopnote]."^";
				$filtervalues 	.= "^";
				$sortingorder 	.= "^".$arrDynamicFieldNames[$loopnote];

			}
		}
		if($SubRolesCount)
		{  
			if($noteTypesCount)
			{
			      $colchknumber = $colchknumber;
			}
			else 
			      $colchknumber = 41; // count incremented from 40 to 41 as the new column added

			for($loopRole=0;$loopRole<count($arrDynamicFieldNamesRole);$loopRole++)
			{
				$role_sno 	= getSnoRole($arrDynamicFieldNamesRole[$loopRole]);
				$checkvariable 	= 'colchkDynamicSubRole'.$role_sno;
				$$checkvariable = "";
				$colchknumber++;
			}
		}
	
		$check		= "checked";
		$check1		= "checked";
		$check2		= "checked";
		$check3		= "checked";
		$check4		= "checked";
		$check5		= "checked";
		$check6		= "";
		
		$dateopt 	= "none";
		$fromdate 	= "";
		$ccheck  	= "";
		$style_timeframerow = "style='display:none'";

		
		$orient		= "landscape";
		$rpaper		= "letter";
		$compname	= $companyname;
		$maintitle	= "Activities Report";
		$sbtitle	= "Activities";
		$efooter	= "";
		$alignn		= "standard";
	}	


	
	if($sortingorder)
	{
		$sortingorder_array = explode("^",$sortingorder);
	}
	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";

	}
	
	function sel($a,$b)
	{
		if($a==$b)
			return "checked";
		else
			return "";
	}
	
	

?>
<html>
<head>
<script language="javascript">
function chkClose()
{
	form1.action="../ses.php";
	form1.submit();
	window.close();

}
</script>
<title>Customize</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/Analytics/Reports/analytics.css">
<!-- <link rel="stylesheet" type="text/css" href="/BSOS/css/ui.dropdownchecklist.css" /> -->
<link rel="stylesheet" type="text/css" href="/BSOS/css/ui.dropdownchecklist_new.css" />
<link rel="stylesheet" type="text/css" href="/BSOS/css/ui.dropdownchecklist.css" />
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/Home/style_screen.css">
<script src=/BSOS/scripts/tabpane.js></script>
<script src=scripts/validatecust.js language="javascript"></script>
<script src=/BSOS/scripts/moveto.js language=javascript></script>
<script src=/BSOS/scripts/common_ajax.js language=javascript></script>
<script src=scripts/link.js language=javascript></script>
<script src=/BSOS/scripts/jquery-min.js language="javascript"></script>
<script src=/BSOS/scripts/ui.core-min.js language="javascript"></script>
<script src=/BSOS/scripts/ui.dropdownchecklist-min.js language="javascript"></script>
</head>
<body>
<form name="form1" action=requirements.php method=post>
<input type=hidden name=tab value='tabview'>
<input type=hidden name=analytics_activitiespage>
<input type=hidden name=daction value='storereport.php'>
<input type=hidden name=tabnam value='addr'>
<input type=hidden name=dateval value="<?php echo $todate1;?>">
<input type="hidden" name="main" value="<?php echo $main;?>">
<input type="hidden" name="id" value="<?php echo $id;?>">
<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0>
	<div id="content">
	<tr>
	<td>
		<table width=100% cellpadding=0 cellspacing=0 border=0>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=modcaption>&nbsp;&nbsp;Report Customization</font></td>
    		</tr>
    		<tr>
    			<td colspan=2><font class=bstrip>&nbsp;</font></td>
    		</tr>
		</table>
	</td>
	</tr>
	</div>
	<div id="grid_form">
	<tr>
	<td>
	<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">

	<tr>
		<td width=100% valign=top align=center>
		<div class="tab-pane" id="tabPane1">
        <script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
			<div class="tab-page" id="tabPage11">
			<h2 class="tab">Report</h2>
				<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) );</script>
                <div class="tab-pane" id="tabPane2">
                <script type="text/javascript">tp2 = new WebFXTabPane( document.getElementById( "tabPane2" ) );</script>
               
				<div class="tab-page" id="tabPage21" >
				<h2 class="tab">Customize</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage21" ));</script>
				<?php require("viewcust.php");?>
				</div>

				<div class="tab-page" id="tabPage22" >
				<h2 class="tab">Columns</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage22" ));</script>
				<?php require("viewcolumn.php");?>
				</div>
				
				<div class="tab-page" id="tabPage23">
				<h2 class="tab">Order</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage23" ));</script>
				<?php require("viewsort.php");?>
				</div>
				
				<div class="tab-page" id="tabPage24">
				<h2 class="tab">Sort</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage24" ));</script>
				<?php require("viewcolsort.php");?>
				</div>
				
				<div class="tab-page" id="tabPage25" >
				<h2 class="tab">Filters</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage25" ));</script>
				<?php require("viewfilter.php");?>
				</div>
				
                <div class="tab-page" id="tabPage26">
				<h2 class="tab">Header/Footer</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage26" ));</script>
				<?php require("viewheader.php");?>
				</div>				
				
				<div class="tab-page" id="tabPage27">
				<h2 class="tab">Page Setup</h2>
				<script type="text/javascript">tp2.addTabPage( document.getElementById( "tabPage27" ));</script>
				<?php require("viewformat.php");?>
				</div>
   
				
			
			 <?php
                if($ind=='')
                    $ind=0;
			 ?>
             </div>
		</td>
		</tr>
	</table>
	</td>
	</tr>
	</div>
</tr>
</table>
</div>
</form>
</body>
</html>