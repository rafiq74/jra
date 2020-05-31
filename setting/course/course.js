// Javascript functions for Weeks course format
function save_course_equivalent()
{
	id = document.mform1.id.value;
	cid = document.mform1.course_id.value;
	did = document.mform1.dataid.value;
	eci = document.mform1.equal_course_id.value;
	cw = document.mform1.credit_weight.value;
	cancel = document.mform1.is_cancel.value;

	var pass = true;
	if(cancel == '') //not cancel
	{
		if(cw == '')
		{
			alert("Credit weight cannot be empty");
			pass = false;
		}
		else
		{
			if(isNaN(cw)) //true if not a number
			{
				alert("Credit weight must be a valid number");
				pass = false;
			}
		}
	}
	if(pass)
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url='course_equivalent_action.php';
		url = url+"?id=" + id;
		url = url+"&cid=" + cid;
		url = url+"&did=" + did;
		url = url+"&eci=" + eci;
		url = url+"&cw=" + cw;
		url = url+"&action=" + 1;
		url = url+"&cancel=" + cancel;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);	
	}
	return false;
}

function add_course_equivalent(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='add_course_equivalent_action.php';
	url = url+"?id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function show_course_equivalent(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='course_equivalent_action.php';
	url = url+"?id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function delete_course_equivalent(id, dataid, msg)
{
	if(confirm(msg))
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url='course_equivalent_action.php';
		url = url+"?id=" + id;
		url = url+"&dataid=" + dataid;
		url = url+"&action=" + 2;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);					
	}
}

function save_course_component()
{
	id = document.mform1.id.value;
	cid = document.mform1.course_id.value;
	did = document.mform1.dataid.value;
	ct = document.mform1.class_type.value;
	mcc = document.mform1.main_component.value;
	rt = document.mform1.room_type.value;
	dss = document.mform1.default_section_size.value;
	chw = document.mform1.contact_hour_week.value;
	chc = document.mform1.contact_hour_class.value;
	tww = document.mform1.teacher_workload_weight.value;
	fe = document.mform1.final_exam.value;
	lms = document.mform1.lms_course_creation.value;
	label = document.mform1.label.value;
	cancel = document.mform1.is_cancel.value;

	var pass = true;
	if(cancel == '') //not cancel
	{
		if(dss == '')
		{
			alert("Default section size cannot be empty");
			pass = false;
		}
		else
		{
			if(!CheckNumber(dss, 'Default section size', true)) //true if not a number
			{
				pass = false;
			}
		}
		if(tww == '')
		{
			alert("Teacher workload weight cannot be empty");
			pass = false;
		}
		else
		{
			if(isNaN(tww)) //true if not a number
			{
				alert("Teacher workload weight must be a valid number");
				pass = false;
			}
		}
	}
	if(pass)
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url='course_component_action.php';
		url = url+"?id=" + id;
		url = url+"&cid=" + cid;
		url = url+"&did=" + did;
		url = url+"&ct=" + ct;
		url = url+"&mcc=" + mcc;
		url = url+"&rt=" + rt;
		url = url+"&dss=" + dss;
		url = url+"&chw=" + chw;
		url = url+"&chc=" + chc;
		url = url+"&tww=" + tww;
		url = url+"&fe=" + fe;
		url = url+"&lms=" + lms;
		url = url+"&label=" + label;
		url = url+"&action=" + 1;
		url = url+"&cancel=" + cancel;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);	
	}
	return false;
}

function add_course_component(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='add_course_component_action.php';
	url = url+"?id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function update_course_component(id, dataid)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='add_course_component_action.php';
	url = url+"?id=" + id;
	url = url+"&dataid=" + dataid;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function show_course_component(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='course_component_action.php';
	url = url+"?id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function delete_course_component(id, dataid, msg)
{
	if(confirm(msg))
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url='course_component_action.php';
		url = url+"?id=" + id;
		url = url+"&dataid=" + dataid;
		url = url+"&action=" + 2;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);					
	}
}

function refresh_role()
{
	var role = document.form1.role.value;
	url="role.php?role=" + role;
	location = url;
}

function handleKeyPress(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		search_user();
	}
}


////////////////////////////////////////
//Common functions
////////////////////////////////////////
function CheckNumber(testValue, theControl, showAlert)
{
	var anum=/(^\d+$)/	
	if (!anum.test(testValue))
	{
		if(showAlert)
			alert(theControl + " must be a positive integer number.");
		return false;
	}
	else
		return true;	
}


function stateChanged()
{
	if (xmlhttp.readyState==4)
	{
		document.getElementById("ajax-content").innerHTML=xmlhttp.responseText; //comment to remove message
	}
	else //while loading, display an ajax loading image
	{
		document.getElementById("ajax-content").innerHTML="<br><div align=\"center\"><img src=\"../../images/ajax-loader.gif\" width=\"100\" height=\"100\" /></div>";
	}
}

function GetXmlHttpObject()
{
	if (window.XMLHttpRequest)
  	{
  		// code for IE7+, Firefox, Chrome, Opera, Safari
  		return new XMLHttpRequest();
  	}
	if (window.ActiveXObject)
  	{
  		// code for IE6, IE5
  		return new ActiveXObject("Microsoft.XMLHTTP");
  	}
	return null;
}
