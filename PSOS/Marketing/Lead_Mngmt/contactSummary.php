<?php
	$addr1=$addr;
	$posid1=$posid;
        require("global.inc");
        if(isset($_COOKIE["contact_summary_opendivs"])) {
            $opendivs = $_COOKIE["contact_summary_opendivs"];
        } else {
            $opendivs= '1,2,8';
        }
	require("mysqlfun.inc");
	require("dispfunc.php");
	require("userDefine.php");
    require("config_social_urls.php");
	require_once($akken_psos_include_path.'getSummaryNotes.php');
	require_once("custom_grid_functions.php");
	require_once("multipleRatesClass.php");
	$custom_grid_function_obj = new CustomGridFunctions();
    $socialSrhObj   = new SocialSerchURLS();
	$grdi_details = $custom_grid_function_obj->getGridName("contacts",$username);
	
	if(!empty($grdi_details))
		$totcolumns = count($custom_grid_function_obj->asFields("contacts", $username))+1;
	else
		$totcolumns = 14;

    $module = isset($_GET['module'] )?$_GET['module']: '';


    if($module == 'CRM' || $module == '' || $module == undefined){
        $module = 'CRM';

    }else{
        $module = 'Admin_Contacts';
    }
	$addr=$addr1;
	$posid=$posid1;
	if($sesvar=="new")
        $edit_manage2="";

	if($con_id!="")
		$contasno=substr($con_id,4);

	// Following code added to fix the issue  Task ID: 5000
	if($flg=='report')
	{
		if(is_numeric($contasno))
			$addr=$contasno;
	}

	//Constant CRM_GROUPS is addded in the userdefine.php which returns 'Y'/'N' based on CRM preferences.
	if(CRM_GROUPS == "Y")
	{
		$group_add_link_str =  "javascript:addMoveToCRMGroup('crmContactSummary','Add','','CRM')";
	}

	if($nav=="candidate")
	{
        $que1="select sno, prefix, nickname, fname, mname, lname, email, wphone, hphone, mobile, fax, other, ytitle, csno, approveuser, status, dauser, dadate, stime, cat_id, bpsaid, accessto, ctype, source, department, certifications, codes, keywords, messengerid, spouse, suffix, address1, address2, city, state, country, zipcode, owner, mdate, muser, dontcall, dontemail, fcompany, sourcetype, reportto, crmcontact, maincontact, '', '', '',".tzRetQueryStringDTime("stime","Date","/").",".tzRetQueryStringDTime("mdate","Date","/").",IF (accessto = 'ALL', 'public',IF (accessto = '".$username."', 'private', IF(LOCATE( ',', accessto ) >0,'share','noaccess'))),wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,spouse_name,source_name,reportto_name,deptid from staffoppr_contact where sno='".$addr."'";
        $con_id="oppr".$addr;
	}
	else
	{
    	$que1="select sno, prefix, nickname, fname, mname, lname, email, wphone, hphone, mobile, fax, other, ytitle, csno, approveuser, status, dauser, dadate, stime, cat_id, bpsaid, accessto, ctype, source, department, certifications, codes, keywords, messengerid, spouse, suffix, address1, address2, city, state, country, zipcode, owner, mdate, muser, dontcall, dontemail, fcompany, sourcetype, reportto, crmcontact, maincontact, '', '', '',".tzRetQueryStringDTime("stime","Date","/").",".tzRetQueryStringDTime("mdate","Date","/").",IF (accessto = 'ALL', 'public',IF (accessto = '".$username."', 'private', IF(LOCATE( ',', accessto ) >0,'share','noaccess'))),wphone_extn,hphone_extn,other_extn,other_info,email_2,email_3,description,importance,spouse_name,source_name,reportto_name,deptid from staffoppr_contact where sno='".$contasno."'";
    	$con_id="oppr".$contasno;
    }
	$res1=mysql_query($que1,$db);
	$row1=mysql_fetch_row($res1);

	$merge_sql = ' SELECT MAX( sno ) FROM contact_doc WHERE con_id = "oppr'.$addr.'" AND title LIKE \'merged_contacts.rtf\'';
	$merge_res = mysql_query($merge_sql);
	$merge_row = mysql_fetch_row($merge_res);
	$mergeRecord = $merge_row[0] ? '<a href="#" style="text-decoration: underline;" onclick="javascript:editWin(\'editdoc.php?addr='.$contasno.'&sno='.$merge_row[0].'&con_id=oppr'.$contasno.'&hidemodule='.$module.'\')" >Yes</a>' : 'No';
	
	$contact_name=html_tls_entities($row1[3])." ".html_tls_entities($row1[4])." ".html_tls_entities($row1[5]);
    //$conemail=dispTextdb($row1[3]);
    $row1[6] = stripslashes($row1[6]);
    $email   = $row1[6];
    
    if($email!="")
    {
        $checkdomain=getContactDomain($email);
    }
    else
    {
        $checkdomain='ALL';
    }
    
    $shareval=$row1[21];
    $accessValue = $row1[52];
    $deptname=getManage($row1[24]);
    $nickdis=$row1[13];
    $type1=$row1[19];
    $wphone=$row1[7];
	$hphone=$row1[8];
	$mobile=$row1[9];
	$fax=$row1[10];
	$other=$row1[11];

	/* //this is the query for getting  all users
    $Users_Sql="select us.username,us.name,su.crm from users us LEFT JOIN sysuser su ON (us.username = su.username AND su.crm!='NO') WHERE us.status != 'DA' AND us.type in ('sp','PE','consultant') AND us.name!='' ORDER BY us.name";
    $Users_Res=mysql_query($Users_Sql,$db);

    $Users_Array=array();
    while($Users_Data=mysql_fetch_row($Users_Res))
    {
    	 $Users_Array[$Users_Data[0]]=$Users_Data[1];
    } */
	
	//this is the query for getting  all users
	require_once($akken_psos_include_path.'class.getOwnersList.php');
	$ownersObj = new getOwnersList();
	$Users_Array = $ownersObj->getOwners(); 

    $User_nos=implode(",",array_keys($Users_Array));
    $uersCnt=count($Users_Array);

	//----------Start of Contact's Details-----------
    $home_details="";

    //city
    if($row1[33]!="")
     $home_details.=dispTextdb($row1[33]).", ";

    //State
    if($row1[34]!="")
    	$home_details.=dispTextdb($row1[34]);

    //Zip
    if($row1[36]!="")
        $home_details.=" ".dispTextdb($row1[36]);

    //---------End of Home Address Details--------------------

    if(($row1[13]==0 ||$row1[13]=='') && ($row1[42]!=0 ||$row1[42]!=''))
    {
        $leftcompany="Y";
    }

    if($row1[41]=="Y")
    {
        $dontmail="DO NOT EMAIL | ";
        $conmail="Do Not Email";
    }
    else
    {
        $dontmail="";
        $conmail=$row1[6];
    }


    if($row1[40]=="Y")
    {
        $dontcall="DO NOT CALL | ";
        if($conmail!="")
            $conphone="Do Not Call -";
        else
            $conphone="Do Not Call";
    }
    else
    {
        $dontcall="";
        if($row1[7]!="" || $row1[53] != "")
        {
            if($row1[53] != "" && $row1[7] != "")
				$mod_hea = html_tls_entities($row1[7])."<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($row1[53]);
			else if ($row1[53] != "" && $row1[7] == "")
				$mod_hea = "<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($row1[53]);
			else if ($row1[53] == "" && $row1[7] != "")
				$mod_hea = $row1[7];
				
			if($conmail!="")
                $conphone=$mod_hea." -";
            else
                $conphone=$mod_hea;
        }
    }

    // To chcek the onwer and to disable share and owner listbox if login user is not the owner
	if($row1[37]!=$username)
        $disable="disabled";
    else
        $disable="";

    if($row1[13]==0)
        $lcomp="disabled";
    else
        $lcomp="";

    // For getting Source
    if($row1[23]!=0)
    {
        $source_que="select concat_ws(' ',fname,mname,lname),crmcontact,IF (accessto = 'ALL', 'public',IF (accessto = '".$username."', 'private', if(FIND_IN_SET( '".$username."', accessto ) >0,'share','noaccess'))),owner,status from staffoppr_contact where sno='".$row1[23]."'";
        $source_res=mysql_query($source_que,$db);
        $source_row=mysql_fetch_row($source_res);
        $crm_source=$source_row[1];
        $source_share=$source_row[2];
        $source_owner=$source_row[3];
        $source_stat=$source_row[4];
    }
	else if(trim($row1[62])!="")
	{
		$crm_source="N";
        $source_share=$row1[52];
        $source_owner=$row1[37];
        $source_stat=$row1[15];
	}
	$con_source=trim($row1[62]);

    // For getting Spouse
    if($row1[29]!=0)
    {
        $spou_que="select concat_ws(' ',fname,mname,lname),crmcontact,IF (accessto = 'ALL', 'public',IF (accessto = '".$username."', 'private', if(FIND_IN_SET( '".$username."', accessto ) >0,'share','noaccess'))),owner,status from staffoppr_contact where sno='".$row1[29]."'";
        $spou_res=mysql_query($spou_que,$db);
        $spou_row=mysql_fetch_row($spou_res);
        $crm_spou=$spou_row[1];
        $spouse_share=$spou_row[2];
        $spouse_owner=$spou_row[3];
        $spouse_stat=$spou_row[4];
    }
	else if(trim($row1[61])!="")
	{
		$crm_spou="N";
        $spouse_share=$row1[52];
        $spouse_owner=$row1[37];
        $spouse_stat=$row1[15];
	}
	$con_spouse=trim($row1[61]);
    // For getting Report To
    if($row1[44]!=0)
    {
        $report_que="select concat_ws(' ',fname,mname,lname),crmcontact,IF (accessto = 'ALL', 'public',IF (accessto = '".$username."', 'private', if(FIND_IN_SET( '".$username."', accessto ) >0,'share','noaccess'))),owner,status from staffoppr_contact where sno='".$row1[44]."'";
        $report_res=mysql_query($report_que,$db);
        $report_row=mysql_fetch_row($report_res);
        $crm_report=$report_row[1];
        $report_share=$report_row[2];
        $report_owner=$report_row[3];
        $report_stat=$report_row[4];
    }
	else if(trim($row1[63])!="")
	{
		$crm_report="N";
        $report_share=$row1[52];
        $report_owner=$row1[37];
        $report_stat=$row1[15];
	}
	$con_report=trim($row1[63]);

    $cont_cand_rel="select sno,accessto from candidate_list where contact_id=".$contasno." group by sno";
	$Res_Cont=mysql_query($cont_cand_rel,$db);
	$Data_Cont=mysql_fetch_row($Res_Cont);
	if($Data_Cont[0]==''|| $Data_Cont[0]==0)
	 {
		 $cand_view="make a candidate";
	 }
	 else
	 {
		$cand_view="view candidate";
	 }
	 
	if($Data_Cont[1]=='ALL' || $Data_Cont[1]=='' || $Data_Cont[1]=='null')
	 	$showLink = "Link";
	else if(strpos($Data_Cont[1],",")===false)
		$showLink = "NoLink";
	else
		$showLink = "Link";


   $cque="select ceo_president,cfo,sales_purchse_manager,cname,curl,address1,address2,city,state,country,zip,ctype,csize,nloction,nbyears,nemployee,com_revenue,federalid,phone,fax,'','',industry,keytech,department,parent,'',siccode,csource,owner,accessto,acc_comp,alternative_id,nloction,compowner,ticker,phone_extn,deptid from staffoppr_cinfo where staffoppr_cinfo.sno=".$row1[13];
    $cres=mysql_query($cque,$db);
    $crow=mysql_fetch_row($cres);
	$c_owner=$crow[29];
	$access_to=$crow[30];
	if(strtoupper($access_to)!='ALL')
	$access_share=explode(",",$access_to);
    if($shareval==$username)
    {
        $cshare="PRIVATE";
        $cshare1="PRIVATE";
    }
    else if($shareval=='ALL')
    {
        $cshare="PUBLIC";
        $cshare1="PUBLIC";
    }
    else
    {
        if($accessValue=="noaccess")
        {
            $cshare="PRIVATE";
            $cshare1="PRIVATE";
        }
        else
        {
            $cshare="SHARE";
            $cshare1="SHARED";
        }
    }

    //Contact's Company Details
    $company_details="";
    $company_address="";
    $company_website="";


    //comp name
    if($crow[3]!="")
    {
      $company_details.=dispTextdb($crow[3])."<br>";
      $company_website.=dispTextdb($crow[3]);
    }

    //Address1
    if($crow[5]!="")
    {
    	$company_details.=dispTextdb($crow[5]);
    	$company_address.=dispTextdb($crow[5]);
    }

    //Address2
    if($crow[6]!="")
    {
        $company_details.="&nbsp;".dispTextdb($crow[6])."<br/>";
        if($company_address!=""){
        $company_address.=",&nbsp;".dispTextdb($crow[6]);
        }
        else{
        $company_address.="&nbsp;".dispTextdb($crow[6]);   
        }
    }

    //city
    if($crow[7]!="")
    {
        $company_details.=dispTextdb($crow[7]);
        if($company_address!=""){
        $company_address.=",&nbsp;".dispTextdb($crow[7]);
        }
        else{
        $company_address.="&nbsp;".dispTextdb($crow[7]);
        }
    }

    //State
    if($crow[8]!="")
    {
        $company_details.=",&nbsp;".dispTextdb($crow[8]);
        if($company_address!=""){
        $company_address.=",&nbsp;".dispTextdb($crow[8]);
        }
        else{
        $company_address.="&nbsp;".dispTextdb($crow[8]);
        }
    }

    //Zip
    if($crow[10]!="")
    $company_address.="&nbsp;".dispTextdb($crow[10]);

    //website
    if($crow[4]!="")
    $company_website.="&nbsp;[&nbsp;".dispTextdb($crow[4])."&nbsp;]&nbsp;";

	if($candcomp!="")
	{
        $row1[13]=$candcomp;
    }

    function getMeridiem($gtime){
	$Time=str_replace(":",".",$gtime);
	$meridiem=(($Time==0 || $Time==24 || $Time=='')?'12:00 am':(($Time>=12 && $Time<=12.59)?number_format($Time,2,':',',')." pm ":(($Time>12)?number_format(($Time-12),2,':',',')." pm ":number_format($Time,2,':',',')." am ")));
	return 	$meridiem;
    }
    $module_type_appoint="Marketing->Prospects";
