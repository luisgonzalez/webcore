<?php
/**
 * Represents a Restful resource
 *
 * @package WebCore
 * @subpackage Restful
 */
interface IRestResource extends IObject
{
    /**
     * Gets a list of resources
     *
     * @return mixed
     */
    public static function getList();
    
    /**
     * Gets a resource by ID
     *
     * @param mixed $id
     * @return mixed
     */
    public static function getItem($id);
    
    public static function postItem($data);
    
    public static function putItem($data);
    
    /**
     * Deletes a resource by ID
     *
     * @param mixed $id
     * @return bool
     */
    public static function deleteItem($id);
}

/**
 * Controller for all resources. Resources Controllers should be
 * named <resourceName>Resource.
 * 
 * Use a kinda RewriteRules:
 *
 * RewriteRule ^service/(.*)/(.*) service.php?resource=$1&parameter=$2
 * RewriteRule ^service/(.*) service.php?resource=$1
 *
 * @package WebCore
 * @subpackage Restful
 */
class RestController extends ObjectBase
{
    /**
     * Handles REST connection
     *
     */
    public static function handle()
    {
        HttpResponse::setContentType('json/application');
        
        $request   = HttpRequest::getInstance();
        $parameter = null;
        
        if ($request->getRequestVars()->keyExists('resource') == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Resource is missing');
        
        $resourceController = $request->getRequestVars()->getValue('resource') . "Resource";
        
        if (class_exists($resourceController) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Resource Controller is missing');
        
        if ($request->getRequestVars()->keyExists('parameter'))
            $parameter = $request->getRequestVars()->getValue('parameter');
        
        switch (HttpContextInfo::getRequestMethod())
        {
            case "GET":
                if (is_null($parameter))
                    $data = call_user_func(array(
                        $resourceController,
                        'getList'
                    ));
                else
                    $data = call_user_func(array(
                        $resourceController,
                        'getItem'
                    ), $parameter);
                break;
            case "POST":
                if ($request->getRequestVars()->keyExists('put'))
                    $data = call_user_func(array(
                        $resourceController,
                        'putItem'
                    ), $_POST);
                else
                    $data = call_user_func(array(
                        $resourceController,
                        'postItem'
                    ), $_POST);
                break;
            case "PUT":
                $arrayData = file_get_contents('php://input');
                $arrayData = json_decode($arrayData);
                $data      = call_user_func(array(
                    $resourceController,
                    'putItem'
                ), $arrayData);
                break;
            case "DELETE":
                $data = call_user_func(array(
                    $resourceController,
                    'deleteItem'
                ), $parameter);
                break;
        }
        
        if (isset($data) == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Data is missing');
        
        if (is_object($data))
            $data = JsonSerializer::serialize($data);
        else
            $data = json_encode($data);
        
        echo utf8_encode($data);
    }
}
?>