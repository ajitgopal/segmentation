<?php
require_once('global.inc');
require_once('shift_schedule/class.schedules.php');
class GigBoard 
{
    
    function __construct()
    {
        global $db;
        $this->db = $db;
        $this->schedules  = new Schedules();
        $this->deptAccessObj = new departmentAccess();
    }  
    
    // this function is to fetch the employee username based on employee sno
    public function getEmployeUsernameBySno($empsno){
        if($empsno!=''){
            $query = "SELECT username FROM emp_list WHERE sno=".$recno_data[3];
            $result_resp= mysql_query($query,$this->db);
            $result = mysql_fetch_row($result_resp);
            return $result;
        }else{
            return '';
        }
        
    }
        
    // this function is to fetch hrcon table shift details
    public function getHrconShiftStimeEtimeBasedOnShiftSno($shiftsno){
        if($shiftsno!=''){
            $get_shift_hr_sql   = "SELECT DATE_FORMAT(hst.shift_date, '%m/%d/%Y'),hst.shift_starttime,hst.shift_endtime,hst.shiftnotes,hj.pusername
                                    FROM hrconjob_sm_timeslots hst 
                                    LEFT JOIN hrcon_jobs hj ON (hst.pid = hj.sno)
                                    WHERE hst.sno = '".$shiftsno."'
                                    ORDER BY hst.shift_date ASC";
            $get_shift_hr_res  = mysql_query($get_shift_hr_sql,$this->db);
            if (mysql_num_rows($get_shift_hr_res) > 0) 
            {
                $shifthrjobsrow  = mysql_fetch_row($get_shift_hr_res);

            }else{
                $shifthrjobsrow='';
            }
            return $shifthrjobsrow;
        }else{
            return '';
        }
    }
    
    public function getHrconShiftDetailsBasedOnShiftSno($shiftsno,$assignmentsno)
    {
        if($shiftsno!='' && $assignmentsno!='' ){
            $get_shift_hr_sql   = "SELECT DATE_FORMAT(hst.shift_date, '%m/%d/%Y'),hst.shift_starttime,hst.shift_endtime,hst.shiftnotes,hj.pusername
                                    FROM hrconjob_sm_timeslots hst 
                                    LEFT JOIN hrcon_jobs hj ON (hst.pid = hj.sno)
                                    WHERE hj.pusername='".$assignmentId."' AND hst.sno = '".$shiftsno."'
                                    ORDER BY hst.shift_date ASC";
            $get_shift_hr_res  = mysql_query($get_shift_hr_sql,$this->db);
            if (mysql_num_rows($get_shift_hr_res) > 0) 
            {
                $shifthrjobsrow  = mysql_fetch_row($get_shift_hr_res);

            }else{
                $shifthrjobsrow='';
            }
            return $shifthrjobsrow;
        }else{
            return '';
        }
    } 

    // this function is to fetch hrcon table shift details
    public function getHrconShiftDetailsBasedOnAssignId($assignmentid,$shiftdate){
        if($shiftdate!='' && $assignmentid!='' ){
            $get_shift_hrcon_sql   = "SELECT DATE_FORMAT(hst.shift_date, '%m/%d/%Y'),hst.sno,hj.pusername,hst.shiftnotes
                                    FROM hrconjob_sm_timeslots hst 
                                    LEFT JOIN hrcon_jobs hj ON (hst.pid = hj.sno)
                                    WHERE hj.pusername = '".$assignmentid."' AND DATE_FORMAT(hst.shift_date, '%m/%d/%Y') = '".$shiftdate."'
                                    ORDER BY hst.shift_date ASC";
            $get_shift_hrcon_res  = mysql_query($get_shift_hrcon_sql,$this->db);
            if (mysql_num_rows($get_shift_hrcon_res) > 0) 
            {
                $shifthrconjobsrow  = mysql_fetch_row($get_shift_hrcon_res);

            }else{
                $shifthrconjobsrow='';
            }
            return $shifthrconjobsrow;
        }else{
            return '';
        }     
    }
    
    public function getHrconShiftDetailsBasedOnStimeEtime($assignmentid,$shiftdetails){
        $shift_date = $shiftdetails[0];
        $shift_starttime = $shiftdetails[1];
        $shift_endtime = $shiftdetails[2];
        if ($shift_date !="") {            
            $get_shift_hrcon_sql = "SELECT hst.sno,DATE_FORMAT(hst.shift_date, '%m/%d/%Y'),
                                    hst.shift_starttime,hst.shift_endtime,
                                    hst.event_group_no,hst.shiftnotes,
                                    hj.pusername
                                    FROM hrconjob_sm_timeslots hst 
                                    LEFT JOIN hrcon_jobs hj ON (hst.pid = hj.sno)
                                    WHERE hj.pusername = '".$assignmentid."' 
                                    AND DATE_FORMAT(hst.shift_date, '%m/%d/%Y') = '".$shift_date."'
                                    AND hst.shift_starttime = '".$shift_starttime."'
                                    AND hst.shift_endtime  = '".$shift_endtime."'
                                    ORDER BY hst.shift_date ASC";
            $get_shift_hrcon_res  = mysql_query($get_shift_hrcon_sql,$this->db);
            if (mysql_num_rows($get_shift_hrcon_res) > 0) 
            {
                $shifthrjobsrow  = mysql_fetch_row($get_shift_hrcon_res);

            }else{
                $shifthrjobsrow='';
            }
            return $shifthrjobsrow;
        }else{
            return '';
        }
    }

    public function updateHrconSmShift($shiftsno,$queryDataString){
        $upd_shift_hrcon_sql = "UPDATE hrconjob_sm_timeslots SET ".$queryDataString." WHERE sno='".$shiftsno."'";
        $sm_cand_tf_res = mysql_query($upd_shift_hrcon_sql, $this->db);
        return $sm_cand_tf_res;
    }
    
    // this function is to update hrcon table shift notes
    public function updateHrconShift($shiftsno,$queryDataString){
        $sel_shift_info_sql = "SELECT pid,event_group_no FROM hrconjob_sm_timeslots WHERE sno = '".$shiftsno."'";
        $sel_shift_info_res = mysql_query($sel_shift_info_sql, $this->db);
        $sel_shift_info_row = mysql_fetch_array($sel_shift_info_res);
        $pid                = $sel_shift_info_row[0];
        $event_group_no     = $sel_shift_info_row[1];

        $sel_shift_sno_sql  = "SELECT GROUP_CONCAT(sno) as shiftSno FROM hrconjob_sm_timeslots 
                              WHERE event_group_no='".$event_group_no."' AND pid='".$pid."'";
        $sel_shift_sno_res  = mysql_query($sel_shift_sno_sql, $this->db);
        $sel_shift_sno_row  = mysql_fetch_array($sel_shift_sno_res);

        $event_shift_sno    = $sel_shift_sno_row[0];

        if($shiftsno!='' && $queryDataString!='' )
        {
            if($event_group_no!=0)
            {
                $upd_shift_hrcon_sql = "UPDATE hrconjob_sm_timeslots SET ".$queryDataString." WHERE sno IN (".$event_shift_sno.") AND pid='".$pid."'";
                $sm_cand_tf_res = mysql_query($upd_shift_hrcon_sql, $this->db);
            }
            else
            {

               $upd_shift_hrcon_sql = "UPDATE hrconjob_sm_timeslots SET ".$queryDataString." WHERE sno='".$shiftsno."'";
               $sm_cand_tf_res = mysql_query($upd_shift_hrcon_sql, $this->db); 
            }           
            return true;
        }   
    }
    
