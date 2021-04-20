<?php
	require("dispfunc.php");
	require("reportdatabase.inc");
	/*
	Modifed Date : September 06, 2010
	Modified By  : Piyush R
	Purpose      : (Commission roles enh) Analytics-Need to provide a new column called Commission Role in Assignment and Joborder Reports.
	Task Id      : 5276.


	Modifed Date: August 11, 2010.
	Modified By: Piyush R.
	Purpose: Added new columns.
	Task Id: 5255 - (Prakash) Need to add columns, Federal tax Allowance - State Tax Allowance - Filing Status - Withholding State - Pay check delivery method  , to existing reports New Employee, Timesheet and Assignments reports.
	
	Modifed Date : June 28, 2010.
	Modified By  : Prasadd.
	Purpose      : Added new col called Submitted Candidate Primary Phone. 
	Task Id      : 5183.
	
	Modifed Date : Feb 08, 2010
	Modified By  : Prasadd.
	Purpose      : Added new column 'Workers Compensation Rate'
	Task Id      : 4941 (Prakash) New column 'Workers Compensation Rate' should be added to Assignment report.
	
	Modifed Date : Jan 20, 2010.
	Modified By  : Prasadd.
	Purpose      : Timesheet queries updated with new table timesheet_hours.
	Task Id      : 4906.
	
	Modifed Date : Jan 01, 2010.
	Modified By  : Prasadd.
	Purpose      : Invoice method, frequency column display pulled from customer record staffacc_cinfo table.
	Task Id      : 4879 (Career Connections) In assignments report, need to derive the invoice method and frequence from the customer record.
	
	Modifed Date : Dec 28, 2009
	Modified By  : Sambasivarao L.
	Purpose      : Added new reason column in report
	Task Id      : 4851 (Prakash) Need "Reason" field in Assingment Report. This is the field that pops up when user cancel or close the assignment and enter the end date. We do not need this field in the filters.
	
	Modifed Date : Nov 07, 2009
	Modified By  : Prasadd.
	Purpose      : Added default options code for new reports assignments related
	Task Id      : 4747,4750,4751.
	
	Modifed Date:25th Aug 2009
	Modified By: prasadd
	Purpose: wcompcode,Bill terms, Payment terms display fixed to drop list (contacts,companies,joborders,customers,timeshee,assignment reports)
	TS Task Id:4590.
	
	Modifed Date : July 01, 2009
	Modified By  : Prasadd.
	Purpose      : Added new columns
	Task Id      : 4470.
	*/
	function formateSlashes($argValue)
	{
	   if(!strpos($argValue,'\\\\') === false)
	   {
	   	  $argValue = stripslashes($argValue);
	      $argValue = str_replace('\\','\\\\\\\\\\\\\\',$argValue);
	   }
	   return $argValue;
	}
	
	
	
	function sele($argValue,$argSelectValue)
	{
		if($argValue == $argSelectValue)
		  return "selected";
		else
		  return "";

	}
	
	function sel($argValue,$argSelectValue)
	{
		if($argValue == $argSelectValue)
		  return "checked";
		else
		  return "";
	}
	
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
	
	function getRecruiter($argVendorId)
	{
		global $db;
		$qryStaffContact = "SELECT staffacc_cinfo.cname FROM vendorsubcon,staffacc_cinfo  WHERE staffacc_cinfo.username = vendorsubcon.venid AND vendorsubcon.empid = '".$argVendorId."' AND vendorsubcon.empid!='' AND staffacc_cinfo.type IN('CV','BOTH')"; // Added AND staffacc_cinfo.type IN('CV','BOTH') Piyush R June 3, 2010
		$rsStaffContact = mysql_query($qryStaffContact,$db);
		$rowStaffContact = mysql_fetch_assoc($rsStaffContact);
		return $rowStaffContact['cname'];
	}
	function getBillContact($argId)
	{
		global $db;
		$qryStaffContact = "SELECT CONCAT_WS( ' ', staffacc_contact.fname, staffacc_contact.lname ) 
		as contactname FROM staffacc_contact WHERE staffacc_contact.sno='".$argId."'";
		$rsStaffContact = mysql_query($qryStaffContact,$db);
		$rowStaffContact = mysql_fetch_row($rsStaffContact);
		return $rowStaffContact[0];
	}
	function getBranchLocation($argLocationId)
	{
		global $db;
	    $qryLocation = "SELECT heading,city,state FROM contact_manage WHERE serial_no='".$argLocationId."'";
		$rsLocation = mysql_query($qryLocation,$db);
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
	function getFederalid($argLocationId)
	{
	   global $db;
	   $sql = "SELECT feid FROM contact_manage WHERE serial_no='".$argLocationId."'";
	   $rs = mysql_query($sql,$db);
	   $row = mysql_fetch_array($rs);
	   return $row[0];
	}
	function  getJobType()
	{
	 global $db;
	   $i = 0;
	 $sqljobtype="select sno as jobid from  manage where type='jotype'  and name in('Direct','Internal Direct')"; 
	
	  $resjobtype =  mysql_query($sqljobtype,$db);
	   while($row = mysql_fetch_array($resjobtype))
	   {
		$arrIds[$i] = $row['jobid'];
		 $i++;
	   }
	  // $strIds = implode(",",$arrIds);
	   return $arrIds;
	
	}
	
	function getJobTypeNo()
	{
	 global $db;
	   $i = 0;
	 $sqljobtype="select sno as jobid from  manage where type='jotype'  and name not in('Direct','Internal Direct')"; 
	
	  $resjobtype =  mysql_query($sqljobtype,$db);
	   while($row = mysql_fetch_array($resjobtype))
	   {
		$arrIds[$i] = $row['jobid'];
		 $i++;
	   }
	  // $strIds = implode(",",$arrIds);
	   return $arrIds;
	
	}
	
	function companyName($argCompanyId)
	{
	   global $db;
	   $sql = "SELECT cname,federalid,customerid,inv_method,inv_terms FROM staffacc_cinfo WHERE sno='{$argCompanyId}' AND staffacc_cinfo.type IN('CUST','BOTH') "; // Added AND staffacc_cinfo.type IN('CUST','BOTH') Piyush R June 3, 2010
	   $rs = mysql_query($sql,$db);
	   $row = mysql_fetch_array($rs);
	   return $row;
	}
	// mloc fix
	function companyName_jlocation_billaddr($argCompanyId)
	{
	   global $db;
	   $sql = "SELECT CONCAT(jloc.title,' - ',jloc.address1,' ',jloc.address2,' ',jloc.city,' ',jloc.state,' ',jloc.zipcode) as cname FROM  staffacc_location jloc 
where   jloc.sno='{$argCompanyId}' ";
	   $rs = mysql_query($sql,$db);
	   $row = mysql_fetch_array($rs);
	   return $row;
	}
	function chkUserPreference()
	{
		global $username;
		global $db;
		global $analyticspref;
		$sql = "select users.type from users where users.username=$username";
		$rs = mysql_query($sql,$db);
		$row = mysql_fetch_assoc($rs);
		$user_type = $row['type'];
		//if(($user_type == 'sp') || (strpos($analyticspref, "+4")))
		if(($user_type == 'sp') || (chkUserPref($analyticspref, "4"))) // Updated July 09, 2010 Piyush R
		  return true;
		else
		  return false;
	}
	function getManageListOptions($argListType,$argSel = " ")
	{
		global $db;
		$option = " ";
		$selected = " ";
		$sql = "select distinct name,sno from manage where type='{$argListType}' order by name";
		$rs =  mysql_query($sql,$db);
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
	

	function getColumnName($argFieldName)
	{
		
       $fieldnames  = array("CompanyName" => "client","FirstName" => "fname","MiddleName" => "mname",
	    "LastName" => "lname","PayRate" => "pamount","BillRate" => "bamount","Salary" => "rate","CompenCode" => "wcomp_code",
	    "EmployeeId" => "emp_id","SSNNumber" => "ssn","AssignmentName" => "project","StartDate" => "s_date",
		"Recruiter" => "","SalesAgent" => "owner","Status" => "ustatus","blocation" => "location","otbrate" => "otrate","otpayrate" => "otrate","enddate" => "e_date","expenddate" => "exp_edate","federalid" => "feid","customer" => "customerid","dblpayrate" => "double_prate_amt","dblbrate" => "double_brate_amt","jocreator" => "name","moduser" => "muser","assign_imethod" => "inv_method","assign_iterms" => "inv_terms","workersCompRate" => "wcomp_code","HRMDepartment" => "","fedtaxallowance"=>"","statetaxallowance"=>"","paychkdelmethod"=>"","filingstatus"=>"","withholdingstate"=>"","statetaxwithholdpercentage"=>"", "markup"=>"markup",'empEmailId'=>'email','empPrimaryNumber'=>'wphone','empMobileNumber'=>'mobile','RegularPayRate'=>'RPayrate','RegularSalary'=>'RSalary','Regularotpayrate'=>'over_time','Regulardblpayrate'=>'double_rate_amt','Regularperdiemrate'=>'RDiemTotal','PayBurdenType'=>'PBType','BillBurdenType'=>'BBType',"industry" => "industry","assgnReason"=>"assgnReason","assgnReasonCodes"=>"assgnReasonCodes");
			
			return $fieldnames[$argFieldName];
	
	}
    function getDisplayName($argFieldName)
	{
		$fieldnames = array("CompanyName" => "Company Name","FirstName" => "First Name","MiddleName" => "MiddleName",
		"LastName" => "Last Name","PayRate" => "PayRate","BillRate" => "Bill Rate","Salary" => "Salary",
		"CompenCode" => "Workers Compensation code","EmployeeId" => "Employee Id","SSNNumber" => "SSN","AssignmentName"
		 => "Assignment Name","StartDate" => "Start Date","Recruiter" => "Recruiter/Vendor","SalesAgent" => "Placed By","Status" =>
		 "Status","blocation" => "HRM Location","otbrate" => "Overtime Bill Rate","otpayrate" => "Overtime Pay Rate","enddate" => "End Date","expenddate" => "Expected End Date","federalid" => "Federal ID","customer" => "Customer #","dblpayrate" => "Double Time Pay Rate","dblbrate" => "Double Time Bill Rate","jocreator" => "Job Order Creator","commempname" => "Commission Employee Name","commamount" => "Commission Amount","commsource" => "Commission Source","placefee" => "Placement Fee","margin" => "Margin ($)","assignmentid" => "Assignment ID","paypid" => "Payroll Provider ID#","credate" => "Created Date","moduser" => "Modified User","moddate" => "Modified Date","assignmenttype"=>"Assignment Type","jobtype"=>"Job Type","subhours" => "Total Hours","apphours" => "Number of hours Approved","burden" => "Pay Burden","subdate" => "Submitted Date","aprdate" => "Approved Date","tstrdate" => "Timesheet Start Date","tenddate" => "Timesheet End Date","corpcode" => "Corp Code","pdlodging" => "Lodging","pdmie" => "M&IE","pdtotal" => "Total","pdbillable" => "Billable","pdtaxable" => "Taxable","pdtotamt" => "Per Diem Total Amount","alternateId" => "Job ID","reghours" => "Regular Hours","overtimehours" => "Over Time Hours","doubletimehours" => "Double Time Hours","billablehours" => "Billable Hours","reportperson" => "Reports to","contactperson" => "Contact","assign_category" => "Category","assign_refcode" => "Ref.Code","assign_billcontact" => "Billing Contact","assign_billaddr" => "Billing Address","assign_imethod" => "Invoice Method","assign_iterms" => "Invoice Frequency","assign_pterms" => "Payment Terms(#days)","assign_tsapproved" => "Timesheet Approval","assign_ponumber" => "PO Number","assign_department" => "Department","assign_billterms" => "Billing Terms","assign_sterms" => "Service Terms","jlocation" => "Job Location","assgnReasonCodes"=>"Reason Codes","assgnReason" => "Reason","workersCompRate" => "Workers Compensation Rate","HRMDepartment" => "HRM Department","fedtaxallowance"=>"Federal Tax Allowances","statetaxallowance"=> "State Tax Allowances","paychkdelmethod"=>"Pay Check Delivery Method","filingstatus"=>"Filing Status","withholdingstate"=>"Withholding State","Role"=>"Role","statetaxwithholdpercentage"=> "State Tax Withholding Percentage", "markup"=>"Mark Up",'jobOrderId'=>'Job Order Id','empEmailId'=>'Primary E-mail','empPrimaryNumber'=>'Primary Phone','empMobileNumber'=>'Mobile Phone',"BillBurden" => "Bill Burden","RegularPayRate" => "Regular Pay Rate(HRM)","RegularSalary" => "Salary(HRM)","Regularotpayrate" => "Overtime Pay Rate(HRM)","Regulardblpayrate" => "Double Time Pay Rate(HRM)","Regularperdiemrate" => "Per Diem Total Amount(HRM)","PayBurdenType" => "Pay Burden Type","BillBurdenType" => "Bill Burden Type","industry" => "Industry");
		 
		return $fieldnames[$argFieldName];
	}

    function getTableName($argFieldName)
	{
		$fieldnames = array("CompanyName" => "jobs","FirstName" => "_general","MiddleName" => "_general",
		"LastName" => "_general","CompenCode" => "_jobs","SSNNumber" => "_personal","AssignmentName" => "_jobs",
		"StartDate" => "_jobs","Recruiter" => "candidate_list","CommissionEmployee"=>"emp_list");	
		return $fieldnames[$argFieldName];
	}
	
	function getOwnersList($argSelval = "")
	{
	  global $db;
	  $option = " ";
	  $sql="SELECT '',users.username,users.name from users where users.status != 'DA'";
	   $rs =  mysql_query($sql,$db);
	   while($row = mysql_fetch_array($rs))
	   {
			 if($argSelval && ($argSelval == $row['username']) )
		  		$selected = "selected";
			 else
			 	$selected = " ";
				
		 	 $option .= "<option value='{$row[username]}' {$selected}>".stripslashes($row['name'])."</option>" ;
		}
	   return $option;
	}
	
	
	
	function getCompaniesList($argSel = " ")
	{
	   global $db;
	   $option = " ";
	   $selected = " ";
	   $sql = "select sno,cname from staffacc_cinfo  where staffacc_cinfo.type IN('CUST','BOTH') order by cname"; // Added AND staffacc_cinfo.type IN('CUST','BOTH') Piyush R June 3, 2010
	   $rs =  mysql_query($sql,$db);
	   while($row = mysql_fetch_array($rs))
	   {
	     if($argSel && ($argSel == $row['sno']) )
		  $selected = "selected";
		 else
		   $selected = " ";
	 
		 if($row['cname'])
		 $option .= "<option value='{$row[sno]}' {$selected}>{$row[cname]}</option>" ;
	   }
	   return $option;
	}
	
   function getFieldOptionsList($argFieldName,$argSel = "")
   {
	   global $db;
	   $option = " ";
	   $selected = " ";
	   
	   $colname = getColumnName($argFieldName);
	   $tablename = getTableName($argFieldName);
	   
	   $qryHrCon = "select distinct({$colname}) as columnname from hrcon{$tablename} ";
	   $rsHrCon =  mysql_query($qryHrCon,$db);
	   
	   while($rowHrCon = mysql_fetch_array($rsHrCon))
	   {
	     if($argSel && ($argSel == dispTextdb($rowHrCon['columnname']) ) )
		  $selected = "selected";
		 else
		   $selected = " ";
		   
		 if($rowHrCon['columnname'])
		    $option .= "<option value='".dispfdb($rowHrCon['columnname'])."' {$selected}>{$rowHrCon[columnname]}</option>" ;
	   }
	   
	   if($argFieldName == "AssignmentName")
	   {
		   $qryEmpCon = "select distinct({$colname}) as columnname from hrcon{$tablename} ";
		   $rsEmpCon =  mysql_query($qryEmpCon,$db);
		   while($rowEmpCon = mysql_fetch_array($rsEmpCon))
		   {
			 if($argSel && ($argSel == dispTextdb($rowEmpCon['columnname']) ) )
			  $selected = "selected";
			 else
			   $selected = " ";
			   
			  if($rowEmpCon['columnname'])
				 $option .= "<option value='".dispfdb($rowEmpCon['columnname'])."' {$selected}>{$rowEmpCon[columnname]}</option>" ;
		   }
	    }
	   
	   return $option;
   }	
	
	function  getRecruitersList($argSel = "")
	{
		global $db;
	    $option = " ";
	    $selected = " ";
		 $qry = "SELECT staffacc_contact.sno,CONCAT_WS( ' ', staffacc_contact.fname, staffacc_contact.mname, 
		 staffacc_contact.lname ) as contactname FROM staffacc_contact where CONCAT_WS( ' ', 
		 staffacc_contact.fname, staffacc_contact.mname, staffacc_contact.lname ) != '' and staffacc_contact.username!='' ";
		$rs = mysql_query($qry,$db);
	   while($row = mysql_fetch_array($rs))
	   {
	     if($argSel && ($argSel == dispTextdb($row['contactname']) ) )
		  $selected = "selected";
		 else
		   $selected = " ";
	 
		 if($row['contactname'])
		    $option .= "<option value='".$row['sno']."' {$selected}>{$row[contactname]}</option>" ;
	   }
	   
	   return $option;
	
	}

	function getOptions($argFieldName,$argSelectValue = "",$deptAccesSno)
	{
		global $db;
		if($argFieldName == "HRMDepartment"){
		
			$sql = "SELECT sno,deptname FROM department WHERE sno !='0' AND sno IN ({$deptAccesSno}) ORDER BY deptname ";
			$rs =  mysql_query($sql,$db);
			while($row = mysql_fetch_array($rs))
			{
				$options .= "<option value='".$row['sno']."' ".sele($argSelectValue,$row['sno'])." title='".$row["deptname"]."'>".$row["deptname"]."</option>";
			}
			return $options;
		}elseif($argFieldName == "Role"){
		
			$sql = "SELECT sno,roletitle FROM company_commission where status='active' ORDER BY roletitle ";
			$rs =  mysql_query($sql,$db);
			while($row = mysql_fetch_array($rs))
			{
				$options .= "<option value='".$row['sno']."' ".sele($argSelectValue,$row['sno'])." title='".$row["roletitle"]."'>".$row["roletitle"]."</option>";
			}
			return $options;
		}
		elseif($argFieldName == "PayBurdenType"){
		
			$sql = "SELECT sno,burden_type_name FROM burden_types where ratetype='payrate' ORDER BY burden_type_name";
			$rs =  mysql_query($sql,$db);
			while($row = mysql_fetch_array($rs))
			{
				$options .= "<option value='".$row['sno']."' ".sele($argSelectValue,$row['sno'])." title='".$row["burden_type_name"]."'>".$row["burden_type_name"]."</option>";
			}
			return $options;
		}
		elseif($argFieldName == "BillBurdenType"){
		
			$sql = "SELECT sno,burden_type_name FROM burden_types where ratetype='billrate' ORDER BY burden_type_name";
			$rs =  mysql_query($sql,$db);
			while($row = mysql_fetch_array($rs))
			{
				$options .= "<option value='".$row['sno']."' ".sele($argSelectValue,$row['sno'])." title='".$row["burden_type_name"]."'>".$row["burden_type_name"]."</option>";
			}
			return $options;
		}
		else
		  return false;
	}
	function getCorpCodeOptions($argFieldName,$argSelectValue = "")
	{
		global $db;
		$sqlcorp="SELECT sno,name from  corp_code ORDER BY name  ";
		$rescorp=mysql_query($sqlcorp,$db);		
		while($ccode=mysql_fetch_row($rescorp))
		{
			$options  .= "<option value='$ccode[0]' ".compose_sel($argSelectValue,$ccode[0]).">$ccode[1]</option>";
		}
		
		return $options;
	}
	function getWCompCodeOptions($argFieldName,$argSelectValue = "")
	{
		global $db;
		$sqlcorp="SELECT workerscompid,CONCAT_WS('-',code,title,state) FROM workerscomp WHERE status = 'active' ORDER BY code";
		$rescorp=mysql_query($sqlcorp,$db);		
		while($ccode=mysql_fetch_row($rescorp))
		{
			$options  .= "<option value='$ccode[0]' ".compose_sel($argSelectValue,$ccode[0])." title='".html_tls_specialchars($ccode[1],ENT_QUOTES)."'>".html_tls_specialchars($ccode[1],ENT_QUOTES)."</option>";
		}
		
		return $options;
	}
	function getBillPayTermsOptions($argFieldName,$argSelectValue = "",$billPayType)
	{
		global $db;
		$options = "";
		$sqlcorp="SELECT billpay_termsid, billpay_code FROM bill_pay_terms WHERE billpay_status = 'active' AND billpay_type = '".$billPayType."' ORDER BY billpay_code";
		$rescorp=mysql_query($sqlcorp,$db);		
		while($ccode=mysql_fetch_row($rescorp))
		{
			$options  .= "<option value='$ccode[0]' ".compose_sel($argSelectValue,$ccode[0])." title='".html_tls_specialchars($ccode[1],ENT_QUOTES)."'>".html_tls_specialchars($ccode[1],ENT_QUOTES)."</option>";
		}
		
		return $options;
	}
	//Function for Calculating the billing Information
	function getAsgn_comm_calculate($ArgString)
	{	
            //$ArgString==type|payrate_margin|billrate_margin|burden|margin|Secondtype|payrate_markup|billrate_markup|markup;
            $ArgString_array=explode("|",$ArgString);
            $Firsttype=trim($ArgString_array[0]);
            $payrate_margin=trim($ArgString_array[1]);
            $billrate_margin=trim($ArgString_array[2]);
            $burden=trim($ArgString_array[3]);
            $margin=trim($ArgString_array[4]);
            $bill_burden=trim($ArgString_array[5]);
            
            if($bill_burden != ""){
                $bill_burden_amount = round(($bill_burden/100)*$billrate_margin, 2);
            }  else {
                $bill_burden_amount = 0;
            }

            $margincost = (($billrate_margin - $bill_burden_amount) - ($payrate_margin + (($burden / 100) * $payrate_margin)));
            
            $margincost=round($margincost,2);
            return $margincost;
	}
    function getIndustry($joindustry,$selectedvalue=''){
		global $db;
		$select = "SELECT sno,name FROM manage WHERE type ='joindustry'";
		$result = mysql_query($select,$db);
		$output = "<option value='ALL' ".sele('ALL',$selectedvalue)." >ALL</option>";
		while ($row = mysql_fetch_array($result)) {
			
			$output.= "<option value='".$row['sno']."' ".sele($row['sno'],$selectedvalue).">".$row['name']."</option>";
		}

		return $output; 
	}
	function getFilters($argFilterNames,$argFilterValues,$deptAccesSno)
	{
	   $entirefields_array = array("AssignmentName","HRMDepartment","FirstName","MiddleName","LastName","PayRate","BillRate","Salary","CompenCode",
	   "EmployeeId","SSNNumber","CompanyName","StartDate","Recruiter","SalesAgent","Status","otbrate","blocation","otpayrate","enddate","expenddate","federalid","customer","dblpayrate","dblbrate","jocreator","commempname","commamount","commsource","placefee","margin","assignmentid","paypid","credate","moduser","moddate","assignmenttype","jobtype","subhours","apphours","burden","subdate","aprdate","tstrdate","tenddate","corpcode","pdlodging","pdmie","pdtotal","pdbillable","pdtaxable","pdtotamt","alternateId","reghours","overtimehours","doubletimehours","billablehours","reportperson","contactperson","assign_category","assign_refcode","assign_billcontact","assign_billaddr","assign_billterms","assign_imethod","assign_iterms","assign_pterms","assign_tsapproved","assign_ponumber","assign_department","jlocation","assgnReason","assgnReasonCodes","Role", "markup","empEmailId","BillBurden","RegularPayRate","RegularSalary","Regularotpayrate","Regulardblpayrate","Regularperdiemrate","PayBurdenType","BillBurdenType","industry");

		$filternames_array = explode("^",$argFilterNames);
		$filtervalues_array = explode("^",html_tls_specialchars(trim($argFilterValues),ENT_QUOTES));
		
		$correspondingvalues_array = @array_combine($filternames_array , $filtervalues_array);

		$entirefields_array_count = count($entirefields_array);
	   for($i=0;$i<$entirefields_array_count;$i++)
	   { 

	   		$trFilter = "";
			$row_id = "filter_".$entirefields_array[$i];
			if(in_array($entirefields_array[$i],$filternames_array))
			    $style_filter = '';
			else
			    $style_filter = "style='display:none'";
			
			if($entirefields_array[$i]=='SSNNumber'){
					$style_filter = "style='display:none'"; 
				 }
			$trFilter = "<tr id='{$row_id}' {$style_filter}>";
			$trFilter .= "<td width='5%'>&nbsp;</td>";
			$trFilter .= "<td width='35%'><font class='afontstyle'>".getDisplayName($entirefields_array[$i])."</font></td>";
			$trFilter .= "<td>";
			if(($entirefields_array[$i] == "StartDate") || ($entirefields_array[$i] == "enddate") ||($entirefields_array[$i] == "expenddate")||($entirefields_array[$i] == "credate")||($entirefields_array[$i] == "moddate") || ($entirefields_array[$i] == "subdate") || ($entirefields_array[$i] == "aprdate") || ($entirefields_array[$i] == "tstrdate") || ($entirefields_array[$i] == "tenddate" ))
			{
			  $maxname = "max_".$entirefields_array[$i];
			  $minname = "min_".$entirefields_array[$i];
			  $ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
			  $maxvalue = $ranges[0];
			  $minvalue = $ranges[1];
              		  $trFilter .=  "<font class='afontstyle'>
					From : <input name='".$minname."' value='".$minvalue."' size='8' type='text'  id ='".$minname."' readonly>								
					<script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$minname."'});</script>
					<a href=javascript:resetStartDate('".$minname."')>
					<i alt='Reset' class='fa fa-reply'></i></a>&nbsp;&nbsp;
					
					To : <input name='".$maxname."' value='".$maxvalue."' size='8' type='text' id ='".$maxname."' readonly></font>
					<script language='JavaScript'> new tcal ({'formname':'form1','controlname':'".$maxname."'});</script>
					<a href=javascript:resetStartDate('".$maxname."')>
					<i alt='Reset' class='fa fa-reply'></i></a>";
					// resetStartDate() => HRAssign/scripts/link.js
			}
			elseif(($entirefields_array[$i] == "PayRate") || ($entirefields_array[$i] == "BillRate") 
			|| ($entirefields_array[$i] == "Salary") || ($entirefields_array[$i] == "EmployeeId") || ($entirefields_array[$i] == "otbrate") || ($entirefields_array[$i] == "otpayrate") || ($entirefields_array[$i] == "customer") || ($entirefields_array[$i] == "dblpayrate") || ($entirefields_array[$i] == "dblbrate") || ($entirefields_array[$i] == "commamount") || ($entirefields_array[$i] == "placefee") || ($entirefields_array[$i] == "margin") || ($entirefields_array[$i] == "subhours") || ($entirefields_array[$i] == "apphours") || ($entirefields_array[$i] == "burden") || ($entirefields_array[$i] == "pdlodging") || ($entirefields_array[$i] == "pdmie") || ($entirefields_array[$i] == "pdtotal") || ($entirefields_array[$i] == "pdtotamt") || ($entirefields_array[$i] == "alternateId") || ($entirefields_array[$i] == "reghours") || ($entirefields_array[$i] == "overtimehours") || ($entirefields_array[$i] == "doubletimehours") || ($entirefields_array[$i] == "billablehours") || ($entirefields_array[$i] == "markup") || ($entirefields_array[$i] == "BillBurden") || ($entirefields_array[$i] == "RegularPayRate") || ($entirefields_array[$i] == "RegularSalary") || ($entirefields_array[$i] == "Regularotpayrate") || ($entirefields_array[$i] == "Regulardblpayrate") || ($entirefields_array[$i] == "Regularperdiemrate"))
			 {
			  $maxname = "max_".$entirefields_array[$i];
			  $minname = "min_".$entirefields_array[$i];
			  $ranges = explode("*",$correspondingvalues_array[$entirefields_array[$i]]);
			  $maxvalue = $ranges[0];
			  $minvalue = $ranges[1];
			  $trFilter .=  "<font class='afontstyle'>
			  		Min : <input name='".$minname."' value='".$minvalue."' id='".$minname."' size='3' type='text' maxlength='8'>								
					&nbsp;&nbsp;&nbsp;&nbsp;
					Max : <input name='".$maxname."' value='".$maxvalue."' id='".$maxname."' size='3' type='text' maxlength='8'>
					</font>";
			}
			else if($entirefields_array[$i] == "HRMDepartment")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter .="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .= "<option value='ALL'>ALL</option>";
				$trFilter .= getOptions($entirefields_array[$i], $selectValue,$deptAccesSno);
				$trFilter .= "</select>";
			}
			elseif($entirefields_array[$i] == "Status")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				if($selectValue == "approved")
				{
					$selectValue = "active";
				}
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .= "<option value='active' ".compose_sel("active",$selectValue).">Active</option>";
				$trFilter .= "<option value='closed' ".compose_sel("closed",$selectValue).">Closed</option>";
				$trFilter .= "<option value='cancel' ".compose_sel("cancel",$selectValue).">Cancelled</option>";
				$trFilter .= "<option value='pending' ".compose_sel("pending",$selectValue).">Needs Approval</option>";
				
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "assign_imethod")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .= "<option value='Mail' ".compose_sel("Mail",$selectValue).">Mail</option>";
				$trFilter .= "<option value='Fax' ".compose_sel("Fax",$selectValue).">Fax</option>";
				$trFilter .= "<option value='Email' ".compose_sel("Email",$selectValue).">Email</option>";
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "assign_tsapproved")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .= "<option value='Online' ".compose_sel("Online",$selectValue).">Online</option>";
				$trFilter .= "<option value='Manual' ".compose_sel("Manual",$selectValue).">Manual</option>";
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "assign_iterms")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .= "<option value='Weekly' ".compose_sel("Weekly",$selectValue).">Weekly</option>";
				$trFilter .= "<option value='Bi-Weekly' ".compose_sel("Bi-Weekly",$selectValue).">Bi-Weekly</option>";
				$trFilter .= "<option value='Bi-Monthly' ".compose_sel("Bi-Monthly",$selectValue).">Bi-Monthly</option>";
				$trFilter .= "<option value='Semi-Monthly' ".compose_sel("Semi-Monthly",$selectValue).">Semi-Monthly</option>";
				$trFilter .= "<option value='Monthly' ".compose_sel("Monthly",$selectValue).">Monthly</option>";
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "assign_category")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$categoriesArr = getManageSnoNames("jocategory");
				foreach($categoriesArr as $key => $val)
				{
					$trFilter .= "<option value='$key' ".compose_sel($key,$selectValue).">$val</option>";
				}
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "commsource")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  "<option value='P' ".compose_sel("P",$selectValue).">placement fee</option>";
				$trFilter .= "<option value='BR' ".compose_sel("BR",$selectValue).">bill rate</option>";
				$trFilter .= "<option value='PR' ".compose_sel("PR",$selectValue).">pay rate</option>";
				$trFilter .= "<option value='MN' ".compose_sel("MN",$selectValue).">margin</option>";
				$trFilter .= "<option value='MP' ".compose_sel("MP",$selectValue).">markup</option>";
				$trFilter .= "<option value='RR' ".compose_sel("RR",$selectValue).">salary</option>";
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "assignmenttype")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  "<option value='AS' ".sele("AS",$selectValue).">Administrative Staff</option>";
				$trFilter .=  "<option value='OB' ".sele("OB",$selectValue).">On Bench</option>";
				$trFilter .=  "<option value='OV' ".sele("OV",$selectValue).">On Vacation</option>";
				$trFilter .=  "<option value='OP' ".sele("OP",$selectValue).">Project</option>";
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "jobtype")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$options = getManageListOptions("jotype",$selectValue);
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  $options;
				$trFilter .=  "</select>";
		    }	
			elseif($entirefields_array[$i] == "corpcode")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$options = getCorpCodeOptions($entirefields_array[$i],$selectValue);
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  $options;
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "pdbillable")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  "<option value='Y' ".sele("Y",$selectValue).">Billable</option>";
				$trFilter .=  "<option value='N' ".sele("N",$selectValue).">Non-Billable</option>";
				$trFilter .=  "</select>";
		    }	
			elseif($entirefields_array[$i] == "pdtaxable")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  "<option value='Y' ".sele("Y",$selectValue).">Taxable</option>";
				$trFilter .=  "<option value='N' ".sele("N",$selectValue).">Non-Taxable</option>";
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "CompenCode")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$options = getWCompCodeOptions($entirefields_array[$i],$selectValue);
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}' style='width:180px;'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  $options;
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "assign_billterms")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$options = getBillPayTermsOptions($entirefields_array[$i],$selectValue,'BT');
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}' style='width:180px;'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  $options;
				$trFilter .=  "</select>";
		    }
			elseif($entirefields_array[$i] == "assign_pterms")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$options = getBillPayTermsOptions($entirefields_array[$i],$selectValue,'PT');
				$trFilter.="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}' style='width:180px;'>";
				$trFilter .=  "<option value='ALL'>ALL</option>";
				$trFilter .=  $options;
				$trFilter .=  "</select>";
		    }
			else if($entirefields_array[$i] == "Role")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter .="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .= "<option value='ALL'>ALL</option>";
				$trFilter .= getOptions($entirefields_array[$i], $selectValue);
				$trFilter .= "</select>";
			}
			else if($entirefields_array[$i] == "PayBurdenType")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter .="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .= "<option value='ALL'>ALL</option>";
				$trFilter .= "<option value='Old' ".sele($selectValue,'Old').">Older Burden</option>";
				$trFilter .= getOptions($entirefields_array[$i], $selectValue);
				$trFilter .= "</select>";
			}
			else if($entirefields_array[$i] == "BillBurdenType")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter .="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .= "<option value='ALL'>ALL</option>";
				$trFilter .= "<option value='Old' ".sele($selectValue,'Old').">Older Burden</option>";
				$trFilter .= getOptions($entirefields_array[$i], $selectValue);
				$trFilter .= "</select>";
			}	
            else if($entirefields_array[$i] == "industry")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter .="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .= getIndustry($entirefields_array[$i], $selectValue);
				$trFilter .=  "</select>";
			}
			else if($entirefields_array[$i] == "assgnReasonCodes")
			{
				$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				$trFilter .="<select class=drpdwne  name='select_{$entirefields_array[$i]}' id='select_{$entirefields_array[$i]}'>";
				$trFilter .= getAssignReasonCodes($entirefields_array[$i], $selectValue);
				$trFilter .=  "</select>";
			}			
			else 
			{
			  	$selectValue = $correspondingvalues_array[$entirefields_array[$i]];
				
			  	$trFilter .=  "<font class='afontstyle'>
			  		<input name='select_{$entirefields_array[$i]}' id ='select_{$entirefields_array[$i]}' 
					value='".$selectValue."' type='text'>	
					</font> <a href=javascript:assignmentsWindow('$entirefields_array[$i]')><i alt='Search' class='fa fa-search'></i></a>";
					// assignmentsWindow() => HRAssign/scripts/link.js
			}
			$trFilter .=  "</td>";
			$trFilter .=  "</tr>";
			
			echo $trFilter;
	   }
	}
	
	function getStringFilterIds($argFilterName,$argFilterValue)
	{
	   	global $db;
	   	$i = 0;
	   	//$argFilterValue = formateSlashes($argFilterValue);
	   	if($argFilterName == "CompanyName")
		{
			//$sql = "select sno as filterid,cname from staffacc_cinfo where cname like '%{$argFilterValue}%' AND staffacc_cinfo.type IN('CUST','BOTH')"; // Added AND staffacc_cinfo.type IN('CUST','BOTH') Piyush R June 3, 2010
			$comp_name = str_replace("\\","",$argFilterValue);
			$sql = 'select sno as filterid,cname from staffacc_cinfo where REPLACE(LOWER(REPLACE(cname," ","")),"\\\", "") LIKE "%'.strtolower(str_replace(" ", "", $comp_name)).'%" AND staffacc_cinfo.type IN("CUST","BOTH")';
		}
        else if($argFilterName == "assign_billaddr")
		{ // mloc fix
		     $sql = "SELECT jloc.sno AS filterid, CONCAT( jloc.title, ' - ', jloc.address1, ' ', jloc.address2, ' ', jloc.city, ' ', jloc.state, ' ', jloc.zipcode )
                        AS cname FROM staffacc_location jloc 
                        WHERE TRIM(CONCAT(REPLACE(jloc.title,' ',''),'-',REPLACE(jloc.address1,' ',''),REPLACE(jloc.address2,' ',''),REPLACE(jloc.city,' ',''),REPLACE(jloc.state,' ',''),REPLACE(jloc.zipcode,' ',''))) LIKE '%".str_replace(' ','',$argFilterValue)."%'  "; // Added AND staffacc_cinfo.type IN('CUST','BOTH') Piyush R June 3, 2010
		
		}
             else if($argFilterName == "jlocation")
		{ // mloc fix
		  $sql = "SELECT jloc.sno AS filterid, CONCAT( jloc.title, ' - ', jloc.address1, ' ', jloc.address2, ' ', jloc.city, ' ', jloc.state, ' ', jloc.zipcode ) AS cname FROM staffacc_location jloc  WHERE TRIM(CONCAT(REPLACE(jloc.title,' ',''),'-',REPLACE(jloc.address1,' ',''),REPLACE(jloc.address2,' ',''),REPLACE(jloc.city,' ',''),REPLACE(jloc.state,' ',''),REPLACE(jloc.zipcode,' ',''))) LIKE '%".str_replace(' ','',$argFilterValue)."%'  "; // Added AND staffacc_cinfo.type IN('CUST','BOTH') Piyush R June 3, 2010
		}


              else if($argFilterName == "Recruiter")
		{
	   		$sql = "SELECT vendorsubcon.empid AS filterid FROM vendorsubcon,staffacc_cinfo WHERE staffacc_cinfo.username = vendorsubcon.venid AND staffacc_cinfo.cname LIKE '%{$argFilterValue}%' AND staffacc_cinfo.type IN('CV','BOTH')"; // Added AND staffacc_cinfo.type IN('CV','BOTH') Piyush R June 3, 2010
		}
		
	  	else if($argFilterName == "SalesAgent")
	   		$sql = "SELECT users.username as filterid ,users.name from users where  name like '%{$argFilterValue}%' ";
		else if($argFilterName == "moduser")
	   		$sql = "SELECT users.username as filterid ,users.name from users where name like '%{$argFilterValue}%' ";
	    else if($argFilterName == "blocation")
		{
	   		$wordsArr = explode(",",trim($argFilterValue)); 
			$totalWordsCount = count($wordsArr); 
			/*if($totalWordsCount == 3 && (trim($wordsArr[$totalWordsCount-1]) !=""))
				$wordsArr[$totalWordsCount-1] = getCountryCode($wordsArr[$totalWordsCount-1]);*/
			$argFilterValue = implode(",",$wordsArr);
			$sql = "SELECT serial_no as filterid FROM contact_manage WHERE status != 'BP' AND concat_ws(',',upper(heading),upper(city),upper(state))
			like '%".strtoupper($argFilterValue)."%' ";
		}
		else if($argFilterName == "federalid")
		{
	   		$sql = "SELECT serial_no AS filterid FROM contact_manage WHERE status != 'BP' AND feid
			LIKE '%".$argFilterValue."%' ";
		}
		else if($argFilterName == "commempname"){
	   		//$sql = "SELECT username as filterid from users where name like '%".addslashes($argFilterValue)."%' ";
			$sql = "SELECT  name as filterid from users where name like '%".addslashes($argFilterValue)."%' UNION SELECT concat_ws(' ',b.fname, b.mname, b.lname ) as filterid FROM staffacc_contact b where  concat_ws(' ',b.fname, b.mname, b.lname ) like '%".addslashes($argFilterValue)."%'";
		}	
		elseif($argFilterName == "corpcode" )
			 $sql = "select sno as  filterid from staffacc_cinfo  where  corp_code = '".$argFilterValue."'  AND staffacc_cinfo.type IN('CUST','BOTH')";
		elseif($argFilterName == "assign_billcontact" )
			$sql = "SELECT sno AS  filterid FROM staffacc_contact  WHERE  CONCAT_WS( ' ', staffacc_contact.fname, staffacc_contact.lname ) LIKE '%{$argFilterValue}%' ";
	   // Added AND staffacc_cinfo.type IN('CUST','BOTH') Piyush R June 3, 2010
	   	$rs =  mysql_query($sql,$db);
	   	while($row = mysql_fetch_array($rs))
	   	{
			$arrIds[$i] = $row['filterid'];
			$i++;
	   	}
		
	  	// $strIds = implode(",",$arrIds);
		return $arrIds;
	}
