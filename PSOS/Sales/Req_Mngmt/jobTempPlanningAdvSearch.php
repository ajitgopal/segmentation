<?php
	require("global.inc");
	
	require("Menu.inc");
	$menu=new EmpMenu();
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$dque="select group_concat(sno) from department where sno IN (".$deptAccesSno.")";
	$dres=mysql_query($dque,$db);
	$drow=mysql_fetch_row($dres);
	$deptnos = $drow[0];

	if($deptnos=="")
		$deptnos="0";
			
	function sele($a,$b)
	{
		if($a==$b)
			return "selected";
		else
			return "";
	}

	function sel($a,$b)
	{
		if($a==$b)
			return "checked";
		else
			return "";
	}
	if ($date_range_weekS == "" && $date_range_weekE == "") {
		$monday = strtotime("last monday");
		$monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;

		$sunday = strtotime(date("m/d/Y",$monday)." +6 days");

		$startdateW = date("m/d/Y",$monday);
		$enddateW = date("m/d/Y",$sunday);
	}else{
		$startdateW = $date_range_weekS;
		$enddateW = $date_range_weekE;
	}

	function getSmStartEndDateRange($strDateFrom,$strDateTo) {

		$aryRange=array();
	    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
	    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

	    if ($iDateTo>=$iDateFrom)
	    {
	        array_push($aryRange,date('m/d/Y',$iDateFrom));
	        while ($iDateFrom<$iDateTo)
	        {
	            $iDateFrom+=86400;
	            array_push($aryRange,date('m/d/Y',$iDateFrom));
	        }
	    }
	    return $aryRange;
	}

	function getStartEndDateAssignCount($dateval='',$deptnos='0')
	{
		global $db,$username;

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

	$dayAry = array("MON","TUE","WED","THU","FRI","SAT","SUN");
	$dayStr = " ";
	$startdateW1 = date('Y-m-d',strtotime($startdateW));
	$enddateW1 = date('Y-m-d',strtotime($enddateW));
	$dateArray = getSmStartEndDateRange($startdateW1,$enddateW1);
	$dayStr= " ";
	foreach ($dayAry as $key => $dayvalue) {
		$dateVal = $dateArray[$key];
		$dateary = explode("/", $dateVal);
		$assignCountAry = getStartEndDateAssignCount($dateVal,$deptnos);
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

?>
<html>
<head>
<title>Job/Temp Planning</title>
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<!-- loads Zebra Datepicker CSS -->
<link type="text/css" rel="stylesheet" href="/BSOS/css/shift_schedule/zebra_default.css">
<!-- loads Zebra Datepicker Script -->  
<script type="text/javascript" src="/BSOS/scripts/shift_schedule/zebra_datepicker.js"></script>

<style type="text/css">
	#smallpreloaderW {
	    left: 50%;
	    margin-left: -16px !important;
	    margin-top: -16px !important;
	    position: absolute;
	    top: 50%;
	    z-index: 9999;
	}
	.body_glow {
	    left: 0;
	    position: absolute;
	    top: 0;
	    z-index: 9990;
	    background-color:#fff;
		height: 100%;
		opacity: 0.40;
		width: 100%;
	}
	button.Zebra_DatePicker_Icon_Inside{left: inherit !important; right: 4px !important;top:7px !important;}
	.Zebra_DatePicker { z-index: 9999 !important; }
	.jobTmpCls tr {border-radius:3px; }
	.jobTmpCls tr td {border: 2px #c1c0c0c4 solid; margin: 0px;padding: 2px;text-align: center;font-size: 13px;font-weight: bold;}
	.jobTmpCls tr td {border-right: 1px #c1c0c0c4 solid;}
	#dayname {border-bottom:2px #c1c0c0c4 solid; }

	.akkenPopupBtnBot1 {
	    position: absolute;
		top: 65px;
		left: 495px;
		width: 100%;
		height: 60px;
		line-height: 64px;
		text-align: left;
	}
	.akkenPopupBtnBot1 .akkenPopupBtn {
	    background:#3eb8f0;
		border: 1px solid #3eb8f0;
		padding: 5px 8px;
		margin-right: 10px;
		border-radius: 4px;
		color: #fff;
	    text-decoration: none;
	    font-weight: bold;
	    font-size: 16px;
	}
	.dateFilters {
	    position: inherit;
	    padding: 0px;
	    margin: 5px;
	    font-size: 13px;
	}
</style>

<script type="text/javascript">

	$( document ).ready(function() {


	  	$('#date_range_weekS').Zebra_DatePicker({
		    first_day_of_week : 1,
		    show_clear_date : false,
		    format: 'm/d/Y',
		    onSelect: function(date){
		      
				var months = [ "January", "February", "March", "April", "May", "June", 
				 "July", "August", "September", "October", "November", "December" ];

				var curr = new Date(date);           

				var startDay = 1; 
				var firstday = new Date(curr.setDate(curr.getDate() - (7 + curr.getDay() - startDay) % 7)); 
				var lastday = new Date(curr.setDate(curr.getDate() - curr.getDay()+7));        
				var selectedMonthName = months[curr.getMonth()];

				S_month = ( firstday.getMonth() + 1 );
				S_day =  firstday.getDate();
				S_year = firstday.getFullYear();

				E_month = ( lastday.getMonth() + 1 );
				E_day = lastday.getDate();
				E_year = lastday.getFullYear();
				var Start_date  = ("0" + (S_month)).slice(-2)+'/'+("0" + (S_day)).slice(-2)+'/'+S_year; 
				var End_date  = ("0" + (E_month)).slice(-2)+'/'+("0" + (E_day)).slice(-2)+'/'+E_year; 
				$("#date_range_weekS").val(Start_date);
				$("#date_range_weekE").val(End_date);

				doSearchFilters();
		    }
	  	});
	});
	$("#smallpreloaderW").show();
	$(".body_glow").show();
	$(document).ready(function() {
		$(window).load(function() {
	    	setTimeout(function () {
	       		$("#smallpreloaderW").hide();
				$(".body_glow").hide();   
	    	}, 5000);
		});
	});
	
	function doSearchFilters() {

		var sdate = document.getElementById("date_range_weekS").value;
		var edate = document.getElementById("date_range_weekE").value;
		if (sdate == "" || edate == "") {
			alert("Please Select the Date Range to View Job/Temp Planning results.");
			document.getElementById("date_range_weekS").focus();
			return;
		}else{
			$("#smallpreloaderW").show();
			$(".body_glow").show();
			document.getElementById("tmpSearch").submit();
		}
		
	}
</script>

</head>
<body>
	<!-- Preloader -->
    <img id="smallpreloaderW" src="/BSOS/images/preloader.gif" style="display: none;">
	<div class="body_glow" style="display: none;"></div>

	<form action=jobTempPlanningAdvSearch.php method=post name=tmpSearch id=tmpSearch ENCTYPE="multipart/form-data">

		<div id="main">
			<td valign=top align=center>
				<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI defaultTopRange" align="center">
    				<div id="topheader">
						<tr class="NewGridTopBg">
							<?php
								
								$name=explode("|","fa-times~Close");
								$link=explode("|","javascript:window.close()");

								$heading="knowledge.gif~Job/Temp&nbsp;Planning";
								$menu->showHeadingStrip1($name,$link,$heading);
							?>
						</tr>
					</div>
					<div id="grid_form">
						<tr>
							<td>
								<table width=100% cellpadding=2 cellspacing=0 border=0>
									<tr>
										<td align=right width=25%>
											<b>
												<font class=afontstyle>&nbsp;Date Range&nbsp;:</font>
											</b>
										</td>
							            <td>&nbsp;&nbsp;
							            	<font class=afontstyle>
							            		<div class="dateFilters">
							            			<input type="text" class="datePeriod" name="date_range_weekS" id="date_range_weekS" value="<?php echo $startdateW;?>" readonly>
                                					<input type="text" class="datePeriod" name="date_range_weekE" id="date_range_weekE" value="<?php echo $enddateW;?>" readonly>
                                				</div>
                                				<!-- <div class="akkenPopupBtnBot1">
                                					<a class="akkenPopupBtn" href="javascript:void(0);" onclick="doSearchFilters();" title="View Results"><i class="fa fa-eye"></i>View</a>		
                                				</div> -->	            		
							            	</font>
							            </td>
									</tr>

								</table>
							</td>
						</tr>
					</div>
				</table>
				<table width=100% cellpadding=2 cellspacing=0 border=0 class="ProfileNewUI">
					<tr>
						<td>
							<table width=90% cellpadding=2 cellspacing=0 border=0 align="center" class="jobTmpCls">
								<tr>
									<?php echo $dayStr;?>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<div style="margin-top: 5px; font-size:13px;">
								<div style="clear: both; float: left; width: 100%;margin-left:5%;">
									
									<div>
										<div style="background: #04b431 none repeat scroll 0px 0px; float: left; height: 15px; width: 15px; border: 1px solid; margin: 2px; overflow: hidden;" title="Green"></div>
										<span class="afontstyle" style="width:90%; float: left; margin-top: 2px;">
											(Green): Counts in green represent number of active assignments with that start date.
										</span>
									</div>
									<div style="clear:both"></div>
									<div>
										<div style="background: #ff0000 none repeat scroll 0px 0px; float: left; height: 15px; width: 15px; border: 1px solid; margin: 2px; overflow: hidden;" title="Red"></div>
										<span class="afontstyle" style="width:90%; float: left; margin-top: 2px;">
											(Red): Counts in Red represent number of active assignments what that end date.
										</span>
									</div>
									<div style="clear:both"></div>
									<div>										
										<span class="afontstyle" style="width:90%; float: left; margin-top: 2px;">
										(Date Range): Click on the calendar icon to change dates. Widget will display the whole week for the selected date.
										</span>
									</div>
									<div style="clear:both"></div>
								</div>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</div>
	</form>
</body>
</html>
