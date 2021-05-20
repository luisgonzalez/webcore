<?php
/**
 * @package WebCore
 * @subpackage Security
 * @version 1.0
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.application.php";
require_once "webcore.data.php";
require_once "webcore.web.php";
require_once "webcore.mail.php";

/**
 * Represents a role-based permission to either allow or deny any given operation.
 * 
 * @package WebCore
 * @subpackage Security
 */
class Permission extends ObjectBase
{
    const PERMISSION_ALLOW = 1;
    const PERMISSION_DENY = 0;
    
    const ROLENAME_ALL = '*';
    const ROLENAME_ANONYMOUS = '?';
    
    protected $roleName;
    protected $permissionType;
    
    /**
     * Creates a new instance of this class
     * @param string $roleName
     * @param int $permissionType One of the PERMISSION_-Prefixed constants defined in this class
     */
    public function __construct($roleName = '*', $permissionType = 0)
    {
        $this->permissionType = $permissionType;
        $this->roleName       = $roleName;
    }
    
    /**
     * Create a default instance of this class.
     *
     * @return Permission
     */
    public static function createInstance()
    {
        return new Permission();
    }
    
    /**
     * Gets the role name for this permission object
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }
    
    /**
     * Sets the role name for this permission object
     * @param string $value
     */
    public function setRoleName($value)
    {
        if (!is_string($value))
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'value must be a string');
        $this->roleName = $value;
    }
    
    /**
     * Gets the allow or deny constant associated with this permission.
     *
     * @return int
     */
    public function getPermissionType()
    {
        return $this->permissionType;
    }
    
    /**
     * Sets the allow or deny constant associated with this permisison.
     * @param int $value
     */
    public function setPermissionType($value)
    {
        if (intval($value) != 0 && intval($value) != 1)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'value must be an integer between 0 and 1');
        $this->permissionType = intval($value);
    }
}

/**
 * Provides a Permission object placeholder and permission resolution functionality.
 *
 * @package WebCore
 * @subpackage Security
 */
class PermissionSet extends IndexedCollection
{
    protected $defaultResolve;
    
    /**
     * Creates a new instance of this class
     *
     * @param int $defaultResolve One of the Permission::PERMISSION_ - prefixed constants to which the resolve() method defaults.
     */
    public function __construct($defaultResolve = 0)
    {
        parent::__construct();
        $this->defaultResolve = $defaultResolve;
    }
    
    /**
     * Shortcut method to allow a role into the resolve() resolution.
     *
     * @param string $roleName
     */
    public function addAllowedRole($roleName)
    {
        $allowPerm = new Permission($roleName, Permission::PERMISSION_ALLOW);
        $this->addItem($allowPerm);
    }
    
    /**
     * Gets the default resolution for the resolve() method.
     * @return int
     */
    public function getDefaultResolve()
    {
        return $this->defaultResolve;
    }
    
    /**
     * Sets the default resolution for the resolve() method.
     * @param int $value
     */
    public function setDefaultResolve($value)
    {
        $this->defaultResolve = $value;
    }
    
    /**
     * Resolves whether the user is allowed to execute a given action
     * Returns 0 for deny, 1 for allow
     * 
     * @param MembershipUser $user null for (anonymous)
     * @return int
     */
    public function resolve($user = null)
    {
        $resolution = $this->getDefaultResolve();
        
        if (is_null($user))
        {
            // Anonymous access
            foreach ($this->getArrayReference() as $perm)
            {
                $appliesToRole = (Permission::ROLENAME_ANONYMOUS == $perm->getRoleName());
                
                if ($appliesToRole)
                    $resolution = $perm->getPermissionType();
            }
            
            return $resolution;
        }
        else
        {
            // Role-based Access
            $roles = $user->getRoles();
            
            foreach ($this->getArrayReference() as $perm)
            {
                $appliesToRole = ($roles->containsValue($perm->getRoleName()) || $perm->getRoleName() == Permission::ROLENAME_ALL);
                
                if ($appliesToRole)
                    $resolution = $perm->getPermissionType();
            }
            
            return $resolution;
        }
    }
}

/**
 * Static class to provide easy way to hash data and validate hashes
 *
 * @package WebCore
 * @subpackage Security
 */
