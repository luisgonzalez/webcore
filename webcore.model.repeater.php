<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides models of controls in a repeater.
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.model.php";
require_once "webcore.model.validation.php";

/**
 * Represents a event manager in a models such as DataRepeater or Grid
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class RepeaterEventManagerControlBase extends ControlModelBase implements IEventTrigger
{
    protected $eventName;
    protected $eventValue;
    
    /**
     * Create a instance of this class
     *
     * @param string $name
     * @param string $eventName
     * @param string $defaultEventValue
     */
    protected function __construct($name, $eventName, $defaultEventValue)
    {
        parent::__construct($name, false);
        $this->eventName  = $eventName;
        $this->eventValue = $defaultEventValue;
        Controller::registerEventHandler($eventName, array(
            $this,
            'onEventRaised'
        ));
    }
    
    abstract public function onEventRaised(&$sender, &$event);
    
    /**
     * Gets event name
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }
    
    /**
     * Sets event name
     *
     * @param string $value
     */
    public function setEventName($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'EventName is read-only.');
    }
    
    /**
     * Gets event value
     *
     * @return string
     */
    public function getEventValue()
    {
        return $this->eventValue;
    }
    
    /**
     * Sets event value
     *
     * @param string $value
     */
    public function setEventValue($value)
    {
        $this->eventValue = $value;
    }
}

/**
 * Represents repeater model base.
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class DataRepeaterModelBase extends ContainerModelBase implements IStatefulModel
{
    protected $pageSize;
    protected $isPaged;
    /**
     * @var RepeaterPagingManager
     */
    protected $pagingManager;
    /**
     * @var DataRepeaterState
     */
    protected $state;
    /**
     * @var IndexedCollection
     */
    protected $dataItems;
    
    /**
     * Create a instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    protected function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
        
        $this->pagingManager = new RepeaterPagingManager($name . "Pager");
        $this->children->addControl($this->pagingManager);
        
        $this->state     = new DataRepeaterState();
        $this->isPaged   = false;
        $this->pageSize  = 20;
        $this->dataItems = new IndexedCollection();
    }
    
    /**
     * Gets the IndexedCollection of stdClass Objects from the data source.
     * The collection contains data only after the dataBind() method is called.
     *
     * @return IndexedCollection[KeyedCollection]
     */
    public function &getDataItems()
    {
        return $this->dataItems;
    }
    
    /**
     * Returns the state name
     *
     * @return string
     */
    public function getStateName()
    {
        return Controller::PREFIX_STATE . $this->getName();
    }
    
    /**
     * Gets the state object of the repeater.
     * 
     * @return DataRepeaterState
     */
    public function &getState()
    {
        return $this->state;
    }
    
    /**
     * Gets the paging size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }
    
    /**
     * Sets the paging size
     *
     * @param int $value
     */
    public function setPageSize($value)
    {
        $this->pageSize = $value;
    }
    
    /**
     * Sets if DataRepeater is pageable
     *
     * @param bool $value
     */
    public function setIsPaged($value)
    {
        $this->isPaged = $value;
    }
    
    /**
     * Gets true, if DataRepeater is pageable
     *
     * @return bool
     */
    public function getIsPaged()
    {
        return $this->isPaged;
    }
    
    /**
     * @return RepeaterPagingManager
     */
    public function &getPaginingManager()
    {
        return $this->pagingManager;
    }
}

/**
 * Represents the real-time state management object.
 * Holds user values for the repeater.
 *
 * @package WebCore
 * @subpackage Model
 */
class DataRepeaterState extends ControlStateBase
{
    protected $pageIndex;
    protected $pageCount;
    protected $totalRecordCount;
    
    /**
     * Creates a new instance of this class.
     *
     * @param int $pageIndex
     * @param int $pageCount
     * @param int $totalRecordCount
     */
    public function __construct($pageIndex = 0, $pageCount = -1, $totalRecordCount = -1)
    {
        parent::__construct();
        $this->pageIndex        = $pageIndex;
        $this->pageCount        = $pageCount;
        $this->totalRecordCount = $totalRecordCount;
    }
    
    /**
     * Resets records count
     *
     */
    public function resetRecordCount()
    {
        //$this->pageIndex = 0;
        $this->pageCount        = -1;
        $this->totalRecordCount = -1;
    }
    
