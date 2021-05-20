<?php
/**
 * @package WebCore
 * @subpackage Controller
 * 
 * The Controller is used to bind Views to Models through ControllerEvents registered in the model as callbacks
 * ControllerAction classes are used to avoid re-implementation of patterns such
 * as data saving and data updating in simple scenarios.
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";

/**
 * Represents a generic controller action
 *
 * @package WebCore
 * @subpackage Controller
 */
interface IControllerAction extends IObject
{
    /**
     * Executes the action and returns whether the executed action was completed successfully.
     * @return bool
     */
    public function execute();
}

/**
 * Represents an event that was raised by an IEventTrigger object
 *
 * @package WebCore
 * @subpackage Controller
 */
class ControllerEvent extends ObjectBase
{
    /**
     * @var IEventTrigger
     */
    private $source;
    private $value;
    
    /**
     * Creates a new instance of this class
     *
     * @param IEventTrigger $source
     * @param string $value
     */
    public function __construct(&$source, $value)
    {
        $this->source = $source;
        $this->value  = $value;
    }
    
    /**
     * Gets the name of the event
     *
     * @return string
     */
    public function getName()
    {
        return $this->source->getEventName();
    }
    
    /**
     * Gets the value of the event
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Get the object, by reference, from which the event was triggered
     *
     * @return IEventTrigger
     */
    public function &getSource()
    {
        return $this->source;
    }
}

/**
 * Defines the main event-handling controller
 * For an event to be fired, two conditions must be met:
 * 1. IModel->getName() is a key in HttpContext->getRequestVars()
 * 2. eventValue_{IModel->getName}_{IEventTrigger->getEventName()} is a key in HttpContext->getRequestVars() and its value is not an empty string
 *
 * For an event to be handled, use the registerEventHandler() method and define a function with the following signature:
 * function on{EventName}(IModel &$sender, ControllerEvent &$eventArgs)
 *
 * @package WebCore
 * @subpackage Controller
 */
class Controller extends ObjectBase
{
    const PREFIX_POSTBACKFLAG = 'postBackFlag_';
    const PREFIX_STATE = 'state_';
    const PREFIX_EVENTVALUE = 'eventValue_';
    
    /**
     * @var IndexedCollection
     */
    static private $logInClientBuffer;
    /**
     * @var KeyedCollection
     */
    static private $eventHandlers;
    
    /**
     * Registers a callback function to handle an event.
     * The handler's signarutre must take a ControllerEvent by reference.
     * For example: function onButton_Submit(Form &$model, ControllerEvent &$event)
     *
     * @param string $eventName The name of the event to register
     * @param callback $callback A standard PHP callback to a function that handles the event.
     */
    public static function registerEventHandler($eventName, $callback)
    {
        if (self::$eventHandlers === null)
            self::$eventHandlers = new KeyedCollection();
        self::$eventHandlers->setValue($eventName, $callback);
    }
    
    /**
     * Removes the event handler associated with a named event.
     *
     * @param string $eventName
     */
    public static function unregisterEventHandler($eventName)
    {
        if (self::$eventHandlers === null)
            self::$eventHandlers = new KeyedCollection();
        self::$eventHandlers->removeItem($eventName);
    }
    
