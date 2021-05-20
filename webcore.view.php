<?php
require_once "webcore.php";
require_once "webcore.model.php";
require_once "webcore.html.php";

/**
 * Defines the methods necessary for a class to be renderable.
 *
 * @package WebCore
 * @subpackage View
 */
interface IRenderable extends IObject
{
    /**
     * Renders the associated object
     */
    public function render();
    
    /**
     * Gets the model that this instance renders
     *
     * @return IModel
     */
    public function &getModel();
}

/**
 * Defines the methods for a HTML renderable class
 *
 * @package WebCore
 * @subpackage View
 */
interface IHtmlRenderable extends IRenderable
{
    public function getShowFrame();
    public function setShowFrame($value);
}

/**
 * Defines a basic HTML render
 * 
 * @package WebCore
 * @subpackage View
 */
abstract class HtmlViewBase extends ObjectBase implements IHtmlRenderable
{
    /**
     * @var IModel
     */
    protected $model;
    /**
     * @var KeyedCollection
     */
    protected $renderCallbacks;
    protected $masterCssClass;
    protected $cssClass;
    protected $bodyHeight;
    protected $isAsynchronous;
    protected $showFrame;
    protected $frameWidth;
    
    protected function __construct(&$model)
    {
        if (is_null($model))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model');
        
        if (ObjectIntrospector::isImplementing($model, 'IRootModel') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model must implement IRootModel');
        
        $this->model           = $model;
        $this->bodyHeight      = 0;
        $this->isAsynchronous  = false;
        $this->showFrame       = true;
        $this->frameWidth      = "700px";
        $this->renderCallbacks = new KeyedCollection();
        $this->masterCssClass  = "view";
        
        $this->registerCommonCallbacks();
    }
    
    /**
     * Register common callbacks (toolbar)
     *
     */
    private function registerCommonCallbacks()
    {
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        $callbacks['Toolbar']               = array(
            'HtmlRenderCallbacks',
            'renderToolbar'
        );
        $callbacks['ToolbarButton']         = array(
            'HtmlRenderCallbacks',
            'renderToolbarButton'
        );
        $callbacks['ToolbarLabel']          = array(
            'HtmlRenderCallbacks',
            'renderToolbarLabel'
        );
        $callbacks['ToolbarSplit']          = array(
            'HtmlRenderCallbacks',
            'renderToolbarSplit'
        );
        $callbacks['ToolbarButtonMenu']     = array(
            'HtmlRenderCallbacks',
            'renderToolbarButtonMenu'
        );
        $callbacks['ToolbarButtonMenuItem'] = array(
            'HtmlRenderCallbacks',
            'renderToolbarButtonMenuItem'
        );
    }
    
    /**
     * Register common forms callbacks
     *
     */
    protected function registerCommonFormsDependecies($model)
    {
        $registerAllDependencies = true; // @todo Replace server-side injections by client-side ones.
        self::registerCommonDependecies();
        $rootPath = HttpContext::getLibraryRoot();
        
        $formviewPath = $rootPath . 'js/std.formview.js';
        $cssPath      = $rootPath . 'css/std.formview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlFormView.Js', $formviewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlFormView.Css', $cssPath);
        
        $multiselectors = $model->getChildren()->getTypedControlNames(true, 'MultiSelector');
        if (count($multiselectors) > 0)
        {
            $multiselectorPath = $rootPath . 'js/std.formview.multiselector.js';
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlFormView.MultiSelector.Js', $multiselectorPath);
        }
        
        $calendars = $model->getChildren()->getTypedControlNames(true, 'DateField');
        if (count($calendars) > 0 || $registerAllDependencies === true)
        {
            $calendarPath    = $rootPath . 'js/std.formview.calendar.js';
            $cssCalendarPath = $rootPath . 'css/std.formview.calendar.css';
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlFormView.Calendar.Js', $calendarPath);
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlFormView.Calendar.Css', $cssCalendarPath);
        }
        
        $richTextAreas = $model->getChildren()->getTypedControlNames(true, 'RichTextArea');
        if (count($richTextAreas) > 0)
        {
            $jsRtaPath  = $rootPath . 'js/std.formview.richtext.js';
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlFormView.RichText.Js', $jsRtaPath);
        }
        
        $compoundList = $model->getChildren()->getTypedControlNames(true, 'CompoundListField');
        if (count($compoundList) > 0)
        {
            $compoundListPath = $rootPath . 'js/std.formview.compoundlist.js';
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlFormView.CompoundList.Js', $compoundListPath);
        }
        
        $tabPages = $model->getChildren()->getTypedControlNames(true, 'TabPage');
        if (count($tabPages) > 0 || $registerAllDependencies === true)
        {
            $tabPagePath    = $rootPath . 'js/std.formview.tabview.js';
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlFormView.TabView.Js', $tabPagePath);
        }
    }
    
    /**
     * Renders a trigger to postback model in client
     *
     */
    public function renderPostBackTrigger()
    {
        $javascript = "window.addEvent('domready', function () {
                var htmlView_" . $this->model->getName() . " = new HtmlView('" . HtmlViewBase::getHtmlId($this->model) . "', '" . $this->cssClass . "', " . $this->isAsynchronous . ", true);
                htmlView_" . $this->model->getName() . ".refreshView();
            });";
        
