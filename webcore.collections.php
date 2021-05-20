<?php
/**
 * @package WebCore
 * @subpackage Collections
 * @version 1.0
 * 
 * Provides an additional level of abstraction and improved functionality over standard PHP collections.
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.serialization.php";

/**
 * Provides a base implementation for a collection.
 * This abstract class implements the IColletion interface.
 *
 * @package WebCore
 * @subpackage Collections
 */
abstract class CollectionBase extends ObjectBase implements Iterator, ISerializable
{
    /**
     * Holds a reference to the array this Collection wraps
     *
     * @var &array
     */
    protected $__arrayReference = null;
    
    /**
     * Determines whether the collection is read-only.
     *
     * @var bool
     */
    protected $isReadOnly = false;
    
    /**
     * Creates an instance of a Collection wrapper around the given array reference
     *
     * @param array $arrayReference
     * @param bool $isReadOnly
     */
    protected function __construct(&$arrayReference, $isReadOnly)
    {
        $this->__arrayReference =& $arrayReference;
        $this->isReadOnly = ($isReadOnly === true) ? true : false;
    }
    
    /**
     * Fetches a key in the array.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function key()
    {
        return key($this->__arrayReference);
    }
    
    /**
     * Returns the current element pointer.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function current()
    {
        return current($this->__arrayReference);
    }
    
    /**
     * Advances the internal array pointer.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function next()
    {
        return next($this->__arrayReference);
    }
    
    /**
     * Rewinds internal array pointer.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function rewind()
    {
        return reset($this->__arrayReference);
    }
    
    /**
     * Returns whether the current array pointer is valid (not null).
     * Implemented in order to support native PHP array functions.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->current() === false ? false : true;
    }
    
    /**
     * Gets a reference to the collection this instance wraps
     * Note: This function must be called using the '=&' operator to set the target of its assignment.
     *
     * @return array
     */
    public function &getArrayReference()
    {
        return $this->__arrayReference;
    }
    
    /**
     * Determines whether the collection is read-only
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->isReadOnly;
    }
    
    /**
     * Returns the number of registered keys in the collection
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->__arrayReference, COUNT_NORMAL);
    }
    
    /**
     * Returns TRUE if collection is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->__arrayReference);
    }
    
    /**
     * Returns the number of registered keys in the collection recursively
     *
     * @return int
     */
    public function getCountRecursive()
    {
        return count($this->__arrayReference, COUNT_RECURSIVE);
    }
    
    /**
     * Gets all the values contained in the collection
     *
     * @return mixed
     */
    public function &getValues()
    {
        return array_values($this->__arrayReference);
    }
    
    /**
     * Clears the entire collection
     *
     */
    public function clear()
    {
        unset($this->__arrayReference);
        $this->__arrayReference = array();
    }
    
