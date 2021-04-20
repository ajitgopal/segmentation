<?php
  /***  
    Modified Date   : Mar 1st , 2017.
    Modified By     : Sanghamitra	
    Purpose		    : Allow all job types to resubmit the same candidate to same job order if the status is cancelled/closed 
		              over.So where ever direct and internal direct job orders conditions are there need to remove it.
    Ticket Id		: #813679 
    Line Nos        : 312,313,410,400,401,402
	***/
	$con_id= $conid;
	$tempaddr=$addr;

	require("global.inc");

	$addr=$tempaddr;

	require("mysqlfun.inc");
	require("dispfunc.php");
	require($akken_psos_include_path.'commonfuns.inc');
        require_once("../../Include/candidateHrmComm.php"); //This file contains the functions for updating hiring management tables.
        require_once("../../Include/candidateEmpComm.php");
    
    	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
	
	$conid=$con_id;
	$addr=$con_id; 
	$shareVal=explode("|",$canddet);
	$emplist=$shareVal[1];
	$owner=$shareVal[2];
 	$module = isset($_GET['module'])?$_GET['module']:'';

	if($shareVal[4] == 'Shared')
		$shareVal[4] = "share";

	$TodoImpact="";

	function getMeridiem($gtime)
	{
		$Time=str_replace(":",".",$gtime);
		$meridiem=(($Time==0 || $Time==24 || $Time=='')?'12:00 am':(($Time>=12 && $Time<=12.59)?number_format($Time,2,':',',')." pm ":(($Time>12)?number_format(($Time-12),2,':',',')." pm ":number_format($Time,2,':',',')." am ")));
		return 	$meridiem;	
	}

$module_type_appoint="Marketing->Candidates";
//query to update the muser,mdate.
if($TOGGLE=='' && !isset($TOGGLE) && $rtype!='skill' && $rtype != 'joborder' && $rtype != 'resume')
{

	$que="update candidate_list set muser='".$username."',mtime=NOW() where username='".$addr."'";
	$res=mysql_query($que,$db);
}	
$sql_for_moddate="SELECT ".tzRetQueryStringDTime("mtime","Date","/").",muser,sno from candidate_list where username='".$addr."'";
$sql_moddate_res=mysql_query($sql_for_moddate,$db);
$sql_moddate_data=mysql_fetch_row($sql_moddate_res);
$modDate=$sql_moddate_data[0];
$Mod_User=$sql_moddate_data[1];
$cand_sno=$sql_moddate_data[2];

//this is the query for getting  all users
   $Users_Sql="select us.username,us.name,su.crm  from users us  LEFT JOIN sysuser su ON us.username = su.username  WHERE su.crm!='NO' AND us.status != 'DA' AND us.type in ('sp','PE')  AND us.name!='' ORDER BY us.name";
    $Users_Res=mysql_query($Users_Sql,$db);

    $Users_Array=array();
    while($Users_Data=mysql_fetch_row($Users_Res))
    {
    	 $Users_Array[$Users_Data[0]]=$Users_Data[1];
    }
	
//this is for summary
if($rtype == 'sumdetails')
{		
	if($shareVal[0] == "share" && $shareVal[3]=="No" && $shareVal[1] == '')
	{
		$cshare=strtolower($shareVal[4]);
		$cshared=$shareVal[4];

		$que="update candidate_list set cl_status='".$shareVal[5]."', dontcall='".$shareVal[6]."', dontemail='".$shareVal[7]."' where username='".$addr."'";
		$res=mysql_query($que,$db);
	}	
	else
	{
		if($shareVal[0] == "private")
		{
			$cshare=$shareVal[0];
			$cshared=$shareVal[0];
			if($shareVal[3]=="Yes")//checking if owner has changed
			{
				$upd = "update candidate_list set accessto='".$shareVal[2]."',ctype=IF(ctype='Employee','Employee','My Consultant') where username='".$addr."'";//giving access only to owner
			    mysql_query($upd,$db);
			}
			else
			{
				$upd = "update candidate_list set accessto='".$username."',ctype=IF(ctype='Employee','Employee','My Consultant') where username='".$addr."'";
				mysql_query($upd,$db);
			}
		}
		else if($shareVal[0]=="public")
		{		
			$cshare=$shareVal[0];
			$cshared=$shareVal[0];
			$upd = "update candidate_list set accessto='ALL',ctype=IF(ctype='Employee','Employee','Consultant') where username='".$addr."'";
			mysql_query($upd,$db);
		}
		else if($shareVal[0] == "share")
		{
			$cshare="share";
			$cshared="shared";
			if($shareVal[3]=="Yes")//checking if the owner has been changed
			{
				if ($emplist=="")
				{
					$sql1 = "select accessto from candidate_list where username='".$addr."'";
					$res1 = mysql_query($sql1,$db);
					$row1 = mysql_fetch_array($res1);
					if ($row1['accessto']=='all' or $row1['accessto']=='ALL')
						$emplist1=$emplist.$username.",".$shareVal[2];
					else
						$emplist1=$row1['accessto'].",".$shareVal[2];	
					mysql_free_result($res1);					
				}
				else
				{
					$emplist1=$emplist.$username.",".$shareVal[2];
				}
			}
			else
			{
				$emplist1=$emplist.$shareVal[2];
			}
			
			$upd = "update candidate_list set accessto='".$emplist1."',ctype=IF(ctype='Employee','Employee','Consultant') where username='".$addr."'";
			mysql_query($upd,$db);	
			
		}

		$que="update candidate_list set owner='".$shareVal[2]."',cl_status='".$shareVal[5]."', dontcall='".$shareVal[6]."', dontemail='".$shareVal[7]."' where username='".$addr."'";
		$res=mysql_query($que,$db);
	}
	
	$que="select name from users where username='".$shareVal[2]."'";
	$res=mysql_query($que,$db);
	$Owner_Res=mysql_fetch_row($res);
	$cowner=$Owner_Res[0];
	/* ... .. Raj.. Checking server to change style for FF .. Raj .. ... */
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1){
		$rightflt = "style= 'width:18px;'";
		$rightflt3 = "style='width:34px'";
	}//End of if(To know the server)
	?>
	<!-- Merged the below code from branch_cs_6_14 to branch_cs_6_24 for Job Boards -- kumar raju k. -->
	<div id="candidatesetid">
		<div class="crmsummary-settings">
		 <a onClick="classToggle(joset,'DisplayBlock','DisplayNone','',9)" href="#">
			<span class="crmsummary-settings-span">Settings</span></a>
	
	<?php
	$que="select accessto from candidate_list where username='".$conid."'";
	$res=mysql_query($que,$db);
	$share=mysql_fetch_row($res);
	$shareval=$share[0];
	
	$Cand_Own="select owner from candidate_list where username='".$conid."'";
	$Cand_que=mysql_query($Cand_Own,$db);
	$Cand_res=mysql_fetch_row($Cand_que);
	
	$que="select name from users where username='".$Cand_res[0]."'";
	$res=mysql_query($que,$db);
	$Owner_Res=mysql_fetch_row($res);
	$cowner=$Owner_Res[0];

 // To chcek the onwer and to disable share and owner listbox if login user is not the owner
	if($Cand_res[0]!=$username)
        $disable="disabled";
    else
        $disable="";

/*if($shareval == $username)
	{
        $cshare="private";
		$cshared="private";
	}	
    else if($shareval=='ALL')
     {
	    $cshare="public";
		$cshared="public";
	}	
		
    else
	{
        $cshare="share";
		$cshared="shared";
	}	*/
	
	//The following code is to replace the status field if user updates status value in summary page of candidate and that should reflect in candidate preference tab.--------------START
	$modifiedCandPrefVal=explode("|",$_SESSION[candpage7.$candrn]);
	array_splice($modifiedCandPrefVal, -1, 1, array($shareVal[5]));
	$modifiedCandPrefData = implode("|", $modifiedCandPrefVal);
	$_SESSION[candpage7.$candrn]=$modifiedCandPrefData;
    //-----------END------------
	
	// Merged the below code from branch_cs_6_14 to branch_cs_6_24 for Job Boards -- kumar raju k.
	$candtyp_link='';
	if(($cshare == "share") && ($username != $shareVal[2]))
	{
	  $candtyp_link="viewlink";
	}
	else if(($cshare == "share") && ($username==$shareVal[2]))
	{
	  $candtyp_link="editlink";
	}
	if($disable=="disabled")
	  $disb_sel="yes";
	else
	  $disb_sel="no";  
	  
	  $editset="|^^AKK^^|".$candtyp_link."|".$disb_sel."|^^CandSettingPaneText^^|( ".strtoupper($cshared)." | OWNER: ".strtoupper($cowner);
	  $editset.=(($shareVal[5]!=0)?" | ".getManage($shareVal[5]):"")." )&nbsp;&nbsp;[ <a class=\"crmsummary-contentlnk\" href=\"javascript:editCheckList('cand');\"><font style='text-decoration: none;color: #00f;cursor: pointer;font-size:7.5pt; '>candidate checklist</font></a> ]";
				
   echo $editset; 
}			  	
?>


<?
//this is for joborders
if($rtype == 'joborder')
{	
			$modulejob = '';
if($module == 'Admin_Candidates')
	$modulejob = 'Admin_JobOrders';
else
	$modulejob = 'CRM';

$deptWhrCond = "";
if ($modulejob == 'CRM') {
	$deptWhrCond = " AND posdesc.deptid IN (".$deptAccesSno.") ";
}
	
?>
                    <table width="442" class="panel-table-scroll">
                      <thead>

                      <tr class="panel-table-header" align="center">
                          <th width=20%>Start</th>
                          <th width=25%>Position Title</th>
                          <th width=25%>Company</th>
                          <th width=15%>Job Type</th>
						  <th width=15%>Matching</th>
					  </tr>
	<?php
	//finding the type and id of the candidate record
	
	$conlen=strlen($conid);
	
	for($i=0;$i<$conlen;$i++)
	{
	  if(is_numeric($conid[$i]))
		  break;
	}
	
	$Type=substr($conid,0,$i);
	$Id=substr($conid,$i);
	$candidateVal=$Id;
	$MatchJobOrd=array();
	$MatchJobOrd_scr=array();
		
	// $not_display_joborders_cond - variable defined in /include/userDefines.php page
	
	$query="select posdesc.posid,postitle,contact,postype,posdesc.type,if(DATE_FORMAT(posstartdate,'%c/%e/%Y')='0/0/0000','',".tzRetQueryStringSelBoxDate("posstartdate","Date","/")."),group_concat(skill_name),company,manage.name from manage, posdesc JOIN req_skills  ON req_skills.rid=posdesc.posid where  (posdesc.owner='".$username."' or FIND_IN_SET('".$username."',accessto)>0 or accessto='all') ".$deptWhrCond." AND posdesc.posstatus = manage.sno AND manage.name $not_display_joborders_cond AND manage.type='jostatus' and posdesc.status in ('approve','Accepted')group by posid  order by stime desc";

	$res=mysql_query($query,$db);
	$JOBS="";
	$count=0;
	
	//initially setting  the default vals for Rank
	require("sphinx_config.php");
	include_once("sphinx_common_class.php");

	$vsIRes = getSphinxIndexname(5);
	$SPHINX_CONF['sphinx_index'] = $vsIRes['index_name'];
	$SPHINX_CONF['masters_index_name'] = $vsIRes['masters_index_name'];
			
	while ($result=mysql_fetch_array($res))
	{	 
	  //check whether this candidate was submitted or not
		$flag=false;
		$posdes_type=getManage($result[3]);
		
		if($Id!="")
		{
			$Skill_List = $result[6];
			
			if($Skill_List!='')
			{
				$Skill_List = prepareQueryMatchingjobs($Skill_List);
				$searchstr = '@(profile_data,resume_data) '.$Skill_List;
			
				$Check = "SELECT WEIGHT() AS w, id FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$searchstr}') and snoid=$Id ".$deptWhrCond." ORDER BY w DESC LIMIT 1 OPTION ranker=matchany,max_matches=1000000";

				$Res=mysql_query($Check,$sphinxql);
				$Matching_Rows=mysql_num_rows($Res);
				$Data=mysql_fetch_row($Res);	
				if($Matching_Rows>0)
				{
					if($Data[0]!=0)
						$flag=true;
				}
			}
		
		}
		
		if($flag)
		{	
			//start date
				//startdate of the Requirement
			if($result[5]=='00/00/0000')
				$result[5]="";	
			else
				$result[5]=$result[5];
	
				
				//company name of the contact
				$CompQry="SELECT cname FROM staffoppr_cinfo WHERE sno='".$result[7]."' and status='ER' and (FIND_IN_SET('$username',accessto)>0 OR owner='$username' OR accessto='ALL')";
				$CompRes=mysql_query($CompQry,$db);
				$CompData=mysql_fetch_row($CompRes);
				$CompName=$CompData[0];
				
				//for job type
				if($result[3]=='--')
					$result[3]= "";		
				else
				  $result[3]= trim($result[3],'-');	
			//checking wether the candidate has been submmited for the job order
			$Job_Submit="select count(*) from reqresponse,resume_status,manage where reqresponse.posid='".$result[0]."' and FIND_IN_SET('".$conid."',resumeid) > 0 AND resume_status.req_id=reqresponse.posid AND resume_status.req_id=posdesc.posid AND FIND_IN_SET(resume_status.res_id,reqresponse.resumeid) > 0 AND resume_status.seqnumber=reqresponse.seqnumber AND manage.sno = resume_status.status AND manage.name NOT IN ('Closed','Cancelled')";
			
			$Submit_Res=mysql_query($Job_Submit,$db);	
			$Submit_Count=mysql_fetch_row($Submit_Res);
			$submissionCnt = $Submit_Count[0];
			if($submissionCnt == 0 && ($posdes_type != "Direct" || $posdes_type != "Internal Direct"))
			{
				$Submission_que="select count(*) from reqresponse where reqresponse.posid='".$result[0]."' and FIND_IN_SET('".$conid."',resumeid) > 0 and seqnumber in(select seqnumber from resume_status,manage where resume_status.req_id='".$result[0]."' AND manage.sno=resume_status.status AND manage.name NOT IN ('Closed','Cancelled') and concat('cand',resume_status.res_id) = '".$conid."')";
				$Submission_Res=mysql_query($Submission_que,$db);	
				$Submission_Count=mysql_fetch_row($Submission_Res);
				$submissionCnt = $Submission_Count[0];
			}	
			if($submissionCnt == 0)		
			{
				$MatchJobOrd["scr"][]=$Data[0];
				$MatchJobOrd["jobsr"][]=array($result[5],$result[1],$CompName,$result[3],$Data[0],$result[0]);
				$count++;
			}	
			
		 }
		
	}//while
		
		if($count>0)
			{
				$Res=array_multisort($MatchJobOrd["scr"],  SORT_NUMERIC,SORT_DESC,
				$MatchJobOrd["jobsr"], SORT_STRING, SORT_DESC);
				$lpe=(count($MatchJobOrd["jobsr"])>20)?20:count($MatchJobOrd["jobsr"]);
					
				
				for($i=0;$i<$lpe;$i++)
				{
				     if($MatchJobOrd["jobsr"][$i][3]!='')
						{ 
						  $jotyp="select name from manage where type='jotype' and sno='".$MatchJobOrd["jobsr"][$i][3]."'";
						  $jotyp_que=mysql_query($jotyp,$db);
						  $jotyp_fet=mysql_fetch_array($jotyp_que);	
						}
						?>
						<tr  class="panel-table-content" align="left" valign="middle"  onclick="javascript:openewWindow('/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $MatchJobOrd["jobsr"][$i][5];?>&module=<?php echo $modulejob;?>','MatchingJobsSubmission',1250,900)" style="cursor: hand;">
                        <td ><?php echo $MatchJobOrd["jobsr"][$i][0];?>&nbsp;</td>
						  <td title='<?=$MatchJobOrd["jobsr"][$i][1];?>' ><?php echo $MatchJobOrd["jobsr"][$i][1];?>&nbsp;</td>
						  <td title='<?=$MatchJobOrd["jobsr"][$i][2];?>' ><?php echo $MatchJobOrd["jobsr"][$i][2];?>&nbsp;</td>
						  <td title='<?=$MatchJobOrd["jobsr"][$i][3];?>'><?php echo $jotyp_fet[0];?>&nbsp;</td>
						  <td align="left" title='<?=$MatchJobOrd["jobsr"][$i][4];?>' ><?php echo $MatchJobOrd["jobsr"][$i][4];?>&nbsp;</td>
                        </tr>
					<? }
			         }
			 else {	?>
					 <tr class="panel-table-content" valign="middle" align="center"><td colspan=5>no data found</td></tr>
					<? }?>
                      
    </table>
<?				
}
?>

