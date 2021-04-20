<?php
   /* 
	Modifed Date : Jan 01, 2016.
	Modified By  : Rajesh kumar v	
	Purpose      : Introducing Credit Memos / Adjustments / Write-Off / Discount / VMS Fees / Bad Debts Entries in Account Receivables Module.
	TS Task Id   : Wright Off/Adjustments enhancement.
   
	Modifed Date : Oct 05, 2015.
	Modified By  : Rajesh kumar v
	Purpose      : changed the calendar.js path for fixing the old calendar issue
	TS Task Id   :  AKKEN_7_5_0_765(Receive Payments Issue).

   	Modifed Date : Sept 16, 2010.
	Modified By  : Harikrishna Srinivas.K
	Purpose      : removed ($) symbol for amount
	TS Task Id   : 5268.
	
   	Modifed Date : Sep 24, 2009.
	Modified By  : Kumar Raju k.
	Purpose      : Displying customers with ids in customer invoices and payments.
	TS Task Id   : 4621, (Accounts Enh) Need to show Customers along with id in all places where we are displaying customers in application.
	
   	Modified By: Praveen.P
	Modified Date: 10/07/09
	Purpose: Displaying Bank and Asset Accounts in Deposit drop down box
	Task ID: 4480
   */
   	require("global.inc");
	require("Cemplist.php");
	require("Menu.inc");
	$menu=new EmpMenu();
	$sqldb=new Emplist;
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	if(!isSet($client))
	{
		$client="all";
		$banme="sel";
	}
	
	//Query to get the  Customer's name to display in customers dropdown
	$qu = "SELECT DISTINCT(invoice.client_name), staffacc_cinfo.cname, ".getEntityDispName('staffacc_cinfo.sno', 'staffacc_cinfo.cname', 1)." 
	FROM invoice 
	LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=invoice.client_name AND staffacc_cinfo.type IN ('CUST','BOTH')
	LEFT JOIN Client_Accounts ca ON (ca.typeid=staffacc_cinfo.sno)
	WHERE deliver='yes' AND invoice.billed='no' AND invoice.status = 'ACTIVE' AND ca.deptid !='0' AND ca.deptid IN(".$deptAccesSno.") ORDER BY staffacc_cinfo.cname";
 	$res=mysql_query($qu,$db);
	$bpay=mysql_num_rows($res);

   	$coptions="<option value=all ".sele('all',$client).">-- Select a Customer --</option>";

	while($dd=mysql_fetch_row($res))
	{
		$coptions.="<option value='".$dd[0]."' ".sele($dd[0],$client).">".stripslashes($dd[2])."</option>";  
	}
		
	function sele($a,$b)
	{
        if($a==$b)
            return "selected";
        else
            return "";
	}
		
    if($client!="all")
    {
    	if(!isSet($val))
    	{
            $thisday2=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
            $servicedateto=date("m/d/Y",$thisday2);
    	}
    	else
    	{
    	   $servicedateto=date("m/d/Y",$t1);
    	}
    }
	$menu->showHeader("accounting","Customers","4|5");
?>
<script src="/BSOS/Accounting/suppliers/scripts/validatecredit.js" language="javascript"></script>
<script language="javascript" src="scripts/validateinhis.js"></script>
<script src=/BSOS/scripts/date_format.js language=javascript></script>

