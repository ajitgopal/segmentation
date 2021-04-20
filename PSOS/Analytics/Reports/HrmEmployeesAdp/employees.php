<?php
	ob_start();

	$rlib_filename="hrmemployees.xml";
	require("global_reports.inc");
	require("rlib.inc");
	
	$reportfrm=$reportfrm;
	require_once("functions.inc.php");
	$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	if($format=="")
		$format="html";

	$filename=$companyuser."_emp_adp_".date('m_d_Y_H_i');
	$module="HrmEmpAdp";

	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$db);
		$vrowdata=mysql_fetch_row($rresult);
		$vrow=explode("|username->",$vrowdata[0]);
		$Analytics_HrmEmployeesAdp=$vrow[0];
		$cusername=$vrow[1];
		if(strpos($Analytics_HrmEmployeesAdp,"|username->")!=0)
			$Analytics_HrmEmployeesAdp=$vrow[0];			
			
		session_update("cusername");
		   
		
		session_update("Analytics_HrmEmployeesAdp");
		
		$rdata=explode("|",$Analytics_HrmEmployeesAdp);
	}
	else
	{
		if($defaction == "print")
		{
			$Analytics_HrmEmployeesAdp=$_REQUEST['Analytics_HrmEmployeesAdp'];
			$rdata=explode("|",$Analytics_HrmEmployeesAdp);
			$tab=$rdata[18];
		}
		else 
		{
			$rdata=explode("|",$Analytics_HrmEmployeesAdp);
			$tab=$rdata[18];
		}
	}


	// Start of code for dynamic display of columns of note type
	$arrEarTypes = getAllEarTypes();
	$earTypesCount  = count($arrEarTypes);
	$datanumber = 81; //this number must be changed whenever the rdata static count changes..IMPORTANT
	$loopear = 0;

	for($loopnote=0;$loopnote<$earTypesCount;$loopnote++)
	{
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_tdollamt";
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_compcontr";
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_empcontr";
		$loopear++;
		$arrDynamicFieldNames[$loopear] = $arrEarTypes[$loopnote]."_dollamtded";
		$loopear++;
	}

	$valcount  = 0;
	$rdata_count = count($rdata);
	$checkArray = array();

	for($r=81;$r<$rdata_count;$r++)
	{
		if($rdata[$r] != "")
		{
		   $checkArray[$rdata[$r]] = $rdata[$r];
		   $valcount++; 
		}
	}

	$countarrDynamicFieldNames = count($arrDynamicFieldNames);
	for($loopnote=0;$loopnote<$countarrDynamicFieldNames;$loopnote++)
	{
		$notetype =  $arrDynamicFieldNames[$loopnote];
		$explodEar = explode("_",$notetype); 
		$dynDisplay_name = getDisplayName($arrDynamicFieldNames[$loopnote]);
		$headLine = "--";
		$headLine.= str_repeat("-",strlen($dynDisplay_name));
		if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
		{
			if(in_array($arrDynamicFieldNames[$loopnote],$checkArray))
				$$notetype = array($arrDynamicFieldNames[$loopnote],$dynDisplay_name,$headLine);
			else
				$$notetype = array('','',str_repeat("-",strlen($dynDisplay_name)));
		}
		else
		{
			$$notetype = array($arrDynamicFieldNames[$loopnote],$dynDisplay_name,$headLine); 
		}
		$arrNoteReferences[$loopnote] = $$notetype;
	}
	
	
	$filternames = $rdata[31];
	
	$filtervalues = $rdata[32];
	
	
	    $arrFilterNames = explode('^',$rdata[31]);

		$arrFilterValues = explode('^',addslashes($rdata[32]));
		
		
		
		if($rdata[32] != '')

			{
			
			
                
				$companydata = explode('^',addslashes($rdata[32]));
				// location code
				
				
				 $Ldata = $companydata[0]; 
				
				$feidArrL= explode('!#!',$Ldata);
				
				
				$L=0;

				if(!in_array('ALL',$feidArrL)){
				
				    if($feidArrL[0] != '*' && $feidArrL[0] != '') {

					$filterCondString_L = " AND (";

					foreach($feidArrL as $locationnamel){

					  if($L==0)						
						$filterCondString_L .="co.loccode = '".addslashes($locationnamel)."'";

					  else							
						$filterCondString_L .= " OR co.loccode = '".addslashes($locationnamel)."'";

					

						$L++;		  

					}

					$filterCondString_L .= " )";
					
					}

				}
				
				
				
				
				//dept code
				
				$ddata = $companydata[3];
				
				$feidArrD= explode('!#!',$ddata);
				
				
				$D=0;

				if(!in_array('ALL',$feidArrD)){
				
				    if($feidArrD[0] != '') {

					$filterCondString_D = " AND (";

					foreach($feidArrD as $deptcoded){

					  if($D==0)

						$filterCondString_D .="dt.deptname = '".addslashes($deptcoded)."'";

					  else	

						  $filterCondString_D .= " OR dt.deptname = '".addslashes($deptcoded)."'";

					

						$D++;		  

					}

					$filterCondString_D .= " )";
					
					}

				}

				
				
				
				//status code
				
				
				$sdata = $companydata[1];
				
				if($sdata == 'N') {
				
				 $filterCondString_S .=" and el.empterminated = 'N'";
				
				
				}else if($sdata == 'Y') {
				
				$filterCondString_S .=" and el.empterminated = 'Y'";
				
				}
				
				
				// company code
				 $cdata = $companydata[2]; 
				
				$feidArr= explode('!#!',$cdata);
				
				
				$c=0;

				if(!in_array('ALL',$feidArr)){
				
				    if($feidArr[0] != '') {

					$filterCondString1 = " AND (";

					foreach($feidArr as $companycoded){

					  if($c==0)

						$filterCondString1 .="hw.companycode = '".addslashes($companycoded)."'";

					  else	

						  $filterCondString1 .= " OR hw.companycode = '".addslashes($companycoded)."'";

					

						$c++;		  

					}

					$filterCondString1 .= " )";
					
					}

				}
				
				
				$filtercond = $filterCondString_L ." ".$filterCondString_S . "  ". $filterCondString1 ."  ".$filterCondString_D;
				
				
				

			}


	
	
	

	$cpque="SELECT payperiod FROM cpaysetup WHERE status='ACTIVE'";
	$cpres=mysql_query($cpque,$db);
	$cprow=mysql_fetch_row($cpres);

	$countArrNoteReferences = count($arrNoteReferences);

	$sortarr=array("empid","empssn","empfname","empmname","emplname","empaddr1","empaddr2","empcity","empstate","empzip","empphone","empmaritalst","empgender","empdob","empBranchLocation","empBranchCode","hrmdept","deptcode","emphiredate","empClass","empsalary","empOvertimeRate","empDoublePayrate","empStatus","empEmail","empEthnicity","empVeterans","emppayproviderid","companycode","payfrequency","empltax","filingStatus","empnoallowclaim","empfedtaxemployee","empStateEmployee","filingStateStatus","empstatetaxallowance","empstatetaxemployee","filingLocalStatus","LocalJurisdiction","emplocaltaxallowance","emplocaltaxemployee","empTerminatedDate","empReHireDate","empModifiedDate");

	$date1="";
	$date2="";
	$type="All";

	$sortingorder_array = $sortarr;

	$rep_orient="landscape";
	$rep_paper="letter";
		
	$rep_company=$companyname;
	$rep_header="Employees  data for Payroll Providers Report";
	$rep_title="All Employees";
	$rep_date="date";
	$rep_page="pageno";
	$rep_footer="";	

	$rep_sortorder="ASC";
	$rep_sortcol="empid";

	$empid[0]="empid"; //1
	$empssn[0]="empssn";//2
	$empfname[0]="empfname";//3
	$empmname[0]="empmname";//4

	$emplname[0]="emplname";//5
	$empaddr1[0]="empaddr1";//6
	$empaddr2[0]="empaddr2";//7
	$empcity[0]="empcity";//8
	$empstate[0]="empstate";//9
	$empzip[0]="empzip";//10
	$empphone[0]="empphone";//11
	$empmaritalst[0]="empmaritalst";//12
	$empgender[0] = "empgender";//13
	$empdob[0]="empdob";//14
	$empBranchLocation[0]="empBranchLocation"; //15
	$empBranchCode[0] = "empBranchCode";//16
	$hrmdept[0] = "hrmdept";//17
	$deptcode[0] = "deptcode";//18
	$emphiredate[0]="emphiredate";//19
	
	$empClass[0]="empClass";//20
	
	$empsalary[0]="empsalary";//21
	
	$empOvertimeRate[0]="empOvertimeRate";	//22
	$empDoublePayrate[0]="empDoublePayrate";//23
	$empStatus[0]="empStatus";//24
	$empEmail[0]="empEmail";  //25//added by swapna
	$empEthnicity[0]="empEthnicity";  //26//added by kumar raju
	$empVeterans[0]="empVeterans";  //27//added by kumar raju
	$emppayproviderid[0]="emppayproviderid"; //28
	$companycode[0] = "companycode";//29
	$payfrequency[0]="payfrequency";  //30//added by Piyush R
	$empltax[0]="empltax";//31
	$filingStatus[0] = "filingStatus";//32
	$empnoallowclaim[0] ="empnoallowclaim";//33
    $empfedtaxemployee[0] ="empfedtaxemployee";//34
	$empStateEmployee[0]="empStateEmployee";//35
	$empstatetaxallowance[0] ="empstatetaxallowance";//37
	$empstatetaxemployee[0] ="empstatetaxemployee";//38
	$emplocaltaxallowance[0] ="emplocaltaxallowance";//41
	$emplocaltaxemployee[0]="emplocaltaxemployee";//42
	
	//$empStatus[0]="Status";
	
	
	$filingStateStatus[0]="filingStateStatus";  //36//added by Piyush R
	$filingLocalStatus[0]="filingLocalStatus";  //39//added by Piyush R
	$LocalJurisdiction[0]="LocalJurisdiction";  //40//added by Piyush R

	$empTerminatedDate[0]="empTerminatedDate";  //added by Kumar Raju
	$empReHireDate[0]="empReHireDate";  //added by Kumar Raju
	$empModifiedDate[0]="empModifiedDate";  //added by Kumar Raju

	//Display names for each column heading 
	$empStateEmployee[1]="State Withholding (Employee)";//35	
	$empfedtaxemployee[1]="Additional Federal Tax Amount withheld from check (Employee)";//34
	$empstatetaxemployee[1]="Additional State Tax Amount withheld from check (Employee)";//38
	$empnoallowclaim[1]="Federal Tax Allowances";//33
	$empstatetaxallowance[1]="State Tax Allowances";//37
	$emplocaltaxallowance[1]="Local Tax Allowances";//41
	$emplocaltaxemployee[1]="Additional Local Tax Amount withheld from check (Employee)";//42
	// report for adp
	//$empWithholdTaxLocal[1] = "Withholding Local";
	$filingStateStatus[1] = "State Filing Status"; //36//99
	$filingLocalStatus[1] = "Local Filing  Status"; //39//99
	$LocalJurisdiction[1] = "Local Jurisdiction"; //40//99

	$empTerminatedDate[1]="Terminated Date";  //added by Kumar Raju
	$empReHireDate[1]="Date of Re-Hire";  //added by Kumar Raju
	$empModifiedDate[1]="Modified Date";  //added by Kumar Raju

	$empid[1]="Employee Id  ";//1
	$empssn[1]="SSN";//2
	$empfname[1]="First Name";//3
	$empmname[1]="Middle Name";//4
	$emplname[1]="Last Name";//5
	$empaddr1[1]="Address 1";//6
	$empaddr2[1]="Address 2";//7
	$empcity[1]="City";//8
	$empstate[1]="State";//9
	$empzip[1]="Zip";//10
	$empphone[1]="Primary Phone";//11
	$empmaritalst[1]="Marital Status";//12
	$empgender[1]="Gender";//13
	$empdob[1]="Date of Birth";//14
	$empBranchLocation[1]="HRM Location";//15
	$empBranchCode[1] = "Location Code";//16
	$hrmdept[1] = "HRM Department";//17
	$deptcode[1] = "DeptCode";//18
	$emphiredate[1]="Date of Hire";//19
	
	$empClass[1]="Employee Type";//20
	$empsalary[1]="Salary";//21
	$empOvertimeRate[1]="Overtime Rate";//22
	$empDoublePayrate[1]="Double Time  Rate";//23
	$empStatus[1]="Status";//24
	$empEmail[1]="Email"; //25//added by swapna
	$empEthnicity[1]="Ethnicity"; //26//added by kumar raju
	$empVeterans[1]="Veterans Status"; //27//added by kumar raju
	$emppayproviderid[1]="Payroll Provider ID#";//28
	$companycode[1] ="Company Code"; //29
	$payfrequency[1]="Pay Frequency"; //30//added by Piyush R
	$empltax[1]="Tax";//31
	$filingStatus[1] = "Filing Status";//32

	//underline for each column heading
	$empnoallowclaim[2]="----------------------------";//33
	$empStateEmployee[2]="-------------------------------";//35
	$empfedtaxemployee[2]="-------------------------------------------------------------";//34
	$emplocaltaxemployee[2]="-----------------------------------------------------------";//42
	$empstatetaxemployee[2]="-----------------------------------------------------------";//38
	$empstatetaxallowance[2]="--------------------------";//37
	$emplocaltaxallowance[2]="--------------------------";//41
	$filingStatus[2] = "------------------------------------";//32
	
	$filingStateStatus[2] = "------------------------------"; //36//99
	$filingLocalStatus[2] = "-------------------------"; //39//99
	$LocalJurisdiction[2] = "------------------------------"; //40//99

	$empTerminatedDate[2]="----------------";  //added by Kumar Raju
	$empReHireDate[2]="----------------";  //added by Kumar Raju
	$empModifiedDate[2]="----------------";  //added by Kumar Raju

	$empid[2]="------------";//1
	$empssn[2]="------------";//2
	$empfname[2]="-------------------------";//3
	$empmname[2]="-------------------------";//4
	$emplname[2]="-------------------------";//5
	$empaddr1[2]="--------------------------------------";//6
	$empaddr2[2]="--------------------------------------";//7
	$empcity[2]="-----------------------";//8
	$empstate[2]="----------------------";//9
	$empzip[2]="----------------";//10
	$empphone[2]="---------------";//11
	$empmaritalst[2]="--------------";//12
	$empgender[2]="------";//13
	$empdob[2]="-------------";//14
	$empBranchLocation[2]="-------------------------------------------------";//15
	
	$empBranchCode[2] = "-------------";//16
	$hrmdept[2] = "-------------";//17
	$deptcode[2] = "----------------------";//18
	$emphiredate[2]="------------";//19
	
	$empClass[2]="-----------------------";//20
	$empDoublePayrate[2]="---------------------";//23
	$empOvertimeRate[2]="-----------------------";//22	
	$empsalary[2]="------------------";//21
	$empStatus[2]="----------";//24
	
		
	$emppayproviderid[2]="---------------------";//28
	$empEmail[2]="---------------------------------";//25 //added by swapna
	$empEthnicity[2]="---------------------------------"; //26////added by kumar raju
	$empVeterans[2]="---------------------------------"; //27//added by kumar raju
	$companycode[2] = "--------------------------------";//29
	$payfrequency[2]="---------------------------------"; //30//added by piyush r
	$empltax[2]="-------";//31
	
	//ends here

	$sortarry_count=count($sortarr);

	$rep_sortcolno="";
    if($sortarr[0]!="")
    {
        for($q=0;$q<$sortarry_count;$q++)
        {
            if($sortarr[$q]==$rep_sortcol)
            {
                $rep_sortcolno=$q;
            }
        }
    }

	$k=0;
	
	//Array  for displaying  heading for all the columns selected
	for($q=0;$q<$sortarry_count;$q++)
	{ 
		$variable = $$sortarr[$q] ;
		if($variable[0]!="")
		{ 
			$data[0][$k] = $variable[0];
			$headval[0][$k] = $variable[1];
			$headval[1][$k] = $variable[2];
			$k++;
		}
	}
	
	if($k!=0)
	{
		$data[0][$k]="link";
		$k++;
		$data[0][$k]="link_length";
	}	
 	$i=1;
	
	//Arrays for holding Billable and Taxable Types
	$arrayBillableType = array('Y' => 'Billable','N' =>'Non-Billable');
	$arrayTaxableType = array('Y' => 'Taxable','N' =>'Non-Taxable');
	//Ends here
	
	//Filter  conditions in Main query as per the selected values from the filters for each column
	$filtername_count=count($filternames_array);
	$accstr='';
	$from_hire="";
	$to_hire="";
	$empIntDirCond="";
	$empStatusCond="";

	$groupbyCond="group by el.sno"; //Group by condition intially should have

	//if(in_array("empStatus",$sortarr))
	//	$empStatusCond=" and el.empterminated='N'";

	for($f=0;$f<$filtername_count;$f++)
	{
		$fieldname="";
		$filedtable="";
		$fieldname=getserColName($filternames_array[$f]);
		$filedtable=getserTabName($filternames_array[$f]);

		if($filedtable=="hrcon_compen")
			$filedtable="hc";
		else if($filedtable=="hrcon_personal")
			$filedtable="hp";
		else if($filedtable=="hrcon_general")
			$filedtable="hg";
		else if($filedtable=="hrcon_w4")
			$filedtable="hw";
		else if($filedtable=="hrcon_deposit")
			$filedtable="hd";
		else if($filedtable=="hrcon_jobs")
			$filedtable="hr";

		if(($filternames_array[$f] == "empmaritalst") || ($filternames_array[$f] == "empltax")|| ($filternames_array[$f] == "empgender") || ($filternames_array[$f] == "empAccTypeAcc1")|| ($filternames_array[$f] == "empAccTypeAcc2") ) 
		{
			if($filtervalues_array[$f] != "ALL")
				$accstr=$accstr." and $filedtable.$fieldname like '%$filtervalues_array[$f]%' ";
		}
		else if($filternames_array[$f] == "empClass")
		{ 
			if($filtervalues_array[$f] != "ALL")
			{
				 $accstr=$accstr." and $filedtable.$fieldname = '$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "empStatus")
		{
			$empStatusCond="";
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and el.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "filingStatus")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hw.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "pdbillable")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hc.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "pdtaxable")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hc.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else if($filternames_array[$f] == "empEmail")
		{
			if($filtervalues_array[$f] != "")
			{
				$accstr=$accstr." and hg.$fieldname like '%$filtervalues_array[$f]%' ";
			}
		}
		else if($filternames_array[$f] == "empnoallowclaim" || $filternames_array[$f] == "empstatetaxallowance" || $filternames_array[$f] == "pdlodging"|| $filternames_array[$f] == "pdmie"|| $filternames_array[$f] == "pdtotal")
		{
			if($filtervalues_array[$f]!="*" && $filtervalues_array[$f]!="")
			{
				$fedStatefields=explode("*",$fieldname); 
				$numberFiledname=$fedStatefields[0];
				$exemptFieldname=$fedStatefields[1];
				
				if($filtervalues_array[$f]=="Exempt_$filternames_array[$f]")
				{
					 $accstr=$accstr." and $filedtable.$exemptFieldname ='Y' ";
					 $new_Condition = "  and  $filedtable.$exemptFieldname!='Y'";
				}
				else
				{
					$ranges = explode("*",$filtervalues_array[$f]);
					$maxvalue = $ranges[1];
					$minvalue = $ranges[0];
					
					if($maxvalue!="" && $minvalue!="")
					$accstr=$accstr." and  ".$filedtable.".".$numberFiledname." <=".(double) $maxvalue." and ".$filedtable.".".$numberFiledname." >=".(double) $minvalue;
					elseif($maxvalue!="")
					$accstr=$accstr." and  ".$filedtable.".".$numberFiledname." <=".(double) $maxvalue .$new_Condition;
					elseif($minvalue!="")
					$accstr=$accstr." and  ".$filedtable.".".$numberFiledname." >=".(double) $minvalue;
				}
			}
		}		
		else if($filternames_array[$f] == "empFedEmployee" || $filternames_array[$f] == "empStateEmployee" || $filternames_array[$f] == "empFedCompany" || $filternames_array[$f] == "empStateCompany" || $filternames_array[$f] == "empfedtaxemployee" || $filternames_array[$f] == "empfedtaxcompany" || $filternames_array[$f] == "empstatetaxemployee"  || $filternames_array[$f] == "empstatetaxcompany" || $filternames_array[$f] == "empsecurityemployee"  || $filternames_array[$f] == "empsecuritycompany" || $filternames_array[$f] == "empmedicareemployee" || $filternames_array[$f] == "empmedicarecompany" || $filternames_array[$f] == "emplocalwithhold1employee" || $filternames_array[$f] == "emplocalwithhold1company" || $filternames_array[$f] == "emplw2employee" || $filternames_array[$f] == "emplw2company" || $filternames_array[$f] == "empid" )
		{ //All the columns ,having the number values 
			
			if($filternames_array[$f] == "empid") 
				$tableName="el";
			else if($filternames_array[$f] == "empOvertimeRate")
				$tableName="hr";
			else
				$tableName="hw";
			$ranges = explode("*",$filtervalues_array[$f]);
			$maxvalue = $ranges[1];
			$minvalue = $ranges[0];
			
			if($filtervalues_array[$f] != "ALL" && $filtervalues_array[$f] != "")
			{		
			  if($maxvalue!="" && $minvalue!="")
				$accstr=$accstr." and  ".$tableName.".".$fieldname." <=".(double) $maxvalue." and ".$tableName.".".$fieldname." >=".(double) $minvalue;
			  elseif($maxvalue!="")
				$accstr=$accstr." and  ".$tableName.".".$fieldname." <=".(double) $maxvalue;
			  elseif($minvalue!="")
				$accstr=$accstr." and  ".$tableName.".".$fieldname." >=".(double) $minvalue;
			  
			}
		}		
		else if(($filternames_array[$f] == "empdob") || ($filternames_array[$f] == "emphiredate")) //Date of birth
		{
		 	if($filtervalues_array[$f]!='*')
			{
				$tabFieldname=$filedtable.".".$fieldname;
				$filterDate=explode("*",$filtervalues_array[$f]); 
				$fromDate=$filterDate[0];
				$toDate=$filterDate[1];
				if($fromDate!='')
					$fromDate=date("Y-m-d",strtotime($fromDate));
				else
					$fromDate='';
				if($toDate!='')
					$toDate=date("Y-m-d",strtotime($toDate));
				else
					$toDate='';
				
				$dob_formate=" ".tzRetQueryStringSTRTODate("$tabFieldname","%m-%d-%Y","YMDDate","-")." "; 
				if($fromDate!='' && $toDate!='') 
				   $accstr=$accstr." and $dob_formate  between '".$fromDate."' and  '".$toDate."'";
				else if($fromDate!='' && $toDate=='' )
				   $accstr=$accstr." and  $dob_formate >='".$fromDate."'";
				else if($fromDate=='' && $toDate!='' )
				   $accstr=$accstr." and $dob_formate <='".$toDate."'";
		 	}		
		}
		else if($filternames_array[$f] == "empMdate" || $filternames_array[$f] == "empCrateddate")
		{
			$tabFieldname="el.".$fieldname;
			$filterDate=explode("*",$filtervalues_array[$f]); 
			$fromDate=$filterDate[0];
			$toDate=$filterDate[1];
			if($fromDate!='')
				$fromDate=date("Y-m-d",strtotime($fromDate));
			else
				$fromDate='';
			if($toDate!='')
				$toDate=date("Y-m-d",strtotime($toDate));
			else
				$toDate='';	
			
			if($fromDate != "" && $toDate != "") 
				$accstr=$accstr." and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." >= '".$fromDate."' and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." <= '".$toDate."'";
			elseif($fromDate != "" && $toDate == "")
				$accstr=$accstr."  and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." >= '".$fromDate."' ";
			elseif($toDate != "" && $fromDate == "")
				$accstr=$accstr." and ".tzRetQueryStringDTime($tabFieldname,"YMDDate","-")." <= '".$toDate."' ";
		}
		else if(($filternames_array[$f] == "empBranchLocation") || ($filternames_array[$f] == "empFein") || ($filternames_array[$f] == "empDoublePayrate") || ($filternames_array[$f] == "empsalary")|| ($filternames_array[$f] == "empOvertimeRate") || ($filternames_array[$f] == "empCreateduser")  || ($filternames_array[$f] == "empMuser") )
		{ //These columns have separate filter conditions below
		}
		else if(in_array($filternames_array[$f],$arrDynamicFieldNames))
		{
		}
		else if($filternames_array[$f] == "empssn" && $filtervalues_array[$f] != "")
		{
			$accstr=$accstr." AND REPLACE($filedtable.$fieldname,'-','') like '%$filtervalues_array[$f]%' ";
		}
		else if($filternames_array[$f] == "empEthnicity")
		{
		}
		else if($filternames_array[$f] == "empVeterans")
		{
			if($filtervalues_array[$f] != "ALL")
			{
				$accstr=$accstr." and hp.$fieldname='$filtervalues_array[$f]' ";
			}
		}
		else
		{
			if($filtervalues_array[$f])
				$accstr=$accstr." and $filedtable.$fieldname like '%$filtervalues_array[$f]%' ";
		}
	}//End of Filter conditions

	//$department_dynStr = " AND FIND_IN_SET('".$username."',dt.permission)>0 ";
        $department_dynStr = " AND dt.sno !='0' AND dt.sno IN ({$deptAccesSno}) ";

	/*if($accountingpref!='NO' && chkUserPref($accountingpref,"11"))// New check implemented July 09, 2010 Piyush R chkUserPref
	{
		$department_dynStr = "";
	}*/


	
