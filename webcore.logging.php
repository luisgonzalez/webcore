<?php
/**
 * @package WebCore
 * @subpackage Logging
 * @version 1.0
 * 
 * Provides logging mechanisms for warnings, notices, exceptions and notes.
 * A LogManager is configured under app.settings along with a persistence layer.
 *
 * @todo Allow to FileLogger log in one file per day in a specified folder
 * 
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @author Mario Di Vece <mario@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.application.php";

/**
 * Interface for LogManager
 *
 * @package WebCore
 * @subpackage Logging
 */
interface ILogManager extends IHelper
{
    /**
     * Logs an exception
     */
    public static function logException($ex);
    
    /**
     * Logs a warning
     */
    public static function logWarning($message);
    
    /**
     * Logs information
     */
    public static function logInfo($message);
    
    /**
     * Logs debug information
     */
    public static function debug($message);
    
    /**
     * Logs an user's action
     */
    public static function logUserAction($message);
    
    /**
     * Logs a SQL query
     */
    public static function logSQLQuery($sqlStatement, $params);
}

/**
 * Interface for a Log Provider within a log manager
 * This interface is used as an attribute
 * @package WebCore
 * @subpackage Logging
 */
interface ILogProvider extends ILogManager
{
    
}

/**
 * Provides a singleton for LogManager
 *
 * @package WebCore
 * @subpackage Logging
 */
class LogManager extends HelperBase implements ISingleton, ILogManager
{
    const LOGGER_FILE = 'FileLogProvider';
    const LOGGER_DB = 'DatabaseLogProvider';
    
    const LOG_LEVEL_DISABLED = 0;
    const LOG_LEVEL_EXCEPTION = 1;
    const LOG_LEVEL_WARNING = 2;
    const LOG_LEVEL_INFORMATION = 3;
    const LOG_LEVEL_DEBUG = 4;
    const LOG_LEVEL_DEBUGINCLIENT = 5;
    
    private static $logLevel = 1;
    
    /**
     * Gets log level
     *
     * @return int
     */
    public static function getLogLevel()
    {
        if (!self::isLoaded())
            self::getInstance();
        return self::$logLevel;
    }
    
    /**
     * Sets log level
     *
     * @param int $level
     */
    public static function setLogLevel($level)
    {
        if (!self::isLoaded())
            self::getInstance();
        self::$logLevel = $level;
    }
    
    /**
     * @var IndexedCollection
     */
    private static $__instance = null;
    
    /**
     * Returns true if DEBUG is enabled
     *
     * @return bool
     */
    public static function isDebug()
    {
        return (intval(self::getLogLevel()) >= intval(self::LOG_LEVEL_DEBUG));
    }
    
