<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides models of controls in a data grid.
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.model.repeater.php";

/**
 * Provides the most basic implementation of a bound grid column model.
 * By default, the column will be sortable and exportable. The dault search mode is 'LITERAL'
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class BoundGridColumnBase extends BoundRepeaterFieldModelBase
{
    protected $isSortable;
    protected $isExportable;
    protected $sortExpression;
    protected $searchMemberExpression;
    protected $searchDialog;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption, $name);
        $this->setIsSortable(true);
        $this->setIsExportable(true);
        $this->setSortExpression($this->bindingMemberName);
    }
    
    /**
     * Gets whether the column is searchable based on whether it contains a searchDialog.
     * @return bool
     */
    public function getIsSearchable()
    {
        return (!is_null($this->searchDialog) && $this->searchMemberExpression != '');
    }
    
    /**
     * Ses the search dialog associaed with this column
     *
     * @param GridSearchDialogBase $value
     */
    public function setSearchDialog(&$value)
    {
        if (ObjectIntrospector::isExtending($value, 'GridSearchDialogBase') === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter 'vaue' must be an instance of a GridSearchDialogBase-derived class.");
        
        $this->searchDialog = $value;
    }
    
    /**
     * Gets the search dialog associated with this column
     *
     * @return GridSearchDialogBase
     */
    public function &getSearchDialog()
    {
        return $this->searchDialog;
    }
    
    /**
     * Gets whether the column is sortable
     *
     * @return bool
     */
    public function getIsSortable()
    {
        return $this->isSortable;
    }
    
    /**
     * Sets whether the column is sortable.
     *
     * @param bool $value
     */
    public function setIsSortable($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->isSortable = $value;
    }
    
    /**
     * Gets whether the column is exportable
     *
     * @return bool
     */
    public function getIsExportable()
    {
        return $this->isExportable;
    }
    
    /**
     * Sets whether the column is exportable.
     *
     * @param bool $value
     */
    public function setIsExportable($value)
    {
        if (is_bool($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->isExportable = $value;
    }
    
    /**
     * Gets the sort expression or column name by which the column is sorted against the data source.
     * By default, the sort expression equals the binding member name.
     *
     * @return string
     */
    public function getSortExpression()
    {
        return $this->sortExpression;
    }
    
    /**
     * Sets the sort expression or column name by which the column is sorted against the data source. (without the ASC or DESC)
     * By default, the sort expression equals the binding member name.
     *
     * @param string $value
     */
    public function setSortExpression($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        $this->sortExpression = $value;
    }
    
    /**
     * Gets the search expression or column name by which the column is searched against the data source.
     * By default, the search expression is an empty sting, which means the column is not searchable
     *
     * @return string
     */
    public function getSearchExpression()
    {
        return $this->searchMemberExpression;
    }
    
    /**
     * Sets the search expression or column name by which the column is searched against the data source.
     * By default, the search expression is an empty sting, which means the column is not searchable
     *
     * @param string $value
     */
    public function setSearchExpression($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        $this->searchMemberExpression = $value;
    }
}


/**
 * Provides a column used to grouop several ohe columns inside a grid.
 * The SortExpression will be st to the bindingMemberName by default
 *
 * @package WebCore
 * @subpackage Model
 */
class GroupingColumn extends BoundRepeaterFieldModelBase
{
    protected $sortMode;
    protected $sortExpression;
    
    /**
     * Creates an instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $sortMode Either 'ASC' or 'DESC'
     * @param string $sortExpression The sort expression for this column. If left empty, it will be the binding member name itself
     */
    public function __construct($name, $caption, $sortMode = 'ASC', $sortExpression = '')
    {
        parent::__construct($name, $caption, $name);
        $this->setSortMode($sortMode);
        
        if ($sortExpression !== '')
            $this->setSortExpression($sortExpression);
        else
            $this->setSortExpression($this->bindingMemberName);
    }
    
    /**
     * Gets the sort mode of the column; either 'ASC' or 'DESC'
     */
    public function getSortMode()
    {
        return $this->sortMode;
    }
    
    /**
     * Gets the sort mode of the column; either 'ASC' or 'DESC'
     */
    public function setSortMode($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        if ($value != 'ASC' && $value != 'DESC')
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value; Must be \'ASC\' or \'DESC\'');
        $this->sortMode = $value;
    }
    
    /**
     * Grouping columns are not sortable by change, and thus are not taken into account in the sortable column indexes.
     * @return bool
     */
    public function getIsSortable()
    {
        return false;
    }
    
    /**
     * Gets the sort expression or column name by which the column is sorted against the data source.
     * By default, the sort expression equals the binding member name.
     *
     * @return string
     */
    public function getSortExpression()
    {
        return $this->sortExpression;
    }
    
    /**
     * Sets the sort expression or column name by which the column is sorted against the data source.
     * By default, the sort expression equals the binding member name.
     *
     * @param string $value
     */
    public function setSortExpression($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        $this->sortExpression = $value;
    }
    
    /**
     * Creates a default instance for this class
     *
     * @return GroupingColumn
     */
    public static function createInstance()
    {
        return new GroupingColumn('ISerializable', 'ISerializable');
    }
}

/**
 * Provides an implementation of a bound grid column that holds a maximum character length
 * By default, the column is sortable and exportable.
 *
 * @package WebCore
 * @subpackage Model
 */
class TextBoundGridColumn extends BoundGridColumnBase
{
    protected $maxChars;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param int $maxChars
     * @param string $searchExpression The search expression to use when search paramters are provided. If left empty, the column will not be searchable.
     * @param string $sortExpression The sort expression for this column. If left empty, it will be the binding member name itself
     */
    public function __construct($name, $caption, $maxChars = 50, $searchExpression = '', $sortExpression = '')
    {
        $this->maxChars = $maxChars;
        parent::__construct($name, $caption, $name);
        $this->setSearchExpression($searchExpression);
        if ($sortExpression !== '')
            $this->setSortExpression($sortExpression);
        $this->searchDialog = new TextGridSearchDialog($this->name, Resources::getValue(Resources::SRK_SEARCH_DIALOG_CAPTION) . ' ' . $caption, Resources::getValue(Resources::SRK_SEARCH_DIALOG_MSG));
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return TextBoundGridColumn
     */
    public static function createInstance()
    {
        return new TextBoundGridColumn('ISerializable', 'ISerializable');
    }
    
    /**
     * Gets the maximum amount of characters
     *
     * @return int
     */
    public function getMaxChars()
    {
        return intval($this->maxChars);
    }
    
    /**
     * Sets the maximum amount of characters
     *
     * @param int $value
     */
    public function setMaxChars($value)
    {
        if (is_int($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        if ($value <= 0)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value; value must be greater than 0.');
        
        $this->maxChars = $value;
    }
}

/**
 * Provides an implementation of a bound grid column that holds Dates
 * By default, the column is sortable and exportable.
 *
 * @todo Get Date Format from Culture!
 * @package WebCore
 * @subpackage Model
 */
class DateTimeBoundGridColumn extends BoundGridColumnBase
{
    protected $dateFormat;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $caption
     * @param string $dateFormat In standard PHP date format
     * @param string $searchExpression If left empty, the column will not b searchable
     * @param string $sortExpression The sort expression for this column. If left empty, it will be the binding member name itself
     */
    public function __construct($name, $caption, $dateFormat = '', $searchExpression = '', $sortExpression = '')
    {
        $this->dateFormat = ($dateFormat == '') ? 'n/j/Y g:i A' : $dateFormat;
        
        parent::__construct($name, $caption, $name);
        $this->setSearchExpression($searchExpression);
        if ($sortExpression !== '')
            $this->setSortExpression($sortExpression);
        $this->searchDialog = new DateGridSearchDialog($this->name, Resources::getValue(Resources::SRK_SEARCH_DIALOG_CAPTION) . ' ' . $caption, Resources::getValue(Resources::SRK_SEARCH_DIALOG_MSG));
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return DateTimeBoundGridColumn
     */
    public static function createInstance()
    {
        return new DateTimeBoundGridColumn('ISerializable', 'ISerializable');
    }
    
    /**
     * Gets the date format to use (in standard PHP format)
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }
    
    /**
     * Sets the dete format to use )in standard PHP format)
     *
     * @param string $value
     */
    public function setDateFormat($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->dateFormat = $value;
    }
}

/**
 * Provides an implementation of a bound grid column that holds numeric values.
 * By default, the column is sortable and exportable.
 *
 * @package WebCore
 * @subpackage Model
 */
class NumberBoundGridColumn extends BoundGridColumnBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     * @param string $searchExpression Inf left empty, the column will not be searchable.
     * @param string $sortExpression The sort expression for this column. If left empty, it will be the binding member name itself
     */
    public function __construct($name, $caption, $searchExpression = '', $sortExpression = '')
    {
        parent::__construct($name, $caption, $name);
        $this->setSearchExpression($searchExpression);
        if ($sortExpression !== '')
            $this->setSortExpression($sortExpression);
        $this->searchDialog = new NumberGridSearchDialog($this->name, Resources::getValue(Resources::SRK_SEARCH_DIALOG_CAPTION) . ' ' . $caption, Resources::getValue(Resources::SRK_SEARCH_DIALOG_MSG));
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return NumberBoundGridColumn
     */
    public static function createInstance()
    {
        return new NumberBoundGridColumn('ISerializable', 'ISerializable');
    }
}

/**
 * Provides an implementation of a bound grid column that holds monetary quantities.
 * By default, the column is sortable and exportable.
 *
 * @package WebCore
 * @subpackage Model
 */
class MoneyBoundGridColumn extends BoundGridColumnBase
{
    /**
     * Creates a new instance of this class.
     * @param string $name
     * @param string $caption
     * @param string $searchExpression If left empty, the column will not be searchable.
     * @param string $sortExpression The sort expression for this column. If left empty, it will be the binding member name itself
     */
    public function __construct($name, $caption, $searchExpression = '', $sortExpression = '')
    {
        parent::__construct($name, $caption, $name);
        $this->setSearchExpression($searchExpression);
        if ($sortExpression !== '')
            $this->setSortExpression($sortExpression);
        $this->searchDialog = new NumberGridSearchDialog($this->name, Resources::getValue(Resources::SRK_SEARCH_DIALOG_CAPTION) . ' ' . $caption, Resources::getValue(Resources::SRK_SEARCH_DIALOG_MSG));
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return MoneyBoundGridColumn
     */
    public static function createInstance()
    {
        return new MoneyBoundGridColumn('ISerializable', 'ISerializable');
    }
}

class CheckBoxGridColumn extends BoundGridColumnBase
{
    /**
     * Creates a new instance of this class.
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption, $name);
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return CheckBoxGridColumn
     */
    public static function createInstance()
    {
        return new CheckBoxGridColumn('ISerializable', 'ISerializable');
    }
}

/**
 * Provides the most basic implementation of a column which has items to display and can also fire server-side events.
 * Specify which member in the data source provides the event value in the bindingMemberEventValue argument.
 * If you want to display a static command caption, leave the bindingMemberCommandCaption empty.
 *
 * @package WebCore
 * @subpackage Model
 */
class RowCommandGridColumn extends EventDataItemBase
{
    protected $commandCaption;
    protected $bindingMemberCommandCaption;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     * @param string $commandCaption
     * @param string $eventName
     * @param string $bindingMemberEventValue
     * @param string $bindingMemberCommandCaption
     */
    public function __construct($name, $commandCaption, $eventName, $bindingMemberEventValue, $bindingMemberCommandCaption = '')
    {
        parent::__construct($name, '', $eventName, $bindingMemberEventValue);
        $this->setCommandCaption($commandCaption);
        $this->bindingMemberCommandCaption = $bindingMemberCommandCaption;
    }
    
    /**
     * Creates the default serialization instance for this class
     * 
     * @return RowCommandGridColumn
     */
    public static function createInstance()
    {
        return new RowCommandGridColumn('ISerializable', 'ISerializable', 'ISerializable', 'ISerializable');
    }
    
    /**
     * Gets the command caption; will be overriden by the BindingMemeberCommandCaption property.
     *
     * @return string
     */
    public function getCommandCaption()
    {
        return $this->commandCaption;
    }
    
    /**
     * Sets the command caption; will be overriden by the BindingMemeberCommandCaption property.
     *
     * @param string $value
     */
    public function setCommandCaption($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->commandCaption = $value;
    }
    
    /**
     * Gets the command caption to display based on a member in the data source. Overrides the CommandCaptionProperty if set.
     *
     * @return string
     */
    public function getBindingMemberCommandCaption()
    {
        return $this->bindingMemberCommandCaption;
    }
    
    /**
     * Sets the command caption to display based on a member in the data source. Overrides the CommandCaptionProperty if set.
     *
     * @param string $value
     */
    public function setBindingMemberCommandCaption($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->bindingMemberCommandCaption = $value;
    }
}

/**
 * Predefined RowCommandGridColumn
 *
 * @package WebCore
 * @subpackage Model
 */
class DetailsCommandGridColumn extends RowCommandGridColumn
{
    /**
     * Creates an instance of this class
     *
     * @param string name
     * @param string $eventName
     * @param string $bindingMemberEventValue
     */
    public function __construct($name, $eventName, $bindingMemberEventValue = 'id')
    {
        parent::__construct($name, Resources::getValue(Resources::SRK_GRID_COMMAND_VIEW), $eventName, $bindingMemberEventValue, '');
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return DetailsCommandGridColumn
     */
    public static function createInstance()
    {
        return new DetailsCommandGridColumn('ISerializable', 'ISerializable');
    }
    
    /**
     * Unsupported Operation. Will throw exception.
     */
    public function setBindingMemberCommandCaption($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The command captions cannot be overriden for instances of this class.');
    }
}

/**
 * Predefined RowCommandGridColumn
 *
 * @package WebCore
 * @subpackage Model
 */
class EditCommandGridColumn extends RowCommandGridColumn
{
    /**
     * Creates an instance of this class
     *
     * @param string name
     * @param string $eventName
     * @param string $bindingMemberEventValue
     */
    public function __construct($name, $eventName, $bindingMemberEventValue = 'id')
    {
        parent::__construct($name, Resources::getValue(Resources::SRK_GRID_COMMAND_EDIT), $eventName, $bindingMemberEventValue, '');
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return EditCommandGridColumn
     */
    public static function createInstance()
    {
        return new EditCommandGridColumn('ISerializable', 'ISerializable');
    }
    
    /**
     * Unsupported Operation. Will throw exception.
     */
    public function setBindingMemberCommandCaption($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The command captions cannot be overriden for instances of this class.');
    }
}

/**
 * Predefined RowCommandGridColumn
 *
 * @package WebCore
 * @subpackage Model
 */
class DeleteCommandGridColumn extends RowCommandGridColumn
{
    /**
     * Creates an instance of this class
     *
     * @param string name
     * @param string $eventName
     * @param string $bindingMemberEventValue
     */
    public function __construct($name, $eventName, $bindingMemberEventValue = 'id')
    {
        parent::__construct($name, Resources::getValue(Resources::SRK_GRID_COMMAND_DELETE), $eventName, $bindingMemberEventValue, '');
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return DeleteCommandGridColumn
     */
    public static function createInstance()
    {
        return new DeleteCommandGridColumn('ISerializable', 'ISerializable');
    }
    
    /**
     * Unsupported Operation. Will throw exception.
     */
    public function setBindingMemberCommandCaption($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The command captions cannot be overriden for instances of this class.');
    }
}

/**
 * Predefined RowCommandGridColumn
 *
 * @package WebCore
 * @subpackage Model
 */
class SelectCommandGridColumn extends RowCommandGridColumn
{
    /**
     * Creates an instance of this class
     *
     * @param string name
     * @param string $eventName
     * @param string $bindingMemberEventValue
     */
    public function __construct($name, $eventName, $bindingMemberEventValue = 'id')
    {
        parent::__construct($name, Resources::getValue(Resources::SRK_GRID_COMMAND_SELECT), $eventName, $bindingMemberEventValue, '');
    }
    
    /**
     * Creates a new instance of this class.
     *
     * @return SelectCommandGridColumn
     */
    public static function createInstance()
    {
        return new SelectCommandGridColumn('ISerializable', 'ISerializable');
    }
    
    /**
     * Unsupported Operation. Will throw exception.
     */
    public function setBindingMemberCommandCaption($value)
    {
        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The command captions cannot be overriden for instances of this class.');
    }
}

/**
 * Represents the base implementation for a control container in a grid.
 * Basically the same as its parent class but with additional helper methods specific to Column controls
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class ColumnContainerModelBase extends DataRepeaterModelBase
{
    /**
     * Creates a new instance of this class
     *
     * @param string $name
     * @param string $caption
     */
    public function __construct($name, $caption)
    {
        parent::__construct($name, $caption);
    }
    
    /**
     * Determines whether the column name exists by searching recursively.
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $columnName
     * @return bool
     */
    public function columnExists($columnName)
    {
        return $this->getChildren()->controlExists($columnName, 'ContainerModelBase');
    }
    
    /**
     * Adds a column model to the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param BoundRepeaterFieldModelBase $column
     */
    public function addColumn(&$column)
    {
        if (key_exists('BoundRepeaterFieldModelBase', class_parents($column)) === true)
            $this->getChildren()->addControl($column);
        else
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The column object must inherit from BoundRepeaterFieldModelBase');
    }
    
    /**
     * Gets a column model within the collection.
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param string $columnName
     * @param bool $searchRecursive
     * @return BoundRepeaterFieldModelBase
     */
    public function getColumn($columnName, $searchRecursive = true)
    {
        return $this->getChildren()->getControl($columnName, $searchRecursive, 'BoundRepeaterFieldModelBase');
    }
    
    /**
     * Returns an array of column names within the collection
     * This is a shortcut method. Use the getChildren() method to access the ControlModelCollection.
     *
     * @param bool $searchRecursive
     * @return array
     */
    public function getColumnNames($searchRecursive)
    {
        return $this->getChildren()->getControlNames($searchRecursive, 'BoundRepeaterFieldModelBase');
    }
}

/**
 * Represents the real-time state management object that holds user values
 * that are not part of a grid model but are still required for proper grid operation
 * 
 * @package WebCore
 * @subpackage Model
 */
class GridState extends DataRepeaterState
{
    protected $sortColumnName;
    protected $sortDirection;
    protected $filterExpressionIndex;
    protected $searchColumnName;
    protected $searchOperator;
    protected $searchArgument;
    protected $searchArgumentAlt;
    
    const GRID_SORT_ASCENDING = 'ASC';
    const GRID_SORT_DESCENDING = 'DESC';
    
    /**
     * Creates a new instance of this class.
     *
     * @param int $pageIndex
     * @param int $pageCount
     * @param int $totalRecordCount
     */
    public function __construct($pageIndex = 0, $pageCount = -1, $totalRecordCount = -1)
    {
        parent::__construct($pageIndex, $pageCount, $totalRecordCount);
        
        $this->sortColumnName        = '~';
        $this->sortDirection         = self::GRID_SORT_ASCENDING;
        $this->filterExpressionIndex = -1;
        
        $this->searchArgument    = '';
        $this->searchOperator    = GridSearchDialogBase::OPER_NONE;
        $this->searchArgumentAlt = '';
        $this->searchColumnName  = '~';
    }
    
    /**
     * Creates the default instance of this class
     *
     * @return GridState
     */
    public static function createInstance()
    {
        return new GridState();
    }
    
    /**
     * Gets the name of the column the grid is sorted by
     *
     * @return string
     */
    public function getSortColumnName()
    {
        return $this->sortColumnName;
    }
    
    /**
     * Sets the name of the column the grid is sorted by
     *
     * @param string $value
     */
    public function setSortColumnName($value)
    {
        $this->sortColumnName = $value;
    }
    
    /**
     * Gets the sort direction.
     * Return value is one of GridState::GRID_ prefixed constants
     * @return string
     */
    public function getSortDirection()
    {
        return $this->sortDirection;
    }
    
    /**
     * Sets the sort direction. Value must be one of GridState::GRID_ prefixed constants
     * @param string $value
     */
    public function setSortDirection($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        if ($value != GridState::GRID_SORT_ASCENDING && $value != GridState::GRID_SORT_DESCENDING)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = value; value must be either 'ASC' or 'DESC'");
        
        $this->sortDirection = $value;
    }
    
    /**
     * Gets the filter expression index.
     *
     * @return int
     */
    public function getFilterExpressionIndex()
    {
        return intval($this->filterExpressionIndex);
    }
    
    /**
     * Sets the filter expresison index.
     *
     * @param int $value
     */
    public function setFilterExpressionIndex($value)
    {
        $this->filterExpressionIndex = intval($value);
    }
    /**
     * Gets the search argument
     *
     * @return mixed
     */
    public function getSearchArgument()
    {
        return $this->searchArgument;
    }
    
    /**
     * Sets the search argument
     *
     * @param $value mixed
     */
    public function setSearchArgument($value)
    {
        $this->searchArgument = $value;
    }
    
    /**
     * Gets the search operator.
     * Search operator constants are fined in GridSearchDialogBase::OPER-prefixed constants
     */
    public function getSearchOperator()
    {
        return $this->searchOperator;
    }
    
    /**
     * Sets the search operator.
     * Search operator constants are fined in GridSearchDialogBase::OPER-prefixed constants
     *
     * @param int $value
     */
    public function setSearchOperator($value)
    {
        $this->searchOperator = intval($value);
    }
    
    /**
     * Gets the alternate search argument.
     * 
     * @return mixed
     */
    public function getSearchArgumentAlt()
    {
        return $this->searchArgumentAlt;
    }
    
    /**
     * Sets the alternate search argument.
     * 
     * @param mixed $value
     */
    public function setSearchArgumentAlt($value)
    {
        $this->searchArgumentAlt = $value;
    }
    
    /**
     * Gets the search column name
     *
     * @return string
     */
    public function getSearchColumnName()
    {
        return $this->searchColumnName;
    }
    
    /**
     * Sets the search column name
     *
     * @param string $value
     */
    public function setSearchColumnName($value)
    {
        if (is_string($value) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = value');
        
        $this->searchColumnName = $value;
    }
    
    /**
     * Creates an instance from a base64 string
     *
     * @param string $data
     * @return GridState
     */
    public static function fromBase64($data)
    {
        return Base64Serializer::deserialize($data, get_class());
    }
}

/**
 * Event manager control that provides automatic by-column searching capabilities.
 * 
 * @package WebCore
 * @subpackage Model
 */
class GridSearchManager extends RepeaterEventManagerControlBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, 'SearchByColumn', '~');
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic searching capabilities
     *
     * @param Grid $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        /**
         * @var GridState
         */
        $gridState =& $sender->getState();
        $this->eventValue     = $event->getValue();
        $gridSearchColumnName = $event->getValue();
        
        // After a search, it is always necessary to re-calculate the count
        $gridState->resetRecordCount();
        
        if ($gridSearchColumnName == '~')
        {
            $gridState->setSearchArgument('');
            $gridState->setSearchArgumentAlt('');
            $gridState->setSearchColumnName('~');
            $gridState->setSearchOperator(GridSearchDialogBase::OPER_NONE);
            return;
        }
        
        /**
         * @var BoundGridColumn
         */
        $column = $sender->getColumn($gridSearchColumnName, true);
        
        $request = HttpContext::getRequest()->getRequestVars();
        $dialog  = $column->getSearchDialog();
        $dialog->dataBind($request);
        $isValid = $dialog->validate();
        
        if ($isValid)
        {
            $gridState->setSearchArgument($dialog->getArgumentControl()->getValue());
            if ($dialog->getArgumentAltControl() !== null)
            {
                $gridState->setSearchArgumentAlt($dialog->getArgumentAltControl()->getValue());
            }
            else
            {
                $gridState->setSearchArgumentAlt('');
            }
            $gridState->setSearchColumnName($gridSearchColumnName);
            $gridState->setSearchOperator($dialog->getSearchOperatorsControl()->getValue());
        }
        else
        {
            $gridState->setSearchArgument('');
            $gridState->setSearchArgumentAlt('');
            $gridState->setSearchColumnName('~');
            $gridState->setSearchOperator(GridSearchDialogBase::OPER_NONE);
        }
    }
    
    /**
     * Creates a default instance of this class.
     *
     * @return GridSearchManager
     */
    public static function createInstance()
    {
        return new GridSearchManager('ISerializable');
    }
}

/**
 * 
 * @todo Doc this
 * @package WebCore
 * @subpackage Model
 */
class GridSortingManager extends RepeaterEventManagerControlBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, 'SortByColumn', '~');
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic paging capabilities
     *
     * @param Grid $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        $sortOptions = explode('|', $event->getValue());
        $sender->getState()->setSortColumnName($sortOptions[0]);
        $sender->getState()->setSortDirection($sortOptions[1]);
        $this->eventValue = $event->getValue();
    }
    
    public static function createInstance()
    {
        return new GridSortingManager('ISerializable');
    }
}

/**
 * 
 * @todo Doc this
 * @package WebCore
 * @subpackage Model
 */
class GridFilteringManager extends RepeaterEventManagerControlBase
{
    protected $filterExpressions;
    
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name, 'ApplyFilter', '-1');
        $this->filterExpressions = new IndexedCollection();
    }
    
    /**
     * @todo Doc this
     */
    public function addFilter($caption, $expression)
    {
        $entry = new DictionaryEntry($caption, $expression);
        $this->filterExpressions->addItem($entry);
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic filtering capabilities
     *
     * @param Grid $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        if ($sender->getState()->getFilterExpressionIndex() != $event->getValue())
        {
            // There is a new filter applied; reclaculate record count.
            $sender->getState()->resetRecordCount();
        }
        $sender->getState()->setFilterExpressionIndex($event->getValue());
        $this->eventValue = $event->getValue();
    }
    
    /**
     * @return IndexedCollection[DictionaryEntry]
     */
    public function &getFilterExpressions()
    {
        return $this->filterExpressions;
    }
    
    public static function createInstance()
    {
        return new GridFilteringManager('ISerializable');
    }
}

/**
 * 
 * @todo Doc this
 * @package WebCore
 * @subpackage Model
 */
abstract class GridCustomEventManagerBase extends RepeaterEventManagerControlBase
{
    protected $caption;
    
    /**
     * @todo Doc this
     */
    public function __construct($name, $caption, $eventName)
    {
        parent::__construct($name, $eventName, '~');
        $this->caption = $caption;
    }
    
    public function getCaption()
    {
        return $this->caption;
    }
    
    public function setCaption($value)
    {
        $this->caption = $value;
    }
}

/**
 * 
 * @todo Doc this
 * @package WebCore
 * @subpackage Model
 */
class GridPrintEventManager extends GridCustomEventManagerBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $caption = Resources::getValue(Resources::SRK_GRID_ACTION_PRINT);
        parent::__construct($name, $caption, $name . '_PrintEvent', '~');
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic printing capabilities
     *
     * @param Grid $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        $beforeDataBindCallback = array(
            $this,
            'PreparePrintPeview'
        );
        $afterDataBindCallback  = array(
            $this,
            'OutputPrintPreview'
        );
        $sender->getOnBeforeDataBindCallbacks()->addItem($beforeDataBindCallback);
        $sender->getOnAfterDataBindCallbacks()->addItem($afterDataBindCallback);
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return GridPrintEventManager
     */
    public static function createInstance()
    {
        return new GridPrintEventManager('ISerializable');
    }
    
    /**
     * Before dataBind callback
     * 
     * @param Grid $model
     * @param mixed $dataSource
     */
    public function PreparePrintPeview(&$model, &$dataSource)
    {
        $model->setIsPaged(false);
    }
    
    /**
     * After dataBind callback
     * 
     * @param Grid $model
     * @param mixed $dataSource
     */
    public function OutputPrintPreview(&$model, &$dataSource)
    {
        HtmlViewManager::getDependencyCollection()->clear();
        
        $view = new HtmlGridPrintView($model);
        $tw   = HtmlWriter::getInstance();
        $tw->writeRaw(MarkupWriter::DTD_XHTML_STRICT);
        $tw->openHtml();
        $tw->openHead();
        HtmlViewManager::render();
        $tw->openTitle();
        $tw->writeContent(Resources::getValue(Resources::SRK_PRINTVIEW_TITLE) . $model->getCaption());
        $tw->closeTitle();
        $tw->closeHead();
        $tw->openBody();
        $tw->openDiv();
        $view->render();
        $tw->closeDiv();
        $tw->closeBody();
        $tw->closeHtml();
        
        flush();
        HttpResponse::end();
    }
}

