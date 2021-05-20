<?php
/**
 * @package WebCore
 * @subpackage View
 * @version 1.0
 * 
 * Provides views for several repeater types and repeater fields
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.view.php";

/**
 * Provides a standard Repeater model renderer.
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlRepeaterView extends HtmlViewBase
{
    /**
     * This var was introduced in order to render a description
     * above the grid
     * @var string
     */
    protected $descriptionText;

    public function getDescriptionText()
    {
        return $this->descriptionText;
    }

    public function setDescriptionText($descriptionText)
    {
        $this->descriptionText = $descriptionText;
    }

        /**
     * Creates a new instance of this class
     *
     * @param Repeater $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'repeaterview';
        $this->isAsynchronous = true;
        
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        $callbacks['DataRepeater']          = array(
            'HtmlRepeaterRenderCallbacks',
            'renderDataRepeater'
        );
        $callbacks['EditableDataRepeater']  = array(
            'HtmlRepeaterRenderCallbacks',
            'renderEditableDataRepeater'
        );
        $callbacks['TextRepeaterField']     = array(
            'HtmlRepeaterRenderCallbacks',
            'renderTextRepeaterField'
        );
        $callbacks['LinkRepeaterField']     = array(
            'HtmlRepeaterRenderCallbacks',
            'renderLinkRepeaterField'
        );
        $callbacks['ImageRepeaterField']    = array(
            'HtmlRepeaterRenderCallbacks',
            'renderImageRepeaterField'
        );
        $callbacks['CommandRepeaterField']  = array(
            'HtmlRepeaterRenderCallbacks',
            'renderCommandRepeaterField'
        );
        $callbacks['CommandEditRepeaterField']  = array(
            'HtmlRepeaterRenderCallbacks',
            'renderCommandEditRepeaterField'
        );
        $callbacks['LabelRepeaterField']    = array(
            'HtmlRepeaterRenderCallbacks',
            'renderLabelRepeaterField'
        );
        $callbacks['TextBoxRepeaterField']  = array(
            'HtmlRepeaterRenderCallbacks',
            'renderTextBoxRepeaterField'
        );
        $callbacks['IntegerRepeaterField']  = array(
            'HtmlRepeaterRenderCallbacks',
            'renderTextBoxRepeaterField'
        );
        $callbacks['EmailRepeaterField']    = array(
            'HtmlRepeaterRenderCallbacks',
            'renderTextBoxRepeaterField'
        );
        $callbacks['ComboBoxRepeaterField'] = array(
            'HtmlRepeaterRenderCallbacks',
            'renderComboBoxRepeaterField'
        );
        $callbacks['CheckBoxRepeaterField'] = array(
            'HtmlRepeaterRenderCallbacks',
            'renderCheckBoxRepeaterField'
        );
        $callbacks['SummaryControl']        = array(
            'HtmlRepeaterRenderCallbacks',
            'renderSummaryControl'
        );

        $this->descriptionText = '';
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     *
     */
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        
        $repeaterCssPath = HttpContext::getLibraryRoot() . 'css/std.repeaterbaseview.css';
        
        if (ObjectIntrospector::isA($this->model, 'EditableDataRepeater'))
        {
            $repeaterviewPath = HttpContext::getLibraryRoot() . 'js/std.editablerepeaterview.js';
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlEditableRepeaterView.Js', $repeaterviewPath);
        }
        else
        {
            $repeaterviewPath = HttpContext::getLibraryRoot() . 'js/std.repeaterview.js';
            HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlRepeaterView.Js', $repeaterviewPath);
        }
        
        $cssPath = HttpContext::getLibraryRoot() . 'css/std.repeaterview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlRepeaterBaseView.Css', $repeaterCssPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlRepeaterView.Css', $cssPath);
    }
}

