<?php
require_once "ext/rss/webcore.rss.php";

/**
 * @category Web: RSS Syndication
 * @tutorial Rss for dummies.
 * @author Geovanni Perez <simio@unosquare.com>
 */

class RssSampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $data = DataContext::getInstance()->getAdapter('Users')
            ->addField('Users', 'name_first', 'title')
            ->addField('Users', 'email', 'link')
            ->addField('Users', 'birthdate', 'description')
            ->take(20);
        
        $rssFeed = new RssFeedModel('Users RSS');
        $rssFeed->setDescription('This is just a sample.');
        
        $rssFeed->dataBind($data);
        
        $rssRenderer = new RssFeedView($rssFeed);
        
        HttpResponse::outputBufferStart();
        $rssRenderer->render();
        $contents = HttpResponse::getOutputBuffer();
        HttpResponse::clearOutputBuffer();
        
        $widgetPreview = new HtmlTagView('pre', true, false, false);
        $widgetPreview->getAttributes()->setValue('style', 'max-height: 500px; overflow: auto; border: 1px solid #cdcdcd; background-color: #fcfcfc; padding: 4px; font-family: monospace;');
        $widgetPreview->setContent(htmlentities($contents));
        $this->view = $widgetPreview;
    }
    
    public static function createInstance()
    {
        return new RssSampleWidget();
    }
}

$sample = new RssSampleWidget();
?>