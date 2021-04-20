<?php
	ob_start();
	require("global_reports.inc");
	$rlib_filename="candidateM.xml";
	require("rlib.inc");
	$deptAccessObj = new departmentAccess();
        $deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	if($format=="")
		$format="html";
	$filename="candidates";
	$module="candidates";
	function seleAtype($ast)
	{
		if($ast=="OP")
			$astyp="Project";
		else if($ast=="OB")
			$astyp="On Bench";
		else if($ast=="OV")
			$astyp="On Vacation";
		return $astyp;
	}
	$e_type="Campaign','Submissions";
	if($view=="myreport")
	{
		$rquery="select reportoptions from reportdata where reportid='$id'";
		$rresult=mysql_query($rquery,$db);
		$vrow=mysql_fetch_row($rresult);
		$pagecand=$vrow[0];
		
		
		
		session_update("pagecand");
		$rdata=explode("|",$pagecand);
	}
	else
	{
		$rdata=explode("|",$pagecand);
		$vrow[0]="|||||||";
	}

	$cmt=count($rdata);
	$tab=$rdata[21];
	
	 if($tab=="addr" || ($view=="myreport" && $vrow[0]!="")  || $view=="predef")
	 {
	 	if(!session_is_registered("pagecand"))
			session_register("pagecand");
		$type=$rdata[0];

		if($rdata[1]!="")
    	{
    		$date=explode('/',$rdata[1]);
            $date1=$date[2]."-".$date[0]."-".$date[1];

    		$date=explode('/',$rdata[2]);
    		$date2=$date[2]."-".$date[0]."-".$date[1];
    	}
    	else
    	{
    		$date1="";
    		$date2="";
    	}

		$rep_orient=$rdata[13]!="" ? $rdata[13] : "landscape";
		$rep_paper=$rdata[14]!="" ? $rdata[14] : "letter";

		$rep_company=$rdata[15]!="" ? stripslashes($rdata[15]) : "";
		$rep_header=$rdata[16]!="" ? stripslashes($rdata[16]) : "";
		$rep_title=$rdata[17]!="" ? stripslashes($rdata[17]) : "";
		$rep_date=$rdata[18]!="" ? $rdata[18] : "";
		$rep_page=$rdata[19]!="" ? $rdata[19] : "";
		$rep_footer=$rdata[20]!="" ? stripslashes($rdata[20]) : "";

		if($rdata[29]=="")
		{
			$rep_sortorder="ASC";
			$rep_sortcol="Candidate_name";
		}
		else
		{
			$rep_sortorder=$rdata[30];
			$rep_sortcol=$rdata[29];
		}



		$candidatepname[0]=$rdata[3];
		$candidatename[0]=$rdata[4];
		$candidateskill[0]=$rdata[5];
		$candidaterate[0]=$rdata[6];
		$candidateavai[0]=$rdata[7];
		$candidatetype[0]=$rdata[8];
		$candidateecamp[0]=$rdata[9];
		$candidatesubm[0]=$rdata[10];
		$candidaterej[0]=$rdata[11];
		$candidateasstype[0]=$rdata[12];
		$candidateemail[0]=$rdata[23];
		$candidatephone[0]=$rdata[24];
		$candidatesource[0]=$rdata[25];
		$candidatedays[0]=$rdata[26];
		$candidateinq[0]=$rdata[27];

		$sortarr=explode('~',$rdata[22]);

	}
	else
	{
        $sortarr=array("Candidate_Name","Candidate_Skill","Candidate_Type","Candidate_Assignment","Candidate_email","Candidate_phone","Candidate_source");
        $date1="";
		$date2="";
		$type="All";

		$rep_orient="landscape";
		$rep_paper="letter";

		$rep_company==$companyname;
		$rep_header="Employee Review Report";
		$rep_title="Employee Review";
		$rep_date="date";
		$rep_page="pageno";
		$rep_footer="Footer";

        $rep_sortorder="ASC";
		$rep_sortcol="Candidate_name";

		$candidatename[0]="Candidate_Name";
		$candidateskill[0]="Candidate_Skill";
		$candidatetype[0]="Candidate_Type";
		$candidateemail[0]="Candidate_email";
		$candidateasstype[0]="Candidate_Assignment";
		$candidatesource[0]="Candidate_source";
		$candidatephone[0]="Candidate_phone";

	}

		$candidatename[1]="Employee Name";
		$candidateskill[1]="Skills Name";
		$candidatetype[1]="Type";
		$candidateasstype[1]="Assignment Type";
		$candidateemail[1]="Email";
		$candidatephone[1]="Phone Number";
		$candidatesource[1]="Source";

		$candidatename[2]="-----------------------------";
		$candidateskill[2]="-----------------";
		$candidatetype[2]="-------------";
		$candidateasstype[2]="---------------";
		$candidateemail[2]="-----------------------------";
		$candidatesource[2]="----------------------";
		$candidatephone[2]="-------------------";

	$rep_sortcolno="";
	if($sortarr[0]!="")
	{
	   for($q=0;$q<count($sortarr);$q++)
	   {
		  if($sortarr[$q]==$rep_sortcol)
		  {
			 $rep_sortcolno=$q;
		  }
        }
	}

	$k=0;
    for($q=0;$q<count($sortarr);$q++)
    {
        if($sortarr[$q]=="Candidate_Name")
        {
            if($candidatename[0]!="")
            {
        		$data[0][$k]=$candidatename[0];
        		$headval[0][$k]=$candidatename[1];
        		$headval[1][$k]=$candidatename[2];
        		$k++;
            }
        }
        if($sortarr[$q]=="Candidate_Skill")
        {
            if($candidateskill[0]!="")
            {
		      $data[0][$k]=$candidateskill[0];
		      $headval[0][$k]=$candidateskill[1];
		      $headval[1][$k]=$candidateskill[2];
		      $k++;
            }
        }
        if($sortarr[$q]=="Candidate_Type")
        {
            if($candidatetype[0]!="")
            {
            	$data[0][$k]=$candidatetype[0];
            	$headval[0][$k]=$candidatetype[1];
            	$headval[1][$k]=$candidatetype[2];
            	$k++;
            }
        }
        if($sortarr[$q]=="Candidate_Assignment")
        {
        	if($candidateasstype[0]!="")
        	{
        		$data[0][$k]=$candidateasstype[0];
        		$headval[0][$k]=$candidateasstype[1];
        		$headval[1][$k]=$candidateasstype[2];
        		$k++;
        	}
        }
		if($sortarr[$q]=="Candidate_email")
        {
        	if($candidateemail[0]!="")
        	{
        		$data[0][$k]=$candidateemail[0];
        		$headval[0][$k]=$candidateemail[1];
        		$headval[1][$k]=$candidateemail[2];
        		$k++;
        	}
        }
		if($sortarr[$q]=="Candidate_phone")
        {
        	if($candidatephone[0]!="")
        	{
        		$data[0][$k]=$candidatephone[0];
        		$headval[0][$k]=$candidatephone[1];
        		$headval[1][$k]=$candidatephone[2];
        		$k++;
        	}
        }
		if($sortarr[$q]=="Candidate_source")
        {
        	if($candidatesource[0]!="")
        	{
        		$data[0][$k]=$candidatesource[0];
        		$headval[0][$k]=$candidatesource[1];
        		$headval[1][$k]=$candidatesource[2];
        		$k++;
        	}
        }
    }
	if($k!=0)
	{
		$data[0][$k]="link";
		$k++;
		$data[0][$k]="link_length";
	}
	$i=1;

      	 $mque="select distinct(emp_list.sno),CONCAT(hrcon_general.fname,' ',hrcon_general.mname,' ',hrcon_general.lname),emp_list.name,hrcon_general.email,hrcon_general.wphone,hrcon_jobs.jtype,hrcon_w4.tax,hrcon_general.username,hrcon_skills.skillname,emp_list.username from emp_list LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username LEFT JOIN hrcon_jobs ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username LEFT JOIN department ON hrcon_compen.dept=department.sno LEFT JOIN hrcon_skills ON hrcon_general.username=hrcon_skills.username where (DATE_ADD(".tzRetQueryStringSTRTODate("emp_list.avail","%m-%d-%Y","YMDDate","-").",INTERVAL hrcon_compen.rev_period MONTH)<=CURRENT_DATE and emp_list.estatus='ER') and hrcon_w4.ustatus='active' and hrcon_jobs.ustatus IN ('active','pending') and hrcon_general.ustatus='active' and emp_list.lstatus != 'DA'  and  emp_list.lstatus != 'INACTIVE'  AND department.sno !='0' AND department.sno IN ({$deptAccesSno})  group by emp_list.sno";

		$mres=mysql_query($mque,$db);
		while($arr=mysql_fetch_row($mres))
		{
            $ii=0;
			
			$que="select skillname from hrcon_skills where username='".$arr[9]."' and ustatus='active' and (lastused='Current' or skilllevel='Expert')";
    		$rs=mysql_query($que,$db);
    		$skills="";
    		while($dat=mysql_fetch_row($rs))
    			$skills.=$dat[0].",";
    		$skills=rtrim($skills,",");
    		
            for($q=0;$q<count($sortarr);$q++)
            {
                if($sortarr[$q]=="Candidate_Name")
                {
                    if($candidatename[0]!="")
                    {
    				    $data[$i][$ii] = $arr[1];
    				    $sslen2=trim((strlen($arr[1]) <= strlen($candidatename[2])) ? strlen($arr[1]) : (strlen($candidatename[2])+3));
    				    $ii++;
                    }
                }
                if($sortarr[$q]=="Candidate_Skill")
                {

                    if($candidateskill[0]!="")
                    {
    				    $data[$i][$ii]=$skills;
    				    $sslen3=trim((strlen($skills) <= strlen($candidateskill[2])) ? strlen($skills) : (strlen($candidateskill[2])+3));
    				    $ii++;
                    }
                }
                if($sortarr[$q]=="Candidate_Type")
                {
                    if($candidatetype[0]!="")
                    {
    				    $data[$i][$ii]=$arr[6];
    				    $sslen6=trim((strlen($arr[6]) <= strlen($candidatetype[2])) ? strlen($arr[6]) : (strlen($candidatetype[2])+3));
    				    $ii++;
                    }
                }


            if($sortarr[$q]=="Candidate_Assignment")
            {
                if($candidateasstype[0]!="")
                {
				    $astype=seleAtype($arr[5]);
				    $data[$i][$ii]=$astype;
				    $sslen10=trim((strlen($astype) <= strlen($candidateasstype[2])) ? strlen($astype) : (strlen($candidateasstype[2])+3));
				    $ii++;
                }
            }
			if($sortarr[$q]=="Candidate_email")
                {
                    if($candidateemail[0]!="")
                    {
    				    $data[$i][$ii]=$arr[3];
    				    $sslen11=trim((strlen($arr[3]) <= strlen($candidateemail[2])) ? strlen($arr[3]) : (strlen($candidateemail[2])+3));
				    $ii++;
                    }
                }
			if($sortarr[$q]=="Candidate_phone")
                {
                    if($candidatephone[0]!="")
                    {
    				    $data[$i][$ii]=$arr[4];
    				    $sslen12=trim((strlen($arr[4]) <= strlen($candidatephone[2])) ? strlen($arr[4]) : (strlen($candidatephone[2])+3));
    				    $ii++;
                    }
                }
			if($sortarr[$q]=="Candidate_source")
                {
                    if($candidatesource[0]!="")
                    {
    				    $data[$i][$ii]=$arr[2];
    				    $sslen13=trim((strlen($arr[2]) <= strlen($candidatesource[2])) ? strlen($arr[2]) : (strlen($candidatesource[2])+3));
    				    $ii++;
                    }
                }
        }

            for($q=0;$q<count($sortarr);$q++)
            {
                if($sortarr[$q]=="Candidate_Name")
                {
                    if($candidatename[0]!="")
                        $slength=$sslen2;
                    break;
                }
                if($sortarr[$q]=="Candidate_Skill")
                {
                    if($candidateskill[0]!="")
                        $slength=$sslen3;
                    break;
                }
                if($sortarr[$q]=="Candidate_Type")
                {
                    if($candidatetype[0]!="")
                        $slength=$sslen6;
                    break;
                }

                if($sortarr[$q]=="Candidate_Assignment")
                {
                    if($candidateasstype[0]!="")
                        $slength=$sslen10;
                    break;
                }
				if($sortarr[$q]=="Candidate_email")
                {
                    if($candidateemail[0]!="")
                        $slength=$sslen11;
                    break;
                }
				if($sortarr[$q]=="Candidate_phone")
                {
                    if($candidatephone[0]!="")
                        $slength=$sslen12;
                    break;
                }
				if($sortarr[$q]=="Candidate_source")
                {
                    if($candidatesource[0]!="")
                        $slength=$sslen13;
                    break;
                }
            }

			$data[$i][$ii]="javascript:showEmp('$arr[0]')";
			$ii++;

			if(($candidatepname[0]!="") || ($candidatename[0]!="") || ($candidateskill[0]!="") || ($candidaterate[0]!="") || ($candidateavai[0]!="") || ($candidatetype[0]!="") || ($candidateecamp[0]!="") || ($candidatesubm[0]!="") || ($candidaterej[0]!="") || ($candidateasstype[0]!="") || ($candidateemail[0]!="") || ($candidatephone[0]!="")|| ($candidatesource[0]!="")|| ($candidatedays[0]!="")|| ($candidatedays[0]!=""))
			{

				$data[$i][$ii]=$slength;
    			$ii++;
			}

   			$i++;

  }


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

