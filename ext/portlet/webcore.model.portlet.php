<?php
/**
 * Represents a workspace state
 * 
 * @package WebCore
 * @subpackage Workspace
 */
class WorkspaceState extends ControlStateBase
{
    /**
     * @var KeyedCollection
     */
    protected $portlets;
    protected $orientation;
    
    /**
     * Creates a new instance of this class
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->portlets    = new KeyedCollection();
        $this->orientation = Workspace::ORIENTATION_HORIZONTAL;
    }
    
    /**
     *  Adds a portlet settings to collection
     *
     *  @param string $name
     *  @param PortletSettings $settings
     */
    public function addPortlet($name, $settings)
    {
        $this->portlets->setValue($name, $settings);
    }
    
    public function setOrientation($value)
    {
        $this->orientation = $value;
    }
    
    public function getOrientation()
    {
        return $this->orientation;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return WorkspaceState
     */
    public static function createInstance()
    {
        return new WorkspaceState();
    }
    
    /**
     * Creates an instance from a base64 string
     *
     * @param string $data
     * @return WorkspaceState
     */
    public static function fromBase64($data)
    {
        return Base64Serializer::deserialize($data, get_class());
    }
}

/**
 * Represents a workspace where portlets can be placed.
 * 
 * @package WebCore
 * @subpackage Workspace
 */
class Workspace extends ContainerModelBase implements IRootModel, IStatefulModel
{
    const ORIENTATION_HORIZONTAL = 0;
    const ORIENTATION_VERTICAL = 1;
    
    protected $state;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param int $orientation
     */
    public function __construct($name, $caption, $orientation = 0)
    {
        parent::__construct($name, $caption);
        $this->state = new WorkspaceState();
        $this->state->setOrientation($orientation);
        
        // Automatically deserialize the state if available
        // HACK: Models should use the controller
        $request = HttpContext::getRequest()->getRequestVars();
        if ($request->keyExists($this->getStateName()))
        {
            $this->state = WorkspaceState::fromBase64($request->getValue($this->getStateName()));
        }
    }
    
    public function getOrientation()
    {
        return $this->state->getOrientation();
    }
    
    /**
     * Adds a portlet to workspace
     *
     * @param PortletBase $portlet
     */
    public function addPortlet($portlet)
    {
        $this->getChildren()->addControl($portlet);
        $this->state->addPortlet($portlet->getName(), $portlet->getSettings());
    }
    
    /**
     * Retrieves toolbars
     *
     * @return array
     */
    public function getToolbars()
    {
        $toolbars = array();
        
        foreach ($this->getChildren()->getTypedControlNames(true, 'Toolbar') as $toolbarName)
            $toolbars[] = $this->getChildren()->getControl($toolbarName, true);
        
        return $toolbars;
    }
    
    /**
     * Retrieves portlets
     *
     * @return array
     */
    public function getPortlets()
    {
        $portlets = array();
        
        foreach ($this->getChildren()->getTypedControlNames(true, 'PortletBase') as $portletName)
            $portlets[] = $this->getChildren()->getControl($portletName, true);
        
        return $portlets;
    }
    
    /**
     * Gets the state object of the repeater.
     * 
     * @return WorkspaceState
     */
    public function &getState()
    {
        return $this->state;
    }
    
    /**
     * Returns state name
     *
     * @return string
     */
    public function getStateName()
    {
        return Controller::PREFIX_STATE . $this->getName();
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Workspace
     */
    public static function createInstance()
    {
        return new Workspace('ISerializable', 'ISerializable');
    }
}
?>