    /**
     * Executes a callback for each element in collection.
     * The internal array isn't changed.
     *
     * @param callback $callback
     * @param mixed $userdata
     * @return bool
     */
    public function each($callback, $userdata = null)
    {
        if (is_callable($callback) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Invalid callback passed.');
        
        $reflection = new ReflectionFunction($callback);
        
        if ($reflection->getNumberOfParameters() < 2)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Invalid callback passed.');
        
        return array_walk($this->__arrayReference, $callback, $userdata);
    }
    
    /**
     * Executes a callback for each element in collection and return another
     * collection with new values.
     * The internal array isn't changed.
     *
     * @param callback $callback
     * @param array $params 
     * @return array
     */
    protected function map($callback, $params = null)
    {
        if (is_callable($callback) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Invalid callback passed.');
        
        $reflection = new ReflectionFunction($callback);
        
        if ($reflection->getNumberOfParameters() < 1)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Invalid callback passed.');
        
        return array_map($callback, $this->__arrayReference, $params);
    }
    
    /**
     * Folds elements in a scalar value
     *
     * @param callback $callback
     * @param mixed $initial
     * @return mixed
     */
    public function fold($callback, $initial = null)
    {
        if (is_callable($callback) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Invalid callback passed.');
        
        $reflection = new ReflectionFunction($callback);
        
        if ($reflection->getNumberOfParameters() < 2)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Invalid callback passed.');
        
        return array_reduce($this->__arrayReference, $callback, $initial);
    }
    
    public function __toString()
    {
        return "{" . __CLASS__ . " [Count = " . $this->getCount() . "]}";
    }
    
    /**
     * Gets the object's state data in an associative array.
     *
     * @return array
     */
    public function getObjectState()
    {
        $objectVars    = get_object_vars($this);
        $internalArray = $objectVars['__arrayReference'];
        unset($objectVars['__arrayReference']);
        $objectVars['collectionItems'] = $internalArray;
        
        return SerializationHelper::objectVarsToState($objectVars);
    }
    
    /**
     * Sets the object's state.
     *
     * @param array $state The associative array containing state data.
     */
    public function setObjectState($state)
    {
        $objectVars = SerializationHelper::stateToObjectVars($state);
        
        foreach ($objectVars as $key => $value)
        {
            if ($key == 'collectionItems')
                $this->__arrayReference = $objectVars[$key];
            else
                $this->$key = $objectVars[$key];
        }
    }
    
    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     * 
     */
    public function __clone()
    {
        $vars = get_object_vars($this);
        
        foreach ($vars as $key => $value)
        {
            if (is_object($value))
                $this->$key = clone $value;
        }
    }
}

/**
 * Provides a base implementation for a name-value pair collection
 *
 * @package WebCore
 * @subpackage Collections
 */
abstract class KeyedCollectionBase extends CollectionBase
{
    /**
     * Executes a callback for each element in collection and return another
     * collection with new values.
     * The internal array isn't change.
     * 
     * @param callback $callback
     * @param array $params
     * 
     * @return KeyedCollectionBase
     */
    public function map($callback, $params = null)
    {
        $mapArray = parent::map($callback, $params);
        
        return new KeyedCollectionWrapper($mapArray, false);
    }
    
    /**
     * Takes the keys and values from the collection in the parameter and sets them for this instance.
     * This function overwrites values of this collection with the collection in the paramenter
     * @param KeyedCollectionBase $collection
     */
    public function merge($collection)
    {
        $this->__arrayReference = array_merge($this->getArrayReference(), $collection->getArrayReference());
    }
    
    /**
     * Determines whether a name-value pair exists in the collection
     *
     * @param string $keyName
     * @return bool
     */
    public function keyExists($keyName)
    {
        if (is_string($keyName) === false)
            throw new SystemException(SystemException::EX_INVALIDKEY);
        
        if (is_array($this->__arrayReference) === false)
            return false;
        
        return (key_exists($keyName, $this->__arrayReference));
    }
    
    /**
     * Returns all the keys in the
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->__arrayReference);
    }
    
    /**
     * Gets the value stored in the given collection key.
     * This method does not enfore returning the values by reference. Use the getItem method to enforce return values as reference.
     * If the key is not found, an EX_KEYNOTFOUND exception is thrown.
     *
     * @param string $keyName
     * @return mixed
     */
    public function getValue($keyName)
    {
        if (is_string($keyName) === false)
            throw new SystemException(SystemException::EX_INVALIDKEY);
        if ($this->keyExists($keyName))
            return $this->__arrayReference[$keyName];
        throw new SystemException(SystemException::EX_KEYNOTFOUND, "Key name: '$keyName'");
    }
    
    /**
     * Gets the value stored in the given collection key.
     * This method enforces returning values by reference. If you do not need to enforce by-reference return values, use the getValue method instead.
     * If the key is not found, an EX_KEYNOTFOUND exception is thrown.
     *
     * @param string $keyName
     * @return mixed
     */
    public function &getItem($keyName)
    {
        if (is_string($keyName) === false)
            throw new SystemException(SystemException::EX_INVALIDKEY);
        if ($this->keyExists($keyName))
            return $this->__arrayReference[$keyName];
        throw new SystemException(SystemException::EX_KEYNOTFOUND);
    }
    
    /**
     * Sets or updates a value in the collection given its key.
     * If you need to enforce passing a value by reference, use the setItem method instead.
     *
     * @param string $keyName
     * @param mixed $value
     * @return bool
     */
    public function setValue($keyName, $value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        if (is_string($keyName) === false)
            throw new SystemException(SystemException::EX_INVALIDKEY);
        $this->__arrayReference[$keyName] = $value;
        return true;
    }
    
    /**
     * Sets or updates a value by reference in the collection given its key.
     * This method enforces passing the value by reference. If you need to pass the value itself you can use the setValue method instead.
     *
     * @param string $keyName
     * @param mixed $value
     * @return bool
     */
    public function setItem($keyName, &$value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        if (is_string($keyName) === false)
            throw new SystemException(SystemException::EX_INVALIDKEY);
        $this->__arrayReference[$keyName] = $value;
        return true;
    }
    
    /**
     * Removes a name-value pair given a key name.
     *
     * @param string $keyName
     * @return bool
     */
    public function removeItem($keyName)
    {
        if (is_string($keyName) === false)
            throw new SystemException(SystemException::EX_INVALIDKEY);
        
        if ($this->keyExists($keyName))
            unset($this->__arrayReference[$keyName]);
        else
            throw new SystemException(SystemException::EX_KEYNOTFOUND);
        
        return true;
    }
    
    /**
     * Implodes the sequence into a formatted string.
     *
     * @param string $format
     * @param string $separator
     * @return string
     */
    public function implode($format = '%s = %s', $separator = ', ')
    {
        $keys             = $this->getKeys();
        $implodedSequence = '';
        for ($i = 0; $i < count($keys); $i++)
        {
            $isLast = ($i === (count($keys) - 1)) ? true : false;
            $implodedSequence .= sprintf($format, $keys[$i], $this->getValue($keys[$i] . ""));
            
            if ($isLast === false)
                $implodedSequence .= $separator;
        }
        return $implodedSequence;
    }
    
    /**
     * Helper method used to convert a keyed collection -- or array into an stdClass-typed object.
     *
     * @param KeyedCollection $keyedCollection
     * @return stdClass
     */
    public function toStdClass()
    {
        $class = new stdClass();
        
        foreach ($this->__arrayReference as $key => $value)
            $class->$key = $value;
        
        return $class;
    }
}

/**
 * Provides a base implementarion of an indexed collection of values.
 *
 * @package WebCore
 * @subpackage Collections
 */
abstract class IndexedCollectionBase extends CollectionBase
{
    /**
     * Executes a callback for each element in collection and return another
     * collection with new values.
     * The internal array isn't change.
     * 
     * @param callback $callback
     * @param array $params
     * 
     * @return IndexedCollectionBase
     */
    public function map($callback, $params = array())
    {
        $mapArray = parent::map($callback, $params);
        
        return new IndexedCollectionWrapper($mapArray, false);
    }
    
    /**
     * Reverse the array
     *
     */
    public function reverse()
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        
        rsort($this->__arrayReference);
    }
    
    /**
     * Inserts an item at the given offset. The value is passed by reference.
     * Use the insertAt method instead if you do not need to enforce passing values by reference.
     *
     * @param int $offset
     * @param mixed $value
     * @return bool
     */
    public function insertItemAt($offset, &$value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        
        if ($offset == 0 && $this->isEmpty())
        {
            $this->addItem($value);
            return true;
        }
        
        if (!is_int($offset) || intval($offset) < 0 || intval($offset) >= $this->getCount())
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        
        $insertArray = array(
            $value
        );
        array_splice($this->__arrayReference, $offset, 0, $insertArray);
        
        return true;
    }
    
    /**
     * Inserts an item at the given offset. The value is not passed by refrence.
     * Use the insertItemAt method if you need to enforce passing values by reference.
     *
     * @param int $offset
     * @param mixed $value
     * @return bool
     */
    public function insertAt($offset, $value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        
        if ($offset == 0 && $this->isEmpty())
        {
            $this->addItem($value);
            return true;
        }
        
        if (!is_int($offset) || intval($offset) < 0 || intval($offset) >= $this->getCount())
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        
        $insertArray = array(
            $value
        );
        array_splice($this->__arrayReference, $offset, 0, $insertArray);
        
        return true;
    }
    
    /**
     * Appends a value at the end of the collection
     * The value is passed by reference. Use the addValue method instead if you do not need to pass the value by reference.
     *
     * @param mixed $value
     */
    public function addItem(&$value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        $this->__arrayReference[] = $value;
    }
    
    /**
     * Appends a value at the end of the collection
     * The value is not passed by reference. Use the addItem method instead if you need to pass the value by reference.
     *
     * @param mixed $value
     */
    public function addValue($value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        $this->__arrayReference[] = $value;
    }
    
    /**
     * Gets an item by reference in the collection given its offset.
     * If you need to get the item by value, use getValue instead.
     *
     * @param int $offset
     * @return mixed
     */
    public function &getItem($offset)
    {
        if (!is_int($offset) || intval($offset) < 0 || intval($offset) >= $this->getCount())
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        return $this->__arrayReference[$offset];
    }
    
    /**
     * Gets an item by value in the collection given its offset.
     * If you need to get the item by reference, use getItem instead.
     *
     * @param int $offset
     * @return mixed
     */
    public function getValue($offset)
    {
        if (!is_int($offset) || intval($offset) < 0 || intval($offset) >= $this->getCount())
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        
        return $this->__arrayReference[$offset];
    }
    
    /**
     * Gets the last item by reference in the collection.
     * If you need to get the item by value, use getLastValue instead.
     *
     * @return mixed
     */
    public function &getLastItem()
    {
        $lastIndex = $this->getCount() - 1;
        return $this->getItem($lastIndex);
    }
    
    /**
     * Gets the last item by value in the collection.
     * If you need to get the item by reference, use getLastItem instead.
     *
     * @return mixed
     */
    public function &getLastValue()
    {
        if (!is_int($offset) || intval($offset) < 0 || intval($offset) >= $this->getCount())
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        
        return $this->__arrayReference[$offset];
    }
    
    /**
     * Overwrites a value of an existing item.
     * The value is not passed by reference. Use the setItem method instead if you need to pass the value as reference.
     *
     * @param int $offset
     * @param mixed $value
     */
    public function setValue($offset, $value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        if (!is_int($offset) || intval($offset) < 0 || intval($offset) >= $this->getCount())
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        
        $this->__arrayReference[$offset] = $value;
    }
    
    /**
     * Overwrites a value of an existing item.
     * The value is passed by reference. Use the setValue method instead if you do not need to pass the value as reference.
     *
     * @param int $offset
     * @param mixed $value
     */
    public function setItem($offset, &$value)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        if (!is_int($offset) || intval($offset) < 0 || intval($offset) >= $this->getCount())
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        
        $this->__arrayReference[$offset] = $value;
    }
    
    /**
     * Removes an item from the collection at the given offset.
     *
     * @param int $offset
     * @return bool
     */
    public function removeAt($offset)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        if (!is_int($offset) || intval($offset) < 0)
            throw new SystemException(SystemException::EX_INVALIDOFFSET);
        
        array_splice($this->__arrayReference, $offset, 1);
        return true;
    }
    
