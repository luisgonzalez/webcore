<?php
/**
 * @package WebCore
 * @subpackage Web
 * 
 * 
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.imaging.php";

/**
 * All Http handlers must implement this interface.
 * @package WebCore
 * @subpackage Web
 */
interface IHttpHandler extends IObject
{
    function handleRequest();
}

/**
 * Defines the methods required for a session handler.
 * @package WebCore
 * @subpackage Web
 */
interface ISessionHandler extends ISingleton
{
    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public static function open($savePath, $sessionName);
    /**
     * @return bool
     */
    public static function close();
    /**
     * @param string $sessionId
     * @return string
     */
    public static function read($sessionId);
    /**
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     */
    public static function write($sessionId, $sessionData);
    /**
     * @param string $sessionId
     * @return bool
     */
    public static function destroy($sessionId);
    /**
     * @param int $maxLifetime the number of seconds the session exists for
     * @return bool
     */
    public static function gc($maxLifetime);
}

/**
 * Cookie management singleton.
 * @todo Create CookieContainer class to match path and domain otherwise deleteCookie does not work!
 * @package WebCore
 * @subpackage Web
 */
class CookieManager extends SerializableObjectBase implements ISingleton
{
    /**
     * Represents the instance of the singleton object
     *
     * @var CookieManager
     */
    protected static $__instance = null;
    /**
     * @var KeyedCollection
     */
    protected static $__internalCollection;
    
    /**
     * Creates a new instance of this class.
     */
    protected function __construct()
    {
        self::$__internalCollection = new KeyedCollectionWrapper($_COOKIE, false);
    }
    
    /**
     * Gets a unique cookie key based on it name, path, and domain.
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    protected static function getCookieId($name, $path, $domain)
    {
        return $domain . $path . $name;
    }
    
    /**
     * Sets or registers a cookie value given its identifier and value.
     * @param string $name
     * @param mixed $value
     * @param string $path Specify an empty string to make this cookie valid only for the current folder.
     * @param string $domain Leave empty to specify the cookie is valid for the entire domain.
     * @param string $expires The UNIX string representation of a time. The expiration timestamp is calculated using the strtotime function.
     * @param bool $secure
     * @param bool $httpOnly Prevents access to the cookie on the client-side (i.e. XSS attacks)
     * @return bool Returns true if the cookie was successfully set.
     */
    public function setCookie($name, $value, $path = '/', $domain = '', $expires = '+30 days', $secure = false, $httpOnly = false)
    {
        $cookieId = self::getCookieId($name, $path, $domain);
        return setcookie($cookieId, $value, strtotime($expires), $path, $domain, $secure, $httpOnly);
    }
    
    /**
     * Deletes the cookie given its name, path and domain.
     * Note that the path and domain must match the original intended cookie for this method to not throw an exception.
     * @param string $name
     * @param string $path
     * @param string $domain
     * @return bool Returns true if the cookie was successfully deleted (an expiration time in the past is set).
     */
    public function deleteCookie($name, $path = '/', $domain = '')
    {
        $cookieId = self::getCookieId($name, $path, $domain);
        if (!$this->isCookieSet($name, $path, $domain))
            throw new SystemException(SystemException::EX_INVALIDKEY, "Could not delete cookie with id '$cookieId'. It does not exist.");
        $this->getCookies()->removeItem($cookieId);
        return setcookie($cookieId, '', 1, $path, $domain);
    }
    
    /**
     * Gets the value stored within a cookie.
     * @param string $name The name of the cookie
     * @param string $path The path for which the cookie is valid.
     * @param string $domain The domain for which the cookie is available.
     *
     * @return mixed
     */
    public function getCookieValue($name, $path = '/', $domain = '')
    {
        $cookieId = self::getCookieId($name, $path, $domain);
        return $this->getCookies()->getValue($cookieId);
    }
    
    /**
     * Provides direct access to the cookies collection.
     * Use the provided cookie methods instead for better cookie management.
     * @return KeyedCollection
     */
    public function getCookies()
    {
        return self::$__internalCollection;
    }
    
    /**
     * Determines whether the cokkie with a given name has been set.
     * @return bool
     */
    public function isCookieSet($name, $path = '/', $domain = '')
    {
        $cookieId = self::getCookieId($name, $path, $domain);
        return $this->getCookies()->keyExists($cookieId);
    }
    
    /**
     * Creates a default instance of this class.
     * @return CookieManager
     */
    public static function createInstance()
    {
        return self::getInstance();
    }
    
    /**
     * Gets the singleton instance for this class
     *
     * @return CookieManager
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new CookieManager();
        
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
}


/**
 * Provides a database-agnostic session handler.
 * This session ehandler expects a table structure accessible from the data context.
 * WARNING: If the table is not found, it will be automatically created
 * Tabe Name: AppSessions (use setDbTableName to change this before calling the HttpContext::initialize method)
 * Column: SessionId VARCHAR(255), Primary key
 * Column: SessionData Text
 * Column: SessionExpires Int32
 * Standard ISO SQL command
 * CREATE TABLE AppSessions (
        SessionId varchar(255) NOT NULL,
        SessionData text NOT NULL,
        SessionExpires int NOT NULL,
        PRIMARY KEY (SessionId)
    )
 * @package WebCore
 * @subpackage Web
 */
class DatabaseSessionHandler implements ISessionHandler
{
    
    const DB_TABLENAME = 'AppSessions';
    
    private static $dbTableName = self::DB_TABLENAME;
    
    /**
     * Gets the database table name on which sessions are stored
     */
    public static function getDbTableName()
    {
        return self::$dbTableName;
    }
    
    /**
     * Sets the database table name on which sessions are stored
     */
    public static function setDbTableName($value)
    {
        self::$dbTableName = $value;
    }
    
    /**
     * Gets whether the instance of this singleton has been loaded.
     * @return bool
     */
    public static function isLoaded()
    {
        return true;
    }
    
    /**
     * Gets the instance of this singleton
     * @return eAcceleratorSessionHandler
     */
    public static function getInstance()
    {
        return new DatabaseSessionHandler();
    }
    
