<?php
require_once "webcore.excel.php";
require_once 'calculation/Functions.php';
require_once 'webcore.excel.utils.php';
require_once 'Writer/Excel2007/WriterPart.php';
require_once 'Writer/Excel2007/Theme.php';
require_once 'Writer/Excel2007/Style.php';
require_once 'Writer/Excel2007/Workbook.php';
require_once 'Writer/Excel2007/Worksheet.php';

/**
 * ExcelWriter_Excel2007
 *
 * @package    WebCore
 * @subpackage Excel
 */
class OXmlWriter extends ObjectBase implements IExcelWriter
{
    private $_preCalculateFormulas = true;
    private $_office2003compatibility = false;
    
    /**
     * Private writer parts
     *
     * @var ExcelWriterPart[]
     */
    private $_writerParts;
    
    /**
     * Private PHPExcel
     *
     * @var PHPExcel
     */
    private $_spreadSheet;
    
    /**
     * Private string table
     *
     * @var string[]
     */
    private $_stringTable;
    
    /**
     * Private unique ExcelStyle HashTable
     *
     * @var HashTable
     */
    private $_stylesHashTable;
    
    /**
     * Private unique ExcelStyle_Conditional HashTable
     *
     * @var HashTable
     */
    private $_stylesConditionalHashTable;
    
    /**
     * Private unique ExcelStyle_Fill HashTable
     *
     * @var HashTable
     */
    private $_fillHashTable;
    
    /**
     * Private unique ExcelStyleFont HashTable
     *
     * @var HashTable
     */
    private $_fontHashTable;
    
    /**
     * Private unique ExcelStyleBorders HashTable
     *
     * @var HashTable
     */
    private $_bordersHashTable;
    
    /**
     * Private unique ExcelStyleNumberFormat HashTable
     *
     * @var HashTable
     */
    private $_numFmtHashTable;
    
    /**
     * Private unique ExcelWorksheet_BaseDrawing HashTable
     *
     * @var HashTable
     */
    private $_drawingHashTable;
    
    /**
     * Use disk caching where possible?
     *
     * @var boolean
     */
    private $_useDiskCaching = false;
    
    /**
     * Disk caching directory
     *
     * @var string
     */
    private $_diskCachingDirectory;
    
    /**
     * Create a new ExcelWriter_Excel2007
     *
     * @param 	ExcelWorkbook	$workbook
     */
    public function __construct($workbook = null)
    {
        if (class_exists('ZipArchive') === false)
            throw new SystemException(SystemException::EX_CLASSNOTFOUND, 'You need ZIP module to use XLSX');
        
        // register the zip wrapper.
        ExcelShared_ZipStreamWrapper::register();
        
        // Assign PHPExcel
        $this->setPHPExcel($workbook);
        
        // Set up disk caching location
        $this->_diskCachingDirectory = './';
        
        // Initialise writer parts
        $this->_writerParts['stringtable']  = new ExcelWriterStringTable();
        $this->_writerParts['contenttypes'] = new ExcelWriter_Excel2007_ContentTypes();
        $this->_writerParts['docprops']     = new ExcelWriterDocProps();
        $this->_writerParts['rels']         = new ExcelWriter_Excel2007_Rels();
        $this->_writerParts['theme']        = new ExcelWriter_Excel2007_Theme();
        $this->_writerParts['style']        = new ExcelWriter_Excel2007_Style();
        $this->_writerParts['workbook']     = new ExcelWriter_Excel2007_Workbook();
        $this->_writerParts['worksheet']    = new ExcelWriter_Excel2007_Worksheet();
        $this->_writerParts['drawing']      = new ExcelWriter_Excel2007_Drawing();
        $this->_writerParts['comments']     = new ExcelWriter_Excel2007_Comments();
        
        // Assign parent IWriter
        foreach ($this->_writerParts as $writer)
        {
            $writer->setParentWriter($this);
        }
        
        // Set HashTable variables
        $this->_stringTable                = array();
        $this->_stylesHashTable            = new HashTable();
        $this->_stylesConditionalHashTable = new HashTable();
        $this->_fillHashTable              = new HashTable();
        $this->_fontHashTable              = new HashTable();
        $this->_bordersHashTable           = new HashTable();
        $this->_numFmtHashTable            = new HashTable();
        $this->_drawingHashTable           = new HashTable();
        
        // Other initializations
        $this->_serializePHPExcel = false;
    }
    