class HashProvider extends HelperBase
{
    const ALGORITHM_MD5 = "md5";
    const ALGORITHM_SHA1 = "sha1";
    const ALGORITHM_SHA256 = "sha256";
    const ALGORITHM_CRC32 = "crc32";
    
    /**
     * Hashs a password with settings algorithm
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword($password)
    {
        $securitySettings = Settings::getValue(Settings::SKEY_SECURITY);
        $algo             = $securitySettings[Settings::KEY_SECURITY_HASHALGORITHM];
        
        if ($algo !== '')
            return self::getHash($password, $algo);
        
        return $password;
    }
    
    /**
     * Generates a hash password using settings
     *
     * @param int $length
     * @return string
     */
    public static function generateHashPassword($length = 8)
    {
        $password = "";
        $possible = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $i        = 0;
        
        while ($i < $length)
        {
            $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            
            if (!strstr($password, $char))
            {
                $password .= $char;
                $i++;
            }
        }
        
        return $password;
    }
    
    /**
     * Hashes data
     *
     * @param string $data
     * @param string $algorithm
     * @return string
     */
    private static function hash($data, $algorithm)
    {
        if (in_array($algorithm, hash_algos()))
            return hash($algorithm, $data);
        
        return md5($data);
    }
    
    /**
     * Gets a hash from data
     *
     * @param string $data
     * @param string $algorithm
     * @return string
     */
    public static function getHash($data, $algorithm)
    {
        switch ($algorithm)
        {
            case self::ALGORITHM_SHA1:
                return self::hash($data, self::ALGORITHM_SHA1);
                break;
            case self::ALGORITHM_SHA256:
                return self::hash($data, self::ALGORITHM_SHA256);
                break;
            case self::ALGORITHM_CRC32:
                return self::hash($data, self::ALGORITHM_CRC32);
                break;
            default:
                return self::hash($data, self::ALGORITHM_MD5);
                break;
        }
    }
}

/**
 * Manages forms authentication.
 *
 * @package WebCore
 * @subpackage Security
 */
class FormsAuthentication extends HelperBase
{
    /**
     * Redirect from logins page, if returnUrl
     * variable isn't exist go to root.
     *
     * @param string $returnUrl
     */
    public static function redirectFromLoginPage($returnUrl = '')
    {
        if ($returnUrl === '')
        {
            $securitySettings = Settings::getValue(Settings::SKEY_SECURITY);
            $returnUrl        = $securitySettings[Settings::KEY_SECURITY_WELCOMEPAGE];
        }
        
        HttpResponse::write("<script>document.location.href='$returnUrl';</script>");
        HttpResponse::end();
    }
    
    /**
     * Redirects to application login page
     *
     * @param string $returnUrl
     */
    public static function redirectToLoginPage($returnUrl = '')
    {
        $page = self::getLoginUrl();
        if ($page != HttpContext::getInfo()->getValue('PHP_SELF'))
        {
            if ($returnUrl != '')
                $page .= "?returnUrl=" . base64_encode($returnUrl);
            
            if (headers_sent())
                HttpResponse::write("<script>document.location.href='$page';</script>");
            else
                HttpResponse::redirect($page);
        }
    }
    
    /**
     * Gets the full Url to the login page.
     * @return string
     */
    public static function getLoginUrl()
    {
        $securitySettings = Settings::getValue(Settings::SKEY_SECURITY);
        $page             = $securitySettings[Settings::KEY_SECURITY_LOGINPAGE];
        $page             = HttpContext::getApplicationRoot() . $page;
        return $page;
    }
    
    /**
     * Signs out current user
     *
     */
    public static function signOut()
    {
        session_destroy();
        self::redirectToLoginPage();
    }
    
    /**
     * Sets session user object
     *
     * @param string $userName
     */
    public static function setUser($userName)
    {
        $user = Membership::getInstance()->getUser($userName);
        if (is_null($user))
            throw new SystemException(SystemException::EX_MEMBERSHIPUSER, "Could not find user '{$userName}'.");
        
        HttpSession::getInstance()->setValue('user', $user);
    }
    
