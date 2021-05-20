<?php
class FormsPortlet extends PortletBase
{
    public function __construct($name)
    {
        parent::__construct($name);
        
        $context         = DataContext::getInstance();
        /**
         * @var MySqlMetadataHelper
         */
        $metadataHelper  = $context->getMetadataHelper();
        $comboDataSource = array();
        
        foreach ($metadataHelper->getTables() as $table)
        {
            $comboDataSource[] = array(
                "display" => $table->getValue('tableName'),
                "value" => $table->getValue('tableName')
            );
        }
        
        $message = "Using DataContext " . $context->getConnection()->schema . "@" . $context->getConnection()->host . ".";
        
        $this->model = new Form('forms', 'Forms Generator');
        $this->model->getChildren()->addControl(new TextBlock("info", $message));
        $comboForms = new ComboBox('table', 'Table');
        $comboForms->addOptions($comboDataSource);
        $this->model->getChildren()->addControl($comboForms);
        
        $this->model->addField(new TextField('formName', 'Form PHP Object', 'form'));
        
        $comboView = new ComboBox('view', 'View');
        $comboView->addOption('None', 'None');
        $comboView->addOption('HtmlFormView', 'HTML');
        $comboView->addOption('ExtJsFormView', 'ExtJS');
        $comboView->addOption('SilverlightView', 'Silverlight');
        
        $this->model->getChildren()->addControl($comboView);
        
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
        
        $viewName   = $request->getValue('view');
        $entityName = $request->getValue('table');
        $formName   = $request->getValue('formName');
        $entity     = DataContext::getInstance()->getAdapter($entityName)->defaultEntity();
        
        $formCode = Scaffolder::generateFormModelFromEntity($entity, $formName);
        
        if ($viewName != 'None')
            $formCode .= Scaffolder::generateView($viewName, $formName);
        
        highlight_string('<?php ' . "\r\n" . $formCode . '?>');
        exit();
    }
    
    public static function createInstance()
    {
        return new FormsPortlet('Instance');
    }
}