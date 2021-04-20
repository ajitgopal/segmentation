<?php
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
			$arrEarType[$loop] =  htmlspecialchars($eartypes,ENT_QUOTES);
			$loop++;
		}
		return $arrEarType;
	}

	//-----function  to get the display names for each column which are used in getfilters-----//
	function getDisplayName($argFieldName)
	{
   		//$fieldnames = array("empBranchLocation"=>"HRM Location","empstatus" =>"Status","empCompanyCode" =>"Company Code","empdept" => "Department");
    	$fieldnames = array("empstatus" =>"Employee Status");		//"empWithholdTaxState"=>"Withholding Tax State", removed (duplicate) Piyush R

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
		if($argFilterName == "empBranchLocation")
		{
	   		$wordsArr = explode(",",trim($argFilterValue)); 
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
		$arrColumnName=array("empusername" => "username", "paytypeid" => "paytypeid","password" => "password", "empssn" => "ssn", "emplname" => "lastname", "empfname" => "firstname", "empmname" => "middlename", "empannualsalary" => "annualsalary", "empannualsalaryplus" => "annualsalaryplus", "emphourlysalary" => "hourly_salary", "empbonus" => "bonus", "empfacilityid" => "facilityid", "empdob" => "birth_date", "emphiredate" => "date_hire", "empbenefitdate" => "benefit_date", "emppaytypechangedate" => "paytype_changedate", "emptermdate" => "term_date", "empmaritalst" => "marital_status", "empgender" => "gender", "empaddr1" => "address1", "empaddr2" => "address2", "empcity" => "city", "empstate" => "state", "empzip" => "zipcode", "empPrimaryEmail" => "office_email", "empjobtitle" => "job_title", "empdept" => "department", "occupation" => "occupation", "empid" => "payrollcode");			

		return $arrColumnName[$argFieldName];
	}

	//Function For Getting Search Table Name Of Corresponding Columns..
	function getserTabName($argFieldName)
	{
		$arrTableName  = array("empusername" => "hrcon_personal", "paytypeid" => "hrcon_compen", "password" => "hrcon_personal", "empssn" => "hrcon_personal", "emplname" => "hrcon_general", "empfname" => "hrcon_general", "empmname" => "hrcon_general", "empannualsalary" => "hrcon_jobs", "empannualsalaryplus" => "", "emphourlysalary" => "hrcon_jobs", "empbonus" => "", "empfacilityid" => "", "empdob" => "hrcon_personal", "emphiredate" => "hrcon_compen", "empbenefitdate" => "hrcon_jobs", "emppaytypechangedate" => "", "emptermdate" => "", "empmaritalst" => "hrcon_personal", "empgender" => "hrcon_personal", "empaddr1" => "hrcon_general", "empaddr2" => "hrcon_general", "empcity" => "hrcon_general", "empstate" => "hrcon_general", "empzip" => "hrcon_general", "empPrimaryEmail" => "hrcon_general", "empjobtitle" => "hrcon_jobs", "empdept" => "hrcon_compen", "occupation" => "hrcon_jobs", "empid" => "emp_list");	

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
	function getFilters($argFilterNames,$argFilterValues) 
	{
		$chkExempt="";
		$entirefields_array =   array("empstatus");//"empWithholdTaxState", removed (duplicate) Piyush R
	  	$filternames_array = explode("^",$argFilterNames);
	  	$filtervalues_array = explode("^",dispTextdb($argFilterValues));		   

		$correspondingvalues_array = array_combine($filternames_array , $filtervalues_array);			
		$scriptStr = ' <script type="text/javascript">$(document).ready(function() {'; 

	   	for($i=0;$i<count($entirefields_array);$i++)
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
			if($entirefields_array[$i]=="empstatus")
			{ //Filter for Status
				 
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				if($selectValue==''){
					$defaultsel = 'selected';
				}else{
					$defaultsel = '';
				}
				
				$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
				$showFilter .=  "<option value='ALL'>ALL</option>";
				$showFilter .= "<option value='N' ".$defaultsel." ".sele("N",$selectValue).">Active</option>";
				$showFilter .= "<option value='Y' ".sele("Y",$selectValue).">Terminated</option>";
				$showFilter .=  "</select>";
			}
			else
			{ //Filter for  all remaining columns
				
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$str1 = getserTabName($entirefields_array[$i]);
				$str2 = getserColName($entirefields_array[$i]);
				$showFilter .= "<font class='afontstyle'><input type=text name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}' size=20  value='".$selectValue."'></font><a href=javascript:hrEmployeesWindow('$entirefields_array[$i]','".$str1."^".$str2."')><img class='remind-delete-align' src='/BSOS/images/crm/icon-srch.gif' width='17' height='16' alt='search' border='0' align='middle'></a>";
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

	//function to fetch the Employee Type(Class) from Manage table	
	function getOptions1($argFieldName,$argSelectValue = "",$deptAccesSno) {	
		global $rptdb,$username;

		if($argFieldName=="empdept")
		{
			$sqlcorp="SELECT sno,deptname FROM department WHERE sno !='0' AND sno IN ({$deptAccesSno})  AND status='Active' ORDER BY deptname  ";
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
	
	function getDepartmentList($argSel = " ")
	{
		global $rptdb;
		$option = " ";
		$selected = " ";

		$sql = "SELECT DISTINCT(deptname) AS colname FROM department where status='Active' ORDER BY colname ";
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
			$option .= "<option value='ALL'  >ALL</option>";
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

	//to get the latest assigment department name
	function getAssignmentDepartment($username)
	{
		global $maildb,$db;

		if($username)
		{

			$sel_ass 	= "select jotype from hrcon_jobs where username='".$username."' order by sno desc limit 0,1";
			$ass_res 	= mysql_query($sel_ass,$db);
            $jo_Data 	= mysql_fetch_row($ass_res);
            $jotype 		= $jo_Data[0];

            $manage_sql="select name from manage where sno='".$jotype."' and type = 'jotype'";
            $manage_res=mysql_query($manage_sql,$db);
            $manage_Data=mysql_fetch_row($manage_res);
		}
		return $manage_Data[0];
	}
?>