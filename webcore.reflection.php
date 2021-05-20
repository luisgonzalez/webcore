<?php
require_once 'webcore.serialization.php';
require_once 'webcore.collections.php';
require_once 'webcore.web.php';

/**
 * Provides a static class helper for introspection.
 *
 * @package WebCore
 * @subpackage Reflection
 */
class ObjectIntrospector extends HelperBase
{
    /**
     * Returns true if object is implementing an interface
     *
     * @param mixed $object
     * @param string $interfaceName
     * @return bool
     */
    public static function isImplementing($object, $interfaceName)
    {
        if (is_object($object) === false)
            return false;
        
        return in_array($interfaceName, class_implements($object));
    }
    
    /**
     * Returns true if object is extending an interface
     *
     * @param mixed $object
     * @param string $className
     * @return bool
     */
    public static function isExtending($object, $className)
    {
        if (is_object($object) === false)
            return false;
        
        return in_array($className, class_parents($object));
    }
    
    /**
     * Returns true if object is a kind of class
     *
     * @param mixed $object
     * @param string $className
     * @return bool
     */
    public static function isClass($object, $className)
    {
        if (is_object($object) === false)
            return false;
        
        return (get_class($object) == $className);
    }
    
    /**
     * Checks if the object is of this class or has this class as one of its parents
     *
     * @param mixed $object
     * @param string $typeName
     * @return bool
     */
    public static function isA($object, $typeName)
    {
        if (is_object($object) === false)
            return false;
        
        // if it is using version 5.3 or better.
        if (version_compare(phpversion(), '5.3.0') === 1)
            return is_a($object, $typeName);
        else
            return (self::isClass($object, $typeName) || self::isExtending($object, $typeName) || self::isImplementing($object, $typeName));
    }
}

/**
 * Provides a static class loader.
 * Make a search of one class across all WebCore files.
 * @todo add the include paths to the classcache scanner
 * @package WebCore
 * @subpackage Reflection
 */
class ClassLoader extends HelperBase
{
    private static $allData;
    private static $classCache;
    private static $classCacheFile;
    
    /**
     * Returns the next token resulted after token_get_all()
     *
     * @return array
     */
    private static function getNextToken()
    {
        while ($c = next(self::$allData))
        {
            if (!is_array($c) || $c[0] == T_WHITESPACE)
                continue;
            break;
        }
        
        return current(self::$allData);
    }
    
    /**
     * @return KeyedCollection
     */
    public static function &getClassCache()
    {
        if (is_null(self::$classCache) === false)
            return self::$classCache;
        
        self::$classCache     = new KeyedCollection();
        self::$classCacheFile = HttpContext::getDocumentRoot() . "app.classcache";
        
        if (file_exists(self::$classCacheFile))
        {
            $contents         = file_get_contents(self::$classCacheFile);
            /**
             * @var KeyedCollection
             */
            self::$classCache = XmlSerializer::deserialize($contents, 'KeyedCollection');
        }
        
        return self::$classCache;
    }
    
    /**
     *  Loads require file for a class
     *
     *  @param string $className
     */
    public static function loadClass($className)
    {
        require_once 'webcore.data.php';
        $classCache = self::getClassCache();
        if ($classCache->keyExists($className))
        {
            require_once $classCache->getValue($className);
            return;
        }
        
        set_time_limit(3600); // Might take a while to parse all the files.
        self::$classCache = new KeyedCollection();
        
        $mainDir = dirname(__FILE__);
        $dataDir = DataContext::getDataContextPath();
        $dirs    = array(
            $mainDir,
            $dataDir
        );
        
        $foundClass = false;
        foreach ($dirs as $dirName)
        {
            if (is_dir($dirName) === false) continue;
            $dir = opendir($dirName);
            while (($file = readdir($dir)) !== false)
            {
                $file = str_replace("//", "/", $dirName . "/" . $file);
                if (is_dir($file))
                    continue;
                self::$allData = token_get_all(file_get_contents($file));
                
                while ($token = self::getNextToken())
                {
                    if ($token[0] == T_CLASS || $token[0] == T_INTERFACE)
                    {
                        $currentClass = self::getNextToken();
                        
                        if ($className == $currentClass[1])
                        {
                            require_once $file;
                            $foundClass = true;
                        }
                        
                        $file = str_replace("\\", '/', $file);
                        self::$classCache->setValue($currentClass[1], $file);
                    }
                }
            }
            
            closedir($dir);
        }
        
        file_put_contents(self::$classCacheFile, XmlSerializer::serialize(self::$classCache));
        
        if ($foundClass === false)
            throw new Exception("ClassLoader cannot locate class $className in WebCore.");
    }
    
    /**
     * Adds an include path to the include_path environment variable
     * @param string The path to add
     */
    public static function addIncludePath($path)
    {
        if (is_dir($path) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The path '$path' was not found");
        
        $paths = explode(PATH_SEPARATOR, get_include_path());
        if (array_search($path, $paths) === false)
            array_push($paths, $path);
       
        set_include_path(implode(PATH_SEPARATOR, $paths));
    }
    
    /**
     * Removes an included path
     * @param string $path
     */
    public static function removeIncludePath($path)
    {
        $paths = explode(PATH_SEPARATOR, get_include_path());
        if (($k = array_search($path, $paths)) !== false)
            unset($paths[$k]);
        else
            continue;
        
        if (count($paths) === 0)
        {
            throw new SystemException(SystemException::EX_INVALIDOPERATION, "Path '$path' cannot be removed as it is the only one.");
        }
        
        set_include_path(implode(PATH_SEPARATOR, $paths));
    }
    
    /**
     * Gets an indexed collection of included paths
     * @return IndexedCollection
     */
    public static function getIncludePaths()
    {
        $paths = explode(PATH_SEPARATOR, get_include_path());
        $col = new IndexedCollectionWrapper($paths, true);
        return $col;
    }
}
?>