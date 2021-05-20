<?php
/**
 * ExcelWorksheet_BaseDrawing
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_BaseDrawing extends ComparableBase
{
    /**
     * Image counter
     *
     * @var int
     */
    private static $_imageCounter = 0;
    
    /**
     * Image index
     *
     * @var int
     */
    private $_imageIndex = 0;
    
    /**
     * Name
     *
     * @var string
     */
    protected $_name;
    
    /**
     * Description
     *
     * @var string
     */
    protected $_description;
    
    /**
     * Worksheet
     *
     * @var ExcelWorksheet
     */
    protected $_worksheet;
    
    /**
     * Coordinates
     *
     * @var string
     */
    protected $_coordinates;
    
    /**
     * Offset X
     *
     * @var int
     */
    protected $_offsetX;
    
    /**
     * Offset Y
     *
     * @var int
     */
    protected $_offsetY;
    
    /**
     * Width
     *
     * @var int
     */
    protected $_width;
    
    /**
     * Height
     *
     * @var int
     */
    protected $_height;
    
    /**
     * Proportional resize
     *
     * @var boolean
     */
    protected $_resizeProportional;
    
    /**
     * Rotation
     *
     * @var int
     */
    protected $_rotation;
    
    /**
     * Shadow
     *
     * @var ExcelWorksheet_Drawing_Shadow
     */
    protected $_shadow;
    
    /**
     * Create a new ExcelWorksheet_BaseDrawing
     */
    public function __construct()
    {
        // Initialise values
        $this->_name               = '';
        $this->_description        = '';
        $this->_worksheet          = null;
        $this->_coordinates        = 'A1';
        $this->_offsetX            = 0;
        $this->_offsetY            = 0;
        $this->_width              = 0;
        $this->_height             = 0;
        $this->_resizeProportional = true;
        $this->_rotation           = 0;
        $this->_shadow             = new ExcelWorksheet_Drawing_Shadow();
        
        // Set image index
        self::$_imageCounter++;
        $this->_imageIndex = self::$_imageCounter;
    }
    
    /**
     * Get image index
     *
     * @return int
     */
    public function getImageIndex()
    {
        return $this->_imageIndex;
    }
    
    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Set Name
     *
     * @param string $pValue
     */
    public function setName($pValue = '')
    {
        $this->_name = $pValue;
    }
    
    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * Set Description
     *
     * @param string $pValue
     */
    public function setDescription($pValue = '')
    {
        $this->_description = $pValue;
    }
    
    /**
     * Get Worksheet
     *
     * @return ExcelWorksheet
     */
    public function getWorksheet()
    {
        return $this->_worksheet;
    }
    
    /**
     * Set Worksheet
     *
     * @param 	ExcelWorksheet 	$pValue
     * @param 	bool				$pOverrideOld	If a Worksheet has already been assigned, overwrite it and remove image from old Worksheet?
     * @throws 	Exception
     */
    public function setWorksheet(ExcelWorksheet $pValue = null, $pOverrideOld = false)
    {
        if (is_null($this->_worksheet))
        {
            // Add drawing to ExcelWorksheet
            $this->_worksheet = $pValue;
            $this->_worksheet->getCell($this->_coordinates);
            $this->_worksheet->getDrawingCollection()->append($this);
        }
        else
        {
            if ($pOverrideOld)
            {
                // Remove drawing from old ExcelWorksheet
                $iterator = $this->_worksheet->getDrawingCollection()->getIterator();
                
                while ($iterator->valid())
                {
                    if ($iterator->current()->getHashCode() == $this->getHashCode())
                    {
                        $this->_worksheet->getDrawingCollection()->offsetUnset($iterator->key());
                        $this->_worksheet = null;
                        break;
                    }
                }
                
                // Set new ExcelWorksheet
                $this->setWorksheet($pValue);
            }
            else
            {
                throw new Exception("A ExcelWorksheet has already been assigned. Drawings can only exist on one ExcelWorksheet.");
            }
        }
    }
    
    /**
     * Get Coordinates
     *
     * @return string
     */
    public function getCoordinates()
    {
        return $this->_coordinates;
    }
    
    /**
     * Set Coordinates
     *
     * @param string $pValue
     */
    public function setCoordinates($pValue = 'A1')
    {
        $this->_coordinates = $pValue;
    }
    
    /**
     * Get OffsetX
     *
     * @return int
     */
    public function getOffsetX()
    {
        return $this->_offsetX;
    }
    
    /**
     * Set OffsetX
     *
     * @param int $pValue
     */
    public function setOffsetX($pValue = 0)
    {
        $this->_offsetX = $pValue;
    }
    
    /**
     * Get OffsetY
     *
     * @return int
     */
    public function getOffsetY()
    {
        return $this->_offsetY;
    }
    
    /**
     * Set OffsetY
     *
     * @param int $pValue
     */
    public function setOffsetY($pValue = 0)
    {
        $this->_offsetY = $pValue;
    }
    
    /**
     * Get Width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }
    
    /**
     * Set Width
     *
     * @param int $pValue
     */
    public function setWidth($pValue = 0)
    {
        // Resize proportional?
        if ($this->_resizeProportional && $pValue != 0)
        {
            $ratio         = $this->_height / $this->_width;
            $this->_height = round($ratio * $pValue);
        }
        
        // Set width
        $this->_width = $pValue;
    }
    
    /**
     * Get Height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }
    
    /**
     * Set Height
     *
     * @param int $pValue
     */
    public function setHeight($pValue = 0)
    {
        // Resize proportional?
        if ($this->_resizeProportional && $pValue != 0)
        {
            $ratio        = $this->_width / $this->_height;
            $this->_width = round($ratio * $pValue);
        }
        
        // Set height
        $this->_height = $pValue;
    }
    
    /**
     * Set width and height with proportional resize
     * @author Vincent@luo MSN:kele_100@hotmail.com
     * @param int $width
     * @param int $height
     * @example $objDrawing->setResizeProportional(true);
     * @example $objDrawing->setWidthAndHeight(160,120);
     */
    public function setWidthAndHeight($width = 0, $height = 0)
    {
        $xratio = $width / $this->_width;
        $yratio = $height / $this->_height;
        if ($this->_resizeProportional && !($width == 0 || $height == 0))
        {
            if (($xratio * $this->_height) < $height)
            {
                $this->_height = ceil($xratio * $this->_height);
                $this->_width  = $width;
            }
            else
            {
                $this->_width  = ceil($yratio * $this->_width);
                $this->_height = $height;
            }
        }
    }
    
    /**
     * Get ResizeProportional
     *
     * @return boolean
     */
    public function getResizeProportional()
    {
        return $this->_resizeProportional;
    }
    
    /**
     * Set ResizeProportional
     *
     * @param boolean $pValue
     */
    public function setResizeProportional($pValue = true)
    {
        $this->_resizeProportional = $pValue;
    }
    
    /**
     * Get Rotation
     *
     * @return int
     */
    public function getRotation()
    {
        return $this->_rotation;
    }
    
    /**
     * Set Rotation
     *
     * @param int $pValue
     */
    public function setRotation($pValue = 0)
    {
        $this->_rotation = $pValue;
    }
    
    /**
     * Get Shadow
     *
     * @return ExcelWorksheet_Drawing_Shadow
     */
    public function getShadow()
    {
        return $this->_shadow;
    }
    
    /**
     * Set Shadow
     *
     * @param 	ExcelWorksheet_Drawing_Shadow $pValue
     * @throws 	Exception
     */
    public function setShadow(ExcelWorksheet_Drawing_Shadow $pValue = null)
    {
        $this->_shadow = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_name . $this->_description . $this->_worksheet->getHashCode() . $this->_coordinates . $this->_offsetX . $this->_offsetY . $this->_width . $this->_height . $this->_rotation . $this->_shadow->getHashCode() . __CLASS__);
    }
}

