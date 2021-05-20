<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides generic models
 * 
 * @todo Add TextSelector (i.e. multiselection control using a textbox like a typical mail recipients)
 * @todo Add Autocomplete capability (form extenders?) class AutocompleteExtender extends ControlExtender; __construct(TextField $textfield, string $httpHandlerUrl)
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.serialization.php";

/**
 * Defines the methods that a model must implement.
 *
 * @package WebCore
 * @subpackage Model
 */
interface IModel extends ISerializable
{
    /**
     * Gets the name of the model
     *
     * @return string
     */
    public function getName();
}

/**
 * This interface is used as an attribute. This interface states that the class represents a root
 *  - or main - node in a model hierarchy. For example, a Form represents a set of
 * hierarchically ordered model nodes with containers, fields, and controls in general.
 * Root models are usually what views consume to render the whole model.
 *
 * @package WebCore
 * @subpackage Model
 */
interface IRootModel extends IModel
{
}

/**
 * Defines methods for an object to be able able to raise events in the controller.
 *
 * @package WebCore
 * @subpackage Model
 */
interface IEventTrigger extends IModel
{
    public function getEventName();
    public function setEventName($value);
    public function getEventValue();
    public function setEventValue($value);
}

/**
 * Defines the methods that are necessary for a model to be validatable.
 *
 * @package WebCore
 * @subpackage Model
 */
interface IValidatable extends IModel
{
    /**
     * Validates this instance.
     *
     * @return bool
     */
    public function validate();
}

/**
 * Defines an interface for classes that validate models.
 * Usually, validation is required for (but not limited to) user-input fields.
 *
 * @package WebCore
 * @subpackage Model
 */
interface IValidator extends ISerializable
{
    /**
     * Defines the validation function to call upon model validation.
     *
     * @param IModel $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs);
}

/**
 * This interface is used as an attribute.
 * Used to mark an object as containable within a ControlModelCollection-derived object
 *
 * @package WebCore
 * @subpackage Model
 */
interface IContainable extends IModel
{
}

/**
 * Interface to define a control state model
 *
 * @package WebCore
 * @subpackage Model
 */
interface IControlState extends ISerializable
{
    static function fromBase64($data);
    
    /**
     * Encodes current state to BASE64
     *
     * @return string
     */
    function toBase64($stateName = '');
}

/**
 * Models that implement this interface should always parse the HTTP request state upon instantiation.
 * @package WebCore
 * @subpackage Model
 */
interface IStatefulModel extends IModel
{
    /**
     * Gets the name of the Request key the model's state should exist.
     * This name should be prefixed with Controller::PREFIX_STATE
     * @return string
     */
    public function getStateName();
    
    /**
     * @return IControlState
     */
    public function &getstate();
}

/**
 * Represents the base class from which all models derive
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class ModelBase extends ObjectBase implements IModel
{
    protected $__parent;
    protected $name;
    
    /**
     * Creates a new instance of a model
     *
     * @param string $name
     */
    protected function __construct($name)
    {
        if (is_string($name) === false || trim($name) == '')
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = name");
        
        $wordCount       = str_word_count($name, 0, '\'\\/~`!@#$%^&*()-{}[]:;<>|?+=., 1234567890');
        $underscoreCount = substr_count($name, '_');
        $wordCount       = $wordCount - $underscoreCount;
        
        if ($wordCount > 1)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The name must only contain alphanumeric characters and underscores. No other characters are allowed. Parameter = name");
        
        $this->name     = $name;
        $this->__parent = null;
    }
    
    /**
     * Sets the state of the current instance given the associative array representing its state.
     */
    public function setObjectState($state)
    {
        $objectVars = SerializationHelper::stateToObjectVars($state);
        
        foreach ($objectVars as $key => $value)
            $this->$key = $objectVars[$key];
    }
    
