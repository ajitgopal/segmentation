<?php
	ob_start();
	require("global_reports.inc");
	$rlib_filename="hrmbenefits.xml";
	require("rlib.inc");
	//session_start();
	/*
	Created Date:May 22, 2009.
	Created By :Prasadd.
	Purpose:To Provide Employee Benifit report.
	Bug Id:4372
	*/
	require_once("functions.inc.php");
	$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	if($format=="")
		$format="html";
	$filename="HrmBenefits";	
	$module="HrmBenefits";
	
	//Take availabkle columns...
	$totalFieldsArr = getTotalFieldNames();
	
	$totalFieldsArrCount = count($totalFieldsArr);
	$totalFieldsKeys = array_keys($totalFieldsArr);
	$totalFieldsNames = array_values($totalFieldsArr);

	//Code for  the report opened from MyReports  
	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$rptdb);
		$vrowdata=mysql_fetch_row($rresult);
		$vrow=explode("|username->",$vrowdata[0]);
		$Analytics_HrmBenifits=$vrow[0];
		$cusername=$vrow[1];
		if(strpos($Analytics_HrmBenifits,"|username->")!=0)
			$Analytics_HrmBenifits=$vrow[0];
			
			
		session_update("cusername");
		session_update("Analytics_HrmBenifits");
			
		$rdata=explode("|",$Analytics_HrmBenifits);
		
	}//MyReport code completed
	else 
	{
		$rdata=explode("|",$Analytics_HrmBenifits);
		$tab=$rdata[8];
	}

	//If the Report  comes from  customize page ,fetching the values and kepping it in array.    
	if($tab=="addr" ||  ($view=="myreport" && $vrow[0]!=""))
	{
		if(!session_is_registered("Analytics_HrmBenifits"))
			session_register("Analytics_HrmBenifits");
		
		$rdata=explode("|",$Analytics_HrmBenifits);
		$sortarr=explode("^",$rdata[9]);
		
		//For sorting order
		if($rdata[13]=="")
		{
			$rep_sortorder="ASC";
			$rep_sortcol = "benifit_empFname";
		}
		else
		{
			$rep_sortorder=$rdata[10];//ASC or DESC
			$sortingorder = $rdata[13];//records sorting priority order
		}
		//Page setup tab in customize window
		$rep_orient=$rdata[0]!="" ? $rdata[0] : "landscape";
		$rep_paper=$rdata[1]!="" ? $rdata[1] : "letter";
		
		//header/footer tab in customize window...
		$rep_company=$rdata[2]!="" ? stripslashes($rdata[2]) : "";
		$rep_header=$rdata[3]!="" ? stripslashes($rdata[3]) : "";
		$rep_title=$rdata[4]!="" ? stripslashes($rdata[4]) : "";
		$rep_date=$rdata[5]!="" ? $rdata[5] : "";
		$rep_page=$rdata[6]!="" ? $rdata[6] : "";
		$rep_footer=$rdata[7]!="" ? stripslashes($rdata[7]) : "";
		
		$sortingorder_array = explode("^",$sortingorder);//records sorting priority order
		
		//column names and their corresponding selected values from the filters
		$filternames_array = explode('^',$rdata[11]);
		$filtervalues_array = explode('^',formateSlashes($rdata[12]));
		
		$dateRange = $rdata[14];// start,end dates filter values
	}
	else
	{
		$sortarr=$totalFieldsKeys;
		
		$sortingorder_array = array("benifit_empFname","benifit_empLname");

		//default page set up tab...
		$rep_orient="landscape";
		$rep_paper="letter";
		
		//default company titles -- header/footer tab in customization
		$rep_company=$companyname;
		$rep_header="Benefits Report";
		$rep_title="All Benefits";
		$rep_date="date";
		$rep_page="pageno";
		$rep_footer="";	

        //default sorting
		$rep_sortorder="ASC";
		$rep_sortcol="benifit_empFname";
		
		$dateRange = "";
		
	}
	$sortarrCount = count($sortarr);
	for($c=0;$c<$sortarrCount;$c++)
	{
		$tempVar = $sortarr[$c];
		$$tempVar = array();
	}
	//Each column array preparation with 0-->field name, 1-->header lines,  3-->display name...
	for($tfCnt=0;$tfCnt < $sortarrCount;$tfCnt++)
	{
		$fieldTemp = $$sortarr[$tfCnt];
		$fieldTemp[0] = $sortarr[$tfCnt];
		$fieldTemp[1] = $totalFieldsArr[$sortarr[$tfCnt]];
		
		$headLine= str_repeat("-",strlen($totalFieldsArr[$sortarr[$tfCnt]]));
		$fieldTemp[2] = $headLine;
		$$sortarr[$tfCnt] =$fieldTemp;
	}
	
	//This variable used in rlibdata.php...
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
	for($q=0 ; $q< $sortarrCount ; $q++)
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
 	
	//Start of Preparing Soring Order Data for each column selected under sort tab
	//$department_dynStr = " AND FIND_IN_SET('".$username."',department.permission)>0 ";
	$department_dynStr = " AND department.sno !='0' AND department.sno IN ({$deptAccesSno}) ";
	//if($accountingpref!='NO' && strpos($accountingpref,'11')>0)
