<?php
/**
 * @package WebCore
 * @subpackage Actions
 * @version experimental
 * 
 * Actions are the basis for processes. Actions will eventually will give birth to a workflow engine
 *
 * @todo Implement Workflow Engine
 * @todo In PHP 5.3 a workflow engine can be implemented by means of "serializing functions" and executing them as closures.
 *
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";

/**
 * Represents an action
 *
 * @package WebCore
 * @subpackage Actions
 */
interface IAction extends IObject
{
    public function preExecute();
    public function execute();
    public function postExecute();
}

interface IWorkflowAction extends IAction
{
    public function setParent();
}

interface IWorkflowEngine extends IObject
{
}
?>