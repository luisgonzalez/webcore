<?php
/**
 * @category Forms: A Tabbed Form
 * @tutorial Learn how the tab containers work.
 * @author Mario Di Vece <mario@unosquare.com>
 */

class TestFormWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $this->createModels();
        $this->registerEventHandlers();
        $this->view = new HtmlFormView($this->model);
        $this->view->setIsAsynchronous(true);
        $this->view->setFrameWidth('800px');
    }
    
    protected function createModels()
    {
        $this->model = new Form('fieldTests', 'Control Model-to-View: Standard Tests');
        
        $this->model->addContainer(new TabContainer('tabby', ''));
        
        // Add the textfields
        $textFields = new TabPage('textFields', 'Text Fields');
        $textFields->setIsSideBySide(true);
        $textFields->addField(new LabelField('labelfield', 'Simple LabelField', 'Label Text', "LabelField, no input data"));
        $textFields->addField(new TextField('textfield', 'Simple TextField', '', true, "Simple TextField, basic validator"));
        $textFields->addField(new EmailField('emailfield', 'Email Field', '', true, "Email field, email validator"));
        $textFields->addField(new PasswordField('passwordfield', 'Password Field', '', true, 'Password Field, basic validator'));
        $textFields->addField(new PhoneNumberField('phonenumberfield', 'Phone Field', '', true, 'Phone Field, phone validator'));
        $textFields->addField(new MoneyField('moneyfield', 'Money Field', '', true, 'Money Field, numeric validator'));
        $textFields->addField(new DateField('datefield', 'Date Field', '', true, 'Date Field, date selector'));
        $textFields->addField(new DateTimeField('datetimefield', 'DateTime Field', '', true, 'DateTime Field, date time selector'));
        $textFields->addField(new IntegerField('integerfield', 'Integer Field', '', false, 'Integer Field, numeric validator'));
        $textFields->addField(new DecimalField('decimalfield', 'Decimal Field', '', false, 'Decimal Field, numeric validator'));
        $this->model->getContainer('tabby')->addContainer($textFields);
        
        // Add the text areas
        $textAreas = new TabPage('textAreas', 'Text Areas');
        $textAreas->addField(new TextArea('textarea', 'Text Area', '', true, 'Text Area, basic field validator'));
        $rtfs = new FormSection('rftSec', 'Also Rich Text Areas');
        $rtfs->setIsSideBySide(true);
        $rtfs->addField(new RichTextArea('rta', 'Rich Text Area!', '', true, 'Enter something about yourself.'));
        $rtfs->addField(new RichTextArea('rta2', 'Rich Text Area 2!', '', true, 'Enter something about yourself.'));
        $textAreas->addContainer($rtfs);
        $this->model->getContainer('tabby')->addContainer($textAreas);
        
        // Add the comboboxes
        $comboBoxes = new TabPage('comboBoxes', 'Combo Boxes');
        $comboBoxes->getChildren()->addControl(new MonthYearComboBox('monthyearcombo', 'Month/Year Combo', '', true, 'Month/Year Combo, two comboboxes'));
        $comboBoxes->addField(new ComboBox('combo1', 'Empty Combo', '', '', '', 'Simple combobox, no options'));
        $comboBoxes->addField(new ComboBox('combo2', 'Simple Combo', '', '', '', 'Simple combobox, 2 options'));
        $comboBoxes->addField(new ComboBox('combo3', 'Combo Categories', '', '', '', 'Simple combobox, 2 categories, 2 options each'));
        $comboBoxes->addField(new ComboBox('combo4', 'Cascading Combo', '', 'combo4_Change', '1', 'Simple combobox, 2 options, event firing'));
        $comboBoxes->addField(new ComboBox('combo5', 'Cascading Combo Target', '', '', '', 'Simple combobox, 2 options, cascading target'));
        // Setup the combobox Options
        $optsCombo2[] = array(
            'value' => '1',
            'display' => 'Fruits',
            'category' => ''
        );
        $optsCombo2[] = array(
            'value' => '2',
            'display' => 'Vegetables',
            'category' => ''
        );
        $comboBoxes->getField('combo2')->addOptions($optsCombo2);
        $optsCombo3[] = array(
            'value' => '1',
            'display' => 'Apples',
            'category' => 'Fruits'
        );
        $optsCombo3[] = array(
            'value' => '2',
            'display' => 'Bannanas',
            'category' => 'Fruits'
        );
        $optsCombo3[] = array(
            'value' => '3',
            'display' => 'Tomaoes',
            'category' => 'Vegetables'
        );
        $optsCombo3[] = array(
            'value' => '4',
            'display' => 'Carrots',
            'category' => 'Vegetables'
        );
        $comboBoxes->getField('combo3')->addOptions($optsCombo3);
        $comboBoxes->getField('combo4')->addOption('', '-- Select --');
        $comboBoxes->getField('combo4')->addOptions($optsCombo2);
        $comboBoxes->getField('combo5')->setIsReadOnly(true);
        $this->model->getContainer('tabby')->addContainer($comboBoxes);
        
        $compoundFields = new TabPage('compoundFields', 'Other Fields');
        $compoundFields->getChildren()->addControl(new CompoundListField('compoundList', 'Compound List', null, 'A control to add several items to a collection'));
        $compoundFields->getChildren()->addControl(new CheckBox('isenabled', 'CheckBox'));
        
        $this->model->getContainer('tabby')->addContainer($compoundFields);
        
        // Buttons with events, and persistor
        $this->model->getChildren()->addControl(new Persistor('id', '5438'));
        $this->model->addButton(new Button('buttonSubmit', 'OK', 'buttonSubmit_Click'));
        $this->model->addButton(new Button('buttonCancel', 'Cancel', 'buttonCancel_Click'));
        
    }
    
    protected function registerEventHandlers()
    {
        Controller::registerEventHandler('buttonSubmit_Click', array(
            __CLASS__,
            'on_buttonSubmit_Clicked'
        ));
        Controller::registerEventHandler('buttonCancel_Click', array(
            __CLASS__,
            'on_buttonCancel_Clicked'
        ));
        Controller::registerEventHandler('combo4_Change', array(
            __CLASS__,
            'on_combo4_Changed'
        ));
    }
    
    /**
     * Handles the Submit event of the Button
     *
     * @param Form $sender
     * @param ControllerEvent $eventArgs
     */
    public static function on_buttonSubmit_Clicked(&$sender, &$eventArgs)
    {
        self::populateCascadingComboBox($sender);
        $request = HttpContext::getRequest()->getRequestVars();
        $sender->dataBind($request);
        $isValid = $sender->validate();
    }
    
    /**
     * Handles the Cancel event of the Button
     *
     * @param IModel $sender
     * @param ControllerEvent $event
     */
    public static function on_buttonCancel_Clicked(&$sender, &$eventArgs)
    {
    }
    
    /**
     * Handles the change event of the combobox
     *
     * @param IModel $sender
     * @param ControllerEvent $event
     */
    public static function on_combo4_Changed(&$sender, &$eventArgs)
    {
        self::populateCascadingComboBox($sender);
        $request = HttpContext::getRequest()->getRequestVars();
        $sender->dataBind($request);
    }
    
    private static function populateCascadingComboBox($form)
    {
        $request = HttpContext::getRequest()->getRequestVars();
        $form->getField('combo5')->setIsReadOnly(false);
        if ($request->keyExists('combo4') && $request->getValue('combo4') == 1)
        {
            $form->getField('combo5')->addOption('', '-- Select --');
            $form->getField('combo5')->addOption('1', 'Apples');
            $form->getField('combo5')->addOption('2', 'Bannanas');
        }
        elseif ($request->keyExists('combo4') && $request->getValue('combo4') == 2)
        {
            $form->getField('combo5')->addOption('', '-- Select --');
            $form->getField('combo5')->addOption('3', 'Tomatoes');
            $form->getField('combo5')->addOption('4', 'Carrots');
        }
        else
        {
            $form->getField('combo5')->setIsReadOnly(true);
            $form->getField('combo5')->setValue('');
        }
    }
    
    public function &getView()
    {
        return $this->view;
    }
    
    public static function createInstance()
    {
        return new TestFormWidget();
    }
    
}
$sample = new TestFormWidget();
$sample->handleRequest();
?>