<?php
	ob_start();
	$rlib_filename="candidateM.xml";
	require("global_reports.inc");
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
	$tab=$rdata[13];
		
	 if($tab=="addr" || ($view=="myreport" && $vrow[0]!="")  || $view=="predef")
	 {
	 	session_update("pagecand");
		//$type=$rdata[0];

		/*if($rdata[1]!="")
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
    	}*/

		$rep_orient=$rdata[5]!="" ? $rdata[5] : "landscape";
		$rep_paper=$rdata[6]!="" ? $rdata[6] : "letter";

		$rep_company=$rdata[7]!="" ? $rdata[7] : "";
		$rep_header=$rdata[8]!="" ? $rdata[8] : "";
		$rep_title=$rdata[9]!="" ? $rdata[9] : "";
		$rep_date=$rdata[10]!="" ? $rdata[10] : "";
		$rep_page=$rdata[11]!="" ? $rdata[11] : "";
		$rep_footer=$rdata[12]!="" ? $rdata[12] : "";

		if($rdata[21]=="")
		{
			$rep_sortorder="ASC";
			$rep_sortcol="Customer";
		}
		else
		{
			$rep_sortorder=$rdata[22];
			$rep_sortcol=$rdata[21];
		}



		$customer[0]=$rdata[0];
		$candidatename[0]=$rdata[1];
		$endclient[0]=$rdata[2];
		$ctype[0]=$rdata[3];
		$salesagent[0]=$rdata[4];


		$sortarr=explode('~',$rdata[14]);

	}
	else
	{
        $sortarr=array("Customer","Candidate_Name","End_client","Type");
        $date1="";
		$date2="";
		$type="All";

		$rep_orient="landscape";
		$rep_paper="letter";

		$rep_company==$companyname;
		$rep_header="Assignments Report";
		$rep_title="Assignments";
		$rep_date="date";
		$rep_page="pageno";
		$rep_footer="";

        $rep_sortorder="ASC";
		$rep_sortcol="Customer";
		
		$customer[0]="Customer";
		$candidatename[0]="Candidate_Name";
		$endclient[0]="End_client";
		$ctype[0]="Type";
		$salesagent[0]="Sales_Agent";
	}

		$customer[1]="Customer";
		$candidatename[1]="Candidate Name";
		$endclient[1]="End Client";
		$ctype[1]="Type";
		$salesagent[1]="Sales Agent";
		
		$customer[2]="--------------------";
		$candidatename[2]="--------------------";
		$endclient[2]="-------------------";
        $ctype[2]="---------";
		$salesagent[2]="------------------------";
		
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
        if($sortarr[$q]=="Customer")
        {
            if($customer[0]!="")
            {
		      $data[0][$k]=$customer[0];
		      $headval[0][$k]=$customer[1];
		      $headval[1][$k]=$customer[2];
		      $k++;
            }
        }
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

        if($sortarr[$q]=="End_client")
        {
            if($endclient[0]!="")
            {
            	$data[0][$k]=$endclient[0];
            	$headval[0][$k]=$endclient[1];
            	$headval[1][$k]=$endclient[2];
            	$k++;
            }
        }
        if($sortarr[$q]=="Type")
        {
            if($ctype[0]!="")
            {
            	$data[0][$k]=$ctype[0];
            	$headval[0][$k]=$ctype[1];
            	$headval[1][$k]=$ctype[2];
            	$k++;
            }
        }
        if($sortarr[$q]=="Sales_Agent")
        {
            if($salesagent[0]!="")
            {
            	$data[0][$k]=$salesagent[0];
            	$headval[0][$k]=$salesagent[1];
            	$headval[1][$k]=$salesagent[2];
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
		$mque="select distinct(emp_list.sno),hrcon_jobs.client,hrcon_general.fname,hrcon_general.lname,hrcon_w4.tax,hrcon_jobs.endclient,CONCAT(hrcon_jobs.rate,'/',hrcon_jobs.rateper),hrcon_jobs.s_date,hrcon_jobs.e_date,hrcon_jobs.sagent from emp_list LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username LEFT JOIN hrcon_jobs ON emp_list.username=hrcon_jobs.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.username = hrcon_jobs.client LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username LEFT JOIN department ON hrcon_compen.dept=department.sno where hrcon_w4.ustatus='active' and hrcon_jobs.ustatus='active' and hrcon_general.ustatus='active' and emp_list.lstatus != 'DA'  and  emp_list.lstatus != 'INACTIVE'   and hrcon_jobs.jtype='OP' AND department.sno !='0' AND department.sno IN ({$deptAccesSno}) AND staffacc_cinfo.type IN('CUST','BOTH') group by emp_list.sno ";

	 // Added AND staffacc_cinfo.type IN('CUST','BOTH') Piyush R June 3, 2010
		/*if($date1!="")
        	$mque.=" and (DATE_ADD(CONCAT(SUBSTRING_INDEX(hrcon_jobs.e_date,'-',-1),'-',SUBSTRING_INDEX(hrcon_jobs.e_date,'-',2)),INTERVAL 0 MONTH) between '".$date1."' and '".$date2."') OR  (DATE_ADD(CONCAT(SUBSTRING_INDEX(hrcon_jobs.s_date,'-',-1),'-',SUBSTRING_INDEX(hrcon_jobs.s_date,'-',2)),INTERVAL 0 MONTH) between '".$date1."' and '".$date2."') group by emp_list.sno";*/


		$mres=mysql_query($mque,$db);
		while($arr=mysql_fetch_row($mres))
		{
            $ii=0;
            
    		if($arr[1]!="" && $arr[1]!="none")
    		{
    			$que1="select cname from staffacc_cinfo where username='$arr[1]'";
    			$res1=mysql_query($que1,$db);
    			$row1=mysql_fetch_row($res1);
    			$customer1=$row1[0];
    	    }
    		else
    		{
    			$customer1="";
    		}

    		if($arr[9]!="" && $arr[9]!="none")
    		{
    			$que2="select name from emp_list where username='".$arr[9]."'";
    			$res2=mysql_query($que2,$db);
    			$row2=mysql_fetch_row($res2);
    			$sname=$row2[0];
    		}
    		else
    		{
    			$sname="";
    		}

    		if($arr[5]!="" && $arr[5]!="none")
    		{
    			$que="select cname from staffacc_cinfo where username='$arr[5]'";
    			$res=mysql_query($que,$db);
    			$row=mysql_fetch_row($res);
    			$client1=$row[0];
    		}
    		else
    		{
    			$client1="";
    		}

            for($q=0;$q<count($sortarr);$q++)
            {
                if($sortarr[$q]=="Customer")
                {

                    if($customer[0]!="")
                    {
    				    $data[$i][$ii]=$customer1;
    				    $sslen1=trim((strlen($customer1) <= strlen($customer[2])) ? strlen($customer1) : (strlen($customer[2])+3));
    				    $ii++;
                    }
                }
                if($sortarr[$q]=="Candidate_Name")
                {
                    if($candidatename[0]!="")
                    {
    				    $data[$i][$ii] = $arr[2];
    				    $sslen2=trim((strlen($arr[2]) <= strlen($candidatename[2])) ? strlen($arr[2]) : (strlen($candidatename[2])+3));
    				    $ii++;
                    }
                }

                if($sortarr[$q]=="End_client")
                {
                    if($endclient[0]!="")
                    {
    				    $data[$i][$ii]=$client1;
    				    $sslen3=trim((strlen($client1) <= strlen($endclient[2])) ? strlen($client1) : (strlen($endclient[2])+3));
    				    $ii++;
                    }
                }
                if($sortarr[$q]=="Type")
                {
                    if($ctype[0]!="")
                    {
    				    $data[$i][$ii]=trim($arr[4]);
    				    $sslen4=trim((strlen($arr[4]) <= strlen($ctype[2])) ? strlen($arr[4]) : (strlen($ctype[2])+3));
    				    $ii++;
                    }
                }
                if($sortarr[$q]=="Sales_Agent")
                {
                    if($salesagent[0]!="")
                    {
    				    $data[$i][$ii]=$sname;
    				    $sslen5=trim((strlen($sname) <= strlen($salesagent[2])) ? strlen($sname) : (strlen($salesagent[2])+3));
    				    $ii++;
                    }
                }

        }

            for($q=0;$q<count($sortarr);$q++)
            {
                if($sortarr[$q]=="Customer")
                {
                    if($customer[0]!="")
                        $slength=$sslen1;
                    break;
                }
                if($sortarr[$q]=="Candidate_Name")
                {
                    if($candidatename[0]!="")
                        $slength=$sslen2;
                    break;
                }

                if($sortarr[$q]=="End_client")
                {
                    if($endclient[0]!="")
                        $slength=$sslen3;
                    break;
                }
                if($sortarr[$q]=="Type")
                {
                    if($ctype[0]!="")
                        $slength=$sslen4;
                    break;
                }
                if($sortarr[$q]=="Sales_Agent")
                {
                    if($salesagent[0]!="")
                        $slength=$sslen5;
                    break;
                }

            }

			$data[$i][$ii]="javascript:showEmp('$arr[0]')";
			$ii++;

			if(($candidatename[0]!="") || ($customer[0]!="") || ($endclient[0]!="") || ($ctype[0]!="")|| ($salesagent[0]!=""))
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