/**
 * ExcelWorksheet_MemoryDrawing
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_MemoryDrawing extends ExcelWorksheet_BaseDrawing
{
    /* Rendering functions */
    const RENDERING_DEFAULT = 'imagepng';
    const RENDERING_PNG = 'imagepng';
    const RENDERING_GIF = 'imagegif';
    const RENDERING_JPEG = 'imagejpeg';
    
    /* MIME types */
    const MIMETYPE_DEFAULT = 'image/png';
    const MIMETYPE_PNG = 'image/png';
    const MIMETYPE_GIF = 'image/gif';
    const MIMETYPE_JPEG = 'image/jpeg';
    
    /**
     * Image resource
     *
     * @var resource
     */
    private $_imageResource;
    
    /**
     * Rendering function
     *
     * @var string
     */
    private $_renderingFunction;
    
    /**
     * Mime type
     *
     * @var string
     */
    private $_mimeType;
    
    /**
     * Unique name
     *
     * @var string
     */
    private $_uniqueName;
    
    /**
     * Create a new ExcelWorksheet_MemoryDrawing
     */
    public function __construct()
    {
        // Initialise values
        $this->_imageResource     = null;
        $this->_renderingFunction = self::RENDERING_DEFAULT;
        $this->_mimeType          = self::MIMETYPE_DEFAULT;
        $this->_uniqueName        = md5(rand(0, 9999) . time() . rand(0, 9999));
        
        // Initialize parent
        parent::__construct();
    }
    
    /**
     * Get image resource
     *
     * @return resource
     */
    public function getImageResource()
    {
        return $this->_imageResource;
    }
    
    /**
     * Set image resource
     *
     * @param	$value resource
     */
    public function setImageResource($value = null)
    {
        $this->_imageResource = $value;
        
        if (!is_null($this->_imageResource))
        {
            // Get width/height
            $this->_width  = imagesx($this->_imageResource);
            $this->_height = imagesy($this->_imageResource);
        }
    }
    
    /**
     * Get rendering function
     *
     * @return string
     */
    public function getRenderingFunction()
    {
        return $this->_renderingFunction;
    }
    
    /**
     * Set rendering function
     *
     * @param string $value
     */
    public function setRenderingFunction($value = ExcelWorksheet_MemoryDrawing::RENDERING_DEFAULT)
    {
        $this->_renderingFunction = $value;
    }
    
    /**
     * Get mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }
    
    /**
     * Set mime type
     *
     * @param string $value
     */
    public function setMimeType($value = ExcelWorksheet_MemoryDrawing::MIMETYPE_DEFAULT)
    {
        $this->_mimeType = $value;
    }
    
    /**
     * Get indexed filename (using image index)
     *
     * @return string
     */
    public function getIndexedFilename()
    {
        $extension = strtolower($this->getMimeType());
        $extension = explode('/', $extension);
        $extension = $extension[1];
        
        return $this->_uniqueName . $this->getImageIndex() . '.' . $extension;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_renderingFunction . $this->_mimeType . $this->_uniqueName . parent::getHashCode() . __CLASS__);
    }
}

