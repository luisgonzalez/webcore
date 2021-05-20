<?php
/**
 * @todo Finish this
 * @package WebCore
 * @subpackage Soap
 */
class WsdlWriter extends MarkupWriter
{
    /**
     * Gets the singleton instance for this class.
     *
     * @return WsdlWriter
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new MarkupWriter(true);
        
        return self::$__instance;
    }
}

/**
 * @todo Finish this
 * @package WebCore
 * @subpackage Soap
 */
class PHPParser extends HelperBase implements IHelper
{
    /**
     * Array with all the files to be parsed
     *
     * @var array
     */
    private $files = array();
    
    /**
     * Array holding all the classes
     *
     * @var array
     */
    private $classes = array();
    
    /**
     * Array holding all the classes variables
     *
     * @var array
     */
    private $classesVars = array();
    
    /**
     * Array holding all the data
     *
     * @var array
     */
    private $allData = array();
    
    /**
     * Current class that is parsed
     *
     * @var string
     */
    private $currentClass;
    
    /**
     * The latest comment found for a method
     *
     * @var string
     */
    private $currentMethodComment;
    
    /**
     * The latest type found for a method
     *
     * @var string
     */
    private $currentMethodType;
    /**
     * The latest method found for a class
     *
     * @var string
     */
    private $currentMethod;
    /**
     * Latest parameters found for a method
     *
     * @var array
     */
    private $currentParams = array();
    
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }
    
    /**
     * Return the next token resulted alfter token_get_all()
     *
     * @return array
     */
    private function getNextToken()
    {
        if (is_array($this->allData) == false)
            return false;
        
        while ($c = next($this->allData))
        {
            if (!is_array($c) || $c[0] == T_WHITESPACE)
                continue;
            break;
        }
        
        return current($this->allData);
    }
    
    /**
     * Get next token with a type
     *
     * @param integer $type
     * @return array
     */
    private function getNextTokenWithType($type)
    {
        while ($current = $this->getNextToken())
            if ($current[0] == $type)
                return current($this->allData);
    }
    
    /**
     * Parse a file
     * It gets the data from $this->all_data
     *
     */
    private function parseFile()
    {
        $lookForClassVariables = true; // When this will be set as false we will not look for class variables because a function was defined
        $executionPrsed        = array();
        
        while ($token = $this->getNextToken())
        {
            if ($token[0] == T_CLASS)
            // T_CLASS
            {
                $lookForClassVariables      = true;
                $className                  = $this->getNextTokenWithType(T_STRING);
                $this->currentClass         = $className[1];
                $this->currentMethodComment = $this->currentMethodType = $this->currentMethod = $this->currentParams = null;
                $executionPrsed[]           = 1;
                continue;
            }
            
            if ($lookForClassVariables === true && ($token[0] == T_VARIABLE || $token[0] == T_VAR) && $this->currentClass != null)
            {
                $varName                                          = substr($token[1], 1);
                $doc                                              = new ReflectionProperty($this->currentClass, $varName);
                $doc                                              = $doc->getDocComment();
                $doc                                              = $this->parseComment($doc);
                $this->classesVars[$this->currentClass][$varName] = $doc['vartype'];
                $executionPrsed[]                                 = 2;
                continue;
            }
            
            if ($token[0] == T_DOC_COMMENT)
            // T_DOC_COMMENT
            {
                $nt = $this->getNextToken();
                
                if (($nt[0] == T_PUBLIC || $nt[0] == T_PROTECTED || $nt[0] == T_PRIVATE || $nt[0] == T_FINAL || $nt[0] == T_ABSTRACT || $nt[0] == T_STATIC) || $nt[0] == T_FUNCTION)
                // public | protected | private | final | abstract | static | function
                {
                    $this->currentMethodComment = $token[1];
                    $this->currentMethod        = null;
                    $this->currentParams        = null;
                    prev($this->allData);
                    $executionPrsed[] = 3;
                    continue;
                }
            }
            
            if ($token[0] == T_PUBLIC || $token[0] == T_PROTECTED || $token[0] == T_PRIVATE || $token[0] == T_FINAL || $token[0] == T_ABSTRACT || $token[0] == T_STATIC)
            // public | protected | private | final | abstract | static
            {
                $this->currentMethodType = $token[1];
                $this->currentMethod     = $this->currentParams = null;
                $executionPrsed[]        = 4;
                continue;
            }
            
            if ($token[0] == T_FUNCTION)
            // T_FUNCTION
            {
                $lookForClassVariables = false;
                $f                     = $this->getNextTokenWithType(T_STRING);
                $this->currentMethod   = $f[1];
                $this->currentParams   = null;
                $this->getNextToken(); //
                prev($this->allData); // get rid of white space
                prev($this->allData); //
                if (next($this->allData) == "(")
                {
                    while (($p = next($this->allData)) != ")")
                    {
                        if ($p[0] == T_VARIABLE || $p[0] == T_VAR)
                        // T_VARIABLE
                        {
                            $this->currentParams[] = $p[1];
                        }
                    }
                }
                
                $executionPrsed[] = 5;
            }
            
            if ($this->currentClass && $this->currentMethod)
            {
                $this->classes[$this->currentClass][$this->currentMethod]["comment"] = $this->currentMethodComment;
                if ($this->currentMethod == null)
                    $this->currentMethod = "public";
                $this->classes[$this->currentClass][$this->currentMethod]["type"]   = $this->currentMethodType;
                $this->classes[$this->currentClass][$this->currentMethod]["params"] = $this->currentParams;
                $this->currentMethodComment                                         = $this->currentMethodType = $this->currentMethod = $this->currentParams = null;
            }
        }
    }
    
