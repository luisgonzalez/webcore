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

require_once("webcore.php");
require_once("webcore.data.php");

/**
 * MySql Database Connection class
 *
 * @package WebCore
 * @subpackage Data
 */
class MySqlConnection extends DataConnectionBase
{
    protected $__internalConnection;
    protected $metadataHelper;
    protected $typeTranslator;
    protected $lastInsertedId;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $server The hostname or IP address of the database server
     * @param string $databaseName The initial database to select when connected.
     * @param string $username The Username used to connect to the database
     * @param string $password The Password used to connect to the database.
     * @param int $port The port to connect, if it's zero connects to default port
     * @param int $concurrencyMode The concurrency mode
     * @param bool $connect Determines whether or not to connect to the database server upon instatiation.
     */
    public function __construct($server, $databaseName, $username, $password, $port = 3306, $concurrencyMode = self::CONCURRENCY_FIRST_IN_WINS, $connect = false)
    {
        parent::__construct($server, $port, $databaseName, $username, $password, $concurrencyMode, $connect);
    }
    
    /**
     * Gets an empty SqlCommandBase-derived command for the connection
     * @return MySqlCommand
     */
    public function createCommand()
    {
        return MySqlCommand::createInstance();
    }
    
    protected function refValues($arr)
    {
        if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach($arr as $key => $value)
            {
                if ($key == 0)
                    $refs[$key] = $arr[$key];
                else
                    $refs[$key] = &$arr[$key];
            }
            
            return $refs;
         }
         return $arr;
    }
    
    /**
     * Prepares the SQL command text and its params and executes it.
     * 
     * @param MySqlCommand $sqlCommand
     * @return mysqli_stmt
     */
    protected function executeSqlCommand($sqlCommand)
    {
        $sqlCommand->preparse();
        $stmt = $this->__internalConnection->prepare($sqlCommand->getCommandText());
        
        if ($stmt === false || is_null($stmt))
            throw new SystemException(SystemException::EX_QUERYEXECUTE, $this->__internalConnection->error);
        
        if ($sqlCommand->getParams()->getCount() > 0)
        {
            $callbackArgs    = array();
            $callbackArgs[0] = '';
            
            foreach ($sqlCommand->getParams() as $dataParam)
            {
                $callbackArgs[0] .= $dataParam->getSqlType();
                
                if (StringHelper::IsUTF8($dataParam->getValue()))
                    $callbackArgs[count($callbackArgs)] = $dataParam->getValue();
                else
                    $callbackArgs[count($callbackArgs)] = utf8_encode($dataParam->getValue());
            }
            
            call_user_func_array(array(
                $stmt,
                'bind_param'
            ), $this->refValues($callbackArgs));
        }
        
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Binds an array to a prepared statement
     *
     * @param mysqli_stmt $stmt
     * @param array $out
     */
    protected function bindStatement(&$stmt, &$out)
    {
        $result = $stmt->result_metadata();
        $fields = array();
        $out    = array();
        
        while ($field = $result->fetch_field())
        {
            $out[$field->name] = "";
            $fields[] =& $out[$field->name];
        }
        
        $return = call_user_func_array(array(
            $stmt,
            'bind_result'
        ), $fields);
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    protected function transactionBeginInternal()
    {
        $sqlCommand = new MySqlCommand();
        $sqlCommand->setCommandText("START TRANSACTION");
        $this->executeNonQuery($sqlCommand);
        return true;
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    protected function transactionCommitInternal()
    {
        $sqlCommand = new MySqlCommand();
        $sqlCommand->setCommandText("COMMIT");
        $this->executeNonQuery($sqlCommand);
        return true;
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    protected function transactionRollbackInternal()
    {
        $sqlCommand = new MySqlCommand();
        $sqlCommand->setCommandText("ROLLBACK");
        $this->executeNonQuery($sqlCommand);
        return true;
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    protected function connectInternal()
    {
        $this->__internalConnection = new mysqli();
        $this->__internalConnection->connect($this->getServer(), $this->getUsername(), $this->getPassword(), $this->getDatabaseName(), $this->getPort());
        if (mysqli_connect_errno())
            throw new SystemException(SystemException::EX_DBCONNECTION, mysqli_connect_error());
        
        $this->__internalConnection->set_charset("utf8");
        
        return true;
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    protected function closeInternal()
    {
        $this->__internalConnection->close();
        return true;
    }
    
    /**
     * @param MySqlCommand $sqlCommand
     * @return IndexedCollection<KeyedCollection>
     */
    protected function executeQueryInternal($sqlCommand)
    {
        $rows = new IndexedCollection();
        
        if ($sqlCommand->getParams()->getCount() > 0)
        {
            $stmt = $this->executeSqlCommand($sqlCommand);
            $stmt->store_result();
            $row = array();
            $this->bindStatement($stmt, $row);
            while ($stmt->fetch())
            {
                $rowKeys       = array_keys($row);
                $rowCollection = new KeyedCollection();
                
                foreach ($rowKeys as $rowKey)
                {
                    if (StringHelper::IsUTF8($row[$rowKey]))
                        $rowCollection->setValue($rowKey, utf8_decode($row[$rowKey]));
                    else
                        $rowCollection->setValue($rowKey, $row[$rowKey]);
                }
                $rows->addItem($rowCollection);
            }
            
            $stmt->close();
        }
        else
        {
            $result = $this->__internalConnection->query($sqlCommand->getCommandText());
            
            if ($result === false)
                throw new SystemException(SystemException::EX_QUERYEXECUTE, $this->__internalConnection->error);
            while ($row = $result->fetch_assoc())
            {
                $rowCollection = new KeyedCollection();
                foreach ($row as $key => $value)
                    $rowCollection->setValue($key, $value);
                $rows->addItem($rowCollection);
            }
            
            if (!is_bool($result)) $result->close();
        }
        
        return $rows;
    }
    
    /**
     * @param MySqlCommand $sqlCommand
     * @return int
     */
    protected function executeNonQueryInternal($sqlCommand)
    {
        if ($sqlCommand->getParams()->getCount() > 0)
        {
            $stmt                 = $this->executeSqlCommand($sqlCommand);
            $this->lastInsertedId = $stmt->insert_id;
            $affectedRows         = $stmt->affected_rows;
            if ($affectedRows == -1)
            {
                if ($stmt->errno == 1062)
                    throw new SystemException(SystemException::EX_DUPLICATEDKEY, $stmt->error);
                else
                    throw new SystemException(SystemException::EX_QUERYEXECUTE, $stmt->error);
            }
            
            return $affectedRows;
        }
        else
        {
            $result = $this->__internalConnection->query($sqlCommand->getCommandText());
            if ($result === false)
                throw new SystemException(SystemException::EX_QUERYEXECUTE, $this->__internalConnection->error);
            $this->lastInsertedId = $this->__internalConnection->insert_id;
            $affectedRows         = $this->__internalConnection->affected_rows;
            
            if (!is_bool($result)) $result->close();
            return $affectedRows;
        }
        
    }
    
    /**
     * @param MySqlCommand $sqlCommand
     * @return mixed
     */
    protected function executeScalarInternal($sqlCommand)
    {
        $rows = new IndexedCollection();
        
        if (count($sqlCommand->getParams()) > 0)
        {
            $stmt = $this->executeSqlCommand($sqlCommand);
            $stmt->store_result();
            $row = array();
            $this->bindStatement($stmt, $row);
            while ($stmt->fetch())
            {
                $rowKeys       = array_keys($row);
                $rowCollection = new KeyedCollection();
                foreach ($rowKeys as $rowKey)
                    $rowCollection->setValue($rowKey, $row[$rowKey]);
                $rows->addItem($rowCollection);
                break;
            }
            
            $stmt->close();
        }
        else
        {
            $result = $this->__internalConnection->query($sqlCommand->getCommandText());
            if ($result === false)
                throw new SystemException(SystemException::EX_QUERYEXECUTE, $this->__internalConnection->error);
            while ($row = $result->fetch_assoc())
            {
                $rowCollection = new KeyedCollection();
                foreach ($row as $key => $value)
                    $rowCollection->setValue($key, $value);
                $rows->addItem($rowCollection);
            }
            
            if (!is_bool($result)) $result->close();
        }
        
        if ($rows->getCount() >= 1)
        {
            $firstRowKeys = $rows->getItem(0)->getKeys();
            if (count($firstRowKeys) >= 1)
            {
                return $rows->getItem(0)->getValue($firstRowKeys[0]);
            }
        }
        
        return null;
    }
    
    /**
     * Gets one of the STATE-prefixed constants defined in this class.
     * @return int
     */
    public function getConnectionState()
    {
        if (is_null($this->__internalConnection))
            return self::STATE_CLOSED;
        if ($this->__internalConnection->ping())
            return DataConnectionBase::STATE_OPEN;
        
        return DataConnectionBase::STATE_CLOSED;
    }
    
    /**
     * Gets the associated database engine metadata helper.
     * @return IMetadataHelper
     */
    public function getMetadataHelper()
    {
        if (is_null($this->metadataHelper))
            $this->metadataHelper = new MySqlMetadataHelper($this);
        return $this->metadataHelper;
    }
    
    /**
     * Returns the associated DataTableAdapter for a given table name and schema
     * 
     * @param string $schema
     * @param string $tableName
     * @return IDataTableAdapter
     */
    public function getDataTableAdapter($schema, $tableName)
    {
        return new MySqlDataTableAdapter($schema, $tableName, $this);
    }
    
    /**
     * Gets the IDataTypeTranslator for the current connection.
     * Type translators provide data type conversion bridges accross PHP, the underlying PHP library, and the back-end database engine.
     * @return IDataTypeTranslator
     */
    public function getTypeTranslator()
    {
        if (is_null($this->typeTranslator))
            $this->typeTranslator = new MySqlDataTypeTranslator();
        return $this->typeTranslator;
    }
    
    /**
     * Determines the user under queries are currently being executed.
     * @return string
     */
    public function getCurrentUser()
    {
        return $this->executeScalar(new MySqlCommand('SELECT CURRENT_USER()'));
    }
    
    /**
     * Returns the autoincrement identifier of the last inserted row.
     * @return mixed
     */
    public function getLastInsertedId()
    {
        return $this->lastInsertedId;
    }
    
    /**
     * Gets the number of total rows of the previous select statement as if it were not executed with a limiting clause.
     * @return int
     */
    public function getFoundRows()
    {
        return $this->executeScalar(new MySqlCommand('SELECT FOUND_ROWS()'));
    }
    
    /**
     * Gets the server version in a PHP-standard integer format.
     * @return int
     */
    public function getServerVersion()
    {
        return $this->executeScalar(new MySqlCommand('SELECT VERSION()'));
    }
    
    /**
     * Gets the UNIX timestamp of the current server date and time.
     * @return long
     */
    public function getServerTimestamp()
    {
        return $this->executeScalar(new MySqlCommand('SELECT UNIX_TIMESTAMP()'));
    }
    
    /**
     * Obtains a globally unique identifier in the correct database engine format
     * 
     * @return mixed
     */
    public function getNewGuid()
    {
        return $this->executeScalar(new MySqlCommand('SELECT UUID()'));
    }
    
    /**
     * Returns a correctly quoted string representing the provided identifier
     * 
     * @return string
     * @param string $identifier
     */
    public function quoteIdentifier($identifier)
    {
        return "`{$identifier}`";
    }
}

/**
 * Translate from PHP data types to SQL data types and vice versa.
 *
 * @package WebCore
 * @subpackage Data
 */
class MySqlDataTypeTranslator extends DataTypeTranslatorBase
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
        if (strpos($sqlDataType, "decimal") !== false)
            return "float";
        if (strpos($sqlDataType, "date") !== false || strpos($sqlDataType, "time") !== false)
            return "DateTime";
        if (strpos($sqlDataType, "tinyint") !== false || strpos($sqlDataType, "bool") !== false)
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
            return 'float';
        if (strpos($phpDataType, "date") !== false || strpos($phpDataType, "time") !== false)
            return 'datetime';
        if (strpos($phpDataType, "bool") !== false)
            return 'tinyint';
        if (strpos($phpDataType, "int") !== false)
            return 'int';
        
        return 'longblob';
    }
    
    /**
     * Translates to one of the TYPE_-prefixed enumerations within the SqlParameter class
     * This is due to the fact that mysqli library uses only 4 sql parameter types
     *
     * @param string $sqlDataType
     * @return string
     */
    public function toSqlParameterDataType($sqlDataType)
    {
        if (is_string($sqlDataType) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = sqlDataType');
        if (strpos($sqlDataType, "decimal") !== false || strpos($sqlDataType, "float") !== false)
            return MySqlParameter::MYSQL_TYPE_DOUBLE;
        if (strpos($sqlDataType, "date") !== false || strpos($sqlDataType, "time") !== false)
            return MySqlParameter::MYSQL_TYPE_VARCHAR;
        if (strpos($sqlDataType, "tinyint") !== false || strpos($sqlDataType, "bool") !== false)
            return MySqlParameter::MYSQL_TYPE_INTEGER;
        if (strpos($sqlDataType, "int") !== false)
            return MySqlParameter::MYSQL_TYPE_INTEGER;
        if (strpos($sqlDataType, 'blob') !== false || strpos($sqlDataType, 'binary') !== false)
            return MySqlParameter::MYSQL_TYPE_BINARY;
        
        return MySqlParameter::MYSQL_TYPE_VARCHAR;
    }
    
    /**
     * Determines if the provided datat type string is a binary type
     * @param string $sqlDataType the sql data type
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
 * MySql helper class to extract metadata from a database
 *
 * @package WebCore
 * @subpackage Data
 */
class MySqlMetadataHelper extends MetadataHelperBase
{
    /**
     * Creates a new MetadataHelper instance
     *
     * @param MySqlConnection $connection
     */
    public function __construct($connection)
    {
        parent::__construct($connection);
    }
    
    /**
     * Returns an indexed collection of schemas for the parent IDataConnection
     * Each element contains a KeyedCollection of a single key 'schema'
     * @return IndexedCollection<KeyedCollection>
     */
    public function getSchemas()
    {
        $sqlCommand = new MySqlCommand();
        $strSQL     = "SELECT DISTINCT TABLE_SCHEMA as `schema` FROM information_schema.tables";
        $sqlCommand->setCommandText($strSQL);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Returns an IndexedCollection of table metadata in the given schema.
     * If schema is left empty, all the schemas for the current database are retrieved.
     * The structure of eacch element should be KeyedCollection(['schema'] => $strSchema, ['tableName'] => $strTableName, ['isView'] => $boolIsView)
     * @param string $schema
     * @return IndexedCollection
     */
    public function getTables($schema = '')
    {
        $sqlCommand = new MySqlCommand();
        $strSQL     = "SELECT TABLE_NAME as `tableName`,
                    CASE TABLE_TYPE WHEN 'VIEW' THEN 'true'
                    ELSE 'false' END as `isView`,
                    TABLE_SCHEMA as `schema`
                    FROM information_schema.tables
                    WHERE TABLE_SCHEMA = ?";
        $sqlCommand->setCommandText($strSQL);
        if ($schema == '')
            $schema = $this->getConnection()->getDatabaseName();
        $sqlCommand->addParam("schema", MySqlParameter::MYSQL_TYPE_VARCHAR, $schema);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Returns an IndexedCollection of SP metadata in the given schema.
     * @todo Check
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
     * @param string $schema The name of the schema the table belongs to.
     * @return IndexedCollection
     */
    public function getPrimaryKeyColumnNames($schema, $tableName)
    {
        $sqlCommand = new MySqlCommand();
        $strSQL     = "SELECT COLUMN_NAME as columnName FROM information_schema.columns
                    WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? AND COLUMN_KEY = 'PRI'";
        $sqlCommand->setCommandText($strSQL);
        $sqlCommand->addParam("table", MySqlParameter::MYSQL_TYPE_VARCHAR, $tableName);
        $sqlCommand->addParam("schema", MySqlParameter::MYSQL_TYPE_VARCHAR, $schema);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Return a collection of column metadata for the given schema and table name.
     * The structure of eacch element should be a KeyedCollection with the following keys:
     * columnName, defaultValue, dataType, charLength, isNullable, isCaluculated, databaseType
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName The name of the table to retrieve columns for.
     * @return IndexedCollection
     */
    public function getColumns($schema, $tableName)
    {
        $sqlCommand = new MySqlCommand();
        $strSQL     = "SELECT
                    COLUMN_NAME as columnName,
                    COLUMN_DEFAULT as defaultValue,
                    DATA_TYPE AS dataType,
                    IFNULL(CHARACTER_MAXIMUM_LENGTH, 0) AS charLength,
                    CASE IS_NULLABLE WHEN 'NO' THEN 'false' ELSE 'true' END AS isNullable,
                    CASE EXTRA WHEN 'auto_increment' THEN 'true' ELSE 'false' END AS isCalculated,
                    COLUMN_TYPE AS databaseType
                FROM information_schema.columns
                WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?";
        
        $sqlCommand->setCommandText($strSQL);
        $sqlCommand->addParam("table", MySqlParameter::MYSQL_TYPE_VARCHAR, $tableName);
        $sqlCommand->addParam("schema", MySqlParameter::MYSQL_TYPE_VARCHAR, $schema);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Return a collection of column metadata for the given SP name.
     * @todo Check
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
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName
     * @return IndexedCollection
     */
    public function getDataRelations($schema, $tableName)
    {
        $sqlCommand = new MySqlCommand();
        $strSQL     = "SELECT REFERENCED_TABLE_NAME as foreignTableName, REFERENCED_COLUMN_NAME as foreignColumnName,
                        COLUMN_NAME as localColumnName
                        FROM information_schema.key_column_usage
                        WHERE TABLE_NAME = ? AND CONSTRAINT_NAME != 'PRIMARY' AND REFERENCED_TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL";
        
        $sqlCommand->setCommandText($strSQL);
        $sqlCommand->addParam("table", MySqlParameter::MYSQL_TYPE_VARCHAR, $tableName);
        $sqlCommand->addParam("schema", MySqlParameter::MYSQL_TYPE_VARCHAR, $schema);
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
        $sqlCommand = new MySqlCommand();
        $strSQL     = "SELECT TABLE_NAME as foreignTableName, REFERENCED_COLUMN_NAME as localColumnName,
                    COLUMN_NAME as foreignColumnName
                    FROM information_schema.key_column_usage
                    WHERE REFERENCED_TABLE_NAME = ? AND REFERENCED_TABLE_SCHEMA = ?";
        $sqlCommand->setCommandText($strSQL);
        $sqlCommand->addParam("Table", MySqlParameter::MYSQL_TYPE_VARCHAR, $tableName);
        $sqlCommand->addParam("Schema", MySqlParameter::MYSQL_TYPE_VARCHAR, $schema);
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Gets the string representing the SQL statements used to recreate the table schema.
     * @param string $tableName
     * @param string $schema The name of the schema the table belongs to.
     * @return string
     */
    public function getTableSchemaScript($schema, $tableName)
    {
        $tables = $this->getTables($schema);
        $rows   = new IndexedCollection();
        $isView = false;
        foreach ($tables as $table)
        {
            if ($table->getValue('isView') == 'true' && $table->getValue('tableName') == $tableName)
            {
                $rows   = $this->getConnection()->executeQuery(new MySqlCommand("SHOW CREATE VIEW $tableName"));
                $isView = true;
            }
            
            if ($isView) break;
        }
        if ($isView !== true)
            $rows = $this->getConnection()->executeQuery(new MySqlCommand("SHOW CREATE TABLE $tableName"));
        
        if ($rows->isEmpty()) return '';
        
        $keys = $rows->getValue(0)->getKeys();
        return $rows->getValue(0)->getValue($keys[1]);
    }
    
    /**
     * Gets the string representing the necssary SQL statements used to recreate the entire database schema.
     * @return string
     */
    public function getDatabaseSchemaScript()
    {
        $rows = new IndexedCollection();
        $rows = $this->getConnection()->executeQuery(new MySqlCommand("SHOW CREATE DATABASE " . $this->getConnection()->getDatabaseName()));
        if ($rows->isEmpty()) return '';
        $keys = $rows->getValue(0)->getKeys();
        return $rows->getValue(0)->getValue($keys[1]);
    }
}

/**
 * Represents a MySQL Parameter
 *
 * @package WebCore
 * @subpackage Data
 */
class MySqlParameter extends SqlParameterBase
{
    const MYSQL_TYPE_VARCHAR = 's';
    const MYSQL_TYPE_INTEGER = 'i';
    const MYSQL_TYPE_DOUBLE = 'd';
    const MYSQL_TYPE_BINARY = 'b';
    
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
     * @return MySqlParameter
     */
    public static function createInstance()
    {
        return new MySqlParameter();
    }
}

/**
 * Represents a MySQL Command
 *
 * @package WebCore
 * @subpackage Data
 */
class MySqlCommand extends SqlCommandBase
{
    /**
     * Preparse a SQL Query for preventing NULL parameter values from breaking the command text.
     * For example, when a parameter has no value ($param->Value === null)
     * it replaces the corresponding substitution '?' with the literal NULL while at the same time,
     * removes the parameter in question.
     */
    public function preparse()
    {
        $pos = 0;
        $i   = 0;
        
        if (is_null($this->params) === false)
        {
            foreach ($this->params as $key => $param)
            {
                $pos = strpos($this->commandText, '?', $pos + 1);
                
                if ($pos === false) break;
                if (!is_null($param->getValue())) continue;
                
                $this->commandText = substr_replace($this->commandText, 'NULL', $pos, 1);
                $this->params->removeAt($i);
                $i++;
            }
        }
    }
    
    /**
     * Adds a param to SQL Command
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $type One of the '*_TYPE_' - prefixed constants defined by this class
     * @param string $value The value of the parameter
     */
    public function addParam($name, $type, $value = null)
    {
        $this->params->addItem(new MySqlParameter($name, $type, $value));
    }
    
    /**
     * Adds a quoted parameter to SQL Command (strings, text, floats and dates are all quoted parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    public function addQuotedParam($name, $value)
    {
        $this->params->addItem(new MsSqlParameter($name, MySqlParameter::MYSQL_TYPE_VARCHAR, $value));
    }

    /**
     * Adds an unquoted parameter to SQL Command (integers, booleans, bitfields are examples of unquoted parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    public function addUnquotedParam($name, $value)
    {
        $this->params->addItem(new MsSqlParameter($name, MySqlParameter::MYSQL_TYPE_INTEGER, $value));
    }
    
    /**
     * Adds an unquoted parameter to SQL Command (blob, image, binary are examples of binary parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    public function addBinaryParam($name, $value)
    {
        $this->params->addItem(new MsSqlParameter($name, MySqlParameter::MYSQL_TYPE_BINARY, $value));
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return MySqlCommand
     */
    public static function createInstance()
    {
        return new MySqlCommand();
    }
}

/**
 * DataTable Adapter with MySQL statments
 *
 * @package WebCore
 * @subpackage Data
 */
class MySqlDataTableAdapter extends DataTableAdapterBase
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
        
        $this->sqlCommand = new MySqlCommand();
    }
    
    /**
     * Gets a SELECT SqlCommand for executing against the database.
     * @return SqlCommand
     */
    public function getSelectSqlCommand()
    {
        $sqlCommand = parent::getSelectSqlCommand();
        $strSQL     = $sqlCommand->getCommandText();
        
        if (!is_null($this->limit))
        {
            $strSQL .= " LIMIT " . $this->limit;
            
            if (!is_null($this->offset))
                $strSQL .= " OFFSET " . $this->offset;
        }
        
        $sqlCommand->setCommandText($strSQL);
        
        return $sqlCommand;
    }
}
?>