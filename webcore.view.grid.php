<?php
/**
 * @package WebCore
 * @subpackage View
 * @version 1.0
 * 
 * Provides views of controls in a data grid.
 *
 * @todo Selectable rows (checkboxes?, gridstate?, action buttons?)
 * @todo GridExporters are not setting additional execution time.
 * @todo Improve on the Grid search dialogs
 * @todo Grid search dialogs not showing error message or empty views when filtering results in 0 records.
 * @todo Export to PDF <-- we really need this?!
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.view.form.php";

/**
 * Provides a standard Grid object renderer.
 * Event managers and columns are rendered by using callbacks.
 * Requires std.gridview.js and std.gridview.css
 * Add, remove or modify callbacks using the renderCallbacks KeyedCollection
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlGridView extends HtmlViewBase
{
    protected $searchDialogCssClass;
    protected $searchDialogFramWidth;
    
    /**
     * @todo Doc
     */
    public function getSearchDialogCssClass()
    {
        return $this->searchDialogCssClass;
    }
    
    /**
     * @todo Doc
     */
    public function getSearchDialogFrameWidth()
    {
        return $this->searchDialogFramWidth;
    }
    
    /**
     * Creates a new instance of this class based on a grid model
     *
     * @param Grid $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass              = 'gridview';
        $this->searchDialogCssClass  = 'formview';
        $this->searchDialogFramWidth = '500px';
        $callbacks =& $this->renderCallbacks->getArrayReference();
        $this->isAsynchronous = true;
        $this->frameWidth     = "auto";
        
        // Setup the callbacks for each renderable model
        $callbacks['Grid']                     = array(
            'HtmlGridRenderCallbacks',
            'renderGrid'
        );
        $callbacks['TextBoundGridColumn']      = array(
            'HtmlGridRenderCallbacks',
            'renderTextBoundGridColumn'
        );
        $callbacks['DateTimeBoundGridColumn']  = array(
            'HtmlGridRenderCallbacks',
            'renderDateTimeBoundGridColumn'
        );
        $callbacks['NumberBoundGridColumn']    = array(
            'HtmlGridRenderCallbacks',
            'renderNumberBoundGridColumn'
        );
        $callbacks['MoneyBoundGridColumn']     = array(
            'HtmlGridRenderCallbacks',
            'renderMoneyBoundGridColumn'
        );
        
        $callbacks['CheckBoxGridColumn']     = array(
            'HtmlGridRenderCallbacks',
            'renderCheckBoxGridColumn'
        );
        
        $callbacks['RowCommandGridColumn']     = array(
            'HtmlGridRenderCallbacks',
            'renderRowCommandGridColumn'
        );
        $callbacks['DetailsCommandGridColumn'] = array(
            'HtmlGridRenderCallbacks',
            'renderDetailsCommandGridColumn'
        );
        $callbacks['EditCommandGridColumn']    = array(
            'HtmlGridRenderCallbacks',
            'renderEditCommandGridColumn'
        );
        $callbacks['DeleteCommandGridColumn']  = array(
            'HtmlGridRenderCallbacks',
            'renderDeleteCommandGridColumn'
        );
        $callbacks['SelectCommandGridColumn']  = array(
            'HtmlGridRenderCallbacks',
            'renderSelectCommandGridColumn'
        );
        
        $callbacks['GroupingColumn']               = array(
            'HtmlGridRenderCallbacks',
            'renderGroupingColumn'
        );
        $callbacks['GridPrintEventManager']        = array(
            'HtmlGridRenderCallbacks',
            'renderGridPrintEventManager'
        );
        $callbacks['GridCsvExporterEventManager'] = array(
            'HtmlGridRenderCallbacks',
            'renderGridExporterEventManager'
        );
        $callbacks['GridBiffExporterEventManager'] = array(
            'HtmlGridRenderCallbacks',
            'renderGridExporterEventManager'
        );
        $callbacks['GridOxmlExporterEventManager'] = array(
            'HtmlGridRenderCallbacks',
            'renderGridExporterEventManager'
        );
        
        $callbacks['DateGridSearchDialog']   = array(
            'HtmlGridRenderCallbacks',
            'renderGridSearchDialog'
        );
        $callbacks['TextGridSearchDialog']   = array(
            'HtmlGridRenderCallbacks',
            'renderGridSearchDialog'
        );
        $callbacks['NumberGridSearchDialog'] = array(
            'HtmlGridRenderCallbacks',
            'renderGridSearchDialog'
        );
        
        $callbacks['FormSection']  = array(
            'HtmlFormRenderCallbacks',
            'renderFormSection'
        );
        $callbacks['TextField']    = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['MoneyField']   = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['IntegerField'] = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['DecimalField'] = array(
            'HtmlFormRenderCallbacks',
            'renderTextField'
        );
        $callbacks['Button']       = array(
            'HtmlFormRenderCallbacks',
            'renderButton'
        );
        $callbacks['Persistor']    = array(
            'HtmlFormRenderCallbacks',
            'renderPersistor'
        );
        $callbacks['CheckBox']     = array(
            'HtmlFormRenderCallbacks',
            'renderCheckBox'
        );
        $callbacks['ComboBox']     = array(
            'HtmlFormRenderCallbacks',
            'renderComboBox'
        );
        $callbacks['DateField']    = array(
            'HtmlFormRenderCallbacks',
            'renderDateField'
        );
        $callbacks['LabelField']   = array(
            'HtmlFormRenderCallbacks',
            'renderLabelField'
        );
        $callbacks['TextBlock']    = array(
            'HtmlFormRenderCallbacks',
            'renderTextBlock'
        );
        
        // Register the html dependencies with the HtmlViewManager
        $this->registerDependencies();
    }
    
    /**
     * @todo Doc
     */
    public function getSearchDialogs()
    {
        $retVal = new IndexedCollection();
        foreach ($this->model->getChildren()->getTypedControlNames(true, 'BoundGridColumnBase') as $columnName)
        {
            /**
             * @var BoundGridColumn
             */
            $column = $this->model->getColumn($columnName);
            if ($column->getIsSearchable() === true)
            {
                $searchDialog = $column->getSearchDialog();
                $retVal->addItem($searchDialog);
            }
        }
        return $retVal;
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     *
     */
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        $rootPath = HttpContext::getLibraryRoot();
        
        $repeaterCssPath = $rootPath . 'css/std.repeaterbaseview.css';
        $gridviewPath    = $rootPath . 'js/std.gridview.js';
        $cssPath         = $rootPath . 'css/std.gridview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlRepeaterBaseView.Css', $repeaterCssPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlGridView.Js', $gridviewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlGridView.Css', $cssPath);
        
        foreach ($this->getSearchDialogs() as $dialog)
            self::registerCommonFormsDependecies($dialog);
    }
}