    /**
     * Adds all the values contained in the array.
     * Keyed array keys are always ignored.
     *
     * @param array $array
     */
    public function addRange(&$array)
    {
        if ($this->isReadOnly() === true)
            throw new SystemException(SystemException::EX_READONLYCOLLECTION);
        
        $arrayValues = array_values($array);
        for ($i = 0; $i < count($arrayValues); $i++)
        {
            $this->addItem($arrayValues[$i]);
        }
    }
    
    /**
     * Implodes the sequence into a formatted string.
     *
     * @param string $format
     * @param string $separator
     * @return string
     */
    public function implode($format = '%s', $separator = ', ')
    {
        $implodedSequence = '';
        for ($i = 0; $i < $this->getCount(); $i++)
        {
            $isLast = ($i === ($this->getCount() - 1)) ? true : false;
            $implodedSequence .= sprintf($format, $this->getItem($i));
            
            if ($isLast === false)
                $implodedSequence .= $separator;
        }
        return $implodedSequence;
    }
    
    /**
     * Determines whether the given value exists in the collection.
     *
     * @param mixed $value
     * @return bool
     */
    public function containsValue($value)
    {
        return in_array($value, $this->__arrayReference);
    }
    
    /**
     * Sorts the internal array when it is composed of simple-type values such as integers and strings.
     * This method uses the QuickSort algorithm.
     */
    public function valueSort()
    {
        if (count($this->__arrayReference) == 0)
            return;
        $this->__arrayReference = self::internalValueSort($this->__arrayReference);
    }
    
