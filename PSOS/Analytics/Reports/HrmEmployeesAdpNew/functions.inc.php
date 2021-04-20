<?php 
/*
Modified Date : Sep 2, 2013.
Modified By  : Jayanthi
Purpose      : Functionality for new filter "timesheet date"
TS Task Id   : .
*/
	/*
	Modifed Date : Sep 05, 2009
	Modified By  : Prasadd
	Purpose      : HRM Location search query modified for assignment report,newemployee report,timesheet report.
	Task Id      : 4602.	

	TS ID: 4112   
	By: Swapna
	Date:March 12 2009
	Details: Added new column Email to the HRM new Employees Report.

	Modifed Date: April 06, 2009.
	Modified By: Kumar Raju K.
	Purpose: Added ethnicity and veteran status columns in new employee report.
	TS Task Id: (4204), (Prakash) In HRM - New Employee Report,need to add Ethnicity and veteran status.

	Modifed Date: April 13, 2009.
	Modified By: Fathima.
	Purpose: Modified code for proper displaying of veteran status.
	TS Task Id: (4204), (Prakash) In HRM - New Employee Report,need to add Ethnicity and veteran status.	

	Modifed Date: August 11, 2010.
	Modified By: Piyush R.
	Purpose: Added new columns.
	Task Id: 5255 - (Prakash) Need to add columns, Federal tax Allowance - State Tax Allowance - Filing Status - Withholding State - Pay check delivery method  , to existing reports New Employee, Timesheet and Assignments reports.
	*/

	require_once("global.inc");
	require("dispfunc.php");
	require("reportdatabase.inc");
	//function to benifit types
	// return type Array
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
    $fieldnames = array("empBranchLocation"=>"HRM Location","empstatus" =>"Status","empCompanyCode" =>"Company Code","empdept" => "Department", "chktsdate" => "Enable Date Filter", "timesheet_date" => "TimeSheet Date");
			$arrEarTypes = getAllEarTypes();
			//Pushing the display names of dynamic columns into $fieldnames array 
			if(count($arrEarTypes))
			{
				for($i=0;$i<count($arrEarTypes);$i++)
				{
					$var = $arrEarTypes[$i]."_tdollamt";
					$var1 = $arrEarTypes[$i]."_compcontr";
					$var2 = $arrEarTypes[$i]."_empcontr";
					$var3 = $arrEarTypes[$i]."_dollamtded";
					$arrEarTypes[$i] = str_replace('___',' ',$arrEarTypes[$i]);
					$fieldnames[$var] = str_replace('$$','',$arrEarTypes[$i])." Total Dollar Amt";
					$fieldnames[$var1] = str_replace('$$','',$arrEarTypes[$i])." Co. Contribution";
					$fieldnames[$var2] = str_replace('$$','',$arrEarTypes[$i])." Emp. Contribution";
					$fieldnames[$var3] = str_replace('$$','',$arrEarTypes[$i])." Amount Deducted";
				}
			}
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

	//----- function to get filter ids for HRM Location and FEIN # columns -----//

	function getStringFilterIds($argFilterName,$argFilterValue)

	{

		global $rptdb;

		$i = 0;

		/*if($argFilterName == "empBranchLocation")

		{

			$wordsArr = explode(",",trim($argFilterValue)); 

			$totalWordsCount = count($wordsArr); 

			if($totalWordsCount == 3 && (trim($wordsArr[$totalWordsCount-1]) !=""))

			{

				$wordsArr[$totalWordsCount-1] = getCountryCode($wordsArr[$totalWordsCount-1]);

			}

			$argFilterValue = implode(",",$wordsArr);

			$sql = "SELECT serial_no AS filterid FROM contact_manage WHERE status !='BP' AND (CONCAT(UPPER(city),',',UPPER(state),',',UPPER(country)) like '%".strtoupper($argFilterValue)."%')";

		}*/

		if($argFilterName == "empBranchLocation")

		{

	   		$wordsArr = explode(",",trim($argFilterValue)); 

			/*$totalWordsCount = count($wordsArr); 

			if($totalWordsCount == 3 && (trim($wordsArr[$totalWordsCount-1]) !=""))

				$wordsArr[$totalWordsCount-1] = getCountryCode($wordsArr[$totalWordsCount-1]);*/

			$argFilterValue = implode(",",$wordsArr);

			$sql = "SELECT serial_no as filterid FROM contact_manage WHERE status != 'BP' AND concat_ws(',',upper(heading),upper(city),upper(state))

			like '%".strtoupper($argFilterValue)."%' ";

		}

		else if($argFilterName == "empFein")

		{

	   		$sql = "SELECT serial_no AS filterid FROM contact_manage WHERE status != 'BP' AND feid

			LIKE '%".$argFilterValue."%' ";

		}

		else if(($argFilterName == "empCreateduser") || ($argFilterName == "empMuser") )

			 $sql = "select username as filterid from users where name like '%{$argFilterValue}%'";

		else if(($argFilterName == "empEthnicity"))

			 $sql = "SELECT sno AS filterid FROM manage WHERE name LIKE '%{$argFilterValue}%' AND type='hrethnicity'";
			 
			 
	  

		 

		$rs =  mysql_query($sql,$rptdb);

		while($row = mysql_fetch_array($rs))

		{

			$arrIds[$i] = $row['filterid'];

			$i++;

		}

		return $arrIds;

	}

		

	//---Function for getting HRM Location---------//

	function getBranchLocationval($argLocationId)

	{

		global $rptdb;

	    $qryLocation = "SELECT heading FROM contact_manage WHERE serial_no='".$argLocationId."'";

		$rsLocation = mysql_query($qryLocation,$rptdb);

		$rowLocation = mysql_fetch_assoc($rsLocation);

		$string = "";

		if($rowLocation["heading"] != "")

			$string .= $rowLocation["heading"];

		$string;

		
		return $string;

		

	} 
	

	//---Function For Getting Search Column Name of corresponding fields------//

	function getserColName($argFieldName)

	{

		$arrColumnName  = array("empid" =>"sno","empssn"=>"ssn","empphone"=>"wphone","empfname"=>"fname","empmname"=>"mname","emplname"=>"lname","empaddr1"=>"address1","empaddr2"=>"address2","empcity"=>"city","empstate"=>"state","empzip"=>"zip","empBranchLocation"=>"","empFedEmployee"=>"fwh","empFedCompany"=>"cfwh","empStateEmployee"=>"swh","empStateCompany"=>"cswh","empfedtaxemployee"=>"aftaw","empfedtaxcompany"=>"caftaw","empstatetaxemployee"=>"astaw","empstatetaxcompany"=>"castaw","empsecurityemployee"=>"sswh","empsecuritycompany"=>"csswh","empmedicareemployee"=>"mwh","empmedicarecompany"=>"cmwh","emplocalwithhold1employee"=>"localw1_amt","emplocalwithhold1company"=>"clocalw1_amt","emplw2employee"=>"localw2_amt","emplw2company"=>"clocalw2_amt","empnoallowclaim"=>"tnum*federal_exempt","empstatetaxallowance"=>"tstatetax*state_exempt","emppayproviderid"=>"payrollpid","empFein" => "feid","empAbaAcc1"=>"bankrtno","empAccnumberAcc1"=>"bankacno","empBanknameAcc1"=>"bankname","empAbaAcc2"=>"acc2_bankrtno","empAccnumberAcc2"=>"acc2_bankacno","empBanknameAcc2"=>"acc2_bankname","empDoublePayrate"=>"double_prate_amt","empdob"=>"d_birth","emphiredate"=>"date_hire","empmaritalst" => "m_status","empltax"=>"tax","empgender"=>"hp_gender","empClass"=>"emptype","empAccTypeAcc1"=>"acc1_type","empAccTypeAcc2"=>"acc2_type","empOvertimeRate"=>"double_brate_amt","empsalary"=>"","empStatus"=>"empterminated","filingStatus"=>"fstatus","empMdate"=>"mtime","empCreateduser" => "name","empCrateddate" => "stime","empMuser" => "name","pdlodging"=>"diem_lodging","pdmie"=>"diem_mie","pdtotal"=>"diem_total","pdbillable"=>"diem_billable","pdtaxable"=>"diem_taxable","empEmail"=>"email","empEthnicity"=>"ethnicity","empVeterans"=>"veteran_status","empWithholdTaxState"=>"state_withholding","empTerminatedDate"=>"tdate","empReHireDate"=>"emp_rehire_date","empModifiedDate"=>"udate","empMultipleJobs"=>"multijobs_spouseworks","empDependents"=>"claim_dependents_total","empOtherIncome"=>"other_income_amt","empDeductions"=>"deduction_amt","empExtraWithholding"=>"aftaw","empIsNewOldW4"=>"multijobs_spouseworks");

				

		return $arrColumnName[$argFieldName];

		//"empWithholdTaxState"=>"state", Removed duplicate Piyush R

	}

	//Function For Getting Search Table Name Of Corresponding Columns..

	function getserTabName($argFieldName)

	{

		$arrTableName  = array("empid" =>"hrcon_compen","empssn"=>"hrcon_personal","empphone"=>"hrcon_general","empfname"=>"hrcon_general","empmname"=>"hrcon_general","emplname"=>"hrcon_general","empaddr1"=>"hrcon_general","empaddr2"=>"hrcon_general","empcity"=>"hrcon_general","empstate"=>"hrcon_general","empzip"=>"hrcon_general","empBranchLocation"=>"contact_manage","emppayproviderid"=>"hrcon_w4","empFein" => "contact_manage","empAbaAcc1"=>"hrcon_deposit","empAccnumberAcc1"=>"hrcon_deposit","empBanknameAcc1"=>"hrcon_deposit","empAbaAcc2"=>"hrcon_deposit","empAccnumberAcc2"=>"hrcon_deposit","empBanknameAcc2"=>"hrcon_deposit","empdob"=>"hrcon_personal","emphiredate"=>"hrcon_compen","empmaritalst" => "hrcon_personal","empltax"=>"hrcon_w4","empgender"=>"hrcon_personal","empClass"=>"hrcon_compen","empAccTypeAcc1"=>"hrcon_deposit","empAccTypeAcc2"=>"hrcon_deposit","empOvertimeRate"=>"hrcon_jobs","empsalary"=>"","empnoallowclaim"=>"hrcon_w4","empstatetaxallowance"=>"hrcon_w4","empStatus"=>"emp_list","filingStatus"=>"hrcon_w4","empMdate"=>"emp_list","empCreateduser" => "users","empCrateddate" => "emp_list","empMuser" => "users","pdlodging"=>"hrcon_compen","pdmie"=>"hrcon_compen","pdtotal"=>"hrcon_compen","pdbillable"=>"hrcon_compen","pdtaxable"=>"hrcon_compen","empEmail"=>"hrcon_general","empEthnicity"=>"hrcon_personal","empVeterans"=>"hrcon_personal","paychkdelmethod"=>"hrcon_deposit","empWithholdTaxState"=>"hrcon_w4","empTerminatedDate"=>"emp_list","empReHireDate"=>"hrcon_compen","empModifiedDate"=>"hrcon_compen","empMultipleJobs"=>"hrcon_w4","empDependents"=>"hrcon_w4","empOtherIncome"=>"hrcon_w4","empDeductions"=>"hrcon_w4","empExtraWithholding"=>"hrcon_w4");

		//"empWithholdTaxState"=>"hrcon_general",Removed duplicate Piyush R		

		return $arrTableName[$argFieldName];

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

	function getFilters($argFilterNames,$argFilterValues,$deptAccesSno) 

	{
			$chkExempt="";

			$entirefields_array =   array("empBranchLocation","empCompanyCode","empstatus","empdept","chktsdate","timesheet_date");

		   $filternames_array = explode("^",$argFilterNames);

		   $filtervalues_array = explode("^",dispTextdb($argFilterValues));

			$correspondingvalues_array = array_combine($filternames_array , $filtervalues_array);
			
			$scriptStr = ' <script type="text/javascript">

							$(document).ready(function() {';
//echo "$argFilterNames<br /><br />";
//echo "$argFilterValues<br /><br />";
//print_r($entirefields_array);
//echo "<br />lllllllll<br />";
//print_r($correspondingvalues_array);
		   for($i=0;$i<count($entirefields_array);$i++)

		   { 

				$row_id = "filter_".$entirefields_array[$i];
				$style_filter = (in_array((string)$entirefields_array[$i], $filternames_array)) ? '' : "style='display:none'";

				if($entirefields_array[$i] == "timesheet_date")  {
					if($correspondingvalues_array["chktsdate"] != "false") $showFilter = "<tr id='{$row_id}' >\n";

					 else $showFilter = "<tr id='{$row_id}' style='display:none' >\n";
				}
				 else $showFilter = "<tr id='{$row_id}' {$style_filter}>\n";

				$showFilter .= "\t<td width=3%>&nbsp;</td>\n";


				if($entirefields_array[$i] == "chktsdate") {

					if($correspondingvalues_array[$entirefields_array[$i]] == "true") $sChkDateChecked = "checked";
					else if($correspondingvalues_array[$entirefields_array[$i]] == "false") $sChkDateChecked = "";
					else $sChkDateChecked = "checked";

					$showFilter .= "\t<td width=40%><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."&nbsp;&nbsp;&nbsp;<input type='checkbox' id='select_chktsdate' name='select_chktsdate' onclick=\"toggleDateFilter('filter_timesheet_date');\" $sChkDateChecked /></font></td>\n";
					$showFilter .= "\t<td align=left width=60%>\n";
				}
				else {			
					$showFilter .= "\t<td width=40%><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>\n";
					$showFilter .= "\t<td align=left width=60%>\n";
				}

				 

			 //Filter for Date Columns

			 if(($entirefields_array[$i] == "empdob") ||($entirefields_array[$i] == "emphiredate") || ($entirefields_array[$i] == "empMdate") || ($entirefields_array[$i] == "empCrateddate"))

			 {

			  $maxname = "max_".$entirefields_array[$i];

			  $minname = "min_".$entirefields_array[$i];

			  $ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);

			  $maxvalue = $ranges[1];

			  $minvalue = $ranges[0];

			  $showFilter .=  "<font class='afontstyle'>

					From : <input name='".$minname."' value='".$minvalue."' size='8' type='text'  id='".$minname."' readonly>								

					<a href=javascript:selectDate('filter','".$minname."')>

					<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 alt='calendar'></a>

					<a href=javascript:resetStartDate('".$minname."')>

					<i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;

					To : <input name='".$maxname."' value='".$maxvalue."' size='8' type='text' id='".$maxname."' readonly>

					<a href=javascript:selectDate('filter','".$maxname."')>

					<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 alt='calendar'></a>

					</font><a href=javascript:resetStartDate('".$maxname."')>

					<i alt='Reset' class='fa fa-reply'></i></a>";

					// resetStartDate() => HrmEmployees/scripts/link.js

			 }

				

				else if( ($entirefields_array[$i] == "empFedEmployee") || ($entirefields_array[$i] == "empStateEmployee") || ($entirefields_array[$i] == "empStateCompany") || ($entirefields_array[$i] == "empFedCompany")|| ($entirefields_array[$i] == "empfedtaxemployee")|| ($entirefields_array[$i] == "empfedtaxcompany")|| ($entirefields_array[$i] == "empstatetaxemployee")|| ($entirefields_array[$i] == "empstatetaxcompany")|| ($entirefields_array[$i] == "empsecurityemployee")|| ($entirefields_array[$i] == "empsecuritycompany")|| ($entirefields_array[$i] == "empmedicareemployee")|| ($entirefields_array[$i] == "empmedicarecompany")|| ($entirefields_array[$i] == "emplocalwithhold1employee")|| ($entirefields_array[$i] == "emplocalwithhold1company")|| ($entirefields_array[$i] == "emplw2employee")|| ($entirefields_array[$i] == "emplw2company") || ($entirefields_array[$i] == "empDoublePayrate")|| ($entirefields_array[$i] == "empOvertimeRate")|| $entirefields_array[$i] == "empid" || $entirefields_array[$i] == "empsalary" || $entirefields_array[$i] == "pdlodging" || $entirefields_array[$i] == "pdmie" || $entirefields_array[$i] == "pdtotal" || (preg_match("/_tdollamt/i", $entirefields_array[$i])) || (preg_match("/_compcontr/i", $entirefields_array[$i])) || (preg_match("/_empcontr/i", $entirefields_array[$i])) || (preg_match("/_dollamtded/i", $entirefields_array[$i]))) //Filter for number value columns

				{

				  $maxname = "max_".$entirefields_array[$i];

				  $minname = "min_".$entirefields_array[$i];

				  $ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);

				  $maxvalue = $ranges[1];

				  $minvalue = $ranges[0];

				  $showFilter .= "<font class='afontstyle'>Min : <input name='".$minname."'  id='".$minname."' value='".$minvalue."' size='3' type='text'                     >&nbsp;&nbsp;&nbsp;&nbsp;Max : <input name='".$maxname."' id='".$maxname."' value='".$maxvalue."' size='3'                     type='text'></font>";

				}

				else if(($entirefields_array[$i] == "empnoallowclaim")|| ($entirefields_array[$i] == "empstatetaxallowance"))

				{

					$maxname = "max_".$entirefields_array[$i];

					$minname = "min_".$entirefields_array[$i];

					

					$chkExempt= "Exempt_$entirefields_array[$i]";

					

					$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

					if($selectValue!="" && $selectValue=="Exempt_$entirefields_array[$i]")

					{

						$$chkExempt="checked";

					}

					elseif($selectValue!="")

					{

						$ranges = explode("*",$selectValue);

						$maxvalue = $ranges[1];

						$minvalue = $ranges[0];

					}

					else

						$$chkExempt="";

						

					if($$chkExempt == "checked")

						$disable = " disabled='disabled'";

					else

						$disable = " ";

						

					$showFilter .= "<font class='afontstyle'>Min : <input name='".$minname."' id='".$minname."' value='".$minvalue."' size='3' {$disable} type='text'                     >&nbsp;&nbsp;&nbsp;&nbsp;Max : <input name='".$maxname."' id='".$maxname."' value='".$maxvalue."' size='3' {$disable} type='text'></font>";

					

					 $showFilter .= " &nbsp;&nbsp;<input type='checkbox' name='{$entirefields_array[$i]}' id='fedstate_$entirefields_array[$i]' value='Exempt_$entirefields_array[$i]' {$$chkExempt} onClick='javascript:disableExemptTextbox($entirefields_array[$i],$minname,$maxname);' ><font class=afontstyle>Exempt</font>&nbsp;&nbsp";   

				}

				else if($entirefields_array[$i] == "empltax") //Filter For Tax

				{

					$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

					$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

					$showFilter .=  "<option value='ALL'>ALL</option>";

					$showFilter .= "<option value='W-2' ".sele("W-2",$selectValue).">W-2</option>";

					$showFilter .= "<option value='1099' ".sele("1099",$selectValue).">1099</option>";

					$showFilter .= "<option value='C-to-C' ".sele("C-to-C",$selectValue).">C-to-C</option>";

					$showFilter .= "<option value='None' ".sele("None",$selectValue).">None</option>";

					$showFilter .=  "</select>";

				}

				else if($entirefields_array[$i] == "empmaritalst") //Filter for MArital status

				{

					$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

					$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

					$showFilter .=  "<option value='ALL'>ALL</option>";

					$showFilter .= "<option value='single' ".sele("single",$selectValue).">Single</option>";

					$showFilter .= "<option value='married' ".sele("married",$selectValue).">Married</option>";

					$showFilter .=  "</select>";

				}

				  else if($entirefields_array[$i] == "empClass") //Filter For Class

					{

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

						$options = getOptions($entirefields_array[$i],$selectValue);

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .=$options;

						$showFilter .=  "</select>";

					}		

				  else if($entirefields_array[$i]=="empgender") //Filter For Gender

					{

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .= "<option value='M' ".sele("M",$selectValue).">Male</option>";

						$showFilter .= "<option value='F' ".sele("F",$selectValue).">Female</option>";

						$showFilter .=  "</select>";        

				   

					}

				 else if($entirefields_array[$i]=="empAccTypeAcc1" || $entirefields_array[$i]=="empAccTypeAcc2")

					{ //Filter for  Bank Account types

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .= "<option value='CHECKING' ".sele("CHECKING",$selectValue).">Chk</option>";

						$showFilter .= "<option value='SAVINGS' ".sele("SAVINGS",$selectValue).">Sav</option>";

						$showFilter .=  "</select>";        

				   

					}
					
					elseif($entirefields_array[$i] == "empBranchLocation")

			{

				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';

				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$options = getBranchLocation($selectValue);

				$showFilter.="<select class=drpdwne  multiple='multiple' style='width:150px; height:50px'  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";

				#$trFilter .=  "<option value=''>ALL</option>";

				$showFilter .=  $options;

				$showFilter .=  "</select>";

		    }

				 else if($entirefields_array[$i]=="empstatus")

					{ //Filter for Status

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

						/*if($selectValue=='')

				  			$selectValue='N';*/

					

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .= "<option value='N' ".sele("N",$selectValue).">Active</option>";

						$showFilter .= "<option value='Y' ".sele("Y",$selectValue).">Terminated</option>";

						$showFilter .=  "</select>";        

				   

					}
					
					elseif($entirefields_array[$i] == "empCompanyCode")

			{

				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';

				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$options = getCompanycodeList($selectValue);

				$showFilter.="<select class=drpdwne  multiple='multiple' style='width:150px; height:50px'  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";

				#$trFilter .=  "<option value=''>ALL</option>";

				$showFilter .=  $options;

				$showFilter .=  "</select>";

		    }
			
				elseif($entirefields_array[$i] == "empdept")

			{

				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';

				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$options = getDepartmentList($selectValue,$deptAccesSno);

				$showFilter.="<select class=drpdwne  multiple='multiple' style='width:150px; height:50px'  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";

				#$trFilter .=  "<option value=''>ALL</option>";

				$showFilter .=  $options;

				$showFilter .=  "</select>";

		    }


				else if($entirefields_array[$i]=="filingStatus")

					{ //Filter for Status

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

					

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .= "<option value='Single' ".sele("Single",$selectValue).">Single</option>";

						$showFilter .= "<option value='Married' ".sele("Married",$selectValue).">Married</option>";

						$showFilter .= "<option value='Married, but w/h at higher single rate' ".sele("Married, but w/h at higher single rate",$selectValue).">Married, but w/h at higher single rate</option>";

						$showFilter .= "<option value='Head of Household' ".sele("Head of Household",$selectValue).">Head of Household</option>";

						$showFilter .=  "</select>";        

				   

					}

				else if($entirefields_array[$i]=="pdbillable")

					{ //Filter for Per Diem's Billable Field

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

					

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .= "<option value='Y' ".sele("Y",$selectValue).">Billable</option>";

						$showFilter .= "<option value='N' ".sele("N",$selectValue).">Non-Billable</option>";

						$showFilter .=  "</select>";        

				   

					}

				else if($entirefields_array[$i]=="pdtaxable")

					{ //Filter for Per Diem's Taxable Field

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

					

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .= "<option value='Y' ".sele("Y",$selectValue).">Taxable</option>";

						$showFilter .= "<option value='N' ".sele("N",$selectValue).">Non-Taxable</option>";

						$showFilter .=  "</select>";        

				   

					}

				else if($entirefields_array[$i]=="empVeterans")

					{

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

						$options = getOptions($entirefields_array[$i],$selectValue);

						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

						$showFilter .=  "<option value='ALL'>ALL</option>";

						$showFilter .=$options;

						$showFilter .=  "</select>";        

				   

					}



              // CONDITION FOR ENABLE DATE FILTER CHECKBOX
				else if($entirefields_array[$i] == "chktsdate") {

				     $showFilter .= "<font class='afontstyle'>Note : This filter lists only the employees that have approved timesheets for the below date range.</font>";
				}


			   // CONDITION FOR TIMESHEET DATES FILTER
			   else if($entirefields_array[$i] == "timesheet_date") {

					$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				
				    $aDate = explode("*", $selectValue);

					$sStartDateName = "min_timesheet_date";

					$sEndDateName = "max_timesheet_date";

					$sStartDate = $aDate[0];

					$sEndDate = $aDate[1];

					if($sStartDate == "" && $sEndDate == "") {

						$aDateRange = getStartEndDatesBasedOnWeekendDay('Reports');    // FUNCTION IS IN global_fun.inc

						$sStartDate = $aDateRange['StartDate'];

						$sEndDate = $aDateRange['EndDate'];
					}

					$showFilter .=  "<font class='afontstyle'>";

					$showFilter .=  "From : &nbsp;&nbsp; <input name='".$sStartDateName."' value='".$sStartDate."' size='8' type='text'  id ='".$sStartDateName."' >";
					
					$showFilter .=  "<script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$sStartDateName."'});</script>";

					$showFilter .=  "<a href=javascript:emptyField('$sStartDateName')>";

					$showFilter .=  "<i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

					$showFilter .=  "To : &nbsp;&nbsp; <input name='".$sEndDateName."' value='".$sEndDate."' size='8' type='text' id ='".$sEndDateName."' >";

					$showFilter .=  "<script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$sEndDateName."'});</script>";

					$showFilter .=  "</font><a href=javascript:emptyField('$sEndDateName')>";

					$showFilter .=  "<i alt='Reset' class='fa fa-reply'></i></a>";
					
			   }



				 else

				  { //Filter for  all remaining columns	

				   $selectValue = $correspondingvalues_array[$entirefields_array[$i]];	 

				   

					$str1 = getserTabName($entirefields_array[$i]);

					$str2 = getserColName($entirefields_array[$i]);	

				   

				   	$showFilter .= "<font class='afontstyle'><input type=text name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}' size=20  value='".$selectValue."'></font>

				   <a href=javascript:hrEmployeesWindow('$entirefields_array[$i]','".$str1."^".$str2."')>

						<img class='remind-delete-align' src='/BSOS/images/crm/icon-srch.gif' width='17' height='16' alt='search' 

						border='0' align='middle'></a>";

						// hrEmployeesWindow() => HrmEmployees/scripts/link.js

				  }

				 

				 $showFilter .= "</td>";

				 $showFilter .= "</tr>";

				echo $showFilter;

		   }
		   
		   $scriptStr .='});

						</script>';

		

		echo $scriptStr;				


	}



	//function to fetch the Employee Type(Class) from MAnage table
	
	function getOptions1($argFieldName,$argSelectValue = "") {
	
	global $rptdb;
	
	if($argFieldName=="empdept")

	{

		$sqlcorp="SELECT sno,deptname FROM department WHERE  status='Active' ORDER BY deptname  ";

		$rescorp=mysql_query($sqlcorp,$rptdb);		

		while($ccode=mysql_fetch_row($rescorp))

		{

			$options  .= "<option value='$ccode[0]' ".sele($argSelectValue,$ccode[0]).">$ccode[1]</option>";

		}

		

		return $options;

	}
	
	
	
	
	
	}
	

	function getOptions($argFieldName,$argSelectValue = "")

	{

		global $rptdb;
		
		
		
		
		
		

		if($argFieldName == 'empVeterans')

		{

			$sqlEmptype="SELECT sno,name FROM manage WHERE type='hrveteran'";

		}

		else

		{

			$sqlEmptype="select sno,name from manage where type='jotype' and name 

			in('Temp/Contract','Internal Temp/Contract','Internal Direct')  ";

		}

		$resEmptype=mysql_query($sqlEmptype,$rptdb);

		while($empType=mysql_fetch_row($resEmptype))

		{

			$sel_emptype=$argSelectValue;

			$empType[1]=ucfirst($empType[1]);

			$options .= "<option value='$empType[0]' ".sele($empType[0],$sel_emptype).">$empType[1]</option>";

		}

		return $options;

	}
	
	function getCompanycodeList($argSel = " ")

	{

		global $rptdb;

		$option = " ";

		$selected = " ";

		$sql = "SELECT DISTINCT(empcon_w4.companycode) AS colname FROM empcon_w4 ORDER BY colname ";

		$rs =  mysql_query($sql,$rptdb);

		

		if(!$argSel || ($argSel == 'ALL') || (strpos($argSel,'!#!')>0) && (in_array('ALL',explode('!#!',$argSel))) ){

			$option .= "<option value='ALL' selected>ALL</option>";

		}else{

			$option .= "<option value='ALL'  >ALL</option>" ;

		}

		

		while($row = mysql_fetch_array($rs))

		{

			if(!$argSel || ($argSel == $row['colname']) || ((strpos($argSel,'!#!')>0) && in_array($row['colname'],explode('!#!',$argSel))) )

				$selected = "selected";

			else

				$selected = " ";

		 

		 	if(trim($row['colname'])!='')

			$option .= "<option value='{$row[colname]}' {$selected}>{$row[colname]}</option>" ;

		}

		return $option;

	}
	
	function getDepartmentList($argSel = " ",$deptAccesSno)

	{

		global $rptdb,$username;

		$option = " ";

		$selected = " ";

		$sql = "SELECT DISTINCT(deptname) AS colname FROM department WHERE sno !='0' AND sno IN ({$deptAccesSno})  AND status='Active' ORDER BY colname ";

		$rs =  mysql_query($sql,$rptdb);

		

		if(!$argSel || ($argSel == 'ALL') || (strpos($argSel,'!#!')>0) && (in_array('ALL',explode('!#!',$argSel))) ){

			$option .= "<option value='ALL' selected>ALL</option>";

		}else{

			$option .= "<option value='ALL'  >ALL</option>" ;

		}

		

		while($row = mysql_fetch_array($rs))

		{

			if(!$argSel || ($argSel == $row['colname']) || ((strpos($argSel,'!#!')>0) && in_array($row['colname'],explode('!#!',$argSel))) )

				$selected = "selected";

			else

				$selected = " ";

		 

		 	if(trim($row['colname'])!='')

			$option .= "<option value='{$row[colname]}' {$selected}>{$row[colname]}</option>" ;

		}

		return $option;

	}
	
	
	function getBranchLocation($argSel = " ")

	{

		global $rptdb, $locationStatus;

		$option = " ";

		$selected = " ";
		
		$whereclause = ($locationStatus == "InActive") ? "status='BP'" : "status!='BP'";
		$whereclause = " WHERE ".$whereclause;

		$sql = "SELECT DISTINCT(heading) AS colname, loccode FROM contact_manage ".$whereclause." ORDER BY colname ";

		$rs =  mysql_query($sql,$rptdb);

		

		if(!$argSel || ($argSel == 'ALL') || (strpos($argSel,'!#!')>0) && (in_array('ALL',explode('!#!',$argSel))) ){

			$option .= "<option value='ALL' selected>ALL</option>";

		}else{

			$option .= "<option value='ALL'  >ALL</option>" ;

		}

		

		while($row = mysql_fetch_array($rs))

		{

			if(!$argSel || ($argSel == $row['loccode']) || ((strpos($argSel,'!#!')>0) && in_array($row['loccode'],explode('!#!',$argSel))) )

				$selected = "selected";

			else

				$selected = " ";

		 

		 	if(trim($row['colname'])!='')

			$option .= "<option value='{$row[loccode]}' {$selected}>{$row[colname]}({$row[loccode]})</option>" ;

		}

		return $option;

	}

	function getFederalid($argLocationId)
	{
	   global $rptdb;
	   $sql = "SELECT feid FROM contact_manage WHERE serial_no='".$argLocationId."'";
	   $rs = mysql_query($sql,$rptdb);
	   $row = mysql_fetch_array($rs);
	   return $row[0];
	}
?>