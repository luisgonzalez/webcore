<?php
/**
 * ExcelCell_DataValidation
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCell_DataValidation extends ComparableBase
{
    /* Data validation types */
    const TYPE_NONE = 'none';
    const TYPE_CUSTOM = 'custom';
    const TYPE_DATE = 'date';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_LIST = 'list';
    const TYPE_TEXTLENGTH = 'textLength';
    const TYPE_TIME = 'time';
    const TYPE_WHOLE = 'whole';
    
    /* Data validation error styles */
    const STYLE_STOP = 'stop';
    const STYLE_WARNING = 'warning';
    const STYLE_INFORMATION = 'information';
    
    /* Data validation operators */
    const OPERATOR_BETWEEN = 'between';
    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_GREATERTHAN = 'greaterThan';
    const OPERATOR_GREATERTHANOREQUAL = 'greaterThanOrEqual';
    const OPERATOR_LESSTHAN = 'lessThan';
    const OPERATOR_LESSTHANOREQUAL = 'lessThanOrEqual';
    const OPERATOR_NOTBETWEEN = 'notBetween';
    const OPERATOR_NOTEQUAL = 'notEqual';
    
    private $_formula1;
    private $_formula2;
    private $_type = ExcelCell_DataValidation::TYPE_NONE;
    
    /**
     * Error style
     *
     * @var string
     */
    private $_errorStyle = ExcelCell_DataValidation::STYLE_STOP;
    
    /**
     * Operator
     *
     * @var string
     */
    private $_operator;
    
    /**
     * Allow Blank
     *
     * @var boolean
     */
    private $_allowBlank;
    
    /**
     * Show DropDown
     *
     * @var boolean
     */
    private $_showDropDown;
    
    /**
     * Show InputMessage
     *
     * @var boolean
     */
    private $_showInputMessage;
    
    /**
     * Show ErrorMessage
     *
     * @var boolean
     */
    private $_showErrorMessage;
    
    /**
     * Error title
     *
     * @var string
     */
    private $_errorTitle;
    
    /**
     * Error
     *
     * @var string
     */
    private $_error;
    
    /**
     * Prompt title
     *
     * @var string
     */
    private $_promptTitle;
    
    /**
     * Prompt
     *
     * @var string
     */
    private $_prompt;
    
    /**
     * Parent cell
     *
     * @var ExcelCell
     */
    private $_parent;
    
    /**
     * Create a new ExcelCell_DataValidation
     *
     * @param 	ExcelCell		$pCell	Parent cell
     * @throws	Exception
     */
    public function __construct($pCell = null)
    {
        // Initialise member variables
        $this->_formula1         = '';
        $this->_formula2         = '';
        $this->_type             = ExcelCell_DataValidation::TYPE_NONE;
        $this->_errorStyle       = ExcelCell_DataValidation::STYLE_STOP;
        $this->_operator         = '';
        $this->_allowBlank       = false;
        $this->_showDropDown     = false;
        $this->_showInputMessage = false;
        $this->_showErrorMessage = false;
        $this->_errorTitle       = '';
        $this->_error            = '';
        $this->_promptTitle      = '';
        $this->_prompt           = '';
        
        // Set cell
        $this->_parent = $pCell;
    }
    
    /**
     * Get Formula 1
     *
     * @return string
     */
    public function getFormula1()
    {
        return $this->_formula1;
    }
    
    /**
     * Set Formula 1
     *
     * @param	string	$value
     */
    public function setFormula1($value = '')
    {
        $this->_formula1 = $value;
    }
    
    /**
     * Get Formula 2
     *
     * @return string
     */
    public function getFormula2()
    {
        return $this->_formula2;
    }
    
    /**
     * Set Formula 2
     *
     * @param	string	$value
     */
    public function setFormula2($value = '')
    {
        $this->_formula2 = $value;
    }
    
    /**
     * Get Type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * Set Type
     *
     * @param	string	$value
     */
    public function setType($value = ExcelCell_DataValidation::TYPE_NONE)
    {
        $this->_type = $value;
    }
    
    /**
     * Get Error style
     *
     * @return string
     */
    public function getErrorStyle()
    {
        return $this->_errorStyle;
    }
    
    /**
     * Set Error style
     *
     * @param	string	$value
     */
    public function setErrorStyle($value = ExcelCell_DataValidation::STYLE_STOP)
    {
        $this->_errorStyle = $value;
    }
    
    /**
     * Get Operator
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->_operator;
    }
    
    /**
     * Set Operator
     *
     * @param	string	$value
     */
    public function setOperator($value = '')
    {
        $this->_operator = $value;
    }
    
    /**
     * Get Allow Blank
     *
     * @return boolean
     */
    public function getAllowBlank()
    {
        return $this->_allowBlank;
    }
    
    /**
     * Set Allow Blank
     *
     * @param	boolean	$value
     */
    public function setAllowBlank($value = false)
    {
        $this->_allowBlank = $value;
    }
    
    /**
     * Get Show DropDown
     *
     * @return boolean
     */
    public function getShowDropDown()
    {
        return $this->_showDropDown;
    }
    
    /**
     * Set Show DropDown
     *
     * @param	boolean	$value
     */
    public function setShowDropDown($value = false)
    {
        $this->_showDropDown = $value;
    }
    
    /**
     * Get Show InputMessage
     *
     * @return boolean
     */
    public function getShowInputMessage()
    {
        return $this->_showInputMessage;
    }
    
    /**
     * Set Show InputMessage
     *
     * @param	boolean	$value
     */
    public function setShowInputMessage($value = false)
    {
        $this->_showInputMessage = $value;
    }
    
    /**
     * Get Show ErrorMessage
     *
     * @return boolean
     */
    public function getShowErrorMessage()
    {
        return $this->_showErrorMessage;
    }
    
    /**
     * Set Show ErrorMessage
     *
     * @param	boolean	$value
     */
    public function setShowErrorMessage($value = false)
    {
        $this->_showErrorMessage = $value;
    }
    
    /**
     * Get Error title
     *
     * @return string
     */
    public function getErrorTitle()
    {
        return $this->_errorTitle;
    }
    
    /**
     * Set Error title
     *
     * @param	string	$value
     */
    public function setErrorTitle($value = '')
    {
        $this->_errorTitle = $value;
    }
    
    /**
     * Get Error
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
    
    /**
     * Set Error
     *
     * @param	string	$value
     */
    public function setError($value = '')
    {
        $this->_error = $value;
    }
    
    /**
     * Get Prompt title
     *
     * @return string
     */
    public function getPromptTitle()
    {
        return $this->_promptTitle;
    }
    
    /**
     * Set Prompt title
     *
     * @param	string	$value
     */
    public function setPromptTitle($value = '')
    {
        $this->_promptTitle = $value;
    }
    
    /**
     * Get Prompt
     *
     * @return string
     */
    public function getPrompt()
    {
        return $this->_prompt;
    }
    
    /**
     * Set Prompt
     *
     * @param	string	$value
     */
    public function setPrompt($value = '')
    {
        $this->_prompt = $value;
    }
    
    /**
     * Get parent
     *
     * @return ExcelCell
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * Set Parent
     *
     * @param	ExcelCell	$value
     */
    public function setParent($value = null)
    {
        $this->_parent = $value;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_formula1 . $this->_formula2 . $this->_type = ExcelCell_DataValidation::TYPE_NONE . $this->_errorStyle = ExcelCell_DataValidation::STYLE_STOP . $this->_operator . ($this->_allowBlank ? 't' : 'f') . ($this->_showDropDown ? 't' : 'f') . ($this->_showInputMessage ? 't' : 'f') . ($this->_showErrorMessage ? 't' : 'f') . $this->_errorTitle . $this->_error . $this->_promptTitle . $this->_prompt . $this->_parent->getCoordinate() . __CLASS__);
    }
}

