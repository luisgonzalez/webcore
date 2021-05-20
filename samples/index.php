<?php
/**
 * WebCore 3.0 Samples Browser
 * @version 1.0.0
 * @author Mario Di Vece <mario@unosquare.com>
 */
require_once 'initialize.inc.php';
HttpContext::getPermissionSet()->setDefaultResolve(Permission::PERMISSION_ALLOW);
HttpContext::applySecurity();

class SampleFilesWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct('filesWidget');
        $rep = new DataRepeater('sampleFiles', 'Sample Files Browser');
        $rep->setIsPaged(false);
        $rep->addRepeaterField(new TextRepeaterField('sampleFile', 'Sample File', 'filename'));
        $rep->addRepeaterField(new TextRepeaterField('sampleTutorial', 'Description', 'tutorial'));
        $rep->addRepeaterField(new TextRepeaterField('sampleAuthor', 'Author', 'author'));
        $rep->addRepeaterField(new CommandRepeaterField('openSample', 'Open Sample', 'openSample', 'args'));
        $arr = self::getSampleFiles();
        $rep->dataBind($arr);
        $this->model = $rep;
        $vw          = new HtmlAccordionView($rep, 'category');
        $vw->setIsAsynchronous(false);
        $vw->setFrameWidth('inherit');
        $this->view = $vw;
        Controller::registerEventHandler('openSample', array(
            __CLASS__,
            'openSample_Clicked'
        ));
        if (HttpSession::getInstance()->keyExists('fileName') === false)
        {
            $emptyFile = '';
            HttpSession::getInstance()->setValue('fileName', $emptyFile);
        }
        
        if (HttpSession::getInstance()->keyExists('initialIndex') === false)
        {
            $initialIndex = '0';
            HttpSession::getInstance()->setValue('initialIndex', $initialIndex);
        }
    }
    
    public static function createInstance()
    {
        return new SampleFilesWidget();
    }
    
    /**
     * @param DataRepeater $sender
     * @param ControllerEvent $event
     */
    public static function openSample_Clicked(&$sender, &$event)
    {
        $args = StringHelper::split($event->getValue(), ',');
        HttpSession::getInstance()->setValue('fileName', $args->getItem(0));
        HttpSession::getInstance()->setValue('initialIndex', $args->getItem(1));
    }
    
    /**
     * Gets a list of sample files.
     * @return IndexedCollection
     */
    private static function getSampleFiles()
    {
        $sampleFiles = new IndexedCollection();
        $arrFiles    = scandir(SAMPLES_PATH);
        foreach ($arrFiles as $file)
        {
            if (StringHelper::beginsWith($file, 'samples.') && StringHelper::endsWith($file, '.php'))
            {
                $fileInfo           = new stdClass();
                $fileInfo->filename = $file;
                $fileInfo->category = '';
                $fileInfo->tutorial = '';
                $fileInfo->author   = '';
                $fileInfo->args     = $file;
                $fileHandle         = fopen(SAMPLES_PATH . $file, 'r');
                if ($fileHandle === false)
                {
                    throw new SystemException(SystemException::EX_INVALIDMETHODCALL, "The file '" . SAMPLES_PATH . $file . "' could not be opened for read.");
                    exit();
                }
                while (!feof($fileHandle))
                {
                    $line = fgets($fileHandle);
                    if (StringHelper::strContains($line, '*/'))
                        break;
                    if (StringHelper::strContains($line, '@category'))
                        $fileInfo->category = self::parseDoc($line, '@category');
                    if (StringHelper::strContains($line, '@tutorial'))
                        $fileInfo->tutorial = self::parseDoc($line, '@tutorial');
                    if (StringHelper::strContains($line, '@author'))
                        $fileInfo->author = self::parseDoc($line, '@author');
                }
                
                fclose($fileHandle);
                if ($fileInfo->category !== '')
                {
                    $sampleFiles->addItem($fileInfo);
                }
            }
        }
        
        $sampleFiles->objectSort('category');
        $currentIndex = 0;
        foreach ($sampleFiles as $fileInfo)
        {
            $fileInfo->args .= ',' . $currentIndex;
            $currentIndex++;
        }
        
        return $sampleFiles;
    }
    
    private static function parseDoc($line, $attrib)
    {
        $pos   = stripos($line, $attrib) + strlen($attrib);
        $value = substr($line, $pos);
        return trim($value);
    }
}

$page          = new WebPage(SAMPLES_PATH . 'page.tpl.php', 'WebCore 3.0 Samples Browser');
$samplesWidget = new SampleFilesWidget();
$samplesWidget->handleRequest();
$sampleFile = HttpSession::getInstance()->getValue('fileName');
if ($sampleFile != '')
{
    include_once($sampleFile);
    WebPage::getCurrent()->addContent('right', $sample);
}
$samplesWidget->getView()->setInitialIndex(HttpContext::getSession()->getValue('initialIndex'));

$page->addContent('left', $samplesWidget);
$page->setUseOutputBuffering(true);
$page->render();
?>