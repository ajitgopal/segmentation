// Add an event to the obj given
// event_name refers to the event trigger, without the "on", like click or mouseover
// func_name refers to the function callback when event is triggered
function addEvent(obj,event_name,func_name){

	if (obj.attachEvent){
		obj.attachEvent("on"+event_name, func_name);
	}else if(obj.addEventListener){
		obj.addEventListener(event_name,func_name,true);
	}else{
		obj["on"+event_name] = func_name;
	}
}

// Removes an event from the object
function removeEvent(obj,event_name,func_name){
	if (obj.detachEvent){
		obj.detachEvent("on"+event_name,func_name);
	}else if(obj.removeEventListener){
		obj.removeEventListener(event_name,func_name,true);
	}else{
		obj["on"+event_name] = null;
	}
}

// Stop an event from bubbling up the event DOM
function stopEvent(evt){
	evt || window.event;
	if (evt.stopPropagation){
		evt.stopPropagation();
		evt.preventDefault();
	}else if(typeof evt.cancelBubble != "undefined"){
		evt.cancelBubble = true;
		evt.returnValue = false;
	}
	return false;
}

// Get the obj that starts the event
function getElement(evt){
	if (window.event){
		return window.event.srcElement;
	}else{
		return evt.currentTarget;
	}
}
// Get the obj that triggers off the event
function getTargetElement(evt){
	if (window.event){
		return window.event.srcElement;
	}else{
		return evt.target;
	}
}
// For IE only, stops the obj from being selected
function stopSelect(obj){
	if (typeof obj.onselectstart != 'undefined'){
		addEvent(obj,"selectstart",function(){ return false;});
	}
}

/*    Caret Functions     */

// Get the end position of the caret in the object. Note that the obj needs to be in focus first
function getCaretEnd(obj){
	if(typeof obj.selectionEnd != "undefined"){
		return obj.selectionEnd;
	}else if(document.selection&&document.selection.createRange){
		var M=document.selection.createRange();
		try{
			var Lp = M.duplicate();
			Lp.moveToElementText(obj);
		}catch(e){
			var Lp=obj.createTextRange();
		}
		Lp.setEndPoint("EndToEnd",M);
		var rb=Lp.text.length;
		if(rb>obj.value.length){
			return -1;
		}
		return rb;
	}
}
// Get the start position of the caret in the object
function getCaretStart(obj){
	if(typeof obj.selectionStart != "undefined"){
		return obj.selectionStart;
	}else if(document.selection&&document.selection.createRange){
		var M=document.selection.createRange();
		try{
			var Lp = M.duplicate();
			Lp.moveToElementText(obj);
		}catch(e){
			var Lp=obj.createTextRange();
		}
		Lp.setEndPoint("EndToStart",M);
		var rb=Lp.text.length;
		if(rb>obj.value.length){
			return -1;
		}
		return rb;
	}
}
// sets the caret position to l in the object
function setCaret(obj,l){
	obj.focus();
	if (obj.setSelectionRange){
		obj.setSelectionRange(l,l);
	}else if(obj.createTextRange){
		m = obj.createTextRange();		
		m.moveStart('character',l);
		m.collapse();
		m.select();
	}
}
// sets the caret selection from s to e in the object
function setSelection(obj,s,e){
	obj.focus();
	if (obj.setSelectionRange){
		obj.setSelectionRange(s,e);
	}else if(obj.createTextRange){
		m = obj.createTextRange();		
		m.moveStart('character',s);
		m.moveEnd('character',e);
		m.select();
	}
}

