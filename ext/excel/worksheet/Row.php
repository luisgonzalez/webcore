<?php
/**
 * ExcelWorksheet_Row
 * 
 * Represents a row in ExcelWorksheet, used by ExcelWorksheet_RowIterator
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_Row extends ObjectBase
{
    /**
     * ExcelWorksheet
     *
     * @var ExcelWorksheet
     */
    private $_parent;
    private $_rowIndex = 0;
    
    /**
     * Create a new row
     *
     * @param ExcelWorksheet 		$parent
     * @param int						$rowIndex
     */
    public function __construct($parent = null, $rowIndex = 1)
    {
        // Set parent and row index
        $this->_parent   = $parent;
        $this->_rowIndex = $rowIndex;
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        unset($this->_parent);
    }
    
    /**
     * Get row index
     *
     * @return int
     */
    public function getRowIndex()
    {
        return $this->_rowIndex;
    }
    
    /**
     * Get cell iterator
     *
     * @return ExcelWorksheet_CellIterator
     */
    public function getCellIterator()
    {
        return new ExcelWorksheet_CellIterator($this->_parent, $this->_rowIndex);
    }
}

/**
 * ExcelWorksheet_RowDimension
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_RowDimension extends ObjectBase
{
    /**
	 * Row index
	 *
	 * @var int
	 */
	private $_rowIndex;
	
	/**
	 * Row height (in pt)
	 *
	 * When this is set to a negative value, the row height should be ignored by IWriter
	 *
	 * @var double
	 */
	private $_rowHeight;
	
	/**
	 * Visible?
	 *
	 * @var bool
	 */
	private $_visible;
	
	/**
	 * Outline level
	 *
	 * @var int
	 */
	private $_outlineLevel = 0;
	
	/**
	 * Collapsed
	 *
	 * @var bool
	 */
	private $_collapsed;

	/**
	 * Index to cellXf. Null value means row has no explicit cellXf format.
	 *
	 * @var int|null
	 */
	private $_xfIndex;

    /**
     * Create a new ExcelWorksheet_RowDimension
     *
     * @param int $pIndex Numeric row index
     */
    public function __construct($pIndex = 0)
    {
    	// Initialise values
    	$this->_rowIndex		= $pIndex;
    	$this->_rowHeight		= -1;
    	$this->_visible			= true;
    	$this->_outlineLevel	= 0;
    	$this->_collapsed		= false;

		// set row dimension as unformatted by default
		$this->_xfIndex = null;
    }
    
    /**
     * Get Row Index
     *
     * @return int
     */
    public function getRowIndex() {
    	return $this->_rowIndex;
    }
    
    /**
     * Set Row Index
     *
     * @param int $pValue
     * @return ExcelWorksheet_RowDimension
     */
    public function setRowIndex($pValue) {
    	$this->_rowIndex = $pValue;
    	return $this;
    }
    
    /**
     * Get Row Height
     *
     * @return double
     */
    public function getRowHeight() {
    	return $this->_rowHeight;
    }
    
    /**
     * Set Row Height
     *
     * @param double $pValue
     * @return ExcelWorksheet_RowDimension
     */
    public function setRowHeight($pValue = -1) {
    	$this->_rowHeight = $pValue;
    	return $this;
    }
    
    /**
     * Get Visible
     *
     * @return bool
     */
    public function getVisible() {
    	return $this->_visible;
    }
    
    /**
     * Set Visible
     *
     * @param bool $pValue
     * @return ExcelWorksheet_RowDimension
     */
    public function setVisible($pValue = true) {
    	$this->_visible = $pValue;
    	return $this;
    }
    
    /**
     * Get Outline Level
     *
     * @return int
     */
    public function getOutlineLevel() {
    	return $this->_outlineLevel;
    }
    
    /**
     * Set Outline Level
     *
     * Value must be between 0 and 7
     *
     * @param int $pValue
     * @throws Exception
     * @return ExcelWorksheet_RowDimension
     */
    public function setOutlineLevel($pValue) {
    	if ($pValue < 0 || $pValue > 7) {
    		throw new Exception("Outline level must range between 0 and 7.");
    	}
    	
    	$this->_outlineLevel = $pValue;
    	return $this;
    }
    
    /**
     * Get Collapsed
     *
     * @return bool
     */
    public function getCollapsed() {
    	return $this->_collapsed;
    }
    
    /**
     * Set Collapsed
     *
     * @param bool $pValue
     * @return ExcelWorksheet_RowDimension
     */
    public function setCollapsed($pValue = true) {
    	$this->_collapsed = $pValue;
    	return $this;
    }

	/**
	 * Get index to cellXf
	 *
	 * @return int
	 */
	public function getXfIndex()
	{
		return $this->_xfIndex;
	}

	/**
	 * Set index to cellXf
	 *
	 * @param int $pValue
	 * @return ExcelWorksheet_RowDimension
	 */
	public function setXfIndex($pValue = 0)
	{
		$this->_xfIndex = $pValue;
		return $this;
	}
}

