<?php
require_once 'calculation/Functions.php';

/*
PARTLY BASED ON:
	Copyright (c) 2007 E. W. Bachtal, Inc.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software
	and associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:

	  The above copyright notice and this permission notice shall be included in all copies or substantial
	  portions of the Software.

	The software is provided "as is", without warranty of any kind, express or implied, including but not
	limited to the warranties of merchantability, fitness for a particular purpose and noninfringement. In
	no event shall the authors or copyright holders be liable for any claim, damages or other liability,
	whether in an action of contract, tort or otherwise, arising from, out of or in connection with the
	software or the use or other dealings in the software.

	http://ewbi.blogs.com/develops/2007/03/excel_formula_p.html
	http://ewbi.blogs.com/develops/2004/12/excel_formula_p.html
*/

/**
 * ExcelCalculation_FormulaToken
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCalculation_FormulaToken extends ObjectBase
{
	/* Token types */
	const TOKEN_TYPE_NOOP					= 'Noop';
	const TOKEN_TYPE_OPERAND				= 'Operand';
	const TOKEN_TYPE_FUNCTION				= 'Function';
	const TOKEN_TYPE_SUBEXPRESSION			= 'Subexpression';
	const TOKEN_TYPE_ARGUMENT				= 'Argument';
	const TOKEN_TYPE_OPERATORPREFIX			= 'OperatorPrefix';
	const TOKEN_TYPE_OPERATORINFIX			= 'OperatorInfix';
	const TOKEN_TYPE_OPERATORPOSTFIX		= 'OperatorPostfix';
	const TOKEN_TYPE_WHITESPACE				= 'Whitespace';
	const TOKEN_TYPE_UNKNOWN				= 'Unknown';

	/* Token subtypes */
	const TOKEN_SUBTYPE_NOTHING				= 'Nothing';
	const TOKEN_SUBTYPE_START				= 'Start';
	const TOKEN_SUBTYPE_STOP				= 'Stop';
	const TOKEN_SUBTYPE_TEXT				= 'Text';
	const TOKEN_SUBTYPE_NUMBER				= 'Number';
	const TOKEN_SUBTYPE_LOGICAL				= 'Logical';
	const TOKEN_SUBTYPE_ERROR				= 'Error';
	const TOKEN_SUBTYPE_RANGE				= 'Range';
	const TOKEN_SUBTYPE_MATH				= 'Math';
	const TOKEN_SUBTYPE_CONCATENATION		= 'Concatenation';
	const TOKEN_SUBTYPE_INTERSECTION		= 'Intersection';
	const TOKEN_SUBTYPE_UNION				= 'Union';

	/**
	 * Value
	 *
	 * @var string
	 */
	private $_value;

	/**
	 * Token Type (represented by TOKEN_TYPE_*)
	 *
	 * @var string
	 */
	private $_tokenType;

	/**
	 * Token SubType (represented by TOKEN_SUBTYPE_*)
	 *
	 * @var string
	 */
	private $_tokenSubType;

    /**
     * Create a new ExcelCalculation_FormulaToken
     *
     * @param string	$pValue
     * @param string	$pTokenType 	Token type (represented by TOKEN_TYPE_*)
     * @param string	$pTokenSubType 	Token Subtype (represented by TOKEN_SUBTYPE_*)
     */
    public function __construct($pValue, $pTokenType = ExcelCalculation_FormulaToken::TOKEN_TYPE_UNKNOWN, $pTokenSubType = ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_NOTHING)
    {
    	// Initialise values
    	$this->_value				= $pValue;
    	$this->_tokenType			= $pTokenType;
    	$this->_tokenSubType 		= $pTokenSubType;
    }

    /**
     * Get Value
     *
     * @return string
     */
    public function getValue() {
    	return $this->_value;
    }

    /**
     * Set Value
     *
     * @param string	$value
     */
    public function setValue($value) {
    	$this->_value = $value;
    }

    /**
     * Get Token Type (represented by TOKEN_TYPE_*)
     *
     * @return string
     */
    public function getTokenType() {
    	return $this->_tokenType;
    }

    /**
     * Set Token Type
     *
     * @param string	$value
     */
    public function setTokenType($value = ExcelCalculation_FormulaToken::TOKEN_TYPE_UNKNOWN) {
    	$this->_tokenType = $value;
    }

    /**
     * Get Token SubType (represented by TOKEN_SUBTYPE_*)
     *
     * @return string
     */
    public function getTokenSubType() {
    	return $this->_tokenSubType;
    }

    /**
     * Set Token SubType
     *
     * @param string	$value
     */
    public function setTokenSubType($value = ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_NOTHING) {
    	$this->_tokenSubType = $value;
    }
}

/**
 * ExcelCalculation_FormulaParser
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCalculation_FormulaParser extends ObjectBase
{
	/* Character constants */
	const QUOTE_DOUBLE  = '"';
	const QUOTE_SINGLE  = '\'';
	const BRACKET_CLOSE = ']';
	const BRACKET_OPEN  = '[';
	const BRACE_OPEN    = '{';
	const BRACE_CLOSE   = '}';
	const PAREN_OPEN    = '(';
	const PAREN_CLOSE   = ')';
	const SEMICOLON     = ';';
	const WHITESPACE    = ' ';
	const COMMA         = ',';
	const ERROR_START   = '#';

	const OPERATORS_SN 			= "+-";
	const OPERATORS_INFIX 		= "+-*/^&=><";
	const OPERATORS_POSTFIX 	= "%";

	/**
	 * Formula
	 *
	 * @var string
	 */
	private $_formula;

	/**
	 * Tokens
	 *
	 * @var ExcelCalculation_FormulaToken[]
	 */
	private $_tokens = array();

    /**
     * Create a new ExcelCalculation_FormulaParser
     *
     * @param 	string		$pFormula	Formula to parse
     * @throws 	Exception
     */
    public function __construct($pFormula = '')
    {
    	// Check parameters
    	if (is_null($pFormula)) {
    		throw new Exception("Invalid parameter passed: formula");
    	}

    	// Initialise values
    	$this->_formula = trim($pFormula);
    	// Parse!
    	$this->_parseToTokens();
    }

    /**
     * Get Formula
     *
     * @return string
     */
    public function getFormula() {
    	return $this->_formula;
    }

    /**
     * Get Token
     *
     * @param 	int		$pId	Token id
     * @return	string
     * @throws  Exception
     */
    public function getToken($pId = 0) {
    	if (isset($this->_tokens[$pId])) {
    		return $this->_tokens[$pId];
    	} else {
    		throw new Exception("Token with id $pId does not exist.");
    	}
    }

    /**
     * Get Token count
     *
     * @return string
     */
    public function getTokenCount() {
    	return count($this->_tokens);
    }

    /**
     * Get Tokens
     *
     * @return ExcelCalculation_FormulaToken[]
     */
    public function getTokens() {
    	return $this->_tokens;
    }

    /**
     * Parse to tokens
     */
    private function _parseToTokens()
    {
		// No attempt is made to verify formulas; assumes formulas are derived from Excel, where
		// they can only exist if valid; stack overflows/underflows sunk as nulls without exceptions.

		// Check if the formula has a valid starting =
		$formulaLength = strlen($this->_formula);
		if ($formulaLength < 2 || $this->_formula{0} != '=') return;

		// Helper variables
		$tokens1	= $tokens2 	= $stack = array();
		$inString	= $inPath 	= $inRange 	= $inError = false;
		$token		= $previousToken	= $nextToken	= null;

		$index	= 1;
		$value	= '';

		$ERRORS 			= array("#NULL!", "#DIV/0!", "#VALUE!", "#REF!", "#NAME?", "#NUM!", "#N/A");
		$COMPARATORS_MULTI 	= array(">=", "<=", "<>");

		while ($index < $formulaLength) {
			// state-dependent character evaluation (order is important)

			// double-quoted strings
			// embeds are doubled
			// end marks token
			if ($inString) {
				if ($this->_formula{$index} == ExcelCalculation_FormulaParser::QUOTE_DOUBLE) {
					if ((($index + 2) <= $formulaLength) && ($this->_formula{$index + 1} == ExcelCalculation_FormulaParser::QUOTE_DOUBLE)) {
						$value .= ExcelCalculation_FormulaParser::QUOTE_DOUBLE;
						++$index;
					} else {
						$inString = false;
						$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_TEXT);
						$value = "";
					}
				} else {
					$value .= $this->_formula{$index};
				}
				++$index;
				continue;
			}

			// single-quoted strings (links)
			// embeds are double
			// end does not mark a token
			if ($inPath) {
				if ($this->_formula{$index} == ExcelCalculation_FormulaParser::QUOTE_SINGLE) {
					if ((($index + 2) <= $formulaLength) && ($this->_formula{$index + 1} == ExcelCalculation_FormulaParser::QUOTE_SINGLE)) {
						$value .= ExcelCalculation_FormulaParser::QUOTE_SINGLE;
						++$index;
					} else {
						$inPath = false;
					}
				} else {
					$value .= $this->_formula{$index};
				}
				++$index;
				continue;
			}

			// bracked strings (R1C1 range index or linked workbook name)
			// no embeds (changed to "()" by Excel)
			// end does not mark a token
			if ($inRange) {
				if ($this->_formula{$index} == ExcelCalculation_FormulaParser::BRACKET_CLOSE) {
					$inRange = false;
				}
				$value .= $this->_formula{$index};
				++$index;
				continue;
			}

			// error values
			// end marks a token, determined from absolute list of values
			if ($inError) {
				$value .= $this->_formula{$index};
				++$index;
				if (in_array($value, $ERRORS)) {
					$inError = false;
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_ERROR);
					$value = "";
				}
				continue;
			}

			// scientific notation check
			if (strpos(ExcelCalculation_FormulaParser::OPERATORS_SN, $this->_formula{$index}) !== false) {
				if (strlen($value) > 1) {
					if (preg_match("/^[1-9]{1}(\.[0-9]+)?E{1}$/", $this->_formula{$index}) != 0) {
						$value .= $this->_formula{$index};
						++$index;
						continue;
					}
				}
			}

			// independent character evaluation (order not important)

			// establish state-dependent character evaluations
			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::QUOTE_DOUBLE) {
				if (strlen($value > 0)) {  // unexpected
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_UNKNOWN);
					$value = "";
				}
				$inString = true;
				++$index;
				continue;
 			}

			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::QUOTE_SINGLE) {
				if (strlen($value) > 0) { // unexpected
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_UNKNOWN);
					$value = "";
				}
				$inPath = true;
				++$index;
				continue;
			}

			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::BRACKET_OPEN) {
				$inRange = true;
				$value .= ExcelCalculation_FormulaParser::BRACKET_OPEN;
				++$index;
				continue;
			}

			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::ERROR_START) {
				if (strlen($value) > 0) { // unexpected
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_UNKNOWN);
					$value = "";
				}
				$inError = true;
				$value .= ExcelCalculation_FormulaParser::ERROR_START;
				++$index;
				continue;
			}

			// mark start and end of arrays and array rows
			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::BRACE_OPEN) {
				if (strlen($value) > 0) { // unexpected
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_UNKNOWN);
					$value = "";
				}

				$tmp = new ExcelCalculation_FormulaToken("ARRAY", ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START);
				$tokens1[] = $tmp;
				$stack[] = clone $tmp;

				$tmp = new ExcelCalculation_FormulaToken("ARRAYROW", ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START);
				$tokens1[] = $tmp;
				$stack[] = clone $tmp;

				++$index;
				continue;
			}

			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::SEMICOLON) {
				if (strlen($value) > 0) {
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
					$value = "";
				}

				$tmp = array_pop($stack);
				$tmp->setValue("");
				$tmp->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP);
				$tokens1[] = $tmp;

				$tmp = new ExcelCalculation_FormulaToken(",", ExcelCalculation_FormulaToken::TOKEN_TYPE_ARGUMENT);
				$tokens1[] = $tmp;

				$tmp = new ExcelCalculation_FormulaToken("ARRAYROW", ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START);
				$tokens1[] = $tmp;
				$stack[] = clone $tmp;

				++$index;
				continue;
			}

			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::BRACE_CLOSE) {
				if (strlen($value) > 0) {
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
					$value = "";
				}

				$tmp = array_pop($stack);
				$tmp->setValue("");
				$tmp->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP);
				$tokens1[] = $tmp;

				$tmp = array_pop($stack);
				$tmp->setValue("");
				$tmp->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP);
				$tokens1[] = $tmp;

				++$index;
				continue;
			}

			// trim white-space
			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::WHITESPACE) {
				if (strlen($value) > 0) {
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
					$value = "";
				}
				$tokens1[] = new ExcelCalculation_FormulaToken("", ExcelCalculation_FormulaToken::TOKEN_TYPE_WHITESPACE);
				++$index;
				while (($this->_formula{$index} == ExcelCalculation_FormulaParser::WHITESPACE) && ($index < $formulaLength)) {
					++$index;
				}
				continue;
			}

			// multi-character comparators
			if (($index + 2) <= $formulaLength) {
				if (in_array(substr($this->_formula, $index, 2), $COMPARATORS_MULTI)) {
					if (strlen($value) > 0) {
						$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
						$value = "";
					}
					$tokens1[] = new ExcelCalculation_FormulaToken(substr($this->_formula, $index, 2), ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORINFIX, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_LOGICAL);
					$index += 2;
					continue;
				}
			}

			// standard infix operators
			if (strpos(ExcelCalculation_FormulaParser::OPERATORS_INFIX, $this->_formula{$index}) !== false) {
				if (strlen($value) > 0) {
					$tokens1[] =new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
					$value = "";
				}
				$tokens1[] = new ExcelCalculation_FormulaToken($this->_formula{$index}, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORINFIX);
				++$index;
				continue;
			}

			// standard postfix operators (only one)
			if (strpos(ExcelCalculation_FormulaParser::OPERATORS_POSTFIX, $this->_formula{$index}) !== false) {
				if (strlen($value) > 0) {
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
					$value = "";
				}
				$tokens1[] = new ExcelCalculation_FormulaToken($this->_formula{$index}, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORPOSTFIX);
				++$index;
				continue;
			}

			// start subexpression or function
			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::PAREN_OPEN) {
				if (strlen($value) > 0) {
					$tmp = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START);
					$tokens1[] = $tmp;
					$stack[] = clone $tmp;
					$value = "";
				} else {
					$tmp = new ExcelCalculation_FormulaToken("", ExcelCalculation_FormulaToken::TOKEN_TYPE_SUBEXPRESSION, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START);
					$tokens1[] = $tmp;
					$stack[] = clone $tmp;
				}
				++$index;
				continue;
			}

			// function, subexpression, or array parameters, or operand unions
			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::COMMA) {
				if (strlen($value) > 0) {
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
					$value = "";
				}

				$tmp = array_pop($stack);
				$tmp->setValue("");
				$tmp->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP);
				$stack[] = $tmp;

				if ($tmp->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION) {
					$tokens1[] = new ExcelCalculation_FormulaToken(",", ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORINFIX, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_UNION);
				} else {
					$tokens1[] = new ExcelCalculation_FormulaToken(",", ExcelCalculation_FormulaToken::TOKEN_TYPE_ARGUMENT);
				}
				++$index;
				continue;
			}

			// stop subexpression
			if ($this->_formula{$index} == ExcelCalculation_FormulaParser::PAREN_CLOSE) {
				if (strlen($value) > 0) {
					$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
					$value = "";
				}

				$tmp = array_pop($stack);
				$tmp->setValue("");
				$tmp->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP);
				$tokens1[] = $tmp;

				++$index;
				continue;
			}

        	// token accumulation
			$value .= $this->_formula{$index};
			++$index;
		}

		// dump remaining accumulation
		if (strlen($value) > 0) {
			$tokens1[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND);
		}

		// move tokenList to new set, excluding unnecessary white-space tokens and converting necessary ones to intersections
		$tokenCount = count($tokens1);
		for ($i = 0; $i < $tokenCount; ++$i) {
			$token = $tokens1[$i];
			if (isset($tokens1[$i - 1])) {
				$previousToken = $tokens1[$i - 1];
			} else {
				$previousToken = null;
			}
			if (isset($tokens1[$i + 1])) {
				$nextToken = $tokens1[$i + 1];
			} else {
				$nextToken = null;
			}

			if (is_null($token)) {
				continue;
			}

			if ($token->getTokenType() != ExcelCalculation_FormulaToken::TOKEN_TYPE_WHITESPACE) {
				$tokens2[] = $token;
				continue;
			}

			if (is_null($previousToken)) {
				continue;
			}

			if (! (
					(($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION) && ($previousToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP)) ||
					(($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_SUBEXPRESSION) && ($previousToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP)) ||
					($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND)
				  ) ) {
				continue;
			}

			if (is_null($nextToken)) {
				continue;
			}

			if (! (
					(($nextToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION) && ($nextToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START)) ||
					(($nextToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_SUBEXPRESSION) && ($nextToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START)) ||
					($nextToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND)
				  ) ) {
				continue;
			}

			$tokens2[] = new ExcelCalculation_FormulaToken($value, ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORINFIX, ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_INTERSECTION);
		}

		// move tokens to final list, switching infix "-" operators to prefix when appropriate, switching infix "+" operators
		// to noop when appropriate, identifying operand and infix-operator subtypes, and pulling "@" from function names
		$this->_tokens = array();

		$tokenCount = count($tokens2);
		for ($i = 0; $i < $tokenCount; ++$i) {
			$token = $tokens2[$i];
			if (isset($tokens2[$i - 1])) {
				$previousToken = $tokens2[$i - 1];
			} else {
				$previousToken = null;
			}
			if (isset($tokens2[$i + 1])) {
				$nextToken = $tokens2[$i + 1];
			} else {
				$nextToken = null;
			}

			if (is_null($token)) {
				continue;
			}

			if ($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORINFIX && $token->getValue() == "-") {
				if ($i == 0) {
					$token->setTokenType(ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORPREFIX);
				} else if (
							(($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION) && ($previousToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP)) ||
							(($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_SUBEXPRESSION) && ($previousToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP)) ||
							($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORPOSTFIX) ||
							($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND)
						) {
					$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_MATH);
				} else {
					$token->setTokenType(ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORPREFIX);
				}

				$this->_tokens[] = $token;
				continue;
			}

			if ($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORINFIX && $token->getValue() == "+") {
				if ($i == 0) {
					continue;
				} else if (
							(($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION) && ($previousToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP)) ||
							(($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_SUBEXPRESSION) && ($previousToken->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP)) ||
							($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORPOSTFIX) ||
							($previousToken->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND)
						) {
					$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_MATH);
				} else {
					continue;
				}

				$this->_tokens[] = $token;
				continue;
			}

			if ($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERATORINFIX && $token->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_NOTHING) {
				if (strpos("<>=", substr($token->getValue(), 0, 1)) !== false) {
					$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_LOGICAL);
				} else if ($token->getValue() == "&") {
					$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_CONCATENATION);
				} else {
					$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_MATH);
				}

				$this->_tokens[] = $token;
				continue;
			}

			if ($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND && $token->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_NOTHING) {
				if (!is_numeric($token->getValue())) {
					if (strtoupper($token->getValue()) == "TRUE" || strtoupper($token->getValue() == "FALSE")) {
						$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_LOGICAL);
					} else {
						$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_RANGE);
					}
				} else {
					$token->setTokenSubType(ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_NUMBER);
				}

				$this->_tokens[] = $token;
				continue;
			}

			if ($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION) {
				if (strlen($token->getValue() > 0)) {
					if (substr($token->getValue(), 0, 1) == "@") {
						$token->setValue(substr($token->getValue(), 1));
					}
				}
			}

        	$this->_tokens[] = $token;
		}
    }
}

