<?php
/**
 * @package WebCore
 * @subpackage Scaffolding
 * @version 1.0
 * 
 * Provides code generators for common model-view-controller combos
 * @todo Allow for basing the scaffolding on objects other than data Entities (i.e. Popos or Keyed collections)
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.data.php";
require_once "webcore.model.form.php";
require_once "webcore.model.grid.php";

/**
 * Represents the base implementation of a Widget scaffolder
 * @package WebCore
 * @subpackage Scaffoling
 */
abstract class WidgetScaffolderBase extends SerializableObjectBase
{
    protected $widgetClassName;
    protected $viewTypeName;
    protected $modelTypeName;
    
    protected $source;
    
    protected $optionPackageName;
    protected $optionSubpackageName;
    protected $optionAuthorName;
    protected $optionAuthorEmail;
    protected $metadata;
    protected $optionControlAccessors;
    
    /**
     * Creates a new instance of this class
     */
    protected function __construct()
    {
        // options
        $this->optionControlAccessors = false;
        $this->optionPackageName      = 'Application';
        $this->optionAuthorName       = 'WebCore Scaffolder';
        $this->optionAuthorEmail      = 'webmaster@localhost';
        $this->optionSubpackageName   = 'Widgets';
    }
    
    /**
     * Gets the widget's class name
     * @return string
     */
    public function getWidgetClassName()
    {
        return $this->widgetClassName;
    }
    
    /**
     * Determines the package name
     * @return string
     */
    public function getOptPackageName()
    {
        return $this->optionPackageName;
    }
    
    /**
     * Determines the package name
     * @param string $value
     */
    public function setOptPackageName($value)
    {
        $this->optionPackageName = $value;
    }
    
    /**
     * Determines the subpackage name
     * @return string
     */
    public function getOptSubpackageName()
    {
        return $this->optionSubpackageName;
    }
    
    /**
     * Determines the subpackage name
     * @param string $value
     */
    public function setOptSubpackageName($value)
    {
        $this->optionSubpackageName = $value;
    }
    
    /**
     * Determines the author's name
     * @return string
     */
    public function getOptAuthorName()
    {
        return $this->optionAuthorName;
    }
    
    /**
     * Determines the author's name
     * @param string $value
     */
    public function setOptAuthorName($value)
    {
        $this->optionAuthorName = $value;
    }
    
    /**
     * Determines the author's email
     * @return string
     */
    public function getOptAuthorEmail()
    {
        return $this->optionAuthorEmail;
    }
    
    /**
     * Determines the author's email
     * @param string $value
     */
    public function setOptAuthorEmail($value)
    {
        $this->optionAuthorEmail = $value;
    }
    
    /**
     * Determines whether control model accessor methods should be generated
     * @return bool
     */
    public function getOptControlAccessors()
    {
        return $this->optionControlAccessors;
    }
    
    /**
     * Determines whether control model accessor methods should be generated
     * @param bool $value
     */
    public function setOptControlAccessors($value)
    {
        $this->optionControlAccessors = $value;
    }
    
    /**
     * Generates default metadata to consume by the code generator.
     * This method must be called before the generateCode() method.
     */
    abstract protected function generateMetadata();
    
    /**
     * Generates code based on the previously-generated metadata
     * This method will only work properly if the generateMetadata method was called previously.
     * @return string
     */
    abstract public function generateCode();
    
    /**
     * Gets a keyed collection with all the necessary information for the code generator.
     * @return KeyedCollection
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
    
    /**
     * Sets a keyed collection with all the necessary information for the code generator.
     * @param Popo $value
     */
    public function setMetadata($value)
    {
        $this->metadata = $value;
    }
    
    /**
     * Determines the object that this scaffolder uses to generate its code.
     * @return mixed Usually an EntityBase, Popo, or KeyedCollection
     */
    public function getSource()
    {
        return $this->source;
    }
    
    /**
     * Determines the object that this scaffolder uses to generate its code.
     * This method clears the existing value for the metadata if the regenerate parameter is set to true
     * @param mixed $value Usually an EntityBase, Popo, or KeyedCollection
     * @param bool $regenerate
     */
    public function setSource($value, $regenerate = true)
    {
        $this->source = $value;
        if ($regenerate === true)
        {
            $this->metadata = null;
            $this->generateMetadata();
        }
    }
}

/**
 * Represents an implementation of the Grid Widget Scaffolder
 * @package WebCore
 * @subpackage Scaffoling
 */
class GridWidgetScaffolder extends WidgetScaffolderBase
{
    const META_FIELDS = 'fields';
    const META_JOINS = 'relations';
    
    const CTL_GROUPINGCOULUMN   = 'GroupingColumn';
    const CTL_TEXTCOLUMN        = 'TextBoundGridColumn';
    const CTL_NUMBERCOLUMN      = 'NumberBoundGridColumn';
    const CTL_DATETIMECOLUMN    = 'DateTimeBoundGridColumn';
    const CTL_MONEYCOLUMN       = 'MoneyBoundGridColumn';
    
    protected $optExportExcelBiff;
    protected $optExportExcel2007;
    protected $optPrintPreview;
    protected $optExportCsv;
    protected $optDetailsCommand;
    protected $optSelectCommand;
    protected $optDeleteCommand;
    protected $optEditCommand;
    
    
    public function __construct($viewTypeName = 'HtmlGridView')
    {
        parent::__construct();
        $this->source        = null;
        $this->modelTypeName = 'Grid';
        $this->viewTypeName  = $viewTypeName;
        $this->metadata      = new KeyedCollection();
        $fields              = new IndexedCollection();
        $this->metadata->setValue(self::META_FIELDS, $fields);
        
        // Options
        $this->optExportExcel2007       = false;
        $this->optExportExcelBiff       = false;
        $this->optExportCsv             = true;
        $this->optPrintPreview          = true;
        $this->optDetailsCommand        = true;
        $this->optDeleteCommand         = false;
        $this->optEditCommand           = false;
        $this->optSelectCommand         = false;
    }
    
    /**
     * Set grid print preview and excel exporting capabilities
     * @param bool $excel2007
     * @param bool $excelBiff
     * @param bool $printPreview
     * @param bool $csv
     */
    public function setOptExporters($excel2007, $excelBiff, $printPreview, $csv)
    {
        $this->optExportExcel2007 = $excel2007;
        $this->optExportExcelBiff = $excelBiff;
        $this->optPrintPreview    = $printPreview;
        $this->optExportCsv       = $csv;
    }
    
    /**
     * Gets whether the grid has the capability of exporting to Excel 2007 format
     * @return bool
     */
    public function getOptExcel2007()
    {
        return $this->optExportExcel2007;
    }

    /**
     * Gets whether the grid has the capability of exporting to Excel 97-2003 format
     * @return bool
     */
    public function getOptExcelBiff()
    {
        return $this->optExportExcelBiff;
    }
    
    /**
     * Gets whether the grid has the capability of showing a print preview
     * @return bool
     */
    public function getOptPrintPreview()
    {
        return $this->optPrintPreview;
    }
    
    /**
     * Gets whether the grid has the capability of exporting data to CSV
     * @return bool
     */
    public function getOptCsv()
    {
        return $this->optExportCsv;
    }
    
    /**
     * Sets the available command columns for the scaffolder
     * @param bool $details
     * @param bool $edit
     * @param bool $delete
     * @param bool $select
     */
    public function setOptCommands($details, $edit, $delete, $select)
    {
        $this->optDetailsCommand = $details;
        $this->optDeleteCommand = $delete;
        $this->optEditCommand = $edit;
        $this->optSelectCommand = $select;
    }
    