/**
 * 
 * @todo Doc this class
 * @package WebCore
 * @subpackage Model
 */
abstract class GridExporterEventManagerBase extends GridCustomEventManagerBase
{
    /**
     * @todo Doc this
     */
    public function __construct($name, $caption, $eventName)
    {
        parent::__construct($name, $caption, $eventName);
        set_time_limit(900); // it might take a while to export
    }
    
    /**
     * Instance callback that is automatically registered with the Controller upon model instantiation.
     * This callback enables automatic printing capabilities
     *
     * @param Grid $sender
     * @param ControllerEvent $event
     */
    public function onEventRaised(&$sender, &$event)
    {
        $beforeDataBindCallback = array(
            $this,
            'OnBeforeDataBind'
        );
        $afterDataBindCallback  = array(
            $this,
            'OnAfterDataBind'
        );
        $sender->getOnBeforeDataBindCallbacks()->addItem($beforeDataBindCallback);
        $sender->getOnAfterDataBindCallbacks()->addItem($afterDataBindCallback);
    }
    
    /**
     * Before dataBind callback
     * 
     * @param Grid $model
     * @param mixed $dataSource
     */
    public function OnBeforeDataBind(&$model, &$dataSource)
    {
        $model->setIsPaged(false);
    }
    
    /**
     * After dataBind callback
     * @todo Implement properly
     * @param Grid $model
     * @param mixed $dataSource
     */
    public function OnAfterDataBind(&$model, &$dataSource)
    {
        try
        {
            $view = $this->createView($model);
            $view->render();
        }
        catch (Exception $ex)
        {
            echo $ex;
        }
        exit();
    }
    
