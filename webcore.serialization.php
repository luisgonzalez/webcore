<?php
require_once "webcore.php";

/**
 * Defines the necessary methods for a class to be serializable.
 *
 * @package WebCore
 * @subpackage Serialization
 */
interface ISerializable extends IObject
{
    /**
     * Returns a default instance of the class implementing this method.
     * NOTE: All object variables typed as objects must be populated with a default instance of such type as well.
     * Furthermore, these object variables must implement the ISerializable interface as well.
     * Also, array variables within the class must be initialized (usually to empty arrays) in order for the serializer to
     * be able to determine the variable type.
     *
     * @return mixed
     */
    public static function createInstance();
    
    /**
     * This method must return an associative array representing the state of the object.
     * array variables must be keyed as 'variableName:array'
     * Object variables must be keyed as 'variableName:objectType'
     *
     * @return array
     */
    public function getObjectState();
    
    /**
     * Sets the state of the current instance given the associative array representing its state.
     */
    public function setObjectState($state);
}

/**
 * Contains basic serialization implementation for simpler classes.
 *
 * @package WebCore
 * @subpackage Serialization
 */
abstract class SerializableObjectBase extends ObjectBase implements ISerializable
{
    /**
     * Sets the state of the current instance given the associative array representing its state.
     */
    public function setObjectState($state)
    {
        $objectVars = SerializationHelper::stateToObjectVars($state);
        
        foreach ($objectVars as $key => $value)
            $this->$key = $objectVars[$key];
    }
    
    /**
     * This method must return an associative array representing the state of the object.
     * array variables must be keyed as 'variableName:array'
     * Object variables must be keyed as 'variableName:objectType'
     *
     * @return array
     */
    public function getObjectState()
    {
        $objectVars = get_object_vars($this);
        return SerializationHelper::objectVarsToState($objectVars);
    }
}

/**
 * Defines the methods necessary for a class to be able to serialize and deserialize objects.
 *
 * @package WebCore
 * @subpackage Serialization
 */
interface ISerializer extends IHelper
{
    /**
     * Transforms the state of an ISerializable object into another representation.
     *
     * @param ISerializable $object
     * @return mixed
     */
    public static function serialize(&$object);
    /**
     * Transforms an object state representation into an instance of that same object
     *
     * @param mixed $data
     * @param string $typeName
     * @return mixed
     */
    public static function deserialize(&$data, $typeName);
}

/**
 * Provides helper methods for converting back and forth between
 * state notation and live state representation
 *
 * @package WebCore
 * @subpackage Serialization
 */
class SerializationHelper extends HelperBase
{
    /**
     * Converts the output of get_object_vars into state notation
     *
     * @param array $objectVars
     * @return array
     */
    public static function objectVarsToState(&$objectVars)
    {
        $objectVarKeys = array_keys($objectVars); // get the keys
        $objectState   = array(); // initialize object state
        
        foreach ($objectVarKeys as &$varKey)
        {
            if (is_object($objectVars[$varKey]))
            {
                $object =& $objectVars[$varKey];
                if (ObjectIntrospector::isImplementing($object, 'ISerializable'))
                {
                    $objectState[$varKey . ':' . get_class($object)] = $object->getObjectState();
                }
            }
            elseif (is_array($objectVars[$varKey]))
            {
                $currentArray =& $objectVars[$varKey];
                $objectState[$varKey . ':array'] = self::objectVarsToState($currentArray);
            }
            elseif (is_bool($objectVars[$varKey]))
            {
                $currentArray =& $objectVars[$varKey];
                $objectState[$varKey . ':bool'] =& $objectVars[$varKey];
            }
            elseif (is_int($objectVars[$varKey]))
            {
                $currentArray =& $objectVars[$varKey];
                $objectState[$varKey . ':int'] =& $objectVars[$varKey];
            }
            elseif (is_float($objectVars[$varKey]))
            {
                $currentArray =& $objectVars[$varKey];
                $objectState[$varKey . ':float'] =& $objectVars[$varKey];
            }
            else
            {
                $objectState[$varKey] =& $objectVars[$varKey];
            }
        }
        
        return $objectState;
    }
    
