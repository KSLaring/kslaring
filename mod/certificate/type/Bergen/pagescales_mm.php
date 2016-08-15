<?php
// set up
$font = 'Freesans';
$border = 0;
$portrait = true;
$width = $portrait ? 210 : 297;
$height = $portrait ? 297 : 210;
$pdf->SetAutoPageBreak(false, 0);

// create page scales
// x-scale
$y = 0;
for ($x = 10; $x < $width; $x += 10) {
    $l = ($x % 50) ? 5 : (($x % 100) ? 10 : 17);

    $pdf->Line($x, $y, $x, $y + $l);

    // write numbers to scale
    if (!($x % 100)) {
        $pdf->SetXY($x + 1, $y + 11);
        $pdf->setFont($font, '', 8);
        $pdf->Cell(20, 8, $x, $border, 0, 'L');
    }
}
// y-scale
$x = 0;
for ($y = 10; $y < $height; $y += 10) {
    //    $l = ( $y % 50 ) ? 5 : ( ( $y % 100 ) ? 10 : 17 );
    $l = ($y % 50) ? 5 : 10;

    $pdf->Line($x, $y, $x + $l, $y);

    // write numbers to scale
    if (!($y % 100)) {
        $pdf->SetXY($x + $l, $y - 3);
        $pdf->setFont($font, '', 8);
        $pdf->Cell(20, 8, $y, $border, 0, 'L');
    }
}
