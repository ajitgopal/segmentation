<?php
	require("global.inc");
	//require("global_fun.inc");
	require("Menu.inc");

	$deptAccessObj = new departmentAccess();
	$deptAccesSno_BO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$menu=new EmpMenu();
	//$menu->showHeader("collaboration","Task Manager","6");
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todate=date("m/d/Y",$thisday);
	
	//remindMe and completeddate year, 20 years for past years and 10 years to future.
	$remindMeYear = $completedYear = displayPastFutureYears();

	//Assign Link only for Internal Direct Employees
    	$que="SELECT credit_id,credit_source,credit_inv_bill_id,credit_amount,credit_type,credit_used_status,
		".tzRetQueryStringDTime('credit_cdate','DateTime','/').",credit_notes,credit_memo_trans.used_amount,staffacc_cinfo.cname,credit_desc,credit_muser,credit_mdate,invoice.invoice_date,invoice.total,invoice.invoice_number,emp_list.name,emp_id,line_type FROM credit_memo 
		LEFT JOIN credit_memo_trans ON  (credit_memo_trans.credit_memo_sno = credit_memo.credit_id  AND credit_memo_trans.type='invoice') 
		LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = credit_memo.credit_source 
		LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=staffacc_cinfo.sno) 
		LEFT JOIN invoice ON invoice.sno = credit_memo.credit_inv_bill_id
		LEFT Join emp_list ON emp_list.sno = credit_memo.emp_id
		where credit_id='$creditid' AND credit_used_status='N' AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") ";
		
	$rs=mysql_query($que,$db);
	$arr=mysql_fetch_row($rs);
	//$paidamt=$arr[3]+$arr[14];
	$quepaid = "SELECT SUM(amount) FROM bank_trans WHERE REPLACE(source,'inv','') = $arr[2]";
	$rspaid=mysql_query($quepaid,$db);
	$pai=mysql_fetch_row($rspaid);
	$paidamt=$pai[0];
	$stat=$arr[0];
	$query = "SELECT sc.sno,sc.cname FROM staffacc_cinfo sc LEFT JOIN Client_Accounts ON (Client_Accounts.typeid=sc.sno)  WHERE sc.type='CUST' AND Client_Accounts.deptid !='0' AND Client_Accounts.deptid IN(".$deptAccesSno_BO.") GROUP BY sc.sno ORDER BY sc.cname ";
	$qrs=mysql_query($query,$db);
	if(empty($arr[8])){
		$read_only = '';
	} else {
		$read_only = 'readonly style="background-color:#DCDCDC"';
	}
	if($arr[7]!=='Available Credits'){
		$read_only = 'readonly style="background-color:#DCDCDC"';
	}
	function getSels($a,$b)
	{
		print_r($a);
		
		if(strtolower($a) == strtolower($b))
			return "selected";
		else
			return "";
	}
	
?>
<html>
<head>

<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">

<style type="text/css">
.summarytext select{width: 250px !important;}
.summarytext select[name="rmonth"]{ width:100px !important}
.summarytext select[name="rday"]{width:60px !important}
.summarytext select[name="ryear"]{ width:70px !important}
.summarytext select[name="ctime"]{ width:90px !important}
#custid{width:250px;}
#custid option{width:250px;}
</style>




