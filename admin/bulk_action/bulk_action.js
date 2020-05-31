// Javascript functions
function delete_record_semester(id)
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
