<?php
/**
 * @package WebCore
 * @subpackage Data
 * @version 1.0
 *
 * @todo Stored Procs!
 * 
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @author Mario Di Vece <mario@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.data.php";

/**
 * MsSql Database Connection class
 *
 * @package WebCore
 * @subpackage Data
 */
class MsSqlConnection extends DataConnectionBase
{
    protected $__internalConnection;
    protected $metadataHelper;
    protected $typeTranslator;
    protected $__lastInsertedId;
    
    /**
     * Creates a new Connection instance
     *
     * @param string $host The hostname or IP address of the database server
     * @param string $user The Username used to connect to the database
     * @param string $password The Password used to connect to the database.
     * @param string $database The initial database to select when connected.
     * @param int $port The port to connect, if it's zero connects to default port
     * @param bool $concurrency The concurrency mode
     * @param bool $connect Determines whether or not to connect to the database server upon instatiation.
     */
    public function __construct($server, $databaseName, $username, $password, $port = 1433, $concurrencyMode = self::CONCURRENCY_FIRST_IN_WINS, $connect = false)
    {
        parent::__construct($server, $port, $databaseName, $username, $password, $concurrencyMode, $connect);
    }
    
    /**
     * Gets an empty SqlCommandBase-derived command for the connection
     * @return MsSqlCommand
     */
    public function createCommand()
    {
        return MsSqlCommand::createInstance();
    }
    
    /**
     * Connects to MsSql Database
     *
     */
    protected function connectInternal()
    {
        $connectionInfo = array(
            "UID" => $this->getUsername(),
            "PWD" => $this->getPassword(),
            "Database" => $this->getDatabaseName()
            //"MultipleActiveResultSets" => '0' // @todo This is needed for Azure SQL -- PLEASE DO NOT REMOVE OR LOAD SETTING FROM app.settings!
        );
            
        $this->__internalConnection = sqlsrv_connect($this->getServer(), $connectionInfo);
        
        if (!$this->__internalConnection)
        {
            $errors = sqlsrv_errors();
            throw new SystemException(SystemException::EX_QUERYEXECUTE, $errors[0]['message']);
        }
    }
    
    /**
     * Closes MsSql Database
     *
     */
    protected function closeInternal()
    {
        sqlsrv_close($this->__internalConnection);
    }
    
    /**
     * Executes a scalar query
     * 
     * @param SqlCommandBase $sqlCommand
     * 
     * @return mixed
     */
    protected function executeScalarInternal($sqlCommand)
    {
        $results = $this->executeQueryInternal($sqlCommand);
        if ($results->getCount() >= 1)
        {
            $keys = $results->getItem(0)->getKeys();
            if (count($keys) >= 1)
            {
                return $results->getItem(0)->getValue($keys[0]);
            }
        }
        
        return null;
    }
    
    /**
     * Executes a prepared statement at MsSql
     *
     * @param MsSqlCommand $sqlCommand
     * @return mixed
     */
    protected function executeNonQueryInternal($sqlCommand)
    {
        $params = array();
            
        foreach($sqlCommand->getParams() as $param)
            $params[] = $param->getValue();
        
        if (StringHelper::beginsWith($sqlCommand->getCommandText(), "INSERT "))
            $sqlCommand->setCommandText($sqlCommand->getCommandText() . ' SELECT SCOPE_IDENTITY() AS lastInsertedId');
        
        $result = sqlsrv_query($this->__internalConnection, $sqlCommand->getCommandText(), $params);
        
        if ($result === false)
        {
            $errors = sqlsrv_errors();
            throw new SystemException(SystemException::EX_QUERYEXECUTE, $errors[0]['message']);
        }
        
        if (StringHelper::strContains($sqlCommand->getCommandText(), "INSERT "))
        {
            sqlsrv_next_result($result);
            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            $this->__lastInsertedId = $row['lastInsertedId'];
        }
        
        return $result;
    }
    