        $tw = HtmlWriter::getInstance();
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw($javascript);
        $tw->closeScript(true);
    }
    
    /**
     * Determines whether the view should indent its output upon rendering.
     *
     * @return bool
     */
    public function getIndentOutput()
    {
        return $this->indentOutput;
    }
    
    /**
     * Determines whether the view should indent its output upon rendering.
     *
     * @param bool $value
     */
    public function setIndentOutput($value)
    {
        if (!is_bool($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter \'value\' must be \'bool\'');
        
        $this->indentOutput = $value;
    }
    
    /**
     * Determines if an outer, window-like container is rendered around the form.
     * Use the frameWidth properties to further customize this container.
     *
     * @return bool
     */
    public function getShowFrame()
    {
        return $this->showFrame;
    }
    
    /**
     * Determines if an outer, window-like container is rendered around the form.
     * Use the frameWidth properties to further customize this container.
     *
     * @param $value bool
     */
    public function setShowFrame($value)
    {
        if (!is_bool($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value (must be boolean)');
        $this->showFrame = $value;
    }
    
    /**
     * Determines the outer frame's width as a CSS style attribute
     * This property has no effect if showFrame is set to false.
     *
     * @return string
     */
    public function getFrameWidth()
    {
        return $this->frameWidth;
    }
    
    /**
     * Determines the outer frame's width as a CSS style attribute
     * This property has no effect if showFrame is set to false.
     *
     * @param $value string
     */
    public function setFrameWidth($value)
    {
        $this->frameWidth = $value;
    }
    
    /**
     * Renders the form and each control within it.
     *
     */
    public function render()
    {
        if (HtmlViewManager::hasRendered() == false && $this->isAsynchronous == false)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'HtmlViewManager::render() method must be called before rendering this view.');
        
        $class = get_class($this->model);
        
        HtmlWriter::getInstance()->openDiv();
        HtmlWriter::getInstance()->addAttribute('id', self::getHtmlId($this->model) . '_root');
        if ($this->getShowFrame())
        {
            HtmlWriter::getInstance()->addAttribute('class', $this->masterCssClass . '-frame');
            HtmlWriter::getInstance()->addAttribute('style', 'width: ' . $this->frameWidth . ';');
        }
        else
        {
            HtmlWriter::getInstance()->addAttribute('class', $this->masterCssClass . '-container');
        }
        
        call_user_func_array($this->renderCallbacks->getValue($class), array(
            &$this->model,
            &$this
        ));
        
        HtmlWriter::getInstance()->closeDiv(true);
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
     * Registers a new render callback for the Molder within this root model.
     *
     * @param string The class name of the model to register the callback for
     * @param callback The callback in standard PHP callback syntax to be called whn rendering the model
     *
     */
    public function addRenderCallback($key, $callback)
    {
        $this->renderCallbacks->setValue($key, $callback);
    }
    
    /**
     * Determines if the render method of this view is asynchronous
     *
     * @return bool
     */
    public function getIsAsynchronous()
    {
        return $this->isAsynchronous;
    }
    
    /**
     * Determines if the render method of this view is asynchronous
     * If set to true, no validation for HtmlViewManager::render requirement is performed.
     *
     * @param $value bool
     */
    public function setIsAsynchronous($value)
    {
        if (!is_bool($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'value must be boolean');
        
        $this->isAsynchronous = $value;
        $this->registerDependencies();
    }
    
    /**
     * Gets the Css class prefix to use for rendering.
     *
     * @return string
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }
    
    /**
     * Sets the Css class prefix to use for rendering.
     *
     * @param string $value
     */
    public function setCssClass($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->cssClass = $value;
    }
    
    /**
     * Gets the fixed height used to render the body. 0 or less means automatic height.
     *
     * @return int
     */
    public function getBodyHeight()
    {
        return $this->bodyHeight;
    }
    
    /**
     * Sets the fixed height (in pixels) used to render the body. 0 or less means automatic height.
     * @param int $value
     */
    public function setBodyHeight($value)
    {
        if (is_int($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->bodyHeight = $value;
    }
    
    /**
     * Register common dependecies
     *
     */
    protected function registerCommonDependecies()
    {
        if (intval(LogManager::getLogLevel()) >= intval(LogManager::LOG_LEVEL_DEBUG))
            $mootoolsPath = HttpContext::getLibraryRoot() . 'js/std.mootools124-dev.js';
        else
            $mootoolsPath = HttpContext::getLibraryRoot() . 'js/std.mootools124.js';
        
        $viewPath    = HttpContext::getLibraryRoot() . 'js/std.view.js';
        $viewCssPath = HttpContext::getLibraryRoot() . 'css/std.view.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'mootools12', $mootoolsPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlView.Js', $viewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlView.Css', $viewCssPath);
    }
    
    public function getMasterCssClass()
    {
        return $this->masterCssClass;
    }
    
    public function setMasterCssClass($value)
    {
        $this->masterCssClass = $value;
    }
    
    public static function getHtmlId($model)
    {
        $modelName = get_class($model);
        return strtolower($modelName) . '_' . $model->getName();
    }
    
    protected abstract function registerDependencies();
}

/**
 * Defines the common render callbacks
 *
 * @package WebCore
 * @subpackage View
 */
abstract class HtmlRenderCallbacks extends HelperBase
{
    const HREF_NO_ACTION = 'javascript:void(0);';
    
    /**
     * Helper function to render a message -- DO NOT REGISTER AS A CALLBACK!
     *
     * @param Model $model
     * @param HtmlViewBase $view
     */
    static protected function renderMessages(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        if ($model->hasErrorMessage())
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-error');
            $tw->addAttribute('style', 'display: none;');
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-erroricon');
            $tw->writeContent(Resources::getValue(Resources::SRK_FORM_ERRORTITLE));
            $tw->closeDiv();
            $tw->writeContent($model->getErrorMessage(), false, true, false);
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-message-buttonpanel');
            
            $tw->openA();
            $tw->addAttribute('href', HtmlRenderCallbacks::HREF_NO_ACTION);
            $tw->addAttribute('value', Resources::getValue(Resources::SRK_FORM_ERRORCONTINUE));
            $tw->addAttribute('class', $view->getMasterCssClass() . '-button ' . $view->getMasterCssClass() . '-closemessage');
            $tw->writeContent(Resources::getValue(Resources::SRK_FORM_ERRORCONTINUE));
            $tw->closeA();
            
            $tw->closeDiv();
            $tw->closeDiv();
        }
        elseif ($model->hasMessage())
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-message');
            $tw->addAttribute('style', 'display: none;');
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-messageicon');
            $tw->writeContent(Resources::getValue(Resources::SRK_FORM_INFOTITLE));
            $tw->closeDiv();
            $tw->writeRaw($model->getMessage());
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-message-buttonpanel');
            
            $tw->openA();
            $tw->addAttribute('href', HtmlRenderCallbacks::HREF_NO_ACTION);
            $tw->addAttribute('value', Resources::getValue(Resources::SRK_FORM_ERRORCONTINUE));
            $tw->addAttribute('redirect', $model->getRedirect());
            $tw->addAttribute('class', $view->getMasterCssClass() . '-button ' . $view->getMasterCssClass() . '-closemessage');
            $tw->writeContent(Resources::getValue(Resources::SRK_FORM_ERRORCONTINUE));
            $tw->closeA();
            
            $tw->closeDiv();
            $tw->closeDiv();
        }
    }
    
    /**
     * Renders an initialization Javascript tag
     * 
     */
    static protected function renderInitializationScript($javascript)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openScript();
        $tw->addAttribute('defer', 'defer');
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw($javascript);
        $tw->closeScript(true);
    }
    
    /**
     * Helper function to render a button -- DO NOT REGISTER AS A CALLBACK!
     *
     * @param Model $model
     * @param HtmlViewBase $view
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
     * Helper method to render child controls within a container model.  -- DO NOT REGISTER AS A CALLBACK!
     *
     * @param ContainerModelBase $model
     * @param HtmlViewBase $view
     */
    static protected function renderFieldContainerChildren(&$model, &$view)
    {
        
        $isFormSection = ObjectIntrospector::isExtending($model, 'FieldContainerModelBase');
        $isSideBySide = false;
        $isLeftItem = true;
        
        if ($isFormSection === true)
        {
            $isSideBySide = $model->getIsSideBySide();
        }
        
        // The form's child controls
        foreach ($model->getChildren() as $currentControl)
        {
            if ($currentControl->getVisible() === false)
                continue;
            if (ObjectIntrospector::isA($currentControl, 'ButtonModelBase') === true)
                continue;
            
            $controlClassName = $currentControl->getType()->getName();
            
            if ($view->getRenderCallbacks()->keyExists($controlClassName))
            {
                $renderCallback = $view->getRenderCallbacks()->getValue($controlClassName);
                
                if (is_callable($renderCallback, false))
                {
                    
                    if ($isSideBySide)
                    {
                        $tw = HtmlWriter::getInstance();
                        $tw->openDiv();
                        $tw->addAttribute('class', 'sidebyside');
                        $isLeftItem = !$isLeftItem;
                    }
                    
                    call_user_func_array($renderCallback, array(
                        &$currentControl,
                        &$view
                    ));
                    
                    if ($isSideBySide)
                    {
                        $tw->closeDiv();
                    }
                    
                    continue;
                }
            }
            
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Invalid render callback for model of type '" . $controlClassName . "'");
        }
    }
    
    /**
     * Helper method to render the commonly-used postback flag for models
     *
     * @param IRootModel $model
     * @param HtmlViewBase $view
     */
    static protected function renderPostBackFlag($model, $view)
    {
        $tw = HtmlWriter::getInstance();
        // The postback flag
        $tw->openInput();
        $tw->addAttribute('id', Controller::getPostBackFlagName($model));
        $tw->addAttribute('name', Controller::getPostBackFlagName($model));
        $tw->addAttribute('type', 'hidden');
        $tw->addAttribute('value', '1');
        $tw->closeInput(false);
    }
    
    /**
     * Helper function  that renders a Breadcrumb.
     *
     * @param Breadcrumb $model
     * @param HtmlViewBase $view
     */
    static public function renderBreadcrumb(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-breadcrumb');
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        
        $tw->openSpan();
        $tw->writeContent($model->getLabel());
        $tw->closeSpan();
        
        foreach ($model->getLinks() as $link)
        {
            $tw->openSpan();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-breadcrumb-separator');
            $tw->writeContent($model->getItemSeparator());
            $tw->closeSpan();
            
            $tw->openSpan();
            $tw->openA();
            $tw->addAttribute('href', $link->Url);
            $tw->writeContent($link->Label);
            $tw->closeA();
            $tw->closeSpan();
        }
        
        $tw->closeDiv();
    }
    
    /**
     * Helper function  that renders a toolbar.
     * NOTE: The method exists here so that all HTML views can make use of predefined field model renderers
     *
     * @param Toolbar $model
     * @param HtmlViewBase $view
     */
    static protected function renderToolbar(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar');
        $tw->addAttribute('style', 'border-bottom-style:none;');
        
        $tw->openTable();
        $tw->addAttribute('summary', '');
        $tw->openTr();
        
        foreach ($model->getChildren() as $currentControl)
        {
            if ($currentControl->getVisible() === false)
                continue;
            $controlClassName = $currentControl->getType()->getName();
            
            if ($view->getRenderCallbacks()->keyExists($controlClassName))
            {
                $renderCallback = $view->getRenderCallbacks()->getValue($controlClassName);
                
                if (is_callable($renderCallback, false))
                {
                    $tw->openTd();
                    call_user_func_array($renderCallback, array(
                        &$currentControl,
                        &$view
                    ));
                    $tw->closeTd();
                    
                    continue;
                }
            }
            
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Invalid render callback for model of type '" . $controlClassName . "'");
        }
        
        $tw->closeTr();
        $tw->closeTable();
        
        $tw->closeDiv();
    }
    
    /**
     * Helper function  that renders a toolbar button.
     * NOTE: The method exists here so that all HTML views can make use of predefined field model renderers
     *
     * @param ToolbarButton $model
     * @param HtmlViewBase $view
     */
    static protected function renderToolbarButton(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-button');
        $tw->addAttribute('eventname', $model->getEventName());
        $tw->addAttribute('eventvalue', "~");
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        
        $tw->openSpan();
        $tw->closeSpan(true);
        
        $tw->writeContent($model->getCaption());
        
        $tw->closeA();
    }
    
    /**
     * Helper function  that renders a toolbar label.
     * NOTE: The method exists here so that all HTML views can make use of predefined field model renderers
     *
     * @param ToolbarLabel $model
     * @param HtmlViewBase $view
     */
    static protected function renderToolbarLabel(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openSpan();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-label');
        $tw->writeContent($model->getCaption());
        $tw->closeSpan();
    }
    
    /**
     * Helper function  that renders a toolbar split.
     * NOTE: The method exists here so that all HTML views can make use of predefined field model renderers
     *
     * @param ToolbarSplit $model
     * @param HtmlViewBase $view
     */
    static protected function renderToolbarSplit(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openSpan();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-split');
        $tw->closeSpan(true);
    }
    
    /**
     * Helper function  that renders a toolbar button menu.
     *
     * @param ToolbarButtonMenu $model
     * @param HtmlViewBase $view
     */
    static protected function renderToolbarButtonMenu(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-button-menu');
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        $tw->addAttribute('id', $view->getHtmlId($model));
        
        $tw->writeContent($model->getCaption());
        
        $tw->closeA();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . "-toolbar-buttonmenu");
        
        foreach ($model->getItems() as $item)
            self::renderToolbarButtonMenuItem($item, $view);
        
        $tw->closeDiv();
    }
    
    /**
     * Helper function  that renders a toolbar button menu item.
     *
     * @param ToolbarButtonMenuItem $model
     * @param HtmlFormView $view
     * @param $customManager
     */
    static protected function renderToolbarButtonMenuItem(&$model, &$view, $customManager = '')
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('id', $view->getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        if ($customManager !== '')
            $tw->addAttribute('custommanager', $customManager);
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        $tw->addAttribute('class', 'menubutton-item');
        $tw->addAttribute('eventname', $model->getEventName());
        $tw->addAttribute('eventvalue', $model->getEventValue());
        $tw->writeContent($model->getCaption());
        $tw->closeA();
    }
    
    /**
     * Helper function  that renders a dynamic error marker + a tooltip for a field.
     *
     * @param FieldModelBase $model
     * @param HtmlFormView $view
     */
    static protected function renderFieldError(&$model, &$view)
    {
        if (!$model->hasErrorMessage())
            return;
        
        $tw = HtmlWriter::getInstance();
        
        // Render the marker
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-field-error');
        $tw->closeDiv(true);
        
        // Render the tooltip
        if ($model->getErrorMessage() != '')
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-tooltip');
            $tw->addAttribute('style', 'display: none;');
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-tooltip-top');
            $tw->closeDiv(true);
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-tooltip-content');
            $tw->writeContent($model->getErrorMessage(), false, true, false);
            $tw->closeDiv();
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-tooltip-bottom');
            $tw->closeDiv(true);
            
            $tw->closeDiv();
        }
    }
}

