<?php
/**
 * ExcelStyleAlignment
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyleAlignment extends ExcelStyleBase
{
    /* Horizontal alignment styles */
    const HORIZONTAL_GENERAL = 'general';
    const HORIZONTAL_LEFT = 'left';
    const HORIZONTAL_RIGHT = 'right';
    const HORIZONTAL_CENTER = 'center';
    const HORIZONTAL_JUSTIFY = 'justify';
    
    /* Vertical alignment styles */
    const VERTICAL_BOTTOM = 'bottom';
    const VERTICAL_TOP = 'top';
    const VERTICAL_CENTER = 'center';
    const VERTICAL_JUSTIFY = 'justify';
    
    private $_horizontal;
    private $_vertical;
    
    /**
     * Text rotation
     *
     * @var int
     */
    private $_textRotation;
    
    /**
     * Wrap text
     *
     * @var boolean
     */
    private $_wrapText;
    
    /**
     * Shrink to fit
     *
     * @var boolean
     */
    private $_shrinkToFit;
    
    /**
     * Indent - only possible with horizontal alignment left and right
     *
     * @var int
     */
    private $_indent;
    
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
     * Create a new ExcelStyleAlignment
     */
    public function __construct()
    {
        $this->_horizontal   = ExcelStyleAlignment::HORIZONTAL_GENERAL;
        $this->_vertical     = ExcelStyleAlignment::VERTICAL_BOTTOM;
        $this->_textRotation = 0;
        $this->_wrapText     = false;
        $this->_shrinkToFit  = false;
        $this->_indent       = 0;
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
     * Returns the ExcelStyle_Alignment that is actual bound to ExcelStyle
     *
     * @return ExcelStyle_Alignment
     */
    private function propertyGetBound()
    {
        if (!isset($this->_parent))
            return $this;
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getAlignment(); // Another one is bound
        
        return $this; // No one is bound yet
    }
    
    /**
     * Property Begin Bind
     *
     * If no ExcelStyle_Alignment has been bound to ExcelStyle then bind this one. Return the actual bound one.
     *
     * @return ExcelStyle_Alignment
     */
    private function propertyBeginBind()
    {
        if (!isset($this->_parent))
            return $this; // I am already bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getAlignment(); // Another one is already bound
        
        $this->_parent->propertyCompleteBind($this, $this->_parentPropertyName); // Bind myself
        $this->_parent = null;
        
        return $this;
    }
    
    /**
     * Apply styles from array
     * 
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray(
     * 		array(
     * 			'horizontal' => ExcelStyle_Alignment::HORIZONTAL_CENTER,
     * 			'vertical'   => ExcelStyle_Alignment::VERTICAL_CENTER,
     * 			'rotation'   => 0,
     * 			'wrap'       => true
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
            if (array_key_exists('horizontal', $pStyles))
            {
                $this->setHorizontal($pStyles['horizontal']);
            }
            if (array_key_exists('vertical', $pStyles))
            {
                $this->setVertical($pStyles['vertical']);
            }
            if (array_key_exists('rotation', $pStyles))
            {
                $this->setTextRotation($pStyles['rotation']);
            }
            if (array_key_exists('wrap', $pStyles))
            {
                $this->setWrapText($pStyles['wrap']);
            }
            if (array_key_exists('shrinkToFit', $pStyles))
            {
                $this->setShrinkToFit($pStyles['shrinkToFit']);
            }
            if (array_key_exists('indent', $pStyles))
            {
                $this->setIndent($pStyles['indent']);
            }
        }
        else
        {
            throw new Exception("Invalid style array passed.");
        }
    }
    
    /**
     * Get Horizontal
     *
     * @return string
     */
    public function getHorizontal()
    {
        return $this->propertyGetBound()->_horizontal;
    }
    
    /**
     * Set Horizontal
     *
     * @param string $pValue
     */
    public function setHorizontal($pValue = ExcelStyleAlignment::HORIZONTAL_GENERAL)
    {
        if ($pValue == '')
        {
            $pValue = ExcelStyleAlignment::HORIZONTAL_GENERAL;
        }
        $this->propertyBeginBind()->_horizontal = $pValue;
    }
    
    /**
     * Get Vertical
     *
     * @return string
     */
    public function getVertical()
    {
        return $this->propertyGetBound()->_vertical;
    }
    
    /**
     * Set Vertical
     *
     * @param string $pValue
     */
    public function setVertical($pValue = ExcelStyleAlignment::VERTICAL_BOTTOM)
    {
        if ($pValue == '')
        {
            $pValue = ExcelStyleAlignment::VERTICAL_BOTTOM;
        }
        $this->propertyBeginBind()->_vertical = $pValue;
    }
    
    /**
     * Get TextRotation
     *
     * @return int
     */
    public function getTextRotation()
    {
        return $this->propertyGetBound()->_textRotation;
    }
    
    /**
     * Set TextRotation
     *
     * @param int $pValue
     * @throws Exception
     */
    public function setTextRotation($pValue = 0)
    {
        // Excel2007 value 255 => PHPExcel value -165
        if ($pValue == 255)
        {
            $pValue = -165;
        }
        
        // Set rotation
        if (($pValue >= -90 && $pValue <= 90) || $pValue == -165)
        {
            $this->propertyBeginBind()->_textRotation = $pValue;
        }
        else
        {
            throw new Exception("Text rotation should be a value between -90 and 90.");
        }
    }
    
    /**
     * Get Wrap Text
     *
     * @return boolean
     */
    public function getWrapText()
    {
        return $this->propertyGetBound()->_wrapText;
    }
    
    /**
     * Set Wrap Text
     *
     * @param boolean $pValue
     */
    public function setWrapText($pValue = false)
    {
        if ($pValue == '')
            $pValue = false;
        
        $this->propertyBeginBind()->_wrapText = $pValue;
    }
    
    /**
     * Get Shrink to fit
     *
     * @return boolean
     */
    public function getShrinkToFit()
    {
        return $this->propertyGetBound()->_shrinkToFit;
    }
    
    /**
     * Set Shrink to fit
     *
     * @param boolean $pValue
     */
    public function setShrinkToFit($pValue = false)
    {
        if ($pValue == '')
        {
            $pValue = false;
        }
        $this->propertyBeginBind()->_shrinkToFit = $pValue;
    }
    
    /**
     * Get indent
     *
     * @return int
     */
    public function getIndent()
    {
        return $this->propertyGetBound()->_indent;
    }
    
    /**
     * Set indent
     *
     * @param int $pValue
     */
    public function setIndent($pValue = 0)
    {
        if ($pValue > 0)
        {
            if ($this->getHorizontal() != self::HORIZONTAL_GENERAL && $this->getHorizontal() != self::HORIZONTAL_LEFT && $this->getHorizontal() != self::HORIZONTAL_RIGHT)
            {
                $pValue = 0; // indent not supported
            }
        }
        
        $this->propertyBeginBind()->_indent = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        $property = $this->propertyGetBound();
        return md5($property->_horizontal . $property->_vertical . $property->_textRotation . ($property->_wrapText ? 't' : 'f') . ($property->_shrinkToFit ? 't' : 'f') . $property->_indent . __CLASS__);
    }
}
?>