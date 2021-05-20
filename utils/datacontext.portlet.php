<?php
class DataContextPortlet extends PortletBase
{
    public function __construct($name)
    {
        parent::__construct($name);
        
        $this->model = new Form('datacontexts', 'DataContexts Generator');
        
        $this->model->addField(new TextField('server', 'Server', ''));
        $this->model->addField(new TextField('user', 'User', ''));
        $this->model->addField(new TextField('password', 'Password', ''));
        $this->model->addField(new TextField('database', 'Database', ''));
        $this->model->addButton(new Button("generate", "Generate", "generate"));
        
        Controller::registerEventHandler('generate', array(
            __CLASS__,
            'onButton_Generate'
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
    public static function onButton_Generate(&$sender, &$eventArgs)
    {
        $request = HttpRequest::getInstance()->getRequestVars();
    }
    
    public static function createInstance()
    {
        return new DataContextPortlet('Instance');
    }
}
?>