<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<script language="JavaScript" src="/include/custom/calendar.js"></script>
<style type="text/css">
	.tr2bgcolor{background-color: #f6f7f9 !important;}
</style>
<form name="sheet" id="sheet" action="navinvoice.php" method="POST">
<input type=hidden id="addr" name="addr" />
<input type=hidden id="aa" name="aa" />
<input type=hidden id="oldvalue" name="oldvalue" />
<input type=hidden id="adjustment_str" name="adjustment_str" />
<input type=hidden id="check_str" name="check_str" />
<input type=hidden id="t1" name="t1" />
<input type=hidden id="decimalPref" name="decimalPref" value="<?php echo $decimalPref; ?>" />

<div id="main">
<td valign=top align=center class=tbldata>
<table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI defaultTopRange">
    <div id="content">
    <tr>
    <td class="titleNewPad">
    <table width=100% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
        <tr>
            <td align=left><font class=modcaption>&nbsp;Receive&nbsp;Payments</font></td>
            <?php
            if($client!="all")
            {
            ?>
            <td align=right><font class=afontstyle>&nbsp;</font></td>
            <td align=right>
		<font class=afontstyle>Payment&nbsp;Date&nbsp;&nbsp;
			<input type=text size=10  maxlength="10" readonly id="servicedate" name=servicedate value="<?php echo $servicedateto;?>" />
			<script language="JavaScript"> new tcal ({'formname':window.form,'controlname':'servicedate'});</script>
		</font>&nbsp;&nbsp;&nbsp;
	    </td>
            <?php
            }
            ?>
        </tr>
        <tr>
			<td colspan=3><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
            <td width=50%>&nbsp;<font class=afontstyle>Select a Customer&nbsp;</font>
            <select name=client class=drpdwnacc onChange="javascript:doClient1()">
            <?php
                print $coptions;
            ?>
            </select>
            </td>
					    <td align=right colspan=2>
						<font class=afontstyle>Deposit To<font class=sfontstyle>*</font>&nbsp;
						<select name="bname" style="width:220px;" class=drpdwnacc>
						<option value="sel">-- Select a Bank --</option>
			<?php 
			$optionvalues =  accountsSelBox($bname,$filters = array('BANK','FIXASSET'));	
			foreach($optionvalues as $options)
			{
				echo $options;
			}	 
			?>
						</select>
						</font>&nbsp;&nbsp;&nbsp;
					    </td>
        </tr>
        <tr>
			<td colspan=3><font class=bstrip>&nbsp;</font></td>
		</tr>
		<tr>
            <td width=40%>&nbsp;<font class=afontstyle>Memo&nbsp;&nbsp;<input type=text name=memo size=45 /></font>
            </td>            
        </tr>
    </table>
    </td>
    </tr>
    </div>
		
	<div id="topheader">
	<tr class="NewGridTopBg">
	<?php
	    if($bpay<1)
	    {
		$name=explode("|","fa-credit-card~Receive&nbsp;Payments&nbsp;Register");
		$link=explode("|","javascript:doRecPR()");
		}
		else
		{
			if($client!="all")
			{
	 			$name=explode("|","fa-credit-card~Receive&nbsp;Pay");
				$link=explode("|","javascript:doSave()");			
			}
//			else
//			{
//	 			$name=explode("|","fa-credit-card~Receive&nbsp;Payments&nbsp;Register");
//				$link=explode("|","javascript:doRecPR()");
//			}	
		}
		$heading="";
		$menu->showHeadingStrip1($name,$link,$heading,"left");
		
	?>
	</tr>
	</div>

    <div id="grid_form">
    <tr>
    <?php

    if($client!="all")
    {
        print "<td align=center>";
        
		//Do not show the records with total amount less than zero.
		$query="SELECT sno,client_name,".tzRetQueryStringSTRTODate('invoice_date','%m/%d/%Y','Date','/')." AS invoice_date,
				".tzRetQueryStringSTRTODate('due_date','%m/%d/%Y','Date','/')." AS due_date,deposit,deliver,billed,total,invoice_number,STR_TO_DATE(IF(invoice_date = '0-0-0', '00-00-0000', invoice_date),
                      '%m/%d/%Y') AS get_invoice_date 
				FROM invoice 
				WHERE deliver='yes' 
				AND billed='no'  
				AND status = 'ACTIVE'  
				AND total > 0 
				AND client_name='".$client."' ORDER BY get_invoice_date ASC";

        $message="No Invoices are available for this Customer.";
        $headtitle="<label class='container-chk'><input type=checkbox name=ck id='ck' value=1 onClick=kev() tabindex=3 /><span class='checkmark'></span></label>|Invoice&nbsp;No.|".getEntityDispHeading('ID', 'Customer Name', 1)."|Invoice&nbsp;Date|Due&nbsp;Date|Original&nbsp;Amount|Amount&nbsp;Due|Payment|Adjustments&nbsp;Type|Adjustment&nbsp;Amount|Balance|Payment&nbsp;Method|Check&nbsp;Number";
        $limit=20;
        if(!isSet($page))
        {
            $show=$sqldb->DisplayInvoiceDeliver($query,$db,$offset,$limit,$page,$PHP_SELF,$headtitle,$acctype,$message,$client,$decimalPref);
        }
        else
        {
            if($page>1)
                $offset=intval($limit*($page-1));
            else
                $offset=0;
            $show=$sqldb->DisplayInvoiceDeliver($query,$db,$offset,$limit,$page,$PHP_SELF,$headtitle,$acctype,$message,$client,$decimalPref);
        }
        print $show;
    }
    else
    {
        print "<td class=tr2bgcolor align=center>";
        if($bpay<1)
            print "<font class=afontstyle>&nbsp;No Customer Receivables available.</font>";
        else
            print "<font class=afontstyle>&nbsp;Please select a Customer to list Receive Payments details.</font>";
    }
    ?>
    </td>
    </tr>
    </div>
		
	<div id="botheader">
	<tr class="NewGridBotBg">
	<?php
	   if($bpay<1)
	    {
    		$name=explode("|","fa-credit-card~Receive&nbsp;Payments&nbsp;Register");
    		$link=explode("|","javascript:doRecPR()");
		}
		else
		{
			if($client!="all")
			{
	 			$name=explode("|","fa-credit-card~Receive&nbsp;Pay");
				$link=explode("|","javascript:doSave()");
			}
					//else
					//{
					//	$name=explode("|","new.gif~Receive&nbsp;Payments&nbsp;Register");
					//	$link=explode("|","javascript:doRecPR()");
					//}
		}
		$heading="user.gif~Receive&nbsp;Payments";
		//$menu->showHeadingStrip1($name,$link,$heading);
	?>
	</tr>
	</div>
		
</table>
</td>
</div>

<?php
	$menu->showFooter();
?>
</form>
</body>
</html>