/**
 * ExcelWorksheet_RowIterator
 * 
 * Used to iterate rows in a ExcelWorksheet
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_RowIterator extends IteratorIterator
{
    /**
     * ExcelWorksheet to iterate
     *
     * @var ExcelWorksheet
     */
    private $_subject;
    
    /**
     * Current iterator position
     *
     * @var int
     */
    private $_position = 0;
    
    /**
     * Create a new row iterator
     *
     * @param ExcelWorksheet 		$subject
     */
    public function __construct(ExcelWorksheet $subject = null)
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
        $this->_position = 1;
    }
    
    /**
     * Current ExcelWorksheet_Row
     *
     * @return ExcelWorksheet_Row
     */
    public function current()
    {
        return new ExcelWorksheet_Row($this->_subject, $this->_position);
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
     * More ExcelWorksheet_Row instances available?
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_position <= $this->_subject->getHighestRow();
    }
}

/**
 * ExcelWorksheet_CellIterator
 * 
 * Used to iterate rows in a ExcelWorksheet
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_CellIterator extends IteratorIterator
{
    /**
     * ExcelWorksheet to iterate
     *
     * @var ExcelWorksheet
     */
    private $_subject;
    
    /**
     * Row index
     *
     * @var int
     */
    private $_rowIndex;
    
    /**
     * Current iterator position
     *
     * @var int
     */
    private $_position = 0;
    
    /**
     * Loop only existing cells
     *
     * @var boolean
     */
    private $_onlyExistingCells = true;
    
    /**
     * Create a new cell iterator
     *
     * @param ExcelWorksheet 		$subject
     * @param int						$rowIndex
     */
    public function __construct(ExcelWorksheet $subject = null, $rowIndex = 1)
    {
        // Set subject and row index
        $this->_subject  = $subject;
        $this->_rowIndex = $rowIndex;
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
     * Current ExcelCell
     *
     * @return ExcelCell
     */
    public function current()
    {
        $cellExists = $this->_subject->cellExistsByColumnAndRow($this->_position, $this->_rowIndex);
        if (($this->_onlyExistingCells && $cellExists) || (!$this->_onlyExistingCells))
        {
            return $this->_subject->getCellByColumnAndRow($this->_position, $this->_rowIndex);
        }
        else if ($this->_onlyExistingCells && !$cellExists)
        {
            // Loop untill we find one
            while ($this->valid())
            {
                $this->next();
                if ($this->_subject->cellExistsByColumnAndRow($this->_position, $this->_rowIndex))
                {
                    return $this->_subject->getCellByColumnAndRow($this->_position, $this->_rowIndex);
                }
            }
        }
        
        return null;
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
     * More ExcelCell instances available?
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_position < ExcelCell::columnIndexFromString($this->_subject->getHighestColumn());
    }
    
    /**
     * Get loop only existing cells
     *
     * @return boolean
     */
    public function getIterateOnlyExistingCells()
    {
        return $this->_onlyExistingCells;
    }
    
    /**
     * Set loop only existing cells
     *
     * @return boolean
     */
    public function setIterateOnlyExistingCells($value = true)
    {
        $this->_onlyExistingCells = $value;
    }
}