/* ... .. Raj.. Checking server to change style for FF .. Raj .. ... */
	if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1){
		$rightflt = "style= 'width:26px;'";
		$rightflt3 = "style=''";
	}//End of if(To know the server)
        
        
        
        //Getting the burden details of the company
        //Initialize the pay/bill burden values/string to display in the summary page for pay/bill burden fields
        $burden_details_str = "";
        $bill_burden_details_str = "";

        $loc_burden_details_sql = "SELECT 
            burden_type as 'pay_bt_id',					
            bill_burden_type AS 'bill_bt_id'
            FROM staffoppr_location 
            WHERE csno='".$row1[13]."' AND 
            ltype='com'
            ";									
        $loc_burden_details_rs	= mysql_query($loc_burden_details_sql, $db);
        $loc_burden_details_row = mysql_fetch_row($loc_burden_details_rs);
        $pay_bt_id = $loc_burden_details_row[0];
        $bill_bt_id = $loc_burden_details_row[1];

        //Form the string to select the particular burden types saved for the company
        $bt_snos_str = "";
        if($pay_bt_id != "" && $pay_bt_id != null)
        {
            $bt_snos_str .= $pay_bt_id.",";
        }
        if($bill_bt_id != "" && $bill_bt_id != null)
        {
            $bt_snos_str .= $bill_bt_id.",";
        }

        if($bt_snos_str != "")
        {
            //removing the last appened comma (,)
            $bt_snos_str = substr($bt_snos_str,0,strlen($bt_snos_str)-1);
            //Get the Pay/Bill Burden details of paritcular burden types saved for the company 
            $get_pay_burden_details_sql = "SELECT 
            bt.sno,
            CONCAT(bt.sno,'|',bt.burden_type_name) AS 'bt_details',
GROUP_CONCAT(CONCAT(bim.bi_id,'^',bi.burden_item_name,'^',bi.burden_value,'^',bi.burden_mode,'^',bi.ratetype,'^',bi.max_earned_amnt,'^',bi.billable_status) SEPARATOR '|')  AS 'bi_details'									
            FROM burden_items bi
            JOIN burden_items_map bim ON bim.bi_id = bi.sno
            JOIN burden_types bt ON bt.sno = bim.bt_id AND bt.sno IN (".$bt_snos_str.")
			WHERE bi.bi_status = 'Active' 
            GROUP BY bt.sno
                        ";
            $get_pay_burden_details_rs	= mysql_query($get_pay_burden_details_sql, $db);

            while ($row = mysql_fetch_array($get_pay_burden_details_rs)) 
            {
                $bt_id = $row[0];
                $burden_type_details = $row[1];
                $burden_item_details = $row[2];
                //Form the burden items string to display in the summary section
                //Pay burden string
                if($bt_id == $pay_bt_id)
                {
                    $burden_details_str = buildBurdenDetails($burden_type_details, $burden_item_details);
                }
                else if($bt_id == $bill_bt_id) // Bill burden string
                {
                    $bill_burden_details_str = buildBurdenDetails($burden_type_details, $burden_item_details);
                }
            }
        }


    // Hubspot link formation
    $hs_search_url = "";
    $hs_link_qry = "select vid from hubspot_contacts where hubspot_status='Active' and respective_sno='".$row1[0]."' and module='Contacts' order by mdate desc limit 1";
    $res_hs_link_qry = mysql_query($hs_link_qry, $db);
    if(mysql_num_rows($res_hs_link_qry) > 0)
    {
        while($hs_link_qry_row = mysql_fetch_row($res_hs_link_qry))
        {
            $hs_vid = $hs_link_qry_row[0];
        }
        
        $hs_link_qry2 = "SELECT portal_id FROM hubspot_account WHERE STATUS='A' ";
        $res_hs_link_qry2 = mysql_query($hs_link_qry2, $db);
        if(mysql_num_rows($res_hs_link_qry2) > 0)
        {
            $hs_link_qry_row2 = mysql_fetch_row($res_hs_link_qry2);
            $hs_portal_id = $hs_link_qry_row2[0];
        }
        $hs_search_url = "https://app.hubspot.com/contacts/".$hs_portal_id."/contact/".$hs_vid;
    }
        
?>
<html>
<head>
<title>Contact
<?php if(trim(stripslashes($contact_name))!='') echo " - ".html_tls_specialchars(stripslashes($contact_name),ENT_QUOTES);?>
</title>
<link rel="stylesheet" href="/BSOS/css/fontawesome.css"/>
<link href="/BSOS/Home/style_screen.css" rel="stylesheet" type="text/css">
<link href="/BSOS/css/educeit.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/crm-summary.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/candidatesInfo_tab.css">
<link type="text/css" rel="stylesheet" href="/BSOS/css/CandidatesCustomTab.css">
<link rel="stylesheet" href="/BSOS/popupmessages/css/popup_message.css" media="screen" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/ui.dropdownchecklist.css" />
<link type="text/css" rel="stylesheet" href="/BSOS/css/jquery.resize.css">
<link rel="stylesheet" href="/BSOS/css/multiple-select.css"/>
<link type="text/css" rel="stylesheet" href="/BSOS/css/calendar.css">

<script>var ac_fs_server_host ="<?php echo $_SERVER['HTTP_HOST'];?>";</script>
<script type="text/javascript" src="/BSOS/scripts/AC_FS_Cookie.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery-1.8.3.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>

<script type="text/javascript" src="/BSOS/scripts/jquery.highlight.js"></script>
<script type="text/javascript" src="/BSOS/scripts/crmNextPrev.js"></script>
<script type="text/javascript" src="/BSOS/scripts/jquery.resize.js"></script>
<script type="text/javascript" src="/BSOS/scripts/eraseSessionVars.js"></script>

<script language=javascript src="/BSOS/scripts/crmgroups.js"></script>
<script language=javascript src="/BSOS/scripts/cookies.js"></script>
<script language=javascript src=/BSOS/scripts/common_ajax.js></script>
<script language=javascript src=/BSOS/scripts/validatecommon.js></script>
<script language=javascript src=/BSOS/scripts/tabpane.js></script>
<script language="JavaScript" src="scripts/contactinfo.js"></script>
<script language=javascript src=scripts/validatesup.js></script>
<script language=javascript src=scripts/validatenewsubmanage.js></script>
<script language=javascript src=/BSOS/scripts/commonact.js></script>
<script language=javascript src=/BSOS/scripts/validatecheck.js></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popupMsgArray.js"></script>
<script type="text/javascript" src="/BSOS/popupmessages/scripts/popup-message.js"></script>
<script type="text/javascript" src="/BSOS/scripts/OutLookPlugInDom.js"></script>
<script type="text/javascript" src="/BSOS/scripts/getSocialNetworks.js"></script>
<script src="/BSOS/scripts/ui.core-min.js" language="javascript"></script>
<script src="/BSOS/scripts/ui.dropdownchecklist-min.js" language="javascript"></script>
 <script type="text/javascript" src="/BSOS/scripts/shift_schedule/jquery.modalbox.js"></script> <!-- loads jquery & jquery modalbox END --> 
 <link rel="stylesheet" type="text/css" media="screen" href="/BSOS/css/sphinx_modalbox.css" />
<script type="text/javascript" src="/BSOS/scripts/calendar.js"></script>
<?php require_once("TextUs.php");?>
<script type="text/javascript" src="/BSOS/TextUs/scripts/textus.js"></script>
<script type="text/javascript" src="/BSOS/scripts/cea.js"></script>
<script type="text/javascript" src="/BSOS/HubSpot/scripts/HubSpot.js"></script>
<script type="text/javascript" src="/BSOS/scripts/AkkuBi/jquery.slimscroll.js"></script>

<?php
$que="select CONCAT_WS(' ',staffoppr_contact.fname,if(staffoppr_contact.mname='',' ',staffoppr_contact.mname),staffoppr_contact.lname),staffoppr_contact.deptid from staffoppr_contact where staffoppr_contact.sno=".$addr;
    $res=mysql_query($que,$db);
    $row=mysql_fetch_row($res);  
    $contact_name = $row[0];
    $contact_deptid = $row[1];
    // Adding Department Permission 
    $deptAccessObj = new departmentAccess();
    $deptName = $deptAccessObj->getDepartmentName($contact_deptid);
    $deptUsrAccess = $deptAccessObj->getDepartmentUserAccess($username,$contact_deptid,"'FO'"); 
    if (!$deptUsrAccess && $module =="CRM") {
        $deptAlertMsg = $deptAccessObj->displayPermissionAlertMsg($deptName); 
        ?>        
        <script type='text/javascript'> 
        alert("<?php echo $deptAlertMsg;?>");
        window.close();
        </script>
        <?php exit();           
    }
?>

<style type="text/css">
#leftpane { width:60%; float:left;}
#scrolldisplay {width:auto;}
.custom_pay_rate_class,.custom_bill_rate_class{
border:1px solid #f3f6fb !important;
font-size:11px !important;
}
.crmsummary-content-title-leftborder{font-size:12px;}
#mainpane #leftpane .ui-resizable-handle{z-index:9998 !important;}

 /* this is the new style we added for newly introduced Preferences settings Expand and collapse */
.ToDoPreference .cl-txtlnk {
    border: 2px solid #3eb8f0 !important;
    border-radius: 100% !important;
    box-sizing: border-box !important;
    float: inherit !important;
    height: 22px !important;
    padding: 1px 0 0 1px !important;
    text-align: center !important;
    width: 22px !important;
}
.ToDoPreference .cl-txtlnk i {
    color: #3eb8f0 !important;
    font-weight: bold !important;
}
.opcl-btnleftside_pref{
	background: #474c4f none repeat scroll 0 0;
    display: block;
    float: left;
    height: 10px;
    margin-bottom: 1px;
    margin-left: 5px;
    margin-top: 1px;
    padding: 0;
}
.opcl-btnrightside_pref {
    background: #474c4f none repeat scroll 0 0;
    display: block;
    float: left;
    height: 10px;
    line-height: 5pt;
    margin-bottom: 1px;
    margin-top: 1px;
    padding: 0;
}

.ToDoPreference .cl-txtlnk i.fa-angle-down {
    padding: 1px 0px 0 1px !important;
	margin-left:-5px !important;
}
.prefBorderCls{
	background: #ffffff none repeat scroll 0 0;
    border: 1px solid #cccccc;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    margin-left: 4px;
    margin-right: 4px;
    margin-top: -3px;
    padding: 5px;
}
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') > -1)
	echo ".innerdivstyle{width:auto; overflow-x:auto; overflow-y:hidden;}\n";
else
	echo ".innerdivstyle{width:100%; overflow-x:auto; overflow-y:hidden;}\n";
?>
@media screen and (-webkit-min-device-pixel-ratio:0) { /* hacked for chrome and safari */
 	.innerdivstyle{width:auto;overflow-x:auto; overflow-y:hidden;}
}