/**
 * ExcelWorksheet_Drawing
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_Drawing extends ExcelWorksheet_BaseDrawing
{
    /**
     * Path
     *
     * @var string
     */
    private $_path;
    
    /**
     * Create a new ExcelWorksheet_Drawing
     */
    public function __construct()
    {
        // Initialise values
        $this->_path = '';
        
        // Initialize parent
        parent::__construct();
    }
    
    /**
     * Get Filename
     *
     * @return string
     */
    public function getFilename()
    {
        return basename($this->_path);
    }
    
    /**
     * Get indexed filename (using image index)
     *
     * @return string
     */
    public function getIndexedFilename()
    {
        return str_replace('.' . $this->getExtension(), '', $this->getFilename()) . $this->getImageIndex() . '.' . $this->getExtension();
    }
    
    /**
     * Get Extension
     *
     * @return string
     */
    public function getExtension()
    {
        $exploded = explode(".", basename($this->_path));
        return $exploded[count($exploded) - 1];
    }
    
    /**
     * Get Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * Set Path
     *
     * @param 	string 		$pValue			File path
     * @param 	boolean		$pVerifyFile	Verify file
     * @throws 	Exception
     */
    public function setPath($pValue = '', $pVerifyFile = true)
    {
        if ($pVerifyFile)
        {
            if (file_exists($pValue))
            {
                $this->_path = $pValue;
                
                if ($this->_width == 0 && $this->_height == 0)
                {
                    // Get width/height
                    list($this->_width, $this->_height) = getimagesize($pValue);
                }
            }
            else
            {
                throw new Exception("File $pValue not found!");
            }
        }
        else
        {
            $this->_path = $pValue;
        }
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5($this->_path . parent::getHashCode() . __CLASS__);
    }
}

