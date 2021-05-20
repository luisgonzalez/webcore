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
require_once "webcore.data.php";
require_once "webcore.data.entity.php";

/**
 * Provides base implementation for an DataTable Adapter
 *
 * @package WebCore
 * @subpackage Data
 */
interface IDataTableAdapter extends IHelper
{
    /**
     * Creates an DataTable adapter
     *
     * @param string $schema
     * @param string $tableName
     * @param IDataConnection $connection
     */
    public function __construct($schema, $tableName, $connection);
    
    /**
     * Returns the SQL SELECT statement to execute.
     *
     * @return SqlCommandBase
     */
    public function getSelectSqlCommand();
    
    /**
     * Returns the SQL INSERT statement to execute.
     *
     * @return SqlCommandBase
     */
    public function getInsertSqlCommand();
    
    /**
     * Returns the SQL UPDATE statement to execute.
     *
     * @return SqlCommandBase
     */
    public function getUpdateSqlCommand();
    
    /**
     * Returns the SQL DELETE statement to execute.
     *
     * @return SqlCommandBase
     */
    public function getDeleteSqlCommand();
    
    /**
     * @return IDataConnection
     */
    public function getConnection();
}

/**
 * Provides common implementation for an DataTable Adapter
 *
 * @package WebCore
 * @subpackage Data
 */
abstract class DataTableAdapterBase extends HelperBase implements IDataTableAdapter
{
    const SORT_ASCENDING  = 'ASC';
    const SORT_DESCENDING = 'DESC';
    
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_LEFT  = 'LEFT';
    const JOIN_TYPE_RIGHT = 'RIGHT';
    const JOIN_TYPE_CROSS = 'CROSS';

    /**
     * @var DataConnectionBase
     */
    protected $connection;
    /**
     * @var IndexedCollection
     */
    protected $selectFields;
    /**
     * @var KeyedCollection
     */
    protected $valueMembers;
    protected $tableName;
    protected $limit;
    protected $offset;
    protected $concurrencyMode;
    /**
     * @var KeyedCollection
     */
    protected $keypairs;
    /**
     * @var IndexedCollection
     */
    protected $orderByFields;
    protected $isDistinct;
    protected $deferredExecution;
    /**
     * @var KeyedCollection
     */
    protected $joins;
    /**
     * @var IndexedCollection
     */
    protected $aggregateFields;
    /**
     * @var IndexedCollection
     */
    protected $whereClauses;
    /**
     * @var SqlCommand
     */
    protected $sqlCommand;
    