    /**
     * Sorts the internal array when it is composed of objects.
     * This method uses the QuickSort algorithm.
     *
     * @param $valueProperty The name of the property that holds the value used to sort the array.
     */
    public function objectSort($valueProperty)
    {
        if (count($this->__arrayReference) == 0)
            return;
        $this->__arrayReference = self::internalObjectSort($this->__arrayReference, $valueProperty);
    }
    
    /**
     * Quicksort algorithm to sort the internal array by value.
     * 
     */
    protected static function internalValueSort(&$arr, $left = 0, $right = null)
    {
        static $array = array();
        if (is_null($right))
        {
            $array = $arr;
            $right = count($array) - 1;
        }
        $i   = $left;
        $j   = $right;
        $tmp = $array[(int) (($left + $right) / 2)];
        do
        {
            while ($array[$i] < $tmp)
                $i++;
            while ($tmp < $array[$j])
                $j--;
            
            if ($i <= $j)
            {
                $w         = $array[$i];
                $array[$i] = $array[$j];
                $array[$j] = $w;
                
                $i++;
                $j--;
            }
        } while ($i <= $j);
        
        if ($left < $j)
            self::internalValueSort($array, $left, $j);
        if ($i < $right)
            self::internalValueSort($array, $i, $right);
        return $array;
    }
    