/**
 * ExcelWorksheet_Drawing_Shadow
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelWorksheet_Drawing_Shadow extends ComparableBase
{
    /* Shadow alignment */
    const SHADOW_BOTTOM = 'b';
    const SHADOW_BOTTOM_LEFT = 'bl';
    const SHADOW_BOTTOM_RIGHT = 'br';
    const SHADOW_CENTER = 'ctr';
    const SHADOW_LEFT = 'l';
    const SHADOW_TOP = 't';
    const SHADOW_TOP_LEFT = 'tl';
    const SHADOW_TOP_RIGHT = 'tr';
    
    /**
     * Visible
     *
     * @var boolean
     */
    private $_visible;
    
    /**
     * Blur radius
     *
     * Defaults to 6
     *
     * @var int
     */
    private $_blurRadius;
    
    /**
     * Shadow distance
     *
     * Defaults to 2
     *
     * @var int
     */
    private $_distance;
    
    /**
     * Shadow direction (in degrees)
     *
     * @var int
     */
    private $_direction;
    
    /**
     * Shadow alignment
     *
     * @var int
     */
    private $_alignment;
    
    /**
     * Color
     * 
     * @var ExcelStyle_Color
     */
    private $_color;
    
    /**
     * Alpha
     *
     * @var int
     */
    private $_alpha;
    
    /**
     * Create a new ExcelWorksheet_Drawing_Shadow
     */
    public function __construct()
    {
        // Initialise values
        $this->_visible    = false;
        $this->_blurRadius = 6;
        $this->_distance   = 2;
        $this->_direction  = 0;
        $this->_alignment  = ExcelWorksheet_Drawing_Shadow::SHADOW_BOTTOM_RIGHT;
        $this->_color      = new ExcelStyle_Color(ExcelStyle_Color::COLOR_BLACK);
        $this->_alpha      = 50;
    }
    
    /**
     * Get Visible
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->_visible;
    }
    
    /**
     * Set Visible
     *
     * @param boolean $pValue
     */
    public function setVisible($pValue = false)
    {
        $this->_visible = $pValue;
    }
    
    /**
     * Get Blur radius
     *
     * @return int
     */
    public function getBlurRadius()
    {
        return $this->_blurRadius;
    }
    
    /**
     * Set Blur radius
     *
     * @param int $pValue
     */
    public function setBlurRadius($pValue = 6)
    {
        $this->_blurRadius = $pValue;
    }
    
    /**
     * Get Shadow distance
     *
     * @return int
     */
    public function getDistance()
    {
        return $this->_distance;
    }
    
    /**
     * Set Shadow distance
     *
     * @param int $pValue
     */
    public function setDistance($pValue = 2)
    {
        $this->_distance = $pValue;
    }
    
    /**
     * Get Shadow direction (in degrees)
     *
     * @return int
     */
    public function getDirection()
    {
        return $this->_direction;
    }
    
    /**
     * Set Shadow direction (in degrees)
     *
     * @param int $pValue
     */
    public function setDirection($pValue = 0)
    {
        $this->_direction = $pValue;
    }
    
    /**
     * Get Shadow alignment
     *
     * @return int
     */
    public function getAlignment()
    {
        return $this->_alignment;
    }
    
    /**
     * Set Shadow alignment
     *
     * @param int $pValue
     */
    public function setAlignment($pValue = 0)
    {
        $this->_alignment = $pValue;
    }
    
    /**
     * Get Color
     *
     * @return ExcelStyle_Color
     */
    public function getColor()
    {
        return $this->_color;
    }
    
    /**
     * Set Color
     *
     * @param 	ExcelStyle_Color $pValue
     * @throws 	Exception
     */
    public function setColor(ExcelStyle_Color $pValue = null)
    {
        $this->_color = $pValue;
    }
    
    /**
     * Get Alpha
     *
     * @return int
     */
    public function getAlpha()
    {
        return $this->_alpha;
    }
    
    /**
     * Set Alpha
     *
     * @param int $pValue
     */
    public function setAlpha($pValue = 0)
    {
        $this->_alpha = $pValue;
    }
    
    /**
     * Get hash code
     *
     * @return string	Hash code
     */
    public function getHashCode()
    {
        return md5(($this->_visible ? 't' : 'f') . $this->_blurRadius . $this->_distance . $this->_direction . $this->_alignment . $this->_color->getHashCode() . $this->_alpha . __CLASS__);
    }
}
?>