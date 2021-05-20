<?php
/**
 * ExcelWriterPart
 *
 * @package    WebCore
 * @subpackage Excel
 */
abstract class ExcelWriterPart extends ObjectBase
{
    /**
     * Parent IWriter object
     *
     * @var IExcelWriter
     */
    private $_parentWriter;
    
    /**
     * Set parent IWriter object
     *
     * @param IExcelWriter	$pWriter
     * @throws Exception
     */
    public function setParentWriter($pWriter = null)
    {
        $this->_parentWriter = $pWriter;
    }
    
    /**
     * Get parent IWriter object
     *
     * @return ExcelWriter_IWriter
     * @throws Exception
     */
    public function getParentWriter()
    {
        if (!is_null($this->_parentWriter))
        {
            return $this->_parentWriter;
        }
        else
        {
            throw new Exception("No parent ExcelWriter_IWriter assigned.");
        }
    }
}

/**
 * ExcelWriterStringTable
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriterStringTable extends ExcelWriterPart
{
    /**
     * Create worksheet stringtable
     *
     * @param 	ExcelWorksheet 	$pSheet				Worksheet
     * @param 	string[] 				$pExistingTable 	Existing table to eventually merge with
     * @return 	string[] 				String table for worksheet
     * @throws 	Exception
     */
    public function createStringTable($pSheet = null, $pExistingTable = null)
    {
        if (is_null($pSheet))
            throw new Exception("Invalid ExcelWorksheet object passed.");
        
        // Create string lookup table
        $aStringTable        = array();
        $cellCollection      = null;
        $aFlippedStringTable = null; // For faster lookup
        
        // Is an existing table given?
        if (!is_null($pExistingTable) && is_array($pExistingTable))
        {
            $aStringTable = $pExistingTable;
        }
        
        // Fill index array
        $aFlippedStringTable = $this->flipStringTable($aStringTable);
        
        // Loop trough cells
        $cellCollection = $pSheet->getCellCollection();
        foreach ($cellCollection as $cell)
        {
            if (!is_object($cell->getValue()) && !isset($aFlippedStringTable[$cell->getValue()]) && !is_null($cell->getValue()) && $cell->getValue() !== '' && ($cell->getDataType() == ExcelCell_DataType::TYPE_STRING || $cell->getDataType() == ExcelCell_DataType::TYPE_NULL))
            {
                $aStringTable[]                         = $cell->getValue();
                $aFlippedStringTable[$cell->getValue()] = 1;
                
            }
            else if ($cell->getValue() instanceof ExcelRichText && !isset($aFlippedStringTable[$cell->getValue()->getHashCode()]) && !is_null($cell->getValue()))
            {
                $aStringTable[]                                        = $cell->getValue();
                $aFlippedStringTable[$cell->getValue()->getHashCode()] = 1;
            }
        }
        
        // Return
        return $aStringTable;
    }
    
    /**
     * Write string table to XML format
     *
     * @param 	string[] 	$pStringTable
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeStringTable($pStringTable = null)
    {
        if (is_null($pStringTable))
            throw new Exception("Invalid string table array passed.");
        
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
        
        // String table
        $xDoc->startElement('sst');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $xDoc->writeAttribute('uniqueCount', count($pStringTable));
        
        // Loop trough string table
        foreach ($pStringTable as $textElement)
        {
            $xDoc->startElement('si');
            
            if (!$textElement instanceof ExcelRichText)
            {
                $xDoc->writeElement('t', ExcelShared_String::ControlCharacterPHP2OOXML($textElement));
            }
            else if ($textElement instanceof ExcelRichText)
            {
                $this->writeRichText($xDoc, $textElement);
            }
            
            $xDoc->endElement();
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write Rich Text
     *
     * @param 	ExcelXmlWriter		$xDoc 		XML Writer
     * @param 	ExcelRichText				$pRichText		Rich text
     * @throws 	Exception
     */
    public function writeRichText(ExcelXmlWriter $xDoc = null, ExcelRichText $pRichText = null)
    {
        // Loop trough rich text elements
        $elements = $pRichText->getRichTextElements();
        foreach ($elements as $element)
        {
            // r
            $xDoc->startElement('r');
            
            // rPr
            if ($element instanceof ExcelRichText_Run)
            {
                // rPr
                $xDoc->startElement('rPr');
                
                // rFont
                $xDoc->startElement('rFont');
                $xDoc->writeAttribute('val', $element->getFont()->getName());
                $xDoc->endElement();
                
                // Bold
                $xDoc->startElement('b');
                $xDoc->writeAttribute('val', ($element->getFont()->getBold() ? 'true' : 'false'));
                $xDoc->endElement();
                
                // Italic
                $xDoc->startElement('i');
                $xDoc->writeAttribute('val', ($element->getFont()->getItalic() ? 'true' : 'false'));
                $xDoc->endElement();
                
                // Superscript / subscript
                if ($element->getFont()->getSuperScript() || $element->getFont()->getSubScript())
                {
                    $xDoc->startElement('vertAlign');
                    if ($element->getFont()->getSuperScript())
                    {
                        $xDoc->writeAttribute('val', 'superscript');
                    }
                    else if ($element->getFont()->getSubScript())
                    {
                        $xDoc->writeAttribute('val', 'subscript');
                    }
                    $xDoc->endElement();
                }
                
                // Striketrough
                $xDoc->startElement('strike');
                $xDoc->writeAttribute('val', ($element->getFont()->getStriketrough() ? 'true' : 'false'));
                $xDoc->endElement();
                
                // Color
                $xDoc->startElement('color');
                $xDoc->writeAttribute('rgb', $element->getFont()->getColor()->getARGB());
                $xDoc->endElement();
                
                // Size
                $xDoc->startElement('sz');
                $xDoc->writeAttribute('val', $element->getFont()->getSize());
                $xDoc->endElement();
                
                // Underline
                $xDoc->startElement('u');
                $xDoc->writeAttribute('val', $element->getFont()->getUnderline());
                $xDoc->endElement();
                
                $xDoc->endElement();
            }
            
            // t
            $xDoc->startElement('t');
            $xDoc->writeAttribute('xml:space', 'preserve');
            $xDoc->writeRaw(ExcelShared_String::ControlCharacterPHP2OOXML(htmlspecialchars($element->getText())));
            $xDoc->endElement();
            
            $xDoc->endElement();
        }
    }
    
    /**
     * Flip string table (for index searching)
     *
     * @param 	array	$stringTable	Stringtable
     * @return 	array
     */
    public function flipStringTable($stringTable = array())
    {
        // Return value
        $returnValue = array();
        
        // Loop trough stringtable and add flipped items to $returnValue
        foreach ($stringTable as $key => $value)
        {
            if (!$value instanceof ExcelRichText)
            {
                $returnValue[$value] = $key;
            }
            else if ($value instanceof ExcelRichText)
            {
                $returnValue[$value->getHashCode()] = $key;
            }
        }
        
        // Return
        return $returnValue;
    }
}

