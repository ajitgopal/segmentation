<?php
	require("global.inc");
	
	require("activewidgets.php");
	require("Menu.inc");
	$menu=new EmpMenu();
        $menu->showHeader("hrm","Employee Management","5");

    	$deptAccessObj = new departmentAccess();
	$deptAccesSno = $deptAccessObj->getDepartmentAccess($username,"'BO'");

	session_unregister("recno");
	session_unregister("ramstat");
	session_unregister("conusername");

	session_unregister("spchange");
	session_unregister("page1");
	session_unregister("page2");
	session_unregister("page3");
	session_unregister("page4");
	session_unregister("page5");
	session_unregister("page6");
	session_unregister("page7");
	session_unregister("page8");
	session_unregister("page9");
	session_unregister("page10");
	session_unregister("page111");
	session_unregister("page12");
	session_unregister("page13");
	session_unregister("page14");
	session_unregister("page15");
	session_unregister("page215");
	session_unregister("page17");
	session_unregister("page18");
	session_unregister("page19");
	session_unregister("page20");
	session_unregister("page211");
	session_unregister("page24");	
        session_unregister("page29");
        session_unregister("page30");
	session_unregister("employee_name");	
	
	session_unregister("npage1");
	session_unregister("npage2");
	session_unregister("npage3");
	session_unregister("npage4");
	session_unregister("npage5");
	session_unregister("npage6");
	session_unregister("npage7");
	session_unregister("npage8");
	session_unregister("npage9");
	session_unregister("npage10");
	session_unregister("npage111");
	session_unregister("npage12");
	session_unregister("npage13");
	session_unregister("npage14");
	session_unregister("npage15");
	session_unregister("npage215");
	session_unregister("npage17");
	session_unregister("npage18");
	session_unregister("npage19");
	session_unregister("npage20");
	session_unregister("npage211");
	session_unregister("npage24");	
        session_unregister("npage29");
        session_unregister("npage30");
?>
<link href="/BSOS/css/fontawesome.css" rel="stylesheet" type="text/css">
<script language=javascript src=scripts/validatenewsubmanage.js></script>
<style>
.active-column-0 {width:   30px;}
.active-column-1 {width: 110px; text-align: left;}
.active-column-2 {width: 100px; text-aligh:left;}
.active-column-3 {width: 110px; text-align: left;}
.active-column-4 {width: 110px; text-align: left;}
.active-column-5 {width: 150px; text-align: left;}
.active-column-6 {width: 100px; text-align: left;}
.active-column-7 {width: 80px; text-align: left;}
.active-column-8 {width:  50px; text-align: left;}



@media screen and (-webkit-min-device-pixel-ratio:0) {

.active-box-item{ padding-left:7px;}
}

</style>
<script>
function doRead(src)
{
	form=document.tree;
	rowid=src.getProperty("item/index");
	result=actdata[rowid][9];
    var v_width  = 1150;
    var v_heigth = 620;

	remote=window.open("emprev.php?mf=app&empform=emp&command=emphire&varaddr="+result,"Employee","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=yes,left=30,top=30,dependent=yes");
	remote.focus();
}

function initGrid1()
{
	obj1.setRowText("Search");
	obj1.setRowHeaderWidth("0px");
	obj1.setRowProperty("count", 1);
	obj1.setColumnProperty("count", 8);
	obj1.setDataProperty("text", function(i, j){return headdata[i][j]});
	obj1.setColumnProperty("text", function(i){return headcol[i]});
	obj1.styleSheet=document.styleSheets[document.styleSheets.length-1];
	obj1.styleSheet.addRule("#grid1 .active-box-resize", "display: none;");
	obj1.setAction("selectRow", null);
	obj1.setEvent("onkeydown", null);
	obj1.getTemplate("top/item").setEvent("onmousedown", headerClicked);
}

