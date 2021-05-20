<?php
/**
 * @package WebCore
 * @subpackage Data
 * @version 1.0
 * 
 * @todo Add logical deletes/selects
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.collections.php";
require_once "webcore.application.php";
require_once "webcore.logging.php";

/**
 * Defines the required methods for a class to be able to mange databse connections.
 * @package WebCore
 * @subpackage Data
 */
interface IDataConnection extends IObject
{
    
    /**
     * Begins a new transaction
     */
    public function transactionBegin();
    
    /**
     * Commits the current transaction. Throws an exception otherwise.
     */
    public function transactionCommit();
    
    /**
     * Rolls back the current transaction. Throws an exception otherwise.
     */
    public function transactionRollback();
    
    /**
     * Gets the concurrency mode for sql commands.
     * @return int One of the CONCURRENCY-prefixed constants defined in the DataConnectionBase
     */
    public function getConcurrencyMode();
    
    /**
     * Sets the concurrency mode for sql commands.
     * @param int One of the CONCURRENCY-prefixed constants defined in the DataConnectionBase
     */
    public function setConcurrencyMode($concurrencyMode);
    
    /**
     * Returns a connection object instantiated by using options from the Settings class.
     * @return IDataConnection
     */
    public static function fromSettings();
    
    /**
     * Opens the database connection.
     * @return int One of the STATE-prefixed constants defined in DataConnectionBase
     */
    public function connect();
    
    /**
     * Closes the database connection.
     * @return int One of the STATE-prefixed constants defined in DataConnectionBase
     */
    public function close();
    
    /**
     * Executes a query given a SqlCommand object.
     * This method returns an IndexedCollection of KeyedCollections representing the rows returned form the data source.
     * @param SqlCommandBase $sqlCommand The command containing the query and parameters to execute.
     */
    public function executeQuery($sqlCommand);
    
    /**
     * Used to execute a SqlCommand in the data source.
     * This method returns the number of affected rows or the result of the operation.
     * 
     * @param SqlCommandBase $sqlCommand The SQL command to execute.
     * @return int
     */
    public function executeNonQuery($sqlCommand);
    
    /**
     * Executes a query given a SqlCommand object and returns the value of the first row and column of the result set.
     *
     * @param SqlCommandBase $sqlCommand
     * @return mixed
     */
    public function executeScalar($sqlCommand);
    
    /**
     * Gets the associated database engine metadata helper.
     * @return IMetadataHelper
     */
    public function getMetadataHelper();
    
    /**
     * Gets the associated database engine metadata helper.
     * @return SqlCommandBase
     */
    public function createCommand();
    
    /**
     * Returns the associated DataTableAdapter for a given table name and schema.
     * @param string $schema
     * @param string $tableName
     * @return IDataTableAdapter
     */
    public function getDataTableAdapter($schema, $tableName);
    
    /**
     * Gets the Data Connection state
     * @return int One of the STATE-prefixed constants defined in DataConnectionBase
     */
    public function getConnectionState();
    
    /**
     * Gets the IDataTypeTranslator for the current connection.
     * Type translators provide data type conversion bridges accross PHP, the underlying PHP library, and the back-end database engine.
     * @return IDataTypeTranslator
     */
    public function getTypeTranslator();
    
    /**
     * Determines the name of the protocol this connection object uses.
     * @return string
     */
    public function getProtocolName();
    /**
     * Determines the name of the protocol this connection object uses.
     * @param string $value
     */
    public function setProtocolName($value);
    
    /**
     * Determines the server address or host name of this connection object.
     * @return string
     */
    public function getServer();
    /**
     * Determines the server address or host name of this connection object.
     * @param string $value
     */
    public function setServer($value);
    
    /**
     * Determines the port this connection uses.
     * @return int
     */
    public function getPort();
    /**
     * Determines the port this connection uses.
     * @param int $value
     */
    public function setPort($value);
    
    /**
     * Determines the username used to connect to the database.
     * @return string
     */
    public function getUsername();
    /**
     * Determines the username used to connect to the database.
     * @param string $value
     */
    public function setUsername($value);
    
    /**
     * Determines the password used to connect to the database.
     * @return string
     */
    public function getPassword();
    /**
     * Determines the password used to connect to the database.
     * @param string $value
     */
    public function setPassword($value);
    
    /**
     * Determines the name of the database for this connection.
     * @return string
     */
    public function getDatabaseName();
    /**
     * Determines the name of the database for this connection.
     * @param string $value
     */
    public function setDatabaseName($value);
    
    // Information methods
    
    /**
     * Determines the user under queries are currently being executed.
     * @return string
     */
    public function getCurrentUser();
    /**
     * Returns the autoincrement identifier of the last inserted row.
     * @return mixed
     */
    public function getLastInsertedId();
    