/**
 * ExcelCalculation_Function
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCalculation_Function extends ObjectBase
{
	/* Function categories */
	const CATEGORY_CUBE						= 'Cube';
	const CATEGORY_DATABASE					= 'Database';
	const CATEGORY_DATE_AND_TIME			= 'Date and Time';
	const CATEGORY_ENGINEERING				= 'Engineering';
	const CATEGORY_FINANCIAL				= 'Financial';
	const CATEGORY_INFORMATION				= 'Information';
	const CATEGORY_LOGICAL					= 'Logical';
	const CATEGORY_LOOKUP_AND_REFERENCE		= 'Lookup and Reference';
	const CATEGORY_MATH_AND_TRIG			= 'Math and Trig';
	const CATEGORY_STATISTICAL				= 'Statistical';
	const CATEGORY_TEXT_AND_DATA			= 'Text and Data';

	/**
	 * Category (represented by CATEGORY_*)
	 *
	 * @var string
	 */
	private $_category;

	/**
	 * Excel name
	 *
	 * @var string
	 */
	private $_excelName;

	/**
	 * PHPExcel name
	 *
	 * @var string
	 */
	private $_phpExcelName;

    /**
     * Create a new ExcelCalculation_Function
     *
     * @param 	string		$pCategory 		Category (represented by CATEGORY_*)
     * @param 	string		$pExcelName		Excel function name
     * @param 	string		$workbookName	PHPExcel function mapping
     * @throws 	Exception
     */
    public function __construct($pCategory = null, $pExcelName = null, $workbookName = null)
    {
    	if (!is_null($pCategory) && !is_null($pExcelName) && !is_null($workbookName)) {
    		// Initialise values
    		$this->_category 		= $pCategory;
    		$this->_excelName 		= $pExcelName;
    		$this->_phpExcelName 	= $workbookName;
    	} else {
    		throw new Exception("Invalid parameters passed.");
    	}
    }

    /**
     * Get Category (represented by CATEGORY_*)
     *
     * @return string
     */
    public function getCategory() {
    	return $this->_category;
    }

    /**
     * Set Category (represented by CATEGORY_*)
     *
     * @param 	string		$value
     * @throws 	Exception
     */
    public function setCategory($value = null) {
    	if (!is_null($value)) {
    		$this->_category = $value;
    	} else {
    		throw new Exception("Invalid parameter passed.");
    	}
    }

    /**
     * Get Excel name
     *
     * @return string
     */
    public function getExcelName() {
    	return $this->_excelName;
    }

    /**
     * Set Excel name
     *
     * @param string	$value
     */
    public function setExcelName($value) {
    	$this->_excelName = $value;
    }

    /**
     * Get PHPExcel name
     *
     * @return string
     */
    public function getPHPExcelName() {
    	return $this->_phpExcelName;
    }

    /**
     * Set PHPExcel name
     *
     * @param string	$value
     */
    public function setPHPExcelName($value) {
    	$this->_phpExcelName = $value;
    }
}