    // this function is to delete hrcon table shift 
    public function deleteHrconShift($shiftsno){

        $sel_shift_info_sql = "SELECT pid,event_group_no FROM hrconjob_sm_timeslots WHERE sno = '".$shiftsno."'";
        $sel_shift_info_res = mysql_query($sel_shift_info_sql, $this->db);
        $sel_shift_info_row = mysql_fetch_array($sel_shift_info_res);
        $pid                = $sel_shift_info_row[0];
        $event_group_no     = $sel_shift_info_row[1];

        $sel_shift_sno_sql  = "SELECT GROUP_CONCAT(sno) as shiftSno FROM hrconjob_sm_timeslots 
                              WHERE event_group_no='".$event_group_no."' AND pid='".$pid."'";
        $sel_shift_sno_res  = mysql_query($sel_shift_sno_sql, $this->db);
        $sel_shift_sno_row  = mysql_fetch_array($sel_shift_sno_res);

        $event_shift_sno    = $sel_shift_sno_row[0];

        if($shiftsno!='' && $shiftdate!='' )
        {
            if($event_group_no!=0)
            {
                $upd_shift_hrcon_sql = "DELETE FROM hrconjob_sm_timeslots WHERE sno IN (".$event_shift_sno.") AND pid='".$pid."'";
                $sm_cand_tf_res = mysql_query($upd_shift_hrcon_sql, $this->db);
            }
            else
            {
                $event_shift_sno    = $shiftsno;
                $upd_shift_hrcon_sql = "DELETE FROM hrconjob_sm_timeslots WHERE sno='".$shiftsno."'";
                $sm_cand_tf_res = mysql_query($upd_shift_hrcon_sql, $this->db);
            }

           return $event_shift_sno;
        }   
    }

    public function deleteHrconSmTimeslotShifts($shiftsno)
    {
        $shiftsnos = implode(",", $shiftsno);
        $delt_shift_hrcon_sql = "DELETE FROM hrconjob_sm_timeslots WHERE sno IN (".$shiftsnos.")";
        $sm_cand_tf_res = mysql_query($delt_shift_hrcon_sql, $this->db);
        return $delt_shift_hrcon_sql;
    }

    // get shiftsno from hrconjob_sm_timeslots for history for deleted shifts from gigboard
    public function getHrconTimeslotSnos($shiftsno)
    {
        $sel_shift_info_sql = "SELECT pid,event_group_no FROM hrconjob_sm_timeslots WHERE sno = '".$shiftsno."' AND event_group_no !='0'";
        $sel_shift_info_res = mysql_query($sel_shift_info_sql, $this->db);
        $sel_shift_info_row = mysql_fetch_array($sel_shift_info_res);
        $pid                = $sel_shift_info_row[0];
        $event_group_no     = $sel_shift_info_row[1];

        $sel_shift_sno_sql  = "SELECT GROUP_CONCAT(sno) as shiftSno FROM hrconjob_sm_timeslots 
                              WHERE event_group_no='".$event_group_no."' AND pid='".$pid."' AND event_group_no !='0'";
        $sel_shift_sno_res  = mysql_query($sel_shift_sno_sql, $this->db);
        $sel_shift_sno_row  = mysql_fetch_array($sel_shift_sno_res);
        $event_shift_sno    = $sel_shift_sno_row[0];

        if ($event_shift_sno !="") {
            return $event_shift_sno;
        }else{
            $event_shift_sno = $shiftsno;
            return $event_shift_sno;
        }
    }

    public function getHrconShiftDetails($shiftsno){
        if($shiftsno!=''){
            $fetch_shift_hrcon_sql = "SELECT DATE_FORMAT(shift_starttime, '%Y-%m-%dT%T') AS shift_starttime,DATE_FORMAT(shift_endtime, '%Y-%m-%dT%T') AS shift_endtime,sno,shiftnotes FROM hrconjob_sm_timeslots WHERE sno='".$shiftsno."'";
            $fetch_shift_hrcon_res= mysql_query($fetch_shift_hrcon_sql, $this->db);
            if (mysql_num_rows($fetch_shift_hrcon_res) > 0) 
            {
                $fetch_shift_hrcon_row  = mysql_fetch_row($fetch_shift_hrcon_res);
            }else{
                $fetch_shift_hrcon_row='';
            }
            return $fetch_shift_hrcon_row;
        }else{
            return '';
        }   
    }

    public function getHrconTimeslotDetails($shiftsno){
        $smtfstr = array();
        if(count($shiftsno)>0){
            $shiftsnos = implode(",",$shiftsno);
            $fetch_shift_hrcon_sql = "SELECT DATE_FORMAT(shift_date,'%m/%d/%Y'),
                                            shift_starttime,
                                            shift_endtime,
                                            event_no,
                                            event_group_no,
                                            shift_status,
                                            sm_sno,
                                            no_of_positions 
                                        FROM  hrconjob_sm_timeslots
                                        WHERE sno IN (".$shiftsnos.")";

            $fetch_shift_hrcon_res= mysql_query($fetch_shift_hrcon_sql, $this->db);
            if (mysql_num_rows($fetch_shift_hrcon_res) > 0) 
            {
                while($row  = mysql_fetch_array($fetch_shift_hrcon_res)){
                    $seldateval = $row[0];
                    $fromTF     = $this->schedules->getMinutesFrmDateTime($row[1]);
                    $toTF       = $this->schedules->getMinutesFrmDateTime($row[2]);
                    $recNo      = $row[3];
                    $slotGrpNo  = $row[4];
                    $shiftStatus    = $row[5];
                    $shiftNameSno   = $row[6];
                    $shiftPosNum    = $row[7];
                   
                    $smtfstr[] = $seldateval."^".$fromTF."^".$toTF."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftNameSno."^".$shiftPosNum."^";
                }
            }
        }
        return $smtfstr;  
    }

