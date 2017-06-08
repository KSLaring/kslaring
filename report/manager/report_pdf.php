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
 * Report generator - Outcome report.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  24/09/2012
 * @author      eFaktor     (fbv)
 *
 * The report PDF class is the behind-the-scenes representation of
 * the report report emailed as PDF.
 */
require_once($CFG->libdir.'/tcpdf/tcpdf.php');

/* Report PDF */
class REPORT_PDF extends TCPDF {
    private $fs = 10;
    private $widths;
    private $aligns;
    private $fills;
    private $styles;
    private $horizontalgap;

    public function Header() {
        if (file_exists($img = dirname( __FILE__ ) . '/img/dot_logo_288.png')) {
            $this->Image($img, 25, 5, $this->px2mm(180/5), $this->px2mm(176/5));
        }
        $this->Ln( 12 );
    }//Header

    public function Footer()
    {
        //Go to 1.5 cm from bottom
        $this->SetY( -15 );
        //Select FreeSerif normal 8
        $this->SetFont( 'FreeSerif' , '', $this->fs );
        //Print left aligned page number
        $this->Cell( 0, 10, report::get_string( 'pdf_page_number', $this->PageNo() ), 0, 0, 'L' );
    }

    public function SetWidths($w) {
        //Set the array of column widths
        $this->widths=$w;
    }//SetWidths

    public function SetAligns($a) {
        //Set the array of column alignments
        $this->aligns=$a;
    }//SetAligns

    public function SetFills($f) {
        //Set the array of cell fills
        $this->fills=$f;
    }//SetFills

    public function SetStyles($s) {
        //Set the array of cell fills
        $this->styles=$s;
    }//SetStyles

    public function SetHorizontalgap($g) {
        //Set horizontal gap value
        $this->horizontalgap=$g;
    }//SetHorizontalgap