<?php
//this is for Shortlisted
if($rtype == 'shortlisted')
{ $module = isset($_GET['module'])?$_GET['module']:'';
$modulejob = '';
if($module == 'Admin_Candidates')
	$modulejob = 'Admin_JobOrders';
else
	$modulejob = 'CRM';

	$deptWhrCond = "";
	if ($modulejob == "CRM") {
		$deptWhrCond = " AND posdesc.deptid IN (".$deptAccesSno.") ";
	}
?>
	<table class="panel-table-scroll">
	<thead>
		<tr class="panel-table-header" align="center">
			<th>Short Listed On</th>
			<th>Company</th>
			<th>Job Title</th>
			<?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { ?>
			<th>Shift</th>
			<?php }?>
			<th colspan=2 align="center">Actions</th>			
		</tr>
	</thead>	
<?php
	//finding the type and id of the candidate record
	$conlen=strlen($conid);
	for($i=0;$i<$conlen;$i++)
	{
		if(is_numeric($conid[$i]))
			break;
	}

	$Type=substr($conid,0,$i);
	$Id=substr($conid,$i);
	$candidateVal=$Id;

	if(SHIFT_SCHEDULING_ENABLED == 'Y') {
		$sqry = "SELECT
			posdesc.posid,
			posdesc.postitle,
			".tzRetQueryStringDTime('short_lists.sdate','DateTime','/').",
			users.name,
			staffoppr_cinfo.cname ,
			posdesc.status,
			IF(posdesc.accessto = 'all', 'Public', IF(FIND_IN_SET('".$username."',posdesc.accessto)>0 , 'Share','NONE')),
			shift_setup.shiftname
			
		FROM
			short_lists
			INNER JOIN users ON (users.username = short_lists.suser)
			INNER JOIN posdesc ON (posdesc.posid = short_lists.reqid)
			LEFT JOIN staffoppr_cinfo ON posdesc.company = staffoppr_cinfo.sno
			LEFT JOIN shifts_cand_posdesc ON (shifts_cand_posdesc.joborder_id=short_lists.reqid AND shifts_cand_posdesc.type='shortlisted' AND shifts_cand_posdesc.cand_username = 'cand".$candidateVal."')
			LEFT JOIN shift_setup ON (shift_setup.sno = shifts_cand_posdesc.sm_sno)
		WHERE
			short_lists.candid='$candidateVal' ".$deptWhrCond."
			AND concat(posdesc.posid,'-',IF(shift_setup.sno IS NULL,0,shift_setup.sno)) NOT IN (SELECT concat(req_id,'-',shift_id) FROM resume_status, manage WHERE manage.sno = resume_status.status AND res_id='".$candidateVal."' AND manage.name NOT IN ('Closed','Cancelled') AND resume_status.pstatus!='A')
		GROUP BY short_lists.reqid,shifts_cand_posdesc.sm_sno
		ORDER BY short_lists.sdate DESC";
	}
	else
	{
		$sqry = "SELECT posdesc.posid,posdesc.postitle, ".tzRetQueryStringDTime('short_lists.sdate','DateTime','/').",users.name,staffoppr_cinfo.cname ,posdesc.status,IF(posdesc.accessto = 'all', 'Public', IF(FIND_IN_SET('".$username."',posdesc.accessto)>0 , 'Share','NONE')) FROM short_lists,users, posdesc LEFT JOIN staffoppr_cinfo ON posdesc.company = staffoppr_cinfo.sno  WHERE short_lists.candid='$candidateVal' ".$deptWhrCond." AND posdesc.posid = short_lists.reqid AND short_lists.suser = users.username AND (( posdesc.posid NOT IN (select req_id from resume_status, manage WHERE manage.sno = resume_status.status AND res_id='".$candidateVal."' AND manage.name NOT IN ('Closed','Cancelled') AND resume_status.pstatus!='A'))) GROUP BY short_lists.reqid ORDER BY short_lists.sdate DESC";
	}

	$srs  = mysql_query($sqry,$db);
	if(mysql_num_rows($srs) == 0)
	{
?>
  		<tr class="panel-table-content" valign="middle" align="center"><td colspan="5">no data found</td></tr>
<?php
	}
	else
	{
		while($srow = mysql_fetch_row($srs))
		{    
		 	if($srow[5]!= "backup"  && $srow[5]!= "deleted" && $srow[6]!='NONE')
			{

			?>
			<tr class="panel-table-content" align="left" valign="middle"   style="cursor:pointer;">
				<td onclick="javascript:openewWindow('/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $srow[0];?>&module=<?php echo $modulejob;?>','MatchingJobsSubmission',1200,900);"><?php echo $srow[2];?>&nbsp;</td>
				<td title="<?php echo $srow[4];?>" onclick="javascript:openewWindow('/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $srow[0];?>&module=<?php echo $modulejob;?>','MatchingJobsSubmission',1200,900);"><?php echo $srow[4];?>&nbsp;</td>
				<td title="<?php echo $srow[1];?>" onclick="javascript:openewWindow('/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $srow[0];?>&module=<?php echo $modulejob;?>','MatchingJobsSubmission',1200,900);"><?php echo $srow[1];?>&nbsp;</td>
				<td title="<?php echo $srow[7];?>" onclick="javascript:openewWindow('/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=<?php echo $srow[0];?>&module=<?php echo $modulejob;?>','MatchingJobsSubmission',1200,900);"><?php echo $srow[7];?>&nbsp;</td>
				
				<td align="center" onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/notespopup.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $srow[0]; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $srow[6]; ?>&postatus=<?php echo $srow[5]; ?>','CandidateNotes','430','165')"><i alt='Notes' class='fa fa-file-text'></i>&nbsp;</td>
				<?php if(OUTLOOK_PLUG_IN!="Y" || OUTLOOK_TASK_MANAGER!="Y") { ?>						
				<td align="center"  onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/todoPopupforcandidates.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $srow[0]; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $srow[6]; ?>&postatus=<?php echo $srow[5]; ?>','ToDoReminders','425','230')"><i title='To Do' align='center' border='0' alt='' class='fa fa-thumb-tack'></i>&nbsp;</td> 
				<?php }
			}  ?>		
			</tr>
		<?php		
		}
	}
?>
	</table>
<?php
}
?>

<?php
//this is for document
if($rtype == 'document')
{	
?>				  	<div>
                      <table class="panel-table-scroll">
                      <thead>
                        <tr class="panel-table-header">
                          <th>Date</th>
                          <th>Created By</th>
                          <th>Name</th>
                          <th>Title</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
						
                     	$Doc_Sql="select contact_doc.sno,".tzRetQueryStringDTime("contact_doc.sdate","DateTime","/").",users.name,contact_doc.docname,contact_doc.doctype,contact_doc.title,cmngmt_pr.activity_status from contact_doc LEFT JOIN cmngmt_pr ON contact_doc.sno = cmngmt_pr.tysno AND cmngmt_pr.title= 'Document' ,users where contact_doc.username = users.username AND contact_doc.con_id='".$conid."' order by contact_doc.sdate desc";
    					 $Doc_Res=mysql_query($Doc_Sql,$db);
    					 $Doc_Rows=mysql_num_rows($Doc_Res);

						if($Doc_Rows>0){
                        while($Docs_Data=mysql_fetch_row($Doc_Res))
						{
							if($Docs_Data[6]== '1' && $module == 'CRM'){
								//Hiding the documents which are hiddden from Admin module
							}
							else{
								$docEvent=" onClick=\"javascript:editWin('editdoc.php?sno=$Docs_Data[0]&con_id=$conid&hidemodule=$module')\" style='cursor:hand'" ;												
								?>
								<tr class="panel-table-content" <?php echo $docEvent;?>>
		                          <td title='<?=strtolower($Docs_Data[1])?>'  style="cursor:hand;"><? echo strtolower(dispTextdb($Docs_Data[1]))?></td>
								  <td title='<?=$Docs_Data[2]?>' style="cursor:hand;"><? echo dispTextdb($Docs_Data[2])?></td>
		                          <td title='<?=$Docs_Data[3]?>' style="cursor:hand;"><?php echo stripslashes($Docs_Data[3]);?></td>
		                          <td title='<?=dispTextdb($Docs_Data[5])?>' style="cursor:hand;"><?=strlen($Docs_Data[5])>10?dispTextdb(substr($Docs_Data[5],0,10)."..."):dispTextdb($Docs_Data[5])?></td>
							    </tr>
						<? }
						}
						} else {?>
				   	     <tr class="panel-table-content" valign="middle" align="center"><td colspan=4>no data found</td></tr>
					  <?}?>
                      </tbody>
      				  </table>
        			   </div>
<?php					   
}
?>

