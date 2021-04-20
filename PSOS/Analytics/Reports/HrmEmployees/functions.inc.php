<?php 
	require_once("global.inc");
	
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
			//$fieldnames  = array("empid"=>"Employee Id","empssn"=>"SSN","empfname"=>"First Name","empmname"=>"Middle Name","emplname"=>"Last Name","empaddr1"=>"Address 1","empaddr2"=>"Address 2","empcity"=>"City","empstate"=>"State","empzip"=>"Zip","empphone"=>"Primary Phone","empmaritalst"=>"Marital Status","empdob"=>"Date of Birth","empltax"=>"Tax","emphiredate"=>"Date of Hire","empBranchLocation"=>"HRM Location","empClass"=>"Employee Type","empFedEmployee"=>"Federal Withholding (Employee)","empFedCompany"=>"Federal Withholding (Company)","empStateEmployee"=>"state withholding (Employee)","empStateCompany"=>"state withholding (Company)","empgender"=>"Gender","empfedtaxemployee"=>"Additional Federal Tax Amount withheld from check (Employee)","empfedtaxcompany"=>"Additional Federal Tax Amount withheld from check (Company)","empstatetaxemployee"=>"Additional State Tax Amount withheld from check (Employee)","empstatetaxcompany"=>"Additional State Tax Amount withheld from check (Company)","empsecurityemployee"=>"Social Security (Employee)","empsecuritycompany"=>"Social Security (Company)","empmedicareemployee"=>"Medicare (Employee)","empmedicarecompany"=>"Medicare (Company)","emplocalwithhold1employee"=>"Local Withholding 1 (Employee)","emplocalwithhold1company"=>"Local Withholding 1 (Company)","emplw2employee"=>"Local Withholding 2 (Employee)","emplw2company"=>"Local Withholding 2 (Company)","empnoallowclaim"=>"Total Federal Tax Allowances","empstatetaxallowance"=>"Total State Tax Allowances","emppayproviderid"=>"Payroll Provider ID#","empFein"=>"Federal ID","empAbaAcc1"=>"PrimaryABA","empAccnumberAcc1"=>"PrimaryAcctNo","empAccTypeAcc1"=>"PrimaryAcctType","empBanknameAcc1"=>"PrimaryBankName","empAbaAcc2"=>"Acct2ABA","empAccnumberAcc2"=>"Acct2AcctNo","empAccTypeAcc2"=>"Acct2AcctType","empBanknameAcc2"=>"Acct2BankName","empDoublePayrate"=>"Double Time  Rate","empOvertimeRate"=>"Overtime Rate","empWithholdTaxState"=>"Withholding Tax State","empsalary"=>"Salary","empStatus"=>"Status","filingStatus"=>"Filing Status for Withholding","empCreateduser" => "Created User","empCrateddate" => "Created Date","empMuser" => "Modified User","empMdate" => "Modified Date","pdlodging" => "Lodging","pdmie" => "M&amp;IE","pdtotal" => "Total","pdbillable" => "Billable","pdtaxable" => "Taxable","empEmail" => "Email","empEthnicity" => "Ethnicity","empVeterans" => "Veterans Status");
			
			$fieldnames  = array("empid"=>"Employee Id","empssn"=>"SSN","empfname"=>"First Name","empmname"=>"Middle Name","emplname"=>"Last Name","empaddr1"=>"Address 1","empaddr2"=>"Address 2","empcity"=>"City","empstate"=>"State","empzip"=>"Zip","empphone"=>"Primary Phone","empmaritalst"=>"Marital Status","empdob"=>"Date of Birth","empltax"=>"Tax","emphiredate"=>"Date of Hire","empBranchLocation"=>"HRM Location","empClass"=>"Employee Type","empFedEmployee"=>"Federal Withholding (Employee)","empFedCompany"=>"Federal Withholding (Company)","empStateEmployee"=>"state withholding (Employee)","empStateCompany"=>"state withholding (Company)","empgender"=>"Gender","empfedtaxemployee"=>"Additional Federal Tax Amount withheld from check (Employee)","empfedtaxcompany"=>"Additional Federal Tax Amount withheld from check (Company)","empstatetaxemployee"=>"Additional State Tax Amount withheld from check (Employee)","empstatetaxcompany"=>"Additional State Tax Amount withheld from check (Company)","empsecurityemployee"=>"Social Security (Employee)","empsecuritycompany"=>"Social Security (Company)","empmedicareemployee"=>"Medicare (Employee)","empmedicarecompany"=>"Medicare (Company)","emplocalwithhold1employee"=>"Local Withholding 1 (Employee)","emplocalwithhold1company"=>"Local Withholding 1 (Company)","emplw2employee"=>"Local Withholding 2 (Employee)","emplw2company"=>"Local Withholding 2 (Company)","empnoallowclaim"=>"Federal Tax Allowances","empstatetaxallowance"=>"State Tax Allowances","emppayproviderid"=>"Payroll Provider ID#","empFein"=>"Federal ID","empAbaAcc1"=>"PrimaryABA","empAccnumberAcc1"=>"PrimaryAcctNo","empAccTypeAcc1"=>"PrimaryAcctType","empBanknameAcc1"=>"PrimaryBankName","empAbaAcc2"=>"Acct2ABA","empAccnumberAcc2"=>"Acct2AcctNo","empAccTypeAcc2"=>"Acct2AcctType","empBanknameAcc2"=>"Acct2BankName","empDoublePayrate"=>"Double Time  Rate","empOvertimeRate"=>"Overtime Rate","empsalary"=>"Salary","empStatus"=>"Status","filingStatus"=>"Filing Status","empCreateduser" => "Created User","empCrateddate" => "Created Date","empMuser" => "Modified User","empMdate" => "Modified Date","pdlodging" => "Lodging","pdmie" => "M&amp;IE","pdtotal" => "Total","pdbillable" => "Billable","pdtaxable" => "Taxable","empEmail" => "Email","empEthnicity" => "Ethnicity","empVeterans" => "Veterans Status", "paychkdelmethod"=>"Pay Check Delivery Method","empWithholdTaxState"=>"Withholding State","filingStatusState"=>"Filing Status(state) for Withholding","empHrmDept"=>"HRM Department","empDisability" => "Disability","qca" => "Qualifying Children Amount","otherdependents" => "Other Dependents","claimtot" => "Claim Dependents Total","otherincome" => "Other income (not from jobs)","deduct" => "Deductions","schooldist" => "School District");
			//"empWithholdTaxState"=>"Withholding Tax State", removed (duplicate) Piyush R
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
	function getBranchLocation($argLocationId)
	{
		global $rptdb;
	    $qryLocation = "SELECT heading,city,state FROM contact_manage WHERE serial_no='".$argLocationId."'";
		$rsLocation = mysql_query($qryLocation,$rptdb);
		$rowLocation = mysql_fetch_assoc($rsLocation);
		$string = "";
		if($rowLocation["heading"] != "")
			$string .= $rowLocation["heading"];
		
		if($rowLocation["city"] != "")	
		{
			if($string != "")
				$string .= ",".$rowLocation["city"];
			else
				$string .= $rowLocation["city"];
		}
		if($rowLocation["state"] != "")
		{	
			if($string != "")
				$string .= ",".$rowLocation["state"];
			else
				$string .= $rowLocation["state"];
		}
		return $string;
		
	}
	//---Function For Getting Search Column Name of corresponding fields------//
	function getserColName($argFieldName)
	{
		$arrColumnName  = array("empid" =>"sno","empssn"=>"ssn","empphone"=>"wphone","empfname"=>"fname","empmname"=>"mname","emplname"=>"lname","empaddr1"=>"address1","empaddr2"=>"address2","empcity"=>"city","empstate"=>"state","empzip"=>"zip","empBranchLocation"=>"","empFedEmployee"=>"fwh","empFedCompany"=>"cfwh","empStateEmployee"=>"swh","empStateCompany"=>"cswh","empfedtaxemployee"=>"aftaw","empfedtaxcompany"=>"caftaw","empstatetaxemployee"=>"astaw","empstatetaxcompany"=>"castaw","empsecurityemployee"=>"sswh","empsecuritycompany"=>"csswh","empmedicareemployee"=>"mwh","empmedicarecompany"=>"cmwh","emplocalwithhold1employee"=>"localw1_amt","emplocalwithhold1company"=>"clocalw1_amt","emplw2employee"=>"localw2_amt","emplw2company"=>"clocalw2_amt","empnoallowclaim"=>"tnum*federal_exempt","empstatetaxallowance"=>"tstatetax*state_exempt","emppayproviderid"=>"payrollpid","empFein" => "feid","empAbaAcc1"=>"bankrtno","empAccnumberAcc1"=>"bankacno","empBanknameAcc1"=>"bankname","empAbaAcc2"=>"acc2_bankrtno","empAccnumberAcc2"=>"acc2_bankacno","empBanknameAcc2"=>"acc2_bankname","empDoublePayrate"=>"double_prate_amt","empdob"=>"d_birth","emphiredate"=>"date_hire","empmaritalst" => "m_status","empltax"=>"tax","empgender"=>"hp_gender","empClass"=>"emptype","empAccTypeAcc1"=>"acc1_type","empAccTypeAcc2"=>"acc2_type","empOvertimeRate"=>"double_brate_amt","empsalary"=>"","empStatus"=>"empterminated","filingStatus"=>"fstatus","empMdate"=>"mtime","empCreateduser" => "name","empCrateddate" => "stime","empMuser" => "name","pdlodging"=>"diem_lodging","pdmie"=>"diem_mie","pdtotal"=>"diem_total","pdbillable"=>"diem_billable","pdtaxable"=>"diem_taxable","empEmail"=>"email","empEthnicity"=>"ethnicity","empVeterans"=>"veteran_status","empWithholdTaxState"=>"state_withholding","filingStatusState"=>"fsstatus","empDisability" => "disability","qca" => "qualify_child_amt","otherdependents" => "other_dependents_amt","claimtot" => "claim_dependents_total","otherincome" => "other_income_amt","deduct" => "deduction_amt","schooldist" => "school_dist");

		return $arrColumnName[$argFieldName];
		//"empWithholdTaxState"=>"state", Removed duplicate Piyush R
	}
	//Function For Getting Search Table Name Of Corresponding Columns..
	function getserTabName($argFieldName)
	{
		$arrTableName  = array("empid" =>"hrcon_compen","empssn"=>"hrcon_personal","empphone"=>"hrcon_general","empfname"=>"hrcon_general","empmname"=>"hrcon_general","emplname"=>"hrcon_general","empaddr1"=>"hrcon_general","empaddr2"=>"hrcon_general","empcity"=>"hrcon_general","empstate"=>"hrcon_general","empzip"=>"hrcon_general","empBranchLocation"=>"contact_manage","emppayproviderid"=>"hrcon_w4","empFein" => "contact_manage","empAbaAcc1"=>"hrcon_deposit","empAccnumberAcc1"=>"hrcon_deposit","empBanknameAcc1"=>"hrcon_deposit","empAbaAcc2"=>"hrcon_deposit","empAccnumberAcc2"=>"hrcon_deposit","empBanknameAcc2"=>"hrcon_deposit","empdob"=>"hrcon_personal","emphiredate"=>"hrcon_compen","empmaritalst" => "hrcon_personal","empltax"=>"hrcon_w4","empgender"=>"hrcon_personal","empClass"=>"hrcon_compen","empAccTypeAcc1"=>"hrcon_deposit","empAccTypeAcc2"=>"hrcon_deposit","empOvertimeRate"=>"hrcon_jobs","empsalary"=>"","empnoallowclaim"=>"hrcon_w4","empstatetaxallowance"=>"hrcon_w4","empStatus"=>"emp_list","filingStatus"=>"hrcon_w4","empMdate"=>"emp_list","empCreateduser" => "users","empCrateddate" => "emp_list","empMuser" => "users","pdlodging"=>"hrcon_compen","pdmie"=>"hrcon_compen","pdtotal"=>"hrcon_compen","pdbillable"=>"hrcon_compen","pdtaxable"=>"hrcon_compen","empEmail"=>"hrcon_general","empEthnicity"=>"hrcon_personal","empVeterans"=>"hrcon_personal","paychkdelmethod"=>"hrcon_deposit","empWithholdTaxState"=>"hrcon_w4","filingStatusState"=>"hrcon_w4","empDisability"=>"hrcon_personal","qca"=>"hrcon_w4","otherdependents"=>"hrcon_w4","claimtot"=>"hrcon_w4","otherincome"=>"hrcon_w4","deduct"=>"hrcon_w4","schooldist"=>"hrcon_w4");
		//"empWithholdTaxState"=>"hrcon_general",Removed duplicate Piyush R		
		return $arrTableName[$argFieldName];
	}	
	//This fun called in filter condition preparation..
	function getOrderByName($argFieldName)
	{
		// array('search window parameter','order by index');
		$arrTableName  = array(
								"empFein" 					=> array("contact_manage.feid","cm.feid"),
								"empid" 					=> array("hrcon_compen.fname","hc.emp_id"),
								"empssn" 					=> array("hrcon_personal.empssn","hp.ssn"),
								"empfname" 					=> array("hrcon_general.fname","hg.fname"),
								"empmname" 					=> array("hrcon_general.mname","hg.mname"),
								"emplname" 					=> array("hrcon_general.lname","hg.lname"),
								"empaddr1" 					=> array("hrcon_general.address1","hg.address1"),
								"empaddr2" 					=> array("hrcon_general.address2","hg.address2"),
								"empcity" 					=> array("hrcon_general.city","hg.city"),
								"empstate" 					=> array("hrcon_general.state","hg.state"),
								"empzip" 					=> array("hrcon_general.zip","hg.zip"),
								"empphone" 					=> array("hrcon_general.wphone","hg.wphone"),
								"empmaritalst" 				=> array("hrcon_personal.m_status","hp.m_status"),
								"empgender" 				=> array("hrcon_personal.hp_gender","hp.hp_gender"),
								"empdob" 					=> array("hrcon_personal.d_birth","hp.d_birth"),
								"empStateEmployee" 			=> array("hrcon_w4.swh","hw.swh"),
								"empnoallowclaim" 			=> array("hrcon_w4.federal_exempt","hw.federal_exempt"),
								"empBranchLocation" 		=> array("hrcon_compen.location","hc.location"),
								"emphiredate" 				=> array("hrcon_compen.date_hire","hc.date_hire"),
								"empstatetaxemployee" 		=> array("hrcon_w4.astaw","hw.astaw"),
								"empfedtaxemployee" 		=> array("hrcon_w4.aftaw","hw.aftaw"),
								"empHrmDept" 				=> array("department.deptname","dt.deptname"),
								"empStatus" 				=> array("emp_list.empterminated","empterminatedNew"),
								"empltax" 					=> array("hrcon_w4.tax","hw.tax"),
								"empClass" 					=> array("hrcon_compen.emptype","hc.emptype"),
								"empFedEmployee" 			=> array("hrcon_w4.fwh","hw.fwh"),
								"empFedCompany" 			=> array("hrcon_w4.cfwh","hw.cfwh"),
								"empStateCompany" 			=> array("hrcon_w4.cswh","hw.cswh"),
								"empfedtaxcompany" 			=> array("hrcon_w4.caftaw","hw.caftaw"),
								"empstatetaxcompany" 		=> array("hrcon_w4.castaw","hw.castaw"),
								"empsecurityemployee" 		=> array("hrcon_w4.sswh","hw.sswh"),
								"empsecuritycompany" 		=> array("hrcon_w4.csswh","hw.csswh"),
								"empmedicareemployee" 		=> array("hrcon_w4.mwh","hw.mwh"),
								"empmedicarecompany" 		=> array("hrcon_w4.cmwh","hw.cmwh"),
								"emplocalwithhold1employee" => array("hrcon_w4.localw1_amt","hw.localw1_amt"),
								"emplocalwithhold1company" 	=> array("hrcon_w4.clocalw1_amt","hw.clocalw1_amt"),
								"emplw2employee" 			=> array("hrcon_w4.localw2_amt","hw.localw2_amt"),
								"emplw2company" 			=> array("hrcon_w4.clocalw2_amt","hw.clocalw2_amt"),
								"empnoallowclaim" 			=> array("hrcon_w4.tnum","hw.tnum"),
								"empstatetaxallowance" 		=> array("hrcon_w4.tstatetax","hw.tstatetax"),
								"emppayproviderid" 			=> array("hrcon_w4.payrollpid","hw.payrollpid"),
								"empAbaAcc1" 				=> array("hrcon_deposit.bankrtno","hd.bankrtno"),
								"empAccnumberAcc1" 			=> array("hrcon_deposit.bankacno","hd.bankacno"),
								"empAccTypeAcc1" 			=> array("hrcon_deposit.acc1_type","hd.acc1_type"),
								"empBanknameAcc1" 			=> array("hrcon_deposit.bankname","hd.bankname"),
								"empAbaAcc2" 				=> array("hrcon_deposit.acc2_bankrtno","hd.acc2_bankrtno "),
								"empAccnumberAcc2" 			=> array("hrcon_deposit.acc2_bankacno","hd.acc2_bankacno"),
								"empAccTypeAcc2" 			=> array("hrcon_deposit.acc2_type","hd.acc2_type"),
								"empBanknameAcc2" 			=> array("hrcon_deposit.acc2_bankname","hd.acc2_bankname"),
								"empDoublePayrate" 			=> array("hrcon_compen.double_rate_amt","hc.double_rate_amt"),
								"empOvertimeRate" 			=> array("hrcon_compen.over_time","hc.over_time"),
								"empsalary" 				=> array("hrcon_compen.salary","hc.salary"),
								"filingStatus" 				=> array("hrcon_w4.fstatus","hw.fstatus"),
								"empCreateduser" 			=> array("emp_list.approveuser","el.approveuser"),
								"empCrateddate" 			=> array("emp_list.stime","el.stime"),
								"empMuser" 					=> array("emp_list.muser","el.muser"),
								"empMdate" 					=> array("emp_list.mtime","el.mtime"),
								"pdlodging" 				=> array("hrcon_compen.diem_lodging","hc.diem_lodging"),
								"pdmie" 					=> array("hrcon_compen.diem_mie","hc.diem_mie"),
								"pdtotal" 					=> array("hrcon_compen.diem_total","hc.diem_total"),
								"pdbillable" 				=> array("hrcon_compen.diem_billable","hc.diem_billable"),
								"pdtaxable" 				=> array("hrcon_compen.diem_taxable","hc.diem_taxable"),
								"empEmail" 					=> array("hrcon_general.email","hg.email"),
								"empEthnicity" 				=> array("hrcon_personal.ethnicity","hp.ethnicity"),
								"empVeterans" 				=> array("hrcon_personal.veteran_status","hp.veteran_status"),
								"paychkdelmethod" 			=> array("hrcon_deposit.delivery_method","hd.delivery_method"),
								"empWithholdTaxState" 		=> array("hrcon_w4.state_withholding","hw.state_withholding"),
								"filingStatusState" 		=> array("hrcon_w4.fsstatus","hw.fsstatus"),
								"empDisability" 			=> array("hrcon_personal.disability","m.name"),
								"qca" 						=> array("hrcon_w4.qualify_child_amt","hw.qualify_child_amt"),
								"otherdependents" 			=> array("hrcon_w4.other_dependents_amt","hw.other_dependents_amt"),
								"claimtot" 					=> array("hrcon_w4.claim_dependents_total","hw.claim_dependents_total"),
								"otherincome" 				=> array("hrcon_w4.other_income_amt","hw.other_income_amt"),
								"deduct" 					=> array("hrcon_w4.deduction_amt","hw.deduction_amt"),
								"schooldist" 				=> array("hrcon_w4.school_dist","hw.school_dist")

						);
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
						        $arrTableName[$var] = array("hrcon_contribute.tot_dollar_amt","hcb.tot_dollar_amt");
						        $arrTableName[$var1] = array("hrcon_contribute.comp_contribution","hcb.comp_contribution");
						        $arrTableName[$var2] = array("hrcon_contribute.emp_contribution","hcb.emp_contribution");
						        $arrTableName[$var3] = array("hrcon_contribute.dollar_amt_deduct","hcb.dollar_amt_deduct");
						    }
						}
						
				
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
			$entirefields_array =   array("empfname","empmname","emplname","empphone","empaddr1","empaddr2","empcity","empstate","empzip","empssn","empdob","empmaritalst","emphiredate","empCreateduser" ,"empCrateddate","empMuser","empMdate","empid","empBranchLocation","empltax","empgender","empClass","empFein","empsalary","empDoublePayrate","empOvertimeRate","empStatus","emppayproviderid","filingStatus","empFedEmployee","empFedCompany","empStateEmployee","empStateCompany","empfedtaxemployee","empfedtaxcompany","empstatetaxemployee","empstatetaxcompany","empsecurityemployee","empsecuritycompany","empmedicareemployee","empmedicarecompany","emplocalwithhold1employee","emplocalwithhold1company","emplw2employee","emplw2company","empnoallowclaim","empstatetaxallowance","empAbaAcc1","empAccnumberAcc1","empAccTypeAcc1","empBanknameAcc1","empAbaAcc2","empAccnumberAcc2","empAccTypeAcc2","empBanknameAcc2","pdlodging","pdmie","pdtotal","pdbillable","pdtaxable","empEmail","empEthnicity","empVeterans","filingStatusState","empHrmDept","empDisability","qca","otherdependents","claimtot","otherincome","deduct","schooldist");//"empWithholdTaxState", removed (duplicate) Piyush R

			
			//Code for dynamic columns field names
			$arrEarTypes = getAllEarTypes();
			if(count($arrEarTypes))
			{
				for($i=0;$i<count($arrEarTypes);$i++)
				{
					array_push($entirefields_array,$arrEarTypes[$i]."_tdollamt");
					array_push($entirefields_array,$arrEarTypes[$i]."_compcontr");
					array_push($entirefields_array,$arrEarTypes[$i]."_empcontr");
					array_push($entirefields_array,$arrEarTypes[$i]."_dollamtded");
				}
			}//Dynamic columns completed
		  
		   $filternames_array = explode("^",$argFilterNames);
		   $filtervalues_array = explode("^",dispTextdb($argFilterValues));
		   
			$correspondingvalues_array = array_combine($filternames_array , $filtervalues_array);
			
			$scriptStr = ' <script type="text/javascript">

			$(document).ready(function() {';	
			
		   for($i=0;$i<count($entirefields_array);$i++)
		   { 
			  
			$row_id = "filter_".$entirefields_array[$i];
			
			 if(in_array($entirefields_array[$i],$filternames_array))
					$style_filter = '';
				else
					$style_filter = "style='display:none'";
				
				if($entirefields_array[$i]=='empssn' || $entirefields_array[$i]=='empdob' || $entirefields_array[$i]=='empAbaAcc1' || $entirefields_array[$i]=='empAccnumberAcc1' || $entirefields_array[$i]=='empAbaAcc2' || $entirefields_array[$i]=='empAccnumberAcc2'){
					$style_filter = "style='display:none'"; 
				 }
				 $showFilter = "<tr id='{$row_id}' {$style_filter}>";
				 $showFilter .= "<td width=3%>&nbsp;</td>";
				 $showFilter .= "<td width=40%><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
				 $showFilter .= "<td align=left width=60%>";
				 
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
						<script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$minname."'});</script>
						<a href=javascript:resetStartDate('".$minname."')>
						<i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;
						To : <input name='".$maxname."' value='".$maxvalue."' size='8' type='text' id='".$maxname."' readonly>
						<script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$maxname."'});</script>
						<a href=javascript:resetStartDate('".$maxname."')>
						<i alt='Reset' class='fa fa-reply'></i></a></font>";
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
				 else if($entirefields_array[$i]=="empStatus")
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
				else if($entirefields_array[$i]=="filingStatus")
					{ //Filter for Status
						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
					
						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
						$showFilter .=  "<option value='ALL'>ALL</option>";
						$showFilter .= "<option value='Single' ".sele("Single",$selectValue).">Single</option>";
						$showFilter .= "<option value='Married' ".sele("Married",$selectValue).">Married</option>";
						$showFilter .= "<option value='Married, but w/h at higher single rate' ".sele("Married, but w/h at higher single rate",$selectValue).">Married, but w/h at higher single rate</option>";
						$showFilter .= "<option value='Head of Household' ".sele("Head of Household",$selectValue).">Head of Household</option>";
						$showFilter .= "<option value='Single or Married filing separately' ".sele("Single or Married filing separately",$selectValue).">Single or Married filing separately</option>";
						$showFilter .= "<option value='Married filing jointly' ".sele("Married filing jointly",$selectValue).">Married filing jointly</option>";

						$showFilter .=  "</select>";        
				   
					}
				else if($entirefields_array[$i]=="filingStatusState")
					{ //Filter for Status
						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
					
						$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
						$showFilter .=  "<option value='ALL'>ALL</option>";
						$showFilter .= "<option value='Single' ".sele("Single",$selectValue).">Single</option>";
						$showFilter .= "<option value='Married' ".sele("Married",$selectValue).">Married</option>";
						$showFilter .= "<option value='Married, but w/h at higher single rate' ".sele("Married, but w/h at higher single rate",$selectValue).">Married, but w/h at higher single rate</option>";
						$showFilter .= "<option value='Head of Household' ".sele("Head of Household",$selectValue).">Head of Household</option>";
						$showFilter .= "<option value='Single or Married filing separately' ".sele("Single or Married filing separately",$selectValue).">Single or Married filing separately</option>";
						$showFilter .= "<option value='Married filing jointly' ".sele("Married filing jointly",$selectValue).">Married filing jointly</option>";
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
				else if($entirefields_array[$i] == "empHrmDept")
					{
					
						$scriptStr .= '$("#select_'.$entirefields_array[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';

						$selectValue = $correspondingvalues_array[$entirefields_array[$i]];

						$options = getDepartmentList($selectValue,$deptAccesSno);

						$showFilter.="<select class=drpdwne  multiple='multiple' style='width:150px; height:50px'  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";

						#$trFilter .=  "<option value=''>ALL</option>";

						$showFilter .=  $options;

						$showFilter .=  "</select>";						
						
					}
				else if($entirefields_array[$i]=="empDisability")
				{
					$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
					$options = getDisabilityOptions($entirefields_array[$i],$selectValue);
					$showFilter.="<select class=drpdwne  name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}'>";
					$showFilter .=  "<option value='ALL'>ALL</option>";
					$showFilter .=$options;
					$showFilter .=  "</select>";        
			   
				}	
				 else 
				  { //Filter for  all remaining columns	
				   $selectValue = $correspondingvalues_array[$entirefields_array[$i]];	
					$str1 = getserTabName($entirefields_array[$i]);
					$str2 = getserColName($entirefields_array[$i]);

					if($entirefields_array[$i]=='qca' || $entirefields_array[$i]=='otherdependents' ||$entirefields_array[$i]=='claimtot' ||$entirefields_array[$i]=='otherincome' ||$entirefields_array[$i]=='deduct' ||$entirefields_array[$i]=='schooldist'){
				   		$showFilter .= "<font class='afontstyle'><input type=text name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}' size=20  value='".dispTextdb1(stripslashes($selectValue))."'></font>";
				   	}else{
				   		$showFilter .= "<font class='afontstyle'><input type=text name='{$entirefields_array[$i]}' id='{$entirefields_array[$i]}' size=20  value='".dispTextdb1(stripslashes($selectValue))."'></font>
				   <a href=javascript:hrEmployeesWindow('$entirefields_array[$i]','".$str1."^".$str2."')>
						<i alt='Search' class='fa fa-search'></i></a>";
				   	}
				   	
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

	//Function for getting disability values
	function getDisabilityOptions($argFieldName,$argSelectValue = "")
	{
		global $rptdb;
		$sqldis="SELECT sno,name FROM manage WHERE type='hrdisability' AND status = 'Y'";
		$resdis=mysql_query($sqldis,$rptdb);
		while($disType=mysql_fetch_row($resdis))
		{
			$sel_distype=$argSelectValue;
			$disType[1]=ucfirst($disType[1]);
			$options .= "<option value='$disType[0]' ".sele($disType[0],$sel_distype).">$disType[1]</option>";
		}
		return $options;
	}
	
	function getFederalid($argLocationId)
	{
	   global $rptdb;
	   $sql = "SELECT feid FROM contact_manage WHERE serial_no='".$argLocationId."'";
	   $rs = mysql_query($sql,$rptdb);
	   $row = mysql_fetch_array($rs);
	   return $row[0];
	}
	
	function getDepartmentList($argSel = " ",$deptAccesSno)
	{
		global $rptdb, $username;

		$option = " ";

		$selected = " ";

		$sql = "SELECT DISTINCT(deptname) AS colname FROM department WHERE sno !='0' AND sno IN ({$deptAccesSno}) AND status='Active' ORDER BY colname ";

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
?>