    /**
     * Returns session user object
     *
     * @return MembershipUser
     */
    public static function getUser()
    {
        if (HttpSession::getInstance()->keyExists('user'))
            return HttpSession::getInstance()->getValue('user');
        
        return null;
    }
}

/**
 * Represents a provider for authentificate users
 *
 * @package WebCore
 * @subpackage Security
 */
interface IAuthenticationProvider extends IHelper
{
    public static function validateUser($userName, $password);
}

/**
 * Represents a SMTP provider for authentificate users
 *
 * @package WebCore
 * @subpackage Security
 */
class SmtpAuthenticationProvider extends HelperBase implements IAuthenticationProvider
{
    public static function validateUser($userName, $password)
    {
        try
        {
            $smtpClient = new SmtpClient();
            $smtpClient->setUsername($userName);
            $smtpClient->setPassword($password);
            
            $tracking = array();
            $smtpClient->authenticateUser($tracking);
        }
        catch (SystemException $ex)
        {
            throw $ex;
            return false;
        }
        
        return true;
    }
}

/**
 * Represents a database provider for authentificate users
 *
 * @package WebCore
 * @subpackage Security
 */
class DatabaseAuthenticationProvider extends HelperBase implements IAuthenticationProvider
{
    public static function validateUser($userName, $password)
    {
        $securitySettings = Settings::getValue(Settings::SKEY_SECURITY);
        $userAdapter      = DataContext::getInstance()->getAdapter($securitySettings[Settings::KEY_SECURITY_USERENTITY]);
        $userFields       = explode(",", $securitySettings[Settings::KEY_SECURITY_USERENTITYFIELD]);
        
        $userNameField = $userFields[0];
        $passwordField = $userFields[1];
        $enableField   = $userFields[2];
        
        $user = $userAdapter->where("$userNameField = '$userName' AND $passwordField = '$password' AND $enableField = 1")->selectOne();
        
        if (is_null($user))
            return false;
        return true;
    }
}

/**
 * This singleton represent an easy access to all membership & roles actions,
 * like create or get a user or roles.
 *
 * @package WebCore
 * @subpackage Security
 */
interface IMembershipProvider extends ISingleton
{
    /**
     * Creates a new user
     *
     */
    public function createUser($userName, $password);
    
    /**
     * Gets current user
     *
     */
    public function getUser($username);
    
    /**
     * Updates an user
     *
     */
    public function updateUser($user);
    
    /**
     * Deletes an user
     *
     */
    public function deleteUser($userName);
    
    /**
     * Creates a role
     *
     */
    public function createRole($roleName);
    
    /**
     * Deletes a role
     *
     */
    public function deleteRole($roleName);
    
    /**
     * Adds a user to role
     *
     */
    public function addUserToRole($userName, $roleName);
    
    /**
     * Gets roles for user
     *
     */
    public function getRolesForUser($userId);
    
    /**
     * Gets users in role
     *
     */
    public function getUsersInRole($roleName);
    
    /**
     * Returns true if user is in role
     *
     */
    public function isUserInRole($userName, $roleName);
    
    /**
     * Removes user from role
     *
     */
    public function removeUserFromRole($userName, $roleName);
    
    /**
     * Returns true if role exists
     *
     */
    public function roleExists($roleName);
}

/**
 * Database based Membership Provider
 *
 * @package WebCore
 * @subpackage Security
 */
class DatabaseMembershipProvider extends HelperBase implements IMembershipProvider
{
    /**
     * Represents the instance of the singleton object
     *
     * @var DatabaseMembershipProvider
     */
    private static $__instance = null;
    /**
     * @var TableAdapter
     */
    private static $userAdapter;
    /**
     * @var TableAdapter
     */
    private static $roleAdapter;
    /**
     * @var TableAdapter
     */
    private static $userInRoleAdapter;
    private static $userFields;
    private static $settings;
    
    /**
     * Gets username field
     *
     * @return string
     */
    private function getUserNameField()
    {
        if (count(self::$userFields) > 0)
            return self::$userFields[0];
    }
    
    /**
     * Gets password field
     *
     * @return string
     */
    private function getPasswordField()
    {
        if (count(self::$userFields) > 1)
            return self::$userFields[1];
    }
    
