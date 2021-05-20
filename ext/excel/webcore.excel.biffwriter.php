<?php
require_once "webcore.excel.php";
require_once 'webcore.excel.utils.php';
require_once 'calculation/Functions.php';
require_once 'Writer/Excel5/Workbook.php';
require_once 'webcore.excel.ole.php';

/**
 * BIFF Writer
 *
 * @package    WebCore
 * @subpackage Excel
 */
class BiffWriter extends ObjectBase implements IExcelWriter
{
    /**
	 * PHPExcel object
	 *
	 * @var PHPExcel
	 */
	private $_phpExcel;

	/**
	 * The BIFF version of the written Excel file, BIFF5 = 0x0500, BIFF8 = 0x0600
	 *
	 * @var integer
	 */
	private $_BIFF_version;

	/**
	 * Temporary storage directory
	 *
	 * @var string
	 */
	private $_tempDir = '';

	/**
	 * Total number of shared strings in workbook
	 *
	 * @var int
	 */
	private $_str_total;

	/**
	 * Number of unique shared strings in workbook
	 *
	 * @var int
	 */
	private $_str_unique;

	/**
	 * Array of unique shared strings in workbook
	 *
	 * @var array
	 */
	private $_str_table;

	/**
	 * Color cache. Mapping between RGB value and color index.
	 *
	 * @var array
	 */
	private $_colors;

	/**
	 * Formula parser
	 *
	 * @var ExcelWriter_Excel5_Parser
	 */
	private $_parser;

	/**
	 * Create a new ExcelWriter_Excel5
	 *
	 * @param	PHPExcel	$workbook	PHPExcel object
	 */
	public function __construct($workbook)
	{
		$this->_phpExcel		= $workbook;
		$this->_BIFF_version	= 0x0600;
		$this->_tempDir			= sys_get_temp_dir();
		
		$this->_str_total       = 0;
		$this->_str_unique      = 0;
		$this->_str_table       = array();
		$this->_colors          = array();
		$this->_parser          = new ExcelWriter_Excel5_Parser(0, $this->_BIFF_version);
	}
	
	public function output()
    {
        $filename = tempnam(sys_get_temp_dir(), 'webcore.excel.biffwirter.');
        $this->save($filename);
        return file_get_contents($filename);
    }

	/**
	 * Save PHPExcel to file
	 *
	 * @param	string		$pFileName
	 * @throws	Exception
	 */
	public function save($pFilename = null)
	{
		if (ini_get('mbstring.func_overload') != 0) {
			throw new Exception('Multibyte string function overloading in PHP must be disabled.');
		}

		$this->_phpExcel->garbageCollect();
		
		$saveDateReturnType = ExcelCalculation_Functions::getReturnDateType();
		ExcelCalculation_Functions::setReturnDateType(ExcelCalculation_Functions::RETURNDATE_EXCEL);

		// Initialise workbook writer
		$this->_writerWorkbook = new ExcelWriter_Excel5_Workbook($this->_phpExcel, $this->_BIFF_version,
					$this->_str_total, $this->_str_unique, $this->_str_table, $this->_colors, $this->_parser, $this->_tempDir);

		// Initialise worksheet writers
		$countSheets = count($this->_phpExcel->getAllSheets());
		for ($i = 0; $i < $countSheets; ++$i) {
			$phpSheet  = $this->_phpExcel->getSheet($i);
			
			$writerWorksheet = new ExcelWriter_Excel5_Worksheet($this->_BIFF_version,
									   $this->_str_total, $this->_str_unique,
									   $this->_str_table, $this->_colors,
									   $this->_parser, $this->_tempDir,
									   $phpSheet);
			$this->_writerWorksheets[$i] = $writerWorksheet;
		}

		// add 15 identical cell style Xfs
		// for now, we use the first cellXf instead of cellStyleXf
		$cellXfCollection = $this->_phpExcel->getCellXfCollection();
		for ($i = 0; $i < 15; ++$i) {
			$this->_writerWorkbook->addXfWriter($cellXfCollection[0], true);
		}

		// add all the cell Xfs
		foreach ($this->_phpExcel->getCellXfCollection() as $style) {
			$this->_writerWorkbook->addXfWriter($style, false);
		}

		// initialize OLE file
		$workbookStreamName = ($this->_BIFF_version == 0x0600) ? 'Workbook' : 'Book';
		$OLE = new ExcelShared_OLE_PPS_File(ExcelShared_OLE::Asc2Ucs($workbookStreamName));

		if ($this->_tempDir != '') {
			$OLE->setTempDir($this->_tempDir);
		}
		$res = $OLE->init();

		// Write the worksheet streams before the global workbook stream,
		// because the byte sizes of these are needed in the global workbook stream
		$worksheetSizes = array();
		for ($i = 0; $i < $countSheets; ++$i) {
			$this->_writerWorksheets[$i]->close();
			$worksheetSizes[] = $this->_writerWorksheets[$i]->_datasize;
		}

		// add binary data for global workbook stream
		$OLE->append( $this->_writerWorkbook->writeWorkbook($worksheetSizes) );

		// add binary data for sheet streams
		for ($i = 0; $i < $countSheets; ++$i) {
			while ( ($tmp = $this->_writerWorksheets[$i]->getData()) !== false ) {
				$OLE->append($tmp);
			}
		}

		$root = new ExcelShared_OLE_PPS_Root(time(), time(), array($OLE));
		if ($this->_tempDir != '') {
			$root->setTempDir($this->_tempDir);
		}

		// save the OLE file
		$res = $root->save($pFilename);
		ExcelCalculation_Functions::setReturnDateType($saveDateReturnType);

		// clean up
		foreach ($this->_writerWorksheets as $sheet) {
			$sheet->cleanup();
		}
	}