/**
 * Defines the common repeater base render callbacks
 *
 * @package WebCore
 * @subpackage View
 */
abstract class HtmlRepeaterRenderCallbacksBase extends HtmlRenderCallbacks
{
    /**
     * Helper method to render paging button
     *
     * @param RepeaterBase $model
     * @param HtmlView $view
     */
    static protected function renderPageNext(&$model, &$view)
    {
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        $tw          = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        if ($currentPage == $totalPages)
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-next-off');
        }
        else
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-next');
            $tw->addAttribute('eventvalue', $model->getState()->getPageIndex() + 1);
            $tw->addAttribute('eventname', 'GoPageIndex');
        }
        
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_REPEATER_PAGER_NEXT));
        $tw->openSpan();
        $tw->closeSpan(true);
        $tw->closeA();
    }
    
    /**
     * Helper method to render paging button
     *
     * @param RepeaterBase $model
     * @param HtmlView $view
     */
    static protected function renderPagePrevious(&$model, &$view)
    {
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        $tw          = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        if ($currentPage <= 1)
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-previous-off');
        }
        else
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-previous');
            $tw->addAttribute('eventvalue', $model->getState()->getPageIndex() - 1);
            $tw->addAttribute('eventname', 'GoPageIndex');
        }
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_REPEATER_PAGER_PREV));
        $tw->openSpan();
        $tw->closeSpan(true);
        $tw->closeA();
    }
    
    /**
     * Helper method to render paging button
     *
     * @param RepeaterBase $model
     * @param HtmlView $view
     */
    static protected function renderPageFirst(&$model, &$view)
    {
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        $tw          = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        if ($currentPage <= 1)
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-first-off');
        }
        else
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-first');
            $tw->addAttribute('eventvalue', '0');
            $tw->addAttribute('eventname', 'GoPageIndex');
        }
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_REPEATER_PAGER_FIRST));
        $tw->openSpan();
        $tw->closeSpan(true);
        $tw->closeA();
    }
    
    /**
     * Helper method to render paging button
     *
     * @param RepeaterBase $model
     * @param HtmlView $view
     */
    static protected function renderPageLast(&$model, &$view)
    {
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        if ($currentPage == $totalPages)
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-last-off');
        }
        else
        {
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-last');
            $tw->addAttribute('eventvalue', $model->getState()->getPageCount() - 1);
            $tw->addAttribute('eventname', 'GoPageIndex');
        }
        
        $tw->addAttribute('title', Resources::getValue(Resources::SRK_REPEATER_PAGER_LAST));
        $tw->openSpan();
        $tw->closeSpan(true);
        $tw->closeA();
    }
    
    /**
     * Helper method to render the repeater state and persistors
     *
     * @param DataRepeaterModelBase $model
     * @param HtmlView $view
     */
    protected static function renderDataRepeaterState(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        // The repeaterState
        $tw->openInput();
        $tw->addAttribute('type', 'hidden');
        $tw->addAttribute('id', $model->getStateName());
        $tw->addAttribute('name', $model->getStateName());
        $tw->addAttribute('value', $model->getState()->toBase64($model->getStateName()));
        $tw->closeInput(true);
        
        foreach ($model->getState()->getPersistors() as $key => $name)
        {
            $tw->openInput();
            $tw->addAttribute('type', 'hidden');
            $tw->addAttribute('name', $key);
            $tw->addAttribute('value', $name);
            $tw->closeInput();
        }
    }
}

