<?php
/**
 * ExcelWorksheet_PageMargins
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_PageMargins extends ObjectBase
{
    private $_left;
    private $_right;
    private $_top;
    private $_bottom;
    private $_header;
    private $_footer;
    
    /**
     * Create a new ExcelWorksheet_PageMargins
     * 
     */
    public function __construct()
    {
        // Initialise values
        $this->_left   = 0.7;
        $this->_right  = 0.7;
        $this->_top    = 0.75;
        $this->_bottom = 0.75;
        $this->_header = 0.3;
        $this->_footer = 0.3;
    }
    
    /**
     * Get Left
     *
     * @return double
     */
    public function getLeft()
    {
        return $this->_left;
    }
    
    /**
     * Set Left
     *
     * @param double $pValue
     */
    public function setLeft($pValue)
    {
        $this->_left = $pValue;
    }
    
    /**
     * Get Right
     *
     * @return double
     */
    public function getRight()
    {
        return $this->_right;
    }
    
    /**
     * Set Right
     *
     * @param double $pValue
     */
    public function setRight($pValue)
    {
        $this->_right = $pValue;
    }
    
    /**
     * Get Top
     *
     * @return double
     */
    public function getTop()
    {
        return $this->_top;
    }
    
    /**
     * Set Top
     *
     * @param double $pValue
     */
    public function setTop($pValue)
    {
        $this->_top = $pValue;
    }
    
    /**
     * Get Bottom
     *
     * @return double
     */
    public function getBottom()
    {
        return $this->_bottom;
    }
    
    /**
     * Set Bottom
     *
     * @param double $pValue
     */
    public function setBottom($pValue)
    {
        $this->_bottom = $pValue;
    }
    
    /**
     * Get Header
     *
     * @return double
     */
    public function getHeader()
    {
        return $this->_header;
    }
    
    /**
     * Set Header
     *
     * @param double $pValue
     */
    public function setHeader($pValue)
    {
        $this->_header = $pValue;
    }
    
    /**
     * Get Footer
     *
     * @return double
     */
    public function getFooter()
    {
        return $this->_footer;
    }
    
    /**
     * Set Footer
     *
     * @param double $pValue
     */
    public function setFooter($pValue)
    {
        $this->_footer = $pValue;
    }
}

