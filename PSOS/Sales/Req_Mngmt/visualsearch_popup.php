<?php
/*
Project: Sphinx Search
Purpose: Visual Search Popup.
Created By:  Nagaraju M.
Created Date: 31 Aug 2015
Modified Date: 31 Aug 2015
*/
	require("global.inc");
	//Sphinx includes 
	require("sphinx_config.php");
	require("sphinx_common_class.php");	
	require("visualsearch_job_setup.php");
	

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

	$viewattr = $_GET['viewattr'];
	if($_GET['viewattr']=='')
	{
		if($_GET['querystring']!=''){
			unset($_SESSION["SPHINX_Joborders"]);
		}
		//unset($_SESSION["SPHINX_Joborders"]);
		$_GET['viewattr'] = key($SPHINX_CONF['sphinx_attributes']);
	}
	if($_REQUEST['reset'] == 1){
		unset($_SESSION["SPHINX_Joborders"]);
		unset($_SESSION['SPHINX_Joborders_sub']);
	}
	$viewattr = $_GET['viewattr'];
	if(preg_match('/^(["\']).*\1$/m', $_GET['q'])) $_GET['q'] = "=".$_GET['q'];

	if(!empty($_SESSION["SPHINX_Joborders"]))
	{
		$searchstr = '';
		foreach($_SESSION['SPHINX_Joborders'] as $filtertype=>$filtername){	
			if(count($_SESSION['SPHINX_Joborders'][$filtertype])>0)
			{
				$searchstr .= "&".$filtertype."=";
				foreach($_SESSION['SPHINX_Joborders'][$filtertype] as $filters_id=>$filters_val)
				{
					$searchstr .= $filters_id.',';
				}
			}		
		}
		$searchstr = rtrim($searchstr,",");
		$searchstr = str_replace(array(',&','?&'),array('&','?'),$searchstr);
		if($_GET['q']!='')
		{
			$searchstr = $searchstr.'&q='.$_GET['q'].'&notesopt='.$_GET['notesopt'];
		}
		$querystring = base64_encode($searchstr);
		$_GET['querystring'] = $querystring;
		parse_str($searchstr,$params);
	}
	//print_r($params);
	if(count($params)!=0)
	{
		foreach($params as $o=>$ov)
		{
			$_GET[$o] = $ov;
		}
	}
	if(isset($_GET['notesopt'])){ $notesopt = $_GET['notesopt']; }
		
	$oldurlString = '';
	function rm_url_param($param_rm, $query)
	{
		//empty($query)? $query=$_SERVER['QUERY_STRING'] : '';
		parse_str($query, $params);
		
		unset($params[$param_rm]);
		$newquery = '';
		foreach($params as $k => $v)
		{ 
			$newquery .= '&'.$k.'='.$v; 
		}
		return substr($newquery,1);
	}
	
$oldurlString =  rm_url_param($viewattr,$querystring);

if($_GET['viewattr'])
	$oldurlString =  rm_url_param('viewattr',$oldurlString);

if($_GET['querystring'])
	$oldurlString =  rm_url_param('querystring',$oldurlString);

if($_GET['query'])
	$oldurlString =  rm_url_param('query',$oldurlString);

if($_GET['send_x'])
	$oldurlString =  rm_url_param('send_x',$oldurlString);

if($_GET['send_y'])
	$oldurlString =  rm_url_param('send_y',$oldurlString);

if($_GET['psm'])
	$oldurlString =  rm_url_param('psm',$oldurlString);
	
if($_GET['radius'.$viewattr])
	$oldurlString =  rm_url_param('radius'.$viewattr,$oldurlString);
	
if($_GET[$viewattr.'miles'])
	$oldurlString =  rm_url_param($viewattr.'miles',$oldurlString);

if(isset($_GET['cDateSearch']) && $viewattr=='cuser')
	$oldurlString =  rm_url_param('cDateSearch',$oldurlString);

if(isset($_GET['mDateSearch']) && $viewattr=='muser')
	$oldurlString =  rm_url_param('mDateSearch',$oldurlString);
	
if($_GET['go'])
	$oldurlString =  rm_url_param('go',$oldurlString);

	if($_GET['timeframe']!='')
	{
		$dateopt = $_GET['timeframe'];
		if($dateopt == 'tfday' || $dateopt == 'tflastweek' || $dateopt == 'tflastmonth' || $dateopt == 'tflastyear' || $dateopt == 'tfyeartodate')
		{
			$date_ranges = explode("^",datesModified($dateopt));
			$fromdate = $date_ranges[0];
			$todate = $date_ranges[1];
		}
		else
		{
			$fromdate =  ($_GET['tffromdate']) ? date("Y-m-d",strtotime($_GET['tffromdate'])) : "";
			$todate = ($_GET['tftodate']) ? date("Y-m-d",strtotime($_GET['tftodate'])) : "";
		}
		//echo $fromdate.'#'.$todate;
	}
	
	if($_GET['sitimeframe']!='')
	{
		$dateopt = $_GET['sitimeframe'];
		if($dateopt == 'sitfday' || $dateopt == 'sitflastweek' || $dateopt == 'sitflastmonth' || $dateopt == 'sitflastyear' || $dateopt == 'sitfyeartodate')
		{
			if($dateopt == 'sitfday') {$dateopt ='tfday';}
			if($dateopt == 'sitflastweek') {$dateopt ='tflastweek';}
			if($dateopt == 'sitflastmonth') {$dateopt ='tflastmonth';}
			if($dateopt == 'sitflastyear') {$dateopt ='tflastyear';}
			if($dateopt == 'sitfyeartodate') {$dateopt ='tfyeartodate';}
			
			$date_ranges = explode("^",datesModified($dateopt));
			$sifromdate = $date_ranges[0];
			$sitodate = $date_ranges[1];
		}
		else
		{
			$sifromdate =  ($_GET['sitffromdate']) ? date("Y-m-d",strtotime($_GET['sitffromdate'])) : "";
			$sitodate = ($_GET['sitftodate']) ? date("Y-m-d",strtotime($_GET['sitftodate'])) : "";
		}
	}	
	//echo $sifromdate.'#'.$sitodate;	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Job Orders Attribute Search</title>
<script type="text/javascript" src="/BSOS/scripts/sphinx/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.jscrollpane.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.columnizer.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.mousewheel.js"></script>
<script type="text/javascript" language="javascript" src="scripts/calendar.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/autocomplete/js/jquery-ui.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.cookie.js"></script>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/preloader.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<link rel="stylesheet" type="text/css" href="/BSOS/scripts/sphinx/autocomplete/css/jquery-ui-1.8.21.custom.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/sphinx_modalbox.css"  />
<script type="text/javascript">
$(document).ready(function(){
	$("#acjbvisualsearchFilterConten").addClass("filterContenthide");
		 $('#breadcrumbs').click(function(e) {  
			$("#breadcrumbs").toggleClass("acjFilterhide");
			$("#acjbvisualsearchFilterConten").toggleClass("filterContent");			
		});
	 //parent.top.$('#accitems').trigger("close");
	parent.top.$('#accitems ul li a#<?php echo $viewattr;?>').attr('class','active');
});
/*
Below Two Functions are added because of UI Design
*/
function doSPHINXSearch(e) {
	e.preventDefault();
	$('input.doSPHINXSearch').trigger('click');
	return;
}

