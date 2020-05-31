// Javascript functions
function show_delete_confirm_modal(id)
{
	$("#deleteModal").modal();
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='delete_plan_modal_action.php';
	url = url+"?id=" + id; //this is a must for retriving schedule of section
	url = url+"&action=1";
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChangedModalDelete;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function delete_plan()
{
	document.form_delete.submit();
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

function stateChangedModalDelete()
{
	if (xmlhttp.readyState==4)
	{
		document.getElementById("modal-delete-content").innerHTML=xmlhttp.responseText; //comment to remove message
	}
	else //while loading, display an ajax loading image
	{
		document.getElementById("modal-delete-content").innerHTML="<br><div align=\"center\"><img src=\"../../images/ajax-loader.gif\" width=\"100\" height=\"100\" /></div>";
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
