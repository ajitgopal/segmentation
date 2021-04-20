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
require("visualsearch_com_setup.php");
require_once('json_functions.inc');

$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

$viewattr = $_GET['viewattr'];
$vsAQuery = "SELECT * FROM sphinx_filter_columns WHERE module_id=2 and index_col_name='".$_GET['viewattr']."' AND status=1 order by defaultorder";
$vsARes = mysql_query($vsAQuery,$db);
$vsAFetch = mysql_fetch_array($vsARes);
$querystring = base64_decode($_GET['querystring']);
parse_str($querystring,$params);
// print_r($params);
	if(count($params)!=0)
	{
		foreach($params as $o=>$ov)
		{
			$_GET[$o] = $ov;
		}
	}

	$q = isset($_GET['q'])?stripslashes($_GET['q']):'';
	$q = trim(strtolower($q));
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
	$qa = '';
	if($_GET['term']!=''){
	$tq = trim($_GET['term']);
	$aq = explode(' ',$tq);
	if(strlen($aq[count($aq)-1])<2){
		$searchstr = $tq;
	}else{
		$searchstr = $tq.'*';
	}
		
			if($_GET['viewattr']=="ctype"){
				$qa.= " @ctype_name ^".strtolower($searchstr)." | ".strtolower($searchstr);
			}else if($_GET['viewattr']=="compstatus"){
				$qa.= " @cinfostatus ^".strtolower($searchstr)." | ".strtolower($searchstr);
			}else if($_GET['viewattr']=="csource"){
				$qa.= " @csource_name ^".strtolower($searchstr)." | ".strtolower($searchstr);$searchVar = "csource_name";
			}else if($_GET['viewattr']=="hrm_deptid"){
				$qa.= " @hrm_deptname ^".strtolower($searchstr)." | ".strtolower($searchstr);$searchVar = "hrm_deptname";
			}else if($_GET['viewattr']=="country_id"){
				$qa.= " @country_name ^".strtolower($searchstr)." | ".strtolower($searchstr);
			}else if($_GET['viewattr']=="owner"){
				$qa.= " @owname ^".strtolower($searchstr)." | ".strtolower($searchstr);
			}else if($_GET['viewattr']=="cuser"){
				$qa.= " @createdby ^".strtolower($searchstr)." | ".strtolower($searchstr);
			}else if($_GET['viewattr']=="muser"){
				$qa.= " @modifiedby ^".strtolower($searchstr)." | ".strtolower($searchstr);
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
				 //if ($attr == $attr2) //we dont want to filter on the current attribute. Otherwise the breakdown would only show matching. 
                       // continue;
				
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
			 
				
			
				if($_GET['viewattr']=="ctype"){
					$orderby = "ctype_name ASC";
				}else if($_GET['viewattr']=="compstatus"){
					$orderby = "cinfostatus ASC";
				}else if($_GET['viewattr']=="csource"){
					$orderby = "csource_name ASC";
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
			
			if(in_array($attr,$subArray)){
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
							$countryS = $resultS['queryIds'];
						}else
						{
							$countryS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$countryS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
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
							$countryS = $resultS['queryIds'];
						}else
						{
							$countryS = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$countryS}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="role_rates")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND entity_roledetails.rate LIKE '%{$_GET['term']}%'";
						}
						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(CRC32(entity_roledetails.rate))) AS queryIds FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMCompany' {$sub} order by entity_roledetails.rate ASC";						
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
					if($attr=="opp_name")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(name)) as queryIds FROM staffoppr_oppr WHERE {$sub} AND oppr_status='ACTIVE' order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_steps")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " steps LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CRC32(steps)) as queryIds FROM staffoppr_oppr WHERE {$sub} AND oppr_status='ACTIVE' order by steps ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_stage")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE type='stage' {$sub} order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_otype")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE type='businesstype' {$sub} order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_lead")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE type='compsource' {$sub} order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_reason")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE type='Reason' {$sub} order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_probability")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " probability LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(probability) as queryIds FROM staffoppr_oppr WHERE {$sub} AND oppr_status='ACTIVE' order by probability ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_amount")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " ammount LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(CAST(amount_clear(ammount) AS DECIMAL(25,0))) as queryIds FROM staffoppr_oppr WHERE {$sub} AND oppr_status='ACTIVE' order by ammount ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_ecdate")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " UNIX_TIMESTAMP(cdate) LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(UNIX_TIMESTAMP(cdate)) as queryIds FROM staffoppr_oppr WHERE {$sub} AND oppr_status='ACTIVE' order by UNIX_TIMESTAMP(cdate) ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_products")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE type='products' {$sub} order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_other")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE type='other' {$sub} order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_createdby")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND emp_list.name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(staffoppr_oppr.cuser) as queryIds FROM staffoppr_oppr LEFT JOIN emp_list ON staffoppr_oppr.cuser=emp_list.username where 1=1 {$sub} AND staffoppr_oppr.oppr_status='ACTIVE' order by emp_list.name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
					}
					if($attr=="opp_modifiedby")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " AND emp_list.name LIKE '%{$_GET['term']}%'";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(staffoppr_oppr.muser) as queryIds FROM staffoppr_oppr LEFT JOIN emp_list ON staffoppr_oppr.muser=emp_list.username where 1=1 {$sub} AND staffoppr_oppr.oppr_status='ACTIVE' order by emp_list.name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$oppids = $resultS['queryIds'];
						}else
						{
							$oppids = "0";
						}
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE crc_accessto IN (2914988887,{$username}) AND {$attr} IN ({$oppids}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,100 OPTION max_matches=500000";
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
					
						$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt, weight() AS w FROM {$SPHINX_CONF['sphinx_index']}  WHERE  MATCH('{$qa}') AND crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY w DESC LIMIT 0,10 OPTION ranker=sph04";
					
				}else
				{
				   
					$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE   crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,10 OPTION ranker=sph04";
					
				}
		  }
				if($attr=="owner"){ $query = str_replace("@groupby,","@groupby,owname,",$query);  } 
				if($attr=="cuser"){ $query = str_replace("@groupby,","@groupby,createdby,",$query);  }
				if($attr=="muser"){ $query = str_replace("@groupby,","@groupby,modifiedby,",$query);  }
		  if($attr=="role_commtype" && $attrsIds!=''){
				$explodeComm = explode(",",$attrsIds);
				if(count($explodeComm)!=0)
				{
					foreach($explodeComm as $coms)
					{
						$sphArr[] = array('id' => utf8_encode($coms),'label' =>utf8_encode($candCommTypes_attributes[$coms]));
					}
				}
			}else
			{

				// adding Department Conditions
				$query = str_replace("WHERE","WHERE hrm_deptid IN (".$deptAccesSno.") AND ",$query);
				
				//echo $query;

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
										$queEmpType="SELECT CRC32(entity_roledetails.rate) as sno, entity_roledetails.rate FROM entity_roledetails, entity_roles WHERE entity_roles.crsno=entity_roledetails.crsno AND entity_roles.entityType='CRMCompany' CRC32(entity_roledetails.rate) IN({$attrsIds}) GROUP BY entity_roledetails.rate ORDER BY FIELD(CRC32(entity_roledetails.rate), {$attrsIds}) LIMIT 0,10";
										
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
									if($attr=="opp_name")
									{
										$queoppName="select CRC32(name) as sno, name from staffoppr_oppr where CRC32(name) IN({$attrsIds}) AND oppr_status='ACTIVE' GROUP BY name ORDER BY FIELD(CRC32(name), {$attrsIds}) LIMIT 0,10";
										
										$resoppName=mysql_query($queoppName,$db);
										$attrstotal = 0;
										while($rowoppName=mysql_fetch_assoc($resoppName))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppName['sno']),'label' =>utf8_encode($rowoppName['name']));
										}
									}
									if($attr=="opp_steps")
									{
										$queoppSteps="select CRC32(steps) as sno, steps from staffoppr_oppr where CRC32(steps) IN({$attrsIds}) AND oppr_status='ACTIVE' GROUP BY steps ORDER BY FIELD(CRC32(steps), {$attrsIds}) LIMIT 0,10";
										
										$resoppSteps=mysql_query($queoppSteps,$db);
										$attrstotal = 0;
										while($rowoppSteps=mysql_fetch_assoc($resoppSteps))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppSteps['sno']),'label' =>utf8_encode($rowoppSteps['steps']));
										}
									}
									if($attr=="opp_stage")
									{
										$queoppStage="select sno, name from manage where type='stage' AND sno IN({$attrsIds}) GROUP BY name ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										
										$resoppStage=mysql_query($queoppStage,$db);
										$attrstotal = 0;
										while($rowoppStage=mysql_fetch_assoc($resoppStage))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppStage['sno']),'label' =>utf8_encode($rowoppStage['name']));
										}
									}
									if($attr=="opp_otype")
									{
										$queoppOtype="select sno, name from manage where type='businesstype' AND sno IN({$attrsIds}) GROUP BY name ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										
										$resoppOtype=mysql_query($queoppOtype,$db);
										$attrstotal = 0;
										while($rowoppOtype=mysql_fetch_assoc($resoppOtype))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppOtype['sno']),'label' =>utf8_encode($rowoppOtype['name']));
										}
									}
									if($attr=="opp_lead")
									{
										$queoppLead="select sno, name from manage where type='compsource' AND sno IN({$attrsIds}) GROUP BY name ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										
										$resoppLead=mysql_query($queoppLead,$db);
										$attrstotal = 0;
										while($rowoppLead=mysql_fetch_assoc($resoppLead))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppLead['sno']),'label' =>utf8_encode($rowoppLead['name']));
										}
									}
									if($attr=="opp_reason")
									{
										$queoppReasons="select sno, name from manage where type='Reason' AND sno IN({$attrsIds}) GROUP BY name ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										
										$resoppReasons=mysql_query($queoppReasons,$db);
										$attrstotal = 0;
										while($rowoppReasons=mysql_fetch_assoc($resoppReasons))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppReasons['sno']),'label' =>utf8_encode($rowoppReasons['name']));
										}
									}
									if($attr=="opp_probability")
									{
										$queoppName="select probability as sno, probability as name from staffoppr_oppr where probability IN({$attrsIds}) AND oppr_status='ACTIVE' GROUP BY probability ORDER BY FIELD(probability, {$attrsIds}) LIMIT 0,10";
										
										$resoppName=mysql_query($queoppName,$db);
										$attrstotal = 0;
										while($rowoppName=mysql_fetch_assoc($resoppName))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppName['sno']),'label' =>utf8_encode($rowoppName['name']));
										}
									}
									if($attr=="opp_amount")
									{
										$queoppName="select CAST(amount_clear(ammount) AS DECIMAL(25,0)) as sno, CAST(amount_clear(ammount) AS DECIMAL(25,0)) as name from staffoppr_oppr where CAST(amount_clear(ammount) AS DECIMAL(25,0)) IN({$attrsIds}) AND oppr_status='ACTIVE' GROUP BY ammount ORDER BY FIELD(CAST(amount_clear(ammount) AS DECIMAL(25,0)), {$attrsIds}) LIMIT 0,10";
										
										$resoppName=mysql_query($queoppName,$db);
										$attrstotal = 0;
										while($rowoppName=mysql_fetch_assoc($resoppName))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppName['sno']),'label' =>utf8_encode($rowoppName['name']));
										}
									}
									if($attr=="opp_ecdate")
									{
										$queoppName="select UNIX_TIMESTAMP(cdate) as sno, cdate as name from staffoppr_oppr where  UNIX_TIMESTAMP(cdate) IN({$attrsIds}) AND oppr_status='ACTIVE' GROUP BY cdate ORDER BY FIELD(UNIX_TIMESTAMP(cdate), {$attrsIds}) LIMIT 0,10";
										
										$resoppName=mysql_query($queoppName,$db);
										$attrstotal = 0;
										while($rowoppName=mysql_fetch_assoc($resoppName))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppName['sno']),'label' =>utf8_encode($rowoppName['name']));
										}
									}
									if($attr=="opp_products")
									{
										$queoppProducts="select sno, name from manage where type='products' AND sno IN({$attrsIds}) GROUP BY name ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										
										$resoppProducts=mysql_query($queoppProducts,$db);
										$attrstotal = 0;
										while($rowoppProducts=mysql_fetch_assoc($resoppProducts))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppProducts['sno']),'label' =>utf8_encode($rowoppProducts['name']));
										}
									}
									if($attr=="opp_other")
									{
										$queoppOthers="select sno, name from manage where type='other' AND sno IN({$attrsIds}) GROUP BY name ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										
										$resoppOthers=mysql_query($queoppOthers,$db);
										$attrstotal = 0;
										while($rowoppOthers=mysql_fetch_assoc($resoppOthers))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppOthers['sno']),'label' =>utf8_encode($rowoppOthers['name']));
										}
									}
									if($attr=="opp_createdby")
									{
										$queoppCreatedby="select staffoppr_oppr.cuser as sno, emp_list.name as name from staffoppr_oppr LEFT JOIN emp_list ON staffoppr_oppr.cuser=emp_list.username where staffoppr_oppr.cuser IN({$attrsIds}) AND staffoppr_oppr.oppr_status='ACTIVE' GROUP BY staffoppr_oppr.cuser ORDER BY FIELD(staffoppr_oppr.cuser, {$attrsIds}) LIMIT 0,10";
										
										$resoppCreatedby=mysql_query($queoppCreatedby,$db);
										$attrstotal = 0;
										while($rowoppCreatedby=mysql_fetch_assoc($resoppCreatedby))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppCreatedby['sno']),'label' =>utf8_encode($rowoppCreatedby['name']));
										}
									}
									if($attr=="opp_modifiedby")
									{
										$queoppModifiedby="select staffoppr_oppr.muser as sno, emp_list.name as name from staffoppr_oppr LEFT JOIN emp_list ON staffoppr_oppr.muser=emp_list.username where staffoppr_oppr.muser IN({$attrsIds}) AND staffoppr_oppr.oppr_status='ACTIVE' GROUP BY staffoppr_oppr.muser ORDER BY FIELD(staffoppr_oppr.muser, {$attrsIds}) LIMIT 0,10";
										
										$resoppModifiedby=mysql_query($queoppModifiedby,$db);
										$attrstotal = 0;
										while($rowoppModifiedby=mysql_fetch_assoc($resoppModifiedby))
										{
												$sphArr[] = array('id' => utf8_encode($rowoppModifiedby['sno']),'label' =>utf8_encode($rowoppModifiedby['name']));
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
									
									$value = trim($row[$attr]);
									
								
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