    /**
     * @todo Doc this
     */
    abstract protected function createView($model);
}

/**
 * Exports the grid's data source to a Comma-separated list of values (CSV)
 * @package WebCore
 * @subpackage Model
 */
class GridCsvExporterEventManager extends GridExporterEventManagerBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $caption = Resources::getValue(Resources::SRK_PRINTVIEW_CSVEXPORT);
        parent::__construct($name, $caption, $name . '_ExporterEvent', '~');
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return GridCsvExporterEventManager
     */
    public static function createInstance()
    {
        return new GridCsvExporterEventManager('ISerializable');
    }
    
    /**
     * @todo Doc this
     */
    protected function createView($model)
    {
        return new HtmlGridCsvWriterView($model);
    }
}

/**
 * 
 * @todo Doc this
 * @package WebCore
 * @subpackage Model
 */
class GridBiffExporterEventManager extends GridExporterEventManagerBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $caption = Resources::getValue(Resources::SRK_PRINTVIEW_BIFFEXPORT);
        parent::__construct($name, $caption, $name . '_ExporterEvent', '~');
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return GridBiffExporterEventManager
     */
    public static function createInstance()
    {
        return new GridBiffExporterEventManager('ISerializable');
    }
    
    /**
     * @todo Doc this
     */
    protected function createView($model)
    {
        return new HtmlGridBiffWriterView($model);
    }
}