//get assignment is with time records or not....
function getTimeRowsCount($assid,$user)
{
	global $db;
	
	$sql = "SELECT COUNT(1) FROM timesheet_hours ts WHERE ts.status != 'Backup' AND ts.assid = '".$assid."' AND ts.username = '".$user."'";
	$sqlResult = mysql_query($sql,$db);
	$row = mysql_fetch_row($sqlResult);
	return $row[0];
}
//Function For Getting Time Sheet Details Based On Assignment..
function getTimeSheets($assid,$type,$user,$timeFilterVal="",$subHrsVal="",$appHrsVal="",$appDateVal="")
{
	global $db;
	$subArray = array();
	$apprArray = array();
	$parids = array();
	//query For Getting Submitted timesheet Details For Assignment..
	$subSql="SELECT tsa.parid, IFNULL( (
	SUM( IF( tsa.hourstype='rate1',IFNULL(tsa.hours, 0 ),0)) + SUM( IF( tsa.hourstype='rate2',IFNULL(tsa.hours, 0 ),0)) + SUM( IF( tsa.hourstype='rate3',IFNULL(tsa.hours, 0 ),0)) ) , 0 ) subhrs,tsa.assid,".tzRetQueryStringDTime("tsa.approvetime","DateTime24Sec","/").",tsa.assid,
	".tzRetQueryStringDTime("par_timesheet.stime","Date","/").",".tzRetQueryStringDate("par_timesheet.sdate","Date","/").",".tzRetQueryStringDate("par_timesheet.edate","Date","/").",IF(par_timesheet.ts_multiple='Y', (DATEDIFF(par_timesheet.edate,par_timesheet.sdate)+1),count(tsa.parid)),SUM( IF( tsa.hourstype='rate1',IFNULL(tsa.hours, 0 ),0)) reghrs,SUM( IF( tsa.hourstype='rate2',IFNULL(tsa.hours, 0 ),0)) overtimehrs,SUM( IF( tsa.hourstype='rate3',IFNULL(tsa.hours, 0 ),0)) doubletimehrs,tsaa.billablehrs
	FROM timesheet_hours tsa LEFT JOIN (SELECT ifnull( (
SUM( IF( tss.hourstype='rate1',IFNULL(tss.hours, 0 ),0)) + SUM( IF( tss.hourstype='rate2',IFNULL(tss.hours, 0 ),0)) + SUM( IF( tss.hourstype='rate3',IFNULL(tss.hours, 0 ),0)) ) , 0
) billablehrs,parid,assid FROM timesheet_hours tss WHERE tss.billable='Yes' AND tss.status = 'ER' AND tss.hourstype IN ('rate1','rate2','rate3') GROUP BY tss.parid,tss.assid) tsaa ON (tsaa.parid=tsa.parid AND tsaa.assid=tsa.assid),par_timesheet
	WHERE tsa.status = 'ER'
	AND tsa.hourstype IN ('rate1','rate2','rate3')
	AND tsa.parid = par_timesheet.sno
	AND tsa.assid = '".$assid."' 
	AND tsa.username = '".$user."'
	".$timeFilterVal."
	GROUP BY tsa.parid,tsa.assid ORDER BY tsa.parid";

	//query For Getting approved and Billed timesheet Details For Assignment..
	$apprSql = "SELECT tsa.parid, IFNULL( (
	SUM( IF( tsa.hourstype='rate1',IFNULL(tsa.hours, 0 ),0)) + SUM( IF( tsa.hourstype='rate2',IFNULL(tsa.hours, 0 ),0)) + SUM( IF( tsa.hourstype='rate3',IFNULL(tsa.hours, 0 ),0)) ) , 0
	) apphrs,".tzRetQueryStringDTime("par_timesheet.stime","Date","/")." submitteddate,".tzRetQueryStringDTime("tsa.approvetime"