    /**
     * Parse a comment
     * Extracts description, parameters type and return type
     *
     * @param string $comment
     * @return array
     */
    private function parseComment($comment)
    {
        $comment = trim($comment);
        
        if (strpos($comment, "/*") === 0 && strripos($comment, "*/") === strlen($comment) - 2)
        {
            $lines       = preg_split("(\n\r|\r\n\|r|\n)", $comment);
            $description = "";
            $returntype  = "";
            $varType     = "";
            $params      = array();
            
            while (next($lines))
            {
                $line = trim(current($lines));
                $line = trim(substr($line, strpos($line, "* ") + 2));
                
                if (isset($line[0]) && $line[0] == "@")
                {
                    $parts = explode(" ", $line);
                    
                    if ($parts[0] == "@return")
                    {
                        $returntype = $parts[1];
                    }
                    elseif ($parts[0] == "@param")
                    {
                        $params[$parts[2]] = $parts[1];
                    }
                    elseif ($parts[0] == "@var")
                    {
                        $varType = $parts[1];
                    }
                }
                else
                {
                    $description .= trim("\n" . $line);
                }
            }
            
            $comment = array(
                "description" => $description,
                "params" => $params,
                "return" => $returntype,
                "vartype" => $varType
            );
            return $comment;
        }
        
        return "";
    }
    
    /**
     * Parse the classes
     *
     */
    private function parseClasses()
    {
        $classes       = $this->classes;
        $this->classes = array();
        
        foreach ($classes as $class => $methods)
        {
            foreach ($methods as $method => $attributes)
            {
                $this->classes[$class][$method]["type"]        = $attributes["type"];
                $commentParsed                                 = $this->parseComment($attributes["comment"]);
                $this->classes[$class][$method]["returnType"]  = !isset($commentParsed["return"]) ? false : $commentParsed["return"];
                $this->classes[$class][$method]["description"] = isset($commentParsed["description"]) ? $commentParsed["description"] : "";
                
                if (is_array($attributes["params"]))
                {
                    foreach ($attributes["params"] as $param)
                    {
                        $paramName                                                       = substr($param, 1);
                        $this->classes[$class][$method]["params"][$paramName]["varName"] = $param;
                        
                        if (isset($commentParsed["params"][$param]))
                            $this->classes[$class][$method]["params"][$paramName]["varType"] = $commentParsed["params"][$param];
                    }
                }
            }
        }
    }
    
    /**
     * Get all the parsed classes from the files (filtered)
     *
     * @return array
     */
    public function getClasses()
    {
        foreach ($this->files as $file)
        {
            $this->allData = token_get_all(file_get_contents($file));
            $this->parseFile(file_get_contents($file));
        }
        
        $this->parseClasses();
        return $this->classes;
    }
    
    /**
     * Get all the variables of the classes defined in the files
     *
     * @return array
     */
    public function getClassesVars()
    {
        return $this->classesVars;
    }
}

