<?php
	require("global.inc");
	require("dispfunc.php");

	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'FO'");
	
	$whrDeptAccess = " AND jo.deptid IN (".$deptAccesSno.") ";
	
 	if(isset($_GET['id']))
	{
		$letter = $_GET['id'];
	}
	if(isset($_GET['search']))
	{
		$search = $_GET['search'];
	}
	
	if(isset($_GET['candrn']))
	{
		$candrn = $_GET['candrn'];
	}
	
	if(isset($_GET['mode']))
	{
		$mode = $_GET['mode'];
	}	
	
	if(isset($_GET['roles']))
	{
		$roles = $_GET['roles'];
	}
	
	if(isset($_GET['cmg_frm']))
	{
		$cmg_frm = $_GET['cmg_frm'];
	}
	
	//chking candidate id for submitted job order
	$Cand_Val=substr($cand_id,4);
	 
	function displaydata($pos_id,$pos_tit,$pos_loc_sno,$pos_comp_sno,$pos_cname,$pos_addr1,$pos_addr2,$pos_city,$pos_state)
	{
	        $disp_data = "";
		$disp_data = "".$pos_tit."";
		if($pos_cname != "")
			$disp_data .= " (".$pos_cname.") ";
		if($pos_addr1 != "")
			$disp_data .= " ".$pos_addr1;
		
		if($pos_addr2 != "")
			$disp_data .= " ".$pos_addr2; 
		
		if($pos_city != "")
			$disp_data .= ", ".$pos_city;
		
		if($pos_state != "")
			$disp_data .= ", ".$pos_state;
			
			if($pos_addr1 != "")
				$disp_data .= " ".$pos_addr1;
			
			if($pos_addr2 != "")
				$disp_data .= " ".$pos_addr2; 
			
			if($pos_city != "")
				$disp_data .= ", ".$pos_city;
			
			if($pos_state != "")
				$disp_data .= ", ".$pos_state;
				
			return $disp_data;
	}

	if($chk_link=="getjoborderdetails")
	{
	$cand_resume_status_info = "select rs.req_id,GROUP_CONCAT(DISTINCT(rs.shift_id)) as shift_snos
						from posdesc as jo 
						LEFT JOIN resume_status as rs ON(jo.posid = rs.req_id)
						LEFT JOIN manage as mg ON(mg.sno = rs.status)
						where rs.res_id = '".$Cand_Val."' AND jo.shift_type !='perdiem' AND
						((mg.name NOT IN ('Closed','Cancelled')) OR (jo.postype in (select sno from manage where manage.name in('Direct','Internal Direct')))) group by rs.req_id";
			$cand_resume_res = mysql_query($cand_resume_status_info,$db);
			$num_rows = mysql_num_rows($cand_resume_res);
			$jo_shifts_filled = array();
			$jo_filled = "''";
			if($num_rows > 0)
			{
				while($resume_data = mysql_fetch_row($cand_resume_res))
			  {
				$shifts_on_job = "select count(DISTINCT(sm_sno)) from posdesc_sm_timeslots where pid = '".$resume_data[0]."'";
				$shifts_on_job_res =mysql_query($shifts_on_job,$db);
				$jo_shift_count  = mysql_fetch_row($shifts_on_job_res);
				
				$shift_sno_arr = explode(',',$resume_data[1]);
				if($jo_shift_count[0] > 0)
				{  
					$shift_sno_arr	= array_diff($shift_sno_arr, array(0)); 
					if($jo_shift_count[0] == count($shift_sno_arr) ){
						$jo_shifts_filled[] = $resume_data[0];
					}
				}else{
					$jo_shifts_filled[] = $resume_data[0];
				}
			}
		  
			if(!empty($jo_shifts_filled)){
				$jo_filled = implode(',',$jo_shifts_filled);
			}		  
		} 
	}
	
?>  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>

<style>
.mouseovercont{   
    text-decoration:underline;
    color: #1d89cf;
    font-family:Arial;
    font-size:12px;
    cursor: pointer;
}

.mouseoutcont{    
    text-decoration:none;
    color: #474c4f;
    font-family:Arial;
    font-size:12px;
}
</style>

<script type="text/javascript">
<!--
//Disable right mouse click Script

var message="Function Disabled!";

///////////////////////////////////
function clickIE4(){
	if (event.button==2){
		alert(message);
		return false;
	}
}