<?
//This is for TO DO
if($rtype=="todo")
{
	
	if($delRow!="") //while deleting a todo
	{
	  //$TodoImpact="Yes"; /* Commented for Bug Id 4004 */
	  //$del_Task_Query="update tasklist set taskstatus='Completed',tasktype='task',cuser='".$username."',muser='".$username."',mdate=NOW() where sno=".$delRow;
	  	if($delRow)
		{
			$del_Task_Query="delete from tasklist where sno='".$delRow."'";
			mysql_query($del_Task_Query,$db);
		}
	  
	  // for updating the fname,mname,lname and wphone in the tasklist table
	  UpdateTaskList($conid,'CRM->Candidates');
	 	 	
		//In order to display all the To Dos on Deleting a To do --Chanikya
		$Rem_Sql="SELECT DATE_FORMAT(tasklist.startdate,'%c/%e/%Y %l:%i %p'),
					   tasklist.title,
					   users.name,
					   datediff(tasklist.startdate,NOW()),
					   tasklist.sno,
					   users.username,
					   tasklist.ctime
				FROM tasklist,
					 users
				WHERE users.username=tasklist.cuser
				  AND tasktype='todo'
				  AND tasklist.contactsno='".$conid."'
				  AND tasklist.taskstatus!='Completed'
				  AND tasklist.status NOT IN ('remove',
											  'backup',
											  'ARCHIVE')
				ORDER BY tasklist.sno DESC";

	}
	else if($todoof!="") //sorting todos based on the users
    {		
		if($todoof=='All')
		{
				 
			   $Rem_Sql="select DATE_FORMAT(tasklist.startdate,'%c/%e/%Y %l:%i %p'),tasklist.title,users.name,datediff(tasklist.startdate,NOW()),tasklist.sno,users.username,tasklist.ctime FROM tasklist,users  WHERE users.username=tasklist.cuser AND tasktype='todo' AND tasklist.contactsno='".$conid."'  AND tasklist.taskstatus!='Completed' AND tasklist.status not in ('remove','backup','ARCHIVE')  order by tasklist.sno desc";
		}
		else if($todoof=='Mytodo')
		{
			 $Rem_Sql="select DATE_FORMAT(tasklist.startdate,'%c/%e/%Y %l:%i %p'),tasklist.title,users.name,datediff(tasklist.startdate,NOW()),tasklist.sno,users.username,tasklist.ctime FROM tasklist,users  WHERE users.username=tasklist.cuser AND ((cuser='".$username."' and sendto='') or find_in_set('".$username."',sendto)) AND tasktype='todo' AND tasklist.contactsno='".$conid."'  AND tasklist.taskstatus!='Completed' AND tasklist.status not in ('remove','backup','ARCHIVE')  order by tasklist.sno desc";
		}
		else if(is_numeric($todoof))
		{
			$Rem_Sql="select DATE_FORMAT(tasklist.startdate,'%c/%e/%Y %l:%i %p'),tasklist.title,users.name,datediff(tasklist.startdate,NOW()),tasklist.sno,users.username,tasklist.ctime FROM tasklist,users  WHERE users.username=tasklist.cuser AND ((cuser='".$todoof."' and sendto='') or find_in_set('".$todoof."',sendto)) AND tasktype='todo' AND tasklist.contactsno='".$conid."'  AND tasklist.taskstatus!='Completed' AND tasklist.status not in ('remove','backup','ARCHIVE')  order by tasklist.sno desc";
	 	}
	} 
	  
	else if($updateRow!="") //while updating a ToDo
	{
	  
		$sdate=explode("/",$strt_date);
		$sdate=$sdate[2]."-".$sdate[0]."-".$sdate[1];
		if($donestatus=='Yes')
		{
		   $update_todo="update tasklist set taskstatus='Completed',tasktype='task',startdate='".$sdate."',title='".$todo."',cuser='".$username."',muser='".$username."',mdate=NOW(),ctime='".$strt_time."'  where sno=".$updateRow; 
		   mysql_query($update_todo,$db);	
		   
		   // for updating the fname,mname,lname and wphone in the tasklist table
	  		UpdateTaskList($conid,'CRM->Candidates');
		 
		  $addAsEvent="insert into cmngmt_pr(sno, con_id, username, tysno, title, sdate, subject,lmuser,subtype) values('','$conid','$username','$updateRow','Task',NOW(),'$todo','$username','To Do')";
		   mysql_query($addAsEvent,$db);	
		
		}
		else 
		{
		
			$update_todo="update tasklist set title='".$todo."',startdate='".$sdate."',cuser='".$username."',ctime='".$strt_time."' where sno=".$updateRow;
			mysql_query($update_todo,$db);
			
			// for updating the fname,mname,lname and wphone in the tasklist table
	 		 UpdateTaskList($conid,'CRM->Candidates');
		}		
	
		//In order to display all the To Dos on Updating a To do --Chanikya
		$Rem_Sql="SELECT DATE_FORMAT(tasklist.startdate,'%c/%e/%Y %l:%i %p'),
					   tasklist.title,
					   users.name,
					   datediff(tasklist.startdate,NOW()),
					   tasklist.sno,
					   users.username,
					   tasklist.ctime
				FROM tasklist,
					 users
				WHERE users.username=tasklist.cuser
				  AND tasktype='todo'
				  AND tasklist.contactsno='".$conid."'
				  AND tasklist.taskstatus!='Completed'
				  AND tasklist.status NOT IN ('remove',
											  'backup',
											  'ARCHIVE')
				ORDER BY tasklist.sno DESC";
	}
	else  //while adding a todo 
	{  
		
		$sdate=explode("/",$strt_date);
		$sdate=$sdate[2]."-".$sdate[0]."-".$sdate[1];
		
		$todo=trim($todo);

		$Todo_Query="insert into tasklist(title,type,status,startdate,ctime,datecreated,cuser,contactsno,tasktype,modulename) values('".$todo."',1,'new','".$sdate."','".$strt_time."',NOW(),'".$username."','".$conid."','todo','".$module_type_appoint."')";
		$Todo_Res=mysql_query($Todo_Query,$db);
		$id=mysql_insert_id($db);
		
        // for updating the fname,mname,lname and wphone in the tasklist table
        UpdateTaskList($conid,'CRM->Candidates');
				
		//In order to display all the To Dos on adding a To do --Chanikya
		$Rem_Sql="SELECT DATE_FORMAT(tasklist.startdate,'%c/%e/%Y %l:%i %p'),
					   tasklist.title,
					   users.name,
					   datediff(tasklist.startdate,NOW()),
					   tasklist.sno,
					   users.username,
					   tasklist.ctime
				FROM tasklist,
					 users
				WHERE users.username=tasklist.cuser
				  AND tasktype='todo'
				  AND tasklist.contactsno='".$conid."'
				  AND tasklist.taskstatus!='Completed'
				  AND tasklist.status NOT IN ('remove',
											  'backup',
											  'ARCHIVE')
				ORDER BY tasklist.sno DESC";

	}
		
	  
	  $Rem_Res=mysql_query($Rem_Sql,$db);
	  $Rem_Rows=mysql_num_rows( $Rem_Res);
	 
	 
	  if($Rem_Rows>0)
		{
	  while($Rem_Data=mysql_fetch_row($Rem_Res))
				{			
					$cr_Date=explode(" ",$Rem_Data[0]);
					$cr_Date=$cr_Date[0];
					$ctime 	= $Rem_Data[6];
					$exp_times = explode(":",$ctime);
				    $hours  = $exp_times[0];
			      $min = $exp_times[1];
			      $check = "am";
				  
				  if($hours == "01" || $hours == "02" || $hours == "03" || $hours == "04" || $hours == "05" || $hours == "06" || $hours == "07" || $hours == "08" || $hours == "09")
				     {
				     $pos1 = substr($hours, 1);
				     $hours = $pos1;
					 }  
					 if(($hours == 12 && $min == 30) || ($hours == 12 && $min == 00)) 
					 {
					 $check = "pm";
					 }
					 
				  if($hours == 00 && $min == 00)
				   {
				   $hours = "";
				   $min = "";
			       $check = "";
				   }
				  if($hours == 00 && $min == 30)
				   {
				   $hours = 12;
			       $check = "am";
				   }
			      if($hours == 13)
			      {
			      $hours = 1;
			      $check = "pm";
			      }
			     else if($hours == 14)
			     {
			      $hours = 2;
			      $check = "pm";
			     }
			     else if($hours == 15)
			     {
			      $hours = 3;
			      $check = "pm";
			     }
			     else if($hours == 16)
			    {
			    $hours = 4;
			    $check = "pm";
			    }
			   else if($hours == 17)
			   {
			    $hours = 5;
			    $check = "pm";
			   }
			   else if($hours == 18)
			   {
			   $hours = 6;
			   $check = "pm";
			   }
			   else if($hours == 19)
			   {
			   $hours = 7;
			   $check = "pm";
			  }
			  else if($hours == 20)
			  {
			  $hours = 8;
			  $check = "pm";
			  }
			   if($hours == 21)
			  {
			  $hours = 9;
			  $check = "pm";
			  }
			  else if($hours == 22)
			  {
			  $hours = 10;
			  $check = "pm";
			  }
			  else if($hours == 23)
			  {
			  $hours = 11;
			  $check = "pm";
			  }
			  else if($hours == 24)
			  {
			  $hours = 12;
			  $check = "am";
			  }
			  $exp_newdat = trim($hours.":".$min.$check);
			 $todoData=$cr_Date." - ".$Rem_Data[2];
			 $to_Data1=$cr_Date." ".$exp_newdat." - ".$Rem_Data[2];
			 if(trim($Rem_Data[1])!='')
			 {
				$todoData.=" - ".$Rem_Data[1];
				$to_Data1.=" - ".$Rem_Data[1];
			}	
				
			
					if($Rem_Data[3]<0){ //the todos which r expired
						?>
					<div class="remindtext-alert">
							 <!--display the del link and edit link for owners only-->
							<?php 							
							if($Rem_Data[5]==$username){?>
							<a class="remind-delete-align" href="javascript:delToDo('<?=$Rem_Data[4]?>&candrn=<?=$candrn?>')"><img src="/BSOS/images/crm/icon-delete.gif" width="10" height="9" alt="" border="0" align="left"></a>
							(!)
						 <a href="javascript:editWin('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&candrn=<?=$candrn?>&module=<?=$module?>')"><?php 
						   echo $todoData."<br/>";?></a>
					 
					<? }else{
							//display only content
								if($exp_newdat == ":")
								{
								  echo $todoData."<br/>";
								}
								else
								{
								 	echo $to_Data1."<br/>";
								}
					}?>
				</div>
				<? }else {?><!--normal todos-->
					<div class="remindtext">
							 <!--display the del link for owners only-->
							<?php if($Rem_Data[5]==$username){?>
							<a class="remind-delete-align" href="javascript:delToDo('<?=$Rem_Data[4]?>&candrn=<?=$candrn?>')"><img src="/BSOS/images/crm/icon-delete.gif" width="10" height="9" alt="" border="0" align="left"></a>
						 <a  style='color:black' href="javascript:editWin('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&candrn=<?=$candrn?>&module=<?=$module?>')"><?php 
								if($exp_newdat == ":")
							    {
								 	echo $todoData."<br/>";
								}
								else
								{
								 	echo $to_Data1."<br/>";
								}?></a>
					 <?
					 }else{
							//display only content
								if($exp_newdat == ":")
							    {
								  echo $todoData."<br/>";
								}
								else
								{
								 echo $to_Data1."<br/>";
								}
					 }?>
				 </div>
				<? }
			   }//while
		} 

}
?>

<?
 //----------------------------------------------End Of TO DOS---------------------------------------
