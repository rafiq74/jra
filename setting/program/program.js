// Javascript functions
function add_plan_requisite(msg)
{
	course_id = document.form1.course_id.value;
	if(course_id != "")
	{
		course_id_requisite = document.form1.course_id_requisite.value;
		if(course_id != course_id_requisite) //must be not self
		{
			program_id = document.form1.program_id.value;
			plan_id = document.form1.plan_id.value;
			req_type = document.form1.requisite_type.value;
			eff_status = document.form1.eff_status.value;
			xmlhttp=GetXmlHttpObject();
			if (xmlhttp==null)
			{
				alert ("Browser does not support HTTP Request");
				return;
			}
			var url;
			url='plan_course_requisite_action.php';
			url = url+"?plan_id=" + plan_id;
			url = url+"&program_id=" + program_id;
			url = url+"&course_id=" + course_id;
			url = url+"&course_id_requisite=" + course_id_requisite;
			url = url+"&requisite_type=" + req_type;
			url = url+"&eff_status=" + eff_status;
			url = url+"&display=minimal";
			url = url+"&action=" + 1;
			url = url+"&sid=" + Math.random();
			xmlhttp.onreadystatechange = stateChanged;
			xmlhttp.open("GET", url, true);
			xmlhttp.send(null);	
		}
		else
			alert(msg);
	}
	else
		alert("Please select a course");
}

function save_plan_requisite()
{
	course_id = document.form2.course_id.value;
	program_id = document.form2.program_id.value;	
	plan_id = document.form2.plan_id.value;
	record_id = document.form2.record_id.value;
	req_type = document.form2.requisite_type.value;
	eff_status = document.form2.eff_status.value;
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='plan_course_requisite_action.php';
	url = url+"?plan_id=" + plan_id;
	url = url+"&program_id=" + program_id;
	url = url+"&course_id=" + course_id;
	url = url+"&record_id=" + record_id;
	url = url+"&requisite_type=" + req_type;
	url = url+"&eff_status=" + eff_status;
	url = url+"&display=full";
	url = url+"&action=" + 3;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function show_plan_requisite(plan_id, course_id, display)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='plan_course_requisite_action.php';
	url = url+"?plan_id=" + plan_id;
	url = url+"&course_id=" + course_id;
	url = url+"&display=" + display;
	url = url+"&action=" + 0;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function update_plan_requisite(plan_id, id, course_id, display)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='update_course_requisite_action.php';
	url = url+"?plan_id=" + plan_id;
	url = url+"&id=" + id;
	url = url+"&course_id=" + course_id;
	url = url+"&display=" + display;
	url = url+"&action=" + 0;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function delete_plan_requisite(plan_id, id, course_id, display, msg)
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
		url='plan_course_requisite_action.php';
		url = url+"?plan_id=" + plan_id;
		url = url+"&id=" + id;
		url = url+"&course_id=" + course_id;
		url = url+"&display=" + display;
		url = url+"&action=" + 2;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);	
	}
}

function refresh_requisite(url)
{
	course_id = document.form1.course_id.value;
	level = document.form1.course_level.value;
	level_requisite = document.form1.course_level_requisite.value;
	url = addQSParam(url, "course_id", course_id);
	url = addQSParam(url, "level", level);
	url = addQSParam(url, "level_requisite", level_requisite);
	document.location = url;
}

function add_plan_course()
{
	eff_date = document.form1.eff_date.value;
	if(document.form1.default_date.checked)
		default_date = 1;
	else
		default_date = 0;
	if(default_date == 0 && eff_date == '')
	{
		alert("Please select an effective date");
	}
	else
	{
		program_id = document.form1.program_id.value;
		plan_id = document.form1.plan_id.value;
		course_id = document.form1.course_id.value;
		course_type = document.form1.course_type.value;
		credit = document.form1.credit.value;
		course_level = document.form1.course_level.value;
		compulsory = document.form1.compulsory.value;
		probation_fail = document.form1.probation_fail.value;
		must_pass = document.form1.must_pass.value;
		in_cgpa = document.form1.in_cgpa.value;
		eff_status = document.form1.eff_status.value;
	
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url='plan_course_list_action.php';
		url = url+"?plan_id=" + plan_id;
		url = url+"&program_id=" + program_id;
		url = url+"&course_id=" + course_id;
		url = url+"&course_type=" + course_type;
		url = url+"&credit=" + credit;
		url = url+"&course_level=" + course_level;
		url = url+"&compulsory=" + compulsory;
		url = url+"&must_pass=" + must_pass;
		url = url+"&probation_fail=" + probation_fail;
		url = url+"&in_cgpa=" + in_cgpa;
		url = url+"&eff_status=" + eff_status;
		url = url+"&eff_date=" + eff_date;
		url = url+"&default_date=" + default_date;
		url = url+"&display=minimal";
		url = url+"&action=" + 1;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);	
	}
}

function save_plan_course()
{
	program_id = document.form2.program_id.value;
	plan_id = document.form2.plan_id.value;
	course_id = document.form2.course_id.value;
	course_type = document.form2.course_type.value;
	credit = document.form2.credit.value;
	course_level = document.form2.course_level.value;
	compulsory = document.form2.compulsory.value;
	must_pass = document.form2.must_pass.value;
	in_cgpa = document.form2.in_cgpa.value;
	eff_status = document.form2.eff_status.value;
	record_id = document.form2.record_id.value;

	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='plan_course_list_action.php';
	url = url+"?plan_id=" + plan_id;
	url = url+"&program_id=" + program_id;
	url = url+"&course_id=" + course_id;
	url = url+"&course_type=" + course_type;
	url = url+"&credit=" + credit;
	url = url+"&course_level=" + course_level;
	url = url+"&compulsory=" + compulsory;
	url = url+"&must_pass=" + must_pass;
	url = url+"&in_cgpa=" + in_cgpa;
	url = url+"&eff_status=" + eff_status;
	url = url+"&record_id=" + record_id;
	url = url+"&display=full";
	url = url+"&action=" + 3;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function show_plan_course(plan_id, display)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='plan_course_list_action.php';
	url = url+"?plan_id=" + plan_id;
	url = url+"&display=" + display;
	url = url+"&action=" + 0;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function update_plan_course(plan_id, id, display)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='update_plan_course_action.php';
	url = url+"?plan_id=" + plan_id;
	url = url+"&id=" + id;
	url = url+"&display=" + display;
	url = url+"&action=" + 0;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function delete_plan_course(plan_id, id, display, msg)
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
		url='plan_course_list_action.php';
		url = url+"?plan_id=" + plan_id;
		url = url+"&id=" + id;
		url = url+"&display=" + display;
		url = url+"&action=" + 2;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);	
	}
}

function refresh_program(url)
{
	var program = document.getElementById("program").value;
	url = addQSParam(url, "program", program);
	document.location = url;
}

function delete_plan(id, dataid)
{
	if(confirm("Are you sure you want to delete the selected academic plan?"))
	{
		url = "view_program.php";
		url=url+"?id=" + id;
		url=url+"&dataid=" + dataid;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
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
