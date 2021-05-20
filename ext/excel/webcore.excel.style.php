<?php
require_once 'style/Borders.php';
require_once 'style/Alignment.php';
require_once 'style/NumberFormat.php';

/**
 * ExcelStyleBase
 *
 * @package    WebCore
 * @subpackage Excel
 */
abstract class ExcelStyleBase extends ComparableBase
{
	/**
	 * Style supervisor?
	 *
	 * @var boolean
	 */
	private $_isSupervisor;

	/**
	 * Parent. Only used for style supervisor
	 *
	 * @var PHPExcel
	 */
	private $_parent;
	
	/**
	 * Bind parent. Only used for supervisor
	 *
	 * @param PHPExcel $parent
	 * @return ExcelStyle
	 */
	public function bindParent($parent)
	{
		$this->_parent = $parent;
		return $this;
	}
	
	/**
	 * Is this a supervisor or a real style component?
	 *
	 * @return boolean
	 */
	public function getIsSupervisor()
	{
		return $this->_isSupervisor;
	}
	
	/**
	 * Get the currently active sheet. Only used for supervisor
	 *
	 * @return ExcelWorksheet
	 */
	public function getActiveSheet()
	{
		if (is_null($this->_parent)) var_dump(debug_backtrace());
		
        return $this->_parent->getActiveSheet();
	}
	
	
	/**
	 * Get the currently active cell coordinate in currently active sheet.
	 * Only used for supervisor
	 *
	 * @return string E.g. 'A1'
	 */
	public function getXSelectedCells()
	{
		return $this->_parent->getActiveSheet()->getXSelectedCells();
	}

	/**
	 * Get the currently active cell coordinate in currently active sheet.
	 * Only used for supervisor
	 *
	 * @return string E.g. 'A1'
	 */
	public function getXActiveCell()
	{
		return $this->_parent->getActiveSheet()->getXActiveCell();
	}

	/**
	 * Get parent. Only used for style supervisor
	 *
	 * @return PHPExcel
	 */
	public function getParent()
	{
		return $this->_parent;
	}
}

