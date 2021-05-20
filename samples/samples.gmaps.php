<?php
/**
 * @category Google: GMap Extension
 * @tutorial Handy GMaps extension for WebCore 3.x
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 */
require_once "ext/google/webcore.google.maps.php";

class GoogleMapsSampleWidget extends WidgetBase
{
    protected $mapModel;
    protected $formModel;
    protected $mapView;
    protected $formView;
    
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $compositeView = new HtmlViewCollection();
        
        
        $this->formModel = new Form('mapSearch', 'Search Address');
        $this->formModel->addField(new TextField('query', 'Query'));
        $this->formModel->addButton(new Button('onQuery', 'Search', 'onQuery'));
        $this->formView = new HtmlFormView($this->formModel);
        $this->formView->setFrameWidth('inherit');
        
        $compositeView->addItem($this->formView);
        
        $this->mapModel = new GMap('MyMap', 'Integrating With Google Maps is Extremely Easy!');
        $this->mapView  = new GMapView($this->mapModel);
        $this->mapView->setFrameWidth('inherit');
        
        $compositeView->addItem($this->mapView);
        
        $this->view = $compositeView;
    }
    
    public function handleRequest()
    {
        $request = HttpRequest::getInstance()->getRequestVars();
        
        if (Controller::isPostBack($this->mapModel))
        {
            $this->mapView->render();
            exit();
        }
        
        if (Controller::isPostBack($this->formModel))
        {
            $this->formModel->dataBind($request);
            
            if ($request->keyExists('query'))
            {
                $query       = $request->getValue('query');
                $coordinates = GGeocodeHelper::getCoordinates($query);
                if (is_null($coordinates))
                    return;
                
                $this->mapModel->setLatitude($coordinates['latitude']);
                $this->mapModel->setLongitude($coordinates['longitude']);
                $marker = new GMarker('casa', 'client', $query, $coordinates['latitude'], $coordinates['longitude']);
                $marker->setIsDraggable(true);
                $this->mapModel->addMarker($marker);
            }
        }
    }
    
    public static function createInstance()
    {
        return new GoogleMapsSampleWidget();
    }
}
$sample = new GoogleMapsSampleWidget();
$sample->handleRequest();
?>