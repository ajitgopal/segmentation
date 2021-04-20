<?php
/*
Project: Sphinx Search
Purpose: Visual Search Search suggestion.
Created  By: Nagaraju M.
Created Date: 31 Aug 2015
Modified Date: 31 Aug 2015
*/
require("global.inc");
//Sphinx includes 
require("sphinx_config.php");
require("sphinx_common_class.php");	
require("visualsearch_job_setup.php");
require_once('json_functions.inc');

$deptAccessObj = new departmentAccess();
$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

$viewattr = $_GET['viewattr'];
$vsAQuery = "SELECT * FROM sphinx_filter_columns WHERE module_id=4 and index_col_name='".$_GET['viewattr']."' AND status=1 order by defaultorder";
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
			
		if($_GET['viewattr']=="jobtype_id"){
			$qa.= " @jobtype ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="posstatus"){
			$qa.= " @status_name ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="jostage"){
			$qa.= " @jostage_name ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="sourcetype"){
			$qa.= " @sourcetype_name ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="catid"){
			$qa.= " @category ^".strtolower($searchstr)." | ".strtolower($searchstr);
		}else if($_GET['viewattr']=="hrm_deptid"){
		$qa.= " @hrm_deptname ^".strtolower($searchstr)." | ".strtolower($searchstr);
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
					if($attr=="sub_status")
					{
						$sub='';
						if($_GET['term']!='')
						{
							$sub.= " name LIKE '%{$_GET['term']}%' AND ";
						}
						
						$sub_query = "SELECT GROUP_CONCAT(sno) as queryIds FROM manage WHERE {$sub}  type='interviewstatus' order by name ASC";
						$resultS = mysql_fetch_array(mysql_query($sub_query,$db));
						if($resultS['queryIds']!='')
						{
							$attrsIds = $resultS['queryIds'];
						}else
						{
							$attrsIds = "0";
						}
						
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
						$query = "SELECT @groupby,mvalue as s_dept, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype s_dept {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
						
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
						$query = "SELECT @groupby,mvalue as s_cat, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype s_cat {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
						
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
						$query = "SELECT @groupby,mvalue as s_spec, COUNT(*) AS cnt, id as eid FROM {$SPHINX_CONF['masters_index_name']} WHERE match('@mtype s_spec {$sub}') GROUP BY eid ORDER BY mvalue ASC LIMIT 0,10 OPTION ranker=sph04";
						
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
							$sub.= " AND amount LIKE '%{$_GET['term']}%'";
						}
						$sub_query = "SELECT GROUP_CONCAT(DISTINCT(CRC32(amount))) AS queryIds FROM assign_commission WHERE assigntype='JO' {$sub}  order by amount ASC";						
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
			}else if($qa!=''){
				
					$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt, weight() AS w FROM {$SPHINX_CONF['sphinx_index']}  WHERE  MATCH('{$qa}') AND crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY w DESC LIMIT 0,10 OPTION ranker=sph04";
				
			}else
			{
			   
				$query = "SELECT @groupby,{$attr}, COUNT(*) AS cnt FROM {$SPHINX_CONF['sphinx_index']}  WHERE   crc_accessto IN (2914988887,{$username}) GROUP BY {$attr} ORDER BY {$attr} ASC LIMIT 0,10 OPTION ranker=sph04";
				
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
			}else if($attr=="sub_status" && $attrsIds!=''){
									
				$queEmpType="select sno, name from manage where sno IN ({$attrsIds}) GROUP BY sno ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
				
				$resEmpType=mysql_query($queEmpType,$db);
				$attrstotal = 0;
				while($rowEmpType=mysql_fetch_assoc($resEmpType))
				{
						$sphArr[] = array('id' => utf8_encode($rowEmpType['sno']),'label' =>utf8_encode($rowEmpType['name']));
				}
			}
			else if($attr=="s_dept" && $attrsIds!=''){
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
			}
			else
			{				
				// adding Department Conditions
				$query = str_replace("WHERE","WHERE hrm_deptid IN (".$deptAccesSno.") AND ",$query);
				
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
									
										$queSkilsType="select sno, mvalue as skillname, sno AS snoIDs from req_master where sno IN({$attrsIds}) AND mtype='skillname' ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resSkilsType=mysql_query($queSkilsType,$db);
										while($rowSkillsType=mysql_fetch_assoc($resSkilsType))
										{
											$sphArr[] = array('id' => utf8_encode($rowSkillsType['skillname']),'label' =>utf8_encode($rowSkillsType['skillname']));
											
										}
									}
									
									if($attr=="s_level" && $attrsIds!=''){
									
										$queSkilsType="select sno, mvalue as skilllevel, sno AS snoIDs from req_master where sno IN({$attrsIds}) AND mtype='skilllevel' {$q_sql} ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLevel=mysql_fetch_assoc($resSkilsType))
										{
											$sphArr[] = array('id' => utf8_encode($rowSkillsLevel['skilllevel']),'label' =>utf8_encode($rowSkillsLevel['skilllevel']));
										}
									}
									
									if($attr=="s_lastused" && $attrsIds!=''){
									
										$queSkilsType="select sno, mvalue as lastused, sno AS snoIDs from req_master where sno IN({$attrsIds}) AND mtype='lastused' {$q_sql} ORDER BY FIELD(sno, {$attrsIds}) LIMIT 0,10";
										$resSkilsType=mysql_query($queSkilsType,$db);
										$attrstotal = 0;
										while($rowSkillsLastused=mysql_fetch_assoc($resSkilsType))
										{
											$sphArr[] = array('id' => utf8_encode($rowSkillsLastused['lastused']),'label' =>utf8_encode($rowSkillsLastused['lastused']));
											
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
										$queEmpType="SELECT CRC32(amount) as sno, amount as rate FROM assign_commission WHERE assigntype='JO' AND CRC32(amount) IN({$attrsIds}) GROUP BY amount ORDER BY FIELD(CRC32(amount), {$attrsIds}) LIMIT 0,10";
										
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