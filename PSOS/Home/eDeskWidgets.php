<?php

/**
 * This class will display the widgets in the eDesk page.
 */

require_once('global.inc'); 
require_once('dispfunc.php');
//  Added this condition in order to resolve the fatal error class EmpMneu redeclaration error when accessing this page via MyProfile->Job opportunies
if($viamodule!='myprofile'){
    require_once('EmpMenu.inc');  
}
require_once('calendarfuns.php');
require_once('reminderfuns.php');
require_once('saveactivities.php');
require_once(BSOS_INCL . 'OutLookEnbLinks.php');

class eDeskWidgets {

	public function getCEAWidget($username, $widget_id)
	{
		GLOBAL $db;

		$message_text = "<i>You need to subscribe for Call-Em-All Broadcasting Service.</i>";

		if(DEFAULT_CEA=="Y" && DEFAULT_CEA_USER!="")
		{
			require_once("CallEmAll.php");
			$ceaObj = new CallEmAll();

			$c_consumer = new OAuthConsumer($ceaObj->ckey, $ceaObj->csecret, NULL);
			$c_token = new OAuthToken(DEFAULT_CEA_USER, NULL);

			$ceaObj->endpoint=str_replace("[[ENDPOINT]]","broadcasts?sortby=-StartDate",$ceaObj->endpoint);

			$reqObj=OAuthRequest::from_consumer_and_token($c_consumer, $c_token, "GET", $ceaObj->endpoint);
			$reqObj->sign_request($sig_method, $c_consumer, $c_token);
			$ceaObj->client->setOAUTHAuthorization($reqObj->to_header());

			$data=array("sortby" => "-StartDate");
			$broadCasts = $ceaObj->getBroadCasts($data);

			$message_text="";

			for($i=0;$i<count($broadCasts['Items']);$i++)
			{
				if(strtolower($broadCasts['Items'][$i]['BroadcastStatus'])=="complete"){
					$message_text.="<i><a href=javascript:ceaBCInfo('".$broadCasts['Items'][$i]['Uri']."')>".$broadCasts['Items'][$i]['BroadcastName']." (".$broadCasts['Items'][$i]['BroadcastStatus']." -- ".date('m/d/Y h:i A',strtotime($broadCasts['Items'][$i]['StartDate'])).")</a></i><br>";
					
				}
				else{
					$message_text.="<i>".$broadCasts['Items'][$i]['BroadcastName']." (".$broadCasts['Items'][$i]['BroadcastStatus']." -- ".date('m/d/Y h:i A',strtotime($broadCasts['Items'][$i]['StartDate'])).")</i><br>";
				}

				if($i==4)
					$i=count($broadCasts['Items'])+1;
			}

			if($broadCasts['Size']>3)
			{
				$location="Call-Em-All";
				$viewall_link="javascript:ceaBCAll();";
			}

			if(count($broadCasts['Items'])==0)
				$message_text="<i>You currently have no Call-Em-All Broadcasts.</i>";
		}

		//$image = "users.gif";
		$title = "<span class='widgtitlecolorBlue'><img src='../images/CallEmAll.jpg' title='Call-Em-All' alt='' style='vertical-align: bottom;' /> Call-Em-All Broadcasts</span>";
		$toolbar = "<a href=javascript:refreshWidgets('callemallbroadcasts',".$widget_id.")><i class='fa fa-repeat fa-lg'></i></a>";
		$tooltip = "Call-Em-All Broadcasts";

		$cea_widget=$this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);
		$cea_widget.=$message_text;
		$cea_widget.=$this->getWidgetFooter($location,$viewall_link);

