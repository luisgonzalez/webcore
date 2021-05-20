<?php
/**
 * Class to generate PDF
 *
 * @package WebCore
 * @subpackage Pdf
 * @author Olivier PLATHEY & Renato A.C. WebCore implementation Geo Perez
 */
abstract class PdfGenerator extends ObjectBase
{
    protected $outlines=array();
    protected $OutlineRoot;
    protected $flowingBlockAttr;
    protected $page;               //current page number
    protected $n;                  //current object number
    protected $offsets;            //array of object offsets
    protected $buffer;             //buffer holding in-memory PDF
    protected $pages;              //array containing pages
    protected $state;              //current document state
    protected $compress;           //compression flag
    protected $DefOrientation;     //default orientation
    protected $CurOrientation;     //current orientation
    protected $OrientationChanges; //array indicating orientation changes
    protected $k;                  //scale factor (number of points in user unit)
    protected $fwPt,$fhPt;         //dimensions of page format in points
    protected $fw,$fh;             //dimensions of page format in user unit
    protected $wPt,$hPt;           //current dimensions of page in points
    protected $w,$h;               //current dimensions of page in user unit
    protected $lMargin;            //left margin
    protected $tMargin;            //top margin
    protected $rMargin;            //right margin
    protected $bMargin;            //page break margin
    protected $cMargin;            //cell margin
    protected $x,$y;               //current position in user unit for cell positioning
    protected $lasth;              //height of last cell printed
    protected $LineWidth;          //line width in user unit
    protected $CoreFonts;          //array of standard font names
    protected $fonts;              //array of used fonts
    protected $FontFiles;          //array of font files
    protected $diffs;              //array of encoding differences
    protected $images;             //array of used images
    protected $FontFamily;         //current font family
    protected $FontStyle;          //current font style
    protected $underline;          //underlining flag
    protected $CurrentFont;        //current font info
    protected $FontSizePt;         //current font size in points
    protected $FontSize;           //current font size in user unit
    protected $DrawColor;          //commands for drawing color
    protected $FillColor;          //commands for filling color
    protected $TextColor;          //commands for text color
    protected $ColorFlag;          //indicates whether fill and text colors are different
    protected $ws;                 //word spacing
    protected $AutoPageBreak;      //automatic page breaking
    protected $PageBreakTrigger;   //threshold used to trigger page breaks
    protected $InFooter;           //flag set when processing footer
    protected $ZoomMode;           //zoom display mode
    protected $LayoutMode;         //layout display mode
    protected $title;              //title
    protected $subject;            //subject
    protected $author;             //author
    protected $creator;            //creator
    protected $AliasNbPages;       //alias for total number of pages
    
    public function __construct($orientation='P', $unit='mm', $format='letter')
    {
        $this->page=0;
        $this->n=2;
        $this->buffer='';
        $this->pages=array();
        $this->OrientationChanges=array();
        $this->state=0;
        $this->fonts=array();
        $this->FontFiles=array();
        $this->diffs=array();
        $this->images=array();
        $this->InFooter=false;
        $this->lasth=0;
        $this->FontFamily='';
        $this->FontStyle='';
        $this->FontSizePt=12;
        $this->underline=false;
        $this->DrawColor='0 G';
        $this->FillColor='0 g';
        $this->TextColor='0 g';
        $this->ColorFlag=false;
        $this->ws=0;
        
        $this->CoreFonts=array(
            'courier'=>'Courier','courierB'=>'Courier-Bold','courierI'=>'Courier-Oblique','courierBI'=>'Courier-BoldOblique',
            'helvetica'=>'Helvetica','helveticaB'=>'Helvetica-Bold','helveticaI'=>'Helvetica-Oblique','helveticaBI'=>'Helvetica-BoldOblique',
            'times'=>'Times-Roman','timesB'=>'Times-Bold','timesI'=>'Times-Italic','timesBI'=>'Times-BoldItalic',
            'symbol'=>'Symbol','zapfdingbats'=>'ZapfDingbats');
        
        if($unit=='pt')	$this->k=1;
        elseif($unit=='mm')	$this->k=72/25.4;
        elseif($unit=='cm')	$this->k=72/2.54;
        elseif($unit=='in')	$this->k=72;
        else throw new Exception('Incorrect unit: '.$unit);
        
        if(is_string($format))
        {
            $format=strtolower($format);
            
            if($format=='a3')	$format=array(841.89,1190.55);
            elseif($format=='a4')	$format=array(595.28,841.89);
            elseif($format=='a5')	$format=array(420.94,595.28);
            elseif($format=='letter')	$format=array(612,792);
            else $format=array(612,1008);
            
            $this->fwPt=$format[0];
            $this->fhPt=$format[1];
        }
        else
        {
            $this->fwPt=$format[0]*$this->k;
            $this->fhPt=$format[1]*$this->k;
        }
        
        $this->fw=$this->fwPt/$this->k;
        $this->fh=$this->fhPt/$this->k;
        
        $orientation=strtolower($orientation);
        
        if ($orientation=='l' || $orientation=='landscape')
        {
            $this->DefOrientation='L';
            $this->wPt=$this->fhPt;
            $this->hPt=$this->fwPt;
        }
        else
        {
            $this->DefOrientation='P';
            $this->wPt=$this->fwPt;
            $this->hPt=$this->fhPt;
        }
        
        $this->CurOrientation=$this->DefOrientation;
        $this->w=$this->wPt/$this->k;
        $this->h=$this->hPt/$this->k;
        
        $margin=28.35/$this->k;
        $this->setMargins($margin, $margin);
        $this->cMargin=$margin/10;
        $this->LineWidth=.567/$this->k;
        $this->setAutoPageBreak(true,2*$margin);
        $this->setDisplayMode();
        $this->compress = true;
    }
    
    public function setMargins($left, $top, $right=-1)
    {
        $this->lMargin=$left;
        $this->tMargin=$top;
        if($right==-1) $right=$left;
        $this->rMargin=$right;
    }
    
    public function setLeftMargin($margin)
    {
        $this->lMargin = $margin;
        
        if($this->page > 0 and $this->x<$margin)
            $this->x = $margin;
    }
    
    public function setTopMargin($margin)
    {
        $this->tMargin=$margin;
    }
    
    public function setRightMargin($margin)
    {
        $this->rMargin=$margin;
    }
    
    public function setAutoPageBreak($auto,$margin=0)
    {
        $this->AutoPageBreak=$auto;
        $this->bMargin=$margin;
        $this->PageBreakTrigger=$this->h-$margin;
    }
    
    public function setDisplayMode($zoom = 'fullwidth', $layout='continuous')
    {
        if ($zoom=='fullpage' or $zoom=='fullwidth' or $zoom=='real' or $zoom=='default' or !is_string($zoom))
            $this->ZoomMode=$zoom;
        else
            throw new Exception('Incorrect zoom display mode: '.$zoom);
        
        if ($layout=='single' or $layout=='continuous' or $layout=='two' or $layout=='default')
            $this->LayoutMode=$layout;
        else
            throw new Exception('Incorrect layout display mode: '.$layout);
    }
    
    public function setTitle($title)
    {
        $this->title=$title;
    }
    
    public function setSubject($subject)
    {
        $this->subject=$subject;
    }
    
    public function setAuthor($author)
    {
        $this->author=$author;
    }
    
    public function setCreator($creator)
    {
        //Creator of document
        $this->creator=$creator;
    }
    
    public function AliasNbPages($alias='{nb}')
    {
        //Define an alias for total number of pages
        $this->AliasNbPages=$alias;
    }
    
