<?php
require_once "Drawing.php";

/**
 * ExcelWorksheet_HeaderFooter
 *
 * <code>
 * Header/Footer Formatting Syntax taken from Office Open XML Part 4 - Markup Language Reference, page 1970:
 *
 * There are a number of formatting codes that can be written inline with the actual header / footer text, which
 * affect the formatting in the header or footer.
 * 
 * Example: This example shows the text "Center Bold Header" on the first line (center section), and the date on
 * the second line (center section).
 * 		&CCenter &"-,Bold"Bold&"-,Regular"Header_x000A_&D
 * 
 * General Rules:
 * There is no required order in which these codes must appear.
 * 
 * The first occurrence of the following codes turns the formatting ON, the second occurrence turns it OFF again:
 * - strikethrough
 * - superscript
 * - subscript
 * Superscript and subscript cannot both be ON at same time. Whichever comes first wins and the other is ignored,
 * while the first is ON.
 * &L - code for "left section" (there are three header / footer locations, "left", "center", and "right"). When
 * two or more occurrences of this section marker exist, the contents from all markers are concatenated, in the
 * order of appearance, and placed into the left section.
 * &P - code for "current page #"
 * &N - code for "total pages"
 * &font size - code for "text font size", where font size is a font size in points.
 * &K - code for "text font color"
 * RGB Color is specified as RRGGBB
 * Theme Color is specifed as TTSNN where TT is the theme color Id, S is either "+" or "-" of the tint/shade
 * value, NN is the tint/shade value.
 * &S - code for "text strikethrough" on / off
 * &X - code for "text super script" on / off
 * &Y - code for "text subscript" on / off
 * &C - code for "center section". When two or more occurrences of this section marker exist, the contents
 * from all markers are concatenated, in the order of appearance, and placed into the center section.
 * 
 * &D - code for "date"
 * &T - code for "time"
 * &G - code for "picture as background"
 * &U - code for "text single underline"
 * &E - code for "double underline"
 * &R - code for "right section". When two or more occurrences of this section marker exist, the contents
 * from all markers are concatenated, in the order of appearance, and placed into the right section.
 * &Z - code for "this workbook's file path"
 * &F - code for "this workbook's file name"
 * &A - code for "sheet tab name"
 * &+ - code for add to page #.
 * &- - code for subtract from page #.
 * &"font name,font type" - code for "text font name" and "text font type", where font name and font type
 * are strings specifying the name and type of the font, separated by a comma. When a hyphen appears in font
 * name, it means "none specified". Both of font name and font type can be localized values.
 * &"-,Bold" - code for "bold font style"
 * &B - also means "bold font style".
 * &"-,Regular" - code for "regular font style"
 * &"-,Italic" - code for "italic font style"
 * &I - also means "italic font style"
 * &"-,Bold Italic" code for "bold italic font style"
 * &O - code for "outline style"
 * &H - code for "shadow style"
 * </code>
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_HeaderFooter extends ObjectBase
{
    /* Header/footer image location */
    const IMAGE_HEADER_LEFT = 'LH';
    const IMAGE_HEADER_CENTER = 'CH';
    const IMAGE_HEADER_RIGHT = 'RH';
    const IMAGE_FOOTER_LEFT = 'LF';
    const IMAGE_FOOTER_CENTER = 'CF';
    const IMAGE_FOOTER_RIGHT = 'RF';
    
    private $_oddHeader;
    private $_oddFooter;
    private $_evenHeader;
    private $_evenFooter;
    private $_firstHeader;
    private $_firstFooter;
    
    /**
     * Different header for Odd/Even, defaults to false
     *
     * @var boolean
     */
    private $_differentOddEven;
    
    /**
     * Different header for first page, defaults to false
     *
     * @var boolean
     */
    private $_differentFirst;
    
    /**
     * Scale with document, defaults to true
     *
     * @var boolean
     */
    private $_scaleWithDocument;
    
    /**
     * Align with margins, defaults to true
     *
     * @var boolean
     */
    private $_alignWithMargins;
    
    /**
     * Header/footer images
     *
     * @var ExcelWorksheet_HeaderFooterDrawing[]
     */
    private $_headerFooterImages = array();
    
    /**
     * Create a new ExcelWorksheet_HeaderFooter
     */
    public function __construct()
    {
        // Initialise values
        $this->_oddHeader          = '';
        $this->_oddFooter          = '';
        $this->_evenHeader         = '';
        $this->_evenFooter         = '';
        $this->_firstHeader        = '';
        $this->_firstFooter        = '';
        $this->_differentOddEven   = false;
        $this->_differentFirst     = false;
        $this->_scaleWithDocument  = true;
        $this->_alignWithMargins   = true;
        $this->_headerFooterImages = array();
    }
    
    /**
     * Get OddHeader
     *
     * @return string
     */
    public function getOddHeader()
    {
        return $this->_oddHeader;
    }
    
    /**
     * Set OddHeader
     *
     * @param string $pValue
     */
    public function setOddHeader($pValue)
    {
        $this->_oddHeader = $pValue;
    }
    
    /**
     * Get OddFooter
     *
     * @return string
     */
    public function getOddFooter()
    {
        return $this->_oddFooter;
    }
    
    /**
     * Set OddFooter
     *
     * @param string $pValue
     */
    public function setOddFooter($pValue)
    {
        $this->_oddFooter = $pValue;
    }
    
    /**
     * Get EvenHeader
     *
     * @return string
     */
    public function getEvenHeader()
    {
        return $this->_evenHeader;
    }
    
    /**
     * Set EvenHeader
     *
     * @param string $pValue
     */
    public function setEvenHeader($pValue)
    {
        $this->_evenHeader = $pValue;
    }
    
    /**
     * Get EvenFooter
     *
     * @return string
     */
    public function getEvenFooter()
    {
        return $this->_evenFooter;
    }
    
    /**
     * Set EvenFooter
     *
     * @param string $pValue
     */
    public function setEvenFooter($pValue)
    {
        $this->_evenFooter = $pValue;
    }
    
    /**
     * Get FirstHeader
     *
     * @return string
     */
    public function getFirstHeader()
    {
        return $this->_firstHeader;
    }
    
    /**
     * Set FirstHeader
     *
     * @param string $pValue
     */
    public function setFirstHeader($pValue)
    {
        $this->_firstHeader = $pValue;
    }
    
    /**
     * Get FirstFooter
     *
     * @return string
     */
    public function getFirstFooter()
    {
        return $this->_firstFooter;
    }
    
    /**
     * Set FirstFooter
     *
     * @param string $pValue
     */
    public function setFirstFooter($pValue)
    {
        $this->_firstFooter = $pValue;
    }
    
    /**
     * Get DifferentOddEven
     *
     * @return boolean
     */
    public function getDifferentOddEven()
    {
        return $this->_differentOddEven;
    }
    
    /**
     * Set DifferentOddEven
     *
     * @param boolean $pValue
     */
    public function setDifferentOddEven($pValue = false)
    {
        $this->_differentOddEven = $pValue;
    }
    
    /**
     * Get DifferentFirst
     *
     * @return boolean
     */
    public function getDifferentFirst()
    {
        return $this->_differentFirst;
    }
    
    /**
     * Set DifferentFirst
     *
     * @param boolean $pValue
     */
    public function setDifferentFirst($pValue = false)
    {
        $this->_differentFirst = $pValue;
    }
    
    /**
     * Get ScaleWithDocument
     *
     * @return boolean
     */
    public function getScaleWithDocument()
    {
        return $this->_scaleWithDocument;
    }
    
    /**
     * Set ScaleWithDocument
     *
     * @param boolean $pValue
     */
    public function setScaleWithDocument($pValue = true)
    {
        $this->_scaleWithDocument = $pValue;
    }
    
    /**
     * Get AlignWithMargins
     *
     * @return boolean
     */
    public function getAlignWithMargins()
    {
        return $this->_alignWithMargins;
    }
    
    /**
     * Set AlignWithMargins
     *
     * @param boolean $pValue
     */
    public function setAlignWithMargins($pValue = true)
    {
        $this->_alignWithMargins = $pValue;
    }
    
    /**
     * Add header/footer image
     *
     * @param ExcelWorksheet_HeaderFooterDrawing $image
     * @param string $location
     * @throws Exception
     */
    public function addImage(ExcelWorksheet_HeaderFooterDrawing $image = null, $location = self::IMAGE_HEADER_LEFT)
    {
        $this->_headerFooterImages[$location] = $image;
    }
    
    /**
     * Remove header/footer image
     *
     * @param string $location
     * @throws Exception
     */
    public function removeImage($location = self::IMAGE_HEADER_LEFT)
    {
        if (isset($this->_headerFooterImages[$location]))
        {
            unset($this->_headerFooterImages[$location]);
        }
    }
    
    /**
     * Set header/footer images
     *
     * @param ExcelWorksheet_HeaderFooterDrawing[] $images
     * @throws Exception
     */
    public function setImages($images)
    {
        if (!is_array($images))
        {
            throw new Exception('Invalid parameter!');
        }
        
        $this->_headerFooterImages = $images;
    }
    
    /**
     * Get header/footer images
     *
     * @return HPExcel_Worksheet_HeaderFooterDrawing[]
     */
    public function getImages()
    {
        // Sort array
        $images = array();
        if (isset($this->_headerFooterImages[self::IMAGE_HEADER_LEFT]))
            $images[self::IMAGE_HEADER_LEFT] = $this->_headerFooterImages[self::IMAGE_HEADER_LEFT];
        if (isset($this->_headerFooterImages[self::IMAGE_HEADER_CENTER]))
            $images[self::IMAGE_HEADER_CENTER] = $this->_headerFooterImages[self::IMAGE_HEADER_CENTER];
        if (isset($this->_headerFooterImages[self::IMAGE_HEADER_RIGHT]))
            $images[self::IMAGE_HEADER_RIGHT] = $this->_headerFooterImages[self::IMAGE_HEADER_RIGHT];
        if (isset($this->_headerFooterImages[self::IMAGE_FOOTER_LEFT]))
            $images[self::IMAGE_FOOTER_LEFT] = $this->_headerFooterImages[self::IMAGE_FOOTER_LEFT];
        if (isset($this->_headerFooterImages[self::IMAGE_FOOTER_CENTER]))
            $images[self::IMAGE_FOOTER_CENTER] = $this->_headerFooterImages[self::IMAGE_FOOTER_CENTER];
        if (isset($this->_headerFooterImages[self::IMAGE_FOOTER_RIGHT]))
            $images[self::IMAGE_FOOTER_RIGHT] = $this->_headerFooterImages[self::IMAGE_FOOTER_RIGHT];
        $this->_headerFooterImages = $images;
        
        return $this->_headerFooterImages;
    }
}