/**
 * ExcelStyle_Color
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyle_Color extends ExcelStyleBase
{
    /* Colors */
    const COLOR_BLACK = 'FF000000';
    const COLOR_WHITE = 'FFFFFFFF';
    const COLOR_RED = 'FFFF0000';
    const COLOR_DARKRED = 'FF800000';
    const COLOR_BLUE = 'FF0000FF';
    const COLOR_DARKBLUE = 'FF000080';
    const COLOR_GREEN = 'FF00FF00';
    const COLOR_DARKGREEN = 'FF008000';
    const COLOR_YELLOW = 'FFFFFF00';
    const COLOR_DARKYELLOW = 'FF808000';
    
    /**
     * Indexed colors array
     *
     * @var array
     */
    private static $_indexedColors;
    
    /**
     * ARGB - Alpha RGB
     *
     * @var string
     */
    private $_argb;
    
    /**
     * Create a new ExcelStyle_Color
     * 
     * @param string $pARGB
     */
    public function __construct($pARGB = ExcelStyle_Color::COLOR_BLACK)
    {
        $this->_argb = $pARGB;
    }
    
    /**
     * Apply styles from array
     * 
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getFont()->getColor()->applyFromArray( array('rgb' => '808080') );
     * </code>
     * 
     * @param	array	$pStyles	Array containing style information
     * @throws	Exception
     */
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles))
        {
            if (array_key_exists('rgb', $pStyles))
            {
                $this->setRGB($pStyles['rgb']);
            }
            if (array_key_exists('argb', $pStyles))
            {
                $this->setARGB($pStyles['argb']);
            }
        }
        else
        {
            throw new Exception("Invalid style array passed.");
        }
    }
    
    /**
     * Get ARGB
     *
     * @return string
     */
    public function getARGB()
    {
        return $this->_argb;
    }
    
    /**
     * Set ARGB
     *
     * @param string $pValue
     */
    public function setARGB($pValue = ExcelStyle_Color::COLOR_BLACK)
    {
        if ($pValue == '')
        {
            $pValue = ExcelStyle_Color::COLOR_BLACK;
        }
        $this->_argb = $pValue;
    }
    
    /**
     * Get RGB
     *
     * @return string
     */
    public function getRGB()
    {
        return substr($this->_argb, 2);
    }
    
    /**
     * Set RGB
     *
     * @param string $pValue
     */
    public function setRGB($pValue = '000000')
    {
        if ($pValue == '')
        {
            $pValue = '000000';
        }
        $this->_argb = 'FF' . $pValue;
    }
    
    /**
     * Get indexed color
     * 
     * @param	int		$pIndex
     * @return	ExcelStyle_Color
     */
    public static function indexedColor($pIndex)
    {
        // Clean parameter
        $pIndex = intval($pIndex);
        
        // Indexed colors
        if (is_null(self::$_indexedColors))
        {
            self::$_indexedColors   = array();
            self::$_indexedColors[] = '00000000';
            self::$_indexedColors[] = '00FFFFFF';
            self::$_indexedColors[] = '00FF0000';
            self::$_indexedColors[] = '0000FF00';
            self::$_indexedColors[] = '000000FF';
            self::$_indexedColors[] = '00FFFF00';
            self::$_indexedColors[] = '00FF00FF';
            self::$_indexedColors[] = '0000FFFF';
            self::$_indexedColors[] = '00000000';
            self::$_indexedColors[] = '00FFFFFF';
            self::$_indexedColors[] = '00FF0000';
            self::$_indexedColors[] = '0000FF00';
            self::$_indexedColors[] = '000000FF';
            self::$_indexedColors[] = '00FFFF00';
            self::$_indexedColors[] = '00FF00FF';
            self::$_indexedColors[] = '0000FFFF';
            self::$_indexedColors[] = '00800000';
            self::$_indexedColors[] = '00008000';
            self::$_indexedColors[] = '00000080';
            self::$_indexedColors[] = '00808000';
            self::$_indexedColors[] = '00800080';
            self::$_indexedColors[] = '00008080';
            self::$_indexedColors[] = '00C0C0C0';
            self::$_indexedColors[] = '00808080';
            self::$_indexedColors[] = '009999FF';
            self::$_indexedColors[] = '00993366';
            self::$_indexedColors[] = '00FFFFCC';
            self::$_indexedColors[] = '00CCFFFF';
            self::$_indexedColors[] = '00660066';
            self::$_indexedColors[] = '00FF8080';
            self::$_indexedColors[] = '000066CC';
            self::$_indexedColors[] = '00CCCCFF';
            self::$_indexedColors[] = '00000080';
            self::$_indexedColors[] = '00FF00FF';
            self::$_indexedColors[] = '00FFFF00';
            self::$_indexedColors[] = '0000FFFF';
            self::$_indexedColors[] = '00800080';
            self::$_indexedColors[] = '00800000';
            self::$_indexedColors[] = '00008080';
            self::$_indexedColors[] = '000000FF';
            self::$_indexedColors[] = '0000CCFF';
            self::$_indexedColors[] = '00CCFFFF';
            self::$_indexedColors[] = '00CCFFCC';
            self::$_indexedColors[] = '00FFFF99';
            self::$_indexedColors[] = '0099CCFF';
            self::$_indexedColors[] = '00FF99CC';
            self::$_indexedColors[] = '00CC99FF';
            self::$_indexedColors[] = '00FFCC99';
            self::$_indexedColors[] = '003366FF';
            self::$_indexedColors[] = '0033CCCC';
            self::$_indexedColors[] = '0099CC00';
            self::$_indexedColors[] = '00FFCC00';
            self::$_indexedColors[] = '00FF9900';
            self::$_indexedColors[] = '00FF6600';
            self::$_indexedColors[] = '00666699';
            self::$_indexedColors[] = '00969696';
            self::$_indexedColors[] = '00003366';
            self::$_indexedColors[] = '00339966';
            self::$_indexedColors[] = '00003300';
            self::$_indexedColors[] = '00333300';
            self::$_indexedColors[] = '00993300';
            self::$_indexedColors[] = '00993366';
            self::$_indexedColors[] = '00333399';
            self::$_indexedColors[] = '00333333';
        }
        
        if (array_key_exists($pIndex, self::$_indexedColors))
        {
            return new ExcelStyle_Color(self::$_indexedColors[$pIndex]);
        }
        
        return new ExcelStyle_Color();
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_argb . __CLASS__);
    }
}