/**
 * Represents an HTML View of the SiteMenu control model
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlSiteMenuView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class
     *
     * @param SiteMenu $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'sitemenu';
        $this->isAsynchronous = false;
        $this->showFrame      = false;
        $callbacks =& $this->renderCallbacks->getArrayReference();
        $callbacks['SiteMenu'] = array(
            'HtmlSiteMenuRenderCallbacks',
            'renderSiteMenu'
        );
        $this->registerDependencies();
    }
    
    /**
     * Sets whether the view should display a frame around it. This method is unsupported.
     * @param bool $value
     */
    public function setShowFrame($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'Frames are not supported in this view');
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     */
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        $jsPath  = HttpContext::getLibraryRoot() . 'js/std.sitemenu.js';
        $cssPath = HttpContext::getLibraryRoot() . 'css/std.sitemenu.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlSiteMenuView.Js', $jsPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlSiteMenuView.Css', $cssPath);
    }
}

/**
 * Represents an HTML View of the Breadcrumb control model
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlBreadcrumbView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class
     *
     * @param Breadcrumb $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'breadcrumbview';
        $this->isAsynchronous = false;
        $this->showFrame      = false;
        
        $callbacks =& $this->renderCallbacks->getArrayReference();
        $callbacks['Breadcrumb'] = array(
            'HtmlRenderCallbacks',
            'renderBreadcrumb'
        );
        
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     */
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
    }
}

