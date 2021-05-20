<?php
/**
 * WebCore 3.0 Samples Browser
 * @version 1.0.0
 * @author Mario Di Vece <mario@unosquare.com>
 */
require_once 'initialize.inc.php';
HttpContext::getPermissionSet()->setDefaultResolve(Permission::PERMISSION_ALLOW);
HttpContext::applySecurity();

class TreeSampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $treeName = __CLASS__ . '_tree_';
        
        $tree = new TreeControlModel($treeName);
        $tree->setCaption('Tree Control');
        $tree->setEventName('nodeEvent');
        $tree->addNode('Node 0', 'tag 0');
        $tree->addNode('Node 1', 'tag 1');
        $tree->getNodes()->getLastItem()->addNode('Node 1.1', 'tag 1.1');
        $tree->addNode('Node 2', 'tag 2');
        $tree->addNode('Node 3', 'tag 3');
        $tree->getNodes()->getLastItem()->addNode('Node 3.1', 'tag 3.1');
        $tree->addNode('Node 4', 'tag 4');
        
        Controller::registerEventHandler('nodeEvent', array(
            __CLASS__,
            'OnNodeEvent'
        ));
        
        $this->model = $tree;
        $this->view  = new HtmlTreeControlView($tree);
    }
    
    /**
     * @param TreeControlModel $sender
     * @param ControllerEvent $e
     */
    public static function OnNodeEvent(&$sender, &$e)
    {
        $eventType = $e->getValue();
        switch ($eventType)
        {
            case TreeNodeControlModelBase::EVENTVALUE_COLLAPSE:
                break;
            case TreeNodeControlModelBase::EVENTVALUE_EXPAND:
                break;
            case TreeNodeControlModelBase::EVENTVALUE_SELECT:
                break;
        }
    }
    
    public static function createInstance()
    {
        return new TreeSampleWidget();
    }
}


$page = new WebPage(SAMPLES_PATH . 'page.tpl.php', 'WebCore 3.0 Samples Browser');

$treeWidget = new TreeSampleWidget();
$page->addContent('left', $treeWidget);
$page->setUseOutputBuffering(true);
$page->render();
?>