/**
 * @todo Finish this
 * 
 * @package WebCore
 * @subpackage Soap
 */
abstract class SoapServerBase extends HelperBase implements IHelper
{
    /**
     * Object for PHPParser
     *
     * @var PHPParser
     */
    private $PHPParser;
    
    /**
     * Array with internal variable types
     *
     * @var array
     */
    private $xsd = array("string" => "string", "bool" => "boolean", "boolean" => "boolean", "int" => "integer", "integer" => "integer", "double" => "double", "float" => "float", "number" => "float", "array" => "anyType", "resource" => "anyType", "mixed" => "anyType", "unknown_type" => "anyType", "anyType" => "anyType");
    
    /**
     * Array of typens defined by classes that are parsed
     *
     * @var array
     */
    private $typensDefined = array();
    
    /**
     * Array of typens
     *
     * @var array
     */
    private $typens = array();
    private $typeTypens = array();
    
    /**
     * Array of URLs for undefined typens
     *
     * @var array
     */
    private $typensURLS = array();
    
    /**
     * General URL
     *
     * @var string
     */
    public $classesGeneralURL;
    
    /**
     * Array of classes
     *
     * @var array
     */
    private $classes = array();
    
    /**
     * Array of URLs of classes
     *
     * @var array
     */
    private $classesURLS = array();
    
    /**
     * The name of the WSDL
     *
     * @var string
     */
    private $name;
    
    /**
     * The URL of the WSDL
     *
     * @var string
     */
    private $url;
    
    /**
     * Array of messages
     *
     * @var array
     */
    private $messages = array();
    
    /**
     * Array of bindings
     *
     * @var array
     */
    private $bindings = array();
    
    /**
     * Array of services
     *
     * @var array
     */
    private $services = array();
    
    /**
     * Array of parameters of a class
     *
     * @var array
     */
    private $paramsNames = array();
    
    /**
     * Constructor
     *
     * @param string $name
     * @param string $url
     */
    public function __construct($name, $url)
    {
        $name = str_replace(" ", "_", $name);
        
        $this->PHPParser = new PHPParser();
        $this->name      = $name;
        $this->url       = $url;
    }
    
    /**
     * Set an URL for all the classes that do not have an explicit URL set
     *
     * @param string $url
     */
    public function setClassesGeneralURL($url)
    {
        $this->classesGeneralURL = $url;
    }
    
    /**
     * Set an URL for a class
     *
     * @param string $className
     * @param string $url
     */
    public function addURLToClass($className, $url)
    {
        $this->classesURLS[$className] = $url;
    }
    
    /**
     * Set an URL for a variable type (when a variable is an object)
     *
     * @param string $type
     * @param string $url
     */
    public function addURLToTypens($type, $url)
    {
        $this->typensURLS[$type] = $url;
    }
    
    /**
     * Add a typens that is undefined in internal typens
     *
     * @param string $type
     */
    private function addtypens($type)
    {
        static $t = 0;
        
        if (isset($this->typensURLS[$type]))
        {
            $this->typens["typens" . $t] = $this->typensURLS[$type];
            $this->typeTypens[$type]     = "typens" . $t;
            $t++;
        }
        elseif (array_key_exists($type, $this->classes))
        {
            $this->typensDefined[$type] = $type;
        }
        else
        {
            trigger_error("URL for type <b>" . $type . "</b> was not defined", E_USER_ERROR);
        }
    }
    
