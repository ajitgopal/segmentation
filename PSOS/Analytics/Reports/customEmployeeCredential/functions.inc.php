<?php 
	require("global_reports.inc");	
	require("dispfunc.php");
	require("reportdatabase.inc");
	require_once('credential_management/credentials_type_db.php');
	require_once('credential_management/credentials_name_db.php');
	require_once('credential_management/countries_states.php');
	require_once('credential_management/JSON.php');
	
	$objCredentialsType		= new ManageCredentialsType();
	$objCredentialsName		= new ManageCredentialsName();
	$objCredentialsJson		= new Services_JSON();

//function to benifit types
function getAllEarTypes()
{
	global $rptdb;
	
	$sql = "select distinct(title) from hrcon_contribute where contribution_chk='Y' AND ustatus='active' AND title !='' order by title";
	$rs = mysql_query($sql,$rptdb);

	$loop = 0;

	while($row = mysql_fetch_assoc($rs))
	{
		$eartypes = str_replace(' ','___',"$$".$row['title']);
		$arrEarType[$loop] =  html_tls_specialchars($eartypes,ENT_QUOTES);
		$loop++;
	}

	return $arrEarType;
}

//-----function  to get the display names for each column which are used in getfilters-----//
function getDisplayName($argFieldName)
{
	$fieldnames = array("empBranchLocation"=>"HRM Location","employeestatus" =>"Employee Status","empCompanyCode" =>"Company Code","empdept" => "HRM Department","credentialtype" => "Credential Type","credentialname" => "Credential Name","credentialcountry" => "Country","credentialstates" => "Valid State","credentialvfromto" => "Credential Expiration Date","credentialstatus" => "Credential Status","asgnstatus"=>"Assignment Status","credentialacquiredfromto" => "Credential Acquired Date");
	
	return $fieldnames[$argFieldName];
}

function sele($argValue,$argSelectValue)
{
	if($argValue==$argSelectValue)
		return "selected";
	else
		return "";
}
		

