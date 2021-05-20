<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides models of controls in a calendar
 *
 * @author Jose Luis Gonzalez <luis.gonzalez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.model.php";

/**
 * Provides a Calendar
 *
 * @package WebCore
 * @subpackage Model
 */
class Calendar extends ControlModelBase implements IBindingTarget, IRootModel
{
    /**
     * @var string
     */
    protected $caption;
    
    /**
     * @var Persistor
     */
    protected $initialDate;
    
    /**
     * @var Persistor
     */
    protected $viewMode;
    
    /**
     * @var IndexedCollection
     */
    protected $appointments;
    
    /**
     * @var KeyedCollection
     */
    protected $appointmentFieldsBindingNames;
    
    /**
     * @var IndexedCollection
     */
    protected $onBeforeDataBindCallbacks;
    
    /**
     * @var IndexedCollection
     */
    protected $onAfterDataBindCallbacks;
    
    const VIEWMODE_DAY = 'day';
    const VIEWMODE_WEEK = 'week';
    const VIEWMODE_MONTH = 'month';
    
    /**
     * Constructor
     *
     * @param string $name
     * @param string $caption
     * @param string $initialDate. Any date accepted by strtotime
     * @param string $view. Calendar::VIEWMODE_*
     */
    public function __construct($name, $caption, $initialDate = '', $viewMode = '')
    {
        parent::__construct($name, true);
        
        if ($viewMode == '')
            $viewMode = self::VIEWMODE_MONTH;
        if ($initialDate == '')
            $initialDate = 'now';
        $tmpDate = strtotime($initialDate);
        
        if ($tmpDate === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = initialDate. '$initialDate' is not a valid date");
        
        $this->caption      = $caption;
        $this->appointments = new IndexedCollection();
        $this->viewMode     = new Persistor('viewMode', $viewMode);
        $this->initialDate  = new Persistor('initialDate', $tmpDate);
        
        $request = HttpContext::getRequest()->getRequestVars();
        if ($request->keyExists($this->viewMode->getName()))
            $this->setViewMode($request->getValue($this->viewMode->getName()));
        
        if ($request->keyExists($this->initialDate->getName()))
            $this->setInitialDate($request->getValue($this->initialDate->getName()));
        
        $this->appointmentFieldsBindingNames = new KeyedCollection();
        $this->onBeforeDataBindCallbacks     = new IndexedCollection();
        $this->onAfterDataBindCallbacks      = new IndexedCollection();
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
     * Gets the callbacks that are executed when the dataBind() method is called.
     * The callbacks must be in the standard PHP callback form.
     * The signature of the callbacks must be function callbackFunction(&$model as Calendar, &$dataSource as IObject)
     *
     * @return IndexedCollection
     */
    public function &getOnBeforeDataBindCallbacks()
    {
        return $this->onBeforeDataBindCallbacks;
    }
    
    /**
     * Gets the callbacks that are executed reght before the dataBind() method returns.
     * The callbacks must be in the standard PHP callback form.
     * The signature of the callbacks must be function callbackFunction(&$model as Grid, &$dataSource as IObject)
     *
     * @return IndexedCollection
     */
    public function &getOnAfterDataBindCallbacks()
    {
        return $this->onAfterDataBindCallbacks;
    }
    
    /**
     * Returns the collection of Appointment items
     *
     * @return IndexedCollection
     */
    public function &getAppointments()
    {
        return $this->appointments;
    }
    
    /**
     * Adds an appointment
     *
     * @param Appointment appoinment
     */
    public function addAppointment($appointment)
    {
        if (ObjectIntrospector::isA($appointment, 'Appointment') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Param = appointment');
        
        $this->appointments->addItem($appointment);
    }
    
    /**
     * Gets the persistor for initialDate
     *
     * @return Persistor
     */
    public function &getInitialDatePersistor()
    {
        return $this->initialDate;
    }
    
    /**
     * Gets the persistor for viewMode
     *
     * @return Persistor
     */
    public function &getViewModePersistor()
    {
        return $this->viewMode;
    }
    
    /**
     * Shortcut to get the initial date from the persistor
     *
     * @param $format
     *
     * return mixed
     */
    public function getInitialDate($format = '')
    {
        $date = strtotime(date("Y-m-d", $this->initialDate->getValue()));
        
        if ($format == '')
            return $date;
        
        return date($format, $date);
    }
    
    /**
     * Shortcut to get the initial date from the persistor
     *
     * @param $format
     *
     * return mixed
     */
    public function setInitialDate($value)
    {
        $date = strtotime($value);
        
        if ($date === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = value. '$value' is not a valid date.");
        
        $this->initialDate->setValue($date);
    }
    
    /**
     * Shortcut to get the view mode for the calendar
     *
     * @return string
     */
    public function getViewMode()
    {
        return $this->viewMode->getValue();
    }
    
    /**
     * Shortcut to set the view mode for the calendar
     *
     * @param string $value. One of the CALENDAR_VIEW_MODE_* consts
     */
    public function setViewMode($value)
    {
        $this->viewMode->setValue($value);
    }
    
    /**
     * Sets the binding name for the appointment property
     *
     * @param string $property One of the FIELD_* constants
     * @param string $bindingName
     */
    public function setAppointmentFieldBindingName($fieldName, $bindingName)
    {
        if (is_string($bindingName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Param = bindingName');
        
        $this->appointmentFieldsBindingNames->setValue($fieldName, $bindingName);
    }
    
    /**
     * Gets the binding name for the appointment property
     *
     * @param string $property. One of the FIELD_* constants
     *
     * @return string
     */
    public function getAppointmentFieldBindingName($fieldName)
    {
        if ($this->appointmentFieldsBindingNames->keyExists($fieldName))
            return $this->appointmentFieldsBindingNames->getValue($fieldName);
        
        return $fieldName;
    }
    
    /**
     * Gets the very first date for the calendar.
     * This date will be the first of the month if the month starts on sunday
     * or the first sunday before the month
     *
     * @param $format
     *
     * return mixed
     */
    public function getFirstDay($format = '')
    {
        $firstDayMonth = strtotime(date('Y-m-01', $this->initialDate->getValue()));
        $date          = strtotime('-' . date('w', $firstDayMonth) . ' day', $firstDayMonth);
        
        if ($format == '')
            return $date;
        
        return date($format, $date);
    }
    
    /**
     * Gets the very last date for the calendar.
     * This date will be the last of the month if the month ends on saturday
     * or the first saturday after the month
     *
     * @param $format
     *
     * return mixed
     */
    public function getLastDay($format = '')
    {
        $lastDayMonth = strtotime(date('Y-m-t', $this->initialDate->getValue()));
        $date         = strtotime('+' . (6 - date('w', $lastDayMonth)) . ' day', $lastDayMonth);
        
        if ($format == '')
            return $date;
        
        return date($format, $date);
    }
    
    /**
     * Gets the month of the initial date
     *
     * @return int. 1 through 12
     */
    public function getMonth()
    {
        return intval(date("n", $this->initialDate->getValue()));
    }
    
    /**
     * Creates a default instance of Calendar
     *
     * @return Calendar
     */
    public static function createInstance()
    {
        return new Calendar('ISerializable', 'ISerializable');
    }
    
    /**
     * Binds the datasource into the object
     *
     * @param mixed dataSource. 
     */
    public function dataBind(&$dataSource)
    {
        $this->appointments->clear();
        
        foreach ($this->onBeforeDataBindCallbacks as $callback)
        {
            call_user_func_array($callback, array(
                &$this,
                &$dataSource
            ));
        }
        
        if (ObjectIntrospector::isA($dataSource, 'CollectionBase'))
            $this->dataBindFromCollection($dataSource);
        else if (ObjectIntrospector::isA($dataSource, 'DataTableAdapterBase'))
            $this->dataBindFromTableAdapter($dataSource);
        else
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Param = dataSource. DataSource is not of type CollectionBase or TableAdapter');
        
        foreach ($this->onAfterDataBindCallbacks as $callback)
        {
            call_user_func_array($callback, array(
                &$this,
                &$dataSource
            ));
        }
        
        $this->appointments->objectSort(Appointment::FIELD_START_DATE);
    }
    
    /**
     * Binds a Collection into the object
     *
     * @param CollectionBase $dataSource.
     */
    protected function dataBindFromCollection(&$dataSource)
    {
        foreach ($dataSource as $item)
        {
            if (is_array($item) === false && is_object($item) === false)
                continue;
            $appointment = Appointment::createInstance();
            $appointment->setBindingNames($this->appointmentFieldsBindingNames);
            $appointment->dataBind($item);
            $this->addAppointment($appointment);
        }
    }
    
    /**
     * Binds a TableAdapter into the object
     *
     * @param TableAdapter $dataSource.
     */
    protected function dataBindFromTableAdapter(&$dataSource)
    {
        // Add conditions. Use State
        $firstDay = $this->getFirstDay("Y-m-d 00:00:00");
        $lastDay  = $this->getLastDay("Y-m-d 23:59:59");
        
        $startDateBindingName = $this->getAppointmentFieldBindingName(Appointment::FIELD_START_DATE);
        $endDateBindingName   = $this->getAppointmentFieldBindingName(Appointment::FIELD_END_DATE);
        
        $values = $dataSource->where("$startDateBindingName >= '$firstDay'")->where("$endDateBindingName <= '$lastDay'")->selectNew();
        
        $this->dataBindFromCollection($values);
    }
}

/**
 * Provides minimal data for an appointment
 */
class Appointment extends Popo
{
    const FIELD_START_DATE = 'startDate';
    const FIELD_END_DATE = 'endDate';
    const FIELD_TITLE = 'title';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_IS_ALL_DAY_EVENT = 'isAllDayEvent';
    
    /**
     * @var KeyedCollection
     */
    protected $additionalFields;
    
    /**
     * @var KeyedCollection
     */
    protected $appointmentFieldsBindingNames;
    /**
     * Creates an Instance of Appointment with the title provided and an optional description.
     * If no start date is provided, the current date and time is used
     * If no end date is provided, the start date is used
     * 
     * @param string $title
     * @param string $description
     * @param mixed $startDate Any date accepted by strtotime
     * @param mixed $endDate Any date accepted by strtotime
     */
    public function __construct($title, $description = '', $startDate = '', $endDate = '')
    {
        parent::__construct();
        
        if ($startDate == '')
            $startDate = 'now';
        if ($endDate == '')
            $endDate = $startDate;
        
        $this->addFieldValue(self::FIELD_START_DATE, '');
        $this->addFieldValue(self::FIELD_END_DATE, '');
        $this->addFieldValue(self::FIELD_IS_ALL_DAY_EVENT, 0);
        $this->addFieldValue(self::FIELD_TITLE, $title);
        $this->addFieldValue(self::FIELD_DESCRIPTION, $description);
        
        $this->setStartDate($startDate);
        $this->setEndDate($endDate);
        $this->additionalFields              = new KeyedCollection();
        $this->appointmentFieldsBindingNames = new KeyedCollection();
        
        if ($this->startDate === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = startDate. '$initialDate' is not a valid date");
        if ($this->endDate === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = endDate. '$endDate' is not a valid date");
        if ($this->startDate > $this->endDate)
            throw new SystemException(SystemException::EX_INVALIDOPERATION, "Initial date '$startDate' must happen before end date '$endDate'");
    }
    
    /**
     * Sets the start date for the appointment
     *
     * @param string $value
     */
    public function setStartDate($value)
    {
        $tmpValue = strtotime($value);
        
        if ($tmpValue === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Param = value. '$value' is not a valid date");
        
        $this->startDate = $tmpValue;
    }
    
    /**
     * Gets the start date/time for the appointment
     *
     * @param string format
     *
     * @return mixed
     */
    public function getStartDate($format = '')
    {
        if ($format == '')
            return $this->startDate;
        
        return date($format, $this->startDate);
    }
    
    /**
     * Sets the end date for the appointment
     *
     * @param string value
     */
    public function setEndDate($value)
    {
        $tmpValue = strtotime($value);
        
        if ($tmpValue === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Param = value. '$value' is not a valid date");
        
        $this->endDate = $tmpValue;
    }
    
    /**
     * Gets the end date/time for the appointment
     *
     * @param string format
     *
     * @return mixed
     */
    public function getEndDate($format = '')
    {
        if ($format == '')
            return $this->endDate;
        
        return date($format, $this->endDate);
    }
    
    /**
     * Gets all the additional fields for the current appointment
     *
     * @return KeyedCollection
     */
    public function getAdditionalFields()
    {
        return $this->additionalFields;
    }
    /**
     * Retuns a default instance of Appointment
     *
     * @return Appointment
     */
    public static function createInstance()
    {
        return new Appointment('ISerializable');
    }
    
    /**
     * Sets the binding names for the fields
     * 
     * @param KeyedCollection $bindingNames Use FIELD_* prefixed constants as keys
     */
    public function setBindingNames(&$bindingNames)
    {
        foreach ($bindingNames as $key => $value)
            $this->appointmentFieldsBindingNames->setValue($key, $value);
        ;
    }
    
    /**
     * Binds the data source into the object
     *
     * @param Popo $dataSource.
     */
    public function dataBind(&$dataSource)
    {
        $boundFields = array();
        
        foreach ($this->appointmentFieldsBindingNames as $bindKey => $bindValue)
        {
            $value = $dataSource->getFields()->getValue($bindValue);
            if ($bindKey == self::FIELD_START_DATE)
                $this->setStartDate($value);
            elseif ($bindKey == self::FIELD_END_DATE)
                $this->setEndDate($value);
            else
                $this->setFieldValue($bindKey, $value);
            
            $boundFields[] = $bindValue;
        }
        
        foreach ($dataSource->getFields() as $key => $value)
        {
            if (array_search($key, $boundFields) !== false)
                continue;
            
            if ($key == self::FIELD_START_DATE)
                $this->setStartDate($value);
            elseif ($key == self::FIELD_END_DATE)
                $this->setEndDate($value);
            elseif ($this->getFields()->keyExists($key))
                $this->setFieldValue($key, $value);
            else
                $this->additionalFields->setValue($key, $value);
        }
    }
    
    /**
     * Magic method for getters and setters
     */
    public function __call($name, $arguments)
    {
        if (isset($name[4]) === false) // must be at least 4 chars long
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Method '$name' is not supported");
        
        $method = substr($name, 0, 3);
        $field  = substr($name, 3);
        
        $field = strtolower($field[0]) . substr($field, 1); //lowerCamelCase
        
        if ($method === 'set')
        {
            if (count($arguments) == 0)
                throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'Parameter = value');
            
            if ($this->hasField($field))
                $this->setFieldValue($field, $arguments[0]);
            else
                $this->additionalFields->setValue($field, $arguments[0]);
        }
        elseif ($method === 'get')
        {
            return $this->fields->getValue($field);
        }
        else
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Method '$name' is not supported");
    }
}
?>