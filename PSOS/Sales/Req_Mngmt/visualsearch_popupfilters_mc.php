<?php
/*
Project: Sphinx Search
Purpose: Visual Search Popup filters for Matching score.
Created By: Nagaraju M.
Created Date:  31 Aug 2015
Modified Date: 31 Aug 2015

Modified Date   : Feb 28th, 2017.
Modified By     : Sanghamitra
Purpose		    : Allow all job types to resubmit the same candidate to same job order if the status is cancelled/closed/assignment closed/assignmnet over .
Task Id		    : #813679 
Line Nos        : 29,30,63,64


*/

require_once('credential_management/countries_states.php');
/*Get attribute types */
$vsAFetch = getSphinxFilterColumnTypes(3,$_GET['viewattr']);
	
if($module!="matching_candidates")
{
	$posdes_que="SELECT username,postitle,contact,postype FROM posdesc WHERE posid=$posid";
	$posdes_res=mysql_query($posdes_que,$db);
	$pos_row=mysql_fetch_array($posdes_res);
	$jobType = getManage($pos_row[3]);

	$cand_sub_query="SELECT GROUP_CONCAT(res_id) AS res_ids FROM resume_status, manage WHERE manage.sno = resume_status.status AND req_id='$posid' AND manage.name NOT IN ('Closed','Cancelled') AND resume_status.pstatus!='A'";
	
	$cand_sub_query_res=mysql_query($cand_sub_query,$db);
	$pos_norows=mysql_fetch_array($cand_sub_query_res);
	$resposids = $pos_norows[0];
	if($resposids!='')
	{
		$whereSLQuery = " crc_accessto IN (2914988887,{$username}) AND id NOT IN ({$resposids}) ";
	}else
	{
		$whereSLQuery = " crc_accessto IN (2914988887,{$username}) ";
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

	$q = isset($_REQUEST['q'])?stripslashes(urldecode($_REQUEST['q'])):'';
	$q = trim(strtolower($q));
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
			$q = '@(profile_data,resume_data) '.$q;
		}else if($notesopt=="resume" && $q!='')
		{
			$q = '@(resume_data) '.$q;
		}else
		{
			$q = '@(profile_data,resume_data,notes) '.$q;
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
	$r = '';
	$sub = '';
	$candtype = 'no';
	$attr = $_GET['viewattr'];
	if(!empty($_GET['query']) && $_GET['query']!=''){
		
		$qu1 = trim(strtolower($_GET['query']));
		$qu1 = str_replace(',',' and ',$qu1);
		$qu1 = str_replace('/',' ',$qu1);
		$qu1 = preg_replace('/ or /',' | ',$qu1);
		$qu1 = preg_replace('/ and /',' ',$qu1);
		$qu1 = preg_replace('/ not /',' -',$qu1);
		$qu1 = preg_replace('/[^\w~\|\(\)\#\+\&\^\$\?"\/=-]+/',' ',trim(strtolower($qu1)));
		$qu1 = sph_escape_string($qu1);
		$qu1 = str_replace('\\','',$qu1);
		if($qu1!=''){
			
			if($_GET['viewattr']=="crc_candtype"){
				//$r.= " @candtype ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="jobcatid"){
				$r.= " @category ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="deptid"){
				$r.= " @deptname ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="cl_sourcetype"){
				$r.= " @cl_source_type ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="cl_status"){
				$r.= " @candidate_status ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="country_id"){
				$r.= " @country_name ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="areacode"){
				$r.= " @(hareacode,wareacode,mareacode) ".cleentext(strtolower($qu1),'yes','yes')."*";
				$r2 = " @wareacode ^".strtolower($qu1)."$";
				$r3 = " @mareacode ^".strtolower($qu1)."$";
			}else if($_GET['viewattr']=="owner"){
				$r.= " @owname ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="cuser"){
				$r.= " @createdby ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="muser"){
				$r.= " @modifiedby ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="ascontact"){
				$r.= " @ascont_name ".strtolower($qu1)."*";
			}else if ((strpos($_GET['viewattr'], 'cust_') !== false) && ($vsAFetch['index_col_type']=='numeric') && ($vsAFetch['multivalues']=='N'))
			{				
				
			}else if($_GET['viewattr']=="amount"){
				$qu2 = trim(strtolower($_GET['query']));
				$qu2 = str_replace(',',' and ',$qu2);
				$qu2 = str_replace('/',' ',$qu2);
				$qu2 = preg_replace('/ or /',' | ',$qu2);
				$qu2 = preg_replace('/ and /',' ',$qu2);
				$qu2 = preg_replace('/ not /',' -',$qu2);
				//$qu2 = preg_replace('/[^\w~\|\(\)\^\$\?"\/=-]+/',' ',trim(strtolower($qu2))); // Removed because "." is replacing by " "
				$qu2 = sph_escape_string($qu2);
				$qu2 = str_replace('\\','',$qu2);
				$r.= " @".$_GET['viewattr']." ".cleentext(strtolower($qu2),'yes','yes')."*";
				$sub.= " @mvalue ^".strtolower($qu2)."*";
			}
			else if($_GET['viewattr']=='availsdate'){
				array_push($subArray,"availsdate");
			}
			else
			{
				$r.= " @".$_GET['viewattr']." ".cleentext(strtolower($qu1),'yes','yes')."*";
				$sub.= " @mvalue ^".strtolower($qu1)."*";
			}
			//$r.= " @".$_GET['viewattr']." ^".$qu1."*";
			//$sub.= " @mvalue ^".$qu1."*";
		}
		
	}
		
	//echo $r;
			$SPHINX_CONF['page_size'] = 1000;
			$SPHINX_CONF['max_matches'] = 500000;
			$SPHINX_CONF['link_format'] = '/visualsearch_popup_sl.php?'.$_SERVER['QUERY_STRING'].'&page_id=$id';
			$currentOffset = 0;
			if (!empty($_GET['page'])) {
				$currentPage = intval($_GET['page']);
				if (empty($currentPage) || $currentPage < 1) {$currentPage = 1;}
				
				$currentOffset = ($currentPage -1)* $SPHINX_CONF['page_size'];
				
				if ($currentOffset > ($SPHINX_CONF['max_matches']-$SPHINX_CONF['page_size']) ) {
					die("Only the first {$SPHINX_CONF['max_matches']} results accessible");
				}
			} else {
				$currentPage = 1;
				$currentOffset = 0;
			} 
		
			$counter = 1;
			$q2 = '';
			$q3 = '';
			
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
			 foreach ($SPHINX_CONF['sphinx_attributes'] as $attr2 => $type2) {
				//we dont want to filter on the current attribute. Otherwise the breakdown would only show matching. 
                                        if ($attr == $attr2){
							if($attr2=="cuser" && $cDateSearch!='')
							{
								$q3 .= " AND ctime BETWEEN {$cstrFromDate} AND {$cstrToDate} ";
							}
							
							if($attr2=="muser" && $mDateSearch!='')
							{
								$q3 .= " AND mtime BETWEEN {$mstrFromDate} AND {$mstrToDate} ";
								
							}
						   continue;
					 }
				
					if (!empty($_GET[$attr2])) {
                        if ($type2 == 'string') {
 							/*$loopstr1 = str_replace(",","|",$_GET[$attr2]);
							$q2 .= " @{$attr2} {$loopstr1}";*/
							
							$dbSearchParmVal = str_replace("\\","\\\\",addslashes($_GET[$attr2]));
							$dbSearchParmVal = str_replace("\'","'",$dbSearchParmVal);
							
							if($attr == $attr2)
							{
								$dbSearchParmVal = replace_ie($dbSearchParmVal);
								$dbSearchParmVal = str_replace(',','$" | "^',$dbSearchParmVal);
								if($attr2=="areacode")
								{
									$q2 .= ' @(hareacode,wareacode,mareacode) ("^'.$dbSearchParmVal.'$")';
								}else
								{
									$q2 .= ' @'.$attr2.' ("^'.$dbSearchParmVal.'$")';
								}
							}else
							{
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
								if($attr2=="areacode")
								{
									$q2 .= ' @(hareacode,wareacode,mareacode) '.$new_dbSearchParmVal;
								}else
								{
									$q2 .= ' @'.$attr2.' '.$new_dbSearchParmVal;
								}
							}
                        } else {
							$dbSearchParmVal = replace_ie($_GET[$attr2]);
							if($attr2=="crc_candtype")
							{
								$dbSearchParmVal = $_GET[$attr2];
								$ep = explode(',',$dbSearchParmVal);
								if(count($ep)!=0){
									if($attr == $attr2)
									{
										$dbSearchParmVal = replace_ie($_GET[$attr2]);
										$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") ";
									}else
										{
											
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
								}								
							}else if($attr2=="cuser" && $cDateSearch!='')
							{
								$cdateStr = explode("|",$cDateSearch);
								$cstrFromDate = strtotime($cdateStr[1]);
								$cstrToDate = strtotime($cdateStr[2]);								
								$dbSearchParmVal = $_GET[$attr2];
								$ep = explode(',',$dbSearchParmVal);
								if(count($ep)!=0){
									if($attr == $attr2)
									{
										$dbSearchParmVal = replace_ie($_GET[$attr2]);
										$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND ctime BETWEEN {$cstrFromDate} AND {$cstrToDate} ";
									}else
									{
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
								$q3 .= " AND ctime BETWEEN {$cstrFromDate} AND {$cstrToDate} ";	
								
							}else if($attr2=="muser" && $mDateSearch!='')
							{
								$mdateStr = explode("|",$mDateSearch);
								$mstrFromDate = strtotime($mdateStr[1]);
								$mstrToDate = strtotime($mdateStr[2]);
								$dbSearchParmVal = $_GET[$attr2];
								$ep = explode(',',$dbSearchParmVal);
								if(count($ep)!=0){
									if($attr == $attr2)
									{
										$dbSearchParmVal = replace_ie($_GET[$attr2]);
										$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND mtime BETWEEN {$mstrFromDate} AND {$mstrToDate} ";
									}else
									{
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
								$q3 .= " AND mtime BETWEEN {$mstrFromDate} AND {$mstrToDate} ";								
							}else{
								$dbSearchParmVal = $_GET[$attr2];
								$ep = explode(',',$dbSearchParmVal);
								if(count($ep)!=0){
									if($attr == $attr2)
									{
										$dbSearchParmVal = replace_ie($_GET[$attr2]);
										$q3 .= ' AND '.$attr2.' IN ('.$dbSearchParmVal.')';
									}else
									{										
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
											$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).')';
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
			 }


				if($_GET['viewattr']=="crc_candtype"){
					$orderby = "candtype ASC";
				}else if($_GET['viewattr']=="jobcatid"){
					$orderby = "category ASC";
				}else if($_GET['viewattr']=="deptid"){
					$orderby = "deptname ASC";
				}else if($_GET['viewattr']=="cl_sourcetype"){
					$orderby = "cl_source_type ASC";
				}else if($_GET['viewattr']=="cl_status"){
					$orderby = "candidate_status ASC";
				}else if($_GET['viewattr']=="country_id"){
					$orderby = "country_name ASC";
				}else if($_GET['viewattr']=="owner"){
					$orderby = "owname ASC";
				}else if($_GET['viewattr']=="cuser"){
					$orderby = "createdby ASC";
				}else if($_GET['viewattr']=="muser"){
					$orderby = "modifiedby ASC";
				}else if($_GET['viewattr']=="ascontact"){
					$orderby = "ascont_name ASC";
				}else
				{
					$orderby = "{$attr} ASC";
				}
			
			 $optionsList = '';
			 $optionsList = " OPTION max_matches=500000, ranker=matchany;";
			 
			 if(isset($_SESSION['SPHINX_MCCandidates_sub']['savezipCODE']) && $_SESSION['SPHINX_MCCandidates_sub']['savezipCODE'] !=''){
				$zipCode_all = explode('|',$_SESSION['SPHINX_MCCandidates_sub']['savezipCODE']);
			}
			if(isset($_SESSION['SPHINX_MCCandidates_sub']['saveareacodePSM']) && $_SESSION['SPHINX_MCCandidates_sub']['saveareacodePSM'] !=''){
				$areaCode_all = explode('|',$_SESSION['SPHINX_MCCandidates_sub']['saveareacodePSM']);
			}
			if(($_GET['radiuszip']!='' && $_GET['zipmiles']!='' && $_GET['go'] == 'Search') || ($zipCode_all[0] !='' && $zipCode_all[1] !=''))
			{
				$zipcode=($_GET['radiuszip'])?$_GET['radiuszip']:$zipCode_all[0];
				$zipmiles=($_GET['zipmiles'])?$_GET['zipmiles']:$zipCode_all[1];
				$zipstr = '';
				$zipstr_where = '';
				if($zipcode!='')
				{
					require("cdatabase.inc");									
					$que = "SELECT RADIANS(Latitude) latitude, RADIANS(Longitude) longitude FROM zipcodedb WHERE ZipCode='".trim($zipcode)."'  ";
					$res = mysql_query($que,$maindb);				
					$row = mysql_fetch_assoc($res);
					$latitude = $row["latitude"];
					$longitude = $row["longitude"];
					if($latitude!='' && $longitude!='')
					{
						$meters = $zipmiles*1609.34;
						$zipstr = ", GEODIST({$latitude}, {$longitude}, zip_latitude, zip_longitude) AS distance";
						$zipstr_where = " AND distance BETWEEN 0 AND {$meters} ";
						
					}else{
						$latitude ='-1';
						$longitude ='-1';
						$meters = $zipmiles*1609.34;
						$zipstr = ", GEODIST({$latitude}, {$longitude}, zip_latitude, zip_longitude) AS distance";
						$zipstr_where = " AND distance BETWEEN 0 AND {$meters} ";
						
					}
				}
			 }
			 
			 if(($_GET['radiusareacode']!='' && $_GET['areacodemiles']!='' && $_GET['go'] == 'Search')  || ($areaCode_all[0] !='' && $areaCode_all[1] !=''))
			 {
				$areacode=($_GET['radiusareacode'])?$_GET['radiusareacode']:$areaCode_all[0];
				$areamiles=($_GET['areacodemiles'])?$_GET['areacodemiles']:$areaCode_all[1];
				
				$areastr = '';
				$areastr_where = ''; $areastrWhere = '';
				$areastrP = ''; $areastrS = ''; $areastrM = '';
				$areacodestr = ''; $areacodestrWhere = ''; $areacodestrGrp =''; $areacodestrOrd='';$arecode_arr = array();$areacode_grp = array();$areacode_ord = array();//
				if($areacode!='')
				{
					require("cdatabase.inc");
					$que = "SELECT (MAX(Latitude)-((MAX(Latitude)-MIN(Latitude))/2)) AS latitude, (MAX(Longitude)-((MAX(Longitude)-MIN(Longitude))/2)) AS longitude FROM zipcodedb WHERE AreaCode='".trim($areacode)."' AND Longitude <> 0 AND Latitude <> 0";
					$res = mysql_query($que, $maindb);
					$rows = mysql_fetch_assoc($res);
					$latitudeArea = $rows["latitude"];
					$longitudeArea = $rows["longitude"];
					
					//$meters = round(($areamiles*1609.34),2);
					
					if(is_null($areamiles)){$areamiles=0;}
					if($latitudeArea!='' && $longitudeArea!='')
					{
						if((isset($_GET['hareacode']) && $_GET['hareacode'] !='')|| in_array('hareacode',$areaCode_all))
						{
							$areacodestr .= ", SQRT(69.1*69.1*(harea_lat_deg-{$latitudeArea})*(harea_lat_deg-{$latitudeArea}) + 53*53*(harea_long_deg-({$longitudeArea}))*(harea_long_deg-({$longitudeArea}))) as hdistance_miles";
							$arecode_arr[]= " hdistance_miles < {$areamiles} ";
							$areacode_grp[]=" hdistance_miles ";
							$areacode_ord[]=" hdistance_miles ASC ";
						}
						if((isset($_GET['wareacode'])&& $_GET['wareacode'] !='') || in_array('wareacode',$areaCode_all))
						{ 
							$areacodestr .= ", SQRT(69.1*69.1*(warea_lat_deg-{$latitudeArea})*(warea_lat_deg-{$latitudeArea}) + 53*53*(warea_long_deg-({$longitudeArea}))*(warea_long_deg-({$longitudeArea}))) as wdistance_miles";
							$arecode_arr[]= " wdistance_miles < {$areamiles} ";
							$areacode_grp[]=" wdistance_miles ";
							$areacode_ord[]=" wdistance_miles ASC ";
						}
						if((isset($_GET['mareacode'])&& $_GET['mareacode'] !='') || in_array('mareacode',$areaCode_all))
						{
							$areacodestr .= ", SQRT(69.1*69.1*(marea_lat_deg-{$latitudeArea})*(marea_lat_deg-{$latitudeArea}) + 53*53*(marea_long_deg-({$longitudeArea}))*(marea_long_deg-({$longitudeArea}))) as mdistance_miles";
							$arecode_arr[]= " mdistance_miles < {$areamiles} ";
							$areacode_grp[]=" mdistance_miles ";
							$areacode_ord[]=" mdistance_miles ASC ";
						}
						
						if(count($arecode_arr) >0){
							$areacodestrWhere = implode(" OR ",$arecode_arr);
							$areacodestr .= ", (".$areacodestrWhere.")  as distance_miles";
							$areastrWhere = " AND distance_miles=1  ";//AND mareacode != '_null_'
						}
						if(count($areacode_grp) >0){
							$areacodestrGrp = implode(" , ",$areacode_grp);
						}
						if(count($areacode_ord) >0){
							$areacodestrOrd = implode(" , ",$areacode_ord);
						}
						//$orderby = " distance_miles ASC";
						
					}else{
						$latitudeArea ='-1';
						$longitudeArea ='-1';
						
						if((isset($_GET['hareacode']) && $_GET['hareacode'] !='')|| in_array('hareacode',$areaCode_all))
						{
							$areacodestr .= ", SQRT(69.1*69.1*(harea_lat_deg-{$latitudeArea})*(harea_lat_deg-{$latitudeArea}) + 53*53*(harea_long_deg-({$longitudeArea}))*(harea_long_deg-({$longitudeArea}))) as hdistance_miles";
							$arecode_arr[]= " hdistance_miles < {$areamiles} ";
							$areacode_grp[]=" hdistance_miles ";
							$areacode_ord[]=" hdistance_miles ASC ";
						}
						if((isset($_GET['wareacode']) && $_GET['wareacode'] !='')|| in_array('wareacode',$areaCode_all))
						{ 
							$areacodestr .= ", SQRT(69.1*69.1*(warea_lat_deg-{$latitudeArea})*(warea_lat_deg-{$latitudeArea}) + 53*53*(warea_long_deg-({$longitudeArea}))*(warea_long_deg-({$longitudeArea}))) as wdistance_miles";
							$arecode_arr[]= " wdistance_miles < {$areamiles} ";
							$areacode_grp[]=" wdistance_miles ";
							$areacode_ord[]=" wdistance_miles ASC ";
						}
						if((isset($_GET['mareacode']) && $_GET['mareacode'] !='') || in_array('mareacode',$areaCode_all))
						{
							$areacodestr .= ", SQRT(69.1*69.1*(marea_lat_deg-{$latitudeArea})*(marea_lat_deg-{$latitudeArea}) + 53*53*(marea_long_deg-({$longitudeArea}))*(marea_long_deg-({$longitudeArea}))) as mdistance_miles";
							$arecode_arr[]= " mdistance_miles < {$areamiles} ";
							$areacode_grp[]=" mdistance_miles ";
							$areacode_ord[]=" mdistance_miles ASC ";
						}
						
						if(count($arecode_arr) >0){
							$areacodestrWhere = implode(" OR ",$arecode_arr);
							$areacodestr .= ", (".$areacodestrWhere.")  as distance_miles";
							$areastrWhere = " AND distance_miles=1 ";
						}
						$q2 .= " @(areacode) (^{$areacode} -null$)";
						$orderby = " distance_miles ASC";
					}
				}
			} 
			if($attr=="edu_compdate"){//Search Filter for Completion Year
				$compQue ='';
				$fromMY ='';
				$toMY = '';
				//If comp year exists in session and while searching without savefilters
				if($_GET['fmonth'] !='' && $_GET['fyear'] !='' && $_GET['tmonth'] !='' && $_GET['tyear'] !=''  && ( $_GET['go']=='Search' || !empty($_SESSION['SPHINX_MCCandidates_sub']['compyear']))){
					$start_month = array_keys($months_arr,$_GET['fmonth']);
					$end_month = array_keys($months_arr,$_GET['tmonth']);
					
					
					$fromMY = $_GET['fyear'].'-'.$start_month[0].'-01';
					$last_day = date('t',strtotime('1 '.$_GET['tmonth'].' '.$_GET['tyear']));
					$toMY = $_GET['tyear'].'-'.$end_month[0].'-'.$last_day;
					
					$comp_date="select TO_DAYS('".$fromMY."') AS to_days";
					$comp_date_res=mysql_fetch_assoc(mysql_query($comp_date,$db));
					$comp_to_date="select TO_DAYS('".$toMY."') AS to_days";
					$compto_date_res=mysql_fetch_assoc(mysql_query($comp_to_date,$db));
					
					$compQue .= " AND edu_compdate BETWEEN {$comp_date_res['to_days']} AND {$compto_date_res['to_days']} ";
				}
								
			}
			if($attr=="employment_sdate"){//Search Filter for Experience Start Date
				$expStartQue ='';
				$expStart_fromMY ='';
				$expStart_toMY = '';  
				//If employment_sdate year exists in session and while searching without savefilters
				if($_GET['exps_month'] !='' && $_GET['exps_year'] !='' && $_GET['expe_month'] !='' && $_GET['expe_year'] !='' && ( $_GET['go']=='Search' || !empty($_SESSION['SPHINX_MCCandidates_sub']['employment_sdate']))){
						$start_month = array_keys($months_arr,$_GET['exps_month']);
					$end_month = array_keys($months_arr,$_GET['expe_month']);
					$expStart_fromMY = $_GET['exps_year'].'-'.$start_month[0].'-01';
					$expstart_last_day = date('t',strtotime('1 '.$_GET['expe_month'].' '.$_GET['expe_year']));
					$expStart_toMY = $_GET['expe_year'].'-'.$end_month[0].'-'.$expstart_last_day;
					
					$expstart_date="select TO_DAYS('".$expStart_fromMY."') AS to_days";
					$expstart_date_res=mysql_fetch_assoc(mysql_query($expstart_date,$db));
						
					$expstartto_date="select TO_DAYS('".$expStart_toMY."') AS to_days";
					$expstartto_date_res=mysql_fetch_assoc(mysql_query($expstartto_date,$db));
					$expStartQue .= " AND employment_sdate BETWEEN {$expstart_date_res['to_days']} AND {$expstartto_date_res['to_days']} ";
				}
								
			}
			if($attr=="employment_edate"){//Search Filter for Experience End Date
				$expEndQue ='';
				$expEnd_fromMY ='';
				$expEnd_toMY = '';
				//If employment_edate year exists in session and while searching without savefilters
				if($_GET['expend_smonth'] !='' && $_GET['expend_syear'] !='' && $_GET['expend_smonth']!='0' && $_GET['expend_syear'] !='0'){
					$starte_month = array_keys($months_arr,$_GET['expend_smonth']);
					$expEnd_fromMY = $_GET['expend_syear'].'-'.$starte_month[0].'-01';
					
					$expend_date="select TO_DAYS('".$expEnd_fromMY."') AS to_days";
					$expend_date_res=mysql_fetch_assoc(mysql_query($expend_date,$db));
				}
				if($_GET['expend_emonth'] == '0'){
					$today=date('Y-F-d');
					$today_arr = explode('-',$today);
					$ende_month = array_keys($months_arr,$today_arr[1]);
					$expEnd_toMY = $today_arr[0].'-'.$ende_month[0].'-'.$today_arr[2];
					
					$expendto_date="select TO_DAYS('".$expEnd_toMY."') AS to_days";
					$expendto_date_res=mysql_fetch_assoc(mysql_query($expendto_date,$db));
				}
				elseif($_GET['expend_emonth'] !='' && $_GET['expend_eyear'] !='' && $_GET['expend_emonth']!='0' && $_GET['expend_eyear'] !='0'){
					$ende_month = array_keys($months_arr,$_GET['expend_emonth']);
					$expend_last_day = date('t',strtotime('1 '.$_GET['expend_emonth'].' '.$_GET['expend_eyear']));
					$expEnd_toMY = $_GET['expend_eyear'].'-'.$ende_month[0].'-'.$expend_last_day;
					
					$expendto_date="select TO_DAYS('".$expEnd_toMY."') AS to_days";
					$expendto_date_res=mysql_fetch_assoc(mysql_query($expendto_date,$db));
				}
				if($_GET['expend_smonth'] !='0' && $_GET['expend_syear'] !='0' && $_GET['expend_emonth'] !='0' && $_GET['expend_emonth'] !='Present' && $_GET['expend_eyear'] !='0' && ( $_GET['go']=='Search' || !empty($_SESSION['SPHINX_MCCandidates_sub']['employment_edate']))){
					$expEndQue .= " AND employment_edate BETWEEN {$expend_date_res['to_days']} AND {$expendto_date_res['to_days']} ";
				}
				elseif($_GET['expend_smonth'] !='' && $_GET['expend_syear'] !='' && $_GET['expend_smonth']!='0' && $_GET['expend_syear'] !='0' && ($_GET['expend_emonth']=='0' || $_GET['expend_emonth'] == 'Present')){
					$todays=$expend_date_res['to_days'];
					$expEndQue .= " AND employment_edate BETWEEN {$expend_date_res['to_days']} AND {$expendto_date_res['to_days']} ";
					//$expEndQue .= " AND  employment_edate >= {$todays}";
				}
				elseif($_GET['expend_emonth'] == 'Present'){
					$expEndQue .= " AND  employment_edate = 1 ";
				}
				elseif($_GET['expend_emonth'] !='' && $_GET['expend_eyear'] !='' && $_GET['expend_emonth']!='0' && $_GET['expend_eyear'] !='0' && $_GET['expend_smonth'] ==''){
					$todays=$expendto_date_res['to_days'];
					$expEndQue .= " AND  employment_edate <= {$todays} ";
				}
								
			}
			if($_GET['query']!=''){   
				$c = 1;
				if(in_array($attr,$subArray)){
					$c = 0;
					if($attr=="skills")
					{
						$query = "SELECT @groupby,mvalue as skills, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skillname {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else if($attr=="s_level")
					{
						$query = "SELECT @groupby,mvalue as s_level, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skilllevel {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="s_lastused")
					{
						$query = "SELECT @groupby,mvalue as s_lastused, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype lastused {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="s_type")
					{
						$query = "SELECT @groupby,mvalue as s_type, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skilltype {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="edu_country")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " country LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM countries WHERE {$sub} order by country ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$countryS = $resultS['queryIds'];
						}else
						{
							$countryS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$countryS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="edudegree_level")
					{
						$query = "SELECT @groupby,mvalue as edudegree_level, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype edu_level {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="edu_compdate" || $attr=="employment_sdate" || $attr=="employment_edate")
					{//Search Filter for Completion Year,Experience Start and End dates
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username})  GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					
					} else
					if($attr=="employment_city")
					{
						$query = "SELECT @groupby,mvalue as employment_city, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_city {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="employment_state")
					{
						$query = "SELECT @groupby,mvalue as employment_state, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_state {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="employment_country")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " country LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM countries WHERE {$sub} order by country ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$countryS = $resultS['queryIds'];
						}else
						{
							$countryS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$countryS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="employment")
					{
						$query = "SELECT @groupby,mvalue as employment, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_cname {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="employment_type")
					{
						$query = "SELECT @groupby,mvalue as employment_type, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_ftitle {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="contact_method")
					{
							$cMethod = '';
						$contMthdAttributes = array("1001"=>"Phone","2002"=>"Mobile","3003"=>"Fax","4004"=>"Email");
						$matchingContMthdRecords = preg_grep("/{$_GET['query']}/i",$contMthdAttributes);						

						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt, weight() AS w FROM {$SPHINX_CONF['sphinx_index']} WHERE {$whereSLQuery} {$cMethod} GROUP BY {$attr} ORDER BY w DESC LIMIT 0,10 OPTION ranker=sph04";
					} else
					if($attr=="cre_type")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " credential_type LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(id) as queryIds FROM manage_credentials_type WHERE {$sub} order by credential_type ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$cretypeS = $resultS['queryIds'];
						}else
						{
							$cretypeS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$cretypeS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					
					if($attr=="cre_name")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " credential_name LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(id) as queryIds FROM manage_credentials_name WHERE {$sub} order by credential_name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$crenameS = $resultS['queryIds'];
						}else
						{
							$crenameS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$crenameS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="cre_number")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " cre_number LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(cre_number)) as queryIds FROM candidate_credentials WHERE {$sub} order by cre_number ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$crenumberS = $resultS['queryIds'];
						}else
						{
							$crenumberS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$crenumberS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="availsdate")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " availsdate LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(UNIX_TIMESTAMP((availsdate))) as queryIds FROM candidate_prof WHERE {$sub} order by availsdate ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$creacqS = $resultS['queryIds'];
						}else
						{
							$creacqS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$creacqS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="cre_acquireddate")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " acquired_date LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(acquired_date)) as queryIds FROM candidate_credentials WHERE {$sub} order by acquired_date ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$creacqS = $resultS['queryIds'];
						}else
						{
							$creacqS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$creacqS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="cre_validfrom")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " valid_from LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(valid_from)) as queryIds FROM candidate_credentials WHERE {$sub} order by valid_from ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$crefromS = $resultS['queryIds'];
						}else
						{
							$crefromS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$crefromS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="cre_validto")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " valid_to LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(valid_to)) as queryIds FROM candidate_credentials WHERE {$sub} order by valid_to ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$cretoS = $resultS['queryIds'];
						}else
						{
							$cretoS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$cretoS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="cre_state")
					{
						$sub='';
						if($_GET['query']!='')
						{
							foreach($displayStateNames as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $getCredStates[] = $key;
								}
							}
						}
						$getCredStates = array_unique($getCredStates);
						
						if(!empty($getCredStates))
						{
							$credState_ids = implode(",",$getCredStates);
							$credState_ids = "'".str_replace(",","','",$credState_ids)."'";
						
						}else
						{
							$credState_ids = "'0'";
						}
						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(state_id)) as queryIds FROM candidate_credentials where state_id !='' limit 1";
						$resultS = mysql_fetch_row(mysql_query($sub_query,$db));
						$db_creStateIds = array();
						$db_creStateIds = explode(',',$resultS[0]);
						
						$same_crestaeIds =array();
						$same_crestaeIds = array_intersect($getCredStates,$db_creStateIds);
						$crecStateCom = "'".str_replace(",","','",implode(',',$same_crestaeIds))."'";
						$sub_query_csrState = "SELECT explodeStrAndConcat(',',".$crecStateCom.") as queryIds ";
						$sub_query_csrState_res = mysql_fetch_row(mysql_query($sub_query_csrState,$db));
						if($sub_query_csrState_res[0]!='')
						{
							$cretoState = $sub_query_csrState_res[0];
						}else
						{
							$cretoState = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$cretoState}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="role_types")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " roletitle LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM company_commission WHERE {$sub} order by roletitle ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$roletypeS = $resultS['queryIds'];
						}else
						{
							$roletypeS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$roletypeS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="role_persons")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " AND e.name LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(e.username)) as queryIds FROM emp_list e LEFT JOIN hrcon_compen h ON (h.username = e.username) LEFT JOIN manage m ON (m.sno = h.emptype) WHERE e.lstatus != 'DA' AND e.lstatus != 'INACTIVE' AND e.empterminated !='Y' AND h.ustatus = 'active' AND m.type = 'jotype' AND m.status='Y' AND m.name IN ('Internal Direct', 'Internal Temp/Contract') AND h.job_type <> 'Y' {$sub} ORDER BY e.name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$rolepersonS = $resultS['queryIds'];
						}else
						{
							$rolepersonS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$rolepersonS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else	
					if($attr=="role_rates")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " AND entity_roledetails.rate LIKE '{$_GET['query']}%'";
						}

						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(CRC32(entity_roledetails.rate))) AS queryIds FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMCandidate' {$sub} order by entity_roledetails.rate ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$roleRates = $resultS['queryIds'];
						}else
						{
							$roleRates = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$roleRates}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else					
					if($attr=="role_commtype")
					{
						$getCommTypes = '';
						if($_GET['query']!='')
						{
							foreach($candCommTypes_attributes as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $getCommTypes[] = $key;
								}
							}
							$getCommTypes = array_unique($getCommTypes);
						}
						if($getCommTypes!='')
						{
							$rolecommS = implode(",",$getCommTypes);
						}else
						{
							$rolecommS = "0";
						}
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$rolecommS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else					
					if($attr=="crmgroups")
					{
						$getgroupids = '';
						if($_GET['query']!='')
						{
							foreach($candGroups_attributes as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $getgroupids[] = $key;
								}
							}
							$getgroupids = array_unique($getgroupids);
						}
						if($getgroupids!='')
						{
							$rolecommS = implode(",",$getgroupids);
						}else
						{
							$rolecommS = "0";
						}
						
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$rolecommS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else
					if($attr=="des_jtype")
					{
						$gettypeids = '';
						if($_GET['query']!='')
						{
							foreach($desired_jtype_arr as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $gettypeids[] = $key;
								}
							}
							$gettypeids = array_unique($gettypeids);
						}
						if($gettypeids!='')
						{
							$desTypes = implode(",",$gettypeids);
						}else
						{
							$desTypes = "0";
						}
						
						 $query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$desTypes}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else
					if($attr=="des_jstatus")
					{
						$gettypeids = '';
						if($_GET['query']!='')
						{
							foreach($desired_jstatus_arr as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $gettypeids[] = $key;
								}
							}
							$gettypeids = array_unique($gettypeids);
						}
						if($gettypeids!='')
						{
							$desTypes = implode(",",$gettypeids);
						}else
						{
							$desTypes = "0";
						}
						
						 $query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$desTypes}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="s_dept")
					{
						$getDept_ids = '';
						if($_GET['query']!='')
						{
							foreach($skill_departments as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $getDept_ids[] = $key;
								}
							}
							$getDept_ids = array_unique($getDept_ids);
						}
						if($getDept_ids!='')
						{
							$skill_dept = implode(",",$getDept_ids);
						}else
						{
							$skill_dept = "0";
						}
						
						 $query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$skill_dept}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else
					if($attr=="s_cat")
					{
						$getCat_ids = '';
						if($_GET['query']!='')
						{
							foreach($skill_categories as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $getCat_ids[] = $key;
								}
							}
							$getCat_ids = array_unique($getCat_ids);
						}
						if($getCat_ids!='')
						{
							$skill_cat = implode(",",$getCat_ids);
						}else
						{
							$skill_cat = "0";
						}
						
						 $query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$skill_cat}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else
					if($attr=="s_spec")
					{
						$getSpec_ids = '';
						if($_GET['query']!='')
						{
							foreach($skill_specialities as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $getSpec_ids[] = $key;
								}
							}
							$getSpec_ids = array_unique($getSpec_ids);
						}
						if($getSpec_ids!='')
						{
							$skill_spec = implode(",",$getSpec_ids);
						}else
						{
							$skill_spec = "0";
						}
						
						 $query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$skill_spec}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else
					{
						$getudfs = '';
						$udf_optionattributes =  udf_checkboxoptions($attr);
						if($_GET['query']!='')
						{
							foreach($udf_optionattributes as $key => $value) {
								if (strpos(strtolower($value), strtolower($_GET['query'])) !== false) {
								   $getudfs[] = $key;
								}
							}
							$getudfs = array_unique($getudfs);
						}
						if($getudfs!='')
						{
							$rolecommS = implode(",",$getudfs);
						}else
						{
							$rolecommS = "0";
						}
						
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} AND {$attr} IN ({$rolecommS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					
					}
				}else
				{
					 if($attr=="areacode")
					 {
						$query = "SELECT @groupby,hareacode ,wareacode,mareacode, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE MATCH('{$r}') AND {$whereSLQuery} GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
						
					 }else if($attr=="crc_candtype"){
						
						$candAttributes = array("577727202"=>"Candidate","2766739447"=>"Employee","189759857"=>"My Candidate","0"=>"null");
						
							$searchStr = $_GET['query'];
							$matchingRecords = preg_grep("/{$searchStr}/i",$candAttributes);					
						if(count($matchingRecords) > 0)
						{
							$candAttributesStr = implode(",",array_keys($matchingRecords));
							
							$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE MATCH('{$r}') AND {$whereSLQuery} AND crc_candtype1 IN ({$candAttributesStr}) GROUP BY crc_candtype1 ORDER BY cnt ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						}
						else
						{
							$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE MATCH('') AND {$whereSLQuery} AND crc_candtype1 IN (0) GROUP BY crc_candtype1 ORDER BY cnt ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						}						
					 }else
					 {
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE MATCH('{$r}') AND {$whereSLQuery} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					 }
					
				}
			
			}else{
			
				if($q!='' || $q2!=''){
				
					 if($attr=="areacode")
					 {
						$query = "SELECT @groupby,hareacode ,wareacode,mareacode, COUNT(*) AS cnt {$zipstr} {$areacodestr} FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2}') $q3 AND {$whereSLQuery} {$zipstr_where} {$areastrWhere} GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
						
					 }else if($attr=="crc_candtype"){
					 
						$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2}') $q3 AND {$whereSLQuery} {$zipstr_where} {$areastr_where} GROUP BY crc_candtype1 ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
					 }else
					 {
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2}') $q3 AND {$whereSLQuery} {$zipstr_where} {$areastr_where} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					 }
					
				}else if($q3!='')
				{
				
					$q3 = substr($q3,4);
					if($attr=="areacode")
					{
						$query = "SELECT @groupby,hareacode,wareacode,mareacode, COUNT(*) AS cnt {$zipstr} {$areacodestr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$q3} AND {$whereSLQuery} {$zipstr_where} {$areastrWhere} GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
						
					}else if($attr=="crc_candtype"){
						$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$q3} AND {$whereSLQuery} {$zipstr_where} {$areastr_where} GROUP BY crc_candtype1 ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					 }else
					 {
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$q3} AND  {$whereSLQuery} {$zipstr_where} {$areastr_where} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					 }
					
				}else
				{
					if($attr=="areacode")
					{
						$query = "SELECT @groupby,hareacode,wareacode,mareacode, COUNT(*) AS cnt {$zipstr} {$areacodestr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} {$zipstr_where} {$areastrWhere} GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
					
					}else if($attr=="crc_candtype"){
					
							$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} {$zipstr_where} {$areastr_where} GROUP BY crc_candtype1 ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else if($attr=="amount" && ($minsal!='' || $maxsal!='') ){
						
							$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE  {$whereSLQuery}";
							if($minsal!='' && $maxsal!='')
							{
							  $query .= " AND min_salary >= ".floatval($minsal)." AND max_salary <= ".floatval($maxsal)."  AND min_salary <= ".floatval($maxsal)." ";							  
							}else if($minsal!='' && $maxsal=='')
							{
							  $query .= " AND min_salary >= ".floatval($minsal)." ";	
							}else if($minsal=='' && $maxsal!='')
							{
							  $query .= " AND max_salary <= ".floatval($maxsal)." AND  min_salary <= ".floatval($maxsal)." ";	
							}
							if($currency!='')
							{
								 $query .= " AND currency = ".$currency." ";
							}
							if($salarytype!='')
							{
								 $query .= " AND rperiod = ".$salarytype." ";
							}
							 $query .= " {$zipstr_where} {$areastr_where} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";							
					}else if($attr=="edu_compdate")
					{//Search Filter for Completion Year
					
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) {$compQue} GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
					}else if($attr=="employment_sdate")
					{//Search Filter for Experience Start Date
					
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) {$expStartQue} GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					
					}else if($attr=="employment_edate")
					{//Search Filter for Experience End Date
					
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) {$expEndQue} GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					
					}else
					{
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$whereSLQuery} {$zipstr_where} {$areastr_where} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}
					
				}
				$c = 1;
			}
				$TZque = "SELECT time FROM timezone WHERE phpvar = '".$user_timezone[1]."'";
				$TZres = mysql_fetch_row(mysql_query($TZque,$db));
				$tizoneoffsetTime = strtotime("0000-00-00 ".substr($TZres[0],1)."")-strtotime("0000-00-00 00:00:00");
				$tizoneoffsetType = substr($TZres[0],0,1);
				$tizoneoffset = $tizoneoffsetType.$tizoneoffsetTime;
				
				if($attr=="owner"){ $query = str_replace("@groupby,","@groupby,owname,",$query);  } 
				if($attr=="cuser"){ $query = str_replace("@groupby,","@groupby,createdby,",$query);
									if($fromdate!='' && $todate!='')
									{
										$strFromDate = strtotime($fromdate);
										$strToDate = strtotime($todate);
										$query = str_replace("WHERE","WHERE ctime BETWEEN {$strFromDate} AND {$strToDate} AND ",$query);
									}
								 }
				if($attr=="muser"){ $query = str_replace("@groupby,","@groupby,modifiedby,",$query);  
									if($fromdate!='' && $todate!='')
									{
										$strFromDate = strtotime($fromdate);
										$strToDate = strtotime($todate);
										$query = str_replace("WHERE","WHERE mtime BETWEEN {$strFromDate} AND {$strToDate} AND ",$query);
									}
								}
								
				if($attr=="ascontact"){ $query = str_replace("@groupby,","@groupby,ascont_name,",$query);  }
				
				if($attr=="ctime"){ $query = str_replace(array("@groupby,","GROUP BY ctime ORDER BY ctime"),array("@groupby,YEARMONTHDAY(ctime) as cdate,","GROUP BY cdate ORDER BY cdate"),$query);  }
				if($attr=="mtime"){ $query = str_replace(array("@groupby,","GROUP BY mtime ORDER BY mtime"),array("@groupby,YEARMONTHDAY(mtime) as mdate,","GROUP BY mdate ORDER BY mdate"),$query);  }				
				if($candtype=="yes") {
					$query = str_replace("@groupby,","@groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1,",$query);
				}
				// adding Department Conditions
				$query = str_replace("WHERE","WHERE deptid IN (".$deptAccesSno.") AND ",$query);

				//echo $query;
				//echo $query2;
				//echo $query3;
				$result = mysql_query($query,$sphinxql);
				if (!$result || mysql_errno($sphinxql) > 0) {
					print "<div style='color:red;font-size:12px;font-familty:Arial;'>AkkuSearch is unable to service your request at this time. Please try again later.\n</div>";
					
					if ($SPHINX_CONF['debug'])
						print "<br/>Error: ".mysql_error($sphinxql)."\n\n";
						return;
				}else {
				
				$result2 = mysql_query("SHOW META",$sphinxql);
				$res = array();
				while($row = mysql_fetch_array($result2,MYSQL_ASSOC)) {
					$res[$row['Variable_name']] = $row['Value'];
				}
				 $resultCount = $res['total_found'];
				 $numberOfPages = ceil($res['total']/$SPHINX_CONF['page_size']);
			
				$attrstotal = 0;
				$attrscontent = '';
					$ress = array();
					if(mysql_num_rows($result)!=0){
					
						if($SPHINX_CONF['sphinx_attributes'][$attr]=="numeric")
						{
							
							if(in_array($attr,$subArray)){
							
									$attrsIds='';
									$attrsNewArray = array();
									while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
									
										if($attrsIds==''){
											$attrsIds = $row['@groupby'];
										}else{
											$attrsIds.= ','.$row['@groupby'];
										}
										
												$attrsNewArray[$row['@groupby']] = array("@groupby"=>$row['@groupby'],
																	 "{$attr}"=>$row[$attr],
																	 "cnt"=>$row['cnt']);

									}
									
									if($attr=="skills" && $attrsIds!=''){
									
										$queSkilsType="select sno, mvalue as skillname, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='skillname' ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resSkilsType=mysql_query($queSkilsType,$db);
										while($rowSkillsType=mysql_fetch_assoc($resSkilsType))
										{
											$attrstotal += $attrsNewArray[$rowSkillsType['sno']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowSkillsType['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowSkillsType['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsType['skillname']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsType['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									}else if($attr=="s_level" && $attrsIds!=''){
										$queSkilsType="select sno, mvalue as skilllevel, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='skilllevel' {$q_sql} ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLevel=mysql_fetch_assoc($resSkilsType))
										{
											$attrstotal += $attrsNewArray[$rowSkillsLevel['sno']]['cnt'];
											$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowSkillsLevel['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowSkillsLevel['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsLevel['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsLevel['skilllevel']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsLevel['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="s_lastused" && $attrsIds!=''){
										$queSkilsType="select sno, mvalue as lastused, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='lastused' {$q_sql} ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLastused=mysql_fetch_assoc($resSkilsType))
										{
											$attrstotal += $attrsNewArray[$rowSkillsLastused['sno']]['cnt'];
											$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowSkillsLastused['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowSkillsLastused['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsLastused['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsLastused['lastused']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsLastused['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="s_type" && $attrsIds!=''){
										$queSkilsType="select sno, mvalue as skilltype, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='skilltype' {$q_sql} ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLastused=mysql_fetch_assoc($resSkilsType))
										{
											$attrstotal += $attrsNewArray[$rowSkillsLastused['sno']]['cnt'];
											$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowSkillsLastused['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowSkillsLastused['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsLastused['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsLastused['skilltype']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsLastused['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="edu_country" && $attrsIds!=''){
										$queEduType="select sno,educountry, educountry AS snoIDs  from candidate_edu where educountry IN({$attrsIds}) AND educountry!='0' GROUP BY educountry ORDER BY FIELD(educountry, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resEduType=mysql_query($queEduType,$db);
										$attrstotal = 0;
										while($rowEduType=mysql_fetch_assoc($resEduType))
										{
											
											$attrstotal += $attrsNewArray[$rowEduType['educountry']]['cnt'];
											$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowEduType['snoIDs'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowEduType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowEduType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($country_attributes[$rowEduType['educountry']]))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowEduType['educountry']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="edudegree_level" && $attrsIds!=''){
										$queEduType="select sno, mvalue as edudegree_level, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='edu_level' ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resEduType=mysql_query($queEduType,$db);
										$attrstotal = 0;
										while($rowEduType=mysql_fetch_assoc($resEduType))
										{
											$attrstotal += $attrsNewArray[$rowEduType['sno']]['cnt'];
											$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowEduType['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowEduType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowEduType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowEduType['edudegree_level']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowEduType['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
										
									} else if($attr=="edu_compdate" && $attrsIds!=''){//Search Filter for Completion Year
										
											
											if($_GET['query']){
												$search_date = explode('-',$_GET['query']);
												$start_month = array_keys($months_arr,$search_date[0]);
												$fromMY = $search_date['1'].'-'.$start_month[0].'-01';
												$last_day = date('t',strtotime('1 '.$start_month[0].' '.$search_date[1]));
												$toMY =  $search_date[1].'-'.$start_month[0].'-'.$last_day;
												
												$comp_date="select TO_DAYS('".$fromMY."') AS to_days";
												$comp_date_res=mysql_fetch_assoc(mysql_query($comp_date,$db));
												
													
												$compto_date="select TO_DAYS('".$toMY."') AS to_days";
												$compto_date_res=mysql_fetch_assoc(mysql_query($compto_date,$db));
											}
												
											$compDate = explode(',',$attrsIds);
												
											foreach($compDate as $date){
												if($comp_date_res['to_days'] !='' && $compto_date_res['to_days']!='' && $comp_date_res['to_days'] <=$date && $compto_date_res['to_days'] >=$date){
											
													if($date!=0)
													{
														$que_date="select FROM_DAYS(".$date.") AS from_days";
														$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
														$que_date_res_arr=explode('-',$que_date_res['from_days']);
														
														$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0]; 
														$checked = '';
														if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
															$checked = 'checked="checked"' ; 
														}
														$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
														if($c==1){
															$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
														}
														$attrscontent .="</li>";
													}	
												}elseif($fromMY =='' && $toMY==''){
													$que_date="select FROM_DAYS(".$date.") AS from_days";
													$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
													$que_date_res_arr=explode('-',$que_date_res['from_days']);
														if($date!=0)
														{
															$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0]; 
														}
														else
														{
															$print_value ='Null';
														} 
														$checked = '';
														if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
															$checked = 'checked="checked"' ; 
														}
														$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
														if($c==1){
															$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
														}
														$attrscontent .="</li>";
												}
													
													
												
											}
									}else if($attr=="employment_sdate" && $attrsIds!=''){//Search Filter for Experience Start Date
										
										
										if($_GET['query']){
											$search_date = explode('-',$_GET['query']);
											$start_month = array_keys($months_arr,$search_date[0]);
											$expStart_fromMY = $search_date['1'].'-'.$start_month[0].'-01';
											$expstart_last_day = date('t',strtotime('1 '.$start_month[0].' '.$search_date[1]));
											$expStart_toMY =  $search_date[1].'-'.$start_month[0].'-'.$expstart_last_day;
											
											$expstart_date="select TO_DAYS('".$expStart_fromMY."') AS to_days";
											$expstart_date_res=mysql_fetch_assoc(mysql_query($expstart_date,$db));
											
												
											$expstartto_date="select TO_DAYS('".$expStart_toMY."') AS to_days";
											$expstartto_date_res=mysql_fetch_assoc(mysql_query($expstartto_date,$db));
										}
											
										$expStartDate = explode(',',$attrsIds);
											
										foreach($expStartDate as $date){
										
										
											if($expstart_date_res['to_days'] !='' && $expstartto_date_res['to_days']!='' && $expstart_date_res['to_days'] <=$date && $expstartto_date_res['to_days'] >=$date){
												if($date!=0)
												{
													$que_date="select FROM_DAYS(".$date.") AS from_days";
													$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
													$que_date_res_arr=explode('-',$que_date_res['from_days']);
													
													$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0]; 
													$checked = '';
													if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
														$checked = 'checked="checked"' ; 
													}
													$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
													if($c==1){
														$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
													}
													$attrscontent .="</li>";
												}	
											}elseif($expStart_fromMY =='' && $expStart_toMY==''){
												$que_date="select FROM_DAYS(".$date.") AS from_days";
												$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
												$que_date_res_arr=explode('-',$que_date_res['from_days']);
													if($date!=0)
													{
														$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0]; 
													}
													else
													{
														$print_value ='Null';
													} 
													$checked = '';
													if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
														$checked = 'checked="checked"' ; 
													}
													$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
													if($c==1){
														$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
													}
													$attrscontent .="</li>";
												}
													
													
												
											}
									} else if($attr=="employment_edate" && $attrsIds!=''){//Search Filter for Experience End Date
										
											$present='';
										if($_GET['expend_emonth'] == 'Present'){
											$present = '1';
										}
										if($_GET['query']){
										
											if($_GET['query'] == 'Present'){
												$present = '1';
											}
											else{
												$search_date = explode('-',$_GET['query']);
												$start_month = array_keys($months_arr,$search_date[0]);
												$expEnd_fromMY = $search_date['1'].'-'.$start_month[0].'-01';
												$expend_last_day = date('t',strtotime('1 '.$start_month[0].' '.$search_date[1]));
												$expEnd_toMY = $search_date[1].'-'.$start_month[0].'-'.$expend_last_day;
												
											}
										}
										if($expEnd_fromMY !=''){
											$expend_date="select TO_DAYS('".$expEnd_fromMY."') AS to_days";
											$expend_date_res=mysql_fetch_assoc(mysql_query($expend_date,$db));
										}
										if($expEnd_toMY != ''){
											$expendto_date="select TO_DAYS('".$expEnd_toMY."') AS to_days";
											$expendto_date_res=mysql_fetch_assoc(mysql_query($expendto_date,$db));
										}
											
										$expEndDate = explode(',',$attrsIds);
											
										foreach($expEndDate as $date){
											
											
											if($present =='1'  && $expEnd_fromMY =='' && $expEnd_toMY==''){
												if($date=='1'){
													$print_value = 'Present';
													$checked = '';
													if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
														$checked = 'checked="checked"' ; 
													}
													$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
													if($c==1){
														$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
													}
													$attrscontent .="</li>";
												}
											}
											elseif($expEnd_fromMY =='' && $expEnd_toMY==''){
												
													if($date=='1')
													{
														$print_value = 'Present'; 
													}
													elseif($date!=0)
													{
														$que_date="select FROM_DAYS(".$date.") AS from_days";
														$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
														$que_date_res_arr=explode('-',$que_date_res['from_days']);
														$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0];
													}
													else
													{
														$print_value ='Null';
													} 
													$checked = '';
													if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
														$checked = 'checked="checked"' ; 
													}
													$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
													if($c==1){
														$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
													}
													$attrscontent .="</li>";
												}
												elseif($expEnd_fromMY !='' && $expEnd_toMY!='' && $date!=0 && $date!='1'){
												
													$que_date="select FROM_DAYS(".$date.") AS from_days";
													$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
													$que_date_res_arr=explode('-',$que_date_res['from_days']);
													$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0]; 
													if($expend_date_res['to_days'] !='' && $expend_date_res['to_days'] <=$date && $expendto_date_res['to_days']!=''  && $expendto_date_res['to_days'] >=$date&& $date!=0){
														$checked = '';
														if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
															$checked = 'checked="checked"' ; 
														}
														$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
														if($c==1){
															$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
														}
														$attrscontent .="</li>";
													}
												}
												elseif($expEnd_fromMY !='' && $expEnd_toMY=='' && $date!=0 && $date!='1'){
												
													$que_date="select FROM_DAYS(".$date.") AS from_days";
													$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
													$que_date_res_arr=explode('-',$que_date_res['from_days']);
													$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0]; 
													if($expend_date_res['to_days'] !='' && $expend_date_res['to_days'] <=$date && $date!=0){
														$checked = '';
														if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
															$checked = 'checked="checked"' ; 
														}
														$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
														if($c==1){
															$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
														}
														$attrscontent .="</li>";
													}elseif($present == '1' && $date=='1'){
														$print_value = 'Present';
														$checked = '';
														if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
															$checked = 'checked="checked"' ; 
														}
														$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
														if($c==1){
															$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
														}
														$attrscontent .="</li>";
													}
												}
												elseif($expEnd_fromMY =='' && $expEnd_toMY!='' && $date!=0 && $date!='1'){
													$que_date="select FROM_DAYS(".$date.") AS from_days";
													$que_date_res=mysql_fetch_assoc(mysql_query($que_date,$db));
													$que_date_res_arr=explode('-',$que_date_res['from_days']);
													$print_value = $months_arr[$que_date_res_arr[1]].'-'.$que_date_res_arr[0]; 
													if( $expendto_date_res['to_days']!=''  && $expendto_date_res['to_days'] >=$date&& $date!=0){
														$checked = '';
														if(in_array($print_value,$_SESSION['SPHINX_MCCandidates'][$attr]) || in_array($date,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
															$checked = 'checked="checked"' ; 
														}
														$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$date."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
														if($c==1){
															$attrscontent .="<i class='narrow'>({$attrsNewArray[$date]['cnt']})</i>";
														}
														$attrscontent .="</li>";
													}
												}
													
													
												
											}
									}else if($attr=="employment" && $attrsIds!=''){
										$queEmpType="select sno, mvalue as cname, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_cname' ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$attrstotal += $attrsNewArray[$rowEmpType['sno']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowEmpType['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowEmpType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowEmpType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowEmpType['cname']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowEmpType['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="employment_type" && $attrsIds!=''){
										$queEmpType="select sno, mvalue as ftitle, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_ftitle' ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$attrstotal += $attrsNewArray[$rowEmpType['sno']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowEmpType['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowEmpType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowEmpType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowEmpType['ftitle']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowEmpType['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="employment_city" && $attrsIds!=''){
										$queEmpType="select sno, mvalue as city, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_city' ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$attrstotal += $attrsNewArray[$rowEmpType['sno']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowEmpType['sno'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowEmpType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowEmpType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowEmpType['city']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowEmpType['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="employment_state" && $attrsIds!=''){
										$queEmpType="select sno, mvalue as state, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_state' ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$attrstotal += $attrsNewArray[$rowEmpType['sno']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowEmpType['sno'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowEmpType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowEmpType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowEmpType['state']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowEmpType['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="employment_country" && $attrsIds!=''){
									
										$queEmpType="select sno, country, country AS snoIDs from candidate_work where country IN({$attrsIds}) AND country!='0' GROUP BY country ORDER BY FIELD(country, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$attrstotal += $attrsNewArray[$rowEmpType['country']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowEmpType['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowEmpType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowEmpType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($country_attributes[$rowEmpType['country']]))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowEmpType['country']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="contact_method" && $attrsIds!=''){
											$attrstotal = 0;
											$NRFlag = 0;
											foreach ($attrsNewArray as $idx => $row)
											{
												$attrstotal += $attrsNewArray[$idx]['cnt'];
												$cm = $attrsNewArray[$idx]['@groupby'];
												$cm_count = $attrsNewArray[$idx]['cnt'];
												if($cm=='1001') $cm_print = "Phone";
												if($cm=='2002') $cm_print = "Mobile";
												if($cm=='3003') $cm_print = "Fax";
												if($cm=='4004') $cm_print = "Email";
												
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($cm, $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$cm])){ 
													$checked = 'checked="checked"' ; 
												}
												if($_GET['query'] != '') {
													if(in_array($cm,array_keys($matchingContMthdRecords)))
													{
														$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$cm."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".$cm_print."</a>";
														$NRFlag = 1;
														break;
													}													
													
												}
												else{
							
												$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$cm."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".$cm_print."</a>";
													if($c==1){
														$attrscontent .="<i class='narrow'>(".$cm_count.")</i>";
													}
														$attrscontent .="</li>";
												 }
												}
												
												if($NRFlag == 0 && $_GET['query'] != '')
												{
													print '<li>No data found.</li>';												
												}
								} else if($attr=="cre_type" && $attrsIds!=''){
									
										$quecreType="select id, credential_type, id AS snoIDs from manage_credentials_type where id IN({$attrsIds}) GROUP BY id ORDER BY credential_type ASC,FIELD(id, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$rescreType=mysql_query($quecreType,$db);
										$attrstotal = 0;
										while($rowcreType=mysql_fetch_assoc($rescreType))
										{
												$attrstotal += $attrsNewArray[$rowcreType['id']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowcreType['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowcreType['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowcreType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($creType_attributes[$rowcreType['id']]))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowcreType['id']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="cre_name" && $attrsIds!='')
									{									
										$quecreName="select id, credential_name, id AS snoIDs from manage_credentials_name where id IN({$attrsIds}) GROUP BY id ORDER BY credential_name ASC,FIELD(id, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$rescreName=mysql_query($quecreName,$db);
										$attrstotal = 0;
										while($rowcreName=mysql_fetch_assoc($rescreName))
										{
												$attrstotal += $attrsNewArray[$rowcreName['id']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowcreName['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowcreName['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowcreName['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($creName_attributes[$rowcreName['id']]))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowcreName['id']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="cre_number" && $attrsIds!='')
									{									
										$quecreName="select CRC32(cre_number) as id, cre_number, CRC32(cre_number) AS snoIDs from candidate_credentials where CRC32(cre_number) IN({$attrsIds}) GROUP BY cre_number ORDER BY FIELD(CRC32(cre_number), {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$rescreName=mysql_query($quecreName,$db);
										$attrstotal = 0;
										while($rowcreName=mysql_fetch_assoc($rescreName))
										{
												$attrstotal += $attrsNewArray[$rowcreName['id']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowcreName['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowcreName['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowcreName['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowcreName['cre_number']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowcreName['id']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="availsdate" && $attrsIds!='')
									{									
										$quecreName="select UNIX_TIMESTAMP(availsdate) as id, availsdate, UNIX_TIMESTAMP(availsdate) AS snoIDs from candidate_prof where UNIX_TIMESTAMP(availsdate) IN({$attrsIds}) GROUP BY availsdate ORDER BY FIELD(UNIX_TIMESTAMP(availsdate), {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$rescreName=mysql_query($quecreName,$db);
										$attrstotal = 0;
										while($rowcreName=mysql_fetch_assoc($rescreName))
										{
												$attrstotal += $attrsNewArray[$rowcreName['id']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowcreName['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_Candidates'][$attr][$rowcreName['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowcreName['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".date('m-d-Y',$rowcreName['snoIDs'])."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowcreName['id']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="cre_acquireddate" && $attrsIds!='')
									{									
										$quecreName="select CRC32(acquired_date) as id, DATE_FORMAT(acquired_date,'%m-%d-%Y') AS acquired_date, CRC32(acquired_date) AS snoIDs from candidate_credentials where CRC32(acquired_date) IN({$attrsIds}) GROUP BY acquired_date ORDER BY FIELD(CRC32(acquired_date), {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$rescreName=mysql_query($quecreName,$db);
										$attrstotal = 0;
										while($rowcreName=mysql_fetch_assoc($rescreName))
										{
												$attrstotal += $attrsNewArray[$rowcreName['id']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowcreName['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowcreName['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowcreName['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowcreName['acquired_date']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowcreName['id']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="cre_validfrom" && $attrsIds!='')
									{									
										$quecreName="select CRC32(valid_from) as id, DATE_FORMAT(valid_from,'%m-%d-%Y') AS valid_from, CRC32(valid_from) AS snoIDs from candidate_credentials where CRC32(valid_from) IN({$attrsIds}) GROUP BY valid_from ORDER BY FIELD(CRC32(valid_from), {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$rescreName=mysql_query($quecreName,$db);
										$attrstotal = 0;
										while($rowcreName=mysql_fetch_assoc($rescreName))
										{
												$attrstotal += $attrsNewArray[$rowcreName['id']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowcreName['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowcreName['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowcreName['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowcreName['valid_from']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowcreName['id']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="cre_validto" && $attrsIds!='')
									{									
										$quecreName="select CRC32(valid_to) as id, DATE_FORMAT(valid_to,'%m-%d-%Y') AS valid_to, CRC32(valid_to) AS snoIDs from candidate_credentials where CRC32(valid_to) IN({$attrsIds}) GROUP BY valid_to ORDER BY FIELD(CRC32(valid_to), {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
										
										$rescreName=mysql_query($quecreName,$db);
										$attrstotal = 0;
										while($rowcreName=mysql_fetch_assoc($rescreName))
										{
												$attrstotal += $attrsNewArray[$rowcreName['id']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowcreName['snoIDs'], $xplode)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowcreName['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowcreName['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowcreName['valid_to']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowcreName['id']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else if($attr=="cre_state" && $attrsIds!='')
									{	
										mysql_query("SET SESSION group_concat_max_len = 1000000;",$db);
										$quecreState="select GROUP_CONCAT(state_id),GROUP_CONCAT(explodeStrAndConcat(',',state_id)) from candidate_credentials where explodeStrAndConcat(',',state_id) IN({$attrsIds})  AND state_id!='' LIMIT 0,{$SPHINX_CONF['page_size']}";
										$rescreState=mysql_query($quecreState,$db);
										$states_creds = array();
										$rowcreState=mysql_fetch_row($rescreState);
										
										$stateId_arry = array();
										$stateCrc_arry = array();
										$stateId_arry = explode(',',$rowcreState[0]);
										$stateCrc_arry = explode(',',$rowcreState[1]);
										for($i=0;$i<count($stateId_arry);$i++)
										{
											$states_creds[$displayStateNames[$stateId_arry[$i]]] = array($stateCrc_arry[$i],$stateId_arry[$i]);
										}
										ksort($states_creds);
										$attrstotal = 0;
										foreach($states_creds as $states_creds_key=>$states_creds_val){
											$attrstotal += $attrsNewArray[$states_creds_val[0]]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($states_creds_val[0], $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$states_creds_val[0]])){ 
												$checked = 'checked="checked"' ; 
											}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$states_creds_val[0]."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities($displayStateNames[$states_creds_val[1]])."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$states_creds_val[0]]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
										}
									} else if($attr=="role_types" && $attrsIds!='')
									{									
									$queroleType="select sno, roletitle, sno AS snoIDs from company_commission where sno IN({$attrsIds}) GROUP BY sno ORDER BY roletitle, FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
									
									$resroleType=mysql_query($queroleType,$db);
									$attrstotal = 0;
									while($rowroleType=mysql_fetch_assoc($resroleType))
									{
											$attrstotal += $attrsNewArray[$rowroleType['sno']]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($rowroleType['snoIDs'], $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowroleType['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowroleType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowroleType['roletitle']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowroleType['sno']]['cnt'].")</i>";
										}
											$attrscontent .="</li>";
									}
								} else if($attr=="role_persons" && $attrsIds!='')
								{									
									$querolePer="select CRC32(username) as sno, name, CRC32(username) AS snoIDs from emp_list where CRC32(username) IN({$attrsIds}) GROUP BY username ORDER BY FIELD(CRC32(username), {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
									
									$resrolePer=mysql_query($querolePer,$db);
									$attrstotal = 0;
									while($rowrolePer=mysql_fetch_assoc($resrolePer))
									{
											$attrstotal += $attrsNewArray[$rowrolePer['sno']]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($rowrolePer['snoIDs'], $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowrolePer['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowrolePer['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowrolePer['name']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowrolePer['sno']]['cnt'].")</i>";
										}
											$attrscontent .="</li>";
									}
								} else if($attr=="role_rates" && $attrsIds!='')
								{									
									$queroleRate = "SELECT CRC32(entity_roledetails.rate) as sno, entity_roledetails.rate,CRC32(entity_roledetails.rate) AS snoIDs FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMCandidate' AND CRC32(entity_roledetails.rate) IN({$attrsIds}) GROUP BY CRC32(entity_roledetails.rate) ORDER BY entity_roledetails.rate LIMIT 0,{$SPHINX_CONF['page_size']}";
									
									$resroleRate=mysql_query($queroleRate,$db);
									$attrstotal = 0;
									while($rowroleRate=mysql_fetch_assoc($resroleRate))
									{
											$attrstotal += $attrsNewArray[$rowroleRate['sno']]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($rowroleRate['snoIDs'], $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowroleRate['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowroleRate['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowroleRate['rate']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowroleRate['sno']]['cnt'].")</i>";
										}
											$attrscontent .="</li>";
									}
								} else if($attr=="role_commtype" && $attrsIds!='')
								{									
									$explodeComm = explode(",",$attrsIds);
									if(count($explodeComm)!=0)
									{
										foreach($explodeComm as $coms)
										{
											$attrstotal += $attrsNewArray[$coms]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($coms, $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$coms])){ 
												$checked = 'checked="checked"' ; 
											}
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$coms."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($candCommTypes_attributes[$coms]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$coms]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}
								} else if($attr=="crmgroups" && $attrsIds!='')
								{									
									$explodeComm = explode(",",$attrsIds);
									if(count($explodeComm)!=0)
									{
										foreach($explodeComm as $coms)
										{
											$attrstotal += $attrsNewArray[$coms]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($coms, $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$coms])){ 
												$checked = 'checked="checked"' ; 
											}
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$coms."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($candGroups_attributes[$coms]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$coms]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}
								}else if($attr=="des_jtype" && $attrsIds!='')
								{  //Search Filter for Desired Job type 
									$jType = explode(",",$attrsIds);
									if(count($jType)!=0)
									{
										foreach($jType as $type)
										{
											$attrstotal += $attrsNewArray[$type]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($type, $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(in_array($type,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
													$checked = 'checked="checked"' ; 
											}
											
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$type."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($desired_jtype_arr[$type]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$type]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}
								}else if($attr=="des_jstatus" && $attrsIds!='')
								{ //Search Filter for Desired Job status
						
									$jStatus = explode(",",$attrsIds);
									if(count($jStatus)!=0)
									{
										foreach($jStatus as $status)
										{
											$attrstotal += $attrsNewArray[$status]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($status, $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(in_array($status,$_SESSION['SPHINX_MCCandidates'][$attr])){ 
													$checked = 'checked="checked"' ; 
											}
											
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$status."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($desired_jstatus_arr[$status]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$status]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}
								} else if($attr=="s_dept" && $attrsIds!='')
								{  //Search Filter for Skill Department
								
									$queSkillsDept="select sno, deptname, sno AS snoIDs  FROM department where sno IN({$attrsIds}) ORDER BY deptname ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
									$resSkillsDept=mysql_query($queSkillsDept,$db);
									while($rowSkillsDept=mysql_fetch_assoc($resSkillsDept))
									{
										$attrstotal += $attrsNewArray[$rowSkillsDept['sno']]['cnt'];
										$checked = '';
										if (isset($_GET[$attr])){ 
											$xplode = explode(",",replace_ie($_GET[$attr]));
											if (in_array($rowSkillsDept['sno'], $xplode)) {
												$checked = 'checked="checked"' ; 
											}else{
												$checked = '';
											}
										}
										if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowSkillsDept['snoIDs']])){ 
											$checked = 'checked="checked"' ; 
										}
										
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsDept['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsDept['deptname']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsDept['sno']]['cnt'].")</i>";
										}
										$attrscontent .="</li>";
												
									}
								} else if($attr=="s_cat" && $attrsIds!='')
								{  //Search Filter for Skill Category
									$queSkillsCat="select sno, name , sno AS snoIDs FROM manage WHERE type='jobskillcat' AND sno IN({$attrsIds}) ORDER BY name ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
									$resSkillsCat=mysql_query($queSkillsCat,$db);
									while($rowSkillsCat=mysql_fetch_assoc($resSkillsCat))
									{
										$attrstotal += $attrsNewArray[$rowSkillsCat['sno']]['cnt'];
										$checked = '';
										if (isset($_GET[$attr])){ 
											$xplode = explode(",",replace_ie($_GET[$attr]));
											if (in_array($rowSkillsCat['sno'], $xplode)) {
												$checked = 'checked="checked"' ; 
											}else{
												$checked = '';
											}
										}
										if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowSkillsCat['snoIDs']])){ 
											$checked = 'checked="checked"' ; 
										}
										
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsCat['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsCat['name']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsCat['sno']]['cnt'].")</i>";
										}
										$attrscontent .="</li>";
									}
								} else if($attr=="s_spec" && $attrsIds!='')
								{  //Search Filter for Skill Speciality
									$queSkillsSpec="select sno, name, sno AS snoIDs  FROM manage WHERE type='jobskillspeciality' AND sno IN({$attrsIds}) ORDER BY name ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
									
									$resSkillsSpec=mysql_query($queSkillsSpec,$db);
									while($rowSkillsSpec=mysql_fetch_assoc($resSkillsSpec))
									{
										$attrstotal += $attrsNewArray[$rowSkillsSpec['sno']]['cnt'];
										$checked = '';
										if (isset($_GET[$attr])){ 
											$xplode = explode(",",replace_ie($_GET[$attr]));
											if (in_array($rowSkillsSpec['sno'], $xplode)) {
												$checked = 'checked="checked"' ; 
											}else{
												$checked = '';
											}
										}
										if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$rowSkillsSpec['snoIDs']])){ 
											$checked = 'checked="checked"' ; 
										}
										
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsSpec['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsSpec['name']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsSpec['sno']]['cnt'].")</i>";
										}
										$attrscontent .="</li>";
									}
								}else
								{
									$explodeComm = explode(",",$attrsIds);
									if(count($explodeComm)!=0)
									{
										foreach($explodeComm as $coms)
										{
											$attrstotal += $attrsNewArray[$coms]['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array($coms, $xplode)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$coms])){ 
												$checked = 'checked="checked"' ; 
											}
												$udf_optionattributes =  udf_checkboxoptions($attr);
												
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$coms."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($udf_optionattributes[$coms]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$coms]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}	
								}
									
							}else{
								if($attr=="crc_candtype")
								{
									while($row = mysql_fetch_array($result)) {
											$value = trim($row[1]);
											if(!empty($SPHINX_CONF['sphinx_attributes_suvals'][$attr]))
											{
												$print_value = $SPHINX_CONF['sphinx_attributes_suvals'][$attr][$value];
												if(trim($print_value)!="" && ($print_value!='0')) $attrstotal += $row['cnt'];
											}else
											{
												$print_value = ucfirst($value);
												if($row[$attr]!='')
													$attrstotal += $row['cnt'];
											}
											$count = $row['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr])); 
													 if (in_array($value, $xplode, true)) {
															$checked = 'checked="checked"' ; 
														}else{
															$checked = '';
														}
													}
													
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$value])){ 
													$checked = 'checked="checked"' ; 
												}
												$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$value."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($print_value))."</a>";
												if(($viewattr=="zip" || $viewattr=="areacode") && ($_GET[$viewattr.'miles']!='' &&  $_GET['radius'.$viewattr]!='')){
												$miles = number_format(($row["distance"]*0.000621371),2);
												$attrscontent .="<i class='narrow'>({$count})-{$miles}</i>";
													if($viewattr=="areacode"){
														$_SESSION['ACR_SPHINX_MCCandidates'][$_GET[$viewattr.'miles']][str_replace("_","",$print_value)]=$miles;
													}
												}else{
														if($c==1){
															$attrscontent .="<i class='narrow'>({$count})</i>";
														}
													}
												$attrscontent .="</li>";
										}
								}
								else
								{
									while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
									
										$value = trim($row[$attr]); //we dont use @groupby, because it wrong for string attributes.
										if(!empty($SPHINX_CONF['sphinx_attributes_suvals'][$attr]))
										{
											$print_value = $SPHINX_CONF['sphinx_attributes_suvals'][$attr][$value];
											if(trim($print_value)!="" && ($print_value!='0')) $attrstotal += $row['cnt'];
										}else
										{
											$print_value = ucfirst($value);
											if($row[$attr]!='')
												$attrstotal += $row['cnt'];
										}
										$count = $row['cnt'];
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr])); 
												 if (in_array($value, $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$value])){ 
													$checked = 'checked="checked"' ; 
												}
											if($attr=="ctime" || $attr=="mtime"){ $print_value = date('m/d/Y',$print_value); }
											if($attr=="owner"){ $print_value = $row['owname']; }
											if($attr=="cuser"){ $print_value = $row['createdby']; }
											if($attr=="muser"){ $print_value = $row['modifiedby']; }
											if($attr=="ctime"){ $print_value = date("m/d/Y",strtotime($row['cdate'])); }
											if($attr=="mtime"){ $print_value = date("m/d/Y",strtotime($row['mdate'])); }
											if (strpos($attr, 'cust_') !== false) { 
											if($print_value=='0')
											{
												$print_value = 'null';
											}else
											{
												$print_value = date("m/d/Y",replace_string($print_value)); 
											}
										}
											
											if($attr=="availsdate"){ 
												if($row['availsdate']==0)
												{ 
													$print_value = $row['availsdate'];
												}else
												{
													$print_value = date("m-d-Y",$value); 
												}
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$value."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
										if(($viewattr=="zip" || $viewattr=="areacode") && ($_GET[$viewattr.'miles']!='' &&  $_GET['radius'.$viewattr]!='')){
											$miles = number_format(($row["distance"]*0.000621371),2);
											$attrscontent .="<i class='narrow'>({$count})-{$miles}</i>";

											if($viewattr=="areacode"){
												$_SESSION['ACR_SPHINX_MCCandidates'][$_GET[$viewattr.'miles']][str_replace("_","",$print_value)]=$miles;
											}

											}else{
													if($c==1){
														$attrscontent .="<i class='narrow'>({$count})</i>";
													}
												}
											$attrscontent .="</li>";
									}
								}
							}
						}else
						{
							if($attr=="areacode")
							{
								$areaCodeMasterList = array(); 
								$areaCodeMilesList = array(); 
								while($row1 = mysql_fetch_array($result,MYSQL_ASSOC)) {
									if(isset($row1['hdistance_miles']) && ($row1['hdistance_miles']<=$areamiles)){
										$areaCodeMilesList[$row1['hareacode']][] = number_format($row1['hdistance_miles'],2);
										
									}
									$areaCodeMasterList[$row1['hareacode']][] = 'P-'.$row1['cnt'];
									if(isset($row1['wdistance_miles']) && ($row1['wdistance_miles']<=$areamiles)){
										$areaCodeMilesList[$row1['wareacode']][] = number_format($row1['wdistance_miles'],2);
										
									}
									$areaCodeMasterList[$row1['wareacode']][] = 'S-'.$row1['cnt'];
									if(isset($row1['mdistance_miles']) && ($row1['mdistance_miles']<=$areamiles)){
										$areaCodeMilesList[$row1['mareacode']][] = number_format($row1['mdistance_miles'],2);
										
									}
									$areaCodeMasterList[$row1['mareacode']][] = 'M-'.$row1['cnt'];
									

									
								}
								
								
								ksort($areaCodeMasterList);
								//echo count($areaCodeMasterList);
								if(count($areaCodeMilesList)!=0)
								{
									asort($areaCodeMilesList);
									if($q3!='') {$q3 = " ".$q3;}
									foreach ($areaCodeMilesList as $idA => $rowA)
									{
										$idn =1;
										if($areacode !='' && $idA =='_null_'){
											$idn = 0;
										}
										if($idn ==1){
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												
												if(in_array(trim($idA),$xplode,true)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$idA])){ 
												$checked = 'checked="checked"' ; 
											}
											$subSqlCount = array();
											if((isset($_GET['hareacode']) && $_GET['hareacode'] !='') || in_array('hareacode',$areaCode_all))
											{
												$subSqlCount[] ='hareacode';
											}
											if((isset($_GET['wareacode']) && $_GET['wareacode'] !='') || in_array('wareacode',$areaCode_all))
											{
												$subSqlCount[] ='wareacode';
											}
											if((isset($_GET['mareacode']) && $_GET['mareacode'] !='') || in_array('mareacode',$areaCode_all))
											{
												$subSqlCount[] ='mareacode';
											}
											
											$sqlCountSelect = "SELECT COUNT(DISTINCT snoid) as cnt FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2} @(".implode(',',$subSqlCount).") {$idA}') {$q3} AND {$whereSLQuery}";
											$sqlCount = mysql_fetch_array(mysql_query($sqlCountSelect,$sphinxql));
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$idA."' ".$checked." onclick='javascript:deSelectChk();'/><a class='ckboxa' href='javascript:void(0);'>".replace_string($idA)."</a>";
												if($c==1){
													$attrscontent .="<i class='narrow'><small>(".$sqlCount['cnt'].")-".$areaCodeMilesList[$idA][0]." miles</small></i>";
													if($viewattr=="areacode"){
														$_SESSION['ACR_SPHINX_MCCandidates'][$_GET[$viewattr.'miles']][str_replace("_","",$idA)]=$areaCodeMilesList[$idA][0];
													}
												}
													$attrscontent .="</li>";
										}
											
									}
								}else
								{
									if($q3!='') {$q3 = "AND ".$q3;}
									foreach ($areaCodeMasterList as $idA => $rowA)
									{
											$checked = '';
											if (isset($_GET[$attr])){ 
												$xplode = explode(",",replace_ie($_GET[$attr]));
												if (in_array(trim($idA), $xplode, true)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr][$idA])){ 
												$checked = 'checked="checked"' ; 
											}
											$sqlCountSelect = "SELECT COUNT(DISTINCT snoid) as cnt FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2} @(hareacode,wareacode,mareacode) {$idA}') {$q3} AND {$whereSLQuery}";
											$sqlCount = mysql_fetch_array(mysql_query($sqlCountSelect,$sphinxql));
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$idA."' ".$checked." onclick='javascript:deSelectChk();'/><a class='ckboxa' href='javascript:void(0);'>".replace_string($idA)."</a>";
											if($c==1){
													$attrscontent .="<i class='narrow'><small>(".$sqlCount['cnt'].")</small></i>";
												}
											
													$attrscontent .="</li>";
											
									}
								}
							}else{
								while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
								
									$value = trim($row[$attr]); //we dont use @groupby, because it wrong for string attributes.
									if(!empty($SPHINX_CONF['sphinx_attributes_suvals'][$attr]) && ($value!='0'))
									{
										$print_value = $SPHINX_CONF['sphinx_attributes_suvals'][$attr][$value];
										if(trim($print_value)!="" && ($print_value!='0')) $attrstotal += $row['cnt'];
									}else
									{
										$print_value = ucfirst($value);
										if($row[$attr]!='')
											$attrstotal += $row['cnt'];
									}
									$count = $row['cnt'];

										$checked = '';
										if (isset($_GET[$attr])){ 
											$xplode = explode(",",replace_ie($_GET[$attr])); 
											 if (in_array($value, $xplode, true)) {
													$checked = 'checked="checked"' ; 
												}else{
													$checked = '';
												}
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr]['I|'.$value])){ 
												$checked = 'checked="checked"' ; 
											}
											if(isset($_SESSION['SPHINX_MCCandidates'][$attr]['E|'.$value])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$value."' ".$checked." onclick='javascript:deSelectChk();'/><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
										
										if(($viewattr=="zip" || $viewattr=="areacode") && ($_GET[$viewattr.'miles']!='' &&  $_GET['radius'.$viewattr]!='')){
											$miles = number_format(($row["distance"]*0.000621371),2);
											$attrscontent .="<i class='narrow'>({$count})-{$miles} miles</i>";
											if($viewattr=="zip"){
												$_SESSION['ZCR_SPHINX_MCCandidates'][$_GET[$viewattr.'miles']][html_tls_entities(ucfirst(replace_string($print_value)))]=$miles;
											}
											if($viewattr=="areacode"){
												$_SESSION['ACR_SPHINX_MCCandidates'][$_GET[$viewattr.'miles']][str_replace("_","",$print_value)]=$miles;
											}
										}else{
												if($c==1){
													$attrscontent .="<i class='narrow'>({$count})</i>";
												}
											}
										$attrscontent .="</li>";
								}
							}
						}
							$getMeta = mysql_query("SHOW META LIKE 'total%'",$sphinxql);
							while($rowMeta = mysql_fetch_array($getMeta,MYSQL_ASSOC)) {
								if($rowMeta['Variable_name']=="total_found")
								{
									$total_found = $rowMeta['Value'];
								}
							}
							print '<div id="attr-list"><div class="scroll-area"><div class="scroll-pane"><ul>'.$attrscontent;
							print "</ul></div></div></div>";
							$counter++;
						}else
						{
							print '<li class="nodata" id="nodata">No matching records found.</li>';
							if(in_array( $viewattr, array('profiletitle','jobcatid','skills','edudegree_level','employment_type','amount','availsdate','cre_acquireddate','cre_validfrom','cre_validto')))
							{
								print '<style type="text/css">
										#selrow{display:none !important;}
										#maxSel20Alert{display:none !important;}
									  </style>';
							}
						}
				}
?>