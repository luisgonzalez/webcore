<?php
/**
 * ExcelStyleBorders
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyleBorders extends ExcelStyleBase
{
    /* Diagonal directions */
    const DIAGONAL_NONE = 0;
    const DIAGONAL_UP = 1;
    const DIAGONAL_DOWN = 2;
    
    /**
     * Left
     *
     * @var ExcelStyleBorder
     */
    private $_left;
    
    /**
     * Right
     *
     * @var ExcelStyleBorder
     */
    private $_right;
    
    /**
     * Top
     *
     * @var ExcelStyleBorder
     */
    private $_top;
    
    /**
     * Bottom
     *
     * @var ExcelStyleBorder
     */
    private $_bottom;
    
    /**
     * Diagonal
     *
     * @var ExcelStyleBorder
     */
    private $_diagonal;
    
    /**
     * Vertical
     *
     * @var ExcelStyleBorder
     */
    private $_vertical;
    
    /**
     * Horizontal
     *
     * @var ExcelStyleBorder
     */
    private $_horizontal;
    
    /**
     * DiagonalDirection
     *
     * @var int
     */
    private $_diagonalDirection;
    
    /**
     * Outline, defaults to true
     *
     * @var boolean
     */
    private $_outline;
    
    /**
     * Parent
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
     * Create a new ExcelStyleBorders
     */
    public function __construct()
    {
        // Initialise values
        
        /**
         * The following properties are late bound. Binding is initiated by property classes when they are modified.
         *
         * _left
         * _right
         * _top
         * _bottom
         * _diagonal
         * _vertical
         * _horizontal
         *
         */
        
        $this->_diagonalDirection = ExcelStyleBorders::DIAGONAL_NONE;
        $this->_outline           = true;
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
     * Returns the ExcelStyleBorders that is actual bound to ExcelStyle
     *
     * @return ExcelStyleBorders
     */
    private function propertyGetBound()
    {
        if (!isset($this->_parent))
            return $this; // I am bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getBorders(); // Another one is bound
        
        return $this; // No one is bound yet
    }
    
    /**
     * Property Begin Bind
     *
     * If no ExcelStyleBorders has been bound to ExcelStyle then bind this one. Return the actual bound one.
     *
     * @return ExcelStyleBorders
     */
    private function propertyBeginBind()
    {
        if (!isset($this->_parent))
            return $this; // I am already bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getBorders(); // Another one is already bound
        
        $this->_parent->propertyCompleteBind($this, $this->_parentPropertyName); // Bind myself
        $this->_parent = null;
        
        return $this;
    }
    
    
    /**
     * Property Complete Bind
     *
     * Complete the binding process a child property object started
     *
     * @param	$propertyObject
     * @param	$propertyName			Name of this property in the parent object
     */
    public function propertyCompleteBind($propertyObject, $propertyName)
    {
        switch ($propertyName)
        {
            case "_left":
                $this->propertyBeginBind()->_left = $propertyObject;
                break;
            
            case "_right":
                $this->propertyBeginBind()->_right = $propertyObject;
                break;
            
            case "_top":
                $this->propertyBeginBind()->_top = $propertyObject;
                break;
            
            case "_bottom":
                $this->propertyBeginBind()->_bottom = $propertyObject;
                break;
            
            case "_diagonal":
                $this->propertyBeginBind()->_diagonal = $propertyObject;
                break;
            
            case "_vertical":
                $this->propertyBeginBind()->_vertical = $propertyObject;
                break;
            
            case "_horizontal":
                $this->propertyBeginBind()->_horizontal = $propertyObject;
                break;
            
            default:
                throw new Exception("Invalid property passed.");
        }
    }
    
    /**
     * Property Is Bound
     *
     * Determines if a child property is bound to this one
     *
     * @param	$propertyName			Name of this property in the parent object
     *
     * @return boolean
     */
    public function propertyIsBound($propertyName)
    {
        switch ($propertyName)
        {
            case "_left":
                return isset($this->propertyGetBound()->_left);
            
            case "_right":
                return isset($this->propertyGetBound()->_right);
            
            case "_top":
                return isset($this->propertyGetBound()->_top);
            
            case "_bottom":
                return isset($this->propertyGetBound()->_bottom);
            
            case "_diagonal":
                return isset($this->propertyGetBound()->_diagonal);
            
            case "_vertical":
                return isset($this->propertyGetBound()->_vertical);
            
            case "_horizontal":
                return isset($this->propertyGetBound()->_horizontal);
            
            default:
                throw new Exception("Invalid property passed.");
        }
    }
    
    /**
     * Apply styles from array
     * 
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     * 		array(
     * 			'bottom'     => array(
     * 				'style' => ExcelStyleBorder::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			),
     * 			'top'     => array(
     * 				'style' => ExcelStyleBorder::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			)
     * 		)
     * );
     * </code>
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     * 		array(
     * 			'allborders' => array(
     * 				'style' => ExcelStyleBorder::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
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
            if (array_key_exists('allborders', $pStyles))
            {
                $this->getLeft()->applyFromArray($pStyles['allborders']);
                $this->getRight()->applyFromArray($pStyles['allborders']);
                $this->getTop()->applyFromArray($pStyles['allborders']);
                $this->getBottom()->applyFromArray($pStyles['allborders']);
            }
            if (array_key_exists('left', $pStyles))
            {
                $this->getLeft()->applyFromArray($pStyles['left']);
            }
            if (array_key_exists('right', $pStyles))
            {
                $this->getRight()->applyFromArray($pStyles['right']);
            }
            if (array_key_exists('top', $pStyles))
            {
                $this->getTop()->applyFromArray($pStyles['top']);
            }
            if (array_key_exists('bottom', $pStyles))
            {
                $this->getBottom()->applyFromArray($pStyles['bottom']);
            }
            if (array_key_exists('diagonal', $pStyles))
            {
                $this->getDiagonal()->applyFromArray($pStyles['diagonal']);
            }
            if (array_key_exists('vertical', $pStyles))
            {
                $this->getVertical()->applyFromArray($pStyles['vertical']);
            }
            if (array_key_exists('horizontal', $pStyles))
            {
                $this->getHorizontal()->applyFromArray($pStyles['horizontal']);
            }
            if (array_key_exists('diagonaldirection', $pStyles))
            {
                $this->setDiagonalDirection($pStyles['diagonaldirection']);
            }
            if (array_key_exists('outline', $pStyles))
            {
                $this->setOutline($pStyles['outline']);
            }
        }
        else
        {
            throw new Exception("Invalid style array passed.");
        }
    }
    
    /**
     * Get Left
     *
     * @return ExcelStyleBorder
     */
    public function getLeft()
    {
        $property = $this->propertyGetBound();
        if (isset($property->_left))
            return $property->_left;
        
        $property = new ExcelStyleBorder();
        $property->propertyPrepareBind($this, "_left");
        return $property;
    }
    
    /**
     * Get Right
     *
     * @return ExcelStyleBorder
     */
    public function getRight()
    {
        $property = $this->propertyGetBound();
        if (isset($property->_right))
            return $property->_right;
        
        
        $property = new ExcelStyleBorder();
        $property->propertyPrepareBind($this, "_right");
        return $property;
    }
    
    /**
     * Get Top
     *
     * @return ExcelStyleBorder
     */
    public function getTop()
    {
        $property = $this->propertyGetBound();
        if (isset($property->_top))
            return $property->_top;
        
        
        $property = new ExcelStyleBorder();
        $property->propertyPrepareBind($this, "_top");
        return $property;
    }
    
    /**
     * Get Bottom
     *
     * @return ExcelStyleBorder
     */
    public function getBottom()
    {
        $property = $this->propertyGetBound();
        if (isset($property->_bottom))
            return $property->_bottom;
        
        $property = new ExcelStyleBorder();
        $property->propertyPrepareBind($this, "_bottom");
        return $property;
    }
    
    /**
     * Get Diagonal
     *
     * @return ExcelStyleBorder
     */
    public function getDiagonal()
    {
        $property = $this->propertyGetBound();
        if (isset($property->_diagonal))
            return $property->_diagonal;
        
        $property = new ExcelStyleBorder();
        $property->propertyPrepareBind($this, "_diagonal");
        return $property;
    }
    
    /**
     * Get Vertical
     *
     * @return ExcelStyleBorder
     */
    public function getVertical()
    {
        $property = $this->propertyGetBound();
        if (isset($property->_vertical))
            return $property->_vertical;
        
        $property = new ExcelStyleBorder();
        $property->propertyPrepareBind($this, "_vertical");
        return $property;
    }
    
    /**
     * Get Horizontal
     *
     * @return ExcelStyleBorder
     */
    public function getHorizontal()
    {
        $property = $this->propertyGetBound();
        if (isset($property->_horizontal))
            return $property->_horizontal;
        
        $property = new ExcelStyleBorder();
        $property->propertyPrepareBind($this, "_horizontal");
        return $property;
    }
    
    /**
     * Get DiagonalDirection
     *
     * @return int
     */
    public function getDiagonalDirection()
    {
        return $this->propertyGetBound()->_diagonalDirection;
    }
    
    /**
     * Set DiagonalDirection
     *
     * @param int $pValue
     */
    public function setDiagonalDirection($pValue = ExcelStyleBorders::DIAGONAL_NONE)
    {
        if ($pValue == '')
        {
            $pValue = ExcelStyleBorders::DIAGONAL_NONE;
        }
        $this->propertyBeginBind()->_diagonalDirection = $pValue;
    }
    
    /**
     * Get Outline
     *
     * @return boolean
     */
    public function getOutline()
    {
        return $this->propertyGetBound()->_outline;
    }
    
    /**
     * Set Outline
     *
     * @param boolean $pValue
     */
    public function setOutline($pValue = true)
    {
        if ($pValue == '')
        {
            $pValue = true;
        }
        $this->propertyBeginBind()->_outline = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        $property = $this->propertyGetBound();
        return md5($property->getLeft()->getHashCode() . $property->getRight()->getHashCode() . $property->getTop()->getHashCode() . $property->getBottom()->getHashCode() . $property->getDiagonal()->getHashCode() . $property->getVertical()->getHashCode() . $property->getHorizontal()->getHashCode() . $property->getDiagonalDirection() . ($property->getOutline() ? 't' : 'f') . __CLASS__);
    }
}