/**
 * 
 * @todo Doc this
 * @package WebCore
 * @subpackage Model
 */
class GridOxmlExporterEventManager extends GridExporterEventManagerBase
{
    /**
     * Creates a new instance of this class.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $caption = Resources::getValue(Resources::SRK_PRINTVIEW_OXMLEXPORT);
        parent::__construct($name, $caption, $name . '_ExporterEvent', '~');
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return GridOxmlExporterEventManager
     */
    public static function createInstance()
    {
        return new GridOxmlExporterEventManager('ISerializable');
    }
    
    protected function createView($model)
    {
        return new HtmlGridOpenXmlWriterView($model);
    }
}

/**
 * Represents a control container, specifically designed for column child controls.
 * This simplifies grid-like control model implementations.
 *
 * @package WebCore
 * @subpackage Model
 */
class Grid extends ColumnContainerModelBase implements IBindingTarget, IRootModel
{
    /**
     * @var GridSortingManager
     */
    protected $sortingManager;
    /**
     * @var GridFilteringManager
     */
    protected $filteringManager;
    /**
     * @var GridSearchManager
     */
    protected $searchManager;
    /**
     * @var IndexedCollection
     */
    protected $onBeforeDataBindCallbacks;
    /**
     * @var IndexedCollection
     */
    protected $onAfterDataBindCallbacks;
    protected $toolbar;
    