    public function getPageIndex()
    {
        return $this->pageIndex;
    }
    public function setPageIndex($value)
    {
        $this->pageIndex = $value;
    }
    public function getPageCount()
    {
        return $this->pageCount;
    }
    public function setPageCount($value)
    {
        $this->pageCount = $value;
    }
    public function getTotalRecordCount()
    {
        return $this->totalRecordCount;
    }
    public function setTotalRecordCount($value)
    {
        $this->totalRecordCount = $value;
    }
    
    /**
     * Creates an instance from a Base 64 string
     *
     * @param string $data
     * @return GridState
     */
    public static function fromBase64($data)
    {
        return Base64Serializer::deserialize($data, get_class());
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RepeaterState
     */
    public static function createInstance()
    {
        return new DataRepeaterState();
    }
}

/**
 * Represents a generic event manager
 *
 * @package WebCore
 * @subpackage Model
 */
class RepeaterGenericEventManager extends RepeaterEventManagerControlBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, $name, '-1');
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic paging capabilities
     *
     * @param DataRepeaterModelBase $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        // do nothing
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RepeaterGenericEventManager
     */
    public static function createInstance()
    {
        return new RepeaterGenericEventManager('ISerializable');
    }
}

/**
 * Represents a generic event manager for common Editable Data Repeater events
 *
 * @package WebCore
 * @subpackage Model
 */
class EditableGridGenericEventManager extends RepeaterGenericEventManager
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic paging capabilities
     *
     * @param EditableDataRepeater $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        switch ($this->name)
        {
            case EditableDataRepeater::EVENTNAME_EDIT_ITEM:
                $editId = $event->getValue();
                $sender->setEditKey($editId);
                $dataItems = $sender->getDataItems();
                if ($dataItems->getCount() > 0)
                {
                    $key = $sender->getKey();
                    foreach ($dataItems as $item)
                    {
                        if ($item->$key === $editId)
                        {
                            $sender->dataBindForm($item);
                            return;
                        }
                    }
                }
                else
                {
                    LogManager::logWarning("Edit Item event for EditableDataRepeater '{$sender->getName()}' fired before dataBind().");
                }
        }
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RepeaterGenericEventManager
     */
    public static function createInstance()
    {
        return new EditableGridGenericEventManager('ISerializable');
    }
}

/**
 * Represents a repeater paging event manager
 *
 * @package WebCore
 * @subpackage Model
 */
class RepeaterPagingManager extends RepeaterEventManagerControlBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, 'GoPageIndex', '-1');
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic paging capabilities
     *
     * @param DataRepeaterModelBase $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        $sender->getState()->setPageIndex(intval($event->getValue()));
        $this->eventValue = $event->getValue();
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RepeaterPagingManager
     */
    public static function createInstance()
    {
        return new RepeaterPagingManager('ISerializable');
    }
}