/**
 * Defines the HTML render callbacks for the HtmlSiteMenuView
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlSiteMenuRenderCallbacks extends HtmlRenderCallbacks
{
    /**
     * Renders the full site menu model and all of its children
     *
     * @param SiteMenu $model
     * @param HtmlSiteMenuView $view
     */
    public static function renderSiteMenu(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('class', $view->getCssClass() . '-root');
        
        $tw->openUl();
        $tw->addAttribute('class', $view->getCssClass() . ' ' . $view->getCssClass() . '-bar');
        for ($i = 0; $i < $model->getMenuItems()->getCount(); $i++)
        {
            /**
             *@var SiteMenuItem
             */
            $menuItem = $model->getMenuItems()->getItem($i);
            
            if ($menuItem->getVisible() == false)
                continue;
            
            $tw->openLi();
            $tw->addAttribute('class', $view->getCssClass() . '-button');
            $tw->openA();
            $href = HtmlRenderCallbacks::HREF_NO_ACTION;
            if ($menuItem->getUrl() != '')
                $href = $menuItem->getUrl();
            $tw->addAttribute('href', $href);
            $tw->writeContent($menuItem->getCaption());
            $tw->closeA();
            
            if ($menuItem->getMenuItems()->getCount() > 0)
            {
                $tw->openUl();
                $tw->addAttribute('class', $view->getCssClass());
                for ($j = 0; $j < $menuItem->getMenuItems()->getCount(); $j++)
                {
                    $subMenuItem = $menuItem->getMenuItems()->getItem($j);
                    self::renderSiteMenuItem($subMenuItem, $view);
                }
                $tw->closeUl();
            }
            
            $tw->closeLi();
        }
        
        $tw->closeUl();
        $tw->closeDiv();
        $tw->openTag('div');
        $tw->addAttribute('class', $view->getCssClass() . '-clear');
        $tw->closeTag(true);
        
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw('var ' . $model->getName() . ';' . 'window.addEvent(\'domready\',' . 'function () { ' . $model->getName() . ' = new SiteMenu(\'' . HtmlViewBase::getHtmlId($model) . '\',\'' . $view->getCssClass() . '\');});');
        $tw->closeScript();
    }
    
    /**
     * Renders the full site menu model and all of its children
     *
     * @param SiteMenuItem $model
     * @param HtmlSiteMenuView $view
     */
    protected static function renderSiteMenuItem(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openLi();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->openA();
        $href = HtmlRenderCallbacks::HREF_NO_ACTION;
        if ($model->getUrl() != '')
            $href = $model->getUrl();
        $tw->addAttribute('href', $href);
        $tw->writeContent($model->getCaption());
        $tw->closeA();
        
        if ($model->getMenuItems()->getCount() > 0)
        {
            $tw->openUl();
            $tw->addAttribute('class', $view->getCssClass());
            for ($j = 0; $j < $model->getMenuItems()->getCount(); $j++)
            {
                $subMenuItem = $model->getMenuItems()->getItem($j);
                self::renderSiteMenuItem($subMenuItem, $view);
            }
            $tw->closeUl();
        }
        $tw->closeLi();
    }
}


/**
 * Represents an IRenderable object that includes a php file when its render method is called
 * @package WebCore
 * @subpackage View
 */
class HtmlNuggetView extends ObjectBase implements IRenderable
{
    protected $path;
    protected $args;
    
    /**
     * Creates a new instance of this class
     */
    public function __construct($path, $args = null)
    {
        $this->args = $args;
        $path = HttpContext::getDocumentRoot() . $path;
        if (!is_file($path))
        {
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The file '$path' was not found");
        }
        
        $this->path = $path;
    }
    
    /**
     * Gets the path of the included file
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Sets the path of the included file
     * @param string $path
     */
    public function setPath($path)
    {
        if (!is_file($path))
        {
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The file '$path' was not found");
        }
        $this->path = $path;
    }
    
    /**
     * Renders the html nugget by including the file
     */
    public function render()
    {
        include $this->path;
    }
    
    /**
     * Unsupported. This method is equivalent to calling the getPath method
     * @return string
     */
    public function &getModel()
    {
        return $this->path;
    }
}

/**
 * Represents a Content renderer using a wrapping tag around it content.
 * Content can either be a string or an IRenderable instance of an object.
 * @package WebCore
 * @subpackage View
 */
class HtmlTagView extends SerializableObjectBase implements IRenderable
{
    protected $tagName;
    protected $attributes;
    protected $content;
    protected $writeRaw;
    protected $useNlToBr;
    protected $useSpToNbsp;
    
    /**
     * Creates a new instance of this class
     * @param string $tagName
     * @param bool $writeRaw
     * @param bool $useNlToBr
     * @param bool $useSpToNbsp
     */
    public function __construct($tagName = 'div', $writeRaw = false, $useNlToBr = true, $useSpToNbsp = true)
    {
        $this->tagName     = $tagName;
        $this->attributes  = new KeyedCollection();
        $this->content     = '';
        $this->writeRaw    = $writeRaw;
        $this->useNlToBr   = $useNlToBr;
        $this->useSpToNbsp = $useSpToNbsp;
    }
    
    /**
     * Shorcut method to sets rendering options for this view at once
     * @param bool $writeRaw
     * @param bool $useNlToBr
     * @param bool $useSpToNbsp
     */
    public function setOptions($writeRaw = false, $useNlToBr = true, $useSpToNbsp = true)
    {
        $this->writeRaw    = $writeRaw;
        $this->useNlToBr   = $useNlToBr;
        $this->useSpToNbsp = $useSpToNbsp;
    }
    
    /**
     * Determines whether the rendering of the content should be HTML-encoded or not.
     * @return bool
     */
    public function getWriteRaw()
    {
        return $this->writeRaw;
    }
    
    /**
     * Determines whether the rendering of the content should be HTML-encoded or not.
     * @param bool $value
     */
    public function setWriteRaw($value)
    {
        $this->writeRaw = $value;
    }
    