//Query to fetch all the Employees details
	
	$qryEmp="SELECT el.sno,hc.emp_id,REPLACE(hp.ssn,'-',''),hg.fname,hg.mname,hg.lname,hg.address1,hg.address2,hg.city,hg.state,hg.zip,IF(hg.wphone='---','',hg.wphone),hp.m_status,hp.d_birth,hw.tax,".tzRetQueryStringSTRTODate("hc.date_hire","%m-%d-%Y","Date","-").",hc.location,hc.emptype,hw.fwh,hw.cfwh,hw.swh,hw.cswh,hp.hp_gender,hw.aftaw,hw.caftaw,hw.astaw,hw.castaw,hw.sswh,hw.csswh,hw.mwh,hw.cmwh, hw.localw1_amt,hw.clocalw1_amt,hw.localw2_amt,hw.clocalw2_amt,hw.tnum,hw.tstatetax,hw.payrollpid,hr.client,hd.bankrtno,hd.bankacno,hd.acc1_type,hd.bankname,hd.acc2_bankrtno ,hd.acc2_bankacno,hd.acc2_type,hd.acc2_bankname,hr.pamount,hr.pcurrency ,hr.pperiod ,hc.assign_double,hr.double_brate_amt,hr.double_brate_curr ,hr.double_brate_period,hc.double_rate_amt ,hc.double_brate_curr hrcompenbillcurr,hc.double_rate_period,hr.rate,hr.rateper,hr.rateperiod,hr.jotype,hc.pay_assign,hc.salary,hc.shper,hc.salper,hc.assign_overtime,hc.over_time,hc.ot_currency,hc.ot_period,el.username,hw.federal_exempt,hw.state_exempt,el.empterminated,hw.fstatus,".tzRetQueryStringDTime("el.mtime","Date","/").",el.approveuser,".tzRetQueryStringDTime("el.stime","Date","/").",el.muser,hc.diem_lodging,hc.diem_mie,hc.diem_total,hc.diem_billable,hc.diem_taxable,hc.diem_currency,hc.diem_period,hg.email,hp.ethnicity,hp.veteran_status, hd.delivery_method,hw.state_withholding,hw.fsstatus,hw.flstatus,hw.ljur,dt.depcode,dt.deptname,co.loccode,hw.companycode,hw.tlocaltax,hw.local_exempt,hw.alwh,hw.selALWH,hw.aclwh,hw.selACWH,IF(ep.payperiod_company='Y','".$cpfreq."',ep.payperiod) as  payfrequency
	,hr.otprate_amt,hr.double_prate_amt,hc.job_type,IF((el.tdate='0000-00-00 00:0000' OR el.tdate IS NULL),'',date_format(el.tdate,'%m/%d/%Y')),IF(hc.emp_rehire_date='0000-00-00','',date_format(hc.emp_rehire_date,'%m/%d/%Y')),IF(hc.udate='0000-00-00 00:00:00',date_format(el.mtime,'%m/%d/%Y %H:%i:%s'),date_format(hc.udate,'%m/%d/%Y %H:%i:%s')) FROM hrcon_general hg,hrcon_w4 hw,emp_list el
	LEFT JOIN hrcon_jobs hr ON (el.username=hr.username and hr.ustatus='active')
	LEFT JOIN hrcon_compen hc ON el.username = hc.username  
	LEFT JOIN hrcon_personal hp ON  el.username =hp.username and hp.ustatus = 'active'
	LEFT JOIN hrcon_deposit  hd ON  el.username =hd.username and hd.ustatus = 'active'  
	LEFT JOIN department dt ON hc.dept = dt.sno 
	LEFT JOIN contact_manage co ON hc.location = co.serial_no 
	LEFT JOIN employee_paysetup ep ON el.username = ep.paysetup_username 
	WHERE el.username = hg.username  AND el.username = hw.username  ".$filtercond."  AND el.username =hc.username	AND hw.ustatus = 'active' AND hg.ustatus = 'active' AND hc.ustatus = 'active' AND el.lstatus NOT IN ('DA','INACTIVE') $department_dynStr ";




