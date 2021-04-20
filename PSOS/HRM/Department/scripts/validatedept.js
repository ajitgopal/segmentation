function doAdd()
{
	document.adddept.nav.value="add";
	var v_width  = 1000;
    	var v_heigth = 800;
	var top1=(window.screen.availHeight-v_heigth)/2;
 	var left1=(window.screen.availWidth-v_width)/2;
    remote=window.open("adddept.php","Departments","width="+v_width+"px,height="+v_heigth+"px,left="+left1+"px,top="+top1+"px,statusbar=no,menubar=no,scrollbars=yes,resizable=yes,hotkeys=no");
    remote.focus();
}

function doRename()
{
    form=document.adddept;
    var numAddrs = numSelected();

	if (numAddrs <= 0)
	{
		alert("You haven't selected a Department, Please select a Department to Rename");
		form.focus();
	}
	else if(numAddrs>1)
	{
		alert("You can't Rename more than one Department at a time");
		clearAll();
		form.pdir.value="";
		return;
	}
	else
	{
    	//document.adddept.action="renamedept.php";
    	var valAddrs =valSelected();
		var v_width  = 850;
    	var v_heigth = 550;
		var top1=(window.screen.availHeight-v_heigth)/2;
 		var left1=(window.screen.availWidth-v_width)/2;
    	remote=window.open("renamedept.php?edeptn="+valAddrs,"Departments","width="+v_width+"px,height="+v_heigth+"px,statusbar=no,menubar=no,scrollbars=no,resizable=no,left="+left1+"px,top="+top1+"px,hotkeys=no");
    remote.focus();
	clear_all();
    }
}
function clear_all()                        // added to clear the chk boxes
{
	var e = document.getElementsByName('auids[]');
	var chkbox = document.getElementById('chk') ? document.getElementById('chk') : document.forms[0].chk;
	chkbox.checked = false;
	for (var i=0; i < e.length; i++)
		e[i].checked = false;
}

function doEdit()
{
    form=document.adddept;
	var	numAddrs = numSelected();
	var	valAddrs = valSelected();
	if (numAddrs <= 0)
	{
       	alert("You haven't selected a Department, Please select a Department to Edit");
		document.adddept.focus();
	}
	else if(numAddrs>1)
	{
      	alert("You can't edit more than one Department at a time");
		clearAll();
		document.adddept.pdir.value="";
		return;
	}
	else
	{
		var getSptResp = valAddrs.split('|');
		document.adddept.nav.value="edit";
		document.adddept.addr2.value = getSptResp[0];
		var v_width  = 1000;
    		var v_heigth = 800;
		var top1=(window.screen.availHeight-v_heigth)/2;
 		var left1=(window.screen.availWidth-v_width)/2;
		remote=window.open("editdept.php?edeptname="+getSptResp[0]+"&chkDeflt="+getSptResp[3],"Departments","width="+v_width+"px,height="+v_heigth+"px,left="+left1+"px,top="+top1+"px,statusbar=no,menubar=no,scrollbars=yes,resizable=yes,hotkeys=no");
        remote.focus();
		clear_all();
	}
}

function doDelete()
{
	numAddrs = numSelected();
	valAddrs = valSelected();
	var getSelDefDept = chkSelDefDept();
	var getSelLoc = chkSelSameLoc();
	var getSptSelLoc = getSelLoc.split('|');
	
	if(numAddrs <= 0)
	{
		alert("You haven't selected a Department, Please select a Department to Delete");
		return;
	}
	else if(numAddrs >= 1 && getSelDefDept == false)
	{
		alert("You can't Delete the default Department");
		clearAll();
		return;
	}
	else if(numAddrs > 1 && getSptSelLoc[0] == "false")
	{
		alert("You can't Delete the Departments with different Locations at a time");
		clearAll();
		return;
	}
	else
	{
		document.getElementById('addr').value = valAddrs;	
		var getSelDept = chkSelSubDept(valAddrs);
		var getIds = getDelDeptIds();
		
		if(getSelDept == true)
		{
			var v_width  = 600;
			var v_heigth = 260;
			var top1 = (window.screen.availHeight-v_heigth)/2;
			var left1 = (window.screen.availWidth-v_width)/2;
			
			remote = window.open("dept_deactive_win.php?pageCall=mainGrid&locationVal="+getSptSelLoc[1]+"&deptSno="+getIds,"Departments","width="+v_width+"px,height="+v_heigth+"px,titlebar=no,toolbar=no,statusbar=no,menubar=no,scrollbars=no,left="+left1+"px,top="+top1+"px,resizable=no,hotkeys=no");
			remote.focus();
			clearAll();
		}
		else if(getSelDept == false)
		{
			alert("You have Sub Departments in this Department, You can't delete this Department.");
			clearAll();
			return;
		}
	}
}

