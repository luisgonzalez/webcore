<?php
require_once "webcore.application.php";

/**
 * Represents Google Maps Settings
 *
 * @package WebCore
 * @subpackage Google
 */
class GMapsSettings extends HelperBase implements IExtensionCollection
{
    const KEY_GMAP = 'gmap';
    const KEY_GMAP_APIKEY = 'apiKey';
    
    public static function loadExtensionCollection()
    {
        if (Settings::hasValue(GMapsSettings::KEY_GMAP) == false)
        {
            $defaultsValues                                 = array();
            $defaultsValues[GMapsSettings::KEY_GMAP_APIKEY] = 'ABQIAAAAIJm9P1i0h82w1RPtKVFPKBSt5GJKor4SH1SC2F0FS-5GZBmYqRTOzU7pPMMT6vWdTqUr03a4MBHJHw';
            
            Settings::setValue(GMapsSettings::KEY_GMAP, $defaultsValues);
            Settings::save();
        }
    }
}

/**
 * Represents Google Maps Resources
 * 
 * @package WebCore
 * @subpackage Google
 */
class GMapsResources extends HelperBase implements IExtensionCollection
{
    const SRK_GMAP_MAXIMIZE = 'gmap.maximize';
    const SRK_GMAP_RESTORE = 'gmap.restore';
    const SRK_GMAP_MAPTYPE = 'gmap.maptype';
    const SRK_GMAP_MAPNORMAL = 'gmap.mapnormal';
    const SRK_GMAP_MAPSATELLITE = 'gmap.mapsattelite';
    const SRK_GMAP_MAPHYBRID = 'gmap.maphybrid';
    
    public static function loadExtensionCollection()
    {
        if (Resources::hasValue(GMapsResources::SRK_GMAP_MAPTYPE) == false)
        {
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAXIMIZE, 'Maximize', CultureInfo::CULTURE_ESMX);
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAXIMIZE, 'Maximizar', CultureInfo::CULTURE_ENUS);
            
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_RESTORE, 'Restore', CultureInfo::CULTURE_ESMX);
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_RESTORE, 'Restaurar', CultureInfo::CULTURE_ENUS);
            
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPTYPE, 'Tipo de mapa', CultureInfo::CULTURE_ESMX);
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPTYPE, 'Map type', CultureInfo::CULTURE_ENUS);
            
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPNORMAL, 'Normal', CultureInfo::CULTURE_ESMX);
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPNORMAL, 'Normal', CultureInfo::CULTURE_ENUS);
            
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPSATELLITE, 'Satelital', CultureInfo::CULTURE_ESMX);
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPSATELLITE, 'Satellite', CultureInfo::CULTURE_ENUS);
            
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPHYBRID, 'Híbrido', CultureInfo::CULTURE_ESMX);
            Resources::setValueByCulture(GMapsResources::SRK_GMAP_MAPHYBRID, 'Hybrid', CultureInfo::CULTURE_ENUS);
            Resources::save();
        }
    }
}

/**
 * Represents a helper to use Google Geocoding API
 *
 * @package WebCore
 * @subpackage Google
 */
class GGeocodeHelper extends HelperBase
{
    static $defaultUrl = 'http://maps.google.com/maps/geo?';
    
    /**
     * Gets location from an address query
     *
     * @param string $query
     * @param string $output
     * @param string $gl
     *
     * @return string
     */
    public static function getLocation($query, $output = 'json', $gl = 'MX')
    {
        GMapsSettings::loadExtensionCollection();
        $settings = Settings::getValue(GMapsSettings::KEY_GMAP);
        
        $url = self::$defaultUrl;
        $url .= 'q=' . urlencode($query);
        $url .= '&key=' . $settings[GMapsSettings::KEY_GMAP_APIKEY];
        $url .= '&sensor=false&output=' . $output;
        $url .= '&gl=' . $gl;
        
        $data = file_get_contents($url);
        
        return $data;
    }
    
    /**
     * Gets latitude and longitude from am address query
     *
     * @param string $query
     * @param string $gl
     *
     * @return array
     */
    public static function getCoordinates($query, $gl = 'MX')
    {
        $data = self::getLocation($query, 'csv', $gl);
        $data = explode(",", $data);
        
        if ($data[0] == 200)
            return array(
                'latitude' => $data[2],
                'longitude' => $data[3]
            );
        
        return null;
    }
}

/**
 * Represents a common control in a Google Map
 *
 * @package WebCore
 * @subpackage Google
 */
abstract class GMapsControlBase extends ControlModelBase
{
    protected $latitude;
    protected $longitude;
    protected $caption;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, true);
        
        $this->caption   = $caption;
        $this->latitude  = 20.72493;
        $this->longitude = -103.390289;
    }
    
    /**
     * Gets caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    public function setCaption($value)
    {
        $this->caption = $value;
    }
    
    /**
     * Gets latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    public function setLatitude($value)
    {
        $this->latitude = $value;
    }
    
    /**
     * Gets longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    
    public function setLongitude($value)
    {
        $this->longitude = $value;
    }
}

/**
 * Represents a marker in a Google Map
 *
 * @package WebCore
 * @subpackage Google
 */
