<table width="100%"  border="0" cellspacing="2" cellpadding="2">
  <tr>
	<?php
	$name=explode("|","document.gif~Run&nbsp;Report|close.gif~Close");
	if($main=="main")
	$link=explode("|","javascript:chkReport()|javascript:chkClose()");
	else
	$link=explode("|","javascript:chkReport()|javascript:window.close()");
	$heading="";
	$menu->showHeadingStrip1($name,$link,$heading);
	?>
  </tr>
	<?php
	if($ccheck=="")
	{
        $vis="";
		$vis1="";
		$disable="disabled";
    }
	else
	{
        $vis="<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 >";
		$vis1="<img src=/BSOS/images/calendar.gif width=18 height=16 border=0 >";
		$disable="";
    }
    $thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$fromdates=date("m/d/Y",$thisday);
 	$dateran=$fromdates;
	?>
  <tr>
	<td>
	<fieldset>
	<legend><font class=afontstyle>Customize</font></legend>
	<table width=100% cellpadding=3 cellspacing=0 border=0>
		<tr>
		<?php
		$query="select distinct(emp_list.sno),hrcon_general.fname,hrcon_general.mname,hrcon_general.lname from emp_list LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username LEFT JOIN hrcon_jobs ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username LEFT JOIN department ON hrcon_compen.dept=department.sno where hrcon_w4.ustatus='active' and hrcon_jobs.ustatus='active' and hrcon_general.ustatus='active' and emp_list.lstatus != 'DA'  and  emp_list.lstatus != 'INACTIVE'  and department.sno !='0' AND department.sno IN ({$deptAccesSno})  group by emp_list.sno";
		$res=mysql_query($query,$db);
		?>
			<td ><font class="afontstyle">&nbsp;Select Candidate</font></td>
			<td><font class="afontstyle">
            <select name="selcand">
             <option value=all>All</option>
			<?php
				while($row=mysql_fetch_row($res))
				echo "<option value=".$row[0]." ".sele($selcand,$row[0]).">".$row[1]." ".$row[3]."</option>";
			?>
				</select></font></td>
		</tr>
      <tr>
        <!--<td width="15%">&nbsp;</td>-->
        <td ><input name=cccdate type=checkbox <?php echo $ccheck;?> onClick="getDatSel(document.form1.cccdate,document.form1.daterange,document.form1.csmonth,document.form1.csdate,document.form1.csyear,document.form1.ctmonth,document.form1.ctdate,document.form1.ctyear,'<?php echo $dateran; ?>')"><font class=afontstyle>Profit / Loss in the range of</font></td>
        <td > <font class=afontstyle>
        <!--<input type="hidden" name="b">-->
          <select name=daterange onChange='setRange(this.form)' class=drpdwne <?php echo $disable;?>>
            <option value="">Select Date Range</option>
            <!--<option <?php echo sele("daily",$dateopt);?> value="daily">Daily</option>-->
            <option  <?php echo sele("biweek",$dateopt);?> value="biweek">Bi Weekly</option>
            <option <?php echo sele("Weekly",$dateopt);?> value="Weekly">Weekly</option>
            <option <?php echo sele("bimonth",$dateopt);?> value="bimonth">Bi Monthly</option>
            <option <?php echo sele("month",$dateopt);?> value="month">Monthly</option>
            <option <?php echo sele("quarter",$dateopt);?> value="quarter">Quarterly</option>
            <option <?php echo sele("halfyear",$dateopt);?> value="halfyear">HalfYearly</option>
            <option <?php echo sele("year",$dateopt);?> value="year">Yearly</option>
          </select>
        </font> </td>
        </tr>
                 <?php
					 if($fromdate=="")
					 {
						  $thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
						  $todate=date("m/d/Y",$thisday);
						  $date=explode('/',$todate);
						  $date1=explode('/',$todate);
					}
					else
					{
						$date=$fromdate;
						$date1=$todate;
					}
			     ?>
              <tr>
					<td ><font class=afontstyle>&nbsp;From:&nbsp;</font>
					
					<select name="csmonth" class=drpdwne <?php echo $disable;?> onFocus=changedate();>
					<option value="0">Month</option>
					<option <?php echo sele("01",$date[0]);?> value="01">January</option>
					<option <?php echo sele("02",$date[0]);?> value="02">February</option>
					<option <?php echo sele("03",$date[0]);?> value="03">March</option>
					<option <?php echo sele("04",$date[0]);?> value="04">April</option>
					<option <?php echo sele("05",$date[0]);?> value="05">May</option>
					<option <?php echo sele("06",$date[0]);?> value="06">June</option>
					<option <?php echo sele("07",$date[0]);?> value="07">July</option>
					<option <?php echo sele("08",$date[0]);?> value="08">August</option>
					<option <?php echo sele("09",$date[0]);?> value="09">September</option>
					<option <?php echo sele("10",$date[0]);?> value="10">October</option>
					<option <?php echo sele("11",$date[0]);?> value="11">November</option>
					<option <?php echo sele("12",$date[0]);?> value="12">December</option>
					</select>
					<select name="csdate" class=drpdwne <?php echo $disable;?> onFocus=changedate();>
					<option value="0">Day</option>
					<option <?php echo sele("01",$date[1]);?> value="01">01</option>
					<option <?php echo sele("02",$date[1]);?> value="02">02</option>
					<option <?php echo sele("03",$date[1]);?> value="03">03</option>
					<option <?php echo sele("04",$date[1]);?> value="04">04</option>
					<option <?php echo sele("05",$date[1]);?> value="05">05</option>
					<option <?php echo sele("06",$date[1]);?> value="06">06</option>
					<option <?php echo sele("07",$date[1]);?> value="07">07</option>
					<option <?php echo sele("08",$date[1]);?> value="08">08</option>
					<option <?php echo sele("09",$date[1]);?> value="09">09</option>
					<option <?php echo sele("10",$date[1]);?> value="10">10</option>
					<option <?php echo sele("11",$date[1]);?> value="11">11</option>
					<option <?php echo sele("12",$date[1]);?> value="12">12</option>
					<option <?php echo sele("13",$date[1]);?> value="13">13</option>
					<option <?php echo sele("14",$date[1]);?> value="14">14</option>
					<option <?php echo sele("15",$date[1]);?> value="15">15</option>
					<option <?php echo sele("16",$date[1]);?> value="16">16</option>
					<option <?php echo sele("17",$date[1]);?> value="17">17</option>
					<option <?php echo sele("18",$date[1]);?> value="18">18</option>
					<option <?php echo sele("19",$date[1]);?> value="19">19</option>
					<option <?php echo sele("20",$date[1]);?> value="20">20</option>
					<option <?php echo sele("21",$date[1]);?> value="21">21</option>
					<option <?php echo sele("22",$date[1]);?> value="22">22</option>
					<option <?php echo sele("23",$date[1]);?> value="23">23</option>
					<option <?php echo sele("24",$date[1]);?> value="24">24</option>
					<option <?php echo sele("25",$date[1]);?> value="25">25</option>
					<option <?php echo sele("26",$date[1]);?> value="26">26</option>
					<option <?php echo sele("27",$date[1]);?> value="27">27</option>
					<option <?php echo sele("28",$date[1]);?> value="28">28</option>
					<option <?php echo sele("29",$date[1]);?> value="29">29</option>
					<option <?php echo sele("30",$date[1]);?> value="30">30</option>
					<option <?php echo sele("31",$date[1]);?> value="31">31</option>
					</select>
					<select name="csyear" class=drpdwne <?php echo $disable;?> onFocus=changedate();>
					<option value="0">Year</option>
					<option <?php echo sele("2000",$date[2]);?> value="2000">2000</option>
					<option <?php echo sele("2001",$date[2]);?> value="2001">2001</option>
					<option <?php echo sele("2002",$date[2]);?> value="2002">2002</option>
					<option <?php echo sele("2003",$date[2]);?> value="2003">2003</option>
					<option <?php echo sele("2004",$date[2]);?> value="2004">2004</option>
					<option <?php echo sele("2005",$date[2]);?> value="2005">2005</option>
					<option <?php echo sele("2006",$date[2]);?> value="2006">2006</option>
					<option <?php echo sele("2007",$date[2]);?> value="2007">2007</option>
					<option <?php echo sele("2008",$date[2]);?> value="2008">2008</option>
					<option <?php echo sele("2009",$date[2]);?> value="2009">2009</option>
					<option <?php echo sele("2010",$date[2]);?> value="2010">2010</option>
					<option <?php echo sele("2011",$date[2]);?> value="2011">2011</option>
					<option <?php echo sele("2012",$date[2]);?> value="2012">2012</option>
					<option <?php echo sele("2013",$date[2]);?> value="2013">2013</option>
					<option <?php echo sele("2014",$date[2]);?> value="2014">2014</option>
					<option <?php echo sele("2015",$date[2]);?> value="2015">2015</option>
					<option <?php echo sele("2016",$date[2]);?> value="2016">2016</option>
					<option <?php echo sele("2017",$date[2]);?> value="2017">2017</option>
					<option <?php echo sele("2018",$date[2]);?> value="2018">2018</option>
					<option <?php echo sele("2019",$date[2]);?> value="2019">2019</option>
					<option <?php echo sele("2020",$date[2]);?> value="2020">2020</option>
					</select>&nbsp;<a href=javascript:DateSelector1("customfrom") ID="x" onFocus=changedate();><?php echo $vis;?></a>
					</td>
					
					<td ><font class=afontstyle>&nbsp;To:&nbsp;
					<select name="ctmonth" class=drpdwne <?php echo $disable;?> onFocus=changedate();>
					<option value="0">Month</option>
					<option <?php echo sele("01",$date1[0]);?> value="01">January</option>
					<option <?php echo sele("02",$date1[0]);?> value="02">February</option>
					<option <?php echo sele("03",$date1[0]);?> value="03">March</option>
					<option <?php echo sele("04",$date1[0]);?> value="04">April</option>
					<option <?php echo sele("05",$date1[0]);?> value="05">May</option>
					<option <?php echo sele("06",$date1[0]);?> value="06">June</option>
					<option <?php echo sele("07",$date1[0]);?> value="07">July</option>
					<option <?php echo sele("08",$date1[0]);?> value="08">August</option>
					<option <?php echo sele("09",$date1[0]);?> value="09">September</option>
					<option <?php echo sele("10",$date1[0]);?> value="10">October</option>
					<option <?php echo sele("11",$date1[0]);?> value="11">November</option>
					<option <?php echo sele("12",$date1[0]);?> value="12">December</option>
					</select>
					<select name="ctdate" class=drpdwne <?php echo $disable;?> onFocus=changedate();>
					<option value="0">Day</option>
					<option <?php echo sele("01",$date1[1]);?> value="01">01</option>
					<option <?php echo sele("02",$date1[1]);?> value="02">02</option>
					<option <?php echo sele("03",$date1[1]);?> value="03">03</option>
					<option <?php echo sele("04",$date1[1]);?> value="04">04</option>
					<option <?php echo sele("05",$date1[1]);?> value="05">05</option>
					<option <?php echo sele("06",$date1[1]);?> value="06">06</option>
					<option <?php echo sele("07",$date1[1]);?> value="07">07</option>
					<option <?php echo sele("08",$date1[1]);?> value="08">08</option>
					<option <?php echo sele("09",$date1[1]);?> value="09">09</option>
					<option <?php echo sele("10",$date1[1]);?> value="10">10</option>
					<option <?php echo sele("11",$date1[1]);?> value="11">11</option>
					<option <?php echo sele("12",$date1[1]);?> value="12">12</option>
					<option <?php echo sele("13",$date1[1]);?> value="13">13</option>
					<option <?php echo sele("14",$date1[1]);?> value="14">14</option>
					<option <?php echo sele("15",$date1[1]);?> value="15">15</option>
					<option <?php echo sele("16",$date1[1]);?> value="16">16</option>
					<option <?php echo sele("17",$date1[1]);?> value="17">17</option>
					<option <?php echo sele("18",$date1[1]);?> value="18">18</option>
					<option <?php echo sele("19",$date1[1]);?> value="19">19</option>
					<option <?php echo sele("20",$date1[1]);?> value="20">20</option>
					<option <?php echo sele("21",$date1[1]);?> value="21">21</option>
					<option <?php echo sele("22",$date1[1]);?> value="22">22</option>
					<option <?php echo sele("23",$date1[1]);?> value="23">23</option>
					<option <?php echo sele("24",$date1[1]);?> value="24">24</option>
					<option <?php echo sele("25",$date1[1]);?> value="25">25</option>
					<option <?php echo sele("26",$date1[1]);?> value="26">26</option>
					<option <?php echo sele("27",$date1[1]);?> value="27">27</option>
					<option <?php echo sele("28",$date1[1]);?> value="28">28</option>
					<option <?php echo sele("29",$date1[1]);?> value="29">29</option>
					<option <?php echo sele("30",$date1[1]);?> value="30">30</option>
					<option <?php echo sele("31",$date1[1]);?> value="31">31</option>
					</select>
					<select name="ctyear" class=drpdwne <?php echo $disable;?> onFocus=changedate();>
					<option value="0">Year</option>
					<option <?php echo sele("2000",$date1[2]);?> value="2000">2000</option>
					<option <?php echo sele("2001",$date1[2]);?> value="2001">2001</option>
					<option <?php echo sele("2002",$date1[2]);?> value="2002">2002</option>
					<option <?php echo sele("2003",$date1[2]);?> value="2003">2003</option>
					<option <?php echo sele("2004",$date1[2]);?> value="2004">2004</option>
					<option <?php echo sele("2005",$date1[2]);?> value="2005">2005</option>
					<option <?php echo sele("2006",$date1[2]);?> value="2006">2006</option>
					<option <?php echo sele("2007",$date1[2]);?> value="2007">2007</option>
					<option <?php echo sele("2008",$date1[2]);?> value="2008">2008</option>
					<option <?php echo sele("2009",$date1[2]);?> value="2009">2009</option>
					<option <?php echo sele("2010",$date1[2]);?> value="2010">2010</option>
					<option <?php echo sele("2011",$date1[2]);?> value="2011">2011</option>
					<option <?php echo sele("2012",$date1[2]);?> value="2012">2012</option>
					<option <?php echo sele("2013",$date1[2]);?> value="2013">2013</option>
					<option <?php echo sele("2014",$date1[2]);?> value="2014">2014</option>
					<option <?php echo sele("2015",$date1[2]);?> value="2015">2015</option>
					<option <?php echo sele("2016",$date1[2]);?> value="2016">2016</option>
					<option <?php echo sele("2017",$date1[2]);?> value="2017">2017</option>
					<option <?php echo sele("2018",$date1[2]);?> value="2018">2018</option>
					<option <?php echo sele("2019",$date1[2]);?> value="2019">2019</option>
					<option <?php echo sele("2020",$date1[2]);?> value="2020">2020</option>
					</select>&nbsp;<a href=javascript:DateSelector1("customto")  ID="x1" onFocus=changedate();><?php echo $vis1;?></a>
					</td>

      </tr>
		  <tr><td colspan=2><font class=afontstyle>&nbsp;</td>
			<td colspan=2><font class=afontstyle>&nbsp;&nbsp;</td>
  	  </tr>
      <tr>
        <td colspan=2><font class=bstrip>&nbsp;</font></td>
      </tr>
    </table>
	</fieldset>
	</td>
  </tr>
  <tr>
	<td colspan=2><font class=bstrip>&nbsp;</font></td>
  </tr>
  <tr>
	<?php
	$name=explode("|","document.gif~Run&nbsp;Report|close.gif~Close");
	if($main=="main")
	$link=explode("|","javascript:chkReport()|javascript:chkClose()");
	else
	$link=explode("|","javascript:chkReport()|javascript:window.close()");
	$heading="";
	$menu->showHeadingStrip1($name,$link,$heading);
	?>
  </tr>

</table>
