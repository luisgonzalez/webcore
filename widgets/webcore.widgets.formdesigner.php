<?php

/**
 * Form Designer using the FormWidgetScaffolder as a metadata and code generator
 * @package WebCore
 * @subpackage Widgets
 * @author Mario Di Vece <mario@unosquare.com>
 */
class FormDesignerWidget extends WidgetBase
{
    public static $Javascript;
    
    // Session Keys
    const SK_SCAFFOLDER = 'FormDesignerWidget.Scaffolder';
    
    // tab page names
    const TAB_OPTIONS = 'options';
    const TAB_DESINGER = 'designer';
    const TAB_CODE = 'code';
    const TAB_PREVIEW = 'preview';
    
    /**
     * @var FormWidgetScaffolder
     */
    protected $scaffolder;
    /**
     * @var Form
     */
    protected $formModel;
    /**
     * @var EditableDataRepeater
     */
    protected $gridModel;
    
    protected $gridView;
    protected $formView;
    protected $codeView;
    protected $previewView;
    
    public static function createInstance()
    {
        return new FormDesignerWidget();
    }
    
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $this->persistScaffolder();
        $this->createFormModel();
        $this->createGridModel();
        $this->createViews();
        
        $this->model = null;
    }
    
    private function persistScaffolder()
    {
        $this->scaffolder = new FormWidgetScaffolder();
        HttpContext::getSession()->registerPersistentObject(self::SK_SCAFFOLDER, $this->scaffolder);
    }
    
    private function createFormModel()
    {
        $dataContext    = DataContext::getInstance();
        $metadataHelper = $dataContext->getMetadataHelper();
        $tables         = $metadataHelper->getTables();
        $combo          = new ComboBox('tableCombo', 'Table', '', 'tableComboChanged', '', 'Select a database table to generate the widget from');
        
        foreach ($tables as $table)
        {
            if ($table->getValue('isView') !== 'false')
                continue;
            $combo->addOption($table->getValue('tableName'), $table->getValue('tableName'), $table->getValue('schema'));
        }
        
        $form = new Form('tableSelector', 'To get started, select a table, fill out the options and click on the \'Generate\' button.');
        $form->addField($combo);
        $form->addField(new TextField('authorName', 'Author Name', 'WebCore Scaffolder'));
        $form->addField(new TextField('authorEmail', 'Author Email', 'webcore@' . HttpContext::getInfo()->getServerAddress()));
        $form->addField(new TextField('packageName', 'Package', 'Application', true, 'Determines the documented package name for the widget'));
        $form->addField(new TextField('subpackageName', 'Subpackage', 'Forms', true, 'Determines the documented subpackage name for the widget'));
        $form->addField(new CheckBox('genAccessors', 'Control Accessors', '0', '1', '0', 'Determines whether the scaffolder should generate public accessor methods for each of the controls'));
        
        $form->addButton(new Button('generateButton', 'Generate', 'generateClick'));
        
        Controller::registerEventHandler('generateClick', array(
            __CLASS__,
            'onGenerateClick'
        ));
        $this->formModel = $form;
    }
    
    private function createGridModel()
    {
        $combo = new ComboBoxRepeaterField('ModelType', 'Control');
        
        $combo->addOption(FormWidgetScaffolder::CTL_AUTO_CREATED_DT);
        $combo->addOption(FormWidgetScaffolder::CTL_AUTO_CREATED_USR);
        $combo->addOption(FormWidgetScaffolder::CTL_AUTO_UPDATED_DT);
        $combo->addOption(FormWidgetScaffolder::CTL_AUTO_UPDATED_USR);
        $combo->addOption(FormWidgetScaffolder::CTL_AUTO_FILESIZE);
        $combo->addOption(FormWidgetScaffolder::CTL_AUTO_FILEMIME);
        $combo->addOption(FormWidgetScaffolder::CTL_AUTO_FILENAME);
        $combo->addOption(FormWidgetScaffolder::CTL_CHECKBOX);
        $combo->addOption(FormWidgetScaffolder::CTL_COMBOBOX);
        $combo->addOption(FormWidgetScaffolder::CTL_DATEFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_DATETIMEFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_DECIMALFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_EMAILFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_FILEFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_INTEGERFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_MONEYFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_PASSWORDFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_PERSISTOR);
        $combo->addOption(FormWidgetScaffolder::CTL_TEXTAREA);
        $combo->addOption(FormWidgetScaffolder::CTL_TEXTFIELD);
        $combo->addOption(FormWidgetScaffolder::CTL_TIMEFIELD);
        
        $repeater = new EditableDataRepeater('fieldEditor', 'Customize each field by clicking on the edit button for each row. Click on the \'Preview\' tab to preview the widget.', 'EntityFieldName', false, false);
        
        $repeater->addRepeaterField(new IntegerRepeaterField('DisplayOrder', 'Order'));
        $repeater->addRepeaterField(new CheckBoxRepeaterField('Generate', 'En', '', 'true', 'false'));
        $repeater->addRepeaterField(new LabelRepeaterField('EntityFieldName', 'Field'));
        $repeater->addRepeaterField($combo);
        $repeater->addRepeaterField(new TextBoxRepeaterField('Caption', 'Caption'));
        $repeater->addRepeaterField(new CheckBoxRepeaterField('IsRequired', 'Required', '', 'true', 'false'));
        $repeater->addRepeaterField(new TextBoxRepeaterField('HelpString', 'Help'));
        $repeater->getRepeaterField('HelpString')->setIsRequired(false);
        
        Controller::registerEventHandler(EditableDataRepeater::EVENTNAME_EDIT_ITEM, array(
            __CLASS__,
            'onEditItem'
        ));
        Controller::registerEventHandler(EditableDataRepeater::EVENTNAME_SAVE_ITEM, array(
            __CLASS__,
            'onSaveItem'
        ));
        
        $this->gridModel = $repeater;
    }
    
    private function createViews()
    {
        $compositeView = new HtmlTabView('formdesigner');
        $compositeView->addTabPage('options', '1. Generate');
        $compositeView->addTabPage('designer', '2. Customize');
        $compositeView->addTabPage('preview', '3. Preview');
        $compositeView->addTabPage('code', '4. Code');
        
        $this->formView = new HtmlFormView($this->formModel);
        $this->formView->setIsAsynchronous(true);
        $this->formView->setFrameWidth('auto');
        $this->formView->setShowFrame(false);
        
        $this->gridView = new HtmlRepeaterView($this->gridModel);
        $this->gridView->setIsAsynchronous(true);
        $this->gridView->setFrameWidth('auto');
        $this->gridView->setShowFrame(false);
        
        $this->codeView = new HtmlViewCollection();
        $this->codeView->getRootTagAttributes()->setValue('id', 'generatedCode');
        
        $codeMessage = new HtmlTagView();
        $codeMessage->addAttribute('class', 'view-caption');
        $codeMessage->setContent('Generate a form widget by clicking on the \'Generate\' tab.');
        $this->codeView->addItem($codeMessage);
        
        $this->previewView = new HtmlViewCollection();
        $this->previewView->getRootTagAttributes()->setValue('id', 'preview_container');
        
        $directions = new HtmlTagView();
        $directions->addAttribute('class', 'view-caption');
        $directions->setContent('Generate a form widget by clicking on the \'Generate\' tab.');
        $this->previewView->addItem($directions);
        
        $compositeView->getTabPage(self::TAB_OPTIONS)->addItem($this->formView);
        $compositeView->getTabPage(self::TAB_DESINGER)->addItem($this->gridView);
        $compositeView->getTabPage(self::TAB_CODE)->addItem($this->codeView);
        $compositeView->getTabPage(self::TAB_PREVIEW)->addItem($this->previewView);
        
        $this->view = $compositeView;
    }
    
    /**
     *  Handles the Generate click event for the Quick Form Designer
     *  @param Form $model
     *  @param ControllerEvent $event
     */
    public static function onGenerateClick(&$model, &$event)
    {
        $request = HttpRequest::getInstance()->getRequestVars();
        $model->dataBind($request);
        
        if ($model->validate())
        {
            $dataContext = DataContext::getInstance();
            /**
             * @var FormWidgetScaffolder
             */
            $scaffolder  = HttpContext::getSession()->getItem(self::SK_SCAFFOLDER);
            $scaffolder->setOptAuthorEmail($request->getValue('authorEmail'));
            $scaffolder->setOptAuthorName($request->getValue('authorName'));
            $scaffolder->setOptPackageName($request->getValue('packageName'));
            $scaffolder->setOptSubpackageName($request->getValue('subpackageName'));
            $scaffolder->setOptControlAccessors($request->getValue('genAccessors') === '1');
            $scaffolder->setSource($dataContext->getAdapter($request->getValue('tableCombo'))->defaultEntity());
            
            self::$Javascript = "js_formdesigner.setTabPage('designer');";
        }
        
    }
    
    /**
     * Handles the Edit event of the Field Designer
     * @param EditableDataRepeater $model
     * @param ControllerEvent $event
     */
    public static function onEditItem(&$model, &$event)
    {
        $editId = $event->getValue();
        $model->setEditKey($editId);
    }
    
    /**
     * Handles the Save event of the Field Designer
     * @param EditableDataRepeater $model
     * @param ControllerEvent $event
     */
    public static function onSaveItem(&$model, &$event)
    {
        /**
         * @var FormWidgetScaffolder
         */
        $scaffolder = HttpContext::getSession()->getItem(self::SK_SCAFFOLDER);
        $fieldsMeta = $scaffolder->getMetadata()->getItem(FormWidgetScaffolder::META_FIELDS);
        $fieldName  = $event->getValue();
        $request    = HttpRequest::getInstance()->getRequestVars();
        $model->dataBindForm($request);
        
        if ($model->validate())
        {
            for ($i = 0; $i < $fieldsMeta->getCount(); $i++)
            {
                /**
                 * @var Popo
                 */
                $target = $fieldsMeta->getItem($i);
                if ($target->EntityFieldName == $fieldName)
                {
                    $target->dataBind($request);
                    $fieldsMeta->objectSort('DisplayOrder');
                    return;
                }
            }
            
            $model->setErrorMessage("Unavailable field: '{$fieldName}'");
            return;
        }
        else
        {
            $msg = '';
            foreach ($model->getRepeaterFieldNames() as $di)
            {
                $dataItem = $model->getRepeaterField($di);
                
            }
            $model->setEditKey($fieldName);
        }
    }
    
    private function updateCode()
    {
        $code = $this->scaffolder->generateCode();
        $this->codeView->getItem(0)->setContent('This is the FormWidgetScaffolder-generated code. You can go back and change control properties as needed by clicking on the \'Customize\' tab.');
        
        $codePanel = new HtmlTagView('div', true, false, false);
        $codePanel->getAttributes()->setValue('style', 'max-height: 500px; overflow: auto; border: 1px solid #cdcdcd; background-color: #fcfcfc; padding: 4px;');
        $codePanel->setContent(highlight_string("<?php\r\n" . $code . "?>", true));
        $this->codeView->addItem($codePanel);
        
        $this->codeView->render();
        eval($code);
        $className     = $this->scaffolder->getWidgetClassName();
        $widgetPreview = new $className;
        foreach ($widgetPreview->getModel()->getButtonNames() as $buttonName)
        {
            $widgetPreview->getModel()->getButton($buttonName)->setIsEnabled(false);
        }

        $this->view->getTabPage(self::TAB_PREVIEW)->getItem(0)->getItem(0)->setContent("This is a preview for the '{$className}' widget. Click on the 'Code' tab to obtain the code.");
        $this->view->getTabPage(self::TAB_PREVIEW)->getItem(0)->addItem($widgetPreview);
        $this->view->getTabPage(self::TAB_PREVIEW)->getItem(0)->render();
    }
    
    private static function renderJavascript()
    {
        if (self::$Javascript != '')
        {
            HtmlWriter::getInstance()->renderJavascriptBlock(self::$Javascript);
        }
    }
    
    public function handleRequest()
    {
        if (Controller::isPostBack($this->formModel))
        {
            Controller::handleEvents($this->formModel);
            $request = HttpRequest::getInstance()->getRequestVars();
            $this->formModel->dataBind($request);
            $this->gridModel->dataBind($this->scaffolder->getMetadata()->getItem(FormWidgetScaffolder::META_FIELDS));
            $this->updateCode();
            $this->formView->render();
            $this->gridView->render();
            self::renderJavascript();
            HttpResponse::end();
        }
        
        if (Controller::isPostBack($this->gridModel))
        {
            Controller::handleEvents($this->gridModel);
            $this->gridModel->dataBind($this->scaffolder->getMetadata()->getItem(FormWidgetScaffolder::META_FIELDS));
            $this->updateCode();
            $this->gridView->render();
            HttpResponse::end();
        }
        
    }
    
}

?>