/**
 * ExcelStyleBorder
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyleBorder extends ComparableBase
{
    /* Border style */
    const BORDER_NONE = 'none';
    const BORDER_DASHDOT = 'dashDot';
    const BORDER_DASHDOTDOT = 'dashDotDot';
    const BORDER_DASHED = 'dashed';
    const BORDER_DOTTED = 'dotted';
    const BORDER_DOUBLE = 'double';
    const BORDER_HAIR = 'hair';
    const BORDER_MEDIUM = 'medium';
    const BORDER_MEDIUMDASHDOT = 'mediumDashDot';
    const BORDER_MEDIUMDASHDOTDOT = 'mediumDashDotDot';
    const BORDER_MEDIUMDASHED = 'mediumDashed';
    const BORDER_SLANTDASHDOT = 'slantDashDot';
    const BORDER_THICK = 'thick';
    const BORDER_THIN = 'thin';
    
    /**
     * Border style
     *
     * @var string
     */
    private $_borderStyle;
    
    /**
     * Border color
     * 
     * @var ExcelStyle_Color
     */
    private $_borderColor;
    
    /**
     * Parent
     *
     * @var ExcelStyleBorders
     */
    private $_parent;
    
    /**
     * Parent Property Name
     *
     * @var string
     */
    private $_parentPropertyName;
    
    /**
     * Create a new ExcelStyleBorder
     */
    public function __construct()
    {
        // Initialise values
        $this->_borderStyle = ExcelStyleBorder::BORDER_NONE;
        $this->_borderColor = new ExcelStyle_Color(ExcelStyle_Color::COLOR_BLACK);
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
     * Returns the ExcelStyleBorder that is actual bound to ExcelStyleBorders
     *
     * @return ExcelStyleBorder
     */
    private function propertyGetBound()
    {
        if (!isset($this->_parent))
            return $this; // I am bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
        {
            switch ($this->_parentPropertyName) // Another one is bound
            {
                case "_left":
                    return $this->_parent->getLeft();
                
                case "_right":
                    return $this->_parent->getRight();
                
                case "_top":
                    return $this->_parent->getTop();
                
                case "_bottom":
                    return $this->_parent->getBottom();
                
                case "_diagonal":
                    return $this->_parent->getDiagonal();
                
                case "_vertical":
                    return $this->_parent->getVertical();
                
                case "_horizontal":
                    return $this->_parent->getHorizontal();
            }
        }
        
        return $this; // No one is bound yet
    }
    
    /**
     * Property Begin Bind
     *
     * If no ExcelStyleBorder has been bound to ExcelStyleBorders then bind this one. Return the actual bound one.
     *
     * @return ExcelStyleBorder
     */
    private function propertyBeginBind()
    {
        if (!isset($this->_parent))
            return $this; // I am already bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
        {
            switch ($this->_parentPropertyName) // Another one is already bound
            {
                case "_left":
                    return $this->_parent->getLeft();
                
                case "_right":
                    return $this->_parent->getRight();
                
                case "_top":
                    return $this->_parent->getTop();
                
                case "_bottom":
                    return $this->_parent->getBottom();
                
                case "_diagonal":
                    return $this->_parent->getDiagonal();
                
                case "_vertical":
                    return $this->_parent->getVertical();
                
                case "_horizontal":
                    return $this->_parent->getHorizontal();
            }
        }
        
        $this->_parent->propertyCompleteBind($this, $this->_parentPropertyName); // Bind myself
        $this->_parent = null;
        return $this;
    }
    
    /**
     * Apply styles from array
     * 
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getTop()->applyFromArray(
     * 		array(
     * 			'style' => ExcelStyleBorder::BORDER_DASHDOT,
     * 			'color' => array(
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
            if (array_key_exists('style', $pStyles))
            {
                $this->setBorderStyle($pStyles['style']);
            }
            if (array_key_exists('color', $pStyles))
            {
                $this->getColor()->applyFromArray($pStyles['color']);
            }
        }
        else
        {
            throw new Exception("Invalid style array passed.");
        }
    }
    
    /**
     * Get Border style
     *
     * @return string
     */
    public function getBorderStyle()
    {
        return $this->propertyGetBound()->_borderStyle;
    }
    
    /**
     * Set Border style
     *
     * @param string $pValue
     */
    public function setBorderStyle($pValue = ExcelStyleBorder::BORDER_NONE)
    {
        if ($pValue == '')
        {
            $pValue = ExcelStyleBorder::BORDER_NONE;
        }
        $this->propertyBeginBind()->_borderStyle = $pValue;
    }
    
    /**
     * Get Border Color
     *
     * @return ExcelStyle_Color
     */
    public function getColor()
    {
        // It's a get but it may lead to a modified color which we won't detect but in which case we must bind.
        // So bind as an assurance.
        return $this->propertyBeginBind()->_borderColor;
    }
    
    /**
     * Set Border Color
     *
     * @param 	ExcelStyle_Color $pValue
     * @throws 	Exception
     */
    public function setColor(ExcelStyle_Color $pValue = null)
    {
        $this->propertyBeginBind()->_borderColor = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        $property = $this->propertyGetBound();
        return md5($property->_borderStyle . $property->_borderColor->getHashCode() . __CLASS__);
    }
}
?>