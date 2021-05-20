<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides models of controls in tree control
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

/**
 * Base class for tree node control
 *
 * @todo Doc this class
 * @package WebCore
 * @subpackage Model
 */
abstract class TreeNodeControlModelBase extends ControlModelBase implements IEventTrigger
{
    /**
     * @var IndexedCollection
     */
    protected $nodes;
    protected $caption;
    protected $tag;
    protected $eventName;
    protected $eventValue;
    protected $isExpanded;
    
    const EVENTVALUE_SELECT = 's';
    const EVENTVALUE_EXPAND = 'e';
    const EVENTVALUE_COLLAPSE = 'c';
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, true);
        
        $this->nodes      = new IndexedCollection();
        $this->caption    = $name;
        $this->tag        = $name;
        $this->eventName  = '';
        $this->isExpanded = false;
    }
    
    public function getEventName()
    {
        return $this->eventName;
    }
    
    public function setEventName($value)
    {
        $this->eventName = $value;
    }
    
    public function getEventValue()
    {
        return $this->eventValue;
    }
    
    public function setEventValue($value)
    {
        $this->eventValue = $value;
    }
    
    public function getNodes()
    {
        return $this->nodes;
    }
    
    public function getTag()
    {
        return $this->tag;
    }
    
    public function setTag($value)
    {
        $this->tag = $value;
    }
    
    public function getCaption()
    {
        return $this->caption;
    }
    
    public function setCaption($value)
    {
        $this->caption = $value;
    }
    
    public function getIsExpanded()
    {
        return $this->isExpanded;
    }
    
    public function setIsExpanded($value)
    {
        $this->isExpanded = $value;
    }
}

/**
 * Represents a common tree node control
 *
 * @package WebCore
 * @subpackage Model
 */
class TreeNodeControlModel extends TreeNodeControlModelBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param string $eventName
     */
    public function __construct($name, $caption, $eventName = '')
    {
        parent::__construct($name);
        $this->eventName = $eventName;
        $this->caption   = $caption;
    }
    
    /**
     * This is a shorcut.
     *
     * @param string $caption
     * @param string $tag
     */
    public function addNode($caption, $tag = '')
    {
        $nodeName = '_' . $this->name . '_node' . $this->nodes->getCount();
        $node     = new TreeNodeControlModel($nodeName, $caption, $this->eventName);
        $node->setParent($this);
        $node->setTag($tag);
        $this->nodes->addItem($node);
        return $node;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TreeNodeControlModel
     */
    public static function createInstance()
    {
        return new TreeNodeControlModel('ISerializable', 'ISerializable');
    }
}

/**
 * Boundable tree node control
 *
 * @package WebCore
 * @subpackage Model
 */
class BoundTreeNodeControlModel extends TreeNodeControlModel implements IBindingTarget
{
    protected $captionBindingMemberName;
    protected $idBindingMemberName;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param string $nodesCaptionBindingMemberName
     * @param string $nodesIdBindingMemberName
     */
    public function __construct($name, $caption, $nodesCaptionBindingMemberName = 'display', $nodesIdBindingMemberName = 'value')
    {
        parent::__construct($name, $caption);
        
        $this->captionBindingMemberName = $nodesCaptionBindingMemberName;
        $this->idBindingMemberName      = $nodesIdBindingMemberName;
    }
    
    /**
     * Get caption binding member name
     *
     * @return string
     */
    public function getCaptionBindingMemberName()
    {
        return $this->captionBindingMemberName;
    }
    
    /**
     * Set caption binding member name
     *
     * @param string $value
     */
    public function setCaptionBindingMemberName($value)
    {
        $this->captionBindingMemberName = $value;
    }
    
    /**
     * Get id binding member name
     *
     * @return string
     */
    public function getIdBindingMemberName()
    {
        return $this->idBindingMemberName;
    }
    
    /**
     * Set id binding member name
     *
     * @param string $value
     */
    public function setIdBindingMemberName($value)
    {
        $this->idBindingMemberName = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return BoundTreeNodeControlModel
     */
    public static function createInstance()
    {
        return new BoundTreeNodeControlModel('ISerializable', 'ISerializable', 'ISerializable');
    }
    
    /**
     * Databinds tree node
     * 
     * @param IndexedCollection $dataSource
     */
    public function dataBind(&$dataSource)
    {
        $captionMember = $this->captionBindingMemberName;
        $idMember      = $this->idBindingMemberName;
        
        foreach ($dataSource as $item)
        {
            $captionValue = '';
            $idValue      = '';
            $pidValue     = '';
            
            foreach ($item as $key => $value)
            {
                if ($captionMember != '' && $captionMember === $key)
                    $captionValue = $value;
                
                if ($idMember != '' && $idMember === $key)
                    $idValue = $value;
            }
            
            $this->addNode($captionValue, $idValue);
        }
    }
}

/**
 * Represents a root tree control
 *
 * @package WebCore
 * @subpackage Model
 */
class TreeControlModel extends BoundTreeNodeControlModel implements IRootModel, IStatefulModel
{
    /**
     * @var SimpleControlState
     */
    protected $state;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
        $this->setEventName('selectNode');
        $this->state = new SimpleControlState();
        
        // Automatically deserialize the gridstate if available
        // @todo Models should use the controller
        $request = HttpContext::getRequest()->getRequestVars();
        if ($request->keyExists($this->getStateName()))
        {
            $this->state = SimpleControlState::fromBase64($request->getValue($this->getStateName()));
        }
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
     * @return SimpleControlState
     */
    public function &getState()
    {
        return $this->state;
    }
}
?>