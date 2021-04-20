<?php
	require("global.inc");

	$result = "";
	if($sts == 'dept')
	{
		$i = 0;
		$deptAccessObj = new departmentAccess();
		if ($fromModule == "FO") {
			$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
		}else{
			$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");
		}
		

		$result.="<select name=\"dept\" id=\"dept\" style=\"width: 150px;\" onchange=\"getDeptAccSetup('acc');\">";
		$result.="\t<option value=\""."\">-- Select Department --</option>\n";

		if($locid>0)
		{
			$sque = "SELECT sno,if(depcode!='',CONCAT_WS(' - ',depcode,deptname),deptname) FROM department WHERE (loc_id = '".$locid."' OR deflt = 'Y') AND status='Active' AND sno !='0' AND sno IN (".$deptAccesSno.") ORDER BY deflt";
			$sres  = mysql_query($sque,$db);
			while($srow = mysql_fetch_row($sres))
			{
				if($i == 0)
					$deptid = $srow[0];
				$result.="\t<option value=\"".$srow[0]."\">".$srow[1]."</option>\n";
				$i++;
			}
		}
		$result.="</select>|^^^AkkenSplit^^^|";
	}

	//These are Income Accounts
	$result.= "<select name=\"lstsetup1\" id=\"lstsetup1\" style=\"width:210px;\">\n";
	$result.= getAccountNumbers($locid,$deptid,'Income',$lst1,'','yes');
	$result.= "</select>|^^^AkkenSplit^^^|";

	//These are Expense Accounts
	$result.= "<select name=\"lstsetup2\" id=\"lstsetup2\" style=\"width:210px;\">\n";
	$result.= getAccountNumbers($locid,$deptid,'Expense',$lst2,$scat,'yes');
	$result.="</select>|^^^AkkenSplit^^^|";

	//These are Liability Accounts
	$result.= "<select name=\"lstsetup3\" id=\"lstsetup3\" style=\"width:210px;\">\n";
	$result.= getAccountNumbers($locid,$deptid,$scat,$lst3,'','yes');
	$result.= "</select>";

	echo $result;
?>