/**
 * ExcelStyleFill
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyleFill extends ExcelStyleBase
{
    /* Fill types */
    const FILL_NONE = 'none';
    const FILL_SOLID = 'solid';
    const FILL_GRADIENT_LINEAR = 'linear';
    const FILL_GRADIENT_PATH = 'path';
    const FILL_PATTERN_DARKDOWN = 'darkDown';
    const FILL_PATTERN_DARKGRAY = 'darkGray';
    const FILL_PATTERN_DARKGRID = 'darkGrid';
    const FILL_PATTERN_DARKHORIZONTAL = 'darkHorizontal';
    const FILL_PATTERN_DARKTRELLIS = 'darkTrellis';
    const FILL_PATTERN_DARKUP = 'darkUp';
    const FILL_PATTERN_DARKVERTICAL = 'darkVertical';
    const FILL_PATTERN_GRAY0625 = 'gray0625';
    const FILL_PATTERN_GRAY125 = 'gray125';
    const FILL_PATTERN_LIGHTDOWN = 'lightDown';
    const FILL_PATTERN_LIGHTGRAY = 'lightGray';
    const FILL_PATTERN_LIGHTGRID = 'lightGrid';
    const FILL_PATTERN_LIGHTHORIZONTAL = 'lightHorizontal';
    const FILL_PATTERN_LIGHTTRELLIS = 'lightTrellis';
    const FILL_PATTERN_LIGHTUP = 'lightUp';
    const FILL_PATTERN_LIGHTVERTICAL = 'lightVertical';
    const FILL_PATTERN_MEDIUMGRAY = 'mediumGray';
    
    /**
     * Fill type
     *
     * @var string
     */
    private $_fillType;
    
    /**
     * Rotation
     *
     * @var double
     */
    private $_rotation;
    
    /**
     * Start color
     * 
     * @var ExcelStyle_Color
     */
    private $_startColor;
    
    /**
     * End color
     * 
     * @var ExcelStyle_Color
     */
    private $_endColor;
    
    /**
     * Parent Style
     *
     * @var ExcelStyle
     */
    
    private $_parent;
    
    /**
     * Parent Borders
     *
     * @var _parentPropertyName string
     */
    private $_parentPropertyName;
    
    /**
     * Create a new ExcelStyle_Fill
     */
    public function __construct()
    {
        $this->_fillType   = ExcelStyleFill::FILL_NONE;
        $this->_rotation   = 0;
        $this->_startColor = new ExcelStyle_Color(ExcelStyle_Color::COLOR_WHITE);
        $this->_endColor   = new ExcelStyle_Color(ExcelStyle_Color::COLOR_BLACK);
    }
    
    /**
     * Property Prepare bind
     *
     * Configures this object for late binding as a property of a parent object
     *	 
     * @param $parent
     * @param $parentPropertyName
     */
    public function propertyPrepareBind($parent, $parentPropertyName)
    {
        // Initialize parent ExcelStyle for late binding. This relationship purposely ends immediately when this object
        // is bound to the ExcelStyle object pointed to so as to prevent circular references.
        $this->_parent             = $parent;
        $this->_parentPropertyName = $parentPropertyName;
    }
    
    /**
     * Property Get Bound
     *
     * Returns the ExcelStyle_Fill that is actual bound to ExcelStyle
     *
     * @return ExcelStyle_Fill
     */
    private function propertyGetBound()
    {
        if (!isset($this->_parent))
            return $this; // I am bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getFill(); // Another one is bound
        
        return $this; // No one is bound yet
    }
    
    /**
     * Property Begin Bind
     *
     * If no ExcelStyle_Fill has been bound to ExcelStyle then bind this one. Return the actual bound one.
     *
     * @return ExcelStyle_Fill
     */
    private function propertyBeginBind()
    {
        if (!isset($this->_parent))
            return $this; // I am already bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getFill(); // Another one is already bound
        
        $this->_parent->propertyCompleteBind($this, $this->_parentPropertyName); // Bind myself
        $this->_parent = null;
        
        return $this;
    }
    
    /**
     * Apply styles from array
     * 
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->applyFromArray(
     * 		array(
     * 			'type'       => ExcelStyle_Fill::FILL_GRADIENT_LINEAR,
     * 			'rotation'   => 0,
     * 			'startcolor' => array(
     * 				'rgb' => '000000'
     * 			),
     * 			'endcolor'   => array(
     * 				'argb' => 'FFFFFFFF'
     * 			)
     * 		)
     * );
     * </code>
     * 
     * @param	array	$pStyles	Array containing style information
     * @throws	Exception
     */
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles))
        {
            if (array_key_exists('type', $pStyles))
            {
                $this->setFillType($pStyles['type']);
            }
            if (array_key_exists('rotation', $pStyles))
            {
                $this->setRotation($pStyles['rotation']);
            }
            if (array_key_exists('startcolor', $pStyles))
            {
                $this->getStartColor()->applyFromArray($pStyles['startcolor']);
            }
            if (array_key_exists('endcolor', $pStyles))
            {
                $this->getEndColor()->applyFromArray($pStyles['endcolor']);
            }
            if (array_key_exists('color', $pStyles))
            {
                $this->getStartColor()->applyFromArray($pStyles['color']);
            }
        }
        else
        {
            throw new Exception("Invalid style array passed.");
        }
    }
    
    /**
     * Get Fill Type
     *
     * @return string
     */
    public function getFillType()
    {
        $property = $this->propertyGetBound();
        
        if ($property->_fillType == '')
        {
            $property->_fillType = self::FILL_NONE;
        }
        return $property->_fillType;
    }
    
    /**
     * Set Fill Type
     *
     * @param string $pValue	ExcelStyle_Fill fill type
     */
    public function setFillType($pValue = ExcelStyle_Fill::FILL_NONE)
    {
        $this->propertyBeginBind()->_fillType = $pValue;
    }
    
    /**
     * Get Rotation
     *
     * @return double
     */
    public function getRotation()
    {
        return $this->propertyGetBound()->_rotation;
    }
    
    /**
     * Set Rotation
     *
     * @param double $pValue
     */
    public function setRotation($pValue = 0)
    {
        $this->propertyBeginBind()->_rotation = $pValue;
    }
    
    /**
     * Get Start Color
     *
     * @return ExcelStyle_Color
     */
    public function getStartColor()
    {
        return $this->propertyBeginBind()->_startColor;
    }
    
    /**
     * Set Start Color
     *
     * @param 	ExcelStyle_Color $pValue
     * @throws 	Exception
     */
    public function setStartColor(ExcelStyle_Color $pValue = null)
    {
        $this->propertyBeginBind()->_startColor = $pValue;
    }
    
    /**
     * Get End Color
     *
     * @return ExcelStyle_Color
     */
    public function getEndColor()
    {
        // It's a get but it may lead to a modified color which we won't detect but in which case we must bind.
        // So bind as an assurance.
        return $this->propertyBeginBind()->_endColor;
    }
    
    /**
     * Set End Color
     *
     * @param 	ExcelStyle_Color $pValue
     * @throws 	Exception
     */
    public function setEndColor(ExcelStyle_Color $pValue = null)
    {
        $this->propertyBeginBind()->_endColor = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        $property = $this->propertyGetBound();
        return md5($property->getFillType() . $property->getRotation() . $property->getStartColor()->getHashCode() . $property->getEndColor()->getHashCode() . __CLASS__);
    }
}