/**
 * ExcelWorksheet_ColumnDimension
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_ColumnDimension extends ObjectBase
{
    /**
	 * Column index
	 *
	 * @var int
	 */
	private $_columnIndex;
	
	/**
	 * Column width
	 *
	 * When this is set to a negative value, the column width should be ignored by IWriter
	 *
	 * @var double
	 */
	private $_width;
	
	/**
	 * Auto size?
	 *
	 * @var bool
	 */
	private $_autoSize;
	
	/**
	 * Visible?
	 *
	 * @var bool
	 */
	private $_visible;
	
	/**
	 * Outline level
	 *
	 * @var int
	 */
	private $_outlineLevel = 0;
	
	/**
	 * Collapsed
	 *
	 * @var bool
	 */
	private $_collapsed;

	/**
	 * Index to cellXf
	 *
	 * @var int
	 */
	private $_xfIndex;

    /**
     * Create a new ExcelWorksheet_ColumnDimension
     *
     * @param string $pIndex Character column index
     */
    public function __construct($pIndex = 'A')
    {
    	// Initialise values
    	$this->_columnIndex		= $pIndex;
    	$this->_width			= -1;
    	$this->_autoSize		= false;
    	$this->_visible			= true;
    	$this->_outlineLevel	= 0;
    	$this->_collapsed		= false;

		// set default index to cellXf
		$this->_xfIndex = 0;
    }
    
    /**
     * Get ColumnIndex
     *
     * @return string
     */
    public function getColumnIndex() {
    	return $this->_columnIndex;
    }
    
    /**
     * Set ColumnIndex
     *
     * @param string $pValue
     * @return ExcelWorksheet_ColumnDimension
     */
    public function setColumnIndex($pValue) {
    	$this->_columnIndex = $pValue;
    	return $this;
    }
    
    /**
     * Get Width
     *
     * @return double
     */
    public function getWidth() {
    	return $this->_width;
    }
    
    /**
     * Set Width
     *
     * @param double $pValue
     * @return ExcelWorksheet_ColumnDimension
     */
    public function setWidth($pValue = -1) {
    	$this->_width = $pValue;
    	return $this;
    }
    
    /**
     * Get Auto Size
     *
     * @return bool
     */
    public function getAutoSize() {
    	return $this->_autoSize;
    }
    
    /**
     * Set Auto Size
     *
     * @param bool $pValue
     * @return ExcelWorksheet_ColumnDimension
     */
    public function setAutoSize($pValue = false) {
    	$this->_autoSize = $pValue;
    	return $this;
    }
    
    /**
     * Get Visible
     *
     * @return bool
     */
    public function getVisible() {
    	return $this->_visible;
    }
    
    /**
     * Set Visible
     *
     * @param bool $pValue
     * @return ExcelWorksheet_ColumnDimension
     */
    public function setVisible($pValue = true) {
    	$this->_visible = $pValue;
    	return $this;
    }
    
    /**
     * Get Outline Level
     *
     * @return int
     */
    public function getOutlineLevel() {
    	return $this->_outlineLevel;
    }
    
    /**
     * Set Outline Level
     *
     * Value must be between 0 and 7
     *
     * @param int $pValue
     * @throws Exception
     * @return ExcelWorksheet_ColumnDimension
     */
    public function setOutlineLevel($pValue) {
    	if ($pValue < 0 || $pValue > 7) {
    		throw new Exception("Outline level must range between 0 and 7.");
    	}
    	
    	$this->_outlineLevel = $pValue;
    	return $this;
    }
    
    /**
     * Get Collapsed
     *
     * @return bool
     */
    public function getCollapsed() {
    	return $this->_collapsed;
    }
    
    /**
     * Set Collapsed
     *
     * @param bool $pValue
     * @return ExcelWorksheet_ColumnDimension
     */
    public function setCollapsed($pValue = true) {
    	$this->_collapsed = $pValue;
    	return $this;
    }
        
	/**
	 * Get index to cellXf
	 *
	 * @return int
	 */
	public function getXfIndex()
	{
		return $this->_xfIndex;
	}

	/**
	 * Set index to cellXf
	 *
	 * @param int $pValue
	 * @return ExcelWorksheet_ColumnDimension
	 */
	public function setXfIndex($pValue = 0)
	{
		$this->_xfIndex = $pValue;
		return $this;
	}
}