    /**
     * Determines if the model has caused a postback through its view
     * @param IModel $model
     * @return bool
     */
    public static function isPostBack(&$model)
    {
        if (ObjectIntrospector::isImplementing($model, 'IRootModel') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model must implement IRootModel');
        
        return HttpContext::getRequest()->getRequestVars()->keyExists(self::getPostBackFlagName($model));
    }
    
    /**
     * Calls the registered event handlers.
     * To register an event handler callback, use the registerEventHandler method.
     *
     * @param IModel $model
     * @return int The number of handled events
     */
    public static function handleEvents(&$model)
    {
        if (ObjectIntrospector::isImplementing($model, 'IRootModel') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model must implement IRootModel');
        
        $handledEventCount = 0;
        
        if (self::$eventHandlers == null || self::$eventHandlers->isEmpty())
            return $handledEventCount;
        $events = self::getEvents($model);
        
        for ($i = 0; $i < $events->getCount(); $i++)
        {
            $event =& $events->getItem($i);
            
            if (self::$eventHandlers->keyExists($event->getName()) === true)
            {
                call_user_func(self::$eventHandlers->getValue($event->getName()), $model, $event);
                $handledEventCount++;
            }
        }
        
        return $handledEventCount;
    }
    
    /**
     * Determines whether at least 1 event is supposed to get triggered.
     * This is a simple way to determine if the model has fired events through its view without handling them.
     *
     * @param IModel $model
     * @return bool
     */
    public static function hasEvents(&$model)
    {
        return (self::getEvents($model)->getCount() > 0) ? true : false;
    }
    
    /**
     * Returns an IndexedCollection of ControllerEvent
     *
     * @param IModel $model
     * @return IndexedCollection
     */
    public static function &getEvents(&$model)
    {
        $events  = new IndexedCollection();
        $request = HttpContext::getRequest()->getRequestVars();
        $files   = HttpContext::getRequest()->getPostedFiles();
        
        // For the root model
        if (ObjectIntrospector::isImplementing($model, 'IEventTrigger') === true)
        {
            $eventValueKey = self::PREFIX_EVENTVALUE . $model->getName() . '_' . $model->getEventName();
            
            if ($request->keyExists($eventValueKey) && $request->getValue($eventValueKey) != '')
            {
                $event = new ControllerEvent($model, $request->getValue($eventValueKey));
                $events->addItem($event);
            }
        }
        
        // For its child controls
        $eventTriggerControls = self::getEventTriggeringModels($model);
        
        foreach ($eventTriggerControls as $control)
        {
            $controlName   = $control->getName();
            $eventValueKey = self::PREFIX_EVENTVALUE . $controlName . '_' . $control->getEventName();
            
            if (($request->keyExists($controlName) || $files->keyExists($controlName)) && $request->keyExists($eventValueKey) && $request->getValue($eventValueKey) != '')
            {
                $event = new ControllerEvent($control, $request->getValue($eventValueKey));
                $events->addItem($event);
            }
        }
        
        return $events;
    }
    
    /**
     * Internal, recursive function to scan for IEventTrigger objects inside of $definedVars
     *
     * @param array[mixed] $definedVars
     */
    static private function &getEventTriggeringModels(&$definedVars)
    {
        $ignoreList = array(
            "GLOBALS",
            "_REQUEST",
            "_SERVER",
            "_POST",
            "_GET",
            "_COOKIES",
            "_SESSION",
            "_COOKIE",
            "_FILES",
            "HTTP_POST_VARS",
            "HTTP_GET_VARS",
            "HTTP_COOKIE_VARS",
            "HTTP_SERVER_VARS",
            "HTTP_ENV_VARS",
            "HTTP_SESSION_VARS",
            "_ENV",
            "PHPSESSID",
            "SESS_DBUSER",
            "SESS_DBPASS",
            "HTTP_COOKIE"
        );
        
        $result = array();
        reset($definedVars);
        
        while (list($key, $value) = each($definedVars))
        {
            // Do not process items in the ignore list
            // Cast key to string to avoid NULL == 0 behaviour
            if (in_array("" . $key, $ignoreList))
                continue;
            
            if (is_object($value))
            {
                if (strstr($key, '__parent'))
                    continue;
                
                if (ObjectIntrospector::isImplementing($value, 'IEventTrigger') === true)
                    $result[] = $value;
                
                $subresult = self::getEventTriggeringModels($value);
                if (count($subresult) > 0)
                    $result = array_merge($result, $subresult);
            }
            elseif (is_array($value))
            {
                $subresult = self::getEventTriggeringModels($value);
                if (count($subresult) > 0)
                    $result = array_merge($result, $subresult);
            }
        }
        
        return $result;
    }
    
    /**
     * Makes a transfer to another location by POST
     * using the params
     *
     * @param string $location
     * @param array $params
     * @param bool $renderDependencies
     */
    public static function transfer($location, $params = null, $renderDependencies = true)
    {
        if ($renderDependencies && HtmlViewManager::hasRendered() === false)
            HtmlViewManager::render();
        
        if (is_null($params) === true)
            $params = array();
        $jsonParams = json_encode($params);
        
        HttpResponse::write('<script defer="defer" type="text/javascript">');
        HttpResponse::write("controller.transfer('$location', '$jsonParams');");
        HttpResponse::write("</script>");
        HttpResponse::end();
    }
    
    /**
     * Creates a transfer function on the client side.
     * Returns the corresponding onclick javascript code to place inside an HTML tag.
     * Use this method to create POST-based hyperlinks.
     * 
     * @example $onClick = getOnClickTransfer('goToStatefulPage', $_SERVER['PHP_SELF'], array( "state0" <= 1, "state2" <= "x")); echo '<a href="javascript:void(0);" onclick="' . $onClick . '>Back</a>;"';
     * @param string $location The absolute or relative URL to post to
     * @param array $params A keyed array containing the name-value pairs to post to $location
     * @return string
     */
    public static function getOnClickTransfer($location, $params)
    {
        $jsonParams = json_encode($params);
        $jsonParams = str_replace('"', "\'", $jsonParams);
        
        return "return controller.transfer('$location', '$jsonParams');";
    }
    
    /**
     * Download a file
     *
     * @param string $fileContent
     */
    public static function downloadFile($fileContent, $clean = true, $fileName = '')
    {
        if ($clean == true)
            $fileContent = stripslashes($fileContent);
        
        $hash = md5($fileContent);
        file_put_contents(HttpContext::getTempDir() . $hash, $fileContent);
        
        $location = $_SERVER["PHP_SELF"] . '?_file=' . $hash;
        
        if ($fileName != '')
            $location .= '&_fileName=' . $fileName;
        
        echo "<script defer='defer'>";
        echo "window.open(document.location.href + '$location');";
        echo "</script>";
    }
    
    /**
     * Writes a log entry on the client side.
     *
     * @param string level
     * @param string $message
     */
    public static function writeClientLog($level, $message)
    {
        if (self::$logInClientBuffer === null)
            self::$logInClientBuffer = new IndexedCollection();
        
        $message = str_replace("\r\n", ' ', $message);
        $message = str_replace("\n", ' ', $message);
        $message = htmlentities($message, ENT_QUOTES);
        
        $logLine = "console.$level('$message');";
        self::$logInClientBuffer->addItem($logLine);
    }
    
    /**
     * Gets the name of the request field used to determine whether a model has psoted back or not.
     *
     * @param IRootModel $model
     * @return string
     */
    public static function getPostBackFlagName($model)
    {
        return self::PREFIX_POSTBACKFLAG . $model->getName();
    }
    
    /**
     * Gets logs buffer
     *
     * @return IndexedCollection
     */
    public static function getLogsBuffer()
    {
        return self::$logInClientBuffer;
    }
}

/**
 * Provides a base implementation for a Form, Entity and DataSource action
 * @package WebCore
 * @subpackage Controller
 */
abstract class FormControllerActionBase extends SerializableObjectBase implements IControllerAction
{
    /**
     * @var string
     */
    protected $entityTypeName;
    /**
     * @var Form
     */
    protected $model;
    /**
     * KeyedCollection
     */
    protected $dataSource;
    
    /**
     * Creates a new Instance of this class.
     * @param Form $model
     * @param string $entityTypeName
     * @param KeyedCollection $dataSource If left null, the dataSource becomes the Request::getRequestVars()
     */
    public function __construct($model, $entityTypeName, $dataSource = null)
    {
        if (is_null($dataSource))
            $dataSource = HttpContext::getRequest()->getRequestVars();
        if (is_null($model))
            $model = new Form('ISerializable', 'ISerializable');
        
        if (!ObjectIntrospector::isA($model, 'Form'))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter 'model' must be of type 'Form'");
        if (!ObjectIntrospector::isA($dataSource, 'KeyedCollection'))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter 'dataSource' must be of type 'KeyedCollection'");
        if (!is_string($entityTypeName))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter 'entityTypeName' must be of type 'string'");
        
        $this->model          = $model;
        $this->entityTypeName = $entityTypeName;
        $this->dataSource     = $dataSource;
    }
    
    /**
     * @return Form
     */
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * @return string
     */
    public function getEntityTypeName()
    {
        return $this->entityTypeName;
    }
    
    /**
     * @return KeyedCollection
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }
}

/**
 * Provides a simple way of automatically populating form data based on a data source.
 * @package WebCore
 * @subpackage Controller
 */
class FormPopulateControllerAction extends FormControllerActionBase
{
    /**
     * Creates a new Instance of this class.
     * @param Form $model
     * @param string $entityTypeName
     * @param KeyedCollection $dataSource If left null, the dataSource becomes the Request::getRequestVars()
     */
    public function __construct($model, $entityTypeName, $dataSource = null)
    {
        parent::__construct($model, $entityTypeName, $dataSource);
    }
    
    /**
     * Executes the surrent action
     * 
     */
    public function execute()
    {
        // determine if the primary keys were provided in the request
        $primaryKeyNames = call_user_func(array(
            $this->entityTypeName,
            'getPrimaryKeyFieldNames'
        ));
        $keyCollection   = new KeyedCollection();
        
        foreach ($primaryKeyNames as $priKey)
        {
            if ($this->getDataSource()->keyExists($priKey) && $this->getDataSource()->getValue($priKey) != '')
            {
                $value = $this->getDataSource()->getValue($priKey);
                $keyCollection->setValue($priKey, $value);
            }
        }
        
        $entityAdapter = DataContext::getInstance()->getAdapter($this->entityTypeName);
        $entity        = $entityAdapter->defaultEntity();
        if ($primaryKeyNames->getCount() === $keyCollection->getCount())
        {
            $entity = $entityAdapter->single($keyCollection);
            
            if (is_null($entity))
                throw new SystemException(SystemException::EX_KEYNOTFOUND, 'The entity with the specified identity was not not found.');
                
            $dataSource = $this->getDataSource();
            $entity->dataBind($dataSource);
            $this->model->dataBind($entity);
        }
        
        return true;
    }
    
    public static function createInstance()
    {
        return new FormPopulateControllerAction(null, '', null);
    }
}

/**
 * Provides basic functionality to create or update an entity from a Form model.
 * @package WebCore
 * @subpackage Controller
 */
class FormSaveControllerAction extends FormControllerActionBase
{
    const MODE_NONE = 'none';
    const MODE_INSERT = 'insert';
    const MODE_UPDATE = 'update';
    
    /**
     * @var string
     */
    protected $mode;
    
    /**
     * Creates a new Instance of this class.
     * @param Form $model
     * @param string $entityTypeName
     * @param KeyedCollection $dataSource If left null, the dataSource becomes the Request::getRequestVars()
     */
    public function __construct($model, $entityTypeName, $dataSource = null)
    {
        parent::__construct($model, $entityTypeName, $dataSource);
        
        $this->mode = self::MODE_NONE;
        
        // automatically try to detect the operating mode by inspecting the data source
        $primaryKeyNames = call_user_func(array(
            $entityTypeName,
            'getPrimaryKeyFieldNames'
        ));
        $keysFoundCount  = 0;
        foreach ($primaryKeyNames as $priKey)
        {
            if ($dataSource->keyExists($priKey) && $dataSource->getValue($priKey) != '')
                $keysFoundCount++;
        }
        
        if ($keysFoundCount === $primaryKeyNames->getCount())
            $this->mode = self::MODE_UPDATE;
        else
            $this->mode = self::MODE_INSERT;
    }
    
    public function execute()
    {
        $ds = $this->getDataSource();
        $this->getModel()->dataBind($ds);
        // validate the model bound to the data source
        $isValid = $this->getModel()->validate();
        
        // perform the data operation
        if ($isValid === true)
        {
            $entityAdapter = DataContext::getInstance()->getAdapter($this->getEntityTypeName());
            switch ($this->getDataMode())
            {
                case self::MODE_INSERT:
                    $entity = $entityAdapter->defaultEntity();
                    $entity->dataBind($this->getDataSource());
                    $entityAdapter->insert($entity);
                    break;
                case self::MODE_UPDATE:
                    $primaryKeyNames = call_user_func(array(
                        $this->getEntityTypeName(),
                        'getPrimaryKeyFieldNames'
                    ));
                    $keyCollection   = new KeyedCollection();
                    foreach ($primaryKeyNames as $priKey)
                    {
                        if ($this->getDataSource()->keyExists($priKey) && $this->getDataSource()->getValue($priKey) != '')
                        {
                            $value = $this->getDataSource()->getValue($priKey);
                            $keyCollection->setValue($priKey, $value);
                        }
                    }
                    $entity     = $entityAdapter->single($keyCollection);
                    $dataSource = $this->getDataSource();
                    $entity->dataBind($dataSource);
                    $entityAdapter->update($entity);
                    break;
                default:
                    throw new SystemException(SystemException::EX_INVALIDOPERATION, 'Could not execute action. No data mode was detected.');
                    break;
            }
        }
        
        return $isValid;
    }
    
    /**
     * Determines the operation mode for the execute method.
     * Modes are defined within this class a MODE-prefixed constants
     * @return string
     */
    public function getDataMode()
    {
        return $this->mode;
    }
    
    /**
     * Determines the operation mode for the execute method.
     * Modes are defined within this class a MODE-prefixed constants
     * @param string $value
     */
    public function setDataMode($value)
    {
        $this->mode = $value;
    }
    
    public static function createInstance()
    {
        return new FormSaveControllerAction(null, '', null);
    }
}
?>