<?php
/**
 * ExcelStyleNumberFormat
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyleNumberFormat extends ExcelStyleBase
{
    /* Pre-defined formats */
	const FORMAT_GENERAL					= 'General';

	const FORMAT_TEXT						= '@';

	const FORMAT_NUMBER						= '0';
	const FORMAT_NUMBER_00					= '0.00';
	const FORMAT_NUMBER_COMMA_SEPARATED1	= '#,##0.00';
	const FORMAT_NUMBER_COMMA_SEPARATED2	= '#,##0.00_-';

	const FORMAT_PERCENTAGE					= '0%';
	const FORMAT_PERCENTAGE_00				= '0.00%';

	const FORMAT_DATE_YYYYMMDD2				= 'yyyy-mm-dd';
	const FORMAT_DATE_YYYYMMDD				= 'yy-mm-dd';
	const FORMAT_DATE_DDMMYYYY				= 'dd/mm/yy';
	const FORMAT_DATE_DMYSLASH				= 'd/m/y';
	const FORMAT_DATE_DMYMINUS				= 'd-m-y';
	const FORMAT_DATE_DMMINUS				= 'd-m';
	const FORMAT_DATE_MYMINUS				= 'm-y';
	const FORMAT_DATE_XLSX14				= 'mm-dd-yy';
	const FORMAT_DATE_XLSX15				= 'd-mmm-yy';
	const FORMAT_DATE_XLSX16				= 'd-mmm';
	const FORMAT_DATE_XLSX17				= 'mmm-yy';
	const FORMAT_DATE_XLSX22				= 'm/d/yy h:mm';
	const FORMAT_DATE_DATETIME				= 'd/m/y h:mm';
	const FORMAT_DATE_TIME1					= 'h:mm AM/PM';
	const FORMAT_DATE_TIME2					= 'h:mm:ss AM/PM';
	const FORMAT_DATE_TIME3					= 'h:mm';
	const FORMAT_DATE_TIME4					= 'h:mm:ss';
	const FORMAT_DATE_TIME5					= 'mm:ss';
	const FORMAT_DATE_TIME6					= 'h:mm:ss';
	const FORMAT_DATE_TIME7					= 'i:s.S';
	const FORMAT_DATE_TIME8					= 'h:mm:ss;@';
	const FORMAT_DATE_YYYYMMDDSLASH			= 'yy/mm/dd;@';

	const FORMAT_CURRENCY_USD_SIMPLE		= '"$"#,##0.00_-';
	const FORMAT_CURRENCY_USD				= '$#,##0_-';
	const FORMAT_CURRENCY_EUR_SIMPLE		= '[$EUR ]#,##0.00_-';

	/**
	 * Excel built-in number formats
	 *
	 * @var array
	 */
	private static $_builtInFormats;

	/**
	 * Excel built-in number formats (flipped, for faster lookups)
	 *
	 * @var array
	 */
	private static $_flippedBuiltInFormats;

	/**
	 * Format Code
	 *
	 * @var string
	 */
	private $_formatCode;

	/**
	 * Built-in format Code
	 *
	 * @var string
	 */
	private $_builtInFormatCode;

	/**
	 * Parent Borders
	 *
	 * @var _parentPropertyName string
	 */
	private $_parentPropertyName;

	/**
     * Create a new ExcelStyleNumberFormat
     */
    public function __construct($isSupervisor = false)
    {
    	// Supervisor?
		$this->_isSupervisor = $isSupervisor;

    	// Initialise values
    	$this->_formatCode			= ExcelStyleNumberFormat::FORMAT_GENERAL;
    	$this->_builtInFormatCode	= 0;
    }

	/**
	 * Get the shared style component for the currently active cell in currently active sheet.
	 * Only used for style supervisor
	 *
	 * @return ExcelStyleNumberFormat
	 */
	public function getSharedComponent()
	{
		return $this->_parent->getSharedComponent()->getNumberFormat();
	}

	/**
	 * Build style array from subcomponents
	 *
	 * @param array $array
	 * @return array
	 */
	public function getStyleArray($array)
	{
		return array('numberformat' => $array);
	}

    /**
     * Apply styles from array
     *
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getNumberFormat()->applyFromArray(
     * 		array(
     * 			'code' => ExcelStyleNumberFormat::FORMAT_CURRENCY_EUR_SIMPLE
     * 		)
     * );
     * </code>
     *
     * @param	array	$pStyles	Array containing style information
     * @throws	Exception
     * @return ExcelStyleNumberFormat
     */
	public function applyFromArray($pStyles = null) {
		if (is_array($pStyles)) {
			if ($this->_isSupervisor) {
				$this->getActiveSheet()->getStyle($this->getXSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
			} else {
				if (array_key_exists('code', $pStyles)) {
					$this->setFormatCode($pStyles['code']);
				}
			}
		} else {
			throw new Exception("Invalid style array passed.");
		}
		return $this;
	}

    /**
     * Get Format Code
     *
     * @return string
     */
    public function getFormatCode() {
		if ($this->_isSupervisor) {
			return $this->getSharedComponent()->getFormatCode();
		}
    	if ($this->_builtInFormatCode !== false)
    		return self::builtInFormatCode($this->_builtInFormatCode);
    	
    	return $this->_formatCode;
    }

    /**
     * Set Format Code
     *
     * @param string $pValue
     * @return ExcelStyleNumberFormat
     */
    public function setFormatCode($pValue = ExcelStyleNumberFormat::FORMAT_GENERAL) {
        if ($pValue == '') {
    		$pValue = ExcelStyleNumberFormat::FORMAT_GENERAL;
    	}
		if ($this->_isSupervisor) {
			$styleArray = $this->getStyleArray(array('code' => $pValue));
			$this->getActiveSheet()->getStyle($this->getXSelectedCells())->applyFromArray($styleArray);
		} else {
			$this->_formatCode = $pValue;
			$this->_builtInFormatCode = self::builtInFormatCodeIndex($pValue);
		}
		return $this;
    }

	/**
     * Get Built-In Format Code
     *
     * @return int
     */
    public function getBuiltInFormatCode() {
		if ($this->_isSupervisor) {
			return $this->getSharedComponent()->getBuiltInFormatCode();
		}
    	return $this->_builtInFormatCode;
    }

    /**
     * Set Built-In Format Code
     *
     * @param int $pValue
     * @return ExcelStyleNumberFormat
     */
    public function setBuiltInFormatCode($pValue = 0) {

		if ($this->_isSupervisor) {
			$styleArray = $this->getStyleArray(array('code' => self::builtInFormatCode($pValue)));
			$this->getActiveSheet()->getStyle($this->getXSelectedCells())->applyFromArray($styleArray);
		} else {
			$this->_builtInFormatCode = $pValue;
			$this->_formatCode = self::builtInFormatCode($pValue);
		}
		return $this;
    }

    /**
     * Fill built-in format codes
     */
    private static function fillBuiltInFormatCodes()
    {
    	// Built-in format codes
    	if (is_null(self::$_builtInFormats)) {
			self::$_builtInFormats = array();

			// General
			self::$_builtInFormats[0] = 'General';
			self::$_builtInFormats[1] = '0';
			self::$_builtInFormats[2] = '0.00';
			self::$_builtInFormats[3] = '#,##0';
			self::$_builtInFormats[4] = '#,##0.00';

			self::$_builtInFormats[9] = '0%';
			self::$_builtInFormats[10] = '0.00%';
			self::$_builtInFormats[11] = '0.00E+00';
			self::$_builtInFormats[12] = '# ?/?';
			self::$_builtInFormats[13] = '# ??/??';
			self::$_builtInFormats[14] = 'mm-dd-yy';
			self::$_builtInFormats[15] = 'd-mmm-yy';
			self::$_builtInFormats[16] = 'd-mmm';
			self::$_builtInFormats[17] = 'mmm-yy';
			self::$_builtInFormats[18] = 'h:mm AM/PM';
			self::$_builtInFormats[19] = 'h:mm:ss AM/PM';
			self::$_builtInFormats[20] = 'h:mm';
			self::$_builtInFormats[21] = 'h:mm:ss';
			self::$_builtInFormats[22] = 'm/d/yy h:mm';

			self::$_builtInFormats[37] = '#,##0 ;(#,##0)';
			self::$_builtInFormats[38] = '#,##0 ;[Red](#,##0)';
			self::$_builtInFormats[39] = '#,##0.00;(#,##0.00)';
			self::$_builtInFormats[40] = '#,##0.00;[Red](#,##0.00)';

			self::$_builtInFormats[44] = '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)';
			self::$_builtInFormats[45] = 'mm:ss';
			self::$_builtInFormats[46] = '[h]:mm:ss';
			self::$_builtInFormats[47] = 'mmss.0';
			self::$_builtInFormats[48] = '##0.0E+0';
			self::$_builtInFormats[49] = '@';

			// CHT
			self::$_builtInFormats[27] = '[$-404]e/m/d';
			self::$_builtInFormats[30] = 'm/d/yy';
			self::$_builtInFormats[36] = '[$-404]e/m/d';
			self::$_builtInFormats[50] = '[$-404]e/m/d';
			self::$_builtInFormats[57] = '[$-404]e/m/d';

			// THA
			self::$_builtInFormats[59] = 't0';
			self::$_builtInFormats[60] = 't0.00';
			self::$_builtInFormats[61] = 't#,##0';
			self::$_builtInFormats[62] = 't#,##0.00';
			self::$_builtInFormats[67] = 't0%';
			self::$_builtInFormats[68] = 't0.00%';
			self::$_builtInFormats[69] = 't# ?/?';
			self::$_builtInFormats[70] = 't# ??/??';

			// Flip array (for faster lookups)
			self::$_flippedBuiltInFormats = array_flip(self::$_builtInFormats);
    	}
    }

    /**
     * Get built-in format code
     *
     * @param	int		$pIndex
     * @return	string
     */
    public static function builtInFormatCode($pIndex) {
    	// Clean parameter
		$pIndex = intval($pIndex);

		// Ensure built-in format codes are available
    	self::fillBuiltInFormatCodes();

		// Lookup format code
		if (array_key_exists($pIndex, self::$_builtInFormats)) {
			return self::$_builtInFormats[$pIndex];
		}

    	return '';
    }

    /**
     * Get built-in format code index
     *
     * @param	string		$formatCode
     * @return	int|boolean
     */
    public static function builtInFormatCodeIndex($formatCode) {
    	// Ensure built-in format codes are available
    	self::fillBuiltInFormatCodes();

		// Lookup format code
		if (array_key_exists($formatCode, self::$_flippedBuiltInFormats)) {
			return self::$_flippedBuiltInFormats[$formatCode];
		}

    	return false;
    }

	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */
	public function getHashCode() {
		if ($this->_isSupervisor) {
			return $this->getSharedComponent()->getHashCode();
		}
    	return md5(
    		  $this->_formatCode
    		. $this->_builtInFormatCode
    		. __CLASS__
    	);
    }

	private static $_dateFormatReplacements = array(
			// first remove escapes related to non-format characters
			'\\'	=> '',
			//	12-hour suffix
			'am/pm'	=> 'A',
			//	4-digit year
			'yyyy'	=> 'Y',
			//	2-digit year
			'yy'	=> 'y',
			//	first letter of month - no php equivalent
			'mmmmm'	=> 'M',
			//	full month name
			'mmmm'	=> 'F',
			//	short month name
			'mmm'	=> 'M',
			//	mm is minutes if time or month w/leading zero
			':mm'	=> ':i',
			//	month leading zero
			'mm'	=> 'm',
			//	month no leading zero
			'm'		=> 'n',
			//	full day of week name
			'dddd'	=> 'l',
			//	short day of week name
			'ddd'	=> 'D',
			//	days leading zero
			'dd'	=> 'd',
			//	days no leading zero
			'd'		=> 'j',
			//	seconds
			'ss'	=> 's',
			//	fractional seconds - no php equivalent
			'.s'	=> ''
		);
	private static $_dateFormatReplacements24 = array(
			'hh'	=> 'H',
			'h'		=> 'G'
		);
	private static $_dateFormatReplacements12 = array(
			'hh'	=> 'h',
			'h'		=> 'g'
		);

	/**
	 * Convert a value in a pre-defined format to a PHP string
	 *
	 * @param mixed 	$value		Value to format
	 * @param string 	$format		Format code
	 * @param array		$callBack	Callback function for additional formatting of string
	 * @return string	Formatted string
	 */
	public static function toFormattedString($value = '', $format = '', $callBack = null) {
		// For now we do not treat strings although section 4 of a format code affects strings
		if (!is_numeric($value)) return $value;

		// For 'General' format code, we just pass the value although this is not entirely the way Excel does it,
		// it seems to round numbers to a total of 10 digits.
		if ($format === 'General') {
			return $value;
		}

		// Get the sections, there can be up to four sections
		$sections = explode(';', $format);

		// Fetch the relevant section depending on whether number is positive, negative, or zero?
		// Text not supported yet.
		// Here is how the sections apply to various values in Excel:
		//   1 section:   [POSITIVE/NEGATIVE/ZERO/TEXT]
		//   2 sections:  [POSITIVE/ZERO/TEXT] [NEGATIVE]
		//   3 sections:  [POSITIVE/TEXT] [NEGATIVE] [ZERO]
		//   4 sections:  [POSITIVE] [NEGATIVE] [ZERO] [TEXT]
		switch (count($sections)) {
			case 1:
				$format = $sections[0];
				break;

			case 2:
				$format = ($value >= 0) ? $sections[0] : $sections[1];
				$value = abs($value); // Use the absolute value
				break;

			case 3:
				$format = ($value > 0) ?
					$sections[0] : ( ($value < 0) ?
						$sections[1] : $sections[2]);
				$value = abs($value); // Use the absolute value
				break;

			case 4:
				$format = ($value > 0) ?
					$sections[0] : ( ($value < 0) ?
						$sections[1] : $sections[2]);
				$value = abs($value); // Use the absolute value
				break;

			default:
				// something is wrong, just use first section
				$format = $sections[0];
				break;
		}

		// Save format with color information for later use below
		$formatColor = $format;

		// Strip color information
		$color_regex = '/^\\[[a-zA-Z]+\\]/';
		$format = preg_replace($color_regex, '', $format);

		// Let's begin inspecting the format and converting the value to a formatted string
		if (preg_match('/^(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy]/i', $format)) { // datetime format
			// dvc: convert Excel formats to PHP date formats

			// strip off first part containing e.g. [$-F800] or [$USD-409]
			// general syntax: [$<Currency string>-<language info>]
			// language info is in hexadecimal
			$format = preg_replace('/^(\[\$[A-Z]*-[0-9A-F]*\])/i', '', $format);

			// OpenOffice.org uses upper-case number formats, e.g. 'YYYY', convert to lower-case
			$format = strtolower($format);

			$format = strtr($format,self::$_dateFormatReplacements);
			if (!strpos($format,'A')) {	// 24-hour time format
				$format = strtr($format,self::$_dateFormatReplacements24);
			} else {					// 12-hour time format
				$format = strtr($format,self::$_dateFormatReplacements12);
			}

			$value = gmdate($format, ExcelShared_Date::ExcelToPHP($value));

		} else if (preg_match('/%$/', $format)) { // % number format
			if ($format === self::FORMAT_PERCENTAGE) {
				$value = round( (100 * $value), 0) . '%';
			} else {
				if (preg_match('/\.[#0]+/i', $format, $m)) {
					$s = substr($m[0], 0, 1) . (strlen($m[0]) - 1);
					$format = str_replace($m[0], $s, $format);
				}
				if (preg_match('/^[#0]+/', $format, $m)) {
					$format = str_replace($m[0], strlen($m[0]), $format);
				}
				$format = '%' . str_replace('%', 'f%%', $format);

				$value = sprintf($format, 100 * $value);
			}

		} else {
			if (preg_match ("/^([0-9.,-]+)$/", $value)) {
	 			if ($format === self::FORMAT_CURRENCY_EUR_SIMPLE) {
	 				$value = 'EUR ' . sprintf('%1.2f', $value);

				} else {
					// In Excel formats, "_" is used to add spacing, which we can't do in HTML
					$format = preg_replace('/_./', '', $format);

					// Some non-number characters are escaped with \, which we don't need
					$format = preg_replace("/\\\\/", '', $format);

					// Some non-number strings are quoted, so we'll get rid of the quotes
					$format = preg_replace('/"/', '', $format);

					// TEMPORARY - Convert # to 0
					$format = preg_replace('/\\#/', '0', $format);

					// Find out if we need thousands separator
					$useThousands = preg_match('/,/', $format);
					if ($useThousands) {
						$format = preg_replace('/,/', '', $format);
					}

					if (preg_match('/0?.*\?\/\?/', $format, $m)) {
						//echo 'Format mask is fractional '.$format.' <br />';
						$sign = ($value < 0) ? '-' : '';

						$integerPart = floor(abs($value));
						$decimalPart = trim(fmod(abs($value),1),'0.');
						$decimalLength = strlen($decimalPart);
						$decimalDivisor = pow(10,$decimalLength);

						$GCD = ExcelCalculation_Functions::GCD($decimalPart,$decimalDivisor);

						$adjustedDecimalPart = $decimalPart/$GCD;
						$adjustedDecimalDivisor = $decimalDivisor/$GCD;

						if ((strpos($format,'0') !== false) || (substr($format,0,3) == '? ?')) {
							if ($integerPart == 0) { $integerPart = ''; }
							$value = "$sign$integerPart $adjustedDecimalPart/$adjustedDecimalDivisor";
						} else {
							$adjustedDecimalPart += $integerPart * $adjustedDecimalDivisor;
							$value = "$sign$adjustedDecimalPart/$adjustedDecimalDivisor";
						}

					} else {
						// Handle the number itself
						$number_regex = "/(\d+)(\.?)(\d*)/";
						if (preg_match($number_regex, $format, $matches)) {
							$left = $matches[1];
							$dec = $matches[2];
							$right = $matches[3];
							if ($useThousands) {
								$localeconv = localeconv();
								if (($localeconv['thousands_sep'] == '') || ($localeconv['decimal_point'] == '')) {
									$value = number_format($value, strlen($right), $localeconv['mon_decimal_point'], $localeconv['mon_thousands_sep']);
								} else {
									$value = number_format($value, strlen($right), $localeconv['decimal_point'], $localeconv['thousands_sep']);
								}
							} else {
								$sprintf_pattern = "%1." . strlen($right) . "f";
								$value = sprintf($sprintf_pattern, $value);
							}
							$value = preg_replace($number_regex, $value, $format);
						}
					}
				}
			}
		}

		// Additional formatting provided by callback function
		if ($callBack !== null) {
			list($writerInstance, $function) = $callBack;
			$value = $writerInstance->$function($value, $formatColor);
		}

		return $value;
	}
}