.errorLabel { background-color: #ffff33; }

.akkenTextUSSMS {position:relative; padding:15px;color:#fff; background:#32cd32;  -webkit-border-radius:14px;
  -moz-border-radius:14px;  border-radius:14px; font-size:14PX;word-wrap:break-word; word-break:break-all;}
.akkenTextUSSMS:after { content:""; position:absolute; top:-8px;left:18px; border-width:0 8px 8px; border-style:solid; border-color:#32cd32 transparent;display:block; width:0;}
.akkenTextUSSMS.right {background:#3EB8F0;float:right; padding:5px 15px; margin:5px 5px 15px 0px; box-sizing:border-box; max-width:90%}
.akkenTextUSSMS.right:after {top:4px;right:-5px; bottom:auto;left:auto;border-width:8px 0px 8px 12px;border-color:transparent #3EB8F0;}
.akkenTextUSSMS.left { background:#808080; padding:5px 15px; color:#fff; float:left; margin:5px 5px 15px; box-sizing:border-box; max-width:90%}

.akkenTextUSSMS.left:after { top:4px; left:-5px; bottom:auto; border-width:8px 12px 8px 0px; border-color:transparent #808080;}
.akkenTextUSSMSCustTxt{display: inline-block;font-size: 10px;text-align: left;width: 100%;}
.akkenTextUSSMSCustTxt .TextUSBold, .akkenTextUSSMSAdmiTxt .TextUSBold{ font-weight:bold}
.akkenTextUSSMSAdmiTxt{display: inline-block;font-size: 10px;text-align: right; width: 100%;}
.akkenTextUSSMSM{display:table; width:100%}
.refTextUs{margin-right: 7px;}
.txtUsPad i{ margin: 0px 5px; }
.textUsfloatR { float: right; }
.textUsDropDown {margin-top: -5px; }
.textUsDropDown ul li{ background: none; border: none; }
.textUsfloatR .akkencustomicons ul li:hover{ border: none; border-radius: 3px; }
.textUsDropDown{ margin-top: -8px; }
#textUsSmsDiv{padding: 5px;}

.ui-dropdownchecklist{ height: auto; margin: 4px; padding: 6px 0; width: 200px !important; border: 1px solid #ccc; border-radius: 4px; }
#remind-txtinput-width{width: 120px;}
#tododate{width:84px;}
.categoryDropdown .ui-dropdownchecklist-text{ font-size:13px !important; padding-left:8px;}



/* Call Em All Conversations styles - Start*/
.fa-phone-square:before {
	content: "\f098";
	color:#fd7222;
}
.akkenCeaSMS {position:relative; padding:15px;color:#fff; background:#32cd32;  -webkit-border-radius:14px;
  -moz-border-radius:14px;  border-radius:14px; font-size:14PX;word-wrap:break-word; word-break:break-all;}
.akkenCeaSMS:after { content:""; position:absolute; top:-8px;left:18px; border-width:0 8px 8px; border-style:solid; border-color:#32cd32 transparent;display:block; width:0;}
.akkenCeaSMS.right {background:#e64a19;float:right; padding:5px 15px; margin:5px 5px 15px 0px; box-sizing:border-box; max-width:90%}

.akkenCeaSMS.right:after {top:4px;right:-5px; bottom:auto;left:auto;border-width:8px 0px 8px 12px;border-color:transparent #e64a19;}

.akkenCeaSMS.left { background:rgba(224, 224, 224, 0.75); padding:5px 15px; color:rgba(0, 0, 0, 0.87); float:left; margin:5px 5px 15px; box-sizing:border-box; max-width:90%}

.akkenCeaSMS.left:after { top:4px; left:-5px; bottom:auto; border-width:8px 12px 8px 0px; border-color:transparent rgba(224, 224, 224, 0.75);}
.akkenCeaSMSM{display:table; width:100%}
.akkenCeaSMSCustTxt{display: inline-block;font-size: 10px;text-align: left;width: 100%;}
.akkenCeaSMSCustTxt .ceaBold, .akkenCeaSMSAdmiTxt .ceaBold{ font-weight:bold}
.akkenCeaSMSAdmiTxt{display: inline-block;font-size: 10px;text-align: right; width: 100%;}
.refCea{margin-right: 7px;}
.ceaPad i{ margin: 0px 5px; }
#caeConvDiv{padding: 5px;}
/* Call Em All Conversations styles - End*/

/* Social Search Icons */ 
.socialSearch a.linkedin, .socialSearch a.facebook, .socialSearch a.twitter, .socialSearch a.hubspot{margin-right:3px;vertical-align:bottom;}
.tooltip img{margin: 0px 2px;}

/*tooltip style*/
.tooltip {position: relative; display: inline-block;}
.tooltip .tooltiptext {visibility: hidden;width: 200px;background-color: #e9e9e9;color: #484848;text-align: center;border-radius: 6px;padding: 5px 0;position: absolute;z-index: 1;top: 180%;left: 5%;margin-left: -15px; font-weight:normal; font-size:12px; line-height:18px;}
.tooltip .tooltiptext:after {content: "";position: absolute;bottom: 100%;left: 5%;margin-left: 0px;border-width: 5px;border-style: solid;border-color: transparent transparent #e9e9e9 transparent;}
.tooltip:hover .tooltiptext {visibility: visible;}
</style>
<script type="text/javascript">
$(function(){
	    $('#caeConvDiv').slimscroll({			
	      height: '550px',
		  alwaysVisible: true
	    });
  	});
var newscrollHeight = 100000;
$(document).ready(function() {
 $(window).load(function() {
    $("#caeConvDiv").animate({
            scrollTop: newscrollHeight
        }, 'normal');
		 $(".slimScrollBar").css("top", newscrollHeight+'px');
		
  });
});	
$(function() { $("#leftpane").resizable({ handles: 'e'}); });

function refreshChkList(listBox,values){

	$(document).ready(function() {
		$("#"+listBox).dropdownchecklist("destroy");
		$("#"+listBox).dropdownchecklist({firstItemChecksAll: true, maxDropHeight: 100, width: 150, emptyText: "Select Categories"});
	});
}

function modalBoxCloseandCancel(){
    parent.top.modalBoxClose(); 
}

function modalBoxClose()
{
    $().modalBox('close');
}
</script>
</head>
<body class="categoryDropdown">

<form method='get' name='supreg' id='supreg'>
  <input type=hidden name='summarypage' id='summarypage' value="summary">
  <input type=hidden name='con_id' value="<?php echo $con_id;?>" id='con_id' />
  <input type=hidden name='addr' value="<?php echo $addr;?>" id='addr' />
  <input type=hidden name='contowner' value="<?php echo html_tls_specialchars($row1[37],ENT_QUOTES);?>" id='contowner' />
  <input type=hidden name='emplist' id='emplist' />
  <input type=hidden name='conemail' value="<?php echo html_tls_entities($conemail);?>" id='conemail' />
  <input type=hidden name='conid' value="<?php echo html_tls_entities($conid);?>" id='conid' />
  <input type=hidden name='crm_cand_sno' value="<?php echo $crm_cand_sno?>" id='crm_cand_sno' />
  <input type=hidden name='contasno' value="<?php echo html_tls_entities($contasno)?>" id='contasno' />
  <input type=hidden name='contshare' value="<?php echo html_tls_entities($cshare)?>" id='contshare' />
  <input type=hidden name='contphone' value="<?php echo html_tls_entities($row1[7])?>" id='contphone' />
  <input type=hidden name='contmailid' value="<?php echo html_tls_entities($row1[6])?>" id='contmailid' />
  <input type=hidden name='newcont' value="<?php echo html_tls_entities($newcont);?>" id='newcont' />
  <input type=hidden name='fcompid' value="<?php echo html_tls_entities($row1[13]);?>" id='fcompid' />
  <input type=hidden name='fcompname' value="<?php echo html_tls_specialchars($crow[3],ENT_QUOTES);?>" id='fcompname' />
  <input type=hidden name='candrn' value="<?php echo html_tls_entities($candrn);?>" id='candrn' />
  <input type=hidden name='cmailid' value="<?php echo html_tls_entities($email);?>" id='cmailid' />
  <input type=hidden name='contdomain' value="<?php echo html_tls_entities($checkdomain);?>" id='contdomain' />
  <input type=hidden name='cntaddr1' value="<?php echo html_tls_entities($row1[31]);?>" id='cntaddr1' />
  <input type=hidden name='cntaddr2' value="<?php echo html_tls_entities($row1[32]);?>" id='cntaddr2' />
  <input type=hidden name='cntcity' value="<?php echo html_tls_entities($row1[33]);?>" id='cntcity' />
  <input type=hidden name='cntstate' value="<?php echo html_tls_entities($row1[34]);?>" id='cntstate' />
  <input type=hidden name='cntzip' value="<?php echo html_tls_entities($row1[36]);?>" id='cntzip' />
  <input type=hidden name='cntcntry' value="<?php echo html_tls_entities($row1[35]);?>" id='cntcntry' />
  <input type=hidden name='var_stat' value="<?php echo $var_stat;?>" id='var_stat' />
  <input type=hidden name='mainCompany' value="<?php echo html_tls_entities($companyname);?>" id='mainCompany' />
  <input type=hidden name='posid' value="<?php echo $posid;?>" id='posid' />
  <input type="hidden" name="hdnAssocString" value="" id="hdnAssocString" />
  <input type="hidden" name="hdnContAssoc" value="" id="hdnContAssoc" />
  <input type="hidden" name="hdnCompAssoc" value="" id="hdnCompAssoc" />
  <input type="hidden" name="hdnCandAssoc" value="" id="hdnCandAssoc" />
  <input type="hidden" name="hdnJobAssoc" value="" id="hdnJobAssoc" />
  <input type="hidden" name="hidnewgrpsno" value="" id="hidnewgrpsno" />
  <input type="hidden" name="profilecount" value="" id="profilecount" />
  <input type="hidden" name="notescount" value="" id="notescount" />
  <input type="hidden" name="typecomp" value="<?php echo $typecomp;?>" id="typecomp"> 
  <input type="hidden" name="frm_module" id="frm_module" value="<?php echo $module;?>" /> 

<div id="sumNav">&nbsp;<input class="sumBtn" type="button" name="sprev" id="snext" value="<< Prev" onClick="javascript:prevGridRec(<?=$totcolumns?>,'newmanage.php')">&nbsp;&nbsp;&nbsp;&nbsp;<input class="sumBtn" type="button" name="snext" value="Next >>" onClick="javascript:nextGridRec(<?=$totcolumns?>,'newmanage.php')"></div>
<div id="mergelinks"><input class="sumBtn" type="button" name="snext" value="Remove from Merge List" onClick="javascript:removeList_merge('<?php echo $addr;?>');">&nbsp;&nbsp;&nbsp;&nbsp;<input class="sumBtn" type="button" name="snext" value="Mark as Master" onClick="javascript:masterMark('<?php echo $contasno;?>');window.close();">&nbsp;&nbsp;&nbsp;&nbsp;</div>
  <div id="grid_form">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="white">
              <tr>
                <td width=100% valign=top align=left><div class="tab-pane" id="tabPane1">
                    <script type="text/javascript">tp1 = new WebFXTabPane( document.getElementById( "tabPane1" ) );</script>
                    <div class="tab-page" id="tabPage01">
						<h2 class="tab">Summary</h2>
						<script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage01" ) );</script>
						<div class="crmsummary-navtop" id="contactSummaryNavDiv"> 
							  <?php
							  if(CRM_GROUPS == "Y")
							  {
								?>
									<span id="spanRmvGrpLnk" style="display:none;"><a href="javascript:removeRecordGroup('crmContactSummary','CRM', 'Contact', '<?php echo $addr;?>');">Remove from Group</a> </span>							
								<?php
							   }?>
							<div class="textUsfloatR">
								<a onClick="javascript:windclose();" href="javascript:void(0);"><i class="fa fa-times fa-lg"></i> Close</a>
							</div>
									<div class="textUsfloatR">
										<div class="akkencustomicons textUsDropDown">
										<ul>
											<li>
												<a class="link6" href="javascript:;"><i class="fa fa-globe fa-lg"></i>Integrated Sevices<i class="fa fa-angle-down fa-lg"></i></a>
												<ul class="bottomicons">
                                                    <?php if(CEA_ENABLED=='Y' && CEA_USER_ACCESS=='Y'){ ?>
                                                    <li>
                                                        <a href="javascript:ceaConversationInit('../../CEA/ceaConversationInit.php?mod=crmcontacts&sno=<?=$contasno?>','CEA Conversation',mntcmnt9,9)">Call-Em-All Texting</a>
                                                    </li>
                                                    <?php } ?>
													<?php if(TEXT_US_ENABLED == 'Y' && TEXTUS_USER_ACCESS=='Y') { ?>
													<li>
														<a href="javascript:textusReply('../../TextUs/sendMessage.php?mod=crmcontacts&sno=<?=$addr?>','TextUs SMS',mntcmnt7,7,600,300)">TextUs Texting</a>
													</li>
													<?php } ?>
											<?php 	if(HUBSPOT_ENABLED == 'Y' && HUBSPOT_USER_ACCESS =='Y') 
													{ 	?>
														<li><a href="javascript:HubSpotInitiate('Contacts_inside');">Sync to HubSpot</a></li>
											<?php 	} 	?>
												</ul>
											</li>
										</ul>
										</div>
									</div>
								
							<?php 
							  require(BSOS_INCL."OutLookEnbLinks.php");
							  $OutLookObj=new EnbOutLookPluginLinks(OUTLOOK_PLUG_IN,"CRMCONT");
							  $TaskNewLink="javascript:newTask('addtask','Lead_Mngmt','".$module."')";
							  $AppointLink="javascript:newAppoint('addappoint','Lead_Mngmt','".$module."')";
							  $EmailLink="javascript:newMail('Lead_Mngmt','','".$module."')";
							  
							  $LinkTaskCont=$OutLookObj->EnableAjaxLink('Task',array('AkkenId'=>$addr,''),$TaskNewLink);
							  $LinkAppointCont=$OutLookObj->EnableAjaxLink('App',array('AkkenId'=>$addr,''),$AppointLink);
							  $LinkEmailCont=$OutLookObj->CheckOutlookStatuseEMail(array('AkkenId'=>$addr,''),$EmailLink,html_tls_entities($conemail),'','','');
						  
							?>
							<div class="textUsfloatR">
								<div class="akkencustomicons textUsDropDown">
									<ul>
										<li>
											<a class="link6" href="javascript:;"><i class="fa fa-calendar-o fa-lg"></i>Create<i class="fa fa-angle-down fa-lg"></i></a>
											<ul class="bottomicons">
												<li>
													<a href="javascript:newEvent('addevent','Lead_Mngmt','<?=$module?>')">Create Event</a>
												</li>
												<li>
													<a href="<?php echo $LinkTaskCont;?>">Create Task</a>
												</li>
												<li>
													<a href="<?php echo $LinkAppointCont;?>">Create Appointment</a>
												</li>
											</ul>
										</li>
									</ul>
								</div>
							 </div>
							<div class="textUsfloatR">
								<div class="akkencustomicons textUsDropDown">
									 <ul>
										<li>
											<a class="link6" href="javascript:;">
												<i class="fa fa-plus fa-lg"></i>Add
												<i class="fa fa-angle-down fa-lg"></i>
											</a>
											<?php if(CRM_GROUPS == "Y")
											{
											?>
											<ul class="bottomicons">
												<li>                        
													<a href="javascript:addMoveToCRMGroup('crmContactSummary','Add','','CRM');" id="hrefAddGroup">
													Add to Group</a> 
												</li>
											<?php
											}
											?>
											  <li><a href="javascript:newDoc('adddocument','Lead_Mngmt','<?=$module;?>')">Add Document</a>
											  </li>
											</ul>
										</li>
									</ul>
								</div>
							</div>
                             
						  
						  <script language="javascript"> var LinkEmailCont="<?php echo $LinkEmailCont;?>"; </script>
					       <a href="<?php echo $LinkEmailCont;?>" id="cmid"><i class="fa fa-envelope fa-lg"></i> Send Mail</a> <a href="javascript:updateContact()"><i class="fa fa-clone fa-lg"></i>&nbsp;Update</a>  
						</div>
						<div class="line-top">&nbsp;</div>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="content-border">
						<input type="hidden" name="hdnEmailURL" id="hdnEmailURL" value="<?php echo $LinkEmailCont;?>">
                        <tr>
                          <td>
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
                              <tr>
                                <td valign="top" id="mainpane" height="600"><div id="leftpane"><div class="innerdivstyle">
                                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td><div class="paneltitle ToDoPreference"> 
                    	<span id="rightflt" <?php echo $rightflt;?>>
                        <div class="opcl-btnleftside_pref">
                          <div align="left"></div>
                        </div>
                        <div><a href="javascript:classToggle(mntcmnt8,'DisplayBlock','DisplayNone',mntcmnt8,8,'<?=$module?>')" class="cl-txtlnk" >
                          <div id='hideExp8'><i class="fa fa-angle-right fa-lg"></i></div>
                          </a></div>
                        <div class="opcl-btnrightside_pref">
                          <div align="left"></div>
                        </div>
                        </span>
		  <a href="javascript:classToggle(mntcmnt8,'DisplayBlock','DisplayNone',mntcmnt8,8,'<?=$module?>')" > 
          <span class="paneltitle-span"><i class="fa fa-cog fa-lg"></i></span>
          <span class="paneltitle-span">Preferences</span></a>
	  </div>
		   <div class="DisplayNone" id="mntcmnt8">										
										  <div class="crmsummary-settings-content prefBorderCls">
										  <table width="100%" cellpadding="1" cellspacing="1">
										   <tr>
										   <td colspan="4"> 
											<input name="call" id="call" type="checkbox" <?php echo getChk("Y",$row1[40]);?>>
											<label class="labels"><font color:'#003399' >Do Not Call</font></label>
											<input name="dontmail" id="dontmail" type="checkbox" <?php echo getChk("Y",$row1[41]);?>>
											<label class="labels"><font color:'#003399' >Do Not Email</font></label>
											<input name="leftcomp" id="leftcomp" type="checkbox" <? echo $lcomp;?>>
											<label class="labels"><font color:'#003399' >Left Company</font></label>
											<input name="maincon" id="maincon" type="checkbox" <?php echo getChk("Y",$row1[46]);?>>
											<label class="labels">Display as Main Contact for Company</label>
											</td>
										   </tr>
										  <tr>
										  <td  width="393">
										  <table width="100%"cellpadding="1" cellspacing="1" border="0" >
										 
										   <tr>
											<td>
											<label class="labels"><font color:'#003399' >&nbsp;Share:</font></label></td>
											<td width="75%">
                                              <select class="dropdown" name="conshare" id="conshare" onChange="changeShare()" <?php echo $disable;?>>
                                                <option value="PRIVATE" <?php echo getSel($cshare,"PRIVATE");?>>Private</option>
                                                <option value="SHARE" <?php echo getSel($cshare,"SHARE");?>><?=($cshare=='SHARE')?'Shared':'Share'?></option>
                                                <option value="PUBLIC" <?php echo getSel($cshare,"PUBLIC");?>>Public</option>
                                              </select>
											<span id="view-all-emp" >
                                              <?if($cshare=="SHARE" && $disable==""){?>											
											  <label class="labels"><a href="javascript:changeShare();">edit list</a></label> 
											  <?} else if($cshare=="SHARE" && $disable=="disabled"){?>
											  <label class="labels"><a href="javascript:vieEmplist();">view list</a></label><? }?>
											</span>
											</td></tr>
											<tr>
											 <td>
											<label class="labels">&nbsp;<font color:'#003399' >Owner:</font></label>
											</td>
											<td>
                                              <select class="dropdown" name="owner" id="owner" style="width:150px" <?php echo $disable;?>>
                                                <?php
													foreach($Users_Array as $UserNo=>$uname)
													{
												?>
                                                <option value="<?=$UserNo;?>" <?=getSel($row1[37],$UserNo)?> <?php if($row1[37]=="" && $UserNo==$username) echo 'selected'; ?>>
                                                <?=html_tls_specialchars($uname,ENT_QUOTES)?>
                                                </option>
                                                <?php }?>
                                              </select>
											</td>
										</tr>
                   						<tr>
											<td>
                   								<label class="labels">Contact Type:</label>
				   							</td>
				   							<td>
                                              <select class="dropdown" name="contype" id="contype">
                                                <option value="0">-Select-</option>
                                                <?php
                                                $que1="select sno,name from manage where type='contacttype' order by name";
                                                $res1=mysql_query($que1,$db);
                                				while($dd1=mysql_fetch_row($res1))
                                				{
                                					if($dd1[0]==$row1[22])
                                						print "<option  value=".$dd1[0]." selected >".dispfdb($dd1[1])."</option>";
                                                    else
                                						print "<option  value=".$dd1[0]." >".dispfdb($dd1[1])."</option>";
                                				}
                                			    ?>
                                              </select>
											<?php if(EDITLIST_ACCESSFLAG){ ?>  &nbsp;<a href="javascript:doManage('Contact Type','contype');">edit list</a> <?php } ?>
											</td>
										</tr>
										<tr>
											<td>
												<label class="labels">Category:</label>
											</td>
											<td  style="text-indent:0;">
                                            <?php
													$valArray = getCategoryDropListOptions('category', $type1);
													$valArray['callpage'] = 'contactSummary';
													$valArray['callType'] = 'Category';													
													createChkListDropDown($valArray);
                            				?>
					   <?php if(EDITLIST_ACCESSFLAG){ ?>&nbsp;<a href="javascript:doManage('Category','category');">edit list</a> <?php } ?>
											</td>
										</tr>
										<tr>
											<td>
                    							<label class="labels">Source Type:</label>
											</td>
											<td>					
                                              <select class="dropdown" name="source" id="source">
                                                <option value="">-Select-</option>
												<?php
												$que1="select sno,name from manage where type='compsource' order by name";
												$res1=mysql_query($que1,$db);
												while($dd1=mysql_fetch_row($res1))
												{
												if($dd1[0]==$row1[43])
												print "<option  value=".$dd1[0]." selected >".dispfdb($dd1[1])."</option>";
												else
												print "<option  value=".$dd1[0]." >".dispfdb($dd1[1])."</option>";
												}
												?>
                                              </select>
												<?php if(EDITLIST_ACCESSFLAG){ ?>  &nbsp;<a href="javascript:doManage('Source Type','source');">edit list</a> <?php } ?>
											</td>
										</tr>										
									</table>
								</td>
								<td width="14" align="center"><img src="../../images/vline.GIF" width="1" height="110" /></td>
					 			<td width="150" align="left" valign="top"><a href="javascript:editCheckList('contact');">Contact Checklist<span id="crmsummary-settings-preview" style="visibility:hidden"></span></td>
							 </tr>					 
						 </table>
                                            </div>
                                         </div>
                                        <div class="line3">&nbsp;</div></td>
                                    </tr>

                                    <tr>
                                      <td valign=top><div>
									  <div style="text-align:left" class="resumeHeading">&nbsp;&nbsp;Merge Records&nbsp;:&nbsp;<?php echo $mergeRecord;?></div>
									  <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" id="profilepane">
                                          <tr>
                                            <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                  <td align="center"><table class="crmsummary-contactinfo-table" border="0" cellspacing="0" cellpadding="0">
                                                      <tr>
                                                        <?php if($row1[2]!="")
                                $nick = " (".$row1[2].")  ";
                            ?>
                                                        <td align="center"><span class="crmsummary-contactinfo-title"><b><?php echo stripslashes($contact_name);?></b></span><span class="crmsummary-contactinfo-nickname">&nbsp;<?echo stripslashes($nick);?></span>
                                                          <?php if($showLink == "Link") { ?>
                                                          [ <span id='candView'> <a class="crmsummary-contentlnk" href="javascript:openCand('<?=$Data_Cont[0]?>','<?=$module?>')" ><? echo $cand_view;?></a> </span>]
                                                          <?php } ?></td>
                                                      </tr>
                                                      <tr>
                                                        <td align="center"><b><?php echo stripslashes($row1[12]);?></b></td>
                                                      </tr>
                                                      <tr> <span id="companyname">
                                                        <td align="center"><?php echo stripslashes($crow[3]);?></td>
                                                        </span> </tr>
                                                      <tr> <span id="companyaddrs">
                                                        <? if(stripslashes($crow[5])!="" || stripslashes($crow[6])!="")
                                $compaddress = stripslashes($crow[5]). " ".stripslashes($crow[6]);
                            if($crow[7]!="")
                            {
                                if(stripslashes($crow[5])=="" && stripslashes($crow[6])=="")
                                    $compcity= stripslashes($crow[7]);
                                else
                                    $compcity= ", ".stripslashes($crow[7]);
                            }
                             if(stripslashes($crow[8])!="")
                                $compstate = ", ".stripslashes($crow[8]);
                            //For Zip Code
                            if(stripslashes($crow[10])!="")
                                $compZipCode = " ".stripslashes($crow[10]);

                            $compaddrs_details=trim(stripslashes($compaddress)).$compcity.$compstate.$compZipCode;
                            ?>
                                                        <td align="center"><?php echo trim($compaddrs_details,",");?></td>
                                                        </span> </tr>
                                                      <tr>
                                                        <td align="center"><span class="crmsummary-contentdata-cont" id="ncall">
                                                          <? if($row1[40]=="Y"){ echo $conphone; }?>
                                                          </span> <span id="ccall"><b>
                                                          <? if($row1[40]!="Y"){ echo $conphone; }?>
														  <?
														  	$EmailLink="javascript:newMail('Lead_Mngmt','".addslashes($conmail)."','".$module."')";
															$LinkEmailCont=$OutLookObj->CheckOutlookStatuseEMail(array('AkkenId'=>$addr,''),$EmailLink,html_tls_entities($conemail),'','','');
														  ?>
                                                          </b></span> <a class="crmsummary-contentlnk" href="<?php echo $LinkEmailCont;?>"><span id="cmail">
                                                          <? if($conmail!="" && $row1[41]!="Y"){ ?>
                                                          <?php echo $conmail;}?></span></a> <span class="crmsummary-contentdata-cont" id="nmail">
                                                          <? if($row1[41]=="Y"){ echo $conmail;}?>
                                                          </span></td>
                                                      </tr>
                                                    </table></td>
                                                </tr>
                                              </table></td>
                                          </tr>
                                          <tr>
                                            <!-- --------------------START OF CONTACT DETAILS------------------------  -->
                                            <td><table width="97%" border="0" cellpadding="0" cellspacing="0" class="crmsummary-content-table" align="center">
                                                <tr align="left" valign="left">
                                                  <td align=left width="26%">&nbsp;</td>
                                                  <td align=left width="24%">&nbsp;</td>
                                                  <td align=left width="21%">&nbsp;</td>
                                                  <td align=left width="29%">&nbsp;</td>
                                                </tr>
						
			<?php
			
			$mod = 1;
			include($app_inc_path."custom/getcustomfieldvalues.php");
			
			if(SOCIAL_NETWORKS_FLAG == "TRUE")
			{ 
			?>
				<tr align="left">
				  <td valign="top"><span class="crmsummary-content-title"><?php echo $SOCIAL_PROFILE_LABLE;?></span>
					<div id="insertsn" style="display:none;">
					<?php
					if($email."|".$row1[57]."|".$row1[58]!="||")
					{
					?>
						[<a class="Social_Net_Link" onClick="getSocialNetworks('<?=addslashes(trim($email))."|".addslashes(trim($row1[57]))."|".addslashes(trim($row1[58]));?>','insert', 'OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_SEARCH_LINK;?></a>]&nbsp;
					<?php
					}?>	
						[<a class="Social_Net_Link" onClick="manageSocialNetworks('OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_ADD_LINK;?></a>]
					</div>
					<div id="updatesn" style="display:none;">
					<?php
					if($email."|".$row1[57]."|".$row1[58]!="||")
					{
					?>
						[<a class="Social_Net_Link" onClick="getSocialNetworks('<?=addslashes(trim($email))."|".addslashes(trim($row1[57]))."|".addslashes(trim($row1[58]));?>','update', 'OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_REFRESH_LINK;?></a>]&nbsp;
						<?php
					}?>		
						[<a class="Social_Net_Link" onClick="manageSocialNetworks('OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_EDIT_LINK;?></a>]
					</div>
					<div id="refreshadd" style="display:none;">
					<?php
					if($email."|".$row1[57]."|".$row1[58]!="||")
					{
					?>
						[<a class="Social_Net_Link" onClick="getSocialNetworks('<?=addslashes(trim($email))."|".addslashes(trim($row1[57]))."|".addslashes(trim($row1[58]));?>','update', 'OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_REFRESH_LINK;?></a>]&nbsp;
					<?php
					}?>	
						[<a class="Social_Net_Link" onClick="manageSocialNetworks('OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_ADD_LINK;?></a>]
					</div>
				   </td>
                    <td colspan="4">
                        <table  cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td width="30%" style="border:none">
                                    <div class="socialSearch">
                                    <?php
                                        $firstName  = urlencode($row1[3]);
                                        $middleName = urlencode($row1[4]); 
                                        $lastName   = urlencode($row1[5]);
                                        $candidate_fullname = '"'.urlencode($row1[3])." ".urlencode($row1[4])." ".urlencode($row1[5]).'"';
                                        $candidate_name = '"'.urlencode($row1[3])." ".urlencode($row1[5]).'"';
                                        if($middleName!="")
                                        {
                                            echo "<a href='$socialSrhObj->linkedInBaseURL$firstName $middleName/$lastName' target='_blank' class='linkedin'><div class='tooltip'><img src='../../images/linkedin_icon.png' alt='LinkedIn' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_fullname))." LinkedIn profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->facebookBaseURL$firstName $middleName-$lastName' target='_blank' class='facebook'><div class='tooltip'><img src='../../images/facebook_icon.png' alt='Facebook' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_fullname))." Facebook profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->twitterBaseURL$firstName $middleName $lastName' target='_blank' class='twitter'><div class='tooltip'><div class='tooltip'><img src='../../images/twitter_icon.png' alt='Twitter' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_fullname))." Twitter profiles</span></div></a>";

                                            if($hs_search_url!="")
                                            {
                                                echo "<a href='".$hs_search_url."' target='_blank' class='hubspot'><div class='tooltip'><img src='../../images/hubspot_icon.png' alt='HubSpot' /><span class='tooltiptext'>Click on this icon to view ".str_replace("\\","",urldecode($candidate_fullname))." HubSpot profile</span></div></a>";
                                            }                                   
                                            
                                            
                                        }else{
                                            echo "<a href='$socialSrhObj->linkedInBaseURL$firstName $middleName/$lastName' target='_blank' class='linkedin'><div class='tooltip'><img src='../../images/linkedin_icon.png' alt='LinkedIn' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_name))." LinkedIn profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->facebookBaseURL$firstName $middleName-$lastName' target='_blank' class='facebook'><div class='tooltip'><img src='../../images/facebook_icon.png' alt='Facebook' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_name))." Facebook profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->twitterBaseURL$firstName $middleName $lastName' target='_blank' class='twitter'><div class='tooltip'><div class='tooltip'><img src='../../images/twitter_icon.png' alt='Twitter' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_name))." Twitter profiles</span></div></a>";
                                            
                                            if($hs_search_url!="")
                                            {
                                                echo "<a href='".$hs_search_url."' target='_blank' class='hubspot'><div class='tooltip'><img src='../../images/hubspot_icon.png' alt='HubSpot' /><span class='tooltiptext'>Click on this icon to view ".str_replace("\\","",urldecode($candidate_name))." HubSpot profile</span></div></a>";
                                            } 
                                        }
                                    ?>
                                        
                                    </div>
                                </td>
                                <td colspan=3 nowrap="nowrap" valign="top" style="border:none">
                                    <div id="divsocialnetworks"></div>
                                </td>
                            </tr>
                       </table>
                    </td>
				</tr>
				<script type="text/javascript">
				var SOCIAL_NOEMAIL_MSG="<?php echo $SOCIAL_NOEMAIL_MSG;?>";
				<?php
				if($email."|".$row1[57]."|".$row1[58]=="||")
				{
				?>
					var SOCIAL_PROFILE_MSG="<?php echo $SOCIAL_ADDPROFILE_MSG;?>";
				<?php
				}
				else
				{
				?>
					var SOCIAL_PROFILE_MSG="<?php echo $SOCIAL_NOPROFILE_MSG;?>";
				<?php
				}?>	
				var SOCIAL_NOFOUND_MSG="<?php echo $SOCIAL_NOTFOUND_MSG;?>";
				getSocialNetworks('<?=addslashes(trim($email))."|".addslashes(trim($row1[57]))."|".addslashes(trim($row1[58]));?>','','OPPR','<?=$contasno;?>')
				</script>
			<?php
			}
			else	
			{
			?>
				<tr align="left">
				  <td valign="top"><span class="crmsummary-content-title"><?php echo $SOCIAL_PROFILE_LABLE;?></span>
					  <div id="insertsn" style="display:none;">
						[<a class="Social_Net_Link" onClick="manageSocialNetworks('OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_ADD_LINK;?></a>]
					  </div>			   
					  <div id="updatesn" style="display:none;">
						[<a class="Social_Net_Link" onClick="manageSocialNetworks('OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_EDIT_LINK;?></a>]
					  </div>
					  <div id="refreshadd" style="display:none;">
						[<a class="Social_Net_Link" onClick="manageSocialNetworks('OPPR','<?=$contasno;?>');"><?php echo $SOCIAL_ADD_LINK;?></a>]
					</div>
				  </td>
                  <td colspan="4">
                        <table  cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td width="30%" style="border:none">
                                    <div class="socialSearch">
                                    <?php
                                        $firstName  = urlencode($row1[3]);
                                        $middleName = urlencode($row1[4]); 
                                        $lastName   = urlencode($row1[5]);
                                        $candidate_fullname = '"'.urlencode($row1[3])." ".urlencode($row1[4])." ".urlencode($row1[5]).'"';
                                        $candidate_name = '"'.urlencode($row1[3])." ".urlencode($row1[5]).'"';
                                        if($middleName!="")
                                        {
                                            echo "<a href='$socialSrhObj->linkedInBaseURL$firstName $middleName/$lastName' target='_blank' class='linkedin'><div class='tooltip'><img src='../../images/linkedin_icon.png' alt='LinkedIn' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_fullname))." LinkedIn profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->facebookBaseURL$firstName $middleName-$lastName' target='_blank' class='facebook'><div class='tooltip'><img src='../../images/facebook_icon.png' alt='Facebook' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_fullname))." Facebook profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->twitterBaseURL$firstName $middleName $lastName' target='_blank' class='twitter'><div class='tooltip'><img src='../../images/twitter_icon.png' alt='Twitter' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_fullname))." Twitter profiles</span></div></a>";
                                            
                                            if($hs_search_url!="")
                                            {
                                                echo "<a href='".$hs_search_url."' target='_blank' class='hubspot'><div class='tooltip'><img src='../../images/hubspot_icon.png' alt='HubSpot' /><span class='tooltiptext'>Click on this icon to view ".str_replace("\\","",urldecode($candidate_fullname))." HubSpot profile</span></div></a>";
                                            }
                                            
                                        }else{
                                            echo "<a href='$socialSrhObj->linkedInBaseURL$firstName $middleName/$lastName' target='_blank' class='linkedin'><div class='tooltip'><img src='../../images/linkedin_icon.png' alt='LinkedIn' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_name))." LinkedIn profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->facebookBaseURL$firstName $middleName-$lastName' target='_blank' class='facebook'><div class='tooltip'><img src='../../images/facebook_icon.png' alt='Facebook' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_name))." Facebook profiles</span></div></a>";
                                            echo "<a href='$socialSrhObj->twitterBaseURL$firstName $middleName $lastName' target='_blank' class='twitter'><div class='tooltip'><img src='../../images/twitter_icon.png' alt='Twitter' /><span class='tooltiptext'>Click on this icon to search ".str_replace("\\","",urldecode($candidate_name))." Twitter profiles</span></div></a>";
                                            
                                            if($hs_search_url!="")
                                            {
                                                echo "<a href='".$hs_search_url."' target='_blank' class='hubspot'><div class='tooltip'><img src='../../images/hubspot_icon.png' alt='HubSpot' /><span class='tooltiptext'>Click on this icon to view ".str_replace("\\","",urldecode($candidate_name))." HubSpot profile</span></div></a>";
                                            }
                                        }
                                    ?>
                                        
                                    </div>
                                </td>
                                <td colspan=3 nowrap="nowrap" valign="top" style="border:none">
                                    <div id="divsocialnetworks"></div>
                                </td>
                            </tr>
                       </table>
                    </td>
				</tr>
				<script type="text/javascript">
				var SOCIAL_NOEMAIL_MSG="<?php echo $SOCIAL_NOEMAIL_MSG;?>";
				var SOCIAL_PROFILE_MSG="<?php echo $SOCIAL_ADDPROFILE_MSG;?>";
				var SOCIAL_NOFOUND_MSG="<?php echo $SOCIAL_NOTFOUND_MSG;?>";
				getSocialNetworks('<?=addslashes(trim($email))."|".addslashes(trim($row1[57]))."|".addslashes(trim($row1[58]));?>','','OPPR','<?=$contasno;?>')
				</script>
			<?php
			}
			?>				
                                                <tr align="left" valign="left">
                                                  <td align=left width="26%" class="crmsummary-content-title">Salutation</td>
                                                  <td align=left width="24%"><?php echo html_tls_entities(stripslashes(getManage($row1[1])));?>&nbsp;</td>
                                                  <td align=left width="21%" class="crmsummary-content-title-leftborder">&nbsp;Reports To</td>
                                                  <td align=left width="29%"><span id="report">
                                                    <?php if($crm_report=='N'){echo html_tls_entities(stripslashes($con_report)); ?>
                                                    &nbsp;<a class="linkdata" href="javascript:addCrmContact('<? echo stripslashes($row1[44]);?>','report','<? echo stripslashes($row1[0]);?>')">new »</a>
                                                    <?php } else { if($report_share!="noaccess"){?>
                                                    <a class="crmsummary-contentlnk" href="javascript:viewCrmContact('<? echo stripslashes($row1[44]);?>','report','<? echo stripslashes($report_stat);?>','<? echo $module;?>')"><?php echo html_tls_entities(stripslashes($con_report)); ?></a>
                                                    <? } else echo html_tls_entities(stripslashes($con_report));} ?>
                                                    </span>&nbsp;</td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">Suffix</td>
                                                  <td align=left><?php echo html_tls_entities(getManage($row1[30]));?>&nbsp;</td>
                                                  <td align=left class="crmsummary-content-title-leftborder">&nbsp;Category</td>
                                                  <td align=left><font class="crmsummary-contentdata1">
                                                    <div id="contcatid"><?php echo html_tls_entities(getMultiManage($type1));?>&nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">Title</td>
                                                  <td align=left><?php echo html_tls_entities(stripslashes($row1[12]));?>&nbsp;</td>
												  <td allign=left class="crmsummary-content-title-leftborder">&nbsp;Contact Type</td>
												  <td allign=left><div id=contctype><?php echo html_tls_entities(getManage(stripslashes($row1[22])));?>&nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">Department</td>
                                                  <td align=left><?php echo html_tls_entities(stripslashes($deptname));?>&nbsp;</td>
												  <td align=left class="crmsummary-content-title-leftborder">&nbsp;Contact Source</td>
                                                  <td align=left><span id="csource">
                                                    <?php if($crm_source=='N'){echo html_tls_entities(stripslashes($con_source));  ?>
                                                    &nbsp;<a class="linkdata" href="javascript:addCrmContact('<? echo stripslashes($row1[23]);?>','source','<? echo stripslashes($row1[0]);?>')">new »</a>
                                                    <?php } else { if($source_share!="noaccess") {?>
                                                    <a class="crmsummary-contentlnk" href="javascript:viewCrmContact('<? echo stripslashes($row1[23]);?>','source','<? echo stripslashes($source_stat);?>','<? echo $module;?>')"><?php echo html_tls_entities(stripslashes($con_source)); ?></a>
                                                    <? } else echo html_tls_entities(stripslashes($con_source)); }?>
                                                    </span>&nbsp;
												  </td>                                                 
                                                </tr>
                                                <?php
													if($wphone != "" && $row1[53] != "")
														$office = html_tls_entities($wphone)."<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($row1[53]);
													else if($wphone != "" && $row1[53] == "")
														$office = html_tls_entities($wphone);
													else if($wphone == "" && $row1[53] != "")
														$office = "<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($row1[53]);
												?>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Office</td>
                                                  <td allign=left><?php echo $office; ?>&nbsp;</td>
												  <td class="crmsummary-content-title-leftborder" allign=left>&nbsp;Source Type</td>
												  <td align=left><font class="crmsummary-contentdata1">
                                                    <div id="contstype"><?php echo html_tls_entities(getManage($row1[43]));?>&nbsp;</div>
                                                    </font>
												  </td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Mobile</td>
                                                  <td allign=left class="crmsummary-contentdata1"><?php echo html_tls_entities($mobile);?>&nbsp;</td>
												  <td class="crmsummary-content-title-leftborder" allign=left>&nbsp;Spouse's Name</td>
												  <td allign=left>
												  <span id="spouse">
                                                    <?php if($crm_spou=='N'){echo html_tls_entities(stripslashes($con_spouse)); ?>
                                                    &nbsp;<a class="linkdata" href="javascript:addCrmContact('<? echo stripslashes($row1[29]);?>','spouse','<? echo stripslashes($row1[0]);?>')">new »</a>
                                                    <?php } else { if($spouse_share!="noaccess") {?>
                                                    <a class="crmsummary-contentlnk" href="javascript:viewCrmContact('<? echo stripslashes($row1[29]);?>','spouse','<? echo stripslashes($spouse_stat);?>','<? echo $module;?>')"><?php echo html_tls_entities(stripslashes($con_spouse)); ?></a>
                                                    <? }else echo html_tls_entities(stripslashes($con_spouse)); }?>
                                                    </span>
												  </td>
                                                </tr>
                                                <?php
												if($hphone != "" && $row1[54] != "")
													$home = html_tls_entities($hphone)."<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($row1[54]);
												else if ($hphone != "" && $row1[54] == "")
													$home = html_tls_entities($hphone);
												else if ($hphone == "" && $row1[54] != "")
													$home = "<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($row1[54]);
												?>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Home</td>
                                                  <td allign=left><?php echo stripslashes($home);?>&nbsp;</td>
												  <td allign=left class="crmsummary-content-title-leftborder">&nbsp;Codes</td>
                                                  <td allign=left class="crmsummary-contentdata1"><?php echo html_tls_entities(stripslashes($row1[26]));?>&nbsp;</td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Fax</td>
                                                  <td allign=left><?php echo html_tls_entities(stripslashes($fax));?>&nbsp;</td>
												  <td allign=left class="crmsummary-content-title-leftborder">&nbsp;Importance</td>
                                                  <td allign=left><?php if(trim(stripslashes($row1[60])) == "0"){ echo "";} else {echo html_tls_entities(ucwords(stripslashes($row1[60])));}?>&nbsp;
												  </td>
                                                </tr>
                                                <?php
			if(stripslashes($other) != "" && stripslashes($row1[55]) != "")
				$oth = html_tls_entities(stripslashes($other))."<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities(stripslashes($row1[55]));
			else if (stripslashes($other) != "" && stripslashes($row1[55]) == "")
				$oth = html_tls_entities(stripslashes($other));
			else if (stripslashes($other) == "" && stripslashes($row1[55]) != "")
				$oth = "<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities(stripslashes($row1[55]));
			?>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Other</td>
                                                  <td allign=left colspan="3"><?php echo $oth;?>&nbsp;</td>												  
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title"><?=PRIMARY_EMAIL?></td>
                                                  <td allign=left><div id="cntmail">
                                                      <?php if($row1[41]!="Y") { ?>
                                                      <a class="crmsummary-contentlnk" href="<?php echo $LinkEmailCont;?>"><?php echo $email;?></a>
                                                      <? } else echo $email;?>
                                                      &nbsp;</div></td>
                                                  <td align=right colspan=2><?php if($row1[37]==$username){ ?>
                                                    [ <a href="javascript:addAddressBook('address_book.php?Module=contacts&addr=<?=$addr?>','addressbook','550','340')" class="crmsummary-contentlnk">add/remove from address book</a> ]
                                                    <? } ?>
                                                    &nbsp; </td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Address1</td>
                                                  <td allign=left><?php echo html_tls_entities(stripslashes($row1[31]));?>&nbsp;</td>
                                                  <td colspan="2" class="crmsummary-content-title" allign=left>&nbsp;</td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Address2</td>
                                                  <td allign=left><?php echo html_tls_entities(stripslashes($row1[32]));?>&nbsp;</td>
                                                  <td class="crmsummary-content-title" allign=left><?=ALTERNATE_EMAIL?></td>
                                                  <td allign=left><?php
												 $EmailLink="javascript:newMail('Lead_Mngmt','".addslashes($row1[57])."','".$module."')";
												$LinkEmailCont=$OutLookObj->CheckOutlookStatuseEMail(array('AkkenId'=>$addr,''),$EmailLink,addslashes($row1[57]),'','','');
												   ?>
												    <a class="crmsummary-contentlnk" href="<?php echo $LinkEmailCont;?>"><?php echo html_tls_entities($row1[57]); ?></a>&nbsp;</td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">City,State&nbsp;Zip</td>
                                                  <td allign=left><?php echo trim(stripslashes($home_details),", ");?>&nbsp;</td>
                                                  <td class="crmsummary-content-title" allign=left><?=OTHER_EMAIL?></td>
                                                  <td allign=left><?php
												 $EmailLink="javascript:newMail('Lead_Mngmt','".addslashes($row1[58])."','".$module."')";
												$LinkEmailCont=$OutLookObj->CheckOutlookStatuseEMail(array('AkkenId'=>$addr,''),$EmailLink,addslashes($row1[58]),'','','');
												   ?>
												    <a class="crmsummary-contentlnk" href="<?php echo $LinkEmailCont;?>"><?php echo html_tls_entities($row1[58]); ?></a>&nbsp;</td>
                                                </tr>

												<?php
												$fcompany="";
												if($row1[42]!="")
												{
													$fcque="SELECT group_concat(cname SEPARATOR ', ') FROM staffoppr_cinfo WHERE sno IN ($row1[42])";
													$fcres=mysql_query($fcque,$db);
													$fcrow=mysql_fetch_row($fcres);
													$fcompany=$fcrow[0];
												}
												?>

                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Former Companies</td>
                                                  <td allign=left colspan="3"><?php echo stripslashes($fcompany);?></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Certifications</td>
                                                  <td allign=left colspan=3><?php echo html_tls_entities(stripslashes($row1[25]));?>&nbsp;</td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title" valign="top">Other Information </td>
                                                  <td align=left colspan=3><div class="combrief-paneltxt" style="word-wrap:break-word;text-align:justify;"><?php echo nl2br(html_tls_specialchars(stripslashes($row1[56]),ENT_QUOTES));?>&nbsp;</div></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title" valign="top">Description</td>
                                                  <td align=left colspan=3><div class="combrief-paneltxt" style="word-wrap:break-word;text-align:justify;"><?php echo nl2br(html_tls_specialchars(stripslashes($row1[59]),ENT_QUOTES));?>&nbsp;</div></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">Groups</td>
                                                  <td align=left colspan=3>
												  <font class="crmsummary-contentdata1">
												  		<div id="crmgroups">
														<?php
															echo getCRMGroupNames($contasno,'Contact',"CRM");
														?>&nbsp;														</div>
												  </font>												  </td>
                                                </tr>
												<tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">HRM Department</td>
                                                  <td align=left colspan=3>
												  <font class="crmsummary-contentdata1">
												  		<div id="hrmdept">
														<?php
															echo displayDepartmentName($row1[64]);
														?>&nbsp;</div>
												  </font>												  </td>
                                                </tr>
                                                <!-- --------------------END OF CONTACT DETAILS------------------------  -->
                                                <!-- --------------------START OF COMPANY DETAILS------------------------  -->
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">&nbsp;</td>
                                                  <td align=left colspan=3>&nbsp;</td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">&nbsp;</td>
                                                  <td align=left colspan=3>&nbsp;</td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left class="crmsummary-content-title">&nbsp;<br />
                                                    <br />
                                                    Company Name &nbsp;</td>
                                                  <td align=left colspan=3>&nbsp;<br />
                                                    <br />
                                                    <div id="compname">
                                                      <? if($c_owner==$username || strtoupper($access_to)=='ALL' || in_array($username,$access_share)) { 
                                                        if($module =='Admin_Contacts'){
                                                            $modulecomp = 'Admin_Companies';
                                                        }else{
                                                            $modulecomp = $module;
                                                        }
                                                        ?>
                                                      <a class="crmsummary-contentlnk" href="javascript:viewCrmCompany('<? echo $row1[13]; ?>','ER','<?=$modulecomp?>')">
                                                      <?=dispTextdb($crow[3])?>
                                                      </a>
                                                      <? } else { echo dispTextdb($crow[3]);}?>
                                                      <? if($crow[4]!=''){?>
                                                      [ <a class="crmsummary-contentlnk" href="javascript:companySite('<?=addslashes($crow[4])?>','url','<?=$modulecomp?>')">website</a> ]
                                                      <?}?>
                                                    </div></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Office Address</td>
                                                  <td allign=left colspan=3><font class="crmsummary-contentdata1">
                                                    <div id="offaddr"><?php echo trim(stripslashes($company_address));?>&nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td align=left  width="26%" class="crmsummary-content-title">Customer ID#&nbsp;</td>
                                                  <td align=left width="24%" ><div id="custId">
                                                      <?php if($crow[31]!="" && $crow[31]!="0") echo html_tls_entities($crow[31]);?>
                                                      &nbsp;</div></td>
                                                  <td allign=left width="21%" class="crmsummary-content-title-leftborder" nowrap>&nbsp;Company Size</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compsize">&nbsp;<?php echo html_tls_entities($crow[12]);?>&nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title-leftborder">Company Type&nbsp;</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="comptype"><?php echo getManage($crow[11]);?>&nbsp;</div>
                                                    </font></td>
                                                  <td allign=left class="crmsummary-content-title-leftborder">&nbsp;No. Employees</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compemp">&nbsp;
                                                      <?php if($crow[15]!="") echo html_tls_entities($crow[15]).' employees';?>
                                                      &nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <?php
			if($crow[18] != "" && $crow[36] != "")
				$comp_phone = html_tls_entities($crow[18])."<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($crow[36]);
			else if ($crow[18] != "" && $crow[36] == "")
				$comp_phone = html_tls_entities($crow[18]);
			else if ($crow[18] == "" && $crow[36] != "")
				$comp_phone = "<font class=crmsummary-content-title>&nbsp;ext.</font>&nbsp;".html_tls_entities($crow[36]);
			?>
                                                <tr align="left" valign="left">
                                                  <td allign=left  class="crmsummary-content-title">Main Phone&nbsp;</td>
                                                  <td allign=left ><font class="crmsummary-contentdata1">
                                                    <div id="mphone"><?php echo $comp_phone;?></div>
                                                    </font></td>
                                                  <td allign=left class="crmsummary-content-title-leftborder">&nbsp;No. Locations</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="comploc">&nbsp;
                                                      <?php if($crow[33]!="") echo html_tls_entities($crow[33]);?>
                                                      &nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title">Fax Number</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compfax"><?php echo html_tls_entities(stripslashes($crow[19]));?>&nbsp;</div>
                                                    </font></td>
                                                  <td allign=left class="crmsummary-content-title-leftborder">&nbsp;Company Source&nbsp;</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compsource">&nbsp;<?php echo getManage($crow[28]);?></div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left><font class="crmsummary-content-title">Industry</font></td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compinds"><?php echo html_tls_entities(stripslashes($crow[22]));?>&nbsp;</div>
                                                    </font></td>
                                                  <td allign=left width="21%" class="crmsummary-content-title-leftborder">&nbsp;SIC Code&nbsp;</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compsic">&nbsp;<?php echo html_tls_entities(stripslashes($crow[27]));?>&nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title-leftborder">Year Founded&nbsp;</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compfound"><?php echo html_tls_entities($crow[14]);?>&nbsp;</div>
                                                    </font></td>
                                                  <td allign=left class="crmsummary-content-title">&nbsp;Federal Id</td>
                                                  <td allign=left class="crmsummary-contentdata1">
                                                    <div id="compparent">
                                                    &nbsp;<?php echo html_tls_entities(stripslashes($crow[17]));?>&nbsp;                                                    </div>                                                    </td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title-leftborder" nowrap>Company Revenue&nbsp;</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="comprev"><?php echo html_tls_entities($crow[16]);?>&nbsp;</div>
                                                    </font></td>
                                                  <td allign=left class="crmsummary-content-title-leftborder" nowrap>&nbsp;Ticker Symbol</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="ticker">&nbsp;<?php echo html_tls_entities(stripslashes($crow[35]));?>&nbsp;</div>
                                                    </font></td>
                                                </tr>
                                                <tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title-leftborder" >Company Ownership&nbsp;</td>
                                                  <td allign=left ><font class="crmsummary-contentdata1">
                                                    <div id="compcmpownership"><?php echo html_tls_entities(stripslashes($crow['34']));?>&nbsp;</div>
                                                    </font></td>
                                                  <td allign=left class="crmsummary-content-title-leftborder" >&nbsp;Alternative ID#</td>
                                                  <td allign=left ><font class="crmsummary-contentdata1">
                                                    <div id="compalternateid">&nbsp;<?php echo html_tls_entities(stripslashes($crow['32']));?>&nbsp;</div>
                                                    </font></td>
                                                </tr>
												<tr align="left" valign="left">
                                                  <td allign=left class="crmsummary-content-title-leftborder" >Department&nbsp;</td>
                                                  <td allign=left><font class="crmsummary-contentdata1">
                                                    <div id="compcmpownership"><?php echo html_tls_entities(stripslashes($crow['24']));?>&nbsp;</div>
                                                    </font></td>
													 <td allign=left class="crmsummary-content-title-leftborder" >&nbsp;HRM Department</td>
                                                  <td allign=left ><font class="crmsummary-contentdata1">
                                                    <div id="hrmdeptcmpid">&nbsp;<?php echo displayDepartmentName($crow['37']);?>&nbsp;</div>
                                                    </font></td>								
                                                </tr>
                                                <tr>
                                                    <td allign=left class="crmsummary-content-title-leftborder">Pay Burden</td>
                                                    <td allign=left colspan="3"><?php echo trim(nl2br(html_tls_entity_decode(stripslashes($burden_details_str),ENT_QUOTES)));?>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                    <td allign=left class="crmsummary-content-title-leftborder">Bill Burden</td>
                                                    <td allign=left colspan="3" ><?php echo trim(nl2br(html_tls_entity_decode(stripslashes($bill_burden_details_str),ENT_QUOTES)));?>&nbsp;</td>
                                            </tr>
						<tr>
							<td allign=left class="crmsummary-content-title-leftborder">Default Rate(s)</td>
							<?php
								//For getting the multiple rates
								if($row1[13]!="" || $row1[13]!=0)
								{
									$ratesObj 	    = new multiplerates();
									$mode_rate_type = "company";
									$type_order_id 	= $row1[13];
									echo $ratesObj->dispCustRatesSummary($ratesObj);
									if(SHIFT_SCHEDULING_ENABLED=="Y")
									{
									echo $ratesObj->dispShiftRatesSummary($ratesObj);
									}
								}
							?>
						</tr>
                                              </table>
                                              <div class="crmsummary-createdbytxt" align="right">Created by
                                                <?=getOwnerName($row1[14])?>
                                                &nbsp;(
                                                <?=$row1[50]?>
                                                )&nbsp;-&nbsp;Modified by <span id="createdmodified">
                                                <?=getOwnerName($row1[39])?>
                                                &nbsp;(
                                                <?=$row1[51]?>
                                                )</span></div></td>
                                          </tr>
                                          <!-- --------------------END OF COMPANY DETAILS------------------------  -->
                                        </table></div></td>
                                    </tr>
                                  </table></div></div>

								  <div id="scrolldisplay"><div class="innerdivstyle">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                      <tr>
                                        <td class="ToDoSummaryM"><table class="right-side-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr <?php if(OUTLOOK_PLUG_IN=="Y" && OUTLOOK_TASK_MANAGER == "Y") { ?>style="display:none"; <? } ?>>
                                              <!-- --------------START OF TO DO PANNEL--------------------- -->
                                              
                                              <td class="remind-background"><div class="paneltitle"> 
                                                <span id="rightflt" <?php echo $rightflt;?>>
                                                  <div class="opcl-btnleftside">
                                                    <div align="left"></div>
                                                  </div>
                                                  <div><a href="javascript:classToggle(mntcmnt1,'DisplayBlock','DisplayNone',mntcmnt2,1,'<?=$module?>')" class="cl-txtlnk">
                                                    <div id='hideExp1'><i class="fa fa-angle-right fa-lg"></i></div>
                                                    </a></div>
                                                  <div class="opcl-btnrightside">
                                                    <div align="left"></div>
                                                  </div>
                                                  </span>
                                                <a href="javascript:classToggle(mntcmnt1,'DisplayBlock','DisplayNone',mntcmnt2,1,'<?=$module?>')">
 <span class="paneltitle-span"><i class='fa fa-bell fa-lg'></i> To Do Reminders</span></a></div>
                                                <div class="DisplayNone" id="mntcmnt1">
                                                  <input type="hidden" name="dat" value="<?=date("d");?>" id="dat" />
                                                  <input type="hidden" name="m" value="<?=date("m");?>" id="m" />
                                                  <input type="hidden" name="y" value="<?=date("Y");?>" id="y" />
                                                  <input type="hidden" name="h" value="<?=date("H");?>" id="h" />
                                                  <input type="hidden" name="mins" value="<?=date("i");?>" id="mins" />
                                                  <input type="hidden" name="s" value="<?=date("s");?>" id="s" />
                                                  <div id="leftflt" class="remind-inputs">
                                                    <input class="textinput" id="remind-txtinput-width" type="text" maxlength="255" name="TODO" value="<?=$next_mnth?>" onKeyPress='return disableEnterKey(event)'>
                                                    <span id='todoCal'>
                                                        <input type="text" class="textinput" id="tododate" name="tododate" value="<?php echo date('m/d/Y');?>" readonly size="13" onKeyDown="return false;"/>
                                                    <?php /* ?>
                                                    <select class="dropdown" name="tododate" id="tododate">
                                                      <?php
                                                        for($dd=0; $dd < 31;$dd++)
                                                        {
                                                            $next_day = mktime(date("H"),date("i"),date("s"),date("m"),date("d")+$dd,date("Y"));
                                                            echo "<option value='".date("n/d/Y",$next_day)."'>".date("F j,Y",$next_day)."</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                    <?php */?>
                                                    </span> 
<!--                                                    <a class="remindcal-align" href="javascript:DateSelector_Cont()"><img src="/BSOS/images/crm/sm-icon-cal.gif" width="14" height="15" title="" border="0" align=""></a>-->
                                                    <script language="JavaScript"> new tcal ({'formname':'supreg','controlname':'tododate'});</script>
                                                    <select class="dropdown" name="tododatedur" id="tododatedur">
                                                      <option selected value=''>-Select-</option>
                                                      <option value="24:00:00">12:00am</option>
                                                      <option value="00:30:00">12:30am</option>
                                                      <option value="1:00:00">1:00am</option>
                                                      <option value="1:30:00">1:30am</option>
                                                      <option value="2:00:00">2:00am</option>
                                                      <option value="2:30:00">2:30am</option>
                                                      <option value="3:00:00">3:00am</option>
                                                      <option value="3:30:00">3:30am</option>
                                                      <option value="4:00:00">4:00am</option>
                                                      <option value="4:30:00">4:30am</option>
                                                      <option value="5:00:00">5:00am</option>
                                                      <option value="5:30:00">5:30am</option>
                                                      <option value="6:00:00">6:00am</option>
                                                      <option value="6:30:00">6:30am</option>
                                                      <option value="7:00:00">7:00am</option>
                                                      <option value="7:30:00">7:30am</option>
                                                      <option value="8:00:00">8:00am</option>
                                                      <option value="8:30:00">8:30am</option>
                                                      <option value="9:00:00">9:00am</option>
                                                      <option value="9:30:00">9:30am</option>
                                                      <option value="10:00:00">10:00am</option>
                                                      <option value="10:30:00">10:30am</option>
                                                      <option value="11:00:00">11:00am</option>
                                                      <option value="11:30:00">11:30am</option>
                                                      <option value="12:00:00">12:00pm</option>
                                                      <option value="12:30:00">12:30pm</option>
                                                      <option value="13:00:00">1:00pm</option>
                                                      <option value="13:30:00">1:30pm</option>
                                                      <option value="14:00:00">2:00pm</option>
                                                      <option value="14:30:00">2:30pm</option>
                                                      <option value="15:00:00">3:00pm</option>
                                                      <option value="15:30:00">3:30pm</option>
                                                      <option value="16:00:00">4:00pm</option>
                                                      <option value="16:30:00">4:30pm</option>
                                                      <option value="17:00:00">5:00pm</option>
                                                      <option value="17:30:00">5:30pm</option>
                                                      <option value="18:00:00">6:00pm</option>
                                                      <option value="18:30:00">6:30pm</option>
                                                      <option value="19:00:00">7:00pm</option>
                                                      <option value="19:30:00">7:30pm</option>
                                                      <option value="20:00:00">8:00pm</option>
                                                      <option value="20:30:00">8:30pm</option>
                                                      <option value="21:00:00">9:00pm</option>
                                                      <option value="21:30:00">9:30pm</option>
                                                      <option value="22:00:00">10:00pm</option>
                                                      <option value="22:30:00">10:30pm</option>
                                                      <option value="23:00:00">11:00pm</option>
                                                      <option value="23:30:00">11:30pm</option>
                                                    </select>
                                                  </div>
                                                  <span id="rightflt3" class="remind-background" <?php echo $rightflt3;?>>
                                                  <div><a class="smform-txtlnk" href="javascript:UpdateToDo('','<?=$module?>')">Add</a></div>
                                                  </span>
                                                  <!--for line--->
                                                  <div class="space_5px">&nbsp;</div>
                                                  <div class="space_5px">&nbsp;</div>
                                                  <div class="line4">&nbsp;</div>
                                                  <div class="space_5px">&nbsp;</div>
                                                  <div id="sort" class="remind-sort-input"> <font class="remind-sortby">Sort By:&nbsp;</font>
                                                    <select name='todos' id='todos' class="dropdown-todo" onChange="othersToDo(this.value,'<?=$module?>')">
														<option value='All'>All To Dos</option>
														<option value='Mytodo'>My To Dos</option>
                                                      
                                                      <?php
							foreach($Users_Array as $UserNo=>$uname)
							{
								if($UserNo!=$username){?>
                                                      <option value="<?=$UserNo?>" <?=getSel($username,$UserNo)?>>
                                                      <?=$uname?>
                                                      </option>
                                                      <?}?>
                                                      <?}?>
                                                    </select>
                                                  </div>
                                                  <div class='space_5px'>&nbsp;</div>
                                                  <div id='ToDoRow'>
                                                    <?php
                            //getting all the todos   
							//Changed the query inorder to display All To Dos instead of My To Dos  --Chanikya
							
							$Rem_Sql="SELECT IF(ctime='00:00:00',DATE_FORMAT(tasklist.startdate,'%c/%e/%Y'),DATE_FORMAT(concat_ws(' ', tasklist.startdate,IF (ctime = '24:00:00','00:00:00',ctime)),'%c/%e/%Y %l:%i%p')),
										   tasklist.title,
										   tasklist.cuser,
										   datediff(tasklist.startdate,NOW()),
										   tasklist.sno,
										   '',
										   tasklist.ctime,
										   DATE_FORMAT(tasklist.startdate,'%c/%e/%Y')
									FROM tasklist
									WHERE tasktype='todo'
									  AND tasklist.contactsno='".$con_id."'
									  AND tasklist.taskstatus!='Completed'
									  AND tasklist.status NOT IN ('remove',
																  'backup',
																  'ARCHIVE')
									ORDER BY tasklist.sno DESC";							

                            $Rem_Res=mysql_query($Rem_Sql,$db);
                            $Rem_Rows=mysql_num_rows($Rem_Res);
                            if($Rem_Rows>0)
                            {
                                  $module = isset($_GET['module'] )?$_GET['module']: '';
                                while($Rem_Data=mysql_fetch_row($Rem_Res))
                				{
                					$cr_Date=explode(" ",$Rem_Data[0]);
                					$cr_Date=$cr_Date[0];
									
								$Con_Data=strtolower($Rem_Data[7])."&nbsp;-&nbsp;".$Users_Array[$Rem_Data[2]];
								$Con_Data1=strtolower($Rem_Data[0])."&nbsp;-&nbsp;".$Users_Array[$Rem_Data[2]];
								if(trim($Rem_Data[1])!='')
								{
						    	  $Con_Data.="&nbsp;-&nbsp;".$Rem_Data[1];
								  $Con_Data1.="&nbsp;-&nbsp;".$Rem_Data[1];
								  
								 } 
								 if($Rem_Data[3]<0){ //the todos which r expired
                						?>
                                                    <div class="remindtext-alert">
                                                      <!--display the del link and edit link for owners only-->
                                                      <?php if($Rem_Data[2]==$username){?>
                                                      <a class="remind-delete-align" href="javascript:delToDo('<?=$Rem_Data[4]?>','<?=$module?>')"><i class="fa fa-times-circle fa-lg"></i>
</a> (!) <a href="javascript:editTodo('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&con_id=<?=$con_id?>&module=<?=$module?>')"><?php echo $Con_Data."<br/>";?></a>
                                                      <?}else{
                							//display only content
                							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$Con_Data."<br/>";
                					}?>
                                                    </div>
                                                    <?}else {?>
                                                    <!--normal todos-->
                                                    <div class="remindtext">
                                                      <!--display the del link and edit link for owners only-->
                                                      <?php if($Rem_Data[2]==$username){?>
                                                      <a class="remind-delete-align" href="javascript:delToDo('<?=$Rem_Data[4]?>','<?=$module?>')"><img src="/BSOS/images/crm/icon-delete.gif" width="10" height="9" title="" border="0" align="left"></a> <a href="javascript:editTodo('todoPopup.php?toDoRow=<?=$Rem_Data[4]?>&con_id=<?=$con_id?>&module=<?=$module?>')"><?php echo $Con_Data1."<br/>";?></a>
                                                      <?}else{
                							//display only content
                							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$Con_Data1."<br/>";
                					 }?>
                                                    </div>
                                                    <?}
                			   }//while
                            }
                            ?>
                                                  </div>
                                                  <div class="space_5px">&nbsp;</div>
                                                </div>
                                                <div class="line3">&nbsp;</div></td>
                                            </tr>
                                            <!-- --------------END OF TO DO PANNEL--------------------- -->
                                            <tr>
                                              <!-- --------------START OF NOTES PANNEL--------------------- -->
                                              <td height="100%" class="notes-cell"><div class="line2">&nbsp;</div>
                                                <div class="paneltitle">
                                                    <span id="rightflt" <?php echo $rightflt;?>>
                                                  <div class="opcl-btnleftside">
                                                    <div align="left"></div>
                                                  </div>
                                                  <div><a href="javascript:classToggle(mntcmnt2,'DisplayBlock','DisplayNone',mntcmnt3,2,'<?=$module?>')" class="cl-txtlnk">
                                                    <div id='hideExp2'><i class="fa fa-angle-right fa-lg"></i></div>
                                                    </a></div>
                                                  <div class="opcl-btnrightside">
                                                    <div align="left"></div>
                                                  </div>
                                                  </span>
                                                    <a href="javascript:classToggle(mntcmnt2,'DisplayBlock','DisplayNone',mntcmnt3,2,'<?=$module?>')">  <span class="paneltitle-span"><i class='fa fa-sticky-note fa-lg'></i> Notes</span></a></div>
                                                <!-- Note textarea and Note type selection box -->
                                                <div class="DisplayNone" id="mntcmnt2">
												<!-- Aswini: added the processing message-->
												<span id="SPPW" style="font-size:12px;font-family:Arial,Helvetica,sans-serif;"><?=Display_Process_Msg;?></span>
                                                 <?php 
													// This function display Add Notes panel.
													dispSummaryNotesPanel($addr, 'contact', 'allNotesNew', 'allNotes', $notes_edit_list,$module);
												 ?>												 
                                                  <div id="allNotesTotal" class="notes-paneltxt-summary" style="word-wrap:break-word;word-break:break-all;scroll:auto;text-align:justify;" scroll="auto">
												  <div id="pmsg" style="text-align:justify;"></div> <!--added by swapna for procesing msg-->
                                                    <div id="allNotesNew" style="word-wrap:break-word;word-break:break-all;text-align:justify;" scroll="off"> </div>
                                                    <div id="allNotes" style="word-wrap:break-word;word-break:break-all;text-align:justify;scroll:off" scroll="off">
                                                      <?php 
													  		getAllSummayNotes('contact', $addr, 'false', 'summary','','',$module);															
														?>
                                                    </div>
                                                  </div>
                                                </div></td>
                                            </tr>
                                          <!-- ---------------------END OF NOTES PANNEL----------------  -->
                                            <tr>
                                              <!-- --------------START OF ACTIVITIES PANNEL--------------------- -->
                                              <td><div class="line2">&nbsp;</div>
                                                <div id="Activitiestd">
                                                  <div class="paneltitle"> 
                                                    <span id="rightflt" <?php echo $rightflt;?>>
                                                    <div class="opcl-btnleftside">
                                                      <div align="left"></div>
                                                    </div>
                                                    <div><a href="javascript:classToggle(mntcmnt3,'DisplayBlock','DisplayNone',mntcmnt4,3,'<?=$module?>')" class="cl-txtlnk">
                                                      <div id='hideExp3'><i class="fa fa-angle-right fa-lg"></i></div>
                                                      </a></div>
                                                    <div class="opcl-btnrightside">
                                                      <div align="left"></div>
                                                    </div>
                                                    </span>
                                                    <a href="javascript:classToggle(mntcmnt3,'DisplayBlock','DisplayNone',mntcmnt4,3,'<?=$module?>')">  <span class="paneltitle-span"><i class='fa fa-comments fa-lg'></i> Activities </span></a><span class="paneltitle-suptext">(Last 20)</span><span id="view-all-lnk"><a href="javascript:openewWindow('contact_viewact.php?addr=<?php echo $addr;?>&module=<?php echo $_GET["module"];?>','Activities',1260,600)">all activities</a></span> </div>
                                                  <div class="DisplayNone" id="mntcmnt3" >
                                                    <table width=100%>
                                                      <tr>
                                                        <td height=70 valign=center align=center><font style='FONT-SIZE:12px;  font-family:Arial, Helvetica, sans-serif'><?=Display_Process_Msg?></font> </td>
                                                      </tr>
                                                    </table>
                                                  </div>
                                                </div>
                                                <div class="line3">&nbsp;</div>
                                                <div class="line2">&nbsp;</div></td>
                                            </tr>
                                            <!-- ---------------------END OF ACTIVITIES PANNEL----------------  -->
                                            <tr>
                                              <!-- --------------START OF DOCCUMENTS PANNEL--------------------- -->
                                              <td><div id="docpanel">
                                                  <div class="paneltitle"> 
                                                    <span id="rightflt" <?php echo $rightflt;?>>
                                                    <div class="opcl-btnleftside">
                                                      <div align="left"></div>
                                                    </div>
                                                    <div><a href="javascript:classToggle(mntcmnt4,'DisplayBlock','DisplayNone',mntcmnt5,4,'<?=$module?>')" class="cl-txtlnk">
                                                      <div id='hideExp4'><i class="fa fa-angle-right fa-lg"></i></div>
                                                      </a></div>
                                                    <div class="opcl-btnrightside">
                                                      <div align="left"></div>
                                                    </div>
                                                    </span>
                                                    <a href="javascript:classToggle(mntcmnt4,'DisplayBlock','DisplayNone',mntcmnt5,4,'<?=$module?>')"> 
												  <span class="paneltitle-span"><i class='fa fa-file fa-lg'></i> Documents</span></a> 
												  <span class="paneltitle-suptext">[ <a href="javascript:newDoc('adddocument','Lead_Mngmt','<?=$module;?>')">add document</a> ] </span></div>
                                                  <div class="DisplayNone" id="mntcmnt4" >
                                                    <table width=100%>
                                                      <tr>
                                                        <td height=70 valign=center align=center><font style='FONT-SIZE:12px;  font-family:Arial, Helvetica, sans-serif'><?=Display_Process_Msg?></font> </td>
                                                      </tr>
                                                    </table>
                                                  </div>
                                                </div>
                                                <div class="line3">&nbsp;</div>
                                                <div class="line2">&nbsp;</div></td>
                                            </tr>
                                            <!-- ---------------------END OF DOCCUMENTS PANNEL----------------  -->
                                            <tr>
                                              <!-- --------------START OF JOB ORDERS PANNEL--------------------- -->
                                              <td><div id="jobpanelid">
                                                  <div class="paneltitle">
                                                    <span id="rightflt" <?php echo $rightflt;?>>
                                                    <div class="opcl-btnleftside">
                                                      <div align="left"></div>
                                                    </div>
                                                    <div><a href="javascript:classToggle(mntcmnt5,'DisplayBlock','DisplayNone',mntcmnt6,5,'<?=$module?>')" class="cl-txtlnk">
                                                      <div id='hideExp5'><i class="fa fa-angle-right fa-lg"></i></div>
                                                      </a></div>
                                                    <div class="opcl-btnrightside">
                                                      <div align="left"></div>
                                                    </div>
                                                    </span>
                                                    <?php
                                                    if($module == 'Admin_Contacts')
                                                    {
                                                        $modulejob = 'Admin_JobOrders';
                                                    }else{
                                                        $modulejob = $module;
                                                    }
                                                    ?>
                                                    <a href="javascript:classToggle(mntcmnt5,'DisplayBlock','DisplayNone',mntcmnt6,5,'<?=$module?>')"> <span class="paneltitle-span"><i class='fa fa-list-alt fa-lg'></i> Job Orders</span></a> <span class="paneltitle-suptext">(20 most current)</span> <span class="paneltitle-suptext">[ <a href="javascript:openewWindow('../../Sales/Req_Mngmt/createjoborder.php?contsum=true&frompage=contact&contid=<?php echo $addr;?>','',1000,700)">new </a> ] </span><span id="view-all-lnk"><a href="javascript:openewWindow('allJobOrders.php?frompage=contact&addr=<?php echo $addr;?>&module=<?=$modulejob?>','',750,550)">all job orders</a></span> </div>
                                                  <div class="DisplayNone" id="mntcmnt5" >
                                                    <table width=100%>
                                                      <tr>
                                                        <td height=70 valign=center align=center><font style='FONT-SIZE:12px;  font-family:Arial, Helvetica, sans-serif'><?=Display_Process_Msg?></font> </td>
                                                      </tr>
                                                    </table>
                                                  </div>
                                                </div>
                                                <div class="line3">&nbsp;</div>
                                                <div class="line2">&nbsp;</div></td>
                                            </tr>
                                            <!-- ---------------------END OF jOB ORDERS PANNEL----------------  -->
                                            <tr>
                                              <!-- --------------START OF CANDIDATES PANNEL--------------------- -->
                                              <td><div id="candpanelid">
                                                  <div class="paneltitle">
                                                  <span id="rightflt" <?php echo $rightflt;?>>
                                                    <div class="opcl-btnleftside">
                                                      <div align="left"></div>
                                                    </div>
                                                    <div><a href="javascript:classToggle(mntcmnt6,'DisplayBlock','DisplayNone',mntcmnt1,6,'<?=$module?>')" class="cl-txtlnk">
                                                      <div id='hideExp6'><i class="fa fa-angle-right fa-lg"></i></div>
                                                      </a></div>
                                                    <div class="opcl-btnrightside">
                                                      <div align="left"></div>
                                                    </div>
                                                    </span> 
                                                    <a href="javascript:classToggle(mntcmnt6,'DisplayBlock','DisplayNone',mntcmnt7,6,'<?=$module?>')"> <span class="paneltitle-span"><i class='fa fa-user fa-lg'></i> Candidates</span></a> <span class="paneltitle-suptext">(20 most recent placements)</span> <span class="paneltitle-suptext">[ <a href="javascript:doNew()">new </a> ]</span><span id="view-all-lnk"><a href="javascript:openewWindow('allCandidates.php?addr=<?php echo $addr;?>&module=<?=$module?>','',800,500)">all candidates</a></span> </div>
                                                  <div class="DisplayNone" id="mntcmnt6" >
                                                    <table width=100%>
                                                      <tr>
                                                        <td height=70 valign=center align=center><font style='FONT-SIZE:12px;  font-family:Arial, Helvetica, sans-serif'><?=Display_Process_Msg?></font> </td>
                                                      </tr>
                                                    </table>
                                                  </div>
                                                </div>
                                                <div class="line2">&nbsp;</div></td>
                                            </tr>
                                            <!-- ---------------------END OF CANDIDATES PANNEL----------------  -->
                                            <!-- TextUs SMS History -->
                                                <?php

                                                if(TEXT_US_ENABLED=='Y' && TEXTUS_USER_ACCESS=='Y')
                                                {
                                                ?>
                                                    <tr>
                                                    <td>
                                                        <div id="textuspane1" >
                                                            <div class="paneltitle">
                                                                <span id="rightflt" <?php echo $rightflt;?>>
                                                                    <div class="opcl-btnleftside"><div align="left"></div></div>
                                                                    <div><a href="javascript:classToggle(mntcmnt7,'DisplayBlock','DisplayNone',mntcmnt1,7,'<?=$module?>')"  class="cl-txtlnk">
                                                                    <div id='hideExp7'><i class="fa fa-angle-right fa-lg"></i></div></a></div>
                                                                    <div class="opcl-btnrightside"><div align="left"></div></div>
                                                                </span> 
                                                                <a href="javascript:classToggle(mntcmnt7,'DisplayBlock','DisplayNone',mntcmnt1,7,'<?=$module?>')">
                                                                <span class="paneltitle-span"><i class="fa fa-commenting fa-lg"></i> </span>
                                                                <span class="paneltitle-span">TextUs SMS History</span></a> 
                                                                <span id="view-all-lnk" class="txtUsPad"> <a href="javascript:textusReply('../../TextUs/sendMessage.php?mod=crmcontacts&sno=<?=$contasno?>','TextUs SMS',mntcmnt7,7,600,300)" ><i class="fa fa-reply fa-lg" aria-hidden="true"></i>Reply</a></span>
                                                                <span id="view-all-lnk" class="refTextUs txtUsPad">
                                                                        <a href="javascript:refreshTextusMsgs('<?=$contasno?>','crmcontacts',mntcmnt7,7)" ><i class="fa fa-refresh fa-lg"></i>Refresh</a>
                                                                </span>            
                                                            </div><!-- end of paneltitle-->
                                                            <div class="DisplayNone" id="mntcmnt7" style="padding:0px; background:#fff !important" >
                                                                <div id="textUsSmsDiv"></div>
                                                                <input type="hidden" name="contactId" id="contactId" value="<?php echo $contasno;?>">
                                                                <table width=100% id="processmsg">
                                                                    <tr><td height=70 valign=center align=center>                       
                                                                        <font style='FONT-SIZE:12px;  font-family:Arial, Helvetica, sans-serif'><?=Display_Process_Msg?></font>
                                                                    </td></tr>  
                                                                </table>
                                                                                            
                                                            </div>          
                                                        <!-- end of textuspane1 -->

                                                        <div class="line3">&nbsp;</div>
                                                        <div class="line2">&nbsp;</div>
                                                        </td>
                                                    </tr>
                                            <?php }
                                                  else
                                                  { ?>
                                                <tr>
                                                    <td>
                                                        <div id="textuspane1" style="display:none;">
                                                            <div class="paneltitle">
                                                                <a href="javascript:classToggle(mntcmnt7,'DisplayBlock','DisplayNone',mntcmnt1,7,'<?=$module?>')"></a>           
                                                            </div>
                                                            <div class="DisplayNone" id="mntcmnt7" style="padding:0px; background:#fff !important" >   
                                                                <div id="textUsSmsDiv"></div>
                                                            </div>
                                                         </div>     
                                                        </td>
                                                    </tr>
                                                <?php 
                                                    } ?>
                                                <!-- TextUs SMS History -->
												<!-- Call Em All Converstions History -->
                                                <?php

                                                if(CEA_ENABLED == 'Y' && CEA_USER_ACCESS=='Y')
                                                {
                                                ?>
                                                    <tr>
                                                    <td>
                                                        <div id="ceapane1" >
                                                            <div class="paneltitle">
                                                                <span id="rightflt" <?php echo $rightflt;?>>
                                                                    <div class="opcl-btnleftside"><div align="left"></div></div>
                                                                    <div><a href="javascript:classToggle(mntcmnt9,'DisplayBlock','DisplayNone',mntcmnt9,9,'<?=$module?>')"  class="cl-txtlnk">
                                                                    <div id='hideExp9'><i class="fa fa-angle-right fa-lg"></i></div></a></div>
                                                                    <div class="opcl-btnrightside"><div align="left"></div></div>
                                                                </span> 
                                                                <a href="javascript:classToggle(mntcmnt9,'DisplayBlock','DisplayNone',mntcmnt9,9,'<?=$module?>')">
                                                                <span class="paneltitle-span"><img src="../../images/CallEmAll.jpg" title="Call-Em-All" alt="" /></span>
                                                                <span class="paneltitle-span">Call-Em-All Conversations</span></a> 
																<span id="view-all-lnk" class="ceaPad"> <a href="javascript:ceaConversationInit('../../CEA/ceaConversationInit.php?mod=crmcontacts&sno=<?=$contasno?>','Call-Em-All-Conv-His',mntcmnt9,9)" ><i class="fa fa-reply fa-lg" aria-hidden="true"></i>Reply</a></span>
                                                                <span id="view-all-lnk" class="refTextUs txtUsPad">
																		<a href="javascript:refreshCeaConversations('<?=$contasno?>','crmcontacts',mntcmnt9,9)" ><i class="fa fa-refresh fa-lg"></i>Refresh</a>
                                                                </span>            
                                                            </div><!-- end of paneltitle-->
                                                            <div class="DisplayNone" id="mntcmnt9" style="padding:0px; background:#fff !important" >
                                                                <div id="caeConvDiv"></div>
                                                                <input type="hidden" name="ceaContactId" id="ceaContactId" value="<?php echo $contasno;?>">
                                                                <table width=100% id="ceaprocessmsg">
                                                                    <tr><td height=70 valign=center align=center>                       
                                                                        <font style='FONT-SIZE:12px;  font-family:Arial, Helvetica, sans-serif'><?=Display_Process_Msg?></font>
                                                                    </td></tr>  
                                                                </table>
                                                                                            
                                                            </div>          
                                                        <!-- end of ceapane1 -->

                                                        <div class="line3">&nbsp;</div>
                                                        <div class="line2">&nbsp;</div>
                                                        </td>
                                                    </tr>
												<?php
												}
												else
												{
												?>
													<tr>
                                                    <td>
                                                        <div id="ceapane1" style="display:none;">
                                                            <div class="paneltitle">
                                                                <a href="javascript:classToggle(mntcmnt9,'DisplayBlock','DisplayNone',mntcmnt9,9,'<?=$module?>')"></a>           
                                                            </div>
                                                            <div class="DisplayNone" id="mntcmnt9" style="padding:0px; background:#fff !important" >   
                                                                <div id="caeConvDiv"></div>
                                                            </div>
                                                         </div>     
                                                        </td>
                                                    </tr>
                                                <?php 
                                                    } ?>
                                                <!-- Call Em All Converstions History --> 
                                          </table></td>
                                      </tr>
                                    </table>
                                  </div></div></td>
                              </tr>
                            </table></td>
                        </tr>
                      </table>
                    </div>
                  </div>
				  <div class="tab-page" id="tabPage11">
                    <h2 class="tab">Edit</h2>
                    <script type="text/javascript">tp1.addTabPage( document.getElementById( "tabPage11" ) , "editmanage.php?addr=<?php echo $addr;?>&contasno=<?php echo $contasno;?>&candrow=<?php echo $candrow;?>&candcomp=<?php echo $candcomp;?>&newcont=<?php echo $newcont;?>&candrn=<?php echo $candrn;?>&Rnd=<?=$Rnd?>&cfrm=<?=$cfrm?>&var_stat=<?=$var_stat?>&typecomp=<?=$typecomp?>&module=<?=$module?>" );
			</script>
			</div>
</td>
</tr>
</table>
</div>
</div>
</form>
<form name='conreg' id='conreg' method='post'>
  <input type="hidden" name="sno_staff" id="sno_staff" value="<?php echo $addr;?>" />
  <input type="hidden" name="posid" id="posid" value='' />
</form>

<script language="javascript">
displayPrevNext('newmanage.php');
String.prototype.replaceAll = function(from,to)
{
	var temp = this;
	var index = temp.indexOf(from);
	while(index != -1)
	{
		temp = temp.replace(from,to);
		index = temp.indexOf(from);
	}
	return temp;
}

if(window.opener)
{
	try 
	{
		var keword="";
		parwin = window.opener;
		if(parwin.document.searchfrm.keywords)
		{
			keyword = parwin.document.searchfrm.keywords.value.toUpperCase();
			notesopt = parwin.document.searchfrm.notesopt;
			if(keyword!="")
			{
				profilecount = document.supreg.profilecount;
				notescount = document.supreg.notescount;

				//keywords = keyword.match(/(\w|\s)*\w(?=")|\w+/g);
				keywords = keyword.match(/("[^"]*")|([^\s"]+)/g);
				for(i=0;i<keywords.length;i++)
				{
					if(keywords[i]!="AND" && keywords[i]!="OR")
					{
						if(keywords[i].indexOf("*") != '-1'){
							keywords[i] = keywords[i].replaceAll('*','');
						}
						if(notesopt.value=="profile" || notesopt.value=="both")
						{
							$('#profilepane').highlight(keywords[i].replaceAll('"',''),profilecount,0);
							if(profilecount.value>0)
								document.getElementById("profilepane").className = "crmsummary-highlight";
						}

						if(notesopt.value=="notes" || notesopt.value=="both")
						{
							$('#allNotes').highlight(keywords[i].replaceAll('"',''),notescount,0);
							if(notescount.value>0)
								document.getElementById("allNotes").className = "crmsummary-highlight";
						}
					}
				}
			}
		}
	}
	catch(e){}	
}

if(document.supreg.dontmail.checked==true)
{
    document.getElementById("cmid").disabled = true;
    document.getElementById("cmid").style.cursor='default';
    document.getElementById("cmid").href="#";
}
else
{
    document.getElementById("cmid").disabled = false;
    document.getElementById("cmid").style.cursor='';
    document.getElementById("cmid").href=LinkEmailCont;
}
function doNew()
{
	
	var v_heigth = 470;
	var v_width  = 1000;
	var top=(window.screen.availHeight-v_heigth)/2;
	var left=(window.screen.availWidth-v_width)/2;
	var remote_act=window.open("/BSOS/Marketing/Candidates/conreg1.php?resstat=new&proid=<?php echo $contasno;?>&candrn=<?php echo $candrn; ?>",'CRM_Candidates',"width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left=0,top=0,dependent=no,resizable=yes,left="+left+",top="+top);
	remote_act.focus();
}

	<?php
		if($ptype == "Appointment")
		{
	?>
			result = "editappoint.php?addr=<?php echo $conid . '&line=' . $line . '&con_id=' . $con_id."&module_type_appoint=".$_GET['module_type_appoint'] . "&module=".$_GET['module']; ?>";
			editWin(result);
	<?php
		}
		else if($ptype == "Task")
		{
	?>
			result = "edittask.php?addr=<?php echo $conid . '&sno=' . $line . '&con_id=' . $con_id."&module_type_appoint=".$_GET['module_type_appoint'] . "&module=".$_GET['module']. "&pageName=".$pageName; ?>";
			editWin(result);
	<?php
		}
	?>

	/*window.onload   = setHeight;
	window.onresize = setHeight;
	
	function setHeight(e)
	{
		try{
			document.getElementById("mainpane").height = (parseInt(document.body.clientHeight) - 100);
			$("#leftpane").css({height:"100%"});
			$("#scrolldisplay").css({height:"100%"});
		}
		catch(e){}
	}*/
	document.getElementById("SPPW").style.display = "none";	
	
	try {
			var groupId = window.opener.document.getElementById("groupId").value;
			if(groupId > 0)
				document.getElementById("spanRmvGrpLnk").style.display = "";
	}
	catch(e){}

			//------------ This function will prevent user to close browser if the notes field has any values to save
	window.onbeforeunload = function(e) {
		if (document.getElementById("notes").value != "")
			{
				document.getElementById('notes').style.backgroundColor = "#ffff33";
				if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))  {
						e = window.event || e; 
						if ((e.clientX < 0) || (e.clientY < 0) || window.event.altKey == true)
							{ e.returnValue = 'You have notes that is not saved. \nClick on "Leave this " to close the window without saving notes. \nCick on "Stay on this page" to go back and save the notes.'; }
				} 
				else
					return 'You have notes that is not saved. \nClick on "Leave this " to close the window without saving notes. \nCick on "Stay on this page" to go back and save the notes.';
			}
	}
	function windclose(){
		var mod_const	= "<?=$candrn?>";
		if (document.getElementById("notes").value != "")
			{
				document.getElementById('notes').style.backgroundColor = "#ffff33";
				if (/MSIE (\d+\.\d+);/.test(navigator.userAgent))  {
						var conf = confirm('You have notes that is not saved. \nClick on "OK " to close the window without saving notes. \nCick on "Cancel" to go back and save the notes.');
						if(conf == true){ 

							eraseSessionVars("contacts", mod_const);
							window.close(); 
						}
				}
				else {
						eraseSessionVars("contacts", mod_const);
						window.close();
				}
			}	
			else {
				eraseSessionVars("contacts", mod_const);
				window.close();
			}
		}
<?php
	if(isset($opendivs) && !empty($opendivs))
	{
		$getOpendivs = explode(",",$opendivs);
		if(count($getOpendivs)>0)
		{
			foreach($getOpendivs as $odid)
			{
				if($odid==7)
				{
					?>
                                classToggle(mntcmnt<?=$odid?>,'DisplayBlock','DisplayNone',mntcmnt<?=$odid?>,<?=$odid?>,'<?=$module?>');
				try {
                                   classToggle(joset,'DisplayBlock','DisplayNone','',7,'<?=$module?>');
                                } catch (e) {
                                    console.log('An error has occurred: '+e.message);
                                }
					<?php
				}else
				{?>	
				classToggle(mntcmnt<?=$odid?>,'DisplayBlock','DisplayNone',mntcmnt<?=$odid?>,<?=$odid?>,'<?=$module?>');
<?php
				}
			}
		}
	}
?>
</script>
</body>
</html>