		return $cea_widget;
	}

	public function getTextUsWidget($username, $widget_id)
	{
		GLOBAL $db;

		$message_text = "<i>You need to subscribe for TextUs Broadcasting Service.</i>";
		if(TEXT_US_ENABLED == 'Y' && TEXTUS_USER_ACCESS == 'Y')
		{
			$broadcastsQry = "SELECT tb.sno,tb.group_name,tb.status,tb.deliver_at,tb.broadcast_id FROM textus_broadcasts tb WHERE tb.cuser='".$username."' ORDER BY deliver_at DESC LIMIT 0,5";
			$broadcastsRes = mysql_query($broadcastsQry,$db);
			$broadcastsCount = mysql_num_rows($broadcastsRes);

			$message_text="";

			require_once("TextUs.php");
			// Get textus user account details from posted data or table "textus_users"
			$selTextUsDetQry = "SELECT email,apikey FROM textus_users WHERE status='A' AND username='".$username."'";
			$selTextUsDetRes = mysql_query($selTextUsDetQry, $db);
			$selTextUsDetRow = mysql_fetch_row($selTextUsDetRes);

			$textUsAccountEmail 	= $selTextUsDetRow[0];  
			$textUsApiKey 		= $selTextUsDetRow[1]; 

			$textUsObj = new TextUs($textUsAccountEmail, $textUsApiKey);

			while($broadcastsRow=mysql_fetch_assoc($broadcastsRes))
			{
				$apiBdStatus = $broadcastsRow['status'];
				if($textUsObj->getAuthentication()=="Success")
				{
					$apiBdDetails = $textUsObj->getBroadcastDetails($broadcastsRow['broadcast_id']);
					$apiBdStatus  = $apiBdDetails['status'];
					if($apiBdStatus==NULL || $apiBdStatus=='')
					{
						$apiBdStatus  = 'Scheduled';
					}

					$updateStatusQry = "UPDATE textus_broadcasts SET status='".$apiBdStatus."' WHERE sno=".$broadcastsRow['sno'];
					mysql_query($updateStatusQry,$db);
				}

				$message_text.="<i>".$broadcastsRow['group_name']." (<a style='color:#3eb8f0;' href=javascript:broadcastDetails('".$broadcastsRow['sno']."')>".ucfirst($apiBdStatus)."</a> - ".date('m/d/Y h:i A',strtotime($broadcastsRow['deliver_at'])).")</i><br>";
			}

			if($broadcastsCount==0)
			{
				$message_text="<i>You currently have no TextUs Broadcasts.</i>";
			}
			else
			{
				$location = "TextUs";
				$viewall_link = "javascript:viewAllBroadcasts();";
			}		
		}

		$title = "<span class='widgtitlecolorBlue'><i class='fa fa-commenting fa-lg'></i> TextUs Broadcasts</span>";
		$toolbar = "<a href=javascript:refreshWidgets('textusbroadcasts',".$widget_id.")><i class='fa fa-repeat fa-lg'></i></a>";
		$tooltip = "TextUS Broadcasts";

		

		$image = '';
		$textUs_widget=$this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);
		$textUs_widget.=$message_text;
		$textUs_widget.=$this->getWidgetFooter($location,$viewall_link);

		return $textUs_widget;
	}

	/* 
	* This function displays the VIDEO INTERVIEWS (SPARK HIRE) WIDGET in the eDesk
	*
	* param	string	$username
	* param	integer	$widget_id
	* return	string	$onboarding_widget
	*/

	public function getVideoInterviewsWidget($username, $widget_id)
	{
		GLOBAL $db;

		$message_text = "<i>You need to subscribe for Video Interviewing.</i>";

		if(DEFAULT_VINTERVIEW=="Y" && DEFAULT_VINTERVIEW_USER!="")
		{
			require_once("class.HttpClient.php");
			require_once("json_functions.inc");
			require_once("SparkHire.php");
			require_once("SparkHireDB.php");

			$jobcount=0;
			$message_text="";

			$sparkhire=new SparkHire(DEFAULT_VINTERVIEW_USER);
			//$shUser=$sparkhire->getMe();
			$shDB=new SparkHireDB($sparkhire);
			$jobinfo=$sparkhire->getJobs();
			for($i=0;$i<count($jobinfo);$i++)
			{
				if($jobinfo[$i]['status']!="inactive")
				{
					$jobcount++;

					$jque="SELECT posid FROM posdesc WHERE sh_uuid='".$jobinfo[$i]['uuid']."'";
					$jres=mysql_query($jque,$db);
					$jrow=mysql_fetch_row($jres);

					$cque="SELECT COUNT(1) FROM viInterviews WHERE posid='".$jrow[0]."' AND status!='deleted'";
					$cres=mysql_query($cque,$db);
					$crow=mysql_fetch_row($cres);

					$message_text.="<i><a href=javascript:manVInterview('".$jrow[0]."')>".$jobinfo[$i]['title']." (JO ID : ".$jrow[0]." -- ".$crow[0]." Interviews)</a></i><br>";
				}
			}

			if($jobcount==0)
				$message_text="<i>You currently have no video interviews setup.</i>";
		}

		//$image = "users.gif";
		$title = "<span class='widgtitlecolorBlue'><i class='fa fa-video-camera fa-lg'></i> Video Interviews</span>";
		$toolbar = "<a href=javascript:refreshWidgets('videointerviews',".$widget_id.")><i class='fa fa-repeat fa-lg'></i></a>";
		$tooltip = "Video Interviews";

		$vinterview_widget=$this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);
		$vinterview_widget.=$message_text;
		$vinterview_widget.=$this->getWidgetFooter();

		return $vinterview_widget;
	}

	/* 
	* This function displays the ONBORADING WIDGET in the eDesk
	*
	* param	string	$username
	* param	integer	$widget_id
	* return	string	$onboarding_widget
	*/

	public function getOnBoardingWidget($username, $widget_id)
	{
		GLOBAL $db,$ONBOARDING_ENABLED;

		if(ONBOARDING_ENABLED=="Y")
		{
			$oque="SELECT COUNT(1) FROM obUsers WHERE username='$username' AND status='A'";
			$ores=mysql_query($oque,$db);
			$orow=mysql_fetch_row($ores);
			$OB_User = $orow[0];
		}

		$message_text = "<i>You need to subscribe for Paperless OnBoarding.</i>";
		$onboarding_widget = "";

		if($OB_User>0)
		{
			require_once("array2xml.inc");
			require_once("HrWorkCycles.php");

			$cque="SELECT hrwork_empcode FROM company_info WHERE hrwork_empcode!=''";
			$cres=mysql_query($cque,$db);
			$crow=mysql_fetch_row($cres);

			$uque="SELECT userid, passwd FROM obUsers WHERE username='$username'";
			$ures=mysql_query($uque,$db);
			$urow=mysql_fetch_row($ures);

			$HWEMPCODE = $crow[0];
			$HWUSERNAME = $urow[0];
			$HWPASSWD = $urow[1];

			$hrwc = new HRWorkCycles;
			$hrwc->setHRWURLS();

			$hrwc->api_empcode = $HWEMPCODE;
			$hrwc->api_username = $HWUSERNAME;
			$hrwc->api_password = $HWPASSWD;

			$resp = $hrwc->dbResponse();
			if($hrwc->dbParse($resp))
			{
				$message_text = "";
				$titles = $hrwc->dbTitles($resp);
				$links = $hrwc->dbLinks($resp);

				foreach($titles as $key => $val)
				{
					if($val>0)
					{
						$key_text = ucwords(str_replace("_"," ",$key));
						$message_text.= "<a href=\"javascript:doOnBoardStat('$key','".$links[$key]."')\">$key_text : $val</a><br/>";
					}
				}

				if($message_text=="")
					$message_text="<i>You currently have no pending items.</i>";
			}
			else
			{
				$message_text="<i>".$hrwc->resp_status."</i>";
			}
		}

		
		$title = "Paperless On-Boarding Statuses";
		$toolbar = "<a href=javascript:refreshWidgets('paperlessonboardingstatuses',".$widget_id.")><i class='fa fa-repeat fa-lg'></i></a>";
		$tooltip = "Paperless On-Boarding Statuses";

		$onboarding_widget=$this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);
		$onboarding_widget.=$message_text;
		$onboarding_widget.=$this->getWidgetFooter();

		return $onboarding_widget;
	}

	/*
	 * This function displays the EDESK CALENDAR WIDGET in the eDesk
	 *
	 * param	string	$homeday
	 * param	integer	$widget_id
	 */
	public function getEDeskCalendarWidget($homeday, $widget_id) {

		GLOBAL $homeday;

		echo "
			<div class='box-wrapper portlet sortable' id='$widget_id'>				
				<div id='twomonthcalendar' class='portlet-content' style='margin:0;padding:0;'>";
			echo '
				</div>
			</div>';
	}

	/*
	 * This function displays the CALENDAR WIDGET in the eDesk
	 *
	 * param	string	$homeday
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$calendar_widget
	 */
	public function getCalendarWidget($homeday, $username, $widget_id) {

		GLOBAL $collaborationpref;

		$calendar_widget	= '';

		//$image	= 'scheduler_edesk.gif';
		$tooltip	= 'Scheduler';
		if(OUTLOOK_PLUG_IN == 'N'){
			if (!chkUserPref($collaborationpref, '5') && OUTLOOK_PLUG_IN == 'N') {

				$title		= 'Calendar';
				$toolbar	= '<a href=javascript:refreshWidgets("calendar",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';

			} else {

				$enbOutLookPluginLinks	= new EnbOutLookPluginLinks(OUTLOOK_PLUG_IN, 'HOME');

				$popup_link		= "javascript:winopen3('pathlocator.php?ptype=add&mname=Collaboration->Scheduler&date_val=1&thisday=$homeday', 'yes')";

				$calendar_link	= '/BSOS/Collaboration/Scheduler/calendar.php';

				$title			= '<a href="' . $enbOutLookPluginLinks->EnableAjaxLink('Appnew', '', $calendar_link) . '" class="widgtitlecolorBlue"><i class="fa fa-calendar fa-lg"></i> Calendar</a>
									<a href="' . $enbOutLookPluginLinks->EnableAjaxLink('Appnew', '', $popup_link) . '" class="widgtitlecolorBlue">( New )</a>';
					
				$toolbar		= '<a href=javascript:refreshWidgets("calendar",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';	
			}
		

			$calendar_widget	= $this->getEventWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

			$calendar_widget	.= $this->getCalendarEvents($homeday, $username);

			$calendar_widget	.= $this->getEventWidgetFooter();
		}
			return $calendar_widget;

	}

	/*
	 * This function fetches the CALENDAR EVENTS
	 *
	 * param	string	$homeday
	 * param	string	$username
	 * return	string	$table_row
	 */
	public function getCalendarEvents($homeday, $username) {

		GLOBAL $collaborationpref, $db;

		$utzos	= getUserSTZOffset();

		if ($utzos == "") {

			$utzos	= 0;
		}
		// Code for Admin Preferences for Admin_Management
		$sql = "SELECT admin from sysuser where username=$username";
		$rs = mysql_query($sql,$db);
		$row = mysql_fetch_assoc($rs);
		$admin_pref = $row['admin'];
		$chkdata = strpos($admin_pref, '+14+');//$admin_pref!='NO'  $chkdata !== false
		
		$events	= array();

		$qsdatetime	= mktime(0, 0, 0, date('m', $homeday), date('d', $homeday), date('Y', $homeday)) - $utzos;

		$qedatetime	= mktime(23, 59, 59, date('m', $homeday), date('d', $homeday), date('Y', $homeday)) - $utzos;

		$table_row	= '';

		$querystring	= "SELECT
								appointments.sno, appointments.event event,
								DATE_FORMAT(FROM_UNIXTIME(IF(appointments.recurrence='none',(appointments.sdatetime + '$utzos'),(recurrences.otime + '$utzos'))),'%l:%i%p') stime, 
								DATE_FORMAT(FROM_UNIXTIME(IF(appointments.recurrence='none',(appointments.edatetime + '$utzos'),(recurrences.etime + '$utzos'))),'%l:%i%p') etime, 
								appointments.title, appointments.recurrence, 
								IF(appointments.recurrence='none',(appointments.sdatetime + '$utzos'),(recurrences.otime + '$utzos')) sdatetime, 
								appointments.edatetime, appointments.priority, appointments.enddate, appointments.recurrence_type, appointments.recurrence_subtype, 
								appointments.recurrence_day, appointments.recurrence_week, appointments.recurrence_month, appointments.enddate_option, appointments.contactsno, 
								appointments.modulename, IF(FIND_IN_SET('$username', appointments.pending) > 0, 'TRUE', 'FALSE') psts, appointments.username, cpr.activity_status
							FROM
								appointments
								LEFT JOIN recurrences ON recurrences.ano = appointments.sno 
								LEFT JOIN cmngmt_pr cpr ON (appointments.sno = cpr.tysno AND cpr.title= 'Appointment')
							WHERE
								appointments.status='active' AND (appointments.username='$username' OR FIND_IN_SET('$username', appointments.approved) > 0 OR FIND_IN_SET('$username', appointments.tentative) > 0 OR FIND_IN_SET('$username', appointments.pending) > 0)
								AND ((appointments.recurrence='none' AND appointments.sdatetime>=$qsdatetime AND appointments.sdatetime<=$qedatetime) OR (appointments.recurrence='recurrence' AND recurrences.otime>=$qsdatetime AND recurrences.otime <=$qedatetime))  $groupby
							ORDER BY
								sdatetime, appointments.title ASC";

		$queryresult	= mysql_query($querystring, $db);

		while ($row = mysql_fetch_array($queryresult)) {
			$flag	= TRUE;
			if (substr($row['contactsno'], 0, 3) == 'con') {

				$con_query	= 'SELECT astatus, name FROM consultant_list WHERE username="' . $row['contactsno'] . '"';

				$con_result	= mysql_query($con_query, $db);

				$crow	= mysql_fetch_array($con_result);

				if ($crow['astatus']=="RARCH" || $crow['astatus']=="backup" || $crow['astatus']=="ARCH" || $crow['astatus']=="DELE" || $crow['astatus']=="INACT") {

					$flag	= FALSE;
				}

			} elseif (substr($row['contactsno'], 0, 3) == 'sub') {

				$sub_query	= "SELECT status, CONCAT_WS(' ', fname, mname, lname) FROM subconsultant WHERE subconid ='" . $row['contactsno'] ."'";

				$sub_result	= mysql_query($sub_query, $db);

				$crow	= mysql_fetch_array($sub_result);

				if ($crow['status']=="INACTIVE" || $crow['status']=="backup") {

					$flag	= FALSE;
				}

			} elseif (substr($row['contactsno'], 0, 3) == 'emp') {

				$emp_query	= "SELECT lstatus ,name FROM emp_list WHERE sno ='" . substr($row['contactsno'], 3, strlen($row['contactsno'])) . "'";

				$emp_result	= mysql_query($emp_query, $db);

				$crow	= mysql_fetch_array($emp_result);

				if ($crow['lstatus']=="DA") {

					$flag	= FALSE;
				}

			} elseif (substr($row['contactsno'], 0, 3) == 'acc') {

				$sta_query	= "SELECT staffacc_list.status, staffacc_cinfo.cname FROM staffacc_list LEFT JOIN staffacc_cinfo ON staffacc_list.username = staffacc_cinfo.username WHERE staffacc_list.username= '" . $row['contactsno'] . "'";

				$sta_result	= mysql_query($sta_query, $db);

				$crow	= mysql_fetch_array($sta_result);

				if ($crow['status']!="ACTIVE") {

					$flag	= FALSE;
				}

			} elseif (substr($row['contactsno'], 0, 4) == 'oppr') {

				$stc_query	= "SELECT status,concat_ws(' ',fname,mname,lname) from staffoppr_contact where sno='".substr($row['contactsno'],4,strlen($row['contactsno']))."'";

				$stc_result	= mysql_query($stc_query, $db);

				$crow	= mysql_fetch_array($stc_result);

				if ($crow['status']=="INACTIVE" || $crow==0) {

					$flag	= FALSE;
				}

			} elseif (substr($row['contactsno'], 0, 3) == 'com') {

				$stf_query	= "SELECT status, cname FROM staffoppr_cinfo WHERE sno ='" . substr($row['contactsno'], 3, strlen($row['contactsno'])) . "'";

				$stf_result	= mysql_query($stf_query, $db);

				$crow	= mysql_fetch_array($stf_result);

				if ($crow['status']=="INACTIVE") {

					$flag	= FALSE;
				}

			} elseif (substr($row['contactsno'], 0, 4) == 'cand') {

				$can_query	= "SELECT cl.status, CONCAT_WS(' ',cg.fname,cg.mname,cg.lname) FROM candidate_list cl,candidate_general cg WHERE cl.username = cg.username AND cg.username = '" . $row['contactsno'] . "'";

				$can_result	= mysql_query($can_query, $db);

				$crow	= mysql_fetch_array($can_result);

				if ($crow['status']!="ACTIVE") {

					$flag	= FALSE;
				}
			}

			elseif (substr($row['contactsno'], 0, 3) == 'req') {

				$jo_id = preg_replace("/[^0-9]/", '', $row['contactsno']);

				$job_query	= "SELECT pd.status, pd.postitle FROM posdesc pd WHERE pd.posid = '".$jo_id."' ";

				$job_result	= mysql_query($job_query, $db);

				$crow	= mysql_fetch_array($job_result);

				if ($crow['status'] == "deleted" || $crow['status'] == 'backup') {// Archived and Deleted Joborders condition checking

					$flag	= FALSE;
				}
			}
			else{
				$crow = NULL;
			}

			if ($flag) {

				$event_time		= ($row['event'] == 'allday') ? ' All day ' : $row['stime'] . ' - ' . $row['etime'];

				$anchor_class	= ($row['priority'] == "High") ? 'class = "apply-priority-edesk"' : '';

				$activity_name	= ($row['contactsno'] != '' && $crow[1] != '') ? " (" . html_tls_specialchars(stripslashes($crow[1]),ENT_QUOTES) . ")" : '';

				$module_name	= ($row['modulename'] == '') ? 'Collaboration->Scheduler' : $row['modulename'];

				$psts	= (OUTLOOK_PLUG_IN == 'Y' && $row['psts']=='TRUE') ? ' (Pending)' : '';

				$row['title']	= stripslashes($row['title']);

				if ($module_name == 'Collaboration->Scheduler') {

					if($chkdata == false && $row['activity_status'] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
					$table_row .= "<tr>
									
								<td class='edesktasks' colspan='3'>
								<div class='edeskoverBg'><a title=\"" . html_tls_specialchars($row['title'], ENT_QUOTES) . "\" href=\"javascript:winopen3('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=$homeday&gr_calender=$username', 'no')\">
									<table width='100%' cellspacing='0' cellpadding='0' border='0'>
									<tr>
									<td class='calenwidth'>
										<div style='overflow:hidden;white-space:nowrap;' valign='middle'>
											<a  href=\"javascript:winopen3('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=$homeday&gr_calender=$username', 'no')\" title=\"" . html_tls_specialchars($row['title'], ENT_QUOTES) . "\"><font style='font-size:100%;' >" . $event_time . "</font>
											</a>
										</div>
										
									</td>
									
									
									<td align='left'>
										<div style='width:260px;overflow:hidden;white-space:nowrap;' valign='middle'>&nbsp;
											<font style='font-size:100%;'>" . html_tls_specialchars($row['title'], ENT_QUOTES) . $psts . "</font>
										</div>
									</td>
									</tr>
									
									</table>
									
									</a></div>
								</td>
									
								</tr>";
								
					/*$table_row	.= "<tr class=tr1bgcolor><td style='width:120px'><a href=\"javascript:winopen3('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=$homeday&gr_calender=$username', 'no')\" title=\"" . html_tls_specialchars($row['title'], ENT_QUOTES) . "\" " . $anchor_class . ">" . $event_time . "</a></td>";
					
					$table_row	.= "<td><font style='font-size:100%;'>" . html_tls_specialchars($row['title'], ENT_QUOTES) . $psts . "</font></td></tr>";*/
				}

				} elseif ($module_name == "Accounts->customer") {
					if($chkdata == false && $row['activity_status'] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
				$table_row .= "<tr>
									
								<td class='edesktasks' colspan='3'>
								<div class='edeskoverBg'><a href=\"javascript:winopen2('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=$homeday')\" title=\"" . $activity_name ."&nbsp;". html_tls_specialchars($row['title'], ENT_QUOTES) . "\">
									<table width='100%' cellspacing='0' cellpadding='0' border='0'>
									<tr>
									<td class='calenwidth'>
										<div style='overflow:hidden;white-space:nowrap;' valign='middle'>
											<a  href=\"javascript:winopen2('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=$homeday')\" title=\"" . $activity_name ."&nbsp;". html_tls_specialchars($row['title'], ENT_QUOTES) . "\"><font style='font-size:100%;' >" . $event_time . "</font>
											</a>
										</div>
										
									</td>
									
									<td align='left'>
										<div style='width:260px;overflow:hidden;white-space:nowrap;' valign='middle'>&nbsp;
											<font style='font-size:100%;'>" . $activity_name ."&nbsp;". html_tls_specialchars($row['title'], ENT_QUOTES) . "</font>
										</div>
									</td>
									</tr>
									
									</table>
									
									</a></div>
								</td>
									
								</tr>";
								
					/*$table_row	.= "<tr class=tr1bgcolor><td style='width:120px'><a href=\"javascript:winopen2('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=$homeday')\" title=\"" . html_tls_specialchars($row['title'], ENT_QUOTES) . "\" " . $anchor_class . ">" . $event_time . "</a></td>";
					
					$table_row	.= "<td><font style='font-size:100%;'>" . html_tls_specialchars($row['title'], ENT_QUOTES) . $activity_name . "</font></td></tr>";*/
					}

				} else {
					if($chkdata == false && $row['activity_status'] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
					$table_row .= "<tr>
									
								<td class='edesktasks' colspan='3'>
								<div class='edeskoverBg'><a href=\"javascript:winopen('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=".$homeday."&module=CRM')\" title=\"" . $activity_name ."&nbsp;". html_tls_specialchars($row['title'], ENT_QUOTES). "\">
									<table width='100%' cellspacing='0' cellpadding='0' border='0'>
									<tr>
									<td class='calenwidth'>
										<div style='overflow:hidden;white-space:nowrap;' valign='middle'>
											<a  href=\"javascript:winopen('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=".$homeday."'&module=CRM')\" title=\"" . $activity_name ."&nbsp;". html_tls_specialchars($row['title'], ENT_QUOTES). "\"><font style='font-size:100%;' >" . $event_time . "</font>
											</a>
										</div>
										
									</td>
									
									<td align='left'>
										<div style='width:260px;overflow:hidden;white-space:nowrap;' valign='middle'>&nbsp;
											<font style='font-size:100%;'>" . $activity_name ."&nbsp;". html_tls_specialchars($row['title'], ENT_QUOTES) . "</font>
										</div>
									</td>
									</tr>
									
									</table>
									
									</a></div>
								</td>
									
								</tr>";
								
					/*$table_row	.= "<tr class=tr1bgcolor><td style='width:120px'><a href=\"javascript:winopen('pathlocator.php?ptype=Appointment&mname=$module_name&addr=$username&recsno=" . $row['sno'] . "&con_id=" . $row['contactsno'] . "&thisday=".$homeday."')\" title=\"" . html_tls_specialchars($row['title'], ENT_QUOTES). "\" " . $anchor_class . ">" . $event_time. "</a></td>";
					
					$table_row	.= "<td><font style='font-size:100%;'>" . html_tls_specialchars($row['title'], ENT_QUOTES) . $activity_name . "</font></td></tr>";*/

				}
			}

			} // end-of-if

		} // end-of-while

		if (empty($table_row)) {

			$table_row	.= '<tr><td><i>You have no appointments scheduled today.</i>';

			if (chkUserPref($collaborationpref, '5')) {

				$table_row	.= " <i>Click on the 'new' button to schedule a new appointment.</i>";
			}

			$table_row	.= '</td></tr>';

		}

		return $table_row;
	}

	/*
	 * This function displays the INVITATIONS WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$invitations_widget
	 */
	public function getInvitationsWidget($homeday, $username, $widget_id) {
		//$image		= 'invitation.gif';
		//$title		= 'Invitations';
                $title		= '<span class="widgtitlecolorBlue"><i class="fa fa-credit-card fa-lg"></i> Invitations</span>';
		$tooltip	= 'Invitations';
		$toolbar	= '<a href=javascript:refreshWidgets("invitations",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';

		$invitations_widget	= $this->getEventWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$invitations_widget	.= $this->getInvitationEvents($homeday, $username);

		$invitations_widget	.= $this->getEventWidgetFooter();

		return $invitations_widget;
	}

	/*
	 * This function fetches the INVITATIONS EVENTS
	 *
	 * param	string	$username
	 * return	string	$table_row
	 */
	public function getInvitationEvents($homeday, $username) {

		GLOBAL $db;

		$mail_count	= 0;
		$table_row	= '';

		$querystring	= "SELECT
								mail_headers.mailid, mail_calendar.cid, mail_headers.fromadd, mail_headers.subject
							FROM
								mail_headers
								LEFT JOIN mail_calendar ON mail_headers.mailid=mail_calendar.mailid
							WHERE
								mail_headers.username='$username' AND mail_headers.status='ACTIVE' AND mail_calendar.status='U'";

		$queryresult	= mysql_query($querystring, $db);

		while ($qrow = mysql_fetch_row($queryresult)) {

			$leftencode	= explode('<', $qrow[2]);

			if (trim($leftencode[0]) == '') {

				$rightencode	= explode('>', $leftencode[1]);
				$fname			= $rightencode[0];

			} else {

				$fname	= $leftencode[0];
			}

			$qrow[2]	= $fname;

			$table_row	.= "
				<tr class=tr1bgcolor>
					<td>
						<a href=javascript:viewSchedule(". $homeday . "," . $qrow[0] . ",'" . $qrow[1] . "')>" . $qrow[3] . " ( ". html_tls_entities($qrow[2],ENT_QUOTES). ")</a>
					</td>
				</tr>";

			$mail_count ++;

		} // end-of-while

		if ($mail_count == 0) {

			$table_row	= '<i>You currently have no invitations.</i>';
		}

		return $table_row;
	}

	/*
	 * This function displays the TASKS WIDGET in the eDesk
	 *
	 * param	string	$homeday
	 * param	string	$username
	 * param	date	$start_date
	 * param	string	$where_clause
	 * param	integer	$widget_id
	 * return	string	$tasks_widget
	 */
	public function getTasksWidget($homeday, $username, $start_date, $where_clause, $widget_id) {

		GLOBAL $collaborationpref;

		$tasks_widget	= '';

		//$image	= 'tasks.gif';
		$tooltip	= 'Tasks';

		if (!chkUserPref($collaborationpref, '6') && OUTLOOK_PLUG_IN == 'N') {

			$title		= 'Tasks';
			$toolbar	= '<a href=javascript:refreshWidgets("tasks",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';

		} else {

			$enbOutLookPluginLinks	= new EnbOutLookPluginLinks(OUTLOOK_PLUG_IN, 'HOME');

			$popup_link	= "javascript:winopen1('/BSOS/Collaboration/Tasks/taskadd.php?edesk_id=1&homeday=$homeday')";

			$task_link	= '/BSOS/Collaboration/Tasks/gettask.php';

			$title		= '<a href="' . $enbOutLookPluginLinks->EnableAjaxLink('Tasknew', '', $task_link) . '" class="widgtitlecolorBlue"><i class="fa fa-tasks fa-lg"></i> Tasks</a>
							<a href="' . $enbOutLookPluginLinks->EnableAjaxLink('Tasknew', '', $popup_link) . '">( New )</a>';

			$toolbar	= '<a href=javascript:refreshWidgets("tasks",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';			
		}

		$tasks_widget	= $this->getEventWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$tasks_widget	.= "<i>To create a task, click the 'New' button.</i>";

		$tasks_widget	.= $this->getTasksEvents($homeday, $username, $start_date, $where_clause);

		$tasks_widget	.= $this->getEventWidgetFooter();

		return $tasks_widget;
	}

	/*
	 * This function fetches the TASKS EVENTS
	 *
	 * param	string	$homeday
	 * param	string	$username
	 * param	string	$start_date
	 * param	string	$where_clause
	 * return	string	$table_row
	 */
	public function getTasksEvents($homeday, $username, $start_date, $where_clause) {

		GLOBAL $db;

		$task_count	= 0;
		$table_row	= '';

		// Code for Admin Preferences for Admin_Management
		$sql = "SELECT admin from sysuser where username=$username";
		$rs = mysql_query($sql,$db);
		$row = mysql_fetch_assoc($rs);
		$admin_pref = $row['admin'];
		$chkdata = strpos($admin_pref, '+14+');//$admin_pref!='NO'  $chkdata !== false
		
		$querystring	= 'SELECT
								tasklist.sno, tasklist.title,' . tzRetQueryStringSelBoxDate("tasklist.startdate", "Date", "/") . ",
								tasklist.contactsno, tasklist.modulename, tasklist.cuser, tasklist.tasktype,tasklist.ctime,cpr.activity_status
							FROM
								tasklist
								LEFT JOIN cmngmt_pr cpr ON (tasklist.sno = cpr.tysno AND cpr.title= 'Task')
							WHERE
								((tasklist.cuser = '$username' AND tasklist.sendto = '') OR FIND_IN_SET('$username', tasklist.sendto)) AND
								((tasklist.startdate <= '$start_date') OR (tasklist.startdate = '$start_date')) AND
								tasklist.taskstatus != 'Completed' AND tasklist.status NOT IN ('remove', 'backup', 'ARCHIVE')
								$where_clause $groupby
							ORDER BY
								tasklist.startdate DESC, tasklist.ctime ASC, tasklist.title ASC";
	 
		$queryresult	= mysql_query($querystring, $db);
		
		$table_row .= '<tr>
		<td class="submissiontext" valign="top" align="left" style="width:80px">
			<div style="width:70px;overflow:hidden;white-space:nowrap;">Date</div>
		</td>
		<td nowrap="" class="submissiontext" valign="top" align="left">
			<div style="width:70px;overflow:hidden;white-space:nowrap;">Task</div>
		</td>		
	</tr>';

		while ($row = mysql_fetch_array($queryresult)) {

			$task_count	++;

			$candidate_name	= '';

			if (substr($row[3], 0, 3) == 'con') {

				$con_query	= "SELECT name FROM consultant_list WHERE username='" . $row[3] . "'";

				$con_result	= mysql_query($con_query, $db);

				$con_row	= mysql_fetch_row($con_result);

				$candidate_name	= $con_row[0];

			} elseif (substr($row[3], 0, 3) == 'sub') {

				$sub_query	= "SELECT CONCAT_WS(' ', fname, mname, lname) FROM subconsultant WHERE subconid='" . $row[3] . "'";

				$sub_result	= mysql_query($sub_query, $db);

				$sub_row	= mysql_fetch_row($sub_result);

				$candidate_name	= $sub_row[0];

			} elseif (substr($row[3], 0, 3) == 'emp') {

				$emp_query	= "SELECT name FROM emp_list WHERE sno='" . substr($row[3], 3, strlen($row[3])) . "'";

				$emp_result	= mysql_query($emp_query, $db);

				$emp_row	= mysql_fetch_row($emp_result);

				$candidate_name	= $emp_row[0];

			} elseif (substr($row[3], 0, 3) == 'acc') {

				$sta_query	= "SELECT
									staffacc_cinfo.cname
								FROM
									staffacc_list
									LEFT JOIN staffacc_cinfo ON staffacc_list.username = staffacc_cinfo.username
								WHERE
									staffacc_list.username='" . $row[3] . "'";

				$sta_result	= mysql_query($sta_query, $db);

				$sta_row	= mysql_fetch_row($sta_result);

				$candidate_name	= $sta_row[0];

			} elseif (substr($row[3], 0, 4) == 'oppr') {

				$stf_query	= "SELECT 
									CONCAT_WS(' ', fname, mname, lname) 
								FROM 
									staffoppr_contact 
								WHERE 
									sno='". substr($row[3], 4, strlen($row[3])) . "'";

				$stf_result	= mysql_query($stf_query, $db);

				$stf_row	= mysql_fetch_row($stf_result);

				$candidate_name	= $stf_row[0];

			} elseif (substr($row[3], 0, 3) == 'com') {

				$stc_query	= "SELECT cname FROM staffoppr_cinfo WHERE sno='" . substr($row[3], 3, strlen($row[3])) . "'";

				$stc_result	= mysql_query($stc_query, $db);

				$stc_row	= mysql_fetch_row($stc_result);

				$candidate_name	= $stc_row[0];

			} elseif (substr($row[3], 0, 4) == 'cand') {

				$can_query	= "SELECT CONCAT_WS(' ', fname, mname, lname) FROM candidate_general WHERE username='" . $row[3] . "'";

				$can_result	= mysql_query($can_query, $db);

				$can_row	= mysql_fetch_row($can_result);

				$candidate_name	= $can_row[0];

			} elseif (substr($row[3], 0, 3) == 'req') {

				$pos_query	= "SELECT postitle FROM posdesc WHERE posid='" . substr($row[3], 3, strlen($row[3])) . "'";

				$pos_result	= mysql_query($pos_query, $db);

				$pos_row	= mysql_fetch_row($pos_result);

				$candidate_name	= $pos_row[0];

				$row[4]	= ($row[4] == "Marketing->Candidates") ? 'Req_Mngmt' : $row[4];
			}

			if ($candidate_name == '' && $row[4] == 'Collaboration->Task_Manager') {

				$lis_query	= "SELECT name FROM emp_list WHERE username = '".$row[5]."'";

				$lis_result	= mysql_query($lis_query, $db);

				$lis_row	= mysql_fetch_row($lis_result);

				$candidate_name	= $lis_row[0];
			}

			if (trim($candidate_name) != '')
			$candidate_name	= "( $candidate_name )";
           
			// Convert task to Task
			$row[6]	= ucfirst($row[6]);

			
	
			if ($row[4] == '') {
			if($chkdata == false && $row[8] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
				$table_row .= "<tr>
			<td colspan='2'>
				<div class='edesktasks'>
					<a href=\"javascript:showTaskEdit('', '" . $row[0] ."', '" . $row[3] . "');\" title='" . dispfdb($row[2]) . " ".$candidate_name."'>
					<div valign='middle' style='overflow:hidden;white-space:nowrap;float:left;width:86px;'>
						<font style='font-size:100%;'>" . dispfdb($row[2]) . "</font>
						
					</div>
				
					<div valign='middle' class='eWidgetsText' class='eWidgetsText'>&nbsp;
					<font style='font-size:100%;'>".$row[1] . $candidate_name."</font>
										
					</div>
					<div style='clear:both'></div>
					</a>	
				</div>
			</td>
			
			
			</tr>";
		}
			
				//$table_row	.= "<tr class='tr1bgcolor'><td><a href=\"javascript:showTaskEdit('', '" . $row[0] ."', '" . $row[3] . "');\">" . dispfdb($row[2] . " - " . $row[1] . $candidate_name) . " </a></td></tr>";

			} elseif ($row[4] == 'Collaboration->Task_Manager') {
				if($chkdata == false && $row[8] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
				$table_row .= "<tr>
			<td colspan='2'>
				<div class='edesktasks'>
					<a href=\"javascript:winopen1('pathlocator.php?ptype=$row[6]&mname=$row[4]&addr=$username&recsno=$row[0]&con_id=$row[3]&homeday=$homeday&task_type=$row[6]');\" title='" . dispfdb($row[2]) . " ".$candidate_name."'>
					<div valign='middle' style='overflow:hidden;white-space:nowrap;float:left;width:86px;'>
						<font style='font-size:100%;'>" . dispfdb($row[2]) . "</font>
						
					</div>
				
					<div valign='middle' class='eWidgetsText'>&nbsp;
					<font style='font-size:100%;'>".$row[1] . $candidate_name."</font>
											
					</div>
					<div style='clear:both'></div>
					</a>
				</div>
			</td>
			
			</tr>";
			}
				//$table_row	.= "<tr class='tr1bgcolor'><td><a href=\"javascript:winopen1('pathlocator.php?ptype=$row[6]&mname=$row[4]&addr=$username&recsno=$row[0]&con_id=$row[3]&homeday=$homeday&task_type=$row[6]');\">" . dispfdb($row[2] . " - " . $row[1] . $candidate_name). "</a></td></tr>";

			} else {				
				if($row[7] != '' && $row[6] == 'Todo' ){
					$expArr = explode(":",$row[7]);
					if(is_array($expArr)){
						if( $expArr[0] !='00' ){
							if($expArr[0] >= 12 ){								
								if($expArr[0] == 12){
									$strTime =  " - " .$expArr[0].':'.$expArr[1]." PM";
								}else{	
									$timvVal = $expArr[0] - 12;
									$strTime =  " - " .$timvVal.':'.$expArr[1]." PM";
								}
							}else{
								$strTime =  " - " .$expArr[0].':'.$expArr[1]." AM";
							}
						}else{
							$strTime =  " - " .$expArr[0].':'.$expArr[1]." AM";
						}
					}else{
						$strTime = '';
					}	
				}else{
					$strTime = '';
				}
				if($chkdata == false && $row[8] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
				$table_row .= "<tr>
			<td colspan='2'>
				<div class='edesktasks'>
					<a href=\"javascript:winopen('pathlocator.php?ptype=$row[6]&mname=$row[4]&addr=$username&recsno=$row[0]&con_id=$row[3]&task_type=$row[6]&module=CRM');\" title='" . dispfdb($row[2] . $strTime) . " ".$candidate_name."'>
					<div valign='middle' style='overflow:hidden;white-space:nowrap;float:left;width:86px;'>
						<font style='font-size:100%;'>" . dispfdb($row[2]) . "</font>
					</div>
				
					<div valign='middle' class='eWidgetsText'>&nbsp;
					<font style='font-size:100%;'>".$row[1] . stripslashes($candidate_name)."</font>
											
					</div>
					<div style='clear:both'></div>
					</a>
				</div>
			</td>
			
			</tr>";
		}
			
				//$table_row	.= "<tr class='tr1bgcolor'><td><a href=\"javascript:winopen('pathlocator.php?ptype=$row[6]&mname=$row[4]&addr=$username&recsno=$row[0]&con_id=$row[3]&task_type=$row[6]');\">" . dispfdb($row[2] . $strTime." - " . $row[1].  $candidate_name). " </a></td></tr>";
			}

		} //end-of-while

		if (empty($task_count)) {

			$table_row	= '<i>You have no tasks assigned today.</i>';
		}

		return $table_row;
	}

	/*
	 * This function displays the REMINDERS WIDGET in the eDesk
	 *
	 * param	string	$homeday
	 * param	string	$username
	 * param	date	$start_date
	 * param	integer	$widget_id
	 * return	string	$reminders_widget
	 */
	public function getRemindersWidget($homeday, $username, $start_date, $widget_id) {

		$toolbar			= '';
		$reminders_widget	= '';

		//$image	= 'reminders.gif';
		//$title		= 'Reminders';
		$title      = '<span class="widgtitlecolorBlue"><i class="fa fa-bell fa-lg"></i> Reminders</span>';
		$tooltip	= 'Reminders';
		$toolbar	= '<a href=javascript:refreshWidgets("reminders",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';
		
		$reminders_widget	= $this->getEventWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$reminders_widget	.= $this->getRemindersEvents($homeday, $username, $start_date);

		$reminders_widget	.= $this->getEventWidgetFooter();

		return $reminders_widget;
	}

	/*
	 * This function fetches the REMINDERS EVENTS
	 *
	 * param	string	$homeday
	 * param	string	$username
	 * param	date	$start_date
	 * return	string	$table_row
	 */
	public function getRemindersEvents($homeday, $username, $start_date) {

		GLOBAL $accountingpref, $db;

		$count		= 0;
		$asmt_count	= 0;
		$table_row	= '';
		// Code for Admin Preferences for Admin_Management
		$sql = "SELECT admin from sysuser where username=$username";
		$rs = mysql_query($sql,$db);
		$row = mysql_fetch_assoc($rs);
		$admin_pref = $row['admin'];
		$chkdata = strpos($admin_pref, '+14+');//$admin_pref!='NO'  $chkdata !== false
		
		$rem_query	= 'SELECT 
							tasklist.sno, tasklist.title,' . tzRetQueryStringSelBoxDate("tasklist.startdate", "Date", "/") . ", tasklist.contactsno, tasklist.modulename, tasklist.cuser,cpr.activity_status
						FROM
							tasklist
							LEFT JOIN cmngmt_pr cpr ON (tasklist.sno = cpr.tysno AND cpr.title= 'Task')
						WHERE
							((tasklist.cuser = '$username' AND tasklist.sendto = '') OR FIND_IN_SET('$username', tasklist.sendto)) AND tasklist.startdate <= '$start_date' AND
							DATE_ADD(tasklist.remdate, INTERVAL IF(tasklist.CTIME='00:00:00', '23:59:59', tasklist.CTIME) HOUR_SECOND) >= 
							DATE_ADD(STR_TO_DATE('$start_date', '%Y-%m-%d'), INTERVAL CURTIME() HOUR_SECOND)
							AND tasklist.rem = 'Yes' AND (tasklist.status = 'active' OR tasklist.status = 'new') AND tasklist.taskstatus != 'Completed' $groupby
						ORDER BY
							tasklist.startdate DESC";

		$rem_result	= mysql_query($rem_query, $db);

		while ($rem_row = mysql_fetch_row($rem_result)) {

			$contact_name	= '';

			$count++;

			if (substr($rem_row[3], 0, 3) == 'con') {

				$con_query	= "SELECT name FROM consultant_list WHERE username='". $rem_row[3] . "'";

				$con_result	= mysql_query($con_query, $db);

				$con_row	= mysql_fetch_row($con_result);

				$contact_name	= $con_row[0];

			} elseif (substr($rem_row[3],0,3) == 'sub') {

				$sub_query	= "SELECT CONCAT_WS(' ', fname, mname, lname) FROM subconsultant WHERE subconid='" . $rem_row[3] . "'";

				$sub_result	= mysql_query($sub_query, $db);

				$sub_row	= mysql_fetch_row($sub_result);

				$contact_name	= $sub_row[0];

			} elseif (substr($rem_row[3], 0, 3) == 'emp') {

				$emp_query	= "SELECT name FROM emp_list WHERE sno='" . substr($rem_row[3], 3, strlen($rem_row[3])) . "'";

				$emp_result	= mysql_query($emp_query, $db);

				$emp_row	= mysql_fetch_row($emp_result);

				$contact_name	= $emp_row[0];

			} elseif (substr($rem_row[3], 0, 3) == 'acc') {

				$sta_query	= "SELECT
									staffacc_cinfo.cname
								FROM
									staffacc_list
									LEFT JOIN staffacc_cinfo ON staffacc_list.username = staffacc_cinfo.username
								WHERE
									staffacc_list.username='" . $rem_row[3] . "'";

				$sta_result	= mysql_query($sta_query, $db);

				$sta_row	= mysql_fetch_row($sta_result);

				$contact_name	= $sta_row[0];

			} elseif (substr($rem_row[3], 0, 4) == 'oppr') {

				$stf_query	= "SELECT CONCAT_WS(' ', fname, mname, lname) FROM staffoppr_contact WHERE sno='" . substr($rem_row[3], 4, strlen($rem_row[3])) . "'";

				$stf_result	= mysql_query($stf_query, $db);

				$stf_row	= mysql_fetch_row($stf_result);

				$contact_name	= $stf_row[0];

			} elseif (substr($rem_row[3], 0, 3) == 'com') {

				$stc_query	= "SELECT cname FROM staffoppr_cinfo WHERE sno='".substr($rem_row[3],3,strlen($rem_row[3]))."'";

				$stc_result	= mysql_query($stc_query, $db);

				$stc_row	= mysql_fetch_row($stc_result);

				$contact_name	= $stc_row[0];

			} elseif (substr($rem_row[3], 0, 4) == 'cand') {

				$can_query	= "SELECT CONCAT_WS(' ',fname,mname,lname) FROM candidate_general WHERE username='".$rem_row[3]."'";

				$can_result	= mysql_query($can_query, $db);

				$can_row	= mysql_fetch_row($can_result);

				$contact_name	= $can_row[0];

			} elseif (substr($rem_row[3], 0, 3) == 'req') {

				$pos_query	= "SELECT postitle FROM posdesc WHERE posid='" . substr($rem_row[3], 3, strlen($rem_row[3])) . "'";

				$pos_result	= mysql_query($pos_query, $db);

				$pos_row	= mysql_fetch_row($pos_result);

				$contact_name	= $pos_row[0];

				$rem_row[4]	= ($rem_row[4] == 'Marketing->Candidates') ? 'Req_Mngmt' : $rem_row[4];
			}

			if ($contact_name == '' && $rem_row[4] == 'Collaboration->Task_Manager') {

				$emp_query	= "SELECT name FROM emp_list WHERE username='" . $rem_row[5] . "'";

				$emp_result	= mysql_query($emp_query, $db);

				$emp_row	= mysql_fetch_row($emp_result);

				$contact_name	= $emp_row[0];
			}

			if (trim($contact_name) != '')
			$contact_name	= "( $contact_name )";

			if ($rem_row[4] == '') {
				if($chkdata == false && $rem_row[6] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
				$table_row	.= "<tr class=tr1bgcolor><td><a href=javascript:showTaskEdit('','" . $rem_row[0] . "','" . $rem_row[3] . "');>" . dispfdb($rem_row[2] . ' - ' . $rem_row[1] . $contact_name). ' </a></td></tr>';
			}

			} elseif ($rem_row[4] == 'Collaboration->Task_Manager') {
				if($chkdata == false && $rem_row[6] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{

				$table_row	.= "<tr class=tr1bgcolor><td><a href=\"javascript:winopen1('pathlocator.php?ptype=Task&mname=$rem_row[4]&addr=$username&recsno=$rem_row[0]&con_id=$rem_row[3]&homeday=$homeday')\">" . dispfdb($rem_row[2] . ' - ' . $rem_row[1] . $contact_name) . ' </a></td></tr>';
			}

			} else {
				if($chkdata == false && $rem_row[6] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
				$table_row	.= "<tr class=tr1bgcolor><td><a href=\"javascript:winopen('pathlocator.php?ptype=Task&mname=$rem_row[4]&addr=$username&recsno=$rem_row[0]&con_id=$rem_row[3]&module=CRM')\">" . dispfdb($rem_row[2] . ' - ' . $rem_row[1] . $contact_name). ' </a></td></tr>';
			}
			}

		} // end-of-while

		$appno	= '';

		$nCurrDateVal	= mktime(date('h'), date('i'), 0, date('m', strtotime($start_date)), date('d', strtotime($start_date)), date('Y', strtotime($start_date)));

		//For getting the timestamp value of the date selected in edesk calendar.
		$day_select_home	= getCurTime($start_date);

		$utzos	= getUserSTZOffset();

		if ($utzos == '')
		$utzos	= 0;

		list ($gmtMon, $gmtDay, $gmtYr, $gmtHr, $gmtMin)	= explode('/', gmdate('m/d/Y/H/i'));

		$getGMTTime		= mktime($gmtHr, $gmtMin, 0, $gmtMon, $gmtDay, $gmtYr);

		$dayCurrTime	= $getGMTTime + ($utzos);

		list ($hr, $min)	= explode(':', date('H:i', $dayCurrTime));

		list ($yr, $mon, $day)	= explode('-', $start_date);

		$daySelDate	= mktime($hr, $min, 0, $mon, $day, $yr);

		$apmt_query		= "SELECT 
								appointments.title, appointments.sno, UNIX_TIMESTAMP(CONVERT_TZ(NOW(),'EST5EDT','GMT')) - '$utzos', IF(appointments.recurrence='none', (appointments.sdatetime + '$utzos'), (recurrences.otime + '$utzos')) sdatetime, appointments.event, DATE_FORMAT(FROM_UNIXTIME(appointments.sdatetime + '$utzos'), '%l:%i%p') stime, '', appointments.contactsno, appointments.modulename, appointments.username, appointments.rtime, appointments.priority, appointments.recurrence, appointments.enddate, appointments.recurrence_type, appointments.recurrence_subtype, appointments.recurrence_day, appointments.recurrence_week, appointments.recurrence_month, appointments.enddate_option, '',cpr.activity_status
							FROM
								appointments
								LEFT JOIN recurrences ON recurrences.ano = appointments.sno
								LEFT JOIN cmngmt_pr cpr ON (tasklist.sno = cpr.tysno AND cpr.title= 'Appointment')
							WHERE
								appointments.status='active' AND 
								(appointments.username='$username' OR FIND_IN_SET('$username', appointments.approved) > 0 OR 
								FIND_IN_SET('$username', appointments.tentative)>0) AND appointments.dis='Yes' AND 
								(((IF(appointments.recurrence = 'none', (appointments.sdatetime + '$utzos'), (recurrences.otime + '$utzos'))) - $daySelDate) <= appointments.rtime 
								AND ((IF(appointments.recurrence='none', (appointments.sdatetime + '$utzos'), (recurrences.otime + '$utzos'))) - $daySelDate) >= 0 ) 
							ORDER BY 
								sdatetime, appointments.title ASC";

		$apmt_result	= mysql_query($apmt_query, $db);

		while ($arow = mysql_fetch_array($apmt_result)) {

			$gtimeval	= '';

			if ($arow[4] != 'allday')
			$gtimeval	= $arow[5];

			$flag	= TRUE;

			if (substr($arow[7], 0, 3) == 'con') {

				$con_query	= "SELECT astatus FROM consultant_list WHERE username='" . $arow[7] . "'";

				$con_result	= mysql_query($con_query, $db);

				$con_row	= mysql_fetch_row($con_result);

				if ($con_row[0]=="RARCH" || $con_row[0]=="backup" || $con_row[0]=="ARCH" || $con_row[0]=="DELE" || $con_row[0]=="INACT") {

					$flag	= FALSE;
				}

			} elseif (substr($arow[7], 0, 3) == 'sub') {

				$sub_query	= "SELECT status FROM subconsultant WHERE subconid='" . $arow[7] . "'";

				$sub_result	= mysql_query($sub_query, $db);

				$sub_row	= mysql_fetch_row($sub_result);

				if ($sub_row[0]=="INACTIVE" || $sub_row[0]=="backup") {

					$flag	= FALSE;
				}

			} elseif (substr($arow[7], 0, 3) == 'emp') {

				$emp_query	= "SELECT lstatus FROM emp_list WHERE sno='" . substr($arow[7], 3, strlen($arow[7])) . "'";

				$emp_result	= mysql_query($emp_query, $db);

				$emp_row	= mysql_fetch_row($emp_result);

				if ($emp_row[0] != "DA") {

					$flag	= TRUE;

				} else {

					$flag	= FALSE;
				}

			} elseif (substr($arow[7], 0, 3) == 'acc') {

				$sta_query	= "SELECT status FROM staffacc_list WHERE username='" . $arow[7] . "'";

				$sta_result	= mysql_query($sta_query, $db);

				$sta_row	= mysql_fetch_row($sta_result);

				if ($sta_row[0]=="ACTIVE") {

					$flag	= TRUE;
				}
				else
				{
					$flag	= FALSE;
				}

			} elseif (substr($arow[7], 0, 4) == 'oppr') {

				$stc_query	= "SELECT status FROM staffoppr_contact WHERE sno='".substr($arow[7],4,strlen($arow[7]))."'";

				$stc_result	= mysql_query($stc_query, $db);

				$stc_row	= mysql_fetch_row($stc_result);

				if ($stc_row[0]=="INACTIVE") {

					$flag	= FALSE;
				}

			} elseif (substr($arow[7], 0, 3) == 'com') {

				$stf_query	= "SELECT status FROM staffoppr_cinfo WHERE sno='" . substr($arow[7], 3, strlen($arow[7])) . "'";

				$stf_result	= mysql_query($stf_query, $db);

				$stf_row	= mysql_fetch_row($stf_result);

				if ($stf_row[0]=="INACTIVE") {

					$flag	= FALSE;
				}
			}

			if ($flag) {

				$new_class	= '';

				if ($arow['priority'] == 'High')
				$new_class	= "class='apply-priority-edesk'";

				$count ++;

				$openModulename	= ($arow[9]==$username) ? $arow[8] : 'Collaboration->Scheduler';

				$openWinname	= ($openModulename == 'Collaboration->Scheduler') ? '3' : '';
				if($chkdata == false && $arow[21] == '1'){
						//Hiding the appointments which are hidden from Admin for those who don't have access to Admin>>Data Management
					}else{
				$table_row	.= "
					<tr class='tr1bgcolor'>
						<td>
							<a href=\"javascript:winopen" . $openWinname . "('pathlocator.php?ptype=Appointment&mname=$openModulename&addr=$username&recsno=$arow[1]&con_id=$arow[7]&thisday=$homeday')\" " . $new_class . '>' . date('m/d/Y', $arow[3]) . ' ' . $gtimeval . ' - ' . $arow[0] . '</a>
						</td>
					</tr>';
				}
			}

		} // end-of-while


		if (chkUserPref($accountingpref, '11')) {

			if (isset($homeday))
			$cur_time	= $homeday;
			else
			$cur_time	= time();

			//$asmt_query	= 'SELECT
			//					emp_list.sno, ' . tzRetQueryStringSTRTODate('empcon_jobs.s_date', '%m-%d-%Y', 'Date', '-') . ',' . 
			//					tzRetQueryStringSTRTODate('empcon_jobs.e_date', '%m-%d-%Y', 'Date', '-') . 
			//					", empcon_jobs.project, empcon_jobs.username, empcon_jobs.rtime, empcon_jobs.sno, empcon_jobs.assg_status
			//				FROM
			//					emp_list
			//					LEFT JOIN empcon_jobs ON emp_list.username = empcon_jobs.username
			//				WHERE
			//					emp_list.lstatus != 'DA' AND empcon_jobs.jtype != '' AND empcon_jobs.jotype != '0'
			//					AND ((empcon_jobs.assg_status = 'approved') OR (empcon_jobs.assg_status = 'pending' AND empcon_jobs.modulename = 'my placement'))
			//					AND (UNIX_TIMESTAMP(empcon_jobs.e_date) - $cur_time) <= rtime AND empcon_jobs.rtime != 0
			//					AND empcon_jobs.jtype = 'OP'
			//				GROUP BY
			//					empcon_jobs.sno
			//				ORDER BY
			//					DATE_FORMAT(STR_TO_DATE(empcon_jobs.e_date, '%m-%d-%Y'), '%Y-%m-%d')";
								
			$asmt_query = "SELECT   emp_list.sno,
									DATE_FORMAT(STR_TO_DATE(IF(hrcon_jobs.s_date = '0-0-0', '00-00-0000', hrcon_jobs.s_date), '%m-%d-%Y'), '%m-%d-%Y'),
									DATE_FORMAT(STR_TO_DATE(IF(hrcon_jobs.e_date = '0-0-0', '00-00-0000', hrcon_jobs.e_date), '%m-%d-%Y'), '%m-%d-%Y'),
									hrcon_jobs.project,
									hrcon_jobs.username,
									hrcon_jobs.rtime,
									hrcon_jobs.sno,
									hrcon_jobs.ustatus
						   FROM     emp_list LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
						   WHERE    emp_list.lstatus != 'DA' AND hrcon_jobs.jtype != '' AND hrcon_jobs.jotype != '0' AND ((hrcon_jobs.ustatus = 'active') OR (hrcon_jobs.ustatus = 'pending' AND hrcon_jobs.modulename = 'my placement')) AND (UNIX_TIMESTAMP(hrcon_jobs.e_date) - $cur_time) <= rtime AND hrcon_jobs.rtime != 0 AND hrcon_jobs.jtype = 'OP'
						   GROUP BY hrcon_jobs.sno
						   ORDER BY DATE_FORMAT(STR_TO_DATE(hrcon_jobs.e_date, '%m-%d-%Y'), '%Y-%m-%d')";

			$asmt_result	= mysql_query($asmt_query, $db);

			while ($asmt_row = mysql_fetch_row($asmt_result)) {

				$asmt_count	++;

				$start_date			= explode('-', $asmt_row[1]);
				$asmt_start_date	= $start_date[0] . '/' . $start_date[1] . '/' . $start_date[2];

				$end_date			= explode('-', $asmt_row[2]);
				$asmt_end_date		= $end_date[0] . '/' . $end_date[1] . '/' . $end_date[2];

				$asg_status	= 'approved';

				$recsno		= $asmt_row[6] . '| 15 |' . $asg_status . '|' . $asmt_row[0];

				if ($asmt_row[3] != '') {

					$table_row	.= "
						<tr class='tr1bgcolor'>
							<td>
								<a href=\"javascript:winopen4('pathlocator.php?ptype=Assignment&mname=Accounting->Assignments&recsno=$recsno&con_id=$asmt_row[4]&val_hr=$asmt_row[6]')\">" . $asmt_start_date . ' - '. $asmt_end_date . ' - ' . $asmt_row[3] . '</a>
							</td>
						</tr>';

				} else {

					$table_row	.= "
						<tr class='tr1bgcolor'>
							<td>
								<a href=\"javascript:winopen4('pathlocator.php?ptype=Assignment&mname=Accounting->Assignments&recsno=$recsno&con_id=$asmt_row[4]&val_hr=$asmt_row[6]')\">" . $asmt_start_date . ' - '. $asmt_end_date . ' - Project ' . '</a>
							</td>
						</tr>';
				}
			}

		} // end-of-if

		if (($count == 0) && ($asmt_count == 0)) {

			$table_row	= '<tr><td><i>You have no reminders today.</i></td></tr>';
		}

		return $table_row;
	}

	/*
	 * This function displays the EMAIL WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$email_widget
	 */
	public function getEmailWidget($username, $widget_id) {

		GLOBAL $collaborationpref, $db;

		$inbox_link		= '';
		$message_text	= '';

		if (chkUserPref($collaborationpref, '1')) {

			$email_query	= "SELECT
									fid, foldername, unread, total
								FROM
									e_folder
								WHERE
									username = '$username' AND dis = 'Yes' AND unread != 0
								ORDER BY
									foldername";

			$email_result	= mysql_query($email_query, $db);

			while ($email_row = mysql_fetch_row($email_result)) {

				$folder_id		= $email_row[0];
				$folder_name	= $email_row[1];
				$unread_count	= $email_row[2];
				$total_count	= $email_row[3];

				$inbox_link	.= "<a href=/BSOS/Collaboration/Email/Inbox.php?x=0&folder=$folder_id>$folder_name</a>:$total_count<strong> ($unread_count)</strong><br/>";
			}

			$inbox_name		= 'inbox';

			$mail_query		= "SELECT total, unread FROM e_folder WHERE username='$username' AND foldername='$inbox_name' AND parent='system'";

			$mail_result	= mysql_query($mail_query, $db);

			$mail_row		= mysql_fetch_row($mail_result);

			$tot_count	= $mail_row[0];
			$unrd_count	= $mail_row[1];

			$mail_preference	= TRUE;

			if ($unrd_count == 0) {

				$message_text	= "<a href='/BSOS/Collaboration/Email/Inbox.php?x=0&folder=$inbox_name'>Inbox</a>: $tot_count<br/>";

			} else {

				$message_text	= "<a href='/BSOS/Collaboration/Email/Inbox.php?x=0&folder=$inbox_name'>Inbox</a>: $tot_count<strong> ($unrd_count)</strong><br/>";
			}

		} else {

			 $message_text	= '<i>No mail account is assigned to you.</i>';
		}

		$toolbar		= '';
		$email_widget	= '';

		//$image	= 'email.gif';
		$title		= '<span class="widgtitlecolorBlue"><i class="fa fa-envelope fa-lg"></i> E-mail</span><a href=javascript:newmail();> ( New )</a>';
		$tooltip	= 'E-mail';

		if (OUTLOOK_PLUG_IN == 'Y')
			$title	= '<span class="widgtitlecolorBlue">E-mail [<a href="javascript:openSetup();">Setup</a>]</span>';
		
		if ($mail_preference) {

			$toolbar	.= '';

			$toolbar	.= '<a href=javascript:refreshWidgets("email",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';
		}

		
		$email_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$email_widget	.= $message_text;

		$email_widget	.= $inbox_link;

		$email_widget	.= $this->getWidgetFooter();

		return $email_widget;
	}

	/*
	 * This function displays the OUT LOOK WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$email_widget
	 */
	public function getOutLookWidget($username, $widget_id) {

		GLOBAL $collaborationpref, $db;

		$toolbar		= '';
		$email_widget	= '';

		//$image	= 'email.gif';
		$title		= '<span class="widgtitlecolorBlue">E-mail</span>';
		$tooltip	= 'E-mail';
		$toolbar	= '<a href=javascript:refreshWidgets("outlook",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';		
		
		$message_text	= '<i>You have no email accounts configured.</i>';

		if (OUTLOOK_PLUG_IN == 'Y')
		$title	= '<span class="widgtitlecolorBlue">E-mail [<a href="javascript:openSetup();">Setup</a>]</span>';

		

		$email_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$email_widget	.= $message_text;

		$email_widget	.= $inbox_link;

		$email_widget	.= $this->getWidgetFooter();

		return $email_widget;
	}

	/*
	 * This function displays the CAMPAIGNS WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$campaigns_widget
	 */
	public function getCampaignsWidget($username, $widget_id) {

		GLOBAL $db, $crmpref;

		$campaigns_widget	= '';

		if (chkUserPref($crmpref, '1')) {

			$campaign_count		= 0;
			$message_text		= '<table>';
			$campaign_folder	= 'CampaignResponses';

			$cam_query	= "SELECT
								sno, subject, id
							FROM
								campaign_list
							WHERE
								par_id='0'
								AND (campaign_list.username='$username' OR FIND_IN_SET('$username', accessto) > 0 OR accessto = 'all') 
								AND campaign_list.status = 'OPEN'
							ORDER BY
								campaign_list.camp_date DESC
							LIMIT 20";

			$cam_result	= mysql_query($cam_query, $db);

			while ($cam_row = mysql_fetch_row($cam_result)) {

				preg_match("/(.+)\(eCampaign@.+\)/isU", $cam_row[1], $arrMatches);

				$subject_name	= $arrMatches[1];

				if ($subject_name == '')
				$subject_name	= $cam_row[1];

				$esub	= $cam_row[2];

				$campaign_count ++;

				$pro_query	= "SELECT COUNT(1) FROM process_mail_headers WHERE folder = '$campaign_folder' AND MATCH(subject) AGAINST('$esub') AND status = 'Active'";

				$pro_result	= mysql_query($pro_query, $db);

				$pro_row	= mysql_fetch_row($pro_result);

				$camp_count	= $pro_row[0];


				$hed_query	= "SELECT COUNT(1) FROM process_mail_headers WHERE folder = '$campaign_folder' AND MATCH(subject) AGAINST('$esub') AND seen = 'U' AND status = 'Active'";

				$hed_result	= mysql_query($hed_query, $db);

				$hed_row	= mysql_fetch_row($hed_result);

				$unseen		= $hed_row[0];

				if ($unseen == 0)
				{
					$message_text .= "<tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='javascript:vieweCampaign(" . $cam_row[2] . ")' title='$subject_name'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='javascript:vieweCampaign(" . $cam_row[2] . ")' title='$subject_name'><font style='font-size:100%;'>$subject_name</font>
											</a>
										</div>
										
									</td>
									
									<td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$camp_count</font>
										</div>
									</td>
									</tr>
									</table>									
									</tr>";
					
					//$message_text	.= "<a href=javascript:vieweCampaign(" . $cam_row[2] . ")>$subject_name</a>: $camp_count<br/>";
				}				
				else
				{
					$message_text .= "<tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='javascript:vieweCampaign(" . $cam_row[2] . ")' title='$subject_name'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='javascript:vieweCampaign(" . $cam_row[2] . ")' title='$subject_name'><font style='font-size:100%;'>$subject_name</font>
											</a>
										</div>
										
									</td>
									
									<td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$camp_count ($unseen Unread)</font>
										</div>
									</td>
									</tr>
									</table>									
									</tr>";
					//$message_text	.= "<a href=javascript:vieweCampaign(" . $cam_row[2] . ")>$subject_name</a>: $camp_count ($unseen Unread)<br/>";
				}
			}

			if ($campaign_count == 0) {

				$ecamp_link		= '';
				$message_text	.= '<tr><td><i>No eCampaigns</i></td></tr>';

			} else {

				$ecamp_link	= '/BSOS/Marketing/Campaigns/Campaigns.php';
				
			}
			$message_text .= '</table>';
			$toolbar	= '';
			//$image	= 'campaigns.gif';
			$title		= '<span class="widgtitlecolorBlue"><i class="fa fa-life-ring fa-lg"></i>
eCampaigns</span>';
			$tooltip	= 'eCampaigns';
			$toolbar	= '<a href=javascript:refreshWidgets("campaigns",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';			

			$campaigns_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

			$campaigns_widget	.= $message_text;

			$campaigns_widget	.= $this->getWidgetFooter('eCampaign', $ecamp_link);
		}

		return $campaigns_widget;
	}

	/*
	 * This function displays the SUBMISSIONS WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	string	$not_in_clause
	 * param	integer	$widget_id
	 * return	string	$submissions_widget
	 */
	public function getSubmissionsWidget($username, $not_in_clause, $widget_id) {

		GLOBAL $db, $crmpref;

		$deptAccessObj = new departmentAccess();
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

		$submissions_widget	= '';

		if (chkUserPref($crmpref, '4')) {

			$max_length_query	= "SET SESSION group_concat_max_len = 102400";

			mysql_query($max_length_query, $db);

			$submissions_count	= 0;
			$message_text		= '';
			$response_folder	= 'ReqResponses';

			$submissions_query	= "SELECT
										posdesc.posid, posdesc.postitle, manage.name, staffoppr_cinfo.cname,
										posdesc.sub_sub_count, staffoppr_cinfo.sno
									FROM
										posdesc
										LEFT JOIN manage ON manage.sno=posdesc.posstatus
										LEFT JOIN staffoppr_cinfo ON staffoppr_cinfo.sno = posdesc.company
									WHERE
										(manage.name $not_in_clause OR manage.name IS NULL) 
										AND posdesc.status IN('approve', 'Accepted') 
										AND (posdesc.username='$username' OR FIND_IN_SET('$username', posdesc.accessto) > 0 
										OR posdesc.accessto = 'all' OR posdesc.type='staffacc')
										AND sub_sub_count != 0 AND posdesc.deptid !='0' AND posdesc.deptid IN (".$deptAccesSno.")
									ORDER BY
										posdesc.mdate DESC, staffoppr_cinfo.cname, posdesc.postitle ASC
									LIMIT 20";

			$submissions_result	= mysql_query($submissions_query, $db);

			while ($sub_row = mysql_fetch_array($submissions_result)) {

				$nMails			= 0;
				$nURMails		= 0;
				$submissions	= FALSE;

				$res_query	= "SELECT 
									GROUP_CONCAT(seqnumber SEPARATOR ' ') 
								FROM 
									reqresponse 
								WHERE 
									posid='" . $sub_row[0] . "' AND par_id = '0'";

				$res_result	= mysql_query($res_query, $db);

				$res_row	= mysql_fetch_row($res_result);

				$seq_number	= $res_row[0];


				$mail_query		= "SELECT 
										COUNT(1) 
									FROM 
										process_mail_headers pmh 
									WHERE 
										pmh.folder = '$response_folder' AND MATCH(pmh.subject) AGAINST('$seq_number')";

				$mail_result	= mysql_query($mail_query, $db);

				if (mysql_num_rows($mail_result) > 0) {

					$submissions	= TRUE;

					$mail_row	= mysql_fetch_row($mail_result);

					$nMails		= $mail_row[0];

					$pro_query	= "SELECT 
										COUNT(1) 
									FROM 
										process_mail_headers pmh 
									WHERE 
										pmh.folder = '$response_folder' AND MATCH(pmh.subject) AGAINST('$seq_number')
										AND pmh.seen = 'U'";

					$pro_result	= mysql_query($pro_query, $db);

					$pro_row	= mysql_fetch_row($pro_result);

					$nURMails	= $pro_row[0];
				}

				if ($submissions) {

					$submissions_count ++;

					$companyrow	= $sub_row[3];

					$postitle	= $sub_row[1];

					$message_text	.= '<tr>';

					if ($sub_row[3] != '') {

						$message_text	.= "
							<td>
								<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
									<a href=\"javascript:submissionwindow('/BSOS/Marketing/Companies/viewcompanySummary.php?addr=" . $sub_row[5] . "&module=CRM', 'subinfo', 1200, 700);\" title='" . html_tls_entities($sub_row[3], ENT_QUOTES) . "'>" . 
										"<font style='font-size:100%;'>" . html_tls_entities(stripslashes($companyrow), ENT_QUOTES) . '</font>
									</a>
								</div>
							</td>';

					} else {

						$message_text	.= '<td>&nbsp;</td>';
					}

					$message_text	.= '<td valign="middle">&nbsp;</td>';

					if ($sub_row[1] != '') {

						$message_text	.= "
							<td>
								<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
									<a href=\"javascript:submissionwindow('/BSOS/Sales/Req_Mngmt/redirectjob.php?addr=" . $sub_row[0] . "&module=CRM', 'subinfo', 1200, 700);\" title='" . html_tls_entities($sub_row[1], ENT_QUOTES) . "'>". 
										"<font style='font-size:100%;'>". html_tls_entities($postitle, ENT_QUOTES) ."</font>
									</a>
								</div>
							</td>";

					} else {

						$message_text	.= '<td>&nbsp;</td>';
					}

					$message_text	.= '<td valign="middle">&nbsp;</td>';

					if ($sub_row[4] != '') {

						$message_text	.= "
							<td align='right'>
								<a href=javascript:submissionwindow('/BSOS/Sales/Req_Mngmt/showreqpagesub1.php?frm=canddetails&addr=".$sub_row[0]."&module=CRM','cand',1200,1200)>" . $sub_row[4] . '</a>
							</td>';

					} else {

						$message_text	.= '<td align="right">' . $sub_row[4] . '</td>';
					}

					$message_text	.= '<td valign="middle">&nbsp;&nbsp;</td>';

					if ($nURMails != 0 && $nURMails != '') {

						$nURMails	= "<a href='javascript:viewReq(" . $sub_row[0] . ");'>
											<font color='#484848'><b>$nURMails</b></font>
										</a>";
					}

					$message_text	.= "<td align='right'>$nMails <span class='unreadmail'>($nURMails)</span></td></tr>";
				}
			}

			if ($submissions_count == 0) {

				$message_text	= '<tr><td><i>No Submissions</i></td></tr>';
				$subm_link		= '';

			} else {

				$subm_link	= '/BSOS/Sales/Req_Mngmt/reqman.php';
			}

			$table_html	= '
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td class="submissiontext" valign="top" align="left">
							<div style="width:70px;overflow:hidden;white-space:nowrap;">Company</div>
						</td>
						<td valign="top">&nbsp;</td>
						<td nowrap class="submissiontext" valign="top" align="left">
							<div style="width:70px;overflow:hidden;white-space:nowrap;">Job Title</div>
						</td>
						<td valign="top">&nbsp;</td>
						<td class="submissiontext" valign="top" width="5%">&nbsp;#Sub</td>
						<td valign="top">&nbsp;</td>
						<td class="submissiontext" valign="top" align="center" width="15%">
							&nbsp;Inquiries<br>(<font class="unreadmail">unread</font>)
						</td>
					</tr>';

			$toolbar	= '';
			//$image	= 'submissions.gif';
			$title		= '<a href="/BSOS/Sales/Req_Mngmt/reqman.php" class="widgtitlecolorBlue"><i class="fa fa-check-square-o"></i> Submissions</a>';
			$tooltip	= 'Submissions';
			$toolbar	= '<a href=javascript:refreshWidgets("submissions",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';				

			$submissions_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

			$submissions_widget	.= $table_html;

			$submissions_widget	.= $message_text;

			$submissions_widget	.= '</table>';

			$submissions_widget	.= $this->getWidgetFooter('Submission', $subm_link);
		}

		return $submissions_widget;
	}

	/*
	 * This function displays the POSTINGS WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	string	$not_in_clause
	 * param	integer	$widget_id
	 * return	string	$postings_widget
	 */
	public function getPostingsWidget($username, $not_in_clause, $widget_id) {

		GLOBAL $db, $crmpref;

		$postings_widget	= '';

		if (chkUserPref($crmpref, '5')) {

			$postings_count		= 0;
			$message_text		= '';
			$postings_folder	= 'ReqPostResponses';

			$postings_query	= "SELECT
									posdesc.posid, posdesc.postitle
								FROM
									job_post_det, posdesc
									LEFT JOIN manage ON manage.sno = posdesc.posstatus
								WHERE
									job_post_det.posid = posdesc.posid AND (manage.name $not_in_clause OR manage.name IS NULL)
									AND posdesc.status IN('approve', 'Accepted')
									AND (posdesc.username = '$username' OR FIND_IN_SET('$username', posdesc.accessto) > 0
									OR posdesc.accessto = 'all' OR posdesc.type = 'staffacc')
								GROUP BY
									posdesc.posid
								ORDER BY
									job_post_det.senddate DESC
								LIMIT 20";

			$postings_result	= mysql_query($postings_query, $db);

			while ($postings_row = mysql_fetch_array($postings_result)) {

				$nMails		= 0;
				$nURMails	= 0;
				$submissions	= FALSE;

				// Inquiries - Total Count
				$job_query	= "SELECT
									COUNT(1)
								FROM
									job_post_det jpt, process_mail_headers pmh
								WHERE
									jpt.posid='" . $postings_row[0] . "' AND jpt.par_id='0' 
									AND pmh.subject LIKE CONCAT('%', jpt.seqnumber, '%') AND pmh.folder = 'ReqPost'";

				$job_result	= mysql_query($job_query, $db);

				if (mysql_num_rows($job_result) > 0) {

					$job_row		= mysql_fetch_row($job_result);
					$nMails			= $job_row[0];
					$submissions	= TRUE;

					// Inquires - Unread Count
					$pro_query	= "SELECT
										COUNT(1)
									FROM
										job_post_det jpt, process_mail_headers pmh
									WHERE
										jpt.posid='" . $postings_row[0] . "' AND jpt.par_id = '0'
										AND pmh.subject LIKE CONCAT('%', jpt.seqnumber, '%')
										AND pmh.folder = 'ReqPost' AND seen = 'U'";

					$pro_result	= mysql_query($pro_query, $db);

					if (mysql_num_rows($pro_result) > 0) {

						$pro_row	= mysql_fetch_row($pro_result);

						$nURMails	= $pro_row[0];
					}
				}

				$submissions_inq	= FALSE;
				$nMails1	= 0;
				$nURMails1	= 0;

				// Responses - Total Count
				$hed_query	= "SELECT
									COUNT(1)
								FROM
									job_post_det jpt, process_mail_headers pmh
								WHERE
									jpt.posid ='" . $postings_row[0] . "' AND jpt.par_id = '0' 
									AND pmh.subject LIKE CONCAT('%', jpt.seqnumber, '%') 
									AND pmh.folder = '$postings_folder'";

				$hed_result	= mysql_query($hed_query, $db);

				if (mysql_num_rows($hed_result) > 0) {

					$hed_row	= mysql_fetch_row($hed_result);
					$nMails1	= $hed_row[0];
					$submissions_inq	= TRUE;

					// Responses - Unread Count
					$pmh_query	= "SELECT
										COUNT(1)
									FROM
										job_post_det jpt, process_mail_headers pmh
									WHERE
										jpt.posid='" . $postings_row[0] . "' AND jpt.par_id = '0'
										AND pmh.subject LIKE CONCAT('%', jpt.seqnumber, '%')
										AND pmh.folder = '$postings_folder' AND seen = 'U'";

					$pmh_result	= mysql_query($pmh_query, $db);

					if (mysql_num_rows($pmh_result) > 0) {

						$pmh_row	= mysql_fetch_row($pmh_result);

						$nURMails1	= $pmh_row[0];
					}
				}

				if ($submissions || $submissions_inq) {

					$message_text	.= '<tr>';

					$postings_count ++;

					if ($nMails != 0 && $nURMails != '0')
					$nURMails	= "<font color='#484848'><b>$nURMails</b></font>";

					if ($nMails1 != 0 && $nURMails1 != '0')
					$nURMails1	= "<font color='#484848'><b>$nURMails1</b></font>";

					$message_text	.= "
						<td>&nbsp;&nbsp;
							<a href='javascript:newEnq(" . $postings_row[0] . ");'>" . html_tls_entities($postings_row[1], ENT_QUOTES) . "</a>
						</td>
						<td align='center'>$nMails ($nURMails)</td>
						<td>&nbsp;</td>
						<td align='center'>$nMails1 ($nURMails1)</td>
					</tr>";
				}
			}

			if ($postings_count == 0) {

				$post_link		= '';
				$message_text	= '<i>No Inquiries/Responses</i>';

			} else {

				$post_link	= '/BSOS/Sales/Req_Post/reqman.php';
			}

			$toolbar	= '';
			//$image	= 'postings.gif';
			$title		= '<span class="widgtitlecolorBlue"><i class="fa fa-newspaper-o fa-lg" aria-hidden="true"></i>
 Postings</span>';
			$tooltip	= 'Postings';
			$toolbar	= '<a href=javascript:refreshWidgets("postings",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';			

			$postings_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

			if ($post_link != '') {

				$postings_widget	.= '
					<table align="center" cellpadding="0" cellspacing="0" border="0" style="width:100%;*width:89%;">
						<tr>
							<td valign="top" width="45%">&nbsp;</td>
							<td class="submissiontext" valign="top" width="25%" align="center">
								&nbsp;Inquiries<br>(<font color="#484848">unread</font>)
							</td>
							<td valign="top" width="5%">&nbsp;</td>
							<td class="submissiontext" valign="top" align="center" width="25%">
								&nbsp;Responses<br>(<font color="#484848">unread</font>)
							</td>
						</tr>
						';

				$postings_widget	.= $message_text;

				$postings_widget	.= '</table>';

			} else {

				$postings_widget	.= $message_text;
			}

			$postings_widget	.= $this->getWidgetFooter('Posting', $post_link);
		}

		return $postings_widget;
	}

	/*
	 * This function displays the EMPLOYEES WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$employess_widget
	 */
	public function getEmployeesWidget($username, $widget_id) {

		GLOBAL $db, $crmpref, $hrmpref, $adminpref, $accountingpref;

		$row_sys	= array($crmpref, $hrmpref, $adminpref, $accountingpref);

		$deptAccessObj = new departmentAccess();
		$deptAccesSnoFO = $deptAccessObj->getDepartmentAccess($username,"'FO'");
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

		$message_text		= '';
		$employess_widget	= '';

		// For Employee Management/HRM. and Employee Accounts/Admin
		if (strpos($row_sys[1], "+3+") || strpos($row_sys[2], "+5+")) {

			if ($row_sys[1] != "NO" || $row_sys[2] != "NO") {

				$message_text	= '';
			}
		}

		// Consultant Accounts/Admin
		if (strpos($row_sys[2], "+5+") || strpos($row_sys[1], "+3+") || strpos($row_sys[1], "+5+")) {

			$message_text	= '<table>';

			if (strpos($row_sys[1], "+5+")) {
				
				//This is not to show the new approvals when shift scheduling is in disabled mode
				$ss_status_cond = "";
				//$asgn_ss_cond ="";
				//to show the SS for approval when even no active assignments are exists
				// $asgn_ss_cond = " AND (hrcon_jobs.ustatus = 'active' OR emp_list.roles LIKE '%p28%') ";
				// if(SHIFT_SCHEDULING_ENABLED == 'N')
				// {
				// 	$ss_status_cond = " AND emp_list.roles != 'p28' ";
				// 	$asgn_ss_cond = "AND hrcon_jobs.ustatus = 'active' ";
				// }

				// echo $query_string	= "SELECT
				// 						emp_list.sno
				// 					FROM
				// 						emp_list
				// 						LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username
				// 						LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
				// 						LEFT JOIN hrcon_w4 ON emp_list.username = hrcon_w4.username
				// 						LEFT JOIN hrcon_compen ON hrcon_compen.username = emp_list.username
				// 						LEFT JOIN department ON hrcon_compen.dept = department.sno
				// 					WHERE
				// 						hrcon_w4.ustatus = 'active' ".$asgn_ss_cond."
				// 						AND hrcon_general.ustatus = 'active' AND emp_list.lstatus != 'DA'
				// 						AND emp_list.empterminated != 'Y' AND emp_list.lstatus != 'INACTIVE'
				// 						AND emp_list.roles != '' AND emp_list.roles != 'emp'
				// 						AND FIND_IN_SET('$username', department.permission) > 0 ".$ss_status_cond."
				// 					GROUP BY
				// 						emp_list.sno";


				$query_string	= "SELECT
										emp_list.sno
									FROM
										emp_list
										LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username
										LEFT JOIN hrcon_w4 ON emp_list.username = hrcon_w4.username
										LEFT JOIN hrcon_compen ON hrcon_compen.username = emp_list.username
										LEFT JOIN department ON hrcon_compen.dept = department.sno
									WHERE
										hrcon_w4.ustatus = 'active' 
										AND hrcon_general.ustatus = 'active' 
										AND emp_list.lstatus != 'DA' 
										AND emp_list.lstatus != 'INACTIVE'
										AND emp_list.roles != '' AND emp_list.roles != 'emp' AND department.sno !='0'
										AND department.sno IN (".$deptAccesSno.") ".$ss_status_cond."
									GROUP BY
										emp_list.sno";

				$query_result	= mysql_query($query_string, $db);

				$query_rows		= mysql_num_rows($query_result);
				
				$message_text .= "<tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/HRM/Employee_Mngmt/empman.php?desk=edesk' title='New Approvals'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='/BSOS/HRM/Employee_Mngmt/empman.php?desk=edesk' title='New Approvals'><font style='font-size:100%;'>New Approvals</font>
											</a>
										</div>
										
									</td>
									
									<td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$query_rows</font>
										</div>
									</td>
									</tr>
									</table>
									
									</tr>";
			
				//$message_text	.= "<a href='/BSOS/HRM/Employee_Mngmt/empman.php?desk=edesk'>New Approvals</a>: $query_rows<br/>";
			}

			if (strpos($row_sys[1], "+3+")) {

				$query_string	= "SELECT COUNT(*) FROM applicants aplt 
									LEFT JOIN consultant_compen cc ON (cc.username = aplt.username)
									WHERE aplt.astatus NOT IN ('hire', 'HREJ', 'backup') 
									AND cc.dept !='0' AND cc.dept IN (".$deptAccesSnoFO.")";

				$query_result	= mysql_query($query_string, $db);

				$query_rows		= mysql_fetch_row($query_result);

				$rec_count		= $query_rows[0];

				if ($rec_count == '')
				$rec_count	= 0;

				$message_text .= "<tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/HRM/Hiring_Mngmt/hirman.php' title='New Hires'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='/BSOS/HRM/Hiring_Mngmt/hirman.php' title='New Hires'><font style='font-size:100%;'>New Hires</font>
											</a>
										</div>
									</td><td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$rec_count</font>
										</div>
									</td>
									</tr>
									</table>									
									
									</tr>";
				//$message_text	.= "<a href='/BSOS/HRM/Hiring_Mngmt/hirman.php'>New Hires</a>: $rec_count<br/>";
			}

			if (strpos($row_sys[2], "+5+")) {

				$query_string	= "SELECT
										applicants.username
									FROM
										applicants
										LEFT JOIN hrcon_compen ON hrcon_compen.username=applicants.username
										LEFT JOIN emp_list ON applicants.username=emp_list.username
										LEFT JOIN department ON department.sno=hrcon_compen.dept
									WHERE
										hrcon_compen.ustatus = 'active' AND applicants.astatus = 'hire'
										AND applicants.type IN ('PE', 'sub', 'con') AND emp_list.lstatus NOT IN ('DA','INACTIVE')
										AND emp_list.empterminated != 'Y' AND department.sno !='0'
										AND department.sno IN (".$deptAccesSno.")
									GROUP BY
										applicants.username
									ORDER BY
										applicants.username";

				$query_result	= mysql_query($query_string, $db);

				$rec_count	= 0;

				while ($arr = mysql_fetch_row($query_result)) {

					$rec_count ++;
				}
				
				$message_text .= "<tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/Admin/User_Mngmt/hireemp.php?desk=hires' title='New Hired'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='/BSOS/Admin/User_Mngmt/hireemp.php?desk=hires' title='New Hired'><font style='font-size:100%;'>New Hired</font>
											</a>
										</div>
									</td><td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$rec_count</font>
										</div>
									</td>
									</tr>
									</table>	
									
									</tr>";
									
				//$message_text	.= "<a href='/BSOS/Admin/User_Mngmt/hireemp.php?desk=hires'>New Hired Employees</a>: $rec_count<br/>";
			}

			if (strpos($row_sys[2], "+5+")) {

			//	$message_text	.= '<hr>';

				$query_string	= "SELECT 
										COUNT(*) 
									FROM 
										consultant_general 
										LEFT JOIN consultant_list ON consultant_list.username=consultant_general.username 
									WHERE
										consultant_list.username = consultant_general.username 
										AND IF((consultant_list.astatus = 'ACTIVE'), (consultant_list.astatus = 'ACTIVE' 
										AND adduser = 'Y' AND useracc != 'Y'), (consultant_list.astatus = 'RAccount'))
										AND deptid !='0' AND deptid IN (".$deptAccesSnoFO.") ";

				$query_result	= mysql_query($query_string, $db);

				$query_row		= mysql_fetch_row($query_result);

				$rec_count		= $query_row[0];

				if ($rec_count == '')
				$rec_count	= 0;
				
				$message_text .= "<tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/Admin/User_Mngmt/newcon.php?desk=consult' title='New Consultants'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='/BSOS/Admin/User_Mngmt/newcon.php?desk=consult' title='New Consultants'><font style='font-size:100%;'>New Consultants</font>
											</a>
										</div>
									</td><td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$rec_count</font>
										</div>
								</td>
									</tr>
									</table>	
									
									</tr>";
									
				//$message_text	.= "<a href='/BSOS/Admin/User_Mngmt/newcon.php?desk=consult'>New Consultants</a>: $rec_count<br/>";
			}

			//$image		= 'group.gif';
			$title		= '<span class="widgtitlecolorBlue"><i class="fa fa-user fa-lg"></i> Employees</span>';
			$toolbar	= '<a href=javascript:refreshWidgets("employees",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';
			$tooltip	= 'Employees';
			$message_text .= "</table>";
			$employess_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

			$employess_widget	.= $message_text;

			$employess_widget	.= $this->getWidgetFooter();

		} // end-of-if

		return $employess_widget; 
	}

	/*
	 * This function displays the ACCOUNTING WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$accounting_widget
	 */
	public function getAccountingWidget($username, $widget_id) {

		GLOBAL $db, $crmpref, $hrmpref, $adminpref, $accountingpref;

		$row_sys	= array($crmpref, $hrmpref, $adminpref, $accountingpref);
		
		$deptAccessObj = new departmentAccess();
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
		
		$message_text		= '';
		$accounting_widget	= '';

		if (strpos($row_sys[3], "+1+") || strpos($row_sys[3], "+2+") || strpos($row_sys[3], "+4+") || strpos($row_sys[3], "+11")) {

			if (strpos($row_sys[3], "+1+") || strpos($row_sys[3], "+2+")) {

				if ($row_sys[3] != "NO") {

					$message_text	= '';
				}

				if (strpos($row_sys[3], "+1+")) {
					$message_text .= "";

					// For Time sheets Count..
					$query_string	= "SELECT timesheet_hours.parid
										FROM par_timesheet ,timesheet_hours 
										LEFT JOIN emp_list e ON e.username=p.username 
										LEFT JOIN hrcon_compen ON (hrcon_compen.username = e.username)
										LEFT JOIN department ON (department.sno = hrcon_compen.dept)	
										WHERE timesheet_hours.status = 'ER' and par_timesheet.sno = timesheet_hours.parid
										AND department.sno !='0' AND department.sno IN(".$deptAccesSno.")
										GROUP BY timesheet_hours.username,timesheet_hours.parid";

					$query_result	= mysql_query($query_string, $db);

					$query_rows_ts		= mysql_num_rows($query_result);

					$message_text .= "<tr>
										<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/Accounting/Time_Mngmt/empfaxhis.php' target='_blank' title='New Time Sheet'>
											<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
												<tr>
													<td>
														<div valign='middle' style='width:152px;overflow:hidden;white-space:nowrap;'>
															<a href='/BSOS/Accounting/Time_Mngmt/empfaxhis.php' target='_blank' title='New Approvals'><font style='font-size:100%;'>New Time Sheet Approvals</font>
															</a>
														</div>
													</td>
													<td valign='middle'>&nbsp;</td>
													<td align='right'>
														<div valign='middle' style='width:142px;overflow:hidden;white-space:nowrap;'>&nbsp;
															<font style='font-size:100%;'>$query_rows_ts</font>
														</div>
										
													</td>
												</tr>
											</table>
										</td>
									</tr>";
				}

				if (strpos($row_sys[3], "+1+") && strpos($row_sys[3], "+2+")) {
					//$message_text	.= '<hr>';
				}

				if (strpos($row_sys[3], "+2+")) {
					$message_text .= "";

					// For Expenses Count..
					$query_string_ex	= "SELECT par_expense.sno										
										FROM par_expense,expense,emp_list
										LEFT JOIN hrcon_compen hrc ON hrc.username = emp_list.username
										LEFT JOIN department ON (department.sno = hrc.dept)
                                        WHERE par_expense.sno=expense.parid
                                        AND par_expense.astatus = 'ER'
                                        AND expense.status = 'ER'
                                        AND hrc.dept !='0' AND hrc.dept IN (".$deptAccesSno.")
										GROUP BY par_expense.username,expense.parid";

					$query_result_ex	= mysql_query($query_string_ex, $db);

					$query_rows_ex		= mysql_num_rows($query_result_ex);

					$message_text .= "<tr>
										<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/Accounting/Expense_Mngmt/expensereport.php' target='_blank' title='New Approvals'>
											<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
												<tr>
													<td>
														<div valign='middle' style='width:144px;overflow:hidden;white-space:nowrap;'>
															<a href='/BSOS/Accounting/Expense_Mngmt/expensereport.php' target='_blank' title='New Expenses Approvals'><font style='font-size:100%;'>New Expenses Approvals</font>
															</a>
														</div>
													</td>
													<td valign='middle'>&nbsp;</td>
													<td align='right'>
														<div valign='middle' style='width:140px;overflow:hidden;white-space:nowrap;'>&nbsp;
															<font style='font-size:100%;'>$query_rows_ex</font>
														</div>
													</td>
												</tr>
											</table>
										</td>		
									</tr>";
				}
			}

			//For Pending Invoices..
			$message_text .= "";

			$inv_query	= "SELECT inc.sno,ca.deptid FROM invoice inc
							LEFT JOIN Client_Accounts ca ON inc.client_id = ca.typeid
							WHERE inc.deliver = 'no' AND inc.status = 'ACTIVE' 
							AND ca.deptid !='0' AND ca.deptid IN (".$deptAccesSno.") 
							GROUP BY inc.sno";

			$inv_result	= mysql_query($inv_query, $db);

			$inv_count	= mysql_num_rows($inv_result);

			$message_text .= "<tr>
								<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/Accounting/Bill_Mngmt/invoicedeliver.php' target='_blank' title='Pending Invoices'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
										<tr>
											<td>
												<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
													<a href='/BSOS/Accounting/Bill_Mngmt/invoicedeliver.php' target='_blank' title='Pending Invoices'><font style='font-size:100%;'>Pending Invoices</font>
													</a>
												</div>
											</td>
											<td valign='middle'>&nbsp;</td>
											<td align='right'>
												<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
													<font style='font-size:100%;'>$inv_count</font>
												</div>								
											</td>
										</tr>
									</table>
								</td>	
							</tr>";

			if (strpos($row_sys[3], "+4+")) {

				if (strpos($row_sys[3], "+1+") || strpos($row_sys[3], "+2+")) {
					//$message_text	.= '<hr>';
				}
			}

			//For Assignments..
			if (strpos($row_sys[3], "+11")) {

				if (strpos($row_sys[3], "+1+") || strpos($row_sys[3], "+2+") || strpos($row_sys[3], "+4+")) {
					//$message_text	.= '<hr>';
				}

				//For New Assignments..
				$message_text .= "";

				//$asmt_query		= "SELECT
				//						empcon_jobs.sno
				//					FROM
				//						emp_list
				//						LEFT JOIN empcon_jobs ON emp_list.username = empcon_jobs.username
				//					WHERE
				//						emp_list.lstatus != 'DA' AND empcon_jobs.jtype != '' AND empcon_jobs.jotype!='0'
				//						AND empcon_jobs.assg_status = 'pending' AND empcon_jobs.modulename != 'my placement'
				//					GROUP BY
				//						empcon_jobs.sno";
				$deptque="select group_concat(sno) from department where sno !='0' AND sno IN (".$deptAccesSno.")";
				$deptres=mysql_query($deptque,$db);
				$deptrow=mysql_fetch_row($deptres);
				$deptnos = $deptrow[0];

				if($deptnos=="")
				$deptnos="0";

				$asmt_query 	= " SELECT   hrcon_jobs.sno
										FROM     emp_list LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
										LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username
									WHERE
										emp_list.lstatus != 'DA'
										AND hrcon_jobs.jtype != ''
										AND hrcon_jobs.jotype != '0'
										AND hrcon_jobs.ustatus = 'pending'
										AND hrcon_jobs.modulename != 'my placement'
										AND hrcon_compen.ustatus='active' 
										AND hrcon_compen.dept !='0'
										AND hrcon_compen.dept IN ($deptnos)
									GROUP BY hrcon_jobs.sno";

				$asmt_result	= mysql_query($asmt_query, $db);

				$asmt_count		= mysql_num_rows($asmt_result);
				
				$message_text .= 	"<tr>
										<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href=\"javascript:winopen5('/BSOS/Accounting/Assignment/newassignment.php');\" title='New Assignments'>
											<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
												<tr>
													<td>
														<div valign='middle' style='width:140px;overflow:hidden;white-space:nowrap;'>
															<a href=\"javascript:winopen5('/BSOS/Accounting/Assignment/newassignment.php');\" title='New Assignments'><font style='font-size:100%;'>New Assignments</font>
															</a>
														</div>
													</td>
													<td valign='middle'>&nbsp;</td>
													<td align='right'>
														<div valign='middle' style='width:140px;overflow:hidden;white-space:nowrap;'>&nbsp;
															<font style='font-size:100%;'>$asmt_count</font>
														</div>
													</td>
												</tr>
											</table>
										</td>			
									</tr>";
			}
			$toolbar	= '';
			$title		= '<span class="widgtitlecolorBlue"><i class="fa fa-calculator fa-lg"></i> Accounting</span>';
			$tooltip	= 'Accounting';
			$toolbar	= '<a href=javascript:refreshWidgets("accounting",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';			

			$accounting_widget  = $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);
			//For Front Office User Accounting widget displaying very weird, so from top removed table opening and closing and added here			
			$accounting_widget .= '<table cellpadding="0" cellspacing="0" border="0">';
			$accounting_widget .= $message_text;
			$accounting_widget .= '</table>';
			$accounting_widget .= $this->getWidgetFooter();
		}
		return $accounting_widget;
	}

	/*
	 * This function displays the ANNOUNCEMENTS WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	date	$cur_date
	 * param	integer	$widget_id
	 * return	string	$announcements_widget
	 */
	public function getAnnouncementsWidget($username, $cur_date, $widget_id) {

		GLOBAL $db, $empstatus, $collaborationpref;

		$announcements_text		= '';
		$announcements_widget	= '';

		$msg_query	= "SELECT
							sno, title
						FROM
							messageboard
						WHERE
							(FIND_IN_SET('$username', userlist) > 0 OR createdby = '$username')
							AND status NOT IN ('delete', 'backup') AND exprire_date >= '$cur_date'";

		$msg_result	= mysql_query($msg_query, $db);

		$msg_count	= 0;

		while ($msg_row = mysql_fetch_row($msg_result)) {

			if ($msg_count % 2 == 0)
			$class	= 'tr1bgcolor';
			else
			$class	= 'tr1bgcolor';

			$msg_count ++;

			$announcements_text	.= "<tr class=tr1bgcolor><td><a href=\"javascript:viewMess('".$msg_row[0]."');\">".html_tls_specialchars(stripslashes($msg_row[1]),ENT_QUOTES).'</a></td></tr>';
		}

		if ($msg_count == 0) {

			if ($empstatus == 'sp')
			$announcements_text	= "<tr><td><i>Post messages to a single person, a group or everybody in your company. To view all your announcements, click on 'Announcements'.</i></td></tr>";
			else
			$announcements_text	= '<tr><td><i>No items found</i></td></tr>';
		}

		$toolbar	= '';
		//$image		= 'announcements.gif';
		$tooltip	= 'Announcements';

		if ($empstatus == 'sp') {

			$toolbar	= '<a href=javascript:refreshWidgets("announcements",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';					
		}

		if (!chkUserPref($collaborationpref, '8')) {

			$title	= '<span class="widgtitlecolorBlue"><i class="fa fa-bullhorn fa-lg"></i> Announcements</span><a href="/BSOS/Collaboration/Mesboard/addmessage.php" > ( New ) </a>';

		} else {

			$title	= '<a href="/BSOS/Collaboration/Mesboard/mesboard.php" class="widgtitlecolorBlue"> <i class="fa fa-bullhorn fa-lg"></i> Announcements</a><a href="/BSOS/Collaboration/Mesboard/addmessage.php" > ( New ) </a>';
		}

		$announcements_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$announcements_widget	.= '<table cellpadding="0" cellspacing="0" border="0">';

		$announcements_widget	.= $announcements_text;

		$announcements_widget	.= '</table>';

		$announcements_widget	.= $this->getWidgetFooter();

		return $announcements_widget;
	}

	/*
	 * This function displays the COMPANY NEWS WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$companynews_widget
	 */
	public function getCompanyNewsWidget($username, $widget_id) {

		GLOBAL $db, $empstatus, $adminpref;

		$companynews_text	= '';
		$companynews_widget	= '';

		$news_query		= "SELECT
								serial_no, headline, main_headline
							FROM
								news_manage
							WHERE
								status != 'BP' AND display_news_edesk = 'Y'";

		$news_result	= mysql_query($news_query, $db);

		$news_count		= 0;

		$companynews_text	= '';

		while ($news_row = mysql_fetch_row($news_result)) {

			if ($news_count % 2 == 0)
			$class	= 'tr1bgcolor';
			else
			$class	= 'tr1bgcolor';

			$news_count ++;

			$companynews_text	.= "<tr class=tr1bgcolor><td><a href=javascript:viewNews('". $news_row[0] . "')>" . $news_row[1] . '</a></td></tr>';
		}

		if ($news_count == 0) {

			if ($empstatus == 'sp')
			$companynews_text	= "<tr><td><i>Create news articles and publish them for your users. You can also choose to publish News Items on to your web site. To see all your 'News' articles, click on 'Company News'.</i></td></tr>";
			else
			$companynews_text	= '<tr><td><i>No Items found</i></td></tr>';
		}

		//$image		= 'news.gif';
		$tooltip	= 'Company News';
		$toolbar	= '';

		if ($empstatus == 'sp') {

			$toolbar	= '<a href=javascript:refreshWidgets("companynews",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';				
		}

		if (!chkUserPref($adminpref, '1')) {

			$title	= '<span class="widgtitlecolorBlue"><i class="fa fa-building fa-lg"></i> Company News</span><a href="#" onClick="javascript:addNews();" > ( New )</a>'; 

		} else {

			$title	= '<a href="/BSOS/Admin/News_Mngmt/newsman.php" class="widgtitlecolorBlue"><i class="fa fa-building fa-lg"></i> Company News</a><a href="#" onClick="javascript:addNews();" > ( New )</a>';
		}

		$companynews_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$companynews_widget	.= '<table cellpadding="0" cellspacing="0" border="0">';

		$companynews_widget	.= $companynews_text;

		$companynews_widget	.= '</table>';

		$companynews_widget	.= $this->getWidgetFooter();

		return $companynews_widget;
	}

	/*
	 * This function displays the KNOWLEDGE CENTER WIDGET in the eDesk
	 *
	 * param	string	$username
	 * param	integer	$widget_id
	 * return	string	$knowledgecenter_widget
	 */
	public function getKnowledgeCenterWidget($username, $widget_id) {

		GLOBAL $db, $collaborationpref;

		$knowledgecenter_text	= '';
		$knowledgecenter_widget	= '';

		if (chkUserPref($collaborationpref, '9')) {

			$dep_query	= "SELECT
								department.sno, department.deptname
							FROM
								department
								LEFT JOIN hrcon_compen ON department.sno = hrcon_compen.dept 
							WHERE
								hrcon_compen.username = '$username' and ustatus = 'active'";

			$dep_result	= mysql_query($dep_query, $db);

			$dep_row	= mysql_fetch_array($dep_result);

			$dept_no	= $dep_row[0];

			$res_query	= "SELECT
								serial_no, title, category, type, status
							FROM
								resource_manage
							WHERE
								status = 'VIEW' AND FIND_IN_SET('$dept_no', department) > 0
							ORDER BY
								stime DESC
							LIMIT 0, 5";

			$res_result	= mysql_query($res_query, $db);

			while ($res_row = mysql_fetch_row($res_result)) {

				$knowledgecenter_text	.= "
					<tr class='tr1bgcolor'>
						<td height='21'>
							<a href=\"javascript:viewCont('" . $res_row[0] . "', '');\"> " . $res_row[1] . '</a>
						</td>
					</tr>';
			}

			$man_query	= "SELECT
								serial_no, title, category, type, status
							FROM
								resource_manage
							WHERE
								status = 'ANS' AND username = '$username'
							ORDER BY
								stime DESC
							LIMIT 0, 5";

			$man_result	= mysql_query($man_query, $db);

			while ($man_row = mysql_fetch_row($man_result)) {

				$knowledgecenter_text	.= "
					<tr>
						<td>
							<a href='/BSOS/Collaboration/Info/suggest.php?stat=edit&addr=" . $man_row[0] . "'>" . $man_row[1] . '</a>
						</td>
					</tr>';
			}

			if ($knowledgecenter_text == '') {

				$knowledgecenter_text	.= "
					<tr>
						<td>
							<i>Here you will see recent approved suggestions and answered FAQ's. To see all approved suggestions and answered FAQ's click on Knowledge Center.</i>
						</td>
					</tr>";
			}

			$view_query		= "SELECT
									COUNT(*)
								FROM
									resource_manage
								WHERE
									status NOT IN ('backup', 'VIEW', 'ANS') AND username = '$username'";

			$view_result	= mysql_query($view_query, $db);

			$view_row		= mysql_fetch_row($view_result);

			$view_count		= $view_row[0];

			if ($view_count == '')
			$view_count	= 0;

			$ans_query	= "SELECT
								COUNT(*)
							FROM
								resource_manage
							WHERE
								status IN ('ANS') AND username ='$username'";

			$ans_result	= mysql_query($ans_query, $db);

			$ans_row	= mysql_fetch_row($ans_result);

			$ans_count	= $ans_row[0];

			if ($ans_count == '')
			$ans_count	= 0;

			//$image		= 'knowledgecenter.gif';
			$tooltip	= 'Knowledge Center';
		
			$title		= "<a href='/BSOS/Collaboration/Info/resman.php' class='widgtitlecolorBlue'><i class='fa fa-graduation-cap fa-lg'></i> Knowledge Center</a><a href='javascript:void(0);' onClick=\"javascript:window.open('/BSOS/Collaboration/Info/ask.php', '', 'width=800px, height=365px, statusbar=no, menubar=no, scrollbars=yes, dependent=yes, resizable=yes');\"> ( New )</a>";
	
			$toolbar	= '<a href=javascript:refreshWidgets("knowledgecenter",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';

			$knowledgecenter_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);
			
			$knowledgecenter_widget .= "<table><tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/Collaboration/Info/mypending.php?desk=postings' title='New Postings'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='/BSOS/Collaboration/Info/mypending.php?desk=postings' title='New Postings'><font style='font-size:100%;'>New Postings</font>
											</a>
										</div>
										
									</td>
									
									<td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$view_count</font>
										</div>
									</td>
									</tr>
									</table>
									
									</tr>";
									
			$knowledgecenter_widget .= "<tr>
									<td colspan='3' class='edesktasks'><div class='edeskoverBg'><a href='/BSOS/Collaboration/Info/mypending.php?desk=postings' title='Answered Questions'>
									<table width='100%'  cellspacing='0'  cellpadding='0'  border='0'>
									<tr>
									<td>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>
											<a href='/BSOS/Collaboration/Info/mypending.php?desk=postings' title='Answered Questions'><font style='font-size:100%;'>Answered Questions</font>
											</a>
										</div>
										
									</td>
									
									<td valign='middle'>&nbsp;</td>
									<td align='right'>
										<div valign='middle' style='width:130px;overflow:hidden;white-space:nowrap;'>&nbsp;
											<font style='font-size:100%;'>$ans_count</font>
										</div>
									</td>
									</tr>
									</table>
									
									</tr></table>";
									
			$knowledgecenter_widget	.= "				
				
				<table cellpadding='0' cellspacing='0' border='0'>";
			
			$knowledgecenter_widget	.= $knowledgecenter_text;

			$knowledgecenter_widget	.= '</table>';

		} else {

			$toolbar	= '<a href=javascript:refreshWidgets("knowledgecenter",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';
			$image		= 'knowledgecenter.gif';
			$tooltip	= 'Knowledge Center';
			$title		= "Knowledge Center <a href='javascript:void(0);' onClick=\"javascript:window.open('/BSOS/Collaboration/Info/ask.php', '', 'width=800px, height=365px, statusbar=no, menubar=no, scrollbars=yes, dependent=yes, resizable=yes');\"> ( New )</a>";

			$knowledgecenter_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

			$knowledgecenter_widget	.= "
				<table cellpadding='0' cellspacing='0' border='0'>
					<tr>
						<td>
							<i>No approved suggestions and answered FAQ's are available.</i>
						</td>
					</tr>
				</table>";
		}

		$knowledgecenter_widget	.= $this->getWidgetFooter();

		return $knowledgecenter_widget;
	}

	/*
	 * This function displays the MESSAGES WIDGET in the eDesk
	 *
	 * param	string	$company_user
	 * param	date	$cur_date
	 * param	integer	$widget_id
	 * return	string	$messages_widget
	 */
	public function getMessagesWidget($company_user, $cur_date, $widget_id) {

		GLOBAL $maindb;
		GLOBAL $sess_usertype;

		$messages_text		= '';
		$messages_widget	= '';

		$sys_query	= "SELECT
							sysalerts.sno, sysalerts.title
						FROM
							sysalerts
							LEFT JOIN capp_info ON (FIND_IN_SET(capp_info.sno, sysalerts.userlist) > 0)
						WHERE
							capp_info.comp_id = '$company_user' AND sysalerts.sno != 'NULL'
							AND sysalerts.status != 'backup' AND exprire_date >= '$cur_date'";

		$sys_result	= mysql_query($sys_query, $maindb);

		$sys_count	= 0;

		while ($sys_row = mysql_fetch_row($sys_result)) {

			$sys_count ++;

			$messages_text	.= "
				<tr class='tr1bgcolor'>
					<td>
						<a href=\"javascript:viewAlert('" . $sys_row[0] . "');\">" . $sys_row[1] . '</a>
					</td>
				</tr>';
		}

		mysql_close($maindb);

		if ($sys_count == 0) {

			$messages_text	= '<i>There are currently no new messages from Akken.</i>';
		}

		if($sess_usertype!="SSU"){
			//$image	= 'akkenmessages-edesk.gif';
			$tooltip	= 'Messages from Akken';
			$title		= '<span class="widgtitlecolorBlue"><i class="fa fa-envelope-o fa-lg"></i> Messages from Akken </span>';
			$toolbar	= '<a href=javascript:refreshWidgets("messages",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';

			$messages_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

			$messages_widget	.= '<table cellpadding="0" cellspacing="0" border="0">';

			$messages_widget	.= $messages_text;

			$messages_widget	.= '</table>';
		}
		return $messages_widget;
	}

	/*
	 * This function builds the EVENT WIDGET HEADER
	 *
	 * param	string	$image
	 * param	string	$title
	 * param	string	$toolbar
	 * param	string	$tooltip
	 * param	integer	$widget_id
	 * return	string	$html_header
	 */
	public function getEventWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id) {

		$html_header	= "
			<div class='box-wrapper-schedule portlet sortable' id='$widget_id'>
				<div class='box-top1 portlet-header ui-state-default'>
					<div class='toolbar'>
						$toolbar
					</div>";

		if (isset($image)) {

			$html_header	.= "
				<img src='/BSOS/images/icons/$image' height='20' width='20' alt='$tooltip' align='bottom' style='position:relative;left:-1px;'>";
		}

		$html_header	.= "
					<span style='position:absolute;' class='widgtitlecolorBlue'>$title</span>
				</div>
				<div class='box-content portlet-content'><div id='widgetgrid_".$widget_id."'>
					<table border='0' cellpadding='0' cellspacing='0'>
			";
		
		return $html_header;
	}

	/*
	 * This function builds the EVENT WIDGET FOOTER
	 *
	 * return	string	$html_footer
	 */
	public function getEventWidgetFooter() {

		$html_footer	= '
				</table>
			</div> </div>
		</div> ';

		return $html_footer;
	}

	/*
	 * This function builds the WIDGET HEADER
	 *
	 * param	string	$image
	 * param	string	$title
	 * param	string	$toolbar
	 * param	string	$tooltip
	 * param	integer	$widget_id
	 * return	string	$html_header
	 */
	public function getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id) {

		$html_header	= "
			<div class='box-wrapper portlet sortable' id='$widget_id'>
				<div class='box-top portlet-header ui-state-default'>
					<div class='toolbar'>
						$toolbar
					</div>";
		if(strpos($title, 'Job Opportunities') !== false || strpos($title, 'My Referral Count') !== false){
		$html_header .= "<div class='edeskSignupJA'> <i class='fa fa-envelope fa-lg'></i>
						<a id='acTalentPoolWidget' class='refJobSignAlerts' href='javascript:ess_signup();' data-title='SIGN UP for new Job Alerts!!' data-width='350' data-height='726'>SIGN UP for new Job Alerts!!</a></div>";
		}

		if (isset($image) && $image!='') {

			$html_header	.= "<img src='/BSOS/images/icons/$image' height='16' width='16' alt='$tooltip' align='bottom' style='margin-left:4px;'>";
		}

		$html_header	.= "<span class='widgtitlecolorBlue' style='position:absolute; '>$title";

		if (strip_tags($title) == 'eCampaigns') {

			//$html_header	.= ' <span class="unreadmail">( 20 )</span>';
		}

		if (strip_tags($title) == 'eCampaign' || strip_tags($title) == 'Submissions' || strip_tags($title) == 'Postings') {

			//$html_header	.= ' <span class="unreadmail">( 20 )</span>';
		}
				
		$html_header	.= '</span>
				</div>
				<div class="box-content portlet-content">';
				
		$html_header	.= "<div id='widgetgrid_".$widget_id."'>";
		
		return $html_header;
	}

	/*
	 * This function builds the WIDGET FOOTER
	 *
	 * param	string	$location
	 * param	string	$viewall_link
	 * return	string	$html_footer
	 */
	public function getWidgetFooter($location = '', $viewall_link = '') {

		if ($location == 'eCampaign' || $location == 'Submissions' || $location == 'Posting' || $location == 'Call-Em-All' || $location == 'TextUs') {

			if ($viewall_link != '') {

				$html_footer	= "
					<div class='viewalledesk'>
						<a href='$viewall_link'>View all</a>&nbsp;
					</div>";
			}
		}

		$html_footer	.= '
				</div>
			</div> </div>';

		return $html_footer;
	}

	/*
	 * This function adds the default widgets to the User based on the User Type
	 *
	 * param	string	$username
	 */
	public function addDefaultWidgets($username) {
		GLOBAL $sess_usertype;
		GLOBAL $db;

		// Widget id's from edesk_widgets table
		$res_allowed_widgets = $this->getAllowedWidgets($sess_usertype);

		// User Preference Widget name's from edesk_widgets table		
		$pref_widgets = $this->getPreferenceWidgets($sess_usertype);
		
		while ($rec_allowed_widgets = mysql_fetch_object($res_allowed_widgets))
		{
			$flag	= true;
			
			if (in_array($rec_allowed_widgets->widget_name, $pref_widgets)) {
			
				$flag	= $this->checkUserPreferenceExistsForWidget($rec_allowed_widgets->widget_name);
				if(!$flag)
				{
					$edesk_options_tobe_deleted .= $rec_allowed_widgets->widget_id.",";
				}
			}
			
			if($flag)
			{
				if($this->checkIneDeskOptions($username, $rec_allowed_widgets->widget_id))
				{
                                    // Ess user interface changes-Added the following lines of code for displaying the edesk widjets for Ess users in a predefined positions and columns as per ESS user enhacement scope
                                    if($sess_usertype=='SSU'){
                                            switch ($rec_allowed_widgets->widget_name) {
                                                case 'Company News':
                                                        $position_id=1;
                                                        $col_id='col1';
                                                break;
                                                case 'Announcements':
                                                        $position_id=2;
                                                        $col_id='col1';
                                                break;
                                                case 'Messages from Akken':
                                                        $position_id=3;
                                                         $col_id='col1';
                                                break;
                                                case 'Knowledge Center':
                                                        $position_id=4;
                                                         $col_id='col1';
                                                break;
                                                case 'eDesk Calendar':
                                                        $position_id=5;
                                                        $col_id='col1';
                                                break;
                                                case 'Job Opportunities':
                                                        $position_id=$rec_allowed_widgets->position_id;
                                                        $col_id=$rec_allowed_widgets->column_name;
                                                break;
                                        
                                            }
                                            $insert_query = "INSERT INTO eDesk_options (col_id, position_id, widget_id, user_id, collapse_status, closed_status) VALUES ('".$col_id."', '".$position_id."', '".$rec_allowed_widgets->widget_id."', '".$username."', '".$rec_allowed_widgets->collapse_status."', '".$rec_allowed_widgets->closed_status."')";
                                            mysql_query($insert_query, $db);    
                                    }else {
                                            $insert_query = "INSERT INTO eDesk_options (col_id, position_id, widget_id, user_id, collapse_status, closed_status) VALUES ('$rec_allowed_widgets->column_name', $rec_allowed_widgets->position_id, $rec_allowed_widgets->widget_id, '$username', '$rec_allowed_widgets->collapse_status', '$rec_allowed_widgets->closed_status')";
                                            mysql_query($insert_query, $db);   
                                    }
					
				}
			}
		}
		if(!empty($edesk_options_tobe_deleted))
		{
			$del_edesk_options = "DELETE FROM eDesk_options WHERE widget_id IN (".trim($edesk_options_tobe_deleted,",").") AND user_id = ".$username;			
			mysql_query($del_edesk_options, $db);
		}
		
		$que="update sysuser set refresh_edeskwidgets = 'N' where username='".$username."'";
		mysql_query($que,$db);
	}
	
	/*
	 * This function checks whether particular widget has been enabled in User Preferences
	 *
	 * param	string	$widget_name
	 * return	boolean	$bFlag
	 */
	private function checkUserPreferenceExistsForWidget($widget_name) {

		GLOBAL $collaborationpref, $crmpref, $accountingpref, $hrmpref, $adminpref;

		$bFlag		= true;
		$row_sys	= array($crmpref, $hrmpref, $adminpref, $accountingpref);

		switch ($widget_name) {

			case 'eCampaigns':	if (!chkUserPref($crmpref, '1')) {

									$bFlag	= false;
								}
								break;

			case 'Submissions':	if (!chkUserPref($crmpref, '4')) {

									$bFlag	= false;
								}
								break;

			case 'Postings':	if (!chkUserPref($crmpref, '5')) {

									$bFlag	= false;
								}
								break;

			case 'Employees':	if (!chkUserPref($adminpref, '5') && !chkUserPref($hrmpref, '3') && !chkUserPref($hrmpref, '5')) {

									$bFlag	= false;
								}
								break;

			case 'Accounting':	if (!chkUserPref($accountingpref, '1') && !chkUserPref($accountingpref, '2') && !chkUserPref($accountingpref, '4') && !chkUserPref($accountingpref, '11')) {

									$bFlag	= false;
								}
								break;
			case 'Outlook Email':	if (!chkUserPref($collaborationpref, '11')) {

									$bFlag	= false;
								}
								break;
			case 'E-mail':	if (!chkUserPref($collaborationpref, '1')) {

									$bFlag	= false;
								}
								break;
			case 'Paperless On-Boarding Statuses':	if (!chkUserPref($adminpref, '36')) {

									$bFlag	= false;
								}
								break;
                                                                
                        case 'getEverifyCaseUpdates':	if (!chkUserPref($adminpref, '63') && EVERIFY_ACCESS=='Y') {

									$bFlag	= false;
								}
								break;
		}

		return $bFlag;
	}

	/*
	 * This function delete the CURRENT WIDGETS mapped to the user
	 *
	 * param	string	$username
	 */
	public function deleteCurrentWidgets($username) {

		GLOBAL $db;

		$delete_query	= "DELETE FROM eDesk_options WHERE user_id='$username'";

		mysql_query($delete_query, $db);
	}
	
	public function getAllowedWidgets($usertype)
	{
		GLOBAL $db;
		
		$sel_allowed_widgets = "select widget_id, widget_name, column_name, module_name, preference_id, position_id, collapse_status, closed_status from edesk_widgets where ".strtolower($usertype)." = 'Y' AND active=1 ORDER BY widget_id ";
		$res_allowed_widgets = mysql_query($sel_allowed_widgets, $db);
		
		return $res_allowed_widgets;
	}
	
	public function getPreferenceWidgets($usertype)
	{
		GLOBAL $db;
		
		$sel_preference_widgets = "select GROUP_CONCAT(widget_name) as widget_names from edesk_widgets where ".strtolower($usertype)." = 'Y' AND active=1 AND preference_id = 1 ORDER BY widget_id ";		
		$res_preference_widgets = mysql_query($sel_preference_widgets, $db);
		$rec_preference_widgets = mysql_fetch_array($res_preference_widgets);
		$pref_vals = explode(",",$rec_preference_widgets['widget_names']);
		
		return $pref_vals;
	}
	
	public function checkIneDeskOptions($username, $widget_id)
	{
		GLOBAL $db;
		
		$sel_edesk_options = "SELECT count(1) AS ondesk FROM eDesk_options WHERE user_id = ".$username." AND widget_id = ".$widget_id;	
		$res_edesk_options = mysql_query($sel_edesk_options, $db);
		$rec_edesk_options = mysql_fetch_array($res_edesk_options);
		
		if($rec_edesk_options['ondesk'] == 0)
			return true;
		else
			return false;
	}
        
        // Ess user interface changes :New job Listings widjet display for Ess users
           public function getEssJobPostingsWidjet($username,$widget_id) {
               if(REFERRAL_ITM_ENABLED=='Y'){
		global $db,$myreferencespref,$sess_usertype;
                $allowed_usertype = explode(',',IRM_ALLUSERS_ALLOWED_TYPES);  
                ///BSOS/MyReferences/ibreferences.php?current_page=Shared
                $referral_page = (in_array($sess_usertype,$allowed_usertype)) ? "/BSOS/HRM/IBReferral_Mngmt/IBReferral_Mngmt_Home.php" : "";
               
                        $widjetNameQuery = "select widget_name from edesk_widgets where widget_id='".$widget_id."' and active='1'";
                        $widjetNameRespo = mysql_query($widjetNameQuery, $db);	
                        $widjetNameRow = mysql_fetch_row($widjetNameRespo);
                        // query to check job postings exists or not 
                        
                        $expiredExpr = "";
                        //these wp_locations are location obatining from seo_webiste locations which is existing  in jobBoards 
                        $wp_locations = "";  //(need to confirm if we need to use or not in ESS user job postings)hence for now declared variable as empty

                        $prefQuery = "SELECT search_chk,if(search_chk = 'Y',search_cnt,1) as csearch_cnt,cand_acc_chk,expire_chk,expire_days,show_search_chk FROM jobposting_pref";
                        $prefRes = mysql_query($prefQuery, $db);
                        $prefInfo = mysql_fetch_row($prefRes);
                        if (mysql_num_rows($prefRes) > 0) {

                            $exipredChk = $prefInfo[3];
                            $expiredCount = $prefInfo[4];
                        }
                        if ($exipredChk == 'Y' && $expiredCount != "")
                            $expiredExpr = " AND IF(pd.status='P',DATE_ADD(pd.posted_date,INTERVAL " . $expiredCount . " DAY),DATE_ADD(pd.refresh_date,INTERVAL " . $expiredCount . " DAY)) >= now()";

                        $sqlManage = "SELECT GROUP_CONCAT(sno) FROM manage WHERE name IN ('Closed', 'Cancelled', 'Filled') AND type='jostatus' AND status='Y'";
                        $resManage = mysql_query($sqlManage, $db);
                        $rowManage = mysql_fetch_row($resManage);

                        $orderString = " ORDER BY IF(pd.refresh_date='0000-00-00 00:00:00',pd.posted_date,pd.refresh_date) DESC";

                        $i = 0;

                        if ($wp_locations == ""){
                            $jobsquery = "  SELECT pd.sno,pd.postitle,pd.joblocation,mg.custom_name,DATE_FORMAT(IF(pd.status='P',pd.posted_date,IF(pd.refresh_date='0000-00-00 00:00:00',pd.posted_date,pd.refresh_date)),'%m/%d/%Y') AS posted_date 
                                                FROM api_jobs AS pd  
                                                LEFT JOIN manage AS mg ON pd.postype = mg.sno
                                                WHERE pd.status IN ('P','R') AND pd.posstatus NOT IN ('" . str_replace(",", "','",$rowManage[0])."') ".$expiredExpr.$orderString;
                        }else{
                            $jobsquery = "SELECT pd.sno,pd.postitle,pd.joblocation,mg.custom_name,DATE_FORMAT(IF(pd.status='P',pd.posted_date,IF(pd.refresh_date='0000-00-00 00:00:00',pd.posted_date,pd.refresh_date)),'%m/%d/%Y') AS posted_date FROM api_jobs AS pd 
                                                LEFT JOIN manage AS mg ON pd.postype = mg.sno 
                                                LEFT JOIN hotjobs hj ON hj.sno = pd.req_id 
                                                LEFT JOIN posdesc pdo ON pdo.posid = hj.req_id 
                                                LEFT JOIN department ON department.sno = pdo.deptid 
                                                LEFT JOIN contact_manage ON contact_manage.serial_no = department.loc_id 
                                                WHERE pd.status IN('P','R') AND pd.posstatus NOT IN ('" . str_replace(",", "','",$rowManage[0])."') AND contact_manage.serial_no IN ('" . str_replace(",", "','", $wp_locations) . "') " . $expiredExpr . $orderString;
			}
                        $jobsres = mysql_query($jobsquery, $db);
                        $postings_count = mysql_num_rows($jobsres);
                       
                        $toolbar	= '';
			$image	        = '';
	$title='';
	if (ESS_USER_MYREFERENCES == "ENABLE"){
		$title	.= '<span class="widgtitlecolorBlue" ><i class="fa fa-bullhorn fa-lg"></i>&nbsp;My Referral Count</span>';
	}
	else{
		$title	.= '<span class="widgtitlecolorBlue"><i class="fa fa-suitcase fa-lg"></i> Job Opportunities</span>';
		
	}
	$title .= '';
			$tooltip	= '';
						
                        $postings_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);
                        if ($postings_count == 0) {

				$message_text	= '<i>No Jobs Posted</i>';
                                $postings_widget	.= $message_text;

                        }else{
                        //Jobs Shared count
	$jobs_shared =0;
	$shared_count = "select count(1) from shared_data where username='".$username."'";
	$shared_res = mysql_query($shared_count, $db);	
	$shared_row = mysql_fetch_row($shared_res);
	if($shared_row[0]){
		$jobs_shared = $shared_row[0];
	}
	//Referrals Submitted count
	$submitted_referrals =0;
	$submitted_count = "select count(1) from cand_refer where cand_refer.status='active' and username='".$username."' ";
	$submitted_res = mysql_query($submitted_count, $db);	
	$submitted_row = mysql_fetch_row($submitted_res);
	if($submitted_row[0]){
		$submitted_referrals = $submitted_row[0];
	}
	//Referrals Qualified count
	$qualified_referrals =0;
	 $qualified_count = "select count(DISTINCT candidate_list.sno) from cand_refer 
	LEFT JOIN candidate_list ON candidate_list.candid = cand_refer.ref_id AND candidate_list.candid!=''
	LEFT JOIN resume_status ON resume_status.res_id = candidate_list.sno AND cand_refer.req_id = resume_status.req_id 
	LEFT JOIN applicants ON applicants.username=cand_refer.ref_id  AND FIND_IN_SET(cand_refer.req_id, applicants.jobtitle)>0
	WHERE cand_refer.status='active' and cand_refer.username='".$username."'  and ( (resume_status.pstatus IN ('P','S')) OR  ((applicants.serial_no IS NOT NULL or cand_refer.user_id IS NOT NULL) and applicants.astatus NOT IN ( 'hire',  'HREJ',  'backup',  'HRF' )))";
	$qualified_res = mysql_query($qualified_count, $db);	
	$qualified_row = mysql_fetch_row($qualified_res);
	if(mysql_num_rows($qualified_res)){
		$qualified_referrals = $qualified_row[0];
	}
//	$qualified_count1 = "select count(1) from cand_refer 
//						LEFT JOIN emp_list ON cand_refer.ref_id = emp_list.username
//						LEFT JOIN candidate_list  ON emp_list.sno = SUBSTRING(candidate_list.candid, 4, 2)  AND candidate_list.candid!='' 
//						LEFT JOIN resume_status ON resume_status.res_id = candidate_list.sno AND cand_refer.req_id = resume_status.req_id 
//						LEFT JOIN applicants ON applicants.username=cand_refer.ref_id AND FIND_IN_SET(cand_refer.req_id, applicants.jobtitle)>0 
//						WHERE cand_refer.status='active' and cand_refer.username='".$username."' and (resume_status.pstatus='S' 
//						or ((applicants.serial_no IS NOT NULL or cand_refer.user_id IS NOT NULL) and applicants.astatus NOT IN 
//						( 'hire', 'HREJ', 'backup', 'HRF' ))) and cand_refer.sno NOT IN (select cand_refer.sno from cand_refer 
//	LEFT JOIN candidate_list ON candidate_list.candid = cand_refer.ref_id AND candidate_list.candid!=''
//	LEFT JOIN resume_status ON resume_status.res_id = candidate_list.sno AND cand_refer.req_id = resume_status.req_id 
//	LEFT JOIN applicants ON applicants.username=cand_refer.ref_id  AND FIND_IN_SET(cand_refer.req_id, applicants.jobtitle)>0
//	WHERE cand_refer.status='active' and cand_refer.username='".$username."'  and (resume_status.pstatus='S' or  ((applicants.serial_no IS NOT NULL or cand_refer.user_id IS NOT NULL) and applicants.astatus NOT IN ( 'hire',  'HREJ',  'backup',  'HRF' ))))";
//	$qualified_res1 = mysql_query($qualified_count1, $db);	
//	$qualified_row1 = mysql_fetch_row($qualified_res1);
//	if(mysql_num_rows($qualified_res1)){
//		$qualified_referrals1 = $qualified_row1[0];
//	}
        $qualified_referrals1=0;
	$final_qualified_referrals = $qualified_referrals + $qualified_referrals1;
	//Referrals Hired count
	$hired_referrals =0;
	 $hired_count = "select count(1) from cand_refer 
						LEFT JOIN emp_list ON emp_list.sno = cand_refer.emp_id 
						LEFT JOIN applicants ON applicants.username = cand_refer.ref_id 
						LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username 
						WHERE   cand_refer.status='active' and cand_refer.username='".$username."' and (hrcon_general.ustatus='active' and emp_list.lstatus not in ('DA','INACTIVE') 
						and emp_list.sno IS NOT NULL 
						AND ((FIND_IN_SET(cand_refer.req_id, emp_list.cur_project)>0 or FIND_IN_SET(cand_refer.req_id, applicants.jobtitle)>0) or (applicants.jobtitle ='' and emp_list.cur_project=''))) ";
	$hired_res = mysql_query($hired_count, $db);	
	$hired_row = mysql_fetch_row($hired_res);
	if($hired_row[0]){
		$hired_referrals = $hired_row[0];
	}
        if(REFERRAL_BONUS_MANAGE=='ENABLED'){
            //Referrals Bonus Earned 
            $referrals_Bonus_earned =0;
            $bonus_sum_query = "select SUM(bonus_amount) from cand_refer WHERE cand_refer.status='active' and cand_refer.username='".$username."' and referral_status='".getManageSno('Paid','referral_status')."'";
            $bonus_sum_res = mysql_query($bonus_sum_query, $db);	
            $bonus_sum_row = mysql_fetch_row($bonus_sum_res);
            if($bonus_sum_row[0]){
                    $referrals_Bonus_earned = $bonus_sum_row[0];
            }
        }
               
                        
                        if (ESS_USER_MYREFERENCES == "ENABLE")
                        {
                                $postings_widget.=' <div  class="newEdeskRef">
                                                        <table class="display" width="100%" cellspacing="0">
                                                        <tr>
										<td id="jobs_shared">Jobs Shared';
			
				$postings_widget.='<p style="text-decoration:underline;"><a href="/BSOS/MySharedJobs/mysharedjobs.php">'.$jobs_shared.'</a></p>';
			
			
			$postings_widget.='</td><td>Referrals Generated';
			if(ESS_USER_MYREFERENCES == "DISABLE"){
				$postings_widget.='<p id="referralCount">'.$submitted_referrals.'</p>';
			}else{
				
				$postings_widget.='<p id="referralCount" style="text-decoration:underline;"><a href="/BSOS/MyReferences/myreferences.php">'.$submitted_referrals.'</a></p>';
			}
			$postings_widget.='</td>
                                                            <td>Referrals Qualified<p>'.$final_qualified_referrals.'</p></td>
                                                            <td>Referrals Hired<p>'.$hired_referrals.'</p></td>';
                                                        if(REFERRAL_BONUS_MANAGE=='ENABLED'){
                                                             $postings_widget.= '<td>Referral Bonus Earned ($)<p>'.$referrals_Bonus_earned.'</p></td>';
                                                            } 
                                                       $postings_widget.= '</tr>
                                                        </table></div>';
                        }
		if(ESS_USER_MYREFERENCES == "ENABLE"){
			$postings_widget.='<div class="refJobOppcls"><span class="widgtitlecolorBlue"><i class="fa fa-suitcase fa-lg"></i> Job Opportunities</span></div>';
		}
                            $postings_widget.='<div class="newEdeskSearchBoxBlk">
                                               <span><input type="text" id="ac_keyword" class="essgridsearchboxClr" title="" name="keyword" onkeydown="return searchEssJobs(event)" placeholder="Job Title, Skills, etc..." value="" /></span>
                                                <span><input type="text" id="ac_location" class="essgridsearchboxClr" title="Location" onkeydown="return searchEssJobs(event)" name="location"  placeholder="Location" value="" /></span>
                                                <span><label for="search"><button type="button" onclick="searchEssJobPost(this)" title="Go" id="acjbbtnactiv">Search</button></label></span></div>';
		$postings_widget.='<div class="newEdeskContent" style="padding:0px"><table id="essjobgrid" class="display" width="100%" cellspacing="0">';
                         $addReferralBonus ='';
                         $addReferralBonusTHead='';
                        if(REFERRAL_BONUS_MANAGE=='ENABLED'){
                             $addReferralBonus = '<th style="width:65px"; class="referralBonusWidth">Referral Bonus($)</th>';
                             $addReferralBonusTHead ='<th></th>';
                         }
                        if (ESS_USER_MYREFERENCES == "ENABLE")
                                {
			$postings_widget.='<thead><tr><th class="JobTitleWidth">Job Title</th>'.$addReferralBonus.'<th class="LocationWidth">Location</th><th class="Jobtypewidth">Job Type</th><th class="sorting_disabled ApplyWidth"></th><th class="sorting_disabled RefSocialWidth">Jobs Share</th><th class="sorting_disabled ReferWidth"></th></tr></thead><tfoot><tr><th></th><th></th><th></th><th></th><th></th><th></th>'.$addReferralBonusTHead.'</tr></tfoot>';     
                                }else {
			$postings_widget.='<thead><tr><th class="JobTitleWidth">Job Title</th>'.$addReferralBonus.'<th class="LocationWidth">Location</th><th class="Jobtypewidth">Job Type</th><th class="sorting_disabled ApplyWidth"></th></tr></thead><tfoot><tr><th></th><th></th><th></th><th></th>'.$addReferralBonusTHead.'</tr></tfoot>'; 
                                }                                   
                                           $postings_widget.='</table></div>';
                        }  
                        
                        return $postings_widget;
                
                } 
           }
    /* Job/Temp Planning Enhancement START */       
    public function getJobTempPlanning($username, $widget_id)
	{
		GLOBAL $db;
		
		$deptAccessObj = new departmentAccess();
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

		$dque="select group_concat(sno) from department where sno !='0' AND sno IN (".$deptAccesSno.")";
		$dres=mysql_query($dque,$db);
		$drow=mysql_fetch_row($dres);
		$deptnos = $drow[0];

		if($deptnos=="")
			$deptnos="0";

		$dateArray = array();
		$today = time();
		$wday = date('w', $today);   
		$datemon = date('m/d/Y', $today - ($wday - 1)*86400);
		array_push($dateArray, $datemon);
		$datetue = date('m/d/Y', $today - ($wday - 2)*86400);
		array_push($dateArray, $datetue);
		$datewed = date('m/d/Y', $today - ($wday - 3)*86400);
		array_push($dateArray, $datewed);
		$datethu = date('m/d/Y', $today - ($wday - 4)*86400);
		array_push($dateArray, $datethu);
		$datefri = date('m/d/Y', $today - ($wday - 5)*86400);
		array_push($dateArray, $datefri);
		$datesat = date('m/d/Y', $today - ($wday - 6)*86400);
		array_push($dateArray, $datesat);
		$datesun = date('m/d/Y', $today - ($wday - 7)*86400);
		array_push($dateArray, $datesun);

		$dayAry = array("MON","TUE","WED","THU","FRI","SAT","SUN");
		$dayStr = " ";
		foreach ($dayAry as $key => $dayvalue) {
			$dateVal = $dateArray[$key];
			$dateary = explode("/", $dateVal);
			$assignCountAry = $this->getStartEndDateAssignCount($dateVal,$deptnos);
			$dayStr.= "<td>
							<div class='mainACcls'>
								<div id='dayWidjet'> 
									<div id='dayname'> 
										".$dayvalue."
										<div></div>
										".$dateary[1]."
									</div> 
									<div id='ACcount'>
										<span style='padding:1px;'><a href='javascript:void(0);' onClick=\"javascript:window.open('/BSOS/Accounting/Assignment/manageassign.php?fromModule=jobtempPlanning&status=Active&sdate=".$dateVal."', '_blank');\"><span style='color:green;cursor:pointer;'>".$assignCountAry[0]."</span></a></span>
										/
										<span style='padding:1px;'><a href='javascript:void(0);' onClick=\"javascript:window.open('/BSOS/Accounting/Assignment/manageassign.php?fromModule=jobtempPlanning&status=Active&edate=".$dateVal."', '_blank');\"><span style='color:red;cursor:pointer;'>".$assignCountAry[1]."</span></a></span>
									</div>
								</div>
							</div>
						</td>";
		}

		$toolbar	= '<a href=javascript:refreshWidgets("JobTempPlanning",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';
		$image		= 'knowledgecenter.gif';
		$tooltip	= 'Job/Temp Planning';
		$title		= "Job/Temp Planning <a href='javascript:void(0);' onClick=\"javascript:openJobTempPlanningAdvSrchWin();\"> ( Adv Search )</a>";

		$jobtempplanning_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$jobtempplanning_widget	.= "
				<table cellpadding='0' cellspacing='0' border='0'>
					<tr>
						<td>
						<table cellpadding='0' cellspacing='0' border='0' class='jobTmpCls'>
						<tr>
							".$dayStr."
						</tr>
				</table>
						</td>
					</tr>
				</table>";	

		$jobtempplanning_widget	.= $this->getWidgetFooter();

		return $jobtempplanning_widget;
	}

	public function getMyPlacements($username, $widget_id)
	{
		GLOBAL $db;
		
		$deptAccessObj = new departmentAccess();
		$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

		$dque="select group_concat(sno) from department where sno !='0' AND sno IN(".$deptAccesSno.") ";
		$dres=mysql_query($dque,$db);
		$drow=mysql_fetch_row($dres);
		$deptnos = $drow[0];

		if($deptnos=="")
			$deptnos="0";

		$dateArray = array();
		$today = time();
		$wday = date('w', $today);   
		$datemon = date('m/d/Y', $today - ($wday - 1)*86400);
		array_push($dateArray, $datemon);
		$datetue = date('m/d/Y', $today - ($wday - 2)*86400);
		array_push($dateArray, $datetue);
		$datewed = date('m/d/Y', $today - ($wday - 3)*86400);
		array_push($dateArray, $datewed);
		$datethu = date('m/d/Y', $today - ($wday - 4)*86400);
		array_push($dateArray, $datethu);
		$datefri = date('m/d/Y', $today - ($wday - 5)*86400);
		array_push($dateArray, $datefri);
		$datesat = date('m/d/Y', $today - ($wday - 6)*86400);
		array_push($dateArray, $datesat);
		$datesun = date('m/d/Y', $today - ($wday - 7)*86400);
		array_push($dateArray, $datesun);

		$dayAry = array("MON","TUE","WED","THU","FRI","SAT","SUN");
		$dayStr = " ";
		foreach ($dayAry as $key => $dayvalue) {
			$dateVal = $dateArray[$key];
			$dateary = explode("/", $dateVal);
			$dateSrchVal = str_replace("-", "/", $dateArray[$key]);
			$assignCountAry = $this->getStartEndDateAssignCount($dateVal,$deptnos);
			$dayStr.= "<td>
							<div class='mainACcls'>
								<div id='dayWidjet'> 
									<div id='dayname'> 
										".$dayvalue."
										<div></div>
										".$dateary[1]."
									</div> 
									<div id='ACcount'>
										<span style='padding:1px;'><a href='javascript:void(0);' onClick=\"javascript:viewMyPlaceAdvSrchWin('window','".$dateSrchVal."','sdate');\"><span style='color:green;cursor:pointer;'>".$assignCountAry[0]."</span></a></span>
										/
										<span style='padding:1px;'><a href='javascript:void(0);' onClick=\"javascript:viewMyPlaceAdvSrchWin('window','".$dateSrchVal."','edate');\"><span style='color:red;cursor:pointer;'>".$assignCountAry[1]."</span></a></span>
									</div>
								</div>
							</div>
						</td>";
		}

		$toolbar	= '<a href=javascript:refreshWidgets("MyPlacements",'.$widget_id.')><i class="fa fa-repeat fa-lg"></i></a>';
		$image		= 'knowledgecenter.gif';
		$tooltip	= 'My Placements';
		$title		= "My Placements <a href='javascript:void(0);' onClick=\"javascript:openMyPlacementAdvSrchWin();\"> ( Adv Search )</a>";

		$myplacements_widget	= $this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);

		$myplacements_widget	.= "
				<table cellpadding='0' cellspacing='0' border='0'>
					<tr>
						<td>
						<table cellpadding='0' cellspacing='0' border='0' class='jobTmpCls'>
						<tr>
							".$dayStr."
						</tr>
				</table>
						</td>
					</tr>
				</table>";	

		$myplacements_widget	.= $this->getWidgetFooter();

		return $myplacements_widget;
	}

	public function getStartEndDateAssignCount($dateval='',$deptnos='0')
	{
		global $db;
		$output = array(0=>0,1=>0);
		$select = "SELECT COUNT(hj.sno) AS StartEndDateCount
					FROM hrcon_jobs hj 
					LEFT JOIN emp_list ON emp_list.username = hj.username
					LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username 
					WHERE  hj.ustatus IN('active') AND
					emp_list.lstatus != 'DA' 
					AND hrcon_compen.ustatus='active' AND hrcon_compen.dept IN (".$deptnos.")
					AND hj.jtype!='' AND hj.jotype!='0' 
					AND (DATE_FORMAT(STR_TO_DATE(IF(hj.s_date='0-0-0','00-00-0000',hj.s_date),'%m-%d-%Y'),'%m/%d/%Y') LIKE '%".$dateval."%' ) 
					UNION ALL
					SELECT COUNT(hj.sno) AS StartEndDateCount 
					FROM hrcon_jobs hj 
					LEFT JOIN emp_list ON emp_list.username = hj.username
					LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username 
					WHERE hj.ustatus IN('active') AND
					emp_list.lstatus != 'DA' 
					AND hrcon_compen.ustatus='active' AND hrcon_compen.dept IN (".$deptnos.")
					AND hj.jtype!='' AND hj.jotype!='0'
					AND (DATE_FORMAT(STR_TO_DATE(IF(hj.e_date='0-0-0','00-00-0000',hj.e_date),'%m-%d-%Y'),'%m/%d/%Y') LIKE '%".$dateval."%' )";
		$result = mysql_query($select,$db);
		$i=0;
		while($row = mysql_fetch_array($result)){
			$output[$i] =$row[0];
			$i++;
		}
		return $output;
	}
	/* Job/Temp Planning Enhancement END */
	
	public function getEverifyCaseUpdates($username,$widget_id) 
	{
        GLOBAL $db,$username,$ac_aced;

		require_once('eVerify/class.eVerify.php');
		$ev=new eVerifyAPI();

		$title = "<span class='widgtitlecolorBlue'><img src='../images/eVerify.png' title='E-Verify' alt='E-Verify' style='vertical-align: bottom;' /> E-Verify Case Updates</span>";
		$toolbar = "<a href=javascript:refreshWidgets('everifyUpdates',".$widget_id.")><i class='fa fa-repeat fa-lg'></i></a>";
		$tooltip = "E-Verify Case Updates";
        $image = '';

        $location = '';
        $viewall_link = '';

		$everify_widget=$this->getWidgetHeader($image, $title, $toolbar, $tooltip, $widget_id);


		if($ev->isUserVerified())
		{
			$data=false;
			$caseCounts=$ev->caseAlertCounts($data);

			if($caseCounts['cases_to_be_closed']>0)
				$fnc_link="/BSOS/HRM/Employee_Mngmt/eVerifyEmps.php?case_status=Final Nonconfirmation";
			else
				$fnc_link="#";

			if($caseCounts['work_docs_expiring']>0)
				$docs_link="/BSOS/HRM/Employee_Mngmt/eVerifyEmps.php?case_status=Open";
			else
				$docs_link="#";

			if($caseCounts['cases_with_new_updates']>0)
				$open_link="/BSOS/HRM/Employee_Mngmt/eVerifyEmps.php?case_status=Open";
			else
				$open_link="#";

			// To update cases that are updated with everify from SSN or DHS. We need to sync the status accordingly. Eventually we will move this to back end scripts to sync statuses
			$casesUpdated=$ev->casesUpdated($data);
			if(count($casesUpdated)>0)
			{
				for($l=0;$l<count($casesUpdated);$l++)
				{
					if($casesUpdated[$l]['case_number']!="")
					{
						$dfields="";
						foreach($casesUpdated[$l] as $dkey => $dval)
						{
							if(in_array($dkey,$ev->case_ele))
							{
								if($dkey=="ssn")
								{
									$ssn_no 	= str_replace('-','',$dval);
									$ssn_en 	= $ac_aced->encrypt($ssn_no);
									$last4_ssn 	= substr($ssn_no, -4);
									$ssn_hash 	= $ac_aced->hash_data($last4_ssn, $ac_aced->hash_salt);

									$dfields.=",".$dkey."='".mysql_real_escape_string($ssn_en)."'";
									$dfields.=",ssn_hash='".mysql_real_escape_string($ssn_hash)."'";
								}
								else if($dkey=="date_of_birth")
								{
									$dfields.=",".$dkey."='".mysql_real_escape_string($ac_aced->encrypt(get_standard_dateFormat($dval,"Y-m-d","m-d-Y")))."'";
								}
								else
									$dfields.=",".$dkey."='".mysql_real_escape_string($dval)."'";
							}
						}
	
						$uque="UPDATE everify_cases SET muser='".$username."',mdate=NOW()".$dfields." WHERE case_number='".$casesUpdated[$l]['case_number']."'";
						$ures=mysql_query($uque,$db);
						if($ures)
						{
							$data['case_numbers'][0] = $casesUpdated[$l]['case_number'];
							$casesConfirmUpdated=$ev->casesConfirmUpdated($data);
						}
					}
				}
			}
                
			$message_text	= '<table cellpadding="0" cellspacing="0" border="0"><tbody><tr>
                                <td colspan="3" class="edesktasks"><div class="edeskoverBg"><a href="'.$fnc_link.'" target="_blank" title="Everify Updates">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody>
                                                <tr>
                                                        <td>
                                                                <div valign="middle" style="width:152px;overflow:hidden;white-space:nowrap;">
                                                                        <a href="'.$fnc_link.'" title="Open Cases"><font style="font-size:100%;">Open Cases to be Closed</font>
                                                                        </a>
                                                                </div>
                                                        </td>
                                                        <td valign="middle">&nbsp;</td>
                                                        <td align="right">
                                                                <div valign="middle" style="width:142px;overflow:hidden;white-space:nowrap;">&nbsp;
                                                                        <font style="font-size:100%;">'.$caseCounts['cases_to_be_closed'].'</font>
                                                                </div>
                                                        </td>
                                                </tr>
                                        </tbody></table>
                                </a></div></td>
                        </tr><tr>
                                <td colspan="3" class="edesktasks"><div class="edeskoverBg"><a href="'.$docs_link.'" target="_blank" title="Everify Updates">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody>
                                                <tr>
                                                        <td>
                                                                <div valign="middle" style="width:152px;overflow:hidden;white-space:nowrap;">
                                                                        <a href="'.$docs_link.'" title="Docs Expiring"><font style="font-size:100%;">Documents Expiring</font>
                                                                        </a>
                                                                </div>
                                                        </td>
                                                        <td valign="middle">&nbsp;</td>
                                                        <td align="right">
                                                                <div valign="middle" style="width:142px;overflow:hidden;white-space:nowrap;">&nbsp;
                                                                        <font style="font-size:100%;">'.$caseCounts['work_docs_expiring'].'</font>
                                                                </div>
                                                        </td>
                                                </tr>
                                        </tbody></table>
                                </a></div></td>
                        </tr><tr>
                                <td colspan="3" class="edesktasks"><div class="edeskoverBg"><a href="'.$open_link.'" target="_blank" title="Everify Updates">
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody>
                                                <tr>
                                                        <td>
                                                                <div valign="middle" style="width:152px;overflow:hidden;white-space:nowrap;">
                                                                        <a href="'.$open_link.'" title="New Updates"><font style="font-size:100%;">Cases with New Updates</font>
                                                                        </a>
                                                                </div>
                                                        </td>
                                                        <td valign="middle">&nbsp;</td>
                                                        <td align="right">
                                                                <div valign="middle" style="width:142px;overflow:hidden;white-space:nowrap;">&nbsp;
                                                                        <font style="font-size:100%;">'.$caseCounts['cases_with_new_updates'].'</font>
                                                                </div>
                                                        </td>
                                                </tr>
                                        </tbody></table>
                                </a></div></td>
                        </tr>
                        </tbody></table>';
		}
		else
		{
			$message_text = "<i>We are unable to connect to E-Verify system.</i>";
		}

        $everify_widget .= $message_text;
		$everify_widget .= $this->getWidgetFooter($location,$viewall_link);

		return $everify_widget;
    }
}
?>
