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

require_once $CFG->libdir.'/tcpdf/tcpdf.php';

//in html printable page, call this override to allow Chrome to set orientation in landscape
//call it after $OUTPUT->header()
function sis_print_css_override()
{
	return '
	<style>
	@page {
	  size: auto;
	}
	</style>
	';
}

//get the logo of institution by URL
function sis_print_logo($institute, $ext = 'jpg', $title = '', $width = 100, $height = 100)
{
	if($title == '')
		$title = strtoupper($institute) . ' '  . get_string('logo', 'local_sis');
	$logo_url = new moodle_url('/local/sis/images/institute/' . strtolower($institute) . '/logo.' . $ext);
	$str = '<div class="">' . html_writer::empty_tag('img', array('src' => $logo_url, 'alt' => $title, 'width'=>$width, 'height' => $height)) . '</div>';
	return $str;
}

function sis_print_pdf_logo_url($institute)
{
	$path = sis_print_pdf_image_path($institute);
	return new moodle_url($path . '/logo.jpg');
}

function sis_print_pdf_image_path($institute)
{
	return '../../../local/sis/images/institute/' . strtolower($institute);
}

function sis_print_generic_header($options)
{
	$str = '<div class="pb-3">';
	$str = $str . '<table width="100%" border="0" cellpadding="5">';
	$str = $str . '<tr valign="top">';
	$str = $str . '<td width="15%">'.sis_print_logo($options['institute']).'</td>';
	$str = $str . '<td width="90%">';
	$str = $str . '<h4>' . $options['title'] . '</h4>';
	$str = $str . '<h4>' . $options['subject'] . '</h4>';
	$str = $str . '<h4>' . $options['detail'] . '</h4>';
	$str = $str . '</td>';
	$str = $str . '</tr>';		
	$str = $str . '</table>';
	$str = $str . '</div>';
	return $str;
}

function sis_print_pdf_css($fontSize=10)
{
	$html = '
	<style>
	</style>
	';
	return $html;
}