    /**
     * Gets the number of total rows of the previous select statement as if it were not executed with a limiting clause.
     * @return int
     */
    public function getFoundRows();
    /**
     * Gets the server version in a PHP-standard integer format.
     * @return int
     */
    public function getServerVersion();
    
    /**
     * Gets the UNIX timestamp of the current server date and time.
     * @return long
     */
    public function getServerTimestamp();
    
    /**
     * Obtains a globally unique identifier in the correct database engine format
     * @return mixed
     */
    public function getNewGuid();
    
    /**
     * Returns a correctly quoted string representing the provided identifier
     * @return string
     * @param string $identifier
     */
    public function quoteIdentifier($identifier);
}

/**
 * Defines the necessary methods for a class to provide database type translation.
 * 
 * @package WebCore
 * @subpackage Data
 */
interface IDataTypeTranslator extends IHelper
{
    /**
     * Gets Sql Parameter data type from data
     *
     * @param string $data
     * @return string
     */
    public function getSqlParameterDataType($data);
    
    /**
     * Translates to SQL data type
     *
     * @param string $dataType
     * @return string
     */
    public function toSqlDataType($phpDataType);
    
    /**
     * Translates to PHP data type
     *
     * @param string $sqlDataType
     * @return string
     */
    public function toPhpDataType($sqlDataType);
    
    /**
     * Translates to one of the TYPE_-prefixed enumerations withing the SqlParameter class
     *
     * @param string $sqlDataType
     * @return string
     */
    public function toSqlParameterDataType($sqlDataType);
    
    /**
     * Determines if the provided datat type string is a binary type
     * @param string $sqlDataType the sql data type
     * @return bool
     */
    public function isBinarySqlType($sqlDataType);
}

/**
 * Defines the necessary methods for a class to provide database metadata.
 *
 * @package WebCore
 * @subpackage Data
 */
interface IMetadataHelper extends IHelper
{
    /**
     * Returns an indexed collection of schemas for the parent IDataConnection
     * Each element contains a KeyedCollection of a single key 'schema'
     * @return IndexedCollection<KeyedCollection>
     */
    public function getSchemas();
    
    /**
     * Returns an IndexedCollection of table metadata in the given schema.
     * If schema is left empty, all the schemas for the current database are retrieved.
     * The structure of each element should be KeyedCollection(['schema'] => $strSchema, ['tableName'] => $strTableName, ['isView'] => $boolIsView)
     * @param string $schema
     * @return IndexedCollection
     */
    public function getTables($schema = '');
    
    /**
     * Returns an IndexedCollection of SP metadata in the given schema.
     * @return IndexedCollection
     */
    public function getStoredProcedures();
    
    /**
     * Gets a collection of column names that make up the given table's primary key.
     * Usually, this method will return a single item in the collection.
     * @param string $tableName The name of the table to retrieve the primary key from
     * @param string $schema The name of the schema where the table is located.
     * @return IndexedCollection
     */
    public function getPrimaryKeyColumnNames($schema, $tableName);
    
    /**
     * Return a collection of column metadata for the given schema and table name.
     * The structure of eacch element should be a KeyedCollection with the following keys:
     * columnName, defaultValue, dataType, charLength, isNullable, isCalculated, databaseType
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName The name of the table to retrieve columns for.
     * @return IndexedCollection
     */
    public function getColumns($schema, $tableName);
    
    /**
     * Return a collection of column metadata for the given SP name.
     * @return IndexedCollection
     */
    public function getStoredProcedureColumns($spName);
    
    /**
     * Retrieves metadata about data relations on which the given table name and schema depends.
     * The structure of each element should be a KeyedCollection with the following keys:
     * foreignTableName, foreignColumnName, localColumnName
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName
     * @return IndexedCollection
     */
    public function getDataRelations($schema, $tableName);
    
    /**
     * Retrieves metadata about data relations that depend on the given table name and schema.
     * The structure of each element should be a KeyedCollection with the following keys:
     * foreignTableName, foreignColumnName, localColumnName
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName
     * @return IndexedCollection
     */
    public function getDependentDataRelations($schema, $tableName);
    
    /**
     * Gets the string representing the SQL statements used to recreate the table schema.
     * @param string $schema The name of the schema the table belongs to.
     * @param string $tableName
     * @return string
     */
    public function getTableSchemaScript($schema, $tableName);
    
    /**
     * Gets the string representing the necssary SQL statements used to recreate the entire database schema.
     * @return string
     */
    public function getDatabaseSchemaScript();
}

/**
 * Defines the necessary methods for a class to provide database type translation.
 * 
 * @package WebCore
 * @subpackage Data
 */
