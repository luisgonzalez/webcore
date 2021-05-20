<?php
/**
 * Escher_DgContainer
 *
 * @package    WebCore
 * @subpackage Excel
 */
class Escher_DgContainer extends ObjectBase
{
    /**
     * Drawing index, 1-based.
     *
     * @var int
     */
    private $_dgId;
    
    /**
     * Last shape index in this drawing
     *
     * @var int
     */
    private $_lastSpId;
    private $_spgrContainer = null;
    
    public function getDgId()
    {
        return $this->_dgId;
    }
    
    public function setDgId($value)
    {
        $this->_dgId = $value;
    }
    
    public function getLastSpId()
    {
        return $this->_lastSpId;
    }
    
    public function setLastSpId($value)
    {
        $this->_lastSpId = $value;
    }
    
    public function getSpgrContainer()
    {
        return $this->_spgrContainer;
    }
    
    public function setSpgrContainer($spgrContainer)
    {
        return $this->_spgrContainer = $spgrContainer;
    }
    
}

/**
 * Escher_DggContainer
 *
 * @package    WebCore
 * @subpackage Excel
 */
class Escher_DggContainer extends ObjectBase
{
    /**
     * Maximum shape index of all shapes in all drawings increased by one
     *
     * @var int
     */
    private $_spIdMax;
    
    /**
     * Total number of drawings saved
     *
     * @var int
     */
    private $_cDgSaved;
    
    /**
     * Total number of shapes saved (including group shapes)
     *
     * @var int
     */
    private $_cSpSaved;
    
    /**
     * BLIP Store Container
     *
     * @var Escher_DggContainer_BstoreContainer
     */
    private $_bstoreContainer;
    
    /**
     * Array of options for the drawing group
     *
     * @var array
     */
    private $_OPT = array();
    
    /**
     * Get maximum shape index of all shapes in all drawings (plus one)
     *
     * @return int
     */
    public function getSpIdMax()
    {
        return $this->_spIdMax;
    }
    
    /**
     * Set maximum shape index of all shapes in all drawings (plus one)
     *
     * @param int
     */
    public function setSpIdMax($value)
    {
        $this->_spIdMax = $value;
    }
    
    /**
     * Get total number of drawings saved
     *
     * @return int
     */
    public function getCDgSaved()
    {
        return $this->_cDgSaved;
    }
    
    /**
     * Set total number of drawings saved
     *
     * @param int
     */
    public function setCDgSaved($value)
    {
        $this->_cDgSaved = $value;
    }
    
    /**
     * Get total number of shapes saved (including group shapes)
     *
     * @return int
     */
    public function getCSpSaved()
    {
        return $this->_cSpSaved;
    }
    
    /**
     * Set total number of shapes saved (including group shapes)
     *
     * @param int
     */
    public function setCSpSaved($value)
    {
        $this->_cSpSaved = $value;
    }
    
    /**
     * Get BLIP Store Container
     *
     * @return Escher_DggContainer_BstoreContainer
     */
    public function getBstoreContainer()
    {
        return $this->_bstoreContainer;
    }
    
    /**
     * Set BLIP Store Container
     *
     * @param Escher_DggContainer_BstoreContainer $bstoreContainer
     */
    public function setBstoreContainer($bstoreContainer)
    {
        $this->_bstoreContainer = $bstoreContainer;
    }
    
    /**
     * Set an option for the drawing group
     *
     * @param int $property The number specifies the option
     * @param mixed $value
     */
    public function setOPT($property, $value)
    {
        $this->_OPT[$property] = $value;
    }
    
    /**
     * Get an option for the drawing group
     *
     * @param int $property The number specifies the option
     * @return mixed
     */
    public function getOPT($property)
    {
        if (isset($this->_OPT[$property]))
        {
            return $this->_OPT[$property];
        }
        return null;
    }
}

/**
 * Escher_DggContainer_BstoreContainer
 *
 * @package    WebCore
 * @subpackage Excel
 */
class Escher_DggContainer_BstoreContainer extends ObjectBase
{
    /**
     * BLIP Store Entries. Each of them holds one BLIP (Big Large Image or Picture)
     *
     * @var array
     */
    private $_BSECollection = array();
    