    /**
     * Get writer part
     *
     * @param 	string 	$pPartName		Writer part name
     * @return 	ExcelWriterPart
     */
    function getWriterPart($pPartName = '')
    {
        if ($pPartName != '' && isset($this->_writerParts[strtolower($pPartName)]))
        {
            return $this->_writerParts[strtolower($pPartName)];
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Worsk the same as Save PHPExcel to file, except it returns the file contents.
     *
     * @return  mixed
     * @throws	Exception
     */
    public function output()
    {
        $filename = tempnam(sys_get_temp_dir(), 'webcore.excel.oxmlwriter.');
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
        if (is_null($this->_spreadSheet))
            throw new Exception("PHPExcel object unassigned.");
        
        $originalFilename = $pFilename;
        if (strtolower($pFilename) == 'php://output' || strtolower($pFilename) == 'php://stdout')
        {
            $pFilename = @tempnam('./', 'phpxl');
            if ($pFilename == '')
                $pFilename = $originalFilename;
        }
        
        $saveDateReturnType = ExcelCalculation_Functions::getReturnDateType();
        ExcelCalculation_Functions::setReturnDateType(ExcelCalculation_Functions::RETURNDATE_EXCEL);
        
        // Create string lookup table
        $this->_stringTable = array();
        for ($i = 0; $i < $this->_spreadSheet->getSheetCount(); ++$i)
        {
            $this->_stringTable = $this->getWriterPart('StringTable')->createStringTable($this->_spreadSheet->getSheet($i), $this->_stringTable);
        }
        
        // Create styles dictionaries
        $this->_stylesHashTable->addFromSource($this->getWriterPart('Style')->allStyles($this->_spreadSheet));
        $this->_stylesConditionalHashTable->addFromSource($this->getWriterPart('Style')->allConditionalStyles($this->_spreadSheet));
        $this->_fillHashTable->addFromSource($this->getWriterPart('Style')->allFills($this->_spreadSheet));
        $this->_fontHashTable->addFromSource($this->getWriterPart('Style')->allFonts($this->_spreadSheet));
        $this->_bordersHashTable->addFromSource($this->getWriterPart('Style')->allBorders($this->_spreadSheet));
        $this->_numFmtHashTable->addFromSource($this->getWriterPart('Style')->allNumberFormats($this->_spreadSheet));
        
        // Create drawing dictionary
        $this->_drawingHashTable->addFromSource($this->getWriterPart('Drawing')->allDrawings($this->_spreadSheet));
        
        // Create new ZIP file and open it for writing
        $objZip = new ZipArchive();
        
        // Try opening the ZIP file
        if ($objZip->open($pFilename, ZIPARCHIVE::OVERWRITE) !== true)
        {
            if ($objZip->open($pFilename, ZIPARCHIVE::CREATE) !== true)
            {
                throw new Exception("Could not open " . $pFilename . " for writing.");
            }
        }
        
        // Add [Content_Types].xml to ZIP file
        $objZip->addFromString('[Content_Types].xml', $this->getWriterPart('ContentTypes')->writeContentTypes($this->_spreadSheet));
        
        // Add relationships to ZIP file
        $objZip->addFromString('_rels/.rels', $this->getWriterPart('Rels')->writeRelationships($this->_spreadSheet));
        $objZip->addFromString('xl/_rels/workbook.xml.rels', $this->getWriterPart('Rels')->writeWorkbookRelationships($this->_spreadSheet));
        
        // Add document properties to ZIP file
        $objZip->addFromString('docProps/app.xml', $this->getWriterPart('DocProps')->writeDocPropsApp($this->_spreadSheet));
        $objZip->addFromString('docProps/core.xml', $this->getWriterPart('DocProps')->writeDocPropsCore($this->_spreadSheet));
        
        // Add theme to ZIP file
        $objZip->addFromString('xl/theme/theme1.xml', $this->getWriterPart('Theme')->writeTheme($this->_spreadSheet));
        
        // Add string table to ZIP file
        $objZip->addFromString('xl/sharedStrings.xml', $this->getWriterPart('StringTable')->writeStringTable($this->_stringTable));
        
        // Add styles to ZIP file
        $objZip->addFromString('xl/styles.xml', $this->getWriterPart('Style')->writeStyles($this->_spreadSheet));
        
        // Add workbook to ZIP file
        $objZip->addFromString('xl/workbook.xml', $this->getWriterPart('Workbook')->writeWorkbook($this->_spreadSheet));
        
        // Add worksheets
        for ($i = 0; $i < $this->_spreadSheet->getSheetCount(); ++$i)
        {
            $objZip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->getWriterPart('Worksheet')->writeWorksheet($this->_spreadSheet->getSheet($i), $this->_stringTable));
        }
        
        // Add worksheet relationships (drawings, ...)
        for ($i = 0; $i < $this->_spreadSheet->getSheetCount(); ++$i)
        {
            // Add relationships
            $objZip->addFromString('xl/worksheets/_rels/sheet' . ($i + 1) . '.xml.rels', $this->getWriterPart('Rels')->writeWorksheetRelationships($this->_spreadSheet->getSheet($i), ($i + 1)));
            
            // Add drawing relationship parts
            if ($this->_spreadSheet->getSheet($i)->getDrawingCollection()->count() > 0)
            {
                // Drawing relationships
                $objZip->addFromString('xl/drawings/_rels/drawing' . ($i + 1) . '.xml.rels', $this->getWriterPart('Rels')->writeDrawingRelationships($this->_spreadSheet->getSheet($i)));
                
                // Drawings
                $objZip->addFromString('xl/drawings/drawing' . ($i + 1) . '.xml', $this->getWriterPart('Drawing')->writeDrawings($this->_spreadSheet->getSheet($i)));
            }
            
            // Add comment relationship parts
            if (count($this->_spreadSheet->getSheet($i)->getComments()) > 0)
            {
                // VML Comments
                $objZip->addFromString('xl/drawings/vmlDrawing' . ($i + 1) . '.vml', $this->getWriterPart('Comments')->writeVMLComments($this->_spreadSheet->getSheet($i)));
                
                // Comments
                $objZip->addFromString('xl/comments' . ($i + 1) . '.xml', $this->getWriterPart('Comments')->writeComments($this->_spreadSheet->getSheet($i)));
            }
            
            // Add header/footer relationship parts
            if (count($this->_spreadSheet->getSheet($i)->getHeaderFooter()->getImages()) > 0)
            {
                // VML Drawings
                $objZip->addFromString('xl/drawings/vmlDrawingHF' . ($i + 1) . '.vml', $this->getWriterPart('Drawing')->writeVMLHeaderFooterImages($this->_spreadSheet->getSheet($i)));
                
                // VML Drawing relationships
                $objZip->addFromString('xl/drawings/_rels/vmlDrawingHF' . ($i + 1) . '.vml.rels', $this->getWriterPart('Rels')->writeHeaderFooterDrawingRelationships($this->_spreadSheet->getSheet($i)));
                
                // Media
                foreach ($this->_spreadSheet->getSheet($i)->getHeaderFooter()->getImages() as $image)
                {
                    $objZip->addFromString('xl/media/' . $image->getIndexedFilename(), file_get_contents($image->getPath()));
                }
            }
        }
        
        // Add media
        for ($i = 0; $i < $this->getDrawingHashTable()->getCount(); ++$i)
        {
            if ($this->getDrawingHashTable()->getByIndex($i) instanceof ExcelWorksheet_Drawing)
            {
                $imageContents = null;
                $imagePath     = $this->getDrawingHashTable()->getByIndex($i)->getPath();
                
                if (strpos($imagePath, 'zip://') !== false)
                {
                    $imagePath         = substr($imagePath, 6);
                    $imagePathSplitted = explode('#', $imagePath);
                    
                    $imageZip = new ZipArchive();
                    $imageZip->open($imagePathSplitted[0]);
                    $imageContents = $imageZip->getFromName($imagePathSplitted[1]);
                    $imageZip->close();
                    unset($imageZip);
                }
                else
                {
                    $imageContents = file_get_contents($imagePath);
                }
                
                $objZip->addFromString('xl/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
            }
            else if ($this->getDrawingHashTable()->getByIndex($i) instanceof ExcelWorksheet_MemoryDrawing)
            {
                ob_start();
                call_user_func($this->getDrawingHashTable()->getByIndex($i)->getRenderingFunction(), $this->getDrawingHashTable()->getByIndex($i)->getImageResource());
                $imageContents = ob_get_contents();
                ob_end_clean();
                
                $objZip->addFromString('xl/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
            }
        }
        
        ExcelCalculation_Functions::setReturnDateType($saveDateReturnType);
        
        // Close file
        if ($objZip->close() === false)
            throw new Exception("Could not close zip file $pFilename.");
        
        // If a temporary file was used, copy it to the correct file stream
        if ($originalFilename != $pFilename)
        {
            if (copy($pFilename, $originalFilename) === false)
                throw new Exception("Could not copy temporary zip file $pFilename to $originalFilename.");
            
            @unlink($pFilename);
        }
    }
    
    /**
     * Get PHPExcel object
     *
     * @return PHPExcel
     * @throws Exception
     */
    public function getPHPExcel()
    {
        if (is_null($this->_spreadSheet))
            throw new Exception("No PHPExcel assigned.");
        
        return $this->_spreadSheet;
    }
    
    /**
     * Get PHPExcel object
     *
     * @param 	ExcelWorkbook 	$workbook	PHPExcel object
     */
    public function setPHPExcel($workbook = null)
    {
        $this->_spreadSheet = $workbook;
    }
    
    /**
     * Get string table
     *
     * @return string[]
     */
    public function getStringTable()
    {
        return $this->_stringTable;
    }
    
    /**
     * Get ExcelStyle HashTable
     *
     * @return HashTable
     */
    public function getStylesHashTable()
    {
        return $this->_stylesHashTable;
    }
    
    /**
     * Get ExcelStyle_Conditional HashTable
     *
     * @return HashTable
     */
    public function getStylesConditionalHashTable()
    {
        return $this->_stylesConditionalHashTable;
    }
    
    /**
     * Get ExcelStyle_Fill HashTable
     *
     * @return HashTable
     */
    public function getFillHashTable()
    {
        return $this->_fillHashTable;
    }
    
    /**
     * Get ExcelStyleFont HashTable
     *
     * @return HashTable
     */
    public function getFontHashTable()
    {
        return $this->_fontHashTable;
    }
    
    /**
     * Get ExcelStyleBorders HashTable
     *
     * @return HashTable
     */
    public function getBordersHashTable()
    {
        return $this->_bordersHashTable;
    }
    
    /**
     * Get ExcelStyleNumberFormat HashTable
     *
     * @return HashTable
     */
    public function getNumFmtHashTable()
    {
        return $this->_numFmtHashTable;
    }
    
    /**
     * Get ExcelWorksheet_BaseDrawing HashTable
     *
     * @return HashTable
     */
    public function getDrawingHashTable()
    {
        return $this->_drawingHashTable;
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
    
    /**
     * Get Office2003 compatibility
     *
     * @return boolean
     */
    public function getOffice2003Compatibility()
    {
        return $this->_office2003compatibility;
    }
    
    /**
     * Set Pre-Calculate Formulas
     *
     * @param boolean $pValue	Office2003 compatibility?
     */
    public function setOffice2003Compatibility($pValue = false)
    {
        $this->_office2003compatibility = $pValue;
    }
    
    /**
     * Get use disk caching where possible?
     *
     * @return boolean
     */
    public function getUseDiskCaching()
    {
        return $this->_useDiskCaching;
    }
    
    /**
     * Set use disk caching where possible?
     *
     * @param 	boolean 	$pValue
     * @param	string		$pDirectory		Disk caching directory
     * @throws	Exception	Exception when directory does not exist
     */
    public function setUseDiskCaching($pValue = false, $pDirectory = null)
    {
        $this->_useDiskCaching = $pValue;
        
        if (!is_null($pDirectory))
        {
            if (is_dir($pDirectory))
            {
                $this->_diskCachingDirectory = $pDirectory;
            }
            else
            {
                throw new Exception("Directory does not exist: $pDirectory");
            }
        }
    }
    
    /**
     * Get disk caching directory
     *
     * @return string
     */
    public function getDiskCachingDirectory()
    {
        return $this->_diskCachingDirectory;
    }
}

/**
 * ExcelWriter_Excel2007_ContentTypes
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel2007_ContentTypes extends ExcelWriterPart
{
    /**
     * Write content types to XML format
     *
     * @param 	ExcelWorkbook $workbook
     * @return 	string 						XML Output
     * @throws 	Exception
     */
    public function writeContentTypes($workbook = null)
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
        
        // Types
        $xDoc->startElement('Types');
        $xDoc->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');
        
        // Theme
        $this->_writeOverrideContentType($xDoc, '/xl/theme/theme1.xml', 'application/vnd.openxmlformats-officedocument.theme+xml');
        
        // Styles
        $this->_writeOverrideContentType($xDoc, '/xl/styles.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml');
        
        // Rels
        $this->_writeDefaultContentType($xDoc, 'rels', 'application/vnd.openxmlformats-package.relationships+xml');
        
        // XML
        $this->_writeDefaultContentType($xDoc, 'xml', 'application/xml');
        
        // VML
        $this->_writeDefaultContentType($xDoc, 'vml', 'application/vnd.openxmlformats-officedocument.vmlDrawing');
        
        // Workbook
        $this->_writeOverrideContentType($xDoc, '/xl/workbook.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml');
        
        // DocProps
        $this->_writeOverrideContentType($xDoc, '/docProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml');
        $this->_writeOverrideContentType($xDoc, '/docProps/core.xml', 'application/vnd.openxmlformats-package.core-properties+xml');
        
        // Worksheets
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            $this->_writeOverrideContentType($xDoc, '/xl/worksheets/sheet' . ($i + 1) . '.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml');
        }
        
        // Shared strings
        $this->_writeOverrideContentType($xDoc, '/xl/sharedStrings.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml');
        
        // Add worksheet relationship content types
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            if ($workbook->getSheet($i)->getDrawingCollection()->count() > 0)
            {
                $this->_writeOverrideContentType($xDoc, '/xl/drawings/drawing' . ($i + 1) . '.xml', 'application/vnd.openxmlformats-officedocument.drawing+xml');
            }
        }
        
        // Comments
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            if (count($workbook->getSheet($i)->getComments()) > 0)
            {
                $this->_writeOverrideContentType($xDoc, '/xl/comments' . ($i + 1) . '.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.comments+xml');
            }
        }
        
