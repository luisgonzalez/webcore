<?php
/**
 * @package WebCore
 * @subpackage Data
 * @version 1.0
 * 
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @author Mario Di Vece <mario@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.serialization.php";
require_once "webcore.logging.php";

/**
 * Defines the methods necessary for an Entity metadata registry.
 * @package WebCore
 * @subpackage Data
 */
interface IEntityMetabase extends ISingleton
{
    /**
     * Gets the name of the database this metabase was generated from.
     * @return string
     */
    public function getDatabaseName();
    
    /**
     * Provides direct access to the internal static collection of registered entity type names.
     * @return IndexedCollection<string>
     */
    public function getEntityTypeNames();
    
    /**
     * Determines the name of the entity's schema.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return string
     */
    public function getSchemaName($entityTypeName);
    
    /**
     * Gets the name of the database table this entity belongs to.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return string
     */
    public function getTableName($entityTypeName);
    
    /**
     * Gets whether the entity class is representing a read-only view.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return bool
     */
    public function getIsView($entityTypeName);
    
    /**
     * Provides a string representing the SQL statements to recreate the full database.
     * @return string
     */
    public function getDatabaseDDL();
    
    /**
     * Provides a string representing the SQL statements to recreate the table this entity represents.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return string
     */
    public function getDDL($entityTypeName);
    
    /**
     * Gets an indexed collection of field names that make up the primary key.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return IndexedCollection<string>
     */
    public function getPrimaryKeyFieldNames($entityTypeName);
    
    /**
     * Provides access to this entity's relations.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return IndexedCollection<EntityRelation>
     */
    public function getEntityRelations($entityTypeName);
}

/**
 * Defines the methods needed in order to implement an Entity object.
 * Entities represent a single record within 
 *
 * @package WebCore
 * @subpackage Data
 */
interface IEntity extends ISerializable, IBindingTarget
{
    /**
     * Gets the metabase to which this entity belongs.
     * @return IEntityMetabase
     */
    public static function getMetabase();
    
    /**
     * Determines the name of the entity's schema.
     * @return string
     */
    public static function getSchemaName();
    
    /**
     * Gets the name of the database table this entity belongs to.
     * @return string
     */
    public static function getTableName();
    
    /**
     * Gets whether the entity class is representing a read-only view.
     * @return bool
     */
    public static function getIsView();
    
    /**
     * Gets an indexed collection of field names that make up the primary key.
     * @return IndexedCollection<string>
     */
    public static function getPrimaryKeyFieldNames();
    
    /**
     * Provides access to this entity's relations.
     * @return IndexedCollection<EntityRelation>
     */
    public static function getEntityRelations();
    
    /**
     * Gets the relation to the given entity type.
     * @return EntityRelation
     */
    public static function getEntityRelation($entityTypeName);
    
    /**
     * Provides a string representing the SQL statements to recreate the table this entity represents.
     * @return string
     */
    public static function getDDL();
    
    /**
     * Provides direct access to this entity's fields.
     * @return KeyedCollection<IEntityField>
     */
    public function getEntityFields();
    
    /**
     * Retrieves an IndexedCollection of the specified entityTypeName related to this entity.
     * @param string $entityTypeName The name of the type for the related entity.
     * @return IndexedCollection<$entityTypeName>
     */
    public function getRelatedEntities($entityTypeName);
    
    /**
     * Determines whether any of the entity fields has changed its value.
     * @return bool
     */
    public function getHasChanged();
    
    /**
     * Resets the values of each of the EntityFields to it OriginalValue
     */
    public function discardChanges();
    
    /**
     * Returns the current entity values into a KeyedCollection in which keys are the field names and the values are what the current values of the EntityFields
     * @return KeyedCollection
     */
    public function toDataSource();
}

/**
 * Represent an Entity Field
 *
 * @package WebCore
 * @subpackage Data
 */
interface IEntityField extends ISerializable
{
    /**
     * Gets the name of the column as defined in the database.
     * @return string
     */
    public function getFieldName();
    
    /**
     * Gets the data type as defined in the database.
     * @return string
     */
    public function getDbDataType();
    
    /**
     * Gets the current value for this entity field.
     * @return mixed
     */
    public function getValue();
    