    public function Open()
    {
        if($this->state == 0)
        {
            $this->state=1;
            $this->_out('%PDF-1.3');
        }
    }
    
    public function Close()
    {
        if($this->state==3) return;
        if($this->page==0) $this->addPage();
     
        $this->InFooter=true;
        $this->footer();
        $this->InFooter=false;
        $this->_endpage();
        
        $this->_putpages();
        $this->_putresources();
        //Info
        $this->_newobj();
        $this->_out('<<');
        $this->_putinfo();
        $this->_out('>>');
        $this->_out('endobj');
        //Catalog
        $this->_newobj();
        $this->_out('<<');
        $this->_putcatalog();
        $this->_out('>>');
        $this->_out('endobj');
        
        $o=strlen($this->buffer);
        $this->_out('xref');
        $this->_out('0 '.($this->n+1));
        $this->_out('0000000000 65535 f ');
        
        for($i=1; $i <= $this->n ; $i++)
            $this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
        
        $this->_out('trailer');
        $this->_out('<<');
        $this->_puttrailer();
        $this->_out('>>');
        $this->_out('startxref');
        $this->_out($o);
        $this->_out('%%EOF');
        $this->state=3;
    }
    
    public function addPage($orientation='')
    {
        if($this->state==0)
            $this->Open();
            
        $family=$this->FontFamily;
        $style=$this->FontStyle.($this->underline ? 'U' : '');
        $size=$this->FontSizePt;
        $lw=$this->LineWidth;
        $dc=$this->DrawColor;
        $fc=$this->FillColor;
        $tc=$this->TextColor;
        $cf=$this->ColorFlag;
        
        if($this->page>0)
        {
            $this->InFooter=true;
            $this->footer();
            $this->InFooter=false;
            $this->_endpage();
        }
        
        $this->_beginpage($orientation);
        $this->_out('2 J');
        $this->LineWidth=$lw;
        $this->_out(sprintf('%.2f w',$lw*$this->k));
        //Set font
        if($family)	$this->SetFont($family,$style,$size);
        //Set colors
        $this->DrawColor=$dc;
        if($dc!='0 G') $this->_out($dc);
        $this->FillColor=$fc;
        if($fc!='0 g') $this->_out($fc);
        $this->TextColor=$tc;
        $this->ColorFlag=$cf;
        
        $this->header();
        
        if($this->LineWidth!=$lw)
        {
            $this->LineWidth=$lw;
            $this->_out(sprintf('%.2f w',$lw*$this->k));
        }
        //Restore font
        if($family)	$this->SetFont($family,$style,$size);
        //Restore colors
        if($this->DrawColor!=$dc)
        {
            $this->DrawColor=$dc;
            $this->_out($dc);
        }
        if($this->FillColor!=$fc)
        {
            $this->FillColor=$fc;
            $this->_out($fc);
        }
        $this->TextColor=$tc;
        $this->ColorFlag=$cf;
    }
    
    abstract function header();
    
    abstract function footer();
    
    public function PageNo()
    {
        return $this->page;
    }
    
    public function setDrawColor($r,$g=-1,$b=-1)
    {
        //Set color for all stroking operations
        if(($r==0 and $g==0 and $b==0) or $g==-1)	$this->DrawColor=sprintf('%.3f G',$r/255);
        else $this->DrawColor=sprintf('%.3f %.3f %.3f RG',$r/255,$g/255,$b/255);
        if($this->page>0)	$this->_out($this->DrawColor);
    }
    
    public function setFillColor($r,$g=-1,$b=-1)
    {
        //Set color for all filling operations
        if(($r==0 and $g==0 and $b==0) or $g==-1)	$this->FillColor=sprintf('%.3f g',$r/255);
        else$this->FillColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
        if($this->page>0)	$this->_out($this->FillColor);
    }
    
    public function setTextColor($r,$g=-1,$b=-1)
    {
        //Set color for text
        if(($r==0 and $g==0 and $b==0) or $g==-1)	$this->TextColor=sprintf('%.3f g',$r/255);
        else $this->TextColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
    }
    
    public function GetStringWidth($s)
    {
        $s=(string)$s;
        $cw=&$this->CurrentFont['cw'];
        $w=0;
        $l=strlen($s);
        for($i=0;$i<$l;$i++) $w+=$cw[$s{$i}];
        return $w*$this->FontSize/1000;
    }
    
    public function setLineWidth($width)
    {
        $this->LineWidth=$width;
        if($this->page>0)	$this->_out(sprintf('%.2f w',$width*$this->k));
    }
    
