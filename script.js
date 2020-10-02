// Global javascript function for tplus
////////////////////////////////////////
//Common functions
////////////////////////////////////////
function jra_check_integer(testValue, theControl, showAlert)
{
	if(testValue == '') //don't check if it is empty
		return true;
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

function jra_check_numeric(testValue, theControl, showAlert)
{
	if(testValue == '') //don't check if it is empty
		return true;
	var anum=/^\d*\.?\d*$/	
	if (!anum.test(testValue))
	{
		if(showAlert)
			alert(theControl + " must be a number.");
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