/**
 * ExcelStyleFont
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyleFont extends ExcelStyleBase
{
    /* Underline types */
    const UNDERLINE_NONE = 'none';
    const UNDERLINE_DOUBLE = 'double';
    const UNDERLINE_DOUBLEACCOUNTING = 'doubleAccounting';
    const UNDERLINE_SINGLE = 'single';
    const UNDERLINE_SINGLEACCOUNTING = 'singleAccounting';
    
    /**
     * Name
     *
     * @var string
     */
    private $_name;
    
    /**
     * Bold
     *
     * @var boolean
     */
    private $_bold;
    
    /**
     * Italic
     *
     * @var boolean
     */
    private $_italic;
    
    /**
     * Superscript
     *
     * @var boolean
     */
    private $_superScript;
    
    /**
     * Subscript
     *
     * @var boolean
     */
    private $_subScript;
    
    /**
     * Underline
     *
     * @var string
     */
    private $_underline;
    
    /**
     * Striketrough
     *
     * @var boolean
     */
    private $_striketrough;
    
    /**
     * Foreground color
     * 
     * @var ExcelStyle_Color
     */
    private $_color;
    
    /**
     * Parent Style
     *
     * @var ExcelStyle
     */
    
    private $_parent;
    
    /**
     * Parent Borders
     *
     * @var _parentPropertyName string
     */
    private $_parentPropertyName;
    
    /**
     * Create a new ExcelStyleFont
     */
    public function __construct()
    {
        // Initialise values
        $this->_name         = 'Calibri';
        $this->_size         = 10;
        $this->_bold         = false;
        $this->_italic       = false;
        $this->_superScript  = false;
        $this->_subScript    = false;
        $this->_underline    = ExcelStyleFont::UNDERLINE_NONE;
        $this->_striketrough = false;
        $this->_color        = new ExcelStyle_Color(ExcelStyle_Color::COLOR_BLACK);
    }
    
    /**
     * Property Prepare bind
     *
     * Configures this object for late binding as a property of a parent object
     *	 
     * @param $parent
     * @param $parentPropertyName
     */
    public function propertyPrepareBind($parent, $parentPropertyName)
    {
        // Initialize parent ExcelStyle for late binding. This relationship purposely ends immediately when this object
        // is bound to the ExcelStyle object pointed to so as to prevent circular references.
        $this->_parent             = $parent;
        $this->_parentPropertyName = $parentPropertyName;
    }
    
    /**
     * Property Get Bound
     *
     * Returns the ExcelStyleFont that is actual bound to ExcelStyle
     *
     * @return ExcelStyleFont
     */
    private function propertyGetBound()
    {
        if (!isset($this->_parent))
            return $this; // I am bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getFont(); // Another one is bound
        
        return $this; // No one is bound yet
    }
    
    /**
     * Property Begin Bind
     *
     * If no ExcelStyleFont has been bound to ExcelStyle then bind this one. Return the actual bound one.
     *
     * @return ExcelStyleFont
     */
    private function propertyBeginBind()
    {
        if (!isset($this->_parent))
            return $this; // I am already bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getFont(); // Another one is already bound
        
        $this->_parent->propertyCompleteBind($this, $this->_parentPropertyName); // Bind myself
        $this->_parent = null;
        
        return $this;
    }
    
    /**
     * Apply styles from array
     * 
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getFont()->applyFromArray(
     * 		array(
     * 			'name'      => 'Arial',
     * 			'bold'      => true,
     * 			'italic'    => false,
     * 			'underline' => ExcelStyleFont::UNDERLINE_DOUBLE,
     * 			'strike'    => false,
     * 			'color'     => array(
     * 				'rgb' => '808080'
     * 			)
     * 		)
     * );
     * </code>
     * 
     * @param	array	$pStyles	Array containing style information
     * @throws	Exception
     */
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles))
        {
            if (array_key_exists('name', $pStyles))
            {
                $this->setName($pStyles['name']);
            }
            if (array_key_exists('bold', $pStyles))
            {
                $this->setBold($pStyles['bold']);
            }
            if (array_key_exists('italic', $pStyles))
            {
                $this->setItalic($pStyles['italic']);
            }
            if (array_key_exists('superScript', $pStyles))
            {
                $this->setSuperScript($pStyles['superScript']);
            }
            if (array_key_exists('subScript', $pStyles))
            {
                $this->setSubScript($pStyles['subScript']);
            }
            if (array_key_exists('underline', $pStyles))
            {
                $this->setUnderline($pStyles['underline']);
            }
            if (array_key_exists('strike', $pStyles))
            {
                $this->setStriketrough($pStyles['strike']);
            }
            if (array_key_exists('color', $pStyles))
            {
                $this->getColor()->applyFromArray($pStyles['color']);
            }
            if (array_key_exists('size', $pStyles))
            {
                $this->setSize($pStyles['size']);
            }
        }
        else
        {
            throw new Exception("Invalid style array passed.");
        }
    }
    
    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->propertyGetBound()->_name;
    }
    
    /**
     * Set Name
     *
     * @param string $pValue
     */
    public function setName($pValue = 'Calibri')
    {
        if ($pValue == '')
        {
            $pValue = 'Calibri';
        }
        $this->propertyBeginBind()->_name = $pValue;
    }
    
    /**
     * Get Size
     *
     * @return double
     */
    public function getSize()
    {
        return $this->propertyGetBound()->_size;
    }
    
    /**
     * Set Size
     *
     * @param double $pValue
     */
    public function setSize($pValue = 10)
    {
        if ($pValue == '')
        {
            $pValue = 10;
        }
        $this->propertyBeginBind()->_size = $pValue;
    }
    
    /**
     * Get Bold
     *
     * @return boolean
     */
    public function getBold()
    {
        return $this->propertyGetBound()->_bold;
    }
    
    /**
     * Set Bold
     *
     * @param boolean $pValue
     */
    public function setBold($pValue = false)
    {
        if ($pValue == '')
        {
            $pValue = false;
        }
        $this->propertyBeginBind()->_bold = $pValue;
    }
    
    /**
     * Get Italic
     *
     * @return boolean
     */
    public function getItalic()
    {
        return $this->propertyGetBound()->_italic;
    }
    
    /**
     * Set Italic
     *
     * @param boolean $pValue
     */
    public function setItalic($pValue = false)
    {
        if ($pValue == '')
        {
            $pValue = false;
        }
        $this->propertyBeginBind()->_italic = $pValue;
    }
    
    /**
     * Get SuperScript
     *
     * @return boolean
     */
    public function getSuperScript()
    {
        return $this->propertyGetBound()->_superScript;
    }
    
    /**
     * Set SuperScript
     *
     * @param boolean $pValue
     */
    public function setSuperScript($pValue = false)
    {
        if ($pValue == '')
            $pValue = false;
        
        $this->propertyBeginBind()->_superScript = $pValue;
        $this->propertyBeginBind()->_subScript   = !$pValue;
    }
    
    /**
     * Get SubScript
     *
     * @return boolean
     */
    public function getSubScript()
    {
        return $this->propertyGetBound()->_subScript;
    }
    
    /**
     * Set SubScript
     *
     * @param boolean $pValue
     */
    public function setSubScript($pValue = false)
    {
        if ($pValue == '')
            $pValue = false;
        
        $this->propertyBeginBind()->_subScript   = $pValue;
        $this->propertyBeginBind()->_superScript = !$pValue;
    }
    
    /**
     * Get Underline
     *
     * @return string
     */
    public function getUnderline()
    {
        return $this->propertyGetBound()->_underline;
    }
    
    /**
     * Set Underline
     *
     * @param string $pValue	ExcelStyleFont underline type
     */
    public function setUnderline($pValue = ExcelStyleFont::UNDERLINE_NONE)
    {
        if ($pValue == '')
        {
            $pValue = ExcelStyleFont::UNDERLINE_NONE;
        }
        $this->propertyBeginBind()->_underline = $pValue;
    }
    
    /**
     * Get Striketrough
     *
     * @return boolean
     */
    public function getStriketrough()
    {
        return $this->propertyGetBound()->_striketrough;
    }
    
    /**
     * Set Striketrough
     *
     * @param boolean $pValue
     */
    public function setStriketrough($pValue = false)
    {
        if ($pValue == '')
        {
            $pValue = false;
        }
        $this->propertyBeginBind()->_striketrough = $pValue;
    }
    
    /**
     * Get Color
     *
     * @return ExcelStyle_Color
     */
    public function getColor()
    {
        // It's a get but it may lead to a modified color which we won't detect but in which case we must bind.
        // So bind as an assurance.
        return $this->propertyBeginBind()->_color;
    }
    
    /**
     * Set Color
     *
     * @param 	ExcelStyle_Color $pValue
     * @throws 	Exception
     */
    public function setColor(ExcelStyle_Color $pValue = null)
    {
        $this->propertyBeginBind()->_color = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        $property = $this->propertyGetBound();
        return md5($property->_name . $property->_size . ($property->_bold ? 't' : 'f') . ($property->_italic ? 't' : 'f') . ($property->_superScript ? 't' : 'f') . ($property->_subScript ? 't' : 'f') . $property->_underline . ($property->_striketrough ? 't' : 'f') . $property->_color->getHashCode() . __CLASS__);
    }
}

