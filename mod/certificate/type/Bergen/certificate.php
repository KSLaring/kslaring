<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * A4_embedded certificate type
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

$pdf = new PDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($certificate->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 10;
    $y = 0;
    $sealx = 230;
    $sealy = 150;
    $sigx = 47;
    $sigy = 155;
    $custx = 47;
    $custy = 155;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = 175;
} else { // Portrait
    $x = 10;
    $y = 0;
    $sealx = 150;
    $sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;
}

// Get font families.
$fontsans = get_config('certificate', 'fontsans');
// Load Segoe Print Bold as a custom font. To prepare Moodle for PDF custom fonts
// the fonts directory from »lib/tcpdf/fonts« needs to be copied to »moodledata/fonts«.
// Moodle TCPDF reads the fonts from the moodledata fonts directory if present. Then copy
// the font file to be used into the »moodledata/fonts« directory.
//$fontserif = $pdf->addTTFfont($CFG->dataroot . '/fonts/segoeprb.ttf', 'TrueTypeUnicode',
//    '', 32, $CFG->dataroot . '/fonts/');

$fontserif = get_config('certificate', 'fontserif');

// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
//certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
//$pdf->SetAlpha(0.2);
//certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky,
// $wmarkw, $wmarkh);
//$pdf->SetAlpha(1);
//certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
//certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
//$pdf->SetTextColor(0, 0, 120);
//certificate_print_text($pdf, $x, $y, 'C', $fontsans, '', 30,
// get_string('title', 'certificate'));
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y + 95, 'C', $fontserif, '', 30, fullname($USER));
//certificate_print_text($pdf, $x, $y + 93, 'C', $fontserif, '', 20,
// get_string('certify', 'certificate'));

$pdf->SetTextColor(157, 157, 157);
certificate_print_text($pdf, $x, $y + 121, 'C', $fontsans, '', 18,
    get_string('statement', 'certificate'));

$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y + 142, 'C', $fontsans, 'B', 24,
    format_string($course->fullname));

// Set the line height in the style of a tag entered in the customtext field in the settings.
$customtext =  $certificate->customtext;
certificate_print_text($pdf, 46, $y + 164, 'L', $fontsans, '', 16,
    $customtext, 210 - (46 * 2));

certificate_print_text($pdf, $x, $y + 227, 'C', $fontsans, '', 16,
    'Bergen, ' . certificate_get_date($certificate, $certrecord, $course));
//certificate_print_text($pdf, $x, $y + 102, 'C', $fontserif, '', 10,
// certificate_get_grade($certificate, $course));
//certificate_print_text($pdf, $x, $y + 112, 'C', $fontserif, '', 10,
// certificate_get_outcome($certificate, $course));
//if ($certificate->printhours) {
//    certificate_print_text($pdf, $x, $y + 122, 'C', $fontserif, '', 10,
// get_string('credithours', 'certificate') . ': ' . $certificate->printhours);
//}
//certificate_print_text($pdf, $x, $codey, 'C', $fontserif, '', 10,
// certificate_get_code($certificate, $certrecord));
//$i = 0;
//if ($certificate->printteacher) {
//    $context = context_module::instance($cm->id);
//    if ($teachers = get_users_by_capability($context, 'mod/certificate:printteacher', '',
// $sort = 'u.lastname ASC', '', '', '', '', false)) {
//        foreach ($teachers as $teacher) {
//            $i++;
//            certificate_print_text($pdf, $sigx, $sigy + ($i * 4), 'L', $fontserif, '',
// 12, fullname($teacher));
//        }
//    }
//}

//include_once(__DIR__ . '/pagescales_mm.php');