/**
 * Provides the base implementation for a data item or column model.
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class RepeaterFieldModelBase extends ControlModelBase
{
    protected $caption;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        if (!is_string($caption))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = caption; must be a string.');
        
        parent::__construct($name, true);
        $this->caption = $caption;
    }
    
    /**
     * Gets the header caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Sets the header caption
     *
     * @param string $value
     */
    public function setCaption($value)
    {
        if (!is_string($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value; must be a string.');
        $this->caption = $value;
    }
}

/**
 * Represents a summary control
 *
 * @package WebCore
 * @subpackage Model
 */
class SummaryControl extends ControlModelBase
{
    protected $value;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     */
    public function __construct($name, $caption, $value = '')
    {
        parent::__construct($name, true);
        $this->value = $value;
    }
    
    /**
     * Determines the value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Determines the value
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return SummayControl
     */
    public static function createInstance()
    {
        return new SummayControl('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Provides the base implementation for a data-bound item model.
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class BoundRepeaterFieldModelBase extends RepeaterFieldModelBase
{
    protected $bindingMemberName;
    protected $isBindable;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName)
    {
        parent::__construct($name, $caption);
        $this->setBindingMemberName($bindingMemberName);
    }
    
    /**
     * Gets whether the filed is bindable.
     *
     * @return bool
     */
    public function getIsBindable()
    {
        return $this->isBindable;
    }
    
    /**
     * Sets whether the field is bindable.
     *
     * @param bool $value
     */
    public function setIsBindable($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->isBindable = $value;
    }
    
    /**
     * Gets the name of the data source member from which the column data is extracted upon data binding
     *
     * @return string
     */
    public function getBindingMemberName()
    {
        return $this->bindingMemberName;
    }
    
    /**
     * Sets the name of the data source member from which the column data is extracted upon data binding
     * If the value is an empty string, it will automatically set the isBindable property to false.
     * If the value is a non-empty string, it will automatically set the isBindable property to true.
     *
     * @param string $value
     */
    public function setBindingMemberName($value)
    {
        if (!is_string($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = bindingMemberName; must be a string.');
        
        if (trim($value) === '')
        {
            $this->bindingMemberName = '';
            $this->setIsBindable(false);
        }
        else
        {
            $this->bindingMemberName = trim($value);
            $this->setIsBindable(true);
        }
    }
    
    /**
     * Sets the binding member value. This operation is irrelevant and will throw and exception is called.
     * 
     * @param mixed $value
     */
    public function setValue($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The property is irrelevant in this control model.');
    }
    
    /**
     * Gets the binding member value. This operation is irrelevant and will throw and exception is called.
     * 
     * @return mixed
     */
    public function &getValue()
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The property is irrelevant in this control model.');
    }
}

/**
 * Provides the base class of a data item which has items to display and can also fire server-side events.
 * Use this class to implement data item models that are capable to raise events through their data items.
 * Specify which member in the data source provides the event value in the bindingMemberEventValue argument
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class EventDataItemBase extends BoundRepeaterFieldModelBase implements IEventTrigger
{
    protected $eventName;
    protected $bindingMemberEventValue;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $eventName
     * @param string $bindingMemberEventValue The name of the member to extract the eventValue information from when the column data is populated.
     */
    public function __construct($name, $caption, $eventName, $bindingMemberEventValue)
    {
        parent::__construct($name, $caption, $bindingMemberEventValue);
        $this->setEventName($eventName);
        $this->setBindingMemberEventValue($bindingMemberEventValue);
    }
    
    /**
     * Gets the name of the event to fire
     * @retun string
     */
    public function getEventName()
    {
        return $this->eventName;
    }
    
    /**
     * Sets the name of the event to fire
     * @param string $value
     */
    public function setEventName($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->eventName = $value;
    }
    
    /**
     * Unsupported. Event values are dynamically set by the source's BindingMemberEventValue
     * @return SystemException
     */
    public function getEventValue()
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'Event values are bound through BindingMemberEventValue.');
    }
    
    /**
     * Unsupported. Event values are dynamically set by the source's BindingMemberEventValue
     * @return SystemException
     */
    public function setEventValue($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'Event values are bound through BindingMemberEventValue.');
    }
    
    /**
     * Gets the data member that is used to dynamically populate the event value when the event is fired.
     * @return string
     */
    public function getBindingMemberEventValue()
    {
        return $this->bindingMemberEventValue;
    }
    
    /**
     * Sets the data member that is used to dynamically populate the event value when the event is fired.
     * @return string
     */
    public function setBindingMemberEventValue($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->bindingMemberEventValue = $value;
    }
}

