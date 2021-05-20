<?php
/**
 * Represents a common control in a Google Visualization
 *
 * @package WebCore
 * @subpackage Google
 */
class GVizControl extends ControlModelBase implements IRootModel, IBindingTarget
{
    const TYPE_IMAGESPARKLINE = 'imagesparkline';
    const TYPE_AREACHART = 'areachart';
    const TYPE_PIECHART = 'piechart';
    const TYPE_COLUMNCHART = 'columnchart';
    const TYPE_LINECHART = 'linechart';
    const TYPE_BARCHART = 'barchart';
    
    const OPTION_IS_STACKED = 'isStacked';
    const OPTION_IS_3D = 'is3D';
    const OPTION_SHOW_VALUE_LABELS = 'showValueLabels';
    const OPTION_ENABLE_TOOLTIP = 'enableTooltip';
    const OPTION_SHOW_CATEGORIES = 'showCategories';
    const OPTION_WIDTH = 'width';
    const OPTION_HEIGHT = 'height';
    
    protected $caption;
    protected $type;
    protected $columns;
    protected $dataSource;
    protected $options;
    
    public function __construct($name, $caption, $type = 'barchart')
    {
        parent::__construct($name, true);
        
        $this->caption = $caption;
        $this->type    = $type;
        $this->options = new KeyedCollection();
        $this->setOption(self::OPTION_HEIGHT, 320);
        $this->setOption(self::OPTION_WIDTH, 500);
        $this->setOption(self::OPTION_IS_STACKED, true);
        $this->setOption(self::OPTION_IS_3D, true);
    }
    
    /**
     * Get visualization type
     *
     * @return string
     */
    public function getVizType()
    {
        return $this->type;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function getDataSource()
    {
        return $this->dataSource;
    }
    
    /**
     * Gets caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }
    
    public function setCaption($value)
    {
        $this->caption = $value;
    }
    
    /**
     * Sets an option for the Chart
     *
     * @param string $option. One of the OPTION_* prefixed constants
     * @param bool $value
     */
    public function setOption($option, $value)
    {
        $this->options->setValue($option, $value);
    }
    
    /**
     * Gets the options
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * @param IndexedCollection $dataSource
     */
    public function dataBind(&$dataSource)
    {
        if ($dataSource->isEmpty())
            return;
        
        $this->columns = array();
        
        foreach ($dataSource->getItem(0) as $key => $value)
        {
            if (is_object($value))
                $scalar = $value->getValue();
            else
                $scalar = $value;
            
            if (is_float($scalar) || is_int($scalar))
                $this->columns[] = array(
                    'id' => $key,
                    'type' => 'number'
                );
            else
                $this->columns[] = array(
                    'id' => $key,
                    'type' => 'string'
                );
        }
        
        foreach ($dataSource as $item)
        {
            $arr = array();
            
            foreach ($item as $key => $value)
            {
                $scalar = is_object($value) ? $value->getValue() : $value;
                
                $arr[] = $scalar;
            }
            
            $this->dataSource[] = $arr;
        }
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return GVizControl
     */
    public static function createInstance()
    {
        return new GVizControl('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a Google Chart view
 * 
 * @package WebCore
 * @subpackage Google
 */
class GChartView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class based on a Google Visualization model
     *
     * @param GVizControl $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'vizview';
        $this->frameWidth     = "auto";
        $this->isAsynchronous = true;
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        // Setup the callbacks for each renderable model
        $callbacks['GVizControl'] = array(
            'GVizRenderCallbacks',
            'renderGChartControl'
        );
        
        $this->registerDependencies();
    }
    
    /**
     * Registers model resources and dependencies on the client-side
     *
     */
    protected function registerDependencies()
    {
        //self::registerCommonDependecies();
    }
}

/**
 * Represents a Google Visualization view
 *
 * @package WebCore
 * @subpackage Google
 */
class GVizView extends HtmlViewBase
{
    /**
     * Creates a new instance of this class based on a Google Visualization model
     *
     * @param GVizControl $model
     */
    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass       = 'vizview';
        $this->frameWidth     = "auto";
        $this->isAsynchronous = true;
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        // Setup the callbacks for each renderable model
        $callbacks['GVizControl'] = array(
            'GVizRenderCallbacks',
            'renderGVizControl'
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
        
        $formviewPath = HttpContext::getLibraryRoot() . 'ext/google/google.vizview.js';
        $cssPath      = HttpContext::getLibraryRoot() . 'ext/google/google.vizview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlVizView.Js', $formviewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlVizView.Css', $cssPath);
        
        $googleAPIPath = 'http://www.google.com/jsapi';
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'GoogleAPI.Js', $googleAPIPath);
        
        
        $jsViz          = "gVizView_" . $this->model->getName();
        $jsVizContainer = HtmlViewBase::getHtmlId($this->model) . "_content";
        $jsData         = json_encode($this->model->getDataSource());
        $jsColumns      = json_encode($this->model->getColumns());
        $jsVizType      = $this->model->getVizType();
        $jsVizOptions   = json_encode($this->model->getOptions()->getArrayReference());
        
        $javascript = "var $jsViz = new GVizView('$jsVizContainer', '$jsVizType', $jsVizOptions); {$jsViz}.setColumns('$jsColumns'); {$jsViz}.setData('$jsData'); {$jsViz}.draw();";
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_BLOCK, $this->model->getName(), 'gViz_' . $this->model->getName(), $javascript);
    }
}

/**
 * Contains static callback methods to render Google Visualization
 *
 * @package WebCore
 * @subpackage Google
 */
class GVizRenderCallbacks extends HtmlRenderCallbacks
{
    /**
     * Renders the main control as static chart
     *
     * @param GVizControl $model
     * @param GVizView $view
     */
    public static function renderGChartControl(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $legends = array();
        $data    = array();
        
        foreach ($model->getDataSource() as $item)
        {
            $legends[] = $item[0];
            $data[]    = $item[1];
        }
        
        $url = "http://chart.apis.google.com/chart?";
        $url .= "cht=p3"; // Change for type
        $url .= "&chs=600x500";
        $url .= "&chl=" . implode("|", $legends);
        $url .= "&chd=t:" . implode(",", $data);
        
        $tw->openImg();
        $tw->addAttribute('src', $url);
        $tw->closeImg();
    }
    
    /**
     * Renders the main control
     *
     * @param GVizControl $model
     * @param GVizView $view
     */
    public static function renderGVizControl(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openForm();
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('class', $view->getCssClass());
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();
        
        $tw->openDiv();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model) . '_content');
        $tw->addAttribute('class', $view->getCssClass() . '-content');
        
        $tw->openDiv();
        $tw->addAttribute('id', $model->getName() . '_viz');
        $tw->addAttribute('type', $model->getVizType());
        $tw->addAttribute('class', $view->getCssClass() . '-viz');
        $tw->writeContent('');
        $tw->closeDiv();
        
        self::renderPostBackFlag($model, $view);
        $tw->closeDiv();
        
        $tw->closeForm();
        
        //self::renderInitializationScript($javascript);
    }
}
?>