//dump table specifically for printing. We have to use raw html table
function sis_print_dump_table($data, $table, $fields)
{
	$str = '';
	$str = $str . '<table width="' . $table->width . '" border="' . $table->border . '" cellpadding="' . $table->cellpadding . '" cellspacing="' . $table->cellspacing . '">';
	$str = $str . '<thead>';
	$str = $str . '<tr style="background-color:' . $table->header_bg_color. ';color:' . $table->header_text_color . '">';
	$prevData = array();
	foreach($fields as $key => $field)
	{
		if($key == '#')
			$str = $str . '<td width="5%" align="center">' . get_string('no', 'local_sis') . '</td>';
		else
		{
			$str = $str . '<td width="' . $field['size'] . '" align="' . $field['align'] . '">';
			if(!isset($field['header']))
			{
				if(!isset($field['lang']) || $field['lang'])
					$text = get_string($key, 'local_sis');
				else
					$text = $key;
			}
			else
				$text = $field['header'];
			$str = $str . $text;
			$str = $str . '</td>';
		}
		$prevData[$key] = '';
	}
	$str = $str . '</tr>';
	$str = $str . '</thead>';
	$count = 1;
	foreach($data as $rec)
	{
		$str = $str . '<tr>';
		foreach($fields as $key => $field)
		{
			if($key == '#')
			{
				$str = $str . '<td width="5%" align="center">' . $count . '</td>';
				$prevData[$key] = $count;
			}
			else
			{
				$str = $str . '<td width="' . $field['size'] . '" align="' . $field['align'] . '">';

				if(isset($field['format']))
				{
					if($field['format'] == 'date')
						$text = sis_output_formal_date($rec->$key);
					else if($field['format'] == 'datetime')
						$text = sis_output_formal_datetime($rec->$key);
					else if($field['format'] == 'decimal')
						$text = number_format($rec->$key, 2);
					else if($field['format'] == 'percent')
						$text = $rec->$key . '%';
					else if($field['format'] == 'yesno')
						$text = sis_output_show_yesno($rec->$key);
					else if($field['format'] == 'country')
						$text = sis_lookup_countries($rec->$key);
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
						$text = sis_output_show_campus($rec->$key);
					else if($field['format'] == 'iban')
						$text = sis_output_iban($rec);
					else if($field['format'] == 'combine')
					{
						$cmb = '';
						foreach($field['combine'] as $c)
						{
							if(isset($field['lang']) && $field['lang'] == true)
								$ct = get_string($rec->$c, 'local_sis');
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
							$text = sis_output_format_deduction(sis_output_currency($field['currency'], $rec->$key));
						else
							$text = sis_output_currency($field['currency'], $rec->$key);
					}
					else if($field['format'] == 'currency_negative')
					{
						$text = sis_output_format_deduction(sis_output_currency($field['currency'], $rec->$key));
					}
					else if($field['format'] == 'json')
					{
						$a = json_decode($rec->$key);
						$a_str = implode(', ', $a);
						$text = $a_str;
					}
					else if($field['format'] == 'arabic')
						$text = '<font face="tradbdo" size="12">' . $rec->$key . '</font>';
					else //fallback, just display
					{
						if(isset($field['lang']) && $field['lang'] == true)
							$text = get_string($rec->$key, 'local_sis');
						else
							$text = $rec->$key;
					}
				}
				else if($key == 'eff_status')
					$text = sis_output_show_active($rec->$key);
				else if($key == 'gender')
					$text = sis_output_show_gender($rec->$key);
				else
				{
					if(isset($field['lang']) && $field['lang'] == true)
						$text = get_string($rec->$key, 'local_sis');
					else
						$text = $rec->$key;
				}
				if($text == '' && isset($field['empty_text']) && $field['empty_text'] != '')
					$text = $field['empty_text'];
				if(isset($field['group']) && $field['group'])
				{
					if($prevData[$key] == $text)
					{
						$str = $str . '';
					}
					else
						$str = $str . $text;
				}
				else
					$str = $str . $text;
				$prevData[$key] = $text;
				$str = $str . '</td>';
			}
		}
		$str = $str . '</tr>';
		$count++;
	}
	$str = $str . '</table>';
	return $str;	
}

function sis_print_pdf($htmlArray, $options)
{
	global $CFG, $DB;
	// create new PDF document
	//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	if($options->landscape)
		$orient = 'L';
	else
		$orient = PDF_PAGE_ORIENTATION;
	if(isset($options->format))
	{
		$format = $options->format;
	}
	else
		$format = PDF_PAGE_FORMAT;
	$pdf = new MYPDF($orient, PDF_UNIT, $format, true, 'UTF-8', false);
	$pdf->orient = $orient;
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor($options->author);
	$pdf->SetTitle($options->title);
	$pdf->SetSubject($options->subject);
	$pdf->SetKeywords($options->keyword);

	$pdf->customFooter = $options->custom_footer;
	$pdf->customFooterMargin = $options->custom_footer_margin;

	// set default header data
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 018', PDF_HEADER_STRING);

	$logo_url = sis_print_pdf_logo_url($options->institute);
	
	$institute = $DB->get_record('si_institute', array('institute' => $options->institute));
	if(!isset($options->header) || $options->header)
		$pdf->SetHeaderData($logo_url->out(false), '27', $institute, $options);
	else
		$pdf->setPrintHeader(false);

	if(isset($options->footer) && !$options->footer)
		$pdf->setPrintFooter(false);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$margin_top = $options->top_margin;
	$pdf->SetMargins(PDF_MARGIN_LEFT, $margin_top, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER - 2);

	$fontname = $pdf->addTTFfont($CFG->dirroot.'/lib/tcpdf/fonts/arabic/tradbdo.ttf', 'TrueTypeUnicode', '', 32);

	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	if($options->custom_footer == '')
	{
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	}
	else
	{
		$footer_margin = isset($options->custom_footer_margin) ? $options->custom_footer_margin : 45;
		$pdf->SetFooterMargin($footer_margin);
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, $footer_margin + 5);
	}
	
	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language dependent data:
	$lg = Array();
	$lg['a_meta_charset'] = 'UTF-8';
//	$lg['a_meta_dir'] = 'rtl';
	$lg['a_meta_language'] = 'fa';
	$lg['w_page'] = 'page';

	// set some language-dependent strings (optional)
	$pdf->setLanguageArray($lg);

	// ---------------------------------------------------------

	// set font
	$pdf->SetFont('tradbdo', '', 14); //for arabic

	$pdf->SetFont('helvetica', '', $options->font_size);



	// set LTR direction for english translation
//	$pdf->setRTL(true);

	// set LTR direction for english translation
//	$pdf->setRTL(false);

	// print newline
	$pdf->Ln();

//	$pdf->SetFont('tradbdo', '', 16);

	// Arabic and English content
	//$htmlcontent2 = '<span color="#0000ff">This is Arabic "العربية" Example With TCPDF.</span>';
	//$pdf->WriteHTML($htmlcontent2, true, 0, true, 0);

	//multi page
	//multi page
	$count = 0;
	$pdf->AddPage();
	$html = $options->css . $html;
	$pdf->writeHTML($html['content'], true, false, true, false, '');

	foreach($htmlArray as $html)
	{
		if($html['add_page'])
		{
			$pdf->AddPage();
		}
		//set the font
		$font_style = isset($html['font_style']) ? $html['font_style'] : ''; //B, U or I
		$pdf->SetFont($html['font'], $font_style, $html['font_size']);

//		$pdf->writeHTML($html['content'], true, false, true, false, '');
		$pdf->writeHTMLCell($html['width'], '', '', '', 
			$html['content'], 
			$border = 0, 
			$ln = $html['new_line'], 
			$fill = 0, 
			$reseth = true, 
			$align = $html['align'], 
			$autopadding = true);
	}

	
/*	
	$count = 0;
	foreach($htmlArray as $html)
	{
		$pdf->AddPage();
		if($count == 0)
		{
			if($options->css != '')
				$html = $options->css . $html;
		}
		$pdf->writeHTML($html, true, false, true, false, '');
		$count++;
	}
*/

	// ---------------------------------------------------------	
	
	//Close and output PDF document
	if(isset($options->filename))
		$filename = $options->filename;
	else
		$filename = 'doc';
	$pdf->Output($filename . '.pdf', 'I');
}


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	var $customFooter = '';
	var $orient = '';
    //Page header

    public function Header() {
		global $language;
        // Logo
//        $image_file = 'logo_example.jpg';
		$ormargins = $this->getOriginalMargins();
		$headerfont = $this->getHeaderFont();
		$headerdata = $this->getHeaderData();
		$institute = $headerdata['title'];
		$options = $headerdata['string'];
		if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) 
		{
			$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '10', $headerdata['logo_width'], 0, '', '', '', false, 300, 'L');
			$imgy = $this->getImageRBY();
		} else {
			$imgy = $this->GetY();
		}
		$cell_height = round(($this->getCellHeightRatio() * $headerfont[2]) / $this->getScaleFactor(), 2) + 3;
		$cell_height = 5;
		// set starting margin for text data cell
		if ($this->getRTL()) {
			$header_x = $ormargins['right'];
		} else {
			$header_x = $ormargins['left'];
		}
		$this->SetTextColor(0, 0, 0);
		// header title
		$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