/**
 * ExcelCell_DataType
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCell_DataType extends ObjectBase
{
    /* Data types */
    const TYPE_STRING = 's';
    const TYPE_FORMULA = 'f';
    const TYPE_NUMERIC = 'n';
    const TYPE_BOOL = 'b';
    const TYPE_NULL = 's';
    const TYPE_INLINE = 'inlineStr';
    const TYPE_ERROR = 'e';
    
    /**
     * List of error codes
     *
     * @var array
     */
    private static $_errorCodes = array('#NULL!' => 0, '#DIV/0!' => 1, '#VALUE!' => 2, '#REF!' => 3, '#NAME?' => 4, '#NUM!' => 5, '#N/A' => 6);
    
    /**
     * Get list of error codes
     *
     * @return array
     */
    public static function getErrorCodes()
    {
        return self::$_errorCodes;
    }
    
    /**
     * DataType for value
     *
     * @deprecated Replaced by ExcelCell_IValueBinder infrastructure
     * @param	mixed 	$pValue
     * @return 	int
     */
    public static function dataTypeForValue($pValue = null)
    {
        return ExcelCell_DefaultValueBinder::dataTypeForValue($pValue);
    }
}

/**
 * IExcelCellValueBinder
 *
 * @package    WebCore
 * @subpackage Excel
 */
interface IExcelCellValueBinder extends IObject
{
    /**
     * Bind value to a cell
     *
     * @param ExcelCell $cell	Cell to bind value to
     * @param mixed $value			Value to bind in cell
     * @return boolean
     */
    public function bindValue($cell, $value = null);
}

/**
 * ExcelCell_DefaultValueBinder
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCell_DefaultValueBinder extends ObjectBase implements IExcelCellValueBinder
{
    /**
     * Bind value to a cell
     *
     * @param ExcelCell $cell	Cell to bind value to
     * @param mixed $value			Value to bind in cell
     * @return boolean
     */
    public function bindValue($cell, $value = null)
    {
        // Set value explicit
        $cell->setValueExplicit($value, ExcelCell_DataType::dataTypeForValue($value));
        
        // Done!
        return true;
    }
    
    /**
     * DataType for value
     *
     * @param	mixed 	$pValue
     * @return 	int
     */
    public static function dataTypeForValue($pValue = null)
    {
        // Match the value against a few data types
        if (is_null($pValue))
        {
            return ExcelCell_DataType::TYPE_NULL;
        }
        elseif ($pValue === '')
        {
            return ExcelCell_DataType::TYPE_STRING;
        }
        elseif ($pValue instanceof ExcelRichText)
        {
            return ExcelCell_DataType::TYPE_STRING;
        }
        elseif ($pValue{0} === '=')
        {
            return ExcelCell_DataType::TYPE_FORMULA;
        }
        elseif (is_bool($pValue))
        {
            return ExcelCell_DataType::TYPE_BOOL;
        }
        elseif (preg_match('/^\-?[0-9]*\.?[0-9]*$/', $pValue))
        {
            return ExcelCell_DataType::TYPE_NUMERIC;
        }
        elseif (array_key_exists($pValue, ExcelCell_DataType::getErrorCodes()))
        {
            return ExcelCell_DataType::TYPE_ERROR;
        }
        else
        {
            return ExcelCell_DataType::TYPE_STRING;
        }
    }
}