/**
 * ExcelStyle
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyle extends ExcelStyleBase
{
    /**
	 * Font
	 *
	 * @var ExcelStyleFont
	 */
	private $_font;
	
	/**
	 * Fill
	 *
	 * @var ExcelStyle_Fill
	 */
	private $_fill;

	/**
	 * Borders
	 *
	 * @var ExcelStyle_Borders
	 */
	private $_borders;
	
	/**
	 * Alignment
	 *
	 * @var ExcelStyle_Alignment
	 */
	private $_alignment;
	
	/**
	 * Number Format
	 *
	 * @var ExcelStyleNumberFormat
	 */
	private $_numberFormat;
	
	/**
	 * Conditional styles
	 *
	 * @var ExcelStyle_Conditional[]
	 */
	private $_conditionalStyles;
	
	/**
	 * Protection
	 *
	 * @var ExcelStyle_Protection
	 */
	private $_protection;

	/**
	 * Index of style in collection. Only used for real style.
	 *
	 * @var int
	 */
	private $_index;

    /**
     * Create a new ExcelStyle
	 *
	 * @param boolean $isSupervisor
     */
    public function __construct($isSupervisor = false)
    {
    	// Supervisor?
		$this->_isSupervisor = $isSupervisor;

		// Initialise values
    	$this->_conditionalStyles 	= array();
		$this->_font				= new ExcelStyleFont($isSupervisor);
		$this->_fill				= new ExcelStyleFill($isSupervisor);
		$this->_borders				= new ExcelStyleBorders($isSupervisor);
		$this->_alignment			= new ExcelStyleAlignment($isSupervisor);
		$this->_numberFormat		= new ExcelStyleNumberFormat($isSupervisor);
		$this->_protection			= new ExcelStyleProtection($isSupervisor);

		// bind parent if we are a supervisor
		if ($isSupervisor) {
			$this->_font->bindParent($this);
			$this->_fill->bindParent($this);
			$this->_borders->bindParent($this);
			$this->_alignment->bindParent($this);
			$this->_numberFormat->bindParent($this);
			$this->_protection->bindParent($this);
		}
    }

	/**
	 * Get the shared style component for the currently active cell in currently active sheet.
	 * Only used for style supervisor
	 *
	 * @return ExcelStyle
	 */
	public function getSharedComponent()
	{
		$activeSheet = $this->getActiveSheet();
		$selectedCell = $this->getXActiveCell(); // e.g. 'A1'

		if ($activeSheet->cellExists($selectedCell)) {
			$cell = $activeSheet->getCell($selectedCell);
			$xfIndex = $cell->getXfIndex();
		} else {
			$xfIndex = 0;
		}

		$activeStyle = $this->_parent->getCellXfByIndex($xfIndex);
		return $activeStyle;
	}

    /**
     * Apply styles from array
     * 
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->applyFromArray(
     * 		array(
     * 			'font'    => array(
     * 				'name'      => 'Arial',
     * 				'bold'      => true,
     * 				'italic'    => false,
     * 				'underline' => ExcelStyleFont::UNDERLINE_DOUBLE,
     * 				'strike'    => false,
     * 				'color'     => array(
     * 					'rgb' => '808080'
     * 				)
     * 			),
     * 			'borders' => array(
     * 				'bottom'     => array(
     * 					'style' => ExcelStyle_Border::BORDER_DASHDOT,
     * 					'color' => array(
     * 						'rgb' => '808080'
     * 					)
     * 				),
     * 				'top'     => array(
     * 					'style' => ExcelStyle_Border::BORDER_DASHDOT,
     * 					'color' => array(
     * 						'rgb' => '808080'
     * 					)
     * 				)
     * 			)
     * 		)
     * );
     * </code>
     * 
     * @param	array	$pStyles	Array containing style information
     * @param 	boolean		$pAdvanced	Advanced mode for setting borders. 
     * @throws	Exception
     * @return ExcelStyle
     */
	public function applyFromArray($pStyles = null, $pAdvanced = true) {
		if (is_array($pStyles)) {
			if ($this->_isSupervisor) {

				$pRange = $this->getXSelectedCells();

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

				// ADVANCED MODE:

				if ($pAdvanced && isset($pStyles['borders'])) {

					// 'allborders' is a shorthand property for 'outline' and 'inside' and
					//		it applies to components that have not been set explicitly
					if (isset($pStyles['borders']['allborders'])) {
						foreach (array('outline', 'inside') as $component) {
							if (!isset($pStyles['borders'][$component])) {
								$pStyles['borders'][$component] = $pStyles['borders']['allborders'];
							}
						}
						unset($pStyles['borders']['allborders']); // not needed any more
					}

					// 'outline' is a shorthand property for 'top', 'right', 'bottom', 'left'
					//		it applies to components that have not been set explicitly
					if (isset($pStyles['borders']['outline'])) {
						foreach (array('top', 'right', 'bottom', 'left') as $component) {
							if (!isset($pStyles['borders'][$component])) {
								$pStyles['borders'][$component] = $pStyles['borders']['outline'];
							}
						}
						unset($pStyles['borders']['outline']); // not needed any more
					}

					// 'inside' is a shorthand property for 'vertical' and 'horizontal'
					//		it applies to components that have not been set explicitly
					if (isset($pStyles['borders']['inside'])) {
						foreach (array('vertical', 'horizontal') as $component) {
							if (!isset($pStyles['borders'][$component])) {
								$pStyles['borders'][$component] = $pStyles['borders']['inside'];
							}
						}
						unset($pStyles['borders']['inside']); // not needed any more
					}

					// width and height characteristics of selection, 1, 2, or 3 (for 3 or more)
					$xMax = min($rangeEnd[0] - $rangeStart[0] + 1, 3);
					$yMax = min($rangeEnd[1] - $rangeStart[1] + 1, 3);

					// loop through up to 3 x 3 = 9 regions
					for ($x = 1; $x <= $xMax; ++$x) {
						// start column index for region
						$colStart = ($x == 3) ? 
							ExcelCell::stringFromColumnIndex($rangeEnd[0])
								: ExcelCell::stringFromColumnIndex($rangeStart[0] + $x - 1);

						// end column index for region
						$colEnd = ($x == 1) ?
							ExcelCell::stringFromColumnIndex($rangeStart[0])
								: ExcelCell::stringFromColumnIndex($rangeEnd[0] - $xMax + $x);

						for ($y = 1; $y <= $yMax; ++$y) {

							// which edges are touching the region
							$edges = array();

							// are we at left edge
							if ($x == 1) {
								$edges[] = 'left';
							}

							// are we at right edge
							if ($x == $xMax) {
								$edges[] = 'right';
							}

							// are we at top edge?
							if ($y == 1) {
								$edges[] = 'top';
							}

							// are we at bottom edge?
							if ($y == $yMax) {
								$edges[] = 'bottom';
							}

							// start row index for region
							$rowStart = ($y == 3) ?
								$rangeEnd[1] : $rangeStart[1] + $y - 1;

							// end row index for region
							$rowEnd = ($y == 1) ?
								$rangeStart[1] : $rangeEnd[1] - $yMax + $y;

							// build range for region
							$range = $colStart . $rowStart . ':' . $colEnd . $rowEnd;
							
							// retrieve relevant style array for region
							$regionStyles = $pStyles;
							unset($regionStyles['borders']['inside']);

							// what are the inner edges of the region when looking at the selection
							$innerEdges = array_diff( array('top', 'right', 'bottom', 'left'), $edges );

							// inner edges that are not touching the region should take the 'inside' border properties if they have been set
							foreach ($innerEdges as $innerEdge) {
								switch ($innerEdge) {
									case 'top':
									case 'bottom':
										// should pick up 'horizontal' border property if set
										if (isset($pStyles['borders']['horizontal'])) {
											$regionStyles['borders'][$innerEdge] = $pStyles['borders']['horizontal'];
										} else {
											unset($regionStyles['borders'][$innerEdge]);
										}
										break;
									case 'left':
									case 'right':
										// should pick up 'vertical' border property if set
										if (isset($pStyles['borders']['vertical'])) {
											$regionStyles['borders'][$innerEdge] = $pStyles['borders']['vertical'];
										} else {
											unset($regionStyles['borders'][$innerEdge]);
										}
										break;
								}
							}

							// apply region style to region by calling applyFromArray() in simple mode
							$this->getActiveSheet()->getStyle($range)->applyFromArray($regionStyles, false);
						}
					}
					return;
				}

				// SIMPLE MODE:

				// Selection type, inspect
				if (preg_match('/^[A-Z]+1:[A-Z]+1048576$/', $pRange)) {
					$selectionType = 'COLUMN';
				} else if (preg_match('/^A[0-9]+:XFD[0-9]+$/', $pRange)) {
					$selectionType = 'ROW';
				} else {
					$selectionType = 'CELL';
				}

				// First loop through columns, rows, or cells to find out which styles are affected by this operation
				switch ($selectionType) {
					case 'COLUMN':
						$oldXfIndexes = array();
						for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
							$oldXfIndexes[$this->getActiveSheet()->getColumnDimensionByColumn($col)->getXfIndex()] = true;
						}
						break;

					case 'ROW':
						$oldXfIndexes = array();
						for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
							if ($this->getActiveSheet()->getRowDimension($row)->getXfIndex() == null) {
								$oldXfIndexes[0] = true; // row without explicit style should be formatted based on default style
							} else {
								$oldXfIndexes[$this->getActiveSheet()->getRowDimension($row)->getXfIndex()] = true;
							}
						}
						break;

					case 'CELL':
						$oldXfIndexes = array();
						for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
							for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
								$oldXfIndexes[$this->getActiveSheet()->getCellByColumnAndRow($col, $row)->getXfIndex()] = true;
							}
						}
						break;
				}

				// clone each of the affected styles, apply the style arrray, and add the new styles to the workbook
				$workbook = $this->getActiveSheet()->getParent();
				foreach ($oldXfIndexes as $oldXfIndex => $dummy) {
					$style = $workbook->getCellXfByIndex($oldXfIndex);
					$newStyle = clone $style;
					$newStyle->applyFromArray($pStyles);
					
					if ($existingStyle = $workbook->getCellXfByHashCode($newStyle->getHashCode())) {
						// there is already such cell Xf in our collection
						$newXfIndexes[$oldXfIndex] = $existingStyle->getIndex();
					} else {
						// we don't have such a cell Xf, need to add
						$workbook->addCellXf($newStyle);
						$newXfIndexes[$oldXfIndex] = $newStyle->getIndex();
					}
				}

				// Loop through columns, rows, or cells again and update the XF index
				switch ($selectionType) {
					case 'COLUMN':
						for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
							$columnDimension = $this->getActiveSheet()->getColumnDimensionByColumn($col);
							$oldXfIndex = $columnDimension->getXfIndex();
							$columnDimension->setXfIndex($newXfIndexes[$oldXfIndex]);
						}
						break;

					case 'ROW':
						for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
							$rowDimension = $this->getActiveSheet()->getRowDimension($row);
							$oldXfIndex = $rowDimension->getXfIndex() === null ?
								0 : $rowDimension->getXfIndex(); // row without explicit style should be formatted based on default style
							$rowDimension->setXfIndex($newXfIndexes[$oldXfIndex]);
						}
						break;

					case 'CELL':
						for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
							for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
								$cell = $this->getActiveSheet()->getCellByColumnAndRow($col, $row);
								$oldXfIndex = $cell->getXfIndex();
								$cell->setXfIndex($newXfIndexes[$oldXfIndex]);
							}
						}
						break;
				}

			} else {
				// not a supervisor, just apply the style array directly on style object
				if (array_key_exists('fill', $pStyles)) {
					$this->getFill()->applyFromArray($pStyles['fill']);
				}
				if (array_key_exists('font', $pStyles)) {
					$this->getFont()->applyFromArray($pStyles['font']);
				}
				if (array_key_exists('borders', $pStyles)) {
					$this->getBorders()->applyFromArray($pStyles['borders']);
				}
				if (array_key_exists('alignment', $pStyles)) {
					$this->getAlignment()->applyFromArray($pStyles['alignment']);
				}
				if (array_key_exists('numberformat', $pStyles)) {
					$this->getNumberFormat()->applyFromArray($pStyles['numberformat']);
				}
				if (array_key_exists('protection', $pStyles)) {
					$this->getProtection()->applyFromArray($pStyles['protection']);
				}
			}
		} else {
			throw new Exception("Invalid style array passed.");
		}
		return $this;
	}

    /**
     * Get Fill
     *
     * @return ExcelStyle_Fill
     */
    public function getFill() {
		return $this->_fill;
    }
    
    /**
     * Get Font
     *
     * @return ExcelStyleFont
     */
    public function getFont() {
		return $this->_font;
    }

	/**
	 * Set font
	 *
	 * @param ExcelStyleFont $font
	 * @return ExcelStyle
	 */
	public function setFont(ExcelStyleFont $font)
	{
		$this->_font = $font;
		return $this;
	}

    /**
     * Get Borders
     *
     * @return ExcelStyle_Borders
     */
    public function getBorders() {
		return $this->_borders;
    }
    
    /**
     * Get Alignment
     *
     * @return ExcelStyle_Alignment
     */
    public function getAlignment() {
		return $this->_alignment;
    }
    
    /**
     * Get Number Format
     *
     * @return ExcelStyleNumberFormat
     */
    public function getNumberFormat() {
		return $this->_numberFormat;
    }
    
    /**
     * Get Conditional Styles. Only used on supervisor.
     *
     * @return ExcelStyle_Conditional[]
     */
    public function getConditionalStyles() {
		return $this->getActiveSheet()->getConditionalStyles($this->getXActiveCell());
    }
       
    /**
     * Set Conditional Styles. Only used on supervisor.
     *
     * @param ExcelStyle_Conditional[]	$pValue	Array of condtional styles
     * @return ExcelStyle
     */
    public function setConditionalStyles($pValue = null) {
		if (is_array($pValue)) {
			foreach (ExcelCell::extractAllCellReferencesInRange($this->getXSelectedCells()) as $cellReference) {
				$this->getActiveSheet()->setConditionalStyles($cellReference, $pValue);
			}
		}
		return $this;
    }
    
    /**
     * Get Protection
     *
     * @return ExcelStyle_Protection
     */
    public function getProtection() {
		return $this->_protection;
    }
   
	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */	
	public function getHashCode() {
		$hashConditionals = '';
		foreach ($this->_conditionalStyles as $conditional) {
			$hashConditionals .= $conditional->getHashCode();
		}
		
    	return md5(
    		  $this->getFill()->getHashCode()
    		. $this->getFont()->getHashCode()
    		. $this->getBorders()->getHashCode()
    		. $this->getAlignment()->getHashCode()
    		. $this->getNumberFormat()->getHashCode()
    		. $hashConditionals
    		. $this->getProtection()->getHashCode()
    		. __CLASS__
    	);
    }
    
	/**
	 * Get own index in style collection
	 *
	 * @return int
	 */
	public function getIndex()
	{
		return $this->_index;
	}

	/**
	 * Set own index in style collection
	 *
	 * @param int $pValue
	 */
	public function setIndex($pValue)
	{
		$this->_index = $pValue;
	}
}
?>