$qryEmp.=" $groupbyCond ";
	$resEmp=mysql_query($qryEmp,$rptdb);

	$indexLocation = array_search("empBranchLocation",$filternames_array);
	$indexFederalid = array_search("empFein",$filternames_array);
	//$strLocationIds = getStringFilterIds("empBranchLocation",$filtervalues_array[$indexLocation]);
	$strFederalIds = getStringFilterIds("empFein",$filtervalues_array[$indexFederalid]);
	$indexFein = array_search("empFein",$filternames_array);
	$indexCreatedUser = array_search("empCreateduser",$filternames_array);
   	$indexModifiedUser = array_search("empMuser",$filternames_array);
	$indexDiemBillableType = array_search("pdbillable",$filternames_array);
	$indexDiemTaxableType = array_search("pdtaxable",$filternames_array);
	$indexEmpEthnicity = array_search("empEthnicity",$filternames_array);
	$strEmpEthnicityIds = getStringFilterIds("empEthnicity",$filtervalues_array[$indexEmpEthnicity]);

	//Satrt of Fecting the data for dynamic columns
	$dynamic_values = array();

	$sql = "select hct.tot_dollar_amt,hct.comp_contribution,hct.emp_contribution,hct.dollar_amt_deduct,hct.title,hct.username from emp_list el ,hrcon_contribute hct where el.username = hct.username and hct.ustatus = 'active'"; 
	$res = mysql_query($sql,$rptdb); 
	while($row = mysql_fetch_row($res))
	{
		$row[4] = str_replace(" ","___","$$".$row[4]);
		$dynamic_values[$row[4]."_tdollamt"][$row[5]] = $row[0];
		$dynamic_values[$row[4]."_compcontr"][$row[5]] = $row[1];
		$dynamic_values[$row[4]."_empcontr"][$row[5]] = $row[2];
		$dynamic_values[$row[4]."_dollamtded"][$row[5]] = $row[3];
	}
	//End of Fecting the data for dynamic columns

	$fieldnames = array("empDoublePayrate","empsalary","empOvertimeRate");

	//Code  for fetching the data for each column selected and put it in an array-$data
	$v = 1;
	$v1 = 1;
	$bankAccType1="";
	$bankAccType2="";	
	while($arr=mysql_fetch_row($resEmp))
	{
	 	$ii = 0;
		if($v1==0)
			$v1=1;
		$doublePayRateRate = "";
		$perDiemCurrencyPeriod = $arr[83]." / ".$arr[84]; 

		//Data for class
		if(getManage($arr[17])=="Direct")
			$empCalssType="Internal Direct";
		else if(getManage($arr[17])=="Temp/Contract to Direct")
			$empCalssType="Temp/Contract";
		else
			$empCalssType=getManage($arr[17]);	

		$locationFeid = getFederalid($arr[16]);

		//Billable and Taxable types based on Job types
		if($empCalssType == 'Internal Direct' || $empCalssType == 'Direct')
		{
			$diemBillableType = '';
			$diemTaxableType = '';
			$diemLodgingValue = '';
			$diemMIEValue = '';
			$diemTotalValue = '';
		}
		else
		{
			$diemBillableType = $arrayBillableType["$arr[81]"];
			$diemTaxableType = $arrayTaxableType["$arr[82]"];
			$diemLodgingValue = ($arr[78] > 0 && $arr[78] != '')?($arr[78]." ".$perDiemCurrencyPeriod):'';
			$diemMIEValue = ($arr[79] > 0 && $arr[79] != '')?($arr[79]." ".$perDiemCurrencyPeriod):'';
			$diemTotalValue = ($arr[80] > 0 && $arr[80] != '')?($arr[80]." ".$perDiemCurrencyPeriod):'';
		}
		//ends here
		
		//start of code for filtering 
		//filter conditions for  HRM Location and Federal ID as they needs to fetch from 2 different tables
		if($tab=="addr" || ($view=="myreport" && $vrow[0]!=""))
		{
			if(in_array("empBranchLocation",$filternames_array) && ($filtervalues_array[$indexLocation] != '') && ($filtervalues_array[$indexLocation] != "ALL"))
			{
				$empblocation =  (in_array($arr[16],$strLocationIds)) ? "1" : "0";
				if(!$empblocation == "1")
			 		continue;
			}
			if(in_array("empFein",$filternames_array) && ($filtervalues_array[$indexFederalid] != '') && ($filtervalues_array[$indexFederalid] != "ALL"))
			{
				$empFeid =  (in_array($arr[16],$strFederalIds)) ? "1" : "0";
				if(!$empFeid == "1")
			 		continue;
			}
			if(in_array("empCreateduser",$filternames_array) && ($filtervalues_array[$indexCreatedUser] != '') && ($filtervalues_array[$indexCreatedUser] != "ALL"))
			{
				$strIds = getStringFilterIds("empCreateduser",$filtervalues_array[$indexCreatedUser]);
				$usercond =  (in_array($arr[75],$strIds)) ? "1" : "0";
				if(!$usercond == "1")
				continue;
			}	
			if(in_array("empMuser",$filternames_array) && ($filtervalues_array[$indexModifiedUser] != '') && ($filtervalues_array[$indexModifiedUser] != "ALL"))
			{
				$strIds = getStringFilterIds("empMuser",$filtervalues_array[$indexModifiedUser]);
				$musercond =  (in_array($arr[77],$strIds)) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			if(in_array("pdbillable",$filternames_array) && ($filtervalues_array[$indexDiemBillableType] != '') && ($filtervalues_array[$indexDiemBillableType] != "ALL"))
			{
				$musercond = ($arrayBillableType["$filtervalues_array[$indexDiemBillableType]"] == $diemBillableType) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			if(in_array("pdtaxable",$filternames_array) && ($filtervalues_array[$indexDiemTaxableType] != '') && ($filtervalues_array[$indexDiemTaxableType] != "ALL"))
			{
				$musercond = ($arrayTaxableType["$filtervalues_array[$indexDiemTaxableType]"] == $diemTaxableType) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			if(in_array("empEthnicity",$filternames_array) && ($filtervalues_array[$indexEmpEthnicity] != ''))
			{
				$musercond =  (in_array($arr[86],$strEmpEthnicityIds)) ? "1" : "0";
				if(!$musercond == "1")
				continue;
			}
			
			if($v1 == 0)
				$v1 = 1;

			//Filter Condition for dynamic columns
			for($dcol=0;$dcol<count($filternames_array);$dcol++)
			{
				if($v1==1)		
				{
					if(in_array($arrDynamicFieldNames[$dcol],$filternames_array))
					{
						$indexDyncol = array_search($arrDynamicFieldNames[$dcol],$filternames_array);
						$benColval=$filtervalues_array[$indexDyncol];  
						
						if($benColval != "*" && $benColval !='')
						{
							$rangesBen = explode("*",$benColval);
							$minvalueBen = $rangesBen[0];
							$maxvalueBen = $rangesBen[1];
							
							$dbaseVal = $dynamic_values[$arrDynamicFieldNames[$dcol]][$arr[69]];
							
							if($minvalueBen != "" && $maxvalueBen != "")
								$v1 =  (($dbaseVal >=$minvalueBen) && ($dbaseVal <= $maxvalueBen)) ? "1" : "0";
							elseif($minvalueBen != "" && $maxvalueBen == "")
								$v1 = ( ($dbaseVal >= $minvalueBen) ) ? "1" : "0";
							elseif($maxvalueBen != "" && $minvalueBen == "")
								$v1 =  ( ($dbaseVal <= $maxvalueBen) ) ? "1" : "0";
							elseif($maxvalueBen == "" && $minvalueBen == "")
								$v1 =  1; 
						}
					}
					else
						$v1 =  1;
				}
				else
					break;
			}
		}

		//Filter conditions for  Dobletime PayRate,salary $ Overtime Rate as they need to fetch  from 2 tables based on conditions
		$assnJobType=getManage($arr[60]);//assignment type
		$job_type_hc = $arr[106];

		//Assignening value for  Double Time Pay Rate
		if($arr[50]=="Y")			
		{	
			if($assnJobType=="Direct" || $assnJobType=="Internal Direct")
				$doublePayRateRate = $arr[105];
			else
				$doublePayRateRate = $arr[105];
		}
		else
			$doublePayRateRate = $arr[54];

	   //Assignening value for Salary
	   if($arr[61]=="Y")			
		{	
			if($assnJobType=="Direct" || $assnJobType=="Internal Direct")
				$salary = $arr[57];
			else
				$salary = $arr[47];
		}
		else
			$salary = $arr[62];
			
		//Assignening value for Overtime Rate
	   if($arr[65]=="Y")			
		{	
			if($assnJobType=="Direct" || $assnJobType=="Internal Direct")
				$overTimeRate = $arr[104];
			else
				$overTimeRate = $arr[104];
		}
		else
			$overTimeRate = $arr[66];
	
		if($v == 0)
		  $v = 1;
		if($tab == "addr")
		{
			for($j=0;$j<count($fieldnames);$j++)
			{
				if($v == 1)
				{
				switch($fieldnames[$j])
						{
							case 'empDoublePayrate' :
							$values['empDoublePayrate'] = $doublePayRateRate;
							break;
							case 'empsalary' :
							$values['empsalary'] = $salary;
							break;
							case 'empOvertimeRate' :
							$values['empOvertimeRate'] = $overTimeRate;
							break;
						 
						}
					if(in_array($fieldnames[$j],$filternames_array))
					{
						$index = array_search($fieldnames[$j],$filternames_array);
						
						$ranges = explode("*",$filtervalues_array[$index]);
						$minvalue = $ranges[0];
						$maxvalue = $ranges[1]; 
						
						if($minvalue != "" && $maxvalue != "")
							$v =  (($values[$fieldnames[$j]] >=$minvalue) && ($values[$fieldnames[$j]] <= $maxvalue)) ? "1" : "0";
						elseif($minvalue != "" && $maxvalue == "")
							$v = ( ($values[$fieldnames[$j]] >= $minvalue) ) ? "1" : "0";
						elseif($maxvalue != "" && $minvalue == "")
							$v =  (  ($values[$fieldnames[$j]] <= $maxvalue) ) ? "1" : "0";
						elseif($maxvalue == "" && $minvalue == "")
							$v =  1;
					}
					else
						$v = 1;	
	
				}
				else
				break;
			}
		}
		else
		{	
			$values['empDoublePayrate'] = $doublePayRateRate;
			$values['empsalary'] = $salary;
			$values['empOvertimeRate'] = $overTimeRate;
		}
		
		//-------------Code for assigning the each column's data to the $data---------- 
		//Preparing Data for DOB
		$bdate='';
		$arraybdate='';
		if(in_array("empdob",$sortarr))
		{
			if($arr[13]!="00/00/0000" && $arr[13]!="00-00-0000")
			{
				$bdate=explode("-",$arr[13]);
				$arraybdate[0]=$bdate[2];
				$arraybdate[1]=$bdate[0];
				$arraybdate[2]=$bdate[1];
				$birth_date=implode("/",$arraybdate);
				$birthDate=str_replace("-","/",$arr[13]);
			}
			else
				$birthDate="";
		}
		
		//Data for Total Federal Tax Allowances
		if($arr[70]=='Y')
			$totFedtaxallow='Exempt';//$totFedtaxallow=99; modified Piyush R
		else
			$totFedtaxallow=($arr[35]!=0.00)? $arr[35] : "";
		
		//Data for Total State Tax Allowances
		if($arr[71]=='Y')
			$totStatetaxallow='Exempt';//$totStatetaxallow=99; modified Piyush R
		else
			$totStatetaxallow=($arr[36]!=0.00)? $arr[36] : "";
			
			if($arr[98]=='Y')
			$totLocaltaxallow='Exempt';//$totStatetaxallow=99; modified Piyush R
		else
			$totLocaltaxallow=($arr[97]!=0.00)? $arr[97] : "";
	    
	    
		//Data for Gender
		if($arr[22]=="F")
			$gender="Female";
		else if($arr[22]=="M")	
			 $gender="Male"; 
		else
			$gender=""; 

		//PrimayAcctType
		if($arr[41]=="CHECKING")
			$bankAccType1="Chk";
		else if($arr[41]=="SAVINGS")
			$bankAccType1="Sav";
		else
			$bankAccType1="";

		//Acc2AcctType
		if($arr[45]=="CHECKING")
			$bankAccType2="Chk";
		else if($arr[45]=="SAVINGS")
			$bankAccType2="Sav";
		else
			$bankAccType2="";
      if($arr[89] != 0 || $arr[89] != '0') {
       $qury = "select state_abbr from state_codes where state_id='".$arr[89]."'";
	   $res = mysql_query($qury);
	    $stateArr1 = mysql_fetch_array($res);
		$stateArr = $stateArr1[0];
		 }
	   else
	     $stateArr = '';
	   
		
		$values_array = array(
			"empid" => $arr[0],
			"empssn" => $ac_aced->decrypt($arr[2]),   // decrypt the SSN value
			"empfname" => $arr[3], 
			"empmname" => $arr[4] ,
			"emplname" => $arr[5] , 
			"empaddr1" => $arr[6],
			"empaddr2" => $arr[7],
			"empcity" =>  $arr[8],
			"empstate" => $arr[9],
			"empzip" =>   $arr[10],
			"empphone" => $arr[11],
			"empmaritalst" =>$arr[12],
			"empdob" => get_standard_dateFormat($ac_aced->decrypt($birthDate), 'm-d-Y','m/d/Y'),
			"empltax" => $arr[14],
			"emphiredate" => ($arr[15]!="00/00/0000" && $arr[15]!="00-00-0000") ? str_replace("-","/",$arr[15]) : "",
			"empBranchLocation" => trim(getBranchLocationval($arr[16])),
			"empClass" => ($arr[106] != 'Y') ? $empCalssType : $assnJobType ,
			"empFedEmployee" =>($arr[18]!=0.00)? $arr[18] : "" ,
			"empFedCompany" =>  ($arr[19]!=0.00)? $arr[19] : "",
			"empStateEmployee" => $stateArr ,
			"empStateCompany" =>($arr[21]!=0.00)? $arr[21] : "",
			"empgender" => $gender,
			"empfedtaxemployee" =>($arr[23]!=0.00)? $arr[23] : "",
			"empfedtaxcompany" =>($arr[24]!=0.00)? $arr[24] : "",
			"empstatetaxemployee" =>($arr[25]!=0.00)? $arr[25] : "",
			"empstatetaxcompany" =>($arr[26]!=0.00)? $arr[26] : "",
			"empsecurityemployee" =>($arr[27]!=0.00)? $arr[27] : "",
			"empsecuritycompany" =>($arr[28]!=0.00)? $arr[28] : "",
			"empmedicareemployee" =>($arr[29]!=0.00)? $arr[29] : "",
			"empmedicarecompany" =>($arr[30]!=0.00)? $arr[30] : "",
			"emplocalwithhold1employee" =>($arr[31]!=0.00)? $arr[31] : "",
			"emplocalwithhold1company" =>($arr[32]!=0.00)? $arr[32] : "",
			"emplw2employee" =>($arr[33]!=0.00)? $arr[33] : "",
			"emplw2company" =>($arr[34]!=0.00)? $arr[34] : "",
			"empnoallowclaim" =>$totFedtaxallow,
			"empstatetaxallowance" =>$totStatetaxallow,
			"emppayproviderid" =>$arr[37],
			"empFein" =>getFederalId($arr[16]),
			"empAbaAcc1" =>$ac_aced->decrypt($arr[39]),
			"empAccnumberAcc1" =>$ac_aced->decrypt($arr[40]),
			"empAccTypeAcc1" =>$bankAccType1,
			"empBanknameAcc1" =>$arr[42],
			"empAbaAcc2" =>$ac_aced->decrypt($arr[43]),
			"empAccnumberAcc2" =>$ac_aced->decrypt($arr[44]),
			"empAccTypeAcc2" =>$bankAccType2,
			"empBanknameAcc2" =>$arr[46],
			"empDoublePayrate" =>($values['empDoublePayrate']!=0.00) ? $values['empDoublePayrate'] : "" ,
			"empOvertimeRate" =>($values['empOvertimeRate']!=0.00) ? $values['empOvertimeRate'] : ""  ,
			"empsalary" =>($values['empsalary']!=0.00)? $values['empsalary'] : "" ,
			"empStatus" => ($arr[72]=='N') ?"Active" : "Terminated",
			"filingStatus" => $arr[73],
			"empMdate" => ($arr[74]!='00/00/0000') ? $arr[74] : "",
			"empCreateduser" => getOwnerName($arr[75]),
			"empCrateddate" => ($arr[76]!='00/00/0000') ? $arr[76] : "",
			"empMuser" => getOwnerName($arr[77]),
			"pdlodging" => $diemLodgingValue,
			"pdmie" => $diemMIEValue,
			"pdtotal" => $diemTotalValue,
			"pdbillable" => $diemBillableType,
			"pdtaxable" =>  $diemTaxableType,
			"empEmail" =>  $arr[85], //added by swapna for email
			"empEthnicity" =>  getManage($arr[86]), //added by kumar raju for ethnicity
			"empVeterans" =>  ucfirst(getManage($arr[87])), //added by kumar raju for veterans status
			"paychkdelmethod" => ucfirst(getManage($arr[88])), //added by piyush r
			"empWithholdTaxState" => ($arr[89] != 0)? $stateArr[1] :'', //added by piyush r
			"filingStateStatus"=>$arr[90],
			"filingLocalStatus"=>$arr[91],
			"LocalJurisdiction"=>$arr[92],
			"deptcode"=>$arr[93],
			"hrmdept"=>$arr[94],
			"empBranchCode"=>$arr[95],
			"companycode"=>$arr[96],
			"emplocaltaxallowance"=>$totLocaltaxallow,
			"emplocaltaxemployee"=>($arr[99]!=0.00)? $arr[99] : "",
			"payfrequency"=>$arr[103],
			"empTerminatedDate"=>$arr[107],
			"empReHireDate"=>$arr[108],
			"empModifiedDate"=>$arr[109]
		);

		//Preparing the actual data
		if($v == 1 && $v1 == 1)
		{
			for($q=0;$q<$sortarry_count;$q++)
			{
				if(in_array($sortarr[$q],$arrDynamicFieldNames))  //data array for dynamic columns
				{
					if($dynamic_values[$sortarr[$q]][$arr[69]]!="0.00")
						$values_array[$sortarr[$q]] =  $dynamic_values[$sortarr[$q]][$arr[69]];
					else
						$values_array[$sortarr[$q]] = "";
				}

				$variable = $$sortarr[$q];

				if($variable[0]!="")
				{
					$data[$i][$ii] = $values_array[$sortarr[$q]];
					$sslength_array[$sortarr[$q] ] = trim((strlen($values_array[$sortarr[$q] ]) <=strlen($variable[2])) ? strlen($values_array[$sortarr[$q] ]) : (strlen($variable[2])+3));
					$ii++;
				}
			}

			//Condition for each column's length
			if($sortarry_count)
				$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
			
			//Link that will redirect to Employee's summary screen
			$data[$i][$ii]="javascript:showEmp('$arr[0]','$def_appsvr_domain')";
			$ii++;
			
			if(($empid[0]!="") || ($empssn[0]!="") || ($empfname[0]!="") || ($empmname[0]!="") || ($emplname[0]!="") || ($empaddr1[0]!="") || ($empaddr2[0]!="") || ($empcity[0]!="") || ($empstate[0]!="") || ($empzip[0]!="") || ($empphone[0]!="") || ($empmaritalst[0]!="")  || ($empdob[0]!="")  || ($empltax[0]!="") || ($emphiredate[0]!="") || ($empBranchLocation[0]!="") || ($empClass[0]!="") || ($empFedEmployee[0]!="") || ($empFedCompany[0]!="") || ($empStateEmployee[0]!="") || ($empStateCompany[0]!="") || ($empgender[0]!="")|| ($empfedtaxemployee[0]!="") || ($empfedtaxcompany[0]!="")|| ($empstatetaxemployee[0]!="")|| ($empstatetaxcompany[0]!="") || ($empsecurityemployee[0]!="")|| ($empsecuritycompany[0]!="")|| ($empmedicareemployee[0]!="")|| ($empmedicarecompany[0]!="")|| ($emplocalwithhold1employee[0]!="")|| ($emplocalwithhold1company[0]!="")|| ($emplw2employee[0]!="")|| ($emplw2company[0]!="")|| ($empnoallowclaim[0]!="") || ($empstatetaxallowance[0]!="")|| ($emppayproviderid[0]!="")|| ($empFein[0]!="")|| ($empAbaAcc1[0]!="")|| ($empAccnumberAcc1[0]!="")|| ($empAccTypeAcc1[0]!="")|| ($empBanknameAcc1[0]!="")|| ($empAbaAcc2[0]!="")|| ($empAccnumberAcc2[0]!="")|| ($empAccTypeAcc2[0]!="")|| ($empBanknameAcc2[0]!="")|| ($empDoublePayrate[0]!="")|| ($empOvertimeRate[0]!="")|| ($empStatus[0]!="") || ($filingStatus[0]!="") || ($empMdate[0]!="")  || ($empCreateduser[0]!="") || ($empCrateddate[0]!="") || ($empMuser[0]!="") || ($pdlodging[0]!="")|| ($pdmie[0]!="")|| ($pdtotal[0]!="")|| ($pdbillable[0]!="")|| ($pdtaxable[0]!="") || ($empEmail[0]!="") || ($empEthnicity[0]!="") || ($empVeterans[0]!="")||($paychkdelmethod[0]!="")||($empWithholdTaxState[0]!="") || ($empTerminatedDate[0]!="") || ($empReHireDate[0]!="") || ($empModifiedDate[0]!="")) 
			{
				$data[$i][$ii]=$slength;  //if column selected,corresponding length will be assigned to $data
				$ii++;
			}
			else if($countarrDynamicFieldNames) //for Dynamic columns
			{
				  for($looplength = 0;$looplength<$countarrDynamicFieldNames;$looplength++)
				  {
					   $variable = $arrDynamicFieldNames[$looplength];
					   if($variable[0] != "")
					   {
					 		$data[$i][$ii]=$slength;
						  	$ii++;
					   }
				  }//for($looplength = 0;
			}
			$i++;
		}//End of Preparing actual data
   
	}//end of while

	$data=cleanArray($data);
	if($data=="")
	{
		$data=array();
		$data[0][0]="";
		$headval=array();
		$headval[0][0]="";
	}
	$rep_length=$i-1;
	if($format == 'csv')
	{
		$date=date("YmdHis", time());
		$filename = $filename.".".$format;
		$mime = 'application/'.$format;		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Type: $mime; name=$filename");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Transfer-Encoding: binary");
		$dataHeaderCount = count($headval[0]);
		for($t=0;$t<=$dataHeaderCount; $t++)
		{
			$data[0][$t] = trim($headval[0][$t]);
		}
		foreach($data as $row) 
		{
			$row = array_slice($row,0,count($row)-2);
			print '"'.stripslashes(implode('","',$row))."\"\n";
		}
	 } else {
		require("rlibdata.php");
	  }

	if($defaction == "print")
		echo "<script>window.print(); window.setInterval('window.close();', 10000)</script>";

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
?>