    /**
     * Sets the current value for this entity field.
     * @param mixed $value
     */
    public function setValue($value);
    
    /**
     * Determines if the field is nullable
     * @return bool
     */
    public function getIsNullable();
    
    /**
     * Determines if the field's value is determined by the database engine.
     * Examples include auto-increment fields and calculated fields.
     * @return bool
     */
    public function getIsCalculated();
    
    /**
     * Gets the default value of this field as defined in the database.
     * @return mixed
     */
    public function getDefaultValue();
    
    /**
     * Gets the maximum character length as defined by the database engine.
     * @return int
     */
    public function getLength();
    
    /**
     * Returns original value the field was retrieved with
     * @return mixed
     */
    public function getInitialValue();
    
    /**
     * Sets the initial value for this entity field.
     * @param mixed $value
     */
    public function setInitialValue($value);
    
    /**
     * Determines if the current value of the entity is either null or empty.
     * @return bool
     */
    public function isNullOrEmpty();
    
    /**
     * Returns true if value changes
     * @return bool
     */
    public function getHasChanged();
    
    /**
     * Resets the current value to the initial value.
     */
    public function resetValue();
}

/**
 * Represents a data relation between the owning entity type and the entity defined in the instance of this class.
 * @package WebCore
 * @subpackage Data
 */
class EntityRelation extends SerializableObjectBase
{
    protected $entityTypeName;
    protected $localFieldName;
    protected $foreignFieldName;
    protected $isParentRelation;
    
