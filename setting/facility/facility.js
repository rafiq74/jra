// Javascript functions
function delete_record_building(id)
{
	if(confirm("Are you sure you want to delete the selected Building?"))
	{
		url = "building.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function delete_record_room(id)
{
	if(confirm("Are you sure you want to delete the selected Room?"))
	{
		url = "index.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}
function delete_record_room_type(id)
{
	if(confirm("Are you sure you want to delete the selected Room Type?"))
	{
		url = "room_type.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function delete_record_usage(id)
{
	if(confirm("Are you sure you want to delete the selected Usage?"))
	{
		url = "usage.php";
		url=url+"?id=" + id;
		url=url+"&action=2";
		url=url+"&sid="+Math.random();
		document.location = url;
	}	
}

function refresh_room(url)
{
	var usage = document.getElementById("usage").value;
	url = addQSParam(url, "usage", usage);
	document.location = url;
}

function room_search()
{
	document.form1.submit();
}
function building_search()
{
	document.form1.submit();
}
function usage_search()
{
	document.form1.submit();
}
function room_type_search()
{
	document.form1.submit();
}