<script type= "text/javascript">
function validate(){
	var creditamt = document.getElementById('creditamt').value;
	var invpaid = document.getElementById('invpaid').innerText;
	var custid = document.getElementById('custid').value;
	if(invpaid!=''){
		/* if(Number(creditamt)>Number(invpaid))
		{
			alert("Credit amount is greater than Invoice amount");
			return
		}  */
	}
	if(custid==''){
		alert("Please select Customer");
	   return
	}
	if(creditamt==''){
		alert("Enter Credit Amount");
	   return
	} else {
		document.getElementById("editcred").submit();
	}

}
function validateedit(){
	var creditamt = document.getElementById('creditamts').value;
	var custid = document.getElementById('custid').value;
	/* var invpaid = document.getElementById('invpaid').innerText;
	if(invpaid!=''){
		if(Number(creditamt)>Number(invpaid))
		{
			alert("Credit amount is greater than Invoice amount");
			return
		} 
	} */
	if(custid==''){
		alert("Please select Customer");
	   return
	}
	if(creditamt==''){
		alert("Enter Credit Amount");
	   return
	} else {
		document.getElementById("editcred").submit();
		
	}
}
function updateInvList()
{
	var form=document.editcred;
	cusid = form.custid.value;
	invid = '';
	DynCls_Ajax_result("filterInv.php?custid="+cusid+"&invid="+invid,"rtype","updateDept","refreshInvList()");
}
function refreshInvList()
{
	
	var form=document.editcred;
	var invno = form.invno;
	var empname = form.empname;
	var linetype = form.linetype;
	var current_ctrl = document.getElementById('invno');
	var empname = document.getElementById('empname');
	var linetype = document.getElementById('linetype');
	linetype.length=0;
    var oOptionlt = document.createElement("option");
	oOptionlt.appendChild(document.createTextNode("Select"));
	oOptionlt.setAttribute("value","");
	linetype.appendChild(oOptionlt);
	empname.length=0;
    var oOptionemp = document.createElement("option");
	oOptionemp.appendChild(document.createTextNode("Select"));
	oOptionemp.setAttribute("value","");
	empname.appendChild(oOptionemp);
	invno.length=0;
    var oOption = document.createElement("option");
	oOption.appendChild(document.createTextNode("Select"));
	oOption.setAttribute("value","");
	current_ctrl.appendChild(oOption);
	
	

	if(DynCls_Ajx_responseTxt!="")
	{
		sdept = DynCls_Ajx_responseTxt.split("|akkenCSplit|");
		for(i=0;i<sdept.length;i++)
		{
			ssdept = sdept[i].split("|akkenPSplit|");
			var oOption = document.createElement("option");
			oOption.appendChild(document.createTextNode(ssdept[1]));
			oOption.setAttribute("value",ssdept[0]);
			current_ctrl.appendChild(oOption);
		}
	}
	document.getElementById('invnum').innerText = "";
	document.getElementById('invdate').innerText = "";
	document.getElementById('invamt').innerText = "";
}
function updateInvDet()
{
	var form=document.editcred;
	invid = form.invno.value;
	cusid = '';
	empid = '';
	DynCls_Ajax_result("filterInv.php?custid="+cusid+"&invid="+invid+"&empid="+empid,"rtype","updateInv","refreshInvDet()");
}
function refreshInvDet()
{
	//sdept = DynCls_Ajx_responseTxt.split("|akkenCSplit|");
	sddept = DynCls_Ajx_responseTxt.split("|akkenESplit|");
	
	
	sdept = sddept[0].split("|akkenCSplit|");
	
	if(sdept == ''){
		document.getElementById('invnum').innerText = "";
		document.getElementById('invdate').innerText = "";
		document.getElementById('invamt').innerText = "";
		document.getElementById('invpaid').innerText = "";
		//document.getElementById("invdetails").style.display = "none";
	} else {
		document.getElementById('invnumedt').innerText = "";
		document.getElementById('invdateedt').innerText = "";
		document.getElementById('invamtedt').innerText = "";
		document.getElementById('invpaidedt').innerText = "";
	
		document.getElementById('invnum').innerText = "";
		document.getElementById('invdate').innerText = "";
		document.getElementById('invamt').innerText = "";
		document.getElementById('invpaid').innerText = "";
	
		document.getElementById('invnum').innerText += sdept[0];
		document.getElementById('invdate').innerText += sdept[1];
		document.getElementById('invamt').innerText += sdept[2];
		document.getElementById('invpaid').innerText += sdept[3];
	}
	var form=document.editcred;
	var empname = form.empname;
	var empname = document.getElementById('empname');
	empname.length=0;
	var oOption = document.createElement("option");
	oOption.appendChild(document.createTextNode("Select"));
	oOption.setAttribute("value","");
	empname.appendChild(oOption);
	sedept = sddept[1].split("|akkenECSplit|");
	if(sedept!='')
	{
		for(i=0;i<sedept.length;i++)
		{
			ssdepts = sedept[i].split("|akkenPSplit|");
			ssdept = sedept[i].replace("|akkenPSplit|", "-");
			var oOption = document.createElement("option");
			oOption.appendChild(document.createTextNode(ssdept));
			oOption.setAttribute("value",ssdepts[0]);
			empname.appendChild(oOption);
		}
	}

}