    /**
     * Gets enable field
     *
     * @return string
     */
    private function getEnableField()
    {
        if (count(self::$userFields) > 2)
            return self::$userFields[2];
    }
    
    /**
     * Gets the singleton instance for this class.
     *
     * @return DatabaseMembershipProvider
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
        {
            self::$__instance        = new DatabaseMembershipProvider();
            self::$settings          = Settings::getValue(Settings::SKEY_SECURITY);
            self::$userAdapter       = DataContext::getInstance()->getAdapter(self::$settings[Settings::KEY_SECURITY_USERENTITY]);
            self::$roleAdapter       = DataContext::getInstance()->getAdapter(self::$settings[Settings::KEY_SECURITY_ROLEENTITY]);
            self::$userInRoleAdapter = DataContext::getInstance()->getAdapter(self::$settings[Settings::KEY_SECURITY_USERROLEENTITY]);
            self::$userFields        = explode(",", self::$settings[Settings::KEY_SECURITY_USERENTITYFIELD]);
        }
        
        return self::$__instance;
    }
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
    
    /**
     * Creates an user in database
     *
     * @param string $userName
     * @param string $password
     * @return bool
     */
    public function createUser($userName, $password)
    {
        $userEntity    = self::$userAdapter->defaultEntity();
        $user          = new MembershipUser($userEntity);
        $userNameField = self::getUserNameField();
        $passwordField = self::getPasswordField();
        
        $user->$userNameField = $userName;
        $user->$passwordField = HashProvider::hashPassword($password);
        
        $userEntity = $user->getEntity();
        
        self::$userAdapter->insert($userEntity);
        
        if ($userEntity->UserId > 0)
            return true;
        
        return false;
    }
    
    /**
     * Gets an user
     *
     * @param string $userName
     * @return MembershipUser
     */
    public function getUser($userName)
    {
        $userNameField = self::getUserNameField();
        $user = self::$userAdapter->where("$userNameField = '$userName'")->selectOne();
        
        return new MembershipUser($user);
    }
    
    /**
     * Updates an user in database
     *
     * @param MembershipUser $user
     * @return bool
     */
    public function updateUser($user)
    {
        return self::$userAdapter->update($user);
    }
    
    /**
     * Deletes an user in database
     *
     * @param Entity $user
     * @return bool
     */
    public function deleteUser($user)
    {
        $userKey = self::$settings[Settings::KEY_SECURITY_USERENTITYKEY];
        
        $roles = self::$userInRoleAdapter->where("$userKey = " . $user->UserId)->select();
        self::$userInRoleAdapter->deleteAll($roles);
        
        return self::$userAdapter->delete($user);
    }
    
    /**
     * Gets a role
     *
     * @param string $roleName
     * @return Role
     */
    public function getRole($roleName)
    {
        $roleNameField = self::$settings[Settings::KEY_SECURITY_ROLEENTITYFIELD];
        $role = self::$roleAdapter->where("$roleNameField = '$roleName'")->selectOne();
        
        return $role;
    }
    
    /**
     * Creates a new role in database
     *
     * @param string $roleName
     * @return bool
     */
    public function createRole($roleName)
    {
        $role           = new Role();
        $role->RoleName = $roleName;
        
        self::$roleAdapter->insert($role);
        if (trim($role->RoleId) != '')
            return true;
        
        return false;
    }
    
    /**
     * Deletes a role
     *
     * @param string $roleName
     * @return bool
     */
    public function deleteRole($roleName)
    {
        $rolNameField = self::$settings[Settings::KEY_SECURITY_ROLEENTITYFIELD];
        $role         = self::$roleAdapter->where("$rolNameField = '$roleName'")->selectOne();
        
        if (!is_null($role))
            return self::$roleAdapter->delete($role);
        
        return false;
    }
    
    /**
     * Adds an user to a role
     *
     * @param string $userName
     * @param string $roleName
     * @return bool
     */
    public function addUserToRole($userName, $roleName)
    {
        $user = $this->getUser($userName);
        $role = $this->getRole($roleName);
        
        $userInRole         = new UserInRole();
        $userInRole->UserId = $user->UserId;
        $userInRole->RoleId = $role->RoleId;
        
        self::$userInRoleAdapter->insert($userInRole);
        
        if (!is_null($userInRole))
            return true;
        
        return false;
    }
    