    protected $defaultSortColumnName;
    protected $defaultSortDirection;
    
    /**
     * @todo Doc this
     */
    public function __construct($name, $caption, $pageSize = 20)
    {
        parent::__construct($name, $caption);
        $this->state    = new GridState();
        $this->pageSize = $pageSize;
        $this->isPaged  = true;
        $this->toolbar  = new Toolbar($name . "Toolbar", '');
        
        // Automatically deserialize the gridstate if available
        // @todo Models should use the controller
        $request = HttpContext::getRequest()->getRequestVars();
        if ($request->keyExists($this->getStateName()))
        {
            $this->state = GridState::fromBase64($request->getValue($this->getStateName()));
            if (Controller::isPostBack($this) == false) $this->state->resetRecordCount(); // record count might have changed; update it
        }
        
        $this->sortingManager = new GridSortingManager($name . "Sorter");
        $this->children->addControl($this->sortingManager);
        
        $this->filteringManager = new GridFilteringManager($name . "Filterer");
        $this->children->addControl($this->filteringManager);
        
        $this->searchManager = new GridSearchManager($name . "Searcher");
        $this->children->addControl($this->searchManager);
        
        $this->onAfterDataBindCallbacks  = new IndexedCollection();
        $this->onBeforeDataBindCallbacks = new IndexedCollection();
    }
    