    /**
     * Converts state notation into live state representation
     *
     * @param array $state
     * @return array
     */
    public static function stateToObjectVars(&$state)
    {
        $objectVars = array();
        $stateKeys  = array();
        if (is_array($state) === true)
            $stateKeys = array_keys($state);
        
        foreach ($stateKeys as $stateKey)
        {
            
            $keyParts = explode(':', $stateKey);
            if (count($keyParts) > 1 && $keyParts[1] != '')
            {
                $propertyName = $keyParts[0];
                $propertyType = $keyParts[1];
                
                if ($propertyType == 'array')
                {
                    $arrayState                = self::stateToObjectVars($state[$stateKey]);
                    $objectVars[$propertyName] = $arrayState;
                }
                elseif ($propertyType == 'bool')
                {
                    $objectVars[$propertyName] = (boolean) $state[$stateKey];
                }
                elseif ($propertyType == 'int')
                {
                    $objectVars[$propertyName] = (integer) $state[$stateKey];
                }
                elseif ($propertyType == 'float')
                {
                    $objectVars[$propertyName] = (float) $state[$stateKey];
                }
                else
                {
                    if (in_array('ISerializable', class_implements($propertyType)))
                    {
                        $instanceCallback = array(
                            $propertyType,
                            'createInstance'
                        );
                        $object           = call_user_func($instanceCallback);
                        $object->setObjectState($state[$stateKey]);
                        $objectVars[$propertyName] = $object;
                    }
                }
                
            }
            else
            {
                $propertyName              = $keyParts[0];
                $objectVars[$propertyName] = $state[$stateKey];
            }
        }
        
        return $objectVars;
    }
}

/**
 * Provides methods for serializing and deserializing objects
 * using xml as as the state representation.
 *
 * @package WebCore
 * @subpackage Serialization
 */
class XmlSerializer extends HelperBase implements ISerializer
{
    /**
     * Serializes the ISerializable class instance
     *
     * @param ISerializable $object
     */
    public static function serialize(&$object)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString("    ");
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('root');
        $writer->writeAttribute('typeName', get_class($object));
        $state = $object->getObjectState();
        self::writeElement($writer, '', $state);
        $writer->endElement();
        $writer->endDocument();
        
        return $writer->outputMemory(true);
    }
    
    /**
     * Recursively writes xml elements.
     *
     * @param XMLWriter $writer
     * @param string $elementName
     * @param array $state
     * @param string $keyAttribute
     */
    private static function writeElement(&$writer, $elementName, &$state, $keyAttribute = '')
    {
        $isInArray = false;
        if ($elementName !== '')
        {
            $elementNameParts = explode(':', $elementName);
            if (count($elementNameParts) > 1 && $elementNameParts[1] != '')
            {
                $writer->startElement($elementNameParts[0]);
                if ($keyAttribute != '')
                    $writer->writeAttribute('key', $keyAttribute);
                $writer->writeAttribute('typeName', $elementNameParts[1]);
                $isInArray = ($elementNameParts[1] == 'array') ? true : false;
            }
            else
            {
                $writer->startElement($elementName);
                if ($keyAttribute != '')
                    $writer->writeAttribute('key', $keyAttribute);
            }
        }
        
        if (is_array($state))
        {
            $arrayKeys = array_keys($state);
            
            foreach ($arrayKeys as $arrayKey)
            {
                if ($isInArray === true)
                {
                    $keyParts = explode(':', $arrayKey);
                    $tagKey   = $arrayKey;
                    $tagName  = 'arrayItem';
                    if (count($keyParts) > 1)
                    {
                        $tagKey = $keyParts[0];
                        $tagName .= ':' . $keyParts[1];
                    }
                    self::writeElement($writer, $tagName, $state[$arrayKey], (string) $tagKey);
                }
                else
                {
                    self::writeElement($writer, $arrayKey, $state[$arrayKey]);
                }
            }
        }
        else
        {
            $writer->text(utf8_encode($state));
        }
        
        if ($elementName != '')
            $writer->endElement();
    }
    
    /**
     * Creates a new instance of the provided type name based on the data
     *
     * @param string $data
     * @param string $typeName the ISerializable type name
     * @return $typeName
     */
    public static function deserialize(&$data, $typeName)
    {
        $reader = new XMLReader();
        $reader->XML($data);
        
        $state = self::readElement($reader);
        $state =& $state['root:' . $typeName];
        
        $callback = array(
            $typeName,
            'createInstance'
        );
        $object   = call_user_func($callback);
        
        $object->setObjectState($state);
        
        return $object;
    }
    
    /**
     * Rescursive function to read the state notation inside the xml.
     *
     * @param XMLReader $reader
     * @return array
     */
    private static function readElement(&$reader)
    {
        $state = '';
        
        while ($reader->read())
        {
            $elementName    = $reader->name;
            $elementValue   = $reader->value;
            $nodeType       = $reader->nodeType;
            $isEmptyElement = $reader->isEmptyElement;
            $hasValue       = $reader->hasValue;
            
            switch ($nodeType)
            {
                case XMLReader::END_ELEMENT:
                    return $state;
                    break;
                case XMLReader::ELEMENT:
                    $keyAttrib  = ($reader->moveToAttribute('key')) ? $reader->value : false;
                    $typeAttrib = ($reader->moveToAttribute('typeName')) ? $reader->value : false;
                    $keyName    = $elementName;
                    
                    if ($keyAttrib === false && $typeAttrib !== false)
                        $keyName .= ':' . $typeAttrib;
                    elseif ($keyAttrib !== false && $typeAttrib === false)
                        $keyName = $keyAttrib;
                    elseif ($keyAttrib !== false && $typeAttrib !== false)
                        $keyName = $keyAttrib . ':' . $typeAttrib;
                    
                    $state[$keyName] = ($isEmptyElement) ? '' : self::readElement($reader);
                    
                    break;
                case XMLReader::TEXT:
                    $state .= utf8_decode($elementValue);
                    break;
            }
        }
        return $state;
    }
}