,"Date","/")." approvetime,tsa.assid,".tzRetQueryStringDate("par_timesheet.sdate","Date","/").",".tzRetQueryStringDate("par_timesheet.edate","Date","/").",IF(par_timesheet.ts_multiple='Y', (DATEDIFF(par_timesheet.edate,par_timesheet.sdate)+1),count(tsa.parid)),SUM( IF( tsa.hourstype='rate1',IFNULL(tsa.hours, 0 ),0)) reghrs,SUM( IF( tsa.hourstype='rate2',IFNULL(tsa.hours, 0 ),0)) overtimehrs,SUM( IF( tsa.hourstype='rate3',IFNULL(tsa.hours, 0 ),0)) doubletimehrs,tsaa.billablehrs
	FROM timesheet_hours tsa LEFT JOIN (SELECT ifnull( (
SUM( IF( tss.hourstype='rate1',IFNULL(tss.hours, 0 ),0)) + SUM( IF( tss.hourstype='rate2',IFNULL(tss.hours, 0 ),0)) + SUM( IF( tss.hourstype='rate3',IFNULL(tss.hours, 0 ),0)) ) , 0
) billablehrs,parid,assid FROM timesheet_hours tss  WHERE tss.billable='Yes' AND tss.status IN ('Approved', 'Billed') AND tss.hourstype IN ('rate1','rate2','rate3') GROUP BY tss.parid,tss.assid) tsaa on(tsaa.parid=tsa.parid AND tsaa.assid=tsa.assid),par_timesheet
	WHERE tsa.status IN ('Approved', 'Billed') 
	AND tsa.hourstype IN ('rate1','rate2','rate3')
	AND tsa.parid = par_timesheet.sno
	AND tsa.assid='".$assid."'
	AND tsa.username = '".$user."'
	".$timeFilterVal." ".$appDateVal." 
	GROUP BY ".tzRetQueryStringDTime("tsa.approvetime","Date","/").",tsa.parid ORDER BY tsa.parid";
	
	$subResult = mysql_query($subSql,$db);
	$appResult = mysql_query($apprSql,$db);
	
	//Assigning Submitted Data into arrays..
	while($row = mysql_fetch_row($subResult))
	{
		if(trim($appDateVal) != "") //First get whether it has partial approved or not...
		{
			$Sql="SELECT COUNT(1) FROM timesheet_hours tsa, par_timesheet WHERE tsa.status IN ('Approved', 'Billed') AND tsa.parid = par_timesheet.sno AND tsa.assid = '".$assid."' AND tsa.username = '".$user."' ".$timeFilterVal." AND tsa.parid = '".$row[0]."' GROUP BY tsa.assid";
			$sqlRes = mysql_query($Sql,$db);
			$sqlResRow = mysql_fetch_row($sqlRes);
			if($sqlResRow[0] == 0)
			{
				continue;
			}
		}
		$parids[] = $row[0];
		$subArray[$row[0]]['Submmitted'][$row[5]."|".$row[6]."|".$row[7]."|".$row[8]."|".$row[9]."|".$row[10]."|".$row[11]."|".$row[12]] = $row[1];
	}
	//Assigning Approved Data into arrays..
	while($row = mysql_fetch_row($appResult))
	{
		if(!in_array($row[0],$parids))
			$parids[] = $row[0];
	
		$apprArray[$row[0]]['Approved'][$row[3]."|".$row[2]."|".$row[5]."|".$row[6]."|".$row[7]."|".$row[8]."|".$row[9]."|".$row[10]."|".$row[11]] = $row[1];
	}
	$newArray = array();
	$newArray = $apprArray;
	//Rearranging the data based on time sheet id
	foreach($subArray as $key => $value)
	{
		if(is_array($apprArray[$key]))
		{
			foreach($value as $in_key=>$in_value)
				$newArray[$key][$in_key]=$in_value;
		}
		else
			$newArray[$key]=$value;
	}
	$count = count($parids);
	$resArray = array();
	ksort($newArray);
	$cnt=0;
	//This is for Assigning the data based on the approved time of each timesheet..
	if($type == "approve")
	{
		foreach($newArray as $new_key=>$new_val)
		{
			foreach($new_val['Approved'] as $apprKey => $apprVal)
			{
				$appsubDate = explode("|",$apprKey);
				$resArray['subhrs'][$cnt] = $apprVal;
				$resArray['apphrs'][$cnt] = $apprVal;
				$resArray['subdate'][$cnt] = $appsubDate[1];
				$resArray['appdate'][$cnt] = $appsubDate[0];
				$resArray['strdate'][$cnt] = $appsubDate[2];
				$resArray['enddate'][$cnt] = $appsubDate[3];
				$resArray['days'][$cnt] = $appsubDate[4];
				$resArray['reghrs'][$cnt] = $appsubDate[5];
				$resArray['overtimehrs'][$cnt] = $appsubDate[6];
				$resArray['doubletimehrs'][$cnt] = $appsubDate[7];
				$resArray['billablehrs'][$cnt] = $appsubDate[8];
				$cnt++;
			}
			foreach($new_val['Submmitted'] as $subKey => $subVal)
			{
				$subDate = explode("|",$subKey);
				$resArray['subhrs'][$cnt] = $subVal;
				$resArray['apphrs'][$cnt] = "";
				$resArray['subdate'][$cnt] = $subDate[0];
				$resArray['appdate'][$cnt] = "";
				$resArray['strdate'][$cnt] = $subDate[1];
				$resArray['enddate'][$cnt] = $subDate[2];
				$resArray['days'][$cnt] = $subDate[3];
				$resArray['reghrs'][$cnt] = $subDate[4];
				$resArray['overtimehrs'][$cnt] = $subDate[5];
				$resArray['doubletimehrs'][$cnt] = $subDate[6];
				$resArray['billablehrs'][$cnt] = $subDate[7];
				$cnt++;
			}
		}
	}
	else
	{
		//This is for Assigning the data based on the timesheet..
		foreach($newArray as $new_key=>$new_val)
		{
			$days=0;
			$apprValTot=0;
			$sub_hrs_tot=0;
			$sub_reghrs = 0;
			$sub_overtimehrs = 0;
			$sub_doubletimehrs = 0;
			$sub_billablehrs = 0;
			$oppr_reghrs = 0;
			$oppr_overtimehrs = 0;
			$oppr_doubletimehrs = 0;
			$oppr_billablehrs = 0;
			foreach($new_val['Approved'] as $apprKey => $apprVal)
			{
				$apprValTot=$apprValTot+$apprVal;
				$appsubDate = explode("|",$apprKey);
				$days = $days+$appsubDate[4];
				$resArray['subdate'][$cnt] = $appsubDate[1];
				$resArray['strdate'][$cnt] = $appsubDate[2];
				$resArray['enddate'][$cnt] = $appsubDate[3];
				
				$sub_reghrs = $sub_reghrs+$appsubDate[5];
				$sub_overtimehrs = $sub_overtimehrs+$appsubDate[6];
				$sub_doubletimehrs = $sub_doubletimehrs+$appsubDate[7];
			    //$sub_billablehrs = $sub_billablehrs+$appsubDate[8];
				$sub_billablehrs = $appsubDate[8];
			
			}
			
			foreach($new_val['Submmitted'] as $subKey => $subVal)
			{
				$subDate = explode("|",$subKey);
				$sub_hrs_tot=$sub_hrs_tot+$subVal;
				$days = $days+$subDate[3];
				$resArray['subdate'][$cnt] = $subDate[0];
				$resArray['strdate'][$cnt] = $subDate[1];
				$resArray['enddate'][$cnt] = $subDate[2];
				
				$oppr_reghrs = $oppr_reghrs+$subDate[4];
				$oppr_overtimehrs = $oppr_overtimehrs+$subDate[5];
				$oppr_doubletimehrs = $oppr_doubletimehrs+$subDate[6];
		     	//$oppr_billablehrs = $oppr_billablehrs+$subDate[7];
				$oppr_billablehrs = $subDate[7];
			
			}
			$resArray['subhrs'][$cnt] = $apprValTot+$sub_hrs_tot;
			$resArray['days'][$cnt] = $days;
			$resArray['apphrs'][$cnt] = $apprValTot;
			$resArray['appdate'][$cnt] = "";
			
			$resArray['reghrs'][$cnt] = $sub_reghrs+$oppr_reghrs;
			$resArray['overtimehrs'][$cnt] = $sub_overtimehrs+$oppr_overtimehrs;
			$resArray['doubletimehrs'][$cnt] = $sub_doubletimehrs+$oppr_doubletimehrs;
			$resArray['billablehrs'][$cnt] = $sub_billablehrs+$oppr_billablehrs;
			$cnt++;
		}
	}
	return $resArray;
}
function getWcompRates($sno,$startDate,$endDate)
{
	global $db;
	
	$wcompRatesArr = array();
	$filterCondString = "";
	$fromDate1=$startDate;
	$toDate1=$endDate;
	
	$fromDate = ($fromDate1!='') ? date("Y-m-d",strtotime($fromDate1)) : "";
	$toDate = ($toDate1!='') ? date("Y-m-d",strtotime($toDate1)) : "";
	
	if($fromDate!='' && $toDate!='')
	{ 		
		$filterCondString.=" AND IF(DATE(rp.enddate) != '0000-00-00',( (DATE(rp.startdate) >='".$fromDate."' AND DATE(rp.enddate) <='".$toDate."') OR ('".$fromDate."' BETWEEN DATE(rp.startdate) AND DATE(rp.enddate)) OR ('".$toDate."' BETWEEN DATE(rp.startdate) AND DATE(rp.enddate))),(('".$fromDate."' BETWEEN DATE(rp.startdate) AND DATE_FORMAT(SYSDATE(), '%Y-%m-%d')) OR ('".$toDate."' BETWEEN DATE(rp.startdate) AND IF('".$toDate."' >= DATE_FORMAT(SYSDATE(), '%Y-%m-%d'),'".$toDate."',DATE_FORMAT(SYSDATE(), '%Y-%m-%d')))))";
	}
	   
	else if($fromDate!='' && $toDate=='' )
	{
		$filterCondString.=" AND IF(DATE(rp.enddate) != '0000-00-00',(('".$fromDate."' BETWEEN DATE(rp.startdate) AND DATE(rp.enddate)) OR (DATE(rp.startdate) >='".$fromDate."')),(DATE(rp.startdate) >='".$fromDate."' OR DATE(rp.startdate) <='".$fromDate."'))";
	}
	else if($fromDate=='' && $toDate!='')
	{
		$filterCondString.=" AND IF(DATE(rp.enddate) != '0000-00-00',((DATE(rp.enddate) <='".$toDate."') OR ('".$toDate."' BETWEEN DATE(rp.startdate) AND DATE(rp.enddate))),DATE(rp.startdate) <='".$toDate."')";
	}
	   
	$wrateSql = "SELECT rp.amount,IF(amountmode='PER','%',''),".tzRetQueryStringSelBoxDate("DATE(rp.startdate)","Date","/").",".tzRetQueryStringSelBoxDate("DATE(rp.enddate)","Date","/")." FROM rates_period rp WHERE rp.parentid = '".$sno."' AND rp.parenttype = 'WORKERSCOMP'".$filterCondString;
	
	$wrateResult = mysql_query($wrateSql,$db);
	
	while($wrateRow = mysql_fetch_row($wrateResult))
	{
		$wrateRow[3] = ($wrateRow[3] == "00/00/0000") ? "" : $wrateRow[3];
		$wcompRatesArr[] = $wrateRow[0]." ".$wrateRow[1]." (".trim($wrateRow[2]." - ".$wrateRow[3]," - ").")";
	}
	return $wcompRatesArr;  
}	

function getAssignReasonCodes($joindustry,$argSelectValue){
	global $db;
	$select = "SELECT sno,reason,`type` FROM reason_codes WHERE `type` IN ('assigncancelcode','assignclosecode') AND `status`='Active'";
	$result = mysql_query($select,$db);
	$output = "";
	while ($row = mysql_fetch_array($result)) {
		$reaason_type="";
		if ($row['type'] == "assignclosecode") {
			$reaason_type= "Close Reason";
		}
		else if ($row['type'] == "assigncancelcode") {
			$reaason_type="Cancel Reason";
		}
		$row['reason'] = iconv('utf-8', 'ascii//TRANSLIT', $row['reason']);
		$output.= "<option value='".$row['sno']."' ".sele($argSelectValue,$row['sno'])." >".$row['reason']."(".$reaason_type.")"."</option>";
	}
	return $output;

}

?>