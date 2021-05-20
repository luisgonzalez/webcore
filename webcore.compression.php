<?php
/**
 * @package WebCore
 * @subpackage Compression
 * @version experimental
 * 
 * Provides automatic compression components
 *
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.caching.php";

/**
 * Static helper for compression
 *
 * @package WebCore
 * @subpackage Compression
 */
abstract class HttpCompressor extends HelperBase
{
    const PROVIDER_DOM = "DomCompressor";
    const PROVIDER_RESOURCES = "ResourcesCompressor";
    
    const STORE_CACHEMANAGER = "cacheManager";
    const STORE_FILESYSTEM = "fileSystem";
    
    /**
     * @var CacheManagerBase
     */
    private static $cacheManager;
    private static $storeType;
    private static $cachePath;
    
    public static function getInstance()
    {
        $settings  = Settings::getValue(Settings::SKEY_COMPRESSION);
        $className = $settings[Settings::KEY_COMPRESSION_PROVIDER];
        
        if ($className == self::PROVIDER_DOM)
            return DomCompressor::getInstance();
        else
            return ResourcesCompressor::getInstance();
    }
    
    public static function initializeCompression()
    {
        self::$cachePath = HttpContext::getDocumentRoot() . "cache/";
        
        if (file_exists(self::$cachePath) === false)
            mkdir(self::$cachePath);
        
        $settings = Settings::getValue(Settings::SKEY_COMPRESSION);
        
        if ($settings[Settings::KEY_COMPRESSION_STORE] == self::STORE_CACHEMANAGER)
            self::$cacheManager = CacheManagerBase::fromSettings();
        
        self::$storeType = $settings[Settings::KEY_COMPRESSION_STORE];
    }
    
    /**
     * Validates if exist a hash-cache
     *
     * @param string $hash
     * @return bool
     */
    protected static function containsHash($hash)
    {
        switch (self::$storeType)
        {
            case self::STORE_CACHEMANAGER:
                return self::$cacheManager->contains($hash);
            default:
                return file_exists(self::$cachePath . $hash);
        }
    }
    
    /**
     * Saves hash-cache
     *
     * @param string $hash
     * @param string $content
     * @return bool
     */
    protected static function saveHash($hash, $content)
    {
        switch (self::$storeType)
        {
            case self::STORE_CACHEMANAGER:
                return self::$cacheManager->set($hash, $content);
            default:
                return file_put_contents(self::$cachePath . $hash, utf8_encode($content));
        }
    }
    
    /**
     * Removes a hash-cache
     *
     * @param string $hash
     */
    protected static function removeHash($hash)
    {
        switch (self::$storeType)
        {
            case self::STORE_CACHEMANAGER:
                return false;
            default:
                $pattern = explode("_", $hash);
                $pattern = self::$cachePath . $pattern[0] . "*";
                
                foreach (glob($pattern) as $filename)
                    unlink($filename);
        }
    }
    
    /**
     * Gets URL for request a hash-cache
     *
     * @param string $hash
     * @param string $ext
     * @return string
     */
    protected static function getUrlHash($hash, $ext)
    {
        $ext = substr($ext, 1);
        
        switch (self::$storeType)
        {
            case self::STORE_CACHEMANAGER:
                return getenv("PHP_SELF") . "?_resource_hash=" . $hash . "&_resource_ext=" . $ext;
            default:
                return getenv("PHP_SELF") . "?_cache_hash=" . $hash . "&_resource_ext=" . $ext;
        }
    }
    
    /**
     * Fix CSS relative URLs
     *
     * @todo Fix this, the relative URL aren't fixed
     * @param string $content
     * @param string $path
     * @return string
     */
    protected static function fixCSSUrl($content, $path)
    {
        preg_match_all("/url\((.*?)\)/is", $content, $matches);
        
        if (count($matches[1]) == 0)
            return $content;
        
        foreach ($matches[1] as $file)
        {
            $originalFile = trim($file);
            $file         = preg_replace("/'|\"/", "", $originalFile);
            
            if (substr($file, 0, 1) != "/" && substr($file, 0, 5) != "http:")
            {
                $newPath = $path . "/" . $file;
                $content = str_replace($originalFile, $newPath, $content);
            }
        }
        
        return $content;
    }
    