function doClose()
{
	form=document.adddept;
	var fromText = form.credept.value;
	var fromList = form.sno.value;

		if(form.error.value=="success")
		{
			var toList   = window.opener.document.conreg.dept;
			var len = toList.length;
			toList.options.length=len+1;
			if(fromList != "")
			{
				toList.options[len].text=fromText;
				toList.options[len].value=fromList;
				toList.options[len].selected=true;
				self.close();
			}
			else
			{
				self.close();
			}
		}
		else
		{
			self.close();
		}
}

function isNotEmpty(field, name)
{
	var str=field.value;
	if(str=="")
	{
		alert("The " + name + " field is empty. Please enter the " + name + ".");
		field.focus();
		return false;
	}
	return true;
}

function doSave()
{
    form=document.adddept;	
	
    if( isNotEmpty(form.location,"Location") && isNotEmpty(form.deptname,"Department Name")&& isNotEmpty(form.deptcode,"Department Code") && isAlphaNumeric(form.deptcode,"Department Code"))
	{
		if(chkspchars())
		{
			var str=form.deptname.value;
			var len=str.length;
				
			document.adddept.nav.value="added";
			document.adddept.addr1.value="";
			hrsval = getHrList();
			document.adddept.hrvals.value = hrsval;
			hrsvalBO = getHrListBO();
			document.adddept.hrvalsBO.value = hrsvalBO;
			
			/*if(document.adddept.addr1.value != "")
			{
				if(confirm("Selected employee(s) will be updated to selected Department-Location and employee Accounts to default.\nAre you sure want to proceed?"))
				{
					depCheck(form.deptcode,'deptcode','adddept');
				}
				else
				{
					clearAllel();
					return;
				}
			}
			else
			{*/
				depCheck(form.deptcode,'deptcode','adddept');
			//}
        }
    }
}


function doSave1()
{
	if(document.adddept.deptname.value=="")
	{
		alert("Department Name field is Empty, Please enter Department Name");
		document.adddept.deptname.focus();
		return false;
	}
	else
	{
		return true;
	}
}

function valSelected()
{
	var e =  document.getElementsByName('auids[]');
	var bNone = true;
	var iFound = 0;
	var iVal="";
	for (var i=0; i < e.length; i++)
	{
		if (e[i].checked == true)
		{
			if(iVal=="")
				iVal=e[i].value;
			else
				iVal+=","+e[i].value;
		}
	}
	return iVal;
}
function valSelected1()
{
    var e =  document.getElementsByName('auids[]');
    var bNone = true;
    var iVal = "";
    for (var i=0; i < e.length; i++)
    {
		bNone = false;
		if (e[i].checked == true)
		if(iVal=="")
			iVal=e[i].value.split("|")[0];
		else
			iVal+=","+e[i].value.split("|")[0];
 
    }
    return iVal;
}
function doExport()
{
    valAddrs = valSelected1();
    form=document.adddept;
    if(document.adddept.chk.checked == false)
       document.adddept.addr.value = valAddrs;
    else
       document.adddept.addr.value = "All";
    document.adddept.action="exportdata.php?addr="+document.adddept.addr.value;
    form.submit();
}
function numSelected()
{
	var e =  document.getElementsByName('auids[]');
	var bNone = true;
	var iFound = 0;
	document.adddept.addr.value="";
	for (var i=0; i < e.length; i++)
	{
		bNone = false;
		if (e[i].checked == true)
		{
			iFound++;
			if(document.adddept.addr.value=="")
				document.adddept.addr.value=e[i].value;
			else
				document.adddept.addr.value+=","+e[i].value;
		}
	}
	if (bNone)
	{
		iFound = -1;
	}
	return iFound;
}