    /**
     * Add a BLIP Store Entry
     *
     * @param EscherBstoreContainerBSE $BSE
     */
    public function addBSE($BSE)
    {
        $this->_BSECollection[] = $BSE;
        $BSE->setParent($this);
    }
    
    /**
     * Get the collection of BLIP Store Entries
     *
     * @return EscherBstoreContainerBSE[]
     */
    public function getBSECollection()
    {
        return $this->_BSECollection;
    }
}

/**
 * EscherBstoreContainerBSE
 *
 * @package    WebCore
 * @subpackage Excel
 */
class EscherBstoreContainerBSE extends ObjectBase
{
    const BLIPTYPE_ERROR = 0x00;
    const BLIPTYPE_UNKNOWN = 0x01;
    const BLIPTYPE_EMF = 0x02;
    const BLIPTYPE_WMF = 0x03;
    const BLIPTYPE_PICT = 0x04;
    const BLIPTYPE_JPEG = 0x05;
    const BLIPTYPE_PNG = 0x06;
    const BLIPTYPE_DIB = 0x07;
    const BLIPTYPE_TIFF = 0x11;
    const BLIPTYPE_CMYKJPEG = 0x12;
    
    /**
     * The parent BLIP Store Entry Container
     *
     * @var Escher_DggContainer_BstoreContainer
     */
    private $_parent;
    
    /**
     * The BLIP (Big Large Image or Picture)
     *
     * @var EscherBstoreContainerBSE_Blip
     */
    private $_blip;
    
    /**
     * The BLIP type
     *
     * @var int
     */
    private $_blipType;
    
    /**
     * Set parent BLIP Store Entry Container
     *
     * @param Escher_DggContainer_BstoreContainer $parent
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }
    
    /**
     * Get the BLIP
     *
     * @return EscherBstoreContainerBSE_Blip
     */
    public function getBlip()
    {
        return $this->_blip;
    }
    
    /**
     * Set the BLIP
     *
     * @param EscherBstoreContainerBSE_Blip $blip
     */
    public function setBlip($blip)
    {
        $this->_blip = $blip;
        $blip->setParent($this);
    }
    
    /**
     * Get the BLIP type
     *
     * @return int
     */
    public function getBlipType()
    {
        return $this->_blipType;
    }
    
    /**
     * Set the BLIP type
     *
     * @param int
     */
    public function setBlipType($blipType)
    {
        $this->_blipType = $blipType;
    }
    
}

/**
 * EscherBstoreContainerBSE_Blip
 *
 * @package    WebCore
 * @subpackage Excel
 */
class EscherBstoreContainerBSE_Blip extends ObjectBase
{
    /**
     * The parent BSE
     *
     * @var EscherBstoreContainerBSE
     */
    private $_parent;
    
    /**
     * Raw image data
     *
     * @var string
     */
    private $_data;
    
    /**
     * Get the raw image data
     *
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * Set the raw image data
     *
     * @param string
     */
    public function setData($data)
    {
        $this->_data = $data;
    }
    
    /**
     * Set parent BSE
     *
     * @param EscherBstoreContainerBSE $parent
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }
    
    /**
     * Get parent BSE
     *
     * @return EscherBstoreContainerBSE $parent
     */
    public function getParent()
    {
        return $this->_parent;
    }
}

/**
 * EscherSpgrContainer
 *
 * @package    WebCore
 * @subpackage Excel
 */
class EscherSpgrContainer extends ObjectBase
{
    /**
     * Parent Shape Group Container
     *
     * @var EscherSpgrContainer
     */
    private $_parent;
    
    /**
     * Shape Container collection
     *
     * @var array
     */
    private $_children = array();
    
    /**
     * Set parent Shape Group Container
     *
     * @param EscherSpgrContainer $parent
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }
    
    /**
     * Get the parent Shape Group Container if any
     *
     * @return EscherSpgrContainer|null
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * Add a child. This will be either spgrContainer or spContainer
     *
     * @param mixed $child
     */
    public function addChild($child)
    {
        $this->_children[] = $child;
        $child->setParent($this);
    }
    
    /**
     * Get collection of Shape Containers
     */
    public function getChildren()
    {
        return $this->_children;
    }
    
