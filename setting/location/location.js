// Javascript functions
function refresh_country(url)
{
	var country = document.getElementById("country").value;
	url = addQSParam(url, "country", country);
	document.location = url;
}

function delete_record_city(id)
{
	if(confirm("Are you sure you want to delete the selected City?"))
	{
		url = "index.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function delete_record_state(id)
{
	if(confirm("Are you sure you want to delete the selected State?"))
	{
		url = "state.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}



function city_search()
{
	document.form1.submit();
}
function state_search()
{
	document.form1.submit();
}