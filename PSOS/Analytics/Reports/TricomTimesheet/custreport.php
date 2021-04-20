<?php

require_once('global_reports.inc');
require_once('Menu.inc');
require_once('functions.inc.php');
$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
$menu = new EmpMenu();
$header = '';
$name = explode('|', 'fa fa-play~Run&nbsp;Report|fa fa-times~Close');
$link = explode('|', 'javascript:runReport()|javascript:window.close()');
$report_options = '';
$filtervalues = '';
$filternames = 'empname|timesheetstatus|tdate|department';
$filtervalues = $TricomTimesheet;
if (isset($view) && $view == 'myreport' && isset($id) && !empty($id)) {
    $sel_rep_qry = "SELECT reportoptions FROM reportdata WHERE reportid = " . $id;
    $res_rep_qry = mysql_query($sel_rep_qry, $db);
    $rec_rep_qry = mysql_fetch_object($res_rep_qry);
    $filtervalues = $rec_rep_qry->reportoptions;
}elseif (!isset($main) && $main == '') {
    if (session_is_registered('TricomTimesheet')) {
        session_unregister('TricomTimesheet');
        unset($TricomTimesheet);
    }  
   $filtervalues   = $TricomTimesheet;
}
?>
<html>
    <head>
        <title>Customize</title>
        <link type="text/css" rel="stylesheet" href="/BSOS/css/educeit.css"/>
        <link type="text/css" rel="stylesheet" href="/BSOS/css/calendar.css"/>
        <link type="text/css" rel="stylesheet" href="/BSOS/css/ui.dropdownchecklist.css"/>
        <link type="text/css" rel="stylesheet" href="/BSOS/Analytics/Reports/analytics.css"/>
	<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="/BSOS/Home/style_screen.css">
        <script type="text/javascript" src="/BSOS/scripts/tabpane.js"></script>
        <script type="text/javascript" src="scripts/validatetype.js" ></script>
        <script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
        <script type="text/javascript" src="/BSOS/scripts/jquery-min.js"></script>
        <script type="text/javascript" src="/BSOS/scripts/ui.core-min.js"></script>
        <script type="text/javascript" src="/BSOS/scripts/ui.dropdownchecklist-min.js"></script>
        <script type="text/javascript">
            function resetDate(fieldname)
            {
                document.getElementById(fieldname).value = '';
            }
            function connectSearch(fieldname,dbInstance)
            {
                var modulename = "EmployeeXmlReport";
                var windowName = "AnalyticsSearch_"+fieldname;
                var v_width  = 530;
                var v_heigth = 300;
                var top1=(window.screen.availHeight-v_heigth)/2;
                var left1=(window.screen.availWidth-v_width)/2;
                
                 url = "/PSOS/Analytics/SearchWindows/CRMcommonSearch.php?fieldname="+fieldname+"&modulename="+modulename+"&dbInstance="+dbInstance;       
                remote=window.open(url,windowName,"width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes,resizable=yes");
                remote.focus();
            }
            function displayFilterValue(fieldname,searchvalue,searchid)
            {
                id = fieldname;
                document.getElementById(id).value = searchvalue;
            }
            $(document).ready(function() {
                $("#list_of_departments").dropdownchecklist({
                    firstItemChecksAll:true,
                    width:150,
                    maxDropHeight:70
                });
                
                 runReport = function (format){

                    var minvalue = document.getElementById("min_tdate").value;
                    var maxvalue = document.getElementById("max_tdate").value;
                    m1 = minvalue.split('/');
                    m2 = maxvalue.split('/');
                
                    var from_date     = new Date(m1[2],m1[0],m1[1]);
                    var to_date   = new Date(m2[2],m2[0],m2[1]);
                
                    if(to_date<from_date)
                    {
                        alert("To Date cannot not be less than From Date.\nPlease select a valid date.");
                        return;                           
                    }
                    var apstatusstr = '';
                    astatuslen = document.getElementById('select_timesheetstatus').length;
                    for(var ck=0; ck < astatuslen; ck++)
                    {
                        if(document.getElementById('select_timesheetstatus').options[ck].selected && document.getElementById('select_timesheetstatus').options[ck].value != "" )
                        {
                            if(apstatusstr=='')
                                apstatusstr =document.getElementById('select_timesheetstatus').options[ck].value;
                            else
                                apstatusstr += "!#!"+document.getElementById('select_timesheetstatus').options[ck].value;
                        }
                    }
                    document.getElementById('timesheetstatus').value = apstatusstr;
                    if (window.opener.location.href.indexOf("BSOS/Analytics/Reports/accreport.php") > 0) 
                    {   

                        $("#TricomTimesheet").attr("target", "TricomTimesheetReport").submit();

                    } 
                    else if (window.opener.location.href.indexOf("BSOS/Analytics/Reports/myreports.php") > 0) 
                    {

                        $("#TricomTimesheet").attr("target", "TricomTimesheetReport").submit();

                    } 
                    else if (window.opener.location.href.indexOf("BSOS/Analytics/Reports/TricomTimesheet/header.php") > 0) 
                    {   
                        $("#TricomTimesheet").attr("target", "TricomTimesheetReportWindow").submit();
                    }

                    setTimeout(function() {
                        window.close();
                    }, 10);
                }
            });
        </script>
    </head>
    <body>
        <form name="TricomTimesheet" id="TricomTimesheet" action="storereport.php" method="post">
            <input type="hidden" name="main" value="<?php echo $main; ?>">
            <input type="hidden" name="view" value="<?php echo $view; ?>">
            <input type="hidden" name="reportfrm" value="<?php echo $reportfrm; ?>">
            <input type="hidden" name="timesheetstatus" id="timesheetstatus" value="">
            <div id="main">
                <table width=99% cellpadding=0 cellspacing=0 border=0>
                    <tr>
                        <td>
                            <table width=100% cellpadding=0 cellspacing=0 border=0>
                                <tr>
                                    <td colspan=2><font class="bstrip">&nbsp;</font></td>
                                </tr>
                                <tr>
                                    <td colspan=2><font class="modcaption">&nbsp;Report Customization</font></td>
                                </tr>
                                <tr>
                                    <td colspan=2><font class="bstrip">&nbsp;</font></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white">
                                <tr>
                                    <td width=100% valign=top align=center>
                                        <div class="tab-pane" id="tabPane1">
                                            <script type="text/javascript">
                                                tp2 = new WebFXTabPane(document.getElementById("tabPane1"));
                                            </script>
                                            <div class="tab-page" id="tabPage11">
                                                <h2 class="tab">Report</h2>
                                                <script type="text/javascript">
                                                    tp2.addTabPage(document.getElementById("tabPage11"));
                                                </script>
                                                <div class="tab-pane" id="tabPane2">
                                                    <script type="text/javascript">
                                                        tp2 = new WebFXTabPane(document.getElementById("tabPane2"));
                                                    </script>
                                                    <div class="tab-page" id="tabPage22" >
                                                        <h2 class="tab">Filters</h2>
                                                        <script type="text/javascript">
                                                            tp2.addTabPage(document.getElementById("tabPage22"));
                                                        </script>
                                                        <table width="100%" border="0" cellspacing="2" cellpadding="2" >
                                                            <tr class="NewGridTopBg">
                                                                <?php $menu->showHeadingStrip1($name, $link, $header); ?>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <fieldset>
                                                                        <legend>
                                                                            <font class="afontstyle">Filters</font>
                                                                        </legend>
                                                                        <table width="100%" cellpadding="3" cellspacing="0" border="0" id="filter_table" class="ProfileNewUI">
                                                                            <tbody>
                                                                                <tr id="filter_message">
                                                                                    <td>&nbsp;</td>
                                                                                    <td colspan="2" class="">Select the required options from the Available Columns displayed below and click on Run Report to generate the Report</td>
                                                                                </tr>
                                                                                <?php echo getFilters($filternames, $filtervalues,$main,$accountingExport,$deptAccesSno); ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </fieldset>
                                                                </td>
                                                            </tr>
                                                            <tr><td colspan="2"><font class="bstrip">&nbsp;</font></td></tr>
                                                            <tr class="NewGridBotBg"><?php $menu->showHeadingStrip1($name, $link, $header); ?></tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </body>
</html>