<?php
/**
 * Represents a image gallery
 *
 * @package WebCore
 * @subpackage Gallery
 */
class Gallery extends DataRepeaterModelBase implements IRootModel
{
    /**
     * Create a instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
    }
    
    /**
     * Binds all IBindingTargetMember controls within the repeater to the given data source.
     *
     * @param IndexedCollection $dataSource
     */
    public function dataBind(&$dataSource)
    {
        if (ObjectIntrospector::isA($dataSource, 'TableAdapter'))
        {
            if ($this->isPaged === true)
            {
                if ($this->state->getTotalRecordCount() === -1)
                {
                    $countSource = clone $dataSource;
                    $recordCount = $countSource->count();
                    $this->state->setTotalRecordCount($recordCount);
                    $pageCount = intval(ceil($recordCount / $this->pageSize));
                    $this->state->setPageCount($pageCount);
                    if ($this->state->getPageIndex() >= $this->state->getPageCount())
                    {
                        $this->state->setPageIndex($this->state->getPageCount() - 1);
                    }
                }
                
                $dataSource->take($this->pageSize)->skip($this->state->getPageIndex() * $this->pageSize);
            }
            
            $rows = $dataSource->selectNew()->getArrayReference();
            $this->dataItems->addRange($rows);
        }
        else
        {
            foreach ($dataSource as $item)
            {
                $this->dataItems->addItem($item);
            }
        }
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Gallery
     */
    public static function createInstance()
    {
        return new Gallery('Serializable', 'Serializable');
    }
}

/**
 * Represents a Image entity
 *
 * @package WebCore
 * @subpackage Gallery
 */
class GalleryImage extends ObjectBase
{
    public $FileName;
    public $Thumbnail;
    public $Name;
    public $Alt = '';
    public $Properties;
    public $thumbWidth = 320;
    public $thumbHeight = 240;
    public $writeNameAsHtml = false;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $fileName
     * @param string $name
     */
    public function __construct($fileName, $name)
    {
        $this->FileName   = $fileName;
        $this->Thumbnail  = $fileName;
        $this->Name       = $name;
        $this->Alt        = $name;
        $this->Properties = new KeyedCollection();
    }
    
    /**
     * Creates a thumbnail
     *
     */
    public function createThumbnail()
    {
        if (file_exists($this->FileName) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = fileName');
        
        $info      = pathinfo($this->FileName);
        $baseName  = str_replace("." . $info['extension'], '', $info['basename']);
        $thumbName = $info['dirname'] . '/' . $baseName . ".thumb.jpg";
        if (file_exists($thumbName) == false)
        {
            $thumbResource = ImageHelper::createThumbnailFromFile($this->FileName, $this->thumbWidth, $this->thumbHeight);
            imagejpeg($thumbResource, $thumbName, 90);
        }
        
        $this->Thumbnail = $thumbName;
    }
}

/**
 * Represents a Gallery HTML view
 *
 * @package WebCore
 * @subpackage Gallery
 */
class HtmlGalleryView extends HtmlViewBase
{
    //This var was introduced in order to make this class
    // a little more flexible when implementing
    protected $suffixCssClass = '';
    protected $enableContextMenu = true;

    /**
     * Returns whether or not the context menu is able for the view
     * @return Boolean
     */
    public function getEnableContextMenu()
    {
        return $this->enableContextMenu;
    }

    /**
     * Sets whether or not to enable context menu for the view
     * @param <type> $enableContextMenu
     */
    public function setEnableContextMenu($enableContextMenu)
    {
        $this->enableContextMenu = $enableContextMenu;
    }

    public function getSuffixCssClass()
    {
        return $this->suffixCssClass;
    }

    public function setSuffixCssClass($class)
    {
        $this->suffixCssClass = $class;
    }
    /**
     * Creates a new instance of this class based on a gallery model
     *
     * @param Gallery $model
     */

    public function __construct(&$model)
    {
        parent::__construct($model);
        $this->cssClass   = 'galleryview';
        $this->frameWidth = '90%';
        
        $callbacks =& $this->renderCallbacks->getArrayReference();
        
        // Setup the callbacks for each renderable model
        $callbacks['Gallery'] = array(
            'HtmlGalleryRenderCallbacks',
            'renderGallery'
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
        
        $formviewPath = HttpContext::getLibraryRoot() . 'ext/gallery/std.galleryview.js';
        $cssPath      = HttpContext::getLibraryRoot() . 'ext/gallery/std.galleryview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlGalleryView.Js', $formviewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlGalleryView.Css', $cssPath);
    }
}

/**
 * Represents a Gallery Coverflow-like HTML view
 *
 * @package WebCore
 * @subpackage Gallery
 */
class HtmlGalleryFlowView extends HtmlGalleryView
{
    public function __construct(&$model)
    {
        parent::__construct($model);
        $callbacks =& $this->renderCallbacks->getArrayReference();
        $callbacks['Gallery'] = array(
            'HtmlGalleryRenderCallbacks',
            'renderGalleryFlow'
        );
    }
    
