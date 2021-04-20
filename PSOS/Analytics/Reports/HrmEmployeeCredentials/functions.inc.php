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
		$fieldnames = array("empBranchLocation"=>"HRM Location","empstatus" =>"Status","empCompanyCode" =>"Company Code","empdept" => "Department","credentialtype" => "Credential Type","credentialname" => "Credential Name","credentialcountry" => "Country","credentialstates" => "Valid State","credentialvfromto" => "Credential Expiration Date","credentialstatus" => "Credential Status","credacquiredfromto" => "Credential Acquired Date");

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

			$sql = "SELECT serial_no as filterid FROM contact_manage WHERE status != 'BP' AND concat_ws(',',upper(heading),upper(city),upper(state)) like '%".strtoupper($argFilterValue)."%' ";
		}

		else if($argFilterName == "empFein")
		{
	   		$sql = "SELECT serial_no AS filterid FROM contact_manage WHERE status != 'BP' AND feid LIKE '%".$argFilterValue."%' ";
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
		
		return $string;
	} 
	

	//---Function For Getting Search Column Name of corresponding fields------//
	function getserColName($argFieldName)
	{
		$arrColumnName  = array("empid" =>"sno","empssn"=>"ssn","empphone"=>"wphone","empfname"=>"fname","empmname"=>"mname","emplname"=>"lname","empaddr1"=>"address1","empaddr2"=>"address2","empcity"=>"city","empstate"=>"state","empzip"=>"zip","empBranchLocation"=>"","empFedEmployee"=>"fwh","empFedCompany"=>"cfwh","empStateEmployee"=>"swh","empStateCompany"=>"cswh","empfedtaxemployee"=>"aftaw","empfedtaxcompany"=>"caftaw","empstatetaxemployee"=>"astaw","empstatetaxcompany"=>"castaw","empsecurityemployee"=>"sswh","empsecuritycompany"=>"csswh","empmedicareemployee"=>"mwh","empmedicarecompany"=>"cmwh","emplocalwithhold1employee"=>"localw1_amt","emplocalwithhold1company"=>"clocalw1_amt","emplw2employee"=>"localw2_amt","emplw2company"=>"clocalw2_amt","empnoallowclaim"=>"tnum*federal_exempt","empstatetaxallowance"=>"tstatetax*state_exempt","emppayproviderid"=>"payrollpid","empFein" => "feid","empAbaAcc1"=>"bankrtno","empAccnumberAcc1"=>"bankacno","empBanknameAcc1"=>"bankname","empAbaAcc2"=>"acc2_bankrtno","empAccnumberAcc2"=>"acc2_bankacno","empBanknameAcc2"=>"acc2_bankname","empDoublePayrate"=>"double_prate_amt","empdob"=>"d_birth","emphiredate"=>"date_hire","empmaritalst" => "m_status","empltax"=>"tax","empgender"=>"hp_gender","empClass"=>"emptype","empAccTypeAcc1"=>"acc1_type","empAccTypeAcc2"=>"acc2_type","empOvertimeRate"=>"double_brate_amt","empsalary"=>"","empStatus"=>"empterminated","filingStatus"=>"fstatus","empMdate"=>"mtime","empCreateduser" => "name","empCrateddate" => "stime","empMuser" => "name","pdlodging"=>"diem_lodging","pdmie"=>"diem_mie","pdtotal"=>"diem_total","pdbillable"=>"diem_billable","pdtaxable"=>"diem_taxable","empEmail"=>"email","empEthnicity"=>"ethnicity","empVeterans"=>"veteran_status","empWithholdTaxState"=>"state_withholding");

		return $arrColumnName[$argFieldName];
	}

	//Function For Getting Search Table Name Of Corresponding Columns..
	function getserTabName($argFieldName)
	{
		$arrTableName  = array("empid" =>"hrcon_compen","empssn"=>"hrcon_personal","empphone"=>"hrcon_general","empfname"=>"hrcon_general","empmname"=>"hrcon_general","emplname"=>"hrcon_general","empaddr1"=>"hrcon_general","empaddr2"=>"hrcon_general","empcity"=>"hrcon_general","empstate"=>"hrcon_general","empzip"=>"hrcon_general","empBranchLocation"=>"contact_manage","emppayproviderid"=>"hrcon_w4","empFein" => "contact_manage","empAbaAcc1"=>"hrcon_deposit","empAccnumberAcc1"=>"hrcon_deposit","empBanknameAcc1"=>"hrcon_deposit","empAbaAcc2"=>"hrcon_deposit","empAccnumberAcc2"=>"hrcon_deposit","empBanknameAcc2"=>"hrcon_deposit","empdob"=>"hrcon_personal","emphiredate"=>"hrcon_compen","empmaritalst" => "hrcon_personal","empltax"=>"hrcon_w4","empgender"=>"hrcon_personal","empClass"=>"hrcon_compen","empAccTypeAcc1"=>"hrcon_deposit","empAccTypeAcc2"=>"hrcon_deposit","empOvertimeRate"=>"hrcon_jobs","empsalary"=>"","empnoallowclaim"=>"hrcon_w4","empstatetaxallowance"=>"hrcon_w4","empStatus"=>"emp_list","filingStatus"=>"hrcon_w4","empMdate"=>"emp_list","empCreateduser" => "users","empCrateddate" => "emp_list","empMuser" => "users","pdlodging"=>"hrcon_compen","pdmie"=>"hrcon_compen","pdtotal"=>"hrcon_compen","pdbillable"=>"hrcon_compen","pdtaxable"=>"hrcon_compen","empEmail"=>"hrcon_general","empEthnicity"=>"hrcon_personal","empVeterans"=>"hrcon_personal","paychkdelmethod"=>"hrcon_deposit","empWithholdTaxState"=>"hrcon_w4");		

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
		global $objCredentialsJson, $countries;
		
		$chkExempt="";

		$entirefields_array = array("empBranchLocation","empCompanyCode","empstatus","empdept","credentialtype","credentialname","credentialcountry","credentialstates","credentialvfromto","credentialstatus","credacquiredfromto");
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
				
				$maxvalue = $ranges[1];
				$minvalue = $ranges[0];
				
				$showFilter .=  "<font class='afontstyle'>
	
					       From : <input name='".$minname."' value='".$minvalue."' size='8' type='text' id='".$minname."'>								
					       <script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$minname."'});</script>
					      <a href=javascript:resetStartDate('".$minname."')>
					       <i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;
	
					       To : <input name='".$maxname."' value='".$maxvalue."' size='8' type='text' id='".$maxname."'>
					       <script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$maxname."'});</script>
					       <a href=javascript:resetStartDate('".$maxname."')>
					       <i alt='Reset' class='fa fa-reply'></i></a>";
			}else if(($entirefields_array[$i] == "credacquiredfromto")) {

				$maxname = "max_".$entirefields_array[$i];
				$minname = "min_".$entirefields_array[$i];
	
				$ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
				
				$maxvalue = $ranges[1];
				$minvalue = $ranges[0];
				
				$showFilter .=  "<font class='afontstyle'>
	
					       From : <input name='".$minname."' value='".$minvalue."' size='8' type='text' id='".$minname."'>								
					       <script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$minname."'});</script>
					      <a href=javascript:resetStartDate('".$minname."')>
					       <i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;
	
					       To : <input name='".$maxname."' value='".$maxvalue."' size='8' type='text' id='".$maxname."'>
					       <script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$maxname."'});</script>
					       <a href=javascript:resetStartDate('".$maxname."')>
					       <i alt='Reset' class='fa fa-reply'></i></a>";
			}
			else if($entirefields_array[$i] == "empBranchLocation") {
				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				
				$options = getBranchLocation($selectValue);
				$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$showFilter .=  $options;
				$showFilter .=  "</select>";
			}
			else if($entirefields_array[$i]=="empstatus") { //Filter for Status
			
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
			
				$showFilter .=  "<select class=drpdwne style='width:165px; height:20px' name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
				$showFilter .=  "<option value='ALL'>ALL</option>";
				$showFilter .= "<option value='N' ".sele("N",$selectValue).">Active</option>";
				$showFilter .= "<option value='Y' ".sele("Y",$selectValue).">Terminated</option>";
				$showFilter .=  "</select>";        
			}
			else if($entirefields_array[$i] == "empCompanyCode")
			{
				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$options = getCompanycodeList($selectValue);

				$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$showFilter .=  $options;
				$showFilter .=  "</select>";
			}
			else if($entirefields_array[$i] == "empdept") {
				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$options = getDepartmentList($selectValue,$deptAccesSno);

				$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$showFilter .=  $options;
				$showFilter .=  "</select>";
			}
			else if($entirefields_array[$i] == "credentialtype") {
				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';

				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$options = getCredentialTypes($selectValue);

				$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$showFilter .=  $options;
				$showFilter .=  "</select>";
			}
			else if($entirefields_array[$i] == "credentialname") {
				$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$options = getCredentialNames($selectValue);

				$showFilter .=  "<select class=drpdwne multiple='multiple' style='width:150px; height:50px' name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$showFilter .=  $options;
				$showFilter .=  "</select>";
			}
			else if($entirefields_array[$i] == "credentialcountry") {
				$getCountries = $objCredentialsJson->decode($countries);


				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$showFilter .=  "<select class=drpdwne style='width:165px; height:20px' onChange=\"javascript:getCreCountryStates(this.value,'');\" name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";

				if(!empty($getCountries)){

					$showFilter .=  "<option value='' ".sele("",$selectValue).">ALL</option>";
					foreach($getCountries as $key){

						$showFilter .=  "<option value='".$key->code."' ".sele($key->code,$selectValue).">".$key->name."</option>";
					}
				}
				$showFilter .=  "</select>";
			}
			else if($entirefields_array[$i] == "credentialstates") {
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

				$showFilter .=  "<select class=drpdwne style='width:165px; height:20px' name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>
				<option value=''></option>
				</select>";
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
			$sqlEmptype="select sno,name from manage where type='jotype' and name in('Temp/Contract','Internal Temp/Contract','Internal Direct')  ";
		}

		$resEmptype	= mysql_query($sqlEmptype,$rptdb);

		while($empType = mysql_fetch_row($resEmptype))
		{
			$sel_emptype	= $argSelectValue;
			
			$empType[1]	= ucfirst($empType[1]);
			$options .= "<option value='$empType[0]' ".sele($empType[0],$sel_emptype).">$empType[1]</option>";
		}
		return $options;
	}
	
	function getCompanycodeList($argSel = " ")
	{
		global $rptdb;

		$option 	= " ";
		$selected 	= " ";

		$sql		= "SELECT DISTINCT(empcon_w4.companycode) AS colname FROM empcon_w4 ORDER BY colname ";
		$rs 		=  mysql_query($sql,$rptdb);

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
		global $rptdb, $username;

		$option		= " ";
		$selected	= " ";

		$sql		= "SELECT DISTINCT(deptname) AS colname FROM department  WHERE sno !='0' AND sno IN ({$deptAccesSno}) AND status='Active' ORDER BY colname ";
		$rs		=  mysql_query($sql,$rptdb);

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

		$option		= " ";
		$selected 	= " ";
		
		$whereclause 	= ($locationStatus == "InActive") ? "status='BP'" : "status!='BP'";
		$whereclause 	= " WHERE ".$whereclause;
		
		$sql		= "SELECT DISTINCT(heading) AS colname, loccode FROM contact_manage ".$whereclause." ORDER BY colname ";
		$rs		=  mysql_query($sql,$rptdb);

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
		
		$sql	= "SELECT feid FROM contact_manage WHERE serial_no='".$argLocationId."'";
		$rs	= mysql_query($sql,$rptdb);
		$row	= mysql_fetch_array($rs);
		return $row[0];
	}
	
	function getCredentialTypes($argSel = " ")
	{
		global $rptdb, $objCredentialsType;

		$creds_acttype_list	= $objCredentialsType->getListOfCredentialsType();
		$option 		= " ";
		$selected 		= " ";
		

		if(!$argSel || ($argSel == 'ALL') || (strpos($argSel,'!#!')>0) && (in_array('ALL',explode('!#!',$argSel))) ){
			$option .= "<option value='ALL' selected>ALL</option>";
		}else{
			$option .= "<option value='ALL'>ALL</option>" ;
		}

		foreach ($creds_acttype_list as $type_id => $cre_type) {

			if(!$argSel || ($argSel == $type_id) || ((strpos($argSel,'!#!')>0) && in_array($type_id,explode('!#!',$argSel))) )
				$selected = "selected";
			else
				$selected = " ";

		 	if(trim($type_id)!='')
				$option .= "<option value='{$type_id}' {$selected}>{$cre_type}</option>" ;
		}

		return $option;
	}
	
	function getCredentialNames($argSel = " ")
	{
		global $rptdb, $objCredentialsName;

		$creds_actname_list	= $objCredentialsName->getListOfCredentialsName();
		$option 		= " ";
		$selected 		= " ";
		

		if(!$argSel || ($argSel == 'ALL') || (strpos($argSel,'!#!')>0) && (in_array('ALL',explode('!#!',$argSel))) ){
			$option .= "<option value='ALL' selected>ALL</option>";
		}else{
			$option .= "<option value='ALL'>ALL</option>" ;
		}

		foreach ($creds_actname_list as $name_id => $cre_name) {

			if(!$argSel || ($argSel == $name_id) || ((strpos($argSel,'!#!')>0) && in_array($name_id,explode('!#!',$argSel))) )
				$selected = "selected";
			else
				$selected = " ";

		 	if(trim($name_id)!='')
				$option .= "<option value='{$name_id}' {$selected}>{$cre_name}</option>" ;
		}

		return $option;
	}
?>