//For Resume..
if($rtype=="resume")
{
	 	$resumes  = "<tr>";
		$resumes .=	"<td>";
		$resumes .= "<table width=\"97%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
        $resumes .= "<tr>";
        $resumes .= "<span class=\"resumeHeading\">resume&nbsp;</span>";
                
		$res= $_SESSION[resname.$candrn];
		$resno=$_SESSION[resumesno.$candrn];
		$Ext_info = pathinfo($res);
		$Ext_name= $Ext_info['extension'];
		
		//This condition will satisfies, only if user delete resume from candidate summary page.
        if($deleteCandResume=="remove")
        {
            $_SESSION[resumesno.$candrn]="";
            $_SESSION[resname.$candrn]="";
            $resno=0;
            
            //Updating the candidate_list table for breaking relation b/w candidate_list and con_resumes tables when resume is removed.
            $updCandResumeQue="update candidate_list set resid=0 where username='".$addr."'";
            $resUpdCandResumeQue=mysql_query($updCandResumeQue,$db);
            
            //Updating resume_data field in search_data table when resume is removed.
            $updCandSearchQue="update search_data set resume_data='' where uid='".$cand_sno."' and uid!='' and uid!=0";
            $resUpdCandSearchQue=mysql_query($updCandSearchQue,$db);
            
            //deleting the resume from con_resumes table when resume is removed.
            $deleteCandResumeQue="delete from con_resumes where sno='".$candResumeSno."' and sno!='' and sno!=0";
            mysql_query($deleteCandResumeQue,$db);
            
            // [#814368] CRM - CANDIDATE AND HRM - EMPLOYEE COMMUNICATION PURPOSE 
            //deleting the resume from con_resumes table when resume is removed from summary page of candidate
            if(CRM_to_HRM=='Y')
            {
                global $crmEmpChangesFlag,$crmHrmChangesFlag;
                $candHrmCheck = candEmpExistChecking($addr);
                if($candHrmCheck[0]=="EMPLOYEE EXISTS"){
                    $que="select sno from con_resumes where username='".$candHrmCheck[1]."' and status='default'";
                    $res=mysql_query($que,$db);
                    $row=mysql_fetch_row($res);
                    $_SESSION[resname.$candrn]=$resname;
                        if($row[0]!='' || $row[0]!='0')
                        {
                               
                            $deleteCandResumeQue="delete from con_resumes where sno='".$row[0]."' and sno!='' and sno!=0";
                            mysql_query($deleteCandResumeQue,$db);

                            $crmEmpChangesFlag=true;
                            if($crmEmpChangesFlag){
                               addEmployeeCandidateActivity($addr);
                               addCandEmpActivity($addr);
                            }
                        }
                }
                    
                $candHrmCheck = candHrmExistChecking($addr);
                if($candHrmCheck[0]=="CANDIDATE EXISTS"){

                    $que="select sno from con_resumes where username='".$candHrmCheck[1]."' and status='default'";
                    $res=mysql_query($que,$db);
                    $row=mysql_fetch_row($res);
                    $_SESSION[resname.$candrn]=$resname;
                    if($row[0]!='' || $row[0]!='0')
                    {
                        $deleteCandResumeQue="delete from con_resumes where sno='".$row[0]."' and sno!='' and sno!=0";
                        mysql_query($deleteCandResumeQue,$db);
                            
                        $crmHrmChangesFlag=true;
                        if($crmHrmChangesFlag){
                            addHrirngCandidateActivity($addr);
                            addCandidateActivity($addr);
                        }
                    }
                }
            }
            
        }
				
		if($res != '') 
		{
			if($resno != 0 && trim($resno)!="")
			{
				if($Ext_name=="htm" || $Ext_name=="xhtml" || $Ext_name=="xml" || $Ext_name=="html")
				{
					//$url="getresume.php?fsno=".$resno;
					//$url='javascript:mailAttatchOpen("'.$url.'","resm") ';
					$resumes .= "<a href=javascript:candResumeOpen('getresume.php?fsno=$resno','resm') style=\"text-decoration: underline; color: #474c4f;font-size:11px;\" id=\"resumelinks\" title=\"View\">view</a>";
				}
				else
				{
					$resumes .= "<a href=getresume.php?fsno=$resno style='text-decoration: underline; color: #474c4f;font-size:11px;' id='resumelinks' title='View'>view</a>";
				}
			   $resumes .= "<span style='color:gray;font-size: 6.4pt;'>&nbsp;|&nbsp;</span>";
	     	}
			$resumes .= "<a font  href=\"javascript:openewWindow('revconreg13.php?candrn=$candrn','MatchingJobs',750,300)\" id=\"resumelinks\" title=\"Upload\">upload</a>";
	        //If any resume is there, providing remove link or else not.
	        if($resno != 0 && trim($resno)!="")
	        {
	            $resumes .= "<span style='color:gray;font-size: 6.4pt;'>&nbsp;|&nbsp;</span>";
	            $resumes .= "<a font  href=\"javascript:removeCandResume('$candrn','$resno')\" id=\"resumelinks\" title=\"remove\">remove</a></td></tr></table>";
			}
		}
		else 
		{
			$resumes .= "<a font  href=\"javascript:openewWindow('revconreg13.php?candrn=$candrn','MatchingJobs',750,300)\" id=\"resumelinks\" title=\"Upload\">upload</a>";
		}
		/*if($resno != 0 && trim($resno)!="")
		{
			if($Ext_name=="htm" || $Ext_name=="xhtml" || $Ext_name=="xml" || $Ext_name=="html")
			{
				//$url="getresume.php?fsno=".$resno;
				//$url='javascript:mailAttatchOpen("'.$url.'","resm") ';
				$resumes .= "<a href=javascript:candResumeOpen('getresume.php?fsno=$resno','resm') style=\"text-decoration: underline; color: #474c4f;font-size:11px;\" id=\"resumelinks\" title=\"View\">view</a>";
			}
			else
			{
				$resumes .= "<a href=getresume.php?fsno=$resno style='text-decoration: underline; color: #474c4f;font-size:11px;' id='resumelinks' title='View'>view</a>";
			}
		   $resumes .= "<span style='color:gray;font-size: 6.4pt;'>&nbsp;|&nbsp;</span>";
	     }
		$resumes .= "<a font  href=\"javascript:openewWindow('revconreg13.php?candrn=$candrn','MatchingJobs',750,300)\" id=\"resumelinks\" title=\"Upload\">upload</a>";
        //If any resume is there, providing remove link or else not.
        if($resno != 0 && trim($resno)!="")
        {
            $resumes .= "<span style='color:gray;font-size: 6.4pt;'>&nbsp;|&nbsp;</span>";
            $resumes .= "<a font  href=\"javascript:removeCandResume('$candrn','$resno')\" id=\"resumelinks\" title=\"remove\">remove</a></td></tr></table>";
        }*/
        $resumes .="</td>";
        $resumes .="</tr>";
        $DispText=$resumes;
}
//this is for skills
if($rtype=="skill"){

	switch($ord){
		case "def":
			$addQuery="";
			$order=" skillname ASC,skillyear DESC";
			break;
		case "lused":
			$addQuery=" ,cast(if(lastused='Current',0,if(lastused='',-1,SUBSTRING_INDEX(lastused,' ',1))) AS UNSIGNED) as lused ";
			$order=" lused ASC,skillname ASC";
			break;
		case "slevel":
			$addQuery=" , cast(IF (skilllevel = 'Beginner', 'B',IF (skilllevel = 'Intermediate', 'C',IF (skilllevel = 'Expert', 'D', 'A') )) AS CHAR) AS slevel ";
			$order=" slevel DESC,skillname ASC";
			break;	
		case "exp":
			$addQuery="";
			$order=" cast(skillyear as SIGNED) DESC,skillname ASC";
			break;	
		default:
			$addQuery="";
			$order=" skillname ASC,cast(skillyear as SIGNED) DESC";
			break;
	}
	
	$sque="select skillname,skillyear,lastused,skilllevel ".$addQuery." from candidate_skills where username='".$conid."' and (skillname!='' or skillyear!='' or lastused!='' or skilllevel!='') order by $order";
	$sres=mysql_query($sque,$db);
	$no_rows=mysql_num_rows($sres);
	if($no_rows>0){
		 
		$left_num_rows=ceil($no_rows/2);
		$ct=1;
		$skill="<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" align=\"left\" border=\"0\" class=\"nestedtable-removelines-cand\"><tr><td width=\"50%\" valign=\"top\">
						  <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" >";
		while($srow=mysql_fetch_row($sres))
		{  
			$Skill_Name=dispTextdb($srow[0]);
			$Skill_Name="<font style='color:black;font-weight:bold;font-family:verdana;font-size:7pt;'>$Skill_Name</font>";
			
			$dispskill_arr=array();
			if(trim($srow[1])!="" && $srow[1]!="0") { array_push($dispskill_arr,$srow[1]."yrs"); };
			if(trim($srow[2])!="" && $srow[2]!="0") { array_push($dispskill_arr,$srow[2]); };
			if(trim($srow[3])!="" && $srow[3]!="0") { array_push($dispskill_arr,$srow[3]); };
			$dispSkill="";

			if(count($dispskill_arr)>0) 
				$dispSkill=	"(".implode(",",$dispskill_arr).")";
			$dispSkill=dispTextdb($dispSkill);
			$skill.="<tr><td align=\"left\" valign=\"top\" class=\"afontstyle\" style='color:#777;font-family:arial;font-size:7.5pt;'>";
			$skill.=$Skill_Name.$dispSkill."</td>";
			$skill.="</tr>";
			
			if($left_num_rows==$ct){
				$skill.="</table></td><td valign=\"top\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"nestedtable-removelines-cand\">";
			}	
			$ct++;
		}
		$skill.="</table></td></tr></table>";
	 $DispText=$skill;
 }
}

//this for Notes
if($rtype=="notes")
{
	$candnotes=substr($conid,4);
	$cand_id = $conid;
	$hdnContAssoc = setSummaryNotedAssocIDS($hdnContAssoc, 'oppr', 0);
	$hdnCompAssoc = setSummaryNotedAssocIDS($hdnCompAssoc, 'com', 0);
	$hdnCandAssoc = setSummaryNotedAssocIDS($hdnCandAssoc, 'cand', $addr);
	$hdnJobAssoc = setSummaryNotedAssocIDS($hdnJobAssoc, 'req', 0);		
	
	//Associating JobOrder contact to this note if joborder selected.
	$hdnContAssoc = assNoteJobOrderContacts($hdnJobAssoc, $hdnContAssoc);
	
	$notes=str_replace("&rsquo;","’",$notes);
	$notes=str_replace("&lsquo;","‘",$notes);
	$notes=str_replace("&rdquo;",'”',$notes);
	$notes=str_replace("&ldquo;",'“',$notes);
	
	// Removes NON-ASCII characters
	$notes = preg_replace('/[^(\x20-\x7F)\n]/',' ', $notes);
				
	// Handles non-printable characters
	$notes = preg_replace('/&#[5-6][0-9]{4};/',' ', $notes);
	
	$Notes_Sql= "INSERT INTO notes(contactid,cuser,type,notes,cdate,notes_subtype, candidate, company, contact, joborder, notify) VALUES ('".$candnotes."', '".$username."','cand','".$notes."',now(),'".$sbtype."','".$hdnCandAssoc."','".$hdnCompAssoc."','".$hdnContAssoc."','".$hdnJobAssoc."','".$notify_user."')";
	mysql_query($Notes_Sql,$db);
	$rid=mysql_insert_id($db);		
	
	if(is_numeric($rid) && $rid!=0)
	{		   
		// Aswini: Modified the Query to obtain data through notesAssociate_fun function for notes association		

		$Nquery= "SELECT notes.notes, notes.cuser, ".tzRetQueryStringDTime("notes.cdate","DateTime","/").", notes_subtype, manage.name, users.name,notes.sno, IFNULL(notes.joborder,'') AS JobOrder, IFNULL(notes.contact,'') AS Contact, IFNULL(notes.company,'') AS Company, IFNULL(notes.candidate,'') AS Candidate FROM users, notes LEFT JOIN manage FORCE INDEX ( type_sno ) ON ( manage.sno = notes_subtype AND manage.type = 'notes' ) WHERE notes.cuser = users.username  AND notes.sno = '".$rid."' ORDER BY notes.cdate DESC";
		$Ex_NT_query=getSqlRow($Nquery,$db);
		
		$subType='';
		if($Ex_NT_query[3]!=0)
			$subType="&nbsp;|&nbsp;".$Ex_NT_query[4];
		$Ex_NT_query[4]=addslashes($Ex_NT_query[4]);
		$notes=trim($notes);
			  
		$sqlAct="INSERT INTO cmngmt_pr (con_id,username,tysno,title,sdate,subject,lmuser,subtype, candidate, company, contact, joborder) VALUES 
		  ('".$conid."','".$Ex_NT_query[1]."','".$Ex_NT_query[6]."','Notes',now(),'".$notes."','".$Ex_NT_query[1]."','".$Ex_NT_query[4]."','".$hdnCandAssoc."','".$hdnCompAssoc."','".$hdnContAssoc."','".$hdnJobAssoc."')";
		$resAct=mysql_query($sqlAct,$db);
		
		if (APPLICANT_DISTRIBUTION_ENABLED == 'Y') {
			require("Applicant_Distribution/class.applicant_distribution.php");
			require("Applicant_Distribution/class.autoReAssignScript.php");
			require_once("mailsending_functions.php"); 
			$appDistObj = new applicantDistributions();
			$autoAppDistObj = new autoReAssignScripts($db);

			// select all need data			
			$candSno = str_replace("cand","",$cand_id);
			$candUsername = $cand_id;
			$assignedTo = $appDistObj->getAssignedToUserByCandId($candUsername);
			$groupId = $appDistObj->getGroupIdByCandid($candSno);
			$aplcntUsername = $appDistObj->getAplcntUsernameByCandId($candUsername);
			$aplcntSno = $appDistObj->getApplicantSnoByUsername($aplcntUsername);
			$aplcntFlag = $appDistObj->getApplicantFlagByCandidAplcntSno($candSno,$aplcntSno);
			$ad_note_menu = getManage($sbtype);
			$aplcntSerialNo = $aplcntSno;
			// "AD-Accept" is added in triggers cmngmt_pr_bi_trg
			
			// $assignedTo == $username &&
			if ($assignedTo == $username && $ad_note_menu == "AD-Accept" && $aplcntFlag == "N") {
				$conId= $candUsername.','.$aplcntUsername;
				$asignTo=$assignedTo;
				$subtype1 = "acceptedManual";
				$groupName = $appDistObj->getGroupNameBySno($groupId);
				$groupIdName = " (".$groupId." - ".stripslashes($groupName).")";
				$appDistObj->insertAplcntDistrActivity($conId,$asignTo,$subtype1,$groupIdName);
			}else if ($assignedTo == $username && $ad_note_menu == "AD-Reject" && $aplcntFlag == "N") {

				// "AD-Reject" Assign to next user in the Group.
				// get next avaliable seqno and user
				$conId= $candUsername.','.$aplcntUsername;
				$asignTo=$assignedTo;
				$subtype1 = "rejectedManual";
				$groupName = $appDistObj->getGroupNameBySno($groupId);
				$groupIdName = " (".$groupId." - ".stripslashes($groupName).")";
				$appDistObj->insertAplcntDistrActivity($conId,$asignTo,$subtype1,$groupIdName);
				// Rejected Notification has to set. 
				$aplcnt_name = $appDistObj->getCandidateName($candUsername); 
				$rejected_username = $appDistObj->getAssignToName($assignedTo); 
				$rejected_note= $notes;
				reject_aplcnt_distri_notification($aplcnt_name,$rejected_username,$rejected_note);

				// check max rotation
				$resultGroup = $autoAppDistObj->selectAllAutomationGroupsBySno($groupId);
				$rowGrup = mysql_fetch_row($resultGroup);
				$groupMaxRotation = $rowGrup[3];
				$defaultHouseUser = $rowGrup[4];

				$aplcntGroupSno = $groupId;
				$groupSno = $groupId;
				$aplcntSeqNo = $appDistObj->getSeqNoByUsrIdGroupid($assignedTo,$groupId);
				$lastAssignedSeqNo = $aplcntSeqNo;	

				$resultAplcnt1 = $autoAppDistObj->selectApplicantsByGroupSequence($groupSno,$aplcntSeqNo);	
				$rowGrup1 = mysql_fetch_row($resultAplcnt1);
				$aplcntRotation = $rowGrup1[5];

				$nxtAssignedSeqNo = $autoAppDistObj->getNxtAvailableSeqNo($aplcntGroupSno,$aplcntSeqNo,$lastAssignedSeqNo);
				$aplcntDistriSno =  $appDistObj->getApplicantDistributionSno($groupId,$aplcntSno);
				
				if($aplcntRotation >= $groupMaxRotation){
					$autoAppDistObj->updateCandidateOwner($candSno,$defaultHouseUser);
					$autoAppDistObj->updateApplicantDistriFlag($aplcntDistriSno,'S',$defaultHouseUser);
				}else if ($nxtAssignedSeqNo !='false') {
					$lastAssignedSeqNo = $nxtAssignedSeqNo;
					// get userid based on seqNo and Group Sno 
					$nxtAssignedUsrId = $autoAppDistObj->getUserIdByGroupSnoSeqNo($aplcntGroupSno,$nxtAssignedSeqNo);

					// update assign to userid and seq no
					$autoAppDistObj->updateApplicantDistriSeqNoRotationCount($aplcntDistriSno,$nxtAssignedSeqNo);
					$autoAppDistObj->updateAdGroupMemberCandCount();

					$aplcntDistriCuser = $autoAppDistObj->getAplcntCuserFrmApplicantDistri($aplcntDistriSno);
					//update candidate assign to and assign date columns
					$autoAppDistObj->updateCandidateAssignToDate($candSno,$nxtAssignedUsrId,$aplcntDistriCuser,$groupId);

					// Updating Applicants Record 
					$resultAplcnt = $autoAppDistObj->selectApplicantDetailBySno($aplcntSerialNo);
					if (mysql_num_rows($resultAplcnt)>0) {
						$rowCand = mysql_fetch_row($resultAplcnt);
						$autoAppDistObj->updateApplicantAssignToDate($aplcntSerialNo,$nxtAssignedUsrId,$aplcntDistriCuser,$groupId);
					}
					$asignTo = $nxtAssignedUsrId;
					$conId = $candUsername.','.$aplcntUsername;
					$subtype1 = 'Manual'; 
					$autoAppDistObj->insertAplcntDistrActivity($conId,$asignTo,$subtype1,$aplcntDistriCuser,$groupIdName);
					// Re-Assign Notification has to set
					$aplcnt_name = $appDistObj->getCandidateName($candUsername);
					$assignedByName =  $appDistObj->getAssignToName($username);
					assigned_aplcnt_distri_notification($aplcnt_name,$candSno,$nxtAssignedUsrId,$assignedByName);	
				}				
			}
			
		}

		if(HUBSPOT_ENABLED == 'Y' && HUBSPOT_USER_ACCESS =='Y')
		{
			$cmngmt_pr_last_insert_id = mysql_insert_id($db);
			$akken_psos_Hubspot_path = substr($akken_psos_include_path, 0, -8)."HubSpot/";
			require_once($akken_psos_Hubspot_path."notes_sync.php");
			$notes_sync = sync_notes_to_Hubspot("crmcandidates",$conid,$cmngmt_pr_last_insert_id);
			if($notes_sync['status'] != "error")
			{
				$eng_id = $notes_sync['engagement']['id'];
				$insert_notes = insert_hubspot_notes($candnotes,$rid,$eng_id);
			}
		}
		
		$DispText="^^ACTSplit^^";
		//$rtype="activities";

		$notified_users = "";
		if($notify_user!='')
		{
			$get_users_que = "SELECT GROUP_CONCAT(name) as name FROM users WHERE username IN (".$notify_user.")";
			$get_users_res = mysql_query($get_users_que,$db);
			$get_users_row = mysql_fetch_array($get_users_res);
			$notified_users = $get_users_row['name'];
		}
		
		// Aswini: Modified Display text for notes association which gets data from the assNotesRecordsAppend function in PSOS/include/commonfuns.inc
		
		$DispText.="<b>".strtolower($Ex_NT_query[2]).$subType."&nbsp;-&nbsp;".html_tls_specialchars($Ex_NT_query[5],ENT_QUOTES)."</b><br/>".nl2br(html_tls_specialchars($Ex_NT_query[0],ENT_QUOTES));
		if($notified_users!='')
		{
			$DispText.="<br/><b>Notified:</b> ".$notified_users;
		}
		
		$DispText.=assNotesRecordsAppend($Ex_NT_query, $candnotes, $module);
	}
	else
	{
		$DispText.=0;		
	}
}

