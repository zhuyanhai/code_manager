<?php
/**
 * 内部API (外部模块不可访问)
 * 
 * 访问权限 - 仅 User 目录中的任何程序
 * 
 * 用户 - 登录账户 API
 *
 * - 检测登录的账户是否存在
 * 
 * @package Bll
 * @subpackage Bll
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_User_Passport_Phone
{
    /**
     * 获取类实例
     *
     * @staticvar Bll_User_Passport_Phone $instance
     * @return \Bll_User_Passport_Phone
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
     * 获取用户信息 - 根据登录账户
     * 
     * @param string $account 登录帐号
     * @return array
     */
    public function getByAccount($account)
    {
        $passportPhoneRow = Dao_User_PassportPhone::getSelect()->fromColumns('userid')->where('account=:account', $account)->fetchRow();
        if (!empty($passportPhoneRow)) {
            return $passportPhoneRow->toArray();
        }
        return array();
    }
    
}