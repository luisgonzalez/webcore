<?php
/**
 * Provides a standard Portlet model renderer.
 * @todo Register dependecies of portlets ??!
 * 
 * @package WebCore
 * @subpackage View
 */
class HtmlWorkspaceView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class
     *
     * @param Workspace $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'workspaceview';
        $this->isAsynchronous = true;
        $this->frameWidth     = "90%";
        
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        $callbacks['Workspace'] = array(
            'HtmlWorkspaceRenderCallbacks',
            'renderWorkspace'
        );
        
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     *
     */
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        
        $portletViewPath = HttpContext::getLibraryRoot() . 'ext/portlet/std.portletview.js';
        $cssPath         = HttpContext::getLibraryRoot() . 'ext/portlet/std.portletview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlPortletView.Js', $portletViewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlPortletView.Css', $cssPath);
    }
}

/**
 * Contains static callback methods to render standard framework HTML controls in a Repeater
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlWorkspaceRenderCallbacks extends HtmlRenderCallbacks
{
    /**
     * Renders the main workspace
     *
     * @param Workspace $model
     * @param HtmlPortletView $view
     */
    public static function renderWorkspace(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-workspace');
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_workspace');
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();
        
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        $tw->addAttribute('method', 'post');
        
        foreach ($model->getToolbars() as $currentControl)
        {
            $controlClassName = $currentControl->getType()->getName();
            
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
        
        // Render state data
        $tw->openDiv();
        $tw->addAttribute('style', 'display:none; visibility: collapse;');
        $tw->openInput();
        $tw->addAttribute('type', 'hidden');
        $tw->addAttribute('id', Controller::getPostBackFlagName($model));
        $tw->addAttribute('name', Controller::getPostBackFlagName($model));
        $tw->addAttribute('value', '1');
        $tw->closeInput(false);
        
        $tw->openInput();
        $tw->addAttribute('type', 'hidden');
        $tw->addAttribute('id', $model->getStateName());
        $tw->addAttribute('name', $model->getStateName());
        $tw->addAttribute('value', $model->getState()->toBase64($model->getStateName()));
        $tw->closeInput(false);
        $tw->closeDiv();
        
        $tw->closeForm();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-container');
        
        foreach ($model->getPortlets() as $portlet)
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-portlet');
            $tw->addAttribute('dock', $portlet->getDock());
            
            $tw->openDiv();
            $tw->addAttribute('class', 'view-frame');
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-portlet-close');
            $tw->closeDiv(true);
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-portlet-maximize');
            $tw->closeDiv(true);
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-portlet-minimize');
            $tw->closeDiv(true);
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-portlet-title');
            $tw->writeContent($portlet->getTitle());
            $tw->closeDiv();
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-portlet-content');
            $portlet->render();
            $tw->closeDiv();
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-portlet-resizable');
            $tw->closeDiv(true);
            
            $tw->closeDiv();
            
            $tw->closeDiv();
        }
        
        $tw->openTable();
        $tw->addAttribute('class', $view->getCssClass() . '-dock-table');
        
        $tw->openTr();
        $tw->openTd();
        $tw->addAttribute('colspan', '3');
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_workspace_north');
        $tw->addAttribute('class', $view->getCssClass() . '-dock');
        $tw->closeDiv(true);
        $tw->closeTd(true);
        $tw->closeTr();
        
        $tw->openTr();
        
        $tw->openTd();
        $tw->addAttribute('width', '200px');
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_workspace_west');
        $tw->addAttribute('class', $view->getCssClass() . '-dock ' . $view->getCssClass() . '-dock-vertical');
        $tw->closeDiv(true);
        $tw->closeTd();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-dock-fill');
        $tw->closeTd(true);
        
        $tw->openTd();
        $tw->addAttribute('width', '200px');
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_workspace_east');
        $tw->addAttribute('class', $view->getCssClass() . '-dock ' . $view->getCssClass() . '-dock-vertical');
        $tw->closeDiv(true);
        $tw->closeTd(true);
        
        $tw->closeTr();
        
        $tw->openTr();
        $tw->openTd();
        $tw->addAttribute('colspan', '3');
        $tw->openDiv();
        $tw->addAttribute('id', 'portlet_' . $model->getName() . '_workspace_south');
        $tw->addAttribute('class', $view->getCssClass() . '-dock');
        $tw->closeDiv(true);
        $tw->closeTd(true);
        $tw->closeTr();
        
        $tw->closeTable();
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-taskbar');
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_workspace_taskbar');
        $tw->writeContent(" ");
        $tw->closeDiv();
        
        $enableAsync = ($view->getIsAsynchronous() == true) ? 'true' : 'false';
        $javascript  = "var js_" . HtmlViewBase::getHtmlId($model) . " = null;
                    window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new HtmlPortletView('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', $enableAsync); });";
        self::renderInitializationScript($javascript);
        
        $tw->closeDiv();
    }
}
?>