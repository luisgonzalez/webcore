<?php
require_once "../webcore.php";

class SettingsEditorWidget extends WidgetBase
{
    // Session Keys
    const SK_COLLECTION = 'SettingsEditorWidget.Collection';
    
    /**
     * @var KeyedCollection
     */
    protected $collection;
    protected $formModels;
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->persistCollection();
        $this->createModels();
        $this->createViews();
        $this->model = null;
    }
    
    public static function createInstance()
    {
        $className = __CLASS__;
        return new $className;
    }
    
    private function persistCollection()
    {
        $this->collection = Settings::getCollection();
        HttpContext::getSession()->registerPersistentObject(self::SK_COLLECTION, $this->collection);
    }
    
    private function createModels()
    {
        $this->formModels = new KeyedCollection();
        $sectionNames = $this->collection->getKeys();
        foreach ($sectionNames as $section)
        {
            $addDefault = false;
            $form = new Form(__CLASS__ . '_form_' . $section, '');
            $sectionKeys = array_keys($this->collection->getItem($section));
            $sectionValues = $this->collection->getItem($section);
            foreach ($sectionKeys as $key)
            {
                $addDefault = false;
                if (is_array($sectionValues[$key])) continue;
                switch ($section)
                {
                    case 'application' :
                        switch ($key)
                        {
                            case 'applicationCulture' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('en-US', 'en-US');
                                $c->addOption('es-MX', 'es-MX');
                                $form->addField($c);
                                break;
                            case 'applicationTimezone' :
                                $timezones = timezone_identifiers_list();
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                foreach ($timezones as $tz)
                                {
                                    $tzParts = explode('/', $tz);
                                    if (count($tzParts) <= 1) continue; 
                                    $c->addOption($tz, $tzParts[1], $tzParts[0]);
                                }
                                
                                $form->addField($c);
                                break;
                            case 'applicationSessionHandler' :
                                $classNames = ClassLoader::getClassCache()->getKeys();
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('', '(PHP Default)');
                                foreach ($classNames as $className)
                                {
                                    if (in_array('ISessionHandler', class_implements($className)))
                                    {
                                        $c->addOption($className, $className);
                                    }
                                }
                                
                                $form->addField($c);
                                break;
                            default :
                                $addDefault = true;
                        }
                        break;
                    case 'caching' :
                        switch ($key)
                        {
                            case 'enable' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('1', 'True (1)');
                                $c->addOption('0', 'False (0)');
                                $form->addField($c);
                                break;
                        }
                        
                        break;
                    case 'data' :
                        switch ($key)
                        {
                            case 'connectionManager' :
                                $classNames = ClassLoader::getClassCache()->getKeys();
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                foreach ($classNames as $className)
                                {
                                    if (in_array('DataConnectionBase', class_parents($className)))
                                    {
                                        $c->addOption($className, $className);
                                    }
                                }
                                
                                $form->addField($c);
                                break;
                            case 'concurrencyMode' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('0', 'Last in wins (0)');
                                $c->addOption('1', 'First in wins (1)');
                                $form->addField($c);
                                break;
                            case 'disableDeferredExecution' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('1', 'True (1)');
                                $c->addOption('0', 'False (0)');
                                $form->addField($c);
                                break;
                            case 'allowDropCommands' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('1', 'True (1)');
                                $c->addOption('0', 'False (0)');
                                $form->addField($c);
                                break;
                            default :
                                $addDefault = true;
                        }
                        break;
                    
                    case 'compression' :
                        switch ($key)
                        {
                            case 'compressionProvider' :
                                $classNames = ClassLoader::getClassCache()->getKeys();
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                foreach ($classNames as $className)
                                {
                                    if (in_array('HttpCompressor', class_parents($className)))
                                    {
                                        $c->addOption($className, $className);
                                    }
                                }
                                
                                $form->addField($c);
                                break;
                            case 'store' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('cacheManager', 'CacheManager');
                                $c->addOption('fileSystem', 'FileSystem');
                                $form->addField($c);
                                break;
                            case 'enable' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('1', 'True (1)');
                                $c->addOption('0', 'False (0)');
                                $form->addField($c);
                                break;
                            default :
                                $addDefault = true;
                        }
                        break;
                    
                    case 'logging' :
                        switch ($key)
                        {
                            case 'logLevel' :
                                $c = new ComboBox($section . '__' . $key, StringHelper::toWords($key), $sectionValues[$key]);
                                $c->addOption('0', '0 - Disabled');
                                $c->addOption('1', '1 - Exception');
                                $c->addOption('2', '2 - Warning');
                                $c->addOption('3', '3 - Information');
                                $c->addOption('4', '4 - Debugging');
                                $c->addOption('5', '5 - Debugging Client');
                                $form->addField($c);
                                break;
                            default :
                                $addDefault = true;
                        }
                        break;
                    default :
                        $addDefault = true;
                }
                
                if ($addDefault === true)
                {
                    $form->addField(new TextField(
                        $section . '__' . $key,
                        StringHelper::toWords($key),
                        $sectionValues[$key]));
                }
            }
            
            $this->formModels->setItem($section, $form);
        }
    }
    
    private function createViews()
    {
        $tabView = new HtmlTabView(__CLASS__ . '_tabView');
        $tabPageKeys = $this->formModels->getKeys();
        foreach ($tabPageKeys as $key)
        {
            $tabPage = new HtmlTabViewPage(__CLASS__ . 'tabPage' . $key, StringHelper::toWords($key));
            $formView = new HtmlFormView($this->formModels->getItem($key));
            $formView->setFrameWidth('auto');
            $formView->setShowFrame(false);
            $tabPage->addItem($formView);
            $tabView->getTabPages()->addItem($tabPage);
        }
        
        $this->view = $tabView;
        
    }
    
    public function render()
    {
        $this->view->render();
    }
    
    public function handleRequest()
    {
        
    }
}

HttpContext::initialize();
$widget = new SettingsEditorWidget();
$widget->handleRequest();
HttpResponse::write(MarkupWriter::DTD_XHTML_STRICT . "\r\n");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <?php
            HtmlViewManager::render();
        ?>
        <style type="text/css">
            body
            {
                padding: 0;
                margin: 0;
                font-family: verdana, arial, sans-serif;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div style="width: 975px;">  
            <?php $widget->render(); ?>
        </div>
    </body>
</html>