	/**
	 * Get temporary storage directory
	 *
	 * @return string
	 */
	public function getTempDir() {
		return $this->_tempDir;
	}

	/**
	 * Set temporary storage directory
	 *
	 * @param	string	$pValue		Temporary storage directory
	 * @throws	Exception	Exception when directory does not exist
	 * @return ExcelWriter_Excel5
	 */
	public function setTempDir($pValue = '') {
		if (is_dir($pValue)) {
			$this->_tempDir = $pValue;
		} else {
			throw new Exception("Directory does not exist: $pValue");
		}
		return $this;
	}
}

/**
 * Class for writing Excel BIFF records.
 *
 * From "MICROSOFT EXCEL BINARY FILE FORMAT" by Mark O'Brien (Microsoft Corporation):
 *
 * BIFF (BInary File Format) is the file format in which Excel documents are
 * saved on disk.  A BIFF file is a complete description of an Excel document.
 * BIFF files consist of sequences of variable-length records. There are many
 * different types of BIFF records.  For example, one record type describes a
 * formula entered into a cell; one describes the size and location of a
 * window into a document; another describes a picture format.
 *
 * @author   Xavier Noguer <xnoguer@php.net>
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel5_BIFFwriter extends ObjectBase
{
    /**
	 * The BIFF/Excel version (5).
	 * @var integer
	 */
	var $_BIFF_version = 0x0500;

	/**
	 * The byte order of this architecture. 0 => little endian, 1 => big endian
	 * @var integer
	 */
	private static $_byte_order;

	/**
	 * The string containing the data of the BIFF stream
	 * @var string
	 */
	var $_data;

	/**
	 * The size of the data in bytes. Should be the same as strlen($this->_data)
	 * @var integer
	 */
	var $_datasize;

	/**
	 * The maximum length for a BIFF record (excluding record header and length field). See _addContinue()
	 * @var integer
	 * @see _addContinue()
	 */
	var $_limit;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_data       = '';
		$this->_datasize   = 0;
		$this->_limit      = 2080;
	}

	/**
	 * Determine the byte order and store it as class data to avoid
	 * recalculating it for each call to new().
	 *
	 * @return int
	 */
	public static function getByteOrder()
	{
		if (!isset(self::$_byte_order)) {
			// Check if "pack" gives the required IEEE 64bit float
			$teststr = pack("d", 1.2345);
			$number  = pack("C8", 0x8D, 0x97, 0x6E, 0x12, 0x83, 0xC0, 0xF3, 0x3F);
			if ($number == $teststr) {
				$byte_order = 0;    // Little Endian
			} elseif ($number == strrev($teststr)){
				$byte_order = 1;    // Big Endian
			} else {
				// Give up. I'll fix this in a later version.
				throw new Exception("Required floating point format ".
										 "not supported on this platform.");
			}
			self::$_byte_order = $byte_order;
		}

		return self::$_byte_order;
	}

	/**
	 * General storage function
	 *
	 * @param string $data binary data to append
	 * @access private
	 */
	function _append($data)
	{
		if (strlen($data) - 4 > $this->_limit) {
			$data = $this->_addContinue($data);
		}
		$this->_data      = $this->_data.$data;
		$this->_datasize += strlen($data);
	}

	/**
	 * General storage function like _append, but returns string instead of modifying $this->_data
	 *
	 * @param string $data binary data to write
	 * @return string
	 */
	public function writeData($data)
	{
		if (strlen($data) - 4 > $this->_limit) {
			$data = $this->_addContinue($data);
		}
		$this->_datasize += strlen($data);
		
		return $data;
	}

	/**
	 * Writes Excel BOF record to indicate the beginning of a stream or
	 * sub-stream in the BIFF file.
	 *
	 * @param  integer $type Type of BIFF file to write: 0x0005 Workbook,
	 *                       0x0010 Worksheet.
	 * @access private
	 */
	function _storeBof($type)
	{
		$record  = 0x0809;        // Record identifier

		// According to the SDK $build and $year should be set to zero.
		// However, this throws a warning in Excel 5. So, use magic numbers.
		if ($this->_BIFF_version == 0x0500) {
			$length  = 0x0008;
			$unknown = '';
			$build   = 0x096C;
			$year    = 0x07C9;
		} elseif ($this->_BIFF_version == 0x0600) {
			$length  = 0x0010;

			// by inspection of real files, MS Office Excel 2007 writes the following 
			$unknown = pack("VV", 0x000100D1, 0x00000406);

			$build   = 0x0DBB;
			$year    = 0x07CC;
		}
		$version = $this->_BIFF_version;

		$header  = pack("vv",   $record, $length);
		$data    = pack("vvvv", $version, $type, $build, $year);
		$this->_append($header . $data . $unknown);
	}

	/**
	 * Writes Excel EOF record to indicate the end of a BIFF stream.
	 *
	 * @access private
	 */
	function _storeEof()
	{
		$record    = 0x000A;   // Record identifier
		$length    = 0x0000;   // Number of bytes to follow
		$header    = pack("vv", $record, $length);
		$this->_append($header);
	}

	/**
	 * Writes Excel EOF record to indicate the end of a BIFF stream.
	 *
	 * @access private
	 */
	public function writeEof()
	{
		$record    = 0x000A;   // Record identifier
		$length    = 0x0000;   // Number of bytes to follow
		$header    = pack("vv", $record, $length);
		return $this->writeData($header);
	}

	/**
	 * Excel limits the size of BIFF records. In Excel 5 the limit is 2084 bytes. In
	 * Excel 97 the limit is 8228 bytes. Records that are longer than these limits
	 * must be split up into CONTINUE blocks.
	 *
	 * This function takes a long BIFF record and inserts CONTINUE records as
	 * necessary.
	 *
	 * @param  string  $data The original binary data to be written
	 * @return string        A very convenient string of continue blocks
	 * @access private
	 */
	function _addContinue($data)
	{
		$limit  = $this->_limit;
		$record = 0x003C;         // Record identifier

		// The first 2080/8224 bytes remain intact. However, we have to change
		// the length field of the record.
		$tmp = substr($data, 0, 2) . pack("v", $limit) . substr($data, 4, $limit);

		$header = pack("vv", $record, $limit);  // Headers for continue records

		// Retrieve chunks of 2080/8224 bytes +4 for the header.
		$data_length = strlen($data);
		for ($i = $limit + 4; $i < ($data_length - $limit); $i += $limit) {
			$tmp .= $header;
			$tmp .= substr($data, $i, $limit);
		}

		// Retrieve the last chunk of data
		$header  = pack("vv", $record, strlen($data) - $i);
		$tmp    .= $header;
		$tmp    .= substr($data, $i, strlen($data) - $i);

		return $tmp;
	}
}

