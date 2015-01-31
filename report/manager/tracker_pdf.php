<?php

/**
 * Report generator - Tracker report.
 *
 * Description
 *
 * @package     report
 * @subpackage  generator/tracker
 * @copyright   2010 eFaktor
 * @licence     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @updateDate  12/10/2012
 * @author      eFaktor     (fbv)
 *
 * The report PDF class is the behind-the-scenes representation of
 * the report report emailed as PDF.
 */
require_once($CFG->libdir.'/tcpdf/tcpdf.php');

    /* Report PDF */
class TRACKER_PDF extends TCPDF {
    private $fs = 10;
    private $widths;
    private $aligns;
    private $fills;
    private $styles;
    private $horizontalgap;

    public function Header()
    {
        if( file_exists( $img = dirname( __FILE__ ) . '/img/dot_logo_288_nakos.png' ) )
        {
            $this->Image( $img, 25, 5, $this->px2mm( 180/5 ), $this->px2mm( 176/5 ) );
        }

        $this->Ln( 12 );
    }

    public function Footer()
    {
        //Go to 1.5 cm from bottom
        $this->SetY( -15 );
        //Select FreeSerif normal 8
        $this->SetFont( 'FreeSerif' , '', $this->fs );
        //Print left aligned page number
        $page = get_string('pdf_page_number','local_tracker',$this->PageNo());
        $this->Cell( 0, 10, $page, 0, 0, 'L' );
    }

    public function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths=$w;
    }

    public function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns=$a;
    }

    public function SetFills($f)
    {
        //Set the array of cell fills
        $this->fills=$f;
    }

    public function SetStyles($s)
    {
        //Set the array of cell fills
        $this->styles=$s;
    }

    public function SetHorizontalgap($g)
    {
        //Set horizontal gap value
        $this->horizontalgap=$g;
    }

    public function Row($data)
    {
        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=5*$nb;
        $g=isset($this->horizontalgap) ? $this->horizontalgap : 0;
        $g2=$g/2;

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

    //public function CheckPageBreak($h)
    //{
    //    //If the height h would cause an overflow, add a new page immediately
    //    if($this->GetY()+$h>$this->PageBreakTrigger)
    //        $this->AddPage($this->CurOrientation);
    //}

    public function checkPageBreak($h=0, $y='', $addpage=true) {
        if(($this->y + $h) > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);

            return true;
        }

        return false;
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
        $nb=strlen($s);
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
    public function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=1, $fill=0)
    {
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

//        $style = $fill ? 'DF' : '';
//        if ($border==1)
//            $this->Rect($xi,$yi,$w,$h,$style);
    }

    private function drawRows($w,$h,$txt,$border=0,$align='J',$fill=0,$maxline=0,$prn=0)
    {
//        $cw=&$this->CurrentFont['cw'];
        $cw=$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $margin=28.35/$this->k;
        $cMargin=$margin/10;
        $wmax=($w-2*$cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $b=0;
        if($border)
        {
            if($border==1)
            {
                $border='LTRB';
                $b='LRT';
                $b2='LR';
            }
            else
            {
                $b2='';
                if(is_int(strpos($border,'L')))
                    $b2.='L';
                if(is_int(strpos($border,'R')))
                    $b2.='R';
                $b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
            }
        }
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $ns=0;
        $nl=1;
        while($i<$nb)
        {
            //Get next character
            $c=$s[$i];
            if($c=="\n")
            {
                //Explicit line break
                if($this->w>0)
                {
                    $this->w=0;
                    if ($prn==1) $this->_out('0 Tw');
                }
                if ($prn==1) {
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $ns=0;
                $nl++;
                if($border and $nl==2)
                    $b=$b2;
                if ( $maxline && $nl > $maxline )
                    return substr($s,$i);
                continue;
            }
            if($c==' ')
            {
                $sep=$i;
                $ls=$l;
                $ns++;
            }
            $l+=$cw[ord($c)];
            if($l>$wmax)
            {
                //Automatic line break
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    if($this->w>0)
                    {
                        $this->w=0;
                        if ($prn==1) $this->_out('0 Tw');
                    }
                    if ($prn==1) {
                        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                    }
                }
                else
                {
                    if($align=='J')
                    {
                        $this->w=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                        if ($prn==1) $this->_out(sprintf('%.3f Tw',$this->w*$this->k));
                    }
                    if ($prn==1){
                        $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
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
                    return substr($s,$i);
            }
            else
                $i++;
        }
        //Last chunk
        if($this->w>0)
        {
            $this->w=0;
            if ($prn==1) $this->_out('0 Tw');
        }
        if($border and is_int(strpos($border,'B')))
            $b.='B';
        if ($prn==1) {
            $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
        }
        $this->x=$this->lMargin;
        return $nl;
    }

    /**
     * Conversion pixel -> millimeter in 72 dpi
     *
     * @param float px
     * @return float mm
     */
    //
    private function px2mm( $px ){
        return $px*25.4/72;
    }
}//class_report_pdf