    /**
     * Quicksort algorithm to sort the internal array by object property.
     * 
     */
    protected static function internalObjectSort(&$arr, $valueProperty, $left = 0, $right = null)
    {
        static $array = array();
        if (is_null($right))
        {
            $array = $arr;
            $right = count($array) - 1;
        }
        $i   = $left;
        $j   = $right;
        $tmp = $array[(int) (($left + $right) / 2)]->$valueProperty;
        do
        {
            while ($array[$i]->$valueProperty < $tmp)
                $i++;
            while ($tmp < $array[$j]->$valueProperty)
                $j--;
            
            if ($i <= $j)
            {
                $w         = $array[$i];
                $array[$i] = $array[$j];
                $array[$j] = $w;
                
                $i++;
                $j--;
            }
        } while ($i <= $j);
        
        if ($left < $j)
            self::internalObjectSort($array, $valueProperty, $left, $j);
        if ($i < $right)
            self::internalObjectSort($array, $valueProperty, $i, $right);
        return $array;
    }
}

/**
 * Represents a name-value pair collection
 *
 * @package WebCore
 * @subpackage Collections
 */
class KeyedCollection extends KeyedCollectionBase
{
    public function __construct()
    {
        $initArray = array();
        parent::__construct($initArray, false);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return KeyedCollection
     */
    public static function createInstance()
    {
        return new KeyedCollection();
    }
}

/**
 * Represents an indexed collection of values.
 *
 * @package WebCore
 * @subpackage Collections
 */
class IndexedCollection extends IndexedCollectionBase
{
    public function __construct()
    {
        $initArray = array();
        parent::__construct($initArray, false);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return IndexedCollection
     */
    public static function createInstance()
    {
        return new IndexedCollection();
    }
    
    /**
     * Gets a distinct indexed collection of values found in this collection.
     * Not recommended for object Indexed Collections
     * @return IndexedCollection
     */
    public function getDistinct()
    {
        $retVal = new IndexedCollection();
        for ($i = 0; $i < count($this->__arrayReference); $i++)
        {
            $value = $this->__arrayReference[$i];
            if ($retVal->containsValue($value)) continue;
            $retVal->addItem($value);
        }
        
        return $retVal;
    }
}

/**
 * Wrapper class to enable object-oriented style functionality for the given array
 *
 * @package WebCore
 * @subpackage Collections
 */
class KeyedCollectionWrapper extends KeyedCollectionBase
{
    public function __construct(&$arrayReference, $isReadonly)
    {
        parent::__construct($arrayReference, $isReadonly);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return KeyedCollectionWrapper
     */
    public static function createInstance()
    {
        $arr = array();
        return new KeyedCollectionWrapper($arr, false);
    }
}

/**
 * Wrapper class to enable object-oriented style functionality for the given array
 *
 * @package WebCore
 * @subpackage Collections
 */
class IndexedCollectionWrapper extends IndexedCollectionBase
{
    public function __construct(&$arrayReference, $isReadonly)
    {
        parent::__construct($arrayReference, $isReadonly);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return IndexedCollectionWrapper
     */
    public static function createInstance()
    {
        $arr = array();
        return new IndexedCollectionWrapper($arr, false);
    }
}

/**
 * Provides a simple implementation of a stack.
 *
 * @package WebCore
 * @subpackage Collections 
 */
class Stack extends CollectionBase
{
    /**
     * Creates a new instance
     */
    public function __construct()
    {
        $initArray = array();
        parent::__construct($initArray, false);
    }
    
    /**
     * Gets the number of items in the stack
     *
     * @return int
     */
    public function getDepth()
    {
        return self::getCount();
    }
    
    /**
     * Pushes an item into the stack
     *
     * @param mixed $item
     */
    public function push(&$item)
    {
        array_push($this->__arrayReference, $item);
    }
    