    /**
     * Creates a new instance of this class
     * @param string $entityTypeName The target type name for this EntityRelation
     * @param string $localFieldProperty
     * @param string $foreignFieldProperty
     * @param bool $isParentRelation Indicates if relation makes the owning Entity type dependent on the specified $entityTypeName
     */
    public function __construct($entityTypeName, $localFieldName, $foreignFieldName, $isParentRelation)
    {
        if (is_string($entityTypeName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = entityName');
        if (is_string($localFieldName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = localProperty');
        if (is_string($foreignFieldName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = foreignProperty');
        if (is_bool($isParentRelation) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = isParentRelation');
        
        $this->entityTypeName   = $entityTypeName;
        $this->localFieldName   = $localFieldName;
        $this->foreignFieldName = $foreignFieldName;
        $this->isParentRelation = $isParentRelation;
    }
    
    /**
     * Determines the target type name for this EntityRelation.
     * This is not the entity owning this EntityRelation
     * @return string
     */
    public function getEntityTypeName()
    {
        return $this->entityTypeName;
    }
    
    /**
     * Gets the EntityRelation owner's EntityField name
     * @return string
     */
    public function getLocalFieldName()
    {
        return $this->localFieldName;
    }
    
    /**
     * Gets the name of the foreign EntityField name (i.e. The name of the field to which this relation is established).
     * @return string
     */
    public function getForeignFieldName()
    {
        return $this->foreignFieldName;
    }
    
    /**
     * Indicates if relation makes the owning Entity type dependent on the specified $entityTypeName
     * @example If the Addresses entity owns this relation, and this relation establishes a relation with the Cities entity, the Cities entity is considered a parent entity and thus this method returns true.
     * @return bool
     */
    public function getIsParentRelation()
    {
        return $this->isParentRelation;
    }
    
    /**
     * Gets a default entity instance of the class defined as entityTypeName.
     * This method does not create a default instance of the owning Entity type, but rather for the target entity type.
     * @return Entity
     */
    public function getEntity()
    {
        return DataContext::getInstance()->getAdapter($this->getEntityTypeName())->defaultEntity();
    }
    
    /**
     * Creates a default instance of this class.
     * @return EntityRelation
     */
    public static function createInstance()
    {
        return new EntityRelation("ISerializable", "ISerializable", "ISerializable");
    }
}

/**
 * Represents the base implementation of an entity field.
 * The main purpose of an entity field is to hold values and metadata for any given entity.
 *
 * @package WebCore
 * @subpackage Data
 */
abstract class EntityFieldBase extends SerializableObjectBase implements IEntityField
{
    protected $fieldName;
    protected $dbDataType;
    protected $value;
    protected $initialValue;
    protected $isNullable;
    protected $isCalculated;
    protected $defaultValue;
    protected $length;
    
    /**
     * Creates a new instance of this class
     * @param string $fieldName The name of the column as defined in the database data store
     * @param string $dbDataType The data type of the column as defined in the database engine
     * @param int $length The maximum length of the field (i.e. character length)
     * @param mixed $value The initial value of this entity field.
     * @param string $defaultValue The default value of the field as specified in the database.
     * @param bool $isNullable Set to true is the field is nullable. false otherwise.
     * @param bool $isCalculated Determines if the field is autoincremental or calculated
     */
    public function __construct($fieldName = '', $dbDataType = 'varchar', $length = 255, $value = '', $defaultValue = null, $isNullable = true, $isCalculated = false)
    {
        if (is_string($fieldName) === false || $fieldName == '')
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter fieldName must be a non-empty string.');
        if (is_string($dbDataType) === false || $dbDataType == '')
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter dbDataType must be a non-empty string.');
        if (is_numeric($length) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter length must be numeric.');
        if (is_bool($isNullable) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter isNullable must be boolean.');
        
        $this->fieldName    = $fieldName;
        $this->dbDataType   = $dbDataType;
        $this->value        = $value;
        $this->initialValue = $value;
        $this->isNullable   = $isNullable;
        $this->length       = $length;
        $this->defaultValue = $defaultValue;
        $this->isCalculated = $isCalculated;
    }
    
    /**
     * Gets the name of the column as defined in the database.
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }
    
    /**
     * Gets the data type as defined in the database.
     * @return string
     */
    public function getDbDataType()
    {
        return $this->dbDataType;
    }
    
    /**
     * Gets the current value for this entity field.
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Determines if the field is nullable
     * @return bool
     */
    public function getIsNullable()
    {
        return $this->isNullable;
    }
    
    /**
     * Determines if the field's value is determined by the database engine.
     * Examples include auto-increment fields and calculated fields.
     * @return bool
     */
    public function getIsCalculated()
    {
        return $this->isCalculated;
    }
    
    /**
     * Gets the default value of this field as defined in the database.
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
    
    /**
     * Sets the initial value of this entity field.
     * This enables automatic change tracking.
     * @param mixed $value
     */
    public function setInitialValue($value)
    {
        $this->initialValue = $value;
    }
    
    /**
     * Determines if the current value of the entity is either null or empty.
     * @return bool
     */
    public function isNullOrEmpty()
    {
        return (is_null($this->getValue()) || $this->getValue() == '');
    }
    
    /**
     * Gets the maximum character length as defined by the database engine.
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }
    
    /**
     * Returns original value the field was retrieved with
     * @return mixed
     */
    public function getInitialValue()
    {
        return $this->initialValue;
    }
    
    /**
     * Returns true if the value has changed
     * @return bool
     */
    public function getHasChanged()
    {
        return ($this->initialValue != $this->value);
    }
    
    /**
     * Sets the current value of this entity field.
     * @todo Use Translator!
     * @param mixed $value
     */
    public function setValue($value)
    {
        if (in_array($this->dbDataType, array(
            'float',
            'decimal',
            'money',
            'double'
        )))
        {
            $value = str_replace('$', '', $value);
        }
        
        $this->value = $value;
    }
    
    /**
     * Resets the current value to the original value.
     */
    public function resetValue()
    {
        $this->initialValue = $this->value;
    }
}

/**
 * Represent an Entity's field with primitive value
 * 
 * @package WebCore
 * @subpackage Data
 */
class EntityField extends EntityFieldBase
{
    /**
     * Creates a new instance of this class
     * @param string $fieldName The name of the column as defined in the database data store
     * @param string $dbDataType The data type of the column as defined in the database engine
     * @param int $length The maximum length of the field (i.e. character length)
     * @param mixed $value The initial value of this entity field.
     * @param mixed $defaultValue The default value of the field as specified in the database.
     * @param bool $isNullable Set to true is the field is nullable. false otherwise.
     * @param bool $isCalculated Determines if the field is autoincremental or calculated
     */
    public function __construct($fieldName, $dbDataType = 'varchar', $length = 255, $value = '', $defaultValue = null, $isNullable = false, $isCalculated = false)
    {
        parent::__construct($fieldName, $dbDataType, $length, $value, $defaultValue, $isNullable, $isCalculated);
    }
    
    /**
     * Creates a default instance of this class
     * @return EntityField
     */
    public static function createInstance()
    {
        return new EntityField('ISerializable');
    }
}

/**
 * Represents a field with binary content (BLOB).
 * @package WebCore
 * @subpackage Data
 */
class BinaryEntityField extends EntityFieldBase
{
    protected $deferredExecution;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $fieldName
     * @param string $dbDataType
     * @param bool $deferredExecution By default true to allow table adapters to skip the reading of the potentially large field content into memory.
     * @param bool $isNullable
     */
    public function __construct($fieldName, $dbDataType, $deferredExecution = true, $isNullable = false)
    {
        parent::__construct($fieldName, $dbDataType, 2147483647, '', null, $isNullable);
        $this->deferredExecution = $deferredExecution;
    }
    
    /**
     * This method always returns null as change tracking is not supported for binary fields.
     * @return mixed
     */
    public function getInitialValue()
    {
        return null;
    }
    
    /**
     * Sets entity value like resource
     * @param resource $value
     */
    public function setResourceValue(&$value)
    {
        if (!is_null($value))
        {
            if (is_resource($value) === false)
                throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter value must be a pointer to a resource.');
            
            $this->value = $value;
        }
        else
        {
            $this->value = null;
        }
    }
    
    /**
     * Determines whether the field value is only retrieved when the getValue() method is called.
     * @return int
     */
    public function isDeferredExecution()
    {
        return $this->deferredExecution;
    }
    
    /**
     * Gets the field's value as a string.
     * This method reads a pointer to a resource as it does not keep a copy of the resource in memory at all times.
     * @return string
     */
    public function getValue()
    {
        if (is_null($this->value))
            return null;
        if (is_resource($this->value) == false)
            return $this->value;
        
        $contents = '';
        fseek($this->value, 0);
        while (!feof($this->value))
            $contents .= fread($this->value, 1024);
        
        return $contents;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return BinaryEntityField
     */
    public static function createInstance()
    {
        return new BinaryEntityField('ISerializable', 'ISerializable');
    }
}

/**
 * Base class to represent an database entity
 *
 * @package WebCore
 * @subpackage Data
 */
abstract class EntityBase extends Popo implements IEntity
{
    /**
     * Creates a new entity based on a database table.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Provides direct access to this entity's fields.
     * @return KeyedCollection<IEntityField>
     */
    public function getEntityFields()
    {
        return $this->fields;
    }
    
    /**
     * Retrieves an IndexedCollection of the specified entityTypeName related to this entity.
     * @todo This method needs to be revisited when webcore.adapter.php is refactored. The where clause needs to be parameterized correctly, otherwise autoparsed. i.e. if where('sql') then autoparse. if (where('sql ? AND ?', $p1, $pn)) the parameterize
     * @param string $entityTypeName The name of the type for the related entity.
     * @return mixed. IndexedCollection<$entityTypeName> or $entityTypeName
     */
    public function getRelatedEntities($entityTypeName)
    {
        if (is_string($entityTypeName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter entityTypeName must be a non-empty string.');
        
        $relations = call_user_func(array(
            get_class($this),
            'getEntityRelations'
        ));
        foreach ($relations as $entityRelation)
        {
            if ($entityRelation->getEntityTypeName() == $entityTypeName)
            {
                // @ todo try and see if adding a schema works here. What about cross-schema querys
                $clause  = call_user_func(array(
                    $entityTypeName,
                    'getTableName'
                )) . '.' . $entityRelation->getForeignFieldName() . ' = ' . $this->getFieldValue($entityRelation->getLocalFieldName());
                $adapter = DataContext::getInstance()->getAdapter($entityTypeName)->where($clause);
                
                if ($entityRelation->getIsParentRelation())
                    return $adapter->selectOne();
                else
                    return $adapter->select();
            }
        }
        throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'No direct relations exist between this entity and ' . $entityTypeName . '.');
    }
    
    /**
     * Determines whether any of the entity fields has changed its value.
     * @return bool
     */
    public function getHasChanged()
    {
        foreach ($this->getEntityFields() as $entityField)
        {
            if ($entityField->getHasChanged() === true)
                return true;
        }
        return false;
    }
    
    /**
     * Resets the values of each of the EntityFields to it OriginalValue
     */
    public function discardChanges()
    {
        foreach ($this->getEntityFields() as $entityField)
            $entityField->resetValue();
    }
    
    /**
     * Returns first displayable (text) entity field within the EntityFields collection.
     * Return null if no varchar-typed field is found.
     * @return string
     */
    public function getFirstTextEntityField()
    {
        foreach ($this->getEntityFields() as $fieldName => $fieldObject)
        {
            if ($fieldObject->getDbDataType() == 'varchar')
                return $fieldObject;
        }
        return null;
    }
    
    // Custom POPO implementation
    
    /**
     * Gets an EntityField given its name.
     * This is a shortcut method.
     * @param string $fieldName
     * @return IEntityField
     */
    public function getField($fieldName)
    {
        return $this->getEntityFields()->getItem($fieldName);
    }
    
    /**
     * Returns a KeyedCollection of internal fields.
     *
     * @return KeyedCollection
     */
    public function getFields()
    {
        return $this->getEntityFields();
    }
    
    /**
     * Registers a new field with the given value
     *
     * @param string $fieldName
     * @param IEntityField $value
     */
    public function addFieldValue($fieldName, $value = null)
    {
        if ($this->hasField($fieldName))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "The field with the name '$fieldName' already exists.");
        if (ObjectIntrospector::isA($value, 'IEntityField') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter value must implement IEntityField.");
        $this->getEntityFields()->setItem($fieldName, $value);
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
        
        $this->getField($fieldName)->setValue($value);
    }
    
    /**
     * Gets the value of an EntityField within this object, given its name.
     *
     * @param string $fieldName
     * @return mixed
     */
    public function getFieldValue($fieldName)
    {
        if (is_string($fieldName) === true && $this->hasField($fieldName))
            return $this->getField($fieldName)->getValue();
        throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = fieldName');
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
        
        return $this->getEntityFields()->keyExists($fieldName);
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
            return $this->getFieldValue($name);
        
        if (call_user_func_array(array(
            get_class($this),
            'getEntityRelation'
        ), array(
            $name
        )) !== null)
        {
            /**
             * @var EntityRelation
             */
            $entityRelation = call_user_func_array(array(
                get_class($this),
                'getEntityRelation'
            ), array(
                $name
            ));
            return $this->getRelatedEntities($entityRelation->getEntityTypeName());
        }
        
        throw new SystemException(SystemException::EX_INVALIDKEY, "No field name or related entity was found under '$name'.");
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
     * Returns the current entity values into a KeyedCollection in which keys are
     * the field names and the values are what the current values of the EntityFields
     *
     * @return KeyedCollection
     */
    public function toDataSource()
    {
        $pks              = $this->getPrimaryKeyFieldNames();
        $fieldsCollection = new KeyedCollection();
        $className        = get_class($this);
        
        foreach ($this->getFields() as $fieldName => $entityField)
        {
            $fieldValue = $entityField->getValue();
            $fieldsCollection->setValue($fieldName, $fieldValue);
        }
        
        $fieldsCollection->setValue('__entityName', call_user_func(array(
            $className,
            'getTableName'
        )));
        $fieldsCollection->setValue('__isView', call_user_func(array(
            $className,
            'getIsView'
        )));
        $fieldsCollection->setValue('__primaryKeys', $pks->getArrayReference());
        
        return $fieldsCollection;
    }
    
    // END OF POPO implementation
    
    /**
     * Databinds datasource to Entity
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
                
                if (ObjectIntrospector::isA($field, 'BinaryEntityField') && ObjectIntrospector::isA($value, 'PostedFile') && $value->getErrorCode() == UPLOAD_ERR_OK)
                {
                    $resource = fopen($value->getTempFileName(), "r");
                    $field->setValue($resource);
                }
                else
                    $this->setFieldValue($fieldName, $value);
            }
        }
    }
    
    // Shortcut methods.
    
    /**
     * Registers an entity field in this object.
     * This is a shorcut method.
     * 
     * @param string $fieldName The name of the column as defined in the database data store
     * @param string $dbDataType The data type of the column as defined in the database engine
     * @param int $length The maximum length of the field (i.e. character length)
     * @param mixed $value The initial value of this entity field.
     * @param mixed $defaultValue The default value of the field as specified in the database.
     * @param bool $isNullable Set to False is the field is nullable. True otherwise.
     * @param bool $isCalculated Determines if the field is autoincremental or calculated.
     */
    protected function registerEntityField($fieldName, $dbDataType = 'varchar', $length = 255, $value = '', $defaultValue = null, $isNullable = true, $isCalculated = false)
    {
        $field = new EntityField($fieldName, $dbDataType, $length, $defaultValue, $isNullable, $isCalculated);
        $this->getEntityFields()->setItem($fieldName, $field);
    }
    
    /**
     * Registers a binary entity field in this object.
     * This is a shorcut method.
     * 
     * @param string $fieldName
     * @param string $dbDataType
     * @param bool $deferredExecution By default true to allow table adapters to skip the reading of the potentially large field content into memory.
     * @param bool $isNullable
     */
    protected function registerBinaryEntityField($fieldName, $dbDataType, $deferredExecution = true, $isNullable = true)
    {
        $field = new BinaryEntityField($fieldName, $dbDataType, $deferredExecution, $isNullable);
        $this->getEntityFields()->setItem($fieldName, $field);
    }
}

/**
 * Provides a base implementation of a generic Entity Metabase. This class is intended for code generation.
 * @todo implement a metabase caching mechanism just like classloader, except add a hash or something that expires the cache after the metabase has been regenerated.
 * @package WebCore
 * @subpackage Data
 */
abstract class EntityRegistryBase extends ObjectBase implements IEntityMetabase
{
    protected $entityTypeNames;
    protected $storeProcNameData;
    protected $schemaNameData;
    protected $tableNameData;
    protected $isViewData;
    protected $ddlData;
    protected $primaryKeyFieldNames;
    protected $entityRelations;
    
    protected function __construct()
    {
        $this->entityTypeNames      = new IndexedCollection();
        $this->entityRelations      = new KeyedCollection();
        $this->ddlData              = new KeyedCollection();
        $this->isViewData           = new KeyedCollection();
        $this->storeProcNameData    = new KeyedCollection();
        $this->tableNameData        = new KeyedCollection();
        $this->schemaNameData       = new KeyedCollection();
        $this->primaryKeyFieldNames = new KeyedCollection();
    }
    
    /**
     * Loads the metadata for a given entity type name
     * @return bool Returns true if metadata was loaded. false otherwise.
     */
    protected abstract function loadMetadata($entityTypeName);
    
    /**
     * Provides direct access to the internal static collection of registered entity type names.
     * @return IndexedCollection<string>
     */
    public function getEntityTypeNames()
    {
        return $this->entityTypeNames;
    }
    
    /**
     * Determines the name of the entity's schema.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return string
     */
    public function getSchemaName($entityTypeName)
    {
        if ($this->schemaNameData->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        return $this->schemaNameData->getValue($entityTypeName);
    }
    
    /**
     * Gets the name of the database table this entity belongs to.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return string
     */
    public function getTableName($entityTypeName)
    {
        if ($this->tableNameData->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        return $this->tableNameData->getValue($entityTypeName);
    }
    
    /**
     * Gets whether the entity class is representing a read-only view.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return bool
     */
    public function getIsView($entityTypeName)
    {
        if ($this->isViewData->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        return $this->isViewData->getValue($entityTypeName);
    }
    
    /**
     * Provides a string representing the SQL statements to recreate the full database.
     * @return string
     */
    public function getDatabaseDDL()
    {
        $ddl = '';
        foreach ($this->entityTypeNames as $entityTypeName)
            $ddl .= $this->getDDL($entityTypeName) . ";\r\n\r\n";
        
        return $ddl;
    }
    
    /**
     * Provides a string representing the SQL statements to recreate the table this entity represents.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return string
     */
    public function getDDL($entityTypeName)
    {
        if ($this->ddlData->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        return $this->ddlData->getValue($entityTypeName);
    }
    
    /**
     * Gets an indexed collection of field names that make up the primary key.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return IndexedCollection<string>
     */
    public function getPrimaryKeyFieldNames($entityTypeName)
    {
        if ($this->isViewData->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        if ($this->getIsView($entityTypeName))
            return new IndexedCollection();
        
        if ($this->primaryKeyFieldNames->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        if ($this->primaryKeyFieldNames->keyExists($entityTypeName) === false)
            return new IndexedCollection();
        return $this->primaryKeyFieldNames->getItem($entityTypeName);
    }
    
    /**
     * Provides access to this entity's relations.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return IndexedCollection<EntityRelation>
     */
    public function getEntityRelations($entityTypeName)
    {
        if ($this->getIsView($entityTypeName))
            return new IndexedCollection();
        
        if ($this->entityRelations->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        if ($this->entityRelations->keyExists($entityTypeName) === false)
            return new IndexedCollection();
        return $this->entityRelations->getItem($entityTypeName);
    }
    
    /**
     * Provides the data relation between the provided entity type names.
     * @param string $entityTypeName The name of the Entity type to extract metadata from.
     * @return EntityRelation
     */
    public function getEntityRelation($entityTypeName, $foreignEntityTypeName)
    {
        if ($this->entityRelations->keyExists($entityTypeName) === false)
            $this->loadMetadata($entityTypeName);
        $relations = $this->entityRelations->getItem($entityTypeName);
        foreach ($relations as $relation)
        {
            if (strtolower($relation->getEntityTypeName()) == strtolower($foreignEntityTypeName))
                return $relation;
        }
        throw new SystemException(SystemException::EX_INVALIDKEY, "'$entityTypeName' is not related to '$foreignEntityTypeName'.");
    }
    
    /**
     * Shortcut method for registering basic metadata about an entity.
     * @param string $entityTypeName
     * @param string $schemaName
     * @param string $tableName
     * @param bool $isView
     */
    protected function registerEntity($entityTypeName, $schemaName, $tableName, $isView)
    {
        if ($this->entityTypeNames->containsValue($entityTypeName) === false)
            $this->entityTypeNames->addValue($entityTypeName);
        
        if ($this->schemaNameData->keyExists($entityTypeName) === false)
            $this->schemaNameData->setValue($entityTypeName, $schemaName);
        
        if ($this->tableNameData->keyExists($entityTypeName) === false)
            $this->tableNameData->setValue($entityTypeName, $tableName);
        
        if ($this->isViewData->keyExists($entityTypeName) === false)
            $this->isViewData->setValue($entityTypeName, $isView);
    }
    
    /**
     * Shortcut method to register an entity's DDL
     * @param string $entityTypeName
     * @param string $ddl
     */
    protected function registerEntityDDL($entityTypeName, $ddl)
    {
        if ($this->ddlData->keyExists($entityTypeName) === false)
            $this->ddlData->setValue($entityTypeName, $ddl);
    }
    
    /**
     * Register a new primary key
     * @param string $entityTypeName
     * @param string $fieldName
     */
    protected function registerEntityPriKey($entityTypeName, $fieldName)
    {
        if ($this->primaryKeyFieldNames->keyExists($entityTypeName) === false)
            $this->primaryKeyFieldNames->setValue($entityTypeName, new IndexedCollection());
        
        $this->primaryKeyFieldNames->getItem($entityTypeName)->addValue($fieldName);
    }
    
    /**
     * Adds an Entity relation to the statically-coupled collection of EntityRelations of this class.
     * This is a shorcut method.
     * @param string $entityTypeName The local type name for this EntityRelation
     * @param string $localFieldName
     * @param string $entityTypeName The foreign type name for this EntityRelation
     * @param string $foreignFieldName
     * @param bool $isParentRelation Indicates if relation makes the owning Entity type dependent on the specified $entityTypeName
     */
    protected function registerEntityRelation($entityTypeName, $localFieldName, $foreignTypeName, $foreignFieldName, $isParentRelation)
    {
        $relation = new EntityRelation($foreignTypeName, $localFieldName, $foreignFieldName, $isParentRelation);
        if ($this->entityRelations->keyExists($entityTypeName) === false)
            $this->entityRelations->setValue($entityTypeName, new IndexedCollection());
        
        $this->entityRelations->getItem($entityTypeName)->addItem($relation);
    }
}
?>