    /**
     * Recursively get all spContainers within this spgrContainer
     *
     * @return EscherSpgrContainer_SpContainer[]
     */
    public function getAllSpContainers()
    {
        $allSpContainers = array();
        
        foreach ($this->_children as $child)
        {
            if ($child instanceof EscherSpgrContainer)
            {
                $allSpContainers = array_merge($allSpContainers, $child->getAllSpContainers());
            }
            else
            {
                $allSpContainers[] = $child;
            }
        }
        
        return $allSpContainers;
    }
}

/**
 * EscherSpgrContainer_SpContainer
 *
 * @package    WebCore
 * @subpackage Excel
 */
class EscherSpgrContainer_SpContainer extends ObjectBase
{
    /**
     * Parent Shape Group Container
     *
     * @var EscherSpgrContainer
     */
    private $_parent;
    
    /**
     * Is this a group shape?
     *
     * @var boolean
     */
    private $_spgr = false;
    
    /**
     * Shape type
     *
     * @var int
     */
    private $_spType;
    
    /**
     * Shape index (usually group shape has index 0, and the rest: 1,2,3...)
     *
     * @var boolean
     */
    private $_spId;
    
    /**
     * Array of options
     *
     * @var array
     */
    private $_OPT;
    
    /**
     * Cell coordinates of upper-left corner of shape, e.g. 'A1'
     *
     * @var string
     */
    private $_startCoordinates;
    
    /**
     * Horizontal offset of upper-left corner of shape measured in 1/1024 of column width
     *
     * @var int
     */
    private $_startOffsetX;
    
    /**
     * Vertical offset of upper-left corner of shape measured in 1/256 of row height
     *
     * @var int
     */
    private $_startOffsetY;
    
    /**
     * Cell coordinates of bottom-right corner of shape, e.g. 'B2'
     *
     * @var string
     */
    private $_endCoordinates;
    
    /**
     * Horizontal offset of bottom-right corner of shape measured in 1/1024 of column width
     *
     * @var int
     */
    private $_endOffsetX;
    
    /**
     * Vertical offset of bottom-right corner of shape measured in 1/256 of row height
     *
     * @var int
     */
    private $_endOffsetY;
    
    /**
     * Set parent Shape Group Container
     *
     * @param EscherSpgrContainer $parent
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }
    
    /**
     * Get the parent Shape Group Container
     *
     * @return EscherSpgrContainer
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * Set whether this is a group shape
     *
     * @param boolean $value
     */
    public function setSpgr($value = false)
    {
        $this->_spgr = $value;
    }
    
    /**
     * Get whether this is a group shape
     *
     * @return boolean
     */
    public function getSpgr()
    {
        return $this->_spgr;
    }
    
    /**
     * Set the shape type
     *
     * @param int $value
     */
    public function setSpType($value)
    {
        $this->_spType = $value;
    }
    
    /**
     * Get the shape type
     *
     * @return int
     */
    public function getSpType()
    {
        return $this->_spType;
    }
    
    /**
     * Set the shape index
     *
     * @param int $value
     */
    public function setSpId($value)
    {
        $this->_spId = $value;
    }
    
    /**
     * Get the shape index
     *
     * @return int
     */
    public function getSpId()
    {
        return $this->_spId;
    }
    
    /**
     * Set an option for the Shape Group Container
     *
     * @param int $property The number specifies the option
     * @param mixed $value
     */
    public function setOPT($property, $value)
    {
        $this->_OPT[$property] = $value;
    }
    
    /**
     * Get an option for the Shape Group Container
     *
     * @param int $property The number specifies the option
     * @return mixed
     */
    public function getOPT($property)
    {
        if (isset($this->_OPT[$property]))
        {
            return $this->_OPT[$property];
        }
        return null;
    }
    
    /**
     * Get the collection of options
     *
     * @return array
     */
    public function getOPTCollection()
    {
        return $this->_OPT;
    }
    
    /**
     * Set cell coordinates of upper-left corner of shape
     *
     * @param string $value
     */
    public function setStartCoordinates($value = 'A1')
    {
        $this->_startCoordinates = $value;
    }
    
    /**
     * Get cell coordinates of upper-left corner of shape
     *
     * @return string
     */
    public function getStartCoordinates()
    {
        return $this->_startCoordinates;
    }
    