    /**
     * Returns true if DEBUG in Client is enabled
     *
     * @return bool
     */
    public static function isDebugInClient()
    {
        return (self::getLogLevel() >= self::LOG_LEVEL_DEBUGINCLIENT);
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
    
    /**
     * Gets current instance using Settings
     *
     * @return IndexedCollection
     */
    public static function getInstance()
    {
        
        if (self::isLoaded() === false)
        {
            $logSettings      = Settings::getValue(Settings::SKEY_LOG);
            self::$__instance = new IndexedCollection();
            set_error_handler(array(
                'LogManager',
                'errorHandler'
            ));
            set_exception_handler(array(
                'LogManager',
                'exceptionHandler'
            ));
            
            foreach ($logSettings[Settings::KEY_LOG_PROVIDER] as $logClassName)
                self::$__instance->addItem(new $logClassName);
            
            self::setLogLevel($logSettings[Settings::KEY_LOG_LEVEL]);
            if (self::isDebug())
            {
                ini_set('display_errors', 1);
            }
            else
            {
                ini_set('display_errors', 0);
            }
        }
        
        return self::$__instance;
    }
    
    /**
     * Error handler
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     *
     * @return bool
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $message = "$errstr \n($errfile : $errline)";
        
        switch ($errno)
        {
            case E_RECOVERABLE_ERROR:
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                throw new Exception($message, $errno);
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                self::logWarning($message);
                break;
            default:
                self::logInfo($message);
                break;
        }
        
        return true;
    }
    
    /**
     * Exception handler
     *
     * @param Exception $exception
     */
    public static function exceptionHandler($exception)
    {
        if (self::isDebug())
            echo "<div><pre>" . $exception . "</pre></div>";
        
        self::logException($exception);
    }
    
    /**
     * Logs debug information.
     *
     * @param string $message
     */
    public static function debug($message)
    {
        if (self::isDebug())
        {
            foreach (self::getInstance() as $logManager)
                $logManager->debug($message);
                
            if (self::isDebugInClient())
                Controller::writeClientLog('log', $message);
        }
    }
    
    /**
     * Logs an exception.
     *
     * @param Exception $ex
     */
    public static function logException($ex)
    {
        if (self::getLogLevel() === self::LOG_LEVEL_DISABLED) return;
        foreach (self::getInstance() as $logManager)
            $logManager->logException($ex);
    }
    
    /**
     * Logs a warning.
     *
     * @param string $message
     */
    public static function logWarning($message)
    {
        if (self::getLogLevel() === self::LOG_LEVEL_DISABLED) return;
        if (self::getLogLevel() >= self::LOG_LEVEL_WARNING)
        {
            foreach (self::getInstance() as $logManager)
                $logManager->logWarning($message);
        }
        
        if (self::isDebugInClient())
            Controller::writeClientLog('warn', $message);
    }
    
    /**
     * Logs information.
     *
     * @param string $message
     */
    public static function logInfo($message)
    {
        if (self::getLogLevel() === self::LOG_LEVEL_DISABLED) return;
        if (self::getLogLevel() >= self::LOG_LEVEL_INFORMATION)
        {
            foreach (self::getInstance() as $logManager)
                $logManager->logInfo($message);
        }
    }
    
    /**
     * Logs an user's action
     *
     * @param string $message
     */
    public static function logUserAction($message)
    {
        if (self::getLogLevel() === self::LOG_LEVEL_DISABLED) return;
        foreach (self::getInstance() as $logManager)
            $logManager->logUserAction($message);
    }
    
    /**
     * Logs a SQL query
     *
     * @param string $sqlStatement
     * @param array $params
     */
    public static function logSQLQuery($sqlStatement, $params)
    {
        if (self::getLogLevel() === self::LOG_LEVEL_DISABLED) return;
        $paramsValues = array();
        
        foreach ($params as $param)
            $paramsValues[] = $param->__toString();
        
        foreach (self::getInstance() as $logManager)
            $logManager->logSQLQuery($sqlStatement, $paramsValues);
    }
}

/**
 * Provides file system-based logging
 *
 * @package WebCore
 * @subpackage Logging
 */
class FileLogProvider extends HelperBase implements ILogProvider
{
    /**
     * Logs a fired exception.
     *
     * @param Exception $ex
     */
    public static function logException($ex)
    {
        $message = "[Code = " . $ex->getCode() . ", Message = " . $ex->getMessage() . ", File = " . $ex->getFile() . ", Line = " . $ex->getLine() . ", StackTrace = " . $ex->getTraceAsString() . "]";
        
        self::writeLog('exception', $message);
    }
    
    /**
     * Logs a fired warning.
     *
     * @param string $message
     */
    public static function logWarning($message)
    {
        self::writeLog('warning', $message);
    }
    
    /**
     * Logs a fired Info.
     *
     * @param string $message
     */
    public static function logInfo($message)
    {
        self::writeLog('info', $message);
    }
    
    /**
     * Logs a user-defined log message.
     *
     * @param string $message
     */
    public static function debug($message)
    {
        self::writeLog('debug', $message);
    }
    
    /**
     * Logs a user's action
     *
     * @param string $message
     */
    public static function logUserAction($message)
    {
        self::writeLog('user', $message);
    }
    
    /**
     * Logs a SQL query
     *
     * @param string $sqlStatement
     * @param array $params
     */
    public static function logSQLQuery($sqlStatement, $params)
    {
        $content = "SQL = $sqlStatement; Params = " . implode(", ", $params);
        
        self::writeLog('sqlquery', $content);
    }
    
    /**
     * Writes a message to the log file
     *
     * @param string $action
     * @param string $message
     */
    private static function writeLog($action, $message)
    {
        $logSettings = Settings::getValue(Settings::SKEY_LOG);
        $logFile     = HttpContext::getDocumentRoot() . $logSettings[Settings::KEY_LOG_FILE];
        
        $userId      = "(Anonymous)";
        $currentUser = FormsAuthentication::getUser();
        
        if (is_null($currentUser) === false)
            $userId = $currentUser->getUserId();
            
        @file_put_contents($logFile, ">>$action\t" . date('Y-m-d H:i:s') . "\tUserId\t" . $userId . "\t" . $message . "\r\n", FILE_APPEND);
    }
}

/**
 * Provides database-agnostic logging
 *
 * @package WebCore
 * @subpackage Logging
 */
class DatabaseLogProvider extends HelperBase implements ILogProvider
{
    /**
     * Logs a fired exception.
     *
     * @param Exception $ex
     */
    public static function logException($ex)
    {
        $message = "[Code = " . $ex->getCode() . ", Message = " . $ex->getMessage() . ", File = " . $ex->getFile() . ", Line = " . $ex->getLine() . ", StackTrace = " . $ex->getTraceAsString() . "]";
        self::writeLog('Exception', $message);
    }
    
