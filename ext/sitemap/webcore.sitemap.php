<?php
// @todo Promote this to Core

/**
 * Web site helper
 *
 * @package WebCore
 * @subpackage Web
 */
class WebSiteHelper extends HelperBase
{
    const DEFAULTFILENAME = 'website.settings';
    
    /**
     * Makes pings to search providers
     *
     * @param string $sitemapUrl
     */
    public static function pingSitemap($sitemapUrl)
    {
        $pingServers = array(
            'http://webmaster.live.com/webmaster/ping.aspx?siteMap=%s',
            'http://www.google.com/webmasters/tools/ping?sitemap=%s',
            'http://submissions.ask.com/ping?sitemap=%s',
            'http://www.bing.com/webmaster/ping.aspx?siteMap=%s'
        );
        
        foreach ($pingServers as $pingServer)
        {
            file_get_contents(sprintf($pingServer, $sitemapUrl));
        }
    }
    
    public static function parseFilesystem()
    {
        // @todo Verify all filesystem and then kaboom
    }
    
    /**
     * Generates a sitemap using the settings file
     *
     */
    public static function renderSitemap()
    {
        if (is_null(self::$internalCollection))
            self::load();
        
        $siteMap = SitemapGenerator::getInstance();
        
        foreach (self::$internalCollection as $siteResource)
        {
            $siteMap->addStaticUrl($siteResource->Url, $siteResource->Frecuency);
        }
        
        $siteMap->render();
    }
    
    /**
     * Generetes a site menu using the settings file
     *
     * @param string $name
     * @return SiteMenu
     */
    public static function generateSiteMenu($name)
    {
        if (is_null(self::$internalCollection))
            self::load();
        
        $mainMenu = new SiteMenu($name);
        
        foreach (self::$internalCollection as $siteResource)
        {
            $mainMenu->addMenuItem($siteResource->Caption, $siteResource->Url);
        }
        
        return $mainMenu;
    }
    
    /**
     * Generetes a site menu using the settings file
     *
     * @todo Complete function
     * @param string $name
     * @return Breadcrumb
     */
    public static function generateBreadCrumb($url)
    {
        if (is_null(self::$internalCollection))
            self::load();
        
        return new Breadcrumb();
    }
    
    /**
     * Represents the internal collection
     *
     * @var KeyedCollectionWrapper
     */
    private static $internalCollection = null;
    
    /**
     * Loads the settings from from the given file.
     * If the file is not found, a defualt settings file is created
     *
     * @param string $storeName If not specified, the DEFAULTFILENAME constant is used instead
     */
    public static function load($storeName = '')
    {
        $data = '';
        
        if ($storeName === '')
        {
            if (file_exists(HttpContext::getDocumentRoot() . self::DEFAULTFILENAME) === false)
                self::createDefault(HttpContext::getDocumentRoot() . self::DEFAULTFILENAME);
            $data = file_get_contents(HttpContext::getDocumentRoot() . self::DEFAULTFILENAME);
        }
        else
        {
            if (file_exists($storeName) === false)
                self::createDefault($storeName);
            $data = file_get_contents($storeName);
        }
        
        self::$internalCollection = XmlSerializer::deserialize($data, 'KeyedCollectionWrapper');
    }
    
    /**
     * Saves the changes to the settings
     * If a store name is not specified, it will use the default.
     *
     * @param string $storeName The filename to save the setting file to.
     */
    public static function save($storeName = '')
    {
        if (self::$internalCollection == null)
            self::load($storeName);
        
        if ($storeName === '')
            $fileName = HttpContext::getDocumentRoot() . self::DEFAULTFILENAME;
        else
            $fileName = $storeName;
        
        $data = XmlSerializer::serialize(self::$internalCollection);
        file_put_contents($fileName, $data);
    }
    
    /**
     * Creates a default settings file
     *
     * @param string $fileName The filename the settings will be written to.
     */
    private static function createDefault($fileName)
    {
        $arr              = array();
        $arr['index.php'] = new WebSiteResource('index.php', 'Home');
        
        self::$internalCollection = new KeyedCollectionWrapper($arr, false);
        self::save($fileName);
    }
}

/**
 * Represents a web site item
 *
 * @package WebCore
 * @subpackage Web
 */
class WebSiteResource extends SerializableObjectBase
{
    const RESOURCE_WEBPAGE = 'webPage';
    const RESOURCE_RSS = 'rss';
    const RESOURCE_RESTSERVICE = 'restService';
    const RESOURCE_SOAPSERVICE = 'soapService';
    
    public $Url;
    public $Title;
    public $Description;
    public $ParentUrl;
    public $Frecuency;
    public $Type;
    public $Permissions;
    
    /**
     * Create an instance of this class
     *
     * @param string $url
     * @param string $title
     * @param string $description
     */
    public function __construct($url, $title, $description = '')
    {
        $this->Url         = $url;
        $this->Title       = $title;
        $this->Description = $description;
        $this->Frecuency   = 'daily';
        $this->ParentUrl   = '';
        $this->Type        = self::RESOURCE_WEBPAGE;
        $this->Permissions = array();
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return WebSiteResource
     */
    public static function createInstance()
    {
        return new WebSiteResource('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a Sitemap item
 *
 * @package WebCore
 * @subpackage Web
 */
class SitemapItem extends ObjectBase
{
    public $Location;
    public $Frecuency;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $location
     * @param string $changeFreq
     */
    public function __construct($location, $changeFreq = 'daily')
    {
        $this->Location  = $location;
        $this->Frecuency = $changeFreq;
    }
}

/**
 * Sitemap file generator
 *
 * @package WebCore
 * @subpackage Web
 */
class SitemapGenerator extends HelperBase implements ISingleton
{
    /**
     * Represents the instance of the singleton object
     *
     * @var SitemapGenerator
     */
    private static $__instance = null;
    /**
     * @var Stack
     */
    private static $stack = null;
    
    protected function __construct()
    {
        self::$stack = new Stack();
    }
    
    /**
     * Adds a static url
     *
     * @param string $url
     * @param string $frecuency
     */
    public static function addStaticUrl($url, $frecuency = 'daily')
    {
        if (self::isLoaded())
        {
            self::$stack->push(new SitemapItem($url, $frecuency));
        }
    }
    
    public static function addRangeUrl($url, $min, $max, $frecuency = 'daily')
    {
        if (self::isLoaded())
        {
            for ($i = $min; $i <= $max; $i++)
            {
                $newUrl = sprintf($url, $i);
                self::$stack->push(new SitemapItem($newUrl, $frecuency));
            }
        }
    }
    
    public static function addVariableUrl($url, $data, $frecuency = 'daily')
    {
        if (self::isLoaded())
        {
            foreach ($data as $value)
            {
                $newUrl = sprintf($url, $value);
                self::$stack->push(new SitemapItem($newUrl, $frecuency));
            }
        }
    }
    
    /**
     * Renders sitemap file
     *
     */
    public static function render()
    {
        header('Content-type: text/xml; charset=utf-8');
        
        echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\r\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";
        
        foreach (self::$stack as $item)
        {
            echo "<url><loc>" . $item->Location . "</loc><changefreq>" . $item->Frecuency . "</changefreq></url>" . "\r\n";
        }
        
        echo '</urlset>';
    }
    
    /**
     * Gets current instance
     *
     * @return SitemapGenerator
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new SitemapGenerator();
        
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton's instance is currently loaded
     *
     * @return bool
     */
    public static function isLoaded()
    {
        return (is_null(self::$__instance) === false);
    }
}
?>