/**
 * ExcelCalculation (Singleton)
 *
 * @todo Convert to WebCore ISingleton
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelCalculation extends HelperBase
{
	/**	Constants				*/
	/**	Regular Expressions		*/
	//	Numeric operand
	const CALCULATION_REGEXP_NUMBER		= '[-+]?\d*\.?\d+(e[-+]?\d+)?';
	//	String operand
	const CALCULATION_REGEXP_STRING		= '"(?:[^"]|"")*"';
	//	Opening bracket
	const CALCULATION_REGEXP_OPENBRACE	= '\(';
	//	Function
	const CALCULATION_REGEXP_FUNCTION	= '@?([A-Z][A-Z0-9\.]*)[\s]*\(';
	//	Cell reference (cell or range of cells, with or without a sheet reference)
	const CALCULATION_REGEXP_CELLREF	= '(((\w*)|(\'[^\']*\')|(\"[^\"]*\"))!)?\$?([a-z]+)\$?(\d+)';
	//	Named Range of cells
	const CALCULATION_REGEXP_NAMEDRANGE	= '(((\w*)|(\'.*\')|(\".*\"))!)?([_A-Z][_A-Z0-9]*)';
	//	Error
	const CALCULATION_REGEXP_ERROR		= '\#[A-Z][A-Z0_\/]*[!\?]?';


	/** constants */
	const RETURN_ARRAY_AS_ERROR = 'error';
	const RETURN_ARRAY_AS_VALUE = 'value';
	const RETURN_ARRAY_AS_ARRAY = 'array';

	private static $returnArrayAsType	= self::RETURN_ARRAY_AS_VALUE;

	/**
	 *	Instance of this class
	 *
	 *	@access	private
	 *	@var ExcelCalculation
	 */
	private static $_instance;


	/**
	 *	Calculation cache
	 *
	 *	@access	private
	 *	@var array
	 */
	private $_calculationCache = array ();


	/**
	 *	Calculation cache enabled
	 *
	 *	@access	private
	 *	@var boolean
	 */
	private $_calculationCacheEnabled = true;


	/**
	 *	Calculation cache expiration time
	 *
	 *	@access	private
	 *	@var float
	 */
	private $_calculationCacheExpirationTime = 0.01;


	/**
	 *	List of operators that can be used within formulae
	 *
	 *	@access	private
	 *	@var array
	 */
	private $_operators			= array('+', '-', '*', '/', '^', '&', '%', '~', '>', '<', '=', '>=', '<=', '<>', '|', ':');


	/**
	 *	List of binary operators (those that expect two operands)
	 *
	 *	@access	private
	 *	@var array
	 */
	private $_binaryOperators	= array('+', '-', '*', '/', '^', '&', '>', '<', '=', '>=', '<=', '<>', '|', ':');

	public $suppressFormulaErrors = false;
	public $formulaError = null;
	public $writeDebugLog = false;
	private $debugLogStack = array();
	public $debugLog = array();


	//	Constant conversion from text name/value to actual (datatyped) value
	private $_ExcelConstants = array('TRUE'		=> True,
									 'FALSE'	=> False,
									 'NULL'		=> Null
									);

	//	PHPExcel functions
	private $_PHPExcelFunctions = array(	// PHPExcel functions
				'ABS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'abs',
												 'argumentCount'	=>	'1'
												),
				'ACCRINT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::ACCRINT',
												 'argumentCount'	=>	'4-7'
												),
				'ACCRINTM'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::ACCRINTM',
												 'argumentCount'	=>	'3-5'
												),
				'ACOS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'acos',
												 'argumentCount'	=>	'1'
												),
				'ACOSH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'acosh',
												 'argumentCount'	=>	'1'
												),
				'ADDRESS'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::CELL_ADDRESS',
												 'argumentCount'	=>	'2-5'
												),
				'AMORDEGRC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::AMORDEGRC',
												 'argumentCount'	=>	'6,7'
												),
				'AMORLINC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::AMORLINC',
												 'argumentCount'	=>	'6,7'
												),
				'AND'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOGICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGICAL_AND',
												 'argumentCount'	=>	'1+'
												),
				'AREAS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'ASC'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'ASIN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'asin',
												 'argumentCount'	=>	'1'
												),
				'ASINH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'asinh',
												 'argumentCount'	=>	'1'
												),
				'ATAN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'atan',
												 'argumentCount'	=>	'1'
												),
				'ATAN2'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::REVERSE_ATAN2',
												 'argumentCount'	=>	'2'
												),
				'ATANH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'atanh',
												 'argumentCount'	=>	'1'
												),
				'AVEDEV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::AVEDEV',
												 'argumentCount'	=>	'1+'
												),
				'AVERAGE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::AVERAGE',
												 'argumentCount'	=>	'1+'
												),
				'AVERAGEA'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::AVERAGEA',
												 'argumentCount'	=>	'1+'
												),
				'AVERAGEIF'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2,3'
												),
				'AVERAGEIFS'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3+'
												),
				'BAHTTEXT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'BESSELI'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::BESSELI',
												 'argumentCount'	=>	'2'
												),
				'BESSELJ'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::BESSELJ',
												 'argumentCount'	=>	'2'
												),
				'BESSELK'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::BESSELK',
												 'argumentCount'	=>	'2'
												),
				'BESSELY'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::BESSELY',
												 'argumentCount'	=>	'2'
												),
				'BETADIST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::BETADIST',
												 'argumentCount'	=>	'3-5'
												),
				'BETAINV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::BETAINV',
												 'argumentCount'	=>	'3-5'
												),
				'BIN2DEC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::BINTODEC',
												 'argumentCount'	=>	'1'
												),
				'BIN2HEX'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::BINTOHEX',
												 'argumentCount'	=>	'1,2'
												),
				'BIN2OCT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::BINTOOCT',
												 'argumentCount'	=>	'1,2'
												),
				'BINOMDIST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::BINOMDIST',
												 'argumentCount'	=>	'4'
												),
				'CEILING'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::CEILING',
												 'argumentCount'	=>	'2'
												),
				'CELL'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1,2'
												),
				'CHAR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::CHARACTER',
												 'argumentCount'	=>	'1'
												),
				'CHIDIST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CHIDIST',
												 'argumentCount'	=>	'2'
												),
				'CHIINV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CHIINV',
												 'argumentCount'	=>	'2'
												),
				'CHITEST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2'
												),
				'CHOOSE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::CHOOSE',
												 'argumentCount'	=>	'2+'
												),
				'CLEAN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::TRIMNONPRINTABLE',
												 'argumentCount'	=>	'1'
												),
				'CODE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::ASCIICODE',
												 'argumentCount'	=>	'1'
												),
				'COLUMN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::COLUMN',
												 'argumentCount'	=>	'-1',
												 'passByReference'	=>	array(true)
												),
				'COLUMNS'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::COLUMNS',
												 'argumentCount'	=>	'1'
												),
				'COMBIN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::COMBIN',
												 'argumentCount'	=>	'2'
												),
				'COMPLEX'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::COMPLEX',
												 'argumentCount'	=>	'2,3'
												),
				'CONCATENATE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::CONCATENATE',
												 'argumentCount'	=>	'1+'
												),
				'CONFIDENCE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CONFIDENCE',
												 'argumentCount'	=>	'3'
												),
				'CONVERT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::CONVERTUOM',
												 'argumentCount'	=>	'3'
												),
				'CORREL'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CORREL',
												 'argumentCount'	=>	'2'
												),
				'COS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'cos',
												 'argumentCount'	=>	'1'
												),
				'COSH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'cosh',
												 'argumentCount'	=>	'1'
												),
				'COUNT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::COUNT',
												 'argumentCount'	=>	'1+'
												),
				'COUNTA'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::COUNTA',
												 'argumentCount'	=>	'1+'
												),
				'COUNTBLANK'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::COUNTBLANK',
												 'argumentCount'	=>	'1'
												),
				'COUNTIF'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::COUNTIF',
												 'argumentCount'	=>	'2'
												),
				'COUNTIFS'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2'
												),
				'COUPDAYBS'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3,4'
												),
				'COUPDAYS'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3,4'
												),
				'COUPDAYSNC'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3,4'
												),
				'COUPNCD'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3,4'
												),
				'COUPNUM'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::COUPNUM',
												 'argumentCount'	=>	'3,4'
												),
				'COUPPCD'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3,4'
												),
				'COVAR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::COVAR',
												 'argumentCount'	=>	'2'
												),
				'CRITBINOM'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CRITBINOM',
												 'argumentCount'	=>	'3'
												),
				'CUBEKPIMEMBER'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_CUBE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'CUBEMEMBER'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_CUBE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'CUBEMEMBERPROPERTY'	=> array('category'			=>	ExcelCalculation_Function::CATEGORY_CUBE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'CUBERANKEDMEMBER'		=> array('category'			=>	ExcelCalculation_Function::CATEGORY_CUBE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'CUBESET'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_CUBE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'CUBESETCOUNT'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_CUBE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'CUBEVALUE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_CUBE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'CUMIPMT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CUMIPMT',
												 'argumentCount'	=>	'6'
												),
				'CUMPRINC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CUMPRINC',
												 'argumentCount'	=>	'6'
												),
				'DATE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DATE',
												 'argumentCount'	=>	'3'
												),
				'DATEDIF'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DATEDIF',
												 'argumentCount'	=>	'2,3'
												),
				'DATEVALUE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DATEVALUE',
												 'argumentCount'	=>	'1'
												),
				'DAVERAGE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DAY'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DAYOFMONTH',
												 'argumentCount'	=>	'1'
												),
				'DAYS360'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DAYS360',
												 'argumentCount'	=>	'2,3'
												),
				'DB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DB',
												 'argumentCount'	=>	'4,5'
												),
				'DCOUNT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DCOUNTA'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DDB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DDB',
												 'argumentCount'	=>	'4,5'
												),
				'DEC2BIN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::DECTOBIN',
												 'argumentCount'	=>	'1,2'
												),
				'DEC2HEX'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::DECTOHEX',
												 'argumentCount'	=>	'1,2'
												),
				'DEC2OCT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::DECTOOCT',
												 'argumentCount'	=>	'1,2'
												),
				'DEGREES'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'rad2deg',
												 'argumentCount'	=>	'1'
												),
				'DELTA'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::DELTA',
												 'argumentCount'	=>	'1,2'
												),
				'DEVSQ'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DEVSQ',
												 'argumentCount'	=>	'1+'
												),
				'DGET'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DISC'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DISC',
												 'argumentCount'	=>	'4,5'
												),
				'DMAX'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DMIN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DOLLAR'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::DOLLAR',
												 'argumentCount'	=>	'1,2'
												),
				'DOLLARDE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DOLLARDE',
												 'argumentCount'	=>	'2'
												),
				'DOLLARFR'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DOLLARFR',
												 'argumentCount'	=>	'2'
												),
				'DPRODUCT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DSTDEV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DSTDEVP'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DSUM'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DURATION'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'5,6'
												),
				'DVAR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'DVARP'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATABASE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'EDATE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::EDATE',
												 'argumentCount'	=>	'2'
												),
				'EFFECT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::EFFECT',
												 'argumentCount'	=>	'2'
												),
				'EOMONTH'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::EOMONTH',
												 'argumentCount'	=>	'2'
												),
				'ERF'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::ERF',
												 'argumentCount'	=>	'1,2'
												),
				'ERFC'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::ERFC',
												 'argumentCount'	=>	'1'
												),
				'ERROR.TYPE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::ERROR_TYPE',
												 'argumentCount'	=>	'1'
												),
				'EVEN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::EVEN',
												 'argumentCount'	=>	'1'
												),
				'EXACT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2'
												),
				'EXP'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'exp',
												 'argumentCount'	=>	'1'
												),
				'EXPONDIST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::EXPONDIST',
												 'argumentCount'	=>	'3'
												),
				'FACT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::FACT',
												 'argumentCount'	=>	'1'
												),
				'FACTDOUBLE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::FACTDOUBLE',
												 'argumentCount'	=>	'1'
												),
				'FALSE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOGICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGICAL_FALSE',
												 'argumentCount'	=>	'0'
												),
				'FDIST'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3'
												),
				'FIND'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::SEARCHSENSITIVE',
												 'argumentCount'	=>	'2,3'
												),
				'FINDB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::SEARCHSENSITIVE',
												 'argumentCount'	=>	'2,3'
												),
				'FINV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3'
												),
				'FISHER'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::FISHER',
												 'argumentCount'	=>	'1'
												),
				'FISHERINV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::FISHERINV',
												 'argumentCount'	=>	'1'
												),
				'FIXED'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::FIXEDFORMAT',
												 'argumentCount'	=>	'1-3'
												),
				'FLOOR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::FLOOR',
												 'argumentCount'	=>	'2'
												),
				'FORECAST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::FORECAST',
												 'argumentCount'	=>	'3'
												),
				'FREQUENCY'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2'
												),
				'FTEST'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2'
												),
				'FV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::FV',
												 'argumentCount'	=>	'3-5'
												),
				'FVSCHEDULE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2'
												),
				'GAMMADIST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::GAMMADIST',
												 'argumentCount'	=>	'4'
												),
				'GAMMAINV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::GAMMAINV',
												 'argumentCount'	=>	'3'
												),
				'GAMMALN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::GAMMALN',
												 'argumentCount'	=>	'1'
												),
				'GCD'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::GCD',
												 'argumentCount'	=>	'1+'
												),
				'GEOMEAN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::GEOMEAN',
												 'argumentCount'	=>	'1+'
												),
				'GESTEP'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::GESTEP',
												 'argumentCount'	=>	'1,2'
												),
				'GETPIVOTDATA'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2+'
												),
				'GROWTH'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::GROWTH',
												 'argumentCount'	=>	'1-4'
												),
				'HARMEAN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::HARMEAN',
												 'argumentCount'	=>	'1+'
												),
				'HEX2BIN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::HEXTOBIN',
												 'argumentCount'	=>	'1,2'
												),
				'HEX2DEC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::HEXTODEC',
												 'argumentCount'	=>	'1'
												),
				'HEX2OCT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::HEXTOOCT',
												 'argumentCount'	=>	'1,2'
												),
				'HLOOKUP'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3,4'
												),
				'HOUR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::HOUROFDAY',
												 'argumentCount'	=>	'1'
												),
				'HYPERLINK'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1,2'
												),
				'HYPGEOMDIST'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::HYPGEOMDIST',
												 'argumentCount'	=>	'4'
												),
				'IF'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOGICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STATEMENT_IF',
												 'argumentCount'	=>	'1-3'
												),
				'IFERROR'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOGICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STATEMENT_IFERROR',
												 'argumentCount'	=>	'1'
												),
				'IMABS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMABS',
												 'argumentCount'	=>	'1'
												),
				'IMAGINARY'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMAGINARY',
												 'argumentCount'	=>	'1'
												),
				'IMARGUMENT'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMARGUMENT',
												 'argumentCount'	=>	'1'
												),
				'IMCONJUGATE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMCONJUGATE',
												 'argumentCount'	=>	'1'
												),
				'IMCOS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMCOS',
												 'argumentCount'	=>	'1'
												),
				'IMDIV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMDIV',
												 'argumentCount'	=>	'2'
												),
				'IMEXP'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMEXP',
												 'argumentCount'	=>	'1'
												),
				'IMLN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMLN',
												 'argumentCount'	=>	'1'
												),
				'IMLOG10'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMLOG10',
												 'argumentCount'	=>	'1'
												),
				'IMLOG2'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMLOG2',
												 'argumentCount'	=>	'1'
												),
				'IMPOWER'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMPOWER',
												 'argumentCount'	=>	'2'
												),
				'IMPRODUCT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMPRODUCT',
												 'argumentCount'	=>	'1+'
												),
				'IMREAL'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMREAL',
												 'argumentCount'	=>	'1'
												),
				'IMSIN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMSIN',
												 'argumentCount'	=>	'1'
												),
				'IMSQRT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMSQRT',
												 'argumentCount'	=>	'1'
												),
				'IMSUB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMSUB',
												 'argumentCount'	=>	'2'
												),
				'IMSUM'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::IMSUM',
												 'argumentCount'	=>	'1+'
												),
				'INDEX'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::INDEX',
												 'argumentCount'	=>	'1-4'
												),
				'INDIRECT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::INDIRECT',
												 'argumentCount'	=>	'1,2',
												 'passCellReference'=>	true
												),
				'INFO'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'INT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::INTVALUE',
												 'argumentCount'	=>	'1'
												),
				'INTERCEPT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::INTERCEPT',
												 'argumentCount'	=>	'2'
												),
				'INTRATE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::INTRATE',
												 'argumentCount'	=>	'4,5'
												),
				'IPMT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::IPMT',
												 'argumentCount'	=>	'4-6'
												),
				'IRR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1,2'
												),
				'ISBLANK'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_BLANK',
												 'argumentCount'	=>	'1'
												),
				'ISERR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_ERR',
												 'argumentCount'	=>	'1'
												),
				'ISERROR'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_ERROR',
												 'argumentCount'	=>	'1'
												),
				'ISEVEN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_EVEN',
												 'argumentCount'	=>	'1'
												),
				'ISLOGICAL'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_LOGICAL',
												 'argumentCount'	=>	'1'
												),
				'ISNA'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_NA',
												 'argumentCount'	=>	'1'
												),
				'ISNONTEXT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_NONTEXT',
												 'argumentCount'	=>	'1'
												),
				'ISNUMBER'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_NUMBER',
												 'argumentCount'	=>	'1'
												),
				'ISODD'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_ODD',
												 'argumentCount'	=>	'1'
												),
				'ISPMT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'4'
												),
				'ISREF'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'ISTEXT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::IS_TEXT',
												 'argumentCount'	=>	'1'
												),
				'JIS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'KURT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::KURT',
												 'argumentCount'	=>	'1+'
												),
				'LARGE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LARGE',
												 'argumentCount'	=>	'2'
												),
				'LCM'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::LCM',
												 'argumentCount'	=>	'1+'
												),
				'LEFT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::LEFT',
												 'argumentCount'	=>	'1,2'
												),
				'LEFTB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::LEFT',
												 'argumentCount'	=>	'1,2'
												),
				'LEN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::STRINGLENGTH',
												 'argumentCount'	=>	'1'
												),
				'LENB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::STRINGLENGTH',
												 'argumentCount'	=>	'1'
												),
				'LINEST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LINEST',
												 'argumentCount'	=>	'1-4'
												),
				'LN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'log',
												 'argumentCount'	=>	'1'
												),
				'LOG'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOG_BASE',
												 'argumentCount'	=>	'1,2'
												),
				'LOG10'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'log10',
												 'argumentCount'	=>	'1'
												),
				'LOGEST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGEST',
												 'argumentCount'	=>	'1-4'
												),
				'LOGINV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGINV',
												 'argumentCount'	=>	'3'
												),
				'LOGNORMDIST'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGNORMDIST',
												 'argumentCount'	=>	'3'
												),
				'LOOKUP'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOOKUP',
												 'argumentCount'	=>	'2,3'
												),
				'LOWER'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOWERCASE',
												 'argumentCount'	=>	'1'
												),
				'MATCH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::MATCH',
												 'argumentCount'	=>	'2,3'
												),
				'MAX'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::MAX',
												 'argumentCount'	=>	'1+'
												),
				'MAXA'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::MAXA',
												 'argumentCount'	=>	'1+'
												),
				'MAXIF'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2+'
												),
				'MDETERM'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::MDETERM',
												 'argumentCount'	=>	'1'
												),
				'MDURATION'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'5,6'
												),
				'MEDIAN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::MEDIAN',
												 'argumentCount'	=>	'1+'
												),
				'MEDIANIF'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2+'
												),
				'MID'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::MID',
												 'argumentCount'	=>	'3'
												),
				'MIDB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::MID',
												 'argumentCount'	=>	'3'
												),
				'MIN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::MIN',
												 'argumentCount'	=>	'1+'
												),
				'MINA'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::MINA',
												 'argumentCount'	=>	'1+'
												),
				'MINIF'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2+'
												),
				'MINUTE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::MINUTEOFHOUR',
												 'argumentCount'	=>	'1'
												),
				'MINVERSE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::MINVERSE',
												 'argumentCount'	=>	'1'
												),
				'MIRR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3'
												),
				'MMULT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::MMULT',
												 'argumentCount'	=>	'2'
												),
				'MOD'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::MOD',
												 'argumentCount'	=>	'2'
												),
				'MODE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::MODE',
												 'argumentCount'	=>	'1+'
												),
				'MONTH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::MONTHOFYEAR',
												 'argumentCount'	=>	'1'
												),
				'MROUND'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::MROUND',
												 'argumentCount'	=>	'2'
												),
				'MULTINOMIAL'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::MULTINOMIAL',
												 'argumentCount'	=>	'1+'
												),
				'N'						=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'NA'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::NA',
												 'argumentCount'	=>	'0'
												),
				'NEGBINOMDIST'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NEGBINOMDIST',
												 'argumentCount'	=>	'3'
												),
				'NETWORKDAYS'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::NETWORKDAYS',
												 'argumentCount'	=>	'2+'
												),
				'NOMINAL'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NOMINAL',
												 'argumentCount'	=>	'2'
												),
				'NORMDIST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NORMDIST',
												 'argumentCount'	=>	'4'
												),
				'NORMINV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NORMINV',
												 'argumentCount'	=>	'3'
												),
				'NORMSDIST'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NORMSDIST',
												 'argumentCount'	=>	'1'
												),
				'NORMSINV'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NORMSINV',
												 'argumentCount'	=>	'1'
												),
				'NOT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOGICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGICAL_NOT',
												 'argumentCount'	=>	'1'
												),
				'NOW'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DATETIMENOW',
												 'argumentCount'	=>	'0'
												),
				'NPER'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NPER',
												 'argumentCount'	=>	'3-5'
												),
				'NPV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::NPV',
												 'argumentCount'	=>	'2+'
												),
				'OCT2BIN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::OCTTOBIN',
												 'argumentCount'	=>	'1,2'
												),
				'OCT2DEC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::OCTTODEC',
												 'argumentCount'	=>	'1'
												),
				'OCT2HEX'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_ENGINEERING,
												 'functionCall'		=>	'ExcelCalculation_Functions::OCTTOHEX',
												 'argumentCount'	=>	'1,2'
												),
				'ODD'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::ODD',
												 'argumentCount'	=>	'1'
												),
				'ODDFPRICE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'8,9'
												),
				'ODDFYIELD'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'8,9'
												),
				'ODDLPRICE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'7,8'
												),
				'ODDLYIELD'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'7,8'
												),
				'OFFSET'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::OFFSET',
												 'argumentCount'	=>	'3,5',
												 'passCellReference'=>	true,
												 'passByReference'	=>	array(true)
												),
				'OR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOGICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGICAL_OR',
												 'argumentCount'	=>	'1+'
												),
				'PEARSON'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::CORREL',
												 'argumentCount'	=>	'2'
												),
				'PERCENTILE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PERCENTILE',
												 'argumentCount'	=>	'2'
												),
				'PERCENTRANK'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PERCENTRANK',
												 'argumentCount'	=>	'2,3'
												),
				'PERMUT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PERMUT',
												 'argumentCount'	=>	'2'
												),
				'PHONETIC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'PI'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'pi',
												 'argumentCount'	=>	'0'
												),
				'PMT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PMT',
												 'argumentCount'	=>	'3-5'
												),
				'POISSON'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::POISSON',
												 'argumentCount'	=>	'3'
												),
				'POWER'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::POWER',
												 'argumentCount'	=>	'2'
												),
				'PPMT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PPMT',
												 'argumentCount'	=>	'4-6'
												),
				'PRICE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'6,7'
												),
				'PRICEDISC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PRICEDISC',
												 'argumentCount'	=>	'4,5'
												),
				'PRICEMAT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PRICEMAT',
												 'argumentCount'	=>	'5,6'
												),
				'PROB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3,4'
												),
				'PRODUCT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::PRODUCT',
												 'argumentCount'	=>	'1+'
												),
				'PROPER'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::PROPERCASE',
												 'argumentCount'	=>	'1'
												),
				'PV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::PV',
												 'argumentCount'	=>	'3-5'
												),
				'QUARTILE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::QUARTILE',
												 'argumentCount'	=>	'2'
												),
				'QUOTIENT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::QUOTIENT',
												 'argumentCount'	=>	'2'
												),
				'RADIANS'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'deg2rad',
												 'argumentCount'	=>	'1'
												),
				'RAND'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::RAND',
												 'argumentCount'	=>	'0'
												),
				'RANDBETWEEN'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::RAND',
												 'argumentCount'	=>	'2'
												),
				'RANK'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::RANK',
												 'argumentCount'	=>	'2,3'
												),
				'RATE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3-6'
												),
				'RECEIVED'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::RECEIVED',
												 'argumentCount'	=>	'4-5'
												),
				'REPLACE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::REPLACE',
												 'argumentCount'	=>	'4'
												),
				'REPLACEB'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::REPLACE',
												 'argumentCount'	=>	'4'
												),
				'REPT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'str_repeat',
												 'argumentCount'	=>	'2'
												),
				'RIGHT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::RIGHT',
												 'argumentCount'	=>	'1,2'
												),
				'RIGHTB'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::RIGHT',
												 'argumentCount'	=>	'1,2'
												),
				'ROMAN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::ROMAN',
												 'argumentCount'	=>	'1,2'
												),
				'ROUND'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'round',
												 'argumentCount'	=>	'2'
												),
				'ROUNDDOWN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::ROUNDDOWN',
												 'argumentCount'	=>	'2'
												),
				'ROUNDUP'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::ROUNDUP',
												 'argumentCount'	=>	'2'
												),
				'ROW'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::ROW',
												 'argumentCount'	=>	'-1',
												 'passByReference'	=>	array(true)
												),
				'ROWS'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::ROWS',
												 'argumentCount'	=>	'1'
												),
				'RSQ'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::RSQ',
												 'argumentCount'	=>	'2'
												),
				'RTD'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1+'
												),
				'SEARCH'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::SEARCHINSENSITIVE',
												 'argumentCount'	=>	'2,3'
												),
				'SEARCHB'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::SEARCHINSENSITIVE',
												 'argumentCount'	=>	'2,3'
												),
				'SECOND'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::SECONDOFMINUTE',
												 'argumentCount'	=>	'1'
												),
				'SERIESSUM'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SERIESSUM',
												 'argumentCount'	=>	'4'
												),
				'SIGN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SIGN',
												 'argumentCount'	=>	'1'
												),
				'SIN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'sin',
												 'argumentCount'	=>	'1'
												),
				'SINH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'sinh',
												 'argumentCount'	=>	'1'
												),
				'SKEW'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::SKEW',
												 'argumentCount'	=>	'1+'
												),
				'SLN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::SLN',
												 'argumentCount'	=>	'3'
												),
				'SLOPE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::SLOPE',
												 'argumentCount'	=>	'2'
												),
				'SMALL'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::SMALL',
												 'argumentCount'	=>	'2'
												),
				'SQRT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'sqrt',
												 'argumentCount'	=>	'1'
												),
				'SQRTPI'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SQRTPI',
												 'argumentCount'	=>	'1'
												),
				'STANDARDIZE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STANDARDIZE',
												 'argumentCount'	=>	'3'
												),
				'STDEV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STDEV',
												 'argumentCount'	=>	'1+'
												),
				'STDEVA'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STDEVA',
												 'argumentCount'	=>	'1+'
												),
				'STDEVP'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STDEVP',
												 'argumentCount'	=>	'1+'
												),
				'STDEVPA'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STDEVPA',
												 'argumentCount'	=>	'1+'
												),
				'STEYX'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::STEYX',
												 'argumentCount'	=>	'2'
												),
				'SUBSTITUTE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUBSTITUTE',
												 'argumentCount'	=>	'3,4'
												),
				'SUBTOTAL'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUBTOTAL',
												 'argumentCount'	=>	'2+'
												),
				'SUM'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUM',
												 'argumentCount'	=>	'1+'
												),
				'SUMIF'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUMIF',
												 'argumentCount'	=>	'2,3'
												),
				'SUMIFS'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												),
				'SUMPRODUCT'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1+'
												),
				'SUMSQ'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUMSQ',
												 'argumentCount'	=>	'1+'
												),
				'SUMX2MY2'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUMX2MY2',
												 'argumentCount'	=>	'2'
												),
				'SUMX2PY2'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUMX2PY2',
												 'argumentCount'	=>	'2'
												),
				'SUMXMY2'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::SUMXMY2',
												 'argumentCount'	=>	'2'
												),
				'SYD'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::SYD',
												 'argumentCount'	=>	'4'
												),
				'T'						=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::RETURNSTRING',
												 'argumentCount'	=>	'1'
												),
				'TAN'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'tan',
												 'argumentCount'	=>	'1'
												),
				'TANH'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'tanh',
												 'argumentCount'	=>	'1'
												),
				'TBILLEQ'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::TBILLEQ',
												 'argumentCount'	=>	'3'
												),
				'TBILLPRICE'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::TBILLPRICE',
												 'argumentCount'	=>	'3'
												),
				'TBILLYIELD'			=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::TBILLYIELD',
												 'argumentCount'	=>	'3'
												),
				'TDIST'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::TDIST',
												 'argumentCount'	=>	'3'
												),
				'TEXT'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::TEXTFORMAT',
												 'argumentCount'	=>	'2'
												),
				'TIME'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::TIME',
												 'argumentCount'	=>	'3'
												),
				'TIMEVALUE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::TIMEVALUE',
												 'argumentCount'	=>	'1'
												),
				'TINV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::TINV',
												 'argumentCount'	=>	'2'
												),
				'TODAY'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DATENOW',
												 'argumentCount'	=>	'0'
												),
				'TRANSPOSE'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::TRANSPOSE',
												 'argumentCount'	=>	'1'
												),
				'TREND'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::TREND',
												 'argumentCount'	=>	'1-4'
												),
				'TRIM'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::TRIMSPACES',
												 'argumentCount'	=>	'1'
												),
				'TRIMMEAN'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::TRIMMEAN',
												 'argumentCount'	=>	'2'
												),
				'TRUE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOGICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::LOGICAL_TRUE',
												 'argumentCount'	=>	'0'
												),
				'TRUNC'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_MATH_AND_TRIG,
												 'functionCall'		=>	'ExcelCalculation_Functions::TRUNC',
												 'argumentCount'	=>	'1,2'
												),
				'TTEST'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'4'
												),
				'TYPE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'UPPER'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::UPPERCASE',
												 'argumentCount'	=>	'1'
												),
				'USDOLLAR'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2'
												),
				'VALUE'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_TEXT_AND_DATA,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'1'
												),
				'VAR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::VARFunc',
												 'argumentCount'	=>	'1+'
												),
				'VARA'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::VARA',
												 'argumentCount'	=>	'1+'
												),
				'VARP'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::VARP',
												 'argumentCount'	=>	'1+'
												),
				'VARPA'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::VARPA',
												 'argumentCount'	=>	'1+'
												),
				'VDB'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'5-7'
												),
				'VERSION'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_INFORMATION,
												 'functionCall'		=>	'ExcelCalculation_Functions::VERSION',
												 'argumentCount'	=>	'0'
												),
				'VLOOKUP'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_LOOKUP_AND_REFERENCE,
												 'functionCall'		=>	'ExcelCalculation_Functions::VLOOKUP',
												 'argumentCount'	=>	'3,4'
												),
				'WEEKDAY'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::DAYOFWEEK',
												 'argumentCount'	=>	'1,2'
												),
				'WEEKNUM'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::WEEKOFYEAR',
												 'argumentCount'	=>	'1,2'
												),
				'WEIBULL'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::WEIBULL',
												 'argumentCount'	=>	'4'
												),
				'WORKDAY'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::WORKDAY',
												 'argumentCount'	=>	'2+'
												),
				'XIRR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'2,3'
												),
				'XNPV'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'3'
												),
				'YEAR'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::YEAR',
												 'argumentCount'	=>	'1'
												),
				'YEARFRAC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_DATE_AND_TIME,
												 'functionCall'		=>	'ExcelCalculation_Functions::YEARFRAC',
												 'argumentCount'	=>	'2,3'
												),
				'YIELD'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'6,7'
												),
				'YIELDDISC'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::YIELDDISC',
												 'argumentCount'	=>	'4,5'
												),
				'YIELDMAT'				=> array('category'			=>	ExcelCalculation_Function::CATEGORY_FINANCIAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::YIELDMAT',
												 'argumentCount'	=>	'5,6'
												),
				'ZTEST'					=> array('category'			=>	ExcelCalculation_Function::CATEGORY_STATISTICAL,
												 'functionCall'		=>	'ExcelCalculation_Functions::DUMMY',
												 'argumentCount'	=>	'?'
												)
			);


	//	Internal functions used for special control purposes
	private $_controlFunctions = array(
				'MKMATRIX'	=> array('argumentCount'	=>	'*',
									 'functionCall'		=>	'self::_mkMatrix'
									)
			);




	/**
	 *	Get an instance of this class
	 *
	 *	@access	public
	 *	@return ExcelCalculation
	 */
	public static function getInstance() {
		if (!isset(self::$_instance) || is_null(self::$_instance)) {
			self::$_instance = new ExcelCalculation();
		}

		return self::$_instance;
	}	//	function getInstance()


	/**
	 *	__clone implementation. Cloning should not be allowed in a Singleton!
	 *
	 *	@access	public
	 *	@throws	Exception
	 */
	public final function __clone() {
		throw new Exception ('Cloning a Singleton is not allowed!');
	}	//	function __clone()


	/**
	 *	Set the Array Return Type (Array or Value of first element in the array)
	 *
	 *	@access	public
	 *	@param	 string	$returnType			Array return type
	 *	@return	 boolean					Success or failure
	 */
	public static function setArrayReturnType($returnType) {
		if (($returnType == self::RETURN_ARRAY_AS_VALUE) ||
			($returnType == self::RETURN_ARRAY_AS_ERROR) ||
			($returnType == self::RETURN_ARRAY_AS_ARRAY)) {
			self::$returnArrayAsType = $returnType;
			return True;
		}
		return False;
	}	//	function setExcelCalendar()


	/**
	 *	Return the Array Return Type (Array or Value of first element in the array)
	 *
	 *	@access	public
	 *	@return	 string		$returnType			Array return type
	 */
	public static function getArrayReturnType() {
		return self::$returnArrayAsType;
	}	//	function getExcelCalendar()


	/**
	 *	Is calculation caching enabled?
	 *
	 *	@access	public
	 *	@return boolean
	 */
	public function getCalculationCacheEnabled() {
		return $this->_calculationCacheEnabled;
	}	//	function getCalculationCacheEnabled()


	/**
	 *	Enable/disable calculation cache
	 *
	 *	@access	public
	 *	@param boolean $pValue
	 */
	public function setCalculationCacheEnabled($pValue = true) {
		$this->_calculationCacheEnabled = $pValue;
		$this->clearCalculationCache();
	}	//	function setCalculationCacheEnabled()


	/**
	 *	Enable calculation cache
	 */
	public function enableCalculationCache() {
		$this->setCalculationCacheEnabled(true);
	}	//	function enableCalculationCache()


	/**
	 *	Disable calculation cache
	 */
	public function disableCalculationCache() {
		$this->setCalculationCacheEnabled(false);
	}	//	function disableCalculationCache()


	/**
	 *	Clear calculation cache
	 */
	public function clearCalculationCache() {
		$this->_calculationCache = array();
	}	//	function clearCalculationCache()


	/**
	 *	Get calculation cache expiration time
	 *
	 *	@return float
	 */
	public function getCalculationCacheExpirationTime() {
		return $this->_calculationCacheExpirationTime;
	}	//	getCalculationCacheExpirationTime()


	/**
	 *	Set calculation cache expiration time
	 *
	 *	@param float $pValue
	 */
	public function setCalculationCacheExpirationTime($pValue = 0.01) {
		$this->_calculationCacheExpirationTime = $pValue;
	}	//	function setCalculationCacheExpirationTime()




	/**
	 *	Wrap string values in quotes
	 *
	 *	@param mixed $value
	 *	@return mixed
	 */
	public static function _wrapResult($value) {
		if (is_string($value)) {
			//	Error values cannot be "wrapped"
			if (preg_match('/^'.self::CALCULATION_REGEXP_ERROR.'$/i', $value, $match)) {
				//	Return Excel errors "as is"
				return $value;
			}
			//	Return strings wrapped in quotes
			return '"'.$value.'"';
		//	Convert numeric errors to NaN error
		} else if((is_float($value)) && ((is_nan($value)) || (is_infinite($value)))) {
			return ExcelCalculation_Functions::NaN();
		}

		return $value;
	}	//	function _wrapResult()


	/**
	 *	Remove quotes used as a wrapper to identify string values
	 *
	 *	@param mixed $value
	 *	@return mixed
	 */
	public static function _unwrapResult($value) {
		if (is_string($value)) {
			if ((strlen($value) > 0) && ($value{0} == '"') && (substr($value,-1) == '"')) {
				return substr($value,1,-1);
			}
		//	Convert numeric errors to NaN error
		} else if((is_float($value)) && ((is_nan($value)) || (is_infinite($value)))) {
			return ExcelCalculation_Functions::NaN();
		}
		return $value;
	}	//	function _unwrapResult()




	/**
	 *	Calculate cell value (using formula from a cell ID)
	 *	Retained for backward compatibility
	 *
	 *	@access	public
	 *	@param	ExcelCell	$pCell	Cell to calculate
	 *	@return	mixed
	 *	@throws	Exception
	 */
	public function calculate(ExcelCell $pCell = null) {
		return $this->calculateCellValue($pCell);
	}	//	function calculate()


	/**
	 *	Calculate the value of a cell formula
	 *
	 *	@access	public
	 *	@param	ExcelCell	$pCell		Cell to calculate
	 *	@param	Boolean			$resetLog	Flag indicating whether the debug log should be reset or not
	 *	@return	mixed
	 *	@throws	Exception
	 */
	public function calculateCellValue(ExcelCell $pCell = null, $resetLog = true) {
		if ($resetLog) {
			//	Initialise the logging settings if requested
			$this->formulaError = null;
			$this->debugLog = $this->debugLogStack = array();

			$returnArrayAsType = self::$returnArrayAsType;
			self::$returnArrayAsType = self::RETURN_ARRAY_AS_ARRAY;
		}

		//	Read the formula from the cell
		if (is_null($pCell)) {
			return null;
		}

		if ($resetLog) {
			self::$returnArrayAsType = $returnArrayAsType;
		}
		//	Execute the calculation for the cell formula
		$result = self::_unwrapResult($this->_calculateFormulaValue($pCell->getValue(), $pCell->getCoordinate(), $pCell));

		if ((is_array($result)) && (self::$returnArrayAsType != self::RETURN_ARRAY_AS_ARRAY)) {
			$testResult = ExcelCalculation_Functions::flattenArray($result);
			if (self::$returnArrayAsType == self::RETURN_ARRAY_AS_ERROR) {
				return ExcelCalculation_Functions::VALUE();
			}
			//	If there's only a single cell in the array, then we allow it
			if (count($testResult) != 1) {
				//	If keys are numeric, then it's a matrix result rather than a cell range result, so we permit it
				$r = array_shift(array_keys($result));
				if (!is_numeric($r)) { return ExcelCalculation_Functions::VALUE(); }
				if (is_array($result[$r])) {
					$c = array_shift(array_keys($result[$r]));
					if (!is_numeric($c)) {
						return ExcelCalculation_Functions::VALUE();
					}
				}
			}
			$result = array_shift($testResult);
		}

		if (is_null($result)) {
			return 0;
		} elseif((is_float($result)) && ((is_nan($result)) || (is_infinite($result)))) {
			return ExcelCalculation_Functions::NaN();
		}
		return $result;
	}	//	function calculateCellValue(


	/**
	 *	Validate and parse a formula string
	 *
	 *	@param	string		$formula		Formula to parse
	 *	@return	array
	 *	@throws	Exception
	 */
	public function parseFormula($formula) {
		//	Basic validation that this is indeed a formula
		//	We return an empty array if not
		$formula = trim($formula);
		if ($formula{0} != '=') return array();
		$formula = trim(substr($formula,1));
		$formulaLength = strlen($formula);
		if ($formulaLength < 1) return array();

		//	Parse the formula and return the token stack
		return $this->_parseFormula($formula);
	}	//	function parseFormula()


	/**
	 *	Calculate the value of a formula
	 *
	 *	@param	string		$formula		Formula to parse
	 *	@return	mixed
	 *	@throws	Exception
	 */
	public function calculateFormula($formula, $cellID=null, ExcelCell $pCell = null) {
		//	Initialise the logging settings
		$this->formulaError = null;
		$this->debugLog = $this->debugLogStack = array();

		//	Disable calculation cacheing because it only applies to cell calculations, not straight formulae
		//	But don't actually flush any cache
		$resetCache = $this->getCalculationCacheEnabled();
		$this->_calculationCacheEnabled = false;
		//	Execute the calculation
		$result = self::_unwrapResult($this->_calculateFormulaValue($formula, $cellID, $pCell));
		//	Reset calculation cacheing to its previous state
		$this->_calculationCacheEnabled = $resetCache;

		return $result;
	}	//	function calculateFormula()


	/**
	 *	Parse a cell formula and calculate its value
	 *
	 *	@param	string			$formula	The formula to parse and calculate
	 *	@param	string			$cellID		The ID (e.g. A3) of the cell that we are calculating
	 *	@param	ExcelCell	$pCell		Cell to calculate
	 *	@return	mixed
	 *	@throws	Exception
	 */
	public function _calculateFormulaValue($formula, $cellID=null, ExcelCell $pCell = null) {
//		echo '<b>'.$cellID.'</b><br />';
		$cellValue = '';

		//	Basic validation that this is indeed a formula
		//	We simply return the "cell value" (formula) if not
		$formula = trim($formula);
		if ($formula{0} != '=') return self::_wrapResult($formula);
		$formula = trim(substr($formula,1));
		$formulaLength = strlen($formula);
		if ($formulaLength < 1) return self::_wrapResult($formula);

		$wsTitle = 'Wrk';
		if (!is_null($pCell)) {
			$wsTitle = urlencode($pCell->getParent()->getTitle());
		}
		// Is calculation cacheing enabled?
		if (!is_null($cellID)) {
			if ($this->_calculationCacheEnabled) {
				// Is the value present in calculation cache?
//				echo 'Testing cache value<br />';
				if (isset($this->_calculationCache[$wsTitle][$cellID])) {
//					echo 'Value is in cache<br />';
					$this->_writeDebug('Testing cache value for cell '.$cellID);
					//	Is cache still valid?
					if ((time() + microtime()) - $this->_calculationCache[$wsTitle][$cellID]['time'] < $this->_calculationCacheExpirationTime) {
//						echo 'Cache time is still valid<br />';
						$this->_writeDebug('Retrieving value for '.$cellID.' from cache');
						// Return the cached result
						$returnValue = $this->_calculationCache[$wsTitle][$cellID]['data'];
//						echo 'Retrieving data value of '.$returnValue.' for '.$cellID.' from cache<br />';
						if (is_array($returnValue)) {
							return array_shift(ExcelCalculation_Functions::flattenArray($returnValue));
						}
						return $returnValue;
					} else {
//						echo 'Cache has expired<br />';
						$this->_writeDebug('Cache value for '.$cellID.' has expired');
						//	Clear the cache if it's no longer valid
						unset($this->_calculationCache[$wsTitle][$cellID]);
					}
				}
			}
		}

		$this->debugLogStack[] = $cellID;
		//	Parse the formula onto the token stack and calculate the value
		$cellValue = $this->_processTokenStack($this->_parseFormula($formula), $cellID, $pCell);
		array_pop($this->debugLogStack);

		// Save to calculation cache
		if (!is_null($cellID)) {
			if ($this->_calculationCacheEnabled) {
				$this->_calculationCache[$wsTitle][$cellID]['time'] = (time() + microtime());
				$this->_calculationCache[$wsTitle][$cellID]['data'] = $cellValue;
			}
		}

		//	Return the calculated value
//		while (is_array($cellValue)) {
//			$cellValue = array_shift($cellValue);
//		}

		return $cellValue;
	}	//	function _calculateFormulaValue()


	/**
	 *	Ensure that paired matrix operands are both matrices and of the same size
	 *
	 *	@param	mixed		&$operand1	First matrix operand
	 *	@param	mixed		&$operand2	Second matrix operand
	 *	@param	integer		$resize		Flag indicating whether the matrices should be resized to match
	 *										and (if so), whether the smaller dimension should grow or the
	 *										larger should shrink.
	 *											0 = no resize
	 *											1 = shrink to fit
	 *											2 = extend to fit
	 */
	private static function _checkMatrixOperands(&$operand1,&$operand2,$resize = 1) {
		//	Examine each of the two operands, and turn them into an array if they aren't one already
		//	Note that this function should only be called if one or both of the operand is already an array
		if (!is_array($operand1)) {
			list($matrixRows,$matrixColumns) = self::_getMatrixDimensions($operand2);
			$operand1 = array_fill(0,$matrixRows,array_fill(0,$matrixColumns,$operand1));
			$resize = 0;
		} elseif (!is_array($operand2)) {
			list($matrixRows,$matrixColumns) = self::_getMatrixDimensions($operand1);
			$operand2 = array_fill(0,$matrixRows,array_fill(0,$matrixColumns,$operand2));
			$resize = 0;
		}

		//	Given two matrices of (potentially) unequal size, convert the smaller in each dimension to match the larger
		if ($resize == 2) {
			self::_resizeMatricesExtend($operand1,$operand2);
		} elseif ($resize == 1) {
			self::_resizeMatricesShrink($operand1,$operand2);
		}
	}	//	function _checkMatrixOperands()


	/**
	 *	Read the dimensions of a matrix, and re-index it with straight numeric keys starting from row 0, column 0
	 *
	 *	@param	mixed		&$matrix		matrix operand
	 *	@return	array		An array comprising the number of rows, and number of columns
	 */
	public static function _getMatrixDimensions(&$matrix) {
		$matrixRows = count($matrix);
		$matrixColumns = 0;
		foreach($matrix as $rowKey => $rowValue) {
			$colCount = count($rowValue);
			if ($colCount > $matrixColumns) {
				$matrixColumns = $colCount;
			}
			$matrix[$rowKey] = array_values($rowValue);
		}
		$matrix = array_values($matrix);
		return array($matrixRows,$matrixColumns);
	}	//	function _getMatrixDimensions()


	/**
	 *	Ensure that paired matrix operands are both matrices of the same size
	 *
	 *	@param	mixed		&$matrix1	First matrix operand
	 *	@param	mixed		&$matrix2	Second matrix operand
	 */
	private static function _resizeMatricesShrink(&$matrix1,&$matrix2) {
		list($matrix1Rows,$matrix1Columns) = self::_getMatrixDimensions($matrix1);
		list($matrix2Rows,$matrix2Columns) = self::_getMatrixDimensions($matrix2);

		if (($matrix2Columns < $matrix1Columns) || ($matrix2Rows < $matrix1Rows)) {
			if ($matrix2Columns < $matrix1Columns) {
				for ($i = 0; $i < $matrix1Rows; ++$i) {
					for ($j = $matrix2Columns; $j < $matrix1Columns; ++$j) {
						unset($matrix1[$i][$j]);
					}
				}
			}
			if ($matrix2Rows < $matrix1Rows) {
				for ($i = $matrix2Rows; $i < $matrix1Rows; ++$i) {
					unset($matrix1[$i]);
				}
			}
		}

		if (($matrix1Columns < $matrix2Columns) || ($matrix1Rows < $matrix2Rows)) {
			if ($matrix1Columns < $matrix2Columns) {
				for ($i = 0; $i < $matrix2Rows; ++$i) {
					for ($j = $matrix1Columns; $j < $matrix2Columns; ++$j) {
						unset($matrix2[$i][$j]);
					}
				}
			}
			if ($matrix1Rows < $matrix2Rows) {
				for ($i = $matrix1Rows; $i < $matrix2Rows; ++$i) {
					unset($matrix2[$i]);
				}
			}
		}
	}	//	function _resizeMatricesShrink()


	/**
	 *	Ensure that paired matrix operands are both matrices of the same size
	 *
	 *	@param	mixed		&$matrix1	First matrix operand
	 *	@param	mixed		&$matrix2	Second matrix operand
	 */
	private static function _resizeMatricesExtend(&$matrix1,&$matrix2) {
		list($matrix1Rows,$matrix1Columns) = self::_getMatrixDimensions($matrix1);
		list($matrix2Rows,$matrix2Columns) = self::_getMatrixDimensions($matrix2);

		if (($matrix2Columns < $matrix1Columns) || ($matrix2Rows < $matrix1Rows)) {
			if ($matrix2Columns < $matrix1Columns) {
				for ($i = 0; $i < $matrix2Rows; ++$i) {
					$x = $matrix2[$i][$matrix2Columns-1];
					for ($j = $matrix2Columns; $j < $matrix1Columns; ++$j) {
						$matrix2[$i][$j] = $x;
					}
				}
			}
			if ($matrix2Rows < $matrix1Rows) {
				$x = $matrix2[$matrix2Rows-1];
				for ($i = 0; $i < $matrix1Rows; ++$i) {
					$matrix2[$i] = $x;
				}
			}
		}

		if (($matrix1Columns < $matrix2Columns) || ($matrix1Rows < $matrix2Rows)) {
			if ($matrix1Columns < $matrix2Columns) {
				for ($i = 0; $i < $matrix1Rows; ++$i) {
					$x = $matrix1[$i][$matrix1Columns-1];
					for ($j = $matrix1Columns; $j < $matrix2Columns; ++$j) {
						$matrix1[$i][$j] = $x;
					}
				}
			}
			if ($matrix1Rows < $matrix2Rows) {
				$x = $matrix1[$matrix1Rows-1];
				for ($i = 0; $i < $matrix2Rows; ++$i) {
					$matrix1[$i] = $x;
				}
			}
		}
	}	//	function _resizeMatricesExtend()


	/**
	 *	Format details of an operand for display in the log (based on operand type)
	 *
	 *	@param	mixed		$value	First matrix operand
	 *	@return	mixed
	 */
	private static function _showValue($value) {
		if (is_array($value)) {
			$returnMatrix = array();
			$pad = $rpad = ', ';
			foreach($value as $row) {
				if (is_array($row)) {
					$returnMatrix[] = implode($pad,$row);
					$rpad = '; ';
				} else {
					$returnMatrix[] = $row;
				}
			}
			return '{ '.implode($rpad,$returnMatrix).' }';
		} elseif(is_bool($value)) {
			return ($value) ? 'TRUE' : 'FALSE';
		}

		return $value;
	}	//	function _showValue()


	/**
	 *	Format type and details of an operand for display in the log (based on operand type)
	 *
	 *	@param	mixed		$value	First matrix operand
	 *	@return	mixed
	 */
	private static function _showTypeDetails($value) {
		switch (gettype($value)) {
			case 'double'	:
			case 'float'	:
				$typeString = 'a floating point number';
				break;
			case 'integer'	:
				$typeString = 'an integer number';
				break;
			case 'boolean'	:
				$typeString = 'a boolean';
				break;
			case 'array'	:
				$typeString = 'a matrix';
				break;
			case 'string'	:
				if ($value == '') {
					return 'an empty string';
				} elseif ($value{0} == '#') {
					return 'a '.$value.' error';
				} else {
					$typeString = 'a string';
				}
				break;
			case 'NULL'	:
				return 'a null value';
		}
		return $typeString.' with a value of '.self::_showValue($value);
	}	//	function _showTypeDetails()


	private static function _convertMatrixReferences($formula) {
		static $matrixReplaceFrom = array('{',';','}');
		static $matrixReplaceTo = array('MKMATRIX(MKMATRIX(','),MKMATRIX(','))');

		//	Convert any Excel matrix references to the MKMATRIX() function
		if (strpos($formula,'{') !== false) {
			//	Open and Closed counts used for trapping mismatched braces in the formula
			$openCount = $closeCount = 0;
			//	If there is the possibility of braces within a quoted string, then we don't treat those as matrix indicators
			if (strpos($formula,'"') !== false) {
				//	So instead we skip replacing in any quoted strings by only replacing in every other array element after we've exploded
				//		the formula
				$temp = explode('"',$formula);
				$i = 0;
				foreach($temp as &$value) {
					//	Only count/replace in alternate array entries
					if (($i++ % 2) == 0) {
						$openCount += substr_count($value,'{');
						$closeCount += substr_count($value,'}');
						$value = str_replace($matrixReplaceFrom,$matrixReplaceTo,$value);
					}
				}
				unset($value);
				//	Then rebuild the formula string
				$formula = implode('"',$temp);
			} else {
				//	If there's no quoted strings, then we do a simple count/replace
				$openCount += substr_count($formula,'{');
				$closeCount += substr_count($formula,'}');
				$formula = str_replace($matrixReplaceFrom,$matrixReplaceTo,$formula);
			}
			//	Trap for mismatched braces and trigger an appropriate error
			if ($openCount < $closeCount) {
				if ($openCount > 0) {
					return $this->_raiseFormulaError("Formula Error: Mismatched matrix braces '}'");
				} else {
					return $this->_raiseFormulaError("Formula Error: Unexpected '}' encountered");
				}
			} elseif ($openCount > $closeCount) {
				if ($closeCount > 0) {
					return $this->_raiseFormulaError("Formula Error: Mismatched matrix braces '{'");
				} else {
					return $this->_raiseFormulaError("Formula Error: Unexpected '{' encountered");
				}
			}
		}

		return $formula;
	}	//	function _convertMatrixReferences()


	private static function _mkMatrix() {
		return func_get_args();
	}	//	function _mkMatrix()


	// Convert infix to postfix notation
	private function _parseFormula($formula) {
		if (($formula = self::_convertMatrixReferences(trim($formula))) === false) {
			return false;
		}

		//	Binary Operators
		//	These operators always work on two values
		//	Array key is the operator, the value indicates whether this is a left or right associative operator
		$operatorAssociativity	= array('^' => 0,															//	Exponentiation
										'*' => 0, '/' => 0, 												//	Multiplication and Division
										'+' => 0, '-' => 0,													//	Addition and Subtraction
										'&' => 0,															//	Concatenation
										'|' => 0, ':' => 0,													//	Intersect and Range
										'>' => 0, '<' => 0, '=' => 0, '>=' => 0, '<=' => 0, '<>' => 0		//	Comparison
								 	  );
		//	Comparison (Boolean) Operators
		//	These operators work on two values, but always return a boolean result
		$comparisonOperators	= array('>', '<', '=', '>=', '<=', '<>');

		//	Operator Precedence
		//	This list includes all valid operators, whether binary (including boolean) or unary (such as %)
		//	Array key is the operator, the value is its precedence
		$operatorPrecedence	= array(':' => 8,																//	Range
									'|' => 7,																//	Intersect
									'~' => 6,																//	Negation
									'%' => 5,																//	Percentage
									'^' => 4,																//	Exponentiation
									'*' => 3, '/' => 3, 													//	Multiplication and Division
									'+' => 2, '-' => 2,														//	Addition and Subtraction
									'&' => 1,																//	Concatenation
									'>' => 0, '<' => 0, '=' => 0, '>=' => 0, '<=' => 0, '<>' => 0			//	Comparison
								   );

		$regexpMatchString = '/^('.self::CALCULATION_REGEXP_FUNCTION.
							   '|'.self::CALCULATION_REGEXP_NUMBER.
							   '|'.self::CALCULATION_REGEXP_STRING.
							   '|'.self::CALCULATION_REGEXP_OPENBRACE.
							   '|'.self::CALCULATION_REGEXP_CELLREF.
							   '|'.self::CALCULATION_REGEXP_NAMEDRANGE.
							   '|'.self::CALCULATION_REGEXP_ERROR.
							 ')/i';

		//	Start with initialisation
		$index = 0;
		$stack = new ExcelToken_Stack;
		$output = array();
		$expectingOperator = false;					//	We use this test in syntax-checking the expression to determine when a
													//		- is a negation or + is a positive operator rather than an operation
		$expectingOperand = false;					//	We use this test in syntax-checking the expression to determine whether an operand
													//		should be null in a function call
		//	The guts of the lexical parser
		//	Loop through the formula extracting each operator and operand in turn
		while(True) {
//			echo 'Assessing Expression <b>'.substr($formula, $index).'</b><br />';
			$opCharacter = $formula{$index};	//	Get the first character of the value at the current index position
//			echo 'Initial character of expression block is '.$opCharacter.'<br />';
			if ((in_array($opCharacter, $comparisonOperators)) && (strlen($formula) > $index) && (in_array($formula{$index+1}, $comparisonOperators))) {
				$opCharacter .= $formula{++$index};
//				echo 'Initial character of expression block is comparison operator '.$opCharacter.'<br />';
			}

			//	Find out if we're currently at the beginning of a number, variable, cell reference, function, parenthesis or operand
			$isOperandOrFunction = preg_match($regexpMatchString, substr($formula, $index), $match);
//			echo '$isOperandOrFunction is '.(($isOperandOrFunction)?'True':'False').'<br />';

			if ($opCharacter == '-' && !$expectingOperator) {				//	Is it a negation instead of a minus?
//				echo 'Element is a Negation operator<br />';
				$stack->push('Unary Operator','~');							//	Put a negation on the stack
				++$index;													//		and drop the negation symbol
			} elseif ($opCharacter == '%' && $expectingOperator) {
//				echo 'Element is a Percentage operator<br />';
				$stack->push('Unary Operator','%');							//	Put a percentage on the stack
				++$index;
			} elseif ($opCharacter == '+' && !$expectingOperator) {			//	Positive (rather than plus) can be discarded?
//				echo 'Element is a Positive number, not Plus operator<br />';
				++$index;													//	Drop the redundant plus symbol
			} elseif (($opCharacter == '~') && (!$isOperandOrFunction)) {					//	We have to explicitly deny a tilde, because it's legal
				return $this->_raiseFormulaError("Formula Error: Illegal character '~'");	//		on the stack but not in the input expression

			} elseif ((in_array($opCharacter, $this->_operators) or $isOperandOrFunction) && $expectingOperator) {	//	Are we putting an operator on the stack?
//				echo 'Element with value '.$opCharacter.' is an Operator<br />';
				while($stack->count() > 0 &&
					($o2 = $stack->last()) &&
					in_array($o2['value'], $this->_operators) &&
					($operatorAssociativity[$opCharacter] ? $operatorPrecedence[$opCharacter] < $operatorPrecedence[$o2['value']] : $operatorPrecedence[$opCharacter] <= $operatorPrecedence[$o2['value']])) {
					$output[] = $stack->pop();								//	Swap operands and higher precedence operators from the stack to the output
				}
				$stack->push('Binary Operator',$opCharacter);	//	Finally put our current operator onto the stack
				++$index;
				$expectingOperator = false;

			} elseif ($opCharacter == ')' && $expectingOperator) {			//	Are we expecting to close a parenthesis?
//				echo 'Element is a Closing bracket<br />';
				$expectingOperand = false;
				while (($o2 = $stack->pop()) && $o2['value'] != '(') {		//	Pop off the stack back to the last (
					if (is_null($o2)) return $this->_raiseFormulaError('Formula Error: Unexpected closing brace ")"');
					else $output[] = $o2;
				}
				$d = $stack->last(2);
				if (preg_match('/^'.self::CALCULATION_REGEXP_FUNCTION.'$/i', $d['value'], $matches)) {	//	Did this parenthesis just close a function?
					$functionName = $matches[1];										//	Get the function name
//					echo 'Closed Function is '.$functionName.'<br />';
					$d = $stack->pop();
					$argumentCount = $d['value'];		//	See how many arguments there were (argument count is the next value stored on the stack)
//					if ($argumentCount == 0) {
//						echo 'With no arguments<br />';
//					} elseif ($argumentCount == 1) {
//						echo 'With 1 argument<br />';
//					} else {
//						echo 'With '.$argumentCount.' arguments<br />';
//					}
					$output[] = $d;						//	Dump the argument count on the output
					$output[] = $stack->pop();			//	Pop the function and push onto the output
					if (array_key_exists($functionName, $this->_controlFunctions)) {
//						echo 'Built-in function '.$functionName.'<br />';
						$expectedArgumentCount = $this->_controlFunctions[$functionName]['argumentCount'];
						$functionCall = $this->_controlFunctions[$functionName]['functionCall'];
					} elseif (array_key_exists($functionName, $this->_PHPExcelFunctions)) {
//						echo 'PHPExcel function '.$functionName.'<br />';
						$expectedArgumentCount = $this->_PHPExcelFunctions[$functionName]['argumentCount'];
						$functionCall = $this->_PHPExcelFunctions[$functionName]['functionCall'];
					} else {	// did we somehow push a non-function on the stack? this should never happen
						return $this->_raiseFormulaError("Formula Error: Internal error, non-function on stack");
					}
					//	Check the argument count
					$argumentCountError = False;
					if (is_numeric($expectedArgumentCount)) {
						if ($expectedArgumentCount < 0) {
//							echo '$expectedArgumentCount is between 0 and '.abs($expectedArgumentCount).'<br />';
							if ($argumentCount > abs($expectedArgumentCount)) {
								$argumentCountError = True;
								$expectedArgumentCountString = 'no more than '.abs($expectedArgumentCount);
							}
						} else {
//							echo '$expectedArgumentCount is numeric '.$expectedArgumentCount.'<br />';
							if ($argumentCount != $expectedArgumentCount) {
								$argumentCountError = True;
								$expectedArgumentCountString = $expectedArgumentCount;
							}
						}
					} elseif ($expectedArgumentCount != '*') {
						$isOperandOrFunction = preg_match('/(\d*)([-+,])(\d*)/',$expectedArgumentCount,$argMatch);
//						print_r($argMatch);
//						echo '<br />';
						switch ($argMatch[2]) {
							case '+' :
								if ($argumentCount < $argMatch[1]) {
									$argumentCountError = True;
									$expectedArgumentCountString = $argMatch[1].' or more ';
								}
								break;
							case '-' :
								if (($argumentCount < $argMatch[1]) || ($argumentCount > $argMatch[3])) {
									$argumentCountError = True;
									$expectedArgumentCountString = 'between '.$argMatch[1].' and '.$argMatch[3];
								}
								break;
							case ',' :
								if (($argumentCount != $argMatch[1]) && ($argumentCount != $argMatch[3])) {
									$argumentCountError = True;
									$expectedArgumentCountString = 'either '.$argMatch[1].' or '.$argMatch[3];
								}
								break;
						}
					}
					if ($argumentCountError) {
						return $this->_raiseFormulaError("Formula Error: Wrong number of arguments for $functionName() function: $argumentCount given, ".$expectedArgumentCountString." expected");
					}
				}
				++$index;

			} elseif ($opCharacter == ',') {			//	Is this the comma separator for function arguments?
//				echo 'Element is a Function argument separator<br />';
				while (($o2 = $stack->pop()) && $o2['value'] != '(') {		//	Pop off the stack back to the last (
					if (is_null($o2)) return $this->_raiseFormulaError("Formula Error: Unexpected ','");
					else $output[] = $o2;	// pop the argument expression stuff and push onto the output
				}
				//	If we've a comma when we're expecting an operand, then what we actually have is a null operand;
				//		so push a null onto the stack
				if (($expectingOperand) || (!$expectingOperator)) {
					$output[] = array('type' => 'NULL Value', 'value' => $this->_ExcelConstants['NULL'], 'reference' => NULL);
				}
				// make sure there was a function
				$d = $stack->last(2);
				if (!preg_match('/^'.self::CALCULATION_REGEXP_FUNCTION.'$/i', $d['value'], $matches))
					return $this->_raiseFormulaError("Formula Error: Unexpected ','");
				$d = $stack->pop();
				$stack->push($d['type'],++$d['value'],$d['reference']);	// increment the argument count
				$stack->push('Brace', '(');	// put the ( back on, we'll need to pop back to it again
				$expectingOperator = false;
				$expectingOperand = true;
				++$index;

			} elseif ($opCharacter == '(' && !$expectingOperator) {
//				echo 'Element is an Opening Bracket<br />';
				$stack->push('Brace', '(');
				++$index;

			} elseif ($isOperandOrFunction && !$expectingOperator) {	// do we now have a function/variable/number?
				$expectingOperator = true;
				$expectingOperand = false;
				$val = $match[1];
				$length = strlen($val);
//				echo 'Element with value '.$val.' is an Operand, Variable, Constant, String, Number, Cell Reference or Function<br />';

				if (preg_match('/^'.self::CALCULATION_REGEXP_FUNCTION.'$/i', $val, $matches)) {
					$val = preg_replace('/\s/','',$val);
//					echo 'Element '.$val.' is a Function<br />';
					if (array_key_exists(strtoupper($matches[1]), $this->_PHPExcelFunctions) || array_key_exists(strtoupper($matches[1]), $this->_controlFunctions)) {	// it's a func
						$stack->push('Function', strtoupper($val));
						$ax = preg_match('/^\s*(\s*\))/i', substr($formula, $index+$length), $amatch);
						if ($ax) {
							$stack->push('Operand Count for Function '.strtoupper($val).')', 0);
							$expectingOperator = true;
						} else {
							$stack->push('Operand Count for Function '.strtoupper($val).')', 1);
							$expectingOperator = false;
						}
						$stack->push('Brace', '(');
					} else {	// it's a var w/ implicit multiplication
						$output[] = array('type' => 'Value', 'value' => $matches[1], 'reference' => NULL);
					}
				} elseif (preg_match('/^'.self::CALCULATION_REGEXP_CELLREF.'$/i', $val, $matches)) {
//					echo 'Element '.$val.' is a Cell reference<br />';
//					Watch for this case-change when modifying to allow cell references in different worksheets...
//						Should only be applied to the actual cell column, not the worksheet name
					$cellRef = strtoupper($val);
//					$output[] = $cellRef;
					$output[] = array('type' => 'Cell Reference', 'value' => $val, 'reference' => $cellRef);
//					$expectingOperator = false;
				} else {	// it's a variable, constant, string, number or boolean
//					echo 'Element is a Variable, Constant, String, Number or Boolean<br />';
					if ($opCharacter == '"') {
//						echo 'Element is a String<br />';
						$val = str_replace('""','"',$val);
					} elseif (is_numeric($val)) {
//						echo 'Element is a Number<br />';
						if ((strpos($val,'.') !== False) || (stripos($val,'e') !== False)) {
//							echo 'Casting '.$val.' to float<br />';
							$val = (float) $val;
						} else {
//							echo 'Casting '.$val.' to integer<br />';
							$val = (integer) $val;
						}
					} elseif (array_key_exists(trim(strtoupper($val)), $this->_ExcelConstants)) {
						$excelConstant = trim(strtoupper($val));
//						echo 'Element '.$excelConstant.' is an Excel Constant<br />';
						$val = $this->_ExcelConstants[$excelConstant];
					}
					$output[] = array('type' => 'Value', 'value' => $val, 'reference' => NULL);
				}
				$index += $length;

			} elseif ($opCharacter == ')') {	// miscellaneous error checking
				if ($expectingOperand) {
					$output[] = array('type' => 'Null Value', 'value' => $this->_ExcelConstants['NULL'], 'reference' => NULL);
					$expectingOperand = false;
					$expectingOperator = True;
				} else {
					return $this->_raiseFormulaError("Formula Error: Unexpected ')'");
				}
			} elseif (in_array($opCharacter, $this->_operators) && !$expectingOperator) {
				return $this->_raiseFormulaError("Formula Error: Unexpected operator '$opCharacter'");
			} else {	// I don't even want to know what you did to get here
				return $this->_raiseFormulaError("Formula Error: An unexpected error occured");
			}
			//	Test for end of formula string
			if ($index == strlen($formula)) {
				//	Did we end with an operator?.
				//	Only valid for the % unary operator
				if ((in_array($opCharacter, $this->_operators)) && ($opCharacter != '%')) {
					return $this->_raiseFormulaError("Formula Error: Operator '$opCharacter' has no operands");
				} else {
					break;
				}
			}
			//	Ignore white space
			if (substr($formula, $index, 1) == ' ') {
				while (substr($formula, $index, 1) == ' ') {
					++$index;
				}
				//	If we're expecting an operator, but only have a space between the previous and next operands (and both are
				//		Cell References) then we have an INTERSECTION operator
//				echo 'Possible Intersect Operator<br />';
				if (($expectingOperator) && (preg_match('/^'.self::CALCULATION_REGEXP_CELLREF.'.*/i', substr($formula, $index), $match)) &&
					($output[count($output)-1]['type'] == 'Cell Reference')) {
//					echo 'Element is an Intersect Operator<br />';
					while($stack->count() > 0 &&
						($o2 = $stack->last()) &&
						in_array($o2['value'], $this->_operators) &&
						@($operatorAssociativity[$opCharacter] ? $operatorPrecedence[$opCharacter] < $operatorPrecedence[$o2['value']] : $operatorPrecedence[$opCharacter] <= $operatorPrecedence[$o2['value']])) {
						$output[] = $stack->pop();								//	Swap operands and higher precedence operators from the stack to the output
					}
					$stack->push('Binary Operator','|');	//	Put an Intersect Operator on the stack
					$expectingOperator = false;
				}
			}
		}

		while (!is_null($op = $stack->pop())) {	// pop everything off the stack and push onto output
			if ($opCharacter['value'] == '(') return $this->_raiseFormulaError("Formula Error: Expecting ')'");	// if there are any opening braces on the stack, then braces were unbalanced
			$output[] = $op;
		}
		return $output;
	}	//	function _parseFormula()


	// evaluate postfix notation
	private function _processTokenStack($tokens, $cellID=null, ExcelCell $pCell = null) {
		if ($tokens == false) return false;

		$stack = new ExcelToken_Stack;

		//	Loop through each token in turn
		foreach ($tokens as $tokenData) {
//			print_r($tokenData);
//			echo '<br />';
			$token = $tokenData['value'];
//			echo '<b>Token is '.$token.'</b><br />';
			// if the token is a binary operator, pop the top two values off the stack, do the operation, and push the result back on the stack
			if (in_array($token, $this->_binaryOperators, true)) {
//				echo 'Token is a binary operator<br />';
				//	We must have two operands, error if we don't
				if (is_null($operand2Data = $stack->pop())) return $this->_raiseFormulaError('Internal error - Operand value missing from stack');
				if (is_null($operand1Data = $stack->pop())) return $this->_raiseFormulaError('Internal error - Operand value missing from stack');
				//	Log what we're doing
				$operand1 = $operand1Data['value'];
				$operand2 = $operand2Data['value'];
				if ($token == ':') {
					$this->_writeDebug('Evaluating Range '.self::_showValue($operand1Data['reference']).$token.self::_showValue($operand2Data['reference']));
				} else {
					$this->_writeDebug('Evaluating '.self::_showValue($operand1).' '.$token.' '.self::_showValue($operand2));
				}
				//	Process the operation in the appropriate manner
				switch ($token) {
					//	Comparison (Boolean) Operators
					case '>'	:			//	Greater than
					case '<'	:			//	Less than
					case '>='	:			//	Greater than or Equal to
					case '<='	:			//	Less than or Equal to
					case '='	:			//	Equality
					case '<>'	:			//	Inequality
						$this->_executeBinaryComparisonOperation($cellID,$operand1,$operand2,$token,$stack);
						break;
					//	Binary Operators
					case ':'	:			//	Range
						$sheet1 = $sheet2 = '';
						if (strpos($operand1Data['reference'],'!') !== false) {
							list($sheet1,$operand1Data['reference']) = explode('!',$operand1Data['reference']);
						} else {
							$sheet1 = $pCell->getParent()->getTitle();
						}
						if (strpos($operand2Data['reference'],'!') !== false) {
							list($sheet2,$operand2Data['reference']) = explode('!',$operand2Data['reference']);
						} else {
							$sheet2 = $sheet1;
						}
						if ($sheet1 == $sheet2) {
							$oData = array_merge(explode(':',$operand1Data['reference']),explode(':',$operand2Data['reference']));
							$oCol = $oRow = array();
							foreach($oData as $oDatum) {
								$oCR = ExcelCell::coordinateFromString($oDatum);
								$oCol[] = ExcelCell::columnIndexFromString($oCR[0]) - 1;
								$oRow[] = $oCR[1];
							}
							$cellRef = ExcelCell::stringFromColumnIndex(min($oCol)).min($oRow).':'.ExcelCell::stringFromColumnIndex(max($oCol)).max($oRow);
							$cellValue = $this->extractCellRange($cellRef, $pCell->getParent()->getParent()->getSheetByName($sheet1), false);
							$stack->push('Cell Reference',$cellValue,$cellRef);
						} else {
							$stack->push('Error',ExcelCalculation_Functions::REF(),NULL);
						}

						break;
					case '+'	:			//	Addition
						$this->_executeNumericBinaryOperation($cellID,$operand1,$operand2,$token,'plusEquals',$stack);
						break;
					case '-'	:			//	Subtraction
						$this->_executeNumericBinaryOperation($cellID,$operand1,$operand2,$token,'minusEquals',$stack);
						break;
					case '*'	:			//	Multiplication
						$this->_executeNumericBinaryOperation($cellID,$operand1,$operand2,$token,'arrayTimesEquals',$stack);
						break;
					case '/'	:			//	Division
						$this->_executeNumericBinaryOperation($cellID,$operand1,$operand2,$token,'arrayRightDivide',$stack);
						break;
					case '^'	:			//	Exponential
						$this->_executeNumericBinaryOperation($cellID,$operand1,$operand2,$token,'power',$stack);
						break;
					case '&'	:			//	Concatenation
						//	If either of the operands is a matrix, we need to treat them both as matrices
						//		(converting the other operand to a matrix if need be); then perform the required
						//		matrix operation
						if (is_bool($operand1)) {
							$operand1 = ($operand1) ? 'TRUE' : 'FALSE';
						}
						if (is_bool($operand2)) {
							$operand2 = ($operand2) ? 'TRUE' : 'FALSE';
						}
						if ((is_array($operand1)) || (is_array($operand2))) {
							//	Ensure that both operands are arrays/matrices
							self::_checkMatrixOperands($operand1,$operand2);
							try {
								//	Convert operand 1 from a PHP array to a matrix
								$matrix = new Matrix($operand1);
								//	Perform the required operation against the operand 1 matrix, passing in operand 2
								$matrixResult = $matrix->concat($operand2);
								$result = $matrixResult->getArray();
							} catch (Exception $ex) {
								$this->_writeDebug('JAMA Matrix Exception: '.$ex->getMessage());
								$result = '#VALUE!';
							}
						} else {
							$result = '"'.str_replace('""','"',self::_unwrapResult($operand1,'"').self::_unwrapResult($operand2,'"')).'"';
						}
						$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($result));
						$stack->push('Value',$result);
						break;
					case '|'	:			//	Intersect
						$rowIntersect = array_intersect_key($operand1,$operand2);
						$cellIntersect = $oCol = $oRow = array();
						foreach(array_keys($rowIntersect) as $col) {
							$oCol[] = ExcelCell::columnIndexFromString($col) - 1;
							$cellIntersect[$col] = array_intersect_key($operand1[$col],$operand2[$col]);
							foreach($cellIntersect[$col] as $row => $data) {
								$oRow[] = $row;
							}
						}
						$cellRef = ExcelCell::stringFromColumnIndex(min($oCol)).min($oRow).':'.ExcelCell::stringFromColumnIndex(max($oCol)).max($oRow);
						$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($cellIntersect));
						$stack->push('Value',$cellIntersect,$cellRef);
						break;
				}

			// if the token is a unary operator, pop one value off the stack, do the operation, and push it back on
			} elseif (($token === '~') || ($token === '%')) {
//				echo 'Token is a unary operator<br />';
				if (is_null($arg = $stack->pop())) return $this->_raiseFormulaError('Internal error - Operand value missing from stack');
				$arg = $arg['value'];
				if ($token === '~') {
//					echo 'Token is a negation operator<br />';
					$this->_writeDebug('Evaluating Negation of '.self::_showValue($arg));
					$multiplier = -1;
				} else {
//					echo 'Token is a percentile operator<br />';
					$this->_writeDebug('Evaluating Percentile of '.self::_showValue($arg));
					$multiplier = 0.01;
				}
				if (is_array($arg)) {
					self::_checkMatrixOperands($arg,$multiplier);
					try {
						$matrix1 = new Matrix($arg);
						$matrixResult = $matrix1->arrayTimesEquals($multiplier);
						$result = $matrixResult->getArray();
					} catch (Exception $ex) {
						$this->_writeDebug('JAMA Matrix Exception: '.$ex->getMessage());
						$result = '#VALUE!';
					}
					$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($result));
					$stack->push('Value',$result);
				} else {
					$this->_executeNumericBinaryOperation($cellID,$multiplier,$arg,'*','arrayTimesEquals',$stack);
				}

			} elseif (preg_match('/^'.self::CALCULATION_REGEXP_CELLREF.'$/i', $token, $matches)) {
				$cellRef = null;
//				echo 'Element '.$token.' is a Cell reference<br />';
				if (isset($matches[8])) {
//					echo 'Reference is a Range of cells<br />';
					if (is_null($pCell)) {
//						We can't access the range, so return a REF error
						$cellValue = ExcelCalculation_Functions::REF();
					} else {
						$cellRef = $matches[6].$matches[7].':'.$matches[9].$matches[10];
						if ($matches[2] > '') {
							$matches[2] = trim($matches[2],"\"'");
//							echo '$cellRef='.$cellRef.' in worksheet '.$matches[2].'<br />';
							$this->_writeDebug('Evaluating Cell Range '.$cellRef.' in worksheet '.$matches[2]);
							$cellValue = $this->extractCellRange($cellRef, $pCell->getParent()->getParent()->getSheetByName($matches[2]), false);
							$this->_writeDebug('Evaluation Result for cells '.$cellRef.' in worksheet '.$matches[2].' is '.self::_showTypeDetails($cellValue));
						} else {
//							echo '$cellRef='.$cellRef.' in current worksheet<br />';
							$this->_writeDebug('Evaluating Cell Range '.$cellRef.' in current worksheet');
							$cellValue = $this->extractCellRange($cellRef, $pCell->getParent(), false);
							$this->_writeDebug('Evaluation Result for cells '.$cellRef.' is '.self::_showTypeDetails($cellValue));
						}
					}
				} else {
//					echo 'Reference is a single Cell<br />';
					if (is_null($pCell)) {
//						We can't access the cell, so return a REF error
						$cellValue = ExcelCalculation_Functions::REF();
					} else {
						$cellRef = $matches[6].$matches[7];
						if ($matches[2] > '') {
							$matches[2] = trim($matches[2],"\"'");
//							echo '$cellRef='.$cellRef.' in worksheet '.$matches[2].'<br />';
							$this->_writeDebug('Evaluating Cell '.$cellRef.' in worksheet '.$matches[2]);
							if ($pCell->getParent()->getParent()->getSheetByName($matches[2])->cellExists($cellRef)) {
								$cellValue = $this->extractCellRange($cellRef, $pCell->getParent()->getParent()->getSheetByName($matches[2]), false);
							} else {
								$cellValue = ExcelCalculation_Functions::REF();
							}
							$this->_writeDebug('Evaluation Result for cell '.$cellRef.' in worksheet '.$matches[2].' is '.self::_showTypeDetails($cellValue));
						} else {
//							echo '$cellRef='.$cellRef.' in current worksheet<br />';
							$this->_writeDebug('Evaluating Cell '.$cellRef.' in current worksheet');
							if ($pCell->getParent()->cellExists($cellRef)) {
								$cellValue = $pCell->getParent()->getCell($cellRef)->getCalculatedValue(false);
							} else {
								$cellValue = NULL;
							}
							$this->_writeDebug('Evaluation Result for cell '.$cellRef.' is '.self::_showTypeDetails($cellValue));
						}
					}
				}
				$stack->push('Value',$cellValue,$cellRef);

			// if the token is a function, pop arguments off the stack, hand them to the function, and push the result back on
			} elseif (preg_match('/^'.self::CALCULATION_REGEXP_FUNCTION.'$/i', $token, $matches)) {
//				echo 'Token is a function<br />';
				$functionName = $matches[1];
				$argCount = $stack->pop();
				$argCount = $argCount['value'];
				if ($functionName != 'MKMATRIX') {
					$this->_writeDebug('Evaluating Function '.$functionName.'() with '.(($argCount == 0) ? 'no' : $argCount).' argument'.(($argCount == 1) ? '' : 's'));
				}
				if ((array_key_exists($functionName, $this->_PHPExcelFunctions)) || (array_key_exists($functionName, $this->_controlFunctions))) {	// function
					if (array_key_exists($functionName, $this->_PHPExcelFunctions)) {
						$functionCall = $this->_PHPExcelFunctions[$functionName]['functionCall'];
						$passByReference = isset($this->_PHPExcelFunctions[$functionName]['passByReference']);
						$passCellReference = isset($this->_PHPExcelFunctions[$functionName]['passCellReference']);
					} elseif (array_key_exists($functionName, $this->_controlFunctions)) {
						$functionCall = $this->_controlFunctions[$functionName]['functionCall'];
						$passByReference = isset($this->_controlFunctions[$functionName]['passByReference']);
						$passCellReference = isset($this->_controlFunctions[$functionName]['passCellReference']);
					}
					// get the arguments for this function
//					echo 'Function '.$functionName.' expects '.$argCount.' arguments<br />';
					$args = $argArrayVals = array();
					for ($i = 0; $i < $argCount; ++$i) {
						$arg = $stack->pop();
						$a = $argCount - $i - 1;
						if (($passByReference) &&
							(isset($this->_PHPExcelFunctions[$functionName]['passByReference'][$a])) &&
							($this->_PHPExcelFunctions[$functionName]['passByReference'][$a])) {
							if (is_null($arg['reference'])) {
								$args[] = $cellID;
								if ($functionName != 'MKMATRIX') { $argArrayVals[] = self::_showValue($cellID); }
							} else {
								$args[] = $arg['reference'];
								if ($functionName != 'MKMATRIX') { $argArrayVals[] = self::_showValue($arg['reference']); }
							}
						} else {
							$args[] = self::_unwrapResult($arg['value']);
							if ($functionName != 'MKMATRIX') { $argArrayVals[] = self::_showValue($arg['value']); }
						}
					}
					//	Reverse the order of the arguments
					krsort($args);
					if (($passByReference) && ($argCount == 0)) {
						$args[] = $cellID;
						$argArrayVals[] = self::_showValue($cellID);
					}
//					echo 'Arguments are: ';
//					print_r($args);
//					echo '<br />';
					if ($functionName != 'MKMATRIX') {
						krsort($argArrayVals);
						$this->_writeDebug('Evaluating '. $functionName.'( '.implode(', ',$argArrayVals).' )');
					}
					//	Process each argument in turn, building the return value as an array
//					if (($argCount == 1) && (is_array($args[1])) && ($functionName != 'MKMATRIX')) {
//						$operand1 = $args[1];
//						$this->_writeDebug('Argument is a matrix: '.self::_showValue($operand1));
//						$result = array();
//						$row = 0;
//						foreach($operand1 as $args) {
//							if (is_array($args)) {
//								foreach($args as $arg) {
//									$this->_writeDebug('Evaluating '. $functionName.'( '.self::_showValue($arg).' )');
//									$r = call_user_func_array($functionCall,$arg);
//									$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($r));
//									$result[$row][] = $r;
//								}
//								++$row;
//							} else {
//								$this->_writeDebug('Evaluating '. $functionName.'( '.self::_showValue($args).' )');
//								$r = call_user_func_array($functionCall,$args);
//								$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($r));
//								$result[] = $r;
//							}
//						}
//					} else {
					//	Process the argument with the appropriate function call
						if ($passCellReference) {
							$args[] = $pCell;
						}
						if (strpos($functionCall,'::') !== false) {
							$result = call_user_func_array(explode('::',$functionCall),$args);
						} else {
							$result = call_user_func_array($functionCall,$args);
						}
//					}
					if ($functionName != 'MKMATRIX') {
						$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($result));
					}
					$stack->push('Value',self::_wrapResult($result));
				}

			} else {
				// if the token is a number, boolean, string or an Excel error, push it onto the stack
				if (array_key_exists(strtoupper($token), $this->_ExcelConstants)) {
					$excelConstant = strtoupper($token);
//					echo 'Token is a PHPExcel constant: '.$excelConstant.'<br />';
					$stack->push('Constant Value',$this->_ExcelConstants[$excelConstant]);
					$this->_writeDebug('Evaluating Constant '.$excelConstant.' as '.self::_showTypeDetails($this->_ExcelConstants[$excelConstant]));
				} elseif ((is_numeric($token)) || (is_bool($token)) || (is_null($token)) || ($token == '') || ($token{0} == '"') || ($token{0} == '#')) {
//					echo 'Token is a number, boolean, string, null or an Excel error<br />';
					$stack->push('Value',$token);
				// if the token is a named range, push the named range name onto the stack
				} elseif (preg_match('/^'.self::CALCULATION_REGEXP_NAMEDRANGE.'$/i', $token, $matches)) {
//					echo 'Token is a named range<br />';
					$namedRange = $matches[6];
//					echo 'Named Range is '.$namedRange.'<br />';
					$this->_writeDebug('Evaluating Named Range '.$namedRange);
					$cellValue = $this->extractNamedRange($namedRange, ((null !== $pCell) ? $pCell->getParent() : null), false);
					$this->_writeDebug('Evaluation Result for named range '.$namedRange.' is '.self::_showTypeDetails($cellValue));
					$stack->push('Named Range',$cellValue,$namedRange);
				} else {
					return $this->_raiseFormulaError("undefined variable '$token'");
				}
			}
		}
		// when we're out of tokens, the stack should have a single element, the final result
		if ($stack->count() != 1) return $this->_raiseFormulaError("internal error");
		$output = $stack->pop();
		$output = $output['value'];