/**
 * ExcelWorksheet_Protection
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_Protection extends ObjectBase
{
    /**
     * Sheet
     *
     * @var boolean
     */
    private $_sheet;
    
    /**
     * Objects
     *
     * @var boolean
     */
    private $_objects;
    
    /**
     * Scenarios
     *
     * @var boolean
     */
    private $_scenarios;
    
    /**
     * Format cells
     *
     * @var boolean
     */
    private $_formatCells;
    
    /**
     * Format columns
     *
     * @var boolean
     */
    private $_formatColumns;
    
    /**
     * Format rows
     *
     * @var boolean
     */
    private $_formatRows;
    
    /**
     * Insert columns
     *
     * @var boolean
     */
    private $_insertColumns;
    
    /**
     * Insert rows
     *
     * @var boolean
     */
    private $_insertRows;
    
    /**
     * Insert hyperlinks
     *
     * @var boolean
     */
    private $_insertHyperlinks;
    
    /**
     * Delete columns
     *
     * @var boolean
     */
    private $_deleteColumns;
    
    /**
     * Delete rows
     *
     * @var boolean
     */
    private $_deleteRows;
    
    /**
     * Select locked cells
     *
     * @var boolean
     */
    private $_selectLockedCells;
    
    /**
     * Sort
     *
     * @var boolean
     */
    private $_sort;
    
    /**
     * AutoFilter
     *
     * @var boolean
     */
    private $_autoFilter;
    
    /**
     * Pivot tables
     *
     * @var boolean
     */
    private $_pivotTables;
    
    /**
     * Select unlocked cells
     *
     * @var boolean
     */
    private $_selectUnlockedCells;
    
    /**
     * Password
     *
     * @var string
     */
    private $_password;
    
    /**
     * Create a new ExcelWorksheet_Protection
     */
    public function __construct()
    {
        // Initialise values
        $this->_sheet               = false;
        $this->_objects             = false;
        $this->_scenarios           = false;
        $this->_formatCells         = false;
        $this->_formatColumns       = false;
        $this->_formatRows          = false;
        $this->_insertColumns       = false;
        $this->_insertRows          = false;
        $this->_insertHyperlinks    = false;
        $this->_deleteColumns       = false;
        $this->_deleteRows          = false;
        $this->_selectLockedCells   = false;
        $this->_sort                = false;
        $this->_autoFilter          = false;
        $this->_pivotTables         = false;
        $this->_selectUnlockedCells = false;
        $this->_password            = '';
    }
    
    /**
     * Is some sort of protection enabled?
     *
     * @return boolean
     */
    function isProtectionEnabled()
    {
        return $this->_sheet || $this->_objects || $this->_scenarios || $this->_formatCells || $this->_formatColumns || $this->_formatRows || $this->_insertColumns || $this->_insertRows || $this->_insertHyperlinks || $this->_deleteColumns || $this->_deleteRows || $this->_selectLockedCells || $this->_sort || $this->_autoFilter || $this->_pivotTables || $this->_selectUnlockedCells;
    }
    
    /**
     * Get Sheet
     *
     * @return boolean
     */
    function getSheet()
    {
        return $this->_sheet;
    }
    
    /**
     * Set Sheet
     *
     * @param boolean $pValue
     */
    function setSheet($pValue = false)
    {
        $this->_sheet = $pValue;
    }
    
    /**
     * Get Objects
     *
     * @return boolean
     */
    function getObjects()
    {
        return $this->_objects;
    }
    
    /**
     * Set Objects
     *
     * @param boolean $pValue
     */
    function setObjects($pValue = false)
    {
        $this->_objects = $pValue;
    }
    
    /**
     * Get Scenarios
     *
     * @return boolean
     */
    function getScenarios()
    {
        return $this->_scenarios;
    }
    
    /**
     * Set Scenarios
     *
     * @param boolean $pValue
     */
    function setScenarios($pValue = false)
    {
        $this->_scenarios = $pValue;
    }
    
    /**
     * Get FormatCells
     *
     * @return boolean
     */
    function getFormatCells()
    {
        return $this->_formatCells;
    }
    
    /**
     * Set FormatCells
     *
     * @param boolean $pValue
     */
    function setFormatCells($pValue = false)
    {
        $this->_formatCells = $pValue;
    }
    
    /**
     * Get FormatColumns
     *
     * @return boolean
     */
    function getFormatColumns()
    {
        return $this->_formatColumns;
    }
    
    /**
     * Set FormatColumns
     *
     * @param boolean $pValue
     */
    function setFormatColumns($pValue = false)
    {
        $this->_formatColumns = $pValue;
    }
    
    /**
     * Get FormatRows
     *
     * @return boolean
     */
    function getFormatRows()
    {
        return $this->_formatRows;
    }
    
    /**
     * Set FormatRows
     *
     * @param boolean $pValue
     */
    function setFormatRows($pValue = false)
    {
        $this->_formatRows = $pValue;
    }
    
    /**
     * Get InsertColumns
     *
     * @return boolean
     */
    function getInsertColumns()
    {
        return $this->_insertColumns;
    }
    
    /**
     * Set InsertColumns
     *
     * @param boolean $pValue
     */
    function setInsertColumns($pValue = false)
    {
        $this->_insertColumns = $pValue;
    }
    
    /**
     * Get InsertRows
     *
     * @return boolean
     */
    function getInsertRows()
    {
        return $this->_insertRows;
    }
    
    /**
     * Set InsertRows
     *
     * @param boolean $pValue
     */
    function setInsertRows($pValue = false)
    {
        $this->_insertRows = $pValue;
    }
    
    /**
     * Get InsertHyperlinks
     *
     * @return boolean
     */
    function getInsertHyperlinks()
    {
        return $this->_insertHyperlinks;
    }
    
    /**
     * Set InsertHyperlinks
     *
     * @param boolean $pValue
     */
    function setInsertHyperlinks($pValue = false)
    {
        $this->_insertHyperlinks = $pValue;
    }
    
    /**
     * Get DeleteColumns
     *
     * @return boolean
     */
    function getDeleteColumns()
    {
        return $this->_deleteColumns;
    }
    
    /**
     * Set DeleteColumns
     *
     * @param boolean $pValue
     */
    function setDeleteColumns($pValue = false)
    {
        $this->_deleteColumns = $pValue;
    }
    
    /**
     * Get DeleteRows
     *
     * @return boolean
     */
    function getDeleteRows()
    {
        return $this->_deleteRows;
    }
    
    /**
     * Set DeleteRows
     *
     * @param boolean $pValue
     */
    function setDeleteRows($pValue = false)
    {
        $this->_deleteRows = $pValue;
    }
    
    /**
     * Get SelectLockedCells
     *
     * @return boolean
     */
    function getSelectLockedCells()
    {
        return $this->_selectLockedCells;
    }
    
    /**
     * Set SelectLockedCells
     *
     * @param boolean $pValue
     */
    function setSelectLockedCells($pValue = false)
    {
        $this->_selectLockedCells = $pValue;
    }
    
    /**
     * Get Sort
     *
     * @return boolean
     */
    function getSort()
    {
        return $this->_sort;
    }
    
    /**
     * Set Sort
     *
     * @param boolean $pValue
     */
    function setSort($pValue = false)
    {
        $this->_sort = $pValue;
    }
    
    /**
     * Get AutoFilter
     *
     * @return boolean
     */
    function getAutoFilter()
    {
        return $this->_autoFilter;
    }
    
    /**
     * Set AutoFilter
     *
     * @param boolean $pValue
     */
    function setAutoFilter($pValue = false)
    {
        $this->_autoFilter = $pValue;
    }
    
    /**
     * Get PivotTables
     *
     * @return boolean
     */
    function getPivotTables()
    {
        return $this->_pivotTables;
    }
    
    /**
     * Set PivotTables
     *
     * @param boolean $pValue
     */
    function setPivotTables($pValue = false)
    {
        $this->_pivotTables = $pValue;
    }
    
    /**
     * Get SelectUnlockedCells
     *
     * @return boolean
     */
    function getSelectUnlockedCells()
    {
        return $this->_selectUnlockedCells;
    }
    
    /**
     * Set SelectUnlockedCells
     *
     * @param boolean $pValue
     */
    function setSelectUnlockedCells($pValue = false)
    {
        $this->_selectUnlockedCells = $pValue;
    }
    
    /**
     * Get Password (hashed)
     *
     * @return string
     */
    function getPassword()
    {
        return $this->_password;
    }
    
    /**
     * Set Password
     *
     * @param string 	$pValue
     * @param boolean 	$pAlreadyHashed If the password has already been hashed, set this to true
     */
    function setPassword($pValue = '', $pAlreadyHashed = false)
    {
        if (!$pAlreadyHashed)
        {
            $pValue = ExcelShared_PasswordHasher::hashPassword($pValue);
        }
        $this->_password = $pValue;
    }
}
?>