<?php
/*
	Modifed Date: Sep 11th,2015
	Modified By: Srikanth Algani
	Purpose: Adding new column 'Assignments Created'.
	TS Task Id:
*/
	ob_start();
	require("global_reports.inc");
	$rlib_filename="activities.xml";
	require("rlib.inc");
	require("reportdatabase.inc");
	require_once("functions.inc.php");
        $deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	$format1 = end(explode('|',$_REQUEST['analytics_activitiespage']));
	if($format1 == "csv"){
		$format = "csv";
	}
	else if($format == ""){
		$format	= "html";
	}
	$filename	= "activities";
	$module		= "activities";
	
	
	
	function cleanArray($array)
	{
		foreach ($array as $index => $value)
		{
			if(is_array($array[$index]))
				$array[$index] = cleanArray($array[$index]);

			if(empty($value) && $value!=0)
				$array[$index]="";
		}
		return $array;
	}

	if($view == "myreport")
	{
		$rquery		= "select reportoptions from reportdata where reportid='$id'";
		$rresult	= mysql_query($rquery,$db);
		$vrowdata	= mysql_fetch_row($rresult);

		$vrow		= explode("|username->",$vrowdata[0]);
		$analytics_activitiespage	= $vrow[0];
		$cusername	= $vrow[1];

		if(strpos($analytics_activitiespage,"|username->")!=0)
			$analytics_activitiespage=$vrow[0];

		

			$cusername=$vrow[1];
			session_update("cusername");


	
			$analytics_activitiespage	= $vrow[0];
			session_update("analytics_activitiespage");


		$rdata	= explode("|",$analytics_activitiespage);
	}
	else
	{
		if($flag == "subuser")
		{
			
			$analytics_activitiespage	= $pagescompval;
			session_update("analytics_activitiespage");
		}
		if($defaction == "print")
		{
			$cusername	= $username;
			session_register("cusername");
		}
		if($format1 == "csv"){
			$analytics_activitiespage=$_REQUEST['analytics_activitiespage'];
		}
		 $rdata	= explode("|",$analytics_activitiespage);
		 
		$tab	= $rdata[9];
	}

	if($flag == "subuser")
	{
		session_register("cusername");
		$cusername	= $listname_val;
		$flag		= "";
	}
	
	// Start of code for dynamic display of columns of note type
	$arrNoteTypes 		= getAllNoteTypes();
	$noteTypesCount  	= count($arrNoteTypes);
	$datanumber 		= 59;//this number must be changed whenever the rdata static count changes..IMPORTANT

	
	$loopnote = 0;
	foreach($arrNoteTypes as $key=>$value)
	{
		$arrDynamicVariables[$loopnote] 	= "varNoteType{$loopnote}";
		$arrDynamicFieldNames[$loopnote] 	= "DynamicNoteType_{$key}";
		$notetype 				=  "varNoteType{$loopnote}";
		if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
		{
			$$notetype = array($rdata[$datanumber],$value,str_repeat("-",strlen($value)));
			$datanumber++;
		}
		else
		{
			$$notetype = array("DynamicNoteType_{$key}",$value,str_repeat("-",strlen($value)));
		}
			
		$arrNoteReferences[$loopnote] = $$notetype;
		$loopnote++;
	}
	
	// end of code for dynamic display of columns of note type
	$arrNoteReferencesCount = count($arrNoteReferences);	
	
	//start of code for dynamic display of columns of submission commission roles
	$arrSubRoles 	= getAllRolesTypes();
	$arrSubRolesID 	= getAllRolesTypesID();
	$SubRolesCount  = count($arrSubRoles);
	$datanumberRole = 60; //this number must be changed whenever the rdata static count changes..IMPORTANT
	for($loopRole=0;$loopRole<$SubRolesCount;$loopRole++)
	{
		
		if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
		{		
			$arrDynamicVariablesRole[$loopRole] 	= "varSubRole{$loopRole}";
			$arrDynamicFieldNamesRole[$loopRole] 	= "DynamicSubRole{$loopRole}"."_".$arrSubRolesID[$loopRole];
			$SubRole 				= "varSubRole{$loopRole}";

			if($rdata[$datanumber] !='')
				$$SubRole = array($rdata[$datanumber],$arrSubRoles[$loopRole],str_repeat("-",strlen($arrSubRoles[$loopRole])));
			$datanumber++;

		}
		else
		{
			$$SubRole 	= array("DynamicSubRole{$loopRole}"."_".$arrSubRolesID[$loopRole],$arrSubRoles[$loopRole],str_repeat("-",strlen($arrSubRoles[$loopRole])));
		}
			
		$arrRoleReferences[$loopRole] 	= $$SubRole;
		
		
	}// end of code for dynamic display of columns of submission roleas

	$arrRoleReferencesCount = count($arrRoleReferences);
	
	//Code for changing of columns accourding to custom filters
	$customFileldName = ($tab=="addr" || ($view=="myreport" && $vrow[0]!="")) ? $rdata[0] : "EmployeeName";
	$customFileldDisplayName = getDisplayName($customFileldName);

	if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
	{
		$customfilter	= $rdata[0];
		
		$dateopt 	= $rdata[1];//date filter values;
		if($dateopt == 'tflastweek' || $dateopt == 'tflastmonth' || $dateopt == 'tflastyear')
		{
			$date_ranges 	= explode("^",datesModified($dateopt));
			$fromdate 	= $date_ranges[0];
			$todate 	= $date_ranges[1];
		}
		else
		{
			$fromdate 	=  ($rdata[2]) ? date("Y-m-d",strtotime($rdata[2])) : "";
			$todate 	= ($rdata[3]) ? date("Y-m-d",strtotime($rdata[3])) : "";
		}
		
		
		//filter pane values
		$filternames_array 	= explode('^',$rdata[4]);
		$filtervalues_array 	= explode('^',formateSlashes($rdata[5]));
		
		
		//This one is for filtering the Candidates Applied condition when it is not in Job Orders Pane
		if($customFileldName != "CustomJobOrder")
		{
			if(in_array("CandsApplied",$filternames_array))
			{
				$cur_index 	= array_search("CandsApplied",$filternames_array);//filtervalues_array
				if($filtervalues_array[$cur_index] != "")
				{
					$filtervalues_array[$cur_index] = "";
				}
			}
		}//Ends here 
		
		//sort orders
		$sortingorder 	= $rdata[6];		
		if($sortingorder == "")
		{
			$rep_sortorder	= "ASC";
			$rep_sortcol 	= "EmployeeName";
		}
		else
		{
			$rep_sortcol	= $rdata[6];
			$rep_sortorder	= $rdata[7]; //like ASC or DESC
			$sortingorder_array = explode("^",$sortingorder);
		}
		
		$sortarr 	= explode('^',$rdata[8]);
		$filter_count 	= count($filternames_array);
		$sort_count 	= count($sortarr);
		
		/*
		echo "<pre>";
		print_r($rdata);
		echo "</pre>";
		
		echo "<pre>";
		print_r($sortarr);
		echo "</pre>";
		
		echo "<pre>";
		print_r($filternames_array);
		echo "</pre>";
		*/
		
		//for($m=0;$m<count($filternames_array)-1;$m++)
		//{
		//	$returnValue_F 		= substr($filternames_array[$m], 0, 14);
		//	$returnValue_S 		= substr($sortarr[$m], 0, 14);
		//
		//	if(($returnValue_F == 'DynamicSubRole' && $returnValue_S == 'DynamicSubRole') || ($returnValue_F == 'DynamicSubRole' && $sortarr[$m]==''))
		//	{
		//		$sortarr[$m] 	=  $filternames_array[$m];
		//	}
		//} // for loop
	
		//Format  page values;
		$rep_orient	= $rdata[11]!="" ? $rdata[11] : "landscape";
		$rep_paper	= $rdata[12]!="" ? $rdata[12] : "letter";
		
		//header/footer page values;
		$rep_company	= $rdata[13]!="" ? $rdata[13] : "";

		$rep_header	= "Activities Report";
		$rep_title	= "Date From: ".$fromdate." To: ".$todate;
		
		$rep_date	= $rdata[16]!="" ? $rdata[16] : "";
		$rep_page	= $rdata[17]!="" ? $rdata[17] : "";
		$rep_footer	= $rdata[18]!="" ? $rdata[18] : "";
		
		$varEmployeeName[0]	= $rdata[19] = $rdata[0];
		$varEcampaigns[0] 	= $rdata[20];
		$varSubmissions[0] 	= $rdata[21];
		$varInterviews[0] 	= $rdata[22];
		$varCandidatesPlaced[0] = $rdata[23];
		$varCreatedTasks[0] 	= $rdata[24];
		$varModifiedTasks[0] 	= $rdata[25];
		$varCompletedTasks[0] 	= $rdata[26];
		$varActTypeNotes[0] 	= $rdata[27];
		$varCandsApplied[0] 	= $rdata[28];
		$varCandidatesEcampaigned[0] = $rdata[29];
		$varJobOrdersEcampaigned[0] = $rdata[30];
		$varCandidatesSubmitted[0] = $rdata[31];
		$varJobPostings[0] 	= $rdata[32];
		$varRevenue[0] 		= $rdata[33];
		$varJobOrders[0] 	= $rdata[34];
		$varJobOrdersModified[0] = $rdata[35];
		$varCompanies[0] 	= $rdata[36];
		$varCompaniesModified[0] = $rdata[37];
		$varContacts[0] 	= $rdata[38];
		$varContactsModified[0] = $rdata[39];
		$varCandidates[0] 	= $rdata[40];
		$varCandidatesModified[0] = $rdata[41];
		$varAppointment[0] 	= $rdata[42];
		$varEvent[0] 		= $rdata[43];
		$varSentMail[0] 	= $rdata[44];
		$varReceivedMail[0] 	= $rdata[45];
		$varRespondedDetails[0] = $rdata[46];
                $varAssignmentsCreated[0] = $rdata[47]; // New variable added for Assignments created 
		$varcreatedDate[0] 	= $rdata[48];
		$varcreatedUser[0] 	= $rdata[49];
		$varmodifiedDate[0] 	= $rdata[50];
		$varmodifiedUser[0] 	= $rdata[51];
		$varSource[0] 		= $rdata[52];
		$varSourceType[0] 	= $rdata[53];
		$varOwner[0] 		= $rdata[54];
		$varStatus[0] 		= $rdata[55];
		$varCandidateType[0] 	= $rdata[56];
		$varProfileTitle[0] 	= $rdata[57];
		$varJobsApplied[0] 	= $rdata[58];
	}
	else
	{	            		
		//Add field names in this array to show them by default....
                // New string added after 'Responded details' for new column
		$sortarr 	= array("EmployeeName","Ecampaigns","Submissions","JobPostings","CandidatesEcampaigned","JobOrdersEcampaigned","CandidatesSubmitted","Interviews","CandidatesPlaced","JobOrders","JobOrdersModified","Companies","CompaniesModified","Contacts","ContactsModified","Candidates","CandidatesModified","Revenue","Appointments","Events","SentMail","ReceivedMail","RespondedDetails","AssignmentsCreated","CreatedTasks","ModifiedTasks","CompletedTasks","ActTypeNotes","CandsApplied");

		if($noteTypesCount)
			$sortarr = pushArrayElements($sortarr,$arrDynamicFieldNames);


		if($SubRolesCount)
			$sortarr = pushArrayElements($sortarr,$arrDynamicFieldNamesRole);

		$dateopt 	= "none";
		$fromdate 	= "";
		$todate 	= "";

		// New string added after 'Responded details' for new column
		$filternames 	= "EmployeeName^Ecampaigns^Submissions^JobPostings^CandidatesEcampaigned^JobOrdersEcampaigned^CandidatesSubmitted^Interviews^CandidatesPlaced^JobOrders^JobOrdersModified^Companies^CompaniesModified          ^Contacts^ContactsModified^Candidates^CandidatesModified^Revenue^Appointments^Events^SentMail^ReceivedMail	^RespondedDetails^AssignmentsCreated^CreatedTasks^ModifiedTasks^CompletedTasks^ActTypeNotes^CandsApplied^";
		// appended symbol '^' added after 'Responded details' for new column
                $filtervalues = "^^^^^^^^^^^^^^^^^^^^^^^^^^^";
		
		$filternames_array 	= $sortarr;
		$filtervalues_array 	= "";
		$sortingorder_array 	= $sortarr;

		$rep_orient		= "landscape";
		$rep_paper		= "letter";
		
		$rep_company 		= $companyname;
		$rep_header		= "Activities Report";
		$rep_title		= "Date From: ".$fromdate." To: ".$todate;
		$rep_date		= "date";
		$rep_page		= "pageno";
		$rep_footer		= "";	

		$rep_sortorder		= "ASC";
		$rep_sortcol		= "EmployeeName";

		$varEmployeeName[0] 	= "EmployeeName";
		$varEcampaigns[0] 	= "Ecampaigns";
		$varSubmissions[0] 	= "Submissions";
		$varJobPostings[0] 	= "JobPostings";
		$varCandidatesEcampaigned[0] = "CandidatesEcampaigned";
		$varJobOrdersEcampaigned[0] = "JobOrdersEcampaigned";
		$varCandidatesSubmitted[0] = "CandidatesSubmitted";
		$varInterviews[0] 	= "Interviews";
		$varCandidatesPlaced[0] = "CandidatesPlaced";
		$varJobOrders[0] 	= "JobOrders";
		$varJobOrdersModified[0] = "JobOrdersModified";
		$varCompanies[0] 	= "Companies";
		$varCompaniesModified[0] = "CompaniesModified";
		$varContacts[0] 	= "Contacts";
		$varContactsModified[0] = "ContactsModified";
		$varCandidates[0] 	= "Candidates";
		$varCandidatesModified[0] = "CandidatesModified";
		$varRevenue[0] 		= "Revenue";
		$varAppointment[0] 	= "Appointments";
		$varEvent[0] 		= "Events";
		$varSentMail[0] 	= "SentMail";
		$varReceivedMail[0] 	= "ReceivedMail";
		$varRespondedDetails[0] = "RespondedDetails";
		$varCreatedTasks[0] 	= "CreatedTasks";
		$varModifiedTasks[0] 	= "ModifiedTasks";
		$varCompletedTasks[0] 	= "CompletedTasks";
		$varActTypeNotes[0] 	= "Notes";
		$varcreatedDate[0] 	= "createdDate";
		$varcreatedUser[0] 	= "createdUser";
		$varmodifiedDate[0] 	= "modifiedDate";
		$varmodifiedUser[0] 	= "modifiedUser";
		$varSource[0] 		= "Source";
		$varSourceType[0] 	= "SourceType";
		$varOwner[0] 		= "Owner";
		$varStatus[0] 		= "Status";
		$varCandidateType[0] 	= "CandidateType";
		$varProfileTitle[0] 	= "ProfileTitle";
		$varJobsApplied[0] 	= "JobsApplied";
		$varCandsApplied[0] 	= "CandsApplied";                
                $varAssignmentsCreated[0] = "AssignmentsCreated";// New array index added for new column
	}
        
	$varEmployeeName[1] 	= $customFileldDisplayName;

	if($customFileldName == "CustomCandidate")
		$varEcampaigns[1] 	= "Number of eCampaigns";
	else		
		$varEcampaigns[1] 	= "Number of eCampaigns Sent";

	if($customFileldName == "CustomCandidate")
		$varSubmissions[1] 	= "Number of Submissions";
	else		
		$varSubmissions[1] 	= "Number of Submissions Sent";
	
	$varJobPostings[1] 		= "Number of Job Orders Broadcasted";
	$varCandidatesEcampaigned[1] 	= "Number of Candidates eCampaigned";
	$varJobOrdersEcampaigned[1] 	= "Number of Job Orders eCampaigned";
	$varCandidatesSubmitted[1] 	= "Number of Candidates Submitted";
	$varInterviews[1] 		= "Number of Interviews";

	if($customFileldName == "CustomCandidate")
		$varCandidatesPlaced[1] = "Number of Placements";
	else
		$varCandidatesPlaced[1] = "Number of Candidates Placed";

	$varJobOrders[1] 		= "Number of Job Orders Created";
	$varJobOrdersModified[1] 	= "Number of Job Orders Modified";
	$varCompanies[1] 		= "Number of Companies Created";
	$varCompaniesModified[1] 	= "Number of Companies Modified";
	$varContacts[1] 		= "Number of Contacts Created";
	$varContactsModified[1] 	= "Number of Contacts Modified";
	$varCandidates[1] 		= "Number of Candidates Created";
	$varCandidatesModified[1] 	= "Number of Candidates Modified";
	$varRevenue[1] 			= "Placement Fee";
	$varAppointment[1] 		= "Number of Appointments";
	$varEvent[1] 			= "Number of Events";
	$varSentMail[1] 		= "Number of E-Mails Sent";
	$varReceivedMail[1] 		="Number of E-Mails Received";
	$varRespondedDetails[1] 	= "Number of Responded Details";
	$varCreatedTasks[1] 		= "Number of Created Tasks";
	$varModifiedTasks[1] 		= "Number of Modified Tasks";
	$varCompletedTasks[1] 		= "Number of Completed Tasks";
	$varActTypeNotes[1] 		= "Number of Notes";
	$varcreatedDate[1] 		= "Created Date";
	$varcreatedUser[1] 		= "Created User";
	$varmodifiedDate[1] 		= "Modified Date";
	$varmodifiedUser[1] 		= "Modified User";
	$varSource[1] 			= "Source";
	$varSourceType[1] 		= "Source Type";
	$varOwner[1] 			= "Owner";
	$varStatus[1] 			= "Status";
	$varCandidateType[1] 		= "Candidate Type";
	$varProfileTitle[1] 		= "Profile Title";
	$varJobsApplied[1] 		= "Jobs Applied";
	$varCandsApplied[1] 		= "Candidates Applied";	
	$varAssignmentsCreated[1] 	= "Assignments Created";// New array index added for new column
	
	$varEmployeeName[2] 		= "----------------------";
	$varEcampaigns[2] 		= "------------------------------";
	$varSubmissions[2] 		= "-----------------------------";
	$varJobPostings[2] 		= "----------------------------------";
	$varCandidatesEcampaigned[2] 	= "-----------------------------------";
	$varJobOrdersEcampaigned[2] 	= "-----------------------------------";
	$varCandidatesSubmitted[2] 	= "--------------------------------";
	$varInterviews[2] 		= "--------------------------";
	$varCandidatesPlaced[2] 	= "--------------------------------";
	$varJobOrders[2] 		= "----------------------------------";
	$varJobOrdersModified[2] 	= "--------------------------------";
	$varCompanies[2] 		= "----------------------------------";
	$varCompaniesModified[2] 	= "--------------------------------";
	$varContacts[2] 		= "-------------------------------";
	$varContactsModified[2] 	= "-------------------------------";
	$varCandidates[2] 		= "--------------------------------";
	$varCandidatesModified[2] 	= "-------------------------------";
	$varRevenue[2] 			= "------------------";
	$varAppointment[2] 		= "------------------------";
	$varEvent[2] 			= "-------------------";
	$varSentMail[2] 		= "-------------------------";
	$varReceivedMail[2] 		="----------------------------------";
	$varRespondedDetails[2] 	= "--------------------------------";
	$varCreatedTasks[2] 		= "------------------------------------";
	$varModifiedTasks[2] 		= "----------------------------------";
	$varCompletedTasks[2] 		= "----------------------------------";
	$varActTypeNotes[2] 		= "-------------------------------------";
	$varcreatedDate[2] 		= "----------------------";
	$varcreatedUser[2] 		= "----------------------";
	$varmodifiedDate[2] 		= "------------------------";
	$varmodifiedUser[2] 		= "------------------------";
	$varSource[2] 			= "-------------------";
	$varSourceType[2] 		= "------------------";
	$varOwner[2] 			= "--------------------";
	$varStatus[2] 			= "-------------------";
	$varCandidateType[2] 		= "-----------------------";
	$varProfileTitle[2] 		= "-----------------------";
	$varJobsApplied[2] 		= "--------------------";
	$varCandsApplied[2] 		= "-------------------";
	$varAssignmentsCreated[2] 	= "-----------------------------";// New array index added for new column
		
	$rep_sortcolno	= "";
	if($sortarr[0] != "")
	{
		$sortarrCount = count($sortarr);
		for($q=0; $q<$sortarrCount; $q++)
		{
			if($sortarr[$q] == $rep_sortcol)
			{
				$rep_sortcolno = $q;
			}
		}
	}
		
	$qstr	= "";
	$astr	= "";
	$vstr	= "";
	$fromstr = "";
	$str 	= "";	
        // New array value added after 'Respondeddetails' for new column
	$fieldnames 	= array("EmployeeName","Ecampaigns","Submissions","JobPostings","CandidatesEcampaigned","JobOrdersEcampaigned","CandidatesSubmitted","Interviews","CandidatesPlaced","JobOrders","JobOrdersModified","Companies","CompaniesModified","Contacts","ContactsModified","Candidates","CandidatesModified","Revenue","Appointments","Events","SentMail","ReceivedMail","RespondedDetails","AssignmentsCreated","CreatedTasks","ModifiedTasks","CompletedTasks","ActTypeNotes","createdDate","createdUser","modifiedDate","modifiedUser","Source","SourceType","Owner","Status","CandidateType","ProfileTitle","JobsApplied","CandsApplied");
		
	// New array value added after 'varRespondedDetails' for new column
	$variablenames 	= array("varEmployeeName","varEcampaigns","varSubmissions","varJobPostings","varCandidatesEcampaigned","varJobOrdersEcampaigned","varCandidatesSubmitted","varInterviews","varCandidatesPlaced","varJobOrders","varJobOrdersModified","varCompanies","varCompaniesModified","varContacts","varContactsModified","varCandidates","varCandidatesModified","varRevenue","varAppointment","varEvent","varSentMail","varReceivedMail","varRespondedDetails","varAssignmentsCreated","varCreatedTasks","varModifiedTasks","varCompletedTasks","varActTypeNotes","varcreatedDate","varcreatedUser","varmodifiedDate","varmodifiedUser","varSource","varSourceType","varOwner","varStatus","varCandidateType","varProfileTitle","varJobsApplied","varCandsApplied");
				
	if($noteTypesCount)
	{
		$fieldnames 	= pushArrayElements($fieldnames,$arrDynamicFieldNames);
		$variablenames 	= pushArrayElements($variablenames,$arrDynamicVariables);
	}
		
		
	if($SubRolesCount)
	{
		$fieldnames 	= pushArrayElements($fieldnames,$arrDynamicFieldNamesRole);
		$variablenames 	= pushArrayElements($variablenames,$arrDynamicVariablesRole);
	}
		
	$fieldnames[0] =  $customFileldName;


	//This part of code is for assigning the values to data variables.
	$k	= 0;
	$count_sortarr 		= count($sortarr);
	$count_fieldnames 	= count($fieldnames);
	for($q=0 ; $q< $count_sortarr ; $q++)
	{ 
		for($f=0 ; $f<$count_fieldnames ; $f++)
		{
			if($sortarr[$q] == $fieldnames[$f])
			{
				$variable = $$variablenames[$f];
				if($variable[0] != "")
				{ 
					$data[0][$k] 	= $variable[0];
					$headval[0][$k] = $variable[1];
					$headval[1][$k] = $variable[2];
					$k++;
				}
			}
		}
	}
			
	if($k != 0)
	{
		$data[0][$k]	= "link";
		$k++;
		$data[0][$k]	= "link_length";
	}
	
	$i	= 1;
	$actLoop = 1;
	if(($tab=="addr" || ($view=="myreport" && $vrow[0]!="")))
	{
		$condition = 0;
		if($customFileldName == 'CustomJobOrder')
		{
			$job_str 	= "";
			$jobst_index 	= array_search("CustomJoborderStatus",$filternames_array);
			$jobty_index 	= array_search("CustomJobType",$filternames_array);
			$jobca_index 	= array_search("CustomJobCategory",$filternames_array);
			
			if($filtervalues_array[$jobst_index] != '' && $filtervalues_array[$jobst_index] != 'ALL')
				$job_str .= "and posdesc.posstatus = '".$filtervalues_array[$jobst_index]."'"; 
			
			if($filtervalues_array[$jobty_index] != '' && $filtervalues_array[$jobty_index] != 'ALL')
				$job_str .= "and posdesc.postype = '".$filtervalues_array[$jobty_index]."'"; 
			
			if($filtervalues_array[$jobca_index] != '' && $filtervalues_array[$jobca_index] != 'ALL')
				$job_str .= "and posdesc.catid = '".$filtervalues_array[$jobca_index]."'"; 		
		}
		 
		$index 	= array_search($customFileldName,$filternames_array);

		$customFieldColumnName 	= array("EmployeeName" => "emp_list.name","CustomCompanyName" => "staffoppr_cinfo.cname" ,
		"CustomContactName" => "concat_ws(' ',staffoppr_contact.fname,staffoppr_contact.lname)", "CustomJoborderStatus" => "manage.sno" , "CustomJobType" => "manage.sno", "CustomNoteType" => "manage.sno", "CustomJobCategory" => "manage.sno" , "CustomJobOrder" => "posdesc.postitle");
		
		$str	= "";

		if($customFileldName == "EmployeeName")
		{ 
		       $empIndex = array_search("EmployeeName",$filternames_array);
		       if((in_array("EmployeeName",$filternames_array)))
		       {
			       $empval		= explode("***",$filtervalues_array[$index]);				
			       $feidArr1	= explode('!#!',$empval[0]);
			       if(count($feidArr1) > 0)
			       {
				       $c=0;
				       if(!in_array('ALL',$feidArr1))
				       {
					       if($feidArr1[0] != '')
					       {
						       $str .= " AND (";
						       foreach($feidArr1 as $code)
						       {
							       $code = stripslashes($code);
							       if($c==0)
								       $str .="emp_list.name = '".addslashes($code)."'";
							       else	
								       $str .= " OR emp_list.name = '".addslashes($code)."'";
							       $c++;	  
						       }
						       $str .= " )";
					       }
				       }
			       }
			       $empjobtype=$empval[1];
		       }
			
		       if($empjobtype!='ALL' && $empjobtype!="")
			       $str .= "and hrcon_compen.emptype = '$empjobtype'";
		       
		}
		else if($customFileldName == "CustomCandidate")
		{ 
			if($filtervalues_array[$index] != '')
				$str  .= "and CONCAT_WS(' ',candidate_general.fname,candidate_general.mname,candidate_general.lname) like '%$filtervalues_array[$index]%'";
			
			$cindex = array_search("createdUser",$filternames_array);	
			if((in_array("createdUser",$filternames_array)) && $filtervalues_array[$cindex] != '')
				$str  .= "and candidate_list.cuser = '".getStringFilterIds($filtervalues_array[$cindex])."'";
			
			$mindex = array_search("modifiedUser",$filternames_array);
			if((in_array("modifiedUser",$filternames_array)) && $filtervalues_array[$mindex] != '')
				$str  .= "and candidate_list.muser = '".getStringFilterIds($filtervalues_array[$mindex])."'";
			
			$oindex = array_search("Owner",$filternames_array);
			if((in_array("Owner",$filternames_array)) && $filtervalues_array[$oindex] != '')
				$str  .= "and candidate_list.owner = '".getStringFilterIds($filtervalues_array[$oindex])."'";
			
			$soindex = array_search("Source",$filternames_array);
			if((in_array("Source",$filternames_array)) && $filtervalues_array[$soindex] != '')
				$str  .= "and candidate_general.cg_source like '%".addslashes($filtervalues_array[$soindex])."%'";
			
			$stindex = array_search("SourceType",$filternames_array);
			if((in_array("SourceType",$filternames_array)) && $filtervalues_array[$stindex] != '')
				$str  .= "and candidate_general.cg_sourcetype = '".$filtervalues_array[$stindex]."'";
			
			$staindex = array_search("Status",$filternames_array);
			if((in_array("Status",$filternames_array)) && $filtervalues_array[$staindex] != '')
				$str  .= "and candidate_list.cl_status = '".$filtervalues_array[$staindex]."'";
			
			$ctindex = array_search("CandidateType",$filternames_array);
			if((in_array("CandidateType",$filternames_array)) && $filtervalues_array[$ctindex] != '')
				$str  .= "and candidate_list.ctype = '".$filtervalues_array[$ctindex]."'";
			
			$ptindex = array_search("ProfileTitle",$filternames_array);
			if((in_array("ProfileTitle",$filternames_array)) && $filtervalues_array[$ptindex] != '')
				$str  .= "and candidate_general.profiletitle like '%".addslashes($filtervalues_array[$ptindex])."%'";
			
			$cdindex = array_search("createdDate",$filternames_array);
			if((in_array("createdDate",$filternames_array)) && $filtervalues_array[$cdindex] != '' && $filtervalues_array[$cdindex] != '*')
			{
			 	$range = explode("*",$filtervalues_array[$cdindex]);
			 	if($range[0]!='')
					$range[0] = date("Y-m-d",strtotime($range[0]));
			 	else 
			 		$range[0] = ''; 

				if($range[1] != '')
					$range[1] = date("Y-m-d",strtotime($range[1]));
				else
					$range[1] = '';	
				
				$funTimeZone = tzRetQueryStringDTime('candidate_list.ctime','YMDDate','-');
				if($range[0] != "" && $range[1] != "") 
					$str .= " and ".$funTimeZone." >= '".$range[0]."' and ".$funTimeZone."  <= '".$range[1]."'";
				elseif($range[0] != "" && $range[1] == "")
					$str .= "  and ".$funTimeZone." >= '".$range[0]."' ) ";
				elseif($range[1] != "" && $range[0] == "")
					$str .= " and ".$funTimeZone." <= '".$range[1]."' ) ";
			}
			$mdindex = array_search("modifiedDate",$filternames_array);
			if((in_array("modifiedDate",$filternames_array)) && $filtervalues_array[$mdindex] != '' && $filtervalues_array[$mdindex] != '*')
			{
			 	$range = explode("*",$filtervalues_array[$mdindex]);
			 	if($range[0]!='')
					$range[0] = date("Y-m-d",strtotime($range[0]));
			 	else 
			 		$range[0] = ''; 

				if($range[1] != '')
					$range[1] = date("Y-m-d",strtotime($range[1]));
				else
					$range[1] = '';
				
				$funTimeZone = tzRetQueryStringDTime('candidate_list.mtime','YMDDate','-');		
				if($range[0] != "" && $range[1] != "") 
					$str .= " and ".$funTimeZone." >= '".$range[0]."' and ".$funTimeZone." <= '".$range[1]."'";
				elseif($range[0] != "" && $range[1] == "")
					$str .= "  and ".$funTimeZone." >= '".$range[0]."' ) ";
				elseif($range[1] != "" && $range[0] == "")
					$str .= " and ".$funTimeZone." <= '".$range[1]."' ) ";
			}
		}
		else if((in_array($customFileldName,$filternames_array)) && $filtervalues_array[$index] != '')
		{
			//$str.= "and {$customFieldColumnName[$customFileldName]} like '%$filtervalues_array[$index]%'";
			$comp_name = str_replace("\\","",$filtervalues_array[$index]);
			$str.= 'AND REPLACE(LOWER(REPLACE('.$customFieldColumnName[$customFileldName].'," ","")),"\\\", "") LIKE "%'.strtolower(str_replace(" ", "", $comp_name)).'%"';
		}
		 
		if($customFileldName == "EmployeeName")
		{
			$query="SELECT emp_list.sno, emp_list.username as customid, emp_list.name as customname FROM users,emp_list,hrcon_compen WHERE emp_list.username=hrcon_compen.username and hrcon_compen.ustatus='active' and hrcon_compen.dept IN ($deptAccesSno) and emp_list.username = users.username AND users.status != 'DA' and users.type in ('sp','PE','subcon','consultant') and emp_list.empterminated != 'Y' and emp_list.lstatus != 'DA' and emp_list.lstatus != 'INACTIVE'  {$str} ";
		}
		else if($customFileldName == "CustomCompanyName")
		{
			$query="SELECT sno as customid,cname as customname FROM staffoppr_cinfo where ( staffoppr_cinfo.owner = '{$username}' OR FIND_IN_SET( '{$username}', staffoppr_cinfo.accessto ) >0 OR staffoppr_cinfo.accessto = 'ALL') and staffoppr_cinfo.deptid IN ($deptAccesSno) and crmcompany='Y' {$str}";
		}
		else if($customFileldName == "CustomContactName")
			$query="SELECT sno as customid,IF(mname='',concat_ws(' ',fname,lname),concat_ws(' ',fname,mname,lname)) as customname FROM staffoppr_contact where (staffoppr_contact.owner='{$username}' OR FIND_IN_SET('{$username}', staffoppr_contact.accessto ) >0 OR staffoppr_contact.accessto = 'ALL')  
			and staffoppr_contact.deptid IN ($deptAccesSno) and crmcontact='Y' {$str}";
		else if($customFileldName == "CustomJobOrder")
			$query="select posid as customid,postitle as customname from posdesc where 1=1 and posdesc.deptid IN ($deptAccesSno) {$str} {$job_str}";
		else if($customFileldName == "CustomCandidate")
			$query="select candidate_list.username as customid,CONCAT_WS(' ',candidate_general.fname,candidate_general.mname,candidate_general.lname) as customname,".tzRetQueryStringDTime('candidate_list.ctime','Date','/')." as ctime,candidate_list.cuser,".tzRetQueryStringDTime('candidate_list.mtime','Date','/') ."as mtime,candidate_list.muser as muser,candidate_general.cg_source as source,candidate_general.cg_sourcetype as sourcetype,
candidate_list.owner as owner,candidate_list.cl_status as status,candidate_list.ctype as ctype,candidate_general.profiletitle as title from candidate_list,candidate_general where candidate_list.username = candidate_general.username and candidate_list.deptid IN ($deptAccesSno) AND candidate_list.status='ACTIVE' AND (candidate_list.owner='$username' OR FIND_IN_SET('$username',candidate_list.accessto)>0 OR candidate_list.accessto='ALL') {$str}";
	}
	else
	{
		$query="SELECT emp_list.sno, emp_list.username as customid, emp_list.name as customname FROM users,emp_list,hrcon_compen,manage WHERE emp_list.username=hrcon_compen.username and hrcon_compen.ustatus='active' and hrcon_compen.dept IN ($deptAccesSno) and emp_list.username = users.username AND users.status != 'DA' and users.type in ('sp','PE','subcon','consultant') and emp_list.empterminated != 'Y' and emp_list.lstatus != 'DA' and emp_list.lstatus != 'INACTIVE' and hrcon_compen.emptype=manage.sno and manage.name='Internal Direct'";
		$condition = 1;
	}
	
	$main	= mysql_query($query,$rptdb);
	$v 	= 1;
	
	$eCampaignArray		= array();
	$submissArray 		= array();
	$interviewsArray 	= array();
	$placedArray 		= array();
	$createdTaskArray 	= array();
	$modifiedTaskArray 	= array();
	$completedTaskArray 	= array();
	$notesArray 		= array();
	$candJobsApplArray 	= array();
	$candsAppliedListArray 	= array(0);
	
	if($customFileldName == "CustomCandidate")
	{
		$eCampaignArray 	= getEcampaignsCount($customFileldName,'',$fromdate,$todate);
		$submissArray 		= getSubmissionsCount($customFileldName,'',$fromdate,$todate);
		$interviewsArray 	= getInterviewsCount($customFileldName,'',$fromdate,$todate);
		$placedArray 		= getCandidatesPlacedCount($customFileldName,'',$fromdate,$todate);
		$createdTaskArray 	= getActivitiesCountByType($customFileldName,"CreatedTasks",'',$fromdate,$todate);
		$modifiedTaskArray 	= getActivitiesCountByType($customFileldName,"ModifiedTasks",'',$fromdate,$todate);
		$completedTaskArray 	= getActivitiesCountByType($customFileldName,"CompletedTasks",'',$fromdate,$todate);
		$notesArray 		= getActivitiesCountByType($customFileldName,"Notes",'',$fromdate,$todate);
		$candJobsApplArray 	= getCandJobsApplied($customFileldName,$fromdate,$todate);
	}
	if($customFileldName == "CustomJobOrder")
	{
		$candsAppliedListArray = getCandJobsApplied($customFileldName,$fromdate,$todate);
	}
	
	$arrManage 	= array();
	$manageSql	= "select sno,name from manage where type in ('candsourcetype','candstatus')";
	$manageResult	= mysql_query($manageSql,$rptdb);

	while($rowManage = mysql_fetch_row($manageResult))
	{
		$arrManage[$rowManage[0]] = $rowManage[1];
	}
	
	$arrUsers 	= array();
	$usersSql	= "select username,name from users";
	$usersResult	= mysql_query($usersSql,$rptdb);

	while($rowUsers = mysql_fetch_row($usersResult))
	{
		$arrUsers[$rowUsers[0]] = $rowUsers[1];
	}

	$condTsCon 	= "1";
	
	while($arr = mysql_fetch_array($main))
	{
		$ii	= 0;
		//start of code for filtering of numeric values...
		if($v == 0)
			$v = 1;

		$values[$customFileldName]	= ($customFileldName == "CustomActivityType") ? htmlspecialchars_decode($actTypeDisplayName,ENT_QUOTES) : trim(htmlspecialchars_decode($arr['customname'],ENT_QUOTES));

		if($tab == "addr")
		{
			for($j=1;$j<$count_fieldnames;$j++)
			{
				if($v == 1)
				{
					$index = array_search($fieldnames[$j],$filternames_array);
					if(in_array($fieldnames[$j],$filternames_array))
					 {
						if($customFileldName == "CustomCandidate")
						{
							if($arr['ctype'] == 'Consultant')
								$arr['ctype'] = 'Candidate';
							else if($arr['ctype'] == 'My Consultant')
								$arr['ctype'] = 'My Candidate';
							else
								$arr['ctype'] = $arr['ctype'];
								
							switch($fieldnames[$j])
							{
								case 'Ecampaigns' :
									$values['Ecampaigns'] =  (array_key_exists($arr['customid'],$eCampaignArray)) ? $eCampaignArray[$arr['customid']] : 0;
									break;
								case 'Submissions' :
									$values['Submissions'] = (array_key_exists($arr['customid'],$submissArray)) ? $submissArray[$arr['customid']] : 0;
									break;
								case 'Interviews' :
									$values['Interviews']  = (array_key_exists($arr['customid'],$interviewsArray)) ? $interviewsArray[$arr['customid']] : 0;
									break;
								case 'CandidatesPlaced' :
									$values['CandidatesPlaced'] = (array_key_exists($arr['customid'],$placedArray)) ? $placedArray[$arr['customid']] : 0;
									break;
								case 'CreatedTasks' :
									$values['CreatedTasks'] =  getActivitiesCountByType($customFileldName,"CreatedTasks",$arr['customid'],$fromdate,$todate);
									break;
								case 'ModifiedTasks' :
									$values['ModifiedTasks'] =  getActivitiesCountByType($customFileldName,"ModifiedTasks",$arr['customid'],$fromdate,$todate);
									break;
								case 'CompletedTasks' :
									$values['CompletedTasks'] =  getActivitiesCountByType($customFileldName,"CompletedTasks",$arr['customid'],$fromdate,$todate);
									break;
								case 'ActTypeNotes' :
									$values['ActTypeNotes'] =  (array_key_exists($arr['customid'],$notesArray)) ? $notesArray[$arr['customid']] : 0;
									break;
								case 'createdDate' :
									$values['createdDate'] =  $arr['ctime'];
									break;	
								case 'createdUser' :
									$values['createdUser'] = $arrUsers[$arr['cuser']];
									break;
								case 'modifiedDate' :
									$values['modifiedDate'] =  $arr['mtime'];
									break;
								case 'modifiedUser' :
									$values['modifiedUser'] =  $arrUsers[$arr['muser']];
									break;
								case 'Source' :
									$values['Source'] =  $arr['source'];
									break;		
								case 'SourceType' :
									$values['SourceType'] =  $arrManage[$arr['sourcetype']];
									break;
								case 'Owner' :
									$values['Owner'] =  $arrUsers[$arr['owner']];
									break;
								case 'Status' :
									$values['Status'] =  $arrManage[$arr['status']];
									break;
								case 'CandidateType' :
									$values['CandidateType'] =  $arr['ctype'];
									break;
								case 'ProfileTitle' :
									$values['ProfileTitle'] =  $arr['title'];
									break;
								case 'JobsApplied' :
									$cur_jo_key =  substr($arr['customid'],4);
									$values['JobsApplied'] = (array_key_exists($cur_jo_key,$candJobsApplArray)) ? $candJobsApplArray[$cur_jo_key] : 0;
									break;
								case 'CandsApplied' :
									$values['CandsApplied'] = 0;
									break;	
                                                                case 'AssignmentsCreated' :
									$values['AssignmentsCreated'] =  getassignmentscreatedCount($customFileldName,$arr['customid'],$fromdate,$todate,$deptAccesSno);
									break;
								default :
									for($loopnote=0;$loopnote<$arrNoteReferencesCount;$loopnote++)
									{
										if($arrNoteReferences[$loopnote][0] == $fieldnames[$j])
										{
											$dynamicFieldname = $arrNoteReferences[$loopnote][0];
											$dynamicNoteTypeName = $arrNoteReferences[$loopnote][1];
											$values[$dynamicFieldname] = getNotesCountByType($customFileldName,$dynamicNoteTypeName,$arr['customid'],$fromdate,$todate);
										}
									}

									for($loopRole=0;$loopRole<$arrRoleReferencesCount;$loopRole++)
									{
										if($arrRoleReferences[$loopRole][0] == $fieldnames[$j])
										{
											$dynamicFieldname = $arrRoleReferences[$loopRole][0];
											$dynamicRoleTypeName = $arrRoleReferences[$loopRole][1];
											$values[$dynamicFieldname] = getRoleCountByType($customFileldName,$dynamicRoleTypeName,$arr['customid'],$fromdate,$todate);
										}
									}
							}
							
						}
						else
						{
						
						    
							switch($fieldnames[$j])
							{					
								case 'Ecampaigns' :
									$values['Ecampaigns'] =  getEcampaignsCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'Submissions' :
									$values['Submissions'] = getSubmissionsCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'JobPostings' :
									$values['JobPostings'] = getJobPostingsCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'CandidatesEcampaigned' :
									$values['CandidatesEcampaigned'] =
										getEcampaignsCandiddateCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'JobOrdersEcampaigned' :
									$values['JobOrdersEcampaigned'] = 
										getJobOrdersEcampaignedCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'CandidatesSubmitted' :
									$values['CandidatesSubmitted']  =
										getSubmissionsCandiddateCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'Interviews' :
									$values['Interviews']  = getInterviewsCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'CandidatesPlaced' :
									$values['CandidatesPlaced']  = 
										getCandidatesPlacedCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'JobOrders' :
									$values['JobOrders']  = getJobOrdersCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'JobOrdersModified' :
									$values['JobOrdersModified']  = 
											getJobOrdersModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'Companies' :
									$values['Companies']  = getCompaniesCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'CompaniesModified' :
									$values['CompaniesModified']  = 
										getCompaniesModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'Contacts' :
									$values['Contacts']  = getContactsCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'ContactsModified' :
									$values['ContactsModified']  = 
											getContactsModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'Candidates' :
									$values['Candidates']  = getCandidatesCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'CandidatesModified' :
									$values['CandidatesModified']  = 
										getCandidatesModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'Revenue' :
									$values['Revenue'] =  getRevenue($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'Appointments' :
									$values['Appointments'] =  
									getActivitiesCountByType($customFileldName,"Appointment",$arr['customid'],$fromdate,$todate);
									break;
								case 'Events' :
									$values['Events'] =  getActivitiesCountByType($customFileldName,"Event",$arr['customid'],$fromdate,$todate);
									break;
								case 'SentMail' :
									$values['SentMail'] =  getActivitiesCountByType($customFileldName,"Email",$arr['customid'],$fromdate,$todate);
									break;
								case 'ReceivedMail' :
									$values['ReceivedMail'] =  getActivitiesCountByType($customFileldName,"REmail",$arr['customid'],$fromdate,$todate);
									break;
								case 'RespondedDetails' :
									$values['RespondedDetails'] =  
									getActivitiesCountByType($customFileldName,"Responded Details",$arr['customid'],$fromdate,$todate);
									break;
								case 'CreatedTasks' :
									$values['CreatedTasks'] =  getActivitiesCountByType($customFileldName,"CreatedTasks",$arr['customid'],$fromdate,$todate);
									break;
								case 'ModifiedTasks' :
									$values['ModifiedTasks'] =  getActivitiesCountByType($customFileldName,"ModifiedTasks",$arr['customid'],$fromdate,$todate);
									break;
								case 'CompletedTasks' :
									$values['CompletedTasks'] =  getActivitiesCountByType($customFileldName,"CompletedTasks",$arr['customid'],$fromdate,$todate);
									break;
								case 'ActTypeNotes' :
									$values['ActTypeNotes'] =  getActivitiesCountByType($customFileldName,"Notes",$arr['customid'],$fromdate,$todate);
									break;
								case 'CompenCode' :
									$values['CompenCode'] =  getCompenCode($customFileldName,$arr['customid'],$fromdate,$todate);
									break;
								case 'CandsApplied' :
									$values['CandsApplied'] = (array_key_exists($arr['customid'],$candsAppliedListArray)) ? $candsAppliedListArray[$arr['customid']] : 0;
									break;

                                                                case 'AssignmentsCreated' :
									$values['AssignmentsCreated'] =  getassignmentscreatedCount($customFileldName,$arr['customid'],$fromdate,$todate,$deptAccesSno);
									break;
								default :
								
									for($loopnote=0;$loopnote<$arrNoteReferencesCount;$loopnote++)
									{
										if($arrNoteReferences[$loopnote][0] == $fieldnames[$j])
										{
											$dynamicFieldname = $arrNoteReferences[$loopnote][0];
											$dynamicNoteTypeName = $arrNoteReferences[$loopnote][1];
											$values[$dynamicFieldname] = getNotesCountByType($customFileldName,$dynamicNoteTypeName,$arr['customid'],$fromdate,$todate);
										}
									}

									     
									for($loopRole=0;$loopRole<$arrRoleReferencesCount;$loopRole++)
									{
										if($arrRoleReferences[$loopRole][0] == $fieldnames[$j])
										{
											$dynamicFieldname = $arrRoleReferences[$loopRole][0];
										    $dynamicRoleTypeName = $arrRoleReferences[$loopRole][1];
											$values[$dynamicFieldname] = getRoleCountByType($customFileldName,$dynamicRoleTypeName,$arr['customid'],$fromdate,$todate);
										}
									}
							}
							
						}
						
						if($fieldnames[$j] != 'createdDate' && $fieldnames[$j] != 'modifiedDate')
						{
							$ranges 	= explode("*",$filtervalues_array[$index]);
							$maxvalue 	= $ranges[0]; 
							$minvalue 	= $ranges[1];
						   
					   		if($minvalue != "" && $maxvalue != "")
						 		$v =  ( ($values[$fieldnames[$j]] >= $minvalue) && ($values[$fieldnames[$j]] <= $maxvalue) ) ? "1" : "0";
							elseif($minvalue != "" && $maxvalue == "")
						 		$v = ( ($values[$fieldnames[$j]] >= $minvalue) ) ? "1" : "0";
							elseif($maxvalue != "" && $minvalue == "")
						 		$v =  (  ($values[$fieldnames[$j]] <= $maxvalue) ) ? "1" : "0";
						} 
					}
					else
						$v = 1;
				}
				else
					break;
			} // close for($i=1;$i<count($fieldnames);$i++)
		} //close if($tab == addr)
		else
		{
			$values['Ecampaigns'] 		=  getEcampaignsCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['Submissions'] 		=  getSubmissionsCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['JobPostings'] 		=  getJobPostingsCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['CandidatesEcampaigned'] = getEcampaignsCandiddateCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['JobOrdersEcampaigned'] = getJobOrdersEcampaignedCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['CandidatesSubmitted']  = getSubmissionsCandiddateCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['Interviews']  		= getInterviewsCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['CandidatesPlaced']  	= getCandidatesPlacedCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['JobOrders']  		= getJobOrdersCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['JobOrdersModified'] 	= getJobOrdersModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['Companies']  		= getCompaniesCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['CompaniesModified']  	= getCompaniesModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['Contacts']  		= getContactsCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['ContactsModified']  	= getContactsModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['Candidates']  		= getCandidatesCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['CandidatesModified']	= getCandidatesModifiedCount($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['Revenue'] 		= getRevenue($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['Appointments'] 	= getActivitiesCountByType($customFileldName,"Appointment",$arr['customid'],$fromdate,$todate);
			$values['Events'] 		= getActivitiesCountByType($customFileldName,"Event",$arr['customid'],$fromdate,$todate);
			$values['SentMail'] 		= getActivitiesCountByType($customFileldName,"Email",$arr['customid'],$fromdate,$todate);
			$values['ReceivedMail'] 	= getActivitiesCountByType($customFileldName,"REmail",$arr['customid'],$fromdate,$todate);
			$values['RespondedDetails'] 	= getActivitiesCountByType($customFileldName,"Responded Details",$arr['customid'],$fromdate,$todate);
			
			$values['CreatedTasks'] 	= getActivitiesCountByType($customFileldName,"CreatedTasks",$arr['customid'],$fromdate,$todate);
			$values['ModifiedTasks'] 	= getActivitiesCountByType($customFileldName,"ModifiedTasks",$arr['customid'],$fromdate,$todate);
			$values['CompletedTasks'] 	= getActivitiesCountByType($customFileldName,"CompletedTasks",$arr['customid'],$fromdate,$todate);
			
			$values['ActTypeNotes'] 	= getActivitiesCountByType($customFileldName,"Notes",$arr['customid'],$fromdate,$todate);
			$values['CompenCode'] 		= getCompenCode($customFileldName,$arr['customid'],$fromdate,$todate);
			$values['CandsApplied'] 	= 0;
                        // query to get data count for 'Assignments Created' column
                        $values['AssignmentsCreated'] =  getassignmentscreatedCount($customFileldName,$arr['customid'],$fromdate,$todate,$deptAccesSno);
			for($loopnote=0;$loopnote<$arrNoteReferencesCount;$loopnote++)
			{
				$dynamicFieldname 	= $arrNoteReferences[$loopnote][0];
				$dynamicNoteTypeName 	= $arrNoteReferences[$loopnote][1];
				$values[$dynamicFieldname] = getNotesCountByType($customFileldName,$dynamicNoteTypeName,$arr['customid'],$fromdate,$todate);
			}

			for($loopRole=0;$loopRole<$arrRoleReferencesCount;$loopRole++)
			{
									
				$dynamicFieldname 	= $arrRoleReferences[$loopRole][0];
				$dynamicRoleTypeName 	= $arrRoleReferences[$loopRole][1];
				$values[$dynamicFieldname] = getRoleCountByType($customFileldName,$dynamicRoleTypeName,$arr['customid'],$fromdate,$todate);
			}
		}
		
		$values['CustomCompanyName'] = str_replace("\\","",$values['CustomCompanyName']); 
		$values['CustomContactName'] = str_replace("\\","",$values['CustomContactName']); 
		$values['CustomJobOrder'] = str_replace("\\","",$values['CustomJobOrder']); 
		$values['CustomCandidate'] = str_replace("\\","",$values['CustomCandidate']); 
		if($v)
			$condition = 1;
		else
			$condition = 0;
		//end of code for filtering of numeric values...

		if($condition)
		{	
			for($q=0;$q<$count_sortarr;$q++)
			{
				for($f=0 ; $f<$count_fieldnames ; $f++)
				{
				    
					if($sortarr[$q] == $fieldnames[$f])
					{
						$variable = $$variablenames[$f];
						if($variable[0]!="")
							$data[$i][$ii] = $values[$fieldnames[$f]];
						$sslength_array[$fieldnames[$f]] = trim((strlen($values[$fieldnames[$f]]) <= 
						strlen($variable[2])) ? strlen($values[$fieldnames[$f]]) : (strlen($variable[2])+3));
						$ii++;
					}
				}
			}	

			global $rptdb;
			global $username;
			$sql = "SELECT admin from sysuser where username=$username";
			$rs = mysql_query($sql,$rptdb);
			$row = mysql_fetch_assoc($rs);
			$admin_pref = $row['admin'];
			$chkadmin = strpos($admin_pref, '+14+');//$admin_pref!='NO'  $chkadmin !== false
			$module = 'CRM';

			//Checking whether logged in user have Admin & DataManagement access. Passing the respective parameter to open link.
			if($admin_pref!='NO' && $chkadmin !== false)
			{
				if($customFileldName == "CustomCompanyName")
				{
					$module = 'Admin_Companies';
				}
				else if($customFileldName == "CustomContactName")
				{
					$module = 'Admin_Contacts';
				}
				else if($customFileldName == "CustomJobOrder")
				{
					$module = 'Admin_JobOrders';
				}
				else if($customFileldName == "CustomCandidate")
				{
					$module = 'Admin_Candidates';
				}	
			}
          
			if($count_sortarr)
				$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
		
			if($customFileldName != 'CustomJoborderStatus' && $customFileldName != 'CustomJobType' && $customFileldName != 'CustomJobCategory')
			{
				$data[$i][$ii] = "javascript:showUser('$customFileldName','$arr[0]','".$module."')";
			}
			else
				$data[$i][$ii] = "";
			$ii++;
		
			$pushdata = 0;
			
                        //added new assignments created condition
			if(($varEmployeeName[0]!="") || ($varEcampaigns[0]!="") || ($varSubmissions[0]!="") || ($varJobPostings[0]!="") || ($varCandidatesEcampaigned[0]!="") || ($varJobOrdersEcampaigned[0] != "") || ($varCandidatesSubmitted[0] != "") || ($varInterviews[0] != "") || ($varCandidatesPlaced[0]!="") || ($varJobOrders[0] != "") || ($varJobOrdersModified[0] != "") || ($varCompanies[0]!="") || ($varCompaniesModified[0] != "") || ($varContacts[0] != "") || ($varContactsModified[0]!="") || ($varCandidates[0] != "") || ($varCandidatesModified[0] != "") || ($varRevenue[0]!="") || ($varAppointment[0] != "") || ($varEvent[0] != "")  || ($varSentMail[0] != "") || ($varReceivedMail[0] != "") || ($varRespondedDetails[0]!="") || ($varCreatedTasks[0] != "") || ($varActTypeNotes[0] != "") || ($varcreatedDate[0]!="") || ($varcreatedUser[0] != "") || ($varmodifiedDate[0] != "") || ($varmodifiedUser[0] != "") || ($varSource[0] != "") || ($varSourceType[0] != "") || ($varOwner[0] != "") || ($varStatus[0] != "") || ($varCandidateType[0] != "") || ($varProfileTitle[0] != "") || ($varJobsApplied[0] != "") || ($varCandsApplied[0] != "") || ($varAssignmentsCreated[0]!="") || ($varModifiedTasks[0] != "") || ($varCompletedTasks[0] != ""))
			{
				$data[$i][$ii]	= $slength;
				$ii++;
			}
			else if($arrNoteReferencesCount)
			{
				for($looplength = 0;$looplength < $arrNoteReferencesCount;$looplength++)
				{
					if($arrNoteReferences[$looplength][0] != "")
					       $pushdata = 1;
				}//for($looplength = 0;
				  
				if($pushdata == 1)
				{
					$data[$i][$ii]=$slength;
					$ii++;
				}//if($pushdata == 1)
			}//else if(count($arrNoteReferences))
			else if($arrRoleReferencesCount)
			{
				$pushdata = 1;
				for($looplength = 0;$looplength < $arrRoleReferencesCount;$looplength++)
				{
					if($arrRoleReferences[$looplength][0] != "")
						$pushdata = 2;
				}//for($looplength = 0;
				  
				if($pushdata == 2)
				{
					$data[$i][$ii]=$slength;
					$ii++;
				}//if($pushdata == 1)
			}//else if(count($arrRoleReferences))			
			$i++;
		}//close of if($condition)

	} //close for while..............
  
	 
		
	$data	= cleanArray($data);
	
	if($data == "")
	{
		$data		= array();
		$data[0][0]	= "";
		$headval	= array();
		$headval[0][0]	= "";
	}
	$rep_length	= $i-1;	
	
	require("rlibdata.php");
	if($format == 'csv')
	{
		$fileName = $filename.".".$format;
		$mime = 'application/'.$format;		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Type: $mime; name=$fileName");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$fileName");
		header("Content-Transfer-Encoding: binary");
	}
	

	if($defaction == "print")
		echo "<script>window.print(); window.setInterval('window.close();', 10000)</script>";
?>
