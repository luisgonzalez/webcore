<?php
/**
 * @package WebCore
 * @subpackage Rss
 * @version 1.0
 *
 * Provides easy RSS creation
 * 
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @author Mario Di Vece <mario@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.html.php";
require_once "webcore.web.php";
require_once "webcore.view.php";

/**
 * Interface to create a RSS Item
 *
 * @package WebCore
 * @subpackage Rss
 */
interface IRssItem extends IObject
{
    public function getTitle();
    public function getLink();
    public function getDescription();
    public function getPubDate();
}

/**
 * Represents a RSS Item
 *
 * @package WebCore
 * @subpackage Rss
 */
class RssItem extends SerializableObjectBase implements IRssItem
{
    private $title;
    private $link;
    private $description;
    private $pubDate;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $title
     * @param string $link
     */
    public function __construct($title, $link)
    {
        $this->title   = $title;
        $this->link    = $link;
        $this->pubDate = time();
    }
    
    /**
     * Gets title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Gets link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
    
    /**
     * Gets description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Sets title
     *
     * @param string $value
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }
    
    /**
     * Sets publication date
     *
     * @param string $value
     */
    public function setPubDate($value)
    {
        $this->pubDate = strtotime($value);
    }
    
    public function getPubDate()
    {
        return date('r', $this->pubDate);
    }
    
    /**
     * Sets link
     *
     * @param string $value
     */
    public function setLink($value)
    {
        $this->link = $value;
    }
    
    public function setDescription($value)
    {
        $this->description = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RssItem
     */
    public static function createInstance()
    {
        return new RssItem('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a RSS Item using a defined Entity
 *
 * @package WebCore
 * @subpackage Rss
 */
class RssEntityWrapper extends SerializableObjectBase implements IRssItem
{
    /**
     * @var Popo
     */
    private $entity;
    
    /**
     * Creates a new instance of this class
     *
     * @param Popo $entity
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
    
    /**
     * Gets title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->entity->title;
    }
    
    /**
     * Gets link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->entity->link;
    }
    
    /**
     * Gets description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->entity->description;
    }
    
    /**
     * Gets publication date
     *
     * @return string
     */
    public function getPubDate()
    {
        if ($this->entity->hasField('pubdata'))
        {
            $time = strtotime($this->entity->pubdate);
            
            return date('r', $time);
        }
        
        return date('r');
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RssEntityWrapper
     */
    public static function createInstance()
    {
        return new RssEntityWrapper(null);
    }
}

/**
 * Represents as RSS Feed
 *
 * @package WebCore
 * @subpackage Rss
 */
class RssFeedModel extends ModelBase implements IRootModel, IBindingTarget
{
    private $title;
    private $link;
    private $description;
    private $pubDate;
    /**
     * @var IndexedCollection
     */
    private $items;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $title
     */
    public function __construct($title)
    {
        if (!is_string($title) || $title == '')
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = title");
        
        $this->title = $title;
        $this->link  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $this->items = new IndexedCollection();
        
        $this->pubDate = time();
    }
    
    /**
     * Databinds item to feed
     *
     * @param mixed $dataSource
     */
    public function dataBind(&$dataSource)
    {
        if (ObjectIntrospector::isA($dataSource, 'IDataTableAdapter'))
        {
            $rows = $dataSource->selectNew();
            
            foreach ($rows as $row)
            {
                $rssEntity = new RssEntityWrapper($row);
                $this->items->addItem($rssEntity);
            }
        }
        else
        {
            $this->items = $dataSource;
        }
    }
    
    /**
     * Returns RSS items
     *
     * @return IndexedCollection
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * Sets publication date
     *
     * @param string $value
     */
    public function setPubDate($value)
    {
        $this->pubDate = strtotime($value);
    }
    
    /**
     * Gets the publication date
     *
     * @return string
     */
    public function getPubDate()
    {
        return date('r', $this->pubDate);
    }
    
    /**
     * Gets the title for the feed
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Sets the title to display for the feed
     *
     * @param string $value
     */
    public function setTitle($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->title = $value;
    }
    
    /**
     * Gets the link for the feed
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
    
    /**
     * Sets the link to display for the feed
     *
     * @param string $value
     */
    public function setLink($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->link = $value;
    }
    
    /**
     * Gets the description for the feed
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Sets the description to display for the feed
     *
     * @param string $value
     */
    public function setDescription($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->description = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return RssFeedModel
     */
    public static function createInstance()
    {
        return new RssFeedModel();
    }
}

/**
 * Defines a basic RSS 2.0 render
 *
 * @package WebCore
 * @subpackage Rss
 */
class RssFeedView extends ObjectBase implements IRenderable
{
    /**
     * @var RssFeedModel
     */
    private $model;
    private $url;
    
    /**
     * Creates a new instance of this class
     *
     * @param RssFeedModel $model
     */
    public function __construct($model, $url = '')
    {
        if (is_null($model))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = model');
        
        $this->model = $model;
        $this->url = ($url == '') ? $this->model->getLink() : $url;
    }
    
    /**
     * Gets the RssFeedModel that this instance renders
     *
     * @return RssFeedModel
     */
    public function &getModel()
    {
        return $this->model;
    }
    
    /**
     * Renders the RSS Feed
     *
     */
    public function render()
    {
        header('Content-type: text/xml; charset=utf-8');
        ob_start();
        
        echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\r\n";
        
        $tw = HtmlWriter::getInstance();
        
        $tw->openrss();
        $tw->addAttribute('version', '2.0');
        $tw->addAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        
        $tw->openchannel();
        
        $tw->opentitle();
        $tw->writeRaw($this->model->getTitle());
        $tw->closetitle();
        $tw->openTag('pubDate');
        $tw->writeRaw($this->model->getPubDate());
        $tw->closeTag('pubDate');
        $tw->openlink();
        $tw->writeRaw($this->model->getLink());
        $tw->closeLink();
        $tw->opendescription();
        $tw->writeRaw($this->model->getDescription());
        $tw->closedescription();
        
        $tw->openTag('atom:link');
        $tw->addAttribute('href', $this->url);
        $tw->addAttribute('rel', 'self');
        $tw->addAttribute('type', 'application/rss+xml');
        $tw->closeTag('atom:link');
        
        $tw->opengenerator();
        $tw->writeRaw('Webcore 3');
        $tw->closegenerator();
        $tw->openlanguage();
        $tw->writeRaw(Resources::getCulture());
        $tw->closelanguage();
        
        foreach ($this->model->getItems() as $item)
        {
            $tw->openitem();
            
            $tw->opentitle();
            $tw->writeRaw($item->getTitle());
            $tw->closetitle();
            $tw->openTag('pubDate');
            $tw->writeRaw($item->getPubDate());
            $tw->closeTag('pubDate');
            $tw->openlink();
            $tw->writeRaw($item->getLink());
            $tw->closeLink();
            $tw->opendescription();
            $tw->writeRaw($item->getDescription());
            $tw->closedescription();
            
            $tw->openguid();
            $tw->writeRaw($item->getLink() . "#" . HashProvider::getHash($item->getTitle(), HashProvider::ALGORITHM_MD5));
            $tw->closeguid();
            
            $tw->closeitem();
        }
        
        $tw->closechannel();
        
        $tw->closerss();
        
        $content = ob_get_contents();
        ob_clean();
        echo utf8_encode($content);
    }
}
?>