/**
 * Contains static callback methods to render standard framework HTML controls in a Grid
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlGridRenderCallbacks extends HtmlRepeaterRenderCallbacksBase
{
    /**
     * Renders the full grid model and all of its children
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    public static function renderGrid(&$model, &$view)
    {
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        
        $tw = HtmlWriter::getInstance();
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        
        self::renderGridCaption($model, $view);
        self::renderTopToolbar($model, $view);
        
        // The data
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-container');
        if ($model->getDataItems()->getCount() > 0)
        {
            $tw->openTable();
            $tw->addAttribute('summary', '');
            self::renderColumnHeaders($model, $view);
            self::renderDataItems($model, $view);
            $tw->closeTable();
        }
        
        // The hidden fields
        self::renderDataRepeaterState($model, $view);
        self::renderPostBackFlag($model, $view);
        $tw->closeDiv();
        
        self::renderBottomToolbar($model, $view);
        
        parent::renderMessages($model, $view);
        $tw->closeForm();
        
        self::renderJavaScriptAndSearchDialogs($model, $view);
    }
    
    /**
     * Helper method to render the grid's caption
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderGridCaption(&$model, &$view)
    {
        if ($model->getCaption() == '') return;
        
        $tw = HtmlWriter::getInstance();
        
        // The caption
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();
    }
    
    /**
     * Helper method to render the grid's top toolbar
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderTopToolbar(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        // The top toolbar
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar');
        $tw->addAttribute('style', 'border-bottom-style:none;');
        
        $tw->openTable();
        $tw->addAttribute('summary', '');
        
        $tw->openTr();
        $tw->openTd();
        
        foreach ($model->getToolbar()->getChildren() as $currentControl)
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
                        &$view
                    ));
                    
                    $tw->openSpan();
                    $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-split');
                    $tw->closeSpan(true);
                    continue;
                }
            }
            
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "Invalid render callback for model of type '" . $controlClassName . "'");
        }
        
        self::renderToolbarButtons($model, $view);
        
        $tw->closeTd();
        
        self::renderPagerTop($model, $view);
        
        $tw->closeTr();
        
        $tw->closeTable();
        
        $tw->closeDiv();
    }
    
    /**
     * Helper method to render the grid's bottom toolbar
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderBottomToolbar(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        // The bottom toolbar
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar');
        $tw->addAttribute('style', 'border-top-style:none;');
        $tw->openTable();
        $tw->addAttribute('summary', '');
        $tw->openTr();
        $totalRecords = $model->getState()->getTotalRecordCount();
        if ($totalRecords > 0)
        {
            self::renderPagerBottom($model, $view);
            $tw->openTd();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-records');
            $tw->openSpan();
            $startRecord = ($model->getState()->getPageIndex() * $model->getPageSize()) + 1;
            $endRecord   = min((($model->getState()->getPageIndex() + 1) * $model->getPageSize()), $model->getState()->getTotalRecordCount());
            $tw->writeRaw(sprintf(Resources::getValue(Resources::SRK_REPEATER_PAGER_RECORDS), number_format($startRecord), number_format($endRecord), number_format($totalRecords)));
            $tw->closeSpan();
            $tw->closeTd();
        }
        else
        {
            $tw->openTd();
            $tw->addAttribute('colspan', '2');
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-nodata');
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-pager-nodata');
            $tw->openDiv();
            $tw->writeContent(Resources::getValue(Resources::SRK_REPEATER_PAGER_NORECORDS));
            $tw->closeDiv();
            $tw->closeDiv();
            
            $tw->closeTd(true);
        }
        $tw->closeTr();
        $tw->closeTable();
        $tw->closeDiv();
    }
    
    /**
     * Helper method to render the grid's column headers
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderColumnHeaders(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openThead();
        $tw->openTr();
        $columnIndex = 0;
        foreach ($model->getColumnNames(true) as $columnName)
        {
            /**
             * @var BoundGridColumnBase
             */
            $column = $model->getColumn($columnName);
            if ($column->getVisible() !== false)
            {
                if (ObjectIntrospector::isA($column, 'GroupingColumn') === false)
                {
                    call_user_func_array($view->getRenderCallbacks()->getValue($column->getType()->getName()), array(
                        &$column,
                        &$view,
                        $columnIndex,
                        &$model,
                        true
                    ));
                }
            }
            $columnIndex++;
        }
        $tw->closeTr();
        $tw->closeThead();
    }
    
    /**
     * Helper method to render the grid's data items
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderDataItems(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openTbody();
        $dataItems           = $model->getDataItems();
        $groupingColumnNames = $model->getChildren()->getTypedControlNames(true, 'GroupingColumn');
        
        for ($dataItemIndex = 0; $dataItemIndex < $dataItems->getCount(); $dataItemIndex++)
        {
            $groupChanged = false; // Defines whether a chenge in data has been found for any of the grouping columns
            
            if ($dataItemIndex === 0)
                $groupChanged = true; // force the rendering of grouping columns
            if ($dataItemIndex > 0)
            {
                // Look for a change in data on any of the grouping columns
                foreach ($groupingColumnNames as $groupingColName)
                {
                    /**
                     * @var GroupingColumn
                     */
                    $groupingCol = $model->getColumn($groupingColName);
                    if ($groupingCol->getVisible() === false)
                        continue;
                    $currentDataItem   = $model->getDataItems()->getItem($dataItemIndex);
                    $lastDataItem      = $model->getDataItems()->getItem($dataItemIndex - 1);
                    $dataItemAttribute = $groupingCol->getBindingMemberName();
                    $currentValue      = $currentDataItem->$dataItemAttribute;
                    $lastValue         = $lastDataItem->$dataItemAttribute;
                    if ($currentValue !== $lastValue)
                    {
                        $groupChanged = true;
                        break;
                    }
                }
            }
            
            // If a change in data on any of the grouping columns occurs, it's time to render the grouping columns.
            if ($groupChanged === true && count($groupingColumnNames) > 0)
            {
                // calculate the column span (somewhat expensive, yet necessary)
                $colspan = 0;
                foreach ($model->getColumnNames(true) as $columnName)
                {
                    /**
                     * @var BoundGridColumnBase
                     */
                    $column = $model->getColumn($columnName);
                    
                    if ($column->getVisible() !== false && !in_array($columnName, $groupingColumnNames))
                    {
                        $colspan += 1;
                    }
                }
                
                // Render the grouping columns
                $tw->openTr();
                $tw->addAttribute('class', 'grouping');
                $tw->openTd();
                $tw->addAttribute('colspan', $colspan);
                
                foreach ($groupingColumnNames as $groupingColName)
                {
                    $groupingCol = $model->getColumn($groupingColName);
                    call_user_func_array($view->getRenderCallbacks()->getValue($groupingCol->getType()->getName()), array(
                        &$groupingCol,
                        &$view,
                        $dataItemIndex,
                        &$model,
                        false
                    ));
                }
                
                $tw->closeTd();
                $tw->closeTr();
            }
            
            $tw->openTr();
            foreach ($model->getColumnNames(true) as $columnName)
            {
                /**
                 * @var BoundGridColumnBase
                 */
                $column = $model->getColumn($columnName);
                if ($column->getVisible() !== false)
                {
                    if (ObjectIntrospector::isA($column, 'GroupingColumn') === false)
                    {
                        call_user_func_array($view->getRenderCallbacks()->getValue($column->getType()->getName()), array(
                            &$column,
                            &$view,
                            $dataItemIndex,
                            &$model,
                            false
                        ));
                    }
                }
            }
            $tw->closeTr();
        }
        $tw->closeTbody();
    }
    
    /**
     * Helper method to render the grid's data items
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderJavaScriptAndSearchDialogs(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        // Instantiation script
        $enableAsync = ($view->getIsAsynchronous() == true) ? 'true' : 'false';
        $javascript  = "var js_" . HtmlViewBase::getHtmlId($model) . " = null;

                    window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new HtmlGridView('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', $enableAsync);";
        
        $dialogs = $view->getSearchDialogs();
        
        foreach ($dialogs as $dialog)
        {
            if ($dialog->hasErrorMessage() === true)
            {
                $javascript .= " js_" . HtmlViewBase::getHtmlId($model) . ".showOverlay();";
                break;
            }
        }
        $javascript .= "});";
        self::renderInitializationScript($javascript);
        
        foreach ($dialogs as $dialog)
        {
            $class = get_class($dialog);
            call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                &$dialog,
                &$view
            ));
        }
    }
    
    /**
     * @param BoundGridColumnBase $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    static protected function renderBoundGridColumnHeader(&$model, &$view, $itemIndex, &$gridModel)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTd();
        if ($model->getIsSortable())
        {
            if ($model->getName() == $gridModel->getState()->getSortColumnName())
            {
                if ($gridModel->getState()->getSortDirection() == GridState::GRID_SORT_ASCENDING)
                {
                    $tw->addAttribute('eventname', 'SortByColumn');
                    $tw->addAttribute('eventvalue', $model->getName() . "|" . GridState::GRID_SORT_DESCENDING);
                    $tw->addAttribute('sortdirection', 'asc');
                }
                else
                {
                    $tw->addAttribute('eventname', 'SortByColumn');
                    $tw->addAttribute('eventvalue', $model->getName() . "|" . GridState::GRID_SORT_ASCENDING);
                    $tw->addAttribute('sortdirection', 'desc');
                }
            }
            else
            {
                $tw->addAttribute('eventname', 'SortByColumn');
                $tw->addAttribute('eventvalue', $model->getName() . "|" . GridState::GRID_SORT_ASCENDING);
            }
        }
        $tw->addAttribute('class', $view->getCssClass() . '-header-off');
        
        $tw->writeDiv($model->getCaption());
        $tw->closeTd();
    }
    
    /**
     * Helper method to standardize the rendeiring of column content.
     * 
     * @param string $text
     */
    static protected function renderBoundGridColumnContent($text, $tooltip = '')
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTd();
        $tw->openDiv();
        
        if ($tooltip !== '')
            $tw->addAttribute('title', $tooltip);
        
        $tw->writeContent($text);
        $tw->closeDiv();
        $tw->closeTd();
    }
    
    /**
     * @param BoundGridColumnBase $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderBoundGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
        {
            self::renderBoundGridColumnHeader($model, $view, $itemIndex, $gridModel);
        }
        else // It's the content
        {
            $dataItem        = $gridModel->getDataItems()->getItem($itemIndex);
            $objectAttribute = $model->getBindingMemberName();
            $text            = $dataItem->$objectAttribute;
            
            self::renderBoundGridColumnContent($text);
        }
    }
    
    public static function renderCheckBoxGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openTd();
        
        if ($isHeader)
        {
            $tw->addAttribute('class', $view->getCssClass() . '-header-off');
            $tw->writeDiv(" ");
        }
        else // It's the content
        {
            $dataItem        = $gridModel->getDataItems()->getItem($itemIndex);
            $objectAttribute = $model->getBindingMemberName();
            $text            = $dataItem->$objectAttribute;
            
            $tw->openDiv();
            
            $tw->openInput();
            $tw->addAttribute('class', 'checkboxgrid');
            $tw->addAttribute('type', 'checkbox');
            $tw->addAttribute('value', $text);
            $tw->closeInput();
            
            $tw->closeDiv();
        }
        
        $tw->closeTd();
    }
    
    /**
     * Renders a DateTimeBoundGridColumn
     * 
     * @param DateTimeBoundGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderDateTimeBoundGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
        {
            self::renderBoundGridColumnHeader($model, $view, $itemIndex, $gridModel);
        }
        else // It's the content
        {
            $dataItem        = $gridModel->getDataItems()->getItem($itemIndex);
            $objectAttribute = $model->getBindingMemberName();
            $time            = strtotime($dataItem->$objectAttribute);
            $text            = date($model->getDateFormat(), $time);
            self::renderBoundGridColumnContent($text);
        }
    }
    
    /**
     * Renders a NumberBoundGridColumn
     * 
     * @param NumberBoundGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderNumberBoundGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
        {
            self::renderBoundGridColumnHeader($model, $view, $itemIndex, $gridModel);
        }
        else // It's the content
        {
            $dataItem        = $gridModel->getDataItems()->getItem($itemIndex);
            $objectAttribute = $model->getBindingMemberName();
            $text            = number_format($dataItem->$objectAttribute);
            self::renderBoundGridColumnContent($text);
        }
    }
    
    /**
     * Renders a Symbolic GroupingColumn
     * 
     * @param GroupingColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderGroupingColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        $tw = HtmlWriter::getInstance();
        
        $dataItem        = $gridModel->getDataItems()->getItem($itemIndex);
        $objectAttribute = $model->getBindingMemberName();
        $tw->openSpan();
        
        if ($model->getCaption() != '')
            $tw->writeContent($model->getCaption() . ': ');
        
        $tw->writeContent($dataItem->$objectAttribute);
        $tw->closeSpan();
    }
    
    /**
     * Renders a BoundGridColumn to display Currency Amounts
     * 
     * @param MoneyBoundGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderMoneyBoundGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
        {
            self::renderBoundGridColumnHeader($model, $view, $itemIndex, $gridModel);
        }
        else // It's the content
        {
            $dataItem        = $gridModel->getDataItems()->getItem($itemIndex);
            $objectAttribute = $model->getbindingMemberName();
            $text            = '$' . number_format($dataItem->$objectAttribute, 2);
            self::renderBoundGridColumnContent($text);
        }
    }
    
    /**
     * Renders a simple TextBoundGridColumn
     * 
     * @param TextBoundGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderTextBoundGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
        {
            self::renderBoundGridColumnHeader($model, $view, $itemIndex, $gridModel);
        }
        else // It's the content
        {
            $dataItem        = $gridModel->getDataItems()->getItem($itemIndex);
            $objectAttribute = $model->getbindingMemberName();
            $text            = $dataItem->$objectAttribute;
            $tooltip         = $text;
            if (strlen($text) > $model->getMaxChars())
            {
                $text = substr($text, 0, $model->getMaxChars() - 3);
                $text = trim($text);
                $text .= "...";
            }
            self::renderBoundGridColumnContent($text, $tooltip);
        }
    }
    
    /**
     * @todo Doc
     */
    static protected function renderPredefinedCommandGridColumnHeader(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-rowcommand-header');
        $tw->writeDiv($model->getCaption());
        $tw->closeTd();
    }
    
    /**
     * @todo Doc
     */
    static protected function renderPredefinedCommandGridColumnIcon(&$model, &$view, $itemIndex, &$gridModel, $isHeader, $iconClass)
    {
        $tw               = HtmlWriter::getInstance();
        $dataItem         = $gridModel->getDataItems()->getItem($itemIndex);
        $eventValueMember = $model->getBindingMemberEventValue();
        $eventValue       = $dataItem->$eventValueMember;
        $captionMember    = $model->getBindingMemberCommandCaption();
        
        $tw->openTd();
        $tw->addAttribute('class', 'iconic');
        $tw->openDiv();
        $tw->openA();
        $tw->addAttribute('class', 'rowcommand iconic ' . $iconClass);
        $tw->addAttribute('eventname', $model->getEventName());
        $tw->addAttribute('eventvalue', $eventValue);
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        $tw->addAttribute('title', $model->getCommandCaption());
        if (ObjectIntrospector::isClass($model, 'DeleteCommandGridColumn'))
            $tw->addAttribute('confirmmessage', Resources::getValue(Resources::SRK_CONFIRM_DELETE));
        $tw->writeContent('');
        $tw->closeA();
        $tw->closeDiv();
        $tw->closeTd();
    }
    
    /**
     * Renders a RowCommandGridColumn
     * 
     * @param RowCommandGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderDetailsCommandGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
            self::renderPredefinedCommandGridColumnHeader($model, $view);
        else // It's the content
            self::renderPredefinedCommandGridColumnIcon($model, $view, $itemIndex, $gridModel, $isHeader, 'iconic-details');
    }
    
    /**
     * Renders a RowCommandGridColumn
     * 
     * @param RowCommandGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderEditCommandGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
            self::renderPredefinedCommandGridColumnHeader($model, $view);
        else // It's the content
            self::renderPredefinedCommandGridColumnIcon($model, $view, $itemIndex, $gridModel, $isHeader, 'iconic-edit');
    }
    
    /**
     * Renders a RowCommandGridColumn
     * 
     * @param RowCommandGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderDeleteCommandGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
            self::renderPredefinedCommandGridColumnHeader($model, $view);
        else // It's the content
            self::renderPredefinedCommandGridColumnIcon($model, $view, $itemIndex, $gridModel, $isHeader, 'iconic-delete');
    }
    
    /**
     * Renders a RowCommandGridColumn
     * 
     * @param RowCommandGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderSelectCommandGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        if ($isHeader)
            self::renderPredefinedCommandGridColumnHeader($model, $view);
        else // It's the content
            self::renderPredefinedCommandGridColumnIcon($model, $view, $itemIndex, $gridModel, $isHeader, 'iconic-select');
    }
    
    /**
     * Renders a RowCommandGridColumn
     * 
     * @param RowCommandGridColumn $model
     * @param HtmlGridView $view
     * @param int $itemIndex
     * @param Grid $gridModel
     */
    public static function renderRowCommandGridColumn(&$model, &$view, $itemIndex, &$gridModel, $isHeader)
    {
        $tw = HtmlWriter::getInstance();
        
        if ($isHeader)
        {
            $tw->openTd();
            $tw->addAttribute('class', $view->getCssClass() . '-header-off');
            $tw->writeDiv($model->getCaption());
            $tw->closeTd();
        }
        else // It's the content
        {
            $dataItem         = $gridModel->getDataItems()->getItem($itemIndex);
            $eventValueMember = $model->getBindingMemberEventValue();
            $eventValue       = $dataItem->$eventValueMember;
            $captionMember    = $model->getBindingMemberCommandCaption();
            
            $tw->openTd();
            $tw->openDiv();
            $tw->openA();
            $tw->addAttribute('class', 'rowcommand');
            $tw->addAttribute('eventname', $model->getEventName());
            $tw->addAttribute('eventvalue', $eventValue);
            $tw->addAttribute('name', $model->getName());
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            if ($captionMember === '')
            {
                $tw->writeContent($model->getCommandCaption());
            }
            else
            {
                $tw->writeContent($dataItem->$captionMember);
            }
            $tw->closeA();
            $tw->closeDiv();
            $tw->closeTd();
        }
    }
    
    /**
     * Helper method to render the Toolbar's Search Manager
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderGridSearchManager(&$model, &$view)
    {
        $colNames           = $model->getColumnNames(true);
        $searchableColNames = new IndexedCollection();
        $tw                 = HtmlWriter::getInstance();
        
        foreach ($colNames as $colName)
        {
            /**
             * @var BoundGridColumnBase
             */
            $columnModel = $model->getColumn($colName);
            
            if (ObjectIntrospector::isA($columnModel, 'BoundGridColumnBase'))
            {
                if ($columnModel->getIsSearchable() === true)
                {
                    $searchableColName = $columnModel->getName();
                    $searchableColNames->addItem($searchableColName);
                }
            }
        }
        
        if ($searchableColNames->getCount() > 0)
        {
            /**
             * @var GridState
             */
            $gridState =& $model->getState();
            
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-button-menu ' . $view->getCssClass() . '-toolbar-search');
            $tw->openSpan();
            $tw->closeSpan(true);
            $tw->writeContent(Resources::getValue(Resources::SRK_GRID_TOOLBAR_SEARCH));
            $tw->closeA();
            
            // The no-filter option
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . "-toolbar-buttonmenu");
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            if ($gridState->getSearchColumnName() == '~')
            {
                $tw->addAttribute('class', 'menubutton-item-checked');
            }
            else
            {
                $tw->addAttribute('class', 'menubutton-item');
            }
            $tw->addAttribute('eventname', 'SearchByColumn');
            $tw->addAttribute('eventvalue', '~');
            $tw->writeContent(Resources::getValue(Resources::SRK_GRID_NOSEARCH));
            $tw->closeA();
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            $tw->addAttribute('class', 'menubutton-splitter');
            $tw->writeContent(' ');
            $tw->closeA();
            
            // The search by column options
            foreach ($colNames as $colName)
            {
                $item = $model->getColumn($colName);
                if (ObjectIntrospector::isA($item, 'BoundGridColumnBase'))
                {
                    if ($item->getIsSearchable() === true)
                    {
                        $tw->openA();
                        $tw->addAttribute('href', self::HREF_NO_ACTION);
                        if ($gridState->getSearchColumnName() == $item->getName())
                        {
                            $tw->addAttribute('class', 'menubutton-item-checked');
                        }
                        else
                        {
                            $tw->addAttribute('class', 'menubutton-item');
                        }
                        $tw->addAttribute('eventname', 'SearchByColumn');
                        $tw->addAttribute('eventvalue', $item->getName());
                        $tw->addAttribute('searchdialog', HtmlViewBase::getHtmlId($item->getSearchDialog()));
                        $tw->writeContent($item->getCaption());
                        $tw->closeA();
                    }
                }
            }
            $tw->closeDiv();
        }
    }
    
    /**
     * Helper method to render the GridFilteringManager
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderGridFilteringManager(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        if ($model->getFilteringManager()->getFilterExpressions()->getCount() > 0)
        {
            // The toolbar separator and button
            $tw->openSpan();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-split');
            $tw->closeSpan(true);
            
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-button-menu ' . $view->getCssClass() . '-toolbar-filter');
            $tw->openSpan();
            $tw->closeSpan(true);
            $tw->writeContent(Resources::getValue(Resources::SRK_GRID_TOOLBAR_FILTER));
            $tw->closeA();
            
            // The no-filter option
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . "-toolbar-buttonmenu");
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            
            if ($model->getState()->getFilterExpressionIndex() == -1)
                $tw->addAttribute('class', 'menubutton-item-checked');
            else
                $tw->addAttribute('class', 'menubutton-item');
            
            $tw->addAttribute('custommanager', $model->getName() + 'Filterer');
            $tw->addAttribute('eventname', 'ApplyFilter');
            $tw->addAttribute('eventvalue', '-1');
            $tw->writeContent(Resources::getValue(Resources::SRK_GRID_NOFILTER));
            $tw->closeA();
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            $tw->addAttribute('class', 'menubutton-splitter');
            $tw->writeContent(' ');
            $tw->closeA();
            
            // The filtering options
            $filterIndex = 0;
            foreach ($model->getFilteringManager()->getFilterExpressions() as $item)
            {
                $tw->openA();
                $tw->addAttribute('href', self::HREF_NO_ACTION);
                
                if ($model->getState()->getFilterExpressionIndex() == $filterIndex)
                    $tw->addAttribute('class', 'menubutton-item-checked');
                else
                    $tw->addAttribute('class', 'menubutton-item');
                
                $tw->addAttribute('eventname', 'ApplyFilter');
                $tw->addAttribute('eventvalue', $filterIndex);
                $tw->writeContent($item->getKey());
                $tw->closeA();
                $filterIndex++;
            }
            
            $tw->closeDiv();
        }
    }
    
    /**
     * Helper method to render the Toolbar's Buttons
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderToolbarButtons(&$model, &$view)
    {
        // if ($model->getState()->getTotalRecordCount() <= 0) return; // Do not uncomment this line. searches with no results caan't remove search and quick filters otherwise
        $tw = HtmlWriter::getInstance();
        
        self::renderGridSearchManager($model, $view);
        self::renderGridFilteringManager($model, $view);
        
        $customEventManagerControlNames = $model->getChildren()->getTypedControlNames(true, 'GridCustomEventManagerBase');
        
        if (count($customEventManagerControlNames) > 0)
        {
            // The toolbar separator and button
            $tw->openSpan();
            $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-split');
            $tw->closeSpan(true);
            
            $tw->openA();
            $tw->addAttribute('href', self::HREF_NO_ACTION);
            $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-button-menu ' . $view->getCssClass() . '-toolbar-tools');
            $tw->openSpan();
            $tw->closeSpan(true);
            $tw->writeContent(Resources::getValue(Resources::SRK_GRID_TOOLBAR_TOOLS));
            $tw->closeA();
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getMasterCssClass() . "-toolbar-buttonmenu");
            
            foreach ($customEventManagerControlNames as $customManagerName)
            {
                /**
                 * @var GridCustomEventManagerBase
                 */
                $customManager = $model->getChildren()->getControl($customManagerName, true);
                $class         = $customManager->getType()->getName();
                call_user_func_array($view->getRenderCallbacks()->getValue($class), array(
                    &$customManager,
                    &$view
                ));
            }
            
            $tw->closeDiv();
        }
    }
    
    /**
     * Renders the GridPrintEventManager
     *
     * @param GridPrintEventManager $model
     * @param HtmlGridView $view
     */
    public static function renderGridPrintEventManager($model, $view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        $tw->addAttribute('class', 'menubutton-item');
        $tw->addAttribute('eventname', $model->getEventName());
        $tw->addAttribute('eventvalue', $model->getEventValue());
        $tw->addAttribute('custommanager', $model->getName());
        $tw->addAttribute('id', $view->getModel()->getName() . '_custom_' . $model->getName());
        $tw->addAttribute('windowprops', 'location=no,menubar=no,scrollbars=yes,titlebar=no,toolbar=no,resizable=yes,height=480,width=640,directories=no');
        $tw->writeContent($model->getCaption());
        $tw->closeA();
    }
    
    /**
     * Renders the GridExporterEventManagerBase-derived instances
     *
     * @param GridExporterEventManagerBase $model
     * @param HtmlGridView $view
     */
    public static function renderGridExporterEventManager($model, $view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openA();
        $tw->addAttribute('href', self::HREF_NO_ACTION);
        $tw->addAttribute('class', 'menubutton-item');
        $tw->addAttribute('eventname', $model->getEventName());
        $tw->addAttribute('eventvalue', $model->getEventValue());
        $tw->addAttribute('custommanager', $model->getName());
        $tw->addAttribute('id', $view->getModel()->getName() . '_custom_' . $model->getName());
        $tw->addAttribute('windowprops', 'windowprops'); // Do not create a managed popup, just a target=_blank
        $tw->writeContent($model->getCaption());
        $tw->closeA();
    }
    
    /**
     * Helper method to render paging button splitter
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderPagerSplitter(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openSpan();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-toolbar-split');
        $tw->closeSpan(true);
    }
    
    /**
     * Helper method to render the top pager
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderPagerTop(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        // The pager
        $tw->openTd();
        $tw->addAttribute('class', $view->getCssClass() . '-pager-top');
        
        if ($model->getState()->getTotalRecordCount() > 0 && $model->getIsPaged())
        {
            self::renderPageLast($model, $view);
            self::renderPageNext($model, $view);
            self::renderPagerSplitter($model, $view);
            self::renderPagerDisplay($model, $view);
            self::renderPagerSplitter($model, $view);
            self::renderPagePrevious($model, $view);
            self::renderPageFirst($model, $view);
        }
        else
            $tw->writeContent(' ');
        
        $tw->closeTd();
    }
    
    /**
     * Helper method to render the pager's dynamic page number
     *
     * @param MarkupWriter $tw
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderPagerDisplay(&$model, &$view)
    {
        $currentPage = $model->getState()->getPageIndex() + 1;
        $totalPages  = $model->getState()->getPageCount();
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openSpan();
        $tw->addAttribute('class', $view->getCssClass() . '-pager-page');
        $tw->writeContent(Resources::getValue(Resources::SRK_GRID_PAGER_PAGE));
        
        if ($totalPages > 1 && $model->getIsPaged() === true)
        {
            $tw->openSelect();
            
            $tw->addAttribute('eventname', 'GoPageIndex');
            for ($pageIndex = 0; $pageIndex < $totalPages; $pageIndex++)
            {
                $pageNum = $pageIndex + 1;
                $tw->openOption();
                $tw->addAttribute('value', $pageIndex);
                if ($currentPage == $pageNum)
                    $tw->addAttribute('selected', 'selected');
                $tw->writeContent(number_format($pageNum));
                $tw->closeOption();
            }
            
            $tw->closeSelect();
        }
        else
        {
            $tw->openSpan();
            $tw->writeContent(number_format($currentPage));
            $tw->closeSpan();
        }
        
        $tw->writeContent(Resources::getValue(Resources::SRK_GRID_PAGER_OFPAGE));
        $tw->openSpan();
        $tw->writeContent(number_format($totalPages));
        $tw->closeSpan();
        $tw->closeSpan();
    }
    
    /**
     * Helper method to render the bottom pager
     *
     * @param Grid $model
     * @param HtmlGridView $view
     */
    static protected function renderPagerBottom(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        if ($model->getState()->getTotalRecordCount() > 0 && $model->getIsPaged() === true)
        {
            // The pager
            $tw->openTd();
            $tw->addAttribute('class', $view->getCssClass() . '-pager-bottom');
            self::renderPageFirst($model, $view);
            self::renderPagePrevious($model, $view);
            self::renderPagerSplitter($model, $view);
            self::renderPagerDisplay($model, $view);
            self::renderPagerSplitter($model, $view);
            self::renderPageNext($model, $view);
            self::renderPageLast($model, $view);
            $tw->closeTd();
        }
        else
        {
            $tw->writeContent(' ');
        }
    }
    
    /**
     * Renders a Grid Search Dialog
     *
     * @param GridSearchDialogBase $model
     * @param HtmlGridView $view
     */
    public static function renderGridSearchDialog(&$model, &$view)
    {
        if ($model->getVisible() === false)
            return;
        
        // Temporarily set the model css class to the dialog's
        $gridCssClass = $view->getCssClass();
        $view->setCssClass($view->getSearchDialogCssClass());
        
        $tw = HtmlWriter::getInstance();
        
        // The frame
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-frame');
        $tw->addAttribute('style', 'visibility: ' . ($model->hasErrorMessage() ? '' : 'hidden') . '; width: ' . $view->getSearchDialogFrameWidth() . '; position: absolute; top: 0; left: 0;');
        
        // The form tag
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('action', '');
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('enctype', 'multipart/form-data');
        $tw->addAttribute('targetmodel', $view->getModel()->getName());
        $tw->addAttribute('columnname', $model->getColumnName());
        
        // The form's label
        if ($model->getCaption() != '')
        {
            $tw->openDiv();
            $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_caption');
            $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
            $tw->writeContent($model->getCaption());
            $tw->closeDiv();
        }
        
        // The form's content section
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_content');
        $tw->addAttribute('class', $view->getCssClass() . '-content');
        
        if ($view->getBodyHeight() > 0)
        {
            $tw->addAttribute('style', 'height: ' . $view->getBodyHeight() . 'px;');
        }
        
        // The form's child controls
        self::renderFieldContainerChildren($model, $view);
        
        // The form's error message (if available)
        if ($model->hasErrorMessage())
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-error');
            $tw->addAttribute('style', 'display: none;');
            $tw->openSpan();
            $tw->writeContent(Resources::getValue(Resources::SRK_FORM_ERRORTITLE));
            $tw->closeSpan();
            $tw->writeContent($model->getErrorMessage(), false, true, false);
            $tw->openDiv();
            $tw->openInput();
            $tw->addAttribute('class', $view->getCssClass() . '-error-close');
            $tw->addAttribute('type', 'button');
            $tw->addAttribute('value', Resources::getValue(Resources::SRK_FORM_ERRORCONTINUE));
            $tw->closeInput(true);
            $tw->closeDiv();
            $tw->closeDiv();
        }
        elseif ($model->hasMessage())
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-message');
            $tw->addAttribute('style', 'display: none;');
            $tw->openSpan();
            $tw->writeContent(Resources::getValue(Resources::SRK_FORM_INFOTITLE));
            $tw->closeSpan();
            $tw->writeContent($model->getMessage(), false, true, false);
            $tw->openDiv();
            $tw->openInput();
            $tw->addAttribute('class', $view->getCssClass() . '-message-close');
            $tw->addAttribute('type', 'button');
            $tw->addAttribute('value', Resources::getValue(Resources::SRK_FORM_INFOCONTINUE));
            $tw->addAttribute('redirect', $model->getRedirect());
            $tw->closeInput(true);
            $tw->closeDiv();
            $tw->closeDiv();
        }
        
        $tw->closeDiv();
        
        // The form's button section
        $buttonControls = $model->getChildren()->getControlNames(false, 'ButtonModelBase');
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-buttonpanel-container');
        
        if (count($buttonControls) > 0)
        {
            $tw->openDiv();
            $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_buttonPanel');
            $tw->addAttribute('class', $view->getCssClass() . '-buttonpanel');
            
            foreach ($buttonControls as $buttonName)
                self::renderFormButton($model, $view, $buttonName);
            
            $tw->closeDiv(); // Close the button panel
        }
        
        // Add the loading bar to replace the buttons with.
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-loadingbar');
        $tw->writeContent(' ');
        $tw->closeDiv(true); // Close the ajax bar
        
        self::renderPostBackFlag($model, $view);
        $tw->closeDiv(); // Close the bottom container
        
        $tw->closeForm();
        
        // close the frame
        $tw->closeDiv();
        
        // render the instantiation script
        $enableAsync = ($view->getIsAsynchronous() == true) ? 'true' : 'false';
        $javascript  = "var js_" . HtmlViewBase::getHtmlId($model) . " = null; window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new HtmlFormView('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', $enableAsync); });";
        
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw($javascript);
        $tw->closeScript(true);
        
        // restore the grid's css class
        $view->setCssClass($gridCssClass);
    }
}