    /**
     * Returns a collection from a SQL command
     *
     * @param SqlCommand $sqlCommand
     * @return IndexedCollection
     */
    public function executeQueryInternal($sqlCommand)
    {
        $rows   = new IndexedCollection();
        $result = $this->executeNonQueryInternal($sqlCommand);
        
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))
        {
            $rowKeys       = array_keys($row);
            $rowCollection = new KeyedCollection();
            foreach ($rowKeys as $rowKey)
            {
                if (get_class($row[$rowKey]) === 'DateTime')
                {
                    $dt = $row[$rowKey];
                    $rowCollection->setValue($rowKey, $dt->format('Y-m-d H:i:s'));
                }
                else
                {
                    $rowCollection->setValue($rowKey, $row[$rowKey]);
                }
            }
            $rows->addItem($rowCollection);
        }
        
        sqlsrv_free_stmt($result);
         
        return $rows;
    }
    
    /**
     * Determines the user under queries are currently being executed.
     *
     * @return string
     */
    public function getCurrentUser()
    {
        return $this->executeScalar(new MsSqlCommand('SELECT USER'));
    }
    
    /**
     * Returns last inserted id
     *
     * @return int
     */
    public function getLastInsertedId()
    {
        return $this->__lastInsertedId;
    }
    
    /**
     * Gets the number of total rows of the previous select statement as if it were not executed with a limiting clause.
     * @return int
     */
    public function getFoundRows()
    {
        return $this->executeScalar(new MsSqlCommand('SELECT @@ROWCOUNT'));
    }
    
    /**
     * Gets the server version in a PHP-standard integer format.
     * @return int
     */
    public function getServerVersion()
    {
        return $this->executeScalar(new MsSqlCommand('SELECT @@VERSION'));
    }
    
    /**
     * Gets the UNIX timestamp of the current server date and time.
     * @return long
     */
    public function getServerTimestamp()
    {
        return $this->executeScalar(new MsSqlCommand("SELECT DATEDIFF(second,{d '1970-01-01'}, CURRENT_TIMESTAMP)"));
    }
    
    /**
     * Obtains a globally unique identifier in the correct database engine format
     * @return mixed
     */
    public function getNewGuid()
    { 
        return $this->executeScalar(new MsSqlCommand("SELECT NEWID()"));
    }
    
    /**
     * Returns metadata helper for MsSql
     *
     * @return MsSqlMetadataHelper
     */
    public function getMetadataHelper()
    {
        if (is_null($this->metadataHelper))
            $this->metadataHelper = new MsSqlMetadataHelper($this);
        
        return $this->metadataHelper;
    }
    
    /**
     * Returns the associated DataTableAdapter for a given table name and schema
     * 
     * @param string $schema
     * @param string $tableName
     * 
     * @return MsSqlDataTableAdapter
     */
    public function getDataTableAdapter($schema, $tableName)
    {
        return new MsSqlDataTableAdapter($schema, $tableName, $this);
    }
    
    /**
     * Returns current connection state
     *
     * @return int
     */
    public function getConnectionState()
    {
        /* @todo  Implement connection state management */
        if (is_null($this->__internalConnection))
            return self::STATE_CLOSED;
        
        return DataConnectionBase::STATE_OPEN;
    }
    
    /**
     * Begins transaction
     *
     */
    protected function transactionBeginInternal()
    {
        return sqlsrv_begin_transaction($this->__internalConnection);
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    protected function transactionCommitInternal()
    {
        return sqlsrv_commit($this->__internalConnection);
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    protected function transactionRollbackInternal()
    {
        return sqlsrv_rollback($this->__internalConnection);
    }
    
    /**
     * Gets the IDataTypeTranslator for the current connection
     *
     * @return IDataTypeTranslator
     */
    public function getTypeTranslator()
    {
        return new MsSqlDataTypeTranslator();
    }
    
    /**
     * Gets a formally-formatted string identifier.
     * @return string
     * @param string $identifier
     */
    public function quoteIdentifier($identifier)
    {
        return "[{$identifier}]";
    }
}

/**
 * Translate from PHP data types to SQL data types and vice versa.
 *
 * @package WebCore
 * @subpackage Data
 */
class MsSqlDataTypeTranslator extends DataTypeTranslatorBase
{
    /**
     * Translates to PHP data type
     *
     * @param string $sqlDataType
     * @return string
     */
    public function toPhpDataType($sqlDataType)
    {
        if (is_string($sqlDataType) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = sqlDataType');
        if (strpos($sqlDataType, "decimal") !== false || strpos($sqlDataType, "money") !== false)
            return "float";
        if (strpos($sqlDataType, "date") !== false || strpos($sqlDataType, "time") !== false)
            return "DateTime";
        if (strpos($sqlDataType, "int(1)") !== false || strpos($sqlDataType, "bool") !== false)
            return "bool";
        if (strpos($sqlDataType, "int") !== false)
            return "int";
        return "string";
    }
    
    /**
     * Translates to SQL data type
     *
     * @param string $dataType
     * @return string
     */
    public function toSqlDataType($phpDataType)
    {
        if (is_string($phpDataType) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = $phpDataType');
        
        $phpDataType = strtolower($phpDataType);
        if (strpos($phpDataType, "string") !== false || strpos($phpDataType, "str") !== false)
            return 'varchar';
        if (strpos($phpDataType, "float") !== false || strpos($phpDataType, "double") !== false)
            return 'decimal';
        if (strpos($phpDataType, "date") !== false || strpos($phpDataType, "time") !== false)
            return 'datetime';
        if (strpos($phpDataType, "bool") !== false)
            return 'bit';
        if (strpos($phpDataType, "int") !== false)
            return 'int';
        return 'image';
    }
    
    /**
     * Translates to one of the TYPE_-prefixed enumerations withing the SqlParameter class
     * @todo The SqlParameter:: does not exist. This translator is useless. SqlParameterBase should define Quoted, Unquoted, and Binary to enable provider-agnostic data access.
     * @param string $sqlDataType
     * @return string
     */
    public function toSqlParameterDataType($sqlDataType)
    {
        if (is_string($sqlDataType) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = sqlDataType');
        if (strpos($sqlDataType, "decimal") !== false || strpos($sqlDataType, "money") !== false)
            return MsSqlParameter::TYPE_QUOTED;
        if (strpos($sqlDataType, "date") !== false || strpos($sqlDataType, "time") !== false)
            return MsSqlParameter::TYPE_QUOTED;
        if (strpos($sqlDataType, "bit") !== false || strpos($sqlDataType, "bool") !== false)
            return MsSqlParameter::TYPE_UNQUOTED;
        if (strpos($sqlDataType, "int") !== false)
            return MsSqlParameter::TYPE_UNQUOTED;
        if (strpos($sqlDataType, 'image') !== false || strpos($sqlDataType, 'binary') !== false)
            return MsSqlParameter::TYPE_BINARY;
        return MsSqlParameter::TYPE_QUOTED;
    }
    
    /**
     * Determines if the provided datat type string is a binary type
     * @param $sqlDataType the sql data type
     * @return bool
     */
    public function isBinarySqlType($sqlDataType)
    {
        if (strpos($sqlDataType, 'blob') !== false || strpos($sqlDataType, 'binary') !== false)
            return true;
        return false;
    }
}

/**
 * MsSQL helper class to extract metadata from a database
 *
 * @package WebCore
 * @subpackage Data
 */
class MsSqlMetadataHelper extends MetadataHelperBase
{
    /**
     * Returns an indexed collection of schemas for the parent IDataConnection
     * Each element contains a KeyedCollection of a single key 'schema'
     * @return IndexedCollection<KeyedCollection>
     */
    public function getSchemas()
    {
        $sqlCommand = new MySqlCommand();
        $strSQL     = "SELECT DISTINCT TABLE_SCHEMA as [schema] FROM information_schema.tables";
        $sqlCommand->setCommandText($strSQL);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Returns an IndexedCollection of table metadata in the given schema.
     * If schema is left empty, all the schemas for the current database are retrieved.
     * The structure of each element should be KeyedCollection(['schema'] => $strSchema, ['tableName'] => $strTableName, ['isView'] => $boolIsView)
     * @param string $schema
     * @return IndexedCollection<KeyedCollection>
     */
    public function getTables($schema = '')
    {
        if ($schema == '')
            $schema = $this->getConnection()->getDatabaseName();
        
        $sqlCommand = new MsSqlCommand();
        $strSQL     = "SELECT TABLE_NAME as [tableName],
                    CASE TABLE_TYPE WHEN 'VIEW' THEN 'true'
                    ELSE 'false' END as [isView],
                    TABLE_SCHEMA as [schema]
                    FROM information_schema.TABLES
                    WHERE TABLE_CATALOG = '$schema'
                    ORDER BY TABLE_NAME";
        
        $sqlCommand->setCommandText($strSQL);
        
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Returns an IndexedCollection of SP metadata in the given schema.
     * @return IndexedCollection<KeyedCollection>
     */
    public function getStoredProcedures()
    {
        $sqlCommand = new MsSqlCommand();
        $strSQL     = "SELECT name from sysobjects where type = 'P' and category = 0";
        
        $sqlCommand->setCommandText($strSQL);
        
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Gets a collection of column names that make up the given table's primary key.
     * Usually, this method will return a single item in the collection.
     * @param string $tableName The name of the table to retrieve the primary key from
     * @param string $schema The name of the schema where the table is located.
     * @return IndexedCollection
     * @todo This function really sucks, many entities are generated without PK info and single function blows
     */
    public function getPrimaryKeyColumnNames($schema, $tableName)
    {
        $sqlCommand = new MsSqlCommand();
        $strSQL     = "SELECT CC.COLUMN_NAME  AS columnName FROM INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE CC
                        INNER JOIN information_schema.TABLE_CONSTRAINTS C ON CC.CONSTRAINT_NAME = C.CONSTRAINT_NAME
                        WHERE C.CONSTRAINT_TYPE = 'PRIMARY KEY' AND C.TABLE_NAME = '$tableName'
                        AND C.TABLE_SCHEMA = '$schema'";
        
        $sqlCommand->setCommandText($strSQL);
        
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Return a collection of column metadata for the given schema and table name.
     * The structure of eacch element should be a KeyedCollection with the following keys:
     * columnName, defaultValue, dataType, charLength, isNullable, isCalculated, databaseType
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName The name of the table to retrieve columns for.
     * @return IndexedCollection
     */
    public function getColumns($schema, $tableName)
    {
        $sqlCommand = new MsSqlCommand();
        
        $strSQL = "SELECT
                    COLUMN_NAME as columnName,
                    COLUMN_DEFAULT as defaultValue,
                    DATA_TYPE AS dataType,
                    ISNULL(CHARACTER_MAXIMUM_LENGTH, 0) AS charLength,
                    CASE IS_NULLABLE WHEN 'NO' THEN 'false' ELSE 'true' END AS isNullable,
                    'false' AS isCalculated,
                    DATA_TYPE AS databaseType
                FROM information_schema.columns
                WHERE TABLE_NAME = '$tableName' AND TABLE_SCHEMA = '$schema'";
        
        $sqlCommand->setCommandText($strSQL);
        $results = $this->getConnection()->executeQuery($sqlCommand);
        foreach ($results as $result)
        {
            $defaultValue = str_replace('\'', "\\'", $result->getValue('defaultValue'));
            if (StringHelper::beginsWith($defaultValue, '(('))
            {
                $result->setValue('defaultValue', substr($defaultValue, 2, strlen($defaultValue) - 4));
            }
            elseif (StringHelper::beginsWith($defaultValue, '(N\''))
            {
                $result->setValue('defaultValue', substr($defaultValue, 3, strlen($defaultValue) - 5));
            }
            else
            {
                $result->setValue('defaultValue', '');
            }
        }
        
        return $results;
    }
    
    /**
     * Return a collection of column metadata for the given SP name.
     * @return IndexedCollection
     */
    public function getStoredProcedureColumns($spName)
    {
        $sqlCommand = new MsSqlCommand();
        
        $strSQL = "SELECT syscolumns.name, systypes.name
            FROM syscolumns  INNER JOIN systypes ON systypes.xtype = syscolumns.xtype
            WHERE syscolumns.id = (SELECT id from sysobjects where type = 'P'
            and category = 0 AND name = '$spName')";
        
        $sqlCommand->setCommandText($strSQL);
        
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Retrieves metadata about data relations on which the given table name and schema depends.
     * The structure of each element should be a KeyedCollection with the following keys:
     * foreignTableName, foreignColumnName, localColumnName
     * @todo Currently, this only works with a single schema.
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName
     * @return IndexedCollection
     */
    public function getDataRelations($schema, $tableName)
    {
        $sqlCommand = new MsSqlCommand();
        $strSQL     = "
                    SELECT 
                        localTableName  = FK.TABLE_NAME, 
                        localColumnName = CU.COLUMN_NAME, 
                        foreignTableName  = PK.TABLE_NAME, 
                        foreignColumnName = PT.COLUMN_NAME, 
                        constraintName = C.CONSTRAINT_NAME 
                    FROM 
                        INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS C 
                        INNER JOIN 
                        INFORMATION_SCHEMA.TABLE_CONSTRAINTS FK 
                            ON C.CONSTRAINT_NAME = FK.CONSTRAINT_NAME 
                        INNER JOIN 
                        INFORMATION_SCHEMA.TABLE_CONSTRAINTS PK 
                            ON C.UNIQUE_CONSTRAINT_NAME = PK.CONSTRAINT_NAME 
                        INNER JOIN 
                        INFORMATION_SCHEMA.KEY_COLUMN_USAGE CU 
                            ON C.CONSTRAINT_NAME = CU.CONSTRAINT_NAME 
                        INNER JOIN 
                        ( 
                            SELECT 
                                i1.TABLE_NAME, i2.COLUMN_NAME 
                            FROM 
                                INFORMATION_SCHEMA.TABLE_CONSTRAINTS i1 
                                INNER JOIN 
                                INFORMATION_SCHEMA.KEY_COLUMN_USAGE i2 
                                ON i1.CONSTRAINT_NAME = i2.CONSTRAINT_NAME 
                                WHERE i1.CONSTRAINT_TYPE = 'PRIMARY KEY' 
                        ) PT 
                        ON PT.TABLE_NAME = PK.TABLE_NAME WHERE FK.TABLE_NAME = '{$tableName}'";
        
        $sqlCommand->setCommandText($strSQL);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Retrieves metadata about data relations that depend on the given table name and schema.
     * The structure of each element should be a KeyedCollection with the following keys:
     * foreignTableName, foreignColumnName, localColumnName
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName
     * @return IndexedCollection
     */
    public function getDependentDataRelations($schema, $tableName)
    {
        $sqlCommand = new MsSqlCommand();
        $strSQL     = "
                        SELECT 
                            foreignTableName  = FK.TABLE_NAME, 
                            foreignColumnName = CU.COLUMN_NAME, 
                            localTableName  = PK.TABLE_NAME, 
                            localColumnName = PT.COLUMN_NAME, 
                            constraintName = C.CONSTRAINT_NAME 
                        FROM 
                            INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS C 
                            INNER JOIN 
                            INFORMATION_SCHEMA.TABLE_CONSTRAINTS FK 
                                ON C.CONSTRAINT_NAME = FK.CONSTRAINT_NAME 
                            INNER JOIN 
                            INFORMATION_SCHEMA.TABLE_CONSTRAINTS PK 
                                ON C.UNIQUE_CONSTRAINT_NAME = PK.CONSTRAINT_NAME 
                            INNER JOIN 
                            INFORMATION_SCHEMA.KEY_COLUMN_USAGE CU 
                                ON C.CONSTRAINT_NAME = CU.CONSTRAINT_NAME 
                            INNER JOIN 
                            ( 
                                SELECT 
                                    i1.TABLE_NAME, i2.COLUMN_NAME 
                                FROM 
                                    INFORMATION_SCHEMA.TABLE_CONSTRAINTS i1 
                                    INNER JOIN 
                                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE i2 
                                    ON i1.CONSTRAINT_NAME = i2.CONSTRAINT_NAME 
                                    WHERE i1.CONSTRAINT_TYPE = 'PRIMARY KEY' 
                            ) PT 
                            ON PT.TABLE_NAME = PK.TABLE_NAME WHERE PK.TABLE_NAME = '{$tableName}'";
    
        $sqlCommand->setCommandText($strSQL);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Gets the string representing the SQL statements used to recreate the table schema.
     *
     * @todo Unknown method to get DDL in MsSQL
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName
     * @return string
     */
    public function getTableSchemaScript($schema, $tableName)
    {
        return '';
    }
    
    /**
     * Gets DDL script from database
     * 
     * @todo Unknown method to get DDL in MsSQL
     * @return string
     */
    public function getDatabaseSchemaScript()
    {
        return '';
    }
}

/**
 * Represents a MsSQL Parameter
 *
 * @todo Add suppot for MSSQL constants
 * @package WebCore
 * @subpackage Data
 */
class MsSqlParameter extends SqlParameterBase
{
    const TYPE_QUOTED = 'quoted';
    const TYPE_UNQUOTED = 'unquoted';
    const TYPE_BINARY = 'binary';
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name The name of the parameter
     * @param string $type One of the '*_TYPE_' - prefixed constants defined by this class
     * @param string $value The value of the parameter
     */
    public function __construct($name, $type, $value = NULL)
    {
        parent::__construct($name, $type, $value);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return MsSqlParameter
     */
    public static function createInstance()
    {
        return new MsSqlParameter();
    }
}

/**
 * Represents a MsSQL Command
 *
 * @package WebCore
 * @subpackage Data
 */
class MsSqlCommand extends SqlCommandBase
{
    /**
     * Adds a param to SQL Command
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $type One of the '*_TYPE_' - prefixed constants defined by this class
     * @param string $value The value of the parameter
     */
    public function addParam($name, $type, $value = null)
    {
        $this->params->addItem(new MsSqlParameter($name, $type, $value));
    }
    
    /**
     * Adds a quoted parameter to SQL Command (strings, text, floats and dates are all quoted parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    public function addQuotedParam($name, $value)
    {
        $this->params->addItem(new MsSqlParameter($name, MsSqlParameter::TYPE_QUOTED, $value));
    }

    /**
     * Adds an unquoted parameter to SQL Command (integers, booleans, bitfields are examples of unquoted parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    public function addUnquotedParam($name, $value)
    {
        $this->params->addItem(new MsSqlParameter($name, MsSqlParameter::TYPE_UNQUOTED, $value));
    }
    
    /**
     * Adds an unquoted parameter to SQL Command (blob, image, binary are examples of binary parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    public function addBinaryParam($name, $value)
    {
        $this->params->addItem(new MsSqlParameter($name, MsSqlParameter::TYPE_BINARY, $value));
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return MsSqlCommand
     */
    public static function createInstance()
    {
        return new MsSqlCommand();
    }
}

/**
 * DataTable Adapter with MsSQL statments
 *
 * @package WebCore
 * @subpackage Data
 */
class MsSqlDataTableAdapter extends DataTableAdapterBase
{
    /**
     * Creates a DataTableAdapter for the given table name and schema.
     * 
     * @param string $schema
     * @param string $tableName
     * @param IDataConnection $connection
     */
    public function __construct($schema, $tableName, $connection)
    {
        parent::__construct($schema, $tableName, $connection);
        
        $this->sqlCommand = new MsSqlCommand();
    }
    
    /**
     * Returns a custom SELECT for MsSQL engine
     *
     * @return SqlCommand
     */
    public function getSelectSqlCommand()
    {
        $sqlCommand = parent::getSelectSqlCommand();
        $strSQL     = $sqlCommand->getCommandText();
        
        // Aggregate detection
        $isAggregate = false;
        if ($this->offset === 0 && $this->limit === 1)
        {
            $needle = 'SELECT COUNT(*) FROM';
            if (StringHelper::beginsWith($strSQL, $needle))
                $isAggregate = true;
        }
        
        if (!$isAggregate)
        {
            if ($this->limit > 1)
            {
                if ($this->offset > 0)
                {
                    $innerSQL = StringHelper::replaceStart($strSQL, "SELECT", '');
                    $orderBySQL = '1';
                    $orderByStrPos = stripos($innerSQL, 'ORDER BY');
                    if ($orderByStrPos !== false)
                    {
                        $orderBySQL = substr($innerSQL, $orderByStrPos + strlen('ORDER BY'));
                        $innerSQL = substr($innerSQL, 0, $orderByStrPos);
                    }
                    
                    $rowNumSqlStart = 'SELECT * FROM (SELECT ROW_NUMBER() OVER (ORDER BY ' . $orderBySQL . ') AS [__ROWNUMBER__],';
                    $rowNumSqlEnd = ') AS [__PAGEDRECORDS__] WHERE [__ROWNUMBER__] BETWEEN ' . ($this->offset + 1) . ' AND ' . ($this->offset + $this->limit);
                    $strSQL = $rowNumSqlStart . $innerSQL . $rowNumSqlEnd;
                }
                else
                {
                    $strSQL = StringHelper::replaceStart($strSQL, "SELECT", "SELECT TOP " . $this->limit . " ");
                }
            }
        }

        $sqlCommand->setCommandText($strSQL);
        
        return $sqlCommand;
    }
}
?>