/**
 * ExcelCell_AdvancedValueBinder
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCell_AdvancedValueBinder extends ExcelCell_DefaultValueBinder implements IExcelCellValueBinder
{
    /**
     * Bind value to a cell
     *
     * @param ExcelCell $cell	Cell to bind value to
     * @param mixed $value			Value to bind in cell
     * @return boolean
     */
    public function bindValue($cell, $value = null)
    {
        // Find out data type
        $dataType = parent::dataTypeForValue($value);
        
        // Style logic - strings
        if ($dataType === ExcelCell_DataType::TYPE_STRING && !$value instanceof ExcelRichText)
        {
            // Check for percentage
            if (preg_match('/^\-?[0-9]*\.?[0-9]*\s?\%$/', $value))
            {
                // Convert value to number
                $cell->setValueExplicit((float) str_replace('%', '', $value) / 100, ExcelCell_DataType::TYPE_NUMERIC);
                
                // Set style
                $cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat()->setFormatCode(ExcelStyleNumberFormat::FORMAT_PERCENTAGE);
                
                return true;
            }
            
            // Check for date
            if (strtotime($value) !== false)
            {
                // Convert value to Excel date
                $cell->setValueExplicit(ExcelShared_Date::PHPToExcel(strtotime($value)), ExcelCell_DataType::TYPE_NUMERIC);
                
                // Set style
                $cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat()->setFormatCode(ExcelStyleNumberFormat::FORMAT_DATE_YYYYMMDD2);
                
                return true;
            }
        }
        
        // Style logic - Numbers
        if ($dataType === ExcelCell_DataType::TYPE_NUMERIC)
        {
            // Leading zeroes?
            if (preg_match('/^\-?[0]+[0-9]*\.?[0-9]*$/', $value))
            {
                // Convert value to string
                $cell->setValueExplicit($value, ExcelCell_DataType::TYPE_STRING);
                
                // Set style
                $cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat()->setFormatCode(ExcelStyleNumberFormat::FORMAT_TEXT);
                
                return true;
            }
        }
        
        // Not bound yet? Use parent...
        return parent::bindValue($cell, $value);
    }
}

/**
 * ExcelCell_Hyperlink
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCell_Hyperlink extends ObjectBase
{
    /**
     * Cell representing the hyperlink
     *
     * @var ExcelCell
     */
    private $_cell;
    
    /**
     * URL to link the cell to
     *
     * @var string
     */
    private $_url;
    
    /**
     * Tooltip to display on the hyperlink
     *
     * @var string
     */
    private $_tooltip;
    
    /**
     * Create a new ExcelCell_Hyperlink
     *
     * @param 	ExcelCell		$pCell		Parent cell
     * @param 	string				$pUrl		Url to link the cell to
     * @param	string				$pTooltip	Tooltip to display on the hyperlink
     * @throws	Exception
     */
    public function __construct($pCell = null, $pUrl = '', $pTooltip = '')
    {
        // Initialise member variables
        $this->_url     = $pUrl;
        $this->_tooltip = $pTooltip;
        
        // Set cell
        $this->_parent = $pCell;
    }
    
    /**
     * Get URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }
    
    /**
     * Set URL
     *
     * @param	string	$value
     */
    public function setUrl($value = '')
    {
        $this->_url = $value;
    }
    
    /**
     * Get tooltip
     *
     * @return string
     */
    public function getTooltip()
    {
        return $this->_tooltip;
    }
    
    /**
     * Set tooltip
     *
     * @param	string	$value
     */
    public function setTooltip($value = '')
    {
        $this->_tooltip = $value;
    }
    
    /**
     * Is this hyperlink internal? (to another sheet)
     *
     * @return boolean
     */
    public function isInternal()
    {
        return strpos($this->_url, 'sheet://') !== false;
    }
    
    /**
     * Get parent
     *
     * @return ExcelCell
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * Set Parent
     *
     * @param	ExcelCell	$value
     */
    public function setParent($value = null)
    {
        $this->_parent = $value;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_url . $this->_tooltip . $this->_parent->getCoordinate() . __CLASS__);
    }
}