/**
 * Provides a Accordion model renderer for dataRepeater
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlAccordionView extends HtmlRepeaterView
{
    protected $titleKey;
    protected $initialIndex;
    
    /**
     * Creates a new instance of this class
     *
     * @param Repeater $model
     */
    public function __construct(&$model, $titleKey)
    {
        parent::__construct($model);
        
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        $callbacks['DataRepeater'] = array(
            'HtmlRepeaterRenderCallbacks',
            'renderAccordion'
        );
        
        $this->titleKey     = $titleKey;
        $this->initialIndex = 0;
    }
    
    /**
     * Determines which of the view indices is displayed initially.
     * @return int
     */
    public function getInitialIndex()
    {
        return $this->initialIndex;
    }
    
    /**
     * Determines which of the view indices is displayed initially.
     * @param int $value
     */
    public function setInitialIndex($value)
    {
        $this->initialIndex = $value;
    }
    
    /**
     * Sets the edit key
     *
     * @param string $value
     */
    public function setTitleKey($value)
    {
        $this->titleKey = $value;
    }
    
    /**
     * Gets the edit key
     *
     * @return string
     */
    public function getTitleKey()
    {
        return $this->titleKey;
    }
}

/**
 * Contains static callback methods to render standard framework HTML controls in a Repeater
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlRepeaterRenderCallbacks extends HtmlRepeaterRenderCallbacksBase
{
    const RENDERMODE_NONE = '';
    const RENDERMODE_NEW = 'new';
    const RENDERMODE_EDIT = 'edit';
    
    /**
     * Renders data item breakers within items that form a set of floating divs.
     * @param IRenderable $view
     */
    private static function renderDataItemBreaker(&$view)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-item-breaker');
        $tw->closeDiv(true);
    }
    
    /**
     * @param CommandEditRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     */
    public static function renderCommandEditRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-cell');
        
        if ($itemIndex != -1)
        {
            $dataItem        = $repeaterModel->getDataItems()->getItem($itemIndex);
            $objectAttribute = $model->getBindingMemberName();
            $value           = $dataItem->$objectAttribute;
        
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            $tw->addAttribute('id', 'button_' . $model->getName() . '_' . $itemIndex);
            $tw->addAttribute('name', $model->getName());
            $tw->addAttribute('class', 'rowcommand');
            $tw->addAttribute('eventname', $model->getEventName());
            $tw->addAttribute('eventvalue', $value);
            
            $tw->writeContent($model->getCaption());
            $tw->closeA();
        }
        
        $tw->closeTd();
    }
    
    /**
     * @param CommandRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     */
    public static function renderCommandRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel)
    {
        $tw = HtmlWriter::getInstance();
        
        $dataItem        = $repeaterModel->getDataItems()->getItem($itemIndex);
        $objectAttribute = $model->getBindingMemberName();
        $value           = $dataItem->$objectAttribute;
        
        $tw->openA();
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        $tw->addAttribute('id', 'button_' . $model->getName() . '_' . $itemIndex);
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass() . '-button');
        $tw->addAttribute('eventname', $model->getEventName());
        $tw->addAttribute('eventvalue', $value);
        
        $tw->writeContent($model->getCaption());
        $tw->closeA();
    }
    
    /**
     * @param ImageRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     */
    public static function renderImageRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel)
    {
        $tw = HtmlWriter::getInstance();
        
        $dataItem        = $repeaterModel->getDataItems()->getItem($itemIndex);
        $objectAttribute = $model->getBindingMemberName();
        $text            = $dataItem->$objectAttribute;
        $src             = sprintf($model->getCaption(), $text);
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-item-image ' . $objectAttribute . '-image');
        
        if ($model->getUrl() == '')
        {
            $tw->openImg();
            $tw->addAttribute('src', $src);
            $tw->closeImg();
        }
        else
        {
            $url = sprintf($model->getUrl(), $text);
            
            $tw->openA();
            $tw->addAttribute('href', $url);
            
            $tw->openImg();
            $tw->addAttribute('src', $src);
            $tw->closeImg();
            
            $tw->closeA();
        }
        self::renderDataItemBreaker($view);
        $tw->closeDiv();
    }
    
    /**
     * @param ComboBoxRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     * @param string $renderMode
     */
    public static function renderComboBoxRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel, $renderMode = '')
    {
        $tw              = HtmlWriter::getInstance();
        $objectAttribute = $model->getBindingMemberName();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-cell');
        
        $text = '';
        if ($itemIndex != -1)
        {
            $dataItem = $repeaterModel->getDataItems()->getItem($itemIndex);
            $text     = $dataItem->$objectAttribute;
        }
        
        if ($renderMode != self::RENDERMODE_NONE)
        {
            // determine whether or not to bind to the Model values
            $bindModelEditCondition = $renderMode === self::RENDERMODE_EDIT && ObjectIntrospector::isA($repeaterModel, 'EditableDataRepeater') && $repeaterModel->getMode() !== EditableDataRepeater::EDITMODE_START;
            $bindModelNewCondition  = $renderMode === self::RENDERMODE_NEW && ObjectIntrospector::isA($repeaterModel, 'EditableDataRepeater') && $repeaterModel->getMode() === EditableDataRepeater::EDITMODE_ADD;
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-field-container');

            $tw->openSelect();
            $tw->addAttribute('id', $objectAttribute);
            $tw->addAttribute('name', $objectAttribute);
            $tw->addAttribute('class', $view->getCssClass() . '-select');
            
            $currentOptgroup = '';
            $isOptgroupOpen  = false;
            
            foreach ($model->getOptions() as $option)
            {
                if ($currentOptgroup != $option['category'])
                {
                    if ($isOptgroupOpen === true)
                        $tw->closeOptgroup();
                    
                    if ($option['category'] != '')
                    {
                        $tw->openOptgroup();
                        $tw->addAttribute('label', $option['category']);
                        $isOptgroupOpen  = true;
                        $currentOptgroup = $option['category'];
                    }
                    else
                    {
                        $isOptgroupOpen  = false;
                        $currentOptgroup = $option['category'];
                    }
                }
                
                $tw->openOption();
                $tw->addAttribute('value', $option['value']);
                
                if ($bindModelEditCondition || $bindModelNewCondition)
                {
                    if ($option['value'] == $model->getValue())
                        $tw->addAttribute('selected', 'selected');
                }
                else
                {
                    if ($option['value'] == $text)
                        $tw->addAttribute('selected', 'selected');
                }
                
                $tw->writeContent($option['display'], false, false);
                $tw->closeOption();
            }
            
            if ($isOptgroupOpen === true)
                $tw->closeOptgroup();
            
            $tw->closeSelect();
            
            $tw->closeDiv();
        }
        else
        {
            foreach ($model->getOptions() as $option)
            {
                if ($option['value'] == $text)
                    $tw->writeContent($option['display'], false, false);
            }
        }
        
        $tw->closeTd();
    }
    
    /**
     * @param LabelRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     * @param string $renderMode
     */
    public static function renderLabelRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel, $renderMode = '')
    {
        $tw              = HtmlWriter::getInstance();
        $objectAttribute = $model->getBindingMemberName();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-cell');
        
        if ($itemIndex != -1)
        {
            $dataItem = $repeaterModel->getDataItems()->getItem($itemIndex);
            $text     = $dataItem->$objectAttribute;
        }
        
        $tw->writeContent($text, false, false, false);
        
        $tw->closeTd();
    }
    
    /**
     * @param TextBoxRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     * @param string $renderMode
     */
    public static function renderTextBoxRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel, $renderMode = '')
    {
        $tw              = HtmlWriter::getInstance();
        $objectAttribute = $model->getBindingMemberName();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-cell');
        
        $text = '';
        if ($itemIndex != -1)
        {
            $dataItem = $repeaterModel->getDataItems()->getItem($itemIndex);
            $text     = $dataItem->$objectAttribute;
        }
        
        if ($renderMode != self::RENDERMODE_NONE)
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-field-container');

            $tw->openInput();
            $tw->addAttribute('class', $view->getCssClass() . '-textfield');
            $tw->addAttribute('type', 'text');
            $tw->addAttribute('name', $objectAttribute);
            $tw->addAttribute('id', $objectAttribute);
            
            $valueAttribute = $text;
            
            // determine whether or not to bind to the Model values
            $bindModelEditCondition = $renderMode === self::RENDERMODE_EDIT && ObjectIntrospector::isA($repeaterModel, 'EditableDataRepeater') && $repeaterModel->getMode() !== EditableDataRepeater::EDITMODE_START;
            $bindModelNewCondition  = $renderMode === self::RENDERMODE_NEW && ObjectIntrospector::isA($repeaterModel, 'EditableDataRepeater') && $repeaterModel->getMode() === EditableDataRepeater::EDITMODE_ADD;
            
            if ($bindModelEditCondition || $bindModelNewCondition)
            {
                $valueAttribute = $model->getValue();
            }
            
            $tw->addAttribute('value', $valueAttribute);
            $tw->closeInput(true);
            
            $tw->closeDiv();
            
            self::renderFieldError($model, $view);
        }
        else
        {
            if (strlen($text) > 33) $text = substr($text, 0, 30) . '...';
            $tw->writeContent($text, false, false, false);
        }
        
        $tw->closeTd();
    }
    
    /**
     * @param CheckBoxRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     * @param string $renderMode
     */
    public static function renderCheckBoxRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel, $renderMode = '')
    {
        $tw              = HtmlWriter::getInstance();
        $objectAttribute = $model->getBindingMemberName();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-cell');
        
        $text = $model->getUncheckedValue();
        if ($itemIndex != -1)
        {
            $dataItem = $repeaterModel->getDataItems()->getItem($itemIndex);
            $text     = $dataItem->$objectAttribute;
        }
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-field-container');

        $tw->openInput();
        $tw->addAttribute('class', $view->getCssClass() . '-checkbox');
        $tw->addAttribute('type', 'checkbox');
        
        if ($renderMode == '')
        {
            $tw->addAttribute('disabled', 'disabled');
            if ($model->getCheckedValue() == $text)
                $tw->addAttribute('checked', 'checked');
        }
        else
        {
            // determine whether or not to bind to the Model values
            $bindModelEditCondition = $renderMode === self::RENDERMODE_EDIT && ObjectIntrospector::isA($repeaterModel, 'EditableDataRepeater') && $repeaterModel->getMode() !== EditableDataRepeater::EDITMODE_START;
            $bindModelNewCondition  = $renderMode === self::RENDERMODE_NEW && ObjectIntrospector::isA($repeaterModel, 'EditableDataRepeater') && $repeaterModel->getMode() === EditableDataRepeater::EDITMODE_ADD;
            
            $hiddenVal = $model->getUncheckedValue();
            if ($bindModelEditCondition || $bindModelNewCondition)
            {
                if ($model->getIsChecked())
                {
                    $tw->addAttribute('checked', 'checked');
                    $hiddenVal = $model->getCheckedValue();
                }
            }
            else
            {
                if ($model->getCheckedValue() == $text)
                {
                    $tw->addAttribute('checked', 'checked');
                    $hiddenVal = $model->getCheckedValue();
                }
            }
            
            $tw->addAttribute('name', $objectAttribute . '_ctrl');
            $tw->addAttribute('id', $objectAttribute . '_ctrl');
            $tw->addAttribute('value', $hiddenVal);
            $tw->addAttribute('onchange', "if (this.checked) { \$('" . $objectAttribute . "').set('value', '" . $model->getCheckedValue() . "'); } else { \$('" . $objectAttribute . "').set('value', '" . $model->getUncheckedValue() . "'); } ");
        }
        
        $tw->closeInput(true);
        
        if ($renderMode != '')
        {
            $tw->openInput();
            $tw->addAttribute('id', $objectAttribute);
            $tw->addAttribute('name', $objectAttribute);
            $tw->addAttribute('value', $hiddenVal);
            $tw->addAttribute('type', 'hidden');
            $tw->closeInput();
        }
        
        $tw->closeDiv();
        
        $tw->closeTd();
    }
    
    /**
     * @param LinkRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     */
    public static function renderLinkRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel)
    {
        $tw = HtmlWriter::getInstance();
        
        $dataItem        = $repeaterModel->getDataItems()->getItem($itemIndex);
        $objectAttribute = $model->getBindingMemberName();
        $urlAttribute = $model->getUrlBindingMemberName();
        $text            = $dataItem->$objectAttribute;
        $link            = $dataItem->$urlAttribute;
        
        $tw->openDiv();
        
        if (ObjectIntrospector::isA($repeaterModel, 'Accordion'))
            $tw->addAttribute('class', $view->getCssClass() . '-accordion-container ' . $objectAttribute . '-container');
        else
            $tw->addAttribute('class', $view->getCssClass() . '-item-container ' . $objectAttribute . '-container');
        
        $url = sprintf($model->getUrl(), $link);
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-item-caption ' . $objectAttribute . '-caption');
        $tw->writeContent('   ');
        $tw->closeDiv();
        
        $tw->openA();
        $tw->addAttribute('class', $view->getCssClass() . '-item-link ' . $objectAttribute . '-link');
        $tw->addAttribute('href', $url);
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-item-value ' . $objectAttribute . '-value');
        $tw->writeContent($text);
        $tw->closeDiv();
        
        $tw->closeA();
        
        self::renderDataItemBreaker($view);
        $tw->closeDiv();
    }
    
    /**
     * @param TextRepeaterField $model
     * @param $view
     * @param int $itemIndex
     * @param $repeaterModel
     */
    public static function renderTextRepeaterField(&$model, &$view, $itemIndex, &$repeaterModel)
    {
        $tw = HtmlWriter::getInstance();
        
        $dataItem        = $repeaterModel->getDataItems()->getItem($itemIndex);
        $objectAttribute = $model->getBindingMemberName();
        $text            = $dataItem->$objectAttribute;
        
        $tw->openDiv();
        
        if (ObjectIntrospector::isA($repeaterModel, 'Accordion'))
            $tw->addAttribute('class', $view->getCssClass() . '-accordion-container ' . $objectAttribute . '-container');
        else
            $tw->addAttribute('class', $view->getCssClass() . '-item-container ' . $objectAttribute . '-container');
        
        if ($model->getUrl() == '')
        {
            if ($model->getHasCaption())
            {
                $tw->openDiv();
                $tw->addAttribute('class', $view->getCssClass() . '-item-caption ' . $objectAttribute . '-caption');
                $tw->writeContent($model->getCaption());
                $tw->closeDiv();
            }
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-item-value ' . $objectAttribute . '-value');
            $tw->writeContent($text, false, false, false);
            $tw->closeDiv();
        }
        else
        {
            $url = sprintf($model->getUrl(), $text);
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-item-caption ' . $objectAttribute . '-caption');
            $tw->writeContent('   ');
            $tw->closeDiv();
            
            $tw->openA();
            $tw->addAttribute('class', $view->getCssClass() . '-item-link ' . $objectAttribute . '-link');
            $tw->addAttribute('href', $url);
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-item-value ' . $objectAttribute . '-value');
            $tw->writeContent($model->getCaption());
            $tw->closeDiv();
            
            $tw->closeA();
        }
        self::renderDataItemBreaker($view);
        $tw->closeDiv();
    }
    
    /**
     * Renders JScript initialization code
     *
     * @param DataRepeater $model
     * @param HtmlRepeaterView $view
     */
    protected static function renderRepeaterScript(&$model, &$view, $className = 'HtmlRepeaterView')
    {
        $tw = HtmlWriter::getInstance();
        
        parent::renderMessages($model, $view);
        
        self::renderDataRepeaterState($model, $view);
        self::renderPostBackFlag($model, $view);
        
        $tw->closeDiv();
        $tw->closeForm();
        
        $enableAsync = ($view->getIsAsynchronous() == true) ? 'true' : 'false';
        $javascript  = "var js_" . HtmlViewBase::getHtmlId($model) . " = null;
                    window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new $className('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', $enableAsync); });";
        self::renderInitializationScript($javascript);
    }
    
    /**
     * Renders the generic repeater form
     *
     * @param DataRepeater $model
     * @param HtmlRepeaterView $view
     * @param KeyedCollection $attributes additional attributes to render at the top-level
     */
    protected static function renderRepeaterForm(&$model, &$view, $attributes = null)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        $tw->addAttribute('method', 'post');
        
        if (!is_null($attributes))
        {
            foreach ($attributes->getKeys() as $attribName)
            {
                $tw->addAttribute($attribName, $attributes->getValue($attribName));
            }
        }
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();

        if(strlen($view->getDescriptionText())>0)
        {
            $tw->openDiv();
                $tw->addAttribute('class', $view->getMasterCssClass() . '-description');
                $tw->writeRaw(utf8_encode($view->getDescriptionText()));
            $tw->closeDiv();
        }
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-container');
        
        if ($model->getDataItems()->isEmpty())
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-nodata');
            $tw->openDiv();
            $tw->writeContent(Resources::getValue(Resources::SRK_REPEATER_PAGER_NORECORDS));
            $tw->closeDiv();
            $tw->closeDiv();
        }
    }
    
    /**
     * Renders the summary control editable repeater
     *
     * @param SummaryControl $model
     * @param HtmlRepeaterView $view
     */
    public static function renderSummaryControl(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTr();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-celltotal');
        $tw->writeContent('Total');
        $tw->closeTd();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-celltotal');
        $tw->writeContent($model->getValue());
        $tw->closeTd();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-celltotal');
        $tw->writeContent(' ');
        $tw->closeTd();
        
        $tw->closeTr();
    }
    
    /**
     * Renders the main editable repeater
     *
     * @param EditableDataRepeater $model
     * @param HtmlRepeaterView $view
     */
    public static function renderEditableDataRepeater(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $tw = HtmlWriter::getInstance();
        
        self::renderRepeaterForm($model, $view);
        
        $dataItems     = $model->getDataItems();
        $dataItemNames = $model->getRepeaterFieldNames(true);
        $summaries     = $model->getChildren()->getTypedControlNames(true, 'SummaryControl');
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-table');
        
        $tw->openTable();
        $tw->addAttribute('summary', '');
        
        $tw->openThead();
        $tw->openTr();
        
        foreach ($model->getRepeaterFieldNames(true) as $dataItemName)
        {
            $tw->openTd();
            $tw->addAttribute('class', $view->getCssClass() . '-header');
            
            $dataItem = $model->getRepeaterField($dataItemName);
            
            $tw->openDiv();
            $tw->writeContent($dataItem->getCaption());
            $tw->closeDiv();
            
            $tw->closeTd();
        }
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-header');
        
        $tw->openDiv();
        $tw->writeContent(" ");
        $tw->closeDiv();
        
        $tw->closeTd();
        
        $tw->closeTr();
        $tw->closeThead();
        $tw->openTBody();
        
        // Render the add new row
        if (is_null($model->getEditKey()) && $model->getAllowNew())
        {
            $tw->openTr();
            
            foreach ($model->getRepeaterFieldNames(true) as $dataItemName)
            {
                $dataItem = $model->getRepeaterField($dataItemName);
                $class    = $dataItem->getType()->getName();
                
                //if (ObjectIntrospector::isExtending($dataItem, 'EventDataItemBase') == false)
                    call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                        &$dataItem,
                        &$view,
                        -1,
                        &$model,
                        'new'
                    ));
            }
            
            $tw->openTd();
            $tw->openA();
            $tw->addAttribute('href', HtmlRenderCallbacks::HREF_NO_ACTION);
            $tw->addAttribute('name', EditableDataRepeater::EVENTNAME_ADD_ITEM);
            $tw->addAttribute('class', $view->getCssClass() . '-additem');
            $tw->addAttribute('title', Resources::getValue(Resources::SRK_CAPTION_ADD));
            $tw->writeContent("");
            $tw->closeA();
            $tw->closeTd();
            
            $tw->closeTr();
        }
        
        if ($dataItems->getCount() > 0 || count($summaries) > 0)
        {            
            for ($dataItemIndex = 0; $dataItemIndex < $dataItems->getCount(); $dataItemIndex++)
            {
                $tw->openTr();
                
                $dataItem        = $model->getDataItems()->getItem($dataItemIndex);
                $objectAttribute = $model->getKey();
                $id              = $dataItem->$objectAttribute;
                $renderMode      = ($id == $model->getEditKey()) ? self::RENDERMODE_EDIT : self::RENDERMODE_NONE;
                
                foreach ($model->getRepeaterFieldNames(true) as $dataItemName)
                {
                    $dataItem = $model->getRepeaterField($dataItemName);
                    $class    = $dataItem->getType()->getName();
                    
                    //if (ObjectIntrospector::isExtending($dataItem, 'EventDataItemBase') == false)
                        call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                            &$dataItem,
                            &$view,
                            $dataItemIndex,
                            &$model,
                            $renderMode
                        ));
                }
                
                $tw->openTd();
                
                $actions = new KeyedCollection();
                if ($renderMode === self::RENDERMODE_EDIT)
                {
                    $actions->setValue(EditableDataRepeater::EVENTNAME_SAVE_ITEM, 'saveitem');
                    $actions->setValue(EditableDataRepeater::EVENTNAME_CANCEL_ITEM, 'cancelitem');
                }
                else
                {
                    $actions->setValue(EditableDataRepeater::EVENTNAME_EDIT_ITEM, 'edititem');
                    if ($model->getAllowDelete())
                        $actions->setValue(EditableDataRepeater::EVENTNAME_DELETE_ITEM, 'deleteitem');
                }
                
                foreach ($actions as $key => $value)
                {
                    $tw->openA();
                    $tw->addAttribute('href', HtmlRenderCallbacks::HREF_NO_ACTION);
                    $tw->addAttribute('name', $key);
                    $tw->addAttribute('eventvalue', $id);
                    $tw->addAttribute('eventname', $key);
                    $tw->addAttribute('class', $view->getCssClass() . '-action ' . $view->getCssClass() . '-' . $value);
                    
                    switch ($key)
                    {
                        case EditableDataRepeater::EVENTNAME_ADD_ITEM:
                            $tw->addAttribute('title', Resources::getValue(Resources::SRK_CAPTION_ADD));
                            break;
                        case EditableDataRepeater::EVENTNAME_CANCEL_ITEM:
                            $tw->addAttribute('title', Resources::getValue(Resources::SRK_CAPTION_CANCEL));
                            break;
                        case EditableDataRepeater::EVENTNAME_DELETE_ITEM:
                            $tw->addAttribute('title', Resources::getValue(Resources::SRK_CAPTION_DELETE));
                            $tw->addAttribute('confirmmessage', Resources::getValue(Resources::SRK_CONFIRM_DELETE));
                            break;
                        case EditableDataRepeater::EVENTNAME_SAVE_ITEM:
                            $tw->addAttribute('title', Resources::getValue(Resources::SRK_CAPTION_SAVE));
                            break;
                        case EditableDataRepeater::EVENTNAME_EDIT_ITEM:
                            $tw->addAttribute('title', Resources::getValue(Resources::SRK_CAPTION_EDIT));
                            break;
                        default:
                            LogManager::logWarning("{$key} did not match and EditableDataRepeater EVENTNAME constant.");
                    }
                    
                    $tw->writeContent("");
                    $tw->closeA(true);
                }
                
                $tw->closeTd();
                $tw->closeTr();
            }
            
            foreach ($summaries as $summaryName)
            {
                self::renderSummaryControl($model->getChildren()->getControl($summaryName), $view);
            }
        }
        else
        {
            $tw->openTr();
            $tw->addAttribute('style', 'display: none; visibility: collapse;');
            foreach ($model->getRepeaterFieldNames(true) as $dataItemName)
            {
                $tw->openTd();
                $tw->closeTd(true);
            }
            $tw->closeTr();
        }
        $tw->closeTBody();
        $tw->closeTable();
        $tw->closeDiv();
        
        self::renderRepeaterScript($model, $view, 'HtmlEditableRepeaterView');
    }
    
    /**
     * Renders the main repeater
     *
     * @param DataRepeater $model
     * @param HtmlRepeaterView $view
     */
    public static function renderDataRepeater(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        
        $tw = HtmlWriter::getInstance();
        
        self::renderRepeaterForm($model, $view);
        
        $dataItems     = $model->getDataItems();
        $dataItemNames = $model->getRepeaterFieldNames(true);
        
        if (count($dataItemNames) == 0 && $dataItems->getCount() > 0)
        {
            $item = $dataItems->getItem(0);
            
            foreach ($item as $key => $value)
                $model->addRepeaterField(new TextRepeaterField($key, StringHelper::toUcFirst($key, true), $key));
        }
        
        for ($dataItemIndex = 0; $dataItemIndex < $dataItems->getCount(); $dataItemIndex++)
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-item');
            
            foreach ($model->getRepeaterFieldNames(true) as $dataItemName)
            {
                $dataItem = $model->getRepeaterField($dataItemName);
                $class    = $dataItem->getType()->getName();
                
                if (ObjectIntrospector::isExtending($dataItem, 'EventDataItemBase') == false)
                    call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                        &$dataItem,
                        &$view,
                        $dataItemIndex,
                        &$model,
                        false
                    ));
            }
            
            $buttonControls = $model->getChildren()->getControlNames(true, 'EventDataItemBase');
            
            if (count($buttonControls) > 0)
            {
                $tw->openDiv();
                $tw->addAttribute('class', $view->getCssClass() . '-item-buttonpanel');
                
                foreach ($buttonControls as $buttonName)
                {
                    $dataItem = $model->getRepeaterField($dataItemName);
                    $class    = $dataItem->getType()->getName();
                    
                    call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                        &$dataItem,
                        &$view,
                        $dataItemIndex,
                        &$model,
                        false
                    ));
                }
                
                $tw->closeDiv();
            }
            
            $tw->closeDiv();
        }
        
        if ($model->getIsPaged() && $model->getDataItems()->getCount() > 0)
            self::renderPager($model, $view);
        
        self::renderRepeaterScript($model, $view);
    }
    
    /**
     * Renders the main repeater as accordion
     *
     * @param DataRepeater $model
     * @param HtmlAccordionView $view
     */
    public static function renderAccordion(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        
        $tw = HtmlWriter::getInstance();
        
        $additionalAttribs = new KeyedCollection();
        $additionalAttribs->setValue('initialindex', $view->getInitialIndex());
        
        self::renderRepeaterForm($model, $view, $additionalAttribs);
        
        $dataItems     = $model->getDataItems();
        $dataItemNames = $model->getRepeaterFieldNames(true);
        
        if (count($dataItemNames) == 0 && $dataItems->getCount() > 0)
        {
            $item = $dataItems->getItem(0);
            
            foreach ($item as $key => $value)
                $model->addRepeaterField(new TextRepeaterField($key, StringHelper::toUcFirst($key), $key));
        }
        
        for ($dataItemIndex = 0; $dataItemIndex < $dataItems->getCount(); $dataItemIndex++)
        {
            $tw->openDiv();
            
            $tw->addAttribute('class', $view->getCssClass() . '-accordion');
            
            $dataItem = $dataItems->getItem($dataItemIndex);
            $titleKey = $view->getTitleKey();
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-accordion-title-inactive');
            $tw->writeContent($dataItem->$titleKey);
            $tw->closeDiv();
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-accordion-item');
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-dataitems');
            
            foreach ($model->getRepeaterFieldNames(true) as $dataItemName)
            {
                $dataItem = $model->getRepeaterField($dataItemName);
                $class    = $dataItem->getType()->getName();
                
                if (ObjectIntrospector::isExtending($dataItem, 'EventDataItemBase') == false)
                    call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                        &$dataItem,
                        &$view,
                        $dataItemIndex,
                        &$model,
                        false
                    ));
            }
            
            $tw->closeDiv();
            
            $buttonControls = $model->getChildren()->getControlNames(true, 'EventDataItemBase');
            
            if (count($buttonControls) > 0)
            {
                $tw->openDiv();
                $tw->addAttribute('class', $view->getCssClass() . '-item-buttonpanel');
                
                foreach ($buttonControls as $buttonName)
                {
                    $dataItem = $model->getRepeaterField($dataItemName);
                    $class    = $dataItem->getType()->getName();
                    
                    call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                        &$dataItem,
                        &$view,
                        $dataItemIndex,
                        &$model,
                        false
                    ));
                }
                
                $tw->closeDiv();
            }
            
            $tw->closeDiv();
            
            $tw->closeDiv();
        }
        
        if ($model->getIsPaged() && $model->getDataItems()->getCount() > 0)
            self::renderPager($model, $view);
        
        self::renderRepeaterScript($model, $view);
    }
    
    /**
     * Helper method to render the top pager
     *
     * @param DataRepeater $model
     * @param HtmlDataRepeaterView $view
     */
    public static function renderPager(&$model, &$view)
    {
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-pager');
        
        $tw->openTable();
        $tw->addAttribute('summary', '');
        $tw->openTr();
        
        $tw->openTd();
        self::renderPageFirst($model, $view);
        $tw->closeTd();
        
        $tw->openTd();
        self::renderPagePrevious($model, $view);
        $tw->closeTd();
        
        $tw->openTd();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-records');
        $tw->openSpan();
        
        $startRecord  = ($model->getState()->getPageIndex() * $model->getPageSize()) + 1;
        $endRecord    = min(($currentPage * $model->getPageSize()), $model->getState()->getTotalRecordCount());
        $totalRecords = $model->getState()->getTotalRecordCount();
        
        $tw->writeRaw(sprintf(Resources::getValue(Resources::SRK_REPEATER_PAGER_RECORDS), number_format($startRecord), number_format($endRecord), number_format($totalRecords)));
        $tw->closeSpan();
        $tw->closeTd();
        
        $tw->openTd();
        self::renderPageNext($model, $view);
        $tw->closeTd();
        
        $tw->openTd();
        self::renderPageLast($model, $view);
        $tw->closeTd();
        
        $tw->closeTr();
        $tw->closeTable();
        
        $tw->closeDiv();
    }
}
?>