<?php
require_once("global.inc");
require("reportdatabase.inc");
require("dispfunc.php");
/*
	Modifed Date: Sep 11th,2015
	Modified By: Srikanth Algani
	Purpose: Adding new column 'Assignments Created'.
	TS Task Id:
 
	Modifed Date: Oct 20,2011
	Modified By: Jyothi Chundi
	Purpose: Adding mutiple drop dowm for employee Name.
	TS Task Id:
	
	Modifed Date: June 22,2009
	Modified By: Fathima
	Purpose: Adding columns created task, modified task and completed tasks.
	TS Task Id:4437
	
	Modifed Date:8th April 2009
	Modified By: prasadd
	Purpose: submissionsquery for contact, company changed...
	TS Task Id:4229-support
*/	
function pushArrayElements($argMainArray,$argAppendArray)
{
	for($i=0;$i<count($argAppendArray);$i++)
	{
		array_push($argMainArray,$argAppendArray[$i]);
	}
	return $argMainArray;
}
function selEmpType($actval,$selval,$val)
{
	if(($val=='Internal Direct') || ($actval==$selval)) 
    	return "selected";
	else
		return "";
}
function formateSlashes($argValue)
{
	if(!strpos($argValue,'\\\\') === false)
	{
		$argValue = stripslashes($argValue);
		$argValue = str_replace('\\','\\\\\\\\\\\\\\',$argValue);
	}
	return $argValue;
}

//This is rthe function For User Preferences..
function chkUserPreference()
{
	global $username;
	global $rptdb;
	global $analyticspref;
	$sql = "select users.type from users where users.username=$username";
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_assoc($rs);
	$user_type = $row['type'];
	//if(($user_type == 'sp') || (strpos($analyticspref, "+4")))
	if(($user_type == 'sp') || (chkUserPref($analyticspref, "4")))
		return true;
	else
		return false;
}

//Function For Getting The Country Name
function getCountryName($argCountryId)
{
	global $rptdb;
	$sql = "select country from countries where sno='{$argCountryId}' ";
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	return $row['country'];
}

//Function For Getting The Comapny Name..
function companyName($argCompanyId)
{
	global $rptdb;
	$sql = "select cname from staffoppr_cinfo where sno='{$argCompanyId}' ";
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	return $row['cname'];
}

//Function For Getting te Contact Name..
function contactName($argContactId)
{
	global $rptdb;
	$sql = "select fname from staffoppr_contact where sno='{$argContactId}' ";
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	return $row['fname'];
}