    /**
     * Gets the reflectionclass for this type.
     * @return ReflectionClass
     */
    public function getType()
    {
        return ReflectionClass(__CLASS__);
    }
    
    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public static function open($savePath, $sessionName)
    {
        $originalLogLevel = LogManager::getLogLevel();
        LogManager::setLogLevel(LogManager::LOG_LEVEL_DISABLED);
        
        $found = false;
        $foundCol = new IndexedCollection();
        
        try
        {
            $sql = "SELECT * FROM information_schema.tables WHERE TABLE_NAME LIKE ?";
            $command = DataContext::getInstance()->getConnection()->createCommand();
            $command->setCommandText($sql);
            $command->addQuotedParam('tableName', self::getDbTableName());
            $foundCol = DataContext::getInstance()->executeQuery($command);
        }
        catch (Exception $ex)
        {
            $exString = "<pre>DatabseSessionHandler Exception\nA default database needs to be configured in the app.settings file.\n"
                . $ex->getLine() . " on '" . $ex->getFile() . "'\n"
                . $ex->getCode() . ": " . $ex->getMessage() . "\nStack Trace:\n" . $ex->getTraceAsString() . "</pre>";
            LogManager::setLogLevel($originalLogLevel);
            HttpResponse::write($exString);
            HttpResponse::end();
            return false;
        }
        
        $found = $foundCol->getCount() > 0;
        if (!$found)
        {
            $quotedTableName = DataContext::getInstance()->getConnection()->quoteIdentifier(self::getDbTableName());
            $sql = "CREATE TABLE $quotedTableName ( SessionId varchar(255) NOT NULL, SessionData text NOT NULL, SessionExpires int NOT NULL, PRIMARY KEY (SessionId) )";
            $command = DataContext::getInstance()->getConnection()->createCommand();
            $command->setCommandText($sql);
            $sessionData = DataContext::getInstance()->executeNonQuery($command);
        }
        
        LogManager::setLogLevel($originalLogLevel);
        return true;
    }
    /**
     * @return bool
     */
    public static function close()
    {
        return true;
    }
    /**
     * @param string $sessionId
     * @return string
     */
    public static function read($sessionId)
    {
        $originalLogLevel = LogManager::getLogLevel();
        LogManager::setLogLevel(LogManager::LOG_LEVEL_DISABLED);
        
        try
        {
            $quotedTableName = DataContext::getInstance()->getConnection()->quoteIdentifier(self::getDbTableName());
            $sql = "SELECT SessionData FROM $quotedTableName WHERE SessionId = ?";
            $command = DataContext::getInstance()->getConnection()->createCommand();
            $command->setCommandText($sql);
            $command->addQuotedParam('sessionId', $sessionId);
            $sessionData = DataContext::getInstance()->executeScalar($command);
            
            if (is_null($sessionData))
            {
                LogManager::setLogLevel($originalLogLevel);
                return '';
            }
            else
            {
                LogManager::setLogLevel($originalLogLevel);
                return base64_decode($sessionData);
            }
        }
        catch (Exception $ex)
        {
            $exString = "<pre>DatabseSessionHandler Exception\nUnable to read session record from database.\n"
                . $ex->getLine() . " on '" . $ex->getFile() . "'\n"
                . $ex->getCode() . ": " . $ex->getMessage() . "\nStack Trace:\n" . $ex->getTraceAsString() . "</pre>";
            LogManager::setLogLevel($originalLogLevel);
            HttpResponse::write($exString);
            HttpResponse::end();
            return null;
        }

    }
    /**
     * Writes session data to the database
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     */
    public static function write($sessionId, $sessionData)
    {
        $originalLogLevel = LogManager::getLogLevel();
        LogManager::setLogLevel(LogManager::LOG_LEVEL_DISABLED);
        
        try
        {
            $quotedTableName = DataContext::getInstance()->getConnection()->quoteIdentifier(self::getDbTableName());
            $sql = "SELECT COUNT(*) AS __COUNT__ FROM $quotedTableName WHERE SessionId = ?";
            $command = DataContext::getInstance()->getConnection()->createCommand();
            $command->setCommandText($sql);
            $command->addQuotedParam('sessionId', $sessionId);
            $foundSession = DataContext::getInstance()->executeScalar($command);
            
            $command = DataContext::getInstance()->getConnection()->createCommand();
            if ($foundSession == '0')
            {
                $sql = "INSERT INTO $quotedTableName (SessionId, SessionData, SessionExpires) VALUES (?, ?, ?)";
                $command->addQuotedParam('sessionId', $sessionId);
                $command->addQuotedParam('sessionData', base64_encode($sessionData));
                $command->addQuotedParam('sessionExpires', time());
            }
            else
            {
                $sql = "UPDATE $quotedTableName SET SessionData = ?, SessionExpires = ? WHERE SessionId = ?";
                $command->addQuotedParam('sessionData', base64_encode($sessionData));
                $command->addQuotedParam('sessionExpires', time());
                $command->addQuotedParam('sessionId', $sessionId);
            }
            
            $command->setCommandText($sql);
            DataContext::getInstance()->executeNonQuery($command);
        }
        catch (Exception $ex)
        {
            $exString = "<pre>DatabseSessionHandler Exception\nUnable to write session record to database.\n"
                . $ex->getLine() . " on '" . $ex->getFile() . "'\n"
                . $ex->getCode() . ": " . $ex->getMessage() . "\nStack Trace:\n" . $ex->getTraceAsString() . "</pre>";
            LogManager::setLogLevel($originalLogLevel);
            HttpResponse::write($exString);
            HttpResponse::end();
            return false;
        }
        
        LogManager::setLogLevel($originalLogLevel);
        return true;
    }
    /**
     * @param string $sessionId
     * @return bool
     */
    public static function destroy($sessionId)
    {
        $originalLogLevel = LogManager::getLogLevel();
        LogManager::setLogLevel(LogManager::LOG_LEVEL_DISABLED);
        
        try
        {
            $quotedTableName = DataContext::getInstance()->getConnection()->quoteIdentifier(self::getDbTableName());
            $sql = "DELETE FROM $quotedTableName WHERE SessionId = ?";
            $command = DataContext::getInstance()->getConnection()->createCommand();
            $command->setCommandText($sql);
            $command->addQuotedParam('sessionId', $sessionId);
            DataContext::getInstance()->executeNonQuery($command);
        }
        catch (Exception $ex)
        {
            $exString = "<pre>DatabseSessionHandler Exception\nUnable to destroy session record.\n"
                . $ex->getLine() . " on '" . $ex->getFile() . "'\n"
                . $ex->getCode() . ": " . $ex->getMessage() . "\nStack Trace:\n" . $ex->getTraceAsString() . "</pre>";
            LogManager::setLogLevel($originalLogLevel);
            HttpResponse::write($exString);
            HttpResponse::end();
            return false;
        }

        
        LogManager::setLogLevel($originalLogLevel);
        return true;
    }
    /**
     * @param int $maxLifetime the number of seconds the session exists for
     * @return bool
     */
    public static function gc($maxLifetime)
    {
        $originalLogLevel = LogManager::getLogLevel();
        LogManager::setLogLevel(LogManager::LOG_LEVEL_DISABLED);
        
        try
        {
            $quotedTableName = DataContext::getInstance()->getConnection()->quoteIdentifier(self::getDbTableName());
            $deleteFilter = time() - $maxLifetime;
            $sql = "DELETE FROM $quotedTableName WHERE SessionExpires < ?";
            $command = DataContext::getInstance()->getConnection()->createCommand();
            $command->setCommandText($sql);
            $command->addQuotedParam('sessionExpires', time());
            DataContext::getInstance()->executeNonQuery($command);
        }
        catch (Exception $ex)
        {
            $exString = "<pre>DatabseSessionHandler Exception\nUnable to perform session garbage collection.\n"
                . $ex->getLine() . " on '" . $ex->getFile() . "'\n"
                . $ex->getCode() . ": " . $ex->getMessage() . "\nStack Trace:\n" . $ex->getTraceAsString() . "</pre>";
            LogManager::setLogLevel($originalLogLevel);
            HttpResponse::write($exString);
            HttpResponse::end();
            return false;
        }
        
        LogManager::setLogLevel($originalLogLevel);
        return true;
    }
}

/**
 * Provides a session handler for eAccelerator-enabled web servers.
 * @package WebCore
 * @subpackage Web
 */
class eAcceleratorSessionHandler implements ISessionHandler
{
    protected static $sessionName;
    
    /**
     * Gets whether the instance of this singleton has been loaded.
     * @return bool
     */
    public static function isLoaded()
    {
        return true;
    }
    
    /**
     * Gets the instance of this singleton
     * @return eAcceleratorSessionHandler
     */
    public static function getInstance()
    {
        return new eAcceleratorSessionHandler();
    }
    
    /**
     * Gets the reflectionclass for this type.
     * @return ReflectionClass
     */
    public function getType()
    {
        return ReflectionClass(__CLASS__);
    }
    
    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public static function open($savePath, $sessionName)
    {
        self::$sessionName = $sessionName;
        return true;
    }
    /**
     * @return bool
     */
    public static function close()
    {
        return true;
    }
    /**
     * @param string $sessionId
     * @return string
     */
    public static function read($sessionId)
    {
        return eaccelerator_get(self::$sessionName . '_' . $sessionId);
    }
    /**
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     */
    public static function write($sessionId, $sessionData)
    {
        $ttl = session_cache_limiter() * 60;
        if ($ttl <= 0)
            $ttl = 30 * 60;
        return eaccelerator_put(self::$sessionName . '_' . $sessionId, $sessionData, $ttl);
    }
    /**
     * @param string $sessionId
     * @return bool
     */
    public static function destroy($sessionId)
    {
        return eaccelerator_rm(self::$sessionName . '_' . $sessionId);
    }
    /**
     * @param int $maxLifetime the number of seconds the session exists for
     * @return bool
     */
    public static function gc($maxLifetime)
    {
        eaccelerator_gc();
        return true;
    }
}

/**
 * Singleton class representing the current request's session state
 * The class should not be used on its own but rather accessed through
 * the HttpContext object.
 *
 * @package WebCore
 * @subpackage Web
 */
class HttpSession extends KeyedCollectionBase implements ISingleton
{
    protected function __construct()
    {
        $appSettings = Settings::getValue(Settings::SKEY_APPLICATION);
        $handler     = isset($appSettings[Settings::KEY_APPLICATION_SESSIONHANDLER]) ? $appSettings[Settings::KEY_APPLICATION_SESSIONHANDLER] : '';
        if ($handler != '')
        {
            session_set_save_handler(array(
                $handler,
                'open'
            ), array(
                $handler,
                'close'
            ), array(
                $handler,
                'read'
            ), array(
                $handler,
                'write'
            ), array(
                $handler,
                'destroy'
            ), array(
                $handler,
                'gc'
            ));
        }
        
        session_start();
        parent::__construct($_SESSION, false);
    }
    
    /**
     * Regenerates the current session Id
     * Returns true is successful, false if failure.
     *
     * @return bool
     */
    public static function regenerateSessionId()
    {
        return session_regenerate_id();
    }
    
    /**
     * gets the current session identifier
     *
     * @return string
     */
    public static function getSessionId()
    {
        return session_id();
    }
    
    /**
     * Represents the instance of the singleton object
     *
     * @var HttpSession
     */
    private static $__instance = null;
    
    /**
     * Gets the singleton instance for this class
     *
     * @return HttpSession
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new HttpSession();
        
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return HttpSession
     */
    public static function createInstance()
    {
        return self::getInstance();
    }
    