function initGrid2()
{
	obj2.setColumnProperty("count",9);
	obj2.setColumnProperty("text",function(i){return actcol[i]});
	obj2.setColumnHeaderHeight("1px");
	obj2.setRowHeaderWidth("0px");
	obj2.setModel("row", new Active.Rows.Page);
	obj2.setProperty("row/count", actdata.length);
	obj2.setProperty("row/pageSize",20);
	obj2.setProperty("data/text", function(i,j){return actdata[i][j]});
	obj2.setProperty("selection/multiple", true);
	var row = new Active.Templates.Row;
	//row.setEvent("onmouseover", "mouseover(this, 'active-row-highlight')");
	//row.setEvent("onmouseout", "mouseout(this, 'active-row-highlight')");
	row.setStyle("background", alternate);
	obj2.setTemplate("row", row);

	var column0 = new Active.Templates.Text;
	column0.setStyle('width', '30px');
	obj2.setTemplate('column', column0, 0);

	var column1 = new Active.Templates.Text;
	column1.setStyle('width', '110px');
	obj2.setTemplate('column', column1, 1);

	var column2 = new Active.Templates.Text;
	column2.setStyle('width', '100px');
	obj2.setTemplate('column', column2, 2);

	var column3 = new Active.Templates.Text;
	obj2.setTemplate('column', column3, 3);

	var column4 = new Active.Templates.Text;
	obj2.setTemplate('column', column4, 4);

	var column5 = new Active.Templates.Text;
	column5.setStyle('width', '55x');
	obj2.setTemplate('column', column5, 5);
	
	var column6 = new Active.Templates.Text;
	column6.setStyle('text-align', 'center');
	column6.setStyle('width', '55x');
	obj2.setTemplate('column', column6, 6);

	var column7 = new Active.Templates.Text;
	column7.setStyle('text-align', 'center');
	obj2.setTemplate('column', column7, 7);

	var column8 = new Active.Templates.Text;
	column8.setStyle('text-align', 'center');
	obj2.setTemplate('column', column8, 8);

	row.setEvent("ondblclick", function(){this.action("readMes")});
	obj2.setTemplate("row", row);
	obj2.setAction("readMes", doRead);
}
</script>
<link href="/BSOS/css/NewUiGrid.css" rel="stylesheet" type="text/css"> 
<form name=emp1>
<div id="main">
<td valign=top align=center>
<table width=99% cellpadding=0 cellspacing=0 border=0 class="ProfileNewUI">
	<div id="content">
	<tr>
		<td class="titleNewPad">
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr>
				<td colspan=2><font class=bstrip>&nbsp;</font></td>
			</tr>
			<tr>
				<td align=left><font class=modcaption>Employee Management</font></td>
		    	<td align=right><font class=hfontstyle> Following are the employees, who has been updated their profile.</font></td>
			</tr>
			<tr>
				<td colspan=2><font class=bstrip>&nbsp;</font></td>
			</tr>
			</table>
		</td>
	</tr>
	</div>

	<div id="topheader">
	<tr class="NewGridTopBg">
	<?php
        if($desk=="edesk")
		   $link=explode("|","/BSOS/Home/home.php");
		else
		   $link=explode("|","userman.php");

		$name=explode("|","fa-ban~Cancel");
		$heading="";
		//$link=explode("|","Location=$http_referer");
		$menu->showHeadingStrip1($name,$link,$heading,"left");
	?>
	</tr>
	</div>

	<div id="grid_form">
	<tr>
		<td>
		<?php
		//This is not to show the new approvals when shift scheduling is in disabled mode
		$ss_status_cond = "";
		// $asgn_ss_cond  = "";
		//to show the SS for approval when even no active assignments are exists
		// $asgn_ss_cond = " AND (hrcon_jobs.ustatus = 'active' OR emp_list.roles LIKE '%p28%') ";
		// if(SHIFT_SCHEDULING_ENABLED == 'N')
		// {
		// 	$ss_status_cond = " AND emp_list.roles != 'p28' ";
		// 	$asgn_ss_cond = "AND hrcon_jobs.ustatus = 'active' ";
		// }

		// echo $query="select distinct(emp_list.sno),hrcon_general.fname,hrcon_general.mname,hrcon_general.lname,hrcon_general.email,hrcon_general.wphone,hrcon_jobs.jtype,hrcon_w4.tax,hrcon_general.username from emp_list LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username LEFT JOIN hrcon_jobs ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username LEFT JOIN department ON hrcon_compen.dept=department.sno where hrcon_w4.ustatus='active' ".$asgn_ss_cond." and hrcon_general.ustatus='active' and emp_list.lstatus != 'DA'  and emp_list.empterminated != 'Y' and  emp_list.lstatus != 'INACTIVE'   and emp_list.roles!='' and emp_list.roles!='emp' and FIND_IN_SET('".$username."',department.permission)>0 ".$ss_status_cond." group by emp_list.sno";

		$query="select distinct(emp_list.sno),hrcon_general.fname,hrcon_general.mname,hrcon_general.lname,hrcon_general.email,hrcon_general.wphone,hrcon_jobs.jtype,hrcon_w4.tax,hrcon_general.username from emp_list LEFT JOIN hrcon_general ON emp_list.username = hrcon_general.username LEFT JOIN hrcon_jobs ON emp_list.username=hrcon_jobs.username LEFT JOIN hrcon_w4 ON emp_list.username=hrcon_w4.username LEFT JOIN hrcon_compen ON hrcon_compen.username=emp_list.username LEFT JOIN department ON hrcon_compen.dept=department.sno where hrcon_w4.ustatus='active' and hrcon_general.ustatus='active' and emp_list.lstatus != 'DA' and  emp_list.lstatus != 'INACTIVE'   and emp_list.roles!='' and emp_list.roles!='emp' and department.sno !='0' and department.sno IN(".$deptAccesSno.") ".$ss_status_cond." group by emp_list.sno";

		$headers="<label class='container-chk'><input type=checkbox name=chk onClick=chke1(this,document.forms[0],'auids[]')><span class='checkmark'></span></label>|First Name|Last Name|Skills|Phone|Email|Job Status|Type";
		$sertypes="|<input class=serbox0 type=text name=column0 size=15 onkeyup=searchGrid(this.value,'0')>|<input class=serbox1 type=text name=column1 size=5 onkeyup=searchGrid(this.value,'1')>|<input class=serbox2 type=text name=column2 size=15 onkeyup=searchGrid(this.value,'2')>|<input class=serbox3 type=text name=column3 size=15 onkeyup=searchGrid(this.value,'3')>|<input class=serbox4 type=text name=column4 size=15 onkeyup=searchGrid(this.value,'4')>|<select class=serbox5 name=column5 onChange=searchGrid(this.value,'5',true) ><option value=''>All</option><option value='On Bench'>On Bench</option><option value='On Vacation'>On Vacation</option><option value='Project'>Project</option><option value='Administrative Staff'>Administrative Staff</option></select>|<select class=serbox6 name=column6 onChange=searchGrid(this.value,'6',true) ><option value=''>All</option><option value='W-2'>W-2</option><option value='1099'>1099</option><option value='C-to-C'>C-to-C</option><option value='None'>None</option></select>";

		echo headerGrid($headers,$sertypes);
		$data=mysql_query($query,$db);
		echo displayWorkEmpManEmp($data,$db);
		?>
		<script>
		var obj1 = new Active.Controls.Grid;
		obj1.setId("grid1");
		initGrid1();
		document.write(obj1);

		var obj2=new Active.Controls.Grid;
		obj2.setId("grid2");
		initGrid2();
		document.write(obj2);

		initSers();
		gridPage=showGridPage();
		document.write(gridPage);
		</script>
		</td>
	</tr>
	</div>

	<div id="botheader">
	<tr class="NewGridBotBg">
	<!-- <?php
       //if($desk=="edesk")
        // $link=explode("|","/BSOS/Home/home.php");
       //else
       // $link=explode("|","userman.php");
        
		//$name=explode("|","fa-ban~Cancel");
		//$link=explode("|","Location=$http_referer");
		//$heading="user.gif~";
		//$menu->showHeadingStrip1($name,$link,$heading);
	?> -->
	</tr>
	</div>
</table>
</td>
</div>

<tr>
<?php
	$menu->showFooter();
?>
</tr>
</table>
</form>
<script>
//Removing the &nbsp; in the chrome browser when onloading the page- Email Collaboration grid
$(document).ready(function() { 
    $('#grid1').html(function(i,h){	
    	return h.replace(/&nbsp;/g,'');
    });
	$(window).scrollTop(0);	
});
</script>
</body>
</html>
