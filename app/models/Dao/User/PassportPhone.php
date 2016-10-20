<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_User_PassportPhone_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_User_PassportPhone',
        //完整表名
        'tableName'    => 'tbl_passport_phone',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'user',
        //数据表主键字段名
        'primaryKey'   => 'account',
    );
}

/**
 * tbl_passport_phone 数据表类
 * 
 * 关于手机号账号信息的数据表,主要是在登录时校验账号有效性使用
 * 
 * @package Dao
 * @subpackage Dao_Sop
 * @author allen <allenifox@163.com>
 */
final class Dao_User_PassportPhone extends Dao_Abstract
{

}
