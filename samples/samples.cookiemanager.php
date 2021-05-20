<?php
/**
 * @category Web: CookieManager
 * @tutorial Learn how to use WebCore's cookie manager.
 * @author Mario Di Vece <mario@unosquare.com>
 */

/**
 * @package WebCore
 * @subpackage Samples
 *
 * The Cookie Manager Widget
 */
class CookieManageSampleWidget extends WidgetBase
{
    protected $refreshed;
    
    /**
     * Creates a new instance of this class
     */
    public function __construct()
    {
        parent::__construct('cookieManagerSample');
        
        // This is a model-less widget
        $this->model = null;
    }
    
    /**
     * Creates the default instance of this class
     */
    public static function createInstance()
    {
        return new CookieManageSampleWidget();
    }
    
    /**
     * Determines the logic to handle the current request
     */
    public function handleRequest()
    {
        // Get a reference to the cookie manage object
        $cookieMan = CookieManager::getInstance();
        
        // Check if the cookie is set
        if ($cookieMan->isCookieSet('refreshed') == false)
        {
            $this->refreshed = 0;
            $cookieMan->setCookie('refreshed', $this->refreshed);
        }
        else
        {
            $this->refreshed = $cookieMan->getCookieValue('refreshed');
            $this->refreshed++;
            $cookieMan->setCookie('refreshed', $this->refreshed);
            
            if ($this->refreshed >= 5)
            {
                $cookieMan->deleteCookie('refreshed');
            }
        }
    }
    
    /**
     * gets the Widget's view
     */
    public function &getView()
    {
        // Create the view
        $this->view = HtmlTagView::createTag('div', '', "The page has been refreshed {$this->refreshed} times. \r\nAfter you click the Open Sample link 5 times, the cookie will be deleted. Then, it will be recreated.");
        $this->view->addAttribute('style', 'text-align: center; padding: 20px; border: 1px solid #8B919F; background-color: #FCFCFE;');
        return parent::getView();
    }
}

$sample = new CookieManageSampleWidget();
$sample->handleRequest();
?>