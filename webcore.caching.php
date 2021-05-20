<?php
/**
 * @package WebCore
 * @subpackage Caching
 * @version experimental
 * 
 * Cache managers enable distributable applications. Memcached cache is a popular distributed cache application.
 * Cache managers are configured in the app.settings file.
 * 
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.web.php";
require_once "webcore.application.php";

/**
 * Cache manager interface
 *
 * @package WebCore
 * @subpackage Caching
 */
interface ICacheManager extends IObject
{
    /**
     * Sets a key-value pair. If isSessionData
     * is true, data will be stored in session context.
     *
     * @param string $key
     * @param mixed $data
     * @param bool $isSessionData
     */
    public function set($key, $data, $isSessionData = false);
    
    /**
     * Gets a key-value pair. If isSessionData
     * is true, data will be retrieved in session context.
     *
     * @param string $key
     * @param bool $isSessionData
     */
    public function get($key, $isSessionData = false);
    
    /**
     * Removes a key-value pair. If isSessionData
     * is true, data will be removed in session context.
     *
     * @param string $key
     * @param bool $isSessionData
     */
    public function remove($key, $isSessionData = false);
    
    /**
     * Verifies if a key-value pair exists. If isSessionData
     * is true, data will be checked in session context.
     *
     * @param string $key
     * @param bool $isSessionData
     */
    public function contains($key, $isSessionData = false);
}

/**
 * Base class to implements CacheManagers
 *
 * @package WebCore
 * @subpackage Caching
 */
abstract class CacheManagerBase extends ObjectBase implements ICacheManager
{
    /**
     * Returns default cache manager
     *
     * @return CacheManagerBase
     */
    public static function fromSettings()
    {
        $cacheSettings = Settings::getValue(Settings::SKEY_CACHE);
        $cacheServer   = $cacheSettings[Settings::KEY_CACHE_SERVERS][0];
        $cachePort     = $cacheSettings[Settings::KEY_CACHE_PORTS][0];
        
        if (is_null($cacheServer) || is_null($cachePort))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Invalid parameters in Settings");
        
        if (class_exists('Memcache'))
            return new NativeMemcachedCacheManager($cacheServer, $cachePort);
        else
            return new MemcachedCacheManager($cacheServer, $cachePort);
    }
    