/**
 * ExcelCell
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCell extends ObjectBase
{
    /**
     * Value binder to use
     *
     * @var ExcelCell_IValueBinder
     */
    private static $_valueBinder = null;
    
    /**
     * Column of the cell
     *
     * @var string
     */
    private $_column;
    
    /**
     * Row of the cell
     *
     * @var int
     */
    private $_row;
    
    /**
     * Value of the cell
     *
     * @var mixed
     */
    private $_value;
    
    /**
     * Calculated value of the cell (used for caching)
     *
     * @var mixed
     */
    private $_calculatedValue = null;
    
    /**
     * Type of the cell data
     *
     * @var string
     */
    private $_dataType;
    
    /**
     * Data validation
     *
     * @var ExcelCell_DataValidation
     */
    private $_dataValidation;
    
    /**
     * Hyperlink
     *
     * @var ExcelCell_Hyperlink
     */
    private $_hyperlink;
    
    /**
     * Parent worksheet
     *
     * @var ExcelWorksheet
     */
    private $_parent;
    
    /**
	 * Index to cellXf
	 *
	 * @var int
	 */
	private $_xfIndex;
    
    /**
     * Create a new Cell
     *
     * @param 	string 				$pColumn
     * @param 	int 				$pRow
     * @param 	mixed 				$pValue
     * @param 	string 				$pDataType
     * @param 	ExcelWorksheet	$pSheet
     * @throws	Exception
     */
    public function __construct($pColumn = 'A', $pRow = 1, $pValue = null, $pDataType = null, ExcelWorksheet $pSheet = null)
    {
        // Set value binder?
        if (is_null(self::$_valueBinder))
            self::$_valueBinder = new ExcelCell_DefaultValueBinder();
        
        // Initialise cell coordinate
        $this->_column = strtoupper($pColumn);
        $this->_row    = $pRow;
        
        // Initialise cell value
        $this->_value = $pValue;
        
        // Set worksheet
        $this->_parent = $pSheet;
        
        // Set datatype?
        if (!is_null($pDataType))
        {
            $this->_dataType = $pDataType;
        }
        else
        {
            if (!self::getValueBinder()->bindValue($this, $pValue))
                throw new Exception("Value could not be bound to cell.");
        }
        
        // set default index to cellXf
		$this->_xfIndex = 0;
    }
    
    /**
     * Get cell coordinate column
     *
     * @return string
     */
    public function getColumn()
    {
        return strtoupper($this->_column);
    }
    
    /**
     * Get cell coordinate row
     *
     * @return int
     */
    public function getRow()
    {
        return $this->_row;
    }
    
    /**
     * Get cell coordinate
     *
     * @return string
     */
    public function getCoordinate()
    {
        return $this->_column . $this->_row;
    }
    
    /**
     * Get cell value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Set cell value
     *
     * This clears the cell formula.
     *
     * @param mixed 	$pValue					Value
     * @param bool 		$pUpdateDataType		Update the data type?
     */
    public function setValue($pValue = null, $pUpdateDataType = true)
    {
        if (StringHelper::isUTF8($pValue) == false)
            $pValue = utf8_encode($pValue);
        
        $this->_value = $pValue;
        
        if ($pUpdateDataType)
        {
            if (!self::getValueBinder()->bindValue($this, $pValue))
            {
                throw new Exception("Value could not be bound to cell.");
            }
        }
    }
    
    /**
     * Set cell value (with explicit data type given)
     *
     * @param mixed 	$pValue			Value
     * @param string	$pDataType		Explicit data type
     */
    public function setValueExplicit($pValue = null, $pDataType = ExcelCell_DataType::TYPE_STRING)
    {
        $this->_value    = $pValue;
        $this->_dataType = $pDataType;
    }
    
    /**
     * Get caluclated cell value
     *
     * @return mixed
     */
    public function getCalculatedValue()
    {
        if (!is_null($this->_calculatedValue) && $this->_dataType == ExcelCell_DataType::TYPE_FORMULA)
        {
            try
            {
                $result = ExcelCalculation::getInstance()->calculate($this);
            }
            catch (Exception $ex)
            {
                $result = '#N/A';
            }
            
            if ((is_string($result)) && ($result == '#Not Yet Implemented'))
            {
                return $this->_calculatedValue; // Fallback if calculation engine does not support the formula.
            }
            else
            {
                return $result;
            }
        }
        
        if (is_null($this->_value) || $this->_value === '')
        {
        }
        else if ($this->_dataType != ExcelCell_DataType::TYPE_FORMULA)
        {
            return $this->_value;
        }
        else
        {
            return ExcelCalculation::getInstance()->calculate($this);
        }
    }
    
    /**
     * Set calculated value (used for caching)
     *
     * @param mixed $pValue	Value
     */
    public function setCalculatedValue($pValue = null)
    {
        if (!is_null($pValue))
        {
            $this->_calculatedValue = $pValue;
        }
    }
    
    public function getOldCalculatedValue()
    {
        return $this->_calculatedValue;
    }
    
    /**
     * Get cell data type
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->_dataType;
    }
    
    /**
     * Set cell data type
     *
     * @param string $pDataType
     */
    public function setDataType($pDataType = ExcelCell_DataType::TYPE_STRING)
    {
        $this->_dataType = $pDataType;
    }
    
    /**
     * Has Data validation?
     *
     * @return boolean
     */
    public function hasDataValidation()
    {
        return !is_null($this->_dataValidation);
    }
    
    /**
     * Get Data validation
     *
     * @return ExcelCell_DataValidation
     */
    public function getDataValidation()
    {
        if (is_null($this->_dataValidation))
        {
            $this->_dataValidation = new ExcelCell_DataValidation($this);
        }
        
        return $this->_dataValidation;
    }
    
    /**
     * Set Data validation
     *
     * @param 	ExcelCell_DataValidation	$pDataValidation
     * @throws 	Exception
     */
    public function setDataValidation(ExcelCell_DataValidation $pDataValidation = null)
    {
        $this->_dataValidation = $pDataValidation;
        $this->_dataValidation->setParent($this);
    }
    
    /**
     * Has Hyperlink
     *
     * @return boolean
     */
    public function hasHyperlink()
    {
        return !is_null($this->_hyperlink);
    }
    
    /**
     * Get Hyperlink
     *
     * @return ExcelCell_Hyperlink
     */
    public function getHyperlink()
    {
        if (is_null($this->_hyperlink))
        {
            $this->_hyperlink = new ExcelCell_Hyperlink($this);
        }
        
        return $this->_hyperlink;
    }
    
    /**
     * Set Hyperlink
     *
     * @param 	ExcelCell_Hyperlink	$pHyperlink
     * @throws 	Exception
     */
    public function setHyperlink(ExcelCell_Hyperlink $pHyperlink = null)
    {
        $this->_hyperlink = $pHyperlink;
        $this->_hyperlink->setParent($this);
    }
    
    /**
     * Get parent
     *
     * @return ExcelWorksheet
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * Re-bind parent
     *
     * @param ExcelWorksheet $parent
     */
    public function rebindParent(ExcelWorksheet $parent)
    {
        $this->_parent = $parent;
    }
    
    /**
     * Is cell in a specific range?
     *
     * @param 	string 	$pRange		Cell range (e.g. A1:A1)
     * @return 	boolean
     */
    public function isInRange($pRange = 'A1:A1')
    {
        // Uppercase coordinate
        $pRange = strtoupper($pRange);
        
        // Extract range
        $rangeA = '';
        $rangeB = '';
        if (strpos($pRange, ':') === false)
        {
            $rangeA = $pRange;
            $rangeB = $pRange;
        }
        else
        {
            list($rangeA, $rangeB) = explode(':', $pRange);
        }
        
        // Calculate range outer borders
        $rangeStart = ExcelCell::coordinateFromString($rangeA);
        $rangeEnd   = ExcelCell::coordinateFromString($rangeB);
        
        // Translate column into index
        $rangeStart[0] = ExcelCell::columnIndexFromString($rangeStart[0]) - 1;
        $rangeEnd[0]   = ExcelCell::columnIndexFromString($rangeEnd[0]) - 1;
        
        // Translate properties
        $myColumn = ExcelCell::columnIndexFromString($this->getColumn()) - 1;
        $myRow    = $this->getRow();
        
        // Verify if cell is in range
        return (($rangeStart[0] <= $myColumn && $rangeEnd[0] >= $myColumn) && ($rangeStart[1] <= $myRow && $rangeEnd[1] >= $myRow));
    }
    
    /**
     * Coordinate from string
     *
     * @param 	string 	$pCoordinateString
     * @return 	array 	Array containing column and row (indexes 0 and 1)
     * @throws	Exception
     */
    public static function coordinateFromString($pCoordinateString = 'A1')
    {
        if (strpos($pCoordinateString, ':') !== false)
        {
            throw new Exception('Cell coordinate string can not be a range of cells.');
        }
        else if ($pCoordinateString == '')
        {
            throw new Exception('Cell coordinate can not be zero-length string.');
        }
        else
        {
            // Column
            $column = '';
            
            // Row
            $row = '';
            
            // Convert a cell reference
            if (preg_match("/([$]?[A-Z]+)([$]?\d+)/", $pCoordinateString, $matches))
            {
                list(, $column, $row) = $matches;
            }
            
            // Return array
            return array(
                $column,
                $row
            );
        }
    }
    
    /**
     * Make string coordinate absolute
     *
     * @param 	string 	$pCoordinateString
     * @return 	string	Absolute coordinate
     * @throws	Exception
     */
    public static function absoluteCoordinate($pCoordinateString = 'A1')
    {
        if (strpos($pCoordinateString, ':') === false && strpos($pCoordinateString, ',') === false)
        {
            // Return value
            $returnValue = '';
            
            // Create absolute coordinate
            list($column, $row) = ExcelCell::coordinateFromString($pCoordinateString);
            $returnValue = '$' . $column . '$' . $row;
            
            // Return
            return $returnValue;
        }
        else
        {
            throw new Exception("Coordinate string should not be a cell range.");
        }
    }
    
    /**
     * Split range into coordinate strings
     *
     * @param 	string 	$pRange
     * @return 	array	Array containg one or more arrays containing one or two coordinate strings
     */
    public static function splitRange($pRange = 'A1:A1')
    {
        $exploded = explode(',', $pRange);
        for ($i = 0; $i < count($exploded); $i++)
        {
            $exploded[$i] = explode(':', $exploded[$i]);
        }
        return $exploded;
    }
    
    /**
     * Build range from coordinate strings
     *
     * @param 	array	$pRange	Array containg one or more arrays containing one or two coordinate strings
     * @return  string	String representation of $pRange
     * @throws	Exception
     */
    public static function buildRange($pRange)
    {
        // Verify range
        if (!is_array($pRange) || count($pRange) == 0 || !is_array($pRange[0]))
        {
            throw new Exception('Range does not contain any information.');
        }
        
        // Build range
        $imploded = array();
        for ($i = 0; $i < count($pRange); $i++)
        {
            $pRange[$i] = implode(':', $pRange[$i]);
        }
        $imploded = implode(',', $pRange);
        
        return $imploded;
    }
    
    /**
     * Calculate range dimension
     *
     * @param 	string 	$pRange		Cell range (e.g. A1:A1)
     * @return 	array	Range dimension (width, height)
     */
    public static function rangeDimension($pRange = 'A1:A1')
    {
        // Uppercase coordinate
        $pRange = strtoupper($pRange);
        
        // Extract range
        $rangeA = '';
        $rangeB = '';
        if (strpos($pRange, ':') === false)
        {
            $rangeA = $pRange;
            $rangeB = $pRange;
        }
        else
        {
            list($rangeA, $rangeB) = explode(':', $pRange);
        }
        
        // Calculate range outer borders
        $rangeStart = ExcelCell::coordinateFromString($rangeA);
        $rangeEnd   = ExcelCell::coordinateFromString($rangeB);
        
        // Translate column into index
        $rangeStart[0] = ExcelCell::columnIndexFromString($rangeStart[0]);
        $rangeEnd[0]   = ExcelCell::columnIndexFromString($rangeEnd[0]);
        
        return array(
            ($rangeEnd[0] - $rangeStart[0] + 1),
            ($rangeEnd[1] - $rangeStart[1] + 1)
        );
    }
    
    /**
     * Column index from string
     *
     * @param 	string $pString
     * @return 	int Column index (base 1 !!!)
     * @throws 	Exception
     */
    public static function columnIndexFromString($pString = 'A')
    {
        // Convert to uppercase
        $pString = strtoupper($pString);
        
        $strLen = strlen($pString);
        // Convert column to integer
        if ($strLen == 1)
        {
            return (ord($pString{0}) - 64);
        }
        elseif ($strLen == 2)
        {
            return $result = ((1 + (ord($pString{0}) - 65)) * 26) + (ord($pString{1}) - 64);
        }
        elseif ($strLen == 3)
        {
            return ((1 + (ord($pString{0}) - 65)) * 676) + ((1 + (ord($pString{1}) - 65)) * 26) + (ord($pString{2}) - 64);
        }
        else
        {
            throw new Exception("Column string index can not be " . ($strLen != 0 ? "longer than 3 characters" : "empty") . ".");
        }
    }
    
    /**
     * String from columnindex
     *
     * @param int $pColumnIndex Column index (base 0 !!!)
     * @return string
     */
    public static function stringFromColumnIndex($pColumnIndex = 0)
    {
        // Determine column string
        if ($pColumnIndex < 26)
        {
            return chr(65 + $pColumnIndex);
        }
        return ExcelCell::stringFromColumnIndex((int) ($pColumnIndex / 26) - 1) . chr(65 + $pColumnIndex % 26);
    }
    
    /**
     * Extract all cell references in range
     *
     * @param 	string 	$pRange		Range (e.g. A1 or A1:A10 or A1:A10 A100:A1000)
     * @return 	array	Array containing single cell references
     */
    public static function extractAllCellReferencesInRange($pRange = 'A1')
    {
        // Returnvalue
        $returnValue = array();
        
        // Explode spaces
        $aExplodeSpaces = explode(' ', str_replace('$', '', strtoupper($pRange)));
        foreach ($aExplodeSpaces as $explodedSpaces)
        {
            // Single cell?
            if (strpos($explodedSpaces, ':') === false && strpos($explodedSpaces, ',') === false)
            {
                $col = 'A';
                $row = 1;
                list($col, $row) = ExcelCell::coordinateFromString($explodedSpaces);
                
                if (strlen($col) <= 2)
                {
                    $returnValue[] = $explodedSpaces;
                }
                
                continue;
            }
            
            // Range...
            $range = ExcelCell::splitRange($explodedSpaces);
            for ($i = 0; $i < count($range); $i++)
            {
                // Single cell?
                if (count($range[$i]) == 1)
                {
                    $col = 'A';
                    $row = 1;
                    list($col, $row) = ExcelCell::coordinateFromString($range[$i]);
                    
                    if (strlen($col) <= 2)
                    {
                        $returnValue[] = $explodedSpaces;
                    }
                }
                
                // Range...
                $rangeStart  = $rangeEnd = '';
                $startingCol = $startingRow = $endingCol = $endingRow = 0;
                
                list($rangeStart, $rangeEnd) = $range[$i];
                list($startingCol, $startingRow) = ExcelCell::coordinateFromString($rangeStart);
                list($endingCol, $endingRow) = ExcelCell::coordinateFromString($rangeEnd);
                
                // Conversions...
                $startingCol = ExcelCell::columnIndexFromString($startingCol);
                $endingCol   = ExcelCell::columnIndexFromString($endingCol);
                
                // Current data
                $currentCol = --$startingCol;
                $currentRow = $startingRow;
                
                // Loop cells
                while ($currentCol < $endingCol)
                {
                    $loopColumn = ExcelCell::stringFromColumnIndex($currentCol);
                    while ($currentRow <= $endingRow)
                    {
                        $returnValue[] = $loopColumn . $currentRow;
                        ++$currentRow;
                    }
                    ++$currentCol;
                    $currentRow = $startingRow;
                }
            }
        }
        
        // Return value
        return $returnValue;
    }
    
    /**
     * Compare 2 cells
     *
     * @param 	ExcelCell	$a	Cell a
     * @param 	ExcelCell	$a	Cell b
     * @return 	int		Result of comparison (always -1 or 1, never zero!)
     */
    public static function compareCells(ExcelCell $a, ExcelCell $b)
    {
        if ($a->_row < $b->_row)
        {
            return -1;
        }
        elseif ($a->_row > $b->_row)
        {
            return 1;
        }
        elseif (ExcelCell::columnIndexFromString($a->_column) < ExcelCell::columnIndexFromString($b->_column))
        {
            return -1;
        }
        else
        {
            return 1;
        }
    }
    
    /**
     * Get value binder to use
     *
     * @return ExcelCell_IValueBinder
     */
    public static function getValueBinder()
    {
        return self::$_valueBinder;
    }
    
    /**
     * Set value binder to use
     *
     * @param ExcelCell_IValueBinder $binder
     * @throws Exception
     */
    public static function setValueBinder(ExcelCell_IValueBinder $binder = null)
    {
        if (is_null($binder))
        {
            throw new Exception("A ExcelCell_IValueBinder is required for PHPExcel to function correctly.");
        }
        
        self::$_valueBinder = $binder;
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
	 * @return ExcelCell
	 */
	public function setXfIndex($pValue = 0)
	{
		$this->_xfIndex = $pValue;
		return $this;
	}
}