function sel($argValue,$argSelectValue)
{
	if($argValue==$argSelectValue)
		return "checked";
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
	

function seleAtype($ast)
{
	if($ast=="OP")
		$astyp="Project";
	else if($ast=="OB")
		$astyp="On Bench";
	else if($ast=="OV")
		$astyp="On Vacation";	
	else if($ast=="AS")
		$astyp="Administrative Staff";			

	return $astyp;
} 

//filter function to display the columns in the filter tab
function getFilters($argFilterNames,$argFilterValues,$frmpg,$deptAccesSno) 
{
	global $objCredentialsJson, $countries;
	
	$chkExempt="";

	$entirefields_array = array("employeestatus","empdept","credentialtype","credentialname","credentialstatus","credentialvfromto","asgnstatus","credentialacquiredfromto");

	
	$filternames_array = explode("^",$argFilterNames);
	$filtervalues_array = explode("^",dispTextdb($argFilterValues));
	$correspondingvalues_array = array_combine($filternames_array , $filtervalues_array);
	$getEntireFields_Count = count($entirefields_array);
	
	$scriptStr = '<script type="text/javascript" src="/BSOS/scripts/countries_states.js"></script>
	
		<script type="text/javascript">
	
		$(document).ready(function() {';

	for($i=0; $i<$getEntireFields_Count; $i++)
	{ 

		$row_id = "filter_".$entirefields_array[$i];

		if(in_array($entirefields_array[$i],$filternames_array))
			$style_filter = '';
		else
			$style_filter = "style='display:none'";

		$showFilter = "<tr id='{$row_id}' {$style_filter}>";
		$showFilter .= "<td width=3%>&nbsp;</td>";
		$showFilter .= "<td width=40%><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
		$showFilter .= "<td align=left width=60%>";

		//Filter for Date Columns
		if(($entirefields_array[$i] == "credentialvfromto")) {

			$maxname = "max_".$entirefields_array[$i];
			$minname = "min_".$entirefields_array[$i];

			$ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
			
			if($ranges[0]=='' && $ranges[1]=='' && $frmpg == "main"){					
				$minvalue 	= date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))). "-1 month"));
				$maxvalue 	= date('m/d/Y');
			}
			else
			{
				$minvalue = $ranges[0];
				$maxvalue = $ranges[1];
			}
			
			$showFilter .=  "<font class='afontstyle'>

				       From : <input name='".$minname."' value='".$minvalue."' size='8' type='text' id='".$minname."' readonly>								
				       <script language='JavaScript'> new tcal ({'formname':'customEmployeeCredential','controlname':'".$minname."'});</script>
				      <a href=javascript:resetStartDate('".$minname."')>
				       <i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;

				       To : <input name='".$maxname."' value='".$maxvalue."' size='8' type='text' id='".$maxname."' readonly>
				       <script language='JavaScript'> new tcal ({'formname':'customEmployeeCredential','controlname':'".$maxname."'});</script>
				       <a href=javascript:resetStartDate('".$maxname."')>
				       <i alt='Reset' class='fa fa-reply'></i></a>";
		}else if(($entirefields_array[$i] == "credentialacquiredfromto")) {

			$maxname = "max_".$entirefields_array[$i];
			$minname = "min_".$entirefields_array[$i];

			$ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
			
			if($ranges[0]=='' && $ranges[1]=='' && $frmpg == "main"){					
				/*$minvalue 	= date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))). "-1 month"));
				$maxvalue 	= date('m/d/Y');*/
				$minvalue 	= '';
				$maxvalue 	= '';
			}
			else
			{
				$minvalue = $ranges[0];
				$maxvalue = $ranges[1];
			}
			
			$showFilter .=  "<font class='afontstyle'>

				       From : <input name='".$minname."' value='".$minvalue."' size='8' type='text' id='".$minname."' readonly>								
				       <script language='JavaScript'> new tcal ({'formname':'customEmployeeCredential','controlname':'".$minname."'});</script>
				      <a href=javascript:resetStartDate('".$minname."')>
				       <i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;

				       To : <input name='".$maxname."' value='".$maxvalue."' size='8' type='text' id='".$maxname."' readonly>
				       <script language='JavaScript'> new tcal ({'formname':'customEmployeeCredential','controlname':'".$maxname."'});</script>
				       <a href=javascript:resetStartDate('".$maxname."')>
				       <i alt='Reset' class='fa fa-reply'></i></a>";
		}
		else if($entirefields_array[$i]=="employeestatus") { //Filter for Status
		
			$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
		
			$showFilter .=  "<select class=drpdwne style='width:165px; height:20px' name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
			$showFilter .=  "<option value='ALL'>ALL</option>";
			$showFilter .= "<option value='N' ".sele("N",$selectValue).">Active</option>";
			$showFilter .= "<option value='Y' ".sele("Y",$selectValue).">Terminated</option>";
			$showFilter .=  "</select>";        
		}
		else if($entirefields_array[$i] == "empdept") {
			$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';
			$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
			
			$selected_val	= '';
			$sel_dept_id	= array();

			if (isset($selectValue) && !empty($selectValue)) 
			{
				$sel_dept_id	= explode(',', $selectValue); //hold dept names
			}

			if (empty($sel_dept_id) || $sel_dept_id[0] == 'ALL') 
			{
				$selected_val	= 'selected';
			}
			
			

			//$options = getDepartmentList($selectValue);

			$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}[]' id='select_{$entirefields_array[$i]}'>";
			$showFilter .= "<option value='ALL' ".$selected_val.">ALL</option>";
			//$showFilter .=  $options;
			$departments	= getDepartments($deptAccesSno);
			if (!empty($departments)) 
			{
				foreach ($departments as $id => $name) 
				{
					if (!empty($sel_dept_id)) 
					{
						if (in_array($name, $sel_dept_id)) 
						{
							$selected_val	= 'selected';
						} else 
						{
							$selected_val	= '';
						}

						$showFilter	.= "<option value='".$name."'  ".$selected_val.'>'.$name.'</option>';

					} 
					else 
					{

						$showFilter	.= "<option value='".$name."'  ".$selected_val.'>'.$name.'</option>';
					}
				}
			}
			$showFilter .=  "</select>";
		}
		else if($entirefields_array[$i] == "credentialtype") {
			$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';

			$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
			$selected_val	= '';
			$sel_dept_id	= array();

			if (isset($selectValue) && !empty($selectValue)) 
			{
				$sel_dept_id	= explode(',', $selectValue); //hold dept names
			}

			if (empty($sel_dept_id) || $sel_dept_id[0] == 'ALL') 
			{
				$selected_val	= 'selected';
			}

			$credentialTypes = getCredentialTypes($selectValue);

			$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}[]' id='select_{$entirefields_array[$i]}'>";
			$showFilter .= "<option value='ALL' ".$selected_val.">ALL</option>";
			
			
			if (!empty($credentialTypes)) 
			{
				foreach ($credentialTypes as $id => $name) 
				{
					if (!empty($sel_dept_id)) 
					{
						if (in_array($id, $sel_dept_id)) 
						{
							$selected_val	= 'selected';
						} else 
						{
							$selected_val	= '';
						}

						$showFilter	.= "<option value='".$id."'  ".$selected_val.'>'.$name.'</option>';

					} 
					else 
					{

						$showFilter	.= "<option value='".$id."'  ".$selected_val.'>'.$name.'</option>';
					}
				}
			}
			$showFilter .=  "</select>";
		}
		else if($entirefields_array[$i] == "credentialname") {
			$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';
			
			$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
			$selected_val	= '';
			$sel_cred_name_id	= array();

			if (isset($selectValue) && !empty($selectValue)) 
			{
				$sel_cred_name_id	= explode(',', $selectValue); //hold dept names
			}

			if (empty($sel_cred_name_id) || $sel_cred_name_id[0] == 'ALL') 
			{
				$selected_val	= 'selected';
			}

			$credentialNames = getCredentialNames($selectValue);

			$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}[]' id='select_{$entirefields_array[$i]}'>";
			$showFilter .= "<option value='ALL' ".$selected_val.">ALL</option>";
			if (!empty($credentialNames)) 
			{
				foreach ($credentialNames as $id => $name) 
				{
					if (!empty($sel_cred_name_id)) 
					{
						if (in_array($id, $sel_cred_name_id)) 
						{
							$selected_val	= 'selected';
						} else 
						{
							$selected_val	= '';
						}

						$showFilter	.= "<option value='".$id."'  ".$selected_val.'>'.$name.'</option>';

					} 
					else 
					{

						$showFilter	.= "<option value='".$id."'  ".$selected_val.'>'.$name.'</option>';
					}
				}
			}
			
			$showFilter .=  "</select>";
		}
		else if($entirefields_array[$i] == "credentialstatus") {
			$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

			$showFilter.="<select class=drpdwne style='width:165px; height:20px' name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
			$showFilter .=  "<option value='ALL'>ALL</option>";
			$showFilter .= "<option value='ACTIVE' ".sele("ACTIVE",$selectValue).">ACTIVE</option>";
			$showFilter .= "<option value='INACTIVE' ".sele("INACTIVE",$selectValue).">INACTIVE</option>";
			$showFilter .= "<option value='EXPIRED' ".sele("EXPIRED",$selectValue).">EXPIRED</option>";
			$showFilter .=  "</select>"; 
		}
		else if($entirefields_array[$i] == "asgnstatus") {
			$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
			$showFilter.="<select class=drpdwne style='width:165px; height:20px' name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
			$showFilter .=  "<option value='ALL'>ALL</option>";
			$showFilter .= "<option value='active' ".sele("active",$selectValue).">Active</option>";
			$showFilter .= "<option value='closed' ".sele("closed",$selectValue).">Closed</option>";
			$showFilter .= "<option value='cancel' ".sele("cancel",$selectValue).">Cancelled</option>";
			$showFilter .=  "</select>"; 
		}
		
		$showFilter .= "</td>";
		$showFilter .= "</tr>";

		echo $showFilter;

	}
	   
	$scriptStr .='});

		</script>';

	echo $scriptStr;				
}