/**
 * ExcelWorksheet_HeaderFooterDrawing
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_HeaderFooterDrawing extends ExcelWorksheet_Drawing
{
    private $_path;
    protected $_name;
    protected $_offsetX;
    protected $_offsetY;
    protected $_width;
    protected $_height;
    
    /**
     * Proportional resize
     *
     * @var boolean
     */
    protected $_resizeProportional;
    
    /**
     * Create a new ExcelWorksheet_HeaderFooterDrawing
     */
    public function __construct()
    {
        // Initialise values
        $this->_path               = '';
        $this->_name               = '';
        $this->_offsetX            = 0;
        $this->_offsetY            = 0;
        $this->_width              = 0;
        $this->_height             = 0;
        $this->_resizeProportional = true;
    }
    
    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Set Name
     *
     * @param string $pValue
     */
    public function setName($pValue = '')
    {
        $this->_name = $pValue;
    }
    
    /**
     * Get OffsetX
     *
     * @return int
     */
    public function getOffsetX()
    {
        return $this->_offsetX;
    }
    
    /**
     * Set OffsetX
     *
     * @param int $pValue
     */
    public function setOffsetX($pValue = 0)
    {
        $this->_offsetX = $pValue;
    }
    
    /**
     * Get OffsetY
     *
     * @return int
     */
    public function getOffsetY()
    {
        return $this->_offsetY;
    }
    
    /**
     * Set OffsetY
     *
     * @param int $pValue
     */
    public function setOffsetY($pValue = 0)
    {
        $this->_offsetY = $pValue;
    }
    
    /**
     * Get Width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }
    
    /**
     * Set Width
     *
     * @param int $pValue
     */
    public function setWidth($pValue = 0)
    {
        // Resize proportional?
        if ($this->_resizeProportional && $pValue != 0)
        {
            $ratio         = $this->_width / $this->_height;
            $this->_height = round($ratio * $pValue);
        }
        
        // Set width
        $this->_width = $pValue;
    }
    
    /**
     * Get Height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }
    
    /**
     * Set Height
     *
     * @param int $pValue
     */
    public function setHeight($pValue = 0)
    {
        // Resize proportional?
        if ($this->_resizeProportional && $pValue != 0)
        {
            $ratio        = $this->_width / $this->_height;
            $this->_width = round($ratio * $pValue);
        }
        
        // Set height
        $this->_height = $pValue;
    }
    
    /**
     * Set width and height with proportional resize
     * @author Vincent@luo MSN:kele_100@hotmail.com
     * @param int $width
     * @param int $height
     * @example $objDrawing->setResizeProportional(true);
     * @example $objDrawing->setWidthAndHeight(160,120);
     */
    public function setWidthAndHeight($width = 0, $height = 0)
    {
        $xratio = $width / $this->_width;
        $yratio = $height / $this->_height;
        if ($this->_resizeProportional && !($width == 0 || $height == 0))
        {
            if (($xratio * $this->_height) < $height)
            {
                $this->_height = ceil($xratio * $this->_height);
                $this->_width  = $width;
            }
            else
            {
                $this->_width  = ceil($yratio * $this->_width);
                $this->_height = $height;
            }
        }
    }
    
    /**
     * Get ResizeProportional
     *
     * @return boolean
     */
    public function getResizeProportional()
    {
        return $this->_resizeProportional;
    }
    
    /**
     * Set ResizeProportional
     *
     * @param boolean $pValue
     */
    public function setResizeProportional($pValue = true)
    {
        $this->_resizeProportional = $pValue;
    }
    
    /**
     * Get Filename
     *
     * @return string
     */
    public function getFilename()
    {
        return basename($this->_path);
    }
    
    /**
     * Get Extension
     *
     * @return string
     */
    public function getExtension()
    {
        return end(explode(".", basename($this->_path)));
    }
    
    /**
     * Get Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * Set Path
     *
     * @param 	string 		$pValue			File path
     * @param 	boolean		$pVerifyFile	Verify file
     * @throws 	Exception
     */
    public function setPath($pValue = '', $pVerifyFile = true)
    {
        if ($pVerifyFile)
        {
            if (file_exists($pValue))
            {
                $this->_path = $pValue;
                
                if ($this->_width == 0 && $this->_height == 0)
                {
                    // Get width/height
                    list($this->_width, $this->_height) = getimagesize($pValue);
                }
            }
            else
            {
                throw new Exception("File $pValue not found!");
            }
        }
        else
        {
            $this->_path = $pValue;
        }
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_path . $this->_name . $this->_offsetX . $this->_offsetY . $this->_width . $this->_height . __CLASS__);
    }
}
?>