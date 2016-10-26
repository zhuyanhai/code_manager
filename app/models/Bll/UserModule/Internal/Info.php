<?php
/**
 * 内部API (其它模块不可访问)
 * 
 * 访问权限 - 仅 Bll/UserModule 目录中的任何程序
 * 
 * 用户信息逻辑
 * 
 * @package Bll
 * @subpackage Bll_UserModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_UserModule_Internal_Info extends F_InternalAbstract
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
     * @staticvar Bll_UserModule_Internal_Info $instance
     * @return \Bll_UserModule_Internal_Info
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
            $formatResult = $this->_format(array($userRow));
            return F_Result::build()->success($formatResult[0]);
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
            $formatResult = $this->_format(array($userRow));
            return F_Result::build()->success($formatResult[0]);
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
    	$token  = md5('@c=^s%(5+5)!-=+c,.7@'.md5('token'.$userid . $passwd) . $registerTime);
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
     * 设置登陆COOKIE
     * 
     * @return void
     */
    public function delLoginCookie()
    {
    	Utils_Cookie::del('cm-token');
        Utils_Cookie::del('cm-ticket');
    }
    
//----- 私有方法
    
    /**
     * 格式化用户基本信息
     * 
     * @param Dao_User_User $userRowList
     */
    private function _format($userRowList)
    {
        $return = array();
        foreach ($userRowList as $userRow) {
            $tmp = $userRow->toArray();
            $tmp['isLock'] = $userRow->isLock();
            array_push($return, $tmp);
        }
        return $return;
    }
}