//		$this->SetFont('tradbdo', 'B', '20');	
		
//	$this->SetFont('dejavusans', '', '20');
//	$this->SetFont('aefurat', '', '20');		
		$this->SetX($header_x);
		$this->SetY(13);
		$this->SetFont('helvetica', 'B', '14');	
		$this->MultiCell(0, $cell_height, $institute->institute_name, 0, 'L', 0, 1, '50', '', true, 0, false);
//		$this->SetFont('tradbdo', 'B', '20');	
		$this->SetFont('alarabiya', 'N', '20');	
		$this->MultiCell(0, $cell_height + 5, $institute->institute_name_a, 0, 'L', 0, 1, '50', '', true, 0, false);
		$this->SetFont('helvetica', 'B', '11');	
		$this->MultiCell(0, $cell_height, $options->title, 0, 'L', 0, 1, '50', '', true, 0, false);
//		$this->MultiCell(0, $cell_height, 'Yalla', 1, 'L', 0, 1, '', '', true, 0, false);
		// print an ending header line
		$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		$this->SetY((2.835 / $this->k) + max($imgy, $this->y) + 3);
		if ($this->rtl) {
			$this->SetX($this->original_rMargin);
		} else {
			$this->SetX($this->original_lMargin);
		}
		if($options->header_line)
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
		
    }
	
    // Page footer
    public function Footer() 
	{
		global $USER;
		if($USER->idnumber != '')
			$idnumber = ' ' . $USER->lastname . ' (' . $USER->idnumber . ')';
		$print_user = 'Printed By: ' . $USER->firstname . $idnumber;
        // Position at 15 mm from bottom
        // Set font
        $this->SetFont('helvetica', 'N', 8);
		$printDate = date("d-M-Y : H:i:s", time());
		$iso_file = ''; //iso file
        // Page number
		if($this->customFooter == '') //use default footer
		{
	        $this->SetY(-15);
	        $this->Cell(0, 0, $print_user, 0, false, 'L', 0, '', 0, false, 'T', 'M');
			$this->SetX(-300);
			if($this->orient == 'L')
				$point = 300;
			else
				$point = 220;
	        $this->Cell($point, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	        $this->Cell(0, 0, 'Date Printed: '.$printDate, 0, false, 'R', 0, '', 0, false, 'T', 'M');

		}
		else //use custom footer
		{
			$this->writeHTML($this->customFooter, true, false, true, false, '');
	        $this->Cell(0, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'T', 'M');
	        $this->Cell(0, 0, 'Date Printed: '.$printDate, 0, false, 'R', 0, '', 0, false, 'T', 'M');
		}
    }
}

