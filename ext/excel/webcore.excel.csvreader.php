<?php
require_once 'webcore.excel.php';

/**
 * ExcelReader_CSV
 *
 * @package    WebCore
 * @subpackage Excel
 */
class CsvReader extends ExcelReaderBase
{
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
     * Sheet index to read
     *
     * @var int
     */
    private $_sheetIndex;
    
    /**
     * Create a new ExcelReader_CSV
     */
    public function __construct()
    {
        $this->_delimiter  = ',';
        $this->_enclosure  = '"';
        $this->_lineEnding = PHP_EOL;
        $this->_sheetIndex = 0;
    }
    
    /**
     * Loads PHPExcel from file
     *
     * @param 	string 		$pFilename
     * @throws 	Exception
     */
    public function load($pFilename)
    {
        parent::load($pFilename);
        // Create new PHPExcel
        $objPHPExcel = new PHPExcel();
        
        // Create new PHPExcel
        while ($objPHPExcel->getSheetCount() <= $this->_sheetIndex)
        {
            $objPHPExcel->createSheet();
        }
        $objPHPExcel->setActiveSheetIndex($this->_sheetIndex);
        
        // Open file
        $fileHandle = fopen($pFilename, 'r');
        if ($fileHandle === false)
        {
            throw new Exception("Could not open file $pFilename for reading.");
        }
        
        // Loop trough file
        $currentRow = 0;
        $rowData    = array();
        while (($rowData = fgetcsv($fileHandle, 0, $this->_delimiter, $this->_enclosure)) !== FALSE)
        {
            ++$currentRow;
            $rowDataCount = count($rowData);
            for ($i = 0; $i < $rowDataCount; ++$i)
            {
                if ($rowData[$i] != '')
                {
                    // Unescape enclosures
                    $rowData[$i] = str_replace("\\" . $this->_enclosure, $this->_enclosure, $rowData[$i]);
                    $rowData[$i] = str_replace($this->_enclosure . $this->_enclosure, $this->_enclosure, $rowData[$i]);
                    
                    // Set cell value
                    $objPHPExcel->getActiveSheet()->setCellValue(ExcelCell::stringFromColumnIndex($i) . $currentRow, $rowData[$i]);
                }
            }
        }
        
        // Close file
        fclose($fileHandle);
        
        // Return
        return $objPHPExcel;
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
        {
            $pValue = '"';
        }
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
}
?>