/**
 * Provides a print preview Grid object renderer.
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlGridPrintView extends ObjectBase implements IRenderable
{
    /**
     * @var Grid
     */
    protected $model;
    protected $cssClass;
    
    /**
     * @todo Doc
     */
    public function __construct(&$model)
    {
        if (is_null($model))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model');
        
        if (ObjectIntrospector::isImplementing($model, 'IRootModel') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model must implement IRootModel');
        
        $this->cssClass = 'gridprintview';
        $this->model    = $model;
        
        $this->registerDependencies();
    }
    
    protected function registerDependencies()
    {
        $cssPath = HttpContext::getLibraryRoot() . 'css/std.gridprintview.css';
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlGridPrintView.Css', $cssPath);
    }
    
    /**
     * Renders the form and each control within it.
     *
     */
    public function render()
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openDiv(); // Open Main Container
        $tw->addAttribute('class', $this->cssClass . '-container');
        
        $tw->openDiv();
        $tw->addAttribute('class', 'printcontrol');
        $tw->openButton();
        $tw->addAttribute('onclick', 'window.print();');
        $tw->writeContent(Resources::getValue(Resources::SRK_PRINTVIEW_PRINT));
        $tw->closeButton();
        $tw->closeDiv();
        
        // The title
        $tw->openDiv();
        $tw->addAttribute('class', 'title');
        $tw->writeContent($this->model->getCaption());
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('class', 'subtitle');
        $tw->writeContent(Resources::getValue(Resources::SRK_PRINTVIEW_SUBTITLE) . date('Y-m-d H:i:s'));
        $tw->closeDiv();
        
        // The date generated
        $tw->openDiv();
        $tw->addAttribute('class', 'subtitle');
        $tw->closeDiv();
        
        // Te data table
        $tw->openTable();
        $tw->openThead();
        $tw->openTr();
        
        $columnNames = $this->model->getChildren()->getTypedControlNames(true, 'BoundGridColumnBase');
        $columnNames = array_merge($this->model->getChildren()->getTypedControlNames(true, 'GroupingColumn'), $columnNames);
        
        foreach ($columnNames as $columnName)
        {
            $columnObj = $this->model->getColumn($columnName);
            if ($columnObj->getVisible() == false)
                continue;
            $tw->openTh();
            $tw->writeContent($columnObj->getCaption());
            $tw->closeTh();
        }
        
        $tw->closeTr();
        $tw->closeThead();
        
        $tw->openTbody();
        
        for ($itemIndex = 0; $itemIndex < $this->model->getDataItems()->getCount(); $itemIndex++)
        {
            $tw->openTr();
            foreach ($columnNames as $columnName)
            {
                $columnObj = $this->model->getColumn($columnName);
                if ($columnObj->getVisible() == false)
                    continue;
                $dataItem   = $this->model->getDataItems()->getItem($itemIndex);
                $memberName = $columnObj->getBindingMemberName();
                $tw->openTd();
                $tw->writeContent($dataItem->$memberName, true, true, false);
                $tw->closeTd();
            }
            $tw->closeTr();
        }
        $tw->closeTbody();
        
        $tw->closeTable();
        $tw->closeDiv(); // Close main container
    }
    
    /**
     * Gets the Form that this instance renders
     *
     * @return Form
     */
    public function &getModel()
    {
        return $this->model;
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
}

