<?php
/**
 * ExcelWriter_Excel2007_Workbook
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_Workbook extends ExcelWriterPart
{
    /**
     * Write workbook to XML format
     *
     * @param 	ExcelWorkbook	$workbook
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeWorkbook($workbook = null)
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
        
        // workbook
        $xDoc->startElement('workbook');
        $xDoc->writeAttribute('xml:space', 'preserve');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xDoc->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        
        // fileVersion
        $this->_writeFileVersion($xDoc);
        
        // workbookPr
        $this->_writeWorkbookPr($xDoc);
        
        // workbookProtection
        $this->_writeWorkbookProtection($xDoc, $workbook);
        
        // bookViews
        if ($this->getParentWriter()->getOffice2003Compatibility() === false)
        {
            $this->_writeBookViews($xDoc, $workbook);
        }
        
        // sheets
        $this->_writeSheets($xDoc, $workbook);
        
        // definedNames
        $this->_writeDefinedNames($xDoc, $workbook);
        
        // calcPr
        $this->_writeCalcPr($xDoc);
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write file version
     *
     * @param 	ExcelXmlWriter $xDoc 		XML Writer
     * @throws 	Exception
     */
    private function _writeFileVersion(ExcelXmlWriter $xDoc = null)
    {
        $xDoc->startElement('fileVersion');
        $xDoc->writeAttribute('appName', 'xl');
        $xDoc->writeAttribute('lastEdited', '4');
        $xDoc->writeAttribute('lowestEdited', '4');
        $xDoc->writeAttribute('rupBuild', '4505');
        $xDoc->endElement();
    }
    
    /**
     * Write WorkbookPr
     *
     * @param 	ExcelXmlWriter $xDoc 		XML Writer
     * @throws 	Exception
     */
    private function _writeWorkbookPr(ExcelXmlWriter $xDoc = null)
    {
        $xDoc->startElement('workbookPr');
        
        if (ExcelShared_Date::getExcelCalendar() == ExcelShared_Date::CALENDAR_MAC_1904)
        {
            $xDoc->writeAttribute('date1904', '1');
        }
        
        $xDoc->writeAttribute('codeName', 'ThisWorkbook');
        
        $xDoc->endElement();
    }
    
    /**
     * Write BookViews
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	ExcelWorkbook					$workbook
     * @throws 	Exception
     */
    private function _writeBookViews($xDoc = null, $workbook = null)
    {
        // bookViews
        $xDoc->startElement('bookViews');
        
        // workbookView
        $xDoc->startElement('workbookView');
        
        $xDoc->writeAttribute('activeTab', $workbook->getActiveSheetIndex());
        $xDoc->writeAttribute('autoFilterDateGrouping', '1');
        $xDoc->writeAttribute('firstSheet', '0');
        $xDoc->writeAttribute('minimized', '0');
        $xDoc->writeAttribute('showHorizontalScroll', '1');
        $xDoc->writeAttribute('showSheetTabs', '1');
        $xDoc->writeAttribute('showVerticalScroll', '1');
        $xDoc->writeAttribute('tabRatio', '600');
        $xDoc->writeAttribute('visibility', 'visible');
        
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
    
    /**
     * Write WorkbookProtection
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	ExcelWorkbook					$workbook
     * @throws 	Exception
     */
    private function _writeWorkbookProtection($xDoc = null, $workbook = null)
    {
        if ($workbook->getSecurity()->isSecurityEnabled())
        {
            $xDoc->startElement('workbookProtection');
            $xDoc->writeAttribute('lockRevision', ($workbook->getSecurity()->getLockRevision() ? 'true' : 'false'));
            $xDoc->writeAttribute('lockStructure', ($workbook->getSecurity()->getLockStructure() ? 'true' : 'false'));
            $xDoc->writeAttribute('lockWindows', ($workbook->getSecurity()->getLockWindows() ? 'true' : 'false'));
            
            if ($workbook->getSecurity()->getRevisionsPassword() != '')
            {
                $xDoc->writeAttribute('revisionsPassword', $workbook->getSecurity()->getRevisionsPassword());
            }
            
            if ($workbook->getSecurity()->getWorkbookPassword() != '')
            {
                $xDoc->writeAttribute('workbookPassword', $workbook->getSecurity()->getWorkbookPassword());
            }
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write calcPr
     *
     * @param 	ExcelXmlWriter $xDoc 		XML Writer
     * @throws 	Exception
     */
    private function _writeCalcPr($xDoc = null)
    {
        $xDoc->startElement('calcPr');
        
        $xDoc->writeAttribute('calcId', '124519');
        $xDoc->writeAttribute('calcMode', 'auto');
        $xDoc->writeAttribute('fullCalcOnLoad', '1');
        
        $xDoc->endElement();
    }
    
    /**
     * Write sheets
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	ExcelWorkbook					$workbook
     * @throws 	Exception
     */
    private function _writeSheets($xDoc = null, $workbook = null)
    {
        // Write sheets
        $xDoc->startElement('sheets');
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            // sheet
            $this->_writeSheet($xDoc, $workbook->getSheet($i)->getTitle(), ($i + 1), ($i + 1 + 3));
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write sheet
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	string 						$pSheetname 		Sheet name
     * @param 	int							$pSheetId	 		Sheet id
     * @param 	int							$pRelId				Relationship ID
     * @throws 	Exception
     */
    private function _writeSheet($xDoc = null, $pSheetname = '', $pSheetId = 1, $pRelId = 1)
    {
        if ($pSheetname != '')
        {
            // Write sheet
            $xDoc->startElement('sheet');
            $xDoc->writeAttribute('name', $pSheetname);
            $xDoc->writeAttribute('sheetId', $pSheetId);
            $xDoc->writeAttribute('r:id', 'rId' . $pRelId);
            $xDoc->endElement();
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
    
    /**
     * Write Defined Names
     *
     * @param 	ExcelXmlWriter	$xDoc 		XML Writer
     * @param 	ExcelWorkbook					$workbook
     * @throws 	Exception
     */
    private function _writeDefinedNames($xDoc = null, $workbook = null)
    {
        // Write defined names
        $xDoc->startElement('definedNames');
        
        // Named ranges
        if (count($workbook->getNamedRanges()) > 0)
        {
            // Named ranges
            $this->_writeNamedRanges($xDoc, $workbook);
        }
        
        // Other defined names
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            // definedName for autoFilter
            $this->_writeDefinedNameForAutofilter($xDoc, $workbook->getSheet($i), $i);
            
            // definedName for Print_Titles
            $this->_writeDefinedNameForPrintTitles($xDoc, $workbook->getSheet($i), $i);
            
            // definedName for Print_Area
            $this->_writeDefinedNameForPrintArea($xDoc, $workbook->getSheet($i), $i);
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write named ranges
     *
     * @param 	ExcelXmlWriter	$xDoc 		XML Writer
     * @param 	ExcelWorkbook					$workbook
     * @throws 	Exception
     */
    private function _writeNamedRanges(ExcelXmlWriter $xDoc = null, PHPExcel $workbook)
    {
        // Loop named ranges
        $namedRanges = $workbook->getNamedRanges();
        foreach ($namedRanges as $namedRange)
        {
            $this->_writeDefinedNameForNamedRange($xDoc, $namedRange);
        }
    }
    
    /**
     * Write Defined Name for autoFilter
     *
     * @param 	ExcelXmlWriter	$xDoc 		XML Writer
     * @param 	ExcelNamedRange			$pNamedRange
     * @throws 	Exception
     */
    private function _writeDefinedNameForNamedRange($xDoc = null, $pNamedRange)
    {
        // definedName for named range
        $xDoc->startElement('definedName');
        $xDoc->writeAttribute('name', $pNamedRange->getName());
        if ($pNamedRange->getLocalOnly())
        {
            $xDoc->writeAttribute('localSheetId', $pNamedRange->getWorksheet()->getParent()->getIndex($pNamedRange->getWorksheet()));
        }
        
        // Create absolute coordinate and write as raw text
        $range = ExcelCell::splitRange($pNamedRange->getRange());
        for ($i = 0; $i < count($range); $i++)
        {
            $range[$i][0] = '\'' . str_replace("'", "''", $pNamedRange->getWorksheet()->getTitle()) . '\'!' . ExcelCell::absoluteCoordinate($range[$i][0]);
            if (isset($range[$i][1]))
            {
                $range[$i][1] = ExcelCell::absoluteCoordinate($range[$i][1]);
            }
        }
        $range = ExcelCell::buildRange($range);
        
        $xDoc->writeRaw($range);
        
        $xDoc->endElement();
    }
    
    /**
     * Write Defined Name for autoFilter
     *
     * @param 	ExcelXmlWriter	$xDoc 		XML Writer
     * @param 	ExcelWorksheet			$pSheet
     * @param 	int							$pSheetId
     * @throws 	Exception
     */
    private function _writeDefinedNameForAutofilter(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null, $pSheetId = 0)
    {
        // definedName for autoFilter
        if ($pSheet->getAutoFilter() != '')
        {
            $xDoc->startElement('definedName');
            $xDoc->writeAttribute('name', '_xlnm._FilterDatabase');
            $xDoc->writeAttribute('localSheetId', $pSheetId);
            $xDoc->writeAttribute('hidden', '1');
            
            // Create absolute coordinate and write as raw text
            $range    = ExcelCell::splitRange($pSheet->getAutoFilter());
            $range    = $range[0];
            $range[0] = ExcelCell::absoluteCoordinate($range[0]);
            $range[1] = ExcelCell::absoluteCoordinate($range[1]);
            $range    = implode(':', $range);
            
            $xDoc->writeRaw('\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!' . $range);
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write Defined Name for PrintTitles
     *
     * @param 	ExcelXmlWriter	$xDoc 		XML Writer
     * @param 	ExcelWorksheet			$pSheet
     * @param 	int							$pSheetId
     * @throws 	Exception
     */
    private function _writeDefinedNameForPrintTitles(ExcelXmlWriter $xDoc = null, ExcelWorksheet $pSheet = null, $pSheetId = 0)
    {
        // definedName for PrintTitles
        if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet() || $pSheet->getPageSetup()->isRowsToRepeatAtTopSet())
        {
            $xDoc->startElement('definedName');
            $xDoc->writeAttribute('name', '_xlnm.Print_Titles');
            $xDoc->writeAttribute('localSheetId', $pSheetId);
            
            // Setting string
            $settingString = '';
            
            // Columns to repeat
            if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet())
            {
                $repeat = $pSheet->getPageSetup()->getColumnsToRepeatAtLeft();
                
                $settingString = '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!$' . $repeat[0] . ':$' . $repeat[1];
            }
            
            // Rows to repeat
            if ($pSheet->getPageSetup()->isRowsToRepeatAtTopSet())
            {
                if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet())
                {
                    $settingString .= ',';
                }
                
                $repeat = $pSheet->getPageSetup()->getRowsToRepeatAtTop();
                
                $settingString = '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!$' . $repeat[0] . ':$' . $repeat[1];
            }
            
            $xDoc->writeRaw($settingString);
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write Defined Name for PrintTitles
     *
     * @param 	ExcelXmlWriter	$xDoc 		XML Writer
     * @param 	ExcelWorksheet			$pSheet
     * @param 	int							$pSheetId
     * @throws 	Exception
     */
    private function _writeDefinedNameForPrintArea($xDoc = null, $pSheet = null, $pSheetId = 0)
    {
        // definedName for PrintArea
        if ($pSheet->getPageSetup()->isPrintAreaSet())
        {
            $xDoc->startElement('definedName');
            $xDoc->writeAttribute('name', '_xlnm.Print_Area');
            $xDoc->writeAttribute('localSheetId', $pSheetId);
            
            // Setting string
            $settingString = '';
            
            // Print area
            $printArea    = ExcelCell::splitRange($pSheet->getPageSetup()->getPrintArea());
            $printArea    = $printArea[0];
            $printArea[0] = ExcelCell::absoluteCoordinate($printArea[0]);
            $printArea[1] = ExcelCell::absoluteCoordinate($printArea[1]);
            
            $xDoc->writeRaw('\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!' . implode(':', $printArea));
            
            $xDoc->endElement();
        }
    }
}
?>