    /**
     * This method must return an associative array representing the state of the object.
     * array variables must be keyed as 'variableName:array'
     * Object variables must be keyed as 'variableName:objectType'
     *
     * @return array
     */
    public function getObjectState()
    {
        $objectVars             = get_object_vars($this);
        $objectVars['__parent'] = null;
        
        return SerializationHelper::objectVarsToState($objectVars);
    }
    
    /**
     * Gets the name by which the field is identified. The name must be unique.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Gets the parent reference
     *
     * Ïreturn ModelBase
     */
    public function &getParent()
    {
        return $this->__parent;
    }
    
    /**
     * Sets the parent reference
     *
     * @param ModelBase $value
     */
    public function setParent(&$value)
    {
        $this->__parent = $value;
    }
}

/**
 * Represents a common GUI control.
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class ControlModelBase extends ModelBase implements IContainable
{
    /**
     *
     * @var bool Determines whether the control model is visible or not
     */
    protected $visible;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param bool $isVisible
     */
    protected function __construct($name, $isVisible = true)
    {
        parent::__construct($name);
        $this->setVisible($isVisible);
    }
    
    /**
     * Sets whether the field is visible
     *
     * @param bool $value
     */
    public function setVisible($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->visible = $value;
    }
    
    /**
     * Gets whether the field is visible
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }
}

/**
 * Provides a base implementation for a control implementing the IBindingTargetMember interface
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class BoundControlModelBase extends ControlModelBase implements IBindingTargetMember
{
    /**
     * The name of the member to bind to
     *
     * @var str
     */
    protected $bindingMemberName;
    protected $isBindable;
    protected $value;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param str $value
     * @param bool $isBindable
     */
    protected function __construct($name, $value, $isBindable)
    {
        if (is_bool($isBindable) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = isBindable');
        
        parent::__construct($name, true);
        $this->value             = $value;
        $this->isBindable        = $isBindable;
        $this->bindingMemberName = $name;
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
     * Gets the name of the member to bind to in the parent bindingSourceName
     *
     * @return string
     */
    public function getBindingMemberName()
    {
        return $this->bindingMemberName;
    }
    
    /**
     * Sets the name of the member to bind to in the parent bindingSourceName
     * If the value is an empty string, it will automatically set the isBindable property to false.
     * If the value is a non-empty string, it will automatically set the isBindable property to true.
     *
     * @param string $value
     */
    public function setBindingMemberName($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
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
     * Gets the value of the field.
     *
     * @return mixed
     */
    public function &getValue()
    {
        return $this->value;
    }
    
    /**
     * Sets the value of the field.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}

/**
 * Represents a base implementation of a user input control
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class FieldModelBase extends BoundControlModelBase implements IValidatable
{
    /**
     * Represents the validator object that will perform the validattion
     *
     * @var IValidator
     */
    protected $validator;
    protected $caption;
    protected $isRequired;
    protected $errorMessage;
    protected $helpMessage;
    protected $isReadOnly;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param mixed $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    protected function __construct($name, $caption, $value, $isRequired, $helpMessage)
    {
        parent::__construct($name, $value, true);
        
        if (is_string($caption) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = caption');
        if (is_string($helpMessage) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = helpMessage');
        if (is_bool($isRequired) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = isRequired');
        
        $this->validator    = new BasicFieldValidator();
        $this->isRequired   = $isRequired;
        $this->caption      = $caption;
        $this->errorMessage = '';
        $this->helpMessage  = $helpMessage;
        $this->isReadOnly   = false;
    }
    
    /**
     * Gets whether the filed is read-only.
     *
     * @return bool
     */
    public function getIsReadOnly()
    {
        return $this->isReadOnly;
    }
    
    /**
     * Sets whether the field is read-only.
     *
     * @param bool $value
     */
    public function setIsReadOnly($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->isReadOnly = $value;
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
        
        if (ObjectIntrospector::isImplementing($this->validator, 'IValidator'))
        {
            return $this->validator->validate($this, '');
        }
        
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
     * Gets the caption for the field
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Sets the caption to display for the field.
     *
     * @param string $value
     */
    public function setCaption($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->caption = $value;
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
     * the hasErrorMessage property will be set to false. It will be set to true otherwise.
     *
     * @param string $value
     */
    public function setErrorMessage($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->errorMessage = $value;
    }
    /**
     * Gets the current help message to display.
     *
     * @return string
     */
    public function getHelpMessage()
    {
        return $this->helpMessage;
    }
    
    /**
     * Sets the current help message to display.
     *
     * @param string $value
     */
    public function setHelpMessage($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->helpMessage = $value;
    }
}

/**
 * Represents the base implementation for a control container. i.e. A Model Tree
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class ContainerModelBase extends ControlModelBase implements IValidatable
{
    /**
     * @var ControlModelCollection
     */
    protected $children;
    protected $caption;
    protected $errorMessage;
    protected $message;
    protected $redirect;
    
    /**
     * Creates a new instance of this class
     * @param string $name
     * @param string $caption
     */
    protected function __construct($name, $caption)
    {
        parent::__construct($name, true);
        
        $this->setCaption($caption);
        $this->children     = new ControlModelCollection($this);
        $this->errorMessage = '';
    }
    
    /**
     * Gets the caption for the field
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Sets the caption to display for the field.
     *
     * @param string $value
     */
    public function setCaption($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->caption = $value;
    }
    
    /**
     * Gets the child controls within this container
     *
     * @return ControlModelCollection
     */
    public function &getChildren()
    {
        return $this->children;
    }
    
    /**
     * Validates all the fields recursiveley within this container.
     *
     * @return bool
     */
    public function validate()
    {
        $result     = true;
        $fieldNames = $this->getChildren()->getImplementingControlNames(true, 'IValidatable');
        
        foreach ($fieldNames as $fieldName)
        {
            if ($this->getChildren()->getControl($fieldName, true)->validate() === false)
                $result = false;
        }
        
        return $result;
    }
    
    /**
     * Determines whether the container name exists by searching recursively.
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $containerName
     * @return bool
     */
    public function containerExists($containerName)
    {
        return $this->getChildren()->controlExists($containerName, 'ContainerModelBase');
    }
    
    /**
     * Adds a container model to the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param ContainerModelBase $container
     */
    public function addContainer(&$container)
    {
        if (ObjectIntrospector::isExtending($container, 'ContainerModelBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The container object must inherit from ContainerModelBase');
        
        $this->getChildren()->addControl($container);
    }
    
    /**
     * Gets a container model within the immediate collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $containerName
     * @param bool $searchRecursive
     * @return ContainerModelBase
     */
    public function getContainer($containerName, $searchRecursive = true)
    {
        return $this->getChildren()->getControl($containerName, $searchRecursive, 'ContainerModelBase');
    }
    
    /**
     * Returns an array of container names within the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param bool $searchRecursive
     * @return array
     */
    public function getContainerNames($searchRecursive = true)
    {
        return $this->getChildren()->getControlNames($searchRecursive, 'ContainerModelBase');
    }
    
    /**
     * Gets whether the control field has a message in pool.
     *
     * @return bool
     */
    public function hasMessage()
    {
        if (trim($this->getMessage()) == '')
            return false;
        else
            return true;
    }
    
    /**
     * Gets the current message to display.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Sets the current  message to display.
     *
     * @param string $value
     */
    public function setMessage($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->message = $value;
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
    
    /**
     * Gets the current redirect.
     *
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
    
    /**
     * Sets the current redirect.
     *
     * @param string $value
     */
    public function setRedirect($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->redirect = $value;
    }
}

/**
 * Represents the base implementation for a control container in a form.
 * Basically the same as its parent class but with additional helper methods specific to Field controls
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class FieldContainerModelBase extends ContainerModelBase
{
    
    protected $isSideBySide;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
        $this->isSideBySide = false;
    }
    
    /**
     * Determines whether the filed name exists by searching recursively.
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $fieldName
     * @return bool
     */
    public function fieldExists($fieldName)
    {
        return $this->getChildren()->controlExists($fieldName, 'ContainerModelBase');
    }
    
    /**
     * Adds a field model to the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param FieldModelBase $field
     */
    public function addField(&$field)
    {
        if (ObjectIntrospector::isExtending($field, 'FieldModelBase') === true)
            $this->getChildren()->addControl($field);
        else
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The field object must inherit from FieldModelBase');
    }
    
    /**
     * Gets a field model within the collection.
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $fieldName
     * @param bool $searchRecursive
     * @return FieldModelBase
     */
    public function getField($fieldName, $searchRecursive = true)
    {
        return $this->getChildren()->getControl($fieldName, $searchRecursive, 'FieldModelBase');
    }
    
    /**
     * Returns an array of field names within the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param bool $searchRecursive
     * @return array
     */
    public function getFieldNames($searchRecursive)
    {
        return $this->getChildren()->getControlNames($searchRecursive, 'FieldModelBase');
    }
    
    /**
     * Determines if the layout of this container should display its fields in a side-by-side manner
     * @return bool
     */
    public function getIsSideBySide()
    {
        return $this->isSideBySide;
    }
    
    /**
     * Determines if the layout of this container should display its fields in a side-by-side manner
     * @param bool $value
     */
    public function setIsSideBySide($value)
    {
        $this->isSideBySide = $value;
    }
}

/**
 * Represents a collection of ControlModel objects (Containers and Fields)
 *
 * @package WebCore
 * @subpackage Model
 */
class ControlModelCollection extends CollectionBase
{
    private $__parent;
    
    /**
     * Creates a new instance of this class
     *
     * @param IRootModel $parent
     */
    public function __construct(&$parent)
    {
        $this->__parent = $parent;
        $controls       = array();
        parent::__construct($controls, false);
    }
    
    /**
     * Adds a control to the collection
     *
     * @param ControlModelBase $control
     */
    public function addControl(&$control)
    {
        if (ObjectIntrospector::isExtending($control, 'ControlModelBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The control object must inherit from ControlModelBase');
        
        if ($this->controlExists($control->getName(), 'ControlModelBase') === false)
        {
            $control->setParent($this->__parent);
            $this->__arrayReference[$this->getCount()] = $control;
        }
        else
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The control object (' . $control->getName() . ') must have a unique name accross all containers.');
    }
    
    /**
     * Determines if a control exists, given its name and base type name.
     *
     * @param string $controlName
     * @param string $controlBaseType
     * @return bool
     */
    public function controlExists($controlName, $controlBaseType = 'ControlModelBase')
    {
        $controlNames = $this->getControlNames(true, $controlBaseType);
        return (in_array($controlName, $controlNames)) ? true : false;
    }
    
    /**
     * Gets the control names given a base type name
     *
     * @param bool $searchRecursive
     * @param string $controlBaseType
     * @return array
     */
    public function &getControlNames($searchRecursive, $controlBaseType = 'ControlModelBase')
    {
        $controlNames = array();
        
        foreach ($this->__arrayReference as &$currentControl)
        {
            if (ObjectIntrospector::isExtending($currentControl, $controlBaseType) === true)
            {
                $controlNames[count($controlNames)] = $currentControl->getName();
            }
        }
        
        if ($searchRecursive === true)
        {
            $containerNames = $this->getControlNames(false, 'ContainerModelBase');
            foreach ($containerNames as $containerName)
            {
                $addedNames   = $this->getControl($containerName, false, 'ContainerModelBase')->getChildren()->getControlNames($searchRecursive, $controlBaseType);
                $controlNames = array_merge($controlNames, $addedNames);
            }
        }
        
        return $controlNames;
    }
    
    /**
     * Gets a control given its name and base type name.
     *
     * @param string $controlName
     * @param bool $searchRecursive
     * @param string $controlBaseType
     * @return ControlModelBase
     */
    public function getControl($controlName, $searchRecursive, $controlBaseType = 'ControlModelBase')
    {
        // Search within the immediate collection
        foreach ($this->__arrayReference as &$currentControl)
        {
            if (ObjectIntrospector::isExtending($currentControl, $controlBaseType) === true)
            {
                if ($currentControl->getName() === $controlName)
                    return $currentControl;
            }
        }
        
        // Search within subcontainers
        if ($searchRecursive === true)
        {
            $containerNames = $this->getControlNames(false, 'ContainerModelBase');
            foreach ($containerNames as $containerName)
            {
                $control = $this->getControl($containerName, false, 'ContainerModelBase')->getChildren()->getControl($controlName, $searchRecursive, $controlBaseType);
                if (is_null($control) === false)
                    return $control;
            }
        }
        
        return null;
    }
    
    /**
     * Gets all the control names implementing a given interface name.
     *
     * @param bool $searchRecursive
     * @param string $controlInterface
     * @return array
     */
    public function &getImplementingControlNames($searchRecursive, $controlInterface)
    {
        $controlNames = array();
        
        foreach ($this->__arrayReference as &$currentControl)
        {
            if (ObjectIntrospector::isImplementing($currentControl, $controlInterface) === true)
                $controlNames[count($controlNames)] = $currentControl->getName();
        }
        
        if ($searchRecursive === true)
        {
            $containerNames = $this->getControlNames(false, 'ContainerModelBase');
            foreach ($containerNames as $containerName)
            {
                $addedNames   = $this->getControl($containerName, false, 'ContainerModelBase')->getChildren()->getImplementingControlNames($searchRecursive, $controlInterface);
                $controlNames = array_merge($controlNames, $addedNames);
            }
        }
        
        return $controlNames;
    }
    
    /**
     * Gets an array of controls that implement the IEventTrigger interface
     *
     * @return array
     */
    public function &getEventTriggerControlNames()
    {
        return $this->getImplementingControlNames(true, 'IEventTrigger');
    }
    
    /**
     * Gets the control names of a given type name.
     * It also returns control names which are derived types of the given type name.
     *
     * @param bool $searchRecursive
     * @param string $controlType
     * @return array
     */
    public function &getTypedControlNames($searchRecursive, $controlType)
    {
        $controlNames = array();
        
        foreach ($this->__arrayReference as &$currentControl)
        {
            if (ObjectIntrospector::isA($currentControl, $controlType))
                $controlNames[count($controlNames)] = $currentControl->getName();
        }
        
        if ($searchRecursive === true)
        {
            $containerNames = $this->getControlNames(false, 'ContainerModelBase');
            foreach ($containerNames as $containerName)
            {
                $addedNames   = $this->getControl($containerName, false, 'ContainerModelBase')->getChildren()->getTypedControlNames($searchRecursive, $controlType);
                $controlNames = array_merge($controlNames, $addedNames);
            }
        }
        
        return $controlNames;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ControlModelCollection
     */
    public static function createInstance()
    {
        return new ControlModelCollection(new stdClass());
    }
}

/**
 * Represent a compound field base class
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class CompoundFieldModelBase extends ContainerModelBase implements IValidatable, IBindingTargetMember
{
    protected $bindingMemberName;
    protected $isRequired;
    protected $errorMessage;
    protected $helpMessage;
    protected $isReadOnly;
    protected $value;
    protected $isBindable;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption);
        
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->value        = $value;
        $this->errorMessage = '';
        $this->setHelpMessage($helpMessage);
        $this->setIsRequired($isRequired);
        $this->isReadOnly        = false;
        $this->isBindable        = true;
        $this->bindingMemberName = $name;
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
     * the hasErrorMessage property will be set to false. It will be set to true otherwise.
     *
     * @param string $value
     */
    public function setErrorMessage($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->errorMessage = $value;
    }
    
    /**
     * Gets the current help message to display.
     *
     * @return string
     */
    public function getHelpMessage()
    {
        return $this->helpMessage;
    }
    
    /**
     * Sets the current help message to display.
     *
     * @param string $value
     */
    public function setHelpMessage($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->helpMessage = $value;
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
     * Gets whether the filed is read-only.
     *
     * @return bool
     */
    public function getIsReadOnly()
    {
        return $this->isReadOnly;
    }
    
    /**
     * Sets whether the field is read-only.
     *
     * @param bool $value
     */
    public function setIsReadOnly($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->isReadOnly = $value;
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
     * Gets the name of the member to bind to in the parent bindingSourceName
     *
     * @return string
     */
    public function getBindingMemberName()
    {
        return $this->bindingMemberName;
    }
    
    /**
     * Sets the name of the member to bind to in the parent bindingSourceName
     * If the value is an empty string, it will automatically set the isBindable property to false.
     * If the value is a non-empty string, it will automatically set the isBindable property to true.
     *
     * @param string $value
     */
    public function setBindingMemberName($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
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
}

/**
 * Represents the real-time state management object that holds user values.
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class ControlStateBase extends SerializableObjectBase implements IControlState, IBindingTarget
{
    /**
     * @var KeyedCollection
     */
    protected $persistors;
    
    /**
     * Creates a new instance of this class
     *
     */
    public function __construct()
    {
        $this->persistors = new KeyedCollection();
    }
    
    /**
     * Sets a persistor
     *
     * @param string $name
     * @param mixed $value
     */
    public function setPersistor($name, $value)
    {
        $this->persistors->setValue($name, $value);
    }
    
    /**
     * Retrieves persistors
     *
     * @return KeyedCollection
     */
    public function getPersistors()
    {
        return $this->persistors;
    }
    
    /**
     * Consumes the collection
     *
     * @param CollectionBase
     */
    public function dataBind(&$dataSource)
    {
        $this->setObjectState($dataSource);
    }
    
    /**
     * Encodes current state to Base 64. It also updates the current NavigationHistoryEntry to contain the latest state.
     * @param string $stateName For NavigationHistory compatibility, provide the name of the Request key that must be updated.
     * @return string
     */
    public function toBase64($stateName = '')
    {
        $stateString = Base64Serializer::serialize($this);
        // compatibility with NavigationHistory
        if (!is_null(HttpContext::getNavigationHistory()->getItem(0)) && $stateName !== '')
        {
            $newState = new KeyedCollection();
            $newState->setValue($stateName, $stateString);
            HttpContext::getNavigationHistory()->getItem(0)->getPostedVars()->merge($newState);
        }
        return $stateString;
    }
}

/**
 * Represents a simple the real-time state management object that
 * holds user values.
 *
 * @package WebCore
 * @subpackage Model
 */
class SimpleControlState extends ControlStateBase
{
    /**
     * Creates a new instance of this class
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Creates an instance from a Base 64 string
     *
     * @param string $data
     * @return SimpleControlState
     */
    public static function fromBase64($data)
    {
        return Base64Serializer::deserialize($data, get_class());
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return SimpleControlState
     */
    public static function createInstance()
    {
        return new SimpleControlState();
    }
}

/**
 * Provides a base inplementation for a field model used to raise an event
 * For example, a button
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class ButtonModelBase extends ControlModelBase implements IEventTrigger
{
    protected $eventName;
    protected $eventValue;
    protected $caption;
    protected $isEnabled;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $eventName
     */
    protected function __construct($name, $caption, $eventName)
    {
        parent::__construct($name, true);
        
        $this->eventValue = '';
        $this->setEventName($eventName);
        $this->setCaption($caption);
        $this->isEnabled = true;
    }
    
    /**
     * Gets the caption for the field
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Sets the caption to display for the field.
     *
     * @param string $value
     */
    public function setCaption($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->caption = $value;
    }
    
    /**
     * Gets the name of the event to raise
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }
    
    /**
     * Sets the name of the event to raise
     *
     * @param string $value
     */
    public function setEventName($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        if (trim($value) == '')
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->eventName = $value;
    }
    
    /**
     * Gets the event value.
     *
     * @return string
     */
    public function getEventValue()
    {
        return $this->eventValue;
    }
    
    /**
     * Sets the event value.
     *
     * @param string $value
     */
    public function setEventValue($value)
    {
        $this->eventValue = $value;
    }
    
    /**
     * Gets whether the button is enabled.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }
    
    /**
     * Sets whether the button is enabled.
     *
     * @param bool $value
     */
    public function setIsEnabled($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->isEnabled = $value;
    }
}

/**
 * Represents a common toolbar
 *
 * @package WebCore
 * @subpackage Model
 */
class Toolbar extends ContainerModelBase
{
    protected $showCaption;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
        $this->showCaption = false;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Toolbar
     */
    public static function createInstance()
    {
        return new Toolbar('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common toolbar button
 *
 * @package WebCore
 * @subpackage Model
 */
class ToolbarButton extends ButtonModelBase
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $eventName
     */
    public function __construct($name, $caption, $eventName)
    {
        parent::__construct($name, $caption, $eventName);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ToolbarButton
     */
    public static function createInstance()
    {
        return new ToolbarButton('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common toolbar label
 *
 * @package WebCore
 * @subpackage Model
 */
class ToolbarLabel extends ControlModelBase
{
    private $caption;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param bool $isVisible
     */
    public function __construct($name, $caption, $isVisible = true)
    {
        parent::__construct($name, $isVisible);
        
        $this->setCaption($caption);
    }
    
    /**
     * Gets the caption for the field
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Sets the caption to display for the field.
     *
     * @param string $value
     */
    public function setCaption($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->caption = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ToolbarLabel
     */
    public static function createInstance()
    {
        return new ToolbarLabel('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common toolbar split
 *
 * @package WebCore
 * @subpackage Model
 */
class ToolbarSplit extends ControlModelBase
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param bool $isVisible
     */
    public function __construct($name, $isVisible = true)
    {
        parent::__construct($name, $isVisible);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ToolbarSplit
     */
    public static function createInstance()
    {
        return new ToolbarSplit('ISerializable');
    }
}

/**
 * Represents a common toolbar button menu
 *
 * @package WebCore
 * @subpackage Model
 */
class ToolbarButtonMenu extends ButtonModelBase
{
    protected $children;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption, $name);
        
        $this->children = new IndexedCollection();
    }
    
    /**
     * Adds a button menu item
     *
     * @param ToolbarButtonMenuItem $buttonItem
     */
    public function addItem($buttonItem)
    {
        $this->children->addItem($buttonItem);
    }
    
    /**
     * Returns items
     *
     * @return IndexedCollection
     */
    public function getItems()
    {
        return $this->children;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ToolbarButtonMenu
     */
    public static function createInstance()
    {
        return new ToolbarButtonMenu('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a common toolbar button menu item
 *
 * @package WebCore
 * @subpackage Model
 */
class ToolbarButtonMenuItem extends ButtonModelBase
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $eventName
     * @param string $eventValue
     */
    public function __construct($name, $caption, $eventName, $eventValue = '')
    {
        parent::__construct($name, $caption, $eventName);
        
        $this->eventValue = $eventValue;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return ToolbarButtonMenuItem
     */
    public static function createInstance()
    {
        return new ToolbarButtonMenuItem('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Model class to represent a site's main menu
 * 
 * @package WebCore
 * @subpackage Model
 */
class SiteMenu extends ControlModelBase implements IRootModel
{
    /**
     * @var IndexedCollection
     */
    protected $menuItems;
    /**
     * Creates a new instance of this model
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, true);
        $this->menuItems = new IndexedCollection();
    }
    
    /**
     * Gets the menu items collection
     * @return IndexedCollection
     */
    public function &getMenuItems()
    {
        return $this->menuItems;
    }
    
    /**
     * Adds a new menu item to the menu items collection.
     * @param string $caption
     * @param string $url
     */
    public function addMenuItem($caption, $url = '')
    {
        $name    = StringHelper::getSystemNormalized($this->getName()) . '_' . StringHelper::getSystemNormalized($caption);
        $newItem = new SiteMenuItem($name, $caption, $url);
        $newItem->setParent($this);
        $this->getMenuItems()->addItem($newItem);
    }
    
    /**
     * Creates a default instance of this class
     */
    public static function createInstance()
    {
        return new SiteMenu('ISerializable');
    }
}

/**
 * Model class to represent a site's main menu item within a hierarchy of menu items
 * 
 * @package WebCore
 * @subpackage Model
 */
class SiteMenuItem extends ControlModelBase
{
    /**
     * @var IndexedCollection
     */
    protected $menuItems;
    protected $caption;
    protected $url;
    
    /**
     * Creates a new instance of this model
     * 
     * @param string $name
     * @param string $caption
     * @parma string $url
     */
    public function __construct($name, $caption, $url)
    {
        parent::__construct($name, true);
        $this->caption   = $caption;
        $this->url       = $url;
        $this->menuItems = new IndexedCollection();
    }
    /**
     * Creates a default instance of this class
     */
    public static function createInstance()
    {
        return new SiteMenuItem('ISerializable', 'ISerializable', 'ISerializable');
    }
    /**
     * Gets the menu items collection
     * @return IndexedCollection
     */
    public function &getMenuItems()
    {
        return $this->menuItems;
    }
    
    /**
     * Adds a new menu item to the menu items collection.
     * @param string $caption
     * @param string $url
     */
    public function addMenuItem($caption, $url = '')
    {
        $name    = StringHelper::getSystemNormalized($this->getName()) . '_' . StringHelper::getSystemNormalized($caption);
        $newItem = new SiteMenuItem($name, $caption, $url);
        $newItem->setParent($this);
        $this->getMenuItems()->addItem($newItem);
    }
    
    /**
     * Gets the menu item's caption
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Sets the menu item's caption
     * @param string $value
     */
    public function setCaption($value)
    {
        $this->caption = $value;
    }
    
    /**
     * Gets the menu item's URL
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Sets the menu item's URL
     * @param string $value
     */
    public function setUrl($value)
    {
        $this->url = $value;
    }
}

/**
 * Model class to represent a site's breadcrumb
 *
 * @package WebCore
 * @subpackage Model
 */
class BreadcrumbItem extends ObjectBase
{
    public $Label;
    public $Url;
    
    public function __construct($label, $url = '')
    {
        //@todo Validate url
        $this->Label = $label;
        $this->Url   = $url;
    }
}

/**
 * Model class to represent a site's breadcrumb
 *
 * @package WebCore
 * @subpackage Model
 */
class Breadcrumb extends ControlModelBase implements IRootModel
{
    /**
     * @var IndexedCollection
     */
    private $links;
    private $label;
    private $itemSeparator;
    
    /**
     * Creates a new instance of this model
     * 
     * @param string $name
     * @param string $label
     * @param IndexedCollection<BreadcrumbItem> $links
     */
    public function __construct($name, $label)
    {
        parent::__construct($name, true);
        
        $this->label = $label;
        $this->links = new IndexedCollection();
        $this->itemSeparator = ' | ';
    }
    
    /**
     * Adds a new link to breadcrumb
     *
     * @param string $label
     * @param string $link
     */
    public function addLink($label, $link = '')
    {
        $item = new BreadcrumbItem($label, $link);
        $this->links->addItem($item);
    }
    
    /**
     * Sets the item's separator
     *
     * @param string $value
     */
    public function setItemSeparator($value)
    {
        $this->itemSeparator = $value;
    }
    
    /**
     * Gets the item's separator
     *
     * @return string
    */
    public function getItemSeparator()
    {
        return $this->itemSeparator;
    }
    
    /**
     * Gets the label
     *
     * @return string
    */
    public function getLabel()
    {
        return $this->label;
    }
    
    /**
     * Retrieves links
     *
     * @return IndexedCollection<BreadcrumbItem>
     */
    public function getLinks()
    {
        return $this->links;
    }
    
    /**
     * Creates a default instance of this class
     */
    public static function createInstance()
    {
        return new Breadcrumb('ISerializable');
    }
}
?>