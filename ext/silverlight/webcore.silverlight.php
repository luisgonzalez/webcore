<?php
/**
 *
 * @package    WebCore
 * @subpackage Silverlight
 */
class SilverlightView extends ObjectBase implements IRenderable
{
    /**
     * @var IModel
     */
    protected $model;
    
    public function __construct(&$model)
    {
        if (is_null($model))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model');
        
        if (ObjectIntrospector::isImplementing($model, 'IRootModel') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model must implement IRootModel');
        
        $this->model           = $model;
        $this->renderCallbacks = new KeyedCollection();
        
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        $callbacks['Form']      = array(
            'SilverlighRenderCallbacks',
            'renderForm'
        );
        $callbacks['TextField'] = array(
            'SilverlighRenderCallbacks',
            'renderTextField'
        );
        $callbacks['Button']    = array(
            'SilverlighRenderCallbacks',
            'renderButton'
        );
        
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     *
     */
    protected function registerDependencies()
    {
        $mootoolsPath = HttpContext::getLibraryRoot() . 'js/std.mootools122.js';
        if (intval(LogManager::getLogLevel()) >= intval(LogManager::LOG_LEVEL_DEBUG))
        {
            $mootoolsPath = HttpContext::getLibraryRoot() . 'js/std.mootools122-dev.js';
        }
        $formviewPath = HttpContext::getLibraryRoot() . 'ext/silverlight/std.silverlightview.js';
        $cssPath      = HttpContext::getLibraryRoot() . 'ext/silverlight/std.silverlightview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'mootools12', $mootoolsPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'SilverlightView.Js', $formviewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'SilverlightView.Css', $cssPath);
    }
    
    /**
     * Renders the form and each control within it.
     *
     */
    public function render()
    {
        $class = $this->model->getType()->getName();
        
        call_user_func_array($this->renderCallbacks->getValue($class), array(
            &$this->model,
            &$this
        ));
    }
    
    /**
     * Returns the keyed collection containing the callback methods used to render each model type.
     * The keys are the class names of the model types. The values are the callbacks themselves.
     *
     * @return KeyedCollection
     */
    public function &getRenderCallbacks()
    {
        return $this->renderCallbacks;
    }
    
    /**
     * Gets the model that this instance renders
     *
     * @return IRootModel
     */
    public function &getModel()
    {
        return $this->model;
    }
}

/**
 *
 * @package    WebCore
 * @subpackage Silverlight
 */
class SilverlighRenderCallbacks extends HelperBase
{
    /**
     * Helper function to render a button -- DO NOT REGISTER AS A CALLBACK!
     *
     * @param Model $model
     * @param SilverlightView $view
     * @param string $buttonName
     */
    protected static function renderFormButton(&$model, &$view, $buttonName)
    {
        $currentControl = $model->getChildren()->getControl($buttonName, true);
        
        if ($currentControl->getVisible() === false)
            return;
        
        $controlClassName = $currentControl->getType()->getName();
        
        if ($view->getRenderCallbacks()->keyExists($controlClassName))
        {
            $renderCallback = $view->getRenderCallbacks()->getValue($controlClassName);
            
            if (is_callable($renderCallback, false))
            {
                call_user_func_array($renderCallback, array(
                    &$currentControl,
                    &$view
                ));
                return;
            }
        }
        
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Invalid render callback for model of type '" . $controlClassName . "'");
    }
    
    /**
     * Renders the main form
     *
     * @param Form $model
     * @param SilverlightView $view
     */
    public static function renderForm(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute("id", $model->getName());
        
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw('embedSilverlight("' . $model->getName() . '", "slContext");');
        $tw->closeScript(true);
        
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute("id", $model->getName() . "_xaml");
        $tw->writeContent('', false, false, false);
        $tw->writeRaw("<!--[CDATA[ ");
        
        $tw->openTag("Grid");
        $tw->addAttribute("xmlns", "http://schemas.microsoft.com/winfx/2006/xaml/presentation");
        $tw->addAttribute("xmlns:x", "http://schemas.microsoft.com/winfx/2006/xaml");
        $tw->addAttribute("Width", "500");
        
        $tw->openTag("Grid.RowDefinitions");
        $tw->openTag("RowDefinition");
        $tw->addAttribute('Height', '20');
        $tw->closeTag();
        $tw->openTag("RowDefinition");
        $tw->addAttribute('Height', '*');
        $tw->closeTag();
        $tw->openTag("RowDefinition");
        $tw->addAttribute('Height', '20');
        $tw->closeTag();
        $tw->closeTag();
        
        $tw->openTag("TextBlock");
        $tw->addAttribute("Grid.Row", "0");
        $tw->writeRaw($model->getCaption());
        $tw->closeTag();
        
        $tw->openTag("StackPanel");
        $tw->addAttribute("Grid.Row", "1");
        
        foreach ($model->getChildren() as $currentControl)
        {
            if ($currentControl->getVisible() === false)
                continue;
            
            if (ObjectIntrospector::isA($currentControl, 'ButtonModelBase'))
                continue;
            
            $controlClassName = $currentControl->getType()->getName();
            
            if ($view->getRenderCallbacks()->keyExists($controlClassName))
            {
                $renderCallback = $view->getRenderCallbacks()->getValue($controlClassName);
                
                if (is_callable($renderCallback, false))
                {
                    call_user_func_array($renderCallback, array(
                        &$currentControl,
                        &$view
                    ));
                    continue;
                }
            }
            
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Invalid render callback for model of type '" . $controlClassName . "'");
        }
        
        $tw->closeTag();
        
        $tw->openTag("StackPanel");
        $tw->addAttribute("Orientation", "Horizontal");
        $tw->addAttribute("Background", "Silver");
        $tw->addAttribute("Grid.Row", "2");
        
        $buttonControls = $model->getChildren()->getControlNames(false, 'ButtonModelBase');
        
        foreach ($buttonControls as $buttonName)
            self::renderFormButton($model, $view, $buttonName);
        
        $tw->closeTag();
        
        $tw->closeTag();
        
        $tw->writeRaw(" ]]-->");
        
        $tw->closeDiv();
    }
    
    /**
     * Renders the main form
     *
     * @param TextField $model
     * @param SilverlightView $view
     */
    public static function renderTextField(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTag("Grid");
        $tw->addAttribute('Margin', '4, 4, 4, 4');
        
        $tw->openTag("Grid.ColumnDefinitions");
        $tw->openTag("ColumnDefinition");
        $tw->addAttribute('Width', '100');
        $tw->closeTag();
        $tw->openTag("ColumnDefinition");
        $tw->addAttribute('Width', '*');
        $tw->closeTag();
        $tw->openTag("ColumnDefinition");
        $tw->addAttribute('Width', '50');
        $tw->closeTag();
        $tw->closeTag();
        
        $tw->openTag("TextBlock");
        $tw->addAttribute("Grid.Column", "0");
        $tw->writeRaw($model->getCaption());
        $tw->closeTag();
        
        $tw->openTag('TextBox');
        $tw->addAttribute("Grid.Column", "1");
        $tw->addAttribute("Width", "200");
        $tw->addAttribute("Text", $model->getValue());
        $tw->closeTag();
        
        $tw->openTag("TextBlock");
        $tw->addAttribute("Grid.Column", "2");
        $tw->writeRaw("*");
        $tw->closeTag();
        
        $tw->closeTag();
    }
    
    /**
     * Renders the main form
     *
     * @param Button $model
     * @param SilverlightView $view
     */
    public static function renderButton(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTag('Rectangle');
        $tw->addAttribute("Stroke", "White");
        $tw->addAttribute("StrokeThickness", "1");
        $tw->closeTag();
        
        $tw->openTag("TextBlock");
        $tw->writeRaw($model->getCaption());
        $tw->closeTag();
    }
}
?>