        // Add media content-types
        $aMediaContentTypes = array();
        $mediaCount         = $this->getParentWriter()->getDrawingHashTable()->getCount();
        for ($i = 0; $i < $mediaCount; ++$i)
        {
            $extension = '';
            $mimeType  = '';
            
            if ($this->getParentWriter()->getDrawingHashTable()->getByIndex($i) instanceof ExcelWorksheet_Drawing)
            {
                $extension = strtolower($this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getExtension());
                $mimeType  = $this->_getImageMimeType($this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getPath());
            }
            else if ($this->getParentWriter()->getDrawingHashTable()->getByIndex($i) instanceof ExcelWorksheet_MemoryDrawing)
            {
                $extension = strtolower($this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getMimeType());
                $extension = explode('/', $extension);
                $extension = $extension[1];
                
                $mimeType = $this->getParentWriter()->getDrawingHashTable()->getByIndex($i)->getMimeType();
            }
            
            if (!isset($aMediaContentTypes[$extension]))
            {
                $aMediaContentTypes[$extension] = $mimeType;
                
                $this->_writeDefaultContentType($xDoc, $extension, $mimeType);
            }
        }
        
        $sheetCount = $workbook->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i)
        {
            if (count($workbook->getSheet()->getHeaderFooter()->getImages()) > 0)
            {
                foreach ($workbook->getSheet()->getHeaderFooter()->getImages() as $image)
                {
                    if (!isset($aMediaContentTypes[strtolower($image->getExtension())]))
                    {
                        $aMediaContentTypes[strtolower($image->getExtension())] = $this->_getImageMimeType($image->getPath());
                        
                        $this->_writeDefaultContentType($xDoc, strtolower($image->getExtension()), $aMediaContentTypes[strtolower($image->getExtension())]);
                    }
                }
            }
        }
        
        $xDoc->endElement();
        
        // Return
        return $xDoc->getData();
    }
    
    /**
     * Get image mime type
     *
     * @param 	string	$pFile	Filename
     * @return 	string	Mime Type
     * @throws 	Exception
     */
    private function _getImageMimeType($pFile = '')
    {
        if (ExcelShared_File::file_exists($pFile) == false)
            throw new Exception("File $pFile does not exist");
        
        $image = getimagesize($pFile);
        return image_type_to_mime_type($image[2]);
    }
    
    /**
     * Write Default content type
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	string 						$pPartname 		Part name
     * @param 	string 						$pContentType 	Content type
     * @throws 	Exception
     */
    private function _writeDefaultContentType(ExcelXmlWriter $xDoc = null, $pPartname = '', $pContentType = '')
    {
        if ($pPartname != '' && $pContentType != '')
        {
            // Write content type
            $xDoc->startElement('Default');
            $xDoc->writeAttribute('Extension', $pPartname);
            $xDoc->writeAttribute('ContentType', $pContentType);
            $xDoc->endElement();
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
    
    /**
     * Write Override content type
     *
     * @param 	ExcelXmlWriter 	$xDoc 		XML Writer
     * @param 	string 						$pPartname 		Part name
     * @param 	string 						$pContentType 	Content type
     * @throws 	Exception
     */
    private function _writeOverrideContentType(ExcelXmlWriter $xDoc = null, $pPartname = '', $pContentType = '')
    {
        if ($pPartname != '' && $pContentType != '')
        {
            // Write content type
            $xDoc->startElement('Override');
            $xDoc->writeAttribute('PartName', $pPartname);
            $xDoc->writeAttribute('ContentType', $pContentType);
            $xDoc->endElement();
        }
        else
        {
            throw new Exception("Invalid parameters passed.");
        }
    }
}
?>