function clickNS4(e){
	if (document.layers||document.getElementById&&!document.all){
		if (e.which==2 || e.which==3){
			alert(message);
			return false;
		}
	}
}

if (document.layers){
	document.captureEvents(Event.MOUSEDOWN);
	document.onmousedown=clickNS4;
}
else if (document.all&&!document.getElementById){
	document.onmousedown=clickIE4;
}

document.oncontextmenu=new Function("return false")

function company(str)
{
	id = "com"+str;
	if(document.getElementById(id))
	{
		document.getElementById(id).className="mouseovercont";
	}
}

function company_out(str)
{
	id = "com"+str;
	if(document.getElementById(id))
	{
		document.getElementById(id).className = 'mouseoutcont';
	}
}


function win(posid,cand_id)
{
	if(parent.window.opener.location.href.indexOf("/include/Upload.php")>=0)
	{
		getattachDetails(posid)
	}
	else if(parent.window.opener.location.href.indexOf("/BSOS/Marketing/Candidates/Candidates.php")>=0)
	{
		window.parent.displayShiftsModal(cand_id, posid, '','Shortlist');
	}
	else if(parent.window.opener.location.href.indexOf("/Groups/crmgroups.php")>=0)
	{
		window.parent.displayShiftsModal(cand_id, posid, '','Shortlist');
	}
	else
	{
		parent.window.opener.submission_popup(posid,'');
		parent.close();
	}
} 