    /**
     * Clears all the session variables
     */
    public function clear()
    {
        session_unset();
    }
    
    /**
     * Destroys the session and all data associated with it. this is equivalent to calling session_destroy()
     */
    public function destroy()
    {
        session_destroy();
    }
    
    /**
     * Registers an object -or array- for in-session persistence.
     * If the key does not exist, it creates it with the passed $object
     * If the key already exists, then $object is set to the contents of the key
     * @example $obj = new StdClass(); HttpContext::getSession()->registerPersistentObject('myKey', $obj); $obj->prop1 = 'myProp1Value';
     * @param string $keyName
     * @param object $object
     * @return int Returns 0 if the key did not exist before. Returns 1 if the key existed before.
     */
    public static function registerPersistentObject($keyName, &$object)
    {
        if (!is_object($object) && !is_array($object))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter $object must be an instance of an object or an array.');
        if (!is_string($keyName))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter $keyName must be a string');
        
        $session = self::getInstance();
        if ($session->keyExists($keyName) === false)
        {
            $session->setValue($keyName, $object);
            return 0;
        }
        else
        {
            $object = HttpContext::getSession()->getValue($keyName);
            return 1;
        }
    }
}

/**
 * The singleton response to output headers
 *
 * @package WebCore
 * @subpackage Web
 */
class HttpResponse extends ObjectBase implements ISingleton
{
    /*
     @todo Add all the response codes to constants http://www.krisjordan.com/php-class-for-http-response-status-codes/
    */
    const HTTP_RESPONSE_CODE_404 = '404 Not Found';
    const HTTP_RESPONSE_CODE_403 = '403 Forbidden';
    
    /**
     * Represents the instance of the singleton object
     *
     * @var HttpResponse
     */
    private static $__instance = null;
    
    /**
     * Gets the singleton instance for this class.
     *
     * @return HttpResponse
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new HttpResponse();
        
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        return true;
    }
    
    /**
     * Writes content directly to the response's output stream.
     * Use the getMarkupWriter method to output HTML easily.
     */
    public static function write($content)
    {
        echo $content;
    }
    
    /**
     * Writes content directly to the response's output stream, appending both, a carriage return and a line feed character (CRLF).
     * Use the getMarkupWriter method to output HTML easily.
     */
    public static function writeLine($content)
    {
        echo $content . "\r\n";
    }
    
    /**
     * Ends the response immediately.
     * WARNING: If output buffering is on, you must call the outputBufferFlush method in order to produce some output.
     */
    public static function end()
    {
        exit(0);
    }
    
    /**
     * Gets the output markup writer.
     * @return MarkupWriter
     */
    public static function getMarkupWriter()
    {
        return HtmlWriter::getInstance();
    }
    
    /**
     * Redirects to the given URL
     * @param string $url
     * @param array $params
     */
    public static function redirect($url, $params = null)
    {
        $queryString = '';
        if (is_null($params) === false)
        {
            $queryString = '?';
            $paramNames  = array_keys($params);
            foreach ($paramNames as $param)
            {
                $queryString .= $param . '=' . urlencode($params[$param]) . '&';
            }
        }
        self::appendHeader('Location', $url . $queryString);
        HttpResponse::end();
    }
    
    /**
     * Turns output buffering on.
     */
    public static function outputBufferStart()
    {
        ob_start();
    }
    
    /**
     * Flushes the output buffer; You must call the outputBufferstart function before you call this method.
     */
    public static function outputBufferFlush()
    {
        ob_end_flush();
    }
    
    /**
     * Gets the length in bytes of the output buffer.
     *
     * @return int
     */
    public static function outputBufferGetLength()
    {
        return ob_get_length();
    }
    
    /**
     * Clears the contents of the output buffer
     *
     */
    public static function clearOutputBuffer()
    {
        ob_end_clean();
    }
    
    /**
     * Returns the contents of the output buffer
     *
     * @return string
     */
    public static function getOutputBuffer()
    {
        return ob_get_contents();
    }
    
    /**
     * Adds a header to the response headers.
     *
     * @param string $headerName
     * @param string $headerValue
     */
    public static function appendHeader($headerName, $headerValue)
    {
        header($headerName . ': ' . $headerValue);
    }
    
    /**
     * Sets the response code for the Current HttpRequest
     *
     * @param string $responseCode. One of the HTTP_RESPONSE_CODE_* prefixes constants
     */
    public static function setResponseCode($responseCode)
    {
        header('HTTP/1.0 ' . $responseCode);
    }
    
    /**
     * Sets a Last-Modified header.
     * use strtotime function to obtain timestamps from ISO-compliant dates.
     * @param int $timestamp
     */
    public static function setLastModified($timestamp)
    {
        $timestampStr = gmdate("D, d M Y H:i:s", $timestamp);
        self::appendHeader('Expires', $timestampStr . ' GMT');
    }
    
    /**
     * Sets the Expires header given an offset in minutes.
     *
     * @param int $minutesFromNow The parameter can be a negative integer.
     */
    public static function setExpires($minutesFromNow = 0)
    {
        $offset    = 60 * $minutesFromNow;
        $timestamp = gmdate("D, d M Y H:i:s", time() + $offset);
        self::appendHeader('Expires', $timestamp . ' GMT');
    }
    
    /**
     * Sets the Cache-Control and Expires header to control content cacheability.
     * If minutes is negative a no-cache, must-revalidate header is sent out. Otherwise a max-age value is sent out.
     *
     * @param int $minutes
     */
    public static function setCacheControl($minutes = 0)
    {
        if ($minutes <= 0)
        {
            self::appendHeader('Cache-Control', 'no-cache, must-revalidate');
            self::setExpires(times() - (60 * 24 * 365 * 10));
        }
        else
        {
            $seconds = $minutes * 60;
            self::appendHeader('Cache-Control', 'max-age=' . $seconds . ', must-revalidate');
            self::setExpires(time() + $seconds);
        }
    }
    
    /**
     * Sets the content-type header
     *
     * @param string $mimetype
     */
    public static function setContentType($mimetype = 'application/octet-stream')
    {
        self::appendHeader('Content-Type', $mimetype);
    }
    
    /**
     * Sets the content-disposition header.
     *
     * @param string $disposition Can be attachment or inline
     * @param string $filename If disposition is 'attachment' then you can specify a filename to download.
     */
    public static function setContentDisposition($disposition = 'attachment', $filename = '')
    {
        $headerValue = $disposition;
        if ($filename != '')
            $headerValue .= '; filename="' . $filename . '"';
        
        self::appendHeader('Content-Disposition', $headerValue);
    }
    
    /**
     * Sets the content-length header (in bytes).
     *
     * @param int $length
     */
    public static function setContentLength($length)
    {
        self::appendHeader('Content-Length', $length);
    }
    
    /**
     * Determines whether the headers have already been sent to the client.
     * @return bool
     */
    public static function hasSentHeaders()
    {
        return headers_sent();
    }
}

/**
 * The singleton request holding references to GET, POST, REQUEST and FILES variables.
 *
 * @package WebCore
 * @subpackage Web
 */
class HttpRequest extends ObjectBase implements ISingleton
{
    /**
     * Represents the instance of the singleton object
     *
     * @var HttpRequest
     */
    private static $__instance = null;
    /**
     * Gets a collection representing $_REQUEST variables.
     *
     * @var KeyedCollectionWrapper
     */
    private static $requestItems;
    /**
     * Gets a collection representing $_GET variables
     *
     * @var KeyedCollectionWrapper
     */
    private static $queryStringItems;
    /**
     * Gets a collection representing $_POST variables
     *
     * @var KeyedCollectionWrapper
     */
    private static $postedItems;
    /**
     * Gets a collection representing $_FILES variables
     *
     * @var KeyedCollection
     */
    private static $filesItems;
    
    /**
     * Gets the singleton instance for this class.
     *
     * @return HttpRequest
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
        {
            self::$__instance       = new HttpRequest();
            self::$requestItems     = new KeyedCollectionWrapper($_REQUEST, true);
            self::$queryStringItems = new KeyedCollectionWrapper($_GET, true);
            self::$postedItems      = new KeyedCollectionWrapper($_POST, true);
            self::$filesItems       = new KeyedCollection();
            // Populate the files collection
            $postedFileKeys         = array_keys($_FILES);
            foreach ($postedFileKeys as $fileKey)
            {
                $postedFile = new PostedFile($fileKey);
                self::$filesItems->setValue($fileKey, $postedFile);
            }
        }
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
    
    /**
     * Gets a collection representing $_REQUEST variables (by reference)
     *
     * @return KeyedCollectionWrapper
     */
    public function &getRequestVars()
    {
        $vars = new KeyedCollection();
        
        foreach (self::$requestItems as $key => $value)
        {
            $decodedValue = utf8_decode($value);
            $vars->setValue($key, $decodedValue);
        }
        
        return $vars;
    }
    