/**
 * ExcelNamedRange
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelNamedRange extends ObjectBase
{
    /**
     * Range name
     *
     * @var string
     */
    private $_name;
    
    /**
     * Worksheet on which the named range can be resolved
     *
     * @var ExcelWorksheet
     */
    private $_worksheet;
    
    /**
     * Range of the referenced cells
     *
     * @var string
     */
    private $_range;
    
    /**
     * Is the named range local? (i.e. can only be used on $this->_worksheet)
     *
     * @var bool
     */
    private $_localOnly;
    
    /**
     * Create a new NamedRange
     *
     * @param string $pName
     * @param ExcelWorksheet $pWorksheet
     * @param string $pRange
     * @param bool $pLocalOnly
     */
    public function __construct($pName = null, ExcelWorksheet $pWorksheet, $pRange = 'A1', $pLocalOnly = false)
    {
        // Validate data
        if (is_null($pName) || is_null($pWorksheet) || is_null($pRange))
        {
            throw new Exception('Parameters can not be null.');
        }
        
        // Set local members
        $this->_name      = $pName;
        $this->_worksheet = $pWorksheet;
        $this->_range     = $pRange;
        $this->_localOnly = $pLocalOnly;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Set name
     *
     * @param string $value
     */
    public function setName($value = null)
    {
        if (!is_null($value))
        {
            // Old title
            $oldTitle = $this->_name;
            
            // Re-attach
            if (!is_null($this->_worksheet))
            {
                $this->_worksheet->getParent()->removeNamedRange($this->_name);
            }
            $this->_name = $value;
            if (!is_null($this->_worksheet))
            {
                $this->_worksheet->getParent()->addNamedRange($this);
            }
            
            // New title
            $newTitle = $this->_name;
            ExcelReferenceHelper::getInstance()->updateNamedFormulas($this->_worksheet->getParent(), $oldTitle, $newTitle);
        }
    }
    
    /**
     * Get worksheet
     *
     * @return ExcelWorksheet
     */
    public function getWorksheet()
    {
        return $this->_worksheet;
    }
    
    /**
     * Set worksheet
     *
     * @param ExcelWorksheet $value
     */
    public function setWorksheet(ExcelWorksheet $value = null)
    {
        if (!is_null($value))
        {
            $this->_worksheet = $value;
        }
    }
    
    /**
     * Get range
     *
     * @return string
     */
    public function getRange()
    {
        return $this->_range;
    }
    
    /**
     * Set range
     *
     * @param string $value
     */
    public function setRange($value = null)
    {
        if (!is_null($value))
        {
            $this->_range = $value;
        }
    }
    
    /**
     * Get localOnly
     *
     * @return bool
     */
    public function getLocalOnly()
    {
        return $this->_localOnly;
    }
    
    /**
     * Set localOnly
     *
     * @param bool $value
     */
    public function setLocalOnly($value = false)
    {
        $this->_localOnly = $value;
    }
    
    /**
     * Resolve a named range to a regular cell range
     *
     * @param string $pNamedRange Named range
     * @param ExcelWorksheet $pSheet Worksheet
     * @return ExcelNamedRange
     */
    public static function resolveRange($pNamedRange = '', ExcelWorksheet $pSheet)
    {
        return $pSheet->getParent()->getNamedRange($pNamedRange);
    }
}