    /**
     * Minifies normal text
     *
     * @param string $txt
     * @return string
     */
    protected static function minifyText($txt)
    {
        $txt = preg_replace('/\s+/', ' ', $txt);
        $txt = preg_replace('/\/\*.*?\*\//', '', $txt);
        
        return $txt;
    }
    
    /**
     * Compress output to GZIP
     *
     * @param string $output
     * @return string
     */
    public static function compressOutput($output)
    {
        if (strlen($output) >= 1000)
        {
            $compressed_out = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $compressed_out .= substr(gzcompress($output, 2), 0, -4);
            $encoding = "gzip";
            
            if (strstr(HttpContextInfo::getInstance()->getClientAcceptEncoding(), "x-gzip"))
                $encoding = "x-gzip";
            
            header("Content-Encoding: " . $encoding);
            return $compressed_out;
        }
        
        return $output;
    }
    
    /**
     * Compress a resource managed
     *
     */
    public static function compressResource()
    {
        if (strstr(HttpContextInfo::getInstance()->getClientAcceptEncoding(), "gzip"))
        {
            if (function_exists("gzcompress"))
                ob_start("HttpCompressor::compressOutput");
            else
                ob_start("ob_gzhandler");
        }
        
        $offset = 6000000 * 60;
        header("Cache-Control: must-revalidate");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
    }
}

/**
 * Singleton to compress resources
 *
 * @package WebCore
 * @subpackage Compression
 */
class ResourcesCompressor extends HttpCompressor implements ISingleton
{
    /**
     * Gets current instance
     *
     * @return ResourcesCompressor
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
        {
            self::$__instance = new ResourcesCompressor();
            self::initialize();
        }
        
        return self::$__instance;
    }
    
    /**
     * Initializes compressor
     *
     */
    public static function initialize()
    {
        parent::initializeCompression();
    }
    
    /**
     * Compresses resouces
     *
     * @param array $files
     * @param string $blockContent
     * @param string $ext
     * 
     * @return string
     */
    public static function compressResources($files, $blockContent, $ext)
    {
        $tagCollection = new KeyedCollection();
        $content       = "";
        
        foreach ($files as $file)
        {
            $originalFile = $file;
            $file         = HttpContext::getServerRoot() . $file;
            
            if (strpos($file, $ext) > 0 && file_exists($file))
            {
                $tagCollection->setValue($file, $fileDate);
                $fileContent = file_get_contents($file);
                
                if ($ext == ".css")
                    $fileContent = self::fixCSSUrl($fileContent, dirname($originalFile));
                
                $content .= $fileContent . "\n\r";
            }
        }
        
        $content .= $blockContent;
        
        $hash = implode("-", $tagCollection->getKeys());
        $hash = HashProvider::getHash($tagCollection->implode(), HashProvider::ALGORITHM_MD5);
        
        if (self::containsHash($hash) == false)
        {
            if ($ext == ".js")
                $content = JSMin::minify($content);
            else
                $content = self::minifyText($content);
            
            self::removeHash($hash);
            self::saveHash($hash, $content);
        }
        
        return self::getUrlHash($hash, $ext);
    }
    
    /**
     * Represents the instance of the singleton object
     *
     * @var HttpCompressor
     */
    private static $__instance = null;
    
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

/**
 * Singleton to compress static HTML and resources
 *
 * @package WebCore
 * @subpackage Compression
 */
class DomCompressor extends HttpCompressor implements ISingleton
{
    private static $stopBuffer;
    
    /**
     * Gets current instance
     *
     * @return HttpCompressor
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
        {
            self::$__instance = new HttpCompressor();
            self::initialize();
        }
        
        self::$stopBuffer = false;
        
        return self::$__instance;
    }
    
    /**
     * Compress current page, if it's complete HTML
     *
     * @param string $output
     * @return string
     */
    public static function compressPage($output)
    {
        if (self::$stopBuffer === true)
            return $output;
        
        if (preg_match('/<html.*?>/i', $output))
        {
            $doc = new DOMDocument();
            $doc->loadHTML($output);
            
            self::parseTag($doc, "script", "src", ".js");
            self::parseTag($doc, "link", "href", ".css");
            
            $output = HttpCompressor::minifyText($doc->saveHTML());
        }
        else
        {
            $output = HttpCompressor::minifyText($output);
        }
        
        if (strstr(HttpContextInfo::getInstance()->getClientAcceptEncoding(), "gzip"))
        {
            if (function_exists("gzcompress"))
                $output = self::compressOutput($output);
        }
        
        return $output;
    }
    