    /**
     * Gets whether the grid has a Details Command
     * @return bool
     */
    public function getOptDetailsCommand()
    {
        return $this->optDetailsCommand;
    }
    
    /**
     * Gets whether the grid has a Select Command
     * @return bool
     */
    public function getOptSelectCommand()
    {
        return $this->optSelectCommand;
    }
    
    /**
     * Gets whether the grid has a Edit Command
     * @return bool
     */
    public function getOptEditCommand()
    {
        return $this->optEditCommand;
    }
    
    /**
     * Gets whether the grid has a Delete Command
     * @return bool
     */
    public function getOptDeleteCommand()
    {
        return $this->optDeleteCommand;
    }
    
    /**
     * Generates the default metadata for the current source object
     */
    protected function generateMetadata()
    {
        if (ObjectIntrospector::isExtending($this->source, 'EntityBase') !== true)
        {
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Scaffolder only supports an EntityBase-derived source.');
        }
        
        $this->widgetClassName = $this->source->getType()->getName() . 'GridWidget';
        $this->metadata = new KeyedCollection();
        $fieldMetas = new IndexedCollection();
        $joinMetas = new IndexedCollection();
        
        /**
         * @var EntityBase
         */
        $entity       = $this->source;
        /**
         * @var EntityField
         */
        $fieldObject  = null;
        $pks          = $entity->getPrimaryKeyFieldNames();
        $currentIndex = 0;
        
        foreach ($entity->getFields() as $fieldObject)
        {
            $fieldName = $fieldObject->getFieldName();
            $currentIndex++;
            $fieldMeta = new Popo();
            $fieldMeta->addFieldValue('Generate', ObjectIntrospector::isA($fieldObject, 'BinaryEntityField') ? 'false' : 'true');
            $fieldMeta->addFieldValue('DisplayOrder', $currentIndex * 10);
            $fieldMeta->addFieldValue('SchemaName', $entity->getSchemaName());
            $fieldMeta->addFieldValue('TableName', $entity->getTableName());
            $fieldMeta->addFieldValue('FieldName', $fieldName);
            $fieldMeta->addFieldValue('Caption', StringHelper::toWords($fieldName));
            $fieldMeta->addFieldValue('BindingName', $entity->getTableName() . '_' . $fieldName);
            $fieldMeta->addFieldValue('ModelType', self::CTL_TEXTCOLUMN);
            $fieldMeta->addFieldValue('IsPriKey', $pks->containsValue($fieldName) ? 'true' : 'false');
            $fieldMeta->addFieldValue('DbDataType', $fieldObject->getDbDataType());
            $fieldMetas->addItem($fieldMeta);
        }
        
        $relations = $entity->getEntityRelations();
        foreach ($relations as $relation)
        {
            
            if ($relation->getEntity()->getType()->getName() == $this->source->getType()->getName())
                continue;
            if (!$relation->getIsParentRelation()) // only get the entities this entity depends on.
                continue;
            
            $entity = $relation->getEntity();
            $joinMetas->addValue($entity->getType()->getName());
            
            foreach ($entity->getFields() as $fieldObject)
            {
                $fieldName = $fieldObject->getFieldName();
                $fieldMeta = new Popo();
                $fieldMeta->addFieldValue('Generate',
                    $fieldName == $relation->getForeignFieldName() ||
                    StringHelper::endsWith(strtolower($fieldName), 'id') ? 'false' : 'true');
                $fieldMeta->addFieldValue('DisplayOrder', $currentIndex * 10);
                $fieldMeta->addFieldValue('SchemaName', $entity->getSchemaName());
                $fieldMeta->addFieldValue('TableName', $entity->getTableName());
                $fieldMeta->addFieldValue('FieldName', $fieldName);
                $fieldMeta->addFieldValue('Caption', StringHelper::toWords($fieldName));
                $fieldMeta->addFieldValue('BindingName', $relation->getLocalFieldName() . '_' . $entity->getTableName() . '_' . $fieldName);
                $fieldMeta->addFieldValue('ModelType', self::CTL_TEXTCOLUMN);
                $fieldMeta->addFieldValue('DbDataType', $fieldObject->getDbDataType());
                $fieldMeta->addFieldValue('IsPriKey', 'false');
                $fieldMetas->addItem($fieldMeta);
                $currentIndex++;
            }
        }
        
        foreach ($fieldMetas as $fieldMeta)
        {
            $fieldName = strtolower($fieldMeta->FieldName);
            $fieldDataType = strtolower($fieldMeta->DbDataType);
            
            if (StringHelper::strContains($fieldDataType, 'date'))
            {
                if (StringHelper::strContains($fieldName, 'crea') || StringHelper::strContains($fieldName, 'ins'))
                {
                    $fieldMeta->ModelType = self::CTL_DATETIMECOLUMN;
                    $fieldMeta->Generate = 'false';
                }
                elseif (StringHelper::strContains($fieldName, 'upda') || StringHelper::strContains($fieldName, 'mod'))
                {
                    $fieldMeta->ModelType = self::CTL_DATETIMECOLUMN;
                    $fieldMeta->Generate = 'false';
                }
                elseif (StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_TIME)) ||
                        StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_HOUR)))
                {
                    $fieldMeta->ModelType = self::CTL_DATETIMECOLUMN;
                }
                else
                {
                    $fieldMeta->ModelType = self::CTL_DATETIMECOLUMN;
                }
            }
            elseif ($fieldDataType == 'timespan')
            {
                $fieldMeta->ModelType = self::CTL_DATETIMECOLUMN;
            }
            elseif ($fieldDataType == 'tinyint' || $fieldDataType == 'bit')
            {
                $fieldMeta->ModelType = self::CTL_NUMBERCOLUMN; // @todo implement YesNoBoundGridColumn
            }
            elseif (StringHelper::strContains($fieldDataType, 'int'))
            {
                $fieldMeta->ModelType = self::CTL_NUMBERCOLUMN;
            }
            elseif (StringHelper::strContains($fieldDataType,'float') ||
                    StringHelper::strContains($fieldDataType,'numeric') ||
                    StringHelper::strContains($fieldDataType, 'double')) // @todo Implement PercentageBoundGridColumn
            {
                $fieldMeta->ModelType = self::CTL_NUMBERCOLUMN;
            }
            elseif (StringHelper::strContains($fieldDataType, 'decimal') ||
                    StringHelper::strContains($fieldDataType, 'money'))
            {
                $fieldMeta->ModelType = self::CTL_MONEYCOLUMN;
            }
            elseif (StringHelper::strContains($fieldDataType, 'text'))
            {
                $fieldMeta->ModelType = self::CTL_TEXTCOLUMN;
            }
            elseif (StringHelper::strContains($fieldDataType, 'varchar'))
            {
                if (StringHelper::strContains($fieldName, 'crea') || StringHelper::strContains($fieldName, 'ins'))
                {
                    $fieldMeta->Generate = 'false';
                }
                elseif (StringHelper::strContains($fieldName, 'upda') || StringHelper::strContains($fieldName, 'mod'))
                {
                    $fieldMeta->Generate = 'false';
                }
                elseif (StringHelper::strContains($fieldName, 'password'))
                {
                    $fieldMeta->Generate = 'false';
                }
            }
            else
            {
                if (StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_PASSWORD)) || StringHelper::strContains($fieldName, 'password'))
                {
                    $fieldMeta->ModelType = self::CTL_TEXTCOLUMN;
                    $fieldMeta->Generate = 'false';
                }
                elseif (StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_EMAIL)) || StringHelper::strContains($fieldName, 'email'))
                {
                    $fieldMeta->ModelType = self::CTL_TEXTCOLUMN;
                }
                else
                {
                    $fieldMeta->ModelType = self::CTL_TEXTCOLUMN;
                }
            }
        }
        
        $this->metadata->setValue(self::META_JOINS, $joinMetas);
        $this->metadata->setValue(self::META_FIELDS, $fieldMetas);
    }
    
    /**
     * Returns PHP code for the current metadata.
     * @return string
     */
    public function generateCode()
    {
        $sourceClassName = $this->source->getType()->getName();
        $version         = date('Y.m.d.H.i.s');
        $fieldsMeta      = $this->metadata->getValue(self::META_FIELDS);
        $fieldsMeta->objectSort('DisplayOrder');
        $modelCaption        = StringHelper::toWords($this->source->getType()->getName());
        $defaultInstanceName = strtolower($this->widgetClassName[0]) . substr($this->widgetClassName, 1);
        /**
         * EntityField
         */
        $mainPKFieldMeta = null;
        
        // Find the main primary key within the grid
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->IsPriKey === 'true')
            {
                $mainPKFieldMeta = $fieldMeta;
                break;
            }
        }
        
        $code = "/**\r\n";
        $code .= " * {$this->widgetClassName} represents a Model-View pair to list '{$sourceClassName}' entities within a grid.\r\n";
        $code .= " * @package {$this->optionPackageName}\r\n";
        $code .= " * @subpackage {$this->optionSubpackageName}\r\n";
        $code .= " * @version {$version}\r\n";
        $code .= " * @author {$this->optionAuthorName} <{$this->optionAuthorEmail}>\r\n";
        $code .= " */\r\n";
        $code .= "class {$this->widgetClassName} extends WidgetBase\r\n";
        $code .= "{\r\n";
        $code .= "    // Protected control model declarations\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            $objectName = StringHelper::toUcFirst($fieldMeta->BindingName, false, false);
            $code .= "    protected \$ctl{$objectName};\r\n";
        }
        
        // The initializeComponent function
        $code .= "\r\n";
        $code .= "    /**\r\n     * Initializes control models and plugs them into the model.\r\n     */\r\n";
        $code .= "    protected function initializeComponent()\r\n";
        $code .= "    {\r\n";
        $code .= "        //Control instantiation\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            $objectName = StringHelper::toUcFirst($fieldMeta->BindingName, false, false);
            if ($fieldMeta->ModelType == self::CTL_DATETIMECOLUMN)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}(\r\n";
                $code .= "            '{$fieldMeta->BindingName}', '{$fieldMeta->Caption}', '', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_GROUPINGCOULUMN)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}(\r\n";
                $code .= "            '{$fieldMeta->BindingName}', '{$fieldMeta->Caption}', GridState::GRID_SORT_ASCENDING, '{$fieldMeta->TableName}.{$fieldMeta->FieldName}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_MONEYCOLUMN)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}(\r\n";
                $code .= "            '{$fieldMeta->BindingName}', '{$fieldMeta->Caption}', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_NUMBERCOLUMN)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}(\r\n";
                $code .= "            '{$fieldMeta->BindingName}', '{$fieldMeta->Caption}', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_TEXTCOLUMN)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}(\r\n";
                $code .= "            '{$fieldMeta->BindingName}', '{$fieldMeta->Caption}', 100, '{$fieldMeta->TableName}.{$fieldMeta->FieldName}', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}');\r\n";
            }
            else
            {
                $code .= "        // @todo The following line tries to create a control model this scaffolder was not designed for. Please update as necessary.\r\n";
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->BindingName}', '{$fieldMeta->Caption}', '{$fieldMeta->TableName}.{$fieldMeta->FieldName}');\r\n";
            }
        }
        
        $code .= "\r\n        // Root model instantiation\r\n";
        $code .= "        \$this->model = new {$this->modelTypeName}('{$defaultInstanceName}Model', '{$modelCaption}');\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            $objectName = StringHelper::toUcFirst($fieldMeta->BindingName, false, false);
            $code .= "        \$this->model->addColumn(\$this->ctl{$objectName});\r\n";
        }
        
        if (!is_null($mainPKFieldMeta))
        {
            $code .= "\r\n";
            if ($this->getOptDetailsCommand() === true)
                $code .= "        \$this->model->addColumn(new DetailsCommandGridColumn('detailsCommandColumn', 'detailsCommandColumn_Click', '{$mainPKFieldMeta->BindingName}'));\r\n";
                
            if ($this->getOptEditCommand() === true)
                $code .= "        \$this->model->addColumn(new EditCommandGridColumn('editCommandColumn', 'editCommandColumn_Click', '{$mainPKFieldMeta->BindingName}'));\r\n";
                
            if ($this->getOptDeleteCommand() === true)
                $code .= "        \$this->model->addColumn(new DeleteCommandGridColumn('deleteCommandColumn', 'deleteCommandColumn_Click', '{$mainPKFieldMeta->BindingName}'));\r\n";
                
            if ($this->getOptSelectCommand() === true)
                $code .= "        \$this->model->addColumn(new SelectCommandGridColumn('selectCommandcolumn', 'deleteCommandColumn_Click', '{$mainPKFieldMeta->BindingName}'));\r\n";  
        }

        $code .= "\r\n        // Paging and Sorting options\r\n";
        if (!is_null($mainPKFieldMeta))
            $code .= "        \$this->model->setDefaultSort('{$mainPKFieldMeta->BindingName}', GridState::GRID_SORT_DESCENDING);\r\n";
        $code .= "        \$this->model->setPageSize(20);\r\n\r\n";

        if ($this->getOptPrintPreview() === true)
        {
            $code .= "\r\n        // Grid Print Preview\r\n";
            $code .= "        \$gridPrinter = new GridPrintEventManager('gridPrinter');\r\n";
            $code .= "        \$this->model->getChildren()->addControl(\$gridPrinter);\r\n";
        }
        
        if ($this->getOptCsv() === true)
        {
            $code .= "\r\n        // CSV Data Exporter\r\n";
            $code .= "        \$gridCsvExporter = new GridCsvExporterEventManager('gridCsvExporter');\r\n";
            $code .= "        \$this->model->getChildren()->addControl(\$gridCsvExporter);\r\n";
        }
        
        if ($this->getOptExcel2007() === true)
        {
            $code .= "\r\n        // Grid Excel 2007 Exporter\r\n";
            $code .= "        \$gridOxmlExporter = new GridOxmlExporterEventManager('gridOxmlExporter');\r\n";
            $code .= "        \$this->model->getChildren()->addControl(\$gridOxmlExporter);\r\n";
        }
        
        if ($this->getOptExcelBiff() === true)
        {
            $code .= "\r\n        // Grid Excel 97-2003 Exporter\r\n";
            $code .= "        \$gridBiffExporter = new GridBiffExporterEventManager('gridBiffExporter');\r\n";
            $code .= "        \$this->model->getChildren()->addControl(\$gridBiffExporter);\r\n";
        }
        
        $code .= "\r\n        // View instantiation\r\n";
        $code .= "        \$this->view = new {$this->viewTypeName}(\$this->model);\r\n";
        $code .= "        \$this->view->setIsAsynchronous(true);\r\n";
        $code .= "    }\r\n\r\n";
        
        // The constructor
        $code .= "    /**\r\n     * Creates a new instance of this class.\r\n     */\r\n";
        $code .= "    public function __construct(\$name = '{$defaultInstanceName}')\r\n";
        $code .= "    {\r\n";
        $code .= "        parent::__construct(\$name);\r\n";
        $code .= "        \$this->initializeComponent();\r\n";
        $code .= "        \$this->registerEventHandlers();\r\n";
        $code .= "    }\r\n\r\n";
        
        // The serialization constructor
        $code .= "    /**\r\n     * Creates a default instance of this class.\r\n";
        $code .= "     * @return {$this->widgetClassName}\r\n     */\r\n";
        $code .= "    public static function createInstance()\r\n";
        $code .= "    {\r\n";
        $code .= "        return new {$this->widgetClassName}();\r\n";
        $code .= "    }\r\n\r\n";
        
        // The registerEventHandlers function
        $code .= "    /**\r\n     * Registers event handlers for controls.\r\n";
        $code .= "     */\r\n";
        $code .= "    protected function registerEventHandlers()\r\n";
        $code .= "    {\r\n";
        if (!is_null($mainPKFieldMeta))
        {
            if ($this->getOptDetailsCommand() === true)
                $code .= "        Controller::registerEventHandler('detailsCommandColumn_Click', array(__CLASS__, 'detailsCommandColumn_Clicked'));\r\n";
                
            if ($this->getOptEditCommand() === true)
                $code .= "        Controller::registerEventHandler('editCommandColumn_Click', array(__CLASS__, 'editCommandColumn_Clicked'));\r\n";
                
            if ($this->getOptDeleteCommand() === true)
                $code .= "        Controller::registerEventHandler('deleteCommandColumn_Click', array(__CLASS__, 'deleteCommandColumn_Clicked'));\r\n";
                
            if ($this->getOptSelectCommand() === true)
                $code .= "        Controller::registerEventHandler('selectCommandColumn_Click', array(__CLASS__, 'selectCommandColumn_Click'));\r\n";
        }
        $code .= "    }\r\n\r\n";
        
        // The control accessor functions
        if ($this->getOptControlAccessors() === true)
        {
            foreach ($fieldsMeta as $fieldMeta)
            {
                if ($fieldMeta->Generate !== 'true')
                    continue;
                $objectName = StringHelper::toUcFirst($fieldMeta->BindingName, false, false);
                $code .= "    /**\r\n     * Provides direct access to the '{$fieldMeta->BindingName}' control.\r\n";
                $code .= "     * @return {$fieldMeta->ModelType}\r\n     */\r\n";
                $code .= "    public function &getCtl{$objectName}()\r\n";
                $code .= "    {\r\n";
                $code .= "        return \$this->ctl{$objectName};\r\n";
                $code .= "    }\r\n\r\n";
            }
        }
        
        // The getModel function
        $code .= "    /**\r\n     * Gets this widget's associated model object.\r\n";
        $code .= "     * @return {$this->modelTypeName}\r\n     */\r\n";
        $code .= "    public function &getModel()\r\n";
        $code .= "    {\r\n";
        $code .= "        return \$this->model;\r\n";
        $code .= "    }\r\n\r\n";
        
        // The getView function
        $code .= "    /**\r\n     * Gets this widget's associated view object.\r\n";
        $code .= "     * @return {$this->viewTypeName}\r\n     */\r\n";
        $code .= "    public function &getView()\r\n";
        $code .= "    {\r\n";
        $code .= "        return \$this->view;\r\n";
        $code .= "    }\r\n\r\n";
        
        // The getDataSource function
        $mainEntityName = $this->source->getType()->getName();
        $code .= "    /**\r\n     * Gets this widget's associated data source as a data adapter.\r\n";
        $code .= "     * @return DataTableAdapterBase\r\n     */\r\n";
        $code .= "    public static function &getDataSource()\r\n";
        $code .= "    {\r\n";
        $code .= "        \$dataSource = DataContext::getInstance()->getAdapter('{$mainEntityName}');\r\n";
        $code .= "        \$dataSource\r\n";
        foreach ($this->getMetadata()->getItem(self::META_JOINS) as $join)
        {
            $code .= "            ->joinRelated('{$join}')\r\n";
        }
        
        $lastIndex = 0;
        for ($i = $fieldsMeta->getCount() - 1; $i >= 0; $i--)
        {
            $fieldMeta = $fieldsMeta->getItem($i);
            if ($fieldMeta->Generate === 'true')
            {
                $lastIndex = $i;
                break;
            }
        }
        
        $ci = -1;
        foreach ($fieldsMeta as $fieldMeta)
        {
            $ci++;
            if ($fieldMeta->Generate !== 'true')
                continue;
            
            $code .= "            ->addField('{$fieldMeta->TableName}', '{$fieldMeta->FieldName}', '{$fieldMeta->BindingName}')";
            if ($ci === $lastIndex)
            {
                $code .= ";\r\n";
            }
            else
            {
                $code .= "\r\n";
            }
        }
        
        $code .= "        return \$dataSource;\r\n";
        $code .= "    }\r\n\r\n";
        
        // The handlePostback function
        $code .= "    /**\r\n     * Handles the request data.\r\n";
        $code .= "     * @return int The number of events triggered by the view.\r\n     */\r\n";
        $code .= "    public function handleRequest()\r\n";
        $code .= "    {\r\n";
        $code .= "        \$dataSource = self::getDataSource();\r\n";
        $code .= "        \$handledEvents = 0;\r\n";
        $code .= "        if (Controller::isPostBack(\$this->model) === false)\r\n";
        $code .= "        {\r\n";
        $code .= "            \$this->model->dataBind(\$dataSource);\r\n";
        $code .= "        }\r\n";
        $code .= "        else\r\n";
        $code .= "        {\r\n";
        $code .= "            \$handledEvents = Controller::handleEvents(\$this->model);\r\n";
        $code .= "            \$this->model->dataBind(\$dataSource);\r\n";
        $code .= "            if (\$this->view->getIsAsynchronous())\r\n";
        $code .= "            {\r\n";
        $code .= "                \$this->view->render();\r\n";
        $code .= "                HttpResponse::end();\r\n";
        $code .= "            }\r\n";
        $code .= "        }\r\n\r\n";
        $code .= "        return \$handledEvents;\r\n";
        $code .= "    }\r\n\r\n";
        
        // The event handlers for command columns
        if (!is_null($mainPKFieldMeta))
        {
            
            if ($this->getOptDetailsCommand() === true)
            {
                $code .= "    /**\r\n     * Handles the detailsCommandColumn_Click event.\r\n";
                $code .= "     * @param {$this->modelTypeName} \$sender\r\n";
                $code .= "     * @param ControllerEvent \$event\r\n";
                $code .= "     */\r\n";
                $code .= "    public static function detailsCommandColumn_Clicked(&\$sender, &\$event)\r\n";
                $code .= "    {\r\n";
                $code .= "        // @todo Add custom code below. (Typycally, Controller::transfer logic)\r\n";
                $code .= "        \$requestUrl = HttpContextInfo::getInstance()->getRequestScriptPath();\r\n";
                $code .= "        if (StringHelper::endsWith(\$requestUrl, '.index.php'))\r\n";
                $code .= "        {\r\n";
                $code .= "            \$targetUrl = StringHelper::replaceEnd(\$requestUrl, '.index.php', '.details.php');\r\n";
                $code .= "            Controller::transfer(\$targetUrl . '?{$mainPKFieldMeta->FieldName}=' . urlencode(\$event->getValue()));\r\n";
                $code .= "        }\r\n\r\n";
                $code .= "        \$sender->setMessage('Details command for item ' . \$event->getValue());\r\n";
                $code .= "    }\r\n\r\n";
            }
            
            if ($this->getOptEditCommand() === true)
            {
                $code .= "    /**\r\n     * Handles the editCommandColumn_Click event.\r\n";
                $code .= "     * @param {$this->modelTypeName} \$sender\r\n";
                $code .= "     * @param ControllerEvent \$event\r\n";
                $code .= "     */\r\n";
                $code .= "    public static function editCommandColumn_Clicked(&\$sender, &\$event)\r\n";
                $code .= "    {\r\n";
                $code .= "        // @todo Add custom code below. (Typycally, Controller::transfer logic)\r\n";
                $code .= "        \$requestUrl = HttpContextInfo::getInstance()->getRequestScriptPath();\r\n";
                $code .= "        if (StringHelper::endsWith(\$requestUrl, '.index.php'))\r\n";
                $code .= "        {\r\n";
                $code .= "            \$targetUrl = StringHelper::replaceEnd(\$requestUrl, '.index.php', '.edit.php');\r\n";
                $code .= "            Controller::transfer(\$targetUrl, array('{$mainPKFieldMeta->FieldName}' => \$event->getValue()));\r\n";
                $code .= "        }\r\n\r\n";
                $code .= "        \$sender->setMessage('Edit command for item ' . \$event->getValue());\r\n";
                $code .= "    }\r\n\r\n";
            }
            
            if ($this->getOptDeleteCommand() === true)
            {
                $code .= "    /**\r\n     * Handles the deleteCommandColumn_Click event.\r\n";
                $code .= "     * @param {$this->modelTypeName} \$sender\r\n";
                $code .= "     * @param ControllerEvent \$event\r\n";
                $code .= "     */\r\n";
                $code .= "    public static function deleteCommandColumn_Clicked(&\$sender, &\$event)\r\n";
                $code .= "    {\r\n";
                $code .= "        // @todo Add custom code below. (Typycally, database delete logic)\r\n";
                $code .= "        try\r\n";
                $code .= "        {\r\n";
                $code .= "            \$adapter = DataContext::getInstance()->getAdapter('{$mainEntityName}');\r\n";
                $code .= "            \$entity = \$adapter->single(\$event->GetValue());\r\n";
                $code .= "            \$adapter->delete(\$entity);\r\n";
                $code .= "            \$sender->getState()->resetRecordCount();\r\n";
                $code .= "        }\r\n";
                $code .= "        catch (Exception \$ex)\r\n";
                $code .= "        {\r\n";
                $code .= "            \$sender->setErrorMessage('Error ' . \$ex->getCode() . ': ' . \$ex->getMessage());\r\n";
                $code .= "        }\r\n";
                $code .= "    }\r\n\r\n";
            }
            
            if ($this->getOptSelectCommand() === true)
            {
                $code .= "    /**\r\n     * Handles the selectCommandColumn_Click event.\r\n";
                $code .= "     * @param {$this->modelTypeName} \$sender\r\n";
                $code .= "     * @param ControllerEvent \$event\r\n";
                $code .= "     */\r\n";
                $code .= "    public static function selectCommandColumn_Clicked(&\$sender, &\$event)\r\n";
                $code .= "    {\r\n";
                $code .= "        // @todo Add custom code below.\r\n";
                $code .= "        \$sender->setMessage('Select command for item ' . \$event->getValue());\r\n";
                $code .= "    }\r\n\r\n";
            }
        }
        
        $code .= "}\r\n\r\n";
        return $code;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return GridWidgetScaffolder
     */
    public static function createInstance()
    {
        return new GridWidgetScaffolder();
    }
}

