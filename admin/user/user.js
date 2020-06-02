// Javascript functions for Weeks course format
function confirm_reset_password(url, msg)
{
	if(confirm(msg))
	{
		url = addQSParam(url, "op", "reset");
		document.location = url;		
	}
}

function confirm_reset_eula(url, msg)
{
	if(confirm(msg))
	{
		url = addQSParam(url, "op", "eula");
		document.location = url;		
	}
}

function reset_iban(url, msg)
{
	if(confirm(msg))
	{
		document.location = url;		
	}
}

function show_personal_info(id)
{
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='personal_info_action.php';
	url = url+"?id=" + id;
	url = url+"&action=" + 0;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function refresh_index(url)
{
	var status = document.getElementById("status").value;
	var user_type = document.getElementById("user_type").value;
	url = addQSParam(url, "status", status);
	url = addQSParam(url, "user_type", user_type);
	document.location = url;
}

function refresh_login(url)
{
	var user_type = document.getElementById("user_type").value;
	url = addQSParam(url, "user_type", user_type);
	document.location = url;
}

function refresh_role_index(url)
{
	var role = document.getElementById("role").value;
	url = addQSParam(url, "role", role);
	document.location = url;
}

function add_role()
{
	
	if(confirm("Are you sure you want to add the user to the role?"))
	{
		var role = document.form1.role.value;
		var subrole = document.form1.subrole.value;
		var user = document.getElementById('user');
		var role_value = document.getElementById('role_value');
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="role_action.php";
		url = url+"?emplid=" + user.value;
		url = url+"&role=" + role;
		url = url+"&subrole=" + subrole;
		url = url+"&role_value=" + role_value.value;
		url = url+"&action=" + 1;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}	
}

function delete_role(user)
{
	if(confirm("Are you sure you want to remove the user from the role?"))
	{
		var role = document.form1.role.value;
		var subrole = document.form1.subrole.value;
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="role_action.php";
		url = url+"?emplid=" + user;
		url = url+"&role=" + role;
		url = url+"&subrole=" + subrole;
		url = url+"&action=" + 2;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}	
}

function refresh_role(source)
{
	var role = document.form1.role.value;
	var subrole = document.form1.subrole.value;
	url = source + ".php?role=" + role;
	url = url + "&subrole=" + subrole;
	location = url;
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
}

function handleKeyPress(e)
{
	var key=e.keyCode || e.which;
	if (key==13)
	{
		add_role();
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
