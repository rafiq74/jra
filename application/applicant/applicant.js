// Javascript functions
function refresh_index(url)
{
	var status = document.getElementById("status").value;
	var city = document.getElementById("city").value;
	var per_page = document.getElementById("per_page").value;
	url = addQSParam(url, "status", status);
	url = addQSParam(url, "city", city);
	url = addQSParam(url, "per_page", per_page);
	document.location = url;
}



function delete_file(id, module, filename, action_url, msg)
{
	if(confirm(msg))
	{
		document.form_file.delete_id.value = id;
		document.form_file.delete_module.value = module;
		document.form_file.delete_file.value = filename;
		if(action_url != "")
			document.form_file.action = action_url;
		document.form_file.submit();
	}
}

function confirm_application()
{
	document.form_confirm.submit();
}

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

function show_apply_filter()
{
	$("#myModal").modal();
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='filter_modal_action.php';
	url = url+"?action=1"; //this is a must for retriving schedule of section
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChangedModal;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function show_final_admission(id)
{
	$("#myModal").modal();
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url;
	url='admit_modal_action.php';
	url = url+"?action=1"; //this is a must for retriving schedule of section
	url = url+"&id=" + id;
	url = url+"&sid=" + Math.random();
	xmlhttp.onreadystatechange = stateChangedModal;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);	
}

function update_admit(url)
{
	id = document.form_filter.id.value;
	score = document.form_filter.placement_test_score.value;
	admit = document.form_filter.admit_status.value;
	var pass = true;
	if(score != '')
	{
		if(!jra_check_numeric(score, 'Placement test score', true))
		{
			pass = false
		}
	}
	if(pass)
	{
		 $.ajax({
			type: "post",
			url: "admit_action.php",
			data: {
					id : id,
					score : score, 
					admit : admit
					},
			success: function(data){
				  location = url;
			}
		 });
	}
}

function update_status(id, status, url)
{
	if(status == 5)
		status_text = "Pending";
	else if(status == 11)
		status_text = "Approved";
	else if(status == 12)
		status_text = "Waiting List";
	else if(status == 13)
		status_text = "Rejected";
	if(confirm("Are you sure you would like to update the status to " + status_text))
	{
		 $.ajax({
			type: "post",
			url: "status_action.php",
			data: {
					id : id,
					status : status 
					},
			success: function(data){
				  location = url;
//				$("#ajax_area").html(data);

			}
		 });
	}
}

function update_status_all(status, url)
{
	if(status != 20)
	{
		if(status == 5)
			status_text = "Pending";
		else if(status == 11)
			status_text = "Approved";
		else if(status == 12)
			status_text = "Waiting List";
		else if(status == 13)
			status_text = "Rejected";
		if(confirm("Are you sure you would like to update the status of all the applicants in the list to " + status_text))
		{
			 $.ajax({
				type: "post",
				url: "status_all_action.php",
				data: {
						status : status 
						},
				success: function(data){
					  location = url;
	//				$("#ajax_area").html(data);
	
				}
			 });
		}
	}
	else
	{
		if(confirm("Are you sure you would like to send status update email to all in the list?"))
		{
			 $.ajax({
				type: "post",
				url: "status_all_action.php",
				data: {
						status : status 
						},
				success: function(data){
					  location = url;
	//				$("#ajax_area").html(data);
	
				}
			 });
		}
	}
}

function update_confirm_status(id, status, url)
{
	if(status == 3)
		status_text = "Suspended";
	else if(status == 4)
		status_text = "Unconfirmed";
	else if(status == 6) //suppose to be 1 for confirmed, but 1 already used by unlocked
		status_text = "Confirmed";
	else if(status == 1)
		status_text = "Unlocked";
	else if(status == 5)
		status_text = "Locked";
	if(confirm("Are you sure you would like to update the status to " + status_text))
	{
		 $.ajax({
			type: "post",
			url: "status_confirm_action.php",
			data: {
					id : id,
					status : status 
					},
			success: function(data){
				  location = url;
//				$("#ajax_area").html(data);

			}
		 });
	}
}


function update_confirm_status_all(status, url)
{
	if(status != 20)
	{
		if(status == 3)
			status_text = "Suspended";
		else if(status == 4)
			status_text = "Unconfirmed";
		else if(status == 1)
			status_text = "Unlocked";
		else if(status == 5)
			status_text = "Locked";
		else if(status == 7)
			status_text = "Admitted";
		else if(status == 8)
			status_text = "Not Admitted";
		if(confirm("Are you sure you would like to update the status of all the applicants in the list to " + status_text))
		{
			 $.ajax({
				type: "post",
				url: "status_confirm_all_action.php",
				data: {
						status : status 
						},
				success: function(data){
					  location = url;
	//				$("#ajax_area").html(data);
	
				}
			 });
		}
	}
	else
	{
		if(confirm("Are you sure you would like to send status update email to all in the list?"))
		{
			 $.ajax({
				type: "post",
				url: "status_confirm_all_action.php",
				data: {
						status : status 
						},
				success: function(data){
					  location = url;
	//				$("#ajax_area").html(data);
	
				}
			 });
		}
	}

}

function update_filter(url)
{
	id = document.form_filter.id.value;
	secondary = document.form_filter.secondary_weight.value;
	tahseli = document.form_filter.tahseli_weight.value;
	qudorat = document.form_filter.qudorat_weight.value;
	min_aggregate = document.form_filter.min_aggregate.value;
	num_applicant = document.form_filter.num_applicant.value;
	city_filter = document.form_filter.city_filter.value;
	
	if(secondary == '' || tahseli == '' || qudorat == '')
	{
		alert("Some of the required field is empty");
	}
	else
	{
		if(jra_check_numeric(secondary, 'Secondary School Result Weight', true))
		{
			if(jra_check_numeric(tahseli, 'Tahseli Weight', true))
			{
				if(jra_check_numeric(qudorat, 'Qudorat Weight', true))
				{
					if(jra_check_numeric(min_aggregate, 'Minimum Aggregate', true))
					{
						if(jra_check_integer(num_applicant, 'Number of applicant', true))
						{
							 $.ajax({
								type: "post",
								url: "filter_action.php",
								data: {
										id : id,
										secondary : secondary, 
										tahseli : tahseli,
										qudorat : qudorat,
										aggregate : min_aggregate,
										applicant : num_applicant,
										city_filter : city_filter
										},
								success: function(data){
									  location = url;
								}
							 });
							/*
							$('#myModal').modal('hide');
							xmlhttp=GetXmlHttpObject();
							if (xmlhttp==null)
							{
								alert ("Browser does not support HTTP Request");
								return;
							}
							var url;
							url='filter_action.php';
							url = url+"?id=" + id;
							url = url+"&secondary=" + secondary; 
							url = url+"&tahseli=" + tahseli;
							url = url+"&qudorat=" + qudorat;
							url = url+"&aggregate=" + min_aggregate;
							url = url+"&applicant=" + num_applicant;
							url = url+"&sid=" + Math.random();
							xmlhttp.onreadystatechange = stateChanged;
							xmlhttp.open("GET", url, true);
							xmlhttp.send(null);	
							*/
						}
					}
				}
			}
		}
	}
}


function semester_search()
{
	document.form1.submit();
}

function stateChangedModal()
{
	if (xmlhttp.readyState==4)
	{
		document.getElementById("modal-content").innerHTML=xmlhttp.responseText; //comment to remove message
	}
	else //while loading, display an ajax loading image
	{
		document.getElementById("modal-content").innerHTML="<br><div align=\"center\"><img src=\"../../images/ajax-loader.gif\" width=\"100\" height=\"100\" /></div>";
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