class GMarker extends GMapsControlBase
{
    protected $isDraggable;
    protected $key;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $key
     * @param string $caption
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct($name, $key, $caption = '', $latitude = 0, $longitude = 0)
    {
        parent::__construct($name, $caption);
        
        $this->key         = $key;
        $this->latitude    = $latitude;
        $this->longitude   = $longitude;
        $this->isDraggable = false;
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * Gets if it's draggable
     *
     * @return bool
     */
    public function getIsDraggable()
    {
        return $this->isDraggable;
    }
    
    /**
     * Sets if it's draggable
     *
     * @param bool $value
     */
    public function setIsDraggable($value)
    {
        $this->isDraggable = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return GMarker
     */
    public static function createInstance()
    {
        return new GMarker('ISerializable', 'ISerializable', 0, 0);
    }
}

/**
 * Represents a marker with a radius in a Google Map
 *
 * @package WebCore
 * @subpackage Google
 */
class GMarkerRadius extends GMarker
{
    protected $radius;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $key
     * @param string $caption
     * @param float $latitude
     * @param float $longitude
     * @param int $radius
     */
    public function __construct($name, $key, $caption = '', $latitude = 0, $longitude = 0, $radius = 10)
    {
        parent::__construct($name, $key, $caption, $latitude, $longitude);
        
        $this->radius = $radius;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ControlModelCollection
     */
    public static function createInstance()
    {
        return new GMarkerRadius('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a Google Map panel
 *
 * @todo Doc this
 * @package WebCore
 * @subpackage Google
 */
class GMap extends GMapsControlBase implements IRootModel
{
    private $mapType;
    /**
     * @var IndexedCollection
     */
    private $markers;
    private $displayToolbar;
    /**
     * @var IndexedCollection
     */
    private $persistors;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param boolean $displayToolbar
     */
    public function __construct($name, $caption, $displayToolbar = true)
    {
        parent::__construct($name, $caption);
        
        $this->markers        = new IndexedCollection();
        $this->persistors     = new IndexedCollection();
        $this->displayToolbar = $displayToolbar;
    }
    
    public function setDisplayToolbar($value)
    {
        $this->displayToolbar = $value;
    }
    
    public function getDisplayToolbar()
    {
        return $this->displayToolbar;
    }
    
    public function addPersistor($persistor)
    {
        $this->persistors->addItem($persistor);
    }
    
    public function getPersistors()
    {
        return $this->persistors;
    }
    
    public function addMarker($marker)
    {
        $this->markers->addItem($marker);
    }
    
    public function getMarkers()
    {
        return $this->markers;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ControlModelCollection
     */
    public static function createInstance()
    {
        return new GMap();
    }
}

/**
 * Represents a Google Map view
 *
 * @package WebCore
 * @subpackage Google
 */
class GMapView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class based on a Google Map model
     *
     * @param GMap $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'mapview';
        $this->frameWidth     = "auto";
        $this->isAsynchronous = true;
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        GMapsResources::loadExtensionCollection();
        
        // Setup the callbacks for each renderable model
        $callbacks['GMap']          = array(
            'GMapRenderCallbacks',
            'renderGMap'
        );
        $callbacks['GMarker']       = array(
            'GMapRenderCallbacks',
            'renderGMarker'
        );
        $callbacks['GMarkerRadius'] = array(
            'GMapRenderCallbacks',
            'renderGMarker'
        );
        
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     * @todo Export Tags to JScript
     */
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        
        $formviewPath = HttpContext::getLibraryRoot() . 'ext/google/google.gmapview.js';
        $cssPath      = HttpContext::getLibraryRoot() . 'ext/google/google.gmapview.css';
        
        GMapsSettings::loadExtensionCollection();
        $settings = Settings::getValue(GMapsSettings::KEY_GMAP);
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlMapView.Js', $formviewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlMapView.Css', $cssPath);
        
        $gmapsPath = 'http://www.google.com/jsapi?key=' . $settings[GMapsSettings::KEY_GMAP_APIKEY];
        $lang      = Resources::getCulture();
        $lang      = explode("-", $lang);
        $gmapsCode = 'google.load("maps", "2.x", {"language" : "' . $lang[0] . '"});';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'GMap.Js', $gmapsPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_BLOCK, __CLASS__, 'GMapInvoke', $gmapsCode);
    }
}

/**
 * Represents a Google Static Map view
 *
 * @package WebCore
 * @subpackage Google
 */
class GStaticMapView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class based on a Google Map model
     *
     * @param GMap $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'mapview';
        $this->frameWidth     = "auto";
        $this->isAsynchronous = true;
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        GMapsResources::loadExtensionCollection();
        
        // Setup the callbacks for each renderable model
        $callbacks['GMap'] = array(
            'GMapRenderCallbacks',
            'renderGStaticMap'
        );
        
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     */
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
    }
}

/**
 * Contains static callback methods to render Google Maps
 *
 * @package WebCore
 * @subpackage Google
 */
class GMapRenderCallbacks extends HtmlRenderCallbacks
{
    /**
     * Renders the marker
     *
     * @param GMap $model
     * @param GMapView $view
     */
    public static function renderGMarker(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        if ($model->getIsDraggable())
        {
            $tw->openInput();
            $tw->addAttribute('type', 'hidden');
            $tw->addAttribute('id', $model->getKey());
            $tw->addAttribute('name', $model->getKey());
            $tw->addAttribute('value', '1');
            $tw->closeInput();
        }
        
        $tw->openDiv();
        $tw->addAttribute('key', $model->getKey());
        $tw->addAttribute('latitude', $model->getLatitude());
        $tw->addAttribute('longitude', $model->getLongitude());
        $tw->addAttribute('isDraggable', $model->getIsDraggable());
        
        if (ObjectIntrospector::isA($model, 'GMarkerRadius'))
            $tw->addAttribute('radius', $model->getRadius());
        
        $tw->addAttribute('class', $view->getCssClass() . '-marker');
        $tw->writeRaw($model->getCaption());
        $tw->closeDiv();
    }
    