/**
 * Represents a common field repeater item
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class DataRepeaterFieldModelBase extends BoundRepeaterFieldModelBase implements IValidatable, IBindingTargetMember
{
    /**
     * Represents the validator object that will perform the validattion
     *
     * @var IValidator
     */
    protected $validator;
    protected $isRequired;
    protected $errorMessage;
    protected $value;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName, $isRequired = true)
    {
        parent::__construct($name, $caption, $bindingMemberName);
        
        if (is_bool($isRequired) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = isRequired');
        
        $this->validator    = new BasicFieldValidator();
        $this->isRequired   = $isRequired;
        $this->errorMessage = '';
        $this->value        = '';
    }
    
    /**
     * Gets the Read Only
     *
     * @return bool
     */
    public function getIsReadOnly()
    {
        return false;
    }
    
    /**
     * Gets the validator object used to validate the value in the field.
     *
     * @return IValidator
     */
    public function getValidator()
    {
        return $this->validator;
    }
    
    /**
     * Sets the validator object used to validate the value in the field
     *
     * @param IValidator $value
     */
    public function setValidator($value)
    {
        if (ObjectIntrospector::isImplementing($this->validator, 'IValidator') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->validator = $value;
    }
    
    /**
     * Validates the current value of the model using the associated validator.
     *
     * @return bool
     */
    public function validate()
    {
        if (is_null($this->validator))
            return true;
        
        if (ObjectIntrospector::isImplementing($this->validator, 'IValidator') === true)
            return $this->validator->validate($this, '');
        
        return true;
    }
    
    /**
     * Gets whether a value is required for the control.
     *
     * @return bool
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }
    
    /**
     * Sets whether a value is required for the control.
     *
     * @param bool $value
     */
    public function setIsRequired($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->isRequired = $value;
    }
    
    /**
     * Sets the binding member value. This operation is irrelevant and will throw and exception is called.
     * 
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
     * Gets the binding member value. This operation is irrelevant and will throw and exception is called.
     * 
     * @return mixed
     */
    public function &getValue()
    {
        return $this->value;
    }
    
    /**
     * Gets whether the control field has errors.
     *
     * @return bool
     */
    public function hasErrorMessage()
    {
        if (trim($this->getErrorMessage()) == '')
            return false;
        else
            return true;
    }
    
    /**
     * Gets the current error message to display.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    
    /**
     * Sets the current error message to display. If the value is specified as an empty string,
     * the HasErrors property will be set to false. It will be set to true otherwise.
     *
     * @param string $value
     */
    public function setErrorMessage($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->errorMessage = $value;
    }
}

/**
 * Represents a common label field repeater item
 *
 * @package WebCore
 * @subpackage Model
 */
class LabelRepeaterField extends DataRepeaterFieldModelBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName = '')
    {
        if ($bindingMemberName === '')
            $bindingMemberName = $name;
        parent::__construct($name, $caption, $bindingMemberName);
        $this->isRequired = false;
    }
    
    /**
     * Determines whether this field is requiered.
     * Calling this function has no effect on the underlying variable. calling getIsRequired will always return false.
     * @return bool
     */
    public function setIsRequired($value)
    {
        // not supported
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return LabelRepeaterField
     */
    public static function createInstance()
    {
        return new LabelRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common text field repeater item
 *
 * @package WebCore
 * @subpackage Model
 */
class TextBoxRepeaterField extends DataRepeaterFieldModelBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName = '')
    {
        if ($bindingMemberName === '')
            $bindingMemberName = $name;
        parent::__construct($name, $caption, $bindingMemberName);
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return TextBoxRepeaterField
     */
    public static function createInstance()
    {
        return new TextBoxRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common text field repeater item for Integer input
 *
 * @package WebCore
 * @subpackage Model
 */
class IntegerRepeaterField extends DataRepeaterFieldModelBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName = '')
    {
        if ($bindingMemberName === '')
            $bindingMemberName = $name;
        parent::__construct($name, $caption, $bindingMemberName);
        $validator = new NumericFieldValidator();
        $validator->setAllowDecimals(false);
        $validator->setMinimumValue(0);
        $validator->setIsMoney(false);
        $this->validator = $validator;
        $this->maxChars  = 12;
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return IntegerRepeaterField
     */
    public static function createInstance()
    {
        return new IntegerRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}


/**
 * Represents a common text field repeater item for email input
 *
 * @package WebCore
 * @subpackage Model
 */
class EmailRepeaterField extends DataRepeaterFieldModelBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName = '')
    {
        if ($bindingMemberName === '')
            $bindingMemberName = $name;
        parent::__construct($name, $caption, $bindingMemberName);
        $this->setValidator(new EmailFieldValidator());
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return EmailRepeaterField
     */
    public static function createInstance()
    {
        return new EmailRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common checkbox repeater item
 *
 * @package WebCore
 * @subpackage Model
 */
class CheckBoxRepeaterField extends DataRepeaterFieldModelBase
{
    protected $checkedValue;
    protected $uncheckedValue;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     * @param string $checkedValue
     * @param string $uncheckedValue
     */
    public function __construct($name, $caption, $bindingMemberName = '', $checkedValue = '1', $uncheckedValue = '0')
    {
        if ($bindingMemberName === '')
            $bindingMemberName = $name;
        parent::__construct($name, $caption, $bindingMemberName);
        
        if (is_string($checkedValue) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = checkedValue');
        if (is_string($uncheckedValue) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = uncheckedValue');
        
        $this->checkedValue   = $checkedValue;
        $this->uncheckedValue = $uncheckedValue;
    }
    
    /**
     * Sets the value the checkbox holds when checked.
     *
     * @param string $value
     */
    public function setCheckedValue($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        $this->checkedValue = $value;
    }
    
    /**
     * Gets the value the checkbox holds when checked.
     *
     * @return string
     */
    public function getCheckedValue()
    {
        return $this->checkedValue;
    }
    
    /**
     * Sets the value the checkbox holds when unchecked.
     *
     * @param string $value
     */
    public function setUncheckedValue($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        $this->uncheckedValue = $value;
    }
    
    /**
     * Sets the value the checkbox holds when checked.
     *
     * @return string
     */
    public function getUncheckedValue()
    {
        return $this->uncheckedValue;
    }
    
    /**
     * Gets whether the checkbox is in the checked state.
     *
     * @param bool $value
     */
    public function setIsChecked($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        $this->value = ($value === true) ? $this->getCheckedValue() : $this->getUncheckedValue();
    }
    
    /**
     * Sets whether the checkbox is in the checked state.
     *
     * @param bool $value
     */
    public function getIsChecked()
    {
        return ($this->value == $this->checkedValue);
    }
    
    /**
     * Gets the current value of the model.
     *
     * @return string
     */
    public function &getValue()
    {
        $value = ($this->getIsChecked()) ? $this->getCheckedValue() : $this->getUncheckedValue();
        return $value;
    }
    
    /**
     * Sets the current value of the model.
     *
     * @return string
     */
    public function setValue($value)
    {
        $value = "" . $value;
        
        if ($value !== $this->getCheckedValue() && $value !== $this->getUncheckedValue())
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The value can only be either the checked or the unchecked value. Parameter = value');
        
        if ($value == $this->checkedValue)
            $this->value = $this->checkedValue;
        else
            $this->value = $this->uncheckedValue;
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return CheckBoxRepeaterField
     */
    public static function createInstance()
    {
        return new CheckBoxRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common combobox repeater item
 *
 * @package WebCore
 * @subpackage Model
 */
class ComboBoxRepeaterField extends DataRepeaterFieldModelBase
{
    /**
     *@var IndexedCollection
     */
    protected $options;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName = '')
    {
        if ($bindingMemberName === '')
            $bindingMemberName = $name;
        parent::__construct($name, $caption, $bindingMemberName);
        $this->options = new IndexedCollection();
    }
    
    /**
     * Clears options
     *
     */
    public function clearOptions()
    {
        $this->options = new IndexedCollection();
    }
    
    /**
     * Adds an option to the selectable options.
     * @param string $value The option's value
     * @param string $display The string to display in the option. If none specified, it will display the same as the value.
     * @param string $category The category for this option
     */
    public function addOption($value, $display = '', $category = '')
    {
        if ($display === '')
            $display = $value;
        $newOption = array(
            'value' => $value,
            'display' => $display,
            'category' => $category
        );
        $this->options->addItem($newOption);
    }
    
    /**
     * Adds an IndexedCollection to the selectable options.
     *
     * @param IndexedCollection $items
     * @param string $valueMember The member of the object or array to be used as the value
     * @param string $displayMember The member of the object or array to be displayed
     * @param string $categoryMember The member of the object or array to categorize the option in
     */
    public function addOptions($items, $valueMember = 'value', $displayMember = 'display', $categoryMember = 'category')
    {
        foreach ($items as $option)
        {
            $newOption = array(
                'value' => '',
                'display' => '',
                'category' => ''
            );
            
            if (is_array($option))
            {
                while (list($key, $value) = each($option))
                {
                    if ($key === $valueMember)
                    {
                        $newOption['value'] = $value;
                        continue;
                    }
                    if ($key === $displayMember)
                    {
                        $newOption['display'] = $value;
                        continue;
                    }
                    if ($key === $categoryMember)
                    {
                        $newOption['category'] = $value;
                        continue;
                    }
                }
                
                reset($option);
            }
            else
            {
                $newOption['value']   = $option->$valueMember;
                $newOption['display'] = $option->$displayMember;
                if (isset($option->$categoryMember))
                    $newOption['category'] = $option->$categoryMember;
            }
            
            $this->options->addItem($newOption);
        }
    }
    
    /**
     * Gets the options that have been added to this model.
     *
     * @return IndexedCollection
     */
    public function &getOptions()
    {
        return $this->options;
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return ComboBoxRepeaterField
     */
    public static function createInstance()
    {
        return new ComboBoxRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common data repeater item
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class DataRepeaterField extends BoundRepeaterFieldModelBase
{
    private $url;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the column data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName)
    {
        parent::__construct($name, $caption, $bindingMemberName);
        $this->url = '';
    }
    
    /**
     * Sets a target URL. The binding value replaces the '%s' in the rendering view.
     * @example ...->setUrl('http://domain.ext/view.php?id=%s') results in link generation with the IDs in the Urls.
     * @param string $value
     */
    public function setUrl($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->url = $value;
    }
    
    /**
     * Gets URL to link
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}

/**
 * Represents a command data item
 *
 * @package WebCore
 * @subpackage Model
 */
class CommandRepeaterField extends EventDataItemBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $eventName
     * @param string $bindingMemberEventValue The name of the member to extract the eventValue information from when the data item data is populated.
     */
    public function __construct($name, $caption, $eventName, $bindingMemberEventValue = 'id')
    {
        parent::__construct($name, $caption, $eventName, $bindingMemberEventValue);
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return CommandRepeaterField
     */
    public static function createInstance()
    {
        return new CommandRepeaterField('ISerializable', 'ISerializable', 'ISerializable', 'ISerializable');
    }
}

class CommandEditRepeaterField extends EventDataItemBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $eventName
     * @param string $bindingMemberEventValue The name of the member to extract the eventValue information from when the data item data is populated.
     */
    public function __construct($name, $caption, $eventName, $bindingMemberEventValue = 'id')
    {
        parent::__construct($name, $caption, $eventName, $bindingMemberEventValue);
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return CommandRepeaterField
     */
    public static function createInstance()
    {
        return new CommandRepeaterField('ISerializable', 'ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a normal link data item
 * 
 * @package WebCore
 * @subpackage Model
 */
class LinkRepeaterField extends DataRepeaterField
{
    private $urlBindingMemberName;
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $textbindingMemberName The name of the data source member from which the data item data is extracted upon data binding
     * @param string $urlBindingMemberName
     * @param string $url
     */
    public function __construct($name, $textbindingMemberName, $urlBindingMemberName, $url)
    {
        parent::__construct($name, '', $textbindingMemberName);
        $this->setUrl($url);
        $this->urlBindingMemberName = $urlBindingMemberName;
    }
    
    public function getUrlBindingMemberName()
    {
        return $this->urlBindingMemberName;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return LinkRepeaterField
     */
    public static function createInstance()
    {
        return new LinkRepeaterField('ISerializable', 'ISerializable', 'ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a normal text data item
 * 
 * @package WebCore
 * @subpackage Model
 */
class TextRepeaterField extends DataRepeaterField
{
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $bindingMemberName The name of the data source member from which the data item data is extracted upon data binding
     */
    public function __construct($name, $caption, $bindingMemberName)
    {
        parent::__construct($name, $caption, $bindingMemberName);
    }
    
    /**
     * @return bool
     */
    public function getHasCaption()
    {
        return $this->getCaption() != '';
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TextRepeaterField
     */
    public static function createInstance()
    {
        return new TextRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a image data item
 *
 * @package WebCore
 * @subpackage Model
 */
class ImageRepeaterField extends DataRepeaterField
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $imagePath
     * @param string $bindingMemberName The name of the data source member from which the data item data is extracted upon data binding
     */
    public function __construct($name, $imagePath, $bindingMemberName)
    {
        parent::__construct($name, $imagePath, $bindingMemberName);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ImageRepeaterField
     */
    public static function createInstance()
    {
        return new ImageRepeaterField('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents the standard data repeater model implementation.
 *
 * @package WebCore
 * @subpackage Model
 */
class DataRepeater extends DataRepeaterModelBase implements IBindingTarget, IRootModel
{
    /**
     * Create a instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
        
        $this->state = new DataRepeaterState();
        
        // Automatically deserialize the gridstate if available
        // @todo Models should use the controller
        $request = HttpContext::getRequest()->getRequestVars();
        if ($request->keyExists($this->getStateName()))
        {
            $this->state = DataRepeaterState::fromBase64($request->getValue($this->getStateName()));
            if (Controller::isPostBack($this) == false) $this->state->resetRecordCount(); // record count might have changed; update it
        }
    }
    
    /**
     * Takes either an IndexedCollection[KeyedCollection] or a DataTableAdapter,
     * and generates the data items (IndexedCollection of stdClass Items) for the grid.
     * The data items will then be available to the renderer along with the item collection.
     *
     * @param mixed $dataSource
     */
    public function dataBind(&$dataSource)
    {
        $this->dataItems->clear();
        if (ObjectIntrospector::isA($dataSource, 'IDataTableAdapter'))
        {
            if ($this->isPaged === true)
            {
                if ($this->state->getTotalRecordCount() === -1)
                {
                    $countSource = clone $dataSource;
                    $recordCount = $countSource->count();
                    $this->state->setTotalRecordCount($recordCount);
                    $pageCount = intval(ceil($recordCount / $this->pageSize));
                    $this->state->setPageCount($pageCount);
                    if ($this->state->getPageIndex() >= $this->state->getPageCount())
                    {
                        $this->state->setPageIndex($this->state->getPageCount() - 1);
                    }
                }
                
                $offset = $this->state->getPageIndex() * $this->pageSize;
                $dataSource->take($this->pageSize);
                if ($offset > 0)
                    $dataSource->skip($offset);
            }
            
            $rows = $dataSource->selectNew()->getArrayReference();
            $this->dataItems->addRange($rows);
        }
        else
        {
            if ($this->isPaged === true)
            {
                if ($this->state->getTotalRecordCount() === -1)
                {
                    $this->state->setTotalRecordCount(count($dataSource));
                    $pageCount = intval(ceil($recordCount / $this->pageSize));
                    $this->state->setPageCount($pageCount);
                    if ($this->state->getPageIndex() >= $this->state->getPageCount())
                    {
                        $this->state->setPageIndex($this->state->getPageCount() - 1);
                    }
                }
                
                //@todo Paging repeater with IndexCollections
            }
            
            foreach ($dataSource as $item)
            {
                if (is_string($item))
                {
                    $obj       = new stdClass();
                    $obj->Text = $item;
                }
                elseif (ObjectIntrospector::isExtending($item, 'KeyedCollectionBase'))
                {
                    $obj = $item->toStdClass();
                }
                else
                {
                    $obj = $item;
                }
                
                $this->dataItems->addItem($obj);
            }
        }
    }
    
    /**
     * Determines whether the data item name exists by searching recursively.
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $dataItemName
     * @return bool
     */
    public function repeaterFieldExists($dataItemName)
    {
        return $this->getChildren()->controlExists($dataItemName, 'ContainerModelBase');
    }
    
    /**
     * Adds a data item model to the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param BoundRepeaterFieldModelBase $dataItem
     */
    public function addRepeaterField(&$dataItem)
    {
        if (ObjectIntrospector::isExtending($dataItem, 'BoundRepeaterFieldModelBase') === true)
            $this->getChildren()->addControl($dataItem);
        else
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The data item object must inherit from BoundRepeaterFieldModelBase');
    }
    
    /**
     * Gets a data item model within the collection.
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $dataItemName
     * @param bool $searchRecursive
     * @return BoundRepeaterFieldModelBase
     */
    public function getRepeaterField($dataItemName, $searchRecursive = true)
    {
        return $this->getChildren()->getControl($dataItemName, $searchRecursive, 'BoundRepeaterFieldModelBase');
    }
    
    /**
     * Returns an array of data item names within the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param bool $searchRecursive
     * @return array
     */
    public function getRepeaterFieldNames($searchRecursive)
    {
        return $this->getChildren()->getControlNames($searchRecursive, 'BoundRepeaterFieldModelBase');
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return DataRepeater
     */
    public static function createInstance()
    {
        return new DataRepeater('ISerializable', 'ISerializable');
    }
}

/**
 * Represents the editable data repeater model implementation.
 *
 * @package WebCore
 * @subpackage Model
 */
class EditableDataRepeater extends DataRepeater
{
    const EVENTNAME_ADD_ITEM = 'addItem';
    const EVENTNAME_EDIT_ITEM = 'editItem';
    const EVENTNAME_DELETE_ITEM = 'deleteItem';
    const EVENTNAME_SAVE_ITEM = 'saveItem';
    const EVENTNAME_CANCEL_ITEM = 'cancelItem';
    
    const EDITMODE_ADD = 'add';
    const EDITMODE_START = 'start';
    const EDITMODE_SAVE = 'save';
    const EDITMODE_NONE = 'none';
    
    protected $keyName;
    protected $allowNew;
    protected $allowDelete;
    protected $editKey;
    /**
     * @var KeyedCollection
     */
    protected $callbacks;
    
    /**
     * Create a instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param string $keyName
     * @param bool $allowNew
     * @param bool $allowDelete
     */
    public function __construct($name, $caption, $keyName = 'id', $allowNew = true, $allowDelete = true)
    {
        if (is_string($keyName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = entityId');
        
        parent::__construct($name, $caption);
        
        $this->keyName     = $keyName;
        $this->allowNew    = $allowNew;
        $this->allowDelete = $allowDelete;
        $this->editKey     = null;
        
        if ($this->allowNew)
        {
            $addItemEvent = new RepeaterGenericEventManager(self::EVENTNAME_ADD_ITEM);
            $this->children->addControl($addItemEvent);
        }
        
        if ($this->allowDelete)
        {
            $deleteItemEvent = new RepeaterGenericEventManager(self::EVENTNAME_DELETE_ITEM);
            $this->children->addControl($deleteItemEvent);
        }
        
        $editItemEvent   = new RepeaterGenericEventManager(self::EVENTNAME_EDIT_ITEM);
        $saveItemEvent   = new RepeaterGenericEventManager(self::EVENTNAME_SAVE_ITEM);
        $cancelItemEvent = new RepeaterGenericEventManager(self::EVENTNAME_CANCEL_ITEM);
        
        $this->children->addControl($editItemEvent);
        $this->children->addControl($saveItemEvent);
        $this->children->addControl($cancelItemEvent);
    }
    
    /**
     * Sets the edit key
     *
     * @param string $value
     */
    public function setEditKey($value)
    {
        $this->editKey = $value;
    }
    
    /**
     * Clears the edit key (sets it to null)
     */
    public function endEdit()
    {
        $this->editKey = null;
    }
    
    /**
     * Gets the edit key
     *
     * @return string
     */
    public function getEditKey()
    {
        return $this->editKey;
    }
    
    /**
     * Gets the key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->keyName;
    }
    
    /**
     * Gets the current mode of this model based on the current request.
     * The return string constants are the MODE-prefixed constants defined in this class;
     * @return string
     */
    public function getMode()
    {
        $mode   = self::EDITMODE_NONE;
        $events = Controller::getEvents($this);
        for ($i = 0; $i < $events->getCount(); $i++)
        {
            /**
             * @var ControllerEvent
             */
            $event     = $events->getItem($i);
            $eventName = $event->getName();
            
            switch ($eventName)
            {
                case self::EVENTNAME_ADD_ITEM:
                    return self::EDITMODE_ADD;
                case self::EVENTNAME_CANCEL_ITEM:
                    return self::EDITMODE_NONE;
                case self::EVENTNAME_DELETE_ITEM:
                    return self::EDITMODE_NONE;
                case self::EVENTNAME_EDIT_ITEM:
                    return self::EDITMODE_START;
                case self::EVENTNAME_SAVE_ITEM:
                    return self::EDITMODE_SAVE;
            }
        }
        
        return $mode;
    }
    
    /**
     * Gets whether the control allows a new row
     *
     * @return bool
     */
    public function getAllowNew()
    {
        return $this->allowNew;
    }
    
    /**
     * Sets whether the control allows a new row
     *
     * @param bool $value
     */
    public function setAllowNew($value)
    {
        $this->allowNew = $value;
    }
    
    /**
     * Gets whether the control allows deleting a row
     *
     * @return bool
     */
    public function getAllowDelete()
    {
        return $this->allowDelete;
    }
    
    /**
     * Sets whether the control allows deleting a row
     *
     * @param bool $value
     */
    public function setAllowDelete($value)
    {
        $this->allowDelete = $value;
    }
    
    /**
     * Validates all the IValidatable controls within this form.
     *
     * @return bool
     */
    public function validate()
    {
        $result = parent::validate();
        
        if ($result === true)
            $this->setErrorMessage('');
        else
            $this->setErrorMessage(Resources::getValue(Resources::SRK_FORM_ERRORGENERIC));
        
        return $result;
    }
    
    protected $formDataSource;
    
    /**
     * Binds all IBindingTargetMember controls within the form to the given data source.
     * Also, sets IEventTrigger controls by looking for the following key pattern in the collection
     * IEventTrigger->getEventName() . '_eventValue'
     *
     * @param KeyedCollectionBase $dataSource (also supports Popo binding)
     */
    public function dataBindForm(&$dataSource)
    {
        $this->formDataSource = $dataSource;
        if (is_object($dataSource) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'parameter = dataSource');
        
        if (ObjectIntrospector::isExtending($dataSource, 'KeyedCollectionBase') === false && ObjectIntrospector::isA($dataSource, 'Popo') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'parameter = dataSource');
        
        // First, update the controls that are member-wise bindable
        $controlNames = $this->getChildren()->getImplementingControlNames(true, 'IBindingTargetMember');
        
        foreach ($controlNames as $controlName)
        {
            $control  = $this->getChildren()->getControl($controlName, true);
            $propName = $control->getBindingMemberName();
            
            if ($control->getIsBindable())
            {
                if (ObjectIntrospector::isA($dataSource, 'Popo'))
                {
                    if ($dataSource->hasField($propName))
                        $control->setValue($dataSource->$propName);
                }
                else
                {
                    if ($dataSource->keyExists($propName))
                        $control->setValue($dataSource->getValue($propName));
                }
            }
        }
    }
}
?>