    /**
     * Sets the default column name (boundcolumnmodel name) by which the grid is sorted by default
     * @param string $columnName
     */
    public function setDefaultSort($columnName, $direction = GridState::GRID_SORT_ASCENDING)
    {
        if (is_null($this->getColumn($columnName)))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "'$columnName' is not a valid column model name");
        if ($direction !== GridState::GRID_SORT_ASCENDING && $direction !== GridState::GRID_SORT_DESCENDING)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "'$direction' is not a valid sort direction constant value");
            
        $this->defaultSortColumnName = $columnName;
        $this->defaultSortDirection = $direction;
        
        if ($this->state->getSortColumnName() == '~')
        {
            $this->state->setSortColumnName($this->defaultSortColumnName);
            $this->state->setSortDirection($this->defaultSortDirection);
        }
    }
    
    /**
     * Gets the column name by which the grid is sorted by default
     * @return string
     */
    public function getDefaultSortColumnName()
    {
        return $this->defaultSortColumnName;
    }
    
    /**
     * Gets the default sort direction by which he grid is sorted by default
     * @return string
     */
    public function getDefaultSortDirection()
    {
        return $this->defaultSortDirection;
    }
    
    /**
     * Gets the toolbar control model for this grid
     * @return Toolbar
     */
    public function &getToolbar()
    {
        return $this->toolbar;
    }
    
    /**
     * @return GridSortingManager
     */
    public function &getSortingManager()
    {
        return $this->sortingManager;
    }
    
    /**
     * @return &GridFilteringManager
     */
    public function &getFilteringManager()
    {
        return $this->filteringManager;
    }
    
    /**
     * @return &GridSearchManager
     */
    public function &getSearchManager()
    {
        return $this->searchManager;
    }
    
    /**
     * Gets the callbacks that are executed when the dataBind() method is called.
     * The callbacks must be in the standard PHP callback form.
     * The signature of the callbacks must be function callbackFunction(&$model as Grid, &$dataSource as IObject)
     *
     * @return IndexedCollection
     */
    public function &getOnBeforeDataBindCallbacks()
    {
        return $this->onBeforeDataBindCallbacks;
    }
    
    /**
     * Gets the callbacks that are executed reght before the dataBind() method returns.
     * The callbacks must be in the standard PHP callback form.
     * The signature of the callbacks must be function callbackFunction(&$model as Grid, &$dataSource as IObject)
     *
     * @return IndexedCollection
     */
    public function &getOnAfterDataBindCallbacks()
    {
        return $this->onAfterDataBindCallbacks;
    }
    
    /**
     * Takes either an IndexedCollection[KeyedCollection] or a DataTableAdapter, and generates the data items (IndexedCollection of stdClass Items) for the grid
     * The data items will then be available to the renderer along with the column collection.
     *
     * @param mixed $dataSource
     */
    public function dataBind(&$dataSource)
    {
        foreach ($this->onBeforeDataBindCallbacks as $callback)
        {
            call_user_func_array($callback, array(
                &$this,
                &$dataSource
            ));
        }
        
        if (ObjectIntrospector::isA($dataSource, 'IDataTableAdapter'))
        {
            // Filetering Manager = First WHERE clause 
            $filterIndex = $this->state->getFilterExpressionIndex();
            if ($filterIndex >= 0)
            {
                $whereClauseItem = $this->filteringManager->getFilterExpressions()->getItem($filterIndex);
                $whereClause     = $whereClauseItem->getValue();
                $dataSource->where($whereClause);
            }
            
            // Search Manager = Second WHERE clause
            if ($this->state->getSearchColumnName() != '~')
            {
                $searchOperator = $this->state->getSearchOperator();
                /**
                 * @var BoundGridColumn
                 */
                $column         = $this->getColumn($this->state->getSearchColumnName());
                
                $column->getSearchDialog()->getArgumentControl()->setValue($this->state->getSearchArgument());
                $column->getSearchDialog()->getSearchOperatorsControl()->setValue($this->state->getSearchOperator());
                
                if ($column->getSearchDialog()->getArgumentAltControl() != null)
                {
                    $column->getSearchDialog()->getArgumentAltControl()->setValue($this->state->getSearchArgumentAlt());
                }
                
                switch ($searchOperator)
                {
                    case GridSearchDialogBase::OPER_LITERAL_CONTAINS:
                        $dataSource->where($column->getSearchExpression() . " LIKE '%" . $this->state->getSearchArgument() . "%'");
                        break;
                    case GridSearchDialogBase::OPER_LITERAL_ENDSWITH:
                        $dataSource->where($column->getSearchExpression() . " LIKE '%" . $this->state->getSearchArgument() . "'");
                        break;
                    case GridSearchDialogBase::OPER_LITERAL_EQUALS:
                        $dataSource->where($column->getSearchExpression() . " LIKE '" . $this->state->getSearchArgument() . "'");
                        break;
                    case GridSearchDialogBase::OPER_LITERAL_STARTSWITH:
                        $dataSource->where($column->getSearchExpression() . " LIKE '" . $this->state->getSearchArgument() . "%'");
                        break;
                    case GridSearchDialogBase::OPER_LOGICAL_BETWEEN:
                        $dataSource->where($column->getSearchExpression() . " BETWEEN " . $this->state->getSearchArgument() . " AND " . $this->state->getSearchArgumentAlt());
                        break;
                    case GridSearchDialogBase::OPER_LOGICAL_GT:
                        $dataSource->where($column->getSearchExpression() . " > '" . $this->state->getSearchArgument() . "'");
                        break;
                    case GridSearchDialogBase::OPER_LOGICAL_LT:
                        $dataSource->where($column->getSearchExpression() . " < '" . $this->state->getSearchArgument() . "'");
                        break;
                    case GridSearchDialogBase::OPER_LOGIGAL_EQUALS:
                        $dataSource->where($column->getSearchExpression() . " = '" . $this->state->getSearchArgument() . "'");
                        break;
                    case GridSearchDialogBase::OPER_NONE:
                        break;
                    default:
                        break;
                }
            }
            
            // Paging
            if ($this->isPaged === true)
            {
                if ($this->state->getTotalRecordCount() === -1)
                {
                    $countSource = clone $dataSource;
                    $recordCount = $countSource->count();
                    $this->state->setTotalRecordCount($recordCount);
                    $pageCount = intval(ceil($recordCount / $this->pageSize));
                    $this->state->setPageCount($pageCount);
                    
                    if ($this->state->getPageIndex() > 0 && $this->state->getPageIndex() >= $this->state->getPageCount())
                    {
                        $this->state->setPageIndex($this->state->getPageCount() - 1);
                    }
                }
                
                $dataSource->take($this->pageSize)->skip($this->state->getPageIndex() * $this->pageSize);
            }
            
            // Grouping columns = order-by arguments
            
            /**
             * @var array
             */
            $groupingColumnNames = $this->getChildren()->getTypedControlNames(true, 'GroupingColumn');
            
            if (count($groupingColumnNames > 0))
            {
                foreach ($groupingColumnNames as $colName)
                {
                    /**
                     * @var GroupingColumn
                     */
                    $groupingCol = $this->getColumn($colName);
                    if ($groupingCol->getVisible() !== true)
                        continue;
                    
                    if ($groupingCol->getSortMode() == GridState::GRID_SORT_ASCENDING)
                    {
                        $dataSource->orderBy($groupingCol->getSortExpression());
                    }
                    else
                    {
                        $dataSource->orderByDescending($groupingCol->getSortExpression());
                    }
                }
            }
            
            // Column Sorting Manager
            if ($this->state->getSortColumnName() != '~')
            {
                $column = $this->getColumn($this->state->getSortColumnName(), true);
                if ($this->state->getSortDirection() == GridState::GRID_SORT_ASCENDING)
                {
                    $dataSource->orderBy($column->getSortExpression());
                }
                else
                {
                    $dataSource->orderByDescending($column->getSortExpression());
                }
            }
            // Default the sorting to the first bound column column
            else
            {
                if ($this->getIsPaged() === true)
                {
                    $foundDefaultSortingColumn = false;
                    $boundGridColNames         = $this->getChildren()->getTypedControlNames(true, 'BoundGridColumnBase');
                    foreach ($boundGridColNames as $colName)
                    {
                        $column = $this->getColumn($colName);
                        if ($column->getIsSortable() === true)
                        {
                            $foundDefaultSortingColumn = true;
                            $dataSource->orderBy($column->getSortExpression());
                            $this->state->setSortColumnName($colName);
                            $this->state->setSortDirection(GridState::GRID_SORT_ASCENDING);
                            break;
                        }
                    }
                    
                    if ($foundDefaultSortingColumn !== true)
                        throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'The paging feature requires at least one sortable BoundGridColumn.');
                }
            }
            
            $rows = $dataSource->selectNew()->getArrayReference();
            $this->dataItems->addRange($rows);
        }
        else
        {
            foreach ($dataSource as $item)
            {
                $obj = $item->toStdClass();
                $this->dataItems->addItem($obj);
            }
            
            $this->state->setTotalRecordCount($dataSource->getCount());
        }
        
        foreach ($this->onAfterDataBindCallbacks as $callback)
        {
            call_user_func_array($callback, array(
                &$this,
                &$dataSource
            ));
        }
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return Grid
     */
    public static function createInstance()
    {
        return new Grid('ISerializable', 'ISerializable');
    }
}