if($TodoImpact=='Yes' || $donestatus=='Yes')
	   echo "^^ACTSplit^^";
//activities	   
if($rtype=="activities" || $TodoImpact=='Yes' || $donestatus=='Yes'){
header("Content-type: text/html; charset=".CONVERT_DEFAULT_MAIL_CHAR);
					
				$moduleForActivities = "CRM->Cand"; //Module Name for CRM->Candidates.
				//Function for checking same domian for Candidates.
				$ats_con_id = '';
				$ats_emp_id = '';
				$ats_app_con_id = '';
				$ats_activity_que = "select candid from candidate_list where username='".$con_id."'";
				$ats_activity_res = mysql_query($ats_activity_que,$db);
				$ats_activity_row = mysql_fetch_row($ats_activity_res);
				//echo $ats_activity_row[0];
				if($ats_activity_row[0] !=''){
					if(strpos("*".$ats_activity_row[0], 'emp')){
						$str_emp = substr($ats_activity_row[0], 3);  
						$emp_act_que ="select username from emp_list where sno='".$str_emp."'";
						$emp_act_res = mysql_query($emp_act_que,$db);
						$emp_act_row = mysql_fetch_row($emp_act_res);
						$ats_con_id = $emp_act_row[0];
						$ats_emp_id = $ats_activity_row[0];
						
						$app_que ="select username from applicants where username like '%(".$emp_act_row[0].")%'";
						$app_act_res = mysql_query($app_que,$db);
						$app_act_row = mysql_fetch_row($app_act_res);
						
						if($app_act_row[0] !='' &&  strpos($app_act_row[0],"(") != false){
							$ats_app_con_arr = explode('(',$app_act_row[0]);
							if(count($ats_app_con_arr) > 0){
								$ats_app_con_id = $ats_app_con_arr[0];
							}
						}
					}
					if(strpos("*".$ats_activity_row[0], 'con')){
						$ats_con_id = $ats_activity_row[0];
					}
				}
				$checkDomainCond_activities = checkDomainForActivities($con_id,$moduleForActivities);
				$module = isset($_GET['module'])?$_GET['module']:'';
				if($module == 'CRM'){
					$activity_status = "AND cmngmt_pr.activity_status = '0' ";
				}
				else{
					$activity_status = '';
				}
				if($ats_con_id == ''){
					$Act_que="select sno,IF(DATE_FORMAT(CONVERT_TZ(sdate,'SYSTEM','".$user_timezone[1]."'),'%c/%e/%Y')='0/0/0000','',".tzRetQueryStringDTime("sdate","DateTime","/")."),users.name,subject,CASE WHEN title = 'Email' THEN 'Sent Mail' WHEN title = 'AplcntDistri' THEN 'Applicant Distribution' ELSE title END as title,tysno,cmngmt_pr.username,cmngmt_pr.subtype from cmngmt_pr,users where title IN ('Appointment','Campaign','Email','Event','Task','Postings','Submissions','REmail','Notes','Submitted','Placed','Document','CRM-to-HRM','Employee-to-CRM','Hiring-to-CRM','TextUs SMS Sent','TextUs SMS Received','Textus Brdcst Sent','Call-Em-All Msg Sent','Call-Em-All Msg Rcvd','Call-Em-All BC Sent','eSkill','HubSpot','CaptureME','AplcntDistri') $checkDomainCond_activities AND cmngmt_pr.lmuser = users.username AND MATCH(con_id) AGAINST('".$conid."' in boolean mode) $activity_status order by sdate desc";
				}
				else{

					  $Act_que="select * from ((select sno,IF(DATE_FORMAT(CONVERT_TZ(sdate,'SYSTEM','".$user_timezone[1]."'),'%c/%e/%Y')='0/0/0000','',".tzRetQueryStringDTime("sdate","DateTime","/")."),users.name,subject,CASE WHEN title = 'Email' THEN 'Sent Mail' WHEN title = 'AplcntDistri' THEN 'Applicant Distribution' ELSE title END as title,tysno,cmngmt_pr.username,sdate,cmngmt_pr.subtype from cmngmt_pr,users where title IN ('Appointment','Campaign','Email','Event','Task','Postings','Submissions','REmail','Notes','Submitted','Placed','Document','CRM-to-HRM','Employee-to-CRM','Hiring-to-CRM','TextUs SMS Sent','TextUs SMS Received','Textus Brdcst Sent','Call-Em-All Msg Sent','Call-Em-All Msg Rcvd','Call-Em-All BC Sent','eSkill','HubSpot','Responded Details','CaptureME','AplcntDistri') $checkDomainCond_activities AND cmngmt_pr.lmuser = users.username AND MATCH(con_id) AGAINST('".$conid."' in boolean mode) $activity_status) UNION (select sno,IF(DATE_FORMAT(CONVERT_TZ(sdate,'SYSTEM','".$user_timezone[1]."'),'%c/%e/%Y')='0/0/0000','',".tzRetQueryStringDTime("sdate","DateTime","/")."),users.name,subject,CASE WHEN title = 'Email' THEN 'Sent Mail' WHEN title = 'AplcntDistri' THEN 'Applicant Distribution' ELSE title END as title,tysno,cmngmt_pr.username ,sdate,cmngmt_pr.subtype from cmngmt_pr,users where title IN ('Appointment','Campaign','Email','Event','Task','Postings','Submissions','REmail','Notes','Submitted','Placed','Document','CRM-to-HRM','Employee-to-CRM','Hiring-to-CRM','TextUs SMS Sent','TextUs SMS Received','Textus Brdcst Sent','Call-Em-All Msg Sent','Call-Em-All Msg Rcvd','Call-Em-All BC Sent','eSkill','HubSpot','AplcntDistri') $checkDomainCond_activities AND cmngmt_pr.lmuser = users.username AND MATCH(con_id) AGAINST('".$ats_con_id."' in boolean mode) $activity_status)UNION (select sno,IF(DATE_FORMAT(CONVERT_TZ(sdate,'SYSTEM','".$user_timezone[1]."'),'%c/%e/%Y')='0/0/0000','',".tzRetQueryStringDTime("sdate","DateTime","/")."),users.name,subject,CASE WHEN title = 'Email' THEN 'Sent Mail' WHEN title = 'AplcntDistri' THEN 'Applicant Distribution' ELSE title END as title,tysno,cmngmt_pr.username ,sdate,cmngmt_pr.subtype from cmngmt_pr,users where title IN ('Appointment','Campaign','Email','Event','Task','Postings','Submissions','REmail','Notes','Submitted','Placed','Document','CRM-to-HRM','Employee-to-CRM','Hiring-to-CRM','TextUs SMS Sent','TextUs SMS Received','Textus Brdcst Sent','Call-Em-All Msg Sent','Call-Em-All Msg Rcvd','Call-Em-All BC Sent','AplcntDistri') $checkDomainCond_activities AND cmngmt_pr.lmuser = users.username AND MATCH(con_id) AGAINST('".$ats_emp_id."' in boolean mode) $activity_status) UNION (select sno,IF(DATE_FORMAT(CONVERT_TZ(sdate,'SYSTEM','".$user_timezone[1]."'),'%c/%e/%Y')='0/0/0000','',".tzRetQueryStringDTime("sdate","DateTime","/")."),users.name,subject,CASE WHEN title = 'Email' THEN 'Sent Mail' WHEN title = 'AplcntDistri' THEN 'Applicant Distribution' ELSE title END as title,tysno,cmngmt_pr.username ,sdate,cmngmt_pr.subtype from cmngmt_pr,users where title IN ('Appointment','Campaign','Email','Event','Task','Postings','Submissions','REmail','Notes','Submitted','Placed','Document','CRM-to-HRM','Employee-to-CRM','Hiring-to-CRM','TextUs SMS Sent','TextUs SMS Received','Textus Brdcst Sent','Call-Em-All Msg Sent','Call-Em-All Msg Rcvd','Call-Em-All BC Sent','AplcntDistri') $checkDomainCond_activities AND cmngmt_pr.lmuser = users.username AND MATCH(con_id) AGAINST('".$ats_app_con_id."' in boolean mode ) $activity_status)) as activity  order by sdate desc";
						
						
				}
				$totalRec=getSqlNumber($Act_que);
				$Act_que.=" limit 0,20";
				$Ex_Act_que=mysql_query($Act_que,$db);	
						
					?>
					<table class="panel-table-scroll">
                      <thead>

                      <tr class="panel-table-header">
                         <th width=30%>Date</th>
                          <th width=20%>By</th>
                          <th width=30%>Title</th>
                          <th width=20%>Type</th>
                    </tr>
										
					<?php
						$POBsubtype = array("SENT-POB DOCUMENTS","COMPLETED-POB DOCUMENTS","SIGNER ATTACHED DOCUMENT");
					 	if($totalRec>0){
	  						while($row=mysql_fetch_array($Ex_Act_que))
	  						{
								$despath="";
								$width="";
								$height="";
								if($row[4]=="Sent Mail" || $row[4]=="REmail")
								{
									$despath="/BSOS/Activities/email/showemaildet.php?addr=".$conid."&reply=no&message=".$row[5]."&con_id=".$conid."&hidemodule=".$module."&module=".$module;
									$width="980";
									$height="620";
								}	
								else if($row[4]=="Event" || $row[4]=="Placed" || $row[4]=="Submitted")
								{//hidemodule parameter is required to pass in upsnotes.php
									$despath="/BSOS/Activities/event/editnotes.php?addr=".$conid."&sno=".$row[5]."&con_id=".$conid."&do_action=editevent&moduletype=".$module."&act_type=".$row[4]."&hidemodule=".$module."&module=".$module;
									$width="750";
									$height="430";
								}	
								else if($row[4]=="Document")
								{
									$despath="/BSOS/Activities/document/editdoc.php?addr=".$conid."&sno=".$row[5]."&con_id=".$conid."&do_action=viewdoc&hidemodule=".$module."&module=".$module;
									$width="750";
									$height="500";
								}	
								else if($row[4]=="Task")
								{
									$despath="/BSOS/Activities/task/edittask.php?module_type_appoint=".$module_type_appoint."&addr=".$conid."&sno=".$row[5]."&con_id=".$conid."&do_action=edittask&hidemodule=".$module."&module=".$module;
									$width="700";
									$height="700";
								}	
								else if($row[4]=="Appointment")
								{
									$despath="/BSOS/Activities/appointment/editappoint.php?module_type_appoint=".$module_type_appoint."&addr=".$conid."&line=".$row[5]."&do_action=editappoint&hidemodule=".$module."&module=".$module;
									$width="800";
									$height="800";
								}	
								else if($row[4]=="Campaign")
								{
									$despath="/BSOS/Marketing/Campaigns/showcampaigndet.php?frm=act&val=".$row[5]."&hidemodule=".$module."&module=".$module;
									$width="900";
									$height="900";
								}	
								else if($row[4]=="Postings")
								{
									$despath="/BSOS/Sales/Req_Post/postview.php?addr=".$conid."&val=".$row[5]."&con_id=".$conid."";
									$width="750";
									$height="800";
								}	
								else if($row[4]=="Submissions")
								{
									$despath="/BSOS/Sales/Req_Mngmt/subinfo.php?addr=".$conid."&val=".$row[5]."&con_id=".$conid."&nav=candidate&hidemodule=".$module."&module=".$module;
									$width="750";
									$height="800";
								}		
		                        else if($row[4] == "Responded Details")
								{
									$despath="/BSOS/Sales/Req_Mngmt/req_sub.php?frm=act&sno=".$row[5]."&hidemodule=".$module."&module=".$module;
									$width="750";
									$height="800";
								}
								else if($row[4] == "Notes")
								{
									$despath="/BSOS/Sales/Req_Mngmt/notes_details.php?sno=".$row[5]."&con_id=".$conid."&csno=".$row[0]."&hidemodule=".$module."&module=".$module;
									$width="550";
								    $height="300";
								}		
								
								if($row[4]=="REmail")
									$row[4]="Received Mail";
								else if($row[4]=="Campaign")
									$row[4]="eCampaign";
								else if($row[4]=="Submissions")
									$row[4]="Submission";
								else if($row[4]=="Postings")
									$row[4]="Posting";
								else if($row[4]=="Textus Brdcst Sent")
		            				$row[4]	= "TextUs Broadcast Sent";
								else if($row[4]=="Call-Em-All Msg Sent")
		            				$row[4]	= "Call-Em-All SMS Sent";
								else if($row[4]=="Call-Em-All Msg Rcvd")
		            				$row[4]	= "Call-Em-All SMS Received";
								else if($row[4]=="Call-Em-All BC Sent")
		            				$row[4]	= "Call-Em-All Broadcast SMS Sent";
									
								if($despath ==''){
									?>
									<tr align="left" class="panel-table-content" valign="middle" style="cursor: hand;">
									<?php
								}else{
									?>
									<tr align="left" class="panel-table-content" valign="middle" onClick="javascript:openewWindow('<?php echo $despath;?>','subinfo',<?php echo $width;?>,<?php echo $height;?>)" style="cursor: hand;">
									<?php
								}
								if (CANDIDATE_VIEW_POB_DOCS == "N" && in_array($row['subtype'], $POBsubtype)) 
								{
									?>
									<tr align="left" class="panel-table-content" valign="middle" onClick="javascript:alert('You do not have access to view POB Documents.');" style="cursor: hand;">
									<?php
								}else{
									?>						  
			                        <tr align="left" class="panel-table-content" valign="middle" onClick="javascript:openewWindow('<?php echo $despath;?>','subinfo',<?php echo $width;?>,<?php echo $height;?>)" style="cursor: hand;">
									<?php
								}
								?>
										<td><?php echo strtolower($row[1]);?>&nbsp;</td>
				                      	<td title="<?php echo dispTextdb1($row[2]);?>"><?php echo dispTextdb1($row[2])?>&nbsp;</td>
				                      	<td  title="<?php echo str_replace("\\","",dispTextdb1($row[3])); ?>"><?php echo str_replace("\\","",dispTextdb1((strlen($row[3])>10)?substr($row[3],0,10)."...":$row[3])); ?>&nbsp;</td>
				                      	<td><?php echo dispTextdb1($row[4])?>&nbsp;</td>
			                    	</tr>
								<?php
							}
						}
					 	else 
					 	{	?>
					 		<tr class="panel-table-content" valign="middle" align="center"><td colspan=4>no data found</td></tr>
					<?  } ?>

                      </tbody>
    </table>
	<?
	//$DispText="";
}

