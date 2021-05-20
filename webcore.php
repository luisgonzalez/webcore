<?php
/**
 * @package WebCore
 * @version 3.0.0
 * 
 * Root namespace and file for WebCore 3.0 framework implementation.
 * Framework's objectives:
 * Minimize implemention time without compromising customization, visual appeal or extensibility
 * Enable model-driven development: This means you specify what needs to be accomplished for the most time instead of specifying exactly how.
 * Enable a team of developers to get up and running in no time with an exceptional scaffolder and template deployment
 * Enable automatic scalability though harware and with no changes to code
 * Centralization of settings and resources
 * Web 2.0 features by default.
 * Autogeneration of object model based on database. Object model queryable though lambda-like expressions.
 * A workflow engine capable of dehydrating and hydrating processes
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

define('WEBCORE_VERSION_MAJOR', '3');
define('WEBCORE_VERSION_MINOR', '0');
define('WEBCORE_VERSION_REVISION', '0');

/**
 * This function autoloads WebCore classes.
 * This only the require_once 'webcore.php' directive is required when using WebCore classes. 
 * Autoload is called by PHP when the corresponding script tries to use classes that have not been required or included.
 *
 * @param string $className
 */
function __autoload($className)
{
    require_once "webcore.reflection.php";
    
    ClassLoader::loadClass($className);
}

/**
 * Main interface which all framework classes implement.
 * @package WebCore
 */
interface IObject
{
    /**
     * Gets type information about the object.
     * @return ReflectionClass
     */
    public function getType();
}

/**
 * Singleton Pattern interface
 * @package WebCore
 */
interface ISingleton extends IObject
{
    /**
     * Used to get the instance of the singleton.
     * @return mixed
     */
    public static function getInstance();
    /**
     * Used to determine whether the singleton's instance is currently loaded.
     * @return bool
     */
    public static function isLoaded();
}

/**
 * Defines the methods required for an object to consume a CollectionBase-derived class object
 * @package WebCore
 */
interface IBindingTarget extends IObject
{
    /**
     * Consumes the give data source.
     * @param CollectionBase
     */
    public function dataBind(&$dataSource);
}

/**
 * This interface is used as an attribute for helper classes
 * @package WebCore
 */
interface IHelper extends IObject
{
}

/**
 * Defines the methods for an object to be member-wise bindable
 * 
 * @package WebCore
 */
interface IBindingTargetMember extends IObject
{
    /**
     * Gets the name of the member to bind to in the parent bindingSourceName
     *
     * @return string
     */
    public function getBindingMemberName();
    
    /**
     * Sets the name of the member to bind to in the parent bindingSourceName
     * If the value is an empty string, it will automatically set the isBindable property to false.
     * If the value is a non-empty string, it will automatically set the isBindable property to true.
     *
     * @param string $value
     */
    public function setBindingMemberName($value);
    
    /**
     * Gets whether the control is bindable.
     *
     * @return bool
     */
    public function getIsBindable();
    
    /**
     * Sets whether the control is bindable.
     *
     * @param bool $value
     */
    public function setIsBindable($value);
    
    /**
     * Sets the value of the control.
     *
     * @param mixed $value
     */
    public function setValue($value);
    
    /**
     * Gets the value of the control.
     *
     * @return mixed
     */
    public function &getValue();
}

/**
 * Interface to create comparable classes
 * @package WebCore
 */
interface IComparable extends IObject
{
    /**
     * Gets the hash code
     * @return string Hash code
     */
    public function getHashCode();
    
    /**
     * Gets the comparison hash index
     * @return string Hash index
     */
    public function getHashIndex();
    
    /**
     * Sets the hash index
     * @param string $value Hash index
     */
    public function setHashIndex($value);
}

/**
 * Implements the methods that most framework objects support
 * @package WebCore
 */
abstract class ObjectBase implements IObject
{
    /**
     * Used to return the ReflectionClass object
     * @return ReflectionClass
     */
    public function getType()
    {
        return new ReflectionClass($this);
    }
    
    /**
     * Creates a deep copy of the object.
     * Shallow copies keep object references. This method attempts to 
     */
    public function __clone()
    {
        foreach ($this as $key => $val)
        {
            if (is_object($val) || is_array($val))
                $this->{$key} = unserialize(serialize($val));
        }
    }
}

/**
 * Implements the methods that most helpers objects support
 *
 * @package WebCore
 */
class HelperBase extends ObjectBase implements IHelper
{
}

/**
 * Provides static methods to perform string operations.
 * @package WebCore
 */
class StringHelper extends HelperBase
{
    const CONVENTION_AUTO = 0;
    const CONVENTION_PASCAL = 1;
    const CONVENTION_CAMEL = 2;
    const CONVENTION_USCORE = 3;
    
