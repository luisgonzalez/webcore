<?php
require_once 'webcore.excel.php';
require_once 'webcore.excel.cell.php';
require_once 'webcore.excel.utils.php';
require_once 'webcore.excel.calculation.php';

/**
 * ExcelWriter_CSV
 *
 * @package    WebCore
 * @subpackage Excel
 */
class CsvWriter extends ObjectBase implements IExcelWriter
{
    /**
     * PHPExcel object
     *
     * @var PHPExcel
     */
    private $_phpExcel;
    
    /**
     * Delimiter
     *
     * @var string
     */
    private $_delimiter;
    
    /**
     * Enclosure
     *
     * @var string
     */
    private $_enclosure;
    
    /**
     * Line ending
     *
     * @var string
     */
    private $_lineEnding;
    
    /**
     * Sheet index to write
     *
     * @var int
     */
    private $_sheetIndex;
    
    /**
     * Pre-calculate formulas
     *
     * @var boolean
     */
    private $_preCalculateFormulas = true;
    
    /**
     * Whether to write a BOM (for UTF8).
     *
     * @var boolean
     */
    private $_useBOM = false;
    
    /**
     * Create a new ExcelWriter_CSV
     *
     * @param 	ExcelWorkbook	$workbook	PHPExcel object
     */
    public function __construct($workbook)
    {
        $this->_phpExcel   = $workbook;
        $this->_delimiter  = ',';
        $this->_enclosure  = '"';
        $this->_lineEnding = PHP_EOL;
        $this->_sheetIndex = 0;
    }
    
    /**
     * Worsk the same as Save PHPExcel to file, except it returns the file contents.
     *
     * @return  mixed
     * @throws	Exception
     */
    public function output()
    {
        $filename = tempnam(sys_get_temp_dir(), 'webcore.excel.csvwriter.');
        $this->save($filename);
        return file_get_contents($filename);
    }
    
    /**
     * Save PHPExcel to file
     *
     * @param 	string 		$pFileName
     * @throws 	Exception
     */
    public function save($pFilename = null)
    {
        // Fetch sheet
        $sheet = $this->_phpExcel->getSheet($this->_sheetIndex);
        
        $saveArrayReturnType = ExcelCalculation::getArrayReturnType();
        ExcelCalculation::setArrayReturnType(ExcelCalculation::RETURN_ARRAY_AS_VALUE);
        
        // Open file
        $fileHandle = fopen($pFilename, 'w');
        if ($fileHandle === false)
        {
            throw new Exception("Could not open file $pFilename for writing.");
        }
        
        if ($this->_useBOM)
        {
            // Write the UTF-8 BOM code
            fwrite($fileHandle, "\xEF\xBB\xBF");
        }
        
        // Convert sheet to array
        $cellsArray = $sheet->toArray('', $this->_preCalculateFormulas);
        
        // Write rows to file
        foreach ($cellsArray as $row)
        {
            $this->_writeLine($fileHandle, $row);
        }
        
        // Close file
        fclose($fileHandle);
        
        ExcelCalculation::setArrayReturnType($saveArrayReturnType);
    }
    
    /**
     * Get delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->_delimiter;
    }
    
    /**
     * Set delimiter
     *
     * @param	string	$pValue		Delimiter, defaults to ,
     */
    public function setDelimiter($pValue = ',')
    {
        $this->_delimiter = $pValue;
    }
    
    /**
     * Get enclosure
     *
     * @return string
     */
    public function getEnclosure()
    {
        return $this->_enclosure;
    }
    
    /**
     * Set enclosure
     *
     * @param	string	$pValue		Enclosure, defaults to "
     */
    public function setEnclosure($pValue = '"')
    {
        if ($pValue == '')
            $pValue = null;
        
        $this->_enclosure = $pValue;
    }
    
    /**
     * Get line ending
     *
     * @return string
     */
    public function getLineEnding()
    {
        return $this->_lineEnding;
    }
    
    /**
     * Set line ending
     *
     * @param	string	$pValue		Line ending, defaults to OS line ending (PHP_EOL)
     */
    public function setLineEnding($pValue = PHP_EOL)
    {
        $this->_lineEnding = $pValue;
    }
    
    /**
     * Get whether BOM should be used
     *
     * @return boolean
     */
    public function getUseBOM()
    {
        return $this->_useBOM;
    }
    
    /**
     * Set whether BOM should be used
     *
     * @param	boolean	$pValue		Use UTF-8 byte-order mark? Defaults to false
     */
    public function setUseBOM($pValue = false)
    {
        $this->_useBOM = $pValue;
    }
    
    /**
     * Get sheet index
     *
     * @return int
     */
    public function getSheetIndex()
    {
        return $this->_sheetIndex;
    }
    
    /**
     * Set sheet index
     *
     * @param	int		$pValue		Sheet index
     */
    public function setSheetIndex($pValue = 0)
    {
        $this->_sheetIndex = $pValue;
    }
    
    /**
     * Write line to CSV file
     *
     * @param	mixed	$pFileHandle	PHP filehandle
     * @param	array	$pValues		Array containing values in a row
     * @throws	Exception
     */
    private function _writeLine($pFileHandle = null, $pValues = null)
    {
        if (!is_null($pFileHandle) && is_array($pValues))
        {
            // No leading delimiter
            $writeDelimiter = false;
            
            // Build the line
            $line = '';
            
            foreach ($pValues as $element)
            {
                // Escape enclosures
                $element = str_replace($this->_enclosure, $this->_enclosure . $this->_enclosure, $element);
                
                // Add delimiter
                if ($writeDelimiter)
                {
                    $line .= $this->_delimiter;
                }
                else
                {
                    $writeDelimiter = true;
                }
                
                // Add enclosed string
                $line .= $this->_enclosure . $element . $this->_enclosure;
            }
            
            // Add line ending
            $line .= $this->_lineEnding;
            
            // Write to file
            fwrite($pFileHandle, $line);
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
    
    /**
     * Get Pre-Calculate Formulas
     *
     * @return boolean
     */
    public function getPreCalculateFormulas()
    {
        return $this->_preCalculateFormulas;
    }
    
    /**
     * Set Pre-Calculate Formulas
     *
     * @param boolean $pValue	Pre-Calculate Formulas?
     */
    public function setPreCalculateFormulas($pValue = true)
    {
        $this->_preCalculateFormulas = $pValue;
    }
}
?>