/**
 * Represents a grid search dialog
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class GridSearchDialogBase extends Form
{
    // Search mode constants
    const COL_SEARCH_MODE_LITEAL = 1;
    const COL_SEARCH_MODE_LOGICAL = 2;
    
    // Operator Constants
    const OPER_NONE = -1;
    
    const OPER_LITERAL_CONTAINS = 0;
    const OPER_LITERAL_EQUALS = 1;
    const OPER_LITERAL_STARTSWITH = 2;
    const OPER_LITERAL_ENDSWITH = 3;
    
    const OPER_LOGIGAL_EQUALS = 4;
    const OPER_LOGICAL_GT = 5;
    const OPER_LOGICAL_LT = 6;
    const OPER_LOGICAL_BETWEEN = 7;
    
    protected $searchMode;
    protected $operatorsControl;
    protected $dialogMessageControl;
    protected $columnName;
    
    public function __construct($columnName, $caption, $searchMode, $message)
    {
        $name = 'searchDialog_' . $columnName;
        parent::__construct($name, $caption);
        $this->columnName           = $columnName;
        $this->dialogMessageControl = new TextBlock($name . '_dialogMessage', $message);
        $this->getChildren()->addControl($this->dialogMessageControl);
        $this->searchMode = $searchMode;
        
        $this->operatorsControl = new ComboBox($this->name . '_operators', '', '0');
        $this->operatorsControl->setBindingMemberName('operatorsControl');
        if ($searchMode === self::COL_SEARCH_MODE_LITEAL)
        {
            $this->operatorsControl->addOption(self::OPER_LITERAL_CONTAINS, Resources::getValue(Resources::SRK_OPER_CONTAINS));
            $this->operatorsControl->addOption(self::OPER_LITERAL_EQUALS, Resources::getValue(Resources::SRK_OPER_EQUALS));
            $this->operatorsControl->addOption(self::OPER_LITERAL_STARTSWITH, Resources::getValue(Resources::SRK_OPER_STARTSWITH));
            $this->operatorsControl->addOption(self::OPER_LITERAL_ENDSWITH, Resources::getValue(Resources::SRK_OPER_ENDSWITH));
        }
        else
        {
            $this->operatorsControl->addOption(self::OPER_LOGIGAL_EQUALS, ' = ');
            $this->operatorsControl->addOption(self::OPER_LOGICAL_GT, ' > ');
            $this->operatorsControl->addOption(self::OPER_LOGICAL_LT, ' < ');
            $this->operatorsControl->addOption(self::OPER_LOGICAL_BETWEEN, Resources::getValue(Resources::SRK_OPER_BETWEEN));
        }
        
        $this->getChildren()->addControl($this->operatorsControl);
        
        $this->addButton(new Button($this->name . '_ok', Resources::getValue(Resources::SRK_SEARCH_DIALOG_OK), '~dialog_button_ok'));
        $this->addButton(new Button($this->name . '_cancel', Resources::getValue(Resources::SRK_SEARCH_DIALOG_CANCEL), '~dialog_button_cancel'));
        
    }
    
    public function getColumnName()
    {
        return $this->columnName;
    }
    
    /**
     * Gets the control that represens the text block with search instructions
     * 
     * @return TextBlock
     */
    public function &getDialogMessageControl()
    {
        return $this->dialogMessageControl;
    }
    
    /**
     * Gets the search mode as one of the COL_SEARCH_MODE-prefixed defined constants in this class.
     * @return int
     */
    public function getSearchMode()
    {
        return $this->searchMode;
    }
    /**
     * Gets the field that holds the search operators.
     * 
     * @return ComboBox
     */
    public function &getSearchOperatorsControl()
    {
        return $this->operatorsControl;
    }
    /**
     * Gets the field that holds the search argument value.
     * 
     * @return FieldModelBase
     */
    abstract function &getArgumentControl();
    /**
     * Gets the field that holds the alternative (i.e. for range aearches) search argument value.
     * 
     * @return FieldModelBase
     */
    abstract function &getArgumentAltControl();
}

