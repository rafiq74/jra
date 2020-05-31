// Javascript functions
function delete_record_organization(id)
{
	if(confirm("Are you sure you want to delete the selected Organization?"))
	{
		url = "index.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function delete_record_institute(id)
{
	if(confirm("Are you sure you want to delete the selected Institute?"))
	{
		url = "institute.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}
function delete_record_campus(id)
{
	if(confirm("Are you sure you want to delete the selected Campus?"))
	{
		url = "campus.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function delete_record_section(id)
{
	if(confirm("Are you sure you want to delete the selected Section?"))
	{
		url = "section.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function organization_search()
{
	document.form1.submit();
}
function institute_search()
{
	document.form1.submit();
}
function campus_search()
{
	document.form1.submit();
}
function section_search()
{
	document.form1.submit();
}
function stateChanged()
{
	if (xmlhttp.readyState==4)
	{
		document.getElementById("ajax-content").innerHTML=xmlhttp.responseText; //comment to remove message
	}
	else //while loading, display an ajax loading image
	{
		document.getElementById("ajax-content").innerHTML="<br><div align=\"center\"><img src=\"../images/ajax-loader.gif\" width=\"100\" height=\"100\" /></div>";
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