/**
 * Provides methods for serializing and deserializing objects
 * using native PHP functions such as serialize() and unserialize()
 *
 * @package WebCore
 * @subpackage Serialization
 */
class NativeSerializer extends HelperBase implements ISerializer
{
    /**
     * Serializes the ISerializable class instance
     *
     * @param ISerializable $object
     * @return string Returns the string representation of the object's state
     */
    public static function serialize(&$object)
    {
        return serialize($object);
    }
    
    /**
     * Creates a new instance of the provided type name based on the data
     *
     * @param string $data
     * @param string $typeName the ISerializable type name
     * @return $typeName
     */
    public static function deserialize(&$data, $typeName)
    {
        return unserialize($data);
    }
}

/**
 * Provides methods for serializing and deserializing objects
 * using JSON as the state representation.
 *
 * @package WebCore
 * @subpackage Serialization
 */
class JsonSerializer extends HelperBase implements ISerializer
{
    /**
     * Serializes the ISerializable class instance
     *
     * @param ISerializable $object
     */
    public static function serialize(&$object)
    {
        $state = $object->getObjectState();
        
        return json_encode($state);
    }
    
    /**
     * Creates a new instance of the provided type name based on the data
     *
     * @param string $data
     * @param string $typeName the ISerializable type name
     * @return $typeName
     */
    public static function deserialize(&$data, $typeName)
    {
        $state    = json_decode($data, true);
        $callback = array(
            $typeName,
            'createInstance'
        );
        $object   = call_user_func($callback);
        
        $object->setObjectState($state);
        
        return $object;
    }
}

/**
 * Provides methods for serializing and deserializing objects
 * using Base 64 as the state representation.
 *
 * @package WebCore
 * @subpackage Serialization
 */
class Base64Serializer extends HelperBase implements ISerializer
{
    /**
     * Serializes the ISerializable class instance
     *
     * @param ISerializable $object
     */
    public static function serialize(&$object)
    {
        $state    = $object->getObjectState();
        $stateStr = serialize($state);
        return base64_encode($stateStr);
    }
    
    /**
     * Creates a new instance of the provided type name based on the data
     *
     * @param string $data
     * @param string $typeName the ISerializable type name
     * @return $typeName
     */
    public static function deserialize(&$data, $typeName)
    {
        $stateStr = base64_decode($data, true);
        $state    = unserialize($stateStr);
        $callback = array(
            $typeName,
            'createInstance'
        );
        $object   = call_user_func($callback);
        $object->setObjectState($state);
        return $object;
    }
}
?>