//		if ((is_array($output)) && (self::$returnArrayAsType != self::RETURN_ARRAY_AS_ARRAY)) {
//			return array_shift(ExcelCalculation_Functions::flattenArray($output));
//		}
		return $output;
	}	//	function _processTokenStack()


	private function _validateBinaryOperand($cellID,&$operand,&$stack) {
		//	Numbers, matrices and booleans can pass straight through, as they're already valid
		if (is_string($operand)) {
			//	We only need special validations for the operand if it is a string
			//	Start by stripping off the quotation marks we use to identify true excel string values internally
			if ($operand > '' && $operand{0} == '"') { $operand = self::_unwrapResult($operand); }
			//	If the string is a numeric value, we treat it as a numeric, so no further testing
			if (!is_numeric($operand)) {
				//	If not a numeric, test to see if the value is an Excel error, and so can't be used in normal binary operations
				if ($operand > '' && $operand{0} == '#') {
					$stack->push('Value', $operand);
					$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($operand));
					return false;
				} elseif (!ExcelShared_String::convertToNumberIfFraction($operand)) {
					//	If not a numeric or a fraction, then it's a text string, and so can't be used in mathematical binary operations
					$stack->push('Value', '#VALUE!');
					$this->_writeDebug('Evaluation Result is a '.self::_showTypeDetails('#VALUE!'));
					return false;
				}
			}
		}

		//	return a true if the value of the operand is one that we can use in normal binary operations
		return true;
	}	//	function _validateBinaryOperand()


	private function _executeBinaryComparisonOperation($cellID,$operand1,$operand2,$operation,&$stack) {
		//	If we're dealing with matrix operations, we want a matrix result
		//	Note that we don't yet handle the situation where both operands are matrices
		if ((is_array($operand1)) || (is_array($operand2))) {
			$result = array();
			if ((is_array($operand1)) && (!is_array($operand2))) {
				foreach($operand1 as $x => $operandData) {
					$this->_writeDebug('Evaluating '.self::_showValue($operandData).' '.$operation.' '.self::_showValue($operand2));
					$this->_executeBinaryComparisonOperation($cellID,$operandData,$operand2,$operation,$stack);
					$r = $stack->pop();
					$result[$x] = $r['value'];
				}
			} elseif ((!is_array($operand1)) && (is_array($operand2))) {
				foreach($operand2 as $x => $operandData) {
					$this->_writeDebug('Evaluating '.self::_showValue($operand1).' '.$operation.' '.self::_showValue($operandData));
					$this->_executeBinaryComparisonOperation($cellID,$operand1,$operandData,$operation,$stack);
					$r = $stack->pop();
					$result[$x] = $r['value'];
				}
			}
			//	Log the result details
			$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($result));
			//	And push the result onto the stack
			$stack->push('Array',$result);
			return true;
		}

		//	Simple validate the two operands if they are string values
		if (is_string($operand1) && $operand1 > '' && $operand1{0} == '"') { $operand1 = self::_unwrapResult($operand1); }
		if (is_string($operand2) && $operand2 > '' && $operand2{0} == '"') { $operand2 = self::_unwrapResult($operand2); }

		//	execute the necessary operation
		switch ($operation) {
			//	Greater than
			case '>':
				$result = ($operand1 > $operand2);
				break;
			//	Less than
			case '<':
				$result = ($operand1 < $operand2);
				break;
			//	Equality
			case '=':
				$result = ($operand1 == $operand2);
				break;
			//	Greater than or equal
			case '>=':
				$result = ($operand1 >= $operand2);
				break;
			//	Less than or equal
			case '<=':
				$result = ($operand1 <= $operand2);
				break;
			//	Inequality
			case '<>':
				$result = ($operand1 != $operand2);
				break;
		}

		//	Log the result details
		$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($result));
		//	And push the result onto the stack
		$stack->push('Value',$result);
		return true;
	}	//	function _executeBinaryComparisonOperation()


	private function _executeNumericBinaryOperation($cellID,$operand1,$operand2,$operation,$matrixFunction,&$stack) {
		//	Validate the two operands
		if (!$this->_validateBinaryOperand($cellID,$operand1,$stack)) return false;
		if (!$this->_validateBinaryOperand($cellID,$operand2,$stack)) return false;

		//	If either of the operands is a matrix, we need to treat them both as matrices
		//		(converting the other operand to a matrix if need be); then perform the required
		//		matrix operation
		if ((is_array($operand1)) || (is_array($operand2))) {
			//	Ensure that both operands are arrays/matrices
			self::_checkMatrixOperands($operand1,$operand2);
			try {
				//	Convert operand 1 from a PHP array to a matrix
				$matrix = new Matrix($operand1);
				//	Perform the required operation against the operand 1 matrix, passing in operand 2
				$matrixResult = $matrix->$matrixFunction($operand2);
				$result = $matrixResult->getArray();
			} catch (Exception $ex) {
				$this->_writeDebug('JAMA Matrix Exception: '.$ex->getMessage());
				$result = '#VALUE!';
			}
		} else {
			//	If we're dealing with non-matrix operations, execute the necessary operation
			switch ($operation) {
				//	Addition
				case '+':
					$result = $operand1+$operand2;
					break;
				//	Subtraction
				case '-':
					$result = $operand1-$operand2;
					break;
				//	Multiplication
				case '*':
					$result = $operand1*$operand2;
					break;
				//	Division
				case '/':
					if ($operand2 == 0) {
						//	Trap for Divide by Zero error
						$stack->push('Value','#DIV/0!');
						$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails('#DIV/0!'));
						return false;
					} else {
						$result = $operand1/$operand2;
					}
					break;
				//	Power
				case '^':
					$result = pow($operand1,$operand2);
					break;
			}
		}

		//	Log the result details
		$this->_writeDebug('Evaluation Result is '.self::_showTypeDetails($result));
		//	And push the result onto the stack
		$stack->push('Value',$result);
		return true;
	}	//	function _executeNumericBinaryOperation()


	private function _writeDebug($message) {
		//	Only write the debug log if logging is enabled
		if ($this->writeDebugLog) {
			$this->debugLog[] = implode(' -> ',$this->debugLogStack).' -> '.$message;
		}
	}	//	function _writeDebug()


	// trigger an error, but nicely, if need be
	private function _raiseFormulaError($errorMessage) {
		$this->formulaError = $errorMessage;
		echo '_raiseFormulaError message is '.$errorMessage.'<br />';
		if (!$this->suppressFormulaErrors) throw new Exception($errorMessage);
		trigger_error($errorMessage, E_USER_ERROR);
	}	//	function _raiseFormulaError()


	/**
	 * Extract range values
	 *
	 * @param	string				&$pRange		String based range representation
	 * @param	ExcelWorksheet	$pSheet		Worksheet
	 * @return  mixed				Array of values in range if range contains more than one element. Otherwise, a single value is returned.
	 * @throws	Exception
	 */
	public function extractCellRange(&$pRange = 'A1', ExcelWorksheet $pSheet = null, $resetLog=true) {
		// Return value
		$returnValue = array ();

//		echo 'extractCellRange('.$pRange.')<br />';
		// Worksheet given?
		if (!is_null($pSheet)) {
			if (strpos ($pRange, '!') !== false) {
//				echo '$pRange reference includes sheet reference<br />';
				$worksheetReference = ExcelWorksheet::extractSheetTitle($pRange, true);
				$pSheet = $pSheet->getParent()->getSheetByName($worksheetReference[0]);
//				echo 'New sheet name is '.$pSheet->getTitle().'<br />';
				$pRange = $worksheetReference[1];
//				echo 'Adjusted Range reference is '.$pRange.'<br />';
			}

			// Extract range
			$aReferences = ExcelCell::extractAllCellReferencesInRange($pRange);
			$pRange = $pSheet->getTitle().'!'.$pRange;
			if (count($aReferences) == 1) {
				if ($pSheet->cellExists($aReferences[0])) {
					return $pSheet->getCell($aReferences[0])->getCalculatedValue($resetLog);
				} else {
					return NULL;
				}
			}

			// Extract cell data
			foreach ($aReferences as $reference) {
				// Extract range
				list($currentCol,$currentRow) = ExcelCell::coordinateFromString($reference);

				if ($pSheet->cellExists($reference)) {
					$returnValue[$currentRow][$currentCol] = $pSheet->getCell($reference)->getCalculatedValue($resetLog);
				} else {
					$returnValue[$currentRow][$currentCol] = NULL;
				}
			}
		}

		// Return
		return $returnValue;
	}	//	function extractCellRange()


	/**
	 * Extract range values
	 *
	 * @param	string				&$pRange	String based range representation
	 * @param	ExcelWorksheet	$pSheet		Worksheet
	 * @return  mixed				Array of values in range if range contains more than one element. Otherwise, a single value is returned.
	 * @throws	Exception
	 */
	public function extractNamedRange(&$pRange = 'A1', ExcelWorksheet $pSheet = null, $resetLog=true) {
		// Return value
		$returnValue = array ();

		if (!is_null($pSheet)) {
			if (strpos ($pRange, '!') !== false) {
				$worksheetReference = ExcelWorksheet::extractSheetTitle($pRange, true);
				$pSheet = $pSheet->getParent()->getSheetByName($worksheetReference[0]);
				$pRange = $worksheetReference[1];
			}

			// Named range?
			$namedRange = ExcelNamedRange::resolveRange($pRange, $pSheet);
			if (!is_null($namedRange)) {
				$pRange = $namedRange->getRange();
				if ($pSheet->getTitle() != $namedRange->getWorksheet()->getTitle()) {
					if (!$namedRange->getLocalOnly()) {
						$pSheet = $namedRange->getWorksheet();
					} else {
						return $returnValue;
					}
				}
			} else {
				return ExcelCalculation_Functions::REF();
			}

			// Extract range
			$aReferences = ExcelCell::extractAllCellReferencesInRange($pRange);
			if (count($aReferences) == 1) {
				if ($pSheet->cellExists($aReferences[0])) {
					return $pSheet->getCell($aReferences[0])->getCalculatedValue($resetLog);
				} else {
					return NULL;
				}
			}

			// Extract cell data
			foreach ($aReferences as $reference) {
				// Extract range
				list($currentCol,$currentRow) = ExcelCell::coordinateFromString($reference);
				if ($pSheet->cellExists($reference)) {
					$returnValue[$currentRow][$currentCol] = $pSheet->getCell($reference)->getCalculatedValue($resetLog);
				} else {
					$returnValue[$currentRow][$currentCol] = NULL;
				}
			}
		}

		// Return
		return $returnValue;
	}	//	function extractNamedRange()


	/**
	 * Is a specific function implemented?
	 *
	 * @param	string	$pFunction	Function Name
	 * @return	boolean
	 */
	public function isImplemented($pFunction = '') {
		$pFunction = strtoupper ($pFunction);
		if (isset($this->_PHPExcelFunctions[$pFunction])) {
			return ($this->_PHPExcelFunctions[$pFunction]['functionCall'] != 'ExcelCalculation_Functions::DUMMY');
		} else {
			return false;
		}
	}	//	function isImplemented()


	/**
	 * Get a list of all implemented functions as an array of function objects
	 *
	 * @return	array of ExcelCalculation_Function
	 */
	public function listFunctions() {
		// Return value
		$returnValue = array();
		// Loop functions
		foreach($this->_PHPExcelFunctions as $functionName => $function) {
			if ($function['functionCall'] != 'ExcelCalculation_Functions::DUMMY') {
				$returnValue[$functionName] = new ExcelCalculation_Function($function['category'],
																				$functionName,
																				$function['functionCall']
																			   );
			}
		}

		// Return
		return $returnValue;
	}

	/**
	 * Get a list of implemented Excel function names
	 *
	 * @return	array
	 */
	public function listFunctionNames() {
		return array_keys($this->_PHPExcelFunctions);
	}
}


/**
 * ExcelToken
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelToken_Stack
{
	private $_stack = array();
	private $_count = 0;

	public function count() {
		return $this->_count;
	}

	public function push($type,$value,$reference=null) {
		$this->_stack[$this->_count++] = array('type'		=> $type,
											   'value'		=> $value,
											   'reference'	=> $reference
											  );
	}

	public function pop() {
		if ($this->_count > 0) {
			return $this->_stack[--$this->_count];
		}
		return null;
	}

	public function last($n=1) {
		if ($this->_count-$n < 0) {
			return null;
		}
		return $this->_stack[$this->_count-$n];
	}
}
?>