    /**
     * Stops compression
     *
     */
    public static function stopCompression()
    {
        self::$stopBuffer = true;
    }
    
    /**
     * Initializes compressor
     *
     */
    public static function initialize()
    {
        parent::initializeCompression();
        ob_start("DomCompressor::compressPage");
    }
    
    /**
     * Parses tags in DOMDoc
     *
     * @param DOMDocument $doc
     * @param string $tagName
     * @param string $attribute
     * @param string $ext
     * 
     * @return bool
     */
    private static function parseTag(&$doc, $tagName, $attribute, $ext)
    {
        chdir(dirname($_SERVER['SCRIPT_FILENAME']));
        
        $content = "";
        $html    = $doc->documentElement;
        
        $tagCollection = new KeyedCollection();
        $tags          = $html->getElementsByTagName($tagName);
        
        for ($i = 0; $i < $tags->length; $i++)
        {
            if ($tags->item($i)->hasAttribute($attribute))
            {
                $file = $tags->item($i)->attributes->getNamedItem($attribute)->nodeValue;
                
                if ($file[0] == "/")
                    $file = substr($file, 1);
                
                $file = HttpContext::getDocumentRoot() . $file;
                
                if (strpos($file, $ext) > 0 && file_exists($file))
                {
                    $fileDate = filemtime($file);
                    $tagCollection->setValue($file, $fileDate);
                    $parent = $tags->item($i)->parentNode;
                    $parent->removeChild($tags->item($i));
                    $i--;
                    
                    $content .= file_get_contents($file) . "\n\r";
                }
            }
            else
            {
                $content .= $tags->item($i)->nodeValue;
                $parent = $tags->item($i)->parentNode;
                $parent->removeChild($tags->item($i));
                $i--;
            }
        }
        
        if ($tagCollection->isEmpty())
            return false;
        
        $hash = implode("-", $tagCollection->getKeys());
        $hash = HashProvider::getHash($tagCollection->implode(), HashProvider::ALGORITHM_MD5);
        
        if (!self::containsHash($hash))
        {
            if ($tagName == "script")
                $content = JSMin::minify($content);
            else
                $content = self::minifyText($content);
            
            self::removeHash($hash);
            self::saveHash($hash, $content);
        }
        
        $head           = $html->getElementsByTagName("head")->item(0);
        $title          = $head->childNodes->item(0);
        $newTag         = $doc->createElement($tagName);
        $att            = $doc->createAttribute($attribute);
        $att->nodeValue = self::getUrlHash($hash, $ext);
        $newTag->appendChild($att);
        
        if ($tagName == "link")
        {
            $attRel            = $doc->createAttribute("rel");
            $attRel->nodeValue = "stylesheet";
            $newTag->appendChild($attRel);
        }
        
        $head->insertBefore($newTag, $title);
        
        return true;
    }
    
    /**
     * Represents the instance of the singleton object
     *
     * @var HttpCompressor
     */
    private static $__instance = null;
    
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

/**
 * JavaScript Minifier.
 * Minify JavaScript code removing innecesary spaces and vars names.
 *
 * @package WebCore
 * @subpackage Compression
 */
class JSMin extends HelperBase
{
    const ORD_LF = 10;
    const ORD_SPACE = 32;
    
    protected $a = '';
    protected $b = '';
    protected $input = '';
    protected $inputIndex = 0;
    protected $inputLength = 0;
    protected $lookAhead = null;
    protected $output = '';
    
    public static function minify($js)
    {
        $jsmin = new JSMin($js);
        return $jsmin->min();
    }
    
    public function __construct($input)
    {
        $this->input       = str_replace("\r\n", "\n", $input);
        $this->inputLength = strlen($this->input);
    }
    