/* function used to empty the array created in customize screen */
function cleanArray($array) 
{
	foreach ($array as $index => $value) 
	{
		if (is_array($array[$index])) 
		{
			$array[$index]	= cleanArray($array[$index]);
		}

		if (empty($value) && $value != 0) 
		{
			$array[$index]	= '';
		}
	}

	return $array;
}

	
function getDepartments($deptAccesSno) 
{

	global $db;
        
	$departments	= array();
        
	$sel_department_query	= "SELECT 
					d.sno, d.deptname 
				FROM 
					department d 
				WHERE 
                                                                                            d.sno !='0' AND d.sno IN ({$deptAccesSno}) AND 
					d.status='Active'
				ORDER BY 
					d.deptname";
	$res_department_query	= mysql_query($sel_department_query, $db);

	while ($rec = mysql_fetch_object($res_department_query)) 
	{

		$departments[$rec->sno]	= $rec->deptname;
	}

	return $departments;
}
	
function getCredentialTypes($argSel = " ")
{
	global $rptdb, $objCredentialsType;

	$creds_acttype_list	= $objCredentialsType->getListOfCredentialsType();
	return $creds_acttype_list;	
}
	
function getCredentialNames($argSel = " ")
{
	global $rptdb, $objCredentialsName;

	$creds_actname_list	= $objCredentialsName->getListOfCredentialsName();
	return $creds_actname_list;		
}