if($rtype=="subjoborder")
{
	?>
	<table class="panel-table-scroll" width="100%">
	<thead>
	<tr class="panel-table-header">
		<th>Submitted</th>
		<th>Company</th>
		<th>Job title</th>
		<th>Status</th>
		<?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { ?>
		<th>Shift</th>
		<?php } ?>
		<th colspan=3 align="center">Actions</th>
	</tr>

		<?php
		if(SHIFT_SCHEDULING_ENABLED == 'Y')
		{
			// Modified the query to display the exisitng job orders submitted in the candidate record
			$query = "SELECT reqresponse.posid,reqresponse.sno,".tzRetQueryStringDTime("reqresponse.rdate","DateTime","/").",reqresponse.stage,reqresponse.seqnumber,reqresponse.username,manage.sno,manage.name,resume_status.status AS resume_status, resume_status.shift_id, resume_status.sno as res_sno FROM reqresponse,manage,resume_status 
				JOIN posdesc jo ON (jo.posid = reqresponse.posid)
				WHERE FIND_IN_SET('".$conid."',reqresponse.resumeid) > 0 AND jo.deptid IN (".$deptAccesSno.") AND reqresponse.par_id='0' AND reqresponse.posid=resume_status.req_id AND resume_status.res_id='".$cand_sno."' AND resume_status.status=manage.sno AND manage.type='interviewstatus' AND resume_status.pstatus in('I', 'S') AND reqresponse.seqnumber=resume_status.seqnumber GROUP By resume_status.shift_id,resume_status.req_id,resume_status.status
			ORDER BY reqresponse.rdate DESC";
			
		}
		else
		{
			$query = "SELECT reqresponse.posid,reqresponse.sno,".tzRetQueryStringDTime("reqresponse.rdate","DateTime","/").",reqresponse.stage,reqresponse.seqnumber,reqresponse.username,manage.sno,manage.name,resume_status.status AS resume_status, resume_status.sno as res_sno FROM reqresponse,manage,resume_status JOIN posdesc jo ON (jo.posid = reqresponse.posid) WHERE FIND_IN_SET('".$conid."',reqresponse.resumeid) > 0 AND reqresponse.par_id='0' AND jo.deptid IN (".$deptAccesSno.") AND reqresponse.posid=resume_status.req_id AND resume_status.res_id='".$cand_sno."' AND resume_status.status=manage.sno AND manage.type='interviewstatus' AND resume_status.pstatus in('I', 'S') AND reqresponse.seqnumber=resume_status.seqnumber ORDER BY reqresponse.rdate DESC";
		}
		$res=mysql_query($query,$db);
		$count=mysql_num_rows($res);
		$Matchcand = array();
		$candsno = array();
		$status_cand = array();
		while ($result=mysql_fetch_array($res))
		{
			$subno=$result[1];
			$seqnumber = $result[4];
			$sid="'".str_replace(",","','",$result[0])."'";
			$resume_status=$result['resume_status'];

			if($result[5]=="")
			{
				$pque="select username from posdesc where posid='".$addr."'";
				$pres=mysql_query($pque,$db);
				$prow=mysql_fetch_row($pres);
				$result[5]=$prow[0];
			}

			$uID=getOwnerName($result[5]);

			$dque = "select posid,postitle,company,posstatus,type,status,IF(accessto = 'all', 'Public', IF(FIND_IN_SET('".$username."',accessto)>0 , 'Share','NONE')),shift_type from posdesc where posid in ($sid) ";
			$dres=mysql_query($dque,$db);
			while($drow=mysql_fetch_row($dres))
			{
				if($drow[0]!="")
				{
					if($drow[4]!="" && $drow[4]!='none')
					{
						if($drow[4]=='staffoppr')
							$CompQry="SELECT cname FROM staffoppr_cinfo  WHERE sno=".$drow[2];
						else if($drow[4]=='staffacc')
							$CompQry="SELECT cname FROM staffacc_cinfo WHERE  sno=".$drow[2];
						$CompRes=mysql_query($CompQry,$db);
						$CompData=mysql_fetch_row($CompRes);
						$CompName=$CompData[0];
					}
					
					if(SHIFT_SCHEDULING_ENABLED == 'Y')
					{
						$shift_id 	= $result[9];
						if($shift_id!="" || $shift_id!=0)
						{
							$shift_que	= "SELECT shiftname FROM shift_setup WHERE sno='".$shift_id."'";
							$shift_res	= mysql_query($shift_que,$db);
							$shift_row	= mysql_fetch_row($shift_res);
							$shiftName	= $shift_row[0];
						}

						if($drow[7] == "perdiem")
						{
							//fetch the records from resume_status if job shift type is perdiem.
							//pushing the records here without consider the data from the above main query
							$perdiem_data_qry = "SELECT reqresponse.posid,reqresponse.sno,".tzRetQueryStringDTime("reqresponse.rdate","DateTime","/").",reqresponse.stage,reqresponse.seqnumber,reqresponse.username,manage.sno,manage.name,resume_status.status AS resume_status, resume_status.shift_id,resume_status.sno as res_sno FROM reqresponse,manage,resume_status JOIN posdesc jo ON (jo.posid = reqresponse.posid) WHERE FIND_IN_SET('".$conid."',reqresponse.resumeid) > 0  AND reqresponse.par_id='0' AND jo.deptid !='0' AND jo.deptid IN (".$deptAccesSno.") AND reqresponse.posid=resume_status.req_id AND resume_status.res_id='".$cand_sno."' AND resume_status.status=manage.sno AND manage.type='interviewstatus' AND resume_status.pstatus in('I', 'S') AND reqresponse.seqnumber=resume_status.seqnumber AND resume_status.req_id IN ($sid) 
								AND resume_status.Shift_id=$shift_id 
							GROUP By resume_status.shift_id,resume_status.req_id,resume_status.status,resume_status.seqnumber ORDER BY reqresponse.rdate DESC";
							$perdiem_data_rs=mysql_query($perdiem_data_qry,$db) or die(mysql_error());

							while ($perdeim_row=mysql_fetch_array($perdiem_data_rs))
							{
								$perdiem_subno			= $perdeim_row[1];
								$perdiem_seqnumber 		= $perdeim_row[4];
								$perdiem_resume_status	= $perdeim_row['resume_status'];

								$Matchcand["jobsr"][]	= array($perdeim_row[2],$CompName,$drow[1],$perdeim_row[7],$drow[0],$perdiem_seqnumber,$drow[5],$drow[6],$perdiem_resume_status,$perdiem_subno, $shiftName,$shift_id,$perdeim_row[10]);
							}

						}
						else
						{							
							$Matchcand["jobsr"][]	= array($result[2],$CompName,$drow[1],$result[7],$drow[0],$seqnumber,$drow[5],$drow[6],$resume_status,$subno, $shiftName,$shift_id,$result[10]);
						}


					}
					else
					{
						$Matchcand["jobsr"][]	= array($result[2],$CompName,$drow[1],$result[7],$drow[0],$seqnumber,$drow[5],$drow[6],$resume_status,$subno,$shift_id,$result[9]);
					}
					$candsno[]		= $drow[0];
					$status_cand[]		= $result[7];
				}
			}
		}

		if($count>0)
		{
			$lpe=count($Matchcand["jobsr"]);
			for($i=0;$i<$lpe;$i++)
			{
				$jobid=$Matchcand['jobsr'][$i][4];
				$seq_num=$Matchcand['jobsr'][$i][5];
				$resum_status = $Matchcand['jobsr'][$i][8];
				$candaddr = str_replace("cand","",$conid);

				$shiftsnos_param = "";
				if(SHIFT_SCHEDULING_ENABLED == 'Y')
				{
					$shiftsnos_param = "&shiftsnos=".$Matchcand['jobsr'][$i][11];
					$res_sno = $Matchcand['jobsr'][$i][12];
				}
				else
				{
					$res_sno = $Matchcand['jobsr'][$i][11];
				}

				$SubmittedCands=" onClick=\"javascript:openewWindow('/BSOS/Sales/Req_Mngmt/subinfo.php?addr=$candaddr&sno=$jobid&con_id=$conid&mode=subcand&seqnumber=$seq_num".$shiftsnos_param."&res_sno=".$res_sno."&module=".$_GET['module']."','subinfo',750,800)\" style='cursor:hand'" ;

				?>
				<tr class="panel-table-content" align="left" valign="middle" style="cursor: pointer;"  >
					<td <?php echo $SubmittedCands;?> ><?php echo $Matchcand["jobsr"][$i][0];?>&nbsp;</td>
					<td <?php echo $SubmittedCands;?> title="<?php echo dispTextdb($Matchcand["jobsr"][$i][1]);?>"><? echo strlen($Matchcand["jobsr"][$i][1])>20?dispTextdb(substr($Matchcand["jobsr"][$i][1],0,20)."..."):dispTextdb($Matchcand["jobsr"][$i][1])?>&nbsp;</td>
					<td <?php echo $SubmittedCands;?> title="<?php echo dispTextdb($Matchcand["jobsr"][$i][2]);?>"><? echo strlen($Matchcand["jobsr"][$i][2])>15?dispTextdb(substr($Matchcand["jobsr"][$i][2],0,15)."..."):dispTextdb($Matchcand["jobsr"][$i][2])?>&nbsp;</td>
					<td <?php echo $SubmittedCands;?> title="<?php echo dispTextdb($Matchcand["jobsr"][$i][3]);?>"><? echo strlen($Matchcand["jobsr"][$i][3])>20?dispTextdb(substr($Matchcand["jobsr"][$i][3],0,20)."..."):dispTextdb($Matchcand["jobsr"][$i][3])?>&nbsp;</td>
					<?php
					if(SHIFT_SCHEDULING_ENABLED == 'Y')
					{
					?>					
					<td <?php echo $SubmittedCands;?> title="<?php echo dispTextdb($Matchcand["jobsr"][$i][10]);?>"><? echo strlen($Matchcand["jobsr"][$i][10])>20?dispTextdb(substr($Matchcand["jobsr"][$i][10],0,20)."..."):dispTextdb($Matchcand["jobsr"][$i][10])?>&nbsp;</td>
					<?php
					}

					if($Matchcand["jobsr"][$i][3] == "Processing Placement")
					{
						?>
						<td>&nbsp;</td>
						<?php
					}else{
						?>					
						<td onClick="javascript:winopennew_Interview_status('resumereviewf.php?addr=<?php echo $jobid."|".$cand_sno."|".rawurlencode($resum_status)."|".$Matchcand["jobsr"][$i][11]; ?>&subno=<?php echo $Matchcand["jobsr"][$i][10];?>&candidate_id=<?php echo $con_id; ?>&seqnumber=<?php echo $seq_num; ?>&res_sno=<?php echo $res_sno; ?>','3',<?php echo $i; ?>)"><i class='fa fa-ticket' title='Update Status' alt='Update Status'></i>&nbsp;</td>
					<?php } ?>
					<td align="center" onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/notespopup.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $jobid; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $Matchcand["jobsr"][$i][7]; ?>&postatus=<?php echo $Matchcand["jobsr"][$i][6]; ?>','CandidateNotes','430','165')"><i class='fa fa-file-text' title='Notes' alt='Notes'></i>&nbsp;</td>
					<?php if(OUTLOOK_PLUG_IN!="Y" || OUTLOOK_TASK_MANAGER!="Y") { ?>						
					<td align="center"  onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/todoPopupforcandidates.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $jobid; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $Matchcand["jobsr"][$i][7]; ?>&postatus=<?php echo $Matchcand["jobsr"][$i][6]; ?>','ToDoReminders','425','230')"><i title='To Do' align='center' border='0' alt='' class='fa fa-thumb-tack'></i>&nbsp;</td> 
					<?php } ?>
				</tr>
				<? 
			}
		}
		else
		{
			?>
			<tr class="panel-table-content" valign="middle" align="center"><td colspan=7>no data found</td></tr>
			<?
			}
		?>
	</tbody>
	</table>
	<?php
	$DispText="";
}

