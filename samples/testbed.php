<?php
/**
 * WebCore 3.0 Samples Browser
 * @version 1.0.0
 * @author Mario Di Vece <mario@unosquare.com>
 */
require_once 'initialize.inc.php';
HttpContext::getPermissionSet()->setDefaultResolve(Permission::PERMISSION_ALLOW);
HttpContext::applySecurity();

/**
 * AddressesGridWidget represents a Model-View pair to list 'Addresses' entities within a grid.
 * @package Application
 * @subpackage Grids
 * @version 2010.10.20.22.33.27
 * @author WebCore Scaffolder <webcore@localhost>
 */
class AddressesGridWidget extends WidgetBase
{
    // Protected control model declarations
    protected $ctlAddressesId;
    protected $ctlAddressesLine1;
    protected $ctlAddressesLine2;
    protected $ctlAddressesLine3;
    protected $ctlAddressesStateId;
    protected $ctlAddressesPostalCode;
    protected $ctlAddressesDirections;
    protected $ctlAddressesFisrtName;
    protected $ctlAddressesLastName;
    protected $ctlAddressesCompanyName;
    protected $ctlAddressesPhonePrimary;
    protected $ctlAddressesPhoneOffice;
    protected $ctlAddressesPhoneHome;
    protected $ctlStateIdStatesAbbreviation;
    protected $ctlStateIdStatesName;

    /**
     * Initializes control models and plugs them into the model.
     */
    protected function initializeComponent()
    {
        //Control instantiation
        $this->ctlAddressesId = new NumberBoundGridColumn(
            'addresses_id', 'Id', 'addresses.id', 'addresses.id');
        $this->ctlAddressesLine1 = new TextBoundGridColumn(
            'addresses_line1', 'Line 1', 100, 'addresses.line1', 'addresses.line1');
        $this->ctlAddressesLine2 = new TextBoundGridColumn(
            'addresses_line2', 'Line 2', 100, 'addresses.line2', 'addresses.line2');
        $this->ctlAddressesLine3 = new TextBoundGridColumn(
            'addresses_line3', 'Line 3', 100, 'addresses.line3', 'addresses.line3');
        $this->ctlAddressesStateId = new NumberBoundGridColumn(
            'addresses_state_id', 'State Id', 'addresses.state_id', 'addresses.state_id');
        $this->ctlAddressesPostalCode = new TextBoundGridColumn(
            'addresses_postal_code', 'Postal Code', 100, 'addresses.postal_code', 'addresses.postal_code');
        $this->ctlAddressesDirections = new TextBoundGridColumn(
            'addresses_directions', 'Directions', 100, 'addresses.directions', 'addresses.directions');
        $this->ctlAddressesFisrtName = new TextBoundGridColumn(
            'addresses_fisrt_name', 'Fisrt Name', 100, 'addresses.fisrt_name', 'addresses.fisrt_name');
        $this->ctlAddressesLastName = new TextBoundGridColumn(
            'addresses_last_name', 'Last Name', 100, 'addresses.last_name', 'addresses.last_name');
        $this->ctlAddressesCompanyName = new TextBoundGridColumn(
            'addresses_company_name', 'Company Name', 100, 'addresses.company_name', 'addresses.company_name');
        $this->ctlAddressesPhonePrimary = new TextBoundGridColumn(
            'addresses_phone_primary', 'Phone Primary', 100, 'addresses.phone_primary', 'addresses.phone_primary');
        $this->ctlAddressesPhoneOffice = new TextBoundGridColumn(
            'addresses_phone_office', 'Phone Office', 100, 'addresses.phone_office', 'addresses.phone_office');
        $this->ctlAddressesPhoneHome = new TextBoundGridColumn(
            'addresses_phone_home', 'Phone Home', 100, 'addresses.phone_home', 'addresses.phone_home');
        $this->ctlStateIdStatesAbbreviation = new TextBoundGridColumn(
            'state_id_states_abbreviation', 'Abbreviation', 100, 'states.abbreviation', 'states.abbreviation');
        $this->ctlStateIdStatesName = new TextBoundGridColumn(
            'state_id_states_name', 'Name', 100, 'states.name', 'states.name');

        // Root model instantiation
        $this->model = new Grid('addressesGridWidgetModel', 'Addresses');
        $this->model->addColumn($this->ctlAddressesId);
        $this->model->addColumn($this->ctlAddressesLine1);
        $this->model->addColumn($this->ctlAddressesLine2);
        $this->model->addColumn($this->ctlAddressesLine3);
        $this->model->addColumn($this->ctlAddressesStateId);
        $this->model->addColumn($this->ctlAddressesPostalCode);
        $this->model->addColumn($this->ctlAddressesDirections);
        $this->model->addColumn($this->ctlAddressesFisrtName);
        $this->model->addColumn($this->ctlAddressesLastName);
        $this->model->addColumn($this->ctlAddressesCompanyName);
        $this->model->addColumn($this->ctlAddressesPhonePrimary);
        $this->model->addColumn($this->ctlAddressesPhoneOffice);
        $this->model->addColumn($this->ctlAddressesPhoneHome);
        $this->model->addColumn($this->ctlStateIdStatesAbbreviation);
        $this->model->addColumn($this->ctlStateIdStatesName);

        $this->model->addColumn(new DetailsCommandGridColumn('detailsCommandColumn', 'detailsCommandColumn_Click', 'addresses_id'));

        // Paging and Sorting options
        $this->model->setDefaultSort('addresses_id', GridState::GRID_SORT_DESCENDING);
        $this->model->setPageSize(20);


        // Grid Print Preview
        $gridPrinter = new GridPrintEventManager('gridPrinter');
        $this->model->getChildren()->addControl($gridPrinter);

        // CSV Data Exporter
        $gridCsvExporter = new GridCsvExporterEventManager('gridCsvExporter');
        $this->model->getChildren()->addControl($gridCsvExporter);

        // View instantiation
        $this->view = new HtmlGridView($this->model);
        $this->view->setIsAsynchronous(true);
    }