    protected function registerDependencies()
    {
        self::registerCommonDependecies();
        
        $formviewPath = HttpContext::getLibraryRoot() . 'ext/gallery/flow.galleryview.js';
        $cssPath      = HttpContext::getLibraryRoot() . 'ext/gallery/flow.galleryview.css';
        
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, 'HtmlGalleryView.Js', $formviewPath);
        HtmlViewManager::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, 'HtmlGalleryView.Css', $cssPath);
    }
}

/**
 * Contains static callback methods to render Gallery
 *
 * @package WebCore
 * @subpackage Gallery
 */
class HtmlGalleryRenderCallbacks extends HtmlRepeaterRenderCallbacks
{
    /**
     * Helper method to render the gallery flow
     *
     * @param Gallery $model
     * @param HtmlGalleryView $view
     */
    public static function renderGalleryFlow(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        
        foreach ($model->getDataItems() as $item)
        {
            $tw->openDiv();
            
            $tw->openImg();
            $tw->addAttribute('class', $view->getCssClass() . '-thumbnail-image');
            $tw->addAttribute('alt', $item->Name);
            $tw->addAttribute('title', $item->Name);
            $tw->addAttribute('src', $item->FileName);
            $tw->closeImg();
            
            $tw->closeDiv();
        }
        
        $tw->closeForm();
        
        $javascript = "var js_" . HtmlViewBase::getHtmlId($model) . " = null;
                    window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new MooFlow($('" . HtmlViewBase::getHtmlId($model) . "')); });";
        self::renderInitializationScript($javascript);
    }
    
    /**
     * Helper method to render the gallery
     *
     * @param Gallery $model
     * @param HtmlGalleryView $view
     */
    public static function renderGallery(&$model, &$view)
    {
        $tw = HtmlWriter::getInstance();
        
        $tw->openForm();
        $tw->addAttribute('id', HtmlViewBase::getHtmlId($model));
        $tw->addAttribute('name', $model->getName());
        $tw->addAttribute('class', $view->getCssClass().$view->getSuffixCssClass());
        $tw->addAttribute('method', 'post');
        $tw->addAttribute('action', HttpContext::getInfo()->getScriptVirtualPath());
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getMasterCssClass() . '-caption');
        $tw->writeContent($model->getCaption());
        $tw->closeDiv();
        
        foreach ($model->getChildren()->getTypedControlNames(true, 'Toolbar') as $control)
        {
            $currentControl   = $model->getChildren()->getControl($control, true);
            $controlClassName = $currentControl->getType()->getName();
            
            $renderCallback = $view->getRenderCallbacks()->getValue($controlClassName);
            
            if (is_callable($renderCallback, false))
                call_user_func_array($renderCallback, array(
                    &$currentControl,
                    &$view
                ));
        }
        
        $tw->openDiv();
        $tw->addAttribute('class', $view->getCssClass() . '-container');
        
        foreach ($model->getDataItems() as $item)
        {
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-thumbnail');
            
            $tw->openDiv();
            $tw->addAttribute('class', $view->getCssClass() . '-thumbnail-caption');
            
            if($item->writeNameAsHtml)
            {
                $tw->writeRaw(utf8_encode($item->Name));
            }
            else
            {
                $tw->writeContent($item->Name);
            }
            
            $tw->closeDiv();
            
            $tw->openDiv();
            
            $tw->openImg();
            $tw->addAttribute('class', $view->getCssClass() . '-thumbnail-image');
            $tw->addAttribute('fullsize', $item->FileName);
            $tw->addAttribute('alt', $item->Alt);
            $tw->addAttribute('src', $item->Thumbnail);
            $tw->closeImg();
            
            $tw->closeDiv();
            $tw->closeDiv();
        }
        
        $tw->openDiv();
        $tw->addAttribute('style', 'clear: both;');
        $tw->writeContent(" ");
        $tw->closeDiv(true);
        
        if ($model->getIsPaged())
            self::renderPager($model, $view);
        
        $tw->closeDiv();
        
        $tw->closeForm();
        
        $enableAsync = ($view->getIsAsynchronous() == true) ? 'true' : 'false';
        $javascript  = "var js_" . HtmlViewBase::getHtmlId($model) . " = null;

                    window.addEvent('domready', function () { js_" . HtmlViewBase::getHtmlId($model) . " = new HtmlGalleryView('" . HtmlViewBase::getHtmlId($model) . "', '" . $view->getCssClass() . "', $enableAsync); ";
        if(!$view->getEnableContextMenu())
        {
            $javascript.= "js_" . HtmlViewBase::getHtmlId($model).".disableContextMenu();";
        }
        $javascript.=    "});";
        self::renderInitializationScript($javascript);
    }
}
?>