    /**
     * Set offset in x-direction of upper-left corner of shape measured in 1/1024 of column width
     *
     * @param int $startOffsetX
     */
    public function setStartOffsetX($startOffsetX = 0)
    {
        $this->_startOffsetX = $startOffsetX;
    }
    
    /**
     * Get offset in x-direction of upper-left corner of shape measured in 1/1024 of column width
     *
     * @return int
     */
    public function getStartOffsetX()
    {
        return $this->_startOffsetX;
    }
    
    /**
     * Set offset in y-direction of upper-left corner of shape measured in 1/256 of row height
     *
     * @param int $startOffsetY
     */
    public function setStartOffsetY($startOffsetY = 0)
    {
        $this->_startOffsetY = $startOffsetY;
    }
    
    /**
     * Get offset in y-direction of upper-left corner of shape measured in 1/256 of row height
     *
     * @return int
     */
    public function getStartOffsetY()
    {
        return $this->_startOffsetY;
    }
    
    /**
     * Set cell coordinates of bottom-right corner of shape
     *
     * @param string $value
     */
    public function setEndCoordinates($value = 'A1')
    {
        $this->_endCoordinates = $value;
    }
    
    /**
     * Get cell coordinates of bottom-right corner of shape
     *
     * @return string
     */
    public function getEndCoordinates()
    {
        return $this->_endCoordinates;
    }
    
    /**
     * Set offset in x-direction of bottom-right corner of shape measured in 1/1024 of column width
     *
     * @param int $startOffsetX
     */
    public function setEndOffsetX($endOffsetX = 0)
    {
        $this->_endOffsetX = $endOffsetX;
    }
    
    /**
     * Get offset in x-direction of bottom-right corner of shape measured in 1/1024 of column width
     *
     * @return int
     */
    public function getEndOffsetX()
    {
        return $this->_endOffsetX;
    }
    
    /**
     * Set offset in y-direction of bottom-right corner of shape measured in 1/256 of row height
     *
     * @param int $endOffsetY
     */
    public function setEndOffsetY($endOffsetY = 0)
    {
        $this->_endOffsetY = $endOffsetY;
    }
    
    /**
     * Get offset in y-direction of bottom-right corner of shape measured in 1/256 of row height
     *
     * @return int
     */
    public function getEndOffsetY()
    {
        return $this->_endOffsetY;
    }
    
    /**
     * Get the nesting level of this spContainer. This is the number of spgrContainers between this spContainer and
     * the dgContainer. A value of 1 = immediately within first spgrContainer
     * Higher nesting level occurs if and only if spContainer is part of a shape group
     *
     * @return int Nesting level
     */
    public function getNestingLevel()
    {
        $nestingLevel = 0;
        
        $parent = $this->getParent();
        while ($parent instanceof EscherSpgrContainer)
        {
            ++$nestingLevel;
            $parent = $parent->getParent();
        }
        
        return $nestingLevel;
    }
}

/**
 * Escher
 *
 * @package    WebCore
 * @subpackage Excel
 */
class Escher extends ObjectBase
{
    /**
     * Drawing Group Container
     *
     * @var Escher_DggContainer
     */
    private $_dggContainer;
    
    /**
     * Drawing Container
     *
     * @var Escher_DgContainer
     */
    private $_dgContainer;
    
    /**
     * Get Drawing Group Container
     *
     * @return Escher_DgContainer
     */
    public function getDggContainer()
    {
        return $this->_dggContainer;
    }
    
    /**
     * Set Drawing Group Container
     *
     * @param Escher_DggContainer $dggContainer
     */
    public function setDggContainer($dggContainer)
    {
        return $this->_dggContainer = $dggContainer;
    }
    
    /**
     * Get Drawing Container
     *
     * @return Escher_DgContainer
     */
    public function getDgContainer()
    {
        return $this->_dgContainer;
    }
    
    /**
     * Set Drawing Container
     *
     * @param Escher_DgContainer $dgContainer
     */
    public function setDgContainer($dgContainer)
    {
        return $this->_dgContainer = $dgContainer;
    }
    
}

/**
 * EscherExcel5
 *
 * @package    WebCore
 * @subpackage Excel
 */