    /**
     * Renders the main control
     *
     * @param GMap $model
     * @param GMapView $view
     */
    public static function renderGMap(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openForm();
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('class', $view->getCssClass());
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();
        
        if ($model->getDisplayToolbar() == true)
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar');
            $tw->addAttribute('style', 'border-bottom-style:none;');
            
            $tw->openTable();
            $tw->addAttribute('summary', '');
            $tw->openTr();
            
            $tw->openTd();
            $tw->addAttribute('class', $view->getCssClass() . '-toolbar-type');
            $tw->writeContent(Resources::getValue(GMapsResources::SRK_GMAP_MAPTYPE) . ': ');
            $tw->openSelect();
            $tw->addAttribute('class', $view->getCssClass() . '-selector');
            $tw->openOption();
            $tw->addAttribute('value', 'G_NORMAL_MAP');
            $tw->writeContent(Resources::getValue(GMapsResources::SRK_GMAP_MAPNORMAL));
            $tw->closeOption();
            $tw->openOption();
            $tw->addAttribute('value', 'G_SATELLITE_MAP');
            $tw->writeContent(Resources::getValue(GMapsResources::SRK_GMAP_MAPSATELLITE));
            $tw->closeOption();
            $tw->openOption();
            $tw->addAttribute('value', 'G_HYBRID_MAP');
            $tw->writeContent(Resources::getValue(GMapsResources::SRK_GMAP_MAPHYBRID));
            $tw->closeOption();
            $tw->closeSelect();
            
            $tw->openA();
            $tw->addAttribute('href', 'javascript:void(0);');
            $tw->addAttribute('class', $view->getCssClass() . '-maximize');
            $tw->writeContent('Maximizar');
            $tw->closeA();
            
            $tw->closeTd();
            
            $tw->closeTr();
            $tw->closeTable();
            
            $tw->closeDiv();
        }
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_content');
        $tw->addAttribute('class', $view->getCssClass() . '-content');
        
        $tw->openDiv();
        $tw->addAttribute('id', $model->getName() . '_map');
        $tw->addAttribute('latitude', $model->getLatitude());
        $tw->addAttribute('longitude', $model->getLongitude());
        $tw->addAttribute('class', $view->getCssClass() . '-map');
        $tw->writeContent('');
        $tw->closeDiv();
        
        foreach ($model->getMarkers() as $marker)
            self::renderGMarker($marker, $view);
        
        foreach ($model->getPersistors() as $persistor)
        {
            $tw->openInput();
            $tw->addAttribute('id', HtmlViewBase::getHtmlId($persistor));
            $tw->addAttribute('name', $persistor->getName());
            $tw->addAttribute('type', 'hidden');
            $tw->addAttribute('value', $persistor->getValue());
            $tw->closeInput();
        }
        
        self::renderPostBackFlag($model, $view);
        $tw->closeDiv();
        
        $tw->closeForm();
        
        $javascript = "var gMapView_" . $model->getName() . " = null;
                    window.addEvent('domready', function () { gMapView_" . $model->getName() . " = new GMapView('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', true); });";
        self::renderInitializationScript($javascript);
    }
    
    /**
     * Renders the main static control
     *
     * @param GMap $model
     * @param GStaticMapView $view
     */
    public static function renderGStaticMap(&$model, &$view)
    {
        GMapsSettings::loadExtensionCollection();
        $settings = Settings::getValue(GMapsSettings::KEY_GMAP);
        
        $url = "http://maps.google.com/staticmap";
        $url .= "?center=" . $model->getLatitude() . "," . $model->getLongitude();
        $url .= "&zoom=13&size=480x480&maptype=mobile&markers=";
        
        foreach ($model->getMarkers() as $marker)
            $url .= $marker->getLatitude() . "," . $marker->getLongitude() . "|";
        
        $url = substr($url, 0, -1);
        $url .= "&key=" . $settings[GMapsSettings::KEY_GMAP_APIKEY] . "&sensor=false";
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();
        
        $tw->openImg();
        $tw->addAttribute('src', $url);
        $tw->addAttribute('alt', 'GMap');
        $tw->closeImg();
    }
}
?>