/**
 * Represents an implementation of the Form Widget Scaffolder
 * @package WebCore
 * @subpackage Scaffoling
 */
class FormWidgetScaffolder extends WidgetScaffolderBase
{
    const META_FIELDS = 'fields';
    
    const CTL_AUTO_CREATED_DT = 'Auto:Created.DateTime';
    const CTL_AUTO_UPDATED_DT = 'Auto:Updated.DateTime';
    const CTL_AUTO_FILESIZE = 'Auto:File.Size';
    const CTL_AUTO_FILEMIME = 'Auto:File.Mime';
    const CTL_AUTO_FILENAME = 'Auto:File.Name';
    const CTL_AUTO_CREATED_USR = 'Auto:Created.UserId';
    const CTL_AUTO_UPDATED_USR = 'Auto:Updated.UserId';
    const CTL_CHECKBOX = 'CheckBox';
    const CTL_COMBOBOX = 'ComboBox';
    const CTL_DATEFIELD = 'DateField';
    const CTL_DATETIMEFIELD = 'DateTimeField';
    const CTL_DECIMALFIELD = 'DecimalField';
    const CTL_EMAILFIELD = 'EmailField';
    const CTL_FILEFIELD = 'FileField';
    const CTL_INTEGERFIELD = 'IntegerField';
    const CTL_MONEYFIELD = 'MoneyField';
    const CTL_PASSWORDFIELD = 'PasswordField';
    const CTL_PERSISTOR = 'Persistor';
    const CTL_TEXTAREA = 'TextArea';
    const CTL_TEXTFIELD = 'TextField';
    const CTL_TIMEFIELD = 'TimeField';
    
