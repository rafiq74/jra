// Global javascript function for tplus
function sis_xxx()
{
	var code = document.form1.room.value;
	if(code != "")
	{
		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		var url;
		url="room_action.php";
		url = url+"?code=" + code;
		url = url+"&sid=" + Math.random();
		xmlhttp.onreadystatechange = StateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);				
	}
	else
		alert("Please enter the room code");
	return false;
}

////////////////////////////////////////
//Common functions
////////////////////////////////////////
function sis_check_number(testValue, theControl, showAlert)
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

//automatic add query string to url
function addQSParam(myUrl, name, value) {
    var re = new RegExp("([?&]" + name + "=)[^&]+", "");

    function add(sep) {
        myUrl += sep + name + "=" + encodeURIComponent(value);
    }

    function change() {
        myUrl = myUrl.replace(re, "$1" + encodeURIComponent(value));
    }
    if (myUrl.indexOf("?") === -1) {
        add("?");
    } else {
        if (re.test(myUrl)) {
            change();
        } else {
            add("&");
        }
    }
	return myUrl;
}
