<?php
/**
 * @category BarCodes: Code128 Barcode
 * @tutorial Provides an easy way to create Code128 barcodes.
 * @author Luis Gonzalez <luis.gonzalez@unosquare.com>
 */
require_once "ext/barcodegen/webcore.barcodegen.php";

if (HttpHandlerManager::getHasExecuted() == false)
{
    HttpHandlerManager::registerHandler(new BarCodeGeneratorHttpHandler());
}

class BarcodeWidget extends WidgetBase
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
        $this->model = new Form('barcode', 'Code128 Bar Code Generator');
        
        $this->model->addField(new TextField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BARCODE_VALUE, 'BarCode Value'));
        $this->model->addField(new IntegerField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BARCODE_HEIGHT, 'BarCode Height', '20', true, 'The min value is 20. The max value is 100'));
        $this->model->addField(new IntegerField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BAR_WEIGHT, 'BarCode Weight', '1', true, 'The min value is 1. The max value is 4'));
        $this->model->addField(new ComboBox(BarCodeGeneratorHttpHandler::REQUEST_KEY_IMG_TYPE, 'BarCode Image Type', BarCodeGenerator::IMAGE_TYPE_PNG));
        $this->model->addField(new LabelField('barcodeHtml', 'Generated BarCode Html', '', '', false));
        $this->model->addField(new LabelField('barcodeImg', 'Generated BarCode Img', '', '', false));
        
        $heightFieldValidator = $this->model->getField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BARCODE_HEIGHT)->getValidator();
        $heightFieldValidator->setMaximumValue(100);
        $heightFieldValidator->setMinimumValue(20);

        $heightFieldValidator = $this->model->getField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BAR_WEIGHT)->getValidator();
        $heightFieldValidator->setMaximumValue(4);
        $heightFieldValidator->setMinimumValue(1);
        
        $comboBox = $this->model->getField(BarCodeGeneratorHttpHandler::REQUEST_KEY_IMG_TYPE);
        $comboBox->addOption(BarCodeGenerator::IMAGE_TYPE_PNG, 'png');
        $comboBox->addOption(BarCodeGenerator::IMAGE_TYPE_JPG, 'jpg');
        $comboBox->addOption(BarCodeGenerator::IMAGE_TYPE_GIF, 'gif');
        
        // Buttons with events
        $this->model->addButton(new Button('buttonGenerate', 'Generate', 'buttonGenerate_Click'));
    }
    
    protected function registerEventHandlers()
    {
        Controller::registerEventHandler('buttonGenerate_Click', array(
            __CLASS__,
            'on_buttonGenerate_Clicked'
        ));
    }
    
    /**
     * Handles the Submit event of the Button
     *
     * @param Form $sender
     * @param ControllerEvent $eventArgs
     */
    public static function on_buttonGenerate_Clicked(&$sender, &$eventArgs)
    {
        $request = HttpContext::getRequest()->getRequestVars();
        $sender->dataBind($request);
        $isValid = $sender->validate();
        if ($isValid)
        {
            $value = $sender->getField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BARCODE_VALUE)->getValue();
            $height = $sender->getField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BARCODE_HEIGHT)->getValue();
            $weight = $sender->getField(BarCodeGeneratorHttpHandler::REQUEST_KEY_BAR_WEIGHT)->getValue();
            $type = $sender->getField(BarCodeGeneratorHttpHandler::REQUEST_KEY_IMG_TYPE)->getValue();
            
            $barcode = new BarCodeGenerator($value);
            $output = $barcode->getBarCodeAsHtml($weight, $height);
            $sender->getField('barcodeHtml')->setValue($output);
            
            $src = HttpContext::getLibraryRoot() . "ext/barcodegen/webcore.barcodegen.handler.php?" .
                    BarCodeGeneratorHttpHandler::REQUEST_KEY_BARCODE_VALUE . "=" . urlencode($value) . "&" .
                    BarCodeGeneratorHttpHandler::REQUEST_KEY_BARCODE_HEIGHT . "=" . urlencode($height) . "&" .
                    BarCodeGeneratorHttpHandler::REQUEST_KEY_BAR_WEIGHT . "=" . urlencode($weight)  . "&" .
                    BarCodeGeneratorHttpHandler::REQUEST_KEY_IMG_TYPE . "=" . urlencode($type);
                    
            $imageHtml = "<img src='" . $src ."' />";
            $sender->getField('barcodeImg')->setValue($imageHtml);
        }
    }
    
    public function &getView()
    {
        return $this->view;
    }
    
    public static function createInstance()
    {
        return new BarcodeWidget();
    }
    
    public function handleRequest()
    {

        parent::handleRequest();
    }
}

$sample = new BarcodeWidget();
$sample->handleRequest();
?>