    public function __construct($viewTypeName = 'HtmlFormView')
    {
        parent::__construct();
        $this->source        = null;
        $this->modelTypeName = 'Form';
        $this->viewTypeName  = $viewTypeName;
        $this->metadata      = new KeyedCollection();
        $fields              = new IndexedCollection();
        $this->metadata->setValue(self::META_FIELDS, $fields);
    }
    
    /**
     * Generates the default field metadata for the entity this scaffolder represents.
     * @return void
     */
    protected function generateMetadata()
    {
        if (ObjectIntrospector::isExtending($this->source, 'EntityBase') !== true)
        {
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Scaffolder only supports an EntityBase-derived source.');
        }
        
        $this->metadata        = new KeyedCollection();
        $this->widgetClassName = $this->source->getType()->getName() . 'FormWidget';
        
        $fields = new IndexedCollection();
        $pks    = $this->source->getPrimaryKeyFieldNames();
        
        $currentIndex = 0;
        /**
         * @var EntityField
         */
        $fieldObject  = null;
        
        $fileMetas = new IndexedCollection();
        
        foreach ($this->source->getFields() as $fieldName => $fieldObject)
        {
            $currentIndex++;
            $fieldMeta = new Popo();
            $fieldMeta->addFieldValue('DisplayOrder', $currentIndex * 10);
            $fieldMeta->addFieldValue('EntityFieldName', $fieldName);
            $fieldMeta->addFieldValue('Caption', StringHelper::toWords($fieldName));
            $fieldMeta->addFieldValue('IsRequired', ($fieldObject->getIsNullable() == false) ? 'true' : 'false');
            $fieldMeta->addFieldValue('HelpString', '');
            $fieldMeta->addFieldValue('TabName', '_default');
            $fieldMeta->addFieldValue('SectionName', '_default');
            $fieldMeta->addFieldValue('ModelType', 'TextField');
            $fieldMeta->addFieldValue('ForeignEntityName', '');
            $fieldMeta->addFieldValue('ForeignKeyName', '');
            $fieldMeta->addFieldValue('ForeignDisplayName', '');
            $fieldMeta->addFieldValue('Generate', 'true');
            
            if ($pks->containsValue($fieldName))
            {
                // hidden fields here
                $fieldMeta->ModelType  = self::CTL_PERSISTOR;
                $fieldMeta->IsRequired = 'true';
                $fields->addItem($fieldMeta);
                continue;
            }
            
            if (ObjectIntrospector::isA($fieldObject, 'EntityField'))
            {
                $fieldMeta->ModelType = self::CTL_TEXTFIELD;
                $fieldDataType        = $fieldObject->getDbDataType();
                
                $sourceRelations = call_user_func(array(
                    get_class($this->source),
                    'getEntityRelations'
                ));
                $sourceRelation  = null;
                foreach ($sourceRelations as $rel)
                {
                    if ($rel->getLocalFieldName() === $fieldName)
                    {
                        $sourceRelation = $rel;
                        break;
                    }
                }
                
                // For non-related entities
                if (is_null($sourceRelation))
                {
                    if (StringHelper::strContains($fieldDataType, 'date'))
                    {
                        if (StringHelper::strContains($fieldName, 'crea') || StringHelper::strContains($fieldName, 'ins'))
                        {
                            $fieldMeta->ModelType = self::CTL_AUTO_CREATED_DT;
                        }
                        elseif (StringHelper::strContains($fieldName, 'upda') || StringHelper::strContains($fieldName, 'mod'))
                        {
                            $fieldMeta->ModelType = self::CTL_AUTO_UPDATED_DT;
                        }
                        elseif (StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_TIME)) || StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_HOUR)))
                        {
                            $fieldMeta->ModelType = self::CTL_TIMEFIELD;
                        }
                        else
                        {
                            $fieldMeta->ModelType = self::CTL_DATEFIELD;
                        }
                    }
                    elseif ($fieldDataType == 'timespan')
                    {
                        $fieldMeta->ModelType = self::CTL_TIMEFIELD;
                    }
                    elseif ($fieldObject->getDbDataType() == 'tinyint' || $fieldObject->getDbDataType() == 'bit')
                    {
                        $fieldMeta->ModelType = self::CTL_CHECKBOX;
                    }
                    elseif (StringHelper::strContains($fieldObject->getDbDataType(), 'int'))
                    {
                        $fieldMeta->ModelType = self::CTL_INTEGERFIELD;
                    }
                    elseif (StringHelper::strContains($fieldObject->getDbDataType(),'float') ||
                            StringHelper::strContains($fieldObject->getDbDataType(),'numeric') ||
                            StringHelper::strContains($fieldObject->getDbDataType(), 'double'))
                    {
                        $fieldMeta->ModelType = self::CTL_DECIMALFIELD;
                    }
                    elseif (StringHelper::strContains($fieldObject->getDbDataType(), 'decimal') ||
                            StringHelper::strContains($fieldObject->getDbDataType(), 'money'))
                    {
                        $fieldMeta->ModelType = self::CTL_MONEYFIELD;
                    }
                    elseif (StringHelper::strContains($fieldObject->getDbDataType(), 'text'))
                    {
                        $fieldMeta->ModelType = self::CTL_TEXTAREA;
                    }
                    else
                    {
                        if (StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_PASSWORD)) || StringHelper::strContains($fieldName, 'password'))
                        {
                            $fieldMeta->ModelType = self::CTL_PASSWORDFIELD;
                        }
                        elseif (StringHelper::strContains($fieldName, Resources::getValue(Resources::SRK_CAPTION_EMAIL)) || StringHelper::strContains($fieldName, 'email'))
                        {
                            $fieldMeta->ModelType = self::CTL_EMAILFIELD;
                        }
                        else
                        {
                            $fieldMeta->ModelType = self::CTL_TEXTFIELD;
                        }
                    }
                }
                // For entities with relations
                else
                {
                    $fieldMeta->ModelType         = self::CTL_COMBOBOX;
                    $fieldMeta->ForeignEntityName = $sourceRelation->getEntityTypeName();
                    $fieldMeta->ForeignKeyName    = $sourceRelation->getForeignFieldName();
                    $foreignDisplayField          = $sourceRelation->getEntity()->getFirstTextEntityField();
                    if (is_null($foreignDisplayField))
                    {
                        $fieldMeta->ForeignDisplayName = $fieldMeta->ForeignKeyName;
                    }
                    else
                    {
                        $fieldMeta->ForeignDisplayName = $foreignDisplayField->getFieldName();
                    }
                    
                    $settings = Settings::getValue(Settings::SKEY_SECURITY);
                    if (strtolower($fieldMeta->ForeignEntityName) == strtolower($settings[Settings::KEY_SECURITY_USERENTITY]))
                    {
                        if (StringHelper::strContains($fieldName, 'crea') || StringHelper::strContains($fieldName, 'ins'))
                        {
                            $fieldMeta->ModelType = FormWidgetScaffolder::CTL_AUTO_CREATED_USR;
                        }
                        else
                        {
                            $fieldMeta->ModelType = FormWidgetScaffolder::CTL_AUTO_UPDATED_USR;
                        }
                    }
                    
                }
            }
            else
            {
                $fieldMeta->ModelType = self::CTL_FILEFIELD;
                $fileMetas->addItem($fieldMeta);
            }
            
            $fields->addItem($fieldMeta);
        }
        
        // Do a second pass for file fields auto assignments
        foreach ($fileMetas as $fileField)
        {
            foreach ($fields as $field)
            {
                if ($field->EntityFieldName == $fileField->EntityFieldName)
                    continue;
                $fileBaseName = explode('_', $fileField->EntityFieldName);
                if (StringHelper::beginsWith($field->EntityFieldName, $fileBaseName[0]))
                {
                    if (StringHelper::strContains($field->EntityFieldName, 'mime') || StringHelper::strContains($field->EntityFieldName, 'type'))
                    {
                        $field->ModelType = self::CTL_AUTO_FILEMIME;
                    }
                    elseif (StringHelper::strContains($field->EntityFieldName, 'name'))
                    {
                        $field->ModelType = self::CTL_AUTO_FILENAME;
                    }
                    elseif (StringHelper::strContains($field->EntityFieldName, 'length') || StringHelper::strContains($field->EntityFieldName, 'size'))
                    {
                        $field->ModelType = self::CTL_AUTO_FILESIZE;
                    }
                    else
                    {
                        continue;
                    }
                    $field->addFieldValue('FileField', $fileField->EntityFieldName);
                }
            }
        }
        
        $this->metadata->setValue(self::META_FIELDS, $fields);
    }
    
    /**
     * Returns PHP code for the current metadata.
     * @return string
     */
    public function generateCode()
    {
        $sourceClassName = $this->source->getType()->getName();
        $version         = date('Y.m.d.H.i.s');
        $fieldsMeta      = $this->metadata->getValue(self::META_FIELDS);
        $fieldsMeta->objectSort('DisplayOrder');
        $modelCaption        = $this->source->getType()->getName();
        $defaultInstanceName = strtolower($this->widgetClassName[0]) . substr($this->widgetClassName, 1);
        
        $code = "/**\r\n";
        $code .= " * {$this->widgetClassName} represents a Model-View pair to perform '{$sourceClassName}' entity create and update operations.\r\n";
        $code .= " * @package {$this->optionPackageName}\r\n";
        $code .= " * @subpackage {$this->optionSubpackageName}\r\n";
        $code .= " * @version {$version}\r\n";
        $code .= " * @author {$this->optionAuthorName} <{$this->optionAuthorEmail}>\r\n";
        $code .= " */\r\n";
        $code .= "class {$this->widgetClassName} extends WidgetBase\r\n";
        $code .= "{\r\n";
        $code .= "    // Protected control model declarations\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            if (StringHelper::beginsWith($fieldMeta->ModelType, 'Auto:'))
                continue;
            $objectName = StringHelper::toUcFirst($fieldMeta->EntityFieldName, false, false);
            $code .= "    protected \$ctl{$objectName};\r\n";
        }
        
        // The initializeComponent() function
        $code .= "\r\n";
        $code .= "    /**\r\n     * Initializes control models and plugs them into the model.\r\n     */\r\n";
        $code .= "    protected function initializeComponent()\r\n";
        $code .= "    {\r\n";
        $code .= "        //Control instantiation\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            $objectName = StringHelper::toUcFirst($fieldMeta->EntityFieldName, false, false);
            if ($fieldMeta->ModelType == self::CTL_PERSISTOR)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_TIMEFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', date('H:i:s'), {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_DATEFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', date('Y-m-d'), {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_DATETIMEFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', date('Y-m-d H:i:s'), {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_CHECKBOX)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '0', '1', '0', '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_INTEGERFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '0', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_DECIMALFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '0', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_MONEYFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '0', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_TEXTFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_TEXTAREA)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_PASSWORDFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_EMAILFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_COMBOBOX)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '', '', '', '{$fieldMeta->HelpString}');\r\n";
                $code .= "        // @todo Currently, the where clause selects the first 100 records. Please update if necessary.\r\n";
                $code .= "        \$this->ctl{$objectName}->addOptions(\r\n            DataContext::getInstance()->getAdapter('{$fieldMeta->ForeignEntityName}')->take(100)->select(), '{$fieldMeta->ForeignKeyName}', '{$fieldMeta->ForeignDisplayName}'); \r\n";
            }
            elseif ($fieldMeta->ModelType == self::CTL_FILEFIELD)
            {
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '', '{$fieldMeta->EntityFieldName}_Upload', '1', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
            elseif (StringHelper::beginsWith($fieldMeta->ModelType, 'Auto:', false))
            {
                // Skip. Auto fields are set later.
            }
            else
            {
                $code .= "        // @todo The following line tries to create a control model this scaffolder was not designed for. Please update as necessary.\r\n";
                $code .= "        \$this->ctl{$objectName} = new {$fieldMeta->ModelType}('{$fieldMeta->EntityFieldName}', '{$fieldMeta->Caption}', '', {$fieldMeta->IsRequired}, '{$fieldMeta->HelpString}');\r\n";
            }
        }
        $code .= "\r\n        // Root model instantiation\r\n";
        $code .= "        \$this->model = new {$this->modelTypeName}('{$defaultInstanceName}Model', '{$modelCaption}');\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            if (StringHelper::beginsWith($fieldMeta->ModelType, 'Auto:'))
                continue;
            $objectName = StringHelper::toUcFirst($fieldMeta->EntityFieldName, false, false);
            $code .= "        \$this->model->getChildren()->addControl(\$this->ctl{$objectName});\r\n";
        }
        $code .= "\r\n        // Default buttons\r\n";
        $code .= "        \$this->model->addButton(new Button('buttonSubmit', Resources::getValue(Resources::SRK_SEARCH_DIALOG_OK), 'buttonSubmit_Click'));\r\n";
        $code .= "        \$this->model->addButton(new Button('buttonCancel', Resources::getValue(Resources::SRK_SEARCH_DIALOG_CANCEL), 'buttonCancel_Click'));\r\n";
        $code .= "\r\n        // View instantiation\r\n";
        $code .= "        \$this->view = new {$this->viewTypeName}(\$this->model);\r\n";
        $code .= "        \$this->view->setIsAsynchronous(true);\r\n";
        $code .= "    }\r\n\r\n";
        
        // The constructor
        $code .= "    /**\r\n     * Creates a new instance of this class.\r\n     */\r\n";
        $code .= "    public function __construct(\$name = '{$defaultInstanceName}')\r\n";
        $code .= "    {\r\n";
        $code .= "        parent::__construct(\$name);\r\n";
        $code .= "        \$this->initializeComponent();\r\n";
        $code .= "        \$this->registerEventHandlers();\r\n";
        $code .= "    }\r\n\r\n";
        
        // The serialization constructor
        $code .= "    /**\r\n     * Creates a default instance of this class.\r\n";
        $code .= "     * @return {$this->widgetClassName}\r\n     */\r\n";
        $code .= "    public static function createInstance()\r\n";
        $code .= "    {\r\n";
        $code .= "        return new {$this->widgetClassName}();\r\n";
        $code .= "    }\r\n\r\n";
        
        // The registerEventHandlers function
        $code .= "    /**\r\n     * Registers event handlers for controls.\r\n";
        $code .= "     */\r\n";
        $code .= "    protected function registerEventHandlers()\r\n";
        $code .= "    {\r\n";
        $code .= "        Controller::registerEventHandler('buttonSubmit_Click', array(__CLASS__, 'buttonSubmit_Clicked'));\r\n";
        $code .= "        Controller::registerEventHandler('buttonCancel_Click', array(__CLASS__, 'buttonCancel_Clicked'));\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->ModelType === self::CTL_FILEFIELD)
            {
                $code .= "        Controller::registerEventHandler('{$fieldMeta->EntityFieldName}_Upload', array(__CLASS__, '{$fieldMeta->EntityFieldName}_Uploaded'));\r\n";
            }
        }
        $code .= "    }\r\n\r\n";
        
        // The control accessor functions
        if ($this->getOptControlAccessors() === true)
        {
            foreach ($fieldsMeta as $fieldMeta)
            {
                if ($fieldMeta->Generate !== 'true')
                    continue;
                if (StringHelper::beginsWith($fieldMeta->ModelType, 'Auto:'))
                    continue;
                $objectName = StringHelper::toUcFirst($fieldMeta->EntityFieldName, false, false);
                $code .= "    /**\r\n     * Provides direct access to the '{$fieldMeta->EntityFieldName}' control.\r\n";
                $code .= "     * @return {$fieldMeta->ModelType}\r\n     */\r\n";
                $code .= "    public function &getCtl{$objectName}()\r\n";
                $code .= "    {\r\n";
                $code .= "        return \$this->ctl{$objectName};\r\n";
                $code .= "    }\r\n\r\n";
            }
        }
        
        // The getModel function
        $code .= "    /**\r\n     * Gets this widget's associated model object.\r\n";
        $code .= "     * @return {$this->modelTypeName}\r\n     */\r\n";
        $code .= "    public function &getModel()\r\n";
        $code .= "    {\r\n";
        $code .= "        return \$this->model;\r\n";
        $code .= "    }\r\n\r\n";
        
        // The getView function
        $code .= "    /**\r\n     * Gets this widget's associated view object.\r\n";
        $code .= "     * @return {$this->viewTypeName}\r\n     */\r\n";
        $code .= "    public function &getView()\r\n";
        $code .= "    {\r\n";
        $code .= "        return \$this->view;\r\n";
        $code .= "    }\r\n\r\n";
        
        // The handlePostback function
        $code .= "    /**\r\n     * Handles the request data.\r\n";
        $code .= "     * @return int The number of events triggered by the view.\r\n     */\r\n";
        $code .= "    public function handleRequest()\r\n";
        $code .= "    {\r\n";
        $code .= "        \$handledEvents = 0;\r\n";
        $code .= "        if (Controller::isPostBack(\$this->model) === false)\r\n";
        $code .= "        {\r\n";
        $code .= "            \$dataSource = clone HttpContext::getRequest()->getRequestVars();\r\n";
        $code .= "            \$action = new FormPopulateControllerAction(\$this->model, '{$sourceClassName}', \$dataSource);\r\n";
        $code .= "            try\r\n";
        $code .= "            {\r\n";
        $code .= "                \$action->execute();\r\n";
        $code .= "            }\r\n";
        $code .= "            catch (Exception \$ex)\r\n";
        $code .= "            {\r\n";
        $code .= "                \$this->model->setErrorMessage(\$ex->getCode() . ': ' . \$ex->getMessage());\r\n";
        $code .= "            }\r\n";
        $code .= "        }\r\n";
        $code .= "        else\r\n";
        $code .= "        {\r\n";
        $code .= "            \$handledEvents = parent::handleRequest();\r\n";
        $code .= "        }\r\n\r\n";
        $code .= "        return \$handledEvents;\r\n";
        $code .= "    }\r\n\r\n";
        
        // The file upload event handlers (if any)
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            if ($fieldMeta->ModelType === self::CTL_FILEFIELD)
            {
                $code .= "    /**\r\n     * Handles the {$fieldMeta->EntityFieldName} Upload event.\r\n";
                $code .= "     * @param {$this->modelTypeName} \$sender\r\n";
                $code .= "     * @param ControllerEvent \$event\r\n";
                $code .= "     */\r\n";
                $code .= "    public static function {$fieldMeta->EntityFieldName}_Uploaded(&\$sender, &\$event)\r\n";
                $code .= "    {\r\n";
                $code .= "        \$postedFiles = HttpContext::getRequest()->getPostedFiles();\r\n";
                $code .= "        \$sender->dataBind(\$postedFiles);\r\n";
                $code .= "    }\r\n\r\n";
            }
        }
        
        // The buttonSubmit_Clicked function
        $code .= "    /**\r\n     * Handles the buttonSubmit Click event.\r\n";
        $code .= "     * @param {$this->modelTypeName} \$sender\r\n";
        $code .= "     * @param ControllerEvent \$event\r\n";
        $code .= "     */\r\n";
        $code .= "    public static function buttonSubmit_Clicked(&\$sender, &\$event)\r\n";
        $code .= "    {\r\n";
        $code .= "        \$dataSource = clone HttpContext::getRequest()->getRequestVars();\r\n";
        $postedFiles = false;
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            if ($fieldMeta->ModelType === self::CTL_FILEFIELD)
            {
                if ($postedFiles === false)
                {
                    $code .= "        \$postedFiles = HttpContext::getRequest()->getPostedFiles();\r\n\r\n";
                    $postedFiles = true;
                }
                $code .= "        if (\$postedFiles->keyExists('{$fieldMeta->EntityFieldName}'))\r\n";
                $code .= "        {\r\n";
                $code .= "            \$dataSource->setItem('{$fieldMeta->EntityFieldName}', \$postedFiles->getItem('{$fieldMeta->EntityFieldName}'));\r\n";
                
                for ($i = 0; $i < $fieldsMeta->getCount(); $i++)
                {
                    $fileRelMeta = $fieldsMeta->getItem($i);
                    if ($fileRelMeta->hasField('FileField') && $fieldMeta->EntityFieldName === $fileRelMeta->FileField)
                    {
                        if ($fileRelMeta->ModelType === self::CTL_AUTO_FILENAME)
                            $code .= "            \$dataSource->setValue('{$fileRelMeta->EntityFieldName}', \$postedFiles->getItem('{$fieldMeta->EntityFieldName}')->getFileName());\r\n";
                        elseif ($fileRelMeta->ModelType === self::CTL_AUTO_FILEMIME)
                            $code .= "            \$dataSource->setValue('{$fileRelMeta->EntityFieldName}', \$postedFiles->getItem('{$fieldMeta->EntityFieldName}')->getMimeType());\r\n";
                        elseif ($fileRelMeta->ModelType === self::CTL_AUTO_FILESIZE)
                            $code .= "            \$dataSource->setValue('{$fileRelMeta->EntityFieldName}', \$postedFiles->getItem('{$fieldMeta->EntityFieldName}')->getSize());\r\n";
                    }
                }
                
                $code .= "        }\r\n\r\n";
            }
        }
        $code .= "        // @todo If needed, modify or add datasource values.\r\n";
        $code .= "        \$action = new FormSaveControllerAction(\$sender, '{$sourceClassName}', \$dataSource);\r\n";
        $code .= "        \r\n";
        $code .= "        try\r\n";
        $code .= "        {\r\n";
        foreach ($fieldsMeta as $fieldMeta)
        {
            if ($fieldMeta->Generate !== 'true')
                continue;
            if ($fieldMeta->ModelType === self::CTL_AUTO_CREATED_DT)
            {
                $code .= "            if (\$action->getDataMode() === FormSaveControllerAction::MODE_INSERT)\r\n";
                $code .= "                \$action->getDataSource()->setValue('{$fieldMeta->EntityFieldName}', date('Y-m-d H:i:s'));\r\n\r\n";
            }
            elseif ($fieldMeta->ModelType === self::CTL_AUTO_UPDATED_DT)
            {
                $code .= "            if (\$action->getDataMode() === FormSaveControllerAction::MODE_UPDATE)\r\n";
                $code .= "                \$action->getDataSource()->setValue('{$fieldMeta->EntityFieldName}', date('Y-m-d H:i:s'));\r\n\r\n";
            }
            elseif ($fieldMeta->ModelType === self::CTL_AUTO_CREATED_USR)
            {
                $code .= "            if (\$action->getDataMode() === FormSaveControllerAction::MODE_INSERT)\r\n";
                $code .= "            {\r\n";
                $code .= "                if (is_null(HttpContext::getUser()))\r\n";
                $code .= "                    throw new SystemException(SystemException::EX_MEMBERSHIPUSER, 'You must be logged in to perform this action.');\r\n";
                $code .= "                \$action->getDataSource()->setValue('{$fieldMeta->EntityFieldName}', HttpContext::getUser()->getUserId());\r\n";
                $code .= "            }\r\n\r\n";
            }
            elseif ($fieldMeta->ModelType === self::CTL_AUTO_UPDATED_USR)
            {
                $code .= "            if (\$action->getDataMode() === FormSaveControllerAction::MODE_UPDATE)\r\n";
                $code .= "            {\r\n";
                $code .= "                if (is_null(HttpContext::getUser())) throw new Exception('No user context.');\r\n";
                $code .= "                \$action->getDataSource()->setValue('{$fieldMeta->EntityFieldName}', HttpContext::getUser()->getUserId());\r\n";
                $code .= "            }\r\n\r\n";
            }
        }
        $code .= "            if (\$action->execute())\r\n";
        $code .= "            {\r\n";
        $code .= "                \$navigationEntry = HttpContext::getNavigationHistory()->getPreviousItem();\r\n";
        $code .= "                if (!is_null(\$navigationEntry))\r\n";
        $code .= "                    \$navigationEntry->transfer();\r\n";
        $code .= "                else\r\n";
        $code .= "                    Controller::transfer(HttpContext::getApplicationRoot() . 'index.php');\r\n";
        $code .= "            }\r\n";
        $code .= "        }\r\n";
        $code .= "        catch (Exception \$ex)\r\n";
        $code .= "        {\r\n";
        $code .= "            \$sender->setErrorMessage(\$ex->getCode() . ': ' . \$ex->getMessage());\r\n";
        $code .= "        }\r\n";
        $code .= "    }\r\n\r\n";
        
        // The buttonCancel_Clicked function
        $code .= "    /**\r\n     * Handles the buttonCancel Click event.\r\n";
        $code .= "     * @param {$this->modelTypeName} \$sender\r\n";
        $code .= "     * @param ControllerEvent \$event\r\n";
        $code .= "     */\r\n";
        $code .= "    public static function buttonCancel_Clicked(&\$sender, &\$event)\r\n";
        $code .= "    {\r\n";
        $code .= "        \$navigationEntry = HttpContext::getNavigationHistory()->getPreviousItem();\r\n";
        $code .= "        if (!is_null(\$navigationEntry))\r\n";
        $code .= "            \$navigationEntry->transfer();\r\n";
        $code .= "        else\r\n";
        $code .= "            Controller::transfer(HttpContext::getApplicationRoot() . 'index.php');\r\n";
        $code .= "    }\r\n\r\n";
        
        $code .= "}\r\n\r\n";
        return $code;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return FormWidgetScaffolder
     */
    public static function createInstance()
    {
        return new FormWidgetScaffolder();
    }
}
?>