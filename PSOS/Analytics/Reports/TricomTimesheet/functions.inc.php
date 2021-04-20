<?php
require_once('dispfunc.php');
require_once('reportdatabase.inc');
global $accountingExport;
function getFilters($filter_names, $filter_values,$main,$accountingExport,$deptAccesSno)
 {
    $show_filters = '';
    $filtervalues = '';
    $headers =  array('empname' => 'Employee Name','timesheetstatus' => 'Timesheet/Expense Status','tdate' => 'Timesheet/Expense Date','department' => 'HRM Departments');
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

        if ($filternames[$i] == 'tdate')
        {
           $minname     = "min_".$filternames[$i];
            $maxname    = "max_".$filternames[$i];
            $ranges     = explode("*",$filtervalues[$i]);
            $minvalue   = $ranges[0];
            $maxvalue   = $ranges[1];           

            if($maxvalue=='' && $minvalue=='' && $main == ""){
                $minvalue   = date("m/d/Y",strtotime(date("Ym01")));
                $maxvalue   = date("m/d/Y");
                 

            }

            $show_filters .=  "<font class='afontstyle'>
                    From : <input name='".$minname."' value='".$minvalue."' size='10' type='text'  id ='".$minname."' readonly>                              
                    <script language='JavaScript'> new tcal ({'formname':'TricomTimesheet','controlname':'".$minname."'});</script>
                    </font>
                    <a href=javascript:resetDate('".$minname."')>
                    <i alt='Reset' class='fa fa-reply'></i></a>
                    &nbsp;&nbsp;<font class='afontstyle'>
                    To : <input name='".$maxname."' value='".$maxvalue."' size='10' type='text' id ='".$maxname."' readonly>
                    <script language='JavaScript'> new tcal ({'formname':'TricomTimesheet','controlname':'".$maxname."'});</script>
                    </font>
                    <a href=javascript:resetDate('".$maxname."')>
                    <i alt='Reset' class='fa fa-reply'></i></a>";
        }
        else if ($filternames[$i] == 'empname')
        { 
            $dbinstance     = getDbInstance($filternames[$i]);
            $empval         = isset($filtervalues[$i])?$filtervalues[$i]:'';
            $show_filters   .=  "<font class='afontstyle'>
                <input name='empname' id ='".str_replace(" ",'',$headers[$filternames[$i]])."' value='".$empval."' type='text' size='20'>    
                </font> <a href=javascript:connectSearch('".str_replace(" ",'',$headers[$filternames[$i]])."','".$dbinstance."')><i alt='Search' class='fa fa-search'></i></a>";
        }
        else if($filternames[$i] == "timesheetstatus")
        {
               
            $scriptStr .= '$("#select_'.$filternames[$i].'").dropdownchecklist({firstItemChecksAll: true, width: 150,maxDropHeight: 60 });';
            $selectValue = $correspondingvalues_array[$filternames[$i]];
            if(!$selectValue)
            {
                $show_filters .="<select class=drpdwne multiple='multiple' name='select_{$filternames[$i]}' id='select_{$filternames[$i]}'>";
                $show_filters .= "<option value='ALL'>ALL</option>";
                $show_filters .= "<option value='Approved' selected='selected'>Approved</option>";
                $show_filters .= "<option value='ER'>Submitted</option>";
                if($accountingExport == 'Exported'){
                $show_filters .= "<option value='Exported'>Exported</option>";
                }
                $show_filters .= "</select>";
            }
            else if(($selectValue == 'ALL') || (strpos($selectValue,'!#!')>0) && (in_array('ALL',explode('!#!',$selectValue))) )
            {
            
                $show_filters .="<select class=drpdwne multiple='multiple' name='select_{$filternames[$i]}' id='select_{$filternames[$i]}'>";
                $show_filters .= "<option value='ALL' selected='selected'>ALL</option>";
                $show_filters .= "<option value='Approved' selected='selected'>Approved</option>";
                $show_filters .= "<option value='ER' selected='selected'>Submitted</option>";
                if($accountingExport == 'Exported'){
                $show_filters .= "<option value='Exported' selected='selected'>Exported</option>";
                }
                $show_filters .="</select>";
            }
            else
            {
                if( (trim($selectValue) == 'Approved') || ((strpos($selectValue,'!#!')>0) && (in_array('Approved',explode('!#!',$selectValue))) && !(in_array('ER',explode('!#!',$selectValue)))))
                {
                    $show_filters .="<select class=drpdwne multiple='multiple' name='select_{$filternames[$i]}' id='select_{$filternames[$i]}'>";
                    $show_filters .= "<option value='ALL'>ALL</option>";
                    $show_filters .= "<option value='Approved' selected>Approved</option>";
                    $show_filters .= "<option value='ER'>Submitted</option>";
                    if($accountingExport == 'Exported' && !(in_array('Exported',explode('!#!',$selectValue)))){
                    $show_filters .= "<option value='Exported'>Exported</option>";
                    }
                    else if($accountingExport == 'Exported' && (in_array('Exported',explode('!#!',$selectValue)))){
                    $show_filters .= "<option value='Exported' selected='selected'>Exported</option>";  
                    }
                    $show_filters .="</select>";
                }
                else if((trim($selectValue) == 'ER') || ((strpos($selectValue,'!#!')>0) && (in_array('ER',explode('!#!',$selectValue))) && !(in_array('Approved',explode('!#!',$selectValue)))))
                {

                    $show_filters .="<select class=drpdwne multiple='multiple' name='select_{$filternames[$i]}' id='select_{$filternames[$i]}'>";
                    $show_filters .= "<option value='ALL'>ALL</option>";
                    $show_filters .= "<option value='Approved'>Approved</option>";
                    $show_filters .= "<option value='ER' selected='selected'>Submitted</option>";
                    if($accountingExport == 'Exported' && !(in_array('Exported',explode('!#!',$selectValue)))){
                    $show_filters .= "<option value='Exported'>Exported</option>";
                    }
                    else if($accountingExport == 'Exported' && (in_array('Exported',explode('!#!',$selectValue)))){
                    $show_filters .= "<option value='Exported' selected='selected'>Exported</option>";  
                    }
                    $show_filters .="</select>";
                }
                else if((strpos($selectValue,'!#!')>0) && (in_array('ER',explode('!#!',$selectValue))) && (in_array('Approved',explode('!#!',$selectValue))))
                {
                    $show_filters .="<select class=drpdwne multiple='multiple' name='select_{$filternames[$i]}' id='select_{$filternames[$i]}'>";
                    $show_filters .= "<option value='ALL'>ALL</option>";
                    $show_filters .= "<option value='Approved' selected='selected'>Approved</option>";
                    $show_filters .= "<option value='ER' selected='selected'>Submitted</option>";

                    if($accountingExport == 'Exported' && !(in_array('Exported',explode('!#!',$selectValue)))){
                    $show_filters .= "<option value='Exported'>Exported</option>";
                    }
                    else if($accountingExport == 'Exported' && (in_array('Exported',explode('!#!',$selectValue)))){
                    $show_filters .= "<option value='Exported' selected='selected'>Exported</option>";  
                    }
                    $show_filters .="</select>";
                }
            
                else if((trim($selectValue) == 'Exported') || ((strpos($selectValue,'!#!')>0) && (in_array('Exported',explode('!#!',$selectValue))) && $accountingExport == 'Exported'))
                {

                    $show_filters .="<select class=drpdwne multiple='multiple' name='select_{$filternames[$i]}' id='select_{$filternames[$i]}'>";
                    $show_filters .= "<option value='ALL'>ALL</option>";
                    if(!(in_array('Approved',explode('!#!',$selectValue)))) {
                    $show_filters .= "<option value='Approved'>Approved</option>";
                    }
                    else if((in_array('Approved',explode('!#!',$selectValue)))) {
                    $show_filters .= "<option value='Approved' selected>Approved</option>"; 
                    }
                    if(!(in_array('ER',explode('!#!',$selectValue)))) {
                    $show_filters .= "<option value='ER'>Submitted</option>";
                    }
                    else if((in_array('ER',explode('!#!',$selectValue)))) {
                    $show_filters .= "<option value='ER' selected>Submitted</option>";   
                    }

                    $show_filters .= "<option value='Exported' selected>Exported</option>";
                    $show_filters .="</select>";
                }
            }
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
    /*if($accountingExport == 'Exported'){
            if($filternames[$i] == "exported_chk")
            {
                    $show_filters .=    "<font class='afontstyle'><input type= 'checkbox' name= 'exported_chk'>";
            }
            else{
                $show_filters .= "";
            }
        }*/
        
        $show_filters .= '</td></tr>';
    }
    echo $show_filters;
    $scriptStr .='});
                        </script>';
    echo $scriptStr;                

}
function getDbInstance($argFieldName)
{
    // array('main query filter condidion','search window parameter',Order by col);
    $arrTableName  = array("empname" => "emp_list^name");
            
    return $arrTableName[$argFieldName];
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