    /**
     * Gets a collection representing $_POST variables (by reference)
     *
     * @return KeyedCollectionWrapper
     */
    public function &getPostVars()
    {
        return self::$postedItems;
    }
    
    /**
     * Gets a collection representing $_GET variables (by reference)
     *
     * @return KeyedCollectionWrapper
     */
    public function &getQueryStringVars()
    {
        return self::$queryStringItems;
    }
    
    /**
     * Gets a KeyedCollection containing items of type PostedFile
     * This is an enhanced version of the $_FILES array
     *
     * @return KeyedCollection
     */
    public function &getPostedFiles()
    {
        return self::$filesItems;
    }
}

/**
 * Wraps an item from the $_FILES array
 *
 * @package WebCore
 * @subpackage Web
 */
class PostedFile extends ObjectBase
{
    private $__fileReference;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $filesKey
     */
    public function __construct($filesKey)
    {
        if (array_key_exists($filesKey, $_FILES) === false)
            throw new SystemException(SystemException::EX_INVALIDKEY, '$_FILES key ' . $filesKey . ' does not exist.');
        
        $this->__fileReference =& $_FILES[$filesKey];
    }
    
    /**
     * Gets the filename, without the path
     * Example: myfile.ext
     *
     * @return string
     */
    public function getFileName()
    {
        return basename($this->__fileReference['name']);
    }
    
    /**
     * Gets the filename, without the path or extension
     * Example: myfile
     *
     * @return string
     */
    public function getFileBaseName()
    {
        $fileName = $this->getFileName();
        $pathInfo = pathinfo($fileName);
        return $pathInfo['filename'];
    }
    
    /**
     * Gets the file extension of the uploaded file
     * Example: ext
     *
     * @return string
     */
    public function getFileExtension()
    {
        $fileName = $this->getFileName();
        $pathInfo = pathinfo($fileName);
        return $pathInfo['extension'];
    }
    
    /**
     * Gets the mime-type that the client set when the file was uploaded
     * Example: image/gif
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->__fileReference['type'];
    }
    
    /**
     * Gets the size, in bytes of the array.
     *
     * @return int
     */
    public function getSize()
    {
        return (int) $this->__fileReference['size'];
    }
    
    /**
     * Gets the full path in the server to the uploaded file
     *
     * @return string
     */
    public function getTempFileName()
    {
        return $this->__fileReference['tmp_name'];
    }
    
    /**
     * Gets whether file was uploaded and can be correcly found in the server.
     *
     * @return bool
     */
    public function isUploaded()
    {
        return is_uploaded_file($this->getTempFileName());
    }
    
    /**
     * Gets the entire contents of the uploaded filed
     *
     * @return string
     */
    public function readAll()
    {
        $pathFileName = $this->getTempFileName();
        return file_get_contents($pathFileName);
    }
    
    /**
     * Gets the error code of the file upload result
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->__fileReference['error'];
    }
    
    /**
     * Gets a user-friendly message based on the error code
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $errorCode = $this->getErrorCode();
        switch ($errorCode)
        {
            case UPLOAD_ERR_CANT_WRITE:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_CANT_WRITE);
                break;
            case UPLOAD_ERR_EXTENSION:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_EXTENSION);
                break;
            case UPLOAD_ERR_FORM_SIZE:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_FORM_SIZE);
                break;
            case UPLOAD_ERR_INI_SIZE:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_INI_SIZE);
                break;
            case UPLOAD_ERR_NO_FILE:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_NO_FILE);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_NO_TMP_DIR);
                break;
            case UPLOAD_ERR_OK:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_OK);
                break;
            case UPLOAD_ERR_PARTIAL:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_PARTIAL);
                break;
            default:
                return Resources::getValue(Resources::SRK_UPLOAD_ERR_CANT_WRITE);
                break;
        }
        
        return StaticResources::getMessagePostedFileErrorCode($this->getErrorCode());
    }
}

/**
 * Represents a typed, keyed collection wrapping the $_SERVER PHP array
 *
 * @package WebCore
 * @subpackage Web
 */
class HttpContextInfo extends KeyedCollectionBase implements ISingleton
{
    const CLIENT_DESKTOP = "desktop";
    const CLIENT_MOBILE = "mobile";
    const CLIENT_IPHONE = "iphone";
    const CLIENT_BB = "blackberry";
    const CLIENT_BOT = "bot";
    
    const INFO_HTTP_USER_AGENT = 'HTTP_USER_AGENT';
    const INFO_REMOTE_ADDR = 'REMOTE_ADDR';
    const INFO_REMOTE_PORT = 'REMOTE_PORT';
    const INFO_HTTP_ACCEPT = 'HTTP_ACCEPT';
    const INFO_HTTP_ACCEPT_ENCODING = 'HTTP_ACCEPT_ENCODING';
    const INFO_HTTP_ACCEPT_LANGUAGE = 'HTTP_ACCEPT_LANGUAGE';
    const INFO_GATEWAY_INTERFACE = 'GATEWAY_INTERFACE';
    const INFO_SERVER_ADDR = 'SERVER_ADDR';
    const INFO_SERVER_PORT = 'SERVER_PORT';
    const INFO_DOCUMENT_ROOT = 'DOCUMENT_ROOT';
    const INFO_SERVER_NAME = 'SERVER_NAME';
    const INFO_SERVER_SOFTWARE = 'SERVER_SOFTWARE';
    const INFO_SERVER_PROTOCOL = 'SERVER_PROTOCOL';
    const INFO_REQUEST_URI = 'REQUEST_URI';
    const INFO_QUERY_STRING = 'QUERY_STRING';
    const INFO_REQUEST_METHOD = 'REQUEST_METHOD';
    const INFO_REQUEST_TIME = 'REQUEST_TIME';
    const INFO_HTTP_HOST = 'HTTP_HOST';
    const INFO_SCRIPT_NAME = 'SCRIPT_NAME';
    const INFO_HTTP_CONNECTION = 'HTTP_CONNECTION';
    const INFO_PHP_SELF = 'PHP_SELF';
    const INFO_SCRIPT_FILENAME = 'SCRIPT_FILENAME';
    
    private static $clientType;
    
    protected function __construct()
    {
        parent::__construct($_SERVER, true);
    }
    
    /**
     * Represents the instance of the singleton object
     *
     * @var HttpContextInfo
     */
    private static $__instance = null;
    
    /**
     * Gets the singleton instance for this class.
     *
     * @return HttpContextInfo
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
        {
            self::$__instance = new HttpContextInfo();
            self::$__instance->detectClientType();
        }
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
    
    /**
     * Returns client's type
     *
     * @return string
     */
    public function getClientType()
    {
        return self::$clientType;
    }
    
    /**
     * Detects client type using User-Agent
     *
     */
    public function detectClientType()
    {
        self::$clientType = self::CLIENT_DESKTOP;
        $agent            = self::getInstance()->getClientAgent();
        
        $iphoneAgents = array(
            "iPhone",
            "iPod"
        );
        
        foreach ($iphoneAgents as $key)
        {
            if (preg_match("/$key/i", $agent))
            {
                self::$clientType = self::CLIENT_IPHONE;
                return;
            }
        }
        
        $botAgents = array(
            "Teoma",
            "alexa",
            "MSNBot",
            "inktomi",
            "looksmart",
            "Firefly",
            "NationalDirectory",
            "Ask Jeeves",
            "TECNOSEEK",
            "InfoSeek",
            "WebFindBot",
            "girafabot",
            "crawler",
            "www.galaxy.com",
            "Googlebot",
            "Scooter",
            "Slurp",
            "Yammybot",
            "WebBug",
            "Spade",
            "ZyBorg"
        );
        
        foreach ($botAgents as $key)
        {
            if (preg_match("/$key/i", $agent))
            {
                self::$clientType = self::CLIENT_BOT;
                return;
            }
        }
        
        $mobileAgents = array(
            "BlackBerry",
            "Opera Mini",
            "Windows CE",
            "Symbian"
        );
        
        foreach ($mobileAgents as $key)
        {
            if (preg_match("/$key/i", $agent))
            {
                if ($key == "BlackBerry")
                    self::$clientType = self::CLIENT_BB;
                else
                    self::$clientType = self::CLIENT_MOBILE;
                
                return;
            }
        }
    }
    
    /**
     * Returns an info value from the $_SERVER array
     *
     * @return string
     */
    protected static function getInfoValue($key)
    {
        return (self::getInstance()->keyExists($key) === true) ? self::getInstance()->getValue($key) : "";
    }
    
    /**
     * Gets the client's browser name and version (if set in the request)
     *
     * @return string
     */
    public function getClientAgent()
    {
        return self::getInfoValue(self::INFO_HTTP_USER_AGENT);
    }
    
    /**
     * Returns an array with client agent information such as name and version
     * @return array
     */
    public function getClientAgentInfo()
    {
        return get_browser(null, true);
    }
    
