<?php
/**
 * 内部API (外部模块不可访问)
 * 
 * 访问权限 - 仅 Bll/User 目录中的任何程序
 * 
 * 用户 - 登录账户 API
 *
 * - 获取账号的基本信息
 * 
 * @package Bll
 * @subpackage Bll
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_User_Internal_User
{
    /**
     * 获取类实例
     *
     * @staticvar Bll_User_Internal_User $instance
     * @return \Bll_User_Internal_User
     */
    public static function getInstance()
    {
        static $instance = null;
        if(null == $instance){
            $instance = new self();
        }
        return $instance;
    }
    
    /**
     * 获取用户信息 - 根据用户ID
     * 
     * @param int $userid 用户ID
     * @return array
     */
    public function getByUserid($userid)
    {
        $userRow = Dao_User_User::getSelect()->where('userid=:userid', $userid)->fetchRow();
        if (!empty($userRow)) {
            $result = $this->_format(array($userRow));
            return $result[0];
        }
        return array();
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