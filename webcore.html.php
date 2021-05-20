<?php
/**
 * @package WebCore
 * @subpackage Html
 * @version 1.0
 * 
 * Provides classes to control the output of HTML-compliant code.
 * The MarkupWriter class is the main class of this namespace.
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.logging.php";
require_once "webcore.compression.php";

/**
 * Represents a generic XML tag writer which directly writes to the output buffer
 *
 * @package WebCore
 * @subpackage Html
 */
class MarkupWriter extends HelperBase implements ISingleton
{
    
    // STRICT XHTML 1.1 -- Recommended
    const DTD_XHTML_STRICT = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
    // TRANSITIONAL XHTML 1.0
    const DTD_XHTML_TRANSITIONAL = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
    // TRANSITIONAL XHTML 1.0 FRAMESET
    const DTD_XHTML_FRAMESET = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">";
    
    /**
     * @var IndexedCollection
     */
    protected $tagStack;
    protected $indentEnabled;
    protected $deferOutput;
    protected $firstExecution;
    /**
     * @var MarkupWriter
     */
    protected static $__instance = null;
    
    /**
     * Gets the singleton instance for this class.
     *
     * @return MarkupWriter
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new MarkupWriter(false);
        
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
    
    /**
     * Creates a new instance of this class
     *
     * @param bool $deferOutput
     */
    protected function __construct($deferOutput)
    {
        $this->tagStack       = new IndexedCollection();
        $this->indentEnabled  = LogManager::isDebug();
        $this->deferOutput    = $deferOutput;
        $this->firstExecution = true;
        
        if ($this->deferOutput)
            HttpResponse::outputBufferStart();
    }
    
    /**
     * This is used for openX and closeX
     *
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        // Open tag
        switch (substr($method, 0, 4))
        {
            case 'open':
                $tagName = strtolower(substr($method, 4));
                $this->openTag($tagName);
                return;
                break;
            case 'clos':
                $tagName = strtolower(substr($method, 5));
                
                if ($this->getCurrentTag()->getTagName() == $tagName)
                {
                    $fullClose = false;
                    if (count($arguments) > 0 && $arguments[0] === true)
                        $fullClose = true;
                    
                    $this->closeTag($fullClose);
                    return;
                }
                
                throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The current tag does not match the close tag command.');
                break;
        }
        
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The dynamic method is either malformed or does not exist');
    }
    
    /**
     * Opens a new tag and pushes it into the tag stack.
     *
     * @param string $tagName
     */
    public function openTag($tagName)
    {
        if (is_null($this->getCurrentTag()) === false)
        {
            if ($this->getCurrentTag()->getHasContent() === false)
            {
                echo '>';
                $this->getCurrentTag()->setHasContent(true);
            }
        }
        
        $markupTag = new MarkupWriterTagState($tagName);
        $this->tagStack->addItem($markupTag);
        
        if ($this->indentEnabled && $this->firstExecution == false)
            echo "\r\n", str_repeat(' ', $this->tagStack->getCount() * 4);
        
        if ($this->firstExecution)
            $this->firstExecution = false;
        
        echo '<', $tagName;
    }
    
    /**
     * Adds an attribute to the current opened tag befor its content has begun.
     *
     * @param string $attributeName
     * @param string $attributeValue
     */
    public function addAttribute($attributeName, $attributeValue)
    {
        if ($this->getCurrentTag()->getHasContent() === true)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'Tag content has already started.');
        
        $this->getCurrentTag()->setHasAttributes(true);
        
        if (StringHelper::isUTF8($attributeValue)) $attributeValue = utf8_decode($attributeValue);
        