    /**
     * Gets the IP Address of the current request.
     *
     * @return string
     */
    public function getClientIPAddress()
    {
        return self::getInfoValue(self::INFO_REMOTE_ADDR);
    }
    /**
     * Gets the TCP/IP port the client used to send the request
     *
     * @return int
     */
    public function getClientPort()
    {
        return self::getInfoValue(self::INFO_REMOTE_PORT);
        
    }
    /**
     * Gets the comma-delimited mimetypes that the client accepts.
     *
     * @return string
     */
    public function getClientAccept()
    {
        return self::getInfoValue(self::INFO_HTTP_ACCEPT);
        
    }
    /**
     * Gets the comma-delimieted supported stream formats that the client supports.
     * For example, gzip, deflate
     *
     * @return string
     */
    public function getClientAcceptEncoding()
    {
        return self::getInfoValue(self::INFO_HTTP_ACCEPT_ENCODING);
        
    }
    /**
     * Gets the client's language and culture code
     * For example, en-us or es-mx
     * @return string
     */
    public function getClientAcceptLanguage()
    {
        return self::getInfoValue(self::INFO_HTTP_ACCEPT_LANGUAGE);
        
    }
    
    /**
     * Gets the version of the Common Gateway Interface the server is using.
     * For example, CGI/1.1
     *
     * @return string
     */
    public function getServerCGI()
    {
        return self::getInfoValue(self::INFO_GATEWAY_INTERFACE);
        
    }
    /**
     * Gets the server's IP address serving the response
     *
     * @return string
     */
    public function getServerIPAddress()
    {
        return self::getInfoValue(self::INFO_SERVER_ADDR);
    }
    /**
     * Gets the TCP/IP port on which the response is returned by the server
     *
     * @return string
     */
    public function getServerPort()
    {
        return self::getInfoValue(self::INFO_SERVER_PORT);
        
    }
    /**
     * Gets the root path in the server's file system from which all
     * documents are processed and served.
     *
     * @return string
     */
    public function getServerFileSystemWebRootPath()
    {
        return self::getInfoValue(self::INFO_DOCUMENT_ROOT);
        
    }
    /**
     * Gets the name of the server.
     * For example, localhost
     *
     * @return string
     */
    public function getServerAddress()
    {
        return self::getInfoValue(self::INFO_SERVER_NAME);
        
    }
    /**
     * Gets the name and version of the software used to process requests and responses.
     *
     * @return string
     */
    public function getServerSoftware()
    {
        return self::getInfoValue(self::INFO_SERVER_SOFTWARE);
        
    }
    /**
     * Gets the protocol the server is using to process requests and serve response.
     * For example, HTTP/1.1
     *
     * @return string
     */
    public function getServerProtocol()
    {
        return self::getInfoValue(self::INFO_SERVER_PROTOCOL);
        
    }
    
    /**
     * Gets the full Uniform Resource identifier of the request.
     * For example, http://localhost/test/test.php?id=6
     *
     * @return string
     */
    public function getRequestUri()
    {
        return self::getInfoValue(self::INFO_REQUEST_URI);
        
    }
    /**
     * Gets the query string that appears after the script's name
     * For example, id=6
     *
     * @return string
     */
    public function getRequestQueryString()
    {
        return self::getInfoValue(self::INFO_QUERY_STRING);
        
    }
    
    /**
     * Gets the REST method for the request.
     * For example, POST
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return StringHelper::toUpper(self::getInfoValue(self::INFO_REQUEST_METHOD));
        
    }
    /**
     * Gets the timestamp at which the request was made.
     *
     * @return int
     */
    public function getRequestStartTime()
    {
        return self::getInfoValue(self::INFO_REQUEST_TIME);
        
    }
    /**
     * Gets the hostname and port at which the request is directed
     * For example, localhost:88
     *
     * @return string
     */
    public function getRequestHost()
    {
        return self::getInfoValue(self::INFO_HTTP_HOST);
        
    }
    /**
     * Gets the virtual path to the resource at which the request was created.
     * For example, /test/testing.php
     *
     * @return string
     */
    public function getRequestScriptPath()
    {
        return self::getInfoValue(self::INFO_SCRIPT_NAME);
        
    }
    
    /**
     * Gets the connection mode the client requests.
     * For example, Keep-Alive
     *
     * @return string
     */
    public function getRequestConnectionMode()
    {
        return self::getInfoValue(self::INFO_HTTP_CONNECTION);
        
    }
    
    /**
     * Gets the full path to the executing script in the file system.
     * For example: c:\InetPub\wwwroot\test\testing.php
     *
     * @return string
     */
    public function getScriptFileSystemPath()
    {
        return self::getInfoValue(self::INFO_SCRIPT_FILENAME);
        
    }
    
    /**
     * Gets the full virtual path to the executing script.
     * for example: /test/testing.php
     *
     * @return string
     */
    public function getScriptVirtualPath()
    {
        return self::getInfoValue(self::INFO_PHP_SELF);
        
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return HttpContextInfo
     */
    public static function createInstance()
    {
        return self::getInstance();
    }
}

/**
 * Represents a user navigation entry holding significant request information.
 * @package WebCore
 * @subpackage Web
 */
class NavigationEntry extends SerializableObjectBase
{
    protected $postedVars;
    protected $queryVars;
    protected $requestUrl;
    protected $requestTime;
    protected $hadPostedFiles;
    protected $requestMethod;
    protected $scriptVirtualPath;
    
    /**
     * Creates a new instance of this class
     */
    public function __construct()
    {
        $this->postedVars        = clone HttpRequest::getInstance()->getPostVars();
        $this->queryVars         = clone HttpRequest::getInstance()->getQueryStringVars();
        $this->hadPostedFiles    = HttpRequest::getInstance()->getPostedFiles()->getCount() > 0;
        $this->requestUrl        = HttpContextInfo::getInstance()->getRequestUri();
        $this->requestTime       = time();
        $this->requestMethod     = HttpContextInfo::getInstance()->getRequestMethod();
        $this->scriptVirtualPath = HttpContextInfo::getInstance()->getScriptVirtualPath();
    }
    
    /**
     * Transfers the client to the state of this this NavigationEntry
     * @param bool $onlyStateFlags If set to true, rall transfer parameters that are not prefixed by Controller::PREFIX_STATE
     */
    public function transfer($onlyStateFlags = true)
    {
        $params = new KeyedCollection();
        foreach ($this->postedVars->getKeys() as $key)
        {
            if (!StringHelper::beginsWith($key, Controller::PREFIX_STATE) && $onlyStateFlags === true)
                continue;
            $params->setValue($key, $this->postedVars->getValue($key));
        }
        
        Controller::transfer($this->requestUrl, $params->getArrayReference());
    }
    
    /**
     * Creates a new instance of this class.
     */
    public static function createInstance()
    {
        return new NavigationEntry();
    }
    
    /**
     * @return KeyedCollection
     */
    public function getPostedVars()
    {
        return $this->postedVars;
    }
    
    /**
     * @return KeyedCollection
     */
    public function getQueryVars()
    {
        return $this->queryVars;
    }
    
    /**
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }
    
    /**
     * @return int
     */
    public function getRequestTime()
    {
        return $this->requestTime;
    }
    
    /**
     * @return bool
     */
    public function getHadPPostedFiles()
    {
        return $this->hadPostedFiles;
    }
    
    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }
    
    /**
     * @return string
     */
    public function getScriptVirtualPath()
    {
        return $this->scriptVirtualPath;
    }
}

/**
 * Represents the user navigation history.
 * This class is useful to transfer the user back to a previous request or to keep track of navigation statistics.
 * Note: The first item (0) always has the most recent navigation entry.
 * A maximum of 10 navigation entries is kept.
 * 
 * @package WebCore
 * @subpackage Web
 */
class NavigationHistory extends IndexedCollection
{
    protected $storageLimit;
    
    /**
     * Creates a new instance of this class.
     */
    public function __construct($storageLimit = 4)
    {
        $this->storageLimit = $storageLimit; // @todo get this from settings
        $this->isReadOnly   = true;
    }
    
    /**
     * @return NavigationEntry
     */
    public function getValue($offset)
    {
        return parent::getItem($offset);
    }
    
    /**
     * @return NavigationEntry
     */
    public function &getItem($offset)
    {
        return parent::getItem($offset);
    }
    
    /**
     * @return NavigationEntry
     */
    public function &getLastItem()
    {
        return parent::getLastItem();
    }
    
    /**
     * Gets the Navigation entry previous to the current request being navigated.
     * @return NavigationEntry Returns null if no previous item can be found
     */
    public function &getPreviousItem()
    {
        if ($this->getCount() <= 1)
            return null;
        return $this->getItem(1);
    }
    
