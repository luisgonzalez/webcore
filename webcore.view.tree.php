<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides models of controls in tree control
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 * @todo Document these classes approriately
 */

class HtmlTreeControlView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class.
     * @param TreeControlModel $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass = 'treeview';
        $callbacks =& $this->renderCallbacks->getArrayReference();
        $this->isAsynchronous = true;
        $this->frameWidth     = "auto";
        
        // Setup the callbacks for each renderable model
        $callbacks['TreeControlModel']     = array(
            'HtmlTreeControlRenderCallbacks',
            'renderTreeControl'
        );
        $callbacks['TreeNodeControlModel'] = array(
            'HtmlTreeControlRenderCallbacks',
            'renderTreeNodeControl'
        );
        
        $this->registerDependencies();
    }
    
    public function render()
    {
        parent::render();
    }
    
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        
        $controlCssPath = HttpContext::getLibraryRoot() . 'css/std.treeview.css';
        $controlJsPath  = HttpContext::getLibraryRoot() . 'js/std.treeview.js';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlTreeControlView.Css', $controlCssPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlTreeControlView.Js', $controlJsPath);
    }
}

class HtmlTreeControlRenderCallbacks extends HtmlRenderCallbacks
{
    /**
     * Renders the full grid model and all of its children
     *
     * @param TreeNodeControlModel $model
     * @param HtmlTreeControlView $view
     */
    public static function renderTreeNodeControl(&$model, &$view, $level)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openLi();
        $tw->addAttribute('tag', $model->getTag());
        $tw->addAttribute('isExpanded', ($model->getIsExpanded() ? 'true' : 'false'));
        $tw->addAttribute('class', $view->getCssClass() . '-node-' . $level);
        
        $tw->openSpan();
        $tw->writeContent($model->getCaption());
        $tw->closeSpan();
        
        if ($model->getNodes()->getCount() > 0)
        {
            $tw->openUl();
            self::renderNodes($model, $view, $level);
            $tw->closeUl();
        }
        
        $tw->closeLi();
    }
    
    /**
     * @param TreeNodeControlModelBase $model
     * @param HtmlTreeControlView $view
     */
    protected static function renderNodes(&$model, &$view, $level)
    {
        // The child nodes
        foreach ($model->getNodes() as $currentControl)
        {
            if ($currentControl->getVisible() === false)
                continue;
            $controlClassName = $currentControl->getType()->getName();
            
            if ($view->getRenderCallbacks()->keyExists($controlClassName))
            {
                $renderCallback = $view->getRenderCallbacks()->getValue($controlClassName);
                if (is_callable($renderCallback, false))
                {
                    call_user_func_array($renderCallback, array(
                        &$currentControl,
                        &$view,
                        $level + 1
                    ));
                    continue;
                }
            }
            
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Invalid render callback for model of type '" . $controlClassName . "'");
        }
    }
    
    /**
     * Renders the full tree node
     *
     * @param TreeControlModel $model
     * @param HtmlTreeControlView $view
     */
    public static function renderTreeControl(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('enctype', 'multipart/form-data');
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-container');
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_placeholder');
        $tw->closeDiv(true);
        
        $tw->openUl();
        $tw->addAttribute('class', $view->getCssClass() . '-rootnode');
        self::renderNodes($model, $view, 0);
        $tw->closeUl();
        
        // Add the postback hidden field to signal postbacks
        self::renderPostBackFlag($model, $view);
        $tw->closeDiv();
        
        foreach ($model->getState()->getPersistors() as $key => $name)
        {
            $tw->openInput();
            $tw->addAttribute('type', 'hidden');
            $tw->addAttribute('name', $key);
            $tw->addAttribute('value', $name);
            $tw->closeInput();
        }
        
        $tw->closeForm();
        
        $javascript = "var js_" . HtmlViewBase::getHtmlId($model) . " = null;
                    window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new HtmlTreeView('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', true); });";
        self::renderInitializationScript($javascript);
    }
}
?>