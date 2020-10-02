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

// This is the library for global RCYCI functions 
defined('MOODLE_INTERNAL') || die();

function jra_file_get_extension($filename)
{
	$x = explode('.', $filename);
	if(count($x) > 1) //sometimes a file has no extension
		$ext = end($x);
	else
		$ext = '';
	return $ext;

//	$filetype = get_mimetypes_array();

}

function jra_file_supporting_document_path($semester)
{
	global $CFG;
	$file_path = $CFG->dataroot . '/applicant/doc/' . $semester . '/';
	//make sure directory exist. If not create it
	if(!file_exists($file_path))
	{
		mkdir($file_path, 0777, true);
	}
	return $file_path;
}

function jra_file_delete_file($file_path)
{
	if(file_exists($file_path))
	{
		unlink($file_path);
	}	
}

function jra_file_accepted_document_type()
{
	$arr = array();
	$arr[] = 'bmp';
//	$arr[] = 'doc';
//	$arr[] = 'docx';
	$arr[] = 'eps';
	$arr[] = 'pdf';
	$arr[] = 'gif';
	$arr[] = 'png';
	$arr[] = 'jpe';
	$arr[] = 'jpg';
	$arr[] = 'jpeg';
	$arr[] = 'ps';
	$arr[] = 'tif';
	$arr[] = 'tiff';
	return $arr;
}