/**
 * Escher_DggContainer_BstoreContainer
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWriter_Excel5_Escher extends ObjectBase
{
    /**
     * The object we are writing
     */
    private $_object;
    
    /**
     * The written binary data
     */
    private $_data;
    
    /**
     * Shape offsets. Positions in binary stream where a new shape record begins
     *
     * @var array
     */
    private $_spOffsets;
    
    public function __construct($object)
    {
        $this->_object = $object;
    }
    
    /**
     * Process the object to be written
     */
    public function close()
    {
        // initialize
        $this->_data = '';
        
        switch (get_class($this->_object))
        {
            
            case 'Escher':
                if ($dggContainer = $this->_object->getDggContainer())
                {
                    $writer      = new ExcelWriter_Excel5_Escher($dggContainer);
                    $this->_data = $writer->close();
                }
                else if ($dgContainer = $this->_object->getDgContainer())
                {
                    $writer           = new ExcelWriter_Excel5_Escher($dgContainer);
                    $this->_data      = $writer->close();
                    $this->_spOffsets = $writer->getSpOffsets();
                }
                break;
            
            case 'Escher_DggContainer':
                // this is a container record
                
                // initialize
                $innerData = '';
                
                // write the dgg
                $recVer      = 0x0;
                $recInstance = 0x0000;
                $recType     = 0xF006;
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                // dgg data
                $dggData = pack('VVVV', $this->_object->getSpIdMax() // maximum shape identifier increased by one
                    , $this->_object->getCDgSaved() + 1 // number of file identifier clusters increased by one
                    , $this->_object->getCSpSaved(), $this->_object->getCDgSaved() // count total number of drawings saved
                    );
                // add file identifier clusters (one per drawing)
                for ($i = 0; $i < $this->_object->getCDgSaved(); ++$i)
                {
                    $dggData .= pack('VV', 0, 0);
                }
                
                $header = pack('vvV', $recVerInstance, $recType, strlen($dggData));
                $innerData .= $header . $dggData;
                
                // write the bstoreContainer
                if ($bstoreContainer = $this->_object->getBstoreContainer())
                {
                    $writer = new ExcelWriter_Excel5_Escher($bstoreContainer);
                    $innerData .= $writer->close();
                }
                
                // write the record
                $recVer      = 0xF;
                $recInstance = 0x0000;
                $recType     = 0xF000;
                $length      = strlen($innerData);
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                $this->_data = $header . $innerData;
                break;
            
            case 'Escher_DggContainer_BstoreContainer':
                // this is a container record
                
                // initialize
                $innerData = '';
                
                // treat the inner data
                if ($BSECollection = $this->_object->getBSECollection())
                {
                    foreach ($BSECollection as $BSE)
                    {
                        $writer = new ExcelWriter_Excel5_Escher($BSE);
                        $innerData .= $writer->close();
                    }
                }
                
                // write the record
                $recVer      = 0xF;
                $recInstance = count($this->_object->getBSECollection());
                $recType     = 0xF001;
                $length      = strlen($innerData);
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                $this->_data = $header . $innerData;
                break;
            
            case 'EscherBstoreContainerBSE':
                // this is a semi-container record
                
                // initialize
                $innerData = '';
                
                // here we treat the inner data
                if ($blip = $this->_object->getBlip())
                {
                    $writer = new ExcelWriter_Excel5_Escher($blip);
                    $innerData .= $writer->close();
                }
                
                // initialize
                $data    = '';
                $btWin32 = $this->_object->getBlipType();
                $btMacOS = $this->_object->getBlipType();
                $data .= pack('CC', $btWin32, $btMacOS);
                
                $rgbUid = pack('VVVV', 0, 0, 0, 0);
                $data .= $rgbUid;
                
                $tag     = 0;
                $size    = strlen($innerData);
                $cRef    = 1;
                $foDelay = 0;
                $unused1 = 0x0;
                $cbName  = 0x0;
                $unused2 = 0x0;
                $unused3 = 0x0;
                $data .= pack('vVVVCCCC', $tag, $size, $cRef, $foDelay, $unused1, $cbName, $unused2, $unused3);
                
                $data .= $innerData;
                
                // write the record
                $recVer      = 0x2;
                $recInstance = $this->_object->getBlipType();
                $recType     = 0xF007;
                $length      = strlen($data);
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                $this->_data = $header;
                $this->_data .= $data;
                break;
            
            case 'EscherBstoreContainerBSE_Blip':
                // this is an atom record
                
                // write the record
                switch ($this->_object->getParent()->getBlipType())
                {
                    
                    case EscherBstoreContainerBSE::BLIPTYPE_JPEG:
                        // initialize
                        $innerData = '';
                        
                        $rgbUid1 = pack('VVVV', 0, 0, 0, 0);
                        $innerData .= $rgbUid1;
                        
                        $tag = 0xFF;
                        $innerData .= pack('C', $tag);
                        
                        $innerData .= $this->_object->getData();
                        
                        $recVer      = 0x0;
                        $recInstance = 0x46A;
                        $recType     = 0xF01D;
                        $length      = strlen($innerData);
                        
                        $recVerInstance = $recVer;
                        $recVerInstance |= $recInstance << 4;
                        
                        $header = pack('vvV', $recVerInstance, $recType, $length);
                        
                        $this->_data = $header;
                        $this->_data .= $innerData;
                        break;
                    
                    case EscherBstoreContainerBSE::BLIPTYPE_PNG:
                        // initialize
                        $innerData = '';
                        
                        $rgbUid1 = pack('VVVV', 0, 0, 0, 0);
                        $innerData .= $rgbUid1;
                        
                        $tag = 0xFF;
                        $innerData .= pack('C', $tag);
                        
                        $innerData .= $this->_object->getData();
                        
                        $recVer      = 0x0;
                        $recInstance = 0x6E0;
                        $recType     = 0xF01E;
                        $length      = strlen($innerData);
                        
                        $recVerInstance = $recVer;
                        $recVerInstance |= $recInstance << 4;
                        
                        $header = pack('vvV', $recVerInstance, $recType, $length);
                        
                        $this->_data = $header;
                        $this->_data .= $innerData;
                        break;
                        
                }
                break;
            
            case 'Escher_DgContainer':
                // this is a container record
                
                // initialize
                $innerData = '';
                
                // write the dg
                $recVer      = 0x0;
                $recInstance = $this->_object->getDgId();
                $recType     = 0xF008;
                $length      = 8;
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                // number of shapes in this drawing (including group shape)
                $countShapes = count($this->_object->getSpgrContainer()->getChildren());
                $innerData .= $header . pack('VV', $countShapes, $this->_object->getLastSpId());
                //$innerData .= $header . pack('VV', 0, 0);
                
                // write the spgrContainer
                if ($spgrContainer = $this->_object->getSpgrContainer())
                {
                    $writer = new ExcelWriter_Excel5_Escher($spgrContainer);
                    $innerData .= $writer->close();
                    
                    // get the shape offsets relative to the spgrContainer record
                    $spOffsets = $writer->getSpOffsets();
                    
                    // save the shape offsets relative to dgContainer
                    foreach ($spOffsets as &$spOffset)
                    {
                        $spOffset += 24; // add length of dgContainer header data (8 bytes) plus dg data (16 bytes)
                    }
                    
                    $this->_spOffsets = $spOffsets;
                }
                
                // write the record
                $recVer      = 0xF;
                $recInstance = 0x0000;
                $recType     = 0xF002;
                $length      = strlen($innerData);
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                $this->_data = $header . $innerData;
                break;
            
            case 'EscherSpgrContainer':
                // this is a container record
                
                // initialize
                $innerData = '';
                
                // initialize spape offsets
                $totalSize = 8;
                $spOffsets = array();
                
                // treat the inner data
                foreach ($this->_object->getChildren() as $spContainer)
                {
                    $writer = new ExcelWriter_Excel5_Escher($spContainer);
                    $spData = $writer->close();
                    $innerData .= $spData;
                    
                    // save the shape offsets (where new shape records begin)
                    $totalSize += strlen($spData);
                    $spOffsets[] = $totalSize;
                }
                
                // write the record
                $recVer      = 0xF;
                $recInstance = 0x0000;
                $recType     = 0xF003;
                $length      = strlen($innerData);
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                $this->_data      = $header . $innerData;
                $this->_spOffsets = $spOffsets;
                break;
            
            case 'EscherSpgrContainer_SpContainer':
                // initialize
                $data = '';
                
                // write group shape record, if necessary?
                if ($this->_object->getSpgr())
                {
                    $recVer      = 0x1;
                    $recInstance = 0x0000;
                    $recType     = 0xF009;
                    $length      = 0x00000010;
                    
                    $recVerInstance = $recVer;
                    $recVerInstance |= $recInstance << 4;
                    
                    $header = pack('vvV', $recVerInstance, $recType, $length);
                    
                    $data .= $header . pack('VVVV', 0, 0, 0, 0);
                }
                
                // write the shape record
                $recVer      = 0x2;
                $recInstance = $this->_object->getSpType(); // shape type
                $recType     = 0xF00A;
                $length      = 0x00000008;
                
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                $data .= $header . pack('VV', $this->_object->getSpId(), $this->_object->getSpgr() ? 0x0005 : 0xA000);
                
                
                // the options
                if ($this->_object->getOPTCollection())
                {
                    $optData = '';
                    
                    $recVer      = 0x3;
                    $recInstance = count($this->_object->getOPTCollection());
                    $recType     = 0xF00B;
                    foreach ($this->_object->getOPTCollection() as $property => $value)
                    {
                        $optData .= pack('vV', $property, $value);
                    }
                    $length = strlen($optData);
                    
                    $recVerInstance = $recVer;
                    $recVerInstance |= $recInstance << 4;
                    
                    $header = pack('vvV', $recVerInstance, $recType, $length);
                    $data .= $header . $optData;
                }
                
                // the client anchor
                if ($this->_object->getStartCoordinates())
                {
                    $clientAnchorData = '';
                    $recVer           = 0x0;
                    $recInstance      = 0x0;
                    $recType          = 0xF010;
                    
                    // start coordinates
                    list($column, $row) = ExcelCell::coordinateFromString($this->_object->getStartCoordinates());
                    $c1 = ExcelCell::columnIndexFromString($column) - 1;
                    $r1 = $row - 1;
                    
                    // start offsetX
                    $startOffsetX = $this->_object->getStartOffsetX();
                    
                    // start offsetY
                    $startOffsetY = $this->_object->getStartOffsetY();
                    
                    // end coordinates
                    list($column, $row) = ExcelCell::coordinateFromString($this->_object->getEndCoordinates());
                    $c2 = ExcelCell::columnIndexFromString($column) - 1;
                    $r2 = $row - 1;
                    
                    // end offsetX
                    $endOffsetX = $this->_object->getEndOffsetX();
                    
                    // end offsetY
                    $endOffsetY = $this->_object->getEndOffsetY();
                    
                    $clientAnchorData = pack('vvvvvvvvv', 0x00, $c1, $startOffsetX, $r1, $startOffsetY, $c2, $endOffsetX, $r2, $endOffsetY);
                    
                    $length         = strlen($clientAnchorData);
                    $recVerInstance = $recVer;
                    $recVerInstance |= $recInstance << 4;
                    
                    $header = pack('vvV', $recVerInstance, $recType, $length);
                    $data .= $header . $clientAnchorData;
                }
                
                // the client data, just empty for now
                if (!$this->_object->getSpgr())
                {
                    $clientDataData = '';
                    $recVer         = 0x0;
                    $recInstance    = 0x0;
                    $recType        = 0xF011;
                    
                    $length         = strlen($clientDataData);
                    $recVerInstance = $recVer;
                    $recVerInstance |= $recInstance << 4;
                    
                    $header = pack('vvV', $recVerInstance, $recType, $length);
                    $data .= $header . $clientDataData;
                }
                
                // write the record
                $recVer         = 0xF;
                $recInstance    = 0x0000;
                $recType        = 0xF004;
                $length         = strlen($data);
                $recVerInstance = $recVer;
                $recVerInstance |= $recInstance << 4;
                
                $header = pack('vvV', $recVerInstance, $recType, $length);
                
                $this->_data = $header . $data;
                break;
        }
        
        return $this->_data;
    }
    
    /**
     * Gets the shape offsets
     *
     * @return array
     */
    public function getSpOffsets()
    {
        return $this->_spOffsets;
    }
}
?>