<?php
/**
 * 内部API (其它模块不可访问)
 * 
 * 访问权限 - 仅 Bll/AccountModule 目录中的任何程序
 * 
 * 用户信息逻辑
 * 
 * @package Bll
 * @subpackage Bll_AccountModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_AccountModule_Internal_User extends F_InternalAbstract
{
    private function __construct()
    {
        //empty
    }
    
    /**
     * 单例模式
     * 
     * 获取类的对象实例
     * 
     * @staticvar Bll_AccountModule_Internal_User $instance
     * @return \Bll_AccountModule_Internal_User
     */
    public static function getInstance()
    {
        static $instance = null;
        if(null == $instance){
            $instance = new self();
        }

        //本步骤必须，检查是否是本模块的调用
        $instance->checkCall();
        return $instance;
    }
    
    /**
     * 获取用户信息 - 根据用户ID
     * 
     * @param int $userid 用户ID
     * @return ResultSet
     */
    public function getByUserid($userid)
    {
        $userRow = Dao_CodeManager_User::getSelect()->where('userid=:userid', $userid)->fetchRow();
        if (!empty($userRow)) {
            return F_Result::build()->success($userRow->toArray());
        }
        return F_Result::build()->error('用户不存在');
    }
    
    /**
     * 获取用户信息 - 根据用户登录账号
     * 
     * @param string $account 用户登录账号
     * @return ResultSet
     */
    public function getByAccount($account)
    {
        $userRow = Dao_CodeManager_User::getSelect()->where('account=:account', $account)->fetchRow();
        if (!empty($userRow)) {
            return F_Result::build()->success($userRow->toArray());
        }
        return F_Result::build()->error('用户不存在');
    }
    
    /**
     * 生成用户密码，加盐的
     * 
     * @param string $passwdOfClearText 密码明文
     * @param int $registerTime 注册时间
     */
    public function buildPassword($passwdOfClearText, $registerTime)
    {
        $secretKey = '$-#0#-d0()-+;,3f@S1v';
        return md5(md5($passwdOfClearText.$secretKey).$registerTime);
    }
    
    /**
     * 获取登陆COOKIE
     * 
     * @return ResultSet
     */
    public function getLoginCookie()
    {
        $token  = Utils_Cookie::get('cm-token');
        $ticket = Utils_Cookie::get('cm-ticket');
        
        if (empty($ticket) || empty($token)) {
            return F_Result::build()->error('cookie 不存在');
        }
            
        $offset = strlen($ticket) - 14;
    	$userid = substr($ticket, 5, $offset);
        
        return F_Result::build()->success(array('userid' => $userid, 'token' => $token));
    }
    
    /**
     * 设置登陆COOKIE
     * 
	 * @param int $userid 用户ID
     * @param string $passwd 用户密码
     * @param int $registerTime 用户注册时间
     * @param int $isRemember 是否自动登录
     * @return void
     */
    public function setLoginCookie($userid, $passwd, $registerTime, $isRemember)
    {
    	$token  = $this->_createToken($userid, $passwd, $registerTime);
        $time   = microtime(true) * 10000 . '';
        $ticket = substr($time, 0, 5) . $userid . substr($time, 5);
        if (intval($isRemember) === 1) {//记住
            Utils_Cookie::set('cm-token', $token, 10, 'y');
            Utils_Cookie::set('cm-ticket', $ticket, 10, 'y');
        } else {
            Utils_Cookie::set('cm-token', $token);
            Utils_Cookie::set('cm-ticket', $ticket);
        }
    }
    
    /**
     * 检测token是否一致
     * 
     * @param int $userid
     * @param string $passwd
     * @param int $registerTime
     * @param string $cookieToken
     * @return ResultSet
     */
    public function checkToken($userid, $passwd, $registerTime, $cookieToken)
    {
        $token = $this->_createToken($userid, $passwd, $registerTime);
        if ($token === $cookieToken) {
            return F_Result::build()->success();
        }
        return F_Result::build()->error();
    }
    
    /**
     * 设置登陆COOKIE
     * 
     * @return void
     */
    public function delLoginCookie()
    {
    	Utils_Cookie::del('cm-token');
        Utils_Cookie::del('cm-ticket');
    }
    
    /**
     * 添加用户
     * 
     * @param array $userInfo
     * @return F_Result
     */
    public function add($userInfo)
    {
        $time = time();
        $userInfo['create_time'] = $time;
        $userInfo['update_time'] = $time;
        $userInfo['passwd']      = $this->buildPassword($userInfo['passwd'], $time);
        try {
            $userid = Dao_CodeManager_User::getInsert()->insert($userInfo);
            return F_Result::build()->success(array('userid' => $userid));
        } catch(Exception $e) {
            $errorMsg = $e->getMessage();
            if ($e->getCode() == 23000 && preg_match('%^SQLSTATE\[23000\]%i', $errorMsg)) {
                return F_Result::build()->error('账户请勿重复');
            }
        }
    }
    
//----- 私有方法
    
    /**
     * 创建登录token
     * 
     * @param int $userid
     * @param string $passwd
     * @param int $registerTime
     * @return string
     */
    private function _createToken($userid, $passwd, $registerTime)
    {
        return md5('@c=^s%(5+5)!-=+c,.7@'.md5('token'.$userid . $passwd) . $registerTime);
    }
}