/**
 * ExcelStyle_Conditional
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyle_Conditional extends ComparableBase
{
    /* Condition types */
    const CONDITION_NONE = 'none';
    const CONDITION_CELLIS = 'cellIs';
    const CONDITION_CONTAINSTEXT = 'containsText';
    const CONDITION_EXPRESSION = 'expression';
    
    /* Operator types */
    const OPERATOR_NONE = '';
    const OPERATOR_BEGINSWITH = 'beginsWith';
    const OPERATOR_ENDSWITH = 'endsWith';
    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_GREATERTHAN = 'greaterThan';
    const OPERATOR_GREATERTHANOREQUAL = 'greaterThanOrEqual';
    const OPERATOR_LESSTHAN = 'lessThan';
    const OPERATOR_LESSTHANOREQUAL = 'lessThanOrEqual';
    const OPERATOR_NOTEQUAL = 'notEqual';
    const OPERATOR_CONTAINSTEXT = 'containsText';
    const OPERATOR_NOTCONTAINS = 'notContains';
    const OPERATOR_BETWEEN = 'between';
    
    /**
     * Condition type
     *
     * @var int
     */
    private $_conditionType;
    
    /**
     * Operator type
     *
     * @var int
     */
    private $_operatorType;
    
    /**
     * Text
     *
     * @var string
     */
    private $_text;
    
    /**
     * Condition
     *
     * @var string[]
     */
    private $_condition = array();
    
    /**
     * Style
     * 
     * @var ExcelStyle
     */
    private $_style;
    
    /**
     * Create a new ExcelStyle_Conditional
     */
    public function __construct()
    {
        $this->_conditionType = ExcelStyle_Conditional::CONDITION_NONE;
        $this->_operatorType  = ExcelStyle_Conditional::OPERATOR_NONE;
        $this->_text          = null;
        $this->_condition     = array();
        $this->_style         = new ExcelStyle();
    }
    
    /**
     * Get Condition type
     *
     * @return string
     */
    public function getConditionType()
    {
        return $this->_conditionType;
    }
    
    /**
     * Set Condition type
     *
     * @param string $pValue	ExcelStyle_Conditional condition type
     */
    public function setConditionType($pValue = ExcelStyle_Conditional::CONDITION_NONE)
    {
        $this->_conditionType = $pValue;
    }
    
    /**
     * Get Operator type
     *
     * @return string
     */
    public function getOperatorType()
    {
        return $this->_operatorType;
    }
    
    /**
     * Set Operator type
     *
     * @param string $pValue	ExcelStyle_Conditional operator type
     */
    public function setOperatorType($pValue = ExcelStyle_Conditional::OPERATOR_NONE)
    {
        $this->_operatorType = $pValue;
    }
    
    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }
    
    /**
     * Set text
     *
     * @param string $value
     */
    public function setText($value = null)
    {
        $this->_text = $value;
    }
    
    /**
     * Get Condition
     *
     * @deprecated Deprecated, use getConditions instead
     * @return string
     */
    public function getCondition()
    {
        if (isset($this->_condition[0]))
        {
            return $this->_condition[0];
        }
        
        return '';
    }
    
    /**
     * Set Condition
     *
     * @deprecated Deprecated, use setConditions instead
     * @param string $pValue	Condition
     */
    public function setCondition($pValue = '')
    {
        if (!is_array($pValue))
            $pValue = array(
                $pValue
            );
        
        $this->setConditions($pValue);
    }
    
    /**
     * Get Conditions
     *
     * @return string[]
     */
    public function getConditions()
    {
        return $this->_condition;
    }
    
    /**
     * Set Conditions
     *
     * @param string[] $pValue	Condition
     */
    public function setConditions($pValue)
    {
        if (!is_array($pValue))
            $pValue = array(
                $pValue
            );
        
        $this->_condition = $pValue;
    }
    
    /**
     * Add Condition
     *
     * @param string $pValue	Condition
     */
    public function addCondition($pValue = '')
    {
        $this->_condition[] = $pValue;
    }
    
    /**
     * Get Style
     *
     * @return ExcelStyle
     */
    public function getStyle()
    {
        return $this->_style;
    }
    
    /**
     * Set Style
     *
     * @param 	ExcelStyle $pValue
     * @throws 	Exception
     */
    public function setStyle(ExcelStyle $pValue = null)
    {
        $this->_style = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_conditionType . $this->_operatorType . implode(';', $this->_condition) . $this->_style->getHashCode() . __CLASS__);
    }
}

