<?php
/**
 * @category Forms: Calendar
 * @tutorial Learn how to use the calendar control.
 * @author Simio Van Helsing
 */

/**
 * @package WebCore
 * @subpackage Samples
 *
 * Calendar Widget Sample
 */
class CalendarWidget extends WidgetBase
{
    /**
     * Creates a new instance of this class.
     */
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        // Create the calendar Model
        $this->model = new Calendar('calendar', 'Calendar Sample');
        
        // Create the view
        $this->view = new HtmlCalendarView($this->model);
        
        $this->handleRequest();
    }
    
    /**
     * Creates a default instance of this class
     */
    public static function createInstance()
    {
        return new CalendarWidget();
    }
    
    /**
     * Overrides parent WidgetBase::handleRequest.
     * Calendar model should not be bound from the request values
     */
    public function handleRequest()
    {
        $dataContext = DataContext::getInstance();
        $dataSource  = $dataContext->getAdapter('orders'); // get the main table
        $dataSource->innerJoin('addresses', 'addresses.id = orders.address_shipping_id')->innerJoin('states', 'states.id = addresses.state_id')->innerJoin('users', 'orders.user_id = users.id')->addFunctionField("CONCAT(users.name_first, ' ', users.name_last)", 'Name')->addFunctionField("CONCAT(addresses.line1, ' ', addresses.line2, ' ', addresses.line3, ', ', states.name)", 'Shipping Address')->addField("orders", "order_date")->addField("orders", "total", "Total")->addField("orders", "status_code", 'Status'); //->where('usuarios.aplicacion_id = 3'); // Set a default condition
        
        $this->model->setAppointmentFieldBindingName(Appointment::FIELD_START_DATE, 'order_date');
        $this->model->setAppointmentFieldBindingName(Appointment::FIELD_END_DATE, 'order_date');
        $this->model->setAppointmentFieldBindingName(Appointment::FIELD_TITLE, 'Name');
        $this->model->dataBind($dataSource);
        
        // Additional appointments must be added after the dataBind method is called,
        // or else, the appointment will be cleared when dataBind is called
        $appointment = new Appointment('Call with Bob', 'Call 01 800 5555555. Conference ID 25636');
        $this->model->addAppointment($appointment);
        
        if (Controller::isPostBack($this->model) && $this->view->getIsAsynchronous())
        {
            $this->view->render();
            exit();
        }
    }
    
    public static function onAfterDataBindCalendar(&$sender, &$eventArgs)
    {
    }
}

$sample = new CalendarWidget();
$sample->handleRequest();
?>