//	if($accountingpref!='NO' && chkUserPref($accountingpref,"11"))// New check implemented July 09, 2010 Piyush R chkUserPref
//	{
//		$department_dynStr = "";
//	}
	//Filter appending starts...
	$filterCondString = "";
	 
	//Starts of checking the Filter  conditions in Main query as per the selected values from the filters for each column
	$filtername_count=count($filternames_array);
	$filterCondString = "";
	$filterBenifits = array();
	for($f=0;$f<$filtername_count;$f++)
	{
		if($filternames_array[$f] != "" && trim($filtervalues_array[$f]) != "")
		{
			if($filternames_array[$f] == "benifit_empFname" || $filternames_array[$f] == "benifit_empInitial" || $filternames_array[$f] == "benifit_empLname" || $filternames_array[$f] == "benTitle")
			{
				$tabFieldname=getserTabName($filternames_array[$f]);
				if($tabFieldname != ".")//this condition has to be removed later when branchname filed is there
					$filterCondString.=" AND ".$tabFieldname." LIKE '%".$filtervalues_array[$f]."%' ";
			}
			else if(trim($filtervalues_array[$f]) != "*" && $filternames_array[$f] == "emphiredate")
			{
				if($filtervalues_array[$f]!='*')
				{
					$tabFieldname=getserTabName($filternames_array[$f]);
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
					   $filterCondString.=" AND ( $dob_formate >='".$fromDate."' AND $dob_formate <='".$toDate."' ) ";
					else if($fromDate!='' && $toDate=='' )
					   $filterCondString.=" AND  $dob_formate >='".$fromDate."' ";
					else if($fromDate=='' && $toDate!='' )
					{
						$filterCondString.=" AND $dob_formate <='".$toDate."' ";
					}
				}
			}
			else if(trim($filtervalues_array[$f]) != "*" && ($filternames_array[$f] == "Start_Date" || $filternames_array[$f] == "End_Date"))
			{
				//do nothing
			}
			else if(trim($filtervalues_array[$f]) != "*")
			{
				//get numeric filter values
				$filterBenifits[$filternames_array[$f]] = $filtervalues_array[$f];
			}
		}
	}
	$filterBenifitsCount = count($filterBenifits);
	//End  of checking the Filter  conditions
	$dateStringStr = "";
	$selected_fdate = "";
	$selected_tdate = "";
	if($dateRange!="" && trim($dateRange)!="*")
	{
		$filterDate=explode("*",$dateRange); 
		$selected_fdate = ($filterDate[0]) ? date("Y-m-d",strtotime($filterDate[0])) : "";
		$selected_tdate = ($filterDate[1]) ? date("Y-m-d",strtotime($filterDate[1])) : "";
	}
	
	//Query to fetch all the Employees details
	$qryBenifits="SELECT el.sno, hg.fname, hg.mname, hg.lname,hb.eartype,hb.max_allowed,hb.earned,hb.used,hb.avail,hb.double_time,hb.rollover,hb.earning_id,hb.accrual_sdate,hb.adjust,hc.timesheet,el.username,".tzRetQueryStringSTRTODate("hc.date_hire","%m-%d-%Y","Date","/")." FROM  emp_list el,hrcon_general hg
	LEFT JOIN hrcon_personal hp ON (hp.username=hg.username AND hp.ustatus='active'),hrcon_compen hc 
	LEFT JOIN department ON (hc.dept=department.sno),hrcon_benifit hb 
	WHERE el.username=hg.username
	AND el.username=hc.username
	AND el.username=hb.username
	AND hg.ustatus='active'
	AND hc.ustatus = 'active'
	AND hb.ustatus='active' AND hb.earning_chk='Y'
	AND el.lstatus NOT IN('DA','INACTIVE') AND el.empterminated='N' $department_dynStr $filterCondString";
	//echo $qryBenifits; 
	$resBenifits=mysql_query($qryBenifits,$rptdb);
	
	$i=1;
	while($arr=mysql_fetch_row($resBenifits))
	{
	 	$ii = 0;
		
		$empTimesheetStatus=$arr[14]=="" ? "N" : $arr[14];//get valid time sheet status for each employee
		$accrual_date = $arr[12];
		/*if(strpos($arr[16],"/") == 0)
		{
			$getdate = explode(" ",$arr[16]);
			$ndate = explode("-",$getdate[0]);
			if($ndate[1]!="" || $ndate[2]!="" || $ndate[0]!="")
				$startDate = $ndate[1]."/".$ndate[2]."/".$ndate[0];
			else
				$startDate = "";
		}
		else
		{
			$currdate = $arr[16];
		}*/

		if($accrual_date=="" || $accrual_date=="0000-00-00")
		{
			$accrual_date=date("Y")."-01-01";
		}
		//select start and end dates based on accruel date present or not..
		if($selected_fdate == "")
		{
			$startDate = $accrual_date;
		}
		else
		{
			$startDate = $selected_fdate;
		}
		if($selected_tdate == "")
		{
			$endDate = date("Y-m-d");
		}
		else
		{
			$endDate = $selected_tdate;
		}
		
		$dateStringStr = $startDate."*".$endDate;
		
		if($empTimesheetStatus == "N")
		{
			$earnedHours = get_vacationEarned($arr[4],$arr[15],$db,$arr[9],$dateStringStr);
			$usedHours = getUsedHours($arr[15],$arr[4],$db,$arr[9],$dateStringStr);
			$availHours = ($arr[13] + $earnedHours) - $usedHours;
			$sumittedHrs = get_TimesheetHours($arr[4],$arr[15],$db,$arr[9],$dateStringStr,'submitted');
			$approvedHrs = get_TimesheetHours($arr[4],$arr[15],$db,$arr[9],$dateStringStr,'approved');
			$rejectedHrs = get_TimesheetHours($arr[4],$arr[15],$db,$arr[9],$dateStringStr,'rejected');
		}
		else
		{
			$earnedHours = $arr[6];
			$usedHours = $arr[7];
			$availHours = ($arr[13] + $earnedHours) - $usedHours;
			$sumittedHrs = 0;
			$approvedHrs = 0;
			$rejectedHrs = 0;
		}
		if($arr[16]!="00/00/0000")
		{
			$displayhireDate = $arr[16];
		}
		else
			$displayhireDate="";
		
		//get start date display format	
		$ndate = explode("-",$startDate);
		if($ndate[1]!="" || $ndate[2]!="" || $ndate[0]!="")
			$dispStartDate = $ndate[1]."/".$ndate[2]."/".$ndate[0];
		else
			$dispStartDate = "";
			
		//get end date display format
		$ndate = explode("-",$endDate);
		if($ndate[1]!="" || $ndate[2]!="" || $ndate[0]!="")
			$dispEndDate = $ndate[1]."/".$ndate[2]."/".$ndate[0];
		else
			$dispEndDate = "";
			
		//Array for all column's data
		$values_array = array(
								"benifit_empFname" => $arr[1],
								"benifit_empInitial" => $arr[2],
								"benifit_empLname" => $arr[3],
								"benTitle" => $arr[4],
								"maxAllowed" => html_tls_specialchars(toDec($arr[5]),ENT_QUOTES),
								"preEarning" => html_tls_specialchars(toDec($arr[13]),ENT_QUOTES),
								"earned" => html_tls_specialchars(toDec($earnedHours),ENT_QUOTES),
								"used" => html_tls_specialchars(toDec($usedHours),ENT_QUOTES),
								"available" => html_tls_specialchars(toDec($availHours),ENT_QUOTES),
								"submittedHours" => ($sumittedHrs!=0.00) ? $sumittedHrs : "0",
								"approvedHours" => ($approvedHrs!=0.00) ? $approvedHrs : "0",
								"rejectedHours" => ($rejectedHrs!=0.00) ? $rejectedHrs : "0",
								"emphiredate" => $displayhireDate,
								"Start_Date" => $dispStartDate,
								"End_Date" => $dispEndDate
							);
								
		$recordValid = 1;
		if($filterBenifitsCount > 0)
		{
			foreach($filterBenifits as $benifitKey=> $benifitVal)
			{
				$minValue = "";
				$maxvalue = "";
				$tempRecordVal = $values_array[$benifitKey];
				$benifitValArr = explode("*",$benifitVal);
				$minValue = $benifitValArr[0];
				$maxvalue = $benifitValArr[1];
				if($minValue!='' && $maxvalue !='')
				{
					$recordValid = ($tempRecordVal >= $minValue && $tempRecordVal <= $maxvalue)? 1:0;
				}
				elseif($minValue!='')
				{
					$recordValid = ($tempRecordVal >= $minValue)? 1:0;
				}
				elseif($maxvalue !='')
				{
					$recordValid = ($tempRecordVal <= $maxvalue)? 1:0;
				}
				else
				{
					$recordValid = 1;
				}
				if($recordValid == 0)
				{
					break;
				}
			}
		}
		if($recordValid == 0)
		{
			continue;
		}
		//Preparing the actual data
		$lenValid = 0;
		for($q=0;$q<$sortarrCount;$q++)
		{
			$variable = $$sortarr[$q];
			if($variable[0]!="")
			{		
				$data[$i][$ii] = $values_array[$sortarr[$q]];
				$sslength_array[$sortarr[$q] ] = trim((strlen($values_array[$sortarr[$q] ]) <=strlen($variable[2])) ? strlen($values_array[$sortarr[$q] ]) : (strlen($variable[2])+3));
				$ii++;
				
				$lenValid = 1;
			}
		}
		//Condition for each column's length
		if($sortarrCount)
			$slength = $sslength_array[$sortarr[0]] ? $sslength_array[$sortarr[0]] : 1;
		
		//Link that will redirect to Employee's summary screen
		//$data[$i][$ii]="javascript:showEmp('$arr[0]')";
		$data[$i][$ii] = "";//for not showing link
		$ii++;
		
		if($lenValid) 
		{
			$data[$i][$ii]=$slength;  //if column selected,corresponding length will be assigned to $data
			$ii++;
		}
		
		$i++;
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
	
	require("rlibdata.php");
	//condition  for print option from main page
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