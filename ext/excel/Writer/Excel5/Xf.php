<?php
/**
 * Class for generating Excel XF records (formats)
 *
 * @author   Xavier Noguer <xnoguer@rezebra.com>
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel5_Xf extends ObjectBase
{
    /**
     * BIFF version
     *
     * @var int
     */
    private $_BIFFVersion;
    
    /**
     * Style XF or a cell XF ?
     *
     * @var boolean
     */
    private $_isStyleXf;
    
    /**
     * Index to the FONT record. Index 4 does not exist
     * @var integer
     */
    private $_fontIndex;
    
    /**
     * An index (2 bytes) to a FORMAT record (number format).
     * @var integer
     */
    var $_numberFormatIndex;
    
    /**
     * 1 bit, apparently not used.
     * @var integer
     */
    var $_text_justlast;
    
    /**
     * The cell's foreground color.
     * @var integer
     */
    var $_fg_color;
    
    /**
     * The cell's background color.
     * @var integer
     */
    var $_bg_color;
    
    /**
     * Color of the bottom border of the cell.
     * @var integer
     */
    var $_bottom_color;
    
    /**
     * Color of the top border of the cell.
     * @var integer
     */
    var $_top_color;
    
    /**
     * Color of the left border of the cell.
     * @var integer
     */
    var $_left_color;
    
    /**
     * Color of the right border of the cell.
     * @var integer
     */
    var $_right_color;
    
    /**
     * Constructor
     *
     * @access private
     * @param integer $index the XF index for the format.
     * @param ExcelStyle
     */
    public function __construct($style = null)
    {
        $this->_isStyleXf   = false;
        $this->_BIFFVersion = 0x0600;
        $this->_fontIndex   = 0;
        
        $this->_numberFormatIndex = 0;
        
        $this->_text_justlast = 0;
        
        $this->_fg_color = 0x40;
        $this->_bg_color = 0x41;
        
        $this->_diag = 0;
        
        $this->_bottom_color = 0x40;
        $this->_top_color    = 0x40;
        $this->_left_color   = 0x40;
        $this->_right_color  = 0x40;
        $this->_diag_color   = 0x40;
        $this->_style        = $style;
    }
    
    /**
     * Generate an Excel BIFF XF record (style or cell).
     *
     * @param string $style The type of the XF record ('style' or 'cell').
     * @return string The XF record
     */
    function writeXf()
    {
        // Set the type of the XF record and some of the attributes.
        if ($this->_isStyleXf)
        {
            $style = 0xFFF5;
        }
        else
        {
            $style = $this->_mapLocked($this->_style->getProtection()->getLocked());
            $style |= $this->_mapHidden($this->_style->getProtection()->getHidden()) << 1;
        }
        
        // Flags to indicate if attributes have been set.
        $atr_num  = ($this->_numberFormatIndex != 0) ? 1 : 0;
        $atr_fnt  = ($this->_fontIndex != 0) ? 1 : 0;
        $atr_alc  = ((int) $this->_style->getAlignment()->getWrapText()) ? 1 : 0;
        $atr_bdr  = ($this->_mapBorderStyle($this->_style->getBorders()->getBottom()->getBorderStyle()) || $this->_mapBorderStyle($this->_style->getBorders()->getTop()->getBorderStyle()) || $this->_mapBorderStyle($this->_style->getBorders()->getLeft()->getBorderStyle()) || $this->_mapBorderStyle($this->_style->getBorders()->getRight()->getBorderStyle())) ? 1 : 0;
        $atr_pat  = (($this->_fg_color != 0x40) || ($this->_bg_color != 0x41) || $this->_mapFillType($this->_style->getFill()->getFillType())) ? 1 : 0;
        $atr_prot = $this->_mapLocked($this->_style->getProtection()->getLocked()) | $this->_mapHidden($this->_style->getProtection()->getHidden());
        
        // Zero the default border colour if the border has not been set.
        if ($this->_mapBorderStyle($this->_style->getBorders()->getBottom()->getBorderStyle()) == 0)
        {
            $this->_bottom_color = 0;
        }
        if ($this->_mapBorderStyle($this->_style->getBorders()->getTop()->getBorderStyle()) == 0)
        {
            $this->_top_color = 0;
        }
        if ($this->_mapBorderStyle($this->_style->getBorders()->getRight()->getBorderStyle()) == 0)
        {
            $this->_right_color = 0;
        }
        if ($this->_mapBorderStyle($this->_style->getBorders()->getLeft()->getBorderStyle()) == 0)
        {
            $this->_left_color = 0;
        }
        if ($this->_diag == 0)
        {
            $this->_diag_color = 0;
        }
        
        $record = 0x00E0; // Record identifier
        if ($this->_BIFFVersion == 0x0500)
        {
            $length = 0x0010; // Number of bytes to follow
        }
        if ($this->_BIFFVersion == 0x0600)
        {
            $length = 0x0014;
        }
        
        $ifnt = $this->_fontIndex; // Index to FONT record
        $ifmt = $this->_numberFormatIndex; // Index to FORMAT record
        if ($this->_BIFFVersion == 0x0500)
        {
            $align = $this->_mapHAlign($this->_style->getAlignment()->getHorizontal()); // Alignment
            $align |= (int) $this->_style->getAlignment()->getWrapText() << 3;
            $align |= $this->_mapVAlign($this->_style->getAlignment()->getVertical()) << 4;
            $align |= $this->_text_justlast << 7;
            $align |= 0 << 8; // rotation
            $align |= $atr_num << 10;
            $align |= $atr_fnt << 11;
            $align |= $atr_alc << 12;
            $align |= $atr_bdr << 13;
            $align |= $atr_pat << 14;
            $align |= $atr_prot << 15;
            
            $icv = $this->_fg_color; // fg and bg pattern colors
            $icv |= $this->_bg_color << 7;
            
            $fill = $this->_mapFillType($this->_style->getFill()->getFillType()); // Fill and border line style
            $fill |= $this->_mapBorderStyle($this->_style->getBorders()->getBottom()->getBorderStyle()) << 6;
            $fill |= $this->_bottom_color << 9;
            
            $border1 = $this->_mapBorderStyle($this->_style->getBorders()->getTop()->getBorderStyle()); // Border line style and color
            $border1 |= $this->_mapBorderStyle($this->_style->getBorders()->getLeft()->getBorderStyle()) << 3;
            $border1 |= $this->_mapBorderStyle($this->_style->getBorders()->getRight()->getBorderStyle()) << 6;
            $border1 |= $this->_top_color << 9;
            
            $border2 = $this->_left_color; // Border color
            $border2 |= $this->_right_color << 7;
            
            $header = pack("vv", $record, $length);
            $data   = pack("vvvvvvvv", $ifnt, $ifmt, $style, $align, $icv, $fill, $border1, $border2);
        }
        elseif ($this->_BIFFVersion == 0x0600)
        {
            $align = $this->_mapHAlign($this->_style->getAlignment()->getHorizontal()); // Alignment
            $align |= (int) $this->_style->getAlignment()->getWrapText() << 3;
            $align |= $this->_mapVAlign($this->_style->getAlignment()->getVertical()) << 4;
            $align |= $this->_text_justlast << 7;
            
            $used_attrib = $atr_num << 2;
            $used_attrib |= $atr_fnt << 3;
            $used_attrib |= $atr_alc << 4;
            $used_attrib |= $atr_bdr << 5;
            $used_attrib |= $atr_pat << 6;
            $used_attrib |= $atr_prot << 7;
            
            $icv = $this->_fg_color; // fg and bg pattern colors
            $icv |= $this->_bg_color << 7;
            
            $border1 = $this->_mapBorderStyle($this->_style->getBorders()->getLeft()->getBorderStyle()); // Border line style and color
            $border1 |= $this->_mapBorderStyle($this->_style->getBorders()->getRight()->getBorderStyle()) << 4;
            $border1 |= $this->_mapBorderStyle($this->_style->getBorders()->getTop()->getBorderStyle()) << 8;
            $border1 |= $this->_mapBorderStyle($this->_style->getBorders()->getBottom()->getBorderStyle()) << 12;
            $border1 |= $this->_left_color << 16;
            $border1 |= $this->_right_color << 23;
            $diag_tl_to_rb = 0; // FIXME: add method
            $diag_tr_to_lb = 0; // FIXME: add method
            $border1 |= $diag_tl_to_rb << 30;
            $border1 |= $diag_tr_to_lb << 31;
            
            $border2 = $this->_top_color; // Border color
            $border2 |= $this->_bottom_color << 7;
            $border2 |= $this->_diag_color << 14;
            $border2 |= $this->_diag << 21;
            $border2 |= $this->_mapFillType($this->_style->getFill()->getFillType()) << 26;
            
            $header = pack("vv", $record, $length);
            
            //BIFF8 options: identation, shrinkToFit and  text direction
            $biff8_options = $this->_style->getAlignment()->getIndent();
            $biff8_options |= (int) $this->_style->getAlignment()->getShrinkToFit() << 4;
            
            $data = pack("vvvC", $ifnt, $ifmt, $style, $align);
            $data .= pack("CCC", $this->_mapTextRotation($this->_style->getAlignment()->getTextRotation()), $biff8_options, $used_attrib);
            $data .= pack("VVv", $border1, $border2, $icv);
        }
        
        return ($header . $data);
    }
    
    /**
     * Set BIFF version
     *
     * @param int $BIFFVersion
     */
    public function setBIFFVersion($BIFFVersion)
    {
        $this->_BIFFVersion = $BIFFVersion;
    }
    
    /**
     * Is this a style XF ?
     *
     * @param boolean $value
     */
    public function setIsStyleXf($value)
    {
        $this->_isStyleXf = $value;
    }
    
    /**
     * Sets the cell's bottom border color
     *
     * @access public
     * @param int $colorIndex Color index
     */
    function setBottomColor($colorIndex)
    {
        $this->_bottom_color = $colorIndex;
    }
    
    /**
     * Sets the cell's top border color
     *
     * @access public
     * @param int $colorIndex Color index
     */
    function setTopColor($colorIndex)
    {
        $this->_top_color = $colorIndex;
    }
    
    /**
     * Sets the cell's left border color
     *
     * @access public
     * @param int $colorIndex Color index
     */
    function setLeftColor($colorIndex)
    {
        $this->_left_color = $colorIndex;
    }
    
    /**
     * Sets the cell's right border color
     *
     * @access public
     * @param int $colorIndex Color index
     */
    function setRightColor($colorIndex)
    {
        $this->_right_color = $colorIndex;
    }
    
    
    /**
     * Sets the cell's foreground color
     *
     * @access public
     * @param int $colorIndex Color index
     */
    function setFgColor($colorIndex)
    {
        $this->_fg_color = $colorIndex;
    }
    
    /**
     * Sets the cell's background color
     *
     * @access public
     * @param int $colorIndex Color index
     */
    function setBgColor($colorIndex)
    {
        $this->_bg_color = $colorIndex;
    }
    
    /**
     * Sets the index to the number format record
     * It can be date, time, currency, etc...
     *
     * @access public
     * @param integer $numberFormatIndex Index to format record
     */
    function setNumberFormatIndex($numberFormatIndex)
    {
        $this->_numberFormatIndex = $numberFormatIndex;
    }
    
    /**
     * Set the font index.
     *
     * @param int $value Font index, note that value 4 does not exist
     */
    public function setFontIndex($value)
    {
        $this->_fontIndex = $value;
    }
    
    /**
     * Map border style
     */
    private function _mapBorderStyle($borderStyle)
    {
        switch ($borderStyle)
        {
            case ExcelStyleBorder::BORDER_NONE:
                return 0x00;
            case ExcelStyleBorder::BORDER_THIN;
                return 0x01;
            case ExcelStyleBorder::BORDER_MEDIUM;
                return 0x02;
            case ExcelStyleBorder::BORDER_DASHED;
                return 0x03;
            case ExcelStyleBorder::BORDER_DOTTED;
                return 0x04;
            case ExcelStyleBorder::BORDER_THICK;
                return 0x05;
            case ExcelStyleBorder::BORDER_DOUBLE;
                return 0x06;
            case ExcelStyleBorder::BORDER_HAIR;
                return 0x07;
            case ExcelStyleBorder::BORDER_MEDIUMDASHED;
                return 0x08;
            case ExcelStyleBorder::BORDER_DASHDOT;
                return 0x09;
            case ExcelStyleBorder::BORDER_MEDIUMDASHDOT;
                return 0x0A;
            case ExcelStyleBorder::BORDER_DASHDOTDOT;
                return 0x0B;
            case ExcelStyleBorder::BORDER_MEDIUMDASHDOTDOT;
                return 0x0C;
            case ExcelStyleBorder::BORDER_SLANTDASHDOT;
                return 0x0D;
            default:
                return 0x00;
        }
    }
    
    /**
     * Map fill type
     */
    private function _mapFillType($fillType)
    {
        switch ($fillType)
        {
            case ExcelStyleFill::FILL_NONE:
                return 0x00;
            case ExcelStyleFill::FILL_SOLID:
                return 0x01;
            case ExcelStyleFill::FILL_PATTERN_MEDIUMGRAY:
                return 0x02;
            case ExcelStyleFill::FILL_PATTERN_DARKGRAY:
                return 0x03;
            case ExcelStyleFill::FILL_PATTERN_LIGHTGRAY:
                return 0x04;
            case ExcelStyleFill::FILL_PATTERN_DARKHORIZONTAL:
                return 0x05;
            case ExcelStyleFill::FILL_PATTERN_DARKVERTICAL:
                return 0x06;
            case ExcelStyleFill::FILL_PATTERN_DARKDOWN:
                return 0x07;
            case ExcelStyleFill::FILL_PATTERN_DARKUP:
                return 0x08;
            case ExcelStyleFill::FILL_PATTERN_DARKGRID:
                return 0x09;
            case ExcelStyleFill::FILL_PATTERN_DARKTRELLIS:
                return 0x0A;
            case ExcelStyleFill::FILL_PATTERN_LIGHTHORIZONTAL:
                return 0x0B;
            case ExcelStyleFill::FILL_PATTERN_LIGHTVERTICAL:
                return 0x0C;
            case ExcelStyleFill::FILL_PATTERN_LIGHTDOWN:
                return 0x0D;
            case ExcelStyleFill::FILL_PATTERN_LIGHTUP:
                return 0x0E;
            case ExcelStyleFill::FILL_PATTERN_LIGHTGRID:
                return 0x0F;
            case ExcelStyleFill::FILL_PATTERN_LIGHTTRELLIS:
                return 0x10;
            case ExcelStyleFill::FILL_PATTERN_GRAY125:
                return 0x11;
            case ExcelStyleFill::FILL_PATTERN_GRAY0625:
                return 0x12;
            case ExcelStyleFill::FILL_GRADIENT_LINEAR: // does not exist in BIFF8
            case ExcelStyleFill::FILL_GRADIENT_PATH: // does not exist in BIFF8
            default:
                return 0x00;
        }
    }
    
    /**
     * Map to BIFF2-BIFF8 codes for horizontal alignment
     *
     * @param string $hAlign
     * @return int
     */
    private function _mapHAlign($hAlign)
    {
        switch ($hAlign)
        {
            case ExcelStyleAlignment::HORIZONTAL_GENERAL:
                return 0;
            case ExcelStyleAlignment::HORIZONTAL_LEFT:
                return 1;
            case ExcelStyleAlignment::HORIZONTAL_CENTER:
                return 2;
            case ExcelStyleAlignment::HORIZONTAL_RIGHT:
                return 3;
            case ExcelStyleAlignment::HORIZONTAL_JUSTIFY:
                return 5;
            default:
                return 0;
        }
    }
    
    /**
     * Map to BIFF2-BIFF8 codes for vertical alignment
     *
     * @param string $vAlign
     * @return int
     */
    private function _mapVAlign($vAlign)
    {
        switch ($vAlign)
        {
            case ExcelStyleAlignment::VERTICAL_TOP:
                return 0;
            case ExcelStyleAlignment::VERTICAL_CENTER:
                return 1;
            case ExcelStyleAlignment::VERTICAL_BOTTOM:
                return 2;
            case ExcelStyleAlignment::VERTICAL_JUSTIFY:
                return 3;
            default:
                return 2;
        }
    }
    
    /**
     * Map to BIFF8 codes for text rotation angle
     *
     * @param int $textRotation
     * @return int
     */
    private function _mapTextRotation($textRotation)
    {
        if ($textRotation >= 0)
            return $textRotation;
        if ($textRotation == -165)
            return 255;
        if ($textRotation < 0)
            return 90 - $textRotation;
    }
    
    /**
     * Map locked
     *
     * @param string
     * @return int
     */
    private function _mapLocked($locked)
    {
        switch ($locked)
        {
            case ExcelStyleProtection::PROTECTION_INHERIT:
                return 1;
            case ExcelStyleProtection::PROTECTION_PROTECTED:
                return 1;
            case ExcelStyleProtection::PROTECTION_UNPROTECTED:
                return 0;
            default:
                return 1;
        }
    }
    
    /**
     * Map hidden
     *
     * @param string
     * @return int
     */
    private function _mapHidden($hidden)
    {
        switch ($hidden)
        {
            case ExcelStyleProtection::PROTECTION_INHERIT:
                return 0;
            case ExcelStyleProtection::PROTECTION_PROTECTED:
                return 1;
            case ExcelStyleProtection::PROTECTION_UNPROTECTED:
                return 0;
            default:
                return 0;
        }
    }
}
?>