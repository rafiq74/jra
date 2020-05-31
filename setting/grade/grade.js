// Javascript functions for Weeks course format
function save_grade_letter()
{
	id = document.mform1.id.value;
	gid = document.mform1.grade_scheme_id.value;
	did = document.mform1.dataid.value;
	g = document.mform1.grade.value;
	gd = document.mform1.description.value;
	gp = document.mform1.grade_point.value;
	rf = document.mform1.range_from.value;
	rt = document.mform1.range_to.value;
	s = document.mform1.status.value;
	ie = document.mform1.is_enrolled.value;	
	ex = document.mform1.exempted.value;	
	cancel = document.mform1.is_cancel.value;

	var pass = true;
	if(cancel == '') //not cancel
	{
		//check for empty
		if(g == '')
		{
			alert("Grade cannot be empty");
			pass = false;
		}
		else
		{
			if(gd == '')
			{
				alert("Grade description cannot be empty");
				pass = false;
			}
			else
			{
				if(gp == '')
				{
					alert("Grade point cannot be empty");
					pass = false;
				}
				else
				{
					if(rf == '')
					{
						alert("Range from cannot be empty");
						pass = false;
					}
					else
					{
						if(rt == '')
						{
							alert("Range to cannot be empty");
							pass = false;
						}
					}
				}
			}
		}
		if(pass)
		{
			if(isNaN(gp)) //true if not a number
			{
				alert("Grade point must be a valid number");
				pass = false;
			}
			else
			{
				if(!CheckNumber(rf, 'Range from', true)) //true if not a number
				{
					pass = false;
				}
				else
				{
					if(!CheckNumber(rt, 'Range to', true)) //true if not a number
					{
						pass = false;
					}
				}
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
		url='grade_scheme_action.php';
		url = url+"?id=" + id;
		url = url+"&gid=" + gid;
		url = url+"&did=" + did;
		url = url+"&g=" +  encodeURIComponent(g);
		url = url+"&gd=" +  encodeURIComponent(gd);
		url = url+"&gp=" + gp;
		url = url+"&rf=" + rf;
		url = url+"&rt=" + rt;
		url = url+"&s=" + s;
		url = url+"&ie=" + ie;
		url = url+"&ex=" + ex;
		url = url+"&action=" + 1;
		url = url+"&cancel=" + cancel;
		url = url+"&sid=" + Math.random();
		url = encodeURI(url);
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);	
	}
	return false;
}


function show_scheme_detail(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="grade_scheme_action.php";
	url = url+"?id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}


function add_grade_letter(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="add_grade_letter_action.php";
	url=url+"?id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function update_grade_letter(id, dataid)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="add_grade_letter_action.php";
	url=url+"?id=" + id;
	url = url+"&dataid=" + dataid;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function delete_grade_letter(id, dataid, msg)
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
		url = "grade_scheme_action.php";
		url=url+"?id=" + id;
		url=url+"&delete_id=" + dataid;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);					
	}	
}


function save_grade_scheme(parentid)
{
	cancel = document.mform1.is_cancel.value;
	if(cancel == "") //user submit
	{
		if(document.mform1.grade_scheme.value == "")
		{
			alert("Grade scheme cannot be empty");
			return false;
		}
		else
			return true;	
	}
	else
	{
		return true;
	}
}

function add_grade_scheme()
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="add_grade_scheme_action.php";
	url = url+"?sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function update_grade_scheme(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="add_grade_scheme_action.php";
	url=url+"?id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);					
}

function delete_grade_scheme(id, msg)
{
	if(confirm(msg))
	{
		url = "index.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid=" + Math.random();
		document.location = url;
	}
}

function refresh_role()
{
	var role = document.form1.role.value;
	url="role.php?role=" + role;
	location = url;
}

function handleKeyPress2(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		add_role();
	}
}

function search_role()
{
	var role = document.form1.role.value;
	var subrole = document.form1.subrole.value;
	if(role != "" && subrole != "") //at least one must not be empty
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="role_action.php";
		url = url+"?role=" + role;
		url = url+"&subrole=" + subrole;
		url = url+"&action=" + 0;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
	else
		alert("Please select a role and permission");
	return false;
}

function search_user()
{
	var emplid = document.form1.emplid.value;
	var name = document.form1.name.value;
	var theType = document.form1.type.value;
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url="user_action.php";
	//GET example
	url = url+"?emplid=" + emplid;
	url = url+"&name=" + name;
	url = url+"&type=" + theType;
	url = url+"&action=" + 0;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);
	return false;
}

function reset_password(emplid)
{
	if(confirm("Are you sure you want to reset the password?"))
	{
		var emplid = document.form1.emplid.value;
		var name = document.form1.name.value;
		var theType = document.form1.type.value;
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="user_action.php";
		url = url+"?emplid=" + emplid;
		url = url+"&name=" + name;
		url = url+"&type=" + theType;
		url = url+"&action=" + 1;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
}

function handleKeyPress(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		search_user();
	}
}

function handleKeyPress3(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		search_suspen_user();
	}
}

function search_suspend_user()
{
	var emplid = document.form1.emplid.value;
	if(emplid != "") //at least one must not be empty
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="suspend_action.php";
		url = url+"?emplid=" + emplid;
		url = url+"&action=" + 0;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
	else
		alert("Please enter a student id");
	return false;
}

function remove_suspension(emplid)
{
	if(confirm("Are you sure you want to remove the suspension for this user?"))
	{
		document.form1.delete_id.value = emplid;
		document.form1.submit();
	}
}

function validateForm()
{
	if(document.form1.course.value == "")
	{
		alert("Employee ID / Student ID cannot be empty");
		return false;
	}
	else
		return true;
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