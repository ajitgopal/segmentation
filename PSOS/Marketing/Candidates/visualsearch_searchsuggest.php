<?php
/* 
Project: Sphinx Search
Purpose: Visual Search Search suggestion.
Created By: Nagaraju M.
Created Date: 31 Aug 2015
Modified Date: 31 Aug 2015
*/
require("global.inc");
//Sphinx includes 
require("sphinx_config.php");
require("sphinx_common_class.php");	
require("visualsearch_cand_setup.php");
require_once('json_functions.inc');
require_once('credential_management/countries_states.php');

$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

$viewattr = $_GET['viewattr'];
$vsAQuery = "SELECT * FROM sphinx_filter_columns WHERE module_id=3 and index_col_name='".$_GET['viewattr']."' AND status=1 order by defaultorder";
$vsARes = mysql_query($vsAQuery,$db);
$vsAFetch = mysql_fetch_array($vsARes);	
$querystring = base64_decode($_GET['querystring']);
parse_str($querystring,$params);
//print_r($params);
if(count($params)!=0)
{
	foreach($params as $o=>$ov)
	{
		$_GET[$o] = $ov;
	}
}

	$q = isset($_GET['q'])?$_GET['q']:'';
	$q = trim(strtolower(stripslashes($q)));
	$q = str_replace(',',' and ',$q);
	$q = str_replace('/',' ',$q);
	$q = preg_replace('/ or /',' | ',$q);
	$q = preg_replace('/ and /',' ',$q);
	$q = preg_replace('/ not /',' -',$q);
	$q = preg_replace('/[^\w~\|\(\)\^\$\?"\/=-]+/',' ',trim(strtolower($q)));
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
	$qa = '';
	if($_GET['term']!=''){
	$tq = trim($_GET['term']);
	$aq = explode(' ',$tq);
	if(strlen($aq[count($aq)-1])<2){
		$searchstr = $tq;
	}else{
		$searchstr = $tq.'*';
	}
		if($_GET['viewattr']=="crc_candtype"){
			//$qa.= " @candtype ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="jobcatid"){
			$qa.= " @category ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="deptid"){
			$qa.= " @deptname ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="cl_sourcetype"){
			$qa.= " @cl_source_type ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="cl_status"){
			$qa.= " @candidate_status ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="country_id"){
			$qa.= " @country_name ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="owner"){
			$qa.= " @owname ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="cuser"){
			$qa.= " @createdby ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="muser"){
			$qa.= " @modifiedby ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="ascontact"){
			$qa.= " @ascont_name ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if ((strpos($_GET['viewattr'], 'cust_') !== false) && ($vsAFetch['index_col_type']=='numeric') && ($vsAFetch['multivalues']=='N'))
		{				
			
		}else
		{
			$qa.= " @".$_GET['viewattr']." ^".rtrim(cleentext(strtolower($searchstr),'yes','yes'),'_')."*";
			
			$sub = " @mvalue ^".strtolower($searchstr);
		}
	}

		$counter = 1;
		$attr = $_GET['viewattr'];
				$q2 = '';
				$q3 = '';
				
			 foreach ($SPHINX_CONF['sphinx_attributes'] as $attr2 => $type2) {
				 if ($attr == $attr2) //we dont want to filter on the current attribute. Otherwise the breakdown would only show matching. 
                        continue;
				
					if (!empty($_GET[$attr2])) {
                        if ($type2 == 'string') {
							$dbSearchParmVal=str_replace("\\","\\\\",addslashes($_GET[$attr2]));
							$dbSearchParmVal=str_replace("\'","'",$dbSearchParmVal);
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
										$new_dbSearchParmVal.= ' ("^'.implode('" | "',$si_ids).'$")';
									}
									if(count($se_ids)!=0)
									{										
										$new_dbSearchParmVal.= ' !("^'.implode('" | "',$se_ids).'$")';
									}																
								}
							//$dbSearchParmVal = str_replace(',','" | "',$dbSearchParmVal);
							$q2 .= ' @'.$attr2.'  '.$new_dbSearchParmVal;
                        } else {
							$ep = explode(',',$_GET[$attr2]);
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
										$q3 .= ' AND '.$attr2.' NOT IN ('.implode(',',$e_ids).')';
									}
									if(count($i_ids)!=0)
									{												
										$q3 .= ' AND '.$attr2.' IN ('.implode(',',$i_ids).')';
									}
								//$q3 .= ' AND '.$attr2.' IN ('.$_GET[$attr2].')';
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

			if(in_array($attr,$subArray)){

					if($attr=="skills")
					{
						$query = "SELECT @groupby,mvalue as skills, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skillname {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="s_level")
					{
						$query = "SELECT @groupby,mvalue as s_level, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skilllevel {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="s_lastused")
					{
						$query = "SELECT @groupby,mvalue as s_lastused, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype lastused {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="s_type")
					{
						$query = "SELECT @groupby,mvalue as s_type, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype skilltype {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="edu_country")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " country LIKE '{$_GET['term']}%'";
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
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$countryS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="edudegree_level")
					{
						$query = "SELECT @groupby,mvalue as edudegree_level, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype edu_level {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="employment_city")
					{
						$query = "SELECT @groupby,mvalue as employment_city, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_city {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="employment_state")
					{
						$query = "SELECT @groupby,mvalue as employment_state, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_state {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}

					if($attr=="employment_country")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " country LIKE '%{$_GET['term']}%'";
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
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$countryS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="employment")
					{
						$query = "SELECT @groupby,mvalue as employment, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_cname {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="employment_type")
					{
						$query = "SELECT @groupby,mvalue as employment_type, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype work_ftitle {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="contact_method")
					{
						$contMthdAttributes = array("1001"=>"Phone","2002"=>"Mobile","3003"=>"Fax","4004"=>"Email");
						$matchingContMthdRecords = preg_grep("/{$_GET['term']}/i",$contMthdAttributes);
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt, weight() AS w FROM {$SPHINX_CONF['sphinx_index']}  WHERE  crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY w DESC LIMIT 0,10 OPTION ranker=sph04";
					}
					if($attr=="role_types")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " roletitle LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM company_commission WHERE {$sub} order by roletitle ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$roletypes = $resultS['queryIds'];
						}else
						{
							$roletypes = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$roletypes}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="role_persons")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND e.name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(e.username)) as queryIds FROM emp_list e LEFT JOIN hrcon_compen h ON (h.username = e.username) LEFT JOIN manage m ON (m.sno = h.emptype) WHERE e.lstatus != 'DA' AND e.lstatus != 'INACTIVE' AND e.empterminated !='Y' AND h.ustatus = 'active' AND m.type = 'jotype' AND m.status='Y' AND m.name IN ('Internal Direct', 'Internal Temp/Contract') AND h.job_type <> 'Y' {$sub} ORDER BY e.name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$rolepersons = $resultS['queryIds'];
						}else
						{
							$rolepersons = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolepersons}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="role_rates")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND entity_roledetails.rate LIKE '%{$_GET['term']}%'";
						}
						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(CRC32(entity_roledetails.rate))) AS queryIds FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMCandidate' {$sub} order by entity_roledetails.rate ASC";						
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$rolerates = $resultS['queryIds'];
						}else
						{
							$rolerates = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$rolerates}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="role_commtype")
					{
						$getCommTypes = array();
						if($_GET['term']!='')
						{
							foreach($candCommTypes_attributes as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$getCommTypes[] = $key;	
								}								
							}							
						}	
						if(count($getCommTypes)>0)
						{
							$attrsIds = implode(",",$getCommTypes);
						}else
						{
							$attrsIds = "0";
						}
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="des_jtype")
					{
						$desJobtype = array();
						if($_GET['term']!='')
						{
							foreach($desired_jtype_arr as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$desJobtype[] = $key;	
								}								
							}							
						}	
						if(count($desJobtype)>0)
						{
							$attrsIds = implode(",",$desJobtype);
						}else
						{
							$attrsIds = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
						
					}
					if($attr=="des_jstatus")
					{
						$desJobtype = array();
						if($_GET['term']!='')
						{
							foreach($desired_jstatus_arr as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$desJobtype[] = $key;	
								}								
							}							
						}	
						if(count($desJobtype)>0)
						{
							$attrsIds = implode(",",$desJobtype);
						}else
						{
							$attrsIds = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
						
					}
					if($attr=="s_dept")
					{
						$skill_dept = array();
						if($_GET['term']!='')
						{
							foreach($skill_departments as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$skill_dept[] = $key;	
								}								
							}							
						}	
						if(count($skill_dept)>0)
						{
							$attrsIds = implode(",",$skill_dept);
						}else
						{
							$attrsIds = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="s_cat")
					{
						$skill_cat = array();
						if($_GET['term']!='')
						{
							foreach($skill_categories as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$skill_cat[] = $key;	
								}								
							}							
						}	
						if(count($skill_cat)>0)
						{
							$attrsIds = implode(",",$skill_cat);
						}else
						{
							$attrsIds = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
						
					}
					if($attr=="s_spec")
					{
						$skill_spec = array();
						if($_GET['term']!='')
						{
							foreach($skill_specialities as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$skill_spec[] = $key;	
								}								
							}							
						}	
						if(count($skill_spec)>0)
						{
							$attrsIds = implode(",",$skill_spec);
						}else
						{
							$attrsIds = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
						
					}
					if($attr=="crmgroups")
					{
						$getgroupids = array();
						if($_GET['term']!='')
						{
							foreach($candGroups_attributes as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$getgroupids[] = $key;	
								}								
							}							
						}	
						if(count($getgroupids)>0)
						{
							$attrsIds = implode(",",$getgroupids);
						}else
						{
							$attrsIds = "0";
						}
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="cre_state")
					{
						$getCre_stateIds = array();
						if($_GET['term']!='')
						{
							foreach($displayStateNames as $key => $value) {
								if (stripos($value, $_GET['term'])!== false) {
										$getCre_stateIds[] = $key;	
								}								
							}						
						}	
						if(count($getCre_stateIds)>0)
						{
							$attrsIds = implode(",",$getCre_stateIds);
							$attrsIds = "'".str_replace(",","','",$attrsIds)."'";
						}else
						{
							$attrsIds = "'0'";
						}
						$query_credStats = "SELECT GROUP_CONCAT(explodeStrAndConcat(',',state_id)) as state_ids FROM candidate_credentials WHERE FIND_IN_SET(".$attrsIds.",state_id)";
						$query_credStats_res = mysql_fetch_assoc(mysql_query($query_credStats,$db));
						//$credState_attrIds = "'".str_replace(",","','",$query_credStats_res['state_ids'])."'";
						$credState_attrIds = ($query_credStats_res['state_ids']!='')?$query_credStats_res['state_ids']:'0';
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$credState_attrIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if ((strpos($_GET['viewattr'], 'cust_') !== false) && ($vsAFetch['index_col_type']=='numeric') && ($vsAFetch['multivalues']=='Y'))
					{	
						
						$getElementid = mysql_fetch_array(mysql_query("SELECT id FROM udf_form_details WHERE CONCAT_WS('_','cust',id)='".$_GET['viewattr']."'", $db));
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND udf_form_details_options.eoption LIKE '%{$_GET['term']}%'";
						}
					
						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(CRC32(udf_form_details_options.eoption))) AS queryIds FROM udf_form_details_options WHERE custom_form_details_id=".$getElementid['id']." {$sub} order by udf_form_details_options.eoption ASC";						
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$attrsIds = $resultS['queryIds'];
						}else
						{
							$attrsIds = "0";
						}
						
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$attrsIds}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
			}else{
			if($qa!=''){
				if($attr=="crc_candtype"){
					$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt, weight() AS w FROM {$SPHINX_CONF['sphinx_index']}  WHERE  MATCH('{$qa}') AND crc_accessto IN (2914988887,{$username}) GROUP BY crc_candtype1 ORDER BY w DESC LIMIT 0,10 OPTION ranker=sph04";
				}else
				{
					$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt, weight() AS w FROM {$SPHINX_CONF['sphinx_index']}  WHERE  MATCH('{$qa}') AND crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY w DESC LIMIT 0,10 OPTION ranker=sph04";
				}
			}else
			{
				if($attr=="crc_candtype"){
						$searchStr = $_GET['term'];
						$candAttributes = array("577727202"=>"Candidate","2766739447"=>"Employee","189759857"=>"My Candidate");
						$matchingRecords = preg_grep("/{$searchStr}/i",$candAttributes);
						if(count($matchingRecords) > 0)
						{
							$candAttributesStr = implode(",",array_keys($matchingRecords));
							$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE   crc_accessto IN (2914988887,{$username}) AND crc_candtype1 IN ({$candAttributesStr}) GROUP BY crc_candtype1 ORDER BY {$attr} ASC LIMIT 0,10 OPTION ranker=sph04";
						}
						else
						{
							$query = "SELECT @groupby,IF(crc_candtype=2766739447,2766739447,if(owner={$username},189759857,577727202)) as crc_candtype1, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE   crc_accessto IN (2914988887,{$username}) AND crc_candtype1 IN (0)  GROUP BY crc_candtype1 ORDER BY {$attr} ASC LIMIT 0,10 OPTION ranker=sph04";
						}
			   }else{
			   
				$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE   crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,10 OPTION ranker=sph04";
				
				}
			}
	  }
				if($attr=="owner"){ $query = str_replace("@groupby,","@groupby,owname,",$query);  } 
				if($attr=="cuser"){ $query = str_replace("@groupby,","@groupby,createdby,",$query);  }
				if($attr=="muser"){ $query = str_replace("@groupby,","@groupby,modifiedby,",$query);  }
				if($attr=="ascontact"){ $query = str_replace("@groupby,","@groupby,ascont_name,",$query);  }
				
			if($attr=="role_commtype" && $attrsIds!=''){
				$explodeComm = explode(",",$attrsIds);
				if(count($explodeComm)!=0)
				{
					foreach($explodeComm as $coms)
					{
						$sphArr[] = array('id' => utf8_encode($coms),'label' =>utf8_encode($candCommTypes_attributes[$coms]));
					}
				}
			}else if($attr=="crmgroups" && $attrsIds!=''){
				$explodegroups = explode(",",$attrsIds);
				if(count($explodegroups)!=0)
				{
					foreach($explodegroups as $coms)
					{
						$sphArr[] = array('id' => utf8_encode($coms),'label' =>utf8_encode($candGroups_attributes[$coms]));
					}
				}
			}else if($attr=="des_jtype" && $attrsIds!=''){
				$explodetypes = explode(",",$attrsIds);
				if(count($explodetypes)!=0)
				{
					foreach($explodetypes as $type)
					{
						$sphArr[] = array('id' => utf8_encode($type),'label' =>utf8_encode($desired_jtype_arr[$type]));
					}
				}
			}else if($attr=="des_jstatus" && $attrsIds!=''){
				$explodetypes = explode(",",$attrsIds);
				if(count($explodetypes)!=0)
				{
					foreach($explodetypes as $type)
					{
						$sphArr[] = array('id' => utf8_encode($type),'label' =>utf8_encode($desired_jstatus_arr[$type]));
					}
				}
			}else if($attr=="s_dept" && $attrsIds!=''){
				$explodeDept = explode(",",$attrsIds);
				if(count($explodeDept)!=0)
				{
					foreach($explodeDept as $type)
					{
						$sphArr[] = array('id' => utf8_encode($type),'label' =>utf8_encode($skill_departments[$type]));
					}
				}
			}else if($attr=="s_cat" && $attrsIds!=''){
				$explodeCat = explode(",",$attrsIds);
				if(count($explodeCat)!=0)
				{
					foreach($explodeCat as $type)
					{
						$sphArr[] = array('id' => utf8_encode($type),'label' =>utf8_encode($skill_categories[$type]));
					}
				}
			}else if($attr=="s_spec" && $attrsIds!=''){
				$explodeSpec = explode(",",$attrsIds);
				if(count($explodeSpec)!=0)
				{
					foreach($explodeSpec as $type)
					{
						$sphArr[] = array('id' => utf8_encode($type),'label' =>utf8_encode($skill_specialities[$type]));
					}
				}
			}else
			{
				//echo $query;
				
				// adding Department Conditions
				$query = str_replace("WHERE","WHERE deptid IN (".$deptAccesSno.") AND ",$query);

				$result = mysql_query($query,$sphinxql);
				if (!$result || mysql_errno($sphinxql) > 0) {
					print "Query failed: -- please try again later.\n";
					if ($SPHINX_CONF['debug'])
						print "<br/>Error: ".mysql_error($sphinxql)."\n\n";
						return;
				}else {

				$attrstotal = 0;
				$attrscontent = '';
					$ress = array();
					if(mysql_num_rows($result)!=0){
					
						if($type="numeric")
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
											$attrsNewArray[] = array("@groupby"=>$row['@groupby'],
																	 "{$attr}"=>$row[$attr],
																	 "cnt"=>$row['cnt']);
									}
									
									if($attr=="skills" && $attrsIds!=''){
									
										$queSkilsType="select sno, mvalue as skillname, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='skillname' ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resSkilsType=mysql_query($queSkilsType,$db);
										while($rowSkillsType=mysql_fetch_assoc($resSkilsType))
										{
											$sphArr[] = array('id' => utf8_encode($rowSkillsType['skillname']),'label' =>utf8_encode($rowSkillsType['skillname']));
											
										}
									}
									
									if($attr=="s_level" && $attrsIds!=''){
									
										$queSkilsType="select sno, mvalue as skilllevel, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='skilllevel' {$q_sql} ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLevel=mysql_fetch_assoc($resSkilsType))
										{
											$sphArr[] = array('id' => utf8_encode($rowSkillsLevel['skilllevel']),'label' =>utf8_encode($rowSkillsLevel['skilllevel']));
										}
									}
									
									if($attr=="s_lastused" && $attrsIds!=''){
									
										$queSkilsType="select sno, mvalue as lastused, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='lastused' {$q_sql} ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLastused=mysql_fetch_assoc($resSkilsType))
										{
											$sphArr[] = array('id' => utf8_encode($rowSkillsLastused['lastused']),'label' =>utf8_encode($rowSkillsLastused['lastused']));
											
										}
									}
									
									if($attr=="s_type" && $attrsIds!=''){
									
										$queSkilsType="select sno, mvalue as skilltype, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='skilltype' {$q_sql} ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLastused=mysql_fetch_assoc($resSkilsType))
										{
											$sphArr[] = array('id' => utf8_encode($rowSkillsLastused['skilltype']),'label' =>utf8_encode($rowSkillsLastused['skilltype']));
											
										}
									}
									
									if($attr=="education" && $attrsIds!=''){
										$queEduType="select sno,username,heducation, COUNT(DISTINCT username) AS cnt, GROUP_CONCAT(sno) AS snoIDs  from candidate_edu where sno IN({$attrsIds}) AND heducation!='' GROUP BY heducation order by cnt DESC LIMIT 0,10";
										$resEduType=mysql_query($queEduType,$db);
										$attrstotal = 0;
										while($rowEduType=mysql_fetch_assoc($resEduType))
										{
											$sphArr[] = array('id' => utf8_encode($rowEduType['heducation']),'label' =>utf8_encode($rowEduType['heducation']));
											
										}
									}
									
									if($attr=="edu_city" && $attrsIds!=''){
										$queEduType="select sno,username,educity, COUNT(DISTINCT username) AS cnt, GROUP_CONCAT(sno) AS snoIDs  from candidate_edu where sno IN({$attrsIds}) AND educity!='' GROUP BY educity order by cnt DESC LIMIT 0,10";
										$resEduType=mysql_query($queEduType,$db);
										$attrstotal = 0;
										while($rowEduType=mysql_fetch_assoc($resEduType))
										{
											$sphArr[] = array('id' => utf8_encode($rowEduType['educity']),'label' =>utf8_encode($rowEduType['educity']));
										}
									}
									
									if($attr=="edu_state" && $attrsIds!=''){
										$queEduType="select sno,username,edustate, COUNT(DISTINCT username) AS cnt, GROUP_CONCAT(sno) AS snoIDs  from candidate_edu where sno IN({$attrsIds}) AND edustate!='' GROUP BY edustate order by cnt DESC LIMIT 0,10";
										$resEduType=mysql_query($queEduType,$db);
										$attrstotal = 0;
										while($rowEduType=mysql_fetch_assoc($resEduType))
										{
											$sphArr[] = array('id' => utf8_encode($rowEduType['edustate']),'label' =>utf8_encode($rowEduType['edustate']));
										}
									}
									
									if($attr=="edu_country" && $attrsIds!=''){
										$queEduType="select sno,educountry from candidate_edu where educountry IN({$attrsIds}) AND educountry!='0' GROUP BY educountry ORDER BY FIELD(educountry, {$attrsIds}) LIMIT 0,10";
										$resEduType=mysql_query($queEduType,$db);
										$attrstotal = 0;
										while($rowEduType=mysql_fetch_assoc($resEduType))
										{
											$sphArr[] = array('id' => utf8_encode($country_attributes[$rowEduType['educountry']]),'label' =>utf8_encode($country_attributes[$rowEduType['educountry']]));
											
										}
									}
									
									if($attr=="edudegree_level" && $attrsIds!=''){
										$queEduType="select sno, mvalue as edudegree_level, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='edu_level' ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resEduType=mysql_query($queEduType,$db);
										$attrstotal = 0;
										while($rowEduType=mysql_fetch_assoc($resEduType))
										{
											$sphArr[] = array('id' => utf8_encode($rowEduType['edudegree_level']),'label' =>utf8_encode($rowEduType['edudegree_level']));
											
										}
									}
									
									if($attr=="employment" && $attrsIds!=''){
										$queEmp="select sno, mvalue as cname, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_cname' ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resEmp=mysql_query($queEmp,$db);
										$attrstotal = 0;
										while($rowEmp=mysql_fetch_assoc($resEmp))
										{
												$sphArr[] = array('id' => utf8_encode($rowEmp['cname']),'label' =>utf8_encode($rowEmp['cname']));
										}
									}
									if($attr=="employment_city" && $attrsIds!=''){
										$queEmpType="select sno, mvalue as city, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_city' ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$sphArr[] = array('id' => utf8_encode($rowEmpType['city']),'label' =>utf8_encode($rowEmpType['city']));
										
										}
									}
									
									if($attr=="employment_state" && $attrsIds!=''){
										$queEmpType="select sno, mvalue as state, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_state' ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$sphArr[] = array('id' => utf8_encode($rowEmpType['state']),'label' =>utf8_encode($rowEmpType['state']));
											
										}
									}
									
									if($attr=="employment_country" && $attrsIds!=''){
									
										$queEmpType="select sno, country from candidate_work where country IN({$attrsIds}) AND country!='0' GROUP BY country ORDER BY FIELD(country, {$attrsIds}) LIMIT 0,10";
										
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$sphArr[] = array('id' => utf8_encode($country_attributes[$rowEmpType['country']]),'label' =>utf8_encode($country_attributes[$rowEmpType['country']]));
										}
									}
									
									if($attr=="employment_type" && $attrsIds!=''){
										$queEmpType="select sno, mvalue as ftitle, sno AS snoIDs from candidate_master where sno IN({$attrsIds}) AND mtype='work_ftitle' ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$sphArr[] = array('id' => utf8_encode($rowEmpType['ftitle']),'label' =>utf8_encode($rowEmpType['ftitle']));
										}
									}
									
									
									if($attr=="contact_method" && $attrsIds!=''){
											$attrstotal = 0;
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
													$xplode = explode(",",$_GET[$attr]);
													if (in_array($cm, $xplode, true)) {
														$checked = 'checked="checked"' ; 
													}else{
														$checked = '';
													}
												}
												if(in_array($cm,array_keys($matchingContMthdRecords)))
												{
													$sphArr[] = array('id' => utf8_encode($cm_print),'label' =>utf8_encode($cm_print));
												}
											}
									}
									
									if($attr=="role_types" && $attrsIds!=''){
									
										$queEmpType="select sno, roletitle from company_commission where sno IN({$attrsIds}) GROUP BY roletitle ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$sphArr[] = array('id' => utf8_encode($rowEmpType['sno']),'label' =>utf8_encode($rowEmpType['roletitle']));
										}
									}
									if($attr=="role_persons" && $attrsIds!=''){
									
										$queEmpType="select CRC32(username) as sno, name from emp_list where CRC32(username) IN({$attrsIds}) GROUP BY name ORDER BY FIELD(CRC32(username), {$attrsIds}) LIMIT 0,10";
										
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$sphArr[] = array('id' => utf8_encode($rowEmpType['sno']),'label' =>utf8_encode($rowEmpType['name']));
										}
									}
									if($attr=="role_rates")
									{
										$queEmpType="SELECT CRC32(entity_roledetails.rate) as sno, entity_roledetails.rate FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMCandidate' CRC32(entity_roledetails.rate) IN({$attrsIds}) GROUP BY entity_roledetails.rate ORDER BY FIELD(CRC32(entity_roledetails.rate), {$attrsIds}) LIMIT 0,10";
										
										$resEmpType=mysql_query($queEmpType,$db);
										$attrstotal = 0;
										while($rowEmpType=mysql_fetch_assoc($resEmpType))
										{
												$sphArr[] = array('id' => utf8_encode($rowEmpType['sno']),'label' =>utf8_encode($rowEmpType['rate']));
										}										
									}
									if($attr=="role_commtype" && $attrsIds!=''){									
										$explodeComm = explode(",",$attrsIds);
										if(count($explodeComm)!=0)
										{
											foreach($explodeComm as $coms)
											{
												$sphArr[] = array('id' => utf8_encode($coms),'label' =>utf8_encode($candCommTypes_attributes[$coms]));
											}
										}
									}
									if($attr=="crmgroups" && $attrsIds!=''){
									
										$explodegroups = explode(",",$attrsIds);
										if(count($explodegroups)!=0)
										{
											foreach($explodegroups as $coms)
											{
												$sphArr[] = array('id' => utf8_encode($coms),'label' =>utf8_encode($candGroups_attributes[$coms]));
											}
										}
									}
									if($attr=="cre_state" && $attrsIds!=''){
										$queCreStatee="select GROUP_CONCAT(state_id) AS state_id , GROUP_CONCAT(explodeStrAndConcat(',',state_id)) AS snoIDs from candidate_credentials where explodeStrAndConcat(',',state_id) IN ({$attrsIds})  AND state_id!='' ORDER BY FIELD(CRC32(state_id), {$attrsIds}) LIMIT 0,1";
										$queCreStatee_row=mysql_fetch_row(mysql_query($queCreStatee,$db));
										$states_creds = array();
										$stateId_arry = array();
										$stateCrc_arry = array();
										$stateId_arry = explode(',',$queCreStatee_row[0]);
										$stateCrc_arry = explode(',',$queCreStatee_row[1]);
										for($i=0;$i<count($stateId_arry);$i++)
										{
											$states_creds[substr($stateId_arry[$i], 3)] = array($stateCrc_arry[$i],$stateId_arry[$i]);
										}
										ksort($states_creds);
										$attrstotal = 0;
										foreach($states_creds as $states_creds_key=>$states_creds_val){
											$credStateFullName = $displayStateNames[$states_creds_val[1]];
											$sphArr[] = array('id' => utf8_encode($states_creds_val[0]),'label' =>utf8_encode($credStateFullName));
										}
									}
									if ((strpos($_GET['viewattr'], 'cust_') !== false) && ($vsAFetch['index_col_type']=='numeric') && ($vsAFetch['multivalues']=='Y'))
									{
										$queUdfOpt="SELECT CRC32(udf_form_details_options.eoption) as sno, udf_form_details_options.eoption as options  FROM udf_form_details_options WHERE CRC32(udf_form_details_options.eoption) IN({$attrsIds}) GROUP BY udf_form_details_options.eoption ORDER BY FIELD(CRC32(udf_form_details_options.eoption), {$attrsIds}) LIMIT 0,10";
										$resUdfOpt=mysql_query($queUdfOpt,$db);
										$attrstotal = 0;
										while($rowUdfOpt=mysql_fetch_assoc($resUdfOpt))
										{
												$sphArr[] = array('id' => utf8_encode($rowUdfOpt['sno']),'label' =>utf8_encode($rowUdfOpt['options']));
										}
									}
							}else{
							
								while($row = mysql_fetch_array($result)) {
									if($attr=="crc_candtype")
									{
										$value = trim($row[1]);
									}
									else
									{
										$value = trim($row[$attr]);
									}
								
									//$value = trim($row[$attr]); //we dont use @groupby, because it wrong for string attributes.
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
									
									if($attr=="owner"){ $print_value = $row['owname']; }
									if($attr=="cuser"){ $print_value = $row['createdby']; }
									if($attr=="muser"){ $print_value = $row['modifiedby']; }
									if($attr=="ascontact"){ $print_value = $row['ascont_name']; }
									if ((strpos($_GET['viewattr'], 'cust_') !== false) && ($vsAFetch['index_col_type']=='numeric') && ($vsAFetch['multivalues']=='N'))
									{				
										if($print_value==0)
										{											
											$print_value = 'null';
										}else
										{
											$print_value = date("m/d/Y",replace_string($print_value));
										}
									}		
									if((trim($print_value)!='') && ($print_value!='0'))
									{
										
										$sphArr[] = array('id' => utf8_encode(replace_string($print_value)),'label' =>utf8_encode(replace_string($print_value)));
									}
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
								if((trim($print_value)!='') && ($print_value!='0'))
								{
									$sphArr[] = array('id' => utf8_encode(replace_string($print_value)),'label' =>utf8_encode(replace_string($print_value)));
								}
							}
							
						}
						
							$counter++;
						}else
						{
							
						}
				}
			}
echo json_encode($sphArr);
exit();
?>