<?php
	//Set Error / Warning display YES (1) or NO (0)
	ini_set("display_errors","0");
	set_time_limit(1200);

	header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

	if($def_seshash!="")
		$sessionid=$def_seshash;
		
	require_once("AC_FS_Session.php");
	$session = new AC_FS_Session(true, $sessionid);
	/* TLS-01202018 */
	foreach ($_SESSION as $ses_key=>$ses_val)
	{
		${$ses_key}=$ses_val;
	}
	
	require_once("config.inc");
	require_once("setServers.inc");
	require_once("getServers.inc");
	require("cdatabase.inc");
	require_once("wdocument_root.php");
	require_once("def_domain_config.inc");
	require_once("global_fun.inc");	
    require_once("class.DepartmentAccess.inc");
	
	apache_note('companyuser', $companyuser);
	apache_note('username', $username);

	if($username=="" || $companyuser=="")
	{
		if($wintype=="wifr" )
		{
			$wintype=(trim($wintype)!="") ? $wintype : "window";
			echo "<script>alert('Oops! Looks like your session has been logged out. Please log back into your AkkenCloud\u2122 account.');top.window.location.href='".$default_expire_link."';</script>";
			exit();
		}
		else if(trim($xajax)=="gridData" || $Ajaxjunktimestump!="")
		{
			echo '^^^***GridAjaxSessionExpired***^^^|'.$default_expire_link;
			exit;
		}
		else
		{
			echo "<script>
			alert('Oops! Looks like your session has been logged out. Please log back into your AkkenCloud\u2122 account.');
			try
			{
				if(!window.opener.location)
				{
					window.location.href= '".$default_expire_link."';
				}
				else
				{
					window.opener.location.href = '".$default_expire_link."';
					window.close();
				}
			}
			catch(oException)
			{
				window.location.href= '".$default_expire_link."';
			}
			</script>";
			exit();
		}
	}

	$qryuser="SELECT company_info.company_name,company_info.sno FROM capp_info,company_info WHERE capp_info.comp_id='$companyuser' AND capp_info.sno=company_info.sno AND company_info.status!='DA'";
	$resuser=mysql_query($qryuser,$maindb);
	if(mysql_num_rows($resuser)==0)
	{
		Header("Location:$default_deactive_link");
		exit();
	}
	else
	{
		$rowuser=mysql_fetch_row($resuser);
		$companyname=$rowuser[0];
		$company_info_sno=$rowuser[1];
	}

	require("maildatabase.inc");
	require("database.inc");

	//Redirect to default expire page if the user is deactivated

	$que="select status from users where username='$username'";
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);
	if($row[0]=="DA")
	{
		Header("Location:$default_deactive_link");
		exit();
	}

	require("userDefine.php");
	require("getUserInfo.inc");

	/* 
		Personal Info Encryption & Decryption Project
	*/
	require_once("ac_encrypt_decrypt.php");
?>
