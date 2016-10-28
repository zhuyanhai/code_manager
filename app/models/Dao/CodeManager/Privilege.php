<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_Privilege_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_Privilege',
        //完整表名
        'tableName'    => 'tbl_privilege',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //数据表主键字段名
        'primaryKey'   => 'id',
    );
}

/**
 * tbl_privilege 数据表类
 * 
 * 用户信息
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_Privilege extends Dao_Abstract
{
    /**
     * 显示类型格式化后的数据
     * 
     * @return string
     */
    public function ___showMenuType()
    {
        if (!isset($this->menu_type)) {
            return '';
        }
        switch (intval($this->menu_type)) {
            case 1:
                return '模块';
                break;
            case 2:
                return '目录';
                break;
            case 3:
                return '菜单';
                break;
        }
    }
}