/* function used to get Current Date & Time in customize screen */
function getCurrentDateTime() 
{
	global $db;

	$sql	= "SELECT ".tzRetQueryStringDTime('NOW()','DateTimeSec','/');
	$res	= mysql_query($sql, $db);
	$row	= mysql_fetch_row($res);
	$ctime	= date("D m/d/Y h:i A", strtotime($row[0]));

	return $ctime; 
}

/* function used to display the fields in PDF and align the width of the fields */
function getTotalPDFFieldNames()
{
	return array(
			'Last Name|160px',
			'First Name|160px',
			'Middle Name|160px',
			'Empoyee Id|100px',
			'SSN|100px',
			'Credential Name|160px',
			'Credential Status|100px',
			'Expiration Date|150px',
			'On Assignment|100px',
			'Employee Status|100px',
			'Credential Acquired Data|150px'
			);
}

/* function used to convert report data into PDF when exporting */
function convert_to_pdf($input_array, $output_file_name, $get_tsdate_exp, $get_tsdate_exp, $rep_header, $header_array)
{
	global $companyuser, $rptdb;

	$rep_company		= $companyuser;
	$Maxlen			= array();
	
	require('mpdfnew/mpdf.php');
	$mpdf	= new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5);
	$mpdf->SetDisplayMode('fullpage');
	$mpdf->keep_table_proportions = true;
	
	$mpdf->WriteHTML('<style type="text/css">
	body { font-family:Helvetica;font-size:8pt;}
	
		.cls_002{font-size:16pt;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none;}
		.cls_004{color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none;}
		tr.cls_006{background: #CCCCCC; height:30px; font-weight:bold; }
		.cls_006 td{ font-weight:bold; /*padding-right:5px; */}
		tr.cls_007 td{ background: #DDDDDD;font-weight:bold;border-top:1px solid #000000;border-bottom:1px solid #000000;height:28px;}
		tr.cls_008 td{ font-weight:bold;border-top:1px solid #000000;border-bottom:1px solid #000000;padding-left:20px;height:28px;}	
		tr.cls_009 td{ height:25px;color:rgb(0,0,0);/*padding-left:35px;*/}
		tr.cls_011 td{ font-weight:bold;padding-left:45px;height:28px;}
		tr.cls_012 td{ background: #DDDDDD;font-weight:bold;border-top:1px solid #000000;padding-left:60px;height:28px;}
		tr.cls_013 td{ font-weight:bold;border-top:1px solid #000000;padding-left:60px;}
		tr.cls_010 td{ border-top:1px solid #000000;height:25px;color:rgb(0,0,0);font-weight:bold;}
	
		</style><body>');

	$mpdf->SetHTMLFooter('<table width="100%" style="vertical-align: middle; border-top:1px solid #000000;"><tr><td align="left"><span>page {PAGENO} of {nbpg}</span></td><td align="right">generated '.date("m/d/Y h:i:s A").' by '.$rep_company.'</td></tr></table>');
	
	$mpdf->WriteHTML('<table><tr><td width="100%" align="left" class="cls_002">'.$rep_header.'</td></tr><tr><td width="100%" align="left" class="cls_004"></td></tr></table><table border="0" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:11pt;"><tr class="cls_006">');

	$t		= 0;
	$rowwidtharray	= array();

	foreach($header_array as $headingVal)
	{
		if($t == 0)
		{
			$talign	= 'align="left" ';
		}
		else
		{
			$talign	= 'align="left"';
		}
		
		$hlineexp		= explode("|",$headingVal);
		$hline			= $hlineexp[0];
		$hlinewidth		= $hlineexp[1];
		$Maxlen[$t]		= strlen($hline)+2;
		$rowwidtharray[$t]	= $hlinewidth;

		$mpdf->WriteHTML('<td '.$talign.' style="width:'.$hlinewidth.';height:25px;">'.$hline.'</td>');
		$t++;
	}

	$mpdf->WriteHTML('</tr>');
	//$mpdf->WriteHTML('<tr><td>rahesg</td><td>'.implode('-',$input_array[9]).'</td></tr>');
	//Content
	
	foreach($input_array as $arr)
	{

		$k	= 0;
		//$len	= strlen($level2['empname'])+1;

		$mpdf->WriteHTML('<tr class="cls_009"><td align="left" style="width:'.$rowwidtharray[0].';">'.$arr['lastname'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[1].';">'.$arr['firstname'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[2].';">'.$arr['middlename'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[3].';">'.$arr['employeeid'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[4].';">'.$arr['ssn'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[5].';">'.$arr['credentialname'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[6].';">'.$arr['credentialstatus'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[7].';">'.$arr['expiredate'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[8].';">'.$arr['onassignment'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[8].';">'.$arr['employeestatus'].'&nbsp;</td><td align="left" style="width:'.$rowwidtharray[8].';">'.$arr['acquireddate'].'&nbsp;</td></tr>');


		$k++;

	}
	
	$mpdf->WriteHTML('<tr>');

	foreach($Maxlen[0] as $l => $lval)
	{
		$mpdf->WriteHTML('<td>'.str_repeat("&nbsp;",$lval+1).'</td>');					
	}

	$mpdf->WriteHTML('</tr></table></body>');
	$mpdf->Output($output_file_name, 'D');
}

function getEmployeeAssignments($empsno, $where_cond_as)
{
	global $rptdb;
	
	

	$query = "SELECT * FROM (SELECT 
			hr.pusername AS AssignmentID, 
			hr.project AS AssignmentName, 
			trim(staffacc_cinfo.cname) AS Company,
			hr.cdate
		FROM
			emp_list
			LEFT JOIN hrcon_jobs hr ON emp_list.username = hr.username
			LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = hr.client
		WHERE
			emp_list.lstatus != 'DA' AND			
			emp_list.sno = '".$empsno."'
			$where_cond_as
		GROUP BY hr.pusername
		ORDER BY hr.cdate DESC
		LIMIT 0,15) SUB
		ORDER BY SUB.cdate";
	$rs	= mysql_query($query,$rptdb);
	$empAssignmentsArr = array();
	while($row = mysql_fetch_array($rs))
	{
		$empAssignmentsArr[] = $row['AssignmentName']."(".$row['AssignmentID'].")-".$row['Company'];
	}
	return $empAssignmentsArr;
}
?>