    // this function is to fetch resource and events to display on calendar
    public function getResourcesRespDetails($groupby,$assgn_status,$departmentId,$employees,$customers,$sdate,$edate)
    {
        global $username;

        $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");

        $emp_list = "";
        $cust_list = "";
        $cust_list_cond = "";
        if(count($employees)>0)
        {
            $emp_list = implode(',', $employees);
        }
        
        if(count($customers)>0)
        {
          $cust_list = implode(',', $customers);
        }
        //////////////////////////////////////
        $hrcon_assgn_status  = " AND hrcon_jobs.ustatus in('".$assgn_status."')";
        $department ='';
        if ($departmentId !=0) {
            $department          = " AND dep.sno IN ('".$departmentId."')";
            $department_left_join = "LEFT JOIN department dep ON  hrcon_jobs.deptid = dep.sno";
        }

        if($emp_list!='' && $emp_list!=0)
        {
            $emp_list_cond  = " AND emp_list.sno IN (".$emp_list.")";
        }else{
            $emp_list_cond ="";
        }

        if($cust_list!='' && $cust_list!=0)
        {
            $cust_list_cond  = " AND staffacc_cinfo.sno IN (".$cust_list.")";
        }
        $orderBy = ' ORDER BY result.company_name ASC,result.emp_name ASC,result.shift_starttime ASC';
        if ($groupby == "customers") {
            $orderBy = ' ORDER BY result.company_name ASC,result.emp_name ASC,result.shift_starttime ASC';
        }elseif ($groupby == "employees") {
            $orderBy = ' ORDER BY result.emp_name ASC,result.company_name ASC,result.shift_starttime ASC';
        }
        if ($sdate =="" && $edate =="") {
            $query =  $this->getResourcesHrconJobsQuery($employees,$customers,$assgn_status,$departmentId,$groupby);
        }else{
        
            $query  = "SELECT * FROM (
                            SELECT 
                                hrcon_jobs.sno AS assgn_sno,
                                staffacc_cinfo.sno AS company_sno,
                                staffacc_cinfo.username AS customer_sno,
                                CONCAT(emp_list.sno,' - ',emp_list.name) AS emp_name,
                                CONCAT(staffacc_cinfo.sno,' - ',staffacc_cinfo.cname) AS company_name,
                                emp_list.sno AS emp_sno, 
                                hrcon_jobs.pusername AS assignmnet_id,
                                hrcon_jobs.ustatus AS assgn_status,
                                hrcon_jobs.project AS assgn_title,
                                CONCAT(jloc.address1,' ',jloc.address2,' ',jloc.city,' ',jloc.state,' ',jloc.zipcode) AS assgn_loc,
                                hst.sno AS timeslotSno,
                                DATE_FORMAT(hst.shift_starttime, '%Y-%m-%dT%T') AS shift_starttime,
                                DATE_FORMAT(hst.shift_endtime, '%Y-%m-%dT%T') AS shift_endtime,
                                DATE_FORMAT(hst.shift_starttime, '%Y-%m-%d %H:%i:%s') AS shiftStart,
                                DATE_FORMAT(hst.shift_endtime, '%Y-%m-%d %H:%i:%s') AS shiftEnd,
                                DATE_FORMAT(CONVERT_TZ(hst.shift_date, 'SYSTEM', 'EST5EDT'),'%m/%d/%Y') AS shift_date,CONCAT(DATE_FORMAT(hst.shift_starttime,'%h:%i %p'),' - ',DATE_FORMAT(hst.shift_endtime,'%h:%i %p')) AS shift_timing,
                                hst.event_group_no AS event_group_no,
                                shift_setup.shiftcolor AS shift_color,
                                shift_setup.shiftname AS shift_name,
                                hst.shiftnotes AS shift_note,
                                'NO' AS split_shift,
                                ".tzRetQueryStringSTRTODate('hrcon_jobs.s_date','%m-%d-%Y','Date','-')." AS assgn_sdate,
                                ".tzRetQueryStringSTRTODate('hrcon_jobs.e_date','%m-%d-%Y','Date','-')." AS assgn_edate,
                                hg.mobile AS mobile,
                                hg.wphone AS wphone,
                                hg.email AS email,
                                rst.type AS shift_type,
                                rst.shift_date AS cancel_date,
                                rc.reason,
                                rc.reason_description,
                                el.name AS assigned_emp,
                                emp_list.name AS emp_full_name,
                                staffacc_cinfo.cname AS company_full_name,
                                hrcon_jobs.shift_type AS shiftModuleType,
                                '' AS shift_edate,
                                CONCAT(sc.fname,' ',sc.lname) AS jobReportTo,
                                sc.wphone AS jobReportPhone 
                            FROM 
                                emp_list
                                LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
                                LEFT JOIN shift_setup ON (shift_setup.sno = hrcon_jobs.shiftid)
                                LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username
                                LEFT JOIN hrconjob_sm_timeslots hst ON hst.pid = hrcon_jobs.sno
                                LEFT JOIN reassign_sm_timeslots rst ON hrcon_jobs.pusername = rst.from_assign_no AND hst.shift_date = rst.shift_date
                                LEFT JOIN reason_codes rc ON rst.reason_sno = rc.sno
                                LEFT JOIN emp_list el ON rst.to_username = el.username
                                LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = hrcon_jobs.client
                                LEFT JOIN staffacc_location AS jloc ON jloc.sno = hrcon_jobs.endclient
                                LEFT JOIN hrcon_general hg ON emp_list.username = hg.username
                                ".$department_left_join."
                                LEFT JOIN staffacc_location ON hrcon_jobs.endclient = staffacc_location.sno
                                LEFT JOIN staffacc_contact sc ON (sc.sno=hrcon_jobs.manager)
                            WHERE 
                                emp_list.lstatus != 'DA' 
                                AND hrcon_jobs.pusername != ''
                                AND hrcon_compen.ustatus='active'
                                AND hrcon_compen.dept !='0' AND hrcon_compen.dept IN (".$selDept.")
                                AND hrcon_jobs.jtype!='' 
                                AND hrcon_jobs.jotype!='0'
                                AND hrcon_jobs.shiftid !='0'
                                AND hrcon_jobs.shift_type = 'regular'
                                AND                
                                ( 
                                    DATE_FORMAT(hst.shift_date,'%Y-%m-%d') >= DATE_FORMAT('".$sdate."','%Y-%m-%d')
                                    AND DATE_FORMAT(hst.shift_date,'%Y-%m-%d') < DATE_FORMAT('".$edate."','%Y-%m-%d') 
                                )
                                ".$hrcon_assgn_status."
                                ".$department."
                                ".$emp_list_cond."
                                ".$cust_list_cond."
                            GROUP BY emp_list.sno,hst.event_group_no,hst.shift_date,shift_timing
                         
                        UNION ALL 
                         
                            SELECT
                               hrcon_jobs.sno AS assgn_sno,
                               staffacc_cinfo.sno AS company_sno,
                               staffacc_cinfo.username AS customer_sno,
                               CONCAT(emp_list.sno, ' - ', emp_list.name) AS emp_name,
                               CONCAT(staffacc_cinfo.sno, ' - ', staffacc_cinfo.cname) AS company_name,
                               emp_list.sno AS emp_sno,
                               hrcon_jobs.pusername AS assignmnet_id,
                               hrcon_jobs.ustatus AS assgn_status,
                               hrcon_jobs.project AS assgn_title,
                               CONCAT(jloc.address1, ' ', jloc.address2, ' ', jloc.city, ' ', jloc.state, ' ', jloc.zipcode) AS assgn_loc,
                               hpss.sno AS timeslotSno,
                               CONCAT(DATE_FORMAT(hpss.shift_startdate, '%Y-%m-%d'), 'T', DATE_FORMAT(hpss.shift_starttime, '%H:%i:%s')) AS shift_starttime,
                               CONCAT(DATE_FORMAT(hpss.shift_enddate, '%Y-%m-%d'), 'T', DATE_FORMAT(hpss.shift_endtime, '%H:%i:%s')) AS shift_endtime,
                               CONCAT(DATE_FORMAT(hpss.shift_startdate, '%Y-%m-%d'), ' ', DATE_FORMAT(hpss.shift_starttime, '%H:%i:%s')) AS shiftStart,
                               CONCAT(DATE_FORMAT(hpss.shift_enddate, '%Y-%m-%d'), ' ', DATE_FORMAT(hpss.shift_endtime, '%H:%i:%s')) AS shiftEnd,
                               DATE_FORMAT(CONVERT_TZ(hpss.shift_startdate, 'SYSTEM', 'EST5EDT'), '%m/%d/%Y') AS shift_date,
                               CONCAT(DATE_FORMAT(hpss.shift_starttime, '%h:%i %p'), ' - ', DATE_FORMAT(hpss.shift_endtime, '%h:%i %p')) AS shift_timing,
                               '0' AS event_group_no,
                               shift_setup.shiftcolor AS shift_color,
                               shift_setup.shiftname AS shift_name,
                               hpss.shift_note AS shift_note,
                               hpss.split_shift AS split_shift,
                               ".tzRetQueryStringSTRTODate('hrcon_jobs.s_date','%m-%d-%Y','Date','-')." AS assgn_sdate,
                               ".tzRetQueryStringSTRTODate('hrcon_jobs.e_date','%m-%d-%Y','Date','-')." AS assgn_edate,
                               hg.mobile AS mobile,
                               hg.wphone AS wphone,
                               hg.email AS email,
                               prst.reason_type AS shift_type,
                               prst.shift_startdate AS cancel_date,
                               prc.reason AS perdiemReason,
                               prst.reason_desc AS perdiemReasonDesc,
                               pel.name AS assigned_emp,
                               emp_list.name AS emp_full_name,
                               staffacc_cinfo.cname AS company_full_name,
                               hrcon_jobs.shift_type AS shiftModuleType,
                               DATE_FORMAT(CONVERT_TZ(hpss.shift_enddate, 'SYSTEM', 'EST5EDT'), '%m/%d/%Y') AS shift_edate,
                               CONCAT(sc.fname,' ',sc.lname) AS jobReportTo,
                               sc.wphone AS jobReportPhone
                            FROM
                                emp_list 
                                LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username 
                                LEFT JOIN shift_setup ON (shift_setup.sno = hrcon_jobs.shiftid) 
                                LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username 
                                LEFT JOIN hrconjob_perdiem_shift_sch hpss ON hpss.hrconjob_sno = hrcon_jobs.sno 
                                  AND hpss.pusername = hrcon_jobs.pusername
                                LEFT JOIN cancel_reassign_perdiem_shift_info prst ON hrcon_jobs.pusername = prst.from_pusername 
                                  AND hpss.shift_startdate = prst.shift_startdate 
                                  AND hpss.shift_enddate = prst.shift_enddate 
                                LEFT JOIN reason_codes prc ON prst.reason_id = prc.sno  
                                LEFT JOIN emp_list pel ON prst.to_username = pel.username 
                                LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = hrcon_jobs.client 
                                LEFT JOIN staffacc_location AS jloc ON jloc.sno = hrcon_jobs.endclient 
                                LEFT JOIN hrcon_general hg ON emp_list.username = hg.username
                                ".$department_left_join."
                                LEFT JOIN staffacc_location ON hrcon_jobs.endclient = staffacc_location.sno
                                LEFT JOIN staffacc_contact sc ON (sc.sno=hrcon_jobs.manager)
                            WHERE 
                                emp_list.lstatus != 'DA' 
                                AND hrcon_jobs.pusername != ''
                                AND hrcon_compen.ustatus='active'
                                AND hrcon_compen.dept !='0' AND hrcon_compen.dept IN (".$selDept.")
                                AND hrcon_jobs.jtype!='' 
                                AND hrcon_jobs.jotype!='0'
                                AND hrcon_jobs.shiftid !='0'
                                AND hrcon_jobs.shift_type = 'perdiem'
                                AND
                                (
                                    ( 
                                        DATE_FORMAT(hpss.shift_startdate,'%Y-%m-%d') >= DATE_FORMAT('".$sdate."','%Y-%m-%d')
                                        AND DATE_FORMAT(hpss.shift_startdate,'%Y-%m-%d') <= DATE_FORMAT('".$edate."','%Y-%m-%d') 
                                    )
                                    OR 
                                    ( 
                                        DATE_FORMAT(hpss.shift_enddate,'%Y-%m-%d') >= DATE_FORMAT('".$sdate."','%Y-%m-%d')
                                        AND DATE_FORMAT(hpss.shift_enddate,'%Y-%m-%d') <= DATE_FORMAT('".$edate."','%Y-%m-%d') 
                                    )
                                )
                                ".$hrcon_assgn_status."
                                ".$department."
                                ".$emp_list_cond."
                                ".$cust_list_cond."
                            GROUP BY emp_list.sno,hpss.shift_startdate,hpss.shift_enddate,hpss.shift_starttime,hpss.shift_endtime                                           
                        ) result ".$orderBy." ";
        }
        $result = mysql_query($query,$this->db);
        return $result;
    }

    public function getResourcesHrconJobsQuery($employees,$customers,$assgn_status,$departmentId,$groupby)
    {
        global $username;

        $selDept = $this->deptAccessObj->getDepartmentAccess($username,"'BO'");

        $emp_list = "";
        $cust_list = "";
        $cust_list_cond ="";
        $emp_list_cond = "";
        if(count($employees)>0)
        {
            $emp_list = implode(',', $employees);
        }
        
        if(count($customers)>0)
        {
            $cust_list = implode(',', $customers);
        }

        $hrcon_assgn_status  = " AND hrcon_jobs.ustatus in('".$assgn_status."')";
        $department ='';
        if ($departmentId !=0) {
            $department = " AND dep.sno IN ('".$departmentId."')";
            $department_left_join = "LEFT JOIN department dep ON  hrcon_jobs.deptid = dep.sno";
        }

        if($emp_list!='' && $emp_list!=0)
        {
            $emp_list_cond  = " AND emp_list.sno IN (".$emp_list.")";
        }
        else
        {
            $emp_list_cond   = "";
        }

        if($cust_list!='' && $cust_list!=0)
        {
            $cust_list_cond  = " AND staffacc_cinfo.sno IN (".$cust_list.")";
        }
        else
        {
            $cust_list_cond  = "";
        }

        $orderBy = ' ORDER BY staffacc_cinfo.cname ASC,emp_list.name ASC';
        if ($groupby == "customers") {
            $orderBy = ' ORDER BY staffacc_cinfo.cname ASC,emp_list.name ASC';
        }elseif ($groupby == "employees") {
            $orderBy = ' ORDER BY emp_list.name ASC,staffacc_cinfo.cname ASC';
        }
        $query  = "SELECT 
                    hrcon_jobs.sno as assgn_sno,
                    staffacc_cinfo.sno as company_sno,
                    staffacc_cinfo.username as customer_sno,                    
                    CONCAT(emp_list.sno,' - ',emp_list.name) as emp_name,
                    CONCAT(staffacc_cinfo.sno,' - ',staffacc_cinfo.cname) as company_name,
                    emp_list.sno as emp_sno, 
                    hrcon_jobs.pusername as assignmnet_id,
                    hrcon_jobs.ustatus as assgn_status,
                    hrcon_jobs.project as assgn_title,
                    CONCAT(jloc.address1,' ',jloc.address2,' ',jloc.city,' ',jloc.state,' ',jloc.zipcode) AS assgn_loc,
                    shift_setup.shiftcolor AS shift_color,
                    shift_setup.shiftname AS shift_name,
                ".tzRetQueryStringSTRTODate('hrcon_jobs.s_date','%m-%d-%Y','Date','-')." AS assgn_sdate,
                ".tzRetQueryStringSTRTODate('hrcon_jobs.e_date','%m-%d-%Y','Date','-')." AS assgn_edate,
                hg.mobile AS mobile,
                hg.wphone AS wphone,
                hg.email AS email,
                emp_list.name as emp_full_name,
                staffacc_cinfo.cname as company_full_name,
                hst.event_group_no AS event_group_no,
                hrcon_jobs.shift_type AS shiftType,
                CONCAT(sc.fname,' ',sc.lname) AS jobReportTo,
                sc.wphone AS jobReportPhone
                FROM emp_list
                LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
                LEFT JOIN hrcon_compen ON emp_list.username = hrcon_compen.username
                LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = hrcon_jobs.client
                LEFT JOIN staffacc_location AS jloc ON jloc.sno = hrcon_jobs.endclient
                LEFT JOIN manage ON hrcon_jobs.jotype=manage.sno 
                LEFT JOIN users ON hrcon_jobs.owner =users.username
                LEFT JOIN users AS muser ON hrcon_jobs.muser = muser.username
                LEFT JOIN users AS sagent ON hrcon_jobs.owner =sagent.username
                LEFT JOIN shift_setup ON hrcon_jobs.shiftid = shift_setup.sno
                LEFT JOIN hrcon_general hg ON emp_list.username = hg.username
                LEFT JOIN hrconjob_sm_timeslots hst ON hst.pid = hrcon_jobs.sno
                LEFT JOIN staffacc_contact sc ON (sc.sno=hrcon_jobs.manager)
                ".$department_left_join."
                WHERE 
                emp_list.lstatus != 'DA' 
                AND hrcon_compen.ustatus='active'
                AND hrcon_compen.dept !='0' AND hrcon_compen.dept IN (".$selDept.")
                AND hrcon_jobs.jtype!='' 
                AND hrcon_jobs.jotype!='0'
                AND hrcon_jobs.shiftid !='0'
                ".$hrcon_assgn_status."
                ".$department."
                ".$emp_list_cond."
                ".$cust_list_cond."
                GROUP BY emp_list.sno,staffacc_cinfo.sno,hrcon_jobs.pusername 
                ".$orderBy." ";
        return $query;
    }

    public function doInsertNewShiftsToAssign($data)
    {
        global $username;

        if ($data['module'] == "hrcon") {
            $inserthr = "INSERT INTO `hrconjob_sm_timeslots`
                (
                    `sno`, 
                    `pid`, 
                    `shift_date`, 
                    `shift_starttime`, 
                    `shift_endtime`, 
                    `event_type`, 
                    `event_no`, 
                    `event_group_no`, 
                    `shift_status`, 
                    `sm_sno`, 
                    `no_of_positions`, 
                    `cuser`, 
                    `ctime`, 
                    `muser`, 
                    `mtime`, 
                    `shiftnotes`
                )
                VALUES
                (
                    '', 
                    '".$data['pid']."', 
                    '".$data['shift_date']."', 
                    '".$data['shift_starttime']."', 
                    '".$data['shift_endtime']."', 
                    'recurrence', 
                    '1', 
                    '".$data['event_group_no']."', 
                    'busy', 
                    '".$data['sm_sno']."', 
                    '0', 
                    '".$username."', 
                    NOW(), 
                    '".$username."', 
                    NOW(), 
                    ''
                )";
            mysql_query($inserthr,$this->db);

            $sno = mysql_insert_id();
        }
        return $sno;
    }

    // This function is for split shift dates dragging on gigboard
    function splitShiftDates($assgn_sno,$event_group_no,$table)
    {
        if($table!="")
        {
            $que = "SELECT GROUP_CONCAT(CONCAT(hst.shift_starttime, ',', hst.shift_endtime)
                              ORDER BY hst.shift_starttime DESC) AS schHrs
                              FROM ".mysql_real_escape_string($table)." hst
                              WHERE hst.event_group_no = '".$event_group_no."' AND hst.pid='".$assgn_sno."'  GROUP BY hst.event_group_no";

            $res = mysql_query($que,$this->db);

            return $res;
        }
        else
        {
            return "";
        }
    }

    /* */

    public function getResEventData($exeRes,$resource_data,$events_data,$childrenFullArray,$groupby,$callingFrom)
    {
        // Array key exists or not for Company with employee
        if (! function_exists('array_column')) 
        {
              function array_column(array $input, $columnKey, $indexKey = null) {
                  $array = array();
                  foreach ($input as $value) {
                      
                      if (is_null($indexKey)) {
                          $array[] = $value[$columnKey];
                      }
                      else 
                      {                
                          $array[$value[$indexKey]] = $value[$columnKey];
                      }
                  }
                  return $array;
              }
        }
        $i=0;
        while($exeRow = mysql_fetch_array($exeRes))
        {  

            if($groupby=='customers')
            {
              $building_name  = $exeRow["company_name"];
              $building_title = $exeRow["emp_name"];
            }
            else if($groupby=='employees')
            {
              $building_name  = $exeRow["emp_name"];
              $building_title = $exeRow["company_name"];
            }
            if($exeRow['assgn_status'] == "" || $exeRow['assgn_status'] == "active")
                $asg_status = "approved";
            else if($exeRow['assgn_status'] == "cancel")
              $asg_status = "cancelled";
            else
              $asg_status = $exeRow['assgn_status'];

            if ($callingFrom == "resources") {

                if (array_search($exeRow["company_sno"].'|'.$exeRow["emp_sno"].'|'.$exeRow["customer_sno"], array_column($resource_data, 'id'))) 
                {
                    $key = array_search($exeRow["company_sno"].'|'.$exeRow["emp_sno"].'|'.$exeRow["customer_sno"], array_column($resource_data, 'id'));
                      
                    $childrenFullArray = $resource_data[$key]['children'];
                      
                    $childrenval = array_column($childrenFullArray,'id');

                    if (!in_array($exeRow["assignmnet_id"], $childrenval)) {
                      
                        $childrenArray = array(
                                              'id'            => $exeRow["assignmnet_id"],
                                              'title'         => $exeRow["assignmnet_id"],
                                              'eventColor'    => $exeRow["shift_color"],
                                              'assgn_sdate'   => $exeRow["assgn_sdate"],
                                              'assgn_edate'   => $exeRow["assgn_edate"],
                                              'open_assgnment'=> trim($exeRow['assgn_sno']."|"."15"."|".$asg_status."|".$exeRow['emp_sno']),
                                              'assgn_title'   => $exeRow["assgn_title"],
                                              'assgn_loc'     => $exeRow["assgn_loc"],
                                              'asign_status'  => $asg_status,
                                              'shift_type'    => ucfirst($exeRow["shiftType"]),
                                              'job_report_to'    => $exeRow["jobReportTo"],
                                              'job_report_phone'    => $exeRow["jobReportPhone"],
                                              'node'          => 'Child'
                                          );
                        array_push($resource_data[$key]['children'], $childrenArray);               
                    }
                }else{
                      
                    $values =  array(
                                          'id'              => $exeRow["company_sno"].'|'.$exeRow["emp_sno"].'|'.$exeRow["customer_sno"],
                                          'building'        => $building_name,
                                          'title'           => $building_title,
                                          'node'            => 'Parent',
                                          'mobile'          => $exeRow["mobile"],
                                          'wphone'          => $exeRow["wphone"],
                                          'email'           => $exeRow["email"],
                                          'open_employee'   => '',
                                          'group_by'        => $groupby,
                                          'building_order'  => $exeRow["company_full_name"],
                                          'title_order'     => $exeRow["emp_full_name"],
                                          'children'        =>array(
                                                                array(
                                                                  'id'            => $exeRow["assignmnet_id"],
                                                                  'title'         => $exeRow["assignmnet_id"],
                                                                  'eventColor'    => $exeRow["shift_color"],
                                                                  'assgn_sdate'   => $exeRow["assgn_sdate"],
                                                                  'assgn_edate'   => $exeRow["assgn_edate"],
                                                                  'open_assgnment'=> trim($exeRow['assgn_sno']."|"."15"."|".$asg_status."|".$exeRow['emp_sno']),
                                                                  'assgn_title'   => $exeRow["assgn_title"],
                                                                  'assgn_loc'     => $exeRow["assgn_loc"],
                                                                  'asign_status'  => $asg_status,
                                                                  'shift_type'    => ucfirst($exeRow["shiftType"]),
                                                                  'job_report_to'    => $exeRow["jobReportTo"],
                                                                  'job_report_phone'    => $exeRow["jobReportPhone"],
                                                                  'node'          => 'Child'
                                                                )
                                                            )
                                        );
                      array_push($resource_data, $values);
                }          
            }
            if ($callingFrom == "events") {   


                if ($exeRow["shiftModuleType"] == "perdiem") {
                    $check_cancelreassign_qry = "(
                        SELECT crp.reason_type as reasontype,crp.reason_desc as noshow_reason,
                        reason_codes.reason AS reasonName 
                        FROM cancel_reassign_perdiem_shift_info crp 
                        LEFT JOIN reason_codes ON(reason_codes.sno= crp.reason_id) 
                        WHERE crp.from_pusername = '".$exeRow["assignmnet_id"]."'
                        AND ( DATE_FORMAT(shift_startdate,'%Y-%m-%d') = DATE_FORMAT('".$exeRow["shiftStart"]."','%Y-%m-%d') 
                        OR 
                        DATE_FORMAT(shift_enddate,'%Y-%m-%d') = DATE_FORMAT('".$exeRow["shiftEnd"]."','%Y-%m-%d')
                        ) 
                        AND DATE_FORMAT(shift_starttime,'%H:%i:%s')=DATE_FORMAT('".$exeRow["shift_starttime"]."','%H:%i:%s') 
                        AND DATE_FORMAT(shift_endtime,'%H:%i:%s')=DATE_FORMAT('".$exeRow["shift_endtime"]."','%H:%i:%s') 
                        AND crp.reason_type = 'reassignshift')

                        UNION

                        (SELECT crp.reason_type as reasontype,crp.reason_desc as noshow_reason,
                        reason_codes.reason AS reasonName 
                        FROM cancel_reassign_perdiem_shift_info crp 
                        LEFT JOIN reason_codes ON(reason_codes.sno= crp.reason_id) 
                        WHERE crp.pusername = '".$exeRow["assignmnet_id"]."'
                        AND ( DATE_FORMAT(shift_startdate,'%Y-%m-%d') = DATE_FORMAT('".$exeRow["shiftStart"]."','%Y-%m-%d') 
                        OR 
                        DATE_FORMAT(shift_enddate,'%Y-%m-%d') = DATE_FORMAT('".$exeRow["shiftEnd"]."','%Y-%m-%d')
                        ) 
                        AND DATE_FORMAT(shift_starttime,'%H:%i:%s')=DATE_FORMAT('".$exeRow["shift_starttime"]."','%H:%i:%s') 
                        AND DATE_FORMAT(shift_endtime,'%H:%i:%s')=DATE_FORMAT('".$exeRow["shift_endtime"]."','%H:%i:%s') 
                        AND crp.reason_type = 'cancelshift'
                    )";
                }else{
                   $check_cancelreassign_qry = "SELECT reassign_sm_timeslots.type as reasontype,noshow_reason FROM reassign_sm_timeslots 
                    LEFT JOIN reason_codes ON(reason_codes.sno= reassign_sm_timeslots.reason_sno) 
                    WHERE from_assign_no = '".$exeRow["assignmnet_id"]."' 
                    AND DATE_FORMAT(shift_date,'%Y-%m-%d')  = '".$exeRow["cancel_date"]."' 
                    AND DATE_FORMAT(shift_starttime,'%Y-%m-%d %H:%i:%s')='".$exeRow["shiftStart"]."' 
                    AND DATE_FORMAT(shift_endtime,'%Y-%m-%d %H:%i:%s')='".$exeRow["shiftEnd"]."' 
                    AND reassign_sm_timeslots.type IN ('cancelshift','reasonshift')"; 
                }

                

                $check_cancelreassign_res =  mysql_query($check_cancelreassign_qry,$this->db);
                $check_cancelreassign_row = mysql_fetch_assoc($check_cancelreassign_res);
                $shift_type  =  "";

                if($check_cancelreassign_row['reasontype']!='')
                {
                    $shift_type  =  $check_cancelreassign_row['reasontype'];
                    $exeRow["reason_description"] = $check_cancelreassign_row['noshow_reason'];
                    
                    if ($exeRow["shiftModuleType"] == "perdiem") {
                        $exeRow["reason"] = $check_cancelreassign_row['reasonName'];
                    }
                    
                }
                
                if($check_cancelreassign_row['reasontype']==""){

                    /*$check_timesheet_qry = "SELECT DISTINCT par_timesheet.sdate,par_timesheet.edate FROM timesheet_hours JOIN par_timesheet ON (par_timesheet.sno = timesheet_hours.parid ) WHERE assid = '".$exeRow["assignmnet_id"]."' ";
                    $query_result   = mysql_query($check_timesheet_qry,$this->db);
                    if (mysql_num_rows($query_result) > 0) {
                        
                        while($result = mysql_fetch_array($query_result)){
                            $dateStartEndArray = array();
                            $dateStartEndArrays = $this->getSmStartEndDateRange($result['sdate'], $result['edate']);
                            foreach ($dateStartEndArrays as $StartEndArray) {
                                
                                if (!in_array($StartEndArray, $dateStartEndArray)) {
                                    $dateStartEndArray[] = $StartEndArray;
                                }
                            }
                        }
                        $shift_date = date("d-m-Y",strtotime($exeRow["shiftStart"]));
                        
                        if (in_array($shift_date, $dateStartEndArray)) {
                           $shift_type  =  'timesheetFilled';
                           $exeRow["reason_description"] = 'Timesheet has filled for this date.';
                        }
                    }*/
                    $shift_date = date("Y-m-d",strtotime($exeRow["shiftStart"]));
                    $check_timesheet_qry = "SELECT sno FROM timesheet_hours WHERE sdate='".$shift_date."' AND assid = '".$exeRow["assignmnet_id"]."' ";
                    $query_result   = mysql_query($check_timesheet_qry,$this->db);
                    if (mysql_num_rows($query_result) > 0) {
                        $shift_type  =  'timesheetFilled';
                        $exeRow["reason_description"] = 'Timesheet has filled for this date.';
                    }
                }

                $table = "hrconjob_sm_timeslots";  
                $splitDatesRes = "";              
                if($exeRow["shiftModuleType"] == "regular"){
                    $splitDatesRes = $this->splitShiftDates($exeRow["assgn_sno"],$exeRow["event_group_no"],$table);
                }
                
                if($splitDatesRes!="")
                {
                  $splitDatesRow = mysql_fetch_assoc($splitDatesRes);
                  $schduledHours = $splitDatesRow['schHrs'];
                  $schduledHoursVal = explode(",",$schduledHours);
                  $schduledHoursVal1 = $schduledHoursVal[0];
                  $schduledHoursVal2 = $schduledHoursVal[3]; 
                }
                if ($exeRow["shiftModuleType"] == "perdiem") {

                    $schduledHoursVal1 = $exeRow["shiftStart"];
                    $schduledHoursVal2 = $exeRow["shiftEnd"];

                    $splitShift = $exeRow["split_shift"];
                    if ($splitShift == "Y") {
                        $eventGrpNo = rand(10,100);
                        $splitShiftEndTimeT = date("Y-m-d",strtotime($exeRow["shift_date"])).'T23:59:00';  
                        $splitShiftStartTimeT = date("Y-m-d",strtotime($exeRow["shift_edate"])).'T00:00:00';

                        $splitShiftEndTime = date("Y-m-d",strtotime($exeRow["shift_date"])).' 23:59:00';  
                        $splitShiftStartTime = date("Y-m-d",strtotime($exeRow["shift_edate"])).' 00:00:00';

                        // 2019-10-11 00:00:00, -> split start
                        $schduledHoursVal1 = date("Y-m-d",strtotime($exeRow["shift_edate"])).' 00:00:00';
                        // 2019-10-11 04:00:00,2019-10-10 21:00:00,
                        $schduledHoursVal2 = date("Y-m-d",strtotime($exeRow["shift_date"])).' 23:59:00';
                        // 2019-10-10 23:59:00 -> split end

                        $cancelReassignDetails='||||';
                        if ($shift_type !="") {
                            $cancelReassignDetails = $shift_type."|".date("Y-m-d",strtotime($exeRow["shift_date"]))."|".$exeRow["reason"]."|".$exeRow["reason_description"]."|".$exeRow["assigned_emp"];
                        }                      

                        $eventchildrenArray =   array(                            
                                          'id'            => $exeRow["timeslotSno"].'_1',
                                          'resourceId'    => $exeRow["assignmnet_id"],
                                          'start'         => $exeRow["shift_starttime"],
                                          'end'           => $splitShiftEndTimeT,
                                          'shift_type'    => $cancelReassignDetails,
                                          'title'         => $exeRow["shift_name"],
                                          'event_group_no'=> $eventGrpNo,
                                          'shiftStart'    => $exeRow["shiftStart"],
                                          'shiftEnd'      => $splitShiftEndTime,
                                          'shiftNote'     => $exeRow["shift_note"],
                                          'splitStartDate'=> $schduledHoursVal1,
                                          'splitEndDate'  => $schduledHoursVal2,
                                          'assgn_status'  => $asg_status,
                                          'shift_color'   => $exeRow["shift_color"],
                                          'shift_module'  => $exeRow["shiftModuleType"],
                                          'employee_name' => $exeRow["emp_name"]
                                        );
                        array_push($events_data, $eventchildrenArray);
                        $cancelReassignDetails1='||||';
                        if ($shift_type !="") {
                            $cancelReassignDetails1 = $shift_type."|".date("Y-m-d",strtotime($exeRow["shift_edate"]))."|".$exeRow["reason"]."|".$exeRow["reason_description"]."|".$exeRow["assigned_emp"];
                        }
                        $eventchildrenArray1 =   array(                            
                                          'id'            => $exeRow["timeslotSno"].'_2',
                                          'resourceId'    => $exeRow["assignmnet_id"],
                                          'start'         => $splitShiftStartTimeT,
                                          'end'           => $exeRow["shift_endtime"],
                                          'shift_type'    => $cancelReassignDetails1,
                                          'title'         => $exeRow["shift_name"],
                                          'event_group_no'=> $eventGrpNo,
                                          'shiftStart'    => $splitShiftStartTime,
                                          'shiftEnd'      => $exeRow["shiftEnd"],
                                          'shiftNote'     => $exeRow["shift_note"],
                                          'splitStartDate'=> $schduledHoursVal1,
                                          'splitEndDate'  => $schduledHoursVal2,
                                          'assgn_status'  => $asg_status,
                                          'shift_color'   => $exeRow["shift_color"],
                                          'shift_module'  => $exeRow["shiftModuleType"],
                                          'employee_name' => $exeRow["emp_name"]
                                        );
                        array_push($events_data, $eventchildrenArray1);
                    }else{
                        $cancelReassignDetails='||||';
                        if ($shift_type !="") {
                            $cancelReassignDetails = $shift_type."|".date("Y-m-d",strtotime($exeRow["shift_date"]))."|".$exeRow["reason"]."|".$exeRow["reason_description"]."|".$exeRow["assigned_emp"];
                        }
                        $eventchildrenArray =   array(                            
                                          'id'            => $exeRow["timeslotSno"].'_0',
                                          'resourceId'    => $exeRow["assignmnet_id"],
                                          'start'         => $exeRow["shift_starttime"],
                                          'end'           => $exeRow["shift_endtime"],
                                          'shift_type'    => $cancelReassignDetails,
                                          'title'         => $exeRow["shift_name"],
                                          'event_group_no'=> '0',
                                          'shiftStart'    => $exeRow["shiftStart"],
                                          'shiftEnd'      => $exeRow["shiftEnd"],
                                          'shiftNote'     => $exeRow["shift_note"],
                                          'splitStartDate'=> $schduledHoursVal1,
                                          'splitEndDate'  => $schduledHoursVal2,
                                          'assgn_status'  => $asg_status,
                                          'shift_color'   => $exeRow["shift_color"],
                                          'shift_module'  => $exeRow["shiftModuleType"],
                                          'employee_name' => $exeRow["emp_name"]
                                        );
                        array_push($events_data, $eventchildrenArray);
                    }
                    
                }else{
                   $eventchildrenArray =   array(                            
                                          'id'            => $exeRow["timeslotSno"],
                                          'resourceId'    => $exeRow["assignmnet_id"],
                                          'start'         => $exeRow["shift_starttime"],
                                          'end'           => $exeRow["shift_endtime"],
                                          'shift_type'    => $shift_type."|".$exeRow["cancel_date"]."|".$exeRow["reason"]."|".$exeRow["reason_description"]."|".$exeRow["assigned_emp"],
                                          'title'         => $exeRow["shift_name"],
                                          'event_group_no'=> $exeRow["event_group_no"],
                                          'shiftStart'    => $exeRow["shiftStart"],
                                          'shiftEnd'      => $exeRow["shiftEnd"],
                                          'shiftNote'     => $exeRow["shift_note"],
                                          'splitStartDate'=> $schduledHoursVal1,
                                          'splitEndDate'  => $schduledHoursVal2,
                                          'assgn_status'  => $asg_status,
                                          'shift_color'   => $exeRow["shift_color"],
                                          'shift_module'  => $exeRow["shiftModuleType"],
                                          'employee_name' => $exeRow["emp_name"]
                                        ); 
                    array_push($events_data, $eventchildrenArray);
                }
                
                
            }
            $i++;    
        }
        $resultOut = array();
        if ($callingFrom == "resources") {
            array_push($resultOut, $resource_data);
        }
        if ($callingFrom == "events") {
          array_push($resultOut, $events_data);
        }
        return $resultOut;
    }

    public function getSmStartEndDateRange($strDateFrom,$strDateTo) {

        $aryRange=array();
        $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('d-m-Y',$iDateFrom));
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400;
                array_push($aryRange,date('d-m-Y',$iDateFrom));
            }
        }
        return $aryRange;
    }
    /*
        Perdiem Shift's functions
    */
    public function updateHrconPerdiemShift($shiftSno,$queryDataString)
    {   
        $shiftSnoAry = array();
        $shiftSnoAry = explode("_",$shiftSno);
        $shiftSno = $shiftSnoAry[0];
        $update = "UPDATE hrconjob_perdiem_shift_sch SET ".$queryDataString." WHERE sno='".$shiftSno."' ";
        $res = mysql_query($update,$this->db)or die(mysql_error());
        return $res;
    }

    public function checkPerdiemSplitShift($shiftSno)
    {
        $resultOut = false;
        $shiftSnoAry = array();
        $shiftSnoAry = explode("_",$shiftSno);
        $shiftSno = $shiftSnoAry[0];
        $select = "SELECT split_shift FROM hrconjob_perdiem_shift_sch WHERE sno = '".$shiftSno."'";
        $result = mysql_query($select,$this->db);
        $row = mysql_fetch_assoc($result);
        if ($row['split_shift'] == "Y") {
            $resultOut = true;
        }else{
            $resultOut = false;
        }
        return $resultOut;
    }

    public function getHrconPerdiemShiftDetails($shiftsno){
        if($shiftsno!=''){
            $shiftSnoAry = array();
            $shiftSnoAry = explode("_",$shiftsno);
            $shiftsno = $shiftSnoAry[0];
            $fetch_shift_hrcon_sql = "SELECT CONCAT(DATE_FORMAT(shift_startdate, '%Y-%m-%d'),' ',DATE_FORMAT(shift_starttime, '%T')) AS shift_starttime,CONCAT(DATE_FORMAT(shift_enddate, '%Y-%m-%d'),' ',DATE_FORMAT(shift_endtime, '%T')) AS shift_endtime,sno,shift_note 
                FROM hrconjob_perdiem_shift_sch WHERE sno='".$shiftsno."'";
            $fetch_shift_hrcon_res= mysql_query($fetch_shift_hrcon_sql, $this->db);
            if (mysql_num_rows($fetch_shift_hrcon_res) > 0) 
            {
                $fetch_shift_hrcon_row  = mysql_fetch_row($fetch_shift_hrcon_res);
            }else{
                $fetch_shift_hrcon_row='';
            }
            return $fetch_shift_hrcon_row;
        }else{
            return '';
        }   
    }

    public function getHrconPerdiemTimeslotDetails($shiftsno){
        $smtfstr = array();
        if(count($shiftsno)>0){
            $shiftsnos = implode(",",$shiftsno);
            $fetch_shift_hrcon_sql = "SELECT DATE_FORMAT(shift_startdate,'%m/%d/%Y') AS shiftSDate,
                                            DATE_FORMAT(shift_enddate,'%m/%d/%Y') AS shiftEDate,
                                            shift_starttime AS shiftStime,
                                            shift_endtime AS shiftEtime,
                                            '1' AS shiftSqu,
                                            '0' AS shiftRevno,
                                            'busy' AS shiftStus,
                                            shift_id AS shiftId,
                                            '1' AS shiftPosNum,
                                            split_shift AS shiftSplit
                                        FROM  hrconjob_perdiem_shift_sch
                                        WHERE sno IN (".$shiftsnos.")";

            $fetch_shift_hrcon_res= mysql_query($fetch_shift_hrcon_sql, $this->db);
            if (mysql_num_rows($fetch_shift_hrcon_res) > 0) 
            {
                while($row  = mysql_fetch_array($fetch_shift_hrcon_res)){
                    $seldateval = $row[0];
                    $selEdateval = $row[1];
                    $dateSTime = $row[0].' '.$row[2];
                    $dateETime = $row[1].' '.$row[3];
                    $fromTF     = $this->schedules->getMinutesFrmDateTime($dateSTime);
                    $toTF       = $this->schedules->getMinutesFrmDateTime($dateETime);
                    $recNo      = $row[4];
                    $slotGrpNo  = $row[5];
                    $shiftStatus    = $row[6];
                    $shiftNameSno   = $row[7];
                    $shiftPosNum    = $row[8];
                    if ($row[9] == "Y") {
                        $slotGrpNo  = rand(10,100);
                        $shiftStr = $seldateval."^".$fromTF."^1439^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftNameSno."^".$shiftPosNum."^";
                        array_push($smtfstr, $shiftStr);

                        $shiftStr1 = $selEdateval."^0^".$toTF."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftNameSno."^".$shiftPosNum."^";
                        array_push($smtfstr, $shiftStr1);
                    }else{

                        $smtfstr[] = $seldateval."^".$fromTF."^".$toTF."^".$recNo."^".$slotGrpNo."^".$shiftStatus."^".$shiftNameSno."^".$shiftPosNum."^";
                    }                 
                }
            }
        }
        return $smtfstr;  
    }

    public function deleteHrconPerdiemShifts($shiftsno)
    {
        
        $shiftsnos = implode(",", $shiftsno);
        $delt_shift_hrcon_sql = "DELETE FROM hrconjob_perdiem_shift_sch WHERE sno IN (".$shiftsnos.")";
        $sm_cand_tf_res = mysql_query($delt_shift_hrcon_sql, $this->db);
        return $delt_shift_hrcon_sql;
    }

    public function doInsertNewPerdiemShiftsToAssign($data)
    {
        global $username;

        if ($data['module'] == "hrcon") {
            $inserthr = "INSERT INTO `hrconjob_perdiem_shift_sch`
                (
                    `sno`, 
                    `hrconjob_sno`, 
                    `pusername`, 
                    `no_of_shift_position`, 
                    `shift_startdate`, 
                    `shift_enddate`, 
                    `shift_starttime`, 
                    `shift_endtime`, 
                    `split_shift`, 
                    `shift_id`, 
                    `shift_note`, 
                    `cdate`, 
                    `cuser`, 
                    `mdate`, 
                    `muser`
                )
                VALUES
                (
                    '', 
                    '".$data['hrconjobsno']."',
                    '".$data['pusername']."',
                    '1',
                    '".$data['startdate']."',
                    '".$data['enddate']."',
                    '".$data['starttime']."',
                    '".$data['endtime']."',
                    '".$data['splitshift']."',
                    '".$data['sm_sno']."',
                    '',
                    NOW(),
                    '".$username."',
                    NOW(),
                    '".$username."'
                )";
            mysql_query($inserthr,$this->db)or die(mysql_error());

            $sno = mysql_insert_id($this->db);
        }
        return $sno;
    }
}
?>