    /**
     * Logs a fired warning.
     *
     * @param string $message
     */
    public static function logWarning($message)
    {
        self::writeLog('Warning', $message);
    }
    
    /**
     * Logs information.
     *
     * @param string $message
     */
    public static function logInfo($message)
    {
        self::writeLog('Information', $message);
    }
    
    /**
     * Logs a user-defined debug message.
     *
     * @param string $message
     */
    public static function debug($message)
    {
        self::writeLog('Debug', $message);
    }
    
    /**
     * Logs a SQL query
     * @todo Remove very long parameters (thinking BLOBs here)
     * @param string $sqlStatement
     * @param array $params
     */
    public static function logSQLQuery($sqlStatement, $params)
    {
        $content = "SQL = " . $sqlStatement . "; Params = " . implode(", ", $params);
        
        self::writeLog('SQLQuery', $content);
    }
    
    /**
     * Logs a user's action
     *
     * @param string $message
     */
    public static function logUserAction($message)
    {
        self::writeLog('User', $message);
    }
    
    /**
     * Writes a log entry to the database
     * @todo Detect if the table exists and use simple queries to perform logging (no entities to make it provider-agnostic)
     * @param string $action
     * @param string $message
     */
    private static function writeLog($action, $message)
    {
        $originalLogLevel = LogManager::getLogLevel();
        LogManager::setLogLevel(LogManager::LOG_LEVEL_DISABLED);
        
        $logSettings = Settings::getValue(Settings::KEY_LOG);
        $logEntity   = $logSettings[Settings::KEY_LOG_LOGENTITY];
            
        try
        {            
            $userId == "(Anonymous)";
            $currentUser = FormsAuthentication::getUser();
            
            if (is_null($currentUser) === false)
                $userId = $currentUser->getUserId();
            
            $logEntry          = DataContext::getInstance()->getAdapter($logEntity)->defaultEntity();
            $logEntry->LogDate = date("Y-m-d H:i:s");
            $logEntry->Action  = $action;
            $logEntry->User    = $userId;
            $logEntry->Message = $message;
            
            DataContext::getInstance()->getAdapter($logEntity)->insert($logEntry);            
        }
        catch (Exception $ex)
        {
            $exString = "<pre>DatabaseLogManager Exception\nYou need an entity '$logEntity' with columns LogDate, Action, User and Message.\n"
                . $ex->getLine() . " on '" . $ex->getFile() . "'\n"
                . $ex->getCode() . ": " . $ex->getMessage() . "\nStack Trace:\n" . $ex->getTraceAsString() . "</pre>";
            LogManager::setLogLevel($originalLogLevel);
            HttpResponse::write($exString);
            HttpResponse::end();
            return;
        }
        
        LogManager::setLogLevel($originalLogLevel);
    }
}

/**
 * Represents a helper to take and run snapshots
 *
 * @todo Complete this
 * 
 * @package WebCore
 * @subpackage Logging
 */
class HttpContextSnapshotHelper extends HelperBase implements IHelper
{
    /**
     * Takes a snapshot
     *
     * @return string
     */
    public static function takeSnapshot()
    {
        $state = array(
            "request" => HttpContext::getRequest()->getRequestVars(),
            "session" => HttpContext::getSession(),
            "server" => HttpContextInfo::getInstance(),
            "url" => HttpContext::getInfo()->getValue('PHP_SELF')
        );
        
        return JsonSerializer::serialize($state);
    }
    
    /**
     * Loads a snapshot and redirects to it
     *
     * @param string $state
     */
    public static function loadSnapshot($state)
    {
        $stateData = JsonSerializer::deserialize($state, "array");
        
        $session = HttpContext::getSession();
        $session->clear();
        
        foreach ($stateData['session'] as $key => $value)
            $session->setItem($key, $value);
        
        Controller::transfer($stateData['url'], $stateData['request']);
    }
}
?>