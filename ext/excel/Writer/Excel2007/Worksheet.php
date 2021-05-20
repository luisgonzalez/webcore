<?php
/**
 * ExcelWriter_Excel2007_Worksheet
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_Worksheet extends ExcelWriterPart
{
    /**
     * Write worksheet to XML format
     *
     * @param	ExcelWorksheet		$pSheet
     * @param	string[]				$pStringTable
     * @return	string					XML Output
     * @throws	Exception
     */
    public function writeWorksheet($pSheet = null, $pStringTable = null)
    {
        if (!is_null($pSheet))
        {
            // Create XML writer
            $xDoc = null;
            if ($this->getParentWriter()->getUseDiskCaching())
            {
                $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
            }
            else
            {
                $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_MEMORY);
            }
            
            // XML header
            $xDoc->startDocument('1.0', 'UTF-8', 'yes');
            
            // Worksheet
            $xDoc->startElement('worksheet');
            $xDoc->writeAttribute('xml:space', 'preserve');
            $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $xDoc->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            
            // sheetPr
            $this->_writeSheetPr($xDoc, $pSheet);
            
            // Dimension
            $this->_writeDimension($xDoc, $pSheet);
            
            // sheetViews
            $this->_writeSheetViews($xDoc, $pSheet);
            
            // sheetFormatPr
            $this->_writeSheetFormatPr($xDoc, $pSheet);
            
            // cols
            $this->_writeCols($xDoc, $pSheet);
            
            // sheetData
            $this->_writeSheetData($xDoc, $pSheet, $pStringTable);
            
            // sheetProtection
            $this->_writeSheetProtection($xDoc, $pSheet);
            
            // protectedRanges
            $this->_writeProtectedRanges($xDoc, $pSheet);
            
            // autoFilter
            $this->_writeAutoFilter($xDoc, $pSheet);
            
            // mergeCells
            $this->_writeMergeCells($xDoc, $pSheet);
            
            // conditionalFormatting
            $this->_writeConditionalFormatting($xDoc, $pSheet);
            
            // dataValidations
            $this->_writeDataValidations($xDoc, $pSheet);
            
            // hyperlinks
            $this->_writeHyperlinks($xDoc, $pSheet);
            
            // Print options
            $this->_writePrintOptions($xDoc, $pSheet);
            
            // Page margins
            $this->_writePageMargins($xDoc, $pSheet);
            
            // Page setup
            $this->_writePageSetup($xDoc, $pSheet);
            
            // Header / footer
            $this->_writeHeaderFooter($xDoc, $pSheet);
            
            // Breaks
            $this->_writeBreaks($xDoc, $pSheet);
            
            // Drawings
            $this->_writeDrawings($xDoc, $pSheet);
            
            // LegacyDrawing
            $this->_writeLegacyDrawing($xDoc, $pSheet);
            
            // LegacyDrawingHF
            $this->_writeLegacyDrawingHF($xDoc, $pSheet);
            
            $xDoc->endElement();
            
            // Return
            return $xDoc->getData();
        }
        else
        {
            throw new Exception("Invalid ExcelWorksheet object passed.");
        }
    }
    
    /**
     * Write SheetPr
     *
     * @param	ExcelXmlWriter		$xDoc		XML Writer
     * @param	ExcelWorksheet				$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeSheetPr(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // sheetPr
        $xDoc->startElement('sheetPr');
        $xDoc->writeAttribute('codeName', $pSheet->getTitle());
        
        // outlinePr
        $xDoc->startElement('outlinePr');
        $xDoc->writeAttribute('summaryBelow', ($pSheet->getShowSummaryBelow() ? '1' : '0'));
        $xDoc->writeAttribute('summaryRight', ($pSheet->getShowSummaryRight() ? '1' : '0'));
        $xDoc->endElement();
        
        // pageSetUpPr
        if (!is_null($pSheet->getPageSetup()->getFitToHeight()) || !is_null($pSheet->getPageSetup()->getFitToWidth()))
        {
            $xDoc->startElement('pageSetUpPr');
            $xDoc->writeAttribute('fitToPage', '1');
            $xDoc->endElement();
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write Dimension
     *
     * @param	ExcelXmlWriter	$xDoc		XML Writer
     * @param	ExcelWorksheet			$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeDimension(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // dimension
        $xDoc->startElement('dimension');
        $xDoc->writeAttribute('ref', $pSheet->calculateWorksheetDimension());
        $xDoc->endElement();
    }
    
    /**
     * Write SheetViews
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeSheetViews(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // sheetViews
        $xDoc->startElement('sheetViews');
        
        // Sheet selected?
        $sheetSelected = false;
        if ($this->getParentWriter()->getPHPExcel()->getIndex($pSheet) == $this->getParentWriter()->getPHPExcel()->getActiveSheetIndex())
            $sheetSelected = true;
        
        
        // sheetView
        $xDoc->startElement('sheetView');
        $xDoc->writeAttribute('tabSelected', $sheetSelected ? '1' : '0');
        $xDoc->writeAttribute('workbookViewId', '0');
        
        // Zoom scales
        if ($pSheet->getSheetView()->getZoomScale() != 100)
        {
            $xDoc->writeAttribute('zoomScale', $pSheet->getSheetView()->getZoomScale());
        }
        if ($pSheet->getSheetView()->getZoomScaleNormal() != 100)
        {
            $xDoc->writeAttribute('zoomScaleNormal', $pSheet->getSheetView()->getZoomScaleNormal());
        }
        
        // Gridlines
        if ($pSheet->getShowGridlines())
        {
            $xDoc->writeAttribute('showGridLines', 'true');
        }
        else
        {
            $xDoc->writeAttribute('showGridLines', 'false');
        }
        
        // Pane
        if ($pSheet->getFreezePane() != '')
        {
            // Calculate freeze coordinates
            $xSplit      = 0;
            $ySplit      = 0;
            $topLeftCell = $pSheet->getFreezePane();
            
            list($xSplit, $ySplit) = ExcelCell::coordinateFromString($pSheet->getFreezePane());
            $xSplit = ExcelCell::columnIndexFromString($xSplit);
            
            // pane
            $xDoc->startElement('pane');
            $xDoc->writeAttribute('xSplit', $xSplit - 1);
            $xDoc->writeAttribute('ySplit', $ySplit - 1);
            $xDoc->writeAttribute('topLeftCell', $topLeftCell);
            $xDoc->writeAttribute('activePane', 'bottomRight');
            $xDoc->writeAttribute('state', 'frozen');
            $xDoc->endElement();
        }
        
        // Selection
        $xDoc->startElement('selection');
        $xDoc->writeAttribute('activeCell', $pSheet->getSelectedCell());
        $xDoc->writeAttribute('sqref', $pSheet->getSelectedCell());
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
    
    /**
     * Write SheetFormatPr
     *
     * @param	ExcelXmlWriter $xDoc		XML Writer
     * @param	ExcelWorksheet		  $pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeSheetFormatPr(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // sheetFormatPr
        $xDoc->startElement('sheetFormatPr');
        
        // Default row height
        if ($pSheet->getDefaultRowDimension()->getRowHeight() >= 0)
        {
            $xDoc->writeAttribute('customHeight', 'true');
            $xDoc->writeAttribute('defaultRowHeight', ExcelShared_String::FormatNumber($pSheet->getDefaultRowDimension()->getRowHeight()));
        }
        else
        {
            $xDoc->writeAttribute('defaultRowHeight', '12.75');
        }
        
        // Default column width
        if ($pSheet->getDefaultColumnDimension()->getWidth() >= 0)
        {
            $xDoc->writeAttribute('defaultColWidth', ExcelShared_String::FormatNumber($pSheet->getDefaultColumnDimension()->getWidth()));
        }
        
        // Outline level - row
        $outlineLevelRow = 0;
        foreach ($pSheet->getRowDimensions() as $dimension)
        {
            if ($dimension->getOutlineLevel() > $outlineLevelRow)
            {
                $outlineLevelRow = $dimension->getOutlineLevel();
            }
        }
        $xDoc->writeAttribute('outlineLevelRow', (int) $outlineLevelRow);
        
        // Outline level - column
        $outlineLevelCol = 0;
        foreach ($pSheet->getColumnDimensions() as $dimension)
        {
            if ($dimension->getOutlineLevel() > $outlineLevelCol)
            {
                $outlineLevelCol = $dimension->getOutlineLevel();
            }
        }
        $xDoc->writeAttribute('outlineLevelCol', (int) $outlineLevelCol);
        
        $xDoc->endElement();
    }
    
    /**
     * Write Cols
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeCols(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // cols
        $xDoc->startElement('cols');
        
        // Check if there is at least one column dimension specified. If not, create one.
        if (count($pSheet->getColumnDimensions()) == 0)
        {
            if ($pSheet->getDefaultColumnDimension()->getWidth() >= 0)
            {
                $pSheet->getColumnDimension('A')->setWidth($pSheet->getDefaultColumnDimension()->getWidth());
            }
            else
            {
                $pSheet->getColumnDimension('A')->setWidth(9.10);
            }
        }
        
        $pSheet->calculateColumnWidths();
        
        // Loop trough column dimensions
        foreach ($pSheet->getColumnDimensions() as $colDimension)
        {
            // col
            $xDoc->startElement('col');
            $xDoc->writeAttribute('min', ExcelCell::columnIndexFromString($colDimension->getColumnIndex()));
            $xDoc->writeAttribute('max', ExcelCell::columnIndexFromString($colDimension->getColumnIndex()));
            
            if ($colDimension->getWidth() < 0)
            {
                // No width set, apply default of 10
                $xDoc->writeAttribute('width', '9.10');
            }
            else
            {
                // Width set
                $xDoc->writeAttribute('width', ExcelShared_String::FormatNumber($colDimension->getWidth()));
            }
            
            // Column visibility
            if ($colDimension->getVisible() == false)
            {
                $xDoc->writeAttribute('hidden', 'true');
            }
            
            // Auto size?
            if ($colDimension->getAutoSize())
            {
                $xDoc->writeAttribute('bestFit', 'true');
            }
            
            // Custom width?
            if ($colDimension->getWidth() != $pSheet->getDefaultColumnDimension()->getWidth())
            {
                $xDoc->writeAttribute('customWidth', 'true');
            }
            
            // Collapsed
            if ($colDimension->getCollapsed() == true)
            {
                $xDoc->writeAttribute('collapsed', 'true');
            }
            
            // Outline level
            if ($colDimension->getOutlineLevel() > 0)
            {
                $xDoc->writeAttribute('outlineLevel', $colDimension->getOutlineLevel());
            }
            
            // Style
            $styleIndex = $this->getParentWriter()->getStylesHashTable()->getIndexForHashCode($pSheet->getDefaultStyle()->getHashCode());
            if ($styleIndex != '')
            {
                $xDoc->writeAttribute('style', $styleIndex);
            }
            
            $xDoc->endElement();
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write SheetProtection
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeSheetProtection(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // sheetProtection
        $xDoc->startElement('sheetProtection');
        
        if ($pSheet->getProtection()->getPassword() != '')
        {
            $xDoc->writeAttribute('password', $pSheet->getProtection()->getPassword());
        }
        
        $xDoc->writeAttribute('sheet', ($pSheet->getProtection()->getSheet() ? 'true' : 'false'));
        $xDoc->writeAttribute('objects', ($pSheet->getProtection()->getObjects() ? 'true' : 'false'));
        $xDoc->writeAttribute('scenarios', ($pSheet->getProtection()->getScenarios() ? 'true' : 'false'));
        $xDoc->writeAttribute('formatCells', ($pSheet->getProtection()->getFormatCells() ? 'true' : 'false'));
        $xDoc->writeAttribute('formatColumns', ($pSheet->getProtection()->getFormatColumns() ? 'true' : 'false'));
        $xDoc->writeAttribute('formatRows', ($pSheet->getProtection()->getFormatRows() ? 'true' : 'false'));
        $xDoc->writeAttribute('insertColumns', ($pSheet->getProtection()->getInsertColumns() ? 'true' : 'false'));
        $xDoc->writeAttribute('insertRows', ($pSheet->getProtection()->getInsertRows() ? 'true' : 'false'));
        $xDoc->writeAttribute('insertHyperlinks', ($pSheet->getProtection()->getInsertHyperlinks() ? 'true' : 'false'));
        $xDoc->writeAttribute('deleteColumns', ($pSheet->getProtection()->getDeleteColumns() ? 'true' : 'false'));
        $xDoc->writeAttribute('deleteRows', ($pSheet->getProtection()->getDeleteRows() ? 'true' : 'false'));
        $xDoc->writeAttribute('selectLockedCells', ($pSheet->getProtection()->getSelectLockedCells() ? 'true' : 'false'));
        $xDoc->writeAttribute('sort', ($pSheet->getProtection()->getSort() ? 'true' : 'false'));
        $xDoc->writeAttribute('autoFilter', ($pSheet->getProtection()->getAutoFilter() ? 'true' : 'false'));
        $xDoc->writeAttribute('pivotTables', ($pSheet->getProtection()->getPivotTables() ? 'true' : 'false'));
        $xDoc->writeAttribute('selectUnlockedCells', ($pSheet->getProtection()->getSelectUnlockedCells() ? 'true' : 'false'));
        $xDoc->endElement();
    }
    
    /**
     * Write ConditionalFormatting
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeConditionalFormatting(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // Conditional id
        $id = 1;
        
        // Loop trough styles in the current worksheet
        foreach ($pSheet->getStyles() as $cellCoordinate => $style)
        {
            if (count($style->getConditionalStyles()) > 0)
            {
                foreach ($style->getConditionalStyles() as $conditional)
                {
                    // WHY was this again?
                    // if ($this->getParentWriter()->getStylesConditionalHashTable()->getIndexForHashCode( $conditional->getHashCode() ) == '') {
                    //	continue;
                    // }
                    
                    if ($conditional->getConditionType() != ExcelStyle_Conditional::CONDITION_NONE)
                    {
                        // conditionalFormatting
                        $xDoc->startElement('conditionalFormatting');
                        $xDoc->writeAttribute('sqref', $cellCoordinate);
                        
                        // cfRule
                        $xDoc->startElement('cfRule');
                        $xDoc->writeAttribute('type', $conditional->getConditionType());
                        $xDoc->writeAttribute('dxfId', $this->getParentWriter()->getStylesConditionalHashTable()->getIndexForHashCode($conditional->getHashCode()));
                        $xDoc->writeAttribute('priority', $id++);
                        
                        if (($conditional->getConditionType() == ExcelStyle_Conditional::CONDITION_CELLIS || $conditional->getConditionType() == ExcelStyle_Conditional::CONDITION_CONTAINSTEXT) && $conditional->getOperatorType() != ExcelStyle_Conditional::OPERATOR_NONE)
                        {
                            $xDoc->writeAttribute('operator', $conditional->getOperatorType());
                        }
                        
                        if ($conditional->getConditionType() == ExcelStyle_Conditional::CONDITION_CONTAINSTEXT && !is_null($conditional->getText()))
                        {
                            $xDoc->writeAttribute('text', $conditional->getText());
                        }
                        
                        if ($conditional->getConditionType() == ExcelStyle_Conditional::CONDITION_CELLIS || $conditional->getConditionType() == ExcelStyle_Conditional::CONDITION_CONTAINSTEXT || $conditional->getConditionType() == ExcelStyle_Conditional::CONDITION_EXPRESSION)
                        {
                            foreach ($conditional->getConditions() as $formula)
                            {
                                // Formula
                                $xDoc->writeElement('formula', $formula);
                            }
                        }
                        
                        $xDoc->endElement();
                        
                        $xDoc->endElement();
                    }
                }
            }
        }
    }
    
    /**
     * Write DataValidations
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeDataValidations(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // Build a temporary array of datavalidation objects
        $aDataValidations = array();
        foreach ($pSheet->getCellCollection() as $cell)
        {
            if ($cell->hasDataValidation())
            {
                $aDataValidations[] = $cell->getDataValidation();
            }
        }
        
        // Write data validations?
        if (count($aDataValidations) > 0)
        {
            $xDoc->startElement('dataValidations');
            $xDoc->writeAttribute('count', count($aDataValidations));
            
            foreach ($aDataValidations as $dv)
            {
                $xDoc->startElement('dataValidation');
                
                if ($dv->getType() != '')
                {
                    $xDoc->writeAttribute('type', $dv->getType());
                }
                
                if ($dv->getErrorStyle() != '')
                {
                    $xDoc->writeAttribute('errorStyle', $dv->getErrorStyle());
                }
                
                if ($dv->getOperator() != '')
                {
                    $xDoc->writeAttribute('operator', $dv->getOperator());
                }
                
                $xDoc->writeAttribute('allowBlank', ($dv->getAllowBlank() ? '1' : '0'));
                $xDoc->writeAttribute('showDropDown', (!$dv->getShowDropDown() ? '1' : '0'));
                $xDoc->writeAttribute('showInputMessage', ($dv->getShowInputMessage() ? '1' : '0'));
                $xDoc->writeAttribute('showErrorMessage', ($dv->getShowErrorMessage() ? '1' : '0'));
                
                if ($dv->getErrorTitle() !== '')
                {
                    $xDoc->writeAttribute('errorTitle', $dv->getErrorTitle());
                }
                if ($dv->getError() !== '')
                {
                    $xDoc->writeAttribute('error', $dv->getError());
                }
                if ($dv->getPromptTitle() !== '')
                {
                    $xDoc->writeAttribute('promptTitle', $dv->getPromptTitle());
                }
                if ($dv->getPrompt() !== '')
                {
                    $xDoc->writeAttribute('prompt', $dv->getPrompt());
                }
                
                $xDoc->writeAttribute('sqref', $dv->getParent()->getCoordinate());
                
                if ($dv->getFormula1() !== '')
                {
                    $xDoc->writeElement('formula1', $dv->getFormula1());
                }
                if ($dv->getFormula2() !== '')
                {
                    $xDoc->writeElement('formula2', $dv->getFormula2());
                }
                
                $xDoc->endElement();
            }
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write Hyperlinks
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeHyperlinks(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // Build a temporary array of hyperlink objects
        $aHyperlinks = array();
        foreach ($pSheet->getCellCollection() as $cell)
        {
            if ($cell->hasHyperlink())
            {
                $aHyperlinks[] = $cell->getHyperlink();
            }
        }
        
        // Relation ID
        $relationId = 1;
        
        // Write hyperlinks?
        if (count($aHyperlinks) > 0)
        {
            $xDoc->startElement('hyperlinks');
            
            foreach ($aHyperlinks as $hyperlink)
            {
                $xDoc->startElement('hyperlink');
                
                $xDoc->writeAttribute('ref', $hyperlink->getParent()->getCoordinate());
                if (!$hyperlink->isInternal())
                {
                    $xDoc->writeAttribute('r:id', 'rId_hyperlink_' . $relationId);
                    ++$relationId;
                }
                else
                {
                    $xDoc->writeAttribute('location', str_replace('sheet://', '', $hyperlink->getUrl()));
                }
                
                if ($hyperlink->getTooltip() != '')
                {
                    $xDoc->writeAttribute('tooltip', $hyperlink->getTooltip());
                }
                
                $xDoc->endElement();
            }
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write ProtectedRanges
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeProtectedRanges(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        if (count($pSheet->getProtectedCells()) > 0)
        {
            // protectedRanges
            $xDoc->startElement('protectedRanges');
            
            // Loop protectedRanges
            foreach ($pSheet->getProtectedCells() as $protectedCell => $passwordHash)
            {
                // protectedRange
                $xDoc->startElement('protectedRange');
                $xDoc->writeAttribute('name', 'p' . md5($protectedCell));
                $xDoc->writeAttribute('sqref', $protectedCell);
                $xDoc->writeAttribute('password', $passwordHash);
                $xDoc->endElement();
            }
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write MergeCells
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeMergeCells(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        if (count($pSheet->getMergeCells()) > 0)
        {
            // mergeCells
            $xDoc->startElement('mergeCells');
            
            // Loop mergeCells
            foreach ($pSheet->getMergeCells() as $mergeCell)
            {
                // mergeCell
                $xDoc->startElement('mergeCell');
                $xDoc->writeAttribute('ref', $mergeCell);
                $xDoc->endElement();
            }
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write PrintOptions
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writePrintOptions(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // printOptions
        $xDoc->startElement('printOptions');
        
        $xDoc->writeAttribute('gridLines', ($pSheet->getPrintGridlines() ? 'true' : 'false'));
        $xDoc->writeAttribute('gridLinesSet', 'true');
        
        if ($pSheet->getPageSetup()->getHorizontalCentered())
        {
            $xDoc->writeAttribute('horizontalCentered', 'true');
        }
        
        if ($pSheet->getPageSetup()->getVerticalCentered())
        {
            $xDoc->writeAttribute('verticalCentered', 'true');
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write PageMargins
     *
     * @param	ExcelXmlWriter				$xDoc		XML Writer
     * @param	ExcelWorksheet						$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writePageMargins(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // pageMargins
        $xDoc->startElement('pageMargins');
        $xDoc->writeAttribute('left', ExcelShared_String::FormatNumber($pSheet->getPageMargins()->getLeft()));
        $xDoc->writeAttribute('right', ExcelShared_String::FormatNumber($pSheet->getPageMargins()->getRight()));
        $xDoc->writeAttribute('top', ExcelShared_String::FormatNumber($pSheet->getPageMargins()->getTop()));
        $xDoc->writeAttribute('bottom', ExcelShared_String::FormatNumber($pSheet->getPageMargins()->getBottom()));
        $xDoc->writeAttribute('header', ExcelShared_String::FormatNumber($pSheet->getPageMargins()->getHeader()));
        $xDoc->writeAttribute('footer', ExcelShared_String::FormatNumber($pSheet->getPageMargins()->getFooter()));
        $xDoc->endElement();
    }
    
    /**
     * Write AutoFilter
     *
     * @param	ExcelXmlWriter				$xDoc		XML Writer
     * @param	ExcelWorksheet						$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeAutoFilter(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        if ($pSheet->getAutoFilter() != '')
        {
            // autoFilter
            $xDoc->startElement('autoFilter');
            $xDoc->writeAttribute('ref', $pSheet->getAutoFilter());
            $xDoc->endElement();
        }
    }
    
    /**
     * Write PageSetup
     *
     * @param	ExcelXmlWriter			$xDoc		XML Writer
     * @param	ExcelWorksheet					$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writePageSetup(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // pageSetup
        $xDoc->startElement('pageSetup');
        $xDoc->writeAttribute('paperSize', $pSheet->getPageSetup()->getPaperSize());
        $xDoc->writeAttribute('orientation', $pSheet->getPageSetup()->getOrientation());
        
        if (!is_null($pSheet->getPageSetup()->getScale()))
        {
            $xDoc->writeAttribute('scale', $pSheet->getPageSetup()->getScale());
        }
        if (!is_null($pSheet->getPageSetup()->getFitToHeight()))
        {
            $xDoc->writeAttribute('fitToHeight', $pSheet->getPageSetup()->getFitToHeight());
        }
        else
        {
            $xDoc->writeAttribute('fitToHeight', '0');
        }
        if (!is_null($pSheet->getPageSetup()->getFitToWidth()))
        {
            $xDoc->writeAttribute('fitToWidth', $pSheet->getPageSetup()->getFitToWidth());
        }
        else
        {
            $xDoc->writeAttribute('fitToWidth', '0');
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write Header / Footer
     *
     * @param	ExcelXmlWriter		$xDoc		XML Writer
     * @param	ExcelWorksheet				$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeHeaderFooter($xDoc = null, $pSheet = null)
    {
        // headerFooter
        $xDoc->startElement('headerFooter');
        $xDoc->writeAttribute('differentOddEven', ($pSheet->getHeaderFooter()->getDifferentOddEven() ? 'true' : 'false'));
        $xDoc->writeAttribute('differentFirst', ($pSheet->getHeaderFooter()->getDifferentFirst() ? 'true' : 'false'));
        $xDoc->writeAttribute('scaleWithDoc', ($pSheet->getHeaderFooter()->getScaleWithDocument() ? 'true' : 'false'));
        $xDoc->writeAttribute('alignWithMargins', ($pSheet->getHeaderFooter()->getAlignWithMargins() ? 'true' : 'false'));
        
        $xDoc->writeElement('oddHeader', $pSheet->getHeaderFooter()->getOddHeader());
        $xDoc->writeElement('oddFooter', $pSheet->getHeaderFooter()->getOddFooter());
        $xDoc->writeElement('evenHeader', $pSheet->getHeaderFooter()->getEvenHeader());
        $xDoc->writeElement('evenFooter', $pSheet->getHeaderFooter()->getEvenFooter());
        $xDoc->writeElement('firstHeader', $pSheet->getHeaderFooter()->getFirstHeader());
        $xDoc->writeElement('firstFooter', $pSheet->getHeaderFooter()->getFirstFooter());
        $xDoc->endElement();
    }
    
    /**
     * Write Breaks
     *
     * @param	ExcelXmlWriter		$xDoc		XML Writer
     * @param	ExcelWorksheet				$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeBreaks(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null)
    {
        // Get row and column breaks
        $aRowBreaks    = array();
        $aColumnBreaks = array();
        foreach ($pSheet->getBreaks() as $cell => $breakType)
        {
            if ($breakType == ExcelWorksheet::BREAK_ROW)
            {
                $aRowBreaks[] = $cell;
            }
            else if ($breakType == ExcelWorksheet::BREAK_COLUMN)
            {
                $aColumnBreaks[] = $cell;
            }
        }
        
        // rowBreaks
        if (count($aRowBreaks) > 0)
        {
            $xDoc->startElement('rowBreaks');
            $xDoc->writeAttribute('count', count($aRowBreaks));
            $xDoc->writeAttribute('manualBreakCount', count($aRowBreaks));
            
            foreach ($aRowBreaks as $cell)
            {
                $coords = ExcelCell::coordinateFromString($cell);
                
                $xDoc->startElement('brk');
                $xDoc->writeAttribute('id', $coords[1]);
                $xDoc->writeAttribute('man', '1');
                $xDoc->endElement();
            }
            
            $xDoc->endElement();
        }
        
        // Second, write column breaks
        if (count($aColumnBreaks) > 0)
        {
            $xDoc->startElement('colBreaks');
            $xDoc->writeAttribute('count', count($aColumnBreaks));
            $xDoc->writeAttribute('manualBreakCount', count($aColumnBreaks));
            
            foreach ($aColumnBreaks as $cell)
            {
                $coords = ExcelCell::coordinateFromString($cell);
                
                $xDoc->startElement('brk');
                $xDoc->writeAttribute('id', ExcelCell::columnIndexFromString($coords[0]) - 1);
                $xDoc->writeAttribute('man', '1');
                $xDoc->endElement();
            }
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write SheetData
     *
     * @param	ExcelXmlWriter		$xDoc		XML Writer
     * @param	ExcelWorksheet				$pSheet			Worksheet
     * @param	string[]						$pStringTable	String table
     * @throws	Exception
     */
    private function _writeSheetData(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null, $pStringTable = null)
    {
        if (is_array($pStringTable))
        {
            // Flipped stringtable, for faster index searching
            $aFlippedStringTable = $this->getParentWriter()->getWriterPart('stringtable')->flipStringTable($pStringTable);
            
            // sheetData
            $xDoc->startElement('sheetData');
            
            // Get column count
            $colCount = ExcelCell::columnIndexFromString($pSheet->getHighestColumn());
            
            // Highest row number
            $highestRow = $pSheet->getHighestRow();
            
            // Loop trough cells
            $cellCollection = $pSheet->getCellCollection();
            
            $cellsByRow = array();
            foreach ($cellCollection as $cell)
            {
                $cellsByRow[$cell->getRow()][] = $cell;
            }
            
            for ($currentRow = 1; $currentRow <= $highestRow; ++$currentRow)
            {
                // Get row dimension
                $rowDimension = $pSheet->getRowDimension($currentRow);
                
                // Write current row?
                $writeCurrentRow = isset($cellsByRow[$currentRow]) || $rowDimension->getRowHeight() >= 0 || $rowDimension->getVisible() == false || $rowDimension->getCollapsed() == true || $rowDimension->getOutlineLevel() > 0;
                
                if ($writeCurrentRow)
                {
                    // Start a new row
                    $xDoc->startElement('row');
                    $xDoc->writeAttribute('r', $currentRow);
                    $xDoc->writeAttribute('spans', '1:' . $colCount);
                    
                    // Row dimensions
                    if ($rowDimension->getRowHeight() >= 0)
                    {
                        $xDoc->writeAttribute('customHeight', '1');
                        $xDoc->writeAttribute('ht', $rowDimension->getRowHeight());
                    }
                    
                    // Row visibility
                    if ($rowDimension->getVisible() == false)
                    {
                        $xDoc->writeAttribute('hidden', 'true');
                    }
                    
                    // Collapsed
                    if ($rowDimension->getCollapsed() == true)
                    {
                        $xDoc->writeAttribute('collapsed', 'true');
                    }
                    
                    // Outline level
                    if ($rowDimension->getOutlineLevel() > 0)
                    {
                        $xDoc->writeAttribute('outlineLevel', $rowDimension->getOutlineLevel());
                    }
                    
                    // Write cells
                    if (isset($cellsByRow[$currentRow]))
                    {
                        foreach ($cellsByRow[$currentRow] as $cell)
                        {
                            // Write cell
                            $this->_writeCell($xDoc, $pSheet, $cell, $pStringTable, $aFlippedStringTable);
                        }
                    }
                    
                    // End row
                    $xDoc->endElement();
                }
            }
            
            $xDoc->endElement();
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
    
    /**
     * Write Cell
     *
     * @param	ExcelXmlWriter	$xDoc				XML Writer
     * @param	ExcelWorksheet			$pSheet					Worksheet
     * @param	ExcelCell				$pCell					Cell
     * @param	string[]					$pStringTable			String table
     * @param	string[]					$pFlippedStringTable	String table (flipped), for faster index searching
     * @throws	Exception
     */
    private function _writeCell(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null, ExcelCell $pCell = null, $pStringTable = null, $pFlippedStringTable = null)
    {
        if (is_array($pStringTable) && is_array($pFlippedStringTable))
        {
            // Cell
            $xDoc->startElement('c');
            $xDoc->writeAttribute('r', $pCell->getCoordinate());
            
            // Sheet styles
            $aStyles    = $pSheet->getStyles();
            $styleIndex = '';
            if (isset($aStyles[$pCell->getCoordinate()]))
            {
                $styleIndex = $aStyles[$pCell->getCoordinate()]->getHashIndex();
            }
            else
            {
                $styleIndex = $pSheet->getDefaultStyle()->getHashIndex();
            }
            
            if ($styleIndex != '')
            {
                $xDoc->writeAttribute('s', $styleIndex);
            }
            
            // If cell value is supplied, write cell value
            if (is_object($pCell->getValue()) || $pCell->getValue() !== '')
            {
                // Map type
                $mappedType = $pCell->getDataType();
                
                // Write data type depending on its type
                switch (strtolower($mappedType))
                {
                    case 'inlinestr': // Inline string
                        $xDoc->writeAttribute('t', $mappedType);
                        break;
                    case 's': // String
                        $xDoc->writeAttribute('t', $mappedType);
                        break;
                    case 'b': // Boolean
                        $xDoc->writeAttribute('t', $mappedType);
                        break;
                    case 'f': // Formula
                        $calculatedValue = null;
                        if ($this->getParentWriter()->getPreCalculateFormulas())
                        {
                            $calculatedValue = $pCell->getCalculatedValue();
                        }
                        else
                        {
                            $calculatedValue = $pCell->getValue();
                        }
                        if (is_string($calculatedValue))
                        {
                            $xDoc->writeAttribute('t', 'str');
                        }
                        break;
                    case 'e': // Error
                        $xDoc->writeAttribute('t', $mappedType);
                }
                
                // Write data depending on its type
                switch (strtolower($mappedType))
                {
                    case 'inlinestr': // Inline string
                        if (!$pCell->getValue() instanceof ExcelRichText)
                        {
                            $xDoc->writeElement('t', ExcelShared_String::ControlCharacterPHP2OOXML(htmlspecialchars($pCell->getValue())));
                        }
                        else if ($pCell->getValue() instanceof ExcelRichText)
                        {
                            $xDoc->startElement('is');
                            $this->getParentWriter()->getWriterPart('stringtable')->writeRichText($xDoc, $pCell->getValue());
                            $xDoc->endElement();
                        }
                        
                        break;
                    case 's': // String
                        if (!$pCell->getValue() instanceof ExcelRichText)
                        {
                            if (isset($pFlippedStringTable[$pCell->getValue()]))
                            {
                                $xDoc->writeElement('v', $pFlippedStringTable[$pCell->getValue()]);
                            }
                        }
                        else if ($pCell->getValue() instanceof ExcelRichText)
                        {
                            $xDoc->writeElement('v', $pFlippedStringTable[$pCell->getValue()->getHashCode()]);
                        }
                        
                        break;
                    case 'f': // Formula
                        $xDoc->writeElement('f', substr($pCell->getValue(), 1));
                        if ($this->getParentWriter()->getOffice2003Compatibility() === false)
                        {
                            if ($this->getParentWriter()->getPreCalculateFormulas())
                            {
                                $calculatedValue = $pCell->getCalculatedValue();
                                if (!is_array($calculatedValue) && substr($calculatedValue, 0, 1) != '#')
                                {
                                    $xDoc->writeElement('v', $calculatedValue);
                                }
                                else
                                {
                                    $xDoc->writeElement('v', '0');
                                }
                            }
                            else
                            {
                                $xDoc->writeElement('v', '0');
                            }
                        }
                        break;
                    case 'n': // Numeric
                        if (ExcelShared_Date::isDateTime($pCell))
                        {
                            $dateValue = $pCell->getValue();
                            if (is_string($dateValue))
                            {
                                //	Error string
                                $xDoc->writeElement('v', $pFlippedStringTable[$dateValue]);
                            }
                            elseif (!is_float($dateValue))
                            {
                                //	PHP serialized date/time or date/time object
                                $xDoc->writeElement('v', ExcelShared_Date::PHPToExcel($dateValue));
                            }
                            else
                            {
                                //	Excel serialized date/time
                                $xDoc->writeElement('v', $dateValue);
                            }
                        }
                        else
                        {
                            $xDoc->writeElement('v', $pCell->getValue());
                        }
                        break;
                    case 'b': // Boolean
                        $xDoc->writeElement('v', ($pCell->getValue() ? '1' : '0'));
                        break;
                    case 'e': // Error
                        if (substr($pCell->getValue(), 0, 1) == '=')
                        {
                            $xDoc->writeElement('f', substr($pCell->getValue(), 1));
                            $xDoc->writeElement('v', substr($pCell->getValue(), 1));
                        }
                        else
                        {
                            $xDoc->writeElement('v', $pCell->getValue());
                        }
                        
                        break;
                }
            }
            
            $xDoc->endElement();
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
    
    /**
     * Write Drawings
     *
     * @param	ExcelXmlWriter		$xDoc		XML Writer
     * @param	ExcelWorksheet				$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeDrawings($xDoc = null, $pSheet = null)
    {
        // If sheet contains drawings, add the relationships
        if ($pSheet->getDrawingCollection()->count() > 0)
        {
            $xDoc->startElement('drawing');
            $xDoc->writeAttribute('r:id', 'rId1');
            $xDoc->endElement();
        }
    }
    
    /**
     * Write LegacyDrawing
     *
     * @param	ExcelXmlWriter		$xDoc		XML Writer
     * @param	ExcelWorksheet				$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeLegacyDrawing($xDoc = null, $pSheet = null)
    {
        // If sheet contains comments, add the relationships
        if (count($pSheet->getComments()) > 0)
        {
            $xDoc->startElement('legacyDrawing');
            $xDoc->writeAttribute('r:id', 'rId_comments_vml1');
            $xDoc->endElement();
        }
    }
    
    /**
     * Write LegacyDrawingHF
     *
     * @param	ExcelXmlWriter		$xDoc		XML Writer
     * @param	ExcelWorksheet				$pSheet			Worksheet
     * @throws	Exception
     */
    private function _writeLegacyDrawingHF($xDoc = null, $pSheet = null)
    {
        // If sheet contains comments, add the relationships
        if (count($pSheet->getHeaderFooter()->getImages()) > 0)
        {
            $xDoc->startElement('legacyDrawingHF');
            $xDoc->writeAttribute('r:id', 'rId_headerfooter_vml1');
            $xDoc->endElement();
        }
    }
}

/**
 * ExcelWriter_Excel2007_Comments
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_Comments extends ExcelWriterPart
{
    /**
     * Write comments to XML format
     *
     * @param 	ExcelWorksheet				$pWorksheet
     * @return 	string 								XML Output
     * @throws 	Exception
     */
    public function writeComments(ExcelWorksheet $pWorksheet = null)
    {
        // Create XML writer
        $xDoc = null;
        if ($this->getParentWriter()->getUseDiskCaching())
        {
            $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
        }
        else
        {
            $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_MEMORY);
        }
        
        // XML header
        $xDoc->startDocument('1.0', 'UTF-8', 'yes');
        
        // Comments cache
        $comments = $pWorksheet->getComments();
        
        // Authors cache
        $authors  = array();
        $authorId = 0;
        foreach ($comments as $comment)
        {
            if (!isset($authors[$comment->getAuthor()]))
            {
                $authors[$comment->getAuthor()] = $authorId++;
            }
        }
        
        // comments
        $xDoc->startElement('comments');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        
        // Loop trough authors
        $xDoc->startElement('authors');
        foreach ($authors as $author => $index)
        {
            $xDoc->writeElement('author', $author);
        }
        $xDoc->endElement();
        
        // Loop trough comments
        $xDoc->startElement('commentList');
        foreach ($comments as $key => $value)
        {
            $this->_writeComment($xDoc, $key, $value, $authors);
        }
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write comment to XML format
     *
     * @param 	ExcelXmlWriter		$xDoc 			XML Writer
     * @param	string							$pCellReference		Cell reference
     * @param 	ExcelComment				$pComment			Comment
     * @param	array							$pAuthors			Array of authors
     * @throws 	Exception
     */
    public function _writeComment(ExcelXmlWriter $xDoc = null, $pCellReference = 'A1', ExcelComment $pComment = null, $pAuthors = null)
    {
        // comment
        $xDoc->startElement('comment');
        $xDoc->writeAttribute('ref', $pCellReference);
        $xDoc->writeAttribute('authorId', $pAuthors[$pComment->getAuthor()]);
        
        // text
        $xDoc->startElement('text');
        $this->getParentWriter()->getWriterPart('stringtable')->writeRichText($xDoc, $pComment->getText());
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
    
    /**
     * Write VML comments to XML format
     *
     * @param 	ExcelWorksheet				$pWorksheet
     * @return 	string 								XML Output
     * @throws 	Exception
     */
    public function writeVMLComments(ExcelWorksheet $pWorksheet = null)
    {
        // Create XML writer
        $xDoc = null;
        if ($this->getParentWriter()->getUseDiskCaching())
        {
            $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
        }
        else
        {
            $xDoc = new ExcelXmlWriter(ExcelXmlWriter::STORAGE_MEMORY);
        }
        
        // XML header
        $xDoc->startDocument('1.0', 'UTF-8', 'yes');
        
        // Comments cache
        $comments = $pWorksheet->getComments();
        
        // xml
        $xDoc->startElement('xml');
        $xDoc->writeAttribute('xmlns:v', 'urn:schemas-microsoft-com:vml');
        $xDoc->writeAttribute('xmlns:o', 'urn:schemas-microsoft-com:office:office');
        $xDoc->writeAttribute('xmlns:x', 'urn:schemas-microsoft-com:office:excel');
        
        // o:shapelayout
        $xDoc->startElement('o:shapelayout');
        $xDoc->writeAttribute('v:ext', 'edit');
        
        // o:idmap
        $xDoc->startElement('o:idmap');
        $xDoc->writeAttribute('v:ext', 'edit');
        $xDoc->writeAttribute('data', '1');
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // v:shapetype
        $xDoc->startElement('v:shapetype');
        $xDoc->writeAttribute('id', '_x0000_t202');
        $xDoc->writeAttribute('coordsize', '21600,21600');
        $xDoc->writeAttribute('o:spt', '202');
        $xDoc->writeAttribute('path', 'm,l,21600r21600,l21600,xe');
        
        // v:stroke
        $xDoc->startElement('v:stroke');
        $xDoc->writeAttribute('joinstyle', 'miter');
        $xDoc->endElement();
        
        // v:path
        $xDoc->startElement('v:path');
        $xDoc->writeAttribute('gradientshapeok', 't');
        $xDoc->writeAttribute('o:connecttype', 'rect');
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // Loop trough comments
        foreach ($comments as $key => $value)
        {
            $this->_writeVMLComment($xDoc, $key, $value);
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write VML comment to XML format
     *
     * @param 	ExcelXmlWriter		$xDoc 			XML Writer
     * @param	string							$pCellReference		Cell reference
     * @param 	ExcelComment				$pComment			Comment
     * @throws 	Exception
     */
    public function _writeVMLComment(ExcelXmlWriter $xDoc = null, $pCellReference = 'A1', ExcelComment $pComment = null)
    {
        // Metadata
        list($column, $row) = ExcelCell::coordinateFromString($pCellReference);
        $column = ExcelCell::columnIndexFromString($column);
        $id     = 1024 + $column + $row;
        $id     = substr($id, 0, 4);
        
        // v:shape
        $xDoc->startElement('v:shape');
        $xDoc->writeAttribute('id', '_x0000_s' . $id);
        $xDoc->writeAttribute('type', '#_x0000_t202');
        $xDoc->writeAttribute('style', 'position:absolute;margin-left:' . $pComment->getMarginLeft() . ';margin-top:' . $pComment->getMarginTop() . ';width:' . $pComment->getWidth() . ';height:' . $pComment->getHeight() . ';z-index:1;visibility:' . ($pComment->getVisible() ? 'visible' : 'hidden'));
        $xDoc->writeAttribute('fillcolor', '#' . $pComment->getFillColor()->getRGB());
        $xDoc->writeAttribute('o:insetmode', 'auto');
        
        // v:fill
        $xDoc->startElement('v:fill');
        $xDoc->writeAttribute('color2', '#' . $pComment->getFillColor()->getRGB());
        $xDoc->endElement();
        
        // v:shadow
        $xDoc->startElement('v:shadow');
        $xDoc->writeAttribute('on', 't');
        $xDoc->writeAttribute('color', 'black');
        $xDoc->writeAttribute('obscured', 't');
        $xDoc->endElement();
        
        // v:path
        $xDoc->startElement('v:path');
        $xDoc->writeAttribute('o:connecttype', 'none');
        $xDoc->endElement();
        
        // v:textbox
        $xDoc->startElement('v:textbox');
        $xDoc->writeAttribute('style', 'mso-direction-alt:auto');
        
        // div
        $xDoc->startElement('div');
        $xDoc->writeAttribute('style', 'text-align:left');
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // x:ClientData
        $xDoc->startElement('x:ClientData');
        $xDoc->writeAttribute('ObjectType', 'Note');
        
        // x:MoveWithCells
        $xDoc->writeElement('x:MoveWithCells', '');
        
        // x:SizeWithCells
        $xDoc->writeElement('x:SizeWithCells', '');
        
        // x:Anchor
        //$xDoc->writeElement('x:Anchor', $column . ', 15, ' . ($row - 2) . ', 10, ' . ($column + 4) . ', 15, ' . ($row + 5) . ', 18');
        
        // x:AutoFill
        $xDoc->writeElement('x:AutoFill', 'False');
        
        // x:Row
        $xDoc->writeElement('x:Row', ($row - 1));
        
        // x:Column
        $xDoc->writeElement('x:Column', ($column - 1));
        
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
}
?>