    /**
     * Create a message for the WSDL
     *
     * @param string $name
     * @param string $returnType
     * @param array $params
     */
    private function createMessage($name, $returnType = false, $params = array())
    {
        $message = new XmlWriter("message");
        $message->addAttribute("name", $name);
        
        if (is_array($params))
        {
            foreach ($params as $pname => $param)
            {
                if (isset($this->paramsNames[$pname]))
                {
                    $pname = $pname . ($this->paramsNames[$pname] + 1);
                }
                else
                {
                    $this->paramsNames[$pname] = 0;
                }
                
                $part = new XmlWriter("part");
                $part->addAttribute("name", $pname);
                $type = isset($param["varType"]) ? $param["varType"] : "anyType";
                
                if (isset($this->xsd[$type]))
                {
                    $type = "xsd:" . $this->xsd[$type];
                }
                else
                {
                    if (isset($this->typeTypens[$type]))
                    {
                        $type = $this->typeTypens[$type] . ":" . $type;
                    }
                    else
                    {
                        $this->addtypens($type);
                        $typens = isset($this->typensDefined[$type]) ? "typens" : $this->typeTypens[$type];
                        $type   = $typens . ":" . $type;
                    }
                }
                
                $part->addAttribute("type", $type);
                $message->addChild($part);
            }
        }
        
        $this->messages[] = $message;
        
        if ($returnType)
        {
            $message = new XmlWriter("message");
            $message->addAttribute("name", $name . "Response");
            $part = new XmlWriter("part");
            $part->addAttribute("name", $name . "Return");
            $type = isset($returnType) ? $returnType : "anyType";
            
            if (isset($this->xsd[$type]))
            {
                $type = "xsd:" . $this->xsd[$type];
            }
            else
            {
                if (isset($this->typeTypens[$type]))
                {
                    $type = $this->typeTypens[$type] . ":" . $type;
                }
                else
                {
                    $this->addtypens($type);
                    $typens = isset($this->typensDefined[$type]) ? "typens" : $this->typeTypens[$type];
                    $type   = $typens . ":" . $type;
                }
            }
            
            $part->addAttribute("type", $type);
            $message->addChild($part);
            $this->messages[] = $message;
        }
        else
        {
            $message = new XmlWriter("message");
            $message->addAttribute("name", $name . "Response");
            $this->messages[] = $message;
        }
    }
    
    /**
     * Create a portType for the WSDL
     *
     * @param array $portTypes
     */
    private function createPortType($portTypes)
    {
        if (is_array($portTypes) == false)
            return;
        
        $ww = WsdlWriter::getInstance();
        
        foreach ($portTypes as $class => $methods)
        {
            $ww->openTag("portType");
            $pt->addAttribute("name", $class . "PortType");
            
            foreach ($methods as $method => $components)
            {
                $op = new XmlWriter("operation");
                $op->addAttribute("name", $method);
                
                if ($components["documentation"])
                {
                    $doc = new XmlWriter("documentation");
                    $doc->setData($components["documentation"]);
                    $op->addChild($doc);
                }
                
                $input = new XmlWriter("input");
                $input->addAttribute("message", "typens:" . $method);
                $op->addChild($input);
                
                $output = new XmlWriter("output");
                $output->addAttribute("message", "typens:" . $method . "Response");
                $op->addChild($output);
                
                $pt->addChild($op);
            }
            
            $ww->closeTag();
        }
    }
    
    /**
     * Create a binding for the WSDL
     *
     * @param array $bindings
     */
    private function createBinding($bindings)
    {
        if (is_array($bindings) == false)
            return;
        
        $b = new XmlWriter("binding");
        foreach ($bindings as $class => $methods)
        {
            $b->addAttribute("name", $class . "Binding");
            $b->addAttribute("type", "typens:" . $class . "PortType");
            $s = new XmlWriter("soap:binding");
            $s->addAttribute("style", "rpc");
            $s->addAttribute("transport", "http://schemas.xmlsoap.org/soap/http");
            $b->addChild($s);
            
            foreach ($methods as $method => $components)
            {
                $op = new XmlWriter("operation");
                $op->addAttribute("name", $method);
                $s = new XmlWriter("soap:operation");
                $s->addAttribute("soapAction", "urn:" . $class . "Action");
                $op->addChild($s);
                
                $input = new XmlWriter("input");
                $s     = new XmlWriter("soap:body");
                $s->addAttribute("namespace", "urn:" . $this->name);
                $s->addAttribute("use", "encoded");
                $s->addAttribute("encodingStyle", "http://schemas.xmlsoap.org/soap/encoding/");
                $input->addChild($s);
                $op->addChild($input);
                
                $output = new XmlWriter("output");
                $output->addChild($s);
                $op->addChild($output);
                $b->addChild($op);
            }
            
            $this->bindings[] = $b;
        }
    }
    