function updateEmpDet()
{
	var form=document.editcred;
	invid = form.invno.value;
	empid = form.empname.value;
	cusid = '';
	DynCls_Ajax_result("filterInv.php?custid="+cusid+"&invid="+invid+"&empid="+empid,"rtype","updateEmp","refreshEmpList()");
}
function refreshEmpList()
{
	
	var form=document.editcred;
	var linetype = form.linetype;
	var linetype = document.getElementById('linetype');

	linetype.length=0;

	var oOption = document.createElement("option");
	oOption.appendChild(document.createTextNode("Select"));
	oOption.setAttribute("value","");
	linetype.appendChild(oOption);

	if(empid!='')
	{
		if(DynCls_Ajx_responseTxt!="")
		{
			sdepts = DynCls_Ajx_responseTxt.split("|akkenCSplit|");
			
			for(i=0;i<sdepts.length;i++)
			{
				ssdepts = sdepts[i].split("|akkenPSplit|");
				var oOption = document.createElement("option");
				oOption.appendChild(document.createTextNode(ssdepts[0]));
				oOption.setAttribute("value",ssdepts[0]);
				linetype.appendChild(oOption);
			}
		}
	}
}
</script>



<form action=updatecredit.php method=post name='editcred' id='editcred'>
<input type="hidden" name="cdate" id="cdate" value="<?=date('m/d/Y H:i A');?>">
<input type="hidden" name="moddate" id="moddate" value="<?=date('m/d/Y H:i A');?>">
<input type="hidden" name="creditid" id="creditid" value="<?=$arr[0];?>">

	<div id="grid_form">
	<table border="0" width="100%" cellspacing="5" cellpadding="0" bgcolor="white" class="ProfileNewUI">
	<tr>
	  <td width=50% valign=top align=left>
		<div class="tab-pane" id="tabPane1">
		<script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
    	    <div class="tab-page" id="tabPage11">
			<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) );</script>

			<table width=100% cellspacing=0 cellpadding=0 border=0>
			<tr class="NewGridTopBg">
				<?php
					$name=explode("|","fa fa-floppy-o~Save|fa fa-times~Close");
					if($status == 'edit') {
					$heading="task.gif~Credit(CR$arr[0])";
					$link=explode("|","javascript:validateedit()|javascript:parent.top.modalBoxCloseandCancel()");
					} else {
					$heading="task.gif~Create Credit";
					$link=explode("|","javascript:validate()|javascript:parent.top.modalBoxCloseandCancel()");
					}					
					$menu->showHeadingStrip1($name,$link,$heading);
				?>
	</tr>
    	    <tr>
				<td><font class=afontstyle>&nbsp;</font></td>
	 	    </tr>
			<tr >
			<td align=center>
			<?php if($status == 'edit') {?>
			<input type="hidden" name="formtype" value="edit">
				<table width=100% cellspacing=1 cellpadding=0 border=0>
				<?php if(empty($read_only)) {?>
				<tr class="panel-table-content-new">
		            <td align=right class=summaryrow width=25%>&nbsp;Customer Name<font class=sfontstyle>*</font></td>
        		    <td class=summarytext width=250px><select name="custid" id="custid" onChange="updateInvList()"><option value='<?php echo $arr[1];?>' <?php echo getSel($custid,"");?>><?php echo $arr[9];?></option>
					<?php 
					$custid = "";
					while($fetch_period=mysql_fetch_row($qrs))
					{ 
				print "<option value='".$fetch_period[0]."' ".getSels($custid,$fetch_period[0]).">".$fetch_period[0].'-'.$fetch_period[1]."</option>";?>

					<?php } ?>
		</select></td>
						</tr>
						
				
				<tr class="panel-table-content-new">
					<td align=right class=summaryrow>&nbsp;Invoice No</td>
					<td class=summarytext><select name="invno" id="invno" class=drpdwne style="width:175px" onChange="updateInvDet()">
					<?php if($arr[2]=='Select'){ ?>
						   <option value="">Select</option>
					<?php } else { ?>
					       <option value="<?php echo $arr[2];?>"><?php if($arr[2]!='0'){echo $arr[15];}?></option>
					<?php } ?>	   
					</select>
					</td>
				</tr>
				<tr class="panel-table-content-new">
					<td align=right class=summaryrow>&nbsp;Employee Name</td>
					<td class=summarytext><select name="empname" id="empname" class=drpdwne style="width:175px" onChange="updateEmpDet()">
					<?php if($arr[17]=='Select'){ ?>
						   <option value="">Select</option>
					<?php } else { ?>
					       <option value="<?php echo $arr[17];?>"><?php if($arr[16]!='0'){echo $arr[16];}?></option>
					<?php } ?>	   
					</select>
					</td>
				</tr>
				<tr class="panel-table-content-new">
					<td align=right class=summaryrow>&nbsp;Line Types</td>
					<td class=summarytext><select name="linetype" id="linetype" class=drpdwne style="width:175px" onChange="updateInvDet2()">
					<?php if($arr[18]=='Select'){ ?>
						   <option value="">Select</option>
					<?php } else { ?>
					       <option value="<?php echo $arr[18];?>"><?php if($arr[18]!='0'){echo $arr[18];}?></option>
					<?php } ?>	   
					</select>
					</td>
				</tr>
				
				<?php } else {?>
				<tr class="panel-table-content-new">
		            <td align=right class=summaryrow width=25%>&nbsp;Customer Name<font class=sfontstyle>*</font></td>
        		    <td class=summarytext><input type=text name=cusname size=50 maxlength=255 value='<?php echo $arr[9];?>' <?php echo $read_only;?>><input type=hidden name=custid id=custid size=50 maxlength=255 value='<?php echo $arr[1];?>'></td>
				</tr>
				<tr class="panel-table-content-new">
				<td align=right class=summaryrow>&nbsp;Invoice No</td>
				<td class=summarytext>
				<input type=text name=invnos size=50 maxlength=255 value='<?php if($arr[2]!='0'){echo $arr[15];}?>' <?php echo $read_only;?>>
				<input type=hidden name=invno size=50 maxlength=255 value='<?php echo $arr[2];?>' <?php echo $read_only;?>>
				</td>
				</tr>
				
				<tr class="panel-table-content-new">
					<td align=right class=summaryrow>&nbsp;Employee Name</td>
					<td class=summarytext>
					<input type=text name=empnames size=50 maxlength=255 value='<?php if($arr[17]!='0'){echo $arr[16];}?>' <?php echo $read_only;?>>
					<input type=hidden name=empname id=empname size=50 maxlength=255 value='<?php echo $arr[17];?>'>
					</td>
				</tr>
				<tr class="panel-table-content-new">
					<td align=right class=summaryrow>&nbsp;Line Types</td>
					<td class=summarytext>
					<input type=text name=linetype size=50 maxlength=255 value='<?php if($arr[18]!='0'){echo $arr[18];}?>' <?php echo $read_only;?>>
					</td>
				</tr>
				<?php } ?>
		<?php if($arr[2] != 0 ) {?>
		<tr><td></td><td align=right class=summaryrow width=25%>Invoice No: <span id=invnumedt><?php echo $arr[15];?></span><span id=invnum></span></td><td align=right class=summaryrow width=25%>Invoice Date:</td><td align=right class=summaryrow width=25%><span id=invdateedt><?php echo $arr[13];?></span><span id=invdate></span></td></tr>
		<tr><td></td><td align=right class=summaryrow width=25%>Original Amount: <span id=invamtedt><?php echo number_format($arr[14], 2,'.', '');?></span><span id=invamt></span></td><td align=right class=summaryrow width=25%>Paid Amount:</td><td align=right class=summaryrow width=25%><span id=invpaidedt><?php echo number_format($paidamt, 2,'.', '');?></span><span id=invpaid></span></td></tr>
		<?php } ?>
		 
		<tr class="panel-table-content-new">
		            <td align=right class=summaryrow width=25%>&nbsp;Credit Amount<font class=sfontstyle>*</font></td>
        		    <td class=summarytext><input type=text id=creditamts name=creditamts size=50 maxlength=255 value='<?php echo $arr[3];?>' <?php echo $read_only;?>></td>
				</tr>
				<tr class="panel-table-content-new">
			<td align=right class=summaryrow>&nbsp;Type</td>
			<td class=summarytext>
			<?php echo $arr[7];?>
			</td>
		</tr>
		<tr class="panel-table-content-new">
		            <td align=right class=summaryrow width=25%>&nbsp;Description</td>
        		    <td class=summarytext><textarea name="creditdesc" id="creditdesc" rows=5 cols=40 wrap=virtual><?php echo $arr[10];?></textarea></td>
				</tr>
		
			</table>
			<?php } else { ?>
			<input type="hidden" name="formtype" value="create">
			<table width=100% cellspacing=1 cellpadding=0 border=0>
				
				<tr class="panel-table-content-new">
		            <td align=right class=summaryrow width=25%>&nbsp;Customer Name<font class=sfontstyle>*</font></td>
        		    <td class=summarytext width=250px><select name="custid" id="custid" onChange="updateInvList()"><option value='' <?php echo getSel($custid,"");?>>Select</option>
					<?php 
					$custid = "";
					while($fetch_period=mysql_fetch_row($qrs))
			{ 
		print "<option value='".$fetch_period[0]."' ".getSels($custid,$fetch_period[0]).">".$fetch_period[0].'-'.$fetch_period[1]."</option>";?>

			<?php } ?>
</select></td>
				</tr>
				
		
		<tr class="panel-table-content-new">
			<td align=right class=summaryrow>&nbsp;Invoice No</td>
			<td class=summarytext><select name="invno" id="invno" class=drpdwne style="width:175px" onChange="updateInvDet()">
                      <option value="">Select</option>
			     
			</select>
			</td>
		</tr>
		<tr class="panel-table-content-new">
			<td align=right class=summaryrow>&nbsp;Employee Name</td>
			<td class=summarytext><select name="empname" id="empname" class=drpdwne style="width:175px" onChange="updateEmpDet()">
                      <option value="">Select</option>
			     
			</select>
			</td>
		</tr>
		<tr class="panel-table-content-new">
			<td align=right class=summaryrow>&nbsp;Line Types</td>
			<td class=summarytext><select name="linetype" id="linetype" class=drpdwne style="width:175px" onChange="updateInvDet2()">
                      <option value="">Select</option>
			     
			</select>
			</td>
		</tr>
		<span id=invnumedt></span><span id=invdateedt></span><span id=invamtedt></span><span id=invpaidedt></span>
		<tr><td></td><td align=right class=summaryrow width=25%>Invoice No:<span id=invnum></span> </td><td align=right class=summaryrow width=40%>Inv Date:<span id=invdate></span></td><td align=right class=summaryrow width=25%></td></tr>
		<tr><td></td><td align=right class=summaryrow width=25%>Original Amount: <span id=invamt></span></td><td align=right class=summaryrow width=40%>Paid Amount:<span id=invpaid></span></td><td align=right class=summaryrow width=25%></td></tr>
		
		<tr class="panel-table-content-new">
		            <td align=right class=summaryrow width=25%>&nbsp;Credit Amount<font class=sfontstyle>*</font></td>
        		    <td class=summarytext><input type=text id=creditamt name=creditamt size=50 maxlength=255 value=''></td>
				</tr>
				
		<tr class="panel-table-content-new">
		            <td align=right class=summaryrow width=25%>&nbsp;Description</td>
        		    <td class=summarytext><textarea name="creditdesc" id="creditdesc" rows=5 cols=40 wrap=virtual><?php echo $arr[10];?></textarea></td>
				</tr>
		
			</table>
			<?php } ?>
			</div>
			<script>tp1.setSelectedIndex(0);</script>
		</div>
		</td>
	</table>
	</div>
</form>

</body>
</html>