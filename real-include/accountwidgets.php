<?php 
 require_once("dispfunc.php");	
// subtracting and giving decemal values
function bc_sub($firstVal,$secVal,$numDecimal)
{
	return number_format(($firstVal-$secVal), $numDecimal,".", "");
}

// adding and giving decemal values
function bc_add($firstVal,$secVal,$numDecimal)
{
	return number_format(($firstVal+$secVal), $numDecimal,".", "");
}

// Grid header_Common function
function headerGrid($headers,$sertypes)
{
	$header="<"."script".">\n";
	$shead=explode("|",$headers);

	$header.="var headcol = [";
	for($i=0;$i<count($shead);$i++)
	{
		if($i==count($shead)-1)
			$header.="\"".$shead[$i]."\"";
		else
			$header.="\"".$shead[$i]."\",";
	}
	$header .= "];\n";

	$sser=explode("|",$sertypes);
	$header.="var headdata = [[";
	for($i=0;$i<count($sser);$i++)
	{
		if($i==count($sser)-1)
			$header.="\"".$sser[$i]."\"";
		else
			$header.="\"".$sser[$i]."\",";
	}
	$header.="]];\n";

	$header.="</"."script".">\n";
	return $header;
}

// parent relations in accounts register..
function parent($pid)
{
    global  $maildb,$db,$partext;

    $sque="select name,parent from reg_category where sno='".$pid."' AND reg_category.status IN ('ER')";
    $sres=mysql_query($sque,$db);
    $srow=mysql_fetch_row($sres);
    $parent=$srow[0];

    $partext=$parent." : ".$partext;

    if($srow[1]>0)
        parent($srow[1]);
	else		

    mysql_free_result($sres);
    return $partext;
}

function getAccountFullName($pid,$partext)
{
	global  $maildb,$db;
    $sque="select name,parent from reg_category where sno='".$pid."' AND reg_category.status IN ('ER')";
    $sres=mysql_query($sque,$db);
    $srow=mysql_fetch_row($sres);
    $parent=$srow[0];
	$partext=$parent." : ".$partext;

    if($srow[1]>0)
        getAccountFullName($srow[1],$partext);
    
    return $partext;
}

// Accounts Main page
function displayWorkAccCat(&$data,$db)
{
    global $partext;
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{       
		$balance = 0;
		$accountIds = $result[0];
		
		$balance = getTransactionTotal($result[0],$db);		
		$balanceAccounts = getAccountBalance($result[0],$balance,$accountIds,$db);
		
		$balanceAccountsArray = explode("^",$balanceAccounts);
		$balance = $balanceAccountsArray[0];
		$accountIds = $balanceAccountsArray[1];
        
		if($result[11] == '2' || $result[11] == '4' || $result[11] == '3')
			$balanceDisplay = -1 * $balance;
		else
			$balanceDisplay = $balance;
			
		$grid.="[";
		$grid.="\""."<input type=checkbox name=auids[]  OnClick=chk_clearTop_TimeSheet() id=auids[] value=".$result[0].">\",";

		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[1]))),ENT_QUOTES)."\",";
		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result['Acc_Number']))),ENT_QUOTES)."\",";

		/*if($result[3]>0)
        {*/
            $partext="";
			$column = trim(trim(parent($result[3])),":");
			$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($column))),ENT_QUOTES)."\",";
       /* }
        else
        {
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[1]))),ENT_QUOTES)."\",";
        }       */

		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[5]))),ENT_QUOTES)."\",";
	
        $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[9]))),ENT_QUOTES)." - ".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[7]))),ENT_QUOTES)."\",";   //Deposits

        $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[10]))),ENT_QUOTES)." - ".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[8]))),ENT_QUOTES)."\",";   //Payments

		//$grid.="\"".bcsub($ex2,$ex1,2)."\",";
		$grid.="\"".number_format(($balanceDisplay), 2,".", "")."\",";
		
		$grid.="\""."javascript:doReceivePay('".$result[0]."','".$result[2]."','".$accountIds."')"."\"";			
			//$grid.="\""."javascript:doAcShow('".$result[0]."')"."\"";	
		/*}
		else
		{
			$grid.="\""."javascript:doAcShow('".$result[0]."')"."\"";	
		}*/

		$j++;
		
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function getAccountBalance($account,$total,$accountIds,$db)
{
	global $maildb,$db;
	$getChild = "SELECT reg_category.sno FROM reg_category WHERE reg_category.parent = '".$account."' AND reg_category.status = 'ER'";
	$resChild = mysql_query($getChild,$db);
	
	if(mysql_num_rows($resChild) > 0)
	{	
		while($rowChild = mysql_fetch_array($resChild))
		{
			$total += getTransactionTotal($rowChild[0],$db);
			$accountIds .= ",".$rowChild[0];		
			$totalAccounts = getAccountBalance($rowChild[0],$total,$accountIds,$db);
			$totalAccountsArray = explode("^",$totalAccounts);
			$total = $totalAccountsArray[0];
			$accountIds = $totalAccountsArray[1];
		}
		return $total."^".$accountIds;
	}
	else
	{
		return $total."^".$accountIds;


	}
}
function getTransactionTotal($account,$db)
{
	global $maildb,$db;
	$getTotal = "SELECT SUM(acc_transaction.amount) FROM acc_transaction WHERE acc_transaction.accountRefId = '".$account."' AND acc_transaction.status = 'ACTIVE' GROUP BY acc_transaction.accountRefId";
	$resTotal = mysql_query($getTotal,$db);
	$rowTotal = mysql_fetch_array($resTotal);
	
	return $rowTotal[0];
}
function _GetAccountExpenses($acc,$type,$db)
{
        $ex1=0;
        //Payments side
        $quepar="select reg_category.sno,reg_category.name,reg_category.type,reg_category.parent,reg_category.c_date,reg_accdesc.tdesc,reg_category.status from reg_category LEFT JOIN reg_accdesc ON reg_accdesc.type=reg_category.type where reg_category.parent='".$acc."' and reg_category.status IN ('ER') order by reg_category.type,reg_category.parent,reg_category.name asc";
        $rspar=mysql_query($quepar,$db);
        $countpar=mysql_num_rows($rspar);
        if($countpar > 0)
        {
            if($type=="AP")
            {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','CHK','BillPMT','BillCPMT','BILLCONPM','BillCPM','EmpPMT') and cate_id='".$acc."' and status IN ('ER','active')";

                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','BillPMT','BillCPMT','BILLCONPM','BillCPM') and cate_id='".$acc."' and status IN ('ER','active')";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                $ex1 = $dd1[0];
            }
            else if($type=="AR")
            {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','PMT') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                $ex1 = $dd1[0];
            }
            else if($type=="BANK")
            {
                $qu="select SUM(IF(bank_trans.type IN ('Payment','BillPMT','BillCPMT','BILLCONPM','BillCPM','EmpPMT','ChkPMT'),bank_trans.amount,0)) from bank_trans LEFT JOIN acc_reg ON CONCAT('Dep',bank_trans.sno) = acc_reg.source where acc_reg.status = 'ER' and bank_trans.bankid='".$acc."'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Deposit','CHK') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);
                
                $ex1 = ($dd1[0]+$dd2[0]);
           }
           else if($type == "EXP")
           {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','EmpPMT','EXP','BENEFITS','CONTRIB','TAX') and cate_id='".$acc."' and status IN ('ER','active')";
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','EXP','BENEFITS','CONTRIB','TAX','NETPAY','ChkPMT') and cate_id='".$acc."' and status IN ('ER','active')";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount) from acc_reg LEFT JOIN bank_trans ON acc_reg.source = CONCAT('Dep',bank_trans.sno) where bank_trans.type IN ('Deposit','CHK') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);

                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Deposit') and acc_reg.source IN ('Acc".$acc."') GROUP BY acc_reg.source";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);
                
                $ex1 = ($dd1[0]+$dd2[0]+$dd3[0]);
           }
           else
           {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','ChkPMT') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);

                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Deposit','CHK') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);
                
                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Deposit') and (IF(acc_reg.source='opbal','Acc6',acc_reg.source) IN ('Acc".$acc."')) GROUP BY acc_reg.source";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);
                
                $ex1 = ($dd1[0]+$dd2[0]+$dd3[0]);
           }
           while($rowpar = mysql_fetch_array($rspar))
           {
                $ex1+= _GetAccountExpenses($rowpar[0],$rowpar[2],$db);
           }
           return $ex1;
        }
        else
        {
            if($type=="AP")
            {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','CHK','BillPMT','BillCPMT','BILLCONPM','BillCPM','EmpPMT') and cate_id='".$acc."' and status IN ('ER','active')";
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','BillPMT','BillCPMT','BILLCONPM','BillCPM') and cate_id='".$acc."' and status IN ('ER','active')";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                return $dd1[0];
            }
            else if($type=="AR")
            {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','PMT') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                return $dd1[0];
            }
            else if($type=="BANK")
            {
                $qu="select SUM(IF(bank_trans.type IN ('Payment','BillPMT','BillCPMT','BILLCONPM','BillCPM','EmpPMT','ChkPMT'),bank_trans.amount,0)) from bank_trans LEFT JOIN acc_reg ON CONCAT('Dep',bank_trans.sno) = acc_reg.source where acc_reg.status = 'ER' and bank_trans.bankid='".$acc."'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Deposit','CHK') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);
                
                return ($dd1[0]+$dd2[0]);
           }
           else if($type == "EXP")
           {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','EmpPMT','EXP','BENEFITS','CONTRIB','TAX') and cate_id='".$acc."' and status IN ('ER','active')";
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','NETPAY','EXP','BENEFITS','CONTRIB','TAX','ChkPMT') and cate_id='".$acc."' and status IN ('ER','active')";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount) from acc_reg LEFT JOIN bank_trans ON acc_reg.source = CONCAT('Dep',bank_trans.sno) where bank_trans.type IN ('Deposit','CHK') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);

                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Deposit') and acc_reg.source IN ('Acc".$acc."') GROUP BY acc_reg.source";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);
                
                return ($dd1[0]+$dd2[0]+$dd3[0]);
           }
           else
           {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Payment','ChkPMT') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Deposit','CHK') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);
                
                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Deposit') and (IF(acc_reg.source='opbal','Acc6',acc_reg.source) IN ('Acc".$acc."')) GROUP BY acc_reg.source";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);
               
                return ($dd1[0]+$dd2[0]+$dd3[0]);
           }
       }
}


Function _GetAccountIncome($acc,$type,$db)
{
        $ex2=0;
        //Deposits side
        $quepar="select reg_category.sno,reg_category.name,reg_category.type,reg_category.parent,reg_category.c_date,reg_accdesc.tdesc,reg_category.status from reg_category LEFT JOIN reg_accdesc ON reg_accdesc.type=reg_category.type where reg_category.parent='".$acc."' and reg_category.status IN ('ER') order by reg_category.type,reg_category.parent,reg_category.name asc";
        $rspar=mysql_query($quepar,$db);
        $countpar=mysql_num_rows($rspar);
        if($countpar > 0)
        {
            if($type=="AP")
            {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','Bill','CBILL','CONBILL','NETPAY','BENEFITS','CONTRIB','TAX') and cate_id='".$acc."' and status IN ('ER','active')";
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','Bill','CBILL','CONBILL') and cate_id='".$acc."' and status IN ('ER','active')";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                $ex2 = $dd1[0];
            }
            else if($type=="AR")
            {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Invoice','Payment') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                $ex2 = $dd1[0];
            }
            else if($type=="BANK")
            {
                $qu="select SUM(IF(bank_trans.type IN ('Deposit','PMT','CHK'),bank_trans.amount,0)) from bank_trans LEFT JOIN acc_reg ON CONCAT('Dep',bank_trans.sno)=acc_reg.source where acc_reg.status = 'ER' and bank_trans.bankid='".$acc."'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Payment','ChkPMT') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);
                
                $ex2 = ($dd1[0]+$dd2[0]);
            }
            else if($type == "EXP")
            {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','GROSSPAY') and cate_id='".$acc."' and status = 'ER'";
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','CHK') and cate_id='".$acc."' and status = 'ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount) from acc_reg LEFT JOIN bank_trans ON acc_reg.source = CONCAT('Dep',bank_trans.sno) where bank_trans.type IN ('Payment','EmpPMT','ChkPMT') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);

                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Payment') and acc_reg.source IN ('Acc".$acc."') GROUP BY acc_reg.source";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);
               
                $ex2 = ($dd1[0]+$dd2[0]+$dd3[0]);
            }
            else
            {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','CHK') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Payment','ChkPMT') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);

                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Payment') and acc_reg.source IN ('Acc".$acc."') GROUP BY acc_reg.cate_id";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);
                
                $ex2 = ($dd1[0]+$dd2[0]+$dd3[0]);
            }
            while($rowpar = mysql_fetch_array($rspar))
            {
                $ex2+= _GetAccountIncome($rowpar[0],$rowpar[2],$db);
            }
            return $ex2;
        }
        else
        {
            if($type=="AP")
            {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','Bill','CBILL','CONBILL','NETPAY','BENEFITS','CONTRIB','TAX') and cate_id='".$acc."' and status IN ('ER','active')";
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','Bill','CBILL','CONBILL') and cate_id='".$acc."' and status IN ('ER','active')";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                return $dd1[0];
            }
            else if($type=="AR")
            {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Invoice','Payment') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                return $dd1[0];
            }
            else if($type=="BANK")
            {
                $qu="select SUM(IF(bank_trans.type IN ('Deposit','PMT','CHK'),bank_trans.amount,0)) from bank_trans LEFT JOIN acc_reg ON CONCAT('Dep',bank_trans.sno)=acc_reg.source where acc_reg.status = 'ER' and bank_trans.bankid='".$acc."'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Payment','ChkPMT') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);
                
                return ($dd1[0]+$dd2[0]);
            }
            else if($type == "EXP")
            {
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','NETPAY','BENEFITS','CONTRIB','TAX','GROSSPAY') and cate_id='".$acc."' and status IN ('ER','active')";
                //$qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','GROSSPAY') and cate_id='".$acc."' and status = 'ER'";
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','CHK') and cate_id='".$acc."' and status = 'ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);
                
                $qu1="select sum(acc_reg.amount) from acc_reg LEFT JOIN bank_trans ON acc_reg.source = CONCAT('Dep',bank_trans.sno) where bank_trans.type IN ('Payment','EmpPMT','ChkPMT') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);

                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Payment') and acc_reg.source IN ('Acc".$acc."') GROUP BY acc_reg.source";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);
                
                return ($dd1[0]+$dd2[0]+$dd3[0]);
            }
            else
            {
                $qu="select sum(acc_reg.amount) from acc_reg where type IN ('Deposit','CHK') and cate_id='".$acc."' and status='ER'";
                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($res1);
                mysql_free_result($res1);

                $qu1="select sum(acc_reg.amount),bank_trans.account from acc_reg LEFT JOIN bank_trans ON (acc_reg.source = CONCAT('Dep',bank_trans.sno) and acc_reg.cate_id = bank_trans.bankid) where bank_trans.type IN ('Payment','ChkPMT') and bank_trans.account='".$acc."' and acc_reg.status='ER' GROUP BY bank_trans.account";
                $res2=mysql_query($qu1,$db);
                $dd2=mysql_fetch_row($res2);
                mysql_free_result($res2);

                $qu2="select sum(acc_reg.amount) from acc_reg where acc_reg.type IN ('Payment') and (IF(acc_reg.source='opbal','Acc6',acc_reg.source) IN ('Acc".$acc."')) GROUP BY acc_reg.source";
                $res3=mysql_query($qu2,$db);
                $dd3=mysql_fetch_row($res3);
                mysql_free_result($res3);

                return ($dd1[0]+$dd2[0]+$dd3[0]);
            }
        }
}

// Previous Banking main grid Page..Current: displayWorkAccCat()..
function displayWorkBankingTran(&$data,$db)
{
    global $partext;
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
		$grid.="[";
		$grid.="\""."\",";
		
		if($result[4]>0)
        {
            $partext="";
            $grid.="\"".parent($result[4]).$result[0]."\",";
        }
        else
        {
            $grid.="\"".$result[0]."\",";
        }
		
		$grid.="\"".trim($result[1])."\",";
		$grid.="\"".trim($result[2])."\",";
		$grid.="\"".bc_sub($result[1],$result[2],2)."\",";
		$grid.="\"javascript:doRegS(".$result[3].")"."\"";
		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// Invoice history page.
function DisplayInvoiceHis(&$data,$db)
{
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);
	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";
	 
	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{ 
        $clique="select cname from staffacc_cinfo where sno='".$result[1]."'";
        $clires=mysql_query($clique,$db);
        $clirow=mysql_fetch_row($clires);
        $servicedate="";
        if($result[8]!="" && $result[9]!="")
            $servicedate=date('m/d/Y',strtotime($result[8]))." - ".date('m/d/Y',strtotime($result[9]));
        else
            $servicedate=date('m/d/Y',strtotime($result[2]))." - ".date('m/d/Y',strtotime($result[3]));
        
		
	/*$SQL="select timesheet.parid,MIN(par_timesheet.sdate),MAX(par_timesheet.edate) from timesheet left join par_timesheet on (timesheet.parid=par_timesheet.sno)left join invoice on (invoice.sno=timesheet.billable) where timesheet.client='".$result[1]."' and par_timesheet.astatus='Billed' and par_timesheet.pstatus='Billed' and timesheet.billable='".$result[0]."' group by invoice.sno"; 
	$RSQL=mysql_query($SQL,$db);
	$dd=mysql_fetch_row($RSQL); 
	$Sql="select expense.parid,MIN(par_expense.sdate),MAX(par_expense.edate) from expense left join par_expense on (expense.parid=par_expense.sno)left join invoice on (invoice.sno=expense.billable) where expense.client='".$result[1]."' and par_expense.astatus='Billed' and par_expense.pstatus='Billed' and expense.billable='".$result[0]."' group by expense.client";
	$Rsql=mysql_query($Sql,$db); 
	$dd1=mysql_fetch_row($Rsql);
		if(($dd[1]!=NULL) && ($dd1[1]!=NULL))
		{ 
              if($dd[1]<$dd1[1])
              {
           			$fdate=$dd[1];
              }
              else
              {
        			$fdate=$dd1[1];
              }
              
              if($dd[2]>$dd1[2])
              {
           			$todate=$dd[2];
              }
              else
              {
        			$todate=$dd1[2];
              }
		}
		else if($dd[1] != NULL)
		{
			$fdate=$dd[1];
            $todate=$dd[2];
        }
        else if($dd1[1] != NULL)
		{
			$fdate=$dd1[1];
            $todate=$dd1[2];
		}	
		$serfdate=explode("-",$fdate);
		$sertodate=	explode("-",$todate);
		$servicedate=$serfdate[1]."/".$serfdate[2]."/".$serfdate[0]."-".$sertodate[1]."/".$sertodate[2]."/".$sertodate[0];*/
		
        $refqry="select refnumber from qb_invoice where invid=".$result[0];
        $refres=mysql_query($refqry,$db);
        $refnums=mysql_num_rows($refres);
        if($refnums > 0)
        {
            $refrow=mysql_fetch_row($refres);
            $refnumber = $refrow[0];
        }
        else
            $refnumber = $result[0];
		// To get the payemnt 	
		$invSourceNo="inv".$result[0];
            
		$get_paid = "SELECT  SUM(amount) FROM bank_trans WHERE source = '".$invSourceNo."' GROUP BY source";
		$res_paid = mysql_query($get_paid,$db);
		$row_paid = mysql_fetch_row($res_paid);
		
		$grid.="[";
		//$grid.="\""."\",";
		$grid.="\""."<input type=checkbox name=auids[] id=".$result[0]." onclick=chk_clearTop() value=".$result[0].">\",";
		$grid.="\"".stripslashes(gridcell($clirow[0]))."\",";
		$grid.="\"".trim($refnumber)."\",";
		$grid.="\"".trim($servicedate)."\",";
		$grid.="\"".trim(date('m/d/Y',strtotime($result[2])))."\",";
		$grid.="\"".trim(date('m/d/Y',strtotime($result[3])))."\",";
		$grid.="\"".number_format($result[7], 2,".", "")."\",";
		$grid.="\"".bc_sub($result[7],$row_paid[0],2)."\",";
		$grid.="\""."showinvoice.php?printinvoice=yes&addr=".$result[0]."\"";

		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// use Register main page for banking..
function displayBankTrans(&$data,$db,$bank,$acc)
{
    global $companyname;
    
    $grid="<"."script".">\n";
    $row_count = mysql_num_rows($data);
    $column_count = mysql_num_fields($data);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
    	if($i==$column_count-1)
    		$grid.="\""."\"";
    	else
    		$grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $grid.="var actdata = [\n";
    while ($result = mysql_fetch_array($data))
    {
        if($result[2]=="IH")
        {
            $clname = $companyname;
        }
        else if(strpos("*".$result[2],"PE"))
        {
            $que="select name from reg_payee where CONCAT('PE',sno)='".$result[2]."'";
            $res=mysql_query($que,$db);
            $row=mysql_fetch_row($res);
            $clname=$row[0];
        }
        else
        {
            $sque="select cname from staffacc_cinfo where username='".$result[2]."'";
            $sres=mysql_query($sque,$db);
            $norow=mysql_num_rows($sres);
            if ($norow>0)
            {
                $srow=mysql_fetch_row($sres);
                //$tf="Client";
                $clname=$srow[0];
            }
            else
            {
                $sque="select name from emp_list where username='".$result[2]."'";
                $sres=mysql_query($sque,$db);
                $srow=mysql_fetch_row($sres);
                $ctno=mysql_num_rows($sres);
                if ($ctno > 0)
                    $clname = $srow[0]." (Employee)";
                else
                    $clname = "";
            }
        }
        
    	$grid.="[";
    	$grid.="\""."<input type=checkbox name=auids[] id=".$result[0]." value=".$result[6].">\",";
    	$grid.="\"".$result[0]."\",";
    	$grid.="\"".$result[1]."\",";
    	$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
    	
    	$bid = GetID($bank,$db);
        $bankid = explode(",",$bid);

    	if(in_array($result[7],$bankid)) // Deposit To Which Type
    	{
            if($result[4] != "")                //Deposit
            {
                $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$result[3])),ENT_QUOTES)."\",";
                $grid.="\""."0.00"."\",";
                $grid.="\"".number_format($result[4], 2,".", "")."\",";
                $total=($total+$result[4])-$result[5];
                $grid.="\"".number_format($total, 2,".", "")."\",";
                $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&account=".$result[8]."&sno=".$result[6]."&Trans=Deposit"."\",";
                $grid.="\""."Deposit|"."\"";
            }
            else
            {
                $grid.="\"".$result[3]."\",";         //Payment
                $grid.="\"".number_format($result[5], 2,".", "")."\",";
                $grid.="\""."0.00"."\",";
                $total=($total-$result[5])+$result[4];
                $grid.="\"".number_format($total, 2,".", "")."\",";
                $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&account=".$result[8]."&sno=".$result[6]."&Trans=Payment"."\",";
                $grid.="\""."Payment|"."\"";
            }
        }
        else
        {
            $acqu="select reg_category.name from reg_category where sno='".$result[7]."'";
            $acres=mysql_query($acqu,$db);
            $accref=mysql_fetch_row($acres);
            
            if($result[5] != "")
            {
                $grid.="\"".$accref[0]."\",";
                $grid.="\""."0.00"."\",";
                $grid.="\"".number_format($result[5], 2,".", "")."\",";
                $total=($total+$result[5])-$result[4];
                $grid.="\"".number_format($total, 2,".", "")."\",";
                $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&account=".$result[8]."&sno=".$result[6]."&Trans=Deposit"."&ebank=".$result[7]."\",";
                $grid.="\""."Deposit|".$result[7]."\"";
            }
            else
            {
                $grid.="\"".$accref[0]."\",";
                $grid.="\"".number_format($result[4], 2,".", "")."\",";
                $grid.="\""."0.00"."\",";
                $total=($total-$result[4])+$result[5];
                $grid.="\"".number_format($total, 2,".", "")."\",";
                $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&account=".$result[8]."&sno=".$result[6]."&Trans=Payment"."&ebank=".$result[7]."\",";
                $grid.="\""."Payment|".$result[7]."\"";
            }
        }
        
      //  $grid.="\"".number_format($total, 2,".", "")."\",";
      //  $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&sno=".$result[6]."\"";

        // for Self - Transactions..
      	if((in_array($result[7],$bankid)) && (in_array($result[8],$bankid)))
        {   
            $total=($total-$result[4])+$result[5];
            $grid.="],\n";
            $grid.="[";
        	$grid.="\""."<input type=checkbox name=auids[] id=".$result[6]." value=".$result[6].">\",";
        	$grid.="\"".$result[0]."\",";
        	$grid.="\"".$result[1]."\",";
        	$grid.="\"".$clname."\",";
        	
        	$acqu="select reg_category.name from reg_category where sno='".$result[7]."'";
            $acres=mysql_query($acqu,$db);
            $accref=mysql_fetch_row($acres);
            
        	$grid.="\"".$accref[0]."\",";
        	if($result[4] != "")
            {
                $grid.="\"".number_format($result[4], 2,".", "")."\",";
                $grid.="\""."0.00"."\",";
                $grid.="\"".number_format($total, 2,".", "")."\",";
                $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&account=".$result[8]."&sno=".$result[6]."&Trans=Payment"."&ebank=".$result[7]."\",";
                $grid.="\""."Payment|".$result[7]."\"";
            }
            else
            {
                $grid.="\""."0.00"."\",";
                $grid.="\"".number_format($result[5], 2,".", "")."\",";
                $grid.="\"".number_format($total, 2,".", "")."\",";
                $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&account=".$result[8]."&sno=".$result[6]."&Trans=Deposit"."&ebank=".$result[7]."\",";
                $grid.="\""."Deposit|".$result[7]."\"";
            }
        //	$grid.="\"".number_format($total, 2,".", "")."\",";
       //   $grid.="\""."showbill.php?acc=".$acc."&bank=".$bank."&sno=".$result[6]."\"";
        }

    	$j++;
    	if($j==$row_count)
    		$grid.="]\n";
    	else
    		$grid.="],\n";
    }
    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}

Function GetID($addr,$db)
{
    $addrid=$addr;
    $quepar="select sno from reg_category where parent=".$addr;
    $rspar=mysql_query($quepar,$db);
    $countpar=mysql_num_rows($rspar);
    if($countpar > 0)
    {
        while($rowpar=mysql_fetch_row($rspar))
            $addrid.=",".GetID($rowpar[0],$db);
        return $addrid;
    }
    else
        return $addrid;
}

//Customer Register main page in Customers..
function displayWorkAcRRegDet(&$data,$db,$client,$acc,$clname,$clientid,$totalemt)
{
	$decimalPref= getDecimalPreference();
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $statusJ="NO";
	$grid.="var actdata = [\n";
	if($venTypeHid=="cv")
	{
		$attch_wrd='cbil';
		$bill_page='showsupdetc.php';
	}
	else if($venTypeHid=="gv")
	{
		$attch_wrd='bil';	
		$bill_page='showdetv.php';
	}
	else
	{
		$attch_wrd='conbill';
		$bill_page='showsupdetcon.php';
	}	
	$amnt_pay = 0;
	while($result=mysql_fetch_row($data))
	{
		if($result[7] == 'ACTIVE')
		{			
			$getTotPMT = "select sum(amount) from bank_trans where source= 'inv".$result[2]."' AND type= 'PMT'";
			$resTotPMT = mysql_query($getTotPMT,$db);
			$rowTotPMT = mysql_fetch_array($resTotPMT);
			
			//query to get the adjustable amount sum to figure out the invoice due amount
			$getTotAdj = "SELECT SUM(amount) FROM acc_reg WHERE inv_bill_lineid = '".$result[2]."' AND type= 'PMT-ADJ'";
			$resTotAdj = mysql_query($getTotAdj,$db);
			$invAdjAmount = "";
			if(mysql_num_rows($resTotAdj)>0)
			{
				$rowTotAdj = mysql_fetch_array($resTotAdj);
				$invAdjAmount = $rowTotAdj[0];
			}
			//Amount Due Column
			$tempValue = number_format(($result[5]  - $rowTotPMT[0] - $invAdjAmount),$decimalPref,'.','');
			
			if($tempValue <= 0)
				$tempValue = 'PAID';
				
			$duedate = $result[4];				
			$amnt_pay+= ($result[5] );
			
			$url  = "";
			if($j==0)
				$grid.="[";
			else
				$grid.="\n,[";
			
			if($result[9] == 0.00)
				$result[9] = "";
			
			
			
			$grid.="\"\",";
			$grid.="\"".$result[0]."\",";
			$grid.="\"".$result[1]."\",";	//Invoice Text
			$grid.="\"".$result[10]."\",";
			$grid.="\"".gridcell($clname)."\",";
			$grid.="\"".$duedate."\",";		
			$grid.="\"".number_format($result[5],$decimalPref,'.','')."\",";
			$grid.="\"".$result[6]."\",";
			
			if(trim($result[9]) != '' && !is_null($result[9]) && $result[9] != 0)
				$grid.="\"<font color='#003300'>(Applied) ".$result[9]."</font>\",";
			else
				$grid.="\"".$result[9]."\",";
			
			$grid.="\"\","; //Adjustment Type column
			$grid.="\"\","; //Adjustment Amount column
				
			$grid.="\"".$tempValue."\",";
			$grid.="\"".number_format($amnt_pay,$decimalPref,'.','')."\",";		
			
			
			$getAcc_regSno = "SELECT sno FROm acc_reg WHERE source = 'inv".$result[2]."' AND type = 'Invoice' AND status = 'ER'";
			$resAcc_regSno = mysql_query($getAcc_regSno,$db);
			$rowAcc_regSno = mysql_fetch_row($resAcc_regSno);
			
			$grid.="\""."showbill.php?acc=".$acc."&client_no=".$client."&sno=".$result[2]."&type=invoice\"";
			$j++;
			$grid.="]\n";
		}
        $statusJ="YES";
		$bank_trans_qry		=" SELECT DATE_FORMAT(b.dpdate,'%m/%d/%Y %H:%i %p'),
						b.amount,
						b.sno,
						b.checknumber,
						inv.status
						FROM bank_trans b
					LEFT JOIN
						invoice inv ON CONCAT('inv','',inv.sno) = b.source
					WHERE b.source= 'inv".$result[2]."' AND b.type= 'PMT' ";
		$data1=mysql_query($bank_trans_qry,$db);
		$cmltv_diff=$amnt_pay;
		
		$getUsedCredit = "SELECT round(SUM(IFNULL(credit_memo_trans.used_amount,'0.00')),2) Trans_Amount,credit_memo.credit_inv_bill_id billid  FROM credit_memo_trans, credit_memo,invoice where credit_memo_trans.inv_bill_sno =  invoice.sno AND invoice.deliver = 'yes' AND credit_memo.credit_id=credit_memo_trans.credit_memo_sno AND credit_memo.credit_type = 'invoice' AND credit_memo.credit_inv_bill_id = '".$result[2]."' GROUP BY credit_memo_trans.credit_memo_sno";
		$resUsedCredit = mysql_query($getUsedCredit,$db);
		$rowUsedCredit = mysql_fetch_array($resUsedCredit);
		
		if($rowUsedCredit[0] == 0.00)
			$usedAmmount1 = "";
		else
			$usedAmmount1 = $rowUsedCredit[0];
		
		$countbank_trans_qry = mysql_num_rows($data1);
		$linesNumers = 0;
		while($result1=mysql_fetch_row($data1))
		{
			//Following query get the adjustment amount while doing the receive payments. By using the sno.bank_trans and payment type(PMT_ADJ), getting the adjustment amount record from acc_reg table
			$adj_trans_qry	= 	"SELECT ac.amount,
							rc.name
						FROM
							acc_reg ac
						LEFT JOIN
							reg_category rc
						ON
							(ac.source= 'Dep".$result1[2]."'
							AND ac.type= 'PMT-ADJ'
							AND rc.sno = ac.cate_id)
						WHERE rc.name != ''";
			
			$adj_trans_rs	= mysql_query($adj_trans_qry,$db);
			$adj_trans_row	= mysql_fetch_row($adj_trans_rs);
			$adj_amount	= "";
			$adj_type	= "";
			if($adj_trans_row[0] !="")
			{
				$adj_amount	= $adj_trans_row[0]; //adjustment amount column
				$adj_type	= $adj_trans_row[1]; //adjustment type name column
			}
			
			$linesNumers++;
			if($countbank_trans_qry == $linesNumers)
				$usedAmmount = $usedAmmount1;
			else
				$usedAmmount = "";
				
			$amnt_pay	-= $result1[1]-$usedAmmount;
			if($result1['4'] != "DELETED")
			{
				$amnt_pay	-= $adj_amount; // deducting the adjustable amount
			}
			if($j==0)
				$grid.="[";
			else
				$grid.="\n,[";
			$grid.="\"\",";
			$grid.="\"".$result1[0]."\",";
			$grid.="\""."Received Payment"."\",";
			$grid.="\"".$result1[3]."\",";
			$grid.="\"".gridcell($clname)."\",";
			$grid.="\"".$result[4]."\",";
			//$grid.="\"\",";		
			$grid.="\"".$result[6]."\",";			
			$grid.="\"".number_format($result1[1],$decimalPref,'.','')."\",";
			
			if(trim($usedAmmount) != '' && !is_null($usedAmmount) && $usedAmmount != 0)			
				$grid.="\"<a href=javascript:void(0); onclick=javascript:openWinUsedAmount('".$result[2]."','".$result1[2]."'); > <font color='#FF0000'>(Used) ".$usedAmmount."</font></a>\",";
			else
				$grid.="\"".$usedAmmount."\",";
				
			$grid.="\"".$adj_type."\","; //Adjustment Type

			if($result1['4'] != "DELETED")
			{
				$grid.="\"".number_format($adj_amount,$decimalPref,'.','')."\","; //Adjustment Amount
			}
			else
			{
				if($adj_amount!="")
				{
					$grid.="\"<font color='#FF0000'>(Removed) ".number_format($adj_amount,$decimalPref,'.','')."</font>\",";
				}
				else
				{
					$grid.="\"\",";
				}
			}
			
			$grid.="\"\",";			
			$grid.="\"".number_format($amnt_pay,$decimalPref,'.','')."\",";		
			
			$getAcc_regSno1 = "SELECT sno FROM acc_reg WHERE source = 'Dep".$result1[2]."' AND type = 'PMT' AND status = 'ER'";
			$resAcc_regSno1 = mysql_query($getAcc_regSno1,$db);
			$rowAcc_regSno1 = mysql_fetch_row($resAcc_regSno1);			
			$grid.="\""."showbill.php?acc=".$acc."&client_no=".$client."&sno=".$rowAcc_regSno1[0]."\"";
			$j++;
			$grid.="]\n";
		}
		if($totalemt=='')
	    {
			$Creditmemo = "SELECT IFNULL(SUM(cmt.used_amount),0)+cm.credit_amount as total_credit_amt,
		  DATE_FORMAT(cm.credit_cdate,'%m/%d/%Y %H:%i %p'),cm.credit_notes,cm.credit_amount,cm.credit_id,emp_list.name
	  FROM credit_memo cm
		   LEFT JOIN credit_memo_trans cmt
			  ON cmt.credit_memo_sno = cm.credit_id
			  LEFT JOIN emp_list ON emp_list.sno = cm.emp_id
	 WHERE credit_source = '".$clientid."' AND credit_inv_bill_id = '".$result[2]."' GROUP BY cm.credit_id";
			$Creditmemos = mysql_query($Creditmemo,$db);
			while($result2=mysql_fetch_row($Creditmemos))
			{
				if($result2[2]=='Available Credits')
				{
					if($result2[0]!='')
					{
						if($j==0)
							$grid.="[";
						else
							$grid.="\n,[";
						
						$grid.="\"\",";
						$grid.="\"".$result2[1]."\",";
						$grid.="\""."Available Credits"."\",";	//Invoice Text
						$grid.="\"".$result[10]."\",";
						$grid.="\"".gridcell($clname)."\",";
						$grid.="\"".$duedate."\",";		
						$grid.="\"\",";
						$grid.="\"\",";
						if($result2[0]!=''){
						$grid.="\"<font color='#006400'>(Created) ".number_format($result2[0],$decimalPref,'.','')."</font>\",";
						}else{
						$grid.="\"\",";	
						}
						
						$grid.="\"\","; //Adjustment Type column
						$grid.="\"\","; //Adjustment Amount column
							
						$grid.="\"\",";
						if($result2[2]!='Available Credits'){
						$grid.="\"".number_format($amnt_pay,$decimalPref,'.','')."\",";		
						}else{
						$amnt_pay = $amnt_pay - $result2[3];	
						$grid.="\"".number_format($amnt_pay,$decimalPref,'.','')."\",";
						}				
						$invcheck = "SELECT count(inv_bill_lineid) FROM acc_reg WHERE inv_bill_lineid = '".$result[2]."'";
			            $invchecks = mysql_query($invcheck,$db);
			            $invcheckval = mysql_fetch_row($invchecks);
							if($invcheckval[0] == '0'){
								$grid.="\""."showbill.php?credit_id=".$result2[4]."&ccdate=".$result2[1]."&custname=".$clname."&creamt=".$result2[0]."&empname=".$result2[5]."&type=CreditMemo\"";
							}else{
								//$grid.="\""."showbill.php?acc=".$acc."&client_no=".$client."&sno=".$result2[2]."&type=invoice\"";
								$grid.="\""."showbill.php?acc=".$acc."&client_no=".$client."&sno=".$rowAcc_regSno1[0]."\"";
							}
						$j++;
						$grid.="]\n";
					}
				}
			}
		}
		
	}
	if($totalemt=='')
	{
	$Creditmemoid = "SELECT IFNULL(SUM(cmt.used_amount),0)+cm.credit_amount as total_credit_amt,
       DATE_FORMAT(cm.credit_cdate,'%m/%d/%Y %H:%i %p'),cm.credit_notes,credit_id,credit_inv_bill_id,cm.credit_amount
  FROM credit_memo cm
  LEFT JOIN credit_memo_trans cmt
          ON cmt.credit_memo_sno = cm.credit_id
 WHERE credit_source = '".$clientid."' AND credit_inv_bill_id = '0' GROUP BY cm.credit_id";
		$Creditmemoids = mysql_query($Creditmemoid,$db);
		while($result3=mysql_fetch_row($Creditmemoids))
		{
			if($result3[0]!='')
			{
				if($j==0)
					$grid.="[";
				else
					$grid.="\n,[";
				
				$grid.="\"\",";
				$grid.="\"".$result3[1]."\",";
				$grid.="\""."Available Credits"."\",";	//Invoice Text
				$grid.="\""."CR".$result3[3]."\",";
				$grid.="\"".gridcell($clname)."\",";
				$grid.="\"".$duedate."\",";		
				$grid.="\"\",";
				$grid.="\"\",";
				if($result3[0]!=''){
				$creditbal = "SELECT SUM(used_amount) FROM credit_memo_trans WHERE credit_memo_sno = '".$result3[3]."'";
			    $creditbals = mysql_query($creditbal,$db);
			    $creditbalval = mysql_fetch_row($creditbals);
                		
					//$creamt = $result3[0] + $creditbalval[0];
					$creamt = $result3[0];
					  if($creditbalval[0]!=''){				
					$grid.="\"<font color='#006400'>".number_format($creamt,$decimalPref,'.','')."<a href=javascript:void(0); onclick=javascript:openWinCreditAmount('".$result3[3]."'); ></font><font color='#FF0000'> (Used)".$creditbalval[0]."</font></a>\",";
					  } else {
					$grid.="\"<font color='#006400'>".number_format($creamt,$decimalPref,'.','')."</font>\",";	  
					  }
				}else{
				$grid.="\"\",";	
				}
				
				$grid.="\"\","; //Adjustment Type column
				$grid.="\"\","; //Adjustment Amount column
					
				$grid.="\"\",";
				/* if($result2[2]!='Available Credits'){ */
				$amnt_pay = $amnt_pay - $result3[5];
				$grid.="\"".number_format($amnt_pay,$decimalPref,'.','')."\",";		
				/*} else{
				$amnt_pay = $amnt_pay - $result2[0];	
				$grid.="\"".number_format($amnt_pay,$decimalPref,'.','')."\",";
				} */				
				
				$grid.="\""."showbill.php?credit_id=".$result3[3]."&ccdate=".$result3[1]."&custname=".$clname."&creamt=".$creamt."&type=CreditMemo\"";
				//$grid.="\"\"";
				$j++;
				$grid.="]\n";
			}
			
		}
	}
	
    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}


// Delivery of Invoices page...
function DisplayInvoice(&$data,$db)
{
	global $invlocation,$invdept,$invclient,$servicedate,$servicedateto;
	$decimalPref    = getDecimalPreference();

    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	
	require("../Bill_Mngmt/invoice.inc");

	while ($result = @mysql_fetch_array($data))
	{
        $clique="select cname,templateid, sno from staffacc_cinfo where sno='".$result[1]."'";
        $clires=mysql_query($clique,$db);
        $clirow=mysql_fetch_row($clires);

		$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$clirow[1]."'";
		$pres=mysql_query($pque,$db);
		$prow=mysql_fetch_row($pres);

		$deliveryMethod = getDeliveryMethod($result[1],$db);
		$inv_balance = getInvoiceDeliverBalance($result[0],$result[1],$result[8],$result[4],$result[9],$db,$prow[0],$result[11]);
		$orient_pdf = ($result[14] == 1)?'Portrait':'Landscape';
		$grid.="[";
		$grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onclick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label><input type=hidden name=newauids[] id=new".$result[0]." value=".$result[0]."|".$inv_balance."|".$result[14].">\",";		
		$grid.="\"".$result[10]."\",";		
		$grid.="\"".gridcell($clirow[2])."\",";		
		$grid.="\"".stripslashes(gridcell($clirow[0]))."\",";
		$grid.="\"".trim($result[2])."\",";
		$grid.="\"".trim($result[3])."\",";
		$grid.="\"".number_format(trim($inv_balance),$decimalPref,".","")."\",";
		$grid.="\"".$deliveryMethod."\",";
		$grid.="\"".trim($result[12])."\",";
		//$grid.="\"".trim($result[13])."\",";
                $grid.="\"".gridcell($result[13])."\",";
		$grid.="\"".trim($orient_pdf)."\",";
		$temp_type = getDefaultTemp_Type($result[11],'edit');
		
		$grid.="\""."$temp_type?invservicedate=$servicedate&invservicedateto=$servicedateto&invlocation=$invlocation&invdept=$invdept&invclient=$invclient&acc=deliver&addr=".$result[0]."\"";
		

		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function getDeliveryMethod($clinet,$db)
{
	$getDelMethod="SELECT inv_method FROM staffacc_cinfo WHERE sno='".$clinet."'";
	$resDelMethod = mysql_query($getDelMethod,$db);
	$rowDelMethod = mysql_fetch_row($resDelMethod);
	return $rowDelMethod[0];
}

// Customers..Receive Payment register..
function displayPayRegister(&$data,$db)
{
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";

	while ($result = @mysql_fetch_array($data))
	{
        $sque="select cname from staffacc_cinfo where username='".$result[2]."'";
        $sres=mysql_query($sque,$db);
        $srow=mysql_fetch_row($sres);
        $clname=$srow[0];
        
        $qu="select reg_category.name from reg_category where sno=".$result[8];
        $res=mysql_query($qu,$db);
        $accname=mysql_fetch_row($res);

		$grid.="[";
		$grid.="\""."\",";
		$grid.="\"".gridcell($clname)."\",";
		$grid.="\"".trim($result[1])."\",";

        if($result[5]!="")
        {
            $qu="select checknumber,memo,paymethod from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            
			$grid.="\"".trim($dd1[2])."\",";
            $grid.="\"".trim($dd1[0])."\",";            
        }
        else
        {	
			$pay = "Payment";
			$grid.="\"".$pay."\",";
            $grid.="\""."&nbsp;"."\",";  
        }
        $grid.="\"".gridcell($accname[0])."\",";
        $grid.="\"".trim($result[4])."\",";
        $grid.="\""."editpayment.php?acc=receive&sno=".$result[0]."\"";

		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

//Employees main Page..
function DisplayEmployees(&$data,$db,$month,$year)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
        $val1 = _GetUserAccountsPayableEmp($result[2],$result[3],$result[4],$result[5],$month,$year,$db,$result[6],$result[7],$result[8]);
        $val2 = _GetUserAccountsRecievableEmp($result[2],$month,$year,$db);
        $val3 = _GetUserRevenue($val1,$val2);

       	$grid.="[";
		$grid.="\""."\",";
		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[0]))),ENT_QUOTES)."\",";
		$grid.="\"".number_format($val1, 2,".", "")."\",";
        $grid.="\"".number_format($val2, 2,".", "")."\",";
        $grid.="\"".number_format($val3, 2,".", "")."\",";
        $grid.="\"".trim($result[9])."\"";
        
		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function _GetUserRevenue($val1,$val2)
{
    return $val2-$val1;
}

//Account Payables -- payroll & deductions and earnings
function _GetUserAccountsPayableEmp($usern,$salary,$sthours,$ovrate,$month,$year,$db,$salper,$shper,$assid)
{
    //$eque="select if(sum(timesheet.thours)='',0,sum(timesheet.thours)),timesheet.sdate from timesheet Left JOIN par_timesheet ON timesheet.parid=par_timesheet.sno where timesheet.username='".$usern."' and (ISNULL(timesheet.payroll) or timesheet.payroll='') and DATE_FORMAT(timesheet.sdate,'%m-%Y')='".$month."-".$year."' and timesheet!='' and par_timesheet.astatus NOT IN ('Saved','ER','Rejected') group by timesheet.sdate";
    $eque="select if(sum(timesheet_hours.hours)='',0,sum(timesheet_hours.hours)),timesheet_hours.sdate from timesheet_hours Left JOIN par_timesheet ON timesheet_hours.parid=par_timesheet.sno where timesheet_hours.username='".$usern."' and DATE_FORMAT(timesheet_hours.sdate,'%m-%Y')='".$month."-".$year."' and par_timesheet.astatus NOT IN ('Saved','ER','Rejected') group by timesheet_hours.sdate";
    $eres=mysql_query($eque,$db);
    $total = 0.00;


    if($salper=="YEAR")
    {
        if($sthours!=0)
        {
            if($shper=="DAY")
                $sal=$salary/($sthours*261);
            else
                $sal=$salary/(($sthours*261)/5);
        }
    }
    else if($salper=="MONTH")
    {
        if($sthours!=0)
        {
            if($shper=="DAY")
                $sal=$salary/($sthours*(261/12));
            else
                $sal=$salary/($sthours*((261/5)/12));
        }
    }
    else if($salper=="WEEK")
    {
        if($sthours!=0)
        {
            if($shper=="DAY")
                $sal=$salary/($sthours*5);
            else
                $sal=$salary/$sthours;
        }
    }
    else if($salper=="DAY")
    {
        if($sthours!=0)
        {
            if($shper=="DAY")
                $sal=$salary/$sthours;
            else
                $sal=$salary/($sthours/5);
        }
    }
    else
    {
        $sal=$salary;
    }

    if($shper=="DAY")
        $stdhrs=$sthours;
    else
        $stdhrs=($sthours/5);

    $stweek=0;
    $etweek=0;

    while($assrow=mysql_fetch_row($eres))
    {
        $rrate=0.00;
        $otrate=0.00;

        if($assrow[0]>$stdhrs)
        {
            $otrate=$ovrate*($assrow[0]-$stdhrs);
            $rrate=$sal*$stdhrs;
            $total=round($total+$rrate+$otrate,2);
        }
        else
        {
            $rrate=$sal*$assrow[0];
            $total=round($total+$rrate,2);
        }
        // $gtotal=round($gtotal+$total,2);
    }
    return $total;
}  // End of Employee Payables

//Account Recievables Function..
function _GetUserAccountsRecievableEmp($usern,$month,$year,$db)
{
    //$qu="select hrcon_jobs.rate from hrcon_jobs where hrcon_jobs.username='".$usern."' and (IF(SUBSTRING_INDEX(hrcon_jobs.s_date,'-',-1)='".$year."',IF(SUBSTRING_INDEX(hrcon_jobs.s_date,'-',1)='".$month."',1,0),0)>0 OR  IF(SUBSTRING_INDEX(hrcon_jobs.e_date,'-',-1)='".$year."',IF(SUBSTRING_INDEX(hrcon_jobs.e_date,'-',1)='".$month."',1,0),0)>0) and hrcon_jobs.jtype='OP'";
    //$res=mysql_query($qu,$db);
    $total=0;


        $eque="select (timesheet_hours.hours),hj.rate,hj.rateper,hj.otrate,hc.std_hours,hc.shper from timesheet_hours LEFT JOIN par_timesheet ON timesheet_hours.parid=par_timesheet.sno LEFT JOIN hrcon_jobs hj ON timesheet_hours.assid=hj.pusername and timesheet_hours.username=hj.username LEFT JOIN hrcon_compen hc ON timesheet_hours.username=hc.username where timesheet_hours.username='".$usern."' and  par_timesheet.astatus NOT IN ('Saved','ER','Rejected') and timesheet_hours.billable!='' and hc.ustatus='active' and hj.ustatus IN ('active','closed','cancel') and DATE_FORMAT(timesheet_hours.sdate,'%m-%Y')='".$month."-".$year."' ";
        $eres=mysql_query($eque,$db);
        while($row=mysql_fetch_row($eres))
        {

                if($row[2]=="YEAR")
        		{
        			if($row[5]=="DAY")
        				$sal=$row[1]/($row[4]*261);
        			else
        				$sal=$row[1]/(($row[4]*261)/5);
        		}
        		else if($row[2]=="MONTH")
        		{
        			if($row[5]=="DAY")
        				$sal=$row[1]/($row[4]*(261/12));
        			else
        				$sal=$row[1]/($row[4]*((261/5)/12));
        		}
        		else if($row[2]=="WEEK")
        		{
        			if($row[5]=="DAY")
        				$sal=$row[1]/($row[4]*5);
        			else
        				$sal=$row[1]/$row[4];
        		}
        		else if($row[2]=="DAY")
        		{
        			if($row[5]=="DAY")
        				$sal=$row[1]/$row[4];
        			else
        				$sal=$row[1]/($row[4]/5);
        		}
        		else
        		{
        			$sal=$row[1];
        		}
                if(!$row[2]=="HOUR")
                {
                    if($row[5]=="DAY")
                    $stdhrs=$row[4];
                    else
                    $stdhrs=($row[4]/5);
                }
                else
                    $stdhrs=$row[0];

            if($row[0]>$stdhrs)
            {
              if(!is_null($row[3]))
                $otrate=$row[3]*($row[0]-$stdhrs);
              else
                $otrate=0;
                $rrate=$sal*$stdhrs;
                $total=round($total+$rrate+$otrate,2);
            }
            else
            {
            	$rrate=$sal*$row[0];
            	$total=round($total+$rrate,2);
            }
        }
        return round($total);
}  // End of Employee Recievables

function _GetType($val)
{
    if($val=="1099")
        return "Consultant";
    else if($val=="C-to-C")
        return "Sub Contractor";
    else if($val=="W-2")
        return "Employee";
    else
        return "Employee";
}

// Employees Payment Register..
//Modified by vijaya.t (06/03/2009) not to show the other type of payments except Employee Paycheck
//Re-modified by kumar raju to get type column when there is no QB preference in Admin -> Content Management
function displayPaymentEmployeesNew(&$data,$db,$extraFieldsArray,$payempid)
{
    global $companyname,$frmPage;
    
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$k=0;
	$grid.="var actdata = [\n";
	$check_status_grid=0;
	while ($result = @mysql_fetch_array($data))
	{
		$amountDue = $result[3] - $result[10];
		
		if($amountDue == 0)
			$amountDue = "PAID";
		else
			$amountDue =number_format($amountDue,2,'.','');
		
		if($result[5] == $payempid)
		{
		  $bold_open = "<b>";
		  $bold_close = "</b>";
		}
		else
		{
		  $bold_open = "";
		  $bold_close = "";
		}
			
        $grid.="[";
		//if(PAYROLL_PROCESS_BY == "QB")
            $grid.="\""."$bold_open<input type=checkbox name=auids[] id=".$result[6]." onclick=chk_clearTop() value=".$result[6].">\",";
      //  else
          //  $grid.="\""."\",";
		$grid.="\"".trim($result[2])."\",";
		$grid.="\"".trim($result[11])."\",";
		$grid.="\"".trim($result[7])."&nbsp;-&nbsp;".trim($result[8])."\",";
        $grid.="\"".trim($result[1])."\",";
		$grid.="\"".trim($result[9])."\",";
		//if(PAYROLL_PROCESS_BY != "QB")
			//$grid.="\"Employee Paycheck\",";	
		
		$grid.="\"".trim($result[3])."<input type=hidden name=amountTotal[] id=".$amountDue." value=".$amountDue.">\",";
		$grid.="\"".$amountDue."<input type=hidden name=amountDue[] id=".$result[3]."  value=".$result[3].">$bold_close\",";
		//if(PAYROLL_PROCESS_BY == "QB")
		//{
			
	//	}
			$url="../Pay_Employee/paycheckdetails.php?ref=emppayregister.php";
			if($frmPage!="")
				$url="/BSOS/Accounting/Pay_Employee/paycheckdetails.php?ref=$frmPage&frmPage=$frmPage";
            $grid.="\"$url&pp_id=".trim($result[5])."&gid=".trim($result[4])."&user_name=".$transactInfo[0]."\",";
		
        $j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
			
		$check_status_grid=1;
	}	
	/*if(PAYROLL_PROCESS_BY != "QB")
	{
		$j = 0;
		//Query that shows employee transaction starts here....
		$transactCond = ($extraFieldsArray[0] == 'None')?"":" AND bank_trans.payee = '".$extraFieldsArray[0]."'";
		$transactQry = "SELECT emp_list.username,DATE_FORMAT(bank_trans.dpdate,'%m/%d/%Y'),bank_trans.type,
		emp_list.name,bank_trans.amount , bank_trans.bankid,acc_reg.sno,bank_trans.account
		FROM bank_trans 
		LEFT JOIN emp_list ON bank_trans.payee = emp_list.username
		LEFT JOIN hrcon_w4 ON emp_list.username = hrcon_w4.username 
		LEFT JOIN acc_reg ON CONCAT('Dep',bank_trans.sno)=acc_reg.source
		WHERE bank_trans.type IN ('CHK','Payment','Deposit') ".$transactCond."  
		AND hrcon_w4.tax ='W-2' AND hrcon_w4.ustatus = 'active' 
		AND bank_trans.dpdate BETWEEN '".$extraFieldsArray[1]."'  AND '".$extraFieldsArray[2]."'";
		
		$transactRes = mysql_query($transactQry,$db);
		$my_row_count = @mysql_num_rows($transactRes);
		while($transactInfo = @mysql_fetch_array($transactRes))
		{
			$typeValue = $transactInfo[2];
			if($transactInfo[2] == 'CHK')
				$typeValue = 'Check';
			if($check_status_grid==1)
				$grid.=",[";
			else
				$grid.="[";
			$grid.="\""."\",";
			$grid.="\"".trim($transactInfo[1])."\",";
			$grid.="\"".$typeValue."\",";
			$grid.="\"".trim($transactInfo[3])."\",";
			$grid.="\"".trim($transactInfo[4])."\",";
			$grid.="\"../employees/depositDetails.php?acc=bank&category=".$transactInfo[5]."&sno=".$transactInfo[6]."&Trans=".$transactInfo[2]."&ebank".$transactInfo[5]."&account=".$transactInfo[7]."&user_name=".$transactInfo[0]."\",";
			$check_status_grid=2;
			$j++;
			if($j==$my_row_count)
				$grid.="]\n";
			else
				$grid.="],\n";	
		}
		//Ends here
	}*/
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function displayPaymentEmployees(&$data,$db)
{
    global $companyname;
    
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
        if($result[2]=="IH")
		{
			$clname = $companyname;
		}
		else
		{
			$que="select name from emp_list where username='".$result[2]."'";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$clname=$row[0];
		}

        $grid.="[";
		$grid.="\""."\",";
  
        $grid.="\"".trim($result[1])."\",";

        if($result[3] == "EmpPMT")
        {
            $qu="select bank_trans.checknumber,bank_trans.memo from bank_trans where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

            $grid.="\"".trim($dd1[0])."\",";
            $grid.="\""."Employee Payment"."\",";
            $grid.="\"".$clname."\",";
    	//	$grid.="\"".trim($dd1[1])."\",";
    		$grid.="\"".number_format($result[4], 2,".", "")."\",";
        }
		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// Pay_Employee.. Payroll main Page..
function showAllEmployee2(&$data,$db,$empSingle,$payperiod)
{
    global $pay,$fromdate,$todate,$paydate,$rundate,$ac_aced;
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";

    require("../Pay_Employee/Generatepayroll.php");
    $pay=new Generatepayroll();

	$tearning=$tdeductions=$tcontrib=$tnet=0;
	$rowsExists = false;
	while ($result = @mysql_fetch_array($data))
	{
		//$totalHours = chkRegularOverDoubleEarnings('',$result[0],$fromdate,$todate,'','','','','');
		$getDesignTotal = getRegularOverDoubleEarns('UpdateNet',$result[0],$fromdate,$todate,'','Display','','','');
		$getDesignTotalExplode = explode("^|^",$getDesignTotal);
		$totalHours = $getDesignTotalExplode[1];  
		
		$earning=$totalHours;
        $pay_days=$pay->getEmpPayDays($result[0]);
		if($result[3] == '---')
			$result[3] = "";
		else
			$result[3] = trim(trim($result[3],"-"));
			
		if($result[4] == '---')
			$result[4] = "";
		else
			$result[4] = trim(trim($result[4],"-"));			
	 		
		//$earning=$pay->getEmpEarnings($result[0]);
		$compcon=$pay->getCompContribustions($result[0],$earning);
		$id_value = $result[0];
		if($earning != 0){
			$rowsExists = true;
			$grid.="[";
			if((PAYROLL_PROCESS_BY =='QB' && PAYROLL_EMP == 'N'))
			{
				$grid.="\"".""."\",";
			}
			else
				$grid.="\""."<label class='container-chk'><input type=checkbox onclick=parent_check(document.forms[0].chk,this.name) name=auids[] id=".$id_value." value='".$id_value."'><span class='checkmark'></span></label>"."\",";
			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result[1]))),ENT_QUOTES)."\",";
			
			$grid.="\"".$result[5]."\",";
			$grid.="\"".$result[3]."\",";
			$grid.="\"".format_ssn($ac_aced->decrypt($result[4]))."\",";
			$grid.="\"".number_format($earning, 2,".", "")."\",";
			//$grid.="\"".bc_add($earning,$compcon,2)."\",";		// Have to define functionality for contributions
			if( $empSingle != "yes" )
				$grid.="\""."timesheets.php?euser=".$result[0]."&fromdate=".$fromdate."&todate=".$todate."&PayDate=".$paydate."&RunDate=".$rundate."&payperiod=".$payperiod."\"";
			else
				$grid.="\""."timesheets.php?euser=".$result[0]."&fromdate=".$fromdate."&todate=".$todate."&empSingle=yes&PayDate=".$paydate."&RunDate=".$rundate."&payperiod=".$payperiod."\"";
	
			$tearning=$tearning+$earning;
			$tcontrib=$tcontrib+$compcon;
			$tnet=$tnet+($earning+$compcon);
	
			/*$j++;
			if($j==$row_count)
				$grid.="]\n";
			else
				$grid.="],\n";*/
			$grid.="],\n";			
		}
	}
	if($rowsExists){
		$grid = substr($grid,0,strlen($grid)-2);
		$grid.="\n";	
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

//Employees -> Pay Employees -> Work Journal
Function DisplayNetPay(&$data,$db,$payempid)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	
	while ($result = @mysql_fetch_array($data))
	{
        $que="select SUM(earnings) from grosspay where pid=".$result[4];
        $res=mysql_query($que,$db);
        $row=mysql_fetch_row($res);
        $totearn=$row[0];

        $que="select SUM(netpay) from net_pay where pid=".$result[4];
        $res=mysql_query($que,$db);
        $row=mysql_fetch_row($res);
        $totdeduct=$totearn-$row[0];
		
		if($result[4] == $payempid)
		{
		  $bold_open = "<b>";
		  $bold_close = "</b>";
		}
		else
		{
		  $bold_open = "";
		  $bold_close = "";
		}
		
        $grid.="[";
       // $grid.="\""."\",";
       $grid.="\"$bold_open<input type=checkbox onclick=parent_check(document.forms[0].chk,this.name) name=auids[] id=".$result[4]."  value=".$result[4].">\",";
	   $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[5]))),ENT_QUOTES)."\",";
	   $grid.="\"".$result[13]."\",";
        $grid.="\"".$result[0]." - ".$result[1]."\",";
        $grid.="\"".$result[2]."\",";
        $grid.="\"".$result[3]."\",";
        $grid.="\"".number_format($totearn, 2,".", "")."\",";
        $grid.="\"".number_format($totdeduct, 2,".", "")."\",";
        $grid.="\"".bc_sub($totearn,$totdeduct,2)."$bold_close\",";
        //$grid.="\""."nethistory.php?pp_id=".$result[4]."\"";
		$grid.="\""."paycheckdetails.php?ref=netpay.php&pp_id=".$result[12]."&gid=".$result[11]."\"";

        $j++;

        if($j==$row_count)
            $grid.="]\n";
        else
            $grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

//Employees -> Approve PayStubs.
Function _GetLiabilitiesNet($data,$db,$servicedate,$servicedateto)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
		$dueAmount = $result[2] - $result[7];
    	$grid.="[";
		$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] id=".$result[3]." value=".$result[3]."><span class='checkmark'></span></label>\",";
    	$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result[0]))),ENT_QUOTES)."\",";
		$grid.="\"".trim($result[9])."\",";
    	$grid.="\"".trim($result[1])."\",";
		$grid.="\"".trim($result[6])."\",";
		$grid.="\"".trim($result[8])."\",";
    	$grid.="\"".number_format($result[2],2,".","")."\",";
		$grid.="\"".number_format($dueAmount,2,".","")."\",";
    	//$grid.="\""."javascript:doPays('".$result[3]."')"."\"";
		$grid.="\""."../Pay_Employee/paycheckdetails.php?servicedate=$servicedate&servicedateto=$servicedateto&pp_id=".$result[4]."&gid=".$result[5]."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

//Employees -> Pay Employees -> updates
Function DisplayPayroll(&$data,$db)
{
	require("../Pay_Employee/Generatepayroll.php");
    $pay=new Generatepayroll();
	
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
        $totearn=0;
		$totcon=0;
		
		$que="select earnings,ytdearn,status from grosspay where pid=".$result[4];
		$res=mysql_query($que,$db);
		while($row=mysql_fetch_row($res))
		{
            if($result[5]!="")
				$que1="select amount,per from companycon where sno in (".$result[5].")";
           
			if($row[2]=="run")
			{
				$sintdate=explode("/",$result[1]);
				$todate=$sintdate[2]."-".$sintdate[0]."-".$sintdate[1];
				$sintdate1=explode("/",$result[0]);
				$fromdate=$sintdate1[2]."-".$sintdate1[0]."-".$sintdate1[1];
				
				$UsernameAndDates = "payhistory|".$result[6]."|".$fromdate."|".$todate."|".$result[4];
				//$earning = chkRegularOverDoubleEarnings("payhistory",$result[6],$fromdate,$todate,$result[4],'','','','');
				$getDesignTotal = getRegularOverDoubleEarns('UpdateNet',$result[6],$fromdate,$todate,$result[4],'Display','','','');
				$getDesignTotalExplode = explode("^|^",$getDesignTotal);
				//$earning=$pay->getEmpEarnings($UsernameAndDates);
				$earning = $getDesignTotalExplode[1];
				$row[0] = $earning;
				$totearn=$totearn+$row[0];
			}
			else
				$totearn=$totearn+$row[0];
			$res1=mysql_query($que1,$db);
			while($row1=mysql_fetch_row($res1))
			{
				if($row[1]>$row1[0])
				{
					$totcon=$totcon;
				}
				else if(($row[1]+$row[0])>$row1[0])
				{
					$compcon=($row1[0]-$row[1])*($row1[1]/100);
					$totcon=$totcon+round($compcon,2);
				}
				else
				{
					$compcon=($row[0])*($row1[1]/100);
					$totcon=$totcon+round($compcon,2);
				}
			}
		}
    	$grid.="[";
		$grid.="\"<input type=checkbox onclick=parent_check(document.forms[0].chk,this.name) name=auids[] id=".$result[4]." value=".$result[4].">\",";
		
		if($result[12]=="run")
		{
			$status="Ran&nbsp;Gross&nbsp;Pay";			
		}
		else if($result[12]=="net")
		{
			$status="Ran&nbsp;Net&nbsp;Pay";			
		}
		else
		{
			$status="Paystub&nbsp;Generated";			
		}
		$grid.="\"".html_tls_specialchars(str_replace("\"","'",trim($result[7])),ENT_QUOTES)."\",";
		
        $que="select netpay,wid,did,coid from net_pay where pid='".$result[14]."'";
        $res=mysql_query($que,$db);
        $row=mysql_fetch_row($res);
		
		if($row[0] != '')
		{
			$WID = $row[1];
			$DID = $row[2];
			$CODID = $row[3];
			
			$w4Condition = "sno='".$WID."'";
			
			if($DID != '')
				$dedCondition = "sno IN (".$DID.")";
			else
				$dedCondition = "sno IN ('')";
			
		}	        
		else
		{
			$w4Condition = "username='".$result[8]."' and ustatus='active'";
			$dedCondition = "username='".$result[8]."' and ustatus='active'";
			
		}
		
		$que="select IF(fwh='',0,fwh),IF(swh='',0,swh),IF(sswh='',0,sswh),IF(mwh='',0,mwh),fstatus,tnum,sno,tstatetax,IF(aftaw='',0,aftaw),IF(astaw='',0,astaw),IF(localw1='','Local Withholding 1',localw1),IF(localw1_amt='',0,localw1_amt), IF(localw2='','Local Withholding 2',localw2),IF(localw2_amt='',0,localw2_amt),fwh_curr,aftaw_curr,swh_curr,astaw_curr,ssw_curr,mwh_curr,localw1_curr,localw2_curr from net_w4 where ".$w4Condition."";
		$res=mysql_query($que,$db);
		$wrow=mysql_fetch_row($res);
		
		if($wrow[14] == '%' || $wrow[14] == '')
		{
			$fedWithVal = ($totearn/100)*$wrow[0];					
		}
		else
		{
			$fedWithVal = $wrow[0];								
		}
		$fedWithVal = number_format($fedWithVal, 2,".", "");
		if($wrow[15] == '%' || $wrow[15] == '')
		{
			$AddfedWithVal = ($totearn/100)*$wrow[8];															
		}
		else
		{
			$AddfedWithVal = $wrow[8];				
		}
		$AddfedWithVal = number_format($AddfedWithVal, 2,".", "");
		if($wrow[16] == '%' || $wrow[16] == '')
		{
			$StateWithVal = ($totearn/100)*$wrow[1];														
		}
		else
		{
			$StateWithVal = $wrow[1];
		}
		$StateWithVal = number_format($StateWithVal, 2,".", "");
		if($wrow[17] == '%' || $wrow[17] == '')
		{
			$AddStateWithVal = ($totearn/100)*$wrow[9];														
		}
		else
		{
			$AddStateWithVal = $wrow[9];				
		}
		$AddStateWithVal = number_format($AddStateWithVal, 2,".", "");
		if($wrow[18] == '%' || $wrow[18] == '')
		{
			$SSWithVal = ($totearn/100)*$wrow[2];											
		}
		else
		{
			$SSWithVal = $wrow[2];							
		}
		$SSWithVal = number_format($SSWithVal, 2,".", "");
		if($wrow[19] == '%' || $wrow[19] == '')
		{
			$MedicareVal = ($totearn/100)*$wrow[3];													
		}
		else
		{
			$MedicareVal = $wrow[3];				
		}
		$MedicareVal = number_format($MedicareVal, 2,".", "");
		if($wrow[20] == '%' || $wrow[20] == '')
		{
			$LocalWithVal1 = ($totearn/100)*$wrow[11];											
		}
		else
		{
			$LocalWithVal1 = $wrow[11];						
		}
		$LocalWithVal1 = number_format($LocalWithVal1, 2,".", "");
		if($wrow[21] == '%' || $wrow[21] == '')
		{	
			$LocalWithVal2 = ($totearn/100)*$wrow[13];														
		}
		else
		{
			$LocalWithVal2 = $wrow[13];	
		}		
		$LocalWithVal2 = number_format($LocalWithVal2, 2,".", "");
		$btamount=0;
		$atamount=0;
		$deduct_id="";
		$que="select sno, username, type, title, amount, description, taxtype, compcon, ustatus, udate from net_deduct where ".$dedCondition."";
		$res=mysql_query($que,$db);
		while($row=mysql_fetch_row($res))
		{	
			$deduction=($row[4]*((100-$row[7])/100));
			//if($row[6]=="bt")
				$btamount=$btamount+$deduction;
				$btamount=number_format($btamount, 2,".", "");
			/*else
				$atamount=$atamount+$deduction;			*/	
		}		
			
		$totcon=$btamount+($fedWithVal+$AddfedWithVal+$StateWithVal+$AddStateWithVal+$SSWithVal+$MedicareVal+$LocalWithVal1+$LocalWithVal2);
		$totcon = number_format($totcon, 2,".", "");
		
		if(PAYROLL_PROCESS_BY == "VERTEX"){
		
			if($DID != '')
				$queS = "SELECT net_deduct.sno, net_deduct.username, net_deduct.type, net_deduct.title , net_deduct.dollar_amt_deduct, net_deduct.description, net_deduct.taxtype, net_deduct.compcon, net_deduct.ustatus, net_deduct.udate, net_deduct.tot_dollar_period , net_deduct.amount
						FROM net_deduct 						
						WHERE net_deduct.sno IN (".$DID.")							
						ORDER BY  net_deduct.title";
			else
				$queS = "SELECT net_deduct.sno, net_deduct.username, net_deduct.type, net_deduct.title , net_deduct.dollar_amt_deduct, net_deduct.description, net_deduct.taxtype, net_deduct.compcon, net_deduct.ustatus, net_deduct.udate, net_deduct.tot_dollar_period , net_deduct.amount
						FROM net_deduct 						
						WHERE net_deduct.username='".$result[8]."' 
						AND net_deduct.ustatus='active' 
						ORDER BY  net_deduct.title";
						
			if($CODID != '')
				$COIDque = "SELECT title, dollar_amt_deduct, tot_dollar_amt  
							FROM net_contribute 							
							WHERE sno IN (".$CODID.")							
							ORDER BY  title";
			else
				$COIDque = "SELECT title, dollar_amt_deduct, tot_dollar_amt 
							FROM hrcon_contribute 							
							WHERE username='".$result[8]."' AND contribution_chk = 'Y'	AND ustatus = 'active'					
							ORDER BY  title";
						
			$resS = mysql_query($queS,$db);
			$deductAmmount = 0;
			while($row=mysql_fetch_row($resS))
			{		
				if($row[10] == 0){
					$deduction = $row[4];
				}else{
					$deduction = ($totearn * $row[4])/100;
				}
				$deduction = number_format($deduction,2,".","");
				
				$deductAmmount = $deductAmmount + $deduction;									
			}
			
			$COIDres = mysql_query($COIDque,$db);
			while($COIDrow=mysql_fetch_row($COIDres))
			{						
				$deduction = $COIDrow[1];
				
				$deduction = number_format($deduction,2,".","");
				
				$deductAmmount = $deductAmmount + $deduction;				
			}			
			
			$employeeTaxArry = array('100','102','104','200','202','204','300','302','304','400','401','403','406','408','410','412','414','416','418','448','450','451','452','454','456','458','460','462','464','466','468','470','501','503','532','534','536','537','538','539','405','530');
			
			/*$federal_taxes_GEN_arr =array('400','405','478');
			$federal_taxes_EE_arr =array('401','403','406','408','410','412','414','416','418','472','474');
			$state_taxes_GEN_arr =array('450','451','538');
			$state_taxes_EE_arr =array('100','102','104','452','454','456','458','460','462','464','466','468','470');
			$county_taxes_GEN_arr =array('500','501');
			$county_taxes_EE_arr =array('200','202','204','448','476','503');
			$local_taxes_GEN_arr =array('530','536');
			$local_taxes_EE_arr =array('300','302','304','532','534');
			$school_taxes_arr =array('537','539');	
			
			$employeeTaxArry = array_merge($federal_taxes_GEN_arr,$federal_taxes_EE_arr,$state_taxes_GEN_arr,$state_taxes_EE_arr,$county_taxes_GEN_arr,$county_taxes_EE_arr,$local_taxes_GEN_arr,$local_taxes_EE_arr,$school_taxes_arr);*/
			
			$empTaxIds = implode(",",$employeeTaxArry);
			
			$getTotContrib = "SELECT SUM(tax_amt) AS Rate FROM vprt_paycheck_taxamts WHERE pid = '".$result[4]."' AND taxid IN (".$empTaxIds.") AND tax_amt > 0 GROUP BY pid ";
			$resTotContrib = mysql_query($getTotContrib,$db);
			$rowTotContrib = mysql_fetch_array($resTotContrib);
			$totcon = $rowTotContrib['Rate'] + $deductAmmount;
		}
			
		$grid.="\"".trim($result[17])."\",";	
    	$grid.="\"".$result[0]." - ".$result[1]."\",";
    	$grid.="\"".trim($result[2])."\",";
    	$grid.="\"".trim($result[3])."\",";
        $grid.="\"".number_format($totearn, 2,".", "")."\",";
        $grid.="\"".number_format($totcon, 2,".", "")."\",";
		$grid.="\"".bc_sub($totearn,$totcon,2)."\",";
		$grid.="\"".$status."\",";
        //$grid.="\"".bc_add($totearn,$totcon,2)."\",";			// Have to define contribution functionalty
        if($result[12]=="run")
		{
            $grid.="\""."paycheckdetails.php?pp_id=".$result[14]."&gid=".$result[13]."\"";
		}
		else if($result[12]=="net")
		{
            $grid.="\""."edeductions.php?pp_id=".$result[14]."&gid=".$result[13]."\"";
		}
		else
		{
            $grid.="\""."paycheck.php?ref=emphistory.php&pp_id=".$result[14]."&gid=".$result[13]."\"";
		}
       	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
// Employees -> Pay Employee ->(double click) timesheets.php
Function displayGenTimeManApprove(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";

	while ($result = @mysql_fetch_array($data))
	{
        $qu="select IF(timesheet_hours.hourstype='rate1',hours,0),IF(timesheet_hours.hourstype='rate2',hours,0),IF(timesheet_hours.hourstype='rate3',hours,0) from timesheet_hours LEFT JOIN par_timesheet ON timesheet_hours.parid=par_timesheet.sno where UNIX_TIMESTAMP(timesheet_hours.sdate)>='".$result[1]."' and UNIX_TIMESTAMP(timesheet_hours.sdate)<='".$result[2]."' and par_timesheet.username='".$result[6]."' and par_timesheet.astatus IN ('ER','Approved','Billed') AND timesheet_hours.status IN ('Approved','Billed') and (ISNULL(timesheet_hours.payroll) or timesheet_hours.payroll='') and timesheet_hours.parid=".$result[7];
        $hres=mysql_query($qu,$db);

       	$regular = 0;
		$overtime = 0;
		$double = 0;
        while ($hrow =mysql_fetch_array($hres))
        {
           	$regular = $regular+$hrow[0];
			$overtime = $overtime+$hrow[1];
			$double = $double+$hrow[2];
        }
		mysql_free_result($hres);
		$time = number_format($regular,2,'.','');
		$time1 = number_format($overtime,2,'.','');
		$time2 = number_format($double,2,'.','');
		$time3 = number_format($regular+$overtime+$double,2,'.','');
       	$grid.="[";
		$grid.="\""."\",";
        $grid.="\"".trim(date("m/d/Y",$result[10]))."\",";
        $grid.="\"".trim(date("m/d/Y",$result[11]))."\",";
        $grid.="\"".trim($time)."\",";
		$grid.="\"".trim($time1)."\",";
		$grid.="\"".trim($time2)."\",";
		$grid.="\"".trim($time3)."\",";
        $grid.="\""."javascript:ShowDet('".$result[0]."')\"";

		$j++;
		if($j==$row_count)
            $grid.="]\n";
		else
			$grid.="],\n";
   }
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// Employees -> Pay Employee -> Update -> (double click) emphistory.php
Function DisplayEachPayroll(&$data,$db)
{
	/*require("../Pay_Employee/Generatepayroll.php");
    $pay=new Generatepayroll();
	*/
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
        $grid.="[";
		$grid.="\""."\",";
        $totcon=0;
		
		if($result[5]=="run")
		{
			$sintdate=explode("/",$result[9]);
			$todate=$sintdate[2]."-".$sintdate[0]."-".$sintdate[1];
			$sintdate1=explode("/",$result[8]);
			$fromdate=$sintdate1[2]."-".$sintdate1[0]."-".$sintdate1[1];
			
			$UsernameAndDates = "emphistory|".$result[1]."|".$fromdate."|".$todate."|".$result[7];
			$earning = chkRegularOverDoubleEarnings("emphistory",$result[1],$fromdate,$todate,$result[7],'','','','');
			$result[2] = $earning;
		}
		
        if($result[4]!="")
            $que1="select amount,per from companycon where sno in (".$result[4].")";
        else
            $que1="select amount,per from companycon where sno in ('')";
		$res1=mysql_query($que1,$db);
		while($row1=mysql_fetch_row($res1))
		{
			if($result[3]>$row1[0])
			{
				$totcon=$totcon;
			}
			else if(($result[3]+$result[2])>$row1[0])
			{
				$compcon=($row1[0]-$result[3])*($row1[1]/100);
				$totcon=$totcon+round($compcon,2);
			}
			else
			{
				$compcon=($result[2])*($row1[1]/100);
				$totcon=$totcon+round($compcon,2);
			}
		}

		$tearning=$tearning+$result[2];
		$tcon=$tcon+$totcon;

		if($result[5]=="run")
		{
			$status="Ran&nbsp;Gross&nbsp;Pay";
			$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result[0]))),ENT_QUOTES)."\",";
		}
		else if($result[5]=="net")
		{
			$status="Ran&nbsp;Net&nbsp;Pay";
			$grid.="\"".trim($result[0])."\",";
		}
		else
		{
			$status="Paystub&nbsp;Generated";
			$grid.="\"".trim($result[0])."\",";
		}
    	$grid.="\"".number_format($result[2], 2,".", "")."\",";
    	$grid.="\"".number_format($totcon, 2,".", "")."\",";
		$grid.="\"".number_format($result[2], 2,".", "")."\",";
    	$grid.="\"".$status."\",";
    	if($result[5]=="run")
		{
            $grid.="\""."deductions.php?pp_id=".$result[7]."&gid=".$result[6]."\"";
		}
		else if($result[5]=="net")
		{
            $grid.="\""."edeductions.php?pp_id=".$result[7]."&gid=".$result[6]."\"";
		}
		else
		{
            $grid.="\""."paycheck.php?ref=emphistory.php&pp_id=".$result[7]."&gid=".$result[6]."\"";
		}

    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// Employees -> Pay Employee ->Work Journal ->(double click) nethistory.php
Function DisplayEachNetPay(&$data,$db)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
        $que="select netpay from net_pay where pid=".$result[7]." and gid=".$result[6];
		$res=mysql_query($que,$db);
		$row=mysql_fetch_row($res);
		$tnet=$tnet+$row[0];

    	$grid.="[";
		$grid.="\""."\",";
    	$grid.="\"".trim($result[0])."\",";
    	$grid.="\"".number_format($result[2], 2,".", "")."\",";
    	$grid.="\"".bc_sub($result[2],$row[0],2)."\",";
    	$grid.="\"".number_format($row[0], 2,".", "")."\",";
    	$grid.="\""."paycheck.php?ref=nethistory.php&pp_id=".$result[7]."&gid=".$result[6]."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

//Accounts -> Account Payable
Function displayWorkAccRegDet(&$data,$db,$category,$acc)
{
    global $companyname;
    
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
       	$grid.="[";
		if($result[2]=="IH")
		{
			$clname=$companyname;
		}
		else if(strpos("*".$result[2],"PE"))
		{
			$que="select name from reg_payee where CONCAT('PE',sno)='".$result[2]."'";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$clname=$row[0];
		}
		else
		{

			    $sque="select cname from staffacc_cinfo where username='".$result[2]."'";
				$sres=mysql_query($sque,$db);
				$norow=mysql_num_rows($sres);
				if ($norow>0)
                {
					$srow=mysql_fetch_row($sres);
				    //$tf="Client";
					$clname=$srow[0];
                }
				else
				{
					$sque="select name from emp_list where username='".$result[2]."'";
					$sres=mysql_query($sque,$db);
					$srow=mysql_fetch_row($sres);
					$cno=mysql_num_rows($sres);
					if($cno > 0)
					   $clname=$srow[0]." (Employee)";
                    else
                       $clname = "";
				}
		}
        $grid.="\"<input type=checkbox name=auids[] id=".$result[0]." value=".$result[0].">\",";
        $grid.="\"".$result[1]."\",";
		if($result[3]=="Deposit")
		{
		   if($result[5]!="")
		   {
                $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);
                $grid.="\"".$dd1[0]."\",";
		   }
		   else
		   {
                $grid.="\""."&nbsp;"."\",";
		   }
		   $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
		   $grid.="\""."Deposit"."\",";
		   $grid.="\""."&nbsp;"."\",";
           $grid.="\"".number_format($result[4], 2,".", "")."\",";
		   $grid.="\""."0.00"."\",";

		   $tot=$tot+$result[4];
		}
		else if($result[3]=="Bill")
		{
            $qu="select sno,IF(pay!='Yes',".tzRetQueryStringDate('due_date','Date','/').",'PAID') from bill where CONCAT('bil',sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

			$qu1_bill="select accnumber from qb_accreg where accsno='".$result[0]."'";
	        $re1_bill=mysql_query($qu1_bill,$db);
			$acc_count = @mysql_num_rows($re1_bill);
			
            if($acc_count > 0)
            {
                $no1=mysql_fetch_row($re1_bill);
                $grid.="\"".$no1[0]."\",";
            }
            else


            {
                $grid.="\"".$dd1[0]."\",";
            }

            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Bill"."\",";
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$result[4];
		}
		else if($result[3]=="CBILL")
		{
            $qu="select sno,IF(pay!='Yes',".tzRetQueryStringDate('due_date','Date','/').",'PAID') from cvbill where CONCAT('cbil',sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

            $qu1_cbill="select accnumber from qb_accreg where accsno='".$result[0]."'";
            $re1_cbill=mysql_query($qu1_cbill,$db);
			$cbill_rows=mysql_num_rows($rel_cbill);

            if($cbill_rows > 0)
            {
                $no1=mysql_fetch_row($rel_cbill);
                $grid.="\"".$no1[0]."\",";
            }
            else
            {
                $grid.="\""."&nbsp;"."\",";
            }
            
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Bill"."\",";
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$result[4];
		}//Added for Consultants Bill
		else if($result[3]=="CONBILL")
		{
            $qu="select sno,IF(pay!='Yes',".tzRetQueryStringDate('due_date','Date','/').",'PAID') from convbill where CONCAT('conbill',sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

            $qu1_conbill="select accnumber from qb_accreg where accsno='".$result[0]."'";
            $re1_conbill=mysql_query($qu1_conbill,$db);
			$conbill_rows=mysql_num_rows($re1_conbill);
            if($conbill_rows>0)
            {
                $no1=mysql_fetch_row($re1_conbill);
                $grid.="\"".$no1[0]."\",";
            }
            else
            {
                $grid.="\""."&nbsp;"."\",";
            }
            
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Bill"."\",";
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$result[4];
		}
		else if($result[3]=="Payment")
		{
		   if($result[5]!="")
		   {
                $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);
                $grid.="\"".$dd1[0]."\",";
		   }
		   else
		   {
                $grid.="\""."&nbsp;"."\",";
		   }
		   $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
		   $grid.="\""."Payment"."\",";
		   $grid.="\""."&nbsp;"."\",";
		   $grid.="\"".number_format($result[4], 2,".", "")."\",";
		   $grid.="\""."0.00"."\",";

		   $tot=$tot-$result[4];

		}//Changed BillCPM to BillCPMT
		else if($result[3]=="BillCPMT")
		{
		     $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
			 $re2=mysql_query($qu,$db);
			 $dd1=mysql_fetch_row($re2);
			 mysql_free_result($re2);
			 $grid.="\"".$dd1[1]."\",";
             $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
             $grid.="\""."Pay Bill"."\",";
             $grid.="\""." "."\",";
             $grid.="\""."0.00"."\",";
             $grid.="\"".number_format($result[4], 2,".", "")."\",";

		     $tot=$tot-$result[4];
		}
		else if($result[3]=="BillPMT")
		{
            $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".$clname."\",";
            $grid.="\""."Pay Bill"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."0.00"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";

            $tot=$tot-$result[4];
		}//added for Consultant_Bill_Payment
		else if($result[3]=="BILLCONPM")
		{
            $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Pay Bill"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."0.00"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";

            $tot=$tot-$result[4];
		}//Employee Payment
        else if($result[3]=="EmpPMT")
		{
            $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";

            $grid.="\""."Employee Payment"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."0.00"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";

            $tot=$tot-$result[4];
		}//for Net Pay
		else if($result[3]=="NETPAY")
		{
            $qu="select payacc_net.sno,IF(pay!='Yes',".tzRetQueryStringDate("str_to_date(payperiod.paydate,'%d/%m/%Y')",'Date','/').",'PAID'),payperiod.paydate from payacc_net LEFT JOIN payperiod ON payacc_net.netpayid=payperiod.sno where payacc_net.status='ER'and CONCAT('np',payacc_net.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."NETPAY"."\",";
            $grid.="\"".$dd1[2]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$result[4];
		}//for BENEFITS
		else if($result[3]=="BENEFITS")
		{
            $qu="select payacc_ded.sno,IF(pay!='Yes',".tzRetQueryStringDate("str_to_date(payperiod.paydate,'%d/%m/%Y')",'Date','/').",'PAID'),payperiod.paydate from payacc_ded LEFT JOIN payperiod ON payacc_ded.netpayid=payperiod.sno where payacc_ded.status='ER' and CONCAT('bft',payacc_ded.dedsno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."BENEFITS"."\",";
            $grid.="\"".$dd1[2]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$result[4];
		}//CONTRIBUTIONS
		else if($result[3]=="CONTRIB")
		{
            $qu="select payacc_con.sno,IF(pay!='Yes',".tzRetQueryStringDate("str_to_date(payperiod.paydate,'%d/%m/%Y')",'Date','/').",'PAID'),payperiod.paydate from payacc_con LEFT JOIN payperiod ON payacc_con.gppayid=payperiod.sno where payacc_con.status='ER' and CONCAT('conb',payacc_con.conno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."CONTRIB"."\",";
            $grid.="\"".$dd1[2]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$result[4];
		}//TAX.
		else if($result[3]=="TAX")
		{
            $qu="select net_w4.sno,".tzRetQueryStringDate('acc_reg.rdate','Date','/')." from net_w4 LEFT JOIN acc_reg ON acc_reg.source=CONCAT('tax',net_w4.sno) where net_w4.ustatus='active' and CONCAT('tax',net_w4.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."TAX"."\",";
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$result[4];
		}
		$grid.="\"".number_format($tot, 2,".", "")."\",";

        $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."\"";

    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// Accounts -> Expenses
function displayWorkAccRegDetG(&$data,$db,$category)
{
    global $companyname;
 
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
		$grid.="[";
        if($result[2]=="IH")
		{
			$clname=$companyname;
		}
		else if(strpos("*".$result[2],"PE"))
		{
			$que="select name from reg_payee where CONCAT('PE',sno)='".$result[2]."'";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$clname=$row[0];
		}
		else if($result[2]!="")
		{
			$sque="select cname from staffacc_cinfo where username='".$result[2]."'";
			$sres=mysql_query($sque,$db);
			$norow=mysql_num_rows($sres);
            if ($norow>0)
            {
                $srow=mysql_fetch_row($sres);
				$clname=$srow[0];
			}
			else
			{
				$sque="select name from emp_list where username='".$result[2]."'";
				$sres=mysql_query($sque,$db);
				$srow=mysql_fetch_row($sres);
				$ctno=mysql_num_rows($sres);
				if($ctno > 0)
				   $clname=$srow[0];
                else
                   $clname = "";
			}
		}
		 
        $grid.="\"<input type=checkbox name=auids[] id=".$result[0]." value=".$result[0].">\",";

		$grid.="\"".$result[1]."\",";

        if($result[3] == "Payment" || $result[3] == "ChkPMT")
		{
           if(strpos("*".$result[5],"Dep"))
		   {
                $qu="select bank_trans.checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);

                $grid.="\"".$dd1[0]."\",";
        		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
		   }
		   else
		   {
                $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                $re1=mysql_query($qu1,$db);

                if($re1)
                {
                    $no1=mysql_fetch_row($re1);
                    $grid.="\"".$no1[0]."\",";
                }
                else
                {
                    $grid.="\""."&nbsp;"."\",";
                }

        		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
		   }
		 
                $accnum = str_replace("Acc","",$result[5]);

                $eqact = "Capital/Equity";
                
                if(strpos("*".$result[5],"Acc"))
                {
                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);
                    mysql_free_result($res);
                }
                
                if(strpos("*".$result[5],"Dep"))
                {
                    $qu1="select name from reg_category where sno=".$result[8];
                    $res1=mysql_query($qu1,$db);
                    $depact=mysql_fetch_row($res1);
                    mysql_free_result($res1);
                }

                $cid = GetID($category,$db);
                $categid = explode(",",$cid);

                if(in_array($result[7],$categid))
                {
                    if (strpos("*".$result[5],"Acc"))
                        $grid.="\"".$account[0]."\",";
                    elseif (strpos("*".$result[5],"Dep") && $result[3] == 'ChkPMT')
                        $grid.="\"".$depact[0]."\",";
                    elseif (strpos("*".$result[5],"Dep"))
                        $grid.="\"".$depact[0]."\",";
                    elseif ($result[5] == "opbal")
                        $grid.="\"".addslashes($eqact)."\",";
                    else
                        $grid.="\""."&nbsp;"."\",";
                }
                else
                    $grid.="\"".$result[6]."\",";

                if(in_array($result[7],$categid))
                {
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                }
                elseif(in_array(str_replace('Acc','',$result[5]),$categid))
                {
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                }
                elseif(in_array($result[8],$categid))
                {
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                }
                else
                {
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                }
		}
		else if($result[3] == "Deposit" || $result[3] == "CHK")
		{
            if(strpos("*".$result[5],"Dep"))
            {
                $qu="select bank_trans.checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);
                $grid.="\"".$dd1[0]."\",";
            	$grid.="\"".$clname."\",";
            }
            else
            {
                $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                $re1=mysql_query($qu1,$db);

                if($re1)
                {
                    $no1=mysql_fetch_row($re1);
                    $grid.="\"".$no1[0]."\",";
                }
                else
                {
                    $grid.="\""."&nbsp;"."\",";
                }
                
        		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            }
                $accnum = str_replace("Acc","",$result[5]);
                
                $eqact = "Capital/Equity";

                if(strpos("*".$result[5],"Acc"))
                {
                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);
                    mysql_free_result($res);
                }
                
                if(strpos("*".$result[5],"Dep"))
                {
                    $qu1="select name from reg_category where sno=".$result[8];
                    $res1=mysql_query($qu1,$db);
                    $depact=mysql_fetch_row($res1);
                    mysql_free_result($res1);
                }

                $cid = GetID($category,$db);
                $categid = explode(",",$cid);

                if(in_array($result[7],$categid))
                {
                    if (strpos("*".$result[5],"Acc"))
                        $grid.="\"".$account[0]."\",";
                    elseif (strpos("*".$result[5],"Dep") && $result[3] == 'CHK')
                        $grid.="\"".$depact[0]."\",";
                    elseif (strpos("*".$result[5],"Dep"))
                        $grid.="\"".$depact[0]."\",";
                    elseif ($result[5] == "opbal")
                        $grid.="\"".addslashes($eqact)."\",";
                    else
                        $grid.="\""."&nbsp;"."\",";
                }
                else
                    $grid.="\"".$result[6]."\",";

                if(in_array($result[7],$categid))
                {
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                }
                elseif(in_array(str_replace('Acc','',$result[5]),$categid))
                {
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                }

                elseif(in_array($result[8],$categid))
                {
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                }
                else
                {
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                }
                
		}//for EXPenses..
		else if($result[3]=="EXP")
		{
            $qu="select rdate,type,description,amount from acc_reg where status='ER' and sno='".$result[0]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\""."&nbsp;"."\",";
    		$grid.="\"".$clname."\",";
    		$grid.="\"".$dd1[2]."\",";
    		$grid.="\"".number_format($dd1[3], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot+$dd1[3];
		}//for Gross Pay..( Not Using now)
		else if($result[3]=="GROSSPAY")
		{
            $qu="select type,description,amount from acc_reg where status='ER' and sno='".$result[0]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\"".$dd1[0]."\",";
            $grid.="\"".number_format($dd1[2], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";
            
            $tot=$tot-$dd1[2];
		}//TAXes..
		else if($result[3]=="TAX")
		{
            $qu="select type,description,amount from acc_reg where status='ER' and sno='".$result[0]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

            $grid.="\""."&nbsp;"."\",";
			$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\"".$dd1[0]."\",";
            $grid.="\"".number_format($dd1[2], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot-$dd1[2];
		}//for Net Pay..
		else if($result[3]=="NETPAY")
		{
            $qu="select payacc_net.sno,IF(pay!='Yes',DATE_FORMAT(payperiod.paydate,'%m/%d/%Y'),'PAID'),payperiod.paydate from payacc_net LEFT JOIN payperiod ON payacc_net.netpayid=payperiod.sno where payacc_net.status='ER' and CONCAT('np',payacc_net.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".$clname."\",";
            $grid.="\""."NETPAY"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot-$result[4];
		}//for BENEFITS
		else if($result[3]=="BENEFITS")
		{
            $qu="select payacc_ded.sno,IF(pay!='Yes',DATE_FORMAT(payperiod.paydate,'%m/%d/%Y'),'PAID'),payperiod.paydate from payacc_ded LEFT JOIN payperiod ON payacc_ded.netpayid=payperiod.sno where payacc_ded.status='ER' and CONCAT('bft',payacc_ded.dedsno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            
            $grid.="\""."&nbsp;"."\",";
			$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."BENEFITS"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot-$result[4];
		}//CONTRIBUTIONS
		else if($result[3]=="CONTRIB")
		{
            $qu="select payacc_con.sno,IF(pay!='Yes',DATE_FORMAT(payperiod.paydate,'%m/%d/%Y'),'PAID'),payperiod.paydate from payacc_con LEFT JOIN payperiod ON payacc_con.gppayid=payperiod.sno where payacc_con.status='ER' and CONCAT('conb',payacc_con.conno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."CONTRIB"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

            $tot=$tot-$result[4];
		}//Employee Payment
        else if($result[3]=="EmpPMT")
		{
            $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Employee&nbsp;Payment"."\",";
            //$grid.="\""."&nbsp;"."\",";
            $grid.="\""."0.00"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";

            //$tot=$tot-$result[4];
            $tot=$tot+$result[4];
		}
		
		$grid.="\"".number_format($tot, 2,".", "")."\"";
		
		// for Self - Transactions..
        if ($result[7] == str_replace('Acc','',$result[5]))
        {
            $grid.="],\n";

            $grid.="[";
            $grid.="\"<input type=checkbox name=auids[] id=".$result[0]." value=".$result[0].">\",";
    		$grid.="\"".$result[1]."\",";

            if($result[3] == "Payment")
            {
                if($result[5]!="")
                {
    		        $qu="select bank_trans.checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                    $re2=mysql_query($qu,$db);
                    $dd1=mysql_fetch_row($re2);

                    if($dd1[0] != "")
                    {
                        mysql_free_result($re2);
                        $grid.="\"".$dd1[0]."\",";
                    }
                    else
                    {
                        $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                        $re1=mysql_query($qu1,$db);

                        if($re1)
                        {
                            $no1=mysql_fetch_row($re1);
                            $grid.="\"".$no1[0]."\",";
                        }
                        else
                        {
                            $grid.="\""."&nbsp;"."\",";
                        }
                    }
            		$grid.="\"".$clname."\",";
                }
                else
                {
                    $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                    $re1=mysql_query($qu1,$db);

                    if($re1)
                    {
                        $no1=mysql_fetch_row($re1);
                        $grid.="\"".$no1[0]."\",";
                    }
                    else
                    {
                        $grid.="\""."&nbsp;"."\",";
                    }

            		$grid.="\"".$clname."\",";
    		   }
                    $accnum = str_replace("Acc","",$result[5]);
                    $eqact = "Capital/Equity";

                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);

                    $cid = GetID($category,$db);
                    $categid = explode(",",$cid);

                    if(in_array($result[7],$categid))
                    {
                        if (strpos("*".$result[5],"Acc"))
                        {
                            $grid.="\"".$account[0]."\",";
                            $edbanksno=$accnum;
                         }

                        elseif ($result[5] == "opbal")
                        {
                            $grid.="\"".addslashes($eqact)."\",";
                            $edbanksno=6;
                        }

                        else
                           $grid.="\""."&nbsp;"."\",";
                    }
                    else
                    {
                        $grid.="\"".$result[6]."\",";
                        $edbanksno=$result[7];
                    }

                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                    
    		}
    		else if($result[3] == "Deposit")
            {
                if($result[5]!="")
                {
                    $qu="select bank_trans.checknumber,bank_trans.payee from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                    $re2=mysql_query($qu,$db);
                    $dd1=mysql_fetch_row($re2);

                    if($dd1[0] != "")
                    {
                        mysql_free_result($re2);
                        $grid.="\"".$dd1[0]."\",";
                    }
                    else
                    {
                        $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                        $re1=mysql_query($qu1,$db);

                        if($re1)
                        {
                            $no1=mysql_fetch_row($re1);
                            $grid.="\"".$no1[0]."\",";
                        }
                        else
                        {
                            $grid.="\""."&nbsp;"."\",";
                        }
                    }
            		$grid.="\"".$clname."\",";
                }
                else
                {
                    $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                    $re1=mysql_query($qu1,$db);

                    if($re1)
                    {
                        $no1=mysql_fetch_row($re1);
                        $grid.="\"".$no1[0]."\",";
                    }
                    else
                    {
                        $grid.="\""."&nbsp;"."\",";
                    }

            		$grid.="\"".$clname."\",";
                }

                    $accnum = str_replace("Acc","",$result[5]);
                    $eqact = "Capital/Equity";

                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);
                    mysql_free_result($res);

                    $cid = GetID($category,$db);
                    $categid = explode(",",$cid);

                    if(in_array($result[7],$categid))
                    {
                        if (strpos("*".$result[5],"Acc"))
                        {
                            $grid.="\"".$account[0]."\",";
                            $edbanksno=$accnum;
                        }
                        elseif ($result[5] == "opbal")
                        {
                            $grid.="\"".addslashes($eqact)."\",";
                            $edbanksno=6;
                        }
                        else
                            $grid.="\""."&nbsp;"."\",";
                    }
                    else
                    {
                        $grid.="\"".$result[6]."\",";
                        $edbanksno=$result[7];
                    }
                    
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";

                    $tot=$tot-$result[4];
            }

            $grid.="\"".number_format($tot, 2,".", "")."\"";
        } // End of self-trnsactions.

		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// The following funtion is used for all except for Accounts ->  A/C payables, A/C receivables , expense & banks...
function displayWorkAccRegDetG1(&$data,$db,$category,$acc)
{
    global $companyname;
    
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
		$grid.="[";
        if($result[2]=="IH")
		{
			$clname=$companyname;
		}
		else if(strpos("*".$result[2],"PE"))
		{
			$que="select name from reg_payee where CONCAT('PE',sno)='".$result[2]."'";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$clname=$row[0];
		}
		else
		{
			$sque="select cname from staffacc_cinfo where username='".$result[2]."'";
			$sres=mysql_query($sque,$db);
			$norow=mysql_num_rows($sres);
			if ($norow>0)
            {
                $srow=mysql_fetch_row($sres);
				$clname=$srow[0];
            }
			else
			{
				$sque="select name from emp_list where username='".$result[2]."'";
				$sres=mysql_query($sque,$db);
				$srow=mysql_fetch_row($sres);
				$cno=mysql_num_rows($sres);
				if($cno > 0)
				   $clname=$srow[0]." (Employee)";
                else
                   $clname = "";
			}
        }
        $grid.="\"<input type=checkbox name=auids[] id=".$result[0]." value=".$result[0].">\",";
		$grid.="\"".$result[1]."\",";

        if($result[3] == "Payment" || $result[3] == "ChkPMT")
		{
		   if($result[5]!="")
		   {
		        $qu="select bank_trans.checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);

                if($dd1[0] != "")
                {
                    mysql_free_result($re2);
                    $grid.="\"".$dd1[0]."\",";
                }
                else
                {
                    $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                    $re1=mysql_query($qu1,$db);

                    if($re1)
                    {
                        $no1=mysql_fetch_row($re1);
                        $grid.="\"".$no1[0]."\",";
                    }
                    else
                    {
                        $grid.="\""."&nbsp;"."\",";
                    }
                }
        		$grid.="\"".$clname."\",";
		   }
		   else
		   {
                $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                $re1=mysql_query($qu1,$db);

                if($re1)
                {
                    $no1=mysql_fetch_row($re1);
                    $grid.="\"".$no1[0]."\",";
                }
                else
                {
                    $grid.="\""."&nbsp;"."\",";
                }
                
        		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
		   }
                $accnum = str_replace("Acc","",$result[5]);
                $eqact = "Capital/Equity";
                
                if(strpos("*".$result[5],"Acc"))
                {
                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);
                    mysql_free_result($res);
                }
                
                if(strpos("*".$result[5],"Dep"))
                {
                    $qu1="select name from reg_category where sno=".$result[8];
                    $res1=mysql_query($qu1,$db);
                    $depact=mysql_fetch_row($res1);
                    mysql_free_result($res1);
                }
                
                $cid = GetID($category,$db);
                $categid = explode(",",$cid);
                
                if(in_array($result[7],$categid))
                {
                    if (strpos("*".$result[5],"Acc"))
                    {
                        $grid.="\"".$account[0]."\",";
                        $edbanksno=$accnum;
                    }
                    elseif (strpos("*".$result[5],"Dep"))
                    {
                        $grid.="\"".$depact[0]."\",";
                        $edbanksno=$result[8];
                    }
                    elseif ($result[5] == "opbal")
                    {
                        $grid.="\"".addslashes($eqact)."\",";
                        $edbanksno=6;
                    }
                    else
                        $grid.="\""."&nbsp;"."\",";
                }
                else
                {
                    $grid.="\"".$result[6]."\",";
                    if (strpos("*".$result[5],"Acc"))
                    {
                       $edbanksno=$result[7];
                    }
                    elseif (strpos("*".$result[5],"Dep"))
                    {
                       $edbanksno=$result[7];
                    }
                    elseif ($result[5] == "opbal")
                    {
                       $edbanksno=6;
                    }
                    else
                    {
                        $edbanksno=$result[8];
                    }
                }

                if(in_array($result[7],$categid))
                {
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Payment&account=".$edbanksno."\",";
                    $grid.="\""."Payment|"."\"";
                }
                elseif(in_array(str_replace('Acc','',$result[5]),$categid))
                {
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Deposit&ebank=".$edbanksno."&account=".$edbanksno."\",";
                    $grid.="\""."Deposit|".$edbanksno."\"";
                }
                elseif(in_array($result[8],$categid))
                {
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Deposit&ebank=".$edbanksno."&account=".$edbanksno."\",";
                    $grid.="\""."Deposit|".$edbanksno."\"";
                }
                elseif($result[5] == 'opbal')
                {
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Deposit&ebank=".$edbanksno."&account=".$edbanksno."\",";
                    $grid.="\""."Deposit|".$edbanksno."\"";
                }
                else
                {
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Payment&account=".$edbanksno."\",";
                    $grid.="\""."Payment|"."\"";
                }
		}
		else if($result[3] == "Deposit" || $result[3] == "CHK")
		{
		   if($result[5]!="")
		   {
		        $qu="select bank_trans.checknumber,bank_trans.payee from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);

                if($dd1[0] != "")
                {
                    mysql_free_result($re2);
                    $grid.="\"".$dd1[0]."\",";
                }
                else
                {
                    $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                    $re1=mysql_query($qu1,$db);

                    if($re1)
                    {
                        $no1=mysql_fetch_row($re1);
                        $grid.="\"".$no1[0]."\",";
                    }
                    else
                    {
                        $grid.="\""."&nbsp;"."\",";
                    }
                }
        		$grid.="\"".$clname."\",";
		   }
		   else
		   {
                $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                $re1=mysql_query($qu1,$db);

                if($re1)
                {
                    $no1=mysql_fetch_row($re1);
                    $grid.="\"".$no1[0]."\",";
                }
                else
                {
                    $grid.="\""."&nbsp;"."\",";
                }
               
        		$grid.="\"".$clname."\",";
		   }
		   
                $accnum = str_replace("Acc","",$result[5]);
                $eqact = "Capital/Equity";
                
                if(strpos("*".$result[5],"Acc"))
                {
                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);
                    mysql_free_result($res);

                }
                
                if(strpos("*".$result[5],"Dep"))
                {
                    $qu1="select name from reg_category where sno=".$result[8];
                    $res1=mysql_query($qu1,$db);
                    $depact=mysql_fetch_row($res1);
                    mysql_free_result($res1);
                }
                $cid = GetID($category,$db);
                $categid = explode(",",$cid);
                
                if(in_array($result[7],$categid))
                {
                    if (strpos("*".$result[5],"Acc"))
                    {
                        $grid.="\"".$account[0]."\",";
                        $edbanksno=$accnum;
                    }
                    elseif (strpos("*".$result[5],"Dep"))
                    {
                        $grid.="\"".$depact[0]."\",";
                        $edbanksno=$result[8];
                    }
                    elseif ($result[5] == "opbal")
                    {
                        $grid.="\"".addslashes($eqact)."\",";
                          $edbanksno=6;
                    }
                    else
                    {
                        $grid.="\""."&nbsp;"."\",";
                        $edbanksno=$result[8];
                    }
                }
                else
                {
                    $grid.="\"".$result[6]."\",";
                    
                    if ($result[5] == "opbal")
                    {
                        $edbanksno=6;
                    }
                    else
                    {
                       $edbanksno=$result[7];
                    }
                }
                
               
                if(in_array($result[7],$categid))
                {   
                   
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Deposit&account=".$edbanksno."\",";
                    $grid.="\""."Deposit|"."\"";
                }
                elseif(in_array(str_replace('Acc','',$result[5]),$categid))
                {   
                    
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Payment&account=".$edbanksno."&ebank=".$edbanksno."\",";
                    $grid.="\""."Payment|".$edbanksno."\"";
                }
                elseif(in_array($result[8],$categid))
                {   
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Payment&account=".$edbanksno."&ebank=".$edbanksno."\",";
                    $grid.="\""."Payment|".$edbanksno."\"";
                }
                elseif($result[5] == 'opbal')
                {   
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    $tot=$tot-$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Payment&account=".$edbanksno."&ebank=".$edbanksno."\",";
                    $grid.="\""."Payment|".$edbanksno."\"";
                }
                else
                {   
                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Deposit&account=".$edbanksno."\",";
                    $grid.="\""."Deposit|"."\"";
                }
		}
	//	$grid.="\"".number_format($tot, 2,".", "")."\",";
        
      //  $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."\"";
        
        // for Self - Transactions..
        if ($result[7] == str_replace('Acc','',$result[5]))
        {
            $grid.="],\n";
            
            $grid.="[";
            $grid.="\"<input type=checkbox name=auids[] id=".$result[0]." value=".$result[0].">\",";
    		$grid.="\"".$result[1]."\",";

            if($result[3]=="Payment")
    		{
    		   if($result[5]!="")
    		   {
    		        $qu="select bank_trans.checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                    $re2=mysql_query($qu,$db);
                    $dd1=mysql_fetch_row($re2);

                    if($dd1[0] != "")
                    {
                        mysql_free_result($re2);
                        $grid.="\"".$dd1[0]."\",";
                    }
                    else
                    {
                        $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                        $re1=mysql_query($qu1,$db);

                        if($re1)
                        {
                            $no1=mysql_fetch_row($re1);
                            $grid.="\"".$no1[0]."\",";
                        }
                        else
                        {
                            $grid.="\""."&nbsp;"."\",";
                        }
                    }
            		$grid.="\"".$clname."\",";
    		   }
    		   else
    		   {
                    $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                    $re1=mysql_query($qu1,$db);

                    if($re1)
                    {
                        $no1=mysql_fetch_row($re1);
                        $grid.="\"".$no1[0]."\",";
                    }
                    else
                    {
                        $grid.="\""."&nbsp;"."\",";
                    }

            		$grid.="\"".$clname."\",";
    		   }
                    $accnum = str_replace("Acc","",$result[5]);
                    $eqact = "Capital/Equity";

                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);

                    $cid = GetID($category,$db);
                    $categid = explode(",",$cid);

                    if(in_array($result[7],$categid))
                    {
                        if (strpos("*".$result[5],"Acc"))
                        {
                            $grid.="\"".$account[0]."\",";
                            $edbanksno=$accnum;
                         }
                         
                        elseif ($result[5] == "opbal")
                        {
                            $grid.="\"".addslashes($eqact)."\",";
                            $edbanksno=6;
                        }
                            
                        else
                           $grid.="\""."&nbsp;"."\",";
                    }
                    else
                    {
                        $grid.="\"".$result[6]."\",";
                        $edbanksno=$result[7];
                    }

                    $grid.="\""."0.00"."\",";
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $tot=$tot+$result[4];
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Deposit&account=".$edbanksno."&ebank=".$edbanksno."\",";
                    $grid.="\""."Deposit|".$edbanksno."\"";
                    
    		}
    		else if($result[3]=="Deposit")
    		{
    		   if($result[5]!="")
    		   {
    		        $qu="select bank_trans.checknumber,bank_trans.payee from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                    $re2=mysql_query($qu,$db);
                    $dd1=mysql_fetch_row($re2);

                    if($dd1[0] != "")
                    {
                        mysql_free_result($re2);
                        $grid.="\"".$dd1[0]."\",";
                    }
                    else
                    {
                        $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                        $re1=mysql_query($qu1,$db);

                        if($re1)
                        {
                            $no1=mysql_fetch_row($re1);
                            $grid.="\"".$no1[0]."\",";
                        }
                        else
                        {
                            $grid.="\""."&nbsp;"."\",";
                        }
                    }
            		$grid.="\"".$clname."\",";
    		   }
    		   else
    		   {
                    $qu1="select accnumber from qb_accreg where sno='".$result[0]."'";
                    $re1=mysql_query($qu1,$db);

                    if($re1)
                    {
                        $no1=mysql_fetch_row($re1);
                        $grid.="\"".$no1[0]."\",";
                    }
                    else
                    {
                        $grid.="\""."&nbsp;"."\",";
                    }

            		$grid.="\"".$clname."\",";
    		   }

                    $accnum = str_replace("Acc","",$result[5]);
                    $eqact = "Capital/Equity";

                    $qu="select name from reg_category where sno=".$accnum;
                    $res=mysql_query($qu,$db);
                    $account=mysql_fetch_row($res);
                    mysql_free_result($res);

                    $cid = GetID($category,$db);
                    $categid = explode(",",$cid);

                    if(in_array($result[7],$categid))
                    {
                        if (strpos("*".$result[5],"Acc"))
                        {
                            $grid.="\"".$account[0]."\",";
                            $edbanksno=$accnum;
                        }
                        elseif ($result[5] == "opbal")
                        {
                            $grid.="\"".addslashes($eqact)."\",";
                            $edbanksno=6;
                        }
                        else
                            $grid.="\""."&nbsp;"."\",";
                            
                    }
                    else
                    {
                        $grid.="\"".$result[6]."\",";
                        $edbanksno=$result[7];
                    }

                    
                    $grid.="\"".number_format($result[4], 2,".", "")."\",";
                    $grid.="\""."0.00"."\",";
                    
                    $tot=$tot-$result[4];
                    
                    
                    $grid.="\"".number_format($tot, 2,".", "")."\",";
                    $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."&Trans=Payment&account=".$edbanksno."&ebank=".$edbanksno."\",";
                    $grid.="\""."Payment|".$edbanksno."\"";

    		}
    	//	$grid.="\"".number_format($tot, 2,".", "")."\",";

          //  $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."\"";
        }
		$j++;
		
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// Accounts -> Account receivables
function displayWorkAcRRegDet1(&$data,$db,$category,$acc)
{
    global $companyname;
    
    $grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
		$grid.="[";

        if($result[2]=="IH")
		{
			$clname=$companyname;
		}
		else if(strpos("*".$result[2],"PE"))
		{
			$que="select name from reg_payee where CONCAT('PE',sno)='".$result[2]."'";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$clname=$row[0];
		}
		else
		{
			$sque="select cname from staffacc_cinfo where username='".$result[2]."'";
			$sres=mysql_query($sque,$db);
			$norow=mysql_num_rows($sres);

			if ($norow>0)
            {
                $srow=mysql_fetch_row($sres);
			    //$tf="Client";
				$clname=$srow[0];
            }
			else
			{
				$sque="select name from emp_list where username='".$result[2]."'";
				$sres=mysql_query($sque,$db);
				$srow=mysql_fetch_row($sres);
				$cno=mysql_num_rows($sres);
				if($cno > 0)
				   $clname = $srow[0]." (Employee)";
                else
                   $clname = "";
			}
		}
        $grid.="\"<input type=checkbox name=auids[] id=".$result[0]." value=".$result[0].">\",";
        $grid.="\"".$result[1]."\",";
		
		if($result[3]=="Invoice")
		{
            $qu="select sno,IF(billed='no',due_date,'PAID'),invoice_date from invoice where CONCAT('inv',sno)='".$result[5]."' AND status = 'ACTIVE'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
		   
            $qu1="select accnumber from qb_accreg where accsno='".$result[0]."'";
            $re1=mysql_query($qu1,$db);
            $num_rows = mysql_num_rows($re1);

            if( $num_rows > 0 )
            {
                $no1=mysql_fetch_row($re1);
                $grid.="\"".$no1[0]."\",";
            }
            else
            {
                $grid.="\"".$dd1[0]."\",";
            }
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Invoice"."\",";
            $grid.="\"".$dd1[2]."\",";
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";

		    $tot=$tot+$result[4];
		}
		else if($result[3]=="Payment")
		{
            if($result[5]!="")
            {
                $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);
                $grid.="\"".$dd1[0]."\",";
            }
            else
            {
                $grid.="\""."&nbsp;"."\",";
            }
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Payment"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            $grid.="\""."0.00"."\",";
            
            $tot=$tot+$result[4];
		}
		else if($result[3]=="Deposit")
		{
            if($result[5]!="")
            {
                $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);
                $grid.="\"".$dd1[0]."\",";
            }
            else
            {
                $grid.="\""."&nbsp;"."\",";
            }
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Deposit"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."0.00"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            
            $tot=$tot-$result[4];
		}
        else if($result[3]=="PMT")
        {
            $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$clname)),ENT_QUOTES)."\",";
            $grid.="\""."Recieved Payment"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."0.00"."\",";
            $grid.="\"".number_format($result[4], 2,".", "")."\",";
            
            $tot=$tot-$result[4];
        }
		else
		{
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
            $grid.="\""."&nbsp;"."\",";
		}
        $grid.="\"".number_format($tot, 2,".", "")."\",";

        $grid.="\""."showbill.php?acc=".$acc."&bank=".$category."&sno=".$result[0]."&cat=".$result[7]."\"";
        
		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

// Employees -> Link at account payables
Function DisplayEmployeesPayables($data,$db,$month,$year,$usern,$salary,$sthours,$ovrate,$salper,$shper,$addr)
{
    $grid="<"."script".">\n";
   $row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);


	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	   {
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	   }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";

    if($salper=="YEAR")
    {
    	if($shper=="DAY")
    		$sal=$salary/($sthours*261);
    	else
    		$sal=$salary/(($sthours*261)/5);
    }
    else if($salper=="MONTH")
    {
    	if($shper=="DAY")
    		$sal=$salary/($sthours*(261/12));
    	else
    		$sal=$salary/($sthours*((261/5)/12));
    }
    else if($salper=="WEEK")
    {
    	if($shper=="DAY")
    		$sal=$salary/($sthours*5);
    	else
    		$sal=$salary/$sthours;
    }
    else if($salper=="DAY")
    {
    	if($shper=="DAY")
    		$sal=$salary/$sthours;
    	else
    		$sal=$salary/($sthours/5);
    }
    else
    {
    	$sal=$salary;
    }

    if($shper=="DAY")
        $stdhrs=$sthours;
    else
        $stdhrs=($sthours/5);

    $stweek=0;
    $etweek=0;
    $gtotal=0.00;


	while ($result = @mysql_fetch_array($data))
	{

        $qu1="select staffacc_cinfo.cname from staffacc_cinfo where username='".$result[2]."'";
        $re1=mysql_query($qu1,$db);
        $clr=mysql_fetch_row($re1);
        mysql_free_result($re1);


		$assquery="select if(sum(timesheet_hours.hours)='',0,sum(timesheet_hours.hours)),DATE_FORMAT(timesheet_hours.sdate,'%u') from timesheet_hours LEFT JOIN par_timesheet ON timesheet_hours.parid=par_timesheet.sno where timesheet_hours.username='".$addr."' and (ISNULL(timesheet_hours.payroll) or timesheet_hours.payroll='') and DATE_FORMAT(timesheet_hours.sdate,'%m-%Y')='".$month."-".$year."' group by timesheet_hours.sdate";
        $assres=mysql_query($assquery,$db);
        $total=0.00;
        while($assrow=mysql_fetch_row($assres))
        {
            $rrate=0.00;
            $otrate=0.00;
            if($assrow[0]>$stdhrs)
            {
            	$otrate=$ovrate*($assrow[0]-$stdhrs);
            	$rrate=$sal*$stdhrs;
            	$total=round($total+$rrate+$otrate,2);
            }
            else
            {
            	$rrate=$sal*$assrow[0];
            	$total=round($total+$rrate,2);
            }
        }

        $gtotal=round($gtotal+$total,2);
    	$grid.="[";
		$grid.="\""."\",";
    	$grid.="\"".$result[1]."\",";
    	$grid.="\"".$clr[0]."\",";
    	$grid.="\"".$result[3]."\",";
    	$grid.="\"".$sal."/ Hr.\",";
        $grid.="\"".($result[0])."\",";
        $grid.="\"".number_format($total, 2,".", "")."\",";
        $j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

Function DisplayEmployeesRecievables($data,$db,$month,$year)
{
    $grid="<"."script".">\n";
   $row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);


	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	   {
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	   }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";

	while ($result = @mysql_fetch_array($data))
	{
    	$grid.="[";
    	$grid.="\""."\",";
        $qu1="select staffacc_cinfo.cname from staffacc_cinfo where username='".$result[1]."'";
        $re1=mysql_query($qu1,$db);
        $clr=mysql_fetch_row($re1);
        mysql_free_result($re1);
    //    $eque="select ((timesheet.t_hours*60)+timesheet.t_mins),DATE_FORMAT(sheet_date,'%m/%d/%Y') from timesheet where timesheet.username='".$data[3]."' and payroll!='' and astatus='Billed' and DATE_FORMAT(timesheet.sheet_date,'%m')='".$month."' and timesheet.sheet_date='".$year."'";

        $eque="select SUM(timesheet_hours.hours),CONCAT(DATE_FORMAT(par_timesheet.sdate,'%m/%d/%Y'),' - ',DATE_FORMAT(par_timesheet.edate,'%m/%d/%Y')) from timesheet_hours Left JOIN par_timesheet ON timesheet_hours.parid=par_timesheet.sno where timesheet_hours.username='".$result[3]."' and (ISNULL(timesheet_hours.payroll) or timesheet_hours.payroll='') and par_timesheet.astatus='Billed' and DATE_FORMAT(timesheet_hours.sdate,'%m-%Y')='".$month."-".$year."' group by timesheet_hours.parid";

        $eres=mysql_query($eque,$db);
        while($datar=mysql_fetch_row($eres))
        {
            $rate=$result[0]*($datar[0]);

            $grid.="\"".$datar[1]."\",";
            $grid.="\"".$clr[0]."\",";
            $grid.="\"".$result[2]."\",";
            $grid.="\"".$result[0]."\",";
            $grid.="\"".($datar[0])."\",";
            $grid.="\"".$rate."\",";
            $j++;
    		if($j==$row_count)
    			$grid.="]\n";
    		else
    			$grid.="],\n";
	   }
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

Function _GetLiabilities(&$data,$db)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
    $row_count1=$row_count;
	while ($result = @mysql_fetch_array($data))
	{
        //$grids.="<tr class=".$class."><td><input type=checkbox name=auids[] value='".$dd[0]."|".$dd[1]."'></td><td><font class=afontstyle>&nbsp;<a href=javascript:doDedu('".urlencode($dd[3])."')>".$dd[0]."</a></td><td><font class=afontstyle>&nbsp;".$dd[1]."</td><td align=right><font class=afontstyle>&nbsp;".$dd[2]."</font></td></tr>";
    	$grid.="[";
		$grid.="\""."\",";
    	$grid.="\"".trim($result[0])."\",";
    	$grid.="\"".trim($result[1])."\",";
        $grid.="\"".number_format($result[2], 2,".", "")."\",";
    	$grid.="\""."javascript:doDedu('".urlencode($result[3])."')"."\"";
    	$j++;
		$grid.="],\n";
	}

    $j=0;
	//Company contributions
	$qu="select companycon.name,sum(payacc_con.amount),companycon.sno,payacc_con.sno from payacc_con LEFT JOIN companycon ON companycon.sno =payacc_con.conno where  payacc_con.pay='' group by companycon.name order by payacc_con.gppayid";
	$res=mysql_query($qu,$db);
    $row_count = @mysql_num_rows($res);
    $row_count2=$row_count;
	while($dd=mysql_fetch_row($res))
	{
    	$grid.="[";
		$grid.="\""."<input type=checkbox name=auids[] id='".$dd[3]."' value='".$dd[3]."'>\",";
    	$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($dd[0]))),ENT_QUOTES)."\",";
    	$grid.="\""."Contribution"."\",";
        $grid.="\"".number_format($dd[1], 2,".", "")."\",";
    	$grid.="\""."javascript:doCON('".$dd[2]."')"."\"";
    	$j++;
		$grid.="],\n";

	}
	mysql_free_result($res);

    $j=0;
	//Taxes
	$qu="select distinct(payacc_w4.type),SUM(payacc_w4.amount),payacc_w4.w4sno from payacc_w4  where payacc_w4.pay='' group by payacc_w4.type order by payacc_w4.type";
	$res=mysql_query($qu,$db);
	$row_count = @mysql_num_rows($res);
	while($dd=mysql_fetch_row($res))
	{
		$TaxType = _GetTax($dd[0]);
		
    	$grid.="[";
		$grid.="\""."<input type=checkbox name=auids[] id='".$dd[2]."' value='".$dd[2]."'>\",";
    	$grid.="\"".$TaxType."\",";
    	$grid.="\""."Tax"."\",";
        $grid.="\"".number_format($dd[1], 2,".", "")."\",";
    	$grid.="\""."javascript:doTaxes('".$dd[0]."')"."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";

	}
    if(($row_count==0) && !($row_count2==0))
    {
      $str=substr($grid,0,strlen($grid)-2);
      $str.="\n";
      $grid=$str;
    }
    elseif(($row_count==0) && ($row_count2==0) && !($row_count1==0))
    {
      $str=substr($grid,0,strlen($grid)-2);
      $str.="\n";
      $grid=$str;
    }

	mysql_free_result($res);
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

Function _GetTax($val)
{
	if($val=="fwh")
		return "Federal with holding - Employees";
	if($val=="swh")
		return "State with holding - Employees";
	if($val=="sswh")
		return "Social Security with holding - Employees";
	if($val=="mwh")
		return "Medical with holding - Employees";
	if($val=="cfwh")
		return "Federal with holding - Company";
	if($val=="cswh")
		return "State with holding - Company";
	if($val=="csswh")
		return "Social Security with holding - Company";
	if($val=="cmwh")
		return "Medical with holding - Company";
		
	if($val=="aftaw")
		return "Additional Federal Tax Amount Withheld  - Employees";
	if($val=="astaw")
		return "Additional State Tax Amount Withheld  - Employees";
	if($val=="localw1_amt")
		return "Local Withholding 1 - Employees";
	if($val=="localw2_amt")
		return "Local Withholding 2 - Employees";
	if($val=="caftaw")
		return "Additional Federal Tax Amount Withheld - Company";
	if($val=="castaw")
		return "Additional State Tax Amount Withheld - Company";
	if($val=="clocalw1_amt")
		return "Local Withholding 1 - Company";
	if($val=="clocalw2_amt")
		return "Local Withholding 2 - Company";
}

//Accounts -> Account Payable -> Tax Link
Function WorkEmployeeTax(&$data,$db)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);


	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	   {
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	   }
	$grid.="];\n";


	$grid.="var actdata = [\n";
	$result =mysql_fetch_row($data);
	//$tot1=0;
	$tot1=$result[5]+$result[6]+$result[7]+$result[8]+$result[9]+$result[10]+$result[11]+$result[12];

    $grid.="[";
	$grid.="\""."\",";
	$grid.="\""."Federal with holding - Employees"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	 $grid.="\"".number_format(round($result[5]), 2,".", "")."\",";
    $grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."State with holding - Employees"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
    $grid.="\"".number_format(round($result[6]), 2,".", "")."\",";
    $grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."Social Security with holding - Employees"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	$grid.="\"".number_format(round($result[7]), 2,".", "")."\",";
    $grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."Medical with holding - Employees"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	$grid.="\"".number_format(round($result[8]), 2,".", "")."\",";
	$grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."Federal with holding - Company"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	$grid.="\"".number_format(round($result[9]), 2,".", "")."\",";
	$grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."State with holding - Company"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	$grid.="\"".number_format(round($result[10]), 2,".", "")."\",";
    $grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."Social Security with holding - Company"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	$grid.="\"".number_format(round($result[11]), 2,".", "")."\",";
	$grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."Medical with holding - Company"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	$grid.="\"".number_format(round($result[12]), 2,".", "")."\",";
	$grid.="],\n";
	$grid.="[";
	$grid.="\""."\",";
	$grid.="\""."Total Tax Amount:"."\",";
	$grid.="\""."&nbsp;Tax"."\",";
	$grid.="\"".number_format(round($tot1),2,".", "")."\",";
	$grid.="\"".$tot1."\"";
	$grid.="]\n";

	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
//Employees -> Pay Liabilities
Function _GetTaxLiabilities(&$data,$db)
{
   $grid="<"."script".">\n";
   $row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);


	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	   {
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	   }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{

    $query="select emp_list.name,CONCAT(payperiod.fromdate,'-',payperiod.todate),'',payacc_w4.amount,payacc_w4.w4sno from payacc_w4  LEFT JOIN net_pay ON payacc_w4.netpayid=net_pay.sno LEFT JOIN emp_list ON net_pay.username=emp_list.username LEFT JOIN payperiod ON net_pay.pid=payperiod.sno where payacc_w4.type='".$result[0]."' and payacc_w4.netpayid='".$result[1]."' and payacc_w4.pay='' group by payacc_w4.netpayid order by payacc_w4.netpayid";

				$res=mysql_query($query,$db);
				$dat=mysql_fetch_row($res);
				
				$TaxName = _GetTax($result[0]);
				if($TaxName == 'Local Withholding 1 - Employees' || $TaxName == 'Local Withholding 2 - Employees' || $TaxName == 'Local Withholding 1 - Company' || $TaxName == 'Local Withholding 2 - Company')
				{
					$TaxType = substr($result[0],0,strpos($result[0],"_"));
					if($TaxType == 'clocalw1' || $TaxType == 'clocalw2')
						$TaxType = substr($TaxType,1,strlen($TaxType)-1);
					$getTaxName = "SELECT $TaxType FROM net_w4 WHERE sno='".$result[2]."'";
					$resTaxName = mysql_query($getTaxName,$db);
					$rowTaxName = mysql_fetch_row($resTaxName);
					$TaxName = $rowTaxName[0];
				}
                $grid.="[";
                $grid.="\""."\",";
                $grid.="\"".$dat[0]."\",";
                $grid.="\"".$dat[1]."\",";
                $grid.="\"".number_format(round($dat[3],2), 2,".", "")."\",";
                $grid.="\"".$TaxName."\"";
    	        $j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

//For Payliabilities->Contribution page

Function _GetContrLiabilities($mydata,$db)
{

    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($mydata);
    $column_count = @mysql_num_fields($mydata);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $grid.="var actdata = [\n";

  	while($dd=mysql_fetch_array($mydata))
			{
                $gpquery="select grosspay.earnings,grosspay.ytdearn,payperiod.contribution,grosspay.sno from grosspay LEFT JOIN net_pay ON net_pay.gid=grosspay.sno LEFT JOIN payperiod ON payperiod.sno=grosspay.pid where grosspay.sno='".$dd[1]."'";
				$gpres=mysql_query($gpquery,$db);
				$gpdata=mysql_fetch_row($gpres);

				$totcon=0;
				$compcon=0;
				$tearning=0;
				$tcon=0;
				$query="select emp_list.name,CONCAT(payperiod.fromdate,' - ',payperiod.todate),companycon.per,payacc_con.amount,net_pay.sno,companycon.amount from payacc_con LEFT JOIN companycon ON payacc_con.conno=companycon.sno LEFT JOIN net_pay ON net_pay.gid=payacc_con.gppayid LEFT JOIN emp_list ON emp_list.username=net_pay.username LEFT JOIN payperiod ON net_pay.pid=payperiod.sno where payacc_con.sno='".$dd[2]."'";

				$res=mysql_query($query,$db);
				$data=mysql_fetch_row($res);

				$conper=0;
				$comtotal=0;
				if($gpdata[1]>$data[5])
				{
					$totcon=$totcon;
					$comtotal=0;
					$conper=0;
				}
				else if(($gpdata[1]+$gpdata[0])>$data[5])
				{
					$comtotal=round(($data[5]-$gpdata[1]),2);
					$compcon=($data[5]-$gpdata[1])*($data[2]/100);
					$totcon=round($compcon,2);
					$conper=$data[2];
				}
				else
				{
                    $comtotal=round($gpdata[0],2);
					$compcon=($gpdata[0])*($data[2]/100);
					$totcon=round($compcon,2);
					$conper=$data[2];
				}
                $newdataamt=0.00;
                $newdataamt=$data[3];

        $grid.="[";
        $grid.="\""."\",";
        $grid.="\"".$data[0]."\",";
        $grid.="\"".trim($data[1])."\",";
        $grid.="\"".number_format($comtotal,2)."\",";
        $grid.="\"".number_format($conper,2)."\",";
        $grid.="\"".number_format($newdataamt,2)."\",";
        $grid.="\""."\"";

        $j++;
        if($j==$row_count)
            $grid.="]\n";
        else
            $grid.="],\n";
			}
     $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}

//Employees -> Pay Liabilities
Function _GetDedLiabilities($data,$db)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $grid.="var actdata = [\n";
    while ($result = @mysql_fetch_array($data))
    {
        $query="select emp_list.name,CONCAT(payperiod.fromdate,'-',payperiod.todate),net_deduct.compcon,payacc_ded.amount,net_pay.sno from net_deduct LEFT JOIN emp_list ON emp_list.username=net_deduct.username LEFT JOIN payacc_ded ON payacc_ded.dedsno=net_deduct.sno LEFT JOIN net_pay ON net_pay.sno=payacc_ded.netpayid LEFT JOIN payperiod ON net_pay.pid=payperiod.sno where net_deduct.sno='".$result[0]."'";
        $res=mysql_query($query,$db);
        $dat=mysql_fetch_row($res);

        $grid.="[";
        $grid.="\""."\",";
        $grid.="\"".$dat[0]."\",";
        $grid.="\"".trim($dat[1])."\",";
        $grid.="\"".number_format(round((((100-$dat[2])/100)*$dat[3]),2), 2,".", "")."\",";
        $grid.="\"".number_format(round(((($dat[2])/100)*$dat[3]),2), 2,".", "")."\",";
        $grid.="\"".number_format($dat[3], 2,".", "")."\",";
        $grid.="\"".$dat[3]."\"";

        $j++;
        if($j==$row_count)
            $grid.="]\n";
        else
            $grid.="],\n";
    }
    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}

Function DisplaySupliers(&$data,$db)
{
	$grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);
	$decimalPref    = getDecimalPreference(); 

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
	$grid.="var actdata = [\n";
	$statusJ="NO";
	
	//To get the general vendors	
			
	$vendortype = "GeneralVendors";
	$VenType = "General Vendor";	
	if(PAYROLL_PROCESS_BY =='QB' /*&& PAYROLL_EMP == 'Y'*/)
	{
		while ($result = @mysql_fetch_array($data))
		{
			if($j==0)
				$grid.="[";
			else
				$grid.="\n,[";
				
			$vendortype = "GeneralVendors";
			$VenType = "General Vendor";
	
			$grid.="\""."\",";
			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result[0]))),ENT_QUOTES)."\",";
			$grid.="\"".getVendorId($result[2],'GV')."\",";
			$grid.="\"".$VenType."\",";
			$grid.="\"".$result[3]."\",";
			$grid.="\"".$result[4]."\",";
			$grid.="\"".$result[5]."\",";		
			$grid.="\"javascript:doEdit('".$result[2]."')\"";
			
			$j++;
			$grid.="]\n";
			$statusJ="YES";
		}
	}
	elseif(PAYROLL_PROCESS_BY !='QB')
	{		
		while ($result = @mysql_fetch_array($data))
		{
			if($j==0)
				$grid.="[";
			else
				$grid.="\n,[";
				
			$vendortype = "GeneralVendors";
			$VenType = "General Vendor";
			
			$val1  = _GetSuppliersAccountsPayable1($result[1],$db,'GeneralVendors');
			if( number_format($val1,2,'.','') == "-0.00" ) {
				$val1 = "0.00";
			}
			$val2  = _GetSuppliersPDUE1($result[1],$db,$vendortype,1,30);
			if( number_format($val2,2,'.','') == "-0.00" ) {
				$val2 = "0.00";
			}
			$val3  = _GetSuppliersPDUE1($result[1],$db,$vendortype,31,60);
			if( number_format($val3,2,'.','') == "-0.00" ) {
				$val3 = "0.00";
			}
			$val4  = _GetSuppliersPDUE1($result[1],$db,$vendortype,61,90);
			if( number_format($val4,$decimalPref,'.','') == "-0.00" ) {
				$val4 = "0.00";
			}
					
			$val5  = _GetSuppliersPDUE1($result[1],$db,$vendortype,0,90);
			if( number_format($val5,2,'.','') == "-0.00" ) {
				$val5 = "0.00";
			}
			
			$val_tot = $val1 + $val2 + $val3+ $val4 + $val5;
			
			$grid.="\""."\",";
			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result[0]))),ENT_QUOTES)."\",";
			$grid.="\"".getVendorId($result[2],'GV')."\",";
			$grid.="\"".$VenType."\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result[1]."','0','0','gv')>".number_format($val1,2,'.','')."</a>\",";		
			$grid.="\""."<a href=javascript:doACPVDE('".$result[1]."','1','30','gv')>".number_format($val2,2,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result[1]."','31','60','gv')>".number_format($val3,2,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result[1]."','61','90','gv')>".number_format($val4,2,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result[1]."','0','90','gv')>".number_format($val5,2,'.','')."</a>\",";		
			$grid.="\""."<a href=javascript:doACPVDE('".$result[1]."','-1','-1','gv')>".number_format($val_tot,2,'.','')."</a>\",";		
			//$grid.="\"".number_format($val_tot,2,'.','')."\",";	
			$grid.="\"javascript:doEdit('".$result[2]."')\"";
			$j++;
			$grid.="]\n";
			$statusJ="YES";
	
		}		

	}
		
	
	//To get the Consulting Vendors
    $query1="SELECT staffacc_cinfo.cname, staffacc_cinfo.username,staffacc_cinfo.phone, staffacc_cinfo.city, staffacc_cinfo.state,staffacc_cinfo.sno FROM staffacc_cinfo,staffacc_list,vendors WHERE staffacc_list.username = staffacc_cinfo.username AND vendors.vendorid=staffacc_cinfo.sno AND staffacc_cinfo.type IN ('CV','BOTH') group by vendors.vendorid";
	$data1=mysql_query($query1,$db);
    while ($result1 = @mysql_fetch_array($data1))
	{
        $vendortype = "ConsultingVendors";
		$VenType = "Consulting Vendor";	

        if($statusJ!="YES")
            $grid.="[";
        else
            $grid.="\n,[";

		if(PAYROLL_PROCESS_BY == 'QB')
		{
			$grid.="\""."\",";

			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result1[0]))),ENT_QUOTES)."\",";
			$grid.="\"".getVendorId($result1[5],'CV')."\",";
			$grid.="\"".$VenType."\",";
			$grid.="\"".$result1[2]."\",";
			$grid.="\"".$result1[3]."\",";
			$grid.="\"".$result1[4]."\",";		
			$grid.="\"javascript:docvEdit('".$result1[1]."')\"";
	
		}	
		else
		{
		    $val_tot  = _GetSuppliersAccountsPayableTtl($result1[1],$db,'ConsultingVendors');
			if( number_format($val_tot,$decimalPref,'.','') == "-0.00" ) {
				$val_tot = "0.00";
			}
			
			$val1  = _GetSuppliersAccountsPayable1($result1[1],$db,$vendortype);
			if( number_format($val1,$decimalPref,'.','') == "-0.00" ) {
				$val1 = "0.00";
			}
			$val12 = _GetSuppliersPDUE1($result1[1],$db,$vendortype,1,30);
			if( number_format($val12,$decimalPref,'.','') == "-0.00" ) {
				$val12 = "0.00";
			}
			$val13 = _GetSuppliersPDUE1($result1[1],$db,$vendortype,31,60);
			if( number_format($val13,$decimalPref,'.','') == "-0.00" ) {
				$val13 = "0.00";
			}
			$val14 = _GetSuppliersPDUE1($result1[1],$db,$vendortype,61,90);
			if( number_format($val14,$decimalPref,'.','') == "-0.00" ) {
				$val14 = "0.00";
			}
			$val5  = _GetSuppliersPDUE1($result1[1],$db,$vendortype,0,90);
			if( number_format($val5,$decimalPref,'.','') == "-0.00" ) {
				$val5 = "0.00";
			}
	
			$grid.="\""."\",";
			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result1[0]))),ENT_QUOTES)."\",";
			$grid.="\"".getVendorId($result1[5],'CV')."\",";
			$grid.="\"".$VenType."\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result1[1]."','0','0','cv')>".number_format($val1,$decimalPref,'.','')."</a>\",";		
			$grid.="\""."<a href=javascript:doACPVDE('".$result1[1]."','1','30','cv')>".number_format($val12,$decimalPref,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result1[1]."','31','60','cv')>".number_format($val13,$decimalPref,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result1[1]."','61','90','cv')>".number_format($val14,$decimalPref,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result1[1]."','0','90','cv')>".number_format($val5,$decimalPref,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result1[1]."','-1','-1','cv')>".number_format($val_tot,$decimalPref,'.','')."</a>\",";		
			$grid.="\"javascript:docvEdit('".$result1[1]."')\"";	
		}	
			$grid.="]\n";
			$statusJ="YES";
    }
   	//To get the consultants
   	$query2="select emp_list.username,emp_list.name,hrcon_general.wphone,hrcon_general.city,hrcon_general.state,emp_list.sno FROM emp_list LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username  LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username where hrcon_w4.tax='1099' and hrcon_w4.ustatus='active' AND emp_list.lstatus NOT IN ('DA','INACTIVE') AND emp_list.empterminated != 'Y' AND hrcon_general.ustatus='active' group by emp_list.username order by emp_list.name";
    $data2=mysql_query($query2,$db);
	while($result2 = @mysql_fetch_array($data2))
    {
        $vendortype = "Consultant";
		$VenType = "Consultant";
		
        if($statusJ!="YES")
            $grid.="[";
        else
            $grid.="\n,[";

        if(PAYROLL_PROCESS_BY=='QB')
		{	
			$grid.="\""."\",";
			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result2[1]))),ENT_QUOTES)."\",";
			$grid.="\"".getVendorId($result2[5],'CT')."\",";
			$grid.="\"".$VenType."\",";
			$grid.="\"".$result2[2]."\",";
			$grid.="\"".$result2[3]."\",";
			$grid.="\"".$result2[4]."\",";
			$grid.="\"javascript:doConEdit('".$result2[5]."')\"";
		}
		else
		{
			$val_tot  = _GetSuppliersAccountsPayableTtl($result2[0],$db,'Consultant');
			if( number_format($val_tot,2,'.','') == "-0.00" ) {
			$val_tot = "0.00";
			}
			
			$val1 = _GetSuppliersAccountsPayable1($result2[0],$db,$vendortype);
			if( number_format($val1,2,'.','') == "-0.00" ) {
			$val1 = "0.00";
			}
			$val12 = _GetSuppliersPDUE1($result2[0],$db,$vendortype,1,30);
			if( number_format($val12,2,'.','') == "-0.00" ) {
			$val12 = "0.00";
			}
			$val13 = _GetSuppliersPDUE1($result2[0],$db,$vendortype,31,60);
			if( number_format($val13,2,'.','') == "-0.00" ) {
			$val13 = "0.00";
			}
			$val14 = _GetSuppliersPDUE1($result2[0],$db,$vendortype,61,90);
			if( number_format($val14,2,'.','') == "-0.00" ) {
			$val14 = "0.00";
			}
			$val5  = _GetSuppliersPDUE1($result2[0],$db,$vendortype,0,90);
			if( number_format($val5,2,'.','') == "-0.00" ) {
			$val5 = "0.00";
			}

			
			$grid.="\""."\",";
			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result2[1]))),ENT_QUOTES)."\",";
			$grid.="\"".getVendorId($result2[5],'CT')."\",";
			$grid.="\"".$VenType."\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result2[0]."','0','0','con')>".number_format($val1,2,'.','')."</a>\",";		
			$grid.="\""."<a href=javascript:doACPVDE('".$result2[0]."','1','30','con')>".number_format($val12,2,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result2[0]."','31','60','con')>".number_format($val13,2,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result2[0]."','61','90','con')>".number_format($val14,2,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result2[0]."','0','90','con')>".number_format($val5,2,'.','')."</a>\",";
			$grid.="\""."<a href=javascript:doACPVDE('".$result2[0]."','-1','-1','con')>".number_format($val_tot,2,'.','')."</a>\",";	
			// 	$grid.="\"javascript:doACPVDEE('".$result2[0]."','0','0')\"";
			$grid.="\"javascript:doConEdit('".$result2[5]."')\"";
		}	
       
		$grid.="]\n";
        $statusJ="YES";
    }
    
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}


Function _GetSuppliersAccountsPayableTtl($usern,$db,$vendortype)
{
	 if($vendortype=="ConsultingVendors")
    {
        $bill_table='cvbill';
		$src_ventype='cbil';
		$billType = 'BillCPMT';
		$decimalPref= getDecimalPreference();
    }
    else if($vendortype=="GeneralVendors")
    {
        $bill_table='bill';
		$src_ventype='bil';		
		$billType = 'BillPMT';
		$decimalPref=2;
    }
    else
    {
        $bill_table='convbill';
		$src_ventype='conbill';	
		$billType = 'BILLCONPM';
		$decimalPref=2;
    }	
	
	$bal_qry_tot="SELECT sum( ".$bill_table.".total ) FROM ".$bill_table." WHERE ".$bill_table.".client_id = '".$usern."' and ".$bill_table.".status='ER'";
	$bal_rslt_set=mysql_query($bal_qry_tot,$db);
	$bal_tot=mysql_fetch_row($bal_rslt_set);
	$bal_qry_tot1="SELECT IF(ISNULL(sum( bank_trans.amount )),0,sum( bank_trans.amount )) FROM bank_trans WHERE  bank_trans.payee = '".$usern."' AND type = '".$billType."' ";
	$bal_rslt_set1=mysql_query($bal_qry_tot1,$db);
	$bal_tot1=mysql_fetch_row($bal_rslt_set1);
	$totbal=$bal_tot[0]-$bal_tot1[0];
	//$to_bal_rslt=$totbal?$totbal:$bal_tot[0];
	return number_format($totbal, $decimalPref,'.', '');
}

Function _GetSuppliersAccountsPayable1($usern,$db,$vendortype)
{
    if($vendortype=="ConsultingVendors")
    {
        $bill_table='cvbill';
		$src_ventype='cbil';
		$decimalPref= getDecimalPreference();
    }
    else if($vendortype=="GeneralVendors")
    {
        $bill_table='bill';
		$src_ventype='bil';
		$decimalPref=2;
    }
    else
    {
        $bill_table='convbill';
		$src_ventype='conbill';
		$decimalPref=2;
    }	
	 		
	$bal_qry_tot="SELECT sum( ".$bill_table.".total )-sum(IFNULL(transamount.Trans_Amount,0.00)) FROM ".$bill_table." LEFT JOIN (SELECT round(SUM(IFNULL(credit_memo_trans.used_amount,0.00)),".$decimalPref.") Trans_Amount,credit_memo_trans.inv_bill_sno  AS billSno FROM credit_memo_trans WHERE credit_memo_trans.type = '".$src_ventype."' GROUP BY credit_memo_trans.inv_bill_sno) transamount ON (transamount.billSno = ".$bill_table.".sno)  WHERE ".$bill_table.".client_id = '".$usern."' and ".$bill_table.".status='ER' and ".$bill_table.".due_date > CURDATE() ";
	$bal_rslt_set=mysql_query($bal_qry_tot,$db);
	$bal_tot=mysql_fetch_row($bal_rslt_set);
							
	$bal_qry_tot1="SELECT (round(SUM(bank_trans.amount),".$decimalPref.") - IFNULL(transamount.Trans_Amount,0.00))  FROM bank_trans ,".$bill_table." LEFT JOIN (SELECT round(SUM(IFNULL(credit_memo_trans.used_amount,'0.00')),".$decimalPref.") Trans_Amount,credit_memo.credit_inv_bill_id billid  FROM credit_memo_trans, credit_memo, ".$bill_table."	
							where credit_memo_trans.inv_bill_sno = ".$bill_table.".sno AND ".$bill_table.".status = 'ER' AND credit_memo.credit_id=credit_memo_trans.credit_memo_sno
							AND credit_memo.credit_type = '".$src_ventype."' GROUP BY credit_memo_trans.credit_memo_sno) transamount ON (transamount.billid=".$bill_table.".sno) WHERE  bank_trans.payee = '".$usern."' AND bank_trans.source = CONCAT('".$src_ventype."',".$bill_table.".sno) and ".$bill_table.".status IN('ER', 'DL') and ".$bill_table.".due_date > CURDATE()";
	$bal_rslt_set1=mysql_query($bal_qry_tot1,$db);
	$bal_tot1=mysql_fetch_row($bal_rslt_set1);
	$totbal=$bal_tot[0]-$bal_tot1[0];
	//$to_bal_rslt=$totbal?$totbal:$bal_tot[0];
	return number_format($totbal, $decimalPref,'.', '');    
}

Function _GetSuppliersPDUE1($usern,$db,$vendortype,$st,$en)
{
	if($vendortype=="ConsultingVendors")
    {
        $bill_table='cvbill';
		$src_ventype='cbil';
		$decimalPref= getDecimalPreference();
    }
    else if($vendortype=="GeneralVendors")
    {
        $bill_table='bill';
		$src_ventype='bil';	
		$decimalPref = 2;
    }
    else
    {
        $bill_table='convbill';
		$src_ventype='conbill';
		$decimalPref = 2;
    }	
	
	if($en==30){
		$due_date_qry = " $bill_table.due_date BETWEEN DATE_SUB(CURDATE(),INTERVAL 30 DAY) AND CURDATE() ";
	}
	else if($en==60){
		$due_date_qry = " $bill_table.due_date BETWEEN DATE_SUB(CURDATE(),INTERVAL 60 DAY) AND DATE_SUB(CURDATE(),INTERVAL 31 DAY)  ";
	}
	else if($en==90){
		if($st==0){
			$due_date_qry = " $bill_table.due_date <= DATE_SUB(CURDATE(),INTERVAL 91 DAY) ";
		}
		else{
			$due_date_qry = " $bill_table.due_date BETWEEN DATE_SUB(CURDATE(),INTERVAL 90 DAY) AND DATE_SUB(CURDATE(),INTERVAL 61 DAY) ";
		}
	}
	
	$bal_qry_tot="SELECT sum( ".$bill_table.".total )-sum(IFNULL(transamount.Trans_Amount,0.00)) FROM ".$bill_table." LEFT JOIN (SELECT round(SUM(IFNULL(credit_memo_trans.used_amount,0.00)),".$decimalPref.") Trans_Amount,credit_memo_trans.inv_bill_sno  AS billSno FROM credit_memo_trans WHERE credit_memo_trans.type = '".$src_ventype."' GROUP BY credit_memo_trans.inv_bill_sno) transamount ON (transamount.billSno = ".$bill_table.".sno)  WHERE ".$bill_table.".client_id = '".$usern."' and ".$bill_table.".status='ER'  and ".$due_date_qry;
	
	//$bal_qry_tot="SELECT sum( ".$bill_table.".total ) FROM ".$bill_table." WHERE ".$bill_table.".client_id = '".$usern."' and ".$bill_table.".status='ER' and ".$due_date_qry;
	$bal_rslt_set=mysql_query($bal_qry_tot,$db);
	$bal_tot=mysql_fetch_row($bal_rslt_set);
	$bal_qry_tot1="SELECT (round(SUM(bank_trans.amount),".$decimalPref.") - SUM(IFNULL(transamount.Trans_Amount,0.00)))  FROM bank_trans ,".$bill_table." LEFT JOIN (SELECT round(SUM(IFNULL(credit_memo_trans.used_amount,'0.00')),".$decimalPref.") Trans_Amount,credit_memo.credit_inv_bill_id billid  FROM credit_memo_trans, credit_memo, ".$bill_table."	
							where credit_memo_trans.inv_bill_sno = ".$bill_table.".sno AND ".$bill_table.".status = 'ER' AND credit_memo.credit_id=credit_memo_trans.credit_memo_sno
							AND credit_memo.credit_type = '".$src_ventype."' GROUP BY credit_memo_trans.credit_memo_sno) transamount ON (transamount.billid=".$bill_table.".sno) WHERE  bank_trans.payee = '".$usern."' AND bank_trans.source = CONCAT('".$src_ventype."',".$bill_table.".sno) and bank_trans.source in (select concat('".$src_ventype."',".$bill_table.".sno) from ".$bill_table." WHERE ".$bill_table.".client_id = '".$usern."' and ".$bill_table.".status IN('ER','DL') and ".$due_date_qry .")";
							
	//echo "<br>".$bal_qry_towt1="SELECT sum( bank_trans.amount ) FROM bank_trans WHERE  bank_trans.payee = '".$usern."' and bank_trans.source in (select concat('".$src_ventype."',".$bill_table.".sno) from ".$bill_table." WHERE ".$bill_table.".client_id = '".$usern."' and ".$bill_table.".status='ER' and ".$due_date_qry .")";
	 
	$bal_rslt_set1=mysql_query($bal_qry_tot1,$db);
	$bal_tot1=mysql_fetch_row($bal_rslt_set1);
	
	$totbal=$bal_tot[0]-$bal_tot1[0];
	//$to_bal_rslt=$totbal?$totbal:$bal_tot[0];
	return number_format($totbal, $decimalPref,'.', '');
}

Function _GetSuppliersPDUE($usern,$db,$vendortype,$st,$en)
{
    $thisday1=mktime(date("H"),date("i"),date("s"),date("m"),date("d")-$st,date("Y"));
    $indate1=date("Y-m-d",$thisday1);
    $thisday2=mktime(date("H"),date("i"),date("s"),date("m"),date("d")-$en,date("Y"));
    $indate=date("Y-m-d",$thisday2);
    
    if($vendortype=="ConsultingVendors")
    {
        $qu="select IF(acc_reg.type IN('CBILL','Deposit'),acc_reg.amount,0),IF(acc_reg.type IN('Payment','BillCPMT'),acc_reg.amount,0) from acc_reg where acc_reg.cate_id=1 and acc_reg.cename='".$usern."' and acc_reg.status='ER' and acc_reg.rdate>='".$indate."' and acc_reg.rdate<='".$indate1."'";
    }
    else if($vendortype=="GeneralVendors")
    {
        $qu="select IF(acc_reg.type IN('Bill','Deposit'),acc_reg.amount,0),IF(acc_reg.type IN('Payment','BillPMT'),acc_reg.amount,0) from acc_reg where acc_reg.cate_id=1 and acc_reg.cename='".$usern."' and acc_reg.status='ER' and acc_reg.rdate>='".$indate."' and acc_reg.rdate<='".$indate1."'";
    }
    else
    {
        $qu="select IF(acc_reg.type IN('CONBILL','Deposit'),acc_reg.amount,0),IF(acc_reg.type IN('Payment','BillCONPM'),acc_reg.amount,0) from acc_reg where acc_reg.cate_id=1 and acc_reg.cename='".$usern."' and acc_reg.status='ER' and acc_reg.rdate>='".$indate."' and acc_reg.rdate<='".$indate1."'";
    }

    $res=mysql_query($qu,$db);
    $total=0;
    while($dd=mysql_fetch_row($res))
    {
       $total = $total + (number_format($dd[0],2,'.','') - number_format($dd[1],2,'.',''));
    }
    mysql_free_result($res);
    return $total;
}

function displayWorkAccRegDetcon(&$data,$db)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
	$grid.="var actdata = [\n";
	$statusJ="NO";
	while ($result = @mysql_fetch_array($data))
	{
       $url = "";
	    if($j==0)
       		$grid.="[";
		else
			$grid.="\n,[";

		$grid.="\""."\",";

        if($result[2]=="IH")
		{
			$clname=$companyname;
		}
		else
		{
			$sque="select cname from staffacc_cinfo where username='".$result[2]."'";
			$sres=mysql_query($sque,$db);
			$srow=mysql_fetch_row($sres);
			$clname=$srow[0];
		}

        $grid.="\"".$result[1]."\",";

        if($result[3]=="Deposit")
        {
            if($result[5]!="")
            {
                $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);
                $grid.="\"".$dd1[0]."\",";
                $grid.="\""."Deposit"."\",";
            }
            else
            {
                $grid.="\"\",";
                $grid.="\""."Deposit"."\",";
            }

            $grid.="\"".$clname."\",";
            $grid.="\"\",";
            $grid.="\"".$result[4]."\",";
            $grid.="\"\",";
            $tot=$tot+$result[4];

        }
        else if($result[3]=="Bill")
        {
           $qu="select sno,IF(pay!='Yes',".tzRetQueryStringDate('due_date','Date','/').",'PAID') from bill where CONCAT('bil',sno)='".$result[5]."'";
           $re2=mysql_query($qu,$db);
           $dd1=mysql_fetch_row($re2);
           mysql_free_result($re2);

           $grid.="\"".$dd1[0]."\",";
           $grid.="\""."Bill"."\",";
           $grid.="\"".$clname."\",";
           $grid.="\"".$dd1[1]."\",";
           $grid.="\"".$result[4]."\",";
           $grid.="\"\",";

           $tot=$tot+$result[4];
        }
        else if($result[3]=="CBILL")
        {
            $qu="select sno,IF(pay!='Yes',DATE_FORMAT(due_date,'%m/%d/%Y'),'PAID') from cvbill where CONCAT('cbil',sno)='".$result[5]."'";

            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

            $grid.="\"".$dd1[0]."\",";
            $grid.="\""."Bill"."\",";
            $grid.="\"".$clname."\",";

            $paid_cbill="select round(sum(amount),2) from bank_trans where type='BillCPMT' and source='".$result[5]."' and payee='".$result[2]."' group by source";

            $paid_cbill_query=mysql_query($paid_cbill,$db);
            $paid_cbill_row=mysql_fetch_row($paid_cbill_query);

            if($result[4]<=$paid_cbill_row[0])
                $grid.="\""."PAID"."\",";
            else
                $grid.="\"".$result[1]."\",";

            $grid.="\"".$result[4]."\",";
            $grid.="\"\",";

            $tot=$tot+$result[4];
			$url = "showsupdetc.php?acc=ConsultingVendors&billno=".$dd1[0];
        }

        else if($result[3]=="Payment")
        {
            if($result[5]!="")
            {
                $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);
                $grid.="\"".$dd1[0]."\",";
                $grid.="\""."Check Payment"."\",";
            }
            else
            {
                $grid.="\"\",";
                $grid.="\""."Payment"."\",";
            }
            
            $grid.="\"".$clname."\",";
            $grid.="\"\",";
            $grid.="\"".$result[4]."\",";
            $grid.="\"\",";
            
            $tot=$tot-$result[4];
        }
            
        else if($result[3]=="BillCPMT")
		{
		     $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
			 $re2=mysql_query($qu,$db);
			 $dd1=mysql_fetch_row($re2);
			 mysql_free_result($re2);

			 $grid.="\"".$dd1[1]."\",";
			 $grid.="\""."Pay Bill"."\",";//Bill Payment made to Pay Bill so as to Differentiate 'Bill' and 'Bill Payment' in grid search.
			 $grid.="\"".$clname."\",";
			 $grid.="\"\",";
			 $grid.="\"\",";
			 $grid.="\"".$result[4]."\",";
			 
    	     $tot=$tot-$result[4];
			 $url = "editcbillpaym.php?acc=ConsultingVendors&sno=".$result[0];
        }
			
		else if($result[3]=="BillPMT")
		{
            $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

            $grid.="\"".$dd1[1]."\",";
            $grid.="\""."Pay Bill"."\",";//Bill Payment made to Pay Bill so as to Differentiate 'Bill' and 'Bill Payment' in grid search.
            $grid.="\"".$clname."\",";
            $grid.="\"\",";
            $grid.="\"\",";
            $grid.="\"".$result[4]."\",";
            
            $tot=$tot-$result[4];
        }
            
		$grid.="\"".number_format($tot,2,'.','')."\",";
		$grid.="\"".$url."\"";
		
        $j++;
        $grid.="]\n";
        $statusJ="YES";
    }
    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}

Function displayWorkAccRegDetVendor(&$data,$db)
{
			
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $statusJ="NO";
	$grid.="var actdata = [\n";
	
    while($result=mysql_fetch_row($data))
    {
		$url  = "";
        if($j==0)
       		$grid.="[";
		else
			$grid.="\n,[";

        if($result[2]=="IH")
    	{
    		$clname=$companyname;
    	}
    	else if(strpos("*".$result[2],"PE"))
    	{
    		$que="select name from reg_payee where CONCAT('PE',sno)='".$result[2]."'";
    		$res=mysql_query($que,$db);
    		$row=mysql_fetch_row($res);
    		$clname=$row[0];
    	}
    	$grid.="\"\",";
        $grid.="\"".$result[1]."\",";

    	if($result[3]=="Deposit")
    	{
    	   if($result[5]!="")
    	   {
    	      $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
    		  $re2=mysql_query($qu,$db);
    		  $dd1=mysql_fetch_row($re2);
    		  mysql_free_result($re2);
    		  $grid.="\"".$dd1[0]."\",";
    		  $grid.="\""."Deposit"."\",";

    	   }
    	   else
    	   {
             $grid.="\"\",";
             $grid.="\""."Deposit"."\",";
    	   }

           $grid.="\"".$clname."\",";
           $grid.="\"\",";
           $grid.="\"".$result[4]."\",";
    	   $grid.="\"\",";

    	   $tot=$tot+$result[4];
    	}
    	else if($result[3]=="Bill")
    	{

           $qu="select sno,IF(pay!='Yes',DATE_FORMAT(due_date,'%m/%d/%Y'),'PAID') from bill where CONCAT('bil',sno)='".$result[5]."'";
    	   $re2=mysql_query($qu,$db);
    	   $dd1=mysql_fetch_row($re2);
    	   mysql_free_result($re2);

    	   $grid.="\"".$dd1[0]."\",";
    	   $grid.="\""."Bill"."\",";
    	   $grid.="\"".$clname."\",";


    	   $paid_bill="select round(sum(amount),2) from bank_trans where type='BillPMT' and source='".$result[5]."' and payee='".$result[2]."' group by source";

           $paid_bill_query=mysql_query($paid_bill,$db);
    	   $paid_bill_row=mysql_fetch_row($paid_bill_query);

           if($result[4]<=$paid_bill_row[0])
                $grid.="\""."PAID"."\",";
           else
                $grid.="\"".$result[1]."\",";

           $grid.="\"".$result[4]."\",";
           $grid.="\"\",";
 		   $url = "showdetv.php?acc=GeneralVendors&billno=".$dd1[0];
    	   $tot=$tot+$result[4];

    	}
    	else if($result[3]=="CBILL")
    	{
    	   $qu="select sno,IF(pay!='Yes',DATE_FORMAT(due_date,'%m/%d/%Y'),'PAID') from cvbill where CONCAT('cbil',sno)='".$result[5]."'";
    	   $re2=mysql_query($qu,$db);
    	   $dd1=mysql_fetch_row($re2);
    	   mysql_free_result($re2);

    	   $grid.="\"".$dd1[0]."\",";
    	   $grid.="\""."Bill"."\",";
    	   $grid.="\"".$clname."\",";
           $grid.="\"".$dd1[1]."\",";
           $grid.="\"".$result[4]."\",";

    	   $grid.="\"\",";

    	   $tot=$tot+$result[4];
    	}
    	else if($result[3]=="Payment")
    	{
    	   if($result[5]!="")
    	   {
    	      $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
    		  $re2=mysql_query($qu,$db);
    		  $dd1=mysql_fetch_row($re2);
    		  mysql_free_result($re2);
    		  $grid.="\"".$dd1[0]."\",";
    	      $grid.="\""."Check Payment"."\",";

    	   }
    	   else
    	   {
                $grid.="\"\",";
                $grid.="\""."Payment"."\",";
       	   }
       	   $grid.="\"".$clname."\",";
    	   $grid.="\"\",";
    	   $grid.="\"".$result[4]."\",";
    	   $grid.="\"\",";

    	   $tot=$tot-$result[4];
    	}
    	else if($result[3]=="BillCPMT")
    	{
    	     $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
    		 $re2=mysql_query($qu,$db);
    		 $dd1=mysql_fetch_row($re2);
    		 mysql_free_result($re2);

    		 $grid.="\"".$dd1[1]."\",";
    		 $grid.="\""."Pay Bill"."\",";//Bill Payment made to Pay Bill so as to Differentiate 'Bill' and 'Bill Payment' in grid search.
    		 $grid.="\"".$clname."\",";
    	     $grid.="\"\",";
    	     $grid.="\"\",";
    	     $grid.="\"".$result[4]."\",";

    	     $tot=$tot-$result[4];
    	}
    	else if($result[3]=="BillPMT")
    	{
    	      $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
    		  $re2=mysql_query($qu,$db);
    		  $dd1=mysql_fetch_row($re2);
    		  mysql_free_result($re2);

    		  $grid.="\"".$dd1[1]."\",";
    		  $grid.="\""."Pay Bill"."\",";//Bill Payment made to Pay Bill so as to Differentiate 'Bill' and 'Bill Payment' in grid search.
    		  $grid.="\"".$clname."\",";
              $grid.="\"\",";
    	      $grid.="\"\",";
    	      $grid.="\"".$result[4]."\",";

    	     $tot=$tot-$result[4];
			 $url ="editbillpaym.php?acc=GeneralVendors&sno=".$result[0];
    	}
    	$grid.="\"".number_format($tot,2,'.','')."\",";
		$grid.="\"".$url."\",";
        $j++;
        $grid.="]\n";
        $statusJ="YES";
     }

    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}

Function displayWorkAccRegDetConsultants(&$data,$db)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $statusJ="NO";
    $grid.="var actdata = [\n";
    while($result=mysql_fetch_row($data))
    {
	$url = "";
        if($j==0)
            $grid.="[";
        else
            $grid.="\n,[";

    	if($result[2]=="IH")
    	{
    		$clname=$companyname;
    	}
    	else
    	{
    	    	$sque="select name from emp_list where username='".$result[2]."'";
    			$sres=mysql_query($sque,$db);
    			$srow=mysql_fetch_row($sres);
    			$clname=$srow[0];
    	}

        $grid.="\"\",";
        $grid.="\"".$result[1]."\",";

        if($result[3]=="Deposit")
    	{
    	   if($result[5]!="")
    	   {
                $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);

                $grid.="\"".$dd1[0]."\",";
                $grid.="\""."Deposit"."\",";
           }
    	   else
    	   {
               $grid.="\"\",";
    	       $grid.="\""."Deposit"."\",";
    	   }

    	   $grid.="\"".$clname."\",";
    	   $grid.="\"\",";
    	   $grid.="\"".$result[4]."\",";
    	   $grid.="\"\",";
    	   $tot=$tot+$result[4];

    	}
    	else if($result[3]=="CONBILL")
    	{
    	   $qu="select sno,IF(pay!='Yes',DATE_FORMAT(due_date,'%m/%d/%Y'),'PAID') from convbill where CONCAT('conbill',sno)='".$result[5]."'";
    	   $re2=mysql_query($qu,$db);
    	   $dd1=mysql_fetch_row($re2);
    	   mysql_free_result($re2);

    	   $grid.="\"".$dd1[0]."\",";
    	   $grid.="\""."Bill"."\",";
           $grid.="\"".$clname."\",";

    	   $paid_conbill="select round(sum(amount),2) from bank_trans where type='BILLCONPM' and source='".$result[5]."' and payee='".$result[2]."' group by source";

           $paid_conbill_query=mysql_query($paid_conbill,$db);
    	   $paid_conbill_row=mysql_fetch_row($paid_conbill_query);

           if($result[4]<=$paid_conbill_row[0])
                $grid.="\""."PAID"."\",";
           else
                $grid.="\"".$result[1]."\",";

    	   $grid.="\"".$result[4]."\",";
    	   $grid.="\"\",";
           $tot=$tot+$result[4];
		   $url = "showsupdetcon.php?acc=Consultants&billno=".$dd1[0];

    	}
    	else if($result[3]=="Payment")
    	{
    	   if($result[5]!="")
    	   {
    	      $qu="select checknumber from bank_trans where CONCAT('Dep',sno)='".$result[5]."'";
    		  $re2=mysql_query($qu,$db);
    		  $dd1=mysql_fetch_row($re2);
    		  mysql_free_result($re2);

    		  $grid.="\"".$dd1[0]."\",";
    		  $grid.="\""."Check Payment"."\",";

    	   }
    	   else
    	   {
                $grid.="\"\",";
                $grid.="\""."Payment"."\",";
    	   }
    	   $grid.="\"".$clname."\",";
    	   $grid.="\"\",";
    	   $grid.="\"".$result[4]."\",";
    	   $grid.="\"\",";

    	   $tot=$tot-$result[4];

    	}
    	else if($result[3]=="BILLCONPM")
    	{
    	     $qu="select reg_category.name,bank_trans.checknumber from bank_trans LEFT JOIN reg_category ON reg_category.sno=bank_trans.bankid where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
    		 $re2=mysql_query($qu,$db);
    		 $dd1=mysql_fetch_row($re2);
    		 mysql_free_result($re2);

    		 $grid.="\"".$dd1[1]."\",";
    		 $grid.="\""."Pay Bill"."\",";//Bill Payment made to Pay Bill so as to Differentiate 'Bill' and 'Bill Payment' in grid search.
    	     $grid.="\"".$clname."\",";
    	     $grid.="\"\",";
    		 $grid.="\"\",";
    		 $grid.="\"".$result[4]."\",";
    	     $tot=$tot-$result[4];
	   	     $url = "editconbillpaym.php?acc=Consultants&sno=".$result[0];

    	}

        $grid.="\"".number_format($tot,2,'.','')."\",";
		$grid.="\"".$url."\",";
        $j++;
        $grid.="]\n";
        $statusJ="YES";
    }

    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}

Function displaySuppliersDet(&$data,$db,$venTypeHid,$viewFrmAll)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);
	$decimalPref    = getDecimalPreference();//sathvika-to add demcimal preference
	
    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $statusJ="NO";
	$grid.="var actdata = [\n";
	if($venTypeHid=="cv")
	{
		$attch_wrd='cbil';
		$bill_page='showsupdetc.php';
		$venAccType = "conven";
		$bill_table='cvbill';
	}
	else if($venTypeHid=="gv")
	{
		$attch_wrd='bil';	
		$bill_page='showdetv.php';
		$venAccType = "genven";
		$bill_table='bill';
	}
	else
	{
		$attch_wrd='conbill';
		$bill_page='showsupdetcon.php';
		$venAccType = "consultant";
		$bill_table='convbill';
	}	
	$cmltv_diff=0;
    while($result=mysql_fetch_row($data))
    {		
		if($result[6] == 'ER'){
			$url  = "";
				if($j==0)
					$grid.="[";
				else
					$grid.="\n,[";
			
			if($tempValue <= 0)
				$tempValue = 'PAID';
	
			$grid.="\"\",";
			$grid.="\"".$result[1]."\",";
			$grid.="\""."Bill"."\",";//String Bill Payment changed to Pay Bill Differentiate 'Bill' and 'Bill Payment' in  grid  search.
			$grid.="\"".$result[2]."\",";
			$grid.="\"".$result[3]."\",";
			$amnt_pay=number_format($result[4],$decimalPref,'.','');
			$grid.="\"".$amnt_pay."\",";
			$grid.="\"\",";
			if($result[5] != 0)
					$appliedCredits = number_format($result[5],$decimalPref,'.','');
				else
					$appliedCredits = "";
				$grid.="\"".$appliedCredits."\",";
			$cmltv_diff = $cmltv_diff + $result[4] - $appliedCredits;
			
			$getTotPMT = "select sum(amount) from bank_trans where source= '".$attch_wrd.$result[2]."'";
			$resTotPMT = mysql_query($getTotPMT,$db);
			$rowTotPMT = mysql_fetch_array($resTotPMT);
			
			$tempValue = number_format(($result[4] - $appliedCredits - $rowTotPMT[0]),$decimalPref,'.','');
			if($tempValue <= 0)
				$tempValue = "PAID";
			
			$grid.="\"".$tempValue."\",";
			$grid.="\"".number_format($cmltv_diff,$decimalPref,'.','')."\",";		
			$tot=$tot-$result[4];
			$url = $bill_page."?acc=".$venAccType."&billno=".$result[2]."&viewFrmAll=".$viewFrmAll;		
			
			$grid.="\"".number_format($tot,$decimalPref,'.','')."\",";
			$grid.="\"".$url."\",";
			$j++;
			$grid.="]\n";
			$statusJ="YES";
		}
		$bank_trans_qry="select dpdate,amount,sno,(select acc_reg.rec_pay_id  from acc_reg where concat('Dep',bank_trans.sno) =acc_reg.source ),checknumber from bank_trans where source= '".$attch_wrd.$result[2]."'";
		$data1=mysql_query($bank_trans_qry,$db);
		
		$getUsedCredit = "SELECT round(SUM(IFNULL(credit_memo_trans.used_amount,'0.00')),2) Trans_Amount,credit_memo.credit_inv_bill_id billid  FROM credit_memo_trans, credit_memo,".$bill_table." where credit_memo_trans.inv_bill_sno = ".$bill_table.".sno AND ".$bill_table.".status IN('ER','DL') AND credit_memo.credit_id=credit_memo_trans.credit_memo_sno AND credit_memo.credit_type = '".$attch_wrd."' AND credit_memo.credit_inv_bill_id = '".$result[2]."' GROUP BY credit_memo_trans.credit_memo_sno";
		$resUsedCredit = mysql_query($getUsedCredit,$db);
		$rowUsedCredit = mysql_fetch_array($resUsedCredit);
		
		if($rowUsedCredit[0] == 0.00)
			$usedAmmount1 = "";
		else
			$usedAmmount1 = $rowUsedCredit[0];
		
		$countbank_trans_qry = mysql_num_rows($data1);
		$linesNumers = 0;
		while($result1=mysql_fetch_row($data1))
    	{
			$linesNumers++;
			if($countbank_trans_qry == $linesNumers)
				$usedAmmount = $usedAmmount1;
			else
				$usedAmmount = "";
			
			if($j==0)
                $grid.="[";
            else
                $grid.="\n,[";
					
			$grid.="\"\",";
			$grid.="\"".$result[1]."\",";
			$grid.="\""."Pay Bill"."\",";//String Bill Payment changed to Pay Bill Differentiate 'Bill' and 'Bill Payment' in  grid  search.
			$grid.="\"".$result1[4]."\",";
			$grid.="\"\",";
			$grid.="\"\",";
			$amnt_pay1=number_format($result1[1],$decimalPref,'.','');
			$grid.="\"".$amnt_pay1."\",";
			$cmltv_diff=$cmltv_diff-$result1[1] + $usedAmmount;			
			$grid.="\"".$usedAmmount."\",";
			$grid.="\"\",";
			$cmltv_diff1=number_format($cmltv_diff,$decimalPref,'.','');
			$grid.="\"".$cmltv_diff1."\",";
			
			
			$tot=$tot-$result[4];
			$url = "editconbillpaym.php?bilNumber=".$result[2]."&ven_type=".strtoupper($venTypeHid)."&sno=".$result1[3]."&FromReg=yes&viewFrmAll=".$viewFrmAll;
			
			$grid.="\"".number_format($tot,$decimalPref,'.','')."\",";
			$grid.="\"".$url."\",";
			$j++;
			$grid.="]\n";
		}
    }
    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}
/// vendor grids

function GetPaymentDet($val,$db,$amt)
{
	$decimalPref= getDecimalPreference();
	 $qu="select ".$amt."-sum(round(amount,".$decimalPref.")),source from bank_trans where source='cbil".$val."' and type='BillCPMT' group by source";	
	$res=mysql_query($qu,$db);
	$dd=mysql_fetch_row($res);
	if($dd[0]=="")
		return $amt;
	else
		return $dd[0];	
}
			
function GetVPayDetails($val,$db,$amt)
{
	$qu="select ".$amt."-sum(round(amount,2)),source from bank_trans where source='bil".$val."' and type='BillPMT' group by source";			
	$res=mysql_query($qu,$db);
	$dd=mysql_fetch_row($res);
	if($dd[0]=="")
		return number_format($amt, 2,'.', '');
	else
		return number_format($dd[0], 2,'.', '');
}

function GetPaymentDet1($val,$db,$amt)
{
	$qu="select ".$amt."-sum(round(amount,2)),source from bank_trans where source='conbill".$val."' and type='BillCONPM' group by source";
	$res=mysql_query($qu,$db);
	$dd=mysql_fetch_row($res);
	if($dd[0]=="")
		return $amt;
	else
		return $dd[0];
}


//  bill history
//Modified by Vijaya on 19/01/2009 to fix the grid columns problem in Accounting/Vendors/Bill History.
function displayBillHistory(&$data,$db)
{
    global $partext;
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count ;$i++)
	{
		if($i==$column_count -1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{

		$balance_amt= GetVPayDetails($result[0],$db,$result[4]);
		//$balance_amt=number_format($result[4],2,'.','');
	
		$creditBalance=number_format(getAppliedCreditAmount($result[0],'bil'),2,'.',''); //Applied credit amount.
		$balance_amt = $balance_amt-$creditBalance;
          if($result[7] == 'Yes')
		    $stat="PAID";
		else
			$stat="UNPAID";
		
		$grid.="[";
		$grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onclick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label>\",";
		
		$partext="";
		$grid.="\"".$result[2]."\",";
		$grid.="\"".$result[9]."\",";
		$grid.="\"".$result[8]."\",";
		$grid.="\"".$result[1]."\","; 
		$grid.="\"".$result[3]." [$stat] \",";  
		$grid.="\"".number_format($result[4],2,".","")."\",";  
		$grid.="\"".number_format($balance_amt,2,".","")."\",";
		$grid.="\"".$result[0]."\"";

		$j++;
		
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function displayBillCHistory(&$data,$db)
{
    global $partext;
	$grid="<"."script".">\n";
	 $row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);
	$decimalPref    = getDecimalPreference();
	
	$grid.="var actcol = [";
	for($i=0;$i<$column_count ;$i++)
	{
		if($i==$column_count -1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
		$balance_amt=GetPaymentDet($result[0],$db,$result[4]);
        //$balance_amt= $result[4];
		$creditBalance = getAppliedCreditAmount($result[0],'cbil'); //Applied credit amount.
		$creditBalance = number_format($creditBalance,2,".","");

        $balance_amt = $balance_amt-$creditBalance;
		if($result[7] == 'Yes')
		    $stat="PAID";
		else
			$stat="UNPAID";
		
		$grid.="[";
		$grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onclick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label>\",";

		$partext="";
		$grid.="\"".$result[2]."\",";
		$grid.="\"".stripslashes(gridcell($result[10]))."\",";
		$grid.="\"".$result[8]."\",";  
		$grid.="\"".$result[1]."\",";  
		$grid.="\"".$result[3]." [$stat] \",";  
		$grid.="\"".number_format($result[4],$decimalPref,".","")."\",";  
		$grid.="\"".number_format($balance_amt,$decimalPref,".","")."\",";
		$grid.="\"".$result[0]."\"";

		$j++;
		
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";		
			
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
//////////////////////////

// bill con history
function displayBillConHistory(&$data,$db)
{
    global $partext;
	$grid="<"."script".">\n";
	 $row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);
	

   if($balance_amt<=0)
	    $stat="PAID";
	else
	$stat="UNPAID";
	
	$grid.="var actcol = [";
	for($i=0;$i<$column_count ;$i++)
	{
		if($i==$column_count -1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{      
		$balance_amt=GetPaymentDet1($result[0],$db,$result[4]);
		//$balance_amt =$result[4];

		$totCreditedAmt = getAppliedCreditAmount($result[0],'conbill'); //Applied credit amount.
		$balance_amt = $balance_amt-$totCreditedAmt;
		if($result[7] == 'Yes')
			$stat="PAID";
		else
			$stat="UNPAID";
		$partext="";
		
		$grid.="[";
		$grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onclick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label>\",";
		$grid.="\"".$result[2]."\",";
		$grid.="\"".gridcell($result[9])."\",";
		$grid.="\"".gridcell($result[8])."\",";
		$grid.="\"".$result[1]."\",";  
		$grid.="\"".$result[3]." [$stat] \",";  
		$grid.="\"".number_format($result[4],2,".","")."\",";  
		$grid.="\"".number_format($balance_amt,2,".","")."\",";
		$grid.="\"".$result[0]."\"";
		
		$j++;
		
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function displayBillPaymentVendor(&$data,$db)
{
	$grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

    $grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
            $grid.="\""."\"";
        else
            $grid.="\""."\",";
    }
    $grid.="];\n";

    $j=0;
    $statusJ="NO";
	$grid.="var actdata = [\n";
	while($result=mysql_fetch_row($data))
	{
		if($j==0)
                $grid.="[";
            else
                $grid.="\n,[";

        $grid.="\"\",";

		if($result[2]=="IH")
		{
			$clname=$companyname;
		}
		else if(strpos("*".$result[2],"PE"))
		{
			$que="select name from reg_payee where CONCAT('PE',sno)='".$result[2]."'";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$clname=$row[0];
		}

        $grid.="\"".$result[1]."\",";
        //$grid.="\""."<a href=editbillpaym.php?acc=vender&sno=$result[0]>".$result[1]."</a>"."\",";

		if($result[3]=="BillPMT")
		{
            $qu="select bank_trans.checknumber,bank_trans.memo from bank_trans where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
            $re2=mysql_query($qu,$db);
            $dd1=mysql_fetch_row($re2);
            mysql_free_result($re2);

            $grid.="\"".$dd1[0]."\",";
            $grid.="\""."Bill Payment"."\",";
            $grid.="\"".$clname."\",";
            $grid.="\"".$dd1[1]."\",";
            $grid.="\"".$result[4]."\",";
  		}
  		$grid.="\""."editbillpaym.php?acc=vender&sno=".$result[0]."\"";
		$j++;
        $grid.="]\n";
        $statusJ="YES";
    }
    $grid.="];\n";
    $grid.="</"."script".">\n";
    return $grid;
}

function displayBillPayment(&$data,$db)
{
        $grid="<"."script".">\n";
        $row_count = @mysql_num_rows($data);
        $column_count = @mysql_num_fields($data);

        $grid.="var actcol = [";
        for($i=0;$i<$column_count;$i++)
        {
            if($i==$column_count-1)
                $grid.="\""."\"";
            else
                $grid.="\""."\",";
        }
        $grid.="];\n";

        $j=0;
        $statusJ="NO";
    	$grid.="var actdata = [\n";
    	while($result=mysql_fetch_row($data))
    	{
    		if($j==0)
                $grid.="[";
            else
                $grid.="\n,[";

            $grid.="\"\",";

    		if($result[2]=="IH")
    		{
    			$clname=$companyname;
    		}
    		else
    		{
    			    $sque="select cname from staffacc_cinfo where username='".$result[2]."'";
    				$sres=mysql_query($sque,$db);
    				$srow=mysql_fetch_row($sres);
        			$clname=$srow[0];
       		}

            //$grid.="\""."<a href=editcbillpaym.php?acc=vender&sno=$result[0]>".$result[1]."</a>"."\",";
            $grid.="\"".$result[1]."\",";
            
            if($result[3]=="BillCPMT")
    		{
                $qu="select bank_trans.checknumber,bank_trans.memo from bank_trans where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);

                $grid.="\"".$dd1[0]."\",";
                $grid.="\""."Bill Payment"."\",";
                $grid.="\"".$clname."\",";
                $grid.="\"".$dd1[1]."\",";
                $grid.="\"".$result[4]."\",";
    		}
    		$grid.="\""."editcbillpaym.php?acc=vender&sno=".$result[0]."\",";
    		$j++;
            $grid.="]\n";
            $statusJ="YES";
        }
        $grid.="];\n";
        $grid.="</"."script".">\n";
        return $grid;
}

function displayBillPaymentFun(&$data,$db,$ven_type,$indate,$duedate,$client)
{
    	$grid="<"."script".">\n";
        $row_count = @mysql_num_rows($data);
        $column_count = @mysql_num_fields($data);

        $grid.="var actcol = [";
        for($i=0;$i<$column_count;$i++)
        {
            if($i==$column_count-1)
                $grid.="\""."\"";
            else
                $grid.="\""."\",";
        }
        $grid.="];\n";

        $j=0;
        $statusJ="NO";
    	$grid.="var actdata = [\n";
		if($ven_type=='CV'){			
			$tab_name='staffacc_cinfo,vendors';
			$col_name="vendors.sno,staffacc_cinfo.cname";
			$cond_name='BillCPMT';
			$where_cond_nm="staffacc_cinfo.sno = vendors.vendorid AND vendors.vendortype = 'CV' AND username";
		}
		else if($ven_type=='GV'){			
			$tab_name='reg_payee,vendors';
			$col_name="vendors.sno,reg_payee.name";
			$cond_name='BillPMT';
			$where_cond_nm="reg_payee.sno = vendors.vendorid AND vendors.vendortype = 'GV' AND CONCAT('PE',reg_payee.sno)";
		}
		else if($ven_type=='CON'){			
			$tab_name='emp_list,vendors';
			$col_name="vendors.sno,emp_list.name";
			$cond_name='BILLCONPM';
			$where_cond_nm="emp_list.sno = vendors.vendorid AND vendors.vendortype = 'CT' AND username";
		}
    	while($result=mysql_fetch_row($data))
    	{
    		if($j==0)
                $grid.="[";
            else
                $grid.="\n,[";

            $grid.="\"\",";

    		if($result[2]=="IH")
    		{
    			$clname=$companyname;
    		}
    		else
    		{
    			$que="select ".$col_name." from ".$tab_name." where ".$where_cond_nm."='".$result[2]."'";
    			$res=mysql_query($que,$db);
    			$row=mysql_fetch_row($res);
    			$clname=$row[1];
				$clid=$row[0];
    		}
			$grid.="\"".stripslashes($clname)."\",";
			$grid.="\"".$clid."\",";
            $grid.="\"".$result[1]."\",";
           // $grid.="\""."<a href=editconbillpaym.php?acc=vender&sno=$result[0]>".$result[1]."</a>"."\",";

    		
/*if($result[3]==$cond_name)
    		{
                $qu="select bank_trans.checknumber,bank_trans.paymethod,(select name from reg_category where sno=bank_trans.bankid) bankname from bank_trans where CONCAT('Dep',bank_trans.sno)='".$result[5]."'";
                $re2=mysql_query($qu,$db);
                $dd1=mysql_fetch_row($re2);
                mysql_free_result($re2);*/
			$grid.="\"".$result[7]."\",";
			$grid.="\"".$result[8]."\",";
			$grid.="\"".$result[6]."\",";
			$grid.="\"".$result[4]."\",";
          	//}

          	$grid.="\""."editconbillpaym.php?acc=vender&ven_type=".$ven_type."&sno=".$result[0]."&sdate=".$indate."&edate=".$duedate."&clientValue=".$client."\",";
    		$j++;
            $grid.="]\n";
            $statusJ="YES";
        }
        $grid.="];\n";
        $grid.="</"."script".">\n";
        return $grid;
}

function DisplayEmpSupliersList3(&$data,$db,$indate,$duedate) // Calculating suppliers payables
{
        $grid="<"."script".">\n";
        $row_count = @mysql_num_rows($data);
        $column_count = @mysql_num_fields($data);

        $grid.="var actcol = [";
        for($i=0;$i<$column_count;$i++)
        {
            if($i==$column_count-1)
                $grid.="\""."\"";
            else
                $grid.="\""."\",";
        }
        $grid.="];\n";

        $j=0;
        $statusJ="NO";
    	$grid.="var actdata = [\n";
        $total=0;
        while($result=mysql_fetch_row($data))
        {
            $creditBalance = number_format(getAppliedCreditAmount($result[0],'bil'),2,'.',''); //Applied credit amount.

            if($j==0)
                $grid.="[";
            else
                $grid.="\n,[";

            $grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onClick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label>\",";
            $grid.="\"".$result[1]."\",";
            //$grid.="\""."<a href=javascript:doACPEV2('".$result[0]."','".$indate."','".$duedate."');>".$result[2]."</a>"."\",";
            $grid.="\"".$result[2]."\",";
            $grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",$result[8])),ENT_QUOTES)."\",";
			$grid.="\"".$result[9]."\",";
            $grid.="\"".$result[3]."\",";
            
			$result[4] =  getGeneralVendorBillAmount($result[0],$db);
			
			$grid.="\"".number_format($result[4], 2,'.','')."<input type=hidden name=totalBillValue[] value=".number_format($result[4], 2,'.','').">\",";
			//$grid.="\"".number_format($result[4], 2,'.','')."\",";
            $val=GetUserAccountsPayableDet1($result[0],$indate,$duedate,$db);
            $grid.="\"".number_format(($result[4]-$val-$creditBalance), 2,'.','')."\",";
            $total=$total+($result[4]-$val-$creditBalance);
            $grid.="\"".number_format($total, 2,'.','')."\",";
            $grid.="\"javascript:doACPEV2('".$result[0]."','".$indate."','".$duedate."')\"";
            $j++;
            $grid.="]\n";
            $statusJ="YES";
        }
        $grid.="];\n";
        $grid.="</"."script".">\n";
        return $grid;
}

function DisplayEmpSupliersList4(&$data,$db,$indate,$duedate) // Calculating suppliers payables
{
        $grid="<"."script".">\n";
        $row_count = @mysql_num_rows($data);
        $column_count = @mysql_num_fields($data);
		$tothours = cPaySetupTableFunc('hours'); // Getting working hours per day based on setup.
		$decimalPref    = getDecimalPreference();
	
        $grid.="var actcol = [";
        for($i=0;$i<$column_count;$i++)
        {
            if($i==$column_count-1)
                $grid.="\""."\"";
            else
                $grid.="\""."\",";
        }
        $grid.="];\n";

        $j=0;
        $statusJ="NO";
    	$grid.="var actdata = [\n";
        $total=0;
        while($result=mysql_fetch_row($data))
		{
           $creditBalance = number_format(getAppliedCreditAmount($result[0],'cbil'),2,'.',''); //Applied credit amount.
        
            if($j==0)
                $grid.="[";
            else
                $grid.="\n,[";

            $grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onClick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label>\",";
            $grid.="\"".$result[1]."\",";
            $grid.="\"".$result[2]."\",";
            //$grid.="\""."<a href=javascript:doACPVE1('".$result[0]."','".$indate."','".$duedate."');>".$result[2]."</a>"."\",";
            $grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",$result[8])),ENT_QUOTES)."\",";
			$grid.="\"".$result[9]."\",";
            $grid.="\"".$result[3]."\",";

			$result[4] =  getConsultingBillAmount($result[0],$db, $tothours);
            $grid.="\"".number_format($result[4], $decimalPref,'.','')."<input type=hidden name=totalBillValue[] value=".number_format($result[4], $decimalPref,'.','').">\",";
            $val=GetUserAccountsPayableDet($result[0],$indate,$duedate,$db);
            $grid.="\"".number_format(($result[4]-$val-$creditBalance), $decimalPref,'.','')."\",";
            $total=$total+($result[4]-$val-$creditBalance);
            $grid.="\"".number_format($total, $decimalPref,'.','')."\",";
            $grid.="\"javascript:doACPVE1('".$result[0]."','".$indate."','".$duedate."')\"";
 
            $j++;
            $grid.="]\n";
            $statusJ="YES";
		}

        $grid.="];\n";
        $grid.="</"."script".">\n";
        return $grid;
}

function getConsultingBillAmount($billno,$db, $tothours)
{
	$decimalPref    = getDecimalPreference();
	$sql_client = "select client_id, tax, discount from cvbill where sno=".$billno."";
	$res_client = mysql_query($sql_client,$db);
	$row_client = mysql_fetch_row($res_client);
	$exp = false;

	$total = $ttotal = $subtotal = 0;
					
		//$qu="select itemno,itemdesc,quantity,cost,type,pusername,ratetype from cvbillitem where billid='".$billno."'";
		
		$qu="SELECT cvbillitem.itemno, cvbillitem.itemdesc, cvbillitem.quantity, cvbillitem.cost, cvbillitem.type, cvbillitem.pusername, cvbillitem.ratetype, vendoritem.rate, vendoritem.rateType  
		FROM cvbillitem 
		LEFT JOIN vendoritem ON cvbillitem.itemno = vendoritem.sno AND vendoritem.status = 'ACTIVE' AND cvbillitem.type = 'OI'
		WHERE billid='".$billno."'";		
        $res=mysql_query($qu,$db);
        while($dd=mysql_fetch_row($res))
        {
            if($dd[4]=="EX" || $dd[4]=="OI")
            {       
				$rateVal = ($dd[4]=="EX" ? $dd[3] : $dd[7] );                       
	            $dd[3] = number_format($rateVal,$decimalPref,'.','');
				$dd[2] = $dd[2];
				
				if($dd[8] == '%')
				{
					$subtotal = number_format((($rateVal*$dd[2])/100),$decimalPref,'.','');
				}
				else
				{
					$subtotal = number_format(($rateVal*$dd[2]),$decimalPref,'.','');
				}
				$total=number_format($total,$decimalPref,'.','')+$subtotal;
				 $total=number_format($total,$decimalPref,'.','');
				$exp = true;
				
			}
            else if($dd[4]=="TS")
            {
                $qu="select DATE_FORMAT(par_timesheet.sdate,'%m/%d/%Y'),DATE_FORMAT(par_timesheet.edate,'%m/%d/%Y'),emp_list.name,emp_list.username from par_timesheet LEFT JOIN emp_list ON emp_list.username=par_timesheet.username where par_timesheet.sno='".$dd[0]."'";

                $res1=mysql_query($qu,$db);
                $dd1=mysql_fetch_array($res1);
                mysql_free_result($res1);
							
				$timesheetCalcResult = calculateBillTimesheet($billno, $dd[0], $dd[5], $dd[6], $tothours);
				$timesheetCalcResult = explode('|',  $timesheetCalcResult);
				$thours = $timesheetCalcResult[0];
				$timesheetCalcResult[1];
				$ttotal += number_format(($timesheetCalcResult[1]),2,'.','');				
			    $ttotal = number_format($ttotal,2,'.','');
            }
        }
		if($exp)
		{
			$total = number_format($total+$ttotal,$decimalPref,'.','');
		}else{			
			$total = number_format($total+$ttotal,2,'.','');
		}
		
        /*//For getting the extra amount paid from credited amount.
        $queCredit = "SELECT SUM(used_amount) FROM credit_memo_trans WHERE inv_bill_sno='".$billno."' AND type='cbil'";
        $resCredit = mysql_query($queCredit,$db);
        $rowCredit = mysql_fetch_row($resCredit);
        $creditBalance = $rowCredit[0];*/
        
		$dTax = number_format($total * $row_client[1] / 100,2,'.','');
		$dDiscount = number_format($total * $row_client[2] / 100,2,'.','');
		$total = $total + $dTax - $dDiscount;
	
		if($exp)
		{
			return number_format($total,$decimalPref,'.','');
		}else{
			return number_format($total,2,'.','');
		}
}

function DisplayEmpSupliersList5(&$data,$db,$indate,$duedate) // Calculating suppliers payables
{
        $grid="<"."script".">\n";
        $row_count = @mysql_num_rows($data);
        $column_count = @mysql_num_fields($data);
		$tothours = cPaySetupTableFunc('hours'); // Getting working hours per day based on setup.
        $grid.="var actcol = [";
        for($i=0;$i<$column_count;$i++)
        {
            if($i==$column_count-1)
                $grid.="\""."\"";
            else
                $grid.="\""."\",";
        }
        $grid.="];\n";

        $j=0;
        $statusJ="NO";
    	$grid.="var actdata = [\n";
        $total=0;
        while($result=mysql_fetch_row($data))
        {
            $totCreditedAmt = number_format(getAppliedCreditAmount($result[0],'conbill'),2,'.',''); //Applied credit amount.
        
            if($j==0)
                $grid.="[";
            else
                $grid.="\n,[";

            $grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onClick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label>\",";
            $grid.="\"".$result[1]."\",";
            $grid.="\"".$result[2]."\",";
          //  $grid.="\""."<a href=javascript:doACPVE2('".$result[0]."','".$indate."','".$duedate."');>".$result[2]."</a>"."\",";
            $grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",$result[9])),ENT_QUOTES)."\",";
			$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",$result[8])),ENT_QUOTES)."\",";
            $grid.="\"".$result[3]."\",";
			
			$result[4] =  getConsultantBillAmount($result[0],$db, $tothours);
			
            $grid.="\"".number_format($result[4], 2,'.','')."<input type=hidden name=totalBillValue[] value=".number_format($result[4], 2,'.','').">\",";
			
            $val=GetUserAccountsPayableDet($result[0],$indate,$duedate,$db);
            $grid.="\"".number_format(($result[4]-$val-$totCreditedAmt), 2,'.','')."\",";
            $total=$total+($result[4]-$val-$totCreditedAmt);
            $grid.="\"".number_format($total, 2,'.','')."\",";
            $grid.="\"javascript:doACPVE2('".$result[0]."','".$indate."','".$duedate."')\"";

            $j++;
            $grid.="]\n";
            $statusJ="YES";
			/* $grid.="\""."<input type=checkbox name=auids[] id=".$result[0]." onClick=chk_clearTop() value=".$result[0].">\",";
            $grid.="\"".$result[1]."\",";
            //$grid.="\""."<a href=javascript:doACPEV2('".$result[0]."','".$indate."','".$duedate."');>".$result[2]."</a>"."\",";
            $grid.="\"".$result[2]."\",";
            $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",$result[8])),ENT_QUOTES)."\",";
			$grid.="\"".$result[9]."\",";
            $grid.="\"".$result[3]."\",";
			$result[4] =  getConsultantBillAmount($result[0],$db, $tothours);

            $grid.="\"".number_format($result[4], 2,'.','')."\",";
            $val=GetUserAccountsPayableDet($result[0],$indate,$duedate,$db);
            $grid.="\"".number_format(($result[4]-$val-$totCreditedAmt), 2,'.','')."\",";
            $total=$total+($result[4]-$val-$totCreditedAmt);
            $grid.="\"".number_format($total, 2,'.','')."\",";
            $grid.="\"javascript:doACPEV2('".$result[0]."','".$indate."','".$duedate."')\"";
            $j++;
            $grid.="]\n";
            $statusJ="YES";*/
        }
        $grid.="];\n";
        $grid.="</"."script".">\n";
        return $grid;

 $grid.="\""."<label class='container-chk'><input type=checkbox name=auids[] id=".$result[0]." onClick=chk_clearTop() value=".$result[0]."><span class='checkmark'></span></label>\",";
            $grid.="\"".$result[1]."\",";
            //$grid.="\""."<a href=javascript:doACPEV2('".$result[0]."','".$indate."','".$duedate."');>".$result[2]."</a>"."\",";
            $grid.="\"".$result[2]."\",";
            $grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",$result[8])),ENT_QUOTES)."\",";
			$grid.="\"".$result[9]."\",";
            $grid.="\"".$result[3]."\",";
            $grid.="\"".number_format($result[4], 2,'.','')."\",";
            $val=GetUserAccountsPayableDet($result[0],$indate,$duedate,$db);
            $grid.="\"".number_format(($result[4]-$val-$totCreditedAmt), 2,'.','')."\",";
            $total=$total+($result[4]-$val-$totCreditedAmt);
            $grid.="\"".number_format($total, 2,'.','')."\",";
            $grid.="\"javascript:doACPEV2('".$result[0]."','".$indate."','".$duedate."')\"";
            $j++;
            $grid.="]\n";
            $statusJ="YES";
}
// Function for Get consultant bill amount for grid display
function getConsultantBillAmount($billno,$db, $tothours)
{
	$sql_client = "select client_id, tax, discount from convbill where sno=".$billno."";
	$res_client = mysql_query($sql_client,$db);
	$row_client = mysql_fetch_row($res_client);

	$total = 0;
	//$qu="select itemno,itemdesc,quantity,cost,type,pusername,ratetype from convbillitem where billid='".$billno."'";
	$qu="SELECT convbillitem.itemno, convbillitem.itemdesc, convbillitem.quantity, convbillitem.cost, convbillitem.type, convbillitem.pusername, convbillitem.ratetype, vendoritem.rate, vendoritem.rateType  
		FROM convbillitem 
		LEFT JOIN vendoritem ON convbillitem.itemno = vendoritem.sno AND vendoritem.status = 'ACTIVE' AND convbillitem.type = 'OI'
		WHERE billid='".$billno."'";	
	$res=mysql_query($qu,$db);
	while($dd=mysql_fetch_row($res))
	{
		if($dd[4]=="EX" || $dd[4]=="OI")
		{ 
			$rateVal = ($dd[4]=="EX" ? $dd[3] : $dd[7] );                       
			$dd[3] = number_format($rateVal,2,'.','');
			$dd[2] = $dd[2];
			
			if($dd[8] == '%')
			{
				$subtotal = number_format((($rateVal*$dd[2])/100),2,'.','');
			}
			else
			{
				$subtotal = number_format(($rateVal*$dd[2]),2,'.','');
			}
				
			$total=$total+$subtotal;
			$total=number_format($total,2,'.','');
 
		}
		else if($dd[4]=="TS")
		{
		
			$qu="select DATE_FORMAT(par_timesheet.sdate,'%m/%d/%Y'),DATE_FORMAT(par_timesheet.edate,'%m/%d/%Y'),emp_list.name, par_timesheet.username from par_timesheet LEFT JOIN emp_list ON emp_list.username=par_timesheet.username  where par_timesheet.sno='".$dd[0]."'";
			
			$res1=mysql_query($qu,$db);
			$dd1=mysql_fetch_array($res1);
			mysql_free_result($res1);
					
			$timesheetCalcResult = calculateBillTimesheet($billno, $dd[0], $dd[5], $dd[6], $tothours);
			$timesheetCalcResult = explode('|',  $timesheetCalcResult);
			$thours = $timesheetCalcResult[0];
			$timesheetCalcResult[1];
			$ttotal += $timesheetCalcResult[1];
			$ttotal = number_format($ttotal,2,'.','');		
		}
	} # Main Loop Ends 
	$total = number_format($total+$ttotal,2,'.','');
	
	$tax = (($total * $row_client[1]) / 100);
	$tax = number_format($tax,2,'.','');
	$disc = (($total * $row_client[2]) / 100);
	$disc = number_format($disc,2,'.','');
	
	/*$queCredit = "SELECT SUM(used_amount) FROM credit_memo_trans WHERE inv_bill_sno='".$billno."' AND type='conbill'";
    $resCredit = mysql_query($queCredit,$db);
    $rowCredit = mysql_fetch_row($resCredit);
    $creditBalance = $rowCredit[0];*/
	$total = $total + $tax - $disc;

	return number_format($total,2,'.','');
}
function GetUserAccountsPayableDet($billno,$indate,$duedate,$db)
{
    //Account Payables -- payroll & deductions and earnings
    $eque="select round(sum(bank_trans.amount),2) from bank_trans where bank_trans.source='cbil".$billno."' and type='BILLCONPM'";
    $eres=mysql_query($eque,$db);
    $total=0;
    $data=mysql_fetch_row($eres);
    mysql_free_result($eres);
    return $data[0];
}  // End of Suppliers Payables

function GetUserAccountsPayableDet1($billno,$indate,$duedate,$db)
{
	//Account Payables -- payroll & deductions and earnings
	$eque="select round(sum(bank_trans.amount),2) from bank_trans where bank_trans.source=concat('bil',$billno) and type='BillPMT'";
	$eres=mysql_query($eque,$db);
	$total=0;
	$data=mysql_fetch_row($eres);
	mysql_free_result($eres);
	return $data[0];
}

function Benefits($db)
{
		
	$column_count = 2;
	$grid="<"."script".">\n";
	
	$query_ear="select sno,eartype from companyear";
	$res_ear=mysql_query($query_ear,$db);
	$row_count_ear = mysql_num_rows($res_ear);
	
	$query_cont="select  sno,name from companycon where status='active'";
	$res_cont =mysql_query($query_cont,$db);
	$row_count_cont = mysql_num_rows($res_cont);

		

	$row_count = $row_count_ear + $row_count_cont;
	//$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count ;$i++)
	{
		if($i==$column_count -1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($res_ear))
	{	

        $grid.="[";
        $grid.="\""."<input type=checkbox name=auids[] id=e_".$result[0]." OnClick=chk_clearTop() value=e_".$result[0].">\",";
		$grid.="\"".gridcell($result[1])."\",";
		$grid.="\""."Earned Benefit"."\",";
        $grid.="\"e_".$result[0]."\",";

		$j++;

        if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	
	while ($result = @mysql_fetch_array($res_cont))
	{	
        $grid.="[";
        $grid.="\""."<input type=checkbox name=auids[] id=c_".$result[0]." OnClick=chk_clearTop() value=c_".$result[0].">\",";
		$grid.="\"".gridcell($result[1])."\",";
		$grid.="\""."Other Benefit"."\",";
        $grid.="\"c_".$result[0]."\",";
		$j++;
		
        if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return   $grid;       
}
//////////////////////////////////
function displayWorkEmpManEmpAssign(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	while ($result = @mysql_fetch_array($data))
	{
        $name=$result[2]." ".$result[3];

		if($result[9]!="" && $result[9]!="none")
		{
			//$que="select name from emp_list where username='".$result[9]."'";
			$que="select fname,mname,lname  from hrcon_general where hrcon_general.ustatus IN ('active','ACTIVE') AND  username='".$result[9]."' ";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			if(trim($row[1])!="")
				$sname=trim($row[0])." ".trim($row[1])." ".trim($row[2]);
			else 
				$sname=trim($row[0])." ".trim($row[2]);
		}
		else
		{
			$sname="";
		}

		if($result[1]!=0)
		{
			$que="select cname from staffacc_cinfo where sno=$result[1]";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$customer=$row[0];
	    }
		else
		{
			$customer="";
		}

		if($result[5]!=0)
		{
			$que="select cname from staffacc_cinfo where sno=$result[5]";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$client=$row[0];
		}
		else
		{
			$client="";
		}
		$jobstatus = explode('|',$result[11]) ;
		if($jobstatus[1] == "newreq")
		{
			$jstatus = "Needs Approval" ;
		}
		else
		{
			$jstatus = $jobstatus[0] ;
		}

        $grid.="[";
		$grid.="\""."<input type=checkbox name=auids[] id=".$result[10]."  value=".$result[10].">\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($customer)),ENT_QUOTES)."\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($name)),ENT_QUOTES)."\",";
		$grid.="\"".trim($result[4])."\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($client)),ENT_QUOTES)."\",";
       	$grid.="\"".trim($result[6])."\",";
     	$grid.="\"".trim(str_replace('-','/',$result[7]))."\",";
		$grid.="\"".trim(str_replace('-','/',$result[8]))."\",";
     	$grid.="\"".html_tls_specialchars(addslashes(trim($sname)),ENT_QUOTES)."\",";
		$grid.="\"".ucwords(trim($jstatus))."\",";
     	$grid.="\"".trim($result[0]."|"."15"."|".$result[10])."\"";


		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";

	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

//For main page query in accounting assignments. 
function displayWorkEmpManEmpAssign1()
{
	global $maildb,$db;
	
	$query="SELECT empcon_jobs.sno, empcon_jobs.client, emp_list.name, '',empcon_w4.tax, empcon_jobs.endclient, 
	if(manage.name IN ( 'Direct','Internal Direct'),concat(empcon_jobs.rate,'/',empcon_jobs.rateperiod),concat(empcon_jobs.pamount,'/',empcon_jobs.pperiod))
	, ".tzRetQueryStringSTRTODate('empcon_jobs.s_date','%m-%d-%Y','Date','-').", ".tzRetQueryStringSTRTODate('empcon_jobs.e_date','%m-%d-%Y','Date','-').", empcon_jobs.sagent
	, empcon_jobs.assg_status,emp_list.sno,empcon_jobs.username,empcon_jobs.owner
	FROM emp_list
	LEFT JOIN empcon_jobs ON emp_list.username = empcon_jobs.username
	LEFT JOIN empcon_w4 ON emp_list.username=empcon_w4.username
	LEFT JOIN manage  ON empcon_jobs.jotype=manage.sno 
	WHERE emp_list.lstatus != 'DA'   
	AND empcon_jobs.jtype!='' AND empcon_jobs.jotype!='0'
	AND empcon_jobs.assg_status <> 'reject' group by empcon_jobs.sno";
	$data=mysql_query($query,$db);
	
	
	$query1="SELECT hrcon_jobs.sno, hrcon_jobs.client, emp_list.name, '',hrcon_w4.tax, hrcon_jobs.endclient, 
	if(manage.name IN ( 'Direct','Internal Direct'),concat(hrcon_jobs.rate,'/',hrcon_jobs.rateperiod),concat(hrcon_jobs.pamount,'/',hrcon_jobs.pperiod)), ".tzRetQueryStringSTRTODate('hrcon_jobs.s_date','%m-%d-%Y','Date','-').", ".tzRetQueryStringSTRTODate('hrcon_jobs.e_date','%m-%d-%Y','Date','-').", hrcon_jobs.sagent, hrcon_jobs.assg_status,emp_list.sno,hrcon_jobs.username,hrcon_jobs.owner,hrcon_jobs.ustatus
	FROM emp_list
	LEFT JOIN hrcon_jobs ON emp_list.username = hrcon_jobs.username
	LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username
	LEFT JOIN manage  ON hrcon_jobs.jotype=manage.sno
	WHERE emp_list.lstatus != 'DA' 
	AND hrcon_jobs.jtype!='' AND hrcon_jobs.jotype!='0'
	AND hrcon_w4.ustatus='active'
	AND hrcon_jobs.ustatus in('closed','cancel') group by hrcon_jobs.sno";
	$data1=mysql_query($query1,$db);
	
	$grid="<"."script".">\n";
	$r= @mysql_num_rows($data);
	$r1= @mysql_num_rows($data1);
	$row_count =$r+$r1;
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	
	//For Active and need approval records from empcon_jobs.
	while ($result = @mysql_fetch_array($data))
	{
		//Customer
		if($result[1]!=0)
		{
			$que="select cname from staffacc_cinfo where sno=$result[1]";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$customer=$row[0];
	    }
		else
		{
			$customer="";
		}

		//Candidate Name	
        $name=$result[2];
		
		//End Client
		if($result[5]!=0)
		{
			$que="select cname from staffacc_cinfo where sno=".$result[5];
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$client=$row[0];
		}
		else
		{
			$client="";
		}
		
		//Sales agent
		$sname=getOwnerName($result[13]);
		$jobstatus = $result[10] ;
		if($jobstatus == "pending")
		{
			$jobstatus = "Needs Approval" ;
		}
		else
		{
			$jobstatus = "Active" ;
		}
		if($result[10] == "")
			$asg_status = "approved" ;
		else
			$asg_status = $result[10] ;
			
			
			// for rate dispay
		if( (strlen($result[6]) <= 5) || ($result[6] == '0.00/0.00') || ($result[6] == '0.00/HOURLY') || ($result[6] == '0.00/DAY') || ($result[6] == '0.00/YEARLY') || ($result[6] == '0.00/WEEKLY') || ($result[6] == '0.00/MONTHLY') || ($result[6] == '0.00/FLATFEE') )
			$result[6] = '';
		
		// for display of start date.
		if(trim($result[7]) == '00-00-0000' || $result[7] == '0-0-0' )
		$result[7] = '';
		
		// for end date display
		if(trim($result[8]) == '00-00-0000' || $result[8] == '0-0-0')
		$result[8] = '';
			
        $grid.="[";
		$grid.="\""."<input type=checkbox name=auids[] id=".$result[0]."|".$asg_status."|".$result[11]."  value=".$result[0]."|".$asg_status."|".$result[11].">\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($customer)),ENT_QUOTES)."\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($name)),ENT_QUOTES)."\",";
		$grid.="\"".trim($result[4])."\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($client)),ENT_QUOTES)."\",";
       	$grid.="\"".trim($result[6])."\",";
     	$grid.="\"".trim(str_replace('-','/',$result[7]))."\",";
		$grid.="\"".trim(str_replace('-','/',$result[8]))."\",";
     	$grid.="\"".html_tls_specialchars(addslashes(trim($sname)),ENT_QUOTES)."\",";
		$grid.="\"".ucwords(trim($jobstatus))."\",";
     	$grid.="\"".trim($result[0]."|"."15"."|".$asg_status."|".$result[11])."\"";
		$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";

	}
	
	// For closed records from hrcon_jobs table.
	

	while ($result = @mysql_fetch_array($data1))
	{
		//Customer
		if($result[1]!=0)
		{
			$que="select cname from staffacc_cinfo where sno=$result[1]";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$customer=$row[0];
	    }
		else
		{
			$customer="";
		}

		//Candidate Name	
        $name=$result[2];
		
		//End Client
		if($result[5]!=0)
		{
			$que="select cname from staffacc_cinfo where sno=$result[5]";
			$res=mysql_query($que,$db);
			$row=mysql_fetch_row($res);
			$client=$row[0];
		}
		else
		{
			$client="";
		}
		
		//Sales agent
		$sname=getOwnerName($result[13]);
		$jobstatus = $result[10] ;
		
		// for rate dispay
		if( (strlen($result[6]) <= 5) || ($result[6] == '0.00/0.00') || ($result[6] == '0.00/HOURLY') || ($result[6] == '0.00/DAY') || ($result[6] == '0.00/YEARLY') || ($result[6] == '0.00/WEEKLY') || ($result[6] == '0.00/MONTHLY') || ($result[6] == '0.00/FLATFEE') )
		$result[6] = '';
	
		// for display of start date.
		if($result[7] == '0-0-0' || trim($result[7]) == '00-00-0000')
		$result[7] = '';
		
		// for end date display
		if($result[8] == '0-0-0' || trim($result[8]) == '00-00-0000')
		$result[8] = '';
		if($result[14]=='cancel')
			$result[14]='cancelled';
        $grid.="[";
		$grid.="\""."<input type=checkbox name=auids[] id=".$result[0]."|".$result[14]."|".$result[11]."  value=".$result[0]."|".$result[14]."|".$result[11].">\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($customer)),ENT_QUOTES)."\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($name)),ENT_QUOTES)."\",";
		$grid.="\"".trim($result[4])."\",";
		$grid.="\"".html_tls_specialchars(addslashes(trim($client)),ENT_QUOTES)."\",";
       	$grid.="\"".trim($result[6])."\",";
     	$grid.="\"".trim(str_replace('-','/',$result[7]))."\",";
		$grid.="\"".trim(str_replace('-','/',$result[8]))."\",";
     	$grid.="\"".html_tls_specialchars(addslashes(trim($sname)),ENT_QUOTES)."\",";
		$grid.="\"".ucwords(trim($result[14]))."\",";
     	$grid.="\"".trim($result[0]."|"."15"."|$result[14]"."|".$result[11])."\"";
		$j++;
		
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}

	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

Function displayArchiveInvoiceall(&$data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto)
{
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$time="0";
	$expense="0";
	$charge="0";
	$amountdue="0";
	$clientuser="";
    $row_count1=$row_count;

	while ($result = @mysql_fetch_array($data))
	{
        if($result[1]!="" && $result[0]!="")
		{
            if($clientuser=="")
                $clientuser.=$result[2];
            else
                $clientuser.="','".$result[2];

			$ftdate=$result[0];
			$ttdate=$result[1];
			
/*			$cservicedate=date("m/d/Y", strtotime($cs2));
			$cservicedateto=date("m/d/Y", strtotime($cs1));
*/
			$cservicedate=date("m/d/Y", strtotime($ttdate));
			$cservicedateto=date("m/d/Y", strtotime($ftdate));

			$time=getArchiveTime($cs2,$cs1,$result[2],$db);
			$expense=getArchiveExpense($cs2,$cs1,$result[2],$db);
    		$charge=getCharges($ftdate,$ttdate,$result[2],$db);
			
			$lque="select staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid from staffacc_cinfo LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username where staffacc_cinfo.sno=".$result[2];

			$lres=mysql_query($lque,$db);
			$lrow=mysql_fetch_row($lres);
			$cli=$lrow[0];
			$cliid=$lrow[1];
			
			$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$lrow[2]."'";
			$pres=mysql_query($pque,$db);
			$prow=mysql_fetch_row($pres);
			
			$perDiemTot=getArchivePerDiem($cs2,$cs1,$result[2],$db,$prow[0]);
			$amountdue=$time+$expense+$charge+$perDiemTot;

			$qstr="stat=prev&indate=$cdate&duedate=$duedate&tsdate=$servicedate&tsdate1=$servicedateto&client=$result[2]";
	    	$grid.="[";
            $grid.="\"<input type=checkbox name=auids[] id=".urldecode($result[2])." OnClick=chk_clearTop() value=".urldecode($result[2])."><input type=hidden name=cliid[] id=cliid[] value=".$cliid.">\",";
        	$grid.="\"".gridcell($cli)."\",";
        	$grid.="\"".$cservicedateto."-".$cservicedate."\",";
        	$grid.="\"".number_format($time, 2,".", "")."\",";
        	$grid.="\"".number_format($expense, 2,".", "")."\",";
        	$grid.="\"".number_format($amountdue, 2,".", "")."\",";
        	$grid.="\""."invoice.php?".$qstr."\"";

        	$j++;
   			$grid.="],\n";
		}
	}

	$j=0;

	$sque="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),expense.client from par_expense LEFT JOIN expense ON expense.parid=par_expense.sno where expense.client NOT IN('".$clientuser."') and expense.billable='bil' and par_expense.astatus IN ('Archive','Archived','ER') and expense.status IN ('Archive','Archived') and DATE_FORMAT(par_expense.sdate,'%Y-%m-%d')>='".$cs1."' and DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$cs2."' group by expense.client";
	$sres=mysql_query($sque,$db);
	$row_count = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		if($srow[1]!="" && $srow[0]!="")
		{
			$ftdate=$srow[0];
			$ttdate=$srow[1];

			$cservicedate=date("m/d/Y", strtotime($cs2));
			$cservicedateto=date("m/d/Y", strtotime($cs1));

			$time=getArchiveTime($cs2,$cs1,$srow[2],$db);
			$expense=getArchiveExpense($cs2,$cs1,$srow[2],$db);
    		$charge=getCharges($ftdate,$ttdate,$srow[2],$db);
			//$perDiemTot=getArchivePerDiem($cs2,$cs1,$srow[2],$db);
			$amountdue=$time+$expense+$charge;
			
			$lque="select staffacc_cinfo.cname,staffacc_list.sno from staffacc_cinfo LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username where staffacc_cinfo.sno=".$srow[2];
			$lres=mysql_query($lque,$db);
			$lrow=mysql_fetch_row($lres);
			$cli=$lrow[0];
			$cliid=$lrow[1];
			$qstr="stat=prev&indate=$cdate&duedate=$duedate&tsdate=$servicedate&tsdate1=$servicedateto&client=$srow[2]";
            $grid.="[";
            $grid.="\"<input type=checkbox name=auids[] id=".urldecode($srow[2])." OnClick=chk_clearTop() value=".urldecode($srow[2])."><input type=hidden name=cliid[] id=cliid[] value=".$cliid.">\",";
        	$grid.="\"".gridcell($cli)."\",";
        	$grid.="\"".$cservicedateto."-".$cservicedate."\",";
        	$grid.="\"".number_format($time, 2,".", "")."\",";
        	$grid.="\"".number_format($expense, 2,".", "")."\",";
        	$grid.="\"".number_format($amountdue, 2,".", "")."\",";
        	$grid.="\""."invoice.php?".$qstr."\"";

        	$j++;
    		if($j==$row_count)
    			$grid.="]\n";
    		else
    			$grid.="],\n";
        }
    }
    if(($row_count==0) && !($row_count1==0))
    {
      $str=substr($grid,0,strlen($grid)-2);
      $str.="\n";
      $grid=$str;
    }

	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

Function getArchiveTime($cdate,$ttdate,$name,$db)
{
	$reqclient=$name;
	$count=0;

	$eque="select emp_list.username,emp_list.name,timesheet.assid,SUM(timesheet.thours),timesheet.parid,".tzRetQueryStringDate('par_timesheet.sdate','Date','/').",hrcon_jobs.bamount,".tzRetQueryStringDate('par_timesheet.edate','Date','/').",hrcon_jobs.sno,SUM(timesheet.othours),hrcon_jobs.bperiod,hrcon_jobs.otbrate_amt,hrcon_jobs.otbrate_period,SUM(timesheet.double_hours), hrcon_jobs.double_brate_amt, hrcon_jobs.double_brate_period, SUM(round((ROUND(CAST(timesheet.thours AS DECIMAL(12,2)),2) * IF(hrcon_jobs.bperiod='YEAR',ROUND((CAST(hrcon_jobs.bamount AS DECIMAL(12,2))/(8*261)),2),IF(hrcon_jobs.bperiod='MONTH',ROUND((CAST(hrcon_jobs.bamount AS DECIMAL(12,2))/(8*(261/12))),2),IF(hrcon_jobs.bperiod='WEEK',ROUND((CAST(hrcon_jobs.bamount AS DECIMAL(12,2))/(8*5)),2),IF(hrcon_jobs.bperiod='DAY',ROUND((CAST(hrcon_jobs.bamount AS DECIMAL(12,2))/8),2),ROUND(CAST(hrcon_jobs.bamount AS DECIMAL(12,2)),2)))))),2)), SUM(round((ROUND(CAST(timesheet.othours AS DECIMAL(12,2)),2) * IF(hrcon_jobs.otbrate_period='YEAR',ROUND((CAST(hrcon_jobs.otbrate_amt AS DECIMAL(12,2))/(8*261)),2),IF(hrcon_jobs.otbrate_period='MONTH',ROUND((CAST(hrcon_jobs.otbrate_amt AS DECIMAL(12,2))/(8*(261/12))),2),IF(hrcon_jobs.otbrate_period='WEEK',ROUND((CAST(hrcon_jobs.otbrate_amt AS DECIMAL(12,2))/(8*5)),2),IF(hrcon_jobs.otbrate_period='DAY',ROUND((CAST(hrcon_jobs.otbrate_amt AS DECIMAL(12,2))/8),2),ROUND(CAST(hrcon_jobs.otbrate_amt AS DECIMAL(12,2)),2)))))),2)), SUM(round((ROUND(CAST(timesheet.double_hours AS DECIMAL(12,2)),2) * IF(hrcon_jobs.double_brate_period='YEAR',ROUND((CAST(hrcon_jobs.double_brate_amt AS DECIMAL(12,2))/(8*261)),2),IF(hrcon_jobs.double_brate_period='MONTH',ROUND((CAST(hrcon_jobs.double_brate_amt AS DECIMAL(12,2))/(8*(261/12))),2),IF(hrcon_jobs.double_brate_period='WEEK',ROUND((CAST(hrcon_jobs.double_brate_amt AS DECIMAL(12,2))/(8*5)),2),IF(hrcon_jobs.double_brate_period='DAY',ROUND((CAST(hrcon_jobs.double_brate_amt AS DECIMAL(12,2))/8),2),ROUND(CAST(hrcon_jobs.double_brate_amt AS DECIMAL(12,2)),2)))))),2)) from timesheet LEFT JOIN emp_list ON timesheet.username=emp_list.username LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid and hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet on (par_timesheet.sno=timesheet.parid) where timesheet.client!='' and timesheet.type!='EARN' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and hrcon_jobs.client=".$reqclient." and timesheet.billable='Yes' and timesheet.status='Archive' group by timesheet.parid,timesheet.assid order by emp_list.name,timesheet.sdate";
	$eres=mysql_query($eque,$db);
				while($erow=mysql_fetch_row($eres))
				{
					$thours_time=$erow[3];
					if($trate=="")
						$trate=0;
					$totmins=$erow[3];
					$rhours = $erow[3] + $erow[9] + $erow[13];
					$tothours = 8;
					if($erow[10]=="YEAR")
						$trate=$erow[6]/($tothours*261);
					else if($erow[10]=="MONTH")
						$trate=$erow[6]/($tothours*(261/12));
					else if($erow[10]=="WEEK")
						$trate=$erow[6]/($tothours*5);
					else if($erow[10]=="DAY")
						$trate=$erow[6]/$tothours;
					else
						$trate=$erow[6];
					
					if($erow[12]=="YEAR")
						$otrate=$erow[11]/($tothours*261);
					else if($erow[12]=="MONTH")
						$otrate=$erow[11]/($tothours*(261/12));
					else if($erow[12]=="WEEK")
						$otrate=$erow[11]/($tothours*5);
					else if($erow[12]=="DAY")
						$otrate=$erow[11]/$tothours;
					else
						$otrate=$erow[11];
						
					if($erow[15]=="YEAR")
						$dotrate=$erow[14]/($tothours*261);
					else if($erow[15]=="MONTH")
						$dotrate=$erow[14]/($tothours*(261/12));
					else if($erow[15]=="WEEK")
						$dotrate=$erow[14]/($tothours*5);
					else if($erow[15]=="DAY")
						$dotrate=$erow[14]/$tothours;
					else
						$dotrate=$erow[14];
						
					$regrate = $trate*$erow[3];
					$overate = $otrate*$erow[9];
					$dbrate = $dotrate*$erow[13];
					$tamount += $regrate + $overate + $dbrate;
				}
	return $tamount;
}
Function getArchiveExpense($cdate,$ttdate,$name,$db)
{
	$reqclient=$name;
	$eque="select expense.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and expense.client=".$reqclient." AND expense.status = 'Archive' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') order by expense.edate";
	$eres1=mysql_query($eque,$db);
	$exp=0;
	while($erow=mysql_fetch_row($eres1))
	{
		$exp=$exp+getExpenseRate($erow[0],$db);
	}

	return $exp;
}

Function getInvoiceDeliverBalance($invid,$cli_name,$discount,$deposit,$tax,$db,$perDiem_chk,$templateId)
{
    // Creating Invoice Instance..
    $invoice=new Invoice();
    $decimalPref    = getDecimalPreference();

    // Time sheets..
    $taxt2 = $taxt;
	
	$grp_personId ='';
	if($templateId != ''){
		$grp_personId = get_personGrping_basedTemp($templateId);
	}
	
	$time_inv_qry = "SELECT emp_list.username,emp_list.name,'',SUM(timesheet.hours),timesheet.assid,".tzRetQueryStringDate('MIN(timesheet.sdate)','Date','/').",	 '0',".tzRetQueryStringDate('MAX(timesheet.sdate)','Date','/').",hrcon_jobs.sno,timesheet.tax,timesheet.parid,'0',					 '','0','',SUM(SUBSTRING(timesheet.hours,1,(INSTR(timesheet.hours,'.')-1))),SUM(SUBSTRING(timesheet.hours,(INSTR(timesheet.hours, '.')+1))),'0','','0','','0','','','0',hrcon_jobs.diem_billrate,timesheet.perdiem_billed,hrcon_jobs.diem_period,'0','0','0', hrcon_jobs.sno, timesheet.hourstype FROM timesheet_hours AS timesheet LEFT JOIN emp_list ON timesheet.username=emp_list.username LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid and hrcon_jobs.username=timesheet.username) LEFT JOIN hrcon_compen ON (hrcon_jobs.username = hrcon_compen.username AND hrcon_compen.ustatus = 'active') WHERE hrcon_jobs.client='".$cli_name."' AND timesheet.billable='".$invid."' AND timesheet.billable!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN('active', 'closed','cancel') AND hrcon_jobs.client=timesheet.client GROUP BY timesheet.username,timesheet.parid,timesheet.assid,timesheet.hourstype".$grp_personId;
	
	$eres = mysql_query($time_inv_qry,$db);

	$tcount = 0;
	$pdcount = 0;
	$parIdAsgnIdArr = array();
	while($erow=mysql_fetch_row($eres))
	{
		$parIdAsgnId = $erow[10]."-".$erow[4];
		
		$getRates = "SELECT multiplerates_assignment.rate, multiplerates_assignment.period, ROUND((ROUND(CAST('".$erow[3]."' AS DECIMAL(12,2)),2) * IF( multiplerates_assignment.period='YEAR',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*261)),2),IF(multiplerates_assignment.period='MONTH', ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*(261/12))),2), IF(multiplerates_assignment.period='WEEK',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*5)),2),IF(multiplerates_assignment.period='DAY',ROUND(( CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/8),2),ROUND(CAST( multiplerates_assignment.rate AS DECIMAL(12,2)),2)))))),2) FROM multiplerates_assignment WHERE multiplerates_assignment.asgnid = '".$erow[31]."' AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = '".$erow[32]."' AND multiplerates_assignment.ratetype = 'billrate'";
		$resRates = mysql_query($getRates,$db);
		$rowRates = mysql_fetch_row($resRates);
		
		$erow[6] = $rowRates[0];
		$erow[12] = $rowRates[1];
		$erow[28] = $rowRates[2];		
		
		$erow[3] = number_format($erow[3],2,'.','');
		$erow[11] = number_format($erow[11],2,'.','');
		$doublehours = number_format($erow[24],2,'.','');
				
		$thours_time[$tcount]=$erow[3];
		if($trate[$tcount]=="")
			$trate[$tcount]=0;
		$totmins[$tcount]=$erow[3];
		if($erow[9]=="yes")
		{
			$i=$tcount+1;
			if($taxt1=="")
				$taxt1=$i;
			else
				$taxt1.=",".$i;
		}
		if($taxt2=="")
			$taxt=$taxt1;
		else
			$taxt=$taxt2;
		$rhours[$tcount] = $erow[3] + $erow[11] + $doublehours;;

		$tothours = 8;
		
		if($erow[12]=="FLATFEE")
			$regrate = number_format($erow[6],2,'.','');
		else
			$regrate = number_format($erow[28],2,'.','');//number_format(($erow[3]*$trate[$tcount]),2,'.','');
		
					
		if($erow[26]=="billable" && $perDiem_chk=="Y")
		{
			if(!in_array($parIdAsgnId,$parIdAsgnIdArr))		
			{
				$getperDiemDays = "SELECT IF(timesheet_hours.edate='0000-00-00',COUNT(DISTINCT(timesheet_hours.sdate)),DATEDIFF(timesheet_hours.edate,timesheet_hours.sdate)+1) FROM timesheet_hours WHERE timesheet_hours.parid = '".$erow[10]."' AND timesheet_hours.assid = '".$erow[4]."' AND timesheet_hours.billable = '".$invid."' AND timesheet_hours.status = 'Billed' GROUP BY timesheet_hours.assid";	
				$resperDiemDays = mysql_query($getperDiemDays,$db); 
				$rowperDiemDays = mysql_fetch_array($resperDiemDays);
				
				$perDiemDays = $rowperDiemDays[0];
				$parIdAsgnIdArr[] = $parIdAsgnId;
			}
			else
			{
				$perDiemDays = 0;
			}
			//$perDiemDays = TimesheetDaysRange($erow[4],$erow[10],'Billed');
			
			if($erow[27]!="" && $erow[27]!="FLATFEE")
			{
				$currentTime = $erow[27];
				$perDiemTOT = calculateAmountTotal($erow[25],$currentTime,'day');
				
				$perDiemAmount[$pdcount] = number_format(($perDiemTOT * $perDiemDays),2,'.','');
			}
			else if($erow[27]!="" && $erow[27]=="FLATFEE")
				$perDiemAmount[$pdcount] = number_format($erow[25],2,'.','');
			else
				$perDiemAmount[$pdcount] = number_format(($erow[25] * $perDiemDays),2,'.','');
			
			$pdcount++;
		}

		$tamount[$tcount] = $regrate;
		$tcount++;
		
    }
	
    $ttotal=$invoice->getTotal($tamount);
	$perDiemTotal=$invoice->getTotal($perDiemAmount);

	// Expenses..
	$taxe2=$taxe;
	$ecount=0;
    $eque="select emp_list.username,emp_list.name,".tzRetQueryStringDate('expense.edate','Date','/').",IF(expense.billable!='',expense.expense_billrate,ROUND(expense.quantity * expense.unitcost,".$decimalPref.")),par_expense.advance,expense.quantity,expense.unitcost,expense.sno,expense.tax,exp_type.title,expense.parid from par_expense LEFT JOIN emp_list ON par_expense.username=emp_list.username LEFT JOIN expense ON expense.parid=par_expense.sno LEFT JOIN exp_type ON exp_type.sno=expense.expid LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where expense.billable='".$invid."' and expense.client='".$cli_name."' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') group by emp_list.username,expense.sno order by emp_list.name,expense.parid,expense.edate";
	$eres=mysql_query($eque,$db);
	while($erow=mysql_fetch_row($eres))
	{
		$eamount[$ecount]=$erow[3];

		if($erow[8]=="yes")
		{
			if($taxe1=="")
				$taxe1=($ecount+1);
			else
				$taxe1.=",".($ecount+1);
		}
		if($taxe2=="")
			$taxe=$taxe1;
		else
			$taxe=$taxe2;

		$ecount++;
    }

    $etotal=$invoice->getTotal($eamount);

	//Credits / charges..
	$i=0;
	$ique="SELECT sno,created_by,in_no,client_name,ser_date,employee_name,description,type,amount,tax,username,pusername FROM credit_charge WHERE in_no='".$invid."'";
	$ires=mysql_query($ique,$db);
	$taxc2=$taxc;

	while($irow=mysql_fetch_row($ires))
	{
		if($camount[$i]=="")
		{
			if($irow[7] == "Credit")
				$camount[$i]="-".$irow[8];
			else
				$camount[$i]=$irow[8];
		}

        if($irow[9]=="yes")
		{
			if($taxc1=="")
				$taxc1=($i+1);
			else
				$taxc1.=",".($i+1);
		}
		if($taxc2=="")
			$taxc=$taxc1;
		else
			$taxc=$taxc2;

        $i++;
	}

    $ctotal=$invoice->getTotal($camount);

	// sub total..
    $totaltax = 0;
    $totaltax=$invoice->getActTax($tamount,$eamount,$etamount,$camount,$taxt,$taxe,$taxet,$taxc,$tax);
    $stotal=$invoice->getSubTotal($ttotal,$etotal,$ctotal);
	$stotal=$stotal+$perDiemTotal;
	
	//Getting total for checked values in timesheets, expences and charges.
	$getChkTotal=$invoice->getCheckedTotal($tamount,$eamount,$camount,$taxt,$taxe,$taxc);
	
	$taxdiscPreference = getCustomerTaxDisc($templateId);
	$expForPreference = explode("|",$taxdiscPreference);
	$taxForPreference = $expForPreference[0];
	$discForPreference = $expForPreference[1];

	// Comp discounts..
	$disc_amount  = 0;
	$qry_disc = "select discname,amount,discount,amountmode,taxmode from invoice_discounts where invid='".$invid."' order by sno";
	$res_disc = mysql_query($qry_disc,$db);
	$btDiscTotal = 0.00;	// Get before tax discount amount sum...
	if($discForPreference == "Y")
	{
		while($disc_data = mysql_fetch_array($res_disc))
		{
			if($disc_data[2] == "yes")
			{
				$total_hours=array_sum($rhours);
				
				if($disc_data[4] == 'at')
				{
					if($disc_data[3] == '/hr')
					{
						if($stotal != "" && $stotal != 0)
							$disc_total = number_format(($disc_data[1] * $total_hours),2,'.','');
					}
					else if(trim($disc_data[3]) == '%')
						$disc_total = number_format((($stotal * $disc_data[1]) / 100),2,'.','');
					else
						$disc_total = number_format($disc_data[1],2,'.','');
				}
				else
				{
					if($disc_data[3] == '/hr')
					{
						if($stotal != "" && $stotal != 0)
							$disc_total = number_format(($disc_data[1] * $total_hours),2,'.','');
					}
					else if(trim($disc_data[3]) == '%')
					{
						$btDiscTotal = number_format(($btDiscTotal + (($getChkTotal * $disc_data[1]) /100)),2,'.','');
						$disc_total = number_format((($stotal * $disc_data[1]) / 100),2,'.','');
					}
					else
					{
						//$btDiscTotal = number_format(($btDiscTotal + $disc_data[1]),2,'.','');
						$btDiscTotal =  number_format(($btDiscTotal + (($getChkTotal * $disc_data[1]) /$stotal)),2,'.','');
						$disc_total = number_format($disc_data[1],2,'.','');
					}
				}
				
				$disc_amount += $disc_total;
			}
		}
	}
	else
		$disc_amount = 0.00;
	
	$newTaxableAmount = $getChkTotal - $btDiscTotal;// Get before tax taxable amount sum...
	
	// Comp taxes..
	$tax_amount  = 0;
	$qry_tax = "select taxtype,amount,tax,taxmode from invoice_taxes where invid='".$invid."' order by sno";
	$res_tax = mysql_query($qry_tax,$db);

	if($taxForPreference == "Y")
	{
		while($tax_data = mysql_fetch_array($res_tax))
		{
			if($tax_data[2] == "yes")
			{
				$total_hours=array_sum($rhours);
				
				if($tax_data[3] == '/hr')
				{
					if($getChkTotal != "" && $getChkTotal != 0)
						$tax_total = number_format(($tax_data[1] * $total_hours),2,'.','');
				}
				else if(trim($tax_data[3]) == '%')
					$tax_total = number_format((($newTaxableAmount * $tax_data[1]) / 100),2,'.','');
				else
					$tax_total = number_format($tax_data[1],2,'.','');
				
				$tax_amount += $tax_total;
			}
		}
	}
	else
		$tax_amount = 0.00;
	
	// Discount..
	//$totaldis=$invoice->getDiscount($stotal,$discount);
	$totaldis = 0;

	// Final total..
	//$totaltotal = $invoice->getTotalTotal($stotal,$discount);
	$totaltotal = $stotal;	
	$totaltotal = $totaltotal - $disc_amount;

	$totaltax = $totaltax + $tax_amount;
	$baltotal=$invoice->getBalance($totaltotal,$deposit,$totaltax);
	
	//For getting the extra amount paid from credited amount.
	$queCredit = "SELECT SUM(used_amount) FROM credit_memo_trans WHERE inv_bill_sno='".$invid."' AND type='invoice'";
	$resCredit = mysql_query($queCredit,$db);
	$rowCredit = mysql_fetch_row($resCredit);
	$creditBalance = $rowCredit[0];
	//$baltotal = $baltotal - $creditBalance;
	
	$queChargeCredit = "SELECT inv_tot_cust1_opt, inv_tot_cust2_opt, inv_tot_cust3_opt FROM IT_Totals itt LEFT JOIN Invoice_Template it ON (itt.inv_tot_sno = it.invtmp_totals) LEFT JOIN invoice inv ON (it.invtmp_sno = inv.templateid) WHERE inv.sno = '".$invid."'";
	$resChargeCredit = mysql_query($queChargeCredit,$db);
	$rowChargeCredit = mysql_fetch_row($resChargeCredit);
	
	//For getting the extra amount paid from credited amount.
	$queColumns = "SELECT tot_cust1,tot_cust2,tot_cust3 FROM invoice_columns WHERE invid = '".$invid."'";
	$resColumns = mysql_query($queColumns,$db);
	$rowColumns = mysql_fetch_row($resColumns);
	
	if($rowChargeCredit[0] == "debit")
		$baltotal = $baltotal + $rowColumns[0];
	else if($rowChargeCredit[0] == "credit")
		$baltotal = $baltotal - $rowColumns[0];
	
	if($rowChargeCredit[1] == "debit")
		$baltotal = $baltotal + $rowColumns[1];
	else if($rowChargeCredit[1] == "credit")
		$baltotal = $baltotal - $rowColumns[1];
	
	if($rowChargeCredit[2] == "debit")
		$baltotal = $baltotal + $rowColumns[2];
	else if($rowChargeCredit[2] == "credit")
		$baltotal = $baltotal - $rowColumns[2];
	
	$baltotal = number_format($baltotal,$decimalPref,'.','');

	return $baltotal;
}

Function getArchivePerDiem($cdate,$ttdate,$name,$db,$perDiem_chk)
{
	$reqclient=$name;
	$parDiemTOT = 0.00;
	
	$eque="select timesheet_hours.assid,timesheet_hours.parid,if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billrate,hrcon_compen.diem_billrate),if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billable,hrcon_compen.diem_billable), if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_period,hrcon_compen.diem_period) from timesheet_hours LEFT JOIN emp_list ON timesheet_hours.username=emp_list.username LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet_hours.assid and hrcon_jobs.username=timesheet_hours.username) LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username and hrcon_compen.ustatus='active') LEFT JOIN par_timesheet on (par_timesheet.sno=timesheet_hours.parid) where timesheet_hours.client!='' and timesheet_hours.type!='EARN' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and hrcon_jobs.client=".$reqclient." and timesheet_hours.billable='Yes' and timesheet_hours.status='Archive' group by timesheet_hours.parid,timesheet_hours.assid order by emp_list.name,timesheet_hours.sdate";
	$eres=mysql_query($eque,$db);
	
	while($erow=mysql_fetch_row($eres))
	{
		if($erow[3]=="Y" && $perDiem_chk=="Y")
		{
			$perDiemDays = TimesheetDaysRange($erow[0],$erow[1],'Archive');
			
			if($erow[4]!="" && $erow[4]!="FLATFEE")
			{
				$currentTime = $erow[4];
				$perDiemTOT = calculateAmountTotal($erow[2],$currentTime,'day');
				
				$parDiemTotal = $perDiemTOT * $perDiemDays;
			}
			else if($erow[4]!="" && $erow[4]=="FLATFEE")
				$parDiemTotal = $erow[2];
			else
				$parDiemTotal = $erow[2] * $perDiemDays;
		}
		else
			$parDiemTotal = 0.00;
		
		$parDiemTOT = $parDiemTOT + $parDiemTotal;
	}
	return $parDiemTOT;
}

function getAppliedCreditAmount($creditSno,$vendorType)
{
    global $maildb,$db;
    if($vendorType=='bil')
    {
        //For getting the extra amount paid from credited amount.
        $queCredit = "SELECT SUM(used_amount) FROM credit_memo_trans WHERE inv_bill_sno='".$creditSno."' AND type='bil'";
        $resCredit = mysql_query($queCredit,$db);
        $rowCredit = mysql_fetch_row($resCredit);
        $creditBalance = $rowCredit[0];
    }
    else if($vendorType=='cbil')
    {
        //Query for getting the total credited amount.
        $queCredit = "SELECT SUM(used_amount) FROM credit_memo_trans WHERE inv_bill_sno='".$creditSno."' AND type = 'cbil'";
        $resCredit = mysql_query($queCredit,$db);
        $rowCredit = mysql_fetch_row($resCredit);
        $creditBalance = $rowCredit[0];
    }
    else if($vendorType=='conbill')
    {
        //Query for getting the total credited amount.
        $queCredit = "SELECT SUM(used_amount) FROM credit_memo_trans WHERE inv_bill_sno='".$creditSno."' AND type = 'conbill'";
        $resCredit = mysql_query($queCredit,$db);
        $rowCredit = mysql_fetch_row($resCredit);
        $creditBalance = $rowCredit[0];
    }
    if($creditBalance == "")
		$creditBalance = 0;
    return $creditBalance;
}

function getCustomerTaxDisc($customerSno)
{
    global $maildb,$db;
    $tque="SELECT inv_tot_tax_chk, inv_tot_discount_chk FROM IT_Totals LEFT JOIN Invoice_Template ON IT_Totals.inv_tot_sno=Invoice_Template.invtmp_totals WHERE Invoice_Template.invtmp_sno = '".$customerSno."'";
    $tres=mysql_query($tque,$db);
    $trow=mysql_fetch_row($tres);
    return $trow[0]."|".$trow[1];
}
function displayReceivePay(&$data,$db,$acctype,$addr,$accounts,$frmPage)
{    
	global $partext;
	$decimalPref= getDecimalPreference();
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$totalAmount = 0;
	$testCount = mysql_num_rows($data);
	$i = 0;
	while ($result = @mysql_fetch_array($data))
	{			
		$duedate = $amtCharge = $amtPaid = $totalAmountDisplay	= $entity = "";	
		
		if($result['entityRefType'] == 'CUST' || $result['entityRefType'] == 'CV')
		{
			$getEntity = "SELECT staffacc_cinfo.cname FROM staffacc_cinfo,Client_Accounts WHERE Client_Accounts.clienttype = '".$result['entityRefType']."' AND Client_Accounts.sno = '".$result[3]."' AND staffacc_cinfo.sno = Client_Accounts.typeid";
		}	
		else if($result['entityRefType'] == 'GV')
		{
			$getEntity = "SELECT reg_payee.name FROM reg_payee,Client_Accounts WHERE Client_Accounts.clienttype = 'GV' AND Client_Accounts.sno = '".$result[3]."' AND reg_payee.sno = Client_Accounts.typeid";
		}			
		else if($result['entityRefType'] == 'EMP')
		{
			$getEntity = "SELECT emp_list.name FROM emp_list,employee_accounts WHERE employee_accounts.sno = '".$result[3]."' AND emp_list.username = employee_accounts.username";
		}	
		else if($result['entityRefType'] == 'ACC')
 		{
 			$getEntity = "SELECT reg_category.name FROM reg_category WHERE reg_category.sno = '".$result[3]."' ";
 		}	
		if($result['entityRefType'] != '')
		{
			$resEntity = mysql_query($getEntity,$db);
			$rowEntity = mysql_fetch_array($resEntity);
			
			$entity = $rowEntity[0];
		}
		else
			$entity = "";
		if($acctype == 'AR')
		{			
			if($result[7] == 'Invoice' || $result[7] == 'Payment')
			{
				if($result[7] != 'Payment')
				{
					$getInvoiceDet = "SELECT IF(billed = 'Yes','PAID',due_date),DATEDIFF(date(NOW()), str_to_date(due_date,'%m/%d/%Y')) FROM invoice WHERE sno = '".$result[5]."'";
					$resInvoiceDet = mysql_query($getInvoiceDet,$db);
					$rowInoviceDet = mysql_fetch_array($resInvoiceDet);
					
					$getDueAmount = "SELECT SUM(amount) FROM bank_trans WHERE source = 'inv".$result[5]."' AND type = 'PMT'";
					$resDueAmount = mysql_query($getDueAmount,$db);
					$rowDueAmount = mysql_fetch_array($resDueAmount);
					
					$DueAmount =number_format($result['txnAmount'], $decimalPref,".", "") - number_format($rowDueAmount[0], $decimalPref,".", "");
					
					if($rowInoviceDet[1] > 0 && $rowInoviceDet[0] != 'PAID')
						$duedate = "<font color='red'>".$rowInoviceDet[0]."</font><font color = '#006600'> (".number_format($DueAmount, $decimalPref,".", "").")</font>";
					else
						$duedate = $rowInoviceDet[0];
				}
				else
					$duedate = "";
				if($result['txnAmount'] < 0)
					$amtCharge = "<font color='red'>".number_format($result['txnAmount'], $decimalPref,".", "")."</font>";
				else
					$amtCharge = number_format($result['txnAmount'], $decimalPref,".", "");
					
				$amtPaid = "";
			}
			else if($result[7] == 'PMT' || $result[7] == 'Deposit')
			{
				if($result[7] != 'Deposit')
				{
					$getInvoiceNumber = "SELECT inv_bill_lineid FROM acc_reg WHERE rec_pay_id = '".$result[5]."'";
					$resInvoiceNumber = mysql_query($getInvoiceNumber,$db);
					if(mysql_num_rows($resInvoiceNumber) > 1)
						$duedate = "-- Split --";	
					else if(mysql_num_rows($resInvoiceNumber) == 1)
					{
						$rowInvoiceNumber = mysql_fetch_array($resInvoiceNumber);
						
						$getInvoiceDisplayNumber = "SELECT invoice_number FROM invoice WHERE sno = '".$rowInvoiceNumber[0]."'";
						$resInvoiceDisplayNumber = mysql_query($getInvoiceDisplayNumber,$db);
						$rowInvoiceDisplayNumber = mysql_fetch_array($resInvoiceDisplayNumber);
						
						$duedate = "INV : ".$rowInvoiceDisplayNumber['invoice_number'];	
					}
					else
						$duedate = "";
				}
				else
						$duedate = "";
									
				$amtCharge = "";					
				$amtPaid = number_format(abs($result['txnAmount']), $decimalPref,".", "");
			}
			$totalAmount += $result['txnAmount'];
			$totalAmountDisplay = $totalAmount;
		}
		else if($acctype == 'AP')
		{					
			if($result[7] == 'Bill' || $result[7] == 'CBILL' || $result[7] == 'CONBILL'  || $result[7] == 'Deposit')
			{
				if($result[7] == 'Bill')
				{
					$billtable = "bill";
					$appendBill = "bil";
					$appendType = "BillPMT";
				}
				else if($result[7] == 'CBILL')
				{
					$billtable = "cvbill";
					$appendBill = "cbil";
					$appendType = "BillCPMT";
				}
				else if($result[7] == 'CONBILL')
				{
					$billtable = "convbill";
					$appendBill = "conbill";
					$appendType = "BILLCONPM";
				}
					
				if($result[7] != 'Deposit')
				{
					$getBillDet = "SELECT IF(pay = 'Yes','PAID',DATE_FORMAT(due_date,'%m/%d/%Y')),DATEDIFF(date(NOW()), due_date) FROM ".$billtable." WHERE sno = '".$result[5]."'";
					$resBillDet = mysql_query($getBillDet,$db);
					if(mysql_num_rows($resBillDet) > 0)
					{
						$rowBilleDet = mysql_fetch_array($resBillDet);
					}
					else
						$rowBilleDet = array();
						
					$getDueAmount = "SELECT SUM(amount) FROM bank_trans WHERE source = '".$appendBill."".$result[5]."' AND type = '".$appendType."'";
					$resDueAmount = mysql_query($getDueAmount,$db);
					$rowDueAmount = mysql_fetch_array($resDueAmount);
					
					$DueAmount =number_format(abs($result['txnAmount']), $decimalPref,".", "") - number_format($rowDueAmount[0], $decimalPref,".", "");
					
					if($rowBilleDet[1] > 0 && $rowBilleDet[0] != 'PAID')
						$duedate = "<font color='red'>".$rowBilleDet[0]."</font><font color = '#006600'> (".number_format($DueAmount, $decimalPref,".", "").")</font>";						
					else
						$duedate = $rowBilleDet[0];
				}
				else
					$duedate = "";
				$amtCharge = number_format(abs($result['txnAmount']), $decimalPref,".", "");
					
				$amtPaid = "";
			}
			else if($result[7] == 'BillPMT' || $result[7] == 'BillCPMT' || $result[7] == 'BILLCONPM'  || $result[7] == 'Payment')
			{		
				if($result[7] != 'Payment')
				{
					$getInvoiceNumber = "SELECT inv_bill_lineid FROM acc_reg WHERE rec_pay_id = '".$result[5]."'";
					$resInvoiceNumber = mysql_query($getInvoiceNumber,$db);
					if(mysql_num_rows($resInvoiceNumber) > 1)
						$duedate = "-- Split --";	
					else if(mysql_num_rows($resInvoiceNumber) == 1)
					{
						$rowInvoiceNumber = mysql_fetch_array($resInvoiceNumber);
						$duedate = "Bill : ".$rowInvoiceNumber[0];	
					}
					else
						$duedate = "";
				}
				else
						$duedate = "";
								
				$amtCharge = "";					
				$amtPaid = number_format($result['txnAmount'], $decimalPref,".", "");
			}
			$totalAmount += $result['txnAmount'];
			$totalAmountDisplay = -1 * $totalAmount;
		}
		else if($acctype=="INC" || $acctype=="EXP" || $acctype=="EXINC" || $acctype=="EXEXP")
		{
			if($result['invStatus'] == "ACTIVE")
			{
				if($acctype=="INC" || $acctype=="EXINC")
					$charge = -1 * $result['txnAmount'];
				else
					$charge = $result['txnAmount'];
					
				if($charge < 0)
				{
					$amtCharge = "";				
					$amtPaid = number_format(abs($charge), $decimalPref,".", "");
				}
				else
				{
					$amtCharge = number_format($charge, $decimalPref,".", "");				
					$amtPaid = "";
				}			
				$totalAmount += $charge;
				$totalAmountDisplay = $totalAmount;
			}
			else
			{
				//Bypassing the adjustment records for the deleted invoices
				$amtPaid 		=  number_format($result['txnAmount'], $decimalPref,".", "");
				$totalAmountDisplay 	= $totalAmount;
			}
		}
		else
		{			
			if(($result['accType'] == 2 && $acctype != 'AP') || ($result['accType'] == 3))
			{
				if($result['txnAmount'] < 0)
				{
					$amtCharge = number_format(abs($result['txnAmount']), $decimalPref,".", "");				
					$amtPaid = "";
				}
				else
				{
					$amtCharge = ""; 					
					$amtPaid = number_format($result['txnAmount'], $decimalPref,".", "");
				}
				$totalAmount += $result['txnAmount'];
				$totalAmountDisplay = -1 * $totalAmount;
			}
			else
			{
				if($result['txnAmount'] < 0)
				{
					$amtPaid = number_format(abs($result['txnAmount']), $decimalPref,".", "");				
					$amtCharge = "";
				}
				else
				{
					$amtPaid = ""; 					
					$amtCharge = number_format($result['txnAmount'], $decimalPref,".", "");
				}
				$totalAmount += $result['txnAmount'];
				$totalAmountDisplay = $totalAmount;
			}
		}		
		
		$partext="";		
		$fullName = trim(trim(parent($result['AccountSno'])),":");
		$grid.="[";
		$grid.="\"\",";
				
		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($fullName))),ENT_QUOTES)."\",";
		
	    $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result['txnDate']))),ENT_QUOTES)."\",";
        
		if($acctype == 'AR' || $acctype == 'AP')
		{
			$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result['refNumber']))),ENT_QUOTES)."\",";
		}
		
		$grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($result['txnTypeFullName']))),ENT_QUOTES)."\",";   
		
		
        $grid.="\"".html_tls_specialchars(addslashes(str_replace("\"","'",trim($entity))),ENT_QUOTES)."\",";
			

		$grid.="\"".gridcell($result['trnasMeno'])."\","; 
		
		if($acctype == 'AR' || $acctype == 'AP')
		{
			$grid.="\"".$duedate."\","; 
		}
		
		$grid.="\"".$amtCharge."\","; 
		
		$grid.="\"".$amtPaid."\","; 		
		
		$grid.="\"".number_format($totalAmountDisplay, $decimalPref,".", "")."\","; 	
		
		if($result[7] == 'Paycheck')
		{
			$getPidGid = "SELECT pid,gid FROM net_pay WHERE sno = '".$result['txnID']."'";
			$resPidGid = mysql_query($getPidGid,$db);
			$rowPidGid = mysql_fetch_array($resPidGid);
			$pp_id = $rowPidGid['pid'];
			$gid = $rowPidGid['gid'];
			
			$grid.="\""."showbill.php?linetype=".$result[7]."&frmPage=".$frmPage."&acc=showreceive&bank=".$addr."&accounts=".$accounts."&acctype=".$acctype."&gid=".$gid."&pp_id=".$pp_id."\"";
		}
		else if($result[7] == 'EmpPMT' || $result[7] == 'LiabPMT' || $result[7] == 'TaxPMT' || $result[7] == 'TaxinvPMT')
		{		
			$grid.="\""."showbill.php?linetype=".$result[7]."&frmPage=".$frmPage."&acc=showreceive&bank=".$addr."&accounts=".$accounts."&acctype=".$acctype."&lineId=".$result['txnLineId']."&masterId=".$result['txnID']."\"";
		}
		else
			$grid.="\""."showbill.php?linetype=".$result[7]."&frmPage=".$frmPage."&acc=showreceive&bank=".$addr."&accounts=".$accounts."&acctype=".$acctype."&addr=".$result['txnID']."\"";		
		
		$j++;
		
		if($j==$row_count)
		{
			$grid.="]\n";
			break;
			}
		else
			$grid.="],\n";		
			
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function showapprovehistory(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";

		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$totalAmount = 0;
	while ($result = @mysql_fetch_array($data))
	{	
		$grid.="[";
		$grid.="\""."<input type=checkbox name=auids[] id='".$dd[2]."' value='".$dd[2]."'>\",";
    	$grid.="\"".$result[0]."\",";
		$grid.="\"".$result[6]."\",";
    	$grid.="\"".$result[1]."\",";        
		$grid.="\"".$result[3]."\",";
		$grid.="\"".number_format($result[2], 2,".", "")."\",";
    	$grid.="\""."payemppstubview.php?masterId=".$result['txnId']."&lineId=".$result['txnLineId']."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function ShowLiabilities(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$totalAmount = 0;
	while ($result = @mysql_fetch_array($data))
	{	
		$grid.="[";	
		$grid.="\"\",";	
    	$grid.="\"".$result[0]."\",";
		$grid.="\"".html_tls_specialchars(stripslashes(str_replace("\"","'",trim($result[5]))),ENT_QUOTES)."\",";
		$grid.="\"".$result[6]."\",";
		$grid.="\"".$result[1]."\",";
    	$grid.="\"".number_format($result[2], 2,".", "")."\",";     		
		$grid.="\"".$result[3]."\",";
    	$grid.="\""."paycheckliabilities.php?payliabilitiesId=".$result[4]."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function ShowPaidLiabilities(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$totalAmount = 0;
	while ($result = @mysql_fetch_array($data))
	{	
		$grid.="[";	
		$grid.="\"\",";	
    	$grid.="\"".$result[0]."\",";
		$grid.="\"".gridcell($result[1])."\",";
		$grid.="\"".$result[6]."\",";
		$grid.="\"".$result[2]."\",";
    	$grid.="\"".$result[3]."\",";     		
		$grid.="\"".number_format($result[4], 2,".", "")."\",";
    	$grid.="\""."viewpaycheckliabilities.php?masterId=".$result[5]."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function ShowTaxPaidLiabilities(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$totalAmount = 0;
	while ($result = @mysql_fetch_array($data))
	{	
		$grid.="[";	
		$grid.="\"\",";	
    	$grid.="\"".$result[0]."\",";
		$grid.="\"".gridcell($result[1])."\",";
		$grid.="\"".$result[6]."\",";
		$grid.="\"".$result[2]."\",";
    	$grid.="\"".$result[3]."\",";     		
		$grid.="\"".number_format($result[4], 2,".", "")."\",";
    	$grid.="\""."viewtaxliabilities.php?masterId=".$result[5]."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function ShowCompanyTaxes(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);
	$decimalPref= getDecimalPreference();

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$totalAmount = 0;
	while ($result = @mysql_fetch_array($data))
	{	
		if($result[3] != "PAID")
			$result[3] = number_format($result[3], $decimalPref,".", "");
		$grid.="[";	
		$grid.="\"\",";	
    	$grid.="\"".$result[0]."\",";
		$grid.="\"".$result[1]."\",";
    	$grid.="\"".number_format($result[2], 2,".", "")."\",";     		
		$grid.="\"".$result[3]."\",";
    	$grid.="\""."paytaxes.php?taxesId=".$result[4]."&taxType=".$result[1]."\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}
function displayWorkersCompensation(&$data,$db)
{
	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$totalAmount = 0;
	while ($result = @mysql_fetch_array($data))
	{	
		$grid.="[";			
    	$grid.="\"".html_tls_specialchars(addslashes($result[0]),ENT_QUOTES)."\",";
		$grid.="\"".html_tls_specialchars(addslashes($result[1]),ENT_QUOTES)."\",";
    	$grid.="\"".$result[2]."\",";     		
		$grid.="\"".$result[3]."\",";
		$grid.="\"".$result[4]."\",";
		$grid.="\"".$result[5]."\",";
    	$grid.="\""."newcompcode.php?compId=".$result[6]."&type=edit\"";
    	$j++;
		if($j==$row_count)
			$grid.="]\n";
		else
			$grid.="],\n";
	}
	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function getGeneralVendorBillAmount($billno,$db)
{
	$sql_client = "select client_id, tax, discount from bill where sno=".$billno."";
	$res_client = mysql_query($sql_client,$db);
	$row_client = mysql_fetch_row($res_client);

	$total = $ttotal = $subtotal = 0;
					
	$qu="SELECT billitem.itemno, billitem.itemdesc, billitem.quantity, billitem.cost, billitem.  	
costtype, vendoritem.rate, vendoritem.rateType  
	FROM billitem 
	LEFT JOIN vendoritem ON billitem.itemno = vendoritem.sno AND vendoritem.status = 'ACTIVE'
	WHERE billid='".$billno."'";		
	$res=mysql_query($qu,$db);
	
	while($dd=mysql_fetch_row($res))
	{
		$dd[3] = $dd[5];
		$dd[2] = $dd[2];
		
		if($dd[6] == '%')
		{
			$subtotal = number_format((($dd[5]*$dd[2])/100),2,'.','');
		}
		else
		{
			$subtotal = number_format(($dd[5]*$dd[2]),2,'.','');
		}
		$total=number_format($total,2,'.','')+$subtotal;
		$total=number_format($total,2,'.','');		
	}

	$total = number_format($total+$ttotal,2,'.','');		
   
	$dTax = number_format(($total * $row_client[1]) / 100,2,'.','');
	$dDiscount = number_format($total * ($row_client[2]) / 100,2,'.','');
	$total = $total + $dTax - $dDiscount;		
	return number_format($total,2,'.','');
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Function getMaxMindate($a,$b,$c,$d,$e,$f)
{
	$dd[0]=$a;
	$dd[1]=$b;
	$dd1[0]=$c;
	$dd1[1]=$d;
	$dd2[0]=$e;
	$dd2[1]=$f;
	if(($dd[0]!=NULL) && ($dd1[0]!=NULL) && ($dd2[0]!=NULL))
	{ 
		  if(($dd[0]<=$dd1[0]) && ($dd[0]<$dd2[0]))
		  {
				$fdate=$dd[0];
		  }
		  else if(($dd1[0]<=$dd[0]) && ($dd1[0]<$dd2[0]))
		  {
				$fdate=$dd1[0];
		  }
		  else
		  {
		  		$fdate=$dd2[0];
		  }
		  if(($dd[1]>=$dd1[1]) && ($dd[1]>$dd2[1]))
		  {
				$todate=$dd[1];
		  }
		  else if(($dd1[1]>=$dd[1]) && ($dd1[1]>$dd2[1]))
		  {
				$todate=$dd1[1];
		  }
		  else
		  {
				$todate=$dd2[1];
		  }
	}
	else if(($dd[0]!=NULL) && ($dd1[0]!=NULL))
	{
		if($dd[0]<$dd1[0])
		{
			$fdate=$dd[0];
		}
		else 
		{
			$fdate=$dd1[0];
		}
		if($dd[1]>$dd1[1])
		{
			$todate=$dd[1];
		}
		else 
		{
			$todate=$dd1[1];
		}
	}
	else if(($dd[0]!=NULL) && ($dd2[0]!=NULL))
	{
		if($dd[0]<$dd2[0])
		{
			$fdate=$dd[0];
		}
		else 
		{
			$fdate=$dd2[0];
		}
		if($dd[1]>$dd2[1])
		{
			$todate=$dd[1];
		}
		else 
		{
			$todate=$dd2[1];
		}
	}
	else if(($dd1[0]!=NULL) && ($dd2[0]!=NULL))
	{
		if($dd1[0]<$dd2[0])
		{
			$fdate=$dd1[0];
		}
		else 
		{
			$fdate=$dd2[0];
		}
		if($dd1[1]>$dd2[1])
		{
			$todate=$dd1[1];
		}
		else 
		{
			$todate=$dd2[1];
		}
	}
	else if($dd[0] != NULL)
	{
		$fdate=$dd[0];
		$todate=$dd[1];
	}
	else if($dd1[0] != NULL)
	{
		$fdate=$dd1[0];
		$todate=$dd1[1];
	}
	else if($dd2[0] != NULL)
	{
		$fdate=$dd2[0];
		$todate=$dd2[1];
	}	
	$senddate = $fdate."|".$todate;
	return $senddate;
}
Function getTimedate($cs2,$cs1,$client,$db,$chkPusernames = '')
{
	if($chkPusernames != '')
		$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
		
	$qu="select MIN(par_timesheet.sdate),MAX(par_timesheet.edate) from timesheet_hours left join par_timesheet on (timesheet_hours.parid=par_timesheet.sno)left join invoice on (invoice.sno=timesheet_hours.billable) where timesheet_hours.client='".$client."' and par_timesheet.astatus IN ('ER','Approved') AND timesheet_hours.status = 'Approved' and timesheet_hours.billable='Yes' and par_timesheet.sdate>='".$cs1."' and par_timesheet.edate<='".$cs2."' ".$condAdd." group by invoice.sno" ;
	
	$res=mysql_query($qu,$db);
	$dd=mysql_fetch_row($res);
	
	$date1 = $dd[0]."|".$dd[1];
	return $date1;
}
Function getExpensedate($cs2,$cs1,$client,$db,$chkPusernames = '')
{
	if($chkPusernames != '')
		$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
		
	$qu1="select MIN(par_expense.sdate),MAX(par_expense.edate) from expense left join par_expense on (expense.parid=par_expense.sno)left join invoice on (invoice.sno=expense.billable) where expense.client='".$client."' and par_expense.astatus IN ('ER','Approved') AND expense.status='Approved' and expense.billable='bil' and (expense.edate >='".$cs1."' AND expense.edate <= '".$cs2."') ".$condAdd." group by expense.client";
	$res1=mysql_query($qu1,$db);
	$dd1=mysql_fetch_row($res1);
	
	$date1 = $dd1[0]."|".$dd1[1];
	return $date1;
}
Function getPlacementFeedate($cs2,$cs1,$client,$db,$chkPusernames = '')
{
	if($chkPusernames != '')
		$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
		
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);
	
	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];
	
	$qu2="select MIN(date_format(str_to_date(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ),'%m-%d-%Y'),'%Y-%m-%d')),MAX(date_format( str_to_date(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ),'%m-%d-%Y'),'%Y-%m-%d')) from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y') AND hrcon_jobs.ustatus in ('closed') AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client='".$client."' AND (date_format( str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )  >='".$cs1."' AND date_format( str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )  <= '".$cs2."') AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' ".$condAdd." group by hrcon_jobs.client";
	$res2=mysql_query($qu2,$db);
	$dd2=mysql_fetch_row($res2);
	
	$date1 = $dd2[0]."|".$dd2[1];
	return $date1;
}

Function getPlacementFee($cdate,$ttdate,$name,$db,$Charge_sno,$chkPusernames = '', $po_number = '')
{
	global $assignmentsUsed,$assignmentsUsedTotal;

	$po_number_clause	= '';

	if (!empty($po_number)) {

		$po_number_clause	= " AND hrcon_jobs.po_num = '".$po_number."' ";
	}

	if($chkPusernames != '')
		$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);
	
	if($Charge_sno != '')
		$template_Charge_Check = " AND hrcon_jobs.client NOT IN ('".$Charge_sno."')";
		
	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];
		
	$sque="select hrcon_jobs.client,hrcon_jobs.pusername,hrcon_jobs.placement_fee,hrcon_jobs.s_date,hrcon_jobs.username,hrcon_jobs.sno from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y')  AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL )  AND hrcon_jobs.ustatus in ('closed') AND str_to_date(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$ttdate."' and str_to_date(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cdate."' AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' AND hrcon_jobs.client='".$name."' ".$condAdd." ".$template_Charge_Check." ".$po_number_clause;
	$eres=mysql_query($sque,$db);
	$placementfee = 0;
			
	while($erow=mysql_fetch_row($eres))
	{		
		if($chkPusernames == '')
			$assignmentsUsedTotal[] = $erow[5];
		
		$assignmentsUsed[] = $erow[5];
		
		$que1 = "SELECT SUM(amount) FROM credit_charge WHERE username='".$erow[4]."' AND pusername='".$erow[1]."'";
		$res1 = mysql_query($que1,$db);
		$rrow1 = mysql_fetch_row($res1);	
		if( $rrow1[0] >= $erow[2])
			$placementfee1 = 0;
		else
			$placementfee1 = $erow[2]-$rrow1[0];
		$placementfee += $placementfee1;
	}
	return $placementfee;
}

Function getTime($cdate,$ttdate,$name,$db,$Time_sno,$chkPusernames = '', $po_number = '',$templateId='')
{
	global $assignmentsUsed,$assignmentsUsedTotal;

	$po_number_clause	= '';

	if (!empty($po_number)) {

		$po_number_clause	= " AND hrcon_jobs.po_num = '".$po_number."' ";
	}

	if($chkPusernames != '')
		$condAdd = "AND timesheet.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	$reqclient=$name;
	$count=0;
	if($Time_sno != '')
		$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";
		
	$grp_personId ='';
	if($templateId != ''){
		$grp_personId = get_personGrping_basedTemp($templateId);
	}
	 $eque = "SELECT SUM(timesheet.hours), hrcon_jobs.sno, timesheet.hourstype, GROUP_CONCAT(timesheet.sno) FROM timesheet_hours AS timesheet LEFT JOIN emp_list ON timesheet.username=emp_list.username LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid AND hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno = timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate>='".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND  par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND hrcon_jobs.client=timesheet.client ".$template_Time_Check."  ".$condAdd." ".$po_number_clause." GROUP BY timesheet.parid,timesheet.assid,timesheet.hourstype".$grp_personId." ORDER BY emp_list.name, timesheet.sdate";
	
	$eres=mysql_query($eque,$db);
	$count=0;
	$tamount = 0;
	$taxAmount = 0;
	$timeAmounts = array();
	$timeModSnos = "";
	while($erow=mysql_fetch_row($eres))
	{	
		$getRates = "SELECT '', multiplerates_assignment.rate,multiplerates_assignment.period, ROUND((ROUND(CAST('".$erow[0]."' AS DECIMAL(12,2)),2) * IF(multiplerates_assignment.period='YEAR',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*261)),2), IF(multiplerates_assignment.period='MONTH',ROUND(( CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*(261/12))),2),IF(multiplerates_assignment.period='WEEK',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*5)),2),IF(multiplerates_assignment.period='DAY',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/8),2),ROUND(CAST( multiplerates_assignment.rate AS DECIMAL(12,2)),2)))))),2),multiplerates_assignment.taxable AS Taxable FROM  multiplerates_assignment WHERE multiplerates_assignment.asgnid = '".$erow[1]."' AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = '".$erow[2]."' AND multiplerates_assignment.ratetype = 'billrate'";
		$resRates = mysql_query($getRates,$db);
		$rowRates = mysql_fetch_array($resRates);
		if($rowRates[2]=="FLATFEE")
		{
			if($erow[0] != 0)
				$regrate = number_format($rowRates[1],2,'.','');
			else
				$regrate = 0.00;
		}
		else
			$regrate = number_format($rowRates[3],2,'.','');//number_format(($erow[3]*$trate),2,'.','');
			
		if($regrate != 0){	
			if($chkPusernames == '')
				$assignmentsUsedTotal[] = $erow[1];
			
			$assignmentsUsed[] = $erow[1];
		}
		
		$tamount += $regrate;

		
		if($rowRates['Taxable'] == 'Y')
		{
			$taxAmount += $regrate;
			if($timeModSnos == "")
				$timeModSnos = $erow[3];
			else
				$timeModSnos .= ",".$erow[3];
		}
		
	}
	$timeAmounts[0] = $tamount;
	$timeAmounts[1] = $taxAmount;
	$timeAmounts[2] = $timeModSnos;
	return $timeAmounts;
}

Function getTimeRowsCount($cdate,$ttdate,$name,$db)
{
	$reqclient=$name;
	$count=0;
	
	$eque = "SELECT SUM(timesheet.hours) FROM timesheet_hours AS timesheet LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid AND hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno = timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate>='".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND  par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND hrcon_jobs.client=timesheet.client GROUP BY timesheet.parid,timesheet.assid,timesheet.hourstype ORDER BY timesheet.sdate";
	$eres=mysql_query($eque,$db);
	return mysql_num_rows($eres)>0;
}

Function getExpense($cdate,$ttdate,$name,$db,$Exp_sno,$chkPusernames = '', $po_number = '')
{
	global $assignmentsUsed,$assignmentsUsedTotal;
	$reqclient=$name;

	$po_number_clause	= '';

	if (!empty($po_number)) {

		$po_number_clause	= " AND hrcon_jobs.po_num = '".$po_number."' ";
	}

	if($chkPusernames != '')
		$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	if($Exp_sno != '')
		$template_Expense_Check = " AND expense.client NOT IN ('".$Exp_sno."')";

		
	$eque="select expense.sno, hrcon_jobs.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') ".$template_Expense_Check." ".$condAdd." ".$po_number_clause." order by expense.edate";
	$eres1=mysql_query($eque,$db);
	$exp=0;
	while($erow=mysql_fetch_row($eres1))
	{	
		$expRowRate = getExpenseRate($erow[0],$db);
		if($expRowRate > 0){	
			if($chkPusernames == '')
				$assignmentsUsedTotal[] = $erow[1];
			
			$assignmentsUsed[] = $erow[1];
		}
		$exp=$exp+$expRowRate;
	}


	return $exp;
}

Function getExpenseRowsCount($cdate,$ttdate,$name,$db)
{
	$reqclient=$name;
	$eque="select expense.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') order by expense.edate";
	$eres1=mysql_query($eque,$db);
	return mysql_num_rows($eres1)>0;
}

Function getCharges($cdate,$ttdate,$name,$db)
{
	$que="select amount from credit_charge where client_name='".$name."' and ser_date > '".$ttdate."' and ser_date <= '".$cdate."'";
	$res=mysql_query($que,$db);
	$rate=0;
	while($row=mysql_fetch_row($res))
	{
		$rate=$rate+$row[0];
	}
	return $rate;
}

Function getExpenseRate($sno,$db)
{   
    $decimalPref= getDecimalPreference();
	$que="select IF(billable!='',round(expense_billrate,".$decimalPref."),round((unitcost*quantity),".$decimalPref.")) from expense where sno=".$sno;
	$res=mysql_query($que,$db);
	$rate=0;
	while($row=mysql_fetch_row($res))
	{
		$rate=$rate+$row[0];
	}
	return $rate;
}

	Function getTimedate_emp($cs2,$cs1,$client,$empuser,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
		
		$qu="select MIN(par_timesheet.sdate),MAX(par_timesheet.edate) from timesheet_hours left join par_timesheet on (timesheet_hours.parid=par_timesheet.sno)left join invoice on (invoice.sno=timesheet_hours.billable) where par_timesheet.username = '".$empuser."'  and timesheet_hours.client='".$client."' and par_timesheet.astatus IN ('ER','Approved') AND timesheet_hours.status = 'Approved' and timesheet_hours.billable='Yes' and par_timesheet.sdate>='".$cs1."' and par_timesheet.edate<='".$cs2."' ".$condAdd." group by invoice.sno" ;
		
		$res=mysql_query($qu,$db);
		$dd=mysql_fetch_row($res);
		
		$date1 = $dd[0]."|".$dd[1];
		return $date1;
	}
	
	Function getExpensedate_emp($cs2,$cs1,$client,$empuser,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$qu1="select MIN(par_expense.sdate),MAX(par_expense.edate) from expense left join par_expense on (expense.parid=par_expense.sno)left join invoice on (invoice.sno=expense.billable) where par_expense.username = '".$empuser."'  and expense.client='".$client."' and par_expense.astatus IN ('ER','Approved') AND expense.status='Approved' and expense.billable='bil' and (expense.edate >='".$cs1."' AND expense.edate <= '".$cs2."') ".$condAdd." group by expense.client";
		$res1=mysql_query($qu1,$db);
		$dd1=mysql_fetch_row($res1);
		
		$date1 = $dd1[0]."|".$dd1[1];
		return $date1;
	}
	
	Function getPlacementFeedate_emp($cs2,$cs1,$client,$empuser,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$todaydate=date("m-d-Y",$thisday);
		
		$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
		$resdirect = mysql_query($quedirect,$db);
		$rowdirect = mysql_fetch_row($resdirect);
		$snodirect = $rowdirect[0];
		
		$qu2="select MIN( 
	date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )
	), MAX( 
	date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )
	)
    	 from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where hrcon_jobs.username = '".$empuser."'  and (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y') AND hrcon_jobs.ustatus in ('active','closed') AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN(".$snodirect.") AND hrcon_jobs.client='".$client."' AND (date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' ) 
	 >='".$cs1."' AND date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' ) 
	 <= '".$cs2."') AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' ".$condAdd." group by hrcon_jobs.client";
		$res2=mysql_query($qu2,$db);
		$dd2=mysql_fetch_row($res2);
		
		$date1 = $dd2[0]."|".$dd2[1];
		return $date1;
	}
	
	Function getTime_emp($cdate,$ttdate,$name,$empuser,$db,$Time_sno,$chkPusernames = '',$templateId='')
	{
		global $assignmentsUsed,$assignmentsUsedTotal;
	
		if($chkPusernames != '')
			$condAdd = "AND timesheet.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$reqclient=$name;
		$count=0;
		if($Time_sno != '')
			$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";		
		
		$grp_personId ='';
		if($templateId != ''){
			$grp_personId = get_personGrping_basedTemp($templateId);
		}
		
		$eque="SELECT SUM(timesheet.hours),hrcon_jobs.sno, timesheet.hourstype, GROUP_CONCAT(timesheet.sno) FROM timesheet_hours AS timesheet LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid and hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN('active','closed','cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate>='".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND par_timesheet.username='".$empuser."' AND hrcon_jobs.client=timesheet.client ".$template_Time_Check." ".$condAdd." GROUP BY timesheet.parid,timesheet.assid, timesheet.hourstype".$grp_personId;

		$eres=mysql_query($eque,$db);
		$count=0;
		$taxAmount = $tamount = 0;
		$timeAmounts = array();
		$timeModSnos = "";
		while($erow=mysql_fetch_row($eres))
		{			
			$getRates = "SELECT '', multiplerates_assignment.rate,multiplerates_assignment.period, ROUND((ROUND(CAST('".$erow[0]."' AS DECIMAL(12,2)),2) * IF(multiplerates_assignment.period='YEAR',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*261)),2), IF(multiplerates_assignment.period='MONTH',ROUND(( CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*(261/12))),2),IF(multiplerates_assignment.period='WEEK',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*5)),2),IF(multiplerates_assignment.period='DAY',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/8),2),ROUND(CAST( multiplerates_assignment.rate AS DECIMAL(12,2)),2)))))),2), multiplerates_assignment.taxable AS Taxable FROM  multiplerates_assignment WHERE multiplerates_assignment.asgnid = '".$erow[1]."' AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = '".$erow[2]."' AND multiplerates_assignment.ratetype = 'billrate'";
			$resRates = mysql_query($getRates,$db);
			$rowRates = mysql_fetch_array($resRates);
		
			if($rowRates[2]=="FLATFEE")
			{
				if($erow[0] != 0)
					$regrate = number_format($rowRates[1],2,'.','');
				else
					$regrate = 0.00;
			}
			else
				$regrate = number_format($rowRates[3],2,'.','');//number_format(($erow[3]*$trate),2,'.','');
			
			$tamount += $regrate;
			
			if($regrate != 0){
				if($chkPusernames == '')
					$assignmentsUsedTotal[] = $erow[1];
				
				$assignmentsUsed[] = $erow[1];
			}
			
			if($rowRates['Taxable'] == 'Y')
			{
				$taxAmount += $regrate;
				if($timeModSnos == "")
					$timeModSnos = $erow[3];
				else
					$timeModSnos .= ",".$erow[3];
			}
		
		}
		$timeAmounts[0] = $tamount;
		$timeAmounts[1] = $taxAmount;
		$timeAmounts[2] = $timeModSnos;
		return $timeAmounts;
	}
	
	Function getTimeRowsCount_emp($cdate,$ttdate,$name,$empuser,$db)
	{
		$reqclient=$name;
		$count=0;
		
		$eque="SELECT SUM(timesheet.hours) FROM timesheet_hours AS timesheet LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid and hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate >= '".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND par_timesheet.username='".$empuser."' AND hrcon_jobs.client=timesheet.client ".$template_Time_Check." GROUP BY timesheet.parid,timesheet.assid,timesheet.hourstype";
		$eres=mysql_query($eque,$db);
		return mysql_num_rows($eres)>0;
	}
	
	Function getExpense_emp($cdate,$ttdate,$name,$empuser,$db,$Exp_sno,$chkPusernames = '')
	{
		global $assignmentsUsed,$assignmentsUsedTotal;
		$reqclient=$name;
		
		if($chkPusernames != '')
			$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
		
		if($Exp_sno != '')
			$exp_template_check = "AND expense.client NOT IN ('".$Exp_sno."')";
		$eque="select expense.sno,hrcon_jobs.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and par_expense.username = '".$empuser."' AND expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') ".$exp_template_check." ".$condAdd."order by expense.edate";
		$eres1=mysql_query($eque,$db);
		$exp=0;
		while($erow=mysql_fetch_row($eres1))
		{
			$expRowTotal = getExpenseRate($erow[0],$db);
			if($expRowTotal > 0){
				if($chkPusernames == '')
					$assignmentsUsedTotal[] = $erow[1];
				
				$assignmentsUsed[] = $erow[1];
			}
			$exp=$exp+$expRowTotal;
		}
	
		return $exp;
	}
	
	Function getExpenseRowsCount_emp($cdate,$ttdate,$name,$empuser,$db)
	{
		$reqclient=$name;
		$eque="select expense.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where par_expense.username = '".$empuser."' AND  expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') order by expense.edate";
		$eres1=mysql_query($eque,$db);
		return mysql_num_rows($eres1)>0;
	}
	
	Function getCharges_emp($cdate,$ttdate,$name,$empuser,$db)
	{
		$que="select amount from credit_charge where username = '".$empuser."' AND client_name='".$name."' and ser_date > '".$ttdate."' and ser_date <= '".$cdate."'";
		$res=mysql_query($que,$db);
		$rate=0;
		while($row=mysql_fetch_row($res))
		{
			$rate=$rate+$row[0];
		}
		return $rate;
	}
	
	Function getPlacementFee_emp($cdate,$ttdate,$name,$empuser,$db,$Charge_sno,$chkPusernames = '')
	{
		global $assignmentsUsed,$assignmentsUsedTotal;
	
		if($chkPusernames != '')
			$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$todaydate=date("m-d-Y",$thisday);
		if($Charge_sno != '')
			$charge_template_check = " AND hrcon_jobs.client NOT IN ('".$Charge_sno."')";
		$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
		$resdirect = mysql_query($quedirect,$db);
		$rowdirect = mysql_fetch_row($resdirect);
		$snodirect = $rowdirect[0];
			
		 $sque="select hrcon_jobs.client,hrcon_jobs.pusername,hrcon_jobs.placement_fee,hrcon_jobs.s_date,hrcon_jobs.username,hrcon_jobs.sno from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y')  AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL )  AND hrcon_jobs.ustatus in ('active','closed') AND str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date
	), '%m-%d-%Y' )>='".$ttdate."' and str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date
	), '%m-%d-%Y' )<='".$cdate."' AND hrcon_jobs.jotype IN(".$snodirect.") AND hrcon_jobs.client='".$name."' ".$condAdd ." AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' AND hrcon_jobs.username ='".$empuser."' ".$charge_template_check;
		$eres=mysql_query($sque,$db);
		$placementfee = 0;
		while($erow=mysql_fetch_row($eres))
		{
			if($chkPusernames == '')
				$assignmentsUsedTotal[] = $erow[5];
			
			$assignmentsUsed[] = $erow[5];
			
			$que1 = "SELECT SUM(amount) FROM credit_charge WHERE username='".$erow[4]."' AND pusername='".$erow[1]."'";
			$res1 = mysql_query($que1,$db);
			$rrow1 = mysql_fetch_row($res1);	
			if( $rrow1[0] >= $erow[2])
				$placementfee1 = 0;
			else
				$placementfee1 = $erow[2]-$rrow1[0];
			$placementfee += $placementfee1;
		}
		return $placementfee;
	}
	
	function getAsgnBillAddr($asgnSnos,$db)
	{		
		$getBillAddr = "SELECT bill_address, client, attention, bill_contact FROM hrcon_jobs WHERE sno IN (".$asgnSnos.") GROUP BY bill_address";
		$resBillAddr = mysql_query($getBillAddr, $db);
		$rowBillAddr = mysql_fetch_row($resBillAddr);

		$getCustDetails = "SELECT templateid FROM staffacc_cinfo WHERE sno = '".$rowBillAddr[1]."'";
		$resCustDetails = mysql_query($getCustDetails,$db);
		$rowCustDetails = mysql_fetch_array($resCustDetails);
		
		$tpl_array_values = genericTemplate($rowCustDetails['templateid']);	
		$template_Bill_To = $tpl_array_values[2];
		
		if($template_Bill_To['Billing_Add'][0] == 'Y')
			$billingAddressSelected = true;
		else
			$billingAddressSelected = false;
		
		if($billingAddressSelected){	
			if($template_Bill_To['Company_Attn'][0] == 'Y')
				$billingAttnSelected = true;
			else
				$billingAttnSelected = false;
				
			if($template_Bill_To['Billing_CT'][0] == 'Y')
				$billingContactSelected = true;
			else
				$billingContactSelected = false;
		}else{
			$billingAttnSelected = false;
			$billingContactSelected = false;
		}	
			
		$getCustBillAddr = "SELECT bill_address, attention, bill_contact FROM staffacc_cinfo WHERE sno = '".$rowBillAddr[1]."'";
		$resCustBillAddr = mysql_query($getCustBillAddr, $db);
		$rowCustBillAddr = mysql_fetch_row($resCustBillAddr);
			
		if($rowBillAddr[0] == '0' || $rowBillAddr[0] == ''){			
			$customerSno = $rowCustBillAddr[0];
			$contactSno = $rowCustBillAddr[2];
		}
		else{
			$customerSno = $rowBillAddr[0];
			$contactSno = $rowBillAddr[3];
		}
		
		if($rowBillAddr[2] == '')
			$attention = $rowCustBillAddr[1];
		else
			$attention = $rowBillAddr[2];
			
		//Getting Billing Contact details
		$sqlBilCont="SELECT CONCAT_WS(' ',IF(fname='',NULL,fname),IF(mname='',NULL,mname),IF(lname='',NULL,lname)) FROM staffacc_contact  WHERE sno='".$contactSno."' AND staffacc_contact.username!='' ";
		$resBilCont=mysql_query($sqlBilCont,$db);
		$invBilCont=mysql_fetch_row($resBilCont);
		
		$invBilCont[0] = addslashes($invBilCont[0]);
		$attention = addslashes($attention);
		
		if($billingContactSelected && $billingAttnSelected)
			$groupByCond = "CONCAT_WS(',',IF('".$invBilCont[0]."'='',NULL,'".$invBilCont[0]."'),IF('".$attention."'='',NULL,'".$attention."'),IF(address1='',NULL,address1),IF(address2='',NULL,address2))";	
		else if ($billingContactSelected && !$billingAttnSelected)	
			$groupByCond = "CONCAT_WS(',',IF('".$invBilCont[0]."'='',NULL,'".$invBilCont[0]."'),IF(address1='',NULL,address1),IF(address2='',NULL,address2))";
		else if (!$billingContactSelected && $billingAttnSelected)	
			$groupByCond = "CONCAT_WS(',',IF('".$attention."'='',NULL,'".$attention."'),IF(address1='',NULL,address1),IF(address2='',NULL,address2))";
		else
			$groupByCond = "CONCAT_WS(',',IF(address1='',NULL,address1),IF(address2='',NULL,address2))";
		
		$getCustDetails = "SELECT city, state, ".$groupByCond." AS address FROM staffacc_location WHERE sno = '".$customerSno."'";
		$resCustDetails = mysql_query($getCustDetails, $db);
		$rowCustDetails = mysql_fetch_array($resCustDetails);
		
		if(mysql_num_rows($resCustDetails) <= 0){
			$contAddr = "";

			if($billingContactSelected)
				$contAddr .= $invBilCont[0];			
			
			if($billingAttnSelected)
				$contAddr .= ",".$attention;	
				
			$rowCustDetails = array('address'=>trim($contAddr,','));
		}
		
		return $rowCustDetails;
	}
	
Function getPerDiem($cdate,$ttdate,$name,$db,$Time_sno,$perDiem_chk,$chkPusernames = '')
{
	$reqclient=$name;
	$parDiemTOT = 0.00;
	$template_Time_Check = "";
	
	if($chkPusernames != '')
		$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	if($perDiem_chk == 'Y')
	{	
		if($Time_sno != '')
			$template_Time_Check = " AND timesheet_hours.client NOT IN ('".$Time_sno."')";
		
		$eque="SELECT timesheet_hours.assid,timesheet_hours.parid,hrcon_jobs.diem_billrate,hrcon_jobs.diem_billable, hrcon_jobs.diem_period, IF(timesheet_hours.edate = '0000-00-00',1,DATEDIFF(timesheet_hours.edate,timesheet_hours.sdate)+1) AS Days FROM timesheet_hours LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername = timesheet_hours.assid AND hrcon_jobs.username = timesheet_hours.username) WHERE timesheet_hours.client != '' AND timesheet_hours.type != 'EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client = '".$reqclient."' AND timesheet_hours.billable = 'Yes' AND timesheet_hours.sdate >= '".$ttdate."' AND timesheet_hours.sdate <= '".$cdate."' AND timesheet_hours.status = 'Approved' ".$template_Time_Check." ".$condAdd." GROUP BY timesheet_hours.sdate,timesheet_hours.assid";
		$eres=mysql_query($eque,$db);
		
		$asgnListArrr = array();
		
		while($erow=mysql_fetch_row($eres))
		{			
			if($erow[3]=="Y")
			{
				$perDiemDays = $erow[5];
				
				if($erow[4]=="")

					$erow[4] = "DAY";
				
				if($erow[4] == "FLATFEE")
				{					
					if(!in_array($erow[1],$asgnListArrr))
					{
						$parDiemTotal = $erow[2];
						$asgnListArrr[] = $erow[1];
					}
					else
						$parDiemTotal = 0.00;
				}				
				else
				{					
					$perDayAmount = calculateAmountTotal($erow[2],$erow[4],'day');
					
					$parDiemTotal = $perDayAmount * $perDiemDays;
				}				
			}
			else
				$parDiemTotal = 0.00;
			
			$parDiemTOT = $parDiemTOT + $parDiemTotal;
		}
	}
	
	return $parDiemTOT;
}

Function getPerDiem_emp($cdate,$ttdate,$name,$empuser,$db,$Time_sno,$perDiem_chk,$chkPusernames = '')
{
	$reqclient=$name;
	$parDiemTOT = 0.00;
	
	if($chkPusernames != '')
		$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	$template_Time_Check = "";
	
	if($perDiem_chk == 'Y')
	{	
		if($Time_sno != '')
			$template_Time_Check = " AND timesheet_hours.client NOT IN ('".$Time_sno."')";
		
		$eque="SELECT timesheet_hours.assid,timesheet_hours.parid,hrcon_jobs.diem_billrate,hrcon_jobs.diem_billable, hrcon_jobs.diem_period, IF(timesheet_hours.edate = '0000-00-00',1,DATEDIFF(timesheet_hours.edate,timesheet_hours.sdate)+1) AS Days FROM timesheet_hours LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername = timesheet_hours.assid AND hrcon_jobs.username = timesheet_hours.username) LEFT JOIN emp_list ON timesheet_hours.username=emp_list.username WHERE timesheet_hours.client != '' AND timesheet_hours.type != 'EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client = '".$reqclient."' AND timesheet_hours.billable = 'Yes' AND timesheet_hours.sdate >= '".$ttdate."' AND timesheet_hours.sdate <= '".$cdate."' AND timesheet_hours.status = 'Approved' AND timesheet_hours.username='".$empuser."' ".$template_Time_Check." ".$condAdd." GROUP BY timesheet_hours.sdate,timesheet_hours.assid";
		
		$eres=mysql_query($eque,$db);
	
		$asgnListArrr = array();
		
		while($erow=mysql_fetch_row($eres))
		{			
			if($erow[3]=="Y")
			{
				$perDiemDays = $erow[5];
				
				if($erow[4]=="")
					$erow[4] = "DAY";
				
				if($erow[4] == "FLATFEE")
				{					
					if(!in_array($erow[1],$asgnListArrr))
					{
						$parDiemTotal = $erow[2];
						$asgnListArrr[] = $erow[1];
					}
					else
						$parDiemTotal = 0.00;
				}				
				else
				{					
					$perDayAmount = calculateAmountTotal($erow[2],$erow[4],'day');
					
					$parDiemTotal = $perDayAmount * $perDiemDays;
				}				
			}
			else
				$parDiemTotal = 0.00;
			
			$parDiemTOT = $parDiemTOT + $parDiemTotal;
		}
	}
	
	return $parDiemTOT;
}

//Customers -> Create Invoices
function displayWorkCreateInvoiceall(&$data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check)
{
	global $assignmentsUsed,$assignmentsUsedTotal,$loc_clause,$invtype,$invlocation,$invdept,$invservicedate,$invservicedateto,$username;
    $decimalPref    = getDecimalPreference();
	
    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$time="0";
	$expense="0";
	$charge="0";
	$amountdue="0";
	$clientuser="";
	$placementfee = 0;
	$check_status_grid=0;
    $row_count1=$row_count;
	
	$template_Check_arr = explode("|",$template_Check);
	$Time_sno = $template_Check_arr[0];
	$Exp_sno = $template_Check_arr[1];
	$Charge_sno = $template_Check_arr[2];	
	$TiExCh_Val = $Time_sno."^".$Exp_sno."^".$Charge_sno;	
	$TiExCh_Val = str_replace("','","-",$TiExCh_Val);

	while ($result = @mysql_fetch_array($data))
	{
		$assignmentsUsed = array();
        if($result[10]!='0')
			{
				$result[5] = $result[10];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($result[5]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
        if($result[1]!="" && $result[0]!="")
		{
            if($clientuser=="")
                $clientuser.=$result[2];
            else
                $clientuser.="','".$result[2];

			$ftdate=$result[0];
			$ttdate=$result[1];

			
			$timedate1 = getTimedate($cs2,$cs1,$result[2],$db);
			$expensedate1 = getExpensedate($cs2,$cs1,$result[2],$db);
			$placementfeedate1 = getPlacementFeedate($cs2,$cs1,$result[2],$db);
			
			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
			$MaxMinDates = explode("|",$MaxMinDates1);
			
			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			
			$timeAmounts=getTime($cs2,$cs1,$result[2],$db,$Time_sno,'','',$result[5]);
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			$timeRowsCount=getTimeRowsCount($cs2,$cs1,$result[2],$db);
			$expense=getExpense($cs2,$cs1,$result[2],$db,$Exp_sno);
			$expenseRowsCount=getExpenseRowsCount($cs2,$cs1,$result[2],$db);
			
			$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$result[5]."'";
			$pres=mysql_query($pque,$db);
			$prow=mysql_fetch_row($pres);			
			
 			$perDiemTot=getPerDiem($cs2,$cs1,$result[2],$db,$Time_sno,$prow[0]);
			
			$timeExpenseRowCount = NULL;
			if( $timeRowsCount || $expenseRowsCount )
			{
				$timeExpenseRowCount = 'Y';
			}
			else
			{
				$timeExpenseRowCount = 'N';		
			}
    		$charge=getCharges($ftdate,$ttdate,$result[2],$db);
			$placementfee = getPlacementFee($cs2,$cs1,$result[2],$db,$Charge_sno);
			$burdenchargeamt = getBurdenChargesData($db,$result[2],$Time_sno,$cs1,$cs2);
            
			$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$amountdue = $time+$expense+$charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$getSubToTDue = $amountdue;
			
			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($result[5]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];
			
			
			$getFieldsTotal = $time + $expense + $charge + $placementfee;
			
			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;
			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;
				
			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
				$burdenchargeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
				$burdenchargeTaxTotal = $burdenchargeamt;
			}
			
			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal + $burdenchargeTaxTotal;
			
			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);
			
			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $result[2]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			
			$invoiceCount = count($getArrayForInvoiceCount);
			
			if($getAlertForMultipleInvoice == "Split"){
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			}else{
				$discountTaxFlatChk = "";				
			}		
			
			$tque = "SELECT rp.amount, rp.amountmode
					FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
					WHERE cdt.customer_sno = '".$result[2]."' 
					AND cdt.tax_discount_id = ct.taxid 
					AND ct.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = ct.sno
					AND rp.parenttype = 'TAX'
					AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);
			
			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
					FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
					WHERE cdt.customer_sno = '".$result[2]."' 
					AND cdt.tax_discount_id = cd.discountid 
					AND cd.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = cd.sno
					AND rp.parenttype = 'DISCOUNT' 
					AND cdt.type = 'Discount' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);
						
			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...
			
			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						else
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
					}
				}
			}
            else
                $totalDiscAmount = "0";			
			
			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
			
			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
                		$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
					}
				}
			}
            else
                $totalTaxAmount = "0";
				
			
			if($amountdue  !=0)
    			$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			
			$cli=$result[3];
			$cliid=$result[4];
			$template_id = $result[5];	
						
			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName = getDefaultTemp_Name();  

			if(number_format($amountdue, $decimalPref,".", "") != 0)
			{
				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$result[2]";
				$grid.="[";
				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$result[2]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$result[6]."|".$getSubToTDue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$result[2]."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($cli)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				$grid.="\"".gridcell($result[7])."\",";
				$grid.="\"".stripslashes(gridcell($cli))."\",";				
				$grid.="\"".$invoiceCount."\",";
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($totalcharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($result[8])."\",";
				$grid.="\"".gridcell($result[9])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?val=redirect&invtype=Assignment&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$result[2]\"";
				}
				else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
				}
				
				
				$j++;
				
				if($j==$row_count1)
				{
					$grid.="]\n";
					$check_status_grid=1;
				}
				else
				{
					$grid.="],";
					$check_status_grid=0;
				}
			}
			else
				$j++; 			
			
		}
	}

	$j=0;
				
	$sque="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),expense.client ,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid FROM expense LEFT JOIN par_expense ON expense.parid = par_expense.sno LEFT JOIN emp_list ON emp_list.username = par_expense.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = expense.client LEFT JOIN staffacc_list ON staffacc_list.username = staffacc_cinfo.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=expense.assid LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where ".$loc_clause." par_expense.username=hrcon_jobs.username and hrcon_jobs.client= expense.client and expense.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and  expense.client != '' and expense.client NOT IN('".$clientuser."') and expense.billable='bil' and par_expense.astatus IN ('Approve','Approved','ER') and expense.status IN ('Approve','Approved') AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d')>='".$cs1."' and DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$cs2."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") group by expense.client";
	$sres=mysql_query($sque,$db);
	$row_count = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
		if($srow[10]!='0')
			{
				$srow[5] = $srow[10];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($srow[5]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		if($srow[1]!="" && $srow[0]!="")
		{
			if($clientuser=="")
                $clientuser.=$srow[2];
            else
                $clientuser.="','".$srow[2];
				
			$ftdate=$srow[0];
			$ttdate=$srow[1];
			
			$timedate1 = getTimedate($cs2,$cs1,$srow[2],$db);
			$expensedate1 = getExpensedate($cs2,$cs1,$srow[2],$db);
			$placementfeedate1 = getPlacementFeedate($cs2,$cs1,$srow[2],$db);
			
			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
			$MaxMinDates = explode("|",$MaxMinDates1);
			
			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			
			$timeAmounts=getTime($cs2,$cs1,$srow[2],$db,$Time_sno);
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			$expense=getExpense($cs2,$cs1,$srow[2],$db,$Exp_sno);
    		$charge=getCharges($ftdate,$ttdate,$srow[2],$db);
			$placementfee = getPlacementFee($cs2,$cs1,$srow[2],$db,$Charge_sno);
			$amountdue=$time+$expense+$charge+$placementfee;
			$expcharges	= $charge+$placementfee;	 	
			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($srow[5]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];
			
			$getFieldsTotal = $time + $expense + $charge + $placementfee;
			
			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;
			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;
				
			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}
			
			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
			
			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);
			
			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $srow[2]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);
			
			if($getAlertForMultipleInvoice == "Split"){
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			}else{
				$discountTaxFlatChk = "";				
			}		
			
			$tque = "SELECT rp.amount, rp.amountmode
					FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
					WHERE cdt.customer_sno = '".$srow[2]."' 
					AND cdt.tax_discount_id = ct.taxid 
					AND ct.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = ct.sno
					AND rp.parenttype = 'TAX'
					AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);
			
			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
					FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
					WHERE cdt.customer_sno = '".$srow[2]."' 
					AND cdt.tax_discount_id = cd.discountid 
					AND cd.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = cd.sno
					AND rp.parenttype = 'DISCOUNT' 
					AND cdt.type = 'Discount' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);
			
			
			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...
			
			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						else
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
					}
				}
			}
            else
                $totalDiscAmount = "0";
			
			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
			
			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
                		$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
					}
				}
			}
            else
                $totalTaxAmount = "0";
			
            if($amountdue  >0)
   				$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			
			$cli=$srow[3];
			$cliid=$srow[4];
			$template_id = $srow[5];	
								
			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name();  
			
			if(number_format($amountdue, $decimalPref,".", "") > 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				
				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[2]";
				if($check_status_grid==1)
					$grid.=",[";
				else
					$grid.="[";
					
				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$srow[2]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$srow[6]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[2]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				$grid.="\"".gridcell($srow[7])."\",";
				$grid.="\"".stripslashes(gridcell($cli))."\",";
				$grid.="\"".$invoiceCount."\",";
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($expcharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($srow[8])."\",";
				$grid.="\"".gridcell($srow[9])."\",";
				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=Assignment&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$srow[2]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
				}		
				$j++;
				if($j==$row_count)
				{
					$grid.="]\n";
					$check_status_grid=2;
				}
				else
				{
					$grid.="],";
					$check_status_grid=0;
				}
			}
			else
				$j++;
    		
        }
    }
	$j=0;
	
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);
	
	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];
	
	$sque="select hrcon_jobs.client,hrcon_jobs.pusername,SUM(hrcon_jobs.placement_fee),MIN( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )), MAX( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date,'%m-%d-%Y'))),hrcon_jobs.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billrate,hrcon_compen.diem_billrate),if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billable,hrcon_compen.diem_billable), if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_period,hrcon_compen.diem_period),staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username and hrcon_compen.ustatus='active') LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where ".$loc_clause." hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'','".$clientuser."') AND str_to_date(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$cs1."' and str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cs2."' AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") GROUP BY hrcon_jobs.client";
	$sres=mysql_query($sque,$db);
	$row_count3 = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
		if($srow[16]!='0')
			{
				$srow[8] = $srow[16];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($srow[8]);		
		$template_Time=$tpl_array_values[4];

		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
					$noTimeTax = true;
			}
		}

		$template_Expense=$tpl_array_values[5];

		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
					$noExpenseTax = true;
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		if($srow[3]!="" && $srow[4]!="")
		{
			if($clientuser=="")
                $clientuser.=$srow[0];
            else
                $clientuser.="','".$srow[0];
				
			$ftdate=$srow[3];
			$ttdate=$srow[4];
						
			$timedate1 = getTimedate($cs2,$cs1,$srow[0],$db);
			$expensedate1 = getExpensedate($cs2,$cs1,$srow[0],$db);
			$placementfeedate1 = getPlacementFeedate($cs2,$cs1,$srow[0],$db);
			
			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
			$MaxMinDates = explode("|",$MaxMinDates1);
			
			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			
			$timeAmounts=getTime($cs2,$cs1,$srow[0],$db,$Time_sno);
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			$expense=getExpense($cs2,$cs1,$srow[0],$db,$Exp_sno);
    		$charge=getCharges($ftdate,$ttdate,$srow[0],$db);
			$placementfee = getPlacementFee($cs2,$cs1,$srow[0],$db,$Charge_sno);
			$amountdue=$time+$expense+$charge+$placementfee;
			$plscharges=$charge+$placementfee;			
			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($srow[8]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];
			
			$getFieldsTotal = $time + $expense + $charge + $placementfee;
			
			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;
			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;
				
			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}
			
			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
			
			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);
			
			$assignmentsUsed = array_unique($assignmentsUsed);
			
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $srow[0]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);
			
			if($getAlertForMultipleInvoice == "Split"){
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			}else{
				$discountTaxFlatChk = "";				
			}		
			
			$tque = "SELECT rp.amount, rp.amountmode
					FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
					WHERE cdt.customer_sno = '".$srow[0]."' 
					AND cdt.tax_discount_id = ct.taxid 
					AND ct.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = ct.sno
					AND rp.parenttype = 'TAX'
					AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);
			
			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
					FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
					WHERE cdt.customer_sno = '".$srow[0]."' 
					AND cdt.tax_discount_id = cd.discountid 
					AND cd.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = cd.sno
					AND rp.parenttype = 'DISCOUNT' 
					AND cdt.type = 'Discount' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);
			
			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...
			
			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						else
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
					}
				}
			}
            else
                $totalDiscAmount = "0";
			
			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
			
			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
                		$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
					}
				}
			}
            else
                $totalTaxAmount = "0";
			
            if($amountdue  >0)
    			$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

			$cli=$srow[6];
			$cliid=$srow[7];
			$template_id = $srow[8];
						
			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name();  
			
			if(number_format($amountdue, $decimalPref,".", "") > 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				
				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[0]";
				if($check_status_grid==1 || $check_status_grid==2)
					$grid.=",[";
				else
					$grid.="[";
				
				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$srow[0]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$srow[12]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[0]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				$grid.="\"".gridcell($srow[13])."\",";
				$grid.="\"".stripslashes(gridcell($cli))."\",";
				$grid.="\"".$invoiceCount."\",";
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($plscharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($srow[14])."\",";
				$grid.="\"".gridcell($srow[15])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=Assignment&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$srow[0]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
				}
				$check_status_grid=3;
				$j++;
				if($j==$row_count3)

					$grid.="]\n";
				else
					$grid.="],";
			}
			else
				$j++;
        }
    }
	$grid = trim($grid,",");

  	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function displayWorkCreateInvoiceall_emp(&$data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check)
{
	global $assignmentsUsed,$assignmentsUsedTotal,$loc_clause,$invtype,$invlocation,$invdept,$invservicedate,$invservicedateto,$username;
    $decimalPref    = getDecimalPreference();

	$deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$time="0";
	$expense="0";
	$charge="0";
	$amountdue="0";
	$clientuser="";
	$empusercheck="";
	$placementfee = 0;
	$check_status_grid=0;
	$row_count1=$row_count;

	$template_Check_arr = explode("|",$template_Check);
	$Time_sno = $template_Check_arr[0];
	$Exp_sno = $template_Check_arr[1];
	$Charge_sno = $template_Check_arr[2];	
	$TiExCh_Val = $Time_sno."^".$Exp_sno."^".$Charge_sno;
	$TiExCh_Val = str_replace("','","-",$TiExCh_Val);

	while ($result = @mysql_fetch_array($data))
	{
		$assignmentsUsed = array();
        if($result[12]!='0')
			{
				$result[7] = $result[12];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;

		$tpl_array_values = genericTemplate($result[7]);		
		$template_Time=$tpl_array_values[4];

		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}

		$template_Expense=$tpl_array_values[5];

		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];

		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}

		if($result[1]!="" && $result[0]!="")
		{
			if($clientuser=="")
				$clientuser.=$result[2];
			else
				$clientuser.="','".$result[2];
				
			if($empusercheck=="")
				$empusercheck.=$result[4];
			else
				$empusercheck.="','".$result[4];
				
			if($clientempusercheck=="")
				$clientempusercheck.=$result[2]."|".$result[4];
			else
				$clientempusercheck.="','".$result[2]."|".$result[4];			

			$ftdate=$result[0];
			$ttdate=$result[1];

			$timedate1 = getTimedate_emp($cs2,$cs1,$result[2],$result[4],$db);
			$expensedate1 = getExpensedate_emp($cs2,$cs1,$result[2],$result[4],$db);
			$placementfeedate1 = getPlacementFeedate_emp($cs2,$cs1,$result[2],$result[4],$db);
			
			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);

			$MaxMinDates = explode("|",$MaxMinDates1);
			
			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			
			$timeAmounts=getTime_emp($cs2,$cs1,$result[2],$result[4],$db,$Time_sno,'',$result[7]);
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			$timeRowsCount=getTimeRowsCount_emp($cs2,$cs1,$result[2],$result[4],$db);
			$expense=getExpense_emp($cs2,$cs1,$result[2],$result[4],$db,$Exp_sno);
			$expenseRowsCount=getExpenseRowsCount_emp($cs2,$cs1,$result[2],$result[4],$db);
			$timeExpenseRowCount = NULL;
			
			if($timeRowsCount || $expenseRowsCount)
				$timeExpenseRowCount = 'Y';
			else
				$timeExpenseRowCount = 'N';		
			$charge=getCharges_emp($ftdate,$ttdate,$result[2],$result[4],$db);
			$placementfee = getPlacementFee_emp($cs2,$cs1,$result[2],$result[4],$db,$Charge_sno);

			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);

			$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$result[7]."'";
			$pres=mysql_query($pque,$db);
			$prow=mysql_fetch_row($pres);

			$perDiemTot=getPerDiem_emp($cs2,$cs1,$result[2],$result[4],$db,$Time_sno,$prow[0]);
			$burdenchargeamt = getBurdenChargesData($db,$result[2],$Time_sno,$cs1,$cs2,'',$result[4]);
            
			$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$amountdue=$time+$expense+$charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$getSubToTDue = $amountdue;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($result[7]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];

			$getFieldsTotal = $time + $expense + $charge + $placementfee;

			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;

			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;

			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}

			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $result[2]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);

			if($getAlertForMultipleInvoice == "Split")
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			else
				$discountTaxFlatChk = "";

			$tque = "SELECT rp.amount, rp.amountmode
			FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
			WHERE cdt.customer_sno = '".$result[2]."' 
			AND cdt.tax_discount_id = ct.taxid 
			AND ct.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = ct.sno
			AND rp.parenttype = 'TAX'
			AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);

			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
			FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
			WHERE cdt.customer_sno = '".$result[2]."' 
			AND cdt.tax_discount_id = cd.discountid 
			AND cd.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = cd.sno
			AND rp.parenttype = 'DISCOUNT' 
			AND cdt.type = 'Discount' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);

			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...

			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						else
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
					}
				}
			}
			else
			{
				$totalDiscAmount = "0";
			}

			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
					{
						$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
					}
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
					}
				}
			}
			else
			{
				$totalTaxAmount = "0";
			}

			if($amountdue  !=0)
				$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

			$cli=$result[5];
			$cliid=$result[6];
			$template_id = $result[7];

			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name();  						

			if(number_format($amountdue, $decimalPref,".", "") != 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);

				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$result[2]&empuser=$result[4]";
				$grid.="[";
				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($result[2])."|".$cservicedateto."-".$cservicedate."|".$result[4]."|".$TiExCh_Val."|".$amountdue."|".$result[8]."|".$getSubToTDue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[]  id=cliid[] value=".$result[2]."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($cli)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				$grid.="\"".gridcell($result[3])."\",";				
				$grid.="\"".gridcell($result[9])."\",";
				$grid.="\"".gridcell($cli)."\",";
				$grid.="\"".$invoiceCount."\",";
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($totalcharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($result[10])."\",";
				$grid.="\"".gridcell($result[11])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=EmployeeAsgn&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$result[4]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
				}
				$j++;

				if($j==$row_count1)
				{
					$grid.="]\n";
					$check_status_grid=1;
				}
				else
				{
					$grid.="],";
					$check_status_grid=0;
				}
			}
			else
			{
				$j++;				
			}
		}
	}

	$j=0;

	$sque="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),expense.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid from par_expense LEFT JOIN expense ON expense.parid=par_expense.sno LEFT JOIN emp_list ON emp_list.username =par_expense.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=expense.client LEFT JOIN staffacc_list ON  staffacc_list.username=staffacc_cinfo.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=expense.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active' LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." par_expense.username=hrcon_jobs.username and hrcon_jobs.client= expense.client and expense.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and expense.client != '' and (CONCAT_WS('|',expense.client,par_expense.username)  NOT IN ('".$clientempusercheck."')) and expense.billable='bil' and par_expense.astatus IN ('Approve','Approved','ER') and expense.status IN ('Approve','Approved') AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d')>='".$cs1."' and DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$cs2."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") group by par_expense.username,expense.client";
	$sres=mysql_query($sque,$db);
	$row_count = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
        if($srow[12]!='0')
			{
				$srow[7] = $srow[12];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
	
		$tpl_array_values = genericTemplate($srow[7]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}		
	
		if($srow[1]!="" && $srow[0]!="")
		{
			if($clientuser=="")
				$clientuser.=$srow[2];
			else
				$clientuser.="','".$srow[2];
				
			if($empusercheck=="")
				$empusercheck.=$srow[4];
			else
				$empusercheck.="','".$srow[4];
				
			if($clientempusercheck=="")
				$clientempusercheck.=$srow[2]."|".$srow[4];
			else
				$clientempusercheck.="','".$srow[2]."|".$srow[4];

			$ftdate=$srow[0];
			$ttdate=$srow[1];

			$timedate1 = getTimedate_emp($cs2,$cs1,$srow[2],$srow[4],$db);
			$expensedate1 = getExpensedate_emp($cs2,$cs1,$srow[2],$srow[4],$db);
			$placementfeedate1 = getPlacementFeedate_emp($cs2,$cs1,$srow[2],$srow[4],$db);

			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
			$MaxMinDates = explode("|",$MaxMinDates1);

			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];

			$timeAmounts=getTime_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Time_sno);
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			$expense=getExpense_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Exp_sno);
			$charge=getCharges_emp($ftdate,$ttdate,$srow[2],$srow[4],$db);
			$placementfee = getPlacementFee_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Charge_sno);
			//$perDiemTot=getPerDiem_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Time_sno);
			$amountdue=$time+$expense+$charge+$placementfee;
			$expcharges	= $charge+$placementfee;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($srow[7]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];

			$getFieldsTotal = $time + $expense + $charge + $placementfee;

			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;

			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;

			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}

			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);

			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $srow[2]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);

			if($getAlertForMultipleInvoice == "Split")
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			else
				$discountTaxFlatChk = "";
		
			$tque = "SELECT rp.amount, rp.amountmode
			FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
			WHERE cdt.customer_sno = '".$srow[2]."' 
			AND cdt.tax_discount_id = ct.taxid 
			AND ct.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = ct.sno
			AND rp.parenttype = 'TAX'
			AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);

			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
			FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
			WHERE cdt.customer_sno = '".$srow[2]."' 
			AND cdt.tax_discount_id = cd.discountid 
			AND cd.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = cd.sno
			AND rp.parenttype = 'DISCOUNT' 
			AND cdt.type = 'Discount' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);

			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...
			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						else
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
					}
				}
			}
			else
			{
				$totalDiscAmount = "0";
			}

			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
						$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
					}
				}
			}
			else
			{
				$totalTaxAmount = "0";
			}

			if($amountdue  >0)
				$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

			$cli=$srow[5];
			$cliid=$srow[6];
			$template_id = $srow[7];
			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name();  

			if(number_format($amountdue, $decimalPref,".", "") > 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);

				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[2]&empuser=$srow[4]";
				if($check_status_grid==1)
					$grid.=",[";
				else
					$grid.="[";

				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($srow[2])."|".$cservicedateto."-".$cservicedate."|".$srow[4]."|".$TiExCh_Val."|".$amountdue."|".$srow[8]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[2]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				$grid.="\"".gridcell($srow[3])."\",";
				$grid.="\"".gridcell($srow[9])."\",";	
				$grid.="\"".gridcell($cli)."\",";
				$grid.="\"".$invoiceCount."\",";			
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($expcharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($srow[10])."\",";
				$grid.="\"".gridcell($srow[11])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=EmployeeAsgn&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$srow[4]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
						
					
				}

				$j++;
				if($j==$row_count)
				{
					$grid.="]\n";
					$check_status_grid=2;
				}
				else
				{
					$grid.="],";
					$check_status_grid=0;
				}
			}
			else
			{
				$j++;
			}
		}
	}

	$j=0;

	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);

	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];

	$sque="select hrcon_jobs.client,hrcon_jobs.pusername,SUM(hrcon_jobs.placement_fee),MIN(IF (	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) ) 	), MAX( IF (	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )),hrcon_jobs.username,emp_list.name,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billrate,hrcon_compen.diem_billrate),if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billable,hrcon_compen.diem_billable), if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_period,hrcon_compen.diem_period),staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username and hrcon_compen.ustatus='active') LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'') AND (CONCAT_WS('|',hrcon_jobs.client,hrcon_jobs.username)  NOT IN ('".$clientempusercheck."')) AND str_to_date(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$cs1."' and str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cs2."' AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") GROUP BY hrcon_jobs.username,hrcon_jobs.client";
	$sres=mysql_query($sque,$db);
	$row_count3 = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
        if($srow[17]!='0')
			{
				$srow[9] = $srow[17];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;

		$tpl_array_values = genericTemplate($srow[9]);		
		$template_Time=$tpl_array_values[4];

		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}

		$template_Expense=$tpl_array_values[5];

		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
	
		if($srow[3]!="" && $srow[4]!="")
		{
			if($clientuser=="")
				$clientuser.=$srow[0];
			else
				$clientuser.="','".$srow[0];
				
			if($empusercheck=="")
				$empusercheck.=$srow[5];
			else
				$empusercheck.="','".$srow[5];
				
			if($clientempusercheck=="")
				$clientempusercheck.=$srow[0]."|".$srow[5];
			else
				$clientempusercheck.="','".$srow[0]."|".$srow[5];
				
			$ftdate=$srow[3];
			$ttdate=$srow[4];

			$timedate1 = getTimedate_emp($cs2,$cs1,$srow[0],$srow[5],$db);
			$expensedate1 = getExpensedate_emp($cs2,$cs1,$srow[0],$srow[5],$db);
			$placementfeedate1 = getPlacementFeedate_emp($cs2,$cs1,$srow[0],$srow[5],$db);
			
			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
			$MaxMinDates = explode("|",$MaxMinDates1);
			
			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			
			$timeAmounts=getTime_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Time_sno);
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			$expense=getExpense_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Exp_sno);
			$charge=getCharges_emp($ftdate,$ttdate,$srow[0],$srow[5],$db);
			$placementfee = getPlacementFee_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Charge_sno);
			//$perDiemTot=getPerDiem_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Time_sno);
			$amountdue=$time+$expense+$charge+$placementfee;
			$plscharges=$charge+$placementfee;
			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($srow[9]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];
			
			$getFieldsTotal = $time + $expense + $charge + $placementfee;
			
			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;
			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;
				
			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}
			
			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
			
			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);
			
			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $srow[0]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);
			
			if($getAlertForMultipleInvoice == "Split"){
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			}else{
				$discountTaxFlatChk = "";				
			}		
			
			$tque = "SELECT rp.amount, rp.amountmode
					FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
					WHERE cdt.customer_sno = '".$srow[0]."' 
					AND cdt.tax_discount_id = ct.taxid 
					AND ct.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = ct.sno
					AND rp.parenttype = 'TAX'
					AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);
			
			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
					FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
					WHERE cdt.customer_sno = '".$srow[0]."' 
					AND cdt.tax_discount_id = cd.discountid 
					AND cd.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = cd.sno
					AND rp.parenttype = 'DISCOUNT' 
					AND cdt.type = 'Discount' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);
		
			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...
			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						else
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
							$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
					}
				}
			}
			else
				$totalDiscAmount = "0";
			
			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
			
			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
						$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
					}
				}
			}
			else
				$totalTaxAmount = "0";
			
			if($amountdue  >0)
				$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

			$cli=$srow[7];
			$cliid=$srow[8];
			$template_id = $srow[9];
			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name();  
			if(number_format($amountdue, $decimalPref,".", "") > 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				
				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[0]&empuser=$srow[5]";
				if($check_status_grid==1 || $check_status_grid==2)
					$grid.=",[";
				else
					$grid.="[";
				
				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($srow[0])."|".$cservicedateto."-".$cservicedate."|".$srow[5]."|".$TiExCh_Val."|".$amountdue."|".$srow[13]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[0]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				$grid.="\"".gridcell($srow[6])."\",";
				$grid.="\"".gridcell($srow[14])."\",";
				$grid.="\"".gridcell($cli)."\",";	
				$grid.="\"".$invoiceCount."\",";
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($plscharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($srow[15])."\",";
				$grid.="\"".gridcell($srow[16])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=EmployeeAsgn&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$srow[5]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
				}
				$check_status_grid=3;
				$j++;
				if($j==$row_count3)
					$grid.="]\n";
				else
					$grid.="],";
			}
			else
				$j++;
		}
	}

	$grid = trim($grid,",");

	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}	

function displayWorkCreateInvoiceall_assgn(&$data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient)
{
	global $assignmentsUsed,$assignmentsUsedTotal,$loc_clause,$invtype,$invlocation,$invdept,$invservicedate,$invservicedateto,$username;

    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);
	$decimalPref    = getDecimalPreference();

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$time="0";
	$expense="0";
	$charge="0";
	$amountdue="0";
	$clientuser="";
	$placementfee = 0;
	$check_status_grid=0;
    $row_count1=$row_count;
	
	$template_Check_arr = explode("|",$template_Check);
	$Time_sno = $template_Check_arr[0];
	$Exp_sno = $template_Check_arr[1];
	$Charge_sno = $template_Check_arr[2];	
	$TiExCh_Val = $Time_sno."^".$Exp_sno."^".$Charge_sno;	
	$TiExCh_Val = str_replace("','","-",$TiExCh_Val);
	while ($result = @mysql_fetch_array($data))
	{	
		$assignmentsUsedTotal = array();
		 if($result[10]!='0')
			{
				$result[5] = $result[10];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($result[5]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTime($cs2,$cs1,$result[2],$db,$Time_sno);
		getExpense($cs2,$cs1,$result[2],$db,$Exp_sno);
		getPlacementFee($cs2,$cs1,$result[2],$db,$Charge_sno);		
	
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);		
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $result[2]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();
			
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
			
			if($result[1]!="" && $result[0]!="")
			{
				if($clientuser=="")
					$clientuser.=$result[2];
				else
					$clientuser.="','".$result[2];
	
				$ftdate=$result[0];
				$ttdate=$result[1];
				
				$timedate1 = getTimedate($cs2,$cs1,$result[2],$db,$chkPusernames);
				$expensedate1 = getExpensedate($cs2,$cs1,$result[2],$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate($cs2,$cs1,$result[2],$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTime($cs2,$cs1,$result[2],$db,$Time_sno,$chkPusernames,'',$result[5]);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpense($cs2,$cs1,$result[2],$db,$Exp_sno,$chkPusernames);

				$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$result[5]."'";
				$pres=mysql_query($pque,$db);
				$prow=mysql_fetch_row($pres);			

				$perDiemTot=getPerDiem($cs2,$cs1,$result[2],$db,$Time_sno,$prow[0],$chkPusernames);

				$timeExpenseRowCount = NULL;

				$placementfee = getPlacementFee($cs2,$cs1,$result[2],$db,$Charge_sno,$chkPusernames);
				$burdenchargeamt = getBurdenChargesData($db,$result[2],$Time_sno,$cs1,$cs2,$chkPusernames);

				$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
				$amountdue = $time+$expense+$placementfee+$perDiemTot+$burdenchargeamt;
				$getSubToTDue = $amountdue;

				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($result[5]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				
				$getFieldsTotal = $time + $expense + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $result[2]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$result[2]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$result[2]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
							
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				
				$totalDiscAmount = "0";		
				
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";			
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				$totalTaxAmount = "0";
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
					
				if($amountdue  !=0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
				
				$cli=$result[3];
				$cliid=$result[4];
				$template_id = $result[5];	
							
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName = getDefaultTemp_Name();  
				
				if(number_format($amountdue,$decimalPref,".", "") != 0)
				{
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$result[2]&asgnIdValues=$chkPusernames&selClient=$selClient";

					if($check_status_grid==1)
						$grid.=",[";
					else
						$grid.="[";

					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$result[2]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$result[6]."|".$getSubToTDue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$result[2]."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($cli)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($result[7])."\",";
					$grid.="\"".gridcell($cli)."\",";
					$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
					$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
					$grid.="\"".gridcell($asgnBillAddress['state'])."\",";
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($totalcharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue,$decimalPref,".", "")."\",";

					if($selClient == "")
						$grid.="\"".gridcell(stripslashes($templateName))."\",";

					$grid.="\"".gridcell($result[8])."\",";
					$grid.="\"".gridcell($result[9])."\",";
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
					
					$j++;
					
					if($j==$row_count1)
					{
						$grid.="]\n";
						$check_status_grid=1;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++; 			
				
			}		
		}
	}

	$j=0;

	if($selClient !='0' && $selClient != "")
		$clientCond = "AND expense.client IN (".$selClient.")";

	$sque="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),expense.client ,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid FROM expense LEFT JOIN par_expense ON expense.parid = par_expense.sno LEFT JOIN emp_list ON emp_list.username = par_expense.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = expense.client LEFT JOIN staffacc_list ON staffacc_list.username = staffacc_cinfo.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=expense.assid LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where ".$loc_clause." par_expense.username=hrcon_jobs.username and hrcon_jobs.client= expense.client and expense.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and  expense.client != '' and expense.client NOT IN('".$clientuser."') and expense.billable='bil' and par_expense.astatus IN ('Approve','Approved','ER') and expense.status IN ('Approve','Approved') AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d')>='".$cs1."' and DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$cs2."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' ".$clientCond." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") group by expense.client";
	$sres=mysql_query($sque,$db);
	$row_count = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = $assignmentsUsedTotal = array();
		if($srow[10]!='0')
			{
				$srow[5] = $srow[10];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($srow[5]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTime($cs2,$cs1,$srow[2],$db,$Time_sno);
		getExpense($cs2,$cs1,$srow[2],$db,$Exp_sno);
		getPlacementFee($cs2,$cs1,$srow[2],$db,$Charge_sno);
		
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);		
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $srow[2]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
		
			if($srow[1]!="" && $srow[0]!="")
			{
				if($clientuser=="")
					$clientuser.=$srow[2];
				else
					$clientuser.="','".$srow[2];
					
				$ftdate=$srow[0];
				$ttdate=$srow[1];
				
				$timedate1 = getTimedate($cs2,$cs1,$srow[2],$db,$chkPusernames);
				$expensedate1 = getExpensedate($cs2,$cs1,$srow[2],$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate($cs2,$cs1,$srow[2],$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTime($cs2,$cs1,$srow[2],$db,$Time_sno,$chkPusernames);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpense($cs2,$cs1,$srow[2],$db,$Exp_sno,$chkPusernames);
				$charge=getCharges($ftdate,$ttdate,$srow[2],$db);
				$placementfee = getPlacementFee($cs2,$cs1,$srow[2],$db,$Charge_sno,$chkPusernames);
				$amountdue=$time+$expense+$charge+$placementfee;
				$expcharges	= $charge+$placementfee;			
				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($srow[5]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				$getFieldsTotal = $time + $expense + $charge + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $srow[2]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$srow[2]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$srow[2]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
				
				
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
				
				if($amountdue  >0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
				
				$cli=$srow[3];
				$cliid=$srow[4];
				$template_id = $srow[5];	
									
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName =getDefaultTemp_Name();  
				
				if(number_format($amountdue, $decimalPref,".", "") > 0)
				{
					$assignmentsUsed = array_unique($assignmentsUsed);
					$asgnSnos = implode(",",$assignmentsUsed);
					
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[2]&asgnIdValues=$chkPusernames&selClient=$selClient";
					if($check_status_grid==1 || $check_status_grid==2)
						$grid.=",[";
					else
						$grid.="[";
						
					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$srow[2]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$srow[6]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[2]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($srow[7])."\",";
					$grid.="\"".gridcell($cli)."\",";
					$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
					$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
					$grid.="\"".gridcell($asgnBillAddress['state'])."\",";													
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($expcharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";

					if($selClient == "")
						$grid.="\"".gridcell(stripslashes($templateName))."\",";

					$grid.="\"".gridcell($srow[8])."\",";
					$grid.="\"".gridcell($srow[9])."\",";
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
					
					$j++;
					if($j==$row_count)
					{
						$grid.="]\n";
						$check_status_grid=2;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++;
				
			}
		}
    }
	$j=0;
	
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);
	
	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];

	if($selClient !='0' && $selClient != "")
		$clientCond = "AND hrcon_jobs.client IN (".$selClient.")";

	$sque="select hrcon_jobs.client,hrcon_jobs.pusername,SUM(hrcon_jobs.placement_fee),MIN( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )), MAX( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date,'%m-%d-%Y'))),hrcon_jobs.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billrate,hrcon_compen.diem_billrate),if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billable,hrcon_compen.diem_billable), if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_period,hrcon_compen.diem_period),staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username and hrcon_compen.ustatus='active') LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno where ".$loc_clause." hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'','".$clientuser."') AND str_to_date(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$cs1."' and str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cs2."' ".$clientCond." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") GROUP BY hrcon_jobs.client";
	$sres=mysql_query($sque,$db);
	$row_count3 = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = $assignmentsUsedTotal = array();
		if($srow[16]!='0')
			{
				$srow[8] = $srow[16];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($srow[8]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTime($cs2,$cs1,$srow[0],$db,$Time_sno);
		getExpense($cs2,$cs1,$srow[0],$db,$Exp_sno);
		getPlacementFee($cs2,$cs1,$srow[0],$db,$Charge_sno);
			
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);	
	
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $srow[0]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();	
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
			
			if($srow[3]!="" && $srow[4]!="")
			{
				if($clientuser=="")
					$clientuser.=$srow[0];
				else
					$clientuser.="','".$srow[0];
					
				$ftdate=$srow[3];
				$ttdate=$srow[4];
							
				$timedate1 = getTimedate($cs2,$cs1,$srow[0],$db,$chkPusernames);
				$expensedate1 = getExpensedate($cs2,$cs1,$srow[0],$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate($cs2,$cs1,$srow[0],$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTime($cs2,$cs1,$srow[0],$db,$Time_sno,$chkPusernames);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpense($cs2,$cs1,$srow[0],$db,$Exp_sno,$chkPusernames);
				//$charge=getCharges($ftdate,$ttdate,$srow[0],$db);
				$charge = 0;
				$placementfee = getPlacementFee($cs2,$cs1,$srow[0],$db,$Charge_sno,$chkPusernames);
				$amountdue=$time+$expense+$charge+$placementfee;
				$plscharges=$charge+$placementfee;				
				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($srow[8]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				$getFieldsTotal = $time + $expense + $charge + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $srow[0]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$srow[0]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$srow[0]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
				
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
				
				if($amountdue  >0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
	
				$cli=$srow[6];
				$cliid=$srow[7];
				$template_id = $srow[8];
							
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName =getDefaultTemp_Name();  
				
				if(number_format($amountdue, $decimalPref,".", "") > 0)
				{
					$assignmentsUsed = array_unique($assignmentsUsed);
					$asgnSnos = implode(",",$assignmentsUsed);
					
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[0]&asgnIdValues=$chkPusernames&selClient=$selClient";
					if($check_status_grid==1 || $check_status_grid==2 || $check_status_grid==3)
						$grid.=",[";
					else
						$grid.="[";
					
					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$srow[0]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$srow[12]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[0]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($srow[13])."\",";
					$grid.="\"".gridcell($cli)."\",";
					$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
					$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
					$grid.="\"".gridcell($asgnBillAddress['state'])."\",";							
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($plscharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";

					if($selClient == "")
						$grid.="\"".gridcell(stripslashes($templateName))."\",";

					$grid.="\"".gridcell($srow[14])."\",";
					$grid.="\"".gridcell($srow[15])."\",";
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
					$j++;
					if($j==$row_count3)
					{
						$grid.="]\n";
						$check_status_grid=3;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++;
			}
		}
    }
	$grid = trim($grid,",");

  	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function displayWorkCreateInvoiceall_assgnApprover(&$data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient,$manager_sno='')
{
	global $assignmentsUsed,$assignmentsUsedTotal,$loc_clause,$invtype,$invlocation,$invdept,$invservicedate,$invservicedateto,$username;
	
    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$approver_join ='';
	$approver_clause = '';
	if($manager_sno !='')
	{
        $approver_join = " LEFT JOIN staffacc_contact ON hrcon_jobs.manager = staffacc_contact.sno
					LEFT JOIN staffacc_contactacc ON hrcon_jobs.manager = staffacc_contactacc.con_id";
		$approver_clause = " AND hrcon_jobs.manager IN(".$manager_sno.")";
		$invtype='Approver';
 
	}
	
    $grid="<"."script".">\n";
    $row_count = @mysql_num_rows($data);
    $column_count = @mysql_num_fields($data);
	$decimalPref    = getDecimalPreference();

	$grid.="var actcol = [";
    for($i=0;$i<$column_count;$i++)
    {
        if($i==$column_count-1)
        	$grid.="\""."\"";
        else
        	$grid.="\""."\",";
    }
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$time="0";
	$expense="0";
	$charge="0";
	$amountdue="0";
	$clientuser="";
	$placementfee = 0;
	$check_status_grid=0;
    $row_count1=$row_count;
	
	$template_Check_arr = explode("|",$template_Check);
	$Time_sno = $template_Check_arr[0];
	$Exp_sno = $template_Check_arr[1];
	$Charge_sno = $template_Check_arr[2];	
	$TiExCh_Val = $Time_sno."^".$Exp_sno."^".$Charge_sno;	
	$TiExCh_Val = str_replace("','","-",$TiExCh_Val);
	while ($result = @mysql_fetch_array($data))
	{	
		$assignmentsUsedTotal = array();
		
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($result[5]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTime($cs2,$cs1,$result[2],$db,$Time_sno);
		getExpense($cs2,$cs1,$result[2],$db,$Exp_sno);
		getPlacementFee($cs2,$cs1,$result[2],$db,$Charge_sno);		
	
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);		
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $result[2]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();
			
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
			
			if($result[1]!="" && $result[0]!="")
			{
				if($clientuser=="")
					$clientuser.=$result[2];
				else
					$clientuser.="','".$result[2];
	
				$ftdate=$result[0];
				$ttdate=$result[1];
				
				$timedate1 = getTimedate_manager($cs2,$cs1,$result[2],$manager_sno,$db,$chkPusernames);
				$expensedate1 = getExpensedate_manager($cs2,$cs1,$result[2],$manager_sno,$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate_manager($cs2,$cs1,$result[2],$manager_sno,$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTimeManager($cs2,$cs1,$result[2],$db,$Time_sno,$chkPusernames, $manager_sno,$result[5]);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpenseManager($cs2,$cs1,$result[2],$db,$Exp_sno,$chkPusernames, $manager_sno);

				$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$result[5]."'";
				$pres=mysql_query($pque,$db);
				$prow=mysql_fetch_row($pres);			

				$perDiemTot=getPerDiem($cs2,$cs1,$result[2],$db,$Time_sno,$prow[0],$chkPusernames);

				$timeExpenseRowCount = NULL;

				$placementfee = getPlacementFee($cs2,$cs1,$result[2],$db,$Charge_sno,$chkPusernames);
				$burdenchargeamt = getBurdenChargesData($db,$result[2],$Time_sno,$cs1,$cs2,$chkPusernames);

				$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
				$amountdue = $time+$expense+$placementfee+$perDiemTot+$burdenchargeamt;
				$getSubToTDue = $amountdue;

				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($result[5]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				
				$getFieldsTotal = $time + $expense + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $result[2]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$result[2]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$result[2]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
							
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				
				$totalDiscAmount = "0";		
				
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";			
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				$totalTaxAmount = "0";
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
					
				if($amountdue  !=0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
				
				$cli=$result[3];
				$cliid=$result[4];
				$template_id = $result[5];	
							
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName = getDefaultTemp_Name();  
				
				if(number_format($amountdue,$decimalPref,".", "") != 0)
				{
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$result[2]&asgnIdValues=$chkPusernames&selClient=$selClient&manager_sno=$manager_sno";

					if($check_status_grid==1)
						$grid.=",[";
					else
						$grid.="[";

					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$result[2]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$result[6]."|".$getSubToTDue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$result[2]."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($cli)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($result[7])."\",";
					$grid.="\"".gridcell($cli)."\",";
					$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
					$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
					$grid.="\"".gridcell($asgnBillAddress['state'])."\",";
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($totalcharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue,$decimalPref,".", "")."\",";

					if($selClient == "")
						$grid.="\"".gridcell(stripslashes($templateName))."\",";

					$grid.="\"".gridcell($result[8])."\",";
					$grid.="\"".gridcell($result[9])."\",";
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
					
					$j++;
					
					if($j==$row_count1)
					{
						$grid.="]\n";
						$check_status_grid=1;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++; 			
				
			}		
		}
	}

	$j=0;

	if($selClient !='0' && $selClient != "")
		$clientCond = "AND expense.client IN (".$selClient.")";

	$sque="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),expense.client ,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname FROM expense LEFT JOIN par_expense ON expense.parid = par_expense.sno LEFT JOIN emp_list ON emp_list.username = par_expense.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = expense.client LEFT JOIN staffacc_list ON staffacc_list.username = staffacc_cinfo.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=expense.assid LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno ".$approver_join." where ".$loc_clause." par_expense.username=hrcon_jobs.username and hrcon_jobs.client= expense.client and expense.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and  expense.client != '' and expense.client NOT IN('".$clientuser."') and expense.billable='bil' and par_expense.astatus IN ('Approve','Approved','ER') and expense.status IN ('Approve','Approved') AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d')>='".$cs1."' and DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$cs2."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' ".$clientCond . $approver_clause." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") group by expense.client";
	$sres=mysql_query($sque,$db);
	$row_count = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = $assignmentsUsedTotal = array();
		
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($srow[5]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTimeManager($cs2,$cs1,$srow[2],$db,$Time_sno, $manager_sno);
		getExpenseManager($cs2,$cs1,$srow[2],$db,$Exp_sno, $manager_sno);
		getPlacementFeeManager($cs2,$cs1,$srow[2],$db,$Charge_sno, $manager_sno);
		
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);		
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $srow[2]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
		
			if($srow[1]!="" && $srow[0]!="")
			{
				if($clientuser=="")
					$clientuser.=$srow[2];
				else
					$clientuser.="','".$srow[2];
					
				$ftdate=$srow[0];
				$ttdate=$srow[1];
				
				$timedate1 = getTimedate_manager($cs2,$cs1,$srow[2],$manager_sno,$db,$chkPusernames);
				$expensedate1 = getExpensedate_manager($cs2,$cs1,$srow[2],$manager_sno,$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate_manager($cs2,$cs1,$srow[2],$manager_sno,$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTimeManager($cs2,$cs1,$srow[2],$db,$Time_sno,$chkPusernames,$manager_sno);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpenseManager($cs2,$cs1,$srow[2],$db,$Exp_sno,$chkPusernames,$manager_sno);
				$charge=getCharges_manager($ftdate,$ttdate,$srow[2],$manager_sno,$db);
				$placementfee = getPlacementFeeManager($cs2,$cs1,$srow[2],$db,$Charge_sno,$chkPusernames,$manager_sno);
				$amountdue=$time+$expense+$charge+$placementfee;
				$expcharges	= $charge+$placementfee;	
							
				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($srow[5]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				$getFieldsTotal = $time + $expense + $charge + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $srow[2]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$srow[2]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$srow[2]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
				
				
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
				
				if($amountdue  >0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
				
				$cli=$srow[3];
				$cliid=$srow[4];
				$template_id = $srow[5];	
									
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName =getDefaultTemp_Name();  
				
				if(number_format($amountdue, $decimalPref,".", "") > 0)
				{
					$assignmentsUsed = array_unique($assignmentsUsed);
					$asgnSnos = implode(",",$assignmentsUsed);
					
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[2]&asgnIdValues=$chkPusernames&selClient=$selClient&manager_sno=$manager_sno";
					if($check_status_grid==1 || $check_status_grid==2)
						$grid.=",[";
					else
						$grid.="[";
						
					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$srow[2]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$srow[6]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[2]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($srow[7])."\",";
					$grid.="\"".gridcell($cli)."\",";
					$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
					$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
					$grid.="\"".gridcell($asgnBillAddress['state'])."\",";													
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($expcharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";

					if($selClient == "")
						$grid.="\"".gridcell(stripslashes($templateName))."\",";

					$grid.="\"".gridcell($srow[8])."\",";
					$grid.="\"".gridcell($srow[9])."\",";
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
					
					$j++;
					if($j==$row_count)
					{
						$grid.="]\n";
						$check_status_grid=2;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++;
				
			}
		}
    }
	$j=0;
	
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);
	
	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];

	if($selClient !='0' && $selClient != "")
		$clientCond = "AND hrcon_jobs.client IN (".$selClient.")";
 
	$sque="select hrcon_jobs.client,hrcon_jobs.pusername,SUM(hrcon_jobs.placement_fee),MIN( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )), MAX( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date,'%m-%d-%Y'))),hrcon_jobs.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billrate,hrcon_compen.diem_billrate),if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billable,hrcon_compen.diem_billable), if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_period,hrcon_compen.diem_period),staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username and hrcon_compen.ustatus='active') LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno ".$approver_join." where ".$loc_clause." hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'','".$clientuser."') AND str_to_date(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$cs1."' and str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cs2."' ".$clientCond.$approver_clause." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") GROUP BY hrcon_jobs.client";
	$sres=mysql_query($sque,$db);
	$row_count3 = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = $assignmentsUsedTotal = array();
		
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
		
		$tpl_array_values = genericTemplate($srow[8]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTimeManager($cs2,$cs1,$srow[0],$db,$Time_sno,$manager_sno);
		getExpenseManager($cs2,$cs1,$srow[0],$db,$Exp_sno,$manager_sno);
		getPlacementFeeManager($cs2,$cs1,$srow[0],$db,$Charge_sno,$manager_sno);
			
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);	
	
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $srow[0]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();	
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
			
			if($srow[3]!="" && $srow[4]!="")
			{
				if($clientuser=="")
					$clientuser.=$srow[0];
				else
					$clientuser.="','".$srow[0];
					
				$ftdate=$srow[3];
				$ttdate=$srow[4];
							
				$timedate1 = getTimedate_manager($cs2,$cs1,$srow[0],$manager_sno,$db,$chkPusernames);
				$expensedate1 = getExpensedate_manager($cs2,$cs1,$srow[0],$manager_sno,$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate_manager($cs2,$cs1,$srow[0],$manager_sno,$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTimeManager($cs2,$cs1,$srow[0],$db,$Time_sno,$chkPusernames,$manager_sno);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpenseManager($cs2,$cs1,$srow[0],$db,$Exp_sno,$chkPusernames,$manager_sno);
				//$charge=getCharges($ftdate,$ttdate,$srow[0],$db);
				$charge = 0;
				$placementfee = getPlacementFee($cs2,$cs1,$srow[0],$db,$Charge_sno,$chkPusernames,$manager_sno);
				$amountdue=$time+$expense+$charge+$placementfee;
				$plscharges=$charge+$placementfee;	
							
				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($srow[8]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				$getFieldsTotal = $time + $expense + $charge + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $srow[0]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$srow[0]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$srow[0]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
				
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
				
				if($amountdue  >0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
	
				$cli=$srow[6];
				$cliid=$srow[7];
				$template_id = $srow[8];
							
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName =getDefaultTemp_Name();  
				
				if(number_format($amountdue, $decimalPref,".", "") > 0)
				{
					$assignmentsUsed = array_unique($assignmentsUsed);
					$asgnSnos = implode(",",$assignmentsUsed);
					
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[0]&asgnIdValues=$chkPusernames&selClient=$selClient&manager_sno=$manager_sno";
					if($check_status_grid==1 || $check_status_grid==2 || $check_status_grid==3)
						$grid.=",[";
					else
						$grid.="[";
					
					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".$srow[0]."|".$cservicedateto."-".$cservicedate."||".$TiExCh_Val."|".$amountdue."|".$srow[12]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[0]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($srow[13])."\",";
					$grid.="\"".gridcell($cli)."\",";
					$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
					$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
					$grid.="\"".gridcell($asgnBillAddress['state'])."\",";							
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($plscharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";

					if($selClient == "")
						$grid.="\"".gridcell(stripslashes($templateName))."\",";

					$grid.="\"".gridcell($srow[14])."\",";
					$grid.="\"".gridcell($srow[15])."\",";
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
					$j++;
					if($j==$row_count3)
					{
						$grid.="]\n";
						$check_status_grid=3;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++;
			}
		}
    }
	$grid = trim($grid,",");

  	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function displayWorkCreateInvoiceall_empassgn(&$data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient)
{
	global $assignmentsUsed,$assignmentsUsedTotal,$loc_clause,$invtype,$invlocation,$invdept,$invservicedate,$invservicedateto,$username;

    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);
	$decimalPref    = getDecimalPreference();

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$time="0";
	$expense="0";
	$charge="0";
	$amountdue="0";
	$clientuser="";
	$empusercheck="";
	$placementfee = 0;
	$check_status_grid=0;
	$row_count1=$row_count;
	
	$template_Check_arr = explode("|",$template_Check);
	$Time_sno = $template_Check_arr[0];
	$Exp_sno = $template_Check_arr[1];
	$Charge_sno = $template_Check_arr[2];	
	$TiExCh_Val = $Time_sno."^".$Exp_sno."^".$Charge_sno;
	$TiExCh_Val = str_replace("','","-",$TiExCh_Val);
	while ($result = @mysql_fetch_array($data))
	{
		$assignmentsUsed = array();
		if($result[12]!='0')
			{
				$result[7] = $result[12];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
	
		$tpl_array_values = genericTemplate($result[7]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTime_emp($cs2,$cs1,$result[2],$result[4],$db,$Time_sno);
		getExpense_emp($cs2,$cs1,$result[2],$result[4],$db,$Exp_sno);
		getPlacementFee_emp($cs2,$cs1,$result[2],$result[4],$db,$Charge_sno);
	
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);		
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $result[2]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();
			
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
				
			if($result[1]!="" && $result[0]!="")
			{
				if($clientuser=="")
					$clientuser.=$result[2];
				else
					$clientuser.="','".$result[2];
					
				if($empusercheck=="")
					$empusercheck.=$result[4];
				else
					$empusercheck.="','".$result[4];
				
				if($clientempusercheck=="")
					$clientempusercheck.=$result[2]."|".$result[4];
				else
					$clientempusercheck.="','".$result[2]."|".$result[4];
	
				$ftdate=$result[0];
				$ttdate=$result[1];
	
				
				$timedate1 = getTimedate_emp($cs2,$cs1,$result[2],$result[4],$db,$chkPusernames);
				$expensedate1 = getExpensedate_emp($cs2,$cs1,$result[2],$result[4],$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate_emp($cs2,$cs1,$result[2],$result[4],$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
	
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTime_emp($cs2,$cs1,$result[2],$result[4],$db,$Time_sno,$chkPusernames,$result[7]);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$timeRowsCount=getTimeRowsCount_emp($cs2,$cs1,$result[2],$result[4],$db);
				$expense=getExpense_emp($cs2,$cs1,$result[2],$result[4],$db,$Exp_sno,$chkPusernames);
				$expenseRowsCount=getExpenseRowsCount_emp($cs2,$cs1,$result[2],$result[4],$db);
				$timeExpenseRowCount = NULL;
				
				if($timeRowsCount || $expenseRowsCount)
				{
					$timeExpenseRowCount = 'Y';
				}
				else
				{
					$timeExpenseRowCount = 'N';		
				}
				$charge=getCharges_emp($ftdate,$ttdate,$result[2],$result[4],$db);
				$placementfee = getPlacementFee_emp($cs2,$cs1,$result[2],$result[4],$db,$Charge_sno,$chkPusernames);
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$result[7]."'";
				$pres=mysql_query($pque,$db);
				$prow=mysql_fetch_row($pres);
				
				$perDiemTot=getPerDiem_emp($cs2,$cs1,$result[2],$result[4],$db,$Time_sno,$prow[0],$chkPusernames);
				$burdenchargeamt = getBurdenChargesData($db,$result[2],$Time_sno,$cs1,$cs2,$chkPusernames,$result[4]);

				$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
				$amountdue=$time+$expense+$charge+$placementfee+$perDiemTot+$burdenchargeamt;
				$getSubToTDue = $amountdue;
				
				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($result[7]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				$getFieldsTotal = $time + $expense + $charge + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
			
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $result[2]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$result[2]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$result[2]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
				
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
				
				if($amountdue  !=0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			
				$cli=$result[5];
				$cliid=$result[6];
				$template_id = $result[7];
				 
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName =getDefaultTemp_Name();  						
				if(number_format($amountdue, $decimalPref,".", "") != 0)
				{
					$assignmentsUsed = array_unique($assignmentsUsed);
					$asgnSnos = implode(",",$assignmentsUsed);
					
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$result[2]&empuser=$result[4]&asgnIdValues=$chkPusernames&selClient=$selClient";
					if($check_status_grid==1)
						$grid.=",[";
					else
						$grid.="[";
					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($result[2])."|".$cservicedateto."-".$cservicedate."|".$result[4]."|".$TiExCh_Val."|".$amountdue."|".$result[8]."|".$getSubToTDue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[]  id=cliid[] value=".$result[2]."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($cli)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($result[3])."\",";
					$grid.="\"".gridcell($result[9])."\",";
					$grid.="\"".gridcell($cli)."\",";
					if($selClient != "")
					{
						$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
						$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
						$grid.="\"".gridcell($asgnBillAddress['state'])."\",";						
					}
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($totalcharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
					$grid.="\"".gridcell($result[10])."\",";
					$grid.="\"".gridcell($result[11])."\",";
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid.="\"".$temp_type."?".$qstr."\"";
					
					$j++;
					
					 if($j==$row_count1)
					 {
						$grid.="]\n";
						$check_status_grid=1;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++;				
				
			}
		}
	}

	$j=0;
	
	if($selClient != '0' && $selClient != "")
		$empCond = "AND par_expense.username IN ('".str_replace(",","','",$selClient)."')";

	$sque="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),expense.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid from par_expense LEFT JOIN expense ON expense.parid=par_expense.sno LEFT JOIN emp_list ON emp_list.username =par_expense.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=expense.client LEFT JOIN staffacc_list ON  staffacc_list.username=staffacc_cinfo.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=expense.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active' LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." par_expense.username=hrcon_jobs.username and hrcon_jobs.client= expense.client and expense.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and expense.client != '' and (CONCAT_WS('|',expense.client,par_expense.username)  NOT IN ('".$clientempusercheck."')) and expense.billable='bil' and par_expense.astatus IN ('Approve','Approved','ER') and expense.status IN ('Approve','Approved') AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d')>='".$cs1."' and DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$cs2."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' ".$empCond." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") group by par_expense.username,expense.client";
	$sres=mysql_query($sque,$db);
	$row_count = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
		if($srow[12]!='0')
			{
				$srow[7] = $srow[12];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
	
		$tpl_array_values = genericTemplate($srow[7]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}		
		
		///// Start for invoice split loop
		
		getTime_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Time_sno);
		getExpense_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Exp_sno);
		getPlacementFee_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Charge_sno);
	
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);		
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $result[2]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();
			
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
	
			if($srow[1]!="" && $srow[0]!="")
			{
				if($clientuser=="")
					$clientuser.=$srow[2];
				else
					$clientuser.="','".$srow[2];
					
				if($empusercheck=="")
					$empusercheck.=$srow[4];
				else
					$empusercheck.="','".$srow[4];
				
				if($clientempusercheck=="")
					$clientempusercheck.=$srow[2]."|".$srow[4];
				else
					$clientempusercheck.="','".$srow[2]."|".$srow[4];
					
				$ftdate=$srow[0];
				$ttdate=$srow[1];
	
				$timedate1 = getTimedate_emp($cs2,$cs1,$srow[2],$srow[4],$db,$chkPusernames);
				$expensedate1 = getExpensedate_emp($cs2,$cs1,$srow[2],$srow[4],$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate_emp($cs2,$cs1,$srow[2],$srow[4],$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTime_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Time_sno,$chkPusernames);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpense_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Exp_sno,$chkPusernames);
				$charge=getCharges_emp($ftdate,$ttdate,$srow[2],$srow[4],$db);
				$placementfee = getPlacementFee_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Charge_sno,$chkPusernames);
				//$perDiemTot=getPerDiem_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Time_sno);
				$amountdue=$time+$expense+$charge+$placementfee;
				$expcharges	= $charge+$placementfee;	
						
				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($srow[7]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				$getFieldsTotal = $time + $expense + $charge + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $srow[2]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
	
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
			
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$srow[2]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$srow[2]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
			
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";

				if($amountdue  >0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

				$cli=$srow[5];
				$cliid=$srow[6];
				$template_id = $srow[7];
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName =getDefaultTemp_Name();  
				if(number_format($amountdue, $decimalPref,".", "") > 0)
				{
					$assignmentsUsed = array_unique($assignmentsUsed);
					$asgnSnos = implode(",",$assignmentsUsed);
					
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[2]&empuser=$srow[4]&asgnIdValues=$chkPusernames&selClient=$selClient";
					if($check_status_grid==1 || $check_status_grid==2)
						$grid.=",[";
					else
						$grid.="[";
						
					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($srow[2])."|".$cservicedateto."-".$cservicedate."|".$srow[4]."|".$TiExCh_Val."|".$amountdue."|".$srow[8]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[2]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($srow[3])."\",";
					$grid.="\"".gridcell($srow[9])."\",";	
					$grid.="\"".gridcell($cli)."\",";
					if($selClient != ""){
						$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
						$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
						$grid.="\"".gridcell($asgnBillAddress['state'])."\",";
					}			
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($expcharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
					$grid.="\"".gridcell($srow[10])."\",";	
					$grid.="\"".gridcell($srow[11])."\",";	
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
					$grid.="\"".$temp_type."?".$qstr."\"";
					
					$j++;
					if($j==$row_count)
					{
						$grid.="]\n";
						$check_status_grid=2;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++;
				
			}
		}
	}
	$j=0;
	
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);
	
	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];
	
	if($selClient !='0' && $selClient != "")
		$empCond = "AND hrcon_jobs.username IN ('".str_replace(",","','",$selClient)."')";
	
	$sque="select hrcon_jobs.client,hrcon_jobs.pusername,SUM(hrcon_jobs.placement_fee),MIN(IF (	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) ) 	), MAX( IF (	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )),hrcon_jobs.username,emp_list.name,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billrate,hrcon_compen.diem_billrate),if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billable,hrcon_compen.diem_billable), if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_period,hrcon_compen.diem_period),staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,staffacc_cinfo.override_tempid from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username and hrcon_compen.ustatus='active') LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active' LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no LEFT JOIN department ON hrcon_compen.dept=department.sno where ".$loc_clause." hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'') AND (CONCAT_WS('|',hrcon_jobs.client,hrcon_jobs.username)  NOT IN ('".$clientempusercheck."')) AND str_to_date(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$cs1."' and str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cs2."' ".$empCond." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") GROUP BY hrcon_jobs.username,hrcon_jobs.client";
	$sres=mysql_query($sque,$db);
	$row_count3 = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
		if($srow[17]!='0')
			{
				$srow[9] = $srow[17];
			}
		$noTimeTax = $noExpenseTax = $noChargeTax = false;
	
		$tpl_array_values = genericTemplate($srow[9]);		
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
		
		///// Start for invoice split loop
		
		getTime_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Time_sno);
		getExpense_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Exp_sno);
		getPlacementFee_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Charge_sno);
	
		$assignmentsUsedTotal = array_unique($assignmentsUsedTotal);		
		$asgnSnosTotal = implode(",",$assignmentsUsedTotal);
		$custAsgnIdsTotal = $result[2]."|".$asgnSnosTotal;
		$getAlertForMultipleInvoiceTotal = getAlertForMultipleInvoice($custAsgnIdsTotal,'');
		$getArrayForInvoiceCountTotal = getIndividualAssignmentGroups($asgnSnosTotal,$getAlertForMultipleInvoiceTotal);
		
		$invoiceCountTot = count($getArrayForInvoiceCountTotal);
		
		for($invLoop = 0; $invLoop < $invoiceCountTot; $invLoop++)
		{		
			$assignmentsUsed =  array();
			
			$loopAsgns = explode("|^ASGN^|",$getArrayForInvoiceCountTotal[$invLoop]);
			$chkAsgnments = $loopAsgns[0];
			$chkPusernames = $loopAsgns[1];
	
			if($srow[3]!="" && $srow[4]!="")
			{
				if($clientuser=="")
					$clientuser.=$srow[0];
				else
					$clientuser.="','".$srow[0];
					
				if($empusercheck=="")
					$empusercheck.=$srow[5];
				else
					$empusercheck.="','".$srow[5];
				
				if($clientempusercheck=="")
					$clientempusercheck.=$srow[0]."|".$srow[5];
				else
					$clientempusercheck.="','".$srow[0]."|".$srow[5];

				$ftdate=$srow[3];
				$ttdate=$srow[4];

				$timedate1 = getTimedate_emp($cs2,$cs1,$srow[0],$srow[5],$db,$chkPusernames);
				$expensedate1 = getExpensedate_emp($cs2,$cs1,$srow[0],$srow[5],$db,$chkPusernames);
				$placementfeedate1 = getPlacementFeedate_emp($cs2,$cs1,$srow[0],$srow[5],$db,$chkPusernames);
				
				$timedate = explode("|",$timedate1);
				$expensedate = explode("|",$expensedate1);
				$placementfeedate = explode("|",$placementfeedate1);
				$dd[0] = $timedate[0];
				$dd[1] = $timedate[1];
				$dd1[0] = $expensedate[0];
				$dd1[1] = $expensedate[1];
				$dd2[0] = $placementfeedate[0];
				$dd2[1] = $placementfeedate[1];
				$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
				$MaxMinDates = explode("|",$MaxMinDates1);
				
				$sintdate=explode("-",$MaxMinDates[1]);
				$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				$sintdate=explode("-",$MaxMinDates[0]);
				$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
				
				$timeAmounts=getTime_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Time_sno,$chkPusernames);
				$time = $timeAmounts[0];
				$taxTime = $timeAmounts[1];
				$taxTimeSnos = $timeAmounts[2];
				$expense=getExpense_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Exp_sno,$chkPusernames);
				$charge=getCharges_emp($ftdate,$ttdate,$srow[0],$srow[5],$db);
				$placementfee = getPlacementFee_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Charge_sno,$chkPusernames);
				//$perDiemTot=getPerDiem_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Time_sno);
				$amountdue=$time+$expense+$charge+$placementfee;
				$plscharges=$charge+$placementfee;	
				
				//Calculating the total amount including tax based on template.
				$taxdiscForCustomer = getCustomerTaxDisc($srow[9]);
				$expForCustomer = explode("|",$taxdiscForCustomer);
				$taxForCustomer = $expForCustomer[0];
				$discForCustomer = $expForCustomer[1];
				
				$getFieldsTotal = $time + $expense + $charge + $placementfee;
				
				if($noTimeTax)
					$timeTaxTotal = 0;
				else
					$timeTaxTotal = $taxTime;
				if($noExpenseTax)
					$expenseTaxTotal = 0;
				else
					$expenseTaxTotal = $expense;
					
				if($noChargeTax)
				{
					$chargeTaxTotal = 0;
					$placementfeeTaxTotal = 0;
				}
				else
				{
					$chargeTaxTotal = $charge;
					$placementfeeTaxTotal = $placementfee;
				}
				
				$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
				
				$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
				$todaydate=date("Y-m-d",$thisday);
				
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				$custAsgnIds = $srow[0]."|".$asgnSnos;
				$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
				$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
				$invoiceCount = count($getArrayForInvoiceCount);
				$asgnBillAddress = array();
				$asgnBillAddress = getAsgnBillAddr($asgnSnos,$db);
				
				if($getAlertForMultipleInvoice == "Split"){
					$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
				}else{
					$discountTaxFlatChk = "";				
				}		
				
				$tque = "SELECT rp.amount, rp.amountmode
						FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE cdt.customer_sno = '".$srow[0]."' 
						AND cdt.tax_discount_id = ct.taxid 
						AND ct.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = ct.sno
						AND rp.parenttype = 'TAX'
						AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$tres=mysql_query($tque,$db);
				
				$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
						FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE cdt.customer_sno = '".$srow[0]."' 
						AND cdt.tax_discount_id = cd.discountid 
						AND cd.status = 'active'
						AND cdt.status = 'active' 
						AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' 
						AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
				$dres=mysql_query($dque,$db);
			
				$totalTaxAmount = "0";
				$totalDiscAmount = "0";
				
				$btDiscTotal = 0.00;	// Get before tax discount amount sum...
				if($discForCustomer=="Y")
				{
					while($drow=mysql_fetch_row($dres))
					{
						if($drow[2] == "at")
						{
							if($drow[1] == "PER")
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							else
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
						}
						else
						{
							if($drow[1] == "PER")
							{
								$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
								$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');
							}
							else
							{
								$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');						
								$totalDiscAmount += number_format($drow[0],$decimalPref,'.','');
							}
						}
					}
				}
				else
					$totalDiscAmount = "0";
				
				$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
				
				if($taxForCustomer=="Y")
				{
					while($trow=mysql_fetch_row($tres))
					{
						if($trow[1] == "PER")
							$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');
						else
						{
							if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
								$totalTaxAmount += number_format($trow[0],$decimalPref,'.','');
						}
					}
				}
				else
					$totalTaxAmount = "0";
				
				if($amountdue  >0)
					$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

				$cli=$srow[7];
				$cliid=$srow[8];
				$template_id = $srow[9];
				if($template_id !='0' )
					$templateName = getTemplateName($template_id);
				else				
					$templateName =getDefaultTemp_Name();  
				if(number_format($amountdue, $decimalPref,".", "") > 0)
				{
					$assignmentsUsed = array_unique($assignmentsUsed);
					$asgnSnos = implode(",",$assignmentsUsed);
					
					$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[0]&empuser=$srow[5]&asgnIdValues=$chkPusernames&selClient=$selClient";
					if($check_status_grid==1 || $check_status_grid==2 || $check_status_grid==3)
						$grid.=",[";
					else
						$grid.="[";
					
					$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($srow[0])."|".$cservicedateto."-".$cservicedate."|".$srow[5]."|".$TiExCh_Val."|".$amountdue."|".$srow[13]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[0]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
					$grid.="\"".gridcell($srow[6])."\",";
					$grid.="\"".gridcell($srow[14])."\",";	
					$grid.="\"".gridcell($cli)."\",";	
					if($selClient != ""){
						$grid.="\"".gridcell(trim($asgnBillAddress['address'],','))."\",";
						$grid.="\"".gridcell($asgnBillAddress['city'])."\",";
						$grid.="\"".gridcell($asgnBillAddress['state'])."\",";
					}	
					$grid.="\"".$cservicedateto."-".$cservicedate."\",";
					$grid.="\"".number_format($time, 2,".", "")."\",";
					$grid.="\"".number_format($plscharges, 2,".", "")."\",";
					$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
					$grid.="\"".number_format($amountdue,$decimalPref,".", "")."\",";
					$grid.="\"".gridcell($srow[15])."\",";	
					$grid.="\"".gridcell($srow[16])."\",";	
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
					$grid.="\"".$temp_type."?".$qstr."\"";
					
					$j++;
					if($j==$row_count3)
					{
						$grid.="]\n";
						$check_status_grid=3;
					}
					else
					{
						$grid.="],";
						$check_status_grid=0;
					}
				}
				else
					$j++;
			}
		}
	}

	$grid = trim($grid,",");

	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}

function displayCreateInvoiceForPONumber($s_date, $e_date, $cdate, $duedate, $template_Check) {

	global $db, $loc_clause, $assignmentsUsed, $assignmentsUsedTotal, $invtype, $invlocation, $invdept, $invservicedate, $invservicedateto,$username;
    $decimalPref    = getDecimalPreference();

    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	// FOR TIMESHEETS
	$tim_query	= "SELECT
					FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'), FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),
					timesheet.client, hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_list.sno, staffacc_cinfo.templateid, staffacc_cinfo.tax, staffacc_cinfo.sno,
					CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, timesheet.assid, hrcon_jobs.sno,staffacc_cinfo.override_tempid,'' as charge
				FROM
					par_timesheet, timesheet_hours AS timesheet
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client
					LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username
					LEFT JOIN emp_list ON emp_list.username=timesheet.username
					LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid
					LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active'
					LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
				WHERE
					".$loc_clause."
					timesheet.username = hrcon_jobs.username AND hrcon_jobs.client = timesheet.client
					AND timesheet.client != '' AND hrcon_jobs.client != '0' AND hrcon_jobs.po_num != '' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel')
					AND timesheet.parid = par_timesheet.sno AND timesheet.type != 'EARN' AND timesheet.billable = 'Yes'
					AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved'
					AND ".tzRetQueryStringDate('par_timesheet.sdate', 'YMDDate', '-')." >= '".$s_date."'
					AND ".tzRetQueryStringDate('par_timesheet.edate', 'YMDDate', '-')." <= '".$e_date."'
					AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.")
				GROUP BY
					CAST(hrcon_jobs.po_num AS BINARY), timesheet.client
				ORDER BY
					timesheet.sdate ASC";

	$tim_res	= mysql_query($tim_query, $db);
	$row_count	= @mysql_num_rows($tim_res);
	$column_count	= @mysql_num_fields($tim_res);

	$grid	= "<script>\n";
	$grid	.= "var actcol = [";

	for ($i = 0; $i < $column_count; $i++) {

		if ($i == $column_count - 1) {

			$grid	.= "\""."\"";

		} else {

			$grid	.= "\""."\",";
		}
	}

	$grid	.= "];\n";
	$grid	.= "var actdata = [\n";

	$j	= 0;

	$time		= 0;
	$expense	= 0;
	$charge		= 0;
	$amountdue	= 0;
	$placementfee		= 0;
	$check_status_grid	= 0;

	$clientuser	= '';
	$empusercheck	= '';
	$po_number_list	= '';

	$template_Check_arr	= explode('|', $template_Check);

	$Time_sno	= $template_Check_arr[0];
	$Exp_sno	= $template_Check_arr[1];
	$Charge_sno	= $template_Check_arr[2];

	$TiExCh_Val	= $Time_sno.'^'.$Exp_sno.'^'.$Charge_sno;
	$TiExCh_Val	= str_replace("','", '-', $TiExCh_Val);

	while ($result = @mysql_fetch_array($tim_res)) {
		if($result[14]!='0')
			{
				$result[7] = $result[14];
			}

		$start_date	= $result[0];
		$end_date	= $result[1];
		$client_id	= $result[2];
		$po_number	= $result[3];
		$user_name	= $result[4];
		$customer_name	= $result[5];
		$customer_id	= $result[6];
		$template_id	= $result[7];
		$tax_value	= $result[8];
		$cinfo_sno	= $result[9];
		$location	= $result[10];
		$department	= $result[11];
		$assignment_id	= $result[12];
		$hrcon_sno	= $result[13];

		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($template_id);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax	= true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			if (empty($clientuser)) {

				$clientuser	.= $client_id;

			} else {

				$clientuser	.= "','".$client_id;
			}

			if (empty($empusercheck)) {

				$empusercheck	.= $user_name;

			} else {

				$empusercheck	.= "','".$user_name;
			}

			if (empty($po_number_list)) {

				if (!empty($po_number)) {

					$po_number_list	.= "'". $po_number ."'";
				}

			} else {

				if (!empty($po_number)) {

					$po_number_list	.= ", '". $po_number ."'";
				}
			}

			$timedate_emp		= getTimedate($e_date, $s_date, $client_id, $db);
			$expensedate_emp	= getExpensedate($e_date, $s_date, $client_id, $db);
			$placementfeedate_emp	= getPlacementFeedate($e_date, $s_date, $client_id, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];

			$timeAmounts	= getTime($e_date, $s_date, $client_id, $db, $Time_sno, '', $po_number,$template_id);
			$expense	= getExpense($e_date, $s_date, $client_id, $db, $Exp_sno, '', $po_number);

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];

			$timeRowsCount		= getTimeRowsCount($e_date, $s_date, $client_id, $db);
			$expenseRowsCount	= getExpenseRowsCount($e_date, $s_date, $client_id, $db);

			$timeExpenseRowCount	= NULL;

			if ($timeRowsCount || $expenseRowsCount) {

				$timeExpenseRowCount	= 'Y';

			} else {

				$timeExpenseRowCount	= 'N';
			}

			$charge		= getCharges($start_date, $end_date, $client_id, $db);
			$placementfee	= getPlacementFee($e_date, $s_date, $client_id, $db, $Charge_sno, '', $po_number);

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$pque	= "SELECT 
							inv_col_perdiem_chk 
						FROM 
							IT_Columns 
							LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno = Invoice_Template.invtmp_columns
						WHERE
							Invoice_Template.invtmp_sno = '".$template_id."'";

			$pres	= mysql_query($pque, $db);
			$prow	= mysql_fetch_row($pres);

			$perDiemTot	= getPerDiem($e_date, $s_date, $client_id, $db, $Time_sno, $prow[0]);
			$burdenchargeamt = getBurdenChargesData($db, $client_id, $Time_sno, $s_date, $e_date);

			$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$amountdue	= $time + $expense + $charge + $placementfee + $perDiemTot + $burdenchargeamt;
			$getSubToTDue	= $amountdue;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(",",$assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;	// Get before tax discount amount sum...

			if ($discForCustomer == 'Y') {

				while ($drow = mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],$decimalPref,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');
							$totalDiscAmount	+= number_format($drow[0],$decimalPref,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],$decimalPref,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue,$decimalPref, '.', '') > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno";

				$grid	.= "[";
				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$result[8].'|'.$getSubToTDue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos.'|'.$po_number."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[]  id=cliid[] value=".$client_id."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($customer_name)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($po_number)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$invoiceCount."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .= "\"".number_format($totalcharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";

				if ($invoiceCount > 1) {

					$grid	.= "\""."invoiceall.php?val=redirect&invtype=PONumberassgn&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$result[2]&asmt_id=$assignment_id&jobsno=$hrcon_sno&ponum=$po_number\"";

				} else {
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid	.= "\"".$temp_type."?".$qstr."\"";
					
				}

				$j++;

				if ($j == $row_count) {

					$grid	.= "]\n";
					$check_status_grid	= 1;

				} else {

					$grid	.= "],";
					$check_status_grid	= 0;
				}

			} else {

				$j++;
			}
		}
	}

	$j	= 0;

	// FOR EXPENSES
	$exp_query	= "SELECT 
					FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'), FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),
					expense.client, hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_list.sno, staffacc_cinfo.templateid,
					staffacc_cinfo.tax, staffacc_cinfo.sno, CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, expense.assid, hrcon_jobs.sno,staffacc_cinfo.override_tempid
				FROM
					par_expense
					LEFT JOIN expense ON expense.parid = par_expense.sno
					LEFT JOIN emp_list ON emp_list.username = par_expense.username
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = expense.client
					LEFT JOIN staffacc_list ON staffacc_list.username = staffacc_cinfo.username
					LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername = expense.assid
					LEFT JOIN hrcon_compen ON hrcon_compen.username = emp_list.username AND hrcon_compen.ustatus='active'
					LEFT JOIN contact_manage ON hrcon_compen.location = contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
				WHERE
					".$loc_clause."
					par_expense.username = hrcon_jobs.username AND hrcon_jobs.client = expense.client
					AND expense.client != '' AND expense.status IN ('Approve', 'Approved')
					AND hrcon_jobs.client != '0' AND hrcon_jobs.po_num != '' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel')
					AND hrcon_jobs.po_num NOT IN (".$po_number_list.") AND expense.billable = 'bil'
					AND par_expense.astatus IN ('Approve','Approved','ER')
					AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d') >= '".$s_date."'
					AND DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$e_date."' AND emp_list.lstatus != 'DA'
					AND emp_list.lstatus != 'INACTIVE' AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.")
				GROUP BY
					CAST(hrcon_jobs.po_num AS BINARY), expense.client
				ORDER BY 
					expense.edate ASC";

	$exp_res	= mysql_query($exp_query, $db);
	$row_count	= @mysql_num_rows($exp_res);

	while ($srow = mysql_fetch_row($exp_res)) {
		if($srow[14]!='0')
			{
				$srow[7] = $srow[14];
			}

		$start_date	= $srow[0];
		$end_date	= $srow[1];
		$client_id	= $srow[2];
		$po_number	= $srow[3];
		$user_name	= $srow[4];
		$customer_name	= $srow[5];
		$customer_id	= $srow[6];
		$template_id	= $srow[7];
		$tax_value	= $srow[8];
		$cinfo_sno	= $srow[9];
		$location	= $srow[10];
		$department	= $srow[11];
		$assignment_id	= $srow[12];
		$hrcon_sno	= $srow[13];

		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($template_id);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax	= true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			if (empty($clientuser)) {

				$clientuser	.= $client_id;

			} else {

				$clientuser	.= "','".$client_id;
			}

			if (empty($empusercheck)) {

				$empusercheck	.= $user_name;

			} else {

				$empusercheck	.= "','".$user_name;
			}

			if (empty($po_number_list)) {

				if (!empty($po_number)) {

					$po_number_list	.= "'". $po_number ."'";
				}

			} else {

				if (!empty($po_number)) {

					$po_number_list	.= ", '". $po_number ."'";
				}
			}

			$timedate_emp		= getTimedate($e_date, $s_date, $client_id, $db);
			$expensedate_emp	= getExpensedate($e_date, $s_date, $client_id, $db);
			$placementfeedate_emp	= getPlacementFeedate($e_date, $s_date, $client_id, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];

			$timeAmounts	= getTime($e_date, $s_date, $client_id, $db, $Time_sno, '', $po_number);
			$expense	= getExpense($e_date, $s_date, $client_id, $db, $Exp_sno, '', $po_number);
			$charge		= getCharges($start_date, $end_date, $client_id, $db);
			$placementfee	= getPlacementFee($e_date, $s_date, $client_id, $db, $Charge_sno, '', $po_number);

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];
			$amountdue		= $time + $expense + $charge + $placementfee;
			$expcharges	= $charge+$placementfee;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(',', $assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;	// Get before tax discount amount sum...

			if ($discForCustomer == 'Y') {

				while ($drow=mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');
							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],2,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue, $decimalPref, '.', '') > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno";

				if ($check_status_grid == 1) {

					$grid	.= ",[";

				} else {

					$grid	.= "[";
				}

				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$tax_value.'|'.$amountdue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos.'|'.$po_number."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$client_id."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($po_number)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$invoiceCount."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .= "\"".number_format($expcharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";

				if ($invoiceCount > 1) {

					$grid	.= "\""."invoiceall.php?val=redirect&invtype=PONumberassgn&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$result[2]&asmt_id=$assignment_id&jobsno=$hrcon_sno&ponum=$po_number\"";

				} else {
					$temp_type = getDefaultTemp_Type($template_id,'new');
					$grid	.= "\"".$temp_type."?".$qstr."\"";
					
				}

				$j++;

				if ($j == $row_count) {

					$grid	.= "]\n";
					$check_status_grid	= 2;

				} else {

					$grid	.= "],";
					$check_status_grid	= 0;
				}

			} else {

				$j++;
			}
		}
	}

	$j	= 0;

	$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate	= date("m-d-Y",$thisday);

	$quedirect	= "SELECT GROUP_CONCAT(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect	= mysql_query($quedirect,$db);
	$rowdirect	= mysql_fetch_row($resdirect);
	$snodirect	= $rowdirect[0];

	// FOR CHARGES
	$chg_query	= "SELECT 
					hrcon_jobs.client, MIN(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', NOW(), STR_TO_DATE(hrcon_jobs.s_date, '%m-%d-%Y'))),
					MAX(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', NOW(), STR_TO_DATE(hrcon_jobs.s_date, '%m-%d-%Y'))),
					hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_cinfo.templateid, staffacc_cinfo.sno,
					CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, hrcon_jobs.pusername, hrcon_jobs.sno,staffacc_cinfo.override_tempid
				FROM
					hrcon_jobs
					LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username
					LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username AND hrcon_compen.ustatus='active')
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client
					LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username
					LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
				WHERE
					".$loc_clause."
					hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL)
					AND hrcon_jobs.po_num != '' AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'')
					AND hrcon_jobs.po_num NOT IN (".$po_number_list.")
					AND STR_TO_DATE(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y') >= '".$s_date."'
					AND STR_TO_DATE(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' ) <= '".$e_date."' AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.")
				GROUP BY 
					CAST(hrcon_jobs.po_num AS BINARY), hrcon_jobs.client";

	$chg_res	= mysql_query($chg_query, $db);
	$chg_count	= @mysql_num_rows($chg_res);

	while ($srow = mysql_fetch_row($chg_res)) {
		if($srow[12]!='0')
			{
				$srow[6] = $srow[12];
			}

		$client_id	= $srow[0];
		$start_date	= $srow[1];
		$end_date	= $srow[2];
		$po_number	= $srow[3];
		$user_name	= $srow[4];
		$customer_name	= $srow[5];
		$template_id	= $srow[6];
		$cinfo_sno	= $srow[7];
		$location	= $srow[8];
		$department	= $srow[9];
		$assignment_id	= $srow[10];
		$hrcon_sno	= $srow[11];

		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($cinfo_sno);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax = true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			$timedate_emp		= getTimedate($e_date, $s_date, $client_id, $db);
			$expensedate_emp	= getExpensedate($e_date, $s_date, $client_id, $db);
			$placementfeedate_emp	= getPlacementFeedate($e_date, $s_date, $client_id, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];

			$timeAmounts	= getTime($e_date, $s_date, $client_id, $db, $Time_sno, '', $po_number);
			$expense	= getExpense($e_date, $s_date, $client_id, $db, $Exp_sno, '', $po_number);

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];

			$charge		= getCharges($start_date, $end_date, $client_id, $db);
			$placementfee	= getPlacementFee($e_date, $s_date, $client_id, $db, $Charge_sno, '', $po_number);
			$amountdue	= $time + $expense + $charge + $placementfee;
			$plscharges=$charge+$placementfee;	

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(',', $assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;

			if ($discForCustomer == 'Y') {

				while ($drow = mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');
							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],2,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue, $decimalPref,".", "") > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno";

				if ($check_status_grid == 1 || $check_status_grid == 2) {

					$grid	.= ",[";

				} else {

					$grid	.= "[";
				}

				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$srow[13].'|'.$amountdue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos.'|'.$po_number."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$client_id."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($po_number)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$invoiceCount."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .= "\"".number_format($plscharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";

				if ($invoiceCount > 1) {

					$grid	.= "\""."invoiceall.php?val=redirect&invtype=PONumberassgn&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$result[2]&asmt_id=$assignment_id&jobsno=$hrcon_sno&ponum=$po_number\"";

				} else {
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
					$grid	.= "\"".$temp_type."?".$qstr."\"";
					
				}

				$check_status_grid	= 3;

				$j++;

				if ($j == $chg_count) {

					$grid	.= "]\n";

				} else {

					$grid	.= "],";
				}

			} else {

				$j++;
			}
		}
	}

	$grid	= trim($grid, ',');
	$grid	.= "];\n";
	$grid	.= "</script>\n";

	return $grid;
}


function displayCreateInvoiceForPONumber_assgn($s_date, $e_date, $cdate, $duedate, $template_Check) {

	global $db, $loc_clause, $assignmentsUsed, $assignmentsUsedTotal, $invtype, $invlocation, $invdept, $invservicedate, $invservicedateto,$username;
	$decimalPref    = getDecimalPreference();
    
    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");
	// FOR TIMESHEETS
	$tim_query	= "SELECT
					FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'), FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),
					timesheet.client, hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_list.sno, staffacc_cinfo.templateid, staffacc_cinfo.tax, staffacc_cinfo.sno,
					CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, GROUP_CONCAT(timesheet.assid), hrcon_jobs.sno,staffacc_cinfo.override_tempid,'' as charge
				FROM
					par_timesheet, timesheet_hours AS timesheet
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client
					LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username
					LEFT JOIN emp_list ON emp_list.username=timesheet.username
					LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid
					LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active'
					LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
				WHERE
					".$loc_clause."
					timesheet.username = hrcon_jobs.username AND hrcon_jobs.client = timesheet.client
					AND timesheet.client != '' AND hrcon_jobs.client != '0' AND hrcon_jobs.po_num != '' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel')
					AND timesheet.parid = par_timesheet.sno AND timesheet.type != 'EARN' AND timesheet.billable = 'Yes'
					AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved'
					AND ".tzRetQueryStringDate('par_timesheet.sdate', 'YMDDate', '-')." >= '".$s_date."'
					AND ".tzRetQueryStringDate('par_timesheet.edate', 'YMDDate', '-')." <= '".$e_date."'
					AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.")
				GROUP BY
					CAST(hrcon_jobs.po_num AS BINARY), timesheet.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact,hrcon_jobs.attention
				ORDER BY
					timesheet.sdate ASC";
	$tim_res	= mysql_query($tim_query, $db);
	$row_count	= @mysql_num_rows($tim_res);
	$column_count	= @mysql_num_fields($tim_res);

	$grid	= "<script>\n";
	$grid	.= "var actcol = [";

	for ($i = 0; $i < $column_count; $i++) {

		if ($i == $column_count - 1) {

			$grid	.= "\""."\"";

		} else {

			$grid	.= "\""."\",";
		}
	}

	$grid	.= "];\n";
	$grid	.= "var actdata = [\n";

	$j	= 0;

	$time		= 0;
	$expense	= 0;
	$charge		= 0;
	$amountdue	= 0;
	$placementfee		= 0;
	$check_status_grid	= 0;

	$clientuser	= '';
	$empusercheck	= '';
	$po_number_list	= '';

	$template_Check_arr	= explode('|', $template_Check);

	$Time_sno	= $template_Check_arr[0];
	$Exp_sno	= $template_Check_arr[1];
	$Charge_sno	= $template_Check_arr[2];

	$TiExCh_Val	= $Time_sno.'^'.$Exp_sno.'^'.$Charge_sno;
	$TiExCh_Val	= str_replace("','", '-', $TiExCh_Val);

	while ($result = @mysql_fetch_array($tim_res)) {
		if($result[14]!='0')
			{
				$result[7] = $result[14];
			}
		$start_date	= $result[0];
		$end_date	= $result[1];
		$client_id	= $result[2];
		$po_number	= $result[3];
		$user_name	= $result[4];
		$customer_name	= $result[5];
		$customer_id	= $result[6];
		$template_id	= $result[7];
		$tax_value	= $result[8];
		$cinfo_sno	= $result[9];
		$location	= $result[10];
		$department	= $result[11];
		$assignment_id	= $result[12];
		$hrcon_sno	= $result[13];

		$assignmentsUsed	= array();
		$assignmentsUsedTotal = array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($template_id);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax	= true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			if (empty($clientuser)) {

				$clientuser	.= $client_id;

			} else {

				$clientuser	.= "','".$client_id;
			}

			if (empty($empusercheck)) {

				$empusercheck	.= $user_name;

			} else {

				$empusercheck	.= "','".$user_name;
			}

			if (empty($po_number_list)) {

				if (!empty($po_number)) {

					$po_number_list	.= "'". $po_number ."'";
				}

			} else {

				if (!empty($po_number)) {

					$po_number_list	.= ", '". $po_number ."'";
				}
			}
			

			$timedate_emp		= getTimedate($e_date, $s_date, $client_id, $db);
			$expensedate_emp	= getExpensedate($e_date, $s_date, $client_id, $db);
			$placementfeedate_emp	= getPlacementFeedate($e_date, $s_date, $client_id, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];
            $chkPusernames  = $assignment_id;
			$timeAmounts	= getTime($e_date, $s_date, $client_id, $db, $Time_sno,$chkPusernames,'',$template_id);
			$expense	= getExpense($e_date, $s_date, $client_id, $db, $Exp_sno,$chkPusernames,'');

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];

			$timeRowsCount		= getTimeRowsCount($e_date, $s_date, $client_id, $db);
			$expenseRowsCount	= getExpenseRowsCount($e_date, $s_date, $client_id, $db);

			$timeExpenseRowCount	= NULL;

			if ($timeRowsCount || $expenseRowsCount) {

				$timeExpenseRowCount	= 'Y';

			} else {

				$timeExpenseRowCount	= 'N';
			}

			$charge		= getCharges($start_date, $end_date, $client_id, $db);
			$placementfee	= getPlacementFee($e_date, $s_date, $client_id, $db, $Charge_sno, '', $po_number);

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$pque	= "SELECT 
							inv_col_perdiem_chk 
						FROM 
							IT_Columns 
							LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno = Invoice_Template.invtmp_columns
						WHERE
							Invoice_Template.invtmp_sno = '".$template_id."'";

			$pres	= mysql_query($pque, $db);
			$prow	= mysql_fetch_row($pres);

			$perDiemTot	= getPerDiem($e_date, $s_date, $client_id, $db, $Time_sno, $prow[0]);
			$burdenchargeamt = getBurdenChargesData($db, $client_id, $Time_sno, $s_date, $e_date);

			$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$amountdue	= $time + $expense + $charge + $placementfee + $perDiemTot + $burdenchargeamt;
			$getSubToTDue	= $amountdue;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(",",$assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;	// Get before tax discount amount sum...

			if ($discForCustomer == 'Y') {

				while ($drow = mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],$decimalPref,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');
							$totalDiscAmount	+= number_format($drow[0],$decimalPref,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],$decimalPref,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue,$decimalPref, '.', '') > 0) {

				
				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$result[2]&asgnIdValues=$chkPusernames&selClient=$selClient";

				if($check_status_grid==1)
						$grid.=",[";
					else
						$grid.="[";
					
				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$result[8].'|'.$getSubToTDue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[]  id=cliid[] value=".$client_id."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($customer_name)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($po_number)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .= "\"".number_format($totalcharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";
                $invlocation =  $location;
				$invdept = $department;
				
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid	.= "\"".$temp_type."?".$qstr."\"";
					
				

				$j++;

				if ($j == $row_count) {

					$grid	.= "]\n";
					$check_status_grid	= 1;

				} else {

					$grid	.= "],";
					$check_status_grid	= 0;
				}

			} else {

				$j++;
			}
		}
	}

	$j	= 0;

	// FOR EXPENSES
	$exp_query	= "SELECT 
					FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'), FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),
					expense.client, hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_list.sno, staffacc_cinfo.templateid,
					staffacc_cinfo.tax, staffacc_cinfo.sno, CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, GROUP_CONCAT(expense.assid), hrcon_jobs.sno,staffacc_cinfo.override_tempid
				FROM
					par_expense
					LEFT JOIN expense ON expense.parid = par_expense.sno
					LEFT JOIN emp_list ON emp_list.username = par_expense.username
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = expense.client
					LEFT JOIN staffacc_list ON staffacc_list.username = staffacc_cinfo.username
					LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername = expense.assid
					LEFT JOIN hrcon_compen ON hrcon_compen.username = emp_list.username AND hrcon_compen.ustatus='active'
					LEFT JOIN contact_manage ON hrcon_compen.location = contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
				WHERE
					".$loc_clause."
					par_expense.username = hrcon_jobs.username AND hrcon_jobs.client = expense.client
					AND expense.client != '' AND expense.status IN ('Approve', 'Approved')
					AND hrcon_jobs.client != '0' AND hrcon_jobs.po_num != '' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel')
					AND hrcon_jobs.po_num NOT IN (".$po_number_list.") AND expense.billable = 'bil'
					AND par_expense.astatus IN ('Approve','Approved','ER')
					AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d') >= '".$s_date."'
					AND DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$e_date."' AND emp_list.lstatus != 'DA'
					AND emp_list.lstatus != 'INACTIVE'
				GROUP BY
					CAST(hrcon_jobs.po_num AS BINARY), expense.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact,hrcon_jobs.attention
				ORDER BY 
					expense.edate ASC";

	$exp_res	= mysql_query($exp_query, $db);
	$row_count	= @mysql_num_rows($exp_res);

	while ($srow = mysql_fetch_row($exp_res)) {
		if($srow[14]!='0')
			{
				$srow[7] = $srow[14];
			}

		$start_date	= $srow[0];
		$end_date	= $srow[1];
		$client_id	= $srow[2];
		$po_number	= $srow[3];
		$user_name	= $srow[4];
		$customer_name	= $srow[5];
		$customer_id	= $srow[6];
		$template_id	= $srow[7];
		$tax_value	= $srow[8];
		$cinfo_sno	= $srow[9];
		$location	= $srow[10];
		$department	= $srow[11];
		$assignment_id	= $srow[12];
		$hrcon_sno	= $srow[13];

		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($template_id);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax	= true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			if (empty($clientuser)) {

				$clientuser	.= $client_id;

			} else {

				$clientuser	.= "','".$client_id;
			}

			if (empty($empusercheck)) {

				$empusercheck	.= $user_name;

			} else {

				$empusercheck	.= "','".$user_name;
			}

			if (empty($po_number_list)) {

				if (!empty($po_number)) {

					$po_number_list	.= "'". $po_number ."'";
				}

			} else {

				if (!empty($po_number)) {

					$po_number_list	.= ", '". $po_number ."'";
				}
			}

			$timedate_emp		= getTimedate($e_date, $s_date, $client_id, $db);
			$expensedate_emp	= getExpensedate($e_date, $s_date, $client_id, $db);
			$placementfeedate_emp	= getPlacementFeedate($e_date, $s_date, $client_id, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];
			$chkPusernames  = $assignment_id;
			$timeAmounts	= getTime($e_date, $s_date, $client_id, $db, $Time_sno,$chkPusernames,'', $template_id);
			$expense	= getExpense($e_date, $s_date, $client_id, $db, $Exp_sno,$chkPusernames,'');
			$charge		= getCharges($start_date, $end_date, $client_id, $db);
			$placementfee	= getPlacementFee($e_date, $s_date, $client_id, $db, $Charge_sno, '', $po_number);

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];
			$amountdue		= $time + $expense + $charge + $placementfee;
			$expcharges	= $charge+$placementfee;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(',', $assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;	// Get before tax discount amount sum...

			if ($discForCustomer == 'Y') {

				while ($drow=mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');
							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],2,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue, $decimalPref, '.', '') > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno";

				if ($check_status_grid == 1) {

					$grid	.= ",[";

				} else {

					$grid	.= "[";
				}

				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$tax_value.'|'.$amountdue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$client_id."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($po_number)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .= "\"".number_format($expcharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";
				$invlocation =  $location;
				$invdept = $department;
				
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid	.= "\"".$temp_type."?".$qstr."\"";

				$j++;

				if ($j == $row_count) {

					$grid	.= "]\n";
					$check_status_grid	= 2;

				} else {

					$grid	.= "],";
					$check_status_grid	= 0;
				}

			} else {

				$j++;
			}
		}
	}

	$j	= 0;

	$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate	= date("m-d-Y",$thisday);

	$quedirect	= "SELECT GROUP_CONCAT(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect	= mysql_query($quedirect,$db);
	$rowdirect	= mysql_fetch_row($resdirect);
	$snodirect	= $rowdirect[0];

	// FOR CHARGES
	$chg_query	= "SELECT 
					hrcon_jobs.client, MIN(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', NOW(), STR_TO_DATE(hrcon_jobs.s_date, '%m-%d-%Y'))),
					MAX(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', NOW(), STR_TO_DATE(hrcon_jobs.s_date, '%m-%d-%Y'))),
					hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_cinfo.templateid, staffacc_cinfo.sno,
					CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, GROUP_CONCAT(hrcon_jobs.pusername), hrcon_jobs.sno,staffacc_cinfo.override_tempid
				FROM
					hrcon_jobs
					LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username
					LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username AND hrcon_compen.ustatus='active')
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client
					LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username
					LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
				WHERE
					".$loc_clause."
					hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL)
					AND hrcon_jobs.po_num != '' AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'')
					AND hrcon_jobs.po_num NOT IN (".$po_number_list.")
					AND STR_TO_DATE(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y') >= '".$s_date."'
					AND STR_TO_DATE(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' ) <= '".$e_date."'
				GROUP BY 
					CAST(hrcon_jobs.po_num AS BINARY), hrcon_jobs.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact,hrcon_jobs.attention";

	$chg_res	= mysql_query($chg_query, $db);
	$chg_count	= @mysql_num_rows($chg_res);

	while ($srow = mysql_fetch_row($chg_res)) {
        if($srow[12]!='0')
			{
				$srow[6] = $srow[12];
			}
		$client_id	= $srow[0];
		$start_date	= $srow[1];
		$end_date	= $srow[2];
		$po_number	= $srow[3];
		$user_name	= $srow[4];
		$customer_name	= $srow[5];
		$template_id	= $srow[6];
		$cinfo_sno	= $srow[7];
		$location	= $srow[8];
		$department	= $srow[9];
		$assignment_id	= $srow[10];
		$hrcon_sno	= $srow[11];

		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($cinfo_sno);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax = true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			$timedate_emp		= getTimedate($e_date, $s_date, $client_id, $db);
			$expensedate_emp	= getExpensedate($e_date, $s_date, $client_id, $db);
			$placementfeedate_emp	= getPlacementFeedate($e_date, $s_date, $client_id, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];
			$chkPusernames  = $assignment_id;
			$timeAmounts	= getTime($e_date, $s_date, $client_id, $db, $Time_sno,$chkPusernames, '', $template_id);
			$expense	= getExpense($e_date, $s_date, $client_id, $db, $Exp_sno,$chkPusernames, '');

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];

			$charge		= getCharges($start_date, $end_date, $client_id, $db);
			$placementfee	= getPlacementFee($e_date, $s_date, $client_id, $db, $Charge_sno, '', $po_number);
			$amountdue	= $time + $expense + $charge + $placementfee;
			$plscharges=$charge+$placementfee;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(',', $assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;

			if ($discForCustomer == 'Y') {

				while ($drow = mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');
							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],2,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue, $decimalPref,".", "") > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno";

				if ($check_status_grid == 1 || $check_status_grid == 2) {

					$grid	.= ",[";

				} else {

					$grid	.= "[";
				}

				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$srow[13].'|'.$amountdue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$client_id."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($po_number)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .= "\"".number_format($plscharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";

				$invlocation =  $location;
				$invdept = $department;
				
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid	.= "\"".$temp_type."?".$qstr."\"";

				$check_status_grid	= 3;

				$j++;

				if ($j == $chg_count) {

					$grid	.= "]\n";

				} else {

					$grid	.= "],";
				}

			} else {

				$j++;
			}
		}
	}

	$grid	= trim($grid, ',');
	$grid	.= "];\n";
	$grid	.= "</script>\n";

	return $grid;
}
//Function for Billing Contact-Grid
function displayWorkCreateInvoiceall_billingContact(&$data,$db,$cs1,$cs2,$cdate,$duedate,$servicedate,$servicedateto,$template_Check,$selClient,$selAddr)
{
	global $assignmentsUsed,$assignmentsUsedTotal,$loc_clause,$invtype,$invlocation,$invdept,$invservicedate,$invservicedateto,$username;

    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	$grid="<"."script".">\n";
	$row_count = @mysql_num_rows($data);
	$column_count = @mysql_num_fields($data);
	$decimalPref    = getDecimalPreference();

	$grid.="var actcol = [";
	for($i=0;$i<$column_count;$i++)
	{
		if($i==$column_count-1)
			$grid.="\""."\"";
		else
			$grid.="\""."\",";
	}
	$grid.="];\n";

	$j=0;
	$grid.="var actdata = [\n";
	$time="0";
	$expense="0";
	$charge="0";
	$amountdue="0";
	$clientuser="";
	$empusercheck="";
	$billcontusercheck="";
	$placementfee = 0;
	$check_status_grid=0;
	$row_count1=$row_count;

	$template_Check_arr = explode("|",$template_Check);
	$Time_sno = $template_Check_arr[0];
	$Exp_sno = $template_Check_arr[1];
	$Charge_sno = $template_Check_arr[2];	
	$TiExCh_Val = $Time_sno."^".$Exp_sno."^".$Charge_sno;
	$TiExCh_Val = str_replace("','","-",$TiExCh_Val);

	while ($result = @mysql_fetch_array($data))
	{
		$assignmentsUsed = array();
		if($result[15]!='0')
			{
				$result[7] = $result[15];
			}

		$noTimeTax = $noExpenseTax = $noChargeTax = false;

		$temp_type = getDefaultTemp_Type($result[7]);
		if($temp_type == '3'){
			$tpl_array_values = custom_genericTemplate($result[7]);	
		}else{
			$tpl_array_values = genericTemplate($result[7]);
		}
		$template_Time=$tpl_array_values[4];

		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}

		$template_Expense=$tpl_array_values[5];

		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];

		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}

		if($result[1]!="" && $result[0]!="")
		{
			if($clientuser=="")
				$clientuser.=$result[2];
			else
				$clientuser.="','".$result[2];
				
			if($billcontusercheck=="")
				$billcontusercheck.=$result[14];
			else
				$billcontusercheck.="','".$result[14];
				
			if($clientbillcontusercheck=="")
				$clientbillcontusercheck.=$result[2]."|".$result[13]."|".$result[14];
			else
				$clientbillcontusercheck.="','".$result[2]."|".$result[13]."|".$result[14];			

			$ftdate=$result[0];
			$ttdate=$result[1];

			$timedate1 = getTimedate_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db);
			$expensedate1 = getExpensedate_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db);
			$placementfeedate1 = getPlacementFeedate_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db);
			
			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			 $dd[0] = $timedate[0];
			 $dd[1] = $timedate[1];
			 $dd1[0] = $expensedate[0];
			 $dd1[1] = $expensedate[1];
			 $dd2[0] = $placementfeedate[0];
			 $dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);

			$MaxMinDates = explode("|",$MaxMinDates1);
			
			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			
			if(isset($result[17]) && $result[17]!='')
			{ 
		     $timeAmounts=getTime_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db,'',$result[17],$result[7]);
			 $expense=getExpense_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db,$Exp_sno,$result[17]);
		       }else{
			$timeAmounts=getTime_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db,$Time_sno,'',$result[7]);
			$expense=getExpense_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db,$Exp_sno);
			   }
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			$timeRowsCount=getTimeRowsCount_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db);
			
			$expenseRowsCount=getExpenseRowsCount_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db);
			$timeExpenseRowCount = NULL;
			
			if($timeRowsCount || $expenseRowsCount)
				$timeExpenseRowCount = 'Y';
			else
				$timeExpenseRowCount = 'N';		
			$charge=getCharges_billcont($ftdate,$ttdate,$result[2],$result[13],$result[14],$db);
			$placementfee = getPlacementFee_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db,$Charge_sno);

			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);

			$pque="SELECT inv_col_perdiem_chk FROM IT_Columns LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno=Invoice_Template.invtmp_columns WHERE Invoice_Template.invtmp_sno = '".$result[7]."'";
			$pres=mysql_query($pque,$db);
			$prow=mysql_fetch_row($pres);

			$perDiemTot=getPerDiem_billcont($cs2,$cs1,$result[2],$result[13],$result[14],$db,$Time_sno,$prow[0]);
			$burdenchargeamt = getBurdenChargesData_billcont($db,$result[2],$Time_sno,$cs1,$cs2,'',$result[13],$result[14]);

			$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$amountdue=$time+$expense+$charge+$placementfee+$perDiemTot+$burdenchargeamt;
			$getSubToTDue = $amountdue;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($result[7]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];

			$getFieldsTotal = $time + $expense + $charge + $placementfee;

			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;

			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;

			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}

			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $result[2]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);

			if($getAlertForMultipleInvoice == "Split")
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			else
				$discountTaxFlatChk = "";

			$tque = "SELECT rp.amount, rp.amountmode
			FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
			WHERE cdt.customer_sno = '".$result[2]."' 
			AND cdt.tax_discount_id = ct.taxid 
			AND ct.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = ct.sno
			AND rp.parenttype = 'TAX'
			AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);

			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
			FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
			WHERE cdt.customer_sno = '".$result[2]."' 
			AND cdt.tax_discount_id = cd.discountid 
			AND cd.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = cd.sno
			AND rp.parenttype = 'DISCOUNT' 
			AND cdt.type = 'Discount' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);

			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...

			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),2,'.','');
						else
							$totalDiscAmount += number_format($drow[0],2,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),2,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');						
							$totalDiscAmount += number_format($drow[0],2,'.','');
						}
					}
				}
			}
			else
			{
				$totalDiscAmount = "0";
			}

			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
					{
						$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');
					}
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],2,'.','');
					}
				}
			}
			else
			{
				$totalTaxAmount = "0";
			}

			if($amountdue  !=0)
				$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

			$cli=$result[5];
			$cliid=$result[6];
			$template_id = $result[7];

			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name();  						

			if(number_format($amountdue, $decimalPref,".", "") != 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);

				$selCond ='';
				if($selClient !=''){
					$selCond = '&selClient=$selClient&selAddr=$selAddr';
				}
				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$result[2]&billcontuser=$result[14]&billaddr=$result[13]&asgnIdValues=$result[17]".$selCond;
				$grid.="[";
				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($result[2])."|".$cservicedateto."-".$cservicedate."|".$result[14]."|".$TiExCh_Val."|".$amountdue."|".$result[8]."|".$getSubToTDue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."|".$result[13]."|".$result[14]."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[]  id=cliid[] value=".$result[2]."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($cli)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				$grid.="\"".gridcell($result[9])."\",";
				$grid.="\"".gridcell($cli)."\",";
				$grid.="\"".gridcell($result[12])."\",";
			
				$grid.="\"".$invoiceCount."\",";
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($totalcharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($result[10])."\",";
				$grid.="\"".gridcell($result[11])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=BillingContact&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$result[14]&selAddr=$result[13]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
					$grid.="\"".$temp_type."?".$qstr."\"";
					
				}
				$j++;

				if($j==$row_count1)
				{
					$grid.="]\n";
					$check_status_grid=1;
				}
				else
				{
					$grid.="],";
					$check_status_grid=0;
				}
			}
			else
			{
				$j++;				
			}
		}
	}

	$j=0;
	$billCondboth ='';
	if($selAddr!='' && $selClient!=''){
		$billCondboth = " and hrcon_jobs.bill_address='".$selAddr."' and hrcon_jobs.bill_contact='".$selClient."'";
	}
	 $sque="select FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),expense.client,emp_list.name,emp_list.username,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,CONCAT(staffacc_contact.fname,'  ',staffacc_contact.lname),hrcon_jobs.bill_address,hrcon_jobs.bill_contact,staffacc_cinfo.override_tempid,GROUP_CONCAT(expense.assid) from par_expense LEFT JOIN expense ON expense.parid=par_expense.sno LEFT JOIN emp_list ON emp_list.username =par_expense.username LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=expense.client LEFT JOIN staffacc_list ON  staffacc_list.username=staffacc_cinfo.username LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=expense.assid LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active'  LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno   LEFT JOIN staffacc_contact ON staffacc_contact.sno=hrcon_jobs.bill_contact  where ".$loc_clause." par_expense.username=hrcon_jobs.username and hrcon_jobs.client= expense.client and expense.client!='' and hrcon_jobs.client!='0' and hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') and expense.client != '' and (CONCAT_WS('|',expense.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact)  NOT IN ('".$clientbillcontusercheck."')) and expense.billable='bil' and par_expense.astatus IN ('Approve','Approved','ER') and expense.status IN ('Approve','Approved') AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d')>='".$cs1."' and DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$cs2."' AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND (staffacc_contact.fname !='' || staffacc_contact.lname !='') ".$billCondboth." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") group by expense.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact";
	$sres=mysql_query($sque,$db);
	$row_count = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
		if($srow[15]!='0')
			{
				$srow[7] = $srow[15];
			}

		$noTimeTax = $noExpenseTax = $noChargeTax = false;
	
		$temp_type = getDefaultTemp_Type($srow[7]);
		if($temp_type == '3'){
			$tpl_array_values = custom_genericTemplate($srow[7]);	
		}else{
			$tpl_array_values = genericTemplate($srow[7]);	
		}
		$template_Time=$tpl_array_values[4];
		
		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}
	
		$template_Expense=$tpl_array_values[5];
			
		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}		
	
		if($srow[1]!="" && $srow[0]!="")
		{
			if($clientuser=="")
				$clientuser.=$srow[2];
			else
				$clientuser.="','".$srow[2];
				
			if($billcontusercheck=="")
				$billcontusercheck.=$srow[14];
			else
				$billcontusercheck.="','".$srow[14];
				
			if($clientbillcontusercheck=="")
				$clientbillcontusercheck.=$srow[2]."|".$srow[13]."|".$srow[14];
			else
				$clientbillcontusercheck.="','".$srow[2]."|".$srow[13]."|".$srow[14];

			$ftdate=$srow[0];
			$ttdate=$srow[1];

			$timedate1 = getTimedate_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db);
			$expensedate1 = getExpensedate_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db);
			$placementfeedate1 = getPlacementFeedate_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db);

			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
			$MaxMinDates = explode("|",$MaxMinDates1);

			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			if(isset($srow[16]) && $srow[16]!='')
			{ 
		     $timeAmounts=getTime_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db,'',$srow[16],$srow[7]);
			 $expense=getExpense_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db,$Exp_sno,$srow[16]);
		       }else{
			$timeAmounts=getTime_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db,$Time_sno);
			$expense=getExpense_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db,$Exp_sno);
			   }

			
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			
			$charge=getCharges_billcont($ftdate,$ttdate,$srow[2],$srow[13],$srow[14],$db);
			$placementfee = getPlacementFee_billcont($cs2,$cs1,$srow[2],$srow[13],$srow[14],$db,$Charge_sno);
			//$perDiemTot=getPerDiem_emp($cs2,$cs1,$srow[2],$srow[4],$db,$Time_sno);
			$amountdue=$time+$expense+$charge+$placementfee;
			$expcharges	= $charge+$placementfee;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($srow[7]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];

			$getFieldsTotal = $time + $expense + $charge + $placementfee;

			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;

			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;

			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}

			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);

			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $srow[2]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);

			if($getAlertForMultipleInvoice == "Split")
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			else
				$discountTaxFlatChk = "";
		
			$tque = "SELECT rp.amount, rp.amountmode
			FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
			WHERE cdt.customer_sno = '".$srow[2]."' 
			AND cdt.tax_discount_id = ct.taxid 
			AND ct.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = ct.sno
			AND rp.parenttype = 'TAX'
			AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);

			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
			FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
			WHERE cdt.customer_sno = '".$srow[2]."' 
			AND cdt.tax_discount_id = cd.discountid 
			AND cd.status = 'active'
			AND cdt.status = 'active' 
			AND rp.parentid = cd.sno
			AND rp.parenttype = 'DISCOUNT' 
			AND cdt.type = 'Discount' ".$discountTaxFlatChk."
			AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);

			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...
			if($discForCustomer=="Y")
			{
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),2,'.','');
						else
							$totalDiscAmount += number_format($drow[0],2,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),2,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');						
							$totalDiscAmount += number_format($drow[0],2,'.','');
						}
					}
				}
			}
			else
			{
				$totalDiscAmount = "0";
			}

			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
						$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],2,'.','');
					}
				}
			}
			else
			{
				$totalTaxAmount = "0";
			}

			if($amountdue  !=0)
				$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

			$cli=$srow[5];
			$cliid=$srow[6];
			$template_id = $srow[7];
			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name();  

			if(number_format($amountdue, $decimalPref,".", "") != 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);

				$selCond ='';
				if($selClient !=''){
					$selCond = '&selClient=$selClient&selAddr=$selAddr';
				}
				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[2]&billcontuser=$srow[14]&billaddr=$srow[13]&asgnIdValues=$srow[16]".$selCond;
				if($check_status_grid==1)
					$grid.=",[";
				else
					$grid.="[";

				$grid.="\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($srow[2])."|".$cservicedateto."-".$cservicedate."|".$srow[14]."|".$TiExCh_Val."|".$amountdue."|".$srow[8]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."|".$srow[13]."|".$srow[14]."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$srow[2]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				
				$grid.="\"".gridcell($srow[9])."\",";	
				$grid.="\"".gridcell($cli)."\",";
				$grid.="\"".gridcell($srow[12])."\",";
				$grid.="\"".$invoiceCount."\",";			
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($expcharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($srow[10])."\",";
				$grid.="\"".gridcell($srow[11])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=BillingContact&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$srow[14]&selAddr=$result[13]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					$grid.="\"".$temp_type."?".$qstr."\"";
						
					
				}

				$j++;
				if($j==$row_count)
				{
					$grid.="]\n";
					$check_status_grid=2;
				}
				else
				{
					$grid.="],";
					$check_status_grid=0;
				}
			}
			else
			{
				$j++;
			}
		}
	}

	$j=0;

	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);

	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];

	$billCondboth ='';
	if($selAddr!='' && $selClient!=''){
		$billCondboth = " and hrcon_jobs.bill_address='".$selAddr."' and hrcon_jobs.bill_contact='".$selClient."'";
	}
	 $sque="select hrcon_jobs.client,hrcon_jobs.pusername,SUM(hrcon_jobs.placement_fee),MIN(IF (	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) ) 	), MAX( IF (	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', now(), str_to_date( hrcon_jobs.s_date, '%m-%d-%Y' ) )),hrcon_jobs.username,emp_list.name,staffacc_cinfo.cname,staffacc_list.sno,staffacc_cinfo.templateid,if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billrate,hrcon_compen.diem_billrate),if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_billable,hrcon_compen.diem_billable), if(hrcon_compen.diem_pay_assign='Y',hrcon_jobs.diem_period,hrcon_compen.diem_period),staffacc_cinfo.tax, staffacc_cinfo.sno,CONCAT(contact_manage.loccode,' - ',contact_manage.heading),department.deptname,CONCAT(staffacc_contact.fname,'  ',staffacc_contact.lname),hrcon_jobs.bill_address,hrcon_jobs.bill_contact,staffacc_cinfo.override_tempid,GROUP_CONCAT(hrcon_jobs.pusername) from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username and hrcon_compen.ustatus='active') LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username LEFT JOIN hrcon_compen hc ON hc.username=emp_list.username AND hc.ustatus='active'  LEFT JOIN Client_Accounts ON staffacc_cinfo.sno=Client_Accounts.typeid AND Client_Accounts.status = 'active' LEFT JOIN contact_manage ON Client_Accounts.loc_id=contact_manage.serial_no LEFT JOIN department ON Client_Accounts.deptid=department.sno   LEFT JOIN staffacc_contact ON staffacc_contact.sno=hrcon_jobs.bill_contact  where ".$loc_clause." hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'') AND (CONCAT_WS('|',hrcon_jobs.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact)  NOT IN ('".$clientbillcontusercheck."')) AND str_to_date(IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$cs1."' and str_to_date( IF (hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cs2."' AND (staffacc_contact.fname !='' || staffacc_contact.lname !='') ".$billCondboth." AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.") GROUP BY hrcon_jobs.client,hrcon_jobs.bill_address,hrcon_jobs.bill_contact";
	$sres=mysql_query($sque,$db);
	$row_count3 = @mysql_num_rows($sres);
	while($srow=mysql_fetch_row($sres))
	{
		$assignmentsUsed = array();
		if($srow[20]!='0')
			{
				$srow[9] = $srow[20];
			}

		$noTimeTax = $noExpenseTax = $noChargeTax = false;

		$temp_type = getDefaultTemp_Type($srow[9]);
		if($temp_type == '3'){
			$tpl_array_values = custom_genericTemplate($srow[9]);		
		}else{
			$tpl_array_values = genericTemplate($srow[9]);		
		}
		$template_Time=$tpl_array_values[4];

		foreach($template_Time as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noTimeTax = true;
				}
			}
		}

		$template_Expense=$tpl_array_values[5];

		foreach($template_Expense as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noExpenseTax = true;
				}
			}
		}
		$template_Charges=$tpl_array_values[6];
			
		foreach($template_Charges as $key=> $values)
		{
			if($key == "Tax")
			{
				if($values[0] == 'N')
				{
					$noChargeTax = true;
				}
			}
		}
	
		if($srow[3]!="" && $srow[4]!="")
		{
			if($clientuser=="")
				$clientuser.=$srow[0];
			else
				$clientuser.="','".$srow[0];
				
			if($billcontusercheck=="")
				$billcontusercheck.=$srow[19];
			else
				$billcontusercheck.="','".$srow[19];
				
			if($clientbillcontusercheck=="")
				$clientbillcontusercheck.=$srow[0]."|".$srow[18]."|".$srow[19];
			else
				$clientbillcontusercheck.="','".$srow[0]."|".$srow[18]."|".$srow[19];
				
			$ftdate=$srow[3];
			$ttdate=$srow[4];

			$timedate1 = getTimedate_billcont($cs2,$cs1,$srow[0],$srow[18],$srow[19],$db);
			$expensedate1 = getExpensedate_billcont($cs2,$cs1,$srow[0],$srow[18],$srow[19],$db);
			$placementfeedate1 = getPlacementFeedate_billcont($cs2,$cs1,$srow[0],$srow[18],$srow[19],$db);
			
			$timedate = explode("|",$timedate1);
			$expensedate = explode("|",$expensedate1);
			$placementfeedate = explode("|",$placementfeedate1);
			$dd[0] = $timedate[0];
			$dd[1] = $timedate[1];
			$dd1[0] = $expensedate[0];
			$dd1[1] = $expensedate[1];
			$dd2[0] = $placementfeedate[0];
			$dd2[1] = $placementfeedate[1];
			$MaxMinDates1 = getMaxMindate($dd[0],$dd[1],$dd1[0],$dd1[1],$dd2[0],$dd2[1]);
			$MaxMinDates = explode("|",$MaxMinDates1);
			
			$sintdate=explode("-",$MaxMinDates[1]);
			$cservicedate=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			$sintdate=explode("-",$MaxMinDates[0]);
			$cservicedateto=$sintdate[1]."/".$sintdate[2]."/".$sintdate[0];
			if(isset($srow[21]) && $srow[21]!='')
			{ 
		     $timeAmounts=getTime_billcont($cs2,$cs1,$srow[2],$srow[18],$srow[19],$db,'',$srow[21],$srow[7]);
			 $expense=getExpense_billcont($cs2,$cs1,$srow[2],$srow[18],$srow[19],$db,$Exp_sno,$srow[21]);
		       }else{
			$timeAmounts=getTime_billcont($cs2,$cs1,$srow[0],$srow[18],$srow[19],$db,$Time_sno);
			$expense=getExpense_billcont($cs2,$cs1,$srow[0],$srow[18],$srow[19],$db,$Exp_sno);
			   }
			
			
			$time = $timeAmounts[0];
			$taxTime = $timeAmounts[1];
			$taxTimeSnos = $timeAmounts[2];
			
			$charge=getCharges_billcont($ftdate,$ttdate,$srow[0],$srow[18],$srow[19],$db);
			$placementfee = getPlacementFee_billcont($cs2,$cs1,$srow[0],$srow[18],$srow[19],$db,$Charge_sno);
			//$perDiemTot=getPerDiem_emp($cs2,$cs1,$srow[0],$srow[5],$db,$Time_sno);
			$amountdue=$time+$expense+$charge+$placementfee;
			$plscharges=$charge+$placementfee;	
			
			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer = getCustomerTaxDisc($srow[9]);
			$expForCustomer = explode("|",$taxdiscForCustomer);
			$taxForCustomer = $expForCustomer[0];
			$discForCustomer = $expForCustomer[1];
			
			$getFieldsTotal = $time + $expense + $charge + $placementfee;
			
			if($noTimeTax)
				$timeTaxTotal = 0;
			else
				$timeTaxTotal = $taxTime;
			if($noExpenseTax)
				$expenseTaxTotal = 0;
			else
				$expenseTaxTotal = $expense;
				
			if($noChargeTax)
			{
				$chargeTaxTotal = 0;
				$placementfeeTaxTotal = 0;
			}
			else
			{
				$chargeTaxTotal = $charge;
				$placementfeeTaxTotal = $placementfee;
			}
			
			$getTaxesFieldsTotal = $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;
			
			$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate=date("Y-m-d",$thisday);
			
			$assignmentsUsed = array_unique($assignmentsUsed);
			$asgnSnos = implode(",",$assignmentsUsed);
			$custAsgnIds = $srow[0]."|".$asgnSnos;
			$getAlertForMultipleInvoice = getAlertForMultipleInvoice($custAsgnIds,'');
			$getArrayForInvoiceCount = getIndividualAssignmentGroups($asgnSnos,$getAlertForMultipleInvoice);
			$invoiceCount = count($getArrayForInvoiceCount);
			
			if($getAlertForMultipleInvoice == "Split"){
				$discountTaxFlatChk = " AND rp.amountmode != 'FLAT' ";				
			}else{
				$discountTaxFlatChk = "";				
			}		
			
			$tque = "SELECT rp.amount, rp.amountmode
					FROM customer_discounttaxes cdt, company_tax ct, rates_period rp
					WHERE cdt.customer_sno = '".$srow[0]."' 
					AND cdt.tax_discount_id = ct.taxid 
					AND ct.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = ct.sno
					AND rp.parenttype = 'TAX'
					AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$tres=mysql_query($tque,$db);
			
			$dque = "SELECT rp.amount, rp.amountmode, rp.taxmode 
					FROM customer_discounttaxes cdt, company_discount cd, rates_period rp
					WHERE cdt.customer_sno = '".$srow[0]."' 
					AND cdt.tax_discount_id = cd.discountid 
					AND cd.status = 'active'
					AND cdt.status = 'active' 
					AND rp.parentid = cd.sno
					AND rp.parenttype = 'DISCOUNT' 
					AND cdt.type = 'Discount' ".$discountTaxFlatChk."
					AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";
			$dres=mysql_query($dque,$db);
		
			$totalTaxAmount = "0";
			$totalDiscAmount = "0";
			
			$btDiscTotal = 0.00;	// Get before tax discount amount sum...
			if($discForCustomer=="Y")
			{
				
				while($drow=mysql_fetch_row($dres))
				{
					if($drow[2] == "at")
					{
						if($drow[1] == "PER")
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),2,'.','');
						else
							$totalDiscAmount += number_format($drow[0],2,'.','');
					}
					else
					{
						if($drow[1] == "PER")
						{
							$btDiscTotal = number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount += number_format((($amountdue * $drow[0]) /100),2,'.','');
						}
						else
						{
							$btDiscTotal = number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');						
							$totalDiscAmount += number_format($drow[0],2,'.','');
						}
					}
				}
			}
			else
				$totalDiscAmount = "0";
			
			$newTaxableAmount = $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...
			
			if($taxForCustomer=="Y")
			{
				while($trow=mysql_fetch_row($tres))
				{
					if($trow[1] == "PER")
						$totalTaxAmount += number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');
					else
					{
						if($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0)
							$totalTaxAmount += number_format($trow[0],2,'.','');
					}
				}
			}
			else
				$totalTaxAmount = "0";
			
			if($amountdue  !=0)
				$amountdue = ($amountdue + $totalTaxAmount) - $totalDiscAmount;

			$cli=$srow[7];
			$cliid=$srow[8];
			$template_id = $srow[9];
			if($template_id !='0' )
				$templateName = getTemplateName($template_id);
			else				
				$templateName =getDefaultTemp_Name(); 

			if(number_format($amountdue, $decimalPref,".", "") != 0)
			{
				$assignmentsUsed = array_unique($assignmentsUsed);
				$asgnSnos = implode(",",$assignmentsUsed);
				
				$selCond ='';
				if($selClient !=''){
					$selCond = '&selClient=$selClient&selAddr=$selAddr';
				}
				
				$qstr="stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$srow[0]&billcontuser=$srow[19]&billaddr=$srow[18]&asgnIdValues=$srow[21]".$selCond;
				if($check_status_grid==1 || $check_status_grid==2)
					$grid.=",[";
				else
					$grid.="[";
				
				$grid.="\"<input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($srow[0])."|".$cservicedateto."-".$cservicedate."|".$srow[19]."|".$TiExCh_Val."|".$amountdue."|".$srow[13]."|".$amountdue."|".$newTaxableAmount."|".$taxTimeSnos."|".$asgnSnos."|".$srow[18]."|".$srow[19]."' id=auids[]><input type=hidden name=cliid[] id=cliid[] value=".$srow[0]."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";
				
				$grid.="\"".gridcell($srow[14])."\",";
				$grid.="\"".gridcell($cli)."\",";	
				$grid.="\"".gridcell($srow[17])."\",";//change
				$grid.="\"".$invoiceCount."\",";
				$grid.="\"".$cservicedateto."-".$cservicedate."\",";
				$grid.="\"".number_format($time, 2,".", "")."\",";
				$grid.="\"".number_format($plscharges, 2,".", "")."\",";
				$grid.="\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid.="\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid.="\"".gridcell(stripslashes($templateName))."\",";
				$grid.="\"".gridcell($srow[15])."\",";
				$grid.="\"".gridcell($srow[16])."\",";

				if($invoiceCount > 1){
					$grid.="\""."invoiceall.php?invtype=BillingContact&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$srow[19]&selAddr=$result[18]\"";
				}else{
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
					$grid.="\"".$temp_type."?".$qstr."\"";
					
				}
				$check_status_grid=3;
				$j++;
				if($j==$row_count3)
					$grid.="]\n";
				else
					$grid.="],";
			}
			else
				$j++;
		}
	}

	$grid = trim($grid,",");

	$grid.="];\n";
	$grid.="</"."script".">\n";
	return $grid;
}


/**
 * Function used to get the burden charges amount to display in Total Amount for the invoice on the grid
 *		
 * @param	Numeric $client_id - Customer/Client ID assiciated to assignment 
 * @param	string $Time_sno - Customer/Client ID assiciated to timesheet 
 * @param	Dates $ser_fr_date,ser_to_date - Service dates filter in the grid (from & to)
 * @param	string $chkPusernames - timesheet assignment ids seperated by comma
 * @param	string $empuser - timesheet username 
 * @return	Float $burden_charges_values
 */
function getBurdenChargesData($db,$client_id,$Time_sno,$ser_fr_date,$ser_to_date,$chkPusernames = '',$empuser = '')
{
	//Initializing the variables for calcualting the burden details for the invoice items
	$asgmnt_gen_details = array(); // form the assignment total hours and name based on assignment ID & service dates
	$asgmnt_rate_details = array(); //form the assignment multiple rate details basd on assignment id and ratemaster id
	$asgmnt_burden_details_values = array(); // form the burden details based on assignment ID
	$burden_charges_values = 0; //form the burden charges with calculations	
	$hrconjobs_sno_str = ""; // capture the list of hrcon jobs sno string
	$bc_ratewise_amnts = array(); //capture the burden charges total amount based on assignment and service dates and rate master ids (rate1/rate2/..etc.,)
	
	$condAdd = "";
	$template_Time_Check = "";
	
	if($chkPusernames != '')
		$condAdd = " AND timesheet.assid IN ('".str_replace(",","','",$chkPusernames)."')";

	if($Time_sno != '')
		$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";
	
	if($empuser != '')
		$empusercond = " AND timesheet.username='".$empuser."'";
	
	$get_burdencharges_ts_sql = "SELECT emp_list.name,
										timesheet.assid,
										SUM(timesheet.hours),
										".tzRetQueryStringDate('par_timesheet.sdate','Date','/').",
										".tzRetQueryStringDate('par_timesheet.edate','Date','/').",
										timesheet.hourstype,
										hrcon_jobs.sno,
										multiplerates_assignment.ratemasterid,
										multiplerates_assignment.ratetype,
										multiplerates_assignment.rate

										FROM timesheet_hours as timesheet
										JOIN emp_list ON timesheet.username=emp_list.username 
										JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid AND hrcon_jobs.username = timesheet.username) 
										JOIN multiplerates_assignment ON (multiplerates_assignment.asgnid = hrcon_jobs.sno AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = timesheet.hourstype AND multiplerates_assignment.ratetype = 'billrate') 
										JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) 

										WHERE
										hrcon_jobs.client = '".$client_id."' AND 
										timesheet.status = 'Approved' AND 
										timesheet.type != 'EARN' AND 
										timesheet.billable = 'Yes' AND 
										hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND 
										timesheet.sdate >= '".$ser_fr_date."' AND 
										timesheet.sdate <= '".$ser_to_date."' 
										".$condAdd." 
										".$template_Time_Check." 
										".$empusercond." 

										GROUP BY 
										timesheet.parid,timesheet.assid,timesheet.hourstype ";
	$get_burdencharges_ts_res = mysql_query($get_burdencharges_ts_sql,$db);	

	while($bc_details = mysql_fetch_array($get_burdencharges_ts_res))
	{
		$act_service_dates = $bc_details[3]." - ".$bc_details[4];
		//Form the assignment key based on service dates for burden charges
		$asgmnt_key = $act_service_dates;
		
		//capture the 3D array basd on assignment ID and Service Dates for Emp Name/Rates/Total Hrs
		$asgmnt_gen_details[$bc_details[1]][$asgmnt_key]['empname'] = $bc_details[0];		
		//Calculate the total hours for each rate type
		if(isset($asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]]))
		{
			$asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]] += $bc_details[2];
		}
		else
		{
			$asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]] = $bc_details[2];
		}
		//concatename the hrconjobs sno value to get the burden details for calculating the burden charges
		$hrconjobs_sno_str .= $bc_details[6].",";
	
	}
	$hrconjobs_sno_str = substr($hrconjobs_sno_str,0,strlen($hrconjobs_sno_str)-1); // remove the last appended comma (,)
	
	//Get the rates for the above formed hrcon jobs
	$asgmnt_mulrates_details_sql = "SELECT hj.sno,
										   hj.pusername,
										   mr.ratemasterid,
										   mr.ratetype,
										   mr.rate
										FROM multiplerates_assignment mr
										JOIN hrcon_jobs hj ON hj.sno = mr.asgnid
										WHERE mr.asgnid IN (".$hrconjobs_sno_str.") AND mr.asgn_mode='hrcon' AND mr.status='ACTIVE'
										   ";
	$asgmnt_mulrates_details_res = mysql_query($asgmnt_mulrates_details_sql,$db);
	
	//Form the multiple rates(pay/bill rate) details based on assignment id and rate id
	while($mrrow = mysql_fetch_row($asgmnt_mulrates_details_res))
	{
		//Form the assignment rate details based on assid, rate master id and rate type 
		if($mrrow[3] == 'payrate')
		{
			$asgmnt_rate_details[$mrrow[1]][$mrrow[2]]['payrate'] = $mrrow[4];
		}
		else if($mrrow[3] == 'billrate')
		{
			$asgmnt_rate_details[$mrrow[1]][$mrrow[2]]['billrate'] = $mrrow[4];
		}
		
	}
	
	//Get the Burden Details list for the above build hrcon jobs sno if have any
	$asgmnt_burden_details_sql = 	"SELECT h.hrcon_jobs_sno,
						hj.pusername,
						bt.burden_type_name,
						bi.burden_item_name,
						bi.burden_value,
						bi.burden_mode,
						bi.ratetype,
						bi.max_earned_amnt,
						bi.taxable_status,
						bi.assigned_rateids,
						bt.calc_burden_on
					FROM hrcon_burden_details h
					JOIN hrcon_jobs hj ON hj.sno = h.hrcon_jobs_sno
					JOIN burden_types bt on bt.sno = h.bt_id AND bt.burden_type_name != 'Zero Bill Burden'
					JOIN burden_items bi ON bi.sno = h.bi_id
					WHERE h.ratetype='billrate' AND 
					h.hrcon_jobs_sno IN (".$hrconjobs_sno_str.")";
	$asgmnt_burden_details_res = mysql_query($asgmnt_burden_details_sql,$db);
	
	//Form the burden details in an array based on assignment ID
	while($birow = mysql_fetch_row($asgmnt_burden_details_res))
	{
		//Form the assignment burden details based on assignment ID 
		$asgmnt_burden_details_values[$birow[1]][] = array($birow[2],$birow[3],$birow[4],$birow[5],$birow[6],$birow[7],$birow[8],$birow[9],$birow[10]);
	}
	
	//Iterate Through the above formed assignment rates/Hrs 
	foreach($asgmnt_gen_details as $asgmntid=>$asgmntvals)
	{
		//Check if Burden Details exists for particular assignment
		if(isset($asgmnt_burden_details_values[$asgmntid]))
		{
			//Iterate through the assignment rates/Hrs details
			foreach($asgmntvals as $serdates=>$ratevals)
			{
				//Get the week ending date which is the key of assignments rates array
				$weekenddateexp = explode(" - ",$serdates);
				$weekenddate = $weekenddateexp[1];
				
				//Initialize the variables
				$totalBTChargeAmnt = 0; // For calculating the Total Amount for each row based on burden charges calculations 
				$btname = ""; //Burden Type Name
				$empname = $ratevals['empname']; //Employee Nanme
				$btchkflag = 0; //Flag to check atleast one burden item is calculated for that service dates
				$bttaxstatus = array();
				$getThresholdAmount = 0;//For maximum threshold value declaration
				//Iterate Through the Burden details 
				foreach($asgmnt_burden_details_values[$asgmntid] as $asgbtvals)
				{					
					//Split the rates assigned/selected for a particular burden item
					$btrates = explode(",",$asgbtvals[7]);
					
					//Burden type flag to identify whether need to calculate on regular pay/bill rate or selected rates while creating burden type
					$calc_burden_on = $asgbtvals[8];
					
					//Burden Type Name
					$btname = $asgbtvals[0];
					
					//Check whether Burden On of Burden Items is Payrate/BillRate/Hours and do the calculation basedon that
					if($asgbtvals[4] == 'payrate' || $asgbtvals[4] == 'billrate') // IF the Burden On field is payrate/bill rate
					{
						//Iterate through the rates assignment for burden items to do the calculation 
						foreach($btrates as $c)
						{
							//Check if the burden items assigned rate is selected and have the hours calculated in assignment rate for that service dates
							if(array_key_exists($c,$ratevals))
							{
								$totalRateAmnt = 0;
																
								//If burden Mode is Percentage
								if($asgbtvals[3] == 'percentage')
								{
									/*
									Formulae : 
									For Percentage - Burden % of Regular Pay/Bill Rate * Total rate hours
									A = Regular Pay/Bill Rate * Total Rate Hours
									B = Burden Value/100 * A
									For Flat - Burden Value * Total rate hours
										
									*/
									//If Burden On is Pay rate									
									if($asgbtvals[4] == 'payrate')
									{
										//Get the Regular Pay Rate of particular assignment
										$payrateval = 0;
										if($calc_burden_on == 'Regular')
										{
										if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
										{
											$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
										}
										}
										else
										{
											$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
										}
										$totalRateAmnt = $payrateval * $ratevals[$c];
									}
									else if($asgbtvals[4] == 'billrate') //If Burden On is Bill rate
									{
										//Get the Regular Bill Rate of particular assignment
										$billrateval = 0;
										if($calc_burden_on == 'Regular')
										{
										if(isset($asgmnt_rate_details[$asgmntid]['rate1']['billrate']))
										{
											$billrateval = $asgmnt_rate_details[$asgmntid]['rate1']['billrate'];
										}
										}
										else
										{
											$billrateval = $asgmnt_rate_details[$asgmntid][$c]['billrate'];
										}
										$totalRateAmnt = $billrateval * $ratevals[$c];
									}
										
									$totalRateAmnt = ROUND(($asgbtvals[2]/100) * $totalRateAmnt,2);
								}
								else if($asgbtvals[3] == 'flat') // IF the Burden Mode is Flat Amount
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
									
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
								
							}
						}
					}
					else if($asgbtvals[4] == 'hours')
					{
						foreach($btrates as $c)
						{
							if(array_key_exists($c,$ratevals))
							{
								/*
									Formulae : 
									For Percentage - Burden % * Total rate hours of Rate ID Bill Rate
									A = Burden Value/100 * Total Rate Hours
									B = Regular Pay Rate * A
									For Flat - Burden Value * Total rate hours
									
								*/
								$totalRateAmnt = 0;	
															
								if($asgbtvals[3] == 'percentage')
								{
									//Get the Regular Pay Rate of particular assignment
									$payrateval = 0;
									if($calc_burden_on == 'Regular')
									{
									if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
									{
										$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
									}
									}
									else
									{
										$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
									}
									$totalRateAmnt = (($asgbtvals[2]/100) * $ratevals[$c]);
									$totalRateAmnt = ROUND($payrateval * $totalRateAmnt,2);
										
								}
								else if($asgbtvals[3] == 'flat')
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
							}
						}
					}
					else if($asgbtvals[4] == 'uom_units')
					{
						foreach($btrates as $c)
						{
							if(array_key_exists($c,$ratevals))
							{
								/*
									Formulae : 
									For Percentage - Burden % * Total rate units of Rate ID Bill Rate
									A = Burden Value/100 * Total Rate units
									B = Regular Pay Rate * A
									For Flat - Burden Value * Total rate units
									
								*/
								$totalRateAmnt = 0;	
															
								if($asgbtvals[3] == 'percentage')
								{
									//Get the Regular Pay Rate of particular assignment
									$payrateval = 0;
									if($calc_burden_on == 'Regular')
									{
									if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
									{
										$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
									}
									}
									else
									{
										$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
									}
									$totalRateAmnt = (($asgbtvals[2]/100) * $ratevals[$c]);
									$totalRateAmnt = ROUND($payrateval * $totalRateAmnt,2);
										
								}
								else if($asgbtvals[3] == 'flat')
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
							}
						}
					}
				}
				//If Totalrate amount is less than Threshold value then charges should display as same as Totalrate amount. If greater then Threshold value then charges should display as same as Threshold value.
				if($getThresholdAmount != '0' && $totalBTChargeAmnt > $getThresholdAmount)
				{
					$totalBTChargeAmnt = $getThresholdAmount;
				}
				//Form the burden charges array if atleast any one item is calculated
				if($btchkflag == 1)
				{		
					if(in_array("Yes",$bttaxstatus))
					{
						$btchargeTax = "yes";
					}
					else
					{
						$btchargeTax = "";
					}

					$burden_charges_values = $burden_charges_values + number_format($totalBTChargeAmnt,2,".","");
				}
			}
		}
	}
	
	return $burden_charges_values;
}
//Invoices->Billing Contact on Assignment
function getTimedate_billcont($cs2,$cs1,$client,$billaddruser,$billcontuser,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
		
		$qu="select MIN(par_timesheet.sdate),MAX(par_timesheet.edate) from timesheet_hours left join par_timesheet on (timesheet_hours.parid=par_timesheet.sno)left join invoice on (invoice.sno=timesheet_hours.billable)
		left join hrcon_jobs  ON hrcon_jobs.pusername=timesheet_hours.assid		
		where  hrcon_jobs.bill_address = '".$billaddruser."' and hrcon_jobs.bill_contact = '".$billcontuser."'   and timesheet_hours.client='".$client."' and par_timesheet.astatus IN ('ER','Approved') AND timesheet_hours.status = 'Approved' and timesheet_hours.billable='Yes' and par_timesheet.sdate>='".$cs1."' and par_timesheet.edate<='".$cs2."' ".$condAdd." group by invoice.sno" ;
		
		$res=mysql_query($qu,$db);
		$dd=mysql_fetch_row($res);
		
		$date1 = $dd[0]."|".$dd[1];
		return $date1;
	}
	
	Function getExpensedate_billcont($cs2,$cs1,$client,$billaddruser,$billcontuser,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		 $qu1="select MIN(par_expense.sdate),MAX(par_expense.edate) from expense left join par_expense on (expense.parid=par_expense.sno)left join invoice on (invoice.sno=expense.billable) left join hrcon_jobs  ON hrcon_jobs.pusername=expense.assid		
		where  hrcon_jobs.bill_address = '".$billaddruser."' and hrcon_jobs.bill_contact = '".$billcontuser."'  and expense.client='".$client."' and par_expense.astatus IN ('ER','Approved') AND expense.status='Approved' and expense.billable='bil' and (expense.edate >='".$cs1."' AND expense.edate <= '".$cs2."') ".$condAdd." group by expense.client";
		$res1=mysql_query($qu1,$db);
		$dd1=mysql_fetch_row($res1);
		
		$date1 = $dd1[0]."|".$dd1[1];
		return $date1;
	}
	
	Function getPlacementFeedate_billcont($cs2,$cs1,$client,$billaddruser,$billcontuser,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$todaydate=date("m-d-Y",$thisday);
		
		$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN('Direct','Temp/Contract to Direct') AND type='jotype' AND type='jotype'";
		$resdirect = mysql_query($quedirect,$db);
		$rowdirect = mysql_fetch_row($resdirect);
		$snodirect = $rowdirect[0];
		
		 $qu2="select MIN( 
	date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )
	), MAX( 
	date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )
	)
	 from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where hrcon_jobs.bill_address = '".$billaddruser."' and hrcon_jobs.bill_contact = '".$billcontuser."'  and (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y') AND hrcon_jobs.ustatus in ('active','closed') AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client='".$client."' AND (date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' ) 
	 >='".$cs1."' AND date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' ) 
	 <= '".$cs2."') AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' ".$condAdd." group by hrcon_jobs.client";
		$res2=mysql_query($qu2,$db);
		$dd2=mysql_fetch_row($res2);
		
		$date1 = $dd2[0]."|".$dd2[1];
		return $date1;
	}
	
	function getTime_billcont($cdate,$ttdate,$name,$billcontaddr,$billcontuser,$db,$Time_sno,$chkPusernames = '',$templateId='')
	{
		global $assignmentsUsed,$assignmentsUsedTotal;
	
		if($chkPusernames != '')
			$condAdd = "AND timesheet.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$reqclient=$name;
		$count=0;
		if($Time_sno != '')
			$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";		
		
		$grp_personId ='';
		if($templateId != ''){
			$grp_personId = get_personGrping_basedTemp($templateId);
		}
		$eque="SELECT SUM(timesheet.hours),hrcon_jobs.sno, timesheet.hourstype, GROUP_CONCAT(timesheet.sno) FROM timesheet_hours AS timesheet LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid and hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN('active','closed','cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate>='".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND hrcon_jobs.bill_address='".$billcontaddr."' AND hrcon_jobs.bill_contact='".$billcontuser."' AND hrcon_jobs.client=timesheet.client ".$template_Time_Check." ".$condAdd." GROUP BY timesheet.parid,timesheet.assid, timesheet.hourstype".$grp_personId;

		$eres=mysql_query($eque,$db);
		$count=0;
		$taxAmount = $tamount = 0;
		$timeAmounts = array();
		$timeModSnos = "";
		while($erow=mysql_fetch_row($eres))
		{			
			$getRates = "SELECT '', multiplerates_assignment.rate,multiplerates_assignment.period, ROUND((ROUND(CAST('".$erow[0]."' AS DECIMAL(12,2)),2) * IF(multiplerates_assignment.period='YEAR',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*261)),2), IF(multiplerates_assignment.period='MONTH',ROUND(( CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*(261/12))),2),IF(multiplerates_assignment.period='WEEK',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*5)),2),IF(multiplerates_assignment.period='DAY',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/8),2),ROUND(CAST( multiplerates_assignment.rate AS DECIMAL(12,2)),2)))))),2), multiplerates_assignment.taxable AS Taxable FROM  multiplerates_assignment WHERE multiplerates_assignment.asgnid = '".$erow[1]."' AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = '".$erow[2]."' AND multiplerates_assignment.ratetype = 'billrate'";
			$resRates = mysql_query($getRates,$db);
			$rowRates = mysql_fetch_array($resRates);
		
			if($rowRates[2]=="FLATFEE")
			{
				if($erow[0] != 0)
					$regrate = number_format($rowRates[1],2,'.','');
				else
					$regrate = 0.00;
			}
			else
				$regrate = number_format($rowRates[3],2,'.','');//number_format(($erow[3]*$trate),2,'.','');
			
			$tamount += $regrate;
			
			if($regrate != 0){
				if($chkPusernames == '')
					$assignmentsUsedTotal[] = $erow[1];
				
				$assignmentsUsed[] = $erow[1];
			}
			
			if($rowRates['Taxable'] == 'Y')
			{
				$taxAmount += $regrate;
				if($timeModSnos == "")
					$timeModSnos = $erow[3];
				else
					$timeModSnos .= ",".$erow[3];
			}
		
		}
		$timeAmounts[0] = $tamount;
		$timeAmounts[1] = $taxAmount;
		$timeAmounts[2] = $timeModSnos;
		return $timeAmounts;
	}
	Function getTimeRowsCount_billcont($cdate,$ttdate,$name,$billcontaddr,$billcontuser,$db)
	{
		$reqclient=$name;
		$count=0;
		
		$eque="SELECT SUM(timesheet.hours) FROM timesheet_hours AS timesheet LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid and hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate >= '".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND hrcon_jobs.bill_address='".$billcontaddr."' AND hrcon_jobs.bill_contact='".$billcontuser."' AND hrcon_jobs.client=timesheet.client ".$template_Time_Check." GROUP BY timesheet.parid,timesheet.assid,timesheet.hourstype";
		$eres=mysql_query($eque,$db);
		return mysql_num_rows($eres)>0;
	}
	function getExpense_billcont($cdate,$ttdate,$name,$billcontaddr,$billcontuser,$db,$Exp_sno,$chkPusernames = '')
	{
		global $assignmentsUsed,$assignmentsUsedTotal;
		$reqclient=$name;
		
		if($chkPusernames != '')
			$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
		
		if($Exp_sno != '')
			$exp_template_check = "AND expense.client NOT IN ('".$Exp_sno."')";
		$eque="select expense.sno,hrcon_jobs.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and hrcon_jobs.bill_address='".$billcontaddr."' AND hrcon_jobs.bill_contact='".$billcontuser."' AND expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') ".$exp_template_check." ".$condAdd."order by expense.edate";
		$eres1=mysql_query($eque,$db);
		$exp=0;
		while($erow=mysql_fetch_row($eres1))
		{
			$expRowTotal = getExpenseRate($erow[0],$db);
			if($expRowTotal > 0){
				if($chkPusernames == '')
					$assignmentsUsedTotal[] = $erow[1];
				
				$assignmentsUsed[] = $erow[1];
			}
			$exp=$exp+$expRowTotal;
		}
	
		return $exp;
	}
	Function getExpenseRowsCount_billcont($cdate,$ttdate,$name,$billcontaddr,$billcontuser,$db)
	{
		$reqclient=$name;
		$eque="select expense.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where  hrcon_jobs.bill_address='".$billcontaddr."' AND hrcon_jobs.bill_contact='".$billcontuser."' AND  expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') order by expense.edate";
		$eres1=mysql_query($eque,$db);
		return mysql_num_rows($eres1)>0;
	}
	Function getCharges_billcont($cdate,$ttdate,$name,$billcontaddr,$billcontuser,$db)
	{
		$que="select amount from credit_charge left join hrcon_jobs ON hrcon_jobs.pusername=credit_charge.pusername where  hrcon_jobs.bill_address='".$billcontaddr."' AND hrcon_jobs.bill_contact='".$billcontuser."' AND client_name='".$name."' and ser_date > '".$ttdate."' and ser_date <= '".$cdate."'";
		$res=mysql_query($que,$db);
		$rate=0;
		while($row=mysql_fetch_row($res))
		{
			$rate=$rate+$row[0];
		}
		return $rate;
	}
	Function getPlacementFee_billcont($cdate,$ttdate,$name,$billcontaddr,$billcontuser,$db,$Charge_sno,$chkPusernames = '')
	{
		global $assignmentsUsed,$assignmentsUsedTotal;
	
		if($chkPusernames != '')
			$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$todaydate=date("m-d-Y",$thisday);
		if($Charge_sno != '')
			$charge_template_check = " AND hrcon_jobs.client NOT IN ('".$Charge_sno."')";
		$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN('Direct','Temp/Contract to Direct') AND type='jotype'";
		$resdirect = mysql_query($quedirect,$db);
		$rowdirect = mysql_fetch_row($resdirect);
		$snodirect = $rowdirect[0];
			
		 $sque="select hrcon_jobs.client,hrcon_jobs.pusername,hrcon_jobs.placement_fee,hrcon_jobs.s_date,hrcon_jobs.username,hrcon_jobs.sno,hrcon_jobs.bill_address,hrcon_jobs.bill_contact from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y')  AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL )  AND hrcon_jobs.ustatus in ('active','closed') AND str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date
	), '%m-%d-%Y' )>='".$ttdate."' and str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date
	), '%m-%d-%Y' )<='".$cdate."' AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client='".$name."' ".$condAdd ." AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' AND hrcon_jobs.bill_address='".$billcontaddr."' AND hrcon_jobs.bill_contact='".$billcontuser."' ".$charge_template_check;
		$eres=mysql_query($sque,$db);
		$placementfee = 0;
		while($erow=mysql_fetch_row($eres))
		{
			if($chkPusernames == '')
				$assignmentsUsedTotal[] = $erow[5];
			
			$assignmentsUsed[] = $erow[5];
			
			$que1 = "SELECT SUM(amount) FROM credit_charge left join hrcon_jobs ON hrcon_jobs.pusername=credit_charge.pusername where  hrcon_jobs.bill_address='".$billcontaddr."' AND hrcon_jobs.bill_contact='".$billcontuser."'  AND pusername='".$erow[1]."'";
			$res1 = mysql_query($que1,$db);
			$rrow1 = mysql_fetch_row($res1);	
			if( $rrow1[0] >= $erow[2])
				$placementfee1 = 0;
			else
				$placementfee1 = $erow[2]-$rrow1[0];
			$placementfee += $placementfee1;
		}
		return $placementfee;
	}
	Function getPerDiem_billcont($cdate,$ttdate,$name,$billcontaddr,$billcontuser,$db,$Time_sno,$perDiem_chk,$chkPusernames = '')
	{
	$reqclient=$name;
	$parDiemTOT = 0.00;
	
	if($chkPusernames != '')
		$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	$template_Time_Check = "";
	
	if($perDiem_chk == 'Y')
	{	
		if($Time_sno != '')
			$template_Time_Check = " AND timesheet_hours.client NOT IN ('".$Time_sno."')";
		
		$eque="SELECT timesheet_hours.assid,timesheet_hours.parid,hrcon_jobs.diem_billrate,hrcon_jobs.diem_billable, hrcon_jobs.diem_period, IF(timesheet_hours.edate = '0000-00-00',1,DATEDIFF(timesheet_hours.edate,timesheet_hours.sdate)+1) AS Days FROM timesheet_hours LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername = timesheet_hours.assid AND hrcon_jobs.username = timesheet_hours.username) LEFT JOIN emp_list ON timesheet_hours.username=emp_list.username WHERE timesheet_hours.client != '' AND timesheet_hours.type != 'EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client = '".$reqclient."' AND timesheet_hours.billable = 'Yes' AND timesheet_hours.sdate >= '".$ttdate."' AND timesheet_hours.sdate <= '".$cdate."' AND timesheet_hours.status = 'Approved' AND hrcon_jobs.bill_address='".$billcontaddr."' and hrcon_jobs.bill_contact='".$billcontuser."' ".$template_Time_Check." ".$condAdd." GROUP BY timesheet_hours.sdate,timesheet_hours.assid";
		
		$eres=mysql_query($eque,$db);
	
		$asgnListArrr = array();
		
		while($erow=mysql_fetch_row($eres))
		{			
			if($erow[3]=="Y")
			{
				$perDiemDays = $erow[5];
				
				if($erow[4]=="")
					$erow[4] = "DAY";
				
				if($erow[4] == "FLATFEE")
				{					
					if(!in_array($erow[1],$asgnListArrr))
					{
						$parDiemTotal = $erow[2];
						$asgnListArrr[] = $erow[1];
					}
					else
						$parDiemTotal = 0.00;
				}				
				else
				{					
					$perDayAmount = calculateAmountTotal($erow[2],$erow[4],'day');
					
					$parDiemTotal = $perDayAmount * $perDiemDays;
				}				
			}
			else
				$parDiemTotal = 0.00;
			
			$parDiemTOT = $parDiemTOT + $parDiemTotal;
		}
	}
	
	return $parDiemTOT;
}
function getBurdenChargesData_billcont($db,$client_id,$Time_sno,$ser_fr_date,$ser_to_date,$chkPusernames = '',$billcontaddr,$billcontuser = '')
{
	//Initializing the variables for calcualting the burden details for the invoice items
	$asgmnt_gen_details = array(); // form the assignment total hours and name based on assignment ID & service dates
	$asgmnt_rate_details = array(); //form the assignment multiple rate details basd on assignment id and ratemaster id
	$asgmnt_burden_details_values = array(); // form the burden details based on assignment ID
	$burden_charges_values = 0; //form the burden charges with calculations	
	$hrconjobs_sno_str = ""; // capture the list of hrcon jobs sno string
	$bc_ratewise_amnts = array(); //capture the burden charges total amount based on assignment and service dates and rate master ids (rate1/rate2/..etc.,)
	
	$condAdd = "";
	$template_Time_Check = "";
	
	if($chkPusernames != '')
		$condAdd = " AND timesheet.assid IN ('".str_replace(",","','",$chkPusernames)."')";

	if($Time_sno != '')
		$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";
	
	if($billcontuser != '')
		$billcontusercond = " AND hrcon_jobs.bill_address='".$billcontaddr."' and hrcon_jobs.bill_contact='".$billcontuser."'";
	
	$get_burdencharges_ts_sql = "SELECT emp_list.name,
										timesheet.assid,
										SUM(timesheet.hours),
										".tzRetQueryStringDate('par_timesheet.sdate','Date','/').",
										".tzRetQueryStringDate('par_timesheet.edate','Date','/').",
										timesheet.hourstype,
										hrcon_jobs.sno,
										multiplerates_assignment.ratemasterid,
										multiplerates_assignment.ratetype,
										multiplerates_assignment.rate

										FROM timesheet_hours as timesheet
										JOIN emp_list ON timesheet.username=emp_list.username 
										JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid AND hrcon_jobs.username = timesheet.username) 
										JOIN multiplerates_assignment ON (multiplerates_assignment.asgnid = hrcon_jobs.sno AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = timesheet.hourstype AND multiplerates_assignment.ratetype = 'billrate') 
										JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) 

										WHERE
										hrcon_jobs.client = '".$client_id."' AND 
										timesheet.status = 'Approved' AND 
										timesheet.type != 'EARN' AND 
										timesheet.billable = 'Yes' AND 
										hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND 
										timesheet.sdate >= '".$ser_fr_date."' AND 
										timesheet.sdate <= '".$ser_to_date."' 
										".$condAdd." 
										".$template_Time_Check." 
										".$billcontusercond." 

										GROUP BY 
										timesheet.parid,timesheet.assid,timesheet.hourstype ";
	$get_burdencharges_ts_res = mysql_query($get_burdencharges_ts_sql,$db);	

	while($bc_details = mysql_fetch_array($get_burdencharges_ts_res))
	{
		$act_service_dates = $bc_details[3]." - ".$bc_details[4];
		//Form the assignment key based on service dates for burden charges
		$asgmnt_key = $act_service_dates;
		
		//capture the 3D array basd on assignment ID and Service Dates for Emp Name/Rates/Total Hrs
		$asgmnt_gen_details[$bc_details[1]][$asgmnt_key]['empname'] = $bc_details[0];		
		//Calculate the total hours for each rate type
		if(isset($asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]]))
		{
			$asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]] += $bc_details[2];
		}
		else
		{
			$asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]] = $bc_details[2];
		}
		//concatename the hrconjobs sno value to get the burden details for calculating the burden charges
		$hrconjobs_sno_str .= $bc_details[6].",";
	
	}
	$hrconjobs_sno_str = substr($hrconjobs_sno_str,0,strlen($hrconjobs_sno_str)-1); // remove the last appended comma (,)
	
	//Get the rates for the above formed hrcon jobs
	$asgmnt_mulrates_details_sql = "SELECT hj.sno,
										   hj.pusername,
										   mr.ratemasterid,
										   mr.ratetype,
										   mr.rate
										FROM multiplerates_assignment mr
										JOIN hrcon_jobs hj ON hj.sno = mr.asgnid
										WHERE mr.asgnid IN (".$hrconjobs_sno_str.") AND mr.asgn_mode='hrcon' AND mr.status='ACTIVE'
										   ";
	$asgmnt_mulrates_details_res = mysql_query($asgmnt_mulrates_details_sql,$db);
	
	//Form the multiple rates(pay/bill rate) details based on assignment id and rate id
	while($mrrow = mysql_fetch_row($asgmnt_mulrates_details_res))
	{
		//Form the assignment rate details based on assid, rate master id and rate type 
		if($mrrow[3] == 'payrate')
		{
			$asgmnt_rate_details[$mrrow[1]][$mrrow[2]]['payrate'] = $mrrow[4];
		}
		else if($mrrow[3] == 'billrate')
		{
			$asgmnt_rate_details[$mrrow[1]][$mrrow[2]]['billrate'] = $mrrow[4];
		}
		
	}
	
	//Get the Burden Details list for the above build hrcon jobs sno if have any
	$asgmnt_burden_details_sql = 	"SELECT h.hrcon_jobs_sno,
						hj.pusername,
						bt.burden_type_name,
						bi.burden_item_name,
						bi.burden_value,
						bi.burden_mode,
						bi.ratetype,
						bi.max_earned_amnt,
						bi.taxable_status,
						bi.assigned_rateids,
						bt.calc_burden_on
					FROM hrcon_burden_details h
					JOIN hrcon_jobs hj ON hj.sno = h.hrcon_jobs_sno
					JOIN burden_types bt on bt.sno = h.bt_id AND bt.burden_type_name != 'Zero Bill Burden'
					JOIN burden_items bi ON bi.sno = h.bi_id
					WHERE h.ratetype='billrate' AND 
					h.hrcon_jobs_sno IN (".$hrconjobs_sno_str.")";
	$asgmnt_burden_details_res = mysql_query($asgmnt_burden_details_sql,$db);
	
	//Form the burden details in an array based on assignment ID
	while($birow = mysql_fetch_row($asgmnt_burden_details_res))
	{
		//Form the assignment burden details based on assignment ID 
		$asgmnt_burden_details_values[$birow[1]][] = array($birow[2],$birow[3],$birow[4],$birow[5],$birow[6],$birow[7],$birow[8],$birow[9],$birow[10]);
	}
	
	//Iterate Through the above formed assignment rates/Hrs 
	foreach($asgmnt_gen_details as $asgmntid=>$asgmntvals)
	{
		//Check if Burden Details exists for particular assignment
		if(isset($asgmnt_burden_details_values[$asgmntid]))
		{
			//Iterate through the assignment rates/Hrs details
			foreach($asgmntvals as $serdates=>$ratevals)
			{
				//Get the week ending date which is the key of assignments rates array
				$weekenddateexp = explode(" - ",$serdates);
				$weekenddate = $weekenddateexp[1];
				
				//Initialize the variables
				$totalBTChargeAmnt = 0; // For calculating the Total Amount for each row based on burden charges calculations 
				$btname = ""; //Burden Type Name
				$empname = $ratevals['empname']; //Employee Nanme
				$btchkflag = 0; //Flag to check atleast one burden item is calculated for that service dates
				$bttaxstatus = array();
				$getThresholdAmount = 0;//For maximum threshold value declaration
				//Iterate Through the Burden details 
				foreach($asgmnt_burden_details_values[$asgmntid] as $asgbtvals)
				{					
					//Split the rates assigned/selected for a particular burden item
					$btrates = explode(",",$asgbtvals[7]);
					
					//Burden type flag to identify whether need to calculate on regular pay/bill rate or selected rates while creating burden type
					$calc_burden_on = $asgbtvals[8];
					
					//Burden Type Name
					$btname = $asgbtvals[0];
					
					//Check whether Burden On of Burden Items is Payrate/BillRate/Hours and do the calculation basedon that
					if($asgbtvals[4] == 'payrate' || $asgbtvals[4] == 'billrate') // IF the Burden On field is payrate/bill rate
					{
						//Iterate through the rates assignment for burden items to do the calculation 
						foreach($btrates as $c)
						{
							//Check if the burden items assigned rate is selected and have the hours calculated in assignment rate for that service dates
							if(array_key_exists($c,$ratevals))
							{
								$totalRateAmnt = 0;
																
								//If burden Mode is Percentage
								if($asgbtvals[3] == 'percentage')
								{
									/*
									Formulae : 
									For Percentage - Burden % of Regular Pay/Bill Rate * Total rate hours
									A = Regular Pay/Bill Rate * Total Rate Hours
									B = Burden Value/100 * A
									For Flat - Burden Value * Total rate hours
										
									*/
									//If Burden On is Pay rate									
									if($asgbtvals[4] == 'payrate')
									{
										//Get the Regular Pay Rate of particular assignment
										$payrateval = 0;
										if($calc_burden_on == 'Regular')
										{
										if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
										{
											$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
										}
										}
										else
										{
											$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
										}
										$totalRateAmnt = $payrateval * $ratevals[$c];
									}
									else if($asgbtvals[4] == 'billrate') //If Burden On is Bill rate
									{
										//Get the Regular Bill Rate of particular assignment
										$billrateval = 0;
										if($calc_burden_on == 'Regular')
										{
										if(isset($asgmnt_rate_details[$asgmntid]['rate1']['billrate']))
										{
											$billrateval = $asgmnt_rate_details[$asgmntid]['rate1']['billrate'];
										}
										}
										else
										{
											$billrateval = $asgmnt_rate_details[$asgmntid][$c]['billrate'];
										}
										$totalRateAmnt = $billrateval * $ratevals[$c];
									}
										
									$totalRateAmnt = ROUND(($asgbtvals[2]/100) * $totalRateAmnt,2);
								}
								else if($asgbtvals[3] == 'flat') // IF the Burden Mode is Flat Amount
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
									
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
								
							}
						}
					}
					else if($asgbtvals[4] == 'hours')
					{
						foreach($btrates as $c)
						{
							if(array_key_exists($c,$ratevals))
							{
								/*
									Formulae : 
									For Percentage - Burden % * Total rate hours of Rate ID Bill Rate
									A = Burden Value/100 * Total Rate Hours
									B = Regular Pay Rate * A
									For Flat - Burden Value * Total rate hours
									
								*/
								$totalRateAmnt = 0;	
															
								if($asgbtvals[3] == 'percentage')
								{
									//Get the Regular Pay Rate of particular assignment
									$payrateval = 0;
									if($calc_burden_on == 'Regular')
									{
									if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
									{
										$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
									}
									}
									else
									{
										$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
									}
									$totalRateAmnt = (($asgbtvals[2]/100) * $ratevals[$c]);
									$totalRateAmnt = ROUND($payrateval * $totalRateAmnt,2);
										
								}
								else if($asgbtvals[3] == 'flat')
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
							}
						}
					}
					else if($asgbtvals[4] == 'uom_units')
					{
						foreach($btrates as $c)
						{
							if(array_key_exists($c,$ratevals))
							{
								/*
									Formulae : 
									For Percentage - Burden % * Total rate units of Rate ID Bill Rate
									A = Burden Value/100 * Total Rate units
									B = Regular Pay Rate * A
									For Flat - Burden Value * Total rate units
									
								*/
								$totalRateAmnt = 0;	
															
								if($asgbtvals[3] == 'percentage')
								{
									//Get the Regular Pay Rate of particular assignment
									$payrateval = 0;
									if($calc_burden_on == 'Regular')
									{
									if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
									{
										$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
									}
									}
									else
									{
										$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
									}
									$totalRateAmnt = (($asgbtvals[2]/100) * $ratevals[$c]);
									$totalRateAmnt = ROUND($payrateval * $totalRateAmnt,2);
										
								}
								else if($asgbtvals[3] == 'flat')
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
							}
						}
					}
				}
				//If Totalrate amount is less than Threshold value then charges should display as same as Totalrate amount. If greater then Threshold value then charges should display as same as Threshold value.
				if($getThresholdAmount != '0' && $totalBTChargeAmnt > $getThresholdAmount)
				{
					$totalBTChargeAmnt = $getThresholdAmount;
				}
				//Form the burden charges array if atleast any one item is calculated
				if($btchkflag == 1)
				{		
					if(in_array("Yes",$bttaxstatus))
					{
						$btchargeTax = "yes";
					}
					else
					{
						$btchargeTax = "";
					}

					$burden_charges_values = $burden_charges_values + number_format($totalBTChargeAmnt,2,".","");
				}
			}
		}
	}
	
	return $burden_charges_values;
}
function displayCreateInvoiceForApprover($s_date, $e_date, $cdate, $duedate, $template_Check) {

	global $db, $loc_clause, $assignmentsUsed, $assignmentsUsedTotal, $invtype, $invlocation, $invdept, $invservicedate, $invservicedateto,$username;
	$decimalPref    = getDecimalPreference();

    $deptAccessObj = new departmentAccess();
    $deptAccesSnoBO = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	// FOR TIMESHEETS
	$tim_query	= "SELECT
					FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'), FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(timesheet.sdate)),'%Y-%m-%d'),
					timesheet.client, hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_list.sno, staffacc_cinfo.templateid, staffacc_cinfo.tax, staffacc_cinfo.sno,
					CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, timesheet.assid, hrcon_jobs.sno,
					CONCAT(staffacc_contact.fname, '  ', staffacc_contact.lname),hrcon_jobs.manager,staffacc_cinfo.override_tempid,'' as charge 
				FROM
					par_timesheet, timesheet_hours AS timesheet
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=timesheet.client
					LEFT JOIN staffacc_list ON staffacc_list.username=staffacc_cinfo.username
					LEFT JOIN emp_list ON emp_list.username=timesheet.username
					LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername=timesheet.assid
					LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username AND hrcon_compen.ustatus='active'
					LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
					LEFT JOIN staffacc_contact ON hrcon_jobs.manager = staffacc_contact.sno
					LEFT JOIN staffacc_contactacc ON hrcon_jobs.manager = staffacc_contactacc.con_id
				    WHERE
					".$loc_clause."
					timesheet.username = hrcon_jobs.username AND hrcon_jobs.client = timesheet.client
					AND timesheet.client != '' AND hrcon_jobs.client != '0' AND hrcon_jobs.manager != '0' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel')
					AND timesheet.parid = par_timesheet.sno AND timesheet.type != 'EARN' AND timesheet.billable = 'Yes'
					AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved'
					AND ".tzRetQueryStringDate('par_timesheet.sdate', 'YMDDate', '-')." >= '".$s_date."'
					AND ".tzRetQueryStringDate('par_timesheet.edate', 'YMDDate', '-')." <= '".$e_date."'
					AND emp_list.lstatus !='DA' AND emp_list.lstatus != 'INACTIVE' AND staffacc_cinfo.type IN ('CUST','BOTH') AND department.sno !='0' AND department.sno IN (".$deptAccesSnoBO.")
					AND staffacc_contactacc.status='ACTIVE'
					
				GROUP BY
					hrcon_jobs.manager , timesheet.client
				ORDER BY
					timesheet.sdate ASC";

	$tim_res	= mysql_query($tim_query, $db);
	$row_count	= @mysql_num_rows($tim_res);
	$column_count	= @mysql_num_fields($tim_res);

	$grid	= "<script>\n";
	$grid	.= "var actcol = [";

	for ($i = 0; $i < $column_count; $i++) {

		if ($i == $column_count - 1) {

			$grid	.= "\""."\"";

		} else {

			$grid	.= "\""."\",";
		}
	}

	$grid	.= "];\n";
	$grid	.= "var actdata = [\n";

	$j	= 0;

	$time		= 0;
	$expense	= 0;
	$charge		= 0;
	$amountdue	= 0;
	$placementfee		= 0;
	$check_status_grid	= 0;

	$clientuser	= '';
	$empusercheck	= '';
	
        $manager_list	= '';
	$template_Check_arr	= explode('|', $template_Check);

	$Time_sno	= $template_Check_arr[0];
	$Exp_sno	= $template_Check_arr[1];
	$Charge_sno	= $template_Check_arr[2];

	$TiExCh_Val	= $Time_sno.'^'.$Exp_sno.'^'.$Charge_sno;
	$TiExCh_Val	= str_replace("','", '-', $TiExCh_Val);

	while ($result = @mysql_fetch_array($tim_res)) {
		if($result[16]!='0')
			{
				$result[7] = $result[16];
			}

		$start_date	= $result[0];
		$end_date	= $result[1];
		$client_id	= $result[2];
		$po_number	= $result[3];
		$user_name	= $result[4];
		$customer_name	= $result[5];
		$customer_id	= $result[6];
		$template_id	= $result[7];
		$tax_value	= $result[8];
		$cinfo_sno	= $result[9];
		$location	= $result[10];
		$department	= $result[11];
		$assignment_id	= $result[12];
		$hrcon_sno	= $result[13];
                $manager	= $result[14];
		$manager_sno = $result[15];
		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($template_id);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax	= true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			if (empty($clientuser)) {

				$clientuser	.= $client_id;

			} else {

				$clientuser	.= "','".$client_id;
			}

			if (empty($empusercheck)) {

				$empusercheck	.= $user_name;

			} else {

				$empusercheck	.= "','".$user_name;
			}

			
            if (empty($manager_list)) {

				if (!empty($manager_sno)) {

					$manager_list	.= "'". $manager_sno ."'";
				}

			} else {

				if (!empty($manager_sno)) {

					$manager_list	.= ", '". $manager_sno ."'";
				}
			}
			$timedate_emp		    = getTimedate_manager($e_date, $s_date, $client_id, $manager_sno, $db);
			$expensedate_emp	    = getExpensedate_manager($e_date, $s_date, $client_id, $manager_sno, $db);
			$placementfeedate_emp	= getPlacementFeedate_manager($e_date, $s_date, $client_id, $manager_sno, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];

			$timeAmounts	= getTimeManager($e_date, $s_date, $client_id, $db, $Time_sno, '', $manager_sno,$template_id);
			$expense	= getExpenseManager($e_date, $s_date, $client_id, $db, $Exp_sno, '', $manager_sno);

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];

			$timeRowsCount		= getTimeRowsCount_manager($e_date, $s_date, $client_id,$manager_sno ,$db);
			$expenseRowsCount	= getExpenseRowsCount_manager($e_date, $s_date, $client_id,$manager_sno ,$db);

			$timeExpenseRowCount	= NULL;

			if ($timeRowsCount || $expenseRowsCount) {

				$timeExpenseRowCount	= 'Y';

			} else {

				$timeExpenseRowCount	= 'N';
			}

			$charge		    = getCharges_manager($start_date, $end_date, $client_id,$manager_sno, $db);
			$placementfee	= getPlacementFeeManager($e_date, $s_date, $client_id, $db, $Charge_sno, '', $manager_sno);

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$pque	= "SELECT 
							inv_col_perdiem_chk 
						FROM 
							IT_Columns 
							LEFT JOIN Invoice_Template ON IT_Columns.inv_col_sno = Invoice_Template.invtmp_columns
						WHERE
							Invoice_Template.invtmp_sno = '".$template_id."'";

			$pres	= mysql_query($pque, $db);
			$prow	= mysql_fetch_row($pres);

			$perDiemTot	= getPerDiem_manager($e_date, $s_date, $client_id,$manager_sno, $db, $Time_sno, $prow[0]);
			$burdenchargeamt = getBurdenChargesData_manager($db, $client_id,$Time_sno, $s_date, $e_date,'',$manager_sno);
			
			$totalcharges = $charge+$placementfee+$perDiemTot+$burdenchargeamt;			  
			$amountdue	= $time + $expense + $charge + $placementfee + $perDiemTot + $burdenchargeamt;
			$getSubToTDue	= $amountdue;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(",",$assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;	// Get before tax discount amount sum...

			if ($discForCustomer == 'Y') {

				while ($drow = mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],$decimalPref,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),$decimalPref,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),$decimalPref,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),$decimalPref,'.','');
							$totalDiscAmount	+= number_format($drow[0],$decimalPref,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),$decimalPref,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],$decimalPref,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue,$decimalPref, '.', '') > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno&manager_sno=$manager_sno";

				$grid	.= "[";
				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$result[8].'|'.$getSubToTDue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[]  id=cliid[] value=".$client_id."><input type=hidden name=auidsrowscount[] value=".$timeExpenseRowCount."><input type=hidden name=cliname[] value=".gridcell($customer_name)."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($manager)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$invoiceCount."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .= "\"".number_format($totalcharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";

				if ($invoiceCount > 1) {

					
					$grid.="\""."invoiceall.php?val=redirect&invtype=Assignment_Approver&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$result[2]&manager_sno=$manager_sno\"";
				} else {
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
						$grid	.= "\"".$temp_type."?".$qstr."\"";
					
				}

				$j++;

				if ($j == $row_count) {

					$grid	.= "]\n";
					$check_status_grid	= 1;

				} else {

					$grid	.= "],";
					$check_status_grid	= 0;
				}

			} else {

				$j++;
			}
		}
	}

	$j	= 0;
    
	// FOR EXPENSES
	$exp_query	= "SELECT 
					FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'), FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(expense.edate)),'%Y-%m-%d'),
					expense.client, hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_list.sno, staffacc_cinfo.templateid,
					staffacc_cinfo.tax, staffacc_cinfo.sno, CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, expense.assid, hrcon_jobs.sno,CONCAT(staffacc_contact.fname, '  ', staffacc_contact.lname),hrcon_jobs.manager,staffacc_cinfo.override_tempid
				FROM
					par_expense
					LEFT JOIN expense ON expense.parid = par_expense.sno
					LEFT JOIN emp_list ON emp_list.username = par_expense.username
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno = expense.client
					LEFT JOIN staffacc_list ON staffacc_list.username = staffacc_cinfo.username
					LEFT JOIN hrcon_jobs ON hrcon_jobs.pusername = expense.assid
					LEFT JOIN hrcon_compen ON hrcon_compen.username = emp_list.username AND hrcon_compen.ustatus='active'
					LEFT JOIN contact_manage ON hrcon_compen.location = contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
					LEFT JOIN staffacc_contact ON hrcon_jobs.manager = staffacc_contact.sno
					LEFT JOIN staffacc_contactacc ON hrcon_jobs.manager = staffacc_contactacc.con_id
					
				WHERE
					".$loc_clause."
					par_expense.username = hrcon_jobs.username AND hrcon_jobs.client = expense.client
					AND expense.client != '' AND expense.status IN ('Approve', 'Approved')
					AND hrcon_jobs.client != '0'  AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel')
					AND hrcon_jobs.manager NOT IN (".$manager_list.") AND expense.billable = 'bil'
					AND par_expense.astatus IN ('Approve','Approved','ER')
					AND DATE_FORMAT(par_expense.sdate,'%Y-%m-%d') >= '".$s_date."'
					AND DATE_FORMAT(par_expense.edate,'%Y-%m-%d')<='".$e_date."' AND emp_list.lstatus != 'DA'
					AND emp_list.lstatus != 'INACTIVE'
					AND staffacc_contactacc.status='ACTIVE'
					
				GROUP BY
					hrcon_jobs.manager, expense.client
				ORDER BY 
					expense.edate ASC";

	$exp_res	= mysql_query($exp_query, $db);
	$row_count	= @mysql_num_rows($exp_res);

	while ($srow = mysql_fetch_row($exp_res)) {
		if($srow[16]!='0')
			{
				$srow[7] = $srow[16];
			}
 
		$start_date	= $srow[0];
		$end_date	= $srow[1];
		$client_id	= $srow[2];
		$po_number	= $srow[3];
		$user_name	= $srow[4];
		$customer_name	= $srow[5];
		$customer_id	= $srow[6];
		$template_id	= $srow[7];
		$tax_value	= $srow[8];
		$cinfo_sno	= $srow[9];
		$location	= $srow[10];
		$department	= $srow[11];
		$assignment_id	= $srow[12];
		$hrcon_sno	= $srow[13];
        $manager	= $srow[14];
		$manager_sno = $srow[15];
		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($template_id);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax	= true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			if (empty($clientuser)) {

				$clientuser	.= $client_id;

			} else {

				$clientuser	.= "','".$client_id;
			}

			if (empty($empusercheck)) {

				$empusercheck	.= $user_name;

			} else {

				$empusercheck	.= "','".$user_name;
			}

			
            if (empty($manager_list)) {

				if (!empty($manager_sno)) {

					$manager_list	.= "'". $manager ."'";
				}

			} else {

				if (!empty($manager_sno)) {

					$manager_list	.= ", '". $manager_sno ."'";
				}
			}
			$timedate_emp		= getTimedate_manager($e_date, $s_date, $client_id,$manager_sno, $db);
			$expensedate_emp	= getExpensedate_manager($e_date, $s_date, $client_id, $manager_sno,$db);
			$placementfeedate_emp	= getPlacementFeedate_manager($e_date, $s_date, $client_id,$manager_sno, $db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];

			$timeAmounts	= getTimeManager($e_date, $s_date, $client_id, $db, $Time_sno, '', $manager_sno);
			$expense	= getExpenseManager($e_date, $s_date, $client_id, $db, $Exp_sno, '', $manager_sno);
			$charge		= getCharges_manager($start_date, $end_date, $client_id, $manager_sno,$db);
			$placementfee	= getPlacementFeeManager($e_date, $s_date, $client_id, $db, $Charge_sno, '', $manager_sno);

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];
			$amountdue		= $time + $expense + $charge + $placementfee;
			$expcharges	= $charge+$placementfee;

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(',', $assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;	// Get before tax discount amount sum...

			if ($discForCustomer == 'Y') {

				while ($drow=mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');
							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],2,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue, $decimalPref, '.', '') > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno&manager_sno=$manager_sno";

				if ($check_status_grid == 1) {

					$grid	.= ",[";

				} else {

					$grid	.= "[";
				}

				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$tax_value.'|'.$amountdue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$client_id."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($manager)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$invoiceCount."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .="\"".number_format($expcharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";

				if ($invoiceCount > 1) {

					$grid	.= "\""."invoiceall.php?val=redirect&invtype=Assignment_Approver&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno&manager_sno=$manager_sno\"";

				} else {
					$temp_type = getDefaultTemp_Type($template_id,'new');
					$grid	.= "\"".$temp_type."?".$qstr."\"";
					
				}

				$j++;

				if ($j == $row_count) {

					$grid	.= "]\n";
					$check_status_grid	= 2;

				} else {

					$grid	.= "],";
					$check_status_grid	= 0;
				}

			} else {

				$j++;
			}
		}
	}

	$j	= 0;

	$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate	= date("m-d-Y",$thisday);

	$quedirect	= "SELECT GROUP_CONCAT(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect	= mysql_query($quedirect,$db);
	$rowdirect	= mysql_fetch_row($resdirect);
	$snodirect	= $rowdirect[0];

	// FOR CHARGES
	$chg_query	= "SELECT 
					hrcon_jobs.client, MIN(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', NOW(), STR_TO_DATE(hrcon_jobs.s_date, '%m-%d-%Y'))),
					MAX(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', NOW(), STR_TO_DATE(hrcon_jobs.s_date, '%m-%d-%Y'))),
					hrcon_jobs.po_num, emp_list.username, staffacc_cinfo.cname, staffacc_cinfo.templateid, staffacc_cinfo.sno,
					CONCAT(contact_manage.loccode,' - ',contact_manage.heading), department.deptname, hrcon_jobs.pusername, hrcon_jobs.sno,CONCAT(staffacc_contact.fname, '  ', staffacc_contact.lname),hrcon_jobs.manager,staffacc_cinfo.override_tempid
				FROM
					hrcon_jobs
					LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username
					LEFT JOIN hrcon_compen ON (hrcon_jobs.username=hrcon_compen.username AND hrcon_compen.ustatus='active')
					LEFT JOIN staffacc_cinfo ON staffacc_cinfo.sno=hrcon_jobs.client
					LEFT JOIN staffacc_list ON staffacc_cinfo.username=staffacc_list.username
					LEFT JOIN contact_manage ON hrcon_compen.location=contact_manage.serial_no
					LEFT JOIN department ON hrcon_compen.dept=department.sno
					LEFT JOIN staffacc_contact ON hrcon_jobs.manager = staffacc_contact.sno
					LEFT JOIN staffacc_contactacc ON hrcon_jobs.manager = staffacc_contactacc.con_id  AND staffacc_contactacc.status = 'ACTIVE'
					
				WHERE
					".$loc_clause."
					hrcon_jobs.ustatus='closed' AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL)
					AND hrcon_jobs.manager != '' AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client NOT IN (0,'')
					AND hrcon_jobs.manager NOT IN (".$manager_list.")
					AND STR_TO_DATE(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y') >= '".$s_date."'
					AND STR_TO_DATE(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' ) <= '".$e_date."'
					
					
				GROUP BY 
					hrcon_jobs.manager, hrcon_jobs.client";

	$chg_res	= mysql_query($chg_query, $db);
	$chg_count	= @mysql_num_rows($chg_res);

	while ($srow = mysql_fetch_row($chg_res)) {
		if($srow[14]!='0')
			{
				$srow[6] = $srow[14];
			}

		$client_id	= $srow[0];
		$start_date	= $srow[1];
		$end_date	= $srow[2];
		$po_number	= $srow[3];
		$user_name	= $srow[4];
		$customer_name	= $srow[5];
		$template_id	= $srow[6];
		$cinfo_sno	= $srow[7];
		$location	= $srow[8];
		$department	= $srow[9];
		$assignment_id	= $srow[10];
		$hrcon_sno	= $srow[11];
        $manager	= $srow[12];
		$manager_sno = $srow[13];
		$assignmentsUsed	= array();

		$noTimeTax	= false;
		$noExpenseTax	= false;
		$noChargeTax	= false;

		$tpl_array_values	= genericTemplate($cinfo_sno);
		$template_Timesheet	= $tpl_array_values[4];

		foreach ($template_Timesheet as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noTimeTax = true;
				}
			}
		}

		$template_Expense	= $tpl_array_values[5];

		foreach ($template_Expense as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noExpenseTax	= true;
				}
			}
		}

		$template_Charges	= $tpl_array_values[6];

		foreach ($template_Charges as $key => $values) {

			if ($key == 'Tax') {

				if ($values[0] == 'N') {

					$noChargeTax	= true;
				}
			}
		}

		if (!empty($start_date) && !empty($end_date)) {

			$timedate_emp		= getTimedate_manager($e_date, $s_date, $client_id,$manager_sno, $db);
			$expensedate_emp	= getExpensedate_manager($e_date, $s_date, $client_id, $manager_sno,$db);
			$placementfeedate_emp	= getPlacementFeedate_manager($e_date, $s_date, $client_id, $manager_sno,$db);

			$timedate		= explode('|', $timedate_emp);
			$expensedate		= explode('|', $expensedate_emp);
			$placementfeedate	= explode('|', $placementfeedate_emp);

			$td[0]	= $timedate[0];
			$td[1]	= $timedate[1];

			$ed[0]	= $expensedate[0];
			$ed[1]	= $expensedate[1];

			$pd[0]	= $placementfeedate[0];
			$pd[1]	= $placementfeedate[1];

			$maxmindate	= getMaxMindate($td[0], $td[1], $ed[0], $ed[1], $pd[0], $pd[1]);

			$arr_maxmindate	= explode('|', $maxmindate);

			$sintdate	= explode('-', $arr_maxmindate[0]);
			$cservicedateto	= $sintdate[1].'/'.$sintdate[2].'/'.$sintdate[0];

			$eintdate	= explode('-', $arr_maxmindate[1]);
			$cservicedate	= $eintdate[1].'/'.$eintdate[2].'/'.$eintdate[0];

			$timeAmounts	= getTimeManager($e_date, $s_date, $client_id, $db, $Time_sno, '', $manager_sno);
			$expense	= getExpenseManager($e_date, $s_date, $client_id, $db, $Exp_sno, '', $manager_sno);

			$time		= $timeAmounts[0];
			$taxTime	= $timeAmounts[1];
			$taxTimeSnos	= $timeAmounts[2];

			$charge		= getCharges_manager($start_date, $end_date, $client_id, $manager_sno,$db);
			$placementfee	= getPlacementFeeManager($e_date, $s_date, $client_id, $db, $Charge_sno, '', $manager_sno);
			$amountdue	= $time + $expense + $charge + $placementfee;
			$plscharges=$charge+$placementfee;	

			//Calculating the total amount including tax based on template.
			$taxdiscForCustomer	= getCustomerTaxDisc($template_id);
			$expForCustomer		= explode('|', $taxdiscForCustomer);
			$taxForCustomer		= $expForCustomer[0];
			$discForCustomer	= $expForCustomer[1];

			$getFieldsTotal	= $time + $expense + $charge + $placementfee;

			if ($noTimeTax) {

				$timeTaxTotal	= 0;

			} else {

				$timeTaxTotal	= $taxTime;
			}

			if ($noExpenseTax) {

				$expenseTaxTotal	= 0;

			} else {

				$expenseTaxTotal	= $expense;
			}

			if ($noChargeTax) {

				$chargeTaxTotal		= 0;
				$placementfeeTaxTotal	= 0;

			} else {

				$chargeTaxTotal		= $charge;
				$placementfeeTaxTotal	= $placementfee;
			}

			$getTaxesFieldsTotal	= $timeTaxTotal + $expenseTaxTotal + $chargeTaxTotal + $placementfeeTaxTotal;

			$assignmentsUsed	= array_unique($assignmentsUsed);
			$asgnSnos		= implode(',', $assignmentsUsed);
			$custAsgnIds		= $client_id.'|'.$asgnSnos;

			$getAlertForMultipleInvoice	= getAlertForMultipleInvoice($custAsgnIds, '');
			$getArrayForInvoiceCount	= getIndividualAssignmentGroups($asgnSnos, $getAlertForMultipleInvoice);

			$invoiceCount	= count($getArrayForInvoiceCount);

			$discountTaxFlatChk	= '';

			if ($getAlertForMultipleInvoice == 'Split') {

				$discountTaxFlatChk	= " AND rp.amountmode != 'FLAT' ";
			}

			$thisday	= mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
			$todaydate	= date('Y-m-d', $thisday);

			$tque	= "SELECT 
							rp.amount, rp.amountmode
						FROM 
							customer_discounttaxes cdt, company_tax ct, rates_period rp
						WHERE 
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = ct.taxid 
							AND ct.status = 'active' AND cdt.status = 'active' AND rp.parentid = ct.sno AND rp.parenttype = 'TAX'
							AND cdt.type = 'CompanyTax' ".$discountTaxFlatChk."
							AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$tres	= mysql_query($tque, $db);

			$dque	= "SELECT
							rp.amount, rp.amountmode, rp.taxmode
						FROM
							customer_discounttaxes cdt, company_discount cd, rates_period rp
						WHERE
							cdt.customer_sno = '".$client_id."' AND cdt.tax_discount_id = cd.discountid
						AND cd.status = 'active' AND cdt.status = 'active' AND rp.parentid = cd.sno
						AND rp.parenttype = 'DISCOUNT' AND cdt.type = 'Discount' ".$discountTaxFlatChk."
						AND IF(rp.enddate = '0000-00-00 00:00:00', DATEDIFF('".$todaydate."',STR_TO_DATE(rp.startdate, '%Y-%m-%d')) >= 0, '".$todaydate."' BETWEEN STR_TO_DATE(rp.startdate, '%Y-%m-%d') AND STR_TO_DATE(rp.enddate, '%Y-%m-%d'))";

			$dres	= mysql_query($dque, $db);

			$totalTaxAmount		= 0;
			$totalDiscAmount	= 0;
			$btDiscTotal	= 0.00;

			if ($discForCustomer == 'Y') {

				while ($drow = mysql_fetch_row($dres)) {

					if ($drow[2] == 'at') {

						if ($drow[1] == 'PER') {

							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}

					} else {

						if ($drow[1] == 'PER') {

							$btDiscTotal		= number_format(($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /100)),2,'.','');// Get before tax discount amount sum...
							$totalDiscAmount	+= number_format((($amountdue * $drow[0]) /100),2,'.','');

						} else {

							$btDiscTotal		= number_format($btDiscTotal + (($getTaxesFieldsTotal * $drow[0]) /$amountdue),2,'.','');
							$totalDiscAmount	+= number_format($drow[0],2,'.','');
						}
					}
				}

			} else {

				$totalDiscAmount	= 0;
			}

			$newTaxableAmount	= $getTaxesFieldsTotal - $btDiscTotal;// Get before tax taxable amount sum...

			if ($taxForCustomer == 'Y') {

				while ($trow = mysql_fetch_row($tres)) {

					if ($trow[1] == 'PER') {

						$totalTaxAmount	+= number_format((($newTaxableAmount * $trow[0]) /100),2,'.','');

					} else {

						if ($timeTaxTotal > 0 || $expenseTaxTotal > 0 || $chargeTaxTotal > 0 || $placementfeeTaxTotal > 0) {

							$totalTaxAmount	+= number_format($trow[0],2,'.','');
						}
					}
				}

			} else {

				$totalTaxAmount	= 0;
			}

			if ($amountdue > 0) {

				$amountdue	= ($amountdue + $totalTaxAmount) - $totalDiscAmount;
			}

			if (!empty($template_id)) {

				$templateName	= getTemplateName($template_id);

			} else {

				$templateName	= getDefaultTemp_Name();
			}

			if (number_format($amountdue, $decimalPref,".", "") > 0) {

				$assignmentsUsed	= array_unique($assignmentsUsed);
				$asgnSnos	= implode(',', $assignmentsUsed);

				$qstr	= "stat=prev&indate=$cdate&duedate=$duedate&invtype=$invtype&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&serfdate1=$cservicedateto&sertodate1=$cservicedate&client=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno&manager_sno=$manager_sno";

				if ($check_status_grid == 1 || $check_status_grid == 2) {

					$grid	.= ",[";

				} else {

					$grid	.= "[";
				}

				$grid	.= "\"<label class='container-chk'><input type=checkbox name=auids[] OnClick=chk_clearTop() value='".urldecode($client_id).'|'.$cservicedateto."-".$cservicedate.'||'.$TiExCh_Val.'|'.$amountdue.'|'.$srow[13].'|'.$amountdue.'|'.$newTaxableAmount.'|'.$taxTimeSnos.'|'.$asgnSnos."' id=auids[]><span class='checkmark'></span></label><input type=hidden name=cliid[] id=cliid[] value=".$client_id."><input type=hidden name=invcount[] value=".$invoiceCount.">\",";

				$grid	.= "\"".gridcell($manager)."\",";
				$grid	.= "\"".gridcell($cinfo_sno)."\",";
				$grid	.= "\"".gridcell($customer_name)."\",";
				$grid	.= "\"".$invoiceCount."\",";
				$grid	.= "\"".$cservicedateto."-".$cservicedate."\",";
				$grid	.= "\"".number_format($time, 2,".", "")."\",";
				$grid   .="\"".number_format($plscharges, 2,".", "")."\",";
				$grid	.= "\"".number_format($expense, $decimalPref,".", "")."\",";
				$grid	.= "\"".number_format($amountdue, $decimalPref,".", "")."\",";
				$grid	.= "\"".gridcell(stripslashes($templateName))."\",";
				$grid	.= "\"".gridcell($location)."\",";
				$grid	.= "\"".gridcell($department)."\",";

				if ($invoiceCount > 1) {

					$grid	.= "\""."invoiceall.php?val=redirect&invtype=Assignment_Approver&invlocation=$invlocation&invdept=$invdept&invservicedate=$invservicedate&invservicedateto=$invservicedateto&selClient=$client_id&asmt_id=$assignment_id&jobsno=$hrcon_sno&manager_sno=$manager_sno\"";

				} else {
					$temp_type = getDefaultTemp_Type($template_id,'new');
					
					$grid	.= "\"".$temp_type."?".$qstr."\"";
					
				}

				$check_status_grid	= 3;

				$j++;

				if ($j == $chg_count) {

					$grid	.= "]\n";

				} else {

					$grid	.= "],";
				}

			} else {

				$j++;
			}
		}
	}

	$grid	= trim($grid, ',');
	$grid	.= "];\n";
	$grid	.= "</script>\n";

	return $grid;
}
Function getTimeManager($cdate,$ttdate,$name,$db,$Time_sno,$chkPusernames = '', $manager = '',$templateId='')
{
	global $assignmentsUsed,$assignmentsUsedTotal;

	$manager_clause	= '';

	if (!empty($manager)) {

		$manager_clause	= " AND hrcon_jobs.manager = '".$manager."' ";
	}
 
	if($chkPusernames != '')
		$condAdd = "AND timesheet.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	$reqclient=$name; 
	$count=0;
	if($Time_sno != '')
		$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";
	
	$grp_personId ='';
	if($templateId != ''){
		$grp_personId = get_personGrping_basedTemp($templateId);
	}
	$eque = "SELECT SUM(timesheet.hours), hrcon_jobs.sno, timesheet.hourstype, GROUP_CONCAT(timesheet.sno) FROM timesheet_hours AS timesheet LEFT JOIN emp_list ON timesheet.username=emp_list.username LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid AND hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno = timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate>='".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND  par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND hrcon_jobs.client=timesheet.client ".$template_Time_Check."  ".$condAdd." ".$manager_clause." GROUP BY timesheet.parid,timesheet.assid,timesheet.hourstype".$grp_personId." ORDER BY emp_list.name, timesheet.sdate";
	
	$eres=mysql_query($eque,$db);
	$count=0;
	$tamount = 0;
	$taxAmount = 0;
	$timeAmounts = array();
	$timeModSnos = "";
       	while($erow=mysql_fetch_row($eres))
	{	
		$getRates = "SELECT '', multiplerates_assignment.rate,multiplerates_assignment.period, ROUND((ROUND(CAST('".$erow[0]."' AS DECIMAL(12,2)),2) * IF(multiplerates_assignment.period='YEAR',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*261)),2), IF(multiplerates_assignment.period='MONTH',ROUND(( CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*(261/12))),2),IF(multiplerates_assignment.period='WEEK',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/(8*5)),2),IF(multiplerates_assignment.period='DAY',ROUND((CAST(multiplerates_assignment.rate AS DECIMAL(12,2))/8),2),ROUND(CAST( multiplerates_assignment.rate AS DECIMAL(12,2)),2)))))),2),multiplerates_assignment.taxable AS Taxable FROM  multiplerates_assignment WHERE multiplerates_assignment.asgnid = '".$erow[1]."' AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = '".$erow[2]."' AND multiplerates_assignment.ratetype = 'billrate'";
		$resRates = mysql_query($getRates,$db);
		$rowRates = mysql_fetch_array($resRates);
		
                if($rowRates[2]=="FLATFEE")
		{
			if($erow[0] != 0)
				$regrate = number_format($rowRates[1],2,'.','');
			else
				$regrate = 0.00;
		}
		else
			$regrate = number_format($rowRates[3],2,'.','');//number_format(($erow[3]*$trate),2,'.','');
			
		if($regrate != 0){	
			if($chkPusernames == '')
				$assignmentsUsedTotal[] = $erow[1];
			
			$assignmentsUsed[] = $erow[1];
		}
		
		$tamount += $regrate;

		
		if($rowRates['Taxable'] == 'Y')
		{
			$taxAmount += $regrate;
			if($timeModSnos == "")
				$timeModSnos = $erow[3];
			else
				$timeModSnos .= ",".$erow[3];
		}
		
	}
	$timeAmounts[0] = $tamount; 
	$timeAmounts[1] = $taxAmount;
	$timeAmounts[2] = $timeModSnos;
	return $timeAmounts;
}
Function getExpenseManager($cdate,$ttdate,$name,$db,$Exp_sno,$chkPusernames = '', $manager = '')
{
	global $assignmentsUsed,$assignmentsUsedTotal;
	$reqclient=$name;

	$manager_clause	= '';

	if (!empty($manager)) {

		$manager_clause	= " AND hrcon_jobs.manager = '".$manager."' ";
	}

	if($chkPusernames != '')
		$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	if($Exp_sno != '')
		$template_Expense_Check = " AND expense.client NOT IN ('".$Exp_sno."')";

		
	$eque="select expense.sno, hrcon_jobs.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') ".$template_Expense_Check." ".$condAdd." ".$manager_clause." order by expense.edate";
	$eres1=mysql_query($eque,$db);
	$exp=0;
	while($erow=mysql_fetch_row($eres1))
	{	
		$expRowRate = getExpenseRate($erow[0],$db);
		if($expRowRate > 0){	
			if($chkPusernames == '')
				$assignmentsUsedTotal[] = $erow[1];
			
			$assignmentsUsed[] = $erow[1];
		}
		$exp=$exp+$expRowRate;
	}


	return $exp;
}
Function getPlacementFeeManager($cdate,$ttdate,$name,$db,$Charge_sno,$chkPusernames = '', $manager = '')
{
	global $assignmentsUsed,$assignmentsUsedTotal;

	$manager_clause	= '';

	if (!empty($manager)) {

		$manager_clause	= " AND hrcon_jobs.manager = '".$manager."' ";
	}

	if($chkPusernames != '')
		$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	$todaydate=date("m-d-Y",$thisday);
	
	if($Charge_sno != '')
		$template_Charge_Check = " AND hrcon_jobs.client NOT IN ('".$Charge_sno."')";
		
	$quedirect = "SELECT group_concat(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
	$resdirect = mysql_query($quedirect,$db);
	$rowdirect = mysql_fetch_row($resdirect);
	$snodirect = $rowdirect[0];
		
	$sque="select hrcon_jobs.client,hrcon_jobs.pusername,hrcon_jobs.placement_fee,hrcon_jobs.s_date,hrcon_jobs.username,hrcon_jobs.sno from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y')  AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL )  AND hrcon_jobs.ustatus in ('closed') AND str_to_date(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )>='".$ttdate."' and str_to_date(IF(hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date), '%m-%d-%Y' )<='".$cdate."' AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' AND hrcon_jobs.client='".$name."' ".$condAdd." ".$template_Charge_Check." ".$manager_clause; 
	$eres=mysql_query($sque,$db);
	$placementfee = 0;
			
	while($erow=mysql_fetch_row($eres))
	{		
		if($chkPusernames == '')
			$assignmentsUsedTotal[] = $erow[5];
		
		$assignmentsUsed[] = $erow[5];
		
		$que1 = "SELECT SUM(amount) FROM credit_charge WHERE username='".$erow[4]."' AND pusername='".$erow[1]."'";
		$res1 = mysql_query($que1,$db);
		$rrow1 = mysql_fetch_row($res1);	
		if( $rrow1[0] >= $erow[2])
			$placementfee1 = 0;
		else
			$placementfee1 = $erow[2]-$rrow1[0];
		$placementfee += $placementfee1;
	}
	return $placementfee;
}
Function getTimedate_manager($cs2,$cs1,$client,$manager,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
		
		$qu="select MIN(par_timesheet.sdate),MAX(par_timesheet.edate) from timesheet_hours left join par_timesheet on (timesheet_hours.parid=par_timesheet.sno)left join invoice on (invoice.sno=timesheet_hours.billable) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet_hours.assid and hrcon_jobs.username=timesheet_hours.username) where hrcon_jobs.manager = '".$manager."'  and timesheet_hours.client='".$client."' and par_timesheet.astatus IN ('ER','Approved') AND timesheet_hours.status = 'Approved' and timesheet_hours.billable='Yes' and par_timesheet.sdate>='".$cs1."' and par_timesheet.edate<='".$cs2."' ".$condAdd." group by invoice.sno" ;
		
		$res=mysql_query($qu,$db);
		$dd=mysql_fetch_row($res);
		
		$date1 = $dd[0]."|".$dd[1];
		return $date1;
	}
Function getExpensedate_manager($cs2,$cs1,$client,$manager,$db,$chkPusernames = '')
{
		if($chkPusernames != '')
			$condAdd = "AND expense.assid IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$qu1="select MIN(par_expense.sdate),MAX(par_expense.edate) from expense left join par_expense on (expense.parid=par_expense.sno) left join invoice on (invoice.sno=expense.billable) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where hrcon_jobs.manager = '".$manager."'  and expense.client='".$client."' and par_expense.astatus IN ('ER','Approved') AND expense.status='Approved' and expense.billable='bil' and (expense.edate >='".$cs1."' AND expense.edate <= '".$cs2."') ".$condAdd." group by expense.client";
		$res1=mysql_query($qu1,$db);
		$dd1=mysql_fetch_row($res1);
		
		$date1 = $dd1[0]."|".$dd1[1];
		return $date1;
}
Function getPlacementFeedate_manager($cs2,$cs1,$client,$manager,$db,$chkPusernames = '')
	{
		if($chkPusernames != '')
			$condAdd = "AND hrcon_jobs.pusername IN ('".str_replace(",","','",$chkPusernames)."')";
		else
			$condAdd = "";
			
		$thisday=mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		$todaydate=date("m-d-Y",$thisday);
		
		$quedirect = "SELECT GROUP_CONCAT(sno) FROM manage WHERE name IN ('Direct','Temp/Contract to Direct') AND type='jotype'";
		$resdirect = mysql_query($quedirect,$db);
		$rowdirect = mysql_fetch_row($resdirect);
		$snodirect = $rowdirect[0];
		
        $qu2="select MIN( 
        date_format( str_to_date( IF (
        hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )
        ), MAX( 
        date_format( str_to_date( IF (
        hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' )
        )
        from hrcon_jobs LEFT JOIN emp_list ON emp_list.username=hrcon_jobs.username where hrcon_jobs.manager = '".$manager."'  and (emp_list.lstatus='INACTIVE' || emp_list.empterminated='Y') AND hrcon_jobs.ustatus in ('active','closed') AND (hrcon_jobs.assg_status='' || hrcon_jobs.assg_status IS NULL ) AND hrcon_jobs.jotype IN (".$snodirect.") AND hrcon_jobs.client='".$client."' AND (date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' ) 
	 >='".$cs1."' AND date_format( str_to_date( IF (
	hrcon_jobs.s_date = '' || hrcon_jobs.s_date = '0-0-0', '".$todaydate."', hrcon_jobs.s_date ), '%m-%d-%Y' ) , '%Y-%m-%d' ) 
	 <= '".$cs2."') AND hrcon_jobs.placement_fee != '0.00' AND hrcon_jobs.placement_fee!= '0' AND IFNULL(hrcon_jobs.placement_fee,'') != '' ".$condAdd." group by hrcon_jobs.client";
		$res2=mysql_query($qu2,$db);
		$dd2=mysql_fetch_row($res2);
		
		$date1 = $dd2[0]."|".$dd2[1];
		return $date1;
	}
	Function getTimeRowsCount_manager($cdate,$ttdate,$name,$manager,$db)
	{
		$reqclient=$name;
		$count=0;
		
		$eque="SELECT SUM(timesheet.hours) FROM timesheet_hours AS timesheet LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid and hrcon_jobs.username=timesheet.username) LEFT JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) WHERE timesheet.client!='' AND timesheet.type!='EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client='".$reqclient."' AND timesheet.billable='Yes' AND par_timesheet.sdate >= '".$ttdate."' AND par_timesheet.edate<='".$cdate."' AND par_timesheet.astatus IN ('ER','Approved') AND timesheet.status = 'Approved' AND hrcon_jobs.manager='".$manager."' AND hrcon_jobs.client=timesheet.client ".$template_Time_Check." GROUP BY timesheet.parid,timesheet.assid,timesheet.hourstype";
		$eres=mysql_query($eque,$db);
		return mysql_num_rows($eres)>0;
	}
	Function getExpenseRowsCount_manager($cdate,$ttdate,$name,$manager,$db)
	{
		$reqclient=$name;
		$eque="select expense.sno from expense LEFT JOIN par_expense on (par_expense.sno= expense.parid) LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername=expense.assid) where hrcon_jobs.manager = '".$manager."' AND  expense.edate >='".$ttdate."' and expense.edate <= '".$cdate."' and expense.billable='bil' and expense.client='".$reqclient."' and par_expense.astatus in ('Approve','Approved','ER') AND expense.status = 'Approved' and hrcon_jobs.client=expense.client and hrcon_jobs.ustatus IN ('active', 'cancel', 'closed') order by expense.edate";
		$eres1=mysql_query($eque,$db);
		return mysql_num_rows($eres1)>0;
	}
	Function getCharges_manager($cdate,$ttdate,$name,$manager,$db)
	{
		$que="select amount from credit_charge left join hrcon_jobs ON hrcon_jobs.pusername=credit_charge.pusername where client_name='".$name."' and ser_date > '".$ttdate."' and ser_date <= '".$cdate."' and hrcon_jobs.manager='".$manager."'";
		$res=mysql_query($que,$db);
		$rate=0;
		while($row=mysql_fetch_row($res))
		{
			$rate=$rate+$row[0];
		}
		return $rate;
	}
	Function getPerDiem_manager($cdate,$ttdate,$name,$manager,$db,$Time_sno,$perDiem_chk,$chkPusernames = '')
{
	$reqclient=$name;
	$parDiemTOT = 0.00;
	
	if($chkPusernames != '')
		$condAdd = "AND timesheet_hours.assid IN ('".str_replace(",","','",$chkPusernames)."')";
	else
		$condAdd = "";
	
	$template_Time_Check = "";
	
	if($perDiem_chk == 'Y')
	{	
		if($Time_sno != '')
			$template_Time_Check = " AND timesheet_hours.client NOT IN ('".$Time_sno."')";
		
		$eque="SELECT timesheet_hours.assid,timesheet_hours.parid,hrcon_jobs.diem_billrate,hrcon_jobs.diem_billable, hrcon_jobs.diem_period, IF(timesheet_hours.edate = '0000-00-00',1,DATEDIFF(timesheet_hours.edate,timesheet_hours.sdate)+1) AS Days FROM timesheet_hours LEFT JOIN hrcon_jobs ON (hrcon_jobs.pusername = timesheet_hours.assid AND hrcon_jobs.username = timesheet_hours.username) LEFT JOIN emp_list ON timesheet_hours.username=emp_list.username WHERE timesheet_hours.client != '' AND timesheet_hours.type != 'EARN' AND hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND hrcon_jobs.client = '".$reqclient."' AND timesheet_hours.billable = 'Yes' AND timesheet_hours.sdate >= '".$ttdate."' AND timesheet_hours.sdate <= '".$cdate."' AND timesheet_hours.status = 'Approved' AND hrcon_jobs.manager='".$manager."' ".$template_Time_Check." ".$condAdd." GROUP BY timesheet_hours.sdate,timesheet_hours.assid";
		
		$eres=mysql_query($eque,$db);
	
		$asgnListArrr = array();
		
		while($erow=mysql_fetch_row($eres))
		{			
			if($erow[3]=="Y")
			{
				$perDiemDays = $erow[5];
				
				if($erow[4]=="")
					$erow[4] = "DAY";
				
				if($erow[4] == "FLATFEE")
				{					
					if(!in_array($erow[1],$asgnListArrr))
					{
						$parDiemTotal = $erow[2];
						$asgnListArrr[] = $erow[1];
					}
					else
						$parDiemTotal = 0.00;
				}				
				else
				{					
					$perDayAmount = calculateAmountTotal($erow[2],$erow[4],'day');
					
					$parDiemTotal = $perDayAmount * $perDiemDays;
				}				
			}
			else
				$parDiemTotal = 0.00;
			
			$parDiemTOT = $parDiemTOT + $parDiemTotal;
		}
	}
	
	return $parDiemTOT;
}
/**
 * Function used to get the burden charges amount to display in Total Amount for the invoice on the grid
 *		
 * @param	Numeric $client_id - Customer/Client ID assiciated to assignment 
 * @param	string $Time_sno - Customer/Client ID assiciated to timesheet 
 * @param	Dates $ser_fr_date,ser_to_date - Service dates filter in the grid (from & to)
 * @param	string $chkPusernames - timesheet assignment ids seperated by comma
 * @param	string $empuser - timesheet username 
 * @return	Float $burden_charges_values
 */
function getBurdenChargesData_manager($db,$client_id,$Time_sno,$ser_fr_date,$ser_to_date,$chkPusernames = '',$manager = '')
{
	//Initializing the variables for calcualting the burden details for the invoice items
	$asgmnt_gen_details = array(); // form the assignment total hours and name based on assignment ID & service dates
	$asgmnt_rate_details = array(); //form the assignment multiple rate details basd on assignment id and ratemaster id
	$asgmnt_burden_details_values = array(); // form the burden details based on assignment ID
	$burden_charges_values = 0; //form the burden charges with calculations	
	$hrconjobs_sno_str = ""; // capture the list of hrcon jobs sno string
	$bc_ratewise_amnts = array(); //capture the burden charges total amount based on assignment and service dates and rate master ids (rate1/rate2/..etc.,)
	
	$condAdd = "";
	$template_Time_Check = "";
	
	if($chkPusernames != '')
		$condAdd = " AND timesheet.assid IN ('".str_replace(",","','",$chkPusernames)."')";

	if($Time_sno != '')
		$template_Time_Check = " AND timesheet.client NOT IN ('".$Time_sno."')";
	
	if($manager != '')
		$managercond = " AND hrcon_jobs.manager='".$manager."'";
	
	$get_burdencharges_ts_sql = "SELECT emp_list.name,
										timesheet.assid,
										SUM(timesheet.hours),
										".tzRetQueryStringDate('par_timesheet.sdate','Date','/').",
										".tzRetQueryStringDate('par_timesheet.edate','Date','/').",
										timesheet.hourstype,
										hrcon_jobs.sno,
										multiplerates_assignment.ratemasterid,
										multiplerates_assignment.ratetype,
										multiplerates_assignment.rate

										FROM timesheet_hours as timesheet
										JOIN emp_list ON timesheet.username=emp_list.username 
										JOIN hrcon_jobs ON (hrcon_jobs.pusername=timesheet.assid AND hrcon_jobs.username = timesheet.username) 
										JOIN multiplerates_assignment ON (multiplerates_assignment.asgnid = hrcon_jobs.sno AND multiplerates_assignment.asgn_mode = 'hrcon' AND multiplerates_assignment.status = 'ACTIVE' AND multiplerates_assignment.ratemasterid = timesheet.hourstype AND multiplerates_assignment.ratetype = 'billrate') 
										JOIN par_timesheet ON (par_timesheet.sno=timesheet.parid) 

										WHERE
										hrcon_jobs.client = '".$client_id."' AND 
										timesheet.status = 'Approved' AND 
										timesheet.type != 'EARN' AND 
										timesheet.billable = 'Yes' AND 
										hrcon_jobs.ustatus IN ('active', 'closed', 'cancel') AND 
										timesheet.sdate >= '".$ser_fr_date."' AND 
										timesheet.sdate <= '".$ser_to_date."' 
										".$condAdd." 
										".$template_Time_Check." 
										".$managercond." 

										GROUP BY 
										timesheet.parid,timesheet.assid,timesheet.hourstype ";
	$get_burdencharges_ts_res = mysql_query($get_burdencharges_ts_sql,$db);	

	while($bc_details = mysql_fetch_array($get_burdencharges_ts_res))
	{
		$act_service_dates = $bc_details[3]." - ".$bc_details[4];
		//Form the assignment key based on service dates for burden charges
		$asgmnt_key = $act_service_dates;
		
		//capture the 3D array basd on assignment ID and Service Dates for Emp Name/Rates/Total Hrs
		$asgmnt_gen_details[$bc_details[1]][$asgmnt_key]['empname'] = $bc_details[0];		
		//Calculate the total hours for each rate type
		if(isset($asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]]))
		{
			$asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]] += $bc_details[2];
		}
		else
		{
			$asgmnt_gen_details[$bc_details[1]][$asgmnt_key][$bc_details[5]] = $bc_details[2];
		}
		//concatename the hrconjobs sno value to get the burden details for calculating the burden charges
		$hrconjobs_sno_str .= $bc_details[6].",";
	
	}
	$hrconjobs_sno_str = substr($hrconjobs_sno_str,0,strlen($hrconjobs_sno_str)-1); // remove the last appended comma (,)
	
	//Get the rates for the above formed hrcon jobs
	$asgmnt_mulrates_details_sql = "SELECT hj.sno,
										   hj.pusername,
										   mr.ratemasterid,
										   mr.ratetype,
										   mr.rate
										FROM multiplerates_assignment mr
										JOIN hrcon_jobs hj ON hj.sno = mr.asgnid
										WHERE mr.asgnid IN (".$hrconjobs_sno_str.") AND mr.asgn_mode='hrcon' AND mr.status='ACTIVE'
										   ";
	$asgmnt_mulrates_details_res = mysql_query($asgmnt_mulrates_details_sql,$db);
	
	//Form the multiple rates(pay/bill rate) details based on assignment id and rate id
	while($mrrow = mysql_fetch_row($asgmnt_mulrates_details_res))
	{
		//Form the assignment rate details based on assid, rate master id and rate type 
		if($mrrow[3] == 'payrate')
		{
			$asgmnt_rate_details[$mrrow[1]][$mrrow[2]]['payrate'] = $mrrow[4];
		}
		else if($mrrow[3] == 'billrate')
		{
			$asgmnt_rate_details[$mrrow[1]][$mrrow[2]]['billrate'] = $mrrow[4];
		}
		
	}
	
	//Get the Burden Details list for the above build hrcon jobs sno if have any
	$asgmnt_burden_details_sql = 	"SELECT h.hrcon_jobs_sno,
						hj.pusername,
						bt.burden_type_name,
						bi.burden_item_name,
						bi.burden_value,
						bi.burden_mode,
						bi.ratetype,
						bi.max_earned_amnt,
						bi.taxable_status,
						bi.assigned_rateids,
						bt.calc_burden_on
					FROM hrcon_burden_details h
					JOIN hrcon_jobs hj ON hj.sno = h.hrcon_jobs_sno
					JOIN burden_types bt on bt.sno = h.bt_id AND bt.burden_type_name != 'Zero Bill Burden'
					JOIN burden_items bi ON bi.sno = h.bi_id
					WHERE h.ratetype='billrate' AND 
					h.hrcon_jobs_sno IN (".$hrconjobs_sno_str.")";
	$asgmnt_burden_details_res = mysql_query($asgmnt_burden_details_sql,$db);
	
	//Form the burden details in an array based on assignment ID
	while($birow = mysql_fetch_row($asgmnt_burden_details_res))
	{
		//Form the assignment burden details based on assignment ID 
		$asgmnt_burden_details_values[$birow[1]][] = array($birow[2],$birow[3],$birow[4],$birow[5],$birow[6],$birow[7],$birow[8],$birow[9],$birow[10]);
	}
	
	//Iterate Through the above formed assignment rates/Hrs 
	foreach($asgmnt_gen_details as $asgmntid=>$asgmntvals)
	{
		//Check if Burden Details exists for particular assignment
		if(isset($asgmnt_burden_details_values[$asgmntid]))
		{
			//Iterate through the assignment rates/Hrs details
			foreach($asgmntvals as $serdates=>$ratevals)
			{
				//Get the week ending date which is the key of assignments rates array
				$weekenddateexp = explode(" - ",$serdates);
				$weekenddate = $weekenddateexp[1];
				
				//Initialize the variables
				$totalBTChargeAmnt = 0; // For calculating the Total Amount for each row based on burden charges calculations 
				$btname = ""; //Burden Type Name
				$empname = $ratevals['empname']; //Employee Nanme
				$btchkflag = 0; //Flag to check atleast one burden item is calculated for that service dates
				$bttaxstatus = array();
				$getThresholdAmount = 0;//For maximum threshold value declaration
				//Iterate Through the Burden details 
				foreach($asgmnt_burden_details_values[$asgmntid] as $asgbtvals)
				{					
					//Split the rates assigned/selected for a particular burden item
					$btrates = explode(",",$asgbtvals[7]);
					
					//Burden type flag to identify whether need to calculate on regular pay/bill rate or selected rates while creating burden type
					$calc_burden_on = $asgbtvals[8];
					
					//Burden Type Name
					$btname = $asgbtvals[0];
					
					//Check whether Burden On of Burden Items is Payrate/BillRate/Hours and do the calculation basedon that
					if($asgbtvals[4] == 'payrate' || $asgbtvals[4] == 'billrate') // IF the Burden On field is payrate/bill rate
					{
						//Iterate through the rates assignment for burden items to do the calculation 
						foreach($btrates as $c)
						{
							//Check if the burden items assigned rate is selected and have the hours calculated in assignment rate for that service dates
							if(array_key_exists($c,$ratevals))
							{
								$totalRateAmnt = 0;
																
								//If burden Mode is Percentage
								if($asgbtvals[3] == 'percentage')
								{
									/*
									Formulae : 
									For Percentage - Burden % of Regular Pay/Bill Rate * Total rate hours
									A = Regular Pay/Bill Rate * Total Rate Hours
									B = Burden Value/100 * A
									For Flat - Burden Value * Total rate hours
										
									*/
									//If Burden On is Pay rate									
									if($asgbtvals[4] == 'payrate')
									{
										//Get the Regular Pay Rate of particular assignment
										$payrateval = 0;
										if($calc_burden_on == 'Regular')
										{
										if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
										{
											$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
										}
										}
										else
										{
											$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
										}
										$totalRateAmnt = $payrateval * $ratevals[$c];
									}
									else if($asgbtvals[4] == 'billrate') //If Burden On is Bill rate
									{
										//Get the Regular Bill Rate of particular assignment
										$billrateval = 0;
										if($calc_burden_on == 'Regular')
										{
										if(isset($asgmnt_rate_details[$asgmntid]['rate1']['billrate']))
										{
											$billrateval = $asgmnt_rate_details[$asgmntid]['rate1']['billrate'];
										}
										}
										else
										{
											$billrateval = $asgmnt_rate_details[$asgmntid][$c]['billrate'];
										}
										$totalRateAmnt = $billrateval * $ratevals[$c];
									}
										
									$totalRateAmnt = ROUND(($asgbtvals[2]/100) * $totalRateAmnt,2);
								}
								else if($asgbtvals[3] == 'flat') // IF the Burden Mode is Flat Amount
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
									
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
								
							}
						}
					}
					else if($asgbtvals[4] == 'hours')
					{
						foreach($btrates as $c)
						{
							if(array_key_exists($c,$ratevals))
							{
								/*
									Formulae : 
									For Percentage - Burden % * Total rate hours of Rate ID Bill Rate
									A = Burden Value/100 * Total Rate Hours
									B = Regular Pay Rate * A
									For Flat - Burden Value * Total rate hours
									
								*/
								$totalRateAmnt = 0;	
															
								if($asgbtvals[3] == 'percentage')
								{
									//Get the Regular Pay Rate of particular assignment
									$payrateval = 0;
									if($calc_burden_on == 'Regular')
									{
									if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
									{
										$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
									}
									}
									else
									{
										$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
									}
									$totalRateAmnt = (($asgbtvals[2]/100) * $ratevals[$c]);
									$totalRateAmnt = ROUND($payrateval * $totalRateAmnt,2);
										
								}
								else if($asgbtvals[3] == 'flat')
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
							}
						}
					}
					else if($asgbtvals[4] == 'uom_units')
					{
						foreach($btrates as $c)
						{
							if(array_key_exists($c,$ratevals))
							{
								/*
									Formulae : 
									For Percentage - Burden % * Total rate units of Rate ID Bill Rate
									A = Burden Value/100 * Total Rate units
									B = Regular Pay Rate * A
									For Flat - Burden Value * Total rate units
									
								*/
								$totalRateAmnt = 0;	
															
								if($asgbtvals[3] == 'percentage')
								{
									//Get the Regular Pay Rate of particular assignment
									$payrateval = 0;
									if($calc_burden_on == 'Regular')
									{
									if(isset($asgmnt_rate_details[$asgmntid]['rate1']['payrate']))
									{
										$payrateval = $asgmnt_rate_details[$asgmntid]['rate1']['payrate'];
									}
									}
									else
									{
										$payrateval = $asgmnt_rate_details[$asgmntid][$c]['payrate'];
									}
									$totalRateAmnt = (($asgbtvals[2]/100) * $ratevals[$c]);
									$totalRateAmnt = ROUND($payrateval * $totalRateAmnt,2);
										
								}
								else if($asgbtvals[3] == 'flat')
								{
									$totalRateAmnt = ROUND($asgbtvals[2] * $ratevals[$c],2);
								}
								//If one or more burder types are there then highest bill value should be considered
								if($asgbtvals[5] != '0.00' && $asgbtvals[5] > $getThresholdAmount)
								{
									$getThresholdAmount = $asgbtvals[5];
								}
								
								$btchkflag = 1;
								$totalBTChargeAmnt += $totalRateAmnt;
								$bttaxstatus[] = $asgbtvals[6];
							}
						}
					}
				}
				//If Totalrate amount is less than Threshold value then charges should display as same as Totalrate amount. If greater then Threshold value then charges should display as same as Threshold value.
				if($getThresholdAmount != '0' && $totalBTChargeAmnt > $getThresholdAmount)
				{
					$totalBTChargeAmnt = $getThresholdAmount;
				}
				//Form the burden charges array if atleast any one item is calculated
				if($btchkflag == 1)
				{		
					if(in_array("Yes",$bttaxstatus))
					{
						$btchargeTax = "yes";
					}
					else
					{
						$btchargeTax = "";
					}

					$burden_charges_values = $burden_charges_values + number_format($totalBTChargeAmnt,2,".","");
				}
			}
		}
	}
	
	return $burden_charges_values;
}
function get_personGrping_basedTemp($template_sno){
	global $db;
	$ts_persId='';
	$temp_que = "select inv_tmptype_sno from Invoice_Template where invtmp_sno='".$template_sno."'";
	$temp_res = mysql_query($temp_que,$db);
	$temp_row = mysql_fetch_row($temp_res);
	if($temp_row[0] == '3' || $temp_row[0] == '4' || ($temp_row[0] == '5' && THERAPY_SOURCE_ENABLED == 'Y')){
		$ts_persId .= ',timesheet.person_id';
	}
	if($temp_row[0] == '4'){
		$ts_persId .= ',timesheet.sdate';
	}
	return $ts_persId;
}
?>