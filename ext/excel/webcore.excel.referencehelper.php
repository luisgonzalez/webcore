<?php
require_once 'webcore.excel.worksheet.php';
require_once 'worksheet/Drawing.php';

/**
 * ExcelReferenceHelper (Singleton)
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelReferenceHelper extends HelperBase implements ISingleton
{
    /**
     * Instance of this class
     *
     * @var ExcelReferenceHelper
     */
    private static $_instance;
    
    /**
     * Get an instance of this class
     *
     * @return ExcelReferenceHelper
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$_instance = new ExcelReferenceHelper();
        
        return self::$_instance;
    }
    
    /**
     * Determines whether the singleton's instance is currently loaded
     *
     * @return bool
     */
    public static function isLoaded()
    {
        return (is_null(self::$_instance) === false);
    }
    
    /**
     * Create a new ExcelCalculation
     */
    protected function __construct()
    {
    }
    
    /**
     * Insert a new column, updating all possible related data
     *
     * @param 	int	$pBefore	Insert before this one
     * @param 	int	$pNumCols	Number of columns to insert
     * @param 	int	$pNumRows	Number of rows to insert
     * @throws 	Exception
     */
    public function insertNewBefore($pBefore = 'A1', $pNumCols = 0, $pNumRows = 0, ExcelWorksheet $pSheet = null)
    {
        $aCellCollection = $pSheet->getCellCollection();
        
        // Get coordinates of $pBefore
        $beforeColumn = 'A';
        $beforeRow    = 1;
        list($beforeColumn, $beforeRow) = ExcelCell::coordinateFromString($pBefore);
        
        
        // Remove cell styles?
        $highestColumn = $pSheet->getHighestColumn();
        $highestRow    = $pSheet->getHighestRow();
        
        if ($pNumCols < 0 && ExcelCell::columnIndexFromString($beforeColumn) - 2 + $pNumCols > 0)
        {
            for ($i = 1; $i <= $highestRow - 1; ++$i)
            {
                $pSheet->duplicateStyle(new ExcelStyle(), (ExcelCell::stringFromColumnIndex(ExcelCell::columnIndexFromString($beforeColumn) - 1 + $pNumCols) . $i) . ':' . (ExcelCell::stringFromColumnIndex(ExcelCell::columnIndexFromString($beforeColumn) - 2) . $i));
            }
        }
        
        if ($pNumRows < 0 && $beforeRow - 1 + $pNumRows > 0)
        {
            for ($i = ExcelCell::columnIndexFromString($beforeColumn) - 1; $i <= ExcelCell::columnIndexFromString($highestColumn) - 1; ++$i)
            {
                $pSheet->duplicateStyle(new ExcelStyle(), (ExcelCell::stringFromColumnIndex($i) . ($beforeRow + $pNumRows)) . ':' . (ExcelCell::stringFromColumnIndex($i) . ($beforeRow - 1)));
            }
        }
        
        // Loop trough cells, bottom-up, and change cell coordinates
        while (($cell = ($pNumCols < 0 || $pNumRows < 0) ? array_shift($aCellCollection) : array_pop($aCellCollection)))
        {
            // New coordinates
            $newCoordinates = ExcelCell::stringFromColumnIndex(ExcelCell::columnIndexFromString($cell->getColumn()) - 1 + $pNumCols) . ($cell->getRow() + $pNumRows);
            
            // Should the cell be updated?
            if ((ExcelCell::columnIndexFromString($cell->getColumn()) >= ExcelCell::columnIndexFromString($beforeColumn)) && ($cell->getRow() >= $beforeRow))
            {
                // Update cell styles
                $pSheet->duplicateStyle($pSheet->getStyle($cell->getCoordinate()), $newCoordinates . ':' . $newCoordinates);
                $pSheet->duplicateStyle($pSheet->getDefaultStyle(), $cell->getCoordinate() . ':' . $cell->getCoordinate());
                
                // Insert this cell at its new location
                if ($cell->getDataType() == ExcelCell_DataType::TYPE_FORMULA)
                    $pSheet->setCellValue($newCoordinates, $this->updateFormulaReferences($cell->getValue(), $pBefore, $pNumCols, $pNumRows));
                else
                    $pSheet->setCellValue($newCoordinates, $cell->getValue());
                
                // Clear the original cell
                $pSheet->setCellValue($cell->getCoordinate(), '');
            }
        }
        
        // Duplicate styles for the newly inserted cells
        $highestColumn = $pSheet->getHighestColumn();
        $highestRow    = $pSheet->getHighestRow();
        
        if ($pNumCols > 0 && ExcelCell::columnIndexFromString($beforeColumn) - 2 > 0)
        {
            for ($i = $beforeRow; $i <= $highestRow - 1; ++$i)
            {
                // Style
                $pSheet->duplicateStyle($pSheet->getStyle((ExcelCell::stringFromColumnIndex(ExcelCell::columnIndexFromString($beforeColumn) - 2) . $i)), ($beforeColumn . $i) . ':' . (ExcelCell::stringFromColumnIndex(ExcelCell::columnIndexFromString($beforeColumn) - 2 + $pNumCols) . $i));
                
            }
        }
        
        if ($pNumRows > 0 && $beforeRow - 1 > 0)
        {
            for ($i = ExcelCell::columnIndexFromString($beforeColumn) - 1; $i <= ExcelCell::columnIndexFromString($highestColumn) - 1; ++$i)
            {
                // Style
                $pSheet->duplicateStyle($pSheet->getStyle((ExcelCell::stringFromColumnIndex($i) . ($beforeRow - 1))), (ExcelCell::stringFromColumnIndex($i) . $beforeRow) . ':' . (ExcelCell::stringFromColumnIndex($i) . ($beforeRow - 1 + $pNumRows)));
                
            }
        }
        
        
        // Update worksheet: column dimensions
        $aColumnDimensions = array_reverse($pSheet->getColumnDimensions(), true);
        if (count($aColumnDimensions) > 0)
        {
            foreach ($aColumnDimensions as $objColumnDimension)
            {
                $newReference = $this->updateCellReference($objColumnDimension->getColumnIndex() . '1', $pBefore, $pNumCols, $pNumRows);
                list($newReference) = ExcelCell::coordinateFromString($newReference);
                if ($objColumnDimension->getColumnIndex() != $newReference)
                {
                    $objColumnDimension->setColumnIndex($newReference);
                }
            }
            $pSheet->refreshColumnDimensions();
        }
        
        
        // Update worksheet: row dimensions
        $aRowDimensions = array_reverse($pSheet->getRowDimensions(), true);
        if (count($aRowDimensions) > 0)
        {
            foreach ($aRowDimensions as $objRowDimension)
            {
                $newReference = $this->updateCellReference('A' . $objRowDimension->getRowIndex(), $pBefore, $pNumCols, $pNumRows);
                list(, $newReference) = ExcelCell::coordinateFromString($newReference);
                if ($objRowDimension->getRowIndex() != $newReference)
                {
                    $objRowDimension->setRowIndex($newReference);
                }
            }
            $pSheet->refreshRowDimensions();
            
            $copyDimension = $pSheet->getRowDimension($beforeRow - 1);
            for ($i = $beforeRow; $i <= $beforeRow - 1 + $pNumRows; ++$i)
            {
                $newDimension = $pSheet->getRowDimension($i);
                $newDimension->setRowHeight($copyDimension->getRowHeight());
                $newDimension->setVisible($copyDimension->getVisible());
                $newDimension->setOutlineLevel($copyDimension->getOutlineLevel());
                $newDimension->setCollapsed($copyDimension->getCollapsed());
            }
        }
        
        
        // Update worksheet: breaks
        $aBreaks = array_reverse($pSheet->getBreaks(), true);
        foreach ($aBreaks as $key => $value)
        {
            $newReference = $this->updateCellReference($key, $pBefore, $pNumCols, $pNumRows);
            if ($key != $newReference)
            {
                $pSheet->setBreak($newReference, $value);
                $pSheet->setBreak($key, ExcelWorksheet::BREAK_NONE);
            }
        }
        
        
        // Update worksheet: merge cells
        $aMergeCells = array_reverse($pSheet->getMergeCells(), true);
        foreach ($aMergeCells as $key => $value)
        {
            $newReference = $this->updateCellReference($key, $pBefore, $pNumCols, $pNumRows);
            if ($key != $newReference)
            {
                $pSheet->mergeCells($newReference);
                $pSheet->unmergeCells($key);
            }
        }
        
        
        // Update worksheet: protected cells
        $aProtectedCells = array_reverse($pSheet->getProtectedCells(), true);
        foreach ($aProtectedCells as $key => $value)
        {
            $newReference = $this->updateCellReference($key, $pBefore, $pNumCols, $pNumRows);
            if ($key != $newReference)
            {
                $pSheet->protectCells($newReference, $value, true);
                $pSheet->unprotectCells($key);
            }
        }
        
        
        // Update worksheet: autofilter
        if ($pSheet->getAutoFilter() != '')
        {
            $pSheet->setAutoFilter($this->updateCellReference($pSheet->getAutoFilter(), $pBefore, $pNumCols, $pNumRows));
        }
        
        
        // Update worksheet: freeze pane
        if ($pSheet->getFreezePane() != '')
        {
            $pSheet->freezePane($this->updateCellReference($pSheet->getFreezePane(), $pBefore, $pNumCols, $pNumRows));
        }
        
        
        // Page setup
        if ($pSheet->getPageSetup()->isPrintAreaSet())
        {
            $pSheet->getPageSetup()->setPrintArea($this->updateCellReference($pSheet->getPageSetup()->getPrintArea(), $pBefore, $pNumCols, $pNumRows));
        }
        
        
        // Update worksheet: drawings
        $aDrawings = $pSheet->getDrawingCollection();
        foreach ($aDrawings as $objDrawing)
        {
            $newReference = $this->updateCellReference($objDrawing->getCoordinates(), $pBefore, $pNumCols, $pNumRows);
            if ($objDrawing->getCoordinates() != $newReference)
            {
                $objDrawing->setCoordinates($newReference);
            }
        }
        
        
        // Update workbook: named ranges
        if (count($pSheet->getParent()->getNamedRanges()) > 0)
        {
            foreach ($pSheet->getParent()->getNamedRanges() as $namedRange)
            {
                if ($namedRange->getWorksheet()->getHashCode() == $pSheet->getHashCode())
                {
                    $namedRange->setRange($this->updateCellReference($namedRange->getRange(), $pBefore, $pNumCols, $pNumRows));
                }
            }
        }
        
        
        // Garbage collect
        $pSheet->garbageCollect();
    }
    
    /**
     * Update references within formulas
     *
     * @param 	string	$pFormula	Formula to update
     * @param 	int		$pBefore	Insert before this one
     * @param 	int		$pNumCols	Number of columns to insert
     * @param 	int		$pNumRows	Number of rows to insert
     * @return 	string	Updated formula
     * @throws 	Exception
     */
    public function updateFormulaReferences($pFormula = '', $pBefore = 'A1', $pNumCols = 0, $pNumRows = 0)
    {
        // Formula stack
        $executableFormulaArray = array();
        
        // Parse formula into a tree of tokens
        $objParser = new ExcelCalculation_FormulaParser($pFormula);
        
        // Loop trough parsed tokens and create an executable formula
        $inFunction = false;
        $token      = null;
        $tokenCount = $objParser->getTokenCount();
        for ($i = 0; $i < $tokenCount; ++$i)
        {
            $token = $objParser->getToken($i);
            
            // Is it a cell reference? Not a cell range?
            if (($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND) && ($token->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_RANGE))
            {
                // New cell reference
                $newCellReference = $this->updateCellReference($token->getValue(), $pBefore, $pNumCols, $pNumRows);
                
                // Add adjusted cell coordinate to executable formula array
                $executableFormulaArray[] = $newCellReference;
                
                continue;
            }
            
            // Is it a subexpression?
            if ($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_SUBEXPRESSION)
            {
                // Temporary variable
                $tmp = '';
                switch ($token->getTokenSubType())
                {
                    case ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START:
                        $tmp = '(';
                        break;
                    case ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP:
                        $tmp = ')';
                        break;
                }
                
                // Add to executable formula array
                $executableFormulaArray[] = $tmp;
                
                continue;
            }
            
            // Is it a function?
            if ($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_FUNCTION)
            {
                // Temporary variable
                $tmp = '';
                
                // Check the function type
                if ($token->getValue() == 'ARRAY' || $token->getValue() == 'ARRAYROW')
                {
                    // An array or an array row...
                    $tmp = '(';
                }
                else
                {
                    // A regular function call...
                    switch ($token->getTokenSubType())
                    {
                        case ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_START:
                            $tmp        = strtoupper($token->getValue()) . '(';
                            $inFunction = true;
                            break;
                        case ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_STOP:
                            $tmp = ')';
                            break;
                    }
                }
                
                // Add to executable formula array
                $executableFormulaArray[] = $tmp;
                
                continue;
            }
            
            // Is it text?
            if (($token->getTokenType() == ExcelCalculation_FormulaToken::TOKEN_TYPE_OPERAND) && ($token->getTokenSubType() == ExcelCalculation_FormulaToken::TOKEN_SUBTYPE_TEXT))
            {
                // Add to executable formula array
                $executableFormulaArray[] = '"' . $token->getValue() . '"';
                
                continue;
            }
            
            // Is it something else?
            $executableFormulaArray[] = $token->getValue();
        }
        
        // Return result
        return '=' . implode(' ', $executableFormulaArray);
    }
    
    /**
     * Update cell reference
     *
     * @param 	string	$pCellRange			Cell range
     * @param 	int		$pBefore			Insert before this one
     * @param 	int		$pNumCols			Number of columns to increment
     * @param 	int		$pNumRows			Number of rows to increment
     * @return 	string	Updated cell range
     * @throws 	Exception
     */
    public function updateCellReference($pCellRange = 'A1', $pBefore = 'A1', $pNumCols = 0, $pNumRows = 0)
    {
        // Is it a range or a single cell?
        if (strpos($pCellRange, ':') === false && strpos($pCellRange, ',') === false)
        {
            // Single cell
            return $this->_updateSingleCellReference($pCellRange, $pBefore, $pNumCols, $pNumRows);
        }
        else
        {
            // Range
            return $this->_updateCellRange($pCellRange, $pBefore, $pNumCols, $pNumRows);
        }
    }
    
    /**
     * Update named formulas (i.e. containing worksheet references / named ranges)
     *
     * @param Workbook $workbook	Object to update
     * @param string $oldName		Old name (name to replace)
     * @param string $newName		New name
     */
    public function updateNamedFormulas($workbook, $oldName = '', $newName = '')
    {
        foreach ($workbook->getWorksheetIterator() as $sheet)
        {
            foreach ($sheet->getCellCollection() as $cell)
            {
                if (!is_null($cell) && $cell->getDataType() == ExcelCell_DataType::TYPE_FORMULA)
                {
                    $formula = $cell->getValue();
                    if (strpos($formula, $oldName) !== false)
                    {
                        $formula = str_replace($oldName, $newName, $formula);
                        $cell->setValue($formula, false);
                    }
                }
            }
        }
    }
    
    /**
     * Update cell range
     *
     * @param 	string	$pCellRange			Cell range
     * @param 	int		$pBefore			Insert before this one
     * @param 	int		$pNumCols			Number of columns to increment
     * @param 	int		$pNumRows			Number of rows to increment
     * @return 	string	Updated cell range
     * @throws 	Exception
     */
    private function _updateCellRange($pCellRange = 'A1:A1', $pBefore = 'A1', $pNumCols = 0, $pNumRows = 0)
    {
        if (strpos($pCellRange, ':') !== false || strpos($pCellRange, ',') !== false)
        {
            // Update range
            $range = ExcelCell::splitRange($pCellRange);
            for ($i = 0; $i < count($range); $i++)
            {
                for ($j = 0; $j < count($range[$i]); $j++)
                {
                    $range[$i][$j] = $this->_updateSingleCellReference($range[$i][$j], $pBefore, $pNumCols, $pNumRows);
                }
            }
            
            // Recreate range string
            return ExcelCell::buildRange($range);
        }
        else
        {
            throw new Exception("Only cell ranges may be passed to this method.");
        }
    }
    
    /**
     * Update single cell reference
     *
     * @param 	string	$pCellReference		Single cell reference
     * @param 	int		$pBefore			Insert before this one
     * @param 	int		$pNumCols			Number of columns to increment
     * @param 	int		$pNumRows			Number of rows to increment
     * @return 	string	Updated cell reference
     * @throws 	Exception
     */
    private function _updateSingleCellReference($pCellReference = 'A1', $pBefore = 'A1', $pNumCols = 0, $pNumRows = 0)
    {
        if (strpos($pCellReference, ':') === false && strpos($pCellReference, ',') === false)
        {
            // Get coordinates of $pBefore
            $beforeColumn = 'A';
            $beforeRow    = 1;
            list($beforeColumn, $beforeRow) = ExcelCell::coordinateFromString($pBefore);
            
            // Get coordinates
            $newColumn = 'A';
            $newRow    = 1;
            list($newColumn, $newRow) = ExcelCell::coordinateFromString($pCellReference);
            
            // Verify which parts should be updated
            $updateColumn = (ExcelCell::columnIndexFromString($newColumn) >= ExcelCell::columnIndexFromString($beforeColumn)) && (strpos($newColumn, '$') === false) && (strpos($beforeColumn, '$') === false);
            
            $updateRow = ($newRow >= $beforeRow) && (strpos($newRow, '$') === false) && (strpos($beforeRow, '$') === false);
            
            // Create new column reference
            if ($updateColumn)
            {
                $newColumn = ExcelCell::stringFromColumnIndex(ExcelCell::columnIndexFromString($newColumn) - 1 + $pNumCols);
            }
            
            // Create new row reference
            if ($updateRow)
            {
                $newRow = $newRow + $pNumRows;
            }
            
            // Return new reference
            return $newColumn . $newRow;
        }
        else
        {
            throw new Exception("Only single cell references may be passed to this method.");
        }
    }
}
?>