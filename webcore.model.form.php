<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides models of controls in a data input form.
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.model.php";
require_once "webcore.model.validation.php";

/**
 * Implements a field to upload and download files.
 *
 * @package WebCore
 * @subpackage Model
 */
class FileField extends FieldModelBase implements IEventTrigger
{
    protected $allowPreview;
    protected $allowedExtensions;
    protected $eventName;
    protected $eventValue;

    /**
     * This var will wrap the value for the posted files
     * @var KeyedCollection
     */
    protected $postedFileValue;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param string $eventName
     * @param string $eventValue
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $eventName = '', $eventValue = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        
        $this->allowPreview      = true;
        $this->allowedExtensions = array();
        $this->validator         = new FileFieldValidator();
        $this->eventName         = $eventName;
        $this->eventValue        = $eventValue;

    }

    /**
     *
     * @return KeyedCollection
     */
    public function getPostedFileValue()
    {
        return $this->postedFileValue;
    }

    /**
     * The KeyedCollection retrieved from the posted files
     * @param KeyedCollection $value
     */
    public function setPostedFileValue($value)
    {
        $this->postedFileValue = $value;
    }
    /**
     * Creates a default instance of this class
     *
     * @return FileField
     */
    public static function createInstance()
    {
        return new FileField('ISerializable', 'ISerializable');
    }
    
    /**
     * Return true if field supports
     * image preview
     *
     * @return bool
     */
    public function getAllowPreview()
    {
        return $this->allowPreview;
    }
    
    public function setAllowPreview($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->allowPreview = $value;
    }
    
    /**
     * Gets allowed extension array
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }
    
    /**
     * Adds a allowed extension
     *
     * @param string $value
     */
    public function addAllowedExtension($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->allowedExtensions[] = $value;
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
}

/**
 * Implements a field that is used to persist a value accross events.
 * This field is usually invisible in the view. The HtmlFormView, for example,
 * will render this field as a hidden field.
 *
 * @package WebCore
 * @subpackage Model
 */
class Persistor extends BoundControlModelBase
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value)
    {
        parent::__construct($name, $value, true);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Persistor
     */
    public static function createInstance()
    {
        return new Persistor('ISerializable', '');
    }
}

/**
 * Represents a single-line text input
 *
 * @package WebCore
 * @subpackage Model
 */
class TextField extends FieldModelBase
{
    protected $maxChars;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        $this->maxChars = 255;
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TextField
     */
    public static function createInstance()
    {
        return new TextField('ISerializable', 'ISerializable');
    }
    
    /**
     * Determines the maximum length of characters to accept
     *
     * @return int
     */
    public function getMaxChars()
    {
        return $this->maxChars;
    }
    
