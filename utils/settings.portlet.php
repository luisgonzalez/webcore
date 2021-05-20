<?php
class SettingsPortlet extends PortletBase
{
    public function __construct($name)
    {
        parent::__construct($name);
        
        $collection = Settings::getCollection();
        
        eval(Scaffolder::generateFormModelFromIndexedCollection($collection, 'settings'));
        
        $this->model = $form;
        
        Controller::registerEventHandler('save', array(
            __CLASS__,
            'onButton_Save'
        ));
        
        $this->view = new HtmlFormView($this->model);
        $this->view->setIsAsynchronous(true);
        
        $this->handleRequest();
    }
    
    /**
     * Handles the Submit event of the Button
     *
     * @param Form $sender
     * @param ControllerEvent $eventArgs
     */
    public static function onButton_Save(&$sender, &$eventArgs)
    {
        $sender->dataBind(HttpRequest::getInstance()->getRequestVars());
        
        if ($sender->validate())
        {
            foreach ($sender->getChildren()->getImplementingControlNames(true, 'FieldContainerModelBase') as $formSection)
            {
                var_dump($formSection);
            }
        }
    }
    
    public static function createInstance()
    {
        return new SettingsPortlet('Instance');
    }
}
?>