/**
 * ExcelWriterDocProps
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriterDocProps extends ExcelWriterPart
{
    /**
     * Write docProps/app.xml to XML format
     *
     * @param 	ExcelWorkbook	$workbook
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeDocPropsApp($workbook = null)
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
        
        // Properties
        $xDoc->startElement('Properties');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/officeDocument/2006/extended-properties');
        $xDoc->writeAttribute('xmlns:vt', 'http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes');
        
        // Application
        $xDoc->writeElement('Application', 'Microsoft Excel');
        
        // DocSecurity
        $xDoc->writeElement('DocSecurity', '0');
        
        // ScaleCrop
        $xDoc->writeElement('ScaleCrop', 'false');
        
        // HeadingPairs
        $xDoc->startElement('HeadingPairs');
        
        // Vector
        $xDoc->startElement('vt:vector');
        $xDoc->writeAttribute('size', '2');
        $xDoc->writeAttribute('baseType', 'variant');
        
        
        // Variant
        $xDoc->startElement('vt:variant');
        $xDoc->writeElement('vt:lpstr', 'Worksheets');
        $xDoc->endElement();
        
        // Variant
        $xDoc->startElement('vt:variant');
        $xDoc->writeElement('vt:i4', $workbook->getSheetCount());
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // TitlesOfParts
        $xDoc->startElement('TitlesOfParts');
        
        // Vector
        $xDoc->startElement('vt:vector');
        $xDoc->writeAttribute('size', $workbook->getSheetCount());
        $xDoc->writeAttribute('baseType', 'lpstr');
        
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            $xDoc->writeElement('vt:lpstr', $workbook->getSheet($i)->getTitle());
        }
        
        $xDoc->endElement();
        
        $xDoc->endElement();
        
        // Company
        $xDoc->writeElement('Company', 'Microsoft Corporation');
        
        // LinksUpToDate
        $xDoc->writeElement('LinksUpToDate', 'false');
        
        // SharedDoc
        $xDoc->writeElement('SharedDoc', 'false');
        
        // HyperlinksChanged
        $xDoc->writeElement('HyperlinksChanged', 'false');
        
        // AppVersion
        $xDoc->writeElement('AppVersion', '12.0000');
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write docProps/core.xml to XML format
     *
     * @param 	ExcelWorkbook	$workbook
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeDocPropsCore($workbook = null)
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
        
        // cp:coreProperties
        $xDoc->startElement('cp:coreProperties');
        $xDoc->writeAttribute('xmlns:cp', 'http://schemas.openxmlformats.org/package/2006/metadata/core-properties');
        $xDoc->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $xDoc->writeAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
        $xDoc->writeAttribute('xmlns:dcmitype', 'http://purl.org/dc/dcmitype/');
        $xDoc->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        
        // dc:creator
        $xDoc->writeElement('dc:creator', $workbook->getProperties()->getCreator());
        
        // cp:lastModifiedBy
        $xDoc->writeElement('cp:lastModifiedBy', $workbook->getProperties()->getLastModifiedBy());
        
        // dcterms:created
        $xDoc->startElement('dcterms:created');
        $xDoc->writeAttribute('xsi:type', 'dcterms:W3CDTF');
        $xDoc->writeRaw(date(DATE_W3C, $workbook->getProperties()->getCreated()));
        $xDoc->endElement();
        
        // dcterms:modified
        $xDoc->startElement('dcterms:modified');
        $xDoc->writeAttribute('xsi:type', 'dcterms:W3CDTF');
        $xDoc->writeRaw(date(DATE_W3C, $workbook->getProperties()->getModified()));
        $xDoc->endElement();
        
        // dc:title
        $xDoc->writeElement('dc:title', $workbook->getProperties()->getTitle());
        
        // dc:description
        $xDoc->writeElement('dc:description', $workbook->getProperties()->getDescription());
        
        // dc:subject
        $xDoc->writeElement('dc:subject', $workbook->getProperties()->getSubject());
        
        // cp:keywords
        $xDoc->writeElement('cp:keywords', $workbook->getProperties()->getKeywords());
        
        // cp:category
        $xDoc->writeElement('cp:category', $workbook->getProperties()->getCategory());
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
}

/**
 * ExcelWriter_Excel2007_Rels
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_Rels extends ExcelWriterPart
{
    /**
     * Write relationships to XML format
     *
     * @param 	ExcelWorkbook	$workbook
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeRelationships($workbook = null)
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
        
        // Relationships
        $xDoc->startElement('Relationships');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        
        // Relationship docProps/app.xml
        $this->_writeRelationship($xDoc, 3, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties', 'docProps/app.xml');
        
        // Relationship docProps/core.xml
        $this->_writeRelationship($xDoc, 2, 'http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties', 'docProps/core.xml');
        
        // Relationship xl/workbook.xml
        $this->_writeRelationship($xDoc, 1, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument', 'xl/workbook.xml');
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write workbook relationships to XML format
     *
     * @param 	ExcelWorkbook	$workbook
     * @return 	string 		XML Output
     * @throws 	Exception
     */
    public function writeWorkbookRelationships($workbook = null)
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
        
        // Relationships
        $xDoc->startElement('Relationships');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        
        // Relationship styles.xml
        $this->_writeRelationship($xDoc, 1, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles', 'styles.xml');
        
        // Relationship theme/theme1.xml
        $this->_writeRelationship($xDoc, 2, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme', 'theme/theme1.xml');
        
        // Relationship sharedStrings.xml
        $this->_writeRelationship($xDoc, 3, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings', 'sharedStrings.xml');
        
        // Relationships with sheets
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            $this->_writeRelationship($xDoc, ($i + 1 + 3), 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet', 'worksheets/sheet' . ($i + 1) . '.xml');
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write worksheet relationships to XML format
     *
     * Numbering is as follows:
     * 	rId1 				- Drawings
     *  rId_hyperlink_x 	- Hyperlinks
     *
     * @param 	ExcelWorksheet		$pWorksheet
     * @param 	int						$pWorksheetId
     * @return 	string 					XML Output
     * @throws 	Exception
     */
    public function writeWorksheetRelationships(ExcelWorksheet $pWorksheet = null, $pWorksheetId = 1)
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
        
        // Relationships
        $xDoc->startElement('Relationships');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        
        // Write drawing relationships?
        if ($pWorksheet->getDrawingCollection()->count() > 0)
        {
            $this->_writeRelationship($xDoc, 1, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing', '../drawings/drawing' . $pWorksheetId . '.xml');
        }
        
        // Write hyperlink relationships?
        $i = 1;
        foreach ($pWorksheet->getCellCollection() as $cell)
        {
            if ($cell->hasHyperlink() && !$cell->getHyperlink()->isInternal())
            {
                $this->_writeRelationship($xDoc, '_hyperlink_' . $i, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink', $cell->getHyperlink()->getUrl(), 'External');
                
                ++$i;
            }
        }
        
        // Write comments relationship?
        $i = 1;
        if (count($pWorksheet->getComments()) > 0)
        {
            $this->_writeRelationship($xDoc, '_comments_vml' . $i, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/vmlDrawing', '../drawings/vmlDrawing' . $pWorksheetId . '.vml');
            
            $this->_writeRelationship($xDoc, '_comments' . $i, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/comments', '../comments' . $pWorksheetId . '.xml');
        }
        
        // Write header/footer relationship?
        $i = 1;
        if (count($pWorksheet->getHeaderFooter()->getImages()) > 0)
        {
            $this->_writeRelationship($xDoc, '_headerfooter_vml' . $i, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/vmlDrawing', '../drawings/vmlDrawingHF' . $pWorksheetId . '.vml');
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write drawing relationships to XML format
     *
     * @param 	ExcelWorksheet			$pWorksheet
     * @return 	string 						XML Output
     * @throws 	Exception
     */
    public function writeDrawingRelationships(ExcelWorksheet $pWorksheet = null)
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
        
        // Relationships
        $xDoc->startElement('Relationships');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        
        // Loop trough images and write relationships
        $i        = 1;
        $iterator = $pWorksheet->getDrawingCollection()->getIterator();
        while ($iterator->valid())
        {
            if ($iterator->current() instanceof ExcelWorksheet_Drawing || $iterator->current() instanceof ExcelWorksheet_MemoryDrawing)
            {
                // Write relationship for image drawing
                $this->_writeRelationship($xDoc, $i, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image', '../media/' . str_replace(' ', '', $iterator->current()->getIndexedFilename()));
            }
            
            $iterator->next();
            ++$i;
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write header/footer drawing relationships to XML format
     *
     * @param 	ExcelWorksheet			$pWorksheet
     * @return 	string 						XML Output
     * @throws 	Exception
     */
    public function writeHeaderFooterDrawingRelationships(ExcelWorksheet $pWorksheet = null)
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
        
        // Relationships
        $xDoc->startElement('Relationships');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        
        // Loop trough images and write relationships
        foreach ($pWorksheet->getHeaderFooter()->getImages() as $key => $value)
        {
            // Write relationship for image drawing
            $this->_writeRelationship($xDoc, $key, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image', '../media/' . $value->getIndexedFilename());
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Write Override content type
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	int							$pId			Relationship ID. rId will be prepended!
     * @param 	string						$pType			Relationship type
     * @param 	string 						$pTarget		Relationship target
     * @param 	string 						$pTargetMode	Relationship target mode
     * @throws 	Exception
     */
    private function _writeRelationship($xDoc = null, $pId = 1, $pType = '', $pTarget = '', $pTargetMode = '')
    {
        if ($pType != '' && $pTarget != '')
        {
            // Write relationship
            $xDoc->startElement('Relationship');
            $xDoc->writeAttribute('Id', 'rId' . $pId);
            $xDoc->writeAttribute('Type', $pType);
            $xDoc->writeAttribute('Target', $pTarget);
            
            if ($pTargetMode != '')
            {
                $xDoc->writeAttribute('TargetMode', $pTargetMode);
            }
            
            $xDoc->endElement();
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
}
?>