    /**
     * Pops an item from the stack.
     *
     * @return mixed
     */
    public function &pop()
    {
        $item = array_pop($this->__arrayReference);
        return $item;
    }
    
    /**
     * Reads the item at the top of the stack
     *
     * @return mixed
     */
    public function &getCurrent()
    {
        return $this->__arrayReference[count($this->__arrayReference) - 1];
    }
    
    /**
     * Implodes the sequence into a formatted string.
     *
     * @param string $format
     * @param string $separator
     * @return string
     */
    public function implode($format = '%s', $separator = '/')
    {
        $implodedSequence = '';
        for ($i = 0; $i < $this->getCount(); $i++)
        {
            $isLast = ($i === ($this->getCount() - 1)) ? true : false;
            $implodedSequence .= sprintf($format, $this->__arrayReference[$i]);
            
            if ($isLast === false)
                $implodedSequence .= $separator;
        }
        return $implodedSequence;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Stack
     */
    public static function createInstance()
    {
        return new Stack();
    }
}

/**
 * Represents a name-value pair for use inside collections, mainly inside indexed collections.
 * Instances of this class do not need to be stored inside a collection and can be
 * useful in the common scenario where a name-value pair object is needed.
 *
 * @package WebCore
 * @subpackage Collections
 */
class DictionaryEntry extends SerializableObjectBase
{
    protected $entryKey;
    protected $entryValue;
    
    public function __construct($entryKey, $entryValue)
    {
        $this->entryKey   = $entryKey;
        $this->entryValue = $entryValue;
    }
    