    /**
     * Gets roles for an user
     *
     * @param int $userId
     * @return IndexedCollection
     */
    public function getRolesForUser($userId)
    {
        $rolesCollection = new IndexedCollection();
        $userKey         = self::$settings[Settings::KEY_SECURITY_USERROLEENTITYUSERKEY];
        $rolNameField    = self::$settings[Settings::KEY_SECURITY_ROLEENTITYFIELD];
        $userRoleEntity  = self::$settings[Settings::KEY_SECURITY_USERROLEENTITY];
        
        $roles = self::$roleAdapter->joinRelated($userRoleEntity)->where($userRoleEntity . "." . "$userKey = $userId")->addField(self::$settings[Settings::KEY_SECURITY_ROLEENTITY], $rolNameField)->selectNew();
        
        foreach ($roles as $rol)
        {
            $rolName = $rol->$rolNameField;
            $rolesCollection->addItem($rolName);
        }
        
        return $rolesCollection;
    }
    
    /**
     * Gets users in a role
     *
     * @param string $roleName
     * @return IndexedCollection
     */
    public function getUsersInRole($roleName)
    {
        $userRoleEntity = self::$settings[Settings::KEY_SECURITY_USERROLEENTITY];
        $rolId          = self::$settings[Settings::KEY_SECURITY_ROLEENTITYKEY];
        $userNameField  = self::getUserNameField();
        
        $rol = self::getRole($roleName);
        
        $users = self::$userAdapter->joinRelated($userRoleEntity)->where($userRoleEntity . "." . $rolId . " = " . $rol->$rolId)->select();
        
        return $users;
    }
    
    /**
     * Returns true if user is in role
     *
     * @param string $userName
     * @param string $roleName
     * @return bool
     */
    public function isUserInRole($userName, $roleName)
    {
        $userKey = self::$settings[Settings::KEY_SECURITY_USERENTITYKEY];
        
        $user = $this->getUser($userName);
        $role = $this->getRole($roleName);
        
        $userInRole = self::$userInRoleAdapter()->where("$userKey = " . $user->UserID)->where("RoleID = " . $role->RoleID)->selectOne();
        
        if (is_null($userInRole))
            return false;
        
        return true;
    }
    
    /**
     * Removes a user from role
     *
     * @param string $userName
     * @param string $roleName
     * @return bool
     */
    public function removeUserFromRole($userName, $roleName)
    {
        $user = $this->getUser($userName);
        $role = $this->getRole($roleName);
        
        $userInRole         = new UserInRole();
        $userInRole->UserId = $user->UserId;
        $userInRole->RoleId = $role->RoleId;
        
        return self::$userInRoleAdapter->delete($userInRole);
    }
    
    /**
     * Returns true if role exists
     *
     * @param string $roleName
     * @return bool
     */
    public function roleExists($roleName)
    {
        $role = $this->getRole($roleName);
        
        if (is_null($role))
            return false;
        
        return true;
    }
}

/**
 * Represent a abstract class for Security Managers like Membership and Roles
 * 
 * @package WebCore
 * @subpackage Security
 */
abstract class SecurityManager extends HelperBase implements ISingleton
{
    /**
     * @var IMembershipProvider
     */
    protected static $membershipProvider;
    protected static $applicationName;
    
    protected function __construct()
    {
        $settings      = Settings::getValue(Settings::SKEY_SECURITY);
        $providerClass = $settings[Settings::KEY_SECURITY_MEMBERSHIPPROVIDER];
        
        if (ObjectIntrospector::isImplementing(new $providerClass(), 'IMembershipProvider') === false)
        {
            throw new SystemException(SystemException::EX_CLASSNOTFOUND, 'MembershipProvider class is not recognized');
        }
        
        self::$membershipProvider = call_user_func(array($providerClass, 'getInstance'));
    }
    
    /**
     * Gets application name
     *
     * @return string
     */
    public function getApplicationName()
    {
        return self::$applicationName;
    }
    
    /**
     * Gets application name
     *
     * @param string $applicationName
     */
    public function setApplicationName($applicationName)
    {
        self::$applicationName = $applicationName;
    }
}

