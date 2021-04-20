<?php
require_once('dispfunc.php');
require_once('reportdatabase.inc');
global $accountingExport;
function getFilters($filter_names, $filter_values,$main,$accountingExport,$deptAccesSno)
 {
    $show_filters = '';
    $filtervalues = '';
    $headers = 	array('department' => 'HRM Departments','Customer' => 'Customer Name','modifieddate' => 'Last Exported Date');
    $filternames = explode('|', $filter_names);
    $fields_count = count($filternames);

    if (isset($filter_values) && !empty($filter_values)) {
        $filtervalues = explode('|', $filter_values);
    }
     $correspondingvalues_array = @array_combine($filternames , $filtervalues);
     $scriptStr = ' <script type="text/javascript">

                            $(document).ready(function() {';
    for ($i = 0; $i < $fields_count; $i++) 
    {
        $row_id = 'filter_' . $filternames[$i];
        $show_filters .= "
        <tr id='" . $row_id . "'>
        <td width=3%>&nbsp;</td>
        <td width=25%><font class='afontstyle'>" . $headers[$filternames[$i]] . "</font></td>
        <td align=left width=60%>";
        $argSelectValue = $filtervalues[$i];

        if ($filternames[$i] == 'modifieddate')
        {
            $exprt_date = $filternames[$i];
            if ($main == "")
            {
                $value = '';
            }else{
                $value = $filtervalues[$i];
            }
$show_filters .= '<input type="text" name="modifieddate" id="datepicker-time" class="form-control" value="'.$value.'" size="23" readonly="readonly" style="position: relative; inset: auto;">&nbsp;<i class="fa fa-info-circle fa-md" 
title="Please select the last exported file timestamp saved in your directory.&nbsp;
eg: The last saved exported filename: Tricom-Customer-20200927-093109-AM.csv
Date to be selected under Last Exported Date filter: 09/27/2020 09:31:09 AM"></i>';

        }
        else if($filternames[$i] == 'Customer')
        {
            $value = html_tls_specialchars($filtervalues[$i]);
            $show_filters .=  '<font class="afontstyle">
            <input type="text" name="Customer" id ="'.$filternames[$i].'"   value="'.stripslashes($value).'">   
            </font> <a href=javascript:connectSearch("'.$filternames[$i].'","staffacc_cinfo^name")>
            <i alt="Search" class="fa fa-search"></i></a>';
        }
        else if ($filternames[$i] == 'department') 
        {

        $selected_val   = '';
        $sel_dept_id    = array();

        if (isset($filtervalues[$i]) && !empty($filtervalues[$i])) 
        {
            $sel_dept_id    = explode(',', $filtervalues[$i]);
        }

        if (empty($sel_dept_id) || $sel_dept_id[0] == 'ALL') 
        {

            $selected_val   = 'selected';
        }

        $show_filters   .= "<select class='afontstyle' multiple='multiple' style='width:150px;height:50px' name='department[]' id='list_of_departments'><option value='ALL' ".$selected_val.">ALL</option>";
        $departmentslist    = getDepartments($deptAccesSno);
        if (!empty($departmentslist)) 
        {

            foreach ($departmentslist as $id => $name) 
            {
             if (!empty($sel_dept_id)) 
                {

                    if (in_array($id, $sel_dept_id)) 
                    {

                        $selected_val   = 'selected';

                    } else 
                    {

                        $selected_val   = '';
                    }

                    $show_filters   .= "<option value='".$id."'  ".$selected_val.'>'.$name.'</option>';

                } 
                else 
                {

                    $show_filters   .= "<option value='".$id."'  ".$selected_val.'>'.$name.'</option>';
                }
            }
        }

        $show_filters   .= '</select>';
    }
        
        $show_filters .= '</td></tr>';
    }
    echo $show_filters;
    $scriptStr .='});
                        </script>';
    echo $scriptStr;                

}
function getDepartments($deptAccesSno) 
{

    global $rptdb, $username;
    $departments    = array();

    $sel_department_query   = "SELECT 
                                    d.sno, d.deptname 
                                FROM 
                                    department d 
                                WHERE 
                                   d.sno !='0' AND d.sno IN ({$deptAccesSno}) AND 
                                    d.status='Active'
                                ORDER BY 
                                    d.deptname";
    $res_department_query   = mysql_query($sel_department_query, $rptdb);

    while ($rec = mysql_fetch_object($res_department_query)) 
    {

        $departments[$rec->sno] = $rec->deptname;
    }

    return $departments;
}
function sele($argValue, $argSelectValue) {
    if ($argValue == $argSelectValue)
        return "selected";
    else
        return "";
}

function getCurrentDateTime() {
    global $db;
    $sql = "SELECT " . tzRetQueryStringDTime('NOW()', 'DateTimeSec', '/');
    $res = mysql_query($sql, $db);
    $row = mysql_fetch_row($res);
    $ctime = date("D m/d/Y h:i A", strtotime($row[0]));
    return $ctime;
}

function cleanArray($array) {
    foreach ($array as $index => $value) {
        if (is_array($array[$index])) {
            $array[$index] = cleanArray($array[$index]);
        }
        if (empty($value) && $value != 0) {
            $array[$index] = '';
        }
    }
    return $array;
}



?>