    /**
     * Adds the current navigation enetry to the NavigationHistory
     * @param bool $withReplace Determines whether the las entry should be replaced with the new one if the script virtual paths match exactly.
     */
    public function addCurrent($withReplace = true)
    {
        // unlock the collection
        $this->isReadOnly = false;
        $entry            = new NavigationEntry();
        $updated          = false;
        
        if (strstr($entry->getScriptVirtualPath(), 'getfile')) return;
        
        if ($this->getCount() > 0 && $withReplace === true)
        {
            if ($this->getItem(0)->getScriptVirtualPath() === $entry->getScriptVirtualPath())
            {
                $oldNav = $this->getItem(0);
                $oldNav->getPostedVars()->merge($entry->getPostedVars());
                $updated = true;
            }
        }
        if ($updated === false)
        {
            parent::insertAt(0, $entry);
        }
        
        if ($this->getCount() > $this->storageLimit)
        {
            $this->removeAt($this->storageLimit);
        }
        $this->isReadOnly = true;
    }
}

/**
 * Represents the current HTTP context
 * Always call the initialize() method in order to enable access to child objects.
 *
 * @package WebCore
 * @subpackage Web
 */
class HttpContext extends ObjectBase
{
    /**
     * State variable to determine whether the Context has been initialized.
     *
     * @var bool
     */
    private static $isInitialized = false;
    protected static $accessPermissionSet;
    /**
     * @var NavigationHistory
     */
    protected static $navigationHistory;
    
    /**
     * Initializes the current HTTP context available to the current request.
     * This method is usuarlly called in the first line of logic after the require
     * statements.
     *
     */
    public static function initialize()
    {
        if (self::$isInitialized === true) return;
        
        ClassLoader::addIncludePath(HttpContext::getFSLibraryRoot());
        self::$isInitialized = true;
        $appSettings         = Settings::getValue(Settings::SKEY_APPLICATION);
        
        if (array_key_exists(Settings::KEY_APPLICATION_TIMEZONE, $appSettings))
            date_default_timezone_set($appSettings[Settings::KEY_APPLICATION_TIMEZONE]);
        else
            date_default_timezone_set('America/Mexico_City');
        
        LogManager::getInstance();
        HttpSession::getInstance();
        self::$navigationHistory = new NavigationHistory();
        HttpContext::getSession()->registerPersistentObject('__navigationHistory', self::$navigationHistory);
        self::getNavigationHistory()->addCurrent(); // @todo if appSettings->storageLimit > 0 then addCurrent
        
        self::registerStandardHandlers();
        HttpHandlerManager::handleRequest();
        
        $settings = Settings::getValue(Settings::SKEY_COMPRESSION);
        
        if ($settings[Settings::KEY_COMPRESSION_ENABLED] == 1)
        {
            $className = $settings[Settings::KEY_COMPRESSION_PROVIDER];
            if ($className == HttpCompressor::PROVIDER_DOM)
                DomCompressor::getInstance()->initialize();
        }
    }
    
    /**
     * registers the common handlers for the frameword
     */
    private static function registerStandardHandlers()
    {
        $fileHandler = new FileHttpHandler();
        $resourcesHandler = new ResourcesHttpHandler();
        $imageHandler = new ImageHttpHandler();
        
        HttpHandlerManager::registerHandler($fileHandler);
        HttpHandlerManager::registerHandler($resourcesHandler);
        HttpHandlerManager::registerHandler($imageHandler);
    }
    
    /**
     * Returns the current Session object.
     *
     * @return HttpSession
     */
    public static function getSession()
    {
        if (self::$isInitialized === false)
            throw new SystemException(SystemException::EX_NOCONTEXT);
        
        return HttpSession::getInstance();
    }
    
    /**
     * Gets the preserved user navigation history.
     * @return NavigationHistory
     */
    public static function &getNavigationHistory()
    {
        if (self::$isInitialized === false)
            throw new SystemException(SystemException::EX_NOCONTEXT);
        
        return self::$navigationHistory;
    }
    
    /**
     * Gets the Cookie Manager object
     * @return CookieManager
     */
    public static function &getCookieManager()
    {
        if (self::$isInitialized === false)
            throw new SystemException(SystemException::EX_NOCONTEXT);
        
        return CookieManager::getInstance();
    }
    
    /**
     * Gets the current HttpRequest object, containing all GET and POST variables
     *
     * @return HttpRequest
     */
    public static function getRequest()
    {
        return HttpRequest::getInstance();
    }
    
    /**
     * Gets the current HttpResponse object, from which response processing can be manipulated
     * @return HttpResponse
     */
    public static function getResponse()
    {
        return HttpResponse::getInstance();
    }
    
    /**
     * Gets the PermissionSet object in effect
     * @return PermissionSet
     */
    public static function getPermissionSet()
    {
        if (is_null(self::$accessPermissionSet))
            self::$accessPermissionSet = new PermissionSet(Permission::PERMISSION_DENY);
        
        return self::$accessPermissionSet;
    }
    
    /**
     * @return IMembershipUser
     */
    public static function getUser()
    {
        return FormsAuthentication::getUser();
    }
    
    /**
     * @return IndexedCollection
     */
    public static function getUserRoles()
    {
        $user = self::getUser();
        if (is_null($user))
            return new IndexedCollection();
        
        return $user->getRoles();
    }
    
    /**
     * Resolves role-based security for this context according to the rules dfined by PermissionSet
     */
    public static function applySecurity()
    {
        /**
         * @var MembershipUser
         */
        $user  = self::getUser();
        $allow = self::getPermissionSet()->resolve($user);
        
        if ($allow == Permission::PERMISSION_DENY)
        {
            FormsAuthentication::redirectToLoginPage(HttpContext::getInfo()->getRequestUri());
        }
    }
    
    /**
     * Determines whether the Context has been properly initialized.
     *
     * @return bool
     */
    public static function isInitialized()
    {
        return self::$isInitialized;
    }
    
    /**
     * Gets the current request's information.
     *
     * @return HttpContextInfo
     */
    public static function getInfo()
    {
        return HttpContextInfo::getInstance();
    }
    
    /**
     * Gets WebCore's libraray extensions absolute path (for use with php include paths)
     * Paths returned always have a trailing slash '/'.
     * This is a file system path
     * @return string
     */
    public static function getFSLibraryExtensionsRoot()
    {
        return self::getFSLibraryRoot() . 'ext/';
    }
    
    /**
     * Gets WebCore's libraray absolute path (for use with php include paths)
     * Paths returned always have a trailing slash '/'.
     * This is a file system path
     * @return string
     */
    public static function getFSLibraryRoot()
    {
        return str_replace('//', '/', str_replace('\\', '/', realpath(dirname(__FILE__)) . '/'));
    }
    
    /**
     * Gets WebCore's libraray virtual path (for use with css and js includes)
     * Paths returned always have a trailing slash '/'.
     * This is a virtual path
     * @return string
     */
    public static function getLibraryRoot()
    {
        if (defined('LIBRARY_ROOT') === true)
        {
            if (strrchr(LIBRARY_ROOT, '/') == '/' || strrchr(LIBRARY_ROOT, '\\') == '\\')
                return str_replace('\\', '/', LIBRARY_ROOT);
            else
                return str_replace('\\', '/', LIBRARY_ROOT) . '/';
        }
        
        $libRoot = str_replace('\\', '/', self::getApplicationRoot() . substr(realpath(dirname(__FILE__)), stripos(realpath(dirname(__FILE__)), 'webcore')) . '/');
        return $libRoot;
    }
    
    /**
     * Gets the absolute, real path from which the server takes documents.
     * If the constant DOCUMENT_ROOT is not defined, the method tries to obtain the path automatically.
     * Paths returned always have a trailing slash '/'.
     * This is a file system path
     * @return string
     */
    public static function getDocumentRoot()
    {
        if (defined('DOCUMENT_ROOT') === true)
        {
            if (strrchr(DOCUMENT_ROOT, '/') == '/' || strrchr(DOCUMENT_ROOT, '\\') == '\\')
                return str_replace('\\', '/', DOCUMENT_ROOT);
            else
                return str_replace('\\', '/', DOCUMENT_ROOT) . '/';
        }
        
        // Get local and absolute paths
        $localPathInfo = pathinfo(getenv("SCRIPT_NAME"));
        $localPath     = $localPathInfo['dirname'];
        
        $absolutePath = realpath('.');
        
        // a fix for Windows slashes. Windows 2k and above accept forward slashes
        $absolutePath = str_replace("\\", "/", $absolutePath);
        
        // To lower case strings
        $localPath    = strtolower($localPath);
        $absolutePath = strtolower($absolutePath);
        
        // Cut off the last part of the script's path, matching the absolute path
        $documentRoot = substr($absolutePath, 0, strpos($absolutePath, $localPath));
        
        if ($documentRoot != '/')
            $documentRoot = $documentRoot . '/';
        return $documentRoot;
    }
    