/**
 * Helper class to provide membership's actions
 *
 * @package WebCore
 * @subpackage Security
 */
class Membership extends SecurityManager
{
    protected static $__instance = null;
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
    
    /**
     * Gets the singleton instance for this class.
     *
     * @return Membership
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new Membership();
        
        return self::$__instance;
    }
    
    /**
     * Creates an new user
     *
     * @param string $userName
     * @param string $password
     * @return bool
     */
    public function createUser($userName, $password)
    {
        $user = $this->getUser($userName);
        
        if (is_null($user))
            return parent::$membershipProvider->createUser($userName, $password);
        
        throw new SystemException(SystemException::EX_MEMBERSHIPUSER, 'Action = create');
    }
    
    /**
     * Gets an user
     *
     * @return MembershipUser
     */
    public function getUser($userName)
    {
        return self::$membershipProvider->getUser($userName);
    }
    
    /**
     * Validates username and password for do a login
     *
     * @param string $userName
     * @param string $password
     * @return bool
     */
    public function validateUser($userName, $password)
    {
        $password = HashProvider::hashPassword($password);
        $securitySettings = Settings::getValue(Settings::SKEY_SECURITY);
        $authClass = $securitySettings[Settings::KEY_SECURITY_AUTHPROVIDER];
        
        if ($authClass == '')
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'No authentication provider class was found in app.settings.');
        
        if (ObjectIntrospector::isImplementing(new $authClass(), 'IAuthenticationProvider') === false)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'No authentication provider class was found in app.settings.');
            
        return call_user_func(array($authClass, 'validateUser'), $userName, $password);
    }
    
    /**
     * Updates an user
     *
     * @param MembershipUser $user
     */
    public function updateUser($user)
    {
        self::$membershipProvider->updateUser($user);
    }
    
    /**
     * Deletes an user
     *
     * @param MembershipUser $userName
     * @return bool
     */
    public function deleteUser($userName)
    {
        $user = $this->getUser($userName);
        
        if (is_null($user))
            throw new SystemException(SystemException::EX_MEMBERSHIPUSER, 'Action = delete');
        
        return self::$membershipProvider->deleteUser($user);
    }
}

/**
 * Helper class to provide role's actions
 * 
 * @package WebCore
 * @subpackage Security
 */
class RoleManager extends SecurityManager
{
    protected static $__instance = null;
    
    /**
     * Determines whether the singleton has loaded its instance
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (is_null(self::$__instance))
            return false;
        
        return true;
    }
    
    /**
     * Gets the singleton instance for this class.
     *
     * @return Roles
     */
    public static function getInstance()
    {
        if (self::isLoaded() === false)
            self::$__instance = new RoleManager();
        
        return self::$__instance;
    }
    
    /**
     * Creates a new role
     *
     * @param string $roleName
     * @return bool
     */
    public function createRole($roleName)
    {
        if ($this->roleExists($roleName))
            throw new SystemException(SystemException::EX_MEMBERSHIPROLE, 'Action = create');
        
        return self::$membershipProvider->createRole($roleName);
    }
    
    /**
     * Deletes a role
     *
     * @param string $roleName
     * @return bool
     */
    public function deleteRole($roleName)
    {
        if ($this->roleExists($roleName) === true)
            return self::$membershipProvider->deleteRole($roleName);
        
        throw new SystemException(SystemException::EX_MEMBERSHIPROLE, 'Action = delete');
    }
    
    /**
     * Adds a user to role
     *
     * @param string $userName
     * @param string $roleName
     * @return bool
     */
    public function addUserToRole($userName, $roleName)
    {
        if ($this->isUserInRole($userName, $roleName))
            throw new SystemException(SystemException::EX_MEMBERSHIPUSER, 'Action = addRole');
        
        return self::$membershipProvider->addUserToRole($userName, $roleName);
    }
    
    /**
     * Gets roles from an user
     *
     * @param int $userId
     * @return IndexedCollection
     */
    public function getRolesForUser($userId)
    {
        return self::$membershipProvider->getRolesForUser($userId);
    }
    