    /**
     * Gets a system-normalized string removing all charachters except alphanumeric ones.
     * @param string $value
     * @return string
     */
    public static function getSystemNormalized($value)
    {
        return preg_replace('/[^a-z0-9A-Z ]/', '', $value);
    }
    
    /**
     * Gets whether the mbstring extension is available
     * @return boolean
     */
    public static function getIsMbstringEnabled()
    {
        return function_exists('mb_convert_encoding');
    }
    
    /**
     * Get whether the iconv extension is available
     * @return boolean
     */
    public static function getIsIconvEnabled()
    {
        return function_exists('iconv');
    }
    
    /**
     * Checks if a string is encoded as UTF8
     * @param string $value
     * @return boolean
     */
    public static function isUTF8($value = '')
    {
        return utf8_encode(utf8_decode($value)) === $value;
    }
    
    /**
     * Converts string from one encoding to another.
     * mbstring is tried first, then iconv, otherwise no conversion occurs.
     * @param string $value
     * @param string $to Encoding to convert to, e.g. 'UTF-8'
     * @param string $from Encoding to convert from, e.g. 'UTF-16LE'
     * @return string
     */
    public static function convertEncoding($value, $to, $from)
    {
        if (self::getIsMbstringEnabled())
            return mb_convert_encoding($value, $to, $from);
        elseif (self::getIsIconvEnabled())
            return iconv($from, $to, $value);
        
        return $value;
    }
    
    /**
     * Gets character count. First try mbstring, then iconv, finally strlen
     *
     * @param string $value
     * @param string $enc Encoding
     * @return int Character count
     */
    public static function countCharacters($value, $enc = '')
    {
        if ($enc === '' && self::getIsMbstringEnabled())
            $enc = mb_internal_encoding();
        
        if (self::getIsMbstringEnabled())
            return mb_strlen($value, $enc);
        
        if (self::getIsIconvEnabled())
            return iconv_strlen($value, $enc);
        
        return strlen($value);
    }
    
    /**
     * Returns string with first char in upper case
     *
     * @param string $underscorePhrase
     * @param bool $spacesBetweenWords
     * @param bool $camelCase
     * @return string
     */
    public static function toUcFirst($underscorePhrase, $spacesBetweenWords = false, $camelCase = false)
    {
        $words     = explode('_', $underscorePhrase);
        $output    = '';
        $separator = ($spacesBetweenWords === true) ? ' ' : '';
        
        foreach ($words as $word)
            $output .= $separator . ucfirst($word);
        
        if ($camelCase === true)
        {
            $ret = trim($output);
            $ret = strtolower($output[0]) . substr($output, 1);
            return $ret;
        }
        
        return trim($output);
    }
    
    /**
     * Gets a string with all of it's applicable characters as upper case
     * @param string $str
     * @return string
     */
    public static function toUpper($str)
    {
        return strtoupper($str);
    }
    
    /**
     * Gets a string with all of it's applicable characters as lower case
     * @param string $str
     * @return string
     */
    public static function toLower($str)
    {
        return strtolower($str);
    }
    