abstract class DataTypeTranslatorBase extends HelperBase implements IDataTypeTranslator
{
    /**
     * Gets Sql Parameter data type from data
     *
     * @param string $data
     * @return string
     */
    public function getSqlParameterDataType($data)
    {
        $phpType = null;
        
        if (is_int($data))
            $phpType = 'int';
        if (is_float($data) || is_numeric($data) || is_double($data))
            $phpType = 'float';
        if (is_bool($data))
            $phpType = 'bool';
        if (is_string($data))
            $phpType = 'string';
        
        if (is_null($phpType))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = $phpType');
        
        $sqlType = $this->toSqlDataType($phpType);
        
        return $this->toSqlParameterDataType($sqlType);
    }
}

/**
 * Database connection base implementation.
 *
 * @package WebCore
 * @subpackage Data
 */
abstract class DataConnectionBase extends ObjectBase implements IDataConnection
{
    // Basic connection states
    const STATE_CLOSED = 0;
    const STATE_OPEN = 1;
    
    // Concurrency states
    
    /**
     *  Updates and Deletes do not need to match every field value (excluding binary fields) for them to be effectively committed.
     */
    const CONCURRENCY_LAST_IN_WINS = 0;
    /**
     * Updates and Deletes always need to match every field value (excluding binary fields) for them to be effectively committed.
     */
    const CONCURRENCY_FIRST_IN_WINS = 1;
    
    // State variables
    protected $transactionCapable;
    protected $isInTransaction;
    