    /**
     * Gets users from a role
     *
     * @param string $roleName
     * @return IndexedCollection
     */
    public function getUsersInRole($roleName)
    {
        if (self::roleExists($roleName))
            return self::$membershipProvider->getUsersInRole($roleName);
        
        throw new SystemException(SystemException::EX_MEMBERSHIPUSER, 'Action = getUsers');
    }
    
    /**
     * Returns true if user is in role
     *
     * @param string $userName
     * @param string $roleName
     * @return bool
     */
    public function isUserInRole($userName, $roleName)
    {
        if ($this->roleExists($roleName))
            return self::$membershipProvider->isUserInRole($userName, $roleName);
        
        throw new SystemException(SystemException::EX_MEMBERSHIPROLE, 'Action = isUser');
    }
    
    /**
     * Removes user from role
     *
     * @param string $userName
     * @param string $roleName
     * @return bool
     */
    public function removeUserFromRole($userName, $roleName)
    {
        if (self::roleExists($roleName) === true)
            return self::$membershipProvider->removeUserFromRole($userName, $roleName);
        
        throw new SystemException(SystemException::EX_MEMBERSHIPUSER, 'Action = removeUser');
    }
    
    /**
     * Returns true if role exists
     *
     * @param string $roleName
     * @return bool
     */
    public function roleExists($roleName)
    {
        return self::$membershipProvider->roleExists($roleName);
    }
}

/**
 * This class represents an user
 *
 * @package WebCore
 * @subpackage Security
 */
interface IMembershipUser extends IObject
{
    public function getUserId();
    
    public function getUserName();
    
    public function getRoles();
}

/**
 * This class represents an user with an inner entity
 *
 * @package WebCore
 * @subpackage Security
 */
class MembershipUser extends ObjectBase implements IMembershipUser
{
    /**
     * @var KeyedCollection
     */
    protected $userEntity;
    protected $userFields;
    protected $userKey;
    
    /**
     * Creates a new instance of this class
     *
     * @param EntityBase $userEntity
     */
    public function __construct($userEntity)
    {
        $settings         = Settings::getValue(Settings::SKEY_SECURITY);
        $this->userFields = explode(",", $settings[Settings::KEY_SECURITY_USERENTITYFIELD]);
        $this->userKey    = $settings[Settings::KEY_SECURITY_USERENTITYKEY];
        
        if (ObjectIntrospector::isA($userEntity, 'EntityBase'))
            $this->userEntity = $userEntity->toDataSource();
        else
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, "Parameter = userEntity");
    }
    
    /**
     * Gets username
     *
     * @return string
     */
    public function getUserName()
    {
        $userName = $this->userFields[0];
        
        return $this->userEntity->getValue($userName);
    }
    
    /**
     * Returns true if user is enable
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        $enable = $this->userFields[2];
        
        return $this->userEntity->getValue($enable);
    }
    
    /**
     * Gets user ID
     *
     * @return mixed
     */
    public function getUserId()
    {
        $key = $this->userKey;
        return $this->userEntity->getValue($key);
    }
    
    /**
     * Gets user's property
     *
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->userEntity->getValue($name);
    }
    
    /**
     * Sets user's property
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->userEntity->setValue($name, $value);
    }
    
    /**
     * Checks if current user is in role
     *
     * @param string $roleName
     * @return bool
     */
    public function isInRole($roleName)
    {
        return RoleManager::getInstance()->isUserInRole(self::getUserName(), $roleName);
    }
    
    /**
     * Adds current user to a role
     *
     * @param string $roleName
     * @return bool
     */
    public function addToRole($roleName)
    {
        return RoleManager::getInstance()->addUserToRole(self::getUserName(), $roleName);
    }
    
    /**
     * Removes current user from a role
     *
     * @param string $roleName
     * @return bool
     */
    public function removeFromRole($roleName)
    {
        return RoleManager::getInstance()->removeUserFromRole(self::getUserName(), $roleName);
    }
    
    /**
     * Gets user roles
     *
     * @return IndexedCollection
     */
    public function getRoles()
    {
        return RoleManager::getInstance()->getRolesForUser(self::getUserId());
    }
    
    /**
     * Gets keyed collection
     *
     * @return KeyedCollection
     */
    public function toDataSource()
    {
        return $this->userEntity;
    }
}
?>