    /**
     * Converts a camel-cased, pascal-cased or underscore-separated word into a human-readable string
     * @param string $str
     * @param int $convention
     * @return string
     */
    public static function toWords($str, $convention = self::CONVENTION_AUTO)
    {
        if ($convention === self::CONVENTION_AUTO)
        {
            if (self::strContains($str, '_'))
            {
                $convention = self::CONVENTION_USCORE;
            }
            else
            {
                $convention = self::CONVENTION_PASCAL;
            }
        }
        
        if ($convention === self::CONVENTION_USCORE)
        {
            return ucwords(str_replace('_', ' ', $str));
        }
        
        if ($convention === self::CONVENTION_PASCAL || $convention === self::CONVENTION_CAMEL)
        {
            $baseStr = ucfirst($str);
            $whitespaceBefore = strtoupper($str) . "01234567890";
            
            $retStr = "";
            for ($i = 0; $i < strlen($baseStr); $i++)
            {
                $chr = $baseStr[$i];
                if (strstr($whitespaceBefore, $chr) !== false)
                {
                    $retStr .= " " . $chr;
                }
                else
                {
                    $retStr .= $chr;
                }
            }
            
            return trim(ucwords($retStr));
        }
        else
        {
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, '$convention must be one of the CONVENTION-prefixed constants');
        }
        
    }
    
    /**
     * Checks if a string contains a value.
     * 
     * @param string $str
     * @param string $needle
     * @param bool $caseInvariant
     * @return bool
     */
    public static function strContains($str, $needle, $caseInvariant = true)
    {
        if ($caseInvariant === true)
            return (stripos($str, $needle) !== false || strtolower($needle) === strtolower($str));
        else
            return (strpos($str, $needle) !== false || $needle === $str);
    }
    
    /**
     * Determines whether as string begins with the specified needle.
     * @param string $str
     * @param string $needle
     * @param bool $caseInvariant
     * @return bool
     */
    public static function beginsWith($str, $needle, $caseInvariant = true)
    {
        $match = substr($str, 0, strlen($needle));
        return self::strContains($match, $needle, $caseInvariant);
    }
    
    /**
     * Determines whether as string ends with the specified needle.
     * @param string $str
     * @param string $needle
     * @param bool $caseInvariant
     * @return bool
     */
    public static function endsWith($str, $needle, $caseInvariant = true)
    {
        $match = substr($str, strlen($str) - strlen($needle));
        return self::strContains($match, $needle, $caseInvariant);
    }
    
    /**
     * Replaces the ending of the string if the specified needle is found at the end of the string
     * @param string $str
     * @param string $needle
     * @param string $replace
     * @param bool $caseInvariant
     */
    public static function replaceEnd($str, $needle, $replace, $caseInvariant = true)
    {
        $match = substr($str, strlen($str) - strlen($needle));
        if (self::strContains($match, $needle, $caseInvariant))
        {
            return substr($str, 0, strlen($str) - strlen($needle)) . $replace;
        }
        return $str;
    }
    
    /**
     * Replaces the start of the string if the specified needle is found at the start of the string
     * @param string $str
     * @param string $needle
     * @param string $replace
     * @param bool $caseInvariant
     */
    public static function replaceStart($str, $needle, $replace, $caseInvariant = true)
    {
        $match = substr($str, 0, strlen($needle));
        if (self::strContains($match, $needle, $caseInvariant))
        {
            return $replace . substr($str, strlen($needle));
        }
        return $str;
    }
    
    /**
     * Splits the provided string by its separator.
     * @param string $string
     * @param string $separator
     * @param bool $trim Whether to trim each entry
     * @return IndexedCollection
     */
    public static function split($string, $separator, $trim = true)
    {
        $exploded = explode($separator, $string);
        
        if ($trim === true)
        {
            for ($entryIndex = 0; $entryIndex < count($exploded); $entryIndex++)
                $exploded[$entryIndex] = trim('' . $exploded[$entryIndex]);
        }
        
        return new IndexedCollectionWrapper($exploded, false);
    }
    
    /**
     * Converts decimal to roman number string
     *
     * @param int $value
     * @param bool $toupper
     * @return string
     */
    public static function convertDecimalToRoman($value, $toupper = true)
    {
        if ($value >= 5000 || $value < 1)
            return "?"; //supports up to 4999
        
        $aux = (int) ($value / 1000);
        
        if ($aux !== 0)
        {
            $value %= 1000;
            
            while ($aux !== 0)
            {
                $r1 .= "M";
                $aux--;
            }
        }
        
        $aux = (int) ($value / 100);
        
        if ($aux !== 0)
        {
            $value %= 100;
            
            switch ($aux)
            {
                case 3:
                    $r2 = "C";
                case 2:
                    $r2 .= "C";
                case 1:
                    $r2 .= "C";
                    break;
                case 9:
                    $r2 = "CM";
                    break;
                case 8:
                    $r2 = "C";
                case 7:
                    $r2 .= "C";
                case 6:
                    $r2 .= "C";
                case 5:
                    $r2 = "D" . $r2;
                    break;
                case 4:
                    $r2 = "CD";
                    break;
                default:
                    break;
            }
        }
        
        $aux = (int) ($value / 10);
        
        if ($aux !== 0)
        {
            $value %= 10;
            switch ($aux)
            {
                case 3:
                    $r3 = "X";
                case 2:
                    $r3 .= "X";
                case 1:
                    $r3 .= "X";
                    break;
                case 9:
                    $r3 = "XC";
                    break;
                case 8:
                    $r3 = "X";
                case 7:
                    $r3 .= "X";
                case 6:
                    $r3 .= "X";
                case 5:
                    $r3 = "L" . $r3;
                    break;
                case 4:
                    $r3 = "XL";
                    break;
                default:
                    break;
            }
        }
        
        switch ($value)
        {
            case 3:
                $r4 = "I";
            case 2:
                $r4 .= "I";
            case 1:
                $r4 .= "I";
                break;
            case 9:
                $r4 = "IX";
                break;
            case 8:
                $r4 = "I";
            case 7:
                $r4 .= "I";
            case 6:
                $r4 .= "I";
            case 5:
                $r4 = "V" . $r4;
                break;
            case 4:
                $r4 = "IV";
                break;
            default:
                break;
        }
        
        $roman = $r1 . $r2 . $r3 . $r4;
        
        if (!$toupper)
            $roman = strtolower($roman);
        
        return $roman;
    }
}