/**
 * ExcelComment
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelComment extends ComparableBase
{
    private $_author;
    private $_text;
    private $_width = '96pt';
    private $_marginLeft = '59.25pt';
    private $_marginTop = '1.5pt';
    private $_visible = false;
    private $_height = '55.5pt';
    
    /**
     * Comment fill color
     *
     * @var ExcelStyle_Color
     */
    private $_fillColor;
    
    /**
     * Create a new ExcelComment
     */
    public function __construct()
    {
        $this->_author    = 'Author';
        $this->_text      = new ExcelRichText();
        $this->_fillColor = new ExcelStyle_Color('FFFFFFE1');
    }
    
    /**
     * Get Author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->_author;
    }
    
    /**
     * Set Author
     *
     * @param string $pValue
     */
    public function setAuthor($pValue = '')
    {
        $this->_author = $pValue;
    }
    
    /**
     * Get Rich text comment
     *
     * @return ExcelRichText
     */
    public function getText()
    {
        return $this->_text;
    }
    
    /**
     * Set Rich text comment
     *
     * @param ExcelRichText $pValue
     */
    public function setText(ExcelRichText $pValue)
    {
        $this->_text = $pValue;
    }
    
    /**
     * Get comment width (CSS style, i.e. XXpx or YYpt)
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->_width;
    }
    
    /**
     * Set comment width (CSS style, i.e. XXpx or YYpt)
     *
     * @param string $value
     */
    public function setWidth($value = '96pt')
    {
        $this->_width = $value;
    }
    
    /**
     * Get comment height (CSS style, i.e. XXpx or YYpt)
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->_height;
    }
    
    /**
     * Set comment height (CSS style, i.e. XXpx or YYpt)
     *
     * @param string $value
     */
    public function setHeight($value = '55.5pt')
    {
        $this->_height = $value;
    }
    
    /**
     * Get left margin (CSS style, i.e. XXpx or YYpt)
     *
     * @return string
     */
    public function getMarginLeft()
    {
        return $this->_marginLeft;
    }
    
    /**
     * Set left margin (CSS style, i.e. XXpx or YYpt)
     *
     * @param string $value
     */
    public function setMarginLeft($value = '59.25pt')
    {
        $this->_marginLeft = $value;
    }
    
    /**
     * Get top margin (CSS style, i.e. XXpx or YYpt)
     *
     * @return string
     */
    public function getMarginTop()
    {
        return $this->_marginTop;
    }
    
    /**
     * Set top margin (CSS style, i.e. XXpx or YYpt)
     *
     * @param string $value
     */
    public function setMarginTop($value = '1.5pt')
    {
        $this->_marginTop = $value;
    }
    
    /**
     * Is the comment visible by default?
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->_visible;
    }
    
    /**
     * Set comment default visibility
     *
     * @param boolean $value
     */
    public function setVisible($value = false)
    {
        $this->_visible = $value;
    }
    
    /**
     * Get fill color
     *
     * @return ExcelStyle_Color
     */
    public function getFillColor()
    {
        return $this->_fillColor;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_author . $this->_text->getHashCode() . $this->_width . $this->_height . $this->_marginLeft . $this->_marginTop . ($this->_visible ? 1 : 0) . $this->_fillColor->getHashCode() . __CLASS__);
    }
}

