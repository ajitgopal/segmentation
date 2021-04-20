<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<table width="100%"  border="0" cellspacing="2" cellpadding="2">
  <tr class="NewGridTopBg">
	<?php
	$name=explode("|","fa fa-play~Run&nbsp;Report|fa fa-file-excel-o~Export&nbsp;to &nbsp;CSV|fa fa-times~Close");
	if($main=="main")
	$link=explode("|","javascript:chkReport()|javascript:chkReportCSV()|javascript:chkClose()");
	else
	$link=explode("|","javascript:chkReport()|javascript:chkReportCSV()|javascript:window.close()");
	$heading="";
	$menu->showHeadingStrip1($name,$link,$heading);
	?>
  </tr>
  <tr>
	<td>
	<fieldset>
	<legend><font class=afontstyle>Filters</font></legend>

	<table width=100% cellpadding=3 cellspacing=0 border=0 id="filter_table" class="ProfileNewUI">
	<tbody >
	<tr id="filter_message"><td width="5%">&nbsp;</td>
	<td colspan="2" class="">Select the required options from the Available Columns displayed below and click on Run Report to generate the Report</td></tr>
		
		
		<?php
		  getFilters($filternames,$filtervalues,$deptAccesSno);
		?>
		
	  </tbody>
	</table>
	</fieldset>
	</td>
  </tr>
  <tr>
	<td colspan=2><font class=bstrip>&nbsp;</font></td>
  </tr>
  <tr class="NewGridBotBg">
	<?php
	$name=explode("|","fa fa-play~Run&nbsp;Report|fa fa-file-excel-o~Export&nbsp;to &nbsp;CSV|fa fa-times~Close");
	if($main=="main")
	$link=explode("|","javascript:chkReport()|javascript:chkReportCSV()|javascript:chkClose()");
	else
	$link=explode("|","javascript:chkReport()|javascript:chkReportCSV()|javascript:window.close()");
	$heading="";
	$menu->showHeadingStrip1($name,$link,$heading);
	?>
  </tr>

</table>