function doSPHINXCancel(e) {
	e.preventDefault();
	$("input.doSPHINXCancel").trigger('click');
	//$('#reset_profiletitle input')[0].trigger('click');
	return;
}

function do_search()
{
    info = document.f1.cand_name.value;
    if(info == "")
    {
        alert("Enter any Search keyword");
        document.f1.cand_name.focus();
        return false
    }
    else
    {
        return true;
    }
}
 
function resetDate(fieldname)
{
	document.getElementById(fieldname).value = '';
}

function getCustomRow(element)
{
	 if(element.options[element.options.selectedIndex].value == "tfcustom")
	 {
	   document.getElementById('timeframe_row').style.display = "";
	 }else{
	   document.getElementById('timeframe_row').style.display = "none";
	   document.frm_<?=$viewattr;?>.tffromdate.value = "";
	   document.frm_<?=$viewattr;?>.tftodate.value = "";
	 }
}


function getSiCustomRow(element)
{
	 if(element.options[element.options.selectedIndex].value == "sitfcustom")
	 {
	   document.getElementById('sitimeframe_row').style.display = "";
	 }else{
	   document.getElementById('sitimeframe_row').style.display = "none";
	   document.frm_<?=$viewattr;?>.sitffromdate.value = "";
	   document.frm_<?=$viewattr;?>.sitftodate.value = "";
	 }
	if(element.value == 'ALL')
	{
		window.location.href = 'visualsearch_popup.php?viewattr=<?=$viewattr;?>&querystring=<?=$_GET['querystring'];?>';
	}	 
}