/**
 * ExcelWorksheet_PageSetup
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_PageSetup extends ObjectBase
{
    /* Paper size */
	const PAPERSIZE_LETTER							= 1;
	const PAPERSIZE_LETTER_SMALL					= 2;
	const PAPERSIZE_TABLOID							= 3;
	const PAPERSIZE_LEDGER							= 4;
	const PAPERSIZE_LEGAL							= 5;
	const PAPERSIZE_STATEMENT						= 6;
	const PAPERSIZE_EXECUTIVE						= 7;
	const PAPERSIZE_A3								= 8;
	const PAPERSIZE_A4								= 9;
	const PAPERSIZE_A4_SMALL						= 10;
	const PAPERSIZE_A5								= 11;
	const PAPERSIZE_B4								= 12;
	const PAPERSIZE_B5								= 13;
	const PAPERSIZE_FOLIO							= 14;
	const PAPERSIZE_QUARTO							= 15;
	const PAPERSIZE_STANDARD_1						= 16;
	const PAPERSIZE_STANDARD_2						= 17;
	const PAPERSIZE_NOTE							= 18;
	const PAPERSIZE_NO9_ENVELOPE					= 19;
	const PAPERSIZE_NO10_ENVELOPE					= 20;
	const PAPERSIZE_NO11_ENVELOPE					= 21;
	const PAPERSIZE_NO12_ENVELOPE					= 22;
	const PAPERSIZE_NO14_ENVELOPE					= 23;
	const PAPERSIZE_C								= 24;
	const PAPERSIZE_D								= 25;
	const PAPERSIZE_E								= 26;
	const PAPERSIZE_DL_ENVELOPE						= 27;
	const PAPERSIZE_C5_ENVELOPE						= 28;
	const PAPERSIZE_C3_ENVELOPE						= 29;
	const PAPERSIZE_C4_ENVELOPE						= 30;
	const PAPERSIZE_C6_ENVELOPE						= 31;
	const PAPERSIZE_C65_ENVELOPE					= 32;
	const PAPERSIZE_B4_ENVELOPE						= 33;
	const PAPERSIZE_B5_ENVELOPE						= 34;
	const PAPERSIZE_B6_ENVELOPE						= 35;
	const PAPERSIZE_ITALY_ENVELOPE					= 36;
	const PAPERSIZE_MONARCH_ENVELOPE				= 37;
	const PAPERSIZE_6_3_4_ENVELOPE					= 38;
	const PAPERSIZE_US_STANDARD_FANFOLD				= 39;
	const PAPERSIZE_GERMAN_STANDARD_FANFOLD			= 40;
	const PAPERSIZE_GERMAN_LEGAL_FANFOLD			= 41;
	const PAPERSIZE_ISO_B4							= 42;
	const PAPERSIZE_JAPANESE_DOUBLE_POSTCARD		= 43;
	const PAPERSIZE_STANDARD_PAPER_1				= 44;
	const PAPERSIZE_STANDARD_PAPER_2				= 45;
	const PAPERSIZE_STANDARD_PAPER_3				= 46;
	const PAPERSIZE_INVITE_ENVELOPE					= 47;
	const PAPERSIZE_LETTER_EXTRA_PAPER				= 48;
	const PAPERSIZE_LEGAL_EXTRA_PAPER				= 49;
	const PAPERSIZE_TABLOID_EXTRA_PAPER				= 50;
	const PAPERSIZE_A4_EXTRA_PAPER					= 51;
	const PAPERSIZE_LETTER_TRANSVERSE_PAPER			= 52;
	const PAPERSIZE_A4_TRANSVERSE_PAPER				= 53;
	const PAPERSIZE_LETTER_EXTRA_TRANSVERSE_PAPER	= 54;
	const PAPERSIZE_SUPERA_SUPERA_A4_PAPER			= 55;
	const PAPERSIZE_SUPERB_SUPERB_A3_PAPER			= 56;
	const PAPERSIZE_LETTER_PLUS_PAPER				= 57;
	const PAPERSIZE_A4_PLUS_PAPER					= 58;
	const PAPERSIZE_A5_TRANSVERSE_PAPER				= 59;
	const PAPERSIZE_JIS_B5_TRANSVERSE_PAPER			= 60;
	const PAPERSIZE_A3_EXTRA_PAPER					= 61;
	const PAPERSIZE_A5_EXTRA_PAPER					= 62;
	const PAPERSIZE_ISO_B5_EXTRA_PAPER				= 63;
	const PAPERSIZE_A2_PAPER						= 64;
	const PAPERSIZE_A3_TRANSVERSE_PAPER				= 65;
	const PAPERSIZE_A3_EXTRA_TRANSVERSE_PAPER		= 66;

	/* Page orientation */
	const ORIENTATION_DEFAULT	= 'default';
	const ORIENTATION_LANDSCAPE	= 'landscape';
	const ORIENTATION_PORTRAIT	= 'portrait';

	/**
	 * Paper size
	 *
	 * @var int
	 */
	private $_paperSize;

	/**
	 * Orientation
	 *
	 * @var string
	 */
	private $_orientation;

	/**
	 * Scale (Print Scale)
	 *
	 * Print scaling. Valid values range from 10 to 400
	 * This setting is overridden when fitToWidth and/or fitToHeight are in use
	 *
	 * @var int?
	 */
	private $_scale;

	/**
	  * Fit To Page
	  * Whether scale or fitToWith / fitToHeight applies
	  *
	  * @var boolean
	  */
	private $_fitToPage;

	/**
	  * Fit To Height
	  * Number of vertical pages to fit on
	  *
	  * @var int?
	  */
	private $_fitToHeight;

	/**
	  * Fit To Width
	  * Number of horizontal pages to fit on
	  *
	  * @var int?
	  */
	private $_fitToWidth;

	/**
	 * Columns to repeat at left
	 *
	 * @var array Containing start column and end column, empty array if option unset
	 */
	private $_columnsToRepeatAtLeft = array('', '');

	/**
	 * Rows to repeat at top
	 *
	 * @var array Containing start row number and end row number, empty array if option unset
	 */
	private $_rowsToRepeatAtTop = array(0, 0);

	/**
	 * Center page horizontally
	 *
	 * @var boolean
	 */
	private $_horizontalCentered = false;

	/**
	 * Center page vertically
	 *
	 * @var boolean
	 */
	private $_verticalCentered = false;

	/**
	 * Print area
	 *
	 * @var string
	 */
	private $_printArea = null;
	
	/**
	 * First page number
	 *
	 * @var int
	 */
	private $_firstPageNumber = null;

    /**
     * Create a new ExcelWorksheet_PageSetup
     */
    public function __construct()
    {
    	// Initialise values
    	$this->_paperSize 				= ExcelWorksheet_PageSetup::PAPERSIZE_LETTER;
    	$this->_orientation				= ExcelWorksheet_PageSetup::ORIENTATION_DEFAULT;
    	$this->_scale					= 100;
    	$this->_fitToPage				= false;
    	$this->_fitToHeight				= 1;
    	$this->_fitToWidth				= 1;
    	$this->_columnsToRepeatAtLeft 	= array('', '');
    	$this->_rowsToRepeatAtTop		= array(0, 0);
    	$this->_horizontalCentered		= false;
    	$this->_verticalCentered		= false;
    	$this->_printArea				= null;
    	$this->_firstPageNumber         = null;
    }

    /**
     * Get Paper Size
     *
     * @return int
     */
    public function getPaperSize() {
    	return $this->_paperSize;
    }

    /**
     * Set Paper Size
     *
     * @param int $pValue
     * @return ExcelWorksheet_PageSetup
     */
    public function setPaperSize($pValue = ExcelWorksheet_PageSetup::PAPERSIZE_LETTER) {
    	$this->_paperSize = $pValue;
    	return $this;
    }

    /**
     * Get Orientation
     *
     * @return string
     */
    public function getOrientation() {
    	return $this->_orientation;
    }

    /**
     * Set Orientation
     *
     * @param string $pValue
     * @return ExcelWorksheet_PageSetup
     */
    public function setOrientation($pValue = ExcelWorksheet_PageSetup::ORIENTATION_DEFAULT) {
    	$this->_orientation = $pValue;
    	return $this;
    }

	/**
	 * Get Scale
	 *
	 * @return int?
	 */
	public function getScale() {
		return $this->_scale;
	}

	/**
	 * Set Scale
	 *
	 * Print scaling. Valid values range from 10 to 400
	 * This setting is overridden when fitToWidth and/or fitToHeight are in use
	 *
	 * @param 	int? 	$pValue
	 * @param boolean $pUpdate Update fitToPage so scaling applies rather than fitToHeight / fitToWidth
	 * @throws 	Exception
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setScale($pValue = 100, $pUpdate = true) {
		// Microsoft Office Excel 2007 only allows setting a scale between 10 and 400 via the user interface,
		// but it is apparently still able to handle any scale >= 0, where 0 results in 100
		if (($pValue >= 0) || is_null($pValue)) {
			$this->_scale = $pValue;
			if ($pUpdate) {
				$this->_fitToPage = false;
			}
		} else {
			throw new Exception("Scale must not be negative");
		}
		return $this;
	}

	/**
	 * Get Fit To Page
	 *
	 * @return boolean
	 */
	public function getFitToPage() {
		return $this->_fitToPage;
	}

	/**
	 * Set Fit To Page
	 *
	 * @param boolean $pValue
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setFitToPage($pValue = true) {
		$this->_fitToPage = $pValue;
		return $this;
	}

	/**
	 * Get Fit To Height
	 *
	 * @return int?
	 */
	public function getFitToHeight() {
		return $this->_fitToHeight;
	}

	/**
	 * Set Fit To Height
	 *
	 * @param int? $pValue
	 * @param boolean $pUpdate Update fitToPage so it applies rather than scaling
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setFitToHeight($pValue = 1, $pUpdate = true) {
		$this->_fitToHeight = $pValue;
		if ($pUpdate) {
			$this->_fitToPage = true;
		}
		return $this;
	}

	/**
	 * Get Fit To Width
	 *
	 * @return int?
	 */
	public function getFitToWidth() {
		return $this->_fitToWidth;
	}

	/**
	 * Set Fit To Width
	 *
	 * @param int? $pValue
	 * @param boolean $pUpdate Update fitToPage so it applies rather than scaling
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setFitToWidth($pValue = 1, $pUpdate = true) {
		$this->_fitToWidth = $pValue;
		if ($pUpdate) {
			$this->_fitToPage = true;
		}
		return $this;
	}

	/**
	 * Is Columns to repeat at left set?
	 *
	 * @return boolean
	 */
	public function isColumnsToRepeatAtLeftSet() {
		if (is_array($this->_columnsToRepeatAtLeft)) {
			if ($this->_columnsToRepeatAtLeft[0] != '' && $this->_columnsToRepeatAtLeft[1] != '') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Columns to repeat at left
	 *
	 * @return array Containing start column and end column, empty array if option unset
	 */
	public function getColumnsToRepeatAtLeft() {
		return $this->_columnsToRepeatAtLeft;
	}

	/**
	 * Set Columns to repeat at left
	 *
	 * @param array $pValue Containing start column and end column, empty array if option unset
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setColumnsToRepeatAtLeft($pValue = null) {
		if (is_array($pValue)) {
			$this->_columnsToRepeatAtLeft = $pValue;
		}
		return $this;
	}

	/**
	 * Set Columns to repeat at left by start and end
	 *
	 * @param string $pStart
	 * @param string $pEnd
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setColumnsToRepeatAtLeftByStartAndEnd($pStart = 'A', $pEnd = 'A') {
		$this->_columnsToRepeatAtLeft = array($pStart, $pEnd);
		return $this;
	}

	/**
	 * Is Rows to repeat at top set?
	 *
	 * @return boolean
	 */
	public function isRowsToRepeatAtTopSet() {
		if (is_array($this->_rowsToRepeatAtTop)) {
			if ($this->_rowsToRepeatAtTop[0] != 0 && $this->_rowsToRepeatAtTop[1] != 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Rows to repeat at top
	 *
	 * @return array Containing start column and end column, empty array if option unset
	 */
	public function getRowsToRepeatAtTop() {
		return $this->_rowsToRepeatAtTop;
	}

	/**
	 * Set Rows to repeat at top
	 *
	 * @param array $pValue Containing start column and end column, empty array if option unset
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setRowsToRepeatAtTop($pValue = null) {
		if (is_array($pValue)) {
			$this->_rowsToRepeatAtTop = $pValue;
		}
		return $this;
	}

	/**
	 * Set Rows to repeat at top by start and end
	 *
	 * @param int $pStart
	 * @param int $pEnd
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setRowsToRepeatAtTopByStartAndEnd($pStart = 1, $pEnd = 1) {
		$this->_rowsToRepeatAtTop = array($pStart, $pEnd);
		return $this;
	}

	/**
	 * Get center page horizontally
	 *
	 * @return bool
	 */
	public function getHorizontalCentered() {
		return $this->_horizontalCentered;
	}

	/**
	 * Set center page horizontally
	 *
	 * @param bool $value
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setHorizontalCentered($value = false) {
		$this->_horizontalCentered = $value;
		return $this;
	}

	/**
	 * Get center page vertically
	 *
	 * @return bool
	 */
	public function getVerticalCentered() {
		return $this->_verticalCentered;
	}

	/**
	 * Set center page vertically
	 *
	 * @param bool $value
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setVerticalCentered($value = false) {
		$this->_verticalCentered = $value;
		return $this;
	}

	/**
	 * Get print area
	 *
	 * @return string
	 */
	public function getPrintArea() {
		return $this->_printArea;
	}

	/**
	 * Is print area set?
	 *
	 * @return boolean
	 */
	public function isPrintAreaSet() {
		return !is_null($this->_printArea);
	}

	/**
	 * Set print area
	 *
	 * @param string $value
	 * @throws Exception
	 * @return ExcelWorksheet_PageSetup
	 */
	public function setPrintArea($value) {
    	if (strpos($value,':') === false) {
    		throw new Exception('Cell coordinate must be a range of cells.');
    	} elseif (strpos($value,'$') !== false) {
    		throw new Exception('Cell coordinate must not be absolute.');
    	} else {
			$this->_printArea = strtoupper($value);
    	}
    	return $this;
	}

	/**
	 * Set print area
	 *
	 * @param int $column1		Column 1
	 * @param int $row1			Row 1
	 * @param int $column2		Column 2
	 * @param int $row2			Row 2
	 * @return ExcelWorksheet_PageSetup
	 */
    public function setPrintAreaByColumnAndRow($column1, $row1, $column2, $row2)
    {
    	return $this->setPrintArea(ExcelCell::stringFromColumnIndex($column1) . $row1 . ':' . ExcelCell::stringFromColumnIndex($column2) . $row2);
    }
    
	/**
	 * Get first page number
	 *
	 * @return int
	 */
    public function getFirstPageNumber() {
		return $this->_firstPageNumber;
    }
    
    /**
     * Set first page number
     *
     * @param int $value
     * @return ExcelWorksheet_HeaderFooter
     */
    public function setFirstPageNumber($value = null) {
		$this->_firstPageNumber = $value;
		return $this;
    }
    
    /**
     * Reset first page number
     *
     * @return ExcelWorksheet_HeaderFooter
     */
    public function resetFirstPageNumber() {
		return $this->setFirstPageNumber(null);
    }
}

/**
 * ExcelWorksheet_SheetView
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_SheetView extends ObjectBase
{
    /**
     * ZoomScale
     * 
     * Valid values range from 10 to 400.
     *
     * @var int
     */
    private $_zoomScale;
    
    /**
     * ZoomScaleNormal
     * 
     * Valid values range from 10 to 400.
     *
     * @var int
     */
    private $_zoomScaleNormal;
    
    /**
     * Create a new ExcelWorksheet_SheetView
     */
    public function __construct()
    {
        // Initialise values
        $this->_zoomScale       = 100;
        $this->_zoomScaleNormal = 100;
    }
    
    /**
     * Get ZoomScale
     *
     * @return int
     */
    public function getZoomScale()
    {
        return $this->_zoomScale;
    }
    
    /**
     * Set ZoomScale
     *
     * Valid values range from 10 to 400.
     *
     * @param 	int 	$pValue
     * @throws 	Exception
     */
    public function setZoomScale($pValue = 100)
    {
        if (($pValue >= 10 && $pValue <= 400) || is_null($pValue))
        {
            $this->_zoomScale = $pValue;
        }
        else
        {
            throw new Exception("Valid scale is between 10 and 400.");
        }
    }
    
    /**
     * Get ZoomScaleNormal
     *
     * @return int
     */
    public function getZoomScaleNormal()
    {
        return $this->_zoomScaleNormal;
    }
    
    /**
     * Set ZoomScale
     *
     * Valid values range from 10 to 400.
     *
     * @param 	int 	$pValue
     * @throws 	Exception
     */
    public function setZoomScaleNormal($pValue = 100)
    {
        if (($pValue >= 10 && $pValue <= 400) || is_null($pValue))
        {
            $this->_zoomScaleNormal = $pValue;
        }
        else
        {
            throw new Exception("Valid scale is between 10 and 400.");
        }
    }
}
?>