class EscherExcel5 extends ObjectBase
{
    const DGGCONTAINER = 0xF000;
    const BSTORECONTAINER = 0xF001;
    const DGCONTAINER = 0xF002;
    const SPGRCONTAINER = 0xF003;
    const SPCONTAINER = 0xF004;
    const DGG = 0xF006;
    const BSE = 0xF007;
    const DG = 0xF008;
    const SPGR = 0xF009;
    const SP = 0xF00A;
    const OPT = 0xF00B;
    const CLIENTTEXTBOX = 0xF00D;
    const CLIENTANCHOR = 0xF010;
    const CLIENTDATA = 0xF011;
    const BLIPJPEG = 0xF01D;
    const BLIPPNG = 0xF01E;
    const SPLITMENUCOLORS = 0xF11E;
    const TERTIARYOPT = 0xF122;
    
    /**
     * Escher stream data (binary)
     *
     * @var string
     */
    private $_data;
    
    /**
     * Size in bytes of the Escher stream data
     *
     * @var int
     */
    private $_dataSize;
    
    /**
     * Current position of stream pointer in Escher stream data
     *
     * @var int
     */
    private $_pos;
    
    /**
     * The object to be returned by the reader. Modified during load.
     *
     * @var mixed
     */
    private $_object;
    
    /**
     * Create a new EscherExcel5 instance
     *
     * @param mixed $object
     */
    public function __construct($object)
    {
        $this->_object = $object;
    }
    
    /**
     * Load Escher stream data. May be a partial Escher stream.
     *
     * @param string $data
     */
    public function load($data)
    {
        $this->_data = $data;
        
        // total byte size of Excel data (workbook global substream + sheet substreams)
        $this->_dataSize = strlen($this->_data);
        
        $this->_pos = 0;
        
        // Parse Escher stream
        while ($this->_pos < $this->_dataSize)
        {
            // offset: 2; size: 2: Record Type
            $fbt = $this->_GetInt2d($this->_data, $this->_pos + 2);
            
            switch ($fbt)
            {
                case self::DGGCONTAINER:
                    $this->_readDggContainer();
                    break;
                case self::DGG:
                    $this->_readDgg();
                    break;
                case self::BSTORECONTAINER:
                    $this->_readBstoreContainer();
                    break;
                case self::BSE:
                    $this->_readBSE();
                    break;
                case self::BLIPJPEG:
                    $this->_readBlipJPEG();
                    break;
                case self::BLIPPNG:
                    $this->_readBlipPNG();
                    break;
                case self::OPT:
                    $this->_readOPT();
                    break;
                case self::TERTIARYOPT:
                    $this->_readTertiaryOPT();
                    break;
                case self::SPLITMENUCOLORS:
                    $this->_readSplitMenuColors();
                    break;
                case self::DGCONTAINER:
                    $this->_readDgContainer();
                    break;
                case self::DG:
                    $this->_readDg();
                    break;
                case self::SPGRCONTAINER:
                    $this->_readSpgrContainer();
                    break;
                case self::SPCONTAINER:
                    $this->_readSpContainer();
                    break;
                case self::SPGR:
                    $this->_readSpgr();
                    break;
                case self::SP:
                    $this->_readSp();
                    break;
                case self::CLIENTTEXTBOX:
                    $this->_readClientTextbox();
                    break;
                case self::CLIENTANCHOR:
                    $this->_readClientAnchor();
                    break;
                case self::CLIENTDATA:
                    $this->_readClientData();
                    break;
                default:
                    $this->_readDefault();
                    break;
            }
        }
        
        return $this->_object;
    }
    
    /**
     * Read a generic record
     */
    private function _readDefault()
    {
        // offset 0; size: 2; recVer and recInstance
        $verInstance = $this->_GetInt2d($this->_data, $this->_pos);
        
        // offset: 2; size: 2: Record Type
        $fbt = $this->_GetInt2d($this->_data, $this->_pos + 2);
        
        // bit: 0-3; mask: 0x000F; recVer
        $recVer = (0x000F & $verInstance) >> 0;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read DggContainer record (Drawing Group Container)
     */
    private function _readDggContainer()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        // record is a container, read contents
        $dggContainer = new Escher_DggContainer();
        $this->_object->setDggContainer($dggContainer);
        $reader = new EscherExcel5($dggContainer);
        $reader->load($recordData);
    }
    