$(function() {
<?php if($_GET['radiuszip']==''){ ?>
	parent.top.$('#savezipCODE').val('');
<?php } ?>

	$("#attr-list ul").columnizer({ count: 3});
	$("#attr-list .scroll-area").jScrollPane();
	
		$("#frm_<?=$viewattr;?>").submit(function(event){
			
			<?php 
				if($viewattr=="zip")
				{
					?>
						var radius<?=$viewattr;?> = $("input#radius<?=$viewattr;?>").val();
						var miles<?=$viewattr;?> = $("input#<?=$viewattr;?>miles").val();
						
						var checked = $("#frm_<?=$viewattr;?> input[name=<?=$viewattr;?>]:checked").length > 0;
						if(checked==false)
						{
							if(radius<?=$viewattr;?>=="")
							{
								alert("Please select at least one <?=$SPHINX_CONF['sphinx_attributes_headings'][$viewattr]?>");
								return false;
							}
							if(miles<?=$viewattr;?>=="")
							{
								alert("Please enter a value for Miles");
								document.frm_<?=$viewattr;?>.<?=$viewattr;?>miles.focus();
								return false;
							}							
							document.frm_<?=$viewattr;?>.submit();
							return true;
						}else
						{
							<?php if($_GET['radiuszip']=='' && $_GET['zipmiles']==''){ ?>
							$("input#radiuszip").val('');
							$("input#zipmiles").val('');
							<?php }?>
							

								var frm = document.frm_zip;
								if(frm.radiuszip.value!='' && frm.zipmiles.value!='')
								{
									var zipcode_all ='';
							
									if(frm.<?=$viewattr;?>selecctall.checked == true){
									
										zipcode_all = '|zipcodeall';
									}
							
									var savezipCODE = frm.radiuszip.value+'|'+frm.zipmiles.value+zipcode_all;
							
									parent.top.$("input#savezipCODE").val(savezipCODE);
								}else
								{
									parent.top.$("input#savezipCODE").val('');
								}
						}
					
			<?php
				}else if($viewattr=="cuser" || $viewattr=="muser")
				{
					?>
						var timeframe = $("select#timeframe").val();
						var checked = $("#frm_<?=$viewattr;?> input[name=<?=$viewattr;?>]:checked").length > 0;
						if(checked==false)
						{
							
							if(timeframe=="")
							{
								alert("Please select at least one <?=$SPHINX_CONF['sphinx_attributes_headings'][$viewattr]?>");
								return false;
							}
							if(timeframe=="tfcustom")
							{
								 if(document.frm_<?=$viewattr;?>.tffromdate.value=="")
								 {
									alert("Please enter a from date");
									document.frm_<?=$viewattr;?>.tffromdate.focus();
									return false;
								 }
								 if(document.frm_<?=$viewattr;?>.tftodate.value=="")
								 {
									alert("Please enter a to date");
									document.frm_<?=$viewattr;?>.tftodate.focus();
									return false;
								 }
							}
							
							document.frm_<?=$viewattr;?>.submit();
							return true;
						}else
						{
							<?php if($_GET['timeframe']=='' && $_GET['viewattr']=='cuser'){ ?>
							parent.top.$('#cDateSearch').val('');
							<?php } if($_GET['timeframe']=='' && $_GET['viewattr']=='muser'){ ?>
							parent.top.$('#mDateSearch').val('');
							<?php } ?>

							<?php if($fromdate!='' && $todate!=''){ ?>
							var daterangeStr = " - (<?=date("m/d/Y",strtotime($fromdate));?> to <?=date("m/d/Y",strtotime($todate));?>) ";
							var dateSearch = "<?=$_GET['timeframe'];?>|<?=date("m/d/Y",strtotime($fromdate));?>|<?=date("m/d/Y",strtotime($todate));?>";
							<?php if($viewattr=="cuser"){?>
								parent.top.$("input#cDateSearch").val(dateSearch);
								var actionStrAppend = "&cDateSearch="+dateSearch;
							<?php } if($viewattr=="muser"){?>
								parent.top.$("input#mDateSearch").val(dateSearch);
								var actionStrAppend = "&mDateSearch="+dateSearch;
							<?php }?>
							<?php }else{?>
							var daterangeStr = '';
							var actionStrAppend = '';
							<?php }?>
						}				
				<?php }else if($viewattr=="start_date" || $viewattr=="due_date" || $viewattr=="end_date")
					{
					?>
						var timeframe = $("select#sitimeframe").val();
						var checked = $("#frm_<?=$viewattr;?> input[name=<?=$viewattr;?>]:checked").length > 0;
						if(checked==false)
						{
							
							if(timeframe=="")
							{
								alert("Please select at least one <?=$SPHINX_CONF['sphinx_attributes_headings'][$viewattr]?>");
								return false;
							}
							if(timeframe=="sitfcustom")
							{
								 if(document.frm_<?=$viewattr;?>.sitffromdate.value=="")
								 {
									alert("Please enter a from date");
									document.frm_<?=$viewattr;?>.sitffromdate.focus();
									return false;
								 }
								 if(document.frm_<?=$viewattr;?>.sitftodate.value=="")
								 {
									alert("Please enter a to date");
									document.frm_<?=$viewattr;?>.sitftodate.focus();
									return false;
								 }
							}
							
							document.frm_<?=$viewattr;?>.submit();
							return true;
						}else
						{
							<?php if($_GET['sitimeframe']=='' && $_GET['viewattr']=='start_date'){ ?>
								parent.top.$('#sDateSearch').val('');
							<?php } if($_GET['sitimeframe']=='' && $_GET['viewattr']=='due_date'){ ?>
								parent.top.$('#dDateSearch').val('');
							<?php } if($_GET['sitimeframe']=='' && $_GET['viewattr']=='end_date'){ ?>
								parent.top.$('#eDateSearch').val('');
							<?php } ?>

							<?php if($sifromdate!='' && $sitodate!=''){ ?>
							var daterangeStr = " - (<?=date("m/d/Y",strtotime($sifromdate));?> to <?=date("m/d/Y",strtotime($sitodate));?>) ";
							var dateSearch = "<?=$_GET['sitimeframe'];?>|<?=date("m/d/Y",strtotime($sifromdate));?>|<?=date("m/d/Y",strtotime($sitodate));?>";
								<?php if($viewattr=="start_date"){?>
									parent.top.$("input#sDateSearch").val(dateSearch);
									var actionStrAppend = "&sDateSearch="+dateSearch;
								<?php } if($viewattr=="due_date"){?>
									parent.top.$("input#dDateSearch").val(dateSearch);
									var actionStrAppend = "&dDateSearch="+dateSearch;
								<?php } if($viewattr=="end_date"){?>
									parent.top.$("input#eDateSearch").val(dateSearch);
									var actionStrAppend = "&eDateSearch="+dateSearch;
								<?php }?>
							<?php }else{?>
							var daterangeStr = '';
							var actionStrAppend = '';
							<?php }?>
						}				
				<?php } 
				else if($viewattr=="sub_status")
				{
					?>
						var checked = $("#frm_<?=$viewattr;?> input[name=<?=$viewattr;?>]:checked").length > 0;
						if(checked==false)
						{
							
							 if(document.frm_<?=$viewattr;?>.sitffromdate.value=="")
							 {
								alert("Please enter a From date");
								document.frm_<?=$viewattr;?>.sitffromdate.focus();
								return false;
							 }
							 if(document.frm_<?=$viewattr;?>.sitftodate.value=="")
							 {
								alert("Please enter a To date");
								document.frm_<?=$viewattr;?>.sitftodate.focus();
								return false;
							 }
							
							
							document.frm_<?=$viewattr;?>.submit();
							return true;
						}else
						{
							<?php if($_GET['sitffromdate']=='' && $_GET['sitftodate']=='' && $_GET['viewattr']=='sub_status'){ ?>
								parent.top.$('#savesubStatus').val('');
								$("input#sitffromdate").val('');
								$("input#sitftodate").val('');
							<?php }  ?>

							var frm = document.frm_sub_status;
								if(frm.sitffromdate.value!='' && frm.sitftodate.value!='')
								{
									var savesubStatus = frm.sitffromdate.value+'|'+frm.sitftodate.value;
									
									parent.top.$("input#savesubStatus").val(savesubStatus);
									var actionStrAppend = "&savesubStatus="+savesubStatus;
								}else
								{
									parent.top.$("input#savesubStatus").val('');
									var dateRangeSearch = '';
									var actionStrAppend = '';
								}
							
						}				
				<?php
				}?>
					
					$.ajax({
						url : 'savefilters.php?cmdtype=view',
						dataType:  "jsonp",
						async: false,
						success : function(session)
						{
							parent.top.$("input#SpeedSearchString").val(session.searchqstr);
							parent.top.$("#breadCrumbNav").html(session.html);							
							parent.top.$("input#cDateSearch").val(session.cDateSearch);						
							parent.top.$("input#mDateSearch").val(session.mDateSearch);
							parent.top.$("input#savezipCODE").val(session.savezipCODE);
							parent.top.$("input#sDateSearch").val(session.sDateSearch);						
							parent.top.$("input#dDateSearch").val(session.dDateSearch);
							parent.top.$("input#eDateSearch").val(session.eDateSearch);
							parent.top.$("input#savesubStatus").val(session.savesubStatus);
						}
					});
					parent.iframeLoader.init();
					parent.top.resetOpen();
					parent.top.doGridSearch('search');
					parent.top.getVsearchLeft('search');
					parent.top.modalBoxClose();
					return true;		
			
		});	
	
			$("input:checkbox[name=zip]").click(function(event){
				var checked = $("#frm_zip input:checked").length;
				if(checked>=1)
				{
					//$('.zip_box').hide();
					<?php if($_GET['radiuszip']=='' && $_GET['zipmiles']==''){ ?>
					$("input#radiuszip").val('');
					$("input#zipmiles").val('');
					<?php }?>
				}else
				{
					//$('.zip_box').show();
				}
			});		
		$('.ckboxa').click(function(){
				//var parentTag = $(this).parent().children().get(0).tagName;
				var checkbox = $(this).parent().children(),
					isChecked = checkbox.is(':checked');
					var frm  = checkbox.attr("name");
				if(isChecked) {
					checkbox.prop('checked',false);
				}else{
					checkbox.prop('checked',true); 
				}
				checkbox.trigger('change');				
		});
	
	$("#reset_<?=$viewattr?>").click(function(event){
		$('#frm_<?=$viewattr?> input:checkbox[name=<?=$viewattr?>]').attr('checked',false);
		
		<?php		
		if($viewattr=="zip"){
		?>
		$('.zip_box').show(); 
		<?php
		}
		?>
	});
		<?php
			if(!empty($_GET['zip']))
			{
				$zipList = explode(",",$_GET['zip']);
				if(count($zipList)>1)
				{
					?>
					//$('.zip_box').hide();
					<?php
				}
			}
			if((isset($_SESSION['SPHINX_Joborders_sub']['chkZipAll']) && $_SESSION['SPHINX_Joborders_sub']['chkZipAll'] !='')   )
			{
				if(isset($_SESSION['SPHINX_Joborders_sub']['chkZipAll']) && $viewattr == 'zip')
				{
			?>
					document.getElementById('selrow').style.display='block';
				
					document.getElementById('zipselecctall').checked=true;
			<?php 
				}
			}
			if( isset($_GET['querystring']) && isset($_GET['query']) && in_array( $_GET['viewattr'], array('skills','catid') ) )
			{ ?>
					document.getElementById('selrow').style.display='block';
					document.getElementById('maxSel20Alert').style.display='block';
			<?php
			} ?>
});
function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');   
    if (urlparts.length>=2) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {    
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                pars.splice(i, 1);
            }
        }

        url= urlparts[0]+'?'+pars.join('&');
        return url;
    } else {
        return url;
    }
}
function isNumber(field,name) 
{
	var str =field.value;
	for(var i=0;i<str.length;i++)
	{
		if((str.substring(i,i+1)<"0") || (str.substring(i,i+1)>"9"))
		{
			alert("The "+name+" accepts numbers only.\nPlease re-enter your "+name+".");
			field.select();
			field.focus();
			return false;
		}
	}
	return true;
}
function modalBoxCloseandCancel()
{
	var pvstr = parent.top.$("input#SpeedSearchString").val();
		$.ajax({
			url : 'savefilters.php?cmdtype=resetbynewchanges',
			dataType:  "jsonp",
			async: false,
			data: {
				q: pvstr,
				format: "json"
			},
			success : function(session)
			{
				//parent.top.$("input#SpeedSearchString").val(session.searchqstr);
				//parent.top.$("#breadCrumbNav").html(session.html);	
				//alert(session.count);					
			}
		});
	//return false;
	parent.top.modalBoxClose();
}
function selectAll()
{
	var chkall =  '';
	var searchtype = '';
	var selected = $("input[type='radio'][name='nativesearch']:checked");
	if (selected.length > 0) {
		searchtype = selected.val();
	}
	


	if(document.getElementById('<?php echo $viewattr;?>selecctall').checked==true)
	{
		var actionStrAppend='';
		<?php if($viewattr=="zip"){ ?>
			var frm = document.frm_zip;
			if(frm.radiuszip.value!='' && frm.zipmiles.value!='')
			{
				var savezipCODE = frm.radiuszip.value+'|'+frm.zipmiles.value;
				actionStrAppend = '&savezipcode='+savezipCODE;
			}
		<?php }?>
		
		var moduletype = '<?php echo $viewattr;?>';
		var moduleid = '';
		var modulevalue = '';
		var selCount = 1;

		$("input[name='<?php echo $viewattr;?>']").each(function() {
			if(moduletype=='skills' || moduletype=='catid')
				if(selCount>20) return false;
			this.checked = true;
			moduletype = this.name;
			moduleid += this.value+'^';
			modulevalue += encodeURIComponent($(this).parent().children('a').text())+'^';
			selCount++;
		});
		
		if(moduletype == 'zip')
		{
			chkall =  '&chkall=zipcodeall';
		}
		if(moduletype == 'skills')
		{
			chkall =  '&chkall=skillsall';
		}
		if(moduletype == 'catid')
		{
			chkall =  '&chkall=catidall';
		}

		$.ajax({
			url : 'savefilters.php?cmdtype=add'+chkall+'&searchtype='+searchtype+'&moduletype='+moduletype+'&moduleid='+moduleid+'&modulevalue='+modulevalue+actionStrAppend,
			type : 'post',
			dataType:  "jsonp",
			async: false,
			success : function(session)
			{
				$("#breadCrumbNav").html(session.html);
				$("#breadcrumbs").html(session.count);
				var activeclass = $('#breadcrumbs').attr('class');		
				if(activeclass=='acjbvisualsearchFilterSelect')
				{
					$("#breadcrumbs").toggleClass("acjFilterhide");
					$("#acjbvisualsearchFilterConten").toggleClass("filterContent");
				}
			}
		});
	}
	else
	{
		var moduletype = '';
		var moduleid = '';
		var modulevalue = '';
		$("input[name='<?php echo $viewattr;?>']").each(function() {
			if(this.checked == true)
			{
				this.checked = false;
				moduletype = this.name;
				moduleid += this.value+'^';
				modulevalue += encodeURIComponent($(this).parent().children('a').text())+'^';
			}
		});

		if(moduletype == 'zip')
		{
			chkall =  '&chkall=zipcodeall';
		}
		if(moduletype == 'skills')
		{
			chkall =  '&chkall=skillsall';
		}
		if(moduletype == 'catid')
		{
			chkall =  '&chkall=catidall';
		}
		
		$.ajax({
			
			url : 'savefilters.php?cmdtype=update'+chkall+'&searchtype='+searchtype+'&moduletype='+moduletype+'&moduleid='+moduleid+'&modulevalue='+modulevalue,
			type : 'post',
			dataType:  "jsonp",
			async: false,
			success : function(session)
			{
				$("#breadCrumbNav").html(session.html);
				$("#breadcrumbs").html(session.count);
				var activeclass = $('#breadcrumbs').attr('class');		
				if(activeclass=='acjbvisualsearchFilterSelect')
				{
					$("#breadcrumbs").toggleClass("acjFilterhide");
					$("#acjbvisualsearchFilterConten").toggleClass("filterContent");
				}
			}
		});
		
	}
}
function deSelectChk(){
	var e = document.getElementsByName('<?php echo $viewattr;?>');
	var radius ='';
	var miles ='';
	if(document.getElementById('radius<?php echo $viewattr;?>')){
		 radius = document.getElementById('radius<?php echo $viewattr;?>').value;
	}
	if(document.getElementById('<?php echo $viewattr;?>miles')){
		 miles = document.getElementById('<?php echo $viewattr;?>miles').value;
	}
	for (var i=0; i < e.length; i++)
    {
        if (e[i].checked == false)
        {
            document.getElementById('<?php echo $viewattr;?>selecctall').checked=false;
            return;
        }
    }
    for (var i=0; i < e.length; i++)
    {
        if (e[i].checked == true && radius!='' && miles!='')
        {
            document.getElementById('<?php echo $viewattr;?>selecctall').checked=true;
            return;
        }        
    }
}
</script>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
</head>
<body>
<!-- Preloader -->
<span id="closeicon" class="close" onclick="javascript: modalBoxCloseandCancel();"></span>
<div id="preloader">
  <div id="status">&nbsp;</div>
