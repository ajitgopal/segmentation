<?php 
	//Set Error / Warning display YES (1) or NO (0)
	ini_set("display_errors","0");
    
	$app_inc_path= $_SERVER['DOCUMENT_ROOT'].'/include/';  //application include path
	
	//header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

	require_once("AC_FS_Session.php");
	$session = new AC_FS_Session();
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
	require_once("AC_FS_Cookie.php");
	require_once("global_fun.inc");
	require_once("broadbean_config.inc");
	require_once("clientSocket.inc");
    require_once("ac_password_hash.php");
    require_once("ac_password_policy.php");
    require_once("UploadHandler.php");
    require_once("class.DepartmentAccess.inc");
	apache_note('companyuser', $companyuser);
	apache_note('username', $username);

	if($username=="" || $companyuser=="")
	{
		if($wintype=="wifr" )
		{
			$wintype=(trim($wintype)!="") ? $wintype : "window";
			echo "<script>alert('AK0001: Oops! Looks like your session has been logged out. Please log back into your AkkenCloud\u2122 account.');top.window.location.href='".$default_expire_link."';</script>";
			exit();
		}
		else if(trim($xajax)=="gridData" || $Ajaxjunktimestump!="")
		{
			echo '^^^***GridAjaxSessionExpired***^^^|'.$default_expire_link;
			exit;
		}
		else
        {
			if(isset($_REQUEST['frm_src']) && isset($_REQUEST['frm_ws_id']) && $_REQUEST['frm_src'] == 'ols' && $_REQUEST['frm_ws_id'] != '' ){
		      
			  if($_REQUEST['frm_src'] == 'ols'){
					setCookie('ols_redirect',urlencode($_SERVER['REQUEST_URI']), time() + 3600, '/');
					Header("Location:$default_expire_link");
					exit();
				} else {
					Header("Location:$default_expire_link");
					exit();
				}
				
		   }else{
			echo "<script>
			
			
			alert('AK0001: Oops! Looks like your session has been logged out. Please log back into your AkkenCloud\u2122 account.');
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

	if($sesinsid && !$AllowMultipleSess)
	{
		$quelog="update userlog_acc set last_access=NOW() where sno='$sesinsid'";
		mysql_query($quelog,$db);

		if($pri_production)
			checkUserSession();

		require("ipblock.inc");
	}

	require("userDefine.php");
	require("xajax.inc");
	require("xajaxResponse.inc");

	require("ConvertCharset.class.php");
	require("showhex.inc");
	require("getUserInfo.inc");
	require("mailconfig.inc");
	require("trackEmails.inc");
	require("smtp.inc");

	$smtp=new smtp_class;
	$smtp->host_name=$hostname;			/* Change this variable to the address of the SMTP server to relay, like "smtp.myisp.com" */
	$smtp->host_port=$hostport;			/* Change this variable to the address of the SMTP server to relay, like "smtp.myisp.com" */
	$smtp->localhost="smtp.akken.com";	/* Your computer address */
	$smtp->esp=$ext_esp;

	if($ogsslchk=="Yes")
	{
		$smtp->ssl=1;
		$smtp->encrypt=$og_encrypt;
	}
	else
	{
		$smtp->ssl=0;
	}

	$smtp->pop3_auth_host=$pophost;		/* Set to the POP3 authentication host if your SMTP server requires prior POP3 authentication */
	$smtp->pop3_auth_port=$popport;		/* Set to the POP3 authentication port if your SMTP server requires prior POP3 authentication */
	$smtp->user=$popuser;				/* Set to the user name if the server requires authetication */
	//$smtp->realm=$realm;				/* Set to the authetication realm, usually the authentication user e-mail domain */
	$smtp->password=$poppwd;			/* Set to the authetication password */

	if($smtp->saveCopy)
		$smtp->imapExtSno=$imapExtSno;		/* Set external mail account sno if the type is IMAP to save sent items on IMAP Server*/

	////////////////////////////////////  START OF SMTP for internal submissions /////////////////////////////
	$smtp_internalSub=new smtp_class;
	$smtp_internalSub->host_name="smtp.akken.com";			/* Change this variable to the address of the SMTP server to relay, like "smtp.myisp.com" */
	$smtp_internalSub->host_port="25";			/* Change this variable to the address of the SMTP server to relay, like "smtp.myisp.com" */
	$smtp_internalSub->localhost="smtp.akken.com";	/* Your computer address */
	////////////////////////////////////   END OF SMTP for internal submissions /////////////////////////////
	
	// To set boundaries for Mixed/Related/Alternate parts in sending mails
	//$cur_time=time();
	$boun_mixed_part=md5(uniqid(microtime()));
	$boun_related_part=md5(uniqid(microtime()));
	$boun_alternative_part=md5(uniqid(microtime()));
	$cur_time=$boun_mixed_part;
	
	$decimalPref = getDecimalPreference(); //get the decimal preference value from company settings.

	/* 
		Personal Info Encryption & Decryption Project
	*/
	require_once("ac_encrypt_decrypt.php"); 
    if ($ac_aced->isValid() == false) {
    	print "<script> alert('PIED-001: There is a technical problem with the connection. Please refresh the screen and if the problem still persists, please try again after some time.'); </script>";
    	echo '<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#e9ece9; opacity:9;"><tr><td><div style="width:100%;text-align:center;"> PIED-001: There is a technical problem with the connection. Please refresh the screen and if the problem still persists, please try again after some time. </div></td></tr></table>';
    	exit();
    }
    
    $rules_info = "<ul><li>Should be between 8 and 64 characters</li><li>Should have at least one uppercase letter</li><li>Should have at least one lowercase letter</li><li>Should have at least one number</li><li>Should have at least one special character <span style='color:green'>(Cannot use '&' character)</span></li></ul>";
?>