/**
 * Provides an Excel data exporter view for a Grid object.
 *
 * @package WebCore
 * @subpackage View
 */
abstract class HtmlGridExcelWriterViewBase extends ObjectBase implements IRenderable
{
    /**
     * @var Grid
     */
    protected $model;
    
    /**
     * Creates a new instance of this class
     *
     * @param Grid $model
     */
    protected function __construct(&$model)
    {
        if (is_null($model))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model');
        
        if (ObjectIntrospector::isImplementing($model, 'IRootModel') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model must implement IRootModel');
        
        $this->model = $model;
    }
    
    /**
     * Renders the form and each control within it.
     * @todo Biff Writer not working correctly. Data shows up but it says it is corrupt.
     * @todo Table format.
     */
    public function render()
    {
        $workBook  = new ExcelWorkbook();
        $workSheet = $workBook->getActiveSheet();
        $workSheet->setTitle(date('YmdHis'));
        
        $columnNames = $this->model->getChildren()->getTypedControlNames(true, 'BoundGridColumnBase');
        $columnNames = array_merge($this->model->getChildren()->getTypedControlNames(true, 'GroupingColumn'), $columnNames);
        $columnIndex = 0;
        $rowIndex    = 0;
        
        foreach ($columnNames as $columnName)
        {
            $columnObj = $this->model->getColumn($columnName);
            if ($columnObj->getVisible() == false)
                continue;
            $cell = $workSheet->getCellByColumnAndRow($columnIndex, $rowIndex + 1);
            $cell->setValue(utf8_encode($columnObj->getCaption()));
            $cellStyle = $workSheet->getStyle($cell->getCoordinate());
            $cellStyle->getFont()->setBold(true);
            $workSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            
            $columnIndex++;
        }
        
        $rowIndex += 1;
        $isRowEven = true;
        for ($itemIndex = 0; $itemIndex < $this->model->getDataItems()->getCount(); $itemIndex++)
        {
            $columnIndex = 0;
            foreach ($columnNames as $columnName)
            {
                $columnObj = $this->model->getColumn($columnName);
                if ($columnObj->getVisible() == false)
                    continue;
                $dataItem   = $this->model->getDataItems()->getItem($itemIndex);
                $memberName = $columnObj->getBindingMemberName();
                $cell       = $workSheet->getCellByColumnAndRow($columnIndex, $rowIndex + 1);
                $cell->setValueExplicit($dataItem->$memberName, ExcelCell_DataType::TYPE_STRING);
                $cellStyle = $workSheet->getStyle($cell->getCoordinate());
                
                if (ObjectIntrospector::isA($columnObj, 'DateTimeBoundGridColumn'))
                {
                    $dt = new DateTime($dataItem->$memberName . ' UTC');
                    $cell->setValue($dt->format('n/j/Y g:i: A'));
                    $cellStyle->getNumberFormat()->setFormatCode(ExcelStyleNumberFormat::FORMAT_DATE_DATETIME);
                }
                if (ObjectIntrospector::isA($columnObj, 'MoneyBoundGridColumn'))
                {
                    $cellStyle->getNumberFormat()->setFormatCode(ExcelStyleNumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                }
                
                $columnIndex++;
            }
            $rowIndex++;
            $isRowEven = !$isRowEven;
        }
        
        $this->outputWorkbook($workBook);
    }
    
    /**
     * @todo Doc
     */
    abstract protected function outputWorkbook($workBook);
    
    /**
     * @todo Doc
     */
    static protected function getSafeFilename($filename)
    {
        $safename = preg_replace('/[^a-z0-9A-Z ]/', '', $filename);
        if ($safename == '')
        {
            $safename = date('YmdHis');
        }
        
        return $safename;
    }
    
    /**
     * Gets the Form that this instance renders
     *
     * @return Form
     */
    public function &getModel()
    {
        return $this->model;
    }
}

/**
 * Provides an Excel 97-2003 data exporter view for a Grid object.
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlGridBiffWriterView extends HtmlGridExcelWriterViewBase
{
    /**
     * @todo Doc
     */
    public function __construct(&$model)
    {
        include_once "ext/excel/webcore.excel.biffwriter.php";
        parent::__construct($model);
    }
    
    /**
     * @todo Doc
     */
    protected function outputWorkbook($workBook)
    {
        $writer   = new BiffWriter($workBook);
        $contents = $writer->output();
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . strlen($contents));
        header('Content-Disposition: attachment; filename="' . self::getSafeFilename($this->model->getCaption()) . '.xls"');
        echo $contents;
    }
}

