<?php
	// Getting information about the Applicant -- jobtitle,type and email 

	// Modified the query, removed fetch data from applicants table and kept fetching from emp_list table -- kumar raju k.

   	$que="select type,'',email,name from emp_list where username='".$appuser."'";
   	$res=mysql_query($que,$db);
   	$row=mysql_fetch_row($res);
   	
	$emp_name=$row[3];
	$apptype=$row[0];
	$appjob=$row[1];
	$appmail=$row[2];

	$que="select email from hrcon_general where hrcon_general.ustatus='active' AND username='".$appuser."'";
   	$res1=mysql_query($que,$db);
   	$row1=mysql_fetch_row($res1);
   	$empemail=$row1[0];

	// Checking whether an account is existed for the Applicant as a Consultant

	if(strpos("*".$appuser,"app") || strpos("*".$appuser,"lead") || strpos("*".$appuser,"hr") || strpos("*".$appuser,"mar") ||strpos("*".$appuser,"con"))
	{
		// Not yet added as a consultant 
		$emp_type="PE";

		$que="insert into users (userid,name,type,status,cdate,um_cuser,um_activate_date,um_activate_user,usertype) values ('".addslashes($empuser)."','".addslashes($emp_name)."','".$emp_type."','Employee',now(),'".$username."',NOW(),'".$username."','$powerusertype')";
		mysql_query($que,$db);
		$empuserid=mysql_insert_id($db);
        if($empuserid){
            $updated = $acph->updateUserPassword('users', $emppass, ['username'=>$empuserid]);
            if(!$updated){
                print "<script>alert('Something went worng. Please try after some time.');</script>";
            }
        }
        /* Adding FO Department Permission to create user */
        $deptAccessObj = new departmentAccess();
        $deptAccessObj->instDepartmentFOPermissionForNewUsr($empuserid);
        /* END */

		$iqry = "INSERT INTO snsusers(userid,status,cuser,muser,cdate,mdate) VALUES('".$empuserid."','ACTIVE','".$username."','".$username."',NOW(),NOW())";
		mysql_query($iqry,$db);

		// For inserting into userstatus table for activate and deactivate information.
		$queUserStatus="insert into userstatus (sno,username,activated,activate_user) values ('','".$empuserid."',now(),'".$username."')";
		mysql_query($queUserStatus,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','inbox','system')";
        mysql_query($que,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','outbox','system')";
        mysql_query($que,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','sentmessages','system')";
        mysql_query($que,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','drafts','system')";
        mysql_query($que,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','trash','system')";
        mysql_query($que,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','unsubscribe','system')";
        mysql_query($que,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','failed','system')";
        mysql_query($que,$db);

        $que="insert into e_folder(fid,username,foldername,parent) values('','$empuserid','spam','system')";
        mysql_query($que,$db);

		$que="insert into sysuser (username, crm, hrm, collaboration, accounting, analytics, dashboard, admin, myprofile, plugin_qb, plugin_outlook, plugin_taxi) values ('".$empuserid."','".$syscrm."','".$syshrm."','".$syscollab."','".$sysaccount."','".$sysanalytics."','".$sysdashboard."','".$sysadmin."','".$sysmyprofile."','".$plugin_qb."','".$plugin_outlook."','".$plugin_taxi."')";
	    mysql_query($que,$db);

		//Query to update the LoginStatus of QB_Loginstatus to 0 if akken is selected
		if($plugin_outlook == "N")
		{
			$upd_loginstatus="UPDATE QB_LoginStatus SET LoginStatus=0 WHERE username='".$empuserid."'";
			mysql_query($upd_loginstatus,$db);
		}	
	}
	else
	{
		// After forwading from hiring->mngmt to emp->mngmt and creating account, deleting last login records for applicant tracking -> create user account.
		$del_que="delete from userlog_acc where username='".$appuser."'";
		mysql_query($del_que,$db);

		// Added as a consultant
		$que="update users set userid='".$empuser."',type='PE',status='Employee' ,um_muser='".$username."',um_mdate=now(),last_login='0000-00-00 00:00:00',usertype='$powerusertype' where username='".$appuser."'";
		mysql_query($que,$db);
		$empuserid=$appuser;
                if($empuserid){
                    $updated = $acph->updateUserPassword('users', $emppass, ['username'=>$empuserid]);
                    if(!$updated){
                        print "<script>alert('Something went worng. Please try after some time.');</script>";
                    }
                }

		//checking sysuser table wheter record there or not
		$sel_qry = "select * from sysuser where username = '".$empuserid."'";
		$res_qry = mysql_query($sel_qry,$db);	
		$num_qry = mysql_num_rows($res_qry);
		if($num_qry == 0)
		{
		   $que="insert into sysuser (username, crm, hrm, collaboration, accounting, analytics, dashboard, admin, myprofile, plugin_qb, plugin_outlook, plugin_taxi) values ('".$empuserid."','".$syscrm."','".$syshrm."','".$syscollab."','".$sysaccount."','".$sysanalytics."','".$sysdashboard."','".$sysadmin."','".$sysmyprofile."','".$plugin_qb."','".$plugin_outlook."','".$plugin_taxi."')";
	       mysql_query($que,$db);
		}
		else
		{	
			$que = "update sysuser set  crm = '".$syscrm."', hrm = '".$syshrm."', collaboration = '".$syscollab."', accounting = '".$sysaccount."', analytics = '".$sysanalytics."', dashboard = '".$sysdashboard."',admin= '".$sysadmin."', myprofile ='".$sysmyprofile."', plugin_qb='".$plugin_qb."', plugin_outlook='".$plugin_outlook."', plugin_taxi='".$plugin_taxi."' where username = '".$empuserid."'";
			mysql_query($que,$db);
	    }		

		//Query to update the LoginStatus of QB_Loginstatus to 0 if akken is selected
		if($plugin_outlook == "N")
		{
			$upd_loginstatus="UPDATE QB_LoginStatus SET LoginStatus=0 WHERE username='".$empuserid."'";
			mysql_query($upd_loginstatus,$db);
		}

		$sysque="select fname, mname, lname, email, wphone, hphone, mobile, fax, profiletitle, address1, address2, city, state, country, zip from hrcon_general where hrcon_general.ustatus='active' AND username = '".$empuserid."'";
		$sysres=mysql_query($sysque,$db);
		$arrdata=mysql_fetch_row($sysres);

		insUpdAppCRMContact($arrdata,$empuserid,'insert');

		if(strpos("*".$syscollab, "+1+")>0)
		{
			$que="insert into mail_editor(sno,username,editorname,autofill,lsdate) values ('','".$empuserid."','advanced','yes',0)";
			mysql_query($que,$db);
		}		 
	} 

	// Deleting Applicant data and Dumping into Employee Data (moving Applicant Info to Employee Info)

	updateAppData($appuser,$empuserid,$db);
	deleteAppData($appuser,$empuserid,$db);
	
	// To insert users as contacts of CRM companies of selected companies.
	$sysque="select fname, mname, lname, email, wphone, hphone, mobile, fax, profiletitle, address1, address2, city, state, country, zip from hrcon_general where hrcon_general.ustatus='active' AND username = '".$empuserid."'";
	$sysres=mysql_query($sysque,$db);
	$arrdata=mysql_fetch_row($sysres);
	
	insUpdAppCRMContact($arrdata,$empuserid,'insert');
	
	if(strpos("*".$syscollab, "+1+")>0)
	{
		$que="insert into mail_editor(sno,username,editorname,autofill,lsdate) values ('','".$empuserid."','advanced','yes',0)";
		mysql_query($que,$db);
	}
	
	$que="select emailuser from EmailAcc where username='$username'";
	$res=mysql_query($que,$db);
	$row=mysql_fetch_row($res);

	if(strpos("+".$syscollab."+","+1+"))
	{
	    //We have removed the following code for Sub Domain. We are restrict the entry into EmailAcc, Virtual,mailbox tables.
		/* //Removed the updating of EmailAcc table and kept direct inserting into the same table -- kumar raju k.
		$que= "INSERT into EmailAcc(username,emailuser,mquota,type) VALUES('".$empuserid."','".$user_mailid."','".$mbqs."','R')";
		mysql_query($que,$db);

		require("mailaccins.php");
		$to=$user_mailid."@".$domainname;

		// updating contacts to private
		$queup="update staffoppr_contact set accessto=owner where email='".$to."'";
        mysql_query($queup,$db); */
	}

	$dque = "SELECT CONCAT(hg.fname, hg.lname), u.userid FROM hrcon_general hg LEFT JOIN users u ON hg.username=u.username WHERE hg.ustatus='active' AND u.username='".$empuserid."'";
	$dres = mysql_query($dque,$db);
	$drow = mysql_fetch_row($dres);

	$new_user_info.=$drow[0]." (".$drow[1].") <BR>";

	//For updating sys admin company_info.totusers
	updateSysTotalUsers();

	//getting company name from company_info table
	$customerName	= getCompanyNameForUserMngmt();
	//getting CustomerID 
	//$customerID	= getCompanyID();
	$login_url = '';
   	$login_que = "SELECT contact_manage.login_url AS contact_manage_login_url FROM contact_manage 
				  LEFT JOIN hrcon_compen ON contact_manage.serial_no = hrcon_compen.location 
				  WHERE hrcon_compen.username = '".$empuserid."'
				  AND hrcon_compen.ustatus='active' 
				  AND contact_manage.status !='BP'";
    $login_res = mysql_query($login_que,$db);
    $login_rs = mysql_fetch_row($login_res);
    $url = $login_rs[0];
    $login_url    = ($url!= '')? '<a href="'.$url.'" target=_blank>'.$url.'</a>':'<a href=https://login.akken.com/ target=_blank>https://login.akken.com/</a><br><tr><td>Company ID : '.$companyuser.'</td></tr>';

	if($multiuser!="Yes")
	{
		require_once("new_user_mail_temp.inc");

		if($mailPrefPwrUsers == 'yes') 
		{
			$matter_body	= $pwrUserMatter;
			$matter = stripslashes(str_replace('{{@user_name}}', ucwords($emp_name), $matter_body));
			$matter = stripslashes(str_replace('{{@company_legal_name}}', ucwords($customerName), $matter));
			$matter = stripslashes(str_replace('{{@user_id}}', $empuser, $matter));
			$matter = stripslashes(str_replace('{{@password}}', $emppass, $matter));
			$matter = stripslashes(str_replace('{{@customer_login_url}}', $login_url, $matter));
			//$matter = stripslashes(str_replace('{{@company_id}}', $companyuser, $matter));

			//activity matter - removing password
			$activityMatter = stripslashes(str_replace('{{@user_name}}', ucwords($emp_name), $matter_body));
			$activityMatter = stripslashes(str_replace('{{@company_legal_name}}', ucwords($customerName), $activityMatter));
			$activityMatter = stripslashes(str_replace('{{@user_id}}', $empuser, $activityMatter));
			$activityMatter = stripslashes(str_replace('{{@password}}', '######', $activityMatter));
			$activityMatter = stripslashes(str_replace('{{@customer_login_url}}', $login_url, $activityMatter));
			//$activityMatter = stripslashes(str_replace('{{@company_id}}', $companyuser, $activityMatter));
			sendMailUserCreation($appmail,$matter,$appuser,$mailSubPwrUsers,$activityMatter);
		}

		session_unregister("appuser");
		session_unregister("cbox");
		session_unregister("emp_name");
		session_unregister("txbox");
		session_unregister("empuser");
		session_unregister("emppass");
		session_unregister("mbqs");
		session_unregister("user_mailid");
		session_unregister("no_user");
		session_unregister("avail_msize");
		session_unregister("powerusertype");

		session_unregister("mailPrefPwrUsers");
		session_unregister("pwrUserMatter");
		session_unregister("mailSubPwrUsers");

		Header("Location:userman.php?acctype=$acctype");
	}
?>