    /**
     * Determines the maximum length of characters to accept
     *
     * @param int $value
     */
    public function setMaxChars($value)
    {
        if (!is_int($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter \'value\' must be of type \'int\'');
        $this->maxChars = $value;
    }
}

/**
 * Represents a single-line text input for password input
 *
 * @package WebCore
 * @subpackage Model
 */
class PasswordField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return PasswordField
     */
    public static function createInstance()
    {
        return new PasswordField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for email input
 *
 * @package WebCore
 * @subpackage Model
 */
class EmailField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $this->validator = new EmailFieldValidator();
        $this->maxChars  = 200;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return EmailField
     */
    public static function createInstance()
    {
        return new EmailField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for website input
 *
 * @package WebCore
 * @subpackage Model
 */
class WebSiteField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param str $name
     * @param str $caption
     * @param str $value
     * @param bool $isRequired
     * @param str $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $this->validator = new RegExFieldValidator("(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)", "Dirección web incorrecta.");
        $this->maxChars = 200;
    }

    /**
     * Creates a default instance of this class
     *
     * @return WebSiteField
     */
    public static function createInstance()
    {
        return new WebSiteField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for phone number input
 *
 * @package WebCore
 * @subpackage Model
 */
class PhoneNumberField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $this->validator = new PhoneNumberFieldValidator();
        $this->maxChars  = 20;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return PhoneNumberField
     */
    public static function createInstance()
    {
        return new PhoneNumberField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for money input
 *
 * @package WebCore
 * @subpackage Model
 */
class MoneyField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '0', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $validator = new NumericFieldValidator();
        $validator->setAllowDecimals(true);
        $validator->setMinimumValue(0);
        $validator->setIsMoney(true);
        $this->setValidator($validator);
        $this->maxChars = 18;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return MoneyField
     */
    public static function createInstance()
    {
        return new MoneyField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for integer input
 *
 * @package WebCore
 * @subpackage Model
 */
class IntegerField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '0', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $validator = new NumericFieldValidator();
        $validator->setAllowDecimals(false);
        $validator->setMinimumValue(0);
        $validator->setIsMoney(false);
        $this->validator = $validator;
        $this->maxChars  = 12;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return IntegerField
     */
    public static function createInstance()
    {
        return new IntegerField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for date input
 *
 * @package WebCore
 * @subpackage Model
 */
class DateTextField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '0', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $validator = new DateFieldValidator();
        $this->validator = $validator;
        $this->maxChars  = 10;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return IntegerField
     */
    public static function createInstance()
    {
        return new DateTextField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for decimals input
 *
 * @package WebCore
 * @subpackage Model
 */
class DecimalField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '0', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $validator = new NumericFieldValidator();
        $validator->setAllowDecimals(true);
        $validator->setMinimumValue(0);
        $validator->setIsMoney(false);
        $this->validator = $validator;
        $this->maxChars  = 12;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return DecimalField
     */
    public static function createInstance()
    {
        return new DecimalField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a simple multi-line text input
 *
 * @package WebCore
 * @subpackage Model
 */
class TextArea extends FieldModelBase
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TextArea
     */
    public static function createInstance()
    {
        return new TextArea('ISerializable', 'ISerializable');
    }
    
}

/**
 * Represents a rich text (HTML) multi-line text input
 *
 * @package WebCore
 * @subpackage Model
 */
class RichTextArea extends FieldModelBase
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RichTextArea
     */
    public static function createInstance()
    {
        return new RichTextArea('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a field for date input
 * @package WebCore
 * @subpackage Model
 */
class DateField extends FieldModelBase
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $this->validator = new DateFieldValidator();
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return DateField
     */
    public static function createInstance()
    {
        return new DateField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a ComboBox input
 *
 * @package WebCore
 * @subpackage Model
 */
class ComboBox extends FieldModelBase implements IEventTrigger
{
    protected $eventName;
    protected $eventValue;
    /**
     *@var IndexedCollection
     */
    protected $options;
    protected $multiline;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param string $eventName Leave empty if not intended for event triggering
     * @param string $eventValue Leave empty if not intended for event triggering
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $eventName = '', $eventValue = '', $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, true, $helpMessage);
        
        if (is_string($eventName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = eventName');
        
        $this->eventName  = $eventName;
        $this->eventValue = $eventValue;
        $this->options    = new IndexedCollection();
        $this->multiline  = false;
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
     * @param string $display The string to display in the option
     * @param string $category The category for this option
     */
    public function addOption($value, $display, $category = '')
    {
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
     * Gets the multiline value.
     *
     * @return bool
     */
    public function getMultiline()
    {
        return $this->multiline;
    }
    
    /**
     * Sets the multiline value.
     *
     * @param bool $value
     */
    public function setMultiline($value)
    {
        $this->multiline = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return CollectionBase
     */
    public static function createInstance()
    {
        return new ComboBox('ISerializable', 'ISerializable');
    }
}

class CheckBoxGroup extends ComboBox
{
    public function __construct($name, $caption, $value = '', $eventName = '', $eventValue = '', $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $eventName, $eventValue, $helpMessage);
        $this->isRequired = false;
    }
}

/**
 * Represents a checkbox input
 *
 * @package WebCore
 * @subpackage Model
 */
class CheckBox extends FieldModelBase
{
    protected $checkedValue;
    protected $uncheckedValue;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param string $checkedValue
     * @param string $uncheckedValue
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '0', $checkedValue = '1', $uncheckedValue = '0', $helpMessage = '')
    {
        if (is_string($checkedValue) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = checkedValue');
        if (is_string($uncheckedValue) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = uncheckedValue');
        
        $this->checkedValue   = $checkedValue;
        $this->uncheckedValue = $uncheckedValue;
        
        parent::__construct($name, $caption, $value, true, $helpMessage);
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
     * Creates a default instance of this class
     *
     * @return CollectionBase
     */
    public static function createInstance()
    {
        return new CheckBox('ISerializable', 'ISerializable');
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
}

/**
 * Represents a control container with a caption within a Form.
 *
 * @package WebCore
 * @subpackage Model
 */
class FormSection extends FieldContainerModelBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
    }
    
    public function validate()
    {
        return true;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return FormSection
     */
    public static function createInstance()
    {
        return new FormSection('ISerializable', 'ISerializable');
    }
}

/**
 * Represents the standard web Form model implementation.
 *
 * @package WebCore
 * @subpackage Model
 */
class Form extends FieldContainerModelBase implements IBindingTarget, IRootModel
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
    }
    
    /**
     * Adds a button to the buttonContainer subcontainer
     *
     * @param ButtonModelBase $button
     */
    public function addButton(&$button)
    {
        $this->getChildren()->addControl($button);
    }
    
    /**
     * Gets a button, given its name by searching for it recursively.
     *
     * @param string $buttonName
     * @return ButtonModelBase
     */
    public function getButton($buttonName)
    {
        return $this->getChildren()->getControl($buttonName, true, 'ButtonModelBase');
    }
    
    /**
     * Gets all button names within the form
     * 
     * @param bool $searchRecursive
     * @return array
     */
    public function getButtonNames($searchRecursive = true)
    {
        return $this->getChildren()->getControlNames($searchRecursive, 'ButtonModelBase');
    }
    
    /**
     * Determines if a button control exists, given its name
     *
     * @param string $buttonName
     * @return bool
     */
    public function buttonExists($buttonName)
    {
        return $this->getChildren()->controlExists($buttonName, 'ButtonModelBase');
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
    
    /**
     * Binds all IBindingTargetMember controls within the form to the given data source.
     * Also, sets IEventTrigger controls by looking for the following key pattern in the collection
     * IEventTrigger->getEventName() . '_eventValue'
     *
     * @param KeyedCollectionBase $dataSource
     */
    public function dataBind(&$dataSource)
    {
        if (is_object($dataSource) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'parameter = dataSource');
        
        if (ObjectIntrospector::isA($dataSource, 'Popo'))
            $objSource = $dataSource->toDataSource();
        else
            $objSource = $dataSource;
        
        if (ObjectIntrospector::isExtending($objSource, 'KeyedCollectionBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'parameter = dataSource');
        
        // First, update the controls that are member-wise bindable
        $controlNames = $this->getChildren()->getImplementingControlNames(true, 'IBindingTargetMember');
        
        foreach ($controlNames as $controlName)
        {
            $control = $this->getChildren()->getControl($controlName, true);
            
            if ($control->getIsBindable() && $objSource->keyExists($control->getBindingMemberName()))
            {
                $control->setValue($objSource->getValue($control->getBindingMemberName()));
            }
        }
        
        // Now, bind the event values in the model
        $eventTriggerControlNames = $this->getChildren()->getEventTriggerControlNames();
        
        foreach ($eventTriggerControlNames as $controlName)
        {
            $control       = $this->getChildren()->getControl($controlName, true);
            $eventValueKey = Controller::PREFIX_EVENTVALUE . $control->getName() . "_" . $control->getEventName();
            if ($objSource->keyExists($eventValueKey))
            {
                $control->setEventValue($objSource->getValue($eventValueKey));
            }
        }
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Form
     */
    public static function createInstance()
    {
        return new Form('serializableInstance', '');
    }
}

/**
 * Represents button that is used to raise an event.
 * For example, a button to submit a form
 *
 * @package WebCore
 * @subpackage Model
 */
class Button extends ButtonModelBase
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
     * @return Button
     */
    public static function createInstance()
    {
        return new Button('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text input for credit card input
 *
 * @package WebCore
 * @subpackage Model
 */
class CreditCardField extends TextField
{
    private $type;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $this->validator = new CreditCardFieldValidator();
    }
    
    public function getCreditCardType()
    {
        return $this->type;
    }
    
    public function setCreditCardType($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->type = $value;
    }
}

/**
 * Represents a field for time input
 * @package WebCore
 * @subpackage Model
 */
class TimeField extends TextField
{
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '')
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TimeField
     */
    public static function createInstance()
    {
        return new TimeField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a single-line text
 *
 * @package WebCore
 * @subpackage Model
 */
class LabelField extends FieldModelBase
{
    private $htmlEncode;
    
    /**
     * Creates a new instance of the class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $value = '', $helpMessage = '', $htmlEncode = true)
    {
        parent::__construct($name, $caption, $value, false, $helpMessage);
        $this->htmlEncode = $htmlEncode;
    }
    
    /**
     * Gets true if the encode should be encoded
     * 
     * @return bool
     */
    public function getHtmlEncode()
    {
        return $this->htmlEncode;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TextField
     */
    public static function createInstance()
    {
        return new LabelField('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a Month-Year comboboxes group
 * @todo Fix tooltip, SUCKS!
 * @package WebCore
 * @subpackage Model
 */
class MonthYearComboBox extends CompoundFieldModelBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param string $value
     * @param bool $isRequired
     * @param string $helpMessage
     * @param int $fromYear
     */
    public function __construct($name, $caption, $value = '', $isRequired = true, $helpMessage = '', $fromYear = -1)
    {
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        
        $comboMonth = new ComboBox($this->name . 'Month', $this->caption);
        
        $months     = explode(',', Resources::getValue(Resources::SRK_MONTH_NAMES));
        $countMonth = 1;
        
        foreach ($months as $month)
        {
            $comboMonth->addOption($countMonth, $month);
            $countMonth++;
        }
        
        $comboYear = new ComboBox($this->name . 'Year', $this->caption);
        $fromYear = ($fromYear == -1) ? intval(date("Y")) - 10 : $fromYear;
        
        for ($i = 0; $i < 20; $i++)
        {
            $year = $fromYear + $i;
            $comboYear->addOption($year, $year);
        }
        
        if ($value === '')
        {
            $comboYear->setValue(intval(date('Y')));
            $comboMonth->setValue(intval(date('m')));
        }
        
        $this->getChildren()->addControl($comboMonth);
        $this->getChildren()->addControl($comboYear);
        
        if ($value != '') $this->setValue($value);
    }
    
    /**
     * Gets control value like an array
     *
     * @return array
     */
    public function &getValue()
    {
        $comboMonth = $this->getChildren()->getControl($this->name . 'Month');
        $comboYear  = $this->getChildren()->getControl($this->name . 'Year');
        
        return array(
            "year" => intval($comboYear->getValue()),
            "month" => intval($comboMonth->getValue())
        );
    }
    
    /**
     * Sets the value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $date = strtotime($value);
        
        $comboMonth = $this->getChildren()->getControl($this->name . 'Month');
        $comboYear  = $this->getChildren()->getControl($this->name . 'Year');
        
        $comboMonth->setValue(date("m", $date));
        $comboYear->setValue(date("Y", $date));
    }
    
    /**
     * Validates values in compound
     *
     * @return bool
     */
    public function validate()
    {
        $comboMonth = $this->getChildren()->getControl($this->name . 'Month', true);
        $comboYear  = $this->getChildren()->getControl($this->name . 'Year', true);
        $this->setErrorMessage('');
        
        if ($comboMonth->validate() && $comboYear->validate())
            return true;
        
        $errorMessage = $comboMonth->getErrorMessage();
        
        if ($errorMessage == '')
            $errorMessage = $comboYear->getErrorMessage();
        
        $this->setErrorMessage($errorMessage);
        
        return false;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return MonthYearComboBox
     */
    public static function createInstance()
    {
        return new MonthYearComboBox('ISerializable', 'ISerializable');
    }
}

/**
 * Creates a list compound field
 *
 * @package WebCore
 * @subpackage Model
 */
class CompoundListField extends CompoundFieldModelBase
{
    /**
     * @var KeyedCollection
     */
    private $items;
    
    /**
     * Creates a instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param KeyedCollection $items
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $items = null, $helpMessage = '')
    {
        parent::__construct($name, $caption, '', true, $helpMessage);
        
        if (is_null($items))
            $this->items = new KeyedCollection();
        else
            $this->items = $items;
        
        $this->getChildren()->addControl(new Persistor($name . "_items", json_encode($this->items->getArrayReference())));
    }
    
    public function validate()
    {
        return true;
    }
    
    /**
     * Gets the control value
     *
     * @return string
     */
    public function &getValue()
    {
        return $this->getChildren()->getControl($this->name . '_items', true)->getValue();
    }
    
    /**
     * Sets the value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->getChildren()->getControl($this->name . '_items', true)->setValue($value);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return CompoundListField
     */
    public static function createInstance()
    {
        return new CompoundListField('ISerializable', 'ISerializable');
    }
}

/**
 * Creates a datetime compound field
 *
 * @package WebCore
 * @subpackage Model
 */
class DateTimeField extends FieldModelBase
{
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
        parent::__construct($name, $caption, $value, $isRequired, $helpMessage);
        $this->validator = new RegExFieldValidator("/\d{4}(-\d{2}){2} \d{2}(:\d{2}){2}/");
        $this->setValue($value);
    }
    
    /**
     * Sets the date/time value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $timestamp = strtotime($value);
        if ($value == '' || $timestamp === false)
            parent::setValue('');
        else
            parent::setValue($timestamp);
    }
    
    /**
     * Gets the control value
     *
     * @return string
     */
    public function &getValue($format = 'Y-m-d H:i:s')
    {
        if (parent::getValue() == '') return '';
        
        $val = date($format, intval(parent::getValue()));
        
        return $val;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return DateTimeField
     */
    public static function createInstance()
    {
        return new DateTimeField('ISerializable', 'ISerializable');
    }
}

/**
 * Creates a text block component
 *
 * @package WebCore
 * @subpackage Model
 */
class TextBlock extends ControlModelBase
{
    private $text;
    private $htmlEncode;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $text
     * @param bool $htmlEncode
     */
    public function __construct($name, $text, $htmlEncode = true)
    {
        parent::__construct($name, true);
        $this->setText($text);
        $this->htmlEncode = $htmlEncode;
    }
    
    /**
     * Gets true if the encode should be encoded
     * 
     * @return bool
     */
    public function getHtmlEncode()
    {
        return $this->htmlEncode;
    }
    
    /**
     * Gets the text to display
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * Sets the text to display
     *
     * @param string $value
     */
    public function setText($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = text');
        
        $this->text = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TextBlock
     */
    public static function createInstance()
    {
        return new TextBlock('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a tab view container with a caption within a Form.
 *
 * @package WebCore
 * @subpackage Model
 */
class TabContainer extends FieldContainerModelBase
{
    /**
     * @var Persistor
     */
    protected $activeTabPagePersistor;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
        
        $this->activeTabPagePersistor = new Persistor($this->name . '_activeTabName', '');
        
        if (HttpRequest::getInstance()->getRequestVars()->keyExists($this->activeTabPagePersistor->getName()))
        {
            $value = HttpRequest::getInstance()->getRequestVars()->getValue($this->activeTabPagePersistor->getName());
            $this->activeTabPagePersistor->setValue($value);
        }
    }
    
    /**
     * Adds a container model to the collection
     *
     * Overrides the parent method in order to set the active tab page if there is none
     *
     * @param ContainerModelBase $container
     */
    public function addContainer(&$container)
    {
        parent::addContainer($container);
        
        if (ObjectIntrospector::isA($container, 'TabPage') && $this->activeTabPagePersistor->getValue() == '')
            $this->activeTabPagePersistor->setValue($container->getName());
    }
    
    /**
     * Returns all the names of the TabPage Containers
     * 
     * @return array
     */
    public function getTabPageNames()
    {
        return $this->getChildren()->getTypedControlNames(true, "TabPage");
    }
    
    /**
     * Sets the tab page that will be displayed on render
     *
     * @param string $name
     */
    public function setActiveTabPage($name)
    {
        if (array_search($name, $this->getTabPageNames()) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "TabPage '$name' does not exist.");
        
        $this->activeTabPagePersistor->setValue($name);
    }
    
    /**
     * Gets the name of the active tab page
     *
     * @return string;
     */
    public function getActiveTabPagePersistor()
    {
        return $this->activeTabPagePersistor;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TabContainer
     */
    public static function createInstance()
    {
        return new TabContainer('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a tab page container with a caption within a TabContainer.
 *
 * @package WebCore
 * @subpackage Model
 */
class TabPage extends FieldContainerModelBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
    }
    
    /**
     * Gets whether the filed is bindable.
     *
     * @return bool
     */
    public function getIsBindable()
    {
        return false;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TabPage
     */
    public static function createInstance()
    {
        return new TabPage('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a multiselector control.
 *
 * @package WebCore
 * @subpackage Model
 */
class MultiSelector extends CompoundFieldModelBase
{
    protected $dataSource;
    protected $sourceDataSource;
    protected $destinationDataSource;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param KeyedCollection $dataSource
     * @param IndexedCollection $value
     * @param string $isRequired
     * @param string $helpMessage
     */
    public function __construct($name, $caption, $dataSource = null, $value = null, $isRequired = true, $helpMessage = '')
    {
        if (is_null($dataSource) === false && ObjectIntrospector::isClass($dataSource, 'KeyedCollection') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = dataSource');
        
        if (is_null($value) === false && ObjectIntrospector::isClass($value, 'IndexedCollection') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        parent::__construct($name, $caption, '', $isRequired, $helpMessage);
        
        $this->getChildren()->addControl(new Persistor($name . '_items', ''));
        
        if (is_null($dataSource) === true)
            $this->dataSource = new KeyedCollection();
        else
            $this->dataSource = $dataSource;
        
        $this->setValue($value);
    }
    
    /**
     * Creates a empty instance of this class
     *
     * @return SourceCodeControl
     */
    public static function createInstance()
    {
        return new MultiSelector('ISerializable', 'ISerializable');
    }
    
    /**
     * Sets the value.
     *
     * @param mixed $value. NULL, string JSON encoded array, IndexedCollectionBase, DataTableAdapter
     */
    public function setValue($value)
    {
        if (is_null($value))
        {
            $this->value = new IndexedCollection();
            $this->getChildren()->getControl($this->name . '_items')->setValue("[]");
        }
        elseif (is_string($value))
        {
            $arrayValue  = json_decode($value);
            $this->value = new IndexedCollection();
            $this->value->addRange($arrayValue);
            
            $this->getChildren()->getControl($this->name . '_items', false)->setValue($value);
        }
        elseif (ObjectIntrospector::isExtending($value, 'IndexedCollectionBase'))
        {
            $this->value = $value;
            $this->getChildren()->getControl($this->name . '_items', false)->setValue(json_encode($value->getArrayReference()));
        }
        else
        {
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        }
    }
    
    /**
     * Gets the value
     *
     * @return IndexedCollectionBase;
     */
    public function &getValue()
    {
        return $this->value;
    }
    
    /**
     * Sets the dataSource
     *
     * @param KeyedCollection
     */
    public function &setDataSource($dataSource)
    {
        return $this->dataSource;
    }
    
    /**
     * Gets the dataSource
     *
     * @return KeyedCollection
     */
    public function &getDataSource()
    {
        return $this->dataSource;
    }
}
?>