if($rtype=="placedjoborder")
{
	?>
                        <table class="panel-table-scroll" width="100%">
                        <thead>
				        <tr class="panel-table-header">
                            <th>Placed</th>
                            <th>Company</th>
                            <th>Job title</th>
                            <th>Status</th>
                            <?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { ?>
				<th>Shift</th>
			    <?php } ?>
                        </tr>
                            <?php
				 $query = "select reqresponse.posid,reqresponse.sno,".tzRetQueryStringDTime("resume_status.mdate","DateTime","/").",reqresponse.stage,reqresponse.seqnumber,reqresponse.username,manage.sno,manage.name,resume_status.shift_id from reqresponse,manage,resume_status
				 JOIN posdesc jo ON (jo.posid = reqresponse.posid)
				  where FIND_IN_SET('".$conid."',resumeid) > 0 and par_id='0' AND jo.deptid !='0' AND jo.deptid IN(".$deptAccesSno.") and reqresponse.posid=resume_status.req_id and resume_status.res_id='".$cand_sno."' and resume_status.status=manage.sno and manage.type='interviewstatus' and resume_status.pstatus='P' AND reqresponse.seqnumber=resume_status.seqnumber   ORDER BY reqresponse.rdate DESC";
				$res=mysql_query($query,$db);
                            $count=mysql_num_rows($res);
                            $que="select sno,candid,username from candidate_list where username='".$conid."'";
							$cand_candid=mysql_query($que,$db);
                            $cand_empsno=mysql_fetch_row($cand_candid);
							if(substr_compare($cand_empsno[1],"emp", 0, 3)==0)
							{
								$table="empcon";
								$table1="select sno,name,username from emp_list where sno='";
							}
							if(substr_compare($cand_empsno[1],"con", 0, 3)==0)
							{
								$table="consultant";
								$table1="select serial_no,name,username from consultant_list where serial_no='";
							}
								$emplist_sno=substr($cand_empsno[0], 3,(strlen($cand_empsno[0])-3));
								$emp_que = $table1.$emplist_sno."'";
								$emp_res = mysql_query($emp_que, $db);
								$emp_row = mysql_fetch_row($emp_res);
								$emp_jobs_que="select sno from ".$table."_jobs where username='".$emp_row[2]."'";
								$emp_jobs_res = mysql_query($emp_jobs_que, $db);
								$emp_jobs_row = mysql_fetch_row($emp_jobs_res);
								$Matchcand = array();
								$candsno = array();
								$status_cand = array();
							while ($result=mysql_fetch_array($res))
                            {
								$subno=$result[1];
								$seqnumber = $result[4];
								$sid="'".str_replace(",","','",$result[0])."'";
								
								if($result[5]=="")
								{
									$pque="select username from posdesc where posid='".$addr."'";
									$pres=mysql_query($pque,$db);
									$prow=mysql_fetch_row($pres);
									$result[5]=$prow[0];
								}
								
		  						$uID=getOwnerName($result[5]); 
								
								 $dque = "select posid,postitle,company,posstatus,type from posdesc where posid in ($sid) GROUP BY posid ";
								$dres=mysql_query($dque,$db);	
								while($drow=mysql_fetch_row($dres))
								{
									if($drow[0]!="")
									{
									   if($drow[4]!="" && $drow[4]!='none')
									   {
											 $CompQry="SELECT cname,shiftid FROM placement_jobs,staffacc_cinfo WHERE placement_jobs.client=staffacc_cinfo.sno AND staffacc_cinfo.type IN ('CUST', 'BOTH') AND posid='".$drow[0]."' AND candidate='".$conid."' AND shiftid='".$result[8]."' GROUP BY placement_jobs.shiftid  ";
											$CompRes=mysql_query($CompQry,$db);
											$CompData=mysql_fetch_row($CompRes);
											$CompName=$CompData[0];
											$ShiftId =$CompData[1];
											$ShiftName ='';
											if ($ShiftId !=0) {
												$selectShiftName = "SELECT shiftname FROM shift_setup WHERE sno='".$ShiftId."'";
												$Shiftres=mysql_query($selectShiftName,$db);
												$Shiftrow=mysql_fetch_row($Shiftres);
												$ShiftName = $Shiftrow[0];
											}
											
										}
										$selectReasonCodes = "SELECT rc.sno,rc.reason,rc.reason_description AS reasonNotes,rc.type,hj.ustatus,el.name FROM resume_status rs
											LEFT JOIN candidate_list cl ON (rs.res_id=cl.sno AND cl.ctype='Employee')
											LEFT JOIN emp_list el ON (CONCAT('emp',el.sno) = cl.candid)
											LEFT JOIN hrcon_jobs hj ON (hj.username = el.username AND hj.posid = rs.req_id)
											LEFT JOIN reason_codes rc ON (hj.reason_id = rc.sno)
											WHERE rs.req_id='".$drow[0]."' AND rs.res_id='".$cand_sno."' AND rs.shift_id='".$ShiftId."' ";
										$resultCode = mysql_query($selectReasonCodes,$db);
										$reason_code_tooltip ='';
										if (mysql_num_rows($resultCode)>0) {
											$rowCode = mysql_fetch_assoc($resultCode);
											if ($rowCode['sno'] !=0) {
												$rowCode['reason'] = iconv('utf-8', 'ascii//TRANSLIT', $rowCode['reason']);
												$rowCode['reasonNotes'] = iconv('utf-8', 'ascii//TRANSLIT', $rowCode['reasonNotes']);
												$reason_code_tooltip = '<span><a href="#" class="tooltip"><i class="fa fa-info-circle"></i><span class="tooltipReasonCode"><table class="notestooltiptable" width="250" height="80"><tbody><tr><td class="notestooltip" align="center" style="text-align:center"><div style="font-weight:lighter;">'.stripslashes($rowCode['reason']).'</div></td></tr><tr><td class="notestooltip"><div style="padding: 4px !important;">'.stripslashes($rowCode['reasonNotes']).'</div></td></tr></tbody></table></span></a></span>';
											}
										}						
										$Matchcand["jobsr"][]=array($result[2],$CompName,$drow[1],$result[7],$drow[0],$cand_empsno[0],$cand_empsno[1],$seqnumber,$ShiftName,$ShiftId,$reason_code_tooltip);
										$candsno[]=$drow[0];
										$status_cand[]=$result[7];
										
									}
								}
                        
                            }
							$Matchcand["jobsr"] = array_values(array_map("unserialize", array_unique(array_map("serialize", $Matchcand["jobsr"]))));
							
                            if($count>0)
                            {
								$lpe=count($Matchcand["jobsr"]);
                                for($i=0;$i<$lpe;$i++)
                                {	
                                	$reason_code_tooltip_data ='';
                                	$reason_code_tooltip_data = $Matchcand["jobsr"][$i][10];
									 $PlacedCands="onClick=\"javascript:openewWindow('../../Sales/Req_Mngmt/viewjobs.php?candidateVal=".$cand_empsno[2]."&req_id=".$Matchcand["jobsr"][$i][4]."&cand_sno=".$Matchcand["jobsr"][$i][5]."&candid=".$Matchcand["jobsr"][$i][6]."&tol=".$contact_mail."&FromSubmit=yes&seqnumber=".$Matchcand["jobsr"][$i][7]."&shiftid=".$Matchcand["jobsr"][$i][9]."&module=".$module." ','subinfo',900,800)\" style='cursor:hand'" ;
									 ?>
									<tr class="panel-table-content" align="left" valign="middle" style="cursor: hand;"  <?php echo $PlacedCands;?>>
															<td><?php echo $Matchcand["jobsr"][$i][0];?>&nbsp;</td>
										<td  title="<?php echo dispTextdb($Matchcand["jobsr"][$i][1]);?>"><? echo strlen($Matchcand["jobsr"][$i][1])>20?dispTextdb(substr($Matchcand["jobsr"][$i][1],0,20)."..."):dispTextdb($Matchcand["jobsr"][$i][1])?>&nbsp;</td>
										<td title="<?php echo dispTextdb($Matchcand["jobsr"][$i][2]);?>"><? echo strlen($Matchcand["jobsr"][$i][2])>15?dispTextdb(substr($Matchcand["jobsr"][$i][2],0,15)."..."):dispTextdb($Matchcand["jobsr"][$i][2])?>&nbsp;</td>
										<td title="<?php echo dispTextdb($Matchcand["jobsr"][$i][3]);?>"><? echo strlen($Matchcand["jobsr"][$i][3])>20?dispTextdb(substr($Matchcand["jobsr"][$i][3],0,20)."..."):dispTextdb($Matchcand["jobsr"][$i][3])?>&nbsp;<?php echo $reason_code_tooltip_data;?></td>
										<?php if(SHIFT_SCHEDULING_ENABLED == 'Y') { ?>
										<td title="<?php echo dispTextdb($Matchcand["jobsr"][$i][8]);?>"><? echo strlen($Matchcand["jobsr"][$i][8])>20?dispTextdb(substr($Matchcand["jobsr"][$i][8],0,20)."..."):dispTextdb($Matchcand["jobsr"][$i][8])?>&nbsp;</td>
										<?php } ?>
						  </tr>
                                <? 
								   }
							}
                            else
                            {	?>
                               <tr class="panel-table-content" valign="middle" align="center"><td colspan=8>no data found</td></tr>
                            <? }?>
                            </tbody>
                        </table>
                   	<?php $DispText=""; 
					}
 if($rtype=="Applied"){
?>
<!-- Panel for to display the Candidates Applied for Job -->
				<table class="panel-table-scroll" width="100%">
					<tr class="panel-table-header">
						<th>Applied</th>
						<th>Job title</th>
						<th>Source Type</th>
						<th>Note</th>
						<?php if(OUTLOOK_PLUG_IN!="Y" || OUTLOOK_TASK_MANAGER!="Y") { ?>
								<th>To Do</th>
						<?php } ?>							
						<th>Submit</th>
					</tr>
					<?php
						$app_job_qry = "SELECT  ".tzRetQueryStringDTime("candidate_appliedjobs.applied_date","DateTime","/").", posdesc.posid, posdesc.postitle, posdesc.refcode,candidate_appliedjobs.status,posdesc.status,IF(posdesc.accessto = 'all', 'Public', IF(FIND_IN_SET('".$username."',posdesc.accessto)>0 , 'Share','NONE')), posdesc.sourcetype  FROM candidate_appliedjobs,posdesc,candidate_general WHERE candidate_appliedjobs.req_id = posdesc.posid AND posdesc.deptid !='0' AND posdesc.deptid IN (".$deptAccesSno.") AND candidate_appliedjobs.candidate_id='".$cand_sno."' AND SUBSTRING(candidate_general.username,5)=candidate_appliedjobs.candidate_id ORDER BY candidate_appliedjobs.applied_date DESC";
						
						$source = "select sno,name from manage where type='josourcetype'"; // Fetched the source 
						$res_source = mysql_query($source,$db);
						while($source_fet = mysql_fetch_row($res_source))
						{
							$source_arr[$source_fet[0]] = $source_fet[1]; // Keeping all those into an array
						}
						
						$app_job_res = mysql_query($app_job_qry,$db);
						$count=mysql_num_rows($app_job_res);
						if($count > 0)
						{
							while($app_job_fet = mysql_fetch_row($app_job_res))
							{	
								if($app_job_fet[3]!='')
							   		$ref_code=" (".$app_job_fet[3].")";						  
							  	else
							   	    $ref_code="";
								
								if($Role_Submission=='YES'){
									if($app_job_fet[4]=="applied")//Checking candidates whether applied or not
									{	
										if($app_job_fet[5]!= "backup"  && $app_job_fet[5]!= "deleted" && $app_job_fet[6]!= 'NONE')
										{
											$submitted ="<a href=javascript:doSubmit_matcand($cand_sno,$app_job_fet[1],$candrn) class=crmsummary-sublnk><img src='/BSOS/images/crm/sm-icon-submit.gif' width='15' height='15' alt='' border='0' align='center'></a>&nbsp;";
											//$submitted ="<a href=javascript:doSubmit_matcand('sdfasdfa','adfa','fdsfa') class=crmsummary-sublnk><img src='/BSOS/images/crm/sm-icon-submit.gif' width='15' height='15' alt='' border='0' align='center'></a>&nbsp;";
										}
										else
										{
											$submitted = "";
										}
									}
									else	
									{						
										if($app_job_fet[5]!= "backup")
										{	
											if($app_job_fet[6]== "NONE")	
												$submitted='';											
											else
												$submitted ="[&nbsp;<a href=javascript:submittedinfo($app_job_fet[1],'$conid') class=crmsummary-sublnk>Info</a>&nbsp;]";
										}
									}
								}else{
									if($app_job_fet[4]=="applied")//Checking candidates whether applied or not
									{	
										if($app_job_fet[5]!= "backup"  && $app_job_fet[5]!= "deleted" && $app_job_fet[6]!= 'NONE')
										{
											$submitted ="<a href=javascript:submission_popup($app_job_fet[1],'Appcand') class=crmsummary-sublnk><img src='/BSOS/images/crm/sm-icon-submit.gif' width='15' height='15' alt='' border='0' align='center'></a>&nbsp;";
										}
										else
										{
											$submitted = "";
										}
									}
									else	
									{						
										if($app_job_fet[5]!= "backup")
										{	
											if($app_job_fet[6]== "NONE")	
												$submitted='';											
											else
												$submitted ="[&nbsp;<a href=javascript:submittedinfo($app_job_fet[1],'$conid') class=crmsummary-sublnk>Info</a>&nbsp;]";
										}
									}
								}
								if($app_job_fet[5]!= "backup"  && $app_job_fet[5]!= "deleted" && $app_job_fet[6]!='NONE')
								{									
					?>
				
					<tr class="panel-table-content" align="left" valign="middle" style="cursor: hand;">

						<td title="<?php echo dispTextdb($app_job_fet[0]); ?>" onClick='javascript:openjoborders(<?php echo $app_job_fet[1]; ?>,"Appcand","<?=$module;?>");'><?php echo strlen($app_job_fet[0])>20?dispTextdb(substr($app_job_fet[0],0,20)."..."):dispTextdb($app_job_fet[0]); ?>&nbsp;</td>
										
						<td title="<?php echo dispTextdb($app_job_fet[2]); ?>"  onClick='javascript:openjoborders(<?php echo $app_job_fet[1]; ?>,"Appcand","<?=$module;?>");'><?php echo strlen($app_job_fet[2])>20?dispTextdb(substr($app_job_fet[2],0,20)."..."):dispTextdb($app_job_fet[2]); ?>&nbsp;</td>
										
						<td title="<?php echo $source_arr[$app_job_fet[7]];  ?>" onClick='javascript:openjoborders(<?php echo $app_job_fet[1]; ?>,"Appcand","<?=$module;?>");'><?php echo $source_arr[$app_job_fet[7]];  ?>&nbsp;</td>
						
						<td align="center" onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/notespopup.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $app_job_fet[1]; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $app_job_fet[6]; ?>&postatus=<?php echo $app_job_fet[5]; ?>','CandidateNotes','430','165')"><?php echo "<i class='fa fa-file-text' title='Notes' alt='Notes'></i>" ?>&nbsp;</td>
						<?php if(OUTLOOK_PLUG_IN!="Y" || OUTLOOK_TASK_MANAGER!="Y") { ?>						
						<td align="center"  onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/todoPopupforcandidates.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $app_job_fet[1]; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $app_job_fet[6]; ?>&postatus=<?php echo $app_job_fet[5]; ?>','ToDoReminders','425','230')"><?php echo "<i title='To Do' align='center' border='0' alt='' class='fa fa-thumb-tack'></i>" ?>&nbsp;</td> 
						<?php } ?>
						
						<td align="center"><?php echo "$submitted" ?>&nbsp;</td> 
						<!--td align="center" onClick="javascript:doSubmit_matcand(<?php echo $cand_sno;?>,<?php echo $app_job_fet[1]?>,<?php echo $candrn; ?>)"><?php echo "$submitted" ?>&nbsp;</td-->
					</tr>
						<?php  }
							else if($app_job_fet[5] == 'deleted' || $app_job_fet[5] == 'backup' || $app_job_fet[6]=='NONE')
								{ ?>
										<tr class="panel-table-content" align="left" valign="middle" style="cursor: hand;">
				
										<td title="<?php echo dispTextdb($app_job_fet[0]); ?>" onClick="<?php if($app_job_fet[5] == 'deleted'){ echo "javascript:openarchjoborders(".$app_job_fet[1].",'');"; } ?>"><?php echo strlen($app_job_fet[0])>20?dispTextdb(substr($app_job_fet[0],0,20)."..."):dispTextdb($app_job_fet[0]); ?>&nbsp;</td>
														
										<td title="<?php echo dispTextdb($app_job_fet[2]); ?>"  onClick="<?php if($app_job_fet[5] == 'deleted'){ echo "javascript:openarchjoborders(".$app_job_fet[1].",'');";  } ?>"><?php echo strlen($app_job_fet[2])>20?dispTextdb(substr($app_job_fet[2],0,20)."..."):dispTextdb($app_job_fet[2]); ?>&nbsp;</td>
														
										<td title="<?php echo $source_arr[$app_job_fet[7]];  ?>" onClick="<?php if($app_job_fet[5] == 'deleted'){ echo "javascript:openarchjoborders(".$app_job_fet[1].",'');";  } ?>" ><?php echo $source_arr[$app_job_fet[7]];  ?>&nbsp;</td>
										
										<td align="center" onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/notespopup.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $app_job_fet[1]; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $app_job_fet[6]; ?>&postatus=<?php echo $app_job_fet[5]; ?>','CandidateNotes','430','165')"><?php echo "<i class='fa fa-file-text' title='Notes' alt='Notes'></i>" ?>&nbsp;</td>
						<?php if(OUTLOOK_PLUG_IN!="Y" || OUTLOOK_TASK_MANAGER!="Y") { ?>				
						<td align="center"  onClick="javascript:open_ToDo('/BSOS/Marketing/Candidates/todoPopupforcandidates.php?Module=Candidates&addr=<?php echo $cand_sno;?>&posid=<?php echo $app_job_fet[1]; ?>&candrn=<?php echo $candrn; ?>&user=<?php echo $app_job_fet[6]; ?>&postatus=<?php echo $app_job_fet[5]; ?>','ToDoReminders','425','230')"><?php echo "<i title='To Do' align='center' border='0' alt='' class='fa fa-thumb-tack'></i>" ?>&nbsp;</td> 
						<?php } ?>
										
										<td align="center"><?php echo "$submitted"; ?>&nbsp;</td>
										<!--td align="center" onClick="javascript:doSubmit_matcand(<?php echo $cand_sno;?>,<?php echo $app_job_fet[1]; ?>,<?php echo $candrn; ?>)"><?php echo "$submitted" ?>&nbsp;</td-->	
									</tr>
						<?php	} 
						 	} 
						} 
						else
                        { ?>
                     <tr class="panel-table-content" valign="middle" align="center">
					 	<td colspan=8>no data found</td>
					</tr>
                  <?php }?>
			</table>
<!-----------------------End of Panel Candidates Applied for Job ------------------------------>


<?php $DispText=""; }					