/**
 * ExcelRichText_ITextElement
 *
 * @package    WebCore
 * @subpackage Excel
 */
interface IExcelTextElement extends IObject
{
    /**
     * Get text
     *
     * @return string	Text
     */
    public function getText();
    
    /**
     * Set text
     *
     * @param 	$pText string	Text
     */
    public function setText($pText = '');
    
    /**
     * Get font
     *
     * @return ExcelStyleFont
     */
    public function getFont();
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode();
}

/**
 * ExcelRichText_TextElement
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelRichText_TextElement extends ObjectBase implements IExcelTextElement
{
    /**
     * Text
     *
     * @var string
     */
    private $_text;
    
    /**
     * Create a new ExcelRichText_TextElement instance
     *
     * @param 	string		$pText		Text
     */
    public function __construct($pText = '')
    {
        // Initialise variables
        $this->_text = $pText;
    }
    
    /**
     * Get text
     *
     * @return string	Text
     */
    public function getText()
    {
        return $this->_text;
    }
    
    /**
     * Set text
     *
     * @param 	$pText string	Text
     */
    public function setText($pText = '')
    {
        $this->_text = $pText;
    }
    
    /**
     * Get font
     *
     * @return ExcelStyleFont
     */
    public function getFont()
    {
        return null;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_text . __CLASS__);
    }
}