    protected $concurrencyMode;
    protected $server;
    protected $port;
    protected $username;
    protected $password;
    protected $databaseName;
    protected $connectionState;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $server The hostname or IP address of the database server
     * @param string $username The Username used to connect to the database
     * @param string $password The Password used to connect to the database.
     * @param string $databaseName The initial database to select when connected.
     * @param int $port The port to connect, if it's zero connects to default port
     * @param bool $concurrency The concurrency mode
     * @param bool $connect Determines whether or not to connect to the database server upon instatiation.
     */
    public function __construct($server, $port, $databaseName, $username, $password, $concurrencyMode = self::CONCURRENCY_FIRST_IN_WINS, $connect = false)
    {
        if (is_string($server) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = server');
        if (is_string($username) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = username');
        if (is_string($password) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = password');
        if (is_string($databaseName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = $databaseName');
        
        $this->transactionCapable = true;
        $this->server             = $server;
        $this->port               = $port;
        $this->databaseName       = $databaseName;
        $this->username           = $username;
        $this->password           = $password;
        $this->concurrencyMode    = $concurrencyMode;
        $this->isInTransaction    = false;
        $this->connectionState    = self::STATE_CLOSED;
        
        if ($connect === true)
            $this->connect();
    }
    
    /**
     * @return bool true if successful, false otherwise
     */
    abstract protected function transactionBeginInternal();
    /**
     * @return bool true if successful, false otherwise
     */
    abstract protected function transactionCommitInternal();
    /**
     * @return bool true if successful, false otherwise
     */
    abstract protected function transactionRollbackInternal();
    /**
     * @return bool true if successful, false otherwise
     */
    abstract protected function connectInternal();
    /**
     * @return bool true if successful, false otherwise
     */
    abstract protected function closeInternal();
    /**
     * @param SqlCommand $sqlCommand
     * @return IndexedCollection<KeyedCollection>
     */
    abstract protected function executeQueryInternal($sqlCommand);
    /**
     * @param SqlCommand $sqlCommand
     * @return int
     */
    abstract protected function executeNonQueryInternal($sqlCommand);
    /**
     * @param SqlCommand $sqlCommand
     * @return mixed
     */
    abstract protected function executeScalarInternal($sqlCommand);
    
    /**
     * Gets one of the STATE-prefixed constants defined in this class.
     * @return int
     */
    public function getConnectionState()
    {
        return $this->connectionState;
    }
    
    /**
     * Begins a new transaction
     */
    public function transactionBegin()
    {
        if ($this->transactionCapable && !$this->isInTransaction && $this->getConnectionState() === DataConnectionBase::STATE_OPEN)
        {
            $this->isInTransaction = true;
            $this->transactionBeginInternal();
        }
    }
    
    /**
     * Commits the current transaction
     */
    public function transactionCommit()
    {
        if ($this->transactionCapable && $this->isInTransaction && $this->getConnectionState() === DataConnectionBase::STATE_OPEN)
        {
            $this->isInTransaction = false;
            $this->transactionCommitInternal();
        }
    }
    
    /**
     * Rolls back the current transaction
     */
    public function transactionRollback()
    {
        if ($this->transactionCapable && $this->isInTransaction && $this->getConnectionState() === DataConnectionBase::STATE_OPEN)
        {
            $this->isInTransaction = false;
            $this->transactionRollbackInternal();
        }
    }
    
    /**
     * Gets the concurrency mode for sql commands.
     * @return int One of the CONCURRENCY-prefixed constants defined in the DataConnectionBase
     */
    public function getConcurrencyMode()
    {
        return $this->concurrencyMode;
    }
    
    /**
     * Sets the concurrency mode for sql commands.
     * @param int $value One of the CONCURRENCY-prefixed constants defined in the DataConnectionBase
     */
    public function setConcurrencyMode($value)
    {
        $this->concurrencyMode = $value;
    }
    
    /**
     * Get a new connection instance of a typed connection using settings loaded from the Settings class.
     * The settings are loaded from the 'data' section of the Settings
     * 
     * @return DataConnectionBase
     */
    public static function fromSettings()
    {
        $dbSettings = Settings::getValue(Settings::SKEY_DATA);
        
        $dbProvider  = $dbSettings[Settings::KEY_DATA_PROVIDER];
        $dbServer    = $dbSettings[Settings::KEY_DATA_SERVER];
        $dbUsername  = $dbSettings[Settings::KEY_DATA_USERNAME];
        $dbPassword  = $dbSettings[Settings::KEY_DATA_PASSWORD];
        $dbName      = $dbSettings[Settings::KEY_DATA_DATABASE];
        $dbPort      = $dbSettings[Settings::KEY_DATA_PORT];
        $concurrency = $dbSettings[Settings::KEY_DATA_CONCURRENCY];
        
        switch (strtolower($dbProvider))
        {
            case 'mysqlconnection':
                require_once("webcore.data.mysql.php");
                return new MySqlConnection($dbServer, $dbName, $dbUsername, $dbPassword, $dbPort, $concurrency, true);
                break;
            case 'mssqlconnection':
                require_once("webcore.data.mssql.php");
                return new MssqlConnection($dbServer, $dbName, $dbUsername, $dbPassword, $dbPort, $concurrency, true);
                break;
        }
        
        throw new SystemException(SystemException::EX_DBPROVIDERNOTFOUND, "The database provider $dbProvider is not supported.");
    }
    
    /**
     * Opens the database connection.
     * @return int One of the STATE-prefixed constants defined in DataConnectionBase
     */
    public function connect()
    {
        if ($this->getConnectionState() !== DataConnectionBase::STATE_OPEN)
        {
            if ($this->connectInternal())
                $this->connectionState = self::STATE_OPEN;
            else
                $this->connectionState = self::STATE_CLOSED;
        }
        return $this->getConnectionState();
    }
    
    /**
     * Closes the database connection.
     * @return int One of the STATE-prefixed constants defined in DataConnectionBase
     */
    public function close()
    {
        if ($this->getConnectionState() !== DataConnectionBase::STATE_CLOSED)
        {
            if ($this->closeInternal())
                $this->connectionState = DataConnectionBase::STATE_CLOSED;
        }
        
        return $this->getConnectionState();
    }
    
    /**
     * Executes a query given a SqlCommand object.
     * This method returns an IndexedCollection of KeyedCollections representing the rows returned form the data source.
     * 
     * @param SqlCommand $sqlCommand The command containing the query and parameters to execute.
     * @return IndexedCollection
     */
    public function executeQuery($sqlCommand)
    {
        if ($this->getConnectionState() === DataConnectionBase::STATE_OPEN)
        {
            LogManager::debug($sqlCommand->__toString());
            return $this->executeQueryInternal($sqlCommand);
        }
        
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The connection must be in its STATE_OPEN state in order to execute the command.');
    }
    
    /**
     * Used to execute a SqlCommand in the data source.
     * This method returns the number of affected rows or the result of the operation.
     * 
     * @param SqlCommandBase $sqlCommand The SQL command to execute.
     * @return int
     */
    public function executeNonQuery($sqlCommand)
    {
        if ($this->getConnectionState() === DataConnectionBase::STATE_OPEN)
        {
            LogManager::debug($sqlCommand->__toString());
            return $this->executeNonQueryInternal($sqlCommand);
        }
        
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The connection must be in its STATE_OPEN state in order to execute the command.');
    }
    
    /**
     * Executes a query given a SqlCommand object and returns the value of the first row and column of the result set.
     *
     * @param SqlCommandBase $sqlCommand
     * @return mixed
     */
    public function executeScalar($sqlCommand)
    {
        if ($this->getConnectionState() === DataConnectionBase::STATE_OPEN)
        {
            LogManager::debug($sqlCommand->__toString());
            return $this->executeScalarInternal($sqlCommand);
        }
        
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The connection must be in its STATE_OPEN state in order to execute the command.');
    }
    
    /**
     * Determines the server address or host name of this connection object.
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }
    
    /**
     * Determines the server address or host name of this connection object.
     * @param string $value
     */
    public function setServer($value)
    {
        $this->server = $value;
    }
    
    /**
     * Determines the port this connection uses.
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
    /**
     * Determines the port this connection uses.
     * @param int $value
     */
    public function setPort($value)
    {
        $this->port = $value;
    }
    
    /**
     * Determines the name of the protocol this connection object uses.
     * @return string
     */
    public function getProtocolName()
    {
        return 'TCP/IP';
    }
    
    /**
     * Determines the name of the protocol this connection object uses.
     * @param string $value
     */
    public function setProtocolName($value)
    {
    }
    
    /**
     * Determines the username used to connect to the database.
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    /**
     * Determines the username used to connect to the database.
     * @param string $value
     */
    public function setUsername($value)
    {
        $this->username = $value;
    }
    
    /**
     * Determines the password used to connect to the database.
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * Determines the password used to connect to the database.
     * @param string $value
     */
    public function setPassword($value)
    {
        $this->password = $value;
    }
    
    /**
     * Determines the name of the database for this connection.
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }
    /**
     * Determines the name of the database for this connection.
     * @param string $value
     */
    public function setDatabaseName($value)
    {
        $this->databaseName = $value;
    }
}

/**
 * Base class for database metadata extraction.
 *
 * @package WebCore
 * @subpackage Data
 */
abstract class MetadataHelperBase extends HelperBase implements IMetadataHelper
{
    /**
     * @var ConnectionBase
     */
    protected $connection;
    
    /**
     * Creates a new MetadataHelper instance
     *
     * @param IDataConnection $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * Gets the data connection object for this MetadataHelper
     * @return IDataConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}

/**
 * Provides general data access to the database.
 * 
 * @package WebCore
 * @subpackage Data
 */
class DataContext extends ObjectBase implements ISingleton
{
    /**
     * @var IDataConnection
     */
    protected static $connection;
    /**
     * Represents the instance of the singleton object
     *
     * @var DataContext
     */
    private static $__instance = null;
    
    /**
     * Generates entities from a DataContext
     * @todo Add comments to the generated entities
     * @todo Add SP creation
     * @param DataContext $dataContext
     */
    protected static function generate($dataContext)
    {
        set_time_limit(600);
        
        if (ObjectIntrospector::isA($dataContext, 'DataContext') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = dataContext');
        
        $metadataHelper = $dataContext->getMetadataHelper();
        $translator     = $dataContext->getConnection()->getTypeTranslator();
        
        $dbDefinitionClass = ucfirst($dataContext->getConnection()->getDatabaseName()) . 'Metabase';
        $tables            = $metadataHelper->getTables($dataContext->getConnection()->getDatabaseName());
        
        $class = "<" . "?php\r\n\r\n";
        $class .= "class {$dbDefinitionClass} extends EntityRegistryBase\r\n";
        $class .= "{\r\n";
        $class .= "    protected static \$__instance;\r\n";
        $class .= "    public static function isLoaded() { return (is_null(self::\$__instance)) ? false : true; }\r\n";
        $class .= "    public static function getInstance() { if (self::isLoaded() == false) self::\$__instance = new {$dbDefinitionClass}(); return self::\$__instance; }\r\n";
        $class .= "    protected function __construct()\r\n";
        $class .= "    {\r\n";
        $class .= "        parent::__construct();\r\n";
        foreach ($tables as $table)
        {
            $tableName      = $table->getValue("tableName");
            $entityTypeName = ucfirst($tableName);
            $class .= "        \$this->entityTypeNames->addValue('{$entityTypeName}');\r\n";
        }
        $class .= "    }\r\n\r\n";
        $class .= "    public function getDatabaseName()\r\n";
        $class .= "    {\r\n";
        $class .= "        return '" . $dataContext->getConnection()->getDatabaseName() . "';\r\n";
        $class .= "    }\r\n\r\n";
        $class .= "    public function getDatabaseDDL()\r\n";
        $class .= "    {\r\n";
        $class .= "        \$dbDdl = \"" . str_replace("\"", "\\\"", $metadataHelper->getDatabaseSchemaScript()) . ";\\r\\n\\r\\n\";\r\n";
        $class .= "        return \$dbDDL . parent::getDatabaseDDL();\r\n";
        $class .= "    }\r\n\r\n";
        $class .= "    protected function loadMetadata(\$entityTypeName)\r\n";
        $class .= "    {\r\n";
        $class .= "        switch (\$entityTypeName)\r\n";
        $class .= "        {\r\n";
        foreach ($tables as $table)
        {
            $tableName      = $table->getValue("tableName");
            $entityTypeName = ucfirst($tableName);
            $isView         = $table->getValue('isView');
            $schemaName     = $table->getValue('schema');
            
            $class .= "            case '$entityTypeName' :\r\n";
            $class .= "                \$this->registerEntity('{$entityTypeName}', '{$schemaName}', '{$tableName}', {$isView});\r\n";
            
            foreach ($metadataHelper->getPrimaryKeyColumnNames($schemaName, $tableName) as $primaryKey)
            {
                $columnName = $primaryKey->getValue('columnName');
                $class .= "                \$this->registerEntityPriKey('{$entityTypeName}', '{$columnName}');\r\n";
            }
            foreach ($metadataHelper->getDataRelations($schemaName, $tableName) as $dataRelation)
            {
                $localFieldName   = $dataRelation->getValue('localColumnName');
                $foreignFieldName = $dataRelation->getValue('foreignColumnName');
                $foreignTableName = ucfirst($dataRelation->getValue('foreignTableName'));
                $columnName       = $primaryKey->getValue('columnName'); //@todo This hack what?!
                
                $class .= "                \$this->registerEntityRelation('{$entityTypeName}', '{$localFieldName}', '{$foreignTableName}', '{$foreignFieldName}', true);\r\n";
            }
            foreach ($metadataHelper->getDependentDataRelations($schemaName, $tableName) as $dataRelation)
            {
                $localFieldName   = $dataRelation->getValue('localColumnName');
                $foreignFieldName = $dataRelation->getValue('foreignColumnName');
                $foreignTableName = ucfirst($dataRelation->getValue('foreignTableName'));
                
                $columnName = $primaryKey->getValue('columnName');
                $class .= "                \$this->registerEntityRelation('{$entityTypeName}', '{$localFieldName}', '{$foreignTableName}', '{$foreignFieldName}', false);\r\n";
            }
            $class .= "                \$this->registerEntityDDL('{$entityTypeName}', \"" . str_replace("\n", " ", str_replace("\"", "\\\"", $metadataHelper->getTableSchemaScript($schemaName, $tableName))) . "\");\r\n";
            $class .= "                return true;\r\n";
            $class .= "                break;\r\n\r\n";
        }
        $class .= "            default:\r\n";
        $class .= "                return false;\r\n";
        $class .= "                break;\r\n";
        $class .= "        }\r\n";
        $class .= "    }\r\n";
        $class .= "}\r\n\r\n";
        
        foreach ($tables as $table)
        {
            $tableName      = $table->getValue("tableName");
            $entityTypeName = ucfirst($tableName);
            $isView         = $table->getValue('isView');
            $schemaName     = $table->getValue('schema');
            
            // class declaration
            $class .= "class {$entityTypeName} extends EntityBase\r\n";
            $class .= "{\r\n";
            $class .= "    public static function getMetabase() { return {$dbDefinitionClass}::getInstance(); }\r\n";
            $class .= "    public static function getSchemaName() { return self::getMetabase()->getSchemaName('{$entityTypeName}'); }\r\n";
            $class .= "    public static function getTableName() { return self::getMetabase()->getTableName('{$entityTypeName}'); }\r\n";
            $class .= "    public static function getIsView() { return self::getMetabase()->getIsView('{$entityTypeName}'); }\r\n";
            $class .= "    public static function getDDL() { return self::getMetabase()->getDDL('{$entityTypeName}'); }\r\n";
            $class .= "    public static function getPrimaryKeyFieldNames() { return self::getMetabase()->getPrimaryKeyFieldNames('{$entityTypeName}'); }\r\n";
            $class .= "    public static function getEntityRelations() { return self::getMetabase()->getEntityRelations('{$entityTypeName}'); }\r\n";
            $class .= "    public static function getEntityRelation(\$entityTypeName) { return self::getMetabase()->getEntityRelation('{$entityTypeName}', \$entityTypeName); }\r\n";
            $class .= "    public static function createInstance() { return new {$entityTypeName}(); }\r\n\r\n";
            // entity class constructor
            
            $class .= "    public function __construct()\r\n";
            $class .= "    {\r\n";
            $class .= "        parent::__construct();\r\n";
            foreach ($metadataHelper->getColumns($schemaName, $tableName) as $column)
            {
                $columnName   = $column->getValue('columnName');
                $defaultValue = str_replace('\'', "\\'", $column->getValue('defaultValue'));
                $dataType     = $column->getValue('dataType');
                $charLength   = $column->getValue('charLength');
                $isNullable   = $column->getValue('isNullable');
                $isCalculated = $column->getValue('isCalculated');
                $databaseType = $column->getValue('databaseType');
                
                if ($translator->isBinarySqlType($column->getValue('dataType')) === true)
                {
                    $class .= "        \$this->registerBinaryEntityField('{$columnName}', '{$dataType}', true, {$isNullable});\r\n";
                }
                else
                {
                    //@todo Here we have a bug, MSSQL can have defaultValues with '
                    $class .= "        \$this->registerEntityField('{$columnName}', '{$dataType}', {$charLength}, '{$defaultValue}', '{$defaultValue}', {$isNullable}, {$isCalculated});\r\n";
                }
                
            }
            $class .= "    }\r\n";
            $class .= "}\r\n\r\n";
        }
        
        $class .= "?" . ">";
        
        $filePath = HttpContext::getDocumentRoot() . "data/";
        
        if (!is_dir($filePath))
            mkdir($filePath);
        
        $filePath .= $dataContext->getConnection()->getDatabaseName() . ".context.php";
        file_put_contents($filePath, $class);
    }
    
    /**
     * Gets the singleton instance for this class.
     * @return DataContext
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
        {
            self::$__instance = new DataContext();
            if (is_null(self::$connection))
                self::$connection = DataConnectionBase::fromSettings();
            $dataContextFile = self::getDataContextPath() . self::$connection->getDatabaseName() . ".context.php";
            if (!file_exists($dataContextFile))
                self::generate(self::$__instance);
            require_once $dataContextFile;
        }
        
        return self::$__instance;
    }
    
    /**
     * Gets the full path of the data context file for the current application
     * @return string
     */
    public static function getDataContextPath()
    {
        return HttpContext::getDocumentRoot() . "data/";
    }
    
    /**
     * Sets the database connection object to use.
     * @param IDataConnection $connection
     */
    public static function setConnection($connection)
    {
        if (!ObjectIntrospector::isImplementing($connection, IDataConnection))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Object must implement IDataConnection.');
        self::$connection = $connection;
    }
    
    /**
     * Gets the current database connection object.
     * 
     * @return DataConnectionBase
     */
    public function getConnection()
    {
        return self::$connection;
    }
    
    /**
     * Gets metadata helper from connection.
     * This is a shortcut method.
     * @return IMetadataHelper
     */
    public function getMetadataHelper()
    {
        return self::$connection->getMetadataHelper();
    }
    
    /**
     * Creates the database schema in the current database connection.
     * This method reads the DatabaseDefinition class and all of its child entity types to recreate the schema in the connection.
     */
    public function createDatabase()
    {
        $entitiesFile = HttpContext::getDocumentRoot() . "data/dataContext." . self::$connection->getDatabaseName() . ".php";
        if (!file_exists($entitiesFile))
            throw new SystemException(SystemException::EX_CLASSNOTFOUND, 'Supporting entities file not found.');
        require_once $entitiesFile;
        
        $dbDefinitionClass = ucfirst($this->getConnection()->getDatabaseName()) . 'DatabaseDefinition';
        /**
         * @var IndexedCollection
         */
        $dbEntityTypes     = call_user_func(array(
            $dbDefinitionClass,
            'getEntityTypeNames'
        ));
        $dbDDL             = call_user_func(array(
            $dbDefinitionClass,
            'getDDL'
        ));
        $createDbCommand   = self::$connection->createCommand();
        $createDbCommand->setCommandText($dbDDL);
        
        try
        {
            $this->getConnection()->transactionBegin();
            $this->getConnection()->executeNonQuery($createDbCommand);
            
            foreach ($dbEntityTypes as $entityTypeName)
            {
                $entityDDL           = call_user_func(array(
                    $entityTypeName,
                    'getDDL'
                ));
                $createEntityCommand = self::$connection->createCommand();
                $createEntityCommand->setCommandText($entityDDL);
                $this->getConnection()->executeNonQuery($createDbCommand);
            }
            
            $this->getConnection()->transactionCommit();
        }
        catch (Exception $ex)
        {
            $this->getConnection()->transactionRollback();
        }
    }
    
    /**
     * Drops (deletes) the current database in the current connection if it exists
     * @return int
     */
    public function dropDatabase()
    {
        $dbSettings = Settings::getValue(Settings::SKEY_DATA);
        if ($dbSettings[Settings::KEY_DATA_DROPENABLE] !== '1')
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'This operation must be explicily allowed in the settings file.');
        $dropCommand = $this->getConnection()->createCommand();
        $dropCommand->setCommandText("DROP DATABASE " . $this->getConnection()->getDatabaseName());
        return $this->getConnection()->executeNonQuery($dropCommand);
    }
    
    /**
     * Begins a new transaction
     */
    public function transactionBegin()
    {
        $this->getConnection()->transactionBegin();
    }
    
    /**
     * Commits the current transaction
     */
    public function transactionCommit()
    {
        $this->getConnection()->transactionCommit();
    }
    
    /**
     * Rolls back the current transaction
     */
    public function transactionRollback()
    {
        $this->getConnection()->transactionRollback();
    }
    
    /**
     * Executes a given SQL query for which a recordset is expected to be returned.
     * This is a shortcut method.
     * 
     * @param SqlCommandBase $sqlCommand
     * @return IndexedCollection<KeyedCollection>
     */
    public function executeQuery($sqlCommand)
    {
        return $this->getConnection()->executeQuery($sqlCommand);
    }
    
    /**
     * Executes the given SQL query and returns the number of affected rows
     * This is a shortcut method.
     * 
     * @param SqlCommandBase $sqlCommand
     * @return int
     */
    public function executeNonQuery($sqlCommand)
    {
        return $this->getConnection()->executeNonQuery($sqlCommand);
    }
    
    /**
     * Obtains the top-most, left-most value of the dataset returned by executing the SQL command
     * This is a shortcut method.
     * @param SqlCommandBase $sqlCommand
     * @return mixed
     */
    public function executeScalar($sqlCommand)
    {
        if (is_object($sqlCommand) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER);
            
        return $this->getConnection()->executeScalar($sqlCommand);
    }
    
    /**
     * Gets a DataTableAdapter for the given entity type name.
     * 
     * @param string $entityTypeName
     * @return DataTableAdapterBase
     */
    public function getAdapter($entityTypeName)
    {
        if (is_string($entityTypeName) === false || class_exists($entityTypeName) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = $entityTypeName');
        
        $table  = call_user_func(array(
            $entityTypeName,
            'getTableName'
        ));
        $schema = call_user_func(array(
            $entityTypeName,
            'getSchemaName'
        ));
        
        return $this->getConnection()->getDataTableAdapter($schema, $table);
    }
    
    /**
     * Magic method to get the TableAdapter for the given entity.
     *
     * @param string $name
     * @return DataTableAdapterBase
     */
    public function __get($entityTypeName)
    {
        return $this->getAdapter($entityTypeName);
    }
    
    /**
     * Determines whether the singleton's instance is currently loaded
     *
     * @return bool
     */
    public static function isLoaded()
    {
        return (is_null(self::$__instance) === false);
    }
}

/**
 * Represents a SQL Command parameter passed as a query argument in prepared statements.
 * 
 * @package WebCore
 * @subpackage Data
 */
abstract class SqlParameterBase extends SerializableObjectBase
{
    protected $name;
    protected $sqlType;
    protected $value;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name The name of the parameter
     * @param string $type One of the '*_TYPE_' - prefixed constants defined by this class
     * @param string $value The value of the parameter
     */
    public function __construct($name, $type, $value = NULL)
    {
        $this->name    = $name;
        $this->sqlType = $type;
        $this->value   = $value;
    }
    
    public function __toString()
    {
        return $this->name . ': ' . $this->value;
    }
    
    public function getName()
    {
        return $this->name;
    }
    public function setName($value)
    {
        $this->name = $value;
    }
    public function getSqlType()
    {
        return $this->sqlType;
    }
    public function setSqlType($value)
    {
        $this->sqlType = $value;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function setValue($value)
    {
        $this->value = $value;
    }
}

/**
 * Represents a generic SQL Statement for either querying data or executing a command.
 *
 * @package WebCore
 * @subpackage Data
 */
abstract class SqlCommandBase extends SerializableObjectBase
{
    /**
     * @var IndexedCollection
     */
    protected $params;
    protected $commandText;
    
    /**
     * Creates a new instance of a generic SQL command
     *
     * @param string $commandText
     */
    public function __construct($commandText = '')
    {
        $this->commandText = $commandText;
        $this->params      = new IndexedCollection();
    }
    
    /**
     * Gets command text
     *
     * @return string
     */
    public function getCommandText()
    {
        return $this->commandText;
    }
    
    /**
     * Sets command text
     *
     * @param string $commandText
     */
    public function setCommandText($commandText)
    {
        if (is_string($commandText) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = commandText');
        
        $this->commandText = $commandText;
    }
    
    /**
     * Adds a parameter to SQL Command
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $type One of the '*_TYPE_' - prefixed constants defined by this class
     * @param string $value The value of the parameter
     */
    abstract public function addParam($name, $type, $value = null);
    
    /**
     * Adds a quoted parameter to SQL Command (strings, text, floats and dates are all quoted parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    abstract public function addQuotedParam($name, $value);

    /**
     * Adds an unquoted parameter to SQL Command (integers, booleans, bitfields are examples of unquoted parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    abstract public function addUnquotedParam($name, $value);
    
    /**
     * Adds an unquoted parameter to SQL Command (blob, image, binary are examples of binary parameters)
     * If using indexed parameters (?) as opposed to named parameters (@name) the $name argument is only used for reference
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     */
    abstract public function addBinaryParam($name, $value);
    
    /**
     * Gets the SQL Command parameters as an indexed array
     * 
     * @return IndexedCollection
     */
    public function getParams()
    {
        return $this->params;
    }
    
    public function __toString()
    {
        $text = 'SQL = ' . $this->commandText;
        if ($this->params->getCount() > 0)
        {
            $text .= ' << Params = {' . $this->params->implode() . '}';
        }
        return $text;
    }
}
?>