    /**
     * Creates a new instance of this class.
     */
    public function __construct($name = 'addressesGridWidget')
    {
        parent::__construct($name);
        $this->initializeComponent();
        $this->registerEventHandlers();
    }

    /**
     * Creates a default instance of this class.
     * @return AddressesGridWidget
     */
    public static function createInstance()
    {
        return new AddressesGridWidget();
    }

    /**
     * Registers event handlers for controls.
     */
    protected function registerEventHandlers()
    {
        Controller::registerEventHandler('detailsCommandColumn_Click', array(__CLASS__, 'detailsCommandColumn_Clicked'));
    }

    /**
     * Gets this widget's associated model object.
     * @return Grid
     */
    public function &getModel()
    {
        return $this->model;
    }

    /**
     * Gets this widget's associated view object.
     * @return HtmlGridView
     */
    public function &getView()
    {
        return $this->view;
    }

    /**
     * Gets this widget's associated data source as a data adapter.
     * @return DataTableAdapterBase
     */
    public static function &getDataSource()
    {
        $dataSource = DataContext::getInstance()->getAdapter('Addresses');
        $dataSource
            ->joinRelated('States')
            ->addField('addresses', 'id', 'addresses_id')
            ->addField('addresses', 'line1', 'addresses_line1')
            ->addField('addresses', 'line2', 'addresses_line2')
            ->addField('addresses', 'line3', 'addresses_line3')
            ->addField('addresses', 'state_id', 'addresses_state_id')
            ->addField('addresses', 'postal_code', 'addresses_postal_code')
            ->addField('addresses', 'directions', 'addresses_directions')
            ->addField('addresses', 'fisrt_name', 'addresses_fisrt_name')
            ->addField('addresses', 'last_name', 'addresses_last_name')
            ->addField('addresses', 'company_name', 'addresses_company_name')
            ->addField('addresses', 'phone_primary', 'addresses_phone_primary')
            ->addField('addresses', 'phone_office', 'addresses_phone_office')
            ->addField('addresses', 'phone_home', 'addresses_phone_home')
            ->addField('states', 'abbreviation', 'state_id_states_abbreviation')
            ->addField('states', 'name', 'state_id_states_name');
        return $dataSource;
    }

    /**
     * Handles the request data.
     * @return int The number of events triggered by the view.
     */
    public function handleRequest()
    {
        $dataSource = self::getDataSource();
        $handledEvents = 0;
        if (Controller::isPostBack($this->model) === false)
        {
            $this->model->dataBind($dataSource);
        }
        else
        {
            $handledEvents = Controller::handleEvents($this->model);
            $this->model->dataBind($dataSource);
            if ($this->view->getIsAsynchronous())
            {
                $this->view->render();
                HttpResponse::end();
            }
        }

        return $handledEvents;
    }

    /**
     * Handles the detailsCommandColumn_Click event.
     * @param Grid $sender
     * @param ControllerEvent $event
     */
    public static function detailsCommandColumn_Clicked(&$sender, &$event)
    {
        // @todo Add custom code below. (Typycally, Controller::transfer logic)
        $requestUrl = HttpContextInfo::getInstance()->getRequestScriptPath();
        if (StringHelper::endsWith($requestUrl, '.index.php'))
        {
            $targetUrl = StringHelper::replaceEnd($requestUrl, '.index.php', '.details.php');
            Controller::transfer($targetUrl . '?id=' . urlencode($event->getValue()));
        }

        $sender->setMessage('Details command for item ' . $event->getValue());
    }

}

$widget = new AddressesGridWidget();
$widget->handleRequest();

HttpResponse::write(MarkupWriter::DTD_XHTML_STRICT . "\r\n");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>WebCore Testbed</title>
        <?php HtmlViewManager::render(); ?>
    </head>
    <body>
        <pre>
        <?php
            $widget->render();
        ?>
        </pre>
    </body>
</html>