function doUpdate2()
{
	trimtextbox();
	form=document.adddept;
	if(form.rdeptname1.value=="")
	{
		alert("The Department Name field is Empty, Please enter the Department Name");
		form.rdeptname1.focus();
		return;
	}
	if(form.deptcode.value=="")
	{
		alert("The Department Code field is Empty, Please enter the Department Code");
		form.deptcode.focus();
		return;
	}
			
	var str=form.rdeptname1.value;
	var len=str.length;
	if(str.indexOf(' ')==0)
	{
	alert("\nThe New Department Name field contains space at the beginning.\n\nPlease re-enter your New Department Name");
	form.rdeptname1.select();
	form.rdeptname1.focus();
	return;
	}
	if(str.lastIndexOf(' ')==len-1)
	{
	alert("\nThe New Department Name field contains space at the end.\n\nPlease re-enter your New Department Name ");
	form.rdeptname1.select();
	form.rdeptname1.focus();
	return;
	}
		
	if(isAlphaNumeric(form.deptcode,"Department Code"))
	{
		if(chkspchars())
		{
			var deptsno  = document.getElementById("edname").value;
			var deptname = document.getElementById("rdeptname1").value;
			var deptcode = document.getElementById("deptcode").value;
			
			var content = "navigation=department&rtype=dupicateChk&sno="+deptsno+"&nameVal="+deptname+"&codeVal="+deptcode;
			var url = "/BSOS/Include/getAccounts.php";
			DynCls_Ajax_result(url,'dupicateChk',content,"chkDeptmt()");
			
		}
	}
	
}

function chkDeptmt()
{
	var result = DynCls_Ajx_responseTxt.split("|");
	if(result[0] == "YES" || result[1] == "YES")
	{
		if(result[1] == "YES")
			alert("The Department Name Already Exists.");
		else
			alert("The Department Code Already Exists.");
		return;
	}
	else
	{
		document.adddept.nav.value = "renamed";
		document.adddept.action="dodept.php";
		document.adddept.submit();
	}
}


function clearAll()
{
	var e = document.getElementsByName('auids[]');
	var chkbox = document.getElementById('chk') ? document.getElementById('chk') : document.forms[0].chk;
	chkbox.checked = false;
   	for (var i=0; i < e.length; i++)
		e[i].checked = false;
}

function doUpdate()
{
	form=document.adddept;	
	
    if( isNotEmpty(form.location,"Location") && isNotEmpty(form.deptname,"Department Name")&& isNotEmpty(form.deptcode,"Department Code") && isAlphaNumeric(form.deptcode,"Department Code"))
	{
		if(chkspchars())
		{
			var str=form.deptname.value;
			var len=str.length;
					
			var prevloc = document.getElementById("prevLoc").value;
			var currloc = document.getElementById("location").value;
			var prevdepcode = document.getElementById("prevDepCode").value;
			var currdepcode = document.getElementById("deptcode").value;
			var bNone = 0;
			if(prevloc != currloc)
			{
				if(confirm("All the existing records(Departments, Accounts, Employees, Customers, Vendors and Taxes) will be moved to selected location?"))
					bNone = 1;
				else
					bNone = 2;
			}
			
			if(bNone == 1)
			{
				document.adddept.prevLoc.value = prevloc;
				document.adddept.currLoc.value = currloc;
			}
			
			if(bNone != 2)
			{
				hrsval = getHrList();
				document.adddept.hrvals.value = hrsval;
				document.adddept.nav.value="edited";
				hrsvalBO = getHrListBO();
				document.adddept.hrvalsBO.value = hrsvalBO;

				if(prevdepcode == currdepcode)
					document.adddept.submit();
				else if((prevdepcode != currdepcode) && depCheck(form.deptcode,'deptcode','adddept')) { }
			}
		}
	}
}



function getHrList()
{
	form=document.adddept;
	var selList="";
	for(i=0;i<form.avahrs.length;i++)
	{
		if(form.avahrs[i].selected)
		{
			if(selList=="")
				selList=form.avahrs.options[i].value
			else
				selList=selList+","+form.avahrs.options[i].value
		}
	}

	return selList;
}

function getHrListBO()
{
	form=document.adddept;
	var selList="";
	for(i=0;i<form.avahrsBO.length;i++)
	{
		if(form.avahrsBO[i].selected)
		{
			if(selList=="")
				selList=form.avahrsBO.options[i].value
			else
				selList=selList+","+form.avahrsBO.options[i].value
		}
	}

	return selList;
}