    /**
     * Determines whether new lines in contents hsould be replaced by BR tags
     * @return bool
     */
    public function getUseNlToBr()
    {
        return $this->useNlToBr;
    }
    
    /**
     * Determines whether new lines in contents hsould be replaced by BR tags
     * @param bool $value
     */
    public function setUseNlToBr($value)
    {
        $this->useNlToBr = $value;
    }
    
    /**
     * Determines whether spaces should be automatically replaced by NBSP entities
     * @return bool
     */
    public function getUseSpToNbsp()
    {
        return $this->useSpToNbsp;
    }
    
    /**
     * Determines whether new lines in contents hsould be replaced by BR tags
     * @param bool $value
     */
    public function setUseSpToNbsp($value)
    {
        $this->useSpToNbsp = $value;
    }
    
    /**
     * Creates a default instance of this class.
     * @return HtmlTagView
     */
    public static function createInstance()
    {
        return new HtmlTagView();
    }
    
    /**
     * Creates an instance of this class using a tag, css class and defined content.
     * @param string $tagName
     * @param string $clasName
     * @param mixed $content Either an IRenderable object or a string
     * @return HtmlTagView
     */
    public static function createTag($tagName = 'div', $className = '', $content = '')
    {
        $obj = new HtmlTagView($tagName);
        $obj->setContent($content);
        if ($className != '')
            $obj->attributes->setValue('class', $className);
        return $obj;
    }
    
    /**
     * Sets the tag name for this instance.
     * @param string $value
     */
    public function setTagName($value)
    {
        $this->tagName = $value;
    }
    
    /**
     * Gets the tag name for this instance.
     * @return string
     */
    public function getTagName()
    {
        return $this->tagName;
    }
    
    /**
     * Sets the content to render inside the enclosing tag.
     * Argument can be either a string or an IRenderable object
     * @param mixed $value
     */
    public function setContent($value)
    {
        if (is_string($value) === false && ObjectIntrospector::isImplementing($value, 'IRenderable') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->content = $value;
    }
    
    /**
     * Gets the content to render inside the enclosing tag.
     * The return value can either be a string or an IRenderable object
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Provides direct access to the attributes of this tag.
     * @return KeyedCollection
     */
    public function &getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Returns the content of this tag.
     * @return string
     */
    public function &getModel()
    {
        return $this->content;
    }
    
    /**
     * Renders this HTML tag with the HtmlWriter
     */
    public function render()
    {
        $tw = HtmlWriter::getInstance();
        if ($this->tagName != '')
        {
            $tw->openTag($this->tagName);
            
            foreach ($this->attributes->getKeys() as $attribName)
                $tw->addAttribute($attribName, $this->attributes->getValue($attribName));
        }
        if (is_string($this->content))
        {
            if ($this->writeRaw === true)
                $tw->writeRaw($this->content);
            else
                $tw->writeContent($this->content, LogManager::isDebug(), $this->useNlToBr, $this->useSpToNbsp);
        }
        elseif (is_object($this->content) && ObjectIntrospector::isImplementing($this->content, 'IRenderable'))
        {
            $this->content->render();
        }
        else
        {
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The content for this tag must be either as string or an instance of an IRenderable object.');
        }
        
        if ($this->tagName != '')
            $tw->closeTag();
    }
    
    /**
     * Shortcut method to add an attribute to this Html tag
     * @param string $attribute
     * @param string $value
     */
    public function addAttribute($attribute, $value)
    {
        $this->attributes->setValue($attribute, $value);
    }
}

/**
 * Represents a collection of views in which every element is renderable.
 * The class supports rendering views in a tree-like fashion.
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlViewCollection extends IndexedCollection implements IRenderable
{
    protected $rootTagName;
    protected $rootTagAttributes;
    protected $childTagName;
    protected $childTagAttributes;
    
    /**
     * Creates a new instance of this class.
     */
    public function __construct()
    {
        parent::__construct();
        $this->rootTagName        = 'div';
        $this->rootTagAttributes  = new KeyedCollection();
        $this->childTagName       = 'div';
        $this->childTagAttributes = new KeyedCollection();
    }
    
    /**
     * Method is unsupported.
     * @return SystemException
     */
    public function &getModel()
    {
        throw new SystemException(SystemException::EX_INVALIDOPERATION, 'This view is model-less.');
    }
    
    /**
     * Determines the tag to use in order to enclose all the renderable elements in the collection.
     * If set to an empty string, no tag is rendered around the set of renderable elements.
     * @return string
     */
    public function getRootTagName()
    {
        return $this->rootTagName;
    }
    
    /**
     * Determines the tag to use in order to enclose all the renderable elements in the collection.
     * If set to an empty string, no tag is rendered around the set of renderable elements.
     * @param string $value
     */
    public function setRootTagName($value)
    {
        $this->rootTagName = $value;
    }
    
    /**
     * Gets the keyed collection with the attributes to render for the root tag.
     * @return KeyedCollection
     */
    public function getRootTagAttributes()
    {
        return $this->rootTagAttributes;
    }
    
    /**
     * Determines the tag to use in order to enclose each of the renderable elements in the collection.
     * If set to an empty string, no tag is rendered around each of the renderable elements.
     * @return string
     */
    public function getChildTagName()
    {
        return $this->childTagName;
    }
    
    /**
     * Determines the tag to use in order to enclose each of the renderable elements in the collection.
     * If set to an empty string, no tag is rendered around each of the renderable elements.
     * @param string $value
     */
    public function setChildTagName($value)
    {
        $this->childTagName = $value;
    }
    
    /**
     * Gets the keyed collection with the attributes to render for each of the children tags.
     * @return KeyedCollection
     */
    public function getChildTagAttributes()
    {
        return $this->childTagAttributes;
    }
    
    /**
     * Renders this collection along with its children.
     */
    public function render()
    {
        $tw = HtmlWriter::getInstance();
        if ($this->rootTagName != '')
        {
            $tw->openTag($this->rootTagName);
            foreach ($this->rootTagAttributes->getKeys() as $rootAttrib)
            {
                $tw->addAttribute($rootAttrib, $this->rootTagAttributes->getValue($rootAttrib));
            }
        }
        
        if ($this->getCount() > 0)
        {
            foreach ($this->getArrayReference() as $view)
            {   
                if ($this->childTagName != '')
                {
                    $tw->openTag($this->childTagName);
                    foreach ($this->childTagAttributes->getKeys() as $childAttrib)
                    {
                        $tw->addAttribute($childAttrib, $this->childTagAttributes->getValue($childAttrib));
                    }
                }
                
                $view->render();
                if ($this->childTagName != '')
                    $tw->closeTag(true);
            }
        }

        if ($this->rootTagName != '')
            $tw->closeTag(true);
    }
}