/**
 * Provides an Excel 2007 (open xml) data exporter view for a Grid object.
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlGridOpenXmlWriterView extends HtmlGridExcelWriterViewBase
{
    /**
     * @todo Doc
     */
    public function __construct(&$model)
    {
        include_once "ext/excel/webcore.excel.oxmlwriter.php";
        parent::__construct($model);
    }
    
    /**
     * @todo Doc
     */
    protected function outputWorkbook($workBook)
    {
        $writer   = new OxmlWriter($workBook);
        $contents = $writer->output();
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . strlen($contents));
        header('Content-Disposition: attachment; filename="' . self::getSafeFilename($this->model->getCaption()) . '.xlsx"');
        echo $contents;
    }
}

/**
 * Provides a simple CSV data exporter view for a Grid object.
 *
 * @package WebCore
 * @subpackage View
 */
class HtmlGridCsvWriterView extends ObjectBase implements IRenderable
{
    /**
     * @var Grid
     */
    protected $model;
    
    /**
     * Creates a new instance of this class
     *
     * @param Grid $model
     */
    public function __construct(&$model)
    {
        if (is_null($model))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model');
        
        if (ObjectIntrospector::isImplementing($model, 'IRootModel') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model must implement IRootModel');
        
        $this->model = $model;
    }
    
    /**
     * Renders the grid data for each of the columns
     * @todo Table format.
     */
    public function render()
    {        
        $columnNames = $this->model->getChildren()->getTypedControlNames(true, 'BoundGridColumnBase');
        $columnNames = array_merge($this->model->getChildren()->getTypedControlNames(true, 'GroupingColumn'), $columnNames);
        $columnIndex = 0;
        $rowIndex    = 0;
        
        $contents = '';
        
        // Do the row headers first
        $line = new IndexedCollection();
        foreach ($columnNames as $columnName)
        {
            $columnObj = $this->model->getColumn($columnName);
            if ($columnObj->getVisible() == false)
                continue;
            $line->addValue('"' . str_replace('"', '""', $columnObj->getCaption()) . '"');
            $columnIndex++;
        }
        
        // Flush the line contents
        $contents .= $line->implode('%s', ',') . "\r\n";

        $line->clear();

        $rowIndex += 1;
        $isRowEven = true;
        for ($itemIndex = 0; $itemIndex < $this->model->getDataItems()->getCount(); $itemIndex++)
        {
            $columnIndex = 0;
            foreach ($columnNames as $columnName)
            {
                $columnObj = $this->model->getColumn($columnName);
                if ($columnObj->getVisible() == false)
                    continue;
                $dataItem   = $this->model->getDataItems()->getItem($itemIndex);
                $memberName = $columnObj->getBindingMemberName();
                
                $line->addValue('"' . str_replace('"', '""', utf8_decode($dataItem->$memberName)) . '"');
                $columnIndex++;
            }
            
            $rowIndex++;
            $isRowEven = !$isRowEven;
            $contents .= $line->implode('%s', ',') . "\r\n";
            $line->clear();
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . strlen($contents));
        header('Content-Disposition: attachment; filename="' . self::getSafeFilename($this->model->getCaption()) . '.csv"');
        echo $contents;
    }
    
    /**
     * @todo Doc
     */
    static private function getSafeFilename($filename)
    {
        $safename = preg_replace('/[^a-z0-9A-Z ]/', '', $filename);
        if ($safename == '')
        {
            $safename = date('YmdHis');
        }
        
        return $safename;
    }
    
    /**
     * Gets the Form that this instance renders
     *
     * @return Form
     */
    public function &getModel()
    {
        return $this->model;
    }
}

?>