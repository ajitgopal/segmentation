<?php 
/*
Project: Sphinx Search
Purpose: Get Visual search left Preferences.
Created By: Nagaraju M.
Created Date: 31 Aug 2015
Modified Date: 31 Aug 2015

Modified Date   : Feb 28th, 2017.
Modified By     : Sanghamitra
Purpose		    : Allow all job types to resubmit the same candidate to same job order if the status is cancelled/closed/assignment closed/assignmnet over .
Task Id		    : #813679 
Line Nos        : 103,104,136,137
*/
require("global.inc");
//Sphinx includes 
include_once("sphinx_config.php");
include_once("sphinx_common_class.php"); 
include_once("json_encode.inc");

$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
global $db,$visualsearch_mod;	
if($sphinxql !='')
{
	$vsIRes = getSphinxIndexname($visualsearch_mod);
	$SPHINX_CONF['sphinx_index'] = $vsIRes['index_name'];
	$SPHINX_CONF['masters_index_name'] = $vsIRes['masters_index_name'];	
	$queryStringSearch = "SELECT * FROM sphinx_filter_columns WHERE module_id=".$visualsearch_mod." AND status=1 order by defaultorder";
	$vsSRes = mysql_query($queryStringSearch,$db);
	$SPHINX_CONF['sphinx_attributes'] = array();
	while($vsSFetch=mysql_fetch_array($vsSRes))
	{
		$SPHINX_CONF['sphinx_attributes'][$vsSFetch['index_col_name']] =  $vsSFetch['index_col_type'];
	}
	
	$jsonRep = array();
	$q = isset($_REQUEST['q'])?stripslashes(urldecode(urlencode($_REQUEST['q']))):'';
	$q = trim(strtolower(stripslashes($q)));
	$q = str_replace(',',' and ',$q);
	$q = str_replace('/',' ',$q);
	$q = preg_replace('/ or /',' | ',$q);
	$q = preg_replace('/ and /',' ',$q);
	$q = preg_replace('/ not /',' -',$q);
	$q = preg_replace('/[^\w~*\|\(\)\#\+\&\^\$\?"\/=-]+/',' ',trim(strtolower($q)));
	$q = sph_escape_string($q);
	$q = str_replace('\\','',$q);
	$q = prepareQuery($q);
	
	if (!empty($q)) {

		if($notesopt=="notes" && $q!='')
		{
			$q = '@notes '.$q;
		}else if($notesopt=="profile" && $q!='')
		{
			if($visualsearch_mod==3 || $visualsearch_mod==5){ $q = '@(profile_data,resume_data) '.$q; }elseif($visualsearch_mod==4){ $q = '@(search_data) '.$q; }else{ $q = '@(profile_data) '.$q; }
		}else if($notesopt=="resume" && $q!='')
		{
			if($visualsearch_mod==3 || $visualsearch_mod==5){ $q = '@(resume_data) '.$q; }elseif($visualsearch_mod==4){ $q = '@(search_data) '.$q; }else{ $q = '@(profile_data) '.$q; }
		}else
		{
			if($visualsearch_mod==3 || $visualsearch_mod==5){ $q = '@(profile_data,resume_data,notes) '.$q; }elseif($visualsearch_mod==4){ $q = '@(search_data,notes) '.$q; }else{ $q = '@(profile_data,notes) '.$q; }
		}
		
		//produce a version for display
		$qo = $q;
		if (strlen($qo) > 64) {
			$qo = '--complex query--';
		}
	}else
	{
		$q = '';
	}
	
	$searchstr_main = $searchstr;
		$searchstr2 = '';		
		if($_REQUEST['speedstr']!='')
		{
			$SpeedSearch_String = $_REQUEST['speedstr'];
			
			if(substr($SpeedSearch_String, 0, 1) == '?')
			{
				$SpeedSearch_String = substr($SpeedSearch_String, 1);
			}
			parse_str($SpeedSearch_String,$outputVariables);			
			
		}
		
		//echo $searchstr2;
		
			$SPHINX_CONF['page_size'] = 500000;
			$currentOffset = 0;

		 //$q.= " @accessto ALL | @accessto {$username} ";		 		
		$counter = 1;
		if($visualsearch_mod==5)
		{
			if($module!="matching_candidates")
			{
				$posdes_que="SELECT username,postitle,contact,postype FROM posdesc WHERE posid=$posid";
				$posdes_res=mysql_query($posdes_que,$db);
				$pos_row=mysql_fetch_array($posdes_res);
				$jobTypev = getManage($pos_row[3]);

				$cand_sub_queryv1="SELECT GROUP_CONCAT(res_id) AS res_ids FROM resume_status, manage WHERE manage.sno = resume_status.status AND req_id='$posid' AND manage.name NOT IN ('Closed','Cancelled') AND resume_status.pstatus!='A'";
				
				$cand_sub_query_resv1=mysql_query($cand_sub_queryv1,$db);
				$pos_norows=mysql_fetch_array($cand_sub_query_resv1);
				$resposids = $pos_norows[0];
				if($resposids!='')
				{
					$whereSLQuery = " short_lists IN ({$posid}) AND id NOT IN ({$resposids}) ";
				}else
				{
					$whereSLQuery = " short_lists IN ({$posid}) ";
				}
			}else
			{
				$whereSLQuery = " crc_accessto IN (2914988887,{$username}) ";
				if($posid!="")
				{
					$rlque="SELECT candids FROM remove_lists WHERE username='$username' AND reqid='$posid'";
					$rlres=mysql_query($rlque,$db);
					$rlrow=mysql_fetch_row($rlres);

					if($rlrow[0]!="")
						$whereSLQuery .= " AND id NOT IN (".$rlrow[0].") ";
					
					
					$posdes_que="SELECT username,postitle,contact,postype FROM posdesc WHERE posid=$posid";
					$posdes_res=mysql_query($posdes_que,$db);
					$pos_row=mysql_fetch_array($posdes_res);
					$jobType = getManage($pos_row[3]);
					
					$rsusername = array();
					$rsque="select res_id from resume_status, manage WHERE manage.sno = resume_status.status AND req_id='".$posid."' AND manage.name NOT IN ('Closed','Cancelled') AND resume_status.pstatus!='A'";
					
					$rsres=mysql_query($rsque,$db);
					while($rsrow=mysql_fetch_row($rsres))
						$rsusername[] = $rsrow[0];

					if(count($rsusername)>0)
						$whereSLQuery .=" AND id NOT IN (".implode(",",$rsusername).") ";
				}
			}
		}else
		{
			$whereSLQuery = " crc_accessto IN (2914988887,{$username}) ";
		}
					//Visual Search Hiddings
					$stopSection = "no";
					$candtype = 'no';
					$q2 = '';
					$q3 = '';
					$uString = $_GET;
					$lastAttr = end(array_keys($uString));
					$candtype = 'no';
					
				if($visualsearch_mod==1)
				{
					if(isset($_SESSION['SPHINX_Contacts_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_Contacts_sub']['cDateSearch']))
					{
						$cDateSearch = $_SESSION['SPHINX_Contacts_sub']['cDateSearch'];
						$cdateStr = explode("|",$cDateSearch);
						$cstrFromDate = strtotime($cdateStr[1]);
						$cstrToDate = strtotime($cdateStr[2]);
					}
					if(isset($_SESSION['SPHINX_Contacts_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_Contacts_sub']['mDateSearch']))
					{
						$mDateSearch = $_SESSION['SPHINX_Contacts_sub']['mDateSearch'];
						$mdateStr = explode("|",$mDateSearch);
						$mstrFromDate = strtotime($mdateStr[1]);
						$mstrToDate = strtotime($mdateStr[2]);
					}
				}
				if($visualsearch_mod==2)
				{
					if(isset($_SESSION['SPHINX_Companies_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_Companies_sub']['cDateSearch']))
					{
						$cDateSearch = $_SESSION['SPHINX_Companies_sub']['cDateSearch'];
						$cdateStr = explode("|",$cDateSearch);
						$cstrFromDate = strtotime($cdateStr[1]);
						$cstrToDate = strtotime($cdateStr[2]);

					}
					if(isset($_SESSION['SPHINX_Companies_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_Companies_sub']['mDateSearch']))
					{
						$mDateSearch = $_SESSION['SPHINX_Companies_sub']['mDateSearch'];
						$mdateStr = explode("|",$mDateSearch);
						$mstrFromDate = strtotime($mdateStr[1]);
						$mstrToDate = strtotime($mdateStr[2]);
					}	
				}
				if($visualsearch_mod==3)
				{
					if(isset($_SESSION['SPHINX_Candidates_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_Candidates_sub']['cDateSearch']))
					{
						$cDateSearch = $_SESSION['SPHINX_Candidates_sub']['cDateSearch'];
						$cdateStr = explode("|",$cDateSearch);
						$cstrFromDate = strtotime($cdateStr[1]);
						$cstrToDate = strtotime($cdateStr[2]);

					}
					if(isset($_SESSION['SPHINX_Candidates_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_Candidates_sub']['mDateSearch']))
					{
						$mDateSearch = $_SESSION['SPHINX_Candidates_sub']['mDateSearch'];
						$mdateStr = explode("|",$mDateSearch);
						$mstrFromDate = strtotime($mdateStr[1]);
						$mstrToDate = strtotime($mdateStr[2]);
					}	
				}
				if($visualsearch_mod==4)
				{
					if(isset($_SESSION['SPHINX_Joborders_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['cDateSearch']))
					{
						$cDateSearch = $_SESSION['SPHINX_Joborders_sub']['cDateSearch'];
						$cdateStr = explode("|",$cDateSearch);
						$cstrFromDate = strtotime($cdateStr[1]);
						$cstrToDate = strtotime($cdateStr[2]);

					}
					if(isset($_SESSION['SPHINX_Joborders_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['mDateSearch']))
					{
						$mDateSearch = $_SESSION['SPHINX_Joborders_sub']['mDateSearch'];
						$mdateStr = explode("|",$mDateSearch);
						$mstrFromDate = strtotime($mdateStr[1]);
						$mstrToDate = strtotime($mdateStr[2]);
					}
					if(isset($_SESSION['SPHINX_Joborders_sub']['sDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['sDateSearch']))
					{
						$sDateSearch = $_SESSION['SPHINX_Joborders_sub']['sDateSearch'];
						$stdateStr = explode("|",$sDateSearch);
						$stFromDate = strtotime($stdateStr[1]);
						$stToDate = strtotime($stdateStr[2]);
					}
					if(isset($_SESSION['SPHINX_Joborders_sub']['eDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['eDateSearch']))
					{
						$eDateSearch = $_SESSION['SPHINX_Joborders_sub']['eDateSearch'];
						$enddateStr = explode("|",$_REQUEST['eDateSearch']);
						$endFromDate = strtotime($enddateStr[1]);
						$endToDate = strtotime($enddateStr[2]);
						
					}
					if(isset($_SESSION['SPHINX_Joborders_sub']['dDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['dDateSearch']))
					{
						$dDateSearch = $_SESSION['SPHINX_Joborders_sub']['dDateSearch'];
						$duedateStr = explode("|",$dDateSearch);
						$dueFromDate = strtotime($duedateStr[1]);
						$dueToDate = strtotime($duedateStr[2]);
					}					
				}
				if($visualsearch_mod==5)
				{
					if($module!="matching_candidates")
					{
						if(isset($_SESSION['SPHINX_SLCandidates_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_SLCandidates_sub']['cDateSearch']))
						{
							$cDateSearch = $_SESSION['SPHINX_SLCandidates_sub']['cDateSearch'];
							$cdateStr = explode("|",$cDateSearch);
							$cstrFromDate = strtotime($cdateStr[1]);
							$cstrToDate = strtotime($cdateStr[2]);

						}
						if(isset($_SESSION['SPHINX_SLCandidates_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_SLCandidates_sub']['mDateSearch']))
						{
							$mDateSearch = $_SESSION['SPHINX_SLCandidates_sub']['mDateSearch'];
							$mdateStr = explode("|",$mDateSearch);
							$mstrFromDate = strtotime($mdateStr[1]);
							$mstrToDate = strtotime($mdateStr[2]);
						}
					}else
					{
						if(isset($_SESSION['SPHINX_MCCandidates_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_MCCandidates_sub']['cDateSearch']))
						{
							$cDateSearch = $_SESSION['SPHINX_MCCandidates_sub']['cDateSearch'];
							$cdateStr = explode("|",$cDateSearch);
							$cstrFromDate = strtotime($cdateStr[1]);
							$cstrToDate = strtotime($cdateStr[2]);

						}
						if(isset($_SESSION['SPHINX_MCCandidates_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_MCCandidates_sub']['mDateSearch']))
						{
							$mDateSearch = $_SESSION['SPHINX_MCCandidates_sub']['mDateSearch'];
							$mdateStr = explode("|",$mDateSearch);
							$mstrFromDate = strtotime($mdateStr[1]);
							$mstrToDate = strtotime($mdateStr[2]);
						}
					}
				}

				if($visualsearch_mod==7)
				{
					if(isset($_SESSION['SPHINX_Opportunities_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_Opportunities_sub']['cDateSearch']))
					{
						$cDateSearch = $_SESSION['SPHINX_Opportunities_sub']['cDateSearch'];
						$cdateStr = explode("|",$cDateSearch);
						$cstrFromDate = strtotime($cdateStr[1]);
						$cstrToDate = strtotime($cdateStr[2]);

					}
					if(isset($_SESSION['SPHINX_Opportunities_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_Opportunities_sub']['mDateSearch']))
					{
						$mDateSearch = $_SESSION['SPHINX_Opportunities_sub']['mDateSearch'];
						$mdateStr = explode("|",$mDateSearch);
						$mstrFromDate = strtotime($mdateStr[1]);
						$mstrToDate = strtotime($mdateStr[2]);
					}	
				}
						
						foreach ($SPHINX_CONF['sphinx_attributes'] as $attr2 => $type2) {
							//if (($attr == $attr2) && ($attr== $lastAttr)) //we dont want to filter on the current attribute. Otherwise the breakdown would only show matching. 
									//continue;
							if (isset($_REQUEST[$attr2]) && ($_REQUEST[$attr2]!='')) {
								if ($type2 == 'string') {
									if($visualsearch_mod!=7){
									$dbSearchParmVal=str_replace("\\","\\\\",addslashes($_REQUEST[$attr2]));
									$dbSearchParmVal=str_replace("\'","'",$dbSearchParmVal);
									}else{

										$dbSearchParmVal= $_REQUEST[$attr2];
									}
									$zipcode_group=0;
									if($attr2=='zip' && strpos($dbSearchParmVal,'zipcodeall') !== false){
										$stringep_zip = explode('|',$dbSearchParmVal);	
										if(count($stringep_zip)!=0){
											$se_ids_zip = array();
											$si_ids_zip = array();
											
											require("cdatabase.inc");									
											$que = "SELECT RADIANS(Latitude) latitude, RADIANS(Longitude) longitude FROM zipcodedb WHERE ZipCode='".trim($stringep_zip[0])."'  ";
											$res = mysql_query($que,$maindb);				
											$row = mysql_fetch_assoc($res);
											$latitude = $row["latitude"];
											$longitude = $row["longitude"];
											
											if($latitude!='' && $longitude!='')
											{
												$meters = $stringep_zip[1]*1609.34;
											}
											else{
												$latitude ='-1';
												$longitude ='-1';
												$meters = $stringep_zip[1]*1609.34;
											}	
											$new_dbSearchParmVal = '';
											$zipstr = ", GEODIST({$latitude}, {$longitude}, zip_latitude, zip_longitude) AS distance";
											$zipstr_where = " AND distance BETWEEN 0 AND {$meters} ";

											if($stringep_zip[3]=="E")
											{
												$zipstr_where = " AND distance > {$meters} ";
											}

											$zipcode_group=1;
										}
									}else if($attr2=="createdby" && $visualsearch_mod==7)
									{
										//echo '3==========';
										$cdateStr = explode("|",$cDateSearch);
										$cstrFromDate = strtotime($cdateStr[1]);
										$cstrToDate = strtotime($cdateStr[2]);
										$ep = explode(',',$dbSearchParmVal);
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											$i_id = implode(',',$i_ids);
											$e_id = implode(',',$e_ids);
											$createdby_i_id = ''.str_replace(",","','",$i_id).'';
											$createdby_e_id = ''.str_replace(",","','",$e_id).'';

											if(count($e_ids)!=0)
											{												
												//$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).') ';
												$q3 .= " AND ".$attr2." NOT IN ('".str_replace('\\\\', "", $createdby_e_id)."')";

											}
											if(count($i_ids)!=0)
											{												
												//$q3 .= ' AND '.$attr2.' IN ('.implode(',',$i_ids).')';
												$q3 .= " AND ".$attr2." IN ('".str_replace('\\\\', "", $createdby_i_id)."')";
											}									
										}
										if($cDateSearch!='' && $visualsearch_mod==7){
											$q3 .= " AND ctime BETWEEN {$cstrFromDate} AND {$cstrToDate} ";
										}
										
										
									}else if($visualsearch_mod==7 && $attr2 == 'modifiedby')
									{
										$mdateStr = explode("|",$mDateSearch);
										$mstrFromDate = strtotime($mdateStr[1]);
										$mstrToDate = strtotime($mdateStr[2]);	
										$ep = explode(',',$dbSearchParmVal);
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											$i_id = implode(',',$i_ids);
											$e_id = implode(',',$e_ids);
											$modifiedby_i_id = ''.str_replace(",","','",$i_id).'';
											$modifiedby_e_id = ''.str_replace(",","','",$e_id).'';
											if(count($e_ids)!=0)
											{												
												$q3 .= " AND ".$attr2." NOT IN ('".str_replace('\\\\', "", $modifiedby_e_id)."')";

											}
											if(count($i_ids)!=0)
											{												
												$q3 .= " AND ".$attr2." IN ('".str_replace('\\\\', "", $modifiedby_i_id)."')";
												//$q3 .= ' AND '.$attr2.' IN ("'.str_replace('\\', '', $modifiedby_i_id).'")';
											}									
										}
										if($mDateSearch!='' && $visualsearch_mod==7){
											$q3 .= " AND mtime BETWEEN {$mstrFromDate} AND {$mstrToDate} ";
										}
										
									}else if($visualsearch_mod==7 && ($attr2 == 'cname' || $attr2 == 'name' || $attr2 == 'steps')){

										$ep = explode(',',$dbSearchParmVal);
										
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
										$i_id = implode(',',$i_ids);
										$e_id = implode(',',$e_ids);
										$name_i_id = ''.str_replace(",","','",$i_id).'';
										$name_e_id = ''.str_replace(",","','",$e_id).'';
										if(count($e_ids)!=0)
										{												
										$q3 .= " AND ".$attr2." NOT IN ('".str_replace('\\\\', "", $name_e_id)."')";
										}
										if(count($i_ids)!=0)
										{												
										$q3 .= " AND ".$attr2." IN ('".str_replace('\\\\', "", $name_i_id)."')";
										}
									}
										
									}
									else{
										$stringep = explode(',',$dbSearchParmVal);
										if(count($stringep)!=0){
											$se_ids = array();
											$si_ids = array();
											foreach ($stringep as $se_i_id) {
												$se_i_ids = explode("|",$se_i_id);	
												if($se_i_ids[0]=='E')
												{
													$se_ids[] = $se_i_ids[1];
												}else
												{
													$si_ids[] = $se_i_ids[1];
												}
											}
											$new_dbSearchParmVal = '';									
											if(count($si_ids)!=0)
											{												
												$new_dbSearchParmVal.= ' ("^'.implode('$" | "^',$si_ids).'$")';
											}
											if(count($si_ids)==0 && count($se_ids)!=0)
											{
												$q2.= "@label_akken __AKKEN__";
											}
											if(count($se_ids)!=0)
											{										
												$new_dbSearchParmVal.= ' !("^'.implode('$" | "^',$se_ids).'$")';
											}																
										}	
									}								
									if($attr2=='areacode')
									{
										if($visualsearch_mod==3)
										{
											if($_REQUEST['psm']!='')
											{
												$areaStr = explode("|",$_REQUEST['psm']);
												$subSqlCount = array();
												if(isset($areaStr[2]))
												{
													$subSqlCount[] = $areaStr[2];
												}
												if(isset($areaStr[3]))
												{
													$subSqlCount[] = $areaStr[3];
												}
												if(isset($areaStr[4]))
												{
													$subSqlCount[] = $areaStr[4];
												}
											}else
											{
												$subSqlCount = array('hareacode','wareacode','mareacode');
											}
										}else
										{
											$subSqlCount = array('areacode');
										}
										$q2 .= ' @('.implode(',',$subSqlCount).') '.$new_dbSearchParmVal;
									}else if($zipcode_group == 0)
									{
										if($visualsearch_mod==7 && ($attr2 == 'createdby' || $attr2 == 'modifiedby'|| $attr2 == 'cname' || $attr2 == 'name' || $attr2 == 'steps')){
											$q2 .= '';
										}
										else{
											$q2 .= ' @'.$attr2.' '.$new_dbSearchParmVal;
										}
										
									}
									
								} else {
									$dbSearchParmVal = $_REQUEST[$attr2];
									if($attr2=="crc_candtype")
									{
										$ep = explode(',',$dbSearchParmVal);
										if(count($ep)!=0){
											$candtype = 'yes';
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											if(count($e_ids)!=0)
											{												
												$q3 .= ' AND crc_candtype1 NOT IN ('.implode(',',$e_ids).') ';
											}
											if(count($i_ids)!=0)
											{												
												$q3 .= ' AND crc_candtype1 IN ('.implode(',',$i_ids).')';
											}									
										}								
									}else if($attr2=="cuser" && $cDateSearch!='')
									{
										$cdateStr = explode("|",$cDateSearch);
										$cstrFromDate = strtotime($cdateStr[1]);
										$cstrToDate = strtotime($cdateStr[2]);
										$ep = explode(',',$dbSearchParmVal);
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											if(count($e_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).') ';
											}
											if(count($i_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' IN ('.implode(',',$i_ids).')';
											}									
										}
										$q3 .= " AND ctime BETWEEN {$cstrFromDate} AND {$cstrToDate} ";
										
									}else if($attr2=="muser" && $mDateSearch!='' )
									{
										$mdateStr = explode("|",$mDateSearch);
										$mstrFromDate = strtotime($mdateStr[1]);
										$mstrToDate = strtotime($mdateStr[2]);	
										$ep = explode(',',$dbSearchParmVal);
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											if(count($e_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).') ';
											}
											if(count($i_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' IN ('.implode(',',$i_ids).')';
											}									
										}
										$q3 .= " AND mtime BETWEEN {$mstrFromDate} AND {$mstrToDate} ";
									}
									else if($attr2=="start_date" && $sDateSearch!='')
									{
										$stdateStr = explode("|",$sDateSearch);
										$stFromDate = strtotime($stdateStr[1]);
										$stToDate = strtotime($stdateStr[2]);
										$ep = explode(',',$dbSearchParmVal);
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											if(count($e_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).') ';
											}
											if(count($i_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' IN ('.implode(',',$i_ids).')';
											}									
										}
										$q3 .= " AND start_date BETWEEN {$stFromDate} AND {$stToDate} ";
									}
									else if($attr2=="sub_status" && (($_SESSION['SPHINX_Joborders_sub']['savesubStatus']!='') || $savesubStatus!='' ))
									{ //Left Side Filters count
										if($_SESSION['SPHINX_Joborders_sub']['savesubStatus']!=''){
											$subStatusDates = explode('|',$_SESSION['SPHINX_Joborders_sub']['savesubStatus']);
										}
										if($savesubStatus!=''){
											$subStatusDates = explode('|',$savesubStatus);
										}
										$fromDate = strtotime($subStatusDates[0].' 00:00:00');
										$toDate = strtotime($subStatusDates[1].' 23:59:59');
										$ep = explode(',',$dbSearchParmVal);
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											if(count($e_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).') ';
											}
											if(count($i_ids)!=0)
											{												
												$q3 .= ' AND '.$attr2.' IN ('.implode(',',$i_ids).')';
											}									
										}
										$q3 .= " AND sub_date BETWEEN {$fromDate} AND {$toDate} ";
									}else{
										$ep = explode(',',$_REQUEST[$attr2]);
										
										
										if(count($ep)!=0){
											$e_ids = array();
											$i_ids = array();
											foreach ($ep as $e_i_id) {
												$e_i_ids = explode("|",$e_i_id);	
												if($e_i_ids[0]=='E')
												{
													$e_ids[] = $e_i_ids[1];
												}else
												{
													$i_ids[] = $e_i_ids[1];
												}
											}
											if(count($e_ids)!=0)
											{												
												
												
													$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).') ';
											
											}
											if(count($i_ids)!=0)
											{	
											
													$q3 .= ' AND '.$attr2.' IN ('.implode(',',$i_ids).')';
																							
												
											}											
										}
									}
								}
							}
					 } 
					 
		$vsHQuery = "SELECT * FROM sphinx_modules_groups WHERE module_id=".$visualsearch_mod." and status=1 order by id";
		$vsHRes = mysql_query($vsHQuery,$db);
		while($vsHFetch=mysql_fetch_array($vsHRes))
		{
				$vsAQuery = "SELECT * FROM sphinx_filter_columns WHERE module_id=".$visualsearch_mod." and group_id='".$vsHFetch["id"]."' AND status=1 order by defaultorder";
				$vsARes = mysql_query($vsAQuery,$db);
				while($vsAFetch=mysql_fetch_array($vsARes))
				{
				  $attr = $vsAFetch['index_col_name'];
				  $type = $vsAFetch['index_col_type'];
				  $multivalues = $vsAFetch['multivalues'];
					
						if($type=="numeric"){
							$query_tot = "COUNT(*)";
							//$where_tot = " AND {$attr} NOT IN (0)";
							$where_tot = "";
						 }else
						 {
							$query_tot = "COUNT(DISTINCT {$attr})";
							$where_tot = "";
						 }
						 
						 if($multivalues=='Y'){
							//$optionsList = " OPTION max_matches=500000";
							$optionsList = '';
						 }else
						 {
							$optionsList = '';
						 } 
						 
						 if($attr=="areacode")
						 {
							//$groupby = "wareacode, hareacode, mareacode";
							//$groupby = " hareacode ";
							$groupby = " areacode ";
						 }else
						 {
							$groupby = $attr;
						 }
						 if($visualsearch_mod==5 && $module=="matching_candidates")
						 {
							$optionsList = " OPTION ranker=matchany;";
						 }
						 if($candtype=="yes")
						{
							$candtypeSearch = ", IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1";
						}else
						{
							$candtypeSearch = '';
						}
						if($q!='' || $q2!=''){ 
							$where_cnt = '';
							if($visualsearch_mod == '7' && ($attr == 'reason_id' || $attr == 'lead_id' || $attr == 'otype_id' || $attr == 'other' || $attr == 'products' || $attr == 'amount' || $attr == 'contacts')){
								$where_cnt = 'AND '.$attr.' > 0';
							}	
							$query = "SELECT @groupby, {$attr}, COUNT(*) AS cnt {$zipstr} {$areastr} {$candtypeSearch} FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2}') $q3 AND {$whereSLQuery} {$zipstr_where} {$areastr_where} {$searchstr2} {$where_cnt} GROUP BY {$groupby} ORDER BY cnt DESC LIMIT 0,1 {$optionsList}";
						}else if($q3!='')
						{ 
							$q3 = ltrim($q3," AND");
							$where_cnt = '';
							if($visualsearch_mod == '7' && ($attr == 'reason_id' || $attr == 'lead_id' || $attr == 'otype_id' || $attr == 'other' || $attr == 'products' || $attr == 'amount'|| $attr == 'contacts')){
								$where_cnt = 'AND '.$attr.' > 0';
							}	
								$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} {$areastr} {$candtypeSearch} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$q3} AND {$whereSLQuery} {$zipstr_where} {$areastr_where} {$searchstr2} {$where_cnt} GROUP BY {$groupby} ORDER BY cnt DESC LIMIT 0,1 {$optionsList}";
							//}
							
						}else
						{ 
							$where_cnt = '';
							if($visualsearch_mod == '7' && ($attr == 'reason_id' || $attr == 'lead_id' || $attr == 'otype_id' || $attr == 'other' || $attr == 'products' || $attr == 'amount'|| $attr == 'contacts')){
								$where_cnt = 'AND '.$attr.' > 0';
							}
							$query ="SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} {$areastr} {$candtypeSearch} FROM {$SPHINX_CONF['sphinx_index']}  WHERE  {$whereSLQuery} {$zipstr_where} {$areastr_where} {$searchstr2} {$where_cnt} GROUP BY {$groupby} ORDER BY cnt DESC LIMIT 0,1 {$optionsList}";
							
						}
						
						
						if ($visualsearch_mod == "5") {
							// Adding HRM Department Condtions For CRM Matching Candidate  Module
							$query = str_replace("WHERE","WHERE deptid IN (".$deptAccesSno.") AND ",$query);
						}elseif ($visualsearch_mod == "4") {
							// Adding HRM Department Condtions For CRM Job Order Module
							$query = str_replace("WHERE","WHERE hrm_deptid IN (".$deptAccesSno.") AND ",$query);
						}else if ($visualsearch_mod == "3") {
							// Adding HRM Department Condtions For CRM Candidate Module
							$query = str_replace("WHERE","WHERE deptid IN (".$deptAccesSno.") AND ",$query);
						}else if ($visualsearch_mod == "2") {
							// Adding HRM Department Condtions For CRM Companies Module
							$query = str_replace("WHERE","WHERE hrm_deptid IN (".$deptAccesSno.") AND ",$query);
						}else if ($visualsearch_mod == "1") {
							// Adding HRM Department Condtions For CRM Contact Module
							$query = str_replace("WHERE","WHERE hrm_deptid IN (".$deptAccesSno.") AND ",$query);
						}						

						//OPTION max_matches=1000
						//echo $query."<br>";
						//echo $queryCount;
						$sphresult = mysql_query($query,$sphinxql);
						if (!$sphresult || mysql_errno($sphinxql) > 0) {
							//print "Query failed: -- please try again later.\n";
								if ($SPHINX_CONF['debug'])
								{
									//print "<br/>Error: ".mysql_error($sphinxql)."\n\n";
									return;
								}
						}else {	
							  $totalfound = '0';
							if(mysql_num_rows($sphresult)!=0){								
								$getMeta = mysql_query("SHOW META LIKE 'total%'",$sphinxql);
								while($rowMeta = mysql_fetch_array($getMeta,MYSQL_ASSOC)) {
									
									if($rowMeta['Variable_name']=="total_found")
									{
										$totalfound = $rowMeta['Value'];
									}
								}
								/*if($multivalues=='Y')
								{
									$attrstotal = $total_found;
								}else{
									$allAttrCount = mysql_fetch_array(mysql_query($queryCount,$sphinxql),MYSQL_ASSOC);
									$attrstotal = $allAttrCount['cnt'];
								}*/
								$counter++;
							}
							}	
					$jsonRep[$attr] = $totalfound;										
				}			
		}
	$newJosnarray['data'] = $jsonRep;
	echo '['.json_encode($newJosnarray).']';
	exit;	
}
else{
	echo 'ERROR: unable to connect to searchd';
	exit;	
}
?>