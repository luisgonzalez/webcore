<?php
require_once 'webcore.excel.cell.php';
require_once 'worksheet/Row.php';
require_once 'worksheet/PageMargins.php';
require_once 'worksheet/HeaderFooter.php';
require_once 'worksheet/Drawing.php';
require_once 'webcore.excel.style.php';
require_once 'webcore.excel.referencehelper.php';
require_once 'webcore.excel.calculation.php';

/**
 * ExcelWorksheet
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet extends ComparableBase
{
    /* Break types */
	const BREAK_NONE	= 0;
	const BREAK_ROW		= 1;
	const BREAK_COLUMN	= 2;
	
	/* Sheet state */
	const SHEETSTATE_VISIBLE 	= 'visible';
	const SHEETSTATE_HIDDEN 	= 'hidden';
	const SHEETSTATE_VERYHIDDEN = 'veryHidden';

	/**
	 * Parent spreadsheet
	 *
	 * @var PHPExcel
	 */
	private $_parent;

	/**
	 * Collection of cells
	 *
	 * @var ExcelCell[]
	 */
	private $_cellCollection = array();

	/**
	 * Collection of row dimensions
	 *
	 * @var ExcelWorksheet_RowDimension[]
	 */
	private $_rowDimensions = array();

	/**
	 * Default row dimension
	 *
	 * @var ExcelWorksheet_RowDimension
	 */
	private $_defaultRowDimension = null;

	/**
	 * Collection of column dimensions
	 *
	 * @var ExcelWorksheet_ColumnDimension[]
	 */
	private $_columnDimensions = array();

	/**
	 * Default column dimension
	 *
	 * @var ExcelWorksheet_ColumnDimension
	 */
	private $_defaultColumnDimension = null;

	/**
	 * Collection of drawings
	 *
	 * @var ExcelWorksheet_BaseDrawing[]
	 */
	private $_drawingCollection = null;

	/**
	 * Worksheet title
	 *
	 * @var string
	 */
	private $_title;
	
	/**
	 * Sheet state
	 *
	 * @var string
	 */
	private $_sheetState;

	/**
	 * Page setup
	 *
	 * @var ExcelWorksheet_PageSetup
	 */
	private $_pageSetup;

	/**
	 * Page margins
	 *
	 * @var ExcelWorksheet_PageMargins
	 */
	private $_pageMargins;

	/**
	 * Page header/footer
	 *
	 * @var ExcelWorksheet_HeaderFooter
	 */
	private $_headerFooter;

	/**
	 * Sheet view
	 *
	 * @var ExcelWorksheet_SheetView
	 */
	private $_sheetView;

	/**
	 * Protection
	 *
	 * @var ExcelWorksheet_Protection
	 */
	private $_protection;

	/**
	 * Collection of styles
	 *
	 * @var ExcelStyle[]
	 */
	private $_styles = array();

	/**
	 * Conditional styles. Indexed by cell coordinate, e.g. 'A1'
	 *
	 * @var array
	 */
	private $_conditionalStylesCollection = array();

	/**
	 * Is the current cell collection sorted already?
	 *
	 * @var boolean
	 */
	private $_cellCollectionIsSorted = false;

	/**
	 * Collection of breaks
	 *
	 * @var array
	 */
	private $_breaks = array();

	/**
	 * Collection of merged cell ranges
	 *
	 * @var array
	 */
	private $_mergeCells = array();

	/**
	 * Collection of protected cell ranges
	 *
	 * @var array
	 */
	private $_protectedCells = array();

	/**
	 * Autofilter Range
	 *
	 * @var string
	 */
	private $_autoFilter = '';

	/**
	 * Freeze pane
	 *
	 * @var string
	 */
	private $_freezePane = '';

	/**
	 * Show gridlines?
	 *
	 * @var boolean
	 */
	private $_showGridlines = true;

	/**
	* Print gridlines?
	*
	* @var boolean
	*/
	private $_printGridlines = false;

	/**
	 * Show summary below? (Row/Column outline)
	 *
	 * @var boolean
	 */
	private $_showSummaryBelow = true;

	/**
	 * Show summary right? (Row/Column outline)
	 *
	 * @var boolean
	 */
	private $_showSummaryRight = true;

	/**
	 * Collection of comments
	 *
	 * @var ExcelComment[]
	 */
	private $_comments = array();

	/**
	 * Selected cell
	 *
	 * @var string
	 */
	private $_selectedCell = 'A1';

	/**
	 * Temporary property used by style supervisor. Will be removed
	 *
	 * @var string
	 */
	private $_xActiveCell = 'A1';

	/**
	 * Temporary property used by style supervisor. Will be removed
	 *
	 * @var string
	 */
	private $_xSelectedCells = 'A1:A1';

	/**
	 * Cached highest column
	 *
	 * @var string
	 */
	private $_cachedHighestColumn = 'A';

	/**
	 * Cached highest row
	 *
	 * @var int
	 */
	private $_cachedHighestRow = 1;

	/**
	 * Right-to-left?
	 *
	 * @var boolean
	 */
	private $_rightToLeft = false;

	/**
	 * Hyperlinks. Indexed by cell coordinate, e.g. 'A1'
	 *
	 * @var array
	 */
	private $_hyperlinkCollection = array();

	/**
	 * Data validation objects. Indexed by cell coordinate, e.g. 'A1'
	 *
	 * @var array
	 */
	private $_dataValidationCollection = array();

	/**
	 * Tab color
	 *
	 * @var ExcelStyle_Color
	 */
	private $_tabColor;

	/**
	 * Create a new worksheet
	 *
	 * @param PHPExcel 		$pParent
	 * @param string 		$pTitle
	 */
	public function __construct($pParent = null, $pTitle = 'Worksheet')
	{
		// Set parent and title
		$this->_parent = $pParent;
		$this->setTitle($pTitle);
		$this->setSheetState(ExcelWorksheet::SHEETSTATE_VISIBLE);

		// Set page setup
		$this->_pageSetup 			= new ExcelWorksheet_PageSetup();

		// Set page margins
		$this->_pageMargins 		= new ExcelWorksheet_PageMargins();

		// Set page header/footer
		$this->_headerFooter 		= new ExcelWorksheet_HeaderFooter();

		// Set sheet view
		$this->_sheetView           = new ExcelWorksheet_SheetView();

    	// Drawing collection
    	$this->_drawingCollection 	= new ArrayObject();

    	// Protection
    	$this->_protection			= new ExcelWorksheet_Protection();

    	// Gridlines
    	$this->_showGridlines		= true;
		$this->_printGridlines		= false;

    	// Outline summary
    	$this->_showSummaryBelow	= true;
    	$this->_showSummaryRight	= true;

    	// Default row dimension
    	$this->_defaultRowDimension = new ExcelWorksheet_RowDimension(null);

    	// Default column dimension
    	$this->_defaultColumnDimension = new ExcelWorksheet_ColumnDimension(null);
	}

	/**
	 * Check sheet title for valid Excel syntax
	 *
	 * @param string $pValue The string to check
	 * @return string The valid string
	 * @throws Exception
	 */
	private static function _checkSheetTitle($pValue)
	{
		// Some of the printable ASCII characters are invalid:  * : / \ ? [ ]
		if (preg_match('/(\\*|\\:|\\/|\\\\|\\?|\\[|\\])/', $pValue)) {
			throw new Exception('Invalid character found in sheet title');
		}

		// Maximum 31 characters allowed for sheet title
		if (ExcelShared_String::CountCharacters($pValue) > 31) {
			throw new Exception('Maximum 31 characters allowed in sheet title.');
		}

		return $pValue;
	}

	/**
	 * Get collection of cells
	 *
	 * @param boolean $pSorted Also sort the cell collection?
	 * @return ExcelCell[]
	 */
	public function getCellCollection($pSorted = true)
	{
		if ($pSorted) {
			// Re-order cell collection
			$this->sortCellCollection();
		}

		return $this->_cellCollection;
	}

	/**
	 * Sort collection of cells
	 *
	 * @return ExcelWorksheet
	 */
	public function sortCellCollection()
	{
		if (!$this->_cellCollectionIsSorted) {
			// Re-order cell collection
        	// uasort($this->_cellCollection, array('ExcelCell', 'compareCells')); <-- slow

			$indexed = array();
			foreach (array_keys($this->_cellCollection) as $index) {
				$rowNum = $this->_cellCollection[$index]->getRow();
				$colNum = ExcelCell::columnIndexFromString($this->_cellCollection[$index]->getColumn());

				// Columns are limited to ZZZ (18278), so 20000 is plenty to assure no conflicts
				$key =  $rowNum * 20000 + $colNum;

				$indexed[$key] = $index; // &$this->_cellCollection[$index];
			}
			ksort($indexed);

			// Rebuild cellCollection from the sorted index
			$newCellCollection = array();
		    foreach ($indexed as $index) {
		        $newCellCollection[$index] = $this->_cellCollection[$index];
			}

			$this->_cellCollection = $newCellCollection;

			$this->_cellCollectionIsSorted = true;
		}
		return $this;
	}

	/**
	 * Get collection of row dimensions
	 *
	 * @return ExcelWorksheet_RowDimension[]
	 */
	public function getRowDimensions()
	{
		return $this->_rowDimensions;
	}

	/**
	 * Get default row dimension
	 *
	 * @return ExcelWorksheet_RowDimension
	 */
	public function getDefaultRowDimension()
	{
		return $this->_defaultRowDimension;
	}

	/**
	 * Get collection of column dimensions
	 *
	 * @return ExcelWorksheet_ColumnDimension[]
	 */
	public function getColumnDimensions()
	{
		return $this->_columnDimensions;
	}

	/**
	 * Get default column dimension
	 *
	 * @return ExcelWorksheet_ColumnDimension
	 */
	public function getDefaultColumnDimension()
	{
		return $this->_defaultColumnDimension;
	}

	/**
	 * Get collection of drawings
	 *
	 * @return ExcelWorksheet_BaseDrawing[]
	 */
	public function getDrawingCollection()
	{
		return $this->_drawingCollection;
	}

	/**
	 * Refresh column dimensions
	 *
	 * @return ExcelWorksheet
	 */
	public function refreshColumnDimensions()
	{
		$currentColumnDimensions = $this->getColumnDimensions();
		$newColumnDimensions = array();

		foreach ($currentColumnDimensions as $objColumnDimension) {
			$newColumnDimensions[$objColumnDimension->getColumnIndex()] = $objColumnDimension;
		}

		$this->_columnDimensions = $newColumnDimensions;

		return $this;
	}

	/**
	 * Refresh row dimensions
	 *
	 * @return ExcelWorksheet
	 */
	public function refreshRowDimensions()
	{
		$currentRowDimensions = $this->getRowDimensions();
		$newRowDimensions = array();

		foreach ($currentRowDimensions as $objRowDimension) {
			$newRowDimensions[$objRowDimension->getRowIndex()] = $objRowDimension;
		}

		$this->_rowDimensions = $newRowDimensions;

		return $this;
	}

    /**
     * Calculate worksheet dimension
     *
     * @return string  String containing the dimension of this worksheet
     */
    public function calculateWorksheetDimension()
    {
        // Return
        return 'A1' . ':' .  $this->getHighestColumn() . $this->getHighestRow();
    }

	/**
	 * Calculate widths for auto-size columns
	 *
	 * @param  boolean  $calculateMergeCells  Calculate merge cell width
	 * @return ExcelWorksheet;
	 */
	public function calculateColumnWidths($calculateMergeCells = false)
	{
		$autoSizes = array();
		foreach ($this->getColumnDimensions() as $colDimension) {
			if ($colDimension->getAutoSize()) {
				$autoSizes[$colDimension->getColumnIndex()] = -1;
			}
		}

		foreach ($this->getCellCollection() as $cell) {
			if (isset($autoSizes[$cell->getColumn()])) {
				// Calculated value
				$cellValue = $cell->getCalculatedValue();

				// To formatted string
				$cellValue = ExcelStyleNumberFormat::toFormattedString($cellValue, $this->getParent()->getCellXfByIndex($cell->getXfIndex())->getNumberFormat()->getFormatCode());

				foreach ($this->getMergeCells() as $cells) {
					if ($cell->isInRange($cells) && !$calculateMergeCells) {
						$cellValue = ''; // do not calculate merge cells
					}
				}

				$autoSizes[$cell->getColumn()] = max(
					(float)$autoSizes[$cell->getColumn()],
					(float)ExcelSharedFont::calculateColumnWidth(
						$this->getParent()->getCellXfByIndex($cell->getXfIndex())->getFont(),
						$cellValue,
						$this->getParent()->getCellXfByIndex($cell->getXfIndex())->getAlignment()->getTextRotation(),
						$this->getDefaultStyle()->getFont()
					)
				);
			}
		}
		foreach ($autoSizes as $columnIndex => $width) {
			if ($width == -1) $width = $this->getDefaultColumnDimension()->getWidth();
			$this->getColumnDimension($columnIndex)->setWidth($width);
		}

		return $this;
    }

    /**
     * Get parent
     *
     * @return PHPExcel
     */
    public function getParent() {
    	return $this->_parent;
    }

    /**
     * Re-bind parent
     *
     * @param PHPExcel $parent
     * @return ExcelWorksheet
     */
    public function rebindParent(PHPExcel $parent) {
		$namedRanges = $this->_parent->getNamedRanges();
		foreach ($namedRanges as $namedRange) {
			$parent->addNamedRange($namedRange);
		}

		$this->_parent->removeSheetByIndex(
			$this->_parent->getIndex($this)
		);
		$this->_parent = $parent;

		return $this;
    }

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->_title;
	}

    /**
     * Set title
     *
     * @param string $pValue String containing the dimension of this worksheet
	 * @return ExcelWorksheet
     */
    public function setTitle($pValue = 'Worksheet')
    {
    	// Is this a 'rename' or not?
    	if ($this->getTitle() == $pValue) {
    		return;
    	}

		// Syntax check
		self::_checkSheetTitle($pValue);

    	// Old title
    	$oldTitle = $this->getTitle();

		// Is there already such sheet name?
		if ($this->getParent()->getSheetByName($pValue)) {
			// Use name, but append with lowest possible integer

			$i = 1;
			while ($this->getParent()->getSheetByName($pValue . ' ' . $i)) {
				++$i;
			}

			$altTitle = $pValue . ' ' . $i;
			$this->setTitle($altTitle);

			return;
		}

		// Set title
        $this->_title = $pValue;

    	// New title
    	$newTitle = $this->getTitle();
    	ExcelReferenceHelper::getInstance()->updateNamedFormulas($this->getParent(), $oldTitle, $newTitle);

    	return $this;
    }
	
	/**
	 * Get sheet state
	 *
	 * @return string Sheet state (visible, hidden, veryHidden)
	 */
	public function getSheetState() {
		return $this->_sheetState;
	}
	
	/**
	 * Set sheet state
	 *
	 * @param string $value Sheet state (visible, hidden, veryHidden)
	 * @return ExcelWorksheet
	 */
	public function setSheetState($value = ExcelWorksheet::SHEETSTATE_VISIBLE) {
		$this->_sheetState = $value;
		return $this;
	}
	
    /**
     * Get page setup
     *
     * @return ExcelWorksheet_PageSetup
     */
    public function getPageSetup()
    {
    	return $this->_pageSetup;
    }

    /**
     * Set page setup
     *
     * @param ExcelWorksheet_PageSetup	$pValue
     * @return ExcelWorksheet
     */
    public function setPageSetup(ExcelWorksheet_PageSetup $pValue)
    {
   		$this->_pageSetup = $pValue;
   		return $this;
    }

    /**
     * Get page margins
     *
     * @return ExcelWorksheet_PageMargins
     */
    public function getPageMargins()
    {
    	return $this->_pageMargins;
    }

    /**
     * Set page margins
     *
     * @param ExcelWorksheet_PageMargins	$pValue
     * @return ExcelWorksheet
     */
    public function setPageMargins(ExcelWorksheet_PageMargins $pValue)
    {
   		$this->_pageMargins = $pValue;
   		return $this;
    }

    /**
     * Get page header/footer
     *
     * @return ExcelWorksheet_HeaderFooter
     */
    public function getHeaderFooter()
    {
    	return $this->_headerFooter;
    }

    /**
     * Set page header/footer
     *
     * @param ExcelWorksheet_HeaderFooter	$pValue
     * @return ExcelWorksheet
     */
    public function setHeaderFooter(ExcelWorksheet_HeaderFooter $pValue)
    {
    	$this->_headerFooter = $pValue;
    	return $this;
    }

    /**
     * Get sheet view
     *
     * @return ExcelWorksheet_HeaderFooter
     */
    public function getSheetView()
    {
    	return $this->_sheetView;
    }

    /**
     * Set sheet view
     *
     * @param ExcelWorksheet_SheetView	$pValue
     * @return ExcelWorksheet
     */
    public function setSheetView(ExcelWorksheet_SheetView $pValue)
    {
    	$this->_sheetView = $pValue;
    	return $this;
    }

    /**
     * Get Protection
     *
     * @return ExcelWorksheet_Protection
     */
    public function getProtection()
    {
    	return $this->_protection;
    }

    /**
     * Set Protection
     *
     * @param ExcelWorksheet_Protection	$pValue
     * @return ExcelWorksheet
     */
    public function setProtection(ExcelWorksheet_Protection $pValue)
    {
   		$this->_protection = $pValue;
   		return $this;
    }

    /**
     * Get highest worksheet column
     *
     * @return string Highest column name
     */
    public function getHighestColumn()
    {
		return $this->_cachedHighestColumn;
    }

    /**
     * Get highest worksheet row
     *
     * @return int Highest row number
     */
    public function getHighestRow()
    {
		return $this->_cachedHighestRow;
    }

    /**
     * Set a cell value
     *
     * @param string 	$pCoordinate	Coordinate of the cell
     * @param mixed 	$pValue			Value of the cell
     * @return ExcelWorksheet
     */
    public function setCellValue($pCoordinate = 'A1', $pValue = null)
    {
    	// Set value
    	$this->getCell($pCoordinate)->setValue($pValue);

    	return $this;
    }

    /**
     * Set a cell value by using numeric cell coordinates
     *
     * @param string 	$pColumn		Numeric column coordinate of the cell
     * @param string 	$pRow			Numeric row coordinate of the cell
     * @param mixed 	$pValue			Value of the cell
     * @return ExcelWorksheet
     */
    public function setCellValueByColumnAndRow($pColumn = 0, $pRow = 0, $pValue = null)
    {
    	return $this->setCellValue(ExcelCell::stringFromColumnIndex($pColumn) . $pRow, $pValue);
    }

    /**
     * Set a cell value
     *
     * @param string 	$pCoordinate	Coordinate of the cell
     * @param mixed 	$pValue			Value of the cell
     * @param string	$pDataType		Explicit data type
     * @return ExcelWorksheet
     */
    public function setCellValueExplicit($pCoordinate = 'A1', $pValue = null, $pDataType = ExcelCell_DataType::TYPE_STRING)
    {
    	// Set value
    	$this->getCell($pCoordinate)->setValueExplicit($pValue, $pDataType);
    	return $this;
    }

    /**
     * Set a cell value by using numeric cell coordinates
     *
     * @param string 	$pColumn		Numeric column coordinate of the cell
     * @param string 	$pRow			Numeric row coordinate of the cell
     * @param mixed 	$pValue			Value of the cell
     * @param string	$pDataType		Explicit data type
     * @return ExcelWorksheet
     */
    public function setCellValueExplicitByColumnAndRow($pColumn = 0, $pRow = 0, $pValue = null, $pDataType = ExcelCell_DataType::TYPE_STRING)
    {
    	return $this->setCellValueExplicit(ExcelCell::stringFromColumnIndex($pColumn) . $pRow, $pValue, $pDataType);
    }

    /**
     * Get cell at a specific coordinate
     *
     * @param 	string 			$pCoordinate	Coordinate of the cell
     * @throws 	Exception
     * @return 	ExcelCell 	Cell that was found
     */
    public function getCell($pCoordinate = 'A1')
    {
		// Check cell collection
		if (isset($this->_cellCollection[$pCoordinate])) {
			return $this->_cellCollection[$pCoordinate];
		}

		// Worksheet reference?
		if (strpos($pCoordinate, '!') !== false) {
			$worksheetReference = ExcelWorksheet::extractSheetTitle($pCoordinate, true);
			return $this->getParent()->getSheetByName($worksheetReference[0])->getCell($worksheetReference[1]);
		}

		// Named range?
		if ((!preg_match('/^'.ExcelCalculation::CALCULATION_REGEXP_CELLREF.'$/i', $pCoordinate, $matches)) &&
			(preg_match('/^'.ExcelCalculation::CALCULATION_REGEXP_NAMEDRANGE.'$/i', $pCoordinate, $matches))) {
			$namedRange = ExcelNamedRange::resolveRange($pCoordinate, $this);
			if (!is_null($namedRange)) {
				$pCoordinate = $namedRange->getRange();
				if ($this->getHashCode() != $namedRange->getWorksheet()->getHashCode()) {
					if (!$namedRange->getLocalOnly()) {
						return $namedRange->getWorksheet()->getCell($pCoordinate);
					} else {
						throw new Exception('Named range ' . $namedRange->getName() . ' is not accessible from within sheet ' . $this->getTitle());
					}
				} else {
					//Allow named ranges within the same sheet.
					return $this->getCell($pCoordinate);
				}
			}
		}

    	// Uppercase coordinate
    	$pCoordinate = strtoupper($pCoordinate);

    	if (strpos($pCoordinate,':') !== false || strpos($pCoordinate,',') !== false) {
    		throw new Exception('Cell coordinate can not be a range of cells.');
    	} elseif (strpos($pCoordinate,'$') !== false) {
    		throw new Exception('Cell coordinate must not be absolute.');
    	} else {
			// Create new cell object

			// Coordinates
			$aCoordinates = ExcelCell::coordinateFromString($pCoordinate);

			$this->_cellCollection[$pCoordinate] = new ExcelCell($aCoordinates[0], $aCoordinates[1], null, ExcelCell_DataType::TYPE_NULL, $this);
			$this->_cellCollectionIsSorted = false;

			if (ExcelCell::columnIndexFromString($this->_cachedHighestColumn) < ExcelCell::columnIndexFromString($aCoordinates[0]))
				$this->_cachedHighestColumn = $aCoordinates[0];

			if ($this->_cachedHighestRow < $aCoordinates[1])
				$this->_cachedHighestRow = $aCoordinates[1];

			// Cell needs appropriate xfIndex
			$rowDimensions    = $this->getRowDimensions();
			$columnDimensions = $this->getColumnDimensions();

			if ( isset($rowDimensions[$aCoordinates[1]]) && $rowDimensions[$aCoordinates[1]]->getXfIndex() !== null ) {
				// then there is a row dimension with explicit style, assign it to the cell
				$this->_cellCollection[$pCoordinate]->setXfIndex($rowDimensions[$aCoordinates[1]]->getXfIndex());

			} else if ( isset($columnDimensions[$aCoordinates[0]]) ) {
				// then there is a column dimension, assign it to the cell
				$this->_cellCollection[$pCoordinate]->setXfIndex($columnDimensions[$aCoordinates[0]]->getXfIndex());

			} else {
				// set to default index
				$this->_cellCollection[$pCoordinate]->setXfIndex(0);
			}

	        return $this->_cellCollection[$pCoordinate];
    	}
    }

    /**
     * Get cell at a specific coordinate by using numeric cell coordinates
     *
     * @param 	string $pColumn		Numeric column coordinate of the cell
     * @param 	string $pRow		Numeric row coordinate of the cell
     * @return 	ExcelCell 		Cell that was found
     */
    public function getCellByColumnAndRow($pColumn = 0, $pRow = 0)
    {
		$coordinate = ExcelCell::stringFromColumnIndex($pColumn) . $pRow;

		if (!isset($this->_cellCollection[$coordinate])) {
			$columnLetter = ExcelCell::stringFromColumnIndex($pColumn);

			$this->_cellCollection[$coordinate] = new ExcelCell($columnLetter, $pRow, null, ExcelCell_DataType::TYPE_NULL, $this);
			$this->_cellCollectionIsSorted = false;

			if (ExcelCell::columnIndexFromString($this->_cachedHighestColumn) < $pColumn)
				$this->_cachedHighestColumn = $pColumn;

			if ($this->_cachedHighestRow < $pRow)
				$this->_cachedHighestRow = $pRow;
		}

		return $this->_cellCollection[$coordinate];
    }

    /**
     * Cell at a specific coordinate exists?
     *
     * @param 	string 			$pCoordinate	Coordinate of the cell
     * @throws 	Exception
     * @return 	boolean
     */
    public function cellExists($pCoordinate = 'A1')
    {
    	// Worksheet reference?
		if (strpos($pCoordinate, '!') !== false) {
			$worksheetReference = ExcelWorksheet::extractSheetTitle($pCoordinate, true);
			return $this->getParent()->getSheetByName($worksheetReference[0])->cellExists($worksheetReference[1]);
		}

		// Named range?
		if ((!preg_match('/^'.ExcelCalculation::CALCULATION_REGEXP_CELLREF.'$/i', $pCoordinate, $matches)) &&
			(preg_match('/^'.ExcelCalculation::CALCULATION_REGEXP_NAMEDRANGE.'$/i', $pCoordinate, $matches))) {
			$namedRange = ExcelNamedRange::resolveRange($pCoordinate, $this);
			if (!is_null($namedRange)) {
				$pCoordinate = $namedRange->getRange();
				if ($this->getHashCode() != $namedRange->getWorksheet()->getHashCode()) {
					if (!$namedRange->getLocalOnly()) {
						return $namedRange->getWorksheet()->cellExists($pCoordinate);
					} else {
						throw new Exception('Named range ' . $namedRange->getName() . ' is not accessible from within sheet ' . $this->getTitle());
					}
				}
			}
		}

    	// Uppercase coordinate
    	$pCoordinate = strtoupper($pCoordinate);

    	if (strpos($pCoordinate,':') !== false || strpos($pCoordinate,',') !== false) {
    		throw new Exception('Cell coordinate can not be a range of cells.');
    	} elseif (strpos($pCoordinate,'$') !== false) {
    		throw new Exception('Cell coordinate must not be absolute.');
    	} else {
	    	// Coordinates
	    	$aCoordinates = ExcelCell::coordinateFromString($pCoordinate);

	        // Cell exists?
	        return isset($this->_cellCollection[$pCoordinate]);
    	}
    }

    /**
     * Cell at a specific coordinate by using numeric cell coordinates exists?
     *
     * @param 	string $pColumn		Numeric column coordinate of the cell
     * @param 	string $pRow		Numeric row coordinate of the cell
     * @return 	boolean
     */
    public function cellExistsByColumnAndRow($pColumn = 0, $pRow = 0)
    {
    	return $this->cellExists(ExcelCell::stringFromColumnIndex($pColumn) . $pRow);
    }

    /**
     * Get row dimension at a specific row
     *
     * @param int $pRow	Numeric index of the row
     * @return ExcelWorksheet_RowDimension
     */
    public function getRowDimension($pRow = 0)
    {
    	// Found
    	$found = null;

        // Get row dimension
        if (!isset($this->_rowDimensions[$pRow])) {
        	$this->_rowDimensions[$pRow] = new ExcelWorksheet_RowDimension($pRow);

			if ($this->_cachedHighestRow < $pRow)
				$this->_cachedHighestRow = $pRow;
        }
        return $this->_rowDimensions[$pRow];
    }

    /**
     * Get column dimension at a specific column
     *
     * @param string $pColumn	String index of the column
     * @return ExcelWorksheet_ColumnDimension
     */
    public function getColumnDimension($pColumn = 'A')
    {
    	// Uppercase coordinate
    	$pColumn = strtoupper($pColumn);

    	// Fetch dimensions
    	if (!isset($this->_columnDimensions[$pColumn])) {
    		$this->_columnDimensions[$pColumn] = new ExcelWorksheet_ColumnDimension($pColumn);

			if (ExcelCell::columnIndexFromString($this->_cachedHighestColumn) < ExcelCell::columnIndexFromString($pColumn))
				$this->_cachedHighestColumn = $pColumn;
    	}
    	return $this->_columnDimensions[$pColumn];
    }

    /**
     * Get column dimension at a specific column by using numeric cell coordinates
     *
     * @param 	string $pColumn		Numeric column coordinate of the cell
     * @param 	string $pRow		Numeric row coordinate of the cell
     * @return 	ExcelWorksheet_ColumnDimension
     */
    public function getColumnDimensionByColumn($pColumn = 0)
    {
        return $this->getColumnDimension(ExcelCell::stringFromColumnIndex($pColumn));
    }

    /**
     * Get styles
     *
     * @return ExcelStyle[]
     */
    public function getStyles()
    {
    	return $this->_styles;
    }

    /**
     * Get default style of workbork.
     *
     * @deprecated
     * @return 	ExcelStyle
     * @throws 	Exception
     */
    public function getDefaultStyle()
    {
    	return $this->_parent->getDefaultStyle();
    }

    /**
     * Set default style - should only be used by ExcelIReader implementations!
     *
     * @deprecated
     * @param 	ExcelStyle $value
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function setDefaultStyle(ExcelStyle $value)
    {
		$this->_parent->setDefaultStyle($value);
		return $this;
    }

    /**
     * Get style for cell
     *
     * @param 	string 	$pCellCoordinate	Cell coordinate to get style for
     * @return 	ExcelStyle
     * @throws 	Exception
     */
    public function getStyle($pCellCoordinate = 'A1')
    {
		// set this sheet as active
		$this->_parent->setActiveSheetIndex($this->_parent->getIndex($this));

		// set cell coordinate as active
		$this->setXSelectedCells($pCellCoordinate);

		return $this->_parent->getCellXfSupervisor();
    }

	/**
	 * Get conditional styles for a cell
	 *
	 * @param string $pCoordinate
	 * @return ExcelStyle_Conditional[]
	 */
	public function getConditionalStyles($pCoordinate = 'A1')
	{
		if (!isset($this->_conditionalStylesCollection[$pCoordinate])) {
			$this->_conditionalStylesCollection[$pCoordinate] = array();
		}
		return $this->_conditionalStylesCollection[$pCoordinate];
	}

	/**
	 * Do conditional styles exist for this cell?
	 *
	 * @param string $pCoordinate
	 * @return boolean
	 */
	public function conditionalStylesExists($pCoordinate = 'A1')
	{
		if (isset($this->_conditionalStylesCollection[$pCoordinate])) {
			return true;
		}
		return false;
	}

	/**
	 * Removes conditional styles for a cell
	 *
	 * @param string $pCoordinate
	 * @return ExcelWorksheet
	 */
	public function removeConditionalStyles($pCoordinate = 'A1')
	{
		unset($this->_conditionalStylesCollection[$pCoordinate]);
		return $this;
	}

	/**
	 * Get collection of conditional styles
	 *
	 * @return array
	 */
	public function getConditionalStylesCollection()
	{
		return $this->_conditionalStylesCollection;
	}

	/**
	 * Set conditional styles
	 *
	 * @param $pCoordinate string E.g. 'A1'
	 * @param $pValue ExcelStyle_Conditional[]
	 * @return ExcelWorksheet
	 */
	public function setConditionalStyles($pCoordinate = 'A1', $pValue)
	{
		$this->_conditionalStylesCollection[$pCoordinate] = $pValue;
		return $this;
	}

    /**
     * Get style for cell by using numeric cell coordinates
     *
     * @param 	int $pColumn	Numeric column coordinate of the cell
     * @param 	int $pRow		Numeric row coordinate of the cell
     * @return 	ExcelStyle
     */
    public function getStyleByColumnAndRow($pColumn = 0, $pRow = 0)
    {
    	return $this->getStyle(ExcelCell::stringFromColumnIndex($pColumn) . $pRow);
    }

    /**
     * Set shared cell style to a range of cells
     *
     * Please note that this will overwrite existing cell styles for cells in range!
     *
     * @deprecated
     * @param 	ExcelStyle	$pSharedCellStyle	Cell style to share
     * @param 	string			$pRange				Range of cells (i.e. "A1:B10"), or just one cell (i.e. "A1")
     * @throws	Exception
     * @return ExcelWorksheet
     */
     public function setSharedStyle(ExcelStyle $pSharedCellStyle = null, $pRange = '')
    {
		$this->duplicateStyle($pSharedCellStyle, $pRange);
		return $this;
    }

    /**
     * Duplicate cell style to a range of cells
     *
     * Please note that this will overwrite existing cell styles for cells in range!
     *
     * @param 	ExcelStyle	$pCellStyle	Cell style to duplicate
     * @param 	string			$pRange		Range of cells (i.e. "A1:B10"), or just one cell (i.e. "A1")
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function duplicateStyle(ExcelStyle $pCellStyle = null, $pRange = '')
    {
    	// make sure we have a real style and not supervisor
		$style = $pCellStyle->getIsSupervisor() ? $pCellStyle->getSharedComponent() : $pCellStyle;

		// Add the style to the workbook if necessary
		$workbook = $this->_parent;
		if ($existingStyle = $this->_parent->getCellXfByHashCode($pCellStyle->getHashCode())) {
			// there is already such cell Xf in our collection
			$xfIndex = $existingStyle->getIndex();
		} else {
			// we don't have such a cell Xf, need to add
			$workbook->addCellXf($pCellStyle);
			$xfIndex = $pCellStyle->getIndex();
		}

		// Uppercase coordinate
    	$pRange = strtoupper($pRange);

   		// Is it a cell range or a single cell?
   		$rangeA 	= '';
   		$rangeB 	= '';
   		if (strpos($pRange, ':') === false) {
   			$rangeA = $pRange;
   			$rangeB = $pRange;
   		} else {
   			list($rangeA, $rangeB) = explode(':', $pRange);
   		}

   		// Calculate range outer borders
   		$rangeStart = ExcelCell::coordinateFromString($rangeA);
   		$rangeEnd 	= ExcelCell::coordinateFromString($rangeB);

   		// Translate column into index
   		$rangeStart[0]	= ExcelCell::columnIndexFromString($rangeStart[0]) - 1;
   		$rangeEnd[0]	= ExcelCell::columnIndexFromString($rangeEnd[0]) - 1;

   		// Make sure we can loop upwards on rows and columns
   		if ($rangeStart[0] > $rangeEnd[0] && $rangeStart[1] > $rangeEnd[1]) {
   			$tmp = $rangeStart;
   			$rangeStart = $rangeEnd;
   			$rangeEnd = $tmp;
   		}

   		// Loop through cells and apply styles
   		for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
   			for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
   				$this->getCell(ExcelCell::stringFromColumnIndex($col) . $row)->setXfIndex($xfIndex);
   			}
   		}

   		return $this;
    }

    /**
     * Duplicate cell style array to a range of cells
     *
     * Please note that this will overwrite existing cell styles for cells in range,
     * if they are in the styles array. For example, if you decide to set a range of
     * cells to font bold, only include font bold in the styles array.
     *
     * @deprecated
     * @param	array			$pStyles	Array containing style information
     * @param 	string			$pRange		Range of cells (i.e. "A1:B10"), or just one cell (i.e. "A1")
     * @param 	boolean			$pAdvanced	Advanced mode for setting borders.
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function duplicateStyleArray($pStyles = null, $pRange = '', $pAdvanced = true)
    {
		$this->getStyle($pRange)->applyFromArray($pStyles, $pAdvanced);
    	return $this;
    }

    /**
     * Set break on a cell
     *
     * @param 	string			$pCell		Cell coordinate (e.g. A1)
     * @param 	int				$pBreak		Break type (type of ExcelWorksheet::BREAK_*)
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function setBreak($pCell = 'A1', $pBreak = ExcelWorksheet::BREAK_NONE)
    {
    	// Uppercase coordinate
    	$pCell = strtoupper($pCell);

    	if ($pCell != '') {
    		$this->_breaks[$pCell] = $pBreak;
    	} else {
    		throw new Exception('No cell coordinate specified.');
    	}

    	return $this;
    }

    /**
     * Set break on a cell by using numeric cell coordinates
     *
     * @param 	int 	$pColumn	Numeric column coordinate of the cell
     * @param 	int 	$pRow		Numeric row coordinate of the cell
     * @param 	int		$pBreak		Break type (type of ExcelWorksheet::BREAK_*)
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function setBreakByColumnAndRow($pColumn = 0, $pRow = 0, $pBreak = ExcelWorksheet::BREAK_NONE)
    {
    	return $this->setBreak(ExcelCell::stringFromColumnIndex($pColumn) . $pRow, $pBreak);
    }

    /**
     * Get breaks
     *
     * @return array[]
     */
    public function getBreaks()
    {
    	return $this->_breaks;
    }

    /**
     * Set merge on a cell range
     *
     * @param 	string			$pRange		Cell range (e.g. A1:E1)
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function mergeCells($pRange = 'A1:A1')
    {
    	// Uppercase coordinate
    	$pRange = strtoupper($pRange);

    	if (strpos($pRange,':') !== false) {
    		$this->_mergeCells[$pRange] = $pRange;
    	} else {
    		throw new Exception('Merge must be set on a range of cells.');
    	}

    	return $this;
    }

    /**
     * Set merge on a cell range by using numeric cell coordinates
     *
     * @param 	int $pColumn1	Numeric column coordinate of the first cell
     * @param 	int $pRow1		Numeric row coordinate of the first cell
     * @param 	int $pColumn2	Numeric column coordinate of the last cell
     * @param 	int $pRow2		Numeric row coordinate of the last cell
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function mergeCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 0, $pColumn2 = 0, $pRow2 = 0)
    {
    	$cellRange = ExcelCell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . ExcelCell::stringFromColumnIndex($pColumn2) . $pRow2;
    	return $this->mergeCells($cellRange);
    }

    /**
     * Remove merge on a cell range
     *
     * @param 	string			$pRange		Cell range (e.g. A1:E1)
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function unmergeCells($pRange = 'A1:A1')
    {
    	// Uppercase coordinate
    	$pRange = strtoupper($pRange);

    	if (strpos($pRange,':') !== false) {
    		if (isset($this->_mergeCells[$pRange])) {
    			unset($this->_mergeCells[$pRange]);
    		} else {
    			throw new Exception('Cell range ' . $pRange . ' not known as merged.');
    		}
    	} else {
    		throw new Exception('Merge can only be removed from a range of cells.');
    	}

    	return $this;
    }

    /**
     * Remove merge on a cell range by using numeric cell coordinates
     *
     * @param 	int $pColumn1	Numeric column coordinate of the first cell
     * @param 	int $pRow1		Numeric row coordinate of the first cell
     * @param 	int $pColumn2	Numeric column coordinate of the last cell
     * @param 	int $pRow2		Numeric row coordinate of the last cell
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function unmergeCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 0, $pColumn2 = 0, $pRow2 = 0)
    {
    	$cellRange = ExcelCell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . ExcelCell::stringFromColumnIndex($pColumn2) . $pRow2;
    	return $this->unmergeCells($cellRange);
    }

    /**
     * Get merge cells array.
     *
     * @return array[]
     */
    public function getMergeCells()
    {
    	return $this->_mergeCells;
    }

	/**
	 * Set merge cells array for the entire sheet. Use instead mergeCells() to merge
	 * a single cell range.
	 *
	 * @param array 
	 */
	public function setMergeCells($pValue = array())
	{
		$this->_mergeCells = $pValue;

		return $this;
	}

    /**
     * Set protection on a cell range
     *
     * @param 	string			$pRange				Cell (e.g. A1) or cell range (e.g. A1:E1)
     * @param 	string			$pPassword			Password to unlock the protection
     * @param 	boolean 		$pAlreadyHashed 	If the password has already been hashed, set this to true
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function protectCells($pRange = 'A1', $pPassword = '', $pAlreadyHashed = false)
    {
    	// Uppercase coordinate
    	$pRange = strtoupper($pRange);

    	if (!$pAlreadyHashed) {
    		$pPassword = ExcelShared_PasswordHasher::hashPassword($pPassword);
    	}
    	$this->_protectedCells[$pRange] = $pPassword;

    	return $this;
    }

    /**
     * Set protection on a cell range by using numeric cell coordinates
     *
     * @param 	int 	$pColumn1			Numeric column coordinate of the first cell
     * @param 	int 	$pRow1				Numeric row coordinate of the first cell
     * @param 	int 	$pColumn2			Numeric column coordinate of the last cell
     * @param 	int 	$pRow2				Numeric row coordinate of the last cell
     * @param 	string	$pPassword			Password to unlock the protection
     * @param 	boolean $pAlreadyHashed 	If the password has already been hashed, set this to true
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function protectCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 0, $pColumn2 = 0, $pRow2 = 0, $pPassword = '', $pAlreadyHashed = false)
    {
    	$cellRange = ExcelCell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . ExcelCell::stringFromColumnIndex($pColumn2) . $pRow2;
    	return $this->protectCells($cellRange, $pPassword, $pAlreadyHashed);
    }

    /**
     * Remove protection on a cell range
     *
     * @param 	string			$pRange		Cell (e.g. A1) or cell range (e.g. A1:E1)
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function unprotectCells($pRange = 'A1')
    {
    	// Uppercase coordinate
    	$pRange = strtoupper($pRange);

    	if (isset($this->_protectedCells[$pRange])) {
    		unset($this->_protectedCells[$pRange]);
    	} else {
    		throw new Exception('Cell range ' . $pRange . ' not known as protected.');
    	}
    	return $this;
    }

    /**
     * Remove protection on a cell range by using numeric cell coordinates
     *
     * @param 	int 	$pColumn1			Numeric column coordinate of the first cell
     * @param 	int 	$pRow1				Numeric row coordinate of the first cell
     * @param 	int 	$pColumn2			Numeric column coordinate of the last cell
     * @param 	int 	$pRow2				Numeric row coordinate of the last cell
     * @param 	string	$pPassword			Password to unlock the protection
     * @param 	boolean $pAlreadyHashed 	If the password has already been hashed, set this to true
     * @throws	Exception
     * @return ExcelWorksheet
     */
    public function unprotectCellsByColumnAndRow($pColumn1 = 0, $pRow1 = 0, $pColumn2 = 0, $pRow2 = 0, $pPassword = '', $pAlreadyHashed = false)
    {
    	$cellRange = ExcelCell::stringFromColumnIndex($pColumn1) . $pRow1 . ':' . ExcelCell::stringFromColumnIndex($pColumn2) . $pRow2;
    	return $this->unprotectCells($cellRange, $pPassword, $pAlreadyHashed);
    }

    /**
     * Get protected cells
     *
     * @return array[]
     */
    public function getProtectedCells()
    {
    	return $this->_protectedCells;
    }

    /**
     * Get Autofilter Range
     *
     * @return string
     */
    public function getAutoFilter()
    {
    	return $this->_autoFilter;
    }

    /**
     * Set Autofilter Range
     *
     * @param 	string		$pRange		Cell range (i.e. A1:E10)
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function setAutoFilter($pRange = '')
    {
    	// Uppercase coordinate
    	$pRange = strtoupper($pRange);

    	if (strpos($pRange,':') !== false) {
    		$this->_autoFilter = $pRange;
    	} else {
    		throw new Exception('Autofilter must be set on a range of cells.');
    	}
    	return $this;
    }

    /**
     * Set Autofilter Range by using numeric cell coordinates
     *
     * @param 	int 	$pColumn1	Numeric column coordinate of the first cell
     * @param 	int 	$pRow1		Numeric row coordinate of the first cell
     * @param 	int 	$pColumn2	Numeric column coordinate of the second cell
     * @param 	int 	$pRow2		Numeric row coordinate of the second cell
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function setAutoFilterByColumnAndRow($pColumn1 = 0, $pRow1 = 0, $pColumn2 = 0, $pRow2 = 0)
    {
    	return $this->setAutoFilter(
    		ExcelCell::stringFromColumnIndex($pColumn1) . $pRow1
    		. ':' .
    		ExcelCell::stringFromColumnIndex($pColumn2) . $pRow2
    	);
    }

    /**
     * Get Freeze Pane
     *
     * @return string
     */
    public function getFreezePane()
    {
    	return $this->_freezePane;
    }

    /**
     * Freeze Pane
     *
     * @param 	string		$pCell		Cell (i.e. A1)
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function freezePane($pCell = '')
    {
    	// Uppercase coordinate
    	$pCell = strtoupper($pCell);

    	if (strpos($pCell,':') === false && strpos($pCell,',') === false) {
    		$this->_freezePane = $pCell;
    	} else {
    		throw new Exception('Freeze pane can not be set on a range of cells.');
    	}
    	return $this;
    }

    /**
     * Freeze Pane by using numeric cell coordinates
     *
     * @param 	int 	$pColumn	Numeric column coordinate of the cell
     * @param 	int 	$pRow		Numeric row coordinate of the cell
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function freezePaneByColumnAndRow($pColumn = 0, $pRow = 0)
    {
    	return $this->freezePane(ExcelCell::stringFromColumnIndex($pColumn) . $pRow);
    }

    /**
     * Unfreeze Pane
     *
     * @return ExcelWorksheet
     */
    public function unfreezePane()
    {
    	return $this->freezePane('');
    }

    /**
     * Insert a new row, updating all possible related data
     *
     * @param 	int	$pBefore	Insert before this one
     * @param 	int	$pNumRows	Number of rows to insert
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function insertNewRowBefore($pBefore = 1, $pNumRows = 1) {
    	if ($pBefore >= 1) {
    		$objReferenceHelper = ExcelReferenceHelper::getInstance();
    		$objReferenceHelper->insertNewBefore('A' . $pBefore, 0, $pNumRows, $this);
    	} else {
    		throw new Exception("Rows can only be inserted before at least row 1.");
    	}
    	return $this;
    }

    /**
     * Insert a new column, updating all possible related data
     *
     * @param 	int	$pBefore	Insert before this one
     * @param 	int	$pNumCols	Number of columns to insert
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function insertNewColumnBefore($pBefore = 'A', $pNumCols = 1) {
    	if (!is_numeric($pBefore)) {
    		$objReferenceHelper = ExcelReferenceHelper::getInstance();
    		$objReferenceHelper->insertNewBefore($pBefore . '1', $pNumCols, 0, $this);
    	} else {
    		throw new Exception("Column references should not be numeric.");
    	}
    	return $this;
    }

    /**
     * Insert a new column, updating all possible related data
     *
     * @param 	int	$pBefore	Insert before this one (numeric column coordinate of the cell)
     * @param 	int	$pNumCols	Number of columns to insert
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function insertNewColumnBeforeByIndex($pBefore = 0, $pNumCols = 1) {
    	if ($pBefore >= 0) {
    		return $this->insertNewColumnBefore(ExcelCell::stringFromColumnIndex($pBefore), $pNumCols);
    	} else {
    		throw new Exception("Columns can only be inserted before at least column A (0).");
    	}
    }

    /**
     * Delete a row, updating all possible related data
     *
     * @param 	int	$pRow		Remove starting with this one
     * @param 	int	$pNumRows	Number of rows to remove
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function removeRow($pRow = 1, $pNumRows = 1) {
    	if ($pRow >= 1) {
    		$objReferenceHelper = ExcelReferenceHelper::getInstance();
    		$objReferenceHelper->insertNewBefore('A' . ($pRow + $pNumRows), 0, -$pNumRows, $this);
    	} else {
    		throw new Exception("Rows to be deleted should at least start from row 1.");
    	}
    	return $this;
    }

    /**
     * Remove a column, updating all possible related data
     *
     * @param 	int	$pColumn	Remove starting with this one
     * @param 	int	$pNumCols	Number of columns to remove
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function removeColumn($pColumn = 'A', $pNumCols = 1) {
    	if (!is_numeric($pColumn)) {
    		$pColumn = ExcelCell::stringFromColumnIndex(ExcelCell::columnIndexFromString($pColumn) - 1 + $pNumCols);
    		$objReferenceHelper = ExcelReferenceHelper::getInstance();
    		$objReferenceHelper->insertNewBefore($pColumn . '1', -$pNumCols, 0, $this);
    	} else {
    		throw new Exception("Column references should not be numeric.");
    	}
    	return $this;
    }

    /**
     * Remove a column, updating all possible related data
     *
     * @param 	int	$pColumn	Remove starting with this one (numeric column coordinate of the cell)
     * @param 	int	$pNumCols	Number of columns to remove
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function removeColumnByIndex($pColumn = 0, $pNumCols = 1) {
    	if ($pColumn >= 0) {
    		return $this->removeColumn(ExcelCell::stringFromColumnIndex($pColumn), $pNumCols);
    	} else {
    		throw new Exception("Columns can only be inserted before at least column A (0).");
    	}
    }

    /**
     * Show gridlines?
     *
     * @return boolean
     */
    public function getShowGridlines() {
    	return $this->_showGridlines;
    }

    /**
     * Set show gridlines
     *
     * @param boolean $pValue	Show gridlines (true/false)
     * @return ExcelWorksheet
     */
    public function setShowGridlines($pValue = false) {
    	$this->_showGridlines = $pValue;
    	return $this;
    }

	/**
	* Print gridlines?
	*
	* @return boolean
	*/
	public function getPrintGridlines() {
		return $this->_printGridlines;
	}

	/**
	* Set print gridlines
	*
	* @param boolean $pValue Print gridlines (true/false)
	* @return ExcelWorksheet
	*/
	public function setPrintGridlines($pValue = false) {
		$this->_printGridlines = $pValue;
		return $this;
	}

    /**
     * Show summary below? (Row/Column outlining)
     *
     * @return boolean
     */
    public function getShowSummaryBelow() {
    	return $this->_showSummaryBelow;
    }

    /**
     * Set show summary below
     *
     * @param boolean $pValue	Show summary below (true/false)
     * @return ExcelWorksheet
     */
    public function setShowSummaryBelow($pValue = true) {
    	$this->_showSummaryBelow = $pValue;
    	return $this;
    }

    /**
     * Show summary right? (Row/Column outlining)
     *
     * @return boolean
     */
    public function getShowSummaryRight() {
    	return $this->_showSummaryRight;
    }

    /**
     * Set show summary right
     *
     * @param boolean $pValue	Show summary right (true/false)
     * @return ExcelWorksheet
     */
    public function setShowSummaryRight($pValue = true) {
    	$this->_showSummaryRight = $pValue;
    	return $this;
    }

    /**
     * Get comments
     *
     * @return ExcelComment[]
     */
    public function getComments()
    {
    	return $this->_comments;
    }

    /**
     * Get comment for cell
     *
     * @param 	string 	$pCellCoordinate	Cell coordinate to get comment for
     * @return 	ExcelComment
     * @throws 	Exception
     */
    public function getComment($pCellCoordinate = 'A1')
    {
    	// Uppercase coordinate
    	$pCellCoordinate = strtoupper($pCellCoordinate);

    	if (strpos($pCellCoordinate,':') !== false || strpos($pCellCoordinate,',') !== false) {
    		throw new Exception('Cell coordinate string can not be a range of cells.');
    	} else if (strpos($pCellCoordinate,'$') !== false) {
    		throw new Exception('Cell coordinate string must not be absolute.');
    	} else if ($pCellCoordinate == '') {
    		throw new Exception('Cell coordinate can not be zero-length string.');
    	} else {
    		// Check if we already have a comment for this cell.
    		// If not, create a new comment.
    		if (isset($this->_comments[$pCellCoordinate])) {
    			return $this->_comments[$pCellCoordinate];
    		} else {
    			$newComment = new ExcelComment();
    			$this->_comments[$pCellCoordinate] = $newComment;
    			return $newComment;
    		}
    	}
    }

    /**
     * Get comment for cell by using numeric cell coordinates
     *
     * @param 	int $pColumn	Numeric column coordinate of the cell
     * @param 	int $pRow		Numeric row coordinate of the cell
     * @return 	ExcelComment
     */
    public function getCommentByColumnAndRow($pColumn = 0, $pRow = 0)
    {
    	return $this->getComment(ExcelCell::stringFromColumnIndex($pColumn) . $pRow);
    }

    /**
     * Get selected cell
     *
     * @return string
     */
    public function getSelectedCell()
    {
    	return $this->_selectedCell;
    }

    /**
     * Temporary method used by style supervisor. Will be removed
     *
     * @return string
     */
    public function getXActiveCell()
    {
    	return $this->_xActiveCell;
    }

    /**
     * Temporary method used by style supervisor. Will be removed
     *
     * @return string
     */
    public function getXSelectedCells()
    {
    	return $this->_xSelectedCells;
    }

    /**
     * Selected cell
     *
     * @param 	string		$pCell		Cell (i.e. A1)
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function setSelectedCell($pCoordinate = '')
    {
    	// Uppercase coordinate
    	$pCoordinate = strtoupper($pCoordinate);

    	if (strpos($pCoordinate,':') === false && strpos($pCoordinate,',') === false) {
    		$this->_selectedCell = $pCoordinate;
    	} else {
    		throw new Exception('Selected cell can not be set on a range of cells.');
    	}
    	return $this;
    }

    /**
     * Temporary method used by style supervisor. Will be removed
     *
     * @param 	string		$pCell		Cell (i.e. A1)
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function setXSelectedCells($pCoordinate = 'A1:A1')
    {
		// Uppercase coordinate
    	$pCoordinate = strtoupper($pCoordinate);

		// Convert 'A' to 'A:A'
		$pCoordinate = preg_replace('/^([A-Z]+)$/', '${1}:${1}', $pCoordinate);

		// Convert '1' to '1:1'
		$pCoordinate = preg_replace('/^([0-9]+)$/', '${1}:${1}', $pCoordinate);

		// Convert 'A:C' to 'A1:C1048576'
		$pCoordinate = preg_replace('/^([A-Z]+):([A-Z]+)$/', '${1}1:${2}1048576', $pCoordinate);

    	// Convert '1:3' to 'A1:XFD3'
		$pCoordinate = preg_replace('/^([0-9]+):([0-9]+)$/', 'A${1}:XFD${2}', $pCoordinate);

    	if (strpos($pCoordinate,':') !== false || strpos($pCoordinate,',') !== false) {
			list($first, ) = ExcelCell::splitRange($pCoordinate);
			$this->_xActiveCell = $first[0];
		} else {
			$this->_xActiveCell = $pCoordinate;
		}
		$this->_xSelectedCells = $pCoordinate;
    	return $this;
    }

    /**
     * Selected cell by using numeric cell coordinates
     *
     * @param 	int 	$pColumn	Numeric column coordinate of the cell
     * @param 	int 	$pRow		Numeric row coordinate of the cell
     * @throws 	Exception
     * @return ExcelWorksheet
     */
    public function setSelectedCellByColumnAndRow($pColumn = 0, $pRow = 0)
    {
    	return $this->setSelectedCell(ExcelCell::stringFromColumnIndex($pColumn) . $pRow);
    }

    /**
	 * Get right-to-left
	 *
	 * @return boolean
     */
    public function getRightToLeft() {
    	return $this->_rightToLeft;
    }

    /**
     * Set right-to-left
     *
     * @param boolean $value Right-to-left true/false
     * @return ExcelWorksheet
     */
    public function setRightToLeft($value = false) {
    	$this->_rightToLeft = $value;
    	return $this;
    }

    /**
     * Fill worksheet from values in array
     *
     * @param array $source	Source array
     * @param mixed $nullValue Value treated as "null"
     * @throws Exception
     * @return ExcelWorksheet
     */
    public function fromArray($source = null, $nullValue = null, $pCell = 'A1') {
    	if (is_array($source)) {
			// start coordinate
			list ($startColumn, $startRow) = ExcelCell::coordinateFromString($pCell);
			$startColumn = ExcelCell::columnIndexFromString($startColumn) - 1;

			// Loop through $source
			$currentRow = $startRow - 1;
			$rowData = null;
			foreach ($source as $rowData) {
				++$currentRow;

				$rowCount = count($rowData);
				for ($i = 0; $i < $rowCount; ++$i) {
					if ($rowData[$i] != $nullValue) {
						// Set cell value
						$this->setCellValue(
							ExcelCell::stringFromColumnIndex($i + $startColumn) . $currentRow, $rowData[$i]
						);
					}
				}
			}
    	} else {
    		throw new Exception("Parameter \$source should be an array.");
    	}
    	return $this;
    }

    /**
     * Create array from worksheet
     *
     * @param mixed $nullValue Value treated as "null"
     * @param boolean $calculateFormulas Should formulas be calculated?
     * @return array
     */
    public function toArray($nullValue = null, $calculateFormulas = true) {
    	// Returnvalue
    	$returnValue = array();

        // Garbage collect...
        $this->garbageCollect();

    	// Get worksheet dimension
    	$dimension = explode(':', $this->calculateWorksheetDimension());
    	$dimension[0] = ExcelCell::coordinateFromString($dimension[0]);
    	$dimension[0][0] = ExcelCell::columnIndexFromString($dimension[0][0]) - 1;
    	$dimension[1] = ExcelCell::coordinateFromString($dimension[1]);
    	$dimension[1][0] = ExcelCell::columnIndexFromString($dimension[1][0]) - 1;

    	// Loop through cells
    	for ($row = $dimension[0][1]; $row <= $dimension[1][1]; ++$row) {
    		for ($column = $dimension[0][0]; $column <= $dimension[1][0]; ++$column) {
    			// Cell exists?
    			if ($this->cellExistsByColumnAndRow($column, $row)) {
    				$cell = $this->getCellByColumnAndRow($column, $row);

    				if ($cell->getValue() instanceof ExcelRichText) {
    					$returnValue[$row][$column] = $cell->getValue()->getPlainText();
    				} else {
	    				if ($calculateFormulas) {
	    					$returnValue[$row][$column] = $cell->getCalculatedValue();
	    				} else {
	    					$returnValue[$row][$column] = $cell->getValue();
	    				}
    				}

					$style = $this->_parent->getCellXfByIndex($cell->getXfIndex());

    				$returnValue[$row][$column] = ExcelStyleNumberFormat::toFormattedString($returnValue[$row][$column], $style->getNumberFormat()->getFormatCode());
    			} else {
    				$returnValue[$row][$column] = $nullValue;
    			}
    		}
    	}

    	// Return
    	return $returnValue;
    }

	/**
	 * Get row iterator
	 *
	 * @return ExcelWorksheet_RowIterator
	 */
	public function getRowIterator() {
		return new ExcelWorksheet_RowIterator($this);
	}

    /**
     * Run PHPExcel garabage collector.
     *
     * @return ExcelWorksheet
     */
    public function garbageCollect() {
    	// Build a reference table from images
    	$imageCoordinates = array();
  		$iterator = $this->getDrawingCollection()->getIterator();
   		while ($iterator->valid()) {
   			$imageCoordinates[$iterator->current()->getCoordinates()] = true;

   			$iterator->next();
   		}

		// Lookup highest column and highest row if cells are cleaned
		$highestColumn = -1;
		$highestRow    = 1;

    	// Find cells that can be cleaned
    	foreach ($this->_cellCollection as $coordinate => $cell) {
    		// Can be cleaned?
    		$canBeCleaned = false;

    		// Empty value?
    		if (is_null($cell->getValue()) || (!is_object($cell->getValue()) && $cell->getValue() === '' && !$cell->hasHyperlink())) {
				// default style ?
				if ($cell->getXfIndex() == 0) {
					$canBeCleaned = true;
				}
    		}

    		// Referenced in image?
    		if (isset($imageCoordinates[$coordinate]) && $imageCoordinates[$coordinate] === true) {
    			$canBeCleaned = false;
    		}

    		// Clean?
    		if ($canBeCleaned) {
				// Remove the cell
    			unset($this->_cellCollection[$coordinate]);
    		} else {
				// Determine highest column and row
				if ($highestColumn < ExcelCell::columnIndexFromString($cell->getColumn())) {
					$highestColumn = ExcelCell::columnIndexFromString($cell->getColumn());
				}
				if ($cell->getRow() > $highestRow) {
					$highestRow = $cell->getRow();
				}
			}
    	}

        // Loop through column dimensions
        foreach ($this->_columnDimensions as $dimension) {
        	if ($highestColumn < ExcelCell::columnIndexFromString($dimension->getColumnIndex())) {
        		$highestColumn = ExcelCell::columnIndexFromString($dimension->getColumnIndex());
        	}
        }

        // Loop through row dimensions
        foreach ($this->_rowDimensions as $dimension) {
        	if ($highestRow < $dimension->getRowIndex()) {
        		$highestRow = $dimension->getRowIndex();
        	}
        }

		// Cache values
		if ($highestColumn < 0) {
			$this->_cachedHighestColumn = 'A';
		} else {
			$this->_cachedHighestColumn = ExcelCell::stringFromColumnIndex(--$highestColumn);
		}
		$this->_cachedHighestRow = $highestRow;

		// Return
    	return $this;
    }

	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */
	public function getHashCode() {
    	return md5(
    		  $this->_title
    		. $this->_autoFilter
    		. ($this->_protection->isProtectionEnabled() ? 't' : 'f')
    		//. $this->calculateWorksheetDimension()
    		. __CLASS__
    	);
    }

    /**
     * Extract worksheet title from range.
     *
     * Example: extractSheetTitle('test!A1') ==> 'A1'
     * Example: extractSheetTitle('test!A1', true) ==> array('test', 'A1');
     *
     * @param string $pRange	Range to extract title from
     * @param bool $returnRange	Return range? (see example)
     * @return mixed
     */
    public static function extractSheetTitle($pRange, $returnRange = false) {
    	// Sheet title included?
    	if (strpos($pRange, '!') === false) {
    		return '';
    	}

    	// Position of separator exclamation mark
		$sep = strrpos($pRange, '!');

		// Extract sheet title
		$reference[0] = substr($pRange, 0, $sep);
		$reference[1] = substr($pRange, $sep + 1);

    	// Strip possible enclosing single quotes
    	if (strpos($reference[0], '\'') === 0) {
    		$reference[0] = substr($reference[0], 1);
    	}
    	if (strrpos($reference[0], '\'') === strlen($reference[0]) - 1) {
    		$reference[0] = substr($reference[0], 0, strlen($reference[0]) - 1);
    	}

    	if ($returnRange) {
    		return $reference;
    	} else {
    		return $reference[1];
    	}
    }

	/**
	 * Get hyperlink
	 *
	 * @param string $pCellCoordinate	Cell coordinate to get hyperlink for
	 */
	public function getHyperlink($pCellCoordinate = 'A1')
	{
		// return hyperlink if we already have one
		if (isset($this->_hyperlinkCollection[$pCellCoordinate])) {
			return $this->_hyperlinkCollection[$pCellCoordinate];
		}

		// else create hyperlink
		$cell = $this->getCell($pCellCoordinate);
		$this->_hyperlinkCollection[$pCellCoordinate] = new ExcelCell_Hyperlink($cell);
		return $this->_hyperlinkCollection[$pCellCoordinate];
	}

	/**
	 * Set hyperlnk
	 *
	 * @param string $pCellCoordinate	Cell coordinate to insert hyperlink
	 * @param 	ExcelCell_Hyperlink	$pHyperlink
	 * @return ExcelWorksheet
	 */
	public function setHyperlink($pCellCoordinate = 'A1', ExcelCell_Hyperlink $pHyperlink = null)
	{
		if ($pHyperlink === null) {
			unset($this->_hyperlinkCollection[$pCellCoordinate]);
		} else {
			$this->_hyperlinkCollection[$pCellCoordinate] = $pHyperlink;
			$pHyperlink->setParent($this->getCell($pCellCoordinate));
		}
		return $this;
	}

	/**
	 * Hyperlink at a specific coordinate exists?
	 *
	 * @param string $pCellCoordinate
	 * @return boolean
	 */
	public function hyperlinkExists($pCoordinate = 'A1')
	{
		return isset($this->_hyperlinkCollection[$pCoordinate]);
	}

	/**
	 * Get collection of hyperlinks
	 *
	 * @return ExcelCell_Hyperlink[]
	 */
	public function getHyperlinkCollection()
	{
		return $this->_hyperlinkCollection;
	}

	/**
	 * Get data validation
	 *
	 * @param string $pCellCoordinate	Cell coordinate to get data validation for
	 */
	public function getDataValidation($pCellCoordinate = 'A1')
	{
		// return data validation if we already have one
		if (isset($this->_dataValidationCollection[$pCellCoordinate])) {
			return $this->_dataValidationCollection[$pCellCoordinate];
		}

		// else create data validation
		$cell = $this->getCell($pCellCoordinate);
		$this->_dataValidationCollection[$pCellCoordinate] = new ExcelCell_DataValidation($cell);
		return $this->_dataValidationCollection[$pCellCoordinate];
	}

	/**
	 * Set data validation
	 *
	 * @param string $pCellCoordinate	Cell coordinate to insert data validation
	 * @param 	ExcelCell_DataValidation	$pDataValidation
	 * @return ExcelWorksheet
	 */
	public function setDataValidation($pCellCoordinate = 'A1', ExcelCell_DataValidation $pDataValidation = null)
	{
		if ($pDataValidation === null) {
			unset($this->_dataValidationCollection[$pCellCoordinate]);
		} else {
			$this->_dataValidationCollection[$pCellCoordinate] = $pDataValidation;
			$pDataValidation->setParent($this->getCell($pCellCoordinate));
		}
		return $this;
	}

	/**
	 * Data validation at a specific coordinate exists?
	 *
	 * @param string $pCellCoordinate
	 * @return boolean
	 */
	public function dataValidationExists($pCoordinate = 'A1')
	{
		return isset($this->_dataValidationCollection[$pCoordinate]);
	}

	/**
	 * Get collection of data validations
	 *
	 * @return ExcelCell_DataValidation[]
	 */
	public function getDataValidationCollection()
	{
		return $this->_dataValidationCollection;
	}

	/**
	 * Get tab color
	 *
	 * @return ExcelStyle_Color
	 */
	public function getTabColor()
	{
		if (is_null($this->_tabColor))
			$this->_tabColor = new ExcelStyle_Color();

		return $this->_tabColor;
	}

	/**
	 * Reset tab color
	 *
	 * @return ExcelWorksheet
	 */
	public function resetTabColor()
	{
		$this->_tabColor = null;
		unset($this->_tabColor);

		return $this;
	}

	/**
	 * Tab color set?
	 *
	 * @return boolean
	 */
	public function isTabColorSet()
	{
		return !is_null($this->_tabColor);
	}

	/**
	 * Copy worksheet (!= clone!)
	 *
	 * @return ExcelWorksheet
	 */
	public function copy() {
		$copied = clone $this;

		return $copied;
	}

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		foreach ($this as $key => $val) {
			if ($key == '_parent') {
				continue;
			}

			if (is_object($val) || (is_array($val))) {
				$this->{$key} = unserialize(serialize($val));
			}
		}
	}
}

/**
 * ExcelWorksheetIterator
 * 
 * Used to iterate worksheets in PHPExcel
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheetIterator extends IteratorIterator
{
    /**
     * Spreadsheet to iterate
     *
     * @var PHPExcel
     */
    private $_subject;
    
    /**
     * Current iterator position
     *
     * @var int
     */
    private $_position = 0;
    
    /**
     * Create a new worksheet iterator
     *
     * @param Workbook 		$subject
     */
    public function __construct($subject = null)
    {
        // Set subject
        $this->_subject = $subject;
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        unset($this->_subject);
    }
    
    /**
     * Rewind iterator
     */
    public function rewind()
    {
        $this->_position = 0;
    }
    
    /**
     * Current ExcelWorksheet
     *
     * @return ExcelWorksheet
     */
    public function current()
    {
        return $this->_subject->getSheet($this->_position);
    }
    
    /**
     * Current key
     *
     * @return int
     */
    public function key()
    {
        return $this->_position;
    }
    
    /**
     * Next value
     */
    public function next()
    {
        ++$this->_position;
    }
    
    /**
     * More ExcelWorksheet instances available?
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_position < $this->_subject->getSheetCount();
    }
}
?>