function clearDeptVals() 
{
	var len; 
	len=document.adddept.avadept.length; 
	for(var i=0;i<len;i++) 
	{ 
		document.adddept.avadept.selectedIndex=-1; 
	} 
}

function clrAll() 
{ 
	var len; 
	len=document.adddept.avahrs.length; 
	for(var i=0;i<len;i++) 
	{ 
		document.adddept.avahrs.selectedIndex=-1; 
	} 
}
function selectAll() 
{ 
	var len; 
	len=document.adddept.avahrs.length; 
	for(var i=0;i<len;i++) 
	{ 
		document.adddept.avahrs.options[i].selected=true;
	} 
}

function clrAllBO() 
{ 
	var len; 
	len=document.adddept.avahrsBO.length; 
	for(var i=0;i<len;i++) 
	{ 
		document.adddept.avahrsBO.selectedIndex=-1; 
	} 
}
function selectAllBO() 
{ 
	var len; 
	len=document.adddept.avahrsBO.length; 
	for(var i=0;i<len;i++) 
	{ 
		document.adddept.avahrsBO.options[i].selected=true;
	} 
}

function chk_clearTop()
{
	var e = document.getElementsByName('auids[]');
	var chkbox = document.getElementById('chk') ? document.getElementById('chk') : document.forms[0].chk;
	for (var i=0; i < e.length; i++)
	{
		if (e[i].checked == false)
		{
			chkbox.checked=false;
			return;
		 }
    }
	chkbox.checked=true;
	return;
}

	function doCloseWindow()
    {
       if(window.opener)
       {
            var parwin=window.opener.location.href;
            window.opener.location.href=parwin;
       }
       window.close();
    }


function doClose1()
{
   if(window.opener)
   {
        var parwin=window.opener.location.href;
        window.opener.location.href=parwin;
   }
   window.close();
}
//Functions added by vijaya for opening a new window to add a location - 18/06/2009
function doLocation()
{
	var v_heigth = 500;
	var v_width  = 900;
    var top1=(window.screen.availHeight-v_heigth)/2;
    var left1=(window.screen.availWidth-v_width)/2;
	remote=window.open("../Hiring_Mngmt/newcon.php","con","width="+v_width+",height="+v_heigth+",statusbar=no,menubar=no,scrollbars=no,resizable=no,hotkeys=no,left="+left1+"px,top="+top1+"px,dependent=yes");
	remote.focus();
}
//Functions added by vijaya for opening a new window to add a location- 18/06/2009
function editLocation()
{
	form=document.forms[0];
	var addr=form.location.options[form.location.options.selectedIndex].value;
	if(addr=='')
	{
		alert('Please select a location to edit');
		return;
	}
	else
	{
		if(addr=='none')
		{
			alert("You haven't selected a Location, Please select a Location from the Available Locations");
		}
		else
		{
			remote=window.open("/BSOS/HRM/Hiring_Mngmt/editlocation.php?addr="+addr,"editcust","width=700,height=450,statusbar=no,menubar=no,scrollbars=yes,resizable=no,hotkeys=no");
			remote.focus();
		}
	}
}
/*function openNewWindow()
{	
	return;
}
*/function getAjaxRespForDelete(valAddrs)
{
	var respValue = DynCls_Ajx_responseTxt;
	
	if(respValue == "1")
	{
		alert("You can not Archive Record(s) as associated with Accounts.");
		return;
	}
	else if(respValue == "Y")
	{
		alert("You can not Archive default Record.");
		return;
	}
	else
	{
		if(confirm("Removed Department will not be available anywhere. Are you sure you want to remove the Department?"))
		{
			document.adddept.nav.value="remove";
			document.adddept.addr2.value = valAddrs;
			document.adddept.submit();
		}
		else
		{
			clearAll();
			document.adddept.pdir.value="";
		}
	}
}

function clearAllel()
{
	var e = document.getElementsByName('auids[]');
	var chkbox = document.getElementById('chk') ? document.getElementById('chk') : document.forms[0].chk;
	chkbox.checked = false;
   	for (var i=0; i < e.length; i++)
		e[i].checked = false;
}

function getLocClass(obj,getselval,currDept)
{
	document.getElementById('load_img').style.display = '';
	document.getElementById('avadept').disabled = true;
	
	var locval = obj.options[obj.selectedIndex].value;
	var clsid = obj.options[obj.selectedIndex].id;
	
		
 	DynCls_Ajax_result('renamedept.php?locationVal='+locval+'&snoVal='+currDept,'','rtype=pardepartment','dispParDepartments('+clsid+',"'+getselval+'")');	

}