/**
 * Provides a templating engine for web page output.
 * 
 * @package WebCore
 * @subpackage View
 */
class WebPage extends ObjectBase implements IRenderable
{
    protected $pageTitle;
    /**
     * @var KeyedCollection
     */
    protected $contentPlaceholders;
    protected $templateFile;
    protected $pageFields;
    protected static $currentWebPage;
    protected $useOutputBuffering;
    
    /**
     * Creates a new instance of this class
     * @param string $templateFile the php file to include when rendering. If a relative path is provided, the document root will be prepended to the template file path
     * @param string $pageTitle The title of the page
     */
    public function __construct($templateFile, $pageTitle = '')
    {
        if (!StringHelper::beginsWith($templateFile, HttpContext::getDocumentRoot())) $templateFile = HttpContext::getDocumentRoot() . $templateFile;
        if (!is_file($templateFile)) throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The file '$templateFile' was not found.");
        
        $this->pageTitle           = $pageTitle;
        $this->templateFile        = $templateFile;
        $this->contentPlaceholders = new KeyedCollection();
        $this->pageFields          = new KeyedCollection();
        self::$currentWebPage      = $this;
        $this->useOutputBuffering  = false;
    }
    
    /**
     * Gets the current web page being server in order to provide access to it from a template.
     * @return WebPage
     */
    public static function &getCurrent()
    {
        return self::$currentWebPage;
    }
    
    /**
     * Determines whether to use output buffering when rendering.
     * @return bool
     */
    public function getUseOutputBuffering()
    {
        return $this->useOutputBuffering;
    }
    
    /**
     * Determines whether to use output buffering when rendering.
     * @param bool $value
     */
    public function setUseOutputBuffering($value)
    {
        if (!is_bool($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'value must be bool.');
        
        $this->useOutputBuffering = $value;
    }
    
    /**
     * Creates a default instance of this class
     * @return WebPage
     */
    public static function createInstance()
    {
        return new WebPage('');
    }
    
    /**
     * Returns a reference to this object as it is not based on a model.
     * @return WebPage
     */
    public function &getModel()
    {
        return $this;
    }
    
    /**
     * Provides access to the custom page fields collection.
     * Use this keyed collection for variable storage.
     * @return KeyedCollection
     */
    public function &getFields()
    {
        return $this->pageFields;
    }
    
    /**
     * Shortcut method to get a page field
     *
     * @param string $name
     * 
     * @return mixed
     */
    public function getField($name)
    {
        if ($this->pageFields->keyExists($name) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "field '$name' does not exist");
        
        return $this->pageFields->getValue($name);
    }
    
    /**
     * Shortcut method to set a page field
     *
     * @param string $name
     * @param mixed value
     */
    public function setField($name, $value)
    {
        if (!is_string($name))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'name must be a string.');
        
        $this->pageFields->setValue($name, $value);
    }
    
    /**
     * Gets the page's title
     * @return string
     */
    public function getTitle()
    {
        return $this->pageTitle;
    }
    
    /**
     * Sets the page's title
     * @param string $value
     */
    public function setTitle($value)
    {
        if (!is_string($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'value must be a string.');
        $this->pageTitle = $value;
    }
    
    /**
     * Gets the content placeholders for this page.
     * Content placeholders are basically a collection of views.
     * @return KeyedCollection
     */
    public function &getPlaceholders()
    {
        return $this->contentPlaceholders;
    }
    
    /**
     * Gets current place holder.
     * @return IRenderable
     */
    public function &getPlaceholder($name)
    {
        if (!is_string($name))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'name must be a string.');
        
        return $this->contentPlaceholders->getItem($name);
    }
    
    /**
     * Shortcut method to render a placeholder (if it exists)
     */
    public function renderContent($placeHolderName)
    {
        if ($this->contentPlaceholders->keyExists($placeHolderName))
            $this->contentPlaceholders->getItem($placeHolderName)->render();
    }
    
    /**
     * Gets the template file upon page rending.
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }
    
    /**
     * Sets the template file upon rendering
     * @param string $value
     */
    public function setTemplateFile($value)
    {
        if (!is_string($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'value must be a string.');
        
        $this->templateFile = $value;
    }
    
    /**
     * Adds a view to the specified placeholder
     * @param string $placeholderName
     * @param IRenderable $view
     */
    public function addContent($placeholderName, $view)
    {
        if (!is_string($placeholderName))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'placeholderName must be a string.');
        if (!ObjectIntrospector::isA($view, 'IRenderable'))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'view must implement IRenderable.');
        
        // Dynamically create the placeholder if it does not exist
        if ($this->contentPlaceholders->keyExists($placeholderName) === false)
        {
            if (ObjectIntrospector::isA($view, 'HtmlNuggetView'))
            {
                $this->contentPlaceholders->setItem($placeholderName, $view);
                return;
            }
            else
            {
                $this->contentPlaceholders->setValue($placeholderName, new HtmlViewCollection());
            }
        }
        
        /**
         * @var HtmlViewCollection
         */
        $placeholder = $this->contentPlaceholders->getItem($placeholderName);
        if (ObjectIntrospector::isA($placeholder, 'HtmlViewCollection'))
        {
            $placeholder->addItem($view);
        }
        else
        {
            throw new SystemException(SystemException::EX_INVALIDOPERATION, "Placeholder '$placeholderName' is already set to an object.");
        }
    }
    
    /**
     * Register HtmlViewManager dependencies prior to rendering.
     * This method is usually overridden in derived classes.
     */
    protected function registerDependencies()
    {
    }
    
    /**
     * Called before the render() method.
     * Instantiate any models and views here.
     */
    protected function preRender()
    {
    }
    
    /**
     * Outputs standard headers such as content type and encoding
     */
    protected function outputHeaders()
    {
        HttpResponse::setContentType('text/html');
        HttpResponse::appendHeader('Content-Encoding', 'UTF-8');
    }
    
    /**
     * Outputs the web page to the output buffer.
     */
    public function render()
    {
        HttpContext::applySecurity();
        $this->preRender();
        $this->registerDependencies();
        $this->outputHeaders();
        
        if ($this->useOutputBuffering === true)
            HttpResponse::outputBufferStart();
        
        include_once($this->templateFile);
        
        if ($this->useOutputBuffering === true)
        {
            HttpResponse::setContentLength(HttpResponse::outputBufferGetLength());
            HttpResponse::outputBufferFlush();
        }
        
    }
}