/*    Escape function   */
String.prototype.addslashes = function(){
	return this.replace(/(["\\\.\|\[\]\^\*\+\?\$\(\)])/g, '\\$1');
}
String.prototype.trim = function () {
    return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
};
/* --- Escape --- */

/* Offset position from top of the screen */
function curTop(obj){
	toreturn = 0;
	while(obj){
		toreturn += obj.offsetTop;
		obj = obj.offsetParent;
	}
	return toreturn;
}
function curLeft(obj){
	toreturn = 0;
	while(obj){
		toreturn += obj.offsetLeft;
		obj = obj.offsetParent;
	}
	return toreturn;
}
/* ------ End of Offset function ------- */

/* Types Function */

// is a given input a number?
function isNumber(a) {
    return typeof a == 'number' && isFinite(a);
}

/* Object Functions */

function replaceHTML(obj,text){
	while(el = obj.childNodes[0]){
		obj.removeChild(el);
	};
	obj.appendChild(document.createTextNode(text));
}

//this is for adding the option to select box
function addOptionsToSelectBox(sobj,opt,sval)
{
	var totopt=sobj.options.length;
	var sflag	= "yes";
	for(var cnt=0; cnt<totopt; cnt++){
		if(sobj.options[cnt].value == sval)
		 sflag	= "no";
	}
	if(sflag=="yes" && opt!="")
	  sobj.options[sobj.options.length]	= new Option(opt,sval);
}

function HashEncode(arg1)
{
	var Reg=new RegExp("\\#","gi");
	var Arg=arg1.replace(Reg,"\%23");
	return Arg;
}

function UrlEncode(arg1)
{
	var RegAmp=new RegExp("\\&","gi");
	var RegH=new RegExp("\\#","gi");
	var Arg=arg1.replace(RegH,"\%23");
	 Arg=Arg.replace(RegAmp,"\%26");
	return Arg;
}

function UserUrlEncode(arg1)
{
	var RegAmp=new RegExp("\\&","gi");
	var Arg=arg1.replace(RegAmp,"\\|*amp*\\|");
	return Arg;
}

function UserUrlDecode(arg1)
{
	var Reg=new RegExp("\\|\\*amp\\*\\|","gi");
	var Arg=arg1.replace(Reg,"\%26");
	return Arg;
}

/* Function added to calculate the perdiem total dynamically. And is a common function for assignments and compensation screens. - Vijaya(11/11/2008) */
function calculatePerDiem()
{
    form=document.forms[0];
 
    if(isNumbervalidation(form.txt_lodging,"Lodging") && isNumbervalidation(form.txt_mie,"M&IE") && isNumbervalidation(form.txt_total,"Per Diem Total")) //To check the floating(6.2) format
	{
        var perDiemTotal=0,perDiem1,perDiem2, assign_flag;
		//Checking for all the possible values
		if(form.txt_lodging.value=='')
			perDiem1 = '';
		else
			perDiem1 = form.txt_lodging.value;

		if(form.txt_mie.value=='')
			perDiem2 = '';
		else
			perDiem2 = form.txt_mie.value;

		if(form.txt_total.value=='')
			perDiemTotal = '';
		else
			perDiemTotal = form.txt_total.value;

		if(perDiem1!="" && perDiem2!="")
		{
            perDiemTotal = parseFloat(perDiem1) + parseFloat(perDiem2);
        }
        else if(perDiem1=="" && perDiem2=="")
        {
            perDiemTotal = form.txt_total.value;
			
        }
        else if(perDiem1=="" || perDiem2=="")
		{			
            if(perDiemTotal=="")
            {
                if(perDiem1!="")
                    perDiemTotal = parseFloat(perDiem1);
                else if(perDiem2!="")
                    perDiemTotal = parseFloat(perDiem2);
            }
            else if(perDiemTotal!="")
            {
                if(perDiem1!="" && perDiem1!="0")
                	perDiemTotal = form.txt_lodging.value;
                else if(perDiem2!="" && perDiem2!="0")
                    perDiemTotal = form.txt_mie.value;                
            }
		}
		if(perDiem1 > 0 || perDiem2 > 0)
			form.txt_total.disabled = true;
		else if(perDiem1 <= 0 && perDiem2 <= 0)
			form.txt_total.disabled = false;
		
		if(perDiemTotal!='' || perDiemTotal=='0' )
		{
			form.txt_total.value = parseFloat(perDiemTotal);
		}

        return true;
    }
	else
	{		
		return false;
	}	
}
function IsNumeric(sText)

{
	var ValidChars = "0123456789.";
	var IsNumber=true;
	var Char;
	
	
	for (i = 0; i < sText.length && IsNumber == true; i++) 
	{ 
		Char = sText.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) 
		{
			IsNumber = false;
		}
	}
	return IsNumber;
   
}
function convertLessThnGrtThn(convrtStr){ //function to convert less than greater than 
	//toSearch=toSearch.replace(new RegExp('&',"gi"),"&amp;");	
	convrtStr=convrtStr.replace(new RegExp('<',"gi"),"&lt;");	
	convrtStr=convrtStr.replace(new RegExp('>',"gi"),"&gt;");	
	//toSearch=toSearch.replace(new RegExp('"',"gi"),"&quot;");	
	//toSearch=toSearch.replace(new RegExp("'","gi"),"&#039;");	
	return convrtStr;
}
/* 	CheckBox Functionality - Added by vijaya for Timesheets and Expenses Delete option	*/
function doChk_TimeSheet(obj)
{
	
	if(obj)
		var e=obj
	else
		var e = document.forms[0].chk;
	if ( e.checked == true )
	{
		checkAll_TimeSheet();
	}
	else
	{
		clearAll_TimeSheet();
	}
}//End of the Func()

function clearAll_TimeSheet()
{
	var e = document.getElementsByName("auids[]");
	//var chkbox = document.getElementById('chk') ? document.getElementById('chk') : document.forms[0].chk;
	for (var i=0; i < e.length; i++)
		if (e[i].name == "auids[]")
			e[i].checked = false;
}

function checkAll_TimeSheet()
{
	var e = document.getElementsByName("auids[]");
	//var chkbox = document.getElementById('chk') ? document.getElementById('chk') : document.forms[0].chk;
	for (var i=0; i < e.length; i++)
		//if (e[i].name == "auids[]")
		//{
			if(e[i].disabled!=true)
				e[i].checked = true;
		//}
}
function numSelected_TimeSheet()
{
	var e = document.getElementsByName("auids[]");
	var bNone = true;
	var iFound = 0;
	for (var i=0; i < e.length; i++)
	{
		if (e[i].name == "auids[]")
		{
			bNone = false;
			if (e[i].checked == true)
				iFound++;
		}
	}
	if (bNone)
		iFound = -1;
	return iFound;
}
function valSelected_TimeSheet()
{
	var e = document.getElementsByName("auids[]");
	var bNone = true;
	var iVal = "";
	for (var i=0; i < e.length; i++)
	{
		if (e[i].name == "auids[]")
		{
			bNone = false;
			if (e[i].checked == true)
			{
				userval=e[i].value;
				if(iVal=="")
					iVal=userval;
				else
					iVal+=","+userval;

			}
		}
	}
	if (bNone)
		iVal = "";
	return iVal;
}

function chk_clearTop_TimeSheet()
{
	var e = document.getElementsByName("auids[]");
	var chkbox = document.getElementById('chk') ? document.getElementById('chk') : document.forms[0].chk;
	for (var i=0; i < e.length; i++)
	{
		if (e[i].name == "auids[]")
		{

			if (e[i].checked == false)
			{
                chkbox.checked=false;
                return;
            }
        }
    }
    chkbox.checked=true;
    return;
}

//Employment Eligible Proof field has to allow all special chars except | and ^".
// Added new function for the employee elgibale field.
function validateSplCharsChk(field,name)
{
	var str = field.value;
	for (var i = 0; i < str.length; i++)
	{
		var ch = str.substring(i, i + 1);
		if ( ch == "^" || ch == "|" )
		{
			alert(name + " does not accept | and ^ characters. Please re-enter " 

+ name + ".");
			field.focus();
			return false;
		}
	}
	return true;
}

function showBillableText(opt,val)
{
	document.getElementById(val).style.display = (opt.value == 'Y') ? 'block' : 'none';
	
	if(val == 'spanperdiem' && opt.value == 'Y')
	{
		var txtBill = document.getElementById('txtperdiem');
		txtBill.value = (txtBill.value == '' || txtBill.value == '0.00' || txtBill.value == '0') ? document.getElementById('txt_total').value : txtBill.value;
	}
}

function changeDynStyles(employeeType)
{
	if(employeeType == "")
	{
		document.getElementById('tr_hours').className = "tr1bgcolor";
		document.getElementById('crm-joborder-hourscustom').className = "tr2bgcolor";
		document.getElementById('tr_rewPer').className = "tr1bgcolor";
		document.getElementById('tr_increment').className = "tr2bgcolor";
		document.getElementById('tr_bonus').className = "tr1bgcolor";
	}
}
function showBillDiv(obj,tot_txt)
{
	var billDiv = document.getElementById('bill_Div');
	var txtBill = document.getElementById('diem_billrate');
	billDiv.style.display = (obj.value == 'Y') ? '' : 'none';
	txtBill.value = (obj.value == 'Y' && (txtBill.value == '' || txtBill.value == '0.00')) ? document.getElementById(tot_txt).value : txtBill.value;
}//End of Func(showBillDiv)

//To Enable / Disable Employee Work Comp Code -- Added By Sandeep Ganachary
function checkWorkCompCode(chk)
{
	if(chk.checked == true)
		document.getElementById("txtworkcompcode").disabled = "disabled";
	else
		document.getElementById("txtworkcompcode").disabled = "";
}

//To Enable / Disable Employee Pay roll details - Added By Sandeep Ganachary
function checkPayBasedCompany(chk,selVal,comVal)
{
	if(chk.checked == true){
		var disable = "disabled";
		document.getElementById("lstpayperiod").value = selVal;
	}
	else{
		var disable = "";
		document.getElementById("lstpayperiod").value = comVal;
	}
	
	document.getElementById("lstpayperiod").disabled = disable;
	document.getElementById("txtpayhours").disabled  = disable;
	document.getElementById("txtpaydays").disabled   = disable;
}

function isPhNum(field,name){
	var s=field.value;
	var i;
	for (i = 0; i < s.length; i++)
	{
		// Check that current character is number.
		var c = s.charAt(i);
		if((c=='-') || (c=='(') || (c==')'))
			continue;
		if (((c < "0") || (c > "9")))
		{
			alert("The " + name + " field is invalid. Please enter the " + name + ".");
			field.focus();
			return false;
		}
	}
	// All characters are numbers.
	return true;
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

//To validate company / job location fileds in entire application - Added by Vipin.
function ValidMadisonJoborder(val)
{
	form = document.conreg;
	trimtextbox();
	if(val == 'company')
	{
		if(((!isNotEmpty(form.compname,"Company name")) || (!validateSplCharsChk(form.compname,"Company name")) || (!validateSplCharsChk(document.conreg.compaddress1,"Address1")) || (!isNotEmpty(form.compaddress1,"Address1")) || (!validateSplCharsChk(form.compaddress2,"Address2")) || (!validateSplCharsChk(form.compcity,"city")) || (!isNotEmpty(form.compcity,"city")) || (!validateSplCharsChk(form.compstate,"state")) || (!isNotEmpty(form.compstate,"state")) || (!validateSplCharsChk(form.compzip,"Zip")) || (!isNotEmpty(form.compzip,"Zip")) || (!isNotEmpty(form.compphone,"phone")) || (!validateSplCharsChk(form.compphone,"phone")) || (!isPhNum(form.compphone,"phone")) ||  (!validateSplCharsChk(form.compfax,"Fax")) ||  (!validateSplCharsChk(form.compfid,"FederalId")) || (!validateSplCharsChk(form.departmentname,"Department name")) || (!isNotEmpty(form.departmentname,"Department name"))))
		return false;
	}
	else if(val == 'jobloc')
	{
		if(((!isNotEmpty(form.jobloc_compname,"Company name")) || (!validateSplCharsChk(form.jobloc_compname,"Company name")) || (!validateSplCharsChk(form.jobloc_compaddress1,"Address1")) || (!isNotEmpty(form.jobloc_compaddress1,"Address1")) || (!validateSplCharsChk(form.jobloc_compaddress2,"Address2")) || (!validateSplCharsChk(form.jobloc_compcity,"city")) || (!isNotEmpty(form.jobloc_compcity,"city")) || (!validateSplCharsChk(form.jobloc_compstate,"state")) || (!isNotEmpty(form.jobloc_compstate,"state")) || (!validateSplCharsChk(form.jobloc_compzip,"Zip")) || (!isNotEmpty(form.jobloc_compzip,"Zip")) || (!isNotEmpty(form.jobloc_compphone,"phone")) || (!validateSplCharsChk(form.jobloc_compphone,"phone")) || (!isPhNum(form.jobloc_compphone,"phone")) ||  (!validateSplCharsChk(form.jobloc_compfax,"Fax")) || (!validateSplCharsChk(form.jobloc_compfid,"FederalId")) || (!validateSplCharsChk(form.jobloc_departmentname,"Department name")) || (!isNotEmpty(form.jobloc_departmentname,"Department name"))))
			return false;
	}

	return true;
}

function doAccSetup(type,obj)
{
	var url = "/BSOS/Accounting/Acc_Reg/newcat.php?aa="+type;
	
	if(type == "edit")
	{	
		var adr =  document.getElementById(obj).value;
		
		if(adr == 0)
		{
			alert("Please Select Account to Edit");
			return;
		}
		var url = url+"&addr="+adr;
	}
	
	var v_width  = 850;
	var v_heigth = 400;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	remote=window.open(url,"newaccount","width="+v_width+"px,height="+v_heigth+"px,resizable=no,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes");
	remote.focus();
}

function getDeptAccSetup(sts)
{
	var url = "/BSOS/Include/ajax_accountsetup.php";
	var rtype = sts;
	var locid = document.getElementById("location").value;

	var scat = "";
	var lst1 = "";
	var lst2 = ""; 
	var lst3 = "";
	var fromModule = "BO";

	if(window.location.href.indexOf("/BSOS/HRM/Consultant_Leads/conreg19.php") < 0 && window.location.href.indexOf("/BSOS/HRM/Consultant_Leads/revconreg19.php") < 0)
	{
		scat  = document.getElementById("scatval").value;
		lst1  = document.getElementById("lstsetup1").value;
		lst2  = document.getElementById("lstsetup2").value;
		lst3  = document.getElementById("lstsetup3").value;
		fromModule = "FO";
	}

	var content = "sts="+sts+"&locid="+locid+"&scat="+scat+"&lst1="+lst1+"&lst2="+lst2+"&lst3="+lst3+"&fromModule="+fromModule;

	if(sts == "dept")
	{
		var funname = "resultDeptAccSetup()";
	}
	else
	{
		var content = content+"&deptid="+document.getElementById("dept").value;
		var funname = "resultAccSetup()";
	}

	DynCls_Ajax_result(url,rtype,content,funname);
}

function resultDeptAccSetup()
{
	var result = DynCls_Ajx_responseTxt.split("|^^^AkkenSplit^^^|");
	document.getElementById("deptdiv").innerHTML = result[0];
	if(window.location.href.indexOf("/BSOS/HRM/Consultant_Leads/conreg19.php") < 0 && window.location.href.indexOf("/BSOS/HRM/Consultant_Leads/revconreg19.php") < 0)
	{
		document.getElementById("accdiv1").innerHTML = result[1];
		document.getElementById("accdiv2").innerHTML = result[2];
		document.getElementById("accdiv3").innerHTML = result[3];
	}
}

function resultAccSetup()
{
	var result = DynCls_Ajx_responseTxt.split("|^^^AkkenSplit^^^|");
	if(window.location.href.indexOf("/BSOS/HRM/Consultant_Leads/conreg19.php") < 0 && window.location.href.indexOf("/BSOS/HRM/Consultant_Leads/revconreg19.php") < 0)
	{
		document.getElementById("accdiv1").innerHTML = result[0];
		document.getElementById("accdiv2").innerHTML = result[1];
		document.getElementById("accdiv3").innerHTML = result[2];
	}
}

function doManageAccounts(type)
{
	var url = "/BSOS/Manage/manageAccounts.php?accTypeSno="+type;
	var v_width  = 800;
	var v_heigth = 400;
	var top1=(window.screen.availHeight-v_heigth)/2;
	var left1=(window.screen.availWidth-v_width)/2;
	remote=window.open(url,"newaccount","width="+v_width+"px,height="+v_heigth+"px,resizable=no,statusbar=no,menubar=no,scrollbars=yes,left="+left1+"px,top="+top1+"px,dependent=yes");
	remote.focus();	
}

function checkMinMaxLength(field,name,minimum,maximum)
{
	var str = field.value;
	//Check of QuickBooks Canada is enabled, then do the SIN validation whan navigated between tabs else do the SSN validation
	if(typeof(QBCDefault) != 'undefined' && QBCDefault == 'Y')
	{
		var sinchk = "no";
		var s = 0;
		 if (str.length == 9) {
			for (i=0; i<9; i++) {
			  x = eval(str.substring(i, i+1));
			  
			  i % 2 ? x << 1 > 9 ? s += (x << 1) - 9 : s += x << 1 : s += x;
			}
			
			s % 10 ? sinchk = "no" : sinchk = "yes";
		  }
		  else {
			alert("The SSN field accepts numbers only without spaces.\nPlease re-enter your SSN.");
			field.select();
			field.focus();
			return false;
		  }
		  if(sinchk == "no")
		  {
			alert("Invalid SSN.\nPlease re-enter your SSN.");
			field.select();
			field.focus();
			return false;
		  }
	}
	else
	{
		
		var flength = str.length;
		var chkRegExp = /^\$?(\d{3})(\-\d{2})(\-\d{4})?$/;
		var chkflag = true;
		
		if(flength > minimum && str != "")
		{
			if(chkRegExp.test(str))
				chkflag = true;
			else
				chkflag = false;
		}
		else if(flength == minimum && str != "")
		{
			for(var i=0;i<flength;i++)
			{
				if((str.substring(i,i+1)<"0") || (str.substring(i,i+1)>"9"))
					chkflag = false;
			}
		}
		else if(flength < minimum && str != "")
			chkflag = false;
		
		if(chkflag == false)
		{
			alert(name+" should be of the format xxxxxxxxx with numbers (or) xxx-xx-xxxx with numbers and hyphens.");
			field.select();
			field.focus();
			return false;
		}
	}
	return true;
}