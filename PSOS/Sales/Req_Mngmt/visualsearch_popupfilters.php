<?php
/*
Project: Sphinx Search
Purpose:  Visual Search Popup filters.
Created By: Nagaraju M.
Created Date: 31 Aug 2015
Modified Date: 31 Aug 2015
*/
	/*Get attribute types */
	$vsAFetch = getSphinxFilterColumnTypes(4,$_GET['viewattr']);
	
	//$q = isset($_GET['q'])?stripslashes($_GET['q']):'';
	$q = isset($_REQUEST['q'])?stripslashes(urldecode($_REQUEST['q'])):'';
	$q = trim(strtolower($q));
	$q = str_replace(',',' and ',$q);
	$q = str_replace('/',' ',$q);
	$q = preg_replace('/ or /',' | ',$q);
	$q = preg_replace('/ and /',' ',$q);
	$q = preg_replace('/ not /',' -',$q);
	//$q = preg_replace('/[^\w~\|\(\)\^\$\?"\/=-]+/',' ',trim(strtolower($q)));
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
			$q = '@(search_data) '.$q;
		}else
		{
			$q = '@(search_data,notes) '.$q;
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
	$attr = $_GET['viewattr'];
	if($_GET['query']!=''){
		
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
		
		
			if($_GET['viewattr']=="jobtype_id"){
				$r.= " @jobtype ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="posstatus"){
				$r.= " @status_name ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="jostage"){
				$r.= " @jostage_name ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="sourcetype"){
				$r.= " @sourcetype_name ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="catid"){
				$r.= " @category ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="hrm_deptid"){
				$r.= " @hrm_deptname ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="country_id"){
				$r.= " @country_name ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="owner"){
				$r.= " @owname ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="cuser"){
				$r.= " @createdby ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="muser"){
				$r.= " @modifiedby ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="start_date" || $_GET['viewattr']=="due_date" || $_GET['viewattr']=="start_date"){
				//$r.= " @modifiedby ".strtolower($qu1)."*";
			}else if ((strpos($_GET['viewattr'], 'cust_') !== false) && ($vsAFetch['index_col_type']=='numeric') && ($vsAFetch['multivalues']=='N'))
			{				
			}else if($_GET['viewattr']=="sub_status"){//Search filter Submission Status
				
			}else
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
			$SPHINX_CONF['link_format'] = '/visualsearch_popup.php?'.$_SERVER['QUERY_STRING'].'&page_id=$id';
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
		 //$q.= " @accessto ALL | @accessto {$username} ";
		
		$counter = 1;
			

		
				$q2 = '';
				$q3 = '';
				
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
					$enddateStr = explode("|",$_GET['eDateSearch']);
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
				
					if (isset($_GET[$attr2]) && ($_GET[$attr2]!='')) {
                        if ($type2 == 'string') { 							
							
							$dbSearchParmVal=str_replace("\\","\\\\",addslashes($_GET[$attr2]));
							$dbSearchParmVal=str_replace("\'","'",$dbSearchParmVal);
							if($attr == $attr2)
							{
								$dbSearchParmVal = replace_ie($dbSearchParmVal);
								$dbSearchParmVal = str_replace(',','$" | "^',$dbSearchParmVal);
								$q2 .= ' @'.$attr2.' ("^'.$dbSearchParmVal.'$")';
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
								$q2 .= ' @'.$attr2.' '.$new_dbSearchParmVal;
							}	
							
                        } else {
							$dbSearchParmVal = replace_ie($_GET[$attr2]);
							if($attr2=="cuser" && $cDateSearch!='')
							{
								$cdateStr = explode("|",$cDateSearch);
								$cstrFromDate = strtotime($cdateStr[1]);
								$cstrToDate = strtotime($cdateStr[2]);
								$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND ctime BETWEEN {$cstrFromDate} AND {$cstrToDate} ";
							}else if($attr2=="muser" && $mDateSearch!='')
							{
								$mdateStr = explode("|",$mDateSearch);
								$mstrFromDate = strtotime($mdateStr[1]);
								$mstrToDate = strtotime($mdateStr[2]);
								$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND mtime BETWEEN {$mstrFromDate} AND {$mstrToDate} ";
							}else if($attr2=="start_date" && $sDateSearch!='')
							{
								$stdateStr = explode("|",$sDateSearch);
								$stFromDate = strtotime($stdateStr[1]);
								$stToDate = strtotime($stdateStr[2]);
								$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND start_date BETWEEN {$stFromDate} AND {$stToDate} ";
							}else if($attr2=="due_date" && $dDateSearch!='')
							{
								$duedateStr = explode("|",$dDateSearch);
								$dueFromDate = strtotime($duedateStr[1]);
								$dueToDate = strtotime($duedateStr[2]);
								$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND due_date BETWEEN {$dueFromDate} AND {$dueToDate} ";
							}else if($attr2=="end_date" && $eDateSearch!='')
							{
								$enddateStr = explode("|",$eDateSearch);
								$endFromDate = strtotime($enddateStr[1]);
								$endToDate = strtotime($enddateStr[2]);
								$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND end_date BETWEEN {$endFromDate} AND {$endToDate} ";
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


				if($_GET['viewattr']=="jobtype_id"){
					$orderby = "jobtype ASC";
				}else if($_GET['viewattr']=="posstatus"){
					$orderby = "status_name ASC";
				}else if($_GET['viewattr']=="jostage"){
					$orderby.= "jostage_name ASC";
				}else if($_GET['viewattr']=="sourcetype"){
					$orderby = "sourcetype_name ASC";
				}else if($_GET['viewattr']=="catid"){
					$orderby = "category ASC";
				}else if($_GET['viewattr']=="hrm_deptid"){
					$orderby = "hrm_deptname ASC";
				}else if($_GET['viewattr']=="country_id"){
					$orderby = "country_name ASC";
				}else if($_GET['viewattr']=="owner"){
					$orderby = "owname ASC";
				}else if($_GET['viewattr']=="cuser"){
					$orderby = "createdby ASC";
				}else if($_GET['viewattr']=="muser"){
					$orderby = "modifiedby ASC";
				}else
				{
					$orderby = "{$attr} ASC";
				}
			 $optionsList = " OPTION max_matches=500000";
			 
			 
			 if(isset($_SESSION['SPHINX_Joborders_sub']['savezipCODE']) && $_SESSION['SPHINX_Joborders_sub']['savezipCODE'] !=''){
				$zipCode_all = explode('|',$_SESSION['SPHINX_Joborders_sub']['savezipCODE']);
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
					$res = mysql_query($que, $maindb);
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
			if($attr=="sub_status")
			{//Search filter Submission Status
				
				$fromDate='';$toDate='';
				$submissionDate='';
				if(isset($_GET['sitffromdate']) && isset($_GET['sitftodate']))
				{
					$fromDate = strtotime($_GET['sitffromdate'].' 00:00:00');
					$toDate = strtotime($_GET['sitftodate'].' 23:59:59');
				
					$submissionDate = " AND sub_date BETWEEN ".$fromDate." AND ".$toDate." ";
				}
				else if(isset($_SESSION['SPHINX_Joborders_sub']['savesubStatus']) && !empty($_SESSION['SPHINX_Joborders_sub']['savesubStatus'])){
					$subStatusDates = explode('|',$_SESSION['SPHINX_Joborders_sub']['savesubStatus']);
					$fromDate = strtotime($subStatusDates[0].' 00:00:00');
					$toDate = strtotime($subStatusDates[1].' 23:59:59');
				
					$submissionDate = " AND sub_date BETWEEN ".$fromDate." AND ".$toDate." ";
				}
				
			}
                        if($_GET['query']!=''){
				$c = 1;
				if(in_array($attr,$subArray)){
					$c = 0;
					if($attr=="skills")
					{
						$query = "SELECT @groupby,mvalue as skills, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skillname {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="s_level")
					{
						$query = "SELECT @groupby,mvalue as s_level, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skilllevel {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
					if($attr=="s_lastused")
					{
						$query = "SELECT @groupby,mvalue as s_lastused, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype lastused {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else
					if($attr=="sub_status")
					{
						$subSt='';
						$status_date='';
						if($_GET['query']!='')
						{
							$subSt.= " AND name LIKE '{$_GET['query']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE type='interviewstatus'  {$subSt} order by sno ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$subStat = $resultS['queryIds'];
						}else
						{
							$subStat = "0";
						}
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$subStat}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}else					
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
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$roletypeS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolepersonS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else	 
					if($attr=="role_rates")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " AND amount LIKE '{$_GET['query']}%'";
						}

						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(CRC32(amount))) AS queryIds FROM assign_commission WHERE assigntype='JO' {$sub} order by amount ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$roleRates = $resultS['queryIds'];
						}else
						{
							$roleRates = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$roleRates}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolecommS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else if($attr=="sub_status"){//Search filter Submission Status
						
						if($_GET['query'] !=''){
						$submissionQue='';
							$sub_status = array_keys($jobSubmissionStatus,$_GET['query']);
							
							 $query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$sub_status[0]}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						}
						
								
					}else
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
						
						
						 $query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$skill_dept}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						
						 $query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$skill_cat}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						
						 $query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$skill_spec}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolecommS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					}
				}else
				{
					
					$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE MATCH('{$r}') AND crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					
					
				}
			
			}else{
			
				if($q!='' || $q2!=''){
				
					 $query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2}') $q3 AND crc_accessto IN (2914988887,{$username}) {$zipstr_where}   {$submissionDate} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";					 
					
				}else if($q3!='')
				{
				
					$q3 = substr($q3,4);
					$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$q3} AND crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$submissionDate}  GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					
					
				}else
				{
					$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt {$zipstr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$submissionDate} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					
					
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
				
				if($attr=="ctime"){ $query = str_replace(array("@groupby,","GROUP BY ctime ORDER BY ctime"),array("@groupby,YEARMONTHDAY(ctime) as cdate,","GROUP BY cdate ORDER BY cdate"),$query);  }
				if($attr=="mtime"){ $query = str_replace(array("@groupby,","GROUP BY mtime ORDER BY mtime"),array("@groupby,YEARMONTHDAY(mtime) as mdate,","GROUP BY mdate ORDER BY mdate"),$query);  }
				
				if($attr=="start_date"){ 
					if($_GET['query']!=''){
						 $query = str_replace("WHERE","WHERE start_date IN (".strtotime($_GET['query']).") AND ",$query);
					}
					$query = str_replace(array("@groupby,start_date","GROUP BY start_date ORDER BY start_date"),array("@groupby,YEARMONTHDAY(start_date) as startdate,start_date","GROUP BY startdate ORDER BY startdate"),$query);  
				
					if($sifromdate!='' && $sitodate!='')
					{
						$strFromDate = strtotime($sifromdate);
						$strToDate = strtotime($sitodate);
						$query = str_replace("WHERE","WHERE start_date BETWEEN {$strFromDate} AND {$strToDate} AND ",$query);
					}
				
				}
				if($attr=="due_date"){ 
				
					if($_GET['query']!=''){
						 $query = str_replace("WHERE","WHERE due_date IN (".strtotime($_GET['query']).") AND ",$query);
					}
					
					$query = str_replace(array("@groupby,due_date","GROUP BY due_date ORDER BY due_date"),array("@groupby,YEARMONTHDAY(due_date) as duedate,due_date","GROUP BY duedate ORDER BY duedate"),$query);
					
					if($sifromdate!='' && $sitodate!='')
					{
						$strFromDate = strtotime($sifromdate);
						$strToDate = strtotime($sitodate);
						$query = str_replace("WHERE","WHERE due_date BETWEEN {$strFromDate} AND {$strToDate} AND ",$query);
					}
				
				}
				if($attr=="end_date"){ 
				
					if($_GET['query']!=''){
						 $query = str_replace("WHERE","WHERE end_date IN (".strtotime($_GET['query']).") AND ",$query);
					}
					
					$query = str_replace(array("@groupby,end_date","GROUP BY end_date ORDER BY end_date"),array("@groupby,YEARMONTHDAY(end_date) as enddate,end_date","GROUP BY enddate ORDER BY enddate"),$query);  
					
					if($sifromdate!='' && $sitodate!='')
					{
						$strFromDate = strtotime($sifromdate);
						$strToDate = strtotime($sitodate);
						$query = str_replace("WHERE","WHERE end_date BETWEEN {$strFromDate} AND {$strToDate} AND ",$query);
					}
				}
				
				// adding Department Conditions
				$query = str_replace("WHERE","WHERE hrm_deptid IN (".$deptAccesSno.") AND ",$query);
				
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
										$queSkilsType="select sno, mvalue as skillname, sno AS snoIDs from req_master where sno IN({$attrsIds}) AND mtype='skillname' ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowSkillsType['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsType['skillname']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsType['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else
									
									if($attr=="s_level" && $attrsIds!=''){
										$queSkilsType="select sno, mvalue as skilllevel, sno AS snoIDs from req_master where sno IN({$attrsIds}) AND mtype='skilllevel' {$q_sql} ORDER BY mvalue ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowSkillsLevel['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
											}	
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsLevel['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsLevel['skilllevel']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsLevel['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else
									
									if($attr=="s_lastused" && $attrsIds!=''){
										$queSkilsType="select sno, mvalue as lastused, sno AS snoIDs from req_master where sno IN({$attrsIds}) AND mtype='lastused' {$q_sql} ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowSkillsLastused['snoIDs']])){ 
													$checked = 'checked="checked"' ; 
											}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowSkillsLastused['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowSkillsLastused['lastused']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowSkillsLastused['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
									} else
									if($attr=="role_types" && $attrsIds!='')
									{									
									$queroleType="select sno, roletitle, sno AS snoIDs from company_commission where sno IN({$attrsIds}) GROUP BY sno ORDER BY roletitle ASC,FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
									
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowroleType['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowroleType['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowroleType['roletitle']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowroleType['sno']]['cnt'].")</i>";
										}
											$attrscontent .="</li>";
									}
								} else
								
								if($attr=="role_persons" && $attrsIds!='')
								{									
									$querolePer="select CRC32(username) as sno, name, CRC32(username) AS snoIDs from emp_list where CRC32(username) IN({$attrsIds}) GROUP BY username ORDER BY name ASC,FIELD(CRC32(username), {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']}";
									
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowrolePer['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowrolePer['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowrolePer['name']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowrolePer['sno']]['cnt'].")</i>";
										}
											$attrscontent .="</li>";
									}
								} else
								if($attr=="role_rates" && $attrsIds!='')
								{									
									$queroleRate = "SELECT CRC32(amount) as sno, amount as rate,CRC32(amount) AS snoIDs FROM assign_commission WHERE  assigntype='JO' AND CRC32(amount) IN({$attrsIds}) GROUP BY amount ORDER BY amount LIMIT 0,{$SPHINX_CONF['page_size']}";
									
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowroleRate['snoIDs']])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowroleRate['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowroleRate['rate']))."</a>";
										if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowroleRate['sno']]['cnt'].")</i>";
										}
											$attrscontent .="</li>";
									}
								} else
								if($attr=="role_commtype" && $attrsIds!='')
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$coms])){ 
												$checked = 'checked="checked"' ; 
											}
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$coms."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($candCommTypes_attributes[$coms]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$coms]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}
								}else if($attr=="sub_status" && $attrsIds!=''){//Search Filter for Submission Status
										
									
										
											
										$submission_status = explode(',',$attrsIds);
											
										foreach($submission_status as $status){
										
											if($status!=''){
													
												$print_value = $jobSubmissionStatus[$status]; 
												$checked = '';
												if(in_array($print_value,$_SESSION['SPHINX_Joborders'][$attr])){ 
													$checked = 'checked="checked"' ; 
												}elseif(in_array($status,$_SESSION['SPHINX_Joborders'][$attr])){
													$checked = 'checked="checked"' ;
												}
												$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$status."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
												if($c==1){
													$attrscontent .="<i class='narrow'>({$attrsNewArray[$status]['cnt']})</i>";
												}
												$attrscontent .="</li>";
												
											}
												
										}
									}else if($attr=="s_dept" && $attrsIds!='')
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
										if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowSkillsDept['snoIDs']])){ 
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
										if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowSkillsCat['snoIDs']])){ 
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
										if(isset($_SESSION['SPHINX_Joborders'][$attr][$rowSkillsSpec['snoIDs']])){ 
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
											if(isset($_SESSION['SPHINX_Joborders'][$attr][$coms])){ 
												$checked = 'checked="checked"' ; 
											}
												$udf_optionattributes = udf_checkboxoptions($attr);
												
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$coms."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($udf_optionattributes[$coms]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$coms]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}	
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
										
										if($attr=="ctime" || $attr=="mtime"){ $print_value = date('m/d/Y',$print_value); }
										if($attr=="owner"){ $print_value = $row['owname']; }
										if($attr=="cuser"){ $print_value = $row['createdby']; }
										if($attr=="muser"){ $print_value = $row['modifiedby']; }
										if($attr=="ctime"){ $print_value = date("m/d/Y",strtotime($row['cdate'])); }
										if($attr=="mtime"){ $print_value = date("m/d/Y",strtotime($row['mdate'])); }
										if($attr=="start_date"){ if($row['start_date'] != 0) $print_value = date("m/d/Y",strtotime($row['startdate'])); }
										if($attr=="due_date"){ if($row['due_date'] != 0) $print_value = date("m/d/Y",strtotime($row['duedate'])); }
										if($attr=="end_date"){ if($row['end_date'] != 0) $print_value = date("m/d/Y",strtotime($row['enddate'])); }
										if (strpos($attr, 'cust_') !== false) { 
											if($print_value=='0')
											{
												$print_value = 'null';
											}else
											{
												$print_value = date("m/d/Y",replace_string($print_value)); 
											}
										}
										
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$value."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
										if(($viewattr=="zip" ) && ($_GET[$viewattr.'miles']!='' &&  $_GET['radius'.$viewattr]!='')){
										$miles = number_format(($row["distance"]*0.000621371),2);
										$attrscontent .="<i class='narrow'>({$count})-{$miles}</i>";
										}else{
												if($c==1){
													$attrscontent .="<i class='narrow'>({$count})</i>";
												}
											}
										$attrscontent .="</li>";
								}
							}
						}else
						{
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
									
									$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$value."' ".$checked."  onclick='javascript:deSelectChk();'/><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
									
									if(($viewattr=="zip" ) && ($_GET[$viewattr.'miles']!='' &&  $_GET['radius'.$viewattr]!='')){
										$miles = number_format(($row["distance"]*0.000621371),2);
										$attrscontent .="<i class='narrow'>({$count})-{$miles} miles</i>";
									}else{
											if($c==1){
												$attrscontent .="<i class='narrow'>({$count})</i>";
											}
										}
									$attrscontent .="</li>";
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
							if(in_array($viewattr, array('skills','catid')))
							{ 
								print '<style type="text/css">
										#selrow{display:none !important;}
										#maxSel20Alert{display:none !important;}
									  </style>';
							}
						}
				}
?>