</div>
<div id="msg" class="VisualNewpopupM">
  <table width="730" cellpadding=1 cellspacing=1 border=0>
    <tr>
      <td valign=middle align="left" colspan="2" style="white-space: nowrap; padding:5px 15px"><form method="get" name="search_form" id="search_form">
          <input type="hidden" name="viewattr" value="<?=$viewattr;?>" >
          <input type="hidden" name="querystring" value="<?=$_GET['querystring'];?>" >
          <font><?php echo $SPHINX_CONF['sphinx_attributes_headings'][$viewattr];?>:</font>
          <input type="text" size="45" maxlength="30" class="VisualNewpopupInput" name="query" id="suggest" value="<?=$_GET['query'];?>" autocomplete="off"/>
         <span class="visualNewSearch" onclick="return document.getElementById('search_form').submit();"><i class="fa fa-search fa-lg" type="submit" id="send" name="send" align="absmiddle" title="Search"></i></span>
          <span class="visualNewSearch" onclick="return window.location.href='visualsearch_popup.php?viewattr=<?=$viewattr;?>&reset=1&querystring=<?=$_GET[querystring];?>';"><a href="visualsearch_popup.php?viewattr=<?=$viewattr;?>&reset=1&querystring=<?=$_GET['querystring'];?>" class="<?=$cssClas?>"><i class="fa fa-reply fa-lg" title="Reset"></i></a></span>
        </form></td>
    </tr>
    <tr>
      <td colspan="2"><div class="VisualNewSortListHedBg">
      </td>
    </tr>
	
    <tr>
    
    <td colspan="2">
    
    <form method="get" id="frm_<?=$viewattr?>" name="frm_<?=$viewattr?>">
    
    <input type="hidden" name="viewattr" value="<?=$viewattr;?>" >
    <input type="hidden" name="querystring" value="<?=$_GET['querystring'];?>" >
    <table width=100% cellpadding=1 cellspacing=1 border=0>
	 <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="nativesearch" value="I" checked="checked" >Include in Search &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="nativesearch" value="E">Exclude in Search &nbsp;</td><td align="right"  style="padding-right:30px; display:none;" id="selrow"><div style="color:red;display:none;float:right;padding-top:4px;" id="maxSel20Alert">&nbsp;(* Limit 20)</div><div style="float:right;"><input type="checkbox" name="<?php echo $viewattr;?>selecctall" id="<?php echo $viewattr;?>selecctall" value="1"  onclick="javascript:selectAll();">Select/Deselect All</div></td></tr>
      <tr>
        <td colspan="2"><?php include_once("visualsearch_popupfilters.php");?>
        </td>
      </tr>
      <tr>
        <td valign=top align="left"></td>
        <td valign=top align="right">&nbsp;&nbsp;</td>
      </tr>
    </table>
    </td>
    
    </tr>
  </table>