    /**
     * Read Dgg record (Drawing Group)
     */
    private function _readDgg()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read BstoreContainer record (Blip Store Container)
     */
    private function _readBstoreContainer()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        // record is a container, read contents
        $bstoreContainer = new Escher_DggContainer_BstoreContainer();
        $this->_object->setBstoreContainer($bstoreContainer);
        $reader = new EscherExcel5($bstoreContainer);
        $reader->load($recordData);
    }
    
    /**
     * Read BSE record
     */
    private function _readBSE()
    {
        // offset: 0; size: 2; recVer and recInstance
        
        // bit: 4-15; mask: 0xFFF0; recInstance
        $recInstance = (0xFFF0 & $this->_GetInt2d($this->_data, $this->_pos)) >> 4;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        // add BSE to BstoreContainer
        $BSE = new EscherBstoreContainerBSE();
        $this->_object->addBSE($BSE);
        
        $BSE->setBLIPType($recInstance);
        
        // offset: 0; size: 1; btWin32 (MSOBLIPTYPE)
        $btWin32 = ord($recordData[0]);
        
        // offset: 1; size: 1; btWin32 (MSOBLIPTYPE)
        $btMacOS = ord($recordData[1]);
        
        // offset: 2; size: 16; MD4 digest
        $rgbUid = substr($recordData, 2, 16);
        
        // offset: 18; size: 2; tag
        $tag = $this->_GetInt2d($recordData, 18);
        
        // offset: 20; size: 4; size of BLIP in bytes
        $size = $this->_GetInt4d($recordData, 20);
        
        // offset: 24; size: 4; number of references to this BLIP
        $cRef = $this->_GetInt4d($recordData, 24);
        
        // offset: 28; size: 4; MSOFO file offset
        $foDelay = $this->_GetInt4d($recordData, 28);
        
        // offset: 32; size: 1; unused1
        $unused1 = ord($recordData{32});
        
        // offset: 33; size: 1; size of nameData in bytes (including null terminator)
        $cbName = ord($recordData{33});
        
        // offset: 34; size: 1; unused2
        $unused2 = ord($recordData{34});
        
        // offset: 35; size: 1; unused3
        $unused3 = ord($recordData{35});
        
        // offset: 36; size: $cbName; nameData
        $nameData = substr($recordData, 36, $cbName);
        
        // offset: 36 + $cbName, size: var; the BLIP data
        $blipData = substr($recordData, 36 + $cbName);
        
        // record is a container, read contents
        $reader = new EscherExcel5($BSE);
        $reader->load($blipData);
    }
    
    /**
     * Read BlipJPEG record. Holds raw JPEG image data
     */
    private function _readBlipJPEG()
    {
        // offset: 0; size: 2; recVer and recInstance
        
        // bit: 4-15; mask: 0xFFF0; recInstance
        $recInstance = (0xFFF0 & $this->_GetInt2d($this->_data, $this->_pos)) >> 4;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        $pos = 0;
        
        // offset: 0; size: 16; rgbUid1 (MD4 digest of)
        $rgbUid1 = substr($recordData, 0, 16);
        $pos += 16;
        
        // offset: 16; size: 16; rgbUid2 (MD4 digest), only if $recInstance = 0x46B or 0x6E3
        if (in_array($recInstance, array(
            0x046B,
            0x06E3
        )))
        {
            $rgbUid2 = substr($recordData, 16, 16);
            $pos += 16;
        }
        
        // offset: var; size: 1; tag
        $tag = ord($recordData{$pos});
        $pos += 1;
        
        // offset: var; size: var; the raw image data
        $data = substr($recordData, $pos);
        
        $blip = new EscherBstoreContainerBSE_Blip();
        $blip->setData($data);
        
        $this->_object->setBlip($blip);
    }
    
    /**
     * Read BlipPNG record. Holds raw PNG image data
     */
    private function _readBlipPNG()
    {
        // offset: 0; size: 2; recVer and recInstance
        
        // bit: 4-15; mask: 0xFFF0; recInstance
        $recInstance = (0xFFF0 & $this->_GetInt2d($this->_data, $this->_pos)) >> 4;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        $pos = 0;
        
        // offset: 0; size: 16; rgbUid1 (MD4 digest of)
        $rgbUid1 = substr($recordData, 0, 16);
        $pos += 16;
        
        // offset: 16; size: 16; rgbUid2 (MD4 digest), only if $recInstance = 0x46B or 0x6E3
        if ($recInstance == 0x06E1)
        {
            $rgbUid2 = substr($recordData, 16, 16);
            $pos += 16;
        }
        
        // offset: var; size: 1; tag
        $tag = ord($recordData{$pos});
        $pos += 1;
        
        // offset: var; size: var; the raw image data
        $data = substr($recordData, $pos);
        
        $blip = new EscherBstoreContainerBSE_Blip();
        $blip->setData($data);
        
        $this->_object->setBlip($blip);
    }
    
    /**
     * Read OPT record. This record may occur within DggContainer record or SpContainer
     */
    private function _readOPT()
    {
        // offset: 0; size: 2; recVer and recInstance
        
        // bit: 4-15; mask: 0xFFF0; recInstance
        $recInstance = (0xFFF0 & $this->_GetInt2d($this->_data, $this->_pos)) >> 4;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        $this->_readOfficeArtRGFOPTE($recordData, $recInstance);
    }
    
    /**
     * Read TertiaryOPT record
     */
    private function _readTertiaryOPT()
    {
        // offset: 0; size: 2; recVer and recInstance
        
        // bit: 4-15; mask: 0xFFF0; recInstance
        $recInstance = (0xFFF0 & $this->_GetInt2d($this->_data, $this->_pos)) >> 4;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read SplitMenuColors record
     */
    private function _readSplitMenuColors()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read DgContainer record (Drawing Container)
     */
    private function _readDgContainer()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        // record is a container, read contents
        $dgContainer = new Escher_DgContainer();
        $this->_object->setDgContainer($dgContainer);
        $reader = new EscherExcel5($dgContainer);
        $escher = $reader->load($recordData);
    }
    
    /**
     * Read Dg record (Drawing)
     */
    private function _readDg()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read SpgrContainer record (Shape Group Container)
     */
    private function _readSpgrContainer()
    {
        // context is either context DgContainer or SpgrContainer
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        // record is a container, read contents
        $spgrContainer = new EscherSpgrContainer();
        
        if ($this->_object instanceof Escher_DgContainer)
        {
            // DgContainer
            $this->_object->setSpgrContainer($spgrContainer);
        }
        else
        {
            // SpgrContainer
            $this->_object->addChild($spgrContainer);
        }
        
        $reader = new EscherExcel5($spgrContainer);
        $escher = $reader->load($recordData);
    }
    
    /**
     * Read SpContainer record (Shape Container)
     */
    private function _readSpContainer()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // add spContainer to spgrContainer
        $spContainer = new EscherSpgrContainer_SpContainer();
        $this->_object->addChild($spContainer);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        // record is a container, read contents
        $reader = new EscherExcel5($spContainer);
        $escher = $reader->load($recordData);
    }
    
    /**
     * Read Spgr record (Shape Group)
     */
    private function _readSpgr()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read Sp record (Shape)
     */
    private function _readSp()
    {
        // offset: 0; size: 2; recVer and recInstance
        
        // bit: 4-15; mask: 0xFFF0; recInstance
        $recInstance = (0xFFF0 & $this->_GetInt2d($this->_data, $this->_pos)) >> 4;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read ClientTextbox record
     */
    private function _readClientTextbox()
    {
        // offset: 0; size: 2; recVer and recInstance
        
        // bit: 4-15; mask: 0xFFF0; recInstance
        $recInstance = (0xFFF0 & $this->_GetInt2d($this->_data, $this->_pos)) >> 4;
        
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read ClientAnchor record. This record holds information about where the shape is anchored in worksheet
     */
    private function _readClientAnchor()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
        
        // offset: 2; size: 2; upper-left corner column index (0-based)
        $c1 = $this->_GetInt2d($recordData, 2);
        
        // offset: 4; size: 2; upper-left corner horizontal offset in 1/1024 of column width
        $startOffsetX = $this->_GetInt2d($recordData, 4);
        
        // offset: 6; size: 2; upper-left corner row index (0-based)
        $r1 = $this->_GetInt2d($recordData, 6);
        
        // offset: 8; size: 2; upper-left corner vertical offset in 1/256 of row height
        $startOffsetY = $this->_GetInt2d($recordData, 8);
        
        // offset: 10; size: 2; bottom-right corner column index (0-based)
        $c2 = $this->_GetInt2d($recordData, 10);
        
        // offset: 12; size: 2; bottom-right corner horizontal offset in 1/1024 of column width
        $endOffsetX = $this->_GetInt2d($recordData, 12);
        
        // offset: 14; size: 2; bottom-right corner row index (0-based)
        $r2 = $this->_GetInt2d($recordData, 14);
        
        // offset: 16; size: 2; bottom-right corner vertical offset in 1/256 of row height
        $endOffsetY = $this->_GetInt2d($recordData, 16);
        
        // set the start coordinates
        $this->_object->setStartCoordinates(ExcelCell::stringFromColumnIndex($c1) . ($r1 + 1));
        
        // set the start offsetX
        $this->_object->setStartOffsetX($startOffsetX);
        
        // set the start offsetY
        $this->_object->setStartOffsetY($startOffsetY);
        
        // set the end coordinates
        $this->_object->setEndCoordinates(ExcelCell::stringFromColumnIndex($c2) . ($r2 + 1));
        
        // set the end offsetX
        $this->_object->setEndOffsetX($endOffsetX);
        
        // set the end offsetY
        $this->_object->setEndOffsetY($endOffsetY);
    }
    
    /**
     * Read ClientData record
     */
    private function _readClientData()
    {
        $length     = $this->_GetInt4d($this->_data, $this->_pos + 4);
        $recordData = substr($this->_data, $this->_pos + 8, $length);
        
        // move stream pointer to next record
        $this->_pos += 8 + $length;
    }
    
    /**
     * Read OfficeArtRGFOPTE table of property-value pairs
     *
     * @param string $data Binary data
     * @param int $n Number of properties
     */
    private function _readOfficeArtRGFOPTE($data, $n)
    {
        $splicedComplexData = substr($data, 6 * $n);
        
        // loop through property-value pairs
        for ($i = 0; $i < $n; ++$i)
        {
            // read 6 bytes at a time
            $fopte = substr($data, 6 * $i, 6);
            
            // offset: 0; size: 2; opid
            $opid = $this->_GetInt2d($fopte, 0);
            
            // bit: 0-13; mask: 0x3FFF; opid.opid
            $opidOpid = (0x3FFF & $opid) >> 0;
            
            // bit: 14; mask 0x4000; 1 = value in op field is BLIP identifier
            $opidFBid = (0x4000 & $opid) >> 14;
            
            // bit: 15; mask 0x8000; 1 = this is a complex property, op field specifies size of complex data
            $opidFComplex = (0x8000 & $opid) >> 15;
            
            // offset: 2; size: 4; the value for this property
            $op = $this->_GetInt4d($fopte, 2);
            
            if ($opidFComplex)
            {
                $complexData        = substr($splicedComplexData, 0, $op);
                $splicedComplexData = substr($splicedComplexData, $op);
                
                // we store string value with complex data
                $value = $complexData;
            }
            else
            {
                // we store integer value
                $value = $op;
            }
            
            $this->_object->setOPT($opidOpid, $value);
        }
    }
    
    /**
     * Read 16-bit unsigned integer
     *
     * @param string $data
     * @param int $pos
     * @return int
     */
    private function _GetInt2d($data, $pos)
    {
        return ord($data[$pos]) | (ord($data[$pos + 1]) << 8);
    }
    
    /**
     * Read 32-bit signed integer
     *
     * @param string $data
     * @param int $pos
     * @return int
     */
    private function _GetInt4d($data, $pos)
    {
        $_or_24 = ord($data[$pos + 3]);
        if ($_or_24 >= 128)
        {
            // negative number
            $_ord_24 = -abs((256 - $_or_24) << 24);
        }
        else
        {
            $_ord_24 = ($_or_24 & 127) << 24;
        }
        return ord($data[$pos]) | (ord($data[$pos + 1]) << 8) | (ord($data[$pos + 2]) << 16) | $_ord_24;
    }
}
?>