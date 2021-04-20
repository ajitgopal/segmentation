<?php
/*
Project: Sphinx Search
Purpose:  Visual Search Popup filters.
Created By: Nagaraju M.
Created Date: 31 Aug 2015
Modified Date: 31 Aug 2015
*/
	/*Get attribute types */
	$vsAFetch = getSphinxFilterColumnTypes(1,$_GET['viewattr']);
	
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
			$q = '@(profile_data) '.$q;
		}else
		{
			$q = '@(profile_data,notes) '.$q;
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
	$q2 = '';
	$q3 = '';
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
		
			if($_GET['viewattr']=="csno"){
				$r.= " @cname ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="ctype"){
				$r.= " @contact_type ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="cat_id"){
				$r.= " @category ^".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="sourcetype"){
				$r.= " @source_name ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="dep_id"){
				$r.= " @dep_name ".strtolower($qu1)."*";
			}else if($_GET['viewattr']=="hrm_deptid"){
				$r.= " @hrm_deptname ".strtolower($qu1)."*";
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
			}else if($_GET['viewattr']=="ascandidate"){
				$r.= " @ascand_name ".strtolower($qu1)."*";
			}else if ((strpos($_GET['viewattr'], 'cust_') !== false) && ($vsAFetch['index_col_type']=='numeric') && ($vsAFetch['multivalues']=='N'))
			{				
				
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
							if($attr2=="cuser" && $cDateSearch!='')
							{
								$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND ctime BETWEEN {$cstrFromDate} AND {$cstrToDate} ";
							}else if($attr2=="muser" && $mDateSearch!='')
							{
								$q3 .= " AND {$attr2} IN (".$dbSearchParmVal.") AND mtime BETWEEN {$mstrFromDate} AND {$mstrToDate} ";
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


				if($_GET['viewattr']=="csno"){
					$orderby = "cname ASC";
				}else if($_GET['viewattr']=="ctype"){
					$orderby = "contact_type ASC";
				}else if($_GET['viewattr']=="cat_id"){
					$orderby.= "category ASC";
				}else if($_GET['viewattr']=="sourcetype"){
					$orderby = "source_name ASC";
				}else if($_GET['viewattr']=="dep_id"){
					$orderby = "dep_name ASC";
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
				}else if($_GET['viewattr']=="ascandidate"){
					$orderby = "ascand_name ASC";
				}else
				{
					$orderby = "{$attr} ASC";
				}
			$optionsList = " OPTION max_matches=500000";
			if(isset($_SESSION['SPHINX_Contacts_sub']['savezipCODE']) && $_SESSION['SPHINX_Contacts_sub']['savezipCODE'] !=''){
				$zipCode_all = explode('|',$_SESSION['SPHINX_Contacts_sub']['savezipCODE']);
			}
			if(isset($_SESSION['SPHINX_Contacts_sub']['saveareacodePSM']) && $_SESSION['SPHINX_Contacts_sub']['saveareacodePSM'] !=''){
				$areaCode_all = explode('|',$_SESSION['SPHINX_Contacts_sub']['saveareacodePSM']);
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
					
					if($latitudeArea!='' && $longitudeArea!='')
					{
						if(is_null($areamiles)){$areamiles=0;}
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
			
                        if($_GET['query']!=''){
				$c = 1;
				if(in_array($attr,$subArray)){
					$c = 0;
					if($attr=="cat_id")
					{
												
						$query = "SELECT @groupby,category as catname, COUNT(DISTINCT snoid) AS cnt, id FROM {$SPHINX_CONF['sphinx_index']}  WHERE match('{$r}') GROUP BY {$attr} ORDER BY category ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$roletypeS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolepersonS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else					
					if($attr=="role_rates")
					{
						$sub='';
						if($_GET['query']!='')
						{
							$sub.= " AND entity_roledetails.rate LIKE '{$_GET['query']}%'";
						}

						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(CRC32(entity_roledetails.rate))) AS queryIds FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMContact' {$sub} order by entity_roledetails.rate ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$roleRates = $resultS['queryIds'];
						}else
						{
							$roleRates = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$roleRates}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolecommS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
						
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolecommS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					} else
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
					 if($attr=="areacode")
					 {
						$query = "SELECT @groupby,hareacode ,wareacode,mareacode, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE MATCH('{$r}') AND crc_accessto IN (2914988887,{$username}) GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
						
					 }else
					 {
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE MATCH('{$r}') AND crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					 }
					
				}
			
			}else{
			
				if($q!='' || $q2!=''){
				
					 if($attr=="areacode")
					 {
						$query = "SELECT @groupby,hareacode,wareacode,mareacode, COUNT(DISTINCT snoid) AS cnt {$zipstr} {$areacodestr} FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2}') $q3 AND crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$areastrWhere} GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
						
					 }else
					 {
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2}') $q3 AND crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$areastr_where} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					 }
					
				}else if($q3!='')
				{
				
					$q3 = substr($q3,4);
					if($attr=="areacode")
					{
						$query = "SELECT @groupby,hareacode,wareacode,mareacode, COUNT(DISTINCT snoid) AS cnt {$zipstr} {$areacodestr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$q3} AND crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$areastrWhere} GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
						
					}else
					 {
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE {$q3} AND crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$areastr_where} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
					 }
					
				}else
				{
					if($attr=="areacode")
					{
						$query = "SELECT @groupby,hareacode,wareacode,mareacode, COUNT(DISTINCT snoid) AS cnt {$zipstr} {$areacodestr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$areastrWhere} GROUP BY hareacode,wareacode,mareacode ORDER BY hareacode ASC LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
						
						
					}else
					{
						
						$query = "SELECT @groupby,{$attr}, COUNT(DISTINCT snoid) AS cnt {$zipstr} {$areastr} FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) {$zipstr_where} {$areastr_where} GROUP BY {$attr} ORDER BY {$orderby} LIMIT {$currentOffset},{$SPHINX_CONF['page_size']} {$optionsList}";
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
				if($attr=="ascandidate"){ $query = str_replace("@groupby,","@groupby,ascand_name,",$query);}
				if($attr=="ctime"){ $query = str_replace(array("@groupby,","GROUP BY ctime ORDER BY ctime"),array("@groupby,YEARMONTHDAY(ctime) as cdate,","GROUP BY cdate ORDER BY cdate"),$query);  }
				if($attr=="mtime"){ $query = str_replace(array("@groupby,","GROUP BY mtime ORDER BY mtime"),array("@groupby,YEARMONTHDAY(mtime) as mdate,","GROUP BY mdate ORDER BY mdate"),$query);  }
				
				if($attr=="csno"){ $query = str_replace(array("@groupby,"),array("@groupby,cname,"),$query);  }
				
				// adding Department Conditions
				$query = str_replace("WHERE","WHERE hrm_deptid IN (".$deptAccesSno.") AND ",$query);

				//echo $query;
				//echo $query2;
				//echo $query3;
				$result = mysql_query($query,$sphinxql);
				if (!$result || mysql_errno($sphinxql) > 0) {
					print "<div style='color:red;font-size:12px;font-familty:Arial;'>AkkuSearch is unable to service your request at this time. Please try again later.\n</div>\n";
					
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
								$nullids = 'no';
								while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
								   if($row['@groupby']==0)
								   {
									   $nullids = 'yes';
								   }
									if($attrsIds==''){
										$attrsIds = $row['@groupby'];
									}else{
										$attrsIds.= ','.$row['@groupby'];
									}
									
											$attrsNewArray[$row['@groupby']] = array("@groupby"=>$row['@groupby'],
																 "{$attr}"=>$row[$attr],
																 "cnt"=>$row['cnt']);

								} 
									if($attr=="cat_id" && $attrsIds!=''){
									
										$catCond = '';
										if($_GET['query']!=''){
											$catCond = "AND name LIKE '".$_GET['query']."%'";											
										}
																			
										$queCategory="select sno, name as catname, sno AS snoIDs from manage where sno IN({$attrsIds}) AND type='Category' ".$catCond." ";
										if($nullids=="yes")
										{
											$queCategory.=" UNION SELECT '0','null','0' ";
										}
										$queCategory.=" ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,{$SPHINX_CONF['page_size']} ";
										
										$resqueCategory=mysql_query($queCategory,$db);
										$resCntCategory = mysql_num_rows($resqueCategory);
																			
										while($rowCategory=mysql_fetch_assoc($resqueCategory))
										{
											$attrstotal += $attrsNewArray[$rowCategory['sno']]['cnt'];
												$checked = '';
												if (isset($_GET[$attr])){ 
													$xplode = explode(",",replace_ie($_GET[$attr]));
													if (in_array($rowCategory['sno'], $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$rowCategory['snoIDs']."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($rowCategory['catname']))."</a>";
											if($c==1){
												$attrscontent .="<i class='narrow'>(".$attrsNewArray[$rowCategory['sno']]['cnt'].")</i>";
											}
												$attrscontent .="</li>";
										}
										if($resCntCategory == 0)
										{
											$attrscontent .= '<li>No data found.</li>';
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$rowroleType['snoIDs']])){ 
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$rowrolePer['snoIDs']])){ 
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
									$queroleRate = "SELECT CRC32(entity_roledetails.rate) as sno, entity_roledetails.rate,CRC32(entity_roledetails.rate) AS snoIDs FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMContact' AND CRC32(entity_roledetails.rate) IN({$attrsIds}) GROUP BY CRC32(entity_roledetails.rate) ORDER BY entity_roledetails.rate LIMIT 0,{$SPHINX_CONF['page_size']}";
									
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$rowroleRate['snoIDs']])){ 
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$coms])){ 
												$checked = 'checked="checked"' ; 
											}
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$coms."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($candCommTypes_attributes[$coms]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$coms]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
									}
								} else
								if($attr=="crmgroups" && $attrsIds!='')
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$coms])){ 
												$checked = 'checked="checked"' ; 
											}
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$coms."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst($candGroups_attributes[$coms]))."</a>";
											if($c==1){
											$attrscontent .="<i class='narrow'>(".$attrsNewArray[$coms]['cnt'].")</i>";
											}
											$attrscontent .="</li>";
											
										}
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$coms])){ 
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
										
										if($attr=="csno"){ $print_value = $row['cname']; }
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
										
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$value."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
										if(($viewattr=="zip" || $viewattr=="areacode") && ($_GET[$viewattr.'miles']!='' &&  $_GET['radius'.$viewattr]!='')){
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$idA])){ 
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
											
											$sqlCountSelect = "SELECT COUNT(DISTINCT snoid) as cnt FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2} @(".implode(',',$subSqlCount).") {$idA}') {$q3} AND crc_accessto IN (2914988887,{$username})";
											$sqlCount = mysql_fetch_array(mysql_query($sqlCountSelect,$sphinxql));
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$idA."' ".$checked." onclick='javascript:deSelectChk();' /><a class='ckboxa' href='javascript:void(0);'>".replace_string($idA)."</a>";
												if($c==1){
													$attrscontent .="<i class='narrow'><small>(".$sqlCount['cnt'].")-".$areaCodeMilesList[$idA][0]." miles</small></i>";
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr][$idA])){ 
												$checked = 'checked="checked"' ; 
											}
											$sqlCountSelect = "SELECT COUNT(DISTINCT snoid) as cnt FROM {$SPHINX_CONF['sphinx_index']} WHERE MATCH('{$q}{$q2} @(hareacode,wareacode,mareacode) {$idA}') {$q3} AND crc_accessto IN (2914988887,{$username})";
											$sqlCount = mysql_fetch_array(mysql_query($sqlCountSelect,$sphinxql));
											
											$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$idA."' ".$checked." /><a class='ckboxa' href='javascript:void(0);'>".replace_string($idA)."</a>";
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
											if(isset($_SESSION['SPHINX_Contacts'][$attr]['I|'.$value])){ 
												$checked = 'checked="checked"' ; 
											}
											if(isset($_SESSION['SPHINX_Contacts'][$attr]['E|'.$value])){ 
												$checked = 'checked="checked"' ; 
											}
										$attrscontent .= "<li><input class='ckbox' type='checkbox' name='".$attr."' value='".$value."' ".$checked." onclick='javascript:deSelectChk();' /><a class='ckboxa' href='javascript:void(0);'>".html_tls_entities(ucfirst(replace_string($print_value)))."</a>";
										
										if(($viewattr=="zip" || $viewattr=="areacode") && ($_GET[$viewattr.'miles']!='' &&  $_GET['radius'.$viewattr]!='')){
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
							print '<li class="nodata"  id="nodata">No matching records found.</li>';
							if($viewattr=='ytitle')
							{ 
								print '<style type="text/css">
										#selrow{display:none !important;}
										#maxSel20Alert{display:none !important;}
									  </style>';
							}
						}
				}
?>