function getattachDetails(str1)
{
	var v_width  = 550;
	var v_heigth = 400;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;

	top.remoteattachfiles=window.open("../../../../include/attachmentscreen.php?cand_id="+str1+"&candjotype=joborder","attachment_files","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,top.dependent=yes,resizable=yes");	
	top.remoteattachfiles.focus();
}
function cand_docs_attach(str,prodoc,resumename,candid)
{
	var toList   = parent.window.opener.document.attachment.cbFiles;
	var cbf = parent.window.opener.document.getElementById('cbFiles');
	var mainselbox = parent.window.opener.document.getElementById('cbFiles');
	var chk_jobdocs=parent.window.opener.document.attachment.jorderdocs.value;
	var chk_joprof=parent.window.opener.document.attachment.jorderprofile.value;
	var mainselen= mainselbox.length;
	
	var len = toList.length;
	var str_split=str.split("|^");
	var spl_len = str_split.length;
	var chkval="";
	
	if(str_split[0] != '')
	{
		for(i=0;i<spl_len;i++)
		{
			var canddetail=str_split[i].split("|-");
			for(j=0;j<mainselen;j++)
			{
				if(mainselbox.options[j].text == canddetail[1] && mainselbox.options[j].value == canddetail[0])
				var chksel="sel";
			} 
		  
			if(chksel!="sel")
			{
				var opt=parent.window.opener.document.createElement('option');
				parent.window.opener.document.getElementById('cbFiles').options.add(opt);
				opt.text=canddetail[1];
				opt.value=canddetail[0];
				opt.title=canddetail[1];
			       
				if(chk_jobdocs=="")
					chk_jobdocs = canddetail[0]+"|-"+canddetail[1];
				else
					chk_jobdocs += "|jorderdocs^"+canddetail[0]+"|-"+canddetail[1];
				
				parent.window.opener.document.attachment.jorderdocs.value=chk_jobdocs;
			}		  
		}
	}
	if(prodoc)
	{
		var prodoc1 = prodoc.substring(0,3);
		if(prodoc1 == 'yes')
		{
			proname1 = prodoc.split("|");
			proname = proname1[1]+".html";
			var mainselbox = parent.window.opener.document.getElementById('cbFiles');
			var mainselen= mainselbox.length;
			for(i=0;i<mainselen;i++)
			{
				if(mainselbox.options[i].text == proname && mainselbox.options[i].value == candid)
					var chksel="prof";
			} 
			
			if(chksel != "prof")
			{
				var opt=parent.window.opener.document.createElement('option');
				parent.window.opener.document.getElementById('cbFiles').options.add(opt);
				opt.text=proname;
				opt.value=candid;
				opt.title=proname;
				if(chk_joprof=="")
				chk_joprof=candid+"|-"+proname;
				else
				chk_joprof += "|joborderprofile^"+candid+"|-"+proname;
				parent.window.opener.document.attachment.jorderprofile.value=chk_joprof;
			}	
		}
	}
	parent.close();
}
</script>
</head>
<body>
<?php 
	if($search != "")//For search based on enter data  in textbox 
	{
		$q=1;
		if($by=='bycompany')//For company
		{
			$order_chk = "comp.cname,jo.postitle";
			$like_chk = "REPLACE(comp.cname, ' ', '')  LIKE REPLACE('%".$search."%', ' ', '')";
			$ser_cond = "jo.company = comp.sno";
		}
		else if($by=='bytitle')//for job title
		{
			$order_chk = "jo.postitle,comp.cname";
			$like_chk = "REPLACE(jo.postitle, ' ', '')  LIKE REPLACE('%".$search."%', ' ', '')";
			$ser_cond = "jo.company = comp.sno";
		}
		else //for job location
		{
			$order_chk = "comp.cname";
			$like_chk = "(REPLACE(comp.cname, ' ', '')  LIKE REPLACE('%".$search."%', ' ', '') or REPLACE(joloc.city, ' ', '')  LIKE REPLACE('%".$search."%', ' ', '') or REPLACE(joloc.state, ' ', '')  LIKE REPLACE('%".$search."%', ' ', '') OR REPLACE(CONCAT(REPLACE(joloc.city, ' ', ''),REPLACE(joloc.state, ' ', '')), ' ', '') LIKE REPLACE('%".$search."%', ' ', '') OR REPLACE(CONCAT(REPLACE(joloc.state, ' ', ''),REPLACE(joloc.city, ' ', '')), ' ', '') LIKE REPLACE('%".$search."%', ' ', ''))";
			$ser_cond = "jo.company = comp.sno";
		}

			
		
		
		
?>
	<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?
			if($chk_link=="getjoborderdetails")
			{
				$que =  "select
						   jo.posid,
						   jo.postitle,
						   jo.location,
						   jo.company,
						   comp.cname,
						   comp.address1,
						   comp.address2,
						   comp.city,
						   comp.state,
						joloc.address1,
						joloc.address2,
						joloc.city,
						joloc.state,
						jo.shiftid
					       from manage ma,posdesc jo LEFT JOIN staffoppr_cinfo comp on  ".$ser_cond."
					       LEFT JOIN staffoppr_location joloc ON joloc.sno = jo.location 
					       where (jo.owner='".$username."' or FIND_IN_SET('".$username."',jo.accessto)>0  or jo.accessto='all') ".$whrDeptAccess." AND jo.posstatus = ma.sno AND ma.name $not_display_joborders_cond AND ma.type='jostatus' and jo.status in ('approve','Accepted') and jo.posid not in (".$jo_filled.") and ".$like_chk." order by ".$order_chk;
			}
			else
			{
				$que =  "select
					   jo.posid,
					   jo.postitle,
					   jo.location,
					   jo.company,
					   comp.cname,
					   comp.address1,
					   comp.address2,
					   comp.city,
					   comp.state,
					   joloc.address1,
					joloc.address2,
					joloc.city,
					joloc.state,
					jo.shiftid
				       from manage ma,posdesc jo LEFT JOIN staffoppr_cinfo comp on ".$ser_cond."
				       LEFT JOIN staffoppr_location joloc ON joloc.sno = jo.location
				       where (jo.owner='".$username."' or FIND_IN_SET('".$username."',jo.accessto)>0  or jo.accessto='all') ".$whrDeptAccess." AND jo.posstatus = ma.sno AND ma.name $not_display_joborders_cond AND ma.type='jostatus' and jo.status in ('approve','Accepted') and ".$like_chk."  order by ".$order_chk;
			}
		 
		$que_res = mysql_query($que,$db);
		$num_rows = mysql_num_rows($que_res);
		if($num_rows > 0)
		{
			while($que_row = mysql_fetch_row($que_res))
			{
				$pos_id = $que_row[0];
				$pos_tit = $que_row[1];
				$pos_loc_sno = $que_row[2];
				$pos_comp_sno = $que_row[3];
				$pos_cname = $que_row[4];
				$pos_addr1 = $que_row[5];
				$pos_addr2 = $que_row[6];
				$pos_city = $que_row[7];
				$pos_state = $que_row[8];
				
				$jopos_addr1 = $que_row[9];
				$jopos_addr2 = $que_row[10];
				$jopos_city = $que_row[11];
				$jopos_state = $que_row[12];
				$posdesc_shift_id = $que_row[13];
				$disp_data = displaydata($pos_id,$pos_tit,$pos_loc_sno,$pos_comp_sno,$pos_cname,$jopos_addr1,$jopos_addr2,$jopos_city,$jopos_state);		     
			?> 
			<TR  onmouseover="company('<?php echo $q;?>')" onMouseOut="company_out(<?php echo $q;?>)" onMouseDown="company_out('<?php echo $q;?>')" id=com<?php echo $q;?> class="mouseoutcont">
			<?php if($mode!='shortlist' && $roles!='no' && $cmg_frm!='email'){ ?>
				<td colspan="5" height="22" id="posdesc_shiftid_<?=$pos_id;?>" onclick="win_roles('<?=$Cand_Val;?>', '<?=$pos_id;?>','<?=$candrn;?>','<?=$hidemodule;?>');" data_shiftid="<?php echo $posdesc_shift_id;?>" ><? echo str_replace("\\","",$disp_data); ?></td>
				<?php }else{ ?>
				<td colspan="5" height="22" id="posdesc_shiftid_<?=$pos_id;?>" onclick="win('<?=$pos_id;?>','<?=$cand_id;?>')" data_shiftid="<?php echo $posdesc_shift_id;?>" ><? echo str_replace("\\","",$disp_data); ?></td>
			<?php } ?>
			</tr>
			<TR nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<? 
			$q++;
			}//while
		} //if	
		else
		{ 
		?>
			<TR class="mouseoutcont">
				<td colspan="5" height="22" align="center">Search results not found.</td>
			</tr>
		<?
		}
		?>	 	
	</table>
	<?
	}
	else
	{
		$q=1;
		if($letter == "")
			$letter = 'a';
		  
		$order_chk = "comp.cname,jo.postitle";
		$like_chk = "jo.postitle"; 
		
		if($letter == "others")//For others search
		{
		?>
			<table border="0" cellpadding="1" cellspacing="1" width="100%" >
		<?
		for($i=0 ; $i<10;$i++)
		{
			if($chk_link=="getjoborderdetails")
			{
				$que =  "select
						jo.posid,
						jo.postitle,
						jo.location,
						jo.company,
						comp.cname,
						comp.address1,
						comp.address2,
						comp.city,
						comp.state,
						joloc.address1,
						joloc.address2,
						joloc.city,
						joloc.state
						from manage ma,posdesc jo LEFT JOIN staffoppr_cinfo comp on jo.company = comp.sno
						LEFT JOIN staffoppr_location joloc ON joloc.sno = jo.location
						where (jo.owner='".$username."' or FIND_IN_SET('".$username."',jo.accessto)>0  or jo.accessto='all') ".$whrDeptAccess." AND jo.posstatus = ma.sno AND ma.name $not_display_joborders_cond AND ma.type='jostatus' and jo.status in ('approve','Accepted') and jo.posid not in (".$jo_filled.") and ".$like_chk." LIKE '".$i."%' order by ".$order_chk;
			}
			else
			{
				 $que =  "select
						jo.posid,
						jo.postitle,
						jo.location,
						jo.company,
						comp.cname,
						comp.address1,
						comp.address2,
						comp.city,
						comp.state,
						joloc.address1,
						joloc.address2,
						joloc.city,
						joloc.state
					from manage ma,posdesc jo LEFT JOIN staffoppr_cinfo comp on jo.company = comp.sno
					LEFT JOIN staffoppr_location joloc ON joloc.sno = jo.location
					where (jo.owner='".$username."' or FIND_IN_SET('".$username."',jo.accessto)>0  or jo.accessto='all') ".$whrDeptAccess." AND jo.posstatus = ma.sno AND ma.name $not_display_joborders_cond AND ma.type='jostatus' and jo.status in ('approve','Accepted') and ".$like_chk." LIKE '".$i."%' order by ".$order_chk;			  
			}
			$que_res = mysql_query($que,$db);
			$num_rows = mysql_num_rows($que_res);
			while($que_row = mysql_fetch_row($que_res))
			{
				$test = $q;
				$pos_id = $que_row[0];
				$pos_tit = $que_row[1];
				$pos_loc_sno = $que_row[2];
				$pos_comp_sno = $que_row[3];
				$pos_cname = $que_row[4];
				$pos_addr1 = $que_row[5];
				$pos_addr2 = $que_row[6];
				$pos_city = $que_row[7];
				$pos_state = $que_row[8];
				
				$jopos_addr1 = $que_row[9];
				$jopos_addr2 = $que_row[10];
				$jopos_city = $que_row[11];
				$jopos_state = $que_row[12];
				$posdesc_shift_id = $que_row[13];	  
				$disp_data = displaydata($pos_id,$pos_tit,$pos_loc_sno,$pos_comp_sno,$pos_cname,$jopos_addr1,$jopos_addr2,$jopos_city,$jopos_state);
			?>
                  
			<tr  onmouseover="company('<?php echo $q;?>')" onMouseOut="company_out(<?php echo $q;?>)" onMouseDown="company_out('<?php echo $q;?>')" id=com<?php echo $q;?> class="mouseoutcont">
			<?php if($mode!='shortlist' && $roles!='no' && $cmg_frm!='email'){ ?>
				<td colspan="5" height="22" id="posdesc_shiftid_<?=$pos_id;?>" onclick="win_roles('<?=$Cand_Val;?>', '<?=$pos_id;?>','<?=$candrn;?>','<?=$hidemodule;?>');" data_shiftid="<?php echo $posdesc_shift_id;?>" ><? echo str_replace("\\","",$disp_data); ?></td>
			<?php }else{ ?>
				<td colspan="5" height="22" id="posdesc_shiftid_<?=$pos_id;?>" onclick="win('<?=$pos_id;?>','<?=$cand_id;?>')" data_shiftid="<?php echo $posdesc_shift_id;?>" ><? echo str_replace("\\","",$disp_data);?></td>
			<?php } ?>
			</tr>
			<TR nowrap="nowrap">
				<td colspan="5" bgcolor="#ffffff"></td>
			</tr>
			<?  
			       $q++;
			}//while
		} //for
		?>
		<? if($test == 0)
		{
			?>
			<TR class="mouseoutcont">
				<td colspan="5" height="22" align="center">Results not found.</td>
			</tr>
		<?
		}
		?>	
        </table>
        <?
	} 
	else 
	{ // For all Alphabetical search 
	?>
        <table border="0" cellpadding="1" cellspacing="1" width="100%" >
        <?
		$q=1;
		if($chk_link=="getjoborderdetails")
		{       
                        $order_chk = "comp.cname,jo.postitle";
                        $like_chk = "REPLACE(jo.postitle, ' ', '') LIKE '".$letter."%'" ;
                            
                        if($by=='bycompany')//For company
                        {
				$order_chk = "comp.cname,jo.postitle";
				$like_chk = "REPLACE(comp.cname, ' ', '') LIKE '".$letter."%'" ;
                        }
                        else if($by=='bytitle')//for job title
                        {
				$order_chk = "comp.cname,jo.postitle";
				$like_chk = "REPLACE(jo.postitle, ' ', '') LIKE '".$letter."%'" ; 
                        }
                        else if($by=='byloc') //for job location
                        {
				$order_chk = "comp.cname,jo.postitle";
				$like_chk="(REPLACE(joloc.city, ' ', '') LIKE '".$letter."%' or REPLACE(joloc.state, ' ', '') LIKE '".$letter."%' or REPLACE(CONCAT(REPLACE(joloc.city, ' ', ''),REPLACE(joloc.state, ' ', '')), ' ', '') LIKE '".$letter."%' or REPLACE(CONCAT(REPLACE(joloc.state, ' ', ''),REPLACE(joloc.city, ' ', '')), ' ', '') LIKE '".$letter."%' )" ;

                        }else { ///  by default when page loading
                              
				$order_chk = "comp.cname,jo.postitle";
				$like_chk = "REPLACE(jo.postitle, ' ', '') LIKE '".$letter."%'" ;
                        }
				 	
			$que =  "select
			       jo.posid,
			       jo.postitle,
			       jo.location,
			       jo.company,
			       comp.cname,
			       comp.address1,
			       comp.address2,
			       comp.city,
			       comp.state,
			       joloc.address1,
			       joloc.address2,
			       joloc.city,
			       joloc.state,
			       jo.shiftid
			       from manage ma,posdesc jo LEFT JOIN staffoppr_cinfo comp on jo.company = comp.sno
			       LEFT JOIN staffoppr_location joloc ON joloc.sno = jo.location
			       where (jo.owner='".$username."' or FIND_IN_SET('".$username."',jo.accessto)>0  or jo.accessto='all') ".$whrDeptAccess." AND jo.posstatus = ma.sno AND ma.name $not_display_joborders_cond AND ma.type='jostatus' and jo.status in ('approve','Accepted') and jo.posid not in (".$jo_filled.") and ".$like_chk." order by ".$order_chk;
		}
		else
		{  
			$que =  "select
			       jo.posid,
			       jo.postitle,
			       jo.location,
			       jo.company,
			       comp.cname,
			       comp.address1,
			       comp.address2,
			       comp.city,
			       comp.state,
			       joloc.address1,
			       joloc.address2,
			       joloc.city,
			       joloc.state,
			       jo.shiftid
			       from manage ma,posdesc jo LEFT JOIN staffoppr_cinfo comp on jo.company = comp.sno
			       LEFT JOIN staffoppr_location joloc ON joloc.sno = jo.location
			       where (jo.owner='".$username."' or FIND_IN_SET('".$username."',jo.accessto)>0  or jo.accessto='all') ".$whrDeptAccess." AND jo.posstatus = ma.sno AND ma.name $not_display_joborders_cond AND ma.type='jostatus' and jo.status in ('approve','Accepted') and ".$like_chk." LIKE '".$letter."%' order by ".$order_chk;
		 
		}
			 
		$que_res = mysql_query($que,$db);
		$num_rows = mysql_num_rows($que_res);
		if($num_rows > 0)
		{
			while($que_row = mysql_fetch_row($que_res))
			{
				$pos_id = $que_row[0];
				$pos_tit = $que_row[1];
				$pos_loc_sno = $que_row[2];
				$pos_comp_sno = $que_row[3];
				$pos_cname = $que_row[4];
				$pos_addr1 = $que_row[5];
				$pos_addr2 = $que_row[6];
				$pos_city = $que_row[7];
				$pos_state = $que_row[8];
				
				$jopos_addr1 = $que_row[9];
				$jopos_addr2 = $que_row[10];
				$jopos_city = $que_row[11];
				$jopos_state = $que_row[12];	  
			  	$posdesc_shift_id = $que_row[13];
				$disp_data = displaydata($pos_id,$pos_tit,$pos_loc_sno,$pos_comp_sno,$pos_cname,$jopos_addr1,$jopos_addr2,$jopos_city,$jopos_state);
	  
    ?>
<TR  onmouseover="company('<?php echo $q;?>')" onMouseOut="company_out('<?php echo $q;?>')" onMouseDown="company_out('<?php echo $q;?>')" id=com<?php echo $q;?> class="mouseoutcont">
		<?php if($mode!='shortlist' && $roles!='no' && $cmg_frm!='email'){ ?>
			<td colspan="5" height="22" id="posdesc_shiftid_<?=$pos_id;?>" onclick="win_roles('<?=$Cand_Val;?>', '<?=$pos_id;?>','<?=$candrn;?>','<?=$hidemodule;?>');" data_shiftid="<?php echo $posdesc_shift_id;?>"><? echo str_replace("\\","",$disp_data); ?></td>
			<?php }else{ ?>
			<td colspan="5" height="22" id="posdesc_shiftid_<?=$pos_id;?>" onclick="win('<?=$pos_id;?>','<?=$cand_id;?>')" data_shiftid="<?php echo $posdesc_shift_id;?>"><? echo str_replace("\\","",$disp_data); ?></td>
		<?php } ?>
                </tr>
                <TR nowrap="nowrap">
                <td colspan="5" bgcolor="#ffffff"></td>
                </tr>
			<?
				$q++;
			}//while
		}//if
		else
		{      
			?>
			<TR class="mouseoutcont">
				<td colspan="5" height="22" align="center">Results not found.</td>
			</tr>
			</table>
	<?
		} //else if
	?>  
	<?
	}
	?>	
<? }?>

</body>
</html>

<script language="javascript" >
	function win_roles(cand_sno, posid, candrn, hidemodule)
	{	var shiftid =0;
		if (document.getElementById("posdesc_shiftid_"+posid)) {
			shiftid = document.getElementById("posdesc_shiftid_"+posid).getAttribute("data_shiftid");
		}		
		window.parent.displayShiftsModal(cand_sno, posid, candrn,'Submit',shiftid,hidemodule);
	}
</script>
