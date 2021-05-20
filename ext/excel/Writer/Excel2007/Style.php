<?php
/**
 * ExcelWriter_Excel2007_Style
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_Style extends ExcelWriterPart
{
    /**
     * Write styles to XML format
     *
     * @param 	ExcelWorkbook	$workbook
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeStyles($workbook = null)
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
        
        // styleSheet
        $xDoc->startElement('styleSheet');
        $xDoc->writeAttribute('xml:space', 'preserve');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        
        // numFmts
        $xDoc->startElement('numFmts');
        $xDoc->writeAttribute('count', $this->getParentWriter()->getNumFmtHashTable()->getCount());
        
        // numFmt
        for ($i = 0; $i < $this->getParentWriter()->getNumFmtHashTable()->getCount(); ++$i)
        {
            $this->_writeNumFmt($xDoc, $this->getParentWriter()->getNumFmtHashTable()->getByIndex($i), $i);
        }
        
        $xDoc->endElement();
        
        // fonts
        $xDoc->startElement('fonts');
        $xDoc->writeAttribute('count', $this->getParentWriter()->getFontHashTable()->getCount());
        
        // font
        for ($i = 0; $i < $this->getParentWriter()->getFontHashTable()->getCount(); ++$i)
        {
            $this->_writeFont($xDoc, $this->getParentWriter()->getFontHashTable()->getByIndex($i));
        }
        
        $xDoc->endElement();
        
        // fills
        $xDoc->startElement('fills');
        $xDoc->writeAttribute('count', $this->getParentWriter()->getFillHashTable()->getCount());
        
        // fill
        for ($i = 0; $i < $this->getParentWriter()->getFillHashTable()->getCount(); ++$i)
        {
            $this->_writeFill($xDoc, $this->getParentWriter()->getFillHashTable()->getByIndex($i));
        }
        
        $xDoc->endElement();
        
        // borders
        $xDoc->startElement('borders');
        $xDoc->writeAttribute('count', $this->getParentWriter()->getBordersHashTable()->getCount());
        
        // border
        for ($i = 0; $i < $this->getParentWriter()->getBordersHashTable()->getCount(); ++$i)
        {
            $this->_writeBorder($xDoc, $this->getParentWriter()->getBordersHashTable()->getByIndex($i));
        }
        
        $xDoc->endElement();
        
        // cellStyleXfs
        $xDoc->startElement('cellStyleXfs');
        $xDoc->writeAttribute('count', $this->getParentWriter()->getStylesHashTable()->getCount());
        
        // xf
        $xDoc->startElement('xf');
        $xDoc->writeAttribute('numFmtId', 0);
        $xDoc->writeAttribute('fontId', 0);
        $xDoc->writeAttribute('fillId', 0);
        $xDoc->writeAttribute('borderId', 0);
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // cellXfs
        $xDoc->startElement('cellXfs');
        $xDoc->writeAttribute('count', $this->getParentWriter()->getStylesHashTable()->getCount());
        
        // xf
        for ($i = 0; $i < $this->getParentWriter()->getStylesHashTable()->getCount(); ++$i)
        {
            $this->_writeCellStyleXf($xDoc, $this->getParentWriter()->getStylesHashTable()->getByIndex($i));
        }
        
        $xDoc->endElement();
        
        // cellStyles
        $xDoc->startElement('cellStyles');
        $xDoc->writeAttribute('count', 1);
        
        // cellStyle
        $xDoc->startElement('cellStyle');
        $xDoc->writeAttribute('name', 'Normal');
        $xDoc->writeAttribute('xfId', 0);
        $xDoc->writeAttribute('builtinId', 0);
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // dxfs
        $xDoc->startElement('dxfs');
        $xDoc->writeAttribute('count', $this->getParentWriter()->getStylesConditionalHashTable()->getCount());
        
        // dxf
        for ($i = 0; $i < $this->getParentWriter()->getStylesConditionalHashTable()->getCount(); ++$i)
        {
            $this->_writeCellStyleDxf($xDoc, $this->getParentWriter()->getStylesConditionalHashTable()->getByIndex($i)->getStyle());
        }
        
        $xDoc->endElement();
        
        // tableStyles
        $xDoc->startElement('tableStyles');
        $xDoc->writeAttribute('defaultTableStyle', 'TableStyleMedium9');
        $xDoc->writeAttribute('defaultPivotStyle', 'PivotTableStyle1');
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write Fill
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	ExcelStyle_Fill			$pFill			Fill style
     * @throws 	Exception
     */
    private function _writeFill($xDoc = null, $pFill = null)
    {
        // Check if this is a pattern type or gradient type
        if ($pFill->getFillType() == ExcelStyleFill::FILL_GRADIENT_LINEAR || $pFill->getFillType() == ExcelStyleFill::FILL_GRADIENT_PATH)
        {
            // Gradient fill
            $this->_writeGradientFill($xDoc, $pFill);
        }
        else
        {
            // Pattern fill
            $this->_writePatternFill($xDoc, $pFill);
        }
    }
    
    /**
     * Write Gradient Fill
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	ExcelStyle_Fill			$pFill			Fill style
     * @throws 	Exception
     */
    private function _writeGradientFill($xDoc = null, $pFill = null)
    {
        // fill
        $xDoc->startElement('fill');
        
        // gradientFill
        $xDoc->startElement('gradientFill');
        $xDoc->writeAttribute('type', $pFill->getFillType());
        $xDoc->writeAttribute('degree', $pFill->getRotation());
        
        // stop
        $xDoc->startElement('stop');
        $xDoc->writeAttribute('position', '0');
        
        // color
        $xDoc->startElement('color');
        $xDoc->writeAttribute('rgb', $pFill->getStartColor()->getARGB());
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // stop
        $xDoc->startElement('stop');
        $xDoc->writeAttribute('position', '1');
        
        // color
        $xDoc->startElement('color');
        $xDoc->writeAttribute('rgb', $pFill->getEndColor()->getARGB());
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
    
    /**
     * Write Pattern Fill
     *
     * @param 	ExcelXmlWriter			$xDoc 		XML Writer
     * @param 	ExcelStyle_Fill					$pFill			Fill style
     * @throws 	Exception
     */
    private function _writePatternFill($xDoc = null, $pFill = null)
    {
        // fill
        $xDoc->startElement('fill');
        
        // patternFill
        $xDoc->startElement('patternFill');
        $xDoc->writeAttribute('patternType', $pFill->getFillType());
        
        // fgColor
        $xDoc->startElement('fgColor');
        $xDoc->writeAttribute('rgb', $pFill->getStartColor()->getARGB());
        $xDoc->endElement();
        
        // bgColor
        $xDoc->startElement('bgColor');
        $xDoc->writeAttribute('rgb', $pFill->getEndColor()->getARGB());
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
    
    /**
     * Write Font
     *
     * @param 	ExcelXmlWriter		$xDoc 		XML Writer
     * @param 	ExcelStyleFont				$pFont			Font style
     * @throws 	Exception
     */
    private function _writeFont($xDoc = null, $pFont = null)
    {
        // font
        $xDoc->startElement('font');
        
        // Name
        $xDoc->startElement('name');
        $xDoc->writeAttribute('val', $pFont->getName());
        $xDoc->endElement();
        
        // Size
        $xDoc->startElement('sz');
        $xDoc->writeAttribute('val', $pFont->getSize());
        $xDoc->endElement();
        
        // Bold
        if ($pFont->getBold())
        {
            $xDoc->startElement('b');
            $xDoc->writeAttribute('val', 'true');
            $xDoc->endElement();
        }
        
        // Italic
        if ($pFont->getItalic())
        {
            $xDoc->startElement('i');
            $xDoc->writeAttribute('val', 'true');
            $xDoc->endElement();
        }
        
        // Superscript / subscript
        if ($pFont->getSuperScript() || $pFont->getSubScript())
        {
            $xDoc->startElement('vertAlign');
            if ($pFont->getSuperScript())
            {
                $xDoc->writeAttribute('val', 'superscript');
            }
            else if ($pFont->getSubScript())
            {
                $xDoc->writeAttribute('val', 'subscript');
            }
            $xDoc->endElement();
        }
        
        // Underline
        $xDoc->startElement('u');
        $xDoc->writeAttribute('val', $pFont->getUnderline());
        $xDoc->endElement();
        
        // Striketrough
        if ($pFont->getStriketrough())
        {
            $xDoc->startElement('strike');
            $xDoc->writeAttribute('val', 'true');
            $xDoc->endElement();
        }
        
        // Foreground color
        $xDoc->startElement('color');
        $xDoc->writeAttribute('rgb', $pFont->getColor()->getARGB());
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
    
    /**
     * Write Border
     *
     * @param 	ExcelXmlWriter			$xDoc 		XML Writer
     * @param 	ExcelStyleBorders				$pBorders		Borders style
     * @throws 	Exception
     */
    private function _writeBorder($xDoc = null, $pBorders = null)
    {
        // Write border
        $xDoc->startElement('border');
        // Diagonal?
        switch ($pBorders->getDiagonalDirection())
        {
            case ExcelStyleBorders::DIAGONAL_UP:
                $xDoc->writeAttribute('diagonalUp', 'true');
                $xDoc->writeAttribute('diagonalDown', 'false');
                break;
            case ExcelStyleBorders::DIAGONAL_DOWN:
                $xDoc->writeAttribute('diagonalUp', 'false');
                $xDoc->writeAttribute('diagonalDown', 'true');
                break;
        }
        
        // Outline?
        $xDoc->writeAttribute('outline', ($pBorders->getOutline() ? 'true' : 'false'));
        
        // BorderPr
        $this->_writeBorderPr($xDoc, 'left', $pBorders->getLeft());
        $this->_writeBorderPr($xDoc, 'right', $pBorders->getRight());
        $this->_writeBorderPr($xDoc, 'top', $pBorders->getTop());
        $this->_writeBorderPr($xDoc, 'bottom', $pBorders->getBottom());
        $this->_writeBorderPr($xDoc, 'diagonal', $pBorders->getDiagonal());
        $this->_writeBorderPr($xDoc, 'vertical', $pBorders->getVertical());
        $this->_writeBorderPr($xDoc, 'horizontal', $pBorders->getHorizontal());
        $xDoc->endElement();
    }
    
    /**
     * Write Cell Style Xf
     *
     * @param 	ExcelXmlWriter			$xDoc 		XML Writer
     * @param 	ExcelStyle						$pStyle			Style
     * @throws 	Exception
     */
    private function _writeCellStyleXf($xDoc = null, $pStyle = null)
    {
        // xf
        $xDoc->startElement('xf');
        $xDoc->writeAttribute('xfId', 0);
        $xDoc->writeAttribute('fontId', (int) $this->getParentWriter()->getFontHashTable()->getIndexForHashCode($pStyle->getFont()->getHashCode()));
        $xDoc->writeAttribute('numFmtId', (int) ($this->getParentWriter()->getNumFmtHashTable()->getIndexForHashCode($pStyle->getNumberFormat()->getHashCode()) + 164));
        $xDoc->writeAttribute('fillId', (int) $this->getParentWriter()->getFillHashTable()->getIndexForHashCode($pStyle->getFill()->getHashCode()));
        $xDoc->writeAttribute('borderId', (int) $this->getParentWriter()->getBordersHashTable()->getIndexForHashCode($pStyle->getBorders()->getHashCode()));
        
        // Apply styles?
        $xDoc->writeAttribute('applyFont', (ExcelStyle::getDefaultStyle()->getFont()->getHashCode() != $pStyle->getFont()->getHashCode()) ? '1' : '0');
        $xDoc->writeAttribute('applyNumberFormat', (ExcelStyle::getDefaultStyle()->getNumberFormat()->getHashCode() != $pStyle->getNumberFormat()->getHashCode()) ? '1' : '0');
        $xDoc->writeAttribute('applyFill', (ExcelStyle::getDefaultStyle()->getFill()->getHashCode() != $pStyle->getFill()->getHashCode()) ? '1' : '0');
        $xDoc->writeAttribute('applyBorder', (ExcelStyle::getDefaultStyle()->getBorders()->getHashCode() != $pStyle->getBorders()->getHashCode()) ? '1' : '0');
        $xDoc->writeAttribute('applyAlignment', (ExcelStyle::getDefaultStyle()->getAlignment()->getHashCode() != $pStyle->getAlignment()->getHashCode()) ? '1' : '0');
        if ($pStyle->getProtection()->getLocked() != ExcelStyleProtection::PROTECTION_INHERIT || $pStyle->getProtection()->getHidden() != ExcelStyleProtection::PROTECTION_INHERIT)
        {
            $xDoc->writeAttribute('applyProtection', 'true');
        }
        
        // alignment
        $xDoc->startElement('alignment');
        $xDoc->writeAttribute('horizontal', $pStyle->getAlignment()->getHorizontal());
        $xDoc->writeAttribute('vertical', $pStyle->getAlignment()->getVertical());
        
        $textRotation = 0;
        if ($pStyle->getAlignment()->getTextRotation() >= 0)
        {
            $textRotation = $pStyle->getAlignment()->getTextRotation();
        }
        else if ($pStyle->getAlignment()->getTextRotation() < 0)
        {
            $textRotation = 90 - $pStyle->getAlignment()->getTextRotation();
        }
        
        $xDoc->writeAttribute('textRotation', $textRotation);
        $xDoc->writeAttribute('wrapText', ($pStyle->getAlignment()->getWrapText() ? 'true' : 'false'));
        $xDoc->writeAttribute('shrinkToFit', ($pStyle->getAlignment()->getShrinkToFit() ? 'true' : 'false'));
        
        if ($pStyle->getAlignment()->getIndent() > 0)
        {
            $xDoc->writeAttribute('indent', $pStyle->getAlignment()->getIndent());
        }
        $xDoc->endElement();
        
        // protection
        if ($pStyle->getProtection()->getLocked() != ExcelStyleProtection::PROTECTION_INHERIT || $pStyle->getProtection()->getHidden() != ExcelStyleProtection::PROTECTION_INHERIT)
        {
            $xDoc->startElement('protection');
            if ($pStyle->getProtection()->getLocked() != ExcelStyleProtection::PROTECTION_INHERIT)
            {
                $xDoc->writeAttribute('locked', ($pStyle->getProtection()->getLocked() == ExcelStyleProtection::PROTECTION_PROTECTED ? 'true' : 'false'));
            }
            if ($pStyle->getProtection()->getHidden() != ExcelStyleProtection::PROTECTION_INHERIT)
            {
                $xDoc->writeAttribute('hidden', ($pStyle->getProtection()->getHidden() == ExcelStyleProtection::PROTECTION_PROTECTED ? 'true' : 'false'));
            }
            $xDoc->endElement();
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write Cell Style Dxf
     *
     * @param 	ExcelXmlWriter 		$xDoc 		XML Writer
     * @param 	ExcelStyle					$pStyle			Style
     * @throws 	Exception
     */
    private function _writeCellStyleDxf(ExcelXmlWriter $xDoc = null, ExcelStyle $pStyle = null)
    {
        // dxf
        $xDoc->startElement('dxf');
        
        // font
        $this->_writeFont($xDoc, $pStyle->getFont());
        
        // numFmt
        $this->_writeNumFmt($xDoc, $pStyle->getNumberFormat());
        
        // fill
        $this->_writeFill($xDoc, $pStyle->getFill());
        
        // alignment
        $xDoc->startElement('alignment');
        $xDoc->writeAttribute('horizontal', $pStyle->getAlignment()->getHorizontal());
        $xDoc->writeAttribute('vertical', $pStyle->getAlignment()->getVertical());
        
        $textRotation = 0;
        if ($pStyle->getAlignment()->getTextRotation() >= 0)
        {
            $textRotation = $pStyle->getAlignment()->getTextRotation();
        }
        else if ($pStyle->getAlignment()->getTextRotation() < 0)
        {
            $textRotation = 90 - $pStyle->getAlignment()->getTextRotation();
        }
        
        $xDoc->writeAttribute('textRotation', $textRotation);
        $xDoc->endElement();
        
        // border
        $this->_writeBorder($xDoc, $pStyle->getBorders());
        
        // protection
        if ($pStyle->getProtection()->getLocked() != ExcelStyle_Protection::PROTECTION_INHERIT || $pStyle->getProtection()->getHidden() != ExcelStyle_Protection::PROTECTION_INHERIT)
        {
            $xDoc->startElement('protection');
            if ($pStyle->getProtection()->getLocked() != ExcelStyle_Protection::PROTECTION_INHERIT)
            {
                $xDoc->writeAttribute('locked', ($pStyle->getProtection()->getLocked() == ExcelStyle_Protection::PROTECTION_PROTECTED ? 'true' : 'false'));
            }
            if ($pStyle->getProtection()->getHidden() != ExcelStyle_Protection::PROTECTION_INHERIT)
            {
                $xDoc->writeAttribute('hidden', ($pStyle->getProtection()->getHidden() == ExcelStyle_Protection::PROTECTION_PROTECTED ? 'true' : 'false'));
            }
            $xDoc->endElement();
        }
        
        $xDoc->endElement();
    }
    
    /**
     * Write BorderPr
     *
     * @param 	ExcelXmlWriter		$xDoc 		XML Writer
     * @param 	string							$pName			Element name
     * @param 	ExcelStyleBorder			$pBorder		Border style
     * @throws 	Exception
     */
    private function _writeBorderPr(ExcelXmlWriter $xDoc = null, $pName = 'left', ExcelStyleBorder $pBorder = null)
    {
        // Write BorderPr
        if ($pBorder->getBorderStyle() != ExcelStyleBorder::BORDER_NONE)
        {
            $xDoc->startElement($pName);
            $xDoc->writeAttribute('style', $pBorder->getBorderStyle());
            
            // color
            $xDoc->startElement('color');
            $xDoc->writeAttribute('rgb', $pBorder->getColor()->getARGB());
            $xDoc->endElement();
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Write NumberFormat
     *
     * @param 	ExcelXmlWriter			$xDoc 		XML Writer
     * @param 	ExcelStyleNumberFormat			$pNumberFormat	Number Format
     * @param 	int									$pId			Number Format identifier
     * @throws 	Exception
     */
    private function _writeNumFmt($xDoc = null, $pNumberFormat = null, $pId = 0)
    {
        // Translate formatcode
        $formatCode = $pNumberFormat->getFormatCode();
        
        // numFmt
        $xDoc->startElement('numFmt');
        $xDoc->writeAttribute('numFmtId', ($pId + 164));
        $xDoc->writeAttribute('formatCode', $formatCode);
        $xDoc->endElement();
    }
    
    /**
     * Get an array of all styles
     *
     * @param 	ExcelWorkbook				$workbook
     * @return 	ExcelStyle[]		All styles in PHPExcel
     * @throws 	Exception
     */
    public function allStyles($workbook = null)
    {
        // Get an array of all styles
        $aStyles = array();
        
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            foreach ($workbook->getSheet($i)->getStyles() as $style)
            {
                $aStyles[] = $style;
            }
        }
        
        return $aStyles;
    }
    
    /**
     * Get an array of all conditional styles
     *
     * @param 	ExcelWorkbook				$workbook
     * @return 	ExcelStyle[]		All styles in PHPExcel
     * @throws 	Exception
     */
    public function allConditionalStyles($workbook = null)
    {
        // Get an array of all styles
        $aStyles = array();
        
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            foreach ($workbook->getSheet($i)->getStyles() as $style)
            {
                if (count($style->getConditionalStyles()) > 0)
                {
                    foreach ($style->getConditionalStyles() as $conditional)
                    {
                        $aStyles[] = $conditional;
                    }
                }
            }
        }
        
        return $aStyles;
    }
    
    /**
     * Get an array of all fills
     *
     * @param 	ExcelWorkbook						$workbook
     * @return 	ExcelStyle_Fill[]		All fills in PHPExcel
     * @throws 	Exception
     */
    public function allFills($workbook = null)
    {
        // Get an array of unique fills
        $aFills  = array();
        $aStyles = $this->allStyles($workbook);
        
        foreach ($aStyles as $style)
        {
            if (!array_key_exists($style->getFill()->getHashCode(), $aFills))
            {
                $aFills[$style->getFill()->getHashCode()] = $style->getFill();
            }
        }
        
        return $aFills;
    }
    
    /**
     * Get an array of all fonts
     *
     * @param 	ExcelWorkbook						$workbook
     * @return 	ExcelStyleFont[]		All fonts in PHPExcel
     * @throws 	Exception
     */
    public function allFonts($workbook = null)
    {
        // Get an array of unique fonts
        $aFonts  = array();
        $aStyles = $this->allStyles($workbook);
        
        foreach ($aStyles as $style)
        {
            if (!array_key_exists($style->getFont()->getHashCode(), $aFonts))
            {
                $aFonts[$style->getFont()->getHashCode()] = $style->getFont();
            }
        }
        
        return $aFonts;
    }
    
    /**
     * Get an array of all borders
     *
     * @param 	ExcelWorkbook						$workbook
     * @return 	ExcelStyleBorders[]		All borders in PHPExcel
     * @throws 	Exception
     */
    public function allBorders($workbook = null)
    {
        // Get an array of unique borders
        $aBorders = array();
        $aStyles  = $this->allStyles($workbook);
        
        foreach ($aStyles as $style)
        {
            if (!array_key_exists($style->getBorders()->getHashCode(), $aBorders))
            {
                $aBorders[$style->getBorders()->getHashCode()] = $style->getBorders();
            }
        }
        
        return $aBorders;
    }
    
    /**
     * Get an array of all number formats
     *
     * @param 	ExcelWorkbook								$workbook
     * @return 	ExcelStyleNumberFormat[]		All number formats in PHPExcel
     * @throws 	Exception
     */
    public function allNumberFormats($workbook = null)
    {
        // Get an array of unique number formats
        $aNumFmts = array();
        $aStyles  = $this->allStyles($workbook);
        
        foreach ($aStyles as $style)
        {
            if (!array_key_exists($style->getNumberFormat()->getHashCode(), $aNumFmts))
            {
                $aNumFmts[$style->getNumberFormat()->getHashCode()] = $style->getNumberFormat();
            }
        }
        
        return $aNumFmts;
    }
}

/**
 * ExcelWriter_Excel2007_Drawing
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_Drawing extends ExcelWriterPart
{
    /**
     * Write drawings to XML format
     *
     * @param 	ExcelWorksheet				$pWorksheet
     * @return 	string 								XML Output
     * @throws 	Exception
     */
    public function writeDrawings($pWorksheet = null)
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
        
        // xdr:wsDr
        $xDoc->startElement('xdr:wsDr');
        $xDoc->writeAttribute('xmlns:xdr', 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing');
        $xDoc->writeAttribute('xmlns:a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        
        // Loop trough images and write drawings
        $i        = 1;
        $iterator = $pWorksheet->getDrawingCollection()->getIterator();
        while ($iterator->valid())
        {
            $this->_writeDrawing($xDoc, $iterator->current(), $i);
            
            $iterator->next();
            ++$i;
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write drawings to XML format
     *
     * @param 	ExcelXmlWriter			$xDoc 		XML Writer
     * @param 	ExcelWorksheet_BaseDrawing		$pDrawing
     * @param 	int									$pRelationId
     * @throws 	Exception
     */
    public function _writeDrawing($xDoc = null, $pDrawing = null, $pRelationId = -1)
    {
        if ($pRelationId >= 0)
        {
            // xdr:oneCellAnchor
            $xDoc->startElement('xdr:oneCellAnchor');
            // Image location
            $aCoordinates    = ExcelCell::coordinateFromString($pDrawing->getCoordinates());
            $aCoordinates[0] = ExcelCell::columnIndexFromString($aCoordinates[0]);
            
            // xdr:from
            $xDoc->startElement('xdr:from');
            $xDoc->writeElement('xdr:col', $aCoordinates[0] - 1);
            $xDoc->writeElement('xdr:colOff', ExcelShared_Drawing::pixelsToEMU($pDrawing->getOffsetX()));
            $xDoc->writeElement('xdr:row', $aCoordinates[1] - 1);
            $xDoc->writeElement('xdr:rowOff', ExcelShared_Drawing::pixelsToEMU($pDrawing->getOffsetY()));
            $xDoc->endElement();
            
            // xdr:ext
            $xDoc->startElement('xdr:ext');
            $xDoc->writeAttribute('cx', ExcelShared_Drawing::pixelsToEMU($pDrawing->getWidth()));
            $xDoc->writeAttribute('cy', ExcelShared_Drawing::pixelsToEMU($pDrawing->getHeight()));
            $xDoc->endElement();
            
            // xdr:pic
            $xDoc->startElement('xdr:pic');
            
            // xdr:nvPicPr
            $xDoc->startElement('xdr:nvPicPr');
            
            // xdr:cNvPr
            $xDoc->startElement('xdr:cNvPr');
            $xDoc->writeAttribute('id', $pRelationId);
            $xDoc->writeAttribute('name', $pDrawing->getName());
            $xDoc->writeAttribute('descr', $pDrawing->getDescription());
            $xDoc->endElement();
            
            // xdr:cNvPicPr
            $xDoc->startElement('xdr:cNvPicPr');
            
            // a:picLocks
            $xDoc->startElement('a:picLocks');
            $xDoc->writeAttribute('noChangeAspect', '1');
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // xdr:blipFill
            $xDoc->startElement('xdr:blipFill');
            
            // a:blip
            $xDoc->startElement('a:blip');
            $xDoc->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $xDoc->writeAttribute('r:embed', 'rId' . $pRelationId);
            $xDoc->endElement();
            
            // a:stretch
            $xDoc->startElement('a:stretch');
            $xDoc->writeElement('a:fillRect', null);
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // xdr:spPr
            $xDoc->startElement('xdr:spPr');
            
            // a:xfrm
            $xDoc->startElement('a:xfrm');
            $xDoc->writeAttribute('rot', ExcelShared_Drawing::degreesToAngle($pDrawing->getRotation()));
            $xDoc->endElement();
            
            // a:prstGeom
            $xDoc->startElement('a:prstGeom');
            $xDoc->writeAttribute('prst', 'rect');
            
            // a:avLst
            $xDoc->writeElement('a:avLst', null);
            
            $xDoc->endElement();
            
            // a:solidFill
            $xDoc->startElement('a:solidFill');
            
            // a:srgbClr
            $xDoc->startElement('a:srgbClr');
            $xDoc->writeAttribute('val', 'FFFFFF');
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            if ($pDrawing->getShadow()->getVisible())
            {
                // a:effectLst
                $xDoc->startElement('a:effectLst');
                
                // a:outerShdw
                $xDoc->startElement('a:outerShdw');
                $xDoc->writeAttribute('blurRad', ExcelShared_Drawing::pixelsToEMU($pDrawing->getShadow()->getBlurRadius()));
                $xDoc->writeAttribute('dist', ExcelShared_Drawing::pixelsToEMU($pDrawing->getShadow()->getDistance()));
                $xDoc->writeAttribute('dir', ExcelShared_Drawing::degreesToAngle($pDrawing->getShadow()->getDirection()));
                $xDoc->writeAttribute('algn', $pDrawing->getShadow()->getAlignment());
                $xDoc->writeAttribute('rotWithShape', '0');
                
                // a:srgbClr
                $xDoc->startElement('a:srgbClr');
                $xDoc->writeAttribute('val', $pDrawing->getShadow()->getColor()->getRGB());
                
                // a:alpha
                $xDoc->startElement('a:alpha');
                $xDoc->writeAttribute('val', $pDrawing->getShadow()->getAlpha() * 1000);
                $xDoc->endElement();
                
                $xDoc->endElement();
                
                $xDoc->endElement();
                
                $xDoc->endElement();
            }
            
            $xDoc->endElement();
            
            $xDoc->endElement();
            
            // xdr:clientData
            $xDoc->writeElement('xdr:clientData', null);
            
            $xDoc->endElement();
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
    
    /**
     * Write VML header/footer images to XML format
     *
     * @param 	ExcelWorksheet				$pWorksheet
     * @return 	string 								XML Output
     * @throws 	Exception
     */
    public function writeVMLHeaderFooterImages($pWorksheet = null)
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
        
        // Header/footer images
        $images = $pWorksheet->getHeaderFooter()->getImages();
        
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
        $xDoc->writeAttribute('id', '_x0000_t75');
        $xDoc->writeAttribute('coordsize', '21600,21600');
        $xDoc->writeAttribute('o:spt', '75');
        $xDoc->writeAttribute('o:preferrelative', 't');
        $xDoc->writeAttribute('path', 'm@4@5l@4@11@9@11@9@5xe');
        $xDoc->writeAttribute('filled', 'f');
        $xDoc->writeAttribute('stroked', 'f');
        
        // v:stroke
        $xDoc->startElement('v:stroke');
        $xDoc->writeAttribute('joinstyle', 'miter');
        $xDoc->endElement();
        
        // v:formulas
        $xDoc->startElement('v:formulas');
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'if lineDrawn pixelLineWidth 0');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'sum @0 1 0');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'sum 0 0 @1');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'prod @2 1 2');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'prod @3 21600 pixelWidth');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'prod @3 21600 pixelHeight');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'sum @0 0 1');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'prod @6 1 2');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'prod @7 21600 pixelWidth');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'sum @8 21600 0');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'prod @7 21600 pixelHeight');
        $xDoc->endElement();
        
        // v:f
        $xDoc->startElement('v:f');
        $xDoc->writeAttribute('eqn', 'sum @10 21600 0');
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // v:path
        $xDoc->startElement('v:path');
        $xDoc->writeAttribute('o:extrusionok', 'f');
        $xDoc->writeAttribute('gradientshapeok', 't');
        $xDoc->writeAttribute('o:connecttype', 'rect');
        $xDoc->endElement();
        
        // o:lock
        $xDoc->startElement('o:lock');
        $xDoc->writeAttribute('v:ext', 'edit');
        $xDoc->writeAttribute('aspectratio', 't');
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // Loop trough images
        foreach ($images as $key => $value)
        {
            $this->_writeVMLHeaderFooterImage($xDoc, $key, $value);
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write VML comment to XML format
     *
     * @param 	ExcelXmlWriter		$xDoc 			XML Writer
     * @param	string							$pReference			Reference
     * @param 	ExcelWorksheet_HeaderFooterDrawing	$pImage		Image
     * @throws 	Exception
     */
    public function _writeVMLHeaderFooterImage($xDoc = null, $pReference = '', $pImage = null)
    {
        // Calculate object id
        preg_match('{(\d+)}', md5($pReference), $m);
        $id = 1500 + (substr($m[1], 0, 2) * 1);
        
        // Calculate offset
        $width      = $pImage->getWidth();
        $height     = $pImage->getHeight();
        $marginLeft = $pImage->getOffsetX();
        $marginTop  = $pImage->getOffsetY();
        
        // v:shape
        $xDoc->startElement('v:shape');
        $xDoc->writeAttribute('id', $pReference);
        $xDoc->writeAttribute('o:spid', '_x0000_s' . $id);
        $xDoc->writeAttribute('type', '#_x0000_t75');
        $xDoc->writeAttribute('style', "position:absolute;margin-left:{$marginLeft}px;margin-top:{$marginTop}px;width:{$width}px;height:{$height}px;z-index:1");
        
        // v:imagedata
        $xDoc->startElement('v:imagedata');
        $xDoc->writeAttribute('o:relid', 'rId' . $pReference);
        $xDoc->writeAttribute('o:title', $pImage->getName());
        $xDoc->endElement();
        
        // o:lock
        $xDoc->startElement('o:lock');
        $xDoc->writeAttribute('v:ext', 'edit');
        $xDoc->writeAttribute('rotation', 't');
        $xDoc->endElement();
        
        $xDoc->endElement();
    }
    
    /**
     * Get an array of all drawings
     *
     * @param 	ExcelWorkbook							$workbook
     * @return 	ExcelWorksheet_Drawing[]		All drawings in PHPExcel
     * @throws 	Exception
     */
    public function allDrawings($workbook = null)
    {
        // Get an array of all drawings
        $aDrawings = array();
        
        // Loop trough PHPExcel
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            // Loop trough images and add to array
            $iterator = $workbook->getSheet($i)->getDrawingCollection()->getIterator();
            while ($iterator->valid())
            {
                $aDrawings[] = $iterator->current();
                
                $iterator->next();
            }
        }
        
        return $aDrawings;
    }
}
?>