/**
 * ExcelRichText_Run
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelRichText_Run extends ExcelRichText_TextElement implements IExcelTextElement
{
    /**
     * Font
     *
     * @var ExcelStyleFont
     */
    private $_font;
    
    /**
     * Create a new ExcelRichText_Run instance
     *
     * @param 	string		$pText		Text
     */
    public function __construct($pText = '')
    {
        // Initialise variables
        $this->setText($pText);
        $this->_font = new ExcelStyleFont();
    }
    
    /**
     * Get font
     *
     * @return ExcelStyleFont
     */
    public function getFont()
    {
        return $this->_font;
    }
    
    /**
     * Set font
     *
     * @param	ExcelStyleFont		$pFont		Font
     * @throws 	Exception
     */
    public function setFont(ExcelStyleFont $pFont = null)
    {
        $this->_font = $pFont;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->getText() . $this->_font->getHashCode() . __CLASS__);
    }
}

/**
 * ExcelRichText
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelRichText extends ComparableBase
{
    /**
     * Rich text elements
     *
     * @var IExcelTextElement[]
     */
    private $_richTextElements;
    
    /**
     * Parent cell
     *
     * @var ExcelCell
     */
    private $_parent;
    
    /**
     * Create a new ExcelRichText instance
     *
     * @param 	ExcelCell	$pParent
     * @throws	Exception
     */
    public function __construct($pCell = null)
    {
        // Initialise variables
        $this->_richTextElements = array();
        
        // Set parent?
        if (!is_null($pCell))
        {
            // Set parent cell
            $this->_parent = $pCell;
            
            // Add cell text and style
            if ($this->_parent->getValue() != "")
            {
                $objRun = new ExcelRichText_Run($this->_parent->getValue());
                $objRun->setFont(clone $this->_parent->getParent()->getStyle($this->_parent->getCoordinate())->getFont());
                $this->addText($objRun);
            }
            
            // Set parent value
            $this->_parent->setValue($this);
        }
    }
    
    /**
     * Add text
     *
     * @param 	ExcelRichText_ITextElement		$pText		Rich text element
     * @throws 	Exception
     */
    public function addText($pText = null)
    {
        $this->_richTextElements[] = $pText;
    }
    
    /**
     * Create text
     *
     * @param 	string	$pText	Text
     * @return	ExcelRichText_TextElement
     * @throws 	Exception
     */
    public function createText($pText = '')
    {
        $objText = new ExcelRichText_TextElement($pText);
        $this->addText($objText);
        return $objText;
    }
    
    /**
     * Create text run
     *
     * @param 	string	$pText	Text
     * @return	ExcelRichText_Run
     * @throws 	Exception
     */
    public function createTextRun($pText = '')
    {
        $objText = new ExcelRichText_Run($pText);
        $this->addText($objText);
        return $objText;
    }
    
    /**
     * Get plain text
     *
     * @return string
     */
    public function getPlainText()
    {
        // Return value
        $returnValue = '';
        
        // Loop trough all ExcelRichText_ITextElement
        foreach ($this->_richTextElements as $text)
        {
            $returnValue .= $text->getText();
        }
        
        // Return
        return $returnValue;
    }
    
    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPlainText();
    }
    
    /**
     * Get Rich Text elements
     *
     * @return ExcelRichText_ITextElement[]
     */
    public function getRichTextElements()
    {
        return $this->_richTextElements;
    }
    
    /**
     * Set Rich Text elements
     *
     * @param 	ExcelRichText_ITextElement[]	$pElements		Array of elements
     * @throws 	Exception
     */
    public function setRichTextElements($pElements = null)
    {
        if (is_array($pElements))
        {
            $this->_richTextElements = $pElements;
        }
        else
        {
            throw new Exception("Invalid ExcelRichText_ITextElement[] array passed.");
        }
    }
    
    /**
     * Get parent
     *
     * @return ExcelCell
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * Set parent
     *
     * @param ExcelCell	$value
     */
    public function setParent($value)
    {
        // Set parent
        $this->_parent = $value;
        
        // Set parent value
        $this->_parent->setValue($this);
        
        // Verify style information
        
        $sheet    = $this->_parent->getParent();
        $cellFont = $sheet->getStyle($this->_parent->getCoordinate())->getFont();
        foreach ($this->getRichTextElements() as $element)
        {
            if (!($element instanceof ExcelRichText_Run))
                continue;
            
            if ($element->getFont()->getHashCode() == $sheet->getDefaultStyle()->getFont()->getHashCode())
            {
                if ($element->getFont()->getHashCode() != $cellFont->getHashCode())
                {
                    $element->setFont(clone $cellFont);
                }
            }
        }
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        $hashElements = '';
        foreach ($this->_richTextElements as $element)
        {
            $hashElements .= $element->getHashCode();
        }
        
        return md5($hashElements . __CLASS__);
    }
}
?>