</div>
<div class="acjbvisualsearchFilterCount">
    <?php if($viewattr=="zip" || $viewattr=="areacode"){
			if($viewattr=="zip" ){
				if(isset($_SESSION['SPHINX_Joborders_sub']['savezipCODE']) && !empty($_SESSION['SPHINX_Joborders_sub']['savezipCODE']))
				{
					$zipcodesearch = $_SESSION['SPHINX_Joborders_sub']['savezipCODE'];
					$zipcode_ranges = explode("|",$zipcodesearch);
					$radius = $zipcode_ranges[0];
					$miles = $zipcode_ranges[1];
				}
				if((isset($_GET['radiuszip'])) && (isset($_GET['zipmiles'])))
				{
					$radius = $_GET['radiuszip'];
					$miles = $_GET['zipmiles'];
				}
			}
			if($viewattr=="areacode"){
				if(isset($_SESSION['SPHINX_Joborders_sub']['saveareacodePSM']) && !empty($_SESSION['SPHINX_Joborders_sub']['saveareacodePSM']))
				{
					$areacodesearch = $_SESSION['SPHINX_Joborders_sub']['saveareacodePSM'];
					$areacode_ranges = explode("|",$areacodesearch);
					$radius = $areacode_ranges[0];
					$miles = $areacode_ranges[1];
				}
				
				
				
				if((isset($_GET['radiusareacode'])) && (isset($_GET['areacodemiles'])))
				{
					$radius = $_GET['radiusareacode'];
					$miles = $_GET['areacodemiles'];
				}
			}		
		
	  ?>
	    <div class="acjbvisualFilterB">
    <table width="100%" height="100%">
      <tr>
        <td colspan="2" valign="bottom" align="<?=($viewattr=='areacode')?'left':'left'?>"><span id="radiusbox" class="<?=$attr?>_box"><font style="FONT-SIZE: 8pt; FONT-FAMILY: Arial; COLOR: black;float:left;"><b>Note: Using below filters you can search the
          <?=$SPHINX_CONF['sphinx_attributes_headings'][$attr]?>
          with in miles</b></font><br/>
          <?=$SPHINX_CONF['sphinx_attributes_headings'][$attr]?>
          <input id="radius<?=$attr?>" type="text" maxlength="5" size="5" name="radius<?=$attr?>" value="<?=$radius;?>">
          with in
          <input id="<?=$attr?>miles" type="text" maxlength="3" size="3" name="<?=$attr?>miles" value="<?=$miles;?>">
          miles</span></td>
      </tr>
    </table>
	  </div>
    <?php }
	
	if($viewattr == "sub_status" ){ //Search Filter for Submission Status
		$sitftodate='';
		$sitffromdate='';
		
		
				if(isset($_SESSION['SPHINX_Joborders_sub']['savesubStatus']) && !empty($_SESSION['SPHINX_Joborders_sub']['savesubStatus']))
				{
					$subStatussearch = $_SESSION['SPHINX_Joborders_sub']['savesubStatus'];
					$subStatus_ranges = explode("|",$subStatussearch);
					$sitffromdate = $subStatus_ranges[0];
					$sitftodate = $subStatus_ranges[1];
				}
				if((isset($_GET['sitffromdate'])) && (isset($_GET['sitftodate'])))
				{
					$sitffromdate = $_GET['sitffromdate'];
					$sitftodate = $_GET['sitftodate'];
				}
			
		?>
		<div class="acjbvisualFilterB">
		<table width="100%" height="100%">
		<tr>
					<td valign="bottom" align="left" colspan="2" width=10% class="">
					<span id="sitimeframe_row">From : <input type="text" class="drpdwne" readonly="" id="sitffromdate" size="8" value="<?=$sitffromdate;?>" name="sitffromdate"> <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitffromdate'});</script>
				<a href=javascript:resetDate('sitffromdate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a> To : <input type="text" class="drpdwne" readonly="" id="sitftodate" size="8" value="<?=$sitftodate;?>" name="sitftodate"> 
				<script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitftodate'});</script>
				</font><a href=javascript:resetDate('sitftodate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a></span>
					</td>
				</tr>
				</table>
		</div>
		<?php }?>
	<?php if($viewattr=="cuser"){
		if(isset($_SESSION['SPHINX_Joborders_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['cDateSearch']))
		{
			$cdatesearch = $_SESSION['SPHINX_Joborders_sub']['cDateSearch'];
			$cdate_ranges = explode("|",$cdatesearch);			
			$timeframe = $cdate_ranges[0];
			$tftodate = $cdate_ranges[2];
			$tffromdate = $cdate_ranges[1];
		}		
  ?>
    <div class="acjbvisualFilterB">
    <table width="100%" height="100%">
      <tr>
        <td colspan="2" valign="bottom" align="left"><span id="radiusbox" class="<?=$attr?>_box">
          <?php if($viewattr=="cuser"){ echo "Created Date"; }else{ echo "Modified Date"; } ?>
          <select class="drpdwne" onchange="getCustomRow(this)" name="timeframe" id="timeframe">
            <option value="all">All</option>
            <option value="tfday" <?php if($timeframe=="tfday"){ ?> selected="selected"<?php }?>>ToDay</option>
            <option value="tflastweek" <?php if($timeframe=="tflastweek"){ ?> selected="selected"<?php }?>>Last Week</option>
            <option value="tflastmonth" <?php if($timeframe=="tflastmonth"){ ?> selected="selected"<?php }?>>Last Month</option>
            <option value="tflastyear" <?php if($timeframe=="tflastyear"){ ?> selected="selected"<?php }?>>Last year</option>
            <option value="tfyeartodate" <?php if($timeframe=="tfyeartodate"){ ?> selected="selected"<?php }?>>This Year to Date</option>
            <option value="tfcustom" <?php if($timeframe=="tfcustom"){ ?> selected="selected"<?php }?>>Select Date Range</option>
          </select>
          <span id="timeframe_row" style="display:<?php if($timeframe!='' && $timeframe=='tfcustom'){ echo ''; }else{ echo 'none'; }?>">From :
          <input type="text" class="drpdwne" readonly="" id="tffromdate" size="8" value="<?=$tffromdate;?>" name="tffromdate">
          <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'tffromdate'});</script>
          <a href=javascript:resetDate('tffromdate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a> To :
          <input type="text" class="drpdwne" readonly="" id="tftodate" size="8" value="<?=$tftodate;?>" name="tftodate">
          <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'tftodate'});</script>
          </font><a href=javascript:resetDate('tftodate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a></span></span></td>
      </tr>
    </table>
	  </div>
    <?php } ?>
    <?php if($viewattr=="muser"){		
		if(isset($_SESSION['SPHINX_Joborders_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['mDateSearch']))
		{
			$mdatesearch = $_SESSION['SPHINX_Joborders_sub']['mDateSearch'];
			$mdate_ranges = explode("|",$mdatesearch);			
			$timeframe = $mdate_ranges[0];
			$tftodate = $mdate_ranges[2];
			$tffromdate = $mdate_ranges[1];
		}
  ?>
    <div class="acjbvisualFilterB">
    <table width="100%" height="100%">
      <tr>
        <td colspan="2" valign="bottom" align="left"><span id="radiusbox" class="<?=$attr?>_box">
          <?php if($viewattr=="cuser"){ echo "Created Date"; }else{ echo "Modified Date"; } ?>
          <select class="drpdwne" onchange="getCustomRow(this)" name="timeframe" id="timeframe">
            <option value="all">All</option>
            <option value="tfday" <?php if($timeframe=="tfday"){ ?> selected="selected"<?php }?>>ToDay</option>
            <option value="tflastweek" <?php if($timeframe=="tflastweek"){ ?> selected="selected"<?php }?>>Last Week</option>
            <option value="tflastmonth" <?php if($timeframe=="tflastmonth"){ ?> selected="selected"<?php }?>>Last Month</option>
            <option value="tflastyear" <?php if($timeframe=="tflastyear"){ ?> selected="selected"<?php }?>>Last year</option>
            <option value="tfyeartodate" <?php if($timeframe=="tfyeartodate"){ ?> selected="selected"<?php }?>>This Year to Date</option>
            <option value="tfcustom" <?php if($timeframe=="tfcustom"){ ?> selected="selected"<?php }?>>Select Date Range</option>
          </select>
          <span id="timeframe_row" style="display:<?php if($timeframe!='' && $timeframe=='tfcustom'){ echo ''; }else{ echo 'none'; }?>">From :
          <input type="text" class="drpdwne" readonly="" id="tffromdate" size="8" value="<?=$tffromdate;?>" name="tffromdate">
          <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'tffromdate'});</script>
          <a href=javascript:resetDate('tffromdate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a> To :
          <input type="text" class="drpdwne" readonly="" id="tftodate" size="8" value="<?=$tftodate;?>" name="tftodate">
          <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'tftodate'});</script>
          </font><a href=javascript:resetDate('tftodate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a></span></span></td>
      </tr>
    </table>
	  </div>
    <?php } ?>
	<?php if($viewattr == 'start_date'){ 
			if(isset($_SESSION['SPHINX_Joborders_sub']['sDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['sDateSearch']))
			{
				$sdatesearch = $_SESSION['SPHINX_Joborders_sub']['sDateSearch'];
				$sdate_ranges = explode("|",$sdatesearch);			
				$sitimeframe = $sdate_ranges[0];
				$sitftodate = $sdate_ranges[2];
				$sitffromdate = $sdate_ranges[1];
			}			
	?>
	  <div class="acjbvisualFilterB">
		<table width="100%" height="100%">
      <tr>
        <td colspan="2" valign="bottom" align="left"><span id="radiusbox" class="<?=$attr?>_box">
		<?php if($viewattr=="start_date"){ echo "Start Date"; }else if($viewattr=="due_date"){ echo "Due Date"; }if($viewattr=="end_date"){ echo "End Date"; } ?> <select class="drpdwne" onchange="getSiCustomRow(this)" name="sitimeframe" id="sitimeframe">
<option value="all">All</option>
<option value="sitfday" <?php if($sitimeframe=="sitfday"){ ?> selected="selected"<?php }?>>ToDay</option>
<option value="sitflastweek" <?php if($sitimeframe=="sitflastweek"){ ?> selected="selected"<?php }?>>Last Week</option>
<option value="sitflastmonth" <?php if($sitimeframe=="sitflastmonth"){ ?> selected="selected"<?php }?>>Last Month</option>
<option value="sitflastyear" <?php if($sitimeframe=="sitflastyear"){ ?> selected="selected"<?php }?>>Last year</option>
<option value="sitfyeartodate" <?php if($sitimeframe=="sitfyeartodate"){ ?> selected="selected"<?php }?>>This Year to Date</option>
<option value="sitfcustom" <?php if($sitimeframe=="sitfcustom"){ ?> selected="selected"<?php }?>>Select Date Range</option>
</select> <span id="sitimeframe_row" style="display:<?php if($sitimeframe!='' && $sitimeframe=='sitfcustom'){ echo ''; }else{ echo 'none'; }?>">From : <input type="text" class="drpdwne" readonly="" id="sitffromdate" size="8" value="<?=$sitffromdate;?>" name="sitffromdate"> <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitffromdate'});</script>
				<a href=javascript:resetDate('sitffromdate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a> To : <input type="text" class="drpdwne" readonly="" id="sitftodate" size="8" value="<?=$sitftodate;?>" name="sitftodate"> 
				<script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitftodate'});</script>
				</font><a href=javascript:resetDate('sitftodate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a></span></span></td>
      </tr>
    </table>
	  </div>
  <?php } ?>
  
  <?php if($viewattr == 'due_date'){ 			
			if(isset($_SESSION['SPHINX_Joborders_sub']['eDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['eDateSearch']))
			{
				$edatesearch = $_SESSION['SPHINX_Joborders_sub']['eDateSearch'];
				$edate_ranges = explode("|",$edatesearch);			
				$sitimeframe = $edate_ranges[0];
				$sitftodate = $edate_ranges[2];
				$sitffromdate = $edate_ranges[1];
			}			
	?>
	  <div class="acjbvisualFilterB">
		<table width="100%" height="100%">
      <tr>
        <td colspan="2" valign="bottom" align="left"><span id="radiusbox" class="<?=$attr?>_box">
		<?php if($viewattr=="start_date"){ echo "Start Date"; }else if($viewattr=="due_date"){ echo "Due Date"; }if($viewattr=="end_date"){ echo "End Date"; } ?> <select class="drpdwne" onchange="getSiCustomRow(this)" name="sitimeframe" id="sitimeframe">
<option value="all">All</option>
<option value="sitfday" <?php if($sitimeframe=="sitfday"){ ?> selected="selected"<?php }?>>ToDay</option>
<option value="sitflastweek" <?php if($sitimeframe=="sitflastweek"){ ?> selected="selected"<?php }?>>Last Week</option>
<option value="sitflastmonth" <?php if($sitimeframe=="sitflastmonth"){ ?> selected="selected"<?php }?>>Last Month</option>
<option value="sitflastyear" <?php if($sitimeframe=="sitflastyear"){ ?> selected="selected"<?php }?>>Last year</option>
<option value="sitfyeartodate" <?php if($sitimeframe=="sitfyeartodate"){ ?> selected="selected"<?php }?>>This Year to Date</option>
<option value="sitfcustom" <?php if($sitimeframe=="sitfcustom"){ ?> selected="selected"<?php }?>>Select Date Range</option>
</select> <span id="sitimeframe_row" style="display:<?php if($sitimeframe!='' && $sitimeframe=='sitfcustom'){ echo ''; }else{ echo 'none'; }?>">From : <input type="text" class="drpdwne" readonly="" id="sitffromdate" size="8" value="<?=$sitffromdate;?>" name="sitffromdate"> <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitffromdate'});</script>
				<a href=javascript:resetDate('sitffromdate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a> To : <input type="text" class="drpdwne" readonly="" id="sitftodate" size="8" value="<?=$sitftodate;?>" name="sitftodate"> 
				<script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitftodate'});</script>
				</font><a href=javascript:resetDate('sitftodate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a></span></span></td>
      </tr>
    </table>
	  </div>
  <?php } ?>
  
	<?php if($viewattr == 'end_date'){
			if(isset($_SESSION['SPHINX_Joborders_sub']['dDateSearch']) && !empty($_SESSION['SPHINX_Joborders_sub']['dDateSearch']))
			{
				$ddatesearch = $_SESSION['SPHINX_Joborders_sub']['dDateSearch'];
				$ddate_ranges = explode("|",$ddatesearch);			
				$sitimeframe = $ddate_ranges[0];
				$sitftodate = $ddate_ranges[2];
				$sitffromdate = $ddate_ranges[1];
			}
	?>
	  <div class="acjbvisualFilterB">
		<table width="100%" height="100%">
      <tr>
        <td colspan="2" valign="bottom" align="left"><span id="radiusbox" class="<?=$attr?>_box">
		<?php if($viewattr=="start_date"){ echo "Start Date"; }else if($viewattr=="due_date"){ echo "Due Date"; }if($viewattr=="end_date"){ echo "End Date"; } ?> <select class="drpdwne" onchange="getSiCustomRow(this)" name="sitimeframe" id="sitimeframe">
<option value="all">All</option>
<option value="sitfday" <?php if($sitimeframe=="sitfday"){ ?> selected="selected"<?php }?>>ToDay</option>
<option value="sitflastweek" <?php if($sitimeframe=="sitflastweek"){ ?> selected="selected"<?php }?>>Last Week</option>
<option value="sitflastmonth" <?php if($sitimeframe=="sitflastmonth"){ ?> selected="selected"<?php }?>>Last Month</option>
<option value="sitflastyear" <?php if($sitimeframe=="sitflastyear"){ ?> selected="selected"<?php }?>>Last year</option>
<option value="sitfyeartodate" <?php if($sitimeframe=="sitfyeartodate"){ ?> selected="selected"<?php }?>>This Year to Date</option>
<option value="sitfcustom" <?php if($sitimeframe=="sitfcustom"){ ?> selected="selected"<?php }?>>Select Date Range</option>
</select> <span id="sitimeframe_row" style="display:<?php if($sitimeframe!='' && $sitimeframe=='sitfcustom'){ echo ''; }else{ echo 'none'; }?>">From : <input type="text" class="drpdwne" readonly="" id="sitffromdate" size="8" value="<?=$sitffromdate;?>" name="sitffromdate"> <script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitffromdate'});</script>
				<a href=javascript:resetDate('sitffromdate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a> To : <input type="text" class="drpdwne" readonly="" id="sitftodate" size="8" value="<?=$sitftodate;?>" name="sitftodate"> 
				<script language='JavaScript'> new tcal ({'formname':'frm_<?=$viewattr?>','controlname':'sitftodate'});</script>
				</font><a href=javascript:resetDate('sitftodate')><img class='remind-delete-align' src=/BSOS/images/reset.gif width='17' height='16' alt='Reset' border='0' align='absmiddle'></a></span></span></td>
      </tr>
    </table>
	  </div>
  <?php } ?>

  <div id="breadcrumbs" class="acjbvisualsearchFilterSelect">(0)Filters Selected</div>
  <div id="acjbvisualsearchFilterConten" class="filterContenthide"><span id="breadCrumbNav"></span></div>
  <div class="acjbvisualsearchFilterBM">
    <div class="acjbvisualsearchFilterB">
      <div class="acjbPages">
        <?php
	if ($numberOfPages > 1) {
                print "<p class='pages'>".pagesString($currentPage,$numberOfPages)."</p>";
            }
		?>
      </div>
      <div style="float:right">
      	<span onclick="doSPHINXCancel(event);" ><i class='fa fa-ban'></i>Cancel
        <input type='hidden' name='reset_<?=$viewattr?>' id='reset_<?=$viewattr?>' value='Cancel' class='acjbvisualsearchBtn btn doSPHINXCancel' onclick="javascript:modalBoxCloseandCancel();">
        </span>
        <span onclick="doSPHINXSearch(event);"><i class='fa fa-search'></i>Search
        </span>
        <input type='submit' name='go' id='go' value='Search' class='acjbvisualsearchBtn btn doSPHINXSearch' style="display:none;">
      </div>
    </div>
  </div>
</div>
</form>
<!-- Preloader -->
<script type="text/javascript"><?php
if($_GET['reset'] == 1){
	?>
	parent.top.document.getElementById('savezipCODE').value='';
	<?php
}
?>
	//<![CDATA[
		$(window).load(function() { // makes sure the whole site is loaded
			$('#status').fadeOut(); // will first fade out the loading animation
			$('#preloader').delay(350).fadeOut('slow'); // will fade out the white DIV that covers the website.
			$('body').delay(350).css({'overflow':'visible'});
			parent.top.$('img#preloaderW').hide();
		});
	//]]>
function __highlight(s, t) {
    var matcher = new RegExp("(" + $.ui.autocomplete.escapeRegex(t) + ")", "ig");
    return s.replace(matcher, "<strong>$1</strong>");
}
function filtersApply()
{
	$(function(){
		$('input:checkbox.ckbox').on('change', function(){
			var actionStrAppend = '';
		<?php if($fromdate!='' && $todate!=''){ ?>
			var dateSearch = "<?=$_GET['timeframe'];?>|<?=date("m/d/Y",strtotime($fromdate));?>|<?=date("m/d/Y",strtotime($todate));?>";
			<?php if($viewattr=="cuser"){?>
			var actionStrAppend = "&cDateSearch="+dateSearch;
			<?php } if($viewattr=="muser"){?>
			var actionStrAppend = "&mDateSearch="+dateSearch;
			<?php } ?>
		<?php } ?>
		
		<?php if($sifromdate!='' && $sitodate!=''){ ?>
			var dateSearch = "<?=$_GET['sitimeframe'];?>|<?=date("m/d/Y",strtotime($sifromdate));?>|<?=date("m/d/Y",strtotime($sitodate));?>";
			<?php if($viewattr=="cuser"){?>
			var actionStrAppend = "&cDateSearch="+dateSearch;
			<?php } if($viewattr=="muser"){?>
			var actionStrAppend = "&mDateSearch="+dateSearch;
			<?php } if($viewattr=="start_date"){?>
			var actionStrAppend = "&sDateSearch="+dateSearch;
			<?php } if($viewattr=="end_date"){?>
			var actionStrAppend = "&eDateSearch="+dateSearch;
			<?php } if($viewattr=="due_date"){?>
			var actionStrAppend = "&dDateSearch="+dateSearch;
			<?php }?>
		<?php }?>
		
		<?php  if($viewattr=="zip"){ ?>
			//var actionStrAppend = '';	
			var frm = document.frm_zip;
			if(frm.radiuszip.value!='' && frm.zipmiles.value!='')
			{
				var savezipCODE = frm.radiuszip.value+'|'+frm.zipmiles.value;
				actionStrAppend = '&savezipcode='+savezipCODE;
			}
		<?php }
		if($viewattr=="sub_status"){ ?>
			var frm = document.frm_sub_status;
			if(frm.sitffromdate.value!='' && frm.sitftodate.value!='')
			{
				var savesubStatus = frm.sitffromdate.value+'|'+frm.sitftodate.value;
				actionStrAppend = '&savesubStatus='+savesubStatus;
			}
		<?php }
		?>		
			if($(this).is(':checked')==true)
			{
				var chkall = "";
				var moduletype = this.name;
				var moduleid = this.value;
				var modulevalue = encodeURIComponent($(this).parent().children('a').text());
				var searchtype = '';
				var selected = $("input[type='radio'][name='nativesearch']:checked");
				if (selected.length > 0) {
					searchtype = selected.val();
				}
				if(document.getElementById('<?php echo $viewattr;?>selecctall').checked==true)
				{
					if(moduletype == 'zip')
					{
						chkall = "&chkall=zipcodeall";
					}
				}
				var modulevalue = encodeURIComponent($(this).parent().children('a').text());				
				$.ajax({
					url : 'savefilters.php?cmdtype=add'+chkall+'&searchtype='+searchtype+'&moduletype='+moduletype+'&moduleid='+moduleid+'&modulevalue='+modulevalue+actionStrAppend,
					dataType:  "jsonp",
					async: false,
					success : function(session)
					{
						$("#breadCrumbNav").html(session.html);
						$("#breadcrumbs").html(session.count);
						var activeclass = $('#breadcrumbs').attr('class');		
						if(activeclass=='acjbvisualsearchFilterSelect')
						{
							$("#breadcrumbs").toggleClass("acjFilterhide");
							$("#acjbvisualsearchFilterConten").toggleClass("filterContent");
						}
					}
				});
			}else if($(this).is(':checked')==false)
			{
				var chkall = "";
				var moduletype = this.name;
				var moduleid = this.value;
				var modulevalue = $(this).parent().children('a').text();
				var searchtype = '';
				var selected = $("input[type='radio'][name='nativesearch']:checked");
				if (selected.length > 0) {
					searchtype = selected.val();
				}
				if(document.getElementById('<?php echo $viewattr;?>selecctall').checked==true)
				{
					if(moduletype == 'zip')
					{
						chkall = "&chkall=zipcodeall";
					}
				}
				$.ajax({
					url : 'savefilters.php?cmdtype=update'+chkall+'&searchtype='+searchtype+'&moduletype='+moduletype+'&moduleid='+moduleid+'&modulevalue='+modulevalue+'&uncheck=1',
					dataType:  "jsonp",
					async: false,
					success : function(session)
					{
						$("#breadCrumbNav").html(session.html);
						$("#breadcrumbs").html(session.count);
						var activeclass = $('#breadcrumbs').attr('class');		
						if(activeclass=='acjbvisualsearchFilterSelect')
						{
							$("#breadcrumbs").toggleClass("acjFilterhide");
							$("#acjbvisualsearchFilterConten").toggleClass("filterContent");
						}
					}
				});
			}
		});
	});
}
function filtersLoading()
{
	var nodata='';
	<?php if($viewattr=="zip"){ 
	$radius = $_GET['radius'.$viewattr];
	$miles = $_GET[$viewattr.'miles'];
	if($radius!='' && $miles!='')
	{
	?>
	if(document.getElementById('nodata')){
		nodata=document.getElementById('nodata').html();
	}
			if(nodata != "undefined"){
				$('#selrow').show();
			}
	<?php	
	}
	}?>
	
	$.ajax({
		url : 'savefilters.php?cmdtype=view',
		dataType:  "jsonp",
		success : function(session)
		{
			$("#breadCrumbNav").html(session.html);
			$("#breadcrumbs").html(session.count);
			if(session.html!='')
			{
				$("#breadcrumbs").toggleClass("acjFilterhide");
				$("#acjbvisualsearchFilterConten").toggleClass("filterContent");
			}
		}
	});
}
function filtersRemove(moduletype,moduleid)
{
	$.ajax({
		url : 'savefilters.php?cmdtype=update&moduletype='+moduletype+'&moduleid='+moduleid,
		dataType:  "jsonp",
		async: false,
		success : function(session)
		{
			$("#breadcrumbs").html(session.count);			
		}
	});
}
$(document).ready(
	
	function() {
	    $("#suggest").autocomplete(
		    {
			source : function(request, response) {
			    $.ajax({
				url : 'visualsearch_searchsuggest.php?viewattr=<?=$_GET['viewattr'];?>&querystring=<?=$_GET['querystring'];?>',
				dataType : 'json',
				data : {
				    term : request.term
				},

				success : function(data) {
				    response($.map(data, function(item) {
					return {
					    label : __highlight(item.label,
						    request.term),
					    value : item.label
					};
				    }));
				}
			    });
			},
			minLength : 3,
			select : function(event, ui) {

			    $('#searchbutton').submit();
			}
		    }).keydown(function(e) {
		if (e.keyCode === 13) {
		    $("#search_form").trigger('submit');
		}
	    }).data("autocomplete")._renderItem = function(ul, item) {

		return $("<li></li>").data("item.autocomplete", item).append(
			$("<a></a>").html(item.label)).appendTo(ul);
	    };
		filtersApply();
		filtersLoading();
	});
$(document).on('click', ".remove", function () {
  var closest = $(this).parent().closest('span[id]').attr('id');
  var spans = $("#"+closest+" span.rcons").size();
  $(this).parent(".rcons").remove();
  if(spans==1)
  {
	$("#"+closest+" b").remove();
	$("#"+closest).next('br').remove();
	$("#"+closest).parent(".deleteAll").remove();
	$("#"+closest).remove();
  }
});
function remFilters(moduletype,moduleid)
{
var chkall = '';
	var zcodeall = moduleid.indexOf("zipcodeall");
	if(zcodeall != -1)
	{
		chkall =  '&chkall=zipcodeall&remall=zip';
	}
	var view_attr ='';
	<?php if($viewattr=="zip"){ ?>
		 view_attr = 'zip';
	
	<?php }
	?>
	$.ajax({
		url : 'savefilters.php?cmdtype=update'+chkall+'&moduletype='+moduletype+'&moduleid='+moduleid,
		dataType:  "jsonp",
		async: false,
		success : function(session)
		{
		if(zcodeall == -1)
		{
			moduleid = moduleid.replace("E|", "");
			moduleid = moduleid.replace("I|", "");
			if(($("input[name='"+moduletype+"']").length > 0)){	
				if($("input[name='"+moduletype+"'][value='"+moduleid+"']").val() == moduleid)
				{
					$("input[name='"+moduletype+"'][value='"+moduleid+"']").prop('checked',false);
				}
					}
			}
			else
			{
				moduleid = "";
				if(moduletype == view_attr){
					document.getElementById(moduletype+'selecctall').checked=false;
					$("input[name='"+moduletype+"']").each(function() {
						this.checked = false;
					});
				}
			}
			$("#breadcrumbs").html(session.count);
				if(moduletype=='zip')
				{
					if(moduletype=='zip')
					{
						var checked = $("#frm_zip input:checkbox:checked").length;				
						if(checked>=1)
						{
							$('.zip_box').show();							
						}else
						{
							$('.zip_box').show();
						}
					}
					
				}		
		}
	});
}
$(document).on('click', ".removeAll", function () {
	
  var closest = $(this).parent().closest('span[id]').attr('id');
  if(closest)
  {
	$(this).parent(".deleteAll").next('br').remove();
	$(this).parent(".deleteAll").remove();
	$(this).remove();
  }
  
});
function remAllFilters(filterType)
{
	
	if(filterType == 'zip')
	{
		parent.document.getElementById('savezipCODE').value='';
	}
	var view_attr ='';
	<?php if($viewattr=="zip"){ ?>
		 view_attr = 'zip';
	
	<?php }
	?>
	
	$.ajax({
		url : 'savefilters.php?cmdtype=delete&moduletype='+filterType,
		dataType:  "jsonp",
		async: false,
		success : function(session)
		{
			$("input#SpeedSearchString").val(session.searchqstr);
			if(filterType == view_attr){
				if(document.getElementById('<?php echo $viewattr;?>selecctall')){
					document.getElementById('<?php echo $viewattr;?>selecctall').checked=false;
				}
				
			}
			$("input[name='"+filterType+"']").each(function() {
					this.checked = false;
				});
			$("#breadcrumbs").html(session.count);
					
			
		}
	});
}
</script>
<?php
if($viewattr=="zip" ){
	
		?>
		<script>
		$('.<?php echo $viewattr;?>_box').show();
		</script>
		<?php
	
}
?>
</body>
</html>