<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<table width="100%"  border="0" cellspacing="2" cellpadding="2">
  <tr class="NewGridTopBg">
	<?php
	$name=explode("|","fa fa-play~Run&nbsp;Report|fa fa-times~Close");
	if($main=="main")
	$link=explode("|","javascript:chkReport()|javascript:chkClose()");
	else
	$link=explode("|","javascript:chkReport()|javascript:window.close()");
	$heading="";
	$menu->showHeadingStrip1($name,$link,$heading);
	?> 
  </tr>
	<?php
	if($dcheck=="")
	{
        $vis="";
		$vis1="";
		$disable2="disabled";
    }
	else
	{
        $vis="<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 >";
		$vis1="<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 >";
		$disable2="";
    }

	?>
  <tr>
	<td>
	<fieldset>
	<legend><font class=afontstyle>Filters</font></legend>
	
	<table width=100% cellpadding=3 cellspacing=0 border=0 id="filter_table" class="ProfileNewUI">
	 
	 <tbody >
	<tr id="filter_message"><td>&nbsp;</td>
	<td colspan="2" class="">Select the required options from the Available Columns displayed below and click on Run Report to generate the Report</td></tr>
		
		
		<?php
		  getFilters($filternames,$filtervalues,$deptAccesSno);
		?>
		
	  </tbody>
   </table>	 
	
	
	<!--
	<table width=100% cellpadding=3 cellspacing=0 border=0>
	  <tr>
	    <td>&nbsp;</td>
	    <td><font class=afontstyle>Select Candidate Type</font></td>
	    <td>
        <select name=candtype class=drpdwne>
        <option value=All>All</option>
        <option  <?php //echo sele("Employee",$opt);?> value="Employee">Employee</option>
        <option <?php //echo sele("MyConsultants",$opt);?> value="MyConsultants">MyConsultants</option>
        <option <?php //echo sele("Consultants",$opt);?> value="Consultants">Consultants</option>
        <option <?php //echo sele("MyContractors",$opt);?> value="MyContractors">MyContractors</option>
        <option <?php //echo sele("Contractors",$opt);?> value="Contractors">Contractors</option>
        </select>
        </td>
   	 </tr>	 
	 
	  <tr>
		<td colspan=2><font class=bstrip>&nbsp;</font></td>
	  </tr>
	</table>-->
	</fieldset>
	</td>
  </tr>

  <tr>
	<td colspan=2><font class=bstrip>&nbsp;</font></td>
  </tr>
  <tr class="NewGridBotBg">
	<?php
	$name=explode("|","fa fa-play~Run&nbsp;Report|fa fa-times~Close");
	if($main=="main")
	$link=explode("|","javascript:chkReport()|javascript:chkClose()");
	else
	$link=explode("|","javascript:chkReport()|javascript:window.close()");
	$heading="";
	$menu->showHeadingStrip1($name,$link,$heading);
	?>
  </tr>

</table>