/**
 * ExcelStyle_Protection
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelStyleProtection extends ExcelStyleBase
{
    /** Protection styles */
    const PROTECTION_INHERIT = 'inherit';
    const PROTECTION_PROTECTED = 'protected';
    const PROTECTION_UNPROTECTED = 'unprotected';
    
    /**
     * Locked
     *
     * @var string
     */
    private $_locked;
    
    /**
     * Hidden
     *
     * @var string
     */
    private $_hidden;
    
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
     * Create a new ExcelStyle_Protection
     */
    public function __construct()
    {
        // Initialise values
        $this->_locked = self::PROTECTION_INHERIT;
        $this->_hidden = self::PROTECTION_INHERIT;
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
     * Returns the ExcelStyle_Protection that is actual bound to ExcelStyle
     *
     * @return ExcelStyle_Protection
     */
    private function propertyGetBound()
    {
        if (!isset($this->_parent))
            return $this; // I am bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getProtection(); // Another one is bound
        
        return $this; // No one is bound yet
    }
    
    /**
     * Property Begin Bind
     *
     * If no ExcelStyle_Protection has been bound to ExcelStyle then bind this one. Return the actual bound one.
     *
     * @return ExcelStyle_Protection
     */
    private function propertyBeginBind()
    {
        if (!isset($this->_parent))
            return $this; // I am already bound
        
        if ($this->_parent->propertyIsBound($this->_parentPropertyName))
            return $this->_parent->getProtection(); // Another one is already bound
        
        $this->_parent->propertyCompleteBind($this, $this->_parentPropertyName); // Bind myself
        $this->_parent = null;
        
        return $this;
    }
    
    /**
     * Apply styles from array
     *
     * <code>
     * $objPHPExcel->getActiveSheet()->getStyle('B2')->getLocked()->applyFromArray( array('locked' => true, 'hidden' => false) );
     * </code>
     *
     * @param	array	$pStyles	Array containing style information
     * @throws	Exception
     */
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles))
        {
            if (array_key_exists('locked', $pStyles))
            {
                $this->setLocked($pStyles['locked']);
            }
            if (array_key_exists('hidden', $pStyles))
            {
                $this->setHidden($pStyles['hidden']);
            }
        }
        else
        {
            throw new Exception("Invalid style array passed.");
        }
    }
    
    /**
     * Get locked
     *
     * @return string
     */
    public function getLocked()
    {
        return $this->propertyGetBound()->_locked;
    }
    
    /**
     * Set locked
     *
     * @param string $pValue
     */
    public function setLocked($pValue = self::PROTECTION_INHERIT)
    {
        $this->propertyBeginBind()->_locked = $pValue;
    }
    
    /**
     * Get hidden
     *
     * @return string
     */
    public function getHidden()
    {
        return $this->propertyGetBound()->_hidden;
    }
    
    /**
     * Set hidden
     *
     * @param string $pValue
     */
    public function setHidden($pValue = self::PROTECTION_INHERIT)
    {
        $this->propertyBeginBind()->_hidden = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        $property = $this->propertyGetBound();
        return md5($property->_locked . $property->_hidden . __CLASS__);
    }
}
?>