    /**
     * Sets a key-value pair. If isSessionData
     * is true, data will be stored in session context.
     *
     * @param string $key
     * @param mixed $data
     * @param bool $isSessionData
     */
    public function set($key, $data, $isSessionData = false)
    {
        if (is_string($key) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = key');
        
        if ($isSessionData)
            $key = HttpContext::getSession()->getSessionId() . $key;
        
        return $this->internalSet($key, $data);
    }
    
    /**
     * Internal function to set data.
     *
     * @param string $key
     * @param mixed $data
     */
    protected abstract function internalSet($key, $data);
    
    /**
     * Gets a key-value pair. If isSessionData
     * is true, data will be retrieved in session context.
     *
     * @param string $key
     * @param bool $isSessionData
     */
    public function get($key, $isSessionData = false)
    {
        if (is_string($key) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = key');
        
        if ($isSessionData)
            $key = HttpContext::getSession()->getSessionId() . $key;
        
        return $this->internalGet($key);
    }
    
    /**
     * Internal function to get data.
     *
     * @param string $key
     * @param mixed $data
     */
    protected abstract function internalGet($key);
    
    /**
     * Removes a key-value pair. If isSessionData
     * is true, data will be removed in session context.
     *
     * @param string $key
     * @param bool $isSessionData
     */
    public function remove($key, $isSessionData = false)
    {
        if (is_string($key) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = key');
        
        if ($isSessionData)
            $key = HttpContext::getSession()->getSessionId() . $key;
        
        return $this->internalRemove($key);
    }
    
    /**
     * Internal function to remove data.
     *
     * @param string $key
     * @param mixed $data
     */
    protected abstract function internalRemove($key);
    
    /**
     * Verifies if a key-value pair exists. If isSessionData
     * is true, data will be checked in session context.
     *
     * @param string $key
     * @param bool $isSessionData
     */
    public function contains($key, $isSessionData = false)
    {
        if (is_string($key) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = key');
        
        if ($isSessionData)
            $key = HttpContext::getSession()->getSessionId() . $key;
        
        return $this->internalContains($key);
    }
    
    /**
     * Internal function to contains data.
     *
     * @param string $key
     * @param mixed $data
     */
    protected abstract function internalContains($key);
}

/**
 * Memcached client using PHP Library
 *
 * @package WebCore
 * @subpackage Caching
 */
class NativeMemcachedCacheManager extends CacheManagerBase
{
    /**
     * @var Memcache
     */
    private $client;
    
    /**
     * Init a Memcached cache manager
     *
     * @param string $server
     * @param int $port
     */
    public function __construct($server, $port)
    {
        $this->client = new Memcache();
        $this->cliente->connect($server, $port);
    }
    
    /**
     * Set key-value pair to Memcached
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    protected function internalSet($key, $data)
    {
        return $this->client->set($key, $data);
    }
    
    /**
     * Get key-value pair from Memcached
     *
     * @param string $key
     * @return string
     */
    protected function internalGet($key)
    {
        return $this->client->get($key);
    }
    
    /**
     * Remove key-value pair from Memcached
     *
     * @param string $key
     * @return bool
     */
    protected function internalRemove($key)
    {
        return $this->client->delete($key);
    }
    
    /**
     * Return true if key exists
     *
     * @param string $key
     * @return bool
     */
    protected function internalContains($key)
    {
        $result = $this->internalGet($key);
        
        return ($result !== false);
    }
}

/**
 * Memcached client
 *
 * @package WebCore
 * @subpackage Caching
 */
class MemcachedCacheManager extends CacheManagerBase
{
    /**
     * @var resource
     */
    private $sock;
    
    /**
     * Init a Memcached cache manager
     *
     * @param string $server
     * @param int $port
     */
    public function __construct($server, $port)
    {
        $this->sock = fsockopen($server, $port);
    }
    
    /**
     * Send and receive a message to Memcached server
     *
     * @param string $message
     * @return string
     */
    private function sendAndReceive($message)
    {
        $message = $message . "\r\n";
        fwrite($this->sock, $message);
        
        $result = fgets($this->sock);
        return $result;
    }
    
    /**
     * Send and receive a GET message to Memcached server
     *
     * @param string $message
     * @return string
     */
    private function sendAndReceiveMessage($message)
    {
        $message = $message . "\r\n";
        fwrite($this->sock, $message);
        
        $return = "";
        
        while (true)
        {
            $result = fgets($this->sock);
            
            if ($result == "END\r\n" || $result === 0x00)
                break;
            
            $return .= $result;
        }
        
        return $return;
    }
    
    /**
     * Set key-value pair to Memcached
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    protected function internalSet($key, $data)
    {
        $result = $this->sendAndReceive("set $key 0 0 " . strlen($data) . "\r\n" . $data);
        
        return ($result == "STORED\r\n");
    }
    
    /**
     * Get key-value pair from Memcached
     *
     * @param string $key
     * @return string
     */
    protected function internalGet($key)
    {
        $result = $this->sendAndReceiveMessage("get $key");
        
        return $result;
    }
    
    /**
     * Remove key-value pair from Memcached
     *
     * @param string $key
     * @return bool
     */
    protected function internalRemove($key)
    {
        $result = $this->sendAndReceive("delete $key");
        
        return ($result == "DELETED\r\n");
    }
    
    /**
     * Return true if key exists
     *
     * @param string $key
     * @return bool
     */
    protected function internalContains($key)
    {
        $result = $this->sendAndReceive("get $key");
        
        return ($result != "END\r\n");
    }
}
?>