    /**
     * Create a service for the WSDL
     *
     * @param array $services
     */
    private function createService($services)
    {
        if (is_array($services) == false)
            return;
        
        foreach ($services as $class => $methods)
        {
            if (isset($this->classesURLS[$class]) || $this->classesGeneralURL)
            {
                $url  = isset($this->classesURLS[$class]) ? $this->classesURLS[$class] : $this->classesGeneralURL;
                $port = new XmlWriter("port");
                $port->addAttribute("name", $class . "Port");
                $port->addAttribute("binding", "typens:" . $class . "Binding");
                $soap = new XmlWriter("soap:address");
                isset($this->classesURLS[$class]) ? $soap->addAttribute("location", $this->classesURLS[$class]) : "";
                $port->addChild($soap);
            }
            else
            {
                trigger_error("URL for class <b>" . $class . "</b> was not defined", E_USER_ERROR);
            }
        }
        
        $this->services[] = $port;
    }
    
    /**
     * Generate the WSDL
     *
     */
    public function createWSDL()
    {
        $ww = WsdlWriter::getInstance();
        
        $ww->openTag('definitions');
        $ww->addAttribute("name", $this->name);
        $ww->addAttribute("targetNamespace", "urn:" . $this->name);
        $ww->addAttribute("xmlns:typens", "urn:" . $this->name);
        $ww->addAttribute("xmlns:xsd", "http://www.w3.org/2001/XMLSchema");
        $ww->addAttribute("xmlns:soap", "http://schemas.xmlsoap.org/wsdl/soap/");
        $ww->addAttribute("xmlns:soapenc", "http://schemas.xmlsoap.org/soap/encoding/");
        $ww->addAttribute("xmlns:wsdl", "http://schemas.xmlsoap.org/wsdl/");
        $ww->addAttribute("xmlns", "http://schemas.xmlsoap.org/wsdl/");
        
        $this->classes = $this->PHPParser->getClasses();
        
        foreach ($this->classes as $class => $methods)
        {
            $pbs = array();
            ksort($methods);
            
            foreach ($methods as $method => $components)
            {
                if ($components["type"] == "public" || $components["type"] == "")
                {
                    if (array_key_exists("params", $components))
                        $this->createMessage($method, $components["returnType"], $components["params"]);
                    else
                        $this->createMessage($method, $components["returnType"]);
                    
                    $pbs[$class][$method]["documentation"] = $components["description"];
                    $pbs[$class][$method]["input"]         = $method;
                    $pbs[$class][$method]["output"]        = $method;
                }
            }
            
            $this->createPortType($pbs);
            $this->createBinding($pbs);
            $this->createService($pbs);
        }
        
        // adding typens
        foreach ($this->typens as $typenNo => $url)
            $ww->addAttribute("xmlns:" . $typenNo, $url);
        
        // add types
        if (is_array($this->typensDefined) && count($this->typensDefined) > 0)
        {
            $ww->openTag('types');
            $ww->openTag('xsd:schema');
            $ww->addAttribute("xmlns", "http://www.w3.org/2001/XMLSchema");
            $ww->addAttribute("targetNamespace", "urn:" . $this->name);
            
            $vars = $this->PHPParser->getClassesVars();
            foreach ($this->typensDefined as $typensDefined)
            {
                $ww->openTag("xsd:complexType");
                $ww->addAttribute("name", $typensDefined);
                
                $ww->openTag("xsd:all");
                
                if (isset($vars[$typensDefined]) && is_array($vars[$typensDefined]))
                {
                    ksort($vars[$typensDefined]);
                    
                    foreach ($vars[$typensDefined] as $varName => $varType)
                    {
                        $ww->openTag("xsd:element");
                        $ww->addAttribute("name", $varName);
                        $varType = isset($this->xsd[$varType]) ? "xsd:" . $this->xsd[$varType] : "anyType";
                        $ww->addAttribute("type", $varType);
                        $ww->closeTag();
                    }
                }
                
                $ww->closeTag();
                $ww->closeTag();
            }
            
            $ww->closeTag();
            ;
            $ww->closeTag();
        }
        
        // @todo Write Ports, Messages, Bindings and Services
        
        // $ww->openTag('service');
        // $ww->addAttribute("name", $this->name . "Service");
        // Services
        // $ww->closeTag();
        
        $ww->closeTag();
    }
    
    /**
     * Get the WSDL
     *
     * @return string
     */
    public function getWSDL()
    {
        return WsdlWriter::getInstance()->getOutput();
    }
}
?>