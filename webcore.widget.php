<?php
/**
 * @package WebCore
 * @subpackage Widget
 * @version 1.0
 * 
 * @todo document properly
 * @todo rename to workspace
 *
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @author Mario Di Vece <mario@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.view.php";

/**
 * Represents a PortletSettings to graphic representation
 * 
 * @package WebCore
 * @subpackage Widget
 */
class PortletSettings extends SerializableObjectBase
{
    const NO_DOCK = 0;
    const DOCK_NORTH = 1;
    const DOCK_SOUTH = 2;
    const DOCK_EAST = 3;
    const DOCK_WEST = 4;
    const DOCK_FILL = 5;
    
    public $X;
    public $Y;
    public $Width;
    public $Height;
    public $Dock;
    
    /**
     * Creates a new instance of this class
     *
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     */
    public function __construct($x, $y, $width, $height)
    {
        $this->X      = $x;
        $this->Y      = $y;
        $this->Width  = $width;
        $this->Height = $height;
        $this->Dock   = PortletSettings::NO_DOCK;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return PortletSettings
     */
    public static function createInstance()
    {
        return new PortletSettings(0, 0, 0, 0);
    }
}

/**
 * Widget Base Class
 *
 * @package WebCore
 * @subpackage Widget
 */
abstract class WidgetBase extends ControlModelBase implements IRenderable
{
    /**
     * @var IRenderable
     */
    protected $view;
    /**
     * @var IModel
     */
    protected $model;
    
    /**
     * Creates a new widget instance
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, true);
    }
    
    /**
     * Default handler for postback action.
     * This is only a suggested implementation of the handleRequest method.
     * You must redefine the body of this method formally.
     * 
     * @return int The number of events triggered by the view.
     */
    public function handleRequest()
    {
        $handledEvents = 0;
        if (Controller::isPostBack($this->model))
        {
            $handledEvents = Controller::handleEvents($this->model);
            
            if ($handledEvents == 0)
            {
                $request = HttpContext::getRequest()->getRequestVars();
                $this->model->dataBind($request);
            }
            
            if ($this->getView()->getIsAsynchronous() == true)
            {
                $this->render();
                HttpResponse::end();
            }
        }
        
        return $handledEvents;
    }
    
    /**
     * Renders widget
     *
     */
    public function render()
    {
        $this->getView()->render();
    }
    
    /**
     * Gets the IRenderable object for this widget
     * 
     * @return IRenderable
     */
    public function &getView()
    {
        return $this->view;
    }
    
    /**
     * Gets the IModel object for this widget
     * 
     * @return IModel
     */
    public function &getModel()
    {
        return $this->model;
    }
}

/**
 * Represents a workspace's widget.
 * 
 * @package WebCore
 * @subpackage Widget
 */
abstract class PortletBase extends WidgetBase
{
    /**
     * @var PortletSettings
     */
    protected $settings;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->settings = new PortletSettings(0, 0, 200, 200);
    }
    
    /**
     * Renders the portlet
     *
     */
    public function render()
    {
        $this->view->setShowFrame(false);
        
        parent::render();
    }
    
    /**
     * Gets title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->model->getCaption();
    }
    
    /**
     * Sets title
     *
     * @param string $value
     */
    public function setTitle($value)
    {
        $this->model->setCaption($value);
    }
    
    /**
     * Gets settings
     *
     * @return PortletSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Sets settings
     *
     * @param PortletSettings $value
     */
    public function setSettings($value)
    {
        $this->settings = $value;
    }
    
    public function getDock()
    {
        return $this->settings->Dock;
    }
    
    public function setDock($value)
    {
        $this->settings->Dock = $value;
    }
}
?>