    public function getKey()
    {
        return $this->entryKey;
    }
    public function setKey($value)
    {
        $this->entryKey = $value;
    }
    public function getValue()
    {
        return $this->entryValue;
    }
    public function setValue($value)
    {
        $this->entryValue = $value;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return DictionaryEntry
     */
    public static function createInstance()
    {
        return new DictionaryEntry('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a hash table.
 * The items in collection only can be objects using IComparable interface
 *
 * @package WebCore
 * @subpackage Collections
 */
class HashTable extends CollectionBase
{
    /**
     * HashTable key map
     *
     * @var array
     */
    private $_keyMap = array();
    
    /**
     * Create a new HashTable
     *
     * @param  $source	Optional source array to create HashTable from
     */
    public function __construct($source = null)
    {
        $initArray = array();
        parent::__construct($initArray, false);
        
        if (is_null($source) === false)
            $this->addFromSource($source);
    }
    
    /**
     * Add HashTable items from source
     *
     * @param  array $source Source array to create HashTable from
     */
    public function addFromSource($source)
    {
        if (is_array($source) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Invalid array parameter passed.');
        
        foreach ($source as $item)
            $this->add($item);
    }
    
    /**
     * Adds HashTable item
     *
     * @param 	IComparable $item
     */
    public function add($item)
    {
        if (ObjectIntrospector::isImplementing($item, 'IComparable') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Item must implement IComparable.');
        
        $hashCode = $item->getHashCode();
        
        if (isset($this->__arrayReference[$hashCode]))
        {
            $index = $this->__arrayReference[$hashCode]->getHashIndex();
        }
        else
        {
            $this->__arrayReference[$hashCode] = $item;
            $index                             = count($this->__arrayReference) - 1;
            $this->_keyMap[$index]             = $hashCode;
        }
        
        $item->setHashIndex($index);
    }
    
    /**
     * Removes HashTable item
     *
     * @param IComparable $item Item to remove
     */
    public function remove($item)
    {
        if (ObjectIntrospector::isImplementing($item, 'IComparable') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Item must implement IComparable.');
        
        $hashCode = $item->getHashCode();
        
        if (isset($this->__arrayReference[$hashCode]))
        {
            unset($this->__arrayReference[$hashCode]);
            $deleteKey = -1;
            
            foreach ($this->_keyMap as $key => $value)
            {
                if ($deleteKey >= 0)
                    $this->_keyMap[$key - 1] = $value;
                if ($value == $hashCode)
                    $deleteKey = $key;
            }
            
            $index = count($this->_keyMap) - 1;
            unset($this->_keyMap[$index]);
        }
    }
    
    /**
     * Clears HashTable
     *
     */
    public function clear()
    {
        parent::clear();
        $this->_keyMap = array();
    }
    
    /**
     * Gets index for hash code
     *
     * @param 	string 	$hashCode
     * @return 	int 	Index
     */
    public function getIndexForHashCode($hashCode)
    {
        return array_search($hashCode, $this->_keyMap);
    }
    
    /**
     * Gets by index
     *
     * @param	int	$index
     * @return 	IComparable
     */
    public function getByIndex($index = 0)
    {
        if (isset($this->_keyMap[$index]))
            return $this->getByHashCode($this->_keyMap[$index]);
        return null;
    }
    
    /**
     * Gets by hashcode
     *
     * @param	string	$hashCode
     * @return 	IComparable
     */
    public function getByHashCode($hashCode)
    {
        if (isset($this->__arrayReference[$hashCode]))
            return $this->__arrayReference[$hashCode];
        
        return null;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return HashTable
     */
    public static function createInstance()
    {
        return new HashTable();
    }
}

/**
 * Represents a more functional version of the stdClass provided by PHP
 * Added funcionality includes Serialization and Iteration.
 * 
 * @package WebCore
 * @subpackage Collections
 */
class Popo extends SerializableObjectBase implements Iterator
{
    /**
     * @var KeyedCollection
     */
    protected $fields;
    
    /**
     * Creates a new instance of this class.
     */
    public function __construct()
    {
        $this->fields = new KeyedCollection();
    }
    
    /**
     * Converts the internal entity fields into a KeyedCollection of field values.
     * This method is useful to perform data binding.
     * @return KeyedCollection
     */
    public function toDataSource()
    {
        $fieldsCollection = new KeyedCollection();
        
        foreach ($this->getFields() as $fieldName => $fieldValue)
        {
            $fieldsCollection->setValue($fieldName, $fieldValue);
        }
        
        return $fieldsCollection;
    }
    
    /**
     * Returns a KeyedCollection of internal fields.
     *
     * @return KeyedCollection
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    /**
     * Registers a new field with the given value
     *
     * @param string $fieldName
     * @param mixed $value
     */
    public function addFieldValue($fieldName, $value = null)
    {
        if ($this->hasField($fieldName))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The field with the name '$fieldName' already exists.");
        $this->fields->setValue($fieldName, $value);
    }
    
    /**
     * Sets field's value
     *
     * @param string $fieldName
     * @param string $value
     */
    public function setFieldValue($fieldName, $value)
    {
        if (!$this->hasField($fieldName))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The field with the name '$fieldName' does not exist.");
        $this->getfields()->setValue($fieldName, $value);
    }
    
    /**
     * Returns true if entity has field
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasField($fieldName)
    {
        if (is_string($fieldName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter 'fieldName' must be a non-empty string.");
        return $this->fields->keyExists($fieldName);
    }
    
    /**
     * Databinds datasource to this Popo
     *
     * @param KeyedCollection $dataSource
     */
    public function dataBind(&$dataSource)
    {
        if (ObjectIntrospector::isExtending($dataSource, 'KeyedCollectionBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = dataSource');
        
        foreach ($this->getFields() as $fieldName => $field)
        {
            if ($dataSource->keyExists($fieldName))
            {
                $value = $dataSource->getValue($fieldName);
                $this->setFieldValue($fieldName, $value);
            }
        }
    }
    
    /**
     * Magic method to retrieve the stored value of the given field name.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->hasField($name))
            return $this->fields->getValue($name);
        
        throw new SystemException(SystemException::EX_INVALIDKEY, "Key '$name' does not exist.");
    }
    
    /**
     * Magic method to set a value in the given field assignment.
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->setFieldValue($name, $value);
    }
    
    /**
     * Fetches a key in the array.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function key()
    {
        return key($this->getFields()->getArrayReference());
    }
    
    /**
     * Returns the current element pointer.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function current()
    {
        return current($this->getFields()->getArrayReference());
    }
    
    /**
     * Advances the internal array pointer.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function next()
    {
        return next($this->getFields()->getArrayReference());
    }
    
    /**
     * Rewinds internal array pointer.
     * Implemented in order to support native PHP array functions.
     *
     * @return pointer
     */
    public function rewind()
    {
        return reset($this->getFields()->getArrayReference());
    }
    
    /**
     * Returns whether the current array pointer is valid (not null).
     * Implemented in order to support native PHP array functions.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->current() === false ? false : true;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Popo
     */
    public static function createInstance()
    {
        return new Popo();
    }
}
?>