    function Line($x1,$y1,$x2,$y2)
    {
        $this->_out(sprintf('%.2f %.2f m %.2f %.2f l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
    }
    
    function Rect($x,$y,$w,$h,$style='')
    {
        if($style=='F')	$op='f';
        elseif($style=='FD' or $style=='DF') $op='B';
        else $op='S';
        $this->_out(sprintf('%.2f %.2f %.2f %.2f re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
    }
    
    function AddFont($family,$style='',$file='')
    {
        $family=strtolower($family);
        if($family=='arial') $family='helvetica';
        $style=strtoupper($style);
        if($style=='IB') $style='BI';
        if(isset($this->fonts[$family.$style]))	throw new Exception('Font already added: '.$family.' '.$style);
        if($file=='')	$file=str_replace(' ','',$family).strtolower($style).'.php';

        include("fonts/" . $file);
        
        if(!isset($name))
            throw new Exception('Could not include font definition file');
        
        $i=count($this->fonts)+1;
        $this->fonts[$family.$style]=array('i'=>$i,'type'=>$type,'name'=>$name,'desc'=>$desc,'up'=>$up,'ut'=>$ut,'cw'=>$cw,'enc'=>$enc,'file'=>$file);
        if($diff)
        {
            //Search existing encodings
            $d=0;
            $nb=count($this->diffs);
            
            for($i=1;$i<=$nb;$i++)
            {
                if($this->diffs[$i]==$diff)
                {
                    $d=$i;
                    break;
                }
            }
            
            if($d==0)
            {
                $d=$nb+1;
                $this->diffs[$d]=$diff;
            }
            $this->fonts[$family.$style]['diff']=$d;
        }
        
        if($file)
        {
            if($type=='TrueType')
                $this->FontFiles[$file]=array('length1'=>$originalsize);
            else
                $this->FontFiles[$file]=array('length1'=>$size1,'length2'=>$size2);
        }
    }
    
    public function setFont($family,$style='',$size=0)
    {
        //Select a font; size given in points
        global $fpdf_charwidths;
    
        $family=strtolower($family);
        if($family=='')	$family=$this->FontFamily;
        if($family=='monospace') $family='courier';
        if($family=='serif') $family='times';
        if($family=='sans') $family='arial';
        if($family=='arial') $family='helvetica';
        elseif($family=='symbol' or $family=='zapfdingbats') $style='';
        $style=strtoupper($style);
        
        if(is_int(strpos($style,'U')))
        {
            $this->underline=true;
            $style=str_replace('U','',$style);
        }
        else $this->underline=false;
        if ($style=='IB') $style='BI';
        if ($size==0) $size=$this->FontSizePt;
        //Test if font is already selected
        if($this->FontFamily==$family and $this->FontStyle==$style and $this->FontSizePt==$size) return;
        //Test if used for the first time
        $fontkey=$family.$style;
        if(!isset($this->fonts[$fontkey]))
        {
            //Check if one of the standard fonts
            if(isset($this->CoreFonts[$fontkey]))
            {
                if(!isset($fpdf_charwidths[$fontkey]))
                {
                    $file=$family;
                    if($family=='times' or $family=='helvetica') $file.=strtolower($style);
                    $file.='.php';

                    include("fonts/" . $file);
                    if(!isset($fpdf_charwidths[$fontkey])) throw new Exception('Could not include font metric file');
                }
                
                $i=count($this->fonts)+1;
                $this->fonts[$fontkey]=array('i'=>$i,'type'=>'core','name'=>$this->CoreFonts[$fontkey],'up'=>-100,'ut'=>50,'cw'=>$fpdf_charwidths[$fontkey]);
            }
            else throw new Exception('Undefined font: '.$family.' '.$style);
        }
        //Select it
        $this->FontFamily=$family;
        $this->FontStyle=$style;
        $this->FontSizePt=$size;
        $this->FontSize=$size/$this->k;
        $this->CurrentFont=&$this->fonts[$fontkey];
        
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2f Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }
    
    public function setFontSize($size)
    {
        //Set font size in points
        if($this->FontSizePt==$size) return;
        $this->FontSizePt=$size;
        $this->FontSize=$size/$this->k;
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2f Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }
    
    function Text($x,$y,$txt)
    {
        //Output a string
        $s=sprintf('BT %.2f %.2f Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        if($this->underline and $txt!='')	$s.=' '.$this->_dounderline($x,$y,$txt);
        if($this->ColorFlag) $s='q '.$this->TextColor.' '.$s.' Q';
        $this->_out($s);
    }
    
    function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='',$currentx=0) //EDITEI
    {
        //Output a cell
        $k=$this->k;
        if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AutoPageBreak)
        {
            //Automatic page break
            $x=$this->x;//Current X position
            $ws=$this->ws;//Word Spacing
            if($ws>0)
            {
                $this->ws=0;
                $this->_out('0 Tw');
            }
            $this->addPage($this->CurOrientation);
            $this->x=$x;
            if($ws>0)
            {
                $this->ws=$ws;
                $this->_out(sprintf('%.3f Tw',$ws*$k));
            }
        }
        if($w==0) $w = $this->w-$this->rMargin-$this->x;
        $s='';
        if($fill==1 or $border==1)
        {
            if ($fill==1) $op=($border==1) ? 'B' : 'f';
            else $op='S';
            $s=sprintf('%.2f %.2f %.2f %.2f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
        }
        if(is_string($border))
        {
            $x=$this->x;
            $y=$this->y;
            if(is_int(strpos($border,'L')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
            if(is_int(strpos($border,'T')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
            if(is_int(strpos($border,'R')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
            if(is_int(strpos($border,'B')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        }
        if($txt!='')
        {
            if($align=='R')	$dx=$w-$this->cMargin-$this->GetStringWidth($txt);
            elseif($align=='C')	$dx=($w-$this->GetStringWidth($txt))/2;
            elseif($align=='L' or $align=='J') $dx=$this->cMargin;
        else $dx = 0;
            if($this->ColorFlag) $s.='q '.$this->TextColor.' ';
            $txt2=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
        //Check whether we are going to outline text or not
            if($this->outline_on)
            {
              $s.=' '.sprintf('%.2f w',$this->LineWidth*$this->k).' ';
              $s.=" $this->DrawColor ";
          $s.=" 2 Tr ";
        }
        //Superscript and Subscript Y coordinate adjustment
        $adjusty = 0;
        if($this->SUB) $adjusty = 1;
        if($this->SUP) $adjusty = -1;
        //End of coordinate adjustment
            $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-(($this->y+$adjusty)+.5*$h+.3*$this->FontSize))*$k,$txt2); //EDITEI
            if($this->underline)
                $s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize+$adjusty,$txt2);
        //Superscript and Subscript Y coordinate adjustment (now for striked-through texts)
        $adjusty = 1.6;
        if($this->SUB) $adjusty = 3.05;
        if($this->SUP) $adjusty = 1.1;
        //End of coordinate adjustment
            if($this->strike) //EDITEI
                $s.=' '.$this->_dounderline($this->x+$dx,$this->y+$adjusty,$txt);
            if($this->ColorFlag) $s.=' Q';
            if($link!='') $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
        }
        if($s) $this->_out($s);
        $this->lasth=$h;
        if( strpos($txt,"\n") !== false) $ln=1; //EDITEI - cell now recognizes \n! << comes from <BR> tag
        if($ln>0)
        {
            $this->y += $h;
            if($ln==1) //EDITEI
            {
                if ($currentx != 0) $this->x=$currentx;
                else $this->x=$this->lMargin;
        }
        }
        else $this->x+=$w;
    }
    
    function MultiCell($w,$h,$txt,$border=0,$align='J',$fill=0,$link='')
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)	$w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")	$nb--;
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
                if(is_int(strpos($border,'L')))	$b2.='L';
                if(is_int(strpos($border,'R')))	$b2.='R';
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
            $c=$s{$i};
            
            if($c=="\n")
            {
                if($this->ws>0)
                {
                    $this->ws=0;
                    $this->_out('0 Tw');
                }
                
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $ns=0;
                $nl++;
                if($border and $nl==2) $b=$b2;
                continue;
            }
            
            if($c==' ')
            {
                $sep=$i;
                $ls=$l;
                $ns++;
            }
            $l+=$cw[$c];
            if($l>$wmax)
            {
                //Automatic line break
                if($sep==-1)
                {
                    if($i==$j) $i++;
                    if($this->ws>0)
                    {
                        $this->ws=0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
                }
                else
                {
                    if($align=='J')
                    {
                        $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                        $this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
                    }
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill,$link);
                    $i=$sep+1;
                }
                $sep=-1;
                $j=$i;
                $l=0;
                $ns=0;
                $nl++;
                if($border and $nl==2) $b=$b2;
            }
            else $i++;
        }
        //Last chunk
        if($this->ws>0)
        {
            $this->ws=0;
            $this->_out('0 Tw');
        }
        if($border and is_int(strpos($border,'B')))	$b.='B';
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill,$link);
        $this->x=$this->lMargin;
    }
    
    function Write($h,$txt,$currentx=0,$link='') //EDITEI
    {
        $cw=&$this->CurrentFont['cw'];
        $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            //Get next character
            $c=$s{$i};
            if($c=="\n")
            {
                //Explicit line break
                $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                if($nl==1)
                {
                    if ($currentx != 0) $this->x=$currentx;//EDITEI
                    else $this->x=$this->lMargin;
                    $w=$this->w-$this->rMargin-$this->x;
                    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
                continue;
            }
            
            if($c == ' ')
                $sep=$i;
                
            $l += $cw[$c];
            
            if($l > $wmax)
            {
                if($sep==-1)
                {
                    if($this->x > $this->lMargin)
                    {
                        if ($currentx != 0) $this->x=$currentx;//EDITEI
                        else $this->x=$this->lMargin;
                        $this->y+=$h;
                        $w=$this->w-$this->rMargin-$this->x;
                        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
                        $i++;
                        $nl++;
                        continue;
                    }
                    if($i==$j) $i++;
                    $this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
                }
                else
                {
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
                    $i=$sep+1;
                }
                $sep=-1;
                $j=$i;
                $l=0;
                if($nl==1)
                {
                    if ($currentx != 0) $this->x=$currentx;//EDITEI
                    else $this->x=$this->lMargin;
                    $w=$this->w-$this->rMargin-$this->x;
                    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
                }
                $nl++;
            }
            else $i++;
        }
        //Last chunk
        if($i!=$j) $this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',0,$link);
    }

    function saveFont()
    {
       $saved = array();
       $saved['family'] = $this->FontFamily;
       $saved['style'] = $this->FontStyle;
       $saved['sizePt'] = $this->FontSizePt;
       $saved['size'] = $this->FontSize;
       $saved['curr'] =& $this->CurrentFont;
       $saved['color'] = $this->TextColor; //EDITEI
       $saved['bgcolor'] = $this->FillColor; //EDITEI
       $saved['underline'] = $this->underline; //EDITEI
       $saved['strike'] = $this->strike; //EDITEI
       $saved['SUP'] = $this->SUP; //EDITEI
       $saved['SUB'] = $this->SUB; //EDITEI
       $saved['linewidth'] = $this->LineWidth; //EDITEI
       $saved['drawcolor'] = $this->DrawColor; //EDITEI
       $saved['is_outline'] = $this->outline_on; //EDITEI
    
       return $saved;
    }
    
    function restoreFont( $saved )
    {
       $this->FontFamily = $saved['family'];
       $this->FontStyle = $saved['style'];
       $this->FontSizePt = $saved['sizePt'];
       $this->FontSize = $saved['size'];
       $this->CurrentFont =& $saved['curr'];
       $this->TextColor = $saved['color']; //EDITEI
       $this->FillColor = $saved['bgcolor']; //EDITEI
       $this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well
       $this->underline = $saved['underline']; //EDITEI
       $this->strike = $saved['strike']; //EDITEI
       $this->SUP = $saved['SUP']; //EDITEI
       $this->SUB = $saved['SUB']; //EDITEI
       $this->LineWidth = $saved['linewidth']; //EDITEI
       $this->DrawColor = $saved['drawcolor']; //EDITEI
       $this->outline_on = $saved['is_outline']; //EDITEI
    
       if( $this->page > 0)
          $this->_out( sprintf( 'BT /F%d %.2f Tf ET', $this->CurrentFont['i'], $this->FontSizePt ) );
    }
    
    function newFlowingBlock( $w, $h, $b = 0, $a = 'J', $f = 0 , $is_table = false )
    {
       // cell width in points
       if ($is_table)  $this->flowingBlockAttr['width'] = ($w * $this->k);
       else $this->flowingBlockAttr['width'] = ($w * $this->k) - (2*$this->cMargin*$this->k);
       // line height in user units
       $this->flowingBlockAttr['is_table'] = $is_table;
       $this->flowingBlockAttr['height'] = $h;
       $this->flowingBlockAttr['lineCount'] = 0;
       $this->flowingBlockAttr['border'] = $b;
       $this->flowingBlockAttr['align'] = $a;
       $this->flowingBlockAttr['fill'] = $f;
       $this->flowingBlockAttr['font'] = array();
       $this->flowingBlockAttr['content'] = array();
       $this->flowingBlockAttr['contentWidth'] = 0;
    }
    
    function finishFlowingBlock($outofblock=false)
    {
        if (!$outofblock)
            $currentx = $this->x;
            
        $is_table = $this->flowingBlockAttr['is_table'];
        $maxWidth =& $this->flowingBlockAttr['width'];
        $lineHeight =& $this->flowingBlockAttr['height'];
        $border =& $this->flowingBlockAttr['border'];
        $align =& $this->flowingBlockAttr['align'];
        $fill =& $this->flowingBlockAttr['fill'];
        $content =& $this->flowingBlockAttr['content'];
        $font =& $this->flowingBlockAttr['font'];
        $contentWidth =& $this->flowingBlockAttr['contentWidth'];
        $lineCount =& $this->flowingBlockAttr['lineCount'];
     
        // set normal spacing
        $this->_out( sprintf( '%.3f Tw', 0 ) );
        $this->ws = 0;
     
        // the amount of space taken up so far in user units
        $usedWidth = 0;
     
        $empty = $maxWidth - $contentWidth;
        $empty /= $this->k;
        $b = ''; //do not use borders
        $arraysize = count($content);
        $margins = (2*$this->cMargin);
        if ($outofblock)
        {
           $align = 'C';
           $empty = 0;
           $margins = $this->cMargin;
        }
        switch($align)
        {
           case 'R':
               foreach ( $content as $k => $chunk )
               {
                   $this->restoreFont( $font[ $k ] );
                   $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                   // determine which borders should be used
                   $b = '';
                   if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
                   if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) ) $b .= 'R';
                           
                   if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
                   else $skipln = 0;
     
                   if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, '' , $currentx ); //mono-style line
                   elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 0, 'R', $fill, '' );//first part
                   elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, $skipln, '', $fill, '', $currentx );//last part
                   else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, '' );//middle part
               }
               break;
           case 'L':
           case 'J':
               foreach ( $content as $k => $chunk )
               {
                   $this->restoreFont( $font[ $k ] );
                   $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                   // determine which borders should be used
                   $b = '';
                   if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
                   if ( $k == 0 && is_int( strpos( $border, 'L' ) ) ) $b .= 'L';
     
                   if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
                   else $skipln = 0;
     
                   if (!$is_table and !$outofblock and !$fill and $align=='L' and $k == 0) {$align='';$margins=0;} //Remove margins in this special (though often) case
     
                   if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, '' , $currentx ); //mono-style line
                   elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, $skipln, $align, $fill, '' );//first part
                   elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, $skipln, '', $fill, '', $currentx );//last part
                   else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, $skipln, '', $fill, '' );//middle part
               }
               break;
           case 'C':
               foreach ( $content as $k => $chunk )
               {
                   $this->restoreFont( $font[ $k ] );
                   $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                   // determine which borders should be used
                   $b = '';
                   if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
     
                   if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
                   else $skipln = 0;
     
                   if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, '' , $currentx ); //mono-style line
                   elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 0, 'R', $fill, '' );//first part
                   elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, $skipln, 'L', $fill, '', $currentx );//last part
                   else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, '' );//middle part
               }
               break;
          default: break;
        }
    }
    
    function WriteFlowingBlock($s , $outofblock = false)
    {
        if (!$outofblock) $currentx = $this->x; //EDITEI - in order to make the Cell method work better
        $is_table = $this->flowingBlockAttr['is_table'];
        // width of all the content so far in points
        $contentWidth =& $this->flowingBlockAttr['contentWidth'];
        // cell width in points
        $maxWidth =& $this->flowingBlockAttr['width'];
        $lineCount =& $this->flowingBlockAttr['lineCount'];
        // line height in user units
        $lineHeight =& $this->flowingBlockAttr['height'];
        $border =& $this->flowingBlockAttr['border'];
        $align =& $this->flowingBlockAttr['align'];
        $fill =& $this->flowingBlockAttr['fill'];
        $content =& $this->flowingBlockAttr['content'];
        $font =& $this->flowingBlockAttr['font'];
    
        $font[] = $this->saveFont();
        $content[] = '';
    
        $currContent =& $content[ count( $content ) - 1 ];
    
        // where the line should be cutoff if it is to be justified
        $cutoffWidth = $contentWidth;
    
        // for every character in the string
        for ( $i = 0; $i < strlen( $s ); $i++ )
        {
           // extract the current character
           $c = $s{$i};
           // get the width of the character in points
           $cw = $this->CurrentFont['cw'][ $c ] * ( $this->FontSizePt / 1000 );
    
           if ( $c == ' ' )
           {
               $currContent .= ' ';
               $cutoffWidth = $contentWidth;
               $contentWidth += $cw;
               continue;
           }
           // try adding another char
           if ( $contentWidth + $cw > $maxWidth )
           {
               // it won't fit, output what we already have
               $lineCount++;
               //Readjust MaxSize in order to use the whole page width
               if ($outofblock and ($lineCount == 1) ) $maxWidth = $this->pgwidth * $this->k;
               // contains any content that didn't make it into this print
               $savedContent = '';
               $savedFont = array();
               // first, cut off and save any partial words at the end of the string
               $words = explode( ' ', $currContent );
               
               // if it looks like we didn't finish any words for this chunk
               if ( count( $words ) == 1 )
               {
                  // save and crop off the content currently on the stack
                  $savedContent = array_pop( $content );
                  $savedFont = array_pop( $font );
    
                  // trim any trailing spaces off the last bit of content
                  $currContent =& $content[ count( $content ) - 1 ];
                  $currContent = rtrim( $currContent );
               }
               else // otherwise, we need to find which bit to cut off
               {
                  $lastContent = '';
                  for ( $w = 0; $w < count( $words ) - 1; $w++) $lastContent .= "{$words[ $w ]} ";
    
                  $savedContent = $words[ count( $words ) - 1 ];
                  $savedFont = $this->saveFont();
                  // replace the current content with the cropped version
                  $currContent = rtrim( $lastContent );
               }
               // update $contentWidth and $cutoffWidth since they changed with cropping
               $contentWidth = 0;
               foreach ( $content as $k => $chunk )
               {
                  $this->restoreFont( $font[ $k ] );
                  $contentWidth += $this->GetStringWidth( $chunk ) * $this->k;
               }
               $cutoffWidth = $contentWidth;
               // if it's justified, we need to find the char spacing
               if( $align == 'J' )
               {
                  // count how many spaces there are in the entire content string
                  $numSpaces = 0;
                  foreach ( $content as $chunk ) $numSpaces += substr_count( $chunk, ' ' );
                  // if there's more than one space, find word spacing in points
                  if ( $numSpaces > 0 ) $this->ws = ( $maxWidth - $cutoffWidth ) / $numSpaces;
                  else $this->ws = 0;
                  $this->_out( sprintf( '%.3f Tw', $this->ws ) );
               }
               // otherwise, we want normal spacing
               else $this->_out( sprintf( '%.3f Tw', 0 ) );
    
               //EDITEI - Print content according to alignment
               if (!isset($numSpaces)) $numSpaces = 0;
               $contentWidth -= ($this->ws*$numSpaces);
               $empty = $maxWidth - $contentWidth - 2*($this->ws*$numSpaces);
               $empty /= $this->k;
               $b = ''; //do not use borders
               
               if($this->y+$this->divheight>$this->PageBreakTrigger and !$this->InFooter and $this->AutoPageBreak)
                 {
                    $bak_x=$this->x;//Current X position
                    $ws=$this->ws;//Word Spacing
                      if($ws>0)
                      {
                         $this->ws=0;
                         $this->_out('0 Tw');
                      }
                      $this->addPage($this->CurOrientation);
                      $this->x=$bak_x;
                      if($ws>0)
                      {
                         $this->ws=$ws;
                         $this->_out(sprintf('%.3f Tw',$ws));
                    }
                 }
               $arraysize = count($content);
               $margins = (2*$this->cMargin);
               if ($outofblock)
               {
                  $align = 'C';
                  $empty = 0;
                  $margins = $this->cMargin;
               }
               switch($align)
               {
                 case 'R':
                     foreach ( $content as $k => $chunk )
                     {
                         $this->restoreFont( $font[ $k ] );
                         $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                         // determine which borders should be used
                         $b = '';
                         if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
                         if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) ) $b .= 'R';
    
                         if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, '' , $currentx ); //mono-style line
                         elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 0, 'R', $fill, '' );//first part
                         elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, 1, '', $fill, '', $currentx );//last part
                         else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, '' );//middle part
                     }
                    break;
                 case 'L':
                 case 'J':
                     foreach ( $content as $k => $chunk )
                     {
                         $this->restoreFont( $font[ $k ] );
                         $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                         // determine which borders should be used
                         $b = '';
                         if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
                         if ( $k == 0 && is_int( strpos( $border, 'L' ) ) ) $b .= 'L';
    
                         if (!$is_table and !$outofblock and !$fill and $align=='L' and $k == 0)
                         {
                             //Remove margins in this special (though often) case
                             $align='';
                             $margins=0;
                         }
    
                         if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, '' , $currentx ); //mono-style line
                         elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, 0, $align, $fill, '' );//first part
                         elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 1, '', $fill, '', $currentx );//last part
                         else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, '' );//middle part
    
                         if (!$is_table and !$outofblock and !$fill and $align=='' and $k == 0)
                         {
                             $align = 'L';
                             $margins = (2*$this->cMargin);
                         }
                     }
                     break;
                 case 'C':
                     foreach ( $content as $k => $chunk )
                     {
                         $this->restoreFont( $font[ $k ] );
                         $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                         // determine which borders should be used
                         $b = '';
                         if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
    
                         if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, '' , $currentx ); //mono-style line
                         elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 0, 'R', $fill, '' );//first part
                         elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 1, 'L', $fill, '', $currentx );//last part
                         else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, '' );//middle part
                     }
                     break;
                     default: break;
               }
               // move on to the next line, reset variables, tack on saved content and current char
               $this->restoreFont( $savedFont );
               $font = array( $savedFont );
               $content = array( $savedContent . $s{ $i } );
    
               $currContent =& $content[ 0 ];
               $contentWidth = $this->GetStringWidth( $currContent ) * $this->k;
               $cutoffWidth = $contentWidth;
           }
           // another character will fit, so add it on
           else
           {
               $contentWidth += $cw;
               $currContent .= $s{ $i };
           }
        }
    }
    
    function WordWrap(&$text, $maxwidth)
    {
        $biggestword=0;//EDITEI
        $toonarrow=false;//EDITEI
    
        $text = trim($text);
        if ($text==='') return 0;
        $space = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;
    
        foreach ($lines as $line)
        {
            $words = preg_split('/ +/', $line);
            $width = 0;
    
            foreach ($words as $word)
            {
                $wordwidth = $this->GetStringWidth($word);
    
                  if ($wordwidth > $maxwidth)
                  {
                     if ($wordwidth > $biggestword) $biggestword = $wordwidth;
                       $toonarrow=true;//EDITEI
                  }
                if ($width + $wordwidth <= $maxwidth)
                {
                    $width += $wordwidth + $space;
                    $text .= $word.' ';
                }
                else
                {
                    $width = $wordwidth + $space;
                    $text = rtrim($text)."\n".$word.' ';
                    $count++;
                }
            }
            $text = rtrim($text)."\n";
            $count++;
        }
        $text = rtrim($text);
    
        //Return -(wordsize) if word is bigger than maxwidth 
        if ($toonarrow) return -$biggestword;
        else return $count;
    }
    
    function _SetTextRendering($mode)
    { 
        if (!(($mode == 0) || ($mode == 1) || ($mode == 2))) 
            throw new Exception("Text rendering mode should be 0, 1 or 2 (value : $mode)"); 
        
        $this->_out($mode.' Tr'); 
    } 
    
    public function setTextOutline($width, $r=0, $g=-1, $b=-1) //EDITEI
    { 
      if ($width == false) //Now resets all values
      { 
        $this->outline_on = false;
        $this->SetLineWidth(0.2); 
        $this->SetDrawColor(0); 
        $this->_setTextRendering(0); 
        $this->_out('0 Tr'); 
      }
      else
      { 
        $this->SetLineWidth($width); 
        $this->SetDrawColor($r, $g , $b); 
        $this->_out('2 Tr'); //Fixed
      } 
    }
    
    function Circle($x,$y,$r,$style='')
    {
        $this->Ellipse($x,$y,$r,$r,$style);
    }
    
    function Ellipse($x,$y,$rx,$ry,$style='D')
    {
        if($style=='F') $op='f';
        elseif($style=='FD' or $style=='DF') $op='B';
        else $op='S';
        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->k;
        $h=$this->h;
        $this->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }
    
    function Image($file,$x,$y,$w=0,$h=0,$type='',$link='',$paint=true)
    {
        if(!isset($this->images[$file]))
        {
            if($type=='')
            {
                $pos=strrpos($file,'.');
                if(!$pos)	throw new Exception('Image file has no extension and no type was specified: '.$file);
                $type=substr($file,$pos+1);
            }
            $type=strtolower($type);
            
            if($type=='jpg' or $type=='jpeg')	$info=$this->_parsejpg($file);
            elseif($type=='png') $info=$this->_parsepng($file);
            else
            {
                //Allow for additional formats
                $mtd='_parse'.$type;
                if(!method_exists($this,$mtd)) throw new Exception('Unsupported image type: '.$type);
                $info=$this->$mtd($file);
            }
            
            $info['i']=count($this->images)+1;
            $this->images[$file]=$info;
        }
        else $info=$this->images[$file];
        //Automatic width and height calculation if needed
        if($w==0 and $h==0)
        {
            //Put image at 72 dpi
            $w=$info['w']/$this->k;
            $h=$info['h']/$this->k;
        }
        if($w==0)	$w=$h*$info['w']/$info['h'];
        if($h==0)	$h=$w*$info['h']/$info['w'];
    
        $changedpage = false; //EDITEI
    
        //Avoid drawing out of the paper(exceeding width limits). //EDITEI
        if ( ($x + $w) > $this->fw )
        {
            $x = $this->lMargin;
            $y += 5;
        }
        //Avoid drawing out of the page. //EDITEI
        if ( ($y + $h) > $this->fh ) 
        {
            $this->addPage();
            $y = $tMargin + 10; // +10 to avoid drawing too close to border of page
            $changedpage = true;
        }
    
        $outstring = sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']);
    
        if($paint) //EDITEI
        {
            $this->_out($outstring);
            if($link) $this->Link($x,$y,$w,$h,$link);
        }
    
        //Avoid writing text on top of the image. //EDITEI
        if ($changedpage) $this->y = $y + $h;
        else $this->y = $y + $h;
    
        //Return width-height array //EDITEI
        $sizesarray['WIDTH'] = $w;
        $sizesarray['HEIGHT'] = $h;
        $sizesarray['X'] = $x; //Position before painting image
        $sizesarray['Y'] = $y; //Position before painting image
        $sizesarray['OUTPUT'] = $outstring;
        return $sizesarray;
    }
    
    function DottedRect($x=100,$y=150,$w=50,$h=50)
    {
        $x *= $this->k ;
        $y = ($this->h-$y)*$this->k;
        $w *= $this->k ;
        $h *= $this->k ;// - h?
         
        $herex = $x;
        $herey = $y;
      
        //Make fillcolor == drawcolor
        $bak_fill = $this->FillColor;
        $this->FillColor = $this->DrawColor;
        $this->FillColor = str_replace('RG','rg',$this->FillColor);
        $this->_out($this->FillColor);
       
        while ($herex < ($x + $w)) //draw from upper left to upper right
        {
        $this->DrawDot($herex,$herey);
        $herex += (3*$this->k);
        }
        $herex = $x + $w;
        while ($herey > ($y - $h)) //draw from upper right to lower right
        {
        $this->DrawDot($herex,$herey);
        $herey -= (3*$this->k);
        }
        $herey = $y - $h;
        while ($herex > $x) //draw from lower right to lower left
        {
        $this->DrawDot($herex,$herey);
        $herex -= (3*$this->k);
        }
        $herex = $x;
        while ($herey < $y) //draw from lower left to upper left
        {
        $this->DrawDot($herex,$herey);
        $herey += (3*$this->k);
        }
        $herey = $y;
      
        $this->FillColor = $bak_fill;
        $this->_out($this->FillColor);
    }
    
    function DrawDot($x,$y) //center x y
    {
        $op = 'B'; // draw Filled Dots
        $r = 0.5 * $this->k;  //raio
      
        $x1 = $x - $r;
        $y1 = $y;
        //End Point
        $x2 = $x + $r;
        $y2 = $y;
        //Auxiliar Point
        $x3 = $x;
        $y3 = $y + (2*$r);// 2*raio to make a round (not oval) shape  
      
        //Round join and cap
        $s="\n".'1 J'."\n";
        $s.='1 j'."\n";
      
        //Upper circle
        $s.=sprintf('%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
        $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);//Bezier curve
        //Lower circle
        $y3 = $y - (2*$r);
        $s.=sprintf("\n".'%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
        $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);
        $s.=$op."\n"; //stroke and fill
      
        //Draw in PDF file
        $this->_out($s);
    }
    
    public function setDash($black=false,$white=false)
    {
        if($black and $white)
            $s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
        else $s='[] 0 d';
            $this->_out($s);
    }
    
    function Bookmark($txt,$level=0,$y=0)
    {
        if($y == -1) $y = $this->GetY();
        $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
    }
    
    function _putbookmarks()
    {
        $nb=count($this->outlines);
        if($nb==0) return;
        $lru=array();
        $level=0;
        foreach($this->outlines as $i=>$o)
        {
            if($o['l']>0)
            {
                $parent=$lru[$o['l']-1];
                //Set parent and last pointers
                $this->outlines[$i]['parent']=$parent;
                $this->outlines[$parent]['last']=$i;
                if($o['l']>$level)
                {
                    //Level increasing: set first pointer
                    $this->outlines[$parent]['first']=$i;
                }
            }
            else
                $this->outlines[$i]['parent']=$nb;
            if($o['l']<=$level and $i>0)
            {
                //Set prev and next pointers
                $prev=$lru[$o['l']];
                $this->outlines[$prev]['next']=$i;
                $this->outlines[$i]['prev']=$prev;
            }
            $lru[$o['l']]=$i;
            $level=$o['l'];
        }
        //Outline items
        $n=$this->n+1;
        foreach($this->outlines as $i=>$o)
        {
            $this->_newobj();
            $this->_out('<</Title '.$this->_textstring($o['t']));
            $this->_out('/Parent '.($n+$o['parent']).' 0 R');
            if(isset($o['prev']))
                $this->_out('/Prev '.($n+$o['prev']).' 0 R');
            if(isset($o['next']))
                $this->_out('/Next '.($n+$o['next']).' 0 R');
            if(isset($o['first']))
                $this->_out('/First '.($n+$o['first']).' 0 R');
            if(isset($o['last']))
                $this->_out('/Last '.($n+$o['last']).' 0 R');
            $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
            $this->_out('/Count 0>>');
            $this->_out('endobj');
        }
        //Outline root
        $this->_newobj();
        $this->OutlineRoot=$this->n;
        $this->_out('<</Type /Outlines /First '.$n.' 0 R');
        $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
        $this->_out('endobj');
    }
    
    function Ln($h='')
    {
        //Line feed; default value is last cell height
        $this->x=$this->lMargin;
        if(is_string($h)) $this->y+=$this->lasth;
        else $this->y+=$h;
    }
    
    function GetX()
    {
        //Get x position
        return $this->x;
    }
    
    protected function setX($x)
    {
        if($x >= 0)	$this->x=$x;
        else $this->x = $this->w + $x;
    }
    
    protected function getY()
    {
        return $this->y;
    }
    
    public function setY($y)
    {
        //Set y position and reset x
        $this->x=$this->lMargin;
        if($y>=0)
            $this->y=$y;
        else
            $this->y=$this->h+$y;
    }
    
    public function setXY($x,$y)
    {
        $this->SetY($y);
        $this->SetX($x);
    }
    
    public function output($name='doc.pdf', $dest='')
    {
        if($this->state < 3)
            $this->Close();
        
        switch($dest)
        {
            case 'I':
                Header('Content-Type: application/pdf');
                
                if(headers_sent())
                    throw new Exception('Some data has already been output to browser, can\'t send PDF file');
                Header('Content-Length: '.strlen($this->buffer));
                Header('Content-disposition: inline; filename='.$name);
                
                echo $this->buffer;
                break;
            case 'D':
                Header('Content-Type: application/pdf');
                    
                if(headers_sent())
                    throw new Exception('Some data has already been output to browser, can\'t send PDF file');
                Header('Content-Length: '.strlen($this->buffer));
                Header('Content-disposition: attachment; filename='.$name);
                
                echo $this->buffer;
                break;
            case 'F':
                file_put_contents($name, $this->buffer);
                break;
            default:
                return $this->buffer;
        }
    }
    
    protected function _putpages()
    {
        $nb=$this->page;
        if(!empty($this->AliasNbPages))
        {
            for($n=1;$n<=$nb;$n++)
                $this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
        }
        if($this->DefOrientation=='P')
        {
            $wPt=$this->fwPt;
            $hPt=$this->fhPt;
        }
        else
        {
            $wPt=$this->fhPt;
            $hPt=$this->fwPt;
        }
        $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
        for($n=1;$n<=$nb;$n++)
        {
            //Page
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            
            if(isset($this->OrientationChanges[$n]))
                $this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$hPt,$wPt));
                
            $this->_out('/Resources 2 0 R');
            $this->_out('/Contents '.($this->n+1).' 0 R>>');
            $this->_out('endobj');
            //Page content
            $p=($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
            $this->_newobj();
            $this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }
        //Pages root
        $this->offsets[1]=strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Pages');
        $kids='/Kids [';
        for($i=0;$i<$nb;$i++)
            $kids.=(3+2*$i).' 0 R ';
        $this->_out($kids.']');
        $this->_out('/Count '.$nb);
        $this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$wPt,$hPt));
        $this->_out('>>');
        $this->_out('endobj');
    }
    
    protected function _putfonts()
    {
        $nf=$this->n;
        
        foreach ($this->diffs as $diff)
        {
            //Encodings
            $this->_newobj();
            $this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
            $this->_out('endobj');
        }
        
        foreach ($this->FontFiles as $file=>$info)
        {
            $this->_newobj();
            $this->FontFiles[$file]['n']=$this->n;
            
            $size=filesize("fonts/" . $file);
            
            if(!$size)
                throw new Exception('Font file not found');
            
            $this->_out('<</Length '.$size);
            if(substr($file,-2)=='.z')
                $this->_out('/Filter /FlateDecode');
            $this->_out('/Length1 '.$info['length1']);
            if(isset($info['length2']))
                $this->_out('/Length2 '.$info['length2'].' /Length3 0');
            $this->_out('>>');
            $f=fopen($file,'rb');
            $this->_putstream(fread($f,$size));
            fclose($f);
            $this->_out('endobj');
        }
        
        foreach($this->fonts as $k=>$font)
        {
            //Font objects
            $this->fonts[$k]['n']=$this->n+1;
            $type=$font['type'];
            $name=$font['name'];
            if($type=='core')
            {
                //Standard font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /'.$name);
                $this->_out('/Subtype /Type1');
                if($name!='Symbol' and $name!='ZapfDingbats')
                    $this->_out('/Encoding /WinAnsiEncoding');
                $this->_out('>>');
                $this->_out('endobj');
            }
            elseif($type=='Type1' or $type=='TrueType')
            {
                //Additional Type1 or TrueType font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /'.$name);
                $this->_out('/Subtype /'.$type);
                $this->_out('/FirstChar 32 /LastChar 255');
                $this->_out('/Widths '.($this->n+1).' 0 R');
                $this->_out('/FontDescriptor '.($this->n+2).' 0 R');
                if($font['enc'])
                {
                    if(isset($font['diff']))
                        $this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
                    else
                        $this->_out('/Encoding /WinAnsiEncoding');
                }
                $this->_out('>>');
                $this->_out('endobj');
                //Widths
                $this->_newobj();
                $cw=&$font['cw'];
                $s='[';
                for($i=32;$i<=255;$i++)
                    $s.=$cw[chr($i)].' ';
                $this->_out($s.']');
                $this->_out('endobj');
                //Descriptor
                $this->_newobj();
                $s='<</Type /FontDescriptor /FontName /'.$name;
                foreach($font['desc'] as $k=>$v)
                    $s.=' /'.$k.' '.$v;
                $file=$font['file'];
                if($file)
                    $s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
                $this->_out($s.'>>');
                $this->_out('endobj');
            }
            else
            {
                //Allow for additional types
                $mtd='_put'.strtolower($type);
                if(!method_exists($this,$mtd))
                    throw new Exception('Unsupported font type: '.$type);
                $this->$mtd($font);
            }
        }
    }
    
    protected function _putimages()
    {
        $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
        reset($this->images);
        while(list($file,$info)=each($this->images))
        {
            $this->_newobj();
            $this->images[$file]['n']=$this->n;
            $this->_out('<</Type /XObject');
            $this->_out('/Subtype /Image');
            $this->_out('/Width '.$info['w']);
            $this->_out('/Height '.$info['h']);
            if($info['cs']=='Indexed')
                $this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
            else
            {
                $this->_out('/ColorSpace /'.$info['cs']);
                if($info['cs']=='DeviceCMYK')
                    $this->_out('/Decode [1 0 1 0 1 0 1 0]');
            }
            $this->_out('/BitsPerComponent '.$info['bpc']);
            $this->_out('/Filter /'.$info['f']);
            if(isset($info['parms']))
                $this->_out($info['parms']);
            if(isset($info['trns']) and is_array($info['trns']))
            {
                $trns='';
                for($i=0;$i<count($info['trns']);$i++)
                    $trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
                $this->_out('/Mask ['.$trns.']');
            }
            $this->_out('/Length '.strlen($info['data']).'>>');
            $this->_putstream($info['data']);
            unset($this->images[$file]['data']);
            $this->_out('endobj');
            //Palette
            if($info['cs']=='Indexed')
            {
                $this->_newobj();
                $pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
                $this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
                $this->_putstream($pal);
                $this->_out('endobj');
            }
        }
    }
    
    protected function _putresources()
    {
        $this->_putfonts();
        $this->_putimages();
        //Resource dictionary
        $this->offsets[2]=strlen($this->buffer);
        $this->_out('2 0 obj');
        $this->_out('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
        $this->_out('/Font <<');
        foreach($this->fonts as $font)
            $this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
        $this->_out('>>');
        if(count($this->images))
        {
            $this->_out('/XObject <<');
            foreach($this->images as $image)
                $this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
            $this->_out('>>');
        }
        $this->_out('>>');
        $this->_out('endobj');
        $this->_putbookmarks();
    }
    
    protected function _putinfo()
    {
        if(!empty($this->title))
            $this->_out('/Title '.$this->_textstring($this->title));
        if(!empty($this->subject))
            $this->_out('/Subject '.$this->_textstring($this->subject));
        if(!empty($this->author))
            $this->_out('/Author '.$this->_textstring($this->author));
        if(!empty($this->creator))
            $this->_out('/Creator '.$this->_textstring($this->creator));
        $this->_out('/CreationDate '.$this->_textstring('D:'.date('YmdHis')));
    }
    
    protected function _putcatalog()
    {
        $this->_out('/Type /Catalog');
        $this->_out('/Pages 1 0 R');
        
        if($this->ZoomMode=='fullpage')	$this->_out('/OpenAction [3 0 R /Fit]');
        elseif($this->ZoomMode=='fullwidth') $this->_out('/OpenAction [3 0 R /FitH null]');
        elseif($this->ZoomMode=='real')	$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
        elseif(!is_string($this->ZoomMode))	$this->_out('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
        if($this->LayoutMode=='single')	$this->_out('/PageLayout /SinglePage');
        elseif($this->LayoutMode=='continuous')	$this->_out('/PageLayout /OneColumn');
        elseif($this->LayoutMode=='two') $this->_out('/PageLayout /TwoColumnLeft');
      
        if(count($this->outlines)>0)
        {
            $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
            $this->_out('/PageMode /UseOutlines');
        }
    }
    
    protected function _puttrailer()
    {
        $this->_out('/Size '.($this->n+1));
        $this->_out('/Root '.$this->n.' 0 R');
        $this->_out('/Info '.($this->n-1).' 0 R');
    }
    
    protected function _beginpage($orientation)
    {
        $this->page++;
        $this->pages[$this->page]='';
        $this->state=2;
        $this->x=$this->lMargin;
        $this->y=$this->tMargin;
        $this->FontFamily='';
        
        if(!$orientation)
            $orientation=$this->DefOrientation;
        else
        {
            $orientation=strtoupper($orientation{0});
            if($orientation!=$this->DefOrientation)
                $this->OrientationChanges[$this->page]=true;
        }
        if($orientation!=$this->CurOrientation)
        {
            //Change orientation
            if($orientation=='P')
            {
                $this->wPt=$this->fwPt;
                $this->hPt=$this->fhPt;
                $this->w=$this->fw;
                $this->h=$this->fh;
            }
            else
            {
                $this->wPt=$this->fhPt;
                $this->hPt=$this->fwPt;
                $this->w=$this->fh;
                $this->h=$this->fw;
            }
            $this->PageBreakTrigger=$this->h-$this->bMargin;
            $this->CurOrientation=$orientation;
        }
    }
    
    protected function _endpage()
    {
        //End of page contents
        $this->state=1;
    }
    
    protected function _newobj()
    {
        //Begin a new object
        $this->n++;
        $this->offsets[$this->n]=strlen($this->buffer);
        $this->_out($this->n.' 0 obj');
    }
    
    protected function _dounderline($x,$y,$txt)
    {
        //Underline text
        $up=$this->CurrentFont['up'];
        $ut=$this->CurrentFont['ut'];
        $w=$this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
        return sprintf('%.2f %.2f %.2f %.2f re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
    }
    
    protected function _parsejpg($file)
    {
        //Extract info from a JPEG file
        $a=GetImageSize($file);
        if(!$a)
            throw new Exception('Missing or incorrect image file: '.$file);
        if($a[2]!=2)
            throw new Exception('Not a JPEG file: '.$file);
        if(!isset($a['channels']) or $a['channels']==3)
            $colspace='DeviceRGB';
        elseif($a['channels']==4)
            $colspace='DeviceCMYK';
        else
            $colspace='DeviceGray';
        $bpc=isset($a['bits']) ? $a['bits'] : 8;
        //Read whole file
        $f=fopen($file,'rb');
        $data='';
        while(!feof($f))
            $data.=fread($f,4096);
        fclose($f);
        return array('w'=>$a[0],'h'=>$a[1],'cs'=>$colspace,'bpc'=>$bpc,'f'=>'DCTDecode','data'=>$data);
    }
    
    protected function _parsepng($file)
    {
        $f=fopen($file,'rb');
        
        if(!$f) throw new Exception('Can\'t open image file: '.$file);
        
        if(fread($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
            throw new Exception('Not a PNG file: '.$file);
        
        //Read header chunk
        fread($f,4);
        if(fread($f,4)!='IHDR')	throw new Exception('Incorrect PNG file: '.$file);
        $w=$this->_freadint($f);
        $h=$this->_freadint($f);
        $bpc=ord(fread($f,1));
        if($bpc>8) throw new Exception('16-bit depth not supported: '.$file);
        $ct=ord(fread($f,1));
        
        if($ct==0) $colspace='DeviceGray';
        elseif($ct==2) $colspace='DeviceRGB';
        elseif($ct==3) $colspace='Indexed';
        else throw new Exception('Alpha channel not supported: '.$file);
        
        if(ord(fread($f,1))!=0)	throw new Exception('Unknown compression method: '.$file);
        if(ord(fread($f,1))!=0)	throw new Exception('Unknown filter method: '.$file);
        if(ord(fread($f,1))!=0)	throw new Exception('Interlacing not supported: '.$file);
        fread($f,4);
        $parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
        
        $pal='';
        $trns='';
        $data='';
        do
        {
            $n=$this->_freadint($f);
            $type=fread($f,4);
            
            if($type=='PLTE')
            {
                $pal=fread($f,$n);
                fread($f,4);
            }
            elseif($type=='tRNS')
            {
                $t=fread($f,$n);
                if($ct==0) $trns=array(ord(substr($t,1,1)));
                elseif($ct==2) $trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1)));
                else
                {
                    $pos=strpos($t,chr(0));
                    if(is_int($pos)) $trns=array($pos);
                }
                fread($f,4);
            }
            elseif($type=='IDAT')
            {
                $data.=fread($f,$n);
                fread($f,4);
            }
            elseif($type=='IEND')
                break;
            else
                fread($f,$n+4);
        }
        while($n);
        
        if($colspace=='Indexed' and empty($pal))
            throw new Exception('Missing palette in '.$file);
        
        fclose($f);
        
        return array('w'=>$w,'h'=>$h,'cs'=>$colspace,'bpc'=>$bpc,'f'=>'FlateDecode',
                     'parms'=>$parms,'pal'=>$pal,'trns'=>$trns,'data'=>$data);
    }
    
    protected function _freadint($f)
    {
        $i=ord(fread($f,1))<<24;
        $i+=ord(fread($f,1))<<16;
        $i+=ord(fread($f,1))<<8;
        $i+=ord(fread($f,1));
        return $i;
    }
    
    protected function _textstring($s)
    {
        return '('.$this->_escape($s).')';
    }
    
    protected function _escape($s)
    {
        return str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$s)));
    }
    
    protected function _putstream($s)
    {
        $this->_out('stream');
        $this->_out($s);
        $this->_out('endstream');
    }
    
    protected function _out($s)
    {
        if ($this->state==2)
            $this->pages[$this->page] .= $s."\n";
        else
            $this->buffer .= $s."\n";
    }
}
?>