    /**
     * Creates an DataTable adapter
     *
     * @param string $schema
     * @param string $tableName
     * @param IDataConnection $connection
     */
    public function __construct($schema, $tableName, $connection)
    {
        if (ObjectIntrospector::isA($connection, 'DataConnectionBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = connection');
        
        if (is_string($tableName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = entity');
        
        $this->connection        = $connection;
        $this->tableName         = $tableName;
        $this->whereClauses      = new IndexedCollection();
        $this->selectFields      = new IndexedCollection();
        $this->orderByFields     = new IndexedCollection();
        $this->aggregateFields   = new IndexedCollection();
        $this->deferredExecution = true;
        $this->concurrencyMode   = DataConnectionBase::CONCURRENCY_LAST_IN_WINS;
    }
    
    /**
     * @return IDataConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    public function __clone()
    {
        $sqlCommandState  = serialize($this->sqlCommand);
        $this->sqlCommand = unserialize($sqlCommandState);
    }
    
    /**
     * Returns a correctly quoted string representing the provided identifier
     * 
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $this->getConnection()->quoteIdentifier($identifier);
    }
    
    /**
     * Returns a WHERE clause
     *
     * @param bool $includeValues
     * 
     * @return string
     */
    protected function getWhereClauses($includeValues = false)
    {
        $whereString = "";
        
        if ($this->whereClauses->getCount() > 0)
        {
            $whereString = " WHERE ";
            
            foreach ($this->whereClauses as $whereClause)
            {
                if ($whereString != " WHERE " && $whereClause->getAggregator() == "")
                    $whereClause->setAggregator("AND");
                
                $whereString .= $whereClause->getAggregator() . " ";
                
                if (strstr($whereClause->getIdentifier(), "(") == false)
                {
                    if (is_null($whereClause->getTable()) || $whereClause->getTable() == '')
                        $whereClause->setTable($this->tableName);
                    
                    $whereString .= $this->quoteIdentifier($whereClause->getTable());
                    $whereString .= ".";
                    $whereString .= $this->quoteIdentifier($whereClause->getIdentifier());
                }
                else
                    $whereString .= $whereClause->getIdentifier();
                
                $whereString .= " " . $whereClause->getOperator() . " ";
                
                if (is_array($whereClause->getValue()))
                {
                    if (count($whereClause->getValue()) == 2)
                    {
                        if ($includeValues)
                        {
                            $value = $whereClause->getValue();
                            $whereString .= $value[0] . ' AND ' . $value[1];
                        }
                        else
                            $whereString .= "? AND ? ";
                    }
                    else
                    {
                        if ($includeValues)
                        {
                            $whereString .= "(" . substr($whereClause->getValue(), 0, strlen($whereClause->getValue()) - 2) . ")";
                        }
                        else
                        {
                            $tempValue = str_repeat("?, ", count($whereClause->getValue()));
                            $whereString .= "(" . substr($tempValue, 0, strlen($tempValue) - 2) . ")";
                        }
                    }
                }
                else
                {
                    $whereString .= ($includeValues) ? $whereClause->getValue() : "? ";
                }
            }
        }
        
        return $whereString;
    }
    
    /**
     * Sets WHERE values at SQL Command
     */
    protected function setWhereValues()
    {
        $translator = $this->connection->getTypeTranslator();
        
        foreach ($this->whereClauses->getArrayReference() as $whereClause)
        {
            $value = $whereClause->getValue();
            
            if (is_array($value))
            {
                foreach ($value as $singleValue)
                    $this->sqlCommand->addParam($whereClause->getIdentifier(), $translator->getSqlParameterDataType($singleValue), $singleValue);
            }
            else
            {
                $this->sqlCommand->addParam($whereClause->getIdentifier(), $translator->getSqlParameterDataType($value), $value);
            }
        }
        
        $this->whereClauses = new IndexedCollection();
    }
    
    /**
     * Adds a SQL's WHERE clauses
     *
     * @param WhereClause $clause
     */
    public function addWhereClause($clause)
    {
        if (ObjectIntrospector::isA($clause, 'WhereClause'))
            $this->whereClauses->addItem($clause);
        else
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = clause');
    }
    
    /**
     * Returns a standard SQL query to delete the entity.
     *
     * @return SqlCommandBase
     */
    public function getDeleteSqlCommand()
    {
        $includeVars = ObjectIntrospector::isA($this->connection, 'MsSqlConnection');
        if (function_exists("sqlsrv_connect")) $includeVars = false;
        
        $strSQL = "DELETE FROM " . $this->quoteIdentifier($this->tableName);
        $strSQL .= $this->getWhereClauses($includeVars);
        
        $this->sqlCommand = $this->connection->createCommand();
        $this->sqlCommand->setCommandText($strSQL);
        $this->setWhereValues();
        
        return $this->sqlCommand;
    }
    
    /**
     * Returns UPDATE Query for execute
     * 
     * @return SqlCommandBase
     */
    public function getUpdateSqlCommand()
    {
        $translator = $this->connection->getTypeTranslator();
        
        $includeVars = ObjectIntrospector::isA($this->connection, 'MsSqlConnection');
        if (function_exists("sqlsrv_connect")) $includeVars = false;
        
        $strSQL = "UPDATE " . $this->quoteIdentifier($this->tableName);
        $strSQL .= " SET " . $this->keypairs->implode("%s = ?");
        $strSQL .= $this->getWhereClauses($includeVars);
        $this->sqlCommand = $this->connection->createCommand();
        
        $this->sqlCommand->setCommandText($strSQL);
        foreach ($this->keypairs->getArrayReference() as $key => $value)
            $this->sqlCommand->addParam($key, $translator->getSqlParameterDataType($value), $value);
        
        $this->setWhereValues();
        return $this->sqlCommand;
    }
    
    /**
     * Returns INSERT Query for execute
     *
     * @return SqlCommandBase
     */
    public function getInsertSqlCommand()
    {
        $translator = $this->connection->getTypeTranslator();
        
        $strSQL = "INSERT INTO " . $this->quoteIdentifier($this->tableName);
        $strSQL .= " ( " . $this->selectFields->implode() . " )";
        $strSQL .= " VALUES (" . $this->valueMembers->implode("?") . ")";
        
        $this->sqlCommand = $this->connection->createCommand();
        $this->sqlCommand->setCommandText($strSQL);
        
        foreach ($this->valueMembers->getArrayReference() as $key => $value)
            $this->sqlCommand->addParam($key, $translator->getSqlParameterDataType($value), $value);
        
        return $this->sqlCommand;
    }
    
    /**
     * Returns the SQL SELECT statement to execute.
     *
     * @return SqlCommandBase
     */
    public function getSelectSqlCommand()
    {
        if ($this->selectFields->isEmpty())
            $this->generateSelectFields();
        
        $strSQL = "SELECT ";
        if ($this->isDistinct === true)
            $strSQL .= " DISTINCT ";
        
        $strSQL .= $this->selectFields->implode("%s");
        $strSQL .= " FROM " . $this->quoteIdentifier($this->tableName);
        
        if (!is_null($this->joins))
            $strSQL .= implode(" ", $this->joins->getArrayReference());
        
        $includeVars = ObjectIntrospector::isA($this->connection, 'MsSqlConnection');
        if (function_exists("sqlsrv_connect")) $includeVars = false;
        $strSQL .= $this->getWhereClauses($includeVars);
        
        if ($this->aggregateFields->getCount() > 0)
        {
            $groupBy = new IndexedCollection();
            
            foreach ($this->selectFields as $member)
            {
                if ($this->aggregateFields->containsValue($member) == false)
                {
                    $memberName = explode(" AS ", $member);
                    $memberName = $memberName[0];
                    $groupBy->addItem($memberName);
                }
            }
            
            if ($groupBy->getCount() > 0)
                $strSQL .= " GROUP BY " . $groupBy->implode("%s");
        }
        
        if ($this->orderByFields->getCount() > 0)
            $strSQL .= " ORDER BY " . $this->orderByFields->implode("%s"); // @todo in MSSQL function fields need to specify the expression and not the alias of the field name
        
        $this->sqlCommand = $this->connection->createCommand();
        $this->sqlCommand->setCommandText($strSQL);
        $this->setWhereValues();
        
        return $this->sqlCommand;
    }
    
    /**
     * Generate the column names to select for the current entity.
     *
     */
    private function generateSelectFields()
    {
        $entity             = $this->defaultEntity();
        $this->selectFields = new IndexedCollection();
        $dataSettings       = Settings::getValue(Settings::SKEY_DATA);
        
        foreach ($entity->getFields() as $field)
        {
            if (isset($dataSettings[Settings::KEY_DATA_LOGICALDELETEFIELD]) && $field->getFieldName() == $dataSettings[Settings::KEY_DATA_LOGICALDELETEFIELD])
                continue;
            
            if (ObjectIntrospector::isA($field, 'BinaryEntityField'))
            {
                if ($this->deferredExecution && $field->isDeferredExecution())
                    continue;
            }
            
            $fieldName = $this->quoteIdentifier($entity->getTableName());
            $fieldName .= "." . $this->quoteIdentifier($field->getFieldName());
            
            $this->selectFields->addItem($fieldName);
        }
    }
    

    /**
     * Sets the indexed collection containing the column names to select.
     *
     * @param string $members
     */
    public function setSelectFields($members)
    {
        if (is_string($members) === true)
        {
            $this->selectFields    = new IndexedCollection();
            $this->aggregateFields = new IndexedCollection();
            $members               = explode(",", $members);
            
            foreach ($members as $member)
            {
                $memberName = trim($member);
                $this->selectFields->addItem($memberName);
            }
        }
    }
    
    /**
     * Adds a member in select statement
     *
     * @param string $member
     * @param string $table
     * @param string $alias
     * @param bool $isAggregating
     */
    public function addSelectMember($member, $table = '', $alias = '', $isAggregating = false)
    {
        if (is_string($member) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = member');
        if (is_string($table) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = table');
        if (is_string($alias) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = alias');
        
        if ($isAggregating || ($table == '' && $alias != ''))
        {
            $memberName = $member;
        }
        else
        {
            $memberName = $this->quoteIdentifier($member);
            
            if ($table != '')
                $memberName = $this->quoteIdentifier($table) . "." . $memberName;
        }
        
        if ($alias != '')
            $memberName .= ' AS ' . $this->quoteIdentifier($alias);
        
        $this->selectFields->addItem($memberName);
        
        if ($isAggregating)
            $this->aggregateFields->addItem($memberName);
    }
    
    /**
     * Sets ORDER BY clause
     *
     * @param string $orderBy
     * @param string $direction
     */
    protected function setOrderBy($orderBy, $direction)
    {
        if (is_string($orderBy) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = orderBy');
        if (is_string($direction) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = direction');
        
        $clause = "$orderBy $direction";
        $this->orderByFields->addItem($clause);
    }
    
    /**
     * Sets deferred execution value
     *
     * @param bool $deferredExecution
     */
    public function setDeferredExecution($deferredExecution)
    {
        $this->deferredExecution = $deferredExecution;
    }
    
    /**
     * Sets distinct execution value
     *
     * @param bool $distinct
     */
    public function setDistinct($distinct)
    {
        $this->isDistinct = $distinct;
    }
    
    /**
     * Returns true if it's read-only
     *
     * @return bool
     */
    public function getIsReadOnly()
    {
        return $this->defaultEntity()->getIsView();
    }
    
    /**
     * Returns default entity
     *
     * @return EntityBase
     */
    public function defaultEntity()
    {
        $className = $this->tableName;
        
        return new $className();
    }
    
    /**
     * Adds a field to selection
     *
     * @param string $table
     * @param string $field
     * @param string $alias
     * 
     * @return DataTableAdapterBase
     */
    public function addField($table, $field, $alias = '')
    {
        $this->addSelectMember($field, $table, $alias);
        
        return $this;
    }
    
    /**
     * Adds an aggregate function to selection
     *
     * @param string $function
     * @param string $alias
     * 
     * @return DataTableAdapterBase
     */
    public function addAggregateField($function, $alias)
    {
        $this->addSelectMember($function, '', $alias, true);
        
        return $this;
    }
    
    /**
     * Adds a function to selection
     *
     * @param string $function
     * @param string $alias
     * 
     * @return DataTableAdapterBase
     */
    public function addFunctionField($function, $alias)
    {
        $this->addSelectMember($function, '', $alias);
        
        return $this;
    }
    
    /**
     * Joins a related table
     *
     * @param string $relatedName
     * @param str type
     * 
     * @return DataTableAdapterBase
     */
    public function joinRelated($relatedEntityName, $type = self::JOIN_TYPE_INNER)
    {
        if (is_string($relatedEntityName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = relatedName');
        
        $entity = $this->defaultEntity();
        $entity->getEntityRelations();
        /**
         * @var EntityRelation
         */
        $relation     = $entity->getEntityRelation($relatedEntityName);
        $relatedTable = call_user_func(array(
            $relation->getEntityTypeName(),
            'getTableName'
        ));
        
        $clause = $relatedTable . "." . $relation->getForeignFieldName() . " = ";
        $clause .= $entity->getTableName() . "." . $relation->getLocalFieldName();
        $this->join($relatedTable, $clause, $type);
        
        return $this;
    }
    
    /**
     * Right joins a related table
     *
     * @param string $relatedName
     * @return DataTableAdapterBase
     */
    public function rightJoinRelated($relatedName)
    {
        return $this->joinRelated($relatedName, self::JOIN_TYPE_RIGHT);
    }
    
    /**
     * Left joins a related table
     *
     * @param string $relatedName
     * @return DataTableAdapterBase
     */
    public function leftJoinRelated($relatedName)
    {
        return $this->joinRelated($relatedName, self::JOIN_TYPE_LEFT);
    }
    
    /**
     * Joins a table
     *
     * @param string $tableName
     * @param string $clause
     * @param string type One ot the JOIN_TYPE_* constants
     * @param string $alias
     * 
     * @return DataTableAdapterBase
     */
    public function join($tableName, $clause, $type = self::JOIN_TYPE_INNER, $alias = "")
    {
        if (is_string($tableName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = tableName');
        
        if (is_string($clause) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = clause');
        
        if (is_string($type) === false || in_array($type, array(
            self::JOIN_TYPE_CROSS,
            self::JOIN_TYPE_INNER,
            self::JOIN_TYPE_LEFT,
            self::JOIN_TYPE_RIGHT
        )) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = type');
        
        if (is_null($this->joins))
            $this->joins = new KeyedCollection();
        
        if ($this->joins->keyExists($tableName . $alias))
            return $this;
        
        if ($alias == "")
            $join = " $type JOIN " . $this->quoteIdentifier($tableName) . " ON " . $clause;
        else
            $join = " $type JOIN " . $this->quoteIdentifier($tableName) . "  AS " . $alias . " ON " . $clause;
        
        $this->joins->setValue($tableName . $alias, $join);
        
        return $this;
    }
    
    /**
     * Inner Joins a table
     *
     * @param string $tableName
     * @param string $clause
     * @param string $alias
     * 
     * @return DataTableAdapterBase
     */
    public function innerJoinAs($tableName, $clause, $alias)
    {
        return $this->join($tableName, $clause, self::JOIN_TYPE_INNER, $alias);
    }
    
    /**
     * Inner Joins a table
     *
     * @param string $tableName
     * @param string $clause
     * @return DataTableAdapterBase
     */
    public function innerJoin($tableName, $clause)
    {
        return $this->join($tableName, $clause, self::JOIN_TYPE_INNER);
    }
    
    /**
     * Left joins a table
     *
     * @param string $tableName
     * @param string $clause
     * @return DataTableAdapterBase
     */
    public function leftJoin($tableName, $clause)
    {
        return $this->join($tableName, $clause, self::JOIN_TYPE_LEFT);
    }
    
    /**
     * Right joins a table
     *
     * @param string $tableName
     * @param string $clause
     * @return DataTableAdapterBase
     */
    public function rightJoin($tableName, $clause)
    {
        return $this->join($tableName, $clause, self::JOIN_TYPE_RIGHT);
    }
    
    /**
     * Cross joins a table
     *
     * @param string $tableName
     * @param string $clause
     * @return DataTableAdapterBase
     */
    public function crossJoin($tableName, $clause)
    {
        return $this->join($tableName, $clause, self::JOIN_TYPE_CROSS);
    }
    
    /**
     * Executes an aggregate function
     *
     * @param string $select
     * @return KeyedCollection
     */
    public function aggregate($select)
    {
        if (is_string($select) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = select');
        
        $this->setSelectFields($select);
        $this->limit         = 1;
        $this->offset        = 0;
        $this->orderByFields = new IndexedCollection();
        
        $rows = $this->fetchRows($this->getSelectSqlCommand());
        
        if ($rows->getCount() == 1)
        {
            $row = $rows->getValue(0);
            foreach ($row->getKeys() as $key)
            {
                return $row->getValue($key);
            }
        }
    }
    
    /**
     * Copies values from a row to an Entity
     *
     * @param KeyedCollection $row
     * @return Entity
     */
    protected function entityFromRow($row)
    {
        $entity = $this->defaultEntity();
        
        foreach ($entity->getFields() as $field)
        {
            $fieldName  = $field->getFieldName();
            $fieldValue = null;
            
            if ($row->keyExists($fieldName))
                $fieldValue = $row->getValue($fieldName);
            
            if (ObjectIntrospector::isA($field, 'BinaryEntityField'))
            {
                $fieldResource = tmpfile();
                fwrite($fieldResource, $fieldValue);
                $entity->setFieldValue($fieldName, $fieldResource);
            }
            else
            {
                $entity->setFieldValue($fieldName, $fieldValue);
                $field->setInitialValue($fieldValue);
            }
        }
        
        return $entity;
    }
    
    /**
     * Copies values from a row to a Class
     *
     * @param KeyedCollection $row
     * @return Popo
     */
    protected function popoFromRow($row)
    {
        $popo = new Popo();
        
        foreach ($row as $key => $value)
            $popo->addFieldValue($key, $value);
        
        return $popo;
    }
    
    /**
     * Returns the first occurrence of a record matching the provided where clause.
     *
     * @param bool $deferredExecution
     * @return Entity
     */
    public function selectOne($deferredExecution = false)
    {
        $this->setDeferredExecution($deferredExecution);
        $this->limit  = 1;
        $this->offset = 0;
        
        $row = $this->fetchRows($this->getSelectSqlCommand());
        if ($row->isEmpty())
            return null;
        
        return $this->entityFromRow($row->getValue(0));
    }
    
    /**
     * Returns the first occurrence of a record matching the provided where clause.
     * If no record matches, returns the default Entity for the Adapter
     *
     * @param bool $deferredExecution
     * @return Entity
     */
    public function selectOneOrDefault($deferredExecution = false)
    {
        $entity = $this->selectOne($deferredExecution);
        
        if (is_null($entity) === false)
            return $entity;
        else
            return $this->defaultEntity();
    }
    
    /**
     * Executes a SELECT statement
     * and retrieve a new entity
     *
     * @param bool $distinct
     * @param bool $deferredExecution
     * @return IndexedCollection
     */
    private function selectRows($distinct = false, $deferredExecution = true)
    {
        $this->setDistinct($distinct);
        $this->setDeferredExecution($deferredExecution);
        
        $rows = $this->fetchRows($this->getSelectSqlCommand());
        
        return $rows;
    }
    
    /**
     * Activates DISTINCT hint
     *
     * @return DataTableAdapterBase
     */
    public function distinct()
    {
        $this->setDistinct(true);
        
        return $this;
    }
    
    /**
     * Executes a SELECT statement and retrieve a scalar
     *
     * @return mixed
     */
    public function selectScalar()
    {
        $this->setDeferredExecution(true);
        $this->limit  = 1;
        $this->offset = 0;
        
        $row = $this->fetchRows($this->getSelectSqlCommand());
        
        if ($row->isEmpty())
            return null;
        
        $data = $row->getValue(0);
        $data = $data->getArrayReference();
        $keys = array_keys($data);
        
        return $data[$keys[0]];
    }
    
    /**
     * Executes a SELECT statement and retrieve a new entity
     *
     * @param bool $distinct
     * @param bool $deferredExecution
     * @return IndexedCollection
     */
    public function selectNew($distinct = false, $deferredExecution = true)
    {
        $currentData = new IndexedCollection();
        $rows        = $this->selectRows($distinct, $deferredExecution);
        
        foreach ($rows as $row)
        {
            $currentClass = $this->popoFromRow($row);
            $currentData->addItem($currentClass);
        }
        
        return $currentData;
    }
    
    /**
     * Executes a SELECT statement and return an Entity
     *
     * @param bool $deferredExecution
     * @return mixed
     */
    public function selectNewOne($deferredExecution = false)
    {
        $this->setDeferredExecution($deferredExecution);
        $this->limit  = 1;
        $this->offset = 0;
        
        $row = $this->fetchRows($this->getSelectSqlCommand());
        
        if ($row->isEmpty())
            return null;
        
        return $this->popoFromRow($row->getValue(0));
    }
    
    /**
     * Executes a SELECT statement
     *
     * @param bool $distinct
     * @param bool $deferredExecution
     * @return IndexedCollection
     */
    public function select($distinct = false, $deferredExecution = true)
    {
        $currentData = new IndexedCollection();
        $rows        = $this->selectRows($distinct, $deferredExecution);
        
        foreach ($rows as $row)
        {
            $currentEntity = $this->entityFromRow($row);
            $currentData->addItem($currentEntity);
        }
        
        return $currentData;
    }
    
    /**
     * Sets ORDER BY clause
     *
     * @param string $clause
     * @return DataTableAdapterBase
     */
    public function orderBy($clause)
    {
        $clauses = explode(", ", $clause);
        
        foreach ($clauses as $orderBy)
            $this->setOrderBy(trim($orderBy), self::SORT_ASCENDING);
        
        return $this;
    }
    
    /**
     * Sets ORDER BY DESC clause
     *
     * @param string $clause
     * @return DataTableAdapterBase
     */
    public function orderByDescending($clause)
    {
        $clauses = explode(", ", $clause);
        
        foreach ($clauses as $orderBy)
            $this->setOrderBy(trim($orderBy), self::SORT_DESCENDING);
        
        return $this;
    }
    
    /**
     * Adds clauses to SQL statement
     *
     * @param string $clauses
     * @return DataTableAdapterBase
     */
    public function where($clauses)
    {
        if (is_string($clauses) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = clauses');
        
        $whereCollection = WhereClause::parseWhere($clauses);
        
        foreach ($whereCollection as $whereClause)
            $this->addWhereClause($whereClause);
        
        return $this;
    }
    
    /**
     * Executes a SELECT COUNT(*) statement
     *
     * @return int
     */
    public function count()
    {
        return $this->aggregate("COUNT(*)");
    }
    
    /**
     * Retrieves a entity loaded
     * 
     * @param mixed $identity
     * @return EntityBase
     */
    public function single($identity)
    {
        $entity          = $this->defaultEntity();
        /**
         * @var IndexedCollection
         */
        $primaryKeyNames = call_user_func(array(
            get_class($entity),
            'getPrimaryKeyFieldNames'
        ));
        if ($primaryKeyNames->getCount() === 0)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'Entity has no primary key');
        
        if ($primaryKeyNames->getCount() === 1 && is_object($identity) === false)
            return $this->where($primaryKeyNames->getValue(0) . " = '{$identity}'")->selectOne();
        
        if (ObjectIntrospector::isA($identity, 'KeyedCollection') && $identity->getCount() === $primaryKeyNames->getCount())
        {
            foreach ($primaryKeyNames as $priKey)
            {
                if ($identity->keyExists($priKey) && $identity->getValue($priKey) != '')
                {
                    $value = $identity->getValue($priKey);
                    $this->where("$priKey = '$value'");
                }
                else
                    throw new SystemException(SystemException::EX_KEYNOTFOUND, "Primary key mismatch. '{$priKey}' is empty or not found in identity.");
            }
            
            return $this->selectOne(true);
        }
        
        throw new SystemException(SystemException::EX_INVALIDOPERATION, "Identity parameter mismatch.");
    }
    
    /**
     * Retrieves a entity loaded
     * If no record matches, returns the default Entity for the Adapter
     *
     * @param mixed $id
     * @return EntityBase
     */
    public function singleOrDefault($id)
    {
        $entity = $this->single($id);
        
        if (is_null($entity) === false)
            return $entity;
        
        return $this->defaultEntity();
    }
    
    /**
     * Sets number of records to retrieve
     *
     * @param int $records
     * @return DataTableAdapterBase
     */
    public function take($records)
    {
        if (is_int($records) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = $records');
        
        $this->limit = $records;
        
        return $this;
    }
    
    /**
     * Sets number of records to skip
     *
     * @param int $offset
     * @return DataTableAdapterBase
     */
    public function skip($offset)
    {
        if (is_int($offset) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = offset');
        
        $this->offset = $offset;
        
        return $this;
    }
    
    /**
     * Inserts all the entities in database
     *
     * @param IndexedCollection $entities
     * @param bool $useTransaction
     *
     * return bool
     */
    public function insertAll(&$entities, $useTransaction = false)
    {
        if ($useTransaction === true)
            $this->getConnection()->transactionBegin();
        
        try
        {
            foreach ($entities as $entity)
                $this->insert($entity);
            
            if ($useTransaction === true)
                $this->getConnection()->transactionCommit();
        }
        catch (SystemException $ex)
        {
            if ($useTransaction === true)
                $this->getConnection()->transactionRollback();
            return false;
        }
        
        return true;
    }
    /**
     * Inserts a new entity in database.
     *
     * @todo Revise - This won't work for multi-column primary keys 
     * @param Entity $entity
     */
    public function insert(&$entity)
    {
        if (ObjectIntrospector::isExtending($entity, 'EntityBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = entity, type must be EntityBase');
        
        if ($entity->getIsView() === true)
            throw new SystemException(SystemException::EX_READONLYADAPTER);
        
        $this->selectFields->clear();
        
        $this->valueMembers = new KeyedCollection();
        $keys               = $entity->getPrimaryKeyFieldNames();
        
        foreach ($entity->getFields() as $field)
        {
            if ($keys->containsValue($field->getFieldName()) && $field->getValue() == '0')
                continue;
            
            $fieldName  = $this->quoteIdentifier($field->getFieldName());
            $fieldValue = $field->getValue();
            
            if (!is_null($fieldValue) && trim($fieldValue) != '')
            {
                $this->selectFields->addItem($fieldName);
                $this->valueMembers->setValue($fieldName, $fieldValue);
            }
        }
        
        $this->executeQuery($this->getInsertSqlCommand());
        $primaryKeys = $entity->getPrimaryKeyFieldNames();

        if ($primaryKeys->getCount() === 1)
            $entity->setFieldValue($primaryKeys->getValue(0), $this->connection->getLastInsertedId());
            
        $entity->discardChanges();
    }
    
    /**
     * @param Entity $entity
     */
    protected function getDefaultUpdateWhere($entity)
    {
        $clauses = array();
        $keys    = $entity->getPrimaryKeyFieldNames();
        
        foreach ($entity->getFields() as $field)
        {
            $fieldName  = $field->getFieldName();
            $fieldValue = $field->getValue();
            
            if ($this->concurrencyMode == DataConnectionBase::CONCURRENCY_LAST_IN_WINS)
            {
                if ($keys->containsValue($fieldName))
                    $clauses[] = "$fieldName = $fieldValue";
            }
            else
            {
                $clauses[] = "$fieldName = " . $field->getOriginalValue();
            }
            
            if ($keys->containsValue($fieldName))
                continue;
            
            $fieldName = $this->quoteIdentifier($fieldName);
            
            if (ObjectIntrospector::isA($field, 'BinaryEntityField'))
            {
                if (!is_null($fieldValue) && trim($fieldValue) != '')
                {
                    $fieldValue = addslashes($fieldValue);
                    $this->keypairs->setValue($fieldName, $fieldValue);
                }
            }
            else
            {
                if ($field->getHasChanged())
                    $this->keypairs->setValue($fieldName, $fieldValue);
            }
        }
        
        return $clauses;
    }
    
    /**
     * Updates the given entity.
     * 
     * @param Entity $entity
     * @return bool
     */
    public function update($entity)
    {
        if (ObjectIntrospector::isExtending($entity, 'EntityBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = entity, type must be EntityBase');
        
        if ($entity->getIsView() === true)
            throw new SystemException(SystemException::EX_READONLYADAPTER, 'The entity instance represents a read-only view.');
        
        if ($entity->getHasChanged() === false)
            return true;
        
        $this->keypairs = new KeyedCollection();
        $primaryKeys    = $entity->getPrimaryKeyFieldNames()->getArrayReference();
        
        $clauses         = $this->getDefaultUpdateWhere($entity);
        $whereCollection = WhereClause::parseWhere(implode(" AND ", $clauses));
        
        if ($whereCollection->getCount() == 0)
            throw new SystemException(SystemException::EX_READONLYADAPTER, 'You need a least one clasure to update.');
            
        foreach ($whereCollection as $whereClause)
            $this->addWhereClause($whereClause);
        
        $affectedRows = $this->executeQuery($this->getUpdateSqlCommand());
        
        return ($affectedRows > 0);
    }
    
    /**
     * Deletes all entities in the database.
     *
     * @param IndexedCollection $entities
     * @param bool $useTransaction Determines whether the delete commands should be executed within a new transaction
     * @param bool $logicalDelete Determines whether the system should perform a logical delete instead of actually deleting the record.
     * @return bool
     */
    public function deleteAll($entities, $useTransaction = false, $logicalDelete = false)
    {
        if ($useTransaction === true)
            $this->getConnection()->transactionBegin();
        
        foreach ($entities as $entity)
        {
            if ($this->delete($entity, $logicalDelete) === false)
            {
                if ($useTransaction === true)
                    $this->getConnection()->transactionRollback();
                return false;
            }
        }
        
        if ($useTransaction === true)
            $this->getConnection()->transactionCommit();
        
        return true;
    }
    
    /**
     * Deletes an entity in the database.
     *
     * @param Entity $entity
     * @param bool $logicalDelete
     * @return bool
     */
    public function delete($entity, $logicalDelete = false)
    {
        if (ObjectIntrospector::isExtending($entity, 'EntityBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = entity, type must be EntityBase');
        
        if (call_user_func(array(
            get_class($entity),
            'getIsView'
        )) === true)
            throw new SystemException(SystemException::EX_READONLYADAPTER);
        
        $dataSettings = Settings::getValue(Settings::SKEY_DATA);
        
        if ($logicalDelete === true && $dataSettings[Settings::KEY_DATA_LOGICALDELETEFIELD] == '')
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The logical delete field is not set under Settings.');
        
        $clauses = array();
        
        if ($this->concurrencyMode == DataConnectionBase::CONCURRENCY_LAST_IN_WINS)
        {
            foreach (call_user_func(array(
                get_class($entity),
                'getPrimaryKeyFieldNames'
            )) as $primaryKey)
            {
                $fieldValue = $entity->getFieldValue($primaryKey);
                $clauses[]  = "$primaryKey = $fieldValue";
            }
        }
        else
        {
            foreach ($entity->getFields() as $field)
                $clauses[] = $field->getFieldName() . " = " . $field->getOriginalValue();
        }
        
        $whereCollection = WhereClause::parseWhere(implode(" AND ", $clauses));
        
        foreach ($whereCollection as $whereClause)
            $this->addWhereClause($whereClause);
        
        if ($logicalDelete === false)
        {
            $affectedRows = $this->executeQuery($this->getDeleteSqlCommand());
            return ($affectedRows > 0);
        }
        else
        {
            // @todo Logical delete!
            throw new SystemException(SystemException::EX_NOTIMPLENTED, 'The logical delete functionality is not yet implemented.');
        }
    }
    
    /**
     * Executes an user defined SQL statement
     *
     * @param SqlCommandBase $sqlCommand
     * @return mixed
     */
    private function executeQuery($sqlCommand)
    {
        if (ObjectIntrospector::isA($sqlCommand, 'SqlCommandBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = sqlCommand');
        
        return $this->connection->executeNonQuery($sqlCommand);
    }
    
    /**
     * Executes a SQL statement and returns an IndexedCollection
     *
     * @param SqlCommandBase $sqlCommand
     * @return IndexedCollection
     */
    private function fetchRows($sqlCommand)
    {
        if (ObjectIntrospector::isA($sqlCommand, 'SqlCommandBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = sqlCommand');
        
        return $this->connection->executeQuery($sqlCommand);
    }
}

/**
 * This class provides a easy way to add a well-formed WHERE clause.
 *
 * @package WebCore
 * @subpackage Data
 */
class WhereClause extends ObjectBase
{
    const OPERATOR = 'operator';
    const RANGEOPERATOR = 'rangeOperator';
    const IDENTIFIER = 'identifier';
    const AGGREGATOR = 'aggregator';
    
    protected $identifier;
    protected $value;
    protected $operator;
    protected $aggregator;
    protected $table;
    
    /**
     * Sets the Identifier
     *
     * @param string $value
     */
    public function setIdentifier($value)
    {
        $this->identifier = $value;
    }
    
    /**
     * Gets the Identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * Sets the Value
     *
     * @return string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
     * Gets the Value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Sets the Operator
     *
     * @param string $value
     */
    public function setOperator($value)
    {
        $this->operator = $value;
    }

    /**
     * Gets the Operator
     *
     * @param string
     */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
     * Sets the Aggregator
     *
     * @param string $value
     */
    public function setAggregator($value)
    {
        $this->aggregator = $value;
    }
    
    /**
     * Gets the Aggregator
     *
     * @param string
     */
    public function getAggregator()
    {
        return $this->aggregator;
    }
    
    /**
     * Sets the Table
     *
     * @param string $value
     */
    public function setTable($value)
    {
        $this->table = $value;
    }

    /**
     * Gets the Table
     *
     * @param string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Parses WHERE to generate WhereClause instances
     *
     * @param string $clauses
     * @return IndexedCollection
     */
    public static function parseWhere($clauses)
    {
        if (is_string($clauses) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = whereClause");
        
        preg_match_all("/((\'([^\']|(\\\'))+\')|([^ ]+))+/i", $clauses, $array);
        $array = $array[0];
        
        $nextTokenMustBe    = WhereClause::IDENTIFIER;
        $nextOperatorMustBe = WhereClause::OPERATOR;
        $count              = 0;
        $data               = array();
        
        foreach ($array as $token)
        {
            $type = self::getTokenType($token);
            
            if (isset($data[$count]) && isset($data[$count]['firstIdentifier']) && (isset($data[$count]['OPERATOR']) == false || isset($data[$count]['RANGEOPERATOR']) == false))
            {
                if (strstr($data[$count]['firstIdentifier'], "(") == true && strstr($data[$count]['firstIdentifier'], ")") == false)
                {
                    $data[$count]['firstIdentifier'] .= $token;
                    
                    if (strstr($token, ")"))
                        $nextTokenMustBe == WhereClause::OPERATOR;
                    else
                        $nextTokenMustBe == WhereClause::IDENTIFIER;
                    
                    continue;
                }
            }
            
            if (isset($data[$count]) && key_exists(WhereClause::RANGEOPERATOR, $data[$count]))
            {
                if ($type == WhereClause::AGGREGATOR)
                    continue;
                
                if (isset($data[$count][$type]) && $type == WhereClause::IDENTIFIER)
                {
                    if (is_array($data[$count][$type]) == false)
                    {
                        $tempValue             = str_replace("(", "", $data[$count][$type]);
                        $tempValue             = str_replace(",", "", $tempValue);
                        $data[$count][$type]   = array();
                        $data[$count][$type][] = $tempValue;
                    }
                    
                    $token                 = str_replace(")", "", $token);
                    $token                 = str_replace(",", "", $token);
                    $data[$count][$type][] = $token;
                    continue;
                }
            }
            
            if ($type == WhereClause::AGGREGATOR)
                $count++;
            
            if ($type == WhereClause::IDENTIFIER && (count($data) == 0 || count($data[$count]) == 1))
                $data[$count]['firstIdentifier'] = $token;
            else
                $data[$count][$type] = $token;
            
            if ($type != $nextTokenMustBe && ($type != WhereClause::AGGREGATOR && $count == 1))
                throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = whereClause");
            
            if (($type != WhereClause::AGGREGATOR && $count == 1) && $type != WhereClause::IDENTIFIER && $type != $nextOperatorMustBe)
                throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = whereClause");
            
            if ($type == WhereClause::OPERATOR || $type == WhereClause::AGGREGATOR)
            {
                if ($nextTokenMustBe == WhereClause::OPERATOR)
                    $nextOperatorMustBe = WhereClause::AGGREGATOR;
                else
                    $nextOperatorMustBe = WhereClause::OPERATOR;
                
                $nextTokenMustBe = WhereClause::IDENTIFIER;
            }
            elseif ($type == WhereClause::RANGEOPERATOR)
            {
                $nextOperatorMustBe = WhereClause::AGGREGATOR;
            }
            else
            {
                $nextTokenMustBe = $nextOperatorMustBe;
            }
        }
        
        $whereCollection = new IndexedCollection();
        
        foreach ($data as $whereClause)
        {
            $where = new WhereClause();
            
            if (key_exists(WhereClause::AGGREGATOR, $whereClause))
                $where->setAggregator($whereClause[WhereClause::AGGREGATOR]);
            
            $identifier = explode(".", $whereClause['firstIdentifier']);
            
            if (strstr($identifier[0], "("))
            {
                $where->setIdentifier($whereClause['firstIdentifier']);
            }
            elseif (count($identifier) > 1)
            {
                $where->setTable($identifier[0]);
                $where->setIdentifier($identifier[1]);
            }
            else
            {
                $where->setIdentifier($identifier[0]);
            }
            
            if (key_exists(WhereClause::RANGEOPERATOR, $whereClause))
            {
                $where->setOperator($whereClause[WhereClause::RANGEOPERATOR]);
                $where->setValue($whereClause[WhereClause::IDENTIFIER]);
            }
            else
            {
                $where->setOperator($whereClause[WhereClause::OPERATOR]);
                $where->setValue(str_replace("'", "", $whereClause[WhereClause::IDENTIFIER]));
            }
            
            $whereCollection->addItem($where);
        }
        
        return $whereCollection;
    }
    
    /**
     * Gets token type from value
     *
     * @param string $value
     * @return string
     */
    private static function getTokenType($value)
    {
        switch (strtoupper($value))
        {
            case "=":
            case "<>":
            case "!=":
            case "<":
            case ">":
            case ">=":
            case "<=":
            case "LIKE":
            case "IS":
                return WhereClause::OPERATOR;
                break;
            case "BETWEEN":
            case "IN":
                return WhereClause::RANGEOPERATOR;
                break;
            case "AND":
            case "OR":
                return WhereClause::AGGREGATOR;
                break;
            default:
                return WhereClause::IDENTIFIER;
                break;
        }
    }
}
?>