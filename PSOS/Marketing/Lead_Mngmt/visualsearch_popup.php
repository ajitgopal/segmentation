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
	require("visualsearch_con_setup.php");
	
	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");

	if($_GET['viewattr']=='')
	{
		if($_GET['querystring']!=''){
			unset($_SESSION["SPHINX_Contacts"]);
		}
		//unset($_SESSION["SPHINX_Contacts"]);
		$_GET['viewattr'] = key($SPHINX_CONF['sphinx_attributes']);
	}
	if($_REQUEST['reset'] == 1){
		unset($_SESSION["SPHINX_Contacts"]);
		unset($_SESSION['SPHINX_Contacts_sub']);
	}
	$viewattr = $_GET['viewattr'];
	if(preg_match('/^(["\']).*\1$/m', $_GET['q'])) $_GET['q'] = "=".$_GET['q'];

	if(!empty($_SESSION["SPHINX_Contacts"]))
	{
		$searchstr = '';
		foreach($_SESSION['SPHINX_Contacts'] as $filtertype=>$filtername){	
			if(count($_SESSION['SPHINX_Contacts'][$filtertype])>0)
			{
				$searchstr .= "&".$filtertype."=";
				foreach($_SESSION['SPHINX_Contacts'][$filtertype] as $filters_id=>$filters_val)
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
	
if($_GET['areacode_vals'])
	$oldurlString =  rm_url_param('areacode_vals',$oldurlString);
	
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Contacts Attribute Search</title>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.modalbox.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.jscrollpane.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.columnizer.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.mousewheel.js"></script>
<script type="text/javascript" language="javascript" src="scripts/calendar.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/autocomplete/js/jquery-ui.min.js"></script>
<script type="text/javascript" language="javascript" src="/BSOS/scripts/sphinx/jquery.cookie.js"></script>
<link rel="stylesheet" type="text/css" href="/BSOS/css/preloader.css" />
<link rel="stylesheet" type="text/css" href="/BSOS/css/calendar.css">
<link rel="stylesheet" type="text/css" href="/BSOS/scripts/sphinx/autocomplete/css/jquery-ui-1.8.21.custom.css">
<link rel="stylesheet" type="text/css" href="/BSOS/css/sphinx_modalbox.css" />
<script type="text/javascript">
$(document).ready(function(){
	$("#acjbvisualsearchFilterConten").addClass("filterContenthide");
		 $('#breadcrumbs').click(function(e) {  
			$("#breadcrumbs").toggleClass("acjFilterhide");
			$("#acjbvisualsearchFilterConten").toggleClass("filterContent");			
		});
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

$(function() {
		
	$("#attr-list ul").columnizer({ count: 3});
	$("#attr-list .scroll-area").jScrollPane();
	$("#frm_<?=$viewattr;?>").submit(function(event){		
		<?php 
		if($viewattr=="zip" || $viewattr=="areacode")
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
					<?php 
					if($viewattr=="areacode"){
					?>
						if(document.frm_<?=$viewattr;?>.hareacode.checked==false && document.frm_<?=$viewattr;?>.wareacode.checked==false && document.frm_<?=$viewattr;?>.mareacode.checked==false)
						{
							alert("Please select at least one Areacode type");
							return false;
						}
					<?php }?>
					document.frm_<?=$viewattr;?>.submit();
					return true;
				}else
				{
					<?php  if($_GET['radiusareacode']=='' && $_GET['areacodemiles']==''){ ?>
						$("input#radiusareacode").val('');
						$("input#areacodemiles").val('');
					<?php }?>
					<?php if($_GET['radiuszip']=='' && $_GET['zipmiles']==''){ ?>
					$("input#radiuszip").val('');
					$("input#zipmiles").val('');
					<?php }?>
					
					<?php if($viewattr=="areacode"){ ?>
					var frm = document.frm_areacode;
					var actionStrAppend = '';
					if(frm.radiusareacode.value!='' && frm.areacodemiles.value!='')
					{
						
						var saveareaCODE = frm.radiusareacode.value+'|'+frm.areacodemiles.value;
							if(frm.hareacode.checked==true)
								saveareaCODE += '|hareacode';
							if(frm.wareacode.checked==true)
								saveareaCODE += '|wareacode';
							if(frm.mareacode.checked==true)
								saveareaCODE += '|mareacode';
								
							if(frm.<?=$viewattr;?>selecctall.checked == true){
								saveareaCODE += '|areacodeall';
							}
						parent.top.$("input#saveareacodePSM").val(saveareaCODE);
						 actionStrAppend = '&psm='+saveareaCODE;
					}else
					{
						parent.top.$("input#saveareacodePSM").val('');
					}
					
					<?php } if($viewattr=="zip"){ ?>
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
					
					<?php }?>
				}
		<?php 
			}else if($viewattr=="cuser" || $viewattr=="muser")
			{ ?>
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
						<?php } ?>
					}
			<?php }?>
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
					parent.top.$("input#saveareacodePSM").val(session.saveareacodePSM);								
				}
			});
			parent.iframeLoader.init();
			parent.top.doGridSearch('search');
			parent.top.getVsearchLeft('search');
			parent.top.modalBoxClose();
			return true;		
	});

			$("input:checkbox[name=zip]").click(function(event){
				var checked = $("#frm_zip input:checkbox:checked").length;				
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
		$("input:checkbox[name=areacode]").click(function(event){
			var checked = $("#frm_areacode input[name=areacode]:checked").length;
			if(checked>=1)
			{
				//$('.areacode_box').hide();
				<?php  if($_GET['radiusareacode']=='' && $_GET['areacodemiles']==''){ ?>
				$("input#radiusareacode").val('');
				$("input#areacodemiles").val('');
				<?php }?>
			}else
			{
				//$('.areacode_box').show();
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
		<?php
		if((isset($_SESSION['SPHINX_Contacts_sub']['chkZipAll']) && $_SESSION['SPHINX_Contacts_sub']['chkZipAll'] !='') || (isset($_SESSION['SPHINX_Contacts_sub']['chkAreacodeAll'])) )
			{
				if(isset($_SESSION['SPHINX_Contacts_sub']['chkZipAll']) && $viewattr == 'zip')
				{
			?>
				document.getElementById('selrow').style.display='block';
				
				document.getElementById('zipselecctall').checked=true;
			<?php }
			if(isset($_SESSION['SPHINX_Contacts_sub']['chkAreacodeAll']) && $viewattr == 'areacode'){?>
					document.getElementById('selrow').style.display='block';
					document.getElementById('areacodeselecctall').checked=true;
				<?php }
		}
		if(isset($_GET['querystring']) && isset($_GET['query']) && $_GET['viewattr'] == 'ytitle')
			{ ?>
					document.getElementById('selrow').style.display='block';
					document.getElementById('maxSel20Alert').style.display='block';
			<?php
			}
		?>		
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
	var actionStrAppend = '';
	var selected = $("input[type='radio'][name='nativesearch']:checked");
	if (selected.length > 0) {
		searchtype = selected.val();
	}
	//alert(document.getElementById('selecctall').checked);
	if(document.getElementById('<?php echo $viewattr;?>selecctall').checked==true)
	{
		//alert($("input[name='zip']").size());
		<?php if($viewattr=="areacode"){ ?>
		var frm = document.frm_areacode;
		if(frm.radiusareacode.value!='' && frm.areacodemiles.value!='')
		{
			var saveareaCODE = frm.radiusareacode.value+'|'+frm.areacodemiles.value;
				if(frm.hareacode.checked==true)
					saveareaCODE += '|hareacode';
				if(frm.wareacode.checked==true)
					saveareaCODE += '|wareacode';
				if(frm.mareacode.checked==true)
					saveareaCODE += '|mareacode';			
			 actionStrAppend = '&saveareacode='+saveareaCODE;
		}
		<?php }  if($viewattr=="zip"){ ?>
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
			if(moduletype=='ytitle')
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
		if(moduletype == 'areacode')
		{
			chkall =  '&chkall=areacodeall';
		}
		if(moduletype == 'ytitle')
		{
			chkall =  '&chkall=ytitleall';
		}
		//alert('savefilters.php?cmdtype=add&chkall=zipcodeall&searchtype='+searchtype+'&moduletype='+moduletype+'&moduleid='+moduleid+'&modulevalue='+modulevalue+actionStrAppend);
		$.ajax({
			url : 'savefilters.php?cmdtype=add'+chkall+'&searchtype='+searchtype+'&moduletype='+moduletype+'&moduleid='+moduleid+'&modulevalue='+modulevalue+actionStrAppend,
			type : 'post',
			dataType:  "jsonp",
			async: false,
			success : function(session)
			{
				//alert(session.html);
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
		if(moduletype == 'areacode')
		{
			chkall =  '&chkall=areacodeall';
		}
		if(moduletype == 'ytitle')
		{
			chkall =  '&chkall=ytitleall';
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
	var radiuszip = document.getElementById('radius<?php echo $viewattr;?>').value;
	var zipmiles = document.getElementById('<?php echo $viewattr;?>miles').value;
	
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
        if (e[i].checked == true && radiuszip!='' && zipmiles!='')
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
         <span class="visualNewSearch" onclick="return document.getElementById('search_form').submit();"><i class="fa fa-search fa-lg" type="submit" id="send" name="send" align="absmiddle" value="Search"  title="Search"></i></span>
          <span class="visualNewSearch" onclick="return window.location.href='visualsearch_popup.php?viewattr=<?=$viewattr;?>&reset=1&querystring=<?=$_GET[querystring];?>';"><a href="visualsearch_popup.php?viewattr=<?=$viewattr;?>&reset=1&querystring=<?=$_GET['querystring'];?>" class="<?=$cssClas?>"><i class="fa fa-reply fa-lg" title="Reset"></i><!--<img src="/BSOS/images/reset.gif" border="0" title="Reset" align="absmiddle"/>--></a></span>
        </form></td>
    </tr>    
    <tr>
      <td class="VisualNewSortListHedBg" colspan="2"></td>
    </tr>
   
    <tr>
    
    <td colspan="2">
    
    <form method="get" id="frm_<?=$viewattr?>" name="frm_<?=$viewattr?>">
    
    <input type="hidden" name="viewattr" value="<?=$viewattr;?>" >
    <input type="hidden" name="querystring" value="<?=$_GET['querystring'];?>" >
    <table width=100% cellpadding=1 cellspacing=1 border=0>
	 <tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="radio" name="nativesearch" value="I" checked="checked" >Include in Search &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="nativesearch" value="E">Exclude in Search &nbsp;</td><td align="right" style="padding-right:30px; display:none;" id="selrow"><div style="color:red;display:none;float:right;padding-top:4px;" id="maxSel20Alert">&nbsp;(* Limit 20)</div><div style="float:right;"><input type="checkbox" name="<?php echo $viewattr;?>selecctall" id="<?php echo $viewattr;?>selecctall" value="1"  onclick="javascript:selectAll();">Select/Deselect All</div></td></tr>
      <tr>
        <td colspan="2"><?php include_once("visualsearch_popupfilters.php");?></td>
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
		if(isset($_SESSION['SPHINX_Contacts_sub']['savezipCODE']) && !empty($_SESSION['SPHINX_Contacts_sub']['savezipCODE']))
		{
			$zipcodesearch = $_SESSION['SPHINX_Contacts_sub']['savezipCODE'];
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
		if(isset($_SESSION['SPHINX_Contacts_sub']['saveareacodePSM']) && !empty($_SESSION['SPHINX_Contacts_sub']['saveareacodePSM']))
		{
			$areacodesearch = $_SESSION['SPHINX_Contacts_sub']['saveareacodePSM'];
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
          miles
          <?php if($viewattr=="areacode"){
	if($_GET['radiusareacode']!=''){
		if($_GET['hareacode']=='hareacode') $hchecked = 'checked="checked"'; else $hchecked = '';
		if($_GET['wareacode']=='wareacode') $wchecked = 'checked="checked"'; else $wchecked = '';
		if($_GET['mareacode']=='mareacode') $mchecked = 'checked="checked"'; else $mchecked = '';
	}else
	{
		$hchecked = 'checked="checked"';
		$wchecked = 'checked="checked"';
		$mchecked = 'checked="checked"';
	}
	?>
          <br/>
          <input type="checkbox" value="hareacode" name="hareacode" id="hareacode" <?=$hchecked;?> >
          Primary Areacode
          <input type="checkbox" value="wareacode" name="wareacode" id="wareacode" <?=$wchecked;?> >
          Secondary Areacode
          <input type="checkbox" value="mareacode" name="mareacode" id="mareacode" <?=$mchecked;?> >
          Mobile Areacode
          <?php }?>
          </span></td>
      </tr>
    </table>
	</div>
    <?php }?>
	<?php if($viewattr=="cuser"){
		if(isset($_SESSION['SPHINX_Contacts_sub']['cDateSearch']) && !empty($_SESSION['SPHINX_Contacts_sub']['cDateSearch']))
		{
			$cdatesearch = $_SESSION['SPHINX_Contacts_sub']['cDateSearch'];
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
			if(isset($_SESSION['SPHINX_Contacts_sub']['mDateSearch']) && !empty($_SESSION['SPHINX_Contacts_sub']['mDateSearch']))
			{
				$mdatesearch = $_SESSION['SPHINX_Contacts_sub']['mDateSearch'];
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
<script type="text/javascript">
<?php
if($_GET['reset'] == 1){
	?>
	parent.top.document.getElementById('savezipCODE').value='';
	parent.top.document.getElementById('saveareacodePSM').value='';
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
		<?php if($fromdate!='' && $todate!=''){ ?>
			var dateSearch = "<?=$_GET['timeframe'];?>|<?=date("m/d/Y",strtotime($fromdate));?>|<?=date("m/d/Y",strtotime($todate));?>";
			<?php if($viewattr=="cuser"){?>
			var actionStrAppend = "&cDateSearch="+dateSearch;
			<?php } if($viewattr=="muser"){?>
			var actionStrAppend = "&mDateSearch="+dateSearch;
			<?php }?>
		<?php }else{?>
			var actionStrAppend = '';
		<?php }?>
		<?php if($viewattr=="areacode"){ ?>
		var frm = document.frm_areacode;
		var actionStrAppend = '';
		if(frm.radiusareacode.value!='' && frm.areacodemiles.value!='')
		{
			
			var saveareaCODE = frm.radiusareacode.value+'|'+frm.areacodemiles.value;
				if(frm.hareacode.checked==true)
					saveareaCODE += '|hareacode';
				if(frm.wareacode.checked==true)
					saveareaCODE += '|wareacode';
				if(frm.mareacode.checked==true)
					saveareaCODE += '|mareacode';			
			 actionStrAppend = '&saveareacode='+saveareaCODE;
		}

		<?php } if($viewattr=="zip"){ ?>
			var frm = document.frm_zip;
			if(frm.radiuszip.value!='' && frm.zipmiles.value!='')
			{
				var savezipCODE = frm.radiuszip.value+'|'+frm.zipmiles.value;
				actionStrAppend = '&savezipcode='+savezipCODE;
			}

		<?php }?>		
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
					if(moduletype == 'areacode')
					{
						chkall = "&chkall=areacodeall";
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
					if(moduletype == 'areacode')
					{
						chkall = "&chkall=areacodeall";
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
	<?php if($viewattr=="zip"|| $viewattr=="areacode"){ 
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
	var acodeall = moduleid.indexOf("areacodeall");
	if(zcodeall != -1)
	{
		chkall =  '&chkall=zipcodeall&remall=zip';
	}
	if(acodeall != -1)
	{
		chkall =  '&chkall=areacodeall&remall=areacode';
	}
	var view_attr ='';
	<?php if($viewattr=="zip"){ ?>
		 view_attr = 'zip';
	
	<?php }else if($viewattr=="areacode"){ ?>
		 view_attr = 'areacode';
	<?php }
	?>
	$.ajax({
		url : 'savefilters.php?cmdtype=update'+chkall+'&moduletype='+moduletype+'&moduleid='+moduleid,
		dataType:  "jsonp",
		async: false,
		success : function(session)
		{
		
			if(zcodeall == -1&& acodeall == -1)
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
				if(moduletype=='zip' || moduletype=="areacode")
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
					if(moduletype=='areacode')
					{
						var checked = $("#frm_areacode input:checkbox:checked").length;
						if(checked>=1)
						{
							$('.areacode_box').show();							
						}else
						{
							$('.areacode_box').show();
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
	if(filterType == 'areacode')
	{
		parent.document.getElementById('saveareacodePSM').value='';
	}
	var view_attr ='';
	<?php if($viewattr=="zip"){ ?>
		 view_attr = 'zip';
	
	<?php }else if($viewattr=="areacode"){ ?>
		 view_attr = 'areacode';
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
if($viewattr=="zip" || $viewattr=="areacode"){
	
		?>
		<script>
		$('.<?php echo $viewattr;?>_box').show();
		</script>
		<?php
	
}
?>
</body>
</html>