/**
 * Represents a text search dialog
 *
 * @package WebCore
 * @subpackage Model
 */
class TextGridSearchDialog extends GridSearchDialogBase
{
    protected $argumentControl;
    
    /**
     * @todo Doc this
     */
    public function __construct($name, $caption, $message)
    {
        parent::__construct($name, $caption, GridSearchDialogBase::COL_SEARCH_MODE_LITEAL, $message);
        
        $this->argumentControl = new TextField($this->name . '_argumentControl', '');
        $this->argumentControl->setBindingMemberName('argumentControl');
        $this->getChildren()->addControl($this->argumentControl);
    }
    
    /**
     * Gets the field that holds the search argument value.
     * 
     * @return FieldModelBase
     */
    public function &getArgumentControl()
    {
        return $this->argumentControl;
    }
    
    /**
     * Gets the field that holds the alternative (i.e. for range aearches) search argument value.
     * 
     * @return FieldModelBase
     */
    public function &getArgumentAltControl()
    {
        $nullRef = null;
        return $nullRef;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return TextGridSearchDialog
     */
    public static function createInstance()
    {
        return new TextGridSearchDialog('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a numeric search dialog
 *
 * @package WebCore
 * @subpackage Model
 */
class NumberGridSearchDialog extends GridSearchDialogBase
{
    protected $argumentControl;
    protected $argumentAltControl;
    
    /**
     * @todo Doc this
     */
    public function __construct($columnName, $caption, $message)
    {
        parent::__construct($columnName, $caption, GridSearchDialogBase::COL_SEARCH_MODE_LOGICAL, $message);
        
        $this->argumentControl = new DecimalField($this->name . '_argumentControl', '');
        $this->argumentControl->setBindingMemberName('argumentControl');
        $this->getChildren()->addControl($this->argumentControl);
        
        $this->argumentAltControl = new DecimalField($this->name . '_argumentAltControl', '');
        $this->argumentAltControl->setBindingMemberName('argumentAltControl');
        $this->argumentAltControl->setIsRequired(false);
        $this->getChildren()->addControl($this->argumentAltControl);
    }
    
    /**
     * Custom validation function for this search dialog
     * @return bool
     */
    public function validate()
    {
        if ($this->getSearchOperatorsControl()->getValue() === GridSearchDialogBase::OPER_LOGICAL_BETWEEN)
        {
            $this->argumentAltControl->setIsRequired(true);
        }
        
        return parent::validate();
    }
    
    /**
     * Gets the field that holds the search argument value.
     * 
     * @return FieldModelBase
     */
    public function &getArgumentControl()
    {
        return $this->argumentControl;
    }
    
    /**
     * Gets the field that holds the alternative (i.e. for range aearches) search argument value.
     * 
     * @return FieldModelBase
     */
    public function &getArgumentAltControl()
    {
        return $this->argumentAltControl;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return NumberGridSearchDialog
     */
    public static function createInstance()
    {
        return new NumberGridSearchDialog('ISerializable', 'ISerializable', 'ISerializable');
    }
}

/**
 * Represents a date range search dialog
 *
 * @package WebCore
 * @subpackage Model
 */
class DateGridSearchDialog extends GridSearchDialogBase
{
    protected $argumentControl;
    protected $argumentAltControl;
    
    /**
     * @todo Doc this
     */
    public function __construct($columnName, $caption, $message)
    {
        parent::__construct($columnName, $caption, GridSearchDialogBase::COL_SEARCH_MODE_LOGICAL, $message);
        
        $this->argumentControl = new DateField($this->name . '_argumentControl', '');
        $this->argumentControl->setBindingMemberName('argumentControl');
        $this->getChildren()->addControl($this->argumentControl);
        
        $this->argumentAltControl = new DateField($this->name . '_argumentAltControl', '');
        $this->argumentAltControl->setIsRequired(false);
        $this->argumentAltControl->setBindingMemberName('argumentAltControl');
        $this->getChildren()->addControl($this->argumentAltControl);
    }
    
    /**
     * Custom validation function for this search dialog
     * @return bool
     */
    public function validate()
    {
        if ($this->getSearchOperatorsControl()->getValue() === GridSearchDialogBase::OPER_LOGICAL_BETWEEN)
        {
            $this->argumentAltControl->setIsRequired(true);
        }
        
        return parent::validate();
    }
    
    /**
     * Gets the field that holds the search argument value.
     * 
     * @return FieldModelBase
     */
    public function &getArgumentControl()
    {
        return $this->argumentControl;
    }
    
    /**
     * Gets the field that holds the alternative (i.e. for range aearches) search argument value.
     * 
     * @return FieldModelBase
     */
    public function &getArgumentAltControl()
    {
        return $this->argumentAltControl;
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return DateGridSearchDialog
     */
    public static function createInstance()
    {
        return new DateGridSearchDialog('ISerializable', 'ISerializable', 'ISerializable');
    }
}
?>