//Function For Getting te Employee Name(s).
function getEmployeeList($argSele,$deptAccesSno)
{
	global $rptdb;
	$option = " ";
	$selected = " ";
	$sql = "SELECT DISTINCT(emp_list.name) as colname FROM emp_list LEFT JOIN users ON emp_list.username = users.username LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username  WHERE hrcon_compen.ustatus='active' AND hrcon_compen.dept IN ($deptAccesSno) AND emp_list.lstatus != 'DA' AND emp_list.lstatus != 'INACTIVE' AND users.type IN ('sp', 'PE', 'consultant') AND users.status != 'DA' AND users.usertype != '' ORDER BY colname ";
	$rs =  mysql_query($sql,$rptdb);
	
	$argSelArr 	=  	explode('***',$argSele);
	
	$argSel 	= 	$argSelArr[0];

	if(!$argSel || ($argSel == 'ALL') || (strpos($argSel,'!#!')>0) && (in_array('ALL',explode('!#!',$argSel))) ){

		$option .= "<option value='ALL' selected>ALL</option>";
	}else{
		$option .= "<option value='ALL'>ALL</option>" ;
	}

	while($row = mysql_fetch_array($rs))
	{
		$getEmpName = html_tls_entities($row['colname'],ENT_QUOTES);

		$enc_temp_arr = encode_entity_codes($argSel);
		if(!$argSel || ($argSel == $getEmpName) || ((strpos($argSel,'!#!')>0) && in_array($getEmpName,$enc_temp_arr)) )
		{
			
			$selected = "selected = selected";
		}
		else
		{
			$selected = " ";
		}

		if(trim($row['colname'])!='')
			$option .= "<option value='".$getEmpName."' ".$selected.">".$getEmpName."</option>" ;
	}
	return $option;

}
//used to convert single and double quotes to the filter array
function encode_entity_codes($argSel)
{
	$temp_arr = explode('!#!',$argSel);
	$enc_temp_arr=[];
	
	//building the temp arr by converting the string html_entities
	if((strpos($argSel,'!#!')>0))
	{
		foreach($temp_arr as $key=>$val)
		{
			$enc_temp_arr[$key] = html_tls_entities($val,ENT_QUOTES);
		}
	}
	return $enc_temp_arr;
}
//This is the function for Display Field Labels..
function getDisplayName($argFieldName)
{
	global $customFileldName;
	$fieldnames = array("EmployeeName" => "Employee Name" ,"Ecampaigns" => "eCampaigns" ,"Submissions" => "Submissions" ,
		"JobPostings" => "Job Postings" ,"CandidatesEcampaigned" => "Candidates eCampaigned" ,"JobOrdersEcampaigned" => 
		"JobOrders eCampaigned" ,"CandidatesSubmitted" => "Candidates Submitted" ,"Interviews" => "Interviews" ,"CandidatesPlaced" 
		=> "Candidates Placed" ,"JobOrders" => "JobOrders Created" ,"JobOrdersModified" => "JobOrders Modified" ,"Companies" => 
		"Companies Created" , "CompaniesModified" => "Companies Modified" ,"Contacts" => "Contacts Created" ,"ContactsModified" =>
		 "Contacts Modified" ,"Candidates" => "Candidates" ,"CandidatesModified" => "Candidates Modified" ,"Revenue" => 
		 "Placement Fee" ,"Appointments" => "Appointments" ,"Events" => "Events" ,"SentMail" => "Sent E-Mails" ,"ReceivedMail" => 
		 "Received E-Mails" ,"RespondedDetails" => "Responded Details" ,"CreatedTasks" => "Created Tasks","ModifiedTasks" => "Modified Tasks","CompletedTasks" => "Completed Tasks" ,"ActTypeNotes" => "Notes" ,"CompenCode" => "CompenCode" ,"CustomCompanyName" => "Company Name" , "CustomContactName" => "Contact Name" ,
		"CustomJoborderStatus" => "Job Order Status" , "CustomJobType" => "Job Order Type" , "CustomJobOwner" => "Job Owner" ,
		"CustomJobCategory" => "Job Order Category" , "CustomNoteType" => "Note Type" , "CustomJobOrder" => "Job Order Title",
		"CustomCandidate" => "Candidate Name" , "createdDate" => "Created Date" , "createdUser" => "Created User" , 
		"modifiedDate" => "Modified Date" , "modifiedUser" => "Modified User" , "Source" => "Source" , "SourceType" => 
		"Source Type" , "Owner" => "Owner" , "Status" => "Status" ,"CandidateType" => "Candidate Type" , "ProfileTitle" => 
		"Profile Title","JobsApplied" => "Jobs Applied","CandsApplied" => "Candidates Applied","AssignmentsCreated"=>"Assignments Created");


	//For generating the note items in the fieldnames array
	$arrNoteTypes = getAllNoteTypes();
	if(count($arrNoteTypes))
	{
		$loopnote = 0;
		foreach($arrNoteTypes as $key=>$value)
		{
			$var = "DynamicNoteType_$key";
			$fieldnames[$var] = $value;
		}
	}
	
	$arrSubRole = getAllRolesTypes();
	$arrSubRoleID = getAllRolesTypesID();
	
	if(count($arrSubRole))
	{
		for($j=0;$j<count($arrSubRole);$j++)
		{
		   
			$var = "DynamicSubRole".$j."_".$arrSubRoleID[$j];
			$fieldnames[$var] = $arrSubRole[$j];
		}
	}
	if($customFileldName == 'CustomCandidate' && $argFieldName == 'Ecampaigns')
		return "Number of eCampaigns";
	else if($customFileldName == 'CustomCandidate' && $argFieldName == 'Submissions')
		return "Number of Submissions";
	else if($customFileldName == 'CustomCandidate' && $argFieldName == 'CandidatesPlaced')
		return "Number of Placements";
	else if($customFileldName == 'CustomCandidate' && $argFieldName == 'Interviews')
		return "Number of Interviews";	
	else
		return $fieldnames[$argFieldName];
}
function getOwnersList($argSelval = "")
{
	global $rptdb;
	$option = " ";
	$sql="SELECT '',users.username,users.name from users where users.status != 'DA'";
	$rs =  mysql_query($sql,$rptdb);
	while($row = mysql_fetch_array($rs))
	{
		if($argSelval && ($argSelval == $row['username']) )
			$selected = "selected";
		else
			$selected = " ";
				
		$option .= "<option value='".dispTextdb($row[username])."' {$selected}>".stripslashes($row['name'])."</option>" ;
	}
	return $option;
}
//This is the Function For Display Filters For Activity Report..
function getFilters($argFilterNames,$argFilterValues,$deptAccesSno)
{
	global $rptdb;
	$entirefields_array = array("EmployeeName","CustomCompanyName","CustomContactName","CustomJobOrder","CustomCandidate",
	   						"CustomJoborderStatus","CustomJobType","CustomJobOwner","CustomJobCategory","CustomNoteType"
							,"Ecampaigns","Submissions","JobPostings","CandidatesEcampaigned","JobOrdersEcampaigned"
							,"CandidatesSubmitted","Interviews","CandidatesPlaced","JobOrders","JobOrdersModified","Companies"
							,"CompaniesModified","Contacts","ContactsModified","Candidates","CandidatesModified","Revenue",
							"Appointments","Events","SentMail","ReceivedMail","RespondedDetails","CreatedTasks","ModifiedTasks","CompletedTasks","ActTypeNotes"
							,"createdDate","createdUser","modifiedDate","modifiedUser","Source","SourceType","Owner","Status"
							,"CandidateType","ProfileTitle","JobsApplied","CandsApplied","AssignmentsCreated");
							

	
	$arrNoteTypes 		= getAllNoteTypes();
	$noteTypesCount  	= count($arrNoteTypes);
	if($noteTypesCount)
	{
		foreach($arrNoteTypes as $key=>$value)
		{
			array_push($entirefields_array, "DynamicNoteType_{$key}");
		}
	}
		
	$filternames_array = explode("^",$argFilterNames);
	$filtervalues_array = explode("^",dispTextdb($argFilterValues));

	$SubRolesCount 	= getRoleSubCount();
	$arrSubRoleID 	= getAllRolesTypesID();
	if($SubRolesCount)
	{
		for($i=0;$i<$SubRolesCount;$i++)
			array_push($entirefields_array,"DynamicSubRole{$i}"."_".$arrSubRoleID[$i]);
	}

	   
	$correspondingvalues_array = array_combine($filternames_array , $filtervalues_array);
	$scriptStr = ' <script type="text/javascript">

							$(document).ready(function() {';

	   
	if(in_array("CustomJobOrder",$filternames_array))
		$job_filter = "";
	else
		$job_filter = "style='display:none'";
			
	for($i=0;$i<count($entirefields_array);$i++)
	{
		$row_id = "filter_".$entirefields_array[$i];
		if(in_array($entirefields_array[$i],$filternames_array))
			$style_filter = '';
		else
			$style_filter = "style='display:none'";
			
		if(in_array("EmployeeName",$filternames_array))
		{
			$emptype_filter = "";
			$empval=explode("***",$correspondingvalues_array[$entirefields_array[$i]]);
			$emp_name=$empval[0];
			$emp_jobtype=$empval[1];
		}
		else
		{
			$emptype_filter = "style='display:none'";
		} 
		if($entirefields_array[$i] == "EmployeeName" || $entirefields_array[$i] == "CustomCompanyName" ||  $entirefields_array[$i] == "CustomContactName" || $entirefields_array[$i] == "CustomCandidate")
		{
			if($entirefields_array[$i] == "EmployeeName")
				$selectValue = $emp_name;
			else 
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]]; 
			if($entirefields_array[$i] == "EmployeeName")
			{
				// added mutiple drop down -- jyothi
				//echo $selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter = "<tr id='{$row_id}' {$style_filter}>";
				$trFilter .= "<td width='5%'>&nbsp;</td>";
				$trFilter .= "<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				$trFilter .= "<td >";
				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 160 });';
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$options = getEmployeeList($selectValue,$deptAccesSno);
				$trFilter.="<select class=drpdwne  multiple='multiple' style='width:150px; height:40px'  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>\n";
				$trFilter .=  $options;
				$trFilter .=  "</select>";
				$trFilter .= "</td>";
				$trFilter .= "</tr>";	
			}
			else
			{
				
				$trFilter = "<tr id='{$row_id}' {$style_filter}>";
				$trFilter .= "<td width='5%'>&nbsp;</td>";
				$trFilter .= "<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				$trFilter .= "<td >";
				$selectValue = html_tls_entities($selectValue,ENT_QUOTES);
				
				$trFilter .=  "<input name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
				value='".$selectValue."' type='text'>	
				<a href=javascript:activitiesWindow('$entirefields_array[$i]')>
				<i class='fa fa-search'></i></a>";
				$trFilter .= "</td>";
				$trFilter .= "</tr>";			
			}	
				
			if($entirefields_array[$i] == "EmployeeName")
			{
			   	$trFilter .= "<tr id='emptypeid' {$emptype_filter}>";
				$trFilter .= "<td width='5%'>&nbsp;</td>";
				$trFilter .= "<td width='35%'><font class='afontstyle'>Employee Type</font></td>";
				$trFilter .= "<td >";
				$trFilter.="<select class=drpdwne  name='EmployeeType' id='EmployeeType'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$sqljotype="select sno,name from manage where type='jotype' and name in('Temp/Contract','Internal Temp/Contract','Internal Direct')";
				$resjotype=mysql_query($sqljotype,$rptdb);
			
				while($emptype=mysql_fetch_row($resjotype))
				{
					if($emptype[1] == "Internal Direct" && $emp_jobtype=='')
				  		$sel_jobtype=$emptype[0];
					else
				  		$sel_jobtype=$emp_jobtype;
				 
					$trFilter .= "<option value='$emptype[0]' ".sele($emptype[0],$sel_jobtype).">$emptype[1]</option>";
				}
				$trFilter .=  "</select>"; 
				$trFilter .= "</td>";
				$trFilter .= "</tr>";
			}		
			}
			else if($entirefields_array[$i] == "createdUser" || $entirefields_array[$i] == "modifiedUser" ||  $entirefields_array[$i] == "Owner" || $entirefields_array[$i] == "Source" || $entirefields_array[$i] == "ProfileTitle")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$selectValue = html_tls_entities($selectValue,ENT_QUOTES);
				
				$trFilter = "<tr id='{$row_id}' {$style_filter}>";
				$trFilter .= "<td width='5%'>&nbsp;</td>";
				$trFilter .= "<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				$trFilter .= "<td >";
			  	$trFilter .=  "<input name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
					value='".$selectValue."' type='text'>	
					<a href=javascript:activitiesWindow('$entirefields_array[$i]')>
					<i class='fa fa-search'></i></a>";
				$trFilter .= "</td>";
			    $trFilter .= "</tr>";
			}
			else if($entirefields_array[$i] == "createdDate" || $entirefields_array[$i] == "modifiedDate")
			{
				$minname = "min_".$entirefields_array[$i];
				$maxname = "max_".$entirefields_array[$i];
				$ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
				$minvalue = $ranges[0];
				$maxvalue = $ranges[1];
			  
		  		$trFilter  = "<tr id='{$row_id}' {$style_filter}>";
				$trFilter .= "<td width='5%'>&nbsp;</td>";
				$trFilter .= "<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				$trFilter .= "<td >";
                                $trFilter .= '<font class="afontstyle">
			  		From : <input name="'.$minname.'" value="'.$minvalue.'" size="8" type="text" id ="'.$minname.'" readonly>	
                       <script language="JavaScript">new tcal ({"formname":window.form,"controlname":"'.$minname.'"});</script>
                       <a href=javascript:resetDate("'.$minname.'")>
                       <i class="fa fa-reply"></i></a>
                                        To : <input name="'.$maxname.'" value="'.$maxvalue.'" size="8" type="text" id ="'.$maxname.'" readonly>
                       <script language="JavaScript">new tcal ({"formname":window.form,"controlname":"'.$maxname.'"});</script>
                       <a href=javascript:resetDate("'.$maxname.'")><i class="fa fa-reply"></i></a> 
					';
				
                                
				$trFilter .= "</td>";
			    $trFilter .= "</tr>";	
					// resetStartDate() => HRAssign/scripts/link.js
			}
			else if($entirefields_array[$i] == "SourceType" || $entirefields_array[$i] == "Status")
			{
				if($entirefields_array[$i] == "SourceType")
				  $managetype = "candsourcetype";
				else if($entirefields_array[$i] == "Status")
				  $managetype = "candstatus";
					   
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]]; 
				$trFilter = "<tr id='{$row_id}' {$style_filter}>";
				$trFilter .= "<td width='5%'>&nbsp;</td>";
				$trFilter .="<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				$trFilter .= "<td>";
				$trFilter .=  "<select name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
									class='drpdwne'><option value=''>ALL</option>";
				$trFilter .=  getManageListOptions($managetype,$selectValue);	
				$trFilter .=  "</select>";
				$trFilter .= "</td>";
				$trFilter .= "</tr>";			
			}
			else if($entirefields_array[$i] == "CandidateType")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]]; 
				$trFilter ="<tr id='{$row_id}' {$style_filter}>";
				$trFilter .="<td width='5%'>&nbsp;</td>";
				$trFilter .="<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				$trFilter .= "<td>";
				$trFilter .= "<select name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
								class='drpdwne'><option value=''>ALL</option>";
				$trFilter .= "<option value='Consultant'".compose_sel("Consultant",$selectValue).">Candidate</option>";
				$trFilter .= "<option value='My Consultant'".compose_sel("My Consultant",$selectValue).">My Candidate</option>";
				$trFilter .= "<option value='Employee'".compose_sel("Employee",$selectValue).">Employee</option>";
				$trFilter .= "</select>";
				$trFilter .= "</td>";
				$trFilter .= "</tr>";			
			}
			else if($entirefields_array[$i] == "CustomJobOrder")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$selectValue = html_tls_entities($selectValue,ENT_QUOTES);
				$trFilter = "<tr id='{$row_id}' {$job_filter}>";
				$trFilter .= "<td width='5%'>&nbsp;</td>";
				$trFilter .= "<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				$trFilter .= "<td >";
			  	$trFilter .=  "<input name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
					value='".$selectValue."' type='text'>	
					<a href=javascript:activitiesWindow('$entirefields_array[$i]')>
					<i class='fa fa-search'></i></a>";
				$trFilter .= "</td>";
			    $trFilter .= "</tr>";
			}	
			else if($entirefields_array[$i] == "CustomJoborderStatus" ||  $entirefields_array[$i] == "CustomJobType" ||  $entirefields_array[$i] == "CustomJobCategory")
			  {
					if($entirefields_array[$i] == "CustomJoborderStatus")
					  $managetype = "jostatus";
					else if($entirefields_array[$i] == "CustomJobType")
					  $managetype = "jotype";
					else if($entirefields_array[$i] == "CustomNoteType")
					  $managetype = "notes";
					else if($entirefields_array[$i] == "CustomJobCategory")
					  $managetype = "jocategory";
					   
					$selectValue = $correspondingvalues_array[$entirefields_array[$i]]; 
					$trFilter = "<tr id='{$row_id}' {$job_filter}>";
					$trFilter .= "<td width='5%'>&nbsp;</td>";
					$trFilter .="<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
					$trFilter .= "<td>";
					$trFilter .=  "<select name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
									class='drpdwne'><option value=''>ALL</option>";
					$trFilter .=  getManageListOptions($managetype,$selectValue);	
					$trFilter .=  "</select>";
					$trFilter .= "</td>";
					$trFilter .= "</tr>";			
			  }
			  else if($entirefields_array[$i] == "CustomNoteType")
			  {
					$managetype = "notes";
					   
					$selectValue = $correspondingvalues_array[$entirefields_array[$i]]; 
					$trFilter = "<tr id='{$row_id}' {$style_filter}>";
					$trFilter .= "<td width='5%'>&nbsp;</td>";
					$trFilter .="<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
					$trFilter .= "<td>";
					$trFilter .=  "<select name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
									class='drpdwne'><option value=''>ALL</option>";
					$trFilter .=  getManageListOptions($managetype,$selectValue);	
					$trFilter .=  "</select>";
					$trFilter .= "</td>";
					$trFilter .= "</tr>";			
			  }
			  else if($entirefields_array[$i] == 'Ecampaigns' || $entirefields_array[$i] == 'Submissions' || $entirefields_array[$i] == 'CandidatesPlaced' || $entirefields_array[$i] == 'Interviews')
			  {
			  	$trFilter = "<tr id='{$row_id}' {$style_filter}>";
			  	$trFilter .= "<td width='5%'>&nbsp;</td>";
			  	$trFilter .= "<td width='35%'><font class='afontstyle' id='text_{$entirefields_array[$i]}'>".getDisplayName($entirefields_array[$i])."</font></td>";
			  	$trFilter .= "<td>";
			  	$maxname = "max_".$entirefields_array[$i];
			  	$minname = "min_".$entirefields_array[$i];
			 	$ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
			  	$maxvalue = $ranges[0];
			   	$minvalue = $ranges[1];
			  	$trFilter .= "<font class='afontstyle'>Min : <input name='".$minname."' id='".$minname."' value='".$minvalue."' size='3' 
			  	type='text' maxlength='4'>&nbsp;&nbsp;&nbsp;&nbsp;Max : <input name='".$maxname."' id='".$maxname."' value='".$maxvalue."' 
			  	size='3' type='text' maxlength='4'></font>";
			  	$trFilter .= "</td>";
			  	$trFilter .= "</tr>";
		   	  }
			  else
			  {
			  	$trFilter = "<tr id='{$row_id}' {$style_filter}>";
			  	$trFilter .= "<td width='5%'>&nbsp;</td>";
			  	$trFilter .= "<td width='35%'><font class='afontstyle' >".getDisplayName($entirefields_array[$i])."</font></td>";
			  	$trFilter .= "<td>";
			  	$maxname = "max_".$entirefields_array[$i];
			  	$minname = "min_".$entirefields_array[$i];
			 	$ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
			  	$maxvalue = $ranges[0];
			   	$minvalue = $ranges[1];
			  	$trFilter .= "<font class='afontstyle'>Min : <input name='".$minname."' id='".$minname."' value='".$minvalue."' size='3' 
			  	type='text' maxlength='4'>&nbsp;&nbsp;&nbsp;&nbsp;Max : <input name='".$maxname."' id='".$maxname."'  value='".$maxvalue."' 
			  	size='3' type='text' maxlength='4'></font>";
			  	$trFilter .= "</td>";
			  	$trFilter .= "</tr>";
		   	  }
			echo $trFilter;
	   }
	   $scriptStr .='});
						</script>';
		echo $scriptStr;	
	}
  //gives count of all the eCampaigns Created
function getEcampaignsCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	global $eCampaignArray;
	
	$dateStr = "";
	$count = 0;				
	if($argCustomFieldName == "EmployeeName")
	{
		$funTimeZone = tzRetQueryStringDTime('camp_date','YMDDate','-');
		if($argFromdate)
			$dateStr = "and  ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$sql = "select count(username) from campaign_list where username = '{$argCustomFieldId}' and par_id = '0' {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$row = mysql_fetch_row($rs);
		$result = ($row[0]) ? $row[0] : 0;
		return $result;
	}
	if($argCustomFieldName == "CustomCompanyName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."'";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$sql = "select sno from staffoppr_contact where csno = '{$argCustomFieldId}' ";
		$rs = mysql_query($sql,$rptdb);
		if($rs)
		{
			while($row = mysql_fetch_array($rs))
			{
				$contactid = "oppr".$row['sno'];
				$strCmn="select count(sno) from cmngmt_pr  where title = 'Campaign' and FIND_IN_SET('{$contactid}',con_id) {$dateStr} ";
				$rsCmn = mysql_query($strCmn,$rptdb);
				$rowCmn = mysql_fetch_row($rsCmn);
				$count += $rowCmn[0];
			}
			return $count;
		}
		else
			return 0;
	}
	if($argCustomFieldName == "CustomContactName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$contactid = "oppr".$argCustomFieldId;
		$strCmn ="select count(sno) from cmngmt_pr  where title = 'Campaign' and FIND_IN_SET('{$contactid}',con_id) {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		return $rowCmn[0];
	}
	if($argCustomFieldName == "CustomJobOrder")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$contactid = "req".$argCustomFieldId;
		$strCmn="select count(sno) from cmngmt_pr  where title = 'Campaign' and FIND_IN_SET('".$contactid."',con_id) {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
	if($argCustomFieldName == "CustomCandidate")
	{
		$funTimeZone = tzRetQueryStringDTime('b.camp_date','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$sql="SELECT a.username usr,count(b.sno) cnt  FROM candidate_list a, campaign_list b WHERE FIND_IN_SET(a.username, b.conlist) {$dateStr} GROUP BY a.username";
		$rsCmn = mysql_query($sql,$rptdb);
		while($rowCmn = mysql_fetch_row($rsCmn))
		{
			$eCampaignArray[$rowCmn[0]]=$rowCmn[1];
		}
	}
	return $eCampaignArray;
}
//gives count of all the Submissions sent
function getSubmissionsCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	global $submissArray;
	$dateStr = "";
	   
	if($argCustomFieldName == "EmployeeName")
	{
		$funTimeZone = tzRetQueryStringDTime('rdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		 
		$sql = "select count(username) from reqresponse  where username = '{$argCustomFieldId}' and par_id = '0' {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$row = mysql_fetch_row($rs);
		return $row[0];
	}
	if($argCustomFieldName == "CustomCompanyName")
	{
		/*$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		   
		$sql = "select sno from staffoppr_contact where csno = '{$argCustomFieldId}' ";
		$rs = mysql_query($sql,$rptdb);
		if($rs)
		{
			$count = 0;
			while($row = mysql_fetch_array($rs))
			{
				$contactid = "oppr".$row['sno'];
				$strCmn="select count(sno) from cmngmt_pr where title ='Submissions' and FIND_IN_SET('{$contactid}',con_id ) {$dateStr}";
				$rsCmn = mysql_query($strCmn,$rptdb);
				$rowCmn = mysql_fetch_array($rsCmn);
				$count += $rowCmn[0];
			}
			return $count;
		}
		else
			return 0;*/
		
		$funTimeZone = tzRetQueryStringDTime('rdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " AND ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " AND ".$funTimeZone." <='".$argTodate."' ";
		 
		$sql = "SELECT COUNT(1) FROM reqresponse r,posdesc p  WHERE r.posid=p.posid AND p.company='{$argCustomFieldId}' AND r.par_id = '0' {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$row = mysql_fetch_row($rs);
		return $row[0];
	}
	if($argCustomFieldName == "CustomContactName")
	{
		/*$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		   
		$contactid = "oppr".$argCustomFieldId;
		$strCmn ="select count(sno) from cmngmt_pr  where title = 'Submissions' and FIND_IN_SET('{$contactid}',con_id)  {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		return $rowCmn[0];*/
		$funTimeZone = tzRetQueryStringDTime('rdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " AND ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " AND ".$funTimeZone." <='".$argTodate."' ";
		 
		$sql = "SELECT COUNT(1) FROM reqresponse r,posdesc p  WHERE r.posid=p.posid AND p.contact='{$argCustomFieldId}' AND r.par_id = '0' {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$row = mysql_fetch_row($rs);
		return $row[0];
	}	  
	if($argCustomFieldName == "CustomJobOrder")
	{
		$funTimeZone = tzRetQueryStringDTime('rdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		  
		$strCmn = "Select count(1) from reqresponse where posid = '{$argCustomFieldId}' and par_id='0' {$dateStr}";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
	if($argCustomFieldName == "CustomCandidate")
	{
		
		$funTimeZone = tzRetQueryStringDTime('rdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		
		$sql="SELECT a.username usr, count(1) cnt  FROM candidate_list a, reqresponse b WHERE a.username IN (b.resumeid) {$dateStr} GROUP BY a.username";
		$rsCmn = mysql_query($sql,$rptdb);
		while($rowCmn = mysql_fetch_row($rsCmn))
		{
			$submissArray[$rowCmn[0]]=$rowCmn[1];
		}
	}
	return $submissArray;
}
//gives count of all the eCampagins Candidate count
function getEcampaignsCandiddateCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	 
	if($argCustomFieldName == "EmployeeName")
	{
		$funTimeZone = tzRetQueryStringDTime('camp_date','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
	   
		$sql="select conlist from campaign_list where username = '{$argCustomFieldId}' and par_id='0' and camptype = 'C' {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$count = 0;
		if($rs)
		{
			while($row = mysql_fetch_array($rs))
			{
				$conlist_array = explode(",",$row['conlist']);
				$count += count($conlist_array);
		   	}
			return $count;
		}
		else
			return 0;
	}
	if($argCustomFieldName == "CustomCompanyName")
	{
		
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
			
		$sql = "select sno from staffoppr_contact where csno = '{$argCustomFieldId}' ";
		$rs = mysql_query($sql,$rptdb);
		if($rs)
		{
			$count = 0;
			while($row = mysql_fetch_array($rs))
			{
				$contactid = "oppr".$row['sno'];
				$strCmn ="select sno,tysno from cmngmt_pr where title = 'Campaign' and FIND_IN_SET('{$contactid}',con_id ) {$dateStr}";
				$rsCmn = mysql_query($strCmn,$rptdb);
				$i = 0;
				while($rowCmn = mysql_fetch_array($rsCmn))
				{
					$tysnoids[$i] = $rowCmn['tysno'];
					$i++;
				}
				$tysno_str = implode(",",$tysnoids);
				$sqlReq = "select conlist from campaign_list where sno IN ({$tysno_str}) and par_id= '0' and camptype = 'C'";
				$rsReq = mysql_query($sqlReq,$rptdb);
				while($rowReq = mysql_fetch_array($rsReq))
				{
					$conlist_array = explode(",",$rowReq['conlist']);
					$count += count($conlist_array);
				}
			}
			return $count;
		}
		else
			return 0;
	}
	if($argCustomFieldName == "CustomContactName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		  
		$contactid = "oppr".$argCustomFieldId;
		$strCmn ="select sno,tysno from cmngmt_pr where title = 'Campaign' and FIND_IN_SET('{$contactid}',con_id) {$dateStr}";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$i = 0;
		while($rowCmn = mysql_fetch_array($rsCmn))
		{
			$tysnoids[$i] = $rowCmn['tysno'];
			$i++;
		}
		$tysno_str = implode(",",$tysnoids);
		$sqlReq = "select conlist from campaign_list where sno IN ({$tysno_str}) and par_id= '0' and camptype = 'C'";
		$rsReq = mysql_query($sqlReq,$rptdb);
		$count = 0;
		while($rowReq = mysql_fetch_array($rsReq))
		{
			$conlist_array = explode(",",$rowReq['conlist']);
			$count += count($conlist_array);
		}
		return $count;
	}
	if($argCustomFieldName == "CustomJobOrder")
	{
		return 0;
	}
}
function getSubmissionsCandiddateCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	if($argCustomFieldName == "EmployeeName")
	{
		
		$funTimeZone = tzRetQueryStringDTime('rdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		
		$sql = "select resumeid from reqresponse  where username = '{$argCustomFieldId}' and par_id = '0' {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$count = 0;
		if($rs)
		{
			while($row = mysql_fetch_array($rs))
			{
				$cand_array = explode(",",$row['resumeid']);
				$count += count($cand_array);
			}
			return $count;
		}
		else
			return '0';
	}  
	if($argCustomFieldName == "CustomCompanyName")
	{
		$dateStr	= "";
		$funTimeZone	= tzRetQueryStringDTime('appdate','YMDDate','-');

		if ($argFromdate)
		$dateStr	= " AND ".$funTimeZone." >='".$argFromdate."' ";

		if ($argTodate)
		$dateStr	.= " AND ".$funTimeZone." <='".$argTodate."' ";

		$sql	= "SELECT COUNT(req_id) FROM resume_status, posdesc, manage WHERE resume_status.pstatus = 'S' AND posdesc.posid = resume_status.req_id AND manage.sno = resume_status.status and manage.name ='Submitted' and manage.type = 'interviewstatus' AND posdesc.company = '{$argCustomFieldId}' {$dateStr} GROUP BY resume_status.req_id";
		$res	= mysql_query($sql, $rptdb);

		$count	= 0;

		if ($res) {

			while ($row = mysql_fetch_array($res)) {

				$count += $row[0];
			}
		}

		return $count;
	}
	if($argCustomFieldName == "CustomContactName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		
		$contactid = "oppr".$argCustomFieldId;
		$strCmn ="select sno,tysno from cmngmt_pr where title = 'Submissions' and FIND_IN_SET('{$contactid}',con_id) {$dateStr}";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$i = 0;
		while($rowCmn = mysql_fetch_array($rsCmn))
		{
			$tysnoids[$i] = $rowCmn['tysno'];
			$i++;
		}
		$tysno_str = implode(",",$tysnoids);
		$sqlReq = "select resumeid from reqresponse where sno IN ({$tysno_str}) and par_id= '0' ";
		$rsReq = mysql_query($sqlReq,$rptdb);
		$count = 0;
		while($rowReq = mysql_fetch_array($rsReq))
		{
			$conlist_array = explode(",",$rowReq['resumeid']);
			$count += count($conlist_array);
		}
		return $count;
	}
	if($argCustomFieldName == "CustomJobOrder")
	{
		
		$funTimeZone = tzRetQueryStringDTime('rdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";

		$sqlReq = "select resumeid from reqresponse where posid = {$argCustomFieldId} and par_id= '0' {$dateStr}";
		$rsReq = mysql_query($sqlReq,$rptdb);
		$count = 0;
		while($rowReq = mysql_fetch_array($rsReq))
		{
			$conlist_array = explode(",",$rowReq['resumeid']);
			$count += count($conlist_array);
		}
		return $count;
  	}
} 
function getInterviewsCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	global $interviewsArray;
	$count = 0;
	$dateStr = "";
	
	if($argCustomFieldName == "EmployeeName")
	{
		$funTimeZone = tzRetQueryStringDTime('appdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";

		//$sql = "select count(1) from resume_status,manage where manage.sno= resume_status.status and manage.type ='interviewstatus' and manage.name != 'Submitted' and  appuser='{$argCustomFieldId}' {$dateStr} group by req_id,res_id";
		$sql = "select count(1) from resume_history,manage where manage.sno= resume_history.status and manage.type ='interviewstatus' and manage.name = 'Interview' and resume_history.type='interview' and  appuser='{$argCustomFieldId}' {$dateStr}";
	}
	else if($argCustomFieldName == "CustomCompanyName")
	{
		$funTimeZone = tzRetQueryStringDTime('resume_history.appdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";

		//$sql = "select count(1) from resume_status,posdesc,manage where manage.sno= resume_status.status and manage.name !='Submitted' and manage.type='interviewstatus' and posdesc.company = '{$argCustomFieldId}' and resume_status.req_id = posdesc.posid {$dateStr} group by req_id,res_id";
		$sql = "select count(1) from resume_history,posdesc,manage where manage.sno= resume_history.status and manage.name ='Interview' and manage.type='interviewstatus' and resume_history.type='interview' and posdesc.company = '{$argCustomFieldId}' and resume_history.req_id = posdesc.posid {$dateStr}";
	}
	else if($argCustomFieldName == "CustomContactName")
	{
		$funTimeZone = tzRetQueryStringDTime('resume_history.appdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";

		//$sql = "select count(1) from resume_status,posdesc,manage where manage.sno= resume_status.status and manage.name !='Submitted' and manage.type='interviewstatus' and posdesc.contact = '{$argCustomFieldId}' and resume_status.req_id = posdesc.posid {$dateStr} group by req_id,res_id";
		$sql = "select count(1) from resume_history,posdesc,manage where manage.sno= resume_history.status and manage.name ='Interview' and manage.type='interviewstatus' and resume_history.type='interview' and posdesc.contact = '{$argCustomFieldId}' and resume_history.req_id = posdesc.posid {$dateStr}";
	}
	else if($argCustomFieldName == "CustomJobOrder")
	{
		$funTimeZone = tzRetQueryStringDTime('appdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		
		//$strCmn="select count(distinct(res_id)) from resume_status,manage where manage.sno = resume_status.status and manage.name !='submitted' and manage.type='interviewstatus' and  req_id = ".$argCustomFieldId."   {$dateStr}";
		$strCmn="select count(1) from resume_history,manage where manage.sno = resume_history.status and manage.name ='Interview' and manage.type='interviewstatus' and resume_history.type='interview' and  req_id = ".$argCustomFieldId."   {$dateStr}";
		
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
	if($argCustomFieldName == "CustomCandidate")
	{
		$funTimeZone = tzRetQueryStringDTime('b.appdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		 	
		//$sql="SELECT a.username, count(b.res_id) FROM candidate_list a, resume_status b,manage WHERE b.res_id = REPLACE (a.username,'cand','') and manage.sno = b.status and manage.name in ('Pending Placement','Interview','Tel-Pass','Personal-Pass') and manage.type='interviewstatus' {$dateStr} group by a.username ";
		$sql="SELECT a.username, count(b.res_id) FROM candidate_list a, resume_history b,manage WHERE b.res_id = REPLACE (a.username,'cand','') and manage.sno = b.status and manage.name='Interview' and manage.type='interviewstatus' {$dateStr} and b.type='interview' group by a.username ";
		$rsCmn = mysql_query($sql,$rptdb);
		while($rowCmn = mysql_fetch_row($rsCmn))
		{
			$interviewsArray[$rowCmn[0]]=$rowCmn[1];
		}
		return $interviewsArray;
	}
	$rs = mysql_query($sql,$rptdb);
	if($rs)
	{
		while($row = mysql_fetch_row($rs))
		{
			$count += $row[0];
		}
		return $count;
	}
	else
		return 0;
}
function getCandidatesPlacedCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	global $placedArray;
	 
	$dateStr = "";
        if($argCustomFieldName == "EmployeeName") {
            $hrconTimeZone = tzRetQueryStringDTime('hrcon_jobs.date_placed','YMDDate','-');
            $placeTimeZone = tzRetQueryStringDTime('placement_jobs.date_placed','YMDDate','-');
          
            if($argFromdate) {
                    $hrcondateStr = "AND ".$hrconTimeZone." >='".$argFromdate."' ";
                    $placedateStr = "AND ".$placeTimeZone." >='".$argFromdate."' ";
            }

            if($argTodate) {
                    $hrcondateStr .= "AND ".$hrconTimeZone." <='".$argTodate."' ";
                    $placedateStr .= "AND ".$placeTimeZone." <='".$argTodate."' ";
            }
        }
        else {
            $funTimeZone = tzRetQueryStringDTime('appdate','YMDDate','-');
            if($argFromdate)
                    $dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
            if($argTodate)
                    $dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
        }
	 
	if($argCustomFieldName == "EmployeeName")
		$sql = " SELECT COUNT(t) 
                FROM
                ((SELECT emp_list.sno, hrcon_jobs.pusername AS 't' 
                FROM emp_list 
                LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username 
                LEFT JOIN staffacc_cinfo ON hrcon_jobs.client=staffacc_cinfo.sno 
                LEFT JOIN staffacc_location jloc ON hrcon_jobs.endclient=jloc.sno 
                LEFT JOIN users cuser ON cuser.username=hrcon_jobs.owner 
                LEFT JOIN users muser ON muser.username=hrcon_jobs.muser 
                LEFT JOIN manage ON hrcon_jobs.jotype=manage.sno AND manage.type='jotype' 
                LEFT JOIN posdesc ON posdesc.posid = hrcon_jobs.posid 
                LEFT JOIN users usersowner ON usersowner.username = posdesc.owner 
                WHERE emp_list.lstatus!='DA' 
                AND (hrcon_jobs.ustatus in ('active','pending','closed','cancel')) 
                AND hrcon_jobs.jtype!='' 
                AND hrcon_jobs.jotype!=0 
                AND hrcon_jobs.owner='{$argCustomFieldId}' 
                {$hrcondateStr}
                GROUP BY hrcon_jobs.sno ) 
                UNION 
                (SELECT placement_jobs.sno, placement_jobs.pusername AS 't' 
                FROM candidate_list, placement_jobs 
                LEFT JOIN manage ON placement_jobs.jotype = manage.sno 
                LEFT JOIN staffacc_cinfo a ON a.sno = placement_jobs.client 
                LEFT JOIN staffacc_location jloc ON jloc.sno = placement_jobs.endclient 
                LEFT JOIN users cuser ON cuser.username = placement_jobs.owner 
                LEFT JOIN users muser ON muser.username = placement_jobs.muser 
                LEFT JOIN posdesc ON posdesc.posid = placement_jobs.posid 
                LEFT JOIN users usersowner ON usersowner.username = posdesc.owner 
                WHERE placement_jobs.candidate = candidate_list.username 
                AND candidate_list.candid NOT LIKE 'emp%' 
                AND candidate_list.candid != '' 
                AND candidate_list.candid IS NOT NULL 
                AND placement_jobs.jtype != '' 
                AND placement_jobs.jotype != '0' 
                AND placement_jobs.assg_status = 'Needs Approval' 
                AND placement_jobs.owner='{$argCustomFieldId}' 
                {$placedateStr}
                GROUP BY placement_jobs.sno )) d";
	else if($argCustomFieldName == "CustomCompanyName")
		$sql = "select count(req_id) from resume_status,posdesc where pstatus = 'P' and posdesc.posid = resume_status.req_id and posdesc.company = '{$argCustomFieldId}' {$dateStr} group by req_id";
	else if($argCustomFieldName == "CustomContactName")
		$sql = "select count(req_id) from resume_status, posdesc where pstatus = 'P' and posdesc.posid = resume_status.req_id and posdesc.contact = '{$argCustomFieldId}' {$dateStr} group by req_id";
	else if($argCustomFieldName == "CustomJobOrder")
	{
		$funTimeZone = tzRetQueryStringDTime('appdate','YMDDate','-'); 
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
			
		$strCmn="select count(distinct(res_id)) from resume_status where pstatus='P' and  req_id = ".$argCustomFieldId." {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
	if($argCustomFieldName == "CustomCandidate")
	{
		$funTimeZone = tzRetQueryStringDTime('b.appdate','YMDDate','-'); 
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$sql="select a.username,count(b.res_id) from candidate_list a,resume_status b where b.res_id=replace(a.username,'cand','') and pstatus='p' {$dateStr} group by a.username";
		$rsCmn = mysql_query($sql,$rptdb);
		while($rowCmn = mysql_fetch_row($rsCmn))
		{
			$placedArray[$rowCmn[0]]=$rowCmn[1];
		}
		return $placedArray;
	}
	$rs = mysql_query($sql,$rptdb);
	if($rs)
	{ 
		while($row = mysql_fetch_array($rs))
		{
			$count += $row[0];
		}
		return $count;
	}
	else
		return '0';
}
function getCandJobsApplied($argCustomFieldName,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	$candJobsApplied = array();
	$funTimeZone = tzRetQueryStringDTime('applied_date','YMDDate','-'); 
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	
	if($argCustomFieldName == 'CustomCandidate')
		$sql="SELECT candidate_id, count(candidate_id) FROM candidate_appliedjobs WHERE status = 'applied' {$dateStr} GROUP BY candidate_id";
	else if($argCustomFieldName == 'CustomJobOrder')
		$sql="SELECT candidate_appliedjobs.req_id,count(candidate_appliedjobs.req_id) FROM candidate_appliedjobs,posdesc WHERE candidate_appliedjobs.status = 'applied' AND candidate_appliedjobs.req_id =  posdesc.posid {$dateStr} GROUP BY candidate_appliedjobs.req_id";
	else
		return $candJobsApplied;		
		
	$rsCmn = mysql_query($sql,$rptdb);
	while($rowCmn = mysql_fetch_row($rsCmn))
	{
		$candJobsApplied[$rowCmn[0]] = $rowCmn[1];
	}
	return $candJobsApplied;
}

function getRevenue($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;

	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('udate','YMDDate','-'); 
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName")
		$sql = "select sum(placement_fee) as totalfee from hrcon_jobs where owner='{$argCustomFieldId}' 
		and ustatus ='active' and jtype = 'OP' {$dateStr}";
	else if($argCustomFieldName == "CustomCompanyName")
		$sql = "select sum(placement_fee) as totalfee from hrcon_jobs,posdesc where  ustatus ='active' and jtype = 'OP' {$dateStr} 	and hrcon_jobs.posid = posdesc.posid and posdesc.company = '{$argCustomFieldId}' ";
	else if($argCustomFieldName == "CustomContactName")
		$sql = "select sum(placement_fee) as totalfee from hrcon_jobs,posdesc where  ustatus ='active' and jtype = 'OP' {$dateStr} 	and hrcon_jobs.posid = posdesc.posid and posdesc.contact = '{$argCustomFieldId}' ";
	else if($argCustomFieldName == "CustomJobOrder" )
	{
		$funTimeZone = tzRetQueryStringDTime('udate','YMDDate','-'); 
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$count = "";
		$strCmn="select sum(placement_fee) from hrcon_jobs where posid = '{$argCustomFieldId}' and ustatus='active' {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row['totalfee']	= ($row['totalfee'] != "") ? $row['totalfee'] : 0;
	return $row['totalfee'];
}
function getJobPostingsCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	 
	if($argCustomFieldName == "EmployeeName")
	{	 
		$funTimeZone = tzRetQueryStringDTime('senddate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		 
		$sql = "select sno from job_post_det where username='{$argCustomFieldId}'  {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$row_count = mysql_num_rows($rs);
		if($row_count)
			return $row_count;
		else
			return '0';
	}
	if($argCustomFieldName == "CustomCompanyName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		  
		$sql = "select sno from staffoppr_contact where csno = '{$argCustomFieldId}' ";
		$rs = mysql_query($sql,$rptdb);
		if($rs)
		{
			while($row = mysql_fetch_array($rs))
			{
				$contactid = "oppr".$row['sno'];
				$strCmn ="select count(sno) from cmngmt_pr  where title = 'PostingIP' and FIND_IN_SET('{$contactid}',con_id) {$dateStr}";
				$rsCmn = mysql_query($strCmn,$rptdb);
				$rowCmn = mysql_fetch_row($rsCmn);
				$count += $rowCmn[0];
			}
			return $count;
		}
		else
			return 0;
	}
	if($argCustomFieldName == "CustomContactName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		  
		$contactid = "oppr".$argCustomFieldId;
		$strCmn ="select count(sno) from cmngmt_pr  where title = 'PostingIP' and FIND_IN_SET('{$contactid}',con_id) {$dateStr}";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		return $rowCmn[0];
	}
	if($argCustomFieldName == "CustomJobOrder" )
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = " and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= " and ".$funTimeZone." <='".$argTodate."' ";
		  
		$contactid = "req".$argCustomFieldId;
		$strCmn="select count(sno) from cmngmt_pr  where title = 'PostingIP' and FIND_IN_SET('".$contactid."',con_id) {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
}
function getJobOrdersEcampaignedCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	 
	if($argCustomFieldName == "EmployeeName")
	{
		$funTimeZone = tzRetQueryStringDTime('camp_date','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		
		$sql = "select conlist from campaign_list  where username = '{$argCustomFieldId}' and par_id = '0' and camptype = 'J' {$dateStr}";
		$rs = mysql_query($sql,$rptdb);
		$count = 0;
		if($rs)
		{
			while($row = mysql_fetch_array($rs))
			{
				$conlist_array = explode(",",$row['conlist']);
				$count += count($conlist_array);
			}
			return $count;
		}
		else
			return '0';
	}
	if($argCustomFieldName == "CustomCompanyName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	   
		$sql = "select sno from staffoppr_contact where csno = '{$argCustomFieldId}' ";
		$rs = mysql_query($sql,$rptdb);
		if($rs)
		{
			$count = 0;
			while($row = mysql_fetch_array($rs))
			{
				$contactid = "oppr".$row['sno'];
				$strCmn ="select sno,tysno from cmngmt_pr where title = 'Campaign' and FIND_IN_SET('{$contactid}',con_id) {$dateStr}";
				$rsCmn = mysql_query($strCmn,$rptdb);
				$i = 0;
				while($rowCmn = mysql_fetch_array($rsCmn))
				{
					$tysnoids[$i] = $rowCmn['tysno'];
					$i++;
				}
				$tysno_str = implode(",",$tysnoids);
		        $sqlReq = "select conlist from campaign_list where sno IN ({$tysno_str}) and par_id= '0' and camptype = 'J'";
				$rsReq = mysql_query($sqlReq,$rptdb);
				while($rowReq = mysql_fetch_array($rsReq))
				{
					$conlist_array = explode(",",$rowReq['conlist']);
					$count += count($conlist_array);
				}
			}
			return $count;
		}
		else
			return 0;
	}
	if($argCustomFieldName == "CustomContactName")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
			  
		$contactid = "oppr".$argCustomFieldId;
		$strCmn ="select sno,tysno from cmngmt_pr where title = 'Campaign' and FIND_IN_SET('{$contactid}',con_id) {$dateStr}";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$i = 0;
		while($rowCmn = mysql_fetch_array($rsCmn))
		{
			$tysnoids[$i] = $rowCmn['tysno'];
			$i++;
		}
		$tysno_str = implode(",",$tysnoids);
		$sqlReq = "select conlist from campaign_list where sno IN ({$tysno_str}) and par_id= '0' and camptype = 'J'";
		$rsReq = mysql_query($sqlReq,$rptdb);
		$count = 0;
		while($rowReq = mysql_fetch_array($rsReq))
		{
			$conlist_array = explode(",",$rowReq['conlist']);
			$count += count($conlist_array);
		}
		return $count;
	}
	if($argCustomFieldName == "CustomJobOrder")
	{
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$contactid = "req".$argCustomFieldId;
		$strCmn="select count(sno) from cmngmt_pr  where title = 'Campaign' and FIND_IN_SET('".$contactid."',con_id) {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
}
function getJobOrdersCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	
	$funTimeZone = tzRetQueryStringDTime('stime','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";

	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(posid) from posdesc where username='{$argCustomFieldId}' {$dateStr} ";
	else if($argCustomFieldName == "CustomCompanyName")
		$sql = "select count(posid) from posdesc where company ='{$argCustomFieldId}' {$dateStr} ";
	else if($argCustomFieldName == "CustomContactName")
		$sql = "select count(posid) from posdesc where contact ='{$argCustomFieldId}' {$dateStr} ";
	 
	if($argCustomFieldName == "CustomJobOrder")
	{
		return 0;
	}
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	return $row_count;
}
function getJobOrdersModifiedCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	
	$funTimeZone = tzRetQueryStringDTime('mdate','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(posid) from posdesc where muser='{$argCustomFieldId}' {$dateStr} ";
	else if($argCustomFieldName == "CustomCompanyName")
		$sql = "select count(posid) from posdesc where company ='{$argCustomFieldId}' {$dateStr} ";
	else if($argCustomFieldName == "CustomContactName")
		$sql = "select count(posid) from posdesc where contact ='{$argCustomFieldId}' {$dateStr} ";
	else if($argCustomFieldName == "CustomJobOrder")
	{
		return 0;
	}  
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	return $row_count;
}

function getCompaniesCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('cdate','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	   
	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(sno) from staffoppr_cinfo where staffoppr_cinfo.approveuser = '{$argCustomFieldId}' 
		 and crmcompany='Y' and status = 'ER'  {$dateStr} ";
	else if($argCustomFieldName == "CustomJobOrder")
	{
		return 0;  
	}  	
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = ($row[0] != "") ? $row[0] : 0;
	return $row_count;
}
function getCompaniesModifiedCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('mdate','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";

	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(sno) from staffoppr_cinfo where staffoppr_cinfo.muser = '{$argCustomFieldId}' 
		 and crmcompany='Y' and status = 'ER'  {$dateStr} ";
	else if($argCustomFieldName == "CustomJobOrder" )
	{
		return 0;  
	}
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = ($row[0] != "") ? $row[0] : 0;
	return $row_count;
}
function getContactsCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	 
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('stime','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	   
	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(sno) from staffoppr_contact where  staffoppr_contact.approveuser = {$argCustomFieldId} {$dateStr} ";
	else if($argCustomFieldName == "CustomCompanyName")
		$sql = "select count(sno) from staffoppr_contact where csno = '{$argCustomFieldId}' {$dateStr}";
	else if($argCustomFieldName == "CustomJobOrder" || $argCustomFieldName == "CustomContactName")
	{
		return 0;  
	}	
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	return $row_count;
}
function getContactsModifiedCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('mdate','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(sno) from staffoppr_contact where  staffoppr_contact.muser = {$argCustomFieldId} {$dateStr} ";
	else if($argCustomFieldName == "CustomCompanyName")
		$sql = "select count(sno) from staffoppr_contact where csno = '{$argCustomFieldId}' {$dateStr}";
	else if($argCustomFieldName == "CustomJobOrder" || $argCustomFieldName == "CustomContactName")
	{
		return 0;  
	}
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	return $row_count;
}
function getCandidatesCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('ctime','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(sno) from candidate_list where  candidate_list.cuser = {$argCustomFieldId}  {$dateStr}";
	if($argCustomFieldName == "CustomCompanyName")
		$sql = "select count(candidate_list.sno) from candidate_list,staffoppr_contact where  candidate_list.supid = staffoppr_contact.sno and   staffoppr_contact.csno = '{$argCustomFieldId}' {$dateStr}";
	else if($argCustomFieldName == "CustomContactName")
		$sql = "select count(candidate_list.sno) from candidate_list where  candidate_list.supid = '{$argCustomFieldId}' {$dateStr}";
	else if($argCustomFieldName == "CustomJobOrder" )
	{
		return 0;  
	}
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	return $row_count;
}
function getCandidatesModifiedCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{

	global $rptdb;
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('mtime','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(sno) from candidate_list where  candidate_list.muser = {$argCustomFieldId}  {$dateStr}";
	else if($argCustomFieldName == "CustomCompanyName")
		$sql = "select count(candidate_list.sno) from candidate_list,staffoppr_contact where  candidate_list.supid = staffoppr_contact.sno and   staffoppr_contact.csno = '{$argCustomFieldId}' {$dateStr}";
	else if($argCustomFieldName == "CustomContactName")
		$sql = "select count(candidate_list.sno) from candidate_list where  candidate_list.supid = '{$argCustomFieldId}' {$dateStr}";
	else if($argCustomFieldName == "CustomJobOrder" )
	{
		return 0;
	}	
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	return $row_count;
}
function getActivitiesCountByType($argCustomFieldName,$argActivityType,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	global $createdTaskArray,$modifiedTaskArray,$completedTaskArray;
	global $notesArray;
	global $username;

	$dateStr = "";
	if($argActivityType == 'CreatedTasks' || $argActivityType == 'ModifiedTasks' || $argActivityType == 'CompletedTasks')
	   $funTimeZone = tzRetQueryStringDTime('datecreated','YMDDate','-');
	else
	   $funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
	
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";

	$sql = "SELECT admin from sysuser where username=$username";
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_assoc($rs);
	$admin_pref = $row['admin'];
	$chkadmin = strpos($admin_pref, '+14+');//$admin_pref!='NO'  $chkadmin !== false

	//Checking whether logged in user have Admin & DataManagement access. 
	if($admin_pref != 'NO' && $chkadmin !== false)
	{
		$where = "";
		$elsecase="";
		$notejoin="";
	}
	else
	{
		$where = "AND cmngmt_pr.activity_status = '0'";
		$elsecase="AND activity_status = '0'";
		$notejoin ="AND b.activity_status = '0'";
	}

	   
	if($argCustomFieldName == "EmployeeName") 
	{
		
		if($argActivityType == 'CreatedTasks')
            $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' AND cuser='{$argCustomFieldId}' {$where} AND username='{$argCustomFieldId}' {$dateStr}";
        else if($argActivityType == 'ModifiedTasks')
            $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' AND cuser='{$argCustomFieldId}' {$where} AND username='{$argCustomFieldId}' AND timediff(mdate,datecreated)>'00:00:00' {$dateStr}";
       else if($argActivityType == 'CompletedTasks')
           $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' AND cuser='{$argCustomFieldId}' {$where} AND username='{$argCustomFieldId}' AND taskstatus='Completed' {$dateStr}";
        else
          $sql = "select count(sno) from cmngmt_pr where username = '{$argCustomFieldId}' {$elsecase} and title = '".addslashes($argActivityType)."' {$dateStr}";
        
	}
	else if($argCustomFieldName == "CustomCompanyName")
	{ 

		$sql = "select sno from staffoppr_contact where csno = '{$argCustomFieldId}' ";
		$rs = mysql_query($sql,$rptdb);
		if($rs)
		{
			while($row = mysql_fetch_array($rs))
			{
				$contactid = "oppr".$row['sno'];
				$strCmn="select count(sno) from cmngmt_pr  where title = 'Campaign' {$elsecase}  and FIND_IN_SET('{$contactid}',con_id) {$dateStr} ";
				$rsCmn = mysql_query($strCmn,$rptdb);
				$rowCmn = mysql_fetch_row($rsCmn);
				$count += $rowCmn[0];
			}
		}
		$compid = "com".$argCustomFieldId;
		if($argActivityType == 'CreatedTasks' || $argActivityType == 'ModifiedTasks' || $argActivityType == 'CompletedTasks')
        {
            $setQry = "SET SESSION group_concat_max_len=1073740800";
            mysql_query($setQry,$rptdb);

            $sque="select group_concat('oppr',sno) from staffoppr_contact where csno='{$argCustomFieldId}'";
            $sres=mysql_query($sque,$rptdb);
            $srow=mysql_fetch_row($sres);

            $contactids=$srow[0];
            $compid="com".$argCustomFieldId;

            if($contactids!='')
                $cmnids=$contactids.",".$compid;
            else
                $cmnids=$compid;
				if($argActivityType == 'CreatedTasks')
                $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND match(cmngmt_pr.con_id) against ('$cmnids') {$dateStr}";
            else if($argActivityType == 'ModifiedTasks')
                $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND match(cmngmt_pr.con_id) against ('$cmnids') AND timediff(mdate,datecreated)>'00:00:00' {$dateStr}";
            else if($argActivityType == 'CompletedTasks')
                $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND match(cmngmt_pr.con_id) against ('$cmnids') AND taskstatus='Completed' {$dateStr}";
            
        }else if($argActivityType == 'Email' || $argActivityType == 'REmail') {

			$setQry = "SET SESSION group_concat_max_len=1073740800";       
			 mysql_query($setQry,$rptdb);   

			$sque="select group_concat('oppr',sno) from staffoppr_contact where csno='{$argCustomFieldId}'";       
			 $sres=mysql_query($sque,$rptdb);       
			  $srow=mysql_fetch_row($sres);       
			 $contactids=$srow[0];       
			$compid="com".$argCustomFieldId;       
			 if($contactids!='')       
			 $cmnids=$contactids.",".$compid;       
			  else       
			$cmnids=$compid;     

			 
				$sql = "select count(sno) from cmngmt_pr where match(cmngmt_pr.con_id) against ('$cmnids') 
				{$elsecase}  AND `title` = '".addslashes($argActivityType)."' {$dateStr}";
			
  
			
			 //die();
       
	}
       else{
		
        $sql = "select sno from staffoppr_contact where csno = '{$argCustomFieldId}' ";
		$rs = mysql_query($sql,$rptdb);
		if($rs)
		{
			while($row = mysql_fetch_array($rs))
			{

				$contactid = "oppr".$row['sno'];

				
					$strCmn="select count(sno) from cmngmt_pr  where title ='".addslashes($argActivityType)."' {$elsecase}  and match (con_id) against('{$contactid}') {$dateStr} ";
					
				
				$rsCmn = mysql_query($strCmn,$rptdb);
				$rowCmn = mysql_fetch_row($rsCmn);
				$count += $rowCmn[0];
			}
		}
			
					$sql = "select count(sno) from cmngmt_pr where match (cmngmt_pr.con_id) against( '{$compid}') >0 {$elsecase}  and title = '".addslashes($argActivityType)."' {$dateStr}";
				
		}	
		$rsCmn = mysql_query($sql,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count1 = $count + $rowCmn[0];
		return $count1;
	}
	else if($argCustomFieldName == "CustomContactName")
	{ 
		$contactid = "oppr".$argCustomFieldId;
		 
 		 if($argActivityType == 'CreatedTasks')
			$sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) {$dateStr}";
        else if($argActivityType == 'ModifiedTasks')
            $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' AND MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) {$where} AND timediff(mdate,datecreated)>'00:00:00' {$dateStr}	";
       	else if($argActivityType == 'CompletedTasks')
            $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' AND MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) {$where} AND taskstatus='Completed' {$dateStr}";
        else
		 	$sql ="select count(sno) from cmngmt_pr where MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE)  {$elsecase} and title = '".addslashes($argActivityType)."' {$dateStr}";
 		 	
	}
	else if($argCustomFieldName == "CustomJobOrder")
	{

        if($argActivityType == 'CreatedTasks' || $argActivityType == 'ModifiedTasks' || $argActivityType == 'CompletedTasks')
            $funTimeZone = tzRetQueryStringDTime('datecreated','YMDDate','-');
        else
            $funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		
		$contactid = "req".$argCustomFieldId;
		
		
				if($argActivityType == 'CreatedTasks')
                $strCmn = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) {$dateStr}";
	            else if($argActivityType == 'ModifiedTasks')
	                $strCmn = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) AND timediff(mdate,datecreated)>'00:00:00' {$dateStr}";
	            else if($argActivityType == 'CompletedTasks')
					$strCmn = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) AND taskstatus='Completed' {$dateStr}";
	            else
	                $strCmn="select count(sno) from cmngmt_pr where MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) {$elsecase} and title = '".addslashes($argActivityType)."' {$dateStr}";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
	if($argCustomFieldName == "CustomCandidate")
	{
        if($argActivityType == 'CreatedTasks' || $argActivityType == 'ModifiedTasks' || $argActivityType == 'CompletedTasks')
            $funTimeZone = tzRetQueryStringDTime('datecreated','YMDDate','-');
        else
            $funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";

        if($argActivityType == 'CreatedTasks' || $argActivityType == 'ModifiedTasks' || $argActivityType == 'CompletedTasks')
        {
				 if($argActivityType == 'CreatedTasks')
               $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND FIND_IN_SET( '{$argCustomFieldId}', cmngmt_pr.con_id ) >0 {$dateStr}";
            else if($argActivityType == 'ModifiedTasks')
                $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND FIND_IN_SET( '{$argCustomFieldId}', cmngmt_pr.con_id ) >0 AND timediff(mdate,datecreated)>'00:00:00' {$dateStr}";
            else if($argActivityType == 'CompletedTasks')
                $sql = "SELECT count(1) FROM cmngmt_pr, tasklist WHERE cmngmt_pr.tysno=tasklist.sno AND cmngmt_pr.title='Task' {$where} AND FIND_IN_SET( '{$argCustomFieldId}', cmngmt_pr.con_id ) >0 AND taskstatus='Completed' {$dateStr}";

            $rs = mysql_query($sql,$rptdb);
            $row = mysql_fetch_row($rs);
            return $row[0];
        }


		if($argActivityType == 'Notes')
		{
					
					$sql="select a.username,count(b.sno) from candidate_list a,cmngmt_pr b where a.username in (b.con_id) {$notejoin} and b.title='".addslashes($argActivityType)."' group by a.username";
				
			$rsCmn = mysql_query($sql,$rptdb);
			while($rowCmn = mysql_fetch_row($rsCmn))
			{
				$notesArray[$rowCmn[0]]=$rowCmn[1];
			}
			return $notesArray;
		}
	}
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_row($rs);
	return $row[0];
}
function getCompenCode($argCustomFieldId)
{
	return "";
}
//function to the count of note types 
function getNoteTypesCount()
{
	global $rptdb;
	$sql = "select sno from manage where type='notes' ";
	$rs = mysql_query($sql,$rptdb);
	$row_count = mysql_num_rows($rs);
	return $row_count;
}
//function to get all note types
// return type Array
function getAllNoteTypes()
{
	global $rptdb;
	$sql 	= "SELECT sno,name FROM manage WHERE type='notes' ORDER BY name";
	$rs 	= mysql_query($sql,$rptdb);
	while($row = mysql_fetch_assoc($rs))
	{
		$sno			= $row['sno'];
		$arrNoteType[$sno] 	=  $row['name'];
	}
	return $arrNoteType;
}

function getArrayNotesType($argFieldArray)
{
     global $rptdb;
	 $sql = "select name from manage where type='notes' order by name";
	 $rs = mysql_query($sql,$rptdb);
	 
	 while($row = mysql_fetch_assoc($rs))
	 {
	   array_push($argFieldArray,$row['name']);
	 }
	 return $argFieldArray;
}
function getNotesCountByType($argCustomFieldName,$argNoteType,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	 
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName")
		$sql = "select count(sno) from cmngmt_pr where username = {$argCustomFieldId} and title = 'notes' and subtype = '".addslashes($argNoteType)."' {$dateStr}";
	else if($argCustomFieldName == "CustomCompanyName")
	{ 
		$compid = "com".$argCustomFieldId;
		$sql = " SELECT count( DISTINCT (cp.sno) )FROM `cmngmt_pr` AS cp, staffoppr_contact AS so WHERE so.csno = $argCustomFieldId AND cp.title = 'Notes' AND cp.subtype = '".addslashes($argNoteType)."'  AND ( cp.`con_id` = CONCAT( 'oppr', so.sno ) OR match (cp.con_id) against( '{$compid}') ) {$dateStr}";   
	}
	else if($argCustomFieldName == "CustomContactName")
	{ 
		$contactid = "oppr".$argCustomFieldId;
		$sql = "select count(sno) from cmngmt_pr where MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) and title = 'notes' and subtype = '".addslashes($argNoteType)."' {$dateStr}";
	}
	else if($argCustomFieldName == "CustomJobOrder")
	{		
		$funTimeZone = tzRetQueryStringDTime('sdate','YMDDate','-');
		if($argFromdate)
			$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
		if($argTodate)
			$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
		  
		$contactid = "req".$argCustomFieldId;
		$strCmn="select count(sno) from cmngmt_pr  where title = 'notes' and MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) and subtype = '".addslashes($argNoteType)."' {$dateStr} ";
		$rsCmn = mysql_query($strCmn,$rptdb);
		$rowCmn = mysql_fetch_row($rsCmn);
		$count = $rowCmn[0];
		return $count;
	}
	if($argCustomFieldName == "CustomCandidate")
	{
		$contactid = $argCustomFieldId;
		$sql = "select count(sno) from cmngmt_pr where MATCH(con_id) AGAINST ('{$contactid}' IN BOOLEAN MODE) and title = 'notes' and subtype = '".addslashes($argNoteType)."' {$dateStr}";
	}
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	return $row_count;
}
function getManageListOptions($argListType,$argSel = " ")
{
   global $rptdb;
   $option = " ";
   $selected = " ";
   $sql = "select sno,name from manage where type='{$argListType}' order by name";
   $rs =  mysql_query($sql,$rptdb);
   while($row = mysql_fetch_array($rs))
   {
	 if($argSel && ($argSel == $row['sno']) )
	  $selected = "selected";
	 else
	   $selected = " ";
 
	 $option .= "<option value='{$row[sno]}' {$selected}>{$row[name]}</option>" ;
   }
   return $option;
}
function getStringFilterIds($argFilterValue)
{
	global $rptdb;
	$sql = "select username  from users where name like '%".addslashes($argFilterValue)."%'";
	$rs =  mysql_query($sql,$rptdb);
	$row = mysql_fetch_row($rs);
	return $row[0];
}
function datesModified($dateopt)
{
	$dates="";
	$days="";
	$lastweek="";
	$lastmonth="";
	$lastyear="";
	if($dateopt == 'tflastweek')
	{
		$dates = date("Y-m-d",mktime(0, 0, 0, date("m"),date("d")-(date('w')+1),date("Y")));
		$days = explode("-",$dates);
		$lastweek = date("Y-m-d",mktime(0, 0, 0, $days[1],$days[2]-6,$days[0]))."^".$dates;
		return $lastweek;
	}
	else if($dateopt == 'tflastmonth')
	{
		$dates = date("Y-m-d",mktime(0, 0, 0, date("m"),date("d")-date("d"),date("Y")));
		$days = explode("-",$dates);
		$lastmonth = date("Y-m-d",mktime(0, 0, 0, $days[1],01,$days[0]))."^".$dates;
		return $lastmonth;
	}
	else if($dateopt == 'tflastyear')
	{
		$dates = date("Y-m-d",mktime(0, 0, 0, date("m"),date("d")-(date('z')+1),date("Y")));
		$days = explode("-",$dates);
		$lastyear = date("Y-m-d",mktime(0, 0, 0, 1,1,date("Y")-1))."^".$dates;
		return $lastyear;
	}
}

//function to get all roles types
// return type Array
function getAllRolesTypes()
{
     global $rptdb;
	 $sql = "select roletitle from company_commission WHERE status!='deactive' ORDER BY roletitle, commission_default";
	 $rs = mysql_query($sql,$rptdb);
	 $loop = 0;
	 while($row = mysql_fetch_assoc($rs))
	 {
	    $arrSubRole[$loop] =  $row['roletitle'];
		$loop++;
	 }
	 return $arrSubRole;
}
function getAllRolesTypesAll()
{
     global $rptdb;
	 $sql = "select roletitle from company_commission  ORDER BY roletitle, commission_default";
	 $rs = mysql_query($sql,$rptdb);
	 $loop = 0;
	 while($row = mysql_fetch_assoc($rs))
	 {
	    $arrSubRole[$loop] =  $row['roletitle'];
		$loop++;
	 }
	 return $arrSubRole;
}
function getAllRolesTypesID()
{
     global $rptdb;
	 $sql = "select sno from company_commission WHERE status!='deactive' ORDER BY roletitle, commission_default";
	 $rs = mysql_query($sql,$rptdb);
	 $loop = 0;
	 while($row = mysql_fetch_assoc($rs))
	 {
	    $arrSubRole[$loop] =  $row['sno'];
		$loop++;
	 }
	 return $arrSubRole;
}
function getRoleSubCount()
{
	global $rptdb;
	$sql = "select roletitle from company_commission WHERE status!='deactive' ";
	$rs = mysql_query($sql,$rptdb);
	$row_count = mysql_num_rows($rs);
	return $row_count;
}

/*function getRoleCountByType($argCustomFieldName,$argRoleType,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('e.cdate','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName") {
	 $sql = "select count(e.crsno) from company_commission c left join  entity_submission_roledetails d  on c.sno=d.roleId left join entity_submission_roles e on e.crsno=d.crsno where  e.entityType='CRMSub' and  e.empId='".$argCustomFieldId."' and c.roletitle='".$argRoleType."' and c.status!='deactive' {$dateStr}";
	
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	} else {
	$row_count =0;
	}
	return $row_count;
} */

function getRoleCountByType($argCustomFieldName,$argRoleType,$argCustomFieldId,$argFromdate = '',$argTodate = '')
{
	global $rptdb;
	$dateStr = "";
	$funTimeZone = tzRetQueryStringDTime('d.cdate','YMDDate','-');
	if($argFromdate)
		$dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
	if($argTodate)
		$dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
	 
	if($argCustomFieldName == "EmployeeName") {
	 $sql = "select count(*) from company_commission c left join  entity_submission_roledetails d  on c.sno=d.roleId  where    d.empId='".$argCustomFieldId."' and c.roletitle='".$argRoleType."' and c.status!='deactive' {$dateStr}";
	
	$rs = mysql_query($sql,$rptdb);
	$row = mysql_fetch_array($rs);
	$row_count = $row[0];
	} else {
	$row_count =0;
	}
	return $row_count;
}

function getOneRole($roleres)
{
     global $rptdb;
	 $sno_arr = explode("_",$roleres);
	 $sno = $sno_arr[1];
	 $sql = "select roletitle from company_commission WHERE status!='deactive' and sno='".$sno."' ORDER BY roletitle, commission_default";
	 $rs = mysql_query($sql,$rptdb);
	 $row_count = mysql_num_rows($rs);
	 return $row_count;
}
function getSnoRole($roleres)
{
     global $rptdb;
	 $sno_arr = explode("_",$roleres);
	 $sno = $sno_arr[1];
	 $sql = "select sno from company_commission WHERE status!='deactive' and sno='".$sno."' ORDER BY roletitle, commission_default";
	 $rs = mysql_query($sql,$rptdb);
	 $row = mysql_fetch_array($rs);
	 $row_count = $row[0];
	 
	 return $row_count;
}
// for 'Assignments Created' column data count 
function getassignmentscreatedCount($argCustomFieldName,$argCustomFieldId,$argFromdate = '',$argTodate = '',$deptAccesSno = 0)
{   
    global $rptdb,$username;
    
//    $dque="select group_concat(sno) from department where FIND_IN_SET(CONCAT('+','".$username."','+'),REPLACE(CONCAT('+',permission,'+'),',','+,+'))>0";
     $dque="select group_concat(sno) from department where sno !='0' AND sno IN ({$deptAccesSno})";
    $dres=mysql_query($dque,$rptdb);
    $drow=mysql_fetch_row($dres);
    $deptnos = $drow[0];

    if($deptnos=="")
            $deptnos="0";
    
    $dateStr = "";
    $funTimeZone = tzRetQueryStringDTime('hrcon_jobs.cdate','YMDDate','-'); 
    if($argFromdate)
            $dateStr = "and ".$funTimeZone." >='".$argFromdate."' ";	
    if($argTodate)
            $dateStr .= "and ".$funTimeZone." <='".$argTodate."' ";
    
    if($argCustomFieldName == "EmployeeName"){ 
        $sql = "SELECT emp_list.name
                FROM emp_list
                LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
                LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username
                WHERE hrcon_compen.ustatus='active' AND hrcon_compen.dept IN ($deptnos)
                AND hrcon_jobs.owner='$argCustomFieldId'
                AND emp_list.lstatus != 'DA'
                AND hrcon_jobs.jtype != ''
                AND hrcon_jobs.jotype != '0'
                AND hrcon_jobs.ustatus
                IN ('closed', 'cancel','pending') {$dateStr}"; 
           
    } 
    else if($argCustomFieldName == "CustomCompanyName")
    { 
        $sql = "SELECT acc.sno 'sno' FROM staffacc_cinfo acc WHERE acc.crm_comp =$argCustomFieldId";
        $results = mysql_query($sql,$rptdb);
        $result_data=mysql_fetch_row($results);
        $acc_sno=$result_data[0];
        if($acc_sno){
            $sqlquery ="SELECT hrcon_jobs.pusername
                        FROM emp_list 
                        LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
                        LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username
                        WHERE hrcon_compen.ustatus='active' AND hrcon_compen.dept IN ($deptnos)
                        AND emp_list.lstatus != 'DA'
                        AND hrcon_jobs.jtype != ''
                        AND hrcon_jobs.jotype != '0'
                        AND hrcon_jobs.ustatus IN ('closed', 'cancel','pending')
                        AND hrcon_jobs.CLIENT = $acc_sno {$dateStr}";
                        

            $result=mysql_query($sqlquery,$rptdb);
            $resultcount=mysql_num_rows($result); 
            return $resultcount;
        }else{
            return 0;
        }
    }
    else if($argCustomFieldName == "CustomContactName")
    {
        $sql = "SELECT oppr.acc_cont 'acc_cont'
                   FROM staffoppr_contact oppr 
                   WHERE oppr.sno=".$argCustomFieldId."
                   AND oppr.csno!=0
		   AND oppr.status = 'ER'";
        $results = mysql_query($sql,$rptdb);
        $result_data=mysql_fetch_row($results);
        $acc_cont=$result_data[0];
        
        if($acc_cont){

            $sql = "SELECT hrcon_jobs.pusername 
                    FROM emp_list 
                    LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
                    LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username 
                    WHERE hrcon_compen.ustatus='active' AND hrcon_compen.dept IN ($deptnos)
                    AND emp_list.lstatus != 'DA'
                    AND hrcon_jobs.contact='$acc_cont' 
                    AND hrcon_jobs.jtype != ''
                    AND hrcon_jobs.jotype != '0'
                    AND hrcon_jobs.ustatus
                    IN ('closed', 'cancel','pending') {$dateStr}";
            
            $result = mysql_query($sql,$rptdb);
            $resultcount = mysql_num_rows($result);
            return $resultcount;
        }else{
            return 0;
        }
    }
    else if($argCustomFieldName == "CustomJobOrder" )
    { 
                
        $sql = "SELECT hrcon_jobs.pusername 
                FROM emp_list 
                LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
                LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username 
                WHERE hrcon_compen.ustatus='active' AND hrcon_compen.dept IN ($deptnos)
                AND emp_list.lstatus != 'DA'
                AND hrcon_jobs.posid = '$argCustomFieldId' 
                AND hrcon_jobs.jtype != '' 
                AND hrcon_jobs.jotype != '0' 
                AND hrcon_jobs.ustatus IN ('closed', 'cancel','pending') {$dateStr}";
    }
    else
    {
        $sql="SELECT e.username FROM candidate_list a,emp_list e WHERE e.sno=replace(a.candid,'emp','') AND a.username='$argCustomFieldId' AND a.status='ACTIVE' and a.ctype='Employee'";
        $results = mysql_query($sql,$rptdb);
        $result_data=mysql_fetch_row($results);
        $cand_username=$result_data[0];

        if($cand_username){
            $sql = "SELECT hrcon_jobs.pusername
                    FROM emp_list 
                    LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
                    LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username
                    WHERE hrcon_compen.ustatus='active' AND hrcon_compen.dept IN ($deptnos)
                    AND emp_list.lstatus != 'DA'
                    AND hrcon_jobs.username='$cand_username'
                    AND hrcon_jobs.jtype != ''
                    AND hrcon_jobs.jotype != '0'
                    AND hrcon_jobs.ustatus
                    IN ('closed', 'cancel','pending') {$dateStr}";

            $result = mysql_query($sql,$rptdb);
            $resultcount=mysql_num_rows($result); 
            return $resultcount; 
        }else{ 
            return 0;
        }
    }
    $result = mysql_query($sql,$rptdb);
    $resultcount = mysql_num_rows($result); 
    return $resultcount;    
}

//return the sno of note item
function getNotesSno($noteres)
{
	$notesArr = explode("_",$noteres);
	return $notesArr[1];
}

// return the row count to find whether note item exits in the db or not
function getOneNote($noteres)
{
	global $rptdb;
	$sno_arr = explode("_",$noteres);
	$sno = $sno_arr[1];
	$sql = "SELECT sno FROM manage WHERE type='notes' AND sno='".$sno."' ORDER BY name";
	$rs = mysql_query($sql,$rptdb);
	$row_count = mysql_num_rows($rs);
	return $row_count;
}

// return the array by pushing the new elements along with keys
function pushArrayElementsWithKeys($argMainArray,$argAppendArray)
{	
	foreach($argAppendArray as $key=>$value)
	{
		$argMainArray[$key] = $value;
	}
	return $argMainArray;
}
?>