        echo ' ', $attributeName, '="', htmlentities($attributeValue), '"';
    }
    
    /**
     * Adds an attribute to the current opened tag befor its content has begun.
     *
     * @param string $attributeName
     * @param string $attributeValue
     */
    public function addAttributeRaw($attributeName, $attributeValue)
    {
        if ($this->getCurrentTag()->getHasContent() === true)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'Tag content has already started.');
        
        $this->getCurrentTag()->setHasAttributes(true);
        
        echo ' ' . $attributeName . '="' . $attributeValue . '"';
    }
    
    /**
     * Wrties html-encoded content into the tag.
     *
     * @param string $contentText The raw text to write. Conent will be encoded as HTML.
     * @param bool $indent Determines whether or not to indent the content.
     * @param bool $useNlToBr Determines whether or not to encode new lines to br tags.
     * @param bool $useSpToNbsp Determines whether or not to encode spaces into non-breaking-spaces.
     */
    public function writeContent($contentText, $indent = true, $useNlToBr = true, $useSpToNbsp = true)
    {
        if ($this->getCurrentTag()->getHasContent() !== true)
        {
            echo '>';
            
            if ($this->indentEnabled && $indent)
                echo "\r\n", str_repeat(' ', ($this->tagStack->getCount() + 1) * 4);
            
            $this->getCurrentTag()->setHasContent(true);
        }
        
        echo self::htmlEncode($contentText, $useNlToBr, $useSpToNbsp);
    }
    
    /**
     * Writes content inside the tag without encoding it.
     * Writing tag content with this method is not recommended.
     *
     * @param string $rawText
     */
    public function writeRaw($rawText)
    {
        if (is_null($this->getCurrentTag()) !== true && $this->getCurrentTag()->getHasContent() !== true)
        {
            echo '>';
            $this->getCurrentTag()->setHasContent(true);
        }
        
        echo $rawText;
    }
    
    /**
     * Closes the tag and pops it out of the tag stack.
     *
     * @param bool $fullClose
     */
    public function closeTag($fullClose = false, $indent = true)
    {
        if ($this->getCurrentTag()->getHasContent() === true)
        {
            if ($this->indentEnabled && $this->getCurrentTag()->getTagName() != 'textarea' && $indent === true)
                echo "\r\n", str_repeat(' ', $this->tagStack->getCount() * 4);
            
            echo '</' . $this->getCurrentTag()->getTagName() . '>';
        }
        else
        {
            if ($fullClose === false)
                echo ' />';
            else
                echo '></', $this->getCurrentTag()->getTagName(), '>';
        }
        
        $this->tagStack->removeAt($this->tagStack->getCount() - 1);
    }
    
    /**
     * Gets the current tag object
     *
     * @return MarkupWriterTagState
     */
    public function getCurrentTag()
    {
        if ($this->tagStack->getCount() > 0)
            return $this->tagStack->getItem($this->tagStack->getCount() - 1);
        
        return null;
    }
    
    /**
     * Returns outpu buffer
     */
    public function getOutput()
    {
        if ($this->deferOutput)
        {
            $content = HttpResponse::getOutputBuffer();
            HttpResponse::clearOutputBuffer();
            return $content;
        }
    }
    
    /**
     * Encodes the content for HTML output.
     *
     * @param string $content
     * @param bool $useNlToBr Determines whther new new lines are converted to HTML line breaks.
     * @param bool $useSpToNbsp Determines whether spaces are converted to HTML non-breaking spaces.
     * @return string
     */
    public static function htmlEncode($content, $useNlToBr = true, $useSpToNbsp = true)
    {
        if (StringHelper::isUTF8($content)) $content = utf8_decode($content);
        
        $returnStr = htmlentities($content);
        if ($useSpToNbsp == true)
            $returnStr = str_replace(' ', '&nbsp;', $returnStr);
        if ($useNlToBr == true)
            $returnStr = nl2br($returnStr);
        return $returnStr;
    }
}
/**
 * Represents MarkupWriter specifically designed for writing common HTML tags to the output buffer.
 * 
 * @package WebCore
 * @subpackage Html
 */
class HtmlWriter extends MarkupWriter
{
    /**
     * Gets the singleton instance for this class.
     *
     * @return HtmlWriter
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new HtmlWriter(false);
        
        return self::$__instance;
    }
    
    /**
     * Helper method to run a javascript block of code.
     * @param string $javascript
     */
    public function renderJavascriptBlock($javascript)
    {
        $this->openScript();
        $this->addAttribute('defer', 'defer');
        $this->addAttribute('type', 'text/javascript');
        $this->writeRaw($javascript);
        $this->closeScript(true);
    }
    