    /**
     * Gets the temporary Operating System path
     * Paths returned always have a trailing slash '/'.
     * This is a file system path
     * @return string
     */
    public static function getTempDir()
    {
        $absolutePath = str_replace("\\", "/", sys_get_temp_dir());
        
        if ($absolutePath != '/' && substr($absolutePath, -1) != '/')
            $absolutePath = $absolutePath . '/';
        
        return $absolutePath;
    }
    
    /**
     * Gets the virtual path of the server root.
     * Paths returned always have a trailing slash '/'.
     * This is a file system path
     * @return string
     */
    public static function getServerRoot()
    {
        $current_script = dirname($_SERVER['SCRIPT_NAME']);
        $current_path   = dirname($_SERVER['SCRIPT_FILENAME']);
        
        $adjust = explode("/", $current_script);
        $adjust = count($adjust) - 1;
        
        $traverse      = str_repeat("../", $adjust);
        $adjusted_path = sprintf("%s/%s", $current_path, $traverse);
        
        $absolutePath = realpath($adjusted_path);
        $absolutePath = str_replace("\\", "/", $absolutePath);
        
        return $absolutePath;
    }
    
    /**
     * Gets the virtual path of the application root.
     * If the constant APPLICATION_ROOT is not defined, the method returns '/'
     * Paths returned always have a trailing slash '/'.
     * This is a virtual path
     * @return string
     */
    public static function getApplicationRoot()
    {
        if (defined('APPLICATION_ROOT') === true)
            return APPLICATION_ROOT;
        
        return "/";
    }
}

/**
 * This class provide a easy way to know MIME type
 * and valid file extension of any stream
 * @todo Deprecate with PHP 5.3
 * @package WebCore
 * @subpackage Web
 */
class MimeResolver extends HelperBase
{
    private static $extdata = array(
        'image/jpeg' => 'jpg',
        'image/x-png' => 'png',
        'text/vnd.ms-word' => 'doc',
        'text/vnd.ms-excel' => 'xls',
        'application/msword' => 'doc',
        'application/vnd.ms-excel' => 'xls',
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/x-rar' => 'rar',
        'audio/mpeg' => 'mp3',
        'video/mpeg' => 'mpg',
        'text/html' => 'html',
        'image/gif' => 'gif',
        'video/quicktime' => 'mov',
        'video/avi' => 'avi',
        'image/x-bmp' => 'bmp');
    
    private static $magicdata = array(
        array(0, 2, 0xfff0, 0xf0ff, "audio/mpeg"),
        array(4, 4, 0, "moov", "video/quicktime"),
        array(4, 4, 0, "mdat", "video/quicktime"),
        array(257, 6, 0, "ustar\0", "application/x-tar"),
        array(0, 4, 0, "Rar!", "application/x-rar"),
        array(0, 4, 0, "PK\003\004", "application/zip"),
        array(0, 11, 0, "#!/bin/perl", "application/x-perl"),
        array(0, 12, 0, "#! /bin/perl", "application/x-perl"),
        array(0, 2, 0, "#!", "text/script"),
        array(0, 2, 0, "\037\213", "application/x-gzip"),
        array(0, 2, 0, "BZ", "application/x-bzip"),
        array(0, 5, 0, "\000\001\000\000\000", "font/ttf"),
        array(0, 4, 0, "MM\x00\x2a", "image/tiff"),
        array(0, 4, 0, "\x89PNG", "image/x-png"),
        array(1, 3, 0, "PNG", "image/x-png"),
        array(0, 2, 0, "Øÿ", "image/jpeg"),
        array(0, 3, 0, "ÿØÿ", "image/jpeg"),
        array(0, 2, 0, "BM", "image/x-bmp"),
        array(0, 2, 0, "IC", "image/x-ico"),
        array(0, 4, 0, "FFIL", "font/ttf"),
        array(65, 4, 0, "FFIL", "font/ttf"),
        array(0, 4, 0, "LWFN", "font/type1"),
        array(65, 4, 0, "LWFN", "font/type1"),
        array(0, 2, 0, "MZ", "application/x-ms-dos-executable"),
        array(2080, 27, 0, "Microsoft Word 6.0 Document", "text/vnd.ms-word"),
        array(2080, 26, 0, "Documento Microsoft Word 6", "text/vnd.ms-word"),
        array(2112, 9, 0, "MSWordDoc", "text/vnd.ms-word"),
        array(0, 5, 0, "PO^Q`", "text/vnd.ms-word"),
        array(2080, 29, 0, "Microsoft Excel 5.0 Worksheet", "application/vnd.ms-excel"),
        array(2114, 5, 0, "Biff5", "application/vnd.ms-excel"),
        array(0, 2, 0, "%!", "application/postscript"),
        array(0, 14, 0, "<!DOCTYPE HTML", "text/html"),
        array(0, 14, 0, "<!doctype html", "text/html"),
        array(0, 5, 0, "<html", "text/html"),
        array(0, 5, 0, "<HTML", "text/html"),
        array(0, 4, 0, "%PDF", "application/pdf"),
        array('>2', 2, 0, "º¾", "application/java"),
        array('>8', 4, 0, "WAVE", "audio/x-wav"),
        array(0, 3, 0, "ID3", "audio/mpeg"),
        array(0, 4, 0, "OggS", "audio/x-ogg"),
        array(0, 2, 0, "MM", "image/tiff"),
        array(0, 2, 0, "II", "image/tiff"),
        array(0, 6, 0, "FGF95a", "image/gif"),
        array(0, 3, 0, "PBF", "image/gif"),
        array(0, 3, 0, "GIF", "image/gif"),
        array(0, 4, 0, "\376\067\0\043", "application/msword"),
        array(0, 6, 0, "\320\317\021\340\241\261", "application/msword"),
        array(0, 6, 0, "\333\245-\0\0\0", "application/msword"),
        array('>8', 4, 0, "AVI ", "video/avi"),
        array(0, 4, 0, "³\x01\x00\x00", "video/mpeg"),
        array(0, 4, 0, "º\x01\x00\x00", "video/mpeg"),
        array(0, 4, 0, "\x00\x00¾1", "text/vnd.ms-word"),
        array(0, 2, 0, "\x00\x00", "audio/mpeg"),
        array(0, 5, 0, "<?php", "application/x-php"));
    
    /**
     * Gets Extension value of a MIME type
     *
     * @param string $mimeType
     * @return string
     */
    public static function getExtension($mimeType)
    {
        $collection = new KeyedCollectionWrapper(self::$extdata, true);
        
        if ($collection->keyExists($mimeType))
            return $collection->getValue($mimeType);
        
        return "0";
    }
    
    /**
     * Gets MIME type by Extension
     *
     * @param string $extension
     * @param bool $defaultHtml
     * @return string
     */
    public static function getMimebyExtension($extension, $defaultHtml = true)
    {
        foreach (self::$extdata as $key => $value)
        {
            if ($extension == $value)
                return $key;
        }
        
        return ($defaultHtml) ? "text/html" : "application/octet-stream";
    }
    
    /**
     * Gets MIME type of a stream
     *
     * @param string $binaryData
     * @return string
     */
    public static function getMime(&$binaryData)
    {
        $fd = substr($binaryData, 0, 3072);
        
        foreach (self::$magicdata as $def)
        {
            $pos0 = $def[0];
            
            if ($pos0[0] == ">")
            {
                $pos0 = substr($pos0, 1);
                
                if (strpos($fd, $def[3], $pos0) !== false)
                    return ($def[4]);
            }
            else
            {
                $part = substr($fd, $pos0, $def[1]);
                
                if ($mask = $def[2])
                {
                    $value = 1 * ('0x' . bin2hex($part));
                    
                    if (($value & $mask) == $def[3])
                        return ($def[4]);
                }
                else if ($part == $def[3])
                    return ($def[4]);
            }
        }
        
        return "application/octet-stream";
    }
}

/**
 * Manages, registers and executes all Http Handlers
 * @package WebCore
 * @subpackage Web
 */
class HttpHandlerManager extends HelperBase
{
    /**
     * @var IndexedCollection
     */
    private static $handlers;
    private static $hasExecuted;
    
    /**
     * Gets the registered handler collection
     * @return IndexedCollection
     */
    public static function getHandlers()
    {
        if (is_null(self::$handlers))
            self::$handlers = new IndexedCollection();
            
        return self::$handlers;
    }
    
    /**
     * Determines whether the Handler Manager has executed all the registered handlers
     * @return bool
     */
    public function getHasExecuted()
    {
        return self::$hasExecuted === true;
    }
    
    /**
     * Iterates through every registered HttpHandler and executes the handleRequest method for each of them
     */
    public static function handleRequest()
    {
        if (self::$hasExecuted === true)
            throw new SystemException(SystemException::EX_INVALIDOPERATION, 'The request has already been handled.');
        foreach (self::getHandlers() as $handler)
        {
            $handler->handleRequest();
        }
        
        self::$hasExecuted = true;
    }
    