/**
 * Represents a framework exception.
 * The exception code constants are contained within this class.
 * Messages are automatically set, given the exception code.
 * An optional, user-defined message can also be set in the constructor.
 * 
 * @package WebCore
 */
class SystemException extends Exception implements IObject
{
    // Exception code constants
    const EX_NOCONTEXT = 901;
    const EX_KEYNOTFOUND = 902;
    const EX_SINGLETONINSTANCE = 903;
    const EX_INVALIDOFFSET = 904;
    const EX_INVALIDKEY = 905;
    const EX_NOTIMPLENTED = 906;
    const EX_READONLYCOLLECTION = 906;
    const EX_INVALIDPARAMETER = 907;
    const EX_INVALIDMETHODCALL = 908;
    const EX_CLASSNOTFOUND = 909;
    const EX_INVALIDOPERATION = 910;
    const EX_DBCONNECTION = 1201;
    const EX_QUERYEXECUTE = 1202;
    const EX_DBPROVIDERNOTFOUND = 1203;
    const EX_READONLYADAPTER = 1204;
    const EX_DUPLICATEDKEY = 1205;
    const EX_MEMBERSHIPUSER = 1300;
    const EX_MEMBERSHIPROLE = 1301;
    const EX_MAILCONNECTION = 1401;
    const EX_MAILLOGIN = 1402;
    
    /**
     * Creates a new instance of this class
     *
     * @param int $exceptionCode
     * @param string $extendedMessage
     */
    public function __construct($exceptionCode, $extendedMessage = '')
    {
        $message = "";
        switch ($exceptionCode)
        {
            case self::EX_NOCONTEXT:
                $message = "Context was not initialized. HttpContext::initialize() needs to be called before proper request handling.";
                break;
            case self::EX_KEYNOTFOUND:
                $message = "The key was not found.";
                break;
            case self::EX_SINGLETONINSTANCE:
                $message = "Cannot create instance of an ISingleton. Use the getInstance() method instead.";
                break;
            case self::EX_INVALIDOFFSET:
                $message = "Invalid offset. Offsets must be non-negative integers and less than the size of the collection.";
                break;
            case self::EX_INVALIDKEY:
                $message = "Invalid key. Keys must be non-empty strings.";
                break;
            case self::EX_NOTIMPLENTED:
                $message = "The method is unsupported as it has not yet been implemented.";
                break;
            case self::EX_READONLYCOLLECTION:
                $message = "Unable to modify the colletion because it is read-only.";
                break;
            case self::EX_INVALIDPARAMETER:
                $message = "The parameter is invalid.";
                break;
            case self::EX_INVALIDMETHODCALL:
                $message = "The method is unsupported or the operation is invalid in the current state of the object.";
                break;
            case self::EX_CLASSNOTFOUND:
                $message = "The class was not found.";
                break;
            case self::EX_INVALIDOPERATION:
                $message = "The operation is invalid in the current state of the object.";
                break;
            case self::EX_DBCONNECTION:
                $message = "Cannot connect to database server.";
                break;
            case self::EX_QUERYEXECUTE:
                $message = "Cannot execute query.";
                break;
            case self::EX_READONLYADAPTER:
                $message = "Table adapter is derived from a read-only database object.";
                break;
            case self::EX_DBPROVIDERNOTFOUND:
                $message = "Database provider is not supported.";
                break;
            case self::EX_DUPLICATEDKEY:
                $message = "Duplicate key entry.";
                break;
            case self::EX_MEMBERSHIPUSER:
                $message = "Membership user operation failed.";
                break;
            case self::EX_MEMBERSHIPROLE:
                $message = "Membership role operation failed.";
                break;
            case self::EX_MAILCONNECTION:
                $message = "Connection to mail server failed.";
                break;
            case self::EX_MAILLOGIN:
                $message = "Invalid login information.";
                break;
            default:
                $message = "An unknown exception was thrown.";
                break;
        }
        
        parent::__construct($message . " " . $extendedMessage, $exceptionCode);
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return "{" . __CLASS__ . " [Code = " . $this->getCode() . ", Message = " . $this->getMessage() . ", File = " . $this->getFile() . ", Line = " . $this->getLine() . ", StackTrace = " . $this->getTraceAsString() . "]}";
    }
    
    /**
     * Returns reflection information on this type.
     * 
     * @return ReflectionClass
     */
    public function getType()
    {
        return new ReflectionClass($this);
    }
}

/**
 * Base class to implement comparable classes.
 * 
 * @package WebCore
 */
abstract class ComparableBase extends ObjectBase implements IComparable
{
    protected $hashIndex;
    
    /**
     * Gets the hash index
     * @return string Hash index
     */
    public function getHashIndex()
    {
        return $this->hashIndex;
    }
    
    /**
     * Sets the hash index
     * @param string $value
     */
    public function setHashIndex($value)
    {
        $this->hashIndex = $value;
    }
}
?>