<?php
  	require("global.inc");
	require_once($akken_psos_include_path."commonfuns.inc");
	require_once('credential_management/class.credentials.php');
	require_once('shift_schedule/crm_schedule_db.php');
 
	// Get current timestamp value and assign to candrn, this variable should be passed throug all pages where ever we are navigating. This is for dynamic session. PLS DONT USE $candrn anywhere
	$candrn=strtotime("now");
	$module = $_GET['module'];
	$_SESSION['CRMAdminModule'.$candrn]=$module;
	if($resstat=="new")
	{
		$_SESSION[candpage1.$candrn]="||||";
		$_SESSION[candpage2.$candrn]="|||||||||";
		$_SESSION[candpage3.$candrn]="|";
		$_SESSION[candpage4.$candrn]="";
		$_SESSION[candpage5.$candrn]="";
		$_SESSION[candpage6.$candrn]="";
		$_SESSION[candpage7.$candrn]="";
		$relocate="No";
		//$_SESSION[candpage8.$candrn]="||||||USD|YEAR|On-Site";
		$_SESSION[candpage9.$candrn]="other";
		$_SESSION[candpage10.$candrn]="";
		$_SESSION[candpage111.$candrn]="";
		$_SESSION[candpage12.$candrn]="";
		$_SESSION[candpage14.$candrn]="";
		$_SESSION[resname.$candrn]="";
		$_SESSION[resumesno.$candrn]="";
		$_SESSION[ldndis.$candrn]="";
		$_SESSION[candpage2.$candrn."Roles"]="";
		$_SESSION[candpage15.$candrn]="";
		$_SESSION[availsess.$candrn]="";
	}
	else
	{
			if($posid!="")
				$_SESSION[posid.$candrn]=$posid;

			if($shiftSno!="")
				 $_SESSION[shiftSno.$candrn]=$shiftSno;

			$table="candidate";
	
			$que="select username,supid ,sutype,resid,cl_status,avail,candid,deptid,CONCAT(fname,' ',lname) as candName from candidate_list where sno=".$cno;
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);

			$cl_avail=$row[5];

			$cl_deptid = $row[7];
			$cl_name = $row[8];
			// Adding Department Permission 
			$deptAccessObj = new departmentAccess();
			$deptName = $deptAccessObj->getDepartmentName($cl_deptid);
			$deptUsrAccess = $deptAccessObj->getDepartmentUserAccess($username,$cl_deptid,"'FO'"); 
			if (!$deptUsrAccess && $module =="CRM") {
				$deptAlertMsg = $deptAccessObj->displayPermissionAlertMsg($deptName);
				?>
				<html>
				<head>
				<title>Candidate<?php echo " - ".str_replace("\\","",$cl_name); ?> </title>
				<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
				<script type='text/javascript'> 
				alert("<?php echo $deptAlertMsg;?>");
				window.close();
				</script>				
				</head>
				</html>
				<?php exit(); 			
			}         
            /*sanghamitra : get the candidate employee relation column ie. candid column value */
			$cand_emp_rel=$row[6];
            		$_SESSION['candid'.$candrn]=$cand_emp_rel;
			
			$candPrefStatusVal = $row[4];//This is for status field in preference tab.
			
			//include($app_inc_path."custom/getcustomfields.php");			
			$candid_sno=substr($row[0],4);
			$udfvalues = getUDFValues(3, $candid_sno);
			
			$_SESSION['candpageudf'.$candrn] = $udfvalues;
                        //$_SESSION['candemppageudf'.$candrn] = $udfvalues;// [#814368] CRM - CANDIDATE AND HRM - EMPLOYEE COMMUNICATION PURPOSE

			$_SESSION[conusername.$candrn]=$row[0];
			$_SESSION[conid.$candrn]=$row[0];
			//$_SESSION[conid.$candrn]="cand".$cno;
			
			// Temporary session variable to compare the data in db and user entered. Update table for those tabs whose data is modified by user
			$_SESSION[tconusername.$candrn]=$_SESSION[conusername.$candrn];
			$_SESSION[tconid.$candrn]=$_SESSION[conid.$candrn];
			
			$_SESSION[resname.$candrn]="";
			$_SESSION[resumesno.$candrn]=$row[3];
			
			//$_SESSION[tresname.$candrn]=$_SESSION[resname.$candrn];
			$_SESSION[tresumesno.$candrn]=$_SESSION[resumesno.$candrn];			

			$currentGroupIds = getCRMGroupIds($cno, 'Candidate');	//getting candidate selected groupids with comma separated.
			
    		$query="SELECT sno,username,fname,mname,lname,email,profiletitle,prefix,cg_source,cg_sourcetype,alternate_email,other_email,jobcatid,nickname,deptid FROM ".$table."_general WHERE username='".$_SESSION[conusername.$candrn]."'";
    		$dres=mysql_query($query,$db);
    		$data=mysql_fetch_row($dres);
    		$_SESSION[candpage1.$candrn]=$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7]."|".$data[8]."|".$data[9]."|".$data[10]."|".$data[11]."|".$data[12]."|".$currentGroupIds."|".$cno."|flase|".$data[13]."|".$data[14];
			
			$_SESSION[tcandpage1.$candrn]=$_SESSION[candpage1.$candrn];

    		$ProfileTitle="";
			if($data[6]!="")
				$ProfileTitle=" (".$data[6].")"; 

			$_SESSION[real_name.$candrn]=$data[2]." ".$data[3]." ".$data[4].$ProfileTitle;
    		$_SESSION[real_name.$candrn]=html_tls_entities($_SESSION[real_name.$candrn]);
			
			$_SESSION[treal_name.$candrn]=$_SESSION[real_name.$candrn];

			$query="select sno,address1,address2,city,state,country,zip,hphone,wphone,mobile,fax,CONCAT_WS('-',cphone,cmobile,cfax,cemail),other,hphone_extn,wphone_extn,other_extn from ".$table."_general where username='".$_SESSION[conusername.$candrn]."'";
    		$dres=mysql_query($query,$db);
    		$data=mysql_fetch_row($dres);
    		$_SESSION[candpage2.$candrn]=$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7]."|".$data[8]."|".$data[9]."|".$data[10]."|".$row[2]."-".$row[1]."|".$data[11]."|".$data[12]."|".$data[13]."|".$data[14]."|".$data[15];

			$_SESSION[tcandpage2.$candrn]=$_SESSION[candpage2.$candrn];
			
			// Setting candidate role session.
			 $OBJ_Cand_Role = new CommissionRoles($db, $username, '', 'CRMCandidate');
			 
			 $_SESSION[candpage2.$candrn."Roles"] = $OBJ_Cand_Role->getEntityRoles($cno);
			
			
    		$query="select objective,summary,pstatus,ifother ,addinfo, availsdate, availedate,IF( str_to_date(".tzRetQueryStringDTime('now()','YMDDate','-').",'%Y-%m-%d') >= availsdate,'immediate',availsdate)  from ".$table."_prof where username='".$_SESSION[conusername.$candrn]."'";    							
			$dres=mysql_query($query,$db);
    		$datapro=mysql_fetch_row($dres);

			if($cl_avail=="Inactive")
				$datapro[5]="inactive";

    		$_SESSION[candpage3.$candrn]=$datapro[0]."|".$datapro[1];


			$_SESSION[tcandpage3.$candrn]=$_SESSION[candpage3.$candrn];
			

    		$query="select skillname,lastused,skilllevel,skillyear,sno,manage_skills_id, if( manage_skills_id =0, 'Parsed', 'Managed' ) AS skill_type from ".$table."_skills where username='".$_SESSION[conusername.$candrn]."' order by skill_type asc, skillname";

    		$dres=mysql_query($query,$db);
    		$_SESSION[candpage4.$candrn]="";
    		while($data=mysql_fetch_row($dres))
    		{
    			if($_SESSION[candpage4.$candrn]=="")

    				$_SESSION[candpage4.$candrn]=$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5];

    			else

    				$_SESSION[candpage4.$candrn].="^".$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5];

    		}
			
		// Skill Department
    		$_SESSION[skill_department.$candrn]="";
    		$queryDept="SELECT GROUP_CONCAT(dept_cat_spec_id) AS dept_id FROM candidate_skill_cat_spec WHERE username='".$_SESSION[conusername.$candrn]."' AND type='joskilldept'";

    		$resultDept=mysql_query($queryDept,$db);
    		while ($rowDept = mysql_fetch_array($resultDept)) {
    			$_SESSION[skill_department.$candrn]= $rowDept[0];
    		}
			//Skill Categories
    		$_SESSION[skill_category.$candrn]="";

    		$queryCatt="SELECT GROUP_CONCAT(dept_cat_spec_id) AS dept_id FROM candidate_skill_cat_spec WHERE username='".$_SESSION[conusername.$candrn]."' AND type='jobskillcat'";

    		$resultCatt=mysql_query($queryCatt,$db);
    		while ($rowCatt = mysql_fetch_array($resultCatt)) {
    			$_SESSION[skill_category.$candrn]= $rowCatt[0];
    		}
			
			//Skill Specialities
			$_SESSION[skill_speciality.$candrn]="";

			$querySpty="SELECT GROUP_CONCAT(dept_cat_spec_id) AS dept_id FROM candidate_skill_cat_spec WHERE username='".$_SESSION[conusername.$candrn]."' AND type='jobskillspeciality'";

    		$resultSptyt=mysql_query($querySpty,$db);
    		while ($rowSptyt = mysql_fetch_array($resultSptyt)) {
    			$_SESSION[skill_speciality.$candrn]= $rowSptyt[0];
    		}

			$_SESSION[tcandpage4.$candrn]=$_SESSION[candpage4.$candrn];
			
    		$query="select heducation,educity,edustate,educountry,edudegree_level,edudate,edu_year,edu_month from ".$table."_edu where username='".$_SESSION[conusername.$candrn]."' ORDER BY edu_year DESC,edu_month DESC";
    		$dres=mysql_query($query,$db);
    		$_SESSION[candpage5.$candrn]="";
    		while($data=mysql_fetch_row($dres))
    		{
    			if($_SESSION[candpage5.$candrn]=="")
    				$_SESSION[candpage5.$candrn]=$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7];
    			else
    				$_SESSION[candpage5.$candrn].="^".$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7];
    		}
			
			$_SESSION[tcandpage5.$candrn]=$_SESSION[candpage5.$candrn];
			
    		$query="select cname,ftitle,wdesc,sdate,edate,city,state,country,csno,sno,compensation_beginning,leaving_reason,sdate_year,sdate_month,edate_year,edate_month from ".$table."_work where username='".$_SESSION[conusername.$candrn]."'";
    		$dres=mysql_query($query,$db);
    		$_SESSION[candpage6.$candrn]="";
    		while($data=mysql_fetch_row($dres))
    		{
    			if($_SESSION[candpage6.$candrn]=="")
    				$_SESSION[candpage6.$candrn]=$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7]."|".$data[8]."|".$data[9]."|".$data[10]."|".$data[11]."|".$data[12]."|".$data[13]."|".$data[14]."|".$data[15];
    			else
    				$_SESSION[candpage6.$candrn].="^".$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7]."|".$data[8]."|".$data[9]."|".$data[10]."|".$data[11]."|".$data[12]."|".$data[13]."|".$data[14]."|".$data[15];
    		}
			
			$_SESSION[tcandpage6.$candrn]=$_SESSION[candpage6.$candrn];
	
			$sql = "select desirejob, desirelocation, desirestatus, resourcetype, wtravle, ptravle, tcomments, wlocate, city, state, country, lcomments, tmax, dmax, ccomments, distributename, CONCAT(min_salary,'-',max_salary) as amount, currency, period, compcomments, rperiod, rcurrency, pramount, poamount, iramount, ioamount, aramount, aoamount from candidate_pref where username='".$_SESSION[conusername.$candrn]."'";
			$pres=mysql_query($sql,$db);
			$pdata=mysql_fetch_assoc($pres);
		
			$datapro[2]=$datapro[2]."^".$datapro[3];
			
			$pdata['desirejob']=($pdata['desirejob']=="")?"||":$pdata['desirejob'];
			$pdata['desirestatus']=($pdata['desirestatus']=="")?"|":$pdata['desirestatus'];
        
			$_SESSION[candpage7.$candrn]=$pdata['desirejob']."|".$pdata['desirestatus']."|".$pdata['rcurrency']."|".$pdata['rperiod']."|".$pdata['pramount']."|".$pdata['poamount']."|".$pdata['iramount']."|".$pdata['ioamount']."|".$pdata['aramount']."|".$pdata['aoamount']."|".$pdata['desirelocation']."|".$datapro[5]."|".$datapro[6]."|".$datapro[7]."|".$datapro[2]."|".$pdata['wtravle']."|".$pdata['city']."|".$pdata['state']."|".$pdata['country']."|".$pdata['ptravle']."|".$pdata['tcomments']."|".$pdata['wlocate']."|".$pdata['lcomments']."|".$pdata['tmax']."|".$pdata['dmax']."|".$pdata['ccomments']."|".$pdata['amount']."|".$pdata['currency']."|".$pdata['period']."|".$pdata['compcomments']."|".$pdata['resourcetype']."|".$candPrefStatusVal;
			
			$_SESSION[tcandpage7.$candrn]=$_SESSION[candpage7.$candrn];
			
			$_SESSION[ldndis.$candrn]=$pdata['distributename'];
			
			$_SESSION[tldndis.$candrn]=$_SESSION[ldndis.$candrn];

   			$query="select pstatus,ifother from ".$table."_prof where username='".$_SESSION[conusername.$candrn]."'";
    		$dres=mysql_query($query,$db);
    		while($data=mysql_fetch_row($dres))
    		{
    			$_SESSION[candpage9.$candrn]=$data[0]."|".$data[1]."|".$data[2];
    		}
			
			$_SESSION[tcandpage9.$candrn]=$_SESSION[candpage9.$candrn];
			
    		$query="select affcname,affrole,affsdate,affedate from ".$table."_aff where username='".$_SESSION[conusername.$candrn]."'";
    		$dres=mysql_query($query,$db);
    		$_SESSION[candpage10.$candrn]="";
    		while($data=mysql_fetch_row($dres))
    		{
    			if($_SESSION[candpage10.$candrn]=="")
    				$_SESSION[candpage10.$candrn]=$data[0]."|".$data[1]."|".$data[2]."|".$data[3];
    			else
    				$_SESSION[candpage10.$candrn].="^".$data[0]."|".$data[1]."|".$data[2]."|".$data[3];
    		}
			
			$_SESSION[tcandpage10.$candrn]=$_SESSION[candpage10.$candrn];
			
    		$query="select addinfo,search_tags from ".$table."_prof where username='".$_SESSION[conusername.$candrn]."'";
    		$dres=mysql_query($query,$db);
    		while($data=mysql_fetch_row($dres))
    		{
    			$_SESSION[candpage111.$candrn]=$data[0]."|".$data[1];
    		}
			
			$_SESSION[tcandpage111.$candrn]=$_SESSION[candpage111.$candrn];
			
			//modified
        	$query="select name,company,title,phone,secondary,mobile,email,rship,csno,sno,notes,doc_id from ".$table."_ref where username='".$_SESSION[conusername.$candrn]."'";
    		$dres=mysql_query($query,$db);
    		$_SESSION[candpage12.$candrn]="";
    		while($data=mysql_fetch_row($dres))
    		{
				if($data[8]>0)
				{
					$cque="SELECT csno FROM staffoppr_contact WHERE sno=".$data[8];
					$cres=mysql_query($cque,$db);
					$crow=mysql_fetch_row($cres);
					$comp_sno=$crow[0];
				}

				$getrefdoc_sql = "SELECT docname,body,doctype FROM contact_doc WHERE sno = '".$data[11]."'";
				$getrefdoc_rs = mysql_query($getrefdoc_sql,$db);
				$getrefdoc_data = mysql_fetch_row($getrefdoc_rs);
				
				$attach_folder	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$isDirEx	= $WDOCUMENT_ROOT."/".$attach_folder;

				if(!is_dir($isDirEx))
					mkdir($isDirEx,0777);

				$tfile		= generatePin("num").time();
				$file		= $isDirEx."/".$tfile;
				$fp		= fopen($file,"w");
				fwrite($fp, $getrefdoc_data[1]);
				fclose($fp);
			
				$getFileSize		= filesize($file);
						
				$avar	= $attach_folder . '##AKKEN##' . $getrefdoc_data[0] . '##AKKEN##' . $tfile . '##AKKEN##' . $getFileSize. '##AKKEN##' . $elements[12];
				$old_doc_id = $elements[12];

    			//if($_SESSION[candpage12.$candrn]=="")
    				//$_SESSION[candpage12.$candrn]=$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7]."|".$data[8]."|".$comp_sno."|".$data[9]."|".$data[10]."|".$data[11]."|".$avar;
    			//else
    				//$_SESSION[candpage12.$candrn].="^".$data[0]."|".$data[1]."|".$data[2]."|".$data[3]."|".$data[4]."|".$data[5]."|".$data[6]."|".$data[7]."|".$data[8]."|".$comp_sno."|".$data[9]."|".$data[10]."|".$data[11];    			
			}
			
			
			//$_SESSION[tcandpage12.$candrn]=$_SESSION[candpage12.$candrn];
			
			

   		    $query="select res_name,markadd,filetype from con_resumes where sno = '".$_SESSION[resumesno.$candrn]."'";    
    		$dres=mysql_query($query,$db);
    		$drow=mysql_fetch_row($dres);
    		if(mysql_num_rows($dres)>0)
    		{
    			if($drow[1]=="" && $drow[0]!="")
    				$_SESSION[resname.$candrn]=$drow[0].".txt";
    			else
    				$_SESSION[resname.$candrn]=$drow[1];
    		
    		}
			$_SESSION[tresname.$candrn]=$_SESSION[resname.$candrn];

			// REDUCING SESSION VARIABLES FOR CANDIDATES. BELOW VARIABLE NOT IN USE, SO COMMENTING BELOW CODE
			// $_SESSION['filetype']=$drow[2];

			if($_SESSION[candpage7.$candrn]!="")
				$relocate="Yes";
			else
				$relocate = "No";

		// FOR CANDIDATE CREDENTIALS
		$objCredentialDetails	= new Credentials();

		$_SESSION[candpage14.$candrn]	= $objCredentialDetails->getCandidateCredentialSession($_SESSION[conid.$candrn]);

		$_SESSION[tcandpage14.$candrn]	= $_SESSION[candpage14.$candrn];
		
		
		//FOR SHIFT SCHEDULING AVAILABILITY 
	
		if($datapro[5] == 'immediate')
		{
			$sm_avail_date = '';
			$sm_avail_end_date = '';
			$sm_avail_status = 'immediate';
		}
		else if($datapro[5] == 'inactive')
		{
			$sm_avail_date = '';
			$sm_avail_end_date = '';
			$sm_avail_status = 'inactive';
		}
		else if($datapro[5] != '')
		{
			$sm_avail_date = $datapro[5];
			$sm_avail_end_date = $datapro[6];
			$sm_avail_status = 'other';
		}
		else
		{
			$sm_avail_date = '';
			$sm_avail_end_date = '';
			$sm_avail_status = 'immediate';
		}
		
		
		$sm_avail_date_format = $sm_avail_date;
		if($sm_avail_date != "" && $sm_avail_date != "0-0-0")
			$sm_avail_date_format = date("m/d/Y",strtotime($sm_avail_date));
			
		$sm_str = $sm_avail_status."|".$sm_avail_date_format."|";
		
		//GETTIGN SM AVAILABILITY TIMNEFRAME DETAILS
		$previousDate = date("Y-m-d",strtotime("-2 months",strtotime(date("Y-m-d"))));
		$objScheduleDetails	= new CandidateSchedule();
		$sm_str .= $objScheduleDetails->getTimeFrameDetails($_SESSION[conid.$candrn],'candidates',$previousDate,'');		
		
		$_SESSION[candpage15.$candrn] = $sm_str;
		
		//common session for preference tab and availability tab
		$sm_avail_date_format_avail = $sm_avail_date;
		if($sm_avail_date != "" && $sm_avail_date != "0-0-0")
			$sm_avail_date_format_avail = date("m-d-Y",strtotime($sm_avail_date));
			
		$sm_avail_end_date_format_avail = $sm_avail_end_date;
		if($sm_avail_end_date != "" && $sm_avail_end_date != "0-0-0")
			$sm_avail_end_date_format_avail = date("m-d-Y",strtotime($sm_avail_end_date));	
		$_SESSION[availsess.$candrn] = $sm_avail_status."|".$sm_avail_date_format_avail."|".$sm_avail_end_date_format_avail;
    }
    
    if($dest == "" || $dest==13)
        $dest = 0;
        
	if($resstat=="new")
	{
		Header("Location:conreg1.php?resstat=".$resstat."&proid=".$proid."&candrn=".$candrn."&module=".$module);
	}
	else
	{
		
		if($ptype != "")
			Header("Location: revconreg$dest.php?addr=".$popup."&line=".$line."&con_id=".$con_id."&module_type_appoint=".$_GET['module_type_appoint']."&proid=".$proid."&candrn=".$candrn."&module=".$module."&pageName=".$pageName);
			
		else
			Header("Location:revconreg$dest.php?proid=".$proid."&candrn=".$candrn."&module=".$module."&pageName=".$pageName);
	}
?>