    /**
     * Html tag-closing mechanism which checks for the correct closing tag.
     * @param string $tagName The intended tag to close. If the closing tag provided does not match the current tag in the stack, this method will throw an exception.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    protected function closeHtmlTag($tagName, $fullClose = false, $indent = true)
    {
        if ($this->getCurrentTag()->getTagName() !== $tagName)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The current tag \'' . $this->getCurrentTag()->getTagName() . '\' does not match the close tag \'' . $tagName . '\' command.');
        
        parent::closeTag($fullClose, $indent);
    }
    
    public function writeDiv($content, $indent = true, $useNlToBr = true, $useSpToNbsp = true)
    {
        $this->openTag('div');
        $this->writeContent($content);
        $this->closeTag(true);
    }
    
    /**
     * Opens the given HTML tag.
     */
    public function openDiv()
    {
        $this->openTag('div');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openTr()
    {
        $this->openTag('tr');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openTd()
    {
        $this->openTag('td');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openTable()
    {
        $this->openTag('table');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openSpan()
    {
        $this->openTag('span');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openInput()
    {
        $this->openTag('input');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openLink()
    {
        $this->openTag('link');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openA()
    {
        $this->openTag('a');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openScript()
    {
        $this->openTag('script');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openHtml()
    {
        $this->openTag('html');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openHead()
    {
        $this->openTag('head');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openBody()
    {
        $this->openTag('body');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openForm()
    {
        $this->openTag('form');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openTextArea()
    {
        $this->openTag('textarea');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openLabel()
    {
        $this->openTag('label');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openSelect()
    {
        $this->openTag('select');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openOption()
    {
        $this->openTag('option');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openImg()
    {
        $this->openTag('img');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openUl()
    {
        $this->openTag('ul');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openLi()
    {
        $this->openTag('li');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openButton()
    {
        $this->openTag('button');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openStyle()
    {
        $this->openTag('style');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openThead()
    {
        $this->openTag('thead');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openTbody()
    {
        $this->openTag('tbody');
    }
    /**
     * Opens the given HTML tag.
     */
    public function openOptgroup()
    {
        $this->openTag('optgroup');
    }
    
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeDiv($fullClose = false)
    {
        $this->closeHtmlTag('div', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeTr($fullClose = true)
    {
        $this->closeHtmlTag('tr', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeTd($fullClose = true)
    {
        $this->closeHtmlTag('td', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeTable($fullClose = true)
    {
        $this->closeHtmlTag('table', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeSpan($fullClose = false, $indent = false)
    {
        $this->closeHtmlTag('span', $fullClose, $indent);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeInput($fullClose = true)
    {
        $this->closeHtmlTag('input', false);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeLink($fullClose = false)
    {
        $this->closeHtmlTag('link', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeA($fullClose = false, $indent = false)
    {
        $this->closeHtmlTag('a', $fullClose, $indent);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeScript($fullClose = false)
    {
        $this->closeHtmlTag('script', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeHtml($fullClose = false)
    {
        $this->closeHtmlTag('html', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeHead($fullClose = false)
    {
        $this->closeHtmlTag('head', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeBody($fullClose = false)
    {
        $this->closeHtmlTag('body', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeForm($fullClose = false)
    {
        $this->closeHtmlTag('form', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeTextArea($fullClose = false)
    {
        $this->closeHtmlTag('textarea', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeLabel($fullClose = false)
    {
        $this->closeHtmlTag('label', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeSelect($fullClose = true)
    {
        $this->closeHtmlTag('select', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeOption($fullClose = false)
    {
        $this->closeHtmlTag('option', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeImg($fullClose = false)
    {
        $this->closeHtmlTag('img', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeUl($fullClose = false)
    {
        $this->closeHtmlTag('ul', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeLi($fullClose = false)
    {
        $this->closeHtmlTag('li', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeButton($fullClose = false)
    {
        $this->closeHtmlTag('button', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeStyle($fullClose = false)
    {
        $this->closeHtmlTag('style', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeThead($fullClose = false)
    {
        $this->closeHtmlTag('thead', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeTbody($fullClose = true)
    {
        $this->closeHtmlTag('tbody', $fullClose);
    }
    /**
     * Closes the given HTML tag.
     * @param bool $fullClose Determines whether the tag writer should output the full closing tag. If true and tag has no content, this will output self-closing markup for the gien tag.
     */
    public function closeOptgroup($fullClose = false)
    {
        $this->closeHtmlTag('optgroup', $fullClose);
    }
}

/**
 * Represents an HTML dependency usually injected in the <head> section of the document.
 * Use the HtmlViewManager to register dependencies.
 *
 * @package WebCore
 * @subpackage Html
 */
class HtmlDependency extends ObjectBase
{
    const TYPE_JS_FILE = 0;
    const TYPE_JS_BLOCK = 1;
    const TYPE_CSS_FILE = 2;
    const TYPE_CSS_BLOCK = 3;
    
    protected $type;
    protected $source;
    protected $content;
    
    /**
     * Creates a new instance of this class.
     *
     * @param int $type The type constant defined within this class.
     * @param string $source The identifier of the object that created this dependency.
     * @param string $content The absolute virtual path to the dependency or the text contents of it.
     */
    public function __construct($type, $source, &$content)
    {
        if (is_string($source) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = source');
        if (is_string($content) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = content');
        if (is_int($type) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = type');
        
        $this->type    = $type;
        $this->source  = $source;
        $this->content = $content;
    }
    
    /**
     * Gets the type of the dependency according to the constants defined in this class.
     *
     * @return int
     */
    public function getDependencyType()
    {
        return $this->type;
    }
    
    /**
     * Gets the absolute virtual path to the dependency or the text contents of it.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Gets the absolute virtual path to the dependency or the text contents of it.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}

/**
 * Registers and renders page resource dependencies such as css stylesheets, and javascript code.
 * The render method should be called inside the <head> section of the HTML.
 *
 * @package WebCore
 * @subpackage Html
 */
class HtmlViewManager extends ObjectBase
{
    /**
     * @var KeyedCollection
     */
    protected static $dependencies;
    protected static $hasRendered;
    
    /**
     * Registers a dependency.
     *
     * @param $type The dependency type constant defined in the HtmlDependency class.
     * @param $source The name of the class registering the dependency.
     * @param $id The unique identifier of the dependency.
     * @param $content The absolute virtual path or the string content of the dependency.
     */
    public static function registerDependency($type, $source, $id, &$content)
    {
        if (is_string($id) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = $id');
        
        $dependency = new HtmlDependency($type, $source, $content);
        self::getDependencyCollection()->setValue($id, $dependency);
    }
    
    /**
     * Registers a CSS stylesheet file
     * @param string $path The relative path to the stylesheet file with respect to HttpContext::getApplicationRoot(). example: stylesheets/styles.css
     */
    public static function registerCssFile($path)
    {
        $id = $path;
        $filePath = HttpContext::getApplicationRoot() . $path;
        self::registerDependency(HtmlDependency::TYPE_CSS_FILE, __CLASS__, $id, $filePath);
    }
    
    /**
     * Registers a Javascript stylesheet file
     * @param string $path The relative path to the stylesheet file with respect to HttpContext::getApplicationRoot(). example: javascripts/functions.js
     */
    public static function registerJavascriptFile($path)
    {
        $id = $path;
        $filePath = HttpContext::getApplicationRoot() . $path;
        self::registerDependency(HtmlDependency::TYPE_JS_FILE, __CLASS__, $id, $filePath);
    }
    
    /**
     * Gets the underlying dependency collection.
     *
     * @return KeyedCollection
     */
    public static function &getDependencyCollection()
    {
        if (is_null(self::$dependencies) == true)
            self::$dependencies = new KeyedCollection();
        
        return self::$dependencies;
    }
    
    /**
     * Gets a collection of dependencies
     *
     * @param int $type
     * @param string $source
     * @return KeyedCollection
     */
    public static function &getDependencies($type, $source = '')
    {
        $filteredCollection = new KeyedCollection();
        $depKeys            = self::getDependencyCollection()->getKeys();
        
        foreach ($depKeys as $key)
        {
            $dep = self::$dependencies->getValue($key);
            
            if ($dep->getDependencyType() === $type && ($source == '' || $dep->getSource() === $source))
                $filteredCollection->setValue($key, $dep);
        }
        
        return $filteredCollection;
    }
    
    /**
     * Gets a dependency by id.
     *
     * @return HtmlDependency
     */
    public static function getDependency($id)
    {
        if (self::getDependencyCollection()->keyExists($id))
            return self::getDependencyCollection()->getValue($id);
        
        return null;
    }
    
    /**
     * Determines if the dependencies have been renderered.
     * Useful for views to check whether their own dependencies have been rendered.
     *
     * @return bool
     */
    public static function hasRendered()
    {
        return (self::$hasRendered === true) ? true : false;
    }
    
    /**
     * Renders the registered HTML resources.
     * This method should be called statically inside the <head> section of the HTML document.
     *
     */
    public static function render()
    {
        $settings = Settings::getValue(Settings::SKEY_COMPRESSION);
        
        if ($settings[Settings::KEY_COMPRESSION_ENABLED] == 1)
            self::renderCompressed();
        else
            self::renderSimple();
        
        self::$hasRendered = true;
    }
    
    /**
     * Renders the registered HTML resources using ResourcesCompressor
     * 
     */
    protected static function renderCompressed()
    {
        $tw         = HtmlWriter::getInstance();
        $compressor = ResourcesCompressor::getInstance();
        
        $cssContent = "";
        $cssFiles   = array();
        
        $deps = self::getDependencies(HtmlDependency::TYPE_CSS_FILE);
        
        foreach ($deps as $dep)
            $cssFiles[] = $dep->getContent();
        
        $deps = self::getDependencies(HtmlDependency::TYPE_CSS_BLOCK);
        
        if ($deps->getCount() > 0)
        {
            foreach ($deps as $dep)
                $cssContent .= $dep->getContent() . "\r\n";
        }
        
        $cssLink = $compressor->compressResources($cssFiles, $cssContent, '.css');
        
        self::renderCommonJscript();
        
        $tw->openLink();
        $tw->addAttribute('type', 'text/css');
        $tw->addAttribute('rel', 'stylesheet');
        $tw->addAttributeRaw('href', $cssLink);
        $tw->closeLink();
        
        $jsContent = "";
        $jsFiles   = array();
        
        $deps = self::getDependencies(HtmlDependency::TYPE_JS_FILE);
        
        foreach ($deps as $dep)
        {
            if (strstr($dep->getContent(), 'http:') || strstr($dep->getContent(), 'https:'))
            {
                $tw->openScript();
                $tw->addAttribute('type', 'text/javascript');
                $tw->addAttribute('src', $dep->getContent());
                $tw->closeScript(true);
            }
            else
            {
                $jsFiles[] = $dep->getContent();
            }
        }
        
        $deps = self::getDependencies(HtmlDependency::TYPE_JS_BLOCK);
        
        if ($deps->getCount() > 0)
        {
            foreach ($deps as $dep)
                $jsContent .= $dep->getContent() . "\r\n";
        }
        
        $jsLink = $compressor->compressResources($jsFiles, $jsContent, '.js');
        
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->addAttribute('src', $jsLink);
        $tw->closeScript(true);
    }
    
    /**
     * Renders common Javascript files
     * 
     */
    protected static function renderCommonJscript()
    {
        $tw = HtmlWriter::getInstance();
        
        $isDebugJs = "false";
        
        if (LogManager::isDebugInClient())
        {
            $firebugPath    = HttpContext::getLibraryRoot() . 'js/firebug-lite-compressed.js';
            $firebugCSSPath = HttpContext::getLibraryRoot() . 'css/firebug-lite.css';
            
            $tw->openLink();
            $tw->addAttribute('type', 'text/css');
            $tw->addAttribute('rel', 'stylesheet');
            $tw->addAttribute('href', $firebugCSSPath);
            $tw->closeLink();
            
            $tw->openScript();
            $tw->addAttribute('type', 'text/javascript');
            $tw->addAttribute('src', $firebugPath);
            $tw->closeScript(true);
            
            $isDebugJs = "true";
        }
        
        $controllerPath = HttpContext::getLibraryRoot() . 'js/std.controller.js';
        
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->addAttribute('src', $controllerPath);
        $tw->closeScript(true);
        
        $tw->openScript();
        $tw->addAttribute('type', 'text/javascript');
        $tw->writeRaw("var controller = new Controller();\r\n");
        $tw->writeRaw("controller.isDebugEnabled = $isDebugJs;\r\n");
        $tw->writeRaw("controller.language = '" . Resources::getCulture() . "';");
        
        if (LogManager::isDebugInClient())
        {
            $logs = Controller::getLogsBuffer();
            
            if ($logs != null)
                $tw->writeRaw($logs->implode("%s", "\r\n"));
        }
        
        $tw->closeScript();
    }
    
    /**
     * Renders simple HTML dependecies
     *
     */
    protected static function renderSimple()
    {
        $tw = HtmlWriter::getInstance();
        
        // Css files
        $deps = self::getDependencies(HtmlDependency::TYPE_CSS_FILE);
        foreach ($deps as $dep)
        {
            $tw->openLink();
            $tw->addAttribute('type', 'text/css');
            $tw->addAttribute('rel', 'stylesheet');
            $tw->addAttribute('href', $dep->getContent());
            $tw->closeLink();
        }
        
        // Css blocks
        $deps = self::getDependencies(HtmlDependency::TYPE_CSS_BLOCK);
        if ($deps->getCount() > 0)
        {
            $tw->openStyle();
            $tw->addAttribute('type', 'text/css');
            
            foreach ($deps as $dep)
                $tw->writeRaw($dep->getContent() . "\r\n");
            
            $tw->closeStyle(true);
        }
        
        self::renderCommonJscript();
        
        // Javascript files
        $deps = self::getDependencies(HtmlDependency::TYPE_JS_FILE);
        foreach ($deps as $dep)
        {
            $tw->openScript();
            $tw->addAttribute('type', 'text/javascript');
            $tw->addAttribute('src', $dep->getContent());
            $tw->closeScript(true);
        }
        
        // Javascript blocks
        $deps = self::getDependencies(HtmlDependency::TYPE_JS_BLOCK);
        if ($deps->getCount() > 0)
        {
            $tw->openScript();
            $tw->addAttribute('type', 'text/javascript');
            
            foreach ($deps as $dep)
                $tw->writeRaw($dep->getContent() . "\r\n");
            
            $tw->closeScript(true);
        }
    }
}

/**
 * Represents a state in the MarkupWriter stack to enable it to output valid markup.
 *  
 * @package WebCore
 * @subpackage Html
 */
class MarkupWriterTagState extends ObjectBase
{
    protected $tagName;
    protected $hasContent;
    protected $hasAttributes;
    
    /**
     * Create a new instance of this class
     *
     * @param string $tagName
     */
    public function __construct($tagName)
    {
        $this->tagName       = $tagName;
        $this->hasAttributes = false;
        $this->hasContent    = false;
    }
    
    /**
     * Sets tag name
     *
     * @param string $value
     */
    public function setTagName($value)
    {
        $this->tagName = $value;
    }
    
    /**
     * Gets tag name
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->tagName;
    }
    
    /**
     * Returns true if tag has attributes
     *
     * @return bool
     */
    public function getHasAttributes()
    {
        return $this->hasAttributes;
    }
    
    /**
     * Sets true if tag has attributes
     *
     * @param bool $value
     */
    public function setHasAttributes($value)
    {
        $this->hasAttributes = $value;
    }
    
    /**
     * Returns true if tag has content
     *
     * @return bool
     */
    public function getHasContent()
    {
        return $this->hasContent;
    }
    
    /**
     * Sets true if tag has content
     *
     * @param bool $value
     */
    public function setHasContent($value)
    {
        $this->hasContent = $value;
    }
}
?>