    /**
     * Registers an IHttpHandler object for execution. If the Handler manager has already
     * handled the request, this method will throw and invalid operation exception.
     * @param IHttpHandler
     */
    public static function registerHandler($handler)
    {
        if (self::$hasExecuted === true)
            throw new SystemException(SystemException::EX_INVALIDOPERATION, 'The request has already been handled.');
        if (!ObjectIntrospector::isImplementing($handler, 'IHttpHandler'))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter $handler must implement IHttpHandler');
        
        self::getHandlers()->addItem($handler);
    }
}

/**
 * Handles File Downloads
 * 
 * @package WebCore
 * @subpackage Web
 */
class FileHttpHandler extends ObjectBase implements IHttpHandler
{
    /**
     * Handles file download
     */
    public function handleRequest()
    {
        $queryStringVars = HttpRequest::getInstance()->getQueryStringVars();
        $fileName        = '';
        
        if ($queryStringVars->keyExists("_file"))
        {
            if ($queryStringVars->keyExists("_fileName"))
                $fileName = $queryStringVars->getValue("_fileName");
            
            $file = HttpContext::getTempDir() . $queryStringVars->getValue("_file");
        
            if (file_exists($file))
            {
                $content = file_get_contents($file);
                
                if ($fileName == '')
                {
                    $mime     = MimeResolver::getMime($content);
                    $ext      = MimeResolver::getExtension($mime);
                    $fileName = "unknownfile." . $ext;
                }
                else
                {
                    $ext  = explode(".", $fileName);
                    $ext  = $ext[count($ext) - 1];
                    $mime = MimeResolver::getMimebyExtension($ext);
                }
                
                header('Content-Type: ' . $mime);
                header('Content-Length: ' . strlen($content));
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                
                echo $content;
            }
            
            exit(0);
        }
    }
}

/**
 * Handles Image previewing
 * 
 * @package WebCore
 * @subpackage Web
 */
class ImageHttpHandler extends ObjectBase implements IHttpHandler
{
    /**
     * Handles images and thumbnails
     */
    public function handleRequest()
    {
        $queryStringVars = HttpRequest::getInstance()->getQueryStringVars();
        $fileName        = '';
        
        if ($queryStringVars->keyExists("_file_img_handler"))
        {
            if ($queryStringVars->keyExists("_fileName"))
                $fileName = $queryStringVars->getValue("_fileName");
            
            $img = HttpContext::getTempDir() . $queryStringVars->getValue("_file_img_handler");
            
            if (file_exists($img))
            {
                if ($fileName == '')
                    $fileName = 'image.jpg';
                
                header("Content-type: image/jpg");
                $content = file_get_contents($img);
                
                if ($queryStringVars->keyExists("option"))
                {
                    header("Content-Disposition: inline; filename=" . $fileName);
                    $thumb = imagecreatefromstring($content);
                }
                else
                {
                    $source = imagecreatefromstring($content);
                    $thumb  = ImageHelper::createThumbnail($source, 140, 140);
                }
                
                imagejpeg($thumb);
            }
            else
            {
                header("Content-type: image/gif");
                $missing = ImageHelper::createDummy();
                imagegif($missing);
            }
            
            exit(0);
        }
    }
}

/**
 * Handles resource compression
 * 
 * @package WebCore
 * @subpackage Web
 */
class ResourcesHttpHandler extends ObjectBase implements IHttpHandler
{
    /**
     * Handles resource compression from Memcached or filesystem
     */
    public function handleRequest()
    {
        $queryStringVars = HttpRequest::getInstance()->getQueryStringVars();
        $fileName        = '';
        
        if ($queryStringVars->keyExists("_resource_hash") || $queryStringVars->keyExists("_cache_hash"))
        {
            $ext = "js";
        
            if ($queryStringVars->keyExists("_resource_ext"))
                $ext = $queryStringVars->getValue("_resource_ext");
            
            header("Content-Type: text/" . $ext);
            ResourcesCompressor::getInstance()->compressResource();
            
            if ($queryStringVars->keyExists("_resource_hash"))
            {
                $hash  = $queryStringVars->getValue("_resource_hash");
                $cache = CacheManagerBase::fromSettings();
                
                if ($cache->contains($hash))
                    echo $cache->get($hash);
            }
            else
            {
                chdir(dirname($_SERVER['SCRIPT_FILENAME']));
                $hash = $queryStringVars->getValue("_cache_hash");
                
                $path = HttpContext::getDocumentRoot() . "cache/" . $hash;
                
                if (file_exists($path))
                    echo file_get_contents($path);
            }
            
            exit(0);
        }
    }
}

/**
 * Represents a Uniform Resource Identifier (URI)
 * Provides functionality to parse and modify a given Uri
 * 
 * @package WebCore
 * @subpackage Web
 */
class UniformResourceIdentifier extends SerializableObjectBase
{
    
    protected $scheme;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $path;
    protected $query;
    protected $fragment;
    
    /**
     * Creates a new instance of this class
     * @param string $uri Optional uri to decompose. If not provided, tries to read the current request Uri
     */
    public function __construct($uri = '')
    {
        if (!is_string($uri)) throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'uri parameter expects a string');
        
        $contextInfo = HttpContextInfo::getInstance();
        if ($uri === '') $uri = $contextInfo->getRequestUri();
        $components = parse_url($uri);
        
        $this->setScheme(isset($components['scheme']) ? $components['scheme'] : 'http');
        $this->setHost(isset($components['host']) ? $components['host'] : $contextInfo->getRequestHost());
        $this->setPort(isset($components['port']) ? $components['port'] : '');
        $this->setUsername(isset($components['user']) ? $components['user'] : '');
        $this->setPassword(isset($components['pass']) ? $components['pass'] : '');
        $this->setPath(isset($components['path']) ? $components['path'] : $contextInfo->getScriptVirtualPath());
        $this->setQuery(isset($components['query']) ? $components['query'] : $contextInfo->getRequestQueryString());
        $this->setFragment(isset($components['fragment']) ? $components['fragment'] : '');
    }
    
    /**
     * Creates a new instance of this class
     * @return UniformResourceIdentifier
     */
    public static function createInstance()
    {
        return new UniformResourceIdentifier('');
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Converts the URI to a string
     * @return string
     */
    public function toString()
    {
        $result = $this->getScheme() . '://';
        $hasUsername = $this->getUsername() != '';
        $hasPassword = $this->getPassword() != '';
        if ($hasUsername && $hasPassword)
        {
            $result .= $this->getUsername() . ':' . $this->getPassword() . '@';
        }
        elseif($hasUsername && !$hasPassword)
        {
            $result .= $this->getUsername() . '@';
        }
        
        $result .= $this->getHost() . $this->getPath();        
        if ($this->getQuery() != '') $result .= '?' . $this->getQuery();
        if ($this->getFragment() != '') $result .= '#' . $this->getFragment();
        
        return $result;
    }
    
    /**
     * Gets the URI as a base64-encoded string
     * @return string
     */
    public function toBase64String()
    {
        return base64_encode($this->toString());
    }
    
    /**
     * Removes a query string variable name-value pair from the query string
     * @param string $varName The name of the variable to remove from the query string
     */
    public function removeQueryVariable($varName)
    {
        $queryStr = '';
        $vars = array();
        parse_str($this->getQuery(), $vars);
        
        $keys = array_keys($vars);
        for ($i = 0; $i < count($keys); $i++)
        {
            $keyName = $keys[$i];
            if ($keyName == $varName) continue;
            $value = $vars[$keyName];
            $queryStr .= urlencode($keyName) . '=' . urlencode($value) . '&';
        }
        
        $queryStr = StringHelper::replaceEnd($queryStr, '&', '');
        $this->setQuery($queryStr);
    }
    
    /**
     * Adds a Query string name-value pair to the query string
     * @param string $varName The name of the variable to add
     * @param string $value The value of the variable
     */
    public function addQueryVariable($varName, $value)
    {
        $queryStr = $this->getQuery();
        $queryStr .= '&' . urlencode($varName) . '=' . urlencode($value);
        $queryStr = StringHelper::replaceStart($queryStr, '&', '');
        $this->setQuery($queryStr);
    }
    
    public function getScheme()
    {
        return $this->scheme;
    }
    
    public function setScheme($value)
    {
        $this->scheme = $value;
    }
    
    public function getHost()
    {
        return $this->host;
    }
    
    public function setHost($value)
    {
        $this->host = $value;
    }
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function setPort($value)
    {
        $this->port = $value;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function setUsername($value)
    {
        $this->username = $value;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($value)
    {
        $this->password = $value;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function setPath($value)
    {
        $this->path = $value;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
    
    public function setQuery($value)
    {
        $this->query = $value;
    }
    
    public function getFragment()
    {
        return $this->fragment;
    }
    
    public function setFragment($value)
    {
        $this->fragment = $value;
    }
}
?>