function dispParDepartments(clsid,getselval)
{
	
	  document.getElementById('avadept').options.length=0;
	  
			var varSplitResp = DynCls_Ajx_responseTxt.split('^');
			
				for(var j=0;j<varSplitResp.length;j++)
				{
					var getSptResp = varSplitResp[j].split('|');
					var sel = document.getElementsByName('avadept');
					var len = sel[0].options.length;
					
					if(len==0)
						sel[0].options[0] = new Option('Select Department',0);
					
					len = sel[0].options.length;
					
					for(var k=0;k<sel.length;k++)
					{
						sel[k].options[len] = new Option(getSptResp[1],getSptResp[0]);
						if(getselval == getSptResp[0])
							sel[k].options[len].selected = true;
					}
				}
				
				var clss  = document.getElementById('selClasses');
				for(var i=0;i<clss.length;i++)
				{		   
					if(clss.options[i].value == clsid)
						clss.options[i].selected = true;
				}
		
	
		
		document.getElementById('load_img').style.display = 'none';
		document.getElementById('avadept').disabled = false;
}

function chkSelSubDept(getSelDept)
{
	var bNone = true;
	if(getSelDept.indexOf(',') > -1)
	{
		var getArrDept = getSelDept.split(",");
		for(var i=0; i<getArrDept.length; i++)
		{
			var arrPid = getArrDept[i].split("|");
			if(arrPid[1]!=0)
			{
				bNone = false;
				break;
			}
		}
	}
	else
	{
		var arrPid = getSelDept.split("|");
		if(arrPid[1]!=0)
			bNone = false;
	}
	
	return bNone;
}

function chkSelSameLoc()
{
	var e =  document.getElementsByName('auids[]');
	var bNone = true;
	var chkLoc = "";
	for(var i=0; i<e.length; i++)
	{
		if(e[i].checked == true)
		{
			var arrPid = e[i].value.split("|");
			
			if(chkLoc == "")
				chkLoc = arrPid[2];
			else
			{
				if(chkLoc != arrPid[2])
				{
					bNone = false;
					break;
				}
			}
		}
	}
	
	return bNone+"|"+chkLoc;
}

function chkDefDept(getChkVal)
{
	if(getChkVal == 'yes')
	{
		document.getElementById('avaSpanDept').style.display = 'none';
		document.getElementById('avaSpanDefDept').style.display = '';
	}
	else
	{
		document.getElementById('avaSpanDefDept').style.display = 'none';
		document.getElementById('avaSpanDept').style.display = '';
	}
}

function getRadValue(a)
{
	var j=0;
	for(var i=0;i<a.length;i++)	
		if(a[i].checked)
			j=i+1;

	if(j!=0)
		return a[j-1].value;
	else
		return "";
}

function getDelDeptIds()
{
	var delDeptIds = document.getElementById('addr').value;
	var getIds = "";
	if(delDeptIds.indexOf(',') > -1)
	{
		var getArrDept = delDeptIds.split(",");
		for(var i=0; i<getArrDept.length; i++)
		{
			var arrPid = getArrDept[i].split("|");
			if(getIds == "")
				getIds = arrPid[0];
			else
				getIds = getIds+","+arrPid[0];
		}
	}
	else
	{
		var arrPid = delDeptIds.split("|");
		if(getIds == "")
			getIds = arrPid[0];
		else
			getIds = getIds+","+arrPid[0];
	}
	
	return getIds;
}

function deActiveDept()
{
	var getMovDepts = getRadValue(document.getElementsByName('defaultDept'));
	var getSelDept = document.getElementById('avadept').value;
	
	if(getMovDepts == 'no' && getSelDept == 0)
	{
		alert("Select a Department to move the records.");
		document.getElementById('avadept').focus();
		return;
	}
	
	document.getElementById('nav').value = 'remove';
	document.TheForm.submit();
}

function chkSelDefDept()
{
	var e =  document.getElementsByName('auids[]');
	var bNone = true;
	for(var i=0; i<e.length; i++)
	{
		if(e[i].checked == true)
		{
			var arrPid = e[i].value.split("|");
			
			if(arrPid[3] == 'Y')
			{
				bNone = false;
				break;
			}
		}
	}
	
	return bNone;
}