if($rtype=="displayNotes")
{
//	$candadress=substr($_SESSION[conid.$contactid],4);
	 $Nquery= "SELECT notes.notes, notes.cuser, ".tzRetQueryStringDTime("notes.cdate","DateTime","/")." , notes_subtype, manage.name, users.name FROM users, notes LEFT JOIN manage FORCE INDEX ( type_sno ) ON ( manage.sno = notes_subtype
AND manage.type = 'notes' ) WHERE notes.type = 'cand' AND notes.contactid = '".$contactid."' AND notes.cuser = users.username ORDER BY notes.cdate DESC";
				$Ex_NT_query=mysql_query($Nquery,$db);
				
				while($NRow=mysql_fetch_row($Ex_NT_query)){
					$subType='';
					if($NRow[4]!=''){
						$subType="&nbsp;|&nbsp;".$NRow[4];
					}
				 	$NotesText.="<b>".strtolower($NRow[2]).$subType."&nbsp;-"."&nbsp;".$NRow[5]." </b><br/>".$NRow[0]."<br><br>";
				
				}
	echo $NotesText;
}

echo $DispText;
echo "^^FooterSplit^^";	
echo getOwnerName($Mod_User)."|".$modDate."|".getOwnerName($owner);
echo "^^FooterSplit^^";
echo $deleteCandResume;
?>