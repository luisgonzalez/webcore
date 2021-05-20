<?php
/**
 * @category Web: Navigation
 * @tutorial Shows Navigation History entries
 * @author Mario Di Vece <mario@unosquare.com>
 */

class NavigationSampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    
    public static function createInstance()
    {
        $class = __CLASS__;
        return new $class;
    }
    
    public function handleRequest()
    {
        // do nothing
    }
    
    public function render()
    {
        $tw = HtmlWriter::getInstance();
        $tw->openDiv();
        $tw->addAttribute('style', 'max-height: 500px; overflow: auto; padding: 6px; margin-top: 0px; background-color: #dfdfdf; border: solid 1px #cdcdcd;');
        for ($i = 0; $i < HttpContext::getNavigationHistory()->getCount(); $i++)
        {
            $navEntry = HttpContext::getNavigationHistory()->getItem($i);
            $tw->openDiv();
            $tw->addAttribute('style', 'font-family: courier; margin-top: 0px; border-bottom: 1px solid #dcdcdc; background-color: #fff;');
            $tw->openTable();
            $tw->openTr();
            $tw->openTd();
            $tw->addAttribute('colspan', '2');
            $tw->addAttribute('style', 'font-family: Verdana; font-weight: bold; padding: 4px; padding-bottom: 8px; background-color: #dfdfdf; border-bottom: solid 1px #cdcdcd;');
            $tw->writeContent($navEntry->getRequestUrl());
            $tw->openA();
            $tw->addAttribute('style', 'color: #4444ff; margin-left: 20px;');
            $tw->addAttribute('onclick', Controller::getOnClickTransfer($navEntry->getRequestUrl(), $navEntry->getPostedVars()->getArrayReference()));
            $tw->addAttribute('href', HtmlRenderCallbacks::HREF_NO_ACTION);
            $tw->writeContent('Transfer');
            $tw->closeA();
            $tw->closeTd();
            $tw->closeTr();
            foreach ($navEntry->getPostedVars()->getKeys() as $key)
            {
                $value = $navEntry->getPostedVars()->getValue($key);
                $tw->openTr();
                $tw->openTd();
                $tw->addAttribute('style', 'text-align: right; width: 40%; padding: 4px;');
                $tw->writeContent($key);
                $tw->closeTd();
                $value = $navEntry->getPostedVars()->getValue($key);
                $tw->openTd();
                $tw->addAttribute('style', 'padding: 4px;');
                if (StringHelper::beginsWith($value, 'YTo'))
                {
                    $tw->writeContent(' ');
                    var_dump(Base64Serializer::deserialize($value, 'GridState'));
                }
                else
                {
                    $tw->writeContent("'" . $value . "'");
                }
                $tw->closeTd();
                $tw->closeTr();
            }
            
            $tw->closeTable();
            $tw->closeDiv();
        }
        $tw->closeDiv();
    }
}

$sample = new NavigationSampleWidget();
$sample->handleRequest();
?>