/**
 * Represents a TabView page. This is exactly the same as an HtmlViewCollection except it is used exclusively
 * within the HtmlTabView class. Do not use this class on its own.
 * @package WebCore
 * @subpackage View
 */
class HtmlTabViewPage extends HtmlViewCollection
{
    protected $caption;
    protected $id;
    protected $cssClass;
    
    /**
     * Creates a new instance of this class
     * @param string $id
     * @param string $caption
     */
    public function __construct($id, $caption)
    {
        $this->cssClass          = "formview";
        $this->caption           = $caption;
        $this->id                = $id;
        $this->rootTagAttributes = new KeyedCollection();
        $this->setRootTagName('div');
        $this->getRootTagAttributes()->setValue('id', 'tabpage_' . $this->getId());
        $this->getRootTagAttributes()->setValue('class', $this->getCssClass() . '-tabpage ' . $this->getCssClass() . '-tabpage-nooffsets');
    }
    
    /**
     * Determines the Css Class base name to use for this tab page.
     * @return string
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }
    
    /**
     * Determines the caption of the tab page.
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    /**
     * Determines the caption of the tab page.
     * @param string $value
     */
    public function setCaption($value)
    {
        $this->caption = $value;
    }
    
    /**
     * Determines de HTML id tag attribute of this view
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Determines de HTML id tag attribute of this view
     * @param string $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }
}

/**
 * Provides an HTML, model-less view for laying out IRenderable views within tab pages.
 * @package WebCore
 * @subpackage View
 */
class HtmlTabView extends HtmlViewBase
{
    protected $tabPages;
    protected $id;
    
    /**
     * Creates a new instance of this class
     * @param string $id
     */
    public function __construct($id)
    {
        if (!is_string($id) || $id == '')
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The id '{$id}' is invalid");
        
        $this->bodyHeight      = 0;
        $this->isAsynchronous  = false;
        $this->showFrame       = true;
        $this->frameWidth      = "auto";
        $this->renderCallbacks = new KeyedCollection();
        $this->masterCssClass  = "formview";
        $this->cssClass        = "formview";
        $this->id              = $id;
        $this->tabPages        = new IndexedCollection();
        
        $this->registerDependencies();
    }
    
    /**
     * Method is unsupported.
     * @return SystemException
     */
    public function &getModel()
    {
        throw new SystemException(SystemException::EX_INVALIDOPERATION, 'This view is model-less.');
    }
    
    /**
     * Determines de HTML id tag attribute of this view
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Determines de HTML id tag attribute of this view
     * @param string $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }
    
    /**
     * Adds a new tab page to this control.
     * Tab pages are essentially HtmlViewCollections specific to this parent view.
     * @param string $id
     * @param string $caption
     */
    public function addTabPage($id, $caption)
    {
        if (!is_null($this->getTabPage($id)))
            throw new SystemException(SystemException::EX_DUPLICATEDKEY, 'A TabViewPage with the same id already exists.');
        
        $tabPage = new HtmlTabViewPage($id, $caption);
        $this->tabPages->addItem($tabPage);
    }
    
    /**
     * Gets a tab page by its Id. Returns null if the tab page is not found.
     * @return HtmlTabViewPage
     */
    public function getTabPage($id)
    {
        foreach ($this->tabPages as $tabPage)
        {
            if ($tabPage->getId() == $id)
                return $tabPage;
        }
        
        return null;
    }
    
    /**
     * Gets an IndexedCollection of child HtmlTabViewPage objects.
     * @return IndexedCollection
     */
    public function getTabPages()
    {
        return $this->tabPages;
    }
    
    /**
     * Register HtmlTabView dependencies prior to rendering.
     */
    protected function registerDependencies()
    {
        parent::registerCommonDependecies();
        $rootPath       = HttpContext::getLibraryRoot();
        $tabPagePath    = $rootPath . 'js/std.formview.tabview.js';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlFormView.TabView.Js', $tabPagePath);
    }
    
    /**
     * Gets the client-side javascript variable name for this control.
     * @return string
     */
    public function getJsId()
    {
        return 'js_' . $this->getId();
    }
    
    /**
     * Renders this control through the default HtmlWriter singleton.
     */
    public function render()
    {
        if ($this->getTabPages()->getCount() === 0)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'No tab pages to render.');
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('id', $this->getId());
        $tw->addAttribute('class', $this->getCssClass() . '-tabview');
        $tw->addAttribute('style', 'width:' . $this->getFrameWidth() . ';');
        
        $tw->openInput();
        $tw->addAttribute('type', 'hidden');
        $tw->addAttribute('id', $this->getId() . '_activeTabPage');
        $tw->addAttribute('name', $this->getId() . '_activeTabPage');
        $tw->addAttribute('value', $this->getTabPages()->getItem(0)->getId());
        $tw->closeInput();
        
        $tw->openUl();
        $tw->addAttribute('class', $this->getCssClass() . '-tabtitle-container');
        
        foreach ($this->getTabPages() as $tabPage)
        {
            $tw->openLi();
            $tw->addAttribute('container', $tabPage->getId());
            $tw->addAttribute('class', $this->getCssClass() . '-tabtitle');
            $tw->writeContent($tabPage->getCaption());
            $tw->closeLi();
        }
        
        $tw->closeUl();
        
        foreach ($this->getTabPages() as $tabPage)
        {
            $tabPage->render();
        }
        
        $tw->closeDiv(true);
        
        $javascript = "var js_" . $this->getId() . " = null;
            window.addEvent('domready', function () { " . $this->getJsId() . " = new TabView('" . $this->getId() . "', '" . $this->getCssClass() . "'); });";
        
        $tw->openScript();
        $tw->addAttribute('defer', 'defer');
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw($javascript);
        $tw->closeScript(true);
    }
}
?>