    protected function action($d)
    {
        switch ($d)
        {
            case 1:
                $this->output .= $this->a;
            
            case 2:
                $this->a = $this->b;
                
                if ($this->a === "'" || $this->a === '"')
                {
                    for (; ; )
                    {
                        $this->output .= $this->a;
                        $this->a = $this->get();
                        
                        if ($this->a === $this->b)
                            break;
                        
                        if (ord($this->a) <= self::ORD_LF)
                            throw new Exception('Unterminated string literal.');
                        
                        if ($this->a === '\\')
                        {
                            $this->output .= $this->a;
                            $this->a = $this->get();
                        }
                    }
                }
            case 3:
                $this->b = $this->next();
                
                if ($this->b === '/' && ($this->a === '(' || $this->a === ',' || $this->a === '=' || $this->a === ':' || $this->a === '[' || $this->a === '!' || $this->a === '&' || $this->a === '|' || $this->a === '?'))
                {
                    $this->output .= $this->a . $this->b;
                    
                    for (; ; )
                    {
                        $this->a = $this->get();
                        
                        if ($this->a === '/')
                            break;
                        
                        if ($this->a === '\\')
                        {
                            $this->output .= $this->a;
                            $this->a = $this->get();
                        }
                        elseif (ord($this->a) <= self::ORD_LF)
                            throw new Exception('Unterminated regular expression literal.');
                        
                        $this->output .= $this->a;
                    }
                    
                    $this->b = $this->next();
                }
        }
    }
    
    protected function get()
    {
        $c               = $this->lookAhead;
        $this->lookAhead = null;
        
        if (is_null($c))
        {
            if ($this->inputIndex < $this->inputLength)
            {
                $c = $this->input[$this->inputIndex];
                $this->inputIndex += 1;
            }
            else
                $c = null;
        }
        
        if ($c === "\r")
            return "\n";
        if (is_null($c) || $c === "\n" || ord($c) >= self::ORD_SPACE)
            return $c;
        
        return ' ';
    }
    
    /**
     * Validates a Alphanumeric char
     *
     * @param string $c
     * @return bool
     */
    protected function isAlphaNum($c)
    {
        return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
    }
    
    /**
     * Performs Minify
     *
     * @return string
     */
    protected function min()
    {
        $this->a = "\n";
        $this->action(3);
        
        while ($this->a !== null)
        {
            switch ($this->a)
            {
                case ' ':
                    if ($this->isAlphaNum($this->b))
                    {
                        $this->action(1);
                    }
                    else
                    {
                        $this->action(2);
                    }
                    break;
                
                case "\n":
                    switch ($this->b)
                    {
                        case '{':
                        case '[':
                        case '(':
                        case '+':
                        case '-':
                            $this->action(1);
                            break;
                        
                        case ' ':
                            $this->action(3);
                            break;
                        
                        default:
                            if ($this->isAlphaNum($this->b))
                            {
                                $this->action(1);
                            }
                            else
                            {
                                $this->action(2);
                            }
                    }
                    break;
                
                default:
                    switch ($this->b)
                    {
                        case ' ':
                            if ($this->isAlphaNum($this->a))
                            {
                                $this->action(1);
                                break;
                            }
                            
                            $this->action(3);
                            break;
                        
                        case "\n":
                            switch ($this->a)
                            {
                                case '}':
                                case ']':
                                case ')':
                                case '+':
                                case '-':
                                case '"':
                                case "'":
                                    $this->action(1);
                                    break;
                                
                                default:
                                    if ($this->isAlphaNum($this->a))
                                    {
                                        $this->action(1);
                                    }
                                    else
                                    {
                                        $this->action(3);
                                    }
                            }
                            break;
                        
                        default:
                            $this->action(1);
                            break;
                    }
            }
        }
        
        return $this->output;
    }
    
    /**
     * Moves string pointer to next char
     *
     */
    protected function next()
    {
        $c = $this->get();
        
        if ($c === '/')
        {
            switch ($this->peek())
            {
                case '/':
                    for (; ; )
                    {
                        $c = $this->get();
                        
                        if (ord($c) <= self::ORD_LF)
                            return $c;
                    }
                
                case '*':
                    $this->get();
                    
                    for (; ; )
                    {
                        switch ($this->get())
                        {
                            case '*':
                                if ($this->peek() === '/')
                                {
                                    $this->get();
                                    return ' ';
                                }
                                break;
                            
                            case null:
                                throw new Exception('Unterminated comment.');
                        }
                    }
                
                default:
                    return $c;
            }
        }
        
        return $c;
    }
    
    /**
     * Peeks string
     *
     * @return int
     */
    protected function peek()
    {
        $this->lookAhead = $this->get();
        
        return $this->lookAhead;
    }
}
?>