<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contain all the global functions for RCYCI module
*/

// This is the library for custom user interface
defined('MOODLE_INTERNAL') || die();

function jra_ui_page_title($text, $ret = false)
{	
    $str = html_writer::tag('h3', $text, array());
	$str = $str . '<hr />';
	if($ret)
		return $str;
	else
		echo $str;
}

//2nd level header
function jra_ui_page_title2($text, $cls = array(), $ret = false)
{	
    $str = html_writer::tag('h4', $text, $cls);
	$str = $str . '<hr />';
	if($ret)
		return $str;
	else
		echo $str;
}

//table is the standard moodle table object
function jra_ui_print_table($table, $responsive = true, $ret = false)
{
	$str = html_writer::table($table);
	if($responsive)
		$str = '<div class="table-responsive-md">' . $str . '</div>';
	if($ret)
		return $str;
	else
		echo $str;
}

//this function print the box in one go
function jra_ui_box($text, $header = '', $footer = '', $ret=false)
{
	$str = '<div class="card">';
	if($header != '') //has header
	{
		$str = $str . '<div class="card-header">';
		$str = $str . $header;
		$str = $str . '</div>';
	}
	if($text != '')
	{
		$str = $str . '<div class="card-body">';
		$str = $str . $text;
		$str = $str . '</div>';
	}
	if($footer != '') //has header
	{
		$str = $str . '<div class="card-footer">';
		$str = $str . $footer;
		$str = $str . '</div>';
	}
	$str = $str . '</div>';
	if(!$ret)
		echo $str;
	else
		return $str;
}

//start of a box
function jra_ui_box_start($border = false)
{
	global $OUTPUT;
	if($border)
		$cls = 'jra_box';
	else
		$cls = 'jra_box_frontpage';
	return $OUTPUT->box_start($cls);
}

//start of a box
function jra_ui_box_end()
{
	global $OUTPUT;
	return $OUTPUT->box_end();
}


//print a square box
function jra_ui_square($text, $ret=false)
{
	global $OUTPUT;
	$str = $OUTPUT->box_start('jra_box_plain');
	$str = $str . $text;
	$str = $str . $OUTPUT->box_end();
	if(!$ret)
		echo $str;
	else
		return $str;
}

//flash message will appear once. Once it is shown, it will be removed
function jra_ui_set_flash_message($text, $session_name)
{
	jra_set_session($session_name, $text);
}

function jra_ui_show_flash_message($session_name, $ret = false)
{
	$str = jra_get_session($session_name);
	jra_set_session($session_name, '');	
	if($str != '')
	{
		if($ret)
			return $str;
		else
			echo $str;
	}
}

//$option = primary, secondary, success, danger, warning, info, light, dark
function jra_ui_alert($text, $option, $title = '', $close = true, $ret = false)
{
	$str = '
		<div class="alert alert-' . $option . '" role="alert">
	';
	if($close)
	{
		$str = $str . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
	}
	if($title != '')
		$str = $str . '<h4 class="alert-heading">' . $title . '</h4>';
	$str = $str . $text;
	$str = $str . '</div>';

	if($ret)
		return $str;
	else
		echo $str;
}

//$option = primary, secondary, success, danger, warning, info, light, dark
function jra_ui_label($text, $option, $ret = false)
{
	$option = 'label-' . $option;
	$str = '<span class="label '.$option.'">'.$text.'</span>';
	if($ret)
		return $str;
	else
		echo $str;
}

//$option = primary, secondary, success, danger, warning, info, light, dark
function jra_ui_badge($text, $option, $ret = false)
{
	$option = 'badge-' . $option;
	$str = '<span class="badge '.$option.'">'.$text.'</span>';
	if($ret)
		return $str;
	else
		echo $str;
}

function jra_ui_space($count = 1, $ret = true)
{
	$output = '';
	for($i = 0; $i < $count; $i++)
		$output .= '&nbsp;';
	if(!$ret)
		echo $output;
	else
		return $output;
}

function jra_ui_icon($icon, $size = '', $ret = false)
{
	if($size == '')
		$size = '';
	else if($size == 1)
		$size = 'fa-lg';
	else
		$size = 'fa-' . $size . 'x';
	$str = '<i class="fa fa-' . $icon . ' '.$size.'"></i>';
	if($ret)
		return $str;
	else
		echo $str;
}

function jra_ui_link($text, $url, $attr = array(), $ret = true)
{
	$str = html_writer::link($url, $text, $attr);
	if($ret)
		return $str;
	else
		echo $str;
}

//quick way to display a button
//primary, secondary, success, info, warning, danger, link
function jra_ui_button($text, $url, $type = 'primary', $icon = '', $extra_class = '', $ret = true)
{
	$btn_text = $text;
	if($icon != '')
		$btn_icon = '<i class="fa fa-'.$icon.'" aria-hidden="true"></i>' . ' ';
	else
		$btn_icon = '';
	$btn_class = 'btn btn-' . $type;
	if($extra_class != '')
		$btn_class = $btn_class . ' ' . $extra_class;
	$output = html_writer::link($url, $btn_icon . $btn_text, array(
			'class' => $btn_class,
			'aria-label' => $btn_text,
			));
	if($ret)
		return $output;
	else
		echo $output;
}

//type: info, success, primary, danger, warning. ... (standard bootstrap)
function jra_ui_button_link($url, $text, $type, $size = '')
{
	if($size = 'small')
		$btn_size = ' btn-sm';
	else if($size = 'large')
		$btn_size = ' btn-lg';
	else
		$btn_size = '';
	return html_writer::link($url, $text, array('title' => $text, 'class' => 'btn btn-' . $type . $btn_size));
}
/////////Form helper function///////////////////////
//element is an associative array of value => label
function jra_ui_radio($name, $element, $selected, $space = '&nbsp;')
{
	$str = '';
	foreach($element as $key => $value)
	{
		if($str != '')
			$str = $str . $space;
		if($selected == $key)
			$checked = 'checked';
		else
			$checked = '';
		$str = $str . '<input type="radio" name="'.$name.'" value="'.$key.'" '.$checked.' /> ' . $value;
	}
	return $str;
}

function jra_ui_checkbox($name, $checked = false, $value = '1', $onClick = '')
{
	if($checked)
		$checked = 'checked';
	else
		$checked = '';
	if($onClick != '')
		$oc = 'onClick="'.$onClick.'"';
	else
		$oc = '';
	$str = '<input name="'.$name.'" id = "' . $name . '" type="checkbox" id="'.$name.'" value="'.$value.'" '.$checked.' ' . $oc . '/>';
	return $str;
}

//size is fixed
function jra_ui_input($name, $size, $value = '', $onkeypress='', $maxlength = '100')
{
	if($onkeypress != '')
		$okp = 'onkeypress="'.$onkeypress.'"';
	else
		$okp = '';
	$str = '<input name="'.$name.'" type="text" id="'.$name.'" size="'.$size.'" value="'.$value.'" maxlength="' . $maxlength . '" '.$okp.'/>';
	return $str;
}

//size can be changed
function jra_ui_input_fluid($name, $size, $value = '', $onkeypress='', $maxlength = '100', $label = '')
{
	if($onkeypress != '')
		$okp = 'onkeypress="'.$onkeypress.'"';
	else
		$okp = '';
	$str = '<span class="form-inline">' . $label . '<input name="'.$name.'" type="text" class="form-control" id="'.$name.'" size="'.$size.'" value="'.$value.'" maxlength="'.$maxlength.'" '.$okp.'/></span>';
	return $str;
}

function jra_ui_textarea($name, $rows, $cols, $value = '', $onkeypress='')
{
	if($onkeypress != '')
		$okp = 'onkeypress="'.$onkeypress.'"';
	else
		$okp = '';
	$str = '<textarea name="'.$name.'" id="'.$name.'" rows="'.$rows.'" cols="'.$cols.'" '.$okp.'/>' . $value . '</textarea>';
	return $str;
}

function jra_ui_select($name, $options, $value = '', $onchange = '', $styles = '')
{
	if($onchange != '')
		$oc = 'onchange="'.$onchange.'"';
	else
		$oc = '';
	if($styles != '')
		$st = 'style="'.$styles.'"';
	else
		$st = '';
	$str = '<select class="custom-select" name="'.$name.'" id="'.$name.'" '.$oc.' '.$st.'>';
	foreach($options as $key => $label)
	{
		if($key == $value)
			$selected = 'selected';
		else
			$selected = '';
		$str = $str . '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
	}
	$str = $str . '</select>';
	return $str;
}

function jra_ui_hidden($name, $value)
{
	$str = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'">';
	return $str;
}

function jra_week_dropdown($name, $value='', $onchange='')
{	
	$weeks = jra_academic_week();
	return jra_ui_select($name, $weeks, $value, $onchange);
}

//display records as a table. Name is a unique name, usually the table name for javascript callback
//useful table class table-bordered table-striped table-sm table-hover table-responsive
function jra_ui_dump_table($name, $options = array(), $fields = array(), $lang_name = 'local_jra', $session_name = '', $ret = true)
{
	global $PAGE, $OUTPUT, $DB;
	if(isset($options['debug']) && $options['debug'] === true)
		$DB->set_debug(true); //to enable DB debugging (use only in development)
	//remember all the query string in a session so if we have to return tothe page, we can return it to the current state
	if($session_name == '')
		jra_set_session($name . '_return_params', $_GET);
	else
		jra_set_session($session_name . '_return_params', $_GET);
	
	if(isset($options['default_sort_field']))
		$default_sort_field = $options['default_sort_field'];
	else
		$default_sort_field = 'id';
	$sort = optional_param('sort', '', PARAM_TEXT);
	if(isset($options['desc']) && $options['desc'])
		$initial_sort = 'desc';
	else
		$initial_sort = 'asc';
	$order = optional_param('order', $initial_sort, PARAM_TEXT);	
	if($sort == '' || !isset($fields[$sort]))
	{
		$sort = $default_sort_field;
		$sort_text = $sort . ' ' . $order;
	}
	else
	{
		$sort_arr = explode(',', $sort);
		$sort_text = '';
		foreach($sort_arr as $sa)
		{
			if($sort_text != '')
				$sort_text = $sort_text . ',';
			$sort_text = $sort_text . $sa . ' ' . $order;
		}
	}

	$page = optional_param('page', 0, PARAM_INT);
	
	if(isset($options['perpage']))
		$default_perpage = $options['perpage'];
	else
		$default_perpage = jra_global_var('PER_PAGE');
	$perpage = optional_param('perpage', $default_perpage, PARAM_INT);

	$search = optional_param('search', '', PARAM_TEXT);	
	if(!isset($_GET['search'])) //try to get it from session
	{
		$search = jra_get_session($name . '_list_search');
	}
	else //remember the search in session
	{
		jra_set_session($name . '_list_search', $search);
	}
	$field = optional_param('field', '', PARAM_TEXT);
	if(!isset($_GET['field'])) //try to get it from session
	{
		$field = jra_get_session($name . '_list_field');
	}
	else //remember the field in session
	{
		jra_set_session($name . '_list_field', $field);
	}
	if(!isset($fields[$field]))
	{
		//also make sure that it is really not exist, not because we will use search_key
		$found = false;
		foreach($fields as $key => $value)
		{
			if(isset($value['search_key']))
			{
				$a_key = $value['search_key'];
				if($a_key == $field)
					$found = true;
			}
		}
		if(!$found)
		{
			$field = '';
			jra_set_session($name . '_list_field', $field);
			$search = '';
			jra_set_session($name . '_list_search', $search);
		}
	}
	if(isset($options['condition']))
		$condition = $options['condition'];
	else
		$condition = array();
	//build the condition sql string in case if need to search
	if(isset($options['conditionText']) && $options['conditionText'] != '') //if there is a textual condition, we take it as it is
		$conditionWhere = $options['conditionText'];
	else
	{
		$conditionWhere = '';
		foreach($condition as $c => $v)
		{
			if($conditionWhere != '')
				$conditionWhere = $conditionWhere . ' and';
			$conditionWhere = $conditionWhere . " $c = '$v'";
		}
	}
	//get the count of the records
	if(isset($options['count_field']) && $options['count_field'] != '')
		$count_field = $options['count_field'];
	else
		$count_field = '*'; //defaul to id if not defined
	if(isset($options['sql']) && $options['sql'] != '')
	{
		$sql = strtolower($options['sql']);
		$arr = explode(' from ', $sql); //split the sql
		//reconstruct a count sql
		$count_sql = "select count($count_field) as total from " . $arr[1]; //join with the 2nd part

		if($search != '' && $field != '')
		{
			if($conditionWhere != '')
				$actualCondition = ' and ' . $conditionWhere;
			else
				$actualCondition = '';
//			$count_sql = $count_sql . " WHERE $field like '%$search%'" . $actualCondition;
			$count_sql = $count_sql . " WHERE " . $DB->sql_like($field, ':field', false) . ' ' . $actualCondition;
			$search_param = array('field' => "%$search%");
		}
		else
		{
			$search_param = array();
			if($conditionWhere != '')
				$count_sql = $count_sql . ' WHERE ' . $conditionWhere;
		}
		$totalrecord = $DB->count_records_sql($count_sql, $search_param);
	}
	else //use table name
	{		
		if($search != '' && isset($fields[$field]))
		{
			if($conditionWhere != '')
				$actualCondition = ' and ' . $conditionWhere;
			else
				$actualCondition = '';
//			$count_sql = "SELECT count($count_field) as total FROM {".$name."} WHERE $field like '%$search%'" . $actualCondition;
			$count_sql = "SELECT count($count_field) as total FROM {".$name."} WHERE " . $DB->sql_like($field, ':field', false) . ' '  . $actualCondition;
			$search_param = array('field' => "%$search%");
			
			$totalrecord = $DB->count_records_sql($count_sql, $search_param);
		}
		else
		{
			$totalrecord = $DB->count_records($name, $condition) ;
		}
	}
	
	$start = ($page * $perpage);
	if ($start > $totalrecord) {
		$page = 0;
		$start = 0;
	}

	//now get the proper query
	if(isset($options['sql']) && $options['sql'] != '')
	{
		$sql = $options['sql'];
		if($search != '' && $field != '')
		{
			if($conditionWhere != '')
				$actualCondition = ' and ' . $conditionWhere;
			else
				$actualCondition = '';
//			$sql = $sql . " WHERE $field like '%$search%' $actualCondition";
			
			$sql = $sql . " WHERE " . $DB->sql_like($field, ':field', false) . ' '  . $actualCondition;
			$search_param = array('field' => "%$search%");			
			
		}
		else
		{
			$search_param = array();
			if($conditionWhere != '')
				$sql = $sql . ' WHERE ' . $conditionWhere;
		}
		if($sort_text != '')
			$sql = $sql . " order by $sort_text";
		$records = $DB->get_records_sql($sql, $search_param, $start, $perpage); // Start at result '$start' and return '$perpage'
	}
	else //use table name
	{		
		if($search != '' && $field != '')
		{
			if($conditionWhere != '')
				$actualCondition = ' and ' . $conditionWhere;
			else
				$actualCondition = '';
//			$sql = "SELECT * FROM {".$name."} WHERE $field like '%$search%' $actualCondition";

			$sql = "SELECT * FROM {".$name."} WHERE " . $DB->sql_like($field, ':field', false) . ' '  . $actualCondition;
			$search_param = array('field' => "%$search%");			

			if($sort_text != '')
				$sql = $sql . " order by $sort_text";
			$records = $DB->get_records_sql($sql, $search_param, $start, $perpage); // Start at result '$start' and return '$perpage'
		}
		else
		{
			if(isset($options['conditionText']) && $options['conditionText'] != '') 
			{
				if($conditionWhere != '')
					$actualCondition = ' WHERE ' . $conditionWhere;
				else
					$actualCondition = '';
				$sql = "SELECT * FROM {".$name."} $actualCondition";
				if($sort_text != '')
					$sql = $sql . " order by $sort_text";
				$records = $DB->get_records_sql($sql, array(), $start, $perpage); // Start at result '$start' and return '$perpage'
			}
			else
				$records = $DB->get_records($name, $condition, $sort_text, '*', $start, $perpage);
		}
	}
	$table = new html_table();
	if(!isset($options['table_class']) || $options['table_class'] == '')
		$table->attributes['class'] = 'generaltable table-bordered table-striped';
	else
		$table->attributes['class'] = $options['table_class'];
	if(isset($options['responsive']) && $options['responsive']) //add responsive capability
		$table->attributes['class'] .= ' table-responsive';
	if(isset($options['condensed-table']) && $options['condensed-table']) //make the table condensed
		$table->attributes['class'] .= ' table-sm';
	if(isset($options['hover-table']) && $options['hover-table']) //add hover capability
		$table->attributes['class'] .= ' table-hover';
	if(isset($options['border-table']) && $options['border-table']) //add hover capability
		$table->attributes['class'] .= ' table-bordered';
	$table->width = "100%";
	$no_record = '';
	if(count($fields) == 0) //not provide any field, just dump it all
	{
		//get one of the variable for the fields
		foreach($records as $rec)
		{
			$vars = get_object_vars($rec);
			break;
		}
		if(!isset($vars)) //no record
		{
//			$no_record = jra_ui_alert(get_string('no_record_found', 'local_jra'), 'info', false, true);
		}
		else
		{
			if(!isset($options['numbering']) || $options['numbering']) //use numbering
				$fields['#'] = array(); //add the numbering
			foreach($vars as $key => $value)
			{
				$fields[$key] = array(
								'align' => 'left',
								'size' => '',
								'header' => $key,
						);
			}
			if(isset($options['action']) && $options['action'])			
				$fields['*'] = array(); //add the action column
		}
	}
	$search_init = optional_param('search', $search, PARAM_TEXT);
	$field_init = optional_param('field', $field, PARAM_TEXT);
	
	//for the header
	foreach($fields as $key => $field)
	{
		if(isset($field['visible']) && !$field['visible'])
			continue;
		if($key == '#') //number
		{
			$table->head[] = get_string('no', 'local_jra');
			$table->align[] = 'center';
			$table->size[] = '5%';
		}
		else if($key == '$') //number
		{
			$table->head[] = jra_ui_checkbox('check-all', false, 1, "
				if($('#check-all').prop('checked'))
				{
					// Iterate each checkbox
					$(':checkbox').each(function() {
						this.checked = true;                        
					});					
				}
				else
				{
					// Iterate each checkbox
					$(':checkbox').each(function() {
						this.checked = false; 
					});					
				}
			");
			$table->align[] = 'center';
			$table->size[] = '5%';
		}
		else if($key == '*') //action
		{
			$table->head[] = get_string('action');
			$table->align[] = 'center';
			$table->size[] = '10%';				
		}
		else if($key == '#c') //custom field
		{
			$table->head[] = $field['header'];
			$table->align[] = isset($field['align']) ? $field['align'] : 'left';
			$table->size[] = isset($field['size']) ? $field['size'] : '';
		}
		else
		{
			if(isset($options['sortable']) && $options['sortable'])
			{
				$order = optional_param('order', $initial_sort, PARAM_TEXT);
				if($order == 'desc')
					$order = 'asc';
				else
					$order = 'desc';
				$params = $_GET;
				$params['search'] = $search_init;
				$params['field'] = $field_init;
				if(isset($field['sort']) && $field['sort'] != '')
					$params['sort'] = $field['sort'];
				else
					$params['sort'] = $key;
				$params['order'] = $order;				
				$sort_url = new moodle_url($PAGE->url->out_omit_querystring(), $params);
				$header_text = isset($field['header']) ? $field['header'] : get_string($key, $lang_name);
				$table_header = html_writer::link($sort_url, $header_text, array('title' => get_string('sort_by', 'local_jra') . ' ' . $header_text));
			}
			else
				$table_header = isset($field['header']) ? $field['header'] : get_string($key, $lang_name);
			$table->head[] = $table_header;
			$table->align[] = isset($field['align']) ? $field['align'] : 'left';
			$table->size[] = isset($field['size']) ? $field['size'] : '';
		}
	}
	//for the content		
	$is_admin = jra_is_system_admin();	
	$delete_admin = isset($options['delete_admin']) ? $options['delete_admin'] : false;
	$count = $start + 1;
	if(!isset($options['detail_field']))
		$detail_field = 'id';
	else
		$detail_field = $options['detail_field'];	
	if(!isset($options['detail_var']))
		$detail_var = 'id';
	else
		$detail_var = $options['detail_var'];
	$prevData = false;
	foreach($records as $rec)
	{		
		if(isset($options['detail_page']))
			$detail_url = new moodle_url($options['detail_page'], array($detail_var => $rec->$detail_field));
		else
			$detail_url = new moodle_url('view.php', array($detail_var => $rec->$detail_field));
		foreach($fields as $key => $field)
		{
			if(isset($field['visible']) && !$field['visible'])
				continue;
			if($key == '#') //numbering
				$data[$key] = $count;
			else if($key == '$') //check box
			{
				$check_name = 'chk_' . $rec->$detail_field;
				$data[$key] = jra_ui_checkbox($check_name, false, $rec->$detail_field, "
					$('#$check_name').change(function()
					{
						var input = document.getElementsByTagName('input');
						var checkAll = true;
						for(var i = 0; i < input.length; i++) 
						{
							if(input[i].type == 'checkbox') 
							{
								if(input[i].name != '' && input[i].name != 'check-all')
								{
									if(input[i].checked == false)
									{
										checkAll = false;
										break;
									}
								}
							}  
						}	
						if(checkAll)
							$('#check-all').prop('checked', true)
						else
							$('#check-all').prop('checked', false)
					});					
				");
			}
			else if($key == '*') //action
			{
				$action_txt = '';
				$action_array = $field; //get the array
//				if(count($action_array) == 0) //nothing provided, so default to edit and delete
				{
					if(isset($options['view_page']) && $options['view_page'] != '' && !isset($action_array['view']))
						$action_array['view'] = array();
					if(isset($options['edit_page']) && $options['edit_page'] != '' && !isset($action_array['edit']))
						$action_array['edit'] = array();
					if(!isset($options['disable_delete']) || (isset($options['disable_delete']) && $options['disable_delete'] === false))
					{
						if(!$delete_admin || $is_admin)
						{
							if(!isset($action_array['delete']))
								$action_array['delete'] = array();
						}
					}
				}
				//this is the javascript for view, edit and delete. Must have a javascript function with the same name
				if(isset($action_array['view']))
				{
					$action_opt = $action_array['view'];
					if(isset($action_opt['icon']))
						$action_icon = $action_opt['icon'];
					else
						$action_icon = 'search';
					if(isset($action_opt['title']))
						$action_title = $action_opt['title'];
					else
						$action_title = 'view';
					$view_params = array($detail_var => $rec->{$detail_field});
					if(isset($options['view_page_params']) && is_array($options['view_page_params']))
					{
						$view_params = array_merge($view_params, $options['view_page_params']);
					}
					$view_url = new moodle_url($options['view_page'], $view_params);
					$showAction = true;
					if(isset($action_opt['condition']))
					{
						foreach($action_opt['condition'] as $cond_key => $cond_value)
							break;
						if($rec->{$cond_key} != $cond_value)
							$showAction = false;
					}
					if($showAction)
						$action_txt = $action_txt . html_writer::link($view_url, jra_ui_icon($action_icon, '1.3', true), array('title' => get_string($action_title, 'local_jra'))) . ' '; 
				}
				if(isset($action_array['edit']))
				{
					$action_opt = $action_array['edit'];
					if(isset($action_opt['icon']))
						$action_icon = $action_opt['icon'];
					else
						$action_icon = 'pencil';
					if(isset($action_opt['title']))
						$action_title = $action_opt['title'];
					else
						$action_title = 'edit';
					if(isset($action_opt['params'])) //custom paramegers
					{
						$edit_params = array();
						foreach($action_opt['params'] as $param_key => $param_value)
						{
							if($param_value != '#id') //this is for all other custom parameters
								$edit_params[$param_key] = $param_value;
							else //this is for the key
								$edit_params[$param_key] = $rec->{$detail_field};
						}
					}
					else
					{
						$edit_params = array($detail_var => $rec->{$detail_field});
						if(isset($options['edit_page_params']) && is_array($options['edit_page_params']))
						{
							$edit_params = array_merge($edit_params, $options['edit_page_params']);
						}
					}
					$edit_url = new moodle_url($options['edit_page'], $edit_params);
					$showAction = true;
					if(isset($action_opt['condition']))
					{
						foreach($action_opt['condition'] as $cond_key => $cond_value)
							break;
						if($rec->{$cond_key} != $cond_value)
							$showAction = false;
					}
					if($showAction)
						$action_txt = $action_txt . html_writer::link($edit_url, jra_ui_icon($action_icon, '1.3', true), array('title' => get_string($action_title, 'local_jra'))) . ' '; 
				}
				if(isset($action_array['delete']))
				{
					$action_opt = $action_array['delete'];
					if(isset($action_opt['icon']))
						$action_icon = $action_opt['icon'];
					else
						$action_icon = 'trash';
					if(isset($action_opt['title']))
						$action_title = $action_opt['title'];
					else
						$action_title = 'delete';
					$qs = $_GET;
					unset($qs['search']); //have to remove existing query string
					unset($qs['field']); //have to remove existing query string
					$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
					$delete_url = "javascript:delete_$name('".$rec->$detail_field."', '".$a_url->out(false)."')";
					$showAction = true;
					if(isset($action_opt['condition']))
					{
						foreach($action_opt['condition'] as $cond_key => $cond_value)
							break;
						if($rec->{$cond_key} != $cond_value)
							$showAction = false;
					}
					if($showAction)
						$action_txt = $action_txt .  html_writer::link($delete_url, jra_ui_icon($action_icon, '1.3', true), array('title' => get_string($action_title, 'local_jra'))) . ' ';		
				}
				//custom button
				foreach($action_array as $action_key => $action_action)
				{
					if($action_key != 'view' && $action_key != 'edit' && $action_key != 'delete')
					{
						$showAction = true;
						if(isset($action_action['condition']))
						{
							foreach($action_action['condition'] as $cond_key => $cond_value)
								break;
							if($rec->{$cond_key} != $cond_value)
								$showAction = false;
						}
						if($showAction)
						{
							if(isset($action_action['id']))
								$the_url = new moodle_url($action_action['url'], array('id' => $rec->id));
							else
								$the_url = $action_action['url'];
							$action_txt = $action_txt .  html_writer::link($the_url, $action_action['icon'], array('title' => $action_action['title'], 'target' => $action_action['target'])) . ' ';		
						}
					}
				}
				$data[$key] = $action_txt;
			}
			else if($key == '#c') //custom field
			{
				$custom_key = $field['field'];
				if($field['format'] == 'attendance_highlight')
				{
					$percentage = $rec->$custom_key;
					$color_list = $field['lookup_list'];
					$found = false;
					foreach($color_list as $color)
					{
						if($percentage >= $color['max'])
						{
							$found = true;
							break;
						}
					}
					if($found)
					{
						$text = '<div class="' . $color['color'] . ' text-success border border-dark">&nbsp;</div>';
					}
					else //no highlight
					{
					}
				}
				else
					$text = $rec->$custom_key;
				$data[$key] = $text; //we write as it is first to allow previous data to have full data
			}
			else //all other fields
			{				
				if(isset($field['format']))
				{
					if($field['format'] == 'date')
					{
						if($rec->$key == 0)
							$text = $field['empty_date'];
						else
							$text = jra_output_formal_date($rec->$key);
					}
					else if($field['format'] == 'datetime')
						$text = jra_output_formal_datetime($rec->$key);
					else if($field['format'] == 'decimal')
						$text = number_format($rec->$key, 2);
					else if($field['format'] == 'percent')
						$text = $rec->$key . '%';
					else if($field['format'] == 'week')
						$text = get_string('week', 'local_jra') . ' ' .  str_pad($rec->$key, 2, '0', STR_PAD_LEFT);
					else if($field['format'] == 'yesno')
						$text = jra_output_show_yesno($rec->$key);
					else if($field['format'] == 'country')
						$text = jra_lookup_countries($rec->$key);
					else if($field['format'] == 'lookup')
					{
						$text = $field['lookup_list'][$rec->$key];
						if($text == '') //sometimes, when the field is integer, empty will be 0. So search again with ''
							$text = $field['lookup_list'][''];
					}
					else if($field['format'] == 'static') //static text. Take it from custom value
					{
						$text = $field['static_value'];
					}
					else if($field['format'] == 'campus')
						$text = jra_output_show_campus($rec->$key);
					else if($field['format'] == 'iban')
						$text = jra_output_iban($rec);
					else if($field['format'] == 'grade_lock')
					{
						$ob = new stdClass();
						$ob->level1 = $rec->level1;
						$ob->level2 = $rec->level2;
						$ob->level3 = $rec->level3;
						$grade_status = jra_course_grade_final_grade_status($ob);
						$text = get_string($grade_status, 'local_jra');
					}
					else if($field['format'] == 'combine')
					{
						$cmb = '';
						foreach($field['combine'] as $c)
						{
							if(isset($field['lang']) && $field['lang'] == true)
								$ct = $rec->$c == '' ? '' : get_string($rec->$c, $lang_name);
							else
								$ct = $rec->$c;
							if($cmb != '')
								$cmb = $cmb . ' ';
							$cmb = $cmb . $ct;
						}
						$text = $cmb;
					}
					else if($field['format'] == 'currency')
					{
						if($rec->$key < 0) //negative value
							$text = jra_output_format_deduction(jra_output_currency($field['currency'], $rec->$key));
						else
							$text = jra_output_currency($field['currency'], $rec->$key);
					}
					else if($field['format'] == 'currency_negative')
					{
						$text = jra_output_format_deduction(jra_output_currency($field['currency'], $rec->$key));
					}
					else if($field['format'] == 'json')
					{
						$a = json_decode($rec->$key);
						$a_str = implode(', ', $a);
						$text = $a_str;
					}
					else if($field['format'] == 'status_icon')
					{
						$text = $field['icon_list'][$rec->$key];
					}
					else //fallback, just display
					{
						if(isset($field['lang']) && $field['lang'] == true)
							$text = $rec->$key == '' ? '' : get_string($rec->$key, $lang_name);
						else
							$text = $rec->$key;
					}
				}
				else if($key == 'eff_status')
					$text = jra_output_show_active($rec->$key);
				else if($key == 'gender')
					$text = jra_output_show_gender($rec->$key);
				else
				{
					if(isset($field['lang']) && $field['lang'] == true)
						$text = $rec->$key == '' ? '' : get_string($rec->$key, $lang_name);
					else
						$text = $rec->$key;
				}
				if($text == '' && isset($field['empty_text']) && $field['empty_text'] != '')
					$text = $field['empty_text'];
				if(isset($options['detail_link']) && $options['detail_link'])
				{
					$attributes = array('title' => get_string('view_details', 'local_jra'));
					if(isset($options['detail_target']) && $options['detail_target'] != '')
						$attributes['target'] = $options['detail_target'];
					$text = html_writer::link($detail_url, $text, $attributes);
				}
				$data[$key] = $text; //we write as it is first to allow previous data to have full data
			}
		}
		$tempData = $data;		
		//check if we need to do grouping
		$prevKey = false;
		foreach($fields as $key => $field)
		{
			if(isset($field['visible']) && !$field['visible'])
				continue;
			if(isset($field['group']) && $field['group'] == true) //need to group
			{
				if($prevData !== false)
				{
					$prevText = $prevData[$key];
					if($prevText == $data[$key]) //if grouping
					{
						//check if the previous data is empty. Don't empty it if the previous data is not empty
						//this will avoid having grouped with previous row
						if($prevKey === false || $data[$prevKey] == '')
						{
							$data[$key] = ''; //empty the content
						}
					}
				}
				$prevKey = $key;
			}
		}		
		$table->data[] = $data;
		$prevData = $tempData; //we take full data
		unset($data);
		$count++;
	}
//	if($count == 1) //no record
//		$no_record = jra_ui_alert(get_string('no_record_found', 'local_jra'), 'info', false, true);
	$output = '';
	if(!isset($options['search']) || $options['search']) //by default allow search
	{
		$search_options = array();
		foreach($fields as $key => $value)
		{
			if($key != '#' && $key != '$' && $key != '*')
			{
				if(!isset($value['disable_search']) || !$value['disable_search'])
				{
					if(isset($value['search_key']))
						$search_key = $value['search_key'];
					else
						$search_key = $key;
					$search_options[$search_key] = isset($value['header']) ? $value['header'] : get_string($key, $lang_name);
				}
			}
		}
		//check if there is existing search data
		if($field_init == '') //no initial field, check if there is default
			$field_init = isset($options['default_search_field']) ? $options['default_search_field'] : '';

		$qs = $_GET;
		unset($qs['search']); //have to remove existing query string
		unset($qs['field']); //have to remove existing query string
		$a_url = new moodle_url($PAGE->url->out_omit_querystring(), $qs);
		$search_url = "javascript:search_$name('".$a_url->out(false)."')";
		$searchTxt = '<strong>'.get_string('search').'</strong>' . jra_ui_space(3) . 
					jra_ui_input('search', '25', $search_init, "handleKeyPressSearch(event, '".$a_url->out(false)."')", 30) . jra_ui_space(3) . 
					jra_ui_select('field', $search_options, $field_init) . 
					jra_ui_space(3) . jra_ui_button(get_string('refresh'), $search_url, 'primary', '', 'btn-sm');
		if(isset($options['master_filter']) && $options['master_filter'] != '')
		{
			$searchTxt = $searchTxt . $options['master_filter'];
		}
		if(!isset($options['print']) || $options['print'] == false)
			$output .= jra_ui_box('', $searchTxt);
	}
	$output .= html_writer::table($table);
	if(isset($options['add_form']) && $options['add_form'] == true)
	{
		if(isset($options['form_action_url']) && $options['form_action_url'] != '')
			$form_action_url = $options['form_action_url'];
		else
			$form_action_url = '';
		$output = '<form id="form_chk" name="form_chk" action="' . $form_action_url . '" method="post">' . $output;
		if(isset($options['form_submit_button']) && $options['form_submit_button'] != '')
		{
			$output .= '<div class="text-center pt-3">';
			$output .= '<input type="submit" id="submitbutton" name="submitbutton" value="' . $options['form_submit_button'] . '" class="btn btn-primary">';
			$output .= '</div>';
		}
		$output .= '</form>';
	}
	$base_url = new moodle_url($PAGE->url->out_omit_querystring(), $_GET);
	$output .= $OUTPUT->paging_bar($totalrecord, $page, $perpage, $base_url) . '(Total Records : '.$totalrecord.')';
	
	$output .= $no_record;
	$output .= '<form name="form_list" method="post">';
	$output .= jra_ui_hidden('delete_id', '');
	$output .= jra_ui_hidden('selected_id', '');
	$output .= jra_ui_hidden('search_text', '');
	$output .= jra_ui_hidden('search_field', '');
	$output .= '</form>';
	$output .= '<br />';
	if(!isset($options['search_reference']) || $options['search_reference'] == true)
		$output .= jra_ui_search_reference($fields); //check if we need to build the search reference table
	//universal javascript added
	$output .= '
		 <script>
			function delete_'.$name.'(id, action_url)
			{
				if(confirm("'.get_string('confirm_delete', 'local_jra').'"))
				{
					document.form_list.delete_id.value = id;
					if(action_url != "")
						document.form_list.action = action_url;
					document.form_list.submit();
				}
			}
			
			function search_'.$name.'(url)
			{
				var search = document.getElementById("search").value;
				var field = document.getElementById("field").value;
				url = addQSParam(url, "search", search);
				url = addQSParam(url, "field", field);
				document.location = url;
			}
			
			function handleKeyPressSearch(e, url)
			{
				var key=e.keyCode || e.which;
				if (key==13)
				{
					search_'.$name.'(url);
				}
			}

		</script>
	';
	if($ret)
		return $output;
	else
		echo $output;
}

function jra_ui_search_reference($fields)
{
	$data = array();
	$list = array();
	foreach($fields as $key => $field)
	{
		if(isset($field['show_reference']) && $field['show_reference'] == true)
		{
			if(isset($field['format']))
			{
				if($field['format'] == 'yesno')
				{
					$arr = jra_lookup_yes_no();
					if(isset($field['header']))
						$k = $field['header'];
					else
						$k = get_string($key, 'local_jra');
					$list[$k] = $arr;
				}
				else if($field['format'] == 'lookup')
				{
					$arr = $field['lookup_list'];
					if(isset($field['header']))
						$k = $field['header'];
					else
						$k = get_string($key, 'local_jra');
					$list[$k] = $arr;
				}
			}
			else if($key == 'eff_status')
			{
				$arr = jra_lookup_is_active();
				if(isset($field['header']))
					$k = $field['header'];
				else
					$k = get_string($key, 'local_jra');
				$list[$k] = $arr;
			}
			else if($key == 'gender')
			{
				$arr = jra_lookup_gender();
				if(isset($field['header']))
					$k = $field['header'];
				else
					$k = get_string($key, 'local_jra');
				$list[$k] = $arr;
			}
		}
	}
	if(count($list) == 0) //nothing
		return '';
	else
	{
		foreach($list as $k => $s)
		{
			$lookup_list = '';
			foreach($s as $f => $v)
			{
				if($f == '')
					continue;
				if($lookup_list != '')
					$lookup_list = $lookup_list . '<br />';
				$lookup_list = $lookup_list . $f . ' => ' . $v;
			}
			$lookup_list = '<h6>' . $k . '</h6>' . $lookup_list;
			$lookup_list = '<code>' . $lookup_list . '</code>';
			//one row of data
			$obj = new stdClass();
			$obj->content = $lookup_list;
			$data[] = $obj;
			//end of data row	
		}
		$str = jra_ui_grid_column($data, 4);	
		$str = jra_ui_box($str, get_string('search_references', 'local_jra'), '', true);
	
		$data = array();
		//one row of data
		$obj = new stdClass();
		$obj->column = 2;
		$obj->left_content = $str;
		$obj->right_content = '';
		$data[] = $obj;
		$str = jra_ui_multi_column($data, 8);	
		return $str;
	}
}

//format data in 2 columns, with title on the left and data on the right. Accept an associative array of object
//left is the size of the title. The right is automatically minus from left based on bootstrap 12 columns
function jra_ui_data_detail($data, $left = 3, $padding = 2, $edit = false)
{
	$str = '';
	if(!$edit)
		$right = 12 - $left;
	else
		$right = 11 - $left;
	$str = $str . '<div class="row">';
	foreach($data as $d)
	{
		if(isset($d->full) && $d->full) //use full width
		{
			$str = $str . '	<div class="col-md-12 pt-' . $padding . '">';
			$str = $str . $d->content;
			$str = $str . '	</div>';
		}
		else
		{
			$str = $str . '	<div class="col-md-' . $left . ' pt-' . $padding . '">';
			$str = $str . '<strong>' . $d->title . '</strong>';
			$str = $str . '	</div>';
			$str = $str . '	<div class="col-md-' . $right . ' pt-' . $padding . '">';
			$str = $str . $d->content;
			$str = $str . '	</div>';
			if($edit)
			{
				$str = $str . '	<div class="col-md-1' . ' pt-' . $padding . '">';
				$str = $str . $d->edit;
				$str = $str . '	</div>';
			}
		}
	}
	$str = $str . '</div>';
	return $str;
}

//put data to 1, 2 or 3 columns, left and right. It can also support 3 columns, but the with will be fixed at 4 x 4 x 4
function jra_ui_multi_column($data, $left = 6, $center = 6, $padding = 2)
{
	$str = '';
	$str = $str . '<div class="row">';
	foreach($data as $d)
	{
		if($d->column == 1) //use full width
		{
			$str = $str . '	<div class="col-md-12 pt-' . $padding . '">';
			$str = $str . $d->left_content;
			$str = $str . '	</div>';
		}
		else if($d->column == 2)
		{
			$right = 12 - $left;
			$str = $str . '	<div class="col-md-' . $left . ' pt-' . $padding . '">';
			$str = $str . $d->left_content;
			$str = $str . '	</div>';
			$str = $str . '	<div class="col-md-' . $right . ' pt-' . $padding . '">';
			$str = $str . $d->right_content;
			$str = $str . '	</div>';
		}
		else if($d->column == 3)//3 columns
		{
			$x = 12 - $left;
			$right = $x - $center;
			$str = $str . '	<div class="col-md-' . $left . ' pt-' . $padding . '">';
			$str = $str . $d->left_content;
			$str = $str . '	</div>';
			$str = $str . '	<div class="col-md-' . $center . ' pt-' . $padding . '">';
			$str = $str . $d->center_content;
			$str = $str . '	</div>';
			$str = $str . '	<div class="col-md-' . $right . ' pt-' . $padding . '">';
			$str = $str . $d->right_content;
			$str = $str . '	</div>';
		}
		else //4 columns, break evenly into 4 columns
		{
			$str = $str . '	<div class="col-md-3 pt-' . $padding . '">';
			$str = $str . $d->left_content;
			$str = $str . '	</div>';
			$str = $str . '	<div class="col-md-3 pt-' . $padding . '">';
			$str = $str . $d->center_content;
			$str = $str . '	</div>';
			$str = $str . '	<div class="col-md-3 pt-' . $padding . '">';
			$str = $str . $d->right_content;
			$str = $str . '	</div>';
			$str = $str . '	<div class="col-md-3 pt-' . $padding . '">';
			$str = $str . $d->last_content;
			$str = $str . '	</div>';
		}
	}
	$str = $str . '</div>';
	return $str;
}

//put data to a fix size span into a grid of equal size column
function jra_ui_grid_column($data, $column, $padding = 2)
{
	$str = '';
	$str = $str . '<div class="row">';
	foreach($data as $d)
	{
		$str = $str . '	<div class="col-md-' . $column . ' pt-' . $padding . '">';
		$str = $str . $d->content;
		$str = $str . '	</div>';
	}
	$str = $str . '</div>';
	return $str;
}

//this is special SIS tab that use bootstrap tab. Different from typical moodle tab implementation
function jra_ui_tab($tab_pages, $padding_top = 'pt-3')
{
	$content =  '	
		<div class="' . $padding_top . '">
		  <div class="card">
				<div class="card-header">
					<ul class="nav nav-tabs card-header-tabs" role="tablist">';
	foreach($tab_pages as $t)
	{
		if(isset($t['target']))
			$target = ' target="' . $t['target'] .'"';
		else
			$target = '';
		$content = $content . '<li class="nav-item">
						<a class="nav-link '.$t['active'].'" href="'.$t['url'].'" ' .  $target . '>' . $t['title'] .'</a>
					  </li>';
	}
	$content = $content . '</ul>
				</div>
				<div class="card-body">
					<div class="tab-content border border-0 p-0">';
	foreach($tab_pages as $t)
	{
		$content = $content . '<div role="tabpanel" class="tab-pane fade in '.$t['active'].'" id="main">';
		$content = $content . $t['content'];
		$content = $content . '</div>';
	}
	$content = $content . '</div>
				</div>
			</div>
		</div>
	';
	return $content;	
}

//create a progress bar
function jra_ui_progress_bar($percentage, $color, $text_color, $height = '25', $ret = true)
{
	if($percentage == 0)
		$percentage_text = $percentage . '%';
	else
	{
		$percentage_text = $percentage . '%';
		if($percentage < 15)
			$percentage_width = $percentage;
		else
			$percentage_width = $percentage;
	}
	$str = '
		<div class="progress border border-secondary" style="height:' . $height . 'px">
		  <div class="progress-bar ' . $color . '" style="width:' . $percentage_width . '%;height:' . $height . 'px"><div class="text-nowrap '.$text_color.'">' . $percentage_text . '</div></div>
		</div> 	
	';
	if($ret)
		return $str;
	else
		echo $str;
}

//show the next, previous record
function jra_ui_record_navigator($records, $current_id, $page, $field = 'id', $extra_params = array(), $ret = true)
{
	$lang = current_language();
	if($lang == 'ar') //arabic, flip the arror
	{
		$left_arrow = 'right';
		$right_arrow = 'left';
	}
	else
	{
		$left_arrow = 'left';
		$right_arrow = 'right';
	}
	$total = count($records);
	//find the count of current record
	$count = 1;
	$current = 1;
	$prevId = 0;
	$afterCurrent = false;
	$previous = jra_ui_icon('chevron-circle-' . $left_arrow, '1', true);
	$next = jra_ui_icon('chevron-circle-' . $right_arrow, '1', true);
	$first_id = 0;
	foreach($records as $r)
	{
		if($count == 1) //first record
		{
			$first_url = new moodle_url($page, array_merge(array($field => $r->id), $extra_params));
			$first = html_writer::link($first_url, get_string('first'), array());
			$first_id = $r->id;
		}
		if($afterCurrent) //one index after current
		{
			$next_url = new moodle_url($page, array_merge(array($field => $r->id), $extra_params));
			$next = html_writer::link($next_url, jra_ui_icon('chevron-circle-' . $right_arrow, '1', true), array());
			$afterCurrent = false;
		}
		if($r->id == $current_id)
		{
			$current = $count;
			if($prevId != 0) //has previous
			{
				$previous_url = new moodle_url($page, array_merge(array($field => $prevId), $extra_params));
				$previous = html_writer::link($previous_url, jra_ui_icon('chevron-circle-' . $left_arrow, '1', true), array());
			}
			$afterCurrent = true;
		}
		$count++;
		$prevId = $r->id;
	}
	$last_url = new moodle_url($page, array_merge(array($field => $r->id), $extra_params));
	$last = html_writer::link($last_url, get_string('last'), array());

	$str = '';
	$str = $str . $first;
	$str = $str . '&nbsp;&nbsp;|&nbsp;&nbsp;';
	$str = $str . $previous;
	$str = $str . '&nbsp;&nbsp;';
	$str = $str . $current . ' ' . get_string('of', 'local_jra') . ' ' . $total;
	$str = $str . '&nbsp;&nbsp;';
	$str = $str . $next;
	$str = $str . '&nbsp;&nbsp;|&nbsp;&nbsp;';
	$str = $str . $last;
	if($ret)
		return $str;
	else
		echo $str;
}

//blank is the first item that does nothing
//size = btn-sm for small
function jra_ui_dropdown_menu($items, $text, $type = 'primary', $size = '', $ret = true)
{
	$str = '';
	$str = '<div class="dropdown">
	  <button class="btn btn-' . $type . ' ' . $size . ' dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		' . $text . '
	  </button>
	  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
	foreach($items as $item)
	{
		if($item['title'] == '-')
			$str = $str . '<div class="dropdown-divider"></div>';
		else
		{
			$target = '';
			$icon = '';
			if($item['target'] != '')
				$target = 'target="' . $item['target'] . '"';
			if($item['icon'] != '')
				$icon = jra_ui_icon($item['icon'], '', true) . ' ';
			$str = $str . '<a class="dropdown-item" href="' . $item['url'] . '" '.$target.'>' . $icon . $item['title'] . '</a>';
		}
	  }	  
	  $str = $str . '</div>
	</div>';	
	if($ret)
		return $str;
	else
		echo $str;
}

//the recursive function that extract a node
function jra_ui_treeview_node($data, $node_id_text, $session_name, $always_expanded = false)
{
	$str = '<ul>';
	$count = 0;
	$path = jra_get_session($session_name);
	foreach($data as $key => $node)
	{
		$count_text = $node_id_text . $count;
		$str = $str . '<li>';
		if(is_array($node))
		{
			if($always_expanded)
				$expanded = 'checked="checked"';
			else
				$expanded = '';
			if(strlen($path) >= strlen($count_text) && !$always_expanded)
			{
				$a = substr($path, 0, strlen($count_text));
				if($a == $count_text)
					$expanded = 'checked="checked"'; //text to expand the tree. Empty text to collapse it
			}
			$str = $str . '<input type="checkbox" id="node-' . $count_text . '" ' . $expanded . ' /><label for="node-' . $count_text . '">' . $key . '</label>';
			$str = $str . jra_ui_treeview_node($node, $count_text . '-', $session_name, $always_expanded);
		}
		else
		{
			if($node->url != '')
			{
				$url_attribute = array();
				if(isset($node->url_raw) && $node->url_raw) //this is for javascript url
				{
					$text = html_writer::link($node->url, $node->title, $url_attribute);
				}
				else
				{
					if($count_text == $path) //highlight the selected node
						$url_attribute['class'] = 'font-weight-bold bg-light';
					$node->url_params['path'] = $count_text;
					$url = new moodle_url($node->url, $node->url_params);
					$text = html_writer::link($url, $node->title, $url_attribute);
				}				
			}
			else
				$text = $node->title;
			$str = $str . '<label form="node-' . $count_text . '">' . $text . '</label>';
		}
		$str = $str . '</li>';		
		$count++;
	}
	$str = $str . '</ul>';
	return $str;
}
/* Build a tree structure
$data is an associative array with key as the node. The leaf node contain the final field which must be na object
*/
function jra_ui_treeview($data, $session_name, $always_expanded = false, $ret = true)
{
	$str = '<div class="acidjs-css3-treeview">'; //start of tree
	$str = $str . jra_ui_treeview_node($data, '', $session_name, $always_expanded);
	$str = $str . '</div>'; //end of tree
	/* Sample tree
	$str = '
		<div class="acidjs-css3-treeview">
			<ul>
				<li>
					<input type="checkbox" id="node-0" checked="checked" /><label for="node-0">Libraries</label>
					<ul>
						<li>
							<input type="checkbox" id="node-0-0" checked="checked" /><label for="node-0-0">Documents</label>
							<ul>
								<li>
									<input type="checkbox" id="node-0-0-0" checked="checked" /><label for="node-0-0-0">My Documents</label>
									<ul>
										<li>
											<label form="node-0-0-0-0">Downloads</label>
										</li>
										<li>
											<label form="node-0-0-0-1"><a href="">Projects</a></label>
										</li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	';
	*/
	if($ret)
		return $str;
	else
		echo $str;
}