    public function Row($data)
    {
        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=5*$nb;
        $g=isset($this->horizontalgap) ? $this->horizontalgap : 0;
        $g2=$g/2;
        //Issue a page break first if needed
        $this->checkPageBreak($h);
        //Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i]-$g2;
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=isset($this->fills[$i]) ? $this->fills[$i] : 'D';
            $s=isset($this->styles[$i]) ? $this->styles[$i] : '';
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();
            //Draw the border
            $this->Rect($x,$y,$w,$h,$f);
            //Print the text
            $this->SetFont('',$s);
            $this->MultiCell($w,5,$data[$i],0,$a);
            //Put the position to the right of the cell
            $this->SetXY($x+$w+$g,$y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    public function checkPageBreak($h=0, $y='', $addpage=true) {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    private function NbLines($w,$txt)
    {
        //Computes the number of lines a MultiCell of width w will take
//        $cw=&$this->CurrentFont['cw'];
        $cw=$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $margin=28.35/$this->k;
        $cMargin=$margin/10;
        $wmax=($w-2*$cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
//        $nb=strlen($s);
        $nb=mb_strlen($s,'UTF-8');
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
//            $c=$s[$i];
            $c=mb_substr($s,$i,1,'UTF-8');
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[ord($c)];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }

    /**
     * Draws text within a box defined by width = w, height = h, and aligns
     * the text vertically within the box ($valign = M/B/T for middle, bottom, or top)
     * Also, aligns the text horizontally ($align = L/C/R/J for left, centered, right or justified)
     * drawTextBox uses drawRows
     *
     * This function is provided by TUFaT.com
     */
    public function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=1, $fill=0) {
        $xi=$this->GetX();
        $yi=$this->GetY();

        $hrow=$this->FontSize;
        $textrows=$this->drawRows($w,$hrow,$strText,0,$align,0,0,0);
        $maxrows=floor($h/$this->FontSize);
        $rows=min($textrows,$maxrows);

        $dy=0;
        if (strtoupper($valign)=='M')
            $dy=($h-$rows*$this->FontSize)/2;
        if (strtoupper($valign)=='B')
            $dy=$h-$rows*$this->FontSize;

        $this->SetY($yi+$dy);
        $this->SetX($xi);

        // background rectangle below text
        $style = $fill ? 'DF' : '';
        if ($border==1)
            $this->Rect($xi,$yi,$w,$h,$style);

        $this->drawRows($w,$hrow,$strText,0,$align,0,$rows,1);
    }//drawTextBox

    private function drawRows($w,$h,$txt,$border=0,$align='J',$fill=0,$maxline=0,$prn=0) {
        $cw=$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $margin=28.35/$this->k;
        $cMargin=$margin/10;
        $wmax=($w-2*$cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=mb_strlen($s,'UTF-8');
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $b=0;
        if($border) {
            if($border==1) {
                $border='LTRB';
                $b='LRT';
                $b2='LR';
            }else {
                $b2='';
                if(is_int(strpos($border,'L')))
                    $b2.='L';
                if(is_int(strpos($border,'R')))
                    $b2.='R';
                $b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
            }//if_border_1
        }//if_border

        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $ns=0;
        $nl=1;
        while($i<$nb) {
            //Get next character
            $c=mb_substr($s,$i,1,'UTF-8');
            if($c=="\n") {
                //Explicit line break
                if($this->ws>0) {
                    $this->ws=0;
                    if ($prn==1) $this->_out('0 Tw');
                }//if_ws_>_0
                if ($prn==1) {
                    $this->Cell($w,$h,mb_substr($s,$j,$i-$j,'UTF-8'),$b,2,$align,$fill);
                }//if_prn_1

                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $ns=0;
                $nl++;

                if($border and $nl==2)
                    $b=$b2;
                if ( $maxline && $nl > $maxline )
                    return mb_substr($s,$i,'UTF-8');
                continue;
            }//if_c_n

            if($c==' ') {
                $sep=$i;
                $ls=$l;
                $ns++;
            }//if_c_''

            $_lc=ord($c);
            $l+=$cw[$_lc];
            if($l>$wmax)
            {
                //Automatic line break
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    if($this->ws>0)
                    {
                        $this->ws=0;
                        if ($prn==1) $this->_out('0 Tw');
                    }
                    if ($prn==1) {
                        $this->Cell($w,$h,mb_substr($s,$j,$i-$j,'UTF-8'),$b,2,$align,$fill);
                    }
                }
                else
                {
                    if($align=='J')
                    {
                        $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                        if ($prn==1) $this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
                    }
                    if ($prn==1){
                        $this->Cell($w,$h,mb_substr($s,$j,$sep-$j,'UTF-8'),$b,2,$align,$fill);
                    }
                    $i=$sep+1;
                }
                $sep=-1;
                $j=$i;
                $l=0;
                $ns=0;
                $nl++;
                if($border and $nl==2)
                    $b=$b2;
                if ( $maxline && $nl > $maxline )
                    return mb_substr($s,$i,'UTF-8');
            }
            else
                $i++;
        }
        //Last chunk
        if($this->ws>0)
        {
            $this->ws=0;
            if ($prn==1) $this->_out('0 Tw');
        }
        if($border and is_int(strpos($border,'B')))
            $b.='B';
        if ($prn==1) {
            $this->Cell($w,$h,mb_substr($s,$j,$i-$j,'UTF-8'),$b,2,$align,$fill);
        }
        $this->x=$this->lMargin;
        return $nl;
    }//drawRows

    /**
     * Unicode ord function
     *
     * @param string $ch
     * @return int unicode ord
     */
    private function uniord($ch) {

        $n = ord($ch{0});

        if ($n < 128) {
            return $n; // no conversion required
        }

        if ($n < 192 || $n > 253) {
            return false; // bad first byte || out of range
        }

        $arr = array(1 => 192, // byte position => range from
            2 => 224,
            3 => 240,
            4 => 248,
            5 => 252,
        );

        foreach ($arr as $key => $val) {
            if ($n >= $val) { // add byte to the 'char' array
                $char[] = ord($ch{$key}) - 128;
                $range  = $val;
            } else {
                break; // save some e-trees
            }
        }

        $retval = ($n - $range) * pow(64, sizeof($char));

        foreach ($char as $key => $val) {
            $pow = sizeof($char) - ($key + 1); // invert key
            $retval += $val * pow(64, $pow);   // dark magic
        }

        return $retval;
    }//uniord

    /**
     * Conversion pixel -> millimeter in 72 dpi
     *
